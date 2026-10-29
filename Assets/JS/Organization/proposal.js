/*
| Organization - proposal page script
| Used by: Functions/Organization/proposal.php
| Values from PHP / the database come from ORG_DATA (set in a small <script> on the page).
*/

/* ================================================================
   PROPOSAL DATA
================================================================ */

const proposalData = ORG_DATA.proposalData;

const statusLabels = ORG_DATA.statusLabels;

const projectDetails = ORG_DATA.projectDetails;

const studentProfiles = ORG_DATA.studentProfiles;


/* ================================================================
   DOM ELEMENTS
================================================================ */

const searchInput = document.getElementById('proposalSearch');

const statusFilter = document.getElementById('statusFilter');

const timeFilter = document.getElementById('timeFilter');

const sortButton = document.getElementById('sortButton');

const sortText = document.getElementById('sortText');

const proposalList = document.getElementById('proposalList');

const emptyResults = document.getElementById('emptyResults');

const proposalTotal = document.getElementById('proposalTotal');


let newestFirst = true;

let pendingAction = null;


/* ================================================================
   LOCAL STORAGE
================================================================ */

const STORAGE_KEY = 'skillbridge_organization_proposal_statuses';

// Clear the statuses the old demo version saved in this browser
try { localStorage.removeItem(STORAGE_KEY); } catch (e) {}


function loadSavedStatuses()
{
    try {

        const saved = localStorage.getItem(STORAGE_KEY);

        if (!saved) {
            return {};
        }

        return JSON.parse(saved);

    } catch (error) {

        return {};

    }
}


function saveStatuses(statuses)
{
    localStorage.setItem(
        STORAGE_KEY,
        JSON.stringify(statuses)
    );
}


function getCurrentStatus(id)
{
    // The status comes from the database (project_proposals.status).
    // (Statuses saved in the browser by the old demo version are ignored.)
    const proposal = proposalData.find(
        p => Number(p.id) === Number(id)
    );

    return proposal ? proposal.status : 'pending';
}


/* ================================================================
   APPLY SAVED STATUS
================================================================ */

function applySavedStatuses()
{
    const cards = document.querySelectorAll('.pr-card');

    cards.forEach(card => {

        const id = card.dataset.id;

        const status = getCurrentStatus(id);

        updateCardStatus(
            card,
            status,
            false
        );

    });

}


/* ================================================================
   UPDATE CARD STATUS
================================================================ */

function updateCardStatus(
    card,
    newStatus,
    notify = true   // renamed: "showToast" here hid the showToast() function and crashed
)
{

    card.dataset.status = newStatus;


    /* Status badge */

    const badge = card.querySelector(
        '[data-status-badge]'
    );

    if (badge) {

        badge.className =
            'pr-badge ' + newStatus;

        badge.textContent =
            statusLabels[newStatus];

        // Only "Accepted" / "Rejected" are ever shown as a badge on the
        // card — "Pending" stays hidden (it's just the default state).
        badge.style.display =
            newStatus === 'pending' ? 'none' : '';

    }


    /* Buttons */

    const acceptBtn =
        card.querySelector('.accept-btn');

    const rejectBtn =
        card.querySelector('.reject-btn');


    if (acceptBtn) {

        acceptBtn.disabled =
            newStatus === 'accepted' ||
            newStatus === 'rejected';

    }


    if (rejectBtn) {

        rejectBtn.disabled =
            newStatus === 'accepted' ||
            newStatus === 'rejected';

    }

    /* Hide the Accept / Reject buttons once a decision is made */

    const actions =
        card.querySelector('.pr-actions');

    if (actions) {

        actions.hidden =
            newStatus === 'accepted' ||
            newStatus === 'rejected';

    }


    /* Save */

    const statuses = loadSavedStatuses();

    statuses[card.dataset.id] =
        newStatus;

    saveStatuses(statuses);


    if (notify) {

        if (newStatus === 'accepted') {
            showToast('The proposal was accepted successfully.', 'Proposal accepted');
        }

        if (newStatus === 'rejected') {
            showToast('The proposal was rejected successfully.', 'Proposal rejected');
        }

    }

    applyFilters(true);   // keep the cards already loaded with "Load More"
}


/* ================================================================
   FILTER PROPOSALS
================================================================ */

const PAGE_SIZE = 5;           // proposals shown first, and per "Load More"
let shownLimit = PAGE_SIZE;
const loadMoreBox = document.getElementById('prMore');
const loadMoreBtn = document.getElementById('prLoadMore');

function applyFilters(keepLimit)
{
    // a filter / search / sort change starts again from the first 5
    if (keepLimit !== true) shownLimit = PAGE_SIZE;

    const search =
        searchInput.value
            .trim()
            .toLowerCase();

    const selectedStatus =
        statusFilter.value;

    const selectedTime =
        timeFilter.value;


    const cards =
        Array.from(
            document.querySelectorAll('.pr-card')
        );


    let visibleCount = 0;


    cards.forEach(card => {

        const name =
            card.dataset.name || '';

        const school =
            card.dataset.school || '';

        const project =
            card.dataset.project || '';

        const status =
            card.dataset.status || '';

        const daysAgo =
            parseInt(card.dataset.days, 10) || 0;

        const skills =
            card.dataset.skills || '';


        const searchMatch =
            !search ||
            name.includes(search) ||
            school.includes(search) ||
            project.toLowerCase().includes(search) ||
            skills.includes(search);


        const statusMatch =
            selectedStatus === 'all' ||
            status === selectedStatus;

        const timeMatch =
            selectedTime === 'all' ||
            (selectedTime === 'daily' && daysAgo <= 0) ||
            (selectedTime === 'weekly' && daysAgo <= 7) ||
            (selectedTime === 'monthly' && daysAgo <= 30);


        const shouldShow =
            searchMatch &&
            statusMatch &&
            timeMatch;


        if (shouldShow) {

            visibleCount++;

            // matching, but only the first "shownLimit" are shown
            card.classList.toggle('hidden', visibleCount > shownLimit);

        } else {

            card.classList.add('hidden');

        }

    });


    emptyResults.classList.toggle(
        'show',
        visibleCount === 0
    );

    // "Load More" + "Showing x of y"
    if (loadMoreBox) {
        const shown = Math.min(shownLimit, visibleCount);
        document.getElementById('prShown').textContent = shown;
        document.getElementById('prTotal').textContent = visibleCount;
        loadMoreBox.hidden = visibleCount === 0;
        loadMoreBtn.hidden = shown >= visibleCount;
    }

}

