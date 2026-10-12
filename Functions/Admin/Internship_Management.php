<!--Initialize Admin Environment: Auth Guard, 
Backend Controller & Sidebar-->
<?php
$backendPage = "Internship_Management.php";
require_once __DIR__ . "/Components/Common/AdminInit.php";
?>
<!--End Here -->
<!-- Internship Management Custom Stylesheet -->
<link rel="stylesheet" href="../../Assets/CSS/Admin/internship_management.css?v=<?php echo time(); ?>">

<?php
include "../../Includes/dash_header.php";
?>

<main class="content">
    <div class="im-container">

        <!--  Page Header (Title & Subtitle)-->
        <?php
            $HeaderDetails = [
                "title"      => "Internship Management",
                "subtitle"   => "Monitor, review, and manage internship opportunities provided by companies across all sectors.",
                "breadcrumb" => "Internship Management"
            ];
            include __DIR__ . "/Components/Common/PageHeader.php";
        ?>
        <!-- EndHere -->

        <!--  InternshipManagement cars-->
        <div class="im-stats-grid">

            <?php
                $CardDetails = [
                    "cardIcons"  => ["work", "check_circle", "pause_circle", "group"],
                    "iconColors" => ["navy", "green", "orange", "blue"],
                    "cardLabels" => ["Total Internships", "Active Internships", "Suspended Posts", "Total Applications"],
                    "cardValues" => ["432", "286", "14", "2,850"],
                    "valueIds"   => ["", "", "kpiSuspendedCount", "kpiTotalApps"]
                ];
                include __DIR__ . "/Components/Common/Card.php";
            ?>

        </div>

        <!-- Unified Internships Data Table Card (Toolbar + Table + Pagination) -->
        <div class="im-table-card">
            
            <!-- Filter & Search Toolbar -->
            <?php require_once __DIR__ . '/Components/Internships/InternshipFilterBar.php'; ?>
            <!--End Here-->

            <!-- Internships Data Table -->
            <?php require_once __DIR__ . '/Components/Internships/InternshipTable.php'; ?>
            <!-- Reusable Pagination Component -->
            <?php 
                $totalRecords   = 432;
                $perPage        = 10;
                $currentPageNum = (int)($_GET['page'] ?? 1);
                $entityLabel    = 'entries';
                include __DIR__ . '/Components/Common/Pagination.php';
            ?>
            <!--End Here-->

        </div>

    </div>
</main>

<!--  Add Internship Modal -->
<?php require_once __DIR__ . '/Components/Internships/AddInternshpModal.php'; ?>
<!--End Here-->

<!--  Internship Details Modal -->
<?php require_once __DIR__ . '/Components/Internships/InternshipDetailsModel.php'; ?>
<!--End Here-->

<!--  Suspend Internship & Notify Company Modal -->
<?php require_once __DIR__ . '/Components/Internships/SuspendInternshipResolutionModel.php'; ?>
<!--End Here-->

<!--  Review Suspended Internship & Resolution Modal -->
<?php require_once __DIR__ . '/Components/Internships/ReviewSuspendAndResolutionInternship.php'; ?>
<!--End Here-->

<!--  Toast Notification Alert -->
<div class="im-toast" id="imToast">
    <span class="material-symbols-outlined">check_circle</span>
    <span id="imToastMessage">Operation successful!</span>
</div>

<!-- Internship Management Dedicated Script -->
<script src="../../Assets/JS/Admin/internship_management.js?v=<?php echo time(); ?>"></script>

<?php include "../../Includes/dash_footer.php"; ?>