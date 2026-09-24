<?php

include "../../../Config/db.php";
include "../../../Session/Session.php";

require_role('organization');
$user = current_user();
$organization_email = $user['email'];

$flash = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name          = trim($_POST['org_name'] ?? '');
    $orgtype       = trim($_POST['industry'] ?? '');
    $contactNumber = trim($_POST['contact_number'] ?? '');
    $website       = trim($_POST['website'] ?? '');
    $location      = trim($_POST['address'] ?? '');
    $about         = trim($_POST['about'] ?? '');
    $linkedin      = trim($_POST['linkedin'] ?? '');
    $twitter       = trim($_POST['twitter'] ?? '');
    $facebook      = trim($_POST['facebook'] ?? '');

    // ---- Logo upload (optional) ----
    $logoPath = null;
    if (!empty($_FILES['logo']['name']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
        $maxSizeBytes = 2 * 1024 * 1024; // 2MB

        $fileType = mime_content_type($_FILES['logo']['tmp_name']);
        $fileSize = $_FILES['logo']['size'];

        if (!in_array($fileType, $allowedTypes)) {
            $flash = ['type' => 'error', 'message' => 'Logo must be a JPG, PNG, GIF or WEBP image.'];
        } elseif ($fileSize > $maxSizeBytes) {
            $flash = ['type' => 'error', 'message' => 'Logo file is too large (max 2MB).'];
        } else {
            $ext = pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION);
            $safeEmail = preg_replace('/[^a-zA-Z0-9]/', '_', $organization_email);
            $fileName = $safeEmail . '_' . time() . '.' . $ext;
            $destDir  = __DIR__ . '/../../../Assets/Uploads/org_logos/';
            $destPath = $destDir . $fileName;

            if (move_uploaded_file($_FILES['logo']['tmp_name'], $destPath)) {
                $logoPath = 'Assets/Uploads/org_logos/' . $fileName;
            } else {
                $flash = ['type' => 'error', 'message' => 'Failed to save the uploaded logo.'];
            }
        }
    }

    // ---- Logo removal (only when no new logo was uploaded in the same submit) ----
    $removeLogo = empty($flash) && $logoPath === null && ($_POST['remove_logo'] ?? '') === '1';
    $oldLogoFile = null;
    if ($removeLogo) {
        $q = $conn->prepare("SELECT logo FROM organization WHERE Email=?");
        $q->bind_param("s", $organization_email);
        $q->execute();
        $oldLogoFile = $q->get_result()->fetch_assoc()['logo'] ?? null;
    }

    if (empty($flash)) {
        if ($logoPath !== null) {
            $sql = "UPDATE organization
                    SET Name=?, orgtype=?, contactNumber=?, website=?, location=?, about=?, linkedin=?, twitter=?, facebook=?, logo=?
                    WHERE Email=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param(
                "sssssssssss",
                $name, $orgtype, $contactNumber, $website, $location, $about, $linkedin, $twitter, $facebook, $logoPath, $organization_email
            );
        } else {
            $logoSql = $removeLogo ? ", logo=NULL" : "";
            $sql = "UPDATE organization
                    SET Name=?, orgtype=?, contactNumber=?, website=?, location=?, about=?, linkedin=?, twitter=?, facebook=?" . $logoSql . "
                    WHERE Email=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param(
                "ssssssssss",
                $name, $orgtype, $contactNumber, $website, $location, $about, $linkedin, $twitter, $facebook, $organization_email
            );
        }

        if ($stmt->execute()) {
            $_SESSION['user']['username'] = $name;
            if ($removeLogo) {
                // delete the old image file too (only inside the org_logos folder)
                if (!empty($oldLogoFile)) {
                    $logoDir  = realpath(__DIR__ . '/../../../Assets/Uploads/org_logos');
                    $logoFile = realpath(__DIR__ . '/../../../' . $oldLogoFile);
                    if ($logoDir && $logoFile && strpos($logoFile, $logoDir) === 0 && is_file($logoFile)) {
                        @unlink($logoFile);
                    }
                }
                $flash = ['type' => 'success', 'message' => 'Logo removed successfully.'];
            } else {
                $flash = ['type' => 'success', 'message' => 'Profile updated successfully.'];
            }
        } else {
            $flash = ['type' => 'error', 'message' => 'Update failed. Please try again.'];
        }
    }
}

