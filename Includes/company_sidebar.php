<?php
$currentPage = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Company Dashboard | SkillBridge</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200"
        rel="stylesheet">
    <link rel="stylesheet" href="../../../Assets/CSS/dashboard.css">
    <?= $extra_css ?? '' ?> 
</head>

<body>

    <aside class="sidebar">

        <div class="logo">
            <img src="../../../Assets/Images/logo.png" alt="SkillBridge">
        </div>

        <nav>

            <a href="dashboard.php" class="<?= $currentPage == 'dashboard.php' ? 'active' : '' ?>" title="Dashboard">
                <span class="icon">
                    <span class="material-symbols-outlined">dashboard</span>
                </span>
                <span class="nav-text">Dashboard</span>
            </a>

            <a href="internships.php" class="<?= in_array($currentPage, ['internships.php', 'internships_Management.php']) ? 'active' : '' ?>" title="Internships">
                <span class="icon">
                    <span class="material-symbols-outlined">group</span>
                </span>
                <span class="nav-text">Internships</span>
            </a>

            <a href="university.php" class="<?= $currentPage == 'university.php' ? 'active' : '' ?>" title="Applications">
                <span class="icon">
                    <span class="material-symbols-outlined">school</span>
                </span>
                <span class="nav-text">Applications</span>
            </a>

            <a href="projects.php" class="<?= $currentPage == 'projects.php' ? 'active' : '' ?>" title="Candidates">
                <span class="icon">
                    <span class="material-symbols-outlined">folder_open</span>
                </span>
                <span class="nav-text">Candidates</span>
            </a>

            <a href="" class="<?= $currentPage == 'internships.php' ? 'active' : '' ?>" title="Interviews">
                <span class="icon">
                    <span class="material-symbols-outlined">work</span>
                </span>
                <span class="nav-text">Interviews</span>
            </a>

            <a href="complaints.php" class="<?= $currentPage == 'complaints.php' ? 'active' : '' ?>" title="Reports & Analytics">
                <span class="icon">
                    <span class="material-symbols-outlined">report_problem</span>
                </span>
                <span class="nav-text">Reports & Analytics</span>
            </a>

            <a href="reports.php" class="<?= $currentPage == 'reports.php' ? 'active' : '' ?>" title="Notifications">
                <span class="icon">
                    <span class="material-symbols-outlined">bar_chart</span>
                </span>
                <span class="nav-text">Notifications</span>
            </a>
            <a href="profile.php" class="<?= in_array($currentPage, ['settings.php', 'profile.php']) ? 'active' : '' ?>" title="Profile & Settings">
                <span class="icon">
                    <span class="material-symbols-outlined">settings</span>
                </span>
                <span class="nav-text">Profile & Settings</span>
            </a>

        </nav>
        <button class="logout" style="background: #010f41ff;" onclick="window.location.href='../../../Session/Logout.php'" title="Post Internships">
            <span class="material-symbols-outlined" style="font-size:18px;">add_circle</span>
            <span class="btn-text">Post Internships</span>
        </button>
        <button class="logout" onclick="window.location.href='../../../Session/Logout.php'" title="Logout">
            <span class="material-symbols-outlined" style="font-size:18px;">logout</span>
            <span class="btn-text">Logout</span>
        </button>

    </aside>

<div class="main-wrapper">
