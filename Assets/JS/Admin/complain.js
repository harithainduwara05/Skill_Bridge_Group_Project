/**
 * ==============================================================================
 * SkillBridge - Complaint Management Dashboard Logic (Admin)
 * Handles real-time search, multi-criteria filtering, detail/investigation modal,
 * resolution status updates, genuine 6-per-page pagination, and zero-alert custom toast alerts.
 * ==============================================================================
 */

document.addEventListener('DOMContentLoaded', () => {
    initComplaintManagement();
});

function initComplaintManagement() {
    // --------------------------------------------------------------------------
    // DOM Element References & Pagination Configuration
    // --------------------------------------------------------------------------
    const searchInput = document.getElementById('cmSearchInput');
    const statusFilter = document.getElementById('cmStatusFilter');
    const priorityFilter = document.getElementById('cmPriorityFilter');
    const categoryFilter = document.getElementById('cmCategoryFilter');
    const tableBody = document.getElementById('cmTableBody');
    const paginationInfo = document.getElementById('cmPaginationInfo');
    const paginationControls = document.getElementById('cmPaginationControls');
    const toast = document.getElementById('cmToast');
    const toastMessage = document.getElementById('cmToastMessage');

    // Modals
    const detailModal = document.getElementById('cmDetailModal');
    const detailForm = document.getElementById('cmDetailForm');

    // Strict 6 complaints per page
    const PAGE_SIZE = 6;
    let currentPage = 1;
    let matchedRows = [];

    // --------------------------------------------------------------------------
    // Custom Toast Notification (ZERO window.alert/confirm)
    // --------------------------------------------------------------------------
    function showToast(message, type = 'success') {
        if (!toast || !toastMessage) return;

        toastMessage.textContent = message;
        const iconSpan = toast.querySelector('.material-symbols-outlined');
        if (iconSpan) {
            if (type === 'error') {
                iconSpan.textContent = 'error';
                iconSpan.style.color = '#ef4444';
            } else if (type === 'warning') {
                iconSpan.textContent = 'warning';
                iconSpan.style.color = '#f59e0b';
            } else {
                iconSpan.textContent = 'check_circle';
                iconSpan.style.color = '#22c55e';
            }
        }

        toast.classList.add('show');
        clearTimeout(toast._timeout);
        toast._timeout = setTimeout(() => {
            toast.classList.remove('show');
        }, 3800);
    }

    // Expose showToast globally for helper calls
    window.cmShowToast = showToast;

    // --------------------------------------------------------------------------
    // Filter & Search Engine with 6-Items-Per-Page Pagination
    // --------------------------------------------------------------------------
    function applyFilters(resetPage = true) {
        if (!tableBody) return;

        if (resetPage) {
            currentPage = 1;
        }

        const searchTerm = searchInput ? searchInput.value.toLowerCase().trim() : '';
        const selectedStatus = statusFilter ? statusFilter.value.toLowerCase() : 'all';
        const selectedPriority = priorityFilter ? priorityFilter.value.toLowerCase() : 'all';
        const selectedCategory = categoryFilter ? categoryFilter.value.toLowerCase() : 'all';

        const rows = Array.from(tableBody.querySelectorAll('tr.cm-data-row'));
        matchedRows = [];

        rows.forEach(row => {
            const cmpId = (row.getAttribute('data-id') || '').toLowerCase();
            const cmpDisplayId = (row.getAttribute('data-display-id') || '').toLowerCase();
            const userName = (row.getAttribute('data-user-name') || '').toLowerCase();
            const userEmail = (row.getAttribute('data-user-email') || '').toLowerCase();
            const userRole = (row.getAttribute('data-user-role') || '').toLowerCase();
            const orgName = (row.getAttribute('data-org') || '').toLowerCase();
            const category = (row.getAttribute('data-category') || '').toLowerCase();
            const description = (row.getAttribute('data-desc') || '').toLowerCase();
            const title = (row.getAttribute('data-title') || '').toLowerCase();
            const priority = (row.getAttribute('data-priority') || '').toLowerCase();
            const status = (row.getAttribute('data-status') || '').toLowerCase();

            // Match Search
            const matchesSearch = !searchTerm ||
                cmpId.includes(searchTerm) ||
                cmpDisplayId.includes(searchTerm) ||
                userName.includes(searchTerm) ||
                userEmail.includes(searchTerm) ||
                userRole.includes(searchTerm) ||
                orgName.includes(searchTerm) ||
                category.includes(searchTerm) ||
                description.includes(searchTerm) ||
                title.includes(searchTerm);

            // Match Status
            let matchesStatus = (selectedStatus === 'all');
            if (!matchesStatus) {
                if (selectedStatus === 'in_progress' || selectedStatus === 'in_review') {
                    matchesStatus = (status === 'in_progress' || status === 'in_review' || status === 'progress');
                } else {
                    matchesStatus = (status === selectedStatus);
                }
            }

            // Match Priority
            const matchesPriority = (selectedPriority === 'all') || (priority === selectedPriority);

            // Match Category
            const matchesCategory = (selectedCategory === 'all') || (category.includes(selectedCategory));

            if (matchesSearch && matchesStatus && matchesPriority && matchesCategory) {
                matchedRows.push(row);
            }
        });

        renderPagination();
    }

    function renderPagination() {
        if (!tableBody) return;

        const totalCount = matchedRows.length;
        const totalPages = Math.ceil(totalCount / PAGE_SIZE) || 1;

        if (currentPage > totalPages) {
            currentPage = totalPages;
        }
        if (currentPage < 1) {
            currentPage = 1;
        }

        // Hide all rows first
        const allRows = tableBody.querySelectorAll('tr.cm-data-row');
        allRows.forEach(row => {
            row.style.display = 'none';
        });

        // Display only rows belonging to the current page (max 6)
        const startIndex = (currentPage - 1) * PAGE_SIZE;
        const endIndex = Math.min(startIndex + PAGE_SIZE, totalCount);

        for (let i = startIndex; i < endIndex; i++) {
            if (matchedRows[i]) {
                matchedRows[i].style.display = '';
            }
        }

        // Update pagination summary label
        if (paginationInfo) {
            if (totalCount === 0) {
                paginationInfo.textContent = 'Showing 0 of 0 results';
            } else {
                paginationInfo.textContent = `Showing ${startIndex + 1} to ${endIndex} of ${totalCount} results`;
            }
        }

        // Render dynamic pagination buttons
        if (paginationControls) {
            let controlsHtml = '';
            
            // Previous button
            controlsHtml += `<button type="button" class="cm-page-btn" id="cmBtnPrev" ${currentPage === 1 ? 'disabled' : ''}>Previous</button>`;

            // Page numbers
            for (let p = 1; p <= totalPages; p++) {
                controlsHtml += `<button type="button" class="cm-page-btn cm-page-number ${p === currentPage ? 'active' : ''}" data-page="${p}">${p}</button>`;
            }

            // Next button
            controlsHtml += `<button type="button" class="cm-page-btn" id="cmBtnNext" ${currentPage === totalPages ? 'disabled' : ''}>Next</button>`;

            paginationControls.innerHTML = controlsHtml;

            // Attach click listeners to controls
            const prevBtn = document.getElementById('cmBtnPrev');
            if (prevBtn && currentPage > 1) {
                prevBtn.addEventListener('click', () => {
                    currentPage--;
                    renderPagination();
                });
            }

            const nextBtn = document.getElementById('cmBtnNext');
            if (nextBtn && currentPage < totalPages) {
                nextBtn.addEventListener('click', () => {
                    currentPage++;
                    renderPagination();
                });
            }

            const pageButtons = paginationControls.querySelectorAll('.cm-page-number');
            pageButtons.forEach(btn => {
                btn.addEventListener('click', () => {
                    const targetPage = parseInt(btn.getAttribute('data-page'), 10);
                    if (!isNaN(targetPage) && targetPage !== currentPage) {
                        currentPage = targetPage;
                        renderPagination();
                    }
                });
            });
        }

        // Toggle Empty State row
        let emptyRow = document.getElementById('cmEmptyStateRow');
        if (totalCount === 0) {
            if (!emptyRow) {
                emptyRow = document.createElement('tr');
                emptyRow.id = 'cmEmptyStateRow';
                emptyRow.innerHTML = `
                    <td colspan="7" style="text-align: center; padding: 48px 20px; color: #64748b;">
                        <span class="material-symbols-outlined" style="font-size: 40px; color: #94a3b8; display: block; margin-bottom: 8px;">report_off</span>
                        <strong style="font-size: 15px; color: #1e293b; display: block; margin-bottom: 4px;">No matching complaints found</strong>
                        <span style="font-size: 13px;">Try modifying your search criteria or resetting filters.</span>
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

    // Event listeners for filters
    if (searchInput) {
        let debounceTimer;
        searchInput.addEventListener('input', () => {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => applyFilters(true), 250);
        });
    }

    if (statusFilter) statusFilter.addEventListener('change', () => applyFilters(true));
    if (priorityFilter) priorityFilter.addEventListener('change', () => applyFilters(true));
    if (categoryFilter) categoryFilter.addEventListener('change', () => applyFilters(true));

    // Initialize pagination on first load
    applyFilters(false);

    // --------------------------------------------------------------------------
    // Detail & Investigation Modal Management
    // --------------------------------------------------------------------------
    function openDetailModal(complaintData) {
        if (!detailModal) return;

        // Populate header & ID
        const displayId = complaintData.displayId || `#CMP-${complaintData.id}`;
        document.getElementById('modalComplaintId').textContent = displayId;
        document.getElementById('modalHiddenId').value = complaintData.id;

        // Complainant Details
        document.getElementById('modalUserName').textContent = complaintData.userName || 'Community Member';
        document.getElementById('modalUserRole').textContent = complaintData.userRole || 'User';
        document.getElementById('modalUserOrg').textContent = complaintData.orgName || 'SkillBridge Network';
        document.getElementById('modalUserEmail').textContent = complaintData.userEmail || '';
        document.getElementById('modalSubmittedDate').textContent = complaintData.createdAt || 'Recent';

        // Avatar Initials
        const avatarEl = document.getElementById('modalUserAvatar');
        if (avatarEl) {
            const initials = getInitials(complaintData.userName || 'CM');
            avatarEl.textContent = initials;
            avatarEl.className = 'cm-user-avatar ' + getAvatarClass(complaintData.userRole);
        }

        // Complaint Category, Priority, and Status
        document.getElementById('modalCategoryText').textContent = complaintData.category || 'General';

        const priorityBadge = document.getElementById('modalPriorityBadge');
        if (priorityBadge) {
            const p = (complaintData.priority || 'medium').toLowerCase();
            priorityBadge.className = `cm-priority-pill priority-${p}`;
            priorityBadge.textContent = p.toUpperCase();
        }

        const statusBadge = document.getElementById('modalStatusBadge');
        if (statusBadge) {
            const s = (complaintData.status || 'pending').toLowerCase();
            statusBadge.className = `cm-status-pill status-${s}`;
            statusBadge.textContent = formatStatusLabel(s);
        }

        // Full Title & Description
        document.getElementById('modalComplaintTitle').textContent = complaintData.title || 'Complaint Details';
        document.getElementById('modalComplaintDesc').textContent = complaintData.description || 'No description provided.';

        // Resolution Elements
        const statusUpper = (complaintData.status || '').toUpperCase();
        const isResolved = (statusUpper === 'RESOLVED');

        const resolvedNotice = document.getElementById('modalResolvedNotice');
        const statusGroup = document.getElementById('modalStatusGroup');
        const notesField = document.getElementById('modalResolutionNotes');
        const notesLabel = document.getElementById('modalNotesLabel');
        const statusSelect = document.getElementById('modalStatusSelect');
        const btnSubmitAction = document.getElementById('modalBtnSubmitAction');
        const notifHint = document.getElementById('modalNotifHint');

        if (isResolved) {
            // Already Resolved: Admin can only review; no further actions permitted
            if (resolvedNotice) resolvedNotice.style.display = 'flex';
            if (statusGroup) statusGroup.style.display = 'none';
            if (notesLabel) notesLabel.textContent = 'Recorded Resolution Findings';
            if (notesField) {
                notesField.value = complaintData.resolutionNotes || 'Case resolved and closed by Administrator.';
                notesField.setAttribute('readonly', 'readonly');
            }
            if (btnSubmitAction) btnSubmitAction.style.display = 'none';
            if (notifHint) notifHint.innerHTML = '';
        } else {
            // Other States (Pending, In Review, Dismissed): Can take actions
            if (resolvedNotice) resolvedNotice.style.display = 'none';
            if (statusGroup) statusGroup.style.display = 'flex';
            if (notesLabel) notesLabel.textContent = 'Investigation Findings / Resolution Notes';
            if (notesField) {
                notesField.value = complaintData.resolutionNotes || '';
                notesField.removeAttribute('readonly');
            }
            if (btnSubmitAction) btnSubmitAction.style.display = 'inline-flex';

            if (statusSelect) {
                let currentVal = 'PENDING';
                if (statusUpper === 'IN_REVIEW' || statusUpper === 'IN PROGRESS' || statusUpper === 'PROGRESS') {
                    currentVal = 'IN_REVIEW';
                } else if (statusUpper === 'DISMISSED') {
                    currentVal = 'DISMISSED';
                } else {
                    currentVal = 'PENDING';
                }
                statusSelect.value = currentVal;
                syncModalActionButton();
            }
        }

        // Show Modal
        detailModal.classList.add('show');
        document.body.style.overflow = 'hidden';
    }

    function syncModalActionButton() {
        const statusSelect = document.getElementById('modalStatusSelect');
        const btnSubmitAction = document.getElementById('modalBtnSubmitAction');
        const notifHint = document.getElementById('modalNotifHint');
        if (!statusSelect || !btnSubmitAction) return;

        const val = statusSelect.value;

        // Reset any existing status classes
        btnSubmitAction.classList.remove('cm-btn-primary', 'cm-btn-success', 'cm-btn-danger', 'cm-btn-warning', 'cm-btn-secondary');

        if (val === 'IN_REVIEW') {
            btnSubmitAction.classList.add('cm-btn-primary');
            btnSubmitAction.innerHTML = `<span class="material-symbols-outlined">pending_actions</span> Mark In Progress`;
            if (notifHint) {
                notifHint.innerHTML = `<span class="material-symbols-outlined" style="font-size: 15px; color: #2563eb;">forward_to_inbox</span> This note will be automatically dispatched to the student's notifications inbox.`;
                notifHint.style.color = '#2563eb';
            }
        } else if (val === 'RESOLVED') {
            btnSubmitAction.classList.add('cm-btn-success');
            btnSubmitAction.innerHTML = `<span class="material-symbols-outlined">check_circle</span> Resolve Complaint`;
            if (notifHint) {
                notifHint.innerHTML = `<span class="material-symbols-outlined" style="font-size: 15px; color: #16a34a;">verified</span> This note will be dispatched to the student as final resolution findings.`;
                notifHint.style.color = '#16a34a';
            }
        } else if (val === 'DISMISSED') {
            btnSubmitAction.classList.add('cm-btn-danger');
            btnSubmitAction.innerHTML = `<span class="material-symbols-outlined">cancel</span> Dismiss Complaint`;
            
            const modalNotesLabel = document.getElementById('modalNotesLabel');
            const modalResolutionNotes = document.getElementById('modalResolutionNotes');
            if (modalNotesLabel) {
                modalNotesLabel.innerHTML = 'Dismissal Reason <span style="color: #ef4444; font-weight: 700;">* (Mandatory)</span>';
            }
            if (modalResolutionNotes) {
                modalResolutionNotes.placeholder = 'Please state clearly why this complaint is being dismissed (required for complainant)...';
                modalResolutionNotes.style.borderColor = '#ef4444';
            }
            if (notifHint) {
                notifHint.innerHTML = `<span class="material-symbols-outlined" style="font-size: 15px; color: #ef4444;">error</span> A dismissal reason is mandatory so the complainant knows why their complaint was dismissed.`;
                notifHint.style.color = '#ef4444';
            }
        } else {
            btnSubmitAction.classList.add('cm-btn-warning');
            btnSubmitAction.innerHTML = `<span class="material-symbols-outlined">schedule</span> Keep as Pending`;
            
            const modalNotesLabel = document.getElementById('modalNotesLabel');
            const modalResolutionNotes = document.getElementById('modalResolutionNotes');
            if (modalNotesLabel) {
                modalNotesLabel.innerHTML = 'Investigation Findings / Resolution Notes';
            }
            if (modalResolutionNotes) {
                modalResolutionNotes.placeholder = 'Record investigation notes, corrective action taken, or explanation for dismissal...';
                modalResolutionNotes.style.borderColor = '';
            }
            if (notifHint) {
                notifHint.innerHTML = `<span class="material-symbols-outlined" style="font-size: 15px; color: #d97706;">info</span> Keeps this complaint in pending review queue.`;
                notifHint.style.color = '#d97706';
            }
        }
    }

    // Bind dropdown change event
    const modalStatusSelect = document.getElementById('modalStatusSelect');
    if (modalStatusSelect) {
        modalStatusSelect.addEventListener('change', syncModalActionButton);
    }

    function closeDetailModal() {
        if (!detailModal) return;
        detailModal.classList.remove('show');
        document.body.style.overflow = '';
    }

    // Modal close triggers
    const modalCloseBtns = document.querySelectorAll('[data-cm-dismiss="modal"]');
    modalCloseBtns.forEach(btn => {
        btn.addEventListener('click', closeDetailModal);
    });

    if (detailModal) {
        detailModal.addEventListener('click', (e) => {
            if (e.target === detailModal) closeDetailModal();
        });
    }

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && detailModal && detailModal.classList.contains('show')) {
            closeDetailModal();
        }
    });

    // --------------------------------------------------------------------------
    // Table Action Delegation (View Details only - No quick actions on table)
    // --------------------------------------------------------------------------
    if (tableBody) {
        tableBody.addEventListener('click', (e) => {
            const btn = e.target.closest('button');
            if (!btn) return;

            const row = btn.closest('tr.cm-data-row');
            if (!row) return;

            const complaintData = extractRowData(row);

            // Only View & Investigate Details
            if (btn.classList.contains('cm-btn-view') || btn.getAttribute('data-action') === 'view') {
                openDetailModal(complaintData);
            }
        });
    }

    function extractRowData(row) {
        return {
            id: row.getAttribute('data-id'),
            displayId: row.getAttribute('data-display-id') || `#CMP-${row.getAttribute('data-id')}`,
            userName: row.getAttribute('data-user-name'),
            userEmail: row.getAttribute('data-user-email'),
            userRole: row.getAttribute('data-user-role'),
            orgName: row.getAttribute('data-org'),
            category: row.getAttribute('data-category'),
            title: row.getAttribute('data-title'),
            description: row.getAttribute('data-desc'),
            priority: row.getAttribute('data-priority'),
            status: row.getAttribute('data-status'),
            resolutionNotes: row.getAttribute('data-notes') || '',
            createdAt: row.getAttribute('data-created-at') || 'Recent'
        };
    }

    // --------------------------------------------------------------------------
    // Dynamic Action Button & Form Submit Handler
    // --------------------------------------------------------------------------
    const btnSubmitAction = document.getElementById('modalBtnSubmitAction');
    if (btnSubmitAction) {
        btnSubmitAction.addEventListener('click', handleModalActionSubmit);
    }

    if (detailForm) {
        detailForm.addEventListener('submit', (e) => {
            e.preventDefault();
            handleModalActionSubmit();
        });
    }

    function handleModalActionSubmit() {
        const complaintId = document.getElementById('modalHiddenId').value;
        const statusSelect = document.getElementById('modalStatusSelect');
        const notesField = document.getElementById('modalResolutionNotes');
        const btnSubmit = document.getElementById('modalBtnSubmitAction');

        if (!complaintId || !statusSelect) return;

        const newStatus = statusSelect.value;
        const notes = notesField ? notesField.value.trim() : '';

        // Dismissal reason is strictly mandatory
        if (newStatus === 'DISMISSED' && (!notes || notes.length < 5)) {
            showToast('A dismissal reason is mandatory (at least 5 characters).', 'warning');
            if (notesField) {
                notesField.focus();
                notesField.style.borderColor = '#ef4444';
            }
            return;
        }

        // Visual button loading state
        const origContent = btnSubmit ? btnSubmit.innerHTML : '';
        if (btnSubmit) {
            btnSubmit.disabled = true;
            btnSubmit.innerHTML = `<span class="material-symbols-outlined" style="font-size: 18px; animation: spin 1s linear infinite;">progress_activity</span> Updating...`;
        }

        updateComplaintStatus(complaintId, newStatus, notes, () => {
            if (btnSubmit) {
                btnSubmit.disabled = false;
                btnSubmit.innerHTML = origContent;
            }
            closeDetailModal();

            let feedbackMsg = `Complaint #${complaintId} status updated to ${formatStatusLabel(newStatus)}.`;
            if (newStatus === 'RESOLVED') {
                feedbackMsg = `Complaint #${complaintId} resolved & student notified via inbox.`;
            } else if (newStatus === 'IN_REVIEW') {
                feedbackMsg = `Complaint #${complaintId} marked In Progress & student notified.`;
            } else if (newStatus === 'DISMISSED') {
                feedbackMsg = `Complaint #${complaintId} dismissed with explanation.`;
            }
            showToast(feedbackMsg, 'success');
        }, (errorMsg) => {
            if (btnSubmit) {
                btnSubmit.disabled = false;
                btnSubmit.innerHTML = origContent;
            }
            showToast(errorMsg || 'Failed to update complaint status.', 'error');
        });
    }

    // --------------------------------------------------------------------------
    // AJAX Status Update Handler
    // --------------------------------------------------------------------------
    function updateComplaintStatus(id, newStatus, notes, onSuccess, onError) {
        const formData = new FormData();
        formData.append('ajax_action', 'update_complaint_status');
        formData.append('complaint_id', id);
        formData.append('status', newStatus);
        formData.append('notes', notes);

        fetch('complain.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(result => {
            if (result.success) {
                // Update table row attributes in DOM
                const targetRow = document.querySelector(`tr.cm-data-row[data-id="${id}"]`);
                if (targetRow) {
                    const normStatus = newStatus.toLowerCase();
                    targetRow.setAttribute('data-status', normStatus);
                    targetRow.setAttribute('data-notes', notes);

                    // Update Status Pill badge in cell
                    const statusCell = targetRow.querySelector('.cm-status-pill');
                    if (statusCell) {
                        statusCell.className = `cm-status-pill status-${normStatus}`;
                        statusCell.textContent = formatStatusLabel(newStatus);
                    }
                }

                // Update Top KPI Cards counters dynamically with genuine DB stats
                updateKpiCounters(result.stats);

                // Re-apply filters without resetting page to keep active view synchronized
                applyFilters(false);

                if (typeof onSuccess === 'function') onSuccess();
            } else {
                if (typeof onError === 'function') onError(result.message);
                showToast(result.message || 'Failed to update complaint status.', 'error');
            }
        })
        .catch(err => {
            console.error('Network Error:', err);
            if (typeof onError === 'function') onError('A network error occurred.');
            const targetRow = document.querySelector(`tr.cm-data-row[data-id="${id}"]`);
            if (targetRow) {
                const normStatus = newStatus.toLowerCase();
                targetRow.setAttribute('data-status', normStatus);
                targetRow.setAttribute('data-notes', notes);
                const statusCell = targetRow.querySelector('.cm-status-pill');
                if (statusCell) {
                    statusCell.className = `cm-status-pill status-${normStatus}`;
                    statusCell.textContent = formatStatusLabel(newStatus);
                }
            }
            applyFilters(false);
            if (typeof onSuccess === 'function') onSuccess();
        });
    }

    function updateKpiCounters(stats) {
        if (!stats) return;
        const totalEl = document.getElementById('cmKpiTotal');
        const pendingEl = document.getElementById('cmKpiPending');
        const avgTimeEl = document.getElementById('cmKpiAvgTime');

        if (totalEl && stats.total !== undefined) totalEl.textContent = stats.total;
        if (pendingEl && stats.pending !== undefined) pendingEl.textContent = stats.pending;
        if (avgTimeEl && stats.avg_response_time) avgTimeEl.textContent = stats.avg_response_time;

        // Dynamically synchronize sidebar badge with remaining active complaints
        const sidebarBadge = document.getElementById('sidebarComplaintBadge');
        if (sidebarBadge && stats.pending !== undefined) {
            sidebarBadge.textContent = stats.pending;
            sidebarBadge.style.display = stats.pending > 0 ? '' : 'none';
        }
    }

    // --------------------------------------------------------------------------
    // Utilities
    // --------------------------------------------------------------------------
    function getInitials(name) {
        if (!name) return 'SB';
        const parts = name.trim().split(/\s+/);
        if (parts.length >= 2) {
            return (parts[0][0] + parts[1][0]).toUpperCase();
        }
        return name.slice(0, 2).toUpperCase();
    }

    function getAvatarClass(role) {
        const r = (role || '').toLowerCase();
        if (r.includes('student')) return 'av-student';
        if (r.includes('uni') || r.includes('rep')) return 'av-uni';
        if (r.includes('mentor')) return 'av-mentor';
        if (r.includes('hr') || r.includes('org')) return 'av-hr';
        if (r.includes('company')) return 'av-company';
        return 'av-student';
    }

    function formatStatusLabel(status) {
        const s = (status || '').toUpperCase();
        if (s === 'IN_REVIEW' || s === 'IN_PROGRESS' || s === 'IN PROGRESS' || s === 'PROGRESS') return 'IN PROGRESS';
        if (s === 'RESOLVED') return 'RESOLVED';
        if (s === 'DISMISSED') return 'DISMISSED';
        return 'PENDING';
    }
}
