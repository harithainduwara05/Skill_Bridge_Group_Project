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