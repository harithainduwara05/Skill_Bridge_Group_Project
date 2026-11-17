// ── Modals & Actions ─────────────────────────────────────────────────────────
const addModal    = document.getElementById('addModal');
const viewModal   = document.getElementById('viewModal');
const editModal   = document.getElementById('editModal');
const deleteModal = document.getElementById('deleteModal');

const viewContent          = document.getElementById('viewModalContent');
const editStatusSelect     = document.getElementById('editStatus');
const inactiveStatusNotice = document.getElementById('inactiveStatusNotice');

let currentDeleteTarget = null;

// Modal Bindings via AdminCore
AdminCore.modal.bind(addModal, { trigger: 'btnAddUniversity', closeBtns: ['closeAddModal', 'cancelAddModal'] });
AdminCore.modal.bind(viewModal, { closeBtns: 'closeViewModal' });
AdminCore.modal.bind(editModal, { closeBtns: ['closeEditModal', 'cancelEditModal'] });
AdminCore.modal.bind(deleteModal, { closeBtns: ['closeDeleteModal', 'cancelDeleteModal'] });

// ── View Modal ────────────────────────────────────────────────────────────────
function openViewModal(name, faculty, domain, location, students, status) {
    const badgeCls = status === 'Active' ? 'active' : status === 'Hold' ? 'hold' : 'inactive';
    viewContent.innerHTML =
        detailRow('University',   AdminCore.escapeHtml(name)) +
        detailRow('Faculty',      AdminCore.escapeHtml(faculty)) +
        detailRow('Email Domain', '<span class="univ-domain-badge">@' + AdminCore.escapeHtml(domain) + '</span>') +
        detailRow('Location',     AdminCore.escapeHtml(location)) +
        detailRow('Students',     AdminCore.escapeHtml(students)) +
        detailRow('Status',       '<span class="badge-status ' + badgeCls + '">' + AdminCore.escapeHtml(status) + '</span>');
    AdminCore.modal.open(viewModal);
}

function detailRow(label, val) {
    return `<div class="univ-detail-row"><span class="univ-detail-label">${label}</span><span class="univ-detail-value">${val}</span></div>`;
}

// ── Edit Modal ────────────────────────────────────────────────────────────────
if (editStatusSelect && inactiveStatusNotice) {
    editStatusSelect.addEventListener('change', () => {
        inactiveStatusNotice.style.display = editStatusSelect.value.toLowerCase() === 'inactive' ? 'block' : 'none';
    });
}

function openEditModal(uni, faculty, domain, location, status) {
    if ((status || '').toLowerCase() === 'inactive') {
        AdminCore.toast('This university is currently Inactive and locked from modification.', 'warning');
        return;
    }

    document.getElementById('editOrigDomain').value = domain;
    document.getElementById('editUni').value        = uni;
    document.getElementById('editFac').value        = faculty;
    document.getElementById('editDomain').value     = domain;
    document.getElementById('editLocation').value   = location;

    if (editStatusSelect) {
        for (let i = 0; i < editStatusSelect.options.length; i++) {
            if (editStatusSelect.options[i].value.toLowerCase() === (status || '').toLowerCase()) {
                editStatusSelect.selectedIndex = i;
                break;
            }
        }
        if (inactiveStatusNotice) {
            inactiveStatusNotice.style.display = editStatusSelect.value.toLowerCase() === 'inactive' ? 'block' : 'none';
        }
    }

    AdminCore.modal.open(editModal);
}

// ── Delete Modal ─────────────────────────────────────────────────────────────
const editStatusInsteadBtn = document.getElementById('editStatusInsteadBtn');
if (editStatusInsteadBtn) {
    editStatusInsteadBtn.addEventListener('click', () => {
        AdminCore.modal.close(deleteModal);
        if (currentDeleteTarget && (currentDeleteTarget.status || '').toLowerCase() !== 'inactive') {
            openEditModal(
                currentDeleteTarget.name,
                currentDeleteTarget.faculty,
                currentDeleteTarget.domain,
                currentDeleteTarget.location,
                currentDeleteTarget.status
            );
        }
    });
}

