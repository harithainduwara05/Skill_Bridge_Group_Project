<?php
session_start();

// Database connection & authentication check
// require_once '../../../Config/database.php';
// if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'company') { header('Location: ../../../Auth/login.php'); exit(); }

$user = [
    'name' => 'Sarah Jenkins',
    'title' => 'Senior Recruiter',
    'avatar' => '../../../Assets/Images/admin-avatar.jpg'
];

// Mock data representation for stats
$stats = [
    'total_applicants' => '1,248',
    'under_review' => '412',
    'shortlisted' => '86',
    'interviews' => '24'
];

// Mock applications data (Replace with dynamic DB fetch query)
$applications = [
    [
        'id' => 101,
        'name' => 'Alex Rivera',
        'email' => 'alex.rivera@edu.com',
        'avatar' => '',
        'status_color' => 'green',
        'university' => 'Stanford University',
        'skills' => ['React', 'Figma'],
        'extra_skills' => 2,
        'applied_date' => 'Oct 12, 2023',
        'status' => 'Interviewing',
        'status_badge' => 'badge-interviewing',
        'is_starred' => false
    ],
    [
        'id' => 102,
        'name' => 'Maya Patel',
        'email' => 'maya.p@mit.edu',
        'avatar' => '../../../Assets/Images/avatars/maya.jpg',
        'status_color' => '',
        'university' => 'MIT',
        'skills' => ['Python', 'TensorFlow'],
        'extra_skills' => 0,
        'applied_date' => 'Oct 14, 2023',
        'status' => 'Applied',
        'status_badge' => 'badge-applied',
        'is_starred' => false
    ],
    [
        'id' => 103,
        'name' => 'James Wilson',
        'email' => 'j.wilson@gatech.edu',
        'avatar' => '',
        'status_color' => 'green',
        'university' => 'Georgia Tech',
        'skills' => ['UX Design', 'Wireframing'],
        'extra_skills' => 0,
        'applied_date' => 'Oct 15, 2023',
        'status' => 'Shortlisted',
        'status_badge' => 'badge-shortlisted',
        'is_starred' => true
    ]
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Internship Applications - SkillBridge</title>
    <link rel="stylesheet" href="../../../Assets/CSS/dashboard.css">
    <link rel="stylesheet" href="../../../Assets/CSS/applications.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<div class="dash-wrapper">
    <!-- Shared Sidebar -->
    <?php 
    if (file_exists('company_sidebar.php')) {
        include 'company_sidebar.php';
    } else { ?>
        <aside class="sidebar">
            <div class="sidebar-logo">
                <i class="fa-solid fa-graduation-cap"></i>
                <span>SkillBridge</span>
            </div>
            <nav class="sidebar-nav">
                <a href="dashboard.php"><i class="fa-solid fa-border-all"></i> Dashboard</a>
                <a href="company_profile.php"><i class="fa-regular fa-building"></i> Company Profile</a>
                <a href="internships.php"><i class="fa-solid fa-briefcase"></i> Internships</a>
                <a href="applications.php" class="active"><i class="fa-solid fa-file-lines"></i> Applications</a>
                <a href="candidates.php"><i class="fa-solid fa-user-group"></i> Candidates</a>
                <a href="interviews.php"><i class="fa-regular fa-calendar-check"></i> Interviews</a>
                <a href="reports.php"><i class="fa-solid fa-chart-line"></i> Reports & Analytics</a>
                <a href="notifications.php"><i class="fa-regular fa-bell"></i> Notifications</a>
                <a href="settings.php"><i class="fa-solid fa-gear"></i> Settings</a>
            </nav>
            <div class="sidebar-actions">
                <a href="post_internship.php" class="btn-post-nav"><i class="fa-solid fa-plus"></i> Post Internship</a>
                <a href="../../../Auth/logout.php" class="btn-logout"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
            </div>
        </aside>
    <?php } ?>

    <div class="main-content">
        <!-- Dashboard Header -->
        <header class="dash-header">
            <div class="header-search">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" id="globalSearchInput" placeholder="Search applications...">
            </div>
            <div class="header-right">
                <button class="icon-btn notification-btn"><i class="fa-regular fa-bell"></i><span class="dot"></span></button>
                <div class="user-profile">
                    <div class="user-info">
                        <span class="user-name"><?= htmlspecialchars($user['name']) ?></span>
                        <span class="user-role"><?= htmlspecialchars($user['title']) ?></span>
                    </div>
                    <img src="<?= htmlspecialchars($user['avatar']) ?>" alt="User Avatar" class="header-avatar" onerror="this.src='https://ui-avatars.com/api/?name=Sarah+Jenkins&background=082544&color=fff'">
                </div>
            </div>
        </header>

        <!-- Main Workspace -->
        <main class="page-body">
            <!-- Page Header -->
            <div class="page-header">
                <div class="header-title">
                    <h1>Internship Applications</h1>
                    <p>Manage and review <?= $stats['total_applicants'] ?> active student applications across all programs.</p>
                </div>
                <div class="header-actions">
                    <button class="btn-outline" id="btnExportCsv"><i class="fa-solid fa-download"></i> Export CSV</button>
                    <button class="btn-primary-navy" id="btnBulkAction"><i class="fa-solid fa-plus"></i> Bulk Action</button>
                </div>
            </div>

            <!-- Stats Metric Cards Grid -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon icon-blue"><i class="fa-solid fa-users"></i></div>
                    <div class="stat-details">
                        <span class="stat-label">TOTAL APPLICANTS</span>
                        <h2 class="stat-value"><?= $stats['total_applicants'] ?></h2>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon icon-amber"><i class="fa-solid fa-hourglass-half"></i></div>
                    <div class="stat-details">
                        <span class="stat-label">UNDER REVIEW</span>
                        <h2 class="stat-value text-amber"><?= $stats['under_review'] ?></h2>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon icon-green"><i class="fa-regular fa-circle-check"></i></div>
                    <div class="stat-details">
                        <span class="stat-label">SHORTLISTED</span>
                        <h2 class="stat-value text-green"><?= $stats['shortlisted'] ?></h2>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon icon-purple"><i class="fa-regular fa-calendar"></i></div>
                    <div class="stat-details">
                        <span class="stat-label">INTERVIEWS</span>
                        <h2 class="stat-value text-purple"><?= $stats['interviews'] ?></h2>
                    </div>
                </div>
            </div>

            <!-- Filters Bar Card -->
            <div class="card filter-card">
                <div class="filter-grid">
                    <div class="filter-group">
                        <label for="filterRole">Internship Role</label>
                        <select id="filterRole">
                            <option value="">All Internships</option>
                            <option value="uiux">UI/UX Designer</option>
                            <option value="frontend">Frontend Developer</option>
                            <option value="ml">Machine Learning Engineer</option>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label for="filterStatus">Application Status</label>
                        <select id="filterStatus">
                            <option value="">All Statuses</option>
                            <option value="Applied">Applied</option>
                            <option value="Shortlisted">Shortlisted</option>
                            <option value="Interviewing">Interviewing</option>
                            <option value="Rejected">Rejected</option>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label for="filterSkills">Required Skills</label>
                        <select id="filterSkills">
                            <option value="">Select Skill</option>
                            <option value="React">React</option>
                            <option value="Figma">Figma</option>
                            <option value="Python">Python</option>
                            <option value="UX Design">UX Design</option>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label for="filterUniversity">University</label>
                        <select id="filterUniversity">
                            <option value="">All Universities</option>
                            <option value="Stanford">Stanford University</option>
                            <option value="MIT">MIT</option>
                            <option value="Georgia Tech">Georgia Tech</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Applications Table Card -->
            <div class="card table-card">
                <div class="table-responsive">
                    <table class="applications-table">
                        <thead>
                            <tr>
                                <th>STUDENT NAME</th>
                                <th>UNIVERSITY</th>
                                <th>SKILLS</th>
                                <th>APPLIED DATE</th>
                                <th>STATUS</th>
                                <th class="text-right">ACTIONS</th>
                            </tr>
                        </thead>
                        <tbody id="applicationsTbody">
                            <?php foreach ($applications as $app): ?>
                                <tr data-id="<?= $app['id'] ?>">
                                    <td>
                                        <div class="student-cell">
                                            <div class="avatar-wrapper">
                                                <?php if (!empty($app['avatar'])): ?>
                                                    <img src="<?= htmlspecialchars($app['avatar']) ?>" alt="Avatar" class="student-avatar" onerror="this.src='https://ui-avatars.com/api/?name=<?= urlencode($app['name']) ?>&background=082544&color=fff'">
                                                <?php else: ?>
                                                    <div class="student-avatar-placeholder"><?= strtoupper(substr($app['name'], 0, 1)) ?></div>
                                                <?php endif; ?>
                                                <?php if ($app['status_color'] === 'green'): ?>
                                                    <span class="online-indicator"></span>
                                                <?php endif; ?>
                                            </div>
                                            <div class="student-info">
                                                <span class="student-name"><?= htmlspecialchars($app['name']) ?></span>
                                                <span class="student-email"><?= htmlspecialchars($app['email']) ?></span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="university-name"><?= htmlspecialchars($app['university']) ?></span>
                                    </td>
                                    <td>
                                        <div class="skills-cell">
                                            <?php foreach ($app['skills'] as $skill): ?>
                                                <span class="skill-pill"><?= htmlspecialchars($skill) ?></span>
                                            <?php endforeach; ?>
                                            <?php if ($app['extra_skills'] > 0): ?>
                                                <span class="skill-pill pill-more">+<?= $app['extra_skills'] ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="date-text"><?= htmlspecialchars($app['applied_date']) ?></span>
                                    </td>
                                    <td>
                                        <span class="status-badge <?= $app['status_badge'] ?>"><?= htmlspecialchars($app['status']) ?></span>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <button class="action-btn btn-view" title="View Details"><i class="fa-regular fa-eye"></i></button>
                                            <button class="action-btn btn-doc" title="View Resume"><i class="fa-regular fa-file-lines"></i></button>
                                            <button class="action-btn btn-star <?= $app['is_starred'] ? 'starred' : '' ?>" title="Star Candidate">
                                                <i class="<?= $app['is_starred'] ? 'fa-solid' : 'fa-regular' ?> fa-star"></i>
                                            </button>
                                            <button class="action-btn btn-reject" title="Reject Application"><i class="fa-regular fa-circle-xmark"></i></button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Table Footer Pagination -->
                <div class="table-pagination">
                    <span class="pagination-info">Showing 1-10 of 1,248 candidates</span>
                    <div class="pagination-controls">
                        <button class="page-nav disabled"><i class="fa-solid fa-chevron-left"></i></button>
                        <button class="page-num active">1</button>
                        <button class="page-num">2</button>
                        <button class="page-num">3</button>
                        <span class="page-dots">...</span>
                        <button class="page-num">125</button>
                        <button class="page-nav"><i class="fa-solid fa-chevron-right"></i></button>
                    </div>
                </div>
            </div>

            <!-- Tip / Banner Box -->
            <div class="tip-banner">
                <div class="tip-icon">
                    <i class="fa-regular fa-lightbulb"></i>
                </div>
                <div class="tip-content">
                    <h4>Recruitment Tip</h4>
                    <p>Students from Stanford University currently have a 15% higher retention rate in the UI/UX program. Consider reviewing their portfolios first for the upcoming Winter Cohort.</p>
                    <a href="cohort_analytics.php" class="tip-link">View Cohort Analytics &rarr;</a>
                </div>
            </div>

        </main>

        <!-- Shared Footer -->
        <?php 
        if (file_exists('dash_footer.php')) {
            include 'dash_footer.php';
        } else { ?>
            <footer class="dash-footer">
                <p>&copy; 2026 SkillBridge. All rights reserved.</p>
                <div class="footer-links">
                    <a href="#">Help Center</a>
                    <a href="#">Privacy Policy</a>
                    <a href="#">Terms of Service</a>
                </div>
            </footer>
        <?php } ?>
    </div>
</div>

<script src="../../../Assets/JS/applications.js"></script>
</body>
</html>