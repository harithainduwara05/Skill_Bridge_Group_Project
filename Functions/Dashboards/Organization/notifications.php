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
/* Notification Center – same layout as the student page, organization colours.
   Scoped to this page only – no shared CSS files touched. */
.nc-head { display: flex; align-items: flex-end; justify-content: space-between; gap: 16px; flex-wrap: wrap; margin-bottom: 20px; }
.nc-head h1 { margin: 0; font-size: 24px; font-weight: 800; color: #0f2a4a; letter-spacing: -.3px; }
.nc-head p { margin: 4px 0 0; font-size: 13.5px; color: #64748b; }
.nc-head-actions { display: flex; gap: 12px; flex-wrap: wrap; }
.nc-head-btn { display: inline-flex; align-items: center; gap: 8px; background: #fff; border: 1px solid #e2e8f0; border-radius: 10px;
    padding: 7px 14px; font-size: 12.5px; font-weight: 600; color: #0f2a4a; cursor: pointer; font-family: inherit;
    box-shadow: 0 1px 3px rgba(15, 42, 74, .06); transition: background .15s ease, border-color .15s ease; }
.nc-head-btn:hover { background: #f8fafc; border-color: #cbd5e1; }
.nc-head-btn .material-symbols-outlined { font-size: 16px; }
.nc-head-btn:disabled { opacity: .5; cursor: default; }

.nc-tabs { display: flex; gap: 6px; background: #e9eef5; border-radius: 10px; padding: 4px; margin-bottom: 20px; }
.nc-tab { border: none; background: transparent; padding: 7px 18px; border-radius: 7px; font-size: 13px; font-weight: 600;
    color: #475569; cursor: pointer; font-family: inherit; display: inline-flex; align-items: center; gap: 8px; }
.nc-tab:hover { color: #0f2a4a; }
.nc-tab.active { background: #fff; color: #0f2a4a; box-shadow: 0 1px 4px rgba(15, 42, 74, .10); }
.nc-tab { min-width: 84px; justify-content: center; }
.nc-side { display: flex; align-items: center; gap: 6px; flex-shrink: 0; }
.nc-arch { width: 28px; height: 28px; border-radius: 7px; border: none; background: transparent; color: #94a3b8; cursor: pointer;
    display: inline-flex; align-items: center; justify-content: center; transition: background .15s ease, color .15s ease; }
.nc-arch:hover { background: #f1f5f9; color: #0f2a4a; }
.nc-arch .material-symbols-outlined { font-size: 18px; }
.nc-card.archived { opacity: .85; }

.nc-list { display: flex; flex-direction: column; gap: 12px; }
.nc-card { position: relative; display: flex; gap: 14px; background: #fff; border-radius: 12px; padding: 16px 18px;
    border-left: 3px solid var(--nc-accent, #1e3a5f); box-shadow: 0 1px 6px rgba(15, 42, 74, .07); transition: box-shadow .15s ease; }
.nc-card:hover { box-shadow: 0 4px 16px rgba(15, 42, 74, .10); }
.nc-card.unread { cursor: pointer; }

.nc-icon { width: 38px; height: 38px; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;
    background: var(--nc-soft, #e8eef6); color: var(--nc-accent, #1e3a5f); }
.nc-icon .material-symbols-outlined { font-size: 20px; font-variation-settings: 'FILL' 1; }

/* accent colour per notification type */
.nc-navy  { --nc-accent: #1e3a5f; --nc-soft: #e8eef6; }
.nc-red   { --nc-accent: #dc2626; --nc-soft: #fee2e2; }
.nc-blue  { --nc-accent: #2563eb; --nc-soft: #dbeafe; }
.nc-amber { --nc-accent: #d97706; --nc-soft: #fef3c7; }
.nc-green { --nc-accent: #16a34a; --nc-soft: #dcfce7; }
.nc-gray  { --nc-accent: #64748b; --nc-soft: #f1f5f9; }

.nc-body { flex: 1; min-width: 0; }
.nc-top { display: flex; justify-content: space-between; align-items: flex-start; gap: 14px; }
.nc-title { font-size: 14px; font-weight: 700; color: #0f172a; display: flex; align-items: center; gap: 8px; }
.nc-dot { width: 7px; height: 7px; border-radius: 50%; background: var(--nc-accent, #1e3a5f); flex-shrink: 0; }
.nc-time { font-size: 12px; color: #64748b; white-space: nowrap; }
.nc-msg { font-size: 13px; line-height: 1.5; color: #4b5563; margin: 4px 0 0; }
.nc-msg strong { color: #1e293b; }
.nc-card.read .nc-title { color: #334155; }

.nc-tags { display: flex; gap: 8px; flex-wrap: wrap; margin-top: 8px; }
.nc-tag { font-size: 10px; font-weight: 800; letter-spacing: .3px; text-transform: uppercase; padding: 3px 9px; border-radius: 999px;
    background: #eef2f7; color: #1e3a5f; }
.nc-tag.hot { background: #fff1e6; color: #ea580c; }

.nc-actions { display: flex; gap: 8px; flex-wrap: wrap; margin-top: 12px; align-items: center; }
.nc-btn { display: inline-flex; align-items: center; padding: 7px 14px; border-radius: 7px; font-size: 12.5px; font-weight: 600;
    text-decoration: none; border: 1px solid transparent; font-family: inherit; cursor: pointer; transition: background .15s ease; }
.nc-btn.primary { background: #1e3a5f; color: #fff; }
.nc-btn.primary:hover { background: #14335c; }
.nc-btn.outline { background: #fff; color: #1f2937; border-color: #d1d5db; font-weight: 400; }
.nc-btn.outline:hover { background: #f8fafc; }
.nc-link { display: inline-flex; align-items: center; gap: 4px; font-size: 12.5px; font-weight: 700; color: #1e3a5f; text-decoration: none; }
.nc-link:hover { text-decoration: underline; }
.nc-link .material-symbols-outlined { font-size: 14px; }

.nc-more { margin-top: 12px; border: 2px dashed #d5dde8; border-radius: 12px; padding: 18px 20px; text-align: center; }
.nc-more button { background: none; border: none; font-size: 13.5px; font-weight: 700; color: #1e3a5f; cursor: pointer;
    display: inline-flex; align-items: center; gap: 6px; font-family: inherit; }
.nc-more button:hover { text-decoration: underline; }
.nc-more span.nc-end { font-size: 13px; color: #64748b; display: inline-flex; align-items: center; gap: 8px; }
.nc-more span.nc-end .material-symbols-outlined { font-size: 20px; color: #64748b; }

.nc-empty { text-align: center; padding: 44px 20px; color: #94a3b8; background: #fff; border-radius: 14px; box-shadow: 0 1px 6px rgba(15, 42, 74, .07); }
.nc-empty .material-symbols-outlined { font-size: 36px; color: #cbd5e1; display: block; margin-bottom: 10px; }
.nc-empty div { font-size: 14px; font-weight: 600; color: #64748b; }

@media (max-width: 640px) {
    .nc-card { padding: 18px; gap: 14px; }
    .nc-top { flex-direction: column; gap: 4px; }
}
</style>

<main class="content">

    <div class="nc-head">
        <div>
            <h1>Notification Center</h1>
            <p>Stay updated on student proposals, your teams and your projects.</p>
        </div>
        <div class="nc-head-actions">
            <button type="button" class="nc-head-btn" id="ncMarkAll"><span class="material-symbols-outlined">done_all</span>Mark All as Read</button>
            <button type="button" class="nc-head-btn" id="ncClearAll"><span class="material-symbols-outlined">delete</span>Clear All</button>
        </div>
    </div>

    <div class="nc-tabs" role="tablist">
        <button type="button" class="nc-tab active" data-tab="all">All</button>
        <button type="button" class="nc-tab" data-tab="unread">Unread</button>
        <button type="button" class="nc-tab" data-tab="archived">Archived</button>
    </div>

    <div class="nc-list" id="ncList"></div>
    <div class="nc-empty" id="ncEmpty" hidden>
        <span class="material-symbols-outlined">notifications_off</span>
        <div id="ncEmptyText">No notifications</div>
    </div>
    <div class="nc-more" id="ncMore" hidden></div>

</main>

<script>
(function () {
    // ---- Demo notifications for an ORGANIZATION (6 in the inbox + 2 archived) ----
    // Every button goes to the right page AND opens the right item there:
    //   proposal.php?view=<id>                 -> that proposal
    //   teams.php?team=<name>&open=<view|review|tasks|chat>
    //   manage_projects.php?status=<status>    -> projects with that status
    //   feedback.php?give=1                    -> "Submit feedback" form
    // Notifications that only give information have no button.
    const btn  = (href, label, style) => `<a href="${href}" class="nc-btn ${style || 'primary'}">${label}</a>`;
    const link = (href, label) => `<a href="${href}" class="nc-link">${label} <span class="material-symbols-outlined">arrow_outward</span></a>`;
    const team = (name, open) => 'teams.php?team=' + encodeURIComponent(name) + '&open=' + open;

    const RAW_NOTIFICATIONS = [
        { key: 'prop-1', color: 'navy', glyph: 'description', title: 'New Project Proposal',
          msg: '<strong>Nimasha Fernando</strong> sent a proposal for your <strong>Cloud Migration UI/UX</strong> project.',
          time: '15 mins ago', unread: true,
          actions: btn('proposal.php?view=1', 'Review Proposal') + btn('proposal.php#profile-1', 'View Profile', 'outline') },

        { key: 'team-behind', color: 'red', glyph: 'warning', title: 'Team Behind Schedule',
          msg: '<strong>Vortex Group</strong> is 5 days behind on <strong>Blockchain-Based Academic Credentials</strong>. Check what is blocking them.',
          time: '1 hour ago', unread: true, tags: [['Teams'], ['High Priority', 'hot']],
          actions: btn(team('Vortex Group', 'review'), 'Review Delay') },

        { key: 'team-deadline', color: 'amber', glyph: 'schedule', title: 'Team Deadline Approaching',
          msg: 'The deadline for <strong>Nexus Systems</strong> is <strong>Oct 24</strong> (14 days left). Their progress is 72%.',
          time: 'Yesterday', unread: false, tags: [['Teams'], ['Deadline']],
          actions: btn(team('Nexus Systems', 'tasks'), 'View Tasks', 'outline') },

        { key: 'team-done', color: 'green', glyph: 'task_alt', title: 'Project Completed',
          msg: '<strong>Quantum Analytics</strong> finished <strong>Predictive Maintenance for Smart Cities</strong> and submitted the final report.',
          time: 'Yesterday', unread: false,
          actions: btn(team('Quantum Analytics', 'view'), 'View Final Report') + btn('feedback.php?give=1', 'Give Feedback', 'outline') },

        { key: 'proj-hold', color: 'amber', glyph: 'pause_circle', title: 'Project Put On Hold',
          msg: 'Admin put one of your projects <strong>On Hold</strong> and asked for changes before it can go live.',
          time: '3 days ago', unread: false, tags: [['Projects'], ['Action Needed', 'hot']],
          actions: btn('manage_projects.php?status=hold', 'Fix & Resubmit') },

        { key: 'report', color: 'gray', glyph: 'bar_chart', title: 'Monthly Report Ready',
          msg: 'Your <strong>September</strong> report on projects, teams and student progress is ready.',
          time: '5 days ago', unread: false,
          actions: link('reports.php', 'View Report') },

        // older notifications that start in the Archived tab
        { key: 'arch-withdrawn', color: 'gray', glyph: 'undo', title: 'Proposal Withdrawn',
          msg: '<strong>Dilshan Perera</strong> withdrew his proposal for <strong>Smart Campus Navigator</strong>.',
          time: '2 weeks ago', unread: false, archived: true, actions: '' },

        { key: 'arch-welcome', color: 'navy', glyph: 'waving_hand', title: 'Welcome to SkillBridge',
          msg: 'Your organization account is ready. Post your first project so students can send proposals.',
          time: '3 weeks ago', unread: false, archived: true,
          actions: link('post.php', 'Post a Project') }
    ];

    // Read / archived state is remembered in this browser, so it survives page changes.
    // Open notifications.php?reset=1 to bring every demo notification back as it was.
    const ALL_READ_KEY = 'skillbridge_org_notifs_all_read_v2';
    const READ_KEY     = 'skillbridge_org_notifs_read_v2';
    const ARCH_KEY     = 'skillbridge_org_notifs_archived_v1';     // keys moved to / out of Archived
    const store = {
        get: (k, d) => { try { const v = localStorage.getItem(k); return v === null ? d : JSON.parse(v); } catch (e) { return d; } },
        set: (k, v) => { try { localStorage.setItem(k, JSON.stringify(v)); } catch (e) {} },
    };
    if (new URLSearchParams(location.search).has('reset')) {
        [ALL_READ_KEY, READ_KEY, ARCH_KEY].forEach(k => { try { localStorage.removeItem(k); } catch (e) {} });
        history.replaceState(null, '', location.pathname);
    }
    const allMarkedRead = store.get(ALL_READ_KEY, 0) == 1;
    let readKeys = store.get(READ_KEY, []);
    let archState = store.get(ARCH_KEY, {});      // { key: true (archived) / false (restored) }

    const notifications = RAW_NOTIFICATIONS.map((n, i) => Object.assign({}, n, {
        id: i, read: allMarkedRead || readKeys.includes(n.key) ? true : !n.unread,
        archived: n.key in archState ? archState[n.key] : !!n.archived
    }));

    let tab = 'all';

    const listEl   = document.getElementById('ncList');
    const emptyEl  = document.getElementById('ncEmpty');
    const moreEl   = document.getElementById('ncMore');
    const markAll  = document.getElementById('ncMarkAll');
    const clearAll = document.getElementById('ncClearAll');

    function cardHtml(n) {
        const tags = (n.tags || []).map(t => `<span class="nc-tag ${t[1] || ''}">${t[0]}</span>`).join('');
        return `
        <div class="nc-card nc-${n.color} ${n.read ? 'read' : 'unread'} ${n.archived ? 'archived' : ''}" data-id="${n.id}">
            <div class="nc-icon"><span class="material-symbols-outlined">${n.glyph}</span></div>
            <div class="nc-body">
                <div class="nc-top">
                    <div class="nc-title">${n.read ? '' : '<span class="nc-dot"></span>'}${n.title}</div>
                    <div class="nc-side">
                        <span class="nc-time">${n.time}</span>
                        <button type="button" class="nc-arch" data-arch="${n.id}" title="${n.archived ? 'Move back to inbox' : 'Archive'}">
                            <span class="material-symbols-outlined">${n.archived ? 'unarchive' : 'archive'}</span></button>
                    </div>
                </div>
                <p class="nc-msg">${n.msg}</p>
                ${tags ? `<div class="nc-tags">${tags}</div>` : ''}
                ${n.actions ? `<div class="nc-actions">${n.actions}</div>` : ''}
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
        moreEl.innerHTML = '<span class="nc-end"><span class="material-symbols-outlined">history</span>You have no older notifications from this week.</span>';
    }

    function setArchived(n, value) {
        n.archived = value;
        archState[n.key] = value;
        store.set(ARCH_KEY, archState);
    }

    // clicking a card (or its button) marks that notification as read
    function markRead(n) {
        if (n.read) return;
        n.read = true;
        if (!readKeys.includes(n.key)) readKeys.push(n.key);
        store.set(READ_KEY, readKeys);
    }
    listEl.addEventListener('click', function (e) {
        const arch = e.target.closest('[data-arch]');
        if (arch) {                                   // archive / move back to inbox
            const n = notifications[Number(arch.dataset.arch)];
            setArchived(n, !n.archived);
            if (n.archived) markRead(n);
            render();
            return;
        }
        const card = e.target.closest('.nc-card');
        if (!card) return;
        markRead(notifications[Number(card.dataset.id)]);
        if (!e.target.closest('a')) render();      // links leave the page anyway
    });

    document.querySelectorAll('.nc-tab').forEach(t => t.addEventListener('click', () => {
        document.querySelectorAll('.nc-tab').forEach(x => x.classList.toggle('active', x === t));
        tab = t.dataset.tab;
        render();
    }));

    markAll.addEventListener('click', () => {
        notifications.forEach(n => n.read = true);
        store.set(ALL_READ_KEY, 1);
        render();
    });

    clearAll.addEventListener('click', () => {
        if (!confirm('Clear all notifications? They will be moved to Archived.')) return;
        notifications.filter(n => !n.archived).forEach(n => { markRead(n); setArchived(n, true); });
        render();
    });

    render();
})();
</script>

<footer class="footer">
    <div>&copy; 2026 SkillBridge. All rights reserved.</div>
    <div class="footer-links">
        <a href="/Skill_Bridge_Group_Project/help_center.php">Help Center</a>
        <a href="/Skill_Bridge_Group_Project/help_center.php#knowledgeBaseSection">Privacy Policy</a>
        <a href="/Skill_Bridge_Group_Project/help_center.php#knowledgeBaseSection">Terms of Service</a>
    </div>
</footer>

<?php include "../../../Includes/dash_footer.php"; ?>