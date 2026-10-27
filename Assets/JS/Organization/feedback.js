/*
| Organization - feedback page script
| Used by: Functions/Organization/feedback.php
| Values from PHP / the database come from ORG_DATA (set in a small <script> on the page).
*/

// popup covers the whole screen (moved out of <main>)
document.body.appendChild(document.getElementById('fbModal'));

/* ================= FILTERS: search, rating, sort ================= */
(function () {
    const list   = document.querySelector('.fb-list');
    const cards  = Array.from(list.querySelectorAll('.fb-card'));
    const search = document.getElementById('fbSearch');
    const rating = document.getElementById('fbRating');
    const sort   = document.getElementById('fbSort');
    const clear  = document.getElementById('fbClear');

    const PAGE = 3;                 // cards shown per "Load More"
    let limit = PAGE;
    const moreBtn = document.getElementById('fbLoadMore');

    function apply(keepLimit) {
        if (keepLimit !== true) limit = PAGE;          // filters changed -> start from the first page
        const q = search.value.trim().toLowerCase();
        const n = (c, k) => Number(c.dataset[k]) || 0;
        const sorted = cards.slice().sort((a, b) => {
            if (sort.value === 'oldest') return n(a, 'created') - n(b, 'created');
            if (sort.value === 'high')   return n(b, 'rating') - n(a, 'rating') || n(b, 'created') - n(a, 'created');
            if (sort.value === 'low')    return n(a, 'rating') - n(b, 'rating') || n(b, 'created') - n(a, 'created');
            return n(b, 'created') - n(a, 'created');
        });
        sorted.forEach(c => list.appendChild(c));

        const matching = sorted.filter(c => (rating.value === 'all' || c.dataset.rating === rating.value)
                                         && (q === '' || c.dataset.search.includes(q)));
        cards.forEach(c => { c.hidden = true; });
        matching.slice(0, limit).forEach(c => { c.hidden = false; });

        const shown = Math.min(limit, matching.length);
        document.getElementById('fbShown').textContent = shown;
        document.getElementById('fbTotal').textContent = matching.length;
        moreBtn.hidden = shown >= matching.length;
        document.getElementById('fbEmpty').hidden = matching.length !== 0;
        clear.hidden = q === '' && rating.value === 'all' && sort.value === 'latest';
    }
    search.addEventListener('input', () => apply());
    rating.addEventListener('change', () => apply());
    sort.addEventListener('change', () => apply());
    moreBtn.addEventListener('click', () => {
        const firstNew = limit;
        limit += PAGE;
        apply(true);
        // scroll gently to the first newly loaded card
        const visible = Array.from(list.querySelectorAll('.fb-card:not([hidden])'));
        if (visible[firstNew]) visible[firstNew].scrollIntoView({ behavior: 'smooth', block: 'start' });
    });
    clear.addEventListener('click', () => { search.value = ''; rating.value = 'all'; sort.value = 'latest'; apply(); });
    apply();
})();

