<!--Initialize Admin Environment: Auth Guard, 
Backend Controller & Sidebar-->
<?php
$backendPage = "notification.php";
require_once __DIR__ . "/Components/Common/AdminInit.php";
?>
<!--End Here -->

<link rel="stylesheet" href="../../Assets/CSS/Admin/notification.css?v=<?php echo time(); ?>">
<link rel="stylesheet" href="../../Assets/CSS/flash-toast.css">
<?php
include "../../Includes/dash_header.php";
?>

<main class="content">

    <!-- Flash Notification Component -->
    <?php include __DIR__ . "/Components/Common/FlashToast.php"; ?>
    <!--End Here-->

    <div class="notif-container">

        <!-- Page Header -->
        <?php
            $markAllBtn = '';
            if ($unreadCount > 0) {
                $markAllBtn = '<form method="POST" action="" style="margin:0;"><input type="hidden" name="action" value="mark_all_read"><button type="submit" class="btn-mark-all"><span class="material-symbols-outlined" style="font-size:18px;">done_all</span>Mark All as Read</button></form>';
            }
            $HeaderDetails = [
                "title"        => "Admin Notifications",
                "subtitle"     => "Manage and monitor inquiries, system alerts, and platform messages.",
                "breadcrumb"   => "Notifications",
                "extraActions" => $markAllBtn
            ];
            include __DIR__ . "/Components/Common/PageHeader.php";
        ?>
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
                include __DIR__ . "/Components/Common/Card.php";
            ?>
        </div>
        <!--End Here-->

        <!-- Filter Tabs -->
        <?php require_once __DIR__ . '/Components/Notifications/NotificationFilter.php'; ?>
        <!--End Here-->

        <!-- Notifications List -->
        <?php require_once __DIR__ . '/Components/Notifications/NotificationList.php'; ?>
        <!--End Here-->
    </div>

</main>

<!-- MODAL: DELETE CONFIRMATION POPUP -->
<?php require_once __DIR__ . '/Components/Notifications/NotificationDeleteComPopUp.php'; ?>
<!--End Here-->

<!-- Notification Dedicated Script -->
<script src="../../Assets/JS/Admin/notification.js?v=<?php echo time(); ?>"></script>

<?php include "../../Includes/dash_footer.php"; ?>