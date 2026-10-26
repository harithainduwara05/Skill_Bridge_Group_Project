<?php
/*
| Organization backend - Dashboard: stat card counts, recent project posts, team progress (+ demo rows)
| Used by: Functions/Organization/dashboard.php
| (the page already loaded Config/db.php + Session/Session.php and checked the role)
*/

$user = current_user();
$organization_email = $user['email'];

// ---- Organization name for the "Welcome back" heading ----
$orgStmt = $conn->prepare("SELECT Name FROM organization WHERE Email = ?");
$orgStmt->bind_param("s", $organization_email);
$orgStmt->execute();
$orgRow  = $orgStmt->get_result()->fetch_assoc();
$orgName = $orgRow['Name'] ?? ($user['username'] ?? 'Organization');

// Status flow: Reviewing (waiting for Admin) -> Published (Admin accepted, students can see it)
// -> Active (set manually from Edit Project when the team is full) -> Completed.

// ---- Stat cards: real counts ----
$stmt = $conn->prepare("SELECT COUNT(*) FROM projects WHERE organization_email=?");
$stmt->bind_param("s", $organization_email);
$stmt->execute();
$totalProjects = (int)$stmt->get_result()->fetch_row()[0];

$stmt = $conn->prepare("SELECT COUNT(*) FROM projects WHERE organization_email=? AND status IN ('reviewing','published','accepted','active','inprogress')");
$stmt->bind_param("s", $organization_email);
$stmt->execute();
$activeProjects = (int)$stmt->get_result()->fetch_row()[0];

