    function handlePhotoSelect(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function (e) {
                const preview = document.getElementById('avatarPreview');
                const initial = document.getElementById('avatarInitial');
                if (preview) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                }
                if (initial) {
                    initial.style.display = 'none';
                }
                // Auto submit form to upload photo immediately
                document.getElementById('photoUploadForm').submit();
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    // Toggle Password Visibility
    function togglePassVisibility(inputId, btn) {
        const input = document.getElementById(inputId);
        const icon = btn.querySelector('.material-symbols-outlined');
        if (input.type === 'password') {
            input.type = 'text';
            icon.innerText = 'visibility_off';
        } else {
            input.type = 'password';
            icon.innerText = 'visibility';
        }
    }

    // Password Match & Live Requirements Validation
    const newPass = document.getElementById('newPass');
    const confPass = document.getElementById('confPass');
    const passMsg = document.getElementById('passMatchMsg');
    const reqBox = document.getElementById('adminPassRequirements');

    function updateReqItem(elemId, isValid) {
        const item = document.getElementById(elemId);
        if (!item) return;
        const icon = item.querySelector('.req-icon');
        if (isValid) {
            item.classList.add('valid');
            if (icon) icon.innerText = '✓';
        } else {
            item.classList.remove('valid');
            if (icon) icon.innerText = '✕';
        }
    }

    function checkPasswordRequirements() {
        const val = newPass.value;
        updateReqItem('adminReqLength', val.length >= 8);
        updateReqItem('adminReqUpper', /[A-Z]/.test(val));
        updateReqItem('adminReqLower', /[a-z]/.test(val));
        updateReqItem('adminReqNum', /\d/.test(val));
    }

    function checkPassMatch() {
        if (!confPass.value) {
            passMsg.style.display = 'none';
            return;
        }
        passMsg.style.display = 'block';
        if (newPass.value === confPass.value) {
            passMsg.style.color = '#10b981';
            passMsg.innerText = '✓ Passwords match';
        } else {
            passMsg.style.color = '#ef4444';
            passMsg.innerText = '✗ Passwords do not match';
        }
    }

    // Smooth accordion slide down when New Password field is focused
    newPass.addEventListener('focus', function() {
        reqBox.classList.add('show');
        checkPasswordRequirements();
    });

    // Smooth accordion slide up when clicking away or blur
    newPass.addEventListener('blur', function() {
        reqBox.classList.remove('show');
    });

    // Live update while typing
    newPass.addEventListener('input', function() {
        checkPasswordRequirements();
        checkPassMatch();
    });
    confPass.addEventListener('input', checkPassMatch);