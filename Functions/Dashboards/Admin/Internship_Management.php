<?php
/**
 * ==============================================================================
 * SkillBridge - Internship Management Dashboard (Admin)
 * Provides comprehensive oversight of internship opportunities, company postings,
 * skill requirements, application trends, and approval analytics.
 * ==============================================================================
 */

include "../../../Config/db.php";
require_once "../../../Session/Session.php";

require_login();
require_role('admin');
$user = current_user();

require_once "AdminBackend.php";

include "../../../Includes/admin_sidebar.php";
?>

<!-- Internship Management Custom Stylesheet -->
<link rel="stylesheet" href="../../../Assets/CSS/Admin/internship_management.css?v=<?php echo time(); ?>">

<?php
include "../../../Includes/dash_header.php";
?>

<main class="content">
    <div class="im-container">

        <!-- ==================================================================
             1. Page Header (Title, Subtitle & Add Action)
             ================================================================== -->
        <div class="im-header-row">
            <div class="im-header-info">
                <h1>Internship Management</h1>
                <p>Monitor, review, and manage internship opportunities provided by companies across all sectors.</p>
            </div>
        </div>

        <!-- ==================================================================
             2. KPI Summary Cards (4 Cards Grid)
             ================================================================== -->
        <div class="im-stats-grid">

            <!-- Card 1: Total Internships -->
            <div class="im-stat-card">
                <div class="im-stat-card-top">
                    <div class="im-stat-icon icon-navy">
                        <span class="material-symbols-outlined">work</span>
                    </div>
                    <span class="im-stat-badge badge-green">
                        +12%
                    </span>
                </div>
                <div class="im-stat-details">
                    <div class="im-stat-label">Total Internships</div>
                    <div class="im-stat-value">432</div>
                </div>
            </div>

            <!-- Card 2: Active Internships -->
            <div class="im-stat-card">
                <div class="im-stat-card-top">
                    <div class="im-stat-icon icon-green">
                        <span class="material-symbols-outlined">check_circle</span>
                    </div>
                    <span class="im-stat-badge badge-green">
                        86% Cap
                    </span>
                </div>
                <div class="im-stat-details">
                    <div class="im-stat-label">Active Internships</div>
                    <div class="im-stat-value">286</div>
                </div>
            </div>

            <!-- Card 3: Suspended Posts -->
            <div class="im-stat-card">
                <div class="im-stat-card-top">
                    <div class="im-stat-icon" style="background-color: #fee2e2; color: #dc2626;">
                        <span class="material-symbols-outlined">block</span>
                    </div>
                    <span class="im-stat-badge" style="background-color: #fef2f2; color: #dc2626; border: 1px solid #fecaca;">
                        Policy Action
                    </span>
                </div>
                <div class="im-stat-details">
                    <div class="im-stat-label">Suspended Posts</div>
                    <div class="im-stat-value" id="kpiSuspendedCount">14</div>
                </div>
            </div>

            <!-- Card 4: Total Applications -->
            <div class="im-stat-card">
                <div class="im-stat-card-top">
                    <div class="im-stat-icon icon-blue">
                        <span class="material-symbols-outlined">group</span>
                    </div>
                    <span class="im-stat-badge badge-blue">
                        Avg. 6.5/Job
                    </span>
                </div>
                <div class="im-stat-details">
                    <div class="im-stat-label">Total Applications</div>
                    <div class="im-stat-value" id="kpiTotalApps">2,850</div>
                </div>
            </div>

        </div>

        <!-- ==================================================================
             3. Filter & Search Toolbar Card
             ================================================================== -->
        <div class="im-toolbar-card">
            <div class="im-toolbar-controls">
                
                <!-- Search Box -->
                <div class="im-search-wrap">
                    <span class="material-symbols-outlined">search</span>
                    <input type="text" id="imSearchInput" class="im-search-input" placeholder="Search internships by title, ID, or company...">
                </div>

                <!-- Status Filter -->
                <div class="im-select-wrap">
                    <span class="material-symbols-outlined im-select-icon">tune</span>
                    <select id="imStatusFilter" class="im-select" aria-label="Filter by Status">
                        <option value="all" selected>Status: All</option>
                        <option value="active">Active</option>
                        <option value="suspended">Suspended</option>
                        <option value="closed">Closed</option>
                    </select>
                    <span class="material-symbols-outlined im-select-chevron">expand_more</span>
                </div>

                <!-- Industry Filter -->
                <div class="im-select-wrap">
                    <span class="material-symbols-outlined im-select-icon">domain</span>
                    <select id="imIndustryFilter" class="im-select" aria-label="Filter by Industry">
                        <option value="all" selected>Industry</option>
                        <option value="software development">Software Development</option>
                        <option value="artificial intelligence">Artificial Intelligence</option>
                        <option value="mobile development">Mobile Development</option>
                        <option value="cloud computing">Cloud Computing</option>
                        <option value="cybersecurity">Cybersecurity</option>
                    </select>
                    <span class="material-symbols-outlined im-select-chevron">expand_more</span>
                </div>

                <!-- Duration / Mode Filter -->
                <div class="im-select-wrap">
                    <span class="material-symbols-outlined im-select-icon">schedule</span>
                    <select id="imDurationFilter" class="im-select" aria-label="Filter by Duration or Mode">
                        <option value="all" selected>Duration</option>
                        <option value="full-time">Full-time</option>
                        <option value="part-time">Part-time</option>
                        <option value="remote">Remote</option>
                        <option value="hybrid">Hybrid</option>
                        <option value="on-site">On-site</option>
                    </select>
                    <span class="material-symbols-outlined im-select-chevron">expand_more</span>
                </div>

            </div>
        </div>

        <!-- ==================================================================
             4. Internships Data Table Card
             ================================================================== -->
        <div class="im-table-card">
            <div class="im-table-responsive">
                <table class="im-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>POSITION</th>
                            <th>COMPANY</th>
                            <th>INDUSTRY</th>
                            <th>REQUIRED SKILLS</th>
                            <th>APPLICATIONS</th>
                            <th>DEADLINE</th>
                            <th>STATUS</th>
                            <th class="im-actions-header">ACTIONS</th>
                        </tr>
                    </thead>
                    <tbody id="imTableBody">

                        <!-- Row 1: Software Engineer Intern -->
                        <tr class="im-data-row"
                            data-id="#INT001"
                            data-position="Software Engineer Intern"
                            data-company="Tech Solutions"
                            data-industry="Software Development"
                            data-skills="Java React SQL"
                            data-status="Active"
                            data-duration="Full-time • Remote"
                            data-reason="">
                            <td class="im-internship-id">#INT001</td>
                            <td>
                                <div class="im-position-meta">
                                    <span class="im-position-title">Software Engineer Intern</span>
                                    <span class="im-position-type">Full-time • Remote</span>
                                </div>
                            </td>
                            <td class="im-company-name">Tech Solutions</td>
                            <td>
                                <span class="im-industry-badge">Software Development</span>
                            </td>
                            <td>
                                <div class="im-skills-stack">
                                    <span class="im-skill-tag">Java</span>
                                    <span class="im-skill-tag">React</span>
                                    <span class="im-skill-tag">SQL</span>
                                </div>
                            </td>
                            <td class="im-applications-count">120</td>
                            <td class="im-deadline-date">30 Aug 2026</td>
                            <td class="im-status-cell">
                                <span class="im-status-badge status-active">
                                    <span class="im-status-dot"></span> Active
                                </span>
                            </td>
                            <td class="im-actions-cell">
                                <button type="button" class="im-btn-action" title="View Details" onclick="viewInternshipDetails('#INT001')">
                                    <span class="material-symbols-outlined">visibility</span>
                                </button>
                                <button type="button" class="im-btn-action im-btn-action-suspend" title="Suspend Post" onclick="openSuspendModal('#INT001')">
                                    <span class="material-symbols-outlined">block</span>
                                </button>
                            </td>
                        </tr>

                        <!-- Row 2: AI Research Intern -->
                        <tr class="im-data-row"
                            data-id="#INT002"
                            data-position="AI Research Intern"
                            data-company="Innovate Labs"
                            data-industry="Artificial Intelligence"
                            data-skills="Python ML"
                            data-status="Active"
                            data-duration="Part-time • On-site"
                            data-reason="">
                            <td class="im-internship-id">#INT002</td>
                            <td>
                                <div class="im-position-meta">
                                    <span class="im-position-title">AI Research Intern</span>
                                    <span class="im-position-type">Part-time • On-site</span>
                                </div>
                            </td>
                            <td class="im-company-name">Innovate Labs</td>
                            <td>
                                <span class="im-industry-badge ai">Artificial Intelligence</span>
                            </td>
                            <td>
                                <div class="im-skills-stack">
                                    <span class="im-skill-tag">Python</span>
                                    <span class="im-skill-tag">ML</span>
                                </div>
                            </td>
                            <td class="im-applications-count">85</td>
                            <td class="im-deadline-date">15 Sep 2026</td>
                            <td class="im-status-cell">
                                <span class="im-status-badge status-active">
                                    <span class="im-status-dot"></span> Active
                                </span>
                            </td>
                            <td class="im-actions-cell">
                                <button type="button" class="im-btn-action" title="View Details" onclick="viewInternshipDetails('#INT002')">
                                    <span class="material-symbols-outlined">visibility</span>
                                </button>
                                <button type="button" class="im-btn-action im-btn-action-suspend" title="Suspend Post" onclick="openSuspendModal('#INT002')">
                                    <span class="material-symbols-outlined">block</span>
                                </button>
                            </td>
                        </tr>

                        <!-- Row 3: Mobile Developer Intern -->
                        <tr class="im-data-row"
                            data-id="#INT003"
                            data-position="Mobile Developer Intern"
                            data-company="AppWorks"
                            data-industry="Mobile Development"
                            data-skills="Flutter Firebase"
                            data-status="Active"
                            data-duration="Contract • Hybrid"
                            data-reason="">
                            <td class="im-internship-id">#INT003</td>
                            <td>
                                <div class="im-position-meta">
                                    <span class="im-position-title">Mobile Developer Intern</span>
                                    <span class="im-position-type">Contract • Hybrid</span>
                                </div>
                            </td>
                            <td class="im-company-name">AppWorks</td>
                            <td>
                                <span class="im-industry-badge mobile">Mobile Development</span>
                            </td>
                            <td>
                                <div class="im-skills-stack">
                                    <span class="im-skill-tag">Flutter</span>
                                    <span class="im-skill-tag">Firebase</span>
                                </div>
                            </td>
                            <td class="im-applications-count">64</td>
                            <td class="im-deadline-date">10 Sep 2026</td>
                            <td class="im-status-cell">
                                <span class="im-status-badge status-active">
                                    <span class="im-status-dot"></span> Active
                                </span>
                            </td>
                            <td class="im-actions-cell">
                                <button type="button" class="im-btn-action" title="View Details" onclick="viewInternshipDetails('#INT003')">
                                    <span class="material-symbols-outlined">visibility</span>
                                </button>
                                <button type="button" class="im-btn-action im-btn-action-suspend" title="Suspend Post" onclick="openSuspendModal('#INT003')">
                                    <span class="material-symbols-outlined">block</span>
                                </button>
                            </td>
                        </tr>

                        <!-- Row 4: Cloud Infrastructure Intern -->
                        <tr class="im-data-row"
                            data-id="#INT004"
                            data-position="Cloud DevOps Intern"
                            data-company="CloudScale Ltd"
                            data-industry="Software Development"
                            data-skills="AWS Docker Kubernetes"
                            data-status="Active"
                            data-duration="Full-time • Hybrid"
                            data-reason="">
                            <td class="im-internship-id">#INT004</td>
                            <td>
                                <div class="im-position-meta">
                                    <span class="im-position-title">Cloud DevOps Intern</span>
                                    <span class="im-position-type">Full-time • Hybrid</span>
                                </div>
                            </td>
                            <td class="im-company-name">CloudScale Ltd</td>
                            <td>
                                <span class="im-industry-badge">Software Development</span>
                            </td>
                            <td>
                                <div class="im-skills-stack">
                                    <span class="im-skill-tag">AWS</span>
                                    <span class="im-skill-tag">Docker</span>
                                </div>
                            </td>
                            <td class="im-applications-count">92</td>
                            <td class="im-deadline-date">22 Sep 2026</td>
                            <td class="im-status-cell">
                                <span class="im-status-badge status-active">
                                    <span class="im-status-dot"></span> Active
                                </span>
                            </td>
                            <td class="im-actions-cell">
                                <button type="button" class="im-btn-action" title="View Details" onclick="viewInternshipDetails('#INT004')">
                                    <span class="material-symbols-outlined">visibility</span>
                                </button>
                                <button type="button" class="im-btn-action im-btn-action-suspend" title="Suspend Post" onclick="openSuspendModal('#INT004')">
                                    <span class="material-symbols-outlined">block</span>
                                </button>
                            </td>
                        </tr>

                        <!-- Row 5: Cybersecurity Analyst Intern -->
                        <tr class="im-data-row"
                            data-id="#INT005"
                            data-position="Cybersecurity Analyst Intern"
                            data-company="SecureNet Labs"
                            data-industry="Cybersecurity"
                            data-skills="Network Linux Wireshark"
                            data-status="Active"
                            data-duration="Full-time • On-site"
                            data-reason="">
                            <td class="im-internship-id">#INT005</td>
                            <td>
                                <div class="im-position-meta">
                                    <span class="im-position-title">Cybersecurity Analyst Intern</span>
                                    <span class="im-position-type">Full-time • On-site</span>
                                </div>
                            </td>
                            <td class="im-company-name">SecureNet Labs</td>
                            <td>
                                <span class="im-industry-badge">Cybersecurity</span>
                            </td>
                            <td>
                                <div class="im-skills-stack">
                                    <span class="im-skill-tag">Network</span>
                                    <span class="im-skill-tag">Linux</span>
                                </div>
                            </td>
                            <td class="im-applications-count">47</td>
                            <td class="im-deadline-date">28 Sep 2026</td>
                            <td class="im-status-cell">
                                <span class="im-status-badge status-active">
                                    <span class="im-status-dot"></span> Active
                                </span>
                            </td>
                            <td class="im-actions-cell">
                                <button type="button" class="im-btn-action" title="View Details" onclick="viewInternshipDetails('#INT005')">
                                    <span class="material-symbols-outlined">visibility</span>
                                </button>
                                <button type="button" class="im-btn-action im-btn-action-suspend" title="Suspend Post" onclick="openSuspendModal('#INT005')">
                                    <span class="material-symbols-outlined">block</span>
                                </button>
                            </td>
                        </tr>

                        <!-- Row 6: Data Analytics Intern (Suspended) -->
                        <tr class="im-data-row"
                            data-id="#INT006"
                            data-position="Data Analytics Intern"
                            data-company="Apex Data Corp"
                            data-industry="Software Development"
                            data-skills="Python PowerBI SQL"
                            data-status="Suspended"
                            data-duration="Part-time • Hybrid"
                            data-reason="Reported for deceptive stipend information and policy non-compliance">
                            <td class="im-internship-id">#INT006</td>
                            <td>
                                <div class="im-position-meta">
                                    <span class="im-position-title">Data Analytics Intern</span>
                                    <span class="im-position-type">Part-time • Hybrid</span>
                                </div>
                            </td>
                            <td class="im-company-name">Apex Data Corp</td>
                            <td>
                                <span class="im-industry-badge">Software Development</span>
                            </td>
                            <td>
                                <div class="im-skills-stack">
                                    <span class="im-skill-tag">Python</span>
                                    <span class="im-skill-tag">PowerBI</span>
                                </div>
                            </td>
                            <td class="im-applications-count">38</td>
                            <td class="im-deadline-date">05 Sep 2026</td>
                            <td class="im-status-cell">
                                <span class="im-status-badge status-suspended">
                                    <span class="material-symbols-outlined" style="font-size: 13px;">block</span> Suspended
                                </span>
                            </td>
                            <td class="im-actions-cell">
                                <button type="button" class="im-btn-action" title="View Details" onclick="viewInternshipDetails('#INT006')">
                                    <span class="material-symbols-outlined">visibility</span>
                                </button>
                                <button type="button" class="im-btn-action im-btn-action-reactivate" title="Reactivate Post" onclick="reactivateInternship('#INT006')">
                                    <span class="material-symbols-outlined">check_circle</span>
                                </button>
                            </td>
                        </tr>

                    </tbody>
                </table>
            </div>

            <!-- Pagination Bar -->
            <div class="im-pagination-bar">
                <div class="im-pagination-info" id="imPaginationInfo">
                    Showing 1 to 10 of 432 entries
                </div>
                <div class="im-pagination-controls">
                    <button type="button" class="im-page-btn" id="imPagePrev">Previous</button>
                    <button type="button" class="im-page-btn im-page-number active">1</button>
                    <button type="button" class="im-page-btn im-page-number">2</button>
                    <button type="button" class="im-page-btn im-page-number">3</button>
                    <span class="im-page-ellipsis">..</span>
                    <button type="button" class="im-page-btn im-page-number">44</button>
                    <button type="button" class="im-page-btn" id="imPageNext">Next</button>
                </div>
            </div>

        </div>

    </div>
