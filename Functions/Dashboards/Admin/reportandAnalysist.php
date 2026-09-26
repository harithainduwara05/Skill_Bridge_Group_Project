<?php
/**
 * ==============================================================================
 * SkillBridge - Reports & Performance Analytics Dashboard (Admin)
 * Holistic ecosystem analytics across student placements, partner universities,
 * employer engagement, in-demand technical skills, and platform health.
 * FRONTEND WITH REALISTIC DUMMY DATA (ZERO BACKEND CALLS).
 * ==============================================================================
 */

include "../../../Config/db.php";
include "../../../Session/session.php";

require_login();
require_role('admin');
$user = current_user();

include "../../../Includes/admin_sidebar.php";
?>

<!-- Custom Reports & Analytics Stylesheet -->
<link rel="stylesheet" href="../../../Assets/CSS/Admin/reports_analytics.css?v=<?php echo time(); ?>">

<?php
include "../../../Includes/dash_header.php";
?>

<main class="content">
    <div class="ra-container">

        <!-- ==================================================================
             1. Page Header (Title, Subtitle, Period Pills & Export Actions)
             ================================================================== -->
        <div class="ra-header-row">
            <div class="ra-header-info">
                <h1>
                    <span class="material-symbols-outlined">insights</span>
                    Reports & Performance Analytics
                </h1>
                <p>Ecosystem overview across student placements, university benchmarks, industry demand, and platform health.</p>
            </div>

            <div class="ra-header-actions">
                <!-- Time Period Selector Pills -->
                <div class="ra-period-pills">
                    <button type="button" class="ra-period-btn active" data-period="30d">Last 30 Days</button>
                    <button type="button" class="ra-period-btn" data-period="quarter">This Quarter</button>
                    <button type="button" class="ra-period-btn" data-period="ytd">Year to Date (2026)</button>
                    <button type="button" class="ra-period-btn" data-period="all">All Time</button>
                </div>

                <!-- Export Action -->
                <button type="button" class="ra-btn-export" id="raBtnOpenExport">
                    <span class="material-symbols-outlined">download</span>
                    Export Report
                </button>

                <!-- Print Action -->
                <button type="button" class="ra-btn-secondary" id="raBtnPrint" title="Print Executive Summary">
                    <span class="material-symbols-outlined">print</span>
                    Print
                </button>
            </div>
        </div>

        <!-- ==================================================================
             2. Executive KPI Summary Cards (4 Cards Grid)
             ================================================================== -->
        <div class="ra-kpi-grid">
            
            <!-- Card 1: Total Placed Students -->
            <div class="ra-kpi-card kpi-blue">
                <div class="ra-kpi-top">
                    <div class="ra-kpi-icon icon-blue">
                        <span class="material-symbols-outlined">school</span>
                    </div>
                    <span class="ra-kpi-trend trend-up" id="raKpiPlacedTrend">
                        <span class="material-symbols-outlined">trending_up</span> +18.4% vs last mo
                    </span>
                </div>
                <div class="ra-kpi-label">Placed Students</div>
                <div class="ra-kpi-value" id="raKpiPlaced">1,428</div>
                <div class="ra-kpi-subtext" id="raKpiPlacedSub">
                    <span class="material-symbols-outlined">verified</span> 89.2% Placement Rate
                </div>
            </div>

            <!-- Card 2: Industry Partners & Drives -->
            <div class="ra-kpi-card kpi-indigo">
                <div class="ra-kpi-top">
                    <div class="ra-kpi-icon icon-indigo">
                        <span class="material-symbols-outlined">apartment</span>
                    </div>
                    <span class="ra-kpi-trend trend-up" id="raKpiCompanyTrend">
                        <span class="material-symbols-outlined">trending_up</span> +14 New this month
                    </span>
                </div>
                <div class="ra-kpi-label">Active Industry Partners</div>
                <div class="ra-kpi-value" id="raKpiCompanies">168 Companies</div>
                <div class="ra-kpi-subtext" id="raKpiCompanySub">
                    <span class="material-symbols-outlined">work</span> 432 Active Drives
                </div>
            </div>

            <!-- Card 3: Student Projects Completed -->
            <div class="ra-kpi-card kpi-green">
                <div class="ra-kpi-top">
                    <div class="ra-kpi-icon icon-green">
                        <span class="material-symbols-outlined">task_alt</span>
                    </div>
                    <span class="ra-kpi-trend trend-up" id="raKpiProjectTrend">
                        <span class="material-symbols-outlined">task_alt</span> 94.6% Completed
                    </span>
                </div>
                <div class="ra-kpi-label">Industry Projects</div>
                <div class="ra-kpi-value" id="raKpiProjects">842 Projects</div>
                <div class="ra-kpi-subtext" id="raKpiProjectSub">
                    <span class="material-symbols-outlined">pending_actions</span> 124 In Supervisor Review
                </div>
            </div>

            <!-- Card 4: Platform Grievance Resolution -->
            <div class="ra-kpi-card kpi-amber">
                <div class="ra-kpi-top">
                    <div class="ra-kpi-icon icon-amber">
                        <span class="material-symbols-outlined">verified_user</span>
                    </div>
                    <span class="ra-kpi-trend trend-neutral" id="raKpiResolutionTrend">
                        <span class="material-symbols-outlined">timer</span> 2.4h Avg Response
                    </span>
                </div>
                <div class="ra-kpi-label">Grievance Resolution</div>
                <div class="ra-kpi-value" id="raKpiResolution">98.1% Resolved</div>
                <div class="ra-kpi-subtext" id="raKpiResolutionSub">
                    <span class="material-symbols-outlined">report_problem</span> 3 Open in Queue
                </div>
            </div>

        </div>

        <!-- ==================================================================
             3. Domain Filter Navigation Tabs
             ================================================================== -->
        <div class="ra-tabs-bar">
            <button type="button" class="ra-tab-btn active" data-tab="all">
                <span class="material-symbols-outlined">dashboard</span>
                All Metrics
            </button>
            <button type="button" class="ra-tab-btn" data-tab="placements">
                <span class="material-symbols-outlined">how_to_reg</span>
                Placements & Hiring
            </button>
            <button type="button" class="ra-tab-btn" data-tab="universities">
                <span class="material-symbols-outlined">account_balance</span>
                University Benchmarks
            </button>
            <button type="button" class="ra-tab-btn" data-tab="skills">
                <span class="material-symbols-outlined">psychology</span>
                Market Skills Demand
            </button>
            <button type="button" class="ra-tab-btn" data-tab="operations">
                <span class="material-symbols-outlined">settings_heart</span>
                Platform Operations
            </button>
        </div>

        <!-- ==================================================================
             4. Visual Analytics Charts Grid - Row 1
             ================================================================== -->
        <div class="ra-charts-grid-row" id="secChartsMain">
            
            <!-- Chart 1: Hiring Velocity & Applications (Native SVG Area & Line Chart) -->
            <div class="ra-chart-card">
                <div class="ra-chart-header">
                    <div>
                        <h3>Hiring Velocity & Placement Trends</h3>
                        <p>Monthly application volume compared to confirmed industry placements.</p>
                    </div>
                    <div class="ra-chart-tools">
                        <span class="ra-badge-pill badge-live">Live Analytics</span>
                    </div>
                </div>

                <div class="ra-svg-chart-container">
                    <!-- SVG Area / Line Chart -->
                    <svg id="raHiringSvg" class="ra-svg-chart" viewBox="0 0 660 230" preserveAspectRatio="none">
                        <defs>
                            <linearGradient id="gradApps" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="0%" stop-color="#2563eb" stop-opacity="0.25" />
                                <stop offset="100%" stop-color="#2563eb" stop-opacity="0.0" />
                            </linearGradient>
                            <linearGradient id="gradHired" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="0%" stop-color="#16a34a" stop-opacity="0.25" />
                                <stop offset="100%" stop-color="#16a34a" stop-opacity="0.0" />
                            </linearGradient>
                        </defs>

                        <!-- Horizontal Grid Lines -->
                        <g class="ra-grid-lines">
                            <line x1="50" y1="25" x2="640" y2="25" stroke="#f1f5f9" stroke-width="1" stroke-dasharray="4" />
                            <line x1="50" y1="70" x2="640" y2="70" stroke="#f1f5f9" stroke-width="1" stroke-dasharray="4" />
                            <line x1="50" y1="115" x2="640" y2="115" stroke="#f1f5f9" stroke-width="1" stroke-dasharray="4" />
                            <line x1="50" y1="160" x2="640" y2="160" stroke="#f1f5f9" stroke-width="1" stroke-dasharray="4" />
                            <line x1="50" y1="195" x2="640" y2="195" stroke="#e2e8f0" stroke-width="1.5" />
                        </g>

                        <!-- Y Axis Numeric Labels -->
                        <g class="ra-axis-labels-y" font-size="11" fill="#94a3b8" text-anchor="end" font-family="'Inter', sans-serif">
                            <text x="42" y="29" id="raYLabel4">800</text>
                            <text x="42" y="74" id="raYLabel3">600</text>
                            <text x="42" y="119" id="raYLabel2">400</text>
                            <text x="42" y="164" id="raYLabel1">200</text>
                            <text x="42" y="199">0</text>
                        </g>

                        <!-- Shaded Area Fills -->
                        <path id="raPathAreaApps" fill="url(#gradApps)" d="" />
                        <path id="raPathAreaHired" fill="url(#gradHired)" d="" />

                        <!-- Line Strokes -->
                        <path id="raPathLineApps" fill="none" stroke="#2563eb" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" d="" />
                        <path id="raPathLineHired" fill="none" stroke="#16a34a" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" d="" />

                        <!-- Interactive Data Points (Dots) -->
                        <g id="raDotsApps" class="ra-chart-dots apps"></g>
                        <g id="raDotsHired" class="ra-chart-dots hired"></g>
                    </svg>

                    <!-- Bottom X Axis Month/Period Labels -->
                    <div id="raXLabelsRow" class="ra-x-labels-row">
                        <span>Week 1</span>
                        <span>Week 2</span>
                        <span>Week 3</span>
                        <span>Week 4</span>
                    </div>

                    <!-- Interactive Custom Tooltip Bubble -->
                    <div id="raSvgTooltip" class="ra-svg-tooltip"></div>
                </div>

                <!-- Chart Custom Legend -->
                <div class="ra-chart-legend-row">
                    <div class="ra-legend-item">
                        <span class="ra-legend-dot dot-blue"></span>
                        <span>Applications Submitted</span>
                    </div>
                    <div class="ra-legend-item">
                        <span class="ra-legend-dot dot-green"></span>
                        <span>Students Placed & Hired</span>
                    </div>
                </div>
            </div>

            <!-- Chart 2: Student Share by University (Native SVG Donut) -->
            <div class="ra-chart-card">
                <div class="ra-chart-header">
                    <div>
                        <h3>Student Enrolment by Institute</h3>
                        <p>Distribution of active student candidates across partner universities.</p>
                    </div>
                </div>

                <div class="ra-donut-wrap">
                    <div class="ra-donut-svg-box">
                        <svg class="ra-donut-svg" viewBox="0 0 200 200">
                            <!-- Background base ring -->
                            <circle cx="100" cy="100" r="70" fill="none" stroke="#f8fafc" stroke-width="26" />
                            
                            <!-- Dynamic SVG Donut Segments (circumference = 440) -->
                            <!-- UCSC: 36% (158.4) -->
                            <circle class="ra-donut-seg" cx="100" cy="100" r="70" fill="none" stroke="#2563eb" stroke-width="24" stroke-dasharray="158.4 440" stroke-dashoffset="0" />
                            <!-- Moratuwa: 28% (123.2) -->
                            <circle class="ra-donut-seg" cx="100" cy="100" r="70" fill="none" stroke="#f59e0b" stroke-width="24" stroke-dasharray="123.2 440" stroke-dashoffset="-158.4" />
                            <!-- Kelaniya: 18% (79.2) -->
                            <circle class="ra-donut-seg" cx="100" cy="100" r="70" fill="none" stroke="#8b5cf6" stroke-width="24" stroke-dasharray="79.2 440" stroke-dashoffset="-281.6" />
                            <!-- SLIIT: 12% (52.8) -->
                            <circle class="ra-donut-seg" cx="100" cy="100" r="70" fill="none" stroke="#ef4444" stroke-width="24" stroke-dasharray="52.8 440" stroke-dashoffset="-360.8" />
                            <!-- Others: 6% (26.4) -->
                            <circle class="ra-donut-seg" cx="100" cy="100" r="70" fill="none" stroke="#94a3b8" stroke-width="24" stroke-dasharray="26.4 440" stroke-dashoffset="-413.6" />
                        </svg>

                        <!-- Donut Center Label -->
                        <div class="ra-donut-center-info">
                            <strong id="raDonutTotalCandidates">3,850</strong>
                            <span>Active Students</span>
                        </div>
                    </div>

                    <!-- Donut Legend Breakdown -->
                    <div class="ra-donut-legend-list">
                        <div class="ra-donut-legend-item">
                            <span class="ra-donut-dot" style="background: #2563eb;"></span>
                            <span class="ra-donut-uni-name">UCSC (Colombo)</span>
                            <strong class="ra-donut-val">36% <small style="color:#64748b; font-weight:normal;">(1,386)</small></strong>
                        </div>
                        <div class="ra-donut-legend-item">
                            <span class="ra-donut-dot" style="background: #f59e0b;"></span>
                            <span class="ra-donut-uni-name">UoM (Moratuwa IT)</span>
                            <strong class="ra-donut-val">28% <small style="color:#64748b; font-weight:normal;">(1,078)</small></strong>
                        </div>
                        <div class="ra-donut-legend-item">
                            <span class="ra-donut-dot" style="background: #8b5cf6;"></span>
                            <span class="ra-donut-uni-name">Univ of Kelaniya</span>
                            <strong class="ra-donut-val">18% <small style="color:#64748b; font-weight:normal;">(693)</small></strong>
                        </div>
                        <div class="ra-donut-legend-item">
                            <span class="ra-donut-dot" style="background: #ef4444;"></span>
                            <span class="ra-donut-uni-name">SLIIT (Computing & Eng)</span>
                            <strong class="ra-donut-val">12% <small style="color:#64748b; font-weight:normal;">(462)</small></strong>
                        </div>
                        <div class="ra-donut-legend-item">
                            <span class="ra-donut-dot" style="background: #94a3b8;"></span>
                            <span class="ra-donut-uni-name">Other Partners</span>
                            <strong class="ra-donut-val">6% <small style="color:#64748b; font-weight:normal;">(231)</small></strong>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- ==================================================================
             5. Visual Analytics Charts Grid - Row 2
             ================================================================== -->
        <div class="ra-charts-grid-equal" id="secChartsSkills">
            
            <!-- Skill Demand Progress Bars -->
            <div class="ra-chart-card">
                <div class="ra-chart-header">
                    <div>
                        <h3>Top In-Demand Technical Skills</h3>
                        <p>Most requested competencies across open industry internship drives.</p>
                    </div>
                    <span class="ra-badge-pill">2026 Tech Index</span>
                </div>

                <div class="ra-skills-list">
                    <div class="ra-skill-item">
                        <div class="ra-skill-meta">
                            <span>Full-Stack Web (React, Node.js, Next.js)</span>
                            <span>88% Demand</span>
                        </div>
                        <div class="ra-skill-bar-bg">
                            <div class="ra-skill-bar-fill fill-blue" style="width: 88%;"></div>
                        </div>
                    </div>

                    <div class="ra-skill-item">
                        <div class="ra-skill-meta">
                            <span>Cloud Computing & DevOps (AWS, Docker, CI/CD)</span>
                            <span>79% Demand</span>
                        </div>
                        <div class="ra-skill-bar-bg">
                            <div class="ra-skill-bar-fill fill-indigo" style="width: 79%;"></div>
                        </div>
                    </div>

                    <div class="ra-skill-item">
                        <div class="ra-skill-meta">
                            <span>Python, AI/ML Modeling & Data Analytics</span>
                            <span>74% Demand</span>
                        </div>
                        <div class="ra-skill-bar-bg">
                            <div class="ra-skill-bar-fill fill-green" style="width: 74%;"></div>
                        </div>
                    </div>

                    <div class="ra-skill-item">
                        <div class="ra-skill-meta">
                            <span>Mobile Application Development (Flutter, React Native)</span>
                            <span>62% Demand</span>
                        </div>
                        <div class="ra-skill-bar-bg">
                            <div class="ra-skill-bar-fill fill-amber" style="width: 62%;"></div>
                        </div>
                    </div>

                    <div class="ra-skill-item">
                        <div class="ra-skill-meta">
                            <span>Cyber Security & Threat Assessment</span>
                            <span>54% Demand</span>
                        </div>
                        <div class="ra-skill-bar-bg">
                            <div class="ra-skill-bar-fill fill-purple" style="width: 54%;"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Internship Funnel Progress Steps -->
            <div class="ra-chart-card">
                <div class="ra-chart-header">
                    <div>
                        <h3>Application Conversion Funnel</h3>
                        <p>End-to-end recruitment funnel conversion rate from submission to hired.</p>
                    </div>
                    <span class="ra-badge-pill">Funnel Health</span>
                </div>

                <div class="ra-funnel-grid">
                    <div class="ra-funnel-step">
                        <div class="ra-funnel-step-num">Step 1</div>
                        <div class="ra-funnel-val">4,850</div>
                        <div class="ra-funnel-label">Applications</div>
                        <span class="ra-funnel-pct">100% Volume</span>
                    </div>

                    <div class="ra-funnel-step">
                        <div class="ra-funnel-step-num">Step 2</div>
                        <div class="ra-funnel-val">2,910</div>
                        <div class="ra-funnel-label">Shortlisted</div>
                        <span class="ra-funnel-pct">60.0% Pass</span>
                    </div>

                    <div class="ra-funnel-step">
                        <div class="ra-funnel-step-num">Step 3</div>
                        <div class="ra-funnel-val">1,840</div>
                        <div class="ra-funnel-label">Interviews</div>
                        <span class="ra-funnel-pct">37.9% Screen</span>
                    </div>

                    <div class="ra-funnel-step">
                        <div class="ra-funnel-step-num">Step 4</div>
                        <div class="ra-funnel-val">1,428</div>
                        <div class="ra-funnel-label">Hired Offers</div>
                        <span class="ra-funnel-pct" style="background: #dcfce7; color: #15803d;">29.4% Hired</span>
                    </div>
                </div>

                <div style="margin-top: 20px; padding: 14px 16px; background: #f8fafc; border: 1px dashed var(--ra-border); border-radius: var(--ra-radius-md); display: flex; align-items: center; justify-content: space-between;">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <span class="material-symbols-outlined" style="color: #2563eb; font-size: 22px;">speed</span>
                        <div>
                            <strong style="font-size: 13px; color: var(--ra-text-main); display: block;">Average Time-to-Offer</strong>
                            <span style="font-size: 12px; color: var(--ra-text-muted);">From first candidate application to contract offer issuance</span>
                        </div>
                    </div>
                    <span style="font-size: 15px; font-weight: 800; color: #0b2246;">11.4 Days</span>
                </div>
            </div>

        </div>

        <!-- ==================================================================
             6. Deep-Dive Table 1: Top Performing Partner Universities
             ================================================================== -->
        <div class="ra-table-card" id="secTableUnis">
            <div class="ra-table-header">
                <div>
                    <h3>Top Partner Universities Performance Benchmark</h3>
                    <p>Comparative metrics on candidate quality, project completion, and student hiring percentage.</p>
                </div>
                <span class="ra-badge-pill">Institutes Audit</span>
            </div>

            <div class="ra-table-responsive">
                <table class="ra-table">
                    <thead>
                        <tr>
                            <th>University / Institute</th>
                            <th>Enrolled Students</th>
                            <th>Verification Rate</th>
                            <th>Active Interns</th>
                            <th>Projects Completed</th>
                            <th>Placement %</th>
                            <th>Rating</th>
                        </tr>
                    </thead>
                        <!-- Rank 1: UCSC (Top Government University) -->
                        <tr>
                            <td>
                                <div class="ra-entity-cell">
                                    <div class="ra-entity-icon uni-ucsc">UC</div>
                                    <div class="ra-entity-info">
                                        <span class="ra-entity-name">University of Colombo School of Computing (UCSC)</span>
                                        <span class="ra-entity-sub">Colombo 07 &bull; Computer Science & Information Systems</span>
                                    </div>
                                </div>
                            </td>
                            <td><strong>1,386</strong></td>
                            <td><span class="ra-pill ra-pill-green">99.5% Verified</span></td>
                            <td>580 Students</td>
                            <td>412 Projects</td>
                            <td><strong style="color: #16a34a;">96.4%</strong></td>
                            <td>
                                <span class="ra-rating-badge">
                                    <span class="material-symbols-outlined">star</span> 5.0
                                </span>
                            </td>
                        </tr>

                        <!-- Rank 2: University of Moratuwa -->
                        <tr>
                            <td>
                                <div class="ra-entity-cell">
                                    <div class="ra-entity-icon uni-moratuwa">UM</div>
                                    <div class="ra-entity-info">
                                        <span class="ra-entity-name">University of Moratuwa (UoM)</span>
                                        <span class="ra-entity-sub">Katubedda &bull; Faculty of Information Technology</span>
                                    </div>
                                </div>
                            </td>
                            <td><strong>1,078</strong></td>
                            <td><span class="ra-pill ra-pill-green">99.2% Verified</span></td>
                            <td>460 Students</td>
                            <td>328 Projects</td>
                            <td><strong style="color: #16a34a;">94.8%</strong></td>
                            <td>
                                <span class="ra-rating-badge">
                                    <span class="material-symbols-outlined">star</span> 4.9
                                </span>
                            </td>
                        </tr>

                        <!-- Rank 3: University of Kelaniya -->
                        <tr>
                            <td>
                                <div class="ra-entity-cell">
                                    <div class="ra-entity-icon uni-kln">KL</div>
                                    <div class="ra-entity-info">
                                        <span class="ra-entity-name">University of Kelaniya</span>
                                        <span class="ra-entity-sub">Dalugama &bull; Department of Industrial Management</span>
                                    </div>
                                </div>
                            </td>
                            <td><strong>693</strong></td>
                            <td><span class="ra-pill ra-pill-green">98.4% Verified</span></td>
                            <td>290 Students</td>
                            <td>215 Projects</td>
                            <td><strong style="color: #16a34a;">91.2%</strong></td>
                            <td>
                                <span class="ra-rating-badge">
                                    <span class="material-symbols-outlined">star</span> 4.8
                                </span>
                            </td>
                        </tr>

                        <!-- Rank 4: SLIIT (Non-state Institute - Lower than Government Universities) -->
                        <tr>
                            <td>
                                <div class="ra-entity-cell">
                                    <div class="ra-entity-icon uni-sliit">SL</div>
                                    <div class="ra-entity-info">
                                        <span class="ra-entity-name">Sri Lanka Institute of Information Technology (SLIIT)</span>
                                        <span class="ra-entity-sub">Malabe & Metro Campuses &bull; Computing & Engineering</span>
                                    </div>
                                </div>
                            </td>
                            <td><strong>462</strong></td>
                            <td><span class="ra-pill ra-pill-blue">96.5% Verified</span></td>
                            <td>185 Students</td>
                            <td>130 Projects</td>
                            <td><strong style="color: #16a34a;">87.5%</strong></td>
                            <td>
                                <span class="ra-rating-badge">
                                    <span class="material-symbols-outlined">star</span> 4.6
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ==================================================================
             7. Deep-Dive Table 2: Top Recruiting Industry Partners
             ================================================================== -->
        <div class="ra-table-card" id="secTableCompanies">
            <div class="ra-table-header">
                <div>
                    <h3>Top Recruiting Industry Partners & Hiring Velocity</h3>
                    <p>Overview of corporate recruiters with highest intern engagement and positive mentorship ratings.</p>
                </div>
                <span class="ra-badge-pill">Enterprise Partners</span>
            </div>

            <div class="ra-table-responsive">
                <table class="ra-table">
                    <thead>
                        <tr>
                            <th>Company / Partner</th>
                            <th>Industry Sector</th>
                            <th>Active Vacancies</th>
                            <th>Applications Received</th>
                            <th>Offers Extended</th>
                            <th>Placement Velocity</th>
                            <th>Mentor Index</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <div class="ra-entity-cell">
                                    <div class="ra-entity-icon comp-wso2">W2</div>
                                    <div class="ra-entity-info">
                                        <span class="ra-entity-name">WSO2 Lanka (Pvt) Ltd</span>
                                        <span class="ra-entity-sub">Enterprise Middleware & Cloud Solutions</span>
                                    </div>
                                </div>
                            </td>
                            <td>Enterprise Software</td>
                            <td><strong>42 Openings</strong></td>
                            <td>460 Candidates</td>
                            <td><strong style="color: #16a34a;">38 Hired</strong></td>
                            <td><span class="ra-pill ra-pill-green">Fast (8 Days)</span></td>
                            <td>
                                <span class="ra-rating-badge">
                                    <span class="material-symbols-outlined">star</span> 4.9
                                </span>
                            </td>
                        </tr>

                        <tr>
                            <td>
                                <div class="ra-entity-cell">
                                    <div class="ra-entity-icon comp-ifs">IFS</div>
                                    <div class="ra-entity-info">
                                        <span class="ra-entity-name">IFS R&D International</span>
                                        <span class="ra-entity-sub">ERP & Industrial AI Cloud Platforms</span>
                                    </div>
                                </div>
                            </td>
                            <td>Enterprise Cloud</td>
                            <td><strong>35 Openings</strong></td>
                            <td>390 Candidates</td>
                            <td><strong style="color: #16a34a;">32 Hired</strong></td>
                            <td><span class="ra-pill ra-pill-green">Fast (9 Days)</span></td>
                            <td>
                                <span class="ra-rating-badge">
                                    <span class="material-symbols-outlined">star</span> 4.8
                                </span>
                            </td>
                        </tr>

                        <tr>
                            <td>
                                <div class="ra-entity-cell">
                                    <div class="ra-entity-icon comp-lseg">LS</div>
                                    <div class="ra-entity-info">
                                        <span class="ra-entity-name">London Stock Exchange Group (LSEG)</span>
                                        <span class="ra-entity-sub">Financial Markets Technology & Infrastructure</span>
                                    </div>
                                </div>
                            </td>
                            <td>FinTech & Trading</td>
                            <td><strong>28 Openings</strong></td>
                            <td>320 Candidates</td>
                            <td><strong style="color: #16a34a;">26 Hired</strong></td>
                            <td><span class="ra-pill ra-pill-blue">Standard (12 Days)</span></td>
                            <td>
                                <span class="ra-rating-badge">
                                    <span class="material-symbols-outlined">star</span> 4.9
                                </span>
                            </td>
                        </tr>

                        <tr>
                            <td>
                                <div class="ra-entity-cell">
                                    <div class="ra-entity-icon comp-virtusa">VT</div>
                                    <div class="ra-entity-info">
                                        <span class="ra-entity-name">Virtusa Corporation</span>
                                        <span class="ra-entity-sub">Digital Business Transformation & Engineering</span>
                                    </div>
                                </div>
                            </td>
                            <td>IT Consulting</td>
                            <td><strong>50 Openings</strong></td>
                            <td>510 Candidates</td>
                            <td><strong style="color: #16a34a;">44 Hired</strong></td>
                            <td><span class="ra-pill ra-pill-green">Fast (10 Days)</span></td>
                            <td>
                                <span class="ra-rating-badge">
                                    <span class="material-symbols-outlined">star</span> 4.7
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</main>

