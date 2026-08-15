<?php

include "../../../Config/db.php";
include "../../../Session/Session.php";

require_role('organization');
$user = current_user();
$organization_email = $user['email'];

// ---- Delete action (must run before any HTML/include output) ----
if (isset($_GET['delete'])) {
    $delId = (int)$_GET['delete'];
    $delStmt = $conn->prepare("DELETE FROM projects WHERE id=? AND organization_email=?");
    $delStmt->bind_param("is", $delId, $organization_email);
    $delStmt->execute();
    header("Location: manage_projects.php");
    exit;
}

include "../../../Includes/org_sidebar.php";
include "../../../Includes/dash_header.php";

// ---- Filters ----
$statusFilter   = $_GET['status'] ?? 'all';
$categoryFilter = $_GET['category'] ?? 'all';

$sql    = "SELECT * FROM projects WHERE organization_email = ?";
$types  = "s";
$params = [$organization_email];

if ($statusFilter !== 'all') {
    $sql .= " AND status = ?";
    $types .= "s";
    $params[] = $statusFilter;
}
if ($categoryFilter !== 'all') {
    $sql .= " AND category = ?";
    $types .= "s";
    $params[] = $categoryFilter;
}
$sql .= " ORDER BY posted_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$projects = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$countStmt = $conn->prepare("SELECT COUNT(*) FROM projects WHERE organization_email=?");
$countStmt->bind_param("s", $organization_email);
$countStmt->execute();
$totalProjects = $countStmt->get_result()->fetch_row()[0];
$shownCount    = count($projects);
?>

<main class="content">

    <div class="dashboard-header">
        <div>
            <h1>Project Management</h1>
            <p>Curate and monitor your posted projects and student collaborations.</p>
        </div>

        <a href="post.php" class="btn-solid">
            <span class="material-symbols-outlined" style="font-size:18px;">add</span>
            Create New Project
        </a>
    </div>

    <!-- ===================== TOOLBAR: FILTERS + VIEW ===================== -->
    <div class="page-toolbar">

        <form class="toolbar-filters" method="get">
            <span class="filter-label">Filters:</span>

            <select name="status" class="select-filter" onchange="this.form.submit()">
                <option value="all" <?= (($_GET['status'] ?? 'all') == 'all') ? 'selected' : '' ?>>Status: All</option>
                <option value="open" <?= (($_GET['status'] ?? '') == 'open') ? 'selected' : '' ?>>Open</option>
                <option value="reviewing" <?= (($_GET['status'] ?? '') == 'reviewing') ? 'selected' : '' ?>>Reviewing</option>
                <option value="inprogress" <?= (($_GET['status'] ?? '') == 'inprogress') ? 'selected' : '' ?>>Active</option>
                <option value="closed" <?= (($_GET['status'] ?? '') == 'closed') ? 'selected' : '' ?>>Closed</option>
            </select>

            <select name="category" class="select-filter" onchange="this.form.submit()">
                <option value="all" <?= (($_GET['category'] ?? 'all') == 'all') ? 'selected' : '' ?>>Category: All</option>
                <option value="devops">DevOps</option>
                <option value="aiml">AI/ML</option>
                <option value="frontend">Frontend</option>
                <option value="backend">Backend</option>
            </select>
        </form>

        <div class="toolbar-meta">
            <span class="results-count">Showing <?= $shownCount ?> of <?= $totalProjects ?> projects</span>

            <div class="view-toggle-group">
                <button type="button" class="active" title="List view">
                    <span class="material-symbols-outlined">view_list</span>
                </button>
                <button type="button" title="Grid view">
                    <span class="material-symbols-outlined">grid_view</span>
                </button>
            </div>
        </div>

    </div>

    <!-- ===================== PROJECTS TABLE ===================== -->
    <div class="full-width-section">
        <div class="card">
            <div class="card-body" style="padding:0 0 4px;">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Project Title</th>
                            <th>Category</th>
                            <th>Applicants</th>
                            <th>Assigned Team</th>
                            <th>Deadline</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($projects as $p): ?>
                        <tr>
                            <td class="project-title-cell">
                                <div class="project-title"><?= htmlspecialchars($p['title']) ?></div>
                                <div class="project-meta">Posted <?= htmlspecialchars(date('M d, Y', strtotime($p['posted_at']))) ?></div>
                            </td>
                            <td>
                                <span class="category-tag"><?= htmlspecialchars($p['category']) ?></span>
                            </td>
                            <td>0</td>
                            <td>
                                <div class="team-cell pending">
                                    <span class="team-dot"></span>
                                    Pending
                                </div>
                            </td>
                            <td><?= htmlspecialchars($p['deadline']) ?></td>
                            <td>
                                <span class="badge-status <?= htmlspecialchars($p['status']) ?>"><?= htmlspecialchars(ucfirst($p['status'])) ?></span>
                            </td>
                            <td>
                                <div class="row-actions">
                                    <button class="action-btn" title="View">
                                        <span class="material-symbols-outlined" style="font-size:18px;">visibility</span>
                                    </button>
                                    <a class="action-btn" title="Edit" href="edit_project.php?id=<?= (int)$p['id'] ?>">
                                        <span class="material-symbols-outlined" style="font-size:18px;">edit</span>
                                    </a>
                                    <a class="action-btn danger" title="Delete" href="manage_projects.php?delete=<?= (int)$p['id'] ?>" onclick="return confirm('Delete this project?')">
                                        <span class="material-symbols-outlined" style="font-size:18px;">delete</span>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <!-- ===================== PAGINATION ===================== -->
                <div class="pagination">
                    <a href="#" class="pagination-link">&lsaquo; Previous</a>
                    <div class="page-numbers">
                        <a href="#" class="page-btn active">1</a>
                        <a href="#" class="page-btn">2</a>
                        <a href="#" class="page-btn">3</a>
                        <span class="page-dots">&hellip;</span>
                        <a href="#" class="page-btn">12</a>
                    </div>
                    <a href="#" class="pagination-link">Next &rsaquo;</a>
                </div>
            </div>
        </div>
    </div>

    <!-- ===================== SUMMARY STAT CARDS ===================== -->
    <div class="stats-grid" style="padding:14px 28px 24px;">

        <div class="stat-card">
            <div class="stat-card-top">
                <div class="stat-icon blue">
                    <span class="material-symbols-outlined">rocket_launch</span>
                </div>
                <span class="stat-trend up">↑ 12%</span>
            </div>
            <div class="stat-info">
                <div class="stat-label">Total Projects Posted</div>
                <div class="stat-value">34</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-card-top">
                <div class="stat-icon green">
                    <span class="material-symbols-outlined">groups</span>
                </div>
                <span class="stat-trend up">↑ 5%</span>
            </div>
            <div class="stat-info">
                <div class="stat-label">Active Applications</div>
                <div class="stat-value">156</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-card-top">
                <div class="stat-icon slate">
                    <span class="material-symbols-outlined">sentiment_neutral</span>
                </div>
                <span class="stat-trend down">↓ 2%</span>
            </div>
            <div class="stat-info">
                <div class="stat-label">Assigned Teams</div>
                <div class="stat-value">08</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-card-top">
                <div class="stat-icon navy">
                    <span class="material-symbols-outlined">timer</span>
                </div>
                <span class="stat-trend up">Steady</span>
            </div>
            <div class="stat-info">
                <div class="stat-label">Avg. Response Time</div>
                <div class="stat-value">4.2d</div>
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