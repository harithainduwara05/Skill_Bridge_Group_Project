<?php
include __DIR__ . '/../../../Config/db.php';
include __DIR__ . '/../../../Config/company_schema.php';
include __DIR__ . '/../../../Session/Session.php';

require_role('company');
$user = current_user();
ensure_company_schema($conn);
$company = ensure_company_record($conn, $user);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $companyName = trim(isset($_POST['company_name']) ? $_POST['company_name'] : '');
    $industry = trim(isset($_POST['industry_sector']) ? $_POST['industry_sector'] : 'Technology');
    $contact = trim(isset($_POST['contact_person']) ? $_POST['contact_person'] : '');
    $phone = trim(isset($_POST['contact_number']) ? $_POST['contact_number'] : '');
    $website = trim(isset($_POST['website']) ? $_POST['website'] : '');
    $location = trim(isset($_POST['location']) ? $_POST['location'] : '');
    $size = trim(isset($_POST['company_size']) ? $_POST['company_size'] : '51-200');
    $description = trim(isset($_POST['description']) ? $_POST['description'] : '');

    if ($company && !empty($company['id'])) {
        $stmt = $conn->prepare("UPDATE companies SET company_name = ?, industry_sector = ?, contact_person = ?, contact_number = ?, website = ?, location = ?, company_size = ?, description = ? WHERE id = ?");
        $stmt->bind_param("ssssssssi", $companyName, $industry, $contact, $phone, $website, $location, $size, $description, $company['id']);
        $stmt->execute();
    }

    header('Location: company.php');
    exit;
}

$company = ensure_company_record($conn, $user);
include __DIR__ . '/../../../Includes/company_sidebar.php';
include __DIR__ . '/../../../Includes/dash_header.php';
?>

<main class="content">
    <div class="dashboard-header">
        <div>
            <h1>Company Profile</h1>
            <p>Keep your profile up to date so students can understand your company and opportunities.</p>
        </div>
    </div>

    <div class="content-section" style="max-width: 980px; margin: 0 auto;">
        <form method="POST" action="company.php" style="display:grid; gap:1.2rem;">
            <div style="display:grid; grid-template-columns: repeat(2, minmax(220px, 1fr)); gap:1rem;">
                <div>
                    <label style="display:block; margin-bottom:0.5rem; font-weight:600;">Company Name</label>
                    <input type="text" name="company_name" value="<?php echo htmlspecialchars(isset($company['company_name']) ? $company['company_name'] : 'My Company'); ?>" style="width:100%; padding:0.8rem; border:1px solid #dbe3f0; border-radius:10px;" required>
                </div>
                <div>
                    <label style="display:block; margin-bottom:0.5rem; font-weight:600;">Industry Sector</label>
                    <select name="industry_sector" style="width:100%; padding:0.8rem; border:1px solid #dbe3f0; border-radius:10px;">
                        <option value="Technology" <?php echo ((isset($company['industry_sector']) ? $company['industry_sector'] : 'Technology') === 'Technology') ? 'selected' : ''; ?>>Technology</option>
                        <option value="Finance" <?php echo ((isset($company['industry_sector']) ? $company['industry_sector'] : '') === 'Finance') ? 'selected' : ''; ?>>Finance</option>
                        <option value="Healthcare" <?php echo ((isset($company['industry_sector']) ? $company['industry_sector'] : '') === 'Healthcare') ? 'selected' : ''; ?>>Healthcare</option>
                        <option value="Consulting" <?php echo ((isset($company['industry_sector']) ? $company['industry_sector'] : '') === 'Consulting') ? 'selected' : ''; ?>>Consulting</option>
                    </select>
                </div>
                <div>
                    <label style="display:block; margin-bottom:0.5rem; font-weight:600;">Contact Person</label>
                    <input type="text" name="contact_person" value="<?php echo htmlspecialchars(isset($company['contact_person']) ? $company['contact_person'] : ''); ?>" style="width:100%; padding:0.8rem; border:1px solid #dbe3f0; border-radius:10px;">
                </div>
                <div>
                    <label style="display:block; margin-bottom:0.5rem; font-weight:600;">Contact Number</label>
                    <input type="text" name="contact_number" value="<?php echo htmlspecialchars(isset($company['contact_number']) ? $company['contact_number'] : ''); ?>" style="width:100%; padding:0.8rem; border:1px solid #dbe3f0; border-radius:10px;">
                </div>
                <div>
                    <label style="display:block; margin-bottom:0.5rem; font-weight:600;">Website</label>
                    <input type="text" name="website" value="<?php echo htmlspecialchars(isset($company['website']) ? $company['website'] : ''); ?>" style="width:100%; padding:0.8rem; border:1px solid #dbe3f0; border-radius:10px;">
                </div>
                <div>
                    <label style="display:block; margin-bottom:0.5rem; font-weight:600;">Location</label>
                    <input type="text" name="location" value="<?php echo htmlspecialchars(isset($company['location']) ? $company['location'] : 'Colombo, Sri Lanka'); ?>" style="width:100%; padding:0.8rem; border:1px solid #dbe3f0; border-radius:10px;" required>
                </div>
                <div>
                    <label style="display:block; margin-bottom:0.5rem; font-weight:600;">Company Size</label>
                    <select name="company_size" style="width:100%; padding:0.8rem; border:1px solid #dbe3f0; border-radius:10px;">
                        <option value="1-50" <?php echo ((isset($company['company_size']) ? $company['company_size'] : '') === '1-50') ? 'selected' : ''; ?>>1-50</option>
                        <option value="51-200" <?php echo ((isset($company['company_size']) ? $company['company_size'] : '51-200') === '51-200') ? 'selected' : ''; ?>>51-200</option>
                        <option value="201-500" <?php echo ((isset($company['company_size']) ? $company['company_size'] : '') === '201-500') ? 'selected' : ''; ?>>201-500</option>
                        <option value="500+" <?php echo ((isset($company['company_size']) ? $company['company_size'] : '') === '500+') ? 'selected' : ''; ?>>500+</option>
                    </select>
                </div>
            </div>

            <div>
                <label style="display:block; margin-bottom:0.5rem; font-weight:600;">Company Description</label>
                <textarea name="description" rows="6" style="width:100%; padding:0.8rem; border:1px solid #dbe3f0; border-radius:10px;"><?php echo htmlspecialchars(isset($company['description']) ? $company['description'] : ''); ?></textarea>
            </div>

            <div style="display:flex; justify-content:flex-end;">
                <button type="submit" class="btn-primary" style="background:#082544; color:white; border:none; padding:0.8rem 1.4rem; border-radius:10px; cursor:pointer;">Save Company Profile</button>
            </div>
        </form>
    </div>
</main>

<?php include __DIR__ . '/../../../Includes/dash_footer.php'; ?>
