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

// ---- Pagination ----
$perPage = 5;
$page    = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;

// ---- Fetch ALL of this org's real projects first (no SQL limit here — we paginate in PHP below) ----
$realStmt = $conn->prepare("SELECT * FROM projects WHERE organization_email = ? ORDER BY posted_at DESC");
$realStmt->bind_param("s", $organization_email);
$realStmt->execute();
$realProjects = $realStmt->get_result()->fetch_all(MYSQLI_ASSOC);
foreach ($realProjects as &$rp) { $rp['is_fake'] = false; }
unset($rp);

$totalProjects = count($realProjects);

// ---- Demo/fake project pool — same idea as the dashboard's fake fill, so both pages match ----
// Only used to pad the list up to $demoTarget rows, so pagination (Next/Previous) has something to show.
$demoTarget = 24;
$fakePool = [
    ['title' => 'AI Chatbot for Student Support', 'category' => 'AI / Machine Learning', 'keywords' => 'Python, NLP, Flask', 'deadline' => date('Y-m-d', strtotime('+20 day')), 'status' => 'open', 'applicants' => 5, 'assigned' => 0],
    ['title' => 'Mobile Attendance Tracker', 'category' => 'Mobile Development', 'keywords' => 'Flutter, Firebase', 'deadline' => date('Y-m-d', strtotime('+15 day')), 'status' => 'reviewing', 'applicants' => 3, 'assigned' => 0],
    ['title' => 'Portfolio Website Builder', 'category' => 'Web Development', 'keywords' => 'React, Tailwind CSS', 'deadline' => date('Y-m-d', strtotime('+25 day')), 'status' => 'open', 'applicants' => 2, 'assigned' => 0],
    ['title' => 'Campus Event Management System', 'category' => 'Web Development', 'keywords' => 'Laravel, MySQL', 'deadline' => date('Y-m-d', strtotime('+5 day')), 'status' => 'closed', 'applicants' => 6, 'assigned' => 2],
    ['title' => 'Smart Library Assistant', 'category' => 'Other', 'keywords' => 'Java, Spring Boot', 'deadline' => date('Y-m-d', strtotime('+30 day')), 'status' => 'inprogress', 'applicants' => 4, 'assigned' => 1],
    ['title' => 'Cloud Cost Optimizer Dashboard', 'category' => 'Cloud & DevOps', 'keywords' => 'AWS, Terraform', 'deadline' => date('Y-m-d', strtotime('+18 day')), 'status' => 'open', 'applicants' => 1, 'assigned' => 0],
    ['title' => 'Phishing Detection Browser Extension', 'category' => 'Cybersecurity', 'keywords' => 'JavaScript, ML', 'deadline' => date('Y-m-d', strtotime('+22 day')), 'status' => 'reviewing', 'applicants' => 7, 'assigned' => 0],
    ['title' => 'University Course Recommender', 'category' => 'Data Science', 'keywords' => 'Python, Pandas', 'deadline' => date('Y-m-d', strtotime('+12 day')), 'status' => 'inprogress', 'applicants' => 3, 'assigned' => 2],
    ['title' => 'Redesign of Student Portal UI', 'category' => 'UI/UX Design', 'keywords' => 'Figma, Design System', 'deadline' => date('Y-m-d', strtotime('+10 day')), 'status' => 'open', 'applicants' => 5, 'assigned' => 0],
    ['title' => 'Freelance Marketplace for Students', 'category' => 'Web Development', 'keywords' => 'Node.js, MongoDB', 'deadline' => date('Y-m-d', strtotime('+28 day')), 'status' => 'closed', 'applicants' => 9, 'assigned' => 3],
    ['title' => 'Fitness Tracker Mobile App', 'category' => 'Mobile Development', 'keywords' => 'Kotlin, Room DB', 'deadline' => date('Y-m-d', strtotime('+14 day')), 'status' => 'open', 'applicants' => 2, 'assigned' => 0],
    ['title' => 'Resume Screening AI Tool', 'category' => 'AI / Machine Learning', 'keywords' => 'Python, spaCy', 'deadline' => date('Y-m-d', strtotime('+9 day')), 'status' => 'reviewing', 'applicants' => 4, 'assigned' => 0],
    ['title' => 'Inventory Management System', 'category' => 'Web Development', 'keywords' => 'PHP, MySQL', 'deadline' => date('Y-m-d', strtotime('+16 day')), 'status' => 'inprogress', 'applicants' => 3, 'assigned' => 1],
    ['title' => 'Bug Bounty Leaderboard', 'category' => 'Cybersecurity', 'keywords' => 'Go, PostgreSQL', 'deadline' => date('Y-m-d', strtotime('+7 day')), 'status' => 'open', 'applicants' => 2, 'assigned' => 0],
    ['title' => 'Serverless Data Pipeline Demo', 'category' => 'Cloud & DevOps', 'keywords' => 'AWS Lambda, Python', 'deadline' => date('Y-m-d', strtotime('+19 day')), 'status' => 'closed', 'applicants' => 5, 'assigned' => 2],
    ['title' => 'Sales Forecasting Dashboard', 'category' => 'Data Science', 'keywords' => 'R, Power BI', 'deadline' => date('Y-m-d', strtotime('+11 day')), 'status' => 'reviewing', 'applicants' => 1, 'assigned' => 0],
    ['title' => 'Accessibility Audit & Redesign', 'category' => 'UI/UX Design', 'keywords' => 'WCAG, Figma', 'deadline' => date('Y-m-d', strtotime('+13 day')), 'status' => 'open', 'applicants' => 3, 'assigned' => 0],
    ['title' => 'Peer Tutoring Booking App', 'category' => 'Mobile Development', 'keywords' => 'React Native', 'deadline' => date('Y-m-d', strtotime('+21 day')), 'status' => 'inprogress', 'applicants' => 4, 'assigned' => 1],
    ['title' => 'Open Source Contribution Tracker', 'category' => 'Web Development', 'keywords' => 'GitHub API, Go', 'deadline' => date('Y-m-d', strtotime('+6 day')), 'status' => 'closed', 'applicants' => 8, 'assigned' => 3],
    ['title' => 'Voice Assistant for Campus Navigation', 'category' => 'AI / Machine Learning', 'keywords' => 'Python, Speech-to-Text', 'deadline' => date('Y-m-d', strtotime('+24 day')), 'status' => 'open', 'applicants' => 2, 'assigned' => 0],
];

