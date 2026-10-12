<!--Initialize Admin Environment: Auth Guard, 
Backend Controller & Sidebar-->
<?php
$backendPage = "dashboard.php";
require_once __DIR__ . "/Components/AdminInit.php";
?>
<!--End Here -->

<!-- Admin Dashboard Stylesheet -->
<link rel="stylesheet" href="../../Assets/CSS/Admin/dashboard.css?v=<?php echo time(); ?>">

<?php
include "../../Includes/dash_header.php";
?>

<main class="content">

    <!-- Dashboard Header -->
    <?php require_once __DIR__.'/Components/DashBordHeder.php'?>
    <!--End Here-->


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
        <!--End Here-->

    </div>
    <!-- Recent Registrations + Urgent Complaints -->
    <div class="two-col-grid">
        <!-- Recent Registrations -->
        <?php require_once __DIR__.'/Components/DRecentRegistration.php'?>
        <!--End Here-->

        <!-- Urgent Complaints -->
        <?php require_once __DIR__.'/Components/DUrgentComplaints.php'?>
        <!--End Here-->
    </div>

    <!-- Top Performing Universities -->
    <?php require_once __DIR__.'/Components/DTopPerfomanceUniversity.php'?>
    <!--End Here-->

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