<?php
$page_css = "Assets/CSS/about.css";
include '../Includes/header.php';
?>

<!-- Hero Section -->
<div class="about-hero">
    <div class="hero-content">
        <div class="hero-tag">The Academic-Industry Bridge</div>
        <h1>Bridging the Gap Between Learning and Career Success</h1>
        <p>SkillBridge helps students transform academic knowledge into industry-ready skills through collaboration, projects, and internship opportunities.</p>
        <div class="hero-actions">
            <button class="btn-primary">Explore Programs</button>
            <button class="btn-secondary">Learn More</button>
        </div>
    </div>
    <div class="hero-image">
        <img src="<?php echo $base_url; ?>Assets/Images/landing.jpeg" alt="Students collaborating">
    </div>
</div>

<!-- Mission Section -->
<div class="mission-section">
    <div class="mission-card">
        <div class="mission-icon">🏴</div>
        <div class="mission-content">
            <h2>Our Mission</h2>
            <p>To empower university students by providing a platform where they can develop skills, gain practical experience, and connect with organizations and companies. We believe in creating a seamless transition from the classroom to the tech workplace.</p>
        </div>
    </div>
</div>

<!-- Why SkillBridge -->
<div class="why-section">
    <h2 class="section-title">Why SkillBridge?</h2>
    <p class="section-desc">A purpose-built ecosystem designed to nurture talent and bridge the industry skill gap.</p>

    <div class="features-grid">
        <div class="feature-card">
            <div class="feature-icon">🎓</div>
            <h3>Skill Development</h3>
            <p>Build technical and professional skills aligned with current industry standards.</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon">💻</div>
            <h3>Real World Projects</h3>
            <p>Gain practical experience through collaboration on live industry projects.</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon">🤝</div>
            <h3>Career Opportunities</h3>
            <p>Connect directly with vetted internship providers and leading tech firms.</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon">📈</div>
            <h3>Professional Growth</h3>
            <p>Build impressive portfolios and industry-ready professional career profiles.</p>
        </div>
    </div>
</div>

<!-- How It Works -->
<div class="how-it-works">
    <h2 class="section-title">How SkillBridge Works</h2>
    
    <div class="steps-grid">
        <div class="step-card">
            <div class="step-number">1</div>
            <h3>Create Profile</h3>
            <p>Showcase your academic background and interests.</p>
        </div>
        <div class="step-card">
            <div class="step-number">2</div>
            <h3>Develop Skills</h3>
            <p>Access curated learning paths and resources.</p>
        </div>
        <div class="step-card">
            <div class="step-number">3</div>
            <h3>Collaborate on Projects</h3>
            <p>Join teams and work on practical, real-world solutions.</p>
        </div>
        <div class="step-card">
            <div class="step-number">4</div>
            <h3>Get Career Opportunities</h3>
            <p>Land your dream internship and start your career.</p>
        </div>
    </div>
</div>

<!-- Ecosystem -->
<div class="ecosystem-section">
    <h2 class="section-title">An Integrated Ecosystem</h2>
    
    <div class="eco-grid">
        <div class="eco-card">
            <img src="<?php echo $base_url; ?>Assets/Images/landing.jpeg" alt="Students" class="eco-img">
            <h3>Students</h3>
            <p>Build skills, work on real projects, and gain the experience needed to launch your professional career.</p>
        </div>
        <div class="eco-card">
            <img src="<?php echo $base_url; ?>Assets/Images/landing.jpeg" alt="Organizations" class="eco-img">
            <h3>Organizations</h3>
            <p>Collaborate with talented students to bring fresh perspectives and innovative solutions to your initiatives.</p>
        </div>
        <div class="eco-card">
            <img src="<?php echo $base_url; ?>Assets/Images/landing.jpeg" alt="Companies" class="eco-img">
            <h3>Companies</h3>
            <p>Discover future employees by mentoring talent early and observing their practical skills in action.</p>
        </div>
    </div>
</div>

<!-- CTA Banner -->
<div class="cta-banner">
    <h2>Ready to build your bridge?</h2>
    <p>Join thousands of students and industry partners already shaping the future of work.</p>
    <button class="btn-cta" onclick="window.location.href='../Auth/login.php'">Get Started Today</button>
</div>

<?php include '../Includes/footer.php'; ?>
