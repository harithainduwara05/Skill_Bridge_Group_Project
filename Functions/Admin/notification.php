<!--Initialize Admin Environment: Auth Guard, 
Backend Controller & Sidebar-->
<?php
$backendPage = "notification.php";
require_once __DIR__ . "/Components/AdminInit.php";
?>
<!--End Here -->

<link rel="stylesheet" href="../../Assets/CSS/Admin/notification.css?v=<?php echo time(); ?>">
<link rel="stylesheet" href="../../Assets/CSS/flash-toast.css">
<?php
include "../../Includes/dash_header.php";
?>

<main class="content">

    <!-- Flash Notification Component -->
    <?php include __DIR__ . "/Components/FlashToast.php"; ?>
    <!--End Here-->

    <div class="notif-container">

        <!-- Breadcrumb -->
        <div class="notif-breadcrumb">
            <a href="dashboard.php">Admin</a>
            <span class="material-symbols-outlined" style="font-size:14px;">chevron_right</span>
            <span>Notifications</span>
        </div>

        <!-- Header Row -->
        <?php require_once __DIR__.'/Components/NotificationHeder.php';?>
        <!--End Here-->

        <!-- Notification  Cards -->
        <div class="notif-stats-grid">
            <?php
                $CardDetails = [
                    "cardIcons"  => ["notifications", "mark_email_unread", "contact_support"],
                    "iconColors" => ["blue", "amber", "purple"],
                    "cardLabels" => ["Total Notifications", "Unread Notifications", "Contact Inquiries"],
                    "cardValues" => [number_format($totalCount), number_format($unreadCount), number_format($inquiryCount)]
                ];
                include __DIR__ . "/Components/Card.php";
            ?>
        </div>
        <!--End Here-->

        <!-- Filter Tabs -->
        <?php require_once __DIR__.'/Components/NotificationFilter.php'?>
        <!--End Here-->

        <!-- Notifications List -->
        <?php require_once __DIR__.'/Components/NotificationList.php'?>
        <!--End Here-->
    </div>

</main>

<!-- MODAL: DELETE CONFIRMATION POPUP -->
<?php require_once __DIR__.'/Components/NotificationDeleteComPopUp.php'?>
<!--End Here-->

<!-- Notification Dedicated Script -->
<script src="../../Assets/JS/Admin/notification.js?v=<?php echo time(); ?>"></script>

<footer class="footer">
    <div>&copy; 2026 SkillBridge. All rights reserved.</div>
    <div class="footer-links">
        <a href="/Skill_Bridge_Group_Project/help_center.php">Help Center</a>
        <a href="#">Privacy Policy</a>
        <a href="#">Terms of Service</a>
    </div>
</footer>

<?php include "../../Includes/dash_footer.php"; ?>