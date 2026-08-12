<?php

include "../../../Config/db.php";
include "../../../Session/Session.php";

require_role('organization');
$user = current_user();

include "../../../Includes/org_sidebar.php";
include "../../../Includes/dash_header.php";

?>

<main class="content">
    <div class="dashboard-header">

        <div>
            <h1>Organization Profile</h1>
            <p>Manage your public organization profile and contact information.</p>
        </div>

        <div style="display:flex; gap:10px;">
            <a href="#" class="btn-outline">Edit Profile</a>
            <a href="#" class="btn-solid">Save Changes</a>
        </div>

    </div>

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
                <div class="stat-value">24</div>
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
                <div class="stat-value">18</div>
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
                        <input type="text" class="form-input" value="linkedin.com/company/techcorp">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Twitter</label>
                        <input type="text" class="form-input" value="twitter.com/techcorp">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Facebook</label>
                        <input type="text" class="form-input" value="facebook.com/techcorp">
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
                        <input type="text" class="form-input" value="TechCorp Solutions">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Industry</label>
                        <input type="text" class="form-input" value="Software Development">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Official Email</label>
                        <input type="email" class="form-input" value="contact@techcorp.com">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Contact Number</label>
                        <input type="text" class="form-input" value="+1 (555) 012-3456">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Website URL</label>
                    <input type="text" class="form-input" value="www.techcorp.com">
                </div>

                <div class="form-group">
                    <label class="form-label">Address</label>
                    <input type="text" class="form-input" value="123 Innovation Way, Silicon Valley, CA">
                </div>

                <div class="form-group">
                    <label class="form-label">About Organization</label>
                    <textarea class="form-textarea">TechCorp Solutions is a leading provider of innovative software solutions, specializing in cloud migration and AI optimization for enterprise clients worldwide.</textarea>
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

<?php include "../../../Includes/dash_footer.php"; ?>