</main>

<!-- ==========================================================================
     6. Add Internship Modal
     ========================================================================== -->
<div class="im-modal-backdrop" id="imAddModal">
    <div class="im-modal-dialog">
        <div class="im-modal-header">
            <h3>Add New Internship Opportunity</h3>
            <button type="button" class="im-modal-close" onclick="closeAddInternshipModal()">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <form id="imAddInternshipForm">
            <div class="im-modal-body">
                <div class="im-form-group">
                    <label for="imInputPosition">Position Title *</label>
                    <input type="text" id="imInputPosition" class="im-form-control" placeholder="e.g. Full-Stack Developer Intern" required>
                </div>
                <div class="im-form-row">
                    <div class="im-form-group">
                        <label for="imInputCompany">Company Name *</label>
                        <input type="text" id="imInputCompany" class="im-form-control" placeholder="e.g. Sysco Labs" required>
                    </div>
                    <div class="im-form-group">
                        <label for="imInputIndustry">Industry Category *</label>
                        <input type="text" id="imInputIndustry" class="im-form-control" placeholder="e.g. Software Development" required>
                    </div>
                </div>
                <div class="im-form-row">
                    <div class="im-form-group">
                        <label for="imInputMode">Work Type & Location</label>
                        <input type="text" id="imInputMode" class="im-form-control" placeholder="e.g. Full-time • Remote">
                    </div>
                    <div class="im-form-group">
                        <label for="imInputDeadline">Application Deadline</label>
                        <input type="text" id="imInputDeadline" class="im-form-control" placeholder="e.g. 30 Oct 2026">
                    </div>
                </div>
                <div class="im-form-group">
                    <label for="imInputSkills">Required Skills (Comma separated)</label>
                    <input type="text" id="imInputSkills" class="im-form-control" placeholder="e.g. React, Node.js, SQL">
                </div>
            </div>
            <div class="im-modal-footer">
                <button type="button" class="im-btn-secondary" onclick="closeAddInternshipModal()">Cancel</button>
                <button type="submit" class="im-btn-primary">Add Internship</button>
            </div>
        </form>
    </div>
