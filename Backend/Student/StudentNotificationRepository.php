<?php
declare(strict_types=1);

/** Student-scoped notification persistence using the existing notifications table. */
final class StudentNotificationRepository
{
    public function __construct(private mysqli $db)
    {
    }

    public function counts(string $email): array
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) AS total,
                    COALESCE(SUM(status = 'Unread'), 0) AS unread,
                    COALESCE(SUM(status = 'Archived'), 0) AS archived
             FROM notifications WHERE Email = ?"
        );
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $counts = $stmt->get_result()->fetch_assoc() ?: [];
        $stmt->close();
        return [
            'all' => (int) ($counts['total'] ?? 0) - (int) ($counts['archived'] ?? 0),
            'unread' => (int) ($counts['unread'] ?? 0),
            'archived' => (int) ($counts['archived'] ?? 0)
        ];
    }

    public function items(string $email, string $filter, int $page, int $perPage): array
    {
        $perPage = max(1, min(50, $perPage));
        $offset = (max(1, $page) - 1) * $perPage;
        $statusClause = match ($filter) {
            'unread' => "status = 'Unread'",
            'archived' => "status = 'Archived'",
            default => "status <> 'Archived'"
        };
        $stmt = $this->db->prepare(
            "SELECT notification_id, title, message, type, status, created_at
             FROM notifications
             WHERE Email = ? AND {$statusClause}
             ORDER BY created_at DESC, notification_id DESC
             LIMIT ? OFFSET ?"
        );
        $stmt->bind_param('sii', $email, $perPage, $offset);
        $stmt->execute();
        $items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $items;
    }

    public function changeStatus(string $email, int $id, string $action): bool
    {
        $transition = match ($action) {
            'read' => ["status = 'Read'", "status = 'Unread'"],
            'unread' => ["status = 'Unread'", "status = 'Read'"],
            'archive' => ["status = 'Archived'", "status <> 'Archived'"],
            'restore' => ["status = 'Read'", "status = 'Archived'"],
            default => throw new InvalidArgumentException('Unsupported notification action.')
        };
        [$set, $condition] = $transition;
        $stmt = $this->db->prepare(
            "UPDATE notifications SET {$set} WHERE Email = ? AND notification_id = ? AND {$condition}"
        );
        $stmt->bind_param('si', $email, $id);
        $stmt->execute();
        $changed = $stmt->affected_rows > 0;
        $stmt->close();
        return $changed;
    }

    /** Mark one owned notification as read and return its type for routing. */
    public function openNotification(string $email, int $id): ?string
    {
        $stmt = $this->db->prepare(
            "SELECT type FROM notifications WHERE Email = ? AND notification_id = ? AND status <> 'Archived' LIMIT 1"
        );
        $stmt->bind_param('si', $email, $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if (!$row) {
            return null;
        }
        $this->changeStatus($email, $id, 'read');
        return strtolower(trim((string) ($row['type'] ?? 'system')));
    }

    public function markAllRead(string $email): int
    {
        $stmt = $this->db->prepare("UPDATE notifications SET status = 'Read' WHERE Email = ? AND status = 'Unread'");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $count = $stmt->affected_rows;
        $stmt->close();
        return $count;
    }

    public function deleteOne(string $email, int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM notifications WHERE Email = ? AND notification_id = ?');
        $stmt->bind_param('si', $email, $id);
        $stmt->execute();
        $deleted = $stmt->affected_rows > 0;
        $stmt->close();
        return $deleted;
    }

    public function clear(string $email, string $filter): int
    {
        $condition = match ($filter) {
            'unread' => "status = 'Unread'",
            'archived' => "status = 'Archived'",
            default => "status <> 'Archived'"
        };
        $stmt = $this->db->prepare("DELETE FROM notifications WHERE Email = ? AND {$condition}");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $deleted = $stmt->affected_rows;
        $stmt->close();
        return $deleted;
    }
}