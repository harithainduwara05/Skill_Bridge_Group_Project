<?php

include "../../../Config/db.php";
include "../../../Session/Session.php";

require_role('organization');

$user = current_user();
$organization_email = $user['email'];

// ---- Organization name for the "Welcome back" heading ----
$orgStmt = $conn->prepare("SELECT Name FROM organization WHERE Email = ?");
$orgStmt->bind_param("s", $organization_email);
$orgStmt->execute();
$orgRow  = $orgStmt->get_result()->fetch_assoc();
$orgName = $orgRow['Name'] ?? ($user['username'] ?? 'Organization');

// Projects are NOT promoted automatically any more.
// A new project starts as "Reviewing" and stays Reviewing even when the team is full;
// the organization changes it to "Active" manually from Edit Project.

// ---- Stat cards: real counts ----
$stmt = $conn->prepare("SELECT COUNT(*) FROM projects WHERE organization_email=?");
$stmt->bind_param("s", $organization_email);
$stmt->execute();
$totalProjects = (int)$stmt->get_result()->fetch_row()[0];

$stmt = $conn->prepare("SELECT COUNT(*) FROM projects WHERE organization_email=? AND status IN ('reviewing','inprogress')");
$stmt->bind_param("s", $organization_email);
$stmt->execute();
$activeProjects = (int)$stmt->get_result()->fetch_row()[0];

