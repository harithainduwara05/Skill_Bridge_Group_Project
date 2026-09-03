<?php
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Organization Dashboard | SkillBridge</title>

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet">
<link rel="stylesheet" href="../../../Assets/CSS/dashboard.css?v=8">
<link rel="stylesheet" href="../../../Assets/CSS/flash-toast.css">
</head>

<body>

<aside class="sidebar">

    <div class="logo">
        <img src="../../../Assets/Images/logo.png" alt="SkillBridge">
    </div>

    <nav>

        <a href="dashboard.php"
        class="<?= $currentPage == 'dashboard.php' ? 'active' : '' ?>" title="Dashboard">
            <span class="icon">
                <span class="material-symbols-outlined">dashboard</span>
            </span>
            <span class="nav-text">Dashboard</span>
        </a>

        <a href="post.php"
        class="<?= $currentPage == 'post.php' ? 'active' : '' ?>" title="Post Project">
            <span class="icon">
                <span class="material-symbols-outlined">post_add</span>
            </span>
            <span class="nav-text">Post Project</span>
        </a>

        <a href="manage_projects.php"
        class="<?= $currentPage == 'manage_projects.php' ? 'active' : '' ?>" title="Manage Projects">
            <span class="icon">
                <span class="material-symbols-outlined">folder_open</span>
            </span>
            <span class="nav-text">Manage Projects</span>
        </a>

        <a href="proposal.php"
        class="<?= $currentPage == 'proposal.php' ? 'active' : '' ?>" title="Proposals">
            <span class="icon">
                <span class="material-symbols-outlined">description</span>
            </span>
            <span class="nav-text">Proposals</span>
        </a>

        <a href="teams.php"
        class="<?= $currentPage == 'teams.php' ? 'active' : '' ?>" title="Teams">
            <span class="icon">
                <span class="material-symbols-outlined">group</span>
            </span>
            <span class="nav-text">Teams</span>
        </a>

        <a href="feedback.php"
        class="<?= $currentPage == 'feedback.php' ? 'active' : '' ?>" title="Feedback">
            <span class="icon">
                <span class="material-symbols-outlined">feedback</span>
            </span>
            <span class="nav-text">Feedback</span>
        </a>

        <a href="notifications.php"
        class="<?= $currentPage == 'notifications.php' ? 'active' : '' ?>" title="Notifications">
            <span class="icon">
                <span class="material-symbols-outlined">notifications</span>
            </span>
            <span class="nav-text">Notifications</span>
        </a>

        <a href="settings.php"
        class="<?= in_array($currentPage, ['settings.php', 'profile.php']) ? 'active' : '' ?>" title="Profile & Settings">
            <span class="icon">
                <span class="material-symbols-outlined">settings</span>
            </span>
            <span class="nav-text">Profile & Settings</span>
        </a>

    </nav>

    <a href="post.php" class="post-new-btn" title="Post New Project">
        <span class="material-symbols-outlined" style="font-size:18px;">add</span>
        <span class="btn-text">Post New Project</span>
    </a>

    <button class="logout" onclick="window.location.href='../../../Session/Logout.php'" title="Logout">
        <span class="material-symbols-outlined" style="font-size:18px;">logout</span>
        <span class="btn-text">Logout</span>
    </button>

</aside>

<div class="main-wrapper">