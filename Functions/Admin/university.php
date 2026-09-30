<?php
require_once "../../Config/db.php";
require_once "../../Session/Session.php";
require_login();
require_role('admin');

require_once __DIR__ . "/../../Backend/Admin/university.php";

include "../../Includes/admin_sidebar.php";
?>
<link rel="stylesheet" href="../../Assets/CSS/Admin/university.css">
<?php
include "../../Includes/dash_header.php";
?>

<main class="content">

    <!-- Flash Notification -->
    <?php if (!empty($flash)): ?>
        <div class="flash-toast flash-<?= htmlspecialchars($flash['type']) ?>" id="flashToast">
            <span class="material-symbols-outlined flash-icon">
                <?= $flash['type'] === 'error' ? 'error' : 'check_circle' ?>
            </span>
            <span class="flash-msg"><?= htmlspecialchars($flash['message']) ?></span>
            <button class="flash-close" onclick="this.parentElement.remove()">
                <span class="material-symbols-outlined" style="font-size:16px;">close</span>
            </button>
        </div>
    <?php endif; ?>

    <!-- Page Header -->
    <div class="univ-page-header">
        <div class="univ-breadcrumb">
            <a href="dashboard.php">Admin</a>
            <span class="material-symbols-outlined" style="font-size:14px;color:#9ca3af;">chevron_right</span>
            <span>University Registry</span>
        </div>
        <div class="univ-title-row">
            <div>
                <h1 class="univ-title">University Registry</h1>
                <p class="univ-subtitle">Manage partner institutions and their academic domains to ensure secure student
                    enrollment and verification across the platform.</p>
            </div>
            <button class="btn-add-university" id="btnAddUniversity">
                <span class="material-symbols-outlined" style="font-size:18px;">add</span>
                Add University
            </button>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="univ-stats-grid">

        <div class="univ-stat-card">
            <div class="univ-stat-icon-wrap grey">
                <span class="material-symbols-outlined">school</span>
            </div>
            <div class="univ-stat-info">
                <div class="univ-stat-label">TOTAL INSTITUTIONS</div>
                <div class="univ-stat-value"><?php echo htmlspecialchars($totUni); ?></div>
            </div>
        </div>

        <div class="univ-stat-card">
            <div class="univ-stat-icon-wrap green">
                <span class="material-symbols-outlined">check_circle</span>
            </div>
            <div class="univ-stat-info">
                <div class="univ-stat-label">ACTIVE DOMAINS</div>
                <div class="univ-stat-value">
                    <?php echo htmlspecialchars($activeDomains); ?>
                </div>
            </div>
        </div>

        <div class="univ-stat-card">
            <div class="univ-stat-icon-wrap orange">
                <span class="material-symbols-outlined">schedule</span>
            </div>
            <div class="univ-stat-info">
                <div class="univ-stat-label">ON HOLD</div>
                <div class="univ-stat-value">
                    <?php echo htmlspecialchars($holdDomains); ?>
                </div>
            </div>
        </div>

        <div class="univ-stat-card">
            <div class="univ-stat-icon-wrap blue">
                <span class="material-symbols-outlined">people</span>
            </div>
            <div class="univ-stat-info">
                <div class="univ-stat-label">TOTAL STUDENTS</div>
                <div class="univ-stat-value"><?php echo htmlspecialchars($totalStudents); ?></div>
            </div>
        </div>

    </div>

    <!-- Institution List Table -->
    <div class="full-width-section">
        <div class="card">

            <div class="card-header">
                <div>
                    <h3>Institution List</h3>
                </div>
                <div class="univ-table-actions">
                    <form method="GET" action="" style="display:flex; gap:10px; align-items:center;">
                        <select name="status" class="univ-select-filter" onchange="this.form.submit()">
                            <option value="all" <?= $selectedStatus === 'all' ? 'selected' : '' ?>>All Statuses</option>
                            <option value="Active" <?= $selectedStatus === 'Active' ? 'selected' : '' ?>>Active</option>
                            <option value="Hold" <?= $selectedStatus === 'Hold' ? 'selected' : '' ?>>Hold</option>
                            <option value="Inactive" <?= $selectedStatus === 'Inactive' ? 'selected' : '' ?>>Inactive
                            </option>
                        </select>

                        <div class="univ-search-box">
                            <span class="material-symbols-outlined" style="font-size:18px;color:#9ca3af;">search</span>
                            <input type="text" name="search" placeholder="Search name, email, org..."
                                id="univSearchInput" value="<?= htmlspecialchars($searchQuery) ?>" autocomplete="off">
                        </div>
                    </form>
                    <button type="button" class="univ-icon-btn" title="Export CSV" id="exportBtn">
                        <span class="material-symbols-outlined" style="font-size:18px;">download</span>
                    </button>
                </div>
            </div>

            <div class="card-body" style="padding:0; overflow-x: auto;">
                <table class="data-table" id="univTable">
                    <thead>
                        <tr>
                            <th></th>
                            <th>UNIVERSITY NAME</th>
                            <th>EMAIL DOMAIN</th>
                            <th>LOCATION</th>
                            <th>STUDENTS</th>
                            <th>STATUS</th>
                            <th>ACTIONS</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($pageUniversities)): ?>
                            <tr>
                                <td colspan="7" style="text-align: center; padding: 40px; color: #94a3b8;">
                                    <span class="material-symbols-outlined"
                                        style="font-size: 40px; color: #cbd5e1; display:block; margin-bottom:8px;">search_off</span>
                                    No universities found matching your criteria.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php
                            foreach ($pageUniversities as $row):
                                $initials = strtoupper(substr($row['University'], 0, 3));
                                $badgeCls = match (strtolower($row['Status'] ?? '')) {
                                    'active' => 'active',
                                    'hold' => 'hold',
                                    'inactive', 'deactive' => 'inactive',
                                    default => 'inactive'
                                };
                                ?>
                                <tr>
                                    <td>
                                        <div class="university-logo mit" style="background:#1e293b;">
                                            <?= htmlspecialchars($initials) ?></div>
                                    </td>
                                    <td>
                                        <div class="university-name"><?= htmlspecialchars($row['University']) ?></div>
                                        <div class="university-faculty"><?= htmlspecialchars($row['faculty'] ?? '') ?></div>
                                    </td>
                                    <td><span class="univ-domain-badge">@<?= htmlspecialchars($row['emailEx']) ?></span></td>
                                    <td><?= htmlspecialchars($row['Location'] ?? '—') ?></td>
                                    <?php $stuCount = $adminDB->getStudentCountByDomain($row['emailEx']); ?>
                                    <td><?= $stuCount ?></td>
                                    <td><span
                                            class="badge-status <?= $badgeCls ?>"><?= htmlspecialchars($row['Status'] ?? '') ?></span>
                                    </td>
                                    <td>
                                        <div class="univ-actions-cell">
                                            <button class="action-btn" type="button" title="View Details" onclick="openViewModal(
                                            '<?= htmlspecialchars(addslashes($row['University'])) ?>',
                                            '<?= htmlspecialchars(addslashes($row['faculty'] ?? '')) ?>',
                                            '<?= htmlspecialchars(addslashes($row['emailEx'])) ?>',
                                            '<?= htmlspecialchars(addslashes($row['Location'] ?? '')) ?>',
                                            '<?= $stuCount ?>',
                                            '<?= htmlspecialchars(addslashes($row['Status'] ?? '')) ?>')">
                                                <span class="material-symbols-outlined"
                                                    style="font-size:18px;">visibility</span>
                                            </button>
                                            <?php if (strcasecmp($row['Status'] ?? '', 'Inactive') !== 0): ?>
                                            <button class="action-btn" type="button" title="Edit" onclick="openEditModal(
                                            '<?= htmlspecialchars(addslashes($row['University'])) ?>',
                                            '<?= htmlspecialchars(addslashes($row['faculty'] ?? '')) ?>',
                                            '<?= htmlspecialchars(addslashes($row['emailEx'])) ?>',
                                            '<?= htmlspecialchars(addslashes($row['Location'] ?? '')) ?>',
                                            '<?= htmlspecialchars(addslashes($row['Status'] ?? '')) ?>')">
                                                <span class="material-symbols-outlined" style="font-size:18px;">edit</span>
                                            </button>
                                            <?php endif; ?>
                                            <button class="action-btn" type="button" title="Delete" style="color:#dc2626;"
                                                data-status="<?= htmlspecialchars(trim($row['Status'] ?? '')) ?>"
                                                onclick="openDeleteModal('<?= htmlspecialchars(addslashes($row['emailEx'])) ?>', '<?= htmlspecialchars(addslashes($row['University'])) ?>', <?= (int)$stuCount ?>, '<?= htmlspecialchars(addslashes($row['faculty'] ?? '')) ?>', '<?= htmlspecialchars(addslashes($row['Location'] ?? '')) ?>', '<?= htmlspecialchars(addslashes(trim($row['Status'] ?? ''))) ?>')">
                                                <span class="material-symbols-outlined" style="font-size:18px;">delete</span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination (Shows only if more than 5 universities exist) -->
            <?php if ($totalPages > 1): ?>
                <div class="univ-pagination">
                    <div class="univ-pagination-info">
                        Showing <?= $offset + 1 ?>–<?= min($offset + $recordsPerPage, $totFilteredUni) ?> of
                        <?= $totFilteredUni ?> universities
                    </div>
                    <div class="univ-pagination-controls">
                        <?php if ($currentPage <= 1): ?>
                            <span class="univ-page-btn disabled">Previous</span>
                        <?php else: ?>
                            <a href="?status=<?= urlencode($selectedStatus) ?>&search=<?= urlencode($searchQuery) ?>&page=<?= $currentPage - 1 ?>"
                                class="univ-page-btn" style="text-decoration:none; color:inherit;">Previous</a>
                        <?php endif; ?>

                        <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                            <?php if ($p == $currentPage): ?>
                                <span class="univ-page-btn univ-page-active"><?= $p ?></span>
                            <?php else: ?>
                                <a href="?status=<?= urlencode($selectedStatus) ?>&search=<?= urlencode($searchQuery) ?>&page=<?= $p ?>"
                                    class="univ-page-btn" style="text-decoration:none; color:inherit;"><?= $p ?></a>
                            <?php endif; ?>
                        <?php endfor; ?>

                        <?php if ($currentPage >= $totalPages): ?>
                            <span class="univ-page-btn disabled">Next</span>
                        <?php else: ?>
                            <a href="?status=<?= urlencode($selectedStatus) ?>&search=<?= urlencode($searchQuery) ?>&page=<?= $currentPage + 1 ?>"
                                class="univ-page-btn" style="text-decoration:none; color:inherit;">Next</a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

        </div>
    </div>
