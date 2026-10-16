<?php
/*
| Organization backend - Teams: create / edit team, team status, extend deadline (+ demo teams)
| Used by: Functions/Organization/teams.php
| (the page already loaded Config/db.php + Session/Session.php and checked the role)
*/

$user = current_user();
$organization_email = $user['email'];

require_once __DIR__ . "/team_schema.php";
tmEnsureSchema($conn);

function tmFetch(mysqli $conn, string $sql, string $types = '', array $params = []): array
{
    try {
        $st = $conn->prepare($sql);
        if ($types !== '') $st->bind_param($types, ...$params);
        $st->execute();
        $res = $st->get_result();
        return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    } catch (Throwable $e) {
        return [];
    }
}

// ---------------------------------------------------------------
// Data for "Create New Team"
//   - only this organization's projects that can have a team
//     (Open / Reviewing / Active – not Draft, On Hold, Rejected or Completed)
//   - for each project, only the students whose PROPOSAL WAS ACCEPTED
//     (project_applications.status = 'accepted', set from Proposals page)
//   - a student who is already in a team of that project is shown but locked
// ---------------------------------------------------------------
$tmProjects = tmFetch($conn,
    "SELECT id, title, members, deadline, keywords
     FROM projects
     WHERE organization_email = ? AND status IN ('open', 'reviewing', 'inprogress')
     ORDER BY posted_at DESC", "s", [$organization_email]);

$tmCandidates = [];   // project_id => [ {email, name, sub, skills[], team, team_id} ]
foreach ($tmProjects as $pr) {
    $pid = (int)$pr['id'];

    // who is already in a team for this project
    $inTeam = [];
    foreach (tmFetch($conn,
        "SELECT LOWER(m.Email) AS email, t.id, t.name
         FROM org_team_members m JOIN org_teams t ON t.id = m.team_id
         WHERE t.project_id = ? AND t.organization_email = ?", "is", [$pid, $organization_email]) as $r) {
        $inTeam[$r['email']] = ['id' => (int)$r['id'], 'name' => $r['name']];
    }

    $tmCandidates[$pid] = [];
    $seen = [];
    foreach (tmFetch($conn,
        "SELECT s.Email AS email, s.Name AS name, s.University AS university, s.degree
         FROM project_applications pa
         JOIN student s ON LOWER(s.Email) = LOWER(pa.Email)
         WHERE pa.project_id = ? AND pa.status = 'accepted'
         ORDER BY s.Name", "i", [$pid]) as $r) {
        $key = strtolower($r['email']);
        if (isset($seen[$key])) continue;
        $seen[$key] = true;

        $skills = array_column(tmFetch($conn,
            "SELECT skill_name FROM skills WHERE Email = ? ORDER BY percentage DESC LIMIT 5", "s", [$r['email']]), 'skill_name');

        $tmCandidates[$pid][] = [
            'email'   => $key,
            'name'    => $r['name'],
            'sub'     => trim(preg_replace('/^B\.Sc\.\s*in\s*/i', '', (string)$r['degree']) . ($r['university'] ? ' · ' . $r['university'] : ''), ' ·'),
            'skills'  => $skills,
            'team'    => $inTeam[$key]['name'] ?? null,
            'team_id' => $inTeam[$key]['id'] ?? null,
        ];
    }
}

// ---------- helpers for create / edit ----------
function tmFindProject(array $projects, int $id): ?array
{
    foreach ($projects as $pr) { if ((int)$pr['id'] === $id) return $pr; }
    return null;
}

// "React, node.js ,React" -> ['React', 'node.js']   (max 15, each max 40 chars)
function tmCleanSkills(string $raw): array
{
    $out = [];
    foreach (explode(',', $raw) as $sk) {
        $sk = trim(preg_replace('/\s+/', ' ', $sk));
        if ($sk === '' || mb_strlen($sk) > 40) continue;
        $out[mb_strtolower($sk)] ??= $sk;   // keep the first spelling
    }
    return array_slice(array_values($out), 0, 15);
}

// roles[email] = role  ->  only for the chosen members, trimmed
function tmCleanRoles(array $members, $rawRoles): array
{
    $rawRoles = is_array($rawRoles) ? array_change_key_case($rawRoles, CASE_LOWER) : [];
    $roles = [];
    foreach ($members as $m) { $roles[$m] = trim(preg_replace('/\s+/', ' ', (string)($rawRoles[$m] ?? ''))); }
    return $roles;
}

// Team deadline: required, not in the past, not after the project deadline
function tmCheckDeadline(string $deadline, ?array $project): ?string
{
    if ($deadline === '' || !strtotime($deadline)) return null;
    $dl = date('Y-m-d', strtotime($deadline));
    if ($dl < date('Y-m-d')) return null;
    if ($project && !empty($project['deadline']) && strtotime($project['deadline'])
        && $dl > date('Y-m-d', strtotime($project['deadline']))) return null;
    return $dl;
}

// Save members + roles, and keep student_projects in sync so the
// students see the project and their role in their own dashboard.
function tmSaveMembers(mysqli $conn, int $teamId, int $projectId, array $roles, array $oldMembers = []): void
{
    $d = $conn->prepare("DELETE FROM org_team_members WHERE team_id = ?");
    $d->bind_param("i", $teamId);
    $d->execute();

    $ins  = $conn->prepare("INSERT INTO org_team_members (team_id, Email, role) VALUES (?, ?, ?)");
    $find = $conn->prepare("SELECT student_project_id FROM student_projects WHERE LOWER(Email) = ? AND project_id = ? LIMIT 1");
    $upd  = $conn->prepare("UPDATE student_projects SET role = ? WHERE student_project_id = ?");
    $add  = $conn->prepare("INSERT INTO student_projects (Email, project_id, role, progress, status) VALUES (?, ?, ?, 0, 'In Progress')");

    foreach ($roles as $email => $role) {
        $ins->bind_param("iss", $teamId, $email, $role);
        $ins->execute();

        $find->bind_param("si", $email, $projectId);
        $find->execute();
        $row = $find->get_result()->fetch_assoc();
        if ($row) {
            $spId = (int)$row['student_project_id'];
            $upd->bind_param("si", $role, $spId);
            $upd->execute();
        } else {
            $add->bind_param("sis", $email, $projectId, $role);
            $add->execute();
        }
    }

    // students removed from the team (and not in another team of this project) leave the project
    $removed = array_diff($oldMembers, array_keys($roles));
    if ($removed) {
        $other = $conn->prepare("SELECT 1 FROM org_team_members m JOIN org_teams t ON t.id = m.team_id
                                 WHERE LOWER(m.Email) = ? AND t.project_id = ? LIMIT 1");
        $del = $conn->prepare("DELETE FROM student_projects WHERE LOWER(Email) = ? AND project_id = ?");
        foreach ($removed as $email) {
            $other->bind_param("si", $email, $projectId);
            $other->execute();
            if ($other->get_result()->fetch_row()) continue;
            $del->bind_param("si", $email, $projectId);
            $del->execute();
        }
    }
}

function tmNotify(mysqli $conn, array $emails, string $title, string $message): void
{
    $nq = $conn->prepare("INSERT INTO notifications (Email, title, message, type, status) VALUES (?, ?, ?, 'project', 'Unread')");
    foreach ($emails as $e) {
        $nq->bind_param("sss", $e, $title, $message);
        $nq->execute();
    }
}

// ---------------------------------------------------------------
// Save a new team
// ---------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create_team') {
    $name      = trim(preg_replace('/\s+/', ' ', $_POST['team_name'] ?? ''));
    $projectId = (int)($_POST['project_id'] ?? 0);
    $members   = array_values(array_unique(array_map(fn($m) => strtolower(trim($m)), (array)($_POST['members'] ?? []))));
    $leader    = strtolower(trim($_POST['leader'] ?? ''));
    $roles     = tmCleanRoles($members, $_POST['roles'] ?? []);
    $skills    = tmCleanSkills($_POST['skills'] ?? '');
    $project   = tmFindProject($tmProjects, $projectId);
    $deadline  = tmCheckDeadline(trim($_POST['deadline'] ?? ''), $project);

    // only accepted students who are not in another team of this project
    $allowed = [];
    foreach ($tmCandidates[$projectId] ?? [] as $c) { if (!$c['team_id']) $allowed[] = $c['email']; }

    $error = null;
    if ($name === '' || mb_strlen($name) > 100 || !$project)                              $error = '1';
    elseif (!$members || array_diff($members, $allowed))                                   $error = 'members';
    elseif (count($members) > max(1, (int)$project['members']))                            $error = 'members';
    elseif (!in_array($leader, $members, true))                                            $error = '1';
    elseif (in_array('', $roles, true) || max(array_map('mb_strlen', $roles)) > 50)        $error = 'roles';
    elseif (!$skills)                                                                      $error = 'skills';
    elseif (!$deadline)                                                                    $error = 'deadline';

    if ($error) { header("Location: teams.php?team_error=" . $error); exit; }

    try {
        $conn->begin_transaction();

        $skillText = implode(', ', $skills);
        $ins = $conn->prepare("INSERT INTO org_teams (organization_email, project_id, name, leader_email, skills, deadline, status)
                               VALUES (?, ?, ?, ?, ?, ?, 'ontrack')");
        $ins->bind_param("sissss", $organization_email, $projectId, $name, $leader, $skillText, $deadline);
        $ins->execute();
        $teamId = (int)$conn->insert_id;

        tmSaveMembers($conn, $teamId, $projectId, $roles);

        // tell every member which team they are in and what their role is
        $leaderName = $leader;
        foreach ($tmCandidates[$projectId] as $c) { if ($c['email'] === $leader) $leaderName = $c['name']; }
        $nq = $conn->prepare("INSERT INTO notifications (Email, title, message, type, status) VALUES (?, 'Added to a Team', ?, 'project', 'Unread')");
        foreach ($roles as $email => $role) {
            $msg = 'You were added to the team "' . $name . '" for the project "' . $project['title'] . '" as ' . $role
                 . ($email === $leader ? ' (Team Leader)' : '. Team leader: ' . $leaderName)
                 . '. Deadline: ' . date('M d, Y', strtotime($deadline)) . '.';
            $nq->bind_param("ss", $email, $msg);
            $nq->execute();
        }

        $conn->commit();
        header("Location: teams.php?team_created=1");
    } catch (Throwable $e) {
        try { $conn->rollback(); } catch (Throwable $ignore) {}
        // 1062 = duplicate team name for this organization
        header("Location: teams.php?team_error=" . (($e->getCode() == 1062) ? 'duplicate' : 'save'));
    }
    exit;
}

// ---------------------------------------------------------------
// ⋮ menu actions for teams saved in the database:
//   edit_team (name, deadline) | team_status | extend_deadline (Delay Review)
// ---------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && in_array($_POST['action'] ?? '', ['edit_team', 'team_status', 'extend_deadline'], true)) {
    $teamId = (int)($_POST['team_id'] ?? 0);
    $own = tmFetch($conn, "SELECT id, project_id, name, deadline FROM org_teams WHERE id = ? AND organization_email = ?",
                   "is", [$teamId, $organization_email])[0] ?? null;
    if (!$own) { header("Location: teams.php?team_error=1"); exit; }

    try {
        if ($_POST['action'] === 'team_status') {
            $st = $_POST['status'] ?? '';
            if (!in_array($st, ['ontrack', 'behind', 'completed'], true)) throw new Exception('bad status');
            $q = $conn->prepare("UPDATE org_teams SET status = ? WHERE id = ?");
            $q->bind_param("si", $st, $teamId);
            $q->execute();
            header("Location: teams.php?team_updated=status");
            exit;
        }

        if ($_POST['action'] === 'extend_deadline') {
            $newDl  = trim($_POST['deadline'] ?? '');
            $reason = trim($_POST['reason'] ?? '');
            if (!$newDl || !strtotime($newDl) || strtotime($newDl) <= strtotime('today') || mb_strlen($reason) < 5) {
                header("Location: teams.php?team_error=edit");
                exit;
            }
            $dl = date('Y-m-d', strtotime($newDl));
            $status = !empty($_POST['set_ontrack']) ? 'ontrack' : 'behind';
            $q = $conn->prepare("UPDATE org_teams SET deadline = ?, status = ? WHERE id = ?");
            $q->bind_param("ssi", $dl, $status, $teamId);
            $q->execute();

            // tell every member of the team
            $nTitle = 'Team Deadline Extended';
            $nMsg   = 'The deadline for ' . $own['name'] . ' was moved to ' . date('M d, Y', strtotime($dl)) . '. Reason: ' . mb_substr($reason, 0, 300);
            $nq = $conn->prepare("INSERT INTO notifications (Email, title, message, type, status)
                                  SELECT Email, ?, ?, 'project', 'Unread' FROM org_team_members WHERE team_id = ?");
            $nq->bind_param("ssi", $nTitle, $nMsg, $teamId);
            $nq->execute();

            header("Location: teams.php?team_updated=extended");
            exit;
        }

        // edit_team: only the team name and the team deadline can be changed here
        // (members, leader and skills are set when the team is created)
        $name     = trim(preg_replace('/\s+/', ' ', $_POST['team_name'] ?? ''));
        $pid      = (int)$own['project_id'];
        $project  = tmFindProject($tmProjects, $pid);
        $postedDl = trim($_POST['deadline'] ?? '');
        // keeping the old deadline is always fine (even if it has already passed)
        $deadline = (!empty($own['deadline']) && $postedDl !== '' && strtotime($postedDl)
                     && date('Y-m-d', strtotime($postedDl)) === date('Y-m-d', strtotime($own['deadline'])))
                    ? date('Y-m-d', strtotime($own['deadline']))
                    : tmCheckDeadline($postedDl, $project);

        if ($name === '' || mb_strlen($name) > 100 || !$deadline) {
            header("Location: teams.php?team_error=edit");
            exit;
        }

        $q = $conn->prepare("UPDATE org_teams SET name = ?, deadline = ? WHERE id = ?");
        $q->bind_param("ssi", $name, $deadline, $teamId);
        $q->execute();

        header("Location: teams.php?team_updated=edited");
    } catch (Throwable $e) {
        try { $conn->rollback(); } catch (Throwable $ignore) {}
        header("Location: teams.php?team_error=" . (($e->getCode() == 1062) ? 'duplicate' : 'edit'));
    }
    exit;
}

$flash = null;
$tmErrText = [
    'members'  => 'Only students with an accepted proposal (and not already in another team) can be added, up to the number the project needs.',
    'roles'    => 'Please give every team member a role (max 50 characters).',
    'skills'   => 'Please add at least one required skill.',
    'deadline' => 'Please pick a team deadline from today up to the project deadline.',
    'save'     => 'Something went wrong while saving. Please try again.',
];
$tmUpdated = [
    'status'  => ['Status updated', 'The team status was changed.'],
    'edited'  => ['Team updated', 'Your changes to the team were saved.'],
    'extended' => ['Deadline extended', 'The new deadline was saved and the team was notified.'],
];
if (isset($_GET['team_updated'], $tmUpdated[$_GET['team_updated']])) {
    $flash = ['type' => 'success', 'title' => $tmUpdated[$_GET['team_updated']][0], 'message' => $tmUpdated[$_GET['team_updated']][1]];
} elseif (($_GET['team_error'] ?? '') === 'edit') {
    $flash = ['type' => 'error', 'title' => 'Could not save changes', 'message' => 'Please check the team details and try again.'];
} elseif (isset($_GET['team_created'])) {
    $flash = ['type' => 'success', 'title' => 'Team created', 'message' => 'The new team was created successfully.'];
} elseif (($_GET['team_error'] ?? '') === 'duplicate') {
    $flash = ['type' => 'error', 'title' => 'Name already used', 'message' => 'You already have a team with this name. Please choose another name.'];
} elseif (isset($_GET['team_error'], $tmErrText[$_GET['team_error']])) {
    $flash = ['type' => 'error', 'title' => 'Could not create team', 'message' => $tmErrText[$_GET['team_error']]];
} elseif (isset($_GET['team_error'])) {
    $flash = ['type' => 'error', 'title' => 'Could not create team', 'message' => 'Please fill all required fields and try again.'];
}

// ---------------------------------------------------------------
// Teams created by this organization (from the database)
// ---------------------------------------------------------------
// Buttons at the bottom of a team card depend on the team status
$tmStateActions = [
    'ontrack'   => ['View', 'Task', 'Chat'],
    'behind'    => ['View', 'Review', 'Chat'],
    'completed' => ['Final Report'],
];

$dbTeams = [];
foreach (tmFetch($conn,
    "SELECT t.id, t.project_id, t.name, t.leader_email, t.skills AS team_skills, t.deadline, t.status, t.created_at,
            p.title AS project, p.keywords, p.company, p.category, p.deadline AS project_deadline, p.duration
     FROM org_teams t JOIN projects p ON p.id = t.project_id
     WHERE t.organization_email = ?
     ORDER BY t.created_at DESC", "s", [$organization_email]) as $row) {

    $memberRows = tmFetch($conn,
        "SELECT s.Email, s.Name, s.University, s.degree, s.year, COALESCE(NULLIF(m.role, ''), sp.role) AS role,
                sp.progress, sp.status AS work_status
         FROM org_team_members m
         JOIN student s ON LOWER(s.Email) = LOWER(m.Email)
         LEFT JOIN student_projects sp ON sp.Email = m.Email
              AND sp.project_id = (SELECT project_id FROM org_teams WHERE id = m.team_id)
         WHERE m.team_id = ?", "i", [(int)$row['id']]);

    $leaderName = $row['leader_email']; $leaderRole = 'Team Leader'; $others = []; $progressSum = 0;
    foreach ($memberRows as $mr) {
        $progressSum += (int)($mr['progress'] ?? 0);
        if (strtolower($mr['Email']) === strtolower($row['leader_email'])) {
            $leaderName = $mr['Name'];
            if (!empty($mr['role'])) $leaderRole = $mr['role'];
        } else {
            $others[] = $mr['Name'];
        }
    }
    $percent = $memberRows ? (int)round($progressSum / count($memberRows)) : 0;

    // Full details for the "View" popup
    $detailMembers = [];
    foreach ($memberRows as $mr) {
        $sk = tmFetch($conn, "SELECT skill_name FROM skills WHERE Email = ? ORDER BY percentage DESC LIMIT 4", "s", [$mr['Email']]);
        $isLeader = strtolower($mr['Email']) === strtolower($row['leader_email']);
        $detailMembers[] = [
            'name' => $mr['Name'], 'role' => $mr['role'] ?: ($isLeader ? 'Team Leader' : 'Team Member'),
            'leader' => $isLeader, 'email' => $mr['Email'],
            'university' => $mr['University'] ?? '', 'degree' => $mr['degree'] ?? '', 'year' => $mr['year'] ?? '',
            'skills' => array_column($sk, 'skill_name'),
            'progress' => (int)($mr['progress'] ?? 0), 'work' => $mr['work_status'] ?: 'Not started',
        ];
    }
    usort($detailMembers, fn($a, $b) => $b['leader'] <=> $a['leader']);   // leader first

    $time = 'No deadline'; $tone = 'ok'; $state = $row['status'] ?: 'ontrack';
    if (!empty($row['deadline'])) {
        $days = (int)floor((strtotime($row['deadline']) - strtotime('today')) / 86400);
        if ($days >= 0) { $time = $days . ' day' . ($days === 1 ? '' : 's') . ' left'; $tone = $days <= 14 ? 'warn' : 'ok'; }
        else            { $time = abs($days) . ' days delayed'; $tone = 'late'; }
    }
    $statusText = ['ontrack' => 'On Track', 'behind' => 'Behind Schedule', 'completed' => 'Completed'][$state] ?? 'On Track';

    $dbTeams[] = [
        'name' => $row['name'], 'state' => $state, 'status' => $statusText,
        'project' => $row['project'],
        'leader' => $leaderName, 'role' => $leaderRole,
        'members' => array_slice($others, 0, 3), 'more' => max(0, count($others) - 3),
        'skills' => array_values(array_filter(array_map('trim', explode(',', ($row['team_skills'] ?? '') !== '' ? $row['team_skills'] : ($row['keywords'] ?? ''))))),
        'phase' => $percent > 0 ? 'In Progress' : 'Phase 1: Planning', 'percent' => $percent,
        'deadline' => !empty($row['deadline']) ? date('M d', strtotime($row['deadline'])) : '',
        'time' => $time, 'tone' => $tone,
        'actions' => $tmStateActions[$state] ?? $tmStateActions['ontrack'],
        'is_new' => true,
        'id' => (int)$row['id'],
        'project_id' => (int)$row['project_id'],
        'leader_email' => strtolower($row['leader_email']),
        'member_emails' => array_map(fn($m) => strtolower($m['Email']), $memberRows),
        'deadline_raw' => !empty($row['deadline']) ? date('Y-m-d', strtotime($row['deadline'])) : '',
        'project_deadline_raw' => (!empty($row['project_deadline']) && strtotime($row['project_deadline'])) ? date('Y-m-d', strtotime($row['project_deadline'])) : '',
        'details' => [
            'company' => $row['company'], 'category' => $row['category'], 'duration' => $row['duration'],
            'project_deadline' => (!empty($row['project_deadline']) && strtotime($row['project_deadline'])) ? date('M d, Y', strtotime($row['project_deadline'])) : '',
            'team_deadline' => !empty($row['deadline']) ? date('M d, Y', strtotime($row['deadline'])) : '',
            'created' => date('M d, Y', strtotime($row['created_at'])),
            'members' => $detailMembers,
            'milestones' => [],
            'activity' => [['text' => 'Team created', 'when' => date('M d, Y', strtotime($row['created_at']))]],
        ],
    ];
}

// ---------------------------------------------------------------
// UI-ONLY DEMO DATA (kept for the demo). Teams from the database are shown first.
// state: ontrack | behind | completed      tone: ok | warn | late
// ---------------------------------------------------------------
$summary = [
    ['icon' => 'hub',           'class' => 'navy',  'label' => 'Total Teams',       'value' => 18 + 42],   // active + completed
    ['icon' => 'groups',        'class' => 'blue',  'label' => 'Active Teams',      'value' => 18],
    ['icon' => 'check_circle',  'class' => 'green', 'label' => 'Completed Teams',   'value' => 42],
    ['icon' => 'school',        'class' => 'sky',   'label' => 'Students Involved', 'value' => 156],
];

$demoTeams = [
    [
        'name' => 'Nexus Systems', 'state' => 'ontrack', 'status' => 'On Track',
        'project' => 'AI-Driven Supply Chain Optimizer',
        'leader' => 'Sanduni Perera', 'role' => 'Full Stack Lead',
        'members' => ['Ravindu Pathirana', 'Nimal Silva', 'Amaya Dissanayake'], 'more' => 2,
        'skills' => ['React.js', 'Python', 'TensorFlow', 'Figma'],
        'phase' => 'Phase 2: Prototyping', 'percent' => 72,
        'deadline' => 'Oct 24', 'time' => '14 days left', 'tone' => 'warn',
        'actions' => ['View', 'Task', 'Chat'],
    ],
    [
        'name' => 'Vortex Group', 'state' => 'behind', 'status' => 'Behind Schedule',
        'project' => 'Blockchain-Based Academic Credentials',
        'leader' => 'Malith Senanayake', 'role' => 'Security Architect',
        'members' => ['Kasun Madushanka', 'Tharushi Wijeratne'], 'more' => 1,
        'skills' => ['Solidity', 'Node.js', 'Cryptography'],
        'phase' => 'Phase 1: Smart Contract Design', 'percent' => 45,
        'deadline' => 'Oct 15', 'time' => '5 days delayed', 'tone' => 'late',
        'actions' => ['View', 'Review', 'Chat'],
    ],
    [
        'name' => 'Quantum Analytics', 'state' => 'completed', 'status' => 'Completed',
        'project' => 'Predictive Maintenance for Smart Cities',
        'leader' => 'Erandi Weerasinghe', 'role' => 'Data Analyst',
        'members' => ['Ishan Rathnayake', 'Dilini Herath'], 'more' => 3,
        'skills' => [],
        'phase' => 'All Milestones Met', 'percent' => 100,
        'deadline' => '', 'time' => 'Finished on Sep 18', 'tone' => 'ok',
        'actions' => ['Final Report'],
    ],
    [
        'name' => 'Alpha Ops', 'state' => 'ontrack', 'status' => 'On Track',
        'project' => 'Cloud Infrastructure Automation',
        'leader' => 'Kavinda Jayasuriya', 'role' => 'DevOps Engineer',
        'members' => ['Sahan Liyanage', 'Malsha Karunaratne'], 'more' => 1,
        'skills' => ['AWS', 'Docker', 'Kubernetes'],
        'phase' => 'Phase 3: Strategy Deployment', 'percent' => 85,
        'deadline' => 'Nov 02', 'time' => '22 days left', 'tone' => 'ok',
        'actions' => ['View', 'Task', 'Chat'],
    ],
];




// ---------------------------------------------------------------
// Full details for the demo teams ("View" popup)
// ---------------------------------------------------------------
function tmM($name, $role, $email, $uni, $degree, $year, $skills, $progress, $work, $leader = false) {
    return compact('name', 'role', 'email', 'skills', 'progress', 'work', 'leader') + ['university' => $uni, 'degree' => $degree, 'year' => $year];
}
$UOC = 'University of Colombo'; $UOM = 'University of Moratuwa'; $SLIIT = 'SLIIT'; $UOK = 'University of Kelaniya';

$demoDetails = [
    'Nexus Systems' => [
        'company' => 'SLIIT FOSS Community', 'category' => 'AI / Machine Learning', 'duration' => '12 Weeks',
        'project_deadline' => 'Oct 30, 2026', 'team_deadline' => 'Oct 24, 2026', 'created' => 'Aug 04, 2026',
        'members' => [
            tmM('Sanduni Perera', 'Full Stack Lead', '2023cs041@stu.ucsc.cmb.ac.lk', $UOC, 'B.Sc. in Computer Science', '3', ['React.js', 'Node.js', 'MySQL'], 78, 'In Progress', true),
            tmM('Ravindu Pathirana', 'Backend Developer', 'ravindu.p@uom.lk', $UOM, 'B.Sc. in IT', '3', ['Python', 'FastAPI', 'PostgreSQL'], 74, 'In Progress'),
            tmM('Nimal Silva', 'ML Engineer', 'nimal.s@uom.lk', $UOM, 'B.Sc. in Data Science', '4', ['TensorFlow', 'Pandas', 'Scikit-learn'], 69, 'In Progress'),
            tmM('Amaya Dissanayake', 'UI/UX Designer', 'it22104587@my.sliit.lk', $SLIIT, 'B.Sc. in Software Engineering', '3', ['Figma', 'User Research'], 90, 'In Progress'),
            tmM('Chamod Fernando', 'Data Engineer', '2023is018@stu.ucsc.cmb.ac.lk', $UOC, 'B.Sc. in Information Systems', '3', ['SQL', 'Airflow', 'Python'], 66, 'In Progress'),
            tmM('Ishara Gunawardena', 'QA Engineer', 'ishara.g@kln.ac.lk', $UOK, 'B.Sc. in Software Engineering', '2', ['Selenium', 'Jest'], 55, 'In Progress'),
        ],
        'milestones' => [
            ['title' => 'Phase 1: Requirements & Data Collection', 'done' => true],
            ['title' => 'Phase 2: Prototyping', 'done' => false, 'current' => true],
            ['title' => 'Phase 3: Model Training & Integration', 'done' => false],
            ['title' => 'Phase 4: Testing & Final Demo', 'done' => false],
        ],
        'activity' => [
            ['text' => 'Nimal Silva uploaded the demand-forecast model v2', 'when' => '2 hours ago'],
            ['text' => 'Amaya Dissanayake shared the dashboard prototype', 'when' => 'Yesterday'],
            ['text' => 'Phase 1 marked as completed', 'when' => 'Sep 12, 2026'],
        ],
    ],
    'Vortex Group' => [
        'company' => 'SLIIT FOSS Community', 'category' => 'Blockchain', 'duration' => '10 Weeks',
        'project_deadline' => 'Oct 20, 2026', 'team_deadline' => 'Oct 15, 2026', 'created' => 'Aug 11, 2026',
        'members' => [
            tmM('Malith Senanayake', 'Security Architect', '2023cs077@stu.ucsc.cmb.ac.lk', $UOC, 'B.Sc. in Computer Science', '4', ['Cryptography', 'Solidity'], 52, 'In Progress', true),
            tmM('Kasun Madushanka', 'Blockchain Developer', 'kasun.m@uom.lk', $UOM, 'B.Sc. in Computer Engineering', '3', ['Solidity', 'Hardhat', 'Ethers.js'], 40, 'In Progress'),
            tmM('Tharushi Wijeratne', 'Frontend Developer', 'it22098311@my.sliit.lk', $SLIIT, 'B.Sc. in IT', '3', ['React.js', 'Tailwind CSS'], 48, 'In Progress'),
            tmM('Dulaj Bandara', 'Backend Developer', 'dulaj.b@kln.ac.lk', $UOK, 'B.Sc. in Software Engineering', '3', ['Node.js', 'MongoDB'], 38, 'In Progress'),
        ],
        'milestones' => [
            ['title' => 'Phase 1: Smart Contract Design', 'done' => false, 'current' => true],
            ['title' => 'Phase 2: Credential Issuing Portal', 'done' => false],
            ['title' => 'Phase 3: Verification App & Audit', 'done' => false],
        ],
        'activity' => [
            ['text' => 'Deadline for Phase 1 was missed', 'when' => '5 days ago'],
            ['text' => 'Kasun Madushanka pushed contract tests', 'when' => '6 days ago'],
            ['text' => 'Team meeting: re-planned Phase 1 tasks', 'when' => 'Sep 18, 2026'],
        ],
    ],
    'Quantum Analytics' => [
        'company' => 'SLIIT FOSS Community', 'category' => 'Data Science', 'duration' => '8 Weeks',
        'project_deadline' => 'Sep 20, 2026', 'team_deadline' => 'Sep 18, 2026', 'created' => 'Jul 20, 2026',
        'members' => [
            tmM('Erandi Weerasinghe', 'Data Analyst', '2023is052@stu.ucsc.cmb.ac.lk', $UOC, 'B.Sc. in Information Systems', '4', ['Power BI', 'SQL', 'Python'], 100, 'Completed', true),
            tmM('Ishan Rathnayake', 'ML Engineer', 'ishan.r@uom.lk', $UOM, 'B.Sc. in Data Science', '4', ['XGBoost', 'Pandas'], 100, 'Completed'),
            tmM('Dilini Herath', 'IoT Developer', 'it21876540@my.sliit.lk', $SLIIT, 'B.Sc. in IT', '4', ['Arduino', 'MQTT'], 100, 'Completed'),
            tmM('Pasindu Kumara', 'Backend Developer', 'pasindu.k@kln.ac.lk', $UOK, 'B.Sc. in Software Engineering', '3', ['Java', 'Spring Boot'], 100, 'Completed'),
            tmM('Nethmi Samarasinghe', 'Data Visualization', '2023cs090@stu.ucsc.cmb.ac.lk', $UOC, 'B.Sc. in Computer Science', '3', ['D3.js', 'Chart.js'], 100, 'Completed'),
            tmM('Yasith Abeysekara', 'Technical Writer', 'yasith.a@uom.lk', $UOM, 'B.Sc. in IT', '3', ['Documentation', 'Markdown'], 100, 'Completed'),
        ],
        'milestones' => [
            ['title' => 'Sensor data pipeline', 'done' => true],
            ['title' => 'Failure prediction model', 'done' => true],
            ['title' => 'City maintenance dashboard', 'done' => true],
            ['title' => 'Final report & hand-over', 'done' => true],
        ],
        'activity' => [
            ['text' => 'Final report submitted', 'when' => 'Sep 18, 2026'],
            ['text' => 'Dashboard handed over to the client', 'when' => 'Sep 16, 2026'],
            ['text' => 'All milestones completed', 'when' => 'Sep 15, 2026'],
        ],
    ],
    'Alpha Ops' => [
        'company' => 'SLIIT FOSS Community', 'category' => 'Cloud & DevOps', 'duration' => '12 Weeks',
        'project_deadline' => 'Nov 10, 2026', 'team_deadline' => 'Nov 02, 2026', 'created' => 'Aug 01, 2026',
        'members' => [
            tmM('Kavinda Jayasuriya', 'DevOps Engineer', '2023cs012@stu.ucsc.cmb.ac.lk', $UOC, 'B.Sc. in Computer Science', '4', ['AWS', 'Terraform', 'Docker'], 88, 'In Progress', true),
            tmM('Sahan Liyanage', 'Cloud Engineer', 'sahan.l@uom.lk', $UOM, 'B.Sc. in Computer Engineering', '3', ['Kubernetes', 'Helm'], 84, 'In Progress'),
            tmM('Malsha Karunaratne', 'Automation Developer', 'it22031477@my.sliit.lk', $SLIIT, 'B.Sc. in IT', '3', ['Python', 'Ansible'], 86, 'In Progress'),
            tmM('Tharindu Wickramaratne', 'Monitoring & SRE', 'tharindu.w@kln.ac.lk', $UOK, 'B.Sc. in Software Engineering', '3', ['Grafana', 'Prometheus'], 80, 'In Progress'),
        ],
        'milestones' => [
            ['title' => 'Phase 1: Infrastructure as Code', 'done' => true],
            ['title' => 'Phase 2: CI/CD Pipelines', 'done' => true],
            ['title' => 'Phase 3: Strategy Deployment', 'done' => false, 'current' => true],
            ['title' => 'Phase 4: Monitoring & Hand-over', 'done' => false],
        ],
        'activity' => [
            ['text' => 'Kubernetes cluster moved to production', 'when' => '3 hours ago'],
            ['text' => 'Malsha Karunaratne automated nightly backups', 'when' => 'Yesterday'],
            ['text' => 'Phase 2 marked as completed', 'when' => 'Sep 20, 2026'],
        ],
    ],
];
foreach ($demoTeams as &$dt) { $dt['details'] = $demoDetails[$dt['name']] ?? null; }
unset($dt);

$teams = array_merge($dbTeams, $demoTeams);

// Summary cards also count the teams created from the database
$dbActive = count(array_filter($dbTeams, fn($t) => $t['state'] !== 'completed'));
$dbStudents = array_sum(array_map(fn($t) => 1 + count($t['members']) + $t['more'], $dbTeams));
$dbCompleted = count($dbTeams) - $dbActive;
$summary[0]['value'] += count($dbTeams);      // Total Teams
$summary[1]['value'] += $dbActive;            // Active Teams
$summary[2]['value'] += $dbCompleted;         // Completed Teams
$summary[3]['value'] += $dbStudents;          // Students Involved

function tmInitials($name) {
    $parts = preg_split('/\s+/', trim($name));
    $out = '';
    foreach (array_slice($parts, 0, 2) as $p) { $out .= mb_strtoupper(mb_substr($p, 0, 1)); }
    return $out;
}
$tmColors = ['#1e3a5f', '#0f766e', '#7c3aed', '#b45309', '#be185d', '#2563eb', '#475569'];
$tmActionIcon = ['View' => 'visibility', 'Task' => 'task_alt', 'Chat' => 'chat_bubble', 'Review' => 'rate_review', 'Final Report' => 'description'];