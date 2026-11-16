<?php
require_once __DIR__ . '/team_bootstrap.php';

// Handle all AJAX workspace actions (load, send_message, add_task, move_task, delete_task, upload_file, delete_file, submit_report)
twHandleRequest($conn, $user);

$id = (int) ($_GET['id'] ?? $_GET['team_id'] ?? 0);
$team = $id > 0 ? $teamRepository->team($email, $id) : null;
if (!$team) {
    http_response_code(404);
    exit('Team not found or you are not a member.');
}
$members = $teamRepository->members($email, (int) $id);
$tasks = twTasks($conn, (int) $id);
$files = twFiles($conn, (int) $id, $email, !empty($team['is_leader']));
$messages = twMessages($conn, (int) $id, 0, $email);
$report = twReport($conn, (int) $id);

$isCompleted = strtolower((string) $team['status']) === 'completed';

$taskGroups = [
    'todo' => ['title' => 'Todo', 'items' => []],
    'progress' => ['title' => 'In Progress', 'items' => []],
    'review' => ['title' => 'Review', 'items' => []],
    'done' => ['title' => 'Done', 'items' => []],
];
foreach ($tasks as $task) {
    $col = $task['col'] ?? 'todo';
    if (!isset($taskGroups[$col])) $col = 'todo';
    $taskGroups[$col]['items'][] = $task;
}

$doneCount = count($taskGroups['done']['items']);
$totalTasks = count($tasks);
$pct = $totalTasks > 0 ? (int) round(($doneCount / $totalTasks) * 100) : (int) $team['progress'];
if ($isCompleted) {
    $pct = 100;
}

