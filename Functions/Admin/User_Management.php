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
                            <th style="padding: 12px 20px; font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase;">Actions</th>
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

                                <td style="padding: 14px 20px;">
                                    <div class="user-actions-cell">
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

<!-- MODAL: ADD NEW USER -->
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
                        <label>Role *</label>
                        <select name="role" id="addRoleSelect" onchange="toggleAddFields(this.value)">
                            <option value="student">Student</option>
                            <option value="company">Company</option>
                            <option value="organization">Organization</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                </div>

                <div class="user-form-group">
                    <label>Status</label>
                    <select name="status">
                        <option value="Active">Active</option>
                        <option value="De-Active">De-Active</option>
                        <option value="Hold">Hold</option>
                    </select>
                </div>

                <div class="user-info-notice" style="background:#f0fdf4; border:1px solid #bbf7d0; border-radius:10px; padding:11px 14px; font-size:12.5px; color:#166534; margin-bottom:14px; display:flex; align-items:flex-start; gap:10px;">
                    <span class="material-symbols-outlined" style="font-size:20px; color:#16a34a; flex-shrink:0;">verified_user</span>
                    <div>
                        <strong>Passwordless Security:</strong> No password required. An activation email will be sent to the user instructing them to securely set their password via a 6-digit OTP code.
                    </div>
                </div>

                <!-- Student University & Faculty Searchable Comboboxes (from Database + Manual Entry) -->
                <div id="addStudentUniFacultyGroup">
                    <div class="user-form-group">
                        <label>University / Institution <span style="color:#ef4444;">*</span></label>
                        <div class="univ-combobox" id="addUserUnivCombobox">
                            <div class="univ-combobox-input-wrap">
                                <input type="text" class="univ-combobox-input" name="university_name" id="addUserUnivInput" placeholder="Select or type university name" autocomplete="off">
                                <button type="button" class="univ-combobox-toggle" id="addUserUnivToggle" title="Toggle university list" tabindex="-1">
                                    <span class="material-symbols-outlined">expand_more</span>
                                </button>
                            </div>
                            <div class="univ-combobox-dropdown" id="addUserUnivDropdown">
                                <div class="univ-combobox-list" id="addUserUnivList"></div>
                            </div>
                        </div>
                    </div>

                    <div class="user-form-group">
                        <label>Faculty / Department</label>
                        <div class="univ-combobox" id="addUserFacCombobox">
                            <div class="univ-combobox-input-wrap">
                                <input type="text" class="univ-combobox-input" name="faculty_name" id="addUserFacInput" placeholder="Select or type faculty name" autocomplete="off">
                                <button type="button" class="univ-combobox-toggle" id="addUserFacToggle" title="Toggle faculty list" tabindex="-1">
                                    <span class="material-symbols-outlined">expand_more</span>
                                </button>
                            </div>
                            <div class="univ-combobox-dropdown" id="addUserFacDropdown">
                                <div class="univ-combobox-list" id="addUserFacList"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Non-Student Affiliation Group (Company / Org / Admin) -->
                <div class="user-form-group" id="addOrgNameGroup" style="display:none;">
                    <label id="addOrgLabel">Company / Organization Name</label>
                    <input type="text" name="organization_name" placeholder="e.g. Virtusa Lanka">
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