/* ================= SUBMIT NEW FEEDBACK ================= */
(function () {
    const PROJECTS = ORG_DATA.PROJECTS;
    const LABELS = { 1: 'Needs Work', 2: 'Fair', 3: 'Good', 4: 'Very Good', 5: 'Exceptional' };
    const STATUS = { reviewing: 'Reviewing', published: 'Published', inprogress: 'Active', completed: 'Completed', hold: 'Hold' };
    const COLORS = ['#1e3a5f', '#0f766e', '#7c3aed', '#b45309', '#be185d', '#2563eb', '#475569'];

    const modal = document.getElementById('fbModal');
    const form  = document.getElementById('fbForm');
    const esc = t => { const d = document.createElement('div'); d.textContent = t == null ? '' : String(t); return d.innerHTML; };
    const initials = n => String(n || '').trim().split(/\s+/).slice(0, 2).map(w => w.charAt(0).toUpperCase()).join('');
    const colorFor = n => { let h = 0; for (const c of String(n)) h = (h * 31 + c.charCodeAt(0)) >>> 0; return COLORS[h % COLORS.length]; };
    const err = (k, m) => { const e = form.querySelector('[data-err="' + k + '"]'); if (e) e.textContent = m || ''; };

    function open() { modal.classList.add('open'); }
    function close() { modal.classList.remove('open'); }
    document.getElementById('fbOpenForm').addEventListener('click', () => window.fbOpenForm ? window.fbOpenForm() : open());

    // opened from a notification: feedback.php?give=1 -> open the form straight away
    if (new URLSearchParams(location.search).has('give')) {
        history.replaceState(null, '', location.pathname);
        setTimeout(() => document.getElementById('fbOpenForm').click(), 0);
    }
    modal.querySelectorAll('[data-fb-close]').forEach(b => b.addEventListener('click', close));
    modal.addEventListener('click', e => { if (e.target === modal) close(); });
    document.addEventListener('keydown', e => { if (e.key === 'Escape' && modal.classList.contains('open')) close(); });

    const projectEl = document.getElementById('fbProject');
    document.body.appendChild(document.getElementById('fbViewModal'));

    // project -> team + students
    projectEl.addEventListener('change', () => {
        const p = PROJECTS.find(x => x.id === Number(projectEl.value));
        const box = document.getElementById('fbTeamBox');
        err('project', ''); projectEl.classList.remove('invalid');
        if (!p) { box.hidden = true; return; }
        box.innerHTML = `
            <div class="fb-team-top"><span class="material-symbols-outlined">groups</span>${esc(p.team)}
                <span class="fb-st">${esc(STATUS[p.status] || p.status)}</span></div>
            <div class="fb-people">${p.students.map(st =>
                `<span class="fb-person"><i style="background:${colorFor(st.name)}">${esc(initials(st.name))}</i>${esc(st.name)}${st.role ? ' · ' + esc(st.role) : ''}</span>`).join('')}</div>`;
        box.hidden = false;
    });

    // overall stars
    const starBtns = Array.from(document.querySelectorAll('#fbStarPick button'));
    const ratingVal = document.getElementById('fbRatingVal');
    const starText = document.getElementById('fbStarText');
    function paint(v) { starBtns.forEach(b => b.classList.toggle('on', Number(b.dataset.v) <= v)); }
    starBtns.forEach(b => {
        b.addEventListener('mouseenter', () => paint(Number(b.dataset.v)));
        b.addEventListener('click', () => {
            ratingVal.value = b.dataset.v;
            starText.textContent = b.dataset.v + '/5 · ' + LABELS[b.dataset.v];
            starText.classList.add('set');
            err('rating', '');
        });
    });
    document.getElementById('fbStarPick').addEventListener('mouseleave', () => paint(Number(ratingVal.value) || 0));

    // competency sliders
    form.querySelectorAll('[data-range]').forEach(r => r.addEventListener('input', () => {
        form.querySelector('[data-out="' + r.dataset.range + '"]').textContent = Number(r.value).toFixed(1);
    }));

    // summary counter
    const summary = document.getElementById('fbSummary');
    summary.addEventListener('input', () => {
        document.getElementById('fbCount').textContent = summary.value.length;
        if (summary.value.trim().length >= 20) { err('summary', ''); summary.classList.remove('invalid'); }
    });

    // improvements chips
    let chips = [];
    const chipBox = document.getElementById('fbChipBox');
    const chipIn  = document.getElementById('fbChipInput');
    function renderChips() {
        chipBox.querySelectorAll('.fb-chip').forEach(c => c.remove());
        chips.forEach((c, i) => {
            const el = document.createElement('span');
            el.className = 'fb-chip';
            el.innerHTML = esc(c) + ' <button type="button" aria-label="Remove">&times;</button>';
            el.querySelector('button').addEventListener('click', ev => { ev.stopPropagation(); chips.splice(i, 1); renderChips(); });
            chipBox.insertBefore(el, chipIn);
        });
        document.getElementById('fbImprovements').value = chips.join(', ');
    }
    function addChip(v) {
        (v || '').split(',').map(x => x.trim()).filter(Boolean).forEach(x => {
            if (chips.length < 8 && !chips.some(c => c.toLowerCase() === x.toLowerCase())) chips.push(x);
        });
        chipIn.value = '';
        renderChips();
    }
    chipIn.addEventListener('keydown', e => {
        if (e.key === 'Enter' || e.key === ',') { e.preventDefault(); addChip(chipIn.value); }
        else if (e.key === 'Backspace' && !chipIn.value && chips.length) { chips.pop(); renderChips(); }
    });
    chipIn.addEventListener('blur', () => addChip(chipIn.value));
    chipBox.addEventListener('click', () => chipIn.focus());
    document.querySelectorAll('[data-quick]').forEach(b => b.addEventListener('click', () => addChip(b.dataset.quick)));

    // validation
    form.addEventListener('submit', e => {
        addChip(chipIn.value);
        let ok = true;
        if (!projectEl.value) { err('project', 'Please select a project.'); projectEl.classList.add('invalid'); ok = false; }
        if (!ratingVal.value) { err('rating', 'Please give an overall rating.'); ok = false; }
        if (summary.value.trim().length < 20) { err('summary', 'Please write at least 20 characters.'); summary.classList.add('invalid'); ok = false; }
        if (!ok) {
            e.preventDefault();
            const first = form.querySelector('.fb-err:not(:empty)');
            if (first) first.scrollIntoView({ behavior: 'smooth', block: 'center' });
            return;
        }
    });

    // ---------- open the form (feedback is final, so the form is only for NEW feedback) ----------
    window.fbOpenForm = function () {
        form.reset();
        form.querySelectorAll('.fb-err').forEach(x => x.textContent = '');
        form.querySelectorAll('.invalid').forEach(x => x.classList.remove('invalid'));
        document.getElementById('fbTeamBox').hidden = true;

        // no project can get feedback -> show only the info note
        const nothingToRate = PROJECTS.length === 0;
        document.getElementById('fbNoProjects').hidden = !nothingToRate;
        document.getElementById('fbFields').hidden     = nothingToRate;
        document.getElementById('fbSubmitBtn').hidden  = nothingToRate;

        // start empty
        ratingVal.value = '';
        paint(0);
        starText.textContent = 'Click to rate';
        starText.classList.remove('set');
        form.querySelectorAll('[data-range]').forEach(r => {
            r.value = 3;
            form.querySelector('[data-out="' + r.dataset.range + '"]').textContent = '3.0';
        });
        summary.value = '';
        document.getElementById('fbCount').textContent = 0;
        chips = [];
        renderChips();

        open();
    };
})();

