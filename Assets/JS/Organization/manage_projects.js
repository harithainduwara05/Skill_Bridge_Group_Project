/*
| Organization - manage_projects page script
| Used by: Functions/Organization/manage_projects.php
| Values from PHP / the database come from ORG_DATA (set in a small <script> on the page).
*/

const PROJECT_VIEW_DATA = ORG_DATA.PROJECT_VIEW_DATA;

function pvEl(tag, cls, text) {
    const el = document.createElement(tag);
    if (cls) el.className = cls;
    if (text !== undefined) el.textContent = text;
    return el;
}

function openProjectView(id) {
    const d = PROJECT_VIEW_DATA[id];
    if (!d) return;

    document.getElementById('pvTitle').textContent = d.title;
    document.getElementById('pvCategory').textContent = d.category;
    const STATUS_LABELS = { reviewing: 'Reviewing', published: 'Published', inprogress: 'Active', hold: 'Hold', completed: 'Completed', draft: 'Draft' };
    const st = document.getElementById('pvStatus');
    st.className = 'badge-status ' + d.status;
    st.textContent = d.status ? (STATUS_LABELS[d.status] || (d.status.charAt(0).toUpperCase() + d.status.slice(1))) : '';

    // key facts (empty ones are skipped)
    const info = document.getElementById('pvInfo');
    info.innerHTML = '';
    [
        ['Posted', d.posted],
        ['Deadline', d.deadline],
        ['Applicants', d.applicants],
        ['Assigned Team', d.assigned > 0 ? ('Assigned (' + d.assigned + '/' + d.required + ')') : 'Not Assigned'],
        ['Visibility', d.visibility],
        ['Duration', d.duration],
        ['Students Required', d.members],
        ['Preferred Year', d.year],
        ['Difficulty', d.difficulty]
    ].forEach(function (row) {
        if (row[1] === '' || row[1] === null || row[1] === undefined) return;
        const box = pvEl('div');
        box.appendChild(pvEl('div', 'pv-info-label', row[0]));
        box.appendChild(pvEl('div', 'pv-info-value', String(row[1])));
        info.appendChild(box);
    });

    // text sections (empty ones are skipped)
    const sections = document.getElementById('pvSections');
    sections.innerHTML = '';
    [['Description', d.description], ['Learning Objectives', d.objectives], ['Expected Outcomes', d.outcomes]].forEach(function (sec) {
        if (!sec[1]) return;
        const box = pvEl('div', 'pv-section');
        box.appendChild(pvEl('h4', '', sec[0]));
        box.appendChild(pvEl('p', '', sec[1]));
        sections.appendChild(box);
    });
    if (d.keywords) {
        const box = pvEl('div', 'pv-section');
        box.appendChild(pvEl('h4', '', 'Skills / Keywords'));
        const chips = pvEl('div', 'pv-chips');
        d.keywords.split(',').forEach(function (k) {
            k = k.trim();
            if (k) chips.appendChild(pvEl('span', 'pv-chip', k));
        });
        box.appendChild(chips);
        sections.appendChild(box);
    }

    const pvEdit = document.getElementById('pvEdit');
    pvEdit.href = 'edit_project.php?id=' + encodeURIComponent(d.id);
    pvEdit.style.display = (d.status === 'hold' || d.status === 'rejected') ? 'none' : '';
    document.getElementById('projectViewModal').classList.add('open');
}

function closeProjectView() {
    document.getElementById('projectViewModal').classList.remove('open');
}

document.getElementById('projectViewModal').addEventListener('click', function (e) {
    if (e.target === this) closeProjectView();
});
document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') closeProjectView();
});

let pendingDeleteId = null;

function openDeleteModal(id, title, blockReason) {
    // Not allowed -> show the warning instead of the delete confirmation
    if (blockReason) {
        showBlocked(
            'You can’t delete this project', title, blockReason,
            'Only <b>Rejected</b> projects, or <b>Reviewing</b> projects with <b>0 applicants</b>, can be deleted.'
        );
        return;
    }
    pendingDeleteId = id;
    document.getElementById('deleteProjectTitle').textContent = title;
    document.getElementById('deleteConfirmModal').classList.add('open');
}

