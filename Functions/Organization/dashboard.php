<?php

include "../../Config/db.php";
include "../../Session/Session.php";

require_role('organization');

// Backend (PHP + SQL) of this page
require_once __DIR__ . "/../../Backend/Organization/dashboard.php";

$extra_css = '<link rel="stylesheet" href="../../Assets/CSS/Organization/dashboard.css">';
include "../../Includes/org_sidebar.php";
include "../../Includes/dash_header.php";

?>


<main class="content">
    <div class="dashboard-header">

        <div>
            <h1 class="welcome-heading">Welcome back, <?php echo htmlspecialchars($orgName); ?></h1>
            <p class="welcome-sub">Here's what's happening with your projects today.</p>
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
        <a href="/Skill_Bridge_Group_Project/help_center.php">Help Center</a>
        <a href="/Skill_Bridge_Group_Project/help_center.php#knowledgeBaseSection">Privacy Policy</a>
        <a href="/Skill_Bridge_Group_Project/help_center.php#knowledgeBaseSection">Terms of Service</a>
    </div>
</footer>

<?php include "../../Includes/dash_footer.php"; ?>