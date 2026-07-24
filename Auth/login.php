<?php
require_once "../Config/db.php";
require_once "../Session/Sessionn.php";

$emailError = "";
$passwordError = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_POST["email"]) && !empty($_POST["password"])) {
        $email = $_POST["email"];
        $password = $_POST["password"];

        $hashPassword = sha1($password);

        try {
            $sql = "SELECT fname,Email, role, password FROM User WHERE Email=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();
                $sql = "SELECT name from  {$user['role']} where Email=?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("s",$email);
                $stmt->execute();
                $forignResult = $stmt->get_result();
                $forignUser = $forignResult->fetch_assoc();
                if ($user['password'] === $hashPassword) {
                    $_SESSION['user'] = [
                        'username' => $forignUser['name'],
                        'email' => $user['Email'],
                        'role' => $user['role'],
                    ];

                    switch ($user['role']) {
                        case 'admin':
                            header('Location:../function/adminDashbord.php');
                            exit();
                        case 'organization':
                            header('Location:../function/organizationDashbord.php');
                            exit();
                        case 'company':
                            header('Location:../function/companyDashbord.php');
                            exit();
                        case 'student':
                            header('Location:../function/studentDashbord.php');
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SkillBridge - Login</title>
    <link rel="stylesheet" href="../Assets/CSS/login.css">
</head>

<body>
    <div class="login-wrapper">
        <div class="login-container">
            <!-- Left Side: Form -->
            <div class="login-left">
                <div class="logo-container">
                    <img src="../Assets/Images/logoLog.png" alt="SkillBridge Logo" class="logo-icon">
                    <span class="logo-text">Skill</span>
                </div>

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
                        <a href="#" class="forgot-password">Forgot Password?</a>
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

                <div class="demo-accounts">
                    <p class="demo-title">Demo Accounts:</p>
                    <ul class="demo-list">
                        <li><strong>Admin :</strong> admin@skillbridge.com / admin123</li>
                        <li><strong>Student :</strong> student1@uni.edu / student123</li>
                        <li><strong>Organization :</strong> contact@organization.edu / org123</li>
                        <li><strong>Company :</strong> hr@company.com / comp123</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <script src="../Assets/JS/login.js"></script>
</body>

</html>