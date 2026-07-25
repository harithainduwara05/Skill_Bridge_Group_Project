document.addEventListener('DOMContentLoaded', function () {
  // ===========================================================================
  // NEW: Tab switching (Student / Organization / Company)
  // Each role has its OWN <form class="role-form" data-role-form="student|organization|company">
  // in register.php. Only one is visible at a time — controlled by the
  // ".role-form.active" CSS rule in register-style.css (display:none / block).
  // Clicking a tab just: (1) marks that tab as active, (2) shows its matching
  // form section and hides the rest, (3) updates the heading text/subtitle.
  // If you need to change wording per role, edit the "titles" object below —
  // nothing else needs to change.
  // ===========================================================================
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
    toggle.addEventListener('click', function () {
      const pwd = this.previousElementSibling;
      if (pwd && pwd.tagName === 'INPUT') {
        pwd.type = pwd.type === 'password' ? 'text' : 'password';
      }
    });
  });

  // Password Match Validation (Student form only)
  const studentForm = document.querySelector('.role-form[data-role-form="student"]');
  const pwdInput = document.querySelector('#password');
  const rePwdInput = document.querySelector('#Re-password');

  if (studentForm && pwdInput && rePwdInput) {
    studentForm.addEventListener('submit', function (e) {
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