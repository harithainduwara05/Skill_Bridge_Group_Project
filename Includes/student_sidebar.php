<?php
$currentPage = basename($_SERVER['PHP_SELF']);
$studentUnreadNotifications = 0;
require_once __DIR__ . '/../Config/db.php';
require_once __DIR__ . '/../Session/Session.php';
$sidebarUser = current_user();
$sidebarEmail = $sidebarUser['Email'] ?? $sidebarUser['email'] ?? null;
if (is_string($sidebarEmail) && $sidebarEmail !== '' && isset($conn)) {
    try {
        $unreadStmt = $conn->prepare("SELECT COUNT(*) FROM notifications WHERE Email = ? AND status = 'Unread'");
        $unreadStmt->bind_param('s', $sidebarEmail);
        $unreadStmt->execute();
        $studentUnreadNotifications = (int) ($unreadStmt->get_result()->fetch_row()[0] ?? 0);
        $unreadStmt->close();
    } catch (Throwable $exception) {
        error_log('Student notification sidebar count: ' . $exception->getMessage());
    }
}
require_once __DIR__ . "/../Functions/Student/check_student_access.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Student Dashboard | SkillBridge' ?></title>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../../Assets/CSS/dashboard.css">
    <?= $extra_css ?? '' ?>
    <style>
        .sidebar .student-nav-unread-badge { margin-left: auto; padding: 2px 7px; border-radius: 999px; background: #dc2626; color: #fff; font-size: 11px; font-weight: 700; }
    </style>
</head>
<body>

<aside class="sidebar">
    <div class="logo">
        <img src="../../Assets/Images/logo.png" alt="SkillBridge">
    </div>

    <nav>
        <a href="dashboard.php" class="<?= $currentPage == 'dashboard.php' ? 'active' : '' ?>" title="Dashboard">
            <span class="icon">
                <span class="material-symbols-outlined">dashboard</span>
            </span>
            <span class="nav-text">Dashboard</span>
        </a>

        <a href="projects.php" class="<?= $currentPage == 'projects.php' ? 'active' : '' ?>" title="Projects">
            <span class="icon">
                <span class="material-symbols-outlined">folder_open</span>
            </span>
            <span class="nav-text">Projects</span>
        </a>

        <a href="teams.php" class="<?= $currentPage == 'teams.php' ? 'active' : '' ?>" title="Teams">
            <span class="icon">
                <span class="material-symbols-outlined">group</span>
            </span>
            <span class="nav-text">Teams</span>
        </a>

        <?php
            $user = current_user();
            $email = $user['Email'] ?? $user['email'] ?? null;
        ?>
        <?php if($email && canApplyInternship($email)){ ?>
            <a href="../../Functions/Student/internships.php"
            class="<?= $currentPage == 'internships.php' ? 'active' : '' ?>"
            title="Internships">
                <span class="icon">
                    <span class="material-symbols-outlined">
                        work
                    </span>
                </span>
                <span class="nav-text">
                    Internships
                </span>
            </a>

            <a href="../../Functions/Student/applications.php"
            class="<?= $currentPage == 'applications.php' ? 'active' : '' ?>"
            title="Applications">
                <span class="icon">
                    <span class="material-symbols-outlined">
                        assignment
                    </span>
                </span>
                <span class="nav-text">
                    Applications
                </span>
            </a>
        <?php } ?>
        
        <a href="skills.php" class="<?= $currentPage == 'skills.php' ? 'active' : '' ?>" title="Skills">
            <span class="icon">
                <span class="material-symbols-outlined">psychology</span>
            </span>
            <span class="nav-text">Skills</span>
        </a>

        <a href="portfolio.php" class="<?= $currentPage == 'portfolio.php' ? 'active' : '' ?>" title="Portfolio">
            <span class="icon">
                <span class="material-symbols-outlined">badge</span>
            </span>
            <span class="nav-text">Portfolio</span>
        </a>

        <a href="notifications.php" class="<?= $currentPage == 'notifications.php' ? 'active' : '' ?>" title="Notifications">
            <span class="icon">
                <span class="material-symbols-outlined">notifications</span>
            </span>
            <span class="nav-text">Notifications</span>
            <?php if ($studentUnreadNotifications > 0): ?>
                <span class="student-nav-unread-badge" aria-label="<?= $studentUnreadNotifications ?> unread notifications"><?= $studentUnreadNotifications > 99 ? '99+' : $studentUnreadNotifications ?></span>
            <?php endif; ?>
        </a>

        <a href="settings.php" class="<?= in_array($currentPage, ['settings.php', 'profile.php']) ? 'active' : '' ?>" title="Settings">
            <span class="icon">
                <span class="material-symbols-outlined">settings</span>
            </span>
            <span class="nav-text">Settings</span>
        </a>
    </nav>

    <button class="logout" onclick="window.location.href='../../Session/Logout.php'" title="Logout">
        <span class="material-symbols-outlined" style="font-size:18px;">logout</span>
        <span class="btn-text">Logout</span>
    </button>
</aside>

<div class="main-wrapper">