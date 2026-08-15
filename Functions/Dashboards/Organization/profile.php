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

    $sql = "UPDATE organization
            SET Name=?, orgtype=?, contactNumber=?, website=?, location=?, about=?, linkedin=?, twitter=?, facebook=?
            WHERE Email=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "ssssssssss",
        $name, $orgtype, $contactNumber, $website, $location, $about, $linkedin, $twitter, $facebook, $organization_email
    );

    if ($stmt->execute()) {
        $_SESSION['user']['username'] = $name;
        $flash = ['type' => 'success', 'message' => 'Profile updated successfully.'];
    } else {
        $flash = ['type' => 'error', 'message' => 'Update failed. Please try again.'];
    }
}

// Fetch current organization data (also picks up the just-saved values)
$stmt = $conn->prepare("SELECT * FROM organization WHERE Email=?");
$stmt->bind_param("s", $organization_email);
$stmt->execute();
$org = $stmt->get_result()->fetch_assoc();

// Stat cards
$stmt = $conn->prepare("SELECT COUNT(*) FROM projects WHERE organization_email=?");
$stmt->bind_param("s", $organization_email);
$stmt->execute();
$totalProjects = $stmt->get_result()->fetch_row()[0];

$stmt = $conn->prepare("SELECT COUNT(*) FROM projects WHERE organization_email=? AND status='closed'");
$stmt->bind_param("s", $organization_email);
$stmt->execute();
$completedProjects = $stmt->get_result()->fetch_row()[0];

include "../../../Includes/org_sidebar.php";
include "../../../Includes/dash_header.php";

?>

<main class="content">
    <div class="dashboard-header">

        <div>
            <h1>Organization Profile</h1>
            <p>Manage your public organization profile and contact information.</p>
        </div>

    </div>

    <?php if (!empty($flash)): ?>
    <div class="flash-toast flash-<?= htmlspecialchars($flash['type']) ?>" style="margin:0 28px 16px;">
        <?= htmlspecialchars($flash['message']) ?>
    </div>
    <?php endif; ?>

    <form method="POST" action="">

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
                <div class="stat-value">06</div>
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
                <div class="avatar-wrap">
                    <div class="avatar-circle">
                        <img src="../../../Assets/Images/logo.png" alt="Organization Logo">
                    </div>
                    <a href="#" class="upload-link">Upload New Logo</a>
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

<footer class="footer">
    <div>&copy; 2026 SkillBridge. All rights reserved.</div>
    <div class="footer-links">
        <a href="#">Help Center</a>
        <a href="#">Privacy Policy</a>
        <a href="#">Terms of Service</a>
    </div>
</footer>

<?php include "../../../Includes/dash_footer.php"; ?>