// "Proposals Received" = proposals students sent for this organization's projects
$stmt = $conn->prepare("SELECT COUNT(*) FROM project_proposals pa
                         JOIN projects p ON pa.project_id = p.id
                         WHERE p.organization_email = ?");
$stmt->bind_param("s", $organization_email);
$stmt->execute();
$proposalsReceived = (int)$stmt->get_result()->fetch_row()[0];

// ---- Unread notifications count ----
$stmt = $conn->prepare("SELECT COUNT(*) FROM notifications WHERE Email = ? AND status = 'Unread'");
$stmt->bind_param("s", $organization_email);
$stmt->execute();
$unreadNotifCount = (int)$stmt->get_result()->fetch_row()[0];

// ---- Recent Project Posts (latest 5) ----
// (category + keywords come from project_categories / project_skills, see org_db.php)
require_once __DIR__ . '/org_db.php';
$stmt = $conn->prepare(str_replace("\n    FROM projects p", ",
                                (SELECT COUNT(*) FROM project_proposals pa WHERE pa.project_id = p.id) AS proposal_count,
                                (SELECT COUNT(*) FROM student_projects sp WHERE sp.project_id = p.id) AS team_count
    FROM projects p", ORG_PROJECT_SELECT)
                       . " WHERE p.organization_email = ? ORDER BY p.posted_at DESC LIMIT 5");
$stmt->bind_param("s", $organization_email);
$stmt->execute();
$recentProjects = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
// lowercase, because the Admin saves some statuses as 'Hold'
// (Admin's 'Active' / old 'accepted' = Published; the organization's own Active is 'inprogress')
foreach ($recentProjects as &$rp) {
    $rp['status'] = strtolower(trim($rp['status'] ?? ''));
    if (in_array($rp['status'], ['active', 'approved', 'accepted'], true)) $rp['status'] = 'published';
    // Active only when the team is full: an Active project whose team is not full
    // (old data, a student left, or Students Required was raised) is shown as Published.
    if ($rp['status'] === 'inprogress' && (int)$rp['team_count'] < max(1, (int)($rp['members'] ?? 1))) {
        $rp['status'] = 'published';
    }
}
unset($rp);

// Human-readable label for a status value (DB stores 'inprogress' for what's shown as "Active")
function statusLabel($status) {
    $labels = ['reviewing' => 'Reviewing', 'published' => 'Published', 'inprogress' => 'Active', 'hold' => 'Hold', 'rejected' => 'Rejected', 'completed' => 'Completed', 'draft' => 'Draft'];
    return $labels[$status] ?? ucfirst($status);
}

// ---- Pad with demo/fake data for presentation so the table always shows 5 rows ----
$fakeProjectPool = [
    ['title' => 'AI Chatbot for Student Support', 'keywords' => 'Python, NLP, Flask', 'category' => 'AI/ML', 'posted_at' => date('Y-m-d', strtotime('-1 day')), 'proposal_count' => 5, 'status' => 'reviewing'],
    ['title' => 'Mobile Attendance Tracker', 'keywords' => 'Flutter, Firebase', 'category' => 'Mobile Development', 'posted_at' => date('Y-m-d', strtotime('-3 day')), 'proposal_count' => 3, 'status' => 'reviewing'],
    ['title' => 'Portfolio Website Builder', 'keywords' => 'React, Tailwind CSS', 'category' => 'Web Development', 'posted_at' => date('Y-m-d', strtotime('-10 day')), 'proposal_count' => 2, 'status' => 'reviewing'],
    ['title' => 'Campus Event Management System', 'keywords' => 'Laravel, MySQL', 'category' => 'Web Development', 'posted_at' => date('Y-m-d', strtotime('-14 day')), 'proposal_count' => 6, 'status' => 'completed'],
    ['title' => 'Smart Library Assistant', 'keywords' => 'Java, Spring Boot', 'category' => 'Software Engineering', 'posted_at' => date('Y-m-d', strtotime('-18 day')), 'proposal_count' => 4, 'status' => 'inprogress'],
];
$needed = 5 - count($recentProjects);
if ($needed > 0) {
    // Demo/fake rows are only for filling out this table visually.
    // "Total Projects" / "Active Projects" stay based on the real DB
    // count, since that's what Manage Projects (View All) actually lists.
    $recentProjects = array_merge($recentProjects, array_slice($fakeProjectPool, 0, $needed));
}

// ---- Team Progress (mini team cards: lead, progress, status, deadline) ----
$stmt = $conn->prepare("SELECT p.id, p.title, p.deadline,
                                COUNT(sp.student_project_id) AS team_size,
                                AVG(sp.progress) AS avg_progress,
                                SUM(CASE WHEN sp.status = 'Completed' THEN 1 ELSE 0 END) AS completed_count,
                                (SELECT s.Name FROM student_projects sp2
                                    JOIN student s ON s.Email = sp2.Email
                                    WHERE sp2.project_id = p.id AND sp2.role LIKE '%Lead%'
                                    LIMIT 1) AS lead_name
                         FROM projects p
                         JOIN student_projects sp ON sp.project_id = p.id
                         WHERE p.organization_email = ?
                         GROUP BY p.id, p.title, p.deadline
                         ORDER BY p.posted_at DESC
                         LIMIT 3");
$stmt->bind_param("s", $organization_email);
$stmt->execute();
$teamProgressRows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

foreach ($teamProgressRows as &$tpRow) {
    $tpRow['avg_progress'] = (int)round((float)$tpRow['avg_progress']);

    // Work out a status badge: Completed / Behind Schedule / On Track
    $deadlineTs = $tpRow['deadline'] ? strtotime($tpRow['deadline']) : false;
    if ((int)$tpRow['completed_count'] === (int)$tpRow['team_size']) {
        $tpRow['team_status'] = 'Completed';
    } elseif ($deadlineTs && $deadlineTs < time()) {
        $tpRow['team_status'] = 'Behind Schedule';
    } else {
        $tpRow['team_status'] = 'On Track';
    }

    // Days left / overdue text
    if ($deadlineTs) {
        $daysDiff = (int)ceil(($deadlineTs - time()) / 86400);
        $tpRow['days_text'] = $daysDiff >= 0 ? "$daysDiff days left" : (abs($daysDiff) . " days delayed");
    } else {
        $tpRow['days_text'] = null;
    }

    if (!$tpRow['lead_name']) {
        $tpRow['lead_name'] = 'Unassigned';
    }
}
unset($tpRow);

// ---- Pad with demo/fake teams so the panel always shows a nice full set ----
$fakeTeamPool = [
    ['title' => 'AI-Driven Supply Chain Optimizer', 'lead_name' => 'Sarah Chen', 'team_size' => 4, 'avg_progress' => 72, 'team_status' => 'On Track', 'deadline' => 'Oct 24, 2026', 'days_text' => '14 days left'],
    ['title' => 'Blockchain Academic Credentials', 'lead_name' => 'Marcus Thorne', 'team_size' => 3, 'avg_progress' => 45, 'team_status' => 'Behind Schedule', 'deadline' => 'Oct 15, 2026', 'days_text' => '5 days delayed'],
    ['title' => 'Predictive Maintenance for Smart Cities', 'lead_name' => 'Elena Vance', 'team_size' => 4, 'avg_progress' => 100, 'team_status' => 'Completed', 'deadline' => 'Sep 18, 2026', 'days_text' => null],
    ['title' => 'Cloud Infrastructure Automation', 'lead_name' => 'Kevin Zhang', 'team_size' => 3, 'avg_progress' => 85, 'team_status' => 'On Track', 'deadline' => 'Nov 02, 2026', 'days_text' => '22 days left'],
];
$neededTeams = 3 - count($teamProgressRows);
if ($neededTeams > 0) {
    $teamProgressRows = array_merge($teamProgressRows, array_slice($fakeTeamPool, 0, $neededTeams));
}