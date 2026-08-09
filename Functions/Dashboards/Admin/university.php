<?php
include "../../../Includes/admin_sidebar.php";
require_once "../../../Config/db.php";
require_once "AdminBackend.php";
?>
<link rel="stylesheet" href="../../../Assets/CSS/university.css">
<?php
include "../../../Includes/dash_header.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $university = trim($_POST['university']);
    $faculty = trim($_POST['faculty']);
    $domain = trim($_POST['domain']);
    $location = trim($_POST['location']);
    $status = trim($_POST['status']);
    try {
        $result = $rudManager->get_db('emailEx', 'universityEmails where emailEx=\'' . $domain . '\'');
        if ($result->num_rows > 0) {
            $flash = ['type' => 'error', 'message' => 'Email Domain already exists'];
        } else {
            if (!empty($university) && !empty($faculty) && !empty($domain) && !empty($location) && !empty($status)) {
                $sql = "INSERT into universityemails(University,faculty,emailEx,Status,Location)Values(?,?,?,?,?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sssss", $university, $faculty, $domain, $status, $location);
                $stmt->execute();
            } else {
                $flash = ['type' => 'error', 'message' => 'All fields are required'];
            }
        }
    } catch (mysqli_sql_exception $e) {
        $flash = ['type' => 'error', 'message' => 'Error :' . $e->getMessage()];
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
                    <?php echo $dashboardManager->getTotalCount("universityemails WHERE status = 'Active'"); ?>
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
                    <?php echo $dashboardManager->getTotalCount("universityemails WHERE status = 'Pending'"); ?>
                </div>
            </div>
        </div>

        <div class="univ-stat-card">
            <div class="univ-stat-icon-wrap blue">
                <span class="material-symbols-outlined">people</span>
            </div>
            <div class="univ-stat-info">
                <div class="univ-stat-label">TOTAL STUDENTS</div>
                <div class="univ-stat-value"><?php echo $dashboardManager->getTotalCount("student") ?></div>
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
                    <div class="univ-search-box">
                        <span class="material-symbols-outlined" style="font-size:18px;color:#9ca3af;">search</span>
                        <input type="text" placeholder="Search university or domain..." id="univSearchInput"
                            autocomplete="off">
                    </div>
                    <button type="button" class="univ-icon-btn" title="Filter">
                        <span class="material-symbols-outlined" style="font-size:18px;">filter_list</span>
                    </button>
                    <button type="button" class="univ-icon-btn" title="Export CSV" id="exportBtn">
                        <span class="material-symbols-outlined" style="font-size:18px;">download</span>
                    </button>
                </div>
            </div>

            <div class="card-body" style="padding:0;">
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
                    <?php
                        $uniResult = $rudManager->get_db("*", "universityemails");
                        
                        // Pagination Setup
                        $recordsPerPage = 5;
                        $totalPages = ceil((int)$totUni / $recordsPerPage);
                        if ($totalPages < 1) $totalPages = 1;
                        $currentPage = isset($_GET['page']) ? max(1, min($totalPages, (int)$_GET['page'])) : 1;
                        $offset = ($currentPage - 1) * $recordsPerPage;
                        
                        if ((int)$totUni > 0 && isset($uniResult->num_rows) && $uniResult->num_rows > 0) {
                            $uniResult->data_seek($offset);
                        }
                        
                        $count = 0;
                        while ($count < $recordsPerPage && $row = $uniResult->fetch_assoc()):
                            $initials  = strtoupper(substr($row['University'], 0, 3));
                            $badgeCls  = match(strtolower($row['Status'] ?? '')) {
                                'active'   => 'active',
                                'pending'  => 'pending',
                                default    => 'inactive-badge'
                            };
                            $count++;
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
                            <td><?= $dashboardManager->getTotalCount("student WHERE Email LIKE '%@" . $conn->real_escape_string($row['emailEx']) . "'") ?></td>
                            <td><span class="badge-status <?= $badgeCls ?>"><?= htmlspecialchars($row['Status'] ?? '') ?></span></td>
                            <td>
                                <div class="univ-actions-cell">
                                    <button class="action-btn" type="button" title="View Details"
                                        onclick="openViewModal(
                                            '<?= htmlspecialchars(addslashes($row['University'])) ?>',
                                            '<?= htmlspecialchars(addslashes($row['emailEx'])) ?>',
                                            '<?= htmlspecialchars(addslashes($row['Location'] ?? '')) ?>',
                                            '—',
                                            '<?= htmlspecialchars(addslashes($row['Status'] ?? '')) ?>',
                                            '<?= htmlspecialchars(addslashes($row['Location'] ?? '')) ?>')">
                                        <span class="material-symbols-outlined" style="font-size:18px;">visibility</span>
                                    </button>
                                    <button class="action-btn" type="button" title="Edit">
                                        <span class="material-symbols-outlined" style="font-size:18px;">edit</span>
                                    </button>
                                    <button class="action-btn" type="button" title="Delete" style="color:#dc2626;">
                                        <span class="material-symbols-outlined" style="font-size:18px;">delete</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>

                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="univ-pagination">
                <div class="univ-pagination-controls">
                    <?php if ($currentPage <= 1): ?>
                        <span class="univ-page-btn disabled">Previous</span>
                    <?php else: ?>
                        <a href="?page=<?= $currentPage - 1 ?>" class="univ-page-btn" style="text-decoration:none; color:inherit;">Previous</a>
                    <?php endif; ?>

                    <?php for($p = 1; $p <= $totalPages; $p++): ?>
                        <?php if ($p == $currentPage): ?>
                            <span class="univ-page-btn univ-page-active"><?= $p ?></span>
                        <?php else: ?>
                            <a href="?page=<?= $p ?>" class="univ-page-btn" style="text-decoration:none; color:inherit;"><?= $p ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>

                    <?php if ($currentPage >= $totalPages): ?>
                        <span class="univ-page-btn disabled">Next</span>
                    <?php else: ?>
                        <a href="?page=<?= $currentPage + 1 ?>" class="univ-page-btn" style="text-decoration:none; color:inherit;">Next</a>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </div>
</main>

<!-- ─── Add University Modal ──────────────────────────────────────────────────── -->
<div class="univ-modal-overlay" id="addModal">
    <div class="univ-modal">
        <div class="univ-modal-header">
            <h3>Add New University</h3>
            <button class="univ-modal-close" id="closeAddModal" type="button">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <form class="univ-modal-body" action="" method="post">
            <div class="form-group">
                <label class="form-label">University Name <span style="color:#ef4444;">*</span></label>
                <input type="text" class="form-input" name="university" placeholder="e.g. University of Colombo">
            </div>
            <div class="form-group">
                <label class="form-label">Faculty Name <span style="color:#ef4444;">*</span></label>
                <input type="text" class="form-input" name="faculty" placeholder="e.g. School Of Technology">
            </div>
            <div class="form-group">
                <label class="form-label">Email Domain <span style="color:#ef4444;">*</span></label>
                <input type="text" name="domain" class="form-input" placeholder="e.g. cmb.ac.lk">
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

<!-- ─── View University Modal ────────────────────────────────────────────────── -->
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

<footer class="footer">
    <div>&copy; 2026 SkillBridge. All rights reserved.</div>
    <div class="footer-links">
        <a href="#">Help Center</a>
        <a href="#">Privacy Policy</a>
        <a href="#">Terms of Service</a>
    </div>
</footer>

<?php include "../../../Includes/dash_footer.php"; ?>
<script src="../../../Assets/JS/university.js"></script>