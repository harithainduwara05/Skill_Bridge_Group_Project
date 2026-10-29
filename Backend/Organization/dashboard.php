<?php
/*
| Organization backend - Dashboard: stat card counts, recent project posts, team progress
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
                                (SELECT COUNT(*) FROM org_teams t JOIN org_team_members m ON m.team_id = t.id WHERE t.project_id = p.id) AS team_count
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

// ---- Team Progress (latest 3 teams from org_teams, same status / progress as the Teams page) ----
orgRefreshTeamStatuses($conn, $organization_email);
$stmt = $conn->prepare("SELECT t.id, t.name, t.deadline, t.status, p.title,
                                COALESCE(s.Name, t.leader_email) AS lead_name,
                                (SELECT COUNT(*) FROM org_team_members m WHERE m.team_id = t.id) AS team_size,
                                (SELECT COUNT(*) FROM team_tasks k WHERE k.team_id = t.id) AS task_total,
                                (SELECT COUNT(*) FROM team_tasks k WHERE k.team_id = t.id AND k.status = 'Done') AS task_done
                         FROM org_teams t
                         JOIN projects p ON p.id = t.project_id
                         LEFT JOIN student s ON s.Email = t.leader_email
                         WHERE p.organization_email = ?
                         ORDER BY t.created_at DESC
                         LIMIT 3");
$stmt->bind_param("s", $organization_email);
$stmt->execute();
$teamProgressRows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

foreach ($teamProgressRows as &$tpRow) {
    $state = $tpRow['status'] ?: 'ontrack';
    $tpRow['title'] = $tpRow['name'] . ' - ' . $tpRow['title'];
    $tpRow['team_status'] = ['ontrack' => 'On Track', 'behind' => 'Behind Schedule', 'completed' => 'Completed'][$state] ?? 'On Track';
    // progress = Done tasks / all tasks (100% when the team is completed)
    $tpRow['avg_progress'] = $state === 'completed' ? 100
        : ((int)$tpRow['task_total'] > 0 ? (int)round($tpRow['task_done'] / $tpRow['task_total'] * 100) : 0);

    $tpRow['days_text'] = null;
    if ($state !== 'completed' && !empty($tpRow['deadline'])) {
        $days = (int)floor((strtotime($tpRow['deadline']) - strtotime('today')) / 86400);
        $tpRow['days_text'] = $days >= 0 ? $days . ' day' . ($days === 1 ? '' : 's') . ' left' : abs($days) . ' days delayed';
    }
    $tpRow['deadline'] = !empty($tpRow['deadline']) ? date('M d, Y', strtotime($tpRow['deadline'])) : '';
}
unset($tpRow);