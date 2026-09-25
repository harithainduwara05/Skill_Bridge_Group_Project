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
        Stay updated on your project proposals, internship applications, and team activities.
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
    // ---- Fake dataset: 12 notifications across 3 pages (5 per page) ----
    const templates = [
        { icon: 'navy',  category: 'proposals', title: 'New Project Proposal Received',
          msg: (n) => `<strong>${n.company}</strong> sent a new collaborative brief for the <strong>${n.project}</strong> project. Review the deliverables and timeline within 24 hours.`,
          actions: '<a href="proposals.php" class="notif-btn primary">View Proposal</a><a href="#" class="notif-btn outline">Decline</a>' },
        { icon: 'gray',  category: 'teams', title: 'Team Documents Updated',
          msg: (n) => `The <strong>${n.project} Guidelines</strong> have been updated by ${n.person}. Please review the new linting rules.`,
          actions: '' },
        { icon: 'green', category: 'teams', title: 'New Team Member Joined',
          msg: (n) => `<strong>${n.person}</strong> just joined the "${n.project}" workspace. Send a welcome message to start collaborating.`,
          actions: '' },
        { icon: 'gray',  category: 'system', title: 'System Maintenance Notice',
          msg: () => `SkillBridge will undergo scheduled maintenance this weekend, from 02:00 AM to 04:00 AM EST.`,
          actions: '' },
        { icon: 'navy',  category: 'proposals', title: 'Proposal Deadline Approaching',
          msg: (n) => `Reminder: the review window for <strong>${n.company}</strong>'s <strong>${n.project}</strong> proposal closes tomorrow.`,
          actions: '<a href="proposals.php" class="notif-btn primary">Review Now</a>' },
        { icon: 'green', category: 'teams', title: 'Project Milestone Completed',
          msg: (n) => `<strong>${n.person}</strong> marked a milestone as complete on <strong>${n.project}</strong>. Take a look at the latest progress.`,
          actions: '' },
        { icon: 'navy',  category: 'system', title: 'Feedback Received',
          msg: (n) => `A student left new feedback on <strong>${n.project}</strong>. Your response helps improve future collaborations.`,
          actions: '' },
    ];
    const companies = ['FinTech Solutions', 'TechStream', 'NovaWorks', 'BrightPath Labs', 'CoreSys Ltd', 'Vertex Digital', 'ByteForge', 'Alpine Analytics'];
    const projects  = ['API Integration', 'Cloud Architecture Internship', 'Backend Development', 'Project Phoenix', 'Mobile Attendance Tracker', 'Portfolio Website Builder', 'AI Chatbot Support', 'Data Pipeline Revamp'];
    const people    = ['Sarah Miller', 'Jordan Peterson', 'Elena Vance', 'Marcus Thorne', 'Kevin Zhang', 'Priya Nair'];
    const times     = ['2m ago', '45m ago', '3h ago', 'Yesterday', 'Oct 24', 'Oct 23', 'Oct 22', 'Oct 20', 'Oct 18'];

    const TOTAL_ITEMS = 12;
    const PAGE_SIZE = 5;
    const notifications = [];

    // "Mark all as read" is remembered per-browser, so it survives navigating
    // away and coming back (this dataset is regenerated fresh on every load).
    const ALL_READ_KEY = 'skillbridge_org_notifs_all_read';
    const allMarkedRead = localStorage.getItem(ALL_READ_KEY) === '1';

    for (let i = 0; i < TOTAL_ITEMS; i++) {
        const t = templates[i % templates.length];
        const ctx = {
            company: companies[i % companies.length],
            project: projects[(i + 2) % projects.length],
            person: people[i % people.length]
        };
        notifications.push({
            id: i,
            icon: t.icon,
            category: t.category,
            title: t.title,
            msg: t.msg(ctx),
            time: times[i % times.length],
            actions: t.actions,
            // Only the 2 newest stay unread by default (until "Mark all as read" is used)
            read: allMarkedRead ? true : (i >= 2)
        });
    }

    let currentPage = 1;
    const totalPages = Math.ceil(TOTAL_ITEMS / PAGE_SIZE);

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
            <div class="notif-icon ${n.icon}"><span class="material-symbols-outlined">${iconGlyph(n.icon, n.category)}</span></div>
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

    function iconGlyph(icon, category) {
        if (category === 'proposals' && icon === 'navy') return 'description';
        if (category === 'proposals' && icon === 'blue') return 'work';
        if (category === 'teams' && icon === 'green') return 'group_add';
        if (category === 'teams') return 'article';
        return 'info';
    }

    function renderPage(page) {
        currentPage = Math.min(Math.max(page, 1), totalPages);
        const start = (currentPage - 1) * PAGE_SIZE;
        const pageItems = notifications.slice(start, start + PAGE_SIZE);

        listEl.innerHTML = pageItems.map(cardHtml).join('');
        showingText.textContent = `Showing ${start + 1}\u2013${start + pageItems.length} of ${TOTAL_ITEMS} notifications`;

        renderPagination();
        applyFilters();
    }

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

    function applyFilters() {
        const query = (searchInput.value || '').trim().toLowerCase();
        const filter = filterSelect.value;
        const cards = Array.from(listEl.querySelectorAll('.notif-card'));
        let visibleCount = 0;

        cards.forEach(card => {
            const text = card.textContent.toLowerCase();
            const matchesSearch = query === '' || text.includes(query);
            const matchesFilter =
                filter === 'all' ||
                (filter === 'unread' && card.dataset.read === '0') ||
                (filter !== 'unread' && card.dataset.category === filter);

            const visible = matchesSearch && matchesFilter;
            card.style.display = visible ? '' : 'none';
            if (visible) visibleCount++;
        });

        emptyState.style.display = visibleCount === 0 ? 'block' : 'none';
    }

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