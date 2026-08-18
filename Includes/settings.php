<?php
session_start();

$user = [
    'name' => 'Alex Rivera',
    'title' => 'Talent Lead',
    'avatar' => '../../../Assets/Images/alex-rivera-avatar.jpg'
];

$company_settings = [
    'company_name' => 'SkillBridge Tech',
    'contact_email' => 'admin@skillbridge.tech',
    'timezone' => 'UTC -05:00 Eastern Time (US & Canada)',
    'language' => 'English (United States)'
];

$team_members = [
    [
        'id' => 1,
        'name' => 'Sarah Jenkins',
        'email' => 'sarah.j@skillbridge.tech',
        'role' => 'ADMIN',
        'role_class' => 'role-admin',
        'status' => 'Active',
        'status_class' => 'status-active',
        'avatar' => '../../../Assets/Images/sarah-jenkins.jpg'
    ],
    [
        'id' => 2,
        'name' => 'David Chen',
        'email' => 'd.chen@skillbridge.tech',
        'role' => 'RECRUITER',
        'role_class' => 'role-recruiter',
        'status' => 'Interviewing',
        'status_class' => 'status-interviewing',
        'avatar' => '../../../Assets/Images/david-chen.jpg'
    ],
    [
        'id' => 3,
        'name' => 'Fatima Al-Sayed',
        'email' => 'f.alsayed@skillbridge.tech',
        'role' => 'VIEW-ONLY',
        'role_class' => 'role-view-only',
        'status' => 'Active',
        'status_class' => 'status-active',
        'avatar' => '../../../Assets/Images/fatima.jpg'
    ]
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Company Settings - SkillBridge</title>
    <link rel="stylesheet" href="../../../Assets/CSS/settings.css">
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
            <a href="notifications.php"><i class="fa-regular fa-bell"></i> Notifications</a>
            <a href="settings.php" class="active"><i class="fa-solid fa-gear"></i> Settings</a>
        </nav>
        <div class="sidebar-actions">
            <a href="post_internship.php" class="btn-post-nav"><i class="fa-solid fa-plus"></i> Post Internship</a>
            <a href="../../../Auth/logout.php" class="btn-logout"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
        </div>
    </aside>

    <!-- Main Workspace -->
    <div class="main-content">
        <!-- Dashboard Header -->
        <header class="dash-header">
            <div class="header-search">
                <i class="fa-solid fa-magnifying-glass search-icon"></i>
                <input type="text" placeholder="Search dashboard...">
            </div>

            <div class="header-right">
                <a href="notifications.php" class="icon-btn">
                    <i class="fa-regular fa-bell"></i>
                </a>

                <div class="user-profile">
                    <div class="user-info">
                        <span class="user-name"><?= htmlspecialchars($user['name']) ?></span>
                        <span class="user-role"><?= htmlspecialchars($user['title']) ?></span>
                    </div>
                    <img src="<?= htmlspecialchars($user['avatar']) ?>" alt="Alex Rivera" class="header-avatar" onerror="this.src='https://ui-avatars.com/api/?name=Alex+Rivera&background=082544&color=fff'">
                </div>
            </div>
        </header>

        <!-- Page Body Content -->
        <main class="page-body">
            <!-- Title Section -->
            <div class="page-header-title">
                <h1>Company Settings</h1>
                <p>Manage your organization's global preferences, security, and team access.</p>
            </div>

            <!-- Settings Sub-Navigation Tabs -->
            <div class="settings-tabs">
                <a href="#general" class="tab-link active">General Account</a>
                <a href="#security" class="tab-link">Security</a>
                <a href="#team" class="tab-link">Team Management</a>
                <a href="#api" class="tab-link">API & Integrations</a>
            </div>

            <!-- General Profile Card -->
            <div class="settings-section-grid">
                <div class="section-info">
                    <h3>General Profile</h3>
                    <p>Basic information about your company and portal localization.</p>
                </div>

                <div class="form-card">
                    <form action="process_settings.php" method="POST">
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="company_name">COMPANY NAME</label>
                                <input type="text" id="company_name" name="company_name" value="<?= htmlspecialchars($company_settings['company_name']) ?>" required>
                            </div>

                            <div class="form-group">
                                <label for="contact_email">CONTACT EMAIL</label>
                                <input type="email" id="contact_email" name="contact_email" value="<?= htmlspecialchars($company_settings['contact_email']) ?>" required>
                            </div>

                            <div class="form-group">
                                <label for="timezone">TIME ZONE</label>
                                <div class="select-wrapper">
                                    <select id="timezone" name="timezone">
                                        <option value="UTC -05:00 Eastern Time (US & Canada)" selected>UTC -05:00 Eastern Time (US & Canada)</option>
                                        <option value="UTC +00:00 GMT">UTC +00:00 GMT</option>
                                        <option value="UTC +05:30 India Standard Time">UTC +05:30 India Standard Time</option>
                                    </select>
                                    <i class="fa-solid fa-chevron-down select-icon"></i>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="language">LANGUAGE</label>
                                <div class="select-wrapper">
                                    <select id="language" name="language">
                                        <option value="English (United States)" selected>English (United States)</option>
                                        <option value="English (United Kingdom)">English (United Kingdom)</option>
                                        <option value="Spanish">Spanish</option>
                                    </select>
                                    <i class="fa-solid fa-chevron-down select-icon"></i>
                                </div>
                            </div>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn-save-changes">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Team Management Section -->
            <div class="team-management-wrapper">
                <div class="team-header">
                    <div>
                        <h2>Team Management</h2>
                        <p>Manage user roles and permissions for your team.</p>
                    </div>
                    <button class="btn-invite-member" id="btnInviteMember">
                        <i class="fa-solid fa-user-plus"></i> Invite Member
                    </button>
                </div>

                <div class="table-card">
                    <table class="team-table">
                        <thead>
                            <tr>
                                <th>MEMBER</th>
                                <th>ROLE</th>
                                <th>STATUS</th>
                                <th class="text-right">ACTION</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($team_members as $member): ?>
                                <tr>
                                    <td>
                                        <div class="member-profile-cell">
                                            <div class="avatar-wrapper">
                                                <img src="<?= htmlspecialchars($member['avatar']) ?>" alt="<?= htmlspecialchars($member['name']) ?>" onerror="this.src='https://ui-avatars.com/api/?name=<?= urlencode($member['name']) ?>&background=082544&color=fff'">
                                                <span class="status-indicator <?= strtolower($member['status']) === 'active' ? 'indicator-online' : 'indicator-away' ?>"></span>
                                            </div>
                                            <div>
                                                <span class="member-name"><?= htmlspecialchars($member['name']) ?></span>
                                                <span class="member-email"><?= htmlspecialchars($member['email']) ?></span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge-role <?= $member['role_class'] ?>">
                                            <?= htmlspecialchars($member['role']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="status-text <?= $member['status_class'] ?>">
                                            <i class="fa-solid fa-circle dot-icon"></i> <?= htmlspecialchars($member['status']) ?>
                                        </span>
                                    </td>
                                    <td class="text-right">
                                        <button class="btn-action-menu"><i class="fa-solid fa-ellipsis-vertical"></i></button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>

        <!-- Footer Navigation -->
        <footer class="dash-footer">
            <span>© 2026 SkillBridge. All rights reserved.</span>
            <div class="footer-links">
                <a href="#">Help Center</a>
                <a href="#">Privacy Policy</a>
                <a href="#">Terms of Service</a>
            </div>
        </footer>
    </div>
</div>

<script src="../../../Assets/JS/settings.js"></script>
</body>
</html>