</main>

<!-- Add University Modal  -->
<div class="univ-modal-overlay" id="addModal">
    <div class="univ-modal">
        <div class="univ-modal-header">
            <h3>Add New University</h3>
            <button class="univ-modal-close" id="closeAddModal" type="button">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <form class="univ-modal-body" action="" method="post">
            <input type="hidden" name="action" value="add">
            <div class="form-group">
                <label class="form-label">University Name <span style="color:#ef4444;">*</span></label>
                <div class="univ-combobox" id="addUnivCombobox">
                    <div class="univ-combobox-input-wrap">
                        <input type="text" class="form-input univ-combobox-input" name="university" id="addUnivInput" placeholder="Select or type university name" autocomplete="off" required>
                        <button type="button" class="univ-combobox-toggle" id="addUnivToggle" title="Toggle university list" tabindex="-1">
                            <span class="material-symbols-outlined">expand_more</span>
                        </button>
                    </div>
                    <div class="univ-combobox-dropdown" id="addUnivDropdown">
                        <div class="univ-combobox-list" id="addUnivList"></div>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Faculty Name <span style="color:#ef4444;">*</span></label>
                <input type="text" class="form-input" name="faculty" id="addFacInput" placeholder="e.g. School Of Technology" required>
            </div>
            <div class="form-group">
                <label class="form-label">Email Domain <span style="color:#ef4444;">*</span></label>
                <input type="text" name="domain" id="addDomainInput" class="form-input" placeholder="e.g. cmb.ac.lk" required>
                <small style="color:#9ca3af;font-size:11px;margin-top:4px;display:block;">Enter without the @
                    symbol</small>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Location</label>
                    <input type="text" name="location" id="addLocationInput" class="form-input" placeholder="e.g. Colombo, Sri Lanka">
                </div>
                <div class="form-group">
                    <label class="form-label">Status</label>
                    <select class="form-input" name="status">
                        <option value="Active">Active</option>
                        <option value="Hold">Hold</option>
                        <option value="Inactive">Inactive</option>
                    </select>
                </div>
            </div>
            <div class="univ-modal-footer">
                <button type="button" class="btn-outline" id="cancelAddModal">Cancel</button>
                <button type="submit" class="btn-add-university" style="margin:0;">
                    <span class="material-symbols-outlined" style="font-size:16px;">add</span>
                    Add University
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Edit University Modal -->
<div class="univ-modal-overlay" id="editModal">
    <div class="univ-modal">
        <div class="univ-modal-header">
            <h3>Edit University</h3>
            <button class="univ-modal-close" id="closeEditModal" type="button">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <form class="univ-modal-body" action="" method="post">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="original_domain" id="editOrigDomain">

            <div class="form-group">
                <label class="form-label">University Name <span style="color:#ef4444;">*</span></label>
                <div class="univ-combobox" id="editUnivCombobox">
                    <div class="univ-combobox-input-wrap">
                        <input type="text" class="form-input univ-combobox-input" name="university" id="editUni" placeholder="Select or type university name" autocomplete="off" required>
                        <button type="button" class="univ-combobox-toggle" id="editUnivToggle" title="Toggle university list" tabindex="-1">
                            <span class="material-symbols-outlined">expand_more</span>
                        </button>
                    </div>
                    <div class="univ-combobox-dropdown" id="editUnivDropdown">
                        <div class="univ-combobox-list" id="editUnivList"></div>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Faculty Name <span style="color:#ef4444;">*</span></label>
                <input type="text" class="form-input" name="faculty" id="editFac" required>
            </div>
            <div class="form-group">
                <label class="form-label">Email Domain <span style="color:#ef4444;">*</span></label>
                <input type="text" name="domain" id="editDomain" class="form-input" required>
                <small style="color:#9ca3af;font-size:11px;margin-top:4px;display:block;">Enter without the @
                    symbol</small>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Location</label>
                    <input type="text" name="location" id="editLocation" class="form-input">
                </div>
                <div class="form-group">
                    <label class="form-label">Status</label>
                    <select class="form-input" name="status" id="editStatus">
                        <option value="Active">Active</option>
                        <option value="Hold">Hold</option>
                        <option value="Inactive">Inactive</option>
                    </select>
                    <small id="inactiveStatusNotice" style="color:#b45309; font-size:11.5px; margin-top:5px; display:none; line-height:1.4;">
                        <span class="material-symbols-outlined" style="font-size:13px; vertical-align:middle;">warning</span>
                        <strong>Permanent Change:</strong> Once saved as <em>Inactive</em>, this university cannot be edited or modified again.
                    </small>
                </div>
            </div>
            <div class="univ-modal-footer">
                <button type="button" class="btn-outline" id="cancelEditModal">Cancel</button>
                <button type="submit" class="btn-add-university" style="margin:0;">
                    <span class="material-symbols-outlined" style="font-size:16px;">save</span>
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

