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
$searchQuery    = trim($_GET['search'] ?? '');   // from the header search box

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

$filtered = array_values(array_filter($allProjects, function ($p) use ($statusFilter, $categoryFilter, $searchQuery) {
    if ($statusFilter !== 'all' && $p['status'] !== $statusFilter) return false;
    if ($categoryFilter !== 'all' && $p['category'] !== $categoryFilter) return false;
    if ($searchQuery !== '') {
        // search in title, category and keywords/skills — matches the START of any word
        // (so "AI" finds "AI Chatbot" but not "Tailwind", and "flut" finds "Flutter")
        $haystack = ($p['title'] ?? '') . ' ' . ($p['category'] ?? '') . ' ' . ($p['keywords'] ?? '');
        if (!preg_match('/(?<![\p{L}\p{N}])' . preg_quote($searchQuery, '/') . '/iu', $haystack)) return false;
    }
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
    global $searchQuery;
    $query = [
        'status'   => $statusFilter,
        'category' => $categoryFilter,
        'page'     => $pageNum,
    ];
    if ($searchQuery !== '') $query['search'] = $searchQuery;
    return '?' . http_build_query($query);
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

<style>
    /* ---- Delete confirmation modal (replaces the browser's native confirm()) ---- */
    .confirm-modal-overlay {
        display: none;
        position: fixed;
        inset: 0;
        /* .content > * caps width at 1240px and centers it — override that
           here so the overlay actually covers the full screen, not just
           the centered content column. */
        max-width: none !important;
        width: 100vw;
        height: 100vh;
        margin: 0 !important;
        background: rgba(15, 23, 42, 0.35);
        backdrop-filter: blur(2px);
        z-index: 1000;
        align-items: center;
        justify-content: center;
    }
    .confirm-modal-overlay.open {
        display: flex;
    }
    .confirm-modal {
        background: #fff;
        border-radius: 16px;
        width: 100%;
        max-width: 380px;
        padding: 24px;
        box-shadow: 0 20px 40px rgba(0,0,0,0.2);
    }
    .confirm-modal-icon {
        width: 44px;
        height: 44px;
        border-radius: 50%;
        background: #fee2e2;
        color: #dc2626;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 14px;
    }
    .confirm-modal h3 {
        margin: 0 0 6px 0;
        font-size: 16px;
        color: #111827;
    }
    .confirm-modal p {
        margin: 0 0 20px 0;
        font-size: 13.5px;
        color: #64748b;
        line-height: 1.5;
    }
    .confirm-modal-actions {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
    }
    .confirm-modal-actions button {
        padding: 9px 16px;
        border-radius: 8px;
        font-size: 13.5px;
        font-weight: 600;
        border: none;
        cursor: pointer;
    }
    .confirm-modal-cancel {
        background: #f1f5f9;
        color: #334155;
    }
    .confirm-modal-cancel:hover {
        background: #e2e8f0;
    }
    .confirm-modal-delete {
        background: #dc2626;
        color: #fff;
    }
    .confirm-modal-delete:hover {
        background: #b91c1c;
    }

    /* ---- Project details modal (View button) ---- */
    .pv-modal {
        background: #fff;
        border-radius: 16px;
        width: 100%;
        max-width: 580px;
        max-height: 86vh;
        display: flex;
        flex-direction: column;
        box-shadow: 0 20px 40px rgba(0,0,0,0.2);
        margin: 16px;
    }
    .pv-head { padding: 22px 24px 14px; border-bottom: 1px solid #eef0f3; display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; }
    .pv-head h3 { margin: 8px 0 0; font-size: 18px; color: #111827; line-height: 1.35; }
    .pv-tags { display: flex; gap: 8px; align-items: center; flex-wrap: wrap; }
    .pv-close { background: none; border: none; cursor: pointer; color: #6b7280; padding: 4px; border-radius: 8px; display: flex; }
    .pv-close:hover { background: #f3f4f6; color: #111827; }
    .pv-body { padding: 18px 24px; overflow-y: auto; }
    .pv-info { display: grid; grid-template-columns: repeat(2, 1fr); gap: 14px 20px; margin-bottom: 4px; }
    .pv-info-label { font-size: 11px; font-weight: 700; letter-spacing: .05em; text-transform: uppercase; color: #6b7280; margin-bottom: 3px; }
    .pv-info-value { font-size: 14px; font-weight: 600; color: #111827; }
    .pv-section { margin-top: 18px; }
    .pv-section h4 { margin: 0 0 6px; font-size: 13px; color: #374151; }
    .pv-section p { margin: 0; font-size: 13.5px; line-height: 1.6; color: #4b5563; white-space: pre-wrap; }
    .pv-chips { display: flex; flex-wrap: wrap; gap: 8px; }
    .pv-chip { background: #e8effa; color: #1e3a5f; font-size: 12px; font-weight: 500; padding: 4px 12px; border-radius: 6px; }
    .pv-foot { padding: 14px 24px 20px; border-top: 1px solid #eef0f3; display: flex; justify-content: flex-end; gap: 10px; }
    .pv-foot a, .pv-foot button { display: inline-flex; align-items: center; gap: 6px; padding: 9px 16px; border-radius: 8px; font-size: 13.5px; font-weight: 600; border: none; cursor: pointer; text-decoration: none; font-family: inherit; }
    .pv-btn-edit { background: #1e3a5f; color: #fff; }
    .pv-btn-edit:hover { background: #16304d; }
    .pv-btn-close { background: #f1f5f9; color: #334155; }
    .pv-btn-close:hover { background: #e2e8f0; }
    @media (max-width: 520px) { .pv-info { grid-template-columns: 1fr; } }
</style>

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
            <?php if ($searchQuery !== ''): ?>
                <input type="hidden" name="search" value="<?= htmlspecialchars($searchQuery) ?>">
            <?php endif; ?>
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
            <?php if ($searchQuery !== ''): ?>
                <span class="results-count" style="display:inline-flex; align-items:center; gap:6px;">
                    Search: <strong>“<?= htmlspecialchars($searchQuery) ?>”</strong>
                    <a href="manage_projects.php" title="Clear search" style="color:#2563eb; text-decoration:none; font-weight:600;">Clear</a>
                </span>
            <?php endif; ?>

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
                        <?php if (empty($projects)): ?>
                        <tr>
                            <td colspan="7" style="text-align:center; padding:36px 16px; color:#6b7280;">
                                No projects found<?= $searchQuery !== '' ? ' for “' . htmlspecialchars($searchQuery) . '”' : '' ?>.
                            </td>
                        </tr>
                        <?php endif; ?>
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
                                    <button type="button" class="action-btn" title="View" onclick="openProjectView('<?= htmlspecialchars((string)$p['id']) ?>')">
                                        <span class="material-symbols-outlined" style="font-size:18px;">visibility</span>
                                    </button>
                                    <a class="action-btn" title="Edit" href="edit_project.php?id=<?= (int)$p['id'] ?>">
                                        <span class="material-symbols-outlined" style="font-size:18px;">edit</span>
                                    </a>
                                    <button type="button" class="action-btn danger" title="Delete"
                                        onclick="openDeleteModal(<?= (int)$p['id'] ?>, '<?= addslashes(htmlspecialchars($p['title'])) ?>')">
                                        <span class="material-symbols-outlined" style="font-size:18px;">delete</span>
                                    </button>
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
            <?php if (empty($projects)): ?>
                <div style="grid-column:1 / -1; text-align:center; padding:36px 16px; color:#6b7280;">
                    No projects found<?= $searchQuery !== '' ? ' for “' . htmlspecialchars($searchQuery) . '”' : '' ?>.
                </div>
            <?php endif; ?>
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
                        <button type="button" class="action-btn" title="View" onclick="openProjectView('<?= htmlspecialchars((string)$p['id']) ?>')">
                            <span class="material-symbols-outlined" style="font-size:18px;">visibility</span>
                        </button>
                        <a class="action-btn" title="Edit" href="edit_project.php?id=<?= (int)$p['id'] ?>">
                            <span class="material-symbols-outlined" style="font-size:18px;">edit</span>
                        </a>
                        <button type="button" class="action-btn danger" title="Delete"
                            onclick="openDeleteModal(<?= (int)$p['id'] ?>, '<?= addslashes(htmlspecialchars($p['title'])) ?>')">
                            <span class="material-symbols-outlined" style="font-size:18px;">delete</span>
                        </button>
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

    <!-- ===================== DELETE CONFIRMATION MODAL ===================== -->
    <div class="confirm-modal-overlay" id="deleteConfirmModal">
        <div class="confirm-modal">
            <div class="confirm-modal-icon">
                <span class="material-symbols-outlined">warning</span>
            </div>
            <h3>Delete this project?</h3>
            <p>You're about to delete "<strong id="deleteProjectTitle"></strong>". This action cannot be undone.</p>
            <div class="confirm-modal-actions">
                <button type="button" class="confirm-modal-cancel" onclick="closeDeleteModal()">Cancel</button>
                <button type="button" class="confirm-modal-delete" onclick="confirmDelete()">Delete</button>
            </div>
        </div>
    </div>

    <!-- ===================== PROJECT DETAILS MODAL (View) ===================== -->
    <?php
        $projectViewData = [];
        foreach ($projects as $p) {
            if (!empty($p['is_fake'])) {
                $vApplicants = (int)$p['applicants'];
                $vAssigned   = (int)$p['assigned'];
            } else {
                $vStmt = $conn->prepare("SELECT COUNT(*) FROM student_projects WHERE project_id = ?");
                $vStmt->bind_param("i", $p['id']);
                $vStmt->execute();
                $vApplicants = $vAssigned = (int)$vStmt->get_result()->fetch_row()[0];
            }
            $projectViewData[(string)$p['id']] = [
                'id'         => $p['id'],
                'fake'       => !empty($p['is_fake']),
                'title'      => $p['title'] ?? '',
                'category'   => $p['category'] ?? '',
                'status'     => $p['status'] ?? '',
                'posted'     => date('M d, Y', strtotime($p['posted_at'])),
                'deadline'   => $p['deadline'] ?? '',
                'applicants' => $vApplicants,
                'assigned'   => $vAssigned,
                'visibility' => $p['visibility'] ?? '',
                'duration'   => $p['duration'] ?? '',
                'members'    => $p['members'] ?? '',
                'year'       => $p['preferred_year'] ?? '',
                'difficulty' => $p['difficulty'] ?? '',
                'keywords'   => $p['keywords'] ?? '',
                'description'=> $p['description'] ?? '',
                'objectives' => $p['learning_objectives'] ?? '',
                'outcomes'   => $p['expected_outcomes'] ?? '',
            ];
        }
    ?>
    <div class="confirm-modal-overlay" id="projectViewModal">
        <div class="pv-modal" role="dialog" aria-modal="true" aria-labelledby="pvTitle">
            <div class="pv-head">
                <div>
                    <div class="pv-tags"><span class="category-tag" id="pvCategory"></span><span class="badge-status" id="pvStatus"></span></div>
                    <h3 id="pvTitle"></h3>
                </div>
                <button type="button" class="pv-close" onclick="closeProjectView()" aria-label="Close">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <div class="pv-body">
                <div class="pv-info" id="pvInfo"></div>
                <div id="pvSections"></div>
            </div>
            <div class="pv-foot">
                <button type="button" class="pv-btn-close" onclick="closeProjectView()">Close</button>
                <a class="pv-btn-edit" id="pvEdit" href="#"><span class="material-symbols-outlined" style="font-size:18px;">edit</span>Edit Project</a>
            </div>
        </div>
    </div>

</main>

<script>
    const PROJECT_VIEW_DATA = <?= json_encode($projectViewData, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) ?>;

    function pvEl(tag, cls, text) {
        const el = document.createElement(tag);
        if (cls) el.className = cls;
        if (text !== undefined) el.textContent = text;
        return el;
    }

    function openProjectView(id) {
        const d = PROJECT_VIEW_DATA[id];
        if (!d) return;

        document.getElementById('pvTitle').textContent = d.title;
        document.getElementById('pvCategory').textContent = d.category;
        const st = document.getElementById('pvStatus');
        st.className = 'badge-status ' + d.status;
        st.textContent = d.status ? d.status.charAt(0).toUpperCase() + d.status.slice(1) : '';

        // key facts (empty ones are skipped)
        const info = document.getElementById('pvInfo');
        info.innerHTML = '';
        [
            ['Posted', d.posted],
            ['Deadline', d.deadline],
            ['Applicants', d.applicants],
            ['Assigned Team', d.assigned > 0 ? 'Assigned (' + d.assigned + ')' : 'Pending'],
            ['Visibility', d.visibility],
            ['Duration', d.duration],
            ['Students Required', d.members],
            ['Preferred Year', d.year],
            ['Difficulty', d.difficulty]
        ].forEach(function (row) {
            if (row[1] === '' || row[1] === null || row[1] === undefined) return;
            const box = pvEl('div');
            box.appendChild(pvEl('div', 'pv-info-label', row[0]));
            box.appendChild(pvEl('div', 'pv-info-value', String(row[1])));
            info.appendChild(box);
        });

        // text sections (empty ones are skipped)
        const sections = document.getElementById('pvSections');
        sections.innerHTML = '';
        [['Description', d.description], ['Learning Objectives', d.objectives], ['Expected Outcomes', d.outcomes]].forEach(function (sec) {
            if (!sec[1]) return;
            const box = pvEl('div', 'pv-section');
            box.appendChild(pvEl('h4', '', sec[0]));
            box.appendChild(pvEl('p', '', sec[1]));
            sections.appendChild(box);
        });
        if (d.keywords) {
            const box = pvEl('div', 'pv-section');
            box.appendChild(pvEl('h4', '', 'Skills / Keywords'));
            const chips = pvEl('div', 'pv-chips');
            d.keywords.split(',').forEach(function (k) {
                k = k.trim();
                if (k) chips.appendChild(pvEl('span', 'pv-chip', k));
            });
            box.appendChild(chips);
            sections.appendChild(box);
        }

        document.getElementById('pvEdit').href = 'edit_project.php?id=' + encodeURIComponent(d.id);
        document.getElementById('projectViewModal').classList.add('open');
    }

    function closeProjectView() {
        document.getElementById('projectViewModal').classList.remove('open');
    }

    document.getElementById('projectViewModal').addEventListener('click', function (e) {
        if (e.target === this) closeProjectView();
    });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeProjectView();
    });
</script>

<script>
    let pendingDeleteId = null;

    function openDeleteModal(id, title) {
        pendingDeleteId = id;
        document.getElementById('deleteProjectTitle').textContent = title;
        document.getElementById('deleteConfirmModal').classList.add('open');
    }

    function closeDeleteModal() {
        pendingDeleteId = null;
        document.getElementById('deleteConfirmModal').classList.remove('open');
    }

    function confirmDelete() {
        if (pendingDeleteId !== null) {
            window.location.href = 'manage_projects.php?delete=' + pendingDeleteId;
        }
    }

    document.getElementById('deleteConfirmModal').addEventListener('click', function (e) {
        if (e.target === this) closeDeleteModal();
    });
</script>

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