<!--Initialize Admin Environment: Auth Guard, 
Backend Controller & Sidebar-->
<?php
$backendPage = "User_Management.php";
require_once __DIR__ . "/Components/AdminInit.php";
?>
<!--End Here -->

<link rel="stylesheet" href="../../Assets/CSS/Admin/usermanagement.css">
<?php
include "../../Includes/dash_header.php";
?>

<main class="content">

    <!-- Flash Notification Component -->
    <?php include __DIR__ . "/Components/FlashToast.php"; ?>
    <!-- EndHere -->

    <!-- Page Header -->
    <?php require_once __DIR__.'/Components/UserHeder.php'?>
    <!-- EndHere -->

    <!-- UserManagement  Cards -->
    <div class="user-stats-grid">
        <?php
            $CardDetails = [
                "cardIcons"  => ["group", "how_to_reg", "school", "domain"],
                "iconColors" => ["navy", "green", "blue", "purple"],
                "cardLabels" => ["Total Users", "Active Users", "Students", "Companies & Orgs"],
                "cardValues" => [
                    number_format($stats['total']),
                    number_format($stats['active']),
                    number_format($stats['students']),
                    number_format($stats['companies'] + $stats['organizations'])
                ]
            ];
            include __DIR__ . "/Components/Card.php";
        ?>
    </div>
    <!-- EndHere -->

    <!-- Main Content Card -->
    <div class="full-width-section" style="padding: 0 28px 40px;">
        <div class="card" style="border: 1px solid #e5e7eb; border-radius: 14px; background: #fff; overflow: hidden;">
            
            <!-- Filters Toolbar -->
            <?php require_once __DIR__.'/Components/UserFilterBar.php'?>
            <!-- EndHere -->

            <!-- Users Table -->
            <?php require_once "Components/UserTable.php";?>
            <!-- EndHere -->

            <!-- Reusable Pagination Component -->
            <?php 
                $entityLabel = 'users';
                include __DIR__ . "/Components/Pagination.php";
            ?>
            <!-- EndHere -->

        </div>
    </div>

</main>

<!-- MODAL: ADD NEW USER -->
<?php include_once 'Components/UserModels.php'?>
<!-- EndHere -->

<script>
    window.dbUniversities = <?= json_encode($dbUniversities, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    window.activeInstitutions = <?= json_encode($activeInstitutions, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
</script>
<script src="../../Assets/JS/Admin/usermanagement.js"></script>

<footer class="footer">
    <div>&copy; 2026 SkillBridge. All rights reserved.</div>
    <div class="footer-links">
        <a href="/Skill_Bridge_Group_Project/help_center.php">Help Center</a>
        <a href="#">Privacy Policy</a>
        <a href="#">Terms of Service</a>
    </div>
</footer>
<?php include "../../Includes/dash_footer.php"; ?>
