

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
                if (selectedStatus === 'published' || selectedStatus === 'publish') {
                    matchesStatus = (projectStatus === 'published' || projectStatus === 'publish');
                } else if (selectedStatus === 'active') {
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
            colspan: 6
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
    window.closeHoldModal           = () => AdminCore.modal.close(holdModal, holdForm);
    window.closeReviewHoldModal     = () => AdminCore.modal.close(reviewHoldModal);
    window.closeRejectReasonModal   = () => AdminCore.modal.close(rejectModal, rejectForm);

    // --------------------------------------------------------------------------
    // --------------------------------------------------------------------------
    // Project Details Modal View
    // --------------------------------------------------------------------------
    window.viewProjectDetails = function(projectId) {
        if (!detailsModal) return;
        const row = document.querySelector(`tr[data-id="${projectId}"]`);
        if (!row) return;

        const title              = row.getAttribute('data-title') || projectId;
        const category           = row.getAttribute('data-category') || 'General';
        const org                = row.getAttribute('data-org') || 'N/A';
        const orgEmail           = row.getAttribute('data-org-email') || '';
        const status             = row.getAttribute('data-status') || 'Active';
        const skills             = row.getAttribute('data-skills') || 'General';
        const description        = row.getAttribute('data-description') || 'No description provided.';
        const learningObjectives = row.getAttribute('data-learning-objectives') || 'Standard industry project objectives.';
        const expectedOutcomes   = row.getAttribute('data-expected-outcomes') || 'Production ready deliverables.';
        const difficulty         = row.getAttribute('data-difficulty') || 'Intermediate';
        const duration           = row.getAttribute('data-duration') || '8 Weeks';
        const deadline           = row.getAttribute('data-deadline') || 'Not Specified';
        const members            = row.getAttribute('data-members') || '1';
        const holdReason         = row.getAttribute('data-hold-reason') || '';
        const holdDays           = row.getAttribute('data-hold-days') || '7';
        const teamResponse       = row.getAttribute('data-team-response') || '';
        const rejectReason       = row.getAttribute('data-reject-reason') || '';

        // Core text elements
        const idEl       = document.getElementById('pmDetailId');
        const titleEl    = document.getElementById('pmDetailTitle');
        const catEl      = document.getElementById('pmDetailCategory');
        const orgEl      = document.getElementById('pmDetailOrg');
        const orgEmailEl = document.getElementById('pmDetailOrgEmail');
        const diffEl     = document.getElementById('pmDetailDifficulty');
        const durEl      = document.getElementById('pmDetailDuration');
        const memEl      = document.getElementById('pmDetailMembers');
        const deadEl     = document.getElementById('pmDetailDeadline');
        const descEl     = document.getElementById('pmDetailDescription');
        const objEl      = document.getElementById('pmDetailObjectives');
        const outEl      = document.getElementById('pmDetailOutcomes');

        if (idEl)       idEl.textContent = projectId;
        if (titleEl)    titleEl.textContent = title;
        if (catEl)      catEl.textContent = category;
        if (orgEl)      orgEl.textContent = org;
        if (orgEmailEl) orgEmailEl.textContent = orgEmail;
        if (diffEl)     diffEl.textContent = difficulty;
        if (durEl)      durEl.textContent = duration;
        if (memEl)      memEl.textContent = members;
        if (deadEl)     deadEl.textContent = deadline;
        if (descEl)     descEl.textContent = description;
        if (objEl)      objEl.textContent = learningObjectives;
        if (outEl)      outEl.textContent = expectedOutcomes;

        // Render skill chips
        const skillsContainer = document.getElementById('pmDetailSkillsContainer');
        if (skillsContainer) {
            skillsContainer.innerHTML = '';
            const skillTokens = skills.split(',').map(s => s.trim()).filter(Boolean);
            if (skillTokens.length > 0) {
                skillTokens.forEach(sk => {
                    const span = document.createElement('span');
                    span.className = 'pm-skill-pill';
                    span.textContent = sk;
                    skillsContainer.appendChild(span);
                });
            } else {
                skillsContainer.innerHTML = '<span class="pm-skill-pill">General</span>';
            }
        }

        // Status Badge
        const statusEl = document.getElementById('pmDetailStatus');
        const sLower   = (status || '').toLowerCase();
        if (statusEl) {
            statusEl.className = 'pm-status-pill';
            if (sLower === 'published' || sLower === 'publish') {
                statusEl.innerHTML = '<span class="material-symbols-outlined" style="font-size: 13px; margin-right: 3px;">public</span>Published';
                statusEl.classList.add('status-published');
            } else if (sLower === 'active' || sLower === 'inprogress') {
                statusEl.innerHTML = '<span class="material-symbols-outlined" style="font-size: 13px; margin-right: 3px;">bolt</span>Active';
                statusEl.classList.add('status-active');
            } else if (sLower === 'hold') {
                statusEl.innerHTML = `<span class="material-symbols-outlined" style="font-size: 13px; margin-right: 3px;">pause_circle</span>Hold (${holdDays}d left)`;
                statusEl.classList.add('status-hold');
            } else if (sLower === 'completed' || sLower === 'close' || sLower === 'complete') {
                statusEl.innerHTML = '<span class="material-symbols-outlined" style="font-size: 13px; margin-right: 3px;">task_alt</span>Completed';
                statusEl.classList.add('status-completed');
            } else if (sLower === 'review' || sLower === 'reviewing' || sLower === 'rewiewing') {
                statusEl.innerHTML = '<span class="material-symbols-outlined" style="font-size: 13px; margin-right: 3px;">hourglass_top</span>Review';
                statusEl.classList.add('status-review');
            } else if (sLower === 'rejected') {
                statusEl.innerHTML = '<span class="material-symbols-outlined" style="font-size: 13px; margin-right: 3px;">block</span>Rejected';
                statusEl.classList.add('status-rejected');
            } else {
                statusEl.textContent = status;
                statusEl.classList.add('status-active');
            }
        }

        // Banners
        const reviewBox    = document.getElementById('pmDetailReviewBox');
        const holdBox      = document.getElementById('pmDetailHoldBox');
        const rejectedBox  = document.getElementById('pmDetailRejectedBox');
        const activeBox    = document.getElementById('pmDetailActiveBox');
        const publishedBox = document.getElementById('pmDetailPublishedBox');

        if (reviewBox)    reviewBox.style.display    = (sLower === 'review' || sLower === 'reviewing' || sLower === 'rewiewing') ? 'block' : 'none';
        if (publishedBox) publishedBox.style.display = (sLower === 'published' || sLower === 'publish') ? 'block' : 'none';
        if (activeBox)    activeBox.style.display    = (sLower === 'active' || sLower === 'inprogress') ? 'block' : 'none';
        
        if (holdBox) {
            holdBox.style.display = (sLower === 'hold') ? 'block' : 'none';
            if (sLower === 'hold') {
                const holdReasonEl   = document.getElementById('pmDetailHoldReason');
                const holdDeadlineEl = document.getElementById('pmDetailHoldDeadline');
                const holdRespBadge  = document.getElementById('pmDetailHoldRespBadge');
                if (holdReasonEl)   holdReasonEl.textContent = holdReason || 'Issue reported. Organization has been given a grace period to clarify.';
                if (holdDeadlineEl) holdDeadlineEl.textContent = `${holdDays} Days Left`;
                if (holdRespBadge) {
                    const holdRespWrap = document.getElementById('pmDetailHoldResponseWrap');
                    const holdRespText = document.getElementById('pmDetailHoldResponseText');
                    if (teamResponse && teamResponse.trim() !== '') {
                        holdRespBadge.style.background = '#dcfce7';
                        holdRespBadge.style.color = '#15803d';
                        holdRespBadge.textContent = 'Organization Justification Submitted';
                        if (holdRespWrap && holdRespText) {
                            holdRespWrap.style.display = 'block';
                            holdRespText.textContent = teamResponse;
                        }
                    } else {
                        holdRespBadge.style.background = '#fbbf24';
                        holdRespBadge.style.color = '#78350f';
                        holdRespBadge.textContent = 'Awaiting Organization Response';
                        if (holdRespWrap) {
                            holdRespWrap.style.display = 'none';
                        }
                    }
                }
            }
        }

        if (rejectedBox) {
            rejectedBox.style.display = (sLower === 'rejected') ? 'block' : 'none';
            if (sLower === 'rejected') {
                const rejectReasonEl = document.getElementById('pmDetailRejectReason');
                if (rejectReasonEl) rejectReasonEl.textContent = 'Reason: ' + (rejectReason || 'Violated submission criteria or failed grace period response.');
            }
        }

        // Moderation Controls Area
        const actionControls = document.getElementById('pmDetailActionControls');
        if (actionControls) {
            const escapedTitle = title.replace(/'/g, "\\'");
            if (sLower === 'review' || sLower === 'reviewing' || sLower === 'rewiewing') {
                actionControls.innerHTML = `
                    <div id="pmDetailNormalActions" style="display: flex; align-items: center; justify-content: space-between; gap: 14px; flex-wrap: wrap;">
                        <div>
                            <label style="font-size: 13.5px; font-weight: 700; color: #0f172a; display: block; margin-bottom: 2px;">Initial Moderation Decision</label>
                            <span style="font-size: 12px; color: #64748b;">Accept to publish this project to students, or place on hold if corrections are needed.</span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <button type="button" class="pm-btn-hold" onclick="openHoldModal('${projectId}', true)">
                                <span class="material-symbols-outlined" style="font-size: 18px;">pause_circle</span>
                                <span>Place on Hold</span>
                            </button>
                            <button type="button" class="pm-btn-accept" onclick="showInlinePublishConfirm('${projectId}')">
                                <span class="material-symbols-outlined" style="font-size: 18px;">check_circle</span>
                                <span>Accept & Publish</span>
                            </button>
                        </div>
                    </div>

                    <div id="pmDetailInlineConfirmBox" style="display: none; padding: 14px 16px; background: #f0fdf4; border: 1.5px solid #86efac; border-radius: 10px;">
                        <div style="display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap;">
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <span class="material-symbols-outlined" style="color: #16a34a; font-size: 26px;">public</span>
                                <div>
                                    <strong style="color: #14532d; font-size: 13.5px; display: block;">Publish this project to students now?</strong>
                                    <span style="font-size: 12px; color: #166534;">
                                        Status will become <strong>Published</strong>. Registered university students will be able to discover and apply immediately.
                                    </span>
                                </div>
                            </div>
                            <div style="display: flex; align-items: center; gap: 8px; flex-shrink: 0;">
                                <button type="button" class="pm-btn-secondary" style="padding: 7px 14px; font-size: 12.5px;" onclick="hideInlinePublishConfirm()">Cancel</button>
                                <button type="button" class="pm-btn-accept" id="pmBtnInlinePublish" style="padding: 7px 16px; font-size: 12.5px;" onclick="executeInlinePublish('${projectId}')">
                                    <span class="material-symbols-outlined" style="font-size: 17px;">check_circle</span>
                                    <span>Yes, Publish</span>
                                </button>
                            </div>
                        </div>
                    </div>
                `;
            } else if (sLower === 'published' || sLower === 'publish') {
                actionControls.innerHTML = `
                    <div style="display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap;">
                        <div>
                            <label style="font-size: 13px; font-weight: 700; color: #0369a1; display: block; margin-bottom: 2px;">Published Project Moderation</label>
                            <span style="font-size: 12px; color: #64748b;">This project is published and visible to students. Place on Hold if any ethical or compliance issue arises.</span>
                        </div>
                        <button type="button" class="pm-btn-hold" onclick="openHoldModal('${projectId}', true)">
                            <span class="material-symbols-outlined" style="font-size: 18px;">pause_circle</span>
                            <span>Place on Hold</span>
                        </button>
                    </div>
                `;
            } else if (sLower === 'active' || sLower === 'inprogress') {
                actionControls.innerHTML = `
                    <div style="display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap;">
                        <div>
                            <label style="font-size: 13px; font-weight: 700; color: #1e293b; display: block; margin-bottom: 2px;">Ethical Moderation Action</label>
                            <span style="font-size: 12px; color: #64748b;">Notice a violation or report? Place project on Hold to request the organization to clarify.</span>
                        </div>
                        <button type="button" class="pm-btn-hold" onclick="openHoldModal('${projectId}', true)">
                            <span class="material-symbols-outlined" style="font-size: 18px;">pause_circle</span>
                            <span>Place on Hold</span>
                        </button>
                    </div>
                `;
            } else if (sLower === 'hold') {
                actionControls.innerHTML = `
                    <div style="display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap;">
                        <div>
                            <label style="font-size: 13.5px; font-weight: 700; color: #0f172a; display: block; margin-bottom: 2px;">Hold Review & Decision</label>
                            <span style="font-size: 12px; color: #64748b;">Review organization's submitted justification against pause reason to decide Published or Reject.</span>
                        </div>
                        <button type="button" class="pm-btn-reactivate" onclick="openReviewHoldModal('${projectId}', true)">
                            <span class="material-symbols-outlined" style="font-size: 18px;">rate_review</span>
                            <span>Review Hold & Decide</span>
                        </button>
                    </div>
                `;
            } else if (sLower === 'rejected') {
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
    // Inline Confirmation Handlers inside Project Details Modal
    // (Accept & Publish directly within details modal - no nested popups)
    // --------------------------------------------------------------------------
    window.showInlinePublishConfirm = function(projectId) {
        const normalActions = document.getElementById('pmDetailNormalActions');
        const confirmBox    = document.getElementById('pmDetailInlineConfirmBox');
        if (normalActions && confirmBox) {
            normalActions.style.display = 'none';
            confirmBox.style.display = 'block';
        }
    };

    window.hideInlinePublishConfirm = function() {
        const normalActions = document.getElementById('pmDetailNormalActions');
        const confirmBox    = document.getElementById('pmDetailInlineConfirmBox');
        if (normalActions && confirmBox) {
            confirmBox.style.display = 'none';
            normalActions.style.display = 'flex';
        }
    };

    window.executeInlinePublish = function(projectId) {
        if (!projectId) return;
        const btn = document.getElementById('pmBtnInlinePublish');
        const origHtml = btn ? btn.innerHTML : '';
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<span class="material-symbols-outlined" style="font-size: 17px;">hourglass_top</span><span>Publishing...</span>';
        }

        const formData = new FormData();
        formData.append('ajax_action', 'accept_project');
        formData.append('project_id', projectId);

        fetch('Project_Management.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.text().then(text => {
            try {
                return JSON.parse(text);
            } catch (e) {
                console.error('Non-JSON response:', text);
                throw new Error('Invalid JSON response');
            }
        }))
        .then(data => {
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = origHtml;
            }
            if (data.success) {
                if (detailsModal) AdminCore.modal.close(detailsModal);
                AdminCore.toast(data.message || `Project ${projectId} accepted & published successfully!`, 'success');
                setTimeout(() => window.location.reload(), 600);
            } else {
                AdminCore.toast(data.message || 'Failed to publish project.', 'error');
            }
        })
        .catch(err => {
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = origHtml;
            }
            console.error(err);
            AdminCore.toast('An error occurred while approving the project.', 'error');
        });
    };

    // --------------------------------------------------------------------------
    // Place on Hold Modal Handlers (Stage 1: Pause with Grace Period)
    // --------------------------------------------------------------------------
    window.openHoldModal = function(optionalProjectId, fromDetails = false) {
        let id = optionalProjectId;
        let title = '';

        if (fromDetails && detailsModal) {
            AdminCore.modal.close(detailsModal);
        }

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
    window.openReviewHoldModal = function(projectId, fromDetails = false) {
        if (fromDetails && detailsModal) {
            AdminCore.modal.close(detailsModal);
        }

        const row = document.querySelector(`tr[data-id="${projectId}"]`);
        if (!row) return;

        if ((row.getAttribute('data-status') || '').toLowerCase() === 'rejected') {
            AdminCore.toast(`Action prohibited: Project ${projectId} is permanently rejected and cannot be reactivated.`, 'error');
            return;
        }

        const title        = row.getAttribute('data-title') || projectId;
        const holdReason   = (row.getAttribute('data-hold-reason') || '').trim();
        const holdDays     = row.getAttribute('data-hold-days') || '7';
        const rawResponse  = (row.getAttribute('data-team-response') || '').trim();
        const hasResponse  = (rawResponse !== '');

        const revId           = document.getElementById('pmReviewProjectId');
        const revTitle        = document.getElementById('pmReviewProjectTitle');
        const revAdminReason  = document.getElementById('pmReviewAdminReason');
        const revTeamResponse = document.getElementById('pmReviewTeamResponse');
        const revDaysBadge    = document.getElementById('pmReviewHoldDaysBadge');
        const revTeamStatus   = document.getElementById('pmReviewTeamStatusBadge');
        const teamCard        = document.getElementById('pmReviewTeamCard');
        const teamTitleSpan   = document.getElementById('pmReviewTeamTitleSpan');

        if (revId)          revId.textContent = projectId;
        if (revTitle)       revTitle.textContent = title;
        if (revAdminReason) revAdminReason.textContent = holdReason || 'Reason not specified.';
        if (revDaysBadge)   revDaysBadge.textContent = `Grace Period: ${holdDays}d left`;

        if (hasResponse) {
            if (teamCard) {
                teamCard.style.backgroundColor = '#f0fdf4';
                teamCard.style.borderColor = '#bbf7d0';
            }
            if (teamTitleSpan) {
                teamTitleSpan.style.color = '#166534';
                teamTitleSpan.innerHTML = '<span class="material-symbols-outlined" style="font-size: 18px;">forum</span>Organization Submitted Justification';
            }
            if (revTeamStatus) {
                revTeamStatus.style.background = '#dcfce7';
                revTeamStatus.style.color = '#15803d';
                revTeamStatus.textContent = 'Response Submitted';
            }
            if (revTeamResponse) {
                revTeamResponse.style.color = '#14532d';
                revTeamResponse.style.fontStyle = 'normal';
                revTeamResponse.textContent = rawResponse;
            }
        } else {
            if (teamCard) {
                teamCard.style.backgroundColor = '#f8fafc';
                teamCard.style.borderColor = '#e2e8f0';
            }
            if (teamTitleSpan) {
                teamTitleSpan.style.color = '#475569';
                teamTitleSpan.innerHTML = '<span class="material-symbols-outlined" style="font-size: 18px;">hourglass_empty</span>Organization Response Status';
            }
            if (revTeamStatus) {
                revTeamStatus.style.background = '#fef3c7';
                revTeamStatus.style.color = '#b45309';
                revTeamStatus.textContent = 'Awaiting Response';
            }
            if (revTeamResponse) {
                revTeamResponse.style.color = '#64748b';
                revTeamResponse.style.fontStyle = 'italic';
                revTeamResponse.textContent = 'The organization has not submitted a justification or response yet. They are currently within their grace period to submit explanations or update the project.';
            }
        }

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
                AdminCore.toast(data.message || `Project ${id} has been restored to Published status!`);
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
