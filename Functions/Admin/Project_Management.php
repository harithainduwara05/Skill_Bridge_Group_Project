<?php

require_once "../../Session/Session.php";
require_login();
require_role('admin');
$user = current_user();

require_once __DIR__ . "/../../Backend/Admin/Project_Management.php";

include "../../Includes/admin_sidebar.php";
?>

<!-- Project Management Custom Stylesheet -->
<link rel="stylesheet" href="../../Assets/CSS/Admin/project_management.css?v=<?php echo time(); ?>">

<?php
include "../../Includes/dash_header.php";
?>

<main class="content">
    <div class="pm-container">

        <!--  Page Header (Title & Subtitle)-->
        <div class="pm-header">
            <h1>Project Management</h1>
            <p>Monitor, review, and manage all student projects across the SkillBridge ecosystem.</p>
        </div>

        <!--  KPI Statistics Cards (Row of 4 Cards)-->
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
                    <div class="pm-stat-value"><?php echo $totalProjects  ?></div>
                </div>
            </div>

            <!-- Card 2: Active Projects -->
            <div class="pm-stat-card">
                <div class="pm-stat-card-top">
                    <div class="pm-stat-icon icon-green">
                        <span class="material-symbols-outlined">bolt</span>
                    </div>
                    <span class="pm-stat-trend trend-green">
                        <span class="material-symbols-outlined" style="font-size:14px;">trending_up</span> 8%
                    </span>
                </div>
                <div class="pm-stat-details">
                    <div class="pm-stat-label">Active Projects</div>
                    <div class="pm-stat-value"><?php echo $activeProjects; ?></div>
                </div>
            </div>

            <!-- Card 3: Reviewing -->
            <div class="pm-stat-card">
                <div class="pm-stat-card-top">
                    <div class="pm-stat-icon icon-blue">
                        <span class="material-symbols-outlined">rate_review</span>
                    </div>
                    <span class="pm-stat-trend badge-review">
                        Reviewing
                    </span>
                </div>
                <div class="pm-stat-details">
                    <div class="pm-stat-label">Reviewing</div>
                    <div class="pm-stat-value"><?php echo $reviewingProjects ?></div>
                </div>
            </div>

            <!-- Card 4: Completed Projects -->
            <div class="pm-stat-card">
                <div class="pm-stat-card-top">
                    <div class="pm-stat-icon icon-gray">
                        <span class="material-symbols-outlined">task_alt</span>
                    </div>
                    <span class="pm-stat-trend trend-gray">
                        <span class="material-symbols-outlined" style="font-size:14px;">trending_up</span> 24%
                    </span>
                </div>
                <div class="pm-stat-details">
                    <div class="pm-stat-label">Completed Projects</div>
                    <div class="pm-stat-value"><?php echo $completedProjects ?></div>
                </div>
            </div>

        </div>

        <!--  Filter Toolbar Card-->
        <div class="pm-toolbar-card">
            <div class="pm-toolbar-row">
                <div class="pm-filters-left">
                    
                    <!-- Search Input -->
                    <div class="pm-search-wrap">
                        <span class="material-symbols-outlined">search</span>
                        <input type="text" id="pmSearchInput" class="pm-search-input" placeholder="Search projects...">
                    </div>

                    <!-- Project Status Filter (Active, Hold, Completed, Review, Rejected) -->
                    <div class="pm-select-wrap">
                        <select id="pmStatusFilter" class="pm-select" aria-label="Filter by Project Status">
                            <option value="all">Project Status</option>
                            <option value="active">Active</option>
                            <option value="hold">Hold</option>
                            <option value="completed">Completed</option>
                            <option value="review">Review</option>
                            <option value="rejected">Rejected</option>
                        </select>
                        <span class="material-symbols-outlined select-chevron">expand_more</span>
                    </div>

                    <!-- Project Type Filter -->
                    <div class="pm-select-wrap">
                        <select id="pmTypeFilter" class="pm-select" aria-label="Filter by Project Type">
                            <option value="all">Project Type</option>
                            <?php if (!empty($categoriesList)): ?>
                                <?php foreach ($categoriesList as $cat): ?>
                                    <option value="<?= htmlspecialchars(strtolower($cat)) ?>"><?= htmlspecialchars($cat) ?></option>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <option value="education">Education Technology</option>
                                <option value="health">HealthTech</option>
                                <option value="fintech">FinTech</option>
                                <option value="logistics">Logistics & AI</option>
                                <option value="iot">IoT & Sensors</option>
                                <option value="cybersecurity">Cybersecurity</option>
                            <?php endif; ?>
                        </select>
                        <span class="material-symbols-outlined select-chevron">expand_more</span>
                    </div>

                </div>
            </div>
        </div>

        <!--  Projects Data Table Card-->
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
                        
                        <?php if (!empty($projectsList)): ?>
                            <?php foreach ($projectsList as $proj): 
                                $idNum = (int)$proj['id'];
                                $projId = '#PR' . str_pad($idNum, 3, '0', STR_PAD_LEFT);
                                $title = htmlspecialchars($proj['title'] ?? 'Untitled Project');
                                $category = htmlspecialchars($proj['category'] ?? 'General');
                                $org = htmlspecialchars($proj['company'] ?? 'N/A');
                                
                                $rawStatus = trim($proj['status'] ?? 'Active');
                                $sLower = strtolower($rawStatus);
                                if (in_array($sLower, ['inprogress', 'active'])) {
                                    $status = 'Active';
                                } elseif (in_array($sLower, ['completed', 'complete', 'close'])) {
                                    $status = 'Completed';
                                } elseif (in_array($sLower, ['reviewing', 'review', 'rewiewing'])) {
                                    $status = 'Review';
                                } elseif ($sLower === 'hold') {
                                    $status = 'Hold';
                                } elseif ($sLower === 'rejected') {
                                    $status = 'Rejected';
                                } else {
                                    $status = ucfirst($sLower);
                                }

                                $skillsStr = trim($proj['tech_stack'] ?? '');
                                $skillsArr = !empty($skillsStr) ? array_filter(array_map('trim', explode(',', $skillsStr))) : ['General'];
                                $skillsAttr = htmlspecialchars(implode(' ', $skillsArr));
                                $members = (int)($proj['members'] ?? 1);
                                $createdDate = !empty($proj['posted_at']) ? date('d F Y', strtotime($proj['posted_at'])) : date('d F Y');
                                
                                $holdReason = htmlspecialchars($proj['hold_reason'] ?? '');
                                $holdResponse = htmlspecialchars($proj['hold_response'] ?? '');
                                $orgInitial = !empty($org) ? strtoupper(substr($org, 0, 1)) : 'U';
                                
                                $orgLower = strtolower($org);
                                $orgClass = 'org-default';
                                if (strpos($orgLower, 'ucsc') !== false) {
                                    $orgClass = 'org-ucsc';
                                } elseif (strpos($orgLower, 'abc') !== false) {
                                    $orgClass = 'org-abc';
                                } elseif (strpos($orgLower, 'tech') !== false) {
                                    $orgClass = 'org-tech';
                                } elseif (strpos($orgLower, 'sliit') !== false) {
                                    $orgClass = 'org-sliit';
                                }
                            ?>
                            <tr class="pm-data-row" 
                                data-id="<?= $projId ?>" 
                                data-title="<?= $title ?>" 
                                data-category="<?= $category ?>" 
                                data-org="<?= $org ?>" 
                                data-status="<?= $status ?>" 
                                data-skills="<?= $skillsAttr ?>"
                                data-hold-reason="<?= $holdReason ?>"
                                data-hold-days="5"
                                data-team-response="<?= $holdResponse ?>"
                                data-reject-reason="">
                                <td class="pm-project-id"><?= $projId ?></td>
                                <td>
                                    <div class="pm-project-meta">
                                        <span class="pm-title-main"><?= $title ?></span>
                                        <span class="pm-title-category"><?= $category ?></span>
                                    </div>
                                </td>
                                <td>
                                    <div class="pm-org-wrapper">
                                        <span class="pm-org-avatar <?= $orgClass ?>"><?= $orgInitial ?></span>
                                        <span class="pm-org-name"><?= $org ?></span>
                                    </div>
                                </td>
                                <td>
                                    <div class="pm-skills-list">
                                        <?php foreach (array_slice($skillsArr, 0, 3) as $sk): ?>
                                            <span class="pm-skill-pill"><?= htmlspecialchars($sk) ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="pm-team-count">
                                        <span class="material-symbols-outlined">group</span>
                                        <span><?= $members ?> Members</span>
                                    </div>
                                </td>
                                <td>
                                    <?php if ($status === 'Active'): ?>
                                        <span class="pm-status-pill status-active">Active</span>
                                    <?php elseif ($status === 'Hold'): ?>
                                        <span class="pm-status-pill status-hold" title="On Hold: 5 days left in grace period">
                                            <span class="material-symbols-outlined" style="font-size: 13px; margin-right: 3px;">schedule</span>Hold (5d left)
                                        </span>
                                    <?php elseif ($status === 'Completed'): ?>
                                        <span class="pm-status-pill status-completed">Completed</span>
                                    <?php elseif ($status === 'Review'): ?>
                                        <span class="pm-status-pill status-review">Review</span>
                                    <?php elseif ($status === 'Rejected'): ?>
                                        <span class="pm-status-pill status-rejected">Rejected</span>
                                    <?php else: ?>
                                        <span class="pm-status-pill status-active"><?= htmlspecialchars($status) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="pm-created-date"><?= $createdDate ?></td>
                                <td class="pm-actions-cell">
                                    <button class="pm-btn-icon" title="View Details" onclick="viewProjectDetails('<?= $projId ?>')">
                                        <span class="material-symbols-outlined">visibility</span>
                                    </button>
                                    <?php if ($status === 'Active' || $status === 'Review'): ?>
                                        <button class="pm-btn-icon pm-btn-icon-hold" title="Place on Hold (Temporary Review)" onclick="openHoldModal('<?= $projId ?>')">
                                            <span class="material-symbols-outlined">pause_circle</span>
                                        </button>
                                    <?php elseif ($status === 'Hold'): ?>
                                        <button class="pm-btn-icon pm-btn-icon-reactivate" title="Review Hold & Decide Status" onclick="openReviewHoldModal('<?= $projId ?>')">
                                            <span class="material-symbols-outlined">check_circle</span>
                                        </button>
                                    <?php elseif ($status === 'Rejected'): ?>
                                        <button class="pm-btn-icon" title="Permanently Rejected (Cannot be reactivated)" disabled style="color: #cbd5e1; cursor: not-allowed;">
                                            <span class="material-symbols-outlined">lock</span>
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>

                    </tbody>
                </table>
            </div>

            <!--  Pagination & Table Footer-->
            <div class="pm-pagination-bar">
                <div class="pm-pagination-info" id="pmPaginationInfo">
                    Showing 1 to <?= min(5, count($projectsList ?? [])) ?> of <?= count($projectsList ?? []) ?> results
                </div>
                <div class="pm-pagination-controls" id="pmPaginationControls">
                    <!-- Populated dynamically by project_management.js (5 items per page) -->
                </div>
            </div>

        </div>

    </div>
