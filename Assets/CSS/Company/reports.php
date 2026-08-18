<?php
session_start();

// User & Analytics Overview Data
$user = [
    'name' => 'Sarah Jenkins',
    'title' => 'SR. RECRUITER',
    'avatar' => '../../../Assets/Images/admin-avatar.jpg'
];

$top_metrics = [
    'total_applications' => '2,482',
    'applications_change' => '+12.5%',
    'selection_rate' => '4.2%',
    'selection_change' => '-0.8%',
    'active_internships' => '48',
    'depts_count' => 'Across 12 depts',
    'completion_rate' => '96%',
    'completion_change' => '+2%'
];

$skills_distribution = [
    ['label' => 'React / Frontend', 'value' => '40%', 'color' => '#0f172a'],
    ['label' => 'Python / Backend', 'value' => '30%', 'color' => '#f97316'],
    ['label' => 'UX/UI Design', 'value' => '20%', 'color' => '#38bdf8'],
    ['label' => 'Product Management', 'value' => '10%', 'color' => '#64748b']
];

$department_trends = [
    [
        'name' => 'Software Engineering',
        'sub' => 'FULL STACK FOCUS',
        'icon' => 'fa-code',
        'icon_bg' => '#e0f2fe',
        'icon_color' => '#0284c7',
        'postings' => 12,
        'applicants' => '1,240',
        'hired' => 15,
        'goal' => 20,
        'status' => 'ON TRACK',
        'status_class' => 'badge-track',
        'velocity' => '+18%',
        'velocity_class' => 'text-green'
    ],
    [
        'name' => 'Design & Creative',
        'sub' => 'UX/VISUAL DESIGN',
        'icon' => 'fa-palette',
        'icon_bg' => '#ffedd5',
        'icon_color' => '#c2410c',
        'postings' => 4,
        'applicants' => '420',
        'hired' => 2,
        'goal' => 5,
        'status' => 'INTERVIEWING',
        'status_class' => 'badge-interviewing',
        'velocity' => 'Stable',
        'velocity_class' => 'text-muted'
    ],
    [
        'name' => 'Data Science',
        'sub' => 'ML & ANALYTICS',
        'icon' => 'fa-chart-line',
        'icon_bg' => '#f1f5f9',
        'icon_color' => '#475569',
        'postings' => 2,
        'applicants' => '115',
        'hired' => 3,
        'goal' => 3,
        'status' => 'COMPLETED',
        'status_class' => 'badge-completed',
        'velocity' => '+5%',
        'velocity_class' => 'text-green'
    ]
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics & Performance - SkillBridge</title>
    <link rel="stylesheet" href="../../../Assets/CSS/dashboard.css">
    <link rel="stylesheet" href="../../../Assets/CSS/reports.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Chart.js CDN for Analytics Visualization -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
                <a href="interviews.php"><i class="fa-regular fa-calendar"></i> Interviews</a>
                <a href="reports.php" class="active"><i class="fa-solid fa-chart-line"></i> Reports & Analytics</a>
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
                <input type="text" placeholder="Search data, candidates, reports...">
            </div>
            <div class="header-right">
                <button class="icon-btn notification-btn"><i class="fa-regular fa-bell"></i></button>
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
            <!-- Page Header & Filter Toolbar -->
            <div class="page-header-actions">
                <div>
                    <h1 class="page-title">Analytics & Performance</h1>
                    <p class="page-subtitle">Real-time overview of your recruitment pipeline and intern success metrics.</p>
                </div>

                <div class="header-toolbar">
                    <div class="time-range-picker">
                        <button class="btn-time-range active">Last 30 Days</button>
                        <button class="btn-time-range">Quarterly</button>
                        <button class="btn-time-range">Yearly</button>
                    </div>

                    <div class="export-actions">
                        <button class="btn-export-navy"><i class="fa-solid fa-download"></i> Export PDF</button>
                        <button class="btn-export-outline"><i class="fa-regular fa-file-excel"></i> CSV</button>
                    </div>
                </div>
            </div>

            <!-- Top Metric Cards Grid -->
            <div class="metrics-grid">
                <!-- Total Applications -->
                <div class="card metric-card">
                    <span class="metric-label">TOTAL APPLICATIONS</span>
                    <div class="metric-value-row">
                        <span class="metric-value"><?= htmlspecialchars($top_metrics['total_applications']) ?></span>
                        <span class="pill-change positive"><?= htmlspecialchars($top_metrics['applications_change']) ?></span>
                    </div>
                    <div class="metric-bar-accent bg-navy"></div>
                </div>

                <!-- Selection Rate -->
                <div class="card metric-card">
                    <span class="metric-label">SELECTION RATE</span>
                    <div class="metric-value-row">
                        <span class="metric-value"><?= htmlspecialchars($top_metrics['selection_rate']) ?></span>
                        <span class="pill-change negative"><?= htmlspecialchars($top_metrics['selection_change']) ?></span>
                    </div>
                    <div class="metric-bar-accent bg-orange"></div>
                </div>

                <!-- Active Internships -->
                <div class="card metric-card">
                    <span class="metric-label">ACTIVE INTERNSHIPS</span>
                    <div class="metric-value-row">
                        <span class="metric-value"><?= htmlspecialchars($top_metrics['active_internships']) ?></span>
                        <span class="sub-text"><?= htmlspecialchars($top_metrics['depts_count']) ?></span>
                    </div>
                    <div class="metric-bar-accent bg-navy"></div>
                </div>

                <!-- Completion Rate -->
                <div class="card metric-card">
                    <span class="metric-label">COMPLETION RATE</span>
                    <div class="metric-value-row">
                        <span class="metric-value"><?= htmlspecialchars($top_metrics['completion_rate']) ?></span>
                        <span class="pill-change positive"><?= htmlspecialchars($top_metrics['completion_change']) ?></span>
                    </div>
                    <div class="metric-bar-accent bg-orange"></div>
                </div>
            </div>

            <!-- Analytics Visualizations Grid -->
            <div class="charts-grid">
                <!-- Applications Over Time Area Chart -->
                <div class="card chart-card flex-2">
                    <div class="card-title-bar">
                        <div>
                            <h2>Applications Over Time</h2>
                            <p class="card-subtitle">Volume of submissions per week for all active roles</p>
                        </div>
                        <div class="chart-legend">
                            <span class="legend-item"><span class="legend-dot bg-navy"></span> Technical Roles</span>
                            <span class="legend-item"><span class="legend-dot bg-orange"></span> Creative Roles</span>
                        </div>
                    </div>
                    <div class="chart-container">
                        <canvas id="applicationsChart"></canvas>
                    </div>
                </div>

                <!-- Skills Distribution Doughnut Chart -->
                <div class="card chart-card flex-1">
                    <div class="card-title-bar">
                        <div>
                            <h2>Skills Distribution</h2>
                            <p class="card-subtitle">Primary expertise of top 10% applicants</p>
                        </div>
                    </div>
                    
                    <div class="doughnut-wrapper">
                        <canvas id="skillsChart"></canvas>
                        <div class="doughnut-center-text">
                            <span class="center-number">845</span>
                            <span class="center-label">Qualified</span>
                        </div>
                    </div>

                    <div class="skills-list">
                        <?php foreach ($skills_distribution as $skill): ?>
                            <div class="skill-row">
                                <span class="skill-label-group">
                                    <span class="skill-dot" style="background-color: <?= $skill['color'] ?>;"></span>
                                    <?= htmlspecialchars($skill['label']) ?>
                                </span>
                                <span class="skill-val"><?= htmlspecialchars($skill['value']) ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Hiring Trends Table -->
            <div class="card table-card">
                <div class="table-header">
                    <div>
                        <h2>Hiring Trends by Department</h2>
                        <p class="card-subtitle">Monthly velocity and conversion benchmarks</p>
                    </div>
                    <a href="#" class="link-full-details">View Full Details</a>
                </div>

                <div class="table-responsive">
                    <table class="trends-table">
                        <thead>
                            <tr>
                                <th>DEPARTMENT</th>
                                <th>POSTINGS</th>
                                <th>APPLICANTS</th>
                                <th>HIRE GOAL</th>
                                <th>STATUS</th>
                                <th>VELOCITY</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($department_trends as $dept): ?>
                                <tr>
                                    <td>
                                        <div class="dept-cell">
                                            <div class="dept-icon" style="background-color: <?= $dept['icon_bg'] ?>; color: <?= $dept['icon_color'] ?>;">
                                                <i class="fa-solid <?= $dept['icon'] ?>"></i>
                                            </div>
                                            <div>
                                                <div class="dept-title"><?= htmlspecialchars($dept['name']) ?></div>
                                                <div class="dept-sub"><?= htmlspecialchars($dept['sub']) ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="font-bold"><?= $dept['postings'] ?></td>
                                    <td class="font-bold"><?= $dept['applicants'] ?></td>
                                    <td>
                                        <div class="goal-cell">
                                            <span class="goal-text"><strong><?= $dept['hired'] ?></strong> / <?= $dept['goal'] ?></span>
                                            <div class="mini-progress-bar">
                                                <div class="mini-progress-fill" style="width: <?= min(100, ($dept['hired'] / $dept['goal']) * 100) ?>%;"></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge <?= $dept['status_class'] ?>"><?= htmlspecialchars($dept['status']) ?></span>
                                    </td>
                                    <td class="<?= $dept['velocity_class'] ?> font-bold">
                                        <?php if ($dept['velocity_class'] === 'text-green'): ?>
                                            <i class="fa-solid fa-chart-line"></i>
                                        <?php elseif ($dept['velocity_class'] === 'text-muted'): ?>
                                            &rarr;
                                        <?php endif; ?>
                                        <?= htmlspecialchars($dept['velocity']) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Bottom Highlights Banner Cards -->
            <div class="highlights-grid">
                <!-- Efficiency Metric Box -->
                <div class="card metric-highlight-card">
                    <span class="highlight-label">EFFICIENCY METRIC</span>
                    <h2>Average Time to Hire</h2>
                    <div class="highlight-val-wrapper">
                        <span class="highlight-value">12.5</span>
                        <div class="highlight-sub">
                            <span>Days</span>
                            <span class="sub-muted">vs 18d Avg</span>
                        </div>
                    </div>
                </div>

                <!-- Impact Report Card -->
                <div class="card impact-report-card">
                    <div class="impact-content">
                        <span class="impact-label">IMPACT REPORT</span>
                        <h2>SkillBridge Certified interns show 40% higher retention rates.</h2>
                    </div>
                    <a href="#" class="btn-download-whitepaper">Download Whitepaper</a>
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

<script src="../../../Assets/JS/reports.js"></script>
</body>
</html>