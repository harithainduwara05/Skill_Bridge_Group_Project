<?php $currentPage = basename($_SERVER['PHP_SELF']); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Company Dashboard | SkillBridge</title>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet">

    <link rel="stylesheet" href="../../../Assets/CSS/dashboard.css">

    <?php echo isset($extra_css) ? $extra_css : ''; ?>

</head>

<body>

<aside class="sidebar company-sidebar">

    <div class="logo">
        <img src="../../../Assets/Images/logo.png" alt="SkillBridge">
    </div>


    <nav>

        <a href="dashboard.php"
           class="<?= $currentPage === 'dashboard.php' ? 'active' : '' ?>">

            <span class="icon">
                <img src="../../../Assets/Images/Icons/dashboard.svg" alt="">
            </span>

            Dashboard

        </a>


        <a href="company.php"
           class="<?= $currentPage === 'company.php' ? 'active' : '' ?>">

            <span class="icon">
                <img src="../../../Assets/Images/Icons/profile.png" alt="">
            </span>

            Company Profile

        </a>


        <!-- ONLY CHANGED THIS INTERNSHIP LINK -->

        <a href="internships.php"
           class="<?= in_array(
               $currentPage,
               [
                   'internships.php',
                   'add_internship.php',
                   'edit_internship.php',
                   'view_internship.php'
               ]
           ) ? 'active' : '' ?>">

            <span class="icon">
                <img src="../../../Assets/Images/Icons/internship.png" alt="">
            </span>

            Internships

        </a>


        <a href="#"
           aria-disabled="true"
           title="Applications page is not implemented yet">

            <span class="icon">
                <img src="../../../Assets/Images/Icons/application.png" alt="">
            </span>

            Applications

            <?php if (!empty($companyApplicationCount)): ?>

                <span class="badge">
                    <?= (int) $companyApplicationCount ?>
                </span>

            <?php endif; ?>

        </a>


        <a href="#"
           aria-disabled="true"
           title="Candidates page is not implemented yet">

            <span class="icon">
                <img src="../../../Assets/Images/Icons/shortlist.png" alt="">
            </span>

            Candidates

        </a>


        <a href="#"
           aria-disabled="true"
           title="Interviews page is not implemented yet">

            <span class="icon">
                <img src="../../../Assets/Images/Icons/interviews.png" alt="">
            </span>

            Interviews

        </a>


        <a href="#"
           aria-disabled="true"
           title="Reports page is not implemented yet">

            <span class="icon">
                <span class="material-symbols-outlined">
                    analytics
                </span>
            </span>

            Reports &amp; Analytics

        </a>


        <a href="#"
           aria-disabled="true"
           title="Notifications page is not implemented yet">

            <span class="icon">
                <img src="../../../Assets/Images/Icons/notification.png" alt="">
            </span>

            Notifications

        </a>


        <a href="#"
           aria-disabled="true"
           title="Settings page is not implemented yet">

            <span class="icon">
                <img src="../../../Assets/Images/Icons/settings.png" alt="">
            </span>

            Settings

        </a>

    </nav>


    <!-- ONLY CHANGED THIS POST INTERNSHIP BUTTON -->

    <a class="post-new-btn" href="add_internship.php">

        <span class="material-symbols-outlined">
            add
        </span>

        Post Internship

    </a>


    <button class="logout"
            type="button"
            onclick="window.location.href='../../../Session/Logout.php'">

        <span class="material-symbols-outlined">
            logout
        </span>

        Logout

    </button>

</aside>


<div class="main-wrapper">