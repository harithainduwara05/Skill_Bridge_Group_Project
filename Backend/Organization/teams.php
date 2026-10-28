<?php
/*
| Organization backend - Teams: create / edit team, extend deadline, review the final report
| Team status is never set by hand: twRefreshStatus() / orgRefreshTeamStatuses() work it out
| (Completed only when the organization accepts the team's final report).
| Used by: Functions/Organization/teams.php
| (the page already loaded Config/db.php + Session/Session.php and checked the role)
*/

$user = current_user();
$organization_email = $user['email'];

require_once __DIR__ . "/org_db.php";

// Kanban tasks + team chat are shared with the students of the team (Backend/TeamWorkspace.php).
// The workspace on this page sends fetch() requests with ws_action -> answered here as JSON.
require_once __DIR__ . "/../TeamWorkspace.php";
twHandleRequest($conn, $user);

// a deadline may have passed since the last visit -> save the correct status of every team first
orgRefreshTeamStatuses($conn, $organization_email);

function tmFetch(mysqli $conn, string $sql, string $types = '', array $params = []): array
{
    try {
        $st = $conn->prepare($sql);
        if ($types !== '') $st->bind_param($types, ...$params);
        $st->execute();
        $res = $st->get_result();
        return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    } catch (Throwable $e) {
        return [];
    }
}

