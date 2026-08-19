<?php
session_start();

// Database connection & authentication check
// require_once '../../../Config/database.php';
// if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'company') { header('Location: ../../../Auth/login.php'); exit(); }

$user = [
    'name' => 'Alex Chen',
    'title' => 'Lead Recruiter',
    'avatar' => '../../../Assets/Images/admin-avatar.jpg'
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Internship - SkillBridge</title>
    <link rel="stylesheet" href="../../../Assets/CSS/dashboard.css">
    <link rel="stylesheet" href="../../../Assets/CSS/post_internship.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<div class="dash-wrapper">
    <!-- Include Shared Sidebar -->
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
                <a href="internships.php" class="active"><i class="fa-solid fa-briefcase"></i> Internships</a>
                <a href="applications.php"><i class="fa-solid fa-file-lines"></i> Applications</a>
                <a href="candidates.php"><i class="fa-solid fa-user-group"></i> Candidates</a>
                <a href="interviews.php"><i class="fa-regular fa-calendar-check"></i> Interviews</a>
                <a href="reports.php"><i class="fa-solid fa-chart-line"></i> Reports & Analytics</a>
                <a href="notifications.php"><i class="fa-regular fa-bell"></i> Notifications</a>
                <a href="settings.php"><i class="fa-solid fa-gear"></i> Settings</a>
            </nav>
            <div class="sidebar-actions">
                <a href="../../../Auth/logout.php" class="btn-logout"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
            </div>
        </aside>
    <?php } ?>

    <div class="main-content">
        <!-- Header -->
        <header class="dash-header">
            <div class="header-search">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" placeholder="Search internships or applicants...">
            </div>
            <div class="header-right">
                <button class="icon-btn notification-btn"><i class="fa-regular fa-bell"></i><span class="dot"></span></button>
                <div class="user-profile">
                    <div class="user-info">
                        <span class="user-name"><?= htmlspecialchars($user['name']) ?></span>
                        <span class="user-role"><?= htmlspecialchars($user['title']) ?></span>
                    </div>
                    <img src="<?= htmlspecialchars($user['avatar']) ?>" alt="User Avatar" class="header-avatar" onerror="this.src='https://ui-avatars.com/api/?name=Alex+Chen&background=082544&color=fff'">
                </div>
            </div>
        </header>

        <!-- Main Body -->
        <main class="page-body">
            <form id="internshipForm" action="process_post_internship.php" method="POST">
                <input type="hidden" name="form_action" id="formAction" value="publish">

                <!-- Breadcrumb & Top Header -->
                <div class="breadcrumb">
                    <a href="internships.php">Internships</a> &rsaquo; <span>Post New Internship</span>
                </div>

                <div class="page-header">
                    <div class="header-title">
                        <h1>Create Internship</h1>
                        <p>Provide the details to launch your new talent search program.</p>
                    </div>
                    <div class="header-actions">
                        <button type="button" id="btnSaveDraft" class="btn-outline">Save Draft</button>
                        <button type="submit" id="btnPostTop" class="btn-primary-brown">Post Internship</button>
                    </div>
                </div>

                <div class="form-layout-grid">
                    <!-- Left Column -->
                    <div class="grid-main">
                        
                        <!-- Basic Information Card -->
                        <div class="card">
                            <div class="card-title">
                                <i class="fa-regular fa-circle-question"></i>
                                <h3>Basic Information</h3>
                            </div>

                            <div class="form-group">
                                <label for="internship_title">INTERNSHIP TITLE</label>
                                <input type="text" id="internship_title" name="internship_title" placeholder="e.g., Junior Product Designer" required>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="department">DEPARTMENT</label>
                                    <select id="department" name="department" required>
                                        <option value="Engineering" selected>Engineering</option>
                                        <option value="Design">Design</option>
                                        <option value="Marketing">Marketing</option>
                                        <option value="Finance">Finance</option>
                                        <option value="Human Resources">Human Resources</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="internship_type">INTERNSHIP TYPE</label>
                                    <select id="internship_type" name="internship_type" required>
                                        <option value="Remote" selected>Remote</option>
                                        <option value="On-site">On-site</option>
                                        <option value="Hybrid">Hybrid</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Role Details Card -->
                        <div class="card">
                            <div class="card-title">
                                <i class="fa-regular fa-file-lines"></i>
                                <h3>Role Details</h3>
                            </div>

                            <div class="form-group">
                                <label for="description">DESCRIPTION</label>
                                <textarea id="description" name="description" rows="4" placeholder="Describe the mission of this role..." required></textarea>
                            </div>

                            <div class="form-group">
                                <label for="responsibilities">RESPONSIBILITIES</label>
                                <textarea id="responsibilities" name="responsibilities" rows="4" placeholder="List the daily tasks and key ownership areas..." required></textarea>
                                <span class="field-hint">Use bullet points for clarity.</span>
                            </div>
                        </div>

                        <!-- Required Skills Card -->
                        <div class="card">
                            <div class="card-title">
                                <i class="fa-solid fa-lightbulb"></i>
                                <h3>Required Skills</h3>
                            </div>

                            <div class="skills-tags-wrapper" id="skillsTagWrapper">
                                <div class="tag">Figma <button type="button" class="remove-tag">&times;</button></div>
                                <div class="tag">UI/UX <button type="button" class="remove-tag">&times;</button></div>
                                <div class="tag">Prototyping <button type="button" class="remove-tag">&times;</button></div>
                            </div>

                            <!-- Hidden input storing comma-separated tags for form post -->
                            <input type="hidden" name="required_skills" id="skillsInput" value="Figma,UI/UX,Prototyping">

                            <div class="add-skill-box">
                                <input type="text" id="skillTextField" placeholder="Add a skill and press Enter">
                                <button type="button" id="btnAddSkill"><i class="fa-solid fa-plus"></i></button>
                            </div>
                        </div>

                    </div>

                    <!-- Right Sidebar Column -->
                    <div class="grid-side">
                        
                        <!-- Logistics Card -->
                        <div class="card">
                            <div class="card-title">
                                <i class="fa-regular fa-calendar"></i>
                                <h3>Logistics</h3>
                            </div>

                            <div class="form-group">
                                <label for="duration">DURATION</label>
                                <select id="duration" name="duration" required>
                                    <option value="1 Month">1 Month</option>
                                    <option value="3 Months" selected>3 Months</option>
                                    <option value="6 Months">6 Months</option>
                                    <option value="12 Months">12 Months</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="location">LOCATION</label>
                                <div class="icon-input">
                                    <i class="fa-solid fa-location-dot"></i>
                                    <input type="text" id="location" name="location" placeholder="e.g., San Francisco, CA" required>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>NUMBER OF POSITIONS</label>
                                <div class="number-counter">
                                    <button type="button" id="btnDecrement">-</button>
                                    <input type="number" id="positionsCount" name="positions_count" value="1" min="1" readonly>
                                    <button type="button" id="btnIncrement">+</button>
                                </div>
                            </div>
                        </div>

                        <!-- Deadline Card -->
                        <div class="card">
                            <div class="card-title">
                                <i class="fa-regular fa-clock"></i>
                                <h3>Deadline</h3>
                            </div>

                            <div class="form-group">
                                <label for="application_deadline">APPLICATION DEADLINE</label>
                                <input type="date" id="application_deadline" name="application_deadline" required>
                            </div>

                            <div class="tip-box">
                                <i class="fa-solid fa-lightbulb"></i>
                                <p>Setting a deadline increases application urgency by up to 40%.</p>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Bottom Banner -->
                <div class="bottom-banner">
                    <div class="banner-info">
                        <i class="fa-solid fa-circle-check"></i>
                        <span>Your post will be reviewed and published within 2 hours.</span>
                    </div>
                    <div class="banner-actions">
                        <button type="button" id="btnDiscard" class="btn-outline-banner">Discard</button>
                        <button type="submit" id="btnLaunch" class="btn-navy-banner">Launch Program</button>
                    </div>
                </div>

            </form>
        </main>

        <!-- Footer -->
        <footer class="dash-footer">
            <p>&copy; 2026 SkillBridge. All rights reserved.</p>
            <div class="footer-links">
                <a href="#">Help Center</a>
                <a href="#">Privacy Policy</a>
                <a href="#">Terms of Service</a>
            </div>
        </footer>
    </div>
</div>

<script src="../../../Assets/JS/post_internship.js"></script>
</body>
</html>