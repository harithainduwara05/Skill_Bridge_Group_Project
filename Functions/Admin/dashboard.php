<?php
include "../../Config/db.php";
include "../../Session/session.php";

require_login();
require_role('admin');
$user = current_user();

require_once __DIR__ . "/../../Backend/Admin/dashboard.php";

include "../../Includes/admin_sidebar.php";
?>

<!-- Admin Dashboard Stylesheet -->
<link rel="stylesheet" href="../../Assets/CSS/Admin/dashboard.css?v=<?php echo time(); ?>">

<?php
include "../../Includes/dash_header.php";
?>

<main class="content">

    <!-- Dashboard Header -->
    <div class="dashboard-header">
        <div>
            <h1 class="welcome-heading">
                Welcome back, <?php echo htmlspecialchars($adminName); ?>! 👋
            </h1>
            <h2 class="overview-title">Dashboard Overview</h2>
            <p>
                Manage users, track platform growth, and oversee ongoing collaborations.
            </p>
        </div>
        <!--<button class="btn-new-report">
                <span class="material-symbols-outlined" style="font-size:18px;">add</span>
                New Report
            </button>-->
    </div>


    <!-- DashBord Stats Cards -->
    <div class="stats-grid">

        <?php
            $CardDetails = [
                "cardIcons"  => ["group", "school", "cases", "description"],
                "iconColors" => ["blue", "orange", "slate", "navy"],
                "cardLabels" => ["Total Users", "Active Universities", "Ongoing Projects", "Total Internships"],
                "cardValues" => [$totalUsers, $totUni, $tot_Ongoin_projects, $tot_internship]
            ];
            include __DIR__ . "/Components/Card.php";
        ?>

    </div>
    <!-- Recent Registrations + Urgent Complaints -->
    <div class="two-col-grid">

        <!-- Recent Registrations -->
        <div class="card">
            <div class="card-header">
                <h3>Recent Registrations</h3>
                <a href="User_Management.php" class="btn-outline">View All Users</a>
            </div>
            <div class="card-body" style="padding:0;">
                <table class="data-table">
                    <?php 
                        $TableColumns = ["User", "Role", "Organization", "Status"];
                        include __DIR__ . "/Components/TableHeder.php";
                    ?>
                    <tbody>
                        <?php
                        $limit = (count($allusers) < 5) ? count($allusers) : 5;
                        for ($x = 0; $x < $limit; $x++) {
                            $userRole = strtolower($allusers[$x]['role'] ?? 'student');
                            $userStatus = $allusers[$x]['status'] ?? 'Active';
                            $statusClass = strtolower($userStatus);
                            if ($statusClass === 'de-active' || $statusClass === 'deactive') {
                                $statusClass = 'deactive';
                            } elseif ($statusClass === 'pending') {
                                $statusClass = 'pending';
                            } else {
                                $statusClass = 'active';
                            }
                            ?>
                            <tr>
                                <td>
                                    <div class="user-info">
                                        <div class="user-details">
                                            <div class="user-name">
                                                <?php echo htmlspecialchars($allusers[$x]['user_name']); ?></div>
                                            <div class="user-email"><?php echo htmlspecialchars($allusers[$x]['email']); ?>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td><span
                                        class="badge-role <?= $userRole ?>"><?php echo htmlspecialchars(ucfirst($allusers[$x]['role'])); ?></span>
                                </td>
                                <td><?php echo htmlspecialchars($allusers[$x]['organization_name'] ?: 'N/A'); ?></td>
                                <td><span
                                        class="badge-status-pill <?= $statusClass ?>"><?php echo htmlspecialchars(ucfirst($userStatus)); ?></span>
                                </td>
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
                $limit = min(3, count($complains));
                for ($i = 0; $i < $limit; $i++) {
                    if (($complains[$i]['status'] != "DISMISSED")) {
                        $hasComplaints = true;
                        $priorityClass = strtolower($complains[$i]['priority']);
                        ?>
                        <div class="complaint-item">
                            <div class="complaint-meta">
                                <span class="complaint-id">#<?php echo $complains[$i]['id']; ?></span>
                                <span
                                    class="complaint-priority <?php echo $priorityClass === 'urgent' || $priorityClass === 'high' ? 'high' : ($priorityClass === 'medium' ? 'medium' : 'low'); ?>">
                                    <?php echo htmlspecialchars($complains[$i]['priority']); ?>
                                </span>
                            </div>
                            <div class="complaint-title"><?php echo htmlspecialchars($complains[$i]['title']); ?></div>
                            <div class="complaint-desc">
                                <?php echo htmlspecialchars($complains[$i]['discription']); ?>
                            </div>
                            <div class="complaint-actions">
                                <button class="btn-sm primary" type="button">Review</button>
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
                        <h4 style="margin: 0; font-size: 16px; color: #4b5563; font-weight: 600;">No Urgent Complaints</h4>
                        <p style="margin: 6px 0 0 0; font-size: 13px; color: #6b7280;">There are no high risk complaints to
                            display at
                            the moment.</p>
                    </div>
                <?php } ?>

            </div>
            <a href="complain.php" class="see-all-link">See all
                (<?php echo $complaintDB->getUndismissedComplaintCount(); ?>) complaints</a>
        </div>

    </div>


    <!-- Top Performing Universities -->
    <div class="full-width-section">
        <div class="card">
            <div class="card-header">
                <h3>Top Performing Universities</h3>
                <a href="university.php" class="btn-outline">View All Universities</a>
            </div>
            <div class="card-body" style="padding:0;">
                <table class="data-table">
                    <?php 
                        $TableColumns = ["University Name", "Students", "Active Projects", "Completion Rate", "Status"];
                        include __DIR__ . "/Components/TableHeder.php";
                    ?>
                    <tbody>
                        <?php
                        $limit = (count($popularUni) < 3) ? count($popularUni) : 3;
                        for ($x = 0; $x < $limit; $x++) {
                            $acurate = ($totalAcPro > 0) ? ($popularUni[$x]["ACTIVE PROJECTS"] / $totalAcPro) * 100 : 0;
                            ?>
                            <tr>
                                <td>
                                    <div class="university-info">
                                        <div class="university-logo mit">
                                            <?php echo $popularUni[$x]['UNIVERSITY NAME'][0]; ?>
                                        </div>
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
        <a href="/Skill_Bridge_Group_Project/help_center.php">Help Center</a>
        <a href="#">Privacy Policy</a>
        <a href="#">Terms of Service</a>
    </div>
</footer>
<?php include "../../Includes/dash_footer.php"; ?>