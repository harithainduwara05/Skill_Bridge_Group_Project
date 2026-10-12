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