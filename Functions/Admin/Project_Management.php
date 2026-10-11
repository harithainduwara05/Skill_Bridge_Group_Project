<!--Initialize Admin Environment: Auth Guard, 
Backend Controller & Sidebar-->
<?php
$backendPage = "Project_Management.php";
require_once __DIR__ . "/Components/AdminInit.php";
?>
<!--End Here -->

<!-- Project Management Custom Stylesheet -->
<link rel="stylesheet" href="../../Assets/CSS/Admin/project_management.css?v=<?php echo time(); ?>">

<?php
include "../../Includes/dash_header.php";
?>

<main class="content">
    <div class="pm-container">

        <!--  Page Header (Title & Subtitle)-->
        <div class="pm-header">
            <h1>Project Management</h1>
            <p>Monitor, review, and manage all student projects across the SkillBridge ecosystem.</p>
        </div>

        <!--  Project Management Cards-->
        <div class="pm-stats-grid">

            <?php
                $CardDetails = [
                    "cardIcons"  => ["folder_open", "bolt", "rate_review", "task_alt"],
                    "iconColors" => ["navy", "green", "blue", "grey"],
                    "cardLabels" => ["Total Projects", "Active Projects", "Reviewing", "Completed Projects"],
                    "cardValues" => [$totalProjects, $activeProjects, $reviewingProjects, $completedProjects]
                ];
                include __DIR__ . "/Components/Card.php";
            ?>
            <!--End Here -->

        </div>

        <!-- Unified Projects Data Table Card (Toolbar + Table + Pagination) -->
        <div class="pm-table-card">

            <!-- Filter Toolbar -->
            <?php require_once __DIR__ . '/Components/ProjectFilterBar.php'; ?>
            <!--End Here -->

            <?php require_once __DIR__ . '/Components/ProjectTable.php'; ?>
            <!--End Here -->

            <!-- Reusable Pagination Component -->
            <?php 
                $totalRecords         = count($projectsList ?? []);
                $perPage              = 5;
                $entityLabel          = 'results';
                $paginationInfoId     = 'pmPaginationInfo';
                $paginationControlsId = 'pmPaginationControls';
                $alwaysShow           = true;
                include __DIR__ . '/Components/Pagination.php';
            ?>
            <!--End Here -->

        </div>

    </div>
</main>

<!--  Add Project Modal-->
<?php require_once __DIR__ . '/Components/AddProject.php'; ?>
<!--End Here -->

<!--  Project Details Modal-->
<?php require_once __DIR__ . '/Components/ProjectDetails.php'; ?>
<!--End Here -->

<!--  Place Project on Hold Modal -->
<?php require_once __DIR__ . '/Components/PlaceProjectOnHoldModal.php'; ?>
<!-- End Here -->

<!--  Review Hold & Resolution Modal (Multi-step: Review -> Final Reject Reason) -->
<?php require_once __DIR__ . '/Components/ReviewHoldModal.php'; ?>
<!-- End Here -->

<!--  Reject Project Reason Modal (Permanent & Irreversible) -->
<?php require_once __DIR__ . '/Components/RejectProjectReasonModal.php'; ?>
<!-- End Here -->

<!--  Toast Notification Alert -->
<div class="pm-toast" id="pmToast">
    <span class="material-symbols-outlined">check_circle</span>
    <span id="pmToastMessage">Operation successful!</span>
</div>

<!-- Page Footer -->
<footer class="footer">
    <div>&copy; 2026 SkillBridge. All rights reserved.</div>
    <div class="footer-links">
        <a href="/Skill_Bridge_Group_Project/help_center.php">Help Center</a>
        <a href="#">Privacy Policy</a>
        <a href="#">Terms of Service</a>
    </div>
</footer>

<!-- Project Management Dedicated Script -->
<script src="../../Assets/JS/Admin/project_management.js?v=<?php echo time(); ?>"></script>

<?php include "../../Includes/dash_footer.php"; ?>