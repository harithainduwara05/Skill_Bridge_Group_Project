<?php

include "../../../Config/db.php";
include "../../../Session/Session.php";

is_logged_in();
$user=current_user();

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
                <span class="stat-trend up">↑ 12%</span>
            </div>
            <div class="stat-info">
                <div class="stat-label">Total Projects</div>
                <div class="stat-value">24</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-card-top">
                <div class="stat-icon orange">
                    <span class="material-symbols-outlined">work</span>
                </div>
                <span class="stat-trend up">Stable</span>
            </div>
            <div class="stat-info">
                <div class="stat-label">Active Projects</div>
                <div class="stat-value">08</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-card-top">
                <div class="stat-icon slate">
                    <span class="material-symbols-outlined">description</span>
                </div>
                <span class="stat-trend up">+45</span>
            </div>
            <div class="stat-info">
                <div class="stat-label">Proposals Received</div>
                <div class="stat-value">156</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-card-top">
                <div class="stat-icon navy">
                    <span class="material-symbols-outlined">mail</span>
                </div>
                <span class="notification-dot" style="position:static;border:none;"></span>
            </div>
            <div class="stat-info">
                <div class="stat-label">Unread Messages</div>
                <div class="stat-value">12</div>
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
                        <tr>
                            <td>
                                <div class="user-details">
                                    <div class="user-name">Cloud Migration UI/UX</div>
                                    <div class="user-email">DevOps, React, Figma</div>
                                </div>
                            </td>
                            <td>Oct 12, 2023</td>
                            <td>24</td>
                            <td><span class="badge-status active">Active</span></td>
                        </tr>
                        <tr>
                            <td>
                                <div class="user-details">
                                    <div class="user-name">AI Model Optimization</div>
                                    <div class="user-email">Python, TensorFlow, AWS</div>
                                </div>
                            </td>
                            <td>Oct 08, 2023</td>
                            <td>15</td>
                            <td><span class="badge-status pending">Reviewing</span></td>
                        </tr>
                        <tr>
                            <td>
                                <div class="user-details">
                                    <div class="user-name">Cybersecurity Audit</div>
                                    <div class="user-email">Security+, Pentesting</div>
                                </div>
                            </td>
                            <td>Sep 28, 2023</td>
                            <td>42</td>
                            <td><span class="badge-role company">Closed</span></td>
                        </tr>
                        <tr>
                            <td>
                                <div class="user-details">
                                    <div class="user-name">Mobile App Redesign</div>
                                    <div class="user-email">Swift, Kotlin, UX</div>
                                </div>
                            </td>
                            <td>Oct 14, 2023</td>
                            <td>08</td>
                            <td><span class="badge-status active">Active</span></td>
                        </tr>
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
                    <div class="donut-chart" style="--p-review:65; --p-accepted:20; --p-rejected:15;">
                        <div class="donut-center">
                            <strong>156</strong>
                            <span>Total</span>
                        </div>
                    </div>
                    <ul class="donut-legend">
                        <li><span class="donut-dot review"></span> In Review <b>65%</b></li>
                        <li><span class="donut-dot accepted"></span> Accepted <b>20%</b></li>
                        <li><span class="donut-dot rejected"></span> Rejected <b>15%</b></li>
                    </ul>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3>Notifications <span class="badge">New</span></h3>
                </div>
                <ul class="activity-list" style="padding: 0 20px;">
                    <li class="activity-item">
                        <div class="activity-icon blue">
                            <span class="material-symbols-outlined">description</span>
                        </div>
                        <div class="activity-content">
                            <p>New proposal received for <strong>"Cloud Migration"</strong></p>
                            <span class="activity-time">2 minutes ago</span>
                        </div>
                    </li>
                    <li class="activity-item">
                        <div class="activity-icon orange">
                            <span class="material-symbols-outlined">person</span>
                        </div>
                        <div class="activity-content">
                            <p><strong>John Doe</strong> accepted the "AI Audit" offer</p>
                            <span class="activity-time">45 minutes ago</span>
                        </div>
                    </li>
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