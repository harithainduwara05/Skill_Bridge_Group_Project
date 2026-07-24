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
      <h2>Create a Student Account</h2>
      <p class="subtitle">Start your journey today by choosing your role.</p>

      <div class="tabs">
        <button type="button" class="active" data-role="student">Student</button>
        <button type="button" data-role="organization">Organization</button>
        <button type="button" data-role="company">Company</button>
      </div>

      <?php if ($flash): ?>
        <div class="flash <?= htmlspecialchars($flash['type']) ?>">
          <?= htmlspecialchars($flash['message']) ?>
        </div>
      <?php endif; ?>

      <form action="" method="POST" novalidate>

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
              <?php foreach (['Year 1', 'Year 2', 'Year 3', 'Year 4', 'Year 5'] as $y): ?>
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

      <p class="signin-link">Already have an account? <a href="Auth/login.php">Sign In</a></p>
    </div>
  </div>

</div>

<?php unset($_SESSION['old']); ?>
<?php require_once __DIR__ . '/Includes/register-footer.php'; ?>