function closeDeleteModal() {
    pendingDeleteId = null;
    document.getElementById('deleteConfirmModal').classList.remove('open');
}

function confirmDelete() {
    if (pendingDeleteId !== null) {
        window.location.href = 'manage_projects.php?delete=' + pendingDeleteId;
    }
}

document.getElementById('deleteConfirmModal').addEventListener('click', function (e) {
    if (e.target === this) closeDeleteModal();
});

// Shared warning popup (used for blocked Delete and blocked Edit)
function showBlocked(heading, title, reason, ruleHtml, holdId) {
    document.getElementById('blockedHeading').textContent = heading;
    document.getElementById('blockedProjectTitle').textContent = title;
    document.getElementById('blockedReason').textContent = reason;
    document.getElementById('blockedRule').innerHTML = ruleHtml;

    const holdBtn = document.getElementById('blockedHoldBtn');
    holdBtn.hidden = !holdId;
    holdBtn.onclick = holdId ? function () { closeDeleteBlocked(); openHoldModal(holdId); } : null;

    document.getElementById('deleteBlockedModal').classList.add('open');
}

function openEditBlocked(title, status, holdId) {
    if (status === 'hold') {
        showBlocked(
            'You can’t edit this project', title,
            'This project is On Hold by the Admin, so it can’t be edited from here.',
            'Use the <b>hold icon</b> in the Actions column to see the Admin’s reason, fix the project and send it back.',
            holdId
        );
    } else {
        showBlocked(
            'You can’t edit this project', title,
            'This project was Rejected by the Admin, so it can’t be edited.',
            'Rejected projects can’t be changed. You can delete it and post a new project instead.'
        );
    }
}

function closeDeleteBlocked() {
    document.getElementById('deleteBlockedModal').classList.remove('open');
}
document.getElementById('deleteBlockedModal').addEventListener('click', function (e) {
    if (e.target === this) closeDeleteBlocked();
});

/* ===================== ON HOLD MODAL ===================== */
const HOLD_DATA = ORG_DATA.HOLD_DATA;
const holdModal = document.getElementById('holdModal');
const holdForm  = holdModal.querySelector('form');
let holdCurrentStep = 1;
// form.elements[...] is used because inputs named "title"/"action" clash with form properties
const hf = function (name) { return holdForm.elements.namedItem(name); };

// ---- "Nothing was changed" check ----
const HOLD_COMPARE = ['title', 'category', 'keywords', 'description', 'learning_objectives',
                      'expected_outcomes', 'deadline', 'members', 'duration_weeks'];
let holdOriginal = '';
let holdSendAnyway = false;      // true after the warning was shown once
function holdSnapshot() {
    return HOLD_COMPARE.map(function (n) { return String(hf(n).value).trim(); }).join('|');
}
// Hide the "nothing changed" warning (when the modal opens or the user goes Back)
function holdResetNoChange() {
    holdSendAnyway = false;
    document.getElementById('hmNoChange').hidden = true;
    document.getElementById('hmSubmitText').textContent = 'Send Activation Request';
}
holdForm.addEventListener('submit', function (e) {
    hmAddSkill(hmChipInput.value);   // keep a half-typed skill

    // Check the project details (step 2) and the message (step 3) before sending
    const errors = holdValidate();
    holdShowErrors(errors);
    const step2Bad = HOLD_STEP2_FIELDS.some(function (f) { return errors[f]; });
    if (step2Bad || errors.hold_response) {
        e.preventDefault();
        holdChecked = true;
        if (step2Bad) showHoldStep(2);
        holdFocusFirstError();
        return;
    }

    // First click with nothing changed -> show the warning inside the form.
    // Second click ("Send Anyway") -> send it.
    if (holdSnapshot() === holdOriginal && !holdSendAnyway) {
        e.preventDefault();
        holdSendAnyway = true;
        document.getElementById('hmNoChange').hidden = false;
        document.getElementById('hmSubmitText').textContent = 'Send Anyway';
    }
});

