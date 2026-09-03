<?php
require_once __DIR__ . "/../../../Config/db.php";
require_once __DIR__ . "/../../../Session/session.php";
require_once __DIR__ . "/Company_Backend.php";

is_logged_in();
$user = current_user();
$email = strtolower($user['Email'] ?? $user['email'] ?? '');

$flash = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // ── 1. Upload Company Profile Photo ──
    if ($action === 'upload_photo') {
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
                $targetDir = __DIR__ . '/../../../Assets/Images/Company/';
                if (!is_dir($targetDir)) {
                    mkdir($targetDir, 0777, true);
                }

                $newFileName = 'company_' . time() . '_' . uniqid() . '.' . $fileExt;
                $destPath = $targetDir . $newFileName;

                if (move_uploaded_file($fileTmp, $destPath)) {
                    $updated = $companyDB->updateCompanyProfileImage($email, $newFileName);
                    if ($updated) {
                        $flash = ['type' => 'success', 'title' => 'Photo Updated', 'message' => 'Your company profile image has been updated successfully.'];
                    } else {
                        $flash = ['type' => 'error', 'title' => 'Update Failed', 'message' => 'Failed to save photo name in database.'];
                    }
                } else {
                    $flash = ['type' => 'error', 'title' => 'Upload Failed', 'message' => 'Failed to save uploaded image.'];
                }
            }
        } else {
            $flash = ['type' => 'error', 'title' => 'Upload Error', 'message' => 'Please choose a valid image file to upload.'];
        }
    }

    // ── 2. Remove Profile Photo ──
    elseif ($action === 'remove_photo') {
        $removed = $companyDB->removeCompanyProfileImage($email);
        if ($removed) {
            $flash = ['type' => 'success', 'title' => 'Photo Removed', 'message' => 'Your profile photo has been removed.'];
        } else {
            $flash = ['type' => 'error', 'title' => 'Action Failed', 'message' => 'Could not remove profile photo.'];
        }
    }

    // ── 3. Update Company Profile Details ──
    elseif ($action === 'update_profile') {
        $companyName   = trim($_POST['company_name'] ?? '');
        $companyType   = trim($_POST['company_type'] ?? '');
        $contactPerson = trim($_POST['contact_person'] ?? '');
        $contactNumber = trim($_POST['contact_number'] ?? '');
        $website       = trim($_POST['website'] ?? '');
        $location      = trim($_POST['location'] ?? '');

        if (empty($companyName)) {
            $flash = ['type' => 'error', 'title' => 'Validation Error', 'message' => 'Company Name cannot be empty.'];
        } elseif (empty($contactPerson)) {
            $flash = ['type' => 'error', 'title' => 'Validation Error', 'message' => 'Contact Person name cannot be empty.'];
        } else {
            $updated = $companyDB->updateCompanyProfile(
                $email,
                $companyName,
                $companyType,
                $contactPerson,
                $contactNumber,
                $website,
                $location
            );

            if ($updated) {
                // Update session name if present
                if (isset($_SESSION['user'])) {
                    $_SESSION['user']['username'] = $contactPerson;
                }
                $flash = ['type' => 'success', 'title' => 'Profile Updated', 'message' => 'Your company details have been updated successfully.'];
            } else {
                $flash = ['type' => 'error', 'title' => 'Update Failed', 'message' => 'Failed to save company information to the database.'];
            }
        }
    }

    // ── 4. Change Company Account Password ──
    elseif ($action === 'change_password') {
        $currPass = $_POST['current_password'] ?? '';
        $newPass  = $_POST['new_password'] ?? '';
        $confPass = $_POST['confirm_password'] ?? '';

        if (empty($currPass) || empty($newPass) || empty($confPass)) {
            $flash = ['type' => 'error', 'title' => 'Missing Fields', 'message' => 'Please fill in all password fields.'];
        } elseif (strlen($newPass) < 6) {
            $flash = ['type' => 'error', 'title' => 'Weak Password', 'message' => 'New password must be at least 6 characters long.'];
        } elseif ($newPass !== $confPass) {
            $flash = ['type' => 'error', 'title' => 'Password Mismatch', 'message' => 'New password and confirmation password do not match.'];
        } else {
            $res = $companyDB->changeCompanyPassword($email, $currPass, $newPass);
            if ($res['success']) {
                $flash = ['type' => 'success', 'title' => 'Password Changed', 'message' => $res['message']];
            } else {
                $flash = ['type' => 'error', 'title' => 'Security Error', 'message' => $res['message']];
            }
        }
    }
}

