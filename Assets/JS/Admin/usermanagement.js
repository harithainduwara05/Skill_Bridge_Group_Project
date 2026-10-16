

// Modal Aliases via AdminCore
window.openModal  = (id) => AdminCore.modal.open(id);
window.closeModal = (id) => AdminCore.modal.close(id);

// Add User and Bulk Upload Trigger Buttons
document.getElementById('btnAddUser')?.addEventListener('click', () => openModal('addUserModal'));
document.getElementById('btnBulkUpload')?.addEventListener('click', () => openModal('bulkUploadModal'));

// Close modals on overlay backdrop click
document.querySelectorAll('.user-modal-overlay').forEach(overlay => {
    overlay.addEventListener('click', function(e) {
        if (e.target === this) AdminCore.modal.close(this);
    });
});

// Universities and Faculties dataset
const dbUniversities     = window.dbUniversities || {};
const activeInstitutions = window.activeInstitutions || {};

// ── Bulk Import Modal: University -> Faculty -> Domain ───────────────────────
const bulkUniSelect  = document.getElementById('bulkUniSelect');
const bulkFacSelect  = document.getElementById('bulkFacSelect');
const bulkDomainBox  = document.getElementById('bulkDomainBox');
const bulkDomainIcon = document.getElementById('bulkDomainIcon');
const bulkDomainText = document.getElementById('bulkDomainText');

function updateBulkDomainBadge(domain) {
    if (!bulkDomainBox || !bulkDomainIcon || !bulkDomainText) return;
    if (domain) {
        bulkDomainBox.style.background = '#f0fdf4';
        bulkDomainBox.style.borderColor = '#86efac';
        bulkDomainIcon.style.color = '#16a34a';
        bulkDomainIcon.textContent = 'verified';
        bulkDomainText.innerHTML = `
            <div style="font-weight:600; color:#15803d; font-size:12.5px; display:flex; align-items:center; gap:6px;">
                Authorized Email Domain: <code style="background:#dcfce7; color:#15803d; padding:2px 8px; border-radius:6px; font-family:monospace; font-weight:700; font-size:13px;">@${domain}</code>
            </div>
            <div style="font-size:11.5px; color:#166534; margin-top:2px;">
                Only student emails ending with <strong>@${domain}</strong> will be accepted in this batch.
            </div>
        `;
    } else {
        bulkDomainBox.style.background = '#f8fafc';
        bulkDomainBox.style.borderColor = '#e2e8f0';
        bulkDomainIcon.style.color = '#64748b';
        bulkDomainIcon.textContent = 'domain_verification';
        bulkDomainText.innerHTML = `<span style="color:#64748b; font-size:12px;">Please select a University and Faculty above to view permitted domain.</span>`;
    }
}

if (bulkUniSelect && bulkFacSelect) {
    bulkUniSelect.addEventListener('change', function() {
        const u = this.value;
        bulkFacSelect.innerHTML = '<option value="">-- Choose Faculty --</option>';
        if (u && activeInstitutions[u]) {
            bulkFacSelect.disabled = false;
            bulkFacSelect.style.cursor = 'pointer';
            bulkFacSelect.style.background = '#ffffff';
            const facs = Object.keys(activeInstitutions[u]);
            facs.forEach(f => {
                const opt = document.createElement('option');
                opt.value = f;
                opt.textContent = f;
                bulkFacSelect.appendChild(opt);
            });
            facs.length === 1 ? (bulkFacSelect.value = facs[0], updateBulkDomainBadge(activeInstitutions[u][facs[0]])) : updateBulkDomainBadge(null);
        } else {
            bulkFacSelect.disabled = true;
            bulkFacSelect.style.cursor = 'not-allowed';
            bulkFacSelect.style.background = '#f8fafc';
            updateBulkDomainBadge(null);
        }
    });

    bulkFacSelect.addEventListener('change', function() {
        const u = bulkUniSelect.value;
        const f = this.value;
        updateBulkDomainBadge((u && f && activeInstitutions[u] && activeInstitutions[u][f]) ? activeInstitutions[u][f] : null);
    });
}

