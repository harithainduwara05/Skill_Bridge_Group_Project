/**
 * ==============================================================================
 * Internship Management Module Logic - SkillBridge Admin Dashboard
 * Real-time search, multi-filter engine, interactive tag badges, modals,
 * and chart animations.
 * ==============================================================================
 */

document.addEventListener('DOMContentLoaded', () => {
    initInternshipManagement();
});

function initInternshipManagement() {
    // --------------------------------------------------------------------------
    // DOM Element References
    // --------------------------------------------------------------------------
    const searchInput = document.getElementById('imSearchInput');
    const statusFilter = document.getElementById('imStatusFilter');
    const industryFilter = document.getElementById('imIndustryFilter');
    const durationFilter = document.getElementById('imDurationFilter');
    const clearFiltersBtn = document.getElementById('imBtnClearFilters');
    const tableBody = document.getElementById('imTableBody');
    const paginationInfo = document.getElementById('imPaginationInfo');
    const addInternshipBtn = document.getElementById('imBtnAddInternship');
    const addModal = document.getElementById('imAddModal');
    const addForm = document.getElementById('imAddInternshipForm');
    const detailsModal = document.getElementById('imDetailsModal');
    const toast = document.getElementById('imToast');
    const toastMessage = document.getElementById('imToastMessage');

    // --------------------------------------------------------------------------
    // Search & Filter System
    // --------------------------------------------------------------------------
    function applyFilters() {
        if (!tableBody) return;

        const searchTerm = searchInput ? searchInput.value.toLowerCase().trim() : '';
        const selectedStatus = statusFilter ? statusFilter.value.toLowerCase() : 'all';
        const selectedIndustry = industryFilter ? industryFilter.value.toLowerCase() : 'all';
        const selectedDuration = durationFilter ? durationFilter.value.toLowerCase() : 'all';

        const rows = tableBody.querySelectorAll('tr.im-data-row');
        let visibleCount = 0;

        rows.forEach(row => {
            const id = (row.getAttribute('data-id') || '').toLowerCase();
            const position = (row.getAttribute('data-position') || '').toLowerCase();
            const company = (row.getAttribute('data-company') || '').toLowerCase();
            const industry = (row.getAttribute('data-industry') || '').toLowerCase();
            const skills = (row.getAttribute('data-skills') || '').toLowerCase();
            const status = (row.getAttribute('data-status') || '').toLowerCase();
            const duration = (row.getAttribute('data-duration') || '').toLowerCase();

            // Match search input
            const matchesSearch = !searchTerm ||
                id.includes(searchTerm) ||
                position.includes(searchTerm) ||
                company.includes(searchTerm) ||
                skills.includes(searchTerm);

            // Match status
            const matchesStatus = (selectedStatus === 'all') || (status === selectedStatus);

            // Match industry
            const matchesIndustry = (selectedIndustry === 'all') || (industry.includes(selectedIndustry));

            // Match duration
            const matchesDuration = (selectedDuration === 'all') || (duration.includes(selectedDuration));

            if (matchesSearch && matchesStatus && matchesIndustry && matchesDuration) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        // Update pagination counter text
        if (paginationInfo) {
            paginationInfo.textContent = `Showing 1 to ${visibleCount} of ${rows.length} entries`;
        }

        // Empty state row
        let emptyRow = document.getElementById('imEmptyStateRow');
        if (visibleCount === 0) {
            if (!emptyRow) {
                emptyRow = document.createElement('tr');
                emptyRow.id = 'imEmptyStateRow';
                emptyRow.innerHTML = `
                    <td colspan="9" style="text-align: center; padding: 48px 20px; color: #64748b;">
                        <span class="material-symbols-outlined" style="font-size: 40px; color: #94a3b8; display: block; margin-bottom: 8px;">work_off</span>
                        <strong style="font-size: 15px; color: #1e293b; display: block; margin-bottom: 4px;">No matching internships found</strong>
                        <span style="font-size: 13px;">Try modifying your keyword search or adjusting active filters.</span>
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

    // Attach Filter Listeners
    if (searchInput) searchInput.addEventListener('input', applyFilters);
    if (statusFilter) statusFilter.addEventListener('change', applyFilters);
    if (industryFilter) industryFilter.addEventListener('change', applyFilters);
    if (durationFilter) durationFilter.addEventListener('change', applyFilters);

    if (clearFiltersBtn) {
        clearFiltersBtn.addEventListener('click', () => {
            if (searchInput) searchInput.value = '';
            if (statusFilter) statusFilter.value = 'all';
            if (industryFilter) industryFilter.value = 'all';
            if (durationFilter) durationFilter.value = 'all';
            applyFilters();
            showToast('All filters have been cleared.');
        });
    }

    // --------------------------------------------------------------------------
    // Add Internship Modal
    // --------------------------------------------------------------------------
    window.openAddInternshipModal = function() {
        if (addModal) {
            addModal.classList.add('show');
            document.body.style.overflow = 'hidden';
        }
    };

    window.closeAddInternshipModal = function() {
        if (addModal) {
            addModal.classList.remove('show');
            document.body.style.overflow = '';
            if (addForm) addForm.reset();
        }
    };

    if (addInternshipBtn) {
        addInternshipBtn.addEventListener('click', window.openAddInternshipModal);
    }

    if (addModal) {
        addModal.addEventListener('click', (e) => {
            if (e.target === addModal) {
                window.closeAddInternshipModal();
            }
        });
    }

    // Form submission
    if (addForm) {
        addForm.addEventListener('submit', (e) => {
            e.preventDefault();

            const position = document.getElementById('imInputPosition').value.trim();
            const company = document.getElementById('imInputCompany').value.trim();
            const industry = document.getElementById('imInputIndustry').value.trim();
            const mode = document.getElementById('imInputMode').value.trim();
            const skillsStr = document.getElementById('imInputSkills').value.trim();
            const deadline = document.getElementById('imInputDeadline').value.trim() || '30 Oct 2026';

            if (!position || !company) {
                alert('Please enter position title and company.');
                return;
            }

            const newIdNumber = Math.floor(100 + Math.random() * 900);
            const newId = `#INT${newIdNumber}`;

            const skills = skillsStr ? skillsStr.split(',').map(s => s.trim()).filter(Boolean) : ['General'];
            const skillsStack = skills.map(sk => `<span class="im-skill-tag">${escapeHtml(sk)}</span>`).join('');

            // Industry badge style class
            let indClass = '';
            if (industry.toLowerCase().includes('ai') || industry.toLowerCase().includes('intelligence')) indClass = 'ai';
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
                        <span class="im-position-title">${escapeHtml(position)}</span>
                        <span class="im-position-type">${escapeHtml(mode || 'Full-time • Remote')}</span>
                    </div>
                </td>
                <td class="im-company-name">${escapeHtml(company)}</td>
                <td>
                    <span class="im-industry-badge ${indClass}">${escapeHtml(industry || 'Technology')}</span>
                </td>
                <td>
                    <div class="im-skills-stack">
                        ${skillsStack}
                    </div>
                </td>
                <td class="im-applications-count">0</td>
                <td class="im-deadline-date">${escapeHtml(deadline)}</td>
                <td>
                    <button class="im-btn-action" title="View Details" onclick="viewInternshipDetails('${newId}')">
                        <span class="material-symbols-outlined">visibility</span>
                    </button>
                </td>
            `;

            if (tableBody) {
                tableBody.insertBefore(newRow, tableBody.firstChild);
            }

            window.closeAddInternshipModal();
            showToast(`Internship "${position}" added successfully!`);
            applyFilters();
        });
    }

    // --------------------------------------------------------------------------
    // Suspend Internship Modal & Moderation Logic
    // --------------------------------------------------------------------------
    const suspendModal = document.getElementById('imSuspendModal');
    const suspendForm = document.getElementById('imSuspendForm');
    const suspendTargetId = document.getElementById('imSuspendTargetId');
    const suspendTargetPosition = document.getElementById('imSuspendTargetPosition');
    const suspendTargetIdBadge = document.getElementById('imSuspendTargetIdBadge');
    const suspendTargetCompany = document.getElementById('imSuspendTargetCompany');
    const suspendCategory = document.getElementById('imSuspendCategory');
    const suspendReason = document.getElementById('imSuspendReason');
    const companyMessage = document.getElementById('imCompanyMessage');

    // Generate dynamic professional company notification message
    function generateCompanyMessage(company, position, id, reason) {
        const reasonText = reason ? reason.trim() : '[Violation of SkillBridge terms or student complaint received]';
        return `Dear ${company} Team,\n\nWe would like to notify you that your internship opportunity "${position}" (ID: ${id}) has been temporarily suspended by SkillBridge Administration.\n\nReason for Suspension:\n${reasonText}\n\nWhile suspended, this opportunity is deactivated and hidden from students. If you believe this action was taken in error or if you have addressed this issue, please contact our support desk or reply to this notice.\n\nRegards,\nSkillBridge Compliance & Admin Team`;
    }

    // Auto update notification message preview when admin types specific reason
    if (suspendReason) {
        suspendReason.addEventListener('input', () => {
            const company = suspendTargetCompany ? suspendTargetCompany.textContent.trim() : 'Employer';
            const position = suspendTargetPosition ? suspendTargetPosition.textContent.trim() : 'Internship';
            const id = suspendTargetId ? suspendTargetId.value : '';
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
        const company = row.getAttribute('data-company') || 'Company';

        if (suspendTargetId) suspendTargetId.value = internshipId;
        if (suspendTargetPosition) suspendTargetPosition.textContent = position;
        if (suspendTargetIdBadge) suspendTargetIdBadge.textContent = internshipId;
        if (suspendTargetCompany) suspendTargetCompany.textContent = company;
        if (suspendCategory) suspendCategory.selectedIndex = 0;
        if (suspendReason) suspendReason.value = '';
        if (companyMessage) {
            companyMessage.value = generateCompanyMessage(company, position, internshipId, '');
        }

        suspendModal.classList.add('show');
        document.body.style.overflow = 'hidden';
    };

    window.closeSuspendModal = function() {
        if (suspendModal) {
            suspendModal.classList.remove('show');
            document.body.style.overflow = '';
            if (suspendForm) suspendForm.reset();
        }
    };

    if (suspendModal) {
        suspendModal.addEventListener('click', (e) => {
            if (e.target === suspendModal) {
                window.closeSuspendModal();
            }
        });
    }

    if (suspendForm) {
        suspendForm.addEventListener('submit', (e) => {
            e.preventDefault();

            const targetId = suspendTargetId ? suspendTargetId.value : '';
            const category = suspendCategory ? suspendCategory.value : 'Policy Violation';
            const reason = suspendReason ? suspendReason.value.trim() : '';
            const message = companyMessage ? companyMessage.value.trim() : '';

            if (!targetId || !reason) {
                alert('Please provide the suspension reason.');
                return;
            }

            const row = document.querySelector(`tr[data-id="${targetId}"]`);
            if (row) {
                const company = row.getAttribute('data-company') || 'Company';
                row.setAttribute('data-status', 'Suspended');
                row.setAttribute('data-reason', `${category}: ${reason}`);

                // Update Status Cell (Orange Suspended badge)
                const statusCell = row.querySelector('.im-status-cell');
                if (statusCell) {
                    statusCell.innerHTML = `
                        <span class="im-status-badge status-suspended">
                            <span class="material-symbols-outlined" style="font-size: 13px;">pause_circle</span> Suspended
                        </span>
                    `;
                }

                // Update Actions Cell to Review/Reactivate button
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

                // Update details modal if currently open
                if (detailsModal && detailsModal.classList.contains('show')) {
                    const detailBadge = document.getElementById('imDetailStatusBadge');
                    if (detailBadge) {
                        detailBadge.innerHTML = `
                            <span class="im-status-badge status-suspended">
                                <span class="material-symbols-outlined" style="font-size: 13px;">pause_circle</span> Suspended
                            </span>
                        `;
                    }
                    const detailSuspendedBox = document.getElementById('imDetailSuspendedBox');
                    const detailReason = document.getElementById('imDetailSuspensionReason');
                    if (detailSuspendedBox && detailReason) {
                        detailReason.textContent = `${category}: ${reason}`;
                        detailSuspendedBox.style.display = 'flex';
                    }
                    const modActionContainer = document.getElementById('imModerationActionContainer');
                    if (modActionContainer) {
                        modActionContainer.innerHTML = `
                            <button type="button" class="im-btn-reactivate" onclick="closeInternshipDetailsModal(); openReviewSuspensionModal('${targetId}')">
                                <span class="material-symbols-outlined" style="font-size: 18px;">rate_review</span>
                                <span>Review Suspension & Decide</span>
                            </button>
                        `;
                    }
                }

                // Update KPI counter
                const kpiSuspended = document.getElementById('kpiSuspendedCount');
                if (kpiSuspended) {
                    const current = parseInt(kpiSuspended.textContent.replace(/,/g, ''), 10) || 0;
                    kpiSuspended.textContent = current + 1;
                }

                window.closeSuspendModal();
                showToast(`Listing ${targetId} suspended! Notification dispatched to ${company}.`);
                applyFilters();
            }
        });
    }

    // --------------------------------------------------------------------------
    // Reactivate Internship
    // --------------------------------------------------------------------------
    window.reactivateInternship = function(internshipId) {
        const row = document.querySelector(`tr[data-id="${internshipId}"]`);
        if (!row) return;

        const company = row.getAttribute('data-company') || 'Company';
        row.setAttribute('data-status', 'Active');
        row.setAttribute('data-reason', '');

        // Update Status Cell
        const statusCell = row.querySelector('.im-status-cell');
        if (statusCell) {
            statusCell.innerHTML = `
                <span class="im-status-badge status-active">
                    <span class="im-status-dot"></span> Active
                </span>
            `;
        }

        // Update Actions Cell to Suspend button
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

        // Update details modal if open
        if (detailsModal && detailsModal.classList.contains('show')) {
            const detailBadge = document.getElementById('imDetailStatusBadge');
            if (detailBadge) {
                detailBadge.innerHTML = `
                    <span class="im-status-badge status-active">
                        <span class="im-status-dot"></span> Active
                    </span>
                `;
            }
            const detailSuspendedBox = document.getElementById('imDetailSuspendedBox');
            if (detailSuspendedBox) {
                detailSuspendedBox.style.display = 'none';
            }
            const modActionContainer = document.getElementById('imModerationActionContainer');
            if (modActionContainer) {
                modActionContainer.innerHTML = `
                    <button type="button" class="im-btn-suspend" onclick="openSuspendModal('${internshipId}')">
                        <span class="material-symbols-outlined" style="font-size: 18px;">block</span>
                        <span>Suspend Post & Notify Company</span>
                    </button>
                `;
            }
        }

        // Update KPI counter
        const kpiSuspended = document.getElementById('kpiSuspendedCount');
        if (kpiSuspended) {
            const current = parseInt(kpiSuspended.textContent.replace(/,/g, ''), 10) || 0;
            if (current > 0) kpiSuspended.textContent = current - 1;
        }

        showToast(`Listing ${internshipId} reactivated! Visibility restored for students.`);
        applyFilters();
    };

    // --------------------------------------------------------------------------
    // Review Suspended Internship & Decision Modal Logic
    // --------------------------------------------------------------------------
    const reviewSuspensionModal = document.getElementById('imReviewSuspensionModal');
    const revTargetId = document.getElementById('imRevTargetId');
    const revId = document.getElementById('imRevId');
    const revPosition = document.getElementById('imRevPosition');
    const revCompany = document.getElementById('imRevCompany');
    const revReasonText = document.getElementById('imRevReasonText');
    const revActionCard = document.getElementById('imRevActionCard');
    const revActionText = document.getElementById('imRevActionText');
    const revNoActionCard = document.getElementById('imRevNoActionCard');

    window.openReviewSuspensionModal = function(internshipId) {
        if (!reviewSuspensionModal) return;
        const row = document.querySelector(`tr[data-id="${internshipId}"]`);
        if (!row) return;

        const currentStatus = (row.getAttribute('data-status') || '').toLowerCase();
        if (currentStatus === 'terminated') {
            showToast(`Listing ${internshipId} is permanently terminated and cannot be reactivated or modified.`);
            return;
        }

        const position = row.getAttribute('data-position') || 'Internship';
        const company = row.getAttribute('data-company') || 'Company';
        const reason = row.getAttribute('data-reason') || 'Violation of terms or policy complaint reported.';
        const companyAction = (row.getAttribute('data-company-action') || '').trim();

        if (revTargetId) revTargetId.value = internshipId;
        if (revId) revId.textContent = internshipId;
        if (revPosition) revPosition.textContent = position;
        if (revCompany) revCompany.textContent = company;
        if (revReasonText) revReasonText.textContent = reason;

        if (companyAction) {
            if (revActionCard) revActionCard.style.display = 'block';
            if (revActionText) revActionText.textContent = companyAction;
            if (revNoActionCard) revNoActionCard.style.display = 'none';
        } else {
            if (revActionCard) revActionCard.style.display = 'none';
            if (revNoActionCard) revNoActionCard.style.display = 'block';
        }

        reviewSuspensionModal.classList.add('show');
        document.body.style.overflow = 'hidden';
    };

    window.closeReviewSuspensionModal = function() {
        if (reviewSuspensionModal) {
            reviewSuspensionModal.classList.remove('show');
            if (!detailsModal || !detailsModal.classList.contains('show')) {
                document.body.style.overflow = '';
            }
        }
    };

    if (reviewSuspensionModal) {
        reviewSuspensionModal.addEventListener('click', (e) => {
            if (e.target === reviewSuspensionModal) {
                window.closeReviewSuspensionModal();
            }
        });
    }

    // Option 1: Admin is satisfied with the action -> Reactivate to Active
    window.executeReactivationFromReview = function() {
        const targetId = revTargetId ? revTargetId.value : '';
        if (!targetId) return;

        window.reactivateInternship(targetId);
        window.closeReviewSuspensionModal();
    };

    // Option 2: Admin is NOT satisfied -> Permanently Terminate
    window.executeTerminationFromReview = function() {
        const targetId = revTargetId ? revTargetId.value : '';
        if (!targetId) return;

        const row = document.querySelector(`tr[data-id="${targetId}"]`);
        if (!row) return;

        row.setAttribute('data-status', 'Terminated');
        row.setAttribute('data-terminate-reason', 'Terminated by Admin due to unsatisfactory resolution or failure to resolve policy complaints.');

        // Update status cell in table (Red Terminated badge)
        const statusCell = row.querySelector('.im-status-cell');
        if (statusCell) {
            statusCell.innerHTML = `
                <span class="im-status-badge status-terminated">
                    <span class="material-symbols-outlined" style="font-size: 13px;">cancel</span> Terminated
                </span>
            `;
        }

        // Update actions cell: View Details + locked icon (Cannot be reactivated or edited!)
        const actionsCell = row.querySelector('.im-actions-cell');
        if (actionsCell) {
            actionsCell.innerHTML = `
                <button type="button" class="im-btn-action" title="View Details" onclick="viewInternshipDetails('${targetId}')">
                    <span class="material-symbols-outlined">visibility</span>
                </button>
                <button type="button" class="im-btn-action" disabled title="Permanently Terminated (Cannot be modified)" style="color: #cbd5e1; cursor: not-allowed;">
                    <span class="material-symbols-outlined">lock</span>
                </button>
            `;
        }

        // If details modal is open, refresh it
        if (detailsModal && detailsModal.classList.contains('show')) {
            const detailId = document.getElementById('imDetailId');
            if (detailId && detailId.textContent === targetId) {
                window.viewInternshipDetails(targetId);
            }
        }

        // Update KPI counter
        const kpiSuspended = document.getElementById('kpiSuspendedCount');
        if (kpiSuspended) {
            const current = parseInt(kpiSuspended.textContent.replace(/,/g, ''), 10) || 0;
            if (current > 0) kpiSuspended.textContent = current - 1;
        }

        window.closeReviewSuspensionModal();
        showToast(`Listing ${targetId} has been permanently terminated. State is locked.`);
        applyFilters();
    };

    // --------------------------------------------------------------------------
    // Details Modal
    // --------------------------------------------------------------------------
    window.viewInternshipDetails = function(internshipId) {
        if (!detailsModal) return;

        const row = document.querySelector(`tr[data-id="${internshipId}"]`);
        if (!row) return;

        const position = row.getAttribute('data-position') || 'Internship';
        const company = row.getAttribute('data-company') || 'Company';
        const industry = row.getAttribute('data-industry') || 'Technology';
        const skills = row.getAttribute('data-skills') || 'General';
        const status = (row.getAttribute('data-status') || 'Active').toLowerCase();
        const duration = row.getAttribute('data-duration') || 'Full-time';
        const reason = row.getAttribute('data-reason') || '';
        const deadline = row.cells[6] ? row.cells[6].textContent.trim() : 'N/A';
        const apps = row.cells[5] ? row.cells[5].textContent.trim() : '0';

        document.getElementById('imDetailId').textContent = internshipId;
        document.getElementById('imDetailTitle').textContent = position;
        document.getElementById('imDetailCompany').textContent = company;
        document.getElementById('imDetailIndustry').textContent = industry;
        document.getElementById('imDetailSkills').textContent = skills;
        document.getElementById('imDetailDuration').textContent = duration;
        document.getElementById('imDetailDeadline').textContent = deadline;
        document.getElementById('imDetailApps').textContent = apps;

        const statusBadgeContainer = document.getElementById('imDetailStatusBadge');
        const suspendedBox = document.getElementById('imDetailSuspendedBox');
        const suspendedReasonText = document.getElementById('imDetailSuspensionReason');
        const terminatedBox = document.getElementById('imDetailTerminatedBox');
        const terminatedReasonText = document.getElementById('imDetailTerminationReason');
        const moderationActionContainer = document.getElementById('imModerationActionContainer');
        const terminateReason = row.getAttribute('data-terminate-reason') || '';

        if (status === 'suspended') {
            if (statusBadgeContainer) {
                statusBadgeContainer.innerHTML = `
                    <span class="im-status-badge status-suspended">
                        <span class="material-symbols-outlined" style="font-size: 13px;">pause_circle</span> Suspended
                    </span>
                `;
            }
            if (suspendedBox) {
                suspendedBox.style.display = 'flex';
                if (suspendedReasonText) {
                    suspendedReasonText.textContent = reason || 'Violation of terms or policy complaints reported.';
                }
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
            if (statusBadgeContainer) {
                statusBadgeContainer.innerHTML = `
                    <span class="im-status-badge status-terminated">
                        <span class="material-symbols-outlined" style="font-size: 13px;">cancel</span> Terminated
                    </span>
                `;
            }
            if (suspendedBox) suspendedBox.style.display = 'none';
            if (terminatedBox) {
                terminatedBox.style.display = 'flex';
                if (terminatedReasonText) {
                    terminatedReasonText.textContent = terminateReason || reason || 'Permanently terminated due to policy non-compliance or failure to resolve complaints.';
                }
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
            if (statusBadgeContainer) {
                statusBadgeContainer.innerHTML = `
                    <span class="im-status-badge status-closed">
                        <span class="im-status-dot"></span> Closed
                    </span>
                `;
            }
            if (suspendedBox) suspendedBox.style.display = 'none';
            if (terminatedBox) terminatedBox.style.display = 'none';
            if (moderationActionContainer) {
                moderationActionContainer.innerHTML = `
                    <div style="font-size: 12.5px; color: #64748b; font-style: italic;">
                        This internship listing has closed. No active moderation needed.
                    </div>
                `;
            }
        } else {
            if (statusBadgeContainer) {
                statusBadgeContainer.innerHTML = `
                    <span class="im-status-badge status-active">
                        <span class="im-status-dot"></span> Active
                    </span>
                `;
            }
            if (suspendedBox) suspendedBox.style.display = 'none';
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

        detailsModal.classList.add('show');
        document.body.style.overflow = 'hidden';
    };

    window.closeInternshipDetailsModal = function() {
        if (detailsModal) {
            detailsModal.classList.remove('show');
            document.body.style.overflow = '';
        }
    };

    if (detailsModal) {
        detailsModal.addEventListener('click', (e) => {
            if (e.target === detailsModal) {
                window.closeInternshipDetailsModal();
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
    // Pagination Controls Interaction
    // --------------------------------------------------------------------------
    const pageButtons = document.querySelectorAll('.im-page-number');
    pageButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            pageButtons.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            showToast(`Navigated to page ${btn.textContent.trim()}`);
        });
    });

    // String escape helper
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
}
