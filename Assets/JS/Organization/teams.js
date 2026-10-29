/*
| Organization - teams page script
| Used by: Functions/Organization/teams.php
| Values from PHP / the database come from ORG_DATA (set in a small <script> on the page).
*/

/* Popups are moved to <body> so they cover the WHOLE screen
   (inside <main> they were limited to the content area). */
document.querySelectorAll('.tm-overlay').forEach(o => document.body.appendChild(o));

/* =================================================================
   FILTER (status, project, search) – works on the cards in the page
================================================================= */
(function () {
    const btn     = document.getElementById('tmFilterBtn');
    const panel   = document.getElementById('tmFilterPanel');
    const search  = document.getElementById('tmFilterSearch');
    const sortEl  = document.getElementById('tmFilterSort');
    const grid    = document.querySelector('.tm-grid');
    const seg     = document.getElementById('tmFilterStatus');
    const countEl = document.getElementById('tmFilterCount');
    const result  = document.getElementById('tmFilterResult');
    const empty   = document.getElementById('tmEmpty');
    const cards   = Array.from(document.querySelectorAll('.tm-grid .tm-card'));

    let status = 'all';

    function apply() {
        const q = search.value.trim().toLowerCase();
        let shown = 0;
        const live = cards.filter(c => !c.dataset.deleted);

        live.forEach(function (card) {
            const ok = (status === 'all' || card.dataset.state === status)
                    && (q === '' || card.dataset.search.indexOf(q) !== -1);
            card.hidden = !ok;
            if (ok) shown++;
        });

        // sort the cards
        const num = (c, k) => Number(c.dataset[k]) || 0;
        const sorted = cards.slice().sort(function (a, b) {
            switch (sortEl.value) {
                case 'deadline':                         // no deadline -> last
                    return (num(a, 'deadline') || Infinity) - (num(b, 'deadline') || Infinity);
                case 'progress_low':  return num(a, 'progress') - num(b, 'progress');
                case 'progress_high': return num(b, 'progress') - num(a, 'progress');
                default:              return num(a, 'order') - num(b, 'order');
            }
        });
        sorted.forEach(c => grid.appendChild(c));

        const active = (status !== 'all') + (q !== '') + (sortEl.value !== 'newest');
        countEl.textContent = active;
        countEl.hidden = active === 0;
        result.textContent = 'Showing ' + shown + ' of ' + live.length;
        empty.hidden = shown !== 0;
    }

    function clearAll() {
        status = 'all';
        seg.querySelectorAll('button').forEach(b => b.classList.toggle('on', b.dataset.value === 'all'));
        sortEl.value = 'newest';
        search.value = '';
        apply();
    }

    btn.addEventListener('click', function (e) {
        e.stopPropagation();
        panel.hidden = !panel.hidden;
        btn.setAttribute('aria-expanded', String(!panel.hidden));
        if (!panel.hidden) search.focus();
    });
    panel.addEventListener('click', e => e.stopPropagation());
    document.addEventListener('click', () => { panel.hidden = true; btn.setAttribute('aria-expanded', 'false'); });
    document.addEventListener('keydown', e => { if (e.key === 'Escape') panel.hidden = true; });

    seg.addEventListener('click', function (e) {
        const b = e.target.closest('button');
        if (!b) return;
        status = b.dataset.value;
        seg.querySelectorAll('button').forEach(x => x.classList.toggle('on', x === b));
        apply();
    });
    sortEl.addEventListener('change', apply);
    search.addEventListener('input', apply);
    document.getElementById('tmFilterClear').addEventListener('click', clearAll);
    document.getElementById('tmEmptyClear').addEventListener('click', clearAll);
    document.addEventListener('tm-refilter', apply);

    apply();
})();


/* =================================================================
   TEAM DEADLINE AFTER THE DAY THE PROJECT ENDS (Create Team / Edit Team / Extend Deadline)
   The day the project ends = Application Deadline + duration weeks. A later team deadline is
   allowed only when the organization agrees to make the project longer (popup below):
   the project's duration becomes the whole weeks up to the team deadline (max ORG_DATA.MAX_WEEKS).
================================================================= */
window.TMExtend = (function () {
    const DAY = 86400000;
    const toD = ymd => new Date(ymd + 'T00:00:00');
    const toYmd = d => d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
    const nice = ymd => toD(ymd).toLocaleDateString('en-US', { month: 'short', day: '2-digit', year: 'numeric' });
    const addWeeks = (ymd, w) => toYmd(new Date(toD(ymd).getTime() + w * 7 * DAY));

    // the latest team deadline possible for this project (Application Deadline + MAX_WEEKS)
    function latest(start) { return start ? addWeeks(start, ORG_DATA.MAX_WEEKS) : ''; }

    /* null = the date is not after the day the project ends (nothing to change)
       { tooLate } = after the longest possible project
       { oldWeeks, weeks, end, oldEnd } = the project will be made longer          */
    function info(start, weeks, end, date) {
        if (!date || !end || !start || date <= end) return null;
        if (date > latest(start)) return { tooLate: true, max: nice(latest(start)) };
        const need = Math.ceil((toD(date) - toD(start)) / (7 * DAY));
        const w = Math.min(ORG_DATA.MAX_WEEKS, Math.max(weeks + 1, need));
        return { oldWeeks: weeks, weeks: w, oldEnd: nice(end), end: nice(addWeeks(start, w)) };
    }

    // the yellow note under the date box
    function note(el, x) {
        if (!el) return;
        el.hidden = !x || x.tooLate;
        if (x && !x.tooLate) {
            el.innerHTML = 'This is after the project ends (' + x.oldEnd + '). The project will be made longer: '
                         + x.oldWeeks + ' → <b>' + x.weeks + ' weeks</b>, ending on <b>' + x.end + '</b>.';
        }
    }

    // "Make the project longer?" popup -> onOk() when the organization agrees
    const modal = document.getElementById('tmExtendConfirm');
    let onOk = null;
    function confirm(x, ok) {
        onOk = ok;
        document.getElementById('tmExtendText').innerHTML =
            '<p style="margin:0 0 10px;">The project ends on <b>' + x.oldEnd + '</b>. To keep this team deadline, the project\'s duration '
            + 'will change from <b>' + x.oldWeeks + '</b> to <b>' + x.weeks + ' weeks</b>, so the project will end on <b>' + x.end + '</b>.</p>'
            + '<p style="margin:0;color:#6b7280;font-size:13px;">Students see the new duration on the project page. Other teams of this project keep their own deadlines.</p>';
        modal.classList.add('open');
    }
    if (modal) {
        modal.querySelector('[data-extend-cancel]').addEventListener('click', () => { onOk = null; modal.classList.remove('open'); });
        modal.querySelector('[data-extend-ok]').addEventListener('click', () => { modal.classList.remove('open'); const f = onOk; onOk = null; if (f) f(); });
        modal.addEventListener('click', e => { if (e.target === modal) { onOk = null; modal.classList.remove('open'); } });
    }

    return { info, note, confirm, latest, nice };
})();


/* =================================================================
   SHARED FORM PARTS (used by Create Team and Edit Team)
   - member list with a Role box for every ticked student
================================================================= */
window.TMForm = (function () {
    const COLORS = ['#1e3a5f', '#0f766e', '#7c3aed', '#b45309', '#be185d', '#2563eb', '#475569'];
    const esc = t => { const d = document.createElement('div'); d.textContent = t == null ? '' : String(t); return d.innerHTML; };
    const initials = n => String(n || '').trim().split(/\s+/).slice(0, 2).map(w => w.charAt(0).toUpperCase()).join('');
    const colorFor = n => { let h = 0; for (const c of String(n)) h = (h * 31 + c.charCodeAt(0)) >>> 0; return COLORS[h % COLORS.length]; };

    /* people: [{ value, name, sub, skills[], badge, badgeClass, locked }]
       chosen: { value: role }  -> ticked students and their roles          */
    function renderMembers(box, people, chosen) {
        chosen = chosen || {};
        box.innerHTML = people.map(p => {
            const on = Object.prototype.hasOwnProperty.call(chosen, p.value);
            return `
            <div class="tm-mrow ${on ? 'on' : ''}" data-value="${esc(p.value)}">
                <label class="tm-member ${p.locked ? 'disabled' : ''}" ${p.locked ? 'title="' + esc(p.locked) + '"' : ''}>
                    <input type="checkbox" name="members[]" value="${esc(p.value)}" data-name="${esc(p.name)}"
                           ${on ? 'checked' : ''} ${p.locked ? 'disabled data-locked="1"' : ''}>
                    <span class="tm-av" style="background:${colorFor(p.name)};">${esc(initials(p.name))}</span>
                    <span class="tm-member-info">
                        <span class="tm-member-name">${esc(p.name)}</span><br>
                        <span class="tm-member-sub">${esc(p.sub || '')}</span>
                        ${(p.skills || []).length ? `<span class="tm-mskills">${p.skills.map(s => `<span>${esc(s)}</span>`).join('')}</span>` : ''}
                    </span>
                    ${p.badge ? `<span class="tm-src ${esc(p.badgeClass || '')}">${esc(p.badge)}</span>` : ''}
                </label>
                <div class="tm-role-row" ${on ? '' : 'hidden'}>
                    <span class="material-symbols-outlined">badge</span>
                    <input type="text" class="tm-input" name="roles[${esc(p.value)}]" list="tmRoleList" maxlength="50"
                           placeholder="Role in the team, e.g. Frontend Developer" value="${esc(on ? chosen[p.value] : '')}" ${on ? '' : 'disabled'}>
                    <span class="tm-lead-tag" hidden><span class="material-symbols-outlined">star</span>Leader</span>
                </div>
            </div>`;
        }).join('');
    }

    /* keeps role boxes, max members and the leader list in sync */
    function syncMembers(box, leaderEl, max, pickedEl) {
        const rows = Array.from(box.querySelectorAll('.tm-mrow'));
        const picked = rows.filter(r => r.querySelector('input[type="checkbox"]').checked);

        rows.forEach(r => {
            const cb = r.querySelector('input[type="checkbox"]');
            const role = r.querySelector('.tm-role-row input');
            const full = !cb.checked && picked.length >= max;
            if (!cb.dataset.locked) {
                cb.disabled = full;
                r.querySelector('.tm-member').classList.toggle('disabled', full);
            }
            r.classList.toggle('on', cb.checked);
            r.querySelector('.tm-role-row').hidden = !cb.checked;
            role.disabled = !cb.checked;           // unticked -> role is not sent
        });

        if (pickedEl) pickedEl.textContent = picked.length + ' / ' + max + ' selected';

        const keep = leaderEl.value;
        leaderEl.innerHTML = picked.length
            ? '<option value="">Select the leader</option>' + picked.map(r => {
                const cb = r.querySelector('input[type="checkbox"]');
                return `<option value="${esc(cb.value)}">${esc(cb.dataset.name)}</option>`; }).join('')
            : '<option value="">Choose members first</option>';
        leaderEl.disabled = picked.length === 0;
        if (picked.some(r => r.dataset.value === keep)) leaderEl.value = keep;
        else if (picked.length === 1) leaderEl.value = picked[0].dataset.value;
        markLeader(box, leaderEl.value);
        return picked;
    }

    function markLeader(box, value) {
        box.querySelectorAll('.tm-mrow').forEach(r => { r.querySelector('.tm-lead-tag').hidden = !value || r.dataset.value !== value; });
    }

    return { esc, initials, colorFor, renderMembers, syncMembers, markLeader };
})();