function openDeleteModal(domain, name, stuCount = 0, faculty = '', location = '', status = 'Active') {
    const input                      = document.getElementById('deleteDomainInput');
    const uniDisplay                 = document.getElementById('deleteUniDisplay');
    const domainDisplay              = document.getElementById('deleteDomainDisplay');
    const deleteModalHeader          = document.getElementById('deleteModalHeader');
    const deleteAllowedWrap          = document.getElementById('deleteAllowedWrap');
    const deleteBlockedWrap          = document.getElementById('deleteBlockedWrap');
    const confirmDeleteBtn           = document.getElementById('confirmDeleteBtn');
    const deleteBlockedStuCount      = document.getElementById('deleteBlockedStuCount');
    const deleteBlockedUni           = document.getElementById('deleteBlockedUni');
    const deleteBlockedDomain        = document.getElementById('deleteBlockedDomain');
    const deleteModalRecommendation  = document.getElementById('deleteModalRecommendation');

    currentDeleteTarget = { domain, name, faculty, location, status, stuCount };

    if (input)         input.value = domain;
    if (uniDisplay)    uniDisplay.innerText = name || 'University';
    if (domainDisplay) domainDisplay.innerText = '@' + domain;

    const s = String(status || '').trim().toLowerCase();
    const isInactive = (s === 'inactive' || s === 'deactive' || s === 'disabled');

    if (stuCount > 0) {
        if (deleteAllowedWrap) deleteAllowedWrap.style.display = 'none';
        if (deleteBlockedWrap) deleteBlockedWrap.style.display = 'block';
        if (confirmDeleteBtn)  confirmDeleteBtn.style.display = 'none';

        if (editStatusInsteadBtn) {
            editStatusInsteadBtn.style.display = isInactive ? 'none' : 'inline-flex';
        }

        if (deleteModalRecommendation) {
            deleteModalRecommendation.innerHTML = isInactive
                ? `<span class="material-symbols-outlined" style="font-size:14px; vertical-align:middle; color:#d97706;">lock</span> Status: Institution is <strong>Inactive</strong> and locked while student accounts exist.`
                : `<span class="material-symbols-outlined" style="font-size:14px; vertical-align:middle; color:#d97706;">lightbulb</span> Recommended: Change status to <strong>"Hold"</strong> or <strong>"Inactive"</strong> instead of deleting.`;
        }

        if (deleteBlockedStuCount) deleteBlockedStuCount.innerText = `${stuCount} ${stuCount === 1 ? 'active student' : 'active students'}`;
        if (deleteBlockedUni)      deleteBlockedUni.innerText = name || 'University';
        if (deleteBlockedDomain)   deleteBlockedDomain.innerText = '@' + domain;

        if (deleteModalHeader) {
            deleteModalHeader.innerHTML = `
                <h3 style="color:#b45309; display:flex; align-items:center; gap:8px;">
                    <span class="material-symbols-outlined" style="font-size:22px; color:#d97706;">gpp_maybe</span>
                    <span>Cannot Delete Institution</span>
                </h3>
                <button class="univ-modal-close" type="button" onclick="AdminCore.modal.close('deleteModal')">
                    <span class="material-symbols-outlined">close</span>
                </button>
            `;
        }
    } else {
        if (deleteAllowedWrap) deleteAllowedWrap.style.display = 'block';
        if (deleteBlockedWrap) deleteBlockedWrap.style.display = 'none';
        if (confirmDeleteBtn)  confirmDeleteBtn.style.display = 'inline-flex';
        if (editStatusInsteadBtn) editStatusInsteadBtn.style.display = 'none';

        if (deleteModalHeader) {
            deleteModalHeader.innerHTML = `
                <h3 style="color:#dc2626; display:flex; align-items:center; gap:8px;">
                    <span class="material-symbols-outlined" style="font-size:20px;">delete</span>
                    <span>Delete University</span>
                </h3>
                <button class="univ-modal-close" type="button" onclick="AdminCore.modal.close('deleteModal')">
                    <span class="material-symbols-outlined">close</span>
                </button>
            `;
        }
    }

    AdminCore.modal.open(deleteModal);
}

function confirmDelete(domain, name, stuCount = 0) {
    openDeleteModal(domain, name, stuCount);
}