/* ---- Checks for the hold popup (same rules as Post Project -> Publish;
        the server checks them again in Backend/Organization/manage_projects.php) ---- */
const HOLD_STEP2_FIELDS = ['title', 'category', 'keywords', 'description', 'learning_objectives',
                           'expected_outcomes', 'deadline', 'members', 'duration_weeks'];
let holdChecked = false;     // after the first failed check, errors update while typing
let holdInTeam = 0;          // students already in the team of the open project

function holdValidate() {
    const v = function (name) { return String(hf(name).value).trim(); };
    const e = {};
    const today = new Date();
    const ymd = function (d) {
        return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
    };
    const nextYear = new Date(); nextYear.setFullYear(today.getFullYear() + 1);

    const title = v('title');
    if (title === '') e.title = 'Project title is required.';
    else if (title.length < 3 || title.length > 80) e.title = 'Title must be 3 to 80 characters.';
    else if (!/\p{L}/u.test(title)) e.title = 'Title must have letters, not only numbers or symbols.';

    if (v('category') === '') e.category = 'Please select a category.';

    if (hmSkills.length === 0) e.keywords = 'Add at least one required skill.';
    else if (hmSkills.length > 10) e.keywords = 'You can add up to 10 skills.';
    else if (hmSkills.some(function (s) { return s.length > 30; })) e.keywords = 'Each skill can have up to 30 characters.';

    const desc = v('description');
    if (desc === '') e.description = 'Project description is required.';
    else if (desc.length < 3) e.description = 'Description must have at least 3 characters.';
    else if (desc.length > 3000) e.description = 'Description can have up to 3000 characters.';
    else if (!/\p{L}/u.test(desc)) e.description = 'Description must have words, not only numbers or symbols.';

    if (v('learning_objectives').length > 1500) e.learning_objectives = 'Learning objectives can have up to 1500 characters.';
    if (v('expected_outcomes').length > 1500) e.expected_outcomes = 'Expected outcomes can have up to 1500 characters.';

    const dl = v('deadline');
    if (dl === '') e.deadline = 'Application deadline is required.';
    else if (dl <= ymd(today)) e.deadline = 'The deadline must be after today.';
    else if (dl > ymd(nextYear)) e.deadline = 'The deadline can be at most 1 year from today.';

    const m = v('members'), minM = Math.max(1, holdInTeam);
    if (m === '') e.members = 'Number of students is required.';
    else if (!/^\d+$/.test(m) || +m < 1 || +m > 10) e.members = 'Enter a whole number from 1 to 10.';
    else if (+m < minM) e.members = minM + " students are already in the team, so it can't be less than " + minM + '.';

    const w = v('duration_weeks');
    if (w === '') e.duration_weeks = 'Project duration is required.';
    else if (!/^\d+$/.test(w) || +w < 1 || +w > 52) e.duration_weeks = 'Enter a whole number of weeks from 1 to 52.';

    const msg = v('hold_response');
    if (msg === '') e.hold_response = 'Please write a message to the Admin.';
    else if (msg.length < 10 || msg.length > 500) e.hold_response = 'The message must be 10 to 500 characters (now ' + msg.length + ').';
    return e;
}

function holdShowErrors(errors) {
    holdForm.querySelectorAll('.field-error').forEach(function (box) {
        box.textContent = errors[box.dataset.for] || '';
    });
}

function holdFocusFirstError() {
    const first = holdModal.querySelector('.hm-panel:not([hidden]) .field-error:not(:empty)');
    if (first) first.closest('.hm-field').scrollIntoView({ behavior: 'smooth', block: 'center' });
}

