<?php
require_once "../../../Config/db.php";
require_once "../../../Session/Session.php";

require_login();
require_role('admin');

$loggedInUser = current_user();
$email = strtolower($loggedInUser['Email'] ?? $loggedInUser['email'] ?? '');

require_once "AdminBackend.php";

$flash = null;

// ============================================
// HANDLE POST ACTIONS
// ============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // ── 1. Update Profile Details & Photo ──
    if ($action === 'update_profile') {
        $name = trim($_POST['name'] ?? '');
        $contactNumber = trim($_POST['contact_number'] ?? '');

        if (empty($name)) {
            $flash = ['type' => 'error', 'title' => 'Validation Error', 'message' => 'Full Name cannot be empty.'];
        } else {
            $uploadedFileName = null;

            // Handle Profile Image Upload
            if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
                $fileTmp  = $_FILES['profile_image']['tmp_name'];
                $fileName = $_FILES['profile_image']['name'];
                $fileSize = $_FILES['profile_image']['size'];
                $fileExt  = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

                $allowedExts = ['jpg', 'jpeg', 'png', 'webp'];
                $maxSize = 3 * 1024 * 1024; // 3MB

                if (!in_array($fileExt, $allowedExts)) {
                    $flash = ['type' => 'error', 'title' => 'Invalid File', 'message' => 'Only JPG, JPEG, PNG, and WEBP image files are allowed.'];
                } elseif ($fileSize > $maxSize) {
                    $flash = ['type' => 'error', 'title' => 'File Too Large', 'message' => 'Image size must not exceed 3MB.'];
                } else {
                    $targetDir = __DIR__ . '/../../../Assets/Images/Admin/';
                    if (!is_dir($targetDir)) {
                        mkdir($targetDir, 0777, true);
                    }

                    // Delete old photo if exists
                    $currentProfile = $adminDB->getAdminProfile($email);
                    if (!empty($currentProfile['profile_image'])) {
                        $oldPath = $targetDir . $currentProfile['profile_image'];
                        if (file_exists($oldPath)) {
                            @unlink($oldPath);
                        }
                    }

                    $newFileName = 'admin_' . time() . '_' . uniqid() . '.' . $fileExt;
                    $destPath = $targetDir . $newFileName;

                    if (move_uploaded_file($fileTmp, $destPath)) {
                        $uploadedFileName = $newFileName;
                    } else {
                        $flash = ['type' => 'error', 'title' => 'Upload Failed', 'message' => 'Failed to save uploaded image.'];
                    }
                }
            }

            if (!$flash) {
                $updated = $adminDB->updateAdminProfile($email, $name, $contactNumber, $uploadedFileName);
                if ($updated) {
                    $_SESSION['user']['username'] = $name;
                    $flash = ['type' => 'success', 'title' => 'Profile Updated', 'message' => 'Your profile information has been saved successfully.'];
                } else {
                    $flash = ['type' => 'error', 'title' => 'Update Failed', 'message' => 'Could not update profile details in database.'];
                }
            }
        }
    }

    // ── 2. Remove Profile Photo ──
    elseif ($action === 'remove_photo') {
        $removed = $adminDB->removeAdminProfileImage($email);
        if ($removed) {
            $flash = ['type' => 'success', 'title' => 'Photo Removed', 'message' => 'Your profile photo has been removed.'];
        } else {
            $flash = ['type' => 'error', 'title' => 'Action Failed', 'message' => 'Could not remove profile photo.'];
        }
    }

    // ── 3. Change Password ──
    elseif ($action === 'change_password') {
        $currPass = $_POST['current_password'] ?? '';
        $newPass  = $_POST['new_password'] ?? '';
        $confPass = $_POST['confirm_password'] ?? '';

        if (empty($currPass) || empty($newPass) || empty($confPass)) {
            $flash = ['type' => 'error', 'title' => 'Missing Fields', 'message' => 'Please fill in all password fields.'];
        } elseif (strlen($newPass) < 6) {
            $flash = ['type' => 'error', 'title' => 'Weak Password', 'message' => 'New password must be at least 6 characters long.'];
        } elseif ($newPass !== $confPass) {
            $flash = ['type' => 'error', 'title' => 'Password Mismatch', 'message' => 'New password and confirmation do not match.'];
        } else {
            $res = $adminDB->changeAdminPassword($email, $currPass, $newPass);
            if ($res['success']) {
                $flash = ['type' => 'success', 'title' => 'Security Updated', 'message' => $res['message']];
            } else {
                $flash = ['type' => 'error', 'title' => 'Password Error', 'message' => $res['message']];
            }
        }
    }
}

// Fetch Latest Admin Details
$profile = $adminDB->getAdminProfile($email);
$adminName = $profile['name'] ?? 'Admin';
$adminPhoto = $profile['profile_image'] ?? null;
$adminContact = $profile['contact_number'] ?? '';
$adminRole = ucfirst($profile['role'] ?? 'admin');
$adminCreated = $profile['created_at'] ? date('M d, Y', strtotime($profile['created_at'])) : 'N/A';
$initial = !empty(trim($adminName)) ? strtoupper(mb_substr(trim($adminName), 0, 1)) : 'A';

