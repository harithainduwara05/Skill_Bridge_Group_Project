<?php
require_once __DIR__ . '/team_bootstrap.php';
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$team = $id ? $teamRepository->team($email, $id) : null;
if (!$team) {
    http_response_code(404);
    exit('Team not found or you are not a member.');
}
$members = $teamRepository->members($email, (int) $id);
$tasks = $teamRepository->tasks($email, (int) $id);
$pageTitle = 'View Team | SkillBridge';
$extra_css = '<link rel="stylesheet" href="../../Assets/CSS/Student/view_team.css?v=' . filemtime(__DIR__ . '/../../Assets/CSS/Student/view_team.css') . '">';
include __DIR__ . '/../../Includes/student_sidebar.php';
include __DIR__ . '/../../Includes/dash_header.php';
?>
<main class="content view-team-page">
    <div class="breadcrumb"><a href="teams.php">Teams</a><span>&gt;</span><strong>View Team</strong></div>
    <section class="team-banner">
        <div class="team-banner-left">
            <div class="large-team-logo"><?= teamEsc(teamInitials((string) $team['name'])) ?></div>
            <div>
                <span class="team-status"><?= teamEsc(strtoupper(teamStatus((string) $team['status']))) ?></span>
                <h1><?= teamEsc($team['name']) ?></h1>
                <p><?= teamEsc($team['project_title']) ?></p>
                <div class="team-meta">
                    <span><i class="fa-regular fa-calendar"></i> Deadline: <strong><?= teamEsc(teamDate($team['deadline'])) ?></strong></span>
                    <span><i class="fa-solid fa-user"></i> Leader: <strong><?= teamEsc($team['leader_name']) ?></strong></span>
                </div>
            </div>
        </div>
        <div class="team-progress-card">
            <h3>Project Progress</h3><strong><?= (int) $team['progress'] ?>%</strong>
            <div class="progress-track"><div style="width: <?= (int) $team['progress'] ?>%"></div></div>
            <p><?= teamEsc(teamStatus((string) $team['status'])) ?></p>
        </div>
    </section>
    <nav class="team-tabs"><a class="active" href="#overview">Overview</a><a href="#tasks">Tasks</a><a href="#members">Members</a><a href="team_workspace.php?id=<?= (int) $id ?>">Files &amp; Discussion</a></nav>
    <section class="team-layout" id="overview">
        <div class="team-main">
            <div class="detail-card"><h2>About Project</h2><p><?= nl2br(teamEsc($team['project_description'])) ?></p></div>
            <div class="detail-card"><h2>Technology Stack</h2><div class="skill-list"><?php foreach ($team['technologies'] as $technology): ?><span><?= teamEsc($technology) ?></span><?php endforeach; ?></div></div>
            <div class="detail-card" id="tasks">
                <div class="card-title-row"><h2>Current Tasks</h2><a href="team_workspace.php?id=<?= (int) $id ?>">Open Workspace</a></div>
                <div class="task-preview">
                    <?php if ($tasks): foreach (array_slice($tasks, 0, 3) as $task): ?>
                        <div><span><?= teamEsc(strtoupper((string) $task['status'])) ?></span><h4><?= teamEsc($task['title']) ?></h4></div>
                    <?php endforeach; else: ?><p>No tasks are available for this team.</p><?php endif; ?>
                </div>
            </div>
        </div>
        <aside class="team-sidebar" id="members">
            <div class="detail-card"><h2>Team Members</h2>
                <?php foreach ($members as $member): ?>
                    <div class="member-item"><span class="team-person-initial"><?= teamEsc(teamInitials((string) $member['name'])) ?></span><div><strong><?= teamEsc($member['name']) ?></strong><p><?= teamEsc($member['role'] ?: ((string) $member['Email'] === (string) $team['leader_email'] ? 'Team Leader' : 'Member')) ?></p></div></div>
                <?php endforeach; ?>
            </div>
            <div class="detail-card activity-card"><h2>Organization</h2><p><?= teamEsc($team['organization_name']) ?></p><p>Your role: <?= teamEsc($team['member_role'] ?: 'Member') ?></p></div>
        </aside>
    </section>
</main>
<?php include __DIR__ . '/../../Includes/dash_footer.php'; ?>
