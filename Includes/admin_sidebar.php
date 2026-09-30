<?php
$currentPage = basename($_SERVER['PHP_SELF']);

if (!isset($conn)) {
    @include_once __DIR__ . '/../Config/db.php';
}

$activeComplaintsCount = 0;
if (isset($conn) && $conn instanceof mysqli) {
    // Total complaints excluding RESOLVED and DISMISSED
    $cntRes = $conn->query("SELECT COUNT(*) AS active_count FROM complain WHERE status NOT IN ('RESOLVED', 'DISMISSED')");
    if ($cntRes && $cntRow = $cntRes->fetch_assoc()) {
        $activeComplaintsCount = intval($cntRow['active_count'] ?? 0);
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | SkillBridge</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200"
        rel="stylesheet">
    <link rel="stylesheet" href="../../Assets/CSS/dashboard.css">
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

            <a href="User_Management.php" class="<?= in_array($currentPage, ['User_Management.php']) ? 'active' : '' ?>"
                title="Users">
                <span class="icon">
                    <span class="material-symbols-outlined">group</span>
                </span>
                <span class="nav-text">Users</span>
            </a>

            <a href="university.php" class="<?= $currentPage == 'university.php' ? 'active' : '' ?>"
                title="Universities">
                <span class="icon">
                    <span class="material-symbols-outlined">school</span>
                </span>
                <span class="nav-text">Universities</span>
            </a>

            <a href="Project_Management.php"
                class="<?= in_array($currentPage, ['Project_Management.php']) ? 'active' : '' ?>" title="Projects">
                <span class="icon">
                    <span class="material-symbols-outlined">folder_open</span>
                </span>
                <span class="nav-text">Projects</span>
            </a>

            <a href="Internship_Management.php"
                class="<?= in_array($currentPage, ['Internship_Management.php']) ? 'active' : '' ?>"
                title="Internships">
                <span class="icon">
                    <span class="material-symbols-outlined">work</span>
                </span>
                <span class="nav-text">Internships</span>
            </a>

            <a href="complain.php" class="<?= $currentPage === 'complain.php' ? 'active' : '' ?>"
                title="Complaints">
                <span class="icon">
                    <span class="material-symbols-outlined">report_problem</span>
                </span>
                <span class="nav-text">Complaints</span>
                <?php if ($activeComplaintsCount > 0): ?>
                    <span class="badge" id="sidebarComplaintBadge"><?= $activeComplaintsCount ?></span>
                <?php endif; ?>
            </a>

            <a href="reportandAnalysist.php"
                class="<?= in_array($currentPage, ['reportandAnalysist.php']) ? 'active' : '' ?>"
                title="Reports & Analytics">
                <span class="icon">
                    <span class="material-symbols-outlined">bar_chart</span>
                </span>
                <span class="nav-text">Reports & Analytics</span>
            </a>

            <a href="notification.php" class="<?= in_array($currentPage, ['notification.php']) ? 'active' : '' ?>"
                title="Notifications">
                <span class="icon">
                    <span class="material-symbols-outlined">notifications</span>
                </span>
                <span class="nav-text">Notifications</span>
            </a>

            <a href="profile.php" class="<?= in_array($currentPage, ['settings.php', 'profile.php']) ? 'active' : '' ?>"
                title="Profile & Settings">
                <span class="icon">
                    <span class="material-symbols-outlined">settings</span>
                </span>
                <span class="nav-text">Profile & Settings</span>
            </a>

        </nav>

        <button class="logout" onclick="window.location.href='../../Session/Logout.php'" title="Logout">
            <span class="material-symbols-outlined" style="font-size:18px;">logout</span>
            <span class="btn-text">Logout</span>
        </button>

    </aside>

    <div class="main-wrapper">