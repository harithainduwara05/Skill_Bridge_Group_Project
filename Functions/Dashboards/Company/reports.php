<?php

include "../../../Config/db.php";
include "../../../Session/Session.php";
require_once "../../../Backend/CompanyBackend.php";

require_role('company');

$user = current_user();
$companyEmail = $user['email'] ?? $user['Email'] ?? '';

$companyManager = new CompanyManager($conn);
$company = $companyManager->getCompany($companyEmail);

if (!$company) {
    die('Company profile not found.');
}

/*
|--------------------------------------------------------------------------
| REPORTS CSS
|--------------------------------------------------------------------------
| IMPORTANT:
| Shared header and sidebar are NOT recreated here.
| We use the same header/sidebar used by the Company Dashboard.
*/
$extra_css =
    '<link rel="stylesheet" href="../../../Assets/CSS/Company/reports.css?v=' . time() . '">';

/*
|--------------------------------------------------------------------------
| SHARED COMPANY LAYOUT
|--------------------------------------------------------------------------
*/
include "../../../Includes/company_sidebar.php";
include "../../../Includes/dash_header.php";

?>

<!-- =========================================================
     REPORTS MAIN CONTENT
========================================================= -->

<main class="content company-reports-page">

        <!-- =====================================================
             PAGE HEADER
        ====================================================== -->

        <section class="reports-page-header">

            <div class="reports-title-area">

                <h1>Analytics &amp; Performance</h1>

                <p>
                    Real-time overview of your recruitment pipeline
                    and intern success metrics.
                </p>

            </div>


            <div class="reports-header-actions">

                <!-- PERIOD SELECTOR -->

                <div class="period-selector">

                    <button
                        type="button"
                        class="period-btn active"
                        data-period="30">
                        Last 30 Days
                    </button>

                    <button
                        type="button"
                        class="period-btn"
                        data-period="quarterly">
                        Quarterly
                    </button>

                    <button
                        type="button"
                        class="period-btn"
                        data-period="yearly">
                        Yearly
                    </button>

                </div>


                <!-- EXPORT PDF -->

                <button
                    type="button"
                    class="export-btn"
                    id="exportPdfBtn">

                    <span class="material-symbols-outlined">
                        download
                    </span>

                    Export PDF

                </button>


                <!-- CSV -->

                <button
                    type="button"
                    class="csv-btn"
                    id="exportCsvBtn">

                    <span class="material-symbols-outlined">
                        table_view
                    </span>

                    CSV

                </button>

            </div>

        </section>



        <!-- =====================================================
             METRIC CARDS
        ====================================================== -->

        <section class="metrics-grid">


            <!-- TOTAL APPLICATIONS -->

            <article class="metric-card">

                <span class="metric-label">
                    TOTAL APPLICATIONS
                </span>

                <div class="metric-value-row">

                    <span class="metric-value">
                        2,482
                    </span>

                    <span class="metric-change positive">
                        +12.5%
                    </span>

                </div>

                <div class="metric-progress">

                    <span style="width: 68%;"></span>

                </div>

            </article>



            <!-- SELECTION RATE -->

            <article class="metric-card">

                <span class="metric-label">
                    SELECTION RATE
                </span>

                <div class="metric-value-row">

                    <span class="metric-value">
                        4.2%
                    </span>

                    <span class="metric-change negative">
                        -0.8%
                    </span>

                </div>

                <div class="metric-progress orange">

                    <span style="width: 42%;"></span>

                </div>

            </article>



            <!-- ACTIVE INTERNSHIPS -->

            <article class="metric-card">

                <span class="metric-label">
                    ACTIVE INTERNSHIPS
                </span>

                <div class="metric-value-row">

                    <span class="metric-value">
                        48
                    </span>

                    <span class="metric-small-text">
                        Across 12 depts
                    </span>

                </div>

                <div class="metric-progress">

                    <span style="width: 62%;"></span>

                </div>

            </article>



            <!-- COMPLETION RATE -->

            <article class="metric-card">

                <span class="metric-label">
                    COMPLETION RATE
                </span>

                <div class="metric-value-row">

                    <span class="metric-value">
                        96%
                    </span>

                    <span class="metric-change positive">
                        +2%
                    </span>

                </div>

                <div class="metric-progress orange">

                    <span style="width: 94%;"></span>

                </div>

            </article>

        </section>



        <!-- =====================================================
             ANALYTICS SECTION
        ====================================================== -->

        <section class="analytics-grid">


            <!-- =================================================
                 APPLICATIONS OVER TIME
            ================================================== -->

            <article class="analytics-card applications-chart-card">


                <div class="analytics-card-header">

                    <div>

                        <h2>
                            Applications Over Time
                        </h2>

                        <p>
                            Volume of submissions per week for all active roles
                        </p>

                    </div>


                    <div class="chart-legend">

                        <span>

                            <i class="legend-dot technical"></i>

                            Technical Roles

                        </span>


                        <span>

                            <i class="legend-dot creative"></i>

                            Creative Roles

                        </span>

                    </div>

                </div>



                <!-- CHART -->

                <div class="chart-area">


                    <!-- GRID LINES -->

                    <div class="chart-grid-line line-one"></div>

                    <div class="chart-grid-line line-two"></div>

                    <div class="chart-grid-line line-three"></div>

                    <div class="chart-grid-line line-four"></div>



                    <!-- SVG -->

                    <svg
                        class="chart-svg"
                        viewBox="0 0 800 250"
                        preserveAspectRatio="none"
                        aria-label="Applications over time">


                        <!-- TECHNICAL ROLES -->

                        <polyline
                            class="technical-line"
                            points="
                                10,210
                                115,175
                                220,185
                                325,115
                                430,145
                                535,70
                                640,100
                                745,30
                            ">
                        </polyline>


                        <!-- CREATIVE ROLES -->

                        <polyline
                            class="creative-line"
                            points="
                                10,235
                                115,215
                                220,170
                                325,200
                                430,155
                                535,165
                                640,110
                                745,125
                            ">
                        </polyline>


                    </svg>



                    <!-- WEEK LABELS -->

                    <div class="chart-weeks">

                        <span>WK 1</span>
                        <span>WK 2</span>
                        <span>WK 3</span>
                        <span>WK 4</span>
                        <span>WK 5</span>
                        <span>WK 6</span>
                        <span>WK 7</span>
                        <span>WK 8</span>

                    </div>

                </div>

            </article>



            <!-- =================================================
                 SKILLS DISTRIBUTION
            ================================================== -->

            <article class="analytics-card skills-card">


                <div class="analytics-card-header">

                    <div>

                        <h2>
                            Skills Distribution
                        </h2>

                        <p>
                            Primary expertise of top 10% applicants
                        </p>

                    </div>

                </div>



                <!-- DONUT -->

                <div class="donut-wrapper">

                    <div class="skills-donut">

                        <div class="donut-center">

                            <strong>
                                845
                            </strong>

                            <span>
                                QUALIFIED
                            </span>

                        </div>

                    </div>

                </div>



                <!-- SKILL LIST -->

                <div class="skills-list">


                    <div>

                        <span>

                            <i class="skill-dot frontend"></i>

                            React / Frontend

                        </span>

                        <strong>
                            40%
                        </strong>

                    </div>



                    <div>

                        <span>

                            <i class="skill-dot backend"></i>

                            Python / Backend

                        </span>

                        <strong>
                            30%
                        </strong>

                    </div>



                    <div>

                        <span>

                            <i class="skill-dot design"></i>

                            UX/UI Design

                        </span>

                        <strong>
                            20%
                        </strong>

                    </div>



                    <div>

                        <span>

                            <i class="skill-dot product"></i>

                            Product Management

                        </span>

                        <strong>
                            10%
                        </strong>

                    </div>


                </div>

            </article>

        </section>



        <!-- =====================================================
             HIRING TRENDS
        ====================================================== -->

        <section class="hiring-card">


            <div class="hiring-card-header">

                <div>

                    <h2>
                        Hiring Trends by Department
                    </h2>

                    <p>
                        Monthly velocity and conversion benchmarks
                    </p>

                </div>


                <button
                    type="button"
                    class="details-btn"
                    id="viewDetailsBtn">

                    View Full Details

                </button>

            </div>



            <div class="hiring-table-wrapper">

                <table class="hiring-table">


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


                        <!-- SOFTWARE ENGINEERING -->

                        <tr>

                            <td>

                                <div class="department-cell">

                                    <div class="department-icon">

                                        <span class="material-symbols-outlined">
                                            terminal
                                        </span>

                                    </div>


                                    <div>

                                        <strong>
                                            Software Engineering
                                        </strong>

                                        <small>
                                            FULL STACK FOCUS
                                        </small>

                                    </div>

                                </div>

                            </td>


                            <td>
                                12
                            </td>


                            <td>
                                1,240
                            </td>


                            <td>

                                <strong>
                                    15 / 20
                                </strong>

                                <span class="goal-bar">

                                    <i style="width:75%;"></i>

                                </span>

                            </td>


                            <td>

                                <span class="status-pill on-track">

                                    ON TRACK

                                </span>

                            </td>


                            <td>

                                <span class="velocity positive">

                                    ↗ +18%

                                </span>

                            </td>

                        </tr>



                        <!-- DESIGN -->

                        <tr>

                            <td>

                                <div class="department-cell">

                                    <div class="department-icon orange-icon">

                                        <span class="material-symbols-outlined">
                                            palette
                                        </span>

                                    </div>


                                    <div>

                                        <strong>
                                            Design &amp; Creative
                                        </strong>

                                        <small>
                                            UX/VISUAL DESIGN
                                        </small>

                                    </div>

                                </div>

                            </td>


                            <td>
                                4
                            </td>


                            <td>
                                420
                            </td>


                            <td>

                                <strong>
                                    2 / 5
                                </strong>

                                <span class="goal-bar orange-goal">

                                    <i style="width:40%;"></i>

                                </span>

                            </td>


                            <td>

                                <span class="status-pill interviewing">

                                    INTERVIEWING

                                </span>

                            </td>


                            <td>

                                <span class="velocity stable">

                                    → Stable

                                </span>

                            </td>

                        </tr>



                        <!-- DATA SCIENCE -->

                        <tr>

                            <td>

                                <div class="department-cell">

                                    <div class="department-icon">

                                        <span class="material-symbols-outlined">
                                            monitoring
                                        </span>

                                    </div>


                                    <div>

                                        <strong>
                                            Data Science
                                        </strong>

                                        <small>
                                            ML &amp; ANALYTICS
                                        </small>

                                    </div>

                                </div>

                            </td>


                            <td>
                                2
                            </td>


                            <td>
                                115
                            </td>


                            <td>

                                <strong>
                                    3 / 3
                                </strong>

                                <span class="goal-bar">

                                    <i style="width:100%;"></i>

                                </span>

                            </td>


                            <td>

                                <span class="status-pill completed">

                                    COMPLETED

                                </span>

                            </td>


                            <td>

                                <span class="velocity positive">

                                    ↗ +5%

                                </span>

                            </td>

                        </tr>


                    </tbody>

                </table>

            </div>

        </section>



        <!-- =====================================================
             BOTTOM INSIGHTS
        ====================================================== -->

        <section class="bottom-insights">


            <!-- TIME TO HIRE -->

            <article class="time-to-hire-card">

                <span class="insight-label">

                    EFFICIENCY METRIC

                </span>


                <h2>

                    Average Time to<br>
                    Hire

                </h2>


                <div class="time-value">

                    <strong>
                        12.5
                    </strong>


                    <div>

                        <span>
                            Days
                        </span>

                        <span>
                            vs 18d Avg
                        </span>

                    </div>

                </div>

            </article>



            <!-- IMPACT REPORT -->

            <article class="impact-card">


                <div>

                    <span class="insight-label">

                        IMPACT REPORT

                    </span>


                    <h2>

                        SkillBridge Certified interns show
                        40% higher retention rates.

                    </h2>

                </div>


                <button
                    type="button"
                    class="whitepaper-btn"
                    id="whitepaperBtn">

                    Download<br>
                    Whitepaper

                </button>


            </article>

        </section>

    <?php include "../../../Includes/company_dashboard_footer.php"; ?>
</main>


<!-- =========================================================
     REPORTS JAVASCRIPT
========================================================= -->

<script src="../../../Assets/JS/Company/reports.js?v=<?php echo time(); ?>"></script>


<?php
include "../../../Includes/dash_footer.php";
?>
