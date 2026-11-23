<?php
/*
|--------------------------------------------------------------------------
| REPORT BUILDER – data + filters (used by reports.php and download_report.php)
|--------------------------------------------------------------------------
| 1. rpDataset()  collects everything this organization has:
|      projects, teams, individual students, proposals, feedback
|    (from the database)
| 2. rpParams()   reads the options the organization picked (period, type, ...)
| 3. rpBuild()    filters the rows and makes the stat cards, charts and table
*/

const RP_TYPES = [
    'projects'   => ['label' => 'Projects',    'icon' => 'folder_open',  'hint' => 'Every project you posted'],
    'teams'      => ['label' => 'Teams',       'icon' => 'groups',       'hint' => 'How each team is doing'],
    'individual' => ['label' => 'Individual',  'icon' => 'person',       'hint' => 'Each student’s progress'],
    'proposals'  => ['label' => 'Proposals',   'icon' => 'description',  'hint' => 'Proposals from students'],
    'feedback'   => ['label' => 'Feedback',    'icon' => 'reviews',      'hint' => 'Feedback you gave teams'],
];

const RP_PERIODS = [
    '7d' => 'Last 7 days', '30d' => 'Last 30 days', 'month' => 'This month', '3m' => 'Last 3 months',
    '6m' => 'Last 6 months', 'year' => 'This year', 'all' => 'All time', 'custom' => 'Custom range',
];

// Columns each report can have  (key => label). The organization picks which ones to include.
const RP_COLUMNS = [
    'projects' => [
        'title' => 'Project', 'category' => 'Category', 'status' => 'Status', 'posted' => 'Posted On',
        'deadline' => 'Deadline', 'needed' => 'Students Needed', 'proposals' => 'Proposals',
        'accepted' => 'Accepted', 'teams' => 'Teams', 'students' => 'Students', 'progress' => 'Avg. Progress',
    ],
    'teams' => [
        'team' => 'Team', 'project' => 'Project', 'category' => 'Category', 'status' => 'Status',
        'leader' => 'Team Leader', 'members' => 'Members', 'progress' => 'Progress', 'created' => 'Created On',
        'deadline' => 'Deadline', 'days_left' => 'Time Left', 'skills' => 'Skills',
    ],
    'individual' => [
        'student' => 'Student', 'email' => 'Email', 'university' => 'University', 'team' => 'Team',
        'project' => 'Project', 'role' => 'Role', 'progress' => 'Progress', 'work' => 'Work Status',
        'joined' => 'Joined On', 'skills' => 'Skills',
    ],
    'proposals' => [
        'student' => 'Student', 'email' => 'Email', 'project' => 'Project', 'category' => 'Category',
        'status' => 'Status', 'submitted' => 'Submitted On', 'skills' => 'Skills',
    ],
    'feedback' => [
        'project' => 'Project', 'team' => 'Team', 'category' => 'Category', 'rating' => 'Rating',
        'technical' => 'Technical', 'communication' => 'Communication', 'teamwork' => 'Teamwork',
        'problem' => 'Problem-solving', 'date' => 'Given On',
    ],
];

// Main date of each report type -> used by the period filter and the trend chart
const RP_DATE_FIELD = ['projects' => 'posted', 'teams' => 'created', 'individual' => 'joined', 'proposals' => 'submitted', 'feedback' => 'date'];

const RP_STATUS = [
    // in the order a project goes through them
    'projects'   => ['draft' => 'Draft', 'reviewing' => 'Reviewing', 'hold' => 'Hold', 'published' => 'Published', 'inprogress' => 'Active', 'completed' => 'Completed', 'rejected' => 'Rejected'],
    'teams'      => ['ontrack' => 'On Track', 'behind' => 'Behind Schedule', 'completed' => 'Completed'],
    'individual' => ['In Progress' => 'In Progress', 'Completed' => 'Completed', 'Not started' => 'Not started'],
    'proposals'  => ['pending' => 'Pending', 'accepted' => 'Accepted', 'rejected' => 'Rejected'],
];

