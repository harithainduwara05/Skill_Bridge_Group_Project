<?php
require_once __DIR__ . '/team_bootstrap.php';
$teams = $teamRepository->teams($email);
$activeTeams = array_filter($teams, static fn($team) => !in_array(strtolower((string) $team['status']), ['completed', 'complete', 'done'], true));
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
        <div class="team-stat-card"><div class="stat-icon orange"><i class="fa-solid fa-envelope"></i></div><div><h3>0</h3><p>Pending Invitations</p></div></div>
    </section>
    <section class="invitation-section">
        <h2>Team Invitations</h2>
        <div class="team-empty-state">No pending invitations. Team invitations are not recorded by the current Organization database.</div>
    </section>
    <section class="active-team-section">
        <div class="section-title"><h2>Team Workspaces</h2></div>
        <?php if ($teams): ?>
            <div class="teams-grid">
                <?php foreach ($teams as $team): ?>
                    <?php
                    $status = strtolower((string) $team['status']);
                    $badge = in_array($status, ['completed', 'complete', 'done'], true) ? 'status-complete' : (in_array($status, ['delayed', 'atrisk', 'at_risk', 'blocked'], true) ? 'status-warning' : 'status-track');
                    ?>
                    <article class="student-team-card">
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
        <?php else: ?>
            <div class="team-empty-state">You have not been assigned to an organization team yet. Once an organization adds you as a member, your team will appear here.</div>
        <?php endif; ?>
    </section>
</main>
<?php include __DIR__ . '/../../Includes/dash_footer.php'; ?>
