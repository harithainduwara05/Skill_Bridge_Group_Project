<?php

include "../../Config/db.php";
include "../../Session/Session.php";

require_role('organization');
$user = current_user();
$organization_email = $user['email'];

$extra_css = '<link rel="stylesheet" href="../../Assets/CSS/Organization/notifications.css">';
include "../../Includes/org_sidebar.php";
include "../../Includes/dash_header.php";
?>




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

<?php include "../../Includes/dash_footer.php"; ?>