if (loadMoreBtn) {
    loadMoreBtn.addEventListener('click', function () {
        const firstNew = shownLimit;
        shownLimit += PAGE_SIZE;
        applyFilters(true);
        // scroll gently to the first newly loaded proposal
        const visible = Array.from(document.querySelectorAll('.pr-card:not(.hidden)'));
        if (visible[firstNew]) visible[firstNew].scrollIntoView({ behavior: 'smooth', block: 'start' });
    });
}


/* ================================================================
   SORT
================================================================ */

function sortCards()
{

    const cards =
        Array.from(
            document.querySelectorAll('.pr-card')
        );


    cards.sort((a, b) => {

        const aDays =
            Number(a.dataset.days);

        const bDays =
            Number(b.dataset.days);


        if (newestFirst) {

            return aDays - bDays;

        } else {

            return bDays - aDays;

        }

    });


    cards.forEach(card => {

        proposalList.appendChild(card);

    });


    sortText.textContent =
        newestFirst
            ? 'Sort by Newest'
            : 'Sort by Oldest';


    applyFilters();

}


/* ================================================================
   OPEN MODAL
================================================================ */

function openModal(id)
{

    const modal =
        document.getElementById(id);

    if (modal) {

        modal.classList.add('show');

        document.body.style.overflow =
            'hidden';

    }

}


/* ================================================================
   CLOSE MODAL
================================================================ */

function closeModal(id)
{

    const modal =
        document.getElementById(id);

    if (modal) {

        modal.classList.remove('show');

        document.body.style.overflow =
            '';

    }

}


/* ================================================================
   CLOSE ALL MODALS
================================================================ */

function closeAllModals()
{

    document
        .querySelectorAll('.pr-modal-overlay')
        .forEach(modal => {

            modal.classList.remove('show');

        });

    document.body.style.overflow = '';

}


/* ================================================================
   FULL PROPOSAL
================================================================ */

