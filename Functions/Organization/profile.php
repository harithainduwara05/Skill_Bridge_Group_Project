<?php

include "../../Config/db.php";
include "../../Session/Session.php";

require_role('organization');

// Backend (PHP + SQL) of this page
require_once __DIR__ . "/../../Backend/Organization/profile.php";

$extra_css = '<link rel="stylesheet" href="../../Assets/CSS/Organization/profile.css?v=' . filemtime(__DIR__ . '/../../Assets/CSS/Organization/profile.css') . '">';
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

    <!-- novalidate: checked by the script at the bottom (messages under the fields) and again on the server -->
    <form method="POST" action="" enctype="multipart/form-data" id="profileForm" novalidate>

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
                    <input type="file" name="logo" id="logoInput" accept="image/png, image/jpeg, image/gif, image/webp" style="display:none;" onchange="document.getElementById('profileForm').requestSubmit();">
                    <?php if (!empty($org['logo'])): ?>
                        <input type="hidden" name="remove_logo" id="removeLogo" value="0">
                        <button type="button" class="upload-link" style="background:none;border:none;padding:0;cursor:pointer;color:#dc2626;font-family:inherit;display:inline-flex;align-items:center;gap:4px;"
                                onclick="openRemoveLogoModal()"><span class="material-symbols-outlined" style="font-size:18px;">delete</span>Remove Logo</button>
                    <?php endif; ?>
                    <span class="badge-status verified">Verified</span>
                    <div class="form-hint">JPG, PNG, GIF or WEBP, up to 2MB.</div>
                    <div class="field-error" data-for="logo"></div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3>Social Links</h3>
                </div>
                <div class="card-body">
                    <div class="form-hint" style="margin:0 0 12px;">Optional. Paste the full link to your page.</div>

                    <div class="form-group">
                        <label class="form-label">LinkedIn</label>
                        <input type="text" name="linkedin" class="form-input" maxlength="200" placeholder="https://linkedin.com/company/your-org" value="<?= htmlspecialchars($org['linkedin'] ?? '') ?>">
                        <div class="field-error" data-for="linkedin"><?= htmlspecialchars($errors['linkedin'] ?? '') ?></div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Twitter</label>
                        <input type="text" name="twitter" class="form-input" maxlength="200" placeholder="https://x.com/your-org" value="<?= htmlspecialchars($org['twitter'] ?? '') ?>">
                        <div class="field-error" data-for="twitter"><?= htmlspecialchars($errors['twitter'] ?? '') ?></div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Facebook</label>
                        <input type="text" name="facebook" class="form-input" maxlength="200" placeholder="https://facebook.com/your-org" value="<?= htmlspecialchars($org['facebook'] ?? '') ?>">
                        <div class="field-error" data-for="facebook"><?= htmlspecialchars($errors['facebook'] ?? '') ?></div>
                    </div>

                </div>
            </div>

        </div>

        <div class="card">
            <div class="card-header">
                <h3>Organization Details</h3>
            </div>
            <div class="card-body">
                <div class="form-hint" style="margin:0 0 12px;">Fields marked <span class="req">*</span> are required.</div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Organization Name <span class="req">*</span></label>
                        <input type="text" name="org_name" class="form-input" maxlength="100" value="<?= htmlspecialchars($org['Name'] ?? '') ?>">
                        <div class="field-error" data-for="org_name"><?= htmlspecialchars($errors['org_name'] ?? '') ?></div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Industry <span class="req">*</span></label>
                        <input type="text" name="industry" class="form-input" maxlength="100" placeholder="e.g. Software, Student Society" value="<?= htmlspecialchars($org['orgtype'] ?? '') ?>">
                        <div class="field-error" data-for="industry"><?= htmlspecialchars($errors['industry'] ?? '') ?></div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Official Email</label>
                        <input type="email" class="form-input" value="<?= htmlspecialchars($org['Email'] ?? '') ?>" readonly>
                        <div class="form-hint">Your login email. It can't be changed here.</div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Contact Number <span class="req">*</span></label>
                        <input type="tel" name="contact_number" class="form-input" maxlength="17" placeholder="e.g. 0771234567" value="<?= htmlspecialchars($org['contactNumber'] ?? '') ?>">
                        <div class="field-error" data-for="contact_number"><?= htmlspecialchars($errors['contact_number'] ?? '') ?></div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Website URL <span class="req">*</span></label>
                    <input type="text" name="website" class="form-input" maxlength="200" placeholder="e.g. https://example.com" value="<?= htmlspecialchars($org['website'] ?? '') ?>">
                    <div class="field-error" data-for="website"><?= htmlspecialchars($errors['website'] ?? '') ?></div>
                </div>

                <div class="form-group">
                    <label class="form-label">Address <span class="req">*</span></label>
                    <input type="text" name="address" class="form-input" maxlength="100" value="<?= htmlspecialchars($org['location'] ?? '') ?>">
                    <div class="field-error" data-for="address"><?= htmlspecialchars($errors['address'] ?? '') ?></div>
                </div>

                <div class="form-group">
                    <label class="form-label">About Organization</label>
                    <textarea name="about" class="form-textarea" maxlength="1000"><?= htmlspecialchars($org['about'] ?? '') ?></textarea>
                    <div class="form-hint">Optional, up to 1000 characters.</div>
                    <div class="field-error" data-for="about"><?= htmlspecialchars($errors['about'] ?? '') ?></div>
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

<?php endif; ?>

<script src="../../Assets/JS/Organization/profile.js?v=<?= filemtime(__DIR__ . '/../../Assets/JS/Organization/profile.js') ?>"></script>


<?php include "../../Includes/dash_footer.php"; ?>