<?php
include "../../../Config/db.php";
include "../../../Session/Session.php";
require_once "../../../Backend/CompanyBackend.php";

require_role('company');
$user = current_user();
$companyEmail = isset($user['email']) ? $user['email'] : (isset($user['Email']) ? $user['Email'] : '');
$companyManager = new CompanyManager($conn);
$company = $companyManager->getCompany($companyEmail);
$profileFlash = null;

if (!$company) {
    die('Company profile not found.');
}

if (empty($_SESSION['company_profile_token'])) {
    $_SESSION['company_profile_token'] = sha1(uniqid((string) mt_rand(), true));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = isset($_POST['profile_token']) ? $_POST['profile_token'] : '';
    $companyNameInput = trim(isset($_POST['company_name']) ? $_POST['company_name'] : '');
    $industry = trim(isset($_POST['industry']) ? $_POST['industry'] : '');
    $contactPerson = trim(isset($_POST['contact_person']) ? $_POST['contact_person'] : '');
    $contactNumber = trim(isset($_POST['contact_number']) ? $_POST['contact_number'] : '');
    $website = trim(isset($_POST['website']) ? $_POST['website'] : '');
    $location = trim(isset($_POST['location']) ? $_POST['location'] : '');

    if (!hash_equals($_SESSION['company_profile_token'], $token)) {
        $profileFlash = array('type' => 'error', 'message' => 'Your session expired. Please refresh and try again.');
    } elseif ($companyNameInput === '' || $industry === '' || $contactPerson === '' || $contactNumber === '' || $website === '' || $location === '') {
        $profileFlash = array('type' => 'error', 'message' => 'Please complete all company profile fields.');
    } elseif (!preg_match('/^[0-9+() -]{7,17}$/', $contactNumber)) {
        $profileFlash = array('type' => 'error', 'message' => 'Please enter a valid contact number.');
    } else {
        try {
            $companyManager->updateCompany($companyEmail, $company['Name'], $companyNameInput, $industry, $contactPerson, $contactNumber, $website, $location);
            $_SESSION['user']['username'] = $companyNameInput;
            $company = $companyManager->getCompany($companyEmail);
            $profileFlash = array('type' => 'success', 'message' => 'Company profile updated successfully.');
        } catch (Exception $exception) {
            $profileFlash = array('type' => 'error', 'message' => 'Unable to update the company profile. Please try again.');
        }
    }
}

$companyName = $company['Name'];

// Company profile UI styles are isolated from student and organization pages.
$extra_css = '<link rel="stylesheet" href="../../../Assets/CSS/Company/profile.css">';

include "../../../Includes/company_sidebar.php";
include "../../../Includes/dash_header.php";
?>

<main class="content company-profile-page">
    <header class="company-profile-heading">
        <div>
            <h1>Company Profile</h1>
            <p>Manage your public organization profile and contact information.</p>
        </div>
        <div class="company-profile-actions">
            <button type="button" class="profile-edit-button" id="companyProfileEdit">Edit Profile</button>
            <button type="submit" class="profile-save-button" form="companyProfileForm" disabled>Save Changes</button>
        </div>
    </header>

    <?php if ($profileFlash): ?>
        <div class="company-profile-alert <?= htmlspecialchars($profileFlash['type']) ?>" role="alert"><?= htmlspecialchars($profileFlash['message']) ?></div>
    <?php endif; ?>

    <div class="company-profile-layout">
        <aside class="company-profile-aside">
            <section class="company-logo-card">
                <div class="company-logo-preview">
                    <img src="../../../Assets/Images/logo.png" alt="<?= htmlspecialchars($companyName) ?> logo">
                </div>
                <span class="company-logo-upload">Company Logo</span>
                <?php if (in_array(strtolower($company['Status']), array('verify', 'verified'))): ?><span class="company-verified-badge"><span class="material-symbols-outlined">verified</span>Verified</span><?php endif; ?>
            </section>

            <section class="company-social-card">
                <h2>Social Links</h2>
                <label for="companyLinkedIn">LinkedIn</label>
                <input id="companyLinkedIn" type="text" value="Not available in current profile data" readonly>

                <label for="companyTwitter">Twitter</label>
                <input id="companyTwitter" type="text" value="Not available in current profile data" readonly>

                <label for="companyFacebook">Facebook</label>
                <input id="companyFacebook" type="text" value="Not available in current profile data" readonly>
            </section>
        </aside>

        <section class="company-details-card">
            <h2>Company Details</h2>
            <form id="companyProfileForm" action="" method="post" enctype="multipart/form-data">
                <input type="hidden" name="profile_token" value="<?= htmlspecialchars($_SESSION['company_profile_token']) ?>">
                <div class="company-profile-form-grid">
                    <div class="company-profile-field">
                        <label for="companyProfileName">Company Name</label>
                        <input id="companyProfileName" name="company_name" type="text" value="<?= htmlspecialchars($companyName) ?>" maxlength="100" data-company-editable readonly required>
                    </div>
                    <div class="company-profile-field">
                        <label for="companyIndustry">Industry</label>
                        <input id="companyIndustry" name="industry" type="text" value="<?= htmlspecialchars($company['companytype']) ?>" maxlength="100" data-company-editable readonly required>
                    </div>
                    <div class="company-profile-field">
                        <label for="companyProfileEmail">Company Email</label>
                        <input id="companyProfileEmail" name="company_email" type="email" value="<?= htmlspecialchars($companyEmail) ?>" readonly>
                    </div>
                    <div class="company-profile-field">
                        <label for="companyContactNumber">Contact Number</label>
                        <input id="companyContactNumber" name="contact_number" type="tel" value="<?= htmlspecialchars($company['contactNumber']) ?>" maxlength="17" pattern="[0-9+() -]{7,17}" data-company-editable readonly required>
                    </div>
                    <div class="company-profile-field full-width">
                        <label for="companyContactPerson">Contact Person</label>
                        <input id="companyContactPerson" name="contact_person" type="text" value="<?= htmlspecialchars($company['contactPersonName']) ?>" maxlength="100" data-company-editable readonly required>
                    </div>
                    <div class="company-profile-field full-width">
                        <label for="companyWebsite">Website URL</label>
                        <input id="companyWebsite" name="website" type="text" value="<?= htmlspecialchars($company['website']) ?>" maxlength="200" data-company-editable readonly required>
                    </div>
                    <div class="company-profile-field full-width">
                        <label for="companyAddress">Location</label>
                        <input id="companyAddress" name="location" type="text" value="<?= htmlspecialchars($company['location']) ?>" maxlength="100" data-company-editable readonly required>
                    </div>
                    <div class="company-profile-field full-width">
                        <label for="companyAbout">About Company</label>
                        <textarea id="companyAbout" rows="5" readonly>About-company information is not available in the current database structure.</textarea>
                    </div>
                </div>
            </form>
        </section>
    </div>
</main>

<footer class="company-footer"><span>&copy; 2026 SkillBridge. All rights reserved.</span><nav><a href="#">Help Center</a><a href="#">Privacy Policy</a><a href="#">Terms of Service</a></nav></footer>
<script src="../../../Assets/JS/Company/profile.js"></script>
<?php include "../../../Includes/dash_footer.php"; ?>