$pageTitle = 'Team Workspace | SkillBridge';
$extra_css = '<link rel="stylesheet" href="../../Assets/CSS/Student/team_workspace.css?v=' . filemtime(__DIR__ . '/../../Assets/CSS/Student/team_workspace.css') . '">';
include __DIR__ . '/../../Includes/student_sidebar.php';
include __DIR__ . '/../../Includes/dash_header.php';
?>
<main class="content workspace-page"
      data-team-id="<?= (int) $id ?>"
      data-student-email="<?= teamEsc(strtolower($email)) ?>"
      data-is-leader="<?= !empty($team['is_leader']) ? '1' : '0' ?>"
      data-team-status="<?= teamEsc(strtolower((string) $team['status'])) ?>"
      data-team-deadline="<?= teamEsc((string) ($team['deadline'] ?? '')) ?>">
    <div class="breadcrumb"><a href="teams.php">Teams</a><span>&gt;</span><a href="view_team.php?id=<?= (int) $id ?>"><?= teamEsc($team['name']) ?></a><span>&gt;</span><strong>Workspace</strong></div>
    <section class="workspace-header">
        <div>
            <span class="sprint" id="wsTeamStatusBadge"><i class="fa-solid fa-rocket"></i> <?= teamEsc(strtoupper(teamStatus((string) $team['status']))) ?></span>
            <h1><?= teamEsc($team['project_title']) ?></h1>
            <div class="team-members">
                <span class="team-person-initial"><i class="fa-solid fa-users"></i></span>
                <span><?= count($members) ?> Members</span>
                <p><?= teamEsc($team['name']) ?> <?= !empty($team['is_leader']) ? '(You are Team Leader)' : '' ?></p>
            </div>
        </div>
        <div class="progress-card">
            <h3>Overall Progress</h3>
            <div class="progress-value" id="wsProgressPct"><?= $pct ?>%</div>
            <div class="progress-bar"><div id="wsProgressBar" style="width: <?= $pct ?>%"></div></div>
            <div class="progress-info">
                <span id="wsDoneCount"><?= $doneCount ?> TASKS DONE</span>
                <span id="wsPendingCount"><?= max(0, $totalTasks - $doneCount) ?> PENDING</span>
            </div>
        </div>
    </section>

    <?php if ($isCompleted): ?>
        <div style="margin-bottom:20px;padding:16px 20px;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:12px;display:flex;align-items:center;gap:12px;color:#166534;">
            <i class="fa-solid fa-circle-check" style="font-size:22px;color:#22c55e;"></i>
            <div>
                <strong>Project Completed &amp; Final Report Accepted!</strong>
                <p style="margin:2px 0 0;font-size:13px;color:#15803d;">This project team workspace is in view-only mode. All tasks, discussions, file sharing, and final report submissions are securely locked.</p>
            </div>
        </div>
    <?php endif; ?>

    <section class="workspace-layout">
        <div class="workspace-main">
            <!-- KANBAN BOARD -->
            <div class="kanban-header">
                <h2><i class="fa-solid fa-table-columns"></i> Kanban Board</h2>
                <div class="kanban-controls">
                    <?php if (!$isCompleted): ?>
                        <button type="button" class="workspace-new-task-btn" id="openNewTaskBtn"><i class="fa-solid fa-plus"></i> New Task</button>
                    <?php endif; ?>
                </div>
            </div>
            <div class="kanban-board" id="wsKanbanBoard">
                <?php foreach ($taskGroups as $colKey => $group): ?>
                    <div class="kanban-column" data-col="<?= teamEsc($colKey) ?>">
                        <h3><?= teamEsc(strtoupper($group['title'])) ?> <span id="col-count-<?= teamEsc($colKey) ?>"><?= count($group['items']) ?></span></h3>
                        <div class="kanban-col-body" id="col-body-<?= teamEsc($colKey) ?>">
                            <?php foreach ($group['items'] as $task): ?>
                                <div class="task-card" data-task-id="<?= (int) $task['id'] ?>">
                                    <h4><?= teamEsc($task['title']) ?></h4>
                                    <?php if (!empty($task['description'])): ?><p><?= teamEsc($task['description']) ?></p><?php endif; ?>
                                    <div class="task-card-meta">
                                        <small><i class="fa-regular fa-user"></i> <?= teamEsc($task['who'] ?: 'Unassigned') ?></small>
                                        <small><i class="fa-regular fa-calendar"></i> <?= !empty($task['due']) ? 'Due ' . teamEsc(teamDate($task['due'])) : 'No deadline' ?></small>
                                    </div>
                                    <?php if (!$isCompleted): ?>
                                        <div class="task-card-actions">
                                            <?php if ($colKey === 'todo'): ?>
                                                <button type="button" class="task-act-btn primary" data-move-task="<?= (int) $task['id'] ?>" data-target-col="progress">Start</button>
                                            <?php elseif ($colKey === 'progress'): ?>
                                                <button type="button" class="task-act-btn" data-move-task="<?= (int) $task['id'] ?>" data-target-col="todo">To Do</button>
                                                <button type="button" class="task-act-btn primary" data-review-task="<?= (int) $task['id'] ?>" data-task-title="<?= teamEsc($task['title']) ?>">Send for Review</button>
                                            <?php elseif ($colKey === 'review'): ?>
                                                <button type="button" class="task-act-btn" data-move-task="<?= (int) $task['id'] ?>" data-target-col="progress">Take Back</button>
                                            <?php endif; ?>
                                            <?php if (!empty($team['is_leader'])): ?>
                                                <button type="button" class="task-act-btn danger" data-delete-task="<?= (int) $task['id'] ?>" title="Delete Task"><i class="fa-solid fa-trash"></i></button>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- SHARED ASSETS (DOCUMENTS) -->
            <section class="shared-assets">
                <h2><i class="fa-regular fa-folder-open"></i> Shared Assets</h2>
                <div class="asset-layout">
                    <div class="asset-files" style="<?= $isCompleted ? 'width:100%;' : '' ?>">
                        <div class="asset-header">
                            <span>Team Files</span>
                            <span id="wsFilesCount"><?= count($files) ?> file(s)</span>
                        </div>
                        <div id="wsFilesList">
                            <?php if ($files): foreach ($files as $file): ?>
                                <div class="asset-item" data-file-id="<?= (int) $file['id'] ?>">
                                    <i class="fa-regular fa-file"></i>
                                    <div>
                                        <strong><a href="../../<?= teamEsc($file['path']) ?>" target="_blank" rel="noopener" style="color:#082544;text-decoration:none;"><?= teamEsc($file['name']) ?></a></strong>
                                        <p>Shared by <?= teamEsc($file['by']) ?> &bull; <?= teamEsc($file['when']) ?></p>
                                    </div>
                                    <div class="asset-item-actions" style="margin-left:auto;display:flex;gap:8px;">
                                        <a href="../../<?= teamEsc($file['path']) ?>" download class="task-act-btn" title="Download"><i class="fa-solid fa-download"></i></a>
                                    </div>
                                </div>
                            <?php endforeach; else: ?>
                                <p style="padding:15px;color:#667085;">No shared files yet.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php if (!$isCompleted): ?>
                        <label class="upload-box" id="wsUploadBox" style="cursor:pointer;padding:20px;">
                            <input type="file" id="wsFileInput" multiple hidden accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.txt,.csv,.png,.jpg,.jpeg,.gif,.webp,.zip,.mp4,.fig">
                            <i class="fa-solid fa-cloud-arrow-up"></i>
                            <h4>Upload Shared Files</h4>
                            <p>Click or drop files here (Max 10MB)</p>
                        </label>
                    <?php endif; ?>
                </div>
            </section>

            <!-- FINAL REPORT SUBMISSION SECTION -->
            <section class="final-report-section" style="margin-top:25px;">
                <h2><i class="fa-solid fa-file-signature"></i> Final Report Submission</h2>
                <div class="final-report-box">
                    
                    <?php if ($report): ?>
                        <div style="background:#f4fbf7; border:1px solid #d1fae5; border-radius:14px; padding:22px; box-shadow:0 2px 4px rgba(0,0,0,0.01);">
                            <div style="display:flex; align-items:center; justify-content:space-between; gap:16px; flex-wrap:wrap;">
                                <div style="display:flex; align-items:center; gap:14px;">
                                    <div style="width:42px; height:42px; border-radius:10px; background:#dcfce7; color:#15803d; display:flex; align-items:center; justify-content:center; font-size:18px; flex-shrink:0;">
                                        <i class="fa-solid fa-circle-check"></i>
                                    </div>
                                    <div>
                                        <h3 style="margin:0 0 3px; font-size:16px; color:#0f2a4a; font-weight:750;">Final Completed Report</h3>
                                        <p style="margin:0; color:#475569; font-size:13px;">
                                            <strong style="color:#0f2a4a;"><?= teamEsc($report['name']) ?></strong> &bull; Submitted on <?= teamEsc($report['submitted']) ?>
                                        </p>
                                    </div>
                                </div>
                                <a href="../../<?= teamEsc($report['path']) ?>" target="_blank" download style="display:inline-flex; align-items:center; gap:8px; background:#0f766e; color:#ffffff; padding:10px 18px; border-radius:8px; font-weight:600; font-size:13px; text-decoration:none; transition:background 0.2s;">
                                    <i class="fa-solid fa-download"></i> Download Final Report
                                </a>
                            </div>
                            <p style="margin:14px 0 0; color:#334155; font-size:13.5px; line-height:1.6; background:#ffffff; padding:14px; border-radius:10px; border:1px solid #e2e8f0;"><?= nl2br(teamEsc($report['summary'])) ?></p>
                            <?php if (!empty($report['note'])): ?>
                                <div style="margin-top:10px;padding:10px 12px;background:#fff7ed;border-left:3px solid #f97316;border-radius:6px;font-size:13px;color:#9a3412;">
                                    <strong>Organization Feedback:</strong> <?= nl2br(teamEsc($report['note'])) ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($isCompleted): ?>
                        <p class="info-text" style="margin-top:16px; margin-bottom:0;"><i class="fa-solid fa-circle-check"></i> This project has been completed and accepted. Report submissions are locked.</p>
                    <?php elseif (!empty($team['is_leader']) && ($pct === 100) && (!$report || $report['status'] === 'changes_requested' || $report['status'] === 'draft')): ?>
                        
                        <p class="info-text">
                            When all project tasks are approved and marked as <strong>Done</strong>, the Team Leader can upload the final project report (PDF, DOC, DOCX, or ZIP) for organization review.
                        </p>
                        <hr style="border:0;border-top:1px solid #e5e7eb;margin-bottom:20px;">

                        <form id="wsReportForm">
                            <label>Final Report Document (.pdf, .doc, .docx, .zip - Max 10MB) *</label>
                            <input type="file" id="wsReportFile" name="report" accept=".pdf,.doc,.docx,.zip" required>

                            <label>Project Summary (20 - 2000 characters) *</label>
                            <textarea id="wsReportSummary" name="summary" minlength="20" maxlength="2000" required placeholder="Summarize your team's final deliverables, repository links, and key outcomes..."><?= teamEsc($report['summary'] ?? '') ?></textarea>

                            <button type="submit" class="submit-report-btn" id="wsReportSubmitBtn">
                                <i class="fa-solid fa-paper-plane"></i> <?= $report ? 'Resubmit Final Report' : 'Submit Final Report' ?>
                            </button>
                        </form>

                    <?php elseif (empty($team['is_leader']) && (!$report || $report['status'] !== 'accepted')): ?>
                        <p class="info-text" style="margin-bottom:0;"><i class="fa-solid fa-circle-info"></i> Only the Team Leader (<strong><?= teamEsc($team['leader_name']) ?></strong>) can submit the final report once all tasks are Done.</p>
                    
                    <?php elseif (!empty($team['is_leader']) && $pct < 100 && !$report): ?>
                        <p class="info-text" style="margin-bottom:0;"><i class="fa-solid fa-circle-info"></i> When all project tasks are approved and marked as <strong>Done</strong>, the Team Leader can upload the final project report.</p>
                        <div style="margin-top:16px;padding:14px;background:#fff8f1;border:1px dashed #ff8a00;border-radius:8px;color:#b45309;font-size:13px;">
                            <i class="fa-solid fa-circle-exclamation"></i> <strong>Tasks Pending:</strong> Move all your tasks to <strong>Done</strong> (Currently <?= $pct ?>%) before submitting the Final Report.
                        </div>
                    <?php endif; ?>
                </div>
            </section>
        </div>

        <!-- DISCUSSION / TEAM CHAT PANEL -->
        <aside class="discussion-panel">
            <div class="discussion-header">
                <h2>Discussion</h2>
                <span id="wsChatCount"><?= count($messages) ?> messages</span>
            </div>
            <div class="messages" id="wsChatMessages">
                <?php if ($messages): foreach ($messages as $message): ?>
                    <div class="message <?= !empty($message['me']) ? 'mine' : 'other' ?>" data-msg-id="<?= (int) $message['id'] ?>">
                        <strong><?= teamEsc($message['who']) ?></strong>
                        <small><?= teamEsc($message['date'] . ' ' . $message['time']) ?></small>
                        <p><?= nl2br(teamEsc($message['text'])) ?></p>
                    </div>
                <?php endforeach; else: ?>
                    <p class="ws-empty-chat" style="color:#667085;text-align:center;margin-top:20px;">No team messages yet.</p>
                <?php endif; ?>
            </div>
            <?php if (!$isCompleted): ?>
                <form class="workspace-chat-input" id="wsChatForm">
                    <input type="text" id="wsChatInput" placeholder="Write a message to your team..." maxlength="500" autocomplete="off" required>
                    <button type="submit" class="workspace-send-btn" aria-label="Send"><i class="fa-solid fa-paper-plane"></i></button>
                </form>
            <?php else: ?>
                <div style="padding:12px;background:#f8fafc;border-top:1px solid #e2e8f0;text-align:center;font-size:12.5px;color:#64748b;">
                    <i class="fa-solid fa-lock"></i> Chat is locked because this project is completed.
                </div>
            <?php endif; ?>
        </aside>
    </section>