// ── Live Search & CSV Export ──────────────────────────────────────────────────
const univSearch = document.getElementById('univSearchInput');
if (univSearch) {
    univSearch.addEventListener('input', function () {
        const q = this.value.toLowerCase();
        document.querySelectorAll('#univTable tbody tr').forEach(r => {
            r.style.display = r.textContent.toLowerCase().includes(q) ? '' : 'none';
        });
    });
}

const exportBtn = document.getElementById('exportBtn');
if (exportBtn) {
    exportBtn.addEventListener('click', function () {
        const table = document.getElementById('univTable');
        if (!table) return;
        const rows = [];
        const heads = [...table.querySelectorAll('thead th')].map(th => `"${th.textContent.trim()}"`);
        rows.push(heads.join(','));
        table.querySelectorAll('tbody tr').forEach(row => {
            const cells = [...row.querySelectorAll('td')];
            if (cells.length < 2) return;
            rows.push(cells.map(td => `"${td.textContent.trim().replace(/\s+/g, ' ').replace(/"/g, '""')}"`).join(','));
        });
        const blob = new Blob([rows.join('\n')], { type: 'text/csv' });
        const url  = URL.createObjectURL(blob);
        const a    = Object.assign(document.createElement('a'), { href: url, download: 'universities.csv' });
        a.click();
        URL.revokeObjectURL(url);
    });
}

// ── Searchable Combobox Dropdowns ─────────────────────────────────────────────
let universitiesDataset = [];

function initUniversityComboboxes() {
    const dataPromise = (typeof fetchSriLankanUniversities === 'function')
        ? fetchSriLankanUniversities()
        : Promise.resolve(typeof sriLankaUniversities !== 'undefined' ? sriLankaUniversities : []);

    dataPromise.then(data => {
        universitiesDataset = Array.isArray(data) ? data : [];

        setupCombobox({
            comboboxId: 'addUnivCombobox', inputId: 'addUnivInput', toggleId: 'addUnivToggle',
            dropdownId: 'addUnivDropdown', listId: 'addUnivList',
            domainInputId: 'addDomainInput', locationInputId: 'addLocationInput'
        });

        setupCombobox({
            comboboxId: 'editUnivCombobox', inputId: 'editUni', toggleId: 'editUnivToggle',
            dropdownId: 'editUnivDropdown', listId: 'editUnivList'
        });
    });
}

function setupCombobox(config) {
    const combobox = document.getElementById(config.comboboxId);
    const input    = document.getElementById(config.inputId);
    const toggle   = document.getElementById(config.toggleId);
    const dropdown = document.getElementById(config.dropdownId);
    const list     = document.getElementById(config.listId);

    if (!combobox || !input || !toggle || !dropdown || !list) return;

    let highlightedIndex = -1;

    function renderList(query = '') {
        list.innerHTML = '';
        highlightedIndex = -1;
        const q = (query || '').trim().toLowerCase();

        const filtered = universitiesDataset.filter(u => {
            return (u.name && u.name.toLowerCase().includes(q)) || (u.domain && u.domain.toLowerCase().includes(q));
        });

        let exactMatch = false;

        filtered.forEach(u => {
            if (u.name.toLowerCase() === q) exactMatch = true;

            const item = document.createElement('div');
            item.className = 'univ-combobox-item' + (input.value.trim().toLowerCase() === u.name.toLowerCase() ? ' selected' : '');
            item.innerHTML = `<span>${AdminCore.escapeHtml(u.name)}</span>${u.domain ? `<span class="univ-combobox-domain">@${AdminCore.escapeHtml(u.domain)}</span>` : ''}`;

            item.addEventListener('mousedown', (e) => {
                e.preventDefault();
                selectItem(u);
            });
            list.appendChild(item);
        });

        if (q !== '' && !exactMatch) {
            const customItem = document.createElement('div');
            customItem.className = 'univ-combobox-item custom-entry';
            customItem.innerHTML = `
                <div style="display:flex; align-items:center; gap:6px;">
                    <span class="material-symbols-outlined" style="font-size:16px;">edit_note</span>
                    <span>Use: "<strong>${AdminCore.escapeHtml(query.trim())}</strong>"</span>
                </div>
                <span class="univ-combobox-badge-custom">Manual</span>
            `;
            customItem.addEventListener('mousedown', (e) => {
                e.preventDefault();
                input.value = query.trim();
                closeDropdown();
            });
            list.appendChild(customItem);
        }

        if (filtered.length === 0 && q === '') {
            const empty = document.createElement('div');
            empty.className = 'univ-combobox-empty';
            empty.textContent = 'No universities found.';
            list.appendChild(empty);
        }
    }

    function selectItem(u) {
        input.value = u.name;
        closeDropdown();

        if (config.domainInputId) {
            const domEl = document.getElementById(config.domainInputId);
            if (domEl && (!domEl.value.trim() || domEl.dataset.autofilled === 'true')) {
                domEl.value = u.domain || '';
                domEl.dataset.autofilled = 'true';
            }
        }
        if (config.locationInputId) {
            const locEl = document.getElementById(config.locationInputId);
            if (locEl && (!locEl.value.trim() || locEl.dataset.autofilled === 'true')) {
                locEl.value = u.location || '';
                locEl.dataset.autofilled = 'true';
            }
        }
    }

    function openDropdown()  { renderList(input.value); dropdown.classList.add('open'); toggle.classList.add('open'); }
    function closeDropdown() { dropdown.classList.remove('open'); toggle.classList.remove('open'); highlightedIndex = -1; }

    toggle.addEventListener('click', (e) => {
        e.preventDefault();
        e.stopPropagation();
        dropdown.classList.contains('open') ? closeDropdown() : (openDropdown(), input.focus());
    });

    input.addEventListener('focus', openDropdown);
    input.addEventListener('input', openDropdown);

    document.addEventListener('mousedown', (e) => {
        if (!combobox.contains(e.target)) closeDropdown();
    });
}

