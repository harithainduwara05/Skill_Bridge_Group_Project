/*
| Post Project / Edit Project - checks the form in the browser before it is sent.
| The same rules are checked again on the server (Backend/Organization/project_validation.php).
|
|   Save Draft -> only the Title is required
|   Publish    -> every field marked * is required
|
| Error messages are written into the <div class="field-error" data-for="..."> under each field.
*/
document.addEventListener('DOMContentLoaded', function () {

    const form = document.getElementById('postProjectForm') || document.getElementById('editProjectForm');
    if (!form) return;

    // value of a field by its name ('' if the field is not on the page)
    function val(name) {
        const el = form.querySelector('[name="' + name + '"]');
        return el ? el.value.trim() : '';
    }

    // 'YYYY-MM-DD' of a date (local time), to compare with the date input value
    function ymd(date) {
        const m = String(date.getMonth() + 1).padStart(2, '0');
        const d = String(date.getDate()).padStart(2, '0');
        return date.getFullYear() + '-' + m + '-' + d;
    }

    function isWholeNumber(text) {
        return /^\d+$/.test(text);
    }

    // Draft = "Save Draft" button on Post Project, or status "Draft" on Edit Project
    function isDraft(submitter) {
        if (submitter && submitter.value === 'draft') return true;
        const status = form.querySelector('[name="status"]');
        return !!status && status.value === 'draft';
    }

    function validate(draft) {
        const errors = {};

        // ---- Title (required also for a draft) ----
        const title = val('title');
        if (title === '') errors.title = 'Project title is required.';
        else if (title.length < 3 || title.length > 80) errors.title = 'Title must be 3 to 80 characters.';
        else if (!/\p{L}/u.test(title)) errors.title = 'Title must have letters, not only numbers or symbols.';

        // ---- Category ----
        if (val('category') === '' && !draft) errors.category = 'Please select a category.';

        // ---- Required Skills ----
        const skills = val('keywords').split(',').map(s => s.trim()).filter(Boolean);
        if (skills.length === 0) {
            if (!draft) errors.keywords = 'Add at least one required skill.';
        } else if (skills.length > 10) {
            errors.keywords = 'You can add up to 10 skills.';
        } else if (skills.some(s => s.length > 30)) {
            errors.keywords = 'Each skill can have up to 30 characters.';
        } else if (skills.join(', ').length > 255) {
            errors.keywords = 'The skills list is too long. Remove a few skills.';
        }

        // ---- Description ----
        const desc = val('description');
        if (desc === '') {
            if (!draft) errors.description = 'Project description is required.';
        } else if (desc.length < 3) {
            errors.description = 'Description must have at least 3 characters.';
        } else if (desc.length > 3000) {
            errors.description = 'Description can have up to 3000 characters.';
        } else if (!/\p{L}/u.test(desc)) {
            errors.description = 'Description must have words, not only numbers or symbols.';
        }

        // ---- Optional long texts ----
        if (val('learning_objectives').length > 1500) errors.learning_objectives = 'Learning objectives can have up to 1500 characters.';
        if (val('expected_outcomes').length > 1500) errors.expected_outcomes = 'Expected outcomes can have up to 1500 characters.';

        // ---- Duration: 1 - 52 weeks ----
        const weeks = val('duration_weeks');
        if (weeks === '') {
            if (!draft) errors.duration_weeks = 'Project duration is required.';
        } else if (!isWholeNumber(weeks) || +weeks < 1 || +weeks > 52) {
            errors.duration_weeks = 'Enter a whole number of weeks from 1 to 52.';
        }

        // ---- Students Required: 1 - 10 (Edit: not less than the current team) ----
        const studentsInput = form.querySelector('[name="students_required"]');
        const minStudents = Math.max(1, parseInt(studentsInput && studentsInput.dataset.min, 10) || 1);
        const students = val('students_required');
        if (students === '') {
            if (!draft) errors.students_required = 'Number of students is required.';
        } else if (!isWholeNumber(students) || +students < 1 || +students > 10) {
            errors.students_required = 'Enter a whole number from 1 to 10.';
        } else if (+students < minStudents) {
            errors.students_required = minStudents + " students are already in the team, so it can't be less than " + minStudents + '.';
        }

        // ---- Application Deadline: after today, within 1 year ----
        const deadlineInput = form.querySelector('[name="deadline"]');
        const deadline = val('deadline');
        if (deadline === '') {
            if (!draft) errors.deadline = 'Application deadline is required.';
        } else if (!/^\d{4}-\d{2}-\d{2}$/.test(deadline)) {
            errors.deadline = 'Please choose a valid date.';
        } else if (!deadlineInput.dataset.original || deadline !== deadlineInput.dataset.original) {
            // (Edit Project: a deadline that was not changed is not checked again)
            const today = new Date();
            const nextYear = new Date();
            nextYear.setFullYear(today.getFullYear() + 1);
            if (deadline <= ymd(today)) errors.deadline = 'The deadline must be after today.';
            else if (deadline > ymd(nextYear)) errors.deadline = 'The deadline can be at most 1 year from today.';
        }

        return errors;
    }

    // write each message under its field (and clear the fields that are OK now)
    function showErrors(errors) {
        form.querySelectorAll('.field-error').forEach(function (box) {
            box.textContent = errors[box.dataset.for] || '';
        });
    }

    let checkedAsDraft = null;   // after the first failed try, fields are re-checked while typing

    // the server already found errors (page came back) -> also re-check while typing
    if (form.querySelector('.field-error:not(:empty)')) {
        checkedAsDraft = form.dataset.draft === '1' || isDraft(null);
    }

    form.addEventListener('submit', function (e) {
        const draft = isDraft(e.submitter);
        const errors = validate(draft);
        showErrors(errors);

        if (Object.keys(errors).length > 0) {
            e.preventDefault();
            checkedAsDraft = draft;
            // scroll to the first field with a problem
            const first = form.querySelector('.field-error:not(:empty)');
            if (first) first.closest('.form-group').scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    });

    // live re-check (typing, choosing, adding / removing a skill chip)
    ['input', 'change', 'click', 'keyup'].forEach(function (evt) {
        form.addEventListener(evt, function () {
            if (checkedAsDraft !== null) showErrors(validate(checkedAsDraft));
        });
    });
});
/*
| "+ Add a new category" (Post Project / Edit Project)
| Opens a small popup, the name is saved in project_categories by the same page
| (Backend/Organization/org_db.php -> orgHandleAddCategory) and then chosen in the dropdown.
| Same rules as the server: 2-40 characters, letters needed, no odd symbols.
*/
document.addEventListener('DOMContentLoaded', function () {
    const btn    = document.getElementById('addCategoryBtn');
    const select = document.querySelector('select[name="category"]');
    const done   = document.getElementById('addCategoryDone');
    if (!btn || !select) return;

    const modal = document.createElement('div');
    modal.className = 'add-cat-modal';
    modal.innerHTML =
        '<div class="add-cat-box" role="dialog" aria-modal="true" aria-labelledby="addCatTitle">' +
            '<h3 id="addCatTitle">Add a new category</h3>' +
            '<p class="add-cat-hint">Can\'t find a category that fits your project? Add it here. ' +
            'It will be in the category list from now on.</p>' +
            '<input type="text" class="form-input" id="addCatName" maxlength="40" placeholder="e.g. Game Development" autocomplete="off">' +
            '<div class="add-cat-row"><small class="add-cat-err" id="addCatErr"></small><small class="add-cat-count" id="addCatCount">0 / 40</small></div>' +
            '<div class="add-cat-actions">' +
                '<button type="button" class="add-cat-cancel">Cancel</button>' +
                '<button type="button" class="add-cat-save">Add Category</button>' +
            '</div>' +
        '</div>';
    document.body.appendChild(modal);

    const input = modal.querySelector('#addCatName');
    const err   = modal.querySelector('#addCatErr');
    const count = modal.querySelector('#addCatCount');
    const save  = modal.querySelector('.add-cat-save');

    function open() {
        input.value = ''; err.textContent = ''; count.textContent = '0 / 40';
        input.classList.remove('invalid');
        modal.classList.add('open');
        setTimeout(function () { input.focus(); }, 50);
    }
    function close() { modal.classList.remove('open'); btn.focus(); }

    function check(name) {
        if (name.length < 2)  return 'Please enter at least 2 characters.';
        if (name.length > 40) return 'Please keep it to 40 characters or less.';
        if (!/\p{L}/u.test(name)) return 'The category name must contain letters.';
        if (!/^[\p{L}\p{N}][\p{L}\p{N} &\/+.,()\-]*$/u.test(name)) return 'Use only letters, numbers, spaces and & / + . , ( ) -';
        return '';
    }

    // put the new name in the dropdown A-Z, with "Other" kept last
    function addOption(name) {
        const opts = Array.from(select.options).filter(o => o.value !== '');
        const exists = opts.find(o => o.value.toLowerCase() === name.toLowerCase());
        if (exists) return exists;
        const opt = new Option(name, name);
        const before = opts.find(o => o.value === 'Other' || o.value.localeCompare(name, undefined, { sensitivity: 'base' }) > 0);
        select.insertBefore(opt, before || null);
        return opt;
    }

    function submit() {
        const name = input.value.replace(/\s+/g, ' ').trim();
        const problem = check(name);
        if (problem) { err.textContent = problem; input.classList.add('invalid'); return; }

        save.disabled = true;
        const fd = new FormData();
        fd.append('action', 'add_category');
        fd.append('name', name);
        fetch(location.href, { method: 'POST', body: fd })
            .then(r => r.json())
            .then(function (res) {
                save.disabled = false;
                if (!res.ok) { err.textContent = res.message; input.classList.add('invalid'); return; }
                const opt = addOption(res.name);
                select.value = opt.value;
                select.dispatchEvent(new Event('change', { bubbles: true }));
                if (done) done.textContent = res.exists
                    ? '"' + res.name + '" is already in the list, so it is selected.'
                    : '"' + res.name + '" was added and selected.';
                close();
            })
            .catch(function () {
                save.disabled = false;
                err.textContent = 'Something went wrong. Please try again.';
            });
    }

    btn.addEventListener('click', open);
    save.addEventListener('click', submit);
    modal.querySelector('.add-cat-cancel').addEventListener('click', close);
    modal.addEventListener('click', function (e) { if (e.target === modal) close(); });
    input.addEventListener('input', function () {
        count.textContent = input.value.length + ' / 40';
        err.textContent = ''; input.classList.remove('invalid');
    });
    input.addEventListener('keydown', function (e) {
        if (e.key === 'Enter')  { e.preventDefault(); submit(); }
        if (e.key === 'Escape') { e.preventDefault(); close(); }
    });
    select.addEventListener('change', function () { if (done && !select.value) done.textContent = ''; });
});