// ---------------------------------------------------------------
// Data for "Create New Team"
//   - only this organization's projects that can have a team
//     (Open / Reviewing / Published / Active – not Draft, On Hold, Rejected or Completed)
//   - for each project, only the students whose PROPOSAL WAS ACCEPTED
//     (project_proposals.status = 'accepted', set from Proposals page)
//   - a student who is already in a team of that project is shown but locked
// ---------------------------------------------------------------
$tmProjects = tmFetch($conn,
    ORG_PROJECT_SELECT . " WHERE p.organization_email = ?
       AND p.status IN ('open', 'reviewing', 'published', 'accepted', 'active', 'inprogress')
     ORDER BY p.posted_at DESC", "s", [$organization_email]);

$tmCandidates = [];   // project_id => [ {email, name, sub, skills[], team, team_id} ]
foreach ($tmProjects as $pr) {
    $pid = (int)$pr['id'];

    // who is already in a team for this project
    $inTeam = [];
    foreach (tmFetch($conn,
        "SELECT LOWER(m.Email) AS email, t.id, t.name
         FROM org_team_members m JOIN org_teams t ON t.id = m.team_id
         WHERE t.project_id = ?", "i", [$pid]) as $r) {
        $inTeam[$r['email']] = ['id' => (int)$r['id'], 'name' => $r['name']];
    }

    $tmCandidates[$pid] = [];
    $seen = [];
    foreach (tmFetch($conn,
        "SELECT s.Email AS email, s.Name AS name, s.University AS university, s.degree
         FROM project_proposals pa
         JOIN student s ON LOWER(s.Email) = LOWER(pa.student_email)
         WHERE pa.project_id = ? AND pa.status = 'accepted'
         ORDER BY s.Name", "i", [$pid]) as $r) {
        $key = strtolower($r['email']);
        if (isset($seen[$key])) continue;
        $seen[$key] = true;

        $skills = array_column(tmFetch($conn,
            "SELECT skill_name FROM skills WHERE Email = ? ORDER BY percentage DESC LIMIT 5", "s", [$r['email']]), 'skill_name');

        $tmCandidates[$pid][] = [
            'email'   => $key,
            'name'    => $r['name'],
            'sub'     => trim(preg_replace('/^B\.Sc\.\s*in\s*/i', '', (string)$r['degree']) . ($r['university'] ? ' · ' . $r['university'] : ''), ' ·'),
            'skills'  => $skills,
            'team'    => $inTeam[$key]['name'] ?? null,
            'team_id' => $inTeam[$key]['id'] ?? null,
        ];
    }
}

// ---------- helpers for create / edit ----------
function tmFindProject(array $projects, int $id): ?array
{
    foreach ($projects as $pr) { if ((int)$pr['id'] === $id) return $pr; }
    return null;
}

// roles[email] = role  ->  only for the chosen members, trimmed
function tmCleanRoles(array $members, $rawRoles): array
{
    $rawRoles = is_array($rawRoles) ? array_change_key_case($rawRoles, CASE_LOWER) : [];
    $roles = [];
    foreach ($members as $m) { $roles[$m] = trim(preg_replace('/\s+/', ' ', (string)($rawRoles[$m] ?? ''))); }
    return $roles;
}

// The day the project ends = Application Deadline (projects.deadline) + Duration (projects.duration_weeks).
// The team works AFTER the applications close, so its deadline can be up to this day. '' = no limit.
function tmProjectEnd($deadline, $weeks): string
{
    if (empty($deadline) || !strtotime($deadline)) return '';
    return date('Y-m-d', strtotime($deadline . ' +' . (max(0, (int)$weeks) * 7) . ' days'));
}

// Team deadline: required, not in the past, not after the day the project ends
function tmCheckDeadline(string $deadline, ?array $project): ?string
{
    if ($deadline === '' || !strtotime($deadline)) return null;
    $dl = date('Y-m-d', strtotime($deadline));
    if ($dl < date('Y-m-d')) return null;
    $end = $project ? tmProjectEnd($project['deadline'] ?? '', $project['duration_weeks'] ?? 0) : '';
    if ($end !== '' && $dl > $end) return null;
    return $dl;
}

// Save members + roles, and keep student_projects in sync so the
// students see the project and their role in their own dashboard.
function tmSaveMembers(mysqli $conn, int $teamId, int $projectId, array $roles, array $oldMembers = []): void
{
    $d = $conn->prepare("DELETE FROM org_team_members WHERE team_id = ?");
    $d->bind_param("i", $teamId);
    $d->execute();

    $ins  = $conn->prepare("INSERT INTO org_team_members (team_id, Email, role) VALUES (?, ?, ?)");
    $find = $conn->prepare("SELECT student_project_id FROM student_projects WHERE LOWER(Email) = ? AND project_id = ? LIMIT 1");
    $upd  = $conn->prepare("UPDATE student_projects SET role = ? WHERE student_project_id = ?");
    $add  = $conn->prepare("INSERT INTO student_projects (Email, project_id, role, progress, status) VALUES (?, ?, ?, 0, 'In Progress')");

    foreach ($roles as $email => $role) {
        $ins->bind_param("iss", $teamId, $email, $role);
        $ins->execute();

        $find->bind_param("si", $email, $projectId);
        $find->execute();
        $row = $find->get_result()->fetch_assoc();
        if ($row) {
            $spId = (int)$row['student_project_id'];
            $upd->bind_param("si", $role, $spId);
            $upd->execute();
        } else {
            $add->bind_param("sis", $email, $projectId, $role);
            $add->execute();
        }
    }

    // students removed from the team (and not in another team of this project) leave the project
    $removed = array_diff($oldMembers, array_keys($roles));
    if ($removed) {
        $other = $conn->prepare("SELECT 1 FROM org_team_members m JOIN org_teams t ON t.id = m.team_id
                                 WHERE LOWER(m.Email) = ? AND t.project_id = ? LIMIT 1");
        $del = $conn->prepare("DELETE FROM student_projects WHERE LOWER(Email) = ? AND project_id = ?");
        foreach ($removed as $email) {
            $other->bind_param("si", $email, $projectId);
            $other->execute();
            if ($other->get_result()->fetch_row()) continue;
            $del->bind_param("si", $email, $projectId);
            $del->execute();
        }
    }
}

// Latest 6 things that happened in the team: [ ['text' => .., 'when' => 'Oct 20, 2026'], ... ]
// Built from the dates already saved in the team tables (team created, deadline changes, tasks, files, final report).
function tmActivity(mysqli $conn, int $teamId): array
{
    $rows = tmFetch($conn,
        "SELECT CONCAT('Team created with ', (SELECT COUNT(*) FROM org_team_members m WHERE m.team_id = t.id), ' member(s)') AS text,
                t.created_at AS at FROM org_teams t WHERE t.id = ?
         UNION ALL
         SELECT CONCAT('Deadline moved to ', DATE_FORMAT(d.new_deadline, '%b %d, %Y'), ' – ', d.reason), d.changed_at
         FROM team_deadline_changes d WHERE d.team_id = ?
         UNION ALL
         SELECT CONCAT('Task added: ', k.title), k.created_at FROM team_tasks k WHERE k.team_id = ?
         UNION ALL
         SELECT CONCAT('File shared: ', f.original_name), f.uploaded_at FROM team_files f WHERE f.team_id = ?
         UNION ALL
         SELECT 'Final report sent for review', r.submitted_at FROM team_final_reports r WHERE r.team_id = ?
         UNION ALL
         SELECT IF(r.status = 'accepted', 'Final report accepted – team completed', 'Changes requested on the final report'), r.reviewed_at
         FROM team_final_reports r WHERE r.team_id = ? AND r.reviewed_at IS NOT NULL
         ORDER BY at DESC LIMIT 6",
        "iiiiii", [$teamId, $teamId, $teamId, $teamId, $teamId, $teamId]);
    return array_map(fn($a) => ['text' => $a['text'], 'when' => date('M d, Y', strtotime($a['at']))], $rows);
}

function tmNotify(mysqli $conn, array $emails, string $title, string $message): void
{
    $nq = $conn->prepare("INSERT INTO notifications (Email, title, message, type, status) VALUES (?, ?, ?, 'project', 'Unread')");
    foreach ($emails as $e) {
        $nq->bind_param("sss", $e, $title, $message);
        $nq->execute();
    }
}

// ---------------------------------------------------------------
// Save a new team
// ---------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create_team') {
    $name      = trim(preg_replace('/\s+/', ' ', $_POST['team_name'] ?? ''));
    $projectId = (int)($_POST['project_id'] ?? 0);
    $members   = array_values(array_unique(array_map(fn($m) => strtolower(trim($m)), (array)($_POST['members'] ?? []))));
    $leader    = strtolower(trim($_POST['leader'] ?? ''));
    $roles     = tmCleanRoles($members, $_POST['roles'] ?? []);
    $project   = tmFindProject($tmProjects, $projectId);
    $deadline  = tmCheckDeadline(trim($_POST['deadline'] ?? ''), $project);

    // only accepted students who are not in another team of this project
    $allowed = [];
    foreach ($tmCandidates[$projectId] ?? [] as $c) { if (!$c['team_id']) $allowed[] = $c['email']; }

    $error = null;
    if ($name === '' || !$project)                                                        $error = '1';
    elseif (mb_strlen($name) < 3 || mb_strlen($name) > 100)                                $error = 'name';
    elseif (!$members || array_diff($members, $allowed))                                   $error = 'members';
    elseif (count($members) > max(1, (int)$project['members']))                            $error = 'members';
    elseif (!in_array($leader, $members, true))                                            $error = '1';
    elseif (in_array('', $roles, true) || max(array_map('mb_strlen', $roles)) > 50)        $error = 'roles';
    elseif (!$deadline)                                                                    $error = 'deadline';

    if ($error) { header("Location: teams.php?team_error=" . $error); exit; }

    try {
        $conn->begin_transaction();

        // (the organization of a team = the organization of its project)
        $ins = $conn->prepare("INSERT INTO org_teams (project_id, name, leader_email, deadline, status)
                               VALUES (?, ?, ?, ?, 'ontrack')");
        $ins->bind_param("isss", $projectId, $name, $leader, $deadline);
        $ins->execute();
        $teamId = (int)$conn->insert_id;

        tmSaveMembers($conn, $teamId, $projectId, $roles);

        // tell every member which team they are in and what their role is
        $leaderName = $leader;
        foreach ($tmCandidates[$projectId] as $c) { if ($c['email'] === $leader) $leaderName = $c['name']; }
        $nq = $conn->prepare("INSERT INTO notifications (Email, title, message, type, status) VALUES (?, 'Added to a Team', ?, 'project', 'Unread')");
        foreach ($roles as $email => $role) {
            $msg = 'You were added to the team "' . $name . '" for the project "' . $project['title'] . '" as ' . $role
                 . ($email === $leader ? ' (Team Leader)' : '. Team leader: ' . $leaderName)
                 . '. Deadline: ' . date('M d, Y', strtotime($deadline)) . '.';
            $nq->bind_param("ss", $email, $msg);
            $nq->execute();
        }

        $conn->commit();
        header("Location: teams.php?team_created=1");
    } catch (Throwable $e) {
        try { $conn->rollback(); } catch (Throwable $ignore) {}
        // 1062 = this project already has a team with this name
        header("Location: teams.php?team_error=" . (($e->getCode() == 1062) ? 'duplicate' : 'save'));
    }
    exit;
}

// ---------------------------------------------------------------
// Actions for teams saved in the database:
//   edit_team (⋮ menu: name, deadline) | extend_deadline (Delay Review) | review_report (final report)
// ---------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && in_array($_POST['action'] ?? '', ['edit_team', 'extend_deadline', 'review_report'], true)) {
    $teamId = (int)($_POST['team_id'] ?? 0);
    // the team must belong to one of this organization's projects
    $own = tmFetch($conn, "SELECT t.id, t.project_id, t.name, t.deadline, t.status, p.title AS project
                           FROM org_teams t JOIN projects p ON p.id = t.project_id
                           WHERE t.id = ? AND p.organization_email = ?",
                   "is", [$teamId, $organization_email])[0] ?? null;
    if (!$own) { header("Location: teams.php?team_error=1"); exit; }

    // a Completed team (final report accepted) can't be changed any more
    if ($own['status'] === 'completed') { header("Location: teams.php?team_error=completed"); exit; }

    try {
        if ($_POST['action'] === 'review_report') {
            // Accept the final report  -> team Completed (+ project Completed when all its teams are)
            // Request changes (+ note) -> the team leader fixes it and sends it again
            $report   = twReport($conn, $teamId);
            $decision = $_POST['decision'] ?? '';
            $note     = trim($_POST['note'] ?? '');
            if (!$report || $report['status'] !== 'submitted' || !in_array($decision, ['accept', 'changes'], true)) {
                header("Location: teams.php?team_error=report");
                exit;
            }
            if ($decision === 'changes' && (mb_strlen($note) < 10 || mb_strlen($note) > 500)) {
                header("Location: teams.php?team_error=report_note");
                exit;
            }

            $memberEmails = array_column(tmFetch($conn, "SELECT Email FROM org_team_members WHERE team_id = ?", "i", [$teamId]), 'Email');
            $conn->begin_transaction();

            if ($decision === 'accept') {
                $q = $conn->prepare("UPDATE team_final_reports SET status = 'accepted', review_note = NULL, reviewed_at = NOW() WHERE team_id = ?");
                $q->bind_param("i", $teamId);
                $q->execute();
                twRefreshStatus($conn, $teamId);   // -> completed

                // the members' project (student dashboard) is finished too
                $pid = (int)$own['project_id'];
                $sp = $conn->prepare("UPDATE student_projects SET status = 'Completed', progress = 100 WHERE project_id = ? AND Email = ?");
                foreach ($memberEmails as $me) { $sp->bind_param("is", $pid, $me); $sp->execute(); }

                // the project is Completed when every team of it is Completed
                $left = tmFetch($conn, "SELECT COUNT(*) AS c FROM org_teams WHERE project_id = ? AND status <> 'completed'", "i", [$pid])[0]['c'] ?? 1;
                if ((int)$left === 0) {
                    $pq = $conn->prepare("UPDATE projects SET status = 'completed' WHERE id = ?");
                    $pq->bind_param("i", $pid);
                    $pq->execute();
                }

                tmNotify($conn, $memberEmails, 'Final Report Accepted',
                         'The organization accepted the final report of ' . $own['name'] . ' for "' . $own['project'] . '". The project is completed – well done!');
                twAddMessage($conn, $teamId, $organization_email, 'We accepted your final report. The team is now completed – thank you all!');
                $done = 'report_accepted';
            } else {
                $q = $conn->prepare("UPDATE team_final_reports SET status = 'changes_requested', review_note = ?, reviewed_at = NOW() WHERE team_id = ?");
                $q->bind_param("si", $note, $teamId);
                $q->execute();
                twRefreshStatus($conn, $teamId);

                tmNotify($conn, $memberEmails, 'Final Report: Changes Requested',
                         'The organization asked for changes to the final report of ' . $own['name'] . ': ' . $note);
                twAddMessage($conn, $teamId, $organization_email, 'Please update the final report and send it again. ' . $note);
                $done = 'report_changes';
            }

            $conn->commit();
            header("Location: teams.php?team_updated=" . $done);
            exit;
        }

        if ($_POST['action'] === 'extend_deadline') {
            $newDl  = trim($_POST['deadline'] ?? '');
            $reason = trim($_POST['reason'] ?? '');
            // new date after today, and a short reason (5 - 300 characters) that is sent to the team
            if (!$newDl || !strtotime($newDl) || strtotime($newDl) <= strtotime('today')
                || mb_strlen($reason) < 5 || mb_strlen($reason) > 300) {
                header("Location: teams.php?team_error=edit");
                exit;
            }
            $dl = date('Y-m-d', strtotime($newDl));
            $q = $conn->prepare("UPDATE org_teams SET deadline = ? WHERE id = ?");
            $q->bind_param("si", $dl, $teamId);
            $q->execute();

            // keep a history of deadline changes (team_deadline_changes)
            $oldDl = !empty($own['deadline']) ? $own['deadline'] : date('Y-m-d');
            $hq = $conn->prepare("INSERT INTO team_deadline_changes (team_id, old_deadline, new_deadline, reason) VALUES (?, ?, ?, ?)");
            $hq->bind_param("isss", $teamId, $oldDl, $dl, $reason);
            $hq->execute();

            // the status follows the new deadline (On Track again unless a task is still overdue)
            twRefreshStatus($conn, $teamId);

            // tell every member of the team
            $nTitle = 'Team Deadline Extended';
            $nMsg   = 'The deadline for ' . $own['name'] . ' was moved to ' . date('M d, Y', strtotime($dl)) . '. Reason: ' . mb_substr($reason, 0, 300);
            $nq = $conn->prepare("INSERT INTO notifications (Email, title, message, type, status)
                                  SELECT Email, ?, ?, 'project', 'Unread' FROM org_team_members WHERE team_id = ?");
            $nq->bind_param("ssi", $nTitle, $nMsg, $teamId);
            $nq->execute();

            // ...and the same note in the team chat, so the whole team sees it there too
            twAddMessage($conn, $teamId, $organization_email,
                         'The team deadline was moved to ' . date('M d, Y', strtotime($dl)) . '. Reason: ' . mb_substr($reason, 0, 300));

            header("Location: teams.php?team_updated=extended");
            exit;
        }

        // edit_team: only the team name and the team deadline can be changed here
        // (members and leader are set when the team is created)
        $name     = trim(preg_replace('/\s+/', ' ', $_POST['team_name'] ?? ''));
        $pid      = (int)$own['project_id'];
        $project  = tmFindProject($tmProjects, $pid);
        $postedDl = trim($_POST['deadline'] ?? '');
        // keeping the old deadline is always fine (even if it has already passed)
        $deadline = (!empty($own['deadline']) && $postedDl !== '' && strtotime($postedDl)
                     && date('Y-m-d', strtotime($postedDl)) === date('Y-m-d', strtotime($own['deadline'])))
                    ? date('Y-m-d', strtotime($own['deadline']))
                    : tmCheckDeadline($postedDl, $project);

        if (mb_strlen($name) < 3 || mb_strlen($name) > 100 || !$deadline) {
            header("Location: teams.php?team_error=edit");
            exit;
        }

        $q = $conn->prepare("UPDATE org_teams SET name = ?, deadline = ? WHERE id = ?");
        $q->bind_param("ssi", $name, $deadline, $teamId);
        $q->execute();
        twRefreshStatus($conn, $teamId);

        header("Location: teams.php?team_updated=edited");
    } catch (Throwable $e) {
        try { $conn->rollback(); } catch (Throwable $ignore) {}
        header("Location: teams.php?team_error=" . (($e->getCode() == 1062) ? 'duplicate' : 'edit'));
    }
    exit;
}

$flash = null;
$tmErrText = [
    'name'     => 'The team name must be 3 to 100 characters.',
    'members'  => 'Only students with an accepted proposal (and not already in another team) can be added, up to the number the project needs.',
    'roles'    => 'Please give every team member a role (max 50 characters).',
    'deadline' => 'Please pick a team deadline from today up to the day the project ends.',
    'save'     => 'Something went wrong while saving. Please try again.',
];
$tmUpdated = [
    'report_accepted' => ['Final report accepted', 'The team is now completed and its members were notified.'],
    'report_changes'  => ['Changes requested', 'The team was asked to update the final report and send it again.'],
    'edited'  => ['Team updated', 'Your changes to the team were saved.'],
    'extended' => ['Deadline extended', 'The new deadline was saved and the team was notified.'],
];
if (isset($_GET['team_updated'], $tmUpdated[$_GET['team_updated']])) {
    $flash = ['type' => 'success', 'title' => $tmUpdated[$_GET['team_updated']][0], 'message' => $tmUpdated[$_GET['team_updated']][1]];
} elseif (in_array($_GET['team_error'] ?? '', ['report', 'report_note', 'completed'], true)) {
    $flash = ['type' => 'error', 'title' => 'Could not save', 'message' => [
        'report'      => 'This final report can’t be reviewed now. Please refresh the page.',
        'report_note' => 'Please tell the team what to change (10 to 500 characters).',
        'completed'   => 'This team is completed, so it can no longer be changed.',
    ][$_GET['team_error']]];
} elseif (($_GET['team_error'] ?? '') === 'edit') {
    $flash = ['type' => 'error', 'title' => 'Could not save changes', 'message' => 'Please check the team details and try again.'];
} elseif (isset($_GET['team_created'])) {
    $flash = ['type' => 'success', 'title' => 'Team created', 'message' => 'The new team was created successfully.'];
} elseif (($_GET['team_error'] ?? '') === 'duplicate') {
    $flash = ['type' => 'error', 'title' => 'Name already used', 'message' => 'This project already has a team with this name. Please choose another name.'];
} elseif (isset($_GET['team_error'], $tmErrText[$_GET['team_error']])) {
    $flash = ['type' => 'error', 'title' => 'Could not create team', 'message' => $tmErrText[$_GET['team_error']]];
} elseif (isset($_GET['team_error'])) {
    $flash = ['type' => 'error', 'title' => 'Could not create team', 'message' => 'Please fill all required fields and try again.'];
}

// ---------------------------------------------------------------
// Teams created by this organization (from the database)
// ---------------------------------------------------------------
// Buttons at the bottom of a team card depend on the team status
// (Completed -> only the final report; a report waiting for review -> "Review Report" first)
$tmStateActions = [
    'ontrack'   => ['View', 'Task', 'Chat'],
    'behind'    => ['View', 'Review', 'Chat'],
    'completed' => ['Final Report'],
];

$dbTeams = [];
foreach (tmFetch($conn,
    "SELECT t.id, t.project_id, t.name, t.leader_email, t.deadline, t.status, t.created_at,
            p.title AS project, o.Name AS company, c.category_name AS category,
            p.deadline AS project_deadline, p.duration_weeks, CONCAT(p.duration_weeks, ' Weeks') AS duration,
            (SELECT GROUP_CONCAT(ps.skill_name SEPARATOR ', ') FROM project_skills ps WHERE ps.project_id = p.id) AS keywords
     FROM org_teams t
     JOIN projects p ON p.id = t.project_id
     LEFT JOIN organization o ON o.Email = p.organization_email
     LEFT JOIN project_categories c ON c.category_id = p.category_id
     WHERE p.organization_email = ?
     ORDER BY t.created_at DESC", "s", [$organization_email]) as $row) {

    $memberRows = tmFetch($conn,
        "SELECT s.Email, s.Name, s.University, s.degree, s.year, COALESCE(NULLIF(m.role, ''), sp.role) AS role,
                sp.progress, sp.status AS work_status
         FROM org_team_members m
         JOIN student s ON LOWER(s.Email) = LOWER(m.Email)
         LEFT JOIN student_projects sp ON sp.Email = m.Email
              AND sp.project_id = (SELECT project_id FROM org_teams WHERE id = m.team_id)
         WHERE m.team_id = ?", "i", [(int)$row['id']]);

    $leaderName = $row['leader_email']; $leaderRole = 'Team Leader'; $others = []; $progressSum = 0;
    foreach ($memberRows as $mr) {
        $progressSum += (int)($mr['progress'] ?? 0);
        if (strtolower($mr['Email']) === strtolower($row['leader_email'])) {
            $leaderName = $mr['Name'];
            if (!empty($mr['role'])) $leaderRole = $mr['role'];
        } else {
            $others[] = $mr['Name'];
        }
    }
    // Progress = tasks done on the Kanban board (shared with the students).
    // No tasks yet -> the average progress of the members (student_projects.progress).
    $teamTasks = twTasks($conn, (int)$row['id']);
    if ($teamTasks) {
        $doneTasks = count(array_filter($teamTasks, fn($t) => $t['col'] === 'done'));
        $percent = (int)round($doneTasks / count($teamTasks) * 100);
    } else {
        $percent = $memberRows ? (int)round($progressSum / count($memberRows)) : 0;
    }

    // Full details for the "View" popup
    $detailMembers = [];
    foreach ($memberRows as $mr) {
        $sk = tmFetch($conn, "SELECT skill_name FROM skills WHERE Email = ? ORDER BY percentage DESC LIMIT 4", "s", [$mr['Email']]);
        $isLeader = strtolower($mr['Email']) === strtolower($row['leader_email']);
        $detailMembers[] = [
            'name' => $mr['Name'], 'role' => $mr['role'] ?: ($isLeader ? 'Team Leader' : 'Team Member'),
            'leader' => $isLeader, 'email' => $mr['Email'],
            'university' => $mr['University'] ?? '', 'degree' => $mr['degree'] ?? '', 'year' => $mr['year'] ?? '',
            'skills' => array_column($sk, 'skill_name'),
            'progress' => (int)($mr['progress'] ?? 0), 'work' => $mr['work_status'] ?: 'Not started',
        ];
    }
    usort($detailMembers, fn($a, $b) => $b['leader'] <=> $a['leader']);   // leader first

    $report = twReport($conn, (int)$row['id']);
    $doneCount = count(array_filter($teamTasks, fn($t) => $t['col'] === 'done'));

    $time = 'No deadline'; $tone = 'ok'; $state = $row['status'] ?: 'ontrack';
    if ($state === 'completed') {
        $percent = 100;
        $time = 'Finished on ' . ($report && $report['reviewed'] ? date('M d', strtotime($report['reviewed'])) : '-');
    } elseif (!empty($row['deadline'])) {
        $days = (int)floor((strtotime($row['deadline']) - strtotime('today')) / 86400);
        if ($days >= 0) { $time = $days . ' day' . ($days === 1 ? '' : 's') . ' left'; $tone = $days <= 14 ? 'warn' : 'ok'; }
        else            { $time = abs($days) . ' days delayed'; $tone = 'late'; }
    }
    $statusText = ['ontrack' => 'On Track', 'behind' => 'Behind Schedule', 'completed' => 'Completed'][$state] ?? 'On Track';

    $dbTeams[] = [
        'name' => $row['name'], 'state' => $state, 'status' => $statusText,
        'project' => $row['project'],
        'leader' => $leaderName, 'role' => $leaderRole,
        'members' => array_slice($others, 0, 3), 'more' => max(0, count($others) - 3),
        // the skills a team needs are the project's skills (project_skills)
        'skills' => array_values(array_filter(array_map('trim', explode(',', $row['keywords'] ?? '')))),
        'phase' => $state === 'completed' ? 'Project Completed'
                 : ($report && $report['status'] === 'submitted' ? 'Final report sent – waiting for your review'
                 : ($report && $report['status'] === 'changes_requested' ? 'Final report: changes requested'
                 : ($percent > 0 ? 'In Progress' : 'Phase 1: Planning'))),
        'percent' => $percent,
        // the final report is waiting for this organization
        'report_waiting' => $report && $report['status'] === 'submitted',
        'deadline' => !empty($row['deadline']) ? date('M d', strtotime($row['deadline'])) : '',
        'time' => $time, 'tone' => $tone,
        // a final report waiting for review -> the first button opens it
        'actions' => ($report && $report['status'] === 'submitted' && $state !== 'completed')
                     ? ['Review Report', 'Task', 'Chat'] : ($tmStateActions[$state] ?? $tmStateActions['ontrack']),
        'id' => (int)$row['id'],
        'project_id' => (int)$row['project_id'],
        'leader_email' => strtolower($row['leader_email']),
        'member_emails' => array_map(fn($m) => strtolower($m['Email']), $memberRows),
        'deadline_raw' => !empty($row['deadline']) ? date('Y-m-d', strtotime($row['deadline'])) : '',
        'tasks' => $teamTasks,
        // the team deadline can be moved up to the day the project ends
        'project_deadline_raw' => tmProjectEnd($row['project_deadline'], $row['duration_weeks']),
        'details' => [
            'company' => $row['company'], 'category' => $row['category'], 'duration' => $row['duration'],
            'project_deadline' => tmProjectEnd($row['project_deadline'], $row['duration_weeks']) !== ''
                                  ? date('M d, Y', strtotime(tmProjectEnd($row['project_deadline'], $row['duration_weeks']))) : '',
            'team_deadline' => !empty($row['deadline']) ? date('M d, Y', strtotime($row['deadline'])) : '',
            'created' => date('M d, Y', strtotime($row['created_at'])),
            'members' => $detailMembers,
            // latest things that happened (from the dates in the team tables)
            'activity' => tmActivity($conn, (int)$row['id']),
            // final report (team_final_reports) + how many tasks are Done (the leader can send it when all are)
            'report' => $report,
            'tasks_total' => count($teamTasks), 'tasks_done' => $doneCount,
            'can_review' => true,
        ],
    ];
}

// Only the teams saved in the database (org_teams) are shown
$teams = $dbTeams;

// Summary cards = the teams shown on this page
$activeCount    = count(array_filter($teams, fn($t) => $t['state'] !== 'completed'));
$studentsCount  = array_sum(array_map(fn($t) => 1 + count($t['members']) + $t['more'], $teams));   // leader + members
$summary = [
    ['icon' => 'hub',          'class' => 'navy',  'label' => 'Total Teams',       'value' => count($teams)],
    ['icon' => 'groups',       'class' => 'blue',  'label' => 'Active Teams',      'value' => $activeCount],
    ['icon' => 'check_circle', 'class' => 'green', 'label' => 'Completed Teams',   'value' => count($teams) - $activeCount],
    ['icon' => 'school',       'class' => 'sky',   'label' => 'Students Involved', 'value' => $studentsCount],
];

function tmInitials($name) {
    $parts = preg_split('/\s+/', trim($name));
    $out = '';
    foreach (array_slice($parts, 0, 2) as $p) { $out .= mb_strtoupper(mb_substr($p, 0, 1)); }
    return $out;
}
$tmColors = ['#1e3a5f', '#0f766e', '#7c3aed', '#b45309', '#be185d', '#2563eb', '#475569'];
$tmActionIcon = ['View' => 'visibility', 'Task' => 'task_alt', 'Chat' => 'chat_bubble', 'Review' => 'rate_review',
                 'Final Report' => 'description', 'Review Report' => 'fact_check'];