</div>

<!-- ==========================================================================
     7. Internship Details Modal
     ========================================================================== -->
<div class="im-modal-backdrop" id="imDetailsModal">
    <div class="im-modal-dialog">
        <div class="im-modal-header">
            <div>
                <h3 id="imDetailTitle" style="margin: 0 0 2px;">Internship Details</h3>
                <span style="font-size: 12.5px; color: #64748b;">Post overview and administrative moderation</span>
            </div>
            <button type="button" class="im-modal-close" onclick="closeInternshipDetailsModal()">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <div class="im-modal-body">
            <!-- ID, Status Badge & Industry Header -->
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px; flex-wrap: wrap; gap: 8px;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <span style="font-weight: 800; color: #091a30; font-size: 16px;" id="imDetailId">#INT001</span>
                    <span id="imDetailStatusBadge">
                        <!-- Populated by JS -->
                    </span>
                </div>
                <span class="im-industry-badge" id="imDetailIndustry">Software Development</span>
            </div>

            <!-- Details Metadata Card -->
            <div class="im-details-info-card">
                <div class="im-details-info-row">
                    <span class="im-details-label">Company:</span>
                    <span class="im-details-val" id="imDetailCompany">Tech Solutions</span>
                </div>
                <div class="im-details-info-row">
                    <span class="im-details-label">Work Type & Mode:</span>
                    <span class="im-details-val" id="imDetailDuration">Full-time • Remote</span>
                </div>
                <div class="im-details-info-row">
                    <span class="im-details-label">Required Skills:</span>
                    <span class="im-details-val" id="imDetailSkills">Java, React, SQL</span>
                </div>
                <div class="im-details-info-row">
                    <span class="im-details-label">Applications Received:</span>
                    <span class="im-details-val highlight" id="imDetailApps">120</span>
                </div>
                <div class="im-details-info-row">
                    <span class="im-details-label">Application Deadline:</span>
                    <span class="im-details-val" id="imDetailDeadline">30 Aug 2026</span>
                </div>
            </div>

            <!-- Suspended Banner (Visible if currently suspended) -->
            <div id="imDetailSuspendedBox" class="im-suspended-banner" style="display: none;">
                <div class="im-suspended-banner-header">
                    <span class="material-symbols-outlined">report</span>
                    <strong>Currently Suspended Listing</strong>
                </div>
                <p class="im-suspended-banner-text">
                    This post is deactivated and hidden from students due to reported violations or complaints.
                </p>
                <div class="im-suspended-reason-line">
                    <strong>Recorded Reason:</strong> <span id="imDetailSuspensionReason">-</span>
                </div>
                <div class="im-suspended-notif-line">
                    <span class="material-symbols-outlined" style="font-size: 15px;">mark_email_read</span>
                    <span>An official suspension notice has been dispatched to the company.</span>
                </div>
            </div>

            <!-- Admin Moderation & Control Section -->
            <div class="im-moderation-box">
                <div class="im-moderation-info">
                    <label class="im-moderation-title">Admin Moderation & Policy Action</label>
                    <p class="im-moderation-desc" id="imModerationDesc">
                        If this listing breaches platform regulations or student complaints are verified, suspend it and send an official explanation to the company.
                    </p>
                </div>
                <div id="imModerationActionContainer">
                    <!-- Dynamically populated by JS: Suspend or Reactivate button -->
                </div>
            </div>
        </div>
        <div class="im-modal-footer">
            <button type="button" class="im-btn-secondary" onclick="closeInternshipDetailsModal()">Close</button>
        </div>
    </div>
