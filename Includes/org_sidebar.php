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
        class="<?= $currentPage == 'dashboard.php' ? 'active' : '' ?>">
            <span class="icon">
                <span class="material-symbols-outlined">dashboard</span>
            </span>
            Dashboard
        </a>

        <a href="post.php"
        class="<?= $currentPage == 'post.php' ? 'active' : '' ?>">
            <span class="icon">
                <span class="material-symbols-outlined">post_add</span>
            </span>
            Post Project
        </a>

        <a href="manage_projects.php"
        class="<?= $currentPage == 'manage_projects.php' ? 'active' : '' ?>">
            <span class="icon">
                <span class="material-symbols-outlined">folder_open</span>
            </span>
            Manage Projects
        </a>

        <a href="proposal.php"
        class="<?= $currentPage == 'proposal.php' ? 'active' : '' ?>">
            <span class="icon">
                <span class="material-symbols-outlined">description</span>
            </span>
            Proposals
        </a>

        <a href="teams.php"
        class="<?= $currentPage == 'teams.php' ? 'active' : '' ?>">
            <span class="icon">
                <span class="material-symbols-outlined">group</span>
            </span>
            Teams
        </a>

        <a href="feedback.php"
        class="<?= $currentPage == 'feedback.php' ? 'active' : '' ?>">
            <span class="icon">
                <span class="material-symbols-outlined">feedback</span>
            </span>
            Feedback
        </a>

        <a href="notifications.php"
        class="<?= $currentPage == 'notifications.php' ? 'active' : '' ?>">
            <span class="icon">
                <span class="material-symbols-outlined">notifications</span>
            </span>
            Notifications
        </a>

        <a href="profile.php"
        class="<?= $currentPage == 'profile.php' ? 'active' : '' ?>">
            <span class="icon">
                <span class="material-symbols-outlined">settings</span>
            </span>
            Profile & Settings
        </a>

    </nav>

    <a href="post.php" class="post-new-btn">
        <span class="material-symbols-outlined" style="font-size:18px;">add</span>
        Post New Project
    </a>

    <button class="logout" onclick="window.location.href='../../../Session/Logout.php'">
        <span class="material-symbols-outlined" style="font-size:18px;">logout</span>
        Logout
    </button>

</aside>

<div class="main-wrapper">