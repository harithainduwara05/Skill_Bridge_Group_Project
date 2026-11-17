<?php
require_once __DIR__ . '/project_bootstrap.php';

$pageTitle = "Application Tracking | SkillBridge";
$extra_css = '
<link rel="stylesheet" href="../../Assets/CSS/Student/dashboard.css">
<link rel="stylesheet" href="../../Assets/CSS/Student/applications.css?v=' . time() . '">
';

include "../../Includes/student_sidebar.php";
include "../../Includes/dash_header.php";

// Get filter status from query parameters (default to 'all')
$filterStatus = isset($_GET['status']) ? strtolower(trim($_GET['status'])) : 'all';

// Initialize stats
$stats = [
    'applied' => 0,
    'accepted' => 0,
    'interviews' => 0,
    'rejected' => 0
];

$applications = [];

try {
    // Determine which table handles applications based on repository logic
    $isProjectProposals = $projectRepo->supportsDetailedProposal();
    $sql = "";
    
    if ($isProjectProposals) {
        $sql = "SELECT pp.proposal_id AS id, pp.project_id, p.title AS position, org.Name AS company, 
                       org.Location AS location, pp.submitted_at AS applied_date, pp.status,
                       'Proposal' AS type
                FROM project_proposals pp
                JOIN projects p ON pp.project_id = p.id
                LEFT JOIN organization org ON p.organization_email = org.Email
                WHERE LOWER(TRIM(pp.student_email)) = LOWER(TRIM(?))";
    } else {
        $sql = "SELECT pa.id, pa.project_id, p.title AS position, org.Name AS company, 
                       org.Location AS location, pa.applied_at AS applied_date, pa.status,
                       'Application' AS type
                FROM project_applications pa
                JOIN projects p ON pa.project_id = p.id
                LEFT JOIN organization org ON p.organization_email = org.Email
                WHERE LOWER(TRIM(pa.Email)) = LOWER(TRIM(?))";
    }

    $stmt = $conn->prepare($sql . " ORDER BY applied_date DESC");
    if ($stmt) {
        $stmt->bind_param("s", $projectEmail);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            $status = strtolower($row['status']);
            
            // Calculate Stats
            if ($status === 'pending' || $status === 'applied') {
                $stats['applied']++;
            } elseif ($status === 'accepted' || $status === 'approved') {
                $stats['accepted']++;
            } elseif ($status === 'interview') {
                $stats['interviews']++;
            } elseif ($status === 'rejected') {
                $stats['rejected']++;
            }

            // Apply filter
            if ($filterStatus === 'all' || 
                ($filterStatus === 'applied' && in_array($status, ['pending', 'applied'])) ||
                $filterStatus === $status) {
                $applications[] = $row;
            }
        }
        $stmt->close();
    }
} catch (Throwable $e) {
    error_log("Failed to load applications: " . $e->getMessage());
}

?>

