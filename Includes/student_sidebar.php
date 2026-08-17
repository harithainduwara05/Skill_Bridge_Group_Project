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

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet">
<link rel="stylesheet" href="../../../Assets/CSS/sider.css">
</head>

<body>

<aside class="sidebar">

    <div class="logo">
        <img src="../../../Assets/Images/SkillBridge.png" alt="SkillBridge">
    </div>


    <nav>


        <a href="../../../Functions/Dashboards/Student/dashboard.php" 
        class="<?= $currentPage == 'dashboard.php' ? 'active' : '' ?>">

            <span class="icon">
                <img src="../../../Assets/Images/Icons/dashboard.svg">
            </span>

            Dashboard

        </a>

        <a href="../../../Functions/Dashboards/Student/profile.php"
        class="<?= $currentPage == 'profile.php' ? 'active' : '' ?>">

            <span class="icon">
                <img src="../../../Assets/Images/Icons/profile.png">
            </span>

            Profile

        </a>

        <a href="../../../Functions/Dashboards/Student/skills.php"
        class="<?= $currentPage == 'skills.php' ? 'active' : '' ?>">

            <span class="icon">
                <img src="../../../Assets/Images/Icons/skills.png">
            </span>
            Skills
        </a>



        <a href="../../../Functions/Dashboards/Student/certificates.php"
        class="<?= $currentPage == 'certificates.php' ? 'active' : '' ?>">

            <span class="icon">
                <img src="../../../Assets/Images/Icons/certificates.png">
            </span>

            Certificates

        </a>



        <a href="../../../Functions/Dashboards/Student/portfolio.php"
        class="<?= $currentPage == 'portfolio.php' ? 'active' : '' ?>">

            <span class="icon">
                <img src="../../../Assets/Images/Icons/portfolio.png">
            </span>

            Portfolio

        </a>

        <a href="../../../Functions/Dashboards/Student/projects.php"
        class="<?= $currentPage == 'projects.php' ? 'active' : '' ?>">

            <span class="icon">
                <img src="../../../Assets/Images/Icons/projects.svg">
            </span>
            Projects
        </a>


        <a href="../../../Functions/Dashboards/Student/teams.php"
        class="<?= $currentPage == 'teams.php' ? 'active' : '' ?>">

            <span class="icon">
                <img src="../../../Assets/Images/Icons/teams.png">
            </span>
            Teams
        </a>
        
        <?php if($email && canApplyInternship($email)){ ?>

        <a href="../../../Functions/Dashboards/Student/internships.php"
        class="<?= $currentPage == 'internships.php' ? 'active' : '' ?>">

            <span class="icon">
                <img src="../../../Assets/Images/Icons/internship.png">
            </span>

            Internships

        </a>

        <?php } ?>

        <a href="../../../Functions/Dashboards/Student/notifications.php"
        class="<?= $currentPage == 'notifications.php' ? 'active' : '' ?>">

            <span class="icon">
                <img src="../../../Assets/Images/Icons/notification.png">
            </span>
            Notifications

        </a>



        <a href="../../../Functions/Dashboards/Student/settings.php"
        class="<?= $currentPage == 'settings.php' ? 'active' : '' ?>">

            <span class="icon">
                <img src="../../../Assets/Images/Icons/settings.png">
            </span>
            Settings
        </a>
    </nav>


    <button class="logout" onclick="window.location.href='../../../Session/Logout.php'">
        ⇥ Logout
    </button>

</aside>
