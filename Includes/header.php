<?php
// The header is shared by the project root and pages one directory below it.
// Keep every public URL relative to the currently requested page.
$project_root = dirname(__DIR__);
$request_directory = isset($_SERVER['SCRIPT_FILENAME'])
    ? realpath(dirname($_SERVER['SCRIPT_FILENAME']))
    : false;
$landing_directory = realpath($project_root . DIRECTORY_SEPARATOR . 'landingPage content');
$base_url = ($request_directory && $request_directory === $landing_directory) ? '../' : '';
?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>SkillBridge</title>

    <!-- Main CSS -->
    <link
        rel="stylesheet"
        href="<?php echo $base_url; ?>Assets/CSS/style.css"
    >

    <!-- Landing Page CSS -->
    <link
        rel="stylesheet"
        href="<?php echo $base_url; ?>Assets/CSS/landing.css"
    >

    <?php if (isset($page_css) && $page_css !== ''): ?>
    <link rel="stylesheet" href="<?php echo $base_url . htmlspecialchars($page_css, ENT_QUOTES, 'UTF-8'); ?>">
    <?php endif; ?>

    <!-- Material Symbols -->
    <link
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0"
        rel="stylesheet"
    >

</head>

<body>


<header class="site-header">

    <nav class="navbar">


        <!-- =========================
             LOGO
        ========================== -->

        <a
            href="<?php echo $base_url; ?>index.php"
            class="brand-logo"
        >

            <img
                src="<?php echo $base_url; ?>Assets/Images/logo.png"
                alt="SkillBridge Logo"
            >

        </a>



        <!-- =========================
             NAVIGATION LINKS
        ========================== -->

        <ul class="nav-links">


            <li>

                <a href="<?php echo $base_url; ?>index.php">
                    Home
                </a>

            </li>



            <li>

                <a href="<?php echo $base_url; ?>index.php#about">
                    About
                </a>

            </li>



            <li>

                <a href="<?php echo $base_url; ?>index.php#projects">
                    Projects
                </a>

            </li>



            <li>

                <a href="<?php echo $base_url; ?>index.php#internships">
                    Internships
                </a>

            </li>



            <li>

                <a href="<?php echo $base_url; ?>landingPage%20content/contact.php">
                    Contact Us
                </a>

            </li>


        </ul>



        <!-- =========================
             LOGIN / SIGN UP
        ========================== -->

        <div class="nav-actions">


            <a
                href="<?php echo $base_url; ?>Auth/login.php"
                class="login-link"
            >
                Login
            </a>


            <a
                href="<?php echo $base_url; ?>register.php"
                class="signup-link"
            >
                Sign Up
            </a>


        </div>



        <!-- =========================
             MOBILE MENU BUTTON
        ========================== -->

        <button
            id="menuToggle"
            class="menu-toggle"
            type="button"
            aria-label="Toggle navigation"
        >

            <span class="material-symbols-outlined">
                menu
            </span>

        </button>


    </nav>

</header>
