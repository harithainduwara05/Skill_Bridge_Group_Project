
document.addEventListener('DOMContentLoaded', () => {
    initComplaintManagement();
});

function initComplaintManagement() {
    // --------------------------------------------------------------------------
    // DOM Element References & Pagination Configuration
    // --------------------------------------------------------------------------
    const searchInput        = document.getElementById('cmSearchInput');
    const statusFilter       = document.getElementById('cmStatusFilter');
    const priorityFilter     = document.getElementById('cmPriorityFilter');
    const categoryFilter     = document.getElementById('cmCategoryFilter');
    const tableBody          = document.getElementById('cmTableBody');
    const paginationInfo     = document.getElementById('cmPaginationInfo');
    const paginationControls = document.getElementById('cmPaginationControls');

    // Detail Modal
    const detailModal = document.getElementById('cmDetailModal');
    const detailForm  = document.getElementById('cmDetailForm');

    // Strict 6 complaints per page
    const PAGE_SIZE = 6;
    let currentPage = 1;
    let matchedRows = [];

    // --------------------------------------------------------------------------
    // Filter & Search Engine with Pagination
    // --------------------------------------------------------------------------
    function applyFilters(resetPage = true) {
        if (!tableBody) return;
        if (resetPage) currentPage = 1;

        const searchTerm       = searchInput ? searchInput.value.toLowerCase().trim() : '';
        const selectedStatus   = statusFilter ? statusFilter.value.toLowerCase() : 'all';
        const selectedPriority = priorityFilter ? priorityFilter.value.toLowerCase() : 'all';
        const selectedCategory = categoryFilter ? categoryFilter.value.toLowerCase() : 'all';

        const rows = Array.from(tableBody.querySelectorAll('tr.cm-data-row'));
        matchedRows = [];

        rows.forEach(row => {
            const cmpId        = (row.getAttribute('data-id') || '').toLowerCase();
            const cmpDisplayId = (row.getAttribute('data-display-id') || '').toLowerCase();
            const userName     = (row.getAttribute('data-user-name') || '').toLowerCase();
            const userEmail    = (row.getAttribute('data-user-email') || '').toLowerCase();
            const userRole     = (row.getAttribute('data-user-role') || '').toLowerCase();
            const orgName      = (row.getAttribute('data-org') || '').toLowerCase();
            const category     = (row.getAttribute('data-category') || '').toLowerCase();
            const description  = (row.getAttribute('data-desc') || '').toLowerCase();
            const title        = (row.getAttribute('data-title') || '').toLowerCase();
            const priority     = (row.getAttribute('data-priority') || '').toLowerCase();
            const status       = (row.getAttribute('data-status') || '').toLowerCase();

            const matchesSearch = !searchTerm ||
                cmpId.includes(searchTerm) ||
                cmpDisplayId.includes(searchTerm) ||
                userName.includes(searchTerm) ||
                userEmail.includes(searchTerm) ||
                userRole.includes(searchTerm) ||
                orgName.includes(searchTerm) ||
                category.includes(searchTerm) ||
                title.includes(searchTerm) ||
                description.includes(searchTerm);

            let matchesStatus = (selectedStatus === 'all');
            if (!matchesStatus) {
                if (selectedStatus === 'in_review' || selectedStatus === 'in progress') {
                    matchesStatus = (status === 'in_review' || status === 'in progress' || status === 'progress');
                } else {
                    matchesStatus = (status === selectedStatus);
                }
            }

            const matchesPriority = (selectedPriority === 'all') || (priority === selectedPriority);
            const matchesCategory = (selectedCategory === 'all') || (category === selectedCategory);

            if (matchesSearch && matchesStatus && matchesPriority && matchesCategory) {
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
            emptyIcon: 'report_off',
            emptyMessage: 'No matching complaints found',
            emptySubtext: 'Try adjusting your search criteria or resetting active filters.',
            colspan: 7
        });
    }

    if (searchInput)    searchInput.addEventListener('input', () => applyFilters(true));
    if (statusFilter)   statusFilter.addEventListener('change', () => applyFilters(true));
    if (priorityFilter) priorityFilter.addEventListener('change', () => applyFilters(true));
    if (categoryFilter) categoryFilter.addEventListener('change', () => applyFilters(true));

    // --------------------------------------------------------------------------
    // Detail Modal Bindings via AdminCore
    // --------------------------------------------------------------------------
    AdminCore.modal.bind(detailModal);
    window.closeDetailModal = () => AdminCore.modal.close(detailModal);

    function openDetailModal(complaintData) {
        if (!detailModal) return;

        const displayId = complaintData.displayId || `#CMP-${complaintData.id}`;
        document.getElementById('modalComplaintId').textContent = displayId;
        document.getElementById('modalHiddenId').value          = complaintData.id;

        document.getElementById('modalUserName').textContent      = complaintData.userName || 'Community Member';
        document.getElementById('modalUserRole').textContent      = complaintData.userRole || 'User';
        document.getElementById('modalUserOrg').textContent       = complaintData.orgName || 'SkillBridge Network';
        document.getElementById('modalUserEmail').textContent     = complaintData.userEmail || '';
        document.getElementById('modalSubmittedDate').textContent = complaintData.createdAt || 'Recent';

        const avatarEl = document.getElementById('modalUserAvatar');
        if (avatarEl) {
            avatarEl.textContent = getInitials(complaintData.userName || 'CM');
            avatarEl.className = 'cm-user-avatar ' + getAvatarClass(complaintData.userRole);
        }

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

        document.getElementById('modalComplaintTitle').textContent = complaintData.title || 'Complaint Details';
        document.getElementById('modalComplaintDesc').textContent  = complaintData.description || 'No description provided.';

        const statusUpper = (complaintData.status || '').toUpperCase();
        const isResolved  = (statusUpper === 'RESOLVED');

        const resolvedNotice  = document.getElementById('modalResolvedNotice');
        const statusGroup     = document.getElementById('modalStatusGroup');
        const notesField      = document.getElementById('modalResolutionNotes');
        const notesLabel      = document.getElementById('modalNotesLabel');
        const statusSelect    = document.getElementById('modalStatusSelect');
        const btnSubmitAction = document.getElementById('modalBtnSubmitAction');
        const notifHint       = document.getElementById('modalNotifHint');

        if (isResolved) {
            if (resolvedNotice)  resolvedNotice.style.display = 'flex';
            if (statusGroup)     statusGroup.style.display = 'none';
            if (notesLabel)      notesLabel.textContent = 'Recorded Resolution Findings';
            if (notesField) {
                notesField.value = complaintData.resolutionNotes || 'Case resolved and closed by Administrator.';
                notesField.setAttribute('readonly', 'readonly');
            }
            if (btnSubmitAction) btnSubmitAction.style.display = 'none';
            if (notifHint)       notifHint.innerHTML = '';
        } else {
            if (resolvedNotice)  resolvedNotice.style.display = 'none';
            if (statusGroup)     statusGroup.style.display = 'flex';
            if (notesLabel)      notesLabel.textContent = 'Investigation Findings / Resolution Notes';
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
                }
                statusSelect.value = currentVal;
                syncModalActionButton();
            }
        }

        AdminCore.modal.open(detailModal);
    }

    function syncModalActionButton() {
        const statusSelect    = document.getElementById('modalStatusSelect');
        const btnSubmitAction = document.getElementById('modalBtnSubmitAction');
        const notifHint       = document.getElementById('modalNotifHint');
        if (!statusSelect || !btnSubmitAction) return;

        const val = statusSelect.value;
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
            const modalNotesLabel      = document.getElementById('modalNotesLabel');
            const modalResolutionNotes  = document.getElementById('modalResolutionNotes');
            if (modalNotesLabel)     modalNotesLabel.innerHTML = 'Dismissal Reason <span style="color: #ef4444; font-weight: 700;">* (Mandatory)</span>';
            if (modalResolutionNotes) {
                modalResolutionNotes.placeholder = 'Please state clearly why this complaint is being dismissed...';
                modalResolutionNotes.style.borderColor = '#ef4444';
            }
            if (notifHint) {
                notifHint.innerHTML = `<span class="material-symbols-outlined" style="font-size: 15px; color: #ef4444;">error</span> A dismissal reason is mandatory for the complainant.`;
                notifHint.style.color = '#ef4444';
            }
        } else {
            btnSubmitAction.classList.add('cm-btn-warning');
            btnSubmitAction.innerHTML = `<span class="material-symbols-outlined">schedule</span> Keep as Pending`;
            const modalNotesLabel     = document.getElementById('modalNotesLabel');
            const modalResolutionNotes = document.getElementById('modalResolutionNotes');
            if (modalNotesLabel)      modalNotesLabel.innerHTML = 'Investigation Findings / Resolution Notes';
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

    const modalStatusSelect = document.getElementById('modalStatusSelect');
    if (modalStatusSelect) modalStatusSelect.addEventListener('change', syncModalActionButton);

    // --------------------------------------------------------------------------
    // Table Action Delegation
    // --------------------------------------------------------------------------
    if (tableBody) {
        tableBody.addEventListener('click', (e) => {
            const btn = e.target.closest('button');
            if (!btn) return;

            const row = btn.closest('tr.cm-data-row');
            if (!row) return;

            if (btn.classList.contains('cm-btn-view') || btn.getAttribute('data-action') === 'view') {
                openDetailModal(extractRowData(row));
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
    // Form Submit Handler
    // --------------------------------------------------------------------------
    const btnSubmitAction = document.getElementById('modalBtnSubmitAction');
    if (btnSubmitAction) btnSubmitAction.addEventListener('click', handleModalActionSubmit);

    if (detailForm) {
        detailForm.addEventListener('submit', (e) => {
            e.preventDefault();
            handleModalActionSubmit();
        });
    }

    function handleModalActionSubmit() {
        const complaintId  = document.getElementById('modalHiddenId').value;
        const statusSelect = document.getElementById('modalStatusSelect');
        const notesField   = document.getElementById('modalResolutionNotes');
        const btnSubmit    = document.getElementById('modalBtnSubmitAction');

        if (!complaintId || !statusSelect) return;

        const newStatus = statusSelect.value;
        const notes     = notesField ? notesField.value.trim() : '';

        if (newStatus === 'DISMISSED' && (!notes || notes.length < 5)) {
            AdminCore.toast('A dismissal reason is mandatory (at least 5 characters).', 'warning');
            if (notesField) {
                notesField.focus();
                notesField.style.borderColor = '#ef4444';
            }
            return;
        }

        const origContent = btnSubmit ? btnSubmit.innerHTML : '';
        if (btnSubmit) {
            btnSubmit.disabled = true;
            btnSubmit.innerHTML = `<span class="material-symbols-outlined" style="font-size: 18px; animation: spin 1s linear infinite;">progress_activity</span> Updating...`;
        }

        const formData = new FormData();
        formData.append('ajax_action', 'update_complaint_status');
        formData.append('complaint_id', complaintId);
        formData.append('status', newStatus);
        formData.append('notes', notes);

        fetch('complain.php', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(result => {
                if (btnSubmit) {
                    btnSubmit.disabled = false;
                    btnSubmit.innerHTML = origContent;
                }

                if (result.success) {
                    const targetRow = document.querySelector(`tr.cm-data-row[data-id="${complaintId}"]`);
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

                    updateKpiCounters(result.stats);
                    AdminCore.modal.close(detailModal);

                    let feedbackMsg = `Complaint #${complaintId} status updated to ${formatStatusLabel(newStatus)}.`;
                    if (newStatus === 'RESOLVED')   feedbackMsg = `Complaint #${complaintId} resolved & student notified via inbox.`;
                    if (newStatus === 'IN_REVIEW')  feedbackMsg = `Complaint #${complaintId} marked In Progress & student notified.`;
                    if (newStatus === 'DISMISSED')  feedbackMsg = `Complaint #${complaintId} dismissed with explanation.`;

                    AdminCore.toast(feedbackMsg, 'success');
                    applyFilters(false);
                } else {
                    AdminCore.toast(result.message || 'Failed to update complaint status.', 'error');
                }
            })
            .catch(err => {
                console.error('Network Error:', err);
                if (btnSubmit) {
                    btnSubmit.disabled = false;
                    btnSubmit.innerHTML = origContent;
                }
                AdminCore.toast('A network error occurred.', 'error');
            });
    }

    function updateKpiCounters(stats) {
        if (!stats) return;
        const totalEl   = document.getElementById('cmKpiTotal');
        const pendingEl = document.getElementById('cmKpiPending');
        const avgTimeEl = document.getElementById('cmKpiAvgTime');

        if (totalEl && stats.total !== undefined) totalEl.textContent = stats.total;
        if (pendingEl && stats.pending !== undefined) pendingEl.textContent = stats.pending;
        if (avgTimeEl && stats.avg_response_time) avgTimeEl.textContent = stats.avg_response_time;

        const sidebarBadge = document.getElementById('sidebarComplaintBadge');
        if (sidebarBadge && stats.pending !== undefined) {
            sidebarBadge.textContent = stats.pending;
            sidebarBadge.style.display = stats.pending > 0 ? '' : 'none';
        }
    }

    function getInitials(name) {
        if (!name) return 'SB';
        const parts = name.trim().split(/\s+/);
        return parts.length >= 2 ? (parts[0][0] + parts[1][0]).toUpperCase() : name.slice(0, 2).toUpperCase();
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

    // Initial load
    applyFilters(true);
}
