<?php

include "../../Config/db.php";
include "../../Session/session.php";

require_login();
require_role('admin');
$user = current_user();

require_once __DIR__ . "/../../Backend/Admin/complain.php";

// Include Sidebar and Header
include "../../Includes/admin_sidebar.php";
?>

<!-- Complaint Management Stylesheet -->
<link rel="stylesheet" href="../../Assets/CSS/Admin/complain.css?v=<?php echo time(); ?>">

<?php
include "../../Includes/dash_header.php";
?>

<main class="content">
    <div class="cm-container">

        <!--  Page Header (Title & Subtitle)-->
        <div class="cm-header-row">
            <div class="cm-header-info">
                <h1>Complaint Management</h1>
                <p>Review and resolve reported issues from the SkillBridge community.</p>
            </div>
        </div>

        <!--  KPI Statistics Cards (Row of 3 Cards matching design)-->
        <div class="cm-stats-grid">
            
            <!-- Card 1: Total Complaints -->
            <div class="cm-stat-card">
                <div class="cm-stat-card-top">
                    <div class="cm-stat-icon icon-analytics">
                        <span class="material-symbols-outlined">analytics</span>
                    </div>
                    <span class="cm-stat-trend trend-amber">
                        +12% from last week
                    </span>
                </div>
                <div class="cm-stat-details">
                    <div class="cm-stat-label">Total Complaints</div>
                    <div class="cm-stat-value" id="cmKpiTotal">
                        <?= number_format($stats['total']) ?>
                    </div>
                </div>
            </div>

            <!-- Card 2: Pending Resolution -->
            <div class="cm-stat-card">
                <div class="cm-stat-card-top">
                    <div class="cm-stat-icon icon-pending">
                        <span class="material-symbols-outlined">pending_actions</span>
                    </div>
                    <span class="cm-stat-trend trend-orange">
                        <?= $stats['pending'] > 0 ? 'Requires attention' : 'All resolved' ?>
                    </span>
                </div>
                <div class="cm-stat-details">
                    <div class="cm-stat-label">Pending Resolution</div>
                    <div class="cm-stat-value" id="cmKpiPending">
                        <?= number_format($stats['pending']) ?>
                    </div>
                </div>
            </div>

            <!-- Card 3: Average Response Time -->
            <div class="cm-stat-card">
                <div class="cm-stat-card-top">
                    <div class="cm-stat-icon icon-timer">
                        <span class="material-symbols-outlined">timer</span>
                    </div>
                    <span class="cm-stat-trend trend-blue">
                        Live resolution avg
                    </span>
                </div>
                <div class="cm-stat-details">
                    <div class="cm-stat-label">Average Response Time</div>
                    <div class="cm-stat-value" id="cmKpiAvgTime">
                        <?= htmlspecialchars($stats['avg_response_time']) ?>
                    </div>
                </div>
            </div>

        </div>

        <!--  Filter & Search Toolbar -->
        <div class="cm-toolbar-card">
            <div class="cm-toolbar-row">
                
                <div class="cm-filters-group">
                    
                    <!-- Search Input -->
                    <div class="cm-search-wrap">
                        <span class="material-symbols-outlined">search</span>
                        <input type="text" id="cmSearchInput" class="cm-search-input" 
                               placeholder="Search projects, organizations, users, complaints..." 
                               autocomplete="off">
                    </div>

                    <!-- Status Filter -->
                    <div class="cm-filter-item">
                        <div class="cm-select-wrap">
                            <select id="cmStatusFilter" class="cm-select">
                                <option value="all">All Statuses</option>
                                <option value="pending">Pending</option>
                                <option value="in_progress">In Progress</option>
                                <option value="resolved">Resolved</option>
                                <option value="dismissed">Dismissed</option>
                            </select>
                            <span class="material-symbols-outlined select-chevron">expand_more</span>
                        </div>
                    </div>

                    <!-- Priority Level -->
                    <div class="cm-filter-item">
                        <div class="cm-select-wrap">
                            <select id="cmPriorityFilter" class="cm-select">
                                <option value="all">All Priorities</option>
                                <option value="urgent">Urgent</option>
                                <option value="high">High</option>
                                <option value="medium">Medium</option>
                                <option value="low">Low</option>
                            </select>
                            <span class="material-symbols-outlined select-chevron">expand_more</span>
                        </div>
                    </div>

                    <!-- Category -->
                    <div class="cm-filter-item">
                        <div class="cm-select-wrap">
                            <select id="cmCategoryFilter" class="cm-select">
                                <option value="all">All Categories</option>
                                <option value="Technical">Technical</option>
                                <option value="Organization">Organization</option>
                                <option value="Academic">Academic</option>
                                <option value="Project & Milestones">Project & Milestones</option>
                                <option value="User Conduct">User Conduct</option>
                            </select>
                            <span class="material-symbols-outlined select-chevron">expand_more</span>
                        </div>
                    </div>

                </div>



            </div>
        </div>

        <!--  Complaints Data Table Card-->
        <div class="cm-table-card">
            <div class="cm-table-responsive">
                <table class="cm-table">
                    <thead>
                        <tr>
                            <th>Complaint ID</th>
                            <th>Submitted By</th>
                            <th>Category</th>
                            <th>Description</th>
                            <th>Priority</th>
                            <th>Status</th>
                            <th style="text-align: right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="cmTableBody">
                        <?php if (!empty($complaints)): ?>
                            <?php foreach ($complaints as $idx => $cmp): 
                                // Real Complaint ID padded to 4 digits: #CMP-0001
                                $displayId = '#CMP-' . str_pad($cmp['id'], 4, '0', STR_PAD_LEFT);
                                $priorityLower = strtolower($cmp['priority'] ?? 'medium');
                                $statusRaw = strtoupper($cmp['status'] ?? 'PENDING');
                                
                                // Normalizing status display
                                if ($statusRaw === 'IN_REVIEW' || $statusRaw === 'IN PROGRESS') {
                                    $statusClass = 'progress';
                                    $statusLabel = 'IN PROGRESS';
                                } elseif ($statusRaw === 'RESOLVED') {
                                    $statusClass = 'resolved';
                                    $statusLabel = 'RESOLVED';
                                } elseif ($statusRaw === 'DISMISSED') {
                                    $statusClass = 'dismissed';
                                    $statusLabel = 'DISMISSED';
                                } else {
                                    $statusClass = 'pending';
                                    $statusLabel = 'PENDING';
                                }

                                // Role formatting
                                $roleDisplay = ucfirst($cmp['role'] ?? 'Student');
                                if (stripos($cmp['organization_name'] ?? '', 'University') !== false) {
                                    $roleDisplay = 'University Rep';
                                } elseif ($cmp['role'] === 'company' || $cmp['role'] === 'organization') {
                                    $roleDisplay = 'Organization HR';
                                } elseif ($cmp['role'] === 'mentor') {
                                    $roleDisplay = 'Mentor';
                                }

                                // Avatar initials
                                $userName = $cmp['user_name'] ?? 'Community Member';
                                $nameWords = explode(' ', trim($userName));
                                $initials = count($nameWords) >= 2 
                                    ? strtoupper(substr($nameWords[0], 0, 1) . substr($nameWords[1], 0, 1))
                                    : strtoupper(substr($userName, 0, 2));

                                // Avatar color theme
                                $avatarClass = 'av-student';
                                if (stripos($roleDisplay, 'University') !== false) $avatarClass = 'av-uni';
                                elseif (stripos($roleDisplay, 'Mentor') !== false) $avatarClass = 'av-mentor';
                                elseif (stripos($roleDisplay, 'HR') !== false || stripos($roleDisplay, 'Org') !== false) $avatarClass = 'av-hr';
                                elseif (stripos($roleDisplay, 'Company') !== false) $avatarClass = 'av-company';

                                $categoryDisplay = htmlspecialchars($cmp['category'] ?? 'Technical');
                                $descExcerpt = htmlspecialchars($cmp['title'] . ' - ' . $cmp['discription']);
                            ?>
                            <tr class="cm-data-row"
                                style="<?= $idx >= 6 ? 'display: none;' : '' ?>"
                                data-id="<?= $cmp['id'] ?>"
                                data-display-id="<?= $displayId ?>"
                                data-user-name="<?= htmlspecialchars($userName) ?>"
                                data-user-email="<?= htmlspecialchars($cmp['email']) ?>"
                                data-user-role="<?= htmlspecialchars($roleDisplay) ?>"
                                data-org="<?= htmlspecialchars($cmp['organization_name'] ?? 'SkillBridge') ?>"
                                data-category="<?= $categoryDisplay ?>"
                                data-title="<?= htmlspecialchars($cmp['title']) ?>"
                                data-desc="<?= htmlspecialchars($cmp['discription']) ?>"
                                data-priority="<?= $priorityLower ?>"
                                data-status="<?= strtolower($statusClass) ?>"
                                data-notes="<?= htmlspecialchars($cmp['resolution_notes'] ?? '') ?>"
                                data-created-at="<?= htmlspecialchars($cmp['create_at']) ?>">
                                
                                <!-- ID -->
                                <td>
                                    <span class="cm-complaint-id"><?= $displayId ?></span>
                                </td>

                                <!-- User -->
                                <td>
                                    <div class="cm-user-cell">
                                        <div class="cm-user-avatar <?= $avatarClass ?>"><?= $initials ?></div>
                                        <div class="cm-user-meta">
                                            <span class="cm-user-name"><?= htmlspecialchars($userName) ?></span>
                                            <span class="cm-user-role"><?= htmlspecialchars($roleDisplay) ?></span>
                                        </div>
                                    </div>
                                </td>

                                <!-- Category -->
                                <td>
                                    <span class="cm-category-cell"><?= $categoryDisplay ?></span>
                                </td>

                                <!-- Description -->
                                <td>
                                    <div class="cm-desc-cell" title="<?= $descExcerpt ?>">
                                        <?= $descExcerpt ?>
                                    </div>
                                </td>

                                <!-- Priority -->
                                <td>
                                    <span class="cm-priority-pill priority-<?= $priorityLower ?>">
                                        <?= strtoupper($priorityLower) ?>
                                    </span>
                                </td>

                                <!-- Status -->
                                <td>
                                    <span class="cm-status-pill status-<?= $statusClass ?>">
                                        <?= $statusLabel ?>
                                    </span>
                                </td>

                                <!-- Actions -->
                                <td>
                                    <div class="cm-actions-cell">
                                        <button type="button" class="cm-btn-icon cm-btn-view" data-action="view" title="View & Investigate Details">
                                            <span class="material-symbols-outlined" style="font-size: 19px;">visibility</span>
                                        </button>
                                    </div>
                                </td>

                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr id="cmEmptyStateRow">
                                <td colspan="7" style="text-align: center; padding: 48px 20px; color: #64748b;">
                                    <span class="material-symbols-outlined" style="font-size: 40px; color: #94a3b8; display: block; margin-bottom: 8px;">report_off</span>
                                    <strong style="font-size: 15px; color: #1e293b; display: block; margin-bottom: 4px;">No complaints recorded</strong>
                                    <span style="font-size: 13px;">All platform systems and student activities are running smoothly without open grievances.</span>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>


            <!-- Pagination Footer Bar (Strictly 6 complaints per page)-->
            <div class="cm-pagination-bar">
                <div class="cm-pagination-info" id="cmPaginationInfo">
                    Showing <?= $totalComplaintsCount > 0 ? '1' : '0' ?> to <?= min($totalComplaintsCount, $pageSize) ?> of <?= $totalComplaintsCount ?> results
                </div>
                <div class="cm-pagination-controls" id="cmPaginationControls">
                    <button type="button" class="cm-page-btn" id="cmBtnPrev" disabled>Previous</button>
                    <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                        <button type="button" class="cm-page-btn cm-page-number <?= $p === 1 ? 'active' : '' ?>" data-page="<?= $p ?>"><?= $p ?></button>
                    <?php endfor; ?>
                    <button type="button" class="cm-page-btn" id="cmBtnNext" <?= $totalPages <= 1 ? 'disabled' : '' ?>>Next</button>
                </div>
            </div>

        </div>

    </div>