<!-- MODAL: BULK IMPORT STUDENTS -->
<div class="user-modal-overlay" id="bulkUploadModal">
    <div class="user-modal" style="max-width:560px;">
        <div class="user-modal-header">
            <h3 style="display:flex; align-items:center; gap:8px;">
                <span class="material-symbols-outlined" style="font-size:22px; color:#2563eb;">upload_file</span>
                Bulk Import Students
            </h3>
            <button class="user-modal-close" onclick="closeModal('bulkUploadModal')">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <form method="POST" action="" enctype="multipart/form-data">
            <input type="hidden" name="action" value="bulk_upload">
            <div class="user-modal-body">
                <div class="user-info-notice" style="background:#eff6ff; border:1px solid #bfdbfe; border-radius:10px; padding:12px 14px; font-size:12.5px; color:#1e40af; margin-bottom:16px; display:flex; align-items:flex-start; gap:10px;">
                    <span class="material-symbols-outlined" style="font-size:22px; color:#2563eb; flex-shrink:0;">info</span>
                    <div>
                        <strong>Institutional Bulk Onboarding:</strong> Select the institution and faculty providing the student roster. All imported accounts are created instantly in the database without sending individual emails. Students activate their accounts via <em>"Forgot Password / First-Time Setup"</em>.
                    </div>
                </div>

                <!-- Step 1: Institutional Target Selection -->
                <div class="user-form-row" style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:14px;">
                    <div class="user-form-group" style="margin-bottom:0;">
                        <label>University / Institution <span style="color:#ef4444;">*</span></label>
                        <select name="bulk_university" id="bulkUniSelect" required style="width:100%; padding:10px 12px; border:1.5px solid #cbd5e1; border-radius:10px; background:#fff; font-size:13px; font-weight:500; color:#1e293b; outline:none; transition:border-color 0.2s;">
                            <option value="">-- Choose University --</option>
                            <?php foreach (array_keys($activeInstitutions) as $u): ?>
                                <option value="<?= htmlspecialchars($u) ?>"><?= htmlspecialchars($u) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="user-form-group" style="margin-bottom:0;">
                        <label>Faculty / Department <span style="color:#ef4444;">*</span></label>
                        <select name="bulk_faculty" id="bulkFacSelect" required disabled style="width:100%; padding:10px 12px; border:1.5px solid #cbd5e1; border-radius:10px; background:#f8fafc; font-size:13px; font-weight:500; color:#1e293b; outline:none; transition:border-color 0.2s; cursor:not-allowed;">
                            <option value="">-- Choose University first --</option>
                        </select>
                    </div>
                </div>

                <!-- Dynamic Domain Whitelist Notice -->
                <div id="bulkDomainBox" style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:10px; padding:12px 14px; margin-bottom:16px; display:flex; align-items:center; gap:10px; transition:all 0.25s ease;">
                    <span class="material-symbols-outlined" id="bulkDomainIcon" style="font-size:22px; color:#64748b; flex-shrink:0;">domain_verification</span>
                    <div id="bulkDomainText" style="font-size:12.5px; line-height:1.4;">
                        <span style="color:#64748b;">Please select a University and Faculty above to view the permitted student email domain.</span>
                    </div>
                </div>

                <!-- Step 2: Upload CSV File -->
                <div class="user-form-group">
                    <label>Student Roster CSV File <span style="color:#ef4444;">*</span></label>
                    <input type="file" name="csv_file" accept=".csv" required class="user-file-input" style="padding:10px; border:1.5px dashed #cbd5e1; border-radius:10px; width:100%; background:#f8fafc; cursor:pointer;">
                    <small style="color:#64748b; font-size:11.5px; margin-top:4px; display:block;">Supported format: .csv (UTF-8)</small>
                </div>

                <!-- Step 3: Template Download -->
                <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:10px; padding:12px 14px; margin-top:14px; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:10px;">
                    <div>
                        <strong style="font-size:12.5px; color:#1e293b; display:block;">Need the simplified template format?</strong>
                        <span style="font-size:11.5px; color:#64748b;">Columns: <code>Name, Email, Degree, Academic_Year</code></span>
                    </div>
                    <a href="?action=download_sample_csv" class="btn-sample-download" style="display:inline-flex; align-items:center; gap:6px; padding:7px 12px; background:#fff; color:#2563eb; border:1px solid #bfdbfe; border-radius:8px; font-size:12px; font-weight:600; text-decoration:none;">
                        <span class="material-symbols-outlined" style="font-size:16px;">download</span>
                        Download Sample CSV
                    </a>
                </div>
            </div>
            <div class="user-modal-footer">
                <button type="button" class="btn-secondary" onclick="closeModal('bulkUploadModal')">Cancel</button>
                <button type="submit" class="btn-primary" style="background:#2563eb;">
                    <span class="material-symbols-outlined" style="font-size:18px;">cloud_upload</span>
                    Import Students
                </button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL: EDIT USER-->
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

<!-- MODAL: VIEW USER DETAILS -->
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

<!--MODAL: CHANGE STATUS CONFIRMATION-->
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

