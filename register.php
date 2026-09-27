<?php
require_once 'Auth/Registation_handler.php';
require_once __DIR__ . '/Includes/register-header.php';
$facultyMap = fetchFacultyMap($conn);   // faculties from the database for the student form
?>

<div class="page-wrap">

  <div class="side-panel">
    <a href="index.php" class="brand" title="Back to SkillBridge Home" style="text-decoration:none;">
      <img src="Assets/Images/logo.png" alt="SkillBridge" class="brand-logo">
    </a>
    <h1>Empower Your Career Journey</h1>
    <p>Connect with organizations, collaborate on academic projects, and unlock opportunities designed for future
      talent.</p>
    <div class="avatars">
      <div class="avatar-stack">
        <span class="avatar-circle a1">👩</span>
        <span class="avatar-circle a2">🧑</span>
        <span class="avatar-circle a3">👨</span>
        <span class="avatar-circle count">+5k</span>
      </div>
      <span>Join 5,000+ active users</span>
    </div>
  </div>

  <div class="form-panel">
    <div class="form-inner">
      <?php 
        $activeRole = 'student';
        if (isset($flash['role']) && !empty($flash['role'])) {
            $activeRole = strtolower($flash['role']);
        } elseif (isset($_POST['role']) && !empty($_POST['role'])) {
            $activeRole = strtolower($_POST['role']);
        }
        if (!in_array($activeRole, ['student', 'organization', 'company'])) {
            $activeRole = 'student';
        }

        $titles = [
            'student' => ['title' => 'Create a Student Account', 'subtitle' => 'Start your journey today by choosing your role.'],
            'organization' => ['title' => 'Create Organization Account', 'subtitle' => 'Register your organization and connect with talented students through SkillBridge.'],
            'company' => ['title' => 'Create Company Account', 'subtitle' => 'Join SkillBridge to discover talented students and provide internship opportunities.']
        ];
      ?>
      <h2 id="form-title"><?= $titles[$activeRole]['title'] ?></h2>
      <p class="subtitle" id="form-subtitle"><?= $titles[$activeRole]['subtitle'] ?></p>

      <div class="tabs">
        <button type="button" class="<?= $activeRole === 'student' ? 'active' : '' ?>" data-role="student">Student</button>
        <button type="button" class="<?= $activeRole === 'organization' ? 'active' : '' ?>" data-role="organization">Organization</button>
        <button type="button" class="<?= $activeRole === 'company' ? 'active' : '' ?>" data-role="company">Company</button>
      </div>

      <?php if ($flash): ?>
        <div class="flash <?= htmlspecialchars($flash['type']) ?>">
          <?= htmlspecialchars($flash['message']) ?>
        </div>
      <?php endif; ?>

      <!-- =====================================================================
           STUDENT FORM
           UNCHANGED — same fields/names as before, just wrapped in .role-form
           so it can be shown/hidden by the tab-switch JS below.
      ===================================================================== -->
      <form action="" method="POST" novalidate class="role-form <?= $activeRole === 'student' ? 'active' : '' ?>" data-role-form="student">

        <div class="form-group">
          <label for="full_name">Full Name</label>
          <input type="hidden" id="role" name="role" value="student">
          <input type="text" id="full_name" name="name" placeholder="Alex Johnson"
            value="<?= htmlspecialchars($_SESSION['old']['name'] ?? '') ?>" required>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label for="email">University Email</label>
            <input type="email" id="email" name="email" placeholder="alex@uni.edu"
              value="<?= htmlspecialchars($_SESSION['old']['email'] ?? '') ?>" required>
          </div>
          <div class="form-group">
            <label for="university">University</label>
            <div class="uni-combobox" id="uniCombobox">
              <input type="text" id="university" name="university" placeholder="Select or type university" autocomplete="off"
                role="combobox" aria-expanded="false" aria-controls="uniList" aria-autocomplete="list"
                value="<?= htmlspecialchars($_SESSION['old']['university'] ?? '') ?>" required>
              <button type="button" class="uni-toggle" tabindex="-1" aria-label="Show universities">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
              </button>
              <ul class="uni-list" id="uniList" role="listbox" hidden></ul>
            </div>
          </div>
        </div>

        <!-- Faculty: searchable list loaded from the database — the student must pick one from the list -->
        <div class="form-group">
          <label for="faculty_input">Faculty</label>
          <div class="fac-combobox" id="facCombobox">
            <input type="text" id="faculty_input" placeholder="Select or search faculty" autocomplete="off"
              role="combobox" aria-expanded="false" aria-controls="facList" aria-autocomplete="list"
              value="<?= htmlspecialchars($_POST['faculty'] ?? ($_SESSION['old']['faculty'] ?? '')) ?>">
            <input type="hidden" id="faculty" name="faculty"
              value="<?= htmlspecialchars($_POST['faculty'] ?? ($_SESSION['old']['faculty'] ?? '')) ?>">
            <button type="button" class="fac-toggle" tabindex="-1" aria-label="Show faculties">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
            </button>
            <ul class="fac-list" id="facList" role="listbox" hidden></ul>
          </div>
          <span class="fac-error" id="facError" hidden>Please select a faculty from the list.</span>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label for="degree">Degree Program</label>
            <input type="text" id="degree" name="degree" placeholder="B.S. CS"
              value="<?= htmlspecialchars($_SESSION['old']['degree'] ?? '') ?>" required>
          </div>
          <div class="form-group">
            <label for="year">Academic Year</label>
            <select id="year" name="academicYear" required>
              <option value="">Select year</option>
              <?php foreach (['Year 1', 'Year 2', 'Year 3', 'Year 4'] as $y): ?>
                <option value="<?= $y ?>" <?= (($_SESSION['old']['academicYear'] ?? '') === $y) ? 'selected' : '' ?>>
                  <?= $y ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label for="password">Password</label>
            <div class="password-wrap">
              <input type="password" id="password" name="password" placeholder="••••••••" required minlength="8" pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}" title="Must contain at least 8 characters, including uppercase, lowercase, and numbers">
              <button type="button" class="toggle-eye">👁</button>
            </div>
            <!-- Password Requirements (Smooth downward accordion list, appears on focus) -->
            <div class="reg-pass-req">
              <div class="req-item req-length">
                <span class="req-icon">✕</span>
                <span>At least 8 characters</span>
              </div>
              <div class="req-item req-upper">
                <span class="req-icon">✕</span>
                <span>At least 1 uppercase (A-Z)</span>
              </div>
              <div class="req-item req-lower">
                <span class="req-icon">✕</span>
                <span>At least 1 lowercase (a-z)</span>
              </div>
              <div class="req-item req-num">
                <span class="req-icon">✕</span>
                <span>At least 1 number (0-9)</span>
              </div>
            </div>
          </div>
          <div class="form-group">
            <label for="Re-password">Re-Password</label>
            <div class="password-wrap">
              <input type="password" id="Re-password" name="Re-password" placeholder="••••••••" required minlength="8">
              <button type="button" class="toggle-eye">👁</button>
            </div>
          </div>
        </div>

        <label class="terms">
          <input type="checkbox" name="agree" required>
          <span>I agree to the <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a>.</span>
        </label>

        <button type="submit" class="btn-submit">Create Student Account</button>
      </form>

      <!-- =====================================================================
           ORGANIZATION FORM
           Built to match the provided Organization design screenshot.
      ===================================================================== -->
      <form action="" method="POST" novalidate class="role-form <?= $activeRole === 'organization' ? 'active' : '' ?>" data-role-form="organization">
        <input type="hidden" name="role" value="organization">

        <div class="form-group">
          <label for="org_name">Organization Name</label>
          <input type="text" id="org_name" name="name" placeholder="University Career Center" required>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label for="org_email">Organization Email</label>
            <input type="email" id="org_email" name="email" placeholder="contact@organization.edu" required>
          </div>
          <div class="form-group">
            <label for="org_type">Organization Type</label>
            <select id="org_type" name="org_type" required>
              <option value="">Select Type</option>
              <option value="University">University</option>
              <option value="NGO">NGO</option>
              <option value="Government">Government</option>
              <option value="Other">Other</option>
            </select>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label for="org_contact_person">Contact Person Name</label>
            <input type="text" id="org_contact_person" name="contact_person" placeholder="e.g. John Smith" required>
          </div>
          <div class="form-group">
            <label for="org_contact_number">Contact Number</label>
            <input type="tel" id="org_contact_number" name="contact_number" placeholder="+94 77 123 4567" required>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label for="org_website">Website</label>
            <input type="text" id="org_website" name="website" placeholder="www.organization.edu">
          </div>
          <div class="form-group">
            <label for="org_location">Location</label>
            <input type="text" id="org_location" name="location" placeholder="Colombo, Sri Lanka" required>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label for="org_password">Password</label>
            <div class="password-wrap">
              <input type="password" id="org_password" name="password" placeholder="••••••••" required minlength="8" pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}" title="Must contain at least 8 characters, including uppercase, lowercase, and numbers">
              <button type="button" class="toggle-eye">👁</button>
            </div>
            <!-- Password Requirements (Smooth downward accordion list, appears on focus) -->
            <div class="reg-pass-req">
              <div class="req-item req-length">
                <span class="req-icon">✕</span>
                <span>At least 8 characters</span>
              </div>
              <div class="req-item req-upper">
                <span class="req-icon">✕</span>
                <span>At least 1 uppercase (A-Z)</span>
              </div>
              <div class="req-item req-lower">
                <span class="req-icon">✕</span>
                <span>At least 1 lowercase (a-z)</span>
              </div>
              <div class="req-item req-num">
                <span class="req-icon">✕</span>
                <span>At least 1 number (0-9)</span>
              </div>
            </div>
          </div>
          <div class="form-group">
            <label for="org_re_password">Re-Password</label>
            <div class="password-wrap">
              <input type="password" id="org_re_password" name="Re-password" placeholder="••••••••" required minlength="8">
              <button type="button" class="toggle-eye">👁</button>
            </div>
          </div>
        </div>

        <label class="terms">
          <input type="checkbox" name="agree" required>
          <span>I agree to the <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a>.</span>
        </label>

        <button type="submit" class="btn-submit">Create Organization Account</button>
      </form>

      <!-- =====================================================================
           COMPANY FORM
           Built to match the provided Company design screenshot.
      ===================================================================== -->
      <form action="" method="POST" novalidate class="role-form <?= $activeRole === 'company' ? 'active' : '' ?>" data-role-form="company">
        <input type="hidden" name="role" value="company">

        <div class="form-group">
          <label for="company_name">Company Name</label>
          <input type="text" id="company_name" name="name" placeholder="ABC Technologies Pvt Ltd" required>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label for="company_email">Business Email</label>
            <input type="email" id="company_email" name="email" placeholder="hr@company.com" required>
          </div>
          <div class="form-group">
            <label for="company_industry">Industry Sector</label>
            <select id="company_industry" name="org_type" required>
              <option value="">Select Sector</option>
              <option value="Technology">Technology</option>
              <option value="Finance">Finance</option>
              <option value="Healthcare">Healthcare</option>
              <option value="Manufacturing">Manufacturing</option>
              <option value="Other">Other</option>
            </select>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label for="company_contact_person">Contact Person Name</label>
            <input type="text" id="company_contact_person" name="contact_person" placeholder="e.g. John Smith" required>
          </div>
          <div class="form-group">
            <label for="company_contact_number">Contact Number</label>
            <input type="tel" id="company_contact_number" name="contact_number" placeholder="+94 77 123 4567" required>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label for="company_website">Website</label>
            <input type="text" id="company_website" name="website" placeholder="www.company.com">
          </div>
          <div class="form-group">
            <label for="company_location">Location</label>
            <input type="text" id="company_location" name="location" placeholder="Colombo, Sri Lanka" required>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label for="company_password">Password</label>
            <div class="password-wrap">
              <input type="password" id="company_password" name="password" placeholder="••••••••" required minlength="8" pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}" title="Must contain at least 8 characters, including uppercase, lowercase, and numbers">
              <button type="button" class="toggle-eye">👁</button>
            </div>
            <!-- Password Requirements (Smooth downward accordion list, appears on focus) -->
            <div class="reg-pass-req">
              <div class="req-item req-length">
                <span class="req-icon">✕</span>
                <span>At least 8 characters</span>
              </div>
              <div class="req-item req-upper">
                <span class="req-icon">✕</span>
                <span>At least 1 uppercase (A-Z)</span>
              </div>
              <div class="req-item req-lower">
                <span class="req-icon">✕</span>
                <span>At least 1 lowercase (a-z)</span>
              </div>
              <div class="req-item req-num">
                <span class="req-icon">✕</span>
                <span>At least 1 number (0-9)</span>
              </div>
            </div>
          </div>
          <div class="form-group">
            <label for="company_re_password">Re-Password</label>
            <div class="password-wrap">
              <input type="password" id="company_re_password" name="Re-password" placeholder="••••••••" required minlength="8">
              <button type="button" class="toggle-eye">👁</button>
            </div>
          </div>
        </div>

        <label class="terms">
          <input type="checkbox" name="agree" required>
          <span>I agree to the <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a>.</span>
        </label>

        <button type="submit" class="btn-submit">Create Company Account</button>
      </form>

      <p class="signin-link">Already have an account? <a href="Auth/login.php">Sign In</a></p>
    </div>
  </div>

