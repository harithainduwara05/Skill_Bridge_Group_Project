<?php
/*
|--------------------------------------------------------------------------
| Team Workspace - Kanban tasks, team chat, files and the final report
| (shared by Organization and Student)
|--------------------------------------------------------------------------
| One place for the team's tasks and messages, so the organization and the
| students of a team see the SAME board and the SAME chat.
|
| Used by:
|   Functions/Organization/teams.php                 (organization side)
|   Functions/Dashboards/Student/team_workspace.php  (student side – teammate)
|
| How to use it on a page (before any HTML is printed):
|     require_once __DIR__ . '/../../Backend/TeamWorkspace.php';   // path from the page
|     twHandleRequest($conn, current_user());
| The page's JavaScript then sends   fetch(<same page>, POST ws_action=...)
| and gets JSON back. Actions:
|     load          team_id, after_id     -> members, tasks, files, new messages, final report, team status
|     send_message  team_id, message
|     add_task      team_id, title, description, col, assignee (email or ''), due (Y-m-d or '')
|     move_task     task_id, col
|     delete_task   task_id
|     upload_file   team_id, files[]  (multipart form, max 10MB each)
|     delete_file   file_id
|     submit_report team_id, summary, report (one file: pdf / doc / docx / zip, max 10MB)
|                   only the team leader, only when every task is Done
|
| Who can use a team:
|   - the organization that owns the team's project (projects.organization_email)
|   - a student who is a member of the team         (org_team_members)
|
| Tables (skillbridge_db):
|   team_tasks    (task_id, team_id, assigned_to, title, description, due_date, status, created_at)
|                 status: 'Todo' | 'In Progress' | 'Review' | 'Done'
|   team_messages (message_id, team_id, sender_email, message, sent_at)
|   team_files    (file_id, team_id, uploaded_by, original_name, file_path, file_size, mime_type, uploaded_at)
|                 the files themselves are saved in Assets/Uploads/team_files/
|   team_final_reports (report_id, team_id, submitted_by, original_name, file_path, summary,
|                       status, review_note, submitted_at, reviewed_at)   one row per team
|                 status: 'submitted' -> the organization 'accepted' it  (team Completed)
|                                     -> or asked for 'changes_requested' (the leader sends it again)
|                 the files are saved in Assets/Uploads/final_reports/
|   org_teams.status  'ontrack' | 'behind' | 'completed'  - set by twRefreshStatus(), never by hand
*/

// Kanban column (used by the page)  <->  team_tasks.status (saved in the database)
const TW_STATUS = ['todo' => 'Todo', 'progress' => 'In Progress', 'review' => 'Review', 'done' => 'Done'];

// Shared team files: allowed types + size limit
const TW_FILE_MAX = 10 * 1024 * 1024;   // 10MB per file
const TW_FILE_EXT = ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx', 'txt', 'csv', 'png', 'jpg', 'jpeg', 'gif', 'webp', 'zip', 'mp4', 'fig'];

// Final report: one file + a short summary
const TW_REPORT_EXT = ['pdf', 'doc', 'docx', 'zip'];
const TW_SUMMARY_MIN = 20;
const TW_SUMMARY_MAX = 2000;

/*
| Team status (org_teams.status) is worked out from the other tables, in this order:
|   completed -> the organization accepted the final report
|   ontrack   -> the final report was sent and is waiting for the organization
|   behind    -> the team deadline has passed, or a task that is not Done is past its due date
|   ontrack   -> everything else
| "t" is org_teams.
*/
const TW_STATUS_SQL = "CASE
        WHEN EXISTS (SELECT 1 FROM team_final_reports r WHERE r.team_id = t.id AND r.status = 'accepted')  THEN 'completed'
        WHEN EXISTS (SELECT 1 FROM team_final_reports r WHERE r.team_id = t.id AND r.status = 'submitted') THEN 'ontrack'
        WHEN t.deadline < CURDATE()
          OR EXISTS (SELECT 1 FROM team_tasks k WHERE k.team_id = t.id AND k.status <> 'Done' AND k.due_date < CURDATE())
                                                                                                          THEN 'behind'
        ELSE 'ontrack'
    END";

/** Save the correct status of one team (after tasks, the deadline or the final report change) */
function twRefreshStatus(mysqli $conn, int $teamId): void
{
    $q = $conn->prepare("UPDATE org_teams t SET t.status = " . TW_STATUS_SQL . " WHERE t.id = ?");
    $q->bind_param("i", $teamId);
    $q->execute();
}

