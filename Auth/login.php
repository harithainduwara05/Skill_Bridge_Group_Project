<?php
require_once "../Config/db.php";
require_once "../Session/Session.php";
require_once "../Config/env_loader.php";
require_once "../Includes/simple_smtp.php";

$emailError = "";
$passwordError = "";


// Forgot Password AJAX / API Handler
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['fp_action'])) {
    header('Content-Type: application/json');
    $action = $_POST['fp_action'];

    // 1. Send OTP to user email
    if ($action === 'send_otp') {
        $fpEmail = trim($_POST['fpEmail'] ?? '');
        if (empty($fpEmail)) {
            echo json_encode(['status' => 'error', 'message' => 'Please enter your email address.']);
            exit();
        }

        try {
            $sql = "SELECT COUNT(*) FROM user WHERE Email=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $fpEmail);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_row()[0];

            if ($result >= 1) {
                $otp = random_int(100000, 999999);
                $sql = "UPDATE user SET verification_code=? WHERE Email=?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("is", $otp, $fpEmail);
                $stmt->execute();

                $_SESSION['fp_email'] = $fpEmail;
                $_SESSION['fp_otp'] = (string) $otp;

                try {
                    send_verification_email($fpEmail, $otp);
                    echo json_encode(['status' => 'success', 'message' => 'Verification code sent to your email.']);
                } catch (Exception $mailEx) {
                    echo json_encode(['status' => 'error', 'message' => 'Failed to send email: ' . $mailEx->getMessage()]);
                }
            } else {
                echo json_encode(['status' => 'error', 'message' => 'No account found with this email address.']);
            }
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
        }
        exit();
    }

    // 2. Verify 6-digit OTP
    if ($action === 'verify_otp') {
        $enteredOtp = trim($_POST['otp'] ?? '');
        $email = $_SESSION['fp_email'] ?? '';

        if (empty($enteredOtp)) {
            echo json_encode(['status' => 'error', 'message' => 'Please enter the verification code.']);
            exit();
        }

        $sessionOtp = (string) ($_SESSION['fp_otp'] ?? '');
        $isMatch = false;

        if (!empty($sessionOtp) && $sessionOtp === $enteredOtp) {
            $isMatch = true;
        } elseif (!empty($email)) {
            $sql = "SELECT verification_code FROM user WHERE Email=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $res = $stmt->get_result()->fetch_assoc();
            if ($res && (string) $res['verification_code'] === $enteredOtp) {
                $isMatch = true;
            }
        }

        if ($isMatch) {
            $_SESSION['fp_verified'] = true;
            echo json_encode(['status' => 'success', 'message' => 'OTP verified successfully.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Invalid verification code. Please check your email and try again.']);
        }
        exit();
    }

    // 3. Reset Password
    if ($action === 'reset_password') {
        $newPassword = $_POST['newPassword'] ?? '';
        $confirmPassword = $_POST['confirmPassword'] ?? '';
        $email = $_SESSION['fp_email'] ?? '';
        $verified = $_SESSION['fp_verified'] ?? false;

        if (!$verified || empty($email)) {
            echo json_encode(['status' => 'error', 'message' => 'Session expired or unauthorized. Please verify OTP first.']);
            exit();
        }

        if (empty($newPassword)) {
            echo json_encode(['status' => 'error', 'message' => 'Please enter a new password.']);
            exit();
        }

        // Password policy: At least 8 characters, with uppercase, lowercase, number, and special character
        if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^a-zA-Z0-9]).{8,}$/', $newPassword)) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Password must be at least 8 characters long and contain uppercase, lowercase letters, numbers, and a special character.'
            ]);
            exit();
        }

        if ($newPassword !== $confirmPassword) {
            echo json_encode(['status' => 'error', 'message' => 'Passwords do not match.']);
            exit();
        }

        try {
            $newHashedPass = sha1($newPassword);
            $sql = "UPDATE user SET password=?, verification_code=NULL WHERE Email=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ss", $newHashedPass, $email);
            $stmt->execute();

            // Clear session state
            unset($_SESSION['fp_email'], $_SESSION['fp_otp'], $_SESSION['fp_verified']);

            echo json_encode(['status' => 'success', 'message' => 'Password reset successfully!']);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
        }
        exit();
    }
}

