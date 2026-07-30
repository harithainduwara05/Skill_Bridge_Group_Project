<?php
$currentPage = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>

<html lang="en">
<head>
<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Dashboard | SkillBridge</title>
    <!--<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet">-->
    <link rel="stylesheet" href="../../../Assets/CSS/dashboard.css">
</head>

<aside class="sidebar">

    <div class="logo">
        <img src="../../../Assets/Images/SkillBridge.png" alt="SkillBridge">
    </div>


    <nav>


        <a href="dashboard.php" 
        class="<?= $currentPage == 'dashboard.php' ? 'active' : '' ?>">

            <span class="icon">
                <img src="../../../Assets/Images/Icons/dashboard.svg">
            </span>

            Dashboard

        </a>



        <a href="users.php"
        class="<?= $currentPage == 'users.php' ? 'active' : '' ?>">

            <span class="icon">
                <img src="../../../Assets/Images/Icons/users.svg">
            </span>

            Users

        </a>



        <a href="universities.php">

            <span class="icon">
                <img src="../../../Assets/Images/Icons/university.svg">
            </span>

            Universities

        </a>



        <a href="projects.php">

            <span class="icon">
                <img src="../../../Assets/Images/Icons/projects.svg">
            </span>

            Projects

        </a>



        <a href="internships.php">

            <span class="icon">
                <img src="../../../Assets/Images/Icons/internship.png">
            </span>

            Internships

        </a>



        <a href="complaints.php">

            <span class="icon">
                <img src="../../../Assets/Images/Icons/complaint.png">
            </span>

            Complaints

            <span class="badge">
                12
            </span>

        </a>



        <a href="reports.php">

            <span class="icon">
                <img src="../../../Assets/Images/Icons/report.png">
            </span>

            Reports

        </a>



        <a href="notifications.php">

            <span class="icon">
                <img src="../../../Assets/Images/Icons/notification.png">
            </span>

            Notifications

        </a>



        <a href="settings.php">

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
