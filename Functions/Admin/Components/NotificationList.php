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