// Fetch current organization data (also picks up the just-saved values)
$stmt = $conn->prepare("SELECT * FROM organization WHERE Email=?");
$stmt->bind_param("s", $organization_email);
$stmt->execute();
$org = $stmt->get_result()->fetch_assoc();

// Stat cards — counted from the SAME project list Manage Projects shows
// (real projects first, then the demo projects that pad the list to 24 rows).
$useDemoData = true;          // false = count only real database projects
$demoTarget  = 24;
// demo projects in the same order as Manage Projects: [status, assigned students]
$demoPool = [
    ['open', 0], ['reviewing', 0], ['open', 0], ['closed', 2], ['inprogress', 1],
    ['open', 0], ['reviewing', 0], ['inprogress', 2], ['open', 0], ['closed', 3],
    ['open', 0], ['reviewing', 0], ['inprogress', 1], ['open', 0], ['closed', 2],
    ['reviewing', 0], ['open', 0], ['inprogress', 1], ['closed', 3], ['open', 0],
];

$projectRows = [];   // each: [status, assigned]
$stmt = $conn->prepare("SELECT p.status,
                               (SELECT COUNT(*) FROM student_projects sp WHERE sp.project_id = p.id) AS team_count
                        FROM projects p
                        WHERE p.organization_email = ?");
$stmt->bind_param("s", $organization_email);
$stmt->execute();
foreach ($stmt->get_result()->fetch_all(MYSQLI_ASSOC) as $r) {
    $projectRows[] = [$r['status'], (int)$r['team_count']];
}
if ($useDemoData) {
    $needed = max(0, $demoTarget - count($projectRows));
    $projectRows = array_merge($projectRows, array_slice($demoPool, 0, $needed));
}

$totalProjects     = count($projectRows);
$completedProjects = 0;   // closed projects
$activeTeams       = 0;   // projects with a team assigned that are not closed yet
foreach ($projectRows as [$status, $assigned]) {
    if ($status === 'closed') {
        $completedProjects++;
    } elseif ($assigned > 0) {
        $activeTeams++;
    }
}

include "../../../Includes/org_sidebar.php";
include "../../../Includes/dash_header.php";

?>

<main class="content">
    <div class="dashboard-header">

        <div>
            <h1>Profile & Settings</h1>
            <p>Manage your public organization profile, contact information, and account security.</p>
        </div>

    </div>

    <?php if (!empty($flash)): ?>
    <div class="flash-toast flash-<?= htmlspecialchars($flash['type']) ?>" style="margin:0 28px 16px;">
        <?= htmlspecialchars($flash['message']) ?>
    </div>
    <?php endif; ?>

    <form method="POST" action="" enctype="multipart/form-data" id="profileForm">

    <!-- ===================== STAT CARDS ===================== -->
    <div class="stats-grid">

        <div class="stat-card">
            <div class="stat-card-top">
                <div class="stat-icon blue">
                    <span class="material-symbols-outlined">rocket_launch</span>
                </div>
            </div>
            <div class="stat-info">
                <div class="stat-label">Total Projects</div>
                <div class="stat-value"><?= (int)$totalProjects ?></div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-card-top">
                <div class="stat-icon navy">
                    <span class="material-symbols-outlined">check_circle</span>
                </div>
            </div>
            <div class="stat-info">
                <div class="stat-label">Completed Projects</div>
                <div class="stat-value"><?= (int)$completedProjects ?></div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-card-top">
                <div class="stat-icon slate">
                    <span class="material-symbols-outlined">groups</span>
                </div>
            </div>
            <div class="stat-info">
                <div class="stat-label">Active Teams</div>
                <div class="stat-value"><?= (int)$activeTeams ?></div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-card-top">
                <div class="stat-icon green">
                    <span class="material-symbols-outlined">star</span>
                </div>
            </div>
            <div class="stat-info">
                <div class="stat-label">Avg Rating</div>
                <div class="stat-value">4.9/5.0</div>
            </div>
        </div>

    </div>

    <!-- ===================== PROFILE DETAILS ===================== -->
    <div class="profile-grid">

        <div class="profile-col">

            <div class="card">
                <style>
                    /* Same size/look as the Admin profile photo: 120px circle, white border, soft shadow,
                       and the uploaded image fills the whole circle. */
                    .avatar-circle.logo-circle { width: 120px; height: 120px; box-sizing: border-box; background: #f8fafc;
                                                 border: 4px solid #ffffff; box-shadow: 0 4px 16px rgba(0, 0, 0, 0.12); }
                    .avatar-circle.logo-circle img { width: 100%; height: 100%; object-fit: cover; }
                    /* the default SkillBridge placeholder (no logo uploaded) is shown whole, not cropped */
                    .avatar-circle.logo-circle img.is-default { width: 70%; height: 70%; object-fit: contain; }
                </style>
                <div class="avatar-wrap">
                    <div class="avatar-circle logo-circle">
                        <img src="<?= !empty($org['logo']) ? '../../../' . htmlspecialchars($org['logo']) : '../../../Assets/Images/logo.png' ?>" alt="" id="logoPreview" class="<?= empty($org['logo']) ? 'is-default' : '' ?>" onerror="this.onerror=null;this.classList.add('is-default');this.src='../../../Assets/Images/logo.png';">
                    </div>
                    <label for="logoInput" class="upload-link" style="cursor:pointer;">Upload New Logo</label>
                    <input type="file" name="logo" id="logoInput" accept="image/png, image/jpeg, image/gif, image/webp" style="display:none;" onchange="document.getElementById('profileForm').submit();">
                    <?php if (!empty($org['logo'])): ?>
                        <input type="hidden" name="remove_logo" id="removeLogo" value="0">
                        <button type="button" class="upload-link" style="background:none;border:none;padding:0;cursor:pointer;color:#dc2626;font-family:inherit;display:inline-flex;align-items:center;gap:4px;"
                                onclick="openRemoveLogoModal()"><span class="material-symbols-outlined" style="font-size:18px;">delete</span>Remove Logo</button>
                    <?php endif; ?>
                    <span class="badge-status verified">Verified</span>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3>Social Links</h3>
                </div>
                <div class="card-body">

                    <div class="form-group">
                        <label class="form-label">LinkedIn</label>
                        <input type="text" name="linkedin" class="form-input" value="<?= htmlspecialchars($org['linkedin'] ?? '') ?>">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Twitter</label>
                        <input type="text" name="twitter" class="form-input" value="<?= htmlspecialchars($org['twitter'] ?? '') ?>">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Facebook</label>
                        <input type="text" name="facebook" class="form-input" value="<?= htmlspecialchars($org['facebook'] ?? '') ?>">
                    </div>

                </div>
            </div>

        </div>

        <div class="card">
            <div class="card-header">
                <h3>Organization Details</h3>
            </div>
            <div class="card-body">

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Organization Name</label>
                        <input type="text" name="org_name" class="form-input" value="<?= htmlspecialchars($org['Name'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Industry</label>
                        <input type="text" name="industry" class="form-input" value="<?= htmlspecialchars($org['orgtype'] ?? '') ?>">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Official Email</label>
                        <input type="email" class="form-input" value="<?= htmlspecialchars($org['Email'] ?? '') ?>" readonly>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Contact Number</label>
                        <input type="text" name="contact_number" class="form-input" value="<?= htmlspecialchars($org['contactNumber'] ?? '') ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Website URL</label>
                    <input type="text" name="website" class="form-input" value="<?= htmlspecialchars($org['website'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label class="form-label">Address</label>
                    <input type="text" name="address" class="form-input" value="<?= htmlspecialchars($org['location'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label class="form-label">About Organization</label>
                    <textarea name="about" class="form-textarea"><?= htmlspecialchars($org['about'] ?? '') ?></textarea>
                </div>

                <div style="text-align:right; margin-top:10px;">
                    <button type="submit" class="btn-solid">Save Changes</button>
                </div>

            </div>
        </div>

    </div>
    </form>

</main>

<?php if (!empty($org['logo'])): ?>
<!-- Remove Logo confirmation dialog -->
<style>
    .rl-overlay { position: fixed; inset: 0; z-index: 9999; display: none; align-items: center; justify-content: center;
                  padding: 16px; background: rgba(15, 23, 42, 0.5); backdrop-filter: blur(2px); }
    .rl-overlay.show { display: flex; }
    .rl-card { width: 100%; max-width: 400px; background: #fff; border-radius: 16px; padding: 28px 24px 22px; text-align: center;
               box-shadow: 0 20px 50px rgba(15, 23, 42, 0.25); animation: rlPop 0.18s ease-out; }
    @keyframes rlPop { from { opacity: 0; transform: scale(0.94) translateY(6px); } to { opacity: 1; transform: none; } }
    .rl-icon { width: 56px; height: 56px; margin: 0 auto 14px; border-radius: 50%; background: #fee2e2; color: #dc2626;
               display: flex; align-items: center; justify-content: center; }
    .rl-icon .material-symbols-outlined { font-size: 28px; }
    .rl-title { margin: 0 0 8px; font-size: 18px; font-weight: 700; color: #0f172a; }
    .rl-text { margin: 0 0 22px; font-size: 14px; line-height: 1.55; color: #64748b; }
    .rl-actions { display: flex; gap: 10px; }
    .rl-btn { flex: 1; display: inline-flex; align-items: center; justify-content: center; gap: 6px; padding: 11px 16px;
              border: none; border-radius: 10px; font-family: inherit; font-size: 14px; font-weight: 600; cursor: pointer;
              transition: background 0.15s ease; }
    .rl-btn .material-symbols-outlined { font-size: 18px; }
    .rl-cancel { background: #f1f5f9; color: #334155; }
    .rl-cancel:hover { background: #e2e8f0; }
    .rl-confirm { background: #dc2626; color: #fff; }
    .rl-confirm:hover { background: #b91c1c; }
</style>

<div class="rl-overlay" id="removeLogoModal" onclick="closeRemoveLogoModal()">
    <div class="rl-card" role="dialog" aria-modal="true" aria-labelledby="rlTitle" onclick="event.stopPropagation()">
        <div class="rl-icon"><span class="material-symbols-outlined">delete</span></div>
        <h3 class="rl-title" id="rlTitle">Remove logo?</h3>
        <p class="rl-text">Your organization logo will be removed from your profile. You can upload a new one anytime.</p>
        <div class="rl-actions">
            <button type="button" class="rl-btn rl-cancel" id="rlCancel" onclick="closeRemoveLogoModal()">Cancel</button>
            <button type="button" class="rl-btn rl-confirm" id="rlConfirm" onclick="confirmRemoveLogo()">
                <span class="material-symbols-outlined">delete</span>Remove
            </button>
        </div>
    </div>
</div>

<script>
    function openRemoveLogoModal() {
        document.getElementById('removeLogoModal').classList.add('show');
        document.getElementById('rlCancel').focus();
    }
    function closeRemoveLogoModal() {
        document.getElementById('removeLogoModal').classList.remove('show');
    }
    function confirmRemoveLogo() {
        document.getElementById('rlConfirm').disabled = true;   // avoid double submit
        document.getElementById('removeLogo').value = '1';
        document.getElementById('profileForm').submit();
    }
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeRemoveLogoModal();
    });
</script>
<?php endif; ?>

<script>
    // Auto-hide the flash toast message after a few seconds
    document.addEventListener('DOMContentLoaded', function () {
        const toast = document.querySelector('.flash-toast');
        if (toast) {
            setTimeout(function () {
                toast.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
                toast.style.opacity = '0';
                toast.style.transform = 'translateX(20px)';
                setTimeout(function () {
                    toast.remove();
                }, 400);
            }, 3000); // visible for 3 seconds
        }
    });
</script>

<footer class="footer">
    <div>&copy; 2026 SkillBridge. All rights reserved.</div>
    <div class="footer-links">
        <a href="#">Help Center</a>
        <a href="#">Privacy Policy</a>
        <a href="#">Terms of Service</a>
    </div>
</footer>

<?php include "../../../Includes/dash_footer.php"; ?>