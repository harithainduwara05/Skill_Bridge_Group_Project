<!--Initialize Admin Environment: Auth Guard, 
Backend Controller & Sidebar-->
<?php
$backendPage = "Internship_Management.php";
require_once __DIR__ . "/Components/AdminInit.php";
?>
<!--End Here -->
<!-- Internship Management Custom Stylesheet -->
<link rel="stylesheet" href="../../Assets/CSS/Admin/internship_management.css?v=<?php echo time(); ?>">

<?php
include "../../Includes/dash_header.php";
?>

<main class="content">
    <div class="im-container">

        <!--  Page Header (Title, Subtitle & Add Action)-->
        <div class="im-header-row">
            <div class="im-header-info">
                <h1>Internship Management</h1>
                <p>Monitor, review, and manage internship opportunities provided by companies across all sectors.</p>
            </div>
        </div>

        <!--  InternshipManagement cars-->
        <div class="im-stats-grid">

            <?php
                $CardDetails = [
                    "cardIcons"  => ["work", "check_circle", "pause_circle", "group"],
                    "iconColors" => ["navy", "green", "orange", "blue"],
                    "cardLabels" => ["Total Internships", "Active Internships", "Suspended Posts", "Total Applications"],
                    "cardValues" => ["432", "286", "14", "2,850"],
                    "valueIds"   => ["", "", "kpiSuspendedCount", "kpiTotalApps"]
                ];
                include __DIR__ . "/Components/Card.php";
            ?>

        </div>

        <!-- Unified Internships Data Table Card (Toolbar + Table + Pagination) -->
        <div class="im-table-card">
            
            <!-- Filter & Search Toolbar -->
            <div class="im-toolbar-card">
                <div class="im-toolbar-controls">
                    
                    <!-- Search Box -->
                    <div class="im-search-wrap">
                        <span class="material-symbols-outlined">search</span>
                        <input type="text" id="imSearchInput" class="im-search-input" placeholder="Search internships by title, ID, or company...">
                    </div>

                    <!-- Status Filter (4 Statuses: Active, Suspended, Terminated, Closed) -->
                    <div class="im-select-wrap">
                        <span class="material-symbols-outlined im-select-icon">tune</span>
                        <select id="imStatusFilter" class="im-select" aria-label="Filter by Status">
                            <option value="all" selected>Status: All</option>
                            <option value="active">Active</option>
                            <option value="suspended">Suspended</option>
                            <option value="terminated">Terminated</option>
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

            <!-- Internships Data Table -->
            <?php require_once 'Components/InternshipTable.php'?>
            <!-- Reusable Pagination Component -->
            <?php 
                $totalRecords   = 432;
                $perPage        = 10;
                $currentPageNum = (int)($_GET['page'] ?? 1);
                $entityLabel    = 'entries';
                include __DIR__ . '/Components/Pagination.php';
            ?>

        </div>

    </div>
</main>

<!--  Add Internship Modal -->
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

<!--  Internship Details Modal -->
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
                    <span class="material-symbols-outlined">pause_circle</span>
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

            <!-- Terminated Banner (Visible if permanently terminated) -->
            <div id="imDetailTerminatedBox" class="im-terminated-banner" style="display: none;">
                <div class="im-terminated-banner-header">
                    <span class="material-symbols-outlined">cancel</span>
                    <strong>Permanently Terminated Listing</strong>
                </div>
                <p class="im-terminated-banner-text">
                    This opportunity was permanently terminated due to severe policy violations or refusal to provide acceptable remediation.
                </p>
                <div class="im-terminated-reason-line">
                    <strong>Recorded Grounds:</strong> <span id="imDetailTerminationReason">-</span>
                </div>
                <div class="im-terminated-lock-line">
                    <span class="material-symbols-outlined" style="font-size: 15px;">lock</span>
                    <span>Platform Policy: Once permanently terminated, this post CANNOT be reopened, edited, or reactivated.</span>
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

<!--  Suspend Internship & Notify Company Modal -->
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

