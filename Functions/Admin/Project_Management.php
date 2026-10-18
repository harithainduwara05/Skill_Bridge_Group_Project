<?php
ob_start();
$backendPage = "Project_Management.php";
require_once __DIR__ . "/Components/Common/AdminInit.php";
?>

<!-- Project Management Custom Stylesheet -->
<link rel="stylesheet" href="../../Assets/CSS/Admin/project_management.css?v=<?php echo time(); ?>">

<?php
include "../../Includes/dash_header.php";
?>

<main class="content">
    <div class="pm-container">

        <!-- Flash Notification Component -->
        <?php include __DIR__ . "/Components/Common/FlashToast.php"; ?>

        <!--  Page Header (Title & Subtitle)-->
        <?php
            $HeaderDetails = [
                "title"       => "Project Management",
                "subtitle"    => "Monitor, review, and manage all student projects across the SkillBridge ecosystem.",
                "breadcrumb"  => "Project Management",
                //"btnText"     => "Add Project",
                //"btnId"       => "pmBtnAddProject",
                //"btnIcon"     => "add",
                //"btnClass"    => "pm-btn-primary",
                "headerClass" => "pm-header"
            ];
            include __DIR__ . "/Components/Common/PageHeader.php";
        ?>
        <!-- EndHere -->

        <!--  Project Management Cards-->
        <div class="pm-stats-grid">

            <?php
                $CardDetails = [
                    "cardIcons"  => ["folder_open", "bolt", "rate_review", "task_alt"],
                    "iconColors" => ["navy", "green", "blue", "grey"],
                    "cardLabels" => ["Total Projects", "Active Projects", "Reviewing", "Completed Projects"],
                    "cardValues" => [$totalProjects, $activeProjects, $reviewingProjects, $completedProjects]
                ];
                include __DIR__ . "/Components/Common/Card.php";
            ?>
            <!--End Here -->

        </div>

        <!-- Unified Projects Data Table Card (Toolbar + Table + Pagination) -->
        <div class="pm-table-card">

            <!-- Filter Toolbar -->
            <?php require_once __DIR__ . '/Components/Projects/ProjectFilterBar.php'; ?>
            <!--End Here -->

            <?php require_once __DIR__ . '/Components/Projects/ProjectTable.php'; ?>
            <!--End Here -->

            <!-- Reusable Pagination Component -->
            <?php 
                $totalRecords         = count($projectsList ?? []);
                $perPage              = 5;
                $entityLabel          = 'results';
                $paginationInfoId     = 'pmPaginationInfo';
                $paginationControlsId = 'pmPaginationControls';
                $alwaysShow           = true;
                include __DIR__ . '/Components/Common/Pagination.php';
            ?>
            <!--End Here -->

        </div>

    </div>
</main>

<!--  Add Project Modal (Disabled for Admin) -->
<?php // require_once __DIR__ . '/Components/Projects/AddProject.php'; ?>
<!--End Here -->

<!--  Project Details Modal-->
<?php require_once __DIR__ . '/Components/Projects/ProjectDetails.php'; ?>
<!--End Here -->

<!--  Place Project on Hold Modal -->
<?php require_once __DIR__ . '/Components/Projects/PlaceProjectOnHoldModal.php'; ?>
<!-- End Here -->

<!--  Review Hold & Resolution Modal (Multi-step: Review -> Final Reject Reason) -->
<?php require_once __DIR__ . '/Components/Projects/ReviewHoldModal.php'; ?>
<!-- End Here -->

<!--  Reject Project Reason Modal (Permanent & Irreversible) -->
<?php require_once __DIR__ . '/Components/Projects/RejectProjectReasonModal.php'; ?>
<!-- End Here -->

<!--  Toast Notification Alert -->
<div class="pm-toast" id="pmToast">
    <span class="material-symbols-outlined">check_circle</span>
    <span id="pmToastMessage">Operation successful!</span>
</div>

<!-- Project Management Dedicated Script -->
<script src="../../Assets/JS/Admin/project_management.js?v=<?php echo time(); ?>"></script>

<?php include "../../Includes/dash_footer.php"; ?>