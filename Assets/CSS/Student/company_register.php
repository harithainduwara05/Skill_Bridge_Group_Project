<?php
// Start session for error messages
session_start();

// Get form data if available
$form_data = isset($_SESSION['form_data']) ? $_SESSION['form_data'] : [];
$errors = isset($_SESSION['registration_errors']) ? $_SESSION['registration_errors'] : [];

// Clear session data after displaying
unset($_SESSION['registration_errors']);
unset($_SESSION['form_data']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Company Account - SkillBridge</title>
    <!-- CSS File -->
    <link rel="stylesheet" href="company_register.css">
    <!-- FontAwesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<div class="auth-container">
    <!-- Left Blue Hero Sidebar -->
    <div class="auth-sidebar">
        <div class="sidebar-brand">
            <span class="logo-text"><i class="fa-solid fa-graduation-cap"></i> SkillBridge</span>
        </div>
        
        <div class="sidebar-content">
            <h1>Empower Your<br>Career Journey</h1>
            <p>Connect your organization with skilled students, collaborate on academic projects, and create opportunities for future talent.</p>
        </div>
        
        <div class="sidebar-footer">
            <div class="avatar-group">
                <img src="assets/images/user1.jpg" alt="User" class="avatar" onerror="this.src='https://via.placeholder.com/38'">
                <img src="assets/images/user2.jpg" alt="User" class="avatar" onerror="this.src='https://via.placeholder.com/38'">
                <img src="assets/images/user3.jpg" alt="User" class="avatar" onerror="this.src='https://via.placeholder.com/38'">
                <div class="avatar-count">+5k</div>
            </div>
            <span>Join 5,000+ active users</span>
        </div>
    </div>

    <!-- Right Form Section -->
    <div class="auth-main">
        <div class="form-header">
            <h2>Create Company Account</h2>
            <p>Join SkillBridge to discover talented students and provide internship opportunities.</p>
        </div>

        <!-- Error Messages Display -->
        <?php if (!empty($errors)): ?>
        <div class="error-container">
            <div class="error-header">
                <i class="fa-solid fa-circle-exclamation"></i> Please fix the following errors:
            </div>
            <ul class="error-list">
                <?php foreach ($errors as $error): ?>
                <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

        <!-- Account Type Selector -->
        <div class="account-type-toggle">
            <a href="student_register.php" class="toggle-btn"><i class="fa-solid fa-user-graduate"></i> Student</a>
            <a href="organization_register.php" class="toggle-btn"><i class="fa-solid fa-building-columns"></i> Organization</a>
            <a href="#" class="toggle-btn active"><i class="fa-solid fa-building"></i> Company</a>
        </div>

        <!-- Registration Form -->
        <form action="process_company_register.php" method="POST" id="companyRegisterForm" class="registration-form">
            
            <div class="form-group">
                <label for="company_name">Company Name</label>
                <input type="text" id="company_name" name="company_name" placeholder="ABC Technologies Pvt Ltd" required>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="business_email">Business Email</label>
                    <input type="email" id="business_email" name="business_email" placeholder="hr@company.com" required>
                </div>
                <div class="form-group">
                    <label for="industry_sector">Industry Sector</label>
                    <select id="industry_sector" name="industry_sector" required>
                        <option value="" disabled selected>Select Sector</option>
                        <option value="IT & Software">IT & Software</option>
                        <option value="Finance & Banking">Finance & Banking</option>
                        <option value="Healthcare">Healthcare</option>
                        <option value="Education">Education</option>
                        <option value="Engineering">Engineering</option>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="contact_person">Contact Person Name</label>
                    <input type="text" id="contact_person" name="contact_person" placeholder="e.g. John Smith" required>
                </div>
                <div class="form-group">
                    <label for="contact_number">Contact Number</label>
                    <input type="tel" id="contact_number" name="contact_number" placeholder="+94 77 123 4567" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="website">Website</label>
                    <input type="url" id="website" name="website" placeholder="www.company.com">
                </div>
                <div class="form-group">
                    <label for="location">Location</label>
                    <input type="text" id="location" name="location" placeholder="Colombo, Sri Lanka" required>
                </div>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <div class="password-input-container">
                    <i class="fa-solid fa-lock input-icon-left"></i>
                    <input type="password" id="password" name="password" placeholder="••••••••" required>
                    <i class="fa-solid fa-eye toggle-password" data-target="password"></i>
                </div>
            </div>

            <div class="form-group">
                <label for="confirmPassword">Confirm Password</label>
                <div class="password-input-container">
                    <i class="fa-solid fa-lock input-icon-left"></i>
                    <input type="password" id="confirmPassword" name="confirmPassword" placeholder="••••••••" required>
                    <i class="fa-solid fa-eye toggle-password" data-target="confirmPassword"></i>
                </div>
            </div>

            <div class="checkbox-group">
                <input type="checkbox" id="terms" name="terms" required>
                <label for="terms">I agree to the <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a>.</label>
            </div>

            <button type="submit" class="btn-submit">Create Company Account</button>

            <div class="form-footer">
                <p>Already have an account? <a href="login.php">Sign In</a></p>
            </div>
        </form>
    </div>
</div>

<!-- JS File -->
<script src="company_register.js"></script>
</body>
</html>