function rpQuery(mysqli $conn, string $sql, string $types = '', array $params = []): array
{
    try {
        $st = $conn->prepare($sql);
        if ($types !== '') $st->bind_param($types, ...$params);
        $st->execute();
        $res = $st->get_result();
        return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    } catch (Throwable $e) {
        return [];   // table not created yet -> no rows
    }
}

function rpDate($v): string
{
    return ($v && strtotime((string)$v)) ? date('Y-m-d', strtotime((string)$v)) : '';
}

// ===================================================================
// 1. DATASET
// ===================================================================
function rpDataset(mysqli $conn, string $org): array
{
    // the team status follows the deadline / tasks / final report (Backend/TeamWorkspace.php)
    require_once __DIR__ . '/org_db.php';
    orgRefreshTeamStatuses($conn, $org);

    $d = ['projects' => [], 'teams' => [], 'individual' => [], 'proposals' => [], 'feedback' => []];

    // ---------- projects ----------
    foreach (rpQuery($conn,
        "SELECT p.id, p.title, c.category_name AS category, p.status, p.posted_at, p.deadline, p.members,
                (SELECT COUNT(*) FROM student_projects sp WHERE sp.project_id = p.id) AS students,
                (SELECT COALESCE(AVG(sp.progress), 0) FROM student_projects sp WHERE sp.project_id = p.id) AS progress
         FROM projects p LEFT JOIN project_categories c ON c.category_id = p.category_id
         WHERE p.organization_email = ? ORDER BY p.posted_at DESC", "s", [$org]) as $p) {
        $pid = (int)$p['id'];
        $apps = rpQuery($conn, "SELECT status FROM project_proposals WHERE project_id = ? AND status <> 'withdrawn'", "i", [$pid]);
        $teams = rpQuery($conn, "SELECT COUNT(*) AS c FROM org_teams WHERE project_id = ?", "i", [$pid]);
        $d['projects'][] = [
            'title' => $p['title'], 'category' => $p['category'] ?: 'Uncategorized',
            // Admin's 'Active' / old 'accepted' = Published (the organization's own Active is 'inprogress')
            // Active only when the team is full, otherwise it is shown as Published
            'status_key' => (in_array(strtolower($p['status'] ?? ''), ['active', 'approved', 'accepted'], true)
                             || (strtolower($p['status'] ?? '') === 'inprogress'
                                 && (int)$p['students'] < max(1, (int)$p['members'])))
                            ? 'published' : (strtolower($p['status'] ?? '') ?: 'reviewing'),
            'posted' => rpDate($p['posted_at']), 'deadline' => rpDate($p['deadline']),
            'needed' => (int)$p['members'], 'proposals' => count($apps),
            'accepted' => count(array_filter($apps, fn($a) => $a['status'] === 'accepted')),
            'teams' => (int)($teams[0]['c'] ?? 0), 'students' => (int)$p['students'], 'progress' => (int)round($p['progress']),
        ];
    }

    // ---------- teams + their members (individual) ----------
    $inTeam = [];
    foreach (rpQuery($conn,
        "SELECT t.id, t.name, t.leader_email, t.deadline, t.status, t.created_at,
                p.id AS pid, p.title, c.category_name AS category,
                (SELECT GROUP_CONCAT(ps.skill_name SEPARATOR ', ') FROM project_skills ps WHERE ps.project_id = p.id) AS keywords
         FROM org_teams t JOIN projects p ON p.id = t.project_id
         LEFT JOIN project_categories c ON c.category_id = p.category_id
         WHERE p.organization_email = ? ORDER BY t.created_at DESC", "s", [$org]) as $t) {
        $members = rpQuery($conn,
            "SELECT s.Email, s.Name, s.University, COALESCE(NULLIF(m.role, ''), sp.role) AS role, sp.progress, sp.status AS work
             FROM org_team_members m
             JOIN student s ON LOWER(s.Email) = LOWER(m.Email)
             LEFT JOIN student_projects sp ON LOWER(sp.Email) = LOWER(m.Email) AND sp.project_id = ?
             WHERE m.team_id = ?", "ii", [(int)$t['pid'], (int)$t['id']]);

        $leader = $t['leader_email']; $sum = 0;
        foreach ($members as $m) {
            $sum += (int)$m['progress'];
            if (strtolower($m['Email']) === strtolower($t['leader_email'])) $leader = $m['Name'];
            $inTeam[strtolower($m['Email']) . '|' . $t['pid']] = true;
            $d['individual'][] = [
                'student' => $m['Name'], 'email' => strtolower($m['Email']), 'university' => str_ireplace(' of ', ' of ', (string)($m['University'] ?? '')),
                'team' => $t['name'], 'project' => $t['title'], 'category' => $t['category'] ?: 'Uncategorized',
                'role' => $m['role'] ?: 'Team Member', 'progress' => (int)$m['progress'], 'work' => $m['work'] ?: 'Not started',
                'joined' => rpDate($t['created_at']),
                'skills' => implode(', ', array_column(rpQuery($conn, "SELECT skill_name FROM skills WHERE Email = ? ORDER BY percentage DESC LIMIT 4", "s", [$m['Email']]), 'skill_name')),
            ];
        }
        $d['teams'][] = [
            'team' => $t['name'], 'project' => $t['title'], 'category' => $t['category'] ?: 'Uncategorized',
            'status_key' => $t['status'] ?: 'ontrack', 'leader' => $leader, 'members' => count($members),
            'progress' => $members ? (int)round($sum / count($members)) : 0,
            'created' => rpDate($t['created_at']), 'deadline' => rpDate($t['deadline']),
            'skills' => (string)$t['keywords'],   // a team's skills = its project's skills (project_skills)
        ];
    }

    // students working on a project who are not in a team yet
    foreach (rpQuery($conn,
        "SELECT s.Email, s.Name, s.University, sp.role, sp.progress, sp.status AS work, p.id AS pid, p.title,
                c.category_name AS category, p.posted_at
         FROM student_projects sp JOIN student s ON s.Email = sp.Email JOIN projects p ON p.id = sp.project_id
         LEFT JOIN project_categories c ON c.category_id = p.category_id
         WHERE p.organization_email = ?", "s", [$org]) as $m) {
        if (isset($inTeam[strtolower($m['Email']) . '|' . $m['pid']])) continue;
        $d['individual'][] = [
            'student' => $m['Name'], 'email' => strtolower($m['Email']), 'university' => str_ireplace(' of ', ' of ', (string)($m['University'] ?? '')),
            'team' => 'No team', 'project' => $m['title'], 'category' => $m['category'] ?: 'Uncategorized',
            'role' => $m['role'] ?: 'Team Member', 'progress' => (int)$m['progress'], 'work' => $m['work'] ?: 'Not started',
            'joined' => rpDate($m['posted_at']),
            'skills' => implode(', ', array_column(rpQuery($conn, "SELECT skill_name FROM skills WHERE Email = ? ORDER BY percentage DESC LIMIT 4", "s", [$m['Email']]), 'skill_name')),
        ];
    }

    // ---------- proposals ----------
    foreach (rpQuery($conn,
        "SELECT s.Name, pa.student_email AS Email, pa.status, pa.submitted_at AS applied_at, p.title, c.category_name AS category
         FROM project_proposals pa JOIN projects p ON p.id = pa.project_id
         LEFT JOIN project_categories c ON c.category_id = p.category_id
         LEFT JOIN student s ON LOWER(s.Email) = LOWER(pa.student_email)
         WHERE p.organization_email = ? AND pa.status <> 'withdrawn'", "s", [$org]) as $a) {
        $d['proposals'][] = [
            'student' => $a['Name'] ?: $a['Email'], 'email' => strtolower($a['Email']), 'project' => $a['title'],
            'category' => $a['category'] ?: 'Uncategorized', 'status_key' => $a['status'] ?: 'pending',
            'submitted' => rpDate($a['applied_at']),
            'skills' => implode(', ', array_column(rpQuery($conn, "SELECT skill_name FROM skills WHERE Email = ? ORDER BY percentage DESC LIMIT 3", "s", [$a['Email']]), 'skill_name')),
        ];
    }

    // ---------- feedback ----------
    foreach (rpQuery($conn,
        "SELECT t.name AS team_name, f.rating, f.technical, f.communication, f.teamwork, f.problem_solving, f.created_at,
                p.title, c.category_name AS category
         FROM project_feedback f
         JOIN org_teams t ON t.id = f.team_id
         JOIN projects p ON p.id = t.project_id
         LEFT JOIN project_categories c ON c.category_id = p.category_id
         WHERE p.organization_email = ?", "s", [$org]) as $f) {
        $d['feedback'][] = [
            'project' => $f['title'], 'team' => $f['team_name'] ?: '-', 'category' => $f['category'] ?: 'Uncategorized',
            'rating' => (int)$f['rating'], 'technical' => (float)$f['technical'], 'communication' => (float)$f['communication'],
            'teamwork' => (float)$f['teamwork'], 'problem' => (float)$f['problem_solving'], 'date' => rpDate($f['created_at']),
        ];
    }

    return $d;
}

// ===================================================================
// 2. OPTIONS PICKED BY THE ORGANIZATION
// ===================================================================
function rpParams(array $q): array
{
    $type = isset(RP_TYPES[$q['type'] ?? '']) ? $q['type'] : 'teams';
    $period = isset(RP_PERIODS[$q['period'] ?? '']) ? $q['period'] : 'year';
    $cols = array_values(array_intersect((array)($q['cols'] ?? []), array_keys(RP_COLUMNS[$type])));
    $inc  = array_values(array_intersect((array)($q['inc'] ?? ['cards', 'charts', 'table']), ['cards', 'charts', 'table']));
    $sort = isset(RP_COLUMNS[$type][$q['sort'] ?? '']) ? $q['sort'] : RP_DATE_FIELD[$type];

    return [
        'type' => $type, 'period' => $period,
        'from' => rpDate($q['from'] ?? ''), 'to' => rpDate($q['to'] ?? ''),
        'group' => in_array($q['group'] ?? '', ['day', 'week', 'month'], true) ? $q['group'] : 'auto',
        'status' => trim((string)($q['status'] ?? '')), 'category' => trim((string)($q['category'] ?? '')),
        'project' => trim((string)($q['project'] ?? '')), 'team' => trim((string)($q['team'] ?? '')),
        'student' => trim((string)($q['student'] ?? '')), 'band' => in_array($q['band'] ?? '', ['low', 'mid', 'high'], true) ? $q['band'] : '',
        'rating' => in_array((string)($q['rating'] ?? ''), ['3', '4', '5'], true) ? (int)$q['rating'] : 0,
        'cols' => $cols ?: array_keys(RP_COLUMNS[$type]),
        'sort' => $sort, 'dir' => ($q['dir'] ?? '') === 'asc' ? 'asc' : 'desc',
        'inc' => $inc ?: ['table'],
    ];
}

// Start / end date of the chosen period
function rpRange(array $p, array $rows, string $field): array
{
    $today = date('Y-m-d');
    switch ($p['period']) {
        case '7d':    return [date('Y-m-d', strtotime('-6 days')), $today];
        case '30d':   return [date('Y-m-d', strtotime('-29 days')), $today];
        case 'month': return [date('Y-m-01'), $today];
        case '3m':    return [date('Y-m-d', strtotime('-3 months +1 day')), $today];
        case '6m':    return [date('Y-m-d', strtotime('-6 months +1 day')), $today];
        case 'year':  return [date('Y-01-01'), $today];
        case 'custom':
            $from = $p['from'] ?: date('Y-m-d', strtotime('-29 days'));
            $to   = $p['to'] ?: $today;
            return $from <= $to ? [$from, $to] : [$to, $from];
        default: // all time
            $dates = array_filter(array_column($rows, $field));
            return [$dates ? min($dates) : date('Y-m-d', strtotime('-29 days')), max($today, $dates ? max($dates) : $today)];
    }
}

// ===================================================================
// 3. BUILD THE REPORT
// ===================================================================
function rpBuild(array $data, array $p): array
{
    $type  = $p['type'];
    $field = RP_DATE_FIELD[$type];
    $all   = $data[$type];

    // status label for display
    foreach ($all as &$r) {
        if (isset($r['status_key'])) $r['status'] = RP_STATUS[$type][$r['status_key']] ?? ucfirst($r['status_key']);
        if ($type === 'teams') {
            $r['days_left'] = $r['status_key'] === 'completed' || !$r['deadline'] ? null
                : (int)floor((strtotime($r['deadline']) - strtotime('today')) / 86400);
        }
    }
    unset($r);

    // options for the dropdowns (from ALL rows of this type, before filtering)
    $opts = [
        'category' => rpUnique(array_column($all, 'category')),
        'project'  => rpUnique(array_column($all, $type === 'projects' ? 'title' : 'project')),
        'team'     => rpUnique(array_filter(array_column($all, 'team'), fn($t) => $t !== 'No team' && $t !== '-')),
        'student'  => rpUnique(array_column($all, 'student')),
        'status'   => RP_STATUS[$type] ?? [],
    ];

    [$from, $to] = rpRange($p, $all, $field);

    // ---------- filters ----------
    $rows = array_values(array_filter($all, function ($r) use ($p, $type, $field, $from, $to) {
        $date = $r[$field] ?? '';
        if ($date === '' || $date < $from || $date > $to) return false;
        if ($p['category'] !== '' && $r['category'] !== $p['category']) return false;
        if ($p['project'] !== '' && ($type === 'projects' ? $r['title'] : ($r['project'] ?? '')) !== $p['project']) return false;
        if ($p['team'] !== '' && in_array($type, ['teams', 'individual', 'feedback'], true) && $r['team'] !== $p['team']) return false;
        if ($p['student'] !== '' && in_array($type, ['individual', 'proposals'], true) && $r['student'] !== $p['student']) return false;
        if ($p['status'] !== '') {
            $val = $type === 'individual' ? $r['work'] : ($r['status_key'] ?? '');
            if ($val !== $p['status']) return false;
        }
        if ($p['band'] !== '' && isset($r['progress'])) {
            $pr = $r['progress'];
            if ($p['band'] === 'low' && $pr >= 50) return false;
            if ($p['band'] === 'mid' && ($pr < 50 || $pr >= 80)) return false;
            if ($p['band'] === 'high' && $pr < 80) return false;
        }
        if ($p['rating'] && $type === 'feedback' && $r['rating'] < $p['rating']) return false;
        return true;
    }));

    // ---------- sort ----------
    $s = $p['sort']; $dir = $p['dir'] === 'asc' ? 1 : -1;
    usort($rows, function ($a, $b) use ($s, $dir) {
        $x = $a[$s] ?? ''; $y = $b[$s] ?? '';
        if ($x === null) $x = PHP_INT_MAX; if ($y === null) $y = PHP_INT_MAX;
        return $dir * (is_numeric($x) && is_numeric($y) ? $x <=> $y : strcasecmp((string)$x, (string)$y));
    });

    // ---------- time grouping for the trend chart ----------
    $days  = (int)round((strtotime($to) - strtotime($from)) / 86400) + 1;
    $group = $p['group'] !== 'auto' ? $p['group'] : ($days <= 31 ? 'day' : ($days <= 120 ? 'week' : 'month'));
    $buckets = rpBuckets($from, $to, $group);
    foreach ($rows as $r) {
        $k = rpBucketKey($r[$field], $group);
        if (isset($buckets[$k])) $buckets[$k]['count']++;
    }

    $report = [
        'type' => $type, 'type_label' => RP_TYPES[$type]['label'],
        'from' => $from, 'to' => $to, 'group' => $group,
        'period_text' => date('M d, Y', strtotime($from)) . ' – ' . date('M d, Y', strtotime($to)),
        'rows' => $rows, 'total_rows' => count($rows),
        'columns' => array_intersect_key(RP_COLUMNS[$type], array_flip($p['cols'])),
        'options' => $opts,
    ];

    $trendLabel = ['projects' => 'Projects posted', 'teams' => 'Teams created', 'individual' => 'Students joined',
                   'proposals' => 'Proposals received', 'feedback' => 'Feedback given'][$type];
    $trend = ['id' => 'trend', 'title' => $trendLabel . ' per ' . $group, 'type' => 'line',
              'labels' => array_column($buckets, 'label'), 'series' => [['label' => $trendLabel, 'data' => array_column($buckets, 'count')]]];

    [$report['cards'], $report['charts']] = rpCardsAndCharts($type, $rows);
    array_unshift($report['charts'], $trend);

    // filter summary shown on the page, in the PDF and in the CSV
    $f = ['Period' => RP_PERIODS[$p['period']] . ' (' . $report['period_text'] . ')'];
    if ($p['category'] !== '') $f['Category'] = $p['category'];
    if ($p['project'] !== '')  $f['Project'] = $p['project'];
    if ($p['team'] !== '' && in_array($type, ['teams', 'individual', 'feedback'], true)) $f['Team'] = $p['team'];
    if ($p['student'] !== '' && in_array($type, ['individual', 'proposals'], true)) $f['Student'] = $p['student'];
    if ($p['status'] !== '')   $f['Status'] = RP_STATUS[$type][$p['status']] ?? $p['status'];
    if ($p['band'] !== '')     $f['Progress'] = ['low' => 'Below 50%', 'mid' => '50% – 79%', 'high' => '80% and above'][$p['band']];
    if ($p['rating'])          $f['Rating'] = $p['rating'] . ' stars' . ($p['rating'] < 5 ? ' and above' : '');
    $report['filters'] = $f;

    return $report;
}

function rpUnique(array $v): array
{
    $v = array_values(array_unique(array_filter(array_map('strval', $v), fn($x) => $x !== '')));
    natcasesort($v);
    return array_values($v);
}

function rpBucketKey(string $date, string $group): string
{
    if ($group === 'month') return substr($date, 0, 7);
    if ($group === 'week')  return date('o-\WW', strtotime($date));
    return $date;
}

function rpBuckets(string $from, string $to, string $group): array
{
    $out = [];
    $t = strtotime($from); $end = strtotime($to);
    $guard = 0;
    while ($t <= $end && $guard++ < 4000) {
        $k = rpBucketKey(date('Y-m-d', $t), $group);
        if (!isset($out[$k])) {
            $label = $group === 'month' ? date('M Y', $t)
                   : ($group === 'week' ? 'Wk of ' . date('M d', strtotime('monday this week', $t)) : date('M d', $t));
            $out[$k] = ['label' => $label, 'count' => 0];
        }
        $t = strtotime('+1 day', $t);
    }
    return $out;
}

function rpCount(array $rows, string $key, array $labels = []): array
{
    $c = [];
    foreach ($labels as $k => $l) $c[$l] = 0;
    foreach ($rows as $r) { $l = $labels[$r[$key]] ?? $r[$key]; $c[$l] = ($c[$l] ?? 0) + 1; }
    return array_filter($c, fn($n) => $n > 0);
}

function rpAvg(array $rows, string $key, int $dec = 0): float
{
    return $rows ? round(array_sum(array_column($rows, $key)) / count($rows), $dec) : 0;
}

// Stat cards (4) + charts for each report type
function rpCardsAndCharts(string $type, array $rows): array
{
    $n = count($rows);
    $bar = fn($id, $title, $labels, $data, $label, $opts = []) =>
        array_merge(['id' => $id, 'title' => $title, 'type' => 'bar', 'labels' => array_values($labels), 'series' => [['label' => $label, 'data' => array_values($data)]]], $opts);
    $pie = fn($id, $title, $counts) =>
        ['id' => $id, 'title' => $title, 'type' => 'doughnut', 'labels' => array_keys($counts), 'series' => [['label' => $title, 'data' => array_values($counts)]]];
    $top = function ($rows, $label, $value, $max = 12) {
        usort($rows, fn($a, $b) => $b[$value] <=> $a[$value]);
        $rows = array_slice($rows, 0, $max);
        return [array_column($rows, $label), array_column($rows, $value)];
    };

    switch ($type) {
        case 'projects':
            $active = count(array_filter($rows, fn($r) => in_array($r['status_key'], ['open', 'reviewing', 'published', 'inprogress'], true)));
            $props  = array_sum(array_column($rows, 'proposals'));
            [$l, $v] = $top($rows, 'title', 'progress');
            $cats = rpCount($rows, 'category');
            return [[
                ['icon' => 'folder_open', 'tone' => 'navy',  'label' => 'Projects',          'value' => $n, 'sub' => $active . ' open or active'],
                ['icon' => 'description', 'tone' => 'blue',  'label' => 'Proposals received', 'value' => $props, 'sub' => array_sum(array_column($rows, 'accepted')) . ' accepted'],
                ['icon' => 'school',      'tone' => 'sky',   'label' => 'Students working',   'value' => array_sum(array_column($rows, 'students')), 'sub' => array_sum(array_column($rows, 'teams')) . ' teams'],
                ['icon' => 'trending_up', 'tone' => 'green', 'label' => 'Average progress',   'value' => rpAvg(array_filter($rows, fn($r) => $r['students'] > 0), 'progress') . '%', 'sub' => 'projects with students'],
            ], [
                $pie('status', 'Projects by status', rpCount($rows, 'status')),
                $bar('category', 'Projects by category', array_keys($cats), $cats, 'Projects'),
                $bar('progress', 'Progress by project (%)', $l, $v, 'Progress', ['horizontal' => true, 'max' => 100]),
            ]];

        case 'teams':
            $cnt = rpCount($rows, 'status');
            [$l, $v] = $top($rows, 'team', 'progress');
            [$ml, $mv] = $top($rows, 'team', 'members');
            return [[
                ['icon' => 'groups',       'tone' => 'navy',  'label' => 'Teams',           'value' => $n, 'sub' => array_sum(array_column($rows, 'members')) . ' students'],
                ['icon' => 'check_circle', 'tone' => 'green', 'label' => 'On track',        'value' => $cnt['On Track'] ?? 0, 'sub' => ($cnt['Completed'] ?? 0) . ' completed'],
                ['icon' => 'warning',      'tone' => 'red',   'label' => 'Behind schedule', 'value' => $cnt['Behind Schedule'] ?? 0, 'sub' => 'need attention'],
                ['icon' => 'trending_up',  'tone' => 'blue',  'label' => 'Average progress','value' => rpAvg($rows, 'progress') . '%', 'sub' => 'across these teams'],
            ], [
                $pie('status', 'Teams by status', $cnt),
                $bar('progress', 'Progress by team (%)', $l, $v, 'Progress', ['horizontal' => true, 'max' => 100]),
                $bar('members', 'Members per team', $ml, $mv, 'Members'),
            ]];

        case 'individual':
            $low = count(array_filter($rows, fn($r) => $r['progress'] < 50));
            [$l, $v] = $top($rows, 'student', 'progress', 15);
            $uni = rpCount($rows, 'university');
            $bands = ['Below 50%' => 0, '50% – 79%' => 0, '80% and above' => 0];
            foreach ($rows as $r) $bands[$r['progress'] < 50 ? 'Below 50%' : ($r['progress'] < 80 ? '50% – 79%' : '80% and above')]++;
            return [[
                ['icon' => 'person',       'tone' => 'navy',  'label' => 'Students',          'value' => $n, 'sub' => count(array_unique(array_column($rows, 'team'))) . ' teams'],
                ['icon' => 'trending_up',  'tone' => 'blue',  'label' => 'Average progress',  'value' => rpAvg($rows, 'progress') . '%', 'sub' => 'per student'],
                ['icon' => 'task_alt',     'tone' => 'green', 'label' => 'Finished their work','value' => count(array_filter($rows, fn($r) => $r['work'] === 'Completed')), 'sub' => 'marked completed'],
                ['icon' => 'support',      'tone' => 'red',   'label' => 'Need support',      'value' => $low, 'sub' => 'below 50% progress'],
            ], [
                $bar('progress', 'Progress by student (%)', $l, $v, 'Progress', ['horizontal' => true, 'max' => 100]),
                $pie('bands', 'Students by progress', array_filter($bands)),
                $bar('university', 'Students by university', array_keys($uni), $uni, 'Students'),
            ]];

        case 'proposals':
            $cnt = rpCount($rows, 'status');
            $decided = ($cnt['Accepted'] ?? 0) + ($cnt['Rejected'] ?? 0);
            $per = rpCount($rows, 'project');
            arsort($per); $per = array_slice($per, 0, 12, true);
            return [[
                ['icon' => 'description',  'tone' => 'navy',  'label' => 'Proposals',       'value' => $n, 'sub' => count(array_unique(array_column($rows, 'project'))) . ' projects'],
                ['icon' => 'hourglass_top','tone' => 'sky',   'label' => 'Waiting for review','value' => $cnt['Pending'] ?? 0, 'sub' => 'pending'],
                ['icon' => 'how_to_reg',   'tone' => 'green', 'label' => 'Accepted',        'value' => $cnt['Accepted'] ?? 0, 'sub' => ($cnt['Rejected'] ?? 0) . ' rejected'],
                ['icon' => 'percent',      'tone' => 'blue',  'label' => 'Acceptance rate', 'value' => ($decided ? round(($cnt['Accepted'] ?? 0) / $decided * 100) : 0) . '%', 'sub' => 'of reviewed proposals'],
            ], [
                $pie('status', 'Proposals by status', $cnt),
                $bar('project', 'Proposals per project', array_keys($per), $per, 'Proposals', ['horizontal' => true]),
            ]];

        default: // feedback
            $dist = ['1 star' => 0, '2 stars' => 0, '3 stars' => 0, '4 stars' => 0, '5 stars' => 0];
            foreach ($rows as $r) $dist[$r['rating'] . ($r['rating'] === 1 ? ' star' : ' stars')]++;
            $skills = ['Technical' => rpAvg($rows, 'technical', 1), 'Communication' => rpAvg($rows, 'communication', 1),
                       'Teamwork' => rpAvg($rows, 'teamwork', 1), 'Problem-solving' => rpAvg($rows, 'problem', 1)];
            [$l, $v] = $top($rows, 'team', 'rating');
            return [[
                ['icon' => 'reviews', 'tone' => 'navy',  'label' => 'Feedback given', 'value' => $n, 'sub' => count(array_unique(array_column($rows, 'team'))) . ' teams'],
                ['icon' => 'star',    'tone' => 'green', 'label' => 'Average rating', 'value' => rpAvg($rows, 'rating', 1) . ' / 5', 'sub' => 'overall'],
                ['icon' => 'workspace_premium', 'tone' => 'blue', 'label' => '5-star feedback', 'value' => $dist['5 stars'], 'sub' => 'exceptional work'],
                ['icon' => 'psychology', 'tone' => 'sky', 'label' => 'Strongest skill', 'value' => $rows ? array_search(max($skills), $skills) : '-', 'sub' => $rows ? max($skills) . ' / 5 average' : ''],
            ], [
                $bar('rating', 'Rating distribution', array_keys($dist), $dist, 'Feedback'),
                $bar('skills', 'Average score by skill', array_keys($skills), $skills, 'Score', ['max' => 5]),
                $bar('teams', 'Rating by team', $l, $v, 'Rating', ['horizontal' => true, 'max' => 5]),
            ]];
    }
}

// Text shown in the table / CSV / PDF for one cell
function rpCell(string $col, $v): string
{
    if ($v === null) return $col === 'days_left' ? '-' : '';
    switch ($col) {
        case 'progress': return (int)$v . '%';
        case 'posted': case 'deadline': case 'created': case 'joined': case 'submitted': case 'date':
            return $v ? date('M d, Y', strtotime($v)) : '-';
        case 'days_left':
            if (!is_numeric($v)) return '-';          // completed teams / no deadline
            $v = (int)$v;
            return $v >= 0 ? $v . ' day' . ($v === 1 ? '' : 's') . ' left' : abs($v) . ' days late';
        case 'rating': return $v . ' / 5';
        case 'technical': case 'communication': case 'teamwork': case 'problem': return number_format((float)$v, 1);
        default: return (string)$v;
    }
}