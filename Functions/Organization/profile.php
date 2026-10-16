<?php

include "../../Config/db.php";
include "../../Session/Session.php";

require_role('organization');

// Backend (PHP + SQL) of this page
require_once __DIR__ . "/../../Backend/Organization/profile.php";

$extra_css = '<link rel="stylesheet" href="../../Assets/CSS/Organization/profile.css">';
include "../../Includes/org_sidebar.php";
include "../../Includes/dash_header.php";

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
                

                <div class="avatar-wrap">
                    <div class="avatar-circle logo-circle">
                        <img src="<?= !empty($org['logo']) ? '../../' . htmlspecialchars($org['logo']) : '../../Assets/Images/logo.png' ?>" alt="" id="logoPreview" class="<?= empty($org['logo']) ? 'is-default' : '' ?>" onerror="this.onerror=null;this.classList.add('is-default');this.src='../../Assets/Images/logo.png';">
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
        <a href="/Skill_Bridge_Group_Project/help_center.php">Help Center</a>
        <a href="/Skill_Bridge_Group_Project/help_center.php#knowledgeBaseSection">Privacy Policy</a>
        <a href="/Skill_Bridge_Group_Project/help_center.php#knowledgeBaseSection">Terms of Service</a>
    </div>
</footer>

<?php include "../../Includes/dash_footer.php"; ?>