$photoUrl = null;
if (!empty($adminPhoto) && file_exists(__DIR__ . '/../../../Assets/Images/Admin/' . $adminPhoto)) {
    $photoUrl = $GLOBALS['BASE_URL'] . '/Assets/Images/Admin/' . htmlspecialchars($adminPhoto);
}

include "../../../Includes/admin_sidebar.php";
?>
<link rel="stylesheet" href="../../../Assets/CSS/Admin/profile.css">
<link rel="stylesheet" href="../../../Assets/CSS/Admin/usermanagement.css">
<?php
include "../../../Includes/dash_header.php";
?>

<main class="content">

    <!-- Flash Toast Notification -->
    <?php if (!empty($flash)): ?>
    <div class="flash-toast flash-<?= htmlspecialchars($flash['type']) ?>" id="flashToast">
        <div class="flash-icon-wrap">
            <span class="material-symbols-outlined">
                <?= $flash['type'] === 'success' ? 'check_circle' : 'error' ?>
            </span>
        </div>
        <div class="flash-content">
            <div class="flash-title"><?= htmlspecialchars($flash['title'] ?? 'Notification') ?></div>
            <div class="flash-msg"><?= htmlspecialchars($flash['message']) ?></div>
        </div>
        <button class="flash-close" onclick="closeToast()" type="button" aria-label="Close notification">
            <span class="material-symbols-outlined">close</span>
        </button>
        <div class="flash-progress"></div>
    </div>
    <?php endif; ?>

    <div class="admin-profile-container">

        <!-- Breadcrumb & Header -->
        <div class="profile-breadcrumb">
            <a href="dashboard.php">Dashboard</a>
            <span class="material-symbols-outlined" style="font-size:14px;">chevron_right</span>
            <span>Profile & Settings</span>
        </div>

        <div class="profile-title-row">
            <h1 class="profile-title">Admin Profile & Settings</h1>
            <p class="profile-subtitle">Manage your personal administrator account details, profile picture, and security credentials.</p>
        </div>

        <div class="profile-grid">

            <!-- ── Left Column: Profile Card ── -->
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

            <!-- ── Right Column: Edit Details & Password ── -->
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
                                        <input type="password" name="new_password" id="newPass" required placeholder="Minimum 6 characters" minlength="6">
                                        <button type="button" class="password-toggle-btn" onclick="togglePassVisibility('newPass', this)">
                                            <span class="material-symbols-outlined">visibility</span>
                                        </button>
                                    </div>
                                </div>
                                <div class="profile-form-group">
                                    <label>Confirm New Password *</label>
                                    <div class="password-input-wrap">
                                        <input type="password" name="confirm_password" id="confPass" required placeholder="Re-type new password">
                                        <button type="button" class="password-toggle-btn" onclick="togglePassVisibility('confPass', this)">
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

        </div>

    </div>

</main>

<footer class="footer">
    <div>&copy; 2026 SkillBridge. All rights reserved.</div>
    <div class="footer-links">
        <a href="#">Help Center</a>
        <a href="#">Privacy Policy</a>
        <a href="#">Terms of Service</a>
    </div>
</footer>

<script>
    // Live Profile Photo Preview and Immediate Submit
    function handlePhotoSelect(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function (e) {
                const preview = document.getElementById('avatarPreview');
                const initial = document.getElementById('avatarInitial');
                if (preview) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                }
                if (initial) {
                    initial.style.display = 'none';
                }
                // Auto submit form to upload photo immediately
                document.getElementById('photoUploadForm').submit();
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    // Toggle Password Visibility
    function togglePassVisibility(inputId, btn) {
        const input = document.getElementById(inputId);
        const icon = btn.querySelector('.material-symbols-outlined');
        if (input.type === 'password') {
            input.type = 'text';
            icon.innerText = 'visibility_off';
        } else {
            input.type = 'password';
            icon.innerText = 'visibility';
        }
    }

    // Password Match Validation
    const newPass = document.getElementById('newPass');
    const confPass = document.getElementById('confPass');
    const passMsg = document.getElementById('passMatchMsg');

    function checkPassMatch() {
        if (!confPass.value) {
            passMsg.style.display = 'none';
            return;
        }
        passMsg.style.display = 'block';
        if (newPass.value === confPass.value) {
            passMsg.style.color = '#10b981';
            passMsg.innerText = '✓ Passwords match';
        } else {
            passMsg.style.color = '#ef4444';
            passMsg.innerText = '✗ Passwords do not match';
        }
    }

    newPass.addEventListener('input', checkPassMatch);
    confPass.addEventListener('input', checkPassMatch);

    // Flash Toast Dismissal
    function closeToast() {
        const toast = document.getElementById('flashToast');
        if (toast) {
            toast.style.animation = 'toastSlideOut 0.3s cubic-bezier(0.16, 1, 0.3, 1) forwards';
            setTimeout(() => { toast.remove(); }, 300);
        }
    }

    const activeToast = document.getElementById('flashToast');
    if (activeToast) {
        setTimeout(closeToast, 4000);
    }
</script>

<?php include "../../../Includes/dash_footer.php"; ?>
