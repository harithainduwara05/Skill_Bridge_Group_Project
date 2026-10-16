<?php

include "../../Config/db.php";
include "../../Session/Session.php";

require_role('organization');

// Backend (PHP + SQL) of this page
require_once __DIR__ . "/../../Backend/Organization/feedback.php";

$extra_css = '<link rel="stylesheet" href="../../Assets/CSS/Organization/feedback.css">';
include "../../Includes/org_sidebar.php";
include "../../Includes/dash_header.php";
?>



<main class="content">
    <div class="fb-wrap">

        <!-- Heading -->
        <div class="fb-head">
            <div>
                <h1>Project Feedback &amp; Reviews</h1>
                <p>Manage and analyze technical performance across your academic collaborations.</p>
            </div>
            <button type="button" class="fb-btn solid" id="fbOpenForm"><span class="material-symbols-outlined">add</span>Submit New Feedback</button>
        </div>

        <!-- Stat cards -->
        <div class="fb-stats">
            <div class="fb-stat">
                <div class="fb-stat-top">
                    <div class="fb-stat-icon blue"><span class="material-symbols-outlined">rate_review</span></div>
                </div>
                <div class="fb-stat-label">Feedback Submitted</div>
                <div class="fb-stat-value"><?= (int)$summary['submitted'] ?></div>
            </div>
            <div class="fb-stat">
                <div class="fb-stat-top">
                    <div class="fb-stat-icon sky"><span class="material-symbols-outlined">pending_actions</span></div>
                </div>
                <div class="fb-stat-label">Pending Reviews</div>
                <div class="fb-stat-value"><?= (int)$summary['pending'] ?></div>
            </div>
            <div class="fb-stat">
                <div class="fb-stat-top">
                    <div class="fb-stat-icon green"><span class="material-symbols-outlined" style="font-variation-settings:'FILL' 1;">star</span></div>
                </div>
                <div class="fb-stat-label">Average Rating</div>
                <div class="fb-stat-value"><?= number_format($summary['avg'], 1) ?><small>/5.0</small></div>
            </div>
            <div class="fb-stat">
                <div class="fb-stat-top">
                    <div class="fb-stat-icon sky"><span class="material-symbols-outlined">task_alt</span></div>
                </div>
                <div class="fb-stat-label">Completed Projects</div>
                <div class="fb-stat-value"><?= (int)$summary['completed'] ?></div>
            </div>
        </div>

        <!-- Filters -->
        <div class="fb-filters">
            <div class="fb-search">
                <span class="material-symbols-outlined">search</span>
                <input type="text" id="fbSearch" placeholder="Search by project or team name">
            </div>
            <div class="fb-select-wrap">
                <span class="material-symbols-outlined lead">star</span>
                <select class="fb-select" id="fbRating">
                    <option value="all">All ratings</option>
                    <?php foreach ([5, 4, 3, 2, 1] as $r): ?>
                        <option value="<?= $r ?>"><?= $r ?> Star<?= $r > 1 ? 's' : '' ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="fb-select-wrap">
                <span class="material-symbols-outlined lead">sort</span>
                <select class="fb-select" id="fbSort">
                    <option value="latest">Latest first</option>
                    <option value="oldest">Oldest first</option>
                    <option value="high">Highest rating</option>
                    <option value="low">Lowest rating</option>
                </select>
            </div>
            <button type="button" class="fb-clear" id="fbClear" hidden>Clear</button>
        </div>

        <!-- Review cards -->
        <div class="fb-list">
            <?php foreach ($reviews as $ri => $r): ?>
                <div class="fb-card" data-fb-index="<?= (int)$ri ?>"
                     data-rating="<?= (int)$r['rating'] ?>"
                     data-created="<?= (int)$r['created'] ?>"
                     data-search="<?= htmlspecialchars(strtolower($r['title'] . ' ' . $r['team'])) ?>">

                    <div class="fb-top">
                        <div class="fb-title-row">
                            <div class="fb-proj-icon"><span class="material-symbols-outlined"><?= htmlspecialchars($r['icon']) ?></span></div>
                            <div>
                                <div class="fb-title"><?= htmlspecialchars($r['title']) ?>
                                    <?php if (!empty($r['is_new'])): ?><span class="fb-new">New</span><?php endif; ?></div>
                                <div class="fb-meta">
                                    <span class="material-symbols-outlined">groups</span><?= htmlspecialchars($r['team']) ?>
                                    <span>&bull;</span>
                                    <?= date('M d, Y', strtotime('-' . (int)$r['days_ago'] . ' day')) ?>
                                </div>
                            </div>
                        </div>
                        <div class="fb-rating">
                            <div class="stars"><?= fbStars($r['rating'], 20) ?></div>
                            <div class="fb-rating-label"><?= $ratingLabels[$r['rating']] ?></div>
                        </div>
                    </div>

                    <div class="fb-body">
                        <div class="fb-comp">
                            <div class="fb-label">Competency Breakdown</div>
                            <?php foreach ($r['skills'] as $name => $score): ?>
                                <div class="fb-comp-row">
                                    <div class="fb-comp-top"><span><?= htmlspecialchars($name) ?></span><strong><?= number_format($score, 1) ?></strong></div>
                                    <div class="fb-track"><span style="width:<?= round($score / 5 * 100) ?>%;"></span></div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <div>
                            <div class="fb-section">
                                <div class="fb-label">Performance Summary</div>
                                <p class="fb-summary"><?= htmlspecialchars($r['summary']) ?></p>
                            </div>
                            <div class="fb-section">
                                <div class="fb-label">Suggested Improvements</div>
                                <div class="fb-chips">
                                    <?php foreach ($r['improvements'] as $imp): ?><span class="fb-chip"><?= htmlspecialchars($imp) ?></span><?php endforeach; ?>
                                </div>
                            </div>
                            <div class="fb-foot">
                                <button type="button" class="fb-btn solid" data-fb-view="<?= (int)$ri ?>">View Full Details</button>
                            </div>
                        </div>
                    </div>

                </div>
            <?php endforeach; ?>
        </div>

        <div class="fb-empty" id="fbEmpty" hidden>
            <span class="material-symbols-outlined">search_off</span>
            <div>No feedback matches your filters</div>
        </div>

        <!-- Load more -->
        <div class="fb-more">
            <button type="button" class="fb-btn outline" id="fbLoadMore"><span class="material-symbols-outlined">expand_more</span>Load More Records</button>
            <p>Showing <span id="fbShown"><?= count($reviews) ?></span> of <span id="fbTotal"><?= count($reviews) ?></span> reviews</p>
        </div>

    </div>

    <!-- ===================== FEEDBACK DETAILS (View Full Details) ===================== -->
    <div class="fb-overlay" id="fbViewModal">
        <div class="fb-modal fv-modal" role="dialog" aria-modal="true">
            <div class="fv-head">
                <div class="fv-head-main">
                    <div class="fv-kicker">Feedback Details</div>
                    <h3 id="fvTitle"></h3>
                    <div class="fv-meta" id="fvMeta"></div>
                </div>
                <div class="fv-score">
                    <div class="fv-score-num" id="fvScore"></div>
                    <div class="fv-score-stars" id="fvStars"></div>
                    <div class="fv-score-label" id="fvLabel"></div>
                </div>
                <button type="button" class="fb-x" data-fv-close aria-label="Close"><span class="material-symbols-outlined">close</span></button>
            </div>
            <div class="fv-body" id="fvBody"></div>
            <div class="fb-m-foot fv-foot">
                <button type="button" class="fb-btn outline" id="fvPrint"><span class="material-symbols-outlined">print</span>Print / Save PDF</button>
                <div style="display:flex; gap:10px; margin-left:auto;">
                    <button type="button" class="fb-btn outline" data-fv-close>Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- ===================== SUBMIT NEW FEEDBACK ===================== -->
    <div class="fb-overlay" id="fbModal">
        <form class="fb-modal" method="post" action="feedback.php" id="fbForm" novalidate>
            <input type="hidden" name="action" value="submit_feedback" id="fbAction">
            <input type="hidden" name="feedback_id" id="fbFeedbackId">
            <input type="hidden" name="rating" id="fbRatingVal">

            <div class="fb-m-head">
                <div class="fb-m-icon"><span class="material-symbols-outlined">rate_review</span></div>
                <div>
                    <h3 id="fbFormTitle">Submit New Feedback</h3>
                    <p id="fbFormSub">Rate the team’s work and help them improve.</p>
                </div>
                <button type="button" class="fb-x" data-fb-close aria-label="Close"><span class="material-symbols-outlined">close</span></button>
            </div>

            <div class="fb-m-body">
                <?php if (empty($fbProjects)): ?>
                    <div class="fb-note">
                        <span class="material-symbols-outlined">info</span>
                        None of your projects have students working on them yet. Feedback can be given once a team is assigned.
                    </div>
                <?php else: ?>

                <!-- 1. Project -->
                <div class="fb-step"><span>1</span> Project &amp; Team</div>
                <div class="fb-field">
                    <label class="fb-f-label" for="fbProject">Project *</label>
                    <select id="fbProject" name="project_id" class="fb-input">
                        <option value="">Select a project</option>
                        <?php foreach ($fbProjects as $fp): ?>
                            <option value="<?= $fp['id'] ?>"><?= htmlspecialchars($fp['title']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <div class="fb-err" data-err="project"></div>
                </div>
                <div class="fb-team" id="fbTeamBox" hidden></div>

                <!-- 2. Ratings -->
                <div class="fb-step"><span>2</span> Ratings</div>
                <div class="fb-field">
                    <label class="fb-f-label">Overall Rating *</label>
                    <div class="fb-overall">
                        <div class="fb-star-pick" id="fbStarPick">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <button type="button" data-v="<?= $i ?>" aria-label="<?= $i ?> star"><span class="material-symbols-outlined">star</span></button>
                            <?php endfor; ?>
                        </div>
                        <span class="fb-star-text" id="fbStarText">Click to rate</span>
                    </div>
                    <div class="fb-err" data-err="rating"></div>
                </div>

                <div class="fb-f-label" style="margin-top:4px;">Competency Breakdown *</div>
                <div class="fb-comp-grid">
                    <?php foreach (['technical' => 'Technical Skills', 'communication' => 'Communication', 'teamwork' => 'Teamwork', 'problem_solving' => 'Problem-solving'] as $k => $label): ?>
                        <div class="fb-slider">
                            <div class="fb-slider-top"><span><?= $label ?></span><strong data-out="<?= $k ?>">3.0</strong></div>
                            <input type="range" name="<?= $k ?>" min="1" max="5" step="0.5" value="3" data-range="<?= $k ?>">
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- 3. Written feedback -->
                <div class="fb-step"><span>3</span> Written Feedback</div>
                <div class="fb-field">
                    <label class="fb-f-label" for="fbSummary">Performance Summary *</label>
                    <textarea id="fbSummary" name="summary" class="fb-input" rows="4" maxlength="1500"
                              placeholder="What did the team do well? How was the quality of their work and their communication?"></textarea>
                    <div class="fb-counter"><span class="fb-err" data-err="summary"></span><span><span id="fbCount">0</span>/1500</span></div>
                </div>

                <div class="fb-field">
                    <label class="fb-f-label">Suggested Improvements</label>
                    <div class="fb-chip-box" id="fbChipBox">
                        <input type="text" id="fbChipInput" placeholder="Type a skill to improve and press Enter">
                    </div>
                    <input type="hidden" name="improvements" id="fbImprovements">
                    <div class="fb-quick">
                        <span>Quick add:</span>
                        <?php foreach (['Documentation', 'Unit Testing', 'Time Management', 'Code Reviews', 'Git Workflow'] as $qa): ?>
                            <button type="button" data-quick="<?= $qa ?>">+ <?= $qa ?></button>
                        <?php endforeach; ?>
                    </div>
                </div>


                <?php endif; ?>
            </div>

            <div class="fb-m-foot">
                <button type="button" class="fb-btn outline" data-fb-close>Cancel</button>
                <?php if (!empty($fbProjects)): ?>
                <button type="submit" class="fb-btn solid"><span class="material-symbols-outlined" id="fbSubmitIcon">send</span><span id="fbSubmitText">Submit Feedback</span></button>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <?php if ($flash): ?>
    <div class="flash-toast flash-<?= htmlspecialchars($flash['type']) ?>" id="fbFlash">
        <div class="flash-icon-wrap"><span class="material-symbols-outlined"><?= $flash['type'] === 'success' ? 'check_circle' : 'error' ?></span></div>
        <div class="flash-content">
            <div class="flash-title" style="font-weight:700; font-size:14px;"><?= htmlspecialchars($flash['title']) ?></div>
            <div class="flash-msg" style="font-size:13px; margin-top:2px;"><?= htmlspecialchars($flash['message']) ?></div>
        </div>
    </div>
    <?php endif; ?>
</main>

<script>
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
    const PROJECTS = <?= json_encode($fbProjects, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?>;
    const LABELS = { 1: 'Needs Work', 2: 'Fair', 3: 'Good', 4: 'Very Good', 5: 'Exceptional' };
    const STATUS = { reviewing: 'Reviewing', inprogress: 'Active', completed: 'Completed', hold: 'Hold' };
    const COLORS = ['#1e3a5f', '#0f766e', '#7c3aed', '#b45309', '#be185d', '#2563eb', '#475569'];

    const modal = document.getElementById('fbModal');
    const form  = document.getElementById('fbForm');
    const esc = t => { const d = document.createElement('div'); d.textContent = t == null ? '' : String(t); return d.innerHTML; };
    const initials = n => String(n || '').trim().split(/\s+/).slice(0, 2).map(w => w.charAt(0).toUpperCase()).join('');
    const colorFor = n => { let h = 0; for (const c of String(n)) h = (h * 31 + c.charCodeAt(0)) >>> 0; return COLORS[h % COLORS.length]; };
    const err = (k, m) => { const e = form.querySelector('[data-err="' + k + '"]'); if (e) e.textContent = m || ''; };

    function open() { modal.classList.add('open'); }
    function close() { modal.classList.remove('open'); }
    document.getElementById('fbOpenForm').addEventListener('click', () => window.fbOpenForm ? window.fbOpenForm(null) : open());

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
    if (!projectEl) return;          // no projects -> only the info note is shown

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
        // editing a demo review (not in the database) -> save in this browser
        if (editingDemo) {
            e.preventDefault();
            const val = n => Number(form.querySelector('[data-range="' + n + '"]').value);
            window.fbSaveDemo(editingDemo, {
                rating: Number(ratingVal.value),
                skills: { 'Technical Skills': val('technical'), 'Communication': val('communication'),
                          'Teamwork': val('teamwork'), 'Problem-solving': val('problem_solving') },
                summary: summary.value.trim(),
                improvements: chips.slice(),
                shared: true,
                updated: new Date().toLocaleDateString('en-US', { month: 'short', day: '2-digit', year: 'numeric' })
            });
            close();
        }
    });

    // ---------- open the form: new (r = null) or edit (r = review) ----------
    let editingDemo = null;
    const tempOptClass = 'fb-temp-opt';
    window.fbOpenForm = function (r) {
        form.reset();
        form.querySelectorAll('.fb-err').forEach(x => x.textContent = '');
        form.querySelectorAll('.invalid').forEach(x => x.classList.remove('invalid'));
        projectEl.querySelectorAll('.' + tempOptClass).forEach(o => o.remove());
        projectEl.disabled = false;
        projectEl.classList.remove('fb-select-locked');
        document.getElementById('fbTeamBox').hidden = true;
        editingDemo = null;

        const edit = !!r;
        document.getElementById('fbAction').value = edit ? 'update_feedback' : 'submit_feedback';
        document.getElementById('fbFeedbackId').value = edit && r.id ? r.id : '';
        document.getElementById('fbFormTitle').textContent = edit ? 'Edit Feedback' : 'Submit New Feedback';
        document.getElementById('fbFormSub').textContent = edit ? 'Update the ratings and comments for this team.' : 'Rate the team’s work and help them improve.';
        document.getElementById('fbSubmitText').textContent = edit ? 'Save Changes' : 'Submit Feedback';
        document.getElementById('fbSubmitIcon').textContent = edit ? 'check' : 'send';

        // ratings
        ratingVal.value = edit ? r.rating : '';
        paint(edit ? r.rating : 0);
        starText.textContent = edit ? r.rating + '/5 · ' + LABELS[r.rating] : 'Click to rate';
        starText.classList.toggle('set', edit);
        const map = { technical: 'Technical Skills', communication: 'Communication', teamwork: 'Teamwork', problem_solving: 'Problem-solving' };
        Object.keys(map).forEach(k => {
            const v = edit ? Number(r.skills[map[k]] || 3) : 3;
            form.querySelector('[data-range="' + k + '"]').value = v;
            form.querySelector('[data-out="' + k + '"]').textContent = v.toFixed(1);
        });

        summary.value = edit ? r.summary : '';
        document.getElementById('fbCount').textContent = summary.value.length;
        chips = edit ? r.improvements.slice() : [];
        renderChips();

        if (edit) {
            // the project of a feedback can't be changed
            let opt = Array.from(projectEl.options).find(o => r.project_id && Number(o.value) === r.project_id);
            if (!opt) {
                opt = new Option(r.title, r.project_id || 'demo', true, true);
                opt.className = tempOptClass;
                projectEl.add(opt);
            }
            projectEl.value = opt.value;
            projectEl.disabled = true;
            projectEl.classList.add('fb-select-locked');
            const box = document.getElementById('fbTeamBox');
            box.innerHTML = `
                <div class="fb-team-top"><span class="material-symbols-outlined">groups</span>${esc(r.team)}</div>
                <div class="fb-people">${(r.members || []).map(st =>
                    `<span class="fb-person"><i style="background:${colorFor(st.name)}">${esc(initials(st.name))}</i>${esc(st.name)}${st.role ? ' · ' + esc(st.role) : ''}</span>`).join('')}</div>`;
            box.hidden = false;
            if (!r.id) editingDemo = r.key;
        }
        open();
    };
})();

