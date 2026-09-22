document.addEventListener('DOMContentLoaded', function () {
    // Password toggle functionality
    const togglePasswordBtn = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('password');

    if (togglePasswordBtn && passwordInput) {
        togglePasswordBtn.addEventListener('click', function () {
            // Toggle the type attribute
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);

            // Toggle the eye icon visual state
            const icon = this.querySelector('svg');
            if (type === 'text') {
                // Eye-off icon (password visible)
                icon.innerHTML = '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line>';
                icon.style.color = '#0056b3';
            } else {
                // Eye icon (password hidden)
                icon.innerHTML = '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle>';
                icon.style.color = '#94a3b8';
            }
        });
    }

    // Form submission animation/feedback (Frontend only)
    const loginForm = document.getElementById('loginForm');
    const submitBtn = loginForm ? loginForm.querySelector('.submit-btn') : null;

    if (loginForm && submitBtn) {
        loginForm.addEventListener('submit', function (e) {
            e.preventDefault(); // Prevent actual submission since it's frontend only

            // Basic validation
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;

            if (!email || !password) {
                // Shake effect on button for validation error
                submitBtn.style.animation = 'shake 0.5s';
                setTimeout(() => {
                    submitBtn.style.animation = '';
                }, 500);
                return;
            }

            // Simulate loading state
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<svg class="spinner" viewBox="0 0 50 50"><circle class="path" cx="25" cy="25" r="20" fill="none" stroke-width="5"></circle></svg> Authenticating...';
            submitBtn.style.opacity = '0.8';
            submitBtn.style.pointerEvents = 'none';

            // Simulate API delay
            setTimeout(() => {
                // Reset button
                submitBtn.innerHTML = originalText;
                submitBtn.style.opacity = '1';
                submitBtn.style.pointerEvents = 'all';

                // Submit the form to PHP backend
                loginForm.submit();
            }, 1500);
        });
    }
    
    // Forgot Password Multi-Step Modal Logic
    const forgotPasswordBtn = document.getElementById('forgotPasswordBtn');
    const fpModal = document.getElementById('forgotPasswordModal');
    const fpCloseBtn = document.getElementById('fpCloseBtn');
    const fpBackToLogin1 = document.getElementById('fpBackToLogin1');
    const fpBackToLoginSuccess = document.getElementById('fpBackToLoginSuccess');

    // Steps & Indicators
    const stepEmail = document.getElementById('fpStepEmail');
    const stepOtp = document.getElementById('fpStepOtp');
    const stepPassword = document.getElementById('fpStepPassword');
    const stepSuccess = document.getElementById('fpStepSuccess');

    const dot1 = document.getElementById('dotStep1');
    const dot2 = document.getElementById('dotStep2');
    const dot3 = document.getElementById('dotStep3');
    const line1 = document.getElementById('lineStep1');
    const line2 = document.getElementById('lineStep2');

    // Step 1 Elements
    const fpEmailInput = document.getElementById('fpEmail');
    const fpEmailError = document.getElementById('fpEmailError');
    const fpSendOtpBtn = document.getElementById('fpSendOtpBtn');

    // Step 2 Elements
    const fpDisplayEmail = document.getElementById('fpDisplayEmail');
    const fpChangeEmailBtn = document.getElementById('fpChangeEmailBtn');
    const fpOtpError = document.getElementById('fpOtpError');
    const fpVerifyOtpBtn = document.getElementById('fpVerifyOtpBtn');
    const fpResendBtn = document.getElementById('fpResendBtn');
    const fpResendTimer = document.getElementById('fpResendTimer');
    const otpBoxes = document.querySelectorAll('.otp-box');
    const finalOtpInput = document.getElementById('finalOtp');
    const fpOtpEmailInput = document.getElementById('fpOtpEmail');

    // Step 3 Elements
    const fpVerifiedEmailDisplay = document.getElementById('fpVerifiedEmailDisplay');
    const fpNewPassword = document.getElementById('fpNewPassword');
    const fpConfirmPassword = document.getElementById('fpConfirmPassword');
    const fpPasswordError = document.getElementById('fpPasswordError');
    const fpSubmitPasswordBtn = document.getElementById('fpSubmitPasswordBtn');
    const toggleFpNewPassword = document.getElementById('toggleFpNewPassword');
    const toggleFpConfirmPassword = document.getElementById('toggleFpConfirmPassword');

    let currentEnteredEmail = '';
    let resendCountdown = null;

    // Helper: Eye toggle helper
    function setupPasswordToggle(toggleBtn, inputField) {
        if (!toggleBtn || !inputField) return;
        toggleBtn.addEventListener('click', function () {
            const isPassword = inputField.getAttribute('type') === 'password';
            inputField.setAttribute('type', isPassword ? 'text' : 'password');
            const icon = this.querySelector('svg');
            if (isPassword) {
                icon.innerHTML = '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line>';
                icon.style.color = '#0056b3';
            } else {
                icon.innerHTML = '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle>';
                icon.style.color = '#94a3b8';
            }
        });
    }

    setupPasswordToggle(toggleFpNewPassword, fpNewPassword);
    setupPasswordToggle(toggleFpConfirmPassword, fpConfirmPassword);

    // Open Modal
    function openModal() {
        if (!fpModal) return;
        // Pre-fill email from login form if available
        const loginEmail = document.getElementById('email');
        if (loginEmail && loginEmail.value.trim() && !fpEmailInput.value) {
            fpEmailInput.value = loginEmail.value.trim();
        }
        fpModal.classList.add('show');
        fpModal.setAttribute('aria-hidden', 'false');
        goToStep(1);
        setTimeout(() => fpEmailInput && fpEmailInput.focus(), 200);
    }

    // Close Modal
    function closeModal() {
        if (!fpModal) return;
        fpModal.classList.remove('show');
        fpModal.setAttribute('aria-hidden', 'true');
        if (resendCountdown) {
            clearInterval(resendCountdown);
            resendCountdown = null;
        }
    }

    if (forgotPasswordBtn) {
        forgotPasswordBtn.addEventListener('click', function (e) {
            e.preventDefault();
            openModal();
        });
    }

    if (fpCloseBtn) fpCloseBtn.addEventListener('click', closeModal);
    if (fpBackToLogin1) fpBackToLogin1.addEventListener('click', closeModal);

    if (fpBackToLoginSuccess) {
        fpBackToLoginSuccess.addEventListener('click', function () {
            closeModal();
            const loginEmail = document.getElementById('email');
            if (loginEmail && currentEnteredEmail) {
                loginEmail.value = currentEnteredEmail;
                const loginPassword = document.getElementById('password');
                if (loginPassword) loginPassword.focus();
            }
        });
    }

    // Close on overlay backdrop click
    if (fpModal) {
        fpModal.addEventListener('click', function (e) {
            if (e.target === fpModal) {
                closeModal();
            }
        });
    }

    // Close on ESC key
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && fpModal && fpModal.classList.contains('show')) {
            closeModal();
        }
    });

    // Step Transition Handler
    function goToStep(stepNumber) {
        // Reset all views
        [stepEmail, stepOtp, stepPassword, stepSuccess].forEach(view => {
            if (view) view.classList.remove('active');
        });

        // Reset indicators
        [dot1, dot2, dot3].forEach(dot => {
            if (dot) dot.className = 'fp-step-dot';
        });
        [line1, line2].forEach(line => {
            if (line) line.classList.remove('active');
        });

        if (stepNumber === 1) {
            stepEmail.classList.add('active');
            dot1.classList.add('active');
            if (fpEmailError) fpEmailError.textContent = '';
        } else if (stepNumber === 2) {
            stepOtp.classList.add('active');
            dot1.classList.add('completed');
            line1.classList.add('active');
            dot2.classList.add('active');
            if (fpOtpError) fpOtpError.textContent = '';
            // Reset and focus first OTP input
            otpBoxes.forEach(b => {
                b.value = '';
                b.classList.remove('filled', 'error');
            });
            setTimeout(() => {
                if (otpBoxes[0]) otpBoxes[0].focus();
            }, 150);
            startResendTimer();
        } else if (stepNumber === 3) {
            stepPassword.classList.add('active');
            dot1.classList.add('completed');
            line1.classList.add('active');
            dot2.classList.add('completed');
            line2.classList.add('active');
            dot3.classList.add('active');
            if (fpPasswordError) fpPasswordError.textContent = '';
            if (fpNewPassword) fpNewPassword.value = '';
            if (fpConfirmPassword) fpConfirmPassword.value = '';
            setTimeout(() => {
                if (fpNewPassword) fpNewPassword.focus();
            }, 150);
        } else if (stepNumber === 4) {
            stepSuccess.classList.add('active');
            dot1.classList.add('completed');
            line1.classList.add('active');
            dot2.classList.add('completed');
            line2.classList.add('active');
            dot3.classList.add('completed');
        }
    }

    // Step 1: Send OTP
    if (fpSendOtpBtn) {
        fpSendOtpBtn.addEventListener('click', handleSendOtp);
    }
    if (fpEmailInput) {
        fpEmailInput.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                handleSendOtp();
            }
        });
    }

    function handleSendOtp() {
        const email = fpEmailInput.value.trim();
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

        if (!email) {
            fpEmailError.textContent = 'Please enter your email address.';
            fpEmailInput.focus();
            return;
        }

        if (!emailRegex.test(email)) {
            fpEmailError.textContent = 'Please enter a valid email address.';
            fpEmailInput.focus();
            return;
        }

        fpEmailError.textContent = '';
        currentEnteredEmail = email;

        // Button loading state
        const originalText = fpSendOtpBtn.innerHTML;
        fpSendOtpBtn.innerHTML = '<svg class="spinner" viewBox="0 0 50 50" style="width:18px;height:18px;"><circle class="path" cx="25" cy="25" r="20" fill="none" stroke-width="5"></circle></svg> Sending Code...';
        fpSendOtpBtn.style.pointerEvents = 'none';

        const formData = new FormData();
        formData.append('fp_action', 'send_otp');
        formData.append('fpEmail', email);

        fetch('login.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            fpSendOtpBtn.innerHTML = originalText;
            fpSendOtpBtn.style.pointerEvents = 'all';

            if (data.status === 'success') {
                if (fpDisplayEmail) fpDisplayEmail.textContent = email;
                if (fpVerifiedEmailDisplay) fpVerifiedEmailDisplay.textContent = email;
                if (fpOtpEmailInput) fpOtpEmailInput.value = email;
                goToStep(2);
            } else {
                fpEmailError.textContent = data.message || 'Error sending verification code.';
                fpEmailInput.focus();
            }
        })
        .catch(err => {
            fpSendOtpBtn.innerHTML = originalText;
            fpSendOtpBtn.style.pointerEvents = 'all';
            fpEmailError.textContent = 'Server connection error. Please try again.';
        });
    }

    // Change Email (back to step 1)
    if (fpChangeEmailBtn) {
        fpChangeEmailBtn.addEventListener('click', function () {
            goToStep(1);
        });
    }

    // Resend Timer Simulation
    function startResendTimer() {
        if (!fpResendBtn || !fpResendTimer) return;
        let countdown = 30;
        fpResendBtn.style.display = 'none';
        fpResendTimer.textContent = `(Resend in ${countdown}s)`;

        if (resendCountdown) clearInterval(resendCountdown);

        resendCountdown = setInterval(() => {
            countdown--;
            if (countdown > 0) {
                fpResendTimer.textContent = `(Resend in ${countdown}s)`;
            } else {
                clearInterval(resendCountdown);
                fpResendTimer.textContent = '';
                fpResendBtn.style.display = 'inline';
            }
        }, 1000);
    }

    if (fpResendBtn) {
        fpResendBtn.addEventListener('click', function () {
            if (!currentEnteredEmail) return;

            const formData = new FormData();
            formData.append('fp_action', 'send_otp');
            formData.append('fpEmail', currentEnteredEmail);

            fetch('login.php', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    otpBoxes.forEach(b => {
                        b.value = '';
                        b.classList.remove('filled', 'error');
                    });
                    if (finalOtpInput) finalOtpInput.value = '';
                    if (otpBoxes[0]) otpBoxes[0].focus();
                    if (fpOtpError) {
                        fpOtpError.style.color = '#10b981';
                        fpOtpError.textContent = 'A new 6-digit code has been sent!';
                        setTimeout(() => {
                            fpOtpError.textContent = '';
                            fpOtpError.style.color = '#ef4444';
                        }, 3000);
                    }
                    startResendTimer();
                } else {
                    if (fpOtpError) {
                        fpOtpError.style.color = '#ef4444';
                        fpOtpError.textContent = data.message || 'Failed to resend code.';
                    }
                }
            })
            .catch(() => {
                if (fpOtpError) {
                    fpOtpError.style.color = '#ef4444';
                    fpOtpError.textContent = 'Failed to resend code. Connection error.';
                }
            });
        });
    }

    // Helper: update the hidden input with current 6-digit OTP
    function updateFinalOtpValue() {
        const otpCode = Array.from(otpBoxes).map(b => b.value).join('');
        if (finalOtpInput) {
            finalOtpInput.value = otpCode;
        }
        return otpCode;
    }

    // 6-Digit OTP Box Interactions
    otpBoxes.forEach((input, index) => {
        // Handle character input
        input.addEventListener('input', function (e) {
            // Keep only digits
            this.value = this.value.replace(/[^0-9]/g, '');

            if (this.value) {
                this.classList.add('filled');
                this.classList.remove('error');
                // Move to next input if exists
                if (index < otpBoxes.length - 1) {
                    otpBoxes[index + 1].focus();
                }
            } else {
                this.classList.remove('filled');
            }

            // Sync with hidden input
            const otpCode = updateFinalOtpValue();

            // Auto-clear error if all 6 boxes are filled
            if (otpCode.length === 6) {
                if (fpOtpError) fpOtpError.textContent = '';
            }
        });

        // Handle Backspace & Arrow keys navigation
        input.addEventListener('keydown', function (e) {
            if (e.key === 'Backspace') {
                if (!this.value && index > 0) {
                    otpBoxes[index - 1].focus();
                    otpBoxes[index - 1].value = '';
                    otpBoxes[index - 1].classList.remove('filled');
                } else {
                    this.value = '';
                    this.classList.remove('filled');
                }
                updateFinalOtpValue();
            } else if (e.key === 'ArrowLeft' && index > 0) {
                otpBoxes[index - 1].focus();
            } else if (e.key === 'ArrowRight' && index < otpBoxes.length - 1) {
                otpBoxes[index + 1].focus();
            } else if (e.key === 'Enter') {
                e.preventDefault();
                handleVerifyOtp();
            }
        });

        // Handle Paste (e.g. user copies 6-digit OTP and pastes into any box)
        input.addEventListener('paste', function (e) {
            e.preventDefault();
            const pasteData = (e.clipboardData || window.clipboardData).getData('text');
            const cleanDigits = pasteData.replace(/[^0-9]/g, '').slice(0, 6);

            if (!cleanDigits) return;

            cleanDigits.split('').forEach((char, i) => {
                if (otpBoxes[i]) {
                    otpBoxes[i].value = char;
                    otpBoxes[i].classList.add('filled');
                    otpBoxes[i].classList.remove('error');
                }
            });

            updateFinalOtpValue();

            // Focus on the next empty box or the last box
            const nextEmptyIndex = Array.from(otpBoxes).findIndex(b => !b.value);
            if (nextEmptyIndex !== -1) {
                otpBoxes[nextEmptyIndex].focus();
            } else {
                otpBoxes[otpBoxes.length - 1].focus();
            }
        });
    });

    // Step 2: Verify OTP
    if (fpVerifyOtpBtn) {
        fpVerifyOtpBtn.addEventListener('click', handleVerifyOtp);
    }

    function handleVerifyOtp() {
        const otpCode = updateFinalOtpValue();

        if (otpCode.length < 6) {
            if (fpOtpError) {
                fpOtpError.style.color = '#ef4444';
                fpOtpError.textContent = 'Please enter all 6 digits of the verification code.';
            }
            otpBoxes.forEach(b => {
                if (!b.value) b.classList.add('error');
            });
            return;
        }

        if (fpOtpError) fpOtpError.textContent = '';

        // Simulate verification delay
        const originalText = fpVerifyOtpBtn.innerHTML;
        fpVerifyOtpBtn.innerHTML = '<svg class="spinner" viewBox="0 0 50 50" style="width:18px;height:18px;"><circle class="path" cx="25" cy="25" r="20" fill="none" stroke-width="5"></circle></svg> Verifying...';
        fpVerifyOtpBtn.style.pointerEvents = 'none';

        const formData = new FormData();
        formData.append('fp_action', 'verify_otp');
        formData.append('otp', otpCode);

        fetch('login.php', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            fpVerifyOtpBtn.innerHTML = originalText;
            fpVerifyOtpBtn.style.pointerEvents = 'all';

            if (data.status === 'success') {
                // OTP verified! Transition to Step 3 (New Password)
                goToStep(3);
            } else {
                if (fpOtpError) {
                    fpOtpError.style.color = '#ef4444';
                    fpOtpError.textContent = data.message || 'Invalid verification code.';
                }
                otpBoxes.forEach(b => b.classList.add('error'));
            }
        })
        .catch(err => {
            fpVerifyOtpBtn.innerHTML = originalText;
            fpVerifyOtpBtn.style.pointerEvents = 'all';
            if (fpOtpError) {
                fpOtpError.style.color = '#ef4444';
                fpOtpError.textContent = 'Server error. Please try again.';
            }
        });
    }

    // Step 3: Set New Password
    if (fpSubmitPasswordBtn) {
        fpSubmitPasswordBtn.addEventListener('click', handleResetPassword);
    }

    [fpNewPassword, fpConfirmPassword].forEach(input => {
        if (input) {
            input.addEventListener('keydown', function (e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    handleResetPassword();
                }
            });
        }
    });

    function handleResetPassword() {
        const newPass = fpNewPassword.value;
        const confirmPass = fpConfirmPassword.value;

        if (!newPass) {
            fpPasswordError.textContent = 'Please enter your new password.';
            fpNewPassword.focus();
            return;
        }

        if (newPass.length < 6) {
            fpPasswordError.textContent = 'Password must be at least 6 characters long.';
            fpNewPassword.focus();
            return;
        }

        if (!confirmPass) {
            fpPasswordError.textContent = 'Please re-enter your new password.';
            fpConfirmPassword.focus();
            return;
        }

        if (newPass !== confirmPass) {
            fpPasswordError.textContent = 'Passwords do not match. Please check again.';
            fpConfirmPassword.focus();
            return;
        }

        fpPasswordError.textContent = '';

        const originalText = fpSubmitPasswordBtn.innerHTML;
        fpSubmitPasswordBtn.innerHTML = '<svg class="spinner" viewBox="0 0 50 50" style="width:18px;height:18px;"><circle class="path" cx="25" cy="25" r="20" fill="none" stroke-width="5"></circle></svg> Updating Password...';
        fpSubmitPasswordBtn.style.pointerEvents = 'none';

        const formData = new FormData();
        formData.append('fp_action', 'reset_password');
        formData.append('newPassword', newPass);
        formData.append('confirmPassword', confirmPass);

        fetch('login.php', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            fpSubmitPasswordBtn.innerHTML = originalText;
            fpSubmitPasswordBtn.style.pointerEvents = 'all';

            if (data.status === 'success') {
                // Transition to Step 4 (Success)
                goToStep(4);
            } else {
                fpPasswordError.textContent = data.message || 'Failed to update password.';
            }
        })
        .catch(err => {
            fpSubmitPasswordBtn.innerHTML = originalText;
            fpSubmitPasswordBtn.style.pointerEvents = 'all';
            fpPasswordError.textContent = 'Server error. Please try again.';
        });
    }
});