['input', 'change', 'click', 'keyup'].forEach(function (evt) {
    holdForm.addEventListener(evt, function () {
        if (!holdChecked) return;
        const errors = holdValidate();
        // the message error is shown only on step 3
        if (holdCurrentStep !== 3) delete errors.hold_response;
        holdShowErrors(errors);
    });
});

/* ---- Skills chips in the Fix step (same idea as Post Project) ---- */
let hmSkills = [];
const hmChipBox   = document.getElementById('hmChipBox');
const hmChipInput = document.getElementById('hmChipInput');

function hmRenderSkills() {
    hmChipBox.querySelectorAll('.chip').forEach(function (c) { c.remove(); });
    hmSkills.forEach(function (word, idx) {
        const chip = document.createElement('span');
        chip.className = 'chip';
        chip.appendChild(document.createTextNode(word + ' '));
        const x = document.createElement('button');
        x.type = 'button';
        x.className = 'chip-remove';
        x.innerHTML = '&times;';
        x.addEventListener('click', function (e) {
            e.stopPropagation();
            hmSkills.splice(idx, 1);
            hmRenderSkills();
        });
        chip.appendChild(x);
        hmChipBox.insertBefore(chip, hmChipInput);
    });
    document.getElementById('hmKeywords').value = hmSkills.join(', ');
    hmChipInput.placeholder = hmSkills.length ? 'Add another skill…' : 'Type a skill and press Enter (e.g. Laravel)';
}
function hmSetSkills(list) { hmSkills = list.slice(); hmRenderSkills(); }
function hmAddSkill(raw) {
    (raw || '').split(',').forEach(function (part) {
        const val = part.trim();
        const exists = hmSkills.some(function (s) { return s.toLowerCase() === val.toLowerCase(); });
        if (val && !exists) hmSkills.push(val);
    });
    hmChipInput.value = '';
    hmRenderSkills();
}

hmChipInput.addEventListener('keydown', function (e) {
    if (e.key === 'Enter' || e.key === ',') {
        e.preventDefault();               // Enter must not submit the form
        hmAddSkill(hmChipInput.value);
    } else if (e.key === 'Backspace' && hmChipInput.value === '' && hmSkills.length) {
        hmSkills.pop();
        hmRenderSkills();
    }
});
hmChipInput.addEventListener('blur', function () { hmAddSkill(hmChipInput.value); });
hmChipBox.addEventListener('click', function () { hmChipInput.focus(); });

function openHoldModal(id) {
    const d = HOLD_DATA[id];
    if (!d) return;

    holdForm.reset();
    document.getElementById('hmProjectId').value = d.id;
    document.getElementById('hmTitle').textContent = d.title;
    document.getElementById('hmReason').textContent = d.reason || 'No reason was given by the Admin.';
    document.getElementById('hmHeldAt').textContent = d.held_at ? 'On ' + d.held_at : '';
    document.getElementById('hmPending').hidden = !d.requested_at;
    document.getElementById('hmPendingDate').textContent = '';

    // Step 2: the form opens with the CURRENT project details,
    // so the organization changes only the parts the Admin asked to fix.
    hf('title').value               = d.title;
    hf('description').value         = d.description;
    hf('learning_objectives').value = d.learning_objectives;
    hf('expected_outcomes').value   = d.expected_outcomes;
    hf('members').value             = d.members;
    holdInTeam = d.in_team || 0;
    hf('duration_weeks').value      = d.duration;
    hf('deadline').value            = d.deadline;
    hf('category').value            = d.category;   // stays on "Select category" if not in the list
    hf('hold_response').value       = '';
    hmSetSkills(d.keywords ? d.keywords.split(',').map(function (s) { return s.trim(); }).filter(Boolean) : []);

    // The deadline must be after today (the hold may have taken several days)
    const tomorrow = new Date(); tomorrow.setDate(tomorrow.getDate() + 1);
    hf('deadline').min = tomorrow.getFullYear() + '-' + String(tomorrow.getMonth() + 1).padStart(2, '0') + '-' + String(tomorrow.getDate()).padStart(2, '0');

    // no old error messages from a previous project
    holdChecked = false;
    holdShowErrors({});

    // Remember the original values to detect "nothing was changed" on submit
    holdOriginal = holdSnapshot();
    holdResetNoChange();

    // Short copy of the Admin's reason, so it can be read while typing
    document.getElementById('hmReasonMini').textContent = d.reason || '';
    document.getElementById('hmReasonToggle').open = false;

    showHoldStep(1);
    holdModal.classList.add('open');
}

