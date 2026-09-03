document.addEventListener('DOMContentLoaded', function () {
    const page = document.querySelector('.company-profile-page');
    const form = document.getElementById('companyProfileForm');
    const editButton = document.getElementById('companyProfileEdit');
    const saveButton = document.querySelector('.profile-save-button');

    if (!page || !form || !editButton || !saveButton) return;

    const editableFields = document.querySelectorAll('[data-company-editable]');

    editButton.addEventListener('click', function () {
        const editing = !page.classList.contains('is-editing');
        page.classList.toggle('is-editing', editing);
        editableFields.forEach(function (field) { field.readOnly = !editing; });
        saveButton.disabled = !editing;
        editButton.textContent = editing ? 'Cancel Edit' : 'Edit Profile';

        if (!editing) {
            form.reset();
        }
    });
});
