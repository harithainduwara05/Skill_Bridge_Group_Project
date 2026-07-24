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

  // Password show/hide
  const toggle = document.querySelector('.toggle-eye');
  const pwd = document.querySelector('#password');
  if (toggle && pwd) {
    toggle.addEventListener('click', function () {
      pwd.type = pwd.type === 'password' ? 'text' : 'password';
    });
  }
});