function showFullProposal(id)
{
    const proposal = proposalData.find(p => Number(p.id) === Number(id));
    if (!proposal) {
        return;
    }

    const e       = escapeHtml;
    const status  = getCurrentStatus(id);
    const sp      = studentProfiles[proposal.email] || {};
    const project = projectDetails[proposal.project] || null;

    const avatar = sp.image
        ? `<img src="${e(sp.image)}" alt="">`
        : e(getInitials(proposal.name));

    const skillsHtml = (proposal.skills || [])
        .map(sk => `<span class="pf-chip">${e(sk)}</span>`).join('');

    const planHtml = (proposal.plan || []).map((step, i) => `
        <li>
            <span class="pf-dot">${i + 1}</span>
            <div>
                <div class="pf-when">${e(step.when)}</div>
                <div class="pf-task">${e(step.task)}</div>
            </div>
        </li>`).join('');

    const projectRows = project ? [
        ['Organization', project.company],
        ['Category', project.category],
        ['Duration', project.duration],
        ['Students needed', project.members],
        ['Difficulty', project.difficulty],
        ['Deadline', project.deadline]
    ].filter(r => r[1] !== null && r[1] !== undefined && r[1] !== '')
     .map(r => `<div class="pf-row"><span>${e(r[0])}</span><strong>${e(String(r[1]))}</strong></div>`).join('')
    : '';

    const yearDegree = [sp.degree ? e(sp.degree) : '', sp.year ? 'Year ' + e(sp.year) : ''].filter(Boolean).join(' &bull; ');

    document.getElementById('proposalModalBody').innerHTML = `
        <div class="pf-hero">
            <button type="button" class="pf-close" data-close-modal="proposalModal" aria-label="Close">
                <span class="material-symbols-outlined">close</span>
            </button>
            <div class="pf-kicker">Project Proposal</div>
            <h2 class="pf-title">${e(proposal.project)}</h2>
            <div class="pf-hero-meta">
                <span class="pr-badge ${status}">${e(statusLabels[status])}</span>
                <span><span class="material-symbols-outlined">calendar_today</span> Submitted ${e(proposal.submitted_on || '')}</span>
                <span><span class="material-symbols-outlined">schedule</span> ${Number(proposal.days_ago)} day${Number(proposal.days_ago) === 1 ? '' : 's'} ago</span>
            </div>
        </div>

        <div class="pf-student">
            <div class="pf-avatar">${avatar}</div>
            <div class="pf-student-info">
                <div class="pf-name">${e(proposal.name)}</div>
                <div class="pf-sub">${yearDegree || e(proposal.school)}</div>
                <div class="pf-sub muted"><span class="material-symbols-outlined">school</span>${e(sp.university || proposal.school)}</div>
            </div>
            <div class="pf-mini-stats">
                <div><strong>${(sp.skills || []).length}</strong><span>Skills</span></div>
                <div><strong>${(sp.certificates || []).length}</strong><span>Certificates</span></div>
                <div><strong>${(sp.portfolio || []).length}</strong><span>Portfolio</span></div>
            </div>
        </div>

        <div class="pf-grid">
            <div class="pf-main">

                <section class="pf-sec">
                    <h4><span class="material-symbols-outlined">lightbulb</span> Proposal Summary</h4>
                    <p>${e(proposal.summary)}</p>
                </section>

                ${proposal.motivation ? `
                <section class="pf-sec">
                    <h4><span class="material-symbols-outlined">star</span> Why I’m a Good Fit</h4>
                    <p>${e(proposal.motivation)}</p>
                </section>` : ''}

                ${planHtml ? `
                <section class="pf-sec">
                    <h4><span class="material-symbols-outlined">timeline</span> Work Plan</h4>
                    <ol class="pf-plan">${planHtml}</ol>
                </section>` : ''}

                <section class="pf-sec">
                    <h4><span class="material-symbols-outlined">bolt</span> Skills Offered</h4>
                    <div class="pf-chips">${skillsHtml || '<span class="pf-muted">No skills listed.</span>'}</div>
                </section>

            </div>

            <aside class="pf-side">

                <div class="pf-box">
                    <div class="pf-box-title"><span class="material-symbols-outlined">event_available</span> Availability</div>
                    <div class="pf-row"><span>Time per week</span><strong>${e(proposal.availability || '-')}</strong></div>
                    <div class="pf-row"><span>Can start</span><strong>${e(proposal.start || '-')}</strong></div>
                    <div class="pf-row"><span>Estimated time</span><strong>${e(proposal.estimate || '-')}</strong></div>
                    <div class="pf-row"><span>Preferred role</span><strong>${e(proposal.role || '-')}</strong></div>
                </div>

                <div class="pf-box">
                    <div class="pf-box-title"><span class="material-symbols-outlined">work</span> Applied Project</div>
                    <div class="pf-project-name">${e(proposal.project)}</div>
                    ${projectRows || '<div class="pf-muted">Project details are not available.</div>'}
                </div>

                <div class="pf-box">
                    <div class="pf-box-title"><span class="material-symbols-outlined">alternate_email</span> Contact</div>
                    <div class="pf-contact">${e(proposal.email)}</div>
                    ${sp.github ? `<a class="pf-link" href="${e(sp.github)}" target="_blank" rel="noopener">GitHub <span class="material-symbols-outlined">open_in_new</span></a>` : ''}
                    ${sp.linkedin ? `<a class="pf-link" href="${e(sp.linkedin)}" target="_blank" rel="noopener">LinkedIn <span class="material-symbols-outlined">open_in_new</span></a>` : ''}
                </div>

            </aside>
        </div>
    `;

    // Footer: Accept / Reject only while still pending
    const pending = status === 'pending';
    document.getElementById('proposalModalFoot').innerHTML = `
        <button type="button" class="pr-btn pf-ghost" data-pf-action="profile" data-id="${proposal.id}">
            <span class="material-symbols-outlined">person</span> View Profile
        </button>
        <div class="pf-foot-right">
            ${pending ? `
            <button type="button" class="pr-btn reject" data-pf-action="reject" data-id="${proposal.id}">
                <span class="material-symbols-outlined">close</span> Reject
            </button>
            <button type="button" class="pr-btn accept" data-pf-action="accept" data-id="${proposal.id}">
                <span class="material-symbols-outlined">check</span> Accept
            </button>` : `
            <button type="button" class="pr-btn pr-cancel-btn" data-close-modal="proposalModal">Close</button>`}
        </div>
    `;

    document.getElementById('proposalModalBody').scrollTop = 0;
    openModal('proposalModal');
}

// Buttons inside the Full Proposal popup
document.getElementById('proposalModalFoot').addEventListener('click', function (ev) {
    const btn = ev.target.closest('[data-pf-action]');
    if (!btn) {
        return;
    }
    const id = btn.dataset.id;
    closeModal('proposalModal');

    if (btn.dataset.pfAction === 'profile') { showProfile(id); }
    if (btn.dataset.pfAction === 'accept')  { openAcceptModal(id); }
    if (btn.dataset.pfAction === 'reject')  { openRejectModal(id); }
});


/* ================================================================
   PROFILE
================================================================ */