<!--  Review Suspended Internship & Resolution Modal -->
<div class="im-modal-backdrop" id="imReviewSuspensionModal">
    <div class="im-modal-dialog" style="max-width: 600px;">
        <div class="im-modal-header" style="border-bottom: 1px solid #fde68a; background-color: #fffbeb;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <div style="width: 38px; height: 38px; border-radius: 10px; background-color: #fef3c7; color: #d97706; display: flex; align-items: center; justify-content: center;">
                    <span class="material-symbols-outlined" style="font-size: 22px;">rate_review</span>
                </div>
                <div>
                    <h3 style="color: #92400e; margin: 0; font-size: 17px; font-weight: 700;">Review Suspension & Resolution</h3>
                    <span style="font-size: 12px; color: #b45309;">Assess company's remedial actions to decide between Reactivation or Termination</span>
                </div>
            </div>
            <button type="button" class="im-modal-close" onclick="closeReviewSuspensionModal()">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>

        <div class="im-modal-body" style="gap: 16px; display: flex; flex-direction: column;">
            <input type="hidden" id="imRevTargetId" value="">

            <!-- Target Posting Header -->
            <div style="display: flex; align-items: center; justify-content: space-between; background: #ffffff; border: 1px solid #e2e8f0; padding: 12px 16px; border-radius: 12px;">
                <div>
                    <span id="imRevId" style="font-weight: 800; color: #091a30; font-size: 15px;">#INT006</span>
                    <h4 id="imRevPosition" style="margin: 2px 0 0; font-size: 14px; font-weight: 600; color: #1e293b;">Data Analytics Intern</h4>
                    <span id="imRevCompany" style="font-size: 12px; color: #64748b;">Apex Data Corp</span>
                </div>
                <span class="im-status-badge status-suspended" id="imRevStatusBadge">
                    <span class="material-symbols-outlined" style="font-size: 13px;">pause_circle</span> Suspended
                </span>
            </div>

            <!-- Two Comparison Cards -->
            <div style="display: flex; flex-direction: column; gap: 12px;">
                <!-- 1. Suspension Reason (Admin's Issue) -->
                <div style="background-color: #fffbeb; border: 1px solid #fde68a; border-radius: 12px; padding: 14px 16px;">
                    <div style="display: flex; align-items: center; gap: 6px; margin-bottom: 6px; color: #92400e; font-weight: 700; font-size: 13px;">
                        <span class="material-symbols-outlined" style="font-size: 18px; color: #d97706;">warning</span>
                        <span>Reason for Suspension (Recorded Policy Violation / Complaint)</span>
                    </div>
                    <p id="imRevReasonText" style="margin: 0; font-size: 13px; color: #78350f; line-height: 1.5;">
                        Reported for deceptive stipend information and policy non-compliance.
                    </p>
                </div>

                <!-- 2. Company's Submitted Action / Remedial Response -->
                <div id="imRevActionCard" style="background-color: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 12px; padding: 14px 16px;">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 6px;">
                        <div style="display: flex; align-items: center; gap: 6px; color: #166534; font-weight: 700; font-size: 13px;">
                            <span class="material-symbols-outlined" style="font-size: 18px; color: #16a34a;">fact_check</span>
                            <span>Company's Remedial Action / Resolution Taken</span>
                        </div>
                        <span id="imRevActionBadge" style="font-size: 11px; font-weight: 700; background: #dcfce7; color: #15803d; padding: 2px 8px; border-radius: 6px;">Action Taken</span>
                    </div>
                    <p id="imRevActionText" style="margin: 0; font-size: 13px; color: #14532d; line-height: 1.5;">
                        We have revised the official internship agreement, clearly specified the LKR 50,000 monthly allowance, and agreed to adhere strictly to the platform's fair recruitment guidelines.
                    </p>
                </div>

                <!-- 3. If No Action Submitted Yet (Conditional display) -->
                <div id="imRevNoActionCard" style="display: none; background-color: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 12px; padding: 14px 16px;">
                    <div style="display: flex; align-items: center; gap: 6px; margin-bottom: 4px; color: #64748b; font-weight: 700; font-size: 13px;">
                        <span class="material-symbols-outlined" style="font-size: 18px; color: #94a3b8;">pending</span>
                        <span>No Remedial Action Submitted</span>
                    </div>
                    <p style="margin: 0; font-size: 12.5px; color: #64748b; line-height: 1.45;">
                        The employer has not submitted any response, clarification, or corrective measures yet. You may grant additional time or permanently terminate the listing if the response deadline has elapsed.
                    </p>
                </div>
            </div>

            <!-- Decision Notice -->
            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 10px 14px; font-size: 12px; color: #475569; line-height: 1.45;">
                <strong>Administrative Decision Policy:</strong> If satisfied with the company's corrective steps, choose <strong>Reactivate Internship</strong> to restore student visibility. If the response is unacceptable or absent, choose <strong>Permanently Terminate</strong> (this irreversible action halts the internship forever).
            </div>
        </div>

        <div class="im-modal-footer" style="display: flex; align-items: center; justify-content: space-between; gap: 12px;">
            <button type="button" class="im-btn-secondary" onclick="closeReviewSuspensionModal()">Cancel</button>
            <div style="display: flex; align-items: center; gap: 10px;">
                <!-- Terminate Button (Red) -->
                <button type="button" class="im-btn-danger" id="imBtnExecuteTerminate" onclick="executeTerminationFromReview()" style="display: inline-flex; align-items: center; gap: 6px; height: 38px; padding: 0 16px; background-color: #dc2626; color: #ffffff; border: none; border-radius: 8px; font-weight: 600; font-size: 13px; cursor: pointer;">
                    <span class="material-symbols-outlined" style="font-size: 18px;">cancel</span>
                    <span>Permanently Terminate</span>
                </button>
                <!-- Reactivate Button (Green) -->
                <button type="button" class="im-btn-reactivate" id="imBtnExecuteReactivate" onclick="executeReactivationFromReview()" style="display: inline-flex; align-items: center; gap: 6px; height: 38px; padding: 0 16px; background-color: #10b981; color: #ffffff; border: none; border-radius: 8px; font-weight: 600; font-size: 13px; cursor: pointer;">
                    <span class="material-symbols-outlined" style="font-size: 18px;">check_circle</span>
                    <span>Satisfied - Reactivate</span>
                </button>
            </div>
        </div>
    </div>
</div>

<!--  Toast Notification Alert -->
<div class="im-toast" id="imToast">
    <span class="material-symbols-outlined">check_circle</span>
    <span id="imToastMessage">Operation successful!</span>
</div>

<!-- Page Footer -->
<footer class="footer">
    <div>&copy; 2026 SkillBridge. All rights reserved.</div>
    <div class="footer-links">
        <a href="/Skill_Bridge_Group_Project/help_center.php">Help Center</a>
        <a href="#">Privacy Policy</a>
        <a href="#">Terms of Service</a>
    </div>
</footer>

<!-- Internship Management Dedicated Script -->
<script src="../../Assets/JS/Admin/internship_management.js?v=<?php echo time(); ?>"></script>

<?php include "../../Includes/dash_footer.php"; ?>