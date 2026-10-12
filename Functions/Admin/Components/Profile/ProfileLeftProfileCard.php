<div class="profile-card">
    <div class="profile-card-header">
        <h3>
            <span class="material-symbols-outlined">badge</span>
            Profile Overview
        </h3>
    </div>
    <div class="profile-card-body">
        <form method="POST" action="" enctype="multipart/form-data" id="photoUploadForm">
            <input type="hidden" name="action" value="update_profile">
            <input type="hidden" name="name" value="<?= htmlspecialchars($adminName) ?>">
            <input type="hidden" name="contact_number" value="<?= htmlspecialchars($adminContact) ?>">
            
            <div class="profile-avatar-section">
                <div class="avatar-uploader-wrapper">
                    <?php if (!empty($photoUrl)): ?>
                        <img src="<?= $photoUrl ?>?v=<?= time() ?>" alt="<?= htmlspecialchars($adminName) ?>" class="profile-photo-preview" id="avatarPreview">
                    <?php else: ?>
                        <div class="profile-photo-initial" id="avatarInitial">
                            <?= htmlspecialchars($initial) ?>
                        </div>
                        <img src="" alt="Preview" class="profile-photo-preview" id="avatarPreview" style="display:none;">
                    <?php endif; ?>

                    <!-- Camera Button -->
                    <label for="profilePhotoInput" class="avatar-upload-btn" title="Upload New Profile Photo">
                        <span class="material-symbols-outlined">photo_camera</span>
                    </label>
                    <input type="file" name="profile_image" id="profilePhotoInput" accept="image/png, image/jpeg, image/jpg, image/webp" style="display:none;" onchange="handlePhotoSelect(this)">
                </div>

                <h2 class="profile-card-name"><?= htmlspecialchars($adminName) ?></h2>
                <p class="profile-card-email"><?= htmlspecialchars($email) ?></p>
                
                <span class="profile-role-badge">
                    <span class="material-symbols-outlined" style="font-size:14px;">admin_panel_settings</span>
                    <?= htmlspecialchars($adminRole) ?>
                </span>

                <?php if (!empty($adminPhoto)): ?>
                    <form method="POST" action="" style="margin-top:8px;">
                        <input type="hidden" name="action" value="remove_photo">
                        <button type="submit" class="avatar-remove-btn" onclick="return confirm('Are you sure you want to remove your profile photo?')">
                            <span class="material-symbols-outlined" style="font-size:14px;">delete</span>
                            Remove Photo
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </form>

        <!-- Account Info List -->
        <div class="profile-meta-list">
            <div class="profile-meta-item">
                <span class="profile-meta-label">
                    <span class="material-symbols-outlined">security</span>
                    Access Level
                </span>
                <span class="profile-meta-value" style="color:#10b981;">Full Admin</span>
            </div>
            <div class="profile-meta-item">
                <span class="profile-meta-label">
                    <span class="material-symbols-outlined">verified</span>
                    Account Status
                </span>
                <span class="badge-status-pill active" style="font-size:11px; padding:2px 8px;">Active</span>
            </div>
            <div class="profile-meta-item">
                <span class="profile-meta-label">
                    <span class="material-symbols-outlined">calendar_today</span>
                    Member Since
                </span>
                <span class="profile-meta-value"><?= htmlspecialchars($adminCreated) ?></span>
            </div>
        </div>

    </div>
</div>