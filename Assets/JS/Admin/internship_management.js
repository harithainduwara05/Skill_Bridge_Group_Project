document.addEventListener('DOMContentLoaded', () => {
    initInternshipManagement();
});

function initInternshipManagement() {
    // --------------------------------------------------------------------------
    // DOM Element References & Configuration
    // --------------------------------------------------------------------------
    const searchInput        = document.getElementById('imSearchInput');
    const statusFilter       = document.getElementById('imStatusFilter');
    const industryFilter     = document.getElementById('imIndustryFilter');
    const durationFilter     = document.getElementById('imDurationFilter');
    const clearFiltersBtn    = document.getElementById('imBtnClearFilters');
    const tableBody          = document.getElementById('imTableBody');
    const paginationInfo     = document.getElementById('imPaginationInfo');
    const paginationControls = document.getElementById('imPaginationControls') || document.querySelector('.im-pagination-controls');

    // Modals
    const detailsModal          = document.getElementById('imDetailsModal');
    const suspendModal          = document.getElementById('imSuspendModal');
    const suspendForm           = document.getElementById('imSuspendForm');
    const reviewSuspensionModal = document.getElementById('imReviewSuspensionModal');

    // Suspend Form Elements
    const suspendTargetId       = document.getElementById('imSuspendTargetId');
    const suspendTargetPosition = document.getElementById('imSuspendTargetPosition');
    const suspendTargetIdBadge  = document.getElementById('imSuspendTargetIdBadge');
    const suspendTargetCompany  = document.getElementById('imSuspendTargetCompany');
    const suspendCategory       = document.getElementById('imSuspendCategory');
    const suspendReason         = document.getElementById('imSuspendReason');
    const companyMessage        = document.getElementById('imCompanyMessage');

    // Review Modal Elements
    const revTargetId    = document.getElementById('imRevTargetId');
    const revId          = document.getElementById('imRevId');
    const revPosition    = document.getElementById('imRevPosition');
    const revCompany     = document.getElementById('imRevCompany');
    const revReasonText  = document.getElementById('imRevReasonText');
    const revActionCard  = document.getElementById('imRevActionCard');
    const revActionText  = document.getElementById('imRevActionText');
    const revNoActionCard= document.getElementById('imRevNoActionCard');

    // Pagination State
    const PAGE_SIZE = 6;
    let currentPage = 1;
    let matchedRows = [];

    // --------------------------------------------------------------------------
    // Filter & Search Engine
    // --------------------------------------------------------------------------
    function applyFilters(resetPage = true) {
        if (!tableBody) return;
        if (resetPage) currentPage = 1;

        const searchTerm       = searchInput ? searchInput.value.toLowerCase().trim() : '';
        const selectedStatus   = statusFilter ? statusFilter.value.toLowerCase() : 'all';
        const selectedIndustry = industryFilter ? industryFilter.value.toLowerCase() : 'all';
        const selectedDuration = durationFilter ? durationFilter.value.toLowerCase() : 'all';

        const rows = Array.from(tableBody.querySelectorAll('tr.im-data-row'));
        matchedRows = [];

        rows.forEach(row => {
            const id       = (row.getAttribute('data-id') || '').toLowerCase();
            const position = (row.getAttribute('data-position') || '').toLowerCase();
            const company  = (row.getAttribute('data-company') || '').toLowerCase();
            const industry = (row.getAttribute('data-industry') || '').toLowerCase();
            const skills   = (row.getAttribute('data-skills') || '').toLowerCase();
            const status   = (row.getAttribute('data-status') || '').toLowerCase();
            const duration = (row.getAttribute('data-duration') || '').toLowerCase();

            const matchesSearch = !searchTerm ||
                id.includes(searchTerm) ||
                position.includes(searchTerm) ||
                company.includes(searchTerm) ||
                skills.includes(searchTerm);

            const matchesStatus   = (selectedStatus === 'all') || (status === selectedStatus);
            const matchesIndustry = (selectedIndustry === 'all') || (industry.includes(selectedIndustry));
            const matchesDuration = (selectedDuration === 'all') || (duration.includes(selectedDuration));

            if (matchesSearch && matchesStatus && matchesIndustry && matchesDuration) {
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
            emptyIcon: 'work_off',
            emptyMessage: 'No matching internships found',
            emptySubtext: 'Try modifying your keyword search or adjusting active filters.',
            colspan: 9
        });
    }

    if (searchInput)     searchInput.addEventListener('input', () => applyFilters(true));
    if (statusFilter)    statusFilter.addEventListener('change', () => applyFilters(true));
    if (industryFilter)  industryFilter.addEventListener('change', () => applyFilters(true));
    if (durationFilter)  durationFilter.addEventListener('change', () => applyFilters(true));

    if (clearFiltersBtn) {
        clearFiltersBtn.addEventListener('click', () => {
            if (searchInput)    searchInput.value = '';
            if (statusFilter)   statusFilter.value = 'all';
            if (industryFilter) industryFilter.value = 'all';
            if (durationFilter) durationFilter.value = 'all';
            applyFilters(true);
            AdminCore.toast('Filters reset.');
        });
    }

    // --------------------------------------------------------------------------
    // Modal Bindings via AdminCore
    // --------------------------------------------------------------------------
    AdminCore.modal.bind(detailsModal);
    AdminCore.modal.bind(suspendModal, { form: suspendForm });
    AdminCore.modal.bind(reviewSuspensionModal);

    window.closeInternshipDetailsModal = () => AdminCore.modal.close(detailsModal);
    window.closeSuspendModal           = () => AdminCore.modal.close(suspendModal, suspendForm);
    window.closeReviewSuspensionModal  = () => AdminCore.modal.close(reviewSuspensionModal);

    // --------------------------------------------------------------------------
    // Suspend Internship Modal & Moderation Logic
    // --------------------------------------------------------------------------
    function generateCompanyMessage(company, position, id, reason) {
        const reasonText = reason ? reason.trim() : '[Violation of SkillBridge terms or student complaint received]';
        return `Dear ${company} Team,\n\nWe would like to notify you that your internship opportunity "${position}" (ID: ${id}) has been temporarily suspended by SkillBridge Administration.\n\nReason for Suspension:\n${reasonText}\n\nWhile suspended, this opportunity is deactivated and hidden from students.\n\nRegards,\nSkillBridge Compliance & Admin Team`;
    }

    if (suspendReason) {
        suspendReason.addEventListener('input', () => {
            const company  = suspendTargetCompany ? suspendTargetCompany.textContent.trim() : 'Employer';
            const position = suspendTargetPosition ? suspendTargetPosition.textContent.trim() : 'Internship';
            const id       = suspendTargetId ? suspendTargetId.value : '';
            if (companyMessage) {
                companyMessage.value = generateCompanyMessage(company, position, id, suspendReason.value);
            }
        });
    }

    window.openSuspendModal = function(internshipId) {
        if (!suspendModal) return;
        const row = document.querySelector(`tr[data-id="${internshipId}"]`);
        if (!row) return;

        const position = row.getAttribute('data-position') || 'Internship';
        const company  = row.getAttribute('data-company') || 'Company';

        if (suspendTargetId)       suspendTargetId.value = internshipId;
        if (suspendTargetPosition) suspendTargetPosition.textContent = position;
        if (suspendTargetIdBadge)  suspendTargetIdBadge.textContent = internshipId;
        if (suspendTargetCompany)  suspendTargetCompany.textContent = company;
        if (suspendCategory)       suspendCategory.selectedIndex = 0;
        if (suspendReason)         suspendReason.value = '';
        if (companyMessage)        companyMessage.value = generateCompanyMessage(company, position, internshipId, '');

        AdminCore.modal.open(suspendModal);
    };

    if (suspendForm) {
        suspendForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const targetId = suspendTargetId ? suspendTargetId.value : '';
            const category = suspendCategory ? suspendCategory.value : 'Policy Violation';
            const reason   = suspendReason ? suspendReason.value.trim() : '';

            if (!targetId || !reason) {
                AdminCore.toast('Please provide the suspension reason.', 'warning');
                return;
            }

            const btnSubmit = suspendForm.querySelector('button[type="submit"]');
            const origText = btnSubmit ? btnSubmit.innerHTML : '';
            if (btnSubmit) {
                btnSubmit.disabled = true;
                btnSubmit.innerHTML = 'Suspending...';
            }

            const formData = new FormData();
            formData.append('ajax_action', 'suspend_internship');
            formData.append('id', targetId);
            formData.append('category', category);
            formData.append('reason', reason);

            fetch('Internship_Management.php', {
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
                    AdminCore.modal.close(suspendModal, suspendForm);
                    AdminCore.toast(data.message || `Listing ${targetId} suspended!`);
                    setTimeout(() => window.location.reload(), 600);
                } else {
                    AdminCore.toast(data.message || 'Failed to suspend internship.', 'error');
                }
            })
            .catch(err => {
                if (btnSubmit) {
                    btnSubmit.disabled = false;
                    btnSubmit.innerHTML = origText;
                }
                AdminCore.toast('An unexpected error occurred.', 'error');
            });
        });
    }

    // --------------------------------------------------------------------------
    // Reactivate Internship
    // --------------------------------------------------------------------------
    window.reactivateInternship = function(internshipId) {
        const formData = new FormData();
        formData.append('ajax_action', 'reactivate_internship');
        formData.append('id', internshipId);

        fetch('Internship_Management.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                AdminCore.toast(data.message || `Listing ${internshipId} reactivated!`);
                setTimeout(() => window.location.reload(), 600);
            } else {
                AdminCore.toast(data.message || 'Failed to reactivate internship.', 'error');
            }
        })
        .catch(err => {
            AdminCore.toast('An unexpected error occurred.', 'error');
        });
    };

    // --------------------------------------------------------------------------
    // Review Suspended Internship & Decision Modal Logic
    // --------------------------------------------------------------------------
    window.openReviewSuspensionModal = function(internshipId) {
        if (!reviewSuspensionModal) return;
        const row = document.querySelector(`tr[data-id="${internshipId}"]`);
        if (!row) return;

        if ((row.getAttribute('data-status') || '').toLowerCase() === 'terminated') {
            AdminCore.toast(`Listing ${internshipId} is permanently terminated and cannot be reactivated.`, 'error');
            return;
        }

        const position      = row.getAttribute('data-position') || 'Internship';
        const company       = row.getAttribute('data-company') || 'Company';
        const reason        = row.getAttribute('data-reason') || 'Violation of terms or policy complaint reported.';
        const companyAction = (row.getAttribute('data-company-action') || '').trim();

        if (revTargetId)   revTargetId.value = internshipId;
        if (revId)         revId.textContent = internshipId;
        if (revPosition)   revPosition.textContent = position;
        if (revCompany)    revCompany.textContent = company;
        if (revReasonText) revReasonText.textContent = reason;

        if (companyAction) {
            if (revActionCard)   revActionCard.style.display = 'block';
            if (revActionText)   revActionText.textContent = companyAction;
            if (revNoActionCard) revNoActionCard.style.display = 'none';
        } else {
            if (revActionCard)   revActionCard.style.display = 'none';
            if (revNoActionCard) revNoActionCard.style.display = 'block';
        }

        AdminCore.modal.open(reviewSuspensionModal);
    };

    window.executeReactivationFromReview = function() {
        const targetId = revTargetId ? revTargetId.value : '';
        if (!targetId) return;
        window.reactivateInternship(targetId);
        AdminCore.modal.close(reviewSuspensionModal);
    };

    window.executeTerminationFromReview = function() {
        const targetId = revTargetId ? revTargetId.value : '';
        if (!targetId) return;

        if (!confirm(`Are you sure you want to permanently terminate listing ${targetId}? This action cannot be undone.`)) {
            return;
        }

        const btn = document.getElementById('imBtnExecuteTerminate');
        if (btn) btn.disabled = true;

        const formData = new FormData();
        formData.append('ajax_action', 'terminate_internship');
        formData.append('id', targetId);
        formData.append('reason', 'Terminated by Admin due to policy violation or unaddressed complaints.');

        fetch('Internship_Management.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (btn) btn.disabled = false;
            if (data.success) {
                AdminCore.modal.close(reviewSuspensionModal);
                AdminCore.toast(data.message || `Listing ${targetId} permanently terminated.`);
                setTimeout(() => window.location.reload(), 600);
            } else {
                AdminCore.toast(data.message || 'Failed to terminate internship.', 'error');
            }
        })
        .catch(err => {
            if (btn) btn.disabled = false;
            AdminCore.toast('An unexpected error occurred.', 'error');
        });
    };

    // --------------------------------------------------------------------------
    // Details Modal
    // --------------------------------------------------------------------------
    window.viewInternshipDetails = function(internshipId) {
        if (!detailsModal) return;

        const row = document.querySelector(`tr[data-id="${internshipId}"]`);
        if (!row) return;

        const position = row.getAttribute('data-position') || 'Internship';
        const company  = row.getAttribute('data-company') || 'Company';
        const industry = row.getAttribute('data-industry') || 'Technology';
        const skills   = row.getAttribute('data-skills') || 'General';
        const status   = (row.getAttribute('data-status') || 'Active').toLowerCase();
        const duration = row.getAttribute('data-duration') || 'Full-time';
        const reason   = row.getAttribute('data-reason') || '';
        const deadline = row.cells[6] ? row.cells[6].textContent.trim() : 'N/A';
        const apps     = row.cells[5] ? row.cells[5].textContent.trim() : '0';

        document.getElementById('imDetailId').textContent       = internshipId;
        document.getElementById('imDetailTitle').textContent    = position;
        document.getElementById('imDetailCompany').textContent  = company;
        document.getElementById('imDetailIndustry').textContent = industry;
        document.getElementById('imDetailSkills').textContent   = skills;
        document.getElementById('imDetailDuration').textContent = duration;
        document.getElementById('imDetailDeadline').textContent = deadline;
        document.getElementById('imDetailApps').textContent     = apps;

        const statusBadgeContainer       = document.getElementById('imDetailStatusBadge');
        const suspendedBox               = document.getElementById('imDetailSuspendedBox');
        const suspendedReasonText        = document.getElementById('imDetailSuspensionReason');
        const terminatedBox              = document.getElementById('imDetailTerminatedBox');
        const terminatedReasonText       = document.getElementById('imDetailTerminationReason');
        const moderationActionContainer  = document.getElementById('imModerationActionContainer');
        const terminateReason            = row.getAttribute('data-terminate-reason') || '';

        if (status === 'suspended') {
            if (statusBadgeContainer) statusBadgeContainer.innerHTML = `<span class="im-status-badge status-suspended"><span class="material-symbols-outlined" style="font-size: 13px;">pause_circle</span> Suspended</span>`;
            if (suspendedBox) {
                suspendedBox.style.display = 'flex';
                if (suspendedReasonText) suspendedReasonText.textContent = reason || 'Violation of terms or policy complaints reported.';
            }
            if (terminatedBox) terminatedBox.style.display = 'none';
            if (moderationActionContainer) {
                moderationActionContainer.innerHTML = `
                    <button type="button" class="im-btn-reactivate" onclick="closeInternshipDetailsModal(); openReviewSuspensionModal('${internshipId}')">
                        <span class="material-symbols-outlined" style="font-size: 18px;">rate_review</span>
                        <span>Review Suspension & Decide</span>
                    </button>
                `;
            }
        } else if (status === 'terminated') {
            if (statusBadgeContainer) statusBadgeContainer.innerHTML = `<span class="im-status-badge status-terminated"><span class="material-symbols-outlined" style="font-size: 13px;">cancel</span> Terminated</span>`;
            if (suspendedBox) suspendedBox.style.display = 'none';
            if (terminatedBox) {
                terminatedBox.style.display = 'flex';
                if (terminatedReasonText) terminatedReasonText.textContent = terminateReason || reason || 'Permanently terminated.';
            }
            if (moderationActionContainer) {
                moderationActionContainer.innerHTML = `
                    <div style="display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 12px 14px; background: #fef2f2; border-radius: 10px; border: 1px dashed #fca5a5; width: 100%;">
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <span class="material-symbols-outlined" style="color: #dc2626; font-size: 20px;">lock</span>
                            <span style="font-size: 12.5px; color: #991b1b; font-weight: 500;">Permanently Terminated State. This post cannot be reactivated or edited.</span>
                        </div>
                        <span style="font-size: 11px; font-weight: 700; color: #dc2626; background: #fee2e2; padding: 4px 8px; border-radius: 6px; white-space: nowrap;">TERMINAL STATE</span>
                    </div>
                `;
            }
        } else if (status === 'closed') {
            if (statusBadgeContainer) statusBadgeContainer.innerHTML = `<span class="im-status-badge status-closed"><span class="im-status-dot"></span> Closed</span>`;
            if (suspendedBox)  suspendedBox.style.display = 'none';
            if (terminatedBox) terminatedBox.style.display = 'none';
            if (moderationActionContainer) {
                moderationActionContainer.innerHTML = `<div style="font-size: 12.5px; color: #64748b; font-style: italic;">This internship listing has closed. No active moderation needed.</div>`;
            }
        } else {
            if (statusBadgeContainer) statusBadgeContainer.innerHTML = `<span class="im-status-badge status-active"><span class="im-status-dot"></span> Active</span>`;
            if (suspendedBox)  suspendedBox.style.display = 'none';
            if (terminatedBox) terminatedBox.style.display = 'none';
            if (moderationActionContainer) {
                moderationActionContainer.innerHTML = `
                    <button type="button" class="im-btn-suspend" onclick="openSuspendModal('${internshipId}')">
                        <span class="material-symbols-outlined" style="font-size: 18px;">block</span>
                        <span>Suspend Post & Notify Company</span>
                    </button>
                `;
            }
        }

        AdminCore.modal.open(detailsModal);
    };

    // --------------------------------------------------------------------------
    // Initial Page Load Filter Run
    // --------------------------------------------------------------------------
    applyFilters(true);
}