</main>

<!-- ADD NEW TASK MODAL -->
<div class="ws-modal-overlay" id="wsTaskModal" hidden>
    <form class="ws-modal-card" id="wsTaskForm">
        <div class="ws-modal-head">
            <h3><i class="fa-solid fa-list-check"></i> Add New Task</h3>
            <button type="button" class="ws-modal-close" data-close-modal="wsTaskModal">&times;</button>
        </div>
        <div class="ws-modal-body">
            <label>Task Title *</label>
            <input type="text" id="wsTaskTitle" maxlength="120" required placeholder="e.g. Implement user authentication">

            <label>Description</label>
            <textarea id="wsTaskDesc" rows="3" maxlength="500" placeholder="Optional details about this task..."></textarea>

            <label>Assign To</label>
            <select id="wsTaskAssignee">
                <option value="">Unassigned</option>
                <?php foreach ($members as $m): ?>
                    <option value="<?= teamEsc(strtolower((string) $m['Email'])) ?>"><?= teamEsc($m['name']) ?></option>
                <?php endforeach; ?>
            </select>

            <label>Due Date</label>
            <input type="date" id="wsTaskDue" min="<?= date('Y-m-d') ?>" <?= !empty($team['deadline']) ? 'max="' . teamEsc($team['deadline']) . '"' : '' ?>>
        </div>
        <div class="ws-modal-foot">
            <button type="button" class="workspace-filter-btn" data-close-modal="wsTaskModal">Cancel</button>
            <button type="submit" class="workspace-new-task-btn">Add Task</button>
        </div>
    </form>
