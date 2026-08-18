<?php
require_once __DIR__ . '/Session/Session.php';
require_once __DIR__ . '/Config/db.php';

// Ensure user actually needs to verify or has just verified
$emailToVerify = $_SESSION['verify_email'] ?? null;
if (!$emailToVerify && !isset($_SESSION['verified'])) {
    header('Location: register.php');
    exit;
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['code'])) {
    $code = trim($_POST['code']);

    if (empty($code)) {
        $error = "Please enter the verification code.";
    } else {
        // Check code
        $stmt = $conn->prepare("SELECT verification_code FROM User WHERE Email = ?");
        $stmt->bind_param("s", $emailToVerify);
        $stmt->execute();
        $stmt->bind_result($dbCode);
        $stmt->fetch();
        $stmt->close();

        if ($dbCode && $dbCode === $code) {
            // Update user to active
            $updateStmt = $conn->prepare("UPDATE User SET status = 'Active', verification_code = NULL WHERE Email = ?");
            $updateStmt->bind_param("s", $emailToVerify);
            if ($updateStmt->execute()) {
                $_SESSION['verified'] = true;
                unset($_SESSION['verify_email']); // cleanup
                $roleTable = $_SESSION['userData']['role'];
                if (strtolower($roleTable) === 'student') {
                    $updateUserData = $conn->prepare("Insert into student(Email,name,University,degree,Year)VALUES(?,?,?,?,?)");
                    $updateUserData->bind_param("sssss", $emailToVerify, $_SESSION['userData']['name'], $_SESSION['userData']['university'], $_SESSION['userData']['degree'], $_SESSION['userData']['academicYear']);
                    $updateUserData->execute();
                    unset($_SESSION['userData']);
                } else if (strtolower($roleTable) === 'organization') {
                    $updateUserData = $conn->prepare("INSERT INTO organization(Email,Name,orgtype,contactPersonName,contactNumber,website,location) VALUES (?,?,?,?,?,?,?)");
                    $updateUserData->bind_param("sssssss", $emailToVerify, $_SESSION['userData']['name'], $_SESSION['userData']['type'], $_SESSION['userData']['contactPersonName'], $_SESSION['userData']['contactNumber'], $_SESSION['userData']['website'], $_SESSION['userData']['location']);
                    $updateUserData->execute();
                    unset($_SESSION['userData']);
                } else if (strtolower($roleTable) === 'company') {
                    $updateUserData = $conn->prepare("INSERT INTO company(Email,companyName,companytype,contactPersonName,contactNumber,website,location) VALUES (?,?,?,?,?,?,?)");
                    $updateUserData->bind_param("sssssss", $emailToVerify, $_SESSION['userData']['name'], $_SESSION['userData']['type'], $_SESSION['userData']['contactPersonName'], $_SESSION['userData']['contactNumber'], $_SESSION['userData']['website'], $_SESSION['userData']['location']);
                    $updateUserData->execute();
                    unset($_SESSION['userData']);
                }

            } else {
                $error = "Something went wrong. Please try again.";
            }
            $updateStmt->close();
            $updateUserData->close();
        } else {
            $error = "Invalid or expired verification code.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SkillBridge - Verify Email</title>
    <link rel="stylesheet" href="Assets/CSS/verify-style.css">
</head>

<body>
    <div class="verify-wrapper">
        <div class="verify-card">
            <?php if (isset($_SESSION['verified']) && $_SESSION['verified']): ?>
                <!-- Success State -->
                <div class="success-icon-container">
                    <div class="circle-outer">
                        <div class="circle-inner">
                            <div class="solid-circle">
                                <svg class="check-icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M5 13l4 4L19 7" stroke="currentColor" stroke-width="3" stroke-linecap="round"
                                        stroke-linejoin="round" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <h1 class="verify-title">Email Verified!</h1>
                <p class="verify-subtitle">Your account is now fully active. Welcome to the<br>SkillBridge academic
                    workspace.</p>

                <a href="Auth/login.php" class="btn-primary">Go to Login Page</a>
                <?php unset($_SESSION['verified']); ?>
            <?php else: ?>
                <!-- Verification Form State -->
                <div class="form-header">
                    <h1 class="verify-title">Verify your email</h1>
                    <p class="verify-subtitle">We've sent a 6-digit code to
                        <strong><?= htmlspecialchars($emailToVerify) ?></strong>
                    </p>
                </div>

                <?php if ($error): ?>
                    <div class="error-msg"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="form-group">
                        <input type="text" name="code" class="code-input" placeholder="000000" maxlength="6" required>
                    </div>
                    <button type="submit" class="btn-primary">Verify Account</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>