$stmt = $conn->prepare("SELECT COUNT(*) FROM student_projects sp
                         JOIN projects p ON sp.project_id = p.id
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
$stmt = $conn->prepare("SELECT p.*,
                                (SELECT COUNT(*) FROM student_projects sp WHERE sp.project_id = p.id) AS proposal_count
                         FROM projects p
                         WHERE p.organization_email = ?
                         ORDER BY p.posted_at DESC
                         LIMIT 5");
$stmt->bind_param("s", $organization_email);
$stmt->execute();
$recentProjects = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Human-readable label for a status value (DB stores 'inprogress' for what's shown as "Active")
function statusLabel($status) {
    $labels = ['reviewing' => 'Reviewing', 'inprogress' => 'Active', 'closed' => 'Closed', 'draft' => 'Draft'];
    return $labels[$status] ?? ucfirst($status);
}

// ---- Pad with demo/fake data for presentation so the table always shows 5 rows ----
$fakeProjectPool = [
    ['title' => 'AI Chatbot for Student Support', 'keywords' => 'Python, NLP, Flask', 'category' => 'AI/ML', 'posted_at' => date('Y-m-d', strtotime('-1 day')), 'proposal_count' => 5, 'status' => 'reviewing'],
    ['title' => 'Mobile Attendance Tracker', 'keywords' => 'Flutter, Firebase', 'category' => 'Mobile Development', 'posted_at' => date('Y-m-d', strtotime('-3 day')), 'proposal_count' => 3, 'status' => 'reviewing'],
    ['title' => 'Portfolio Website Builder', 'keywords' => 'React, Tailwind CSS', 'category' => 'Web Development', 'posted_at' => date('Y-m-d', strtotime('-10 day')), 'proposal_count' => 2, 'status' => 'reviewing'],
    ['title' => 'Campus Event Management System', 'keywords' => 'Laravel, MySQL', 'category' => 'Web Development', 'posted_at' => date('Y-m-d', strtotime('-14 day')), 'proposal_count' => 6, 'status' => 'closed'],
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

include "../../../Includes/org_sidebar.php";
include "../../../Includes/dash_header.php";

?>

<style>
    /* Stat cards + Recent Project Posts/Team Progress + CTA banner
       all share the same left/right edges (28px side gutter). */
    .org-main-row {
        display: flex;
        gap: 14px;
        align-items: stretch;
        flex-wrap: wrap;
        padding: 0 28px;
        margin-bottom: 16px;
        box-sizing: border-box;
    }
    .org-main-row > .card { margin-bottom: 0; }
    .stats-grid { box-sizing: border-box; }

    @media (max-width: 1200px) {
        .org-main-row { padding: 0 20px; }
        .full-width-section { padding: 0 20px 16px; }
    }
    @media (max-width: 768px) {
        .org-main-row { padding: 0 16px; }
        .full-width-section { padding: 0 16px 16px; }
    }
</style>

<main class="content">
    <div class="dashboard-header">

        <div>
            <h1>Welcome back, <?php echo htmlspecialchars($orgName); ?></h1>
            <p>Here's what's happening with your projects today.</p>
        </div>

    </div>

    <!-- ===================== STAT CARDS ===================== -->
    <div class="stats-grid">

        <div class="stat-card">
            <div class="stat-card-top">
                <div class="stat-icon blue">
                    <span class="material-symbols-outlined">rocket_launch</span>
                </div>
            </div>
            <div class="stat-info">
                <div class="stat-label">Total Projects</div>
                <div class="stat-value"><?= $totalProjects ?></div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-card-top">
                <div class="stat-icon orange">
                    <span class="material-symbols-outlined">work</span>
                </div>
            </div>
            <div class="stat-info">
                <div class="stat-label">Active Projects</div>
                <div class="stat-value"><?= $activeProjects ?></div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-card-top">
                <div class="stat-icon slate">
                    <span class="material-symbols-outlined">description</span>
                </div>
            </div>
            <div class="stat-info">
                <div class="stat-label">Proposals Received</div>
                <div class="stat-value"><?= $proposalsReceived ?></div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-card-top">
                <div class="stat-icon navy">
                    <span class="material-symbols-outlined">mail</span>
                </div>
            </div>
            <div class="stat-info">
                <div class="stat-label">Unread Notifications</div>
                <div class="stat-value"><?= $unreadNotifCount ?></div>
            </div>
        </div>

    </div>

    <!-- ===================== PROJECT POSTS + TEAM PROGRESS (TWO SEPARATE CARDS) ===================== -->
    <div class="org-main-row">

        <!-- Left: Recent Project Posts -->
        <div class="card" style="flex:2; min-width:320px; margin-bottom:0;">
            <div class="card-header">
                <h3>Recent Project Posts</h3>
                <a href="manage_projects.php" class="btn-link">View All</a>
            </div>
            <div class="card-body">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Project Name</th>
                            <th>Date Posted</th>
                            <th>Proposals</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentProjects as $rp): ?>
                        <tr>
                            <td>
                                <div class="user-details">
                                    <div class="user-name"><?= htmlspecialchars($rp['title']) ?></div>
                                    <div class="user-email"><?= htmlspecialchars($rp['keywords'] ?: $rp['category'] ?: '—') ?></div>
                                </div>
                            </td>
                            <td><?= htmlspecialchars(date('M d, Y', strtotime($rp['posted_at']))) ?></td>
                            <td><?= (int)$rp['proposal_count'] ?></td>
                            <td><span class="badge-status <?= htmlspecialchars($rp['status']) ?>"><?= htmlspecialchars(statusLabel($rp['status'])) ?></span></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Right: Team Progress -->
        <div class="card" style="flex:1; min-width:220px; margin-bottom:0;">
            <div class="card-header">
                <h3>Team Progress</h3>
                <a href="teams.php" class="btn-link">View All</a>
            </div>
            <div class="card-body" style="display:flex; flex-direction:column; gap:10px; padding-top:8px;">
                <?php if (empty($teamProgressRows)): ?>
                    <p style="text-align:center; color:#9ca3af; padding:20px 0;">No teams assigned yet.</p>
                <?php else: ?>
                    <?php foreach ($teamProgressRows as $tpRow):
                        $pct = $tpRow['avg_progress'];
                        $statusClass = $tpRow['team_status'] === 'Completed' ? 'completed'
                                     : ($tpRow['team_status'] === 'Behind Schedule' ? 'behind' : 'on-track');
                    ?>
                    <div class="mini-team-card">
                        <div class="mini-team-card-top">
                            <span class="mini-team-title"><?= htmlspecialchars($tpRow['title']) ?></span>
                            <span class="mini-team-badge <?= $statusClass ?>"><?= htmlspecialchars($tpRow['team_status']) ?></span>
                        </div>
                        <div class="mini-team-lead">Lead: <?= htmlspecialchars($tpRow['lead_name']) ?> · <?= (int)$tpRow['team_size'] ?> member<?= $tpRow['team_size'] == 1 ? '' : 's' ?></div>
                        <div class="progress-bar-container">
                            <div class="progress-bar wide">
                                <div class="progress-bar-fill <?= $statusClass ?>" style="width:<?= $pct ?>%;"></div>
                            </div>
                            <span class="progress-text"><?= $pct ?>%</span>
                        </div>
                        <?php if ($tpRow['days_text']): ?>
                        <div class="mini-team-deadline <?= strpos($tpRow['days_text'], 'delayed') !== false ? 'overdue' : '' ?>">
                            <?= $tpRow['deadline'] ? htmlspecialchars($tpRow['deadline']) . ' · ' : '' ?><?= htmlspecialchars($tpRow['days_text']) ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

    </div>

    <!-- ===================== CTA BANNER ===================== -->
    <div class="full-width-section">
        <div class="cta-banner">
            <div class="cta-text">
                <h3>Scale Your Projects with Top Talent</h3>
                <p>SkillBridge connects you with the brightest students in the industry. Ready to start your next big initiative?</p>
            </div>
        </div>
    </div>

</main>

<footer class="footer">
    <div>&copy; 2026 SkillBridge. All rights reserved.</div>
    <div class="footer-links">
        <a href="#">Help Center</a>
        <a href="#">Privacy Policy</a>
        <a href="#">Terms of Service</a>
    </div>
</footer>

<?php include "../../../Includes/dash_footer.php"; ?>