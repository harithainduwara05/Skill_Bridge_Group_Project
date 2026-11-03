<?php
require_once __DIR__ . '/team_bootstrap.php';

$requestedProjectId = (int) ($_GET['project_id'] ?? 0);
if ($requestedProjectId > 0) {
    $matchedTeam = $teamRepository->teamByProject($email, $requestedProjectId);
    if ($matchedTeam) {
        header('Location: view_team.php?id=' . (int) $matchedTeam['id']);
        exit;
    }
}

$teams = $teamRepository->teams($email);
$activeTeams = array_filter($teams, static fn($team) => !in_array(strtolower((string) $team['status']), ['completed', 'complete', 'done'], true));
$completedTeams = array_filter($teams, static fn($team) => in_array(strtolower((string) $team['status']), ['completed', 'complete', 'done'], true));

$pageTitle = 'My Teams | SkillBridge';
$extra_css = '<link rel="stylesheet" href="../../Assets/CSS/Student/teams.css?v=' . filemtime(__DIR__ . '/../../Assets/CSS/Student/teams.css') . '">';
include __DIR__ . '/../../Includes/student_sidebar.php';
include __DIR__ . '/../../Includes/dash_header.php';
?>
<main class="content teams-page">
    <section class="teams-header">
        <div>
            <h1>My Teams</h1>
            <p>Collaborate with peers and manage your projects.</p>
        </div>
        <div class="team-actions-top">
            <a class="team-secondary-btn" href="projects.php"><i class="fa-solid fa-compass"></i> Discover Projects</a>
        </div>
    </section>

    <section class="team-stat-grid">
        <div class="team-stat-card"><div class="stat-icon"><i class="fa-solid fa-users"></i></div><div><h3><?= count($teams) ?></h3><p>All Teams</p></div></div>
        <div class="team-stat-card"><div class="stat-icon green"><i class="fa-solid fa-user-group"></i></div><div><h3><?= count($activeTeams) ?></h3><p>Active Teams</p></div></div>
        <div class="team-stat-card"><div class="stat-icon orange"><i class="fa-solid fa-circle-check"></i></div><div><h3><?= count($completedTeams) ?></h3><p>Completed Teams</p></div></div>
    </section>

    <section class="invitation-section">
        <h2>Team Invitations</h2>
        <div class="team-empty-state">No pending invitations. Team invitations are not recorded by the current Organization database.</div>
    </section>

    <section class="active-team-section">
        <div class="section-title tm-head-bar">
            <h2>Team Workspaces</h2>

            <!-- Organization-style Filter Dropdown (All, On Track, Behind, Completed + Search + Sort) -->
            <div class="tm-filter-wrap">
                <button type="button" class="tm-btn" id="tmFilterBtn" aria-expanded="false">
                    <i class="fa-solid fa-sliders"></i> Filter
                    <span class="tm-filter-count" id="tmFilterCount" hidden>0</span>
                </button>

                <div class="tm-filter-panel" id="tmFilterPanel" hidden>
                    <div class="tm-filter-row">
                        <label class="tm-f-label" for="tmFilterSearch">Search</label>
                        <input type="text" id="tmFilterSearch" class="tm-input" placeholder="Team, project, leader or skill">
                    </div>
                    <div class="tm-filter-row">
                        <label class="tm-f-label">Status</label>
                        <div class="tm-seg" id="tmFilterStatus">
                            <button type="button" data-value="all" class="on">All</button>
                            <button type="button" data-value="ontrack">On Track</button>
                            <button type="button" data-value="behind">Behind</button>
                            <button type="button" data-value="completed">Completed</button>
                        </div>
                    </div>
                    <div class="tm-filter-row">
                        <label class="tm-f-label" for="tmFilterSort">Sort by</label>
                        <select id="tmFilterSort" class="tm-input">
                            <option value="newest">Newest first</option>
                            <option value="deadline">Deadline (soonest first)</option>
                            <option value="progress_low">Progress (lowest first)</option>
                            <option value="progress_high">Progress (highest first)</option>
                        </select>
                    </div>
                    <div class="tm-filter-foot">
                        <button type="button" class="tm-link" id="tmFilterClear">Clear filters</button>
                        <span class="tm-filter-result" id="tmFilterResult"></span>
                    </div>
                </div>
            </div>
        </div>

        <?php if ($teams): ?>
            <div class="teams-grid" id="studentTeamsGrid">
                <?php $tmOrder = 0; foreach ($teams as $team): ?>
                    <?php
                    $rawStatus = strtolower((string) $team['status']);
                    if (in_array($rawStatus, ['completed', 'complete', 'done'], true)) {
                        $stateKey = 'completed';
                        $badge = 'status-complete';
                    } elseif (in_array($rawStatus, ['delayed', 'atrisk', 'at_risk', 'blocked', 'behind'], true)) {
                        $stateKey = 'behind';
                        $badge = 'status-warning';
                    } else {
                        $stateKey = 'ontrack';
                        $badge = 'status-track';
                    }

                    $searchText = strtolower(
                        ($team['name'] ?? '') . ' ' .
                        ($team['project_title'] ?? '') . ' ' .
                        ($team['leader_name'] ?? '') . ' ' .
                        ($team['organization_name'] ?? '') . ' ' .
                        implode(' ', $team['technologies'] ?? [])
                    );
                    $deadlineTs = !empty($team['deadline']) ? (int) strtotime((string) $team['deadline']) : 0;
                    ?>
                    <article class="student-team-card"
                             data-state="<?= teamEsc($stateKey) ?>"
                             data-search="<?= teamEsc($searchText) ?>"
                             data-order="<?= $tmOrder++ ?>"
                             data-deadline="<?= $deadlineTs ?>"
                             data-progress="<?= (int) $team['progress'] ?>">
                        <div class="card-top">
                            <div class="team-logo"><?= teamEsc(teamInitials((string) $team['name'])) ?></div>
                            <span class="<?= $badge ?>"><?= teamEsc(strtoupper(teamStatus((string) $team['status']))) ?></span>
                        </div>
                        <h3><?= teamEsc($team['name']) ?></h3>
                        <p class="project-name"><?= teamEsc($team['project_title']) ?></p>
                        <div class="leader">
                            <span class="team-person-initial"><?= teamEsc(teamInitials((string) $team['leader_name'])) ?></span>
                            <div><strong><?= teamEsc($team['leader_name']) ?></strong><p>Team Leader</p></div>
                        </div>
                        <div class="members"><div><span class="team-member-count-icon"><i class="fa-solid fa-users"></i></span></div><p><?= (int) $team['member_count'] ?> Members</p></div>
                        <div class="skills">
                            <?php foreach (array_slice($team['technologies'], 0, 5) as $skill): ?><span><?= teamEsc($skill) ?></span><?php endforeach; ?>
                        </div>
                        <div class="progress-area">
                            <div class="progress-label"><span>Project Progress</span><strong><?= (int) $team['progress'] ?>%</strong></div>
                            <div class="progress-bar"><div style="width: <?= (int) $team['progress'] ?>%"></div></div>
                        </div>
                        <div class="deadline"><i class="fa-regular fa-calendar"></i> Deadline: <strong><?= teamEsc(teamDate($team['deadline'])) ?></strong></div>
                        <div class="card-buttons">
                            <a href="view_team.php?id=<?= (int) $team['id'] ?>">View Team</a>
                            <a href="team_workspace.php?id=<?= (int) $team['id'] ?>">Workspace</a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>

            <div class="team-empty-state" id="tmEmpty" hidden style="text-align:center;">
                <p style="margin:0 0 12px;font-weight:700;color:#082544;">No teams match your current filters.</p>
                <button type="button" class="tm-btn" id="tmEmptyClear">Clear filters</button>
            </div>
        <?php else: ?>
            <div class="team-empty-state">You have not been assigned to an organization team yet. Once an organization adds you as a member, your team will appear here.</div>
        <?php endif; ?>
    </section>
</main>
<script src="../../Assets/JS/Student/teams.js?v=<?= filemtime(__DIR__ . '/../../Assets/JS/Student/teams.js') ?>"></script>
<?php include __DIR__ . '/../../Includes/dash_footer.php'; ?>