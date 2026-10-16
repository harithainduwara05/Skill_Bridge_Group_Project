<?php
/**
 * SkillBridge - Notification Management Service
 * Handles admin notification dispatch, status tracking, and inquiry filtering.
 */

require_once __DIR__ . '/AdminDB.php';

class NotificationManagement extends AdminDB
{
    public function markNotificationAsRead($notifId, $adminEmail)
    {
        return $this->runAction("UPDATE notifications SET status = 'Read' WHERE notification_id = ? AND Email = ?", "is", $notifId, $adminEmail);
    }

    public function markAllNotificationsAsRead($adminEmail)
    {
        return $this->runAction("UPDATE notifications SET status = 'Read' WHERE Email = ? AND status = 'Unread'", "s", $adminEmail);
    }

    public function deleteNotification($notifId, $adminEmail)
    {
        return $this->runAction("DELETE FROM notifications WHERE notification_id = ? AND Email = ?", "is", $notifId, $adminEmail);
    }

    public function getNotificationCounts($adminEmail)
    {
        $sql = "
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'Unread' THEN 1 ELSE 0 END) as unread,
                SUM(CASE WHEN type = 'contact_inquiry' THEN 1 ELSE 0 END) as inquiries
            FROM notifications 
            WHERE Email = ?
        ";
        $res = $this->runQuery($sql, "s", $adminEmail);
        if ($res && $row = $res->fetch_assoc()) {
            return [
                'total' => intval($row['total'] ?? 0),
                'unread' => intval($row['unread'] ?? 0),
                'inquiries' => intval($row['inquiries'] ?? 0)
            ];
        }
        return ['total' => 0, 'unread' => 0, 'inquiries' => 0];
    }

    public function getFilteredNotifications($adminEmail, $filterTab = 'all')
    {
        $query = "SELECT * FROM notifications WHERE Email = ?";
        $params = [$adminEmail];
        $types = "s";

        if ($filterTab === 'unread') {
            $query .= " AND status = 'Unread'";
        } elseif ($filterTab === 'inquiries') {
            $query .= " AND type = 'contact_inquiry'";
        }

        $query .= " ORDER BY notification_id DESC";
        $res = $this->runQuery($query, $types, ...$params);
        return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    }
}