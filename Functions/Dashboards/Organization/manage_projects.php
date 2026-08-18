<?php

include "../../../Config/db.php";
include "../../../Session/Session.php";

require_role('organization');

$user = current_user();

include "../../../Includes/org_sidebar.php";
include "../../../Includes/dash_header.php";

// ===================== TEMP DEMO DATA =====================
// Replace this with a real query against your `projects` table, e.g.
// SELECT * FROM projects WHERE organization_id = ? ORDER BY posted_at DESC LIMIT 10 OFFSET ?
$projects = [
    [
        'title'      => 'Cloud Architecture Redesign',
        'posted'     => 'Posted Oct 24, 2023',
        'category'   => 'devops',
        'category_label' => 'DevOps',
        'applicants' => 14,
        'team'       => 'Nexus Systems',
        'deadline'   => 'Dec 15, 2023',
        'status'     => 'reviewing',
        'status_label' => 'Reviewing',
    ],
    [
        'title'      => 'AI Content Moderator',
        'posted'     => 'Posted Oct 20, 2023',
        'category'   => 'aiml',
        'category_label' => 'AI/ML',
        'applicants' => 8,
        'team'       => null, // pending assignment
        'deadline'   => 'Jan 05, 2024',
        'status'     => 'open',
        'status_label' => 'Open',
    ],
    [
        'title'      => 'Mobile Learning App Prototype',
        'posted'     => 'Posted Oct 12, 2023',
        'category'   => 'frontend',
        'category_label' => 'Frontend',
        'applicants' => 22,
        'team'       => 'Skyline Devs',
        'deadline'   => 'Nov 28, 2023',
        'status'     => 'inprogress',
        'status_label' => 'Active',
    ],
    [
        'title'      => 'Data Science Bootcamp Platform',
        'posted'     => 'Posted Sep 30, 2023',
        'category'   => 'backend',
        'category_label' => 'Backend',
        'applicants' => 5,
        'team'       => 'Vortex Group',
        'deadline'   => 'Oct 30, 2023',
        'status'     => 'closed',
        'status_label' => 'Closed',
    ],
];

$totalProjects = 34;
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
                                <div class="project-meta"><?= htmlspecialchars($p['posted']) ?></div>
                            </td>
                            <td>
                                <span class="category-tag <?= $p['category'] ?>"><?= htmlspecialchars($p['category_label']) ?></span>
                            </td>
                            <td><?= (int)$p['applicants'] ?></td>
                            <td>
                                <?php if ($p['team']): ?>
                                    <div class="team-cell">
                                        <span class="team-dot"></span>
                                        <?= htmlspecialchars($p['team']) ?>
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
                                <span class="badge-status <?= $p['status'] ?>"><?= htmlspecialchars($p['status_label']) ?></span>
                            </td>
                            <td>
                                <div class="row-actions">
                                    <button class="action-btn" title="View">
                                        <span class="material-symbols-outlined" style="font-size:18px;">visibility</span>
                                    </button>
                                    <button class="action-btn" title="Edit">
                                        <span class="material-symbols-outlined" style="font-size:18px;">edit</span>
                                    </button>
                                    <button class="action-btn danger" title="Delete">
                                        <span class="material-symbols-outlined" style="font-size:18px;">delete</span>
                                    </button>
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