</div>

<!-- ==========================================================================
     8. Suspend Internship & Notify Company Modal
     ========================================================================== -->
<div class="im-modal-backdrop" id="imSuspendModal">
    <div class="im-modal-dialog" style="max-width: 560px;">
        <div class="im-modal-header im-suspend-header">
            <div class="im-suspend-header-title">
                <div class="im-suspend-header-icon">
                    <span class="material-symbols-outlined">warning</span>
                </div>
                <div>
                    <h3 style="color: #991b1b; margin: 0; font-size: 17px; font-weight: 700;">Suspend Internship Listing</h3>
                    <span style="font-size: 12.5px; color: #b91c1c;">Deactivate post and dispatch formal notification to employer</span>
                </div>
            </div>
            <button type="button" class="im-modal-close" onclick="closeSuspendModal()">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>

        <form id="imSuspendForm">
            <input type="hidden" id="imSuspendTargetId" value="">
            
            <div class="im-modal-body">
                
                <!-- Target Posting Summary Callout -->
                <div class="im-suspend-target-card">
                    <div class="im-suspend-target-row">
                        <span class="im-suspend-target-label">Target Internship:</span>
                        <strong class="im-suspend-target-value" id="imSuspendTargetPosition">Software Engineer Intern</strong>
                        <span class="im-suspend-target-pill" id="imSuspendTargetIdBadge">#INT001</span>
                    </div>
                    <div class="im-suspend-target-row">
                        <span class="im-suspend-target-label">Target Employer:</span>
                        <strong class="im-suspend-target-value" id="imSuspendTargetCompany">Tech Solutions</strong>
                    </div>
                    <div class="im-suspend-notice-alert">
                        <span class="material-symbols-outlined">info</span>
                        <span>This listing will be hidden immediately from applicants. The reason and message below will be delivered as a formal notification to the company.</span>
                    </div>
                </div>

                <!-- Reason Category Selection -->
                <div class="im-form-group">
                    <label for="imSuspendCategory">Violation / Suspension Category *</label>
                    <select id="imSuspendCategory" class="im-form-control" required>
                        <option value="Platform Policy Violation" selected>Platform Policy Violation</option>
                        <option value="Student Complaint Received">Student / Applicant Complaint Received</option>
                        <option value="Misleading Job Description or Stipend">Misleading Job Description or Stipend Details</option>
                        <option value="Unresponsive Employer / Inactive Post">Unresponsive Employer / Inactive Post</option>
                        <option value="Discriminatory or Inappropriate Requirements">Discriminatory or Inappropriate Requirements</option>
                        <option value="Other Policy Breach">Other Policy Breach</option>
                    </select>
                </div>

                <!-- Specific Reason / Internal Note -->
                <div class="im-form-group">
                    <label for="imSuspendReason">Specific Reason / Complaint Summary *</label>
                    <input type="text" id="imSuspendReason" class="im-form-control" placeholder="e.g. Complaint regarding undisclosed unpaid overtime exceeding policy limit..." required>
                </div>

                <!-- Notification Message to Company -->
                <div class="im-form-group">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 2px;">
                        <label for="imCompanyMessage">Notification Message to Company (Sent to Employer) *</label>
                        <span class="im-notif-badge">
                            <span class="material-symbols-outlined" style="font-size: 13px;">mail</span>
                            Direct Notification
                        </span>
                    </div>
                    <textarea id="imCompanyMessage" class="im-form-control" rows="4" style="height: 85px; font-family: 'Inter', sans-serif; font-size: 13px; line-height: 1.45; resize: vertical;" required placeholder="Write message explaining the suspension reason to the company..."></textarea>
                    <small style="font-size: 11.5px; color: #64748b; margin-top: 3px;">
                        The employer will receive this in their notification feed and can submit clarification or contact admin to resolve the issue.
                    </small>
                </div>

            </div>

            <div class="im-modal-footer">
                <button type="button" class="im-btn-secondary" onclick="closeSuspendModal()">Cancel</button>
                <button type="submit" class="im-btn-suspend-submit">
                    <span class="material-symbols-outlined" style="font-size: 18px;">notifications_active</span>
                    <span>Confirm Suspension & Notify Company</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ==========================================================================
     8. Toast Notification Alert
     ========================================================================== -->
<div class="im-toast" id="imToast">
    <span class="material-symbols-outlined">check_circle</span>
    <span id="imToastMessage">Operation successful!</span>
</div>

<!-- Page Footer -->
<footer class="footer">
    <div>&copy; 2026 SkillBridge. All rights reserved.</div>
    <div class="footer-links">
        <a href="#">Help Center</a>
        <a href="#">Privacy Policy</a>
        <a href="#">Terms of Service</a>
    </div>
</footer>

<!-- Internship Management Dedicated Script -->
<script src="../../../Assets/JS/Admin/internship_management.js?v=<?php echo time(); ?>"></script>

<?php include "../../../Includes/dash_footer.php"; ?>