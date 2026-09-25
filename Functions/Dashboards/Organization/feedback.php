<?php

include "../../../Config/db.php";
include "../../../Session/Session.php";

require_role('organization');
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

// ---------------------------------------------------------------
// Projects that can get feedback = this organization's projects that
// already have students working on them. Team name comes from Teams
// (org_teams) when one exists, otherwise "<project> Team".
// ---------------------------------------------------------------
$fbProjects = [];
foreach (fbFetch($conn,
    "SELECT p.id, p.title, p.category, p.status
     FROM projects p
     WHERE p.organization_email = ?
       AND EXISTS (SELECT 1 FROM student_projects sp WHERE sp.project_id = p.id)
     ORDER BY p.posted_at DESC", "s", [$organization_email]) as $pr) {

    $team = fbFetch($conn, "SELECT name FROM org_teams WHERE project_id = ? AND organization_email = ? ORDER BY created_at DESC LIMIT 1",
                    "is", [(int)$pr['id'], $organization_email])[0]['name'] ?? ($pr['title'] . ' Team');
    $students = fbFetch($conn,
        "SELECT s.Name AS name, sp.role FROM student_projects sp JOIN student s ON s.Email = sp.Email WHERE sp.project_id = ?",
        "i", [(int)$pr['id']]);

    $fbProjects[] = [
        'id' => (int)$pr['id'], 'title' => $pr['title'], 'category' => $pr['category'],
        'status' => $pr['status'], 'team' => $team, 'students' => $students,
    ];
}

// ---------------------------------------------------------------
// Save new feedback
// ---------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'submit_feedback') {
    $pid     = (int)($_POST['project_id'] ?? 0);
    $rating  = (int)($_POST['rating'] ?? 0);
    $scores  = [];
    foreach (['technical', 'communication', 'teamwork', 'problem_solving'] as $k) {
        $scores[$k] = round((float)($_POST[$k] ?? 0) * 2) / 2;      // steps of 0.5
    }
    $summaryText  = trim($_POST['summary'] ?? '');
    $improvements = implode(', ', array_slice(array_filter(array_map('trim', explode(',', $_POST['improvements'] ?? ''))), 0, 8));
    $share        = !empty($_POST['share']) ? 1 : 0;

    $project = null;
    foreach ($fbProjects as $fp) { if ($fp['id'] === $pid) { $project = $fp; break; } }

    $scoresOk = count(array_filter($scores, fn($v) => $v >= 1 && $v <= 5)) === 4;
    if (!$project || $rating < 1 || $rating > 5 || !$scoresOk || mb_strlen($summaryText) < 20) {
        header("Location: feedback.php?fb_error=1");
        exit;
    }

    try {
        $q = $conn->prepare("INSERT INTO project_feedback
            (organization_email, project_id, team_name, rating, technical, communication, teamwork, problem_solving, summary, improvements, shared_with_students)
            VALUES (?,?,?,?,?,?,?,?,?,?,?)");
        $q->bind_param("sisiddddssi", $organization_email, $pid, $project['team'], $rating,
            $scores['technical'], $scores['communication'], $scores['teamwork'], $scores['problem_solving'],
            $summaryText, $improvements, $share);
        $q->execute();

        if ($share) {
            $orgName = fbFetch($conn, "SELECT Name FROM organization WHERE Email = ?", "s", [$organization_email])[0]['Name'] ?? 'The organization';
            $nTitle = 'New Project Feedback';
            $nMsg   = $orgName . ' gave your team ' . $rating . '/5 for "' . $project['title'] . '". Open your project to read the full feedback.';
            $n = $conn->prepare("INSERT INTO notifications (Email, title, message, type, status)
                                 SELECT Email, ?, ?, 'project', 'Unread' FROM student_projects WHERE project_id = ?");
            $n->bind_param("ssi", $nTitle, $nMsg, $pid);
            $n->execute();
        }
        header("Location: feedback.php?fb_saved=1");
    } catch (Throwable $e) {
        header("Location: feedback.php?fb_error=1");
    }
    exit;
}

// ---------------------------------------------------------------
// Edit saved feedback
// ---------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update_feedback') {
    $fid    = (int)($_POST['feedback_id'] ?? 0);
    $own    = fbFetch($conn, "SELECT f.id, f.project_id, p.title FROM project_feedback f JOIN projects p ON p.id = f.project_id
                              WHERE f.id = ? AND f.organization_email = ?", "is", [$fid, $organization_email])[0] ?? null;
    $rating = (int)($_POST['rating'] ?? 0);
    $scores = [];
    foreach (['technical', 'communication', 'teamwork', 'problem_solving'] as $k) {
        $scores[$k] = round((float)($_POST[$k] ?? 0) * 2) / 2;
    }
    $summaryText  = trim($_POST['summary'] ?? '');
    $improvements = implode(', ', array_slice(array_filter(array_map('trim', explode(',', $_POST['improvements'] ?? ''))), 0, 8));
    $share        = !empty($_POST['share']) ? 1 : 0;
    $scoresOk     = count(array_filter($scores, fn($v) => $v >= 1 && $v <= 5)) === 4;

    if (!$own || $rating < 1 || $rating > 5 || !$scoresOk || mb_strlen($summaryText) < 20) {
        header("Location: feedback.php?fb_error=1");
        exit;
    }
    try {
        $q = $conn->prepare("UPDATE project_feedback SET rating = ?, technical = ?, communication = ?, teamwork = ?, problem_solving = ?,
                             summary = ?, improvements = ?, shared_with_students = ?, updated_at = NOW() WHERE id = ?");
        $q->bind_param("iddddssii", $rating, $scores['technical'], $scores['communication'], $scores['teamwork'],
                       $scores['problem_solving'], $summaryText, $improvements, $share, $fid);
        $q->execute();

        if ($share) {
            $nTitle = 'Project Feedback Updated';
            $nMsg   = 'The feedback for "' . $own['title'] . '" was updated (' . $rating . '/5).';
            $n = $conn->prepare("INSERT INTO notifications (Email, title, message, type, status)
                                 SELECT Email, ?, ?, 'project', 'Unread' FROM student_projects WHERE project_id = ?");
            $pidN = (int)$own['project_id'];
            $n->bind_param("ssi", $nTitle, $nMsg, $pidN);
            $n->execute();
        }
        header("Location: feedback.php?fb_updated=1");
    } catch (Throwable $e) {
        header("Location: feedback.php?fb_error=1");
    }
    exit;
}

$flash = null;
if (isset($_GET['fb_updated'])) $flash = ['type' => 'success', 'title' => 'Feedback updated', 'message' => 'Your changes to the feedback were saved.'];
if (isset($_GET['fb_saved'])) $flash = ['type' => 'success', 'title' => 'Feedback submitted', 'message' => 'Your feedback was saved and shared with the team.'];
if (isset($_GET['fb_error'])) $flash = ['type' => 'error', 'title' => 'Could not submit feedback', 'message' => 'Please fill all required fields and try again.'];

// ---------------------------------------------------------------
// Feedback saved in the database (shown first)
// ---------------------------------------------------------------
$dbReviews = [];
$catIcon = ['Web Development' => 'language', 'Mobile Development' => 'smartphone', 'AI / Machine Learning' => 'psychology',
            'Data Science' => 'monitoring', 'UI/UX Design' => 'palette', 'Cloud & DevOps' => 'cloud', 'Cybersecurity' => 'shield'];
foreach (fbFetch($conn,
    "SELECT f.*, p.title, p.category FROM project_feedback f JOIN projects p ON p.id = f.project_id
     WHERE f.organization_email = ? ORDER BY f.created_at DESC", "s", [$organization_email]) as $f) {
    $dbReviews[] = [
        'icon' => $catIcon[$f['category']] ?? 'folder', 'title' => $f['title'], 'team' => $f['team_name'],
        'days_ago' => max(0, (int)floor((time() - strtotime($f['created_at'])) / 86400)),
        'created' => strtotime($f['created_at']),
        'rating' => (int)$f['rating'],
        'skills' => ['Technical Skills' => (float)$f['technical'], 'Communication' => (float)$f['communication'],
                     'Teamwork' => (float)$f['teamwork'], 'Problem-solving' => (float)$f['problem_solving']],
        'summary' => $f['summary'],
        'improvements' => array_values(array_filter(array_map('trim', explode(',', $f['improvements'] ?? '')))),
        'is_new' => true,
        'id' => (int)$f['id'], 'key' => 'db-' . (int)$f['id'], 'project_id' => (int)$f['project_id'],
        'category' => $f['category'], 'shared' => (bool)$f['shared_with_students'],
        'updated' => !empty($f['updated_at']) ? date('M d, Y', strtotime($f['updated_at'])) : '',
        'members' => fbFetch($conn, "SELECT s.Name AS name, sp.role FROM student_projects sp JOIN student s ON s.Email = sp.Email WHERE sp.project_id = ?",
                             "i", [(int)$f['project_id']]),
    ];
}

// ---------------------------------------------------------------
// UI-ONLY DEMO DATA (kept for the demo)
// ---------------------------------------------------------------
$summary = [
    'submitted' => 42, 'submitted_change' => '+12%',
    'pending'   => 5,
    'avg'       => 4.8,
    'completed' => 47,
];

$reviews = [
    [
        'icon' => 'cloud', 'title' => 'Cloud Architecture Redesign', 'team' => 'Skyline Team', 'days_ago' => 5,
        'rating' => 5,
        'skills' => ['Technical Skills' => 5.0, 'Communication' => 4.5, 'Teamwork' => 4.8, 'Problem-solving' => 5.0],
        'summary' => 'The team demonstrated exceptional mastery of Azure serverless components. Their ability to migrate the legacy SQL structure into a distributed CosmosDB environment with zero downtime was a significant technical achievement.',
        'improvements' => ['Docker Optimization', 'Agile Documentation', 'Terraform Modularization'],
        'key' => 'demo-cloud', 'category' => 'Cloud & DevOps', 'shared' => true, 'updated' => '',
        'members' => [['name' => 'Tharindu Rajapaksha', 'role' => 'Cloud Architect'], ['name' => 'Hiruni Fernando', 'role' => 'Backend Developer'],
                      ['name' => 'Janith Wickramasinghe', 'role' => 'Database Engineer'], ['name' => 'Oshadi Senarath', 'role' => 'DevOps Engineer']],
    ],
    [
        'icon' => 'api', 'title' => 'Secure API Gateway Implementation', 'team' => 'Omega Devs', 'days_ago' => 21,
        'rating' => 4,
        'skills' => ['Technical Skills' => 4.2, 'Communication' => 4.0, 'Teamwork' => 4.5, 'Problem-solving' => 4.0],
        'summary' => 'Strong execution of the security protocols and OAuth2 implementation. The team was highly responsive to feedback regarding the latency issues in the initial staging environment and optimized the caching layer effectively.',
        'improvements' => ['Security Auditing', 'GraphQL Optimization'],
        'key' => 'demo-api', 'category' => 'Cybersecurity', 'shared' => true, 'updated' => '',
        'members' => [['name' => 'Ravindu Gamage', 'role' => 'Security Engineer'], ['name' => 'Sithumi Perera', 'role' => 'Backend Developer'],
                      ['name' => 'Kaveesha Jayalath', 'role' => 'QA Engineer']],
    ],
];

foreach ($reviews as &$rv) { $rv['created'] = strtotime('-' . (int)$rv['days_ago'] . ' day'); }
unset($rv);
$reviews = array_merge($dbReviews, $reviews);

// stat cards include the saved feedback
if ($dbReviews) {
    $allRatings = array_merge(array_fill(0, (int)$summary['submitted'], (float)$summary['avg']), array_column($dbReviews, 'rating'));
    $summary['avg'] = round(array_sum($allRatings) / count($allRatings), 1);
    $summary['submitted'] += count($dbReviews);
}

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

include "../../../Includes/org_sidebar.php";
include "../../../Includes/dash_header.php";
?>

<style>
    .fb-wrap { padding: 14px 28px 28px; }

    /* ---- heading ---- */
    .fb-head { display: flex; justify-content: space-between; align-items: flex-start; gap: 16px; flex-wrap: wrap; margin-bottom: 20px; }
    .fb-head h1 { margin: 0; font-size: 32px; font-weight: 800; color: #0f3a66; line-height: 1.15; }
    .fb-head p { margin: 6px 0 0; font-size: 14.5px; color: #4b5563; }
    .fb-btn { display: inline-flex; align-items: center; justify-content: center; gap: 6px; padding: 10px 18px; border-radius: 8px; font-family: inherit;
              font-size: 14px; font-weight: 600; cursor: pointer; text-decoration: none; border: 1px solid transparent; transition: all .15s ease; }
    .fb-btn .material-symbols-outlined { font-size: 18px; }
    .fb-btn.solid { background: #0f2a4a; color: #fff; }
    .fb-btn.solid:hover { background: #0a1f38; }
    .fb-btn.outline { background: #fff; border-color: #cbd5e1; color: #1f2937; }
    .fb-btn.outline:hover { background: #f1f5f9; }

    /* ---- stat cards ---- */
    .fb-stats { display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px; margin-bottom: 18px; }
    .fb-stat { background: #fff; border: 1px solid #e5e7eb; border-radius: 14px; padding: 16px 18px; }
    .fb-stat-top { display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px; }
    .fb-stat-icon { width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; }
    .fb-stat-icon .material-symbols-outlined { font-size: 22px; }
    .fb-stat-icon.blue  { background: #dbeafe; color: #1d4ed8; }
    .fb-stat-icon.sky   { background: #e0ecf7; color: #1e4e79; }
    .fb-stat-icon.green { background: #dcfce7; color: #15803d; }
    .fb-pill { font-size: 11px; font-weight: 700; padding: 3px 9px; border-radius: 999px; }
    .fb-pill.up     { background: #dcfce7; color: #166534; }
    .fb-pill.urgent { background: #fee2e2; color: #b91c1c; }
    .fb-stat-label { font-size: 11px; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; color: #6b7280; }
    .fb-stat-value { font-size: 28px; font-weight: 800; color: #111827; line-height: 1.2; margin-top: 2px; }
    .fb-stat-value small { font-size: 14px; font-weight: 500; color: #6b7280; }
    .fb-star { color: #d1d5db; font-variation-settings: 'FILL' 1; }
    .fb-star.on { color: #f59e0b; }

    /* ---- filter bar ---- */
    .fb-filters { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; background: #fff; border: 1px solid #e5e7eb; border-radius: 14px; padding: 12px 14px; margin-bottom: 18px; }
    .fb-select-wrap { position: relative; }
    .fb-select-wrap .lead { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); font-size: 18px; color: #4b5563; pointer-events: none; }
    .fb-select { padding: 10px 34px 10px 38px; border: 1px solid #d1d5db; border-radius: 10px; background: #fff; font-family: inherit; font-size: 14px; color: #374151; cursor: pointer; }
    .fb-sort { margin-left: auto; display: inline-flex; align-items: center; gap: 6px; background: none; border: none; font-family: inherit; font-size: 14px; font-weight: 600; color: #1f2937; cursor: pointer; }
    .fb-sort .material-symbols-outlined { font-size: 18px; }

    /* ---- review cards ---- */
    .fb-list { display: flex; flex-direction: column; gap: 18px; }
    .fb-card { background: #fff; border: 1px solid #e5e7eb; border-radius: 16px; padding: 22px 24px; box-shadow: 0 1px 3px rgba(0,0,0,.04); }
    .fb-top { display: flex; justify-content: space-between; align-items: flex-start; gap: 16px; margin-bottom: 18px; }
    .fb-title-row { display: flex; align-items: center; gap: 14px; }
    .fb-proj-icon { width: 46px; height: 46px; border-radius: 12px; background: #e0ecf7; color: #1e4e79; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .fb-proj-icon .material-symbols-outlined { font-size: 24px; }
    .fb-title { font-size: 18px; font-weight: 700; color: #111827; }
    .fb-meta { display: flex; align-items: center; gap: 6px; font-size: 13px; color: #4b5563; margin-top: 3px; flex-wrap: wrap; }
    .fb-meta .material-symbols-outlined { font-size: 16px; }
    .fb-rating { text-align: right; flex-shrink: 0; }
    .fb-rating .stars { display: flex; justify-content: flex-end; }
    .fb-rating-label { font-size: 11px; font-weight: 800; letter-spacing: .06em; text-transform: uppercase; color: #111827; margin-top: 2px; }

    .fb-body { display: grid; grid-template-columns: 340px 1fr; gap: 24px; }
    .fb-comp { border: 1px solid #e5e7eb; border-radius: 12px; padding: 16px 18px; align-self: start; }
    .fb-label { font-size: 11px; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; color: #6b7280; margin-bottom: 12px; }
    .fb-comp-row { margin-bottom: 12px; }
    .fb-comp-row:last-child { margin-bottom: 0; }
    .fb-comp-top { display: flex; justify-content: space-between; font-size: 13px; color: #374151; margin-bottom: 5px; }
    .fb-comp-top strong { color: #111827; }
    .fb-track { height: 6px; background: #e5e7eb; border-radius: 999px; overflow: hidden; }
    .fb-track span { display: block; height: 100%; background: #0f3a66; border-radius: 999px; }

    .fb-section { margin-bottom: 18px; }
    .fb-summary { margin: 0; font-size: 14px; line-height: 1.6; color: #374151; }
    .fb-chips { display: flex; flex-wrap: wrap; gap: 8px; }
    .fb-chip { background: #dbeafe; color: #1e3a5f; font-size: 12px; font-weight: 600; padding: 5px 12px; border-radius: 999px; }

    .fb-foot { display: flex; justify-content: flex-end; align-items: center; gap: 22px; flex-wrap: wrap; margin-top: 4px; }
    .fb-textbtn { display: inline-flex; align-items: center; gap: 6px; background: none; border: none; font-family: inherit; font-size: 14px; color: #374151; cursor: pointer; padding: 0; }
    .fb-textbtn:hover { color: #0f3a66; }
    .fb-textbtn .material-symbols-outlined { font-size: 18px; }

    /* ---- load more ---- */
    .fb-more { text-align: center; margin-top: 26px; }
    .fb-more p { margin: 10px 0 0; font-size: 13px; color: #6b7280; }

    /* ---- search + clear ---- */
    .fb-search { position: relative; flex: 1; min-width: 220px; }
    .fb-search .material-symbols-outlined { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); font-size: 19px; color: #6b7280; }
    .fb-search input { width: 100%; box-sizing: border-box; padding: 10px 12px 10px 40px; border: 1px solid #d1d5db; border-radius: 10px; font-family: inherit; font-size: 14px; outline: none; }
    .fb-search input:focus, .fb-select:focus { border-color: #93c5fd; box-shadow: 0 0 0 3px rgba(59,130,246,.12); outline: none; }
    .fb-clear { background: none; border: none; font-family: inherit; font-size: 13.5px; font-weight: 600; color: #0f3a66; cursor: pointer; }
    .fb-clear:hover { text-decoration: underline; }
    .fb-clear[hidden], .fb-card[hidden], .fb-empty[hidden] { display: none; }
    .fb-empty { text-align: center; padding: 40px 20px; background: #fff; border: 1px dashed #d1d5db; border-radius: 16px; color: #6b7280; font-weight: 600; }
    .fb-empty .material-symbols-outlined { font-size: 38px; color: #9ca3af; display: block; margin-bottom: 6px; }
    .fb-new { display: inline-block; vertical-align: middle; margin-left: 8px; font-size: 10.5px; font-weight: 700; padding: 2px 8px; border-radius: 999px; background: #dbeafe; color: #1d4ed8; }

    /* ---- submit feedback modal ---- */
    .fb-overlay { position: fixed; inset: 0; z-index: 5000; background: rgba(15,23,42,.45); -webkit-backdrop-filter: blur(4px); backdrop-filter: blur(4px);
                  display: none; align-items: center; justify-content: center; padding: 16px; }
    .fb-overlay.open { display: flex; }
    .fb-modal { width: min(680px, 100%); max-height: 92vh; display: flex; flex-direction: column; overflow: hidden; background: #fff; border-radius: 16px; box-shadow: 0 24px 48px rgba(15,23,42,.22); }
    .fb-m-head { display: flex; gap: 14px; align-items: flex-start; padding: 20px 22px 16px; border-bottom: 1px solid #e5e7eb; }
    .fb-m-icon { width: 44px; height: 44px; border-radius: 10px; flex-shrink: 0; background: #dbeafe; color: #1d4ed8; display: flex; align-items: center; justify-content: center; }
    .fb-m-head h3 { margin: 0; font-size: 19px; font-weight: 800; color: #0f3a66; }
    .fb-m-head p { margin: 3px 0 0; font-size: 13px; color: #6b7280; }
    .fb-x { margin-left: auto; background: none; border: none; cursor: pointer; color: #6b7280; width: 34px; height: 34px; border-radius: 8px; display: flex; align-items: center; justify-content: center; }
    .fb-x:hover { background: #f3f4f6; color: #0f2a4a; }
    .fb-m-body { padding: 16px 22px 18px; overflow-y: auto; }
    .fb-m-foot { display: flex; justify-content: flex-end; gap: 10px; padding: 14px 22px 18px; border-top: 1px solid #e5e7eb; }

    .fb-step { display: flex; align-items: center; gap: 8px; margin: 8px 0 12px; font-size: 14px; font-weight: 800; color: #0f3a66; }
    .fb-step span { width: 24px; height: 24px; border-radius: 7px; background: #0f2a4a; color: #fff; font-size: 12px; display: flex; align-items: center; justify-content: center; }
    .fb-step:not(:first-child) { margin-top: 20px; padding-top: 16px; border-top: 1px solid #f1f3f6; }
    .fb-field { margin-bottom: 14px; }
    .fb-f-label { display: block; font-size: 11.5px; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; color: #6b7280; margin-bottom: 6px; }
    .fb-input { width: 100%; box-sizing: border-box; padding: 10px 12px; border: 1px solid #e5e7eb; border-radius: 10px; background: #f9fafb;
                font-family: inherit; font-size: 14px; color: #111827; outline: none; resize: vertical; }
    .fb-input:focus { border-color: #93c5fd; background: #fff; box-shadow: 0 0 0 3px rgba(59,130,246,.12); }
    .fb-input.invalid { border-color: #ef4444; box-shadow: 0 0 0 3px rgba(239,68,68,.1); }
    .fb-err { font-size: 12.5px; color: #dc2626; margin-top: 5px; }
    .fb-err:empty { display: none; }
    .fb-counter { display: flex; justify-content: space-between; font-size: 12px; color: #9ca3af; margin-top: 4px; }
    .fb-counter > span:last-child { margin-left: auto; }

    .fb-team { border: 1px solid #e5e7eb; border-radius: 12px; padding: 12px 14px; margin: -4px 0 6px; background: #fafbfc; }
    .fb-team[hidden] { display: none; }
    .fb-team-top { display: flex; align-items: center; gap: 8px; font-size: 14px; font-weight: 700; color: #111827; margin-bottom: 8px; }
    .fb-team-top .material-symbols-outlined { font-size: 19px; color: #0f3a66; }
    .fb-team-top .fb-st { margin-left: auto; font-size: 11px; font-weight: 700; padding: 2px 8px; border-radius: 999px; background: #e5e7eb; color: #374151; }
    .fb-people { display: flex; flex-wrap: wrap; gap: 6px; }
    .fb-person { display: inline-flex; align-items: center; gap: 6px; background: #fff; border: 1px solid #e5e7eb; border-radius: 999px; padding: 3px 10px 3px 3px; font-size: 12.5px; color: #374151; }
    .fb-person i { width: 22px; height: 22px; border-radius: 50%; color: #fff; font-style: normal; font-size: 9.5px; font-weight: 700; display: flex; align-items: center; justify-content: center; }

    .fb-overall { display: flex; align-items: center; gap: 12px; }
    .fb-star-pick { display: flex; gap: 2px; }
    .fb-star-pick button { background: none; border: none; padding: 2px; cursor: pointer; line-height: 0; }
    .fb-star-pick .material-symbols-outlined { font-size: 34px; color: #d1d5db; font-variation-settings: 'FILL' 1; transition: transform .1s ease, color .1s ease; }
    .fb-star-pick button:hover .material-symbols-outlined { transform: scale(1.12); }
    .fb-star-pick .on .material-symbols-outlined { color: #f59e0b; }
    .fb-star-text { font-size: 14px; font-weight: 700; color: #6b7280; }
    .fb-star-text.set { color: #111827; }

    .fb-comp-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px 16px; }
    .fb-slider { border: 1px solid #e5e7eb; border-radius: 10px; padding: 10px 12px; }
    .fb-slider-top { display: flex; justify-content: space-between; font-size: 13px; color: #374151; margin-bottom: 6px; }
    .fb-slider-top strong { color: #0f3a66; }
    .fb-slider input[type=range] { width: 100%; accent-color: #0f3a66; cursor: pointer; }

    .fb-chip-box { display: flex; flex-wrap: wrap; gap: 6px; align-items: center; min-height: 44px; padding: 6px 8px; border: 1px solid #e5e7eb; border-radius: 10px; background: #f9fafb; cursor: text; }
    .fb-chip-box:focus-within { border-color: #93c5fd; background: #fff; box-shadow: 0 0 0 3px rgba(59,130,246,.12); }
    .fb-chip-box input { flex: 1; min-width: 180px; border: none; background: transparent; outline: none; font-family: inherit; font-size: 14px; padding: 4px; }
    .fb-chip-box .fb-chip { display: inline-flex; align-items: center; gap: 4px; }
    .fb-chip-box .fb-chip button { background: none; border: none; cursor: pointer; color: #1e3a5f; font-size: 15px; line-height: 1; padding: 0; }
    .fb-quick { display: flex; flex-wrap: wrap; align-items: center; gap: 6px; margin-top: 8px; font-size: 12px; color: #9ca3af; }
    .fb-quick button { border: 1px dashed #cbd5e1; background: #fff; color: #374151; font-family: inherit; font-size: 12px; padding: 3px 9px; border-radius: 999px; cursor: pointer; }
    .fb-quick button:hover { border-color: #0f3a66; color: #0f3a66; }

    .fb-share { display: flex; gap: 10px; align-items: flex-start; padding: 12px 14px; border: 1px solid #e5e7eb; border-radius: 12px; cursor: pointer; font-size: 13.5px; color: #111827; }
    .fb-share input { width: 17px; height: 17px; margin-top: 2px; accent-color: #0f2a4a; }
    .fb-share small { color: #6b7280; font-size: 12.5px; }
    .fb-note { display: flex; gap: 8px; padding: 12px 14px; border-radius: 10px; background: #fffbeb; border: 1px solid #fde68a; color: #92400e; font-size: 13.5px; line-height: 1.5; }

    /* ---- feedback details ---- */
    .fv-modal { width: min(860px, 100%); }
    .fv-head { display: flex; gap: 18px; align-items: flex-start; padding: 20px 22px 16px; border-bottom: 1px solid #e5e7eb; }
    .fv-head-main { flex: 1; min-width: 0; }
    .fv-kicker { font-size: 12px; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; color: #6b7280; }
    .fv-head h3 { margin: 4px 0 6px; font-size: 22px; font-weight: 800; color: #0f3a66; line-height: 1.3; }
    .fv-meta { display: flex; flex-wrap: wrap; align-items: center; gap: 6px 14px; font-size: 13px; color: #4b5563; }
    .fv-meta span { display: inline-flex; align-items: center; gap: 4px; }
    .fv-meta .material-symbols-outlined { font-size: 16px; }
    .fv-score { text-align: center; padding: 8px 14px; border: 1px solid #fde68a; background: #fffbeb; border-radius: 12px; flex-shrink: 0; }
    .fv-score-num { font-size: 26px; font-weight: 800; color: #111827; line-height: 1; }
    .fv-score-num small { font-size: 13px; color: #6b7280; font-weight: 600; }
    .fv-score-stars { display: flex; justify-content: center; margin: 4px 0 2px; }
    .fv-score-label { font-size: 10.5px; font-weight: 800; letter-spacing: .06em; text-transform: uppercase; color: #92400e; }
    .fv-body { padding: 18px 22px; overflow-y: auto; background: #f5f7fb; }

    .fv-stats { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; margin-bottom: 14px; }
    .fv-stat { display: flex; align-items: center; gap: 12px; background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; padding: 14px; }
    .fv-stat-icon { width: 40px; height: 40px; border-radius: 10px; flex-shrink: 0; display: flex; align-items: center; justify-content: center; }
    .fv-stat-label { font-size: 11px; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; color: #6b7280; }
    .fv-stat-value { font-size: 15px; font-weight: 800; color: #111827; }
    .fv-card { background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; padding: 16px 18px; margin-bottom: 14px; }
    .fv-card h4 { display: flex; align-items: center; gap: 6px; margin: 0 0 12px; font-size: 12px; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; color: #6b7280; }
    .fv-card h4 .material-symbols-outlined { font-size: 18px; color: #0f3a66; }
    .fv-card p { margin: 0; font-size: 14.5px; line-height: 1.7; color: #374151; white-space: pre-wrap; }
    .fv-two { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
    .fv-two .fv-card { margin-bottom: 0; }
    .fv-comp { display: grid; grid-template-columns: 130px 1fr 42px; align-items: center; gap: 10px; font-size: 13.5px; color: #374151; padding: 6px 0; }
    .fv-comp strong { text-align: right; color: #111827; }
    .fv-comp .fb-track { height: 8px; }
    .fv-comp .fb-track span.best { background: #16a34a; }
    .fv-comp .fb-track span.low  { background: #ea580c; }
    .fv-member { display: flex; align-items: center; gap: 10px; padding: 7px 0; border-top: 1px solid #f1f3f6; }
    .fv-member:first-child { border-top: none; }
    .fv-member i { width: 32px; height: 32px; border-radius: 50%; color: #fff; font-style: normal; font-size: 11px; font-weight: 700; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .fv-member-name { font-size: 13.5px; font-weight: 600; color: #111827; }
    .fv-member-role { font-size: 12px; color: #6b7280; }
    .fv-info-row { display: flex; justify-content: space-between; gap: 10px; padding: 7px 0; border-top: 1px solid #f1f3f6; font-size: 13px; }
    .fv-info-row:first-child { border-top: none; }
    .fv-info-row span { color: #6b7280; } .fv-info-row strong { color: #111827; font-weight: 600; text-align: right; }
    .fv-empty { font-size: 13px; color: #9ca3af; font-style: italic; }
    .fb-select-locked { background: #f3f4f6 !important; color: #6b7280 !important; cursor: not-allowed; }

    @media (max-width: 700px) { .fv-stats { grid-template-columns: 1fr; } .fv-two { grid-template-columns: 1fr; } .fv-head { flex-wrap: wrap; } .fv-comp { grid-template-columns: 100px 1fr 36px; } }

    /* print only the feedback details */
    @media print {
        body * { visibility: hidden !important; }
        #fbViewModal, #fbViewModal * { visibility: visible !important; }
        #fbViewModal { position: absolute !important; inset: 0 !important; background: none !important; backdrop-filter: none !important; display: block !important; padding: 0 !important; }
        #fbViewModal .fb-modal { max-height: none !important; box-shadow: none !important; width: 100% !important; }
        #fbViewModal .fv-foot, #fbViewModal .fb-x { display: none !important; }
        .fv-body { overflow: visible !important; }
    }

    @media (max-width: 600px) { .fb-comp-grid { grid-template-columns: 1fr; } .fb-search { min-width: 100%; } }

    @media (max-width: 1100px) { .fb-stats { grid-template-columns: repeat(2, 1fr); } }
    @media (max-width: 900px)  { .fb-body { grid-template-columns: 1fr; } }
    @media (max-width: 600px)  { .fb-wrap { padding: 12px 16px 24px; } .fb-head h1 { font-size: 24px; } .fb-stats { grid-template-columns: 1fr; } .fb-top { flex-direction: column; } .fb-rating { text-align: left; } .fb-rating .stars { justify-content: flex-start; } }
</style>

<main class="content">
    <div class="fb-wrap">

        <!-- Heading -->
        <div class="fb-head">
            <div>
                <h1>Project Feedback &amp; Reviews</h1>
                <p>Manage and analyze technical performance across your academic collaborations.</p>
            </div>
            <button type="button" class="fb-btn solid" id="fbOpenForm"><span class="material-symbols-outlined">add</span>Submit New Feedback</button>
        </div>

        <!-- Stat cards -->
        <div class="fb-stats">
            <div class="fb-stat">
                <div class="fb-stat-top">
                    <div class="fb-stat-icon blue"><span class="material-symbols-outlined">rate_review</span></div>
                    <span class="fb-pill up"><?= htmlspecialchars($summary['submitted_change']) ?></span>
                </div>
                <div class="fb-stat-label">Feedback Submitted</div>
                <div class="fb-stat-value"><?= (int)$summary['submitted'] ?></div>
            </div>
            <div class="fb-stat">
                <div class="fb-stat-top">
                    <div class="fb-stat-icon sky"><span class="material-symbols-outlined">pending_actions</span></div>
                    <span class="fb-pill urgent">Urgent</span>
                </div>
                <div class="fb-stat-label">Pending Reviews</div>
                <div class="fb-stat-value"><?= (int)$summary['pending'] ?></div>
            </div>
            <div class="fb-stat">
                <div class="fb-stat-top">
                    <div class="fb-stat-icon green"><span class="material-symbols-outlined" style="font-variation-settings:'FILL' 1;">star</span></div>
                    <div style="display:flex;"><?= fbStars($summary['avg'], 14) ?></div>
                </div>
                <div class="fb-stat-label">Average Rating</div>
                <div class="fb-stat-value"><?= number_format($summary['avg'], 1) ?><small>/5.0</small></div>
            </div>
            <div class="fb-stat">
                <div class="fb-stat-top">
                    <div class="fb-stat-icon sky"><span class="material-symbols-outlined">task_alt</span></div>
                </div>
                <div class="fb-stat-label">Completed Projects</div>
                <div class="fb-stat-value"><?= (int)$summary['completed'] ?></div>
            </div>
        </div>

        <!-- Filters -->
        <div class="fb-filters">
            <div class="fb-search">
                <span class="material-symbols-outlined">search</span>
                <input type="text" id="fbSearch" placeholder="Search by project or team name">
            </div>
            <div class="fb-select-wrap">
                <span class="material-symbols-outlined lead">star</span>
                <select class="fb-select" id="fbRating">
                    <option value="all">All ratings</option>
                    <?php foreach ([5, 4, 3, 2, 1] as $r): ?>
                        <option value="<?= $r ?>"><?= $r ?> Star<?= $r > 1 ? 's' : '' ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="fb-select-wrap">
                <span class="material-symbols-outlined lead">sort</span>
                <select class="fb-select" id="fbSort">
                    <option value="latest">Latest first</option>
                    <option value="oldest">Oldest first</option>
                    <option value="high">Highest rating</option>
                    <option value="low">Lowest rating</option>
                </select>
            </div>
            <button type="button" class="fb-clear" id="fbClear" hidden>Clear</button>
        </div>

        <!-- Review cards -->
        <div class="fb-list">
            <?php foreach ($reviews as $ri => $r): ?>
                <div class="fb-card" data-fb-index="<?= (int)$ri ?>"
                     data-rating="<?= (int)$r['rating'] ?>"
                     data-created="<?= (int)$r['created'] ?>"
                     data-search="<?= htmlspecialchars(strtolower($r['title'] . ' ' . $r['team'])) ?>">

                    <div class="fb-top">
                        <div class="fb-title-row">
                            <div class="fb-proj-icon"><span class="material-symbols-outlined"><?= htmlspecialchars($r['icon']) ?></span></div>
                            <div>
                                <div class="fb-title"><?= htmlspecialchars($r['title']) ?>
                                    <?php if (!empty($r['is_new'])): ?><span class="fb-new">New</span><?php endif; ?></div>
                                <div class="fb-meta">
                                    <span class="material-symbols-outlined">groups</span><?= htmlspecialchars($r['team']) ?>
                                    <span>&bull;</span>
                                    <?= date('M d, Y', strtotime('-' . (int)$r['days_ago'] . ' day')) ?>
                                </div>
                            </div>
                        </div>
                        <div class="fb-rating">
                            <div class="stars"><?= fbStars($r['rating'], 20) ?></div>
                            <div class="fb-rating-label"><?= $ratingLabels[$r['rating']] ?></div>
                        </div>
                    </div>

                    <div class="fb-body">
                        <div class="fb-comp">
                            <div class="fb-label">Competency Breakdown</div>
                            <?php foreach ($r['skills'] as $name => $score): ?>
                                <div class="fb-comp-row">
                                    <div class="fb-comp-top"><span><?= htmlspecialchars($name) ?></span><strong><?= number_format($score, 1) ?></strong></div>
                                    <div class="fb-track"><span style="width:<?= round($score / 5 * 100) ?>%;"></span></div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <div>
                            <div class="fb-section">
                                <div class="fb-label">Performance Summary</div>
                                <p class="fb-summary"><?= htmlspecialchars($r['summary']) ?></p>
                            </div>
                            <div class="fb-section">
                                <div class="fb-label">Suggested Improvements</div>
                                <div class="fb-chips">
                                    <?php foreach ($r['improvements'] as $imp): ?><span class="fb-chip"><?= htmlspecialchars($imp) ?></span><?php endforeach; ?>
                                </div>
                            </div>
                            <div class="fb-foot">
                                <button type="button" class="fb-textbtn" data-fb-edit="<?= (int)$ri ?>"><span class="material-symbols-outlined">edit</span>Edit</button>
                                <button type="button" class="fb-btn solid" data-fb-view="<?= (int)$ri ?>">View Full Details</button>
                            </div>
                        </div>
                    </div>

                </div>
            <?php endforeach; ?>
        </div>

        <div class="fb-empty" id="fbEmpty" hidden>
            <span class="material-symbols-outlined">search_off</span>
            <div>No feedback matches your filters</div>
        </div>

        <!-- Load more -->
        <div class="fb-more">
            <button type="button" class="fb-btn outline">Load More Records</button>
            <p>Showing <span id="fbShown"><?= count($reviews) ?></span> of <?= (int)$summary['submitted'] ?> completed reviews</p>
        </div>

    </div>

    <!-- ===================== FEEDBACK DETAILS (View Full Details) ===================== -->
    <div class="fb-overlay" id="fbViewModal">
        <div class="fb-modal fv-modal" role="dialog" aria-modal="true">
            <div class="fv-head">
                <div class="fv-head-main">
                    <div class="fv-kicker">Feedback Details</div>
                    <h3 id="fvTitle"></h3>
                    <div class="fv-meta" id="fvMeta"></div>
                </div>
                <div class="fv-score">
                    <div class="fv-score-num" id="fvScore"></div>
                    <div class="fv-score-stars" id="fvStars"></div>
                    <div class="fv-score-label" id="fvLabel"></div>
                </div>
                <button type="button" class="fb-x" data-fv-close aria-label="Close"><span class="material-symbols-outlined">close</span></button>
            </div>
            <div class="fv-body" id="fvBody"></div>
            <div class="fb-m-foot fv-foot">
                <button type="button" class="fb-btn outline" id="fvPrint"><span class="material-symbols-outlined">print</span>Print / Save PDF</button>
                <div style="display:flex; gap:10px; margin-left:auto;">
                    <button type="button" class="fb-btn outline" data-fv-close>Close</button>
                    <button type="button" class="fb-btn solid" id="fvEdit"><span class="material-symbols-outlined">edit</span>Edit Feedback</button>
                </div>
            </div>
        </div>
    </div>

    <!-- ===================== SUBMIT NEW FEEDBACK ===================== -->
    <div class="fb-overlay" id="fbModal">
        <form class="fb-modal" method="post" action="feedback.php" id="fbForm" novalidate>
            <input type="hidden" name="action" value="submit_feedback" id="fbAction">
            <input type="hidden" name="feedback_id" id="fbFeedbackId">
            <input type="hidden" name="rating" id="fbRatingVal">

            <div class="fb-m-head">
                <div class="fb-m-icon"><span class="material-symbols-outlined">rate_review</span></div>
                <div>
                    <h3 id="fbFormTitle">Submit New Feedback</h3>
                    <p id="fbFormSub">Rate the team’s work and help them improve.</p>
                </div>
                <button type="button" class="fb-x" data-fb-close aria-label="Close"><span class="material-symbols-outlined">close</span></button>
            </div>

            <div class="fb-m-body">
                <?php if (empty($fbProjects)): ?>
                    <div class="fb-note">
                        <span class="material-symbols-outlined">info</span>
                        None of your projects have students working on them yet. Feedback can be given once a team is assigned.
                    </div>
                <?php else: ?>

                <!-- 1. Project -->
                <div class="fb-step"><span>1</span> Project &amp; Team</div>
                <div class="fb-field">
                    <label class="fb-f-label" for="fbProject">Project *</label>
                    <select id="fbProject" name="project_id" class="fb-input">
                        <option value="">Select a project</option>
                        <?php foreach ($fbProjects as $fp): ?>
                            <option value="<?= $fp['id'] ?>"><?= htmlspecialchars($fp['title']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <div class="fb-err" data-err="project"></div>
                </div>
                <div class="fb-team" id="fbTeamBox" hidden></div>

                <!-- 2. Ratings -->
                <div class="fb-step"><span>2</span> Ratings</div>
                <div class="fb-field">
                    <label class="fb-f-label">Overall Rating *</label>
                    <div class="fb-overall">
                        <div class="fb-star-pick" id="fbStarPick">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <button type="button" data-v="<?= $i ?>" aria-label="<?= $i ?> star"><span class="material-symbols-outlined">star</span></button>
                            <?php endfor; ?>
                        </div>
                        <span class="fb-star-text" id="fbStarText">Click to rate</span>
                    </div>
                    <div class="fb-err" data-err="rating"></div>
                </div>

                <div class="fb-f-label" style="margin-top:4px;">Competency Breakdown *</div>
                <div class="fb-comp-grid">
                    <?php foreach (['technical' => 'Technical Skills', 'communication' => 'Communication', 'teamwork' => 'Teamwork', 'problem_solving' => 'Problem-solving'] as $k => $label): ?>
                        <div class="fb-slider">
                            <div class="fb-slider-top"><span><?= $label ?></span><strong data-out="<?= $k ?>">3.0</strong></div>
                            <input type="range" name="<?= $k ?>" min="1" max="5" step="0.5" value="3" data-range="<?= $k ?>">
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- 3. Written feedback -->
                <div class="fb-step"><span>3</span> Written Feedback</div>
                <div class="fb-field">
                    <label class="fb-f-label" for="fbSummary">Performance Summary *</label>
                    <textarea id="fbSummary" name="summary" class="fb-input" rows="4" maxlength="1500"
                              placeholder="What did the team do well? How was the quality of their work and their communication?"></textarea>
                    <div class="fb-counter"><span class="fb-err" data-err="summary"></span><span><span id="fbCount">0</span>/1500</span></div>
                </div>

                <div class="fb-field">
                    <label class="fb-f-label">Suggested Improvements</label>
                    <div class="fb-chip-box" id="fbChipBox">
                        <input type="text" id="fbChipInput" placeholder="Type a skill to improve and press Enter">
                    </div>
                    <input type="hidden" name="improvements" id="fbImprovements">
                    <div class="fb-quick">
                        <span>Quick add:</span>
                        <?php foreach (['Documentation', 'Unit Testing', 'Time Management', 'Code Reviews', 'Git Workflow'] as $qa): ?>
                            <button type="button" data-quick="<?= $qa ?>">+ <?= $qa ?></button>
                        <?php endforeach; ?>
                    </div>
                </div>

                <label class="fb-share">
                    <input type="checkbox" name="share" value="1" id="fbShare" checked>
                    <span><strong>Share with the students</strong><br><small>Team members get a notification about this feedback.</small></span>
                </label>

                <?php endif; ?>
            </div>

            <div class="fb-m-foot">
                <button type="button" class="fb-btn outline" data-fb-close>Cancel</button>
                <?php if (!empty($fbProjects)): ?>
                <button type="submit" class="fb-btn solid"><span class="material-symbols-outlined" id="fbSubmitIcon">send</span><span id="fbSubmitText">Submit Feedback</span></button>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <?php if ($flash): ?>
    <div class="flash-toast flash-<?= htmlspecialchars($flash['type']) ?>" id="fbFlash">
        <div class="flash-icon-wrap"><span class="material-symbols-outlined"><?= $flash['type'] === 'success' ? 'check_circle' : 'error' ?></span></div>
        <div class="flash-content">
            <div class="flash-title" style="font-weight:700; font-size:14px;"><?= htmlspecialchars($flash['title']) ?></div>
            <div class="flash-msg" style="font-size:13px; margin-top:2px;"><?= htmlspecialchars($flash['message']) ?></div>
        </div>
    </div>
    <?php endif; ?>
</main>

<script>
// popup covers the whole screen (moved out of <main>)
document.body.appendChild(document.getElementById('fbModal'));

/* ================= FILTERS: search, rating, sort ================= */
(function () {
    const list   = document.querySelector('.fb-list');
    const cards  = Array.from(list.querySelectorAll('.fb-card'));
    const search = document.getElementById('fbSearch');
    const rating = document.getElementById('fbRating');
    const sort   = document.getElementById('fbSort');
    const clear  = document.getElementById('fbClear');

    function apply() {
        const q = search.value.trim().toLowerCase();
        let shown = 0;
        cards.forEach(c => {
            const ok = (rating.value === 'all' || c.dataset.rating === rating.value)
                    && (q === '' || c.dataset.search.includes(q));
            c.hidden = !ok;
            if (ok) shown++;
        });
        const n = (c, k) => Number(c.dataset[k]) || 0;
        cards.slice().sort((a, b) => {
            if (sort.value === 'oldest') return n(a, 'created') - n(b, 'created');
            if (sort.value === 'high')   return n(b, 'rating') - n(a, 'rating') || n(b, 'created') - n(a, 'created');
            if (sort.value === 'low')    return n(a, 'rating') - n(b, 'rating') || n(b, 'created') - n(a, 'created');
            return n(b, 'created') - n(a, 'created');
        }).forEach(c => list.appendChild(c));

        document.getElementById('fbShown').textContent = shown;
        document.getElementById('fbEmpty').hidden = shown !== 0;
        clear.hidden = q === '' && rating.value === 'all' && sort.value === 'latest';
    }
    search.addEventListener('input', apply);
    rating.addEventListener('change', apply);
    sort.addEventListener('change', apply);
    clear.addEventListener('click', () => { search.value = ''; rating.value = 'all'; sort.value = 'latest'; apply(); });
    apply();
})();

/* ================= SUBMIT NEW FEEDBACK ================= */
(function () {
    const PROJECTS = <?= json_encode($fbProjects, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?>;
    const LABELS = { 1: 'Needs Work', 2: 'Fair', 3: 'Good', 4: 'Very Good', 5: 'Exceptional' };
    const STATUS = { reviewing: 'Reviewing', inprogress: 'Active', closed: 'Closed', hold: 'On Hold' };
    const COLORS = ['#1e3a5f', '#0f766e', '#7c3aed', '#b45309', '#be185d', '#2563eb', '#475569'];

    const modal = document.getElementById('fbModal');
    const form  = document.getElementById('fbForm');
    const esc = t => { const d = document.createElement('div'); d.textContent = t == null ? '' : String(t); return d.innerHTML; };
    const initials = n => String(n || '').trim().split(/\s+/).slice(0, 2).map(w => w.charAt(0).toUpperCase()).join('');
    const colorFor = n => { let h = 0; for (const c of String(n)) h = (h * 31 + c.charCodeAt(0)) >>> 0; return COLORS[h % COLORS.length]; };
    const err = (k, m) => { const e = form.querySelector('[data-err="' + k + '"]'); if (e) e.textContent = m || ''; };

    function open() { modal.classList.add('open'); }
    function close() { modal.classList.remove('open'); }
    document.getElementById('fbOpenForm').addEventListener('click', () => window.fbOpenForm ? window.fbOpenForm(null) : open());
    modal.querySelectorAll('[data-fb-close]').forEach(b => b.addEventListener('click', close));
    modal.addEventListener('click', e => { if (e.target === modal) close(); });
    document.addEventListener('keydown', e => { if (e.key === 'Escape' && modal.classList.contains('open')) close(); });

    const projectEl = document.getElementById('fbProject');
    document.body.appendChild(document.getElementById('fbViewModal'));
    if (!projectEl) return;          // no projects -> only the info note is shown

    // project -> team + students
    projectEl.addEventListener('change', () => {
        const p = PROJECTS.find(x => x.id === Number(projectEl.value));
        const box = document.getElementById('fbTeamBox');
        err('project', ''); projectEl.classList.remove('invalid');
        if (!p) { box.hidden = true; return; }
        box.innerHTML = `
            <div class="fb-team-top"><span class="material-symbols-outlined">groups</span>${esc(p.team)}
                <span class="fb-st">${esc(STATUS[p.status] || p.status)}</span></div>
            <div class="fb-people">${p.students.map(st =>
                `<span class="fb-person"><i style="background:${colorFor(st.name)}">${esc(initials(st.name))}</i>${esc(st.name)}${st.role ? ' · ' + esc(st.role) : ''}</span>`).join('')}</div>`;
        box.hidden = false;
    });

    // overall stars
    const starBtns = Array.from(document.querySelectorAll('#fbStarPick button'));
    const ratingVal = document.getElementById('fbRatingVal');
    const starText = document.getElementById('fbStarText');
    function paint(v) { starBtns.forEach(b => b.classList.toggle('on', Number(b.dataset.v) <= v)); }
    starBtns.forEach(b => {
        b.addEventListener('mouseenter', () => paint(Number(b.dataset.v)));
        b.addEventListener('click', () => {
            ratingVal.value = b.dataset.v;
            starText.textContent = b.dataset.v + '/5 · ' + LABELS[b.dataset.v];
            starText.classList.add('set');
            err('rating', '');
        });
    });
    document.getElementById('fbStarPick').addEventListener('mouseleave', () => paint(Number(ratingVal.value) || 0));

    // competency sliders
    form.querySelectorAll('[data-range]').forEach(r => r.addEventListener('input', () => {
        form.querySelector('[data-out="' + r.dataset.range + '"]').textContent = Number(r.value).toFixed(1);
    }));

    // summary counter
    const summary = document.getElementById('fbSummary');
    summary.addEventListener('input', () => {
        document.getElementById('fbCount').textContent = summary.value.length;
        if (summary.value.trim().length >= 20) { err('summary', ''); summary.classList.remove('invalid'); }
    });

    // improvements chips
    let chips = [];
    const chipBox = document.getElementById('fbChipBox');
    const chipIn  = document.getElementById('fbChipInput');
    function renderChips() {
        chipBox.querySelectorAll('.fb-chip').forEach(c => c.remove());
        chips.forEach((c, i) => {
            const el = document.createElement('span');
            el.className = 'fb-chip';
            el.innerHTML = esc(c) + ' <button type="button" aria-label="Remove">&times;</button>';
            el.querySelector('button').addEventListener('click', ev => { ev.stopPropagation(); chips.splice(i, 1); renderChips(); });
            chipBox.insertBefore(el, chipIn);
        });
        document.getElementById('fbImprovements').value = chips.join(', ');
    }
    function addChip(v) {
        (v || '').split(',').map(x => x.trim()).filter(Boolean).forEach(x => {
            if (chips.length < 8 && !chips.some(c => c.toLowerCase() === x.toLowerCase())) chips.push(x);
        });
        chipIn.value = '';
        renderChips();
    }
    chipIn.addEventListener('keydown', e => {
        if (e.key === 'Enter' || e.key === ',') { e.preventDefault(); addChip(chipIn.value); }
        else if (e.key === 'Backspace' && !chipIn.value && chips.length) { chips.pop(); renderChips(); }
    });
    chipIn.addEventListener('blur', () => addChip(chipIn.value));
    chipBox.addEventListener('click', () => chipIn.focus());
    document.querySelectorAll('[data-quick]').forEach(b => b.addEventListener('click', () => addChip(b.dataset.quick)));

    // validation
    form.addEventListener('submit', e => {
        addChip(chipIn.value);
        let ok = true;
        if (!projectEl.value) { err('project', 'Please select a project.'); projectEl.classList.add('invalid'); ok = false; }
        if (!ratingVal.value) { err('rating', 'Please give an overall rating.'); ok = false; }
        if (summary.value.trim().length < 20) { err('summary', 'Please write at least 20 characters.'); summary.classList.add('invalid'); ok = false; }
        if (!ok) {
            e.preventDefault();
            const first = form.querySelector('.fb-err:not(:empty)');
            if (first) first.scrollIntoView({ behavior: 'smooth', block: 'center' });
            return;
        }
        // editing a demo review (not in the database) -> save in this browser
        if (editingDemo) {
            e.preventDefault();
            const val = n => Number(form.querySelector('[data-range="' + n + '"]').value);
            window.fbSaveDemo(editingDemo, {
                rating: Number(ratingVal.value),
                skills: { 'Technical Skills': val('technical'), 'Communication': val('communication'),
                          'Teamwork': val('teamwork'), 'Problem-solving': val('problem_solving') },
                summary: summary.value.trim(),
                improvements: chips.slice(),
                shared: document.getElementById('fbShare').checked,
                updated: new Date().toLocaleDateString('en-US', { month: 'short', day: '2-digit', year: 'numeric' })
            });
            close();
        }
    });

    // ---------- open the form: new (r = null) or edit (r = review) ----------
    let editingDemo = null;
    const tempOptClass = 'fb-temp-opt';
    window.fbOpenForm = function (r) {
        form.reset();
        form.querySelectorAll('.fb-err').forEach(x => x.textContent = '');
        form.querySelectorAll('.invalid').forEach(x => x.classList.remove('invalid'));
        projectEl.querySelectorAll('.' + tempOptClass).forEach(o => o.remove());
        projectEl.disabled = false;
        projectEl.classList.remove('fb-select-locked');
        document.getElementById('fbTeamBox').hidden = true;
        editingDemo = null;

        const edit = !!r;
        document.getElementById('fbAction').value = edit ? 'update_feedback' : 'submit_feedback';
        document.getElementById('fbFeedbackId').value = edit && r.id ? r.id : '';
        document.getElementById('fbFormTitle').textContent = edit ? 'Edit Feedback' : 'Submit New Feedback';
        document.getElementById('fbFormSub').textContent = edit ? 'Update the ratings and comments for this team.' : 'Rate the team’s work and help them improve.';
        document.getElementById('fbSubmitText').textContent = edit ? 'Save Changes' : 'Submit Feedback';
        document.getElementById('fbSubmitIcon').textContent = edit ? 'check' : 'send';

        // ratings
        ratingVal.value = edit ? r.rating : '';
        paint(edit ? r.rating : 0);
        starText.textContent = edit ? r.rating + '/5 · ' + LABELS[r.rating] : 'Click to rate';
        starText.classList.toggle('set', edit);
        const map = { technical: 'Technical Skills', communication: 'Communication', teamwork: 'Teamwork', problem_solving: 'Problem-solving' };
        Object.keys(map).forEach(k => {
            const v = edit ? Number(r.skills[map[k]] || 3) : 3;
            form.querySelector('[data-range="' + k + '"]').value = v;
            form.querySelector('[data-out="' + k + '"]').textContent = v.toFixed(1);
        });

        summary.value = edit ? r.summary : '';
        document.getElementById('fbCount').textContent = summary.value.length;
        chips = edit ? r.improvements.slice() : [];
        renderChips();
        document.getElementById('fbShare').checked = edit ? !!r.shared : true;

        if (edit) {
            // the project of a feedback can't be changed
            let opt = Array.from(projectEl.options).find(o => r.project_id && Number(o.value) === r.project_id);
            if (!opt) {
                opt = new Option(r.title, r.project_id || 'demo', true, true);
                opt.className = tempOptClass;
                projectEl.add(opt);
            }
            projectEl.value = opt.value;
            projectEl.disabled = true;
            projectEl.classList.add('fb-select-locked');
            const box = document.getElementById('fbTeamBox');
            box.innerHTML = `
                <div class="fb-team-top"><span class="material-symbols-outlined">groups</span>${esc(r.team)}</div>
                <div class="fb-people">${(r.members || []).map(st =>
                    `<span class="fb-person"><i style="background:${colorFor(st.name)}">${esc(initials(st.name))}</i>${esc(st.name)}${st.role ? ' · ' + esc(st.role) : ''}</span>`).join('')}</div>`;
            box.hidden = false;
            if (!r.id) editingDemo = r.key;
        }
        open();
    };
})();

/* ================= VIEW FULL DETAILS + EDIT ================= */
(function () {
    const REVIEWS = <?= json_encode(array_values(array_map(fn($r) => [
                        'key' => $r['key'], 'id' => $r['id'] ?? null, 'project_id' => $r['project_id'] ?? null,
                        'title' => $r['title'], 'team' => $r['team'], 'category' => $r['category'] ?? '',
                        'date' => date('M d, Y', $r['created']), 'rating' => (int)$r['rating'], 'skills' => $r['skills'],
                        'summary' => $r['summary'], 'improvements' => $r['improvements'], 'shared' => !empty($r['shared']),
                        'updated' => $r['updated'] ?? '', 'members' => $r['members'] ?? [],
                    ], $reviews)), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?>;
    const LABELS = { 1: 'Needs Work', 2: 'Fair', 3: 'Good', 4: 'Very Good', 5: 'Exceptional' };
    const COLORS = ['#1e3a5f', '#0f766e', '#7c3aed', '#b45309', '#be185d', '#2563eb', '#475569'];
    const OV_KEY = 'sb_demo_feedback_overrides';

    const esc = t => { const d = document.createElement('div'); d.textContent = t == null ? '' : String(t); return d.innerHTML; };
    const initials = n => String(n || '').trim().split(/\s+/).slice(0, 2).map(w => w.charAt(0).toUpperCase()).join('');
    const colorFor = n => { let h = 0; for (const c of String(n)) h = (h * 31 + c.charCodeAt(0)) >>> 0; return COLORS[h % COLORS.length]; };
    const loadOv = () => { try { return JSON.parse(localStorage.getItem(OV_KEY)) || {}; } catch (e) { return {}; } };
    const stars = (v, size) => { let h = ''; for (let i = 1; i <= 5; i++) {
        const icon = v >= i ? 'star' : (v >= i - 0.5 ? 'star_half' : 'star');
        h += `<span class="material-symbols-outlined fb-star ${v >= i - 0.5 ? 'on' : ''}" style="font-size:${size}px;">${icon}</span>`; } return h; };

    const effective = i => Object.assign({}, REVIEWS[i], REVIEWS[i].id ? {} : (loadOv()[REVIEWS[i].key] || {}));

    // update a card on the page after a demo edit
    function updateCard(i) {
        const r = effective(i);
        const card = document.querySelector('.fb-card[data-fb-index="' + i + '"]');
        if (!card) return;
        card.dataset.rating = r.rating;
        card.querySelector('.fb-rating .stars').innerHTML = stars(r.rating, 20);
        card.querySelector('.fb-rating-label').textContent = LABELS[r.rating];
        const rows = card.querySelectorAll('.fb-comp-row');
        Object.values(r.skills).forEach((v, k) => {
            if (!rows[k]) return;
            rows[k].querySelector('strong').textContent = Number(v).toFixed(1);
            rows[k].querySelector('.fb-track span').style.width = Math.round(v / 5 * 100) + '%';
        });
        card.querySelector('.fb-summary').textContent = r.summary;
        card.querySelector('.fb-chips').innerHTML = r.improvements.map(x => `<span class="fb-chip">${esc(x)}</span>`).join('');
    }
    REVIEWS.forEach((r, i) => { if (!r.id && loadOv()[r.key]) updateCard(i); });

    window.fbSaveDemo = function (key, data) {
        const all = loadOv(); all[key] = data;
        try { localStorage.setItem(OV_KEY, JSON.stringify(all)); } catch (e) {}
        const i = REVIEWS.findIndex(r => r.key === key);
        updateCard(i);
        const t = document.createElement('div');
        t.className = 'flash-toast flash-success';
        t.innerHTML = '<div class="flash-icon-wrap"><span class="material-symbols-outlined">check_circle</span></div><div class="flash-content"><div class="flash-title" style="font-weight:700;font-size:14px;">Feedback updated</div></div>';
        document.body.appendChild(t);
        setTimeout(() => { t.style.transition = 'opacity .4s'; t.style.opacity = '0'; setTimeout(() => t.remove(), 400); }, 2500);
    };

    // ---------- details popup ----------
    const modal = document.getElementById('fbViewModal');
    let current = -1;

    function openView(i) {
        current = i;
        const r = effective(i);
        const entries = Object.entries(r.skills);
        const avg = entries.reduce((a, [, v]) => a + Number(v), 0) / entries.length;
        const best = entries.reduce((a, b) => (Number(b[1]) > Number(a[1]) ? b : a));
        const low  = entries.reduce((a, b) => (Number(b[1]) < Number(a[1]) ? b : a));

        document.getElementById('fvTitle').textContent = r.title;
        document.getElementById('fvMeta').innerHTML =
            `<span><span class="material-symbols-outlined">groups</span>${esc(r.team)}</span>` +
            (r.category ? `<span><span class="material-symbols-outlined">category</span>${esc(r.category)}</span>` : '') +
            `<span><span class="material-symbols-outlined">calendar_today</span>${esc(r.date)}</span>`;
        document.getElementById('fvScore').innerHTML = r.rating + '<small>/5</small>';
        document.getElementById('fvStars').innerHTML = stars(r.rating, 16);
        document.getElementById('fvLabel').textContent = LABELS[r.rating];

        document.getElementById('fvBody').innerHTML = `
            <div class="fv-stats">
                <div class="fv-stat"><div class="fv-stat-icon" style="background:#dbeafe;color:#1d4ed8;"><span class="material-symbols-outlined">analytics</span></div>
                    <div><div class="fv-stat-label">Competency Average</div><div class="fv-stat-value">${avg.toFixed(1)} / 5</div></div></div>
                <div class="fv-stat"><div class="fv-stat-icon" style="background:#dcfce7;color:#15803d;"><span class="material-symbols-outlined">trending_up</span></div>
                    <div><div class="fv-stat-label">Strongest Area</div><div class="fv-stat-value">${esc(best[0])}</div></div></div>
                <div class="fv-stat"><div class="fv-stat-icon" style="background:#ffedd5;color:#c2410c;"><span class="material-symbols-outlined">construction</span></div>
                    <div><div class="fv-stat-label">Needs Work</div><div class="fv-stat-value">${esc(low[0])}</div></div></div>
            </div>

            <div class="fv-card">
                <h4><span class="material-symbols-outlined">bar_chart</span> Competency Breakdown</h4>
                ${entries.map(([name, v]) => `
                    <div class="fv-comp"><span>${esc(name)}</span>
                        <div class="fb-track"><span class="${name === best[0] ? 'best' : (name === low[0] && low[1] < best[1] ? 'low' : '')}" style="width:${Math.round(v / 5 * 100)}%"></span></div>
                        <strong>${Number(v).toFixed(1)}</strong></div>`).join('')}
            </div>

            <div class="fv-card">
                <h4><span class="material-symbols-outlined">notes</span> Performance Summary</h4>
                <p>${esc(r.summary)}</p>
            </div>

            <div class="fv-card">
                <h4><span class="material-symbols-outlined">lightbulb</span> Suggested Improvements</h4>
                ${r.improvements.length ? `<div class="fb-chips">${r.improvements.map(x => `<span class="fb-chip">${esc(x)}</span>`).join('')}</div>`
                                        : '<div class="fv-empty">No improvements suggested.</div>'}
            </div>

            <div class="fv-two">
                <div class="fv-card">
                    <h4><span class="material-symbols-outlined">group</span> Team Members</h4>
                    ${r.members.length ? r.members.map(m => `
                        <div class="fv-member"><i style="background:${colorFor(m.name)}">${esc(initials(m.name))}</i>
                            <div><div class="fv-member-name">${esc(m.name)}</div><div class="fv-member-role">${esc(m.role || 'Team member')}</div></div></div>`).join('')
                        : '<div class="fv-empty">No member details.</div>'}
                </div>
                <div class="fv-card">
                    <h4><span class="material-symbols-outlined">info</span> Feedback Info</h4>
                    <div class="fv-info-row"><span>Submitted on</span><strong>${esc(r.date)}</strong></div>
                    <div class="fv-info-row"><span>Last edited</span><strong>${esc(r.updated || 'Not edited')}</strong></div>
                    <div class="fv-info-row"><span>Shared with students</span><strong style="color:${r.shared ? '#15803d' : '#6b7280'}">${r.shared ? 'Yes' : 'No'}</strong></div>
                    <div class="fv-info-row"><span>Team size</span><strong>${r.members.length} member${r.members.length === 1 ? '' : 's'}</strong></div>
                </div>
            </div>
        `;
        document.getElementById('fvBody').scrollTop = 0;
        modal.classList.add('open');
    }

    function closeView() { modal.classList.remove('open'); }
    modal.querySelectorAll('[data-fv-close]').forEach(b => b.addEventListener('click', closeView));
    modal.addEventListener('click', e => { if (e.target === modal) closeView(); });
    document.addEventListener('keydown', e => { if (e.key === 'Escape' && modal.classList.contains('open')) closeView(); });
    document.getElementById('fvPrint').addEventListener('click', () => window.print());
    document.getElementById('fvEdit').addEventListener('click', () => { closeView(); openEdit(current); });

    function openEdit(i) {
        if (!window.fbOpenForm) { alert('Editing is not available right now.'); return; }
        window.fbOpenForm(effective(i));
    }

    document.querySelectorAll('[data-fb-view]').forEach(b => b.addEventListener('click', () => openView(Number(b.dataset.fbView))));
    document.querySelectorAll('[data-fb-edit]').forEach(b => b.addEventListener('click', () => openEdit(Number(b.dataset.fbEdit))));
})();

/* hide the success / error message */
(function () {
    const t = document.getElementById('fbFlash');
    if (!t) return;
    setTimeout(() => {
        t.style.transition = 'opacity .4s ease'; t.style.opacity = '0';
        setTimeout(() => t.remove(), 400);
        history.replaceState(null, '', location.pathname);
    }, 3500);
})();
</script>

<footer class="footer">
    <div>&copy; 2026 SkillBridge. All rights reserved.</div>
    <div class="footer-links">
        <a href="#">Help Center</a>
        <a href="#">Privacy Policy</a>
        <a href="#">Terms of Service</a>
    </div>
</footer>

<?php include "../../../Includes/dash_footer.php"; ?>