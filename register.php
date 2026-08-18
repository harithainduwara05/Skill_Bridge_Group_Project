<?php
require_once 'Auth/Registation_handler.php';
require_once __DIR__ . '/Includes/register-header.php';
?>

<div class="page-wrap">

  <div class="side-panel">
    <div class="brand">
      <img src="Assets/Images/logo.png" alt="SkillBridge" class="brand-logo">
    </div>
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
            <input type="text" id="university" name="university" placeholder="University of Colombo"
              value="<?= htmlspecialchars($_SESSION['old']['university'] ?? '') ?>" required>
          </div>
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
              <input type="password" id="password" name="password" placeholder="••••••••" required minlength="8">
              <button type="button" class="toggle-eye">👁</button>
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
              <input type="password" id="org_password" name="password" placeholder="••••••••" required minlength="8">
              <button type="button" class="toggle-eye">👁</button>
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
              <input type="password" id="company_password" name="password" placeholder="••••••••" required minlength="8">
              <button type="button" class="toggle-eye">👁</button>
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

<?php unset($_SESSION['old']); ?>
<?php require_once __DIR__ . '/Includes/register-footer.php'; ?>