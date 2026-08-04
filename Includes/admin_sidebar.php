<?php
$currentPage = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Dashboard | SkillBridge</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet">
    <link rel="stylesheet" href="../../../Assets/CSS/dashboard.css">
</head>
<body>

<aside class="sidebar">

    <div class="logo">
        <img src="../../../Assets/Images/logo.png" alt="SkillBridge">
    </div>

    <nav>

        <a href="dashboard.php"
        class="<?= $currentPage == 'dashboard.php' ? 'active' : '' ?>">
            <span class="icon">
                <span class="material-symbols-outlined">dashboard</span>
            </span>
            Dashboard
        </a>

        <a href="users.php"
        class="<?= $currentPage == 'users.php' ? 'active' : '' ?>">
            <span class="icon">
                <span class="material-symbols-outlined">group</span>
            </span>
            Users
        </a>

        <a href="universities.php"
        class="<?= $currentPage == 'universities.php' ? 'active' : '' ?>">
            <span class="icon">
                <span class="material-symbols-outlined">school</span>
            </span>
            Universities
        </a>

        <a href="projects.php"
        class="<?= $currentPage == 'projects.php' ? 'active' : '' ?>">
            <span class="icon">
                <span class="material-symbols-outlined">folder_open</span>
            </span>
            Projects
        </a>

        <a href="internships.php"
        class="<?= $currentPage == 'internships.php' ? 'active' : '' ?>">
            <span class="icon">
                <span class="material-symbols-outlined">work</span>
            </span>
            Internships
        </a>

        <a href="complaints.php"
        class="<?= $currentPage == 'complaints.php' ? 'active' : '' ?>">
            <span class="icon">
                <span class="material-symbols-outlined">report_problem</span>
            </span>
            Complaints
            <span class="badge">12</span>
        </a>

        <a href="reports.php"
        class="<?= $currentPage == 'reports.php' ? 'active' : '' ?>">
            <span class="icon">
                <span class="material-symbols-outlined">bar_chart</span>
            </span>
            Reports & Analytics
        </a>

        <a href="notifications.php"
        class="<?= $currentPage == 'notifications.php' ? 'active' : '' ?>">
            <span class="icon">
                <span class="material-symbols-outlined">notifications</span>
            </span>
            Notifications
        </a>

        <a href="settings.php"
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
