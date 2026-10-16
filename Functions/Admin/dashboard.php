<!--Initialize Admin Environment: Auth Guard, 
Backend Controller & Sidebar-->
<?php
$backendPage = "dashboard.php";
require_once __DIR__ . "/Components/Common/AdminInit.php";
?>
<!--End Here -->

<!-- Admin Dashboard Stylesheet -->
<link rel="stylesheet" href="../../Assets/CSS/Admin/dashboard.css?v=<?php echo time(); ?>">

<?php
include "../../Includes/dash_header.php";
?>

<main class="content">

    <!-- Dashboard Header -->
    <?php require_once __DIR__ . '/Components/Dashboard/DashBordHeder.php'; ?>
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
            include __DIR__ . "/Components/Common/Card.php";
        ?>
    </div>
    <!--End Here-->

    <!-- Recent Registrations + Urgent Complaints -->
    <div class="two-col-grid">
        <!-- Recent Registrations -->
        <?php require_once __DIR__ . '/Components/Dashboard/DRecentRegistration.php'; ?>
        <!--End Here-->

        <!-- Urgent Complaints -->
        <?php require_once __DIR__ . '/Components/Dashboard/DUrgentComplaints.php'; ?>
        <!--End Here-->
    </div>

    <!-- Top Performing Universities -->
    <?php require_once __DIR__ . '/Components/Dashboard/DTopPerfomanceUniversity.php'; ?>
    <!--End Here-->

</main>

<?php include "../../Includes/dash_footer.php"; ?>