// Fetch latest company profile
$profile = $companyDB->getCompanyProfile($email);

$companyName   = $profile['company_name'] ?? 'HR Pvt LTD';
$companyType   = $profile['company_type'] ?? 'IT';
$contactPerson = $profile['contact_person'] ?? 'Nadeeka Munasingha';
$contactNumber = $profile['contact_number'] ?? '';
$website       = $profile['website'] ?? '';
$location      = $profile['location'] ?? '';
$status        = $profile['company_status'] ?? 'Active';
$companyPhoto  = $profile['profile_image'] ?? '';
$memberSince   = !empty($profile['created_at']) ? date('M d, Y', strtotime($profile['created_at'])) : 'Recent Partner';
$initial       = !empty(trim($companyName)) ? strtoupper(mb_substr(trim($companyName), 0, 1)) : 'C';

// Resolve photo URL
$photoUrl = null;
if (!empty($companyPhoto) && file_exists(__DIR__ . '/../../../Assets/Images/Company/' . $companyPhoto)) {
    $photoUrl = $GLOBALS['BASE_URL'] . '/Assets/Images/Company/' . htmlspecialchars($companyPhoto);
}

include "../../../Includes/company_sidebar.php";
?>
<link rel="stylesheet" href="../../../Assets/CSS/flash-toast.css">
<link rel="stylesheet" href="../../../Assets/CSS/Company/profile.css?v=<?php echo time(); ?>">
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

    <div class="company-profile-wrapper">

        <!-- Breadcrumb & Header -->
        <div class="profile-breadcrumb">
            <a href="dashboard.php">Dashboard</a>
            <span class="material-symbols-outlined">chevron_right</span>
            <span>Profile & Settings</span>
        </div>

        <div class="profile-title-row">
            <h1>Company Profile & Settings</h1>
            <p>Manage your corporate identity, recruiter contacts, and account security credentials.</p>
        </div>

        <div class="profile-layout-grid">

            <!--Left Column: Company Showcase Card-->
            <div class="profile-left-col">
                <div class="profile-section-card">
                    <div class="profile-card-header">
                        <h2>
                            <span class="material-symbols-outlined">corporate_fare</span>
                            Organization Overview
                        </h2>
                    </div>
                    <div class="profile-card-body">
                        <div class="company-showcase">
                            
                            <!-- Profile Photo Form & Upload Container -->
                            <form method="POST" action="" enctype="multipart/form-data" id="companyPhotoForm" style="display:flex; flex-direction:column; align-items:center;">
                                <input type="hidden" name="action" value="upload_photo">
                                <div class="company-avatar-container" onclick="document.getElementById('companyPhotoInput').click()" title="Click to upload / change profile photo">
                                    <?php if (!empty($photoUrl)): ?>
                                        <img src="<?= $photoUrl ?>?v=<?= time() ?>" alt="<?= htmlspecialchars($companyName) ?>" class="company-photo-img" id="companyPhotoPreview">
                                    <?php else: ?>
                                        <div class="company-avatar-emblem" id="companyAvatarInitial">
                                            <?= htmlspecialchars($initial) ?>
                                        </div>
                                        <img src="" alt="Preview" class="company-photo-img" id="companyPhotoPreview" style="display:none;">
                                    <?php endif; ?>

                                    <div class="avatar-hover-overlay">
                                        <span class="material-symbols-outlined">photo_camera</span>
                                        <span>Change</span>
                                    </div>
                                    <input type="file" name="profile_image" id="companyPhotoInput" accept="image/png, image/jpeg, image/jpg, image/webp" style="display:none;" onchange="handlePhotoUpload(this)">
                                </div>
                            </form>

                            <?php if (!empty($photoUrl)): ?>
                                <form method="POST" action="" style="margin-top:-6px; margin-bottom:8px;">
                                    <input type="hidden" name="action" value="remove_photo">
                                    <button type="submit" class="avatar-remove-btn" onclick="return confirm('Are you sure you want to remove the company profile image?')">
                                        <span class="material-symbols-outlined" style="font-size:14px;">delete</span>
                                        Remove Photo
                                    </button>
                                </form>
                            <?php endif; ?>

                            <h2><?= htmlspecialchars($companyName) ?></h2>
                            <p class="contact-sub">
                                <span class="material-symbols-outlined" style="font-size:14px; vertical-align:middle;">person</span>
                                <?= htmlspecialchars(!empty($contactPerson) ? $contactPerson : 'Primary Recruiter') ?>
                            </p>
                            <div class="company-badge-row">
                                <span class="badge-pill verified">
                                    <span class="material-symbols-outlined">verified</span> Verified Employer
                                </span>
                                <span class="badge-pill industry">
                                    <?= htmlspecialchars(!empty($companyType) ? $companyType : 'General') ?>
                                </span>
                            </div>
                        </div>

                        <!-- Showcase Meta Info -->
                        <div class="showcase-meta-list">
                            <div class="showcase-meta-item">
                                <span class="material-symbols-outlined">mail</span>
                                <div>
                                    <strong>Email:</strong>
                                    <span><?= htmlspecialchars($email) ?></span>
                                </div>
                            </div>
                            <div class="showcase-meta-item">
                                <span class="material-symbols-outlined">call</span>
                                <div>
                                    <strong>Contact:</strong>
                                    <span><?= htmlspecialchars(!empty($contactNumber) ? $contactNumber : 'Not provided') ?></span>
                                </div>
                            </div>
                            <?php if (!empty($website)): ?>
                            <div class="showcase-meta-item">
                                <span class="material-symbols-outlined">language</span>
                                <div>
                                    <strong>Website:</strong>
                                    <a href="<?= htmlspecialchars(str_starts_with($website, 'http') ? $website : 'https://' . $website) ?>" target="_blank" rel="noopener noreferrer">
                                        <?= htmlspecialchars($website) ?>
                                    </a>
                                </div>
                            </div>
                            <?php endif; ?>
                            <?php if (!empty($location)): ?>
                            <div class="showcase-meta-item">
                                <span class="material-symbols-outlined">location_on</span>
                                <div>
                                    <strong>Location:</strong>
                                    <span><?= htmlspecialchars($location) ?></span>
                                </div>
                            </div>
                            <?php endif; ?>
                            <div class="showcase-meta-item">
                                <span class="material-symbols-outlined">calendar_today</span>
                                <div>
                                    <strong>Joined:</strong>
                                    <span><?= htmlspecialchars($memberSince) ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!--Right Column: Edit Forms -->
            <div class="profile-right-col">

                <!--Company Information Form -->
                <div class="profile-section-card">
                    <div class="profile-card-header">
                        <h2>
                            <span class="material-symbols-outlined">business</span>
                            Company Details
                        </h2>
                    </div>
                    <div class="profile-card-body">
                        <form method="POST" action="">
                            <input type="hidden" name="action" value="update_profile">

                            <div class="form-grid-2">
                                <div class="form-group">
                                    <label for="company_name">Company Name *</label>
                                    <div class="input-with-icon">
                                        <span class="material-symbols-outlined">apartment</span>
                                        <input type="text" id="company_name" name="company_name" value="<?= htmlspecialchars($companyName) ?>" required placeholder="e.g. Acme Corporation">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="company_type">Industry / Sector</label>
                                    <div class="input-with-icon">
                                        <span class="material-symbols-outlined">category</span>
                                        <input type="text" id="company_type" name="company_type" value="<?= htmlspecialchars($companyType) ?>" placeholder="e.g. Information Technology, Finance">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="contact_person">Contact Person (HR / Recruiter) *</label>
                                    <div class="input-with-icon">
                                        <span class="material-symbols-outlined">person</span>
                                        <input type="text" id="contact_person" name="contact_person" value="<?= htmlspecialchars($contactPerson) ?>" required placeholder="e.g. John Doe">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="contact_number">Contact Number</label>
                                    <div class="input-with-icon">
                                        <span class="material-symbols-outlined">phone</span>
                                        <input type="text" id="contact_number" name="contact_number" value="<?= htmlspecialchars($contactNumber) ?>" placeholder="e.g. +94 77 123 4567">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="website">Company Website</label>
                                    <div class="input-with-icon">
                                        <span class="material-symbols-outlined">link</span>
                                        <input type="text" id="website" name="website" value="<?= htmlspecialchars($website) ?>" placeholder="e.g. www.company.com">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="location">Head Office / Location</label>
                                    <div class="input-with-icon">
                                        <span class="material-symbols-outlined">pin_drop</span>
                                        <input type="text" id="location" name="location" value="<?= htmlspecialchars($location) ?>" placeholder="e.g. Colombo 03, Sri Lanka">
                                    </div>
                                </div>

                                <div class="form-group full-width">
                                    <label for="registered_email">Registered Email Address</label>
                                    <div class="input-with-icon">
                                        <span class="material-symbols-outlined">lock</span>
                                        <input type="email" id="registered_email" value="<?= htmlspecialchars($email) ?>" readonly disabled>
                                    </div>
                                    <span class="field-hint">Primary account email cannot be changed directly. Contact administrator for support.</span>
                                </div>
                            </div>

                            <div class="form-actions-row">
                                <button type="submit" class="btn-save-profile">
                                    <span class="material-symbols-outlined">save</span>
                                    Save Changes
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!--Security & Password Form -->
                <div class="profile-section-card">
                    <div class="profile-card-header">
                        <h2>
                            <span class="material-symbols-outlined">lock_reset</span>
                            Account Security & Password
                        </h2>
                    </div>
                    <div class="profile-card-body">
                        <div class="password-requirements-box">
                            <h4>
                                <span class="material-symbols-outlined">info</span>
                                Password Security Tips
                            </h4>
                            <ul>
                                <li>Use at least 6 characters with a combination of letters and numbers.</li>
                                <li>Ensure your new password differs from previous credentials.</li>
                            </ul>
                        </div>

                        <form method="POST" action="">
                            <input type="hidden" name="action" value="change_password">

                            <div class="form-grid-2">
                                <div class="form-group full-width">
                                    <label for="current_password">Current Password *</label>
                                    <div class="input-with-icon">
                                        <span class="material-symbols-outlined">key</span>
                                        <input type="password" id="current_password" name="current_password" required placeholder="Enter current password">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="new_password">New Password *</label>
                                    <div class="input-with-icon">
                                        <span class="material-symbols-outlined">lock</span>
                                        <input type="password" id="new_password" name="new_password" required minlength="6" placeholder="Minimum 6 characters">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="confirm_password">Confirm New Password *</label>
                                    <div class="input-with-icon">
                                        <span class="material-symbols-outlined">check_circle</span>
                                        <input type="password" id="confirm_password" name="confirm_password" required minlength="6" placeholder="Repeat new password">
                                    </div>
                                </div>
                            </div>

                            <div class="form-actions-row">
                                <button type="submit" class="btn-save-profile btn-save-password">
                                    <span class="material-symbols-outlined">key</span>
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

<script>
    function handlePhotoUpload(input) {
        if (input.files && input.files[0]) {
            var file = input.files[0];

            // Instant preview before submission
            var reader = new FileReader();
            reader.onload = function(e) {
                var preview = document.getElementById('companyPhotoPreview');
                var initial = document.getElementById('companyAvatarInitial');
                if (preview) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                }
                if (initial) {
                    initial.style.display = 'none';
                }
            };
            reader.readAsDataURL(file);

            // Automatically submit the photo upload form
            document.getElementById('companyPhotoForm').submit();
        }
    }

    function closeToast() {
        var toast = document.getElementById('flashToast');
        if (toast) {
            toast.style.opacity = '0';
            toast.style.transform = 'translateY(-10px)';
            setTimeout(function() {
                toast.remove();
            }, 300);
        }
    }

    // Auto-dismiss toast after 5s
    setTimeout(function() {
        closeToast();
    }, 5000);
</script>

<?php include "../../../Includes/dash_footer.php"; ?>