<!-- MODAL: DELETE USER CONFIRMATION-->
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

    const btnBulk = document.getElementById('btnBulkUpload');
    if (btnBulk) {
        btnBulk.addEventListener('click', function() {
            openModal('bulkUploadModal');
        });
    }

    // Close on overlay click
    document.querySelectorAll('.user-modal-overlay').forEach(overlay => {
        overlay.addEventListener('click', function(e) {
            if (e.target === this) {
                this.classList.remove('open');
            }
        });
    });

    // Universities and Faculties fetched from database
    const dbUniversities = <?= json_encode($dbUniversities, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    const activeInstitutions = <?= json_encode($activeInstitutions, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;

    // Bulk Import Modal: Dynamic University -> Faculty -> Authorized Domain Logic
    const bulkUniSelect  = document.getElementById('bulkUniSelect');
    const bulkFacSelect  = document.getElementById('bulkFacSelect');
    const bulkDomainBox  = document.getElementById('bulkDomainBox');
    const bulkDomainIcon = document.getElementById('bulkDomainIcon');
    const bulkDomainText = document.getElementById('bulkDomainText');

    function updateBulkDomainBadge(domain) {
        if (!bulkDomainBox || !bulkDomainIcon || !bulkDomainText) return;
        if (domain) {
            bulkDomainBox.style.background = '#f0fdf4';
            bulkDomainBox.style.borderColor = '#86efac';
            bulkDomainIcon.style.color = '#16a34a';
            bulkDomainIcon.textContent = 'verified';
            bulkDomainText.innerHTML = `
                <div style="font-weight:600; color:#15803d; font-size:12.5px; display:flex; align-items:center; gap:6px;">
                    Authorized Email Domain: <code style="background:#dcfce7; color:#15803d; padding:2px 8px; border-radius:6px; font-family:monospace; font-weight:700; font-size:13px;">@${domain}</code>
                </div>
                <div style="font-size:11.5px; color:#166534; margin-top:2px;">
                    Only student emails ending with <strong>@${domain}</strong> will be accepted in this batch.
                </div>
            `;
        } else {
            bulkDomainBox.style.background = '#f8fafc';
            bulkDomainBox.style.borderColor = '#e2e8f0';
            bulkDomainIcon.style.color = '#64748b';
            bulkDomainIcon.textContent = 'domain_verification';
            bulkDomainText.innerHTML = `
                <span style="color:#64748b; font-size:12px;">Please select a University and Faculty above to view the permitted student email domain.</span>
            `;
        }
    }

    if (bulkUniSelect && bulkFacSelect) {
        bulkUniSelect.addEventListener('change', function() {
            const u = this.value;
            bulkFacSelect.innerHTML = '<option value="">-- Choose Faculty --</option>';
            if (u && activeInstitutions[u]) {
                bulkFacSelect.disabled = false;
                bulkFacSelect.style.cursor = 'pointer';
                bulkFacSelect.style.background = '#ffffff';
                const facs = Object.keys(activeInstitutions[u]);
                facs.forEach(f => {
                    const opt = document.createElement('option');
                    opt.value = f;
                    opt.textContent = f;
                    bulkFacSelect.appendChild(opt);
                });
                if (facs.length === 1) {
                    bulkFacSelect.value = facs[0];
                    updateBulkDomainBadge(activeInstitutions[u][facs[0]]);
                } else {
                    updateBulkDomainBadge(null);
                }
            } else {
                bulkFacSelect.disabled = true;
                bulkFacSelect.style.cursor = 'not-allowed';
                bulkFacSelect.style.background = '#f8fafc';
                updateBulkDomainBadge(null);
            }
        });

        bulkFacSelect.addEventListener('change', function() {
            const u = bulkUniSelect.value;
            const f = this.value;
            if (u && f && activeInstitutions[u] && activeInstitutions[u][f]) {
                updateBulkDomainBadge(activeInstitutions[u][f]);
            } else {
                updateBulkDomainBadge(null);
            }
        });
    }

    function setupSearchableCombobox(config) {
        const combobox = document.getElementById(config.comboboxId);
        const input    = document.getElementById(config.inputId);
        const toggle   = document.getElementById(config.toggleId);
        const dropdown = document.getElementById(config.dropdownId);
        const list     = document.getElementById(config.listId);

        if (!combobox || !input || !toggle || !dropdown || !list) return null;

        let highlightedIndex = -1;

        function escHtml(str) {
            const d = document.createElement('div');
            d.appendChild(document.createTextNode(str || ''));
            return d.innerHTML;
        }

        function renderList(query = '') {
            list.innerHTML = '';
            highlightedIndex = -1;
            const q = (query || '').trim().toLowerCase();
            const dataset = typeof config.getItems === 'function' ? config.getItems() : [];

            const filtered = dataset.filter(itemText => {
                return itemText && itemText.toLowerCase().includes(q);
            });

            let exactMatch = false;

            filtered.forEach(itemText => {
                if (itemText.toLowerCase() === q) exactMatch = true;

                const item = document.createElement('div');
                item.className = 'univ-combobox-item';
                if (input.value.trim().toLowerCase() === itemText.toLowerCase()) {
                    item.classList.add('selected');
                }

                const nameEl = document.createElement('span');
                nameEl.textContent = itemText;
                item.appendChild(nameEl);

                item.addEventListener('mousedown', (e) => {
                    e.preventDefault();
                    selectItem(itemText);
                });

                list.appendChild(item);
            });

            // If query is not empty and doesn't exactly match an existing item,
            // show manual entry option so the user can easily confirm their custom name
            if (q !== '' && !exactMatch) {
                const customItem = document.createElement('div');
                customItem.className = 'univ-combobox-item custom-entry';
                customItem.innerHTML = `
                    <div style="display:flex; align-items:center; gap:6px; overflow:hidden;">
                        <span class="material-symbols-outlined" style="font-size:16px;">edit_note</span>
                        <span style="white-space:nowrap; text-overflow:ellipsis; overflow:hidden;">Use: "<strong>${escHtml(query.trim())}</strong>"</span>
                    </div>
                    <span class="univ-combobox-badge-custom">Manual</span>
                `;
                customItem.addEventListener('mousedown', (e) => {
                    e.preventDefault();
                    selectItem(query.trim());
                });
                list.appendChild(customItem);
            }

            if (filtered.length === 0 && q === '') {
                const empty = document.createElement('div');
                empty.className = 'univ-combobox-empty';
                empty.textContent = config.emptyText || 'No options found.';
                list.appendChild(empty);
            }
        }

        function selectItem(text) {
            input.value = text;
            closeDropdown();
            if (typeof config.onSelect === 'function') {
                config.onSelect(text);
            }
        }

        function openDropdown() {
            renderList(input.value);
            dropdown.classList.add('open');
            toggle.classList.add('open');
        }

        function closeDropdown() {
            dropdown.classList.remove('open');
            toggle.classList.remove('open');
            highlightedIndex = -1;
        }

        toggle.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            if (dropdown.classList.contains('open')) {
                closeDropdown();
            } else {
                openDropdown();
                input.focus();
            }
        });

        input.addEventListener('focus', () => {
            openDropdown();
        });

        input.addEventListener('input', () => {
            openDropdown();
            if (typeof config.onInput === 'function') {
                config.onInput(input.value);
            }
        });

        // Keyboard navigation
        input.addEventListener('keydown', (e) => {
            const items = list.querySelectorAll('.univ-combobox-item');
            if (!items.length) return;

            if (e.key === 'ArrowDown') {
                e.preventDefault();
                if (!dropdown.classList.contains('open')) {
                    openDropdown();
                    return;
                }
                highlightedIndex = (highlightedIndex + 1) % items.length;
                updateHighlight(items);
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                if (!dropdown.classList.contains('open')) {
                    openDropdown();
                    return;
                }
                highlightedIndex = (highlightedIndex - 1 + items.length) % items.length;
                updateHighlight(items);
            } else if (e.key === 'Enter') {
                if (dropdown.classList.contains('open') && highlightedIndex >= 0 && items[highlightedIndex]) {
                    e.preventDefault();
                    items[highlightedIndex].dispatchEvent(new MouseEvent('mousedown'));
                }
            } else if (e.key === 'Escape') {
                closeDropdown();
            }
        });

        function updateHighlight(items) {
            items.forEach((it, idx) => {
                if (idx === highlightedIndex) {
                    it.classList.add('highlighted');
                    it.scrollIntoView({ block: 'nearest' });
                } else {
                    it.classList.remove('highlighted');
                }
            });
        }

        document.addEventListener('mousedown', (e) => {
            if (!combobox.contains(e.target)) {
                closeDropdown();
            }
        });

        return {
            open: openDropdown,
            close: closeDropdown,
            refresh: () => {
                if (dropdown.classList.contains('open')) {
                    renderList(input.value);
                }
            }
        };
    }

    // Initialize University and Faculty Comboboxes
    const facCombobox = setupSearchableCombobox({
        comboboxId: 'addUserFacCombobox',
        inputId: 'addUserFacInput',
        toggleId: 'addUserFacToggle',
        dropdownId: 'addUserFacDropdown',
        listId: 'addUserFacList',
        emptyText: 'No faculties found.',
        getItems: () => {
            const currentUni = (document.getElementById('addUserUnivInput').value || '').trim().toLowerCase();
            if (currentUni) {
                for (const [uName, fList] of Object.entries(dbUniversities)) {
                    if (uName.toLowerCase() === currentUni) {
                        return fList;
                    }
                }
            }
            // If no exact university match or not typed, return all known faculties across all universities
            const allFacs = new Set();
            Object.values(dbUniversities).forEach(facList => {
                facList.forEach(f => allFacs.add(f));
            });
            return Array.from(allFacs);
        }
    });

    const univCombobox = setupSearchableCombobox({
        comboboxId: 'addUserUnivCombobox',
        inputId: 'addUserUnivInput',
        toggleId: 'addUserUnivToggle',
        dropdownId: 'addUserUnivDropdown',
        listId: 'addUserUnivList',
        emptyText: 'No universities found.',
        getItems: () => Object.keys(dbUniversities),
        onSelect: (selectedUni) => {
            if (facCombobox) facCombobox.refresh();
        },
        onInput: (val) => {
            if (facCombobox) facCombobox.refresh();
        }
    });

    // Toggle Role-specific fields in Add Modal
    function toggleAddFields(role) {
        const studentUniFac = document.getElementById('addStudentUniFacultyGroup');
        const orgGroup = document.getElementById('addOrgNameGroup');
        const orgLabel = document.getElementById('addOrgLabel');
        const studentExtra = document.getElementById('addStudentExtraFields');
        const contactGroup = document.getElementById('addContactGroup');
        const univInput = document.getElementById('addUserUnivInput');

        if (role === 'student') {
            studentUniFac.style.display = 'block';
            orgGroup.style.display = 'none';
            studentExtra.style.display = 'grid';
            contactGroup.style.display = 'none';
            if (univInput) univInput.required = true;
        } else if (role === 'company') {
            studentUniFac.style.display = 'none';
            orgGroup.style.display = 'block';
            orgLabel.innerText = 'Company Name';
            studentExtra.style.display = 'none';
            contactGroup.style.display = 'block';
            if (univInput) univInput.required = false;
        } else if (role === 'organization') {
            studentUniFac.style.display = 'none';
            orgGroup.style.display = 'block';
            orgLabel.innerText = 'Organization Name';
            studentExtra.style.display = 'none';
            contactGroup.style.display = 'block';
            if (univInput) univInput.required = false;
        } else if (role === 'admin') {
            studentUniFac.style.display = 'none';
            orgGroup.style.display = 'block';
            orgLabel.innerText = 'Department / System';
            studentExtra.style.display = 'none';
            contactGroup.style.display = 'none';
            if (univInput) univInput.required = false;
        }
    }

    // Add User Form Validation for Student University
    const addUserForm = document.querySelector('#addUserModal form');
    if (addUserForm) {
        addUserForm.addEventListener('submit', function(e) {
            const role = document.getElementById('addRoleSelect').value;
            if (role === 'student') {
                const uniVal = document.getElementById('addUserUnivInput').value.trim();
                if (!uniVal) {
                    e.preventDefault();
                    alert('Please select or type a University name for the student.');
                    document.getElementById('addUserUnivInput').focus();
                }
            }
        });
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
<footer class="footer">
    <div>&copy; 2026 SkillBridge. All rights reserved.</div>
    <div class="footer-links">
        <a href="/Skill_Bridge_Group_Project/help_center.php">Help Center</a>
        <a href="#">Privacy Policy</a>
        <a href="#">Terms of Service</a>
    </div>
</footer>
<?php include "../../Includes/dash_footer.php"; ?>
