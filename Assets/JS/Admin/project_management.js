

document.addEventListener('DOMContentLoaded', () => {
    initProjectManagement();
});

function initProjectManagement() {
    // --------------------------------------------------------------------------
    // DOM Element References & Configuration
    // --------------------------------------------------------------------------
    const searchInput        = document.getElementById('pmSearchInput');
    const statusFilter       = document.getElementById('pmStatusFilter');
    const typeFilter         = document.getElementById('pmTypeFilter');
    const orgFilter          = document.getElementById('pmOrgFilter');
    const tableBody          = document.getElementById('pmTableBody');
    const paginationInfo     = document.getElementById('pmPaginationInfo');
    const paginationControls = document.getElementById('pmPaginationControls') || document.querySelector('.pm-pagination-controls');

    // Modals & Forms
    const detailsModal    = document.getElementById('pmDetailsModal');
    const holdModal       = document.getElementById('pmHoldModal');
    const holdForm        = document.getElementById('pmHoldProjectForm');
    const reviewHoldModal = document.getElementById('pmReviewHoldModal');
    const rejectModal     = document.getElementById('pmRejectModal');
    const rejectForm      = document.getElementById('pmRejectProjectForm');

    // Pagination State
    const PAGE_SIZE = 5;
    let currentPage = 1;
    let matchedRows = [];

    // --------------------------------------------------------------------------
    // Filter & Search Engine
    // --------------------------------------------------------------------------
    function applyFilters(resetPage = true) {
        if (!tableBody) return;
        if (resetPage) currentPage = 1;

        const searchTerm     = searchInput ? searchInput.value.toLowerCase().trim() : '';
        const selectedStatus = statusFilter ? statusFilter.value.toLowerCase() : 'all';
        const selectedType   = typeFilter ? typeFilter.value.toLowerCase() : 'all';
        const selectedOrg    = orgFilter ? orgFilter.value.toLowerCase() : 'all';

        const rows = Array.from(tableBody.querySelectorAll('tr.pm-data-row'));
        matchedRows = [];

        rows.forEach(row => {
            const projectId       = (row.getAttribute('data-id') || '').toLowerCase();
            const projectTitle    = (row.getAttribute('data-title') || '').toLowerCase();
            const projectCategory = (row.getAttribute('data-category') || '').toLowerCase();
            const projectOrg      = (row.getAttribute('data-org') || '').toLowerCase();
            const projectStatus   = (row.getAttribute('data-status') || '').toLowerCase();
            const projectSkills   = (row.getAttribute('data-skills') || '').toLowerCase();

            // Match search query
            const matchesSearch = !searchTerm ||
                projectId.includes(searchTerm) ||
                projectTitle.includes(searchTerm) ||
                projectCategory.includes(searchTerm) ||
                projectOrg.includes(searchTerm) ||
                projectSkills.includes(searchTerm);

            // Match status
            let matchesStatus = (selectedStatus === 'all');
            if (!matchesStatus) {
                if (selectedStatus === 'active') {
                    matchesStatus = (projectStatus === 'active' || projectStatus === 'inprogress');
                } else if (selectedStatus === 'completed' || selectedStatus === 'close') {
                    matchesStatus = (projectStatus === 'completed' || projectStatus === 'close' || projectStatus === 'complete');
                } else if (selectedStatus === 'review' || selectedStatus === 'reviewing') {
                    matchesStatus = (projectStatus === 'review' || projectStatus === 'reviewing' || projectStatus === 'rewiewing');
                } else {
                    matchesStatus = (projectStatus === selectedStatus);
                }
            }

            // Match type/category & organization
            const matchesType = (selectedType === 'all') || (projectCategory.includes(selectedType));
            const matchesOrg  = (selectedOrg === 'all')  || (projectOrg.includes(selectedOrg));

            if (matchesSearch && matchesStatus && matchesType && matchesOrg) {
                matchedRows.push(row);
            }
        });

        // Delegate pagination to AdminCore
        AdminCore.paginate({
            tableBody,
            rows: matchedRows,
            pageSize: PAGE_SIZE,
            currentPage,
            infoEl: paginationInfo,
            controlsEl: paginationControls,
            onPageChange: (newPage) => {
                currentPage = newPage;
                applyFilters(false);
            },
            emptyIcon: 'folder_off',
            emptyMessage: 'No matching projects found',
            emptySubtext: 'Try adjusting your search query or filter selections.',
            colspan: 8
        });
    }

    // Attach search and filter event listeners
    if (searchInput)  searchInput.addEventListener('input', () => applyFilters(true));
    if (statusFilter) statusFilter.addEventListener('change', () => applyFilters(true));
    if (typeFilter)   typeFilter.addEventListener('change', () => applyFilters(true));
    if (orgFilter)    orgFilter.addEventListener('change', () => applyFilters(true));

    // --------------------------------------------------------------------------
    // Modal Bindings via AdminCore
    // --------------------------------------------------------------------------
    AdminCore.modal.bind(detailsModal);
    AdminCore.modal.bind(holdModal, { form: holdForm });
    AdminCore.modal.bind(reviewHoldModal);
    AdminCore.modal.bind(rejectModal, { form: rejectForm });

    window.closeProjectDetailsModal = () => AdminCore.modal.close(detailsModal);
    window.closeHoldModal        = () => AdminCore.modal.close(holdModal, holdForm);
    window.closeReviewHoldModal  = () => AdminCore.modal.close(reviewHoldModal);
    window.closeRejectReasonModal= () => AdminCore.modal.close(rejectModal, rejectForm);

    // --------------------------------------------------------------------------
    // Project Details Modal View
    // --------------------------------------------------------------------------
    window.viewProjectDetails = function(projectId) {
        if (!detailsModal) return;
        const row = document.querySelector(`tr[data-id="${projectId}"]`);
        if (!row) return;

        const title        = row.getAttribute('data-title') || projectId;
        const category     = row.getAttribute('data-category') || 'General';
        const org          = row.getAttribute('data-org') || 'N/A';
        const status       = row.getAttribute('data-status') || 'Active';
        const skills       = row.getAttribute('data-skills') || 'General';
        const holdReason   = row.getAttribute('data-hold-reason') || '';
        const holdDays     = row.getAttribute('data-hold-days') || '5';
        const rejectReason = row.getAttribute('data-reject-reason') || '';

        document.getElementById('pmDetailId').textContent       = projectId;
        document.getElementById('pmDetailTitle').textContent    = title;
        document.getElementById('pmDetailCategory').textContent = category;
        document.getElementById('pmDetailOrg').textContent      = org;
        document.getElementById('pmDetailSkills').textContent   = skills;

        const statusEl = document.getElementById('pmDetailStatus');
        if (statusEl) {
            statusEl.className = 'pm-status-pill';
            if (status === 'Active') {
                statusEl.textContent = 'Active';
                statusEl.classList.add('status-active');
            } else if (status === 'Hold') {
                statusEl.innerHTML = `<span class="material-symbols-outlined" style="font-size: 13px; margin-right: 3px;">schedule</span>Hold (${holdDays}d left)`;
                statusEl.classList.add('status-hold');
            } else if (status === 'Completed' || status === 'Close' || status === 'Complete') {
                statusEl.textContent = 'Completed';
                statusEl.classList.add('status-completed');
            } else if (status === 'Review') {
                statusEl.textContent = 'Review';
                statusEl.classList.add('status-review');
            } else if (status === 'Rejected') {
                statusEl.textContent = 'Rejected';
                statusEl.classList.add('status-rejected');
            }
        }

        // Hold Status Banner
        const holdBox = document.getElementById('pmDetailHoldBox');
        if (holdBox) {
            holdBox.style.display = (status === 'Hold') ? 'block' : 'none';
            if (status === 'Hold') {
                const holdReasonEl   = document.getElementById('pmDetailHoldReason');
                const holdDeadlineEl = document.getElementById('pmDetailHoldDeadline');
                if (holdReasonEl)   holdReasonEl.textContent = holdReason || 'Complaint received or milestone discrepancy reported.';
                if (holdDeadlineEl) holdDeadlineEl.textContent = `${holdDays} Days Left`;
            }
        }

        // Rejected Status Banner
        const rejectedBox = document.getElementById('pmDetailRejectedBox');
        if (rejectedBox) {
            rejectedBox.style.display = (status === 'Rejected') ? 'block' : 'none';
            if (status === 'Rejected') {
                const rejectReasonEl = document.getElementById('pmDetailRejectReason');
                if (rejectReasonEl) rejectReasonEl.textContent = 'Reason: ' + (rejectReason || 'Failed grace period response or committed policy violation.');
            }
        }

        // Moderation Controls
        const actionControls = document.getElementById('pmDetailActionControls');
        if (actionControls) {
            if (status === 'Active' || status === 'Review') {
                actionControls.innerHTML = `
                    <div style="display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap;">
                        <div>
                            <label style="font-size: 13px; font-weight: 700; color: #1e293b; display: block; margin-bottom: 2px;">Ethical Moderation Action</label>
                            <span style="font-size: 12px; color: #64748b;">Notice a complaint or workflow issue? Place project on Hold to give team time to clarify.</span>
                        </div>
                        <button type="button" class="pm-btn-hold" onclick="openHoldModal('${projectId}')">
                            <span class="material-symbols-outlined" style="font-size: 18px;">pause_circle</span>
                            <span>Place on Hold</span>
                        </button>
                    </div>
                `;
            } else if (status === 'Hold') {
                actionControls.innerHTML = `
                    <div style="display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap;">
                        <div>
                            <label style="font-size: 13px; font-weight: 700; color: #1e293b; display: block; margin-bottom: 2px;">Hold Review & Decision</label>
                            <span style="font-size: 12px; color: #64748b;">Review team's submitted justification against pause reason to decide Active or Reject.</span>
                        </div>
                        <button type="button" class="pm-btn-reactivate" onclick="openReviewHoldModal('${projectId}')">
                            <span class="material-symbols-outlined" style="font-size: 18px;">task_alt</span>
                            <span>Open Hold Review</span>
                        </button>
                    </div>
                `;
            } else if (status === 'Rejected') {
                actionControls.innerHTML = `
                    <div style="display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 12px 14px; background: #fef2f2; border-radius: 10px; border: 1px dashed #fca5a5;">
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <span class="material-symbols-outlined" style="color: #dc2626; font-size: 20px;">lock</span>
                            <span style="font-size: 12.5px; color: #991b1b; font-weight: 500;">This project is permanently terminated. Reactivation is strictly prohibited.</span>
                        </div>
                        <span style="font-size: 11px; font-weight: 700; color: #dc2626; background: #fee2e2; padding: 4px 8px; border-radius: 6px; white-space: nowrap;">TERMINAL STATE</span>
                    </div>
                `;
            } else {
                actionControls.innerHTML = `<div style="font-size: 12.5px; color: #64748b; font-style: italic;">This project has been completed. No active moderation needed.</div>`;
            }
        }

        AdminCore.modal.open(detailsModal);
    };

    // --------------------------------------------------------------------------
    // Place on Hold Modal Handlers (Stage 1: Pause with Grace Period)
    // --------------------------------------------------------------------------
    window.openHoldModal = function(optionalProjectId) {
        let id = optionalProjectId;
        let title = '';

        if (id) {
            const row = document.querySelector(`tr[data-id="${id}"]`);
            if (row) {
                if ((row.getAttribute('data-status') || '').toLowerCase() === 'rejected') {
                    AdminCore.toast(`Project ${id} is permanently rejected and cannot be placed on hold.`, 'warning');
                    return;
                }
                title = row.getAttribute('data-title') || id;
            }
        } else {
            id    = document.getElementById('pmDetailId')?.textContent || '';
            title = document.getElementById('pmDetailTitle')?.textContent || '';
        }

        const targetIdEl    = document.getElementById('pmHoldProjectId');
        const targetTitleEl = document.getElementById('pmHoldProjectTitle');
        const reasonInput   = document.getElementById('pmHoldReason');
        const graceSelect   = document.getElementById('pmHoldGracePeriod');

        if (targetIdEl)    targetIdEl.textContent = id;
        if (targetTitleEl) targetTitleEl.textContent = title;
        if (reasonInput)   reasonInput.value = '';
        if (graceSelect)   graceSelect.value = '7';

        AdminCore.modal.open(holdModal);
    };

    if (holdForm) {
        holdForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const id     = document.getElementById('pmHoldProjectId').textContent.trim();
            const reason = document.getElementById('pmHoldReason').value.trim();
            const days   = document.getElementById('pmHoldGracePeriod').value || '7';

            if (!reason) {
                AdminCore.toast('Please specify the issue or complaint reason before confirming hold.', 'warning');
                document.getElementById('pmHoldReason')?.focus();
                return;
            }

            const btnSubmit = holdForm.querySelector('button[type="submit"]');
            const origText = btnSubmit ? btnSubmit.innerHTML : '';
            if (btnSubmit) {
                btnSubmit.disabled = true;
                btnSubmit.innerHTML = 'Placing on Hold...';
            }

            const formData = new FormData();
            formData.append('ajax_action', 'place_on_hold');
            formData.append('project_id', id);
            formData.append('hold_reason', reason);
            formData.append('grace_period', days);

            fetch('Project_Management.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (btnSubmit) {
                    btnSubmit.disabled = false;
                    btnSubmit.innerHTML = origText;
                }
                if (data.success) {
                    AdminCore.modal.close(holdModal, holdForm);
                    AdminCore.toast(data.message || `Project ${id} placed on Hold for ${days} days.`);
                    setTimeout(() => window.location.reload(), 600);
                } else {
                    AdminCore.toast(data.message || 'Failed to place on hold.', 'error');
                }
            })
            .catch(err => {
                if (btnSubmit) {
                    btnSubmit.disabled = false;
                    btnSubmit.innerHTML = origText;
                }
                console.error(err);
                AdminCore.toast('An unexpected error occurred.', 'error');
            });
        });
    }

    // --------------------------------------------------------------------------
    // Review Hold & Resolution Modal Handlers (Stage 2)
    // --------------------------------------------------------------------------
    window.openReviewHoldModal = function(projectId) {
        const row = document.querySelector(`tr[data-id="${projectId}"]`);
        if (!row) return;

        if ((row.getAttribute('data-status') || '').toLowerCase() === 'rejected') {
            AdminCore.toast(`Action prohibited: Project ${projectId} is permanently rejected and cannot be reactivated.`, 'error');
            return;
        }

        const title        = row.getAttribute('data-title') || projectId;
        const holdReason   = row.getAttribute('data-hold-reason') || 'Complaint or workflow discrepancy reported.';
        const holdDays     = row.getAttribute('data-hold-days') || '5';
        const teamResponse = row.getAttribute('data-team-response') || 'We have revised the milestone deliverables and uploaded verified documentation.';

        const revId           = document.getElementById('pmReviewProjectId');
        const revTitle        = document.getElementById('pmReviewProjectTitle');
        const revAdminReason  = document.getElementById('pmReviewAdminReason');
        const revTeamResponse = document.getElementById('pmReviewTeamResponse');
        const revDaysBadge    = document.getElementById('pmReviewHoldDaysBadge');

        if (revId)           revId.textContent = projectId;
        if (revTitle)        revTitle.textContent = title;
        if (revAdminReason)  revAdminReason.textContent = holdReason;
        if (revTeamResponse) revTeamResponse.textContent = teamResponse;
        if (revDaysBadge)    revDaysBadge.textContent = `Grace Period: ${holdDays}d left`;

        const step2Id   = document.getElementById('pmReviewStep2ProjectId');
        const step2Title= document.getElementById('pmReviewStep2ProjectTitle');
        const reasonInput = document.getElementById('pmFinalRejectReason');
        if (step2Id) step2Id.textContent = projectId;
        if (step2Title) step2Title.textContent = title;
        if (reasonInput) {
            reasonInput.value = '';
            reasonInput.style.borderColor = '';
        }
        document.getElementById('pmFinalRejectError')?.style && (document.getElementById('pmFinalRejectError').style.display = 'none');

        window.goToReviewStep();
        AdminCore.modal.open(reviewHoldModal);
    };

    window.goToRejectStep = function() {
        const step1 = document.getElementById('pmReviewStep1');
        const step2 = document.getElementById('pmReviewStep2');
        const footer1 = document.getElementById('pmReviewFooter1');
        const footer2 = document.getElementById('pmReviewFooter2');
        const headerTitle = document.getElementById('pmReviewModalHeaderTitle');
        const headerSub = document.getElementById('pmReviewModalHeaderSub');
        const headerIcon = document.getElementById('pmReviewModalHeaderIcon');

        if (step1 && step2) {
            step1.style.display = 'none';
            if (footer1) footer1.style.display = 'none';
            step2.style.display = 'flex';
            if (footer2) footer2.style.display = 'flex';

            if (headerTitle) headerTitle.textContent = 'Step 2: Confirm Permanent Rejection';
            if (headerSub)   headerSub.textContent = 'Specify official reason for permanently terminating this project';
            if (headerIcon) {
                headerIcon.textContent = 'report_problem';
                headerIcon.style.color = '#dc2626';
            }
        }
    };

    window.goToReviewStep = function() {
        const step1 = document.getElementById('pmReviewStep1');
        const step2 = document.getElementById('pmReviewStep2');
        const footer1 = document.getElementById('pmReviewFooter1');
        const footer2 = document.getElementById('pmReviewFooter2');
        const headerTitle = document.getElementById('pmReviewModalHeaderTitle');
        const headerSub = document.getElementById('pmReviewModalHeaderSub');
        const headerIcon = document.getElementById('pmReviewModalHeaderIcon');

        if (step1 && step2) {
            step2.style.display = 'none';
            if (footer2) footer2.style.display = 'none';
            step1.style.display = 'flex';
            if (footer1) footer1.style.display = 'flex';

            if (headerTitle) headerTitle.textContent = 'Hold Review & Decision';
            if (headerSub)   headerSub.textContent = "Compare Admin pause reason against Team's submitted justification";
            if (headerIcon) {
                headerIcon.textContent = 'task_alt';
                headerIcon.style.color = '#0b2246';
            }
        }
    };

    window.confirmReactivateFromReview = function() {
        const id = document.getElementById('pmReviewProjectId')?.textContent.trim();
        if (!id) return;

        const formData = new FormData();
        formData.append('ajax_action', 'reactivate_project');
        formData.append('project_id', id);

        fetch('Project_Management.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                AdminCore.modal.close(reviewHoldModal);
                AdminCore.toast(data.message || `Project ${id} has been reactivated to Active status!`);
                setTimeout(() => window.location.reload(), 600);
            } else {
                AdminCore.toast(data.message || 'Failed to reactivate project.', 'error');
            }
        })
        .catch(err => {
            console.error(err);
            AdminCore.toast('An unexpected error occurred.', 'error');
        });
    };

    window.executePermanentRejection = function() {
        const id = (document.getElementById('pmReviewStep2ProjectId') || document.getElementById('pmReviewProjectId'))?.textContent.trim();
        const category = document.getElementById('pmFinalRejectCategory')?.value || 'General Grounds';
        const reason   = document.getElementById('pmFinalRejectReason')?.value.trim() || '';

        if (!reason) {
            document.getElementById('pmFinalRejectError')?.style && (document.getElementById('pmFinalRejectError').style.display = 'block');
            document.getElementById('pmFinalRejectReason')?.focus();
            AdminCore.toast('Please state the specific reason for permanently rejecting this project.', 'warning');
            return;
        }

        const formData = new FormData();
        formData.append('ajax_action', 'reject_project');
        formData.append('project_id', id);
        formData.append('category', category);
        formData.append('reason', reason);

        fetch('Project_Management.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                AdminCore.modal.close(reviewHoldModal);
                AdminCore.toast(data.message || `Project ${id} has been permanently rejected.`);
                setTimeout(() => window.location.reload(), 600);
            } else {
                AdminCore.toast(data.message || 'Failed to reject project.', 'error');
            }
        })
        .catch(err => {
            console.error(err);
            AdminCore.toast('An unexpected error occurred.', 'error');
        });
    };

    if (rejectForm) {
        rejectForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const id = document.getElementById('pmRejectProjectId')?.textContent.trim();
            const reason = document.getElementById('pmRejectReason')?.value.trim() || '';

            if (!reason) {
                AdminCore.toast('Please specify the rejection reason.', 'warning');
                return;
            }

            const formData = new FormData();
            formData.append('ajax_action', 'reject_project');
            formData.append('project_id', id);
            formData.append('reason', reason);

            fetch('Project_Management.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    AdminCore.modal.close(rejectModal, rejectForm);
                    AdminCore.toast(data.message || `Project ${id} has been permanently rejected.`);
                    setTimeout(() => window.location.reload(), 600);
                } else {
                    AdminCore.toast(data.message || 'Failed to reject project.', 'error');
                }
            })
            .catch(err => {
                console.error(err);
                AdminCore.toast('An unexpected error occurred.', 'error');
            });
        });
    }

    window.reactivateProject = function(projectId) {
        window.openReviewHoldModal(projectId);
    };

    // --------------------------------------------------------------------------
    // Initial Filter Run on Page Load
    // --------------------------------------------------------------------------
    applyFilters(true);
}