</main>

<!--  Add Project Modal-->
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
                            <option value="Completed">Completed</option>
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

<!--  Project Details Modal-->
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

            <!-- Hold Status Banner (shown when status is Hold) -->
            <div id="pmDetailHoldBox" class="pm-hold-banner" style="display: none;">
                <div class="pm-hold-banner-header">
                    <span class="material-symbols-outlined" style="font-size: 20px;">pause_circle</span>
                    <span>Project Temporarily On Hold</span>
                </div>
                <p class="pm-hold-banner-text" id="pmDetailHoldReason">
                    Complaint received or workflow review required. Team has been granted a grace period to clarify.
                </p>
                <div class="pm-hold-meta-row">
                    <span><strong>Grace Period Remaining:</strong> <span id="pmDetailHoldDeadline">5 Days Left</span></span>
                    <span style="font-size: 11.5px; background: #fbbf24; color: #78350f; padding: 2px 8px; border-radius: 4px; font-weight: 700;">Awaiting Response</span>
                </div>
            </div>

            <!-- Permanent Rejected Banner (shown when status is Rejected) -->
            <div id="pmDetailRejectedBox" class="pm-rejected-banner" style="display: none;">
                <div class="pm-rejected-banner-header">
                    <span class="material-symbols-outlined" style="font-size: 20px;">block</span>
                    <span>Permanently Rejected</span>
                </div>
                <p class="pm-rejected-banner-text" id="pmDetailRejectReason" style="margin-bottom: 8px;">
                    Reason: Violated submission criteria and failed to provide acceptable justification.
                </p>
                <div style="font-size: 11.5px; color: #b91c1c; font-weight: 600; display: flex; align-items: center; gap: 5px;">
                    <span class="material-symbols-outlined" style="font-size: 16px;">lock</span>
                    <span>System Policy: Once rejected, this project CANNOT be reactivated.</span>
                </div>
            </div>

            <!-- Dynamic Moderation Action Area -->
            <div id="pmDetailActionsArea" style="margin-top: 16px; padding-top: 14px; border-top: 1px solid #f1f5f9;">
                <div id="pmDetailActionControls">
                    <!-- Populated dynamically by project_management.js -->
                </div>
            </div>
        </div>
        <div class="pm-modal-footer">
            <button type="button" class="pm-btn-secondary" onclick="closeProjectDetailsModal()">Close</button>
        </div>
    </div>
