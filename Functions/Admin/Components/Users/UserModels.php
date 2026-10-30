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

                <div class="user-info-notice"
                    style="background:#f0fdf4; border:1px solid #bbf7d0; border-radius:10px; padding:11px 14px; font-size:12.5px; color:#166534; margin-bottom:14px; display:flex; align-items:flex-start; gap:10px;">
                    <span class="material-symbols-outlined"
                        style="font-size:20px; color:#16a34a; flex-shrink:0;">verified_user</span>
                    <div>
                        <strong>Passwordless Security:</strong> No password required. An activation email will be sent
                        to the user instructing them to securely set their password via a 6-digit OTP code.
                    </div>
                </div>

                <!-- Student University & Faculty Searchable Comboboxes (from Database + Manual Entry) -->
                <div id="addStudentUniFacultyGroup">
                    <div class="user-form-group">
                        <label>University / Institution <span style="color:#ef4444;">*</span></label>
                        <div class="univ-combobox" id="addUserUnivCombobox">
                            <div class="univ-combobox-input-wrap">
                                <input type="text" class="univ-combobox-input" name="university_name"
                                    id="addUserUnivInput" placeholder="Select or type university name"
                                    autocomplete="off">
                                <button type="button" class="univ-combobox-toggle" id="addUserUnivToggle"
                                    title="Toggle university list" tabindex="-1">
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
                                <input type="text" class="univ-combobox-input" name="faculty_name" id="addUserFacInput"
                                    placeholder="Select or type faculty name" autocomplete="off">
                                <button type="button" class="univ-combobox-toggle" id="addUserFacToggle"
                                    title="Toggle faculty list" tabindex="-1">
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
                <div class="user-info-notice"
                    style="background:#eff6ff; border:1px solid #bfdbfe; border-radius:10px; padding:12px 14px; font-size:12.5px; color:#1e40af; margin-bottom:16px; display:flex; align-items:flex-start; gap:10px;">
                    <span class="material-symbols-outlined"
                        style="font-size:22px; color:#2563eb; flex-shrink:0;">info</span>
                    <div>
                        <strong>Institutional Bulk Onboarding:</strong> Select the institution and faculty providing the
                        student roster. All imported accounts are created instantly in the database without sending
                        individual emails. Students activate their accounts via <em>"Forgot Password / First-Time
                            Setup"</em>.
                    </div>
                </div>

                <!-- Step 1: Institutional Target Selection -->
                <div class="user-form-row"
                    style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:14px;">
                    <div class="user-form-group" style="margin-bottom:0;">
                        <label>University / Institution <span style="color:#ef4444;">*</span></label>
                        <select name="bulk_university" id="bulkUniSelect" required
                            style="width:100%; padding:10px 12px; border:1.5px solid #cbd5e1; border-radius:10px; background:#fff; font-size:13px; font-weight:500; color:#1e293b; outline:none; transition:border-color 0.2s;">
                            <option value="">-- Choose University --</option>
                            <?php foreach (array_keys($activeInstitutions) as $u): ?>
                                <option value="<?= htmlspecialchars($u) ?>"><?= htmlspecialchars($u) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="user-form-group" style="margin-bottom:0;">
                        <label>Faculty / Department <span style="color:#ef4444;">*</span></label>
                        <select name="bulk_faculty" id="bulkFacSelect" required disabled
                            style="width:100%; padding:10px 12px; border:1.5px solid #cbd5e1; border-radius:10px; background:#f8fafc; font-size:13px; font-weight:500; color:#1e293b; outline:none; transition:border-color 0.2s; cursor:not-allowed;">
                            <option value="">-- Choose University first --</option>
                        </select>
                    </div>
                </div>

                <!-- Dynamic Domain Whitelist Notice -->
                <div id="bulkDomainBox"
                    style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:10px; padding:12px 14px; margin-bottom:16px; display:flex; align-items:center; gap:10px; transition:all 0.25s ease;">
                    <span class="material-symbols-outlined" id="bulkDomainIcon"
                        style="font-size:22px; color:#64748b; flex-shrink:0;">domain_verification</span>
                    <div id="bulkDomainText" style="font-size:12.5px; line-height:1.4;">
                        <span style="color:#64748b;">Please select a University and Faculty above to view the permitted
                            student email domain.</span>
                    </div>
                </div>

                <!-- Step 2: Upload CSV File -->
                <div class="user-form-group">
                    <label>Student Roster CSV File <span style="color:#ef4444;">*</span></label>
                    <input type="file" name="csv_file" accept=".csv" required class="user-file-input"
                        style="padding:10px; border:1.5px dashed #cbd5e1; border-radius:10px; width:100%; background:#f8fafc; cursor:pointer;">
                    <small style="color:#64748b; font-size:11.5px; margin-top:4px; display:block;">Supported format:
                        .csv (UTF-8)</small>
                </div>

                <!-- Step 3: Template Download -->
                <div
                    style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:10px; padding:12px 14px; margin-top:14px; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:10px;">
                    <div>
                        <strong style="font-size:12.5px; color:#1e293b; display:block;">Need the simplified template
                            format?</strong>
                        <span style="font-size:11.5px; color:#64748b;">Columns:
                            <code>Name, Email, Degree, Academic_Year</code></span>
                    </div>
                    <a href="?action=download_sample_csv" class="btn-sample-download"
                        style="display:inline-flex; align-items:center; gap:6px; padding:7px 12px; background:#fff; color:#2563eb; border:1px solid #bfdbfe; border-radius:8px; font-size:12px; font-weight:600; text-decoration:none;">
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
                    <div id="statusIconWrap"
                        style="width:42px; height:42px; border-radius:50%; background:#eff6ff; color:#2563eb; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                        <span class="material-symbols-outlined">sync</span>
                    </div>
                    <div>
                        <p style="font-size: 14px; font-weight:600; color:#111827; margin:0 0 6px 0;">
                            Confirm Status Update
                        </p>
                        <p style="font-size: 13px; color:#64748b; margin:0; line-height:1.5;">
                            Are you sure you want to <span id="statusActionText" style="font-weight:600;"></span> the
                            account for:
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
                    <div
                        style="width:40px; height:40px; border-radius:50%; background:#fee2e2; color:#dc2626; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                        <span class="material-symbols-outlined">warning</span>
                    </div>
                    <div>
                        <p style="font-size: 14px; font-weight:600; color:#111827; margin:0 0 6px 0;">
                            Are you sure you want to permanently delete this user?
                        </p>
                        <p style="font-size: 13px; color:#64748b; margin:0; line-height:1.5;">
                            User: <strong id="deleteUserDisplay" style="color:#111827;"></strong><br>
                            This action cannot be undone. All profile records, projects, and applications linked to this
                            account will be removed.
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