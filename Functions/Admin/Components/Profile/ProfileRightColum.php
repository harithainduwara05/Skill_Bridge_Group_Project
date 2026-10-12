<div>

    <!-- Card 1: Personal Details -->
    <div class="profile-card">
        <div class="profile-card-header">
            <h3>
                <span class="material-symbols-outlined">manage_accounts</span>
                Personal Information
            </h3>
        </div>
        <div class="profile-card-body">
            <form method="POST" action="" enctype="multipart/form-data" id="personalInfoForm">
                <input type="hidden" name="action" value="update_profile">

                <div class="profile-form-row">
                    <div class="profile-form-group">
                        <label>Full Name *</label>
                        <input type="text" name="name" value="<?= htmlspecialchars($adminName) ?>" required placeholder="Administrator Name">
                    </div>
                    <div class="profile-form-group">
                        <label>Email Address (Primary)</label>
                        <input type="email" value="<?= htmlspecialchars($email) ?>" disabled readonly title="Email cannot be changed directly">
                    </div>
                </div>

                <div class="profile-form-group">
                    <label>Contact Number (Optional)</label>
                    <input type="text" name="contact_number" value="<?= htmlspecialchars($adminContact) ?>" placeholder="e.g. +94 77 123 4567">
                </div>

                <div style="display:flex; justify-content:flex-end; margin-top:10px;">
                    <button type="submit" class="btn-save-profile">
                        <span class="material-symbols-outlined" style="font-size:18px;">save</span>
                        Save Profile
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Card 2: Security & Change Password -->
    <div class="profile-card">
        <div class="profile-card-header">
            <h3>
                <span class="material-symbols-outlined">lock_reset</span>
                Security & Password
            </h3>
        </div>
        <div class="profile-card-body">
            <form method="POST" action="" id="passwordForm">
                <input type="hidden" name="action" value="change_password">

                <div class="profile-form-group">
                    <label>Current Password *</label>
                    <div class="password-input-wrap">
                        <input type="password" name="current_password" id="currPass" required placeholder="Enter current password">
                        <button type="button" class="password-toggle-btn" onclick="togglePassVisibility('currPass', this)">
                            <span class="material-symbols-outlined">visibility</span>
                        </button>
                    </div>
                </div>

                <div class="profile-form-row">
                    <div class="profile-form-group">
                        <label>New Password *</label>
                        <div class="password-input-wrap">
                            <input type="password" name="new_password" id="newPass" required placeholder="Min 8 chars with uppercase, lowercase & numbers" minlength="8" pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}" title="Must contain at least 8 characters, including uppercase, lowercase, and numbers">
                                <button type="button" class="password-toggle-btn" onmousedown="event.preventDefault();" onclick="togglePassVisibility('newPass', this)">
                                <span class="material-symbols-outlined">visibility</span>
                            </button>
                        </div>
                        <!-- Password Requirements (Smooth downward accordion list, appears on focus) -->
                        <div class="password-requirements" id="adminPassRequirements">
                            <div class="req-item" id="adminReqLength">
                                <span class="req-icon">✕</span>
                                <span>At least 8 characters</span>
                            </div>
                            <div class="req-item" id="adminReqUpper">
                                <span class="req-icon">✕</span>
                                <span>At least 1 uppercase (A-Z)</span>
                            </div>
                            <div class="req-item" id="adminReqLower">
                                <span class="req-icon">✕</span>
                                <span>At least 1 lowercase (a-z)</span>
                            </div>
                            <div class="req-item" id="adminReqNum">
                                <span class="req-icon">✕</span>
                                <span>At least 1 number (0-9)</span>
                            </div>
                        </div>
                    </div>
                    <div class="profile-form-group">
                        <label>Confirm New Password *</label>
                        <div class="password-input-wrap">
                            <input type="password" name="confirm_password" id="confPass" required placeholder="Re-type new password" minlength="8">
                            <button type="button" class="password-toggle-btn" onmousedown="event.preventDefault();" onclick="togglePassVisibility('confPass', this)">
                                <span class="material-symbols-outlined">visibility</span>
                            </button>
                        </div>
                    </div>
                </div>

                <div id="passMatchMsg" style="font-size:12.5px; margin-bottom:14px; display:none;"></div>

                <div style="display:flex; justify-content:flex-end;">
                    <button type="submit" class="btn-update-password">
                        <span class="material-symbols-outlined" style="font-size:18px;">key</span>
                        Update Password
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>