$fakeProjects = [];
$needed = max(0, $demoTarget - $totalProjects);
foreach (array_slice($fakePool, 0, $needed) as $i => $fp) {
    $fp['id']            = 'demo-' . ($i + 1);
    $fp['posted_at']     = date('Y-m-d', strtotime('-' . ($i + 1) . ' day'));
    $fp['is_fake']       = true;
    $fp['proposal_count']= $fp['applicants'];
    $fakeProjects[] = $fp;
}

// ---- Combine real + demo data, then apply filters + pagination in PHP ----
$allProjects = array_merge($realProjects, $fakeProjects);

// ---- Grand totals (real + demo combined) — used by the stat cards below,
//      so the numbers always match what the table actually shows ----
$totalProjectsAll   = count($allProjects);
$fakeApplicantsSum  = array_sum(array_column($fakeProjects, 'applicants'));
$fakeAssignedCount  = count(array_filter($fakeProjects, function ($p) { return (int)$p['assigned'] > 0; }));

$filtered = array_values(array_filter($allProjects, function ($p) use ($statusFilter, $categoryFilter) {
    if ($statusFilter !== 'all' && $p['status'] !== $statusFilter) return false;
    if ($categoryFilter !== 'all' && $p['category'] !== $categoryFilter) return false;
    return true;
}));

$filteredTotal = count($filtered);
$totalPages    = max(1, (int)ceil($filteredTotal / $perPage));
$page          = min($page, $totalPages); // clamp if someone jumps past the last page
$offset        = ($page - 1) * $perPage;

$projects   = array_slice($filtered, $offset, $perPage);
$shownCount = count($projects);

// Helper to build a pagination link that keeps the current filters
function buildPageUrl($pageNum, $statusFilter, $categoryFilter) {
    return '?' . http_build_query([
        'status'   => $statusFilter,
        'category' => $categoryFilter,
        'page'     => $pageNum,
    ]);
}