function showProfile(id, pushHistory = true)
{
    const proposal = proposalData.find(p => Number(p.id) === Number(id));
    if (!proposal) {
        return;
    }

    const sp = studentProfiles[proposal.email] || {
        name: proposal.name, university: proposal.school, degree: '', year: '', bio: '',
        image: '', github: '', linkedin: '', website: '', email: proposal.email,
        skills: [], portfolio: [], projects: [], certificates: []
    };

    const e = escapeHtml;

    // ---------- header ----------
    const avatar = sp.image
        ? `<img src="${e(sp.image)}" alt="${e(sp.name)}">`
        : e(getInitials(sp.name));

    const yearText = sp.year ? `Year ${e(sp.year)}` : '';
    const degreeLine = [e(sp.degree), yearText].filter(Boolean).join(' &bull; ');

    const GITHUB_SVG = '<svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor"><path d="M12 .5a12 12 0 0 0-3.8 23.4c.6.1.8-.3.8-.6v-2.2c-3.3.7-4-1.4-4-1.4-.6-1.4-1.4-1.8-1.4-1.8-1.1-.8.1-.7.1-.7 1.2.1 1.9 1.2 1.9 1.2 1.1 1.8 2.8 1.3 3.5 1 .1-.8.4-1.3.8-1.6-2.7-.3-5.5-1.3-5.5-5.9 0-1.3.5-2.4 1.2-3.2-.1-.3-.5-1.5.1-3.2 0 0 1-.3 3.3 1.2a11.5 11.5 0 0 1 6 0C17.3 4.7 18.3 5 18.3 5c.7 1.7.3 2.9.1 3.2.8.8 1.2 1.9 1.2 3.2 0 4.6-2.8 5.6-5.5 5.9.4.4.8 1.1.8 2.2v3.3c0 .3.2.7.8.6A12 12 0 0 0 12 .5z"/></svg>';
    const LINKEDIN_SVG = '<svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor"><path d="M20.4 20.5h-3.6v-5.6c0-1.3 0-3-1.8-3s-2.1 1.4-2.1 2.9v5.7H9.3V9h3.4v1.6h.1c.5-.9 1.6-1.8 3.4-1.8 3.6 0 4.3 2.4 4.3 5.5v6.2zM5.3 7.4a2.1 2.1 0 1 1 0-4.2 2.1 2.1 0 0 1 0 4.2zM7.1 20.5H3.5V9h3.6v11.5zM22.2 0H1.8C.8 0 0 .8 0 1.7v20.6c0 .9.8 1.7 1.8 1.7h20.4c1 0 1.8-.8 1.8-1.7V1.7C24 .8 23.2 0 22.2 0z"/></svg>';

    const socials = [
        sp.github   ? `<a class="sp-social" href="${e(sp.github)}" target="_blank" rel="noopener">${GITHUB_SVG} GitHub</a>` : '',
        sp.linkedin ? `<a class="sp-social" href="${e(sp.linkedin)}" target="_blank" rel="noopener">${LINKEDIN_SVG} LinkedIn</a>` : '',
        sp.website  ? `<a class="sp-social" href="${e(sp.website)}" target="_blank" rel="noopener"><span class="material-symbols-outlined">language</span> Website</a>` : '',
        sp.email    ? `<a class="sp-social" href="mailto:${e(sp.email)}"><span class="material-symbols-outlined">mail</span> Email</a>` : ''
    ].join('');

    // ---------- skills ----------
    const skillsHtml = sp.skills.length
        ? sp.skills.map(sk => `
            <div class="sp-skill">
                <div class="sp-skill-top">
                    <span class="sp-skill-name">${e(sk.skill_name)}</span>
                    <span class="sp-skill-level">${e(sk.level || '')}</span>
                </div>
                <div class="sp-bar"><span style="width:${Math.max(0, Math.min(100, Number(sk.percentage) || 0))}%"></span></div>
                ${sk.category ? `<div class="sp-skill-cat">${e(sk.category)}</div>` : ''}
            </div>`).join('')
        : '<p class="sp-empty">No skills added yet.</p>';

    // ---------- portfolio ----------
    const portfolioHtml = sp.portfolio.length
        ? sp.portfolio.map(pf => `
            <div class="sp-card">
                <div class="sp-card-icon"><span class="material-symbols-outlined">code_blocks</span></div>
                <div class="sp-card-body">
                    <div class="sp-card-title">${e(pf.title || 'Untitled project')}</div>
                    ${pf.description ? `<div class="sp-card-text">${e(pf.description)}</div>` : ''}
                    ${pf.project_link ? `<a class="sp-card-link" href="${e(pf.project_link)}" target="_blank" rel="noopener">View project <span class="material-symbols-outlined">open_in_new</span></a>` : ''}
                </div>
            </div>`).join('')
        : '<p class="sp-empty">No portfolio projects yet.</p>';

    // ---------- SkillBridge projects ----------
    const projectsHtml = sp.projects.length
        ? sp.projects.map(pr => {
            const pct = Math.max(0, Math.min(100, Number(pr.progress) || 0));
            const done = (pr.status || '').toLowerCase() === 'completed';
            return `
            <div class="sp-proj">
                <div class="sp-proj-top">
                    <div>
                        <div class="sp-card-title">${e(pr.title)}</div>
                        <div class="sp-proj-meta">${e(pr.role || 'Team member')} &bull; ${e(pr.company || '')}</div>
                    </div>
                    <span class="sp-status ${done ? 'done' : 'active'}">${e(pr.status || 'In Progress')}</span>
                </div>
                <div class="sp-bar ${done ? 'done' : ''}"><span style="width:${pct}%"></span></div>
                <div class="sp-proj-pct">${pct}% complete</div>
            </div>`;
        }).join('')
        : '<p class="sp-empty">No SkillBridge projects yet.</p>';

    // ---------- certificates ----------
    const certsHtml = sp.certificates.length
        ? sp.certificates.map(c => {
            const ok = (c.status || '').toLowerCase() === 'approved';
            return `
            <div class="sp-cert">
                <div class="sp-cert-icon"><span class="material-symbols-outlined">workspace_premium</span></div>
                <div class="sp-card-body">
                    <div class="sp-card-title">${e(c.certificate_name || 'Certificate')}</div>
                    <div class="sp-proj-meta">${e(c.issuer || '')}</div>
                </div>
                <span class="sp-status ${ok ? 'done' : 'pending'}">${ok ? 'Approved' : e(c.status || 'Pending')}</span>
            </div>`;
        }).join('')
        : '<p class="sp-empty">No certificates yet.</p>';

    document.getElementById('spBody').innerHTML = `
        <div class="sp-headcard">
            <div class="sp-avatar">${avatar}</div>
            <div class="sp-head-info">
                <h2>${e(sp.name)}</h2>
                ${degreeLine ? `<div class="sp-degree">${degreeLine}</div>` : ''}
                ${sp.university ? `<div class="sp-uni">${e(sp.university)}</div>` : ''}
                <div class="sp-applied">
                    <span class="material-symbols-outlined">assignment</span>
                    Applied for <strong>${e(proposal.project)}</strong>
                </div>
                ${socials ? `<div class="sp-socials">${socials}</div>` : ''}
            </div>
        </div>

        <div class="sp-stats">
            <div class="sp-stat"><span class="sp-stat-icon blue"><span class="material-symbols-outlined">bolt</span></span>
                <div><div class="sp-stat-label">Skills</div><div class="sp-stat-value">${sp.skills.length}</div></div></div>
            <div class="sp-stat"><span class="sp-stat-icon green"><span class="material-symbols-outlined">folder_special</span></span>
                <div><div class="sp-stat-label">Portfolio</div><div class="sp-stat-value">${sp.portfolio.length}</div></div></div>
            <div class="sp-stat"><span class="sp-stat-icon gray"><span class="material-symbols-outlined">work</span></span>
                <div><div class="sp-stat-label">Projects</div><div class="sp-stat-value">${sp.projects.length}</div></div></div>
            <div class="sp-stat"><span class="sp-stat-icon orange"><span class="material-symbols-outlined">workspace_premium</span></span>
                <div><div class="sp-stat-label">Certificates</div><div class="sp-stat-value">${sp.certificates.length}</div></div></div>
        </div>

        ${sp.bio ? `
        <section class="sp-section">
            <h4><span class="material-symbols-outlined">person</span> About</h4>
            <p class="sp-bio">${e(sp.bio)}</p>
        </section>` : ''}

        <section class="sp-section">
            <h4><span class="material-symbols-outlined">bolt</span> Skills</h4>
            <div class="sp-skills">${skillsHtml}</div>
        </section>

        <section class="sp-section">
            <h4><span class="material-symbols-outlined">work</span> SkillBridge Projects</h4>
            <div class="sp-list">${projectsHtml}</div>
        </section>

        <section class="sp-section">
            <h4><span class="material-symbols-outlined">folder_special</span> Portfolio</h4>
            <div class="sp-list">${portfolioHtml}</div>
        </section>

        <section class="sp-section">
            <h4><span class="material-symbols-outlined">verified</span> Certificates</h4>
            <div class="sp-list">${certsHtml}</div>
        </section>
    `;

    openProfileView(proposal.id, pushHistory);
}


