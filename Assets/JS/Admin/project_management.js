

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
    const addProjectModal = document.getElementById('pmAddModal');
    const addProjectForm  = document.getElementById('pmAddProjectForm');
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
    AdminCore.modal.bind(addProjectModal, { trigger: 'pmBtnAddProject', form: addProjectForm });
    AdminCore.modal.bind(detailsModal);
    AdminCore.modal.bind(holdModal, { form: holdForm });
    AdminCore.modal.bind(reviewHoldModal);
    AdminCore.modal.bind(rejectModal, { form: rejectForm });

    window.openAddProjectModal   = () => AdminCore.modal.open(addProjectModal);
    window.closeAddProjectModal  = () => AdminCore.modal.close(addProjectModal, addProjectForm);
    window.closeProjectDetailsModal = () => AdminCore.modal.close(detailsModal);
    window.closeHoldModal        = () => AdminCore.modal.close(holdModal, holdForm);
    window.closeReviewHoldModal  = () => AdminCore.modal.close(reviewHoldModal);
    window.closeRejectReasonModal= () => AdminCore.modal.close(rejectModal, rejectForm);

    // --------------------------------------------------------------------------
    // Add Project Form Submission
    // --------------------------------------------------------------------------
    if (addProjectForm) {
        addProjectForm.addEventListener('submit', (e) => {
            e.preventDefault();

            const title     = document.getElementById('pmInputTitle').value.trim();
            const category  = document.getElementById('pmInputCategory').value.trim();
            const org       = document.getElementById('pmInputOrg').value.trim();
            const skillsStr = document.getElementById('pmInputSkills').value.trim();
            const status    = document.getElementById('pmInputStatus').value;

            if (!title || !org) {
                AdminCore.toast('Please fill in required fields (Title and Organization).', 'warning');
                return;
            }

            const newId = `#PR0${Math.floor(100 + Math.random() * 900)}`;
            const skills = skillsStr ? skillsStr.split(',').map(s => s.trim()).filter(Boolean) : ['General'];
            const skillsPills = skills.map(s => `<span class="pm-skill-pill">${AdminCore.escapeHtml(s)}</span>`).join('');

            const orgInitial = org.charAt(0).toUpperCase();
            let orgClass = 'org-default';
            if (org.toLowerCase().includes('ucsc')) orgClass = 'org-ucsc';
            else if (org.toLowerCase().includes('abc')) orgClass = 'org-abc';
            else if (org.toLowerCase().includes('tech')) orgClass = 'org-tech';
            else if (org.toLowerCase().includes('sliit')) orgClass = 'org-sliit';

            let statusPill = `<span class="pm-status-pill status-active">Active</span>`;
            if (status === 'Hold') {
                statusPill = `<span class="pm-status-pill status-hold"><span class="material-symbols-outlined" style="font-size: 13px; margin-right: 3px;">schedule</span>Hold (7d left)</span>`;
            } else if (status === 'Completed' || status === 'Close' || status === 'Complete') {
                statusPill = `<span class="pm-status-pill status-completed">Completed</span>`;
            } else if (status === 'Review') {
                statusPill = `<span class="pm-status-pill status-review">Review</span>`;
            } else if (status === 'Rejected') {
                statusPill = `<span class="pm-status-pill status-rejected">Rejected</span>`;
            }

            const today = new Date();
            const dateStr = today.toLocaleDateString('en-GB', { day: '2-digit', month: 'long', year: 'numeric' });

            const newRow = document.createElement('tr');
            newRow.className = 'pm-data-row';
            newRow.setAttribute('data-id', newId);
            newRow.setAttribute('data-title', title);
            newRow.setAttribute('data-category', category);
            newRow.setAttribute('data-org', org);
            newRow.setAttribute('data-status', status);
            newRow.setAttribute('data-skills', skills.join(' '));

            let actionsCellContent = `
                <button class="pm-btn-icon" title="View Details" onclick="viewProjectDetails('${newId}')">
                    <span class="material-symbols-outlined">visibility</span>
                </button>
                <button class="pm-btn-icon pm-btn-icon-hold" title="Place on Hold (Temporary Review)" onclick="openHoldModal('${newId}')">
                    <span class="material-symbols-outlined">pause_circle</span>
                </button>
            `;

            newRow.innerHTML = `
                <td class="pm-project-id">${newId}</td>
                <td>
                    <div class="pm-project-meta">
                        <span class="pm-title-main">${AdminCore.escapeHtml(title)}</span>
                        <span class="pm-title-category">${AdminCore.escapeHtml(category || 'Technology')}</span>
                    </div>
                </td>
                <td>
                    <div class="pm-org-wrapper">
                        <span class="pm-org-avatar ${orgClass}">${orgInitial}</span>
                        <span class="pm-org-name">${AdminCore.escapeHtml(org)}</span>
                    </div>
                </td>
                <td><div class="pm-skills-list">${skillsPills}</div></td>
                <td><div class="pm-team-count"><span class="material-symbols-outlined">group</span><span>4 Members</span></div></td>
                <td>${statusPill}</td>
                <td class="pm-created-date">${dateStr}</td>
                <td class="pm-actions-cell">${actionsCellContent}</td>
            `;

            if (tableBody) tableBody.insertBefore(newRow, tableBody.firstChild);

            AdminCore.modal.close(addProjectModal, addProjectForm);
            AdminCore.toast(`Project "${title}" added successfully!`);
            applyFilters(true);
        });
    }

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
            const id     = document.getElementById('pmHoldProjectId').textContent;
            const reason = document.getElementById('pmHoldReason').value.trim();
            const days   = document.getElementById('pmHoldGracePeriod').value || '7';

            if (!reason) {
                AdminCore.toast('Please specify the issue or complaint reason before confirming hold.', 'warning');
                document.getElementById('pmHoldReason')?.focus();
                return;
            }

            const row = document.querySelector(`tr[data-id="${id}"]`);
            if (row) {
                row.setAttribute('data-status', 'Hold');
                row.setAttribute('data-hold-reason', reason);
                row.setAttribute('data-hold-days', days);
                row.setAttribute('data-team-response', 'The team has been formally notified and the grace period timer is active. Awaiting team explanation/clarification.');

                const statusCell = row.cells[5];
                if (statusCell) {
                    statusCell.innerHTML = `
                        <span class="pm-status-pill status-hold" title="On Hold: ${days} days left in grace period">
                            <span class="material-symbols-outlined" style="font-size: 13px; margin-right: 3px;">schedule</span>Hold (${days}d left)
                        </span>
                    `;
                }

                const actionsCell = row.querySelector('.pm-actions-cell');
                if (actionsCell) {
                    actionsCell.innerHTML = `
                        <button class="pm-btn-icon" title="View Details" onclick="viewProjectDetails('${id}')">
                            <span class="material-symbols-outlined">visibility</span>
                        </button>
                        <button class="pm-btn-icon pm-btn-icon-reactivate" title="Review Hold & Decide Status" onclick="openReviewHoldModal('${id}')">
                            <span class="material-symbols-outlined">check_circle</span>
                        </button>
                    `;
                }

                if (detailsModal && detailsModal.classList.contains('show')) {
                    if (document.getElementById('pmDetailId')?.textContent === id) {
                        window.viewProjectDetails(id);
                    }
                }

                AdminCore.modal.close(holdModal, holdForm);
                AdminCore.toast(`Project ${id} placed on Hold for ${days} days. Grace period active.`);
                applyFilters(false);
            }
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
        const row = document.querySelector(`tr[data-id="${id}"]`);
        if (!row) return;

        row.setAttribute('data-status', 'Active');
        row.removeAttribute('data-hold-reason');
        row.removeAttribute('data-hold-days');
        row.removeAttribute('data-team-response');

        if (row.cells[5]) row.cells[5].innerHTML = `<span class="pm-status-pill status-active">Active</span>`;

        const actionsCell = row.querySelector('.pm-actions-cell');
        if (actionsCell) {
            actionsCell.innerHTML = `
                <button class="pm-btn-icon" title="View Details" onclick="viewProjectDetails('${id}')">
                    <span class="material-symbols-outlined">visibility</span>
                </button>
                <button class="pm-btn-icon pm-btn-icon-hold" title="Place on Hold (Temporary Review)" onclick="openHoldModal('${id}')">
                    <span class="material-symbols-outlined">pause_circle</span>
                </button>
            `;
        }

        if (detailsModal && detailsModal.classList.contains('show')) {
            if (document.getElementById('pmDetailId')?.textContent === id) {
                window.viewProjectDetails(id);
            }
        }

        AdminCore.modal.close(reviewHoldModal);
        AdminCore.toast(`Project ${id} has been reactivated to Active status!`);
        applyFilters(false);
    };

    window.executePermanentRejection = function() {
        const id = (document.getElementById('pmReviewStep2ProjectId') || document.getElementById('pmReviewProjectId'))?.textContent.trim();
        const row = document.querySelector(`tr[data-id="${id}"]`);
        if (!row) return;

        const category = document.getElementById('pmFinalRejectCategory')?.value || 'General Grounds';
        const reason   = document.getElementById('pmFinalRejectReason')?.value.trim() || '';

        if (!reason) {
            document.getElementById('pmFinalRejectError')?.style && (document.getElementById('pmFinalRejectError').style.display = 'block');
            document.getElementById('pmFinalRejectReason')?.focus();
            AdminCore.toast('Please state the specific reason for permanently rejecting this project.', 'warning');
            return;
        }

        const fullReason = `[${category}] ${reason}`;
        row.setAttribute('data-status', 'Rejected');
        row.setAttribute('data-reject-reason', fullReason);
        row.removeAttribute('data-hold-reason');
        row.removeAttribute('data-hold-days');
        row.removeAttribute('data-team-response');

        if (row.cells[5]) row.cells[5].innerHTML = `<span class="pm-status-pill status-rejected">Rejected</span>`;

        const actionsCell = row.querySelector('.pm-actions-cell');
        if (actionsCell) {
            actionsCell.innerHTML = `
                <button class="pm-btn-icon" title="View Details" onclick="viewProjectDetails('${id}')">
                    <span class="material-symbols-outlined">visibility</span>
                </button>
                <button class="pm-btn-icon" title="Permanently Rejected" disabled style="color: #cbd5e1; cursor: not-allowed;">
                    <span class="material-symbols-outlined">lock</span>
                </button>
            `;
        }

        if (detailsModal && detailsModal.classList.contains('show')) {
            if (document.getElementById('pmDetailId')?.textContent === id) {
                window.viewProjectDetails(id);
            }
        }

        AdminCore.modal.close(reviewHoldModal);
        AdminCore.toast(`Project ${id} has been permanently rejected.`);
        applyFilters(false);
    };

    window.reactivateProject = function(projectId) {
        window.openReviewHoldModal(projectId);
    };

    // --------------------------------------------------------------------------
    // Initial Filter Run on Page Load
    // --------------------------------------------------------------------------
    applyFilters(true);
}
