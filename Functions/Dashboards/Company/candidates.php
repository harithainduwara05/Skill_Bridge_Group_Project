<?php

require_once '../../../Session/Session.php';
require_role('company');

/*
|--------------------------------------------------------------------------
| COMPANY CANDIDATES PAGE
|--------------------------------------------------------------------------
| Keep the existing shared header + Company sidebar.
| Do not create another header/search bar here.
*/

$pageTitle = 'Candidates';

include '../../../Includes/company_sidebar.php';
include '../../../Includes/dash_header.php';
?>

<link rel="stylesheet"
      href="<?= $GLOBALS['BASE_URL'] ?>/Assets/CSS/Company/candidates.css?v=<?= time(); ?>">

<main class="company-candidates-page">

    <div class="candidate-layout">

        <!-- =====================================================
             LEFT SIDE
        ====================================================== -->
        <div class="candidate-main-column">

            <!-- Candidate Profile -->
            <section class="candidate-profile-card">

                <div class="profile-accent"></div>

                <div class="candidate-avatar">
                    LH
                    <span class="online-dot"></span>
                </div>

                <div class="candidate-info">
                    <h1>Liam Henderson</h1>

                    <h3>Stanford University</h3>

                    <p class="candidate-degree">
                        B.S. in Computer Science • Senior Year
                    </p>

                    <div class="candidate-contact">
                        <span>
                            <span class="material-symbols-outlined">
                                location_on
                            </span>
                            Palo Alto, CA
                        </span>

                        <span>
                            <span class="material-symbols-outlined">
                                mail
                            </span>
                            l.henderson@stanford.edu
                        </span>
                    </div>
                </div>

                <div class="candidate-profile-actions">

                    <button
                        type="button"
                        class="candidate-btn candidate-btn-outline"
                        id="downloadCvBtn"
                    >
                        <span class="material-symbols-outlined">
                            download
                        </span>
                        Download CV
                    </button>

                    <button
                        type="button"
                        class="candidate-btn candidate-btn-shortlist"
                        id="shortlistBtn"
                    >
                        <span class="material-symbols-outlined">
                            star
                        </span>
                        <span id="shortlistText">Shortlist</span>
                    </button>

                </div>

            </section>


            <!-- Skills + Academic -->
            <div class="candidate-overview-grid">

                <!-- Skills -->
                <section class="candidate-card skills-card">

                    <div class="section-title">
                        <span class="section-icon orange">
                            <span class="material-symbols-outlined">
                                bolt
                            </span>
                        </span>

                        <h2>Skills & Expertise</h2>
                    </div>

                    <div class="skills-list">
                        <span>React.js</span>
                        <span>TypeScript</span>
                        <span>Node.js</span>
                        <span>Python</span>
                        <span>UI/UX Design</span>
                        <span>Cloud Computing</span>
                        <span>Data Structures</span>
                        <span>Git</span>
                    </div>

                </section>


                <!-- Academic Snapshot -->
                <section class="academic-card">

                    <h2>Academic Snapshot</h2>

                    <div class="academic-grid">

                        <div class="academic-item">
                            <span>GPA</span>
                            <strong>3.92 / 4.0</strong>
                        </div>

                        <div class="academic-item">
                            <span>Projects</span>
                            <strong>14 Active</strong>
                        </div>

                        <div class="academic-item">
                            <span>Certificates</span>
                            <strong>8 Earned</strong>
                        </div>

                        <div class="academic-item">
                            <span>Awards</span>
                            <strong>Dean's List</strong>
                        </div>

                    </div>

                </section>

            </div>


            <!-- Portfolio -->
            <section class="portfolio-section">

                <div class="portfolio-heading">
                    <h2>Portfolio Projects</h2>

                    <button type="button" class="view-all-btn">
                        View All
                    </button>
                </div>


                <div class="portfolio-grid">

                    <!-- FinTech Project -->
                    <article class="portfolio-card">

                        <div class="portfolio-image">

                            <img
                                src="<?= $GLOBALS['BASE_URL'] ?>/Assets/Images/company/Fintech.jpg"
                                alt="EcoSpend AI"
                            >

                            <span class="project-category">
                                FinTech
                            </span>

                        </div>

                        <div class="portfolio-content">

                            <h3>EcoSpend AI</h3>

                            <p>
                                An AI-driven personal finance tracker that
                                optimizes spending and financial decisions.
                            </p>

                            <div class="portfolio-footer">

                                <div class="tech-stack">
                                    <span>JS</span>
                                    <span>AI</span>
                                    <span>+2</span>
                                </div>

                                <button
                                    type="button"
                                    class="project-open-btn"
                                    aria-label="Open EcoSpend AI"
                                >
                                    <span class="material-symbols-outlined">
                                        open_in_new
                                    </span>
                                </button>

                            </div>

                        </div>

                    </article>


                    <!-- Infrastructure Project -->
                    <article class="portfolio-card">

                        <div class="portfolio-image">

                            <img
                                src="<?= $GLOBALS['BASE_URL'] ?>/Assets/Images/company/infrastructure.jpg"
                                alt="CloudGuard Analytics"
                            >

                            <span class="project-category">
                                Infrastructure
                            </span>

                        </div>

                        <div class="portfolio-content">

                            <h3>CloudGuard Analytics</h3>

                            <p>
                                Distributed monitoring tool for multi-cloud
                                environments using intelligent analytics.
                            </p>

                            <div class="portfolio-footer">

                                <div class="tech-stack">
                                    <span>AV</span>
                                    <span>TS</span>
                                    <span>+1</span>
                                </div>

                                <button
                                    type="button"
                                    class="project-open-btn"
                                    aria-label="Open CloudGuard Analytics"
                                >
                                    <span class="material-symbols-outlined">
                                        open_in_new
                                    </span>
                                </button>

                            </div>

                        </div>

                    </article>

                </div>

            </section>

        </div>


        <!-- =====================================================
             RIGHT SIDE
        ====================================================== -->
        <aside class="candidate-side-column">

            <!-- Resume -->
            <section class="resume-card">

                <div class="resume-header">

                    <strong>Resume_Liam_H.pdf</strong>

                    <div class="resume-actions">

                        <button
                            type="button"
                            id="resumeZoomBtn"
                            title="Preview Resume"
                        >
                            <span class="material-symbols-outlined">
                                zoom_in
                            </span>
                        </button>

                        <button
                            type="button"
                            id="resumeDownloadBtn"
                            title="Download Resume"
                        >
                            <span class="material-symbols-outlined">
                                download
                            </span>
                        </button>

                    </div>

                </div>


                <!-- Resume Preview -->
                <div class="resume-preview" id="resumePreview">

                    <div class="resume-paper">

                        <h2>LIAM HENDERSON</h2>

                        <p class="resume-contact">
                            Silicon Valley, CA • (555) 0123 •
                            l.henderson@stanford.edu
                        </p>

                        <div class="resume-divider"></div>

                        <div class="resume-section">
                            <h4>EDUCATION</h4>

                            <strong>Stanford University</strong>

                            <p>
                                B.S. in Computer Science |
                                2021 – Present
                            </p>
                        </div>


                        <div class="resume-section">

                            <h4>EXPERIENCE</h4>

                            <strong>
                                Software Intern | TechGiant Inc.
                            </strong>

                            <small>
                                June 2023 – August 2023
                            </small>

                            <p>
                                Optimized backend latency and
                                improved application performance.
                            </p>

                            <strong>
                                Research Assistant | Stanford AI Lab
                            </strong>

                            <small>
                                January 2023 – May 2023
                            </small>

                            <p>
                                Assisted in training large language models
                                for specialized research tasks.
                            </p>

                        </div>

                        <div class="resume-document-placeholder">
                            <span class="material-symbols-outlined">
                                description
                            </span>
                        </div>

                    </div>

                </div>

            </section>


            <!-- Certificates -->
            <section class="candidate-card certificates-card">

                <div class="section-title">

                    <span class="section-icon orange">
                        <span class="material-symbols-outlined">
                            verified
                        </span>
                    </span>

                    <h2>Certificates</h2>

                </div>


                <div class="certificate-list">

                    <div class="certificate-item">

                        <div class="certificate-icon">
                            AWS
                        </div>

                        <div class="certificate-info">
                            <strong>AWS Certified Developer</strong>
                            <span>Issued: March 2024</span>
                        </div>

                        <button type="button">
                            <span class="material-symbols-outlined">
                                open_in_new
                            </span>
                        </button>

                    </div>


                    <div class="certificate-item">

                        <div class="certificate-icon">
                            GD
                        </div>

                        <div class="certificate-info">
                            <strong>Google Data Analytics</strong>
                            <span>Issued: Dec 2023</span>
                        </div>

                        <button type="button">
                            <span class="material-symbols-outlined">
                                open_in_new
                            </span>
                        </button>

                    </div>


                    <div class="certificate-item">

                        <div class="certificate-icon">
                            MR
                        </div>

                        <div class="certificate-info">
                            <strong>Meta React Developer</strong>
                            <span>Issued: Aug 2023</span>
                        </div>

                        <button type="button">
                            <span class="material-symbols-outlined">
                                open_in_new
                            </span>
                        </button>

                    </div>

                </div>

            </section>

        </aside>

    </div>

    <?php include '../../../Includes/company_dashboard_footer.php'; ?>
</main>


<!-- ==========================================
     SIMPLE MESSAGE TOAST
========================================== -->
<div class="candidate-toast" id="candidateToast">
    <span class="material-symbols-outlined" id="toastIcon">
        check_circle
    </span>

    <span id="toastMessage">
        Candidate shortlisted successfully.
    </span>
</div>


<script src="<?= $GLOBALS['BASE_URL'] ?>/Assets/JS/Company/candidates.js?v=<?= time(); ?>"></script>

<?php
include '../../../Includes/dash_footer.php';
?>
