<?php
include __DIR__ . '/../../../Config/db.php';
include __DIR__ . '/../../../Config/company_schema.php';
include __DIR__ . '/../../../Session/Session.php';

date_default_timezone_set('Asia/Kolkata');

require_role('company');
$user = current_user();
ensure_company_schema($conn);
$company = ensure_company_record($conn, $user);
$companyId = isset($company['id']) ? (int)$company['id'] : 0;
seed_company_demo_data($conn, $companyId);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? trim($_POST['action']) : 'create';

    if ($action === 'delete' && !empty($_POST['id'])) {
        $id = (int)$_POST['id'];
        $conn->query("DELETE FROM internships WHERE id = $id AND company_id = $companyId");
        header('Location: internships.php');
        exit;
    }

    $title = trim(isset($_POST['title']) ? $_POST['title'] : '');
    $description = trim(isset($_POST['description']) ? $_POST['description'] : '');
    $location = trim(isset($_POST['location']) ? $_POST['location'] : 'Remote');
    $duration = trim(isset($_POST['duration']) ? $_POST['duration'] : '3 months');
    $type = trim(isset($_POST['type']) ? $_POST['type'] : 'Full-time');
    $status = trim(isset($_POST['status']) ? $_POST['status'] : 'active');
    $deadline = trim(isset($_POST['deadline']) ? $_POST['deadline'] : date('Y-m-d', strtotime('+30 days')));

    if ($action === 'update' && !empty($_POST['id'])) {
        $id = (int)$_POST['id'];
        $stmt = $conn->prepare("UPDATE internships SET title = ?, description = ?, location = ?, duration = ?, type = ?, status = ?, deadline = ? WHERE id = ? AND company_id = ?");
        $stmt->bind_param("sssssssii", $title, $description, $location, $duration, $type, $status, $deadline, $id, $companyId);
        $stmt->execute();
    } else {
        $stmt = $conn->prepare("INSERT INTO internships (company_id, title, description, location, duration, type, status, deadline) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssssss", $companyId, $title, $description, $location, $duration, $type, $status, $deadline);
        $stmt->execute();
    }

    header('Location: internships.php');
    exit;
}

$internships = $conn->query("SELECT * FROM internships WHERE company_id = $companyId ORDER BY id DESC");
include __DIR__ . '/../../../Includes/company_sidebar.php';
include __DIR__ . '/../../../Includes/dash_header.php';
?>

<main class="content">
    <div class="dashboard-header">
        <div>
            <h1>Internship Management</h1>
            <p>Create, update, and remove internship postings for your company.</p>
        </div>
    </div>

    <div class="content-section" style="margin-bottom:2rem;">
        <h3 style="margin-bottom:1rem;">Add New Internship</h3>
        <form method="POST" action="internships.php" style="display:grid; gap:1rem;">
            <input type="hidden" name="action" value="create">
            <div style="display:grid; grid-template-columns: repeat(2, minmax(220px,1fr)); gap:1rem;">
                <div>
                    <label style="display:block; margin-bottom:0.5rem; font-weight:600;">Title</label>
                    <input type="text" name="title" required style="width:100%; padding:0.8rem; border:1px solid #dbe3f0; border-radius:10px;">
                </div>
                <div>
                    <label style="display:block; margin-bottom:0.5rem; font-weight:600;">Location</label>
                    <input type="text" name="location" value="Remote" style="width:100%; padding:0.8rem; border:1px solid #dbe3f0; border-radius:10px;">
                </div>
                <div>
                    <label style="display:block; margin-bottom:0.5rem; font-weight:600;">Duration</label>
                    <input type="text" name="duration" value="3 months" style="width:100%; padding:0.8rem; border:1px solid #dbe3f0; border-radius:10px;">
                </div>
                <div>
                    <label style="display:block; margin-bottom:0.5rem; font-weight:600;">Type</label>
                    <select name="type" style="width:100%; padding:0.8rem; border:1px solid #dbe3f0; border-radius:10px;">
                        <option>Full-time</option>
                        <option>Part-time</option>
                        <option>Hybrid</option>
                        <option>Remote</option>
                    </select>
                </div>
                <div>
                    <label style="display:block; margin-bottom:0.5rem; font-weight:600;">Status</label>
                    <select name="status" style="width:100%; padding:0.8rem; border:1px solid #dbe3f0; border-radius:10px;">
                        <option value="active">Active</option>
                        <option value="draft">Draft</option>
                        <option value="closed">Closed</option>
                    </select>
                </div>
                <div>
                    <label style="display:block; margin-bottom:0.5rem; font-weight:600;">Deadline</label>
                    <input type="date" name="deadline" value="<?php echo date('Y-m-d', strtotime('+30 days')); ?>" style="width:100%; padding:0.8rem; border:1px solid #dbe3f0; border-radius:10px;">
                </div>
            </div>
            <div>
                <label style="display:block; margin-bottom:0.5rem; font-weight:600;">Description</label>
                <textarea name="description" rows="4" required style="width:100%; padding:0.8rem; border:1px solid #dbe3f0; border-radius:10px;"></textarea>
            </div>
            <div style="display:flex; justify-content:flex-end;">
                <button type="submit" class="btn-primary" style="background:#082544; color:white; border:none; padding:0.8rem 1.4rem; border-radius:10px; cursor:pointer;">Publish Internship</button>
            </div>
        </form>
    </div>

    <div class="content-section">
        <h3 style="margin-bottom:1rem;">Current Internships</h3>
        <table style="width:100%; border-collapse:collapse;">
            <thead>
                <tr style="background:#f8fafc;">
                    <th style="padding:0.9rem; text-align:left;">Title</th>
                    <th style="padding:0.9rem; text-align:left;">Location</th>
                    <th style="padding:0.9rem; text-align:left;">Type</th>
                    <th style="padding:0.9rem; text-align:left;">Deadline</th>
                    <th style="padding:0.9rem; text-align:left;">Status</th>
                    <th style="padding:0.9rem; text-align:left;">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($internships && $internships->num_rows > 0): ?>
                    <?php while ($row = $internships->fetch_assoc()): ?>
                        <tr style="border-bottom:1px solid #e2e8f0;">
                            <td style="padding:0.9rem;"><?php echo htmlspecialchars($row['title']); ?></td>
                            <td style="padding:0.9rem;"><?php echo htmlspecialchars($row['location']); ?></td>
                            <td style="padding:0.9rem;"><?php echo htmlspecialchars($row['type']); ?></td>
                            <td style="padding:0.9rem;"><?php echo htmlspecialchars($row['deadline']); ?></td>
                            <td style="padding:0.9rem;"><?php echo htmlspecialchars($row['status']); ?></td>
                            <td style="padding:0.9rem;">
                                <form method="POST" action="internships.php" style="display:inline;">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?php echo (int)$row['id']; ?>">
                                    <button type="submit" style="background:#ef4444; color:white; border:none; padding:0.5rem 0.8rem; border-radius:8px; cursor:pointer;">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="6" style="padding:1rem; color:#64748b;">No internships yet.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>

<?php include __DIR__ . '/../../../Includes/dash_footer.php'; ?>
