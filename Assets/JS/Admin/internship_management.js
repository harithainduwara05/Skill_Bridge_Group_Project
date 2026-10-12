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
    const addModal              = document.getElementById('imAddModal');
    const addForm               = document.getElementById('imAddInternshipForm');
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
    AdminCore.modal.bind(addModal, { trigger: 'imBtnAddInternship', form: addForm });
    AdminCore.modal.bind(detailsModal);
    AdminCore.modal.bind(suspendModal, { form: suspendForm });
    AdminCore.modal.bind(reviewSuspensionModal);

    window.openAddInternshipModal      = () => AdminCore.modal.open(addModal);
    window.closeAddInternshipModal     = () => AdminCore.modal.close(addModal, addForm);
    window.closeInternshipDetailsModal = () => AdminCore.modal.close(detailsModal);
    window.closeSuspendModal           = () => AdminCore.modal.close(suspendModal, suspendForm);
    window.closeReviewSuspensionModal  = () => AdminCore.modal.close(reviewSuspensionModal);

    // --------------------------------------------------------------------------
    // Add Internship Form Submission
    // --------------------------------------------------------------------------
    if (addForm) {
        addForm.addEventListener('submit', (e) => {
            e.preventDefault();

            const position  = document.getElementById('imInputPosition').value.trim();
            const company   = document.getElementById('imInputCompany').value.trim();
            const industry  = document.getElementById('imInputIndustry').value.trim();
            const mode      = document.getElementById('imInputMode').value.trim();
            const skillsStr = document.getElementById('imInputSkills').value.trim();
            const deadline  = document.getElementById('imInputDeadline').value.trim() || '30 Oct 2026';

            if (!position || !company) {
                AdminCore.toast('Please enter position title and company.', 'warning');
                return;
            }

            const newId = `#INT${Math.floor(100 + Math.random() * 900)}`;
            const skills = skillsStr ? skillsStr.split(',').map(s => s.trim()).filter(Boolean) : ['General'];
            const skillsStack = skills.map(sk => `<span class="im-skill-tag">${AdminCore.escapeHtml(sk)}</span>`).join('');

            let indClass = '';
            if (industry.toLowerCase().includes('ai')) indClass = 'ai';
            else if (industry.toLowerCase().includes('mobile')) indClass = 'mobile';

            const newRow = document.createElement('tr');
            newRow.className = 'im-data-row';
            newRow.setAttribute('data-id', newId);
            newRow.setAttribute('data-position', position);
            newRow.setAttribute('data-company', company);
            newRow.setAttribute('data-industry', industry);
            newRow.setAttribute('data-skills', skills.join(' '));
            newRow.setAttribute('data-status', 'Active');
            newRow.setAttribute('data-duration', mode);

            newRow.innerHTML = `
                <td class="im-internship-id">${newId}</td>
                <td>
                    <div class="im-position-meta">
                        <span class="im-position-title">${AdminCore.escapeHtml(position)}</span>
                        <span class="im-position-type">${AdminCore.escapeHtml(mode || 'Full-time • Remote')}</span>
                    </div>
                </td>
                <td class="im-company-name">${AdminCore.escapeHtml(company)}</td>
                <td><span class="im-industry-badge ${indClass}">${AdminCore.escapeHtml(industry || 'Technology')}</span></td>
                <td><div class="im-skills-stack">${skillsStack}</div></td>
                <td class="im-applications-count">0</td>
                <td class="im-deadline-date">${AdminCore.escapeHtml(deadline)}</td>
                <td class="im-status-cell">
                    <span class="im-status-badge status-active"><span class="im-status-dot"></span> Active</span>
                </td>
                <td class="im-actions-cell">
                    <button class="im-btn-action" title="View Details" onclick="viewInternshipDetails('${newId}')">
                        <span class="material-symbols-outlined">visibility</span>
                    </button>
                    <button class="im-btn-action im-btn-action-suspend" title="Suspend Post" onclick="openSuspendModal('${newId}')">
                        <span class="material-symbols-outlined">block</span>
                    </button>
                </td>
            `;

            if (tableBody) tableBody.insertBefore(newRow, tableBody.firstChild);

            AdminCore.modal.close(addModal, addForm);
            AdminCore.toast(`Internship "${position}" added successfully!`);
            applyFilters(true);
        });
    }

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

            const row = document.querySelector(`tr[data-id="${targetId}"]`);
            if (row) {
                const company = row.getAttribute('data-company') || 'Company';
                row.setAttribute('data-status', 'Suspended');
                row.setAttribute('data-reason', `${category}: ${reason}`);

                const statusCell = row.querySelector('.im-status-cell');
                if (statusCell) {
                    statusCell.innerHTML = `
                        <span class="im-status-badge status-suspended">
                            <span class="material-symbols-outlined" style="font-size: 13px;">pause_circle</span> Suspended
                        </span>
                    `;
                }

                const actionsCell = row.querySelector('.im-actions-cell');
                if (actionsCell) {
                    actionsCell.innerHTML = `
                        <button type="button" class="im-btn-action" title="View Details" onclick="viewInternshipDetails('${targetId}')">
                            <span class="material-symbols-outlined">visibility</span>
                        </button>
                        <button type="button" class="im-btn-action im-btn-action-reactivate" title="Review Suspension & Decide" onclick="openReviewSuspensionModal('${targetId}')">
                            <span class="material-symbols-outlined">check_circle</span>
                        </button>
                    `;
                }

                if (detailsModal && detailsModal.classList.contains('show')) {
                    window.viewInternshipDetails(targetId);
                }

                const kpiSuspended = document.getElementById('kpiSuspendedCount');
                if (kpiSuspended) {
                    const current = parseInt(kpiSuspended.textContent.replace(/,/g, ''), 10) || 0;
                    kpiSuspended.textContent = current + 1;
                }

                AdminCore.modal.close(suspendModal, suspendForm);
                AdminCore.toast(`Listing ${targetId} suspended! Notification dispatched to ${company}.`);
                applyFilters(false);
            }
        });
    }

    // --------------------------------------------------------------------------
    // Reactivate Internship
    // --------------------------------------------------------------------------
    window.reactivateInternship = function(internshipId) {
        const row = document.querySelector(`tr[data-id="${internshipId}"]`);
        if (!row) return;

        row.setAttribute('data-status', 'Active');
        row.setAttribute('data-reason', '');

        const statusCell = row.querySelector('.im-status-cell');
        if (statusCell) {
            statusCell.innerHTML = `<span class="im-status-badge status-active"><span class="im-status-dot"></span> Active</span>`;
        }

        const actionsCell = row.querySelector('.im-actions-cell');
        if (actionsCell) {
            actionsCell.innerHTML = `
                <button type="button" class="im-btn-action" title="View Details" onclick="viewInternshipDetails('${internshipId}')">
                    <span class="material-symbols-outlined">visibility</span>
                </button>
                <button type="button" class="im-btn-action im-btn-action-suspend" title="Suspend Post" onclick="openSuspendModal('${internshipId}')">
                    <span class="material-symbols-outlined">block</span>
                </button>
            `;
        }

        if (detailsModal && detailsModal.classList.contains('show')) {
            window.viewInternshipDetails(internshipId);
        }

        const kpiSuspended = document.getElementById('kpiSuspendedCount');
        if (kpiSuspended) {
            const current = parseInt(kpiSuspended.textContent.replace(/,/g, ''), 10) || 0;
            if (current > 0) kpiSuspended.textContent = current - 1;
        }

        AdminCore.toast(`Listing ${internshipId} reactivated! Visibility restored for students.`);
        applyFilters(false);
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

        const row = document.querySelector(`tr[data-id="${targetId}"]`);
        if (!row) return;

        row.setAttribute('data-status', 'Terminated');
        row.setAttribute('data-terminate-reason', 'Terminated by Admin due to unsatisfactory resolution.');

        const statusCell = row.querySelector('.im-status-cell');
        if (statusCell) {
            statusCell.innerHTML = `
                <span class="im-status-badge status-terminated">
                    <span class="material-symbols-outlined" style="font-size: 13px;">cancel</span> Terminated
                </span>
            `;
        }

        const actionsCell = row.querySelector('.im-actions-cell');
        if (actionsCell) {
            actionsCell.innerHTML = `
                <button type="button" class="im-btn-action" title="View Details" onclick="viewInternshipDetails('${targetId}')">
                    <span class="material-symbols-outlined">visibility</span>
                </button>
                <button type="button" class="im-btn-action" disabled title="Permanently Terminated" style="color: #cbd5e1; cursor: not-allowed;">
                    <span class="material-symbols-outlined">lock</span>
                </button>
            `;
        }

        if (detailsModal && detailsModal.classList.contains('show')) {
            window.viewInternshipDetails(targetId);
        }

        const kpiSuspended = document.getElementById('kpiSuspendedCount');
        if (kpiSuspended) {
            const current = parseInt(kpiSuspended.textContent.replace(/,/g, ''), 10) || 0;
            if (current > 0) kpiSuspended.textContent = current - 1;
        }

        AdminCore.modal.close(reviewSuspensionModal);
        AdminCore.toast(`Listing ${targetId} permanently terminated.`);
        applyFilters(false);
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