<!-- View University Modal -->
<div class="univ-modal-overlay" id="viewModal">
    <div class="univ-modal">
        <div class="univ-modal-header">
            <h3>University Details</h3>
            <button class="univ-modal-close" id="closeViewModal" type="button">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <div class="univ-modal-body" id="viewModalContent"></div>
    </div>
</div>

<!-- Delete University Modal -->
<div class="univ-modal-overlay" id="deleteModal">
    <div class="univ-modal">
        <div class="univ-modal-header" id="deleteModalHeader">
            <h3 style="color:#dc2626; display:flex; align-items:center; gap:8px;">
                <span class="material-symbols-outlined" style="font-size:20px;">delete</span>
                <span>Delete University</span>
            </h3>
            <button class="univ-modal-close" id="closeDeleteModal" type="button">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <form action="" method="post" id="deleteUnivForm">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="delete_domain" id="deleteDomainInput">

            <!-- STATE 1: Normal Deletion (No Students) -->
            <div id="deleteAllowedWrap" class="univ-modal-body" style="padding: 22px 24px;">
                <div style="display:flex; gap:16px; align-items:flex-start;">
                    <div
                        style="width:44px; height:44px; border-radius:50%; background:#fee2e2; color:#dc2626; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                        <span class="material-symbols-outlined" style="font-size:24px;">warning</span>
                    </div>
                    <div style="flex:1;">
                        <h4 style="font-size: 14.5px; font-weight:700; color:#0f172a; margin:0 0 6px 0;">
                            Are you sure you want to delete this university?
                        </h4>
                        <p style="font-size: 13px; color:#64748b; margin:0 0 14px 0; line-height:1.5;">
                            This action cannot be undone. All student domain associations will be removed.
                        </p>

                        <div
                            style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:10px; padding:12px 14px;">
                            <div
                                style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px; font-size:12.5px;">
                                <span style="color:#64748b; font-weight:600;">University:</span>
                                <strong id="deleteUniDisplay" style="color:#0f172a; font-weight:600;"></strong>
                            </div>
                            <div
                                style="display:flex; justify-content:space-between; align-items:center; font-size:12.5px;">
                                <span style="color:#64748b; font-weight:600;">Domain:</span>
                                <strong id="deleteDomainDisplay"
                                    style="color:#2563eb; font-weight:600; font-family:'Courier New', monospace;"></strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- STATE 2: Blocked Deletion (Active Students Enrolled) -->
            <div id="deleteBlockedWrap" class="univ-modal-body" style="padding: 22px 24px; display:none;">
                <div style="display:flex; gap:16px; align-items:flex-start;">
                    <div
                        style="width:46px; height:46px; border-radius:50%; background:#fef3c7; color:#d97706; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                        <span class="material-symbols-outlined" style="font-size:26px;">gpp_maybe</span>
                    </div>
                    <div style="flex:1;">
                        <h4 style="font-size: 15px; font-weight:700; color:#92400e; margin:0 0 6px 0;">
                            Deletion Restricted: Enrolled Students Found
                        </h4>
                        <div
                            style="background:#fffbeb; border:1.5px solid #fde68a; border-radius:10px; padding:12px 14px; margin-bottom:14px; font-size:12.5px; color:#92400e; line-height:1.5;">
                            This institution cannot be deleted because it currently has <strong id="deleteBlockedStuCount" style="color:#b45309; text-decoration:underline;">0 students</strong> enrolled on SkillBridge. Deleting it would orphan active student accounts and break login authorization.
                        </div>

                        <div
                            style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:10px; padding:12px 14px; margin-bottom:12px;">
                            <div
                                style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px; font-size:12.5px;">
                                <span style="color:#64748b; font-weight:600;">University:</span>
                                <strong id="deleteBlockedUni" style="color:#0f172a; font-weight:600;"></strong>
                            </div>
                            <div
                                style="display:flex; justify-content:space-between; align-items:center; font-size:12.5px;">
                                <span style="color:#64748b; font-weight:600;">Authorized Domain:</span>
                                <strong id="deleteBlockedDomain"
                                    style="color:#2563eb; font-weight:600; font-family:'Courier New', monospace;"></strong>
                            </div>
                        </div>

                        <p id="deleteModalRecommendation" style="font-size:12px; color:#64748b; margin:0; line-height:1.4;">
                            <span class="material-symbols-outlined" style="font-size:14px; vertical-align:middle; color:#d97706;">lightbulb</span>
                            <em>Recommended:</em> If you wish to suspend onboarding or platform access without removing user records, change the status to <strong>"Hold"</strong> or <strong>"Inactive"</strong> instead.
                        </p>
                    </div>
                </div>
            </div>

            <div class="univ-modal-footer">
                <button type="button" class="univ-btn-cancel" id="cancelDeleteModal">Cancel</button>
                <button type="button" class="btn-primary" id="editStatusInsteadBtn" style="background:#d97706; border:none; display:none; align-items:center; gap:6px; padding:8px 16px; border-radius:8px; font-size:13px; font-weight:600; color:#fff; cursor:pointer;">
                    <span class="material-symbols-outlined" style="font-size:16px;">edit</span>
                    Change Status Instead
                </button>
                <button type="submit" class="univ-btn-delete" id="confirmDeleteBtn">
                    <span class="material-symbols-outlined" style="font-size:16px;">delete</span>
                    Delete University
                </button>
            </div>
        </form>
    </div>
