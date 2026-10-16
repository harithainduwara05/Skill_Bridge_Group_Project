<!--Initialize Admin Environment: Auth Guard, 
Backend Controller & Sidebar-->
<?php
$backendPage = "complain.php";
require_once __DIR__ . "/Components/Common/AdminInit.php";
?>
<!--End Here -->

<!-- Complaint Management Stylesheet -->
<link rel="stylesheet" href="../../Assets/CSS/Admin/complain.css?v=<?php echo time(); ?>">

<?php
include "../../Includes/dash_header.php";
?>

<main class="content">
    <div class="cm-container">

        <!--  Page Header (Title & Subtitle)-->
        <?php
            $HeaderDetails = [
                "title"      => "Complaint Management",
                "subtitle"   => "Review and resolve reported issues from the SkillBridge community.",
                "breadcrumb" => "Complaint Management"
            ];
            include __DIR__ . "/Components/Common/PageHeader.php";
        ?>
        <!-- EndHere -->

        <!--  Complain Cards-->
        <div class="cm-stats-grid">
            
            <?php
                $CardDetails = [
                    "cardIcons"  => ["analytics", "pending_actions", "timer"],
                    "iconColors" => ["grey", "amber", "blue"],
                    "cardLabels" => ["Total Complaints", "Pending Resolution", "Average Response Time"],
                    "cardValues" => [number_format($stats['total']), number_format($stats['pending']), $stats['avg_response_time']],
                    "valueIds"   => ["cmKpiTotal", "cmKpiPending", "cmKpiAvgTime"]
                ];
                include __DIR__ . "/Components/Common/Card.php";
            ?>

        </div>
        <!-- EndHere -->

        <!-- Unified Complaints Data Table Card (Toolbar + Table + Pagination) -->
        <div class="cm-table-card">

            <!-- Filter & Search Toolbar -->
            <?php require_once __DIR__ . '/Components/Complaints/ComplaintFilterBar.php'; ?>
            <!-- EndHere -->

            <!--Complain Table-->
            <?php require_once __DIR__ . '/Components/Complaints/complainTable.php'; ?>
            <!-- End Here -->

            <!-- Reusable Pagination Component -->
            <?php 
                $totalRecords         = $totalComplaintsCount;
                $perPage              = $pageSize;
                $entityLabel          = 'results';
                $paginationInfoId     = 'cmPaginationInfo';
                $paginationControlsId = 'cmPaginationControls';
                $alwaysShow           = true;
                include __DIR__ . '/Components/Common/Pagination.php';
            ?>

        </div>

    </div>
</main>

<!-- Complaint Detail & Resolution Modal-->
<?php require_once __DIR__ . '/Components/Complaints/ComplaintDetailsResolutionModal.php'; ?>
<!--End Here-->

<!-- Toast Notification Alert (Custom Feedback, ZERO alerts  -->
<div class="cm-toast" id="cmToast">
    <span class="material-symbols-outlined">check_circle</span>
    <span id="cmToastMessage">Operation successful!</span>
</div>

<!-- Complaint Management Script -->
<script src="../../Assets/JS/Admin/complain.js?v=<?php echo time(); ?>"></script>

<?php include "../../Includes/dash_footer.php"; ?>