// ---- Bottom summary stat cards (real data) ----
$stmt = $conn->prepare("SELECT COUNT(*) FROM student_projects sp
                         JOIN projects p ON sp.project_id = p.id
                         WHERE p.organization_email = ?");
$stmt->bind_param("s", $organization_email);
$stmt->execute();
$activeApplications = (int)$stmt->get_result()->fetch_row()[0] + $fakeApplicantsSum;

// "Assigned Teams" = number of this org's projects that have at least one student attached
$stmt = $conn->prepare("SELECT COUNT(DISTINCT sp.project_id) FROM student_projects sp
                         JOIN projects p ON sp.project_id = p.id
                         WHERE p.organization_email = ?");
$stmt->bind_param("s", $organization_email);
$stmt->execute();
$assignedTeams = (int)$stmt->get_result()->fetch_row()[0] + $fakeAssignedCount;

// No timestamp data exists yet to measure real response time
$avgResponseTime = null;
?>

<main class="content">

    <div class="dashboard-header">
        <div>
            <h1>Project Management</h1>
            <p>Curate and monitor your posted projects and student collaborations.</p>
        </div>
    </div>

    <!-- ===================== SUMMARY STAT CARDS ===================== -->
    <div class="stats-grid">

        <div class="stat-card">
            <div class="stat-card-top">
                <div class="stat-icon blue">
                    <span class="material-symbols-outlined">rocket_launch</span>
                </div>
            </div>
            <div class="stat-info">
                <div class="stat-label">Total Projects Posted</div>
                <div class="stat-value"><?= (int)$totalProjectsAll ?></div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-card-top">
                <div class="stat-icon green">
                    <span class="material-symbols-outlined">groups</span>
                </div>
            </div>
            <div class="stat-info">
                <div class="stat-label">Active Applications</div>
                <div class="stat-value"><?= $activeApplications ?></div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-card-top">
                <div class="stat-icon slate">
                    <span class="material-symbols-outlined">sentiment_neutral</span>
                </div>
            </div>
            <div class="stat-info">
                <div class="stat-label">Assigned Teams</div>
                <div class="stat-value"><?= $assignedTeams ?></div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-card-top">
                <div class="stat-icon navy">
                    <span class="material-symbols-outlined">timer</span>
                </div>
            </div>
            <div class="stat-info">
                <div class="stat-label">Avg. Response Time</div>
                <div class="stat-value"><?= $avgResponseTime ?? 'N/A' ?></div>
            </div>
        </div>

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
                <option value="Web Development" <?= (($_GET['category'] ?? '') == 'Web Development') ? 'selected' : '' ?>>Web Development</option>
                <option value="Mobile Development" <?= (($_GET['category'] ?? '') == 'Mobile Development') ? 'selected' : '' ?>>Mobile Development</option>
                <option value="AI / Machine Learning" <?= (($_GET['category'] ?? '') == 'AI / Machine Learning') ? 'selected' : '' ?>>AI / Machine Learning</option>
                <option value="Data Science" <?= (($_GET['category'] ?? '') == 'Data Science') ? 'selected' : '' ?>>Data Science</option>
                <option value="UI/UX Design" <?= (($_GET['category'] ?? '') == 'UI/UX Design') ? 'selected' : '' ?>>UI/UX Design</option>
                <option value="Cloud & DevOps" <?= (($_GET['category'] ?? '') == 'Cloud & DevOps') ? 'selected' : '' ?>>Cloud & DevOps</option>
                <option value="Cybersecurity" <?= (($_GET['category'] ?? '') == 'Cybersecurity') ? 'selected' : '' ?>>Cybersecurity</option>
                <option value="Other" <?= (($_GET['category'] ?? '') == 'Other') ? 'selected' : '' ?>>Other</option>
            </select>
        </form>

        <div class="toolbar-meta">
            <span class="results-count">Showing <?= $shownCount ?> of <?= $filteredTotal ?> projects<?= $totalPages > 1 ? " (Page $page of $totalPages)" : '' ?></span>

            <div class="view-toggle-group" role="group" aria-label="Project view">
                <button type="button" class="active" data-view="list" title="List view" aria-label="List view" aria-pressed="true">
                    <span class="material-symbols-outlined">view_list</span>
                </button>
                <button type="button" data-view="grid" title="Grid view" aria-label="Grid view" aria-pressed="false">
                    <span class="material-symbols-outlined">grid_view</span>
                </button>
            </div>
        </div>

    </div>

    <!-- ===================== PROJECTS TABLE ===================== -->
    <div class="full-width-section project-list-view" id="projectListView">
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
                            <td>
                                <?php
                                    if (!empty($p['is_fake'])) {
                                        echo (int)$p['applicants'];
                                    } else {
                                        $appStmt = $conn->prepare("SELECT COUNT(*) FROM student_projects WHERE project_id = ?");
                                        $appStmt->bind_param("i", $p['id']);
                                        $appStmt->execute();
                                        echo (int)$appStmt->get_result()->fetch_row()[0];
                                    }
                                ?>
                            </td>
                            <td>
                                <?php
                                    if (!empty($p['is_fake'])) {
                                        $assignedCount = (int)$p['assigned'];
                                    } else {
                                        $teamStmt = $conn->prepare("SELECT COUNT(*) FROM student_projects WHERE project_id = ?");
                                        $teamStmt->bind_param("i", $p['id']);
                                        $teamStmt->execute();
                                        $assignedCount = (int)$teamStmt->get_result()->fetch_row()[0];
                                    }
                                ?>
                                <?php if ($assignedCount > 0): ?>
                                    <div class="team-cell" style="color:#16a34a; font-size:12px; font-style:italic;">
                                        <span class="team-dot" style="background:#16a34a;"></span>
                                        Assigned (<?= $assignedCount ?>)
                                    </div>
                                <?php else: ?>
                                    <div class="team-cell pending">
                                        <span class="team-dot"></span>
                                        Pending
                                    </div>
                                <?php endif; ?>
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
                <?php if ($totalPages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="<?= buildPageUrl($page - 1, $statusFilter, $categoryFilter) ?>" class="pagination-link">&lsaquo; Previous</a>
                    <?php else: ?>
                        <span class="pagination-link" style="opacity:.4; pointer-events:none;">&lsaquo; Previous</span>
                    <?php endif; ?>

                    <div class="page-numbers">
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <a href="<?= buildPageUrl($i, $statusFilter, $categoryFilter) ?>" class="page-btn <?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
                        <?php endfor; ?>
                    </div>

                    <?php if ($page < $totalPages): ?>
                        <a href="<?= buildPageUrl($page + 1, $statusFilter, $categoryFilter) ?>" class="pagination-link">Next &rsaquo;</a>
                    <?php else: ?>
                        <span class="pagination-link" style="opacity:.4; pointer-events:none;">Next &rsaquo;</span>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- ===================== PROJECTS GRID VIEW ===================== -->
    <div class="projects-grid-view" id="projectGridView" hidden>
        <div class="projects-management-grid">
            <?php foreach ($projects as $p): ?>
                <?php
                    if (!empty($p['is_fake'])) {
                        $gridApplicants = (int)$p['applicants'];
                        $gridAssigned = (int)$p['assigned'];
                    } else {
                        $gridAppStmt = $conn->prepare("SELECT COUNT(*) FROM student_projects WHERE project_id = ?");
                        $gridAppStmt->bind_param("i", $p['id']);
                        $gridAppStmt->execute();
                        $gridApplicants = (int)$gridAppStmt->get_result()->fetch_row()[0];

                        $gridTeamStmt = $conn->prepare("SELECT COUNT(*) FROM student_projects WHERE project_id = ?");
                        $gridTeamStmt->bind_param("i", $p['id']);
                        $gridTeamStmt->execute();
                        $gridAssigned = (int)$gridTeamStmt->get_result()->fetch_row()[0];
                    }
                ?>
                <article class="project-management-card">
                    <div class="project-management-card-top">
                        <span class="category-tag"><?= htmlspecialchars($p['category']) ?></span>
                        <span class="badge-status <?= htmlspecialchars($p['status']) ?>"><?= htmlspecialchars(ucfirst($p['status'])) ?></span>
                    </div>

                    <h3><?= htmlspecialchars($p['title']) ?></h3>
                    <div class="project-management-date">Posted <?= htmlspecialchars(date('M d, Y', strtotime($p['posted_at']))) ?></div>

                    <div class="project-management-details">
                        <div>
                            <span class="material-symbols-outlined">groups</span>
                            <span><strong><?= $gridApplicants ?></strong> Applicants</span>
                        </div>
                        <div>
                            <span class="material-symbols-outlined">group</span>
                            <span><?= $gridAssigned > 0 ? '<strong>Assigned (' . $gridAssigned . ')</strong>' : 'Pending' ?></span>
                        </div>
                        <div>
                            <span class="material-symbols-outlined">event</span>
                            <span>Deadline: <strong><?= htmlspecialchars($p['deadline']) ?></strong></span>
                        </div>
                    </div>

                    <div class="project-management-actions">
                        <?php if (empty($p['is_fake'])): ?>
                            <a class="action-btn" title="View" href="proposal.php?project_id=<?= (int)$p['id'] ?>">
                                <span class="material-symbols-outlined" style="font-size:18px;">visibility</span>
                            </a>
                            <a class="action-btn" title="Edit" href="edit_project.php?id=<?= (int)$p['id'] ?>">
                                <span class="material-symbols-outlined" style="font-size:18px;">edit</span>
                            </a>
                            <a class="action-btn danger" title="Delete" href="manage_projects.php?delete=<?= (int)$p['id'] ?>" onclick="return confirm('Delete this project?')">
                                <span class="material-symbols-outlined" style="font-size:18px;">delete</span>
                            </a>
                        <?php else: ?>
                            <span class="demo-project-label">Demo project</span>
                        <?php endif; ?>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>

        <!-- Same pagination is available in grid view. -->
        <?php if ($totalPages > 1): ?>
        <div class="pagination grid-pagination">
            <?php if ($page > 1): ?>
                <a href="<?= buildPageUrl($page - 1, $statusFilter, $categoryFilter) ?>" class="pagination-link">&lsaquo; Previous</a>
            <?php else: ?>
                <span class="pagination-link" style="opacity:.4; pointer-events:none;">&lsaquo; Previous</span>
            <?php endif; ?>

            <div class="page-numbers">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a href="<?= buildPageUrl($i, $statusFilter, $categoryFilter) ?>" class="page-btn <?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
                <?php endfor; ?>
            </div>

            <?php if ($page < $totalPages): ?>
                <a href="<?= buildPageUrl($page + 1, $statusFilter, $categoryFilter) ?>" class="pagination-link">Next &rsaquo;</a>
            <?php else: ?>
                <span class="pagination-link" style="opacity:.4; pointer-events:none;">Next &rsaquo;</span>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>

</main>

<script>
(function () {
    const listView = document.getElementById('projectListView');
    const gridView = document.getElementById('projectGridView');
    const buttons = document.querySelectorAll('.view-toggle-group button[data-view]');

    if (!listView || !gridView || !buttons.length) return;

    function setView(view, save = true) {
        const isGrid = view === 'grid';
        listView.hidden = isGrid;
        gridView.hidden = !isGrid;

        buttons.forEach(button => {
            const active = button.dataset.view === view;
            button.classList.toggle('active', active);
            button.setAttribute('aria-pressed', active ? 'true' : 'false');
        });

        if (save) {
            try { localStorage.setItem('skillbridge-project-view', view); } catch (e) {}
        }
    }

    buttons.forEach(button => {
        button.addEventListener('click', function () {
            setView(this.dataset.view);
        });
    });

    let savedView = 'list';
    try {
        savedView = localStorage.getItem('skillbridge-project-view') || 'list';
    } catch (e) {}
    setView(savedView, false);
})();
</script>

<footer class="footer">
    <div>&copy; 2026 SkillBridge. All rights reserved.</div>
    <div class="footer-links">
        <a href="#">Help Center</a>
        <a href="#">Privacy Policy</a>
        <a href="#">Terms of Service</a>
    </div>
</footer>

<?php include "../../../Includes/dash_footer.php"; ?>