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
$files = $teamRepository->files($email, (int) $id);
$messages = array_reverse($teamRepository->messages($email, (int) $id));
$taskGroups = ['Todo' => [], 'In Progress' => [], 'Review' => [], 'Done' => []];
foreach ($tasks as $task) {
    $status = strtolower(str_replace([' ', '_', '-'], '', (string) $task['status']));
    $column = in_array($status, ['done', 'completed'], true) ? 'Done' : ($status === 'review' ? 'Review' : ($status === 'inprogress' ? 'In Progress' : 'Todo'));
    $taskGroups[$column][] = $task;
}
$pageTitle = 'Team Workspace | SkillBridge';
$extra_css = '<link rel="stylesheet" href="../../Assets/CSS/Student/team_workspace.css?v=' . filemtime(__DIR__ . '/../../Assets/CSS/Student/team_workspace.css') . '">';
include __DIR__ . '/../../Includes/student_sidebar.php';
include __DIR__ . '/../../Includes/dash_header.php';
?>
<main class="content workspace-page">
    <div class="breadcrumb"><a href="teams.php">Teams</a><span>&gt;</span><strong>Workspace</strong></div>
    <section class="workspace-header">
        <div><span class="sprint"><i class="fa-solid fa-rocket"></i> <?= teamEsc(strtoupper(teamStatus((string) $team['status']))) ?></span><h1><?= teamEsc($team['project_title']) ?></h1>
            <div class="team-members"><span class="team-person-initial"><i class="fa-solid fa-users"></i></span><span><?= count($members) ?> Members</span><p><?= teamEsc($team['name']) ?></p></div>
        </div>
        <div class="progress-card"><h3>Overall Progress</h3><div class="progress-value"><?= (int) $team['progress'] ?>%</div><div class="progress-bar"><div style="width: <?= (int) $team['progress'] ?>%"></div></div><div class="progress-info"><span><?= count($taskGroups['Done']) ?> TASKS DONE</span><span><?= count($tasks) - count($taskGroups['Done']) ?> PENDING</span></div></div>
    </section>
    <section class="workspace-layout">
        <div class="workspace-main">
            <div class="kanban-header"><h2><i class="fa-solid fa-table-columns"></i> Kanban Board</h2></div>
            <div class="kanban-board">
                <?php foreach ($taskGroups as $title => $group): ?>
                    <div class="kanban-column"><h3><?= teamEsc(strtoupper($title)) ?> <span><?= count($group) ?></span></h3>
                        <?php foreach ($group as $task): ?><div class="task-card"><h4><?= teamEsc($task['title']) ?></h4><p><?= teamEsc($task['description']) ?></p><small><?= $task['due_date'] ? 'Due ' . teamEsc(teamDate($task['due_date'])) : 'No deadline' ?></small></div><?php endforeach; ?>
                    </div>
                <?php endforeach; ?>
            </div>
            <section class="shared-assets"><h2><i class="fa-regular fa-folder-open"></i> Shared Assets</h2><div class="asset-layout"><div class="asset-files"><div class="asset-header"><span>Team Files</span></div>
                <?php if ($files): foreach ($files as $file): ?><div class="asset-item"><i class="fa-regular fa-file"></i><div><strong><?= teamEsc($file['original_name']) ?></strong><p>Uploaded <?= teamEsc(teamDate($file['uploaded_at'])) ?></p></div></div><?php endforeach; else: ?><p>No shared files yet.</p><?php endif; ?>
            </div></div></section>
        </div>
        <aside class="discussion-panel"><div class="discussion-header"><h2>Discussion</h2><span><?= count($messages) ?> messages</span></div><div class="messages">
            <?php if ($messages): foreach ($messages as $message): ?><div class="message <?= $message['sender_email'] === $email ? 'mine' : 'other' ?>"><strong><?= teamEsc($message['sender_name']) ?></strong><small><?= teamEsc($message['sent_at']) ?></small><p><?= nl2br(teamEsc($message['message'])) ?></p></div><?php endforeach; else: ?><p>No team messages yet.</p><?php endif; ?>
        </div><p class="team-readonly-notice">Team messages and uploads can be managed through the Organization collaboration workflow. This Student workspace currently displays shared records only.</p></aside>
    </section>
</main>
<?php include __DIR__ . '/../../Includes/dash_footer.php'; ?>
