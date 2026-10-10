<?php
require_once "../../Config/db.php";
require_once "../../Session/Session.php";
require_login();
require_role('admin');

require_once __DIR__ . "/../../Backend/Admin/User_Management.php";

include "../../Includes/admin_sidebar.php";
?>
<link rel="stylesheet" href="../../Assets/CSS/Admin/usermanagement.css">
<?php
include "../../Includes/dash_header.php";
?>

<main class="content">

    <!-- Flash Notification Component -->
    <?php include __DIR__ . "/Components/FlashToast.php"; ?>
    <!-- EndHere -->

    <!-- Page Header -->
    <div class="user-page-header">
        <div class="user-breadcrumb">
            <a href="dashboard.php">Admin</a>
            <span class="material-symbols-outlined" style="font-size:14px;color:#9ca3af;">chevron_right</span>
            <span>User Management</span>
        </div>
        <div class="user-title-row">
            <div>
                <h1 class="user-title">User Management</h1>
                <p class="user-subtitle">
                    Oversee, manage, and configure all user accounts across students, universities, partnering companies, and system administrators.
                </p>
            </div>
            <div class="user-header-actions" style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
                <!--<button type="button" class="btn-bulk-user" id="btnBulkUpload">
                    <span class="material-symbols-outlined" style="font-size:18px;">upload_file</span>
                    Bulk Import Students
                </button>-->
                <button class="btn-add-user" id="btnAddUser">
                    <span class="material-symbols-outlined" style="font-size:18px;">person_add</span>
                    Add New User
                </button>
            </div>
        </div>
    </div>

    <!-- UserManagement  Cards -->
    <div class="user-stats-grid">
        <?php
            $CardDetails = [
                "cardIcons"  => ["group", "how_to_reg", "school", "domain"],
                "iconColors" => ["navy", "green", "blue", "purple"],
                "cardLabels" => ["Total Users", "Active Users", "Students", "Companies & Orgs"],
                "cardValues" => [
                    number_format($stats['total']),
                    number_format($stats['active']),
                    number_format($stats['students']),
                    number_format($stats['companies'] + $stats['organizations'])
                ]
            ];
            include __DIR__ . "/Components/Card.php";
        ?>
    </div>
    <!-- EndHere -->

    <!-- Main Content Card -->
    <div class="full-width-section" style="padding: 0 28px 40px;">
        <div class="card" style="border: 1px solid #e5e7eb; border-radius: 14px; background: #fff; overflow: hidden;">
            
            <!-- Filters Toolbar -->
            <div class="user-filter-bar">
                <!-- Role Tabs -->
                <div class="user-role-tabs">
                    <a href="?role=all&status=<?= urlencode($selectedStatus) ?>&search=<?= urlencode($searchQuery) ?>" 
                       class="user-role-tab <?= $selectedRole === 'all' ? 'active' : '' ?>">
                        All Users <span class="tab-badge"><?= $stats['total'] ?></span>
                    </a>
                    <a href="?role=student&status=<?= urlencode($selectedStatus) ?>&search=<?= urlencode($searchQuery) ?>" 
                       class="user-role-tab <?= $selectedRole === 'student' ? 'active' : '' ?>">
                        Students <span class="tab-badge"><?= $stats['students'] ?></span>
                    </a>
                    <a href="?role=company&status=<?= urlencode($selectedStatus) ?>&search=<?= urlencode($searchQuery) ?>" 
                       class="user-role-tab <?= $selectedRole === 'company' ? 'active' : '' ?>">
                        Companies <span class="tab-badge"><?= $stats['companies'] ?></span>
                    </a>
                    <a href="?role=organization&status=<?= urlencode($selectedStatus) ?>&search=<?= urlencode($searchQuery) ?>" 
                       class="user-role-tab <?= $selectedRole === 'organization' ? 'active' : '' ?>">
                        Organizations <span class="tab-badge"><?= $stats['organizations'] ?></span>
                    </a>
                    <a href="?role=admin&status=<?= urlencode($selectedStatus) ?>&search=<?= urlencode($searchQuery) ?>" 
                       class="user-role-tab <?= $selectedRole === 'admin' ? 'active' : '' ?>">
                        Admins <span class="tab-badge"><?= $stats['admins'] ?></span>
                    </a>
                </div>

                <!-- Right search & dropdown -->
                <div class="user-toolbar-right">
                    <form method="GET" action="" style="display:flex; gap:10px; align-items:center;">
                        <input type="hidden" name="role" value="<?= htmlspecialchars($selectedRole) ?>">
                        
                        <select name="status" class="user-select-filter" onchange="this.form.submit()">
                            <option value="all" <?= $selectedStatus === 'all' ? 'selected' : '' ?>>All Statuses</option>
                            <option value="Active" <?= $selectedStatus === 'Active' ? 'selected' : '' ?>>Active</option>
                            <option value="De-Active" <?= $selectedStatus === 'De-Active' ? 'selected' : '' ?>>De-Active</option>
                            <option value="Pending" <?= $selectedStatus === 'Pending' ? 'selected' : '' ?>>Pending</option>
                        </select>

                        <div class="user-search-box">
                            <span class="material-symbols-outlined">search</span>
                            <input type="text" name="search" placeholder="Search name, email, org..." value="<?= htmlspecialchars($searchQuery) ?>">
                        </div>
                    </form>
                </div>
            </div>

            <!-- Users Table -->
            <?php require_once "Components/UserTable.php";?>
            <!-- EndHere -->

            <!-- Pagination Footer (Shows only if more than 5 users exist) -->
            <?php if ($totalPages > 1): ?>
            <div class="user-pagination">
                <div class="user-pagination-info">
                    Showing <?= $offset + 1 ?>–<?= min($offset + $perPage, $totalRecords) ?> of <?= $totalRecords ?> users
                </div>
                <div class="user-pagination-controls">
                    <?php if ($currentPageNum > 1): ?>
                        <a href="?role=<?= urlencode($selectedRole) ?>&status=<?= urlencode($selectedStatus) ?>&search=<?= urlencode($searchQuery) ?>&page=<?= $currentPageNum - 1 ?>" class="user-page-btn">Previous</a>
                    <?php else: ?>
                        <span class="user-page-btn disabled">Previous</span>
                    <?php endif; ?>

                    <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                        <a href="?role=<?= urlencode($selectedRole) ?>&status=<?= urlencode($selectedStatus) ?>&search=<?= urlencode($searchQuery) ?>&page=<?= $p ?>" 
                           class="user-page-btn <?= $p === $currentPageNum ? 'active' : '' ?>">
                            <?= $p ?>
                        </a>
                    <?php endfor; ?>

                    <?php if ($currentPageNum < $totalPages): ?>
                        <a href="?role=<?= urlencode($selectedRole) ?>&status=<?= urlencode($selectedStatus) ?>&search=<?= urlencode($searchQuery) ?>&page=<?= $currentPageNum + 1 ?>" class="user-page-btn">Next</a>
                    <?php else: ?>
                        <span class="user-page-btn disabled">Next</span>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

        </div>
    </div>

</main>

<!-- MODAL: ADD NEW USER -->
<?php include_once 'Components/UserModels.php'?>
<!-- EndHere -->

<script>
    window.dbUniversities = <?= json_encode($dbUniversities, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    window.activeInstitutions = <?= json_encode($activeInstitutions, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
</script>
<script src="../../Assets/JS/Admin/usermanagement.js"></script>

<footer class="footer">
    <div>&copy; 2026 SkillBridge. All rights reserved.</div>
    <div class="footer-links">
        <a href="/Skill_Bridge_Group_Project/help_center.php">Help Center</a>
        <a href="#">Privacy Policy</a>
        <a href="#">Terms of Service</a>
    </div>
</footer>
<?php include "../../Includes/dash_footer.php"; ?>
