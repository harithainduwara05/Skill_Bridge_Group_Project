<?php
session_start();

// Handle tab parameter
$tab = isset($_GET['tab']) ? $_GET['tab'] : 'home';

// Basic routing for landing sub-pages
$valid_tabs = ['home', 'internships', 'projects', 'about', 'contact'];
if (!in_array($tab, $valid_tabs)) {
    $tab = 'home';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SkillBridge - Connect Students with Real-World Opportunities</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- CSS Links -->
    <link rel="stylesheet" href="Assets/CSS/landing.css">
    <?php if ($tab === 'internships'): ?>
        <link rel="stylesheet" href="Assets/CSS/internships.css">
    <?php elseif ($tab === 'projects'): ?>
        <link rel="stylesheet" href="Assets/CSS/projects.css">
    <?php elseif ($tab === 'about'): ?>
        <link rel="stylesheet" href="Assets/CSS/about.css">
    <?php elseif ($tab === 'contact'): ?>
        <link rel="stylesheet" href="Assets/CSS/contact.css">
    <?php endif; ?>
</head>
<body>

    <!-- Header Navigation -->
    <?php include 'Includes/header.php'; ?>

    <!-- Main Content Area -->
    <main>
        <?php
        if ($tab === 'home') {
            ?>
            <!-- HERO SECTION -->
            <section class="hero-section">
                <div class="hero-container">
                    <div class="hero-content">
                        <span class="hero-badge"><i class="fas fa-sparkles"></i> Revolutionizing Skill Validation</span>
                        <h1>Bridge the Gap Between <span>Education & Career</span></h1>
                        <p>SkillBridge empowers university students to validate their academic knowledge through real-world industry projects, verified skill badges, and direct talent matching with top employers.</p>
                        <div class="hero-buttons">
                            <a href="register.php" class="btn-primary">Get Started Free <i class="fas fa-arrow-right"></i></a>
                            <a href="index.php?tab=about" class="btn-secondary">Learn More</a>
                        </div>
                        <div class="students-joined">
                            <div class="student-avatars">
                                <span><i class="fas fa-user-graduate"></i></span>
                                <span><i class="fas fa-user-tie"></i></span>
                                <span><i class="fas fa-laptop-code"></i></span>
                            </div>
                            <p>Joined by <strong>10,000+</strong> students across leading universities</p>
                        </div>
                    </div>
                    <div class="hero-visual">
                        <div class="hero-image-card">
                            <img src="Assets/Images/landing.jpeg" alt="Students Collaboration">
                        </div>
                        <div class="floating-notification">
                            <div class="notification-icon">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div>
                                <strong>Skill Verified!</strong>
                                <p>Full-Stack Web Development assessed by Industry Experts.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- STATS COUNTER BAR -->
            <section class="stats-section">
                <div class="stats-container">
                    <div class="stat-item">
                        <strong>15,000+</strong>
                        <span>Active Students</span>
                    </div>
                    <div class="stat-item">
                        <strong>500+</strong>
                        <span>Partner Companies</span>
                    </div>
                    <div class="stat-item">
                        <strong>1,200+</strong>
                        <span>Projects Completed</span>
                    </div>
                    <div class="stat-item">
                        <strong>94%</strong>
                        <span>Placement Rate</span>
                    </div>
                </div>
            </section>

            <!-- KEY FEATURES -->
            <section class="features-section">
                <div class="section-heading">
                    <h2>Why Choose SkillBridge?</h2>
                    <p>Designed to provide verified skills, practical experience, and career opportunities directly from university classrooms to company boardrooms.</p>
                </div>

                <div class="feature-grid">
                    <div class="feature-card">
                        <div class="feature-icon"><i class="fas fa-certificate"></i></div>
                        <h3>Verified Skill Profiles</h3>
                        <p>Get your competencies reviewed and verified by academic supervisors and corporate experts, guaranteeing maximum trust.</p>
                    </div>

                    <div class="feature-card">
                        <div class="feature-icon"><i class="fas fa-briefcase"></i></div>
                        <h3>Real Industry Projects</h3>
                        <p>Work on live client briefs provided by top companies to gain actual hands-on project experience during your studies.</p>
                    </div>

                    <div class="feature-card">
                        <div class="feature-icon"><i class="fas fa-handshake"></i></div>
                        <h3>Direct Internship Pipeline</h3>
                        <p>Fast-track your job applications. Standout project contributors get recommended directly to partner hiring managers.</p>
                    </div>
                </div>
            </section>

            <!-- CAREER PATHWAY -->
            <section class="career-section">
                <div class="career-container">
                    <div class="career-content">
                        <h2>Build a Verified Portfolio That Employers Trust</h2>
                        <div class="career-step">
                            <div class="step-number">1</div>
                            <div>
                                <h3>Create Your Academic Profile</h3>
                                <p>Import your coursework, projects, and skills validated by university instructors.</p>
                            </div>
                        </div>
                        <div class="career-step">
                            <div class="step-number">2</div>
                            <div>
                                <h3>Solve Company Challenges</h3>
                                <p>Apply for industry-sponsored projects and build solutions under real mentorship.</p>
                            </div>
                        </div>
                        <div class="career-step">
                            <div class="step-number">3</div>
                            <div>
                                <h3>Earn Badges & Get Hired</h3>
                                <p>Showcase verified skill achievements and receive direct hiring proposals.</p>
                            </div>
                        </div>
                    </div>
                    <div class="career-image">
                        <img src="Assets/Images/login-hero.jpg" alt="Career Pathway">
                    </div>
                </div>
            </section>

            <!-- ECOSYSTEM -->
            <section class="ecosystem-section">
                <div class="section-heading">
                    <h2>Empowering the Entire Ecosystem</h2>
                    <p>SkillBridge brings together students, universities, and industry partners under one unified platform.</p>
                </div>

                <div class="ecosystem-grid">
                    <div class="ecosystem-card">
                        <div class="eco-icon"><i class="fas fa-graduation-cap"></i></div>
                        <h3>For Students</h3>
                        <p>Transition seamlessly from academia to industry with proof of skills and real-world project portfolios.</p>
                    </div>

                    <div class="ecosystem-card">
                        <div class="eco-icon"><i class="fas fa-university"></i></div>
                        <h3>For Universities</h3>
                        <p>Enhance graduate outcomes, track student progress, and seamlessly manage industry partnerships.</p>
                    </div>

                    <div class="ecosystem-card">
                        <div class="eco-icon"><i class="fas fa-building"></i></div>
                        <h3>For Companies</h3>
                        <p>Discover pre-vetted junior talent based on proven skill capabilities rather than just resumes.</p>
                    </div>
                </div>
            </section>
            <?php
        } elseif ($tab === 'internships') {
            include 'landingPage_content/internships.php';
        } elseif ($tab === 'projects') {
            include 'landingPage_content/projects.php';
        } elseif ($tab === 'about') {
            include 'landingPage_content/about.php';
        } elseif ($tab === 'contact') {
            include 'landingPage_content/contact.php';
        }
        ?>
    </main>

    <!-- Footer Component -->
    <?php include 'Includes/footer.php'; ?>

    <script src="Assets/JS/landing.js"></script>
</body>
</html>