function closeHoldModal() {
    holdModal.classList.remove('open');
}

function showHoldStep(step) {
    holdCurrentStep = step;
    holdModal.querySelectorAll('.hm-panel').forEach(function (p) {
        p.hidden = Number(p.dataset.panel) !== step;
    });
    holdModal.querySelectorAll('.hm-step').forEach(function (s) {
        const n = Number(s.dataset.step);
        s.classList.toggle('active', n === step);
        s.classList.toggle('done', n < step);
    });
    document.getElementById('hmBack').style.visibility = step === 1 ? 'hidden' : 'visible';
    document.getElementById('hmNext').hidden = step === 3;
    document.getElementById('hmSubmit').hidden = step !== 3;
    holdModal.querySelector('.hm-body').scrollTop = 0;
}

function holdStep(dir) {
    const next = holdCurrentStep + dir;
    if (dir < 0) holdResetNoChange();
    if (next < 1 || next > 3) return;

    // Don't leave step 2 while a project field is wrong (messages are shown under the fields)
    if (dir > 0 && holdCurrentStep === 2) {
        hmAddSkill(document.getElementById('hmChipInput').value);   // keep a half-typed skill
        const errors = holdValidate();
        delete errors.hold_response;              // the message is written in step 3
        holdShowErrors(errors);
        if (Object.keys(errors).length) {
            holdChecked = true;
            holdFocusFirstError();
            return;
        }
    }
    showHoldStep(next);
}

holdModal.addEventListener('click', function (e) {
    if (e.target === holdModal) closeHoldModal();
});
document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && holdModal.classList.contains('open')) closeHoldModal();
});

// Hide the success / error toast after a few seconds
(function () {
    const toast = document.getElementById('holdFlash');
    if (!toast) return;
    // remove ?updated / ?deleted ... from the address bar so a refresh doesn't show it again
    try {
        const url = new URL(location.href);
        ['updated', 'deleted', 'delete_blocked', 'hold_resolved', 'hold_error', 'posted', 'drafted', 'submitted', 'started_early', 'back_to_published'].forEach(k => url.searchParams.delete(k));
        history.replaceState(null, '', url.pathname + (url.search || '') + url.hash);
    } catch (e) {}
    setTimeout(function () {
        toast.style.transition = 'opacity .4s ease, transform .4s ease';
        toast.style.opacity = '0';
        toast.style.transform = 'translateX(20px)';
        setTimeout(function () { toast.remove(); }, 400);
    }, 3500);
})();

(function () {
    const listView = document.getElementById('projectListView');
    const gridView = document.getElementById('projectGridView');
    const buttons = document.querySelectorAll('.view-toggle-group button[data-view]');

    if (!listView || !gridView || !buttons.length) return;

    function setView(view, save = true) {
        const isGrid = view === 'grid';
        listView.hidden = isGrid;
        gridView.hidden = !isGrid;

        buttons.forEach(button => {
            const active = button.dataset.view === view;
            button.classList.toggle('active', active);
            button.setAttribute('aria-pressed', active ? 'true' : 'false');
        });

        if (save) {
            try { localStorage.setItem('skillbridge-project-view', view); } catch (e) {}
        }
    }

    buttons.forEach(button => {
        button.addEventListener('click', function () {
            setView(this.dataset.view);
        });
    });

    let savedView = 'list';
    try {
        savedView = localStorage.getItem('skillbridge-project-view') || 'list';
    } catch (e) {}
    setView(savedView, false);
})();