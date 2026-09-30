<?php
/**
 * SkillBridge - Notifications Backend Controller
 * Handles notification actions (mark read, mark all read, delete) and stats aggregation
 */

require_once __DIR__ . '/../../Config/db.php';
require_once __DIR__ . '/AdminBackend.php';

if (!isset($adminDB) || !($adminDB instanceof AdminDB)) {
    $adminDB = new AdminDB($conn);
}

$user = current_user();
$adminEmail = $user['Email'] ?? $user['email'] ?? '';
$flash = null;

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $notifId = intval($_POST['notification_id'] ?? 0);

    // 1. Mark Single Notification as Read
    if ($action === 'mark_read' && $notifId > 0) {
        if ($adminDB->markNotificationAsRead($notifId, $adminEmail)) {
            $flash = ['type' => 'success', 'message' => 'Notification marked as read.'];
        }
    }

    // 2. Mark All as Read
    elseif ($action === 'mark_all_read') {
        if ($adminDB->markAllNotificationsAsRead($adminEmail)) {
            $flash = ['type' => 'success', 'message' => 'All notifications marked as read.'];
        }
    }

    // 3. Delete Notification
    elseif ($action === 'delete' && $notifId > 0) {
        if ($adminDB->deleteNotification($notifId, $adminEmail)) {
            $flash = ['type' => 'success', 'message' => 'Notification removed.'];
        }
    }
}

// Notification Counts & Stats
$counts = $adminDB->getNotificationCounts($adminEmail);
$totalCount = $counts['total'];
$unreadCount = $counts['unread'];
$inquiryCount = $counts['inquiries'];

// Filter and List
$filterTab = $_GET['tab'] ?? 'all';
$notifList = $adminDB->getFilteredNotifications($adminEmail, $filterTab);
