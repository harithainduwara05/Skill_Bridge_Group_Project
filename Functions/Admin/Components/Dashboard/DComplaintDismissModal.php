<!-- Modal: Urgent Complaint Dismissal with Mandatory Reason -->
<div class="db-modal-backdrop" id="dbComplaintDismissModal">
    <div class="db-modal-dialog" style="max-width: 500px;">
        <form method="POST" id="dbDismissForm" onsubmit="return validateDismissReason()">
            <input type="hidden" name="action" value="dismiss">
            <input type="hidden" name="complaint_id" id="dbDismissComplaintId" value="">

            <div class="db-modal-header">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <div class="db-modal-icon-wrap" style="background: #fef2f2; border-color: #fee2e2;">
                        <span class="material-symbols-outlined" style="font-size: 20px; color: #dc2626;">cancel</span>
                    </div>
                    <div>
                        <h3 class="db-modal-title" id="dbDismissHeaderId">#CMP-0000</h3>
                        <p class="db-modal-subtitle">Complaint Investigation & Resolution</p>
                    </div>
                </div>
                <button type="button" class="db-modal-close" onclick="closeUrgentComplaintDismiss()" title="Close (Esc)">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>

            <div class="db-modal-body" style="gap: 16px;">
                <!-- Complaint Mini Info Card -->
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 12px 14px;">
                    <span style="font-size: 11px; text-transform: uppercase; font-weight: 700; color: #64748b; letter-spacing: 0.5px;">Target Complaint</span>
                    <h4 id="dbDismissTargetTitle" style="margin: 4px 0 0; font-size: 14px; font-weight: 700; color: #0f172a; line-height: 1.35;"></h4>
                </div>

                <!-- Update Resolution Status Preview -->
                <div style="display: flex; flex-direction: column; gap: 4px;">
                    <label style="font-size: 12px; font-weight: 600; color: #334155;">Update Resolution Status</label>
                    <div style="padding: 10px 14px; background: #f1f5f9; border-radius: 8px; font-size: 13px; font-weight: 600; color: #475569; display: flex; align-items: center; justify-content: space-between;">
                        <span>Dismissed (No Action Required)</span>
                        <span class="material-symbols-outlined" style="font-size: 18px; color: #94a3b8;">lock</span>
                    </div>
                </div>

                <!-- Mandatory Dismissal Reason -->
                <div style="display: flex; flex-direction: column; gap: 6px;">
                    <label for="dbDismissReasonText" style="font-size: 12.5px; font-weight: 700; color: #dc2626; display: flex; align-items: center; gap: 4px;">
                        <span>Dismissal Reason *</span>
                        <span style="font-weight: 600; font-size: 11.5px;">(Mandatory)</span>
                    </label>
                    <textarea 
                        id="dbDismissReasonText" 
                        name="dismiss_reason" 
                        required 
                        rows="3"
                        placeholder="e.g. Resolved by Administrator via Quick Action."
                        style="width: 100%; box-sizing: border-box; padding: 10px 12px; font-size: 13px; font-family: inherit; color: #0f172a; border: 1.5px solid #fca5a5; border-radius: 8px; outline: none; transition: border-color 0.2s; resize: vertical;"
                    ></textarea>
                    <small style="font-size: 11.5px; color: #dc2626; display: flex; align-items: center; gap: 4px; margin-top: 2px;">
                        <span class="material-symbols-outlined" style="font-size: 14px;">error</span>
                        A dismissal reason is mandatory for the complainant.
                    </small>
                </div>
            </div>

            <div class="db-modal-footer">
                <button type="button" class="btn-sm secondary" onclick="closeUrgentComplaintDismiss()">
                    Close
                </button>
                <button type="submit" class="btn-sm" style="background: #dc2626; color: #fff; display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px;">
                    <span class="material-symbols-outlined" style="font-size: 16px;">cancel</span>
                    Dismiss Complaint
                </button>
            </div>
        </form>
    </div>
</div>

<script>
window.openUrgentComplaintDismiss = function(id, title) {
    if (!id) return;
    const formattedId = '#CMP-' + String(id).padStart(4, '0');
    
    document.getElementById('dbDismissComplaintId').value = id;
    document.getElementById('dbDismissHeaderId').textContent = formattedId;
    document.getElementById('dbDismissTargetTitle').textContent = title || ('Complaint #' + id);
    
    const reasonInput = document.getElementById('dbDismissReasonText');
    if (reasonInput) {
        reasonInput.value = 'Resolved by Administrator via Quick Action.';
        setTimeout(() => reasonInput.focus(), 150);
    }

    // Close review modal if open
    if (typeof closeUrgentComplaintReview === 'function') {
        closeUrgentComplaintReview();
    }

    const modal = document.getElementById('dbComplaintDismissModal');
    if (window.AdminCore && AdminCore.modal) {
        AdminCore.modal.open(modal);
    } else if (modal) {
        modal.style.display = 'flex';
        modal.classList.add('show');
        document.body.style.overflow = 'hidden';
    }
};

window.closeUrgentComplaintDismiss = function() {
    const modal = document.getElementById('dbComplaintDismissModal');
    if (window.AdminCore && AdminCore.modal) {
        AdminCore.modal.close(modal);
    } else if (modal) {
        modal.style.display = 'none';
        modal.classList.remove('show');
        document.body.style.overflow = '';
    }
};

function validateDismissReason() {
    const reasonInput = document.getElementById('dbDismissReasonText');
    if (!reasonInput || !reasonInput.value.trim()) {
        alert('Please provide a dismissal reason for the complainant.');
        if (reasonInput) reasonInput.focus();
        return false;
    }
    return true;
}

// Auto wire ESC and backdrop click for Dismiss Modal
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('dbComplaintDismissModal');
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                closeUrgentComplaintDismiss();
            }
        });
    }
});
</script>

