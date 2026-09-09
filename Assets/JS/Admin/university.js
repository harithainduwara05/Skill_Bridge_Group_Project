// ── Flash Toast Auto-dismiss ──────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    const toast = document.getElementById('flashToast');
    if (toast) {
        setTimeout(() => {
            toast.style.transition = 'opacity .4s ease, transform .4s ease';
            toast.style.opacity    = '0';
            toast.style.transform  = 'translateX(120%)';
            setTimeout(() => toast.remove(), 400);
        }, 4000); // 4 seconds කට පස්සේ ඉබේ disappear වෙනවා
    }
});

// ── Add Modal ─────────────────────────────────────────────────────────────────
const addModal = document.getElementById('addModal');
document.getElementById('btnAddUniversity').addEventListener('click',  () => addModal.classList.add('open'));
document.getElementById('closeAddModal').addEventListener('click',     () => addModal.classList.remove('open'));
document.getElementById('cancelAddModal').addEventListener('click',    () => addModal.classList.remove('open'));
addModal.addEventListener('click', e => { if (e.target === addModal) addModal.classList.remove('open'); });

// ── View Modal ────────────────────────────────────────────────────────────────
const viewModal   = document.getElementById('viewModal');
const viewContent = document.getElementById('viewModalContent');

function openViewModal(name, faculty, domain, location, students, status) {
    const badgeCls = status === 'Active' ? 'active' : status === 'Pending' ? 'pending' : 'inactive-badge';
    viewContent.innerHTML =
        detailRow('University',   escHtml(name)) +
        detailRow('Faculty',      escHtml(faculty)) +
        detailRow('Email Domain', '<span class="univ-domain-badge">@' + escHtml(domain) + '</span>') +
        detailRow('Location',     escHtml(location)) +
        detailRow('Students',     escHtml(students)) +
        detailRow('Status',       '<span class="badge-status ' + badgeCls + '">' + escHtml(status) + '</span>');
    viewModal.classList.add('open');
}

function detailRow(label, val) {
    return '<div class="univ-detail-row">' +
           '<span class="univ-detail-label">' + label + '</span>' +
           '<span class="univ-detail-value">'  + val   + '</span>' +
           '</div>';
}

document.getElementById('closeViewModal').addEventListener('click', () => viewModal.classList.remove('open'));
viewModal.addEventListener('click', e => { if (e.target === viewModal) viewModal.classList.remove('open'); });

function escHtml(str) {
    const d = document.createElement('div');
    d.appendChild(document.createTextNode(str || '—'));
    return d.innerHTML;
}

