<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once __DIR__ . '/../../Config/db.php';
require_once __DIR__ . '/../../Session/Session.php';
require_once __DIR__ . '/profile_completion.php';
require_once __DIR__ . '/check_student_access.php';

require_login();
require_role('student');
$user = current_user();
if (!$user) {
    exit('Session expired');
}
$email = $user['Email'] ?? $user['email'] ?? null;
if (!$email) {
    exit('Email not found');
}

if (empty($_SESSION['student_settings_csrf'])) {
    $_SESSION['student_settings_csrf'] = bin2hex(random_bytes(32));
}
$csrfToken = $_SESSION['student_settings_csrf'];
$password_error = '';
$password_success = '';
$profile_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($csrfToken, (string) ($_POST['csrf_token'] ?? ''))) {
        http_response_code(403);
        exit('Invalid or expired form token. Refresh and try again.');
    }

    if (isset($_POST['change_password'])) {
        $current_password = (string) ($_POST['current_password'] ?? '');
        $new_password = (string) ($_POST['new_password'] ?? '');
        $confirm_password = (string) ($_POST['confirm_password'] ?? '');

        if ($new_password !== $confirm_password) {
            $password_error = 'New passwords do not match.';
        } elseif (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/', $new_password)) {
            $password_error = 'Password must be at least 8 characters and contain uppercase, lowercase, and numbers.';
        } else {
            $stmt = $conn->prepare('SELECT password FROM user WHERE Email = ?');
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $userData = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if (!$userData) {
                $password_error = 'User not found.';
            } else {
                $storedHash = (string) $userData['password'];
                // Supports old SHA-1 records until the shared login code is migrated.
                $isLegacySha1 = preg_match('/^[a-f0-9]{40}$/i', $storedHash) === 1;
                $verified = $isLegacySha1
                    ? hash_equals(strtolower($storedHash), sha1($current_password))
                    : password_verify($current_password, $storedHash);

                if (!$verified) {
                    $password_error = 'Current password is incorrect.';
                } else {
                    // Compatibility: the existing SkillBridge login may still require SHA-1.
                    // Once Auth/login.php supports password_verify(), change this to:
                    // $newHash = password_hash($new_password, PASSWORD_DEFAULT);
                    $newHash = $isLegacySha1 ? sha1($new_password) : password_hash($new_password, PASSWORD_DEFAULT);
                    $update = $conn->prepare('UPDATE user SET password = ? WHERE Email = ?');
                    $update->bind_param('ss', $newHash, $email);
                    if ($update->execute()) {
                        $password_success = 'Password updated successfully.';
                    } else {
                        $password_error = 'Unable to update password. Please try again.';
                    }
                    $update->close();
                }
            }
        }
    }

    if (isset($_POST['save_changes'])) {
        $name = trim((string) ($_POST['name'] ?? ''));
        $bio = trim((string) ($_POST['bio'] ?? ''));
        $github = trim((string) ($_POST['github'] ?? ''));
        $linkedin = trim((string) ($_POST['linkedin'] ?? ''));
        $website = trim((string) ($_POST['website'] ?? ''));

        // University, degree and year are read-only: don't trust submitted copies.
        $oldStmt = $conn->prepare('SELECT profile_image FROM student WHERE Email = ?');
        $oldStmt->bind_param('s', $email);
        $oldStmt->execute();
        $existing = $oldStmt->get_result()->fetch_assoc();
        $oldStmt->close();

        if (!$existing) {
            $profile_error = 'Student profile not found.';
        } elseif ($name === '' || mb_strlen($name) > 150) {
            $profile_error = 'Please enter a valid name (up to 150 characters).';
        } elseif (mb_strlen($bio) > 3000) {
            $profile_error = 'About Me is too long.';
        } else {
            foreach (['GitHub' => $github, 'LinkedIn' => $linkedin, 'Website' => $website] as $label => $url) {
                if ($url === '') continue;
                $parts = parse_url($url);
                $scheme = strtolower((string) ($parts['scheme'] ?? ''));
                $host = strtolower((string) ($parts['host'] ?? ''));
                if (!filter_var($url, FILTER_VALIDATE_URL) || !in_array($scheme, ['http', 'https'], true)) {
                    $profile_error = 'Please enter a valid ' . $label . ' URL.';
                    break;
                }
                if ($label === 'GitHub' && !in_array($host, ['github.com', 'www.github.com'], true)) {
                    $profile_error = 'Please enter a valid GitHub URL.';
                    break;
                }
                if ($label === 'LinkedIn' && !in_array($host, ['linkedin.com', 'www.linkedin.com'], true)) {
                    $profile_error = 'Please enter a valid LinkedIn URL.';
                    break;
                }
            }
        }

        $oldImage = $existing['profile_image'] ?? null;
        $image = $oldImage;
        $newFilePath = null;
        $folder = __DIR__ . '/../../Assets/Images/Student/';
        if ($profile_error === '') {
            $remove = ($_POST['remove_profile_image'] ?? '') === '1';
            if ($remove) $image = null;

            if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] !== UPLOAD_ERR_NO_FILE) {
                $upload = $_FILES['profile_image'];
                if ($upload['error'] !== UPLOAD_ERR_OK || $upload['size'] > 5 * 1024 * 1024) {
                    $profile_error = 'Image upload failed or exceeds 5 MB.';
                } elseif (!is_uploaded_file($upload['tmp_name'])) {
                    $profile_error = 'Invalid uploaded image.';
                } else {
                    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($upload['tmp_name']);
                    $extensions = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
                    if (!isset($extensions[$mime]) || @getimagesize($upload['tmp_name']) === false) {
                        $profile_error = 'Upload a valid JPG, PNG or WebP image.';
                    } else {
                        if (!is_dir($folder) && !mkdir($folder, 0755, true)) {
                            $profile_error = 'Could not create the image folder.';
                        } else {
                            $image = 'profile_' . bin2hex(random_bytes(12)) . '.' . $extensions[$mime];
                            $newFilePath = $folder . $image;
                            if (!move_uploaded_file($upload['tmp_name'], $newFilePath)) {
                                $profile_error = 'Image upload failed.';
                                $newFilePath = null;
                            }
                        }
                    }
                }
            }
        }

        if ($profile_error === '') {
            $update = $conn->prepare('UPDATE student SET Name = ?, bio = ?, github = ?, linkedin = ?, website = ?, profile_image = ? WHERE Email = ?');
            $update->bind_param('sssssss', $name, $bio, $github, $linkedin, $website, $image, $email);
            $saved = $update->execute();
            $update->close();
            if ($saved) {
                if ($oldImage && $image !== $oldImage) {
                    // Don't delete the application's shared placeholder image.
                    $oldFilePath = $folder . basename($oldImage);
                    if ($oldImage !== 'profile.webp' && is_file($oldFilePath)) @unlink($oldFilePath);
                }
                header('Location: settings.php?success=1');
                exit;
            }
            if ($newFilePath && is_file($newFilePath)) @unlink($newFilePath);
            $profile_error = 'Could not save profile changes.';
        } elseif ($newFilePath && is_file($newFilePath)) {
            @unlink($newFilePath);
        }
    }
}