</div>

<!-- SEND FOR REVIEW MODAL -->
<div class="ws-modal-overlay" id="wsReviewModal" hidden>
    <form class="ws-modal-card" id="wsReviewForm">
        <input type="hidden" id="wsReviewTaskId">
        <div class="ws-modal-head">
            <h3><i class="fa-solid fa-paper-plane"></i> Send Task for Review</h3>
            <button type="button" class="ws-modal-close" data-close-modal="wsReviewModal">&times;</button>
        </div>
        <div class="ws-modal-body">
            <p id="wsReviewTaskName" style="margin:0 0 10px;font-weight:700;color:#082544;"></p>
            <label>What did you complete? (Min 5 characters) *</label>
            <textarea id="wsReviewNote" rows="3" minlength="5" maxlength="500" required placeholder="Describe what was built or tested..."></textarea>

            <label>Reference Link (Optional - GitHub PR, Figma, Drive)</label>
            <input type="url" id="wsReviewLink" placeholder="https://github.com/...">
        </div>
        <div class="ws-modal-foot">
            <button type="button" class="workspace-filter-btn" data-close-modal="wsReviewModal">Cancel</button>
            <button type="submit" class="workspace-new-task-btn">Send for Review</button>
        </div>
    </form>
</div>

<script src="../../Assets/JS/Student/team_workspace.js?v=<?= filemtime(__DIR__ . '/../../Assets/JS/Student/team_workspace.js') ?>"></script>
<?php include __DIR__ . '/../../Includes/dash_footer.php'; ?>