<?php
/**
 * ==============================================================================
 * SkillBridge - Project Management Dashboard (Admin)
 * Displays project KPI metrics, search, filterable projects table,
 * team member information, status indicators, and project addition modal.
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

<!-- Project Management Custom Stylesheet -->
<link rel="stylesheet" href="../../../Assets/CSS/Admin/project_management.css?v=<?php echo time(); ?>">

<?php
include "../../../Includes/dash_header.php";
?>

<main class="content">
    <div class="pm-container">

        <!-- ==================================================================
             1. Page Header (Title & Subtitle)
             ================================================================== -->
        <div class="pm-header">
            <h1>Project Management</h1>
            <p>Monitor, review, and manage all student projects across the SkillBridge ecosystem.</p>
        </div>

        <!-- ==================================================================
             2. KPI Statistics Cards (Row of 4 Cards)
             ================================================================== -->
        <div class="pm-stats-grid">
            
            <!-- Card 1: Total Projects -->
            <div class="pm-stat-card">
                <div class="pm-stat-card-top">
                    <div class="pm-stat-icon icon-navy">
                        <span class="material-symbols-outlined">folder_open</span>
                    </div>
                    <span class="pm-stat-trend trend-green">
                        <span class="material-symbols-outlined" style="font-size:14px;">trending_up</span> 12%
                    </span>
                </div>
                <div class="pm-stat-details">
                    <div class="pm-stat-label">Total Projects</div>
                    <div class="pm-stat-value">2,105</div>
                </div>
            </div>

            <!-- Card 2: Active Projects -->
            <div class="pm-stat-card">
                <div class="pm-stat-card-top">
                    <div class="pm-stat-icon icon-orange">
                        <span class="material-symbols-outlined">bolt</span>
                    </div>
                    <span class="pm-stat-trend trend-green">
                        <span class="material-symbols-outlined" style="font-size:14px;">trending_up</span> 8%
                    </span>
                </div>
                <div class="pm-stat-details">
                    <div class="pm-stat-label">Active Projects</div>
                    <div class="pm-stat-value">1,540</div>
                </div>
            </div>

            <!-- Card 3: Under Review -->
            <div class="pm-stat-card">
                <div class="pm-stat-card-top">
                    <div class="pm-stat-icon icon-pink">
                        <span class="material-symbols-outlined">rate_review</span>
                    </div>
                    <span class="pm-stat-trend badge-new">
                        ! Review
                    </span>
                </div>
                <div class="pm-stat-details">
                    <div class="pm-stat-label">Under Review</div>
                    <div class="pm-stat-value">86</div>
                </div>
            </div>

            <!-- Card 4: Closed Projects -->
            <div class="pm-stat-card">
                <div class="pm-stat-card-top">
                    <div class="pm-stat-icon icon-blue">
                        <span class="material-symbols-outlined">cancel</span>
                    </div>
                    <span class="pm-stat-trend trend-blue">
                        <span class="material-symbols-outlined" style="font-size:14px;">trending_up</span> 24%
                    </span>
                </div>
                <div class="pm-stat-details">
                    <div class="pm-stat-label">Closed Projects</div>
                    <div class="pm-stat-value">479</div>
                </div>
            </div>

        </div>

        <!-- ==================================================================
             3. Filter Toolbar Card
             ================================================================== -->
        <div class="pm-toolbar-card">
            <div class="pm-toolbar-row">
                <div class="pm-filters-left">
                    
                    <!-- Search Input -->
                    <div class="pm-search-wrap">
                        <span class="material-symbols-outlined">search</span>
                        <input type="text" id="pmSearchInput" class="pm-search-input" placeholder="Search projects...">
                    </div>

                    <!-- Project Status Filter (Active, Close, Review, Rejected) -->
                    <div class="pm-select-wrap">
                        <select id="pmStatusFilter" class="pm-select" aria-label="Filter by Project Status">
                            <option value="all">Project Status</option>
                            <option value="active">Active</option>
                            <option value="close">Close</option>
                            <option value="review">Review</option>
                            <option value="rejected">Rejected</option>
                        </select>
                        <span class="material-symbols-outlined select-chevron">expand_more</span>
                    </div>

                    <!-- Project Type Filter -->
                    <div class="pm-select-wrap">
                        <select id="pmTypeFilter" class="pm-select" aria-label="Filter by Project Type">
                            <option value="all">Project Type</option>
                            <option value="education">Education Technology</option>
                            <option value="health">HealthTech</option>
                            <option value="fintech">FinTech</option>
                            <option value="logistics">Logistics & AI</option>
                            <option value="iot">IoT & Sensors</option>
                            <option value="cybersecurity">Cybersecurity</option>
                        </select>
                        <span class="material-symbols-outlined select-chevron">expand_more</span>
                    </div>

                </div>
            </div>
        </div>

        <!-- ==================================================================
             4. Projects Data Table Card
             ================================================================== -->
        <div class="pm-table-card">
            <div class="pm-table-responsive">
                <table class="pm-table">
                    <thead>
                        <tr>
                            <th>PROJECT ID</th>
                            <th>PROJECT TITLE</th>
                            <th>ORGANIZATION</th>
                            <th>REQUIRED SKILLS</th>
                            <th>TEAM MEMBERS</th>
                            <th>STATUS</th>
                            <th>CREATED DATE</th>
                            <th class="pm-actions-cell">ACTIONS</th>
                        </tr>
                    </thead>
                    <tbody id="pmTableBody">
                        
                        <!-- Row 1: AI-Based Learning Platform -->
                        <tr class="pm-data-row" 
                            data-id="#PR001" 
                            data-title="AI-Based Learning Platform" 
                            data-category="Education Technology" 
                            data-org="UCSC" 
                            data-status="Active" 
                            data-skills="Python ML React">
                            <td class="pm-project-id">#PR001</td>
                            <td>
                                <div class="pm-project-meta">
                                    <span class="pm-title-main">AI-Based Learning Platform</span>
                                    <span class="pm-title-category">Education Technology</span>
                                </div>
                            </td>
                            <td>
                                <div class="pm-org-wrapper">
                                    <span class="pm-org-avatar org-ucsc">U</span>
                                    <span class="pm-org-name">UCSC</span>
                                </div>
                            </td>
                            <td>
                                <div class="pm-skills-list">
                                    <span class="pm-skill-pill">Python</span>
                                    <span class="pm-skill-pill">ML</span>
                                    <span class="pm-skill-pill">React</span>
                                </div>
                            </td>
                            <td>
                                <div class="pm-team-count">
                                    <span class="material-symbols-outlined">group</span>
                                    <span>4 Members</span>
                                </div>
                            </td>
                            <td>
                                <span class="pm-status-pill status-active">Active</span>
                            </td>
                            <td class="pm-created-date">12 June 2026</td>
                            <td class="pm-actions-cell">
                                <button class="pm-btn-icon" title="View Details" onclick="viewProjectDetails('#PR001')">
                                    <span class="material-symbols-outlined">visibility</span>
                                </button>
                                <button class="pm-btn-icon pm-btn-icon-reject" title="Reject Project" onclick="openRejectReasonModal('#PR001')">
                                    <span class="material-symbols-outlined">block</span>
                                </button>
                            </td>
                        </tr>

                        <!-- Row 2: Healthcare Management System -->
                        <tr class="pm-data-row" 
                            data-id="#PR002" 
                            data-title="Healthcare Management System" 
                            data-category="HealthTech" 
                            data-org="ABC Institute" 
                            data-status="Review" 
                            data-skills="PHP MySQL">
                            <td class="pm-project-id">#PR002</td>
                            <td>
                                <div class="pm-project-meta">
                                    <span class="pm-title-main">Healthcare Management System</span>
                                    <span class="pm-title-category">HealthTech</span>
                                </div>
                            </td>
                            <td>
                                <div class="pm-org-wrapper">
                                    <span class="pm-org-avatar org-abc">A</span>
                                    <span class="pm-org-name">ABC Institute</span>
                                </div>
                            </td>
                            <td>
                                <div class="pm-skills-list">
                                    <span class="pm-skill-pill">PHP</span>
                                    <span class="pm-skill-pill">MySQL</span>
                                </div>
                            </td>
                            <td>
                                <div class="pm-team-count">
                                    <span class="material-symbols-outlined">group</span>
                                    <span>5 Members</span>
                                </div>
                            </td>
                            <td>
                                <span class="pm-status-pill status-review">Review</span>
                            </td>
                            <td class="pm-created-date">18 June 2026</td>
                            <td class="pm-actions-cell">
                                <button class="pm-btn-icon" title="View Details" onclick="viewProjectDetails('#PR002')">
                                    <span class="material-symbols-outlined">visibility</span>
                                </button>
                                <button class="pm-btn-icon pm-btn-icon-reject" title="Reject Project" onclick="openRejectReasonModal('#PR002')">
                                    <span class="material-symbols-outlined">block</span>
                                </button>
                            </td>
                        </tr>

                        <!-- Row 3: Mobile Banking Application -->
                        <tr class="pm-data-row" 
                            data-id="#PR003" 
                            data-title="Mobile Banking Application" 
                            data-category="FinTech" 
                            data-org="Tech Solutions" 
                            data-status="Close" 
                            data-skills="Flutter Firebase">
                            <td class="pm-project-id">#PR003</td>
                            <td>
                                <div class="pm-project-meta">
                                    <span class="pm-title-main">Mobile Banking Application</span>
                                    <span class="pm-title-category">FinTech</span>
                                </div>
                            </td>
                            <td>
                                <div class="pm-org-wrapper">
                                    <span class="pm-org-avatar org-tech">T</span>
                                    <span class="pm-org-name">Tech Solutions</span>
                                </div>
                            </td>
                            <td>
                                <div class="pm-skills-list">
                                    <span class="pm-skill-pill">Flutter</span>
                                    <span class="pm-skill-pill">Firebase</span>
                                </div>
                            </td>
                            <td>
                                <div class="pm-team-count">
                                    <span class="material-symbols-outlined">group</span>
                                    <span>3 Members</span>
                                </div>
                            </td>
                            <td>
                                <span class="pm-status-pill status-close">Close</span>
                            </td>
                            <td class="pm-created-date">25 June 2026</td>
                            <td class="pm-actions-cell">
                                <button class="pm-btn-icon" title="View Details" onclick="viewProjectDetails('#PR003')">
                                    <span class="material-symbols-outlined">visibility</span>
                                </button>
                                <button class="pm-btn-icon pm-btn-icon-reject" title="Reject Project" onclick="openRejectReasonModal('#PR003')">
                                    <span class="material-symbols-outlined">block</span>
                                </button>
                            </td>
                        </tr>

                        <!-- Row 4: Smart Logistics Engine -->
                        <tr class="pm-data-row" 
                            data-id="#PR004" 
                            data-title="Smart Logistics Engine" 
                            data-category="Logistics & AI" 
                            data-org="ABC Institute" 
                            data-status="Active" 
                            data-skills="Java SpringBoot Docker">
                            <td class="pm-project-id">#PR004</td>
                            <td>
                                <div class="pm-project-meta">
                                    <span class="pm-title-main">Smart Logistics Tracking Engine</span>
                                    <span class="pm-title-category">Logistics & AI</span>
                                </div>
                            </td>
                            <td>
                                <div class="pm-org-wrapper">
                                    <span class="pm-org-avatar org-abc">A</span>
                                    <span class="pm-org-name">ABC Institute</span>
                                </div>
                            </td>
                            <td>
                                <div class="pm-skills-list">
                                    <span class="pm-skill-pill">Java</span>
                                    <span class="pm-skill-pill">SpringBoot</span>
                                    <span class="pm-skill-pill">Docker</span>
                                </div>
                            </td>
                            <td>
                                <div class="pm-team-count">
                                    <span class="material-symbols-outlined">group</span>
                                    <span>5 Members</span>
                                </div>
                            </td>
                            <td>
                                <span class="pm-status-pill status-active">Active</span>
                            </td>
                            <td class="pm-created-date">02 July 2026</td>
                            <td class="pm-actions-cell">
                                <button class="pm-btn-icon" title="View Details" onclick="viewProjectDetails('#PR004')">
                                    <span class="material-symbols-outlined">visibility</span>
                                </button>
                                <button class="pm-btn-icon pm-btn-icon-reject" title="Reject Project" onclick="openRejectReasonModal('#PR004')">
                                    <span class="material-symbols-outlined">block</span>
                                </button>
                            </td>
                        </tr>

                        <!-- Row 5: IoT Soil & Crop Quality Monitor -->
                        <tr class="pm-data-row" 
                            data-id="#PR005" 
                            data-title="IoT Soil & Crop Quality Monitor" 
                            data-category="IoT & Sensors" 
                            data-org="SLIIT" 
                            data-status="Rejected" 
                            data-skills="Python Arduino MQTT">
                            <td class="pm-project-id">#PR005</td>
                            <td>
                                <div class="pm-project-meta">
                                    <span class="pm-title-main">IoT Soil & Crop Quality Monitor</span>
                                    <span class="pm-title-category">IoT & Sensors</span>
                                </div>
                            </td>
                            <td>
                                <div class="pm-org-wrapper">
                                    <span class="pm-org-avatar org-sliit">S</span>
                                    <span class="pm-org-name">SLIIT</span>
                                </div>
                            </td>
                            <td>
                                <div class="pm-skills-list">
                                    <span class="pm-skill-pill">Python</span>
                                    <span class="pm-skill-pill">Arduino</span>
                                    <span class="pm-skill-pill">MQTT</span>
                                </div>
                            </td>
                            <td>
                                <div class="pm-team-count">
                                    <span class="material-symbols-outlined">group</span>
                                    <span>3 Members</span>
                                </div>
                            </td>
                            <td>
                                <span class="pm-status-pill status-rejected">Rejected</span>
                            </td>
                            <td class="pm-created-date">10 July 2026</td>
                            <td class="pm-actions-cell">
                                <button class="pm-btn-icon" title="View Details" onclick="viewProjectDetails('#PR005')">
                                    <span class="material-symbols-outlined">visibility</span>
                                </button>
                                <button class="pm-btn-icon pm-btn-icon-reject" title="Already Rejected" disabled>
                                    <span class="material-symbols-outlined">block</span>
                                </button>
                            </td>
                        </tr>

                    </tbody>
                </table>
            </div>

            <!-- ==============================================================
                 5. Pagination & Table Footer
                 ============================================================== -->
            <div class="pm-pagination-bar">
                <div class="pm-pagination-info" id="pmPaginationInfo">
                    Showing 1 to 3 of 2,105 results
                </div>
                <div class="pm-pagination-controls">
                    <button type="button" class="pm-page-btn" id="pmPagePrev">Previous</button>
                    <button type="button" class="pm-page-btn pm-page-number active">1</button>
                    <button type="button" class="pm-page-btn pm-page-number">2</button>
                    <button type="button" class="pm-page-btn pm-page-number">3</button>
                    <span class="pm-page-ellipsis">...</span>
                    <button type="button" class="pm-page-btn pm-page-number">24</button>
                    <button type="button" class="pm-page-btn" id="pmPageNext">Next</button>
                </div>
            </div>

        </div>

    </div>
</main>

<!-- ==========================================================================
     6. Add Project Modal
     ========================================================================== -->
<div class="pm-modal-backdrop" id="pmAddModal">
    <div class="pm-modal-dialog">
        <div class="pm-modal-header">
            <h3>Add New Student Project</h3>
            <button type="button" class="pm-modal-close" onclick="closeAddProjectModal()">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <form id="pmAddProjectForm">
            <div class="pm-modal-body">
                <div class="pm-form-group">
                    <label for="pmInputTitle">Project Title *</label>
                    <input type="text" id="pmInputTitle" class="pm-form-control" placeholder="e.g. AI-Powered Smart Agriculture" required>
                </div>
                <div class="pm-form-row">
                    <div class="pm-form-group">
                        <label for="pmInputCategory">Project Category / Type *</label>
                        <input type="text" id="pmInputCategory" class="pm-form-control" placeholder="e.g. Education Technology" required>
                    </div>
                    <div class="pm-form-group">
                        <label for="pmInputOrg">Organization / University *</label>
                        <input type="text" id="pmInputOrg" class="pm-form-control" placeholder="e.g. UCSC" required>
                    </div>
                </div>
                <div class="pm-form-row">
                    <div class="pm-form-group">
                        <label for="pmInputSkills">Required Skills (Comma separated)</label>
                        <input type="text" id="pmInputSkills" class="pm-form-control" placeholder="e.g. Python, ML, React">
                    </div>
                    <div class="pm-form-group">
                        <label for="pmInputStatus">Project Status</label>
                        <select id="pmInputStatus" class="pm-form-control">
                            <option value="Active" selected>Active</option>
                            <option value="Review">Review</option>
                            <option value="Close">Close</option>
                            <option value="Rejected">Rejected</option>
                        </select>
                    </div>
                </div>
                <div class="pm-form-group">
                    <label for="pmInputDesc">Project Overview / Description</label>
                    <textarea id="pmInputDesc" class="pm-form-control" placeholder="Brief summary of project objectives and scope..."></textarea>
                </div>
            </div>
            <div class="pm-modal-footer">
                <button type="button" class="pm-btn-secondary" onclick="closeAddProjectModal()">Cancel</button>
                <button type="submit" class="pm-btn-primary">Add Project</button>
            </div>
        </form>
    </div>
</div>

<!-- ==========================================================================
     7. Project Details Modal
     ========================================================================== -->
<div class="pm-modal-backdrop" id="pmDetailsModal">
    <div class="pm-modal-dialog">
        <div class="pm-modal-header">
            <h3 id="pmDetailTitle">Project Overview</h3>
            <button type="button" class="pm-modal-close" onclick="closeProjectDetailsModal()">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <div class="pm-modal-body">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
                <span style="font-weight: 700; color: #0b2246; font-size: 16px;" id="pmDetailId">#PR001</span>
                <span class="pm-status-pill status-active" id="pmDetailStatus">Active</span>
            </div>
            <div style="background: #f8fafc; border-radius: 12px; padding: 14px 16px; display: flex; flex-direction: column; gap: 8px;">
                <div style="font-size: 13px; color: #64748b;">
                    <strong>Category:</strong> <span id="pmDetailCategory">Technology</span>
                </div>
                <div style="font-size: 13px; color: #64748b;">
                    <strong>Organization:</strong> <span id="pmDetailOrg">UCSC</span>
                </div>
                <div style="font-size: 13px; color: #64748b;">
                    <strong>Required Skills:</strong> <span id="pmDetailSkills">Python, ML</span>
                </div>
            </div>
            <div style="margin-top: 16px; padding-top: 14px; border-top: 1px solid #f1f5f9; display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap;">
                <div>
                    <label style="font-size: 13px; font-weight: 700; color: #1e293b; display: block; margin-bottom: 2px;">Admin Action</label>
                    <span style="font-size: 12px; color: #64748b;">Reject this project if it violates guidelines or platform policies.</span>
                </div>
                <button type="button" id="pmBtnRejectProject" class="pm-btn-reject" onclick="openRejectReasonModal()">
                    <span class="material-symbols-outlined" style="font-size: 18px;">block</span>
                    <span>Reject</span>
                </button>
            </div>
        </div>
        <div class="pm-modal-footer">
            <button type="button" class="pm-btn-secondary" onclick="closeProjectDetailsModal()">Close</button>
        </div>
    </div>
</div>

<!-- ==========================================================================
     8. Reject Project Reason Modal
     ========================================================================== -->
<div class="pm-modal-backdrop" id="pmRejectModal">
    <div class="pm-modal-dialog" style="max-width: 500px;">
        <div class="pm-modal-header" style="border-bottom: 1px solid #fee2e2; background-color: #fff5f5;">
            <div style="display: flex; align-items: center; gap: 8px;">
                <span class="material-symbols-outlined" style="color: #dc2626; font-size: 22px;">report_problem</span>
                <h3 style="color: #991b1b; margin: 0; font-size: 17px; font-weight: 700;">Reject Project</h3>
            </div>
            <button type="button" class="pm-modal-close" onclick="closeRejectReasonModal()">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <form id="pmRejectProjectForm">
            <div class="pm-modal-body">
                <p style="font-size: 13.5px; color: #475569; margin: 0 0 14px; line-height: 1.5;">
                    Please state the reason for rejecting <strong id="pmRejectProjectTitle" style="color: #0f172a;">this project</strong> (<span id="pmRejectProjectId" style="font-weight: 700;">#PR001</span>). This reason will be recorded and notified.
                </p>
                <div class="pm-form-group">
                    <label for="pmRejectReason" style="font-weight: 600; font-size: 13px; color: #1e293b;">
                        Rejection Reason *
                    </label>
                    <textarea id="pmRejectReason" class="pm-form-control" rows="4" style="height: 100px; resize: vertical;" placeholder="Enter specific reasons (e.g. Inadequate documentation, non-compliant technology stack, unauthorized duplicate submission)..." required></textarea>
                </div>
            </div>
            <div class="pm-modal-footer">
                <button type="button" class="pm-btn-secondary" onclick="closeRejectReasonModal()">Cancel</button>
                <button type="submit" class="pm-btn-danger">Confirm Rejection</button>
            </div>
        </form>
    </div>
</div>

<!-- ==========================================================================
     8. Toast Notification Alert
     ========================================================================== -->
<div class="pm-toast" id="pmToast">
    <span class="material-symbols-outlined">check_circle</span>
    <span id="pmToastMessage">Operation successful!</span>
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

<!-- Project Management Dedicated Script -->
<script src="../../../Assets/JS/Admin/project_management.js?v=<?php echo time(); ?>"></script>

<?php include "../../../Includes/dash_footer.php"; ?>