/* ================= VIEW FULL DETAILS ================= */
(function () {
    const REVIEWS = ORG_DATA.REVIEWS;
    const LABELS = { 1: 'Needs Work', 2: 'Fair', 3: 'Good', 4: 'Very Good', 5: 'Exceptional' };
    const COLORS = ['#1e3a5f', '#0f766e', '#7c3aed', '#b45309', '#be185d', '#2563eb', '#475569'];

    const esc = t => { const d = document.createElement('div'); d.textContent = t == null ? '' : String(t); return d.innerHTML; };
    const initials = n => String(n || '').trim().split(/\s+/).slice(0, 2).map(w => w.charAt(0).toUpperCase()).join('');
    const colorFor = n => { let h = 0; for (const c of String(n)) h = (h * 31 + c.charCodeAt(0)) >>> 0; return COLORS[h % COLORS.length]; };
    const stars = (v, size) => { let h = ''; for (let i = 1; i <= 5; i++) {
        const icon = v >= i ? 'star' : (v >= i - 0.5 ? 'star_half' : 'star');
        h += `<span class="material-symbols-outlined fb-star ${v >= i - 0.5 ? 'on' : ''}" style="font-size:${size}px;">${icon}</span>`; } return h; };

    const effective = i => REVIEWS[i];

    // ---------- details popup ----------
    const modal = document.getElementById('fbViewModal');
    let current = -1;

    function openView(i) {
        current = i;
        const r = effective(i);
        const entries = Object.entries(r.skills);
        const avg = entries.reduce((a, [, v]) => a + Number(v), 0) / entries.length;
        const best = entries.reduce((a, b) => (Number(b[1]) > Number(a[1]) ? b : a));
        const low  = entries.reduce((a, b) => (Number(b[1]) < Number(a[1]) ? b : a));

        document.getElementById('fvTitle').textContent = r.title;
        document.getElementById('fvMeta').innerHTML =
            `<span><span class="material-symbols-outlined">groups</span>${esc(r.team)}</span>` +
            (r.category ? `<span><span class="material-symbols-outlined">category</span>${esc(r.category)}</span>` : '') +
            `<span><span class="material-symbols-outlined">calendar_today</span>${esc(r.date)}</span>`;
        document.getElementById('fvScore').innerHTML = r.rating + '<small>/5</small>';
        document.getElementById('fvStars').innerHTML = stars(r.rating, 16);
        document.getElementById('fvLabel').textContent = LABELS[r.rating];

        document.getElementById('fvBody').innerHTML = `
            <div class="fv-stats">
                <div class="fv-stat"><div class="fv-stat-icon" style="background:#dbeafe;color:#1d4ed8;"><span class="material-symbols-outlined">analytics</span></div>
                    <div><div class="fv-stat-label">Competency Average</div><div class="fv-stat-value">${avg.toFixed(1)} / 5</div></div></div>
                <div class="fv-stat"><div class="fv-stat-icon" style="background:#dcfce7;color:#15803d;"><span class="material-symbols-outlined">trending_up</span></div>
                    <div><div class="fv-stat-label">Strongest Area</div><div class="fv-stat-value">${esc(best[0])}</div></div></div>
                <div class="fv-stat"><div class="fv-stat-icon" style="background:#ffedd5;color:#c2410c;"><span class="material-symbols-outlined">construction</span></div>
                    <div><div class="fv-stat-label">Needs Work</div><div class="fv-stat-value">${esc(low[0])}</div></div></div>
            </div>

            <div class="fv-card">
                <h4><span class="material-symbols-outlined">bar_chart</span> Competency Breakdown</h4>
                ${entries.map(([name, v]) => `
                    <div class="fv-comp"><span>${esc(name)}</span>
                        <div class="fb-track"><span class="${name === best[0] ? 'best' : (name === low[0] && low[1] < best[1] ? 'low' : '')}" style="width:${Math.round(v / 5 * 100)}%"></span></div>
                        <strong>${Number(v).toFixed(1)}</strong></div>`).join('')}
            </div>

            <div class="fv-card">
                <h4><span class="material-symbols-outlined">notes</span> Performance Summary</h4>
                <p>${esc(r.summary)}</p>
            </div>

            <div class="fv-card">
                <h4><span class="material-symbols-outlined">lightbulb</span> Suggested Improvements</h4>
                ${r.improvements.length ? `<div class="fb-chips">${r.improvements.map(x => `<span class="fb-chip">${esc(x)}</span>`).join('')}</div>`
                                        : '<div class="fv-empty">No improvements suggested.</div>'}
            </div>

            <div class="fv-two">
                <div class="fv-card">
                    <h4><span class="material-symbols-outlined">group</span> Team Members</h4>
                    ${r.members.length ? r.members.map(m => `
                        <div class="fv-member"><i style="background:${colorFor(m.name)}">${esc(initials(m.name))}</i>
                            <div><div class="fv-member-name">${esc(m.name)}</div><div class="fv-member-role">${esc(m.role || 'Team member')}</div></div></div>`).join('')
                        : '<div class="fv-empty">No member details.</div>'}
                </div>
                <div class="fv-card">
                    <h4><span class="material-symbols-outlined">info</span> Feedback Info</h4>
                    <div class="fv-info-row"><span>Submitted on</span><strong>${esc(r.date)}</strong></div>
                    <div class="fv-info-row"><span>Shared with</span><strong style="color:#15803d">All team members</strong></div>
                    <div class="fv-info-row"><span>Team size</span><strong>${r.members.length} member${r.members.length === 1 ? '' : 's'}</strong></div>
                </div>
            </div>
        `;
        document.getElementById('fvBody').scrollTop = 0;
        modal.classList.add('open');
    }

    function closeView() { modal.classList.remove('open'); }
    modal.querySelectorAll('[data-fv-close]').forEach(b => b.addEventListener('click', closeView));
    modal.addEventListener('click', e => { if (e.target === modal) closeView(); });
    document.addEventListener('keydown', e => { if (e.key === 'Escape' && modal.classList.contains('open')) closeView(); });
    document.getElementById('fvPrint').addEventListener('click', () => window.print());


    document.querySelectorAll('[data-fb-view]').forEach(b => b.addEventListener('click', () => openView(Number(b.dataset.fbView))));
})();

/* hide the success / error message */
(function () {
    const t = document.getElementById('fbFlash');
    if (!t) return;
    setTimeout(() => {
        t.style.transition = 'opacity .4s ease'; t.style.opacity = '0';
        setTimeout(() => t.remove(), 400);
        history.replaceState(null, '', location.pathname);
    }, 3500);
})();