<?php
require_once "../../../Config/db.php";
require_once "../../../Session/Session.php";
require_login();
require_role('admin');

$loggedInUser = current_user();
$currentAdminEmail = strtolower($loggedInUser['Email'] ?? $loggedInUser['email'] ?? '');

include "../../../Includes/admin_sidebar.php";
require_once "AdminBackend.php";
?>
<link rel="stylesheet" href="../../../Assets/CSS/Admin/usermanagement.css">
<?php
include "../../../Includes/dash_header.php";

$flash = null;

// ============================================
// HANDLE POST ACTIONS
// ============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // 1. ADD NEW USER
    if ($action === 'add') {
        $name     = trim($_POST['name'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $password = trim($_POST['password'] ?? '');
        $role     = trim($_POST['role'] ?? 'student');
        $status   = trim($_POST['status'] ?? 'Active');
        $orgName  = trim($_POST['organization_name'] ?? '');
        $contact  = trim($_POST['contact_number'] ?? '');
        $degree   = trim($_POST['degree'] ?? '');
        $year     = trim($_POST['academic_year'] ?? '');

        if (empty($name) || empty($email) || empty($password) || empty($role)) {
            $flash = ['type' => 'error', 'message' => 'Name, Email, Password, and Role are required fields.'];
        } elseif ($adminDB->userExists($email)) {
            $flash = ['type' => 'error', 'message' => 'A user with this email address already exists!'];
        } else {
            try {
                $ok = $adminDB->addUser($email, $password, $role, $name, $status, $orgName, $contact, $degree, $year);
                if ($ok) {
                    $flash = ['type' => 'success', 'message' => 'User created successfully!'];
                } else {
                    $flash = ['type' => 'error', 'message' => 'Failed to create user. Please try again.'];
                }
            } catch (Exception $e) {
                $flash = ['type' => 'error', 'message' => 'Error: ' . $e->getMessage()];
            }
        }
    }

    // 2. EDIT USER
    elseif ($action === 'edit') {
        $email   = trim($_POST['email'] ?? '');
        $name    = trim($_POST['name'] ?? '');
        $role    = trim($_POST['role'] ?? 'student');
        $status  = trim($_POST['status'] ?? 'Active');
        $orgName = trim($_POST['organization_name'] ?? '');
        $contact = trim($_POST['contact_number'] ?? '');

        if (empty($email) || empty($name)) {
            $flash = ['type' => 'error', 'message' => 'Name and Email are required.'];
        } else {
            // If editing self, preserve admin role and active status
            if (strtolower($email) === $currentAdminEmail) {
                $role = 'admin';
                $status = 'Active';
            }
            try {
                $adminDB->updateUser($email, $name, $role, $status, $orgName, $contact);
                $flash = ['type' => 'success', 'message' => 'User updated successfully!'];
            } catch (Exception $e) {
                $flash = ['type' => 'error', 'message' => 'Error: ' . $e->getMessage()];
            }
        }
    }

    // 3. TOGGLE STATUS
    elseif ($action === 'toggle_status') {
        $email     = trim($_POST['email'] ?? '');
        $newStatus = trim($_POST['status'] ?? 'Active');
        if (strtolower($email) === $currentAdminEmail) {
            $flash = ['type' => 'error', 'message' => 'You cannot deactivate your own admin account!'];
        } elseif (!empty($email)) {
            try {
                $adminDB->updateUserStatus($email, $newStatus);
                $flash = ['type' => 'success', 'message' => 'User status updated to ' . htmlspecialchars($newStatus) . '!'];
            } catch (Exception $e) {
                $flash = ['type' => 'error', 'message' => 'Error: ' . $e->getMessage()];
            }
        }
    }

    // 4. DELETE USER
    elseif ($action === 'delete') {
        $emailToDelete = trim($_POST['delete_email'] ?? '');
        if (strtolower($emailToDelete) === $currentAdminEmail) {
            $flash = ['type' => 'error', 'message' => 'You cannot delete your own admin account!'];
        } elseif (!empty($emailToDelete)) {
            try {
                $adminDB->deleteUser($emailToDelete);
                $flash = ['type' => 'success', 'message' => 'User account and data deleted successfully!'];
            } catch (Exception $e) {
                $flash = ['type' => 'error', 'message' => 'Error: ' . $e->getMessage()];
            }
        }
    }
}

// ============================================
// FILTERS & STATS
// ============================================
$stats = $adminDB->getUserManagementStats();

$selectedRole   = $_GET['role'] ?? 'all';
$selectedStatus = $_GET['status'] ?? 'all';
$searchQuery    = trim($_GET['search'] ?? '');

$allFilteredUsers = $adminDB->getAllUsersDetailed($selectedRole, $selectedStatus, $searchQuery);
$totalRecords = count($allFilteredUsers);

// Pagination (Only 5 users per page)
$perPage = 5;
$currentPageNum = max(1, intval($_GET['page'] ?? 1));
$totalPages = ceil($totalRecords / $perPage);
if ($currentPageNum > $totalPages && $totalPages > 0) {
    $currentPageNum = $totalPages;
}
$offset = ($currentPageNum - 1) * $perPage;
$usersList = array_slice($allFilteredUsers, $offset, $perPage);
?>

<main class="content">

    <!-- Flash Notification -->
    <?php if (!empty($flash)): ?>
    <div class="flash-toast flash-<?= htmlspecialchars($flash['type']) ?>" id="flashToast">
        <div class="flash-icon-wrap">
            <span class="material-symbols-outlined">
                <?= $flash['type'] === 'error' ? 'error' : 'check_circle' ?>
            </span>
        </div>
        <div class="flash-content">
            <div class="flash-title"><?= $flash['type'] === 'error' ? 'Action Failed' : 'Success!' ?></div>
            <div class="flash-msg"><?= htmlspecialchars($flash['message']) ?></div>
        </div>
        <button class="flash-close" onclick="closeToast()" title="Dismiss" type="button">
            <span class="material-symbols-outlined" style="font-size:18px;">close</span>
        </button>
        <div class="flash-progress"></div>
    </div>
    <?php endif; ?>

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
            <button class="btn-add-user" id="btnAddUser">
                <span class="material-symbols-outlined" style="font-size:18px;">person_add</span>
                Add New User
            </button>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="user-stats-grid">
        <div class="user-stat-card">
            <div class="user-stat-icon-wrap navy">
                <span class="material-symbols-outlined">group</span>
            </div>
            <div class="user-stat-info">
                <div class="user-stat-label">Total Users</div>
                <div class="user-stat-value"><?= number_format($stats['total']) ?></div>
            </div>
        </div>

        <div class="user-stat-card">
            <div class="user-stat-icon-wrap green">
                <span class="material-symbols-outlined">how_to_reg</span>
            </div>
            <div class="user-stat-info">
                <div class="user-stat-label">Active Users</div>
                <div class="user-stat-value"><?= number_format($stats['active']) ?></div>
            </div>
        </div>

        <div class="user-stat-card">
            <div class="user-stat-icon-wrap blue">
                <span class="material-symbols-outlined">school</span>
            </div>
            <div class="user-stat-info">
                <div class="user-stat-label">Students</div>
                <div class="user-stat-value"><?= number_format($stats['students']) ?></div>
            </div>
        </div>

        <div class="user-stat-card">
            <div class="user-stat-icon-wrap purple">
                <span class="material-symbols-outlined">domain</span>
            </div>
            <div class="user-stat-info">
                <div class="user-stat-label">Companies & Orgs</div>
                <div class="user-stat-value"><?= number_format($stats['companies'] + $stats['organizations']) ?></div>
            </div>
        </div>
    </div>

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
            <div class="card-body" style="padding: 0; overflow-x: auto;">
                <table class="data-table" style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background: #f8fafc; border-bottom: 1px solid #e5e7eb; text-align: left;">
                            <th style="padding: 12px 20px; font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase;">User</th>
                            <th style="padding: 12px 18px; font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase;">Role</th>
                            <th style="padding: 12px 18px; font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase;">Affiliation / Organization</th>
                            <th style="padding: 12px 18px; font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase;">Status</th>
                            <th style="padding: 12px 18px; font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase;">Joined Date</th>
                            <th style="padding: 12px 20px; font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; text-align: right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($usersList)): ?>
                            <tr>
                                <td colspan="6" style="text-align: center; padding: 40px; color: #94a3b8;">
                                    <span class="material-symbols-outlined" style="font-size: 40px; color: #cbd5e1; display:block; margin-bottom:8px;">person_search</span>
                                    No users found matching your criteria.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($usersList as $u): 
                                $userRole = strtolower($u['role']);
                                $initial = strtoupper(substr($u['user_name'], 0, 1));
                                $statusClass = strtolower($u['status'] ?? 'active');
                                if ($statusClass === 'de-active') $statusClass = 'deactive';
                                $joinedDate = !empty($u['created_at']) ? date('M d, Y', strtotime($u['created_at'])) : 'N/A';
                                $isSelf = ($currentAdminEmail !== '' && strtolower($u['email']) === $currentAdminEmail);
                            ?>
                            <tr style="border-bottom: 1px solid #f1f5f9; transition: background .15s; <?= $isSelf ? 'background: #fafcff;' : '' ?>">
                                <td style="padding: 14px 20px;">
                                    <div class="user-cell-meta">
                                        <div class="user-avatar-circle <?= $userRole ?>">
                                            <?= $initial ?>
                                        </div>
                                        <div>
                                            <div class="user-name-text">
                                                <?= htmlspecialchars($u['user_name']) ?>
                                                <?php if ($isSelf): ?>
                                                    <span style="font-size:11px; font-weight:700; color:#2563eb; background:#eff6ff; border:1px solid #bfdbfe; padding:1px 6px; border-radius:10px; margin-left:4px;">You</span>
                                                <?php endif; ?>
                                            </div>
                                            <div class="user-email-text"><?= htmlspecialchars($u['email']) ?></div>
                                        </div>
                                    </div>
                                </td>

                                <td style="padding: 14px 18px;">
                                    <span class="badge-role <?= $userRole ?>">
                                        <?= htmlspecialchars(ucfirst($u['role'])) ?>
                                    </span>
                                </td>

                                <td style="padding: 14px 18px; font-size: 13px; color: #334155;">
                                    <?= htmlspecialchars($u['organization_name'] ?: 'N/A') ?>
                                </td>

                                <td style="padding: 14px 18px;">
                                    <span class="badge-status-pill <?= $statusClass ?>">
                                        <?= htmlspecialchars(ucfirst($u['status'] ?: 'Active')) ?>
                                    </span>
                                </td>

                                <td style="padding: 14px 18px; font-size: 12.5px; color: #64748b;">
                                    <?= htmlspecialchars($joinedDate) ?>
                                </td>

                                <td style="padding: 14px 20px; text-align: right;">
                                    <div class="user-actions-cell" style="justify-content: flex-end;">
                                        <!-- View Details -->
                                        <button class="btn-table-action view" title="View Details" onclick="viewUser(<?= htmlspecialchars(json_encode($u), ENT_QUOTES, 'UTF-8') ?>)">
                                            <span class="material-symbols-outlined" style="font-size: 17px;">visibility</span>
                                        </button>
                                        
                                        <!-- Edit User -->
                                        <button class="btn-table-action edit" title="Edit User" onclick="editUser(<?= htmlspecialchars(json_encode($u), ENT_QUOTES, 'UTF-8') ?>, <?= $isSelf ? 'true' : 'false' ?>)">
                                            <span class="material-symbols-outlined" style="font-size: 17px;">edit</span>
                                        </button>

                                        <?php if ($isSelf): ?>
                                            <!-- Disabled Status Toggle for Current Logged-in Admin -->
                                            <button type="button" class="btn-table-action" disabled 
                                                    title="You cannot deactivate your own account" 
                                                    style="color: #94a3b8; background: #f1f5f9; border-color: #e2e8f0; cursor: not-allowed; opacity: 0.5;">
                                                <span class="material-symbols-outlined" style="font-size: 17px;">block</span>
                                            </button>

                                            <!-- Disabled Delete for Current Logged-in Admin -->
                                            <button type="button" class="btn-table-action" disabled 
                                                    title="You cannot delete your own account" 
                                                    style="color: #94a3b8; background: #f1f5f9; border-color: #e2e8f0; cursor: not-allowed; opacity: 0.5;">
                                                <span class="material-symbols-outlined" style="font-size: 17px;">delete</span>
                                            </button>
                                        <?php else: ?>
                                            <!-- Toggle Status Modal Trigger -->
                                            <button type="button" class="btn-table-action" 
                                                    title="<?= $u['status'] === 'Active' ? 'Deactivate User' : 'Activate User' ?>" 
                                                    style="<?= $u['status'] === 'Active' ? 'color:#ef4444;' : 'color:#10b981;' ?>"
                                                    onclick="openStatusModal('<?= htmlspecialchars($u['email'], ENT_QUOTES) ?>', '<?= htmlspecialchars($u['user_name'], ENT_QUOTES) ?>', '<?= $u['status'] === 'Active' ? 'De-Active' : 'Active' ?>')">
                                                <span class="material-symbols-outlined" style="font-size: 17px;">
                                                    <?= $u['status'] === 'Active' ? 'block' : 'check_circle' ?>
                                                </span>
                                            </button>

                                            <!-- Delete User -->
                                            <button class="btn-table-action delete" title="Delete User" onclick="openDeleteModal('<?= htmlspecialchars($u['email'], ENT_QUOTES) ?>', '<?= htmlspecialchars($u['user_name'], ENT_QUOTES) ?>')">
                                                <span class="material-symbols-outlined" style="font-size: 17px;">delete</span>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

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

<!-- ============================================
     MODAL: ADD NEW USER
     ============================================ -->
<div class="user-modal-overlay" id="addUserModal">
    <div class="user-modal">
        <div class="user-modal-header">
            <h3>Add New User</h3>
            <button class="user-modal-close" onclick="closeModal('addUserModal')">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <form method="POST" action="">
            <input type="hidden" name="action" value="add">
            <div class="user-modal-body">
                <div class="user-form-group">
                    <label>Full Name / Contact Person *</label>
                    <input type="text" name="name" required placeholder="e.g. John Doe">
                </div>

                <div class="user-form-row">
                    <div class="user-form-group">
                        <label>Email Address *</label>
                        <input type="email" name="email" required placeholder="user@example.com">
                    </div>
                    <div class="user-form-group">
                        <label>Initial Password *</label>
                        <input type="password" name="password" required placeholder="••••••••">
                    </div>
                </div>

                <div class="user-form-row">
                    <div class="user-form-group">
                        <label>Role *</label>
                        <select name="role" id="addRoleSelect" onchange="toggleAddFields(this.value)">
                            <option value="student">Student</option>
                            <option value="company">Company</option>
                            <option value="organization">Organization</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                    <div class="user-form-group">
                        <label>Status</label>
                        <select name="status">
                            <option value="Active">Active</option>
                            <option value="De-Active">De-Active</option>
                            <option value="Pending">Pending</option>
                        </select>
                    </div>
                </div>

                <div class="user-form-group" id="addOrgNameGroup">
                    <label id="addOrgLabel">University / Institution Name</label>
                    <input type="text" name="organization_name" placeholder="e.g. University of Colombo">
                </div>

                <div class="user-form-row" id="addStudentExtraFields">
                    <div class="user-form-group">
                        <label>Degree / Program</label>
                        <input type="text" name="degree" placeholder="e.g. Computer Science">
                    </div>
                    <div class="user-form-group">
                        <label>Academic Year</label>
                        <input type="text" name="academic_year" placeholder="e.g. 2024">
                    </div>
                </div>

                <div class="user-form-group" id="addContactGroup" style="display:none;">
                    <label>Contact Number</label>
                    <input type="text" name="contact_number" placeholder="e.g. +94 77 123 4567">
                </div>
            </div>
            <div class="user-modal-footer">
                <button type="button" class="btn-secondary" onclick="closeModal('addUserModal')">Cancel</button>
                <button type="submit" class="btn-primary">Create User</button>
            </div>
        </form>
    </div>
</div>

<!-- ============================================
     MODAL: EDIT USER
     ============================================ -->
<div class="user-modal-overlay" id="editUserModal">
    <div class="user-modal">
        <div class="user-modal-header">
            <h3>Edit User Account</h3>
            <button class="user-modal-close" onclick="closeModal('editUserModal')">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <form method="POST" action="">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="email" id="editEmail">
            
            <div class="user-modal-body">
                <div class="user-form-group">
                    <label>Email Address (Read-only)</label>
                    <input type="text" id="editEmailDisplay" disabled style="background:#f1f5f9; cursor:not-allowed;">
                </div>

                <div class="user-form-group">
                    <label>Full Name / Contact Person *</label>
                    <input type="text" name="name" id="editName" required>
                </div>

                <div class="user-form-row">
                    <div class="user-form-group">
                        <label>Role</label>
                        <select name="role" id="editRole">
                            <option value="student">Student</option>
                            <option value="company">Company</option>
                            <option value="organization">Organization</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                    <div class="user-form-group">
                        <label>Status</label>
                        <select name="status" id="editStatus">
                            <option value="Active">Active</option>
                            <option value="De-Active">De-Active</option>
                            <option value="Pending">Pending</option>
                        </select>
                    </div>
                </div>

                <div class="user-form-group">
                    <label>Affiliation / University / Company Name</label>
                    <input type="text" name="organization_name" id="editOrgName">
                </div>

                <div class="user-form-group" id="editContactGroup" style="display:none;">
                    <label>Contact Number</label>
                    <input type="text" name="contact_number" id="editContact">
                </div>
            </div>
            <div class="user-modal-footer">
                <button type="button" class="btn-secondary" onclick="closeModal('editUserModal')">Cancel</button>
                <button type="submit" class="btn-primary">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<!-- ============================================
     MODAL: VIEW USER DETAILS
     ============================================ -->
<div class="user-modal-overlay" id="viewUserModal">
    <div class="user-modal">
        <div class="user-modal-header">
            <h3>User Profile Overview</h3>
            <button class="user-modal-close" onclick="closeModal('viewUserModal')">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <div class="user-modal-body">
            <div class="user-detail-row">
                <div class="user-detail-label">Name</div>
                <div class="user-detail-value" id="viewName">-</div>
            </div>
            <div class="user-detail-row">
                <div class="user-detail-label">Email</div>
                <div class="user-detail-value" id="viewEmail">-</div>
            </div>
            <div class="user-detail-row">
                <div class="user-detail-label">Role</div>
                <div class="user-detail-value" id="viewRole">-</div>
            </div>
            <div class="user-detail-row">
                <div class="user-detail-label">Affiliation</div>
                <div class="user-detail-value" id="viewOrg">-</div>
            </div>
            <div class="user-detail-row">
                <div class="user-detail-label">Status</div>
                <div class="user-detail-value" id="viewStatus">-</div>
            </div>
            <div class="user-detail-row" id="viewContactRow">
                <div class="user-detail-label">Contact Number</div>
                <div class="user-detail-value" id="viewContact">-</div>
            </div>
            <div class="user-detail-row">
                <div class="user-detail-label">Registration Date</div>
                <div class="user-detail-value" id="viewDate">-</div>
            </div>
        </div>
        <div class="user-modal-footer">
            <button type="button" class="btn-secondary" onclick="closeModal('viewUserModal')">Close</button>
        </div>
    </div>
</div>

<!-- ============================================
     MODAL: CHANGE STATUS CONFIRMATION
     ============================================ -->
<div class="user-modal-overlay" id="statusModal">
    <div class="user-modal">
        <div class="user-modal-header">
            <h3>Change User Status</h3>
            <button class="user-modal-close" onclick="closeModal('statusModal')">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <form method="POST" action="">
            <input type="hidden" name="action" value="toggle_status">
            <input type="hidden" name="email" id="statusEmail">
            <input type="hidden" name="status" id="statusNewValue">
            
            <div class="user-modal-body">
                <div style="display:flex; gap:14px; align-items:flex-start;">
                    <div id="statusIconWrap" style="width:42px; height:42px; border-radius:50%; background:#eff6ff; color:#2563eb; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                        <span class="material-symbols-outlined">sync</span>
                    </div>
                    <div>
                        <p style="font-size: 14px; font-weight:600; color:#111827; margin:0 0 6px 0;">
                            Confirm Status Update
                        </p>
                        <p style="font-size: 13px; color:#64748b; margin:0; line-height:1.5;">
                            Are you sure you want to <span id="statusActionText" style="font-weight:600;"></span> the account for:
                            <br>
                            <strong id="statusUserDisplay" style="color:#111827;"></strong>
                        </p>
                    </div>
                </div>
            </div>
            <div class="user-modal-footer">
                <button type="button" class="btn-secondary" onclick="closeModal('statusModal')">Cancel</button>
                <button type="submit" id="btnConfirmStatus" class="btn-primary">Confirm</button>
            </div>
        </form>
    </div>
</div>

<!-- ============================================
     MODAL: DELETE USER CONFIRMATION
     ============================================ -->
<div class="user-modal-overlay" id="deleteUserModal">
    <div class="user-modal">
        <div class="user-modal-header">
            <h3 style="color:#dc2626;">Delete User Account</h3>
            <button class="user-modal-close" onclick="closeModal('deleteUserModal')">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <form method="POST" action="">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="delete_email" id="deleteEmail">
            
            <div class="user-modal-body">
                <div style="display:flex; gap:14px; align-items:flex-start;">
                    <div style="width:40px; height:40px; border-radius:50%; background:#fee2e2; color:#dc2626; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                        <span class="material-symbols-outlined">warning</span>
                    </div>
                    <div>
                        <p style="font-size: 14px; font-weight:600; color:#111827; margin:0 0 6px 0;">
                            Are you sure you want to permanently delete this user?
                        </p>
                        <p style="font-size: 13px; color:#64748b; margin:0; line-height:1.5;">
                            User: <strong id="deleteUserDisplay" style="color:#111827;"></strong><br>
                            This action cannot be undone. All profile records, projects, and applications linked to this account will be removed.
                        </p>
                    </div>
                </div>
            </div>
            <div class="user-modal-footer">
                <button type="button" class="btn-secondary" onclick="closeModal('deleteUserModal')">Cancel</button>
                <button type="submit" class="btn-danger">Delete User</button>
            </div>
        </form>
    </div>
</div>

<script>
    // Modal Helpers
    function openModal(id) {
        const modal = document.getElementById(id);
        if (modal) modal.classList.add('open');
    }

    function closeModal(id) {
        const modal = document.getElementById(id);
        if (modal) modal.classList.remove('open');
    }

    // Add User button listener
    document.getElementById('btnAddUser').addEventListener('click', function() {
        openModal('addUserModal');
    });

    // Close on overlay click
    document.querySelectorAll('.user-modal-overlay').forEach(overlay => {
        overlay.addEventListener('click', function(e) {
            if (e.target === this) {
                this.classList.remove('open');
            }
        });
    });

    // Toggle Role-specific fields in Add Modal
    function toggleAddFields(role) {
        const orgLabel = document.getElementById('addOrgLabel');
        const studentExtra = document.getElementById('addStudentExtraFields');
        const contactGroup = document.getElementById('addContactGroup');

        if (role === 'student') {
            orgLabel.innerText = 'University / Institution Name';
            studentExtra.style.display = 'grid';
            contactGroup.style.display = 'none';
        } else if (role === 'company') {
            orgLabel.innerText = 'Company Name';
            studentExtra.style.display = 'none';
            contactGroup.style.display = 'block';
        } else if (role === 'organization') {
            orgLabel.innerText = 'Organization Name';
            studentExtra.style.display = 'none';
            contactGroup.style.display = 'block';
        } else if (role === 'admin') {
            orgLabel.innerText = 'Department / System';
            studentExtra.style.display = 'none';
            contactGroup.style.display = 'none';
        }
    }

    // View User Modal
    function viewUser(u) {
        document.getElementById('viewName').innerText = u.user_name || 'N/A';
        document.getElementById('viewEmail').innerText = u.email || 'N/A';
        document.getElementById('viewRole').innerText = (u.role || '').toUpperCase();
        document.getElementById('viewOrg').innerText = u.organization_name || 'N/A';
        document.getElementById('viewStatus').innerText = u.status || 'Active';
        document.getElementById('viewContact').innerText = u.contact_number || 'N/A';
        document.getElementById('viewDate').innerText = u.created_at ? new Date(u.created_at).toLocaleDateString('en-US', { year:'numeric', month:'short', day:'numeric' }) : 'N/A';
        openModal('viewUserModal');
    }

    // Edit User Modal
    function editUser(u, isSelf) {
        document.getElementById('editEmail').value = u.email;
        document.getElementById('editEmailDisplay').value = u.email;
        document.getElementById('editName').value = u.user_name || '';
        document.getElementById('editRole').value = (u.role || 'student').toLowerCase();
        document.getElementById('editStatus').value = u.status || 'Active';
        document.getElementById('editOrgName').value = u.organization_name || '';
        
        const contactGroup = document.getElementById('editContactGroup');
        const contactInput = document.getElementById('editContact');
        if (contactGroup && contactInput) {
            if (isSelf) {
                contactGroup.style.display = 'block';
                contactInput.value = u.contact_number || '';
            } else {
                contactGroup.style.display = 'none';
                contactInput.value = '';
            }
        }
        openModal('editUserModal');
    }

    // Change Status Modal (Replaces browser confirm alert)
    function openStatusModal(email, name, newStatus) {
        document.getElementById('statusEmail').value = email;
        document.getElementById('statusNewValue').value = newStatus;
        document.getElementById('statusUserDisplay').innerText = (name || 'User') + ' (' + email + ')';
        
        const actionText = document.getElementById('statusActionText');
        const btn = document.getElementById('btnConfirmStatus');
        const iconWrap = document.getElementById('statusIconWrap');

        if (newStatus === 'Active') {
            actionText.innerText = 'activate';
            btn.className = 'btn-primary';
            btn.style.background = '#10b981';
            btn.innerText = 'Activate User';
            iconWrap.style.background = '#dcfce7';
            iconWrap.style.color = '#16a34a';
        } else {
            actionText.innerText = 'deactivate';
            btn.className = 'btn-danger';
            btn.style.background = '#ef4444';
            btn.innerText = 'Deactivate User';
            iconWrap.style.background = '#fee2e2';
            iconWrap.style.color = '#dc2626';
        }

        openModal('statusModal');
    }

    // Delete User Modal
    function openDeleteModal(email, name) {
        document.getElementById('deleteEmail').value = email;
        document.getElementById('deleteUserDisplay').innerText = `${name} (${email})`;
        openModal('deleteUserModal');
    }

    // Flash Toast Dismissal
    function closeToast() {
        const toast = document.getElementById('flashToast');
        if (toast) {
            toast.style.animation = 'toastSlideOut 0.3s cubic-bezier(0.16, 1, 0.3, 1) forwards';
            setTimeout(() => {
                toast.remove();
            }, 300);
        }
    }

    // Auto dismiss toast after 4 seconds
    const activeToast = document.getElementById('flashToast');
    if (activeToast) {
        setTimeout(closeToast, 4000);
    }
</script>

<?php include "../../../Includes/dash_footer.php"; ?>
