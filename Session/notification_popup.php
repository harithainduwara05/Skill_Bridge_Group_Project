<?php
session_start();

$user = [
    'name' => 'Alex Johnson',
    'title' => 'Company',
    'avatar' => '../../../Assets/Images/alex-avatar.jpg'
];

$unread_count = 5;

$notifications = [
    [
        'id' => 1,
        'title' => 'New Project Proposal Received',
        'description' => 'FinTech Solutions sent a new collaborative brief.',
        'time' => '2M AGO',
        'icon' => 'fa-folder',
        'is_unread' => true,
        'type' => 'proposal'
    ],
    [
        'id' => 2,
        'title' => 'New Internship Opportunity',
        'description' => 'Goldman Sachs posted: "Cloud Engineering Intern".',
        'time' => '1H AGO',
        'icon' => 'fa-briefcase',
        'is_unread' => false,
        'type' => 'opportunity'
    ],
    [
        'id' => 3,
        'title' => 'Application Status Updated',
        'badge' => 'SHORTLISTED',
        'badge_class' => 'badge-shortlisted',
        'time' => '4H AGO',
        'icon' => 'fa-file-lines',
        'is_unread' => false,
        'type' => 'status'
    ],
    [
        'id' => 4,
        'title' => 'Team Invitation',
        'description' => 'Sarah Miller invited you to join "Backend Wizards".',
        'time' => 'YESTERDAY',
        'icon' => 'fa-user-group',
        'is_unread' => false,
        'type' => 'team'
    ],
    [
        'id' => 5,
        'title' => 'Verification Complete',
        'description' => 'Your identity documents have been verified.',
        'time' => '2 DAYS AGO',
        'icon' => 'fa-circle-check',
        'is_unread' => false,
        'type' => 'system'
    ]
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notification Popup - SkillBridge</title>
    <link rel="stylesheet" href="../../../Assets/CSS/notification_popup.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<div class="dash-wrapper">
    <!-- Sidebar Navigation -->
    <aside class="sidebar">
        <div class="sidebar-logo">
            <i class="fa-solid fa-graduation-cap"></i>
            <span>SkillBridge</span>
        </div>
        <nav class="sidebar-nav">
            <a href="dashboard.php"><i class="fa-solid fa-border-all"></i> Dashboard</a>
            <a href="company_profile.php"><i class="fa-regular fa-building"></i> Company Profile</a>
            <a href="internships.php"><i class="fa-solid fa-briefcase"></i> Internships</a>
            <a href="applications.php"><i class="fa-solid fa-file-lines"></i> Applications</a>
            <a href="candidates.php"><i class="fa-solid fa-user-group"></i> Candidates</a>
            <a href="interviews.php"><i class="fa-regular fa-calendar"></i> Interviews</a>
            <a href="reports.php"><i class="fa-solid fa-chart-line"></i> Reports & Analytics</a>
            <a href="notifications.php" class="active"><i class="fa-regular fa-bell"></i> Notifications</a>
            <a href="settings.php"><i class="fa-solid fa-gear"></i> Settings</a>
        </nav>
        <div class="sidebar-actions">
            <a href="post_internship.php" class="btn-post-nav"><i class="fa-solid fa-plus"></i> Post Internship</a>
            <a href="../../../Auth/logout.php" class="btn-logout"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
        </div>
    </aside>

    <!-- Main Section with Notification Dropdown Header -->
    <div class="main-content">
        <!-- Top Navigation Header -->
        <header class="dash-header">
            <div class="breadcrumb">
                <span>Dashboard</span> / <span class="active">Notifications</span>
            </div>
            
            <div class="header-right">
                <div class="notification-trigger-wrapper">
                    <button class="icon-btn notification-btn" id="btnToggleNotifications">
                        <i class="fa-regular fa-bell"></i>
                    </button>

                    <!-- Notifications Dropdown Popup Panel -->
                    <div class="notifications-dropdown show" id="notificationsDropdown">
                        <div class="dropdown-header">
                            <div>
                                <h2>Notifications</h2>
                                <span class="sub-counter"><?= $unread_count ?> new notifications</span>
                            </div>
                            <button class="btn-mark-read" id="btnMarkAllRead">Mark all as read</button>
                        </div>

                        <div class="notifications-list">
                            <?php foreach ($notifications as $item): ?>
                                <div class="notification-item <?= $item['is_unread'] ? 'unread' : '' ?>">
                                    <div class="item-icon-wrapper">
                                        <i class="fa-solid <?= $item['icon'] ?>"></i>
                                    </div>
                                    <div class="item-content">
                                        <div class="item-title-row">
                                            <h4 class="item-title"><?= htmlspecialchars($item['title']) ?></h4>
                                            <?php if ($item['is_unread']): ?>
                                                <span class="unread-dot"></span>
                                            <?php endif; ?>
                                        </div>

                                        <?php if (isset($item['description'])): ?>
                                            <p class="item-desc"><?= htmlspecialchars($item['description']) ?></p>
                                        <?php endif; ?>

                                        <?php if (isset($item['badge'])): ?>
                                            <div class="item-badge-wrap">
                                                <span class="badge <?= $item['badge_class'] ?>">
                                                    <i class="fa-regular fa-circle-check"></i> <?= htmlspecialchars($item['badge']) ?>
                                                </span>
                                            </div>
                                        <?php endif; ?>

                                        <span class="item-time"><?= htmlspecialchars($item['time']) ?></span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="dropdown-footer">
                            <a href="notifications_all.php" class="btn-view-all">View All Notifications <i class="fa-solid fa-arrow-right"></i></a>
                        </div>
                    </div>
                </div>

                <div class="user-profile">
                    <div class="user-info">
                        <span class="user-name"><?= htmlspecialchars($user['name']) ?></span>
                        <span class="user-role"><?= htmlspecialchars($user['title']) ?></span>
                    </div>
                    <img src="<?= htmlspecialchars($user['avatar']) ?>" alt="Alex Johnson" class="header-avatar" onerror="this.src='https://ui-avatars.com/api/?name=Alex+Johnson&background=082544&color=fff'">
                </div>
            </div>
        </header>

        <!-- Main Body Background Canvas -->
        <main class="page-body">
            <!-- Background content canvas -->
        </main>
    </div>
</div>

<script src="../../../Assets/JS/notification_popup.js"></script>
</body>
</html>