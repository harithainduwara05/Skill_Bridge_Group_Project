<?php

include "../../../Config/db.php";
include "../../../Session/Session.php";

require_role('organization');
$user = current_user();
$organization_email = $user['email'];

include "../../../Includes/org_sidebar.php";
include "../../../Includes/dash_header.php";
?>

<style>
/* Scoped to this page only — no shared CSS files touched */
.notif-page-header {
    display: flex;
    align-items: baseline;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 10px;
    margin-bottom: 4px;
}
.notif-page-header .notif-title-row {
    display: flex;
    align-items: center;
    gap: 10px;
}
.notif-page-header h1 {
    margin: 0;
}
.notif-unread-badge {
    background: #1e3a5f;
    color: #fff;
    font-size: 12px;
    font-weight: 700;
    padding: 3px 12px;
    border-radius: 999px;
}
.notif-mark-all {
    font-size: 13.5px;
    font-weight: 600;
    color: #1e3a5f;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}
.notif-mark-all:hover { text-decoration: underline; }
.notif-mark-all .material-symbols-outlined { font-size: 17px; }

.notif-toolbar {
    display: flex;
    align-items: center;
    gap: 12px;
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    padding: 10px 14px;
    margin: 18px 0 20px;
}
.notif-search {
    flex: 1;
    display: flex;
    align-items: center;
    gap: 8px;
    background: #f9fafb;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    padding: 8px 12px;
}
.notif-search .material-symbols-outlined { color: #9ca3af; font-size: 19px; }
.notif-search input {
    border: none;
    background: transparent;
    outline: none;
    font-size: 13.5px;
    width: 100%;
    color: #374151;
    font-family: inherit;
}
.notif-filter-label {
    font-size: 12.5px;
    color: #6b7280;
    white-space: nowrap;
}
.notif-filter-select {
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    padding: 8px 10px;
    font-size: 13px;
    color: #374151;
    background: #f9fafb;
    font-family: inherit;
    cursor: pointer;
}
.notif-icon-btn {
    width: 34px;
    height: 34px;
    border-radius: 8px;
    border: 1px solid #e5e7eb;
    background: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #6b7280;
    cursor: pointer;
    flex-shrink: 0;
    transition: background .15s ease;
}
.notif-icon-btn:hover { background: #f9fafb; color: #1e3a5f; }

.notif-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}
.notif-card {
    display: flex;
    gap: 14px;
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    padding: 16px 18px;
    transition: box-shadow .15s ease;
}
.notif-card.is-read { background: #fafafa; }
.notif-card:hover { box-shadow: 0 2px 10px rgba(0,0,0,0.05); }

.notif-icon {
    width: 42px;
    height: 42px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.notif-icon .material-symbols-outlined { font-size: 21px; }
.notif-icon.navy    { background: #1e3a5f; color: #fff; }
.notif-icon.blue    { background: #dbeafe; color: #2563eb; }
.notif-icon.green   { background: #16a34a; color: #fff; }
.notif-icon.gray    { background: #f3f4f6; color: #9ca3af; }
.notif-icon.red     { background: #fee2e2; color: #dc2626; }
.notif-icon.amber   { background: #fef3c7; color: #b45309; }
.notif-card:not(.is-read) { cursor: pointer; }

.notif-body { flex: 1; min-width: 0; }
.notif-top-row {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 10px;
}
.notif-heading {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 14px;
    font-weight: 700;
    color: #111827;
}
.notif-card.is-read .notif-heading { color: #6b7280; font-weight: 600; }
.notif-heading .unread-dot {
    width: 7px;
    height: 7px;
    background: #2563eb;
    border-radius: 50%;
    flex-shrink: 0;
}
.notif-time {
    font-size: 12px;
    color: #9ca3af;
    white-space: nowrap;
    flex-shrink: 0;
}
.notif-msg {
    font-size: 13px;
    color: #4b5563;
    line-height: 1.5;
    margin: 4px 0 0;
}
.notif-card.is-read .notif-msg { color: #9ca3af; }
.notif-actions {
    display: flex;
    gap: 8px;
    margin-top: 12px;
}
.notif-btn {
    padding: 7px 14px;
    border-radius: 7px;
    font-size: 12.5px;
    font-weight: 600;
    font-family: inherit;
    cursor: pointer;
    border: 1px solid transparent;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
}
.notif-btn.primary { background: #1e3a5f; color: #fff; }
.notif-btn.primary:hover { background: #14335c; }
.notif-btn.outline { background: #fff; border-color: #d1d5db; color: #374151; }
.notif-btn.outline:hover { background: #f9fafb; }
.notif-btn.success { background: #16a34a; color: #fff; }
.notif-btn.success:hover { background: #15803d; }

.notif-footer-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-top: 20px;
    flex-wrap: wrap;
    gap: 10px;
}
.notif-showing {
    font-size: 12.5px;
    color: #6b7280;
}
.notif-pagination {
    display: flex;
    align-items: center;
    gap: 4px;
}
.notif-page-btn {
    min-width: 30px;
    height: 30px;
    border-radius: 7px;
    border: 1px solid #e5e7eb;
    background: #fff;
    color: #374151;
    font-size: 12.5px;
    font-weight: 600;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0 8px;
}
.notif-page-btn:hover { background: #f9fafb; }
.notif-page-btn.active { background: #1e3a5f; border-color: #1e3a5f; color: #fff; }
.notif-page-dots { color: #9ca3af; font-size: 12px; padding: 0 2px; }

.notif-empty {
    text-align: center;
    padding: 40px 0;
    color: #9ca3af;
    font-size: 13.5px;
}
</style>

<main class="content">

    <div class="dashboard-header notif-page-header">
        <div class="notif-title-row">
            <h1>Notifications</h1>
            <span class="notif-unread-badge" id="notifUnreadBadge">2 Unread</span>
        </div>
        <a href="#" class="notif-mark-all" id="notifMarkAllRead">
            <span class="material-symbols-outlined">done_all</span> Mark all as read
        </a>
    </div>
    <p style="color:#6b7280; font-size:13.5px; margin-top:4px;">
        Stay updated on student proposals, your teams and your projects.
    </p>

    <!-- ===================== TOOLBAR ===================== -->
    <div class="notif-toolbar">
        <div class="notif-search">
            <span class="material-symbols-outlined">search</span>
            <input type="text" id="notifSearchInput" placeholder="Search notifications...">
        </div>
        <span class="notif-filter-label">Filter by:</span>
        <select class="notif-filter-select" id="notifFilterSelect">
            <option value="all">All Notifications</option>
            <option value="unread">Unread Only</option>
        </select>
    </div>

    <!-- ===================== NOTIFICATION LIST ===================== -->
    <div class="notif-list" id="notifList"></div>
    <div class="notif-empty" id="notifEmptyState" style="display:none;">
        No notifications match your search.
    </div>

    <div class="notif-footer-row">
        <span class="notif-showing" id="notifShowingText"></span>
        <div class="notif-pagination" id="notifPagination"></div>
    </div>

</main>

<script>
(function () {
    // ---- Demo notifications for an ORGANIZATION (10 items, 5 per page) ----
    // Every button goes to the right page AND opens the right item there:
    //   proposal.php?view=<id>                 -> that proposal
    //   teams.php?team=<name>&open=<view|review|tasks|chat>
    //   manage_projects.php?status=<status>    -> projects with that status
    //   feedback.php?give=1                    -> "Submit feedback" form
    // Notifications that only give information have no button.
    const btn = (href, label, style) => `<a href="${href}" class="notif-btn ${style || 'primary'}">${label}</a>`;
    const team = (name, open) => 'teams.php?team=' + encodeURIComponent(name) + '&open=' + open;

    const RAW_NOTIFICATIONS = [
        { key: 'prop-1', icon: 'navy', glyph: 'description', category: 'proposals', title: 'New Project Proposal',
          msg: '<strong>Nimasha Fernando</strong> sent a proposal for your <strong>Cloud Migration UI/UX</strong> project.',
          time: '15m ago', unread: true,
          actions: btn('proposal.php?view=1', 'Review Proposal') + btn('proposal.php#profile-1', 'View Profile', 'outline') },

        { key: 'team-behind', icon: 'red', glyph: 'warning', category: 'teams', title: 'Team Behind Schedule',
          msg: '<strong>Vortex Group</strong> is 5 days behind on <strong>Blockchain-Based Academic Credentials</strong>. Check what is blocking them.',
          time: '1h ago', unread: true,
          actions: btn(team('Vortex Group', 'review'), 'Review Delay') },

        { key: 'prop-2', icon: 'navy', glyph: 'description', category: 'proposals', title: 'New Project Proposal',
          msg: '<strong>Sahan Wickramasinghe</strong> sent a proposal for your <strong>AI Model Optimization</strong> project.',
          time: '3h ago', unread: false,
          actions: btn('proposal.php?view=2', 'Review Proposal') },

        { key: 'team-chat', icon: 'blue', glyph: 'chat', category: 'teams', title: 'New Team Message',
          msg: '<strong>Kavinda Jayasuriya</strong> (Alpha Ops): “Cluster is now in production. Please keep an eye on the alerts.”',
          time: '5h ago', unread: false,
          actions: btn(team('Alpha Ops', 'chat'), 'Open Chat') },

        { key: 'team-deadline', icon: 'amber', glyph: 'schedule', category: 'teams', title: 'Team Deadline Approaching',
          msg: 'The deadline for <strong>Nexus Systems</strong> is <strong>Oct 24</strong> (14 days left). Their progress is 72%.',
          time: 'Yesterday', unread: false,
          actions: btn(team('Nexus Systems', 'tasks'), 'View Tasks') },

        { key: 'team-done', icon: 'green', glyph: 'task_alt', category: 'teams', title: 'Project Completed',
          msg: '<strong>Quantum Analytics</strong> finished <strong>Predictive Maintenance for Smart Cities</strong> and submitted the final report.',
          time: 'Yesterday', unread: false,
          actions: btn(team('Quantum Analytics', 'view'), 'View Final Report') + btn('feedback.php?give=1', 'Give Feedback', 'outline') },

        { key: 'proj-approved', icon: 'green', glyph: 'verified', category: 'projects', title: 'Project Approved',
          msg: 'SkillBridge Admin approved your project. It is now <strong>Active</strong> and visible to students.',
          time: '2 days ago', unread: false,
          actions: btn('manage_projects.php?status=inprogress', 'View Project', 'outline') },

        { key: 'proj-hold', icon: 'amber', glyph: 'pause_circle', category: 'projects', title: 'Project Put On Hold',
          msg: 'Admin put one of your projects <strong>On Hold</strong> and asked for changes before it can go live.',
          time: '3 days ago', unread: false,
          actions: btn('manage_projects.php?status=hold', 'Fix & Resubmit') },

        { key: 'report', icon: 'gray', glyph: 'bar_chart', category: 'system', title: 'Monthly Report Ready',
          msg: 'Your <strong>September</strong> report on projects, teams and student progress is ready.',
          time: '5 days ago', unread: false,
          actions: btn('reports.php', 'View Report', 'outline') },

        { key: 'verified', icon: 'gray', glyph: 'workspace_premium', category: 'system', title: 'Organization Verified',
          msg: 'Your organization account was verified by SkillBridge. Students can now see a verified badge on your projects.',
          time: '1 week ago', unread: false,
          actions: '' }
    ];

    const PAGE_SIZE = 5;
    const notifications = [];

    // "Mark all as read" is remembered per-browser, so it survives navigating
    // away and coming back (this dataset is regenerated fresh on every load).
    const ALL_READ_KEY = 'skillbridge_org_notifs_all_read_v2';
    const READ_KEY = 'skillbridge_org_notifs_read_v2';          // keys of notifications opened one by one
    const allMarkedRead = localStorage.getItem(ALL_READ_KEY) === '1';
    let readKeys = [];
    try { readKeys = JSON.parse(localStorage.getItem(READ_KEY)) || []; } catch (e) {}

    RAW_NOTIFICATIONS.forEach((n, i) => {
        notifications.push({
            id: i,
            key: n.key,
            icon: n.icon,
            glyph: n.glyph,
            category: n.category,
            title: n.title,
            msg: n.msg,
            time: n.time,
            actions: n.actions,
            read: allMarkedRead || readKeys.includes(n.key) ? true : !n.unread
        });
    });

    let currentPage = 1;
    let totalPages = 1;

    const listEl        = document.getElementById('notifList');
    const emptyState    = document.getElementById('notifEmptyState');
    const showingText   = document.getElementById('notifShowingText');
    const paginationEl  = document.getElementById('notifPagination');
    const searchInput   = document.getElementById('notifSearchInput');
    const filterSelect  = document.getElementById('notifFilterSelect');
    const markAllLink   = document.getElementById('notifMarkAllRead');
    const unreadBadge   = document.getElementById('notifUnreadBadge');

    function updateUnreadBadge() {
        const unreadCount = notifications.filter(n => !n.read).length;
        unreadBadge.textContent = unreadCount + ' Unread';
        unreadBadge.style.display = unreadCount > 0 ? '' : 'none';
    }

    function cardHtml(n) {
        return `
        <div class="notif-card ${n.read ? 'is-read' : ''}" data-id="${n.id}" data-read="${n.read ? 1 : 0}" data-category="${n.category}">
            <div class="notif-icon ${n.icon}"><span class="material-symbols-outlined">${n.glyph}</span></div>
            <div class="notif-body">
                <div class="notif-top-row">
                    <div class="notif-heading">${n.read ? '' : '<span class="unread-dot"></span> '}${n.title}</div>
                    <span class="notif-time">${n.time}</span>
                </div>
                <p class="notif-msg">${n.msg}</p>
                ${n.actions ? `<div class="notif-actions">${n.actions}</div>` : ''}
            </div>
        </div>`;
    }

    // search + filter work on ALL notifications, then the result is split into pages
    const plain = html => { const d = document.createElement('div'); d.innerHTML = html; return d.textContent.toLowerCase(); };
    function filtered() {
        const query = (searchInput.value || '').trim().toLowerCase();
        const filter = filterSelect.value;
        return notifications.filter(n =>
            (query === '' || (n.title + ' ' + plain(n.msg)).toLowerCase().includes(query)) &&
            (filter === 'all' || !n.read));
    }

    function renderPage(page) {
        const items = filtered();
        totalPages = Math.max(1, Math.ceil(items.length / PAGE_SIZE));
        currentPage = Math.min(Math.max(page, 1), totalPages);
        const start = (currentPage - 1) * PAGE_SIZE;
        const pageItems = items.slice(start, start + PAGE_SIZE);

        listEl.innerHTML = pageItems.map(cardHtml).join('');
        emptyState.style.display = items.length ? 'none' : 'block';
        emptyState.textContent = (filterSelect.value === 'unread' && !searchInput.value.trim())
            ? 'You’re all caught up. No unread notifications.'
            : 'No notifications match your search.';
        showingText.textContent = items.length
            ? `Showing ${start + 1}\u2013${start + pageItems.length} of ${items.length} notifications`
            : '';
        renderPagination();
    }

    function applyFilters() { renderPage(1); }

    function renderPagination() {
        paginationEl.innerHTML = '';

        const addBtn = (label, page, opts = {}) => {
            const btn = document.createElement('button');
            btn.className = 'notif-page-btn' + (opts.active ? ' active' : '');
            btn.textContent = label;
            btn.disabled = !!opts.disabled;
            btn.addEventListener('click', () => renderPage(page));
            paginationEl.appendChild(btn);
        };
        const addDots = () => {
            const span = document.createElement('span');
            span.className = 'notif-page-dots';
            span.textContent = '\u2026';
            paginationEl.appendChild(span);
        };

        addBtn('\u2039', currentPage - 1, { disabled: currentPage === 1 });

        const pagesToShow = new Set([1, totalPages, currentPage - 1, currentPage, currentPage + 1]);
        let lastPrinted = 0;
        for (let p = 1; p <= totalPages; p++) {
            if (!pagesToShow.has(p) || p < 1 || p > totalPages) continue;
            if (p - lastPrinted > 1) addDots();
            addBtn(String(p), p, { active: p === currentPage });
            lastPrinted = p;
        }

        addBtn('\u203a', currentPage + 1, { disabled: currentPage === totalPages });
    }

    // Clicking a button (or the card) marks that notification as read.
    // The button link then opens the right page.
    function markRead(n) {
        if (n.read) return;
        n.read = true;
        if (!readKeys.includes(n.key)) readKeys.push(n.key);
        try { localStorage.setItem(READ_KEY, JSON.stringify(readKeys)); } catch (e) {}
        updateUnreadBadge();
    }
    listEl.addEventListener('click', function (e) {
        const card = e.target.closest('.notif-card');
        if (!card) return;
        const n = notifications[Number(card.dataset.id)];
        markRead(n);
        if (!e.target.closest('a')) renderPage(currentPage);   // links leave the page anyway
    });

    searchInput.addEventListener('input', applyFilters);
    filterSelect.addEventListener('change', applyFilters);

    markAllLink.addEventListener('click', function (e) {
        e.preventDefault();
        notifications.forEach(n => n.read = true);
        localStorage.setItem(ALL_READ_KEY, '1');
        updateUnreadBadge();
        renderPage(currentPage);
    });

    updateUnreadBadge();
    renderPage(1);
})();
</script>

<footer class="footer">
    <div>&copy; 2026 SkillBridge. All rights reserved.</div>
    <div class="footer-links">
        <a href="#">Help Center</a>
        <a href="#">Privacy Policy</a>
        <a href="#">Terms of Service</a>
    </div>
</footer>

<?php include "../../../Includes/dash_footer.php"; ?>