// ============================================================================
// 2. Normal Login Form Handler
// ============================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['fp_action'])) {
    if (!empty($_POST["email"]) && !empty($_POST["password"])) {
        $email = $_POST["email"];
        $password = $_POST["password"];

        $hashPassword = sha1($password);

        try {
            $sql = "SELECT Email, role, password FROM User WHERE Email=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();
                $sql = "SELECT Name FROM {$user['role']} WHERE Email=?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $forignResult = $stmt->get_result();
                $forignUser = $forignResult ? $forignResult->fetch_assoc() : null;
                if ($user['password'] === $hashPassword) {
                    $fetchedUsername = $forignUser['Name'] ?? $forignUser['name'] ?? 'User';
                    $_SESSION['user'] = [
                        'username' => $fetchedUsername,
                        'email' => $user['Email'],
                        'role' => $user['role'],
                    ];

                    switch ($user['role']) {
                        case 'admin':
                            header('Location:../Functions/Admin/dashboard.php');
                            exit();
                        case 'organization':
                            header('Location:../Functions/Organization/dashboard.php');
                            exit();
                        case 'company':
                            header('Location:../Functions/Dashboards/Company/dashboard.php');
                            exit();
                        case 'student':
                            header('Location:../Functions/Dashboards/Student/dashboard.php');
                            exit();
                        default:
                            $emailError = "Cannot find role";
                    }
                } else {
                    $passwordError = "Invalid password!";
                }
            } else {
                $emailError = "Invalid user!";
            }
        } catch (Exception $e) {
            $emailError = "Error: " . $e->getMessage();
        }
    } else {
        if (empty($_POST["email"])) {
            $emailError = "Email is required!";
        }
        if (empty($_POST["password"])) {
            $passwordError = "Password is required!";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@500;600;700;800&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../Assets/CSS/login.css?v=<?= time() ?>">
</head>

<body>
    <div class="login-wrapper">
        <div class="login-container">
            <!-- Left Side: Form -->
            <div class="login-left">
                <a href="../index.php" class="logo-container" title="Back to SkillBridge Home" style="text-decoration:none;">
                    <img src="../Assets/Images/logoLog.png" alt="SkillBridge Logo" class="logo-icon">
                    <span class="logo-text">Skill</span>
                </a>

                <h1 class="welcome-title">Welcome Back</h1>
                <p class="welcome-subtitle">Bridge the gap between learning and career success.</p>

                <form id="loginForm" action="" method="POST">
                    <div class="form-group">
                        <label for="email">Email Address</label>

                        <?php if (!empty($emailError)): ?>
                            <span
                                style="color: #ff3333; font-size: 13px; display: block; margin-bottom: 8px; font-weight: 500;"><?php echo $emailError; ?></span>
                        <?php endif; ?>

                        <div class="input-wrapper">
                            <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z">
                                </path>
                                <polyline points="22,6 12,13 2,6"></polyline>
                            </svg>
                            <input type="email" id="email" name="email" placeholder="john@university.edu" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>

                        <?php if (!empty($passwordError)): ?>
                            <span
                                style="color: #ff3333; font-size: 13px; display: block; margin-bottom: 8px; font-weight: 500;"><?php echo $passwordError; ?></span>
                        <?php endif; ?>

                        <div class="input-wrapper">
                            <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                            </svg>
                            <input type="password" id="password" name="password" placeholder="••••••••" required>
                            <button type="button" class="toggle-password" id="togglePassword">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round" class="eye-icon">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div class="form-options">
                        <label class="remember-me">
                            <input type="checkbox" name="remember">
                            <span>Remember me</span>
                        </label>
                        <a href="javascript:void(0);" id="forgotPasswordBtn" class="forgot-password">Forgot Password?</a>
                    </div>

                    <button type="submit" class="submit-btn">
                        Login to Dashboard
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round">
                            <line x1="5" y1="12" x2="19" y2="12"></line>
                            <polyline points="12 5 19 12 12 19"></polyline>
                        </svg>
                    </button>

                    <div class="register-link">
                        Don't have an account? <a href="../register.php">Create Account</a>
                    </div>
                </form>
            </div>

            <!-- Right Side: Info -->
            <div class="login-right">
                <div class="hero-image-container">
                    <img src="../Assets/Images/login-hero.jpg" alt="Students collaborating" class="hero-image">
                </div>

                <h2 class="right-title">Master the Skills That<br>Industry Demands</h2>
                <p class="right-subtitle">Bridge the gap between academic learning and real-<br>world industry experience with certified skill projects,<br>mentorship, and career pathways.</p>

                <div class="right-features">
                    <div class="features-row">
                        <span class="feature-tag">
                            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="#f58220" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 10v6M2 10l10-5 10 5-10 5z"></path><path d="M6 12v5c3 3 9 3 12 0v-5"></path></svg>
                            Verified Academia
                        </span>
                        <span class="feature-tag">
                            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="#38bdf8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path></svg>
                            Real Projects
                        </span>
                    </div>
                    <div class="features-row">
                        <span class="feature-tag">
                            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="#facc15" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                            Certified Skills
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Forgot Password Multi-Step Modal -->
    <div id="forgotPasswordModal" class="fp-modal-overlay" aria-hidden="true">
        <div class="fp-modal-card" role="dialog" aria-modal="true" aria-labelledby="fpModalTitle">
            <button type="button" class="fp-close-btn" id="fpCloseBtn" aria-label="Close modal">&times;</button>

            <!-- Step Progress Indicator -->
            <div class="fp-step-indicator">
                <div class="fp-step-dot active" id="dotStep1" title="Email"></div>
                <div class="fp-step-line" id="lineStep1"></div>
                <div class="fp-step-dot" id="dotStep2" title="OTP Verification"></div>
                <div class="fp-step-line" id="lineStep2"></div>
                <div class="fp-step-dot" id="dotStep3" title="New Password"></div>
            </div>

            <!-- STEP 1: Email Address -->
            <div class="fp-step-view active" id="fpStepEmail">
                <div class="fp-header-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                        <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                    </svg>
                </div>
                <h3 class="fp-title" id="fpModalTitle">Reset or Setup Password</h3>
                <p class="fp-subtitle">Enter your registered email address to receive a 6-digit verification code to set your password.</p>

                <form id="fpEmailForm" action="" method="POST">
                    <div class="form-group" style="text-align: left;">
                        <label for="fpEmail">Email Address</label>
                        <div class="input-wrapper">
                            <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z">
                                </path>
                                <polyline points="22,6 12,13 2,6"></polyline>
                            </svg>
                            <input name="fpEmail" type="email" id="fpEmail" placeholder="e.g. 20xxisxx@stu.xxx.xx.lk"
                                required autocomplete="email">
                        </div>
                        <span class="fp-error-msg" id="fpEmailError"><?php echo $fpemailError; ?></span>
                    </div>

                    <button type="button" class="submit-btn fp-action-btn" id="fpSendOtpBtn">
                        Continue
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round">
                            <line x1="5" y1="12" x2="19" y2="12"></line>
                            <polyline points="12 5 19 12 12 19"></polyline>
                        </svg>
                    </button>

                    <div class="fp-footer-link">
                        Remember your password? <a href="javascript:void(0);" id="fpBackToLogin1">Back to Login</a>
                    </div>
                </form>
            </div>

            <!-- STEP 2: 6-Digit OTP Verification -->
            <div class="fp-step-view" id="fpStepOtp">
                <div class="fp-header-icon fp-otp-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                    </svg>
                </div>
                <h3 class="fp-title">Enter Verification Code</h3>
                <p class="fp-subtitle">
                    We've sent a 6-digit code to <strong id="fpDisplayEmail">your email</strong>
                    <button type="button" class="fp-text-btn" id="fpChangeEmailBtn" title="Change email">Change</button>
                </p>

                <form id="fpOtpForm" method="POST" action="">
                    <!-- Hidden input to send full 6-digit OTP to backend via $_POST['otp'] -->
                    <input type="hidden" name="otp" id="finalOtp" value="">
                    <!--<input type="hidden" name="email" id="fpOtpEmail" value="">-->

                    <!-- 6 Separate OTP Boxes -->
                    <div class="otp-inputs-wrapper" id="otpContainer">
                        <input type="text" class="otp-box" maxlength="1" inputmode="numeric" pattern="[0-9]*"
                            data-index="0" autocomplete="off">
                        <input type="text" class="otp-box" maxlength="1" inputmode="numeric" pattern="[0-9]*"
                            data-index="1" autocomplete="off">
                        <input type="text" class="otp-box" maxlength="1" inputmode="numeric" pattern="[0-9]*"
                            data-index="2" autocomplete="off">
                        <input type="text" class="otp-box" maxlength="1" inputmode="numeric" pattern="[0-9]*"
                            data-index="3" autocomplete="off">
                        <input type="text" class="otp-box" maxlength="1" inputmode="numeric" pattern="[0-9]*"
                            data-index="4" autocomplete="off">
                        <input type="text" class="otp-box" maxlength="1" inputmode="numeric" pattern="[0-9]*"
                            data-index="5" autocomplete="off">
                    </div>
                    <span class="fp-error-msg" id="fpOtpError"></span>

                    <button type="button" class="submit-btn fp-action-btn" id="fpVerifyOtpBtn">
                        Verify Code
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                    </button>

                    <div class="fp-resend-wrapper">
                        Didn't receive code? <button type="button" class="fp-text-btn" id="fpResendBtn">Resend
                            Code</button>
                        <span id="fpResendTimer" class="fp-timer-text"></span>
                    </div>
                </form>
            </div>

            <!-- STEP 3: Enter New Password & Re-enter Password -->
            <div class="fp-step-view" id="fpStepPassword">
                <!-- Verified Email Badge at Top -->
                <div class="fp-verified-email-badge">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                        <polyline points="22 4 12 14.01 9 11.01"></polyline>
                    </svg>
                    <div class="fp-badge-text">
                        <span class="fp-badge-label">Resetting password for</span>
                        <strong id="fpVerifiedEmailDisplay">user@email.com</strong>
                    </div>
                </div>

                <h3 class="fp-title">Set New Password</h3>
                <p class="fp-subtitle">Create a strong password that you haven't used before.</p>

                <form id="fpPasswordForm" action="" method="POST">
                    <div class="form-group" style="text-align: left;">
                        <label for="fpNewPassword">New Password</label>
                        <div class="input-wrapper">
                            <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                            </svg>
                            <input name="newPassword" type="password" id="fpNewPassword"
                                placeholder="Minimum 6 characters" required>
                            <button type="button" class="toggle-password" id="toggleFpNewPassword" tabindex="-1">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round" class="eye-icon">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div class="form-group" style="text-align: left;">
                        <label for="fpConfirmPassword">Re-enter New Password</label>
                        <div class="input-wrapper">
                            <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                            </svg>
                            <input type="password" id="fpConfirmPassword" placeholder="Re-enter your password" required>
                            <button type="button" class="toggle-password" id="toggleFpConfirmPassword" tabindex="-1">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round" class="eye-icon">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                            </button>
                        </div>
                        <span class="fp-error-msg" id="fpPasswordError"></span>
                    </div>

                    <button type="button" class="submit-btn fp-action-btn" id="fpSubmitPasswordBtn">
                        Reset Password
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round">
                            <line x1="5" y1="12" x2="19" y2="12"></line>
                            <polyline points="12 5 19 12 12 19"></polyline>
                        </svg>
                    </button>
                </form>
            </div>

            <!-- STEP 4: Success Message -->
            <div class="fp-step-view" id="fpStepSuccess">
                <div class="fp-header-icon fp-success-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                        stroke-linejoin="round">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                        <polyline points="22 4 12 14.01 9 11.01"></polyline>
                    </svg>
                </div>
                <h3 class="fp-title">Password Reset Successful!</h3>
                <p class="fp-subtitle">Your password has been reset successfully. You can now log in using your new
                    password.</p>

                <button type="button" class="submit-btn fp-action-btn" id="fpBackToLoginSuccess">
                    Back to Login
                </button>
            </div>
        </div>
    </div>

    <script src="../Assets/JS/login.js"></script>
</body>

</html>