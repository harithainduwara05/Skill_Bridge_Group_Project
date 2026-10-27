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
        else if (title.length < 5 || title.length > 100) errors.title = 'Title must be 5 to 100 characters.';

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
        } else if (desc.length < 50) {
            errors.description = 'Description must have at least 50 characters (now ' + desc.length + ').';
        } else if (desc.length > 3000) {
            errors.description = 'Description can have up to 3000 characters.';
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