<!-- ==========================================================================
     8. Export Report Custom Modal (Mockup with format & scope options)
     ========================================================================== -->
<div class="ra-modal-backdrop" id="raExportModal">
    <div class="ra-modal-dialog">
        <div class="ra-modal-header">
            <h3>Generate Analytics Report</h3>
            <button type="button" class="ra-modal-close" id="raBtnCloseExport" title="Close">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>

        <div class="ra-modal-body">
            
            <div class="ra-form-group">
                <label for="raReportScope">Select Report Scope</label>
                <select id="raReportScope" class="ra-form-select">
                    <option value="executive">Full Platform Executive Summary (All Domains)</option>
                    <option value="placements">Student Placements & Hiring Velocity Report</option>
                    <option value="universities">Partner Universities Audit & Completion Index</option>
                    <option value="companies">Corporate Recruiters & Openings Audit</option>
                    <option value="complaints">Grievances Resolution & Support Log</option>
                </select>
            </div>

            <div class="ra-form-group">
                <label>Select Export File Format</label>
                <div class="ra-format-cards">
                    <div class="ra-format-option active" data-format="PDF">
                        <span class="material-symbols-outlined" style="color: #dc2626;">picture_as_pdf</span>
                        <span>PDF Document</span>
                    </div>
                    <div class="ra-format-option" data-format="XLSX">
                        <span class="material-symbols-outlined" style="color: #16a34a;">table_view</span>
                        <span>Excel (.xlsx)</span>
                    </div>
                    <div class="ra-format-option" data-format="CSV">
                        <span class="material-symbols-outlined" style="color: #2563eb;">description</span>
                        <span>CSV Sheet</span>
                    </div>
                </div>
            </div>

            <div class="ra-form-group">
                <label for="raReportTimeframe">Time Horizon</label>
                <select id="raReportTimeframe" class="ra-form-select">
                    <option value="30d">Current Month (September 2026)</option>
                    <option value="quarter">Third Quarter (Q3 2026)</option>
                    <option value="ytd">Year to Date (Jan - Sep 2026)</option>
                    <option value="lifetime">Full Lifetime Historical Data</option>
                </select>
            </div>

            <div style="background: #f8fafc; border: 1px solid var(--ra-border); border-radius: 10px; padding: 12px 14px; display: flex; flex-direction: column; gap: 8px;">
                <label style="display: flex; align-items: center; gap: 8px; font-size: 13px; color: #334155; cursor: pointer;">
                    <input type="checkbox" checked style="accent-color: #2563eb; width: 16px; height: 16px;">
                    Include high-resolution graphical charts and visual summaries
                </label>
                <label style="display: flex; align-items: center; gap: 8px; font-size: 13px; color: #334155; cursor: pointer;">
                    <input type="checkbox" checked style="accent-color: #2563eb; width: 16px; height: 16px;">
                    Anonymize student private contact details in export
                </label>
            </div>

        </div>

        <div class="ra-modal-footer">
            <button type="button" class="ra-btn-secondary" id="raBtnCancelExport">Cancel</button>
            <button type="button" class="ra-btn-export" id="raBtnGenerateReport">
                <span class="material-symbols-outlined">download</span>
                Generate & Download
            </button>
        </div>
    </div>
</div>

<!-- ==========================================================================
     9. Toast Notification Alert
     ========================================================================== -->
<div class="ra-toast" id="raToast">
    <span class="material-symbols-outlined">check_circle</span>
    <span id="raToastMessage">Report generated successfully!</span>
</div>

<!-- Footer -->
<footer class="footer">
    <div>&copy; 2026 SkillBridge. All rights reserved.</div>
    <div class="footer-links">
        <a href="/Skill_Bridge_Group_Project/help_center.php">Help Center</a>
        <a href="#">Privacy Policy</a>
        <a href="#">Terms of Service</a>
    </div>
</footer>

<!-- Pure Native Vanilla JS Reports & Analytics Script (Zero External Libraries) -->
<script src="../../../Assets/JS/Admin/reports_analytics.js?v=<?php echo time(); ?>"></script>

<?php include "../../../Includes/dash_footer.php"; ?>