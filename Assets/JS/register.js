document.addEventListener('DOMContentLoaded', function () {
  // Tab switching (Student / Organization / Company)
  const tabs = document.querySelectorAll('.tabs button');
  tabs.forEach(tab => {
    tab.addEventListener('click', function () {
      tabs.forEach(t => t.classList.remove('active'));
      this.classList.add('active');
      // Only "Student" is wired to a real form right now.
      if (this.dataset.role !== 'student') {
        alert('Registration for "' + this.textContent.trim() + '" is coming soon.');
        document.querySelector('[data-role="student"]').classList.add('active');
        this.classList.remove('active');
      }
    });
  });

  // Password show/hide for all password fields
  const toggles = document.querySelectorAll('.toggle-eye');
  toggles.forEach(toggle => {
    toggle.addEventListener('click', function () {
      const pwd = this.previousElementSibling;
      if (pwd && pwd.tagName === 'INPUT') {
        pwd.type = pwd.type === 'password' ? 'text' : 'password';
      }
    });
  });

  // Password Match Validation
  const form = document.querySelector('form');
  const pwdInput = document.querySelector('#password');
  const rePwdInput = document.querySelector('#Re-password');

  if (form && pwdInput && rePwdInput) {
    form.addEventListener('submit', function (e) {
      if (pwdInput.value !== rePwdInput.value) {
        e.preventDefault(); // Stop form submission
        
        // Turn the re-password box red
        rePwdInput.style.borderColor = 'red';
        
        // Check if error message already exists, if not create one
        let errorMsg = document.querySelector('#pwd-error-msg');
        if (!errorMsg) {
          errorMsg = document.createElement('span');
          errorMsg.id = 'pwd-error-msg';
          errorMsg.style.color = 'red';
          errorMsg.style.fontSize = '12px';
          errorMsg.style.marginTop = '5px';
          errorMsg.style.display = 'block';
          errorMsg.textContent = 'Passwords do not match!';
          // Insert after the password wrap
          rePwdInput.closest('.password-wrap').insertAdjacentElement('afterend', errorMsg);
        }
      } else {
        // Reset styles if they match (in case it was previously wrong)
        rePwdInput.style.borderColor = '';
        const errorMsg = document.querySelector('#pwd-error-msg');
        if (errorMsg) errorMsg.remove();
      }
    });

    // Remove red styling when user starts typing to correct the mistake
    rePwdInput.addEventListener('input', function() {
        rePwdInput.style.borderColor = '';
        const errorMsg = document.querySelector('#pwd-error-msg');
        if (errorMsg) errorMsg.remove();
    });
  }
});