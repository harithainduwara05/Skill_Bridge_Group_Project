
    // Modal Helpers
    function openModal(id) {
        const modal = document.getElementById(id);
        if (modal) modal.classList.add('open');
    }

    function closeModal(id) {
        const modal = document.getElementById(id);
        if (modal) modal.classList.remove('open');
    }

    // Add User button listener
    document.getElementById('btnAddUser').addEventListener('click', function() {
        openModal('addUserModal');
    });

    const btnBulk = document.getElementById('btnBulkUpload');
    if (btnBulk) {
        btnBulk.addEventListener('click', function() {
            openModal('bulkUploadModal');
        });
    }

    // Close on overlay click
    document.querySelectorAll('.user-modal-overlay').forEach(overlay => {
        overlay.addEventListener('click', function(e) {
            if (e.target === this) {
                this.classList.remove('open');
            }
        });
    });

    // Universities and Faculties fetched from database
    const dbUniversities = window.dbUniversities || {};
    const activeInstitutions = window.activeInstitutions || {};

    // Bulk Import Modal: Dynamic University -> Faculty -> Authorized Domain Logic
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
            bulkDomainText.innerHTML = `
                <span style="color:#64748b; font-size:12px;">Please select a University and Faculty above to view the permitted student email domain.</span>
            `;
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
                if (facs.length === 1) {
                    bulkFacSelect.value = facs[0];
                    updateBulkDomainBadge(activeInstitutions[u][facs[0]]);
                } else {
                    updateBulkDomainBadge(null);
                }
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
            if (u && f && activeInstitutions[u] && activeInstitutions[u][f]) {
                updateBulkDomainBadge(activeInstitutions[u][f]);
            } else {
                updateBulkDomainBadge(null);
            }
        });
    }

    function setupSearchableCombobox(config) {
        const combobox = document.getElementById(config.comboboxId);
        const input    = document.getElementById(config.inputId);
        const toggle   = document.getElementById(config.toggleId);
        const dropdown = document.getElementById(config.dropdownId);
        const list     = document.getElementById(config.listId);

        if (!combobox || !input || !toggle || !dropdown || !list) return null;

        let highlightedIndex = -1;

        function escHtml(str) {
            const d = document.createElement('div');
            d.appendChild(document.createTextNode(str || ''));
            return d.innerHTML;
        }

        function renderList(query = '') {
            list.innerHTML = '';
            highlightedIndex = -1;
            const q = (query || '').trim().toLowerCase();
            const dataset = typeof config.getItems === 'function' ? config.getItems() : [];

            const filtered = dataset.filter(itemText => {
                return itemText && itemText.toLowerCase().includes(q);
            });

            let exactMatch = false;

            filtered.forEach(itemText => {
                if (itemText.toLowerCase() === q) exactMatch = true;

                const item = document.createElement('div');
                item.className = 'univ-combobox-item';
                if (input.value.trim().toLowerCase() === itemText.toLowerCase()) {
                    item.classList.add('selected');
                }

                const nameEl = document.createElement('span');
                nameEl.textContent = itemText;
                item.appendChild(nameEl);

                item.addEventListener('mousedown', (e) => {
                    e.preventDefault();
                    selectItem(itemText);
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
                    selectItem(query.trim());
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

        function selectItem(text) {
            input.value = text;
            closeDropdown();
            if (typeof config.onSelect === 'function') {
                config.onSelect(text);
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
            if (typeof config.onInput === 'function') {
                config.onInput(input.value);
            }
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

        document.addEventListener('mousedown', (e) => {
            if (!combobox.contains(e.target)) {
                closeDropdown();
            }
        });

        return {
            open: openDropdown,
            close: closeDropdown,
            refresh: () => {
                if (dropdown.classList.contains('open')) {
                    renderList(input.value);
                }
            }
        };
    }

    // Initialize University and Faculty Comboboxes
    const facCombobox = setupSearchableCombobox({
        comboboxId: 'addUserFacCombobox',
        inputId: 'addUserFacInput',
        toggleId: 'addUserFacToggle',
        dropdownId: 'addUserFacDropdown',
        listId: 'addUserFacList',
        emptyText: 'No faculties found.',
        getItems: () => {
            const currentUni = (document.getElementById('addUserUnivInput').value || '').trim().toLowerCase();
            if (currentUni) {
                for (const [uName, fList] of Object.entries(dbUniversities)) {
                    if (uName.toLowerCase() === currentUni) {
                        return fList;
                    }
                }
            }
            // If no exact university match or not typed, return all known faculties across all universities
            const allFacs = new Set();
            Object.values(dbUniversities).forEach(facList => {
                facList.forEach(f => allFacs.add(f));
            });
            return Array.from(allFacs);
        }
    });

    const univCombobox = setupSearchableCombobox({
        comboboxId: 'addUserUnivCombobox',
        inputId: 'addUserUnivInput',
        toggleId: 'addUserUnivToggle',
        dropdownId: 'addUserUnivDropdown',
        listId: 'addUserUnivList',
        emptyText: 'No universities found.',
        getItems: () => Object.keys(dbUniversities),
        onSelect: (selectedUni) => {
            if (facCombobox) facCombobox.refresh();
        },
        onInput: (val) => {
            if (facCombobox) facCombobox.refresh();
        }
    });

    // Toggle Role-specific fields in Add Modal
    function toggleAddFields(role) {
        const studentUniFac = document.getElementById('addStudentUniFacultyGroup');
        const orgGroup = document.getElementById('addOrgNameGroup');
        const orgLabel = document.getElementById('addOrgLabel');
        const studentExtra = document.getElementById('addStudentExtraFields');
        const contactGroup = document.getElementById('addContactGroup');
        const univInput = document.getElementById('addUserUnivInput');

        if (role === 'student') {
            studentUniFac.style.display = 'block';
            orgGroup.style.display = 'none';
            studentExtra.style.display = 'grid';
            contactGroup.style.display = 'none';
            if (univInput) univInput.required = true;
        } else if (role === 'company') {
            studentUniFac.style.display = 'none';
            orgGroup.style.display = 'block';
            orgLabel.innerText = 'Company Name';
            studentExtra.style.display = 'none';
            contactGroup.style.display = 'block';
            if (univInput) univInput.required = false;
        } else if (role === 'organization') {
            studentUniFac.style.display = 'none';
            orgGroup.style.display = 'block';
            orgLabel.innerText = 'Organization Name';
            studentExtra.style.display = 'none';
            contactGroup.style.display = 'block';
            if (univInput) univInput.required = false;
        } else if (role === 'admin') {
            studentUniFac.style.display = 'none';
            orgGroup.style.display = 'block';
            orgLabel.innerText = 'Department / System';
            studentExtra.style.display = 'none';
            contactGroup.style.display = 'none';
            if (univInput) univInput.required = false;
        }
    }

    // Add User Form Validation for Student University
    const addUserForm = document.querySelector('#addUserModal form');
    if (addUserForm) {
        addUserForm.addEventListener('submit', function(e) {
            const role = document.getElementById('addRoleSelect').value;
            if (role === 'student') {
                const uniVal = document.getElementById('addUserUnivInput').value.trim();
                if (!uniVal) {
                    e.preventDefault();
                    alert('Please select or type a University name for the student.');
                    document.getElementById('addUserUnivInput').focus();
                }
            }
        });
    }

    // View User Modal
    function viewUser(u) {
        document.getElementById('viewName').innerText = u.user_name || 'N/A';
        document.getElementById('viewEmail').innerText = u.email || 'N/A';
        document.getElementById('viewRole').innerText = (u.role || '').toUpperCase();
        document.getElementById('viewOrg').innerText = u.organization_name || 'N/A';
        document.getElementById('viewStatus').innerText = u.status || 'Active';
        document.getElementById('viewContact').innerText = u.contact_number || 'N/A';
        document.getElementById('viewDate').innerText = u.created_at ? new Date(u.created_at).toLocaleDateString('en-US', { year:'numeric', month:'short', day:'numeric' }) : 'N/A';
        openModal('viewUserModal');
    }

    // Edit User Modal
    function editUser(u, isSelf) {
        document.getElementById('editEmail').value = u.email;
        document.getElementById('editEmailDisplay').value = u.email;
        document.getElementById('editName').value = u.user_name || '';
        document.getElementById('editRole').value = (u.role || 'student').toLowerCase();
        document.getElementById('editStatus').value = u.status || 'Active';
        document.getElementById('editOrgName').value = u.organization_name || '';
        
        const contactGroup = document.getElementById('editContactGroup');
        const contactInput = document.getElementById('editContact');
        if (contactGroup && contactInput) {
            if (isSelf) {
                contactGroup.style.display = 'block';
                contactInput.value = u.contact_number || '';
            } else {
                contactGroup.style.display = 'none';
                contactInput.value = '';
            }
        }
        openModal('editUserModal');
    }

    // Change Status Modal (Replaces browser confirm alert)
    function openStatusModal(email, name, newStatus) {
        document.getElementById('statusEmail').value = email;
        document.getElementById('statusNewValue').value = newStatus;
        document.getElementById('statusUserDisplay').innerText = (name || 'User') + ' (' + email + ')';
        
        const actionText = document.getElementById('statusActionText');
        const btn = document.getElementById('btnConfirmStatus');
        const iconWrap = document.getElementById('statusIconWrap');

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

    // Delete User Modal
    function openDeleteModal(email, name) {
        document.getElementById('deleteEmail').value = email;
        document.getElementById('deleteUserDisplay').innerText = `${name} (${email})`;
        openModal('deleteUserModal');
    }

    // Flash Toast Dismissal
    function closeToast() {
        const toast = document.getElementById('flashToast');
        if (toast) {
            toast.style.animation = 'toastSlideOut 0.3s cubic-bezier(0.16, 1, 0.3, 1) forwards';
            setTimeout(() => {
                toast.remove();
            }, 300);
        }
    }

    // Auto dismiss toast after 4 seconds
    const activeToast = document.getElementById('flashToast');
    if (activeToast) {
        setTimeout(closeToast, 4000);
    }
