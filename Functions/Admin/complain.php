<!--Initialize Admin Environment: Auth Guard, 
Backend Controller & Sidebar-->
<?php
$backendPage = "complain.php";
require_once __DIR__ . "/Components/AdminInit.php";
?>
<!--End Here -->

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

        <!--  Complain Cards-->
        <div class="cm-stats-grid">
            
            <?php
                $CardDetails = [
                    "cardIcons"  => ["analytics", "pending_actions", "timer"],
                    "iconColors" => ["grey", "amber", "blue"],
                    "cardLabels" => ["Total Complaints", "Pending Resolution", "Average Response Time"],
                    "cardValues" => [number_format($stats['total']), number_format($stats['pending']), $stats['avg_response_time']],
                    "valueIds"   => ["cmKpiTotal", "cmKpiPending", "cmKpiAvgTime"]
                ];
                include __DIR__ . "/Components/Card.php";
            ?>

        </div>
        <!-- EndHere -->

        <!-- Unified Complaints Data Table Card (Toolbar + Table + Pagination) -->
        <div class="cm-table-card">

            <!-- Filter & Search Toolbar -->
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

            <?php require_once __DIR__ . '/Components/complainTable.php'; ?>

            <!-- Reusable Pagination Component -->
            <?php 
                $totalRecords         = $totalComplaintsCount;
                $perPage              = $pageSize;
                $entityLabel          = 'results';
                $paginationInfoId     = 'cmPaginationInfo';
                $paginationControlsId = 'cmPaginationControls';
                $alwaysShow           = true;
                include __DIR__ . '/Components/Pagination.php';
            ?>

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
