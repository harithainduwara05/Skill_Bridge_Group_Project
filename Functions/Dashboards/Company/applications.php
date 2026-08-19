<?php
include __DIR__ . '/../../../Config/db.php';
include __DIR__ . '/../../../Config/company_schema.php';
include __DIR__ . '/../../../Session/Session.php';

date_default_timezone_set('Asia/Kolkata');

require_role('company');
$user = current_user();
ensure_company_schema($conn);
$company = ensure_company_record($conn, $user);
$companyId = isset($company['id']) ? (int) $company['id'] : 0;
seed_company_demo_data($conn, $companyId);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['status']) && !empty($_POST['application_id'])) {
    $applicationId = (int) $_POST['application_id'];
    $status = trim($_POST['status']);
    $conn->query("UPDATE applications SET status = '$status' WHERE id = $applicationId AND company_id = $companyId");
    header('Location: applications.php');
    exit;
}

$statusFilter = isset($_GET['status']) ? $_GET['status'] : 'all';
$where = "company_id = $companyId";
if ($statusFilter !== 'all') {
    $where .= " AND status = '" . $conn->real_escape_string($statusFilter) . "'";
}

$applications = $conn->query("SELECT * FROM applications WHERE $where ORDER BY applied_date DESC");
include __DIR__ . '/../../../Includes/company_sidebar.php';
include __DIR__ . '/../../../Includes/dash_header.php';
?>

<main class="content">
    <div class="dashboard-header">
        <div>
            <h1>Applications</h1>
            <p>Review candidates, update their status, and move strong applicants into interview stages.</p>
        </div>
    </div>

    <div class="content-section" style="margin-bottom:1.5rem;">
        <form method="GET" action="applications.php" style="display:flex; gap:1rem; align-items:center; flex-wrap:wrap;">
            <label style="font-weight:600;">Filter by status:</label>
            <select name="status" style="padding:0.8rem; border:1px solid #dbe3f0; border-radius:10px;">
                <option value="all" <?php echo ($statusFilter === 'all') ? 'selected' : ''; ?>>All</option>
                <option value="new" <?php echo ($statusFilter === 'new') ? 'selected' : ''; ?>>New</option>
                <option value="shortlisted" <?php echo ($statusFilter === 'shortlisted') ? 'selected' : ''; ?>>Shortlisted</option>
                <option value="interview" <?php echo ($statusFilter === 'interview') ? 'selected' : ''; ?>>Interview</option>
                <option value="rejected" <?php echo ($statusFilter === 'rejected') ? 'selected' : ''; ?>>Rejected</option>
            </select>
            <button type="submit" style="background:#082544; color:white; border:none; padding:0.8rem 1rem; border-radius:10px; cursor:pointer;">Apply</button>
        </form>
    </div>

    <div class="content-section">
        <table style="width:100%; border-collapse:collapse;">
            <thead>
                <tr style="background:#f8fafc;">
                    <th style="padding:0.9rem; text-align:left;">Applicant</th>
                    <th style="padding:0.9rem; text-align:left;">Email</th>
                    <th style="padding:0.9rem; text-align:left;">Position</th>
                    <th style="padding:0.9rem; text-align:left;">Status</th>
                    <th style="padding:0.9rem; text-align:left;">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($applications && $applications->num_rows > 0): ?>
                    <?php while ($row = $applications->fetch_assoc()): ?>
                        <tr style="border-bottom:1px solid #e2e8f0;">
                            <td style="padding:0.9rem;"><?php echo htmlspecialchars($row['applicant_name']); ?></td>
                            <td style="padding:0.9rem;"><?php echo htmlspecialchars($row['email']); ?></td>
                            <td style="padding:0.9rem;"><?php echo htmlspecialchars($row['position']); ?></td>
                            <td style="padding:0.9rem;">
                                <span style="display:inline-block; padding:0.35rem 0.7rem; border-radius:999px; background:#e0f2fe; color:#0f172a; text-transform:capitalize;">
                                    <?php echo htmlspecialchars($row['status']); ?>
                                </span>
                            </td>
                            <td style="padding:0.9rem;">
                                <form method="POST" action="applications.php" style="display:flex; gap:0.5rem; align-items:center; flex-wrap:wrap;">
                                    <input type="hidden" name="application_id" value="<?php echo (int) $row['id']; ?>">
                                    <select name="status" style="padding:0.55rem; border:1px solid #dbe3f0; border-radius:8px;">
                                        <option value="new" <?php echo ($row['status'] === 'new') ? 'selected' : ''; ?>>New</option>
                                        <option value="shortlisted" <?php echo ($row['status'] === 'shortlisted') ? 'selected' : ''; ?>>Shortlisted</option>
                                        <option value="interview" <?php echo ($row['status'] === 'interview') ? 'selected' : ''; ?>>Interview</option>
                                        <option value="rejected" <?php echo ($row['status'] === 'rejected') ? 'selected' : ''; ?>>Rejected</option>
                                    </select>
                                    <button type="submit" style="background:#082544; color:white; border:none; padding:0.55rem 0.8rem; border-radius:8px; cursor:pointer;">Update</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="5" style="padding:1rem; color:#64748b;">No applications available.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>

<?php include __DIR__ . '/../../../Includes/dash_footer.php'; ?>
