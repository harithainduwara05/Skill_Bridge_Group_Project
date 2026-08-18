<?php
$currentPage = basename($_SERVER['PHP_SELF']);
require_once __DIR__ . "/../Session/Session.php";
?>
<!DOCTYPE html>

<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Company Dashboard | SkillBridge</title>

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
            <a href="dashboard.php" class="<?= $currentPage == 'dashboard.php' ? 'active' : '' ?>">
                <span class="icon">
                    <span class="material-symbols-outlined">dashboard</span>
                </span>
                Dashboard
            </a>

            <a href="company.php" class="<?= $currentPage == 'company.php' ? 'active' : '' ?>">
                <span class="icon">
                    <span class="material-symbols-outlined">business</span>
                </span>
                Company Profile
            </a>

            <a href="internships.php" class="<?= $currentPage == 'internships.php' ? 'active' : '' ?>">
                <span class="icon">
                    <span class="material-symbols-outlined">work</span>
                </span>
                Internships
            </a>

            <a href="applications.php" class="<?= $currentPage == 'applications.php' ? 'active' : '' ?>">
                <span class="icon">
                    <span class="material-symbols-outlined">description</span>
                </span>
                Applications
                <span class="badge">12</span>
            </a>

            <a href="candidates.php" class="<?= $currentPage == 'candidates.php' ? 'active' : '' ?>">
                <span class="icon">
                    <span class="material-symbols-outlined">people</span>
                </span>
                Candidates
            </a>

            <a href="interviews.php" class="<?= $currentPage == 'interviews.php' ? 'active' : '' ?>">
                <span class="icon">
                    <span class="material-symbols-outlined">event_note</span>
                </span>
                Interviews
            </a>

            <a href="reports.php" class="<?= $currentPage == 'reports.php' ? 'active' : '' ?>">
                <span class="icon">
                    <span class="material-symbols-outlined">bar_chart</span>
                </span>
                Reports & Analytics
            </a>

            <a href="notifications.php" class="<?= $currentPage == 'notifications.php' ? 'active' : '' ?>">
                <span class="icon">
                    <span class="material-symbols-outlined">notifications</span>
                </span>
                Notifications
            </a>

            <a href="settings.php" class="<?= $currentPage == 'settings.php' ? 'active' : '' ?>">
                <span class="icon">
                    <span class="material-symbols-outlined">settings</span>
                </span>
                Settings
            </a>
        </nav>

        <button class="post-internship-btn" onclick="window.location.href='internships.php'">
            <span class="material-symbols-outlined">add</span>
            Post Internship
        </button>

        <button class="logout" onclick="window.location.href='../../../Session/Logout.php'">
            <span class="material-symbols-outlined">logout</span>
            Logout
        </button>
    </aside>