/* =================================================================
   CREATE NEW TEAM
================================================================= */
(function () {
    const PROJECTS   = ORG_DATA.PROJECTS;
    const CANDIDATES = ORG_DATA.CANDIDATES;
    const TODAY = ORG_DATA.TODAY;
    const F = window.TMForm;

    const modal = document.getElementById('tmCreateModal');
    const form  = document.getElementById('tmCreateForm');
    const openBtn = document.getElementById('tmCreateBtn');

    const nameEl    = document.getElementById('tmName');
    const projectEl = document.getElementById('tmProject');
    const infoEl    = document.getElementById('tmProjectInfo');
    const membersEl = document.getElementById('tmMembers');
    const pickedEl  = document.getElementById('tmPicked');
    const leaderEl  = document.getElementById('tmLeader');
    const deadlineEl= document.getElementById('tmDeadline');
    const dlHint    = document.getElementById('tmDeadlineHint');
    const extendEl  = form.querySelector('[name="extend_project"]');
    const extendNote= form.querySelector('[data-extend-note]');
    const X = window.TMExtend;
    // a date after the day the project ends -> the project will be made longer
    const extendInfo = () => { const pr = currentProject(); return pr ? X.info(pr.start, pr.weeks, pr.deadline, deadlineEl.value) : null; };
    const showExtend = () => { if (extendEl) extendEl.value = '0'; X.note(extendNote, extendInfo()); };

    const err = (key, msg) => { const el = form.querySelector('[data-err="' + key + '"]'); if (el) el.textContent = msg || ''; };

    function open() {
        modal.classList.add('open');
        if (nameEl) setTimeout(() => nameEl.focus(), 50);
    }
    function close() { modal.classList.remove('open'); }

    openBtn.addEventListener('click', open);
    modal.querySelectorAll('[data-close]').forEach(b => b.addEventListener('click', close));
    modal.addEventListener('click', e => { if (e.target === modal) close(); });
    document.addEventListener('keydown', e => { if (e.key === 'Escape' && modal.classList.contains('open')) close(); });

    if (!projectEl) return;   // no projects -> only the info message is shown

    const currentProject = () => PROJECTS.find(p => p.id === Number(projectEl.value)) || null;
    const pickedRows = () => Array.from(membersEl.querySelectorAll('.tm-mrow')).filter(r => r.querySelector('input[type="checkbox"]').checked);

    function refresh() {
        const pr = currentProject();
        if (!pr) { pickedEl.textContent = ''; leaderEl.innerHTML = '<option value="">Choose members first</option>'; leaderEl.disabled = true; return; }
        const picked = F.syncMembers(membersEl, leaderEl, Math.max(1, pr.members), pickedEl);
        if (picked.length) { err('members', ''); membersEl.classList.remove('invalid'); }
    }

    projectEl.addEventListener('change', function () {
        const pr = currentProject();
        err('project', ''); projectEl.classList.remove('invalid');
        err('members', ''); err('leader', ''); err('deadline', '');
        deadlineEl.classList.remove('invalid');

        if (!pr) {
            infoEl.hidden = true;
            membersEl.innerHTML = '<div class="tm-members-empty">Select a project to see the students you accepted for it.</div>';
            deadlineEl.max = '';
            showExtend();
            refresh();
            return;
        }

        infoEl.innerHTML =
            `<span class="tm-info-chip"><span class="material-symbols-outlined">group</span>${pr.members} student${pr.members === 1 ? '' : 's'} needed</span>` +
            (pr.deadline_text ? `<span class="tm-info-chip"><span class="material-symbols-outlined">event</span>Project ends: ${F.esc(pr.deadline_text)}</span>` : '') +
            // the team works on the project's skills (project_skills)
            (pr.skills.length ? `<span class="tm-info-chip"><span class="material-symbols-outlined">bolt</span>Skills: ${F.esc(pr.skills.join(', '))}</span>` : '');
        infoEl.hidden = false;

        // team deadline: today ... the day the project ends (default).
        // A later date is possible too: the project is then made longer (up to MAX_WEEKS).
        deadlineEl.min = TODAY;
        deadlineEl.max = X.latest(pr.start);
        if (deadlineEl.value && ((deadlineEl.max && deadlineEl.value > deadlineEl.max) || deadlineEl.value < TODAY)) deadlineEl.value = '';
        if (pr.deadline && pr.deadline >= TODAY) deadlineEl.value = pr.deadline;   // default = the day the project ends
        dlHint.textContent = pr.deadline_text
            ? 'By default the team works until the project ends (' + pr.deadline_text + '). Pick an earlier date, or a later one to make the project longer.'
            : 'Pick a date from today onwards.';
        showExtend();

        const list = CANDIDATES[pr.id] || [];
        if (list.length) {
            F.renderMembers(membersEl, list.map(c => ({
                value: c.email, name: c.name, sub: c.sub, skills: c.skills,
                badge: c.team ? 'In ' + c.team : 'Accepted', badgeClass: c.team ? 'locked' : 'assigned',
                locked: c.team ? 'Already in the team "' + c.team + '"' : ''
            })), {});
        } else {
            membersEl.innerHTML = '<div class="tm-members-empty">No accepted students for this project yet.<br>' +
                                  'Accept student proposals on the <a href="proposal.php">Proposals</a> page first.</div>';
        }

        refresh();
    });

    membersEl.addEventListener('change', e => {
        const cb = e.target.closest('input[type="checkbox"]');
        refresh();
        if (cb && cb.checked) {
            const role = cb.closest('.tm-mrow').querySelector('.tm-role-row input');
            setTimeout(() => role.focus(), 0);
        }
    });
    membersEl.addEventListener('input', e => { if (e.target.matches('.tm-role-row input')) { e.target.classList.remove('invalid'); err('members', ''); } });

    leaderEl.addEventListener('change', () => {
        err('leader', ''); leaderEl.classList.remove('invalid');
        F.markLeader(membersEl, leaderEl.value);
        // leader with an empty role -> "Team Leader"
        const row = membersEl.querySelector(`.tm-mrow[data-value="${CSS.escape(leaderEl.value)}"]`);
        const role = row && row.querySelector('.tm-role-row input');
        if (role && !role.value.trim()) role.value = 'Team Leader';
    });
    nameEl.addEventListener('input', () => { err('name', ''); nameEl.classList.remove('invalid'); });
    deadlineEl.addEventListener('change', () => { err('deadline', ''); deadlineEl.classList.remove('invalid'); showExtend(); });

    form.addEventListener('submit', function (e) {
        let ok = true;
        const pr = currentProject();
        const picked = pickedRows();

        if (nameEl.value.trim() === '') { err('name', 'Please enter a team name.'); nameEl.classList.add('invalid'); ok = false; }
        else if (nameEl.value.trim().length < 3) { err('name', 'The team name must be at least 3 characters.'); nameEl.classList.add('invalid'); ok = false; }
        if (!pr) { err('project', 'Please select a project.'); projectEl.classList.add('invalid'); ok = false; }

        if (pr && picked.length === 0) {
            err('members', 'Please choose at least one student.'); membersEl.classList.add('invalid'); ok = false;
        } else {
            const noRole = picked.map(r => r.querySelector('.tm-role-row input')).filter(i => !i.value.trim());
            noRole.forEach(i => i.classList.add('invalid'));
            if (noRole.length) { err('members', 'Please give every selected student a role.'); ok = false; }
        }
        if (picked.length && !leaderEl.value) {
            err('leader', 'Please choose a team leader.'); leaderEl.classList.add('invalid'); ok = false;
        }

        const dl = deadlineEl.value;
        if (!dl) { err('deadline', 'Please pick a team deadline.'); deadlineEl.classList.add('invalid'); ok = false; }
        else if (dl < TODAY) { err('deadline', 'The deadline can’t be in the past.'); deadlineEl.classList.add('invalid'); ok = false; }
        else {
            const x = extendInfo();
            if (x && x.tooLate) { err('deadline', 'A project can run up to ' + ORG_DATA.MAX_WEEKS + ' weeks, so the latest team deadline is ' + x.max + '.'); deadlineEl.classList.add('invalid'); ok = false; }
            else if (ok && x && extendEl.value !== '1') {
                // ask first: "Make the project longer?" -> then send the form
                e.preventDefault();
                X.confirm(x, () => { extendEl.value = '1'; form.submit(); });
                return;
            }
        }

        if (!ok) {
            e.preventDefault();
            const first = form.querySelector('.tm-err:not(:empty)');
            if (first) first.closest('.tm-field').scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    });
})();


/* =================================================================
   TEAM DETAILS (View / View Final Report)
================================================================= */
(function () {
    const TEAMS  = ORG_DATA.DETAIL_TEAMS;
    const COLORS = ['#1e3a5f', '#0f766e', '#7c3aed', '#b45309', '#be185d', '#2563eb', '#475569'];

    const modal = document.getElementById('tmViewModal');
    const body  = document.getElementById('tvBody');

    const esc = t => { const d = document.createElement('div'); d.textContent = t == null ? '' : String(t); return d.innerHTML; };
    const initials = n => String(n || '').trim().split(/\s+/).slice(0, 2).map(w => w.charAt(0).toUpperCase()).join('');
    const colorFor = n => { let h = 0; for (const c of String(n)) h = (h * 31 + c.charCodeAt(0)) >>> 0; return COLORS[h % COLORS.length]; };
    const pct = v => Math.max(0, Math.min(100, Number(v) || 0));

    // Final report card: the file + summary from the team leader, and Accept / Send back for changes
    const REPORT_STATE = {
        submitted:         ['Waiting for your review', 'wait'],
        accepted:          ['Accepted', 'ok'],
        changes_requested: ['Sent back for changes', 'changes']
    };
    function reportCard(t, d) {
        const r = d.report;
        if (!r) {
            if (t.state === 'completed') return '';
            const total = d.tasks_total || 0, done = d.tasks_done || 0;
            return `<div class="tv-card tv-report">
                <h4><span class="material-symbols-outlined">description</span> Final Report</h4>
                <div class="tv-empty">Not sent yet. The team leader can send it when every task on the board is Done` +
                (total ? ` (${done} of ${total} done).` : ' (no tasks on the board yet).') + `</div></div>`;
        }
        const st = REPORT_STATE[r.status] || REPORT_STATE.submitted;
        const file = r.path
            ? `<a class="tv-rep-file" href="../../${esc(r.path)}" target="_blank" rel="noopener" download="${esc(r.name)}"><span class="material-symbols-outlined">download</span>${esc(r.name)}</a>`
            : `<span class="tv-rep-file"><span class="material-symbols-outlined">draft</span>${esc(r.name)}</span>`;
        const canReview = r.status === 'submitted' && t.id !== null && t.id !== undefined && d.can_review;
        return `<div class="tv-card tv-report ${st[1]}">
            <h4><span class="material-symbols-outlined">description</span> Final Report <span class="tv-rep-badge ${st[1]}">${esc(st[0])}</span></h4>
            <div class="tv-rep-top">${file}<span class="tv-rep-by">Sent by ${esc(r.by)} on ${esc(r.submitted)}</span></div>
            <p class="tv-rep-summary">${esc(r.summary)}</p>
            ${r.status === 'changes_requested' ? `<div class="tv-rep-note"><strong>You sent it back${r.reviewed ? ' on ' + esc(r.reviewed) : ''}:</strong> ${esc(r.note)}<br><span>The team leader will fix the report and send it again.</span></div>` : ''}
            ${r.status === 'accepted' ? `<div class="tv-rep-done"><span class="material-symbols-outlined">verified</span>Accepted${r.reviewed ? ' on ' + esc(r.reviewed) : ''} – the team is completed.</div>` : ''}
            ${canReview ? `
            <div class="tv-rep-actions" id="tvRepActions">
                <button type="button" class="tm-btn" id="tvRepChanges"><span class="material-symbols-outlined">undo</span>Send Back for Changes</button>
                <button type="button" class="tm-btn solid" id="tvRepAccept"><span class="material-symbols-outlined">check_circle</span>Accept Report</button>
            </div>
            <div class="tv-rep-form" id="tvRepForm" hidden>
                <label class="tm-f-label" for="tvRepNote">What should the team change? *</label>
                <textarea id="tvRepNote" maxlength="500" placeholder="e.g. Please add the test results and screenshots of the final system."></textarea>
                <div class="tm-err" id="tvRepErr"></div>
                <div class="tv-rep-actions">
                    <button type="button" class="tm-btn" id="tvRepCancel">Cancel</button>
                    <button type="button" class="tm-btn solid" id="tvRepSend"><span class="material-symbols-outlined">undo</span>Send Back</button>
                </div>
            </div>
            <div class="tv-rep-hint"><b>Accept</b> marks the team as Completed (and the project too, when all its teams are completed). This can’t be undone.
                <b>Send back</b> lets the team leader fix the report and send it again.</div>` : ''}
        </div>`;
    }

    // Accept (asks first in a popup) / Send back for changes (with a note) -> saved by teams.php (review_report)
    function wireReport(t) {
        const acc = document.getElementById('tvRepAccept');
        if (!acc) return;
        const form = document.getElementById('tmReportForm');
        const send = (decision, note) => {
            form.elements.namedItem('team_id').value = t.id;
            form.elements.namedItem('decision').value = decision;
            form.elements.namedItem('note').value = note || '';
            form.submit();
        };
        // "Accept the final report?" popup
        const askModal = document.getElementById('tvAcceptModal');
        const okBtn = document.getElementById('tvAcceptOk');
        acc.addEventListener('click', () => {
            document.getElementById('tvAcceptTeam').textContent = t.name + ' · ' + t.project;
            okBtn.disabled = false;
            askModal.classList.add('open');
        });
        okBtn.onclick = () => { okBtn.disabled = true; acc.disabled = true; send('accept'); };
        askModal.querySelectorAll('[data-close-accept]').forEach(b => { b.onclick = () => askModal.classList.remove('open'); });
        askModal.onclick = e => { if (e.target === askModal) askModal.classList.remove('open'); };
        const box = document.getElementById('tvRepForm'), btns = document.getElementById('tvRepActions');
        const note = document.getElementById('tvRepNote'), errEl = document.getElementById('tvRepErr');
        document.getElementById('tvRepChanges').addEventListener('click', () => { btns.hidden = true; box.hidden = false; note.focus(); });
        document.getElementById('tvRepCancel').addEventListener('click', () => { box.hidden = true; btns.hidden = false; errEl.textContent = ''; note.classList.remove('invalid'); });
        note.addEventListener('input', () => { errEl.textContent = ''; note.classList.remove('invalid'); });
        document.getElementById('tvRepSend').addEventListener('click', e => {
            const v = note.value.trim();
            if (v.length < 10) { errEl.textContent = 'Please tell the team what to change (at least 10 characters).'; note.classList.add('invalid'); return; }
            e.currentTarget.disabled = true;
            send('changes', v);
        });
    }

    function workBadge(w) {
        const t = String(w || '').toLowerCase();
        const cls = t === 'completed' ? 'done' : (t === 'in progress' ? 'progress' : 'none');
        return `<span class="tv-work ${cls}">${esc(w || 'Not started')}</span>`;
    }

    function open(i) {
        const t = window.tmEffective ? window.tmEffective(i, TEAMS[i]) : TEAMS[i];
        if (!t) return;
        const d = t.details || { members: [], activity: [] };
        const members = d.members || [];

        document.getElementById('tvName').textContent = t.name;
        const st = document.getElementById('tvStatus');
        st.className = 'tm-pill ' + t.state;
        st.textContent = t.status;
        document.getElementById('tvProject').innerHTML = 'Project: <strong>' + esc(t.project) + '</strong>';

        const info = [
            ['Organization', d.company], ['Category', d.category], ['Duration', d.duration],
            ['Team created', d.created], ['Team deadline', d.team_deadline], ['Project ends', d.project_deadline]
        ].filter(r => r[1]).map(r => `<div class="tv-info"><span>${esc(r[0])}</span><strong>${esc(r[1])}</strong></div>`).join('');

        const memberCards = members.length ? members.map(m => `
            <div class="tv-member ${m.leader ? 'leader' : ''}">
                <div class="tv-m-top">
                    <div class="tm-av" style="background:${colorFor(m.name)};">${esc(initials(m.name))}</div>
                    <div class="tv-m-info">
                        <div class="tv-m-name">${esc(m.name)}
                            ${m.leader ? '<span class="tv-leader-tag"><span class="material-symbols-outlined">star</span>Leader</span>' : ''}
                        </div>
                        <div class="tv-m-role">${esc(m.role)}</div>
                        <div class="tv-m-line"><span class="material-symbols-outlined">school</span>${esc([m.university, m.degree].filter(Boolean).join(' · '))}${m.year ? ' · Year ' + esc(m.year) : ''}</div>
                        <div class="tv-m-line"><span class="material-symbols-outlined">mail</span><a href="mailto:${esc(m.email)}">${esc(m.email)}</a></div>
                    </div>
                    ${workBadge(m.work)}
                </div>
                ${(m.skills || []).length ? `<div class="tv-m-skills">${m.skills.map(s => `<span>${esc(s)}</span>`).join('')}</div>` : ''}
                <div class="tv-m-prog">
                    <span>Contribution</span>
                    <div class="tv-bar ${pct(m.progress) === 100 ? 'completed' : ''}"><span style="width:${pct(m.progress)}%"></span></div>
                    <strong>${pct(m.progress)}%</strong>
                </div>
            </div>`).join('')
            : '<div class="tv-empty">No member details available.</div>';

        const acts = (d.activity || []).length ? `<ul class="tv-act">${d.activity.map(a => `
                <li><span class="tv-act-dot"></span><div><div class="tv-act-text">${esc(a.text)}</div><div class="tv-act-when">${esc(a.when)}</div></div></li>`).join('')}</ul>`
            : '<div class="tv-empty">No recent activity.</div>';

        const barClass = t.state === 'completed' ? 'completed' : (t.state === 'behind' ? 'behind' : '');

        body.innerHTML = `
            ${d.report !== undefined || t.state === 'completed' ? reportCard(t, d) : ''}

            <div class="tv-stats">
                <div class="tv-stat"><div class="tv-stat-icon blue"><span class="material-symbols-outlined">groups</span></div>
                    <div><div class="tv-stat-label">Members</div><div class="tv-stat-value">${members.length}</div></div></div>
                <div class="tv-stat"><div class="tv-stat-icon green"><span class="material-symbols-outlined">trending_up</span></div>
                    <div><div class="tv-stat-label">Progress</div><div class="tv-stat-value">${pct(t.percent)}%</div></div></div>
                <div class="tv-stat"><div class="tv-stat-icon navy"><span class="material-symbols-outlined">event</span></div>
                    <div><div class="tv-stat-label">Deadline</div><div class="tv-stat-value">${esc(t.deadline || '-')}</div>
                    <div class="tv-stat-sub ${esc(t.tone)}">${esc(t.time)}</div></div></div>
            </div>

            <div class="tv-card">
                <div class="tv-overall-row"><span>${esc(t.phase)}</span><span>${pct(t.percent)}%</span></div>
                <div class="tv-bar ${barClass}"><span style="width:${pct(t.percent)}%"></span></div>
                ${info ? `<div class="tv-info-grid">${info}</div>` : ''}
            </div>

            <div class="tv-card">
                <h4><span class="material-symbols-outlined">group</span> Team Members <span class="tv-count">${members.length} member${members.length === 1 ? '' : 's'}</span></h4>
                <div class="tv-members">${memberCards}</div>
            </div>

            ${(t.skills || []).length ? `
            <div class="tv-card">
                <h4><span class="material-symbols-outlined">bolt</span> Skills Covered</h4>
                <div class="tv-chips">${t.skills.map(s => `<span class="tm-chip">${esc(s)}</span>`).join('')}</div>
            </div>` : ''}

            <div class="tv-card">
                <h4><span class="material-symbols-outlined">history</span> Recent Activity</h4>
                ${acts}
            </div>
        `;

        wireReport(t);
        body.scrollTop = 0;
        modal.classList.add('open');
    }

    function close() { modal.classList.remove('open'); }

    // works for buttons redrawn after a status change too
    document.addEventListener('click', e => {
        const b = e.target.closest('[data-view-team]');
        if (b) open(Number(b.dataset.viewTeam));
    });
    modal.querySelectorAll('[data-close-view]').forEach(b => b.addEventListener('click', close));
    modal.addEventListener('click', e => { if (e.target === modal) close(); });
    document.addEventListener('keydown', e => { if (e.key === 'Escape' && modal.classList.contains('open')) close(); });
})();


/* =================================================================
   TEAM WORKSPACE – Kanban board + team chat (same page, with Back)
   Teams saved in the database: tasks + chat are saved on the server
   (Backend/TeamWorkspace.php) and are shared with the students of the team.
   Demo teams: tasks and messages are saved in this browser (localStorage).
================================================================= */
(function () {
    const TEAMS = ORG_DATA.WORKSPACE_TEAMS;
    const ORG_NAME = ORG_DATA.ORG_NAME;
    const COLORS = ['#1e3a5f', '#0f766e', '#7c3aed', '#b45309', '#be185d', '#2563eb', '#475569'];
    const COLS = [
        { key: 'todo',     label: 'To Do',       dot: '#6b7280' },
        { key: 'progress', label: 'In Progress', dot: '#ea580c' },
        { key: 'review',   label: 'Review',      dot: '#2563eb' },
        { key: 'done',     label: 'Done',        dot: '#16a34a' }
    ];

    // ---------- demo seed data ----------
    const SEED_TASKS = {
        'Nexus Systems': [
            ['Collect 12 months of supplier data', 'Research', 'done', 'Chamod Fernando', '2026-09-10', 100, 4],
            ['Demand forecast model v2', 'Development', 'progress', 'Nimal Silva', '2026-10-05', 60, 6],
            ['Supplier dashboard prototype', 'Design', 'review', 'Amaya Dissanayake', '2026-10-02', 100, 3],
            ['REST API for stock levels', 'Development', 'progress', 'Ravindu Pathirana', '2026-10-08', 45, 2],
            ['Write test cases for forecasting', 'Testing', 'todo', 'Ishara Gunawardena', '2026-10-12', 0, 0],
            ['Sprint 2 demo slides', 'Documentation', 'todo', 'Sanduni Perera', '2026-10-15', 0, 1]
        ],
        'Vortex Group': [
            ['Credential smart contract', 'Development', 'progress', 'Kasun Madushanka', '2026-09-22', 55, 5],
            ['Threat model & security review', 'Research', 'review', 'Malith Senanayake', '2026-09-25', 100, 2],
            ['Issuer portal UI', 'Design', 'todo', 'Tharushi Wijeratne', '2026-10-06', 0, 0],
            ['Verification API', 'Development', 'todo', 'Dulaj Bandara', '2026-10-10', 0, 1]
        ],
        'Quantum Analytics': [
            ['Sensor data pipeline', 'Development', 'done', 'Dilini Herath', '2026-08-15', 100, 3],
            ['Failure prediction model', 'Development', 'done', 'Ishan Rathnayake', '2026-08-30', 100, 5],
            ['Maintenance dashboard', 'Design', 'done', 'Nethmi Samarasinghe', '2026-09-10', 100, 2],
            ['Final report', 'Documentation', 'done', 'Yasith Abeysekara', '2026-09-18', 100, 4]
        ],
        'Alpha Ops': [
            ['Terraform modules for VPC', 'Development', 'done', 'Kavinda Jayasuriya', '2026-08-25', 100, 2],
            ['CI/CD with GitHub Actions', 'Development', 'done', 'Malsha Karunaratne', '2026-09-15', 100, 3],
            ['Blue-green deployment on K8s', 'Development', 'progress', 'Sahan Liyanage', '2026-10-10', 70, 4],
            ['Grafana alerts & dashboards', 'Testing', 'review', 'Tharindu Wickramaratne', '2026-10-12', 100, 1],
            ['Runbook for the ops team', 'Documentation', 'todo', 'Kavinda Jayasuriya', '2026-10-25', 0, 0]
        ]
    };
    const SEED_CHAT = {
        'Nexus Systems': [
            ['Sanduni Perera', 'Morning everyone! Sprint 2 review is on Friday. Please move finished cards to Review.', '09:05'],
            ['Nimal Silva', 'Forecast model v2 is training now, accuracy is already better than v1 👌', '09:20'],
            ['Amaya Dissanayake', 'Dashboard prototype is ready. Link is on the Review card.', '10:02']
        ],
        'Vortex Group': [
            ['Malith Senanayake', 'We are behind on Phase 1. Let’s finish the contract tests first.', '08:40'],
            ['Kasun Madushanka', 'Working on it, 2 tests still failing. Should be done by tonight.', '09:15']
        ],
        'Quantum Analytics': [
            ['Erandi Weerasinghe', 'Final report submitted. Thank you all for the great work! 🎉', '16:30']
        ],
        'Alpha Ops': [
            ['Kavinda Jayasuriya', 'Cluster is now in production. Please keep an eye on the alerts.', '11:10'],
            ['Tharindu Wickramaratne', 'Grafana alerts are set up, moved the card to Review.', '11:45']
        ]
    };

    const esc = t => { const d = document.createElement('div'); d.textContent = t == null ? '' : String(t); return d.innerHTML; };
    const initials = n => String(n || '').trim().split(/\s+/).slice(0, 2).map(w => w.charAt(0).toUpperCase()).join('');
    const colorFor = n => { let h = 0; for (const c of String(n)) h = (h * 31 + c.charCodeAt(0)) >>> 0; return COLORS[h % COLORS.length]; };
    const av = n => `<span class="tm-av" title="${esc(n)}" style="background:${colorFor(n)};">${esc(initials(n))}</span>`;
    const load = (k, fallback) => { try { const v = JSON.parse(localStorage.getItem(k)); return v || fallback; } catch (e) { return fallback; } };
    const save = (k, v) => { try { localStorage.setItem(k, JSON.stringify(v)); } catch (e) {} };

    const list  = document.getElementById('tmListView');
    const page  = document.getElementById('tmWorkspace');
    const board = document.getElementById('twBoard');
    const msgs  = document.getElementById('twMsgs');
    const input = document.getElementById('twInput');
    const chat  = document.getElementById('twChat');
    const memberFilter = document.getElementById('twMemberFilter');
    const layout = document.getElementById('twLayout');
    const chatToggle = document.getElementById('twChatToggle');

    // 'tasks' = Kanban full width (no chat)   'chat' = Kanban + chat on the side
    function setMode(mode) {
        const chatOn = mode === 'chat';
        layout.classList.toggle('tasks-only', !chatOn);
        chatToggle.classList.toggle('on', chatOn);
        chatToggle.querySelector('span:last-child').textContent = chatOn ? 'Hide Chat' : 'Team Chat';
        if (chatOn) {
            msgs.scrollTop = msgs.scrollHeight;
            chat.classList.add('focus');
            setTimeout(() => chat.classList.remove('focus'), 1500);
            setTimeout(() => input.focus({ preventScroll: true }), 300);
        }
    }
    chatToggle.addEventListener('click', () => {
        const next = layout.classList.contains('tasks-only') ? 'chat' : 'tasks';
        setMode(next);
        if (next === 'chat' && window.innerWidth <= 1150) chat.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });

    let team = null, teamIndex = -1, tasks = [], messages = [], listScroll = 0;

    // ---------- database teams: talk to the server ----------
    const isDbTeam = () => teamIndex >= 0 && TEAMS[teamIndex].id != null;
    const taskCache = {};        // team id -> latest tasks from the server (used by Delay Review)
    let lastMsgId = 0;           // newest chat message we already have
    let pollTimer = null;

    // POST to this page (teams.php) -> Backend/TeamWorkspace.php answers with JSON
    function api(action, data) {
        const fd = new FormData();
        fd.append('ws_action', action);
        Object.keys(data || {}).forEach(k => fd.append(k, data[k]));
        return fetch('teams.php', { method: 'POST', body: fd })
            .then(r => r.json())
            .catch(() => ({ ok: false, message: 'Could not reach the server. Check your connection.' }));
    }

    // server task -> the shape the board uses
    const fromServer = t => ({ id: String(t.id), title: t.title, tag: '', description: t.description || '', col: t.col,
                               who: t.who || '', assignee: t.assignee || '', due: t.due || '', progress: t.progress, comments: 0 });

    function setServerTasks(list) {
        taskCache[TEAMS[teamIndex].id] = list;
        tasks = list.map(fromServer);
    }

    // server messages -> chat rows, with a date line when the day changes
    let lastDate = '';
    function addServerMessages(list) {
        const today = new Date().toLocaleDateString('en-US', { month: 'short', day: '2-digit', year: 'numeric' });
        list.forEach(mg => {
            if (mg.date !== lastDate) {
                messages.push({ sys: true, text: mg.date === today ? 'Today' : mg.date });
                lastDate = mg.date;
            }
            messages.push({ me: mg.me, who: mg.who, text: mg.text, time: mg.time,
                            tag: mg.role === 'organization' && !mg.me ? 'Organization' : '' });
            lastMsgId = Math.max(lastMsgId, mg.id);
        });
    }

    // get new messages + the current tasks (also moves made by the students)
    function refreshFromServer() {
        if (!isDbTeam() || page.hidden) return Promise.resolve();
        const id = TEAMS[teamIndex].id;
        return api('load', { team_id: id, after_id: lastMsgId }).then(res => {
            if (!res.ok || !isDbTeam() || TEAMS[teamIndex].id !== id) return;
            if (res.messages.length) { addServerMessages(res.messages); renderChat(); }
            // shared files (team_files) – also the ones the students uploaded
            if (JSON.stringify(res.files) !== JSON.stringify(files)) { files = res.files; renderFiles(); }
            const before = JSON.stringify(tasks);
            setServerTasks(res.tasks);
            // don't redraw while a task menu is open or a card is being dragged
            if (JSON.stringify(tasks) !== before && !board.querySelector('.tw-menu, .dragging')) renderBoard();
        });
    }

    function startPolling() {
        stopPolling();
        pollTimer = setInterval(() => { if (!document.hidden) refreshFromServer(); }, 4000);   // every 4 seconds
    }
    function stopPolling() { if (pollTimer) { clearInterval(pollTimer); pollTimer = null; } }

    function showChatError(text) {
        const el = document.getElementById('twChatErr');
        el.textContent = text || '';
    }
    const taskKey = () => 'sb_team_tasks_' + team.name;
    const chatKey = () => 'sb_team_chat_' + team.name;
    const fileKey = () => 'sb_team_files_' + team.name;
    let files = [];
    const fileUrls = {};   // files uploaded in this visit can be opened (id -> object URL)

    function seedTasks() {
        return (SEED_TASKS[team.name] || []).map((r, i) => ({
            id: 'd' + i, title: r[0], tag: r[1], col: r[2], who: r[3], due: r[4], progress: r[5], comments: r[6]
        }));
    }
    // helpers used by the Delay Review popup
    window.tmTasksFor = function (i) {
        if (TEAMS[i].id != null) return (taskCache[TEAMS[i].id] || TEAMS[i].tasks || []).map(fromServer);   // database team
        const name = TEAMS[i].name;
        return load('sb_team_tasks_' + name, null) || (SEED_TASKS[name] || []).map((r, k) => ({
            id: 'd' + k, title: r[0], tag: r[1], col: r[2], who: r[3], due: r[4], progress: r[5], comments: r[6] }));
    };
    window.tmPostToChat = function (i, text) {
        const name = TEAMS[i].name;
        const list = load('sb_team_chat_' + name, null) ||
            [{ sys: true, text: 'Today' }].concat((SEED_CHAT[name] || []).map(r => ({ who: r[0], text: r[1], time: r[2] })));
        const now = new Date();
        list.push({ me: true, who: ORG_NAME, text: text,
                    time: String(now.getHours()).padStart(2, '0') + ':' + String(now.getMinutes()).padStart(2, '0') });
        save('sb_team_chat_' + name, list);
    };
    window.tmOpenWorkspace = (i, focus) => openWorkspace(i, focus);

    function seedChat() {
        const base = [{ sys: true, text: 'Today' }];
        return base.concat((SEED_CHAT[team.name] || []).map(r => ({ who: r[0], text: r[1], time: r[2] })));
    }

    // ---------------- open / close ----------------
    function openWorkspace(i, focus, push) {
        team = TEAMS[i]; teamIndex = i;
        if (team && window.tmEffective) team = Object.assign(window.tmEffective(i, team), { name: TEAMS[i].name, displayName: window.tmEffective(i, team).name });
        if (!team) return;

        stopPolling();
        showChatError('');
        const dbTeam = isDbTeam();
        document.getElementById('twUploadErr').textContent = '';
        if (dbTeam) {
            setServerTasks(TEAMS[i].tasks || []);     // tasks that came with the page, refreshed below
            messages = []; lastMsgId = 0; lastDate = '';
        } else {
            tasks    = load(taskKey(), null) || seedTasks();
            messages = load(chatKey(), null) || seedChat();
        }

        document.getElementById('twCrumb').textContent = team.displayName || team.name;
        document.getElementById('twPhase').textContent = team.phase || 'Active Sprint';
        document.getElementById('twProject').textContent = team.project;
        document.getElementById('twTeamName').textContent = (team.displayName || team.name) + ' · ' + team.members.length + ' members';
        document.getElementById('twAvatars').innerHTML = team.members.slice(0, 5).map(m => av(m.name)).join('');

        memberFilter.innerHTML = '<option value="all">All members</option>' +
            team.members.map(m => `<option value="${esc(m.name)}">${esc(m.name)}</option>`).join('');
        // database team: the task is given by email (saved on the server); demo team: by name
        document.getElementById('twTWho').innerHTML = '<option value="">Unassigned</option>' +
            team.members.map(m => `<option value="${esc(dbTeam ? m.email : m.name)}">${esc(m.name)}</option>`).join('');

        files = dbTeam ? [] : (load(fileKey(), null) || seedFiles());
        renderBoard();
        renderChat();
        renderFiles();

        listScroll = window.scrollY;
        list.hidden = true;
        page.hidden = false;

        if (push !== false) history.pushState({ teamWork: i, focus: focus }, '', '#team-' + i + '-' + focus);

        window.scrollTo(0, 0);
        setMode(focus === 'chat' ? 'chat' : 'tasks');
        if (focus === 'chat' && window.innerWidth <= 1150) chat.scrollIntoView({ behavior: 'smooth', block: 'start' });

        if (dbTeam) {                       // load the chat + latest tasks, then keep them up to date
            msgs.innerHTML = '<div class="tw-sys">Loading messages…</div>';
            refreshFromServer().then(() => { if (!messages.length) renderChat(); });
            startPolling();
        }
    }

    function closeWorkspace() {
        stopPolling();
        page.hidden = true;
        list.hidden = false;
        window.scrollTo(0, listScroll);
    }

    document.addEventListener('click', e => {
        const b = e.target.closest('[data-work-team]');
        if (b) openWorkspace(Number(b.dataset.workTeam), b.dataset.workFocus);
    });

    document.getElementById('twBack').addEventListener('click', function () {
        if (history.state && history.state.teamWork !== undefined) history.back();
        else { closeWorkspace(); history.replaceState(null, '', location.pathname); }
    });
    window.addEventListener('popstate', function (e) {
        if (e.state && e.state.teamWork !== undefined) openWorkspace(e.state.teamWork, e.state.focus, false);
        else if (!page.hidden) closeWorkspace();
    });

    // ---------------- Kanban ----------------
    function dueLabel(t) {
        if (!t.due) return '';
        const days = Math.round((new Date(t.due + 'T00:00:00') - new Date(new Date().toDateString())) / 86400000);
        if (t.col === 'done') return new Date(t.due + 'T00:00:00').toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
        if (days < 0)  return `<span class="late">${Math.abs(days)}d overdue</span>`;
        if (days === 0) return '<span class="late">Due today</span>';
        if (days === 1) return 'Due tomorrow';
        return 'Due in ' + days + 'd';
    }

    function renderBoard() {
        const who = memberFilter.value;
        board.innerHTML = COLS.map(c => {
            const items = tasks.filter(t => t.col === c.key && (who === 'all' || t.who === who));
            return `
            <div class="tw-col" data-col="${c.key}">
                <div class="tw-col-head">
                    <span><span class="dot" style="background:${c.dot}"></span>${c.label}</span>
                    <span class="tw-col-count">${items.length}</span>
                </div>
                ${items.length ? items.map(t => `
                    <div class="tw-card ${t.col}" draggable="${canDrag(t)}" data-id="${esc(t.id)}">
                        <button type="button" class="tw-kebab" data-menu="${esc(t.id)}" aria-label="Task options">⋯</button>
                        ${t.tag ? `<span class="tw-tag ${esc(t.tag)}">${esc(t.tag)}</span>` : ''}
                        <div class="tw-card-title">${esc(t.title)}</div>
                        ${t.description ? `<div class="tw-card-desc">${esc(t.description)}</div>` : ''}
                        ${t.col === 'progress' ? `<div class="tw-card-bar"><span style="width:${Number(t.progress) || 0}%"></span></div>` : ''}
                        <div class="tw-card-foot">
                            <span class="left">${t.due ? '<span class="material-symbols-outlined">event</span>' + dueLabel(t) : ''}
                                ${t.comments ? ' &nbsp;<span class="material-symbols-outlined">chat_bubble</span>' + Number(t.comments) : ''}</span>
                            ${t.who ? av(t.who) : ''}
                        </div>
                        ${t.col === 'review' && isDbTeam() ? `<div class="tw-card-actions">
                            <button type="button" class="tw-open-review" data-open-task="${esc(t.id)}"><span class="material-symbols-outlined">rate_review</span>Open &amp; Review</button>
                        </div>` : ''}
                    </div>`).join('') : '<div class="tw-col-empty">No tasks</div>'}
            </div>`;
        }).join('');

        const done = tasks.filter(t => t.col === 'done').length;
        const pct = tasks.length ? Math.round(done / tasks.length * 100) : 0;
        document.getElementById('twPct').textContent = pct + '%';
        document.getElementById('twBar').style.width = pct + '%';
        document.getElementById('twDone').textContent = done + ' tasks done';
        document.getElementById('twPending').textContent = (tasks.length - done) + ' pending';
    }

    // What the organization can do (the same rules as TW_MOVES in Backend/TeamWorkspace.php).
    // The students move their own tasks: Start (To Do -> In Progress) and Send for Review.
    const ORG_MOVES = { review: ['done', 'progress'], done: ['progress'] };
    const canMove = (t, col) => !isDbTeam() || (ORG_MOVES[t.col] || []).includes(col);
    const canDrag = t => !isDbTeam() || !!ORG_MOVES[t.col];
    function whyNot(t, col) {
        if (col === 'done') return 'A task can be marked Done after the student sends it for Review.';
        return 'Students move their own tasks (Start / Send for Review). You can approve or send back tasks in Review.';
    }

    function moveTask(id, col, note) {
        const t = tasks.find(x => String(x.id) === String(id));
        if (!t || t.col === col) return;
        if (!canMove(t, col)) { if (window.tmToast) window.tmToast(whyNot(t, col)); return; }
        // Review -> In Progress needs a note for the student first
        if (isDbTeam() && t.col === 'review' && col === 'progress' && note === undefined) { openBack(t); return; }
        t.col = col;
        if (col === 'done' || col === 'review') t.progress = 100;
        if (col === 'progress' && !t.progress) t.progress = 10;
        if (col === 'todo') t.progress = 0;
        renderBoard();                                   // show it straight away
        if (isDbTeam()) {
            api('move_task', { task_id: id, col: col, note: note || '' }).then(res => {
                if (res.ok) setServerTasks(res.tasks); else showChatError(res.message);
                renderBoard();
            });
        } else {
            save(taskKey(), tasks);
        }
    }

    memberFilter.addEventListener('change', renderBoard);

    // drag & drop
    board.addEventListener('dragstart', e => {
        const card = e.target.closest('.tw-card'); if (!card) return;
        card.classList.add('dragging');
        e.dataTransfer.setData('text/plain', card.dataset.id);
    });
    board.addEventListener('dragend', e => { const c = e.target.closest('.tw-card'); if (c) c.classList.remove('dragging'); });
    board.addEventListener('dragover', e => {
        const col = e.target.closest('.tw-col'); if (!col) return;
        e.preventDefault();
        board.querySelectorAll('.tw-col').forEach(c => c.classList.toggle('drop', c === col));
    });
    board.addEventListener('dragleave', e => { if (!board.contains(e.relatedTarget)) board.querySelectorAll('.tw-col').forEach(c => c.classList.remove('drop')); });
    board.addEventListener('drop', e => {
        const col = e.target.closest('.tw-col'); if (!col) return;
        e.preventDefault();
        board.querySelectorAll('.tw-col').forEach(c => c.classList.remove('drop'));
        moveTask(e.dataTransfer.getData('text/plain'), col.dataset.col);
    });

    // ---------- Task details: open a card to see what the student did, then Approve / Send back ----------
    const detailModal = document.getElementById('twDetailModal');
    const COL_LABEL = { todo: 'To Do', progress: 'In Progress', review: 'Review', done: 'Done' };
    const linkify = url => /^https?:\/\//i.test(url) ? `<a href="${esc(url)}" target="_blank" rel="noopener">${esc(url)}</a>` : esc(url);
    let detailId = null;

    function openTaskDetail(id) {
        const t = tasks.find(x => String(x.id) === String(id));
        if (!t || !isDbTeam()) return;
        detailId = t.id;
        document.getElementById('twDetailTitle').textContent = t.title;
        document.getElementById('twDetailSub').textContent = COL_LABEL[t.col] + (t.who ? ' · ' + t.who : ' · Not given to anyone yet');
        document.getElementById('twDetailBody').innerHTML = '<div class="tw-d-empty">Loading…</div>';
        document.getElementById('twDetailFoot').innerHTML = '';
        detailModal.classList.add('open');

        api('task_detail', { task_id: t.id }).then(res => {
            if (!res.ok) { document.getElementById('twDetailBody').innerHTML = `<div class="tm-err">${esc(res.message)}</div>`; return; }
            const tk = res.task || t, sub = res.submission;
            const due = tk.due ? new Date(tk.due + 'T00:00:00').toLocaleDateString('en-US', { month: 'short', day: '2-digit', year: 'numeric' }) : 'No due date';
            document.getElementById('twDetailBody').innerHTML = `
                <div class="tw-d-grid">
                    <div><span>Status</span><b>${esc(COL_LABEL[tk.col])}</b></div>
                    <div><span>Student</span><b>${esc(tk.who || '—')}</b></div>
                    <div><span>Due date</span><b>${esc(due)}</b></div>
                </div>
                ${tk.description ? `<div class="tw-d-sec"><h4>Task</h4><p>${esc(tk.description)}</p></div>` : ''}
                <div class="tw-d-sec">
                    <h4>What the student did</h4>
                    ${sub ? `<div class="tw-d-sub">
                                <p>${esc(sub.note)}</p>
                                ${sub.link ? `<div class="tw-d-link"><span class="material-symbols-outlined">link</span>${linkify(sub.link)}</div>` : ''}
                                <small>Sent by ${esc(sub.who)} · ${esc(sub.when)}</small>
                             </div>`
                          : '<div class="tw-d-empty">The student has not sent this task for review yet.</div>'}
                </div>
                <div class="tw-d-sec">
                    <h4>Files shared by ${esc(tk.who || 'the student')}</h4>
                    ${res.files.length ? res.files.map(f => `<a class="tw-d-file" href="../../${esc(f.path)}" target="_blank" rel="noopener">
                            <span class="material-symbols-outlined">description</span><span>${esc(f.name)}</span><small>${esc(f.when)}</small></a>`).join('')
                        : '<div class="tw-d-empty">No files shared yet.</div>'}
                </div>
                <div class="tw-d-sec">
                    <h4>History</h4>
                    ${res.history.length ? '<ul class="tw-d-hist">' + res.history.map(h => `<li><b>${esc(h.who)}</b> ${esc(h.text).replace(/\n/g, '<br>')}<small>${esc(h.when)}</small></li>`).join('') + '</ul>'
                        : '<div class="tw-d-empty">Nothing yet.</div>'}
                </div>`;
            document.getElementById('twDetailFoot').innerHTML = tk.col === 'review'
                ? `<button type="button" class="tm-btn" data-d-back><span class="material-symbols-outlined">undo</span>Send back</button>
                   <button type="button" class="tm-btn solid" data-d-approve><span class="material-symbols-outlined">check</span>Approve</button>`
                : '<button type="button" class="tm-btn solid" data-close-detail>Close</button>';
        });
    }
    detailModal.addEventListener('click', e => {
        if (e.target === detailModal || e.target.closest('[data-close-detail]')) { detailModal.classList.remove('open'); return; }
        if (e.target.closest('[data-d-approve]')) { detailModal.classList.remove('open'); moveTask(detailId, 'done'); }
        if (e.target.closest('[data-d-back]'))    { detailModal.classList.remove('open'); moveTask(detailId, 'progress'); }
    });

    // click a card (not its ⋯ menu) or "Open & Review" -> task details
    board.addEventListener('click', e => {
        if (e.target.closest('[data-menu]') || e.target.closest('.tw-menu')) return;
        const open = e.target.closest('[data-open-task]');
        const card = e.target.closest('.tw-card');
        if (open) { e.stopPropagation(); openTaskDetail(open.dataset.openTask); }
        else if (card && isDbTeam()) openTaskDetail(card.dataset.id);
    });

    // Send back popup: a short note for the student (also posted in the team chat)
    const backModal = document.getElementById('twBackModal');
    const backForm  = document.getElementById('twBackForm');
    let backTask = null;
    function openBack(t) {
        backTask = t;
        document.getElementById('twBackTitle').textContent = 'Task: ' + t.title;
        document.getElementById('twBackNote').value = '';
        document.getElementById('twBackErr').textContent = '';
        backModal.classList.add('open');
        setTimeout(() => document.getElementById('twBackNote').focus(), 50);
    }
    backModal.querySelectorAll('[data-close-back]').forEach(b => b.addEventListener('click', () => backModal.classList.remove('open')));
    backModal.addEventListener('click', e => { if (e.target === backModal) backModal.classList.remove('open'); });
    backForm.addEventListener('submit', e => {
        e.preventDefault();
        const note = document.getElementById('twBackNote').value.trim();
        if (note.length < 5) { document.getElementById('twBackErr').textContent = 'Please tell the student what to change (at least 5 characters).'; return; }
        backModal.classList.remove('open');
        if (backTask) moveTask(backTask.id, 'progress', note);
    });

    // ⋯ menu (move / delete) – also works on phones
    board.addEventListener('click', e => {
        const k = e.target.closest('[data-menu]');
        board.querySelectorAll('.tw-menu').forEach(mn => mn.remove());
        if (!k) return;
        e.stopPropagation();
        const id = k.dataset.menu;
        const t = tasks.find(x => String(x.id) === id);
        const menu = document.createElement('div');
        menu.className = 'tw-menu';
        const MOVE_LABEL = { done: ['check', 'Approve (Done)'], progress: [t.col === 'done' ? 'replay' : 'undo', t.col === 'done' ? 'Reopen task' : 'Send back'] };
        const moves = isDbTeam() ? (ORG_MOVES[t.col] || []) : COLS.filter(c => c.key !== t.col).map(c => c.key);
        menu.innerHTML = moves.map(k => {
                const lb = isDbTeam() ? MOVE_LABEL[k] : ['arrow_forward', 'Move to ' + COLS.find(c => c.key === k).label];
                return `<button type="button" data-move="${k}"><span class="material-symbols-outlined">${lb[0]}</span>${lb[1]}</button>`;
            }).join('') +
            (moves.length ? '' : '<div class="tw-menu-note">The student moves this task.</div>') +
            '<hr><button type="button" class="danger" data-del><span class="material-symbols-outlined">delete</span>Delete task</button>';
        menu.addEventListener('click', ev => {
            ev.stopPropagation();
            const mv = ev.target.closest('[data-move]');
            if (mv) { menu.remove(); moveTask(id, mv.dataset.move, t.col === 'done' ? '' : undefined); return; }
            const del = ev.target.closest('[data-del]');
            if (del) {
                // first click asks, second click deletes (no browser popup)
                if (!del.classList.contains('confirm')) {
                    del.classList.add('confirm');
                    del.lastChild.textContent = 'Click again to delete';
                    return;
                }
                if (isDbTeam()) {
                    api('delete_task', { task_id: id }).then(res => {
                        if (res.ok) setServerTasks(res.tasks); else showChatError(res.message);
                        renderBoard();
                    });
                } else {
                    tasks = tasks.filter(x => x.id !== id); save(taskKey(), tasks);
                }
                menu.remove();
                renderBoard();
            }
        });
        k.closest('.tw-card').appendChild(menu);
    });
    document.addEventListener('click', () => board.querySelectorAll('.tw-menu').forEach(mn => mn.remove()));

    // New Task popup
    const taskModal = document.getElementById('twTaskModal');
    const taskForm  = document.getElementById('twTaskForm');
    document.getElementById('twNewTask').addEventListener('click', () => {
        taskForm.reset(); document.getElementById('twTErr').textContent = ''; document.getElementById('twTDueErr').textContent = '';
        // database teams have a Description (team_tasks.description); demo teams have a Type
        document.getElementById('twTDescWrap').hidden = !isDbTeam();
        document.getElementById('twTTagWrap').hidden = isDbTeam();
        taskModal.classList.add('open');
        setTimeout(() => document.getElementById('twTTitle').focus(), 50);
    });
    taskModal.querySelectorAll('[data-close-task]').forEach(b => b.addEventListener('click', () => taskModal.classList.remove('open')));
    taskModal.addEventListener('click', e => { if (e.target === taskModal) taskModal.classList.remove('open'); });
    taskForm.addEventListener('submit', e => {
        e.preventDefault();
        const title = document.getElementById('twTTitle').value.trim();
        const due   = document.getElementById('twTDue').value;
        const now = new Date();   // local date (not UTC)
        const today = now.getFullYear() + '-' + String(now.getMonth() + 1).padStart(2, '0') + '-' + String(now.getDate()).padStart(2, '0');
        const teamDl = team.deadline_raw || '';
        document.getElementById('twTErr').textContent = '';
        document.getElementById('twTDueErr').textContent = '';
        let ok = true;
        if (!title) { document.getElementById('twTErr').textContent = 'Please enter a task title.'; ok = false; }
        else if (title.length < 3) { document.getElementById('twTErr').textContent = 'The task title must be at least 3 characters.'; ok = false; }
        // due date is optional, but it can't be in the past or after the team deadline
        if (due && due < today) { document.getElementById('twTDueErr').textContent = 'The due date can’t be in the past.'; ok = false; }
        else if (due && teamDl && due > teamDl) { document.getElementById('twTDueErr').textContent = 'The due date can’t be after the team deadline.'; ok = false; }
        if (!ok) return;
        const col = 'todo';                     // a new task always starts in To Do

        if (isDbTeam()) {                       // save on the server (the student gets a notification)
            const submitBtn = taskForm.querySelector('[type="submit"]');
            submitBtn.disabled = true;
            api('add_task', {
                team_id: TEAMS[teamIndex].id, title: title, description: document.getElementById('twTDesc').value.trim(),
                assignee: document.getElementById('twTWho').value, due: due
            }).then(res => {
                submitBtn.disabled = false;
                if (!res.ok) { document.getElementById('twTErr').textContent = res.message; return; }
                setServerTasks(res.tasks);
                renderBoard();
                taskModal.classList.remove('open');
            });
            return;
        }

        tasks.push({
            id: 'n' + Date.now(), title: title, tag: document.getElementById('twTTag').value, col: col,
            who: document.getElementById('twTWho').value, due: document.getElementById('twTDue').value,
            progress: col === 'done' || col === 'review' ? 100 : (col === 'progress' ? 10 : 0), comments: 0
        });
        save(taskKey(), tasks);
        renderBoard();
        taskModal.classList.remove('open');
    });

    // ---------------- Shared Assets ----------------
    const SEED_FILES = {
        'Nexus Systems':     [['Supplier_Data_Analysis.pdf', 2.4, 'Chamod Fernando', '2 days ago'], ['Dashboard_Prototype.fig', 8.1, 'Amaya Dissanayake', 'Yesterday'], ['Forecast_Model_v2.zip', 6.7, 'Nimal Silva', '2h ago']],
        'Vortex Group':      [['Threat_Model.pdf', 1.2, 'Malith Senanayake', '3 days ago'], ['Contract_Architecture.png', 0.9, 'Kasun Madushanka', '5 days ago']],
        'Quantum Analytics': [['Final_Report.pdf', 4.2, 'Yasith Abeysekara', 'Sep 18'], ['Demo_Video.mp4', 18.5, 'Nethmi Samarasinghe', 'Sep 16']],
        'Alpha Ops':         [['Infrastructure_Diagram.png', 1.6, 'Kavinda Jayasuriya', 'Yesterday'], ['Ops_Runbook_Draft.docx', 0.4, 'Malsha Karunaratne', '4h ago']]
    };
    function seedFiles() {
        return (SEED_FILES[team.name] || []).map((f, i) => ({ id: 's' + i, name: f[0], size: f[1] * 1024 * 1024, by: f[2], when: f[3] }));
    }
    function fileType(name) {
        const ext = (name.split('.').pop() || '').toLowerCase();
        if (ext === 'pdf') return ['pdf', 'picture_as_pdf'];
        if (['png', 'jpg', 'jpeg', 'gif', 'webp', 'fig'].includes(ext)) return ['img', 'image'];
        if (['doc', 'docx', 'txt', 'ppt', 'pptx', 'xls', 'xlsx', 'csv'].includes(ext)) return ['doc', 'description'];
        if (ext === 'mp4') return ['vid', 'movie'];
        if (ext === 'zip') return ['zip', 'folder_zip'];
        return ['other', 'draft'];
    }
    const sizeText = b => b < 1024 * 1024 ? Math.max(1, Math.round(b / 1024)) + ' KB' : (b / 1024 / 1024).toFixed(1) + ' MB';

    function renderFiles() {
        const box = document.getElementById('twFiles');
        document.getElementById('twFilesCount').textContent = files.length + ' file' + (files.length === 1 ? '' : 's');
        box.innerHTML = files.length ? files.map(f => {
            const [cls, icon] = fileType(f.name);
            // database team: the file is on the server (f.path); demo team: only files added in this visit
            const canOpen = isDbTeam() ? !!f.path : !!fileUrls[f.id];
            const canDelete = isDbTeam() ? !!f.can_delete : true;
            return `
            <div class="tw-file">
                <div class="tw-file-icon ${cls}"><span class="material-symbols-outlined">${icon}</span></div>
                <div class="tw-file-info">
                    <div class="tw-file-name" title="${esc(f.name)}">${esc(f.name)}</div>
                    <div class="tw-file-meta">${sizeText(f.size)} · ${esc(f.by)} · ${esc(f.when)}</div>
                </div>
                <button type="button" class="tw-file-btn" data-open-file="${esc(f.id)}" ${canOpen ? '' : 'disabled'}
                        title="${canOpen ? 'Open / download' : 'Demo file'}"><span class="material-symbols-outlined">download</span></button>
                ${canDelete ? `<button type="button" class="tw-file-btn danger" data-del-file="${esc(f.id)}" title="Remove"><span class="material-symbols-outlined">delete</span></button>` : ''}
            </div>`;
        }).join('') : '<div class="tw-files-empty">No files shared yet.</div>';
    }

    function addFiles(list) {
        const errEl = document.getElementById('twUploadErr');
        const errors = [];

        if (isDbTeam()) {                       // upload to the server (team_files), the whole team can see them
            const toSend = [];
            Array.from(list).forEach(file => {
                if (file.size > 10 * 1024 * 1024) errors.push(`"${file.name}" is larger than 10MB.`);
                else toSend.push(file);
            });
            errEl.textContent = errors.join(' ');
            if (!toSend.length) return;
            errEl.textContent = (errors.join(' ') + ' Uploading…').trim();

            // one file per request, so many files together never go over the server's upload limit
            const teamId = TEAMS[teamIndex].id;
            let chain = Promise.resolve();
            toSend.forEach(file => {
                chain = chain.then(() => {
                    const fd = new FormData();
                    fd.append('ws_action', 'upload_file');
                    fd.append('team_id', teamId);
                    fd.append('files[]', file);
                    return fetch('teams.php', { method: 'POST', body: fd })
                        .then(r => r.json())
                        .catch(() => ({ ok: false, message: `"${file.name}" could not be uploaded.` }))
                        .then(res => {
                            if (!res.ok) { errors.push(res.message); return; }
                            errors.push(...(res.errors || []));
                            files = res.files;
                            renderFiles();
                        });
                });
            });
            chain.then(() => { errEl.textContent = errors.join(' '); });
            return;
        }

        Array.from(list).forEach(file => {
            if (file.size > 10 * 1024 * 1024) { errors.push(`"${file.name}" is larger than 10MB.`); return; }
            const id = 'u' + Date.now() + Math.random().toString(36).slice(2, 6);
            fileUrls[id] = URL.createObjectURL(file);
            files.unshift({ id: id, name: file.name, size: file.size, by: 'You', when: 'Just now' });
        });
        errEl.textContent = errors.join(' ');
        save(fileKey(), files);
        renderFiles();
    }

    const drop = document.getElementById('twDrop');
    const fileInput = document.getElementById('twFileInput');
    fileInput.addEventListener('change', () => { addFiles(fileInput.files); fileInput.value = ''; });
    ['dragenter', 'dragover'].forEach(ev => drop.addEventListener(ev, e => { e.preventDefault(); drop.classList.add('over'); }));
    ['dragleave', 'drop'].forEach(ev => drop.addEventListener(ev, e => { e.preventDefault(); drop.classList.remove('over'); }));
    drop.addEventListener('drop', e => addFiles(e.dataTransfer.files));

    document.getElementById('twFiles').addEventListener('click', e => {
        const o = e.target.closest('[data-open-file]');
        if (o) {
            const f = files.find(x => String(x.id) === o.dataset.openFile);
            if (isDbTeam() && f && f.path) window.open('../../' + f.path, '_blank');
            else if (fileUrls[o.dataset.openFile]) window.open(fileUrls[o.dataset.openFile], '_blank');
        }
        const d = e.target.closest('[data-del-file]');
        if (!d) return;
        // ask first in a popup (no browser popup), then remove
        const f = files.find(x => String(x.id) === String(d.dataset.delFile));
        delFileId = d.dataset.delFile;
        document.getElementById('twFileDelName').textContent = f ? f.name : '';
        document.getElementById('twFileDelErr').textContent = '';
        document.getElementById('twFileDelOk').disabled = false;
        fileDelModal.classList.add('open');
    });

    // "Remove this file?" popup
    const fileDelModal = document.getElementById('twFileDelModal');
    let delFileId = null;
    const closeFileDel = () => { fileDelModal.classList.remove('open'); delFileId = null; };
    fileDelModal.querySelectorAll('[data-close-filedel]').forEach(b => b.addEventListener('click', closeFileDel));
    fileDelModal.addEventListener('click', e => { if (e.target === fileDelModal) closeFileDel(); });
    document.getElementById('twFileDelOk').addEventListener('click', function () {
        if (!delFileId) return;
        const id = delFileId;
        if (isDbTeam()) {
            this.disabled = true;
            api('delete_file', { file_id: id }).then(res => {
                if (res.ok) { files = res.files; renderFiles(); closeFileDel(); if (window.tmToast) window.tmToast('File removed'); }
                else { document.getElementById('twFileDelErr').textContent = res.message; this.disabled = false; }
            });
        } else {
            files = files.filter(f => String(f.id) !== String(id));
            save(fileKey(), files);
            renderFiles();
            closeFileDel();
        }
    });

    // ---------------- Chat ----------------
    function renderChat() {
        if (!messages.length) {
            msgs.innerHTML = '<div class="tw-sys">No messages yet. Say hello to the team!</div>';
            return;
        }
        msgs.innerHTML = messages.map(mg => {
            if (mg.sys) return `<div class="tw-sys">${esc(mg.text)}</div>`;
            const me = mg.me === true;
            return `
            <div class="tw-msg ${me ? 'me' : ''}">
                <div class="tw-msg-meta">
                    ${me ? `<span>${esc(mg.time)}</span><strong>You</strong>` : `${av(mg.who)}<strong>${esc(mg.who)}</strong>${mg.tag ? `<em>${esc(mg.tag)}</em>` : ''}<span>${esc(mg.time)}</span>`}
                </div>
                <div class="tw-bubble">${esc(mg.text)}</div>
            </div>`;
        }).join('');
        msgs.scrollTop = msgs.scrollHeight;
    }

    document.getElementById('twSend').addEventListener('submit', e => {
        e.preventDefault();
        const text = input.value.trim();
        showChatError('');
        if (!text) return;
        if (text.length > 500) { showChatError('A message can have up to 500 characters.'); return; }

        if (isDbTeam()) {                       // save on the server, then show it (with any new replies)
            const sendBtn = e.target.querySelector('button');
            sendBtn.disabled = true;
            api('send_message', { team_id: TEAMS[teamIndex].id, message: text }).then(res => {
                sendBtn.disabled = false;
                if (!res.ok) { showChatError(res.message); return; }
                input.value = '';
                refreshFromServer();
            });
            return;
        }

        const now = new Date();
        messages.push({ me: true, who: ORG_NAME, text: text,
                        time: String(now.getHours()).padStart(2, '0') + ':' + String(now.getMinutes()).padStart(2, '0') });
        save(chatKey(), messages);
        input.value = '';
        renderChat();
    });

    // (runs last, after everything above is ready)
    // open straight from a link like teams.php#team-1-chat (e.g. after refresh)
    const m = location.hash.match(/^#team-(\d+)-(tasks|chat)$/);
    if (m && TEAMS[Number(m[1])]) {
        history.replaceState({ teamWork: Number(m[1]), focus: m[2] }, '', location.hash);
        openWorkspace(Number(m[1]), m[2], false);
    }
})();


/* =================================================================
   ⋮ MENU – Edit Team, Change Status
   Database teams are saved on the server.
   Demo teams (not in the database) are changed in this browser only.
================================================================= */
(function () {
    const TEAMS = ORG_DATA.MENU_TEAMS;
    const CANDIDATES = ORG_DATA.CANDIDATES;
    const PROJECT_MAX = ORG_DATA.PROJECT_MAX;

    const STATUS = { ontrack: ['On Track', '#16a34a'], behind: ['Behind Schedule', '#dc2626'], completed: ['Completed', '#6b7280'] };
    const COLORS = ['#1e3a5f', '#0f766e', '#7c3aed', '#b45309', '#be185d', '#2563eb', '#475569'];
    const OV_KEY = 'sb_demo_team_overrides';

    const esc = t => { const d = document.createElement('div'); d.textContent = t == null ? '' : String(t); return d.innerHTML; };
    const initials = n => String(n || '').trim().split(/\s+/).slice(0, 2).map(w => w.charAt(0).toUpperCase()).join('');
    const colorFor = n => { let h = 0; for (const c of String(n)) h = (h * 31 + c.charCodeAt(0)) >>> 0; return COLORS[h % COLORS.length]; };
    const loadOv = () => { try { return JSON.parse(localStorage.getItem(OV_KEY)) || {}; } catch (e) { return {}; } };
    const saveOv = o => { try { localStorage.setItem(OV_KEY, JSON.stringify(o)); } catch (e) {} };
    const isDb = t => t.id !== null && t.id !== undefined;
    const fmtShort = ymd => ymd ? new Date(ymd + 'T00:00:00').toLocaleDateString('en-US', { month: 'short', day: '2-digit' }) : '';

    // The popups for "View" / Kanban read the edited demo name + status from here too
    window.tmEffective = function (i, t) {
        const o = loadOv()[TEAMS[i] && TEAMS[i].name] || {};
        return Object.assign({}, t, o.name ? { name: o.name } : {},
            o.state ? { state: o.state, status: STATUS[o.state][0] } : {});
    };

    // ---------- buttons at the bottom of a card (depend on the status) ----------
    //   On Track -> View, Task, Chat | Behind Schedule -> View, Review, Chat | Completed -> View Final Report
    const ACTIONS = {
        ontrack:   ['View', 'Task', 'Chat'],
        behind:    ['View', 'Review', 'Chat'],
        completed: ['Final Report']
    };
    const ACTION_ICON = { 'View': 'visibility', 'Task': 'task_alt', 'Chat': 'chat_bubble', 'Review': 'rate_review', 'Final Report': 'description' };

    function renderActions(card, i, state) {
        const box = card.querySelector('.tm-actions');
        if (!box) return;
        const list = ACTIONS[state] || ACTIONS.ontrack;
        box.classList.toggle('single', list.length === 1);
        box.innerHTML = list.map((a, k) => {
            const cls  = a === 'Final Report' ? 'report' : (k === 0 ? 'solid' : '');
            const data = (a === 'View' || a === 'Final Report' || a === 'Review Report') ? `data-view-team="${i}"`
                       : (a === 'Task' || a === 'Chat') ? `data-work-team="${i}" data-work-focus="${a === 'Chat' ? 'chat' : 'tasks'}"`
                       : `data-review-team="${i}"`;
            return `<button type="button" class="tm-btn ${cls}" ${data}>` +
                   `<span class="material-symbols-outlined">${ACTION_ICON[a]}</span>${a === 'Final Report' ? 'View Final Report' : a}</button>`;
        }).join('');
    }

    // ---------- update a card on the page ----------
    function applyToCard(i) {
        const t = TEAMS[i];
        const o = loadOv()[t.name];
        const card = document.querySelector('.tm-card[data-team-index="' + i + '"]');
        if (!o || !card) return;


        if (o.name) card.querySelector('.tm-name').textContent = o.name;

        if (o.state) {
            const pill = card.querySelector('.tm-pill:not(.new)');
            pill.className = 'tm-pill ' + o.state;
            pill.textContent = STATUS[o.state][0];
            card.dataset.state = o.state;
            card.classList.toggle('completed', o.state === 'completed');
            const box = card.querySelector('.tm-progress');
            box.classList.remove('behind', 'completed');
            if (o.state !== 'ontrack') box.classList.add(o.state);
            renderActions(card, i, o.state);
        }

        if (o.members) {
            const all = t.members.filter(m => o.members.includes(m.name));
            const leader = all.find(m => m.name === o.leader) || all[0];
            if (leader) {
                card.querySelector('.tm-leader-name').textContent = leader.name;
                card.querySelector('.tm-leader-role').textContent = (o.roles && o.roles[leader.name]) || leader.role;
                const la = card.querySelector('.tm-leader .tm-av');
                la.textContent = initials(leader.name);
                la.style.background = colorFor(leader.name);
            }
            const others = all.filter(m => m !== leader);
            card.querySelector('.tm-stack').innerHTML =
                others.slice(0, 3).map(m => `<div class="tm-av" title="${esc(m.name)}" style="background:${colorFor(m.name)};">${esc(initials(m.name))}</div>`).join('') +
                (others.length > 3 ? `<div class="tm-av more">+${others.length - 3}</div>` : '');
        }

        if (o.skills) {
            let box = card.querySelector('.tm-skills');
            if (!box && o.skills.length) {
                box = document.createElement('div');
                box.className = 'tm-skills';
                box.innerHTML = '<div class="tm-label">Skills Covered</div><div class="tm-chips"></div>';
                card.querySelector('.tm-progress').before(box);
            }
            if (box) {
                box.hidden = !o.skills.length;
                box.querySelector('.tm-chips').innerHTML = o.skills.map(sk => `<span class="tm-chip">${esc(sk)}</span>`).join('');
            }
        }

        if (o.deadline) {
            const left = card.querySelector('.tm-meta .left');
            if (left) left.innerHTML = '<span class="material-symbols-outlined">calendar_today</span>Deadline: ' + esc(fmtShort(o.deadline));
            card.dataset.deadline = Math.floor(new Date(o.deadline + 'T00:00:00').getTime() / 1000);
            // "X days left" / "X days delayed" next to the deadline
            const right = card.querySelector('.tm-meta .right');
            if (right) {
                const days = Math.round((new Date(o.deadline + 'T00:00:00') - new Date(new Date().toDateString())) / 86400000);
                const tone = days < 0 ? 'late' : (days <= 14 ? 'warn' : 'ok');
                right.className = 'right ' + tone;
                right.innerHTML = '<span class="material-symbols-outlined">' + (days < 0 ? 'warning' : 'schedule') + '</span>' +
                    (days < 0 ? Math.abs(days) + ' days delayed' : days + ' day' + (days === 1 ? '' : 's') + ' left');
            }
        }

        card.dataset.search = [card.querySelector('.tm-name').textContent, t.project,
            card.querySelector('.tm-leader-name').textContent,
            ...Array.from(card.querySelectorAll('.tm-stack .tm-av[title]')).map(a => a.title)].join(' ').toLowerCase();
        document.dispatchEvent(new Event('tm-refilter'));
    }
    TEAMS.forEach((t, i) => { if (!isDb(t)) applyToCard(i); });

    window.tmDemoSave = (i, patch, msg) => demoSave(i, patch, msg);
    window.tmToast = msg => toast(msg);

    function demoSave(i, patch, msg) {
        const all = loadOv();
        all[TEAMS[i].name] = Object.assign(all[TEAMS[i].name] || {}, patch);
        saveOv(all);
        applyToCard(i);
        toast(msg);
    }

    function toast(msg) {
        const el = document.createElement('div');
        el.className = 'flash-toast flash-success';
        el.innerHTML = '<div class="flash-icon-wrap"><span class="material-symbols-outlined">check_circle</span></div>' +
                       '<div class="flash-content"><div class="flash-title" style="font-weight:700;font-size:14px;"></div></div>';
        el.querySelector('.flash-title').textContent = msg;
        document.body.appendChild(el);
        setTimeout(() => { el.style.transition = 'opacity .4s'; el.style.opacity = '0'; setTimeout(() => el.remove(), 400); }, 2500);
    }

    // ---------- the ⋮ menu ----------
    function closeMenus() {
        document.querySelectorAll('.tm-menu').forEach(m => m.remove());
        document.querySelectorAll('.tm-kebab.open').forEach(k => k.classList.remove('open'));
    }

    document.querySelectorAll('[data-team-menu]').forEach(btn => btn.addEventListener('click', function (e) {
        e.stopPropagation();
        const wasOpen = btn.classList.contains('open');
        closeMenus();
        if (wasOpen) return;

        const i = Number(btn.dataset.teamMenu);
        const current = btn.closest('.tm-card').dataset.state;

        // The status is not changed by hand: On Track / Behind Schedule follow the deadline and the task due dates,
        // Completed comes only when the final report is accepted. A Completed team can't be edited.
        const menu = document.createElement('div');
        menu.className = 'tm-menu';
        menu.innerHTML = current === 'completed'
            ? '<div class="tm-menu-note"><span class="material-symbols-outlined">lock</span>This team is completed and can’t be edited.</div>'
            : '<button type="button" data-act="edit"><span class="material-symbols-outlined">edit</span>Edit Team</button>' +
              '<div class="tm-menu-note"><span class="material-symbols-outlined">info</span>The status updates by itself from the deadline, the tasks and the final report.</div>';

        menu.addEventListener('click', ev => {
            ev.stopPropagation();
            const b = ev.target.closest('[data-act]');
            if (!b) return;
            closeMenus();
            if (b.dataset.act === 'edit') openEdit(i);
        });

        btn.classList.add('open');
        btn.parentElement.appendChild(menu);
    }));
    document.addEventListener('click', closeMenus);
    document.addEventListener('keydown', e => { if (e.key === 'Escape') closeMenus(); });

    // ---------- Edit Team (name and deadline only) ----------
    const TODAY = ORG_DATA.TODAY;
    const editModal = document.getElementById('tmEditModal');
    const editForm  = document.getElementById('tmEditForm');
    const teDeadline = document.getElementById('teDeadline');
    const teExtend   = editForm.querySelector('[name="extend_project"]');
    const teNote     = editForm.querySelector('[data-extend-note]');
    let editing = -1, editOldDeadline = '';
    // a date after the day the project ends -> the project will be made longer
    const teInfo = () => {
        const t = TEAMS[editing];
        return t && teDeadline.value !== editOldDeadline
            ? window.TMExtend.info(t.project_start_raw, t.project_weeks, t.project_deadline_raw, teDeadline.value) : null;
    };
    teDeadline.addEventListener('change', () => { teExtend.value = '0'; teErr('teErrDeadline', ''); window.TMExtend.note(teNote, teInfo()); });

    const teErr = (id, msg) => { document.getElementById(id).textContent = msg || ''; };

    function openEdit(i) {
        editing = i;
        const t = TEAMS[i];
        const o = isDb(t) ? {} : (loadOv()[t.name] || {});
        document.getElementById('teId').value = isDb(t) ? t.id : '';
        document.getElementById('teProject').textContent = 'Project: ' + t.project;
        document.getElementById('teName').value = o.name || t.name;
        ['teErrName', 'teErrDeadline'].forEach(id => teErr(id, ''));
        editForm.querySelectorAll('.invalid').forEach(el => el.classList.remove('invalid'));

        editOldDeadline = isDb(t) ? (t.deadline_raw || '') : '';
        teDeadline.value = isDb(t) ? editOldDeadline : (o.deadline || '');

        // deadline: from today (the current deadline can always be kept); a date after the day
        // the project ends is possible too -> the project is then made longer (up to MAX_WEEKS)
        teDeadline.min = editOldDeadline && editOldDeadline < TODAY ? editOldDeadline : TODAY;
        teDeadline.max = window.TMExtend.latest(t.project_start_raw);
        teExtend.value = '0';
        window.TMExtend.note(teNote, null);

        editModal.classList.add('open');
    }

    editModal.querySelectorAll('[data-close-edit]').forEach(b => b.addEventListener('click', () => editModal.classList.remove('open')));
    editModal.addEventListener('click', e => { if (e.target === editModal) editModal.classList.remove('open'); });

    editForm.addEventListener('submit', function (e) {
        const name = document.getElementById('teName').value.trim();
        const dl = teDeadline.value;
        let ok = true;

        if (!name) { teErr('teErrName', 'Please enter a team name.'); ok = false; }
        else if (name.length < 3) { teErr('teErrName', 'The team name must be at least 3 characters.'); ok = false; }
        if (!dl) { teErr('teErrDeadline', 'Please pick a team deadline.'); ok = false; }
        else if (dl !== editOldDeadline && dl < TODAY) { teErr('teErrDeadline', 'The deadline can’t be in the past.'); ok = false; }
        else {
            const x = teInfo();
            if (x && x.tooLate) { teErr('teErrDeadline', 'A project can run up to ' + ORG_DATA.MAX_WEEKS + ' weeks, so the latest team deadline is ' + x.max + '.'); ok = false; }
            else if (ok && x && teExtend.value !== '1' && isDb(TEAMS[editing])) {
                e.preventDefault();
                window.TMExtend.confirm(x, () => { teExtend.value = '1'; editForm.submit(); });
                return;
            }
        }
        if (!ok) { e.preventDefault(); return; }

        if (!isDb(TEAMS[editing])) {          // demo team -> save in the browser
            e.preventDefault();
            demoSave(editing, { name: name, deadline: dl || undefined }, 'Team updated');
            editModal.classList.remove('open');
        }
        // database team -> normal form submit to teams.php
    });

})();


/* =================================================================
   DELAY REVIEW – "Review" button on Behind Schedule teams
   Shows why the team is late and lets the organization act on it.
================================================================= */
(function () {
    const TEAMS = ORG_DATA.REVIEW_TEAMS;
    const COLORS = ['#1e3a5f', '#0f766e', '#7c3aed', '#b45309', '#be185d', '#2563eb', '#475569'];

    const modal = document.getElementById('tmReviewModal');
    const body  = document.getElementById('trBody');
    const esc = t => { const d = document.createElement('div'); d.textContent = t == null ? '' : String(t); return d.innerHTML; };
    const initials = n => String(n || '').trim().split(/\s+/).slice(0, 2).map(w => w.charAt(0).toUpperCase()).join('');
    const colorFor = n => { let h = 0; for (const c of String(n)) h = (h * 31 + c.charCodeAt(0)) >>> 0; return COLORS[h % COLORS.length]; };
    const av = n => `<span class="tm-av" style="background:${colorFor(n)};">${esc(initials(n))}</span>`;
    const today = new Date(new Date().toDateString());
    const toDate = str => { const d = new Date(str); return isNaN(d) ? null : new Date(d.toDateString()); };
    const ymd = d => d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');

    let current = -1;

    function open(i) {
        current = i;
        const base = TEAMS[i];
        const t = window.tmEffective ? Object.assign({}, base, window.tmEffective(i, base)) : base;

        document.getElementById('trName').textContent = t.name;
        document.getElementById('trProject').innerHTML = 'Project: <strong>' + esc(t.project) + '</strong>';

        // expected progress = how much time of the plan has passed
        const start = toDate(t.created), end = toDate(t.team_deadline);
        let expected = 0;
        if (start && end && end > start) expected = Math.round(Math.min(1, Math.max(0, (today - start) / (end - start))) * 100);
        const actual = Math.max(0, Math.min(100, t.percent));
        const gap = Math.max(0, expected - actual);

        // overdue + at-risk tasks from the team's Kanban board
        const tasks = (window.tmTasksFor ? window.tmTasksFor(i) : []).filter(x => x.col !== 'done' && x.due);
        const late = [], risk = [];
        tasks.forEach(x => {
            const days = Math.round((new Date(x.due + 'T00:00:00') - today) / 86400000);
            if (days < 0) late.push(Object.assign({ days: -days }, x));
            else if (days <= 7) risk.push(Object.assign({ days: days }, x));
        });

        const members = t.members.slice().sort((a, b) => a.progress - b.progress);
        const weak = members.filter(m => m.progress < 50);

        const taskRow = (x, isLate) => `
            <div class="tr-task">
                ${x.who ? av(x.who) : '<span class="tm-av" style="background:#cbd5e1;">?</span>'}
                <div class="tr-task-info">
                    <div class="tr-task-title">${esc(x.title)}</div>
                    <div class="tr-task-sub">${esc(x.who || 'Unassigned')} · ${esc({ todo: 'To Do', progress: 'In Progress', review: 'Review' }[x.col] || '')}</div>
                </div>
                <span class="tr-flag ${isLate ? 'late' : 'risk'}">${isLate ? x.days + 'd overdue' : (x.days === 0 ? 'Due today' : 'Due in ' + x.days + 'd')}</span>
            </div>`;

        const reminder =
            `Hi team, our progress is ${actual}% but we should be around ${expected}% by now.` +
            (late.length ? `\n\nPlease finish these overdue tasks first:\n` + late.map(x => `• ${x.title} (${x.who || 'unassigned'})`).join('\n') : '') +
            `\n\nIf anything is blocking you, reply here so we can help.`;

        const minDate = new Date(today); minDate.setDate(minDate.getDate() + 1);
        const suggest = new Date((end && end > today ? end : today)); suggest.setDate(suggest.getDate() + 14);
        // suggest a date up to the day the project ends (a later one makes the project longer)
        if (t.project_deadline_raw && ymd(suggest) > t.project_deadline_raw && t.project_deadline_raw > (t.deadline_raw || '')) {
            suggest.setTime(new Date(t.project_deadline_raw + 'T00:00:00').getTime());
        }
        const latestDl = window.TMExtend.latest(t.project_start_raw);

        body.innerHTML = `
            <div class="tr-alert">
                <span class="material-symbols-outlined">warning</span>
                <div><strong>This team is behind schedule (${esc(t.time)}).</strong><br>
                     Progress is ${actual}% but ${expected}% of the planned time has already passed.
                     ${late.length ? late.length + ' task' + (late.length === 1 ? ' is' : 's are') + ' overdue.' : ''}</div>
            </div>

            <div class="tv-stats">
                <div class="tv-stat"><div class="tv-stat-icon sky"><span class="material-symbols-outlined">schedule</span></div>
                    <div><div class="tv-stat-label">Expected</div><div class="tv-stat-value">${expected}%</div></div></div>
                <div class="tv-stat"><div class="tv-stat-icon" style="background:#fee2e2;color:#dc2626;"><span class="material-symbols-outlined">trending_down</span></div>
                    <div><div class="tv-stat-label">Actual</div><div class="tv-stat-value">${actual}%</div></div></div>
                <div class="tv-stat"><div class="tv-stat-icon" style="background:#fef3c7;color:#b45309;"><span class="material-symbols-outlined">difference</span></div>
                    <div><div class="tv-stat-label">Gap</div><div class="tv-stat-value">${gap}%</div></div></div>
                <div class="tv-stat"><div class="tv-stat-icon navy"><span class="material-symbols-outlined">assignment_late</span></div>
                    <div><div class="tv-stat-label">Overdue Tasks</div><div class="tv-stat-value">${late.length}</div></div></div>
            </div>

            <div class="tv-card">
                <h4><span class="material-symbols-outlined">insights</span> Progress vs Plan</h4>
                <div class="tr-compare-row"><span>Expected</span><div class="tr-bar expected"><span style="width:${expected}%"></span></div><strong>${expected}%</strong></div>
                <div class="tr-compare-row"><span>Actual</span><div class="tr-bar actual"><span style="width:${actual}%"></span></div><strong>${actual}%</strong></div>
                <div class="tr-gap-note">Started ${esc(t.created || '-')} · Deadline ${esc(t.team_deadline || t.deadline || '-')} · The team is <b>${gap}%</b> behind the plan.</div>
            </div>

            <div class="tv-two" style="margin-bottom:14px;">
                <div class="tv-card">
                    <h4><span class="material-symbols-outlined">assignment_late</span> Overdue &amp; At-risk Tasks</h4>
                    ${late.length || risk.length ? late.map(x => taskRow(x, true)).join('') + risk.map(x => taskRow(x, false)).join('')
                                                 : '<div class="tv-empty">No overdue tasks on the board.</div>'}
                </div>
                <div class="tv-card">
                    <h4><span class="material-symbols-outlined">group</span> Member Contribution
                        <span class="tv-count">${weak.length ? weak.length + ' need support' : 'All on track'}</span></h4>
                    ${members.length ? members.map(m => `
                        <div class="tr-mem">
                            ${av(m.name)}
                            <span class="tr-mem-name" title="${esc(m.name)} · ${esc(m.role)}">${esc(m.name)}</span>
                            <div class="tv-bar ${m.progress < 50 ? 'low' : ''}"><span style="width:${m.progress}%"></span></div>
                            <span class="tr-mem-pct">${m.progress}%</span>
                            ${m.progress < 50 ? '<span class="tr-support">Needs support</span>' : ''}
                        </div>`).join('') : '<div class="tv-empty">No member details.</div>'}
                </div>
            </div>

            <div class="tv-card" style="margin-bottom:0;">
                <h4><span class="material-symbols-outlined">task_alt</span> Take Action</h4>
                <div class="tr-tabs">
                    <button type="button" class="on" data-pane="remind"><span class="material-symbols-outlined">campaign</span>Send Reminder</button>
                    <button type="button" data-pane="extend"><span class="material-symbols-outlined">event_repeat</span>Extend Deadline</button>
                </div>

                <div class="tr-pane" data-pane="remind">
                    <textarea id="trReminder" maxlength="1000">${esc(reminder)}</textarea>
                    <div class="tr-pane-foot">
                        <span class="tr-note">The message is posted in the team chat.</span>
                        <button type="button" class="tm-btn solid" id="trSendReminder"><span class="material-symbols-outlined">send</span>Send to Team</button>
                    </div>
                </div>

                <div class="tr-pane" data-pane="extend" hidden>
                    <div class="tm-grid-2">
                        <div class="tm-field">
                            <label class="tm-f-label" for="trNewDate">New Deadline *</label>
                            <input type="date" id="trNewDate" class="tm-input" min="${ymd(minDate)}" max="${latestDl}" value="${ymd(suggest)}">
                            <div class="tm-warn" id="trExtendNote" hidden></div>
                        </div>
                        <div class="tm-field">
                            <label class="tm-f-label">Current Deadline</label>
                            <input type="text" class="tm-input" value="${esc(t.team_deadline || t.deadline || 'Not set')}" disabled>
                        </div>
                    </div>
                    <label class="tm-f-label" for="trReason">Reason *</label>
                    <textarea id="trReason" maxlength="300" placeholder="e.g. The smart contract audit took longer than planned."></textarea>
                    <div class="tm-err" id="trErr"></div>
                    <div class="tr-pane-foot">
                        <span class="tr-note">The team becomes On Track again by itself when no task is overdue.</span>
                        <button type="button" class="tm-btn solid" id="trExtend"><span class="material-symbols-outlined">event_available</span>Extend Deadline</button>
                    </div>
                </div>
            </div>
        `;

        // tabs
        body.querySelectorAll('.tr-tabs button').forEach(b => b.addEventListener('click', () => {
            body.querySelectorAll('.tr-tabs button').forEach(x => x.classList.toggle('on', x === b));
            body.querySelectorAll('.tr-pane').forEach(p => p.hidden = p.dataset.pane !== b.dataset.pane);
        }));

        // send reminder -> team chat
        document.getElementById('trSendReminder').addEventListener('click', () => {
            const text = document.getElementById('trReminder').value.trim();
            if (!text) return;
            if (window.tmPostToChat) window.tmPostToChat(i, text);
            if (window.tmToast) window.tmToast('Reminder sent to the team chat');
            close();
        });

        // extend deadline (a date after the day the project ends makes the project longer)
        const trInfo = date => window.TMExtend.info(t.project_start_raw, t.project_weeks, t.project_deadline_raw, date);
        const trDate = document.getElementById('trNewDate');
        const showTr = () => window.TMExtend.note(document.getElementById('trExtendNote'), trInfo(trDate.value));
        trDate.addEventListener('change', () => { document.getElementById('trErr').textContent = ''; showTr(); });
        showTr();

        document.getElementById('trExtend').addEventListener('click', () => {
            const date = trDate.value;
            const reason = document.getElementById('trReason').value.trim();
            const err = document.getElementById('trErr');
            if (!date || date < ymd(minDate)) { err.textContent = 'Please pick a date after today.'; return; }
            const x = trInfo(date);
            if (x && x.tooLate) { err.textContent = 'A project can run up to ' + ORG_DATA.MAX_WEEKS + ' weeks, so the latest deadline is ' + x.max + '.'; return; }
            if (reason.length < 5) { err.textContent = 'Please write a short reason (at least 5 characters).'; return; }

            if (base.id !== null && base.id !== undefined) {       // database team -> save on the server
                const f = document.getElementById('trExtendForm');
                const send = longer => {
                    f.elements.namedItem('team_id').value = base.id;
                    f.elements.namedItem('deadline').value = date;
                    f.elements.namedItem('reason').value = reason;
                    f.elements.namedItem('extend_project').value = longer ? '1' : '0';
                    f.submit();
                };
                if (x) window.TMExtend.confirm(x, () => send(true));   // ask first: make the project longer?
                else send(false);
                return;
            }
            // demo team -> save in the browser + tell the team in chat
            const nice = new Date(date + 'T00:00:00').toLocaleDateString('en-US', { month: 'short', day: '2-digit', year: 'numeric' });
            if (window.tmPostToChat) window.tmPostToChat(i, `The team deadline has been extended to ${nice}. Reason: ${reason}`);
            if (window.tmDemoSave) window.tmDemoSave(i, { deadline: date, state: 'ontrack' }, 'Deadline extended to ' + nice);
            close();
        });

        body.scrollTop = 0;
        modal.classList.add('open');
    }

    function close() { modal.classList.remove('open'); }

    document.addEventListener('click', e => {
        const b = e.target.closest('[data-review-team]');
        if (b) open(Number(b.dataset.reviewTeam));
    });
    modal.querySelectorAll('[data-close-review]').forEach(b => b.addEventListener('click', close));
    modal.addEventListener('click', e => { if (e.target === modal) close(); });
    document.addEventListener('keydown', e => { if (e.key === 'Escape' && modal.classList.contains('open')) close(); });
    document.getElementById('trOpenBoard').addEventListener('click', () => {
        close();
        if (window.tmOpenWorkspace) window.tmOpenWorkspace(current, 'tasks');
    });
})();


/* =================================================================
   OPENED FROM A NOTIFICATION
   teams.php?team=Vortex%20Group&open=review   (open = view | review | tasks | chat)
================================================================= */
(function () {
    const q = new URLSearchParams(location.search);
    const name = (q.get('team') || '').trim().toLowerCase();
    if (!name) return;
    const open = q.get('open') || 'view';
    history.replaceState(null, '', location.pathname);

    const card = document.querySelector('.tm-card[data-team-name="' + CSS.escape(name) + '"]');
    if (!card) return;
    const i = Number(card.dataset.teamIndex);

    if ((open === 'tasks' || open === 'chat') && window.tmOpenWorkspace) {
        window.tmOpenWorkspace(i, open);
        return;
    }
    card.scrollIntoView({ block: 'center' });
    card.style.transition = 'box-shadow .3s ease';
    card.style.boxShadow = '0 0 0 3px rgba(59,130,246,.45)';
    setTimeout(() => card.style.boxShadow = '', 2500);

    // Review only exists for Behind Schedule teams -> otherwise show the details
    const btn = (open === 'review' && card.querySelector('[data-review-team]')) || card.querySelector('[data-view-team]');
    if (btn) btn.click();
})();


/* hide the success / error message after a few seconds */
(function () {
    const t = document.getElementById('tmFlash');
    if (!t) return;
    setTimeout(function () {
        t.style.transition = 'opacity .4s ease, transform .4s ease';
        t.style.opacity = '0';
        t.style.transform = 'translateX(20px)';
        setTimeout(() => t.remove(), 400);
        history.replaceState(null, '', location.pathname);
    }, 3500);
})();