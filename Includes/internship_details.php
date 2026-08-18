<?php
session_start();

$user = [
    'name' => 'Sarah Jenkins',
    'title' => 'Lead Recruiter',
    'avatar' => '../../../Assets/Images/sarah-avatar.jpg'
];

$internship = [
    'id' => 101,
    'title' => 'Senior Frontend Engineering Intern',
    'company' => 'Stellar Tech Systems',
    'location' => 'Remote (San Francisco, CA)',
    'posted_time' => 'POSTED 12 DAYS AGO',
    'overview_p1' => 'We are seeking a highly motivated Frontend Engineering Intern to join our core product team at Stellar Tech Systems. In this role, you will be instrumental in building the next generation of our enterprise cloud dashboards, working directly with senior architects and product designers.',
    'overview_p2' => 'This is a high-velocity environment where you will move from concept to production code within days. We value clean code, intuitive UX, and the ability to solve complex technical problems with elegant solutions.',
    'responsibilities' => [
        'Develop and maintain reusable UI components using React and Tailwind CSS.',
        'Collaborate with designers to translate Figma mockups into interactive web interfaces.',
        'Optimize applications for maximum speed and scalability across various devices.',
        'Participate in code reviews and advocate for engineering best practices.'
    ],
    'requirements' => [
        'Currently enrolled in a Computer Science or related Bachelor\'s/Master\'s degree.',
        'Proficiency in JavaScript (ES6+), HTML5, and CSS3.',
        'Hands-on experience with modern React frameworks and state management.',
        'Strong understanding of responsive design and browser compatibility.'
    ],
    'skills' => [
        'React.js', 'Tailwind CSS', 'TypeScript', 'Next.js', 
        'UI/UX Principles', 'REST APIs', 'Git Version Control', 'Unit Testing'
    ],
    'pipeline' => [
        'total' => 142,
        'shortlisted' => 48,
        'interviewing' => 12,
        'selected' => 2
    ],
    'engagement' => [
        'views' => '1,248',
        'conversion_rate' => '11.4%',
        'time_to_fill' => '18 Days'
    ],
    'hiring_manager' => [
        'name' => 'Marcus Thorne',
        'title' => 'Hiring Manager'
    ]
];