$stmt = $conn->prepare('SELECT student.* FROM student WHERE Email = ?');
$stmt->bind_param('s', $email);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$student) exit('Student profile not found');

function studentCount(mysqli $conn, string $table, string $email): int {

    // Called only with fixed table names below.
    $q = $conn->prepare("SELECT COUNT(*) AS total FROM `$table` WHERE Email = ?");
    $q->bind_param('s', $email);
    $q->execute();
    $count = (int) ($q->get_result()->fetch_assoc()['total'] ?? 0);
    $q->close();
    return $count;
}
$skill_count = studentCount($conn, 'skills', $email);
$certificate_count = studentCount($conn, 'certificates', $email);
$project_count = 0; // Replace when your project tracking module is implemented.
$completion = calculateProfileCompletion($student, $skill_count, $certificate_count, $project_count);

function esc($value): string {
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
$photo = !empty($student['profile_image']) ? basename($student['profile_image']) : 'profile.webp';
$photoSrc = '../../Assets/Images/Student/' . rawurlencode($photo);
$pageTitle = 'My Profile | SkillBridge';
$extra_css = '<link rel="stylesheet" href="../../Assets/CSS/Student/settings.css">' . "\n" .
    '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">';

include __DIR__ . '/../../Includes/student_sidebar.php';
include __DIR__ . '/../../Includes/dash_header.php';
?>
<div class="profile-container">
    <?php if (isset($_GET['success'])): ?>
        <p role="status" class="success-message">Profile updated successfully.</p>
    <?php endif; ?>
    <?php if ($profile_error): ?>
        <p role="alert" class="error-message"><?= esc($profile_error) ?></p>
    <?php endif; ?>

    <form class="profile-form" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= esc($csrfToken) ?>">
        <div class="profile-title">
            <div>
                <h2>Settings &gt; My Profile</h2>
                <p>Manage your academic identity and professional presence.</p>
            </div>
            <div class="profile-actions">
                <button type="button" class="share-btn" onclick="shareProfile()"><i class="fa-solid fa-share-nodes"></i> Share Profile</button>
                <button type="submit" name="save_changes" value="1" class="save-btn"><i class="fa-solid fa-floppy-disk"></i> Save Changes</button>
            </div>
        </div>

        <div class="profile-card">
            <div class="avatar">
                <?php
                $hasProfileImage = !empty($student['profile_image']);

                $studentName = trim($student['Name'] ?? '');
                $firstLetter = $studentName !== ''
                    ? strtoupper(mb_substr($studentName, 0, 1))
                    : 'S';
                ?>

                <!-- Uploaded Profile Image -->
                <img
                    id="profilePreview"
                    src="<?= $hasProfileImage ? esc($photoSrc) : '' ?>"
                    alt="Student profile image"
                    style="<?= $hasProfileImage ? '' : 'display:none;' ?>"
                    onerror="this.style.display='none'; document.getElementById('profileInitial').style.display='flex';"
                >

                <!-- Default Avatar -->
                <div
                    id="profileInitial"
                    class="profile-initial"
                    style="<?= $hasProfileImage ? 'display:none;' : 'display:flex;' ?>"
                >
                    <?= esc($firstLetter) ?>
                </div>

                <!-- Upload Profile Image -->
                <label class="camera" aria-label="Upload profile image">
                    <i class="fa-solid fa-camera"></i>
                    <input
                        type="file"
                        name="profile_image"
                        accept="image/jpeg,image/png,image/webp"
                        onchange="previewImage(event)"
                    >
                </label>

                <!-- Remove Profile Image -->
                <?php if ($hasProfileImage): ?>
                    <button
                        type="button"
                        class="remove-image"
                        onclick="removeProfileImage()"
                    >
                        <i class="fa-solid fa-trash"></i>
                        Remove Photo
                    </button>
                <?php endif; ?>
            </div>
            <div class="profile-details">
                <h2><?= esc($student['Name']) ?></h2>
                <p>🎓 <?= esc($student['degree']) ?></p>
                <p><?= esc($student['University']) ?> • Year <?= esc($student['year']) ?></p>
                <div class="profile-stats">
                    <div>Profile Strength <strong><?= (int) $completion ?>%</strong></div>
                    <div>Projects <strong><?= $project_count ?></strong></div>
                    <div>Skills <strong><?= $skill_count ?></strong></div>
                </div>
            </div>
        </div>

        <div class="profile-layout">
            <div class="box">
                <h2>Personal Information</h2>
                <label for="studentName">Full Name</label>
                <input id="studentName" type="text" name="name" maxlength="150" value="<?= esc($student['Name']) ?>" required>
                <label for="studentUniversity">University</label>
                <input id="studentUniversity" type="text" value="<?= esc($student['University']) ?>" readonly>
                <label for="studentDegree">Degree</label>
                <input id="studentDegree" type="text" value="<?= esc($student['degree']) ?>" readonly>
                <label for="studentYear">Year</label>
                <input id="studentYear" type="text" value="<?= esc($student['year']) ?>" readonly>
            </div>
            <div class="box">
                <h2>Professional Presence</h2>
                <label for="studentBio">About Me</label>
                <textarea id="studentBio" name="bio" maxlength="3000"><?= esc($student['bio'] ?? '') ?></textarea>
                <label for="studentGithub">GitHub</label>
                <input id="studentGithub" type="url" name="github" value="<?= esc($student['github'] ?? '') ?>" placeholder="https://github.com/username">
                <label for="studentLinkedin">LinkedIn</label>
                <input id="studentLinkedin" type="url" name="linkedin" value="<?= esc($student['linkedin'] ?? '') ?>" placeholder="https://linkedin.com/in/username">
                <label for="studentWebsite">Website</label>
                <input id="studentWebsite" type="url" name="website" value="<?= esc($student['website'] ?? '') ?>" placeholder="https://yourwebsite.com">
            </div>
        </div>
    </form>

    <div class="box security-box">
        <h2>Security &amp; Password</h2>
        <?php if ($password_error): ?><p role="alert" class="error-message"><?= esc($password_error) ?></p><?php endif; ?>
        <?php if ($password_success): ?><p role="status" class="success-message"><?= esc($password_success) ?></p><?php endif; ?>
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= esc($csrfToken) ?>">
            <label for="studentCurrentPass">Current Password *</label>
            <div class="password-field">
                <input id="studentCurrentPass" type="password" name="current_password" autocomplete="current-password" placeholder="Enter current password" required>
                <i class="fa-solid fa-eye" role="button" tabindex="0" aria-label="Show or hide password"></i>
            </div>
            <div class="password-row">
                <div>
                    <label for="studentNewPass">New Password *</label>
                    <div class="password-field">
                        <input type="password" name="new_password" id="studentNewPass" autocomplete="new-password" placeholder="Min 8 chars with uppercase, lowercase &amp; numbers" minlength="8" pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}" required>
                        <i class="fa-solid fa-eye" role="button" tabindex="0" aria-label="Show or hide password"></i>
                    </div>
                    <div class="password-requirements" id="studentPassRequirements">
                        <div class="req-item" id="studReqLength"><span class="req-icon">✕</span><span>At least 8 characters</span></div>
                        <div class="req-item" id="studReqUpper"><span class="req-icon">✕</span><span>At least 1 uppercase (A-Z)</span></div>
                        <div class="req-item" id="studReqLower"><span class="req-icon">✕</span><span>At least 1 lowercase (a-z)</span></div>
                        <div class="req-item" id="studReqNum"><span class="req-icon">✕</span><span>At least 1 number (0-9)</span></div>
                    </div>
                </div>
                <div>
                    <label for="studentConfPass">Confirm New Password *</label>
                    <div class="password-field">
                        <input type="password" name="confirm_password" id="studentConfPass" autocomplete="new-password" placeholder="Re-type new password" minlength="8" required>
                        <i class="fa-solid fa-eye" role="button" tabindex="0" aria-label="Show or hide password"></i>
                    </div>
                </div>
            </div>
            <div id="studentPassMatchMsg" style="font-size:12.5px; margin-bottom:14px; display:none;"></div>
            <button type="submit" name="change_password" value="1" class="password-btn"><i class="fa-solid fa-key"></i> Update Password</button>
        </form>
    </div>