</div>

<!--  Place Project on Hold Modal-->
<div class="pm-modal-backdrop" id="pmHoldModal">
    <div class="pm-modal-dialog" style="max-width: 520px;">
        <div class="pm-modal-header" style="border-bottom: 1px solid #fef3c7; background-color: #fffbeb;">
            <div style="display: flex; align-items: center; gap: 8px;">
                <span class="material-symbols-outlined" style="color: #d97706; font-size: 22px;">pause_circle</span>
                <h3 style="color: #92400e; margin: 0; font-size: 17px; font-weight: 700;">Place Project on Hold</h3>
            </div>
            <button type="button" class="pm-modal-close" onclick="closeHoldModal()">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <form id="pmHoldProjectForm">
            <div class="pm-modal-body">
                <div style="background: #fffbeb; border: 1px solid #fde68a; border-radius: 10px; padding: 12px 14px; font-size: 12.5px; color: #92400e; line-height: 1.45;">
                    <strong>Ethical Moderation Notice:</strong> Projects should not be rejected on first sight. Placing on Hold grants the team a fair grace period to address complaints or workflow issues before any permanent action is considered.
                </div>
                <p style="font-size: 13.5px; color: #475569; margin: 0; line-height: 1.5;">
                    Placing <strong id="pmHoldProjectTitle" style="color: #0f172a;">this project</strong> (<span id="pmHoldProjectId" style="font-weight: 700;">#PR001</span>) on Hold.
                </p>
                <div class="pm-form-group">
                    <label for="pmHoldReason" style="font-weight: 600; font-size: 13px; color: #1e293b;">
                        Issue / Complaint Reason *
                    </label>
                    <textarea id="pmHoldReason" class="pm-form-control" rows="3" style="height: 85px; resize: vertical;" placeholder="Specify the complaint or issue (e.g., Inappropriate content report, milestone discrepancy, team conflict)..." required></textarea>
                </div>
                <div class="pm-form-group">
                    <label for="pmHoldGracePeriod" style="font-weight: 600; font-size: 13px; color: #1e293b;">
                        Grace Period (Days to respond) *
                    </label>
                    <select id="pmHoldGracePeriod" class="pm-form-control">
                        <option value="7">7 Days (Standard Grace Period)</option>
                        <option value="3">3 Days (Urgent Review)</option>
                        <option value="5">5 Days (Moderate Review)</option>
                        <option value="14">14 Days (Extended Inquiry)</option>
                    </select>
                    <span style="font-size: 11.5px; color: #64748b;">If the team fails to provide a proper justification within this period, the project can be permanently rejected.</span>
                </div>
            </div>
            <div class="pm-modal-footer">
                <button type="button" class="pm-btn-secondary" onclick="closeHoldModal()">Cancel</button>
                <button type="submit" class="pm-btn-hold">
                    <span class="material-symbols-outlined" style="font-size: 18px;">pause_circle</span>
                    <span>Confirm Hold</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!--  Review Hold & Resolution Modal (Multi-step: Review -> Final Reject Reason)-->
<div class="pm-modal-backdrop" id="pmReviewHoldModal">
    <div class="pm-modal-dialog" style="max-width: 620px;">
        <div class="pm-modal-header" style="border-bottom: 1px solid #e2e8f0; background-color: #f8fafc;">
            <div style="display: flex; align-items: center; gap: 8px;">
                <span class="material-symbols-outlined" id="pmReviewModalHeaderIcon" style="color: #0b2246; font-size: 24px;">task_alt</span>
                <div>
                    <h3 id="pmReviewModalHeaderTitle" style="color: #0f172a; margin: 0; font-size: 17px; font-weight: 700;">Hold Review & Decision</h3>
                    <span id="pmReviewModalHeaderSub" style="font-size: 12px; color: #64748b;">Compare Admin pause reason against Team's submitted justification</span>
                </div>
            </div>
            <button type="button" class="pm-modal-close" onclick="closeReviewHoldModal()">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>

        <!-- STEP 1: Review & Decision Comparison -->
        <div id="pmReviewStep1" class="pm-modal-body" style="gap: 14px; display: flex; flex-direction: column;">
            <!-- Project Header Info -->
            <div style="display: flex; align-items: center; justify-content: space-between; background: #ffffff; border: 1px solid #e2e8f0; padding: 12px 16px; border-radius: 12px;">
                <div>
                    <span id="pmReviewProjectId" style="font-weight: 700; color: #0b2246; font-size: 15px;">#PR002</span>
                    <h4 id="pmReviewProjectTitle" style="margin: 2px 0 0; font-size: 14px; font-weight: 600; color: #334155;">Healthcare Management System</h4>
                </div>
                <span class="pm-status-pill status-hold" id="pmReviewStatusBadge">
                    <span class="material-symbols-outlined" style="font-size: 13px; margin-right: 3px;">schedule</span>Hold (5d left)
                </span>
            </div>

            <!-- Two Comparison Cards -->
            <div style="display: flex; flex-direction: column; gap: 12px;">
                
                <!-- Card 1: Admin's Pause Reason -->
                <div class="pm-review-card-admin">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 6px;">
                        <span style="font-size: 13px; font-weight: 700; color: #92400e; display: flex; align-items: center; gap: 6px;">
                            <span class="material-symbols-outlined" style="font-size: 18px;">pause_circle</span>
                            Admin's Pause Reason (Why project was placed on Hold)
                        </span>
                        <span id="pmReviewHoldDaysBadge" style="font-size: 11.5px; font-weight: 600; color: #b45309; background: #fef3c7; padding: 2px 8px; border-radius: 6px;">Grace Period: 5d left</span>
                    </div>
                    <p id="pmReviewAdminReason" style="font-size: 13px; color: #78350f; margin: 0; line-height: 1.5;">
                        Discrepancy in project architecture and pending team compliance explanation.
                    </p>
                </div>

                <!-- Card 2: Team's Submitted Reason / Explanation -->
                <div class="pm-review-card-team">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 6px;">
                        <span style="font-size: 13px; font-weight: 700; color: #166534; display: flex; align-items: center; gap: 6px;">
                            <span class="material-symbols-outlined" style="font-size: 18px;">forum</span>
                            Team's Submitted Justification / Explanation
                        </span>
                        <span id="pmReviewTeamStatusBadge" style="font-size: 11.5px; font-weight: 600; color: #15803d; background: #dcfce7; padding: 2px 8px; border-radius: 6px;">Response Submitted</span>
                    </div>
                    <p id="pmReviewTeamResponse" style="font-size: 13px; color: #14532d; margin: 0; line-height: 1.5;">
                        We have uploaded the revised software architecture diagram (v2.1) and clarified student roles in accordance with platform guidelines. Please review the updated milestones.
                    </p>
                </div>

            </div>

            <!-- Decision Options Guidance -->
            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 10px 14px; font-size: 12px; color: #64748b; line-height: 1.45;">
                <strong style="color: #334155;">Final Decision:</strong> Choose <strong>Reactivate Project</strong> to restore it to Active if the justification is valid. Choose <strong>Permanently Reject</strong> to proceed to state the final rejection reason.
            </div>
        </div>

        <!-- STEP 1 FOOTER -->
        <div id="pmReviewFooter1" class="pm-modal-footer" style="display: flex; align-items: center; justify-content: space-between; gap: 12px;">
            <button type="button" class="pm-btn-secondary" onclick="closeReviewHoldModal()">Cancel</button>
            <div style="display: flex; align-items: center; gap: 10px;">
                <!-- Permanently Reject Button -> Navigates to Step 2 -->
                <button type="button" class="pm-btn-reject" onclick="goToRejectStep()" style="height: 40px;">
                    <span class="material-symbols-outlined" style="font-size: 18px;">block</span>
                    <span>Permanently Reject</span>
                </button>
                <!-- Reactivate to Active Button -->
                <button type="button" class="pm-btn-reactivate" onclick="confirmReactivateFromReview()" style="height: 40px; padding: 0 18px;">
                    <span class="material-symbols-outlined" style="font-size: 18px;">check_circle</span>
                    <span>Reactivate Project</span>
                </button>
            </div>
        </div>

        <!-- STEP 2: State Reason for Permanent Rejection -->
        <div id="pmReviewStep2" class="pm-modal-body" style="gap: 14px; display: none; flex-direction: column;">
            <!-- Warning Banner -->
            <div style="background: #fef2f2; border: 1.5px solid #fecaca; border-radius: 12px; padding: 14px 16px;">
                <div style="display: flex; align-items: center; gap: 8px; color: #b91c1c; font-weight: 700; font-size: 13.5px; margin-bottom: 4px;">
                    <span class="material-symbols-outlined" style="font-size: 20px;">report_problem</span>
                    <span>Permanent Terminal Action Warning</span>
                </div>
                <p style="font-size: 12.5px; color: #991b1b; margin: 0; line-height: 1.5;">
                    Once this project is permanently rejected, it <strong>CANNOT</strong> be reactivated under any circumstance. Please provide the official reason for permanent termination.
                </p>
            </div>

            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 10px 14px; font-size: 13px; color: #334155;">
                Rejecting Project: <strong id="pmReviewStep2ProjectTitle">Healthcare Management System</strong> (<span id="pmReviewStep2ProjectId" style="font-weight: 700; color: #0b2246;">#PR002</span>)
            </div>

            <!-- Rejection Category Dropdown -->
            <div class="pm-form-group">
                <label for="pmFinalRejectCategory" style="font-weight: 600; font-size: 13px; color: #1e293b;">
                    Primary Rejection Category *
                </label>
                <select id="pmFinalRejectCategory" class="pm-form-control">
                    <option value="Unsatisfactory Justification">Unsatisfactory or Inadequate Justification</option>
                    <option value="Unresolved Complaint">Unresolved Complaint within Grace Period</option>
                    <option value="Policy Violation">Violation of Platform Policies / Guidelines</option>
                    <option value="Plagiarism / Falsification">Plagiarism or Falsified Project Information</option>
                    <option value="Non-Responsive Team">Team Failed to Respond before Deadline</option>
                    <option value="Other">Other Specific Grounds</option>
                </select>
            </div>

            <!-- Detailed Rejection Reason Textarea -->
            <div class="pm-form-group">
                <label for="pmFinalRejectReason" style="font-weight: 600; font-size: 13px; color: #1e293b;">
                    Official Rejection Reason & Findings *
                </label>
                <textarea id="pmFinalRejectReason" class="pm-form-control" rows="3" style="height: 85px; resize: vertical;" placeholder="Explain clearly why the team's explanation was rejected and why this project is being permanently terminated..." required></textarea>
                <span id="pmFinalRejectError" style="display: none; font-size: 12px; color: #dc2626; font-weight: 500;">Please state the specific reason for rejecting this project.</span>
            </div>
        </div>

        <!-- STEP 2 FOOTER -->
        <div id="pmReviewFooter2" class="pm-modal-footer" style="display: none; align-items: center; justify-content: space-between; gap: 12px;">
            <button type="button" class="pm-btn-secondary" onclick="goToReviewStep()" style="display: inline-flex; align-items: center; gap: 6px;">
                <span class="material-symbols-outlined" style="font-size: 18px;">arrow_back</span>
                <span>Back to Review</span>
            </button>
            <button type="button" class="pm-btn-danger" onclick="executePermanentRejection()" style="display: inline-flex; align-items: center; gap: 6px;">
                <span class="material-symbols-outlined" style="font-size: 18px;">block</span>
                <span>Confirm Permanent Rejection</span>
            </button>
        </div>
    </div>
</div>

<!--  Reject Project Reason Modal (Permanent & Irreversible) -->
<div class="pm-modal-backdrop" id="pmRejectModal">
    <div class="pm-modal-dialog" style="max-width: 520px;">
        <div class="pm-modal-header" style="border-bottom: 1px solid #fee2e2; background-color: #fff5f5;">
            <div style="display: flex; align-items: center; gap: 8px;">
                <span class="material-symbols-outlined" style="color: #dc2626; font-size: 22px;">report_problem</span>
                <h3 style="color: #991b1b; margin: 0; font-size: 17px; font-weight: 700;">Permanently Reject Project</h3>
            </div>
            <button type="button" class="pm-modal-close" onclick="closeRejectReasonModal()">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <form id="pmRejectProjectForm">
            <div class="pm-modal-body">
                <div style="background: #fef2f2; border: 1.5px solid #fecaca; border-radius: 10px; padding: 12px 14px; font-size: 12.5px; color: #991b1b; line-height: 1.45;">
                    <strong>Permanent Action Warning:</strong> Once a project is placed in the <strong>Rejected</strong> state, it <u>CANNOT</u> be reactivated under any circumstance. Only proceed if the grace period has elapsed or the team failed to provide acceptable justification.
                </div>
                <p style="font-size: 13.5px; color: #475569; margin: 0; line-height: 1.5;">
                    Rejecting <strong id="pmRejectProjectTitle" style="color: #0f172a;">this project</strong> (<span id="pmRejectProjectId" style="font-weight: 700;">#PR001</span>).
                </p>
                <div class="pm-form-group">
                    <label for="pmRejectReason" style="font-weight: 600; font-size: 13px; color: #1e293b;">
                        Final Rejection Reason *
                    </label>
                    <textarea id="pmRejectReason" class="pm-form-control" rows="4" style="height: 95px; resize: vertical;" placeholder="State final reason for permanent rejection (e.g., Unresolved complaint after 7-day grace period, confirmed policy violation, failure to respond)..." required></textarea>
                </div>
            </div>
            <div class="pm-modal-footer">
                <button type="button" class="pm-btn-secondary" onclick="closeRejectReasonModal()">Cancel</button>
                <button type="submit" class="pm-btn-danger">Confirm Final Rejection</button>
            </div>
        </form>
    </div>
</div>

<!--  Toast Notification Alert -->
<div class="pm-toast" id="pmToast">
    <span class="material-symbols-outlined">check_circle</span>
    <span id="pmToastMessage">Operation successful!</span>
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

<!-- Project Management Dedicated Script -->
<script src="../../Assets/JS/Admin/project_management.js?v=<?php echo time(); ?>"></script>

<?php include "../../Includes/dash_footer.php"; ?>