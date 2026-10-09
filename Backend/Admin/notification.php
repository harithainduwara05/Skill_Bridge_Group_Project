<?php

require_once __DIR__ . '/loader.php';

$notifDB = new NotificationManagement($conn);
$adminDB = $notifDB;

$user = current_user();
$adminEmail = $user['Email'] ?? $user['email'] ?? '';
$flash = null;

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $notifId = intval($_POST['notification_id'] ?? 0);

    // 1. Mark Single Notification as Read
    if ($action === 'mark_read' && $notifId > 0) {
        if ($notifDB->markNotificationAsRead($notifId, $adminEmail)) {
            $flash = ['type' => 'success', 'message' => 'Notification marked as read.'];
        }
    }

    // 2. Mark All as Read
    elseif ($action === 'mark_all_read') {
        if ($notifDB->markAllNotificationsAsRead($adminEmail)) {
            $flash = ['type' => 'success', 'message' => 'All notifications marked as read.'];
        }
    }

    // 3. Delete Notification
    elseif ($action === 'delete' && $notifId > 0) {
        if ($notifDB->deleteNotification($notifId, $adminEmail)) {
            $flash = ['type' => 'success', 'message' => 'Notification removed.'];
        }
    }
}

// Notification Counts & Stats
$counts = $notifDB->getNotificationCounts($adminEmail);
$totalCount = $counts['total'];
$unreadCount = $counts['unread'];
$inquiryCount = $counts['inquiries'];

// Filter and List
$filterTab = $_GET['tab'] ?? 'all';
$notifList = $notifDB->getFilteredNotifications($adminEmail, $filterTab);
?>