</div>

<style>
  .fac-combobox { position: relative; }
  #facCombobox #faculty_input { padding-right: 38px; cursor: pointer; }
  #facCombobox #faculty_input:focus { cursor: text; }
  .fac-toggle { position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none;
                padding: 2px; color: #667085; cursor: pointer; display: flex; transition: transform .15s ease; }
  .fac-combobox.open .fac-toggle { transform: translateY(-50%) rotate(180deg); color: #0b3d66; }
  .fac-list { position: absolute; z-index: 30; top: calc(100% + 4px); left: 0; right: 0; margin: 0; padding: 6px; list-style: none;
              background: #fff; border: 1px solid #d9dce1; border-radius: 10px; box-shadow: 0 10px 28px rgba(15, 23, 42, .14);
              max-height: 220px; overflow-y: auto; }
  .fac-list[hidden] { display: none; }
  .fac-list li { padding: 9px 12px; font-size: 14px; color: #1f2937; border-radius: 6px; cursor: pointer; }
  .fac-list li:hover, .fac-list li.active { background: #f1f5f9; }
  .fac-list li.selected { font-weight: 600; color: #0b3d66; }
  .fac-list li.fac-empty { color: #94a3b8; cursor: default; }
  .fac-list li.fac-empty:hover { background: none; }
  .fac-error { display: block; margin-top: 5px; font-size: 12px; color: red; }
  .fac-error[hidden] { display: none; }

  .uni-combobox { position: relative; }
  #uniCombobox #university { padding-right: 38px; }
  .uni-toggle { position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none;
                padding: 2px; color: #667085; cursor: pointer; display: flex; transition: transform .15s ease; }
  .uni-combobox.open .uni-toggle { transform: translateY(-50%) rotate(180deg); color: #0b3d66; }
  .uni-list { position: absolute; z-index: 30; top: calc(100% + 4px); left: 0; right: 0; margin: 0; padding: 6px; list-style: none;
              background: #fff; border: 1px solid #d9dce1; border-radius: 10px; box-shadow: 0 10px 28px rgba(15, 23, 42, .14);
              max-height: 220px; overflow-y: auto; }
  .uni-list[hidden] { display: none; }
  .uni-list li { padding: 9px 12px; font-size: 14px; color: #1f2937; border-radius: 6px; cursor: pointer; }
  .uni-list li:hover, .uni-list li.active { background: #f1f5f9; }
  .uni-list li.selected { font-weight: 600; color: #0b3d66; }
  .uni-list li.uni-empty { color: #94a3b8; cursor: default; }
  .uni-list li.uni-empty:hover { background: none; }
</style>

<script>
(function () {
  // universities -> faculties, loaded from the database
  var facultyMap = <?= json_encode($facultyMap, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) ?>;

  // University: searchable list loaded from the database — student can pick from the list or type a new one
  (function () {
    var ubox = document.getElementById('uniCombobox');
    if (!ubox) return;
    var uinput = document.getElementById('university');
    var ulist  = document.getElementById('uniList');
    var utoggle = ubox.querySelector('.uni-toggle');
    var unames = Object.keys(facultyMap);
    var uitems = [], uactive = -1;

    if (!unames.length) return; // nothing in the database yet — leave as a plain text field

    function urender(filter) {
      var q = (filter || '').trim().toLowerCase();
      uitems = unames.filter(function (u) { return !q || u.toLowerCase().indexOf(q) !== -1; });
      uactive = -1;
      ulist.innerHTML = '';
      if (!uitems.length) {
        var empty = document.createElement('li');
        empty.className = 'uni-empty';
        empty.textContent = 'No universities found.';
        ulist.appendChild(empty);
        return;
      }
      uitems.forEach(function (u) {
        var li = document.createElement('li');
        li.setAttribute('role', 'option');
        li.textContent = u;
        if (u === uinput.value) li.className = 'selected';
        li.addEventListener('mousedown', function (e) { e.preventDefault(); uchoose(u); });
        ulist.appendChild(li);
      });
    }
    function uopen(filter) {
      urender(filter);
      ulist.hidden = false;
      ubox.classList.add('open');
      uinput.setAttribute('aria-expanded', 'true');
    }
    function uclose() {
      ulist.hidden = true;
      ubox.classList.remove('open');
      uinput.setAttribute('aria-expanded', 'false');
    }
    function uchoose(u) {
      uinput.value = u;
      uclose();
      // let the faculty combobox know the university changed
      uinput.dispatchEvent(new Event('input', { bubbles: true }));
    }
    function umove(step) {
      var lis = ulist.querySelectorAll('li[role="option"]');
      if (!lis.length) return;
      if (uactive >= 0) lis[uactive].classList.remove('active');
      uactive = (uactive + step + lis.length) % lis.length;
      lis[uactive].classList.add('active');
      lis[uactive].scrollIntoView({ block: 'nearest' });
    }

    uinput.addEventListener('focus', function () { uopen(uinput.value); });
    uinput.addEventListener('click', function () { if (ulist.hidden) uopen(uinput.value); });
    uinput.addEventListener('input', function () { uopen(uinput.value); });
    uinput.addEventListener('keydown', function (e) {
      if (e.key === 'ArrowDown') { e.preventDefault(); if (ulist.hidden) uopen(uinput.value); umove(1); }
      else if (e.key === 'ArrowUp') { e.preventDefault(); if (ulist.hidden) uopen(uinput.value); umove(-1); }
      else if (e.key === 'Enter') { if (!ulist.hidden && uactive >= 0) { e.preventDefault(); uchoose(uitems[uactive]); } }
      else if (e.key === 'Escape') { uclose(); }
    });
    uinput.addEventListener('blur', function () { uclose(); });
    utoggle.addEventListener('mousedown', function (e) {
      e.preventDefault();
      if (ulist.hidden) { uinput.focus(); uopen(uinput.value); } else { uclose(); }
    });
  })();

  var box     = document.getElementById('facCombobox');
  if (!box) return;
  var input   = document.getElementById('faculty_input');
  var hidden  = document.getElementById('faculty');
  var list    = document.getElementById('facList');
  var toggle  = box.querySelector('.fac-toggle');
  var errorEl = document.getElementById('facError');
  var uniInput = document.getElementById('university');
  var form    = box.closest('form');
  var items = [], active = -1;

  function allFaculties() {
    var seen = {}, out = [];
    Object.keys(facultyMap).forEach(function (u) {
      facultyMap[u].forEach(function (f) { if (!seen[f]) { seen[f] = true; out.push(f); } });
    });
    return out;
  }

  // If the typed university matches one in the database, show only its faculties; otherwise show all
  function available() {
    var u = (uniInput && uniInput.value || '').trim().toLowerCase();
    if (u) {
      var names = Object.keys(facultyMap);
      for (var i = 0; i < names.length; i++) {
        if (names[i].toLowerCase() === u) return facultyMap[names[i]];
      }
    }
    return allFaculties();
  }

  // Nothing in the database yet -> hide the field instead of blocking sign-up
  if (!allFaculties().length) { box.closest('.form-group').style.display = 'none'; return; }

  function render(filter) {
    var q = (filter || '').trim().toLowerCase();
    items = available().filter(function (f) { return !q || f.toLowerCase().indexOf(q) !== -1; });
    active = -1;
    list.innerHTML = '';
    if (!items.length) {
      var empty = document.createElement('li');
      empty.className = 'fac-empty';
      empty.textContent = 'No faculties found.';
      list.appendChild(empty);
      return;
    }
    items.forEach(function (f) {
      var li = document.createElement('li');
      li.setAttribute('role', 'option');
      li.textContent = f;
      if (f === hidden.value) li.className = 'selected';
      li.addEventListener('mousedown', function (e) { e.preventDefault(); choose(f); });
      list.appendChild(li);
    });
  }

  function open(filter) {
    render(filter);
    list.hidden = false;
    box.classList.add('open');
    input.setAttribute('aria-expanded', 'true');
  }
  function close() {
    list.hidden = true;
    box.classList.remove('open');
    input.setAttribute('aria-expanded', 'false');
  }
  function choose(f) {
    input.value = f;
    hidden.value = f;
    errorEl.hidden = true;
    close();
  }
  function move(step) {
    var lis = list.querySelectorAll('li[role="option"]');
    if (!lis.length) return;
    if (active >= 0) lis[active].classList.remove('active');
    active = (active + step + lis.length) % lis.length;
    lis[active].classList.add('active');
    lis[active].scrollIntoView({ block: 'nearest' });
  }

  input.addEventListener('focus', function () { open(''); });
  input.addEventListener('click', function () { if (list.hidden) open(''); });
  input.addEventListener('input', function () {
    hidden.value = '';                 // typing never creates a value — it must be picked from the list
    open(input.value);
  });
  input.addEventListener('keydown', function (e) {
    if (e.key === 'ArrowDown') { e.preventDefault(); if (list.hidden) open(input.value); move(1); }
    else if (e.key === 'ArrowUp') { e.preventDefault(); if (list.hidden) open(input.value); move(-1); }
    else if (e.key === 'Enter') {
      if (!list.hidden) {
        e.preventDefault();
        if (active >= 0) choose(items[active]);
        else if (items.length === 1) choose(items[0]);
      }
    }
    else if (e.key === 'Escape') { close(); }
  });
  input.addEventListener('blur', function () {
    close();
    // keep the text only if it exactly matches a faculty from the list, otherwise clear it
    var typed = input.value.trim().toLowerCase();
    var match = available().filter(function (f) { return f.toLowerCase() === typed; })[0];
    if (match) { input.value = match; hidden.value = match; errorEl.hidden = true; }
    else { input.value = ''; hidden.value = ''; }
  });
  toggle.addEventListener('mousedown', function (e) {
    e.preventDefault();
    if (list.hidden) { input.focus(); open(''); } else { close(); }
  });

  // If the university changes and the chosen faculty no longer belongs to it, clear the faculty
  if (uniInput) {
    uniInput.addEventListener('input', function () {
      if (hidden.value && available().indexOf(hidden.value) === -1) { input.value = ''; hidden.value = ''; }
      if (!list.hidden) render(input.value);
    });
  }

  // Block sign-up until a faculty is chosen from the list
  if (form) {
    form.addEventListener('submit', function (e) {
      if (!hidden.value) {
        e.preventDefault();
        errorEl.hidden = false;
        input.focus();
      }
    });
  }
})();
</script>

<?php unset($_SESSION['old']); ?>
<?php require_once __DIR__ . '/Includes/register-footer.php'; ?>