$recent_candidates = [
    [
        'id' => 1,
        'name' => 'Alex Rivera',
        'university' => 'Stanford University',
        'avatar_type' => 'icon',
        'date_applied' => 'Oct 24, 2023',
        'status' => 'Interviewing',
        'status_class' => 'badge-interviewing',
        'rating' => 4
    ],
    [
        'id' => 2,
        'name' => 'Lila Miller',
        'university' => 'MIT Graduate',
        'avatar_type' => 'initials',
        'initials' => 'LM',
        'date_applied' => 'Oct 26, 2023',
        'status' => 'Applied',
        'status_class' => 'badge-applied',
        'rating' => 5
    ]
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($internship['title']) ?> - SkillBridge</title>
    <link rel="stylesheet" href="../../../Assets/CSS/internship_details.css">
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
            <a href="internships.php" class="active"><i class="fa-solid fa-briefcase"></i> Internships</a>
            <a href="applications.php"><i class="fa-solid fa-file-lines"></i> Applications</a>
            <a href="candidates.php"><i class="fa-solid fa-user-group"></i> Candidates</a>
            <a href="interviews.php"><i class="fa-regular fa-calendar"></i> Interviews</a>
            <a href="reports.php"><i class="fa-solid fa-chart-line"></i> Reports & Analytics</a>
            <a href="notifications.php"><i class="fa-regular fa-bell"></i> Notifications</a>
            <a href="settings.php"><i class="fa-solid fa-gear"></i> Settings</a>
        </nav>
        <div class="sidebar-actions">
            <a href="../../../Auth/logout.php" class="btn-logout"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
        </div>
    </aside>

    <!-- Main Content Area -->
    <div class="main-content">
        <!-- Top Header Navigation -->
        <header class="dash-header">
            <div class="header-search">
                <i class="fa-solid fa-magnifying-glass search-icon"></i>
                <input type="text" placeholder="Search internships or candidates...">
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
                    <img src="<?= htmlspecialchars($user['avatar']) ?>" alt="Sarah Jenkins" class="header-avatar" onerror="this.src='https://ui-avatars.com/api/?name=Sarah+Jenkins&background=082544&color=fff'">
                </div>
            </div>
        </header>

        <!-- Main Body Content -->
        <main class="page-body">
            <!-- Back Button Link -->
            <a href="internships.php" class="back-link">
                <i class="fa-solid fa-arrow-left"></i> Back to Internships
            </a>

            <!-- Internship Title Banner -->
            <div class="details-header-card">
                <div class="header-left-info">
                    <h1 class="internship-title"><?= htmlspecialchars($internship['title']) ?></h1>
                    <div class="internship-meta">
                        <span><i class="fa-regular fa-building"></i> <?= htmlspecialchars($internship['company']) ?></span>
                        <span><i class="fa-solid fa-location-dot"></i> <?= htmlspecialchars($internship['location']) ?></span>
                        <span class="badge-posted"><?= htmlspecialchars($internship['posted_time']) ?></span>
                    </div>
                </div>
                <div class="header-action-buttons">
                    <a href="edit_internship.php?id=<?= $internship['id'] ?>" class="btn-secondary">
                        <i class="fa-solid fa-pen-to-square"></i> Edit Internship
                    </a>
                    <a href="applications.php?internship_id=<?= $internship['id'] ?>" class="btn-primary">
                        <i class="fa-regular fa-eye"></i> View Applications
                    </a>
                </div>
            </div>

            <!-- Two Column Layout Area -->
            <div class="details-grid">
                <!-- Left Main Content Column -->
                <div class="left-column">
                    <!-- Overview Section -->
                    <div class="content-card">
                        <h3 class="card-title"><i class="fa-regular fa-circle-question"></i> Internship Overview</h3>
                        <p class="desc-text"><?= htmlspecialchars($internship['overview_p1']) ?></p>
                        <p class="desc-text"><?= htmlspecialchars($internship['overview_p2']) ?></p>
                    </div>

                    <!-- Responsibilities & Requirements Split -->
                    <div class="dual-column-cards">
                        <div class="content-card">
                            <h3 class="card-title"><i class="fa-regular fa-circle-check"></i> Responsibilities</h3>
                            <ul class="bullet-list">
                                <?php foreach ($internship['responsibilities'] as $item): ?>
                                    <li><?= htmlspecialchars($item) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>

                        <div class="content-card">
                            <h3 class="card-title"><i class="fa-solid fa-gear"></i> Requirements</h3>
                            <ul class="bullet-list">
                                <?php foreach ($internship['requirements'] as $item): ?>
                                    <li><?= htmlspecialchars($item) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>

                    <!-- Core Skills Section -->
                    <div class="content-card">
                        <h3 class="card-title"><i class="fa-solid fa-bullseye"></i> Core Skills</h3>
                        <div class="skills-tags-list">
                            <?php foreach ($internship['skills'] as $skill): ?>
                                <span class="skill-tag"><?= htmlspecialchars($skill) ?></span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- Right Sidebar Stats Column -->
                <div class="right-column">
                    <!-- Application Pipeline Stats Card -->
                    <div class="content-card border-top-accent">
                        <h3 class="card-title border-bottom-title">Application Pipeline</h3>
                        
                        <div class="pipeline-summary-box">
                            <div class="summary-icon-text">
                                <i class="fa-solid fa-user-group"></i>
                                <span>Total Applicants</span>
                            </div>
                            <span class="summary-total"><?= $internship['pipeline']['total'] ?></span>
                        </div>

                        <div class="progress-list">
                            <div class="progress-item">
                                <div class="progress-label-row">
                                    <span>Shortlisted</span>
                                    <span class="val-dark"><?= $internship['pipeline']['shortlisted'] ?></span>
                                </div>
                                <div class="progress-bar-bg">
                                    <div class="progress-fill fill-dark" style="width: 35%;"></div>
                                </div>
                            </div>

                            <div class="progress-item">
                                <div class="progress-label-row">
                                    <span>Interviewing</span>
                                    <span class="val-orange"><?= $internship['pipeline']['interviewing'] ?></span>
                                </div>
                                <div class="progress-bar-bg">
                                    <div class="progress-fill fill-orange" style="width: 15%;"></div>
                                </div>
                            </div>

                            <div class="progress-item">
                                <div class="progress-label-row">
                                    <span>Selected</span>
                                    <span class="val-green"><?= $internship['pipeline']['selected'] ?></span>
                                </div>
                                <div class="progress-bar-bg">
                                    <div class="progress-fill fill-green" style="width: 5%;"></div>
                                </div>
                            </div>
                        </div>

                        <a href="manage_applicants.php?id=<?= $internship['id'] ?>" class="btn-manage-applicants">
                            Manage Applicants <i class="fa-solid fa-arrow-right"></i>
                        </a>
                    </div>

                    <!-- Engagement Card -->
                    <div class="content-card">
                        <h3 class="card-title border-bottom-title">Engagement</h3>
                        <div class="engagement-list">
                            <div class="engagement-row">
                                <span class="eng-label">Profile Views</span>
                                <span class="eng-value"><?= $internship['engagement']['views'] ?></span>
                            </div>
                            <div class="engagement-row">
                                <span class="eng-label">Conversion Rate</span>
                                <span class="eng-value rate-accent"><?= $internship['engagement']['conversion_rate'] ?></span>
                            </div>
                            <div class="engagement-row">
                                <span class="eng-label">Time to Fill (Est.)</span>
                                <span class="eng-value"><?= $internship['engagement']['time_to_fill'] ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- Hiring Manager Card -->
                    <div class="content-card manager-card">
                        <div class="manager-info">
                            <img src="../../../Assets/Images/marcus-avatar.jpg" alt="Marcus Thorne" class="manager-avatar" onerror="this.src='https://ui-avatars.com/api/?name=Marcus+Thorne&background=082544&color=fff'">
                            <div>
                                <h4 class="manager-name"><?= htmlspecialchars($internship['hiring_manager']['title']) ?></h4>
                                <p class="manager-sub"><?= htmlspecialchars($internship['hiring_manager']['name']) ?></p>
                            </div>
                        </div>
                        <a href="mailto:marcus@stellar.com" class="icon-mail-btn"><i class="fa-regular fa-envelope"></i></a>
                    </div>
                </div>
            </div>

            <!-- Recent Candidates Table Section -->
            <div class="content-card table-card-section">
                <div class="table-card-header">
                    <h3>Recent Candidates</h3>
                    <a href="candidates.php" class="link-view-all">View All Candidates</a>
                </div>

                <div class="table-responsive">
                    <table class="candidates-table">
                        <thead>
                            <tr>
                                <th>CANDIDATE</th>
                                <th>DATE APPLIED</th>
                                <th>STATUS</th>
                                <th>RATING</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_candidates as $cand): ?>
                                <tr>
                                    <td>
                                        <div class="candidate-profile-cell">
                                            <?php if ($cand['avatar_type'] === 'initials'): ?>
                                                <div class="avatar-initials"><?= $cand['initials'] ?></div>
                                            <?php else: ?>
                                                <div class="avatar-icon-box"><i class="fa-solid fa-user-astronaut"></i></div>
                                            <?php endif; ?>
                                            <div>
                                                <span class="cand-name"><?= htmlspecialchars($cand['name']) ?></span>
                                                <span class="cand-univ"><?= htmlspecialchars($cand['university']) ?></span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="cell-date"><?= htmlspecialchars($cand['date_applied']) ?></td>
                                    <td>
                                        <span class="badge-status <?= $cand['status_class'] ?>">
                                            <?= htmlspecialchars($cand['status']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="star-rating">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <i class="fa-solid fa-star <?= $i <= $cand['rating'] ? 'filled' : 'empty' ?>"></i>
                                            <?php endfor; ?>
                                        </div>
                                    </td>
                                    <td class="cell-action">
                                        <button class="btn-more-options"><i class="fa-solid fa-ellipsis-vertical"></i></button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>

        <!-- Footer -->
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

<script src="../../../Assets/JS/internship_details.js"></script>
</body>
</html>