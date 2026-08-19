<?php
$currentPage = basename($_SERVER['PHP_SELF']);
require_once __DIR__ . "/../Functions/Dashboards/Student/check_student_access.php";
?>
<!DOCTYPE html>

<html lang="en">

<head>
<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Student Dashboard | SkillBridge</title>

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet">
<link rel="stylesheet" href="../../../Assets/CSS/dashboard.css">
<?= $extra_css ?? '' ?>
</head>

<body>

<aside class="sidebar">

    <div class="logo">
        <img src="../../../Assets/Images/logo.png" alt="SkillBridge">
    </div>


    <nav>


        <a href="../../../Functions/Dashboards/Student/dashboard.php" 
        class="<?= $currentPage == 'dashboard.php' ? 'active' : '' ?>">

            <span class="icon">
                <span class="material-symbols-outlined">dashboard</span>
            </span>

            Dashboard

        </a>

        <a href="../../../Functions/Dashboards/Student/profile.php"
        class="<?= $currentPage == 'profile.php' ? 'active' : '' ?>">

            <span class="icon">
                <span class="material-symbols-outlined">person</span>
            </span>

            Profile

        </a>

        <a href="../../../Functions/Dashboards/Student/skills.php"
        class="<?= $currentPage == 'skills.php' ? 'active' : '' ?>">

            <span class="icon">
                <span class="material-symbols-outlined">lightbulb</span>
            </span>
            Skills
        </a>



        <a href="../../../Functions/Dashboards/Student/certificates.php"
        class="<?= $currentPage == 'certificates.php' ? 'active' : '' ?>">

            <span class="icon">
                <span class="material-symbols-outlined">workspace_premium</span>
            </span>

            Certificates

        </a>



        <a href="../../../Functions/Dashboards/Student/portfolio.php"
        class="<?= $currentPage == 'portfolio.php' ? 'active' : '' ?>">

            <span class="icon">
                <span class="material-symbols-outlined">folder_open</span>
            </span>

            Portfolio

        </a>

        <a href="../../../Functions/Dashboards/Student/projects.php"
        class="<?= $currentPage == 'projects.php' ? 'active' : '' ?>">

            <span class="icon">
                <span class="material-symbols-outlined">work</span>
            </span>
            Projects
        </a>


        <a href="../../../Functions/Dashboards/Student/teams.php"
        class="<?= $currentPage == 'teams.php' ? 'active' : '' ?>">

            <span class="icon">
                <span class="material-symbols-outlined">group</span>
            </span>
            Teams
        </a>
        
        <?php if(isset($email) && $email && canApplyInternship($email)){ ?>

        <a href="../../../Functions/Dashboards/Student/internships.php"
        class="<?= $currentPage == 'internships.php' ? 'active' : '' ?>">

            <span class="icon">
                <span class="material-symbols-outlined">assignment</span>
            </span>

            Internships

        </a>

        <?php } ?>

        <a href="../../../Functions/Dashboards/Student/notifications.php"
        class="<?= $currentPage == 'notifications.php' ? 'active' : '' ?>">

            <span class="icon">
                <span class="material-symbols-outlined">notifications</span>
            </span>
            Notifications

        </a>



        <a href="../../../Functions/Dashboards/Student/settings.php"
        class="<?= $currentPage == 'settings.php' ? 'active' : '' ?>">

            <span class="icon">
                <span class="material-symbols-outlined">settings</span>
            </span>
            Settings
        </a>
    </nav>


    <button class="logout" onclick="window.location.href='../../../Session/Logout.php'">
        <span class="material-symbols-outlined" style="font-size:18px;">logout</span>
        Logout
    </button>

</aside>

<div class="main-wrapper">
