<?php
include "../../../Config/db.php";
include "../../../Session/session.php";

is_logged_in();
$user = current_user();

include "../../../Includes/company_sidebar.php";
include "../../../Includes/dash_header.php";

$company_display_name = (!empty($name) && $name !== 'User') ? $name : ($user['username'] ?? $user['Name'] ?? 'ABC Technologies');
?>

<link rel="stylesheet" href="../../../Assets/CSS/Company/dashboard.css?v=<?php echo time(); ?>">

<main class="content">
    <div class="company-dashboard-container">
        <div class="company-dashboard-header">
            <div class="company-welcome-text">
                <h1>Welcome back, <?php echo htmlspecialchars($company_display_name); ?></h1>
                <p>Here is an overview of your recruitment momentum today.</p>
            </div>

            <div class="company-badge-card">
                <div class="company-logo-badge">
                    <?php if (!empty($image_src)): ?>
                        <img src="<?php echo $image_src; ?>?v=<?php echo time(); ?>" alt="Company Logo" style="width:100%;height:100%;border-radius:12px;object-fit:cover;">
                    <?php else: ?>
                        <span class="material-symbols-outlined">corporate_fare</span>
                    <?php endif; ?>
                </div>
                <div class="company-badge-info">
                    <div class="company-badge-name">
                        <h3><?php echo htmlspecialchars($company_display_name); ?></h3>
                        <span class="verified-icon material-symbols-outlined" title="Verified Employer">verified</span>
                    </div>
                    <div class="company-badge-meta">
                        <span class="meta-tag">
                            <span class="material-symbols-outlined">location_on</span> San Francisco, CA
                        </span>
                        <span class="meta-tag">
                            <span class="material-symbols-outlined">group</span> 500-1000 Employees
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="company-stats-grid">
            <!-- Active Internships -->
            <div class="company-stat-card accent-navy">
                <div class="stat-top-row">
                    <div class="stat-icon-box navy">
                        <span class="material-symbols-outlined">work</span>
                    </div>
                    <span class="stat-badge green">+2 this week</span>
                </div>
                <div class="stat-label">Active Internships</div>
                <div class="stat-value">12</div>
            </div>

            <!--Total Applications -->
            <div class="company-stat-card accent-amber">
                <div class="stat-top-row">
                    <div class="stat-icon-box amber">
                        <span class="material-symbols-outlined">description</span>
                    </div>
                    <span class="stat-badge amber">+42 new</span>
                </div>
                <div class="stat-label">Total Applications</div>
                <div class="stat-value">356</div>
            </div>

            <!--Shortlisted -->
            <div class="company-stat-card accent-blue">
                <div class="stat-top-row">
                    <div class="stat-icon-box blue">
                        <span class="material-symbols-outlined">how_to_reg</span>
                    </div>
                    <span class="stat-meta-text">13% conversion</span>
                </div>
                <div class="stat-label">Shortlisted</div>
                <div class="stat-value">48</div>
            </div>

            <!--Interviews -->
            <div class="company-stat-card accent-brown">
                <div class="stat-top-row">
                    <div class="stat-icon-box brown">
                        <span class="material-symbols-outlined">calendar_today</span>
                    </div>
                    <span class="stat-meta-text">3 today</span>
                </div>
                <div class="stat-label">Interviews</div>
                <div class="stat-value">15</div>
            </div>
        </div>

        <div class="company-dashboard-body">

            <!--LEFT COLUMN-->
            <div class="company-main-col">

                <!-- Recent Applications Card -->
                <div class="company-card applications-card">
                    <div class="company-card-header">
                        <h2>Recent Applications</h2>
                        <a href="university.php" class="view-all-link">View All</a>
                    </div>
                    <div class="table-responsive">
                        <table class="company-table">
                            <thead>
                                <tr>
                                    <th>Candidate</th>
                                    <th>University</th>
                                    <th>Position</th>
                                    <th>Status</th>
                                    <th style="text-align: right;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <div class="candidate-cell">
                                            <img class="candidate-avatar" src="https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=100&auto=format&fit=crop&q=80" alt="Maya Sharma" onerror="this.outerHTML='<div class=\'candidate-avatar-placeholder\'>MS</div>'">
                                            <span class="candidate-name">Maya Sharma</span>
                                        </div>
                                    </td>
                                    <td class="univ-cell">Stanford University</td>
                                    <td class="position-cell">Software Engineer</td>
                                    <td><span class="status-pill status-new">NEW</span></td>
                                    <td style="text-align: right;">
                                        <button class="action-menu-btn" title="Actions" aria-label="Candidate actions">
                                            <span class="material-symbols-outlined">more_vert</span>
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="candidate-cell">
                                            <img class="candidate-avatar" src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=100&auto=format&fit=crop&q=80" alt="David Chen" onerror="this.outerHTML='<div class=\'candidate-avatar-placeholder\'>DC</div>'">
                                            <span class="candidate-name">David Chen</span>
                                        </div>
                                    </td>
                                    <td class="univ-cell">MIT</td>
                                    <td class="position-cell">Data Analyst</td>
                                    <td><span class="status-pill status-shortlisted">SHORTLISTED</span></td>
                                    <td style="text-align: right;">
                                        <button class="action-menu-btn" title="Actions" aria-label="Candidate actions">
                                            <span class="material-symbols-outlined">more_vert</span>
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="candidate-cell">
                                            <img class="candidate-avatar" src="https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=100&auto=format&fit=crop&q=80" alt="Sarah Jenkins" onerror="this.outerHTML='<div class=\'candidate-avatar-placeholder\'>SJ</div>'">
                                            <span class="candidate-name">Sarah Jenkins</span>
                                        </div>
                                    </td>
                                    <td class="univ-cell">UC Berkeley</td>
                                    <td class="position-cell">Product Design</td>
                                    <td><span class="status-pill status-selected">SELECTED</span></td>
                                    <td style="text-align: right;">
                                        <button class="action-menu-btn" title="Actions" aria-label="Candidate actions">
                                            <span class="material-symbols-outlined">more_vert</span>
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="candidate-cell">
                                            <img class="candidate-avatar" src="https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=100&auto=format&fit=crop&q=80" alt="Leo Martinez" onerror="this.outerHTML='<div class=\'candidate-avatar-placeholder\'>LM</div>'">
                                            <span class="candidate-name">Leo Martinez</span>
                                        </div>
                                    </td>
                                    <td class="univ-cell">Georgia Tech</td>
                                    <td class="position-cell">Backend Dev</td>
                                    <td><span class="status-pill status-rejected">REJECTED</span></td>
                                    <td style="text-align: right;">
                                        <button class="action-menu-btn" title="Actions" aria-label="Candidate actions">
                                            <span class="material-symbols-outlined">more_vert</span>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Recent Internships Section -->
                <div class="recent-internships-section">
                    <div class="section-header-row">
                        <h2>Recent Internships</h2>
                        <div class="slider-controls">
                            <button class="slider-btn prev" type="button" aria-label="Previous internships">
                                <span class="material-symbols-outlined">chevron_left</span>
                            </button>
                            <button class="slider-btn next" type="button" aria-label="Next internships">
                                <span class="material-symbols-outlined">chevron_right</span>
                            </button>
                        </div>
                    </div>

                    <div class="internships-grid">
                        <!-- Internship Card 1 -->
                        <div class="internship-card">
                            <div class="internship-card-top">
                                <div class="internship-icon-box navy">
                                    <span class="material-symbols-outlined">terminal</span>
                                </div>
                                <span class="status-pill status-active">Active</span>
                            </div>
                            <div class="internship-card-info">
                                <h3>Full Stack Developer</h3>
                                <p>Building next-generation enterprise tools using React, Node.js, and modern cloud architectures...</p>
                            </div>
                            <div class="internship-card-footer">
                                <span class="internship-meta-item">
                                    <span class="material-symbols-outlined">group</span> 12 Applicants
                                </span>
                                <span class="internship-meta-item">
                                    <span class="material-symbols-outlined">schedule</span> 3 Days left
                                </span>
                            </div>
                        </div>

                        <!-- Internship Card 2 -->
                        <div class="internship-card">
                            <div class="internship-card-top">
                                <div class="internship-icon-box orange">
                                    <span class="material-symbols-outlined">bar_chart</span>
                                </div>
                                <span class="status-pill status-active">Active</span>
                            </div>
                            <div class="internship-card-info">
                                <h3>Data Science Intern</h3>
                                <p>Analyzing complex recruitment datasets to optimize talent pipeline performance and predictive models...</p>
                            </div>
                            <div class="internship-card-footer">
                                <span class="internship-meta-item">
                                    <span class="material-symbols-outlined">group</span> 85 Applicants
                                </span>
                                <span class="internship-meta-item">
                                    <span class="material-symbols-outlined">schedule</span> 1 Week left
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- RIGHT COLUMN-->
            <div class="company-side-col">

                <!-- Upcoming Interviews Card -->
                <div class="company-card upcoming-interviews-card">
                    <div class="company-card-header">
                        <h2>
                            Upcoming Interviews
                            <span class="material-symbols-outlined">calendar_month</span>
                        </h2>
                    </div>

                    <div class="interviews-list">
                        <!-- Interview 1 -->
                        <div class="interview-item">
                            <div class="interview-date-box">
                                <span class="date-day">24</span>
                                <span class="date-month">OCT</span>
                            </div>
                            <div class="interview-info">
                                <h4>Interview with Maya Sharma</h4>
                                <div class="interview-time">10:00 AM - 11:00 AM (Video Call)</div>
                                <div class="interview-person">
                                    <img class="interviewer-avatar" src="https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=80&auto=format&fit=crop&q=80" alt="Alex Rivera" onerror="this.style.display='none'">
                                    <span>Interviewer: Alex Rivera</span>
                                </div>
                            </div>
                        </div>

                        <!-- Interview 2 -->
                        <div class="interview-item">
                            <div class="interview-date-box">
                                <span class="date-day">24</span>
                                <span class="date-month">OCT</span>
                            </div>
                            <div class="interview-info">
                                <h4>Interview with David Chen</h4>
                                <div class="interview-time">02:30 PM - 03:30 PM (Office HQ)</div>
                                <div class="interview-person">
                                    <img class="interviewer-avatar" src="https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?w=80&auto=format&fit=crop&q=80" alt="Sarah Lee" onerror="this.style.display='none'">
                                    <span>Interviewer: Sarah Lee</span>
                                </div>
                            </div>
                        </div>

                        <!-- Interview 3 -->
                        <div class="interview-item">
                            <div class="interview-date-box">
                                <span class="date-day">25</span>
                                <span class="date-month">OCT</span>
                            </div>
                            <div class="interview-info">
                                <h4>Panel Interview: 3 Candidates</h4>
                                <div class="interview-time">09:00 AM - 12:00 PM (Conference B)</div>
                                <div class="avatar-stack">
                                    <img class="stack-avatar" src="https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=60&auto=format&fit=crop&q=80" alt="Candidate 1">
                                    <img class="stack-avatar" src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=60&auto=format&fit=crop&q=80" alt="Candidate 2">
                                    <img class="stack-avatar" src="https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=60&auto=format&fit=crop&q=80" alt="Candidate 3">
                                </div>
                            </div>
                        </div>
                    </div>

                    <a href="internships.php" class="view-all-schedule-btn">View All Schedule</a>
                </div>

                <!-- Hiring Goal Card -->
                <div class="hiring-goal-card">
                    <h3>Hiring Goal</h3>
                    <p>You are 65% towards your quarterly intern intake goal.</p>

                    <div class="goal-progress-wrap">
                        <div class="goal-progress-bar">
                            <div class="goal-progress-fill"></div>
                        </div>
                        <div class="goal-labels">
                            <span>13 Hired</span>
                            <span>20 Goal</span>
                        </div>
                    </div>

                    <button class="btn-boost-listings" type="button" onclick="window.location.href='Usermanagemen.php'">Boost Listings</button>
                </div>

            </div>

        </div>
        <div class="company-dashboard-footer">
            <div>&copy; 2026 SkillBridge. All rights reserved.</div>
            <div class="company-footer-links">
                <a href="#">Help Center</a>
                <a href="#">Privacy Policy</a>
                <a href="#">Terms of Service</a>
            </div>
        </div>

    </div>
</main>

<script src="../../../Assets/JS/Company/dashboard.js?v=<?php echo time(); ?>"></script>

<?php include "../../../Includes/dash_footer.php"; ?>