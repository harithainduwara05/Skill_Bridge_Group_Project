<?php
declare(strict_types=1);

require_once __DIR__ . '/../../Config/db.php';
require_once __DIR__ . '/../../Session/Session.php';
require_once __DIR__ . '/../../Backend/Student/StudentNotificationRepository.php';

require_role('student');
$user = current_user();
$email = $user['Email'] ?? $user['email'] ?? null;
if (!is_string($email) || $email === '') {
    http_response_code(403);
    exit('Student session email is missing.');
}

function notificationEscape(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

if (empty($_SESSION['student_notifications_csrf'])) {
    $_SESSION['student_notifications_csrf'] = bin2hex(random_bytes(32));
}
$csrf = $_SESSION['student_notifications_csrf'];
$repository = new StudentNotificationRepository($conn);
$allowedFilters = ['all', 'unread', 'archived'];
$filter = (string) ($_GET['filter'] ?? 'all');
if (!in_array($filter, $allowedFilters, true)) {
    $filter = 'all';
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $requestedFilter = (string) ($_POST['filter'] ?? 'all');
    $requestedFilter = in_array($requestedFilter, $allowedFilters, true) ? $requestedFilter : 'all';
    if (!hash_equals($csrf, (string) ($_POST['csrf'] ?? ''))) {
        http_response_code(403);
        exit('Invalid security token. Refresh the page and try again.');
    }
    try {
        $action = (string) ($_POST['action'] ?? '');
        $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $message = '';
        if ($action === 'open' && is_int($id)) {
            $type = $repository->openNotification($email, $id);
            if ($type === null) {
                throw new InvalidArgumentException('Notification is unavailable.');
            }
            // Route only to known local Student pages; no URL is taken from the database.
            $routes = [
                'project' => 'projects.php',
                'team' => 'teams.php',
                'invitation' => 'teams.php',
                'application' => 'applications.php',
                'candidate' => 'applications.php',
                'internship' => 'internships.php',
                'certificate' => 'portfolio.php',
                'proposal' => 'projects.php'
            ];
            $destination = $routes[$type] ?? 'notifications.php';
            if (!is_file(__DIR__ . '/' . $destination)) {
                $destination = 'notifications.php';
            }
            header('Location: ' . $destination, true, 303);
            exit;
        }
        if ($action === 'mark_all_read') {
            $count = $repository->markAllRead($email);
            $message = $count > 0 ? "{$count} notification(s) marked as read." : 'No unread notifications.';
        } elseif ($action === 'clear') {
            $count = $repository->clear($email, $requestedFilter);
            $message = "{$count} notification(s) deleted.";
        } elseif ($action === 'delete' && is_int($id)) {
            $message = $repository->deleteOne($email, $id) ? 'Notification deleted.' : 'Notification not found.';
        } elseif (in_array($action, ['read', 'unread', 'archive', 'restore'], true) && is_int($id)) {
            $changed = $repository->changeStatus($email, $id, $action);
            $message = $changed ? match ($action) {
                'read' => 'Notification marked as read.',
                'unread' => 'Notification marked as unread.',
                'archive' => 'Notification archived.',
                'restore' => 'Notification restored.'
            } : 'No changes were needed.';
        } else {
            throw new InvalidArgumentException('Invalid notification action.');
        }
        $_SESSION['student_notification_flash'] = ['text' => $message, 'error' => false];
    } catch (Throwable $exception) {
        error_log('Student notifications: ' . $exception->getMessage());
        $_SESSION['student_notification_flash'] = ['text' => 'Unable to update notifications. Please retry.', 'error' => true];
    }
    header('Location: notifications.php?filter=' . rawurlencode($requestedFilter), true, 303);
    exit;
}

$page = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 10;
$error = null;
$counts = ['all' => 0, 'unread' => 0, 'archived' => 0];
$items = [];
$pageCount = 1;
try {
    $counts = $repository->counts($email);
    $pageCount = max(1, (int) ceil($counts[$filter] / $perPage));
    $page = min($page, $pageCount);
    $items = $repository->items($email, $filter, $page, $perPage);
} catch (Throwable $exception) {
    error_log('Student notification loading: ' . $exception->getMessage());
    $error = 'Notifications could not be loaded. Check the server error log.';
}
$flash = $_SESSION['student_notification_flash'] ?? null;
unset($_SESSION['student_notification_flash']);
$typeStyles = [
    'project' => ['update', 'fa-folder-open'],
    'team' => ['invitation', 'fa-users'],
    'invitation' => ['invitation', 'fa-user-plus'],
    'application' => ['success', 'fa-briefcase'],
    'complaint' => ['update', 'fa-circle-exclamation'],
    'certificate' => ['success', 'fa-certificate'],
    'system' => ['system', 'fa-bell'],
    'proposal' => ['update', 'fa-file-lines']
];
$pageTitle = 'Notifications | SkillBridge';
$stylesheetPath = __DIR__ . '/../../Assets/CSS/Student/notifications.css';
$extra_css = '<link rel="stylesheet" href="../../Assets/CSS/Student/notifications.css?v='
    . (is_file($stylesheetPath) ? (string) filemtime($stylesheetPath) : '1') . '">';
include __DIR__ . '/../../Includes/student_sidebar.php';
include __DIR__ . '/../../Includes/dash_header.php';
?>
<main class="content notification-page">
    <section class="notification-header">
        <div>
            <h1>Notification Center</h1>
            <p>Stay updated with your career progress and platform activities.</p>
        </div>
        <div class="notification-actions">
            <form method="post">
                <input type="hidden" name="csrf" value="<?= notificationEscape($csrf) ?>">
                <input type="hidden" name="action" value="mark_all_read">
                <input type="hidden" name="filter" value="<?= notificationEscape($filter) ?>">
                <button class="mark-read" type="submit" <?= $counts['unread'] === 0 ? 'disabled' : '' ?>>
                    <i class="fa-solid fa-check-double" aria-hidden="true"></i> Mark All as Read
                </button>
            </form>
            <form method="post" data-confirm="Delete all notifications in this tab permanently?">
                <input type="hidden" name="csrf" value="<?= notificationEscape($csrf) ?>">
                <input type="hidden" name="action" value="clear">
                <input type="hidden" name="filter" value="<?= notificationEscape($filter) ?>">
                <button class="clear-all" type="submit" <?= $counts[$filter] === 0 ? 'disabled' : '' ?>>
                    <i class="fa-regular fa-trash-can" aria-hidden="true"></i> Clear <?= $filter === 'all' ? 'All' : ucfirst($filter) ?>
                </button>
            </form>
        </div>
    </section>

    <?php if ($flash || $error): ?>
        <div class="notification-toast <?= ($error || ($flash['error'] ?? false)) ? 'is-error' : '' ?>" role="status" data-autohide="<?= ($error || ($flash['error'] ?? false)) ? 7000 : 4500 ?>">
            <i class="fa-solid fa-circle-info" aria-hidden="true"></i>
            <span><?= notificationEscape($error ?? $flash['text']) ?></span>
            <button type="button" class="notification-toast-close" aria-label="Dismiss message">&times;</button>
        </div>
    <?php endif; ?>

    <nav class="notification-tabs" aria-label="Notification filters">
        <?php foreach (['all' => 'All', 'unread' => 'Unread', 'archived' => 'Archived'] as $key => $label): ?>
            <a class="<?= $filter === $key ? 'active' : '' ?>" href="?filter=<?= $key ?>" <?= $filter === $key ? 'aria-current="page"' : '' ?>>
                <?= $label ?> <span><?= $counts[$key] ?></span>
            </a>
        <?php endforeach; ?>
    </nav>

    <section class="notification-list" aria-label="Notifications">
        <?php foreach ($items as $item): ?>
            <?php
            $id = (int) $item['notification_id'];
            $type = strtolower((string) ($item['type'] ?? 'system'));
            [$category, $icon] = $typeStyles[$type] ?? ['system', 'fa-bell'];
            $unread = $item['status'] === 'Unread';
            $date = strtotime((string) $item['created_at']);
            ?>
            <article class="notification-card <?= $category ?> <?= $unread ? 'is-unread' : 'read' ?>">
                <div class="notification-icon"><i class="fa-solid <?= $icon ?>" aria-hidden="true"></i></div>
                <div class="notification-content">
                    <div class="notification-top">
                        <form method="post" class="notification-open-form">
                            <input type="hidden" name="csrf" value="<?= notificationEscape($csrf) ?>">
                            <input type="hidden" name="action" value="open">
                            <input type="hidden" name="id" value="<?= $id ?>">
                            <button type="submit" class="notification-open-button" title="Open related page">
                                <?= notificationEscape($item['title']) ?> <?= $unread ? '<span class="unread-dot" aria-label="Unread"></span>' : '' ?>
                                <i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i>
                            </button>
                        </form>
                        <time datetime="<?= $date ? date('c', $date) : '' ?>" title="<?= notificationEscape($item['created_at']) ?>">
                            <?= $date ? date('d M Y, h:i A', $date) : 'Date unavailable' ?>
                        </time>
                    </div>
                    <p><?= nl2br(notificationEscape($item['message'])) ?></p>
                    <div class="notification-buttons">
                        <?php if ($unread): ?>
                            <form method="post" class="notification-inline-form">
                                <input type="hidden" name="csrf" value="<?= notificationEscape($csrf) ?>">
                                <input type="hidden" name="filter" value="<?= $filter ?>">
                                <input type="hidden" name="id" value="<?= $id ?>">
                                <button type="submit" name="action" value="read"><i class="fa-solid fa-check" aria-hidden="true"></i> Mark read</button>
                            </form>
                        <?php else: ?>
                            <?php if ($item['status'] === 'Read'): ?>
                                <form method="post" class="notification-inline-form">
                                    <input type="hidden" name="csrf" value="<?= notificationEscape($csrf) ?>">
                                    <input type="hidden" name="filter" value="<?= $filter ?>">
                                    <input type="hidden" name="id" value="<?= $id ?>">
                                    <button type="submit" name="action" value="unread"><i class="fa-solid fa-envelope" aria-hidden="true"></i> Mark unread</button>
                                </form>
                            <?php endif; ?>
                        <?php endif; ?>
                        <form method="post" class="notification-inline-form">
                            <input type="hidden" name="csrf" value="<?= notificationEscape($csrf) ?>">
                            <input type="hidden" name="filter" value="<?= $filter ?>">
                            <input type="hidden" name="id" value="<?= $id ?>">
                            <?php if ($filter === 'archived'): ?>
                                <button type="submit" name="action" value="restore"><i class="fa-solid fa-box-open" aria-hidden="true"></i> Restore</button>
                            <?php else: ?>
                                <button type="submit" name="action" value="archive"><i class="fa-solid fa-box-archive" aria-hidden="true"></i> Archive</button>
                            <?php endif; ?>
                        </form>
                        <form method="post" class="notification-inline-form" data-confirm="Delete this notification permanently?">
                            <input type="hidden" name="csrf" value="<?= notificationEscape($csrf) ?>">
                            <input type="hidden" name="filter" value="<?= $filter ?>">
                            <input type="hidden" name="id" value="<?= $id ?>">
                            <button type="submit" name="action" value="delete" class="notification-danger"><i class="fa-regular fa-trash-can" aria-hidden="true"></i> Delete</button>
                        </form>
                    </div>
                </div>
            </article>
        <?php endforeach; ?>
        <?php if (!$items): ?>
            <div class="empty-notification">
                <i class="fa-regular fa-bell-slash" aria-hidden="true"></i>
                <p><?= $filter === 'unread' ? 'You have no unread notifications.' : ($filter === 'archived' ? 'You have no archived notifications.' : 'You have no notifications yet.') ?></p>
            </div>
        <?php endif; ?>
    </section>

    <?php if ($pageCount > 1): ?>
        <nav class="notification-pagination" aria-label="Notification pages">
            <?php for ($number = 1; $number <= $pageCount; $number++): ?>
                <a href="?filter=<?= $filter ?>&amp;page=<?= $number ?>" class="<?= $number === $page ? 'active' : '' ?>" <?= $number === $page ? 'aria-current="page"' : '' ?>><?= $number ?></a>
            <?php endfor; ?>
        </nav>
    <?php endif; ?>
</main>
<script src="../../Assets/JS/Student/notifications.js?v=2" defer></script>
<?php include __DIR__ . '/../../Includes/dash_footer.php'; ?>