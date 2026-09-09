document.addEventListener('DOMContentLoaded', function () {
  const tabs = document.querySelectorAll('.tabs button');
  const forms = document.querySelectorAll('.role-form');
  const titleEl = document.querySelector('#form-title');
  const subtitleEl = document.querySelector('#form-subtitle');

  const titles = {
    student: { title: 'Create a Student Account', subtitle: 'Start your journey today by choosing your role.' },
    organization: { title: 'Create Organization Account', subtitle: 'Register your organization and connect with talented students through SkillBridge.' },
    // Company text/fields now match the provided Company design screenshot.
    company: { title: 'Create Company Account', subtitle: 'Join SkillBridge to discover talented students and provide internship opportunities.' }
  };

  // Set initial heading text based on the active tab (in case page reloaded on error)
  const activeTab = document.querySelector('.tabs button.active');
  if (activeTab && titleEl && subtitleEl && titles[activeTab.dataset.role]) {
    titleEl.textContent = titles[activeTab.dataset.role].title;
    subtitleEl.textContent = titles[activeTab.dataset.role].subtitle;
  }

  tabs.forEach(tab => {
    tab.addEventListener('click', function () {
      const role = this.dataset.role;

      // Toggle active tab button
      tabs.forEach(t => t.classList.remove('active'));
      this.classList.add('active');

      // Toggle active form section (deactivate all, activate the matching one)
      forms.forEach(f => f.classList.remove('active'));
      const targetForm = document.querySelector('.role-form[data-role-form="' + role + '"]');
      if (targetForm) targetForm.classList.add('active');

      // Update heading text for the active role
      if (titleEl && subtitleEl && titles[role]) {
        titleEl.textContent = titles[role].title;
        subtitleEl.textContent = titles[role].subtitle;
      }
    });
  });

  // Password show/hide for all password fields
  const toggles = document.querySelectorAll('.toggle-eye');
  toggles.forEach(toggle => {
    toggle.addEventListener('mousedown', function (e) {
      e.preventDefault(); // Prevents input from losing focus when eye is clicked
    });
    toggle.addEventListener('click', function () {
      const pwd = this.previousElementSibling;
      if (pwd && pwd.tagName === 'INPUT') {
        pwd.type = pwd.type === 'password' ? 'text' : 'password';
      }
    });
  });

  // Password Complexity & Match Validation for all role forms
  const passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/;
  const allRoleForms = document.querySelectorAll('.role-form');
  allRoleForms.forEach(form => {
    const pwdInput = form.querySelector('input[name="password"]');
    const rePwdInput = form.querySelector('input[name="Re-password"]');

    if (pwdInput && rePwdInput) {
      form.addEventListener('submit', function (e) {
        let hasError = false;

        // 1. Password Complexity Validation
        if (!passwordRegex.test(pwdInput.value)) {
          e.preventDefault();
          hasError = true;
          pwdInput.style.borderColor = 'red';

          let complexityMsg = form.querySelector('.pwd-complexity-msg');
          if (!complexityMsg) {
            complexityMsg = document.createElement('span');
            complexityMsg.className = 'pwd-complexity-msg';
            complexityMsg.style.color = 'red';
            complexityMsg.style.fontSize = '12px';
            complexityMsg.style.marginTop = '5px';
            complexityMsg.style.display = 'block';
            complexityMsg.textContent = 'Password must be at least 8 characters long and contain uppercase, lowercase letters, and numbers.';
            pwdInput.closest('.password-wrap').insertAdjacentElement('afterend', complexityMsg);
          }
        } else {
          pwdInput.style.borderColor = '';
          const complexityMsg = form.querySelector('.pwd-complexity-msg');
          if (complexityMsg) complexityMsg.remove();
        }

        // 2. Password Match Validation
        if (pwdInput.value !== rePwdInput.value) {
          e.preventDefault();
          hasError = true;

          // Turn the re-password box red
          rePwdInput.style.borderColor = 'red';

          // Check if error message already exists, if not create one
          let errorMsg = form.querySelector('.pwd-error-msg');
          if (!errorMsg) {
            errorMsg = document.createElement('span');
            errorMsg.className = 'pwd-error-msg';
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
          const errorMsg = form.querySelector('.pwd-error-msg');
          if (errorMsg) errorMsg.remove();
        }
      });

      // Real-time Requirements Checklist (Smooth Accordion)
      const reqBox = form.querySelector('.reg-pass-req');
      if (reqBox) {
        function updateRegReq(selector, isValid) {
          const item = reqBox.querySelector(selector);
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

        function checkRequirements() {
          const val = pwdInput.value;
          updateRegReq('.req-length', val.length >= 8);
          updateRegReq('.req-upper', /[A-Z]/.test(val));
          updateRegReq('.req-lower', /[a-z]/.test(val));
          updateRegReq('.req-num', /\d/.test(val));
        }

        // Smooth accordion slide down when password field is focused
        pwdInput.addEventListener('focus', function() {
          reqBox.classList.add('show');
          checkRequirements();
        });

        // Smooth accordion slide up when clicking outside or away to another field
        pwdInput.addEventListener('blur', function() {
          reqBox.classList.remove('show');
        });

        // Live check while typing
        pwdInput.addEventListener('input', function() {
          checkRequirements();
        });
      }

      // Remove red styling when user starts typing in password
      pwdInput.addEventListener('input', function() {
        if (passwordRegex.test(pwdInput.value)) {
          pwdInput.style.borderColor = '';
          const complexityMsg = form.querySelector('.pwd-complexity-msg');
          if (complexityMsg) complexityMsg.remove();
        }
      });

      // Remove red styling when user starts typing in re-password
      rePwdInput.addEventListener('input', function() {
        rePwdInput.style.borderColor = '';
        const errorMsg = form.querySelector('.pwd-error-msg');
        if (errorMsg) errorMsg.remove();
      });
    }
  });
});