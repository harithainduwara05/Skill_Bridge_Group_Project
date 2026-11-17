<!-- Modal: Urgent Complaint Review Popup -->
<div class="db-modal-backdrop" id="dbComplaintReviewModal">
    <div class="db-modal-dialog">
        <div class="db-modal-header">
            <div style="display: flex; align-items: center; gap: 10px;">
                <div class="db-modal-icon-wrap">
                    <span class="material-symbols-outlined" style="font-size: 20px; color: #ea580c;">warning</span>
                </div>
                <div>
                    <h3 class="db-modal-title">Urgent Complaint Review</h3>
                    <p class="db-modal-subtitle">Review reported issue details and take moderation action.</p>
                </div>
            </div>
            <button type="button" class="db-modal-close" onclick="closeUrgentComplaintReview()" title="Close (Esc)">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>

        <div class="db-modal-body">
            <!-- Badges & Status Strip -->
            <div class="db-modal-meta-strip">
                <span class="db-complaint-badge" id="dbModalComplaintId">#0</span>
                <div style="display: flex; gap: 8px; align-items: center;">
                    <span class="db-pill db-pill-high" id="dbModalPriorityPill">HIGH</span>
                    <span class="db-pill db-pill-status" id="dbModalStatusPill">PENDING</span>
                </div>
            </div>

            <!-- Complaint Title & Description -->
            <div class="db-modal-content-card">
                <h4 class="db-modal-heading" id="dbModalTitle">Complaint Title</h4>
                <div class="db-modal-desc-box" id="dbModalDesc">
                    Detailed description of the issue...
                </div>
            </div>

            <!-- Details Meta Grid -->
            <div class="db-modal-grid">
                <div class="db-modal-info-col">
                    <span class="db-modal-info-label">Complainant Contact</span>
                    <strong class="db-modal-info-val" id="dbModalEmail">user@example.com</strong>
                </div>
                <div class="db-modal-info-col">
                    <span class="db-modal-info-label">Category</span>
                    <strong class="db-modal-info-val" id="dbModalCategory">Technical</strong>
                </div>
                <div class="db-modal-info-col" style="grid-column: span 2;">
                    <span class="db-modal-info-label">Reported On</span>
                    <strong class="db-modal-info-val" id="dbModalDate">Nov 03, 2026</strong>
                </div>
            </div>
        </div>

        <div class="db-modal-footer">
            <button type="button" class="btn-sm secondary" onclick="triggerDismissFromReview()" title="Dismiss this complaint with mandatory reason">
                <span class="material-symbols-outlined" style="font-size: 16px;">visibility_off</span>
                Dismiss
            </button>

            <div style="display: flex; gap: 8px;">
                <button type="button" class="btn-sm secondary" onclick="closeUrgentComplaintReview()">
                    Close
                </button>
                <a href="complain.php" class="btn-sm primary" style="text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
                    <span class="material-symbols-outlined" style="font-size: 16px;">open_in_new</span>
                    Complaints Desk
                </a>
            </div>
        </div>
    </div>
</div>

<script>
window.openUrgentComplaintReview = function(btnOrItem) {
    const item = btnOrItem.closest ? btnOrItem.closest('.complaint-item') : btnOrItem;
    if (!item) return;

    const id       = item.getAttribute('data-id') || '';
    const title    = item.getAttribute('data-title') || 'Complaint Details';
    const desc     = item.getAttribute('data-desc') || 'No description provided.';
    const priority = (item.getAttribute('data-priority') || 'HIGH').toUpperCase();
    const status   = (item.getAttribute('data-status') || 'PENDING').toUpperCase();
    const email    = item.getAttribute('data-email') || 'Not specified';
    const category = item.getAttribute('data-category') || 'General';
    const date     = item.getAttribute('data-date') || 'N/A';

    document.getElementById('dbModalComplaintId').textContent = '#' + id;
    document.getElementById('dbModalDismissId').value = id;
    document.getElementById('dbModalTitle').textContent = title;
    document.getElementById('dbModalDesc').textContent = desc;
    document.getElementById('dbModalEmail').textContent = email;
    document.getElementById('dbModalCategory').textContent = category;
    document.getElementById('dbModalDate').textContent = date;

    const priEl = document.getElementById('dbModalPriorityPill');
    if (priEl) {
        priEl.textContent = priority;
        priEl.className = 'db-pill ' + (priority === 'URGENT' || priority === 'HIGH' ? 'db-pill-high' : (priority === 'MEDIUM' ? 'db-pill-medium' : 'db-pill-low'));
    }

    const stEl = document.getElementById('dbModalStatusPill');
    if (stEl) {
        stEl.textContent = status;
    }

    const modal = document.getElementById('dbComplaintReviewModal');
    if (window.AdminCore && AdminCore.modal) {
        AdminCore.modal.open(modal);
    } else if (modal) {
        modal.style.display = 'flex';
        modal.classList.add('show');
        document.body.style.overflow = 'hidden';
    }
};

window.closeUrgentComplaintReview = function() {
    const modal = document.getElementById('dbComplaintReviewModal');
    if (window.AdminCore && AdminCore.modal) {
        AdminCore.modal.close(modal);
    } else if (modal) {
        modal.style.display = 'none';
        modal.classList.remove('show');
        document.body.style.overflow = '';
    }
};

window.triggerDismissFromReview = function() {
    const id = document.getElementById('dbModalDismissId').value;
    const title = document.getElementById('dbModalTitle').textContent;
    if (typeof openUrgentComplaintDismiss === 'function') {
        openUrgentComplaintDismiss(id, title);
    }
};

// Auto wire ESC and backdrop click
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('dbComplaintReviewModal');
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                closeUrgentComplaintReview();
            }
        });
    }
});
</script>

