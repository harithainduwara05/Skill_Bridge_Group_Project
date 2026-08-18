document.addEventListener('DOMContentLoaded', () => {
    const btnEditProfile = document.getElementById('btnEditProfile');
    const btnSaveProfile = document.getElementById('btnSaveProfile');
    const profileForm = document.getElementById('profileForm');
    const formInputs = profileForm.querySelectorAll('input:not([type="file"]), textarea');
    const logoUpload = document.getElementById('logoUpload');
    const uploadBtnLabel = document.getElementById('uploadBtnLabel');
    const logoPreview = document.getElementById('logoPreview');

    let isEditing = false;

    // Toggle Edit Mode
    btnEditProfile.addEventListener('click', () => {
        isEditing = !isEditing;

        if (isEditing) {
            // Enable Form Fields
            formInputs.forEach(input => {
                input.removeAttribute('readonly');
                input.classList.add('editable');
            });

            // Enable Buttons & Upload Link
            btnSaveProfile.removeAttribute('disabled');
            logoUpload.removeAttribute('disabled');
            uploadBtnLabel.classList.remove('disabled-link');
            btnEditProfile.textContent = 'Cancel Edit';
            btnEditProfile.style.backgroundColor = '#f1f5f9';
        } else {
            // Disable Form Fields & Reset
            formInputs.forEach(input => {
                input.setAttribute('readonly', 'true');
                input.classList.remove('editable');
            });

            btnSaveProfile.setAttribute('disabled', 'true');
            logoUpload.setAttribute('disabled', 'true');
            uploadBtnLabel.classList.add('disabled-link');
            btnEditProfile.textContent = 'Edit Profile';
            btnEditProfile.style.backgroundColor = '#ffffff';
        }
    });

    // Image Upload Preview Logic
    logoUpload.addEventListener('change', (e) => {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function (event) {
                logoPreview.src = event.target.result;
            };
            reader.readAsDataURL(file);
        }
    });

    // Form Submission Handling
    profileForm.addEventListener('submit', (e) => {
        if (!isEditing) {
            e.preventDefault();
            return;
        }

        // Optional confirmation or feedback
        btnSaveProfile.textContent = 'Saving...';
    });
});