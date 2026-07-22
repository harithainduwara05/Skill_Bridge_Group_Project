document.addEventListener('DOMContentLoaded', function() {
    // Password toggle functionality
    const togglePasswordBtn = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('password');

    if (togglePasswordBtn && passwordInput) {
        togglePasswordBtn.addEventListener('click', function() {
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
        loginForm.addEventListener('submit', function(e) {
            e.preventDefault(); // Prevent actual submission since it's frontend only
            
            // Basic validation
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            const role = document.getElementById('role').value;

            if (!email || !password || !role) {
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
                
                // Show success message
                alert(`Login successful for ${role} with email: ${email}!`);
                
            }, 1500);
        });
    }
});
