/**
 * ==============================================================================
 * Project Management Module Logic - SkillBridge Admin Dashboard
 * Handles real-time search, multi-criteria filtering, modal management,
 * and the ethical 2-stage Hold & Irreversible Reject workflow.
 * ==============================================================================
 */

document.addEventListener('DOMContentLoaded', () => {
    initProjectManagement();
});

function initProjectManagement() {
    // --------------------------------------------------------------------------
    // DOM Element References
    // --------------------------------------------------------------------------
    const searchInput = document.getElementById('pmSearchInput');
    const statusFilter = document.getElementById('pmStatusFilter');
    const typeFilter = document.getElementById('pmTypeFilter');
    const orgFilter = document.getElementById('pmOrgFilter');
    const tableBody = document.getElementById('pmTableBody');
    const paginationInfo = document.getElementById('pmPaginationInfo');
    const addProjectBtn = document.getElementById('pmBtnAddProject');
    const addProjectModal = document.getElementById('pmAddModal');
    const addProjectForm = document.getElementById('pmAddProjectForm');
    const detailsModal = document.getElementById('pmDetailsModal');
    const holdModal = document.getElementById('pmHoldModal');
    const holdForm = document.getElementById('pmHoldProjectForm');
    const reviewHoldModal = document.getElementById('pmReviewHoldModal');
    const rejectModal = document.getElementById('pmRejectModal');
    const rejectForm = document.getElementById('pmRejectProjectForm');
    const toast = document.getElementById('pmToast');
    const toastMessage = document.getElementById('pmToastMessage');

    // --------------------------------------------------------------------------
    // Filter & Search Engine
    // --------------------------------------------------------------------------
    function applyFilters() {
        if (!tableBody) return;

        const searchTerm = searchInput ? searchInput.value.toLowerCase().trim() : '';
        const selectedStatus = statusFilter ? statusFilter.value.toLowerCase() : 'all';
        const selectedType = typeFilter ? typeFilter.value.toLowerCase() : 'all';
        const selectedOrg = orgFilter ? orgFilter.value.toLowerCase() : 'all';

        const rows = tableBody.querySelectorAll('tr.pm-data-row');
        let visibleCount = 0;

        rows.forEach(row => {
            const projectId = (row.getAttribute('data-id') || '').toLowerCase();
            const projectTitle = (row.getAttribute('data-title') || '').toLowerCase();
            const projectCategory = (row.getAttribute('data-category') || '').toLowerCase();
            const projectOrg = (row.getAttribute('data-org') || '').toLowerCase();
            const projectStatus = (row.getAttribute('data-status') || '').toLowerCase();
            const projectSkills = (row.getAttribute('data-skills') || '').toLowerCase();

            // Match search query
            const matchesSearch = !searchTerm ||
                projectId.includes(searchTerm) ||
                projectTitle.includes(searchTerm) ||
                projectCategory.includes(searchTerm) ||
                projectOrg.includes(searchTerm) ||
                projectSkills.includes(searchTerm);

            // Match status
            const matchesStatus = (selectedStatus === 'all') || (projectStatus === selectedStatus);

            // Match type/category
            const matchesType = (selectedType === 'all') || (projectCategory.includes(selectedType));

            // Match organization
            const matchesOrg = (selectedOrg === 'all') || (projectOrg.includes(selectedOrg));

            if (matchesSearch && matchesStatus && matchesType && matchesOrg) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        // Update pagination summary label
        if (paginationInfo) {
            paginationInfo.textContent = `Showing 1 to ${visibleCount} of ${rows.length} results`;
        }

        // Toggle empty state row if no results
        let emptyRow = document.getElementById('pmEmptyStateRow');
        if (visibleCount === 0) {
            if (!emptyRow) {
                emptyRow = document.createElement('tr');
                emptyRow.id = 'pmEmptyStateRow';
                emptyRow.innerHTML = `
                    <td colspan="8" style="text-align: center; padding: 48px 20px; color: #64748b;">
                        <span class="material-symbols-outlined" style="font-size: 40px; color: #94a3b8; display: block; margin-bottom: 8px;">folder_off</span>
                        <strong style="font-size: 15px; color: #1e293b; display: block; margin-bottom: 4px;">No matching projects found</strong>
                        <span style="font-size: 13px;">Try adjusting your search query or filter selections.</span>
                    </td>
                `;
                tableBody.appendChild(emptyRow);
            } else {
                emptyRow.style.display = '';
            }
        } else if (emptyRow) {
            emptyRow.style.display = 'none';
        }
    }

    // Attach search and filter event listeners
    if (searchInput) {
        searchInput.addEventListener('input', applyFilters);
    }
    if (statusFilter) {
        statusFilter.addEventListener('change', applyFilters);
    }
    if (typeFilter) {
        typeFilter.addEventListener('change', applyFilters);
    }
    if (orgFilter) {
        orgFilter.addEventListener('change', applyFilters);
    }

    // --------------------------------------------------------------------------
    // Add Project Modal Controls
    // --------------------------------------------------------------------------
    window.openAddProjectModal = function() {
        if (addProjectModal) {
            addProjectModal.classList.add('show');
            document.body.style.overflow = 'hidden';
        }
    };

    window.closeAddProjectModal = function() {
        if (addProjectModal) {
            addProjectModal.classList.remove('show');
            document.body.style.overflow = '';
            if (addProjectForm) addProjectForm.reset();
        }
    };

    if (addProjectBtn) {
        addProjectBtn.addEventListener('click', window.openAddProjectModal);
    }

    if (addProjectModal) {
        addProjectModal.addEventListener('click', (e) => {
            if (e.target === addProjectModal) {
                window.closeAddProjectModal();
            }
        });
    }

    // Handle Add Project Form Submission
    if (addProjectForm) {
        addProjectForm.addEventListener('submit', (e) => {
            e.preventDefault();

            const title = document.getElementById('pmInputTitle').value.trim();
            const category = document.getElementById('pmInputCategory').value.trim();
            const org = document.getElementById('pmInputOrg').value.trim();
            const skillsStr = document.getElementById('pmInputSkills').value.trim();
            const status = document.getElementById('pmInputStatus').value;

            if (!title || !org) {
                showToast('Please fill in required fields (Title and Organization).');
                return;
            }

            // Generate New Unique ID
            const newIdNumber = Math.floor(100 + Math.random() * 900);
            const newId = `#PR0${newIdNumber}`;

            // Parse skills
            const skills = skillsStr ? skillsStr.split(',').map(s => s.trim()).filter(Boolean) : ['General'];
            const skillsPills = skills.map(skill => `<span class="pm-skill-pill">${escapeHtml(skill)}</span>`).join('');

            // Organization initial and styling class
            const orgInitial = org.charAt(0).toUpperCase();
            let orgClass = 'org-default';
            if (org.toLowerCase().includes('ucsc')) orgClass = 'org-ucsc';
            else if (org.toLowerCase().includes('abc')) orgClass = 'org-abc';
            else if (org.toLowerCase().includes('tech')) orgClass = 'org-tech';
            else if (org.toLowerCase().includes('sliit')) orgClass = 'org-sliit';

            // Status Badge styling
            let statusPill = '';
            if (status === 'Active') {
                statusPill = `<span class="pm-status-pill status-active">Active</span>`;
            } else if (status === 'Hold') {
                statusPill = `<span class="pm-status-pill status-hold"><span class="material-symbols-outlined" style="font-size: 13px; margin-right: 3px;">schedule</span>Hold (7d left)</span>`;
            } else if (status === 'Close') {
                statusPill = `<span class="pm-status-pill status-close">Close</span>`;
            } else if (status === 'Review') {
                statusPill = `<span class="pm-status-pill status-review">Review</span>`;
            } else if (status === 'Rejected') {
                statusPill = `<span class="pm-status-pill status-rejected">Rejected</span>`;
            } else {
                statusPill = `<span class="pm-status-pill status-active">Active</span>`;
            }

            // Current formatted date
            const today = new Date();
            const dateStr = today.toLocaleDateString('en-GB', { day: '2-digit', month: 'long', year: 'numeric' });

            // Create new table row
            const newRow = document.createElement('tr');
            newRow.className = 'pm-data-row';
            newRow.setAttribute('data-id', newId);
            newRow.setAttribute('data-title', title);
            newRow.setAttribute('data-category', category);
            newRow.setAttribute('data-org', org);
            newRow.setAttribute('data-status', status);
            newRow.setAttribute('data-skills', skills.join(' '));

            let actionsCellContent = '';
            if (status === 'Active') {
                actionsCellContent = `
                    <button class="pm-btn-icon" title="View Details" onclick="viewProjectDetails('${newId}')">
                        <span class="material-symbols-outlined">visibility</span>
                    </button>
                    <button class="pm-btn-icon pm-btn-icon-hold" title="Place on Hold (Temporary Review)" onclick="openHoldModal('${newId}')">
                        <span class="material-symbols-outlined">pause_circle</span>
                    </button>
                `;
            } else if (status === 'Hold') {
                newRow.setAttribute('data-hold-days', '7');
                newRow.setAttribute('data-hold-reason', 'Initial hold for preliminary documentation review.');
                newRow.setAttribute('data-team-response', 'Team has been notified of the hold. Initial documentation submitted.');
                actionsCellContent = `
                    <button class="pm-btn-icon" title="View Details" onclick="viewProjectDetails('${newId}')">
                        <span class="material-symbols-outlined">visibility</span>
                    </button>
                    <button class="pm-btn-icon pm-btn-icon-reactivate" title="Review Hold & Decide Status" onclick="openReviewHoldModal('${newId}')">
                        <span class="material-symbols-outlined">check_circle</span>
                    </button>
                `;
            } else if (status === 'Rejected') {
                actionsCellContent = `
                    <button class="pm-btn-icon" title="View Details" onclick="viewProjectDetails('${newId}')">
                        <span class="material-symbols-outlined">visibility</span>
                    </button>
                    <button class="pm-btn-icon" title="Permanently Rejected (Cannot be reactivated)" disabled style="color: #cbd5e1; cursor: not-allowed;">
                        <span class="material-symbols-outlined">lock</span>
                    </button>
                `;
            } else {
                actionsCellContent = `
                    <button class="pm-btn-icon" title="View Details" onclick="viewProjectDetails('${newId}')">
                        <span class="material-symbols-outlined">visibility</span>
                    </button>
                `;
            }

            newRow.innerHTML = `
                <td class="pm-project-id">${newId}</td>
                <td>
                    <div class="pm-project-meta">
                        <span class="pm-title-main">${escapeHtml(title)}</span>
                        <span class="pm-title-category">${escapeHtml(category || 'Technology')}</span>
                    </div>
                </td>
                <td>
                    <div class="pm-org-wrapper">
                        <span class="pm-org-avatar ${orgClass}">${orgInitial}</span>
                        <span class="pm-org-name">${escapeHtml(org)}</span>
                    </div>
                </td>
                <td>
                    <div class="pm-skills-list">
                        ${skillsPills}
                    </div>
                </td>
                <td>
                    <div class="pm-team-count">
                        <span class="material-symbols-outlined">group</span>
                        <span>4 Members</span>
                    </div>
                </td>
                <td>
                    ${statusPill}
                </td>
                <td class="pm-created-date">${dateStr}</td>
                <td class="pm-actions-cell">
                    ${actionsCellContent}
                </td>
            `;

            // Prepend new row at top
            if (tableBody) {
                tableBody.insertBefore(newRow, tableBody.firstChild);
            }

            window.closeAddProjectModal();
            showToast(`Project "${title}" added successfully!`);
            applyFilters();
        });
    }

    // --------------------------------------------------------------------------
    // Project Details Modal View
    // --------------------------------------------------------------------------
    window.viewProjectDetails = function(projectId) {
        if (!detailsModal) return;

        const row = document.querySelector(`tr[data-id="${projectId}"]`);
        if (!row) return;

        const title = row.getAttribute('data-title') || projectId;
        const category = row.getAttribute('data-category') || 'General';
        const org = row.getAttribute('data-org') || 'N/A';
        const status = row.getAttribute('data-status') || 'Active';
        const skills = row.getAttribute('data-skills') || 'General';
        const holdReason = row.getAttribute('data-hold-reason') || '';
        const holdDays = row.getAttribute('data-hold-days') || '5';
        const rejectReason = row.getAttribute('data-reject-reason') || '';

        document.getElementById('pmDetailId').textContent = projectId;
        document.getElementById('pmDetailTitle').textContent = title;
        document.getElementById('pmDetailCategory').textContent = category;
        document.getElementById('pmDetailOrg').textContent = org;
        document.getElementById('pmDetailSkills').textContent = skills;

        const statusEl = document.getElementById('pmDetailStatus');
        if (statusEl) {
            statusEl.className = 'pm-status-pill';
            if (status === 'Active') {
                statusEl.textContent = 'Active';
                statusEl.classList.add('status-active');
            } else if (status === 'Hold') {
                statusEl.innerHTML = `<span class="material-symbols-outlined" style="font-size: 13px; margin-right: 3px;">schedule</span>Hold (${holdDays}d left)`;
                statusEl.classList.add('status-hold');
            } else if (status === 'Close') {
                statusEl.textContent = 'Close';
                statusEl.classList.add('status-close');
            } else if (status === 'Review') {
                statusEl.textContent = 'Review';
                statusEl.classList.add('status-review');
            } else if (status === 'Rejected') {
                statusEl.textContent = 'Rejected';
                statusEl.classList.add('status-rejected');
            }
        }

        // Handle Hold Status Banner
        const holdBox = document.getElementById('pmDetailHoldBox');
        if (holdBox) {
            if (status === 'Hold') {
                holdBox.style.display = 'block';
                const holdReasonEl = document.getElementById('pmDetailHoldReason');
                const holdDeadlineEl = document.getElementById('pmDetailHoldDeadline');
                if (holdReasonEl) holdReasonEl.textContent = holdReason || 'Complaint received or milestone discrepancy reported. Team must respond within grace period.';
                if (holdDeadlineEl) holdDeadlineEl.textContent = `${holdDays} Days Left`;
            } else {
                holdBox.style.display = 'none';
            }
        }

        // Handle Rejected Status Banner
        const rejectedBox = document.getElementById('pmDetailRejectedBox');
        if (rejectedBox) {
            if (status === 'Rejected') {
                rejectedBox.style.display = 'block';
                const rejectReasonEl = document.getElementById('pmDetailRejectReason');
                if (rejectReasonEl) rejectReasonEl.textContent = 'Reason: ' + (rejectReason || 'Failed grace period response or committed policy violation.');
            } else {
                rejectedBox.style.display = 'none';
            }
        }

        // Populate Moderation Controls dynamically
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
                            <span style="font-size: 12.5px; color: #991b1b; font-weight: 500;">This project is permanently terminated. Reactivation is strictly prohibited by system governance.</span>
                        </div>
                        <span style="font-size: 11px; font-weight: 700; color: #dc2626; background: #fee2e2; padding: 4px 8px; border-radius: 6px; white-space: nowrap;">TERMINAL STATE</span>
                    </div>
                `;
            } else if (status === 'Close') {
                actionControls.innerHTML = `
                    <div style="font-size: 12.5px; color: #64748b; font-style: italic;">
                        This project was closed by the owner or institution. No active moderation needed.
                    </div>
                `;
            }
        }

        detailsModal.classList.add('show');
        document.body.style.overflow = 'hidden';
    };

    window.closeProjectDetailsModal = function() {
        if (detailsModal) {
            detailsModal.classList.remove('show');
            document.body.style.overflow = '';
        }
    };

    if (detailsModal) {
        detailsModal.addEventListener('click', (e) => {
            if (e.target === detailsModal) {
                window.closeProjectDetailsModal();
            }
        });
    }

    // --------------------------------------------------------------------------
    // Place on Hold Modal Handlers (Stage 1: Pause with Grace Period)
    // --------------------------------------------------------------------------
    window.openHoldModal = function(optionalProjectId) {
        let id = '';
        let title = '';

        if (optionalProjectId) {
            const row = document.querySelector(`tr[data-id="${optionalProjectId}"]`);
            if (row) {
                const currentStatus = (row.getAttribute('data-status') || '').toLowerCase();
                if (currentStatus === 'rejected') {
                    showToast(`Project ${optionalProjectId} is permanently rejected and cannot be placed on hold.`);
                    return;
                }
                id = optionalProjectId;
                title = row.getAttribute('data-title') || optionalProjectId;
            }
        } else {
            id = document.getElementById('pmDetailId') ? document.getElementById('pmDetailId').textContent : '';
            title = document.getElementById('pmDetailTitle') ? document.getElementById('pmDetailTitle').textContent : '';
        }

        const targetIdEl = document.getElementById('pmHoldProjectId');
        const targetTitleEl = document.getElementById('pmHoldProjectTitle');
        const reasonInput = document.getElementById('pmHoldReason');
        const graceSelect = document.getElementById('pmHoldGracePeriod');

        if (targetIdEl) targetIdEl.textContent = id;
        if (targetTitleEl) targetTitleEl.textContent = title;
        if (reasonInput) reasonInput.value = '';
        if (graceSelect) graceSelect.value = '7';

        if (holdModal) {
            holdModal.classList.add('show');
            document.body.style.overflow = 'hidden';
        }
    };

    window.closeHoldModal = function() {
        if (holdModal) {
            holdModal.classList.remove('show');
            if (!detailsModal || !detailsModal.classList.contains('show')) {
                document.body.style.overflow = '';
            }
            const reasonInput = document.getElementById('pmHoldReason');
            if (reasonInput) reasonInput.value = '';
        }
    };

    if (holdModal) {
        holdModal.addEventListener('click', (e) => {
            if (e.target === holdModal) {
                window.closeHoldModal();
            }
        });
    }

    if (holdForm) {
        holdForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const id = document.getElementById('pmHoldProjectId').textContent;
            const reason = document.getElementById('pmHoldReason').value.trim();
            const days = document.getElementById('pmHoldGracePeriod').value || '7';

            if (!reason) {
                showToast('Please specify the issue or complaint reason before confirming hold.');
                const reasonInput = document.getElementById('pmHoldReason');
                if (reasonInput) reasonInput.focus();
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

                // Table row action: ONLY View Details + the Single Checkmark review icon!
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

                // If details modal is currently open, refresh it
                if (detailsModal && detailsModal.classList.contains('show')) {
                    const detailId = document.getElementById('pmDetailId');
                    if (detailId && detailId.textContent === id) {
                        window.viewProjectDetails(id);
                    }
                }

                window.closeHoldModal();
                showToast(`Project ${id} placed on Hold for ${days} days. Grace period notification sent.`);
                applyFilters();
            }
        });
    }

    // --------------------------------------------------------------------------
    // Review Hold & Resolution Modal Handlers (Multi-step: Review -> Final Reject)
    // --------------------------------------------------------------------------
    window.openReviewHoldModal = function(projectId) {
        const row = document.querySelector(`tr[data-id="${projectId}"]`);
        if (!row) return;

        const currentStatus = (row.getAttribute('data-status') || '').toLowerCase();
        if (currentStatus === 'rejected') {
            showToast(`Action prohibited: Project ${projectId} is permanently rejected and cannot be reactivated.`);
            return;
        }

        const title = row.getAttribute('data-title') || projectId;
        const holdReason = row.getAttribute('data-hold-reason') || 'Complaint or workflow discrepancy reported.';
        const holdDays = row.getAttribute('data-hold-days') || '5';
        const teamResponse = row.getAttribute('data-team-response') || 'We have revised the necessary milestone deliverables and uploaded all verified documentation to comply with platform criteria.';

        // Populate Step 1 Elements
        const revId = document.getElementById('pmReviewProjectId');
        const revTitle = document.getElementById('pmReviewProjectTitle');
        const revAdminReason = document.getElementById('pmReviewAdminReason');
        const revTeamResponse = document.getElementById('pmReviewTeamResponse');
        const revDaysBadge = document.getElementById('pmReviewHoldDaysBadge');

        if (revId) revId.textContent = projectId;
        if (revTitle) revTitle.textContent = title;
        if (revAdminReason) revAdminReason.textContent = holdReason;
        if (revTeamResponse) revTeamResponse.textContent = teamResponse;
        if (revDaysBadge) revDaysBadge.textContent = `Grace Period: ${holdDays}d left`;

        // Populate Step 2 Elements
        const step2Id = document.getElementById('pmReviewStep2ProjectId');
        const step2Title = document.getElementById('pmReviewStep2ProjectTitle');
        const finalRejectReason = document.getElementById('pmFinalRejectReason');
        const finalRejectError = document.getElementById('pmFinalRejectError');

        if (step2Id) step2Id.textContent = projectId;
        if (step2Title) step2Title.textContent = title;
        if (finalRejectReason) {
            finalRejectReason.value = '';
            finalRejectReason.style.borderColor = '';
        }
        if (finalRejectError) finalRejectError.style.display = 'none';

        // Always start at Step 1 (Review Comparison)
        window.goToReviewStep();

        if (reviewHoldModal) {
            reviewHoldModal.classList.add('show');
            document.body.style.overflow = 'hidden';
        }
    };

    window.closeReviewHoldModal = function() {
        if (reviewHoldModal) {
            reviewHoldModal.classList.remove('show');
            if (!detailsModal || !detailsModal.classList.contains('show')) {
                document.body.style.overflow = '';
            }
        }
    };

    if (reviewHoldModal) {
        reviewHoldModal.addEventListener('click', (e) => {
            if (e.target === reviewHoldModal) {
                window.closeReviewHoldModal();
            }
        });
    }

    // Step Transition: Go from Review (Step 1) to Final Reject Reason (Step 2)
    window.goToRejectStep = function() {
        const step1 = document.getElementById('pmReviewStep1');
        const step2 = document.getElementById('pmReviewStep2');
        const footer1 = document.getElementById('pmReviewFooter1');
        const footer2 = document.getElementById('pmReviewFooter2');
        const headerTitle = document.getElementById('pmReviewModalHeaderTitle');
        const headerSub = document.getElementById('pmReviewModalHeaderSub');
        const headerIcon = document.getElementById('pmReviewModalHeaderIcon');

        if (step1 && step2 && footer1 && footer2) {
            step1.style.display = 'none';
            footer1.style.display = 'none';
            step2.style.display = 'flex';
            footer2.style.display = 'flex';

            if (headerTitle) headerTitle.textContent = 'Step 2: Confirm Permanent Rejection';
            if (headerSub) headerSub.textContent = 'Specify the official reason for permanently terminating this project';
            if (headerIcon) {
                headerIcon.textContent = 'report_problem';
                headerIcon.style.color = '#dc2626';
            }
        }
    };

    // Step Transition: Go back from Final Reject Reason (Step 2) to Review (Step 1)
    window.goToReviewStep = function() {
        const step1 = document.getElementById('pmReviewStep1');
        const step2 = document.getElementById('pmReviewStep2');
        const footer1 = document.getElementById('pmReviewFooter1');
        const footer2 = document.getElementById('pmReviewFooter2');
        const headerTitle = document.getElementById('pmReviewModalHeaderTitle');
        const headerSub = document.getElementById('pmReviewModalHeaderSub');
        const headerIcon = document.getElementById('pmReviewModalHeaderIcon');

        if (step1 && step2 && footer1 && footer2) {
            step2.style.display = 'none';
            footer2.style.display = 'none';
            step1.style.display = 'flex';
            footer1.style.display = 'flex';

            if (headerTitle) headerTitle.textContent = 'Hold Review & Decision';
            if (headerSub) headerSub.textContent = "Compare Admin pause reason against Team's submitted justification";
            if (headerIcon) {
                headerIcon.textContent = 'task_alt';
                headerIcon.style.color = '#0b2246';
            }
        }
    };

    // Decision Option 1: Reactivate project to Active from Step 1
    window.confirmReactivateFromReview = function() {
        const revId = document.getElementById('pmReviewProjectId');
        if (!revId) return;
        const id = revId.textContent.trim();

        const row = document.querySelector(`tr[data-id="${id}"]`);
        if (!row) return;

        const currentStatus = (row.getAttribute('data-status') || '').toLowerCase();
        if (currentStatus === 'rejected') {
            showToast("Policy Restriction: Permanently rejected projects cannot be reactivated under any circumstance.");
            return;
        }

        // Update row to Active
        row.setAttribute('data-status', 'Active');
        row.removeAttribute('data-hold-reason');
        row.removeAttribute('data-hold-days');
        row.removeAttribute('data-team-response');

        const statusCell = row.cells[5];
        if (statusCell) {
            statusCell.innerHTML = `<span class="pm-status-pill status-active">Active</span>`;
        }

        // Update actions back to: View Details + Hold icon
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

        // Update details modal if open
        if (detailsModal && detailsModal.classList.contains('show')) {
            const detailId = document.getElementById('pmDetailId');
            if (detailId && detailId.textContent === id) {
                window.viewProjectDetails(id);
            }
        }

        window.closeReviewHoldModal();
        showToast(`Project ${id} has been reactivated and restored to Active status!`);
        applyFilters();
    };

    // Decision Option 2: Execute Permanent Rejection from Step 2
    window.executePermanentRejection = function() {
        const step2Id = document.getElementById('pmReviewStep2ProjectId') || document.getElementById('pmReviewProjectId');
        if (!step2Id) return;
        const id = step2Id.textContent.trim();

        const row = document.querySelector(`tr[data-id="${id}"]`);
        if (!row) return;

        const categorySelect = document.getElementById('pmFinalRejectCategory');
        const reasonInput = document.getElementById('pmFinalRejectReason');
        const errorEl = document.getElementById('pmFinalRejectError');

        const category = categorySelect ? categorySelect.value : 'General Grounds';
        const reason = reasonInput ? reasonInput.value.trim() : '';

        // Validation without native JS alert
        if (!reason) {
            if (errorEl) errorEl.style.display = 'block';
            if (reasonInput) {
                reasonInput.style.borderColor = '#dc2626';
                reasonInput.focus();
            }
            showToast('Please state the specific reason for permanently rejecting this project.');
            return;
        }

        const fullRejectionReason = `[${category}] ${reason}`;

        // Set to Terminal Rejected state
        row.setAttribute('data-status', 'Rejected');
        row.setAttribute('data-reject-reason', fullRejectionReason);
        row.removeAttribute('data-hold-reason');
        row.removeAttribute('data-hold-days');
        row.removeAttribute('data-team-response');

        const statusCell = row.cells[5];
        if (statusCell) {
            statusCell.innerHTML = `<span class="pm-status-pill status-rejected">Rejected</span>`;
        }

        // Table row action: View Details + locked icon (Cannot be reactivated!)
        const actionsCell = row.querySelector('.pm-actions-cell');
        if (actionsCell) {
            actionsCell.innerHTML = `
                <button class="pm-btn-icon" title="View Details" onclick="viewProjectDetails('${id}')">
                    <span class="material-symbols-outlined">visibility</span>
                </button>
                <button class="pm-btn-icon" title="Permanently Rejected (Cannot be reactivated)" disabled style="color: #cbd5e1; cursor: not-allowed;">
                    <span class="material-symbols-outlined">lock</span>
                </button>
            `;
        }

        // Update details modal if open
        if (detailsModal && detailsModal.classList.contains('show')) {
            const detailId = document.getElementById('pmDetailId');
            if (detailId && detailId.textContent === id) {
                window.viewProjectDetails(id);
            }
        }

        window.closeReviewHoldModal();
        showToast(`Project ${id} has been permanently rejected. It cannot be reactivated.`);
        applyFilters();
    };

    // --------------------------------------------------------------------------
    // Standalone Reactivate Function Guard
    // --------------------------------------------------------------------------
    window.reactivateProject = function(projectId) {
        const row = document.querySelector(`tr[data-id="${projectId}"]`);
        if (!row) return;

        const currentStatus = (row.getAttribute('data-status') || '').toLowerCase();
        if (currentStatus === 'rejected') {
            showToast("Policy Restriction: Permanently rejected projects cannot be reactivated.");
            return;
        }

        // Open the review modal so admin compares reasons
        window.openReviewHoldModal(projectId);
    };

    // --------------------------------------------------------------------------
    // Standalone Permanent Reject Modal Handlers (if opened directly)
    // --------------------------------------------------------------------------
    window.openRejectReasonModal = function(optionalProjectId) {
        let id = '';
        let title = '';

        if (optionalProjectId) {
            const row = document.querySelector(`tr[data-id="${optionalProjectId}"]`);
            if (row) {
                const currentStatus = (row.getAttribute('data-status') || '').toLowerCase();
                if (currentStatus === 'rejected') {
                    showToast(`Project ${optionalProjectId} is already permanently rejected.`);
                    return;
                }
                id = optionalProjectId;
                title = row.getAttribute('data-title') || optionalProjectId;
            }
        } else {
            id = document.getElementById('pmDetailId') ? document.getElementById('pmDetailId').textContent : '';
            title = document.getElementById('pmDetailTitle') ? document.getElementById('pmDetailTitle').textContent : '';
        }

        const targetIdEl = document.getElementById('pmRejectProjectId');
        const targetTitleEl = document.getElementById('pmRejectProjectTitle');
        const reasonInput = document.getElementById('pmRejectReason');

        if (targetIdEl) targetIdEl.textContent = id;
        if (targetTitleEl) targetTitleEl.textContent = title;
        if (reasonInput) reasonInput.value = '';

        if (rejectModal) {
            rejectModal.classList.add('show');
            document.body.style.overflow = 'hidden';
        }
    };

    window.closeRejectReasonModal = function() {
        if (rejectModal) {
            rejectModal.classList.remove('show');
            if (!detailsModal || !detailsModal.classList.contains('show')) {
                document.body.style.overflow = '';
            }
            const reasonInput = document.getElementById('pmRejectReason');
            if (reasonInput) reasonInput.value = '';
        }
    };

    if (rejectModal) {
        rejectModal.addEventListener('click', (e) => {
            if (e.target === rejectModal) {
                window.closeRejectReasonModal();
            }
        });
    }

    if (rejectForm) {
        rejectForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const id = document.getElementById('pmRejectProjectId').textContent;
            const reason = document.getElementById('pmRejectReason').value.trim();

            if (!reason) {
                showToast('Please state the final rejection reason.');
                const reasonInput = document.getElementById('pmRejectReason');
                if (reasonInput) reasonInput.focus();
                return;
            }

            const row = document.querySelector(`tr[data-id="${id}"]`);
            if (row) {
                row.setAttribute('data-status', 'Rejected');
                row.setAttribute('data-reject-reason', reason);
                row.removeAttribute('data-hold-reason');
                row.removeAttribute('data-hold-days');

                const statusCell = row.cells[5];
                if (statusCell) {
                    statusCell.innerHTML = `<span class="pm-status-pill status-rejected">Rejected</span>`;
                }

                const actionsCell = row.querySelector('.pm-actions-cell');
                if (actionsCell) {
                    actionsCell.innerHTML = `
                        <button class="pm-btn-icon" title="View Details" onclick="viewProjectDetails('${id}')">
                            <span class="material-symbols-outlined">visibility</span>
                        </button>
                        <button class="pm-btn-icon" title="Permanently Rejected (Cannot be reactivated)" disabled style="color: #cbd5e1; cursor: not-allowed;">
                            <span class="material-symbols-outlined">lock</span>
                        </button>
                    `;
                }

                if (detailsModal && detailsModal.classList.contains('show')) {
                    const detailId = document.getElementById('pmDetailId');
                    if (detailId && detailId.textContent === id) {
                        window.viewProjectDetails(id);
                    }
                }

                window.closeRejectReasonModal();
                showToast(`Project ${id} has been permanently rejected. It cannot be reactivated.`);
                applyFilters();
            }
        });
    }

    // --------------------------------------------------------------------------
    // Toast Notification Dispatcher
    // --------------------------------------------------------------------------
    function showToast(message) {
        if (!toast || !toastMessage) return;
        toastMessage.textContent = message;
        toast.classList.add('show');

        setTimeout(() => {
            toast.classList.remove('show');
        }, 3200);
    }

    // --------------------------------------------------------------------------
    // Pagination Interaction
    // --------------------------------------------------------------------------
    const pageButtons = document.querySelectorAll('.pm-page-number');
    pageButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            pageButtons.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            showToast(`Navigated to page ${btn.textContent.trim()}`);
        });
    });

    // Utility string escaper
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
}
