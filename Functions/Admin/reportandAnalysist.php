<!--Initialize Admin Environment: Auth Guard, 
Backend Controller & Sidebar-->
<?php
$backendPage = "reportandAnalysist.php";
require_once __DIR__ . "/Components/Common/AdminInit.php";
?>
<!--End Here -->

<!-- Custom Reports & Analytics Stylesheet -->
<link rel="stylesheet" href="../../Assets/CSS/Admin/reports_analytics.css?v=<?php echo time(); ?>">

<?php
include "../../Includes/dash_header.php";
?>

<main class="content">
    <div class="ra-container">

        <!--  Page Header (Title, Subtitle, Period Pills & Export Actions)-->
        <?php require_once __DIR__ . '/Components/Reports/ReportsHeader.php'; ?>
        <!-- EndHere -->

        <!--  Executive KPI Summary Cards (4 Cards Grid)-->
        <div class="ra-kpi-grid">
            <?php
                $CardDetails = [
                    "cardIcons"    => ["school", "apartment", "task_alt", "verified_user"],
                    "iconColors"   => ["blue", "purple", "green", "amber"],
                    "cardLabels"   => ["Placed Students", "Active Industry Partners", "Industry Projects", "Grievance Resolution"],
                    "cardValues"   => [
                        number_format($placedStudentsCount ?? 0), 
                        number_format($activeCompaniesCount ?? 0) . " Companies", 
                        number_format($totalProjectsCount ?? 0) . " Projects", 
                        ($resolutionRate ?? 100) . "% Resolved"
                    ],
                    "valueIds"     => ["raKpiPlaced", "raKpiCompanies", "raKpiProjects", "raKpiResolution"],
                    "subtexts"     => [
                        ($placementRate ?? 0) . "% Placement Rate", 
                        number_format($activeDrivesCount ?? 0) . " Active Drives", 
                        number_format($reviewProjectsCount ?? 0) . " In Supervisor Review", 
                        number_format($openComplaintsCount ?? 0) . " Open in Queue"
                    ],
                    "subtextIcons" => ["verified", "work", "pending_actions", "report_problem"],
                    "subtextIds"   => ["raKpiPlacedSub", "raKpiCompanySub", "raKpiProjectSub", "raKpiResolutionSub"]
                ];
                include __DIR__ . "/Components/Common/Card.php";
            ?>
        </div>
        <!-- EndHere -->

        <!--  Domain Filter Navigation Tabs-->
        <?php require_once __DIR__ . '/Components/Reports/ReportsTabsBar.php'; ?>
        <!-- EndHere -->

        <!--  Visual Analytics Charts Grid - Row 1 (Hiring Velocity & University Donut)-->
        <?php require_once __DIR__ . '/Components/Reports/ReportsMainCharts.php'; ?>
        <!-- EndHere -->

        <!--  Visual Analytics Charts Grid - Row 2 (Skills Demand & Conversion Funnel)-->
        <?php require_once __DIR__ . '/Components/Reports/ReportsSkillsFunnel.php'; ?>
        <!-- EndHere -->

        <!--  Deep-Dive Table 1: Top Performing Partner Universities-->
        <?php require_once __DIR__ . '/Components/Reports/ReportsUniversityBenchmarkTable.php'; ?>
        <!-- EndHere -->

        <!--  Deep-Dive Table 2: Top Recruiting Industry Partners-->
        <?php require_once __DIR__ . '/Components/Reports/ReportsCompanyRecruiterTable.php'; ?>
        <!-- EndHere -->

    </div>
</main>

<!--  Export Report Custom Modal-->
<?php require_once __DIR__ . '/Components/Reports/ReportsExportModal.php'; ?>
<!-- EndHere -->

<!--  Toast Notification Alert-->
<div class="ra-toast" id="raToast">
    <span class="material-symbols-outlined">check_circle</span>
    <span id="raToastMessage">Report generated successfully!</span>
</div>

<!-- Pure Native Vanilla JS Reports & Analytics Script (Zero External Libraries) -->
<script src="../../Assets/JS/Admin/reports_analytics.js?v=<?php echo time(); ?>"></script>

<?php include "../../Includes/dash_footer.php"; ?>