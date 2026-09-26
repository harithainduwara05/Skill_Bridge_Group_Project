<?php
require_once '../../../Session/Session.php';
require_role('company');

$page_title = 'Applications';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Applications | SkillBridge</title>

    <link rel="stylesheet"
          href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined">

    <link rel="stylesheet"
          href="../../../Assets/JS/Company/applications.css?v=<?php echo filemtime(__DIR__ . '/../../../Assets/JS/Company/applications.css'); ?>">
</head>

<body>

<?php include '../../../Includes/company_sidebar.php'; ?>
<?php include '../../../Includes/dash_header.php'; ?>

<main class="content applications-page">

    <!-- PAGE HEADER -->
    <div class="applications-heading">

        <div>
            <h1>Internship Applications</h1>
            <p>
                Manage and review 1,248 active student applications
                across all programs.
            </p>
        </div>

        <div class="applications-heading-actions">

            <button class="export-btn" id="exportCsvBtn">
                <span class="material-symbols-outlined">
                    download
                </span>
                Export CSV
            </button>

            <button class="bulk-btn" id="bulkActionBtn">
                <span class="material-symbols-outlined">
                    add
                </span>
                Bulk Action
            </button>

        </div>
    </div>


    <!-- SUMMARY CARDS -->
    <section class="application-summary-grid">

        <div class="application-summary-card">
            <div class="summary-icon applicants">
                <span class="material-symbols-outlined">
                    group
                </span>
            </div>

            <div>
                <span class="summary-label">TOTAL APPLICANTS</span>
                <strong>1,248</strong>
            </div>
        </div>


        <div class="application-summary-card">
            <div class="summary-icon review">
                <span class="material-symbols-outlined">
                    hourglass_empty
                </span>
            </div>

            <div>
                <span class="summary-label">UNDER REVIEW</span>
                <strong class="orange-number">412</strong>
            </div>
        </div>


        <div class="application-summary-card">
            <div class="summary-icon shortlisted">
                <span class="material-symbols-outlined">
                    check_circle
                </span>
            </div>

            <div>
                <span class="summary-label">SHORTLISTED</span>
                <strong class="green-number">86</strong>
            </div>
        </div>


        <div class="application-summary-card">
            <div class="summary-icon interviews">
                <span class="material-symbols-outlined">
                    calendar_month
                </span>
            </div>

            <div>
                <span class="summary-label">INTERVIEWS</span>
                <strong class="purple-number">24</strong>
            </div>
        </div>

    </section>


    <!-- FILTERS -->
    <section class="applications-filter-card">

        <div class="filter-group">
            <label>Internship Role</label>

            <select id="roleFilter">
                <option value="all">All Internships</option>
                <option value="software">Software Engineering Intern</option>
                <option value="design">Product Design Fellowship</option>
                <option value="data">Data Analyst Intern</option>
            </select>
        </div>


        <div class="filter-group">
            <label>Application Status</label>

            <select id="statusFilter">
                <option value="all">All Statuses</option>
                <option value="Applied">Applied</option>
                <option value="Interviewing">Interviewing</option>
                <option value="Shortlisted">Shortlisted</option>
            </select>
        </div>


        <div class="filter-group">
            <label>Required Skills</label>

            <select id="skillFilter">
                <option value="all">Select Skill</option>
                <option value="React">React</option>
                <option value="Python">Python</option>
                <option value="UX Design">UX Design</option>
                <option value="Figma">Figma</option>
            </select>
        </div>


        <div class="filter-group">
            <label>University</label>

            <select id="universityFilter">
                <option value="all">All Universities</option>
                <option value="Stanford University">
                    Stanford University
                </option>
                <option value="MIT">MIT</option>
                <option value="Georgia Tech">
                    Georgia Tech
                </option>
            </select>
        </div>

    </section>


    <!-- APPLICATION TABLE -->
    <section class="applications-table-card">

        <div class="applications-table-wrapper">

            <table id="applicationsTable">

                <thead>
                    <tr>
                        <th>STUDENT NAME</th>
                        <th>UNIVERSITY</th>
                        <th>SKILLS</th>
                        <th>APPLIED DATE</th>
                        <th>STATUS</th>
                        <th>ACTIONS</th>
                    </tr>
                </thead>

                <tbody>

                    <!-- ALEX -->
                    <tr
                        data-role="software"
                        data-status="Interviewing"
                        data-skill="React Figma"
                        data-university="Stanford University">

                        <td>
                            <div class="student-cell">

                                <div class="student-avatar avatar-blue">
                                    AR
                                </div>

                                <div>
                                    <strong>Alex Rivera</strong>
                                    <span>alex.rivera@edu.com</span>
                                </div>

                            </div>
                        </td>

                        <td>Stanford University</td>

                        <td>
                            <div class="skills-list">
                                <span>React</span>
                                <span>Figma</span>
                                <span>+2</span>
                            </div>
                        </td>

                        <td>
                            Oct 12,<br>2026
                        </td>

                        <td>
                            <span class="status interviewing">
                                Interviewing
                            </span>
                        </td>

                        <td>
                            <div class="action-buttons">

                                <button
                                    class="action-btn view-btn"
                                    title="View Application">
                                    <span class="material-symbols-outlined">
                                        visibility
                                    </span>
                                </button>

                                <button
                                    class="action-btn document-btn"
                                    title="View Resume">
                                    <span class="material-symbols-outlined">
                                        description
                                    </span>
                                </button>

                                <button
                                    class="action-btn shortlist-btn"
                                    title="Shortlist">
                                    <span class="material-symbols-outlined">
                                        star
                                    </span>
                                </button>

                                <button
                                    class="action-btn reject-btn"
                                    title="Reject">
                                    <span class="material-symbols-outlined">
                                        cancel
                                    </span>
                                </button>

                            </div>
                        </td>
                    </tr>


                    <!-- MAYA -->
                    <tr
                        data-role="data"
                        data-status="Applied"
                        data-skill="Python"
                        data-university="MIT">

                        <td>
                            <div class="student-cell">

                                <div class="student-avatar avatar-brown">
                                    MP
                                </div>

                                <div>
                                    <strong>Maya Patel</strong>
                                    <span>maya.p@mit.edu</span>
                                </div>

                            </div>
                        </td>

                        <td>MIT</td>

                        <td>
                            <div class="skills-list">
                                <span>Python</span>
                                <span>TensorFlow</span>
                            </div>
                        </td>

                        <td>
                            Oct 14,<br>2026
                        </td>

                        <td>
                            <span class="status applied">
                                Applied
                            </span>
                        </td>

                        <td>
                            <div class="action-buttons">

                                <button
                                    class="action-btn view-btn"
                                    title="View Application">
                                    <span class="material-symbols-outlined">
                                        visibility
                                    </span>
                                </button>

                                <button
                                    class="action-btn document-btn"
                                    title="View Resume">
                                    <span class="material-symbols-outlined">
                                        description
                                    </span>
                                </button>

                                <button
                                    class="action-btn shortlist-btn"
                                    title="Shortlist">
                                    <span class="material-symbols-outlined">
                                        star
                                    </span>
                                </button>

                                <button
                                    class="action-btn reject-btn"
                                    title="Reject">
                                    <span class="material-symbols-outlined">
                                        cancel
                                    </span>
                                </button>

                            </div>
                        </td>
                    </tr>


                    <!-- JAMES -->
                    <tr
                        data-role="design"
                        data-status="Shortlisted"
                        data-skill="UX Design"
                        data-university="Georgia Tech">

                        <td>
                            <div class="student-cell">

                                <div class="student-avatar avatar-dark">
                                    JW
                                </div>

                                <div>
                                    <strong>James Wilson</strong>
                                    <span>j.wilson@gatech.edu</span>
                                </div>

                            </div>
                        </td>

                        <td>Georgia Tech</td>

                        <td>
                            <div class="skills-list">
                                <span>UX Design</span>
                                <span>Wireframing</span>
                            </div>
                        </td>

                        <td>
                            Oct 15,<br>2026
                        </td>

                        <td>
                            <span class="status shortlisted">
                                Shortlisted
                            </span>
                        </td>

                        <td>
                            <div class="action-buttons">

                                <button
                                    class="action-btn view-btn"
                                    title="View Application">
                                    <span class="material-symbols-outlined">
                                        visibility
                                    </span>
                                </button>

                                <button
                                    class="action-btn document-btn"
                                    title="View Resume">
                                    <span class="material-symbols-outlined">
                                        description
                                    </span>
                                </button>

                                <button
                                    class="action-btn shortlist-btn active"
                                    title="Shortlisted">
                                    <span class="material-symbols-outlined">
                                        star
                                    </span>
                                </button>

                                <button
                                    class="action-btn reject-btn"
                                    title="Reject">
                                    <span class="material-symbols-outlined">
                                        cancel
                                    </span>
                                </button>

                            </div>
                        </td>
                    </tr>

                </tbody>

            </table>

        </div>


        <!-- PAGINATION -->
        <div class="applications-pagination">

            <p>
                Showing 1–10 of 1,248 candidates
            </p>

            <div class="pagination-buttons">

                <button class="page-arrow">
                    ‹
                </button>

                <button class="page-number active">
                    1
                </button>

                <button class="page-number">
                    2
                </button>

                <button class="page-number">
                    3
                </button>

                <span>...</span>

                <button class="page-number">
                    125
                </button>

                <button class="page-arrow">
                    ›
                </button>

            </div>

        </div>

    </section>

    <?php include '../../../Includes/company_dashboard_footer.php'; ?>
</main>

<script src="../../../Assets/JS/Company/applications.js"></script>

<?php include '../../../Includes/dash_footer.php'; ?>
