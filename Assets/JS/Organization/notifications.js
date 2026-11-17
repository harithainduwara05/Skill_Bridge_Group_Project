/*
| Organization - notifications page script
| Used by: Functions/Organization/notifications.php
| Values from PHP / the database come from ORG_DATA (set in a small <script> on the page).
*/

(function () {
    // ---- Notifications of this organization, loaded from the DB by the backend ----
    const notifications = ORG_DATA.notifications;

    let tab = 'all';

    const listEl   = document.getElementById('ncList');
    const emptyEl  = document.getElementById('ncEmpty');
    const moreEl   = document.getElementById('ncMore');
    const markAll  = document.getElementById('ncMarkAll');
    const clearAll = document.getElementById('ncClearAll');

    // Messages come from the DB -> escape them before putting them in HTML
    function esc(text) {
        const d = document.createElement('div');
        d.textContent = text == null ? '' : String(text);
        return d.innerHTML;
    }

    // Save a change in the DB (same page, answered with JSON)
    function save(action, id) {
        const fd = new FormData();
        fd.append('action', action);
        if (id) fd.append('id', id);
        return fetch('notifications.php', { method: 'POST', body: fd })
            .then(r => r.json())
            .catch(() => ({ success: false }));
    }

    function cardHtml(n) {
        const action = n.link ? `<div class="nc-actions"><a href="${esc(n.link)}" class="nc-btn primary">${esc(n.label)}</a></div>` : '';
        return `
        <div class="nc-card nc-${n.color} ${n.read ? 'read' : 'unread'} ${n.archived ? 'archived' : ''}" data-id="${n.id}">
            <div class="nc-icon"><span class="material-symbols-outlined">${n.glyph}</span></div>
            <div class="nc-body">
                <div class="nc-top">
                    <div class="nc-title">${n.read ? '' : '<span class="nc-dot"></span>'}${esc(n.title)}</div>
                    <div class="nc-side">
                        <span class="nc-time">${esc(n.time)}</span>
                        <button type="button" class="nc-arch" data-arch="${n.id}" title="${n.archived ? 'Move back to inbox' : 'Archive'}">
                            <span class="material-symbols-outlined">${n.archived ? 'unarchive' : 'archive'}</span></button>
                    </div>
                </div>
                <p class="nc-msg">${esc(n.msg)}</p>
                ${action}
            </div>
        </div>`;
    }

    const EMPTY = {
        all: 'No notifications. You’re all clear.',
        unread: 'You’re all caught up. No unread notifications.',
        archived: 'No archived notifications.'
    };

    function render() {
        const inbox = notifications.filter(n => !n.archived);
        const items = tab === 'archived' ? notifications.filter(n => n.archived)
                    : tab === 'unread'   ? inbox.filter(n => !n.read)
                    : inbox;

        markAll.disabled  = !inbox.some(n => !n.read);
        clearAll.disabled = inbox.length === 0;

        listEl.innerHTML = items.map(cardHtml).join('');
        emptyEl.hidden = items.length > 0;
        document.getElementById('ncEmptyText').textContent = EMPTY[tab];

        moreEl.hidden = items.length === 0;
        moreEl.innerHTML = '<span class="nc-end"><span class="material-symbols-outlined">history</span>No older notifications.</span>';
    }

    function findById(id) { return notifications.find(n => n.id === Number(id)); }

    // clicking a card (or its button) marks that notification as read
    function markRead(n) {
        if (n.read) return;
        n.read = true;
        save('read', n.id);
    }

    listEl.addEventListener('click', function (e) {
        const arch = e.target.closest('[data-arch]');
        if (arch) {                                   // archive / move back to inbox
            const n = findById(arch.dataset.arch);
            n.archived = !n.archived;
            n.read = true;
            save(n.archived ? 'archive' : 'unarchive', n.id);
            render();
            return;
        }
        const card = e.target.closest('.nc-card');
        if (!card) return;
        markRead(findById(card.dataset.id));
        if (!e.target.closest('a')) render();      // links leave the page anyway
    });

    document.querySelectorAll('.nc-tab').forEach(t => t.addEventListener('click', () => {
        document.querySelectorAll('.nc-tab').forEach(x => x.classList.toggle('active', x === t));
        tab = t.dataset.tab;
        render();
    }));

    markAll.addEventListener('click', () => {
        notifications.forEach(n => n.read = true);
        save('read_all');
        render();
    });

    clearAll.addEventListener('click', () => {
        if (!confirm('Clear all notifications? They will be moved to Archived.')) return;
        notifications.filter(n => !n.archived).forEach(n => { n.read = true; n.archived = true; });
        save('clear_all');
        render();
    });

    render();
})();