// ── Searchable Combobox Component ────────────────────────────────────────────
function setupSearchableCombobox(config) {
    const combobox = document.getElementById(config.comboboxId);
    const input    = document.getElementById(config.inputId);
    const toggle   = document.getElementById(config.toggleId);
    const dropdown = document.getElementById(config.dropdownId);
    const list     = document.getElementById(config.listId);

    if (!combobox || !input || !toggle || !dropdown || !list) return null;

    let highlightedIndex = -1;

    function renderList(query = '') {
        list.innerHTML = '';
        highlightedIndex = -1;
        const q = (query || '').trim().toLowerCase();
        const dataset = typeof config.getItems === 'function' ? config.getItems() : [];

        const filtered = dataset.filter(itemText => itemText && itemText.toLowerCase().includes(q));
        let exactMatch = false;

        filtered.forEach(itemText => {
            if (itemText.toLowerCase() === q) exactMatch = true;

            const item = document.createElement('div');
            item.className = 'univ-combobox-item' + (input.value.trim().toLowerCase() === itemText.toLowerCase() ? ' selected' : '');
            item.innerHTML = `<span>${AdminCore.escapeHtml(itemText)}</span>`;

            item.addEventListener('mousedown', (e) => {
                e.preventDefault();
                input.value = itemText;
                closeDropdown();
                if (typeof config.onSelect === 'function') config.onSelect(itemText);
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
            empty.textContent = config.emptyText || 'No options found.';
            list.appendChild(empty);
        }
    }

    function openDropdown()  { renderList(input.value); dropdown.classList.add('open'); toggle.classList.add('open'); }
    function closeDropdown() { dropdown.classList.remove('open'); toggle.classList.remove('open'); highlightedIndex = -1; }

    toggle.addEventListener('click', (e) => {
        e.preventDefault();
        dropdown.classList.contains('open') ? closeDropdown() : (openDropdown(), input.focus());
    });

    input.addEventListener('focus', openDropdown);
    input.addEventListener('input', () => {
        openDropdown();
        if (typeof config.onInput === 'function') config.onInput(input.value);
    });

    document.addEventListener('mousedown', (e) => {
        if (!combobox.contains(e.target)) closeDropdown();
    });

    return {
        open: openDropdown,
        close: closeDropdown,
        refresh: () => { if (dropdown.classList.contains('open')) renderList(input.value); }
    };
}

// Initialize Comboboxes
const facCombobox = setupSearchableCombobox({
    comboboxId: 'addUserFacCombobox', inputId: 'addUserFacInput', toggleId: 'addUserFacToggle',
    dropdownId: 'addUserFacDropdown', listId: 'addUserFacList', emptyText: 'No faculties found.',
    getItems: () => {
        const currentUni = (document.getElementById('addUserUnivInput')?.value || '').trim().toLowerCase();
        if (currentUni) {
            for (const [uName, fList] of Object.entries(dbUniversities)) {
                if (uName.toLowerCase() === currentUni) return fList;
            }
        }
        const allFacs = new Set();
        Object.values(dbUniversities).forEach(facList => facList.forEach(f => allFacs.add(f)));
        return Array.from(allFacs);
    }
});

const univCombobox = setupSearchableCombobox({
    comboboxId: 'addUserUnivCombobox', inputId: 'addUserUnivInput', toggleId: 'addUserUnivToggle',
    dropdownId: 'addUserUnivDropdown', listId: 'addUserUnivList', emptyText: 'No universities found.',
    getItems: () => Object.keys(dbUniversities),
    onSelect: () => facCombobox?.refresh(),
    onInput:  () => facCombobox?.refresh()
});

// ── Role-specific fields in Add Modal ────────────────────────────────────────
function toggleAddFields(role) {
    const studentUniFac = document.getElementById('addStudentUniFacultyGroup');
    const orgGroup      = document.getElementById('addOrgNameGroup');
    const orgLabel      = document.getElementById('addOrgLabel');
    const studentExtra  = document.getElementById('addStudentExtraFields');
    const contactGroup  = document.getElementById('addContactGroup');
    const univInput     = document.getElementById('addUserUnivInput');

    if (role === 'student') {
        studentUniFac.style.display = 'block';
        orgGroup.style.display = 'none';
        studentExtra.style.display = 'grid';
        contactGroup.style.display = 'none';
        if (univInput) univInput.required = true;
    } else {
        studentUniFac.style.display = 'none';
        orgGroup.style.display = 'block';
        studentExtra.style.display = 'none';
        contactGroup.style.display = role === 'admin' ? 'none' : 'block';
        if (orgLabel) orgLabel.innerText = role === 'company' ? 'Company Name' : role === 'organization' ? 'Organization Name' : 'Department / System';
        if (univInput) univInput.required = false;
    }
}

// Add User Form Validation
const addUserForm = document.querySelector('#addUserModal form');
if (addUserForm) {
    addUserForm.addEventListener('submit', function(e) {
        if (document.getElementById('addRoleSelect')?.value === 'student') {
            const uniVal = document.getElementById('addUserUnivInput')?.value.trim();
            if (!uniVal) {
                e.preventDefault();
                AdminCore.toast('Please select or type a University name for the student.', 'warning');
                document.getElementById('addUserUnivInput')?.focus();
            }
        }
    });
}

// ── View, Edit, Status & Delete Modals ────────────────────────────────────────
function viewUser(u) {
    document.getElementById('viewName').innerText    = u.user_name || 'N/A';
    document.getElementById('viewEmail').innerText   = u.email || 'N/A';
    document.getElementById('viewRole').innerText    = (u.role || '').toUpperCase();
    document.getElementById('viewOrg').innerText     = u.organization_name || 'N/A';
    document.getElementById('viewStatus').innerText  = u.status || 'Active';
    document.getElementById('viewContact').innerText = u.contact_number || 'N/A';
    document.getElementById('viewDate').innerText    = u.created_at ? new Date(u.created_at).toLocaleDateString('en-US', { year:'numeric', month:'short', day:'numeric' }) : 'N/A';
    openModal('viewUserModal');
}

function editUser(u, isSelf) {
    document.getElementById('editEmail').value        = u.email;
    document.getElementById('editEmailDisplay').value = u.email;
    document.getElementById('editName').value         = u.user_name || '';
    document.getElementById('editRole').value         = (u.role || 'student').toLowerCase();
    document.getElementById('editStatus').value       = u.status || 'Active';
    document.getElementById('editOrgName').value      = u.organization_name || '';

    const contactGroup = document.getElementById('editContactGroup');
    const contactInput = document.getElementById('editContact');
    if (contactGroup && contactInput) {
        contactGroup.style.display = isSelf ? 'block' : 'none';
        contactInput.value = isSelf ? (u.contact_number || '') : '';
    }
    openModal('editUserModal');
}

function openStatusModal(email, name, newStatus) {
    document.getElementById('statusEmail').value        = email;
    document.getElementById('statusNewValue').value     = newStatus;
    document.getElementById('statusUserDisplay').innerText = `${name || 'User'} (${email})`;

    const actionText = document.getElementById('statusActionText');
    const btn        = document.getElementById('btnConfirmStatus');
    const iconWrap   = document.getElementById('statusIconWrap');

    if (newStatus === 'Active') {
        actionText.innerText = 'activate';
        btn.className = 'btn-primary';
        btn.style.background = '#10b981';
        btn.innerText = 'Activate User';
        iconWrap.style.background = '#dcfce7';
        iconWrap.style.color = '#16a34a';
    } else {
        actionText.innerText = 'deactivate';
        btn.className = 'btn-danger';
        btn.style.background = '#ef4444';
        btn.innerText = 'Deactivate User';
        iconWrap.style.background = '#fee2e2';
        iconWrap.style.color = '#dc2626';
    }
    openModal('statusModal');
}

function openDeleteModal(email, name) {
    document.getElementById('deleteEmail').value = email;
    document.getElementById('deleteUserDisplay').innerText = `${name} (${email})`;
    openModal('deleteUserModal');
}
