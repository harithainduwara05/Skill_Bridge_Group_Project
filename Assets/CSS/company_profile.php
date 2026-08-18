<?php
session_start();

// Database connection & authentication check
// require_once '../../../Config/database.php';
// if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'company') { header('Location: ../../../Auth/login.php'); exit(); }

// Mock data (Replace with dynamic DB fetch query)
$company = [
    'name' => 'TechCorp Solutions',
    'industry' => 'Software Development',
    'email' => 'contact@techcorp.com',
    'phone' => '+1 (555) 012-3456',
    'website' => 'www.techcorp.com',
    'address' => '123 Innovation Way, Silicon Valley, CA',
    'about' => 'TechCorp Solutions is a leading provider of innovative software solutions, specializing in cloud migration and AI optimization for enterprise clients worldwide.',
    'linkedin' => 'linkedin.com/company/techcorp',
    'twitter' => 'twitter.com/techcorp',
    'facebook' => 'facebook.com/techcorp',
    'is_verified' => true,
    'logo' => '../../../Assets/Images/company-logo.png'
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Company Profile - SkillBridge</title>
    <link rel="stylesheet" href="../../../Assets/CSS/dashboard.css">
    <link rel="stylesheet" href="../../../Assets/CSS/company_profile.css">
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
                <a href="company_profile.php" class="active"><i class="fa-regular fa-building"></i> Company Profile</a>
                <a href="internships.php"><i class="fa-solid fa-briefcase"></i> Internships</a>
                <a href="applications.php"><i class="fa-solid fa-file-lines"></i> Applications</a>
                <a href="candidates.php"><i class="fa-solid fa-user-group"></i> Candidates</a>
                <a href="interviews.php"><i class="fa-regular fa-calendar-check"></i> Interviews</a>
                <a href="reports.php"><i class="fa-solid fa-chart-line"></i> Reports & Analytics</a>
                <a href="notifications.php"><i class="fa-regular fa-bell"></i> Notifications</a>
                <a href="settings.php"><i class="fa-solid fa-gear"></i> Settings</a>
            </nav>
            <div class="sidebar-actions">
                <a href="post_internship.php" class="btn-post"><i class="fa-solid fa-plus"></i> Post Internship</a>
                <a href="../../../Auth/logout.php" class="btn-logout"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
            </div>
        </aside>
    <?php } ?>

    <div class="main-content">
        <!-- Include Shared Header -->
        <?php 
        if (file_exists('dash_header.php')) {
            include 'dash_header.php';
        } else { ?>
            <header class="dash-header">
                <div class="header-right">
                    <button class="icon-btn notification-btn"><i class="fa-regular fa-bell"></i><span class="dot"></span></button>
                    <div class="user-profile">
                        <div class="user-info">
                            <span class="user-name"><?= htmlspecialchars($company['name']) ?></span>
                            <span class="user-role">Admin Account</span>
                        </div>
                        <img src="../../../Assets/Images/admin-avatar.jpg" alt="Admin" class="header-avatar" onerror="this.src='https://ui-avatars.com/api/?name=Tech+Corp&background=082544&color=fff'">
                    </div>
                </div>
            </header>
        <?php } ?>

        <!-- Page Main Container -->
        <main class="page-body">
            <form id="profileForm" action="process_profile_update.php" method="POST" enctype="multipart/form-data">
                
                <!-- Action Bar -->
                <div class="page-header">
                    <div class="header-title">
                        <h1>Company Profile</h1>
                        <p>Manage your public organization profile and contact information.</p>
                    </div>
                    <div class="header-actions">
                        <button type="button" id="btnEditProfile" class="btn-outline">Edit Profile</button>
                        <button type="submit" id="btnSaveProfile" class="btn-primary" disabled>Save Changes</button>
                    </div>
                </div>

                <div class="profile-grid">
                    <!-- Left Column -->
                    <div class="grid-left">
                        <!-- Logo Card -->
                        <div class="card logo-card">
                            <div class="logo-preview-wrapper">
                                <img id="logoPreview" src="<?= htmlspecialchars($company['logo']) ?>" alt="Company Logo" onerror="this.src='https://via.placeholder.com/130?text=Logo'">
                            </div>
                            <input type="file" id="logoUpload" name="company_logo" accept="image/*" class="file-input-hidden" disabled>
                            <label for="logoUpload" id="uploadBtnLabel" class="btn-upload-text disabled-link">Upload New Logo</label>
                            
                            <?php if ($company['is_verified']): ?>
                                <div class="badge-verified"><i class="fa-solid fa-circle-check"></i> Verified</div>
                            <?php endif; ?>
                        </div>

                        <!-- Social Links Card -->
                        <div class="card social-card">
                            <h3>Social Links</h3>
                            
                            <div class="form-group">
                                <label for="linkedin">LINKEDIN</label>
                                <input type="text" id="linkedin" name="linkedin" value="<?= htmlspecialchars($company['linkedin']) ?>" readonly>
                            </div>

                            <div class="form-group">
                                <label for="twitter">TWITTER</label>
                                <input type="text" id="twitter" name="twitter" value="<?= htmlspecialchars($company['twitter']) ?>" readonly>
                            </div>

                            <div class="form-group">
                                <label for="facebook">FACEBOOK</label>
                                <input type="text" id="facebook" name="facebook" value="<?= htmlspecialchars($company['facebook']) ?>" readonly>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Details Card -->
                    <div class="grid-right">
                        <div class="card details-card">
                            <h3>Company Details</h3>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="company_name">COMPANY NAME</label>
                                    <input type="text" id="company_name" name="company_name" value="<?= htmlspecialchars($company['name']) ?>" readonly required>
                                </div>
                                <div class="form-group">
                                    <label for="industry">INDUSTRY</label>
                                    <input type="text" id="industry" name="industry" value="<?= htmlspecialchars($company['industry']) ?>" readonly required>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="company_email">COMPANY EMAIL</label>
                                    <input type="email" id="company_email" name="company_email" value="<?= htmlspecialchars($company['email']) ?>" readonly required>
                                </div>
                                <div class="form-group">
                                    <label for="contact_number">CONTACT NUMBER</label>
                                    <input type="text" id="contact_number" name="contact_number" value="<?= htmlspecialchars($company['phone']) ?>" readonly required>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="website_url">WEBSITE URL</label>
                                <input type="text" id="website_url" name="website_url" value="<?= htmlspecialchars($company['website']) ?>" readonly>
                            </div>

                            <div class="form-group">
                                <label for="address">ADDRESS</label>
                                <input type="text" id="address" name="address" value="<?= htmlspecialchars($company['address']) ?>" readonly required>
                            </div>

                            <div class="form-group">
                                <label for="about_company">ABOUT COMPANY</label>
                                <textarea id="about_company" name="about_company" rows="4" readonly><?= htmlspecialchars($company['about']) ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </main>

        <!-- Include Shared Footer -->
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

<script src="../../../Assets/JS/company_profile.js"></script>
</body>
</html>