<?php
session_start();

// User & Dashboard State Data
$user = [
    'name' => 'Sarah Jenkins',
    'title' => 'Senior Recruiter',
    'avatar' => '../../../Assets/Images/admin-avatar.jpg'
];

$metrics = [
    'todays_schedule' => '08',
    'todays_change' => '+2 from yesterday',
    'pending_feedback' => '14',
    'upcoming_this_week' => '32'
];

$interviews = [
    [
        'id' => 1,
        'student_name' => 'Alex Rivera',
        'student_role' => 'Computer Science Senior',
        'student_avatar' => '../../../Assets/Images/candidates/alex.jpg',
        'internship' => 'Software Engineering Intern',
        'team' => 'Backend & Devops Team',
        'date' => 'Oct 24, 2023',
        'time' => '10:30 AM - 11:30 AM',
        'status' => 'Interviewing'
    ],
    [
        'id' => 2,
        'student_name' => 'Elena Sorova',
        'student_role' => 'UX/UI Design Intern',
        'student_avatar' => '../../../Assets/Images/candidates/elena.jpg',
        'internship' => 'Product Design Fellowship',
        'team' => 'Mobile Design Squad',
        'date' => 'Oct 25, 2023',
        'time' => '02:00 PM - 02:45 PM',
        'status' => 'Applied'
    ],
    [
        'id' => 3,
        'student_name' => 'Jordan Smith',
        'student_role' => 'Business Analytics',
        'student_avatar' => '../../../Assets/Images/candidates/jordan.jpg',
        'internship' => 'Data Analyst Intern',
        'team' => 'Market Intelligence',
        'date' => 'Oct 26, 2023',
        'time' => '11:00 AM - 12:00 PM',
        'status' => 'Hired'
    ]
];

// Status badge CSS helper
function getStatusClass($status) {
    switch (strtolower($status)) {
        case 'interviewing':
            return 'badge-interviewing';
        case 'applied':
            return 'badge-applied';
        case 'hired':
            return 'badge-hired';
        default:
            return 'badge-default';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Interview Management - SkillBridge</title>
    <link rel="stylesheet" href="../../../Assets/CSS/dashboard.css">
    <link rel="stylesheet" href="../../../Assets/CSS/interviews.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<div class="dash-wrapper">
    <!-- Sidebar Navigation -->
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
                <a href="applications.php"><i class="fa-solid fa-file-lines"></i> Applications</a>
                <a href="candidates.php"><i class="fa-solid fa-user-group"></i> Candidates</a>
                <a href="interviews.php" class="active"><i class="fa-regular fa-calendar"></i> Interviews</a>
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
                <input type="text" placeholder="Search interviews, candidates...">
            </div>
            <div class="header-right">
                <button class="icon-btn notification-btn"><i class="fa-regular fa-bell"></i></button>
                <button class="icon-btn help-btn"><i class="fa-regular fa-circle-question"></i></button>
                <div class="user-profile">
                    <div class="user-info">
                        <span class="user-name"><?= htmlspecialchars($user['name']) ?></span>
                        <span class="user-role"><?= htmlspecialchars($user['title']) ?></span>
                    </div>
                    <img src="<?= htmlspecialchars($user['avatar']) ?>" alt="User Avatar" class="header-avatar" onerror="this.src='https://ui-avatars.com/api/?name=Sarah+Jenkins&background=082544&color=fff'">
                </div>
            </div>
        </header>

        <!-- Main Body -->
        <main class="page-body">
            <!-- Page Header Bar -->
            <div class="page-header-actions">
                <div>
                    <h1 class="page-title">Interview Management</h1>
                    <p class="page-subtitle">Track and coordinate your upcoming talent assessment pipelines.</p>
                </div>
                <button class="btn-schedule"><i class="fa-regular fa-calendar-plus"></i> Schedule Interview</button>
            </div>

            <!-- Metrics Cards Grid -->
            <div class="metrics-grid">
                <div class="card metric-card border-orange">
                    <span class="metric-label">TODAY'S SCHEDULE</span>
                    <div class="metric-value"><?= htmlspecialchars($metrics['todays_schedule']) ?></div>
                    <span class="metric-sub text-green"><strong>+2</strong> from yesterday</span>
                </div>

                <div class="card metric-card border-navy">
                    <span class="metric-label">PENDING FEEDBACK</span>
                    <div class="metric-value"><?= htmlspecialchars($metrics['pending_feedback']) ?></div>
                    <span class="metric-sub text-muted"><i class="fa-regular fa-clock"></i> Due by EOD</span>
                </div>

                <div class="card metric-card border-blue">
                    <span class="metric-label">UPCOMING THIS WEEK</span>
                    <div class="metric-value"><?= htmlspecialchars($metrics['upcoming_this_week']) ?></div>
                    <div class="progress-bar-container">
                        <div class="progress-bar-fill" style="width: 70%;"></div>
                    </div>
                </div>
            </div>

            <!-- Table Container Card -->
            <div class="card table-card">
                <div class="table-header">
                    <h2>Upcoming Interviews</h2>
                    <div class="table-actions">
                        <button class="icon-btn-filter" title="Filter"><i class="fa-solid fa-sliders"></i></button>
                        <button class="icon-btn-filter" title="Export"><i class="fa-solid fa-download"></i></button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="interviews-table">
                        <thead>
                            <tr>
                                <th>STUDENT</th>
                                <th>INTERNSHIP</th>
                                <th>DATE & TIME</th>
                                <th>STATUS</th>
                                <th class="text-right">ACTIONS</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($interviews as $row): ?>
                                <tr>
                                    <td>
                                        <div class="student-info-cell">
                                            <div class="avatar-wrapper">
                                                <img src="<?= htmlspecialchars($row['student_avatar']) ?>" alt="<?= htmlspecialchars($row['student_name']) ?>" class="candidate-avatar" onerror="this.src='https://ui-avatars.com/api/?name=<?= urlencode($row['student_name']) ?>&background=082544&color=fff'">
                                                <span class="status-dot"></span>
                                            </div>
                                            <div>
                                                <div class="candidate-name"><?= htmlspecialchars($row['student_name']) ?></div>
                                                <div class="candidate-sub"><?= htmlspecialchars($row['student_role']) ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="internship-title"><?= htmlspecialchars($row['internship']) ?></div>
                                        <div class="internship-team"><?= htmlspecialchars($row['team']) ?></div>
                                    </td>
                                    <td>
                                        <div class="date-text"><?= htmlspecialchars($row['date']) ?></div>
                                        <div class="time-text"><i class="fa-regular fa-clock"></i> <?= htmlspecialchars($row['time']) ?></div>
                                    </td>
                                    <td>
                                        <span class="badge <?= getStatusClass($row['status']) ?>"><?= htmlspecialchars($row['status']) ?></span>
                                    </td>
                                    <td class="text-right">
                                        <div class="action-buttons">
                                            <button class="btn-action" title="View Details"><i class="fa-regular fa-eye"></i></button>
                                            <button class="btn-action" title="Edit Interview"><i class="fa-solid fa-pen-to-square"></i></button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Table Pagination -->
                <div class="table-pagination">
                    <span class="pagination-info">Showing 1-10 of 42 interviews</span>
                    <div class="pagination-controls">
                        <button class="btn-pagination" disabled>Previous</button>
                        <button class="btn-pagination">Next</button>
                    </div>
                </div>
            </div>
        </main>

        <!-- Footer -->
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

<script src="../../../Assets/JS/interviews.js"></script>
</body>
</html>