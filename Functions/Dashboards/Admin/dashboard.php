<?php
include "../../../Config/db.php";
include "../../../Session/session.php";

require_login();
require_role('admin');
$user = current_user();


require_once "AdminBackend.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'dismiss') {
    $dashboardManager->update_db("status = 'DISMISSED' where id = " . intval($_POST['complaint_id']), "complain");
    echo "<script>window.location.href = 'dashboard.php';</script>";
    exit();
}


include "../../../Includes/admin_sidebar.php";
include "../../../Includes/dash_header.php";
?>

<main class="content">

    <!-- Dashboard Header -->
    <div class="dashboard-header">
        <div>
            <h1>
                Hi <?php echo $user['username']; ?>, Dashboard Overview
            </h1>
            <p>
                Manage users, track platform growth, and oversee ongoing collaborations.
            </p>
        </div>
        <!--<button class="btn-new-report">
                <span class="material-symbols-outlined" style="font-size:18px;">add</span>
                New Report
            </button>-->
    </div>


    <!-- Stats Cards -->
    <div class="stats-grid">

        <!-- Total Users -->
        <div class="stat-card">
            <div class="stat-card-top">
                <div class="stat-icon blue">
                    <span class="material-symbols-outlined">group</span>
                </div>
                <!--<span class="stat-trend up">↑ +12%</span>-->
            </div>
            <div class="stat-info">
                <div class="stat-label">Total Users</div>
                <div class="stat-value"><?php echo htmlspecialchars($totalUsers) ?></div>
            </div>
        </div>

        <!-- Active Universities -->
        <div class="stat-card">
            <div class="stat-card-top">
                <div class="stat-icon orange">
                    <span class="material-symbols-outlined">school</span>
                </div>
                <!--<span class="stat-trend up">↑ +5%</span>-->
            </div>
            <div class="stat-info">
                <div class="stat-label">Active Universities</div>
                <div class="stat-value"><?php echo $totUni ?></div>
            </div>
        </div>

        <!-- Ongoing Projects -->
        <div class="stat-card">
            <div class="stat-card-top">
                <div class="stat-icon slate">
                    <span class="material-symbols-outlined">cases</span>
                </div>
                <!--<span class="stat-trend down">↓ -2%</span>-->
            </div>
            <div class="stat-info">
                <div class="stat-label">Ongoing Projects</div>
                <div class="stat-value"><?php echo $tot_Ongoin_projects ?></div>
            </div>
        </div>

        <!-- Total Internships -->
        <div class="stat-card">
            <div class="stat-card-top">
                <div class="stat-icon navy">
                    <span class="material-symbols-outlined">description</span>
                </div>
                <!--<span class="stat-trend up">↑ +24%</span>-->
            </div>
            <div class="stat-info">
                <div class="stat-label">Total Internships</div>
                <div class="stat-value"><?php echo $tot_internship ?></div>
            </div>
        </div>

    </div>


    <!-- Chart + Activity Section -->
    <div class="dashboard-grid">

        <!-- User Growth Analytics Chart -->
        <div class="card">
            <div class="card-header">
                <div>
                    <h3>User Growth Analytics</h3>
                    <p class="card-subtitle">Active student enrollment vs industry partners</p>
                </div>
                <button class="filter-btn">
                    Last 6 Months
                    <span class="material-symbols-outlined" style="font-size:16px;">expand_more</span>
                </button>
            </div>

            <!-- Simple Bar Chart (CSS only) -->
            <div class="chart-bar-group">
                <div class="chart-bar-item">
                    <div style="display:flex; gap:4px; align-items:flex-end; height:100%;">
                        <div class="chart-bar blue" style="height:55%;"></div>
                        <div class="chart-bar light-blue" style="height:40%;"></div>
                    </div>
                    <span class="chart-bar-label">Jan</span>
                </div>
                <div class="chart-bar-item">
                    <div style="display:flex; gap:4px; align-items:flex-end; height:100%;">
                        <div class="chart-bar blue" style="height:75%;"></div>
                        <div class="chart-bar light-blue" style="height:55%;"></div>
                    </div>
                    <span class="chart-bar-label">Feb</span>
                </div>
                <div class="chart-bar-item">
                    <div style="display:flex; gap:4px; align-items:flex-end; height:100%;">
                        <div class="chart-bar blue" style="height:90%;"></div>
                        <div class="chart-bar light-blue" style="height:65%;"></div>
                    </div>
                    <span class="chart-bar-label">Mar</span>
                </div>
                <div class="chart-bar-item">
                    <div style="display:flex; gap:4px; align-items:flex-end; height:100%;">
                        <div class="chart-bar blue" style="height:65%;"></div>
                        <div class="chart-bar light-blue" style="height:50%;"></div>
                    </div>
                    <span class="chart-bar-label">Apr</span>
                </div>
                <div class="chart-bar-item">
                    <div style="display:flex; gap:4px; align-items:flex-end; height:100%;">
                        <div class="chart-bar blue" style="height:80%;"></div>
                        <div class="chart-bar light-blue" style="height:60%;"></div>
                    </div>
                    <span class="chart-bar-label">May</span>
                </div>
                <div class="chart-bar-item">
                    <div style="display:flex; gap:4px; align-items:flex-end; height:100%;">
                        <div class="chart-bar blue" style="height:100%;"></div>
                        <div class="chart-bar light-blue" style="height:70%;"></div>
                    </div>
                    <span class="chart-bar-label">Jun</span>
                </div>
            </div>

            <div class="chart-legend">
                <div class="chart-legend-item">
                    <span class="chart-legend-dot dark"></span>
                    Students
                </div>
                <div class="chart-legend-item">
                    <span class="chart-legend-dot light"></span>
                    Industry Partners
                </div>
            </div>
        </div>


        <!-- Recent Activity -->
        <div class="card">
            <div class="card-header">
                <h3>Recent Activity</h3>
                <a href="#" class="btn-link">View All</a>
            </div>
            <div class="card-body">
                <ul class="activity-list">

                    <li class="activity-item">
                        <div class="activity-icon blue">
                            <span class="material-symbols-outlined">person_add</span>
                        </div>
                        <div class="activity-content">
                            <p><strong>New Student Signup:</strong> David Miller from Stanford University.</p>
                            <span class="activity-time">2 minutes ago</span>
                        </div>
                    </li>
                </ul>
            </div>
        </div>

    </div>


    <!-- Recent Registrations + Urgent Complaints -->
    <div class="two-col-grid">

        <!-- Recent Registrations -->
        <div class="card">
            <div class="card-header">
                <h3>Recent Registrations</h3>
                <a href="users.php" class="btn-outline">View All Users</a>
            </div>
            <div class="card-body" style="padding:0;">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Role</th>
                            <th>Organization</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $limit = (count($allusers) < 5) ? count($allusers) : 5;
                        for ($x = 0; $x < $limit; $x++) {
                            ?>
                            <tr>
                                <td>
                                    <div class="user-info">
                                        <div class="user-details">
                                            <div class="user-name"><?php echo $allusers[$x]['user_name'] ?></div>
                                            <div class="user-email"><?php echo $allusers[$x]['email'] ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td><span class="badge-role student"><?php echo $allusers[$x]['role'] ?></span></td>
                                <td><?php echo $allusers[$x]['organization_name'] ?></td>
                                <td><span class="badge-status verified"><?php echo $allusers[$x]['status'] ?></span></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>


        <!-- Urgent Complaints -->
        <div class="card complaints-section">
            <div class="card-header">
                <div class="complaints-header">
                    <span class="warning-icon">⚠</span>
                    <h3>Urgent Complaints</h3>
                </div>
            </div>
            <div class="card-body">
                <?php
                $hasComplaints = false;
                $limit = (count($complains) < 5) ? count($complains) : 5;
                for ($i = 0; $i < $limit; $i++) {
                    if (($complains[$i]['status'] != "DISMISSED")) {
                        $hasComplaints = true;
                        ?>
                        <div class="complaint-item">
                            <div class="complaint-meta">
                                <span class="complaint-id"><?php echo $complains[$i]['id']; ?></span>
                                <span class="complaint-priority high"><?php echo $complains[$i]['priority']; ?></span>
                            </div>
                            <div class="complaint-title"><?php echo $complains[$i]['title']; ?></div>
                            <div class="complaint-desc">
                                <?php echo $complains[$i]['discription']; ?>
                            </div>
                            <div class="complaint-actions">
                                <button class="btn-sm primary">Review</button>
                                <form method="POST" style="display:inline-block;">
                                    <input type="hidden" name="action" value="dismiss">
                                    <input type="hidden" name="complaint_id" value="<?php echo $complains[$i]['id']; ?>">
                                    <button type="submit" class="btn-sm secondary">Dismiss</button>
                                </form>
                            </div>
                        </div>
                        <?php
                    }
                }

                if (!$hasComplaints) { ?>
                    <div
                        style="text-align: center; padding: 40px 20px; display: flex; flex-direction: column; align-items: center; justify-content: center; background: #f9fafb; border-radius: 8px; margin: 10px 0; border: 1px dashed #d1d5db;">
                        <span class="material-symbols-outlined"
                            style="font-size: 48px; color: #9ca3af; margin-bottom: 12px;">inbox</span>
                        <h4 style="margin: 0; font-size: 16px; color: #4b5563; font-weight: 600;">No Complaints Found</h4>
                        <p style="margin: 6px 0 0 0; font-size: 13px; color: #6b7280;">There are no complaints to display at
                            the moment.</p>
                    </div>
                <?php } ?>

            </div>
            <a href="complaints.php" class="see-all-link">See all
                (<?php echo $dashboardManager->getTotalCount("complain where status != 'DISMISSED'"); ?>) complaints</a>
        </div>

    </div>


    <!-- Top Performing Universities -->
    <div class="full-width-section">
        <div class="card">
            <div class="card-header">
                <h3>Top Performing Universities</h3>
                <a href="universities.php" class="btn-outline">View All Universities</a>
            </div>
            <div class="card-body" style="padding:0;">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>University Name</th>
                            <th>Students</th>
                            <th>Active Projects</th>
                            <th>Completion Rate</th>
                            <th>Status</th>
                            <!-- <th>Actions</th> -->
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $limit = (count($popularUni) < 3) ? count($popularUni) : 3;
                        for ($x = 0; $x < $limit; $x++) {
                            $acurate = ($popularUni[$x]["ACTIVE PROJECTS"] / $totalAcPro) * 100;
                            ?>
                            <tr>
                                <td>
                                    <div class="university-info">
                                        <div class="university-logo mit">
                                            <?php echo $popularUni[$x]['UNIVERSITY NAME'][0]; ?></div>
                                        <span
                                            class="university-name"><?php echo $popularUni[$x]['UNIVERSITY NAME']; ?></span>
                                    </div>
                                </td>
                                <td><?php echo $popularUni[$x]['STUDENTS']; ?></td>
                                <td><?php echo $popularUni[$x]['ACTIVE PROJECTS']; ?></td>
                                <td>
                                    <div class="progress-bar-container">
                                        <div class="progress-bar">
                                            <div class="progress-bar-fill high" style="width:<?php echo $acurate; ?>%;">
                                            </div>
                                        </div>
                                        <span class="progress-text"><?php echo $acurate; ?>%</span>
                                    </div>
                                </td>
                                <td><span class="badge-status active"><?php echo $popularUni[$x]['status']; ?></span></td>
                                <!--<td><button class="action-btn">⋮</button></td>-->
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
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