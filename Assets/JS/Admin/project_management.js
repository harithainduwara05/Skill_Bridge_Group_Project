/**
 * ==============================================================================
 * Project Management Module Logic - SkillBridge Admin Dashboard
 * Handles real-time search, multi-criteria filtering, modal management,
 * and interactive UI behaviors.
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
    const detailsModal = document.getElementById('pmDetailsModal');
    const addProjectForm = document.getElementById('pmAddProjectForm');
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

    // Close modal on backdrop click
    if (addProjectModal) {
        addProjectModal.addEventListener('click', (e) => {
            if (e.target === addProjectModal) {
                window.closeAddProjectModal();
            }
        });
    }

    // Handle Add Project Form Submission (Frontend UI Mock)
    if (addProjectForm) {
        addProjectForm.addEventListener('submit', (e) => {
            e.preventDefault();

            const title = document.getElementById('pmInputTitle').value.trim();
            const category = document.getElementById('pmInputCategory').value.trim();
            const org = document.getElementById('pmInputOrg').value.trim();
            const skillsStr = document.getElementById('pmInputSkills').value.trim();
            const status = document.getElementById('pmInputStatus').value;

            if (!title || !org) {
                alert('Please fill in required fields.');
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

            // Status Badge styling (Active, Close, Review, Rejected)
            let statusPill = '';
            if (status === 'Active') {
                statusPill = `<span class="pm-status-pill status-active">Active</span>`;
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
                    <button class="pm-btn-icon" title="View Details" onclick="viewProjectDetails('${newId}')">
                        <span class="material-symbols-outlined">visibility</span>
                    </button>
                    <button class="pm-btn-icon pm-btn-icon-reject" title="Reject Project" onclick="openRejectReasonModal('${newId}')">
                        <span class="material-symbols-outlined">block</span>
                    </button>
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

        document.getElementById('pmDetailId').textContent = projectId;
        document.getElementById('pmDetailTitle').textContent = title;
        document.getElementById('pmDetailCategory').textContent = category;
        document.getElementById('pmDetailOrg').textContent = org;
        document.getElementById('pmDetailSkills').textContent = skills;

        const statusEl = document.getElementById('pmDetailStatus');
        if (statusEl) {
            statusEl.textContent = status;
            statusEl.className = 'pm-status-pill';
            if (status === 'Active') statusEl.classList.add('status-active');
            else if (status === 'Close') statusEl.classList.add('status-close');
            else if (status === 'Review') statusEl.classList.add('status-review');
            else if (status === 'Rejected') statusEl.classList.add('status-rejected');
        }

        // Configure Reject Button state for admin
        const rejectBtn = document.getElementById('pmBtnRejectProject');
        if (rejectBtn) {
            if (status === 'Rejected') {
                rejectBtn.disabled = true;
                rejectBtn.innerHTML = '<span class="material-symbols-outlined" style="font-size:18px;">block</span><span>Already Rejected</span>';
            } else {
                rejectBtn.disabled = false;
                rejectBtn.innerHTML = '<span class="material-symbols-outlined" style="font-size:18px;">block</span><span>Reject</span>';
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
    // Reject Project Reason Modal Handlers
    // --------------------------------------------------------------------------
    const rejectModal = document.getElementById('pmRejectModal');
    const rejectForm = document.getElementById('pmRejectProjectForm');

    window.openRejectReasonModal = function(optionalProjectId) {
        let id = '';
        let title = '';

        if (optionalProjectId) {
            const row = document.querySelector(`tr[data-id="${optionalProjectId}"]`);
            if (row) {
                const currentStatus = (row.getAttribute('data-status') || '').toLowerCase();
                if (currentStatus === 'rejected') {
                    showToast(`Project ${optionalProjectId} is already rejected.`);
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
                alert('Please enter a rejection reason.');
                return;
            }

            const row = document.querySelector(`tr[data-id="${id}"]`);
            if (row) {
                row.setAttribute('data-status', 'Rejected');
                row.setAttribute('data-reject-reason', reason);

                const statusCell = row.cells[5];
                if (statusCell) {
                    statusCell.innerHTML = `<span class="pm-status-pill status-rejected">Rejected</span>`;
                }

                // Disable row reject button
                const rowRejectBtn = row.querySelector('.pm-btn-icon-reject');
                if (rowRejectBtn) {
                    rowRejectBtn.disabled = true;
                    rowRejectBtn.title = 'Already Rejected';
                }

                // Update detail modal badge if open
                const statusEl = document.getElementById('pmDetailStatus');
                if (statusEl) {
                    statusEl.textContent = 'Rejected';
                    statusEl.className = 'pm-status-pill status-rejected';
                }

                const rejectBtn = document.getElementById('pmBtnRejectProject');
                if (rejectBtn) {
                    rejectBtn.disabled = true;
                    rejectBtn.innerHTML = '<span class="material-symbols-outlined" style="font-size:18px;">block</span><span>Already Rejected</span>';
                }

                window.closeRejectReasonModal();
                showToast(`Project ${id} has been rejected. Reason logged.`);
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
