<?php
// Includes/header.php
require_once __DIR__ . '/../Session/Sessionn.php';
$base_url = '/Skill_Bridge_Group_Project/';
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>SkillBridge</title>

    <link rel="stylesheet" href="<?php echo $base_url; ?>Assets/CSS/landing.css">
    <?php if(isset($page_css)): ?>
    <link rel="stylesheet" href="<?php echo $base_url . htmlspecialchars($page_css); ?>">
    <?php endif; ?>

</head>

<body>

<header class="site-header">

    <nav class="navbar">

        <!-- Logo -->
        <a href="<?php echo $base_url; ?>index.php" class="brand-logo">
            <img src="<?php echo $base_url; ?>Assets/Images/logo.png" alt="SkillBridge Logo">
        </a>


        <!-- Navigation -->
        <ul class="nav-links">

            <li>
                <a href="<?php echo $base_url; ?>index.php" <?php if(basename($_SERVER['PHP_SELF']) == 'index.php') echo 'class="active"'; ?>>Home</a>
            </li>

            <li>
                <a href="<?php echo $base_url; ?>landingPage content/about.php" <?php if(basename($_SERVER['PHP_SELF']) == 'about.php') echo 'class="active"'; ?>>About</a>
            </li>

            <li>
                <a href="<?php echo $base_url; ?>landingPage content/projects.php" <?php if(basename($_SERVER['PHP_SELF']) == 'projects.php') echo 'class="active"'; ?>>Projects</a>
            </li>

            <li>
                <a href="<?php echo $base_url; ?>landingPage content/internships.php" <?php if(basename($_SERVER['PHP_SELF']) == 'internships.php') echo 'class="active"'; ?>>Internships</a>
            </li>

            <li>
                <a href="<?php echo $base_url; ?>landingPage content/contact.php" <?php if(basename($_SERVER['PHP_SELF']) == 'contact.php') echo 'class="active"'; ?>>Contact Us</a>
            </li>

        </ul>


        <!-- Navigation Buttons -->
        <div class="nav-actions">

            <a href="<?php echo $base_url; ?>Auth/login.php" class="login-link">
                Login
            </a>

            <a href="<?php echo $base_url; ?>register.php" class="signup-link">
                Sign Up
            </a>

        </div>


        <!-- Mobile Menu Button -->
        <button class="menu-toggle" id="menuToggle">
            ☰
        </button>

    </nav>

</header>
