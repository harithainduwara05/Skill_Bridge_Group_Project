<?php
require_once "../../../Config/db.php";
require_once "../../../Session/Session.php";
require_login();
require_role('admin');

include "../../../Includes/admin_sidebar.php";
require_once "AdminBackend.php";
?>
<link rel="stylesheet" href="../../../Assets/CSS/Admin/university.css">
<?php
include "../../../Includes/dash_header.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? 'add';

    if ($action === 'add') {
        $university = trim($_POST['university']);
        $faculty = trim($_POST['faculty']);
        $domain = trim($_POST['domain']);
        $location = trim($_POST['location']);
        $status = trim($_POST['status']);
        try {
            if ($adminDB->domainExists($domain)) {
                $flash = ['type' => 'error', 'message' => 'Email Domain already exists'];
            } elseif (!empty($university) && !empty($faculty) && !empty($domain) && !empty($location) && !empty($status)) {
                $adminDB->addUniversity($university, $faculty, $domain, $status, $location);
                $flash = ['type' => 'success', 'message' => 'University added successfully'];
            } else {
                $flash = ['type' => 'error', 'message' => 'All fields are required'];
            }
        } catch (mysqli_sql_exception $e) {
            $flash = ['type' => 'error', 'message' => 'Error: ' . $e->getMessage()];
        }
    } elseif ($action === 'edit') {
        $origDomain = trim($_POST['original_domain']);
        $university = trim($_POST['university']);
        $faculty = trim($_POST['faculty']);
        $domain = trim($_POST['domain']);
        $location = trim($_POST['location']);
        $status = trim($_POST['status']);
        try {
            $adminDB->updateUniversity($university, $faculty, $domain, $status, $location, $origDomain);
            $flash = ['type' => 'success', 'message' => 'University updated successfully'];
        } catch (mysqli_sql_exception $e) {
            $flash = ['type' => 'error', 'message' => 'Error: ' . $e->getMessage()];
        }
    } elseif ($action === 'delete') {
        $domainToDelete = trim($_POST['delete_domain']);
        try {
            $adminDB->deleteUniversity($domainToDelete);
            $flash = ['type' => 'success', 'message' => 'University deleted successfully'];
        } catch (mysqli_sql_exception $e) {
            $flash = ['type' => 'error', 'message' => 'Error: ' . $e->getMessage()];
        }
    }
}


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
                <div class="univ-stat-value"><?php echo $totUni ?></div>
            </div>
        </div>

        <div class="univ-stat-card">
            <div class="univ-stat-icon-wrap green">
                <span class="material-symbols-outlined">check_circle</span>
            </div>
            <div class="univ-stat-info">
                <div class="univ-stat-label">ACTIVE DOMAINS</div>
                <div class="univ-stat-value">
                    <?php echo $adminDB->getCountWhere("universityemails", "status", "Active"); ?>
                </div>
            </div>
        </div>

        <div class="univ-stat-card">
            <div class="univ-stat-icon-wrap orange">
                <span class="material-symbols-outlined">schedule</span>
            </div>
            <div class="univ-stat-info">
                <div class="univ-stat-label">PENDING APPROVAL</div>
                <div class="univ-stat-value">
                    <?php echo $adminDB->getCountWhere("universityemails", "status", "Pending"); ?>
                </div>
            </div>
        </div>

        <div class="univ-stat-card">
            <div class="univ-stat-icon-wrap blue">
                <span class="material-symbols-outlined">people</span>
            </div>
            <div class="univ-stat-info">
                <div class="univ-stat-label">TOTAL STUDENTS</div>
                <div class="univ-stat-value"><?php echo $adminDB->getCount("student") ?></div>
            </div>
        </div>

    </div>

    <!-- Institution List Table -->
    <div class="full-width-section">
        <div class="card">
            <?php
                $selectedStatus = $_GET['status'] ?? 'all';
                $searchQuery    = trim($_GET['search'] ?? '');
                
                $uniResult = $adminDB->getAllUniversities($selectedStatus, $searchQuery);
                $allUniversities = [];
                if ($uniResult) {
                    while ($r = $uniResult->fetch_assoc()) {
                        $allUniversities[] = $r;
                    }
                }
                $totFilteredUni = count($allUniversities);
                
                // Pagination Setup (5 records per page)
                $recordsPerPage = 5;
                $totalPages = ceil($totFilteredUni / $recordsPerPage);
                $currentPage = isset($_GET['page']) ? max(1, min(max(1, $totalPages), (int)$_GET['page'])) : 1;
                $offset = ($currentPage - 1) * $recordsPerPage;
                $pageUniversities = array_slice($allUniversities, $offset, $recordsPerPage);
            ?>
            <div class="card-header">
                <div>
                    <h3>Institution List</h3>
                </div>
                <div class="univ-table-actions">
                    <form method="GET" action="" style="display:flex; gap:10px; align-items:center;">
                        <select name="status" class="univ-select-filter" onchange="this.form.submit()">
                            <option value="all" <?= $selectedStatus === 'all' ? 'selected' : '' ?>>All Statuses</option>
                            <option value="Active" <?= $selectedStatus === 'Active' ? 'selected' : '' ?>>Active</option>
                            <option value="Pending" <?= $selectedStatus === 'Pending' ? 'selected' : '' ?>>Pending</option>
                            <option value="Inactive" <?= $selectedStatus === 'Inactive' ? 'selected' : '' ?>>Inactive</option>
                        </select>

                        <div class="univ-search-box">
                            <span class="material-symbols-outlined" style="font-size:18px;color:#9ca3af;">search</span>
                            <input type="text" name="search" placeholder="Search name, email, org..." id="univSearchInput"
                                value="<?= htmlspecialchars($searchQuery) ?>" autocomplete="off">
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
                                <span class="material-symbols-outlined" style="font-size: 40px; color: #cbd5e1; display:block; margin-bottom:8px;">search_off</span>
                                No universities found matching your criteria.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php
                            foreach ($pageUniversities as $row):
                                $initials  = strtoupper(substr($row['University'], 0, 3));
                                $badgeCls  = match(strtolower($row['Status'] ?? '')) {
                                    'active'   => 'active',
                                    'pending'  => 'pending',
                                    default    => 'inactive-badge'
                                };
                        ?>
                        <tr>
                            <td>
                                <div class="university-logo mit" style="background:#1e293b;"><?= htmlspecialchars($initials) ?></div>
                            </td>
                            <td>
                                <div class="university-name"><?= htmlspecialchars($row['University']) ?></div>
                                <div class="univ-sub-location"><?= htmlspecialchars($row['Location'] ?? '') ?></div>
                            </td>
                            <td><span class="univ-domain-badge">@<?= htmlspecialchars($row['emailEx']) ?></span></td>
                            <td><?= htmlspecialchars($row['Location'] ?? '—') ?></td>
                            <?php $stuCount = $adminDB->getStudentCountByDomain($row['emailEx']); ?>
                            <td><?= $stuCount ?></td>
                            <td><span class="badge-status <?= $badgeCls ?>"><?= htmlspecialchars($row['Status'] ?? '') ?></span></td>
                            <td>
                                <div class="univ-actions-cell">
                                    <button class="action-btn" type="button" title="View Details"
                                        onclick="openViewModal(
                                            '<?= htmlspecialchars(addslashes($row['University'])) ?>',
                                            '<?= htmlspecialchars(addslashes($row['emailEx'])) ?>',
                                            '<?= htmlspecialchars(addslashes($row['Location'] ?? '')) ?>',
                                            '<?= $stuCount ?>',
                                            '<?= htmlspecialchars(addslashes($row['Status'] ?? '')) ?>',
                                            '<?= htmlspecialchars(addslashes($row['Location'] ?? '')) ?>')">
                                        <span class="material-symbols-outlined" style="font-size:18px;">visibility</span>
                                    </button>
                                    <button class="action-btn" type="button" title="Edit"
                                        onclick="openEditModal(
                                            '<?= htmlspecialchars(addslashes($row['University'])) ?>',
                                            '<?= htmlspecialchars(addslashes($row['faculty'] ?? '')) ?>',
                                            '<?= htmlspecialchars(addslashes($row['emailEx'])) ?>',
                                            '<?= htmlspecialchars(addslashes($row['Location'] ?? '')) ?>',
                                            '<?= htmlspecialchars(addslashes($row['Status'] ?? '')) ?>')">
                                        <span class="material-symbols-outlined" style="font-size:18px;">edit</span>
                                    </button>
                                    <button class="action-btn" type="button" title="Delete" style="color:#dc2626;"
                                        onclick="openDeleteModal('<?= htmlspecialchars(addslashes($row['emailEx'])) ?>', '<?= htmlspecialchars(addslashes($row['University'])) ?>')">
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
                    Showing <?= $offset + 1 ?>–<?= min($offset + $recordsPerPage, $totFilteredUni) ?> of <?= $totFilteredUni ?> universities
                </div>
                <div class="univ-pagination-controls">
                    <?php if ($currentPage <= 1): ?>
                        <span class="univ-page-btn disabled">Previous</span>
                    <?php else: ?>
                        <a href="?status=<?= urlencode($selectedStatus) ?>&search=<?= urlencode($searchQuery) ?>&page=<?= $currentPage - 1 ?>" class="univ-page-btn" style="text-decoration:none; color:inherit;">Previous</a>
                    <?php endif; ?>

                    <?php for($p = 1; $p <= $totalPages; $p++): ?>
                        <?php if ($p == $currentPage): ?>
                            <span class="univ-page-btn univ-page-active"><?= $p ?></span>
                        <?php else: ?>
                            <a href="?status=<?= urlencode($selectedStatus) ?>&search=<?= urlencode($searchQuery) ?>&page=<?= $p ?>" class="univ-page-btn" style="text-decoration:none; color:inherit;"><?= $p ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>

                    <?php if ($currentPage >= $totalPages): ?>
                        <span class="univ-page-btn disabled">Next</span>
                    <?php else: ?>
                        <a href="?status=<?= urlencode($selectedStatus) ?>&search=<?= urlencode($searchQuery) ?>&page=<?= $currentPage + 1 ?>" class="univ-page-btn" style="text-decoration:none; color:inherit;">Next</a>
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
                <input type="text" class="form-input" name="university" placeholder="e.g. University of Colombo" required>
            </div>
            <div class="form-group">
                <label class="form-label">Faculty Name <span style="color:#ef4444;">*</span></label>
                <input type="text" class="form-input" name="faculty" placeholder="e.g. School Of Technology" required>
            </div>
            <div class="form-group">
                <label class="form-label">Email Domain <span style="color:#ef4444;">*</span></label>
                <input type="text" name="domain" class="form-input" placeholder="e.g. cmb.ac.lk" required>
                <small style="color:#9ca3af;font-size:11px;margin-top:4px;display:block;">Enter without the @
                    symbol</small>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Location</label>
                    <input type="text" name="location" class="form-input" placeholder="e.g. Colombo, Sri Lanka">
                </div>
                <div class="form-group">
                    <label class="form-label">Status</label>
                    <select class="form-input" name="status">
                        <option value="Active">Active</option>
                        <option value="Pending">Pending</option>
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
                <input type="text" class="form-input" name="university" id="editUni" required>
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
                        <option value="Pending">Pending</option>
                        <option value="Inactive">Inactive</option>
                    </select>
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
        <div class="univ-modal-header">
            <h3 style="color:#dc2626; display:flex; align-items:center; gap:8px;">
                <span class="material-symbols-outlined" style="font-size:20px;">delete</span>
                Delete University
            </h3>
            <button class="univ-modal-close" id="closeDeleteModal" type="button">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <form action="" method="post">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="delete_domain" id="deleteDomainInput">
            
            <div class="univ-modal-body" style="padding: 22px 24px;">
                <div style="display:flex; gap:16px; align-items:flex-start;">
                    <div style="width:44px; height:44px; border-radius:50%; background:#fee2e2; color:#dc2626; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                        <span class="material-symbols-outlined" style="font-size:24px;">warning</span>
                    </div>
                    <div style="flex:1;">
                        <h4 style="font-size: 14.5px; font-weight:700; color:#0f172a; margin:0 0 6px 0;">
                            Are you sure you want to delete this university?
                        </h4>
                        <p style="font-size: 13px; color:#64748b; margin:0 0 14px 0; line-height:1.5;">
                            This action cannot be undone. All student domain associations will be removed.
                        </p>

                        <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:10px; padding:12px 14px;">
                            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px; font-size:12.5px;">
                                <span style="color:#64748b; font-weight:600;">University:</span>
                                <strong id="deleteUniDisplay" style="color:#0f172a; font-weight:600;"></strong>
                            </div>
                            <div style="display:flex; justify-content:space-between; align-items:center; font-size:12.5px;">
                                <span style="color:#64748b; font-weight:600;">Domain:</span>
                                <strong id="deleteDomainDisplay" style="color:#2563eb; font-weight:600; font-family:'Courier New', monospace;"></strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="univ-modal-footer">
                <button type="button" class="univ-btn-cancel" id="cancelDeleteModal">Cancel</button>
                <button type="submit" class="univ-btn-delete">
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
        <a href="#">Help Center</a>
        <a href="#">Privacy Policy</a>
        <a href="#">Terms of Service</a>
    </div>
</footer>

<?php include "../../../Includes/dash_footer.php"; ?>
<script src="../../../Assets/JS/Admin/university.js"></script>