/* ---- Same-page profile view with Back (also works with the browser Back button) ---- */

let listScrollY = 0;

function openProfileView(id, pushHistory = true)
{
    listScrollY = window.scrollY;

    document.getElementById('proposalListView').hidden = true;
    document.getElementById('studentProfileView').hidden = false;
    window.scrollTo(0, 0);

    if (pushHistory) {
        history.pushState({ profile: Number(id) }, '', '#profile-' + id);
    }
}

function closeProfileView()
{
    document.getElementById('studentProfileView').hidden = true;
    document.getElementById('proposalListView').hidden = false;
    window.scrollTo(0, listScrollY);
}

document.getElementById('spBack').addEventListener('click', function () {
    if (history.state && history.state.profile) {
        history.back();            // popstate below closes the profile
    } else {
        closeProfileView();
        history.replaceState(null, '', location.pathname + location.search);
    }
});

document.addEventListener('DOMContentLoaded', function () {
    // opened from a notification: proposal.php?view=2 -> show that proposal
    const viewId = new URLSearchParams(location.search).get('view');
    if (viewId) {
        history.replaceState(null, '', location.pathname);
        showFullProposal(Number(viewId));
    }

    const m = location.hash.match(/^#profile-(\d+)$/);
    if (m) {
        history.replaceState({ profile: Number(m[1]) }, '', location.hash);
        showProfile(Number(m[1]), false);
    }
});

window.addEventListener('popstate', function (e) {
    if (e.state && e.state.profile) {
        showProfile(e.state.profile, false);
    } else {
        closeProfileView();
    }
});


/* ================================================================
   CONFIRM ACTION
================================================================ */

function askConfirmation(
    id,
    action
)
{

    const proposal =
        proposalData.find(
            p => Number(p.id) === Number(id)
        );


    if (!proposal) {
        return;
    }


    pendingAction = {
        id: Number(id),
        action: action
    };


    const confirmIcon =
        document.getElementById(
            'confirmIcon'
        );

    const confirmTitle =
        document.getElementById(
            'confirmTitle'
        );

    const confirmMessage =
        document.getElementById(
            'confirmMessage'
        );

    const confirmButton =
        document.getElementById(
            'confirmActionButton'
        );


    if (action === 'accept') {

        confirmIcon.textContent =
            'check_circle';

        confirmTitle.textContent =
            'Accept Proposal';

        confirmMessage.textContent =
            `Are you sure you want to accept ${proposal.name}'s proposal?`;

        confirmButton.textContent =
            'Accept';

        confirmButton.classList.remove(
            'danger'
        );

    }


    if (action === 'reject') {

        confirmIcon.textContent =
            'cancel';

        confirmTitle.textContent =
            'Reject Proposal';

        confirmMessage.textContent =
            `Are you sure you want to reject ${proposal.name}'s proposal?`;

        confirmButton.textContent =
            'Reject';

        confirmButton.classList.add(
            'danger'
        );

    }


    openModal('confirmModal');

}


/* ================================================================
   PERFORM ACTION
================================================================ */

function performAction()
{

    if (!pendingAction) {
        return;
    }


    const id =
        pendingAction.id;

    const action =
        pendingAction.action;


    let newStatus;


    if (action === 'accept') {
        newStatus = 'accepted';
    }

    if (action === 'reject') {
        newStatus = 'rejected';
    }


    const card =
        document.querySelector(
            `.pr-card[data-id="${id}"]`
        );


    // Close the confirmation straight away
    pendingAction = null;

    closeModal('confirmModal');


    if (card && newStatus === 'accepted') {
        saveAcceptOnServer(id, false).then(function (data) {
            updateCardStatus(card, 'accepted', !data.message);
            if (data.message) showToast(data.message, 'Proposal accepted');
        }).catch(function (err) {
            showToast(err.message || 'Please try again.', 'Could not accept the proposal', 'error');
        });
    } else if (card && newStatus) {

        updateCardStatus(
            card,
            newStatus,
            true
        );

    }


    pendingAction = null;

    closeModal('confirmModal');

}


/* ================================================================
   PORTFOLIO PREVIEW
   PDF   -> shown with the browser's own PDF viewer (iframe)
   image -> shown as an image
   no file but profile projects -> open the student's profile
================================================================ */
document.addEventListener('click', function (e) {
    const openBtn = e.target.closest('[data-portfolio-open]');
    if (openBtn) {
        const p = proposalData.find(x => Number(x.id) === Number(openBtn.dataset.portfolioOpen));
        if (!p || !p.portfolio) return;

        const body = document.getElementById('pvBody');
        body.innerHTML = '';
        if (p.portfolio_type === 'pdf') {
            const frame = document.createElement('iframe');
            frame.src = p.portfolio;
            frame.title = 'Portfolio preview';
            body.appendChild(frame);
        } else {
            const img = document.createElement('img');
            img.src = p.portfolio;
            img.alt = 'Portfolio preview';
            body.appendChild(img);
        }
        document.getElementById('pvName').textContent    = p.portfolio_name || 'Portfolio';
        document.getElementById('pvStudent').textContent = (p.name || '') + ' · ' + (p.project || '');
        document.getElementById('pvDownload').href       = p.portfolio;
        document.getElementById('pvDownload').hidden     = false;
        document.getElementById('pvLink').hidden         = true;
        document.getElementById('pvBody').classList.remove('pv-project-mode');
        openModal('portfolioModal');
        return;
    }

    // a profile portfolio project -> details + screenshot + "View on GitHub" button
    const itemBtn = e.target.closest('[data-portfolio-item]');
    if (itemBtn) {
        const [pid, itemId] = itemBtn.dataset.portfolioItem.split(':').map(Number);
        const p  = proposalData.find(x => Number(x.id) === pid);
        const it = p && (p.portfolio_items || []).find(x => Number(x.id) === itemId);
        if (!it) return;

        const body = document.getElementById('pvBody');
        body.innerHTML = '';
        body.classList.add('pv-project-mode');

        const card = document.createElement('div');
        card.className = 'pv-project';
        if (it.image) {
            const img = document.createElement('img');
            img.src = it.image;
            img.alt = it.title;
            card.appendChild(img);
        }
        const h = document.createElement('h3');
        h.textContent = it.title;
        card.appendChild(h);
        const d = document.createElement('p');
        d.textContent = it.description || 'The student did not add a description for this project.';
        card.appendChild(d);
        if (!it.link) {
            const n = document.createElement('p');
            n.className = 'pv-muted';
            n.textContent = 'No link was added for this project.';
            card.appendChild(n);
        }
        body.appendChild(card);

        document.getElementById('pvName').textContent    = it.title;
        document.getElementById('pvStudent').textContent = (p.name || '') + ' · portfolio project';
        document.getElementById('pvDownload').hidden     = true;
        const link = document.getElementById('pvLink');
        link.hidden = !it.link;
        link.href   = it.link || '#';
        document.getElementById('pvLinkText').textContent = it.link_label || 'Open project';
        openModal('portfolioModal');
        return;
    }

    const profileBtn = e.target.closest('[data-portfolio-profile]');
    if (profileBtn) {
        showProfile(Number(profileBtn.dataset.portfolioProfile));
    }
});

// stop the PDF from staying loaded after the preview is closed
document.querySelectorAll('[data-close-modal="portfolioModal"]').forEach(function (btn) {
    btn.addEventListener('click', function () { document.getElementById('pvBody').innerHTML = ''; });
});


/* Accept -> saved in the database so the student can be put in a team */
async function saveAcceptOnServer(id, silent, message)
{
    const body = new FormData();
    body.append('action', 'accept_proposal');
    body.append('proposal_id', id);
    if (silent) body.append('silent', '1');
    if (message) body.append('message', message);

    const res  = await fetch('proposal.php', { method: 'POST', body: body });
    const data = await res.json();
    if (!data.ok) throw new Error(data.message || 'Something went wrong.');
    return data;
}



/* ================================================================
   TOAST
================================================================ */

let toastTimer;

/*
| Same toast as Manage Projects ("Project updated"):
| top-right white card, coloured left border, round icon, bold title + message.
| (styles come from Assets/CSS/flash-toast.css, loaded on every Organization page)
|   showToast('Nimal has been notified.', 'Proposal accepted')          -> green
|   showToast('Please try again.', 'Could not accept', 'error')          -> red
*/
function showToast(message, title, type)
{
    type  = type === 'error' ? 'error' : 'success';
    title = title || (type === 'error' ? 'Something went wrong' : 'Done');

    // only one toast at a time
    document.querySelectorAll('.flash-toast.pr-flash').forEach(t => t.remove());
    clearTimeout(toastTimer);

    const toast = document.createElement('div');
    toast.className = 'flash-toast flash-' + type + ' pr-flash';
    toast.innerHTML =
        '<div class="flash-icon-wrap"><span class="material-symbols-outlined">' +
            (type === 'success' ? 'check_circle' : 'error') +
        '</span></div>' +
        '<div class="flash-content">' +
            '<div class="flash-title" style="font-weight:700; font-size:14px;"></div>' +
            '<div class="flash-msg" style="font-size:13px; margin-top:2px;"></div>' +
        '</div>';
    toast.querySelector('.flash-title').textContent = title;
    toast.querySelector('.flash-msg').textContent   = message || '';
    document.body.appendChild(toast);

    // hide after a few seconds (same timing and slide-out as Manage Projects)
    toastTimer = setTimeout(function () {
        toast.style.transition = 'opacity .4s ease, transform .4s ease';
        toast.style.opacity = '0';
        toast.style.transform = 'translateX(20px)';
        setTimeout(function () { toast.remove(); }, 400);
    }, 3500);
}


/* ================================================================
   INITIALS
================================================================ */

function getInitials(name)
{

    return name
        .trim()
        .split(/\s+/)
        .slice(0, 2)
        .map(
            part =>
                part.charAt(0).toUpperCase()
        )
        .join('');

}


/* ================================================================
   HTML ESCAPE
================================================================ */

function escapeHtml(value)
{

    return String(value)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');

}


/* ================================================================
   SEARCH EVENT
================================================================ */

searchInput.addEventListener(
    'input',
    applyFilters
);


/* ================================================================
   STATUS FILTER EVENT
================================================================ */

statusFilter.addEventListener(
    'change',
    applyFilters
);


/* ================================================================
   TIME FILTER EVENT
================================================================ */

timeFilter.addEventListener(
    'change',
    applyFilters
);


/* ================================================================
   SORT EVENT
================================================================ */

sortButton.addEventListener(
    'click',
    () => {

        newestFirst =
            !newestFirst;

        sortCards();

    }
);


/* ================================================================
   BUTTON EVENTS
================================================================ */

document.addEventListener(
    'click',
    function(event)
    {

        const fullProposalButton =
            event.target.closest(
                '.view-proposal-btn'
            );


        const profileButton =
            event.target.closest(
                '.view-profile-btn'
            );


        const acceptButton =
            event.target.closest(
                '.accept-btn'
            );


        const rejectButton =
            event.target.closest(
                '.reject-btn'
            );


        if (fullProposalButton) {

            showFullProposal(
                fullProposalButton.dataset.id
            );

            return;

        }


        if (profileButton) {

            showProfile(
                profileButton.dataset.id
            );

            return;

        }


        if (acceptButton) {

            if (!acceptButton.disabled) {

                openAcceptModal(
                    acceptButton.dataset.id
                );

            }

            return;

        }


        if (rejectButton) {

            if (!rejectButton.disabled) {

                openRejectModal(
                    rejectButton.dataset.id
                );

            }

            return;

        }

    }
);


/* ================================================================
   ACCEPT WITH MESSAGE  (same as Reject: the student gets a notification)
================================================================ */

let acceptingId = null;

const acMessage = document.getElementById('acMessage');
const acError   = document.getElementById('acError');
const acSubmit  = document.getElementById('acSubmit');

function openAcceptModal(id)
{
    const proposal = proposalData.find(p => Number(p.id) === Number(id));
    if (!proposal) {
        return;
    }

    acceptingId = Number(id);

    document.getElementById('acStudent').textContent = proposal.name;
    document.getElementById('acProject').textContent = proposal.project;

    acMessage.value = '';
    acMessage.classList.remove('invalid');
    acError.textContent = '';
    document.getElementById('acCount').textContent = '0';
    document.querySelectorAll('#acceptModal .ac-chip').forEach(c => c.classList.remove('picked'));

    acSubmit.disabled = false;

    openModal('acceptModal');
    setTimeout(() => acMessage.focus(), 50);
}

// Quick messages fill the textarea (can still be edited)
document.querySelectorAll('#acceptModal .ac-chip').forEach(function (chip) {
    chip.addEventListener('click', function () {
        document.querySelectorAll('#acceptModal .ac-chip').forEach(c => c.classList.remove('picked'));
        chip.classList.add('picked');
        acMessage.value = chip.textContent.trim();
        acMessage.dispatchEvent(new Event('input'));
        acMessage.focus();
    });
});

acMessage.addEventListener('input', function () {
    document.getElementById('acCount').textContent = acMessage.value.length;
    if (acMessage.value.trim().length >= 10) {
        acMessage.classList.remove('invalid');
        acError.textContent = '';
    }
});

acSubmit.addEventListener('click', async function () {
    const message = acMessage.value.trim();

    if (message.length < 10) {
        acMessage.classList.add('invalid');
        acError.textContent = 'Please write a message (at least 10 characters).';
        acMessage.focus();
        return;
    }

    const proposal = proposalData.find(p => Number(p.id) === acceptingId);
    const card = document.querySelector(`.pr-card[data-id="${acceptingId}"]`);

    acSubmit.disabled = true;
    const oldLabel = acSubmit.innerHTML;
    acSubmit.innerHTML = '<span class="material-symbols-outlined">hourglass_top</span> Accepting…';

    try {
        await saveAcceptOnServer(acceptingId, false, message);

        // Accepted straight away + popup closes
        closeModal('acceptModal');

        if (card) {
            updateCardStatus(card, 'accepted', false);
        }

        showToast(`${proposal ? proposal.name : 'The student'} has been notified.`, 'Proposal accepted');
        acceptingId = null;

    } catch (err) {
        acError.textContent = err.message || 'Could not accept the proposal. Please try again.';
    } finally {
        acSubmit.disabled = false;
        acSubmit.innerHTML = oldLabel;
    }
});

document.getElementById('acceptModal').addEventListener('click', function (e) {
    if (e.target === this) {
        closeModal('acceptModal');
    }
});


/* ================================================================
   REJECT WITH REASON
================================================================ */

let rejectingId = null;

const rjReason = document.getElementById('rjReason');
const rjError  = document.getElementById('rjError');
const rjSubmit = document.getElementById('rjSubmit');

function openRejectModal(id)
{
    const proposal = proposalData.find(p => Number(p.id) === Number(id));
    if (!proposal) {
        return;
    }

    rejectingId = Number(id);

    document.getElementById('rjStudent').textContent = proposal.name;
    document.getElementById('rjProject').textContent = proposal.project;

    rjReason.value = '';
    rjReason.classList.remove('invalid');
    rjError.textContent = '';
    document.getElementById('rjCount').textContent = '0';
    document.querySelectorAll('#rejectModal .rj-chip').forEach(c => c.classList.remove('picked'));

    rjSubmit.disabled = false;

    openModal('rejectModal');
    setTimeout(() => rjReason.focus(), 50);
}

// Quick reasons fill the textarea (can still be edited)
document.querySelectorAll('#rejectModal .rj-chip').forEach(function (chip) {
    chip.addEventListener('click', function () {
        document.querySelectorAll('#rejectModal .rj-chip').forEach(c => c.classList.remove('picked'));
        chip.classList.add('picked');
        rjReason.value = chip.textContent.trim();
        rjReason.dispatchEvent(new Event('input'));
        rjReason.focus();
    });
});

rjReason.addEventListener('input', function () {
    document.getElementById('rjCount').textContent = rjReason.value.length;
    if (rjReason.value.trim().length >= 10) {
        rjReason.classList.remove('invalid');
        rjError.textContent = '';
    }
});

rjSubmit.addEventListener('click', async function () {
    const reason = rjReason.value.trim();

    if (reason.length < 10) {
        rjReason.classList.add('invalid');
        rjError.textContent = 'Please write a reason (at least 10 characters).';
        rjReason.focus();
        return;
    }

    const proposal = proposalData.find(p => Number(p.id) === rejectingId);
    const card = document.querySelector(`.pr-card[data-id="${rejectingId}"]`);

    rjSubmit.disabled = true;
    const oldLabel = rjSubmit.innerHTML;
    rjSubmit.innerHTML = '<span class="material-symbols-outlined">hourglass_top</span> Rejecting…';

    try {
        const body = new FormData();
        body.append('action', 'reject_proposal');
        body.append('proposal_id', rejectingId);
        body.append('reason', reason);

        const res  = await fetch('proposal.php', { method: 'POST', body: body });
        const data = await res.json();

        if (!data.ok) {
            throw new Error(data.message || 'Something went wrong.');
        }

        // Rejected straight away + popup closes
        closeModal('rejectModal');

        if (card) {
            updateCardStatus(card, 'rejected', false);
        }

        showToast(`${proposal ? proposal.name : 'The student'} has been notified with your reason.`, 'Proposal rejected');
        rejectingId = null;

    } catch (err) {
        rjError.textContent = err.message || 'Could not reject the proposal. Please try again.';
    } finally {
        rjSubmit.disabled = false;
        rjSubmit.innerHTML = oldLabel;
    }
});

document.getElementById('rejectModal').addEventListener('click', function (e) {
    if (e.target === this) {
        closeModal('rejectModal');
    }
});


/* ================================================================
   CONFIRM BUTTON
================================================================ */

document
    .getElementById('confirmActionButton')
    .addEventListener(
        'click',
        performAction
    );


/* ================================================================
   CLOSE MODAL BUTTONS
================================================================ */

document.addEventListener(
    'click',
    function(event)
    {

        const closeButton =
            event.target.closest(
                '[data-close-modal]'
            );


        if (!closeButton) {
            return;
        }


        closeModal(
            closeButton.dataset.closeModal
        );

    }
);


/* ================================================================
   CLOSE WHEN CLICKING OUTSIDE MODAL
================================================================ */

document
    .querySelectorAll('.pr-modal-overlay')
    .forEach(overlay => {

        overlay.addEventListener(
            'click',
            function(event)
            {

                if (event.target === overlay) {

                    closeModal(
                        overlay.id
                    );

                }

            }
        );

    });


/* ================================================================
   ESCAPE KEY
================================================================ */

document.addEventListener(
    'keydown',
    function(event)
    {

        if (event.key === 'Escape') {

            closeAllModals();

        }

    }
);


/* ================================================================
   INITIAL LOAD
================================================================ */

applySavedStatuses();

sortCards();

applyFilters();