// ── Client-side live search ───────────────────────────────────────────────────
document.getElementById('univSearchInput').addEventListener('input', function () {
    const q    = this.value.toLowerCase();
    const rows = document.querySelectorAll('#univTable tbody tr');
    rows.forEach(r => {
        r.style.display = r.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
});

// ── Export CSV ────────────────────────────────────────────────────────────────
document.getElementById('exportBtn').addEventListener('click', function () {
    const table = document.getElementById('univTable');
    const rows  = [];
    const heads = [...table.querySelectorAll('thead th')].map(th => '"' + th.textContent.trim() + '"');
    rows.push(heads.join(','));
    table.querySelectorAll('tbody tr').forEach(row => {
        const cells = [...row.querySelectorAll('td')];
        if (cells.length < 2) return;
        rows.push(cells.map(td => '"' + td.textContent.trim().replace(/\s+/g, ' ').replace(/"/g, '""') + '"').join(','));
    });
    const blob = new Blob([rows.join('\n')], { type: 'text/csv' });
    const url  = URL.createObjectURL(blob);
    const a    = Object.assign(document.createElement('a'), { href: url, download: 'universities.csv' });
    a.click();
    URL.revokeObjectURL(url);
});

// ── Edit Modal ────────────────────────────────────────────────────────────────
const editModal = document.getElementById('editModal');
if (editModal) {
    document.getElementById('closeEditModal').addEventListener('click',  () => editModal.classList.remove('open'));
    document.getElementById('cancelEditModal').addEventListener('click', () => editModal.classList.remove('open'));
    editModal.addEventListener('click', e => { if (e.target === editModal) editModal.classList.remove('open'); });
}

function openEditModal(uni, faculty, domain, location, status) {
    document.getElementById('editOrigDomain').value = domain;
    document.getElementById('editUni').value = uni;
    document.getElementById('editFac').value = faculty;
    document.getElementById('editDomain').value = domain;
    document.getElementById('editLocation').value = location;
    
    const statusSelect = document.getElementById('editStatus');
    if (statusSelect) {
        for (let i = 0; i < statusSelect.options.length; i++) {
            if (statusSelect.options[i].value.toLowerCase() === (status || '').toLowerCase()) {
                statusSelect.selectedIndex = i;
                break;
            }
        }
    }
    
    if (editModal) editModal.classList.add('open');
}

// ── Delete Modal ─────────────────────────────────────────────────────────────
const deleteModal = document.getElementById('deleteModal');
if (deleteModal) {
    const closeDel = document.getElementById('closeDeleteModal');
    const cancelDel = document.getElementById('cancelDeleteModal');
    if (closeDel) closeDel.addEventListener('click', () => deleteModal.classList.remove('open'));
    if (cancelDel) cancelDel.addEventListener('click', () => deleteModal.classList.remove('open'));
    deleteModal.addEventListener('click', e => { if (e.target === deleteModal) deleteModal.classList.remove('open'); });
}

function openDeleteModal(domain, name) {
    const input = document.getElementById('deleteDomainInput');
    const uniDisplay = document.getElementById('deleteUniDisplay');
    const domainDisplay = document.getElementById('deleteDomainDisplay');
    
    if (input) input.value = domain;
    if (uniDisplay) uniDisplay.innerText = name || 'University';
    if (domainDisplay) domainDisplay.innerText = '@' + domain;
    
    if (deleteModal) deleteModal.classList.add('open');
}

function confirmDelete(domain, name) {
    openDeleteModal(domain, name);
}

// ── University Searchable Combobox Dropdown ──────────────────────────────────
let universitiesDataset = [];

function initUniversityComboboxes() {
    const dataPromise = (typeof fetchSriLankanUniversities === 'function')
        ? fetchSriLankanUniversities()
        : Promise.resolve(typeof sriLankaUniversities !== 'undefined' ? sriLankaUniversities : []);

    dataPromise.then(data => {
        universitiesDataset = Array.isArray(data) ? data : [];

        setupCombobox({
            comboboxId: 'addUnivCombobox',
            inputId: 'addUnivInput',
            toggleId: 'addUnivToggle',
            dropdownId: 'addUnivDropdown',
            listId: 'addUnivList',
            domainInputId: 'addDomainInput',
            locationInputId: 'addLocationInput'
        });

        setupCombobox({
            comboboxId: 'editUnivCombobox',
            inputId: 'editUni',
            toggleId: 'editUnivToggle',
            dropdownId: 'editUnivDropdown',
            listId: 'editUnivList'
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
            const nameMatch = u.name && u.name.toLowerCase().includes(q);
            const domainMatch = u.domain && u.domain.toLowerCase().includes(q);
            return nameMatch || domainMatch;
        });

        let exactMatch = false;

        filtered.forEach(u => {
            if (u.name.toLowerCase() === q) exactMatch = true;

            const item = document.createElement('div');
            item.className = 'univ-combobox-item';
            if (input.value.trim().toLowerCase() === u.name.toLowerCase()) {
                item.classList.add('selected');
            }

            const nameEl = document.createElement('span');
            nameEl.textContent = u.name;

            item.appendChild(nameEl);

            if (u.domain) {
                const domEl = document.createElement('span');
                domEl.className = 'univ-combobox-domain';
                domEl.textContent = '@' + u.domain;
                item.appendChild(domEl);
            }

            item.addEventListener('mousedown', (e) => {
                e.preventDefault();
                selectItem(u);
            });

            list.appendChild(item);
        });

        // If query is not empty and doesn't exactly match an existing item,
        // show manual entry option so the user can easily confirm their custom name
        if (q !== '' && !exactMatch) {
            const customItem = document.createElement('div');
            customItem.className = 'univ-combobox-item custom-entry';
            customItem.innerHTML = `
                <div style="display:flex; align-items:center; gap:6px; overflow:hidden;">
                    <span class="material-symbols-outlined" style="font-size:16px;">edit_note</span>
                    <span style="white-space:nowrap; text-overflow:ellipsis; overflow:hidden;">Use: "<strong>${escHtml(query.trim())}</strong>"</span>
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

        // Prefill domain & location if in Add modal and not already manually filled
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

    function openDropdown() {
        renderList(input.value);
        dropdown.classList.add('open');
        toggle.classList.add('open');
    }

    function closeDropdown() {
        dropdown.classList.remove('open');
        toggle.classList.remove('open');
        highlightedIndex = -1;
    }

    toggle.addEventListener('click', (e) => {
        e.preventDefault();
        e.stopPropagation();
        if (dropdown.classList.contains('open')) {
            closeDropdown();
        } else {
            openDropdown();
            input.focus();
        }
    });

    input.addEventListener('focus', () => {
        openDropdown();
    });

    input.addEventListener('input', () => {
        openDropdown();
    });

    // Keyboard navigation
    input.addEventListener('keydown', (e) => {
        const items = list.querySelectorAll('.univ-combobox-item');
        if (!items.length) return;

        if (e.key === 'ArrowDown') {
            e.preventDefault();
            if (!dropdown.classList.contains('open')) {
                openDropdown();
                return;
            }
            highlightedIndex = (highlightedIndex + 1) % items.length;
            updateHighlight(items);
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            if (!dropdown.classList.contains('open')) {
                openDropdown();
                return;
            }
            highlightedIndex = (highlightedIndex - 1 + items.length) % items.length;
            updateHighlight(items);
        } else if (e.key === 'Enter') {
            if (dropdown.classList.contains('open') && highlightedIndex >= 0 && items[highlightedIndex]) {
                e.preventDefault();
                items[highlightedIndex].dispatchEvent(new MouseEvent('mousedown'));
            }
        } else if (e.key === 'Escape') {
            closeDropdown();
        }
    });

    function updateHighlight(items) {
        items.forEach((it, idx) => {
            if (idx === highlightedIndex) {
                it.classList.add('highlighted');
                it.scrollIntoView({ block: 'nearest' });
            } else {
                it.classList.remove('highlighted');
            }
        });
    }

    // Close when clicking outside
    document.addEventListener('mousedown', (e) => {
        if (!combobox.contains(e.target)) {
            closeDropdown();
        }
    });
}

// Track manual typing on domain and location so autofill doesn't overwrite
['addDomainInput', 'addLocationInput'].forEach(id => {
    const el = document.getElementById(id);
    if (el) {
        el.addEventListener('input', () => {
            el.dataset.autofilled = 'false';
        });
    }
});

// Initialize on DOM load
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initUniversityComboboxes);
} else {
    initUniversityComboboxes();
}