// Track manual typing so autofill doesn't overwrite
['addDomainInput', 'addLocationInput'].forEach(id => {
    const el = document.getElementById(id);
    if (el) el.addEventListener('input', () => { el.dataset.autofilled = 'false'; });
});

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initUniversityComboboxes);
} else {
    initUniversityComboboxes();
}


// ── Instant Frontend Table Filter & Real-Time Search Engine ─────────────────
(function() {
    const tableBody = document.querySelector('#univTable tbody');
    const searchInput = document.getElementById('univSearchInput');
    const statusFilter = document.getElementById('univStatusFilter');
    const paginationInfo = document.getElementById('univPaginationInfo');
    const paginationControls = document.getElementById('univPaginationControls');

    if (!tableBody) return;

    let currentPage = 1;
    const PAGE_SIZE = 5;

    function applyUniFilters(resetPage = true) {
        if (resetPage) currentPage = 1;

        const q = (searchInput ? searchInput.value : '').toLowerCase().trim();
        const selectedStatus = (statusFilter ? statusFilter.value : 'all').toLowerCase();

        const rows = Array.from(tableBody.querySelectorAll('tr.univ-row'));
        const matched = [];

        rows.forEach(row => {
            const status = (row.getAttribute('data-status') || '').toLowerCase();
            const searchBlob = (row.getAttribute('data-search') || '').toLowerCase();

            const matchStatus = (selectedStatus === 'all' || status === selectedStatus);
            const matchSearch = !q || searchBlob.includes(q);

            if (matchStatus && matchSearch) {
                matched.push(row);
            }
        });

        // Hide static PHP empty state if present
        const staticEmpty = tableBody.querySelector('.table-empty-state');
        if (staticEmpty) {
            staticEmpty.closest('tr').style.display = 'none';
        }

        // Delegate pagination to AdminCore
        AdminCore.paginate({
            tableBody,
            rows: matched,
            pageSize: PAGE_SIZE,
            currentPage,
            infoEl: paginationInfo,
            controlsEl: paginationControls,
            onPageChange: (newPage) => {
                currentPage = newPage;
                applyUniFilters(false);
            },
            emptyIcon: 'school',
            emptyMessage: 'No institutions found matching your criteria',
            emptySubtext: 'Try adjusting your search query or selecting a different status.',
            colspan: 7
        });
    }

    // Real-time Search Input typing
    if (searchInput) {
        searchInput.addEventListener('input', () => applyUniFilters(true));
    }

    // Status Dropdown change
    if (statusFilter) {
        statusFilter.addEventListener('change', () => applyUniFilters(true));
    }

    // Initial page load filter run
    applyUniFilters(true);
})();
