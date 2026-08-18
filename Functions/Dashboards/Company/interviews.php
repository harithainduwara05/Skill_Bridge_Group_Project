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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_POST['delete_id'])) {
        $id = (int) $_POST['delete_id'];
        $conn->query("DELETE FROM interviews WHERE id = $id AND company_id = $companyId");
        header('Location: interviews.php');
        exit;
    }

    $applicant = trim(isset($_POST['applicant_name']) ? $_POST['applicant_name'] : '');
    $position = trim(isset($_POST['position']) ? $_POST['position'] : '');
    $date = trim(isset($_POST['interview_date']) ? $_POST['interview_date'] : date('Y-m-d H:i:s'));
    $type = trim(isset($_POST['interview_type']) ? $_POST['interview_type'] : 'Video Call');
    $status = trim(isset($_POST['status']) ? $_POST['status'] : 'scheduled');
    $notes = trim(isset($_POST['notes']) ? $_POST['notes'] : '');

    $stmt = $conn->prepare("INSERT INTO interviews (company_id, applicant_name, position, interview_date, interview_type, status, notes) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issssss", $companyId, $applicant, $position, $date, $type, $status, $notes);
    $stmt->execute();

    header('Location: interviews.php');
    exit;
}

$interviews = $conn->query("SELECT * FROM interviews WHERE company_id = $companyId ORDER BY interview_date ASC");
include __DIR__ . '/../../../Includes/company_sidebar.php';
include __DIR__ . '/../../../Includes/dash_header.php';
?>

<main class="content">
    <div class="dashboard-header">
        <div>
            <h1>Interview Schedule</h1>
            <p>Arrange interviews, keep track of dates, and manage candidate conversations.</p>
        </div>
    </div>

    <div class="content-section" style="margin-bottom:2rem;">
        <h3 style="margin-bottom:1rem;">Schedule New Interview</h3>
        <form method="POST" action="interviews.php" style="display:grid; gap:1rem;">
            <div style="display:grid; grid-template-columns: repeat(2, minmax(220px,1fr)); gap:1rem;">
                <div>
                    <label style="display:block; margin-bottom:0.5rem; font-weight:600;">Applicant Name</label>
                    <input type="text" name="applicant_name" required style="width:100%; padding:0.8rem; border:1px solid #dbe3f0; border-radius:10px;">
                </div>
                <div>
                    <label style="display:block; margin-bottom:0.5rem; font-weight:600;">Position</label>
                    <input type="text" name="position" required style="width:100%; padding:0.8rem; border:1px solid #dbe3f0; border-radius:10px;">
                </div>
                <div>
                    <label style="display:block; margin-bottom:0.5rem; font-weight:600;">Interview Date</label>
                    <input type="datetime-local" name="interview_date" required style="width:100%; padding:0.8rem; border:1px solid #dbe3f0; border-radius:10px;">
                </div>
                <div>
                    <label style="display:block; margin-bottom:0.5rem; font-weight:600;">Interview Type</label>
                    <select name="interview_type" style="width:100%; padding:0.8rem; border:1px solid #dbe3f0; border-radius:10px;">
                        <option>Video Call</option>
                        <option>In-person</option>
                        <option>Panel Interview</option>
                        <option>Phone Screen</option>
                    </select>
                </div>
            </div>
            <div>
                <label style="display:block; margin-bottom:0.5rem; font-weight:600;">Notes</label>
                <textarea name="notes" rows="3" style="width:100%; padding:0.8rem; border:1px solid #dbe3f0; border-radius:10px;"></textarea>
            </div>
            <div style="display:flex; justify-content:flex-end;">
                <button type="submit" style="background:#082544; color:white; border:none; padding:0.8rem 1.4rem; border-radius:10px; cursor:pointer;">Save Interview</button>
            </div>
        </form>
    </div>

    <div class="content-section">
        <h3 style="margin-bottom:1rem;">Scheduled Interviews</h3>
        <table style="width:100%; border-collapse:collapse;">
            <thead>
                <tr style="background:#f8fafc;">
                    <th style="padding:0.9rem; text-align:left;">Applicant</th>
                    <th style="padding:0.9rem; text-align:left;">Position</th>
                    <th style="padding:0.9rem; text-align:left;">Date</th>
                    <th style="padding:0.9rem; text-align:left;">Type</th>
                    <th style="padding:0.9rem; text-align:left;">Status</th>
                    <th style="padding:0.9rem; text-align:left;">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($interviews && $interviews->num_rows > 0): ?>
                    <?php while ($row = $interviews->fetch_assoc()): ?>
                        <tr style="border-bottom:1px solid #e2e8f0;">
                            <td style="padding:0.9rem;"><?php echo htmlspecialchars($row['applicant_name']); ?></td>
                            <td style="padding:0.9rem;"><?php echo htmlspecialchars($row['position']); ?></td>
                            <td style="padding:0.9rem;"><?php echo htmlspecialchars($row['interview_date']); ?></td>
                            <td style="padding:0.9rem;"><?php echo htmlspecialchars($row['interview_type']); ?></td>
                            <td style="padding:0.9rem;"><?php echo htmlspecialchars($row['status']); ?></td>
                            <td style="padding:0.9rem;">
                                <form method="POST" action="interviews.php" style="display:inline;">
                                    <input type="hidden" name="delete_id" value="<?php echo (int) $row['id']; ?>">
                                    <button type="submit" style="background:#ef4444; color:white; border:none; padding:0.55rem 0.8rem; border-radius:8px; cursor:pointer;">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="6" style="padding:1rem; color:#64748b;">No interviews scheduled yet.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>

<?php include __DIR__ . '/../../../Includes/dash_footer.php'; ?>