</div>

<footer class="footer">
    <div>&copy; 2026 SkillBridge. All rights reserved.</div>
    <div class="footer-links">
        <a href="/Skill_Bridge_Group_Project/help_center.php">Help Center</a>
        <a href="#">Privacy Policy</a>
        <a href="#">Terms of Service</a>
    </div>
</footer>

<?php include "../../Includes/dash_footer.php"; ?>
<script src="../../Assets/JS/Admin/universityData.js"></script>
<script src="../../Assets/JS/Admin/university.js?v=<?= time() ?>"></script>
<script>
// Direct safety guarantee to hide Change Status button for Inactive universities
(function() {
    var origFn = window.openDeleteModal;
    window.openDeleteModal = function(domain, name, stuCount, faculty, location, status) {
        if (typeof origFn === 'function') {
            origFn(domain, name, stuCount, faculty, location, status);
        }
        var s = String(status || '').trim().toLowerCase();
        var isInactive = (s === 'inactive' || s === 'deactive' || s === 'disabled');
        var btn = document.getElementById('editStatusInsteadBtn');
        var rec = document.getElementById('deleteModalRecommendation');
        if (btn && isInactive) {
            btn.style.setProperty('display', 'none', 'important');
        }
        if (rec && isInactive) {
            rec.innerHTML = '<span class="material-symbols-outlined" style="font-size:14px; vertical-align:middle; color:#d97706;">lock</span> <em>Status:</em> This institution is currently <strong>Inactive</strong> and locked. Deleting it is restricted while student accounts remain enrolled.';
        }
    };
})();
</script>