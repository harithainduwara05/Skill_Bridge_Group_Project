<!--Initialize Admin Environment: Auth Guard, 
Backend Controller & Sidebar-->
<?php
$backendPage = "Internship_Management.php";
require_once __DIR__ . "/Components/AdminInit.php";
?>
<!--End Here -->
<!-- Internship Management Custom Stylesheet -->
<link rel="stylesheet" href="../../Assets/CSS/Admin/internship_management.css?v=<?php echo time(); ?>">

<?php
include "../../Includes/dash_header.php";
?>

<main class="content">
    <div class="im-container">

        <!--  Page Header (Title, Subtitle & Add Action)-->
        <div class="im-header-row">
            <div class="im-header-info">
                <h1>Internship Management</h1>
                <p>Monitor, review, and manage internship opportunities provided by companies across all sectors.</p>
            </div>
        </div>

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
                include __DIR__ . "/Components/Card.php";
            ?>

        </div>

        <!-- Unified Internships Data Table Card (Toolbar + Table + Pagination) -->
        <div class="im-table-card">
            
            <!-- Filter & Search Toolbar -->
            <?php require_once __DIR__.'/Components/InternshipFilterBar.php'?>
            <!--End Here-->

            <!-- Internships Data Table -->
            <?php require_once 'Components/InternshipTable.php'?>
            <!-- Reusable Pagination Component -->
            <?php 
                $totalRecords   = 432;
                $perPage        = 10;
                $currentPageNum = (int)($_GET['page'] ?? 1);
                $entityLabel    = 'entries';
                include __DIR__ . '/Components/Pagination.php';
            ?>
            <!--End Here-->

        </div>

    </div>
</main>

<!--  Add Internship Modal -->
<?php require_once __DIR__ . '/Components/AddInternshpModal.php'; ?>
<!--End Here-->

<!--  Internship Details Modal -->
<?php require_once __DIR__.'/Components/InternshipDetailsModel.php'?>
<!--End Here-->

<!--  Suspend Internship & Notify Company Modal -->
<?php require_once __DIR__.'/Components/SuspendInternshipResolutionModel.php'?>
<!--End Here-->

<!--  Review Suspended Internship & Resolution Modal -->
<?php require_once __DIR__.'/Components/ReviewSuspendAndResolutionInternship.php'?>
<!--End Here-->

<!--  Toast Notification Alert -->
<div class="im-toast" id="imToast">
    <span class="material-symbols-outlined">check_circle</span>
    <span id="imToastMessage">Operation successful!</span>
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

<!-- Internship Management Dedicated Script -->
<script src="../../Assets/JS/Admin/internship_management.js?v=<?php echo time(); ?>"></script>

<?php include "../../Includes/dash_footer.php"; ?>