<main class="content">
    <section class="applications-header">
        <div>
            <h1>Application Tracking</h1>
            <p>Monitor your professional journey. Keep track of every internship and job application in real-time.</p>
        </div>
        <div>
            <a href="projects.php" class="btn-primary">+ New Application</a>
        </div>
    </section>

    <!-- Statistics Cards -->
    <div class="stats-container" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 25px;">
        <div class="stat-card" style="background: #fff; padding: 20px; border-radius: 12px; border: 1px solid #e2e8f0; display: flex; align-items: center; gap: 16px;">
            <div class="stat-icon" style="width: 48px; height: 48px; background: #eff6ff; color: #3b82f6; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 20px;">
                <i class="fa-solid fa-paper-plane"></i>
            </div>
            <div>
                <span style="font-size: 12px; color: #64748b; font-weight: 700; text-transform: uppercase;">Applied</span>
                <h2 style="margin: 4px 0 0; font-size: 24px; color: #0f172a; font-weight: 800;"><?= str_pad((string)$stats['applied'], 2, '0', STR_PAD_LEFT) ?></h2>
            </div>
        </div>

        <div class="stat-card" style="background: #fff; padding: 20px; border-radius: 12px; border: 1px solid #e2e8f0; display: flex; align-items: center; gap: 16px;">
            <div class="stat-icon" style="width: 48px; height: 48px; background: #f0fdf4; color: #22c55e; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 20px;">
                <i class="fa-solid fa-circle-check"></i>
            </div>
            <div>
                <span style="font-size: 12px; color: #64748b; font-weight: 700; text-transform: uppercase;">Accepted</span>
                <h2 style="margin: 4px 0 0; font-size: 24px; color: #0f172a; font-weight: 800;"><?= str_pad((string)$stats['accepted'], 2, '0', STR_PAD_LEFT) ?></h2>
            </div>
        </div>

        <div class="stat-card" style="background: #fff; padding: 20px; border-radius: 12px; border: 1px solid #e2e8f0; display: flex; align-items: center; gap: 16px;">
            <div class="stat-icon" style="width: 48px; height: 48px; background: #f5f3ff; color: #8b5cf6; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 20px;">
                <i class="fa-solid fa-comments"></i>
            </div>
            <div>
                <span style="font-size: 12px; color: #64748b; font-weight: 700; text-transform: uppercase;">Interviews</span>
                <h2 style="margin: 4px 0 0; font-size: 24px; color: #0f172a; font-weight: 800;"><?= str_pad((string)$stats['interviews'], 2, '0', STR_PAD_LEFT) ?></h2>
            </div>
        </div>
    </div>

    <!-- Data Table Section -->
    <section class="applications-list-container" style="background: #fff; border-radius: 12px; border: 1px solid #e2e8f0; padding: 20px;">
        <div class="list-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3 style="margin: 0; font-size: 18px; color: #0f172a;">All Applications</h3>
            <div class="filter-dropdown">
                <form method="GET" action="applications.php" id="statusFilterForm">
                    <label for="statusFilter" style="font-size: 14px; color: #64748b; margin-right: 8px;">Filter by Status:</label>
                    <select name="status" id="statusFilter" onchange="document.getElementById('statusFilterForm').submit();" style="padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 14px; outline: none;">
                        <option value="all" <?= $filterStatus === 'all' ? 'selected' : '' ?>>All Statuses</option>
                        <option value="applied" <?= $filterStatus === 'applied' ? 'selected' : '' ?>>Applied</option>
                        <option value="interview" <?= $filterStatus === 'interview' ? 'selected' : '' ?>>Interview</option>
                        <option value="accepted" <?= $filterStatus === 'accepted' ? 'selected' : '' ?>>Accepted</option>
                        <option value="rejected" <?= $filterStatus === 'rejected' ? 'selected' : '' ?>>Rejected</option>
                    </select>
                </form>
            </div>
        </div>

        <table style="width: 100%; border-collapse: collapse; text-align: left;">
            <thead>
                <tr style="border-bottom: 1px solid #e2e8f0;">
                    <th style="padding: 12px 16px; font-size: 12px; text-transform: uppercase; color: #64748b;">Company</th>
                    <th style="padding: 12px 16px; font-size: 12px; text-transform: uppercase; color: #64748b;">Position</th>
                    <th style="padding: 12px 16px; font-size: 12px; text-transform: uppercase; color: #64748b;">Applied Date</th>
                    <th style="padding: 12px 16px; font-size: 12px; text-transform: uppercase; color: #64748b;">Status</th>
                    <th style="padding: 12px 16px; font-size: 12px; text-transform: uppercase; color: #64748b;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($applications)): ?>
                    <tr>
                        <td colspan="5" style="padding: 30px; text-align: center; color: #64748b;">No applications found.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($applications as $app): 
                        $statusClass = '';
                        $statusLabel = ucfirst($app['status']);
                        
                        if (in_array(strtolower($app['status']), ['pending', 'applied'])) {
                            $statusClass = 'background: #eff6ff; color: #3b82f6;';
                            $statusLabel = 'Applied';
                        } elseif (strtolower($app['status']) === 'accepted') {
                            $statusClass = 'background: #f0fdf4; color: #22c55e;';
                        } elseif (strtolower($app['status']) === 'rejected') {
                            $statusClass = 'background: #fef2f2; color: #ef4444;';
                        } elseif (strtolower($app['status']) === 'interview') {
                            $statusClass = 'background: #f5f3ff; color: #8b5cf6;';
                        } else {
                            $statusClass = 'background: #f1f5f9; color: #64748b;';
                        }

                        $companyInitial = strtoupper(substr($app['company'] ?? 'C', 0, 1));
                    ?>
                        <tr style="border-bottom: 1px solid #f1f5f9; transition: background 0.2s;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='transparent'">
                            <td style="padding: 16px; display: flex; align-items: center; gap: 12px;">
                                <div style="width: 40px; height: 40px; background: #e2e8f0; color: #0f172a; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 16px;">
                                    <?= projectEsc($companyInitial) ?>
                                </div>
                                <div>
                                    <div style="font-weight: 600; color: #0f172a;"><?= projectEsc($app['company']) ?></div>
                                    <div style="font-size: 13px; color: #64748b;"><?= projectEsc($app['location'] ?? 'Remote') ?></div>
                                </div>
                            </td>
                            <td style="padding: 16px;">
                                <div style="font-weight: 600; color: #0f172a;"><?= projectEsc($app['position']) ?></div>
                                <div style="font-size: 13px; color: #64748b;"><?= projectEsc($app['type']) ?></div>
                            </td>
                            <td style="padding: 16px; color: #64748b; font-size: 14px;">
                                <?= date('M d, Y', strtotime($app['applied_date'])) ?>
                            </td>
                            <td style="padding: 16px;">
                                <span style="<?= $statusClass ?> padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight: 600;">
                                    <?= projectEsc($statusLabel) ?>
                                </span>
                            </td>
                            <td style="padding: 16px;">
                                <a href="view_project.php?id=<?= $app['project_id'] ?>" style="color: #64748b; transition: color 0.2s;" title="View Project">
                                    <i class="fa-regular fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </section>
</main>

<?php include "../../Includes/dash_footer.php"; ?>