/** The team's final report (or null if it was not sent yet). path = "Assets/Uploads/final_reports/..." */
function twReport(mysqli $conn, int $teamId): ?array
{
    $q = $conn->prepare("SELECT r.report_id, r.original_name, r.file_path, r.summary, r.status, r.review_note,
                                r.submitted_at, r.reviewed_at, COALESCE(s.Name, r.submitted_by) AS submitted_by_name
                         FROM team_final_reports r LEFT JOIN student s ON s.Email = r.submitted_by
                         WHERE r.team_id = ?");
    $q->bind_param("i", $teamId);
    $q->execute();
    $r = $q->get_result()->fetch_assoc();
    if (!$r) return null;
    return [
        'id' => (int)$r['report_id'], 'name' => $r['original_name'] ?: basename($r['file_path']), 'path' => $r['file_path'],
        'summary' => $r['summary'], 'status' => $r['status'], 'note' => $r['review_note'] ?? '',
        'by' => $r['submitted_by_name'], 'submitted' => date('M d, Y', strtotime($r['submitted_at'])),
        'reviewed' => !empty($r['reviewed_at']) ? date('M d, Y', strtotime($r['reviewed_at'])) : '',
    ];
}

/**
 * The team row if this user may open it, otherwise null.
 * Organization -> must own the team's project.  Student -> must be a member of the team.
 */
function twTeamFor(mysqli $conn, int $teamId, string $email, string $role): ?array
{
    if ($role === 'organization') {
        $q = $conn->prepare("SELECT t.id, t.name, t.project_id, t.leader_email, t.deadline, t.status, p.organization_email, p.title AS project
                             FROM org_teams t JOIN projects p ON p.id = t.project_id
                             WHERE t.id = ? AND p.organization_email = ?");
    } elseif ($role === 'student') {
        $q = $conn->prepare("SELECT t.id, t.name, t.project_id, t.leader_email, t.deadline, t.status, p.organization_email, p.title AS project
                             FROM org_teams t
                             JOIN projects p ON p.id = t.project_id
                             JOIN org_team_members m ON m.team_id = t.id
                             WHERE t.id = ? AND LOWER(m.Email) = LOWER(?)");
    } else {
        return null;
    }
    $q->bind_param("is", $teamId, $email);
    $q->execute();
    return $q->get_result()->fetch_assoc() ?: null;
}

/** Team members: [ ['email' => .., 'name' => .., 'role' => .., 'leader' => bool], ... ] */
function twMembers(mysqli $conn, array $team): array
{
    $q = $conn->prepare("SELECT LOWER(m.Email) AS email, s.Name AS name, m.role
                         FROM org_team_members m JOIN student s ON LOWER(s.Email) = LOWER(m.Email)
                         WHERE m.team_id = ? ORDER BY s.Name");
    $id = (int)$team['id'];
    $q->bind_param("i", $id);
    $q->execute();
    $out = [];
    foreach ($q->get_result()->fetch_all(MYSQLI_ASSOC) as $m) {
        $m['leader'] = $m['email'] === strtolower($team['leader_email']);
        $out[] = $m;
    }
    return $out;
}

/** All tasks of a team, with the assignee's name. col = Kanban column (todo / progress / review / done) */
function twTasks(mysqli $conn, int $teamId): array
{
    $q = $conn->prepare("SELECT t.task_id, t.title, t.description, t.status, LOWER(t.assigned_to) AS assignee,
                                s.Name AS assignee_name, t.due_date
                         FROM team_tasks t LEFT JOIN student s ON LOWER(s.Email) = LOWER(t.assigned_to)
                         WHERE t.team_id = ? ORDER BY t.task_id");
    $q->bind_param("i", $teamId);
    $q->execute();
    $out = [];
    foreach ($q->get_result()->fetch_all(MYSQLI_ASSOC) as $t) {
        $col = array_search($t['status'], TW_STATUS, true) ?: 'todo';
        $out[] = [
            'id' => (int)$t['task_id'], 'title' => $t['title'], 'description' => $t['description'] ?? '',
            'col' => $col, 'assignee' => $t['assignee'] ?? '', 'who' => $t['assignee_name'] ?? '',
            'due' => $t['due_date'] ?? '',
            'progress' => ['todo' => 0, 'progress' => 50, 'review' => 100, 'done' => 100][$col],
        ];
    }
    return $out;
}

/** Messages of a team newer than $afterId (0 = all). The sender's name and role come from their account. */
function twMessages(mysqli $conn, int $teamId, int $afterId, string $myEmail): array
{
    $q = $conn->prepare("SELECT m.message_id, m.sender_email, m.message, m.sent_at, u.role,
                                COALESCE(s.Name, o.Name, m.sender_email) AS sender_name
                         FROM team_messages m
                         LEFT JOIN user u         ON u.Email = m.sender_email
                         LEFT JOIN student s      ON s.Email = m.sender_email
                         LEFT JOIN organization o ON o.Email = m.sender_email
                         WHERE m.team_id = ? AND m.message_id > ?
                         ORDER BY m.message_id LIMIT 500");
    $q->bind_param("ii", $teamId, $afterId);
    $q->execute();
    $out = [];
    foreach ($q->get_result()->fetch_all(MYSQLI_ASSOC) as $m) {
        $ts = strtotime($m['sent_at']);
        $out[] = [
            'id' => (int)$m['message_id'], 'who' => $m['sender_name'], 'role' => $m['role'] ?? '',
            'me' => strtolower($m['sender_email']) === strtolower($myEmail),
            'text' => $m['message'],
            'time' => date('H:i', $ts), 'date' => date('M d, Y', $ts),
        ];
    }
    return $out;
}

/**
 * Files shared in a team. path = "Assets/Uploads/team_files/..." (the page adds "../../" in front)
 * can_delete: the person who uploaded it, the organization or the team leader
 */
function twFiles(mysqli $conn, int $teamId, string $myEmail, bool $canDeleteAll): array
{
    $q = $conn->prepare("SELECT f.file_id, f.original_name, f.file_path, f.file_size, f.uploaded_by, f.uploaded_at,
                                COALESCE(s.Name, o.Name, f.uploaded_by) AS uploader
                         FROM team_files f
                         LEFT JOIN student s      ON s.Email = f.uploaded_by
                         LEFT JOIN organization o ON o.Email = f.uploaded_by
                         WHERE f.team_id = ? ORDER BY f.file_id DESC");
    $q->bind_param("i", $teamId);
    $q->execute();
    $out = [];
    foreach ($q->get_result()->fetch_all(MYSQLI_ASSOC) as $f) {
        $mine = strtolower($f['uploaded_by']) === strtolower($myEmail);
        $out[] = [
            'id' => (int)$f['file_id'], 'name' => $f['original_name'], 'path' => $f['file_path'],
            'size' => (int)$f['file_size'], 'by' => $mine ? 'You' : $f['uploader'],
            'when' => date('M d, Y', strtotime($f['uploaded_at'])),
            'can_delete' => $mine || $canDeleteAll,
        ];
    }
    return $out;
}

/** Save a chat message (also used for automatic messages, e.g. deadline extended) */
function twAddMessage(mysqli $conn, int $teamId, string $email, string $text): void
{
    $q = $conn->prepare("INSERT INTO team_messages (team_id, sender_email, message) VALUES (?, ?, ?)");
    $q->bind_param("iss", $teamId, $email, $text);
    $q->execute();
}

function twJson(array $data): void
{
    header('Content-Type: application/json');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Answers the workspace requests (POST with ws_action). Does nothing for other requests.
 * $user = current_user()  (needs 'email', 'role' and 'username')
 */
function twHandleRequest(mysqli $conn, ?array $user): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['ws_action'])) return;
    if (!$user) twJson(['ok' => false, 'message' => 'Please log in again.']);

    $email  = strtolower($user['email'] ?? '');
    $role   = $user['role'] ?? '';
    $name   = $user['username'] ?? $email;
    $action = $_POST['ws_action'];

    try {
        // ---- the team (from team_id, or from the task for move / delete) ----
        $taskRow = null; $fileRow = null;
        if ($action === 'delete_file') {
            $fileId = (int)($_POST['file_id'] ?? 0);
            $q = $conn->prepare("SELECT file_id, team_id, uploaded_by, file_path FROM team_files WHERE file_id = ?");
            $q->bind_param("i", $fileId);
            $q->execute();
            $fileRow = $q->get_result()->fetch_assoc();
            if (!$fileRow) twJson(['ok' => false, 'message' => 'This file no longer exists.']);
            $teamId = (int)$fileRow['team_id'];
        } elseif (in_array($action, ['move_task', 'delete_task'], true)) {
            $taskId = (int)($_POST['task_id'] ?? 0);
            $q = $conn->prepare("SELECT task_id, team_id, title FROM team_tasks WHERE task_id = ?");
            $q->bind_param("i", $taskId);
            $q->execute();
            $taskRow = $q->get_result()->fetch_assoc();
            if (!$taskRow) twJson(['ok' => false, 'message' => 'This task no longer exists.']);
            $teamId = (int)$taskRow['team_id'];
        } else {
            $teamId = (int)($_POST['team_id'] ?? 0);
        }

        $team = twTeamFor($conn, $teamId, $email, $role);
        if (!$team) twJson(['ok' => false, 'message' => 'You are not a member of this team.']);
        $members  = twMembers($conn, $team);
        $isLeader = $email === strtolower($team['leader_email']);
        $canDeleteAll = $role === 'organization' || $isLeader;   // can remove any task / file of the team

        // a Completed team (final report accepted) is finished: the board and the report can't change any more
        if ($team['status'] === 'completed' && in_array($action, ['add_task', 'move_task', 'delete_task', 'submit_report'], true)) {
            twJson(['ok' => false, 'message' => 'This team is completed, so its work can no longer be changed.']);
        }

        switch ($action) {

            case 'load':
                twRefreshStatus($conn, $teamId);   // the deadline may have passed since the last visit
                $st = $conn->prepare("SELECT status FROM org_teams WHERE id = ?");
                $st->bind_param("i", $teamId);
                $st->execute();
                $status = $st->get_result()->fetch_row()[0] ?? 'ontrack';
                twJson(['ok' => true,
                        'members'   => $members,
                        'tasks'     => twTasks($conn, $teamId),
                        'files'     => twFiles($conn, $teamId, $email, $canDeleteAll),
                        'messages'  => twMessages($conn, $teamId, (int)($_POST['after_id'] ?? 0), $email),
                        'report'    => twReport($conn, $teamId),
                        'status'    => $status,
                        'is_leader' => $isLeader]);

            case 'send_message':
                $text = trim($_POST['message'] ?? '');
                if ($text === '')              twJson(['ok' => false, 'message' => 'Type a message first.']);
                if (mb_strlen($text) > 500)    twJson(['ok' => false, 'message' => 'A message can have up to 500 characters.']);
                twAddMessage($conn, $teamId, $email, $text);
                twJson(['ok' => true]);

            case 'add_task':
                $title    = trim(preg_replace('/\s+/', ' ', $_POST['title'] ?? ''));
                $desc     = trim($_POST['description'] ?? '');
                $col      = $_POST['col'] ?? 'todo';
                $assignee = strtolower(trim($_POST['assignee'] ?? ''));
                $due      = trim($_POST['due'] ?? '');

                if (mb_strlen($title) < 3 || mb_strlen($title) > 120)  twJson(['ok' => false, 'message' => 'The task title must be 3 to 120 characters.']);
                if (mb_strlen($desc) > 500)                            twJson(['ok' => false, 'message' => 'The description can have up to 500 characters.']);
                if (!isset(TW_STATUS[$col]))                           twJson(['ok' => false, 'message' => 'Please choose a column.']);
                if ($assignee !== '' && !in_array($assignee, array_column($members, 'email'), true))
                    twJson(['ok' => false, 'message' => 'The task can only be given to a member of this team.']);
                if ($due !== '') {
                    $d = DateTime::createFromFormat('Y-m-d', $due);
                    if (!$d || $d->format('Y-m-d') !== $due)           twJson(['ok' => false, 'message' => 'Please choose a valid due date.']);
                    if ($due < date('Y-m-d'))                          twJson(['ok' => false, 'message' => 'The due date can’t be in the past.']);
                    if (!empty($team['deadline']) && $due > $team['deadline'])
                        twJson(['ok' => false, 'message' => 'The due date can’t be after the team deadline.']);
                }

                $status   = TW_STATUS[$col];
                $assignDb = $assignee !== '' ? $assignee : null;
                $dueDb    = $due !== '' ? $due : null;
                $descDb   = $desc !== '' ? $desc : null;
                $q = $conn->prepare("INSERT INTO team_tasks (team_id, assigned_to, title, description, due_date, status)
                                     VALUES (?, ?, ?, ?, ?, ?)");
                $q->bind_param("isssss", $teamId, $assignDb, $title, $descDb, $dueDb, $status);
                $q->execute();

                // tell the student who got the task (not when they gave it to themselves)
                if ($assignee !== '' && $assignee !== $email) {
                    $nTitle = 'New Task Assigned';
                    $nMsg   = $name . ' gave you the task "' . $title . '" in the team ' . $team['name']
                            . ($due !== '' ? ' (due ' . date('M d, Y', strtotime($due)) . ')' : '') . '.';
                    $n = $conn->prepare("INSERT INTO notifications (Email, title, message, type, status) VALUES (?, ?, ?, 'team', 'Unread')");
                    $n->bind_param("sss", $assignee, $nTitle, $nMsg);
                    $n->execute();
                }
                twRefreshStatus($conn, $teamId);
                twJson(['ok' => true, 'tasks' => twTasks($conn, $teamId)]);

            case 'move_task':
                $col = $_POST['col'] ?? '';
                if (!isset(TW_STATUS[$col])) twJson(['ok' => false, 'message' => 'Unknown column.']);
                $status = TW_STATUS[$col];
                $id = (int)$taskRow['task_id'];
                $q = $conn->prepare("UPDATE team_tasks SET status = ? WHERE task_id = ?");
                $q->bind_param("si", $status, $id);
                $q->execute();
                twRefreshStatus($conn, $teamId);
                twJson(['ok' => true, 'tasks' => twTasks($conn, $teamId)]);

            case 'delete_task':
                // only the organization or the team leader
                if (!$canDeleteAll) {
                    twJson(['ok' => false, 'message' => 'Only the organization or the team leader can delete a task.']);
                }
                $id = (int)$taskRow['task_id'];
                $q = $conn->prepare("DELETE FROM team_tasks WHERE task_id = ?");
                $q->bind_param("i", $id);
                $q->execute();
                twRefreshStatus($conn, $teamId);
                twJson(['ok' => true, 'tasks' => twTasks($conn, $teamId)]);

            case 'upload_file':
                $files = $_FILES['files'] ?? null;
                if (!$files || !is_array($files['name'])) twJson(['ok' => false, 'message' => 'Choose a file to upload.']);

                $dir = __DIR__ . '/../Assets/Uploads/team_files/';
                if (!is_dir($dir)) mkdir($dir, 0775, true);
                $ins = $conn->prepare("INSERT INTO team_files (team_id, uploaded_by, original_name, file_path, file_size, mime_type)
                                       VALUES (?, ?, ?, ?, ?, ?)");
                $errors = [];
                foreach ($files['name'] as $i => $original) {
                    if ($files['error'][$i] === UPLOAD_ERR_NO_FILE) continue;
                    $ext  = strtolower(pathinfo($original, PATHINFO_EXTENSION));
                    $size = (int)$files['size'][$i];
                    if ($files['error'][$i] !== UPLOAD_ERR_OK || $size > TW_FILE_MAX) { $errors[] = '"' . $original . '" is larger than 10MB.'; continue; }
                    if (!in_array($ext, TW_FILE_EXT, true))                            { $errors[] = '"' . $original . '" is not a supported file type.'; continue; }

                    // safe file name on disk; the real name is kept in the database
                    $stored = 'team_' . $teamId . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                    if (!move_uploaded_file($files['tmp_name'][$i], $dir . $stored)) { $errors[] = '"' . $original . '" could not be saved.'; continue; }

                    $path = 'Assets/Uploads/team_files/' . $stored;
                    $mime = mime_content_type($dir . $stored) ?: 'application/octet-stream';
                    $name = mb_substr(basename($original), 0, 255);
                    $ins->bind_param("isssis", $teamId, $email, $name, $path, $size, $mime);
                    $ins->execute();
                }
                twJson(['ok' => true, 'errors' => $errors, 'files' => twFiles($conn, $teamId, $email, $canDeleteAll)]);

            case 'delete_file':
                if (!$canDeleteAll && strtolower($fileRow['uploaded_by']) !== $email) {
                    twJson(['ok' => false, 'message' => 'Only the person who uploaded the file, the organization or the team leader can remove it.']);
                }
                $id = (int)$fileRow['file_id'];
                $q = $conn->prepare("DELETE FROM team_files WHERE file_id = ?");
                $q->bind_param("i", $id);
                $q->execute();
                // remove the file from the folder too (only inside team_files)
                $full = realpath(__DIR__ . '/../' . $fileRow['file_path']);
                $base = realpath(__DIR__ . '/../Assets/Uploads/team_files');
                if ($full && $base && strpos($full, $base) === 0 && is_file($full)) @unlink($full);
                twJson(['ok' => true, 'files' => twFiles($conn, $teamId, $email, $canDeleteAll)]);

            case 'submit_report':
                // the team leader sends the final report when every task on the board is Done
                if ($role !== 'student' || !$isLeader) twJson(['ok' => false, 'message' => 'Only the team leader can send the final report.']);

                $old = twReport($conn, $teamId);
                if ($old && $old['status'] === 'submitted') twJson(['ok' => false, 'message' => 'The final report was already sent. Please wait for the organization to review it.']);

                $tasks = twTasks($conn, $teamId);
                $done  = count(array_filter($tasks, fn($t) => $t['col'] === 'done'));
                if (!$tasks || $done < count($tasks)) {
                    twJson(['ok' => false, 'message' => 'Move every task to Done before sending the final report (' . $done . ' of ' . count($tasks) . ' done).']);
                }

                $summary = trim($_POST['summary'] ?? '');
                $len = mb_strlen($summary);
                if ($len < TW_SUMMARY_MIN || $len > TW_SUMMARY_MAX) {
                    twJson(['ok' => false, 'message' => 'The summary must be ' . TW_SUMMARY_MIN . ' to ' . TW_SUMMARY_MAX . ' characters.']);
                }

                $f = $_FILES['report'] ?? null;
                if (!$f || $f['error'] === UPLOAD_ERR_NO_FILE)             twJson(['ok' => false, 'message' => 'Please choose the report file.']);
                $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
                if ($f['error'] !== UPLOAD_ERR_OK || $f['size'] > TW_FILE_MAX) twJson(['ok' => false, 'message' => 'The report file must be 10MB or smaller.']);
                if (!in_array($ext, TW_REPORT_EXT, true))                      twJson(['ok' => false, 'message' => 'The report must be a PDF, Word (.doc / .docx) or ZIP file.']);

                $dir = __DIR__ . '/../Assets/Uploads/final_reports/';
                if (!is_dir($dir)) mkdir($dir, 0775, true);
                $stored = 'report_' . $teamId . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                if (!move_uploaded_file($f['tmp_name'], $dir . $stored)) twJson(['ok' => false, 'message' => 'The file could not be saved. Please try again.']);
                $path     = 'Assets/Uploads/final_reports/' . $stored;
                $original = mb_substr(basename($f['name']), 0, 255);

                // one report per team: sending it again (after "changes requested") replaces the old one
                $q = $conn->prepare("INSERT INTO team_final_reports (team_id, submitted_by, original_name, file_path, summary, status)
                                     VALUES (?, ?, ?, ?, ?, 'submitted')
                                     ON DUPLICATE KEY UPDATE submitted_by = VALUES(submitted_by), original_name = VALUES(original_name),
                                         file_path = VALUES(file_path), summary = VALUES(summary), status = 'submitted',
                                         review_note = NULL, submitted_at = CURRENT_TIMESTAMP, reviewed_at = NULL");
                $q->bind_param("issss", $teamId, $email, $original, $path, $summary);
                $q->execute();

                // remove the old file (only inside final_reports)
                if ($old) {
                    $full = realpath(__DIR__ . '/../' . $old['path']);
                    $base = realpath(__DIR__ . '/../Assets/Uploads/final_reports');
                    if ($full && $base && strpos($full, $base) === 0 && is_file($full)) @unlink($full);
                }

                // tell the organization, and the team in the chat
                $nTitle = 'Final Report Submitted';
                $nMsg   = $name . ' sent the final report of the team "' . $team['name'] . '" for "' . $team['project'] . '". Open the team to review it.';
                $n = $conn->prepare("INSERT INTO notifications (Email, title, message, type, status) VALUES (?, ?, ?, 'team', 'Unread')");
                $n->bind_param("sss", $team['organization_email'], $nTitle, $nMsg);
                $n->execute();
                twAddMessage($conn, $teamId, $email, 'I sent the final report (' . $original . ') to the organization for review.');

                twRefreshStatus($conn, $teamId);
                twJson(['ok' => true, 'report' => twReport($conn, $teamId)]);
        }
        twJson(['ok' => false, 'message' => 'Unknown action.']);
    } catch (Throwable $e) {
        twJson(['ok' => false, 'message' => 'Something went wrong. Please try again.']);
    }
}