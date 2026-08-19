<?php
session_start();
include "../Config/db.php";

$error = "";

if($_SERVER['REQUEST_METHOD'] == 'POST'){

    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if($result->num_rows == 1){

        $user = $result->fetch_assoc();

        if(password_verify($password, $user['password'])){

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role']    = $user['role'];
            $_SESSION['name']    = $user['name'];

            if($user['role'] == 'student'){
                header("Location: ../Functions/Dashboards/Student/dashboard.php");
            }else if($user['role'] == 'organization'){
                header("Location: ../Functions/Dashboards/Organization/dashboard.php");
            }else if($user['role'] == 'company'){
                header("Location: ../Functions/Dashboards/Company/dashboard.php");
            }else if($user['role'] == 'admin'){
                header("Location: ../Functions/Dashboards/Admin/dashboard.php");
            }
            exit();

        }else{
            $error = "Invalid Password!";
        }

    }else{
        $error = "User not found!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Skill Bridge - Login</title>
<link rel="stylesheet" href="../Assets/CSS/login.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>

<div class="login-wrapper">

  <!-- LEFT HERO SECTION -->
  <div class="login-left">
    <div class="brand">
        <h2>Skill Bridge</h2>
    </div>

    <div class="hero-content">
        <h1>Connect. Build.<br>Elevate Your Career.</h1>
        <p>
           Bridge the gap between academic knowledge and real-world experience. 
           Join thousands of students and companies creating the future.
        </p>
    </div>

    <div class="left-footer">
        © 2026 Skill Bridge. All rights reserved.
    </div>
  </div>

  <!-- RIGHT FORM SECTION -->
  <div class="login-right">

     <div class="form-container">

        <h2>Welcome Back</h2>
        <p class="subtitle">Enter your credentials to access your account</p>

        <?php if($error): ?>
           <div class="error-msg"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST">

            <div class="input-group">
                <label>Email Address</label>
                <div class="input-field">
                   <i class="fa-solid fa-envelope"></i>
                   <input type="email" name="email" placeholder="name@company.com" required>
                </div>
            </div>

            <div class="input-group">
                <label>Password</label>
                <div class="input-field">
                   <i class="fa-solid fa-lock"></i>
                   <input type="password" name="password" id="password" placeholder="••••••••" required>
                   <i class="fa-solid fa-eye toggle-password" id="togglePassword"></i>
                </div>
            </div>

            <div class="form-options">
                <label><input type="checkbox"> Remember me</label>
                <a href="#">Forgot password?</a>
            </div>

            <button type="submit" class="login-btn">Sign In</button>

        </form>

        <div class="register-link">
            Don't have an account? <a href="../register.php">Create an account</a>
        </div>

     </div>

  </div>

</div>

<script src="../Assets/JS/login.js"></script>
</body>
</html>