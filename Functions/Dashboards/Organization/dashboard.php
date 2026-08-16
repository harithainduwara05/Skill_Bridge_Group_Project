<?php

include "../../../Config/db.php";
include "../../../Session/Session.php";

require_role('organization');

$user = current_user();
$organization_email = $user['email'];

// ---- Stat cards: real counts ----
$stmt = $conn->prepare("SELECT COUNT(*) FROM projects WHERE organization_email=?");
$stmt->bind_param("s", $organization_email);
$stmt->execute();
$totalProjects = (int)$stmt->get_result()->fetch_row()[0];

$stmt = $conn->prepare("SELECT COUNT(*) FROM projects WHERE organization_email=? AND status IN ('open','reviewing','inprogress')");
$stmt->bind_param("s", $organization_email);
$stmt->execute();
$activeProjects = (int)$stmt->get_result()->fetch_row()[0];

$stmt = $conn->prepare("SELECT COUNT(*) FROM student_projects sp
                         JOIN projects p ON sp.project_id = p.id
                         WHERE p.organization_email = ?");
$stmt->bind_param("s", $organization_email);
$stmt->execute();
$proposalsReceived = (int)$stmt->get_result()->fetch_row()[0];

// ---- Notifications (real, now that notifications.Email can reference any user) ----
$stmt = $conn->prepare("SELECT * FROM notifications WHERE Email = ? ORDER BY created_at DESC LIMIT 5");
$stmt->bind_param("s", $organization_email);
$stmt->execute();
$orgNotifications = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$stmt = $conn->prepare("SELECT COUNT(*) FROM notifications WHERE Email = ? AND status = 'Unread'");
$stmt->bind_param("s", $organization_email);
$stmt->execute();
$unreadNotifCount = (int)$stmt->get_result()->fetch_row()[0];

// ---- Recent Project Posts (latest 4) ----
$stmt = $conn->prepare("SELECT p.*,
                                (SELECT COUNT(*) FROM student_projects sp WHERE sp.project_id = p.id) AS proposal_count
                         FROM projects p
                         WHERE p.organization_email = ?
                         ORDER BY p.posted_at DESC
                         LIMIT 4");
$stmt->bind_param("s", $organization_email);
$stmt->execute();
$recentProjects = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// ---- Proposal Status breakdown (In Review / Accepted / Rejected) ----
$stmt = $conn->prepare("SELECT sp.status, COUNT(*) AS cnt
                         FROM student_projects sp
                         JOIN projects p ON sp.project_id = p.id
                         WHERE p.organization_email = ?
                         GROUP BY sp.status");
$stmt->bind_param("s", $organization_email);
$stmt->execute();
$statusRows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$reviewCount = $acceptedCount = $rejectedCount = 0;
foreach ($statusRows as $row) {
    $s = strtolower(trim($row['status'] ?? ''));
    if ($s === 'accepted') {
        $acceptedCount += (int)$row['cnt'];
    } elseif ($s === 'rejected') {
        $rejectedCount += (int)$row['cnt'];
    } else {
        // Anything else (Active, Pending, NULL, etc.) counts as "In Review"
        $reviewCount += (int)$row['cnt'];
    }
}
$proposalTotal = $reviewCount + $acceptedCount + $rejectedCount;
$reviewPct   = $proposalTotal > 0 ? round(($reviewCount   / $proposalTotal) * 100) : 0;
$acceptedPct = $proposalTotal > 0 ? round(($acceptedCount / $proposalTotal) * 100) : 0;
$rejectedPct = $proposalTotal > 0 ? max(0, 100 - $reviewPct - $acceptedPct) : 0;

include "../../../Includes/org_sidebar.php";
include "../../../Includes/dash_header.php";

?>

<main class="content">
    <div class="dashboard-header">

        <div>
            <h1>Organization Dashboard</h1>
            <p>Welcome back, <?php echo htmlspecialchars($user['username'] ?? 'there'); ?>, here's what's happening with your projects today.</p>
        </div>

        <a href="#" class="btn-outline">
            <span class="material-symbols-outlined">download</span>
            Download Report
        </a>

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

    <!-- ===================== PROJECT POSTS + PROPOSAL STATUS ===================== -->
    <div class="dashboard-grid">

        <div class="card">
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
                        <?php if (empty($recentProjects)): ?>
                        <tr>
                            <td colspan="4" style="text-align:center; color:#9ca3af; padding:24px 0;">
                                No projects posted yet. <a href="post.php">Post your first project</a>.
                            </td>
                        </tr>
                        <?php else: foreach ($recentProjects as $rp): ?>
                        <tr>
                            <td>
                                <div class="user-details">
                                    <div class="user-name"><?= htmlspecialchars($rp['title']) ?></div>
                                    <div class="user-email"><?= htmlspecialchars($rp['keywords'] ?: $rp['category'] ?: '—') ?></div>
                                </div>
                            </td>
                            <td><?= htmlspecialchars(date('M d, Y', strtotime($rp['posted_at']))) ?></td>
                            <td><?= (int)$rp['proposal_count'] ?></td>
                            <td><span class="badge-status <?= htmlspecialchars($rp['status']) ?>"><?= htmlspecialchars(ucfirst($rp['status'])) ?></span></td>
                        </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div style="display:flex; flex-direction:column; gap:16px;">

            <div class="card">
                <div class="card-header">
                    <h3>Proposal Status</h3>
                </div>
                <div class="card-body">
                    <?php if ($proposalTotal === 0): ?>
                        <p style="text-align:center; color:#9ca3af; padding:20px 0;">No proposals yet.</p>
                    <?php else: ?>
                    <div class="donut-chart" style="--p-review:<?= $reviewPct ?>; --p-accepted:<?= $acceptedPct ?>; --p-rejected:<?= $rejectedPct ?>;">
                        <div class="donut-center">
                            <strong><?= $proposalTotal ?></strong>
                            <span>Total</span>
                        </div>
                    </div>
                    <ul class="donut-legend">
                        <li><span class="donut-dot review"></span> In Review <b><?= $reviewPct ?>%</b></li>
                        <li><span class="donut-dot accepted"></span> Accepted <b><?= $acceptedPct ?>%</b></li>
                        <li><span class="donut-dot rejected"></span> Rejected <b><?= $rejectedPct ?>%</b></li>
                    </ul>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3>Notifications <?php if ($unreadNotifCount > 0): ?><span class="badge">New</span><?php endif; ?></h3>
                </div>
                <ul class="activity-list" style="padding: 0 20px;">
                    <?php if (empty($orgNotifications)): ?>
                    <li class="activity-item" style="border:none;">
                        <div class="activity-content">
                            <p style="color:#9ca3af;">No notifications yet.</p>
                        </div>
                    </li>
                    <?php else: foreach ($orgNotifications as $n):
                        $icon = 'notifications';
                        $iconClass = 'blue';
                        if (($n['type'] ?? '') === 'proposal') { $icon = 'description'; $iconClass = 'blue'; }
                        elseif (($n['type'] ?? '') === 'acceptance') { $icon = 'person'; $iconClass = 'orange'; }
                    ?>
                    <li class="activity-item">
                        <div class="activity-icon <?= $iconClass ?>">
                            <span class="material-symbols-outlined"><?= $icon ?></span>
                        </div>
                        <div class="activity-content">
                            <p><?= htmlspecialchars($n['title'] ?: $n['message']) ?></p>
                            <span class="activity-time"><?= htmlspecialchars(date('M d, Y g:i A', strtotime($n['created_at']))) ?></span>
                        </div>
                    </li>
                    <?php endforeach; endif; ?>
                </ul>
                <a href="notifications.php" class="see-all-link">See All Notifications</a>
            </div>

        </div>

    </div>

    <!-- ===================== CTA BANNER ===================== -->
    <div class="full-width-section">
        <div class="cta-banner">
            <div class="cta-text">
                <h3>Scale Your Projects with Top Talent</h3>
                <p>SkillBridge connects you with the brightest students in the industry. Ready to start your next big initiative?</p>
                <a href="post.php" class="btn-sm primary">
                    <span class="material-symbols-outlined" style="font-size:16px;">rocket_launch</span>
                    Post New Project
                </a>
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