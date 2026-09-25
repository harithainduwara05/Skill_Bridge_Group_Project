<?php

include "../../../Config/db.php";
include "../../../Session/Session.php";

require_role('organization');

$user = current_user();
$organization_email = $user['email'];

// Human-readable label for a status value (DB stores 'inprogress' for what's shown as "Active")
function reportStatusLabel($status) {
    $labels = ['reviewing' => 'Reviewing', 'inprogress' => 'Active', 'closed' => 'Closed', 'draft' => 'Draft'];
    return $labels[$status] ?? ucfirst((string)$status);
}

// ---- All projects of this organization + team stats per project ----
$stmt = $conn->prepare("SELECT p.id, p.title, p.category, p.members, p.status, p.deadline, p.posted_at,
                               COUNT(sp.student_project_id) AS team_size,
                               COALESCE(AVG(sp.progress), 0) AS avg_progress,
                               SUM(CASE WHEN sp.status = 'Completed' THEN 1 ELSE 0 END) AS completed_count
                        FROM projects p
                        LEFT JOIN student_projects sp ON sp.project_id = p.id
                        WHERE p.organization_email = ?
                        GROUP BY p.id, p.title, p.category, p.members, p.status, p.deadline, p.posted_at
                        ORDER BY p.posted_at DESC");
$stmt->bind_param("s", $organization_email);
$stmt->execute();
$projects = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// ---- Summary numbers ----
$totalProjects    = count($projects);
$activeProjects   = 0;
$closedProjects   = 0;
$studentsInvolved = 0;
$progressSum      = 0;
$projectsWithTeam = 0;

$statusCounts   = ['reviewing' => 0, 'inprogress' => 0, 'closed' => 0, 'draft' => 0];
$categoryCounts = [];

foreach ($projects as $p) {
    $status = $p['status'] ?: 'reviewing';
    $statusCounts[$status] = ($statusCounts[$status] ?? 0) + 1;

    if (in_array($status, ['reviewing', 'inprogress'])) $activeProjects++;
    if ($status === 'closed') $closedProjects++;

    $studentsInvolved += (int)$p['team_size'];
    if ((int)$p['team_size'] > 0) {
        $progressSum += (float)$p['avg_progress'];
        $projectsWithTeam++;
    }

    $cat = trim((string)$p['category']) !== '' ? $p['category'] : 'Uncategorized';
    $categoryCounts[$cat] = ($categoryCounts[$cat] ?? 0) + 1;
}
arsort($categoryCounts);

$avgProgress = $projectsWithTeam > 0 ? (int)round($progressSum / $projectsWithTeam) : 0;

// ---- Projects posted per month (last 6 months) ----
$monthly = [];
for ($i = 5; $i >= 0; $i--) {
    $key = date('Y-m', strtotime("first day of -$i month"));
    $monthly[$key] = 0;
}
foreach ($projects as $p) {
    $key = date('Y-m', strtotime($p['posted_at']));
    if (isset($monthly[$key])) $monthly[$key]++;
}
$maxMonthly = max(1, max($monthly));
$maxStatus  = max(1, max($statusCounts));
$maxCat     = max(1, $categoryCounts ? max($categoryCounts) : 1);

include "../../../Includes/org_sidebar.php";
include "../../../Includes/dash_header.php";

?>

<style>
    .rep-row {
        display: flex;
        gap: 14px;
        align-items: stretch;
        flex-wrap: wrap;
        padding: 0 28px;
        margin-bottom: 16px;
        box-sizing: border-box;
    }
    .rep-row > .card { margin-bottom: 0; flex: 1; min-width: 280px; }
    .rep-row > .card.wide { flex: 2; min-width: 320px; }

    .hbar-list { display: flex; flex-direction: column; gap: 14px; padding-top: 4px; }
    .hbar-item-top { display: flex; justify-content: space-between; font-size: 13px; color: #374151; margin-bottom: 6px; }
    .hbar-item-top strong { color: #111827; }
    .hbar-track { height: 8px; background: #e5e7eb; border-radius: 4px; overflow: hidden; }
    .hbar-fill { height: 100%; border-radius: 4px; background: linear-gradient(90deg, #1e40af, #3b82f6); transition: width .6s ease; }
    .hbar-fill.reviewing  { background: linear-gradient(90deg, #1d4ed8, #60a5fa); }
    .hbar-fill.inprogress { background: linear-gradient(90deg, #ea580c, #fb923c); }
    .hbar-fill.closed     { background: linear-gradient(90deg, #475569, #94a3b8); }
    .hbar-fill.draft      { background: linear-gradient(90deg, #a16207, #facc15); }

    .vbar-chart { display: flex; align-items: flex-end; justify-content: space-between; gap: 12px; height: 180px; padding: 10px 4px 0; }
    .vbar-col { flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: flex-end; height: 100%; }
    .vbar-value { font-size: 12px; font-weight: 600; color: #374151; margin-bottom: 4px; }
    .vbar { width: 100%; max-width: 42px; background: linear-gradient(180deg, #3b82f6, #1e40af); border-radius: 6px 6px 0 0; min-height: 3px; }
    .vbar-label { font-size: 12px; color: #6b7280; margin-top: 6px; }

    .rep-empty { text-align: center; color: #9ca3af; padding: 20px 0; font-size: 14px; }
    .rep-table-wrap { overflow-x: auto; }
    .progress-bar-fill.rep { background: linear-gradient(90deg, #1e40af, #3b82f6); }

    @media (max-width: 1200px) { .rep-row { padding: 0 20px; } }
    @media (max-width: 768px)  { .rep-row { padding: 0 16px; } }
</style>

<main class="content">
    <div class="dashboard-header">
        <div>
            <h1>Reports & Analytics</h1>
            <p>Track how your projects and student teams are performing.</p>
        </div>

        <a href="download_report.php" class="btn-outline">
            <span class="material-symbols-outlined">download</span>
            Export CSV
        </a>
    </div>

    <!-- ===================== SUMMARY CARDS ===================== -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-card-top">
                <div class="stat-icon blue"><span class="material-symbols-outlined">rocket_launch</span></div>
            </div>
            <div class="stat-info">
                <div class="stat-label">Total Projects</div>
                <div class="stat-value"><?= $totalProjects ?></div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-card-top">
                <div class="stat-icon orange"><span class="material-symbols-outlined">work</span></div>
            </div>
            <div class="stat-info">
                <div class="stat-label">Active Projects</div>
                <div class="stat-value"><?= $activeProjects ?></div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-card-top">
                <div class="stat-icon green"><span class="material-symbols-outlined">school</span></div>
            </div>
            <div class="stat-info">
                <div class="stat-label">Students Involved</div>
                <div class="stat-value"><?= $studentsInvolved ?></div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-card-top">
                <div class="stat-icon navy"><span class="material-symbols-outlined">trending_up</span></div>
            </div>
            <div class="stat-info">
                <div class="stat-label">Avg. Team Progress</div>
                <div class="stat-value"><?= $avgProgress ?>%</div>
            </div>
        </div>
    </div>

    <!-- ===================== CHARTS ===================== -->
    <div class="rep-row">
        <div class="card wide">
            <div class="card-header">
                <h3>Projects Posted (Last 6 Months)</h3>
            </div>
            <div class="card-body">
                <div class="vbar-chart">
                    <?php foreach ($monthly as $ym => $count): ?>
                        <div class="vbar-col">
                            <span class="vbar-value"><?= $count ?></span>
                            <div class="vbar" style="height: <?= round($count / $maxMonthly * 100) ?>%;"></div>
                            <span class="vbar-label"><?= date('M', strtotime($ym . '-01')) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3>Projects by Status</h3>
            </div>
            <div class="card-body">
                <div class="hbar-list">
                    <?php foreach ($statusCounts as $status => $count): ?>
                        <div>
                            <div class="hbar-item-top">
                                <span><?= htmlspecialchars(reportStatusLabel($status)) ?></span>
                                <strong><?= $count ?></strong>
                            </div>
                            <div class="hbar-track">
                                <div class="hbar-fill <?= htmlspecialchars($status) ?>" style="width: <?= round($count / $maxStatus * 100) ?>%;"></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="rep-row">
        <div class="card">
            <div class="card-header">
                <h3>Projects by Category</h3>
            </div>
            <div class="card-body">
                <?php if (empty($categoryCounts)): ?>
                    <p class="rep-empty">No projects posted yet.</p>
                <?php else: ?>
                    <div class="hbar-list">
                        <?php foreach (array_slice($categoryCounts, 0, 6, true) as $cat => $count): ?>
                            <div>
                                <div class="hbar-item-top">
                                    <span><?= htmlspecialchars($cat) ?></span>
                                    <strong><?= $count ?></strong>
                                </div>
                                <div class="hbar-track">
                                    <div class="hbar-fill" style="width: <?= round($count / $maxCat * 100) ?>%;"></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="card wide">
            <div class="card-header">
                <h3>Project Performance</h3>
                <a href="manage_projects.php" class="btn-link">Manage Projects</a>
            </div>
            <div class="card-body rep-table-wrap">
                <?php if (empty($projects)): ?>
                    <p class="rep-empty">No projects posted yet.</p>
                <?php else: ?>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Project</th>
                                <th>Team</th>
                                <th>Progress</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($projects as $p):
                                $pct = (int)round((float)$p['avg_progress']);
                            ?>
                            <tr>
                                <td>
                                    <div class="user-details">
                                        <div class="user-name"><?= htmlspecialchars($p['title']) ?></div>
                                        <div class="user-email"><?= htmlspecialchars($p['category'] ?: '—') ?></div>
                                    </div>
                                </td>
                                <td><?= (int)$p['team_size'] ?> / <?= (int)$p['members'] ?></td>
                                <td>
                                    <div class="progress-bar-container">
                                        <div class="progress-bar">
                                            <div class="progress-bar-fill rep" style="width:<?= $pct ?>%;"></div>
                                        </div>
                                        <span class="progress-text"><?= $pct ?>%</span>
                                    </div>
                                </td>
                                <td><span class="badge-status <?= htmlspecialchars($p['status']) ?>"><?= htmlspecialchars(reportStatusLabel($p['status'])) ?></span></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
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