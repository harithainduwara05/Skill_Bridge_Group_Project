<?php
/*
| Organization backend - Feedback: give feedback to a project team + list the feedback given
| Used by: Functions/Organization/feedback.php
| (the page already loaded Config/db.php + Session/Session.php and checked the role)
|
| Rules
|  - Feedback is given to a TEAM (org_teams) that is COMPLETED - the organization accepted
|    the team's final report (Teams page) - and the team has members.
|  - The whole team always sees the feedback.
|  - ONE feedback per team (project_feedback.team_id is UNIQUE), and it is FINAL once submitted (it can not be edited),
|    like Upwork / Fiverr: so a rating can't be changed later to affect a student unfairly.
|  - Every member of the team gets a notification when the feedback is given.
| Tables: project_feedback (one row per team) + feedback_improvements (one row per area to improve)
*/

$user = current_user();
$organization_email = $user['email'];

function fbFetch(mysqli $conn, string $sql, string $types = '', array $params = []): array
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

// Members of a team (name + role in the team)
function fbMembers(mysqli $conn, int $teamId): array
{
    return fbFetch($conn,
        "SELECT s.Name AS name, m.role
         FROM org_team_members m JOIN student s ON s.Email = m.Email
         WHERE m.team_id = ? ORDER BY s.Name", "i", [$teamId]);
}

// ---------------------------------------------------------------
// All teams of this organization's projects + team size + feedback (if any)
// ---------------------------------------------------------------
$orgTeams = fbFetch($conn,
    "SELECT t.id AS team_id, t.name AS team_name, p.id AS project_id, p.title, c.category_name AS category,
            t.status AS team_status,
            (SELECT COUNT(*) FROM org_team_members m WHERE m.team_id = t.id) AS team_size,
            (SELECT f.id FROM project_feedback f WHERE f.team_id = t.id LIMIT 1) AS feedback_id
     FROM org_teams t
     JOIN projects p ON p.id = t.project_id
     LEFT JOIN project_categories c ON c.category_id = p.category_id
     WHERE p.organization_email = ?
     ORDER BY t.created_at DESC", "s", [$organization_email]);

// Teams that can get NEW feedback (team Completed, team has members, no feedback yet)
// 'id' = the team id (sent by the form as team_id)
$fbProjects = [];
foreach ($orgTeams as $tm) {
    if ($tm['team_status'] !== 'completed' || (int)$tm['team_size'] === 0 || $tm['feedback_id']) continue;

    $fbProjects[] = [
        'id' => (int)$tm['team_id'], 'project_id' => (int)$tm['project_id'],
        'title' => $tm['title'], 'category' => $tm['category'],
        'status' => 'completed',
        'team' => $tm['team_name'],
        'students' => fbMembers($conn, (int)$tm['team_id']),
    ];
}

// ---------------------------------------------------------------
// Read + check the form
// returns [data, errorCode]  (errorCode '' = everything is fine)
// ---------------------------------------------------------------
function fbReadForm(): array
{
    $rating = (int)($_POST['rating'] ?? 0);

    $scores = [];
    foreach (['technical', 'communication', 'teamwork', 'problem_solving'] as $k) {
        $scores[$k] = round((float)($_POST[$k] ?? 0) * 2) / 2;      // steps of 0.5
    }

    $summary = trim($_POST['summary'] ?? '');

    // improvements: unique, max 8, each max 40 characters
    $improvements = [];
    foreach (explode(',', $_POST['improvements'] ?? '') as $imp) {
        $imp = mb_substr(trim(preg_replace('/\s+/', ' ', $imp)), 0, 40);
        if ($imp === '' || in_array(mb_strtolower($imp), array_map('mb_strtolower', $improvements), true)) continue;
        if (count($improvements) >= 8 || mb_strlen(implode(', ', array_merge($improvements, [$imp]))) > 500) break;
        $improvements[] = $imp;
    }

    $error = '';
    if ($rating < 1 || $rating > 5)                                                       $error = 'rating';
    elseif (count(array_filter($scores, fn($v) => $v >= 1 && $v <= 5)) !== 4)            $error = 'scores';
    elseif (mb_strlen($summary) < 20)                                                    $error = 'summary_short';
    elseif (mb_strlen($summary) > 1500)                                                  $error = 'summary_long';

    return [[
        'rating' => $rating, 'scores' => $scores, 'summary' => $summary,
        'improvements' => $improvements,       // saved one per row in feedback_improvements
    ], $error];
}

// Send a notification to every member of the team
function fbNotifyTeam(mysqli $conn, int $teamId, string $title, string $message): void
{
    $n = $conn->prepare("INSERT INTO notifications (Email, title, message, type, status)
                         SELECT Email, ?, ?, 'project', 'Unread' FROM org_team_members WHERE team_id = ?");
    $n->bind_param("ssi", $title, $message, $teamId);
    $n->execute();
}

$orgName = fbFetch($conn, "SELECT Name FROM organization WHERE Email = ?", "s", [$organization_email])[0]['Name'] ?? 'The organization';

// ---------------------------------------------------------------
// Save NEW feedback
// ---------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'submit_feedback') {
    $teamId = (int)($_POST['team_id'] ?? 0);

    $project = null;
    foreach ($fbProjects as $fp) { if ($fp['id'] === $teamId) { $project = $fp; break; } }
    if (!$project) {
        // not this organization's team, team not Completed, no members, or already has feedback
        header("Location: feedback.php?fb_error=project");
        exit;
    }

    [$d, $error] = fbReadForm();
    if ($error !== '') {
        header("Location: feedback.php?fb_error=" . $error);
        exit;
    }

    try {
        $conn->begin_transaction();
        $q = $conn->prepare("INSERT INTO project_feedback
            (team_id, rating, technical, communication, teamwork, problem_solving, summary)
            VALUES (?,?,?,?,?,?,?)");
        $q->bind_param("iidddds", $teamId, $d['rating'],
            $d['scores']['technical'], $d['scores']['communication'], $d['scores']['teamwork'], $d['scores']['problem_solving'],
            $d['summary']);
        $q->execute();
        $feedbackId = (int)$conn->insert_id;

        // areas to improve: one row each
        $iq = $conn->prepare("INSERT INTO feedback_improvements (feedback_id, area) VALUES (?, ?)");
        foreach ($d['improvements'] as $area) {
            $iq->bind_param("is", $feedbackId, $area);
            $iq->execute();
        }
        $conn->commit();

        fbNotifyTeam($conn, $teamId, 'New Project Feedback',
            $orgName . ' gave your team ' . $d['rating'] . '/5 for "' . $project['title'] . '". Open your project to read the full feedback.');

        header("Location: feedback.php?fb_saved=1");
    } catch (Throwable $e) {
        try { $conn->rollback(); } catch (Throwable $ignore) {}
        header("Location: feedback.php?fb_error=save");
    }
    exit;
}

// ---------------------------------------------------------------
// Messages after saving
// ---------------------------------------------------------------
$fbErrors = [
    'project'       => 'This team can not get feedback. Choose a Completed team (final report accepted) that has no feedback yet.',
    'rating'        => 'Please give an overall rating from 1 to 5 stars.',
    'scores'        => 'Each competency score must be between 1 and 5.',
    'summary_short' => 'The performance summary must be at least 20 characters.',
    'summary_long'  => 'The performance summary can be at most 1500 characters.',
    'save'          => 'Something went wrong while saving. Please try again.',
];
$flash = null;
if (isset($_GET['fb_saved']))   $flash = ['type' => 'success', 'title' => 'Feedback submitted', 'message' => 'Your feedback was saved and the team has been notified.'];
if (isset($_GET['fb_error']))   $flash = ['type' => 'error', 'title' => 'Could not save feedback',
                                          'message' => $fbErrors[$_GET['fb_error']] ?? 'Please fill all required fields and try again.'];

// ---------------------------------------------------------------
// Feedback given by this organization (from the database)
// ---------------------------------------------------------------
$catIcon = ['Web Development' => 'language', 'Mobile Development' => 'smartphone', 'AI / Machine Learning' => 'psychology',
            'Data Science' => 'monitoring', 'UI/UX Design' => 'palette', 'Cloud & DevOps' => 'cloud', 'Cybersecurity' => 'shield'];
$reviews = [];
foreach (fbFetch($conn,
    "SELECT f.*, t.name AS team_name, t.project_id, p.title, c.category_name AS category,
            (SELECT GROUP_CONCAT(i.area SEPARATOR ',') FROM feedback_improvements i WHERE i.feedback_id = f.id) AS improvements
     FROM project_feedback f
     JOIN org_teams t ON t.id = f.team_id
     JOIN projects p ON p.id = t.project_id
     LEFT JOIN project_categories c ON c.category_id = p.category_id
     WHERE p.organization_email = ? ORDER BY f.created_at DESC", "s", [$organization_email]) as $f) {
    $created = strtotime($f['created_at']);
    $reviews[] = [
        'icon' => $catIcon[$f['category']] ?? 'folder', 'title' => $f['title'], 'team' => $f['team_name'],
        'days_ago' => max(0, (int)floor((time() - $created) / 86400)),
        'created' => $created,
        'rating' => (int)$f['rating'],
        'skills' => ['Technical Skills' => (float)$f['technical'], 'Communication' => (float)$f['communication'],
                     'Teamwork' => (float)$f['teamwork'], 'Problem-solving' => (float)$f['problem_solving']],
        'summary' => $f['summary'],
        'improvements' => array_values(array_filter(array_map('trim', explode(',', $f['improvements'] ?? '')))),
        'is_new' => (time() - $created) < 7 * 86400,          // "New" badge for the first week
        'id' => (int)$f['id'], 'key' => 'db-' . (int)$f['id'], 'project_id' => (int)$f['project_id'],
        'category' => $f['category'],
        'members' => fbMembers($conn, (int)$f['team_id']),
    ];
}

// ---------------------------------------------------------------
// Stat cards (real numbers)
//   Pending Reviews    = Completed teams (with members) that have no feedback yet
//   Completed Projects = this organization's Completed projects
// ---------------------------------------------------------------
$pending = 0;
foreach ($orgTeams as $tm) {
    if ($tm['team_status'] === 'completed' && (int)$tm['team_size'] > 0 && !$tm['feedback_id']) $pending++;
}
$completed = (int)(fbFetch($conn, "SELECT COUNT(*) AS n FROM projects WHERE organization_email = ? AND status = 'completed'",
                           "s", [$organization_email])[0]['n'] ?? 0);
$summary = [
    'submitted' => count($reviews),
    'pending'   => $pending,
    'avg'       => $reviews ? round(array_sum(array_column($reviews, 'rating')) / count($reviews), 1) : 0,
    'completed' => $completed,
];

$ratingLabels = [5 => 'Exceptional', 4 => 'Very Good', 3 => 'Good', 2 => 'Fair', 1 => 'Needs Work'];

// star icons: full / half / empty
function fbStars($rating, $size = 20) {
    $html = '';
    for ($i = 1; $i <= 5; $i++) {
        if ($rating >= $i)            { $html .= '<span class="material-symbols-outlined fb-star on" style="font-size:' . $size . 'px;">star</span>'; }
        elseif ($rating >= $i - 0.5)  { $html .= '<span class="material-symbols-outlined fb-star on" style="font-size:' . $size . 'px;">star_half</span>'; }
        else                          { $html .= '<span class="material-symbols-outlined fb-star" style="font-size:' . $size . 'px;">star</span>'; }
    }
    return $html;
}