</main>

<!-- Complaint Detail & Resolution Modal-->
<div class="cm-modal-backdrop" id="cmDetailModal">
    <div class="cm-modal-dialog">
        <form id="cmDetailForm" class="cm-modal-form">
            <div class="cm-modal-header">
                <div>
                    <h3 id="modalComplaintId">#CMP-9284</h3>
                    <span style="font-size: 12px; color: #64748b;">Complaint Investigation & Resolution</span>
                </div>
                <button type="button" class="cm-modal-close" data-cm-dismiss="modal" title="Close">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>

            <input type="hidden" id="modalHiddenId" value="">

            <div class="cm-modal-body">
                
                <!-- Complainant Identity Box -->
                <div class="cm-detail-meta-box">
                    <div style="display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #e2e8f0; padding-bottom: 10px;">
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <div class="cm-user-avatar" id="modalUserAvatar">JD</div>
                            <div>
                                <strong id="modalUserName" style="font-size: 14.5px; color: #0f172a; display: block;">John Doe</strong>
                                <span id="modalUserRole" style="font-size: 12px; color: #64748b; font-weight: 500;">Student</span>
                            </div>
                        </div>
                        <div style="display: flex; gap: 8px;">
                            <span id="modalPriorityBadge" class="cm-priority-pill priority-high">HIGH</span>
                            <span id="modalStatusBadge" class="cm-status-pill status-pending">PENDING</span>
                        </div>
                    </div>

                    <div class="cm-meta-row">
                        <span>Organization / University:</span>
                        <strong id="modalUserOrg">SkillBridge Network</strong>
                    </div>

                    <div class="cm-meta-row">
                        <span>Contact Email:</span>
                        <strong id="modalUserEmail">user@example.com</strong>
                    </div>

                    <div class="cm-meta-row">
                        <span>Category:</span>
                        <strong id="modalCategoryText">Technical</strong>
                    </div>

                    <div class="cm-meta-row">
                        <span>Date Reported:</span>
                        <strong id="modalSubmittedDate">2026-09-25 10:20:00</strong>
                    </div>
                </div>

                <!-- Complaint Content Box -->
                <div class="cm-detail-desc-box">
                    <h4 id="modalComplaintTitle">Issue Title</h4>
                    <p id="modalComplaintDesc">Detailed description of the complaint submitted by the user...</p>
                </div>

                <!-- Notice when already resolved -->
                <div id="modalResolvedNotice" class="cm-resolved-notice" style="display: none;">
                    <span class="material-symbols-outlined">verified</span>
                    <div>
                        <strong style="font-size: 13.5px;">This Complaint is Resolved</strong>
                        <p style="margin: 3px 0 0; font-size: 12.5px; color: #166534; line-height: 1.4;">This issue has been successfully resolved and finalized. You can review the details and recorded findings below, but no further actions can be taken.</p>
                    </div>
                </div>

                <!-- Resolution Findings & Actions -->
                <div class="cm-resolution-area" id="modalResolutionArea">
                    <div class="cm-form-group" id="modalStatusGroup">
                        <label for="modalStatusSelect">Update Resolution Status</label>
                        <div class="cm-select-wrap">
                            <select id="modalStatusSelect" class="cm-select">
                                <option value="PENDING">Pending (Requires Attention)</option>
                                <option value="IN_REVIEW">In Progress (Under Active Investigation)</option>
                                <option value="RESOLVED">Resolved (Issue Addressed & Verified)</option>
                                <option value="DISMISSED">Dismissed (No Action Required)</option>
                            </select>
                            <span class="material-symbols-outlined select-chevron">expand_more</span>
                        </div>
                    </div>

                    <div class="cm-form-group">
                        <label for="modalResolutionNotes" id="modalNotesLabel">Investigation Findings / Resolution Notes</label>
                        <textarea id="modalResolutionNotes" class="cm-form-control" 
                                  placeholder="Record investigation notes, corrective action taken, or explanation for dismissal..."></textarea>
                        <small id="modalNotifHint" style="font-size: 11.5px; color: #64748b; margin-top: 5px; display: flex; align-items: center; gap: 5px;"></small>
                    </div>
                </div>

            </div>

            <div class="cm-modal-footer">
                <button type="button" class="cm-btn-secondary" data-cm-dismiss="modal">Close</button>
                <button type="button" id="modalBtnSubmitAction" class="cm-btn-primary">Update Status</button>
            </div>
        </form>

    </div>
</div>

<!-- Toast Notification Alert (Custom Feedback, ZERO alerts  -->
<div class="cm-toast" id="cmToast">
    <span class="material-symbols-outlined">check_circle</span>
    <span id="cmToastMessage">Operation successful!</span>
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

<!-- Complaint Management Script -->
<script src="../../Assets/JS/Admin/complain.js?v=<?php echo time(); ?>"></script>

<?php include "../../Includes/dash_footer.php"; ?>