/* ================= VIEW FULL DETAILS ================= */
(function () {
    const REVIEWS = <?= json_encode(array_values(array_map(fn($r) => [
                        'key' => $r['key'], 'id' => $r['id'] ?? null, 'project_id' => $r['project_id'] ?? null,
                        'title' => $r['title'], 'team' => $r['team'], 'category' => $r['category'] ?? '',
                        'date' => date('M d, Y', $r['created']), 'rating' => (int)$r['rating'], 'skills' => $r['skills'],
                        'summary' => $r['summary'], 'improvements' => $r['improvements'], 'shared' => !empty($r['shared']),
                        'updated' => $r['updated'] ?? '', 'members' => $r['members'] ?? [],
                    ], $reviews)), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?>;
    const LABELS = { 1: 'Needs Work', 2: 'Fair', 3: 'Good', 4: 'Very Good', 5: 'Exceptional' };
    const COLORS = ['#1e3a5f', '#0f766e', '#7c3aed', '#b45309', '#be185d', '#2563eb', '#475569'];
    const OV_KEY = 'sb_demo_feedback_overrides';

    const esc = t => { const d = document.createElement('div'); d.textContent = t == null ? '' : String(t); return d.innerHTML; };
    const initials = n => String(n || '').trim().split(/\s+/).slice(0, 2).map(w => w.charAt(0).toUpperCase()).join('');
    const colorFor = n => { let h = 0; for (const c of String(n)) h = (h * 31 + c.charCodeAt(0)) >>> 0; return COLORS[h % COLORS.length]; };
    const loadOv = () => { try { return JSON.parse(localStorage.getItem(OV_KEY)) || {}; } catch (e) { return {}; } };
    const stars = (v, size) => { let h = ''; for (let i = 1; i <= 5; i++) {
        const icon = v >= i ? 'star' : (v >= i - 0.5 ? 'star_half' : 'star');
        h += `<span class="material-symbols-outlined fb-star ${v >= i - 0.5 ? 'on' : ''}" style="font-size:${size}px;">${icon}</span>`; } return h; };

    const effective = i => Object.assign({}, REVIEWS[i], REVIEWS[i].id ? {} : (loadOv()[REVIEWS[i].key] || {}));

    // update a card on the page after a demo edit
    function updateCard(i) {
        const r = effective(i);
        const card = document.querySelector('.fb-card[data-fb-index="' + i + '"]');
        if (!card) return;
        card.dataset.rating = r.rating;
        card.querySelector('.fb-rating .stars').innerHTML = stars(r.rating, 20);
        card.querySelector('.fb-rating-label').textContent = LABELS[r.rating];
        const rows = card.querySelectorAll('.fb-comp-row');
        Object.values(r.skills).forEach((v, k) => {
            if (!rows[k]) return;
            rows[k].querySelector('strong').textContent = Number(v).toFixed(1);
            rows[k].querySelector('.fb-track span').style.width = Math.round(v / 5 * 100) + '%';
        });
        card.querySelector('.fb-summary').textContent = r.summary;
        card.querySelector('.fb-chips').innerHTML = r.improvements.map(x => `<span class="fb-chip">${esc(x)}</span>`).join('');
    }
    REVIEWS.forEach((r, i) => { if (!r.id && loadOv()[r.key]) updateCard(i); });

    window.fbSaveDemo = function (key, data) {
        const all = loadOv(); all[key] = data;
        try { localStorage.setItem(OV_KEY, JSON.stringify(all)); } catch (e) {}
        const i = REVIEWS.findIndex(r => r.key === key);
        updateCard(i);
        const t = document.createElement('div');
        t.className = 'flash-toast flash-success';
        t.innerHTML = '<div class="flash-icon-wrap"><span class="material-symbols-outlined">check_circle</span></div><div class="flash-content"><div class="flash-title" style="font-weight:700;font-size:14px;">Feedback updated</div></div>';
        document.body.appendChild(t);
        setTimeout(() => { t.style.transition = 'opacity .4s'; t.style.opacity = '0'; setTimeout(() => t.remove(), 400); }, 2500);
    };

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
                    <div class="fv-info-row"><span>Shared with students</span><strong style="color:${r.shared ? '#15803d' : '#6b7280'}">${r.shared ? 'Yes' : 'No'}</strong></div>
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