</div>

<script>
    
    function previewImage(event) {
        const image = document.getElementById("profilePreview");
        const initial = document.getElementById("profileInitial");
        const file = event.target.files[0];

        if (file) {
            image.src = URL.createObjectURL(file);
            image.style.display = "block";
            initial.style.display = "none";
        }
    }
    function showToast(message, type) {
        const toast = document.createElement('div');
        toast.className = 'toast ' + type;
        const icon = document.createElement('i');
        icon.className = 'fa-solid ' + (type === 'error' ? 'fa-circle-xmark' : 'fa-circle-check');
        const span = document.createElement('span');
        span.textContent = message;
        toast.append(icon, span);
        document.body.appendChild(toast);
        setTimeout(() => toast.classList.add('show'), 100);
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }
    function shareProfile() {
        const email = <?= json_encode((string)$email, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
        const base = <?= json_encode((string)($BASE_URL ?? '/Skill_Bridge_Group_Project')) ?>;
        const url = window.location.origin + base + '/Functions/Student/public_profile.php?email=' + encodeURIComponent(email);
        if (!navigator.clipboard) return showToast('Clipboard is not available.', 'error');
        navigator.clipboard.writeText(url)
            .then(() => showToast('Public profile link copied successfully!', 'success'))
            .catch(() => showToast('Failed to copy profile link.', 'error'));
    }
    function removeProfileImage() {
        if (!confirm('Remove current profile image?')) return;
        const form = document.querySelector('form.profile-form');
        for (const [name, value] of [['remove_profile_image', '1'], ['save_changes', '1']]) {
            const input = document.createElement('input');
            input.type = 'hidden'; input.name = name; input.value = value;
            form.appendChild(input);
        }
        form.submit();
    }
    document.querySelectorAll('.password-field i').forEach(icon => {
        icon.addEventListener('mousedown', e => e.preventDefault());
        const toggle = () => {
            const input = icon.parentElement.querySelector('input');
            input.type = input.type === 'password' ? 'text' : 'password';
            icon.classList.toggle('fa-eye', input.type === 'password');
            icon.classList.toggle('fa-eye-slash', input.type !== 'password');
        };
        icon.addEventListener('click', toggle);
        icon.addEventListener('keydown', e => { if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); toggle(); } });
    });
    const studNewPass = document.getElementById('studentNewPass');
    const studConfPass = document.getElementById('studentConfPass');
    const studPassMsg = document.getElementById('studentPassMatchMsg');
    const studReqBox = document.getElementById('studentPassRequirements');
    function updateStudReqItem(id, valid) {
        const item = document.getElementById(id);
        if (!item) return;
        item.classList.toggle('valid', valid);
        item.querySelector('.req-icon').textContent = valid ? '✓' : '✕';
    }
    function checkStudPasswordRequirements() {
        const val = studNewPass.value;
        updateStudReqItem('studReqLength', val.length >= 8);
        updateStudReqItem('studReqUpper', /[A-Z]/.test(val));
        updateStudReqItem('studReqLower', /[a-z]/.test(val));
        updateStudReqItem('studReqNum', /\d/.test(val));
    }
    function checkStudPassMatch() {
        if (!studConfPass.value) { studPassMsg.style.display = 'none'; return; }
        studPassMsg.style.display = 'block';
        const match = studNewPass.value === studConfPass.value;
        studPassMsg.style.color = match ? '#10b981' : '#ef4444';
        studPassMsg.textContent = match ? '✓ Passwords match' : '✗ Passwords do not match';
    }
    studNewPass.addEventListener('focus', () => { studReqBox.classList.add('show'); checkStudPasswordRequirements(); });
    studNewPass.addEventListener('blur', () => studReqBox.classList.remove('show'));
    studNewPass.addEventListener('input', () => { checkStudPasswordRequirements(); checkStudPassMatch(); });
    studConfPass.addEventListener('input', checkStudPassMatch);
</script>
<?php include __DIR__ . '/../../Includes/dash_footer.php'; ?>
