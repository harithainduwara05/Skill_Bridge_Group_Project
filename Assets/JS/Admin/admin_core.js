const AdminCore = {
    /**
     * Display a floating feedback toast notification (success, error, warning)
     */
    toast(message, type = 'success') {
        // Find existing toast element on the page or create a dynamic one
        let toast = document.getElementById('adminToast') ||
                    document.getElementById('pmToast') ||
                    document.getElementById('imToast') ||
                    document.getElementById('cmToast') ||
                    document.getElementById('raToast');

        let msgEl = document.getElementById('adminToastMessage') ||
                    document.getElementById('pmToastMessage') ||
                    document.getElementById('imToastMessage') ||
                    document.getElementById('cmToastMessage') ||
                    document.getElementById('raToastMessage');

        if (!toast) {
            toast = document.createElement('div');
            toast.id = 'adminToast';
            toast.className = 'action-toast';
            toast.innerHTML = `
                <span class="material-symbols-outlined" style="font-size:20px;">check_circle</span>
                <span id="adminToastMessage"></span>
            `;
            document.body.appendChild(toast);
            msgEl = document.getElementById('adminToastMessage');
        }

        if (msgEl) {
            msgEl.textContent = message;
        }

        const iconEl = toast.querySelector('.material-symbols-outlined');
        if (iconEl) {
            if (type === 'error') {
                iconEl.textContent = 'error';
                iconEl.style.color = '#ef4444';
            } else if (type === 'warning') {
                iconEl.textContent = 'warning';
                iconEl.style.color = '#f59e0b';
            } else {
                iconEl.textContent = 'check_circle';
                iconEl.style.color = '#22c55e';
            }
        }

        toast.classList.add('show');
        clearTimeout(toast._timer);
        toast._timer = setTimeout(() => {
            toast.classList.remove('show');
        }, 3600);
    },

    /**
     * Modal controller: Open, Close, and Auto-Bind handlers
     */
    modal: {
        open(modalOrId) {
            const modal = typeof modalOrId === 'string' ? document.getElementById(modalOrId) : modalOrId;
            if (!modal) return;
            modal.classList.add('show', 'open', 'active');
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        },

        close(modalOrId, formToReset = null) {
            const modal = typeof modalOrId === 'string' ? document.getElementById(modalOrId) : modalOrId;
            if (!modal) return;
            modal.classList.remove('show', 'open', 'active');
            modal.style.display = 'none';
            document.body.style.overflow = '';
            if (formToReset) {
                const form = typeof formToReset === 'string' ? document.getElementById(formToReset) : formToReset;
                if (form && typeof form.reset === 'function') form.reset();
            }
        },

        /**
         * Auto-wires trigger button, close buttons, backdrop click, and ESC key
         */
        bind(modalOrId, options = {}) {
            const modal = typeof modalOrId === 'string' ? document.getElementById(modalOrId) : modalOrId;
            if (!modal) return;

            // Trigger open button
            if (options.trigger) {
                const triggerEl = typeof options.trigger === 'string' ? document.getElementById(options.trigger) : options.trigger;
                if (triggerEl) {
                    triggerEl.addEventListener('click', (e) => {
                        e.preventDefault();
                        if (typeof options.onOpen === 'function') options.onOpen();
                        AdminCore.modal.open(modal);
                    });
                }
            }

            // Close buttons (selectors or explicit elements)
            const closeElements = [];
            if (options.closeBtns) {
                (Array.isArray(options.closeBtns) ? options.closeBtns : [options.closeBtns]).forEach(btn => {
                    const el = typeof btn === 'string' ? document.getElementById(btn) : btn;
                    if (el) closeElements.push(el);
                });
            }
            modal.querySelectorAll('[data-dismiss="modal"], [data-close], .btn-close, .modal-close').forEach(b => {
                closeElements.push(b);
            });

            closeElements.forEach(btn => {
                btn.addEventListener('click', (e) => {
                    e.preventDefault();
                    if (typeof options.onClose === 'function') options.onClose();
                    AdminCore.modal.close(modal, options.form);
                });
            });

            // Backdrop click closes modal
            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    if (typeof options.onClose === 'function') options.onClose();
                    AdminCore.modal.close(modal, options.form);
                }
            });

            // ESC key closes modal
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && (modal.classList.contains('show') || modal.classList.contains('open') || modal.style.display === 'flex')) {
                    if (typeof options.onClose === 'function') options.onClose();
                    AdminCore.modal.close(modal, options.form);
                }
            });
        }
    },

    /**
     * Unified Table Pagination & Empty-State Engine
     */
    paginate(config) {
        const {
            tableBody,
            rows = [],
            pageSize = 5,
            currentPage = 1,
            infoEl,
            controlsEl,
            onPageChange,
            emptyIcon = 'search_off',
            emptyMessage = 'No matching records found',
            emptySubtext = 'Try adjusting your search keywords or active filters.',
            colspan = 8
        } = config;

        if (!tableBody) return;

        const totalCount = rows.length;
        const totalPages = Math.ceil(totalCount / pageSize) || 1;
        const page = Math.min(Math.max(1, currentPage), totalPages);

        // Hide all table rows first
        const allRows = tableBody.querySelectorAll('tr[data-id], tr.pm-data-row, tr.im-data-row, tr.cm-data-row, tr.univ-row, tr.user-row');
        allRows.forEach(r => r.style.display = 'none');

        // Show only current page rows
        const start = (page - 1) * pageSize;
        const end = Math.min(start + pageSize, totalCount);

        for (let i = start; i < end; i++) {
            if (rows[i]) rows[i].style.display = '';
        }

        // Update pagination summary label ("Showing 1 to 5 of 24 results")
        if (infoEl) {
            infoEl.textContent = totalCount === 0
                ? 'Showing 0 of 0 entries'
                : `Showing ${start + 1} to ${end} of ${totalCount} entries`;
        }

        // Render dynamic pagination buttons
        if (controlsEl) {
            let html = '';
            html += `<button type="button" class="page-btn page-prev" ${page === 1 ? 'disabled' : ''}>Previous</button>`;

            if (totalPages <= 7) {
                for (let p = 1; p <= totalPages; p++) {
                    html += `<button type="button" class="page-btn page-num ${p === page ? 'active' : ''}" data-page="${p}">${p}</button>`;
                }
            } else {
                html += `<button type="button" class="page-btn page-num ${1 === page ? 'active' : ''}" data-page="1">1</button>`;
                if (page > 3) html += `<span class="page-ellipsis">...</span>`;

                const startP = Math.max(2, page - 1);
                const endP = Math.min(totalPages - 1, page + 1);
                for (let p = startP; p <= endP; p++) {
                    html += `<button type="button" class="page-btn page-num ${p === page ? 'active' : ''}" data-page="${p}">${p}</button>`;
                }

                if (page < totalPages - 2) html += `<span class="page-ellipsis">...</span>`;
                html += `<button type="button" class="page-btn page-num ${totalPages === page ? 'active' : ''}" data-page="${totalPages}">${totalPages}</button>`;
            }

            html += `<button type="button" class="page-btn page-next" ${page === totalPages ? 'disabled' : ''}>Next</button>`;
            controlsEl.innerHTML = html;

            // Bind previous & next button clicks
            const prevBtn = controlsEl.querySelector('.page-prev');
            if (prevBtn && page > 1) {
                prevBtn.addEventListener('click', () => onPageChange(page - 1));
            }

            const nextBtn = controlsEl.querySelector('.page-next');
            if (nextBtn && page < totalPages) {
                nextBtn.addEventListener('click', () => onPageChange(page + 1));
            }

            // Bind number clicks
            controlsEl.querySelectorAll('.page-num').forEach(btn => {
                btn.addEventListener('click', () => {
                    const target = parseInt(btn.getAttribute('data-page'), 10);
                    if (!isNaN(target) && target !== page) {
                        onPageChange(target);
                    }
                });
            });
        }

        // Toggle clean empty state row when 0 rows match
        let emptyRow = tableBody.querySelector('.admin-empty-state-row');
        if (totalCount === 0) {
            if (!emptyRow) {
                emptyRow = document.createElement('tr');
                emptyRow.className = 'admin-empty-state-row';
                emptyRow.innerHTML = `
                    <td colspan="${colspan}" style="text-align: center; padding: 48px 20px; color: #64748b;">
                        <span class="material-symbols-outlined" style="font-size: 40px; color: #94a3b8; display: block; margin-bottom: 8px;">${emptyIcon}</span>
                        <strong style="font-size: 15px; color: #1e293b; display: block; margin-bottom: 4px;">${emptyMessage}</strong>
                        <span style="font-size: 13px;">${emptySubtext}</span>
                    </td>
                `;
                tableBody.appendChild(emptyRow);
            } else {
                emptyRow.style.display = '';
            }
        } else if (emptyRow) {
            emptyRow.style.display = 'none';
        }
    },

    /**
     * XSS sanitizer
     */
    escapeHtml(str) {
        const div = document.createElement('div');
        div.appendChild(document.createTextNode(str || ''));
        return div.innerHTML;
    }
};

// Global aliases for full backwards compatibility
window.AdminCore = AdminCore;
window.showToast = AdminCore.toast;
window.cmShowToast = AdminCore.toast;
window.escapeHtml = AdminCore.escapeHtml;
