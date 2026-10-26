<?php
/*
| Organization backend - Notifications: loads this organization's notifications from the DB
| and handles Mark as read / Archive / Mark all as read / Clear all.
| Used by: Functions/Organization/notifications.php
| (the page already loaded Config/db.php + Session/Session.php and checked the role)
|
| notifications.status:  'Unread' -> 'Read' -> 'Archived'
*/

$user = current_user();
$organization_email = $user['email'];

// ---- Actions from the page (sent with fetch, answered with JSON) ----
// must run before any HTML is printed
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $id     = (int)($_POST['id'] ?? 0);
    $ok     = false;

    if ($action === 'read' && $id > 0) {
        // only an Unread one becomes Read (an Archived one stays Archived)
        $q = $conn->prepare("UPDATE notifications SET status = 'Read'
                             WHERE notification_id = ? AND Email = ? AND status = 'Unread'");
        $q->bind_param("is", $id, $organization_email);
        $ok = $q->execute();
    } elseif ($action === 'archive' && $id > 0) {
        $q = $conn->prepare("UPDATE notifications SET status = 'Archived'
                             WHERE notification_id = ? AND Email = ?");
        $q->bind_param("is", $id, $organization_email);
        $ok = $q->execute();
    } elseif ($action === 'unarchive' && $id > 0) {
        $q = $conn->prepare("UPDATE notifications SET status = 'Read'
                             WHERE notification_id = ? AND Email = ?");
        $q->bind_param("is", $id, $organization_email);
        $ok = $q->execute();
    } elseif ($action === 'read_all') {
        $q = $conn->prepare("UPDATE notifications SET status = 'Read' WHERE Email = ? AND status = 'Unread'");
        $q->bind_param("s", $organization_email);
        $ok = $q->execute();
    } elseif ($action === 'clear_all') {
        $q = $conn->prepare("UPDATE notifications SET status = 'Archived' WHERE Email = ? AND status <> 'Archived'");
        $q->bind_param("s", $organization_email);
        $ok = $q->execute();
    }

    header('Content-Type: application/json');
    echo json_encode(['success' => (bool)$ok]);
    exit;
}

// ---- "5 mins ago", "Yesterday", "Oct 12, 2026" ----
function notifTimeAgo($datetime) {
    $ts = strtotime($datetime);
    if ($ts === false) return '';
    $diff = time() - $ts;
    if ($diff < 60)     return 'Just now';
    if ($diff < 3600)   return floor($diff / 60) . ' mins ago';
    if ($diff < 86400)  return floor($diff / 3600) . ' hours ago';
    if ($diff < 172800) return 'Yesterday';
    if ($diff < 604800) return floor($diff / 86400) . ' days ago';
    return date('M d, Y', $ts);
}

// ---- Colour, icon and button for each notification ----
// Decided from the title first (project status messages from the Admin),
// then from the type column.
function notifStyle($title, $type) {
    $t = strtolower($title);

    // a team leader sent the final report -> open Teams to review it
    if (strpos($t, 'final report') !== false)
        return ['amber', 'fact_check', 'teams.php', 'Review Report'];

    if ((strpos($t, 'accepted') !== false || strpos($t, 'published') !== false) && strpos($t, 'proposal') === false)
        return ['green', 'verified', 'manage_projects.php?status=published', 'View Project'];
    if (strpos($t, 'reactivated') !== false)
        return ['green', 'restart_alt', 'manage_projects.php?status=published', 'View Project'];
    if (strpos($t, 'hold') !== false)
        return ['amber', 'pause_circle', 'manage_projects.php?status=hold', 'Fix & Resubmit'];
    if (strpos($t, 'rejected') !== false)
        return ['red', 'cancel', 'manage_projects.php?status=rejected', 'View Project'];
    if (strpos($t, 'sent for review') !== false || strpos($t, 'activation request') !== false)
        return ['navy', 'hourglass_top', 'manage_projects.php', 'View Projects'];

    switch (strtolower((string)$type)) {
        case 'proposal':  return ['navy', 'description', 'proposal.php', 'View Proposals'];
        case 'candidate': return ['navy', 'person_add', 'proposal.php', 'View Proposals'];
        case 'team':      return ['navy', 'groups', 'teams.php', 'View Teams'];
        case 'project':   return ['navy', 'folder_open', 'manage_projects.php', 'View Projects'];
        case 'complaint': return ['gray', 'report', '', ''];
        default:          return ['gray', 'notifications', '', ''];
    }
}

// ---- Load this organization's notifications (newest first) ----
$nq = $conn->prepare("SELECT notification_id, title, message, type, status, created_at
                      FROM notifications WHERE Email = ? ORDER BY created_at DESC, notification_id DESC");
$nq->bind_param("s", $organization_email);
$nq->execute();

// named $orgNotifications (not $notifications): Includes/dash_header.php uses
// $notifications for the bell dropdown and would overwrite this list
$orgNotifications = [];
foreach ($nq->get_result()->fetch_all(MYSQLI_ASSOC) as $row) {
    [$color, $glyph, $link, $linkLabel] = notifStyle($row['title'] ?? '', $row['type'] ?? '');
    $row['status'] = ucfirst(strtolower(trim($row['status'] ?? 'Unread')));   // 'Unread' / 'Read' / 'Archived'
    $orgNotifications[] = [
        'id'       => (int)$row['notification_id'],
        'title'    => $row['title'] ?? '',
        'msg'      => $row['message'] ?? '',
        'time'     => notifTimeAgo($row['created_at']),
        'read'     => $row['status'] !== 'Unread',
        'archived' => $row['status'] === 'Archived',
        'color'    => $color,
        'glyph'    => $glyph,
        'link'     => $link,
        'label'    => $linkLabel,
    ];
}