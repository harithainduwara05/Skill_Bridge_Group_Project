<?php
require_once "../../../Config/db.php";
require_once "../../../Session/Session.php";

require_login();
require_role('admin');

$user = current_user();
$adminEmail = $user['Email'] ?? $user['email'] ?? '';

require_once "AdminBackend.php";

$flash = null;

// ============================================
// HANDLE POST ACTIONS
// ============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $notifId = intval($_POST['notification_id'] ?? 0);

    // 1. Mark Single Notification as Read
    if ($action === 'mark_read' && $notifId > 0) {
        $stmt = $conn->prepare("UPDATE notifications SET status = 'Read' WHERE notification_id = ? AND Email = ?");
        if ($stmt) {
            $stmt->bind_param("is", $notifId, $adminEmail);
            if ($stmt->execute()) {
                $flash = ['type' => 'success', 'message' => 'Notification marked as read.'];
            }
            $stmt->close();
        }
    }

    // 2. Mark All as Read
    elseif ($action === 'mark_all_read') {
        $stmt = $conn->prepare("UPDATE notifications SET status = 'Read' WHERE Email = ? AND status = 'Unread'");
        if ($stmt) {
            $stmt->bind_param("s", $adminEmail);
            if ($stmt->execute()) {
                $flash = ['type' => 'success', 'message' => 'All notifications marked as read.'];
            }
            $stmt->close();
        }
    }

    // 3. Delete Notification
    elseif ($action === 'delete' && $notifId > 0) {
        $stmt = $conn->prepare("DELETE FROM notifications WHERE notification_id = ? AND Email = ?");
        if ($stmt) {
            $stmt->bind_param("is", $notifId, $adminEmail);
            if ($stmt->execute()) {
                $flash = ['type' => 'success', 'message' => 'Notification removed.'];
            }
            $stmt->close();
        }
    }
}

// ============================================
// STATS & COUNTS
// ============================================
$totalCount = 0;
$unreadCount = 0;
$inquiryCount = 0;

$countStmt = $conn->prepare("
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = 'Unread' THEN 1 ELSE 0 END) as unread,
        SUM(CASE WHEN type = 'contact_inquiry' THEN 1 ELSE 0 END) as inquiries
    FROM notifications 
    WHERE Email = ?
");
if ($countStmt) {
    $countStmt->bind_param("s", $adminEmail);
    $countStmt->execute();
    $res = $countStmt->get_result()->fetch_assoc();
    $totalCount = intval($res['total'] ?? 0);
    $unreadCount = intval($res['unread'] ?? 0);
    $inquiryCount = intval($res['inquiries'] ?? 0);
    $countStmt->close();
}

// ============================================
// FILTERING
// ============================================
$filterTab = $_GET['tab'] ?? 'all';
$query = "SELECT * FROM notifications WHERE Email = ?";
$params = [$adminEmail];
$types = "s";

if ($filterTab === 'unread') {
    $query .= " AND status = 'Unread'";
} elseif ($filterTab === 'inquiries') {
    $query .= " AND type = 'contact_inquiry'";
}

$query .= " ORDER BY notification_id DESC";

$notifList = [];
$stmt = $conn->prepare($query);
if ($stmt) {
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $notifList = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

include "../../../Includes/admin_sidebar.php";
?>
<link rel="stylesheet" href="../../../Assets/CSS/Admin/notification.css?v=<?php echo time(); ?>">
<link rel="stylesheet" href="../../../Assets/CSS/flash-toast.css">
<?php
include "../../../Includes/dash_header.php";
?>

<main class="content">

    <!-- Flash Toast Notification -->
    <?php if (!empty($flash)): ?>
    <div class="flash-toast flash-<?= htmlspecialchars($flash['type']) ?>" id="flashToast" style="position:fixed; top:20px; right:20px; z-index:9999; background:#ffffff; border-left:4px solid <?= $flash['type'] === 'success' ? '#10b981' : '#ef4444' ?>; box-shadow:0 4px 14px rgba(0,0,0,0.1); border-radius:8px; padding:12px 18px; display:flex; align-items:center; gap:10px;">
        <span class="material-symbols-outlined" style="color:<?= $flash['type'] === 'success' ? '#10b981' : '#ef4444' ?>;">
            <?= $flash['type'] === 'success' ? 'check_circle' : 'error' ?>
        </span>
        <span style="font-size:13.5px; font-weight:600; color:#1e293b;"><?= htmlspecialchars($flash['message']) ?></span>
    </div>
    <script>
        setTimeout(function() {
            var t = document.getElementById('flashToast');
            if (t) t.remove();
        }, 3500);
    </script>
    <?php endif; ?>

    <div class="notif-container">

        <!-- Breadcrumb -->
        <div class="notif-breadcrumb">
            <a href="dashboard.php">Admin</a>
            <span class="material-symbols-outlined" style="font-size:14px;">chevron_right</span>
            <span>Notifications</span>
        </div>

        <!-- Header Row -->
        <div class="notif-header-row">
            <div class="notif-title-wrap">
                <h1>Admin Notifications</h1>
                <p>Manage and monitor inquiries, system alerts, and platform messages.</p>
            </div>

            <?php if ($unreadCount > 0): ?>
            <form method="POST" action="" style="margin:0;">
                <input type="hidden" name="action" value="mark_all_read">
                <button type="submit" class="btn-mark-all">
                    <span class="material-symbols-outlined" style="font-size:18px;">done_all</span>
                    Mark All as Read
                </button>
            </form>
            <?php endif; ?>
        </div>

        <!-- Stats Overview Cards -->
        <div class="notif-stats-grid">
            <div class="notif-stat-card">
                <div class="notif-stat-icon blue">
                    <span class="material-symbols-outlined">notifications</span>
                </div>
                <div class="notif-stat-data">
                    <h3><?= number_format($totalCount) ?></h3>
                    <span>Total Notifications</span>
                </div>
            </div>

            <div class="notif-stat-card">
                <div class="notif-stat-icon amber">
                    <span class="material-symbols-outlined">mark_email_unread</span>
                </div>
                <div class="notif-stat-data">
                    <h3><?= number_format($unreadCount) ?></h3>
                    <span>Unread Notifications</span>
                </div>
            </div>

            <div class="notif-stat-card">
                <div class="notif-stat-icon purple">
                    <span class="material-symbols-outlined">contact_support</span>
                </div>
                <div class="notif-stat-data">
                    <h3><?= number_format($inquiryCount) ?></h3>
                    <span>Contact Inquiries</span>
                </div>
            </div>
        </div>

        <!-- Filter Tabs -->
        <div class="notif-filter-tabs">
            <a href="?tab=all" class="notif-tab <?= $filterTab === 'all' ? 'active' : '' ?>">
                All
                <span class="notif-tab-badge"><?= $totalCount ?></span>
            </a>
            <a href="?tab=unread" class="notif-tab <?= $filterTab === 'unread' ? 'active' : '' ?>">
                Unread
                <span class="notif-tab-badge"><?= $unreadCount ?></span>
            </a>
            <a href="?tab=inquiries" class="notif-tab <?= $filterTab === 'inquiries' ? 'active' : '' ?>">
                Contact Inquiries
                <span class="notif-tab-badge"><?= $inquiryCount ?></span>
            </a>
        </div>

        <!-- Notifications List -->
        <?php if (empty($notifList)): ?>
            <div class="notif-empty-state">
                <span class="material-symbols-outlined">notifications_off</span>
                <h3>No Notifications Found</h3>
                <p>You have no notifications under this category at the moment.</p>
            </div>
        <?php else: ?>
            <div class="notif-list">
                <?php foreach ($notifList as $item): 
                    $isUnread = ($item['status'] === 'Unread');
                    $isContact = ($item['type'] === 'contact_inquiry');
                    $formattedTime = date('M d, Y • h:i A', strtotime($item['created_at']));
                ?>
                    <div class="notif-item-card <?= $isUnread ? 'unread' : '' ?>">
                        <div class="notif-item-icon <?= $isContact ? 'inquiry' : ($item['type'] === 'admin' ? 'admin' : 'system') ?>">
                            <span class="material-symbols-outlined">
                                <?= $isContact ? 'mail' : ($item['type'] === 'admin' ? 'security' : 'info') ?>
                            </span>
                        </div>

                        <div class="notif-item-content">
                            <div class="notif-item-header">
                                <h4><?= htmlspecialchars($item['title'] ?? 'Notification') ?></h4>
                                <div class="notif-tag-wrap">
                                    <?php if ($isContact): ?>
                                        <span class="badge-tag inquiry">Contact Inquiry</span>
                                    <?php else: ?>
                                        <span class="badge-tag admin"><?= htmlspecialchars(ucfirst($item['type'] ?? 'System')) ?></span>
                                    <?php endif; ?>

                                    <span class="badge-tag <?= $isUnread ? 'unread' : 'read' ?>">
                                        <?= htmlspecialchars($item['status']) ?>
                                    </span>
                                </div>
                            </div>

                            <div class="notif-item-time">
                                <span class="material-symbols-outlined" style="font-size:14px;">schedule</span>
                                <?= htmlspecialchars($formattedTime) ?>
                            </div>

                            <div class="notif-item-body"><?= htmlspecialchars($item['message'] ?? '') ?></div>

                            <div class="notif-item-actions">
                                <?php if ($isUnread): ?>
                                <form method="POST" action="" style="margin:0;">
                                    <input type="hidden" name="action" value="mark_read">
                                    <input type="hidden" name="notification_id" value="<?= $item['notification_id'] ?>">
                                    <button type="submit" class="btn-item-action">
                                        <span class="material-symbols-outlined" style="font-size:15px;">check</span>
                                        Mark as Read
                                    </button>
                                </form>
                                <?php endif; ?>

                                <button type="button" class="btn-item-action delete" onclick="openDeleteModal(<?= $item['notification_id'] ?>, <?= htmlspecialchars(json_encode($item['title'] ?? 'Notification'), ENT_QUOTES, 'UTF-8') ?>)">
                                    <span class="material-symbols-outlined" style="font-size:15px;">delete</span>
                                    Delete
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </div>

</main>

<!-- MODAL: DELETE CONFIRMATION POPUP -->
<div class="notif-modal-overlay" id="deleteNotifModal" onclick="closeDeleteModal(event)">
    <div class="notif-modal-card" onclick="event.stopPropagation()">
        <div class="notif-modal-header">
            <h3 style="color:#dc2626; display:flex; align-items:center; gap:8px;">
                <span class="material-symbols-outlined" style="font-size:22px; color:#dc2626;">delete</span>
                Delete Notification
            </h3>
            <button type="button" class="notif-modal-close" onclick="closeDeleteModal()" aria-label="Close">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <form method="POST" action="">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="notification_id" id="deleteNotifId">

            <div class="notif-modal-body">
                <div style="display:flex; gap:16px; align-items:flex-start;">
                    <div class="notif-modal-warning-icon">
                        <span class="material-symbols-outlined">warning</span>
                    </div>
                    <div>
                        <p style="font-size: 14.5px; font-weight:600; color:#0f172a; margin:0 0 6px 0;">
                            Are you sure you want to delete this notification?
                        </p>
                        <p style="font-size: 13px; color:#64748b; margin:0; line-height:1.5;">
                            Item: <strong id="deleteNotifTitle" style="color:#0f172a;"></strong><br>
                            This action cannot be undone. The notification will be permanently removed.
                        </p>
                    </div>
                </div>
            </div>

            <div class="notif-modal-footer">
                <button type="button" class="btn-modal-secondary" onclick="closeDeleteModal()">Cancel</button>
                <button type="submit" class="btn-modal-danger">
                    <span class="material-symbols-outlined" style="font-size:16px;">delete</span>
                    Delete Notification
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openDeleteModal(notifId, notifTitle) {
    document.getElementById('deleteNotifId').value = notifId;
    document.getElementById('deleteNotifTitle').textContent = notifTitle;
    document.getElementById('deleteNotifModal').classList.add('active');
}

function closeDeleteModal(event) {
    if (!event || event.target.id === 'deleteNotifModal' || event.type === 'click') {
        const modal = document.getElementById('deleteNotifModal');
        if (modal) modal.classList.remove('active');
    }
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeDeleteModal();
    }
});
</script>

<footer class="footer">
    <div>&copy; 2026 SkillBridge. All rights reserved.</div>
    <div class="footer-links">
        <a href="#">Help Center</a>
        <a href="#">Privacy Policy</a>
        <a href="#">Terms of Service</a>
    </div>
</footer>

<?php include "../../../Includes/dash_footer.php"; ?>