<?php

include "../../../Config/db.php";
include "../../../Session/Session.php";

require_role('organization');
$user = current_user();
$organization_email = $user['email'];

include __DIR__ . "/team_schema.php";
tmEnsureSchema($conn);

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
//     (Open / Reviewing / Active – not Draft, On Hold, Rejected or Completed)
//   - for each project, only the students whose PROPOSAL WAS ACCEPTED
//     (project_applications.status = 'accepted', set from Proposals page)
//   - a student who is already in a team of that project is shown but locked
// ---------------------------------------------------------------
$tmProjects = tmFetch($conn,
    "SELECT id, title, members, deadline, keywords
     FROM projects
     WHERE organization_email = ? AND status IN ('open', 'reviewing', 'inprogress')
     ORDER BY posted_at DESC", "s", [$organization_email]);

$tmCandidates = [];   // project_id => [ {email, name, sub, skills[], team, team_id} ]
foreach ($tmProjects as $pr) {
    $pid = (int)$pr['id'];

    // who is already in a team for this project
    $inTeam = [];
    foreach (tmFetch($conn,
        "SELECT LOWER(m.Email) AS email, t.id, t.name
         FROM org_team_members m JOIN org_teams t ON t.id = m.team_id
         WHERE t.project_id = ? AND t.organization_email = ?", "is", [$pid, $organization_email]) as $r) {
        $inTeam[$r['email']] = ['id' => (int)$r['id'], 'name' => $r['name']];
    }

    $tmCandidates[$pid] = [];
    $seen = [];
    foreach (tmFetch($conn,
        "SELECT s.Email AS email, s.Name AS name, s.University AS university, s.degree
         FROM project_applications pa
         JOIN student s ON LOWER(s.Email) = LOWER(pa.Email)
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

// "React, node.js ,React" -> ['React', 'node.js']   (max 15, each max 40 chars)
function tmCleanSkills(string $raw): array
{
    $out = [];
    foreach (explode(',', $raw) as $sk) {
        $sk = trim(preg_replace('/\s+/', ' ', $sk));
        if ($sk === '' || mb_strlen($sk) > 40) continue;
        $out[mb_strtolower($sk)] ??= $sk;   // keep the first spelling
    }
    return array_slice(array_values($out), 0, 15);
}

// roles[email] = role  ->  only for the chosen members, trimmed
function tmCleanRoles(array $members, $rawRoles): array
{
    $rawRoles = is_array($rawRoles) ? array_change_key_case($rawRoles, CASE_LOWER) : [];
    $roles = [];
    foreach ($members as $m) { $roles[$m] = trim(preg_replace('/\s+/', ' ', (string)($rawRoles[$m] ?? ''))); }
    return $roles;
}

// Team deadline: required, not in the past, not after the project deadline
function tmCheckDeadline(string $deadline, ?array $project): ?string
{
    if ($deadline === '' || !strtotime($deadline)) return null;
    $dl = date('Y-m-d', strtotime($deadline));
    if ($dl < date('Y-m-d')) return null;
    if ($project && !empty($project['deadline']) && strtotime($project['deadline'])
        && $dl > date('Y-m-d', strtotime($project['deadline']))) return null;
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
    $skills    = tmCleanSkills($_POST['skills'] ?? '');
    $project   = tmFindProject($tmProjects, $projectId);
    $deadline  = tmCheckDeadline(trim($_POST['deadline'] ?? ''), $project);

    // only accepted students who are not in another team of this project
    $allowed = [];
    foreach ($tmCandidates[$projectId] ?? [] as $c) { if (!$c['team_id']) $allowed[] = $c['email']; }

    $error = null;
    if ($name === '' || mb_strlen($name) > 100 || !$project)                              $error = '1';
    elseif (!$members || array_diff($members, $allowed))                                   $error = 'members';
    elseif (count($members) > max(1, (int)$project['members']))                            $error = 'members';
    elseif (!in_array($leader, $members, true))                                            $error = '1';
    elseif (in_array('', $roles, true) || max(array_map('mb_strlen', $roles)) > 50)        $error = 'roles';
    elseif (!$skills)                                                                      $error = 'skills';
    elseif (!$deadline)                                                                    $error = 'deadline';

    if ($error) { header("Location: teams.php?team_error=" . $error); exit; }

    try {
        $conn->begin_transaction();

        $skillText = implode(', ', $skills);
        $ins = $conn->prepare("INSERT INTO org_teams (organization_email, project_id, name, leader_email, skills, deadline, status)
                               VALUES (?, ?, ?, ?, ?, ?, 'ontrack')");
        $ins->bind_param("sissss", $organization_email, $projectId, $name, $leader, $skillText, $deadline);
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
        // 1062 = duplicate team name for this organization
        header("Location: teams.php?team_error=" . (($e->getCode() == 1062) ? 'duplicate' : 'save'));
    }
    exit;
}

// ---------------------------------------------------------------
// ⋮ menu actions for teams saved in the database:
//   edit_team (name, deadline) | team_status | extend_deadline (Delay Review)
// ---------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && in_array($_POST['action'] ?? '', ['edit_team', 'team_status', 'extend_deadline'], true)) {
    $teamId = (int)($_POST['team_id'] ?? 0);
    $own = tmFetch($conn, "SELECT id, project_id, name, deadline FROM org_teams WHERE id = ? AND organization_email = ?",
                   "is", [$teamId, $organization_email])[0] ?? null;
    if (!$own) { header("Location: teams.php?team_error=1"); exit; }

    try {
        if ($_POST['action'] === 'team_status') {
            $st = $_POST['status'] ?? '';
            if (!in_array($st, ['ontrack', 'behind', 'completed'], true)) throw new Exception('bad status');
            $q = $conn->prepare("UPDATE org_teams SET status = ? WHERE id = ?");
            $q->bind_param("si", $st, $teamId);
            $q->execute();
            header("Location: teams.php?team_updated=status");
            exit;
        }

        if ($_POST['action'] === 'extend_deadline') {
            $newDl  = trim($_POST['deadline'] ?? '');
            $reason = trim($_POST['reason'] ?? '');
            if (!$newDl || !strtotime($newDl) || strtotime($newDl) <= strtotime('today') || mb_strlen($reason) < 5) {
                header("Location: teams.php?team_error=edit");
                exit;
            }
            $dl = date('Y-m-d', strtotime($newDl));
            $status = !empty($_POST['set_ontrack']) ? 'ontrack' : 'behind';
            $q = $conn->prepare("UPDATE org_teams SET deadline = ?, status = ? WHERE id = ?");
            $q->bind_param("ssi", $dl, $status, $teamId);
            $q->execute();

            // tell every member of the team
            $nTitle = 'Team Deadline Extended';
            $nMsg   = 'The deadline for ' . $own['name'] . ' was moved to ' . date('M d, Y', strtotime($dl)) . '. Reason: ' . mb_substr($reason, 0, 300);
            $nq = $conn->prepare("INSERT INTO notifications (Email, title, message, type, status)
                                  SELECT Email, ?, ?, 'project', 'Unread' FROM org_team_members WHERE team_id = ?");
            $nq->bind_param("ssi", $nTitle, $nMsg, $teamId);
            $nq->execute();

            header("Location: teams.php?team_updated=extended");
            exit;
        }

        // edit_team: only the team name and the team deadline can be changed here
        // (members, leader and skills are set when the team is created)
        $name     = trim(preg_replace('/\s+/', ' ', $_POST['team_name'] ?? ''));
        $pid      = (int)$own['project_id'];
        $project  = tmFindProject($tmProjects, $pid);
        $postedDl = trim($_POST['deadline'] ?? '');
        // keeping the old deadline is always fine (even if it has already passed)
        $deadline = (!empty($own['deadline']) && $postedDl !== '' && strtotime($postedDl)
                     && date('Y-m-d', strtotime($postedDl)) === date('Y-m-d', strtotime($own['deadline'])))
                    ? date('Y-m-d', strtotime($own['deadline']))
                    : tmCheckDeadline($postedDl, $project);

        if ($name === '' || mb_strlen($name) > 100 || !$deadline) {
            header("Location: teams.php?team_error=edit");
            exit;
        }

        $q = $conn->prepare("UPDATE org_teams SET name = ?, deadline = ? WHERE id = ?");
        $q->bind_param("ssi", $name, $deadline, $teamId);
        $q->execute();

        header("Location: teams.php?team_updated=edited");
    } catch (Throwable $e) {
        try { $conn->rollback(); } catch (Throwable $ignore) {}
        header("Location: teams.php?team_error=" . (($e->getCode() == 1062) ? 'duplicate' : 'edit'));
    }
    exit;
}

$flash = null;
$tmErrText = [
    'members'  => 'Only students with an accepted proposal (and not already in another team) can be added, up to the number the project needs.',
    'roles'    => 'Please give every team member a role (max 50 characters).',
    'skills'   => 'Please add at least one required skill.',
    'deadline' => 'Please pick a team deadline from today up to the project deadline.',
    'save'     => 'Something went wrong while saving. Please try again.',
];
$tmUpdated = [
    'status'  => ['Status updated', 'The team status was changed.'],
    'edited'  => ['Team updated', 'Your changes to the team were saved.'],
    'extended' => ['Deadline extended', 'The new deadline was saved and the team was notified.'],
];
if (isset($_GET['team_updated'], $tmUpdated[$_GET['team_updated']])) {
    $flash = ['type' => 'success', 'title' => $tmUpdated[$_GET['team_updated']][0], 'message' => $tmUpdated[$_GET['team_updated']][1]];
} elseif (($_GET['team_error'] ?? '') === 'edit') {
    $flash = ['type' => 'error', 'title' => 'Could not save changes', 'message' => 'Please check the team details and try again.'];
} elseif (isset($_GET['team_created'])) {
    $flash = ['type' => 'success', 'title' => 'Team created', 'message' => 'The new team was created successfully.'];
} elseif (($_GET['team_error'] ?? '') === 'duplicate') {
    $flash = ['type' => 'error', 'title' => 'Name already used', 'message' => 'You already have a team with this name. Please choose another name.'];
} elseif (isset($_GET['team_error'], $tmErrText[$_GET['team_error']])) {
    $flash = ['type' => 'error', 'title' => 'Could not create team', 'message' => $tmErrText[$_GET['team_error']]];
} elseif (isset($_GET['team_error'])) {
    $flash = ['type' => 'error', 'title' => 'Could not create team', 'message' => 'Please fill all required fields and try again.'];
}

// ---------------------------------------------------------------
// Teams created by this organization (from the database)
// ---------------------------------------------------------------
// Buttons at the bottom of a team card depend on the team status
$tmStateActions = [
    'ontrack'   => ['View', 'Task', 'Chat'],
    'behind'    => ['View', 'Review', 'Chat'],
    'completed' => ['Final Report'],
];

$dbTeams = [];
foreach (tmFetch($conn,
    "SELECT t.id, t.project_id, t.name, t.leader_email, t.skills AS team_skills, t.deadline, t.status, t.created_at,
            p.title AS project, p.keywords, p.company, p.category, p.deadline AS project_deadline, p.duration
     FROM org_teams t JOIN projects p ON p.id = t.project_id
     WHERE t.organization_email = ?
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
    $percent = $memberRows ? (int)round($progressSum / count($memberRows)) : 0;

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

    $time = 'No deadline'; $tone = 'ok'; $state = $row['status'] ?: 'ontrack';
    if (!empty($row['deadline'])) {
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
        'skills' => array_values(array_filter(array_map('trim', explode(',', ($row['team_skills'] ?? '') !== '' ? $row['team_skills'] : ($row['keywords'] ?? ''))))),
        'phase' => $percent > 0 ? 'In Progress' : 'Phase 1: Planning', 'percent' => $percent,
        'deadline' => !empty($row['deadline']) ? date('M d', strtotime($row['deadline'])) : '',
        'time' => $time, 'tone' => $tone,
        'actions' => $tmStateActions[$state] ?? $tmStateActions['ontrack'],
        'is_new' => true,
        'id' => (int)$row['id'],
        'project_id' => (int)$row['project_id'],
        'leader_email' => strtolower($row['leader_email']),
        'member_emails' => array_map(fn($m) => strtolower($m['Email']), $memberRows),
        'deadline_raw' => !empty($row['deadline']) ? date('Y-m-d', strtotime($row['deadline'])) : '',
        'project_deadline_raw' => (!empty($row['project_deadline']) && strtotime($row['project_deadline'])) ? date('Y-m-d', strtotime($row['project_deadline'])) : '',
        'details' => [
            'company' => $row['company'], 'category' => $row['category'], 'duration' => $row['duration'],
            'project_deadline' => (!empty($row['project_deadline']) && strtotime($row['project_deadline'])) ? date('M d, Y', strtotime($row['project_deadline'])) : '',
            'team_deadline' => !empty($row['deadline']) ? date('M d, Y', strtotime($row['deadline'])) : '',
            'created' => date('M d, Y', strtotime($row['created_at'])),
            'members' => $detailMembers,
            'milestones' => [],
            'activity' => [['text' => 'Team created', 'when' => date('M d, Y', strtotime($row['created_at']))]],
        ],
    ];
}

// ---------------------------------------------------------------
// UI-ONLY DEMO DATA (kept for the demo). Teams from the database are shown first.
// state: ontrack | behind | completed      tone: ok | warn | late
// ---------------------------------------------------------------
$summary = [
    ['icon' => 'hub',           'class' => 'navy',  'label' => 'Total Teams',       'value' => 18 + 42],   // active + completed
    ['icon' => 'groups',        'class' => 'blue',  'label' => 'Active Teams',      'value' => 18],
    ['icon' => 'check_circle',  'class' => 'green', 'label' => 'Completed Teams',   'value' => 42],
    ['icon' => 'school',        'class' => 'sky',   'label' => 'Students Involved', 'value' => 156],
];

$demoTeams = [
    [
        'name' => 'Nexus Systems', 'state' => 'ontrack', 'status' => 'On Track',
        'project' => 'AI-Driven Supply Chain Optimizer',
        'leader' => 'Sanduni Perera', 'role' => 'Full Stack Lead',
        'members' => ['Ravindu Pathirana', 'Nimal Silva', 'Amaya Dissanayake'], 'more' => 2,
        'skills' => ['React.js', 'Python', 'TensorFlow', 'Figma'],
        'phase' => 'Phase 2: Prototyping', 'percent' => 72,
        'deadline' => 'Oct 24', 'time' => '14 days left', 'tone' => 'warn',
        'actions' => ['View', 'Task', 'Chat'],
    ],
    [
        'name' => 'Vortex Group', 'state' => 'behind', 'status' => 'Behind Schedule',
        'project' => 'Blockchain-Based Academic Credentials',
        'leader' => 'Malith Senanayake', 'role' => 'Security Architect',
        'members' => ['Kasun Madushanka', 'Tharushi Wijeratne'], 'more' => 1,
        'skills' => ['Solidity', 'Node.js', 'Cryptography'],
        'phase' => 'Phase 1: Smart Contract Design', 'percent' => 45,
        'deadline' => 'Oct 15', 'time' => '5 days delayed', 'tone' => 'late',
        'actions' => ['View', 'Review', 'Chat'],
    ],
    [
        'name' => 'Quantum Analytics', 'state' => 'completed', 'status' => 'Completed',
        'project' => 'Predictive Maintenance for Smart Cities',
        'leader' => 'Erandi Weerasinghe', 'role' => 'Data Analyst',
        'members' => ['Ishan Rathnayake', 'Dilini Herath'], 'more' => 3,
        'skills' => [],
        'phase' => 'All Milestones Met', 'percent' => 100,
        'deadline' => '', 'time' => 'Finished on Sep 18', 'tone' => 'ok',
        'actions' => ['Final Report'],
    ],
    [
        'name' => 'Alpha Ops', 'state' => 'ontrack', 'status' => 'On Track',
        'project' => 'Cloud Infrastructure Automation',
        'leader' => 'Kavinda Jayasuriya', 'role' => 'DevOps Engineer',
        'members' => ['Sahan Liyanage', 'Malsha Karunaratne'], 'more' => 1,
        'skills' => ['AWS', 'Docker', 'Kubernetes'],
        'phase' => 'Phase 3: Strategy Deployment', 'percent' => 85,
        'deadline' => 'Nov 02', 'time' => '22 days left', 'tone' => 'ok',
        'actions' => ['View', 'Task', 'Chat'],
    ],
];




// ---------------------------------------------------------------
// Full details for the demo teams ("View" popup)
// ---------------------------------------------------------------
function tmM($name, $role, $email, $uni, $degree, $year, $skills, $progress, $work, $leader = false) {
    return compact('name', 'role', 'email', 'skills', 'progress', 'work', 'leader') + ['university' => $uni, 'degree' => $degree, 'year' => $year];
}
$UOC = 'University of Colombo'; $UOM = 'University of Moratuwa'; $SLIIT = 'SLIIT'; $UOK = 'University of Kelaniya';

$demoDetails = [
    'Nexus Systems' => [
        'company' => 'SLIIT FOSS Community', 'category' => 'AI / Machine Learning', 'duration' => '12 Weeks',
        'project_deadline' => 'Oct 30, 2026', 'team_deadline' => 'Oct 24, 2026', 'created' => 'Aug 04, 2026',
        'members' => [
            tmM('Sanduni Perera', 'Full Stack Lead', '2023cs041@stu.ucsc.cmb.ac.lk', $UOC, 'B.Sc. in Computer Science', '3', ['React.js', 'Node.js', 'MySQL'], 78, 'In Progress', true),
            tmM('Ravindu Pathirana', 'Backend Developer', 'ravindu.p@uom.lk', $UOM, 'B.Sc. in IT', '3', ['Python', 'FastAPI', 'PostgreSQL'], 74, 'In Progress'),
            tmM('Nimal Silva', 'ML Engineer', 'nimal.s@uom.lk', $UOM, 'B.Sc. in Data Science', '4', ['TensorFlow', 'Pandas', 'Scikit-learn'], 69, 'In Progress'),
            tmM('Amaya Dissanayake', 'UI/UX Designer', 'it22104587@my.sliit.lk', $SLIIT, 'B.Sc. in Software Engineering', '3', ['Figma', 'User Research'], 90, 'In Progress'),
            tmM('Chamod Fernando', 'Data Engineer', '2023is018@stu.ucsc.cmb.ac.lk', $UOC, 'B.Sc. in Information Systems', '3', ['SQL', 'Airflow', 'Python'], 66, 'In Progress'),
            tmM('Ishara Gunawardena', 'QA Engineer', 'ishara.g@kln.ac.lk', $UOK, 'B.Sc. in Software Engineering', '2', ['Selenium', 'Jest'], 55, 'In Progress'),
        ],
        'milestones' => [
            ['title' => 'Phase 1: Requirements & Data Collection', 'done' => true],
            ['title' => 'Phase 2: Prototyping', 'done' => false, 'current' => true],
            ['title' => 'Phase 3: Model Training & Integration', 'done' => false],
            ['title' => 'Phase 4: Testing & Final Demo', 'done' => false],
        ],
        'activity' => [
            ['text' => 'Nimal Silva uploaded the demand-forecast model v2', 'when' => '2 hours ago'],
            ['text' => 'Amaya Dissanayake shared the dashboard prototype', 'when' => 'Yesterday'],
            ['text' => 'Phase 1 marked as completed', 'when' => 'Sep 12, 2026'],
        ],
    ],
    'Vortex Group' => [
        'company' => 'SLIIT FOSS Community', 'category' => 'Blockchain', 'duration' => '10 Weeks',
        'project_deadline' => 'Oct 20, 2026', 'team_deadline' => 'Oct 15, 2026', 'created' => 'Aug 11, 2026',
        'members' => [
            tmM('Malith Senanayake', 'Security Architect', '2023cs077@stu.ucsc.cmb.ac.lk', $UOC, 'B.Sc. in Computer Science', '4', ['Cryptography', 'Solidity'], 52, 'In Progress', true),
            tmM('Kasun Madushanka', 'Blockchain Developer', 'kasun.m@uom.lk', $UOM, 'B.Sc. in Computer Engineering', '3', ['Solidity', 'Hardhat', 'Ethers.js'], 40, 'In Progress'),
            tmM('Tharushi Wijeratne', 'Frontend Developer', 'it22098311@my.sliit.lk', $SLIIT, 'B.Sc. in IT', '3', ['React.js', 'Tailwind CSS'], 48, 'In Progress'),
            tmM('Dulaj Bandara', 'Backend Developer', 'dulaj.b@kln.ac.lk', $UOK, 'B.Sc. in Software Engineering', '3', ['Node.js', 'MongoDB'], 38, 'In Progress'),
        ],
        'milestones' => [
            ['title' => 'Phase 1: Smart Contract Design', 'done' => false, 'current' => true],
            ['title' => 'Phase 2: Credential Issuing Portal', 'done' => false],
            ['title' => 'Phase 3: Verification App & Audit', 'done' => false],
        ],
        'activity' => [
            ['text' => 'Deadline for Phase 1 was missed', 'when' => '5 days ago'],
            ['text' => 'Kasun Madushanka pushed contract tests', 'when' => '6 days ago'],
            ['text' => 'Team meeting: re-planned Phase 1 tasks', 'when' => 'Sep 18, 2026'],
        ],
    ],
    'Quantum Analytics' => [
        'company' => 'SLIIT FOSS Community', 'category' => 'Data Science', 'duration' => '8 Weeks',
        'project_deadline' => 'Sep 20, 2026', 'team_deadline' => 'Sep 18, 2026', 'created' => 'Jul 20, 2026',
        'members' => [
            tmM('Erandi Weerasinghe', 'Data Analyst', '2023is052@stu.ucsc.cmb.ac.lk', $UOC, 'B.Sc. in Information Systems', '4', ['Power BI', 'SQL', 'Python'], 100, 'Completed', true),
            tmM('Ishan Rathnayake', 'ML Engineer', 'ishan.r@uom.lk', $UOM, 'B.Sc. in Data Science', '4', ['XGBoost', 'Pandas'], 100, 'Completed'),
            tmM('Dilini Herath', 'IoT Developer', 'it21876540@my.sliit.lk', $SLIIT, 'B.Sc. in IT', '4', ['Arduino', 'MQTT'], 100, 'Completed'),
            tmM('Pasindu Kumara', 'Backend Developer', 'pasindu.k@kln.ac.lk', $UOK, 'B.Sc. in Software Engineering', '3', ['Java', 'Spring Boot'], 100, 'Completed'),
            tmM('Nethmi Samarasinghe', 'Data Visualization', '2023cs090@stu.ucsc.cmb.ac.lk', $UOC, 'B.Sc. in Computer Science', '3', ['D3.js', 'Chart.js'], 100, 'Completed'),
            tmM('Yasith Abeysekara', 'Technical Writer', 'yasith.a@uom.lk', $UOM, 'B.Sc. in IT', '3', ['Documentation', 'Markdown'], 100, 'Completed'),
        ],
        'milestones' => [
            ['title' => 'Sensor data pipeline', 'done' => true],
            ['title' => 'Failure prediction model', 'done' => true],
            ['title' => 'City maintenance dashboard', 'done' => true],
            ['title' => 'Final report & hand-over', 'done' => true],
        ],
        'activity' => [
            ['text' => 'Final report submitted', 'when' => 'Sep 18, 2026'],
            ['text' => 'Dashboard handed over to the client', 'when' => 'Sep 16, 2026'],
            ['text' => 'All milestones completed', 'when' => 'Sep 15, 2026'],
        ],
    ],
    'Alpha Ops' => [
        'company' => 'SLIIT FOSS Community', 'category' => 'Cloud & DevOps', 'duration' => '12 Weeks',
        'project_deadline' => 'Nov 10, 2026', 'team_deadline' => 'Nov 02, 2026', 'created' => 'Aug 01, 2026',
        'members' => [
            tmM('Kavinda Jayasuriya', 'DevOps Engineer', '2023cs012@stu.ucsc.cmb.ac.lk', $UOC, 'B.Sc. in Computer Science', '4', ['AWS', 'Terraform', 'Docker'], 88, 'In Progress', true),
            tmM('Sahan Liyanage', 'Cloud Engineer', 'sahan.l@uom.lk', $UOM, 'B.Sc. in Computer Engineering', '3', ['Kubernetes', 'Helm'], 84, 'In Progress'),
            tmM('Malsha Karunaratne', 'Automation Developer', 'it22031477@my.sliit.lk', $SLIIT, 'B.Sc. in IT', '3', ['Python', 'Ansible'], 86, 'In Progress'),
            tmM('Tharindu Wickramaratne', 'Monitoring & SRE', 'tharindu.w@kln.ac.lk', $UOK, 'B.Sc. in Software Engineering', '3', ['Grafana', 'Prometheus'], 80, 'In Progress'),
        ],
        'milestones' => [
            ['title' => 'Phase 1: Infrastructure as Code', 'done' => true],
            ['title' => 'Phase 2: CI/CD Pipelines', 'done' => true],
            ['title' => 'Phase 3: Strategy Deployment', 'done' => false, 'current' => true],
            ['title' => 'Phase 4: Monitoring & Hand-over', 'done' => false],
        ],
        'activity' => [
            ['text' => 'Kubernetes cluster moved to production', 'when' => '3 hours ago'],
            ['text' => 'Malsha Karunaratne automated nightly backups', 'when' => 'Yesterday'],
            ['text' => 'Phase 2 marked as completed', 'when' => 'Sep 20, 2026'],
        ],
    ],
];
foreach ($demoTeams as &$dt) { $dt['details'] = $demoDetails[$dt['name']] ?? null; }
unset($dt);

$teams = array_merge($dbTeams, $demoTeams);

// Summary cards also count the teams created from the database
$dbActive = count(array_filter($dbTeams, fn($t) => $t['state'] !== 'completed'));
$dbStudents = array_sum(array_map(fn($t) => 1 + count($t['members']) + $t['more'], $dbTeams));
$dbCompleted = count($dbTeams) - $dbActive;
$summary[0]['value'] += count($dbTeams);      // Total Teams
$summary[1]['value'] += $dbActive;            // Active Teams
$summary[2]['value'] += $dbCompleted;         // Completed Teams
$summary[3]['value'] += $dbStudents;          // Students Involved

function tmInitials($name) {
    $parts = preg_split('/\s+/', trim($name));
    $out = '';
    foreach (array_slice($parts, 0, 2) as $p) { $out .= mb_strtoupper(mb_substr($p, 0, 1)); }
    return $out;
}
$tmColors = ['#1e3a5f', '#0f766e', '#7c3aed', '#b45309', '#be185d', '#2563eb', '#475569'];
$tmActionIcon = ['View' => 'visibility', 'Task' => 'task_alt', 'Chat' => 'chat_bubble', 'Review' => 'rate_review', 'Final Report' => 'description'];

include "../../../Includes/org_sidebar.php";
include "../../../Includes/dash_header.php";
?>

<style>
    .tm-wrap { padding: 14px 28px 28px; }

    /* ---- summary cards ---- */
    .tm-stats { display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px; margin-bottom: 26px; }
    .tm-stat { display: flex; align-items: center; gap: 14px; background: #fff; border: 1px solid #e5e7eb; border-radius: 14px; padding: 16px 18px; }
    .tm-stat-icon { width: 46px; height: 46px; border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .tm-stat-icon .material-symbols-outlined { font-size: 24px; }
    .tm-stat-icon.blue  { background: #dbeafe; color: #2563eb; }
    .tm-stat-icon.green { background: #dcfce7; color: #16a34a; }
    .tm-stat-icon.sky   { background: #e0f2fe; color: #0369a1; }
    .tm-stat-icon.navy  { background: #1e3a5f; color: #fff; }
    .tm-stat-label { font-size: 13px; color: #4b5563; }
    .tm-stat-value { font-size: 22px; font-weight: 800; color: #111827; line-height: 1.2; }

    /* ---- heading row ---- */
    .tm-head { display: flex; align-items: flex-end; justify-content: space-between; gap: 16px; flex-wrap: wrap; margin-bottom: 18px; }
    .tm-head h2 { margin: 0; font-size: 24px; font-weight: 800; color: #0f3a66; }
    .tm-head p { margin: 4px 0 0; font-size: 14px; color: #4b5563; }
    .tm-head-actions { display: flex; gap: 10px; }
    .tm-btn { display: inline-flex; align-items: center; justify-content: center; gap: 6px; padding: 9px 16px; border-radius: 8px; font-family: inherit;
              font-size: 13px; font-weight: 600; cursor: pointer; text-decoration: none; border: 1px solid #d1d5db; background: #fff; color: #1f2937; transition: all .15s ease; }
    .tm-btn .material-symbols-outlined { font-size: 17px; }
    .tm-btn:hover { background: #f3f4f6; }
    .tm-btn.solid { background: #0f2a4a; border-color: #0f2a4a; color: #fff; }
    .tm-btn.solid:hover { background: #0a1f38; }

    /* ---- team cards ---- */
    .tm-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 18px; }
    .tm-card { background: #fff; border: 1px solid #e5e7eb; border-radius: 16px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,.04);
               display: flex; flex-direction: column; }
    .tm-card.completed { background: #fafafa; border: 1.5px dashed #cbd5e1; box-shadow: none; }
    .tm-card.completed .tm-name, .tm-card.completed .tm-project { color: #6b7280; }

    .tm-top { display: flex; align-items: center; justify-content: space-between; margin-bottom: 4px; }
    .tm-title { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
    .tm-name { font-size: 17px; font-weight: 700; color: #111827; }
    .tm-pill { font-size: 11px; font-weight: 700; padding: 3px 10px; border-radius: 999px; }
    .tm-pill.ontrack   { background: #dcfce7; color: #166534; }
    .tm-pill.behind    { background: #fee2e2; color: #b91c1c; }
    .tm-pill.completed { background: #e5e7eb; color: #4b5563; }
    .tm-kebab { background: none; border: none; cursor: pointer; color: #6b7280; padding: 2px; border-radius: 6px; display: flex; }
    .tm-top { position: relative; }
    .tm-kebab.open { background: #f3f4f6; color: #0f2a4a; }
    .tm-menu {
        position: absolute; top: 32px; right: 0; z-index: 40; min-width: 210px; background: #fff;
        border: 1px solid #e5e7eb; border-radius: 12px; padding: 6px; box-shadow: 0 12px 30px rgba(15,23,42,.14);
    }
    .tm-menu button {
        display: flex; align-items: center; gap: 8px; width: 100%; border: none; background: none; padding: 8px 10px;
        border-radius: 8px; font-family: inherit; font-size: 13.5px; color: #1f2937; cursor: pointer; text-align: left;
    }
    .tm-menu button:hover { background: #f3f4f6; }
    .tm-menu button .material-symbols-outlined { font-size: 18px; color: #4b5563; }
    .tm-menu button.danger, .tm-menu button.danger .material-symbols-outlined { color: #dc2626; }
    .tm-menu button.danger:hover { background: #fef2f2; }
    .tm-menu-label { font-size: 10.5px; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; color: #9ca3af; padding: 8px 10px 4px; }
    .tm-menu button .tm-check { margin-left: auto; font-size: 17px; color: #16a34a !important; }
    .tm-menu .st-dot { width: 9px; height: 9px; border-radius: 50%; flex-shrink: 0; margin: 0 4px; }
    .tm-menu hr { border: none; border-top: 1px solid #f1f3f6; margin: 5px 0; }
    .tm-btn-danger { background: #dc2626; border-color: #dc2626; color: #fff; }
    .tm-btn-danger:hover { background: #b91c1c; }
    .tm-del-icon { width: 56px; height: 56px; margin: 0 auto; border-radius: 50%; background: #fee2e2; color: #dc2626; display: flex; align-items: center; justify-content: center; }
    .tm-del-icon .material-symbols-outlined { font-size: 28px; }
    .tm-demo-note { font-size: 12px; color: #92400e; background: #fffbeb; border: 1px solid #fde68a; padding: 8px 10px; border-radius: 8px; margin-bottom: 14px; }
    .tm-kebab:hover { background: #f3f4f6; }
    .tm-project { font-size: 13.5px; color: #374151; margin: 0 0 16px; }

    .tm-people { display: flex; justify-content: space-between; align-items: flex-start; gap: 16px; margin-bottom: 16px; }
    .tm-label { font-size: 10.5px; font-weight: 700; letter-spacing: .06em; color: #6b7280; text-transform: uppercase; margin-bottom: 8px; }
    .tm-leader { display: flex; align-items: center; gap: 10px; }
    .tm-leader-name { font-size: 14px; font-weight: 700; color: #111827; line-height: 1.2; }
    .tm-leader-role { font-size: 12.5px; color: #4b5563; }
    .tm-av { width: 40px; height: 40px; border-radius: 50%; color: #fff; font-size: 13px; font-weight: 700; display: flex; align-items: center; justify-content: center;
             border: 2px solid #fff; flex-shrink: 0; }
    .tm-stack { display: flex; }
    .tm-stack .tm-av { width: 32px; height: 32px; font-size: 11px; margin-left: -8px; }
    .tm-stack .tm-av:first-child { margin-left: 0; }
    .tm-stack .tm-av.more { background: #e5e7eb; color: #4b5563; }
    .tm-card.completed .tm-av { filter: grayscale(1); opacity: .7; }

    .tm-skills { margin-bottom: 16px; }
    .tm-chips { display: flex; flex-wrap: wrap; gap: 8px; }
    .tm-chip { background: #dbeafe; color: #1e3a5f; font-size: 12px; font-weight: 500; padding: 4px 12px; border-radius: 6px; }

    .tm-progress { background: #f3f4f6; border: 1px solid #e5e7eb; border-radius: 12px; padding: 14px 16px; margin-bottom: 16px; }
    .tm-progress.behind { background: #fef2f2; border-color: #ef4444; border-left-width: 4px; }
    .tm-progress.completed { background: #f3f4f6; }
    .tm-prow { display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; font-size: 14px; font-weight: 700; color: #111827; }
    .tm-progress.behind .tm-prow { color: #b91c1c; }
    .tm-bar { height: 7px; background: #d1d5db; border-radius: 999px; overflow: hidden; margin-bottom: 10px; }
    .tm-bar span { display: block; height: 100%; border-radius: 999px; background: #0f3a66; }
    .tm-progress.behind .tm-bar span { background: #b91c1c; }
    .tm-progress.completed .tm-bar span { background: #166534; }
    .tm-meta { display: flex; justify-content: space-between; align-items: center; font-size: 12.5px; color: #4b5563; }
    .tm-meta .left, .tm-meta .right { display: inline-flex; align-items: center; gap: 5px; }
    .tm-meta .material-symbols-outlined { font-size: 16px; }
    .tm-meta .right { font-weight: 600; }
    .tm-meta .right.ok   { color: #166534; }
    .tm-meta .right.warn { color: #dc2626; }
    .tm-meta .right.late { color: #b91c1c; }

    .tm-actions { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-top: auto; }   /* always sit at the bottom of the card */
    .tm-actions.single { grid-template-columns: 1fr; }
    .tm-actions .tm-btn { padding: 9px 10px; }
    .tm-actions .tm-btn.report { background: #d1d5db; border-color: #d1d5db; color: #374151; }
    .tm-actions .tm-btn.report:hover { background: #c4c9d1; }

    .tm-pill.new { background: #dbeafe; color: #1d4ed8; }

    /* ---- filter ---- */
    .tm-filter-wrap { position: relative; }
    .tm-filter-count {
        min-width: 18px; height: 18px; padding: 0 5px; border-radius: 999px; background: #0f2a4a; color: #fff;
        font-size: 11px; font-weight: 700; display: inline-flex; align-items: center; justify-content: center;
    }
    .tm-filter-count[hidden] { display: none; }
    .tm-filter-panel {
        position: absolute; right: 0; top: calc(100% + 8px); z-index: 30; width: 320px;
        background: #fff; border: 1px solid #e5e7eb; border-radius: 14px; padding: 16px;
        box-shadow: 0 12px 30px rgba(15,23,42,.12);
    }
    .tm-filter-panel[hidden] { display: none; }
    .tm-filter-row + .tm-filter-row { margin-top: 14px; }
    .tm-f-label { display: block; font-size: 11.5px; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; color: #6b7280; margin-bottom: 6px; }
    .tm-input {
        width: 100%; box-sizing: border-box; padding: 10px 12px; border: 1px solid #e5e7eb; border-radius: 10px;
        background: #f9fafb; font-family: inherit; font-size: 14px; color: #111827; outline: none;
    }
    .tm-input:focus { border-color: #93c5fd; background: #fff; box-shadow: 0 0 0 3px rgba(59,130,246,.12); }
    .tm-input:disabled { color: #9ca3af; cursor: not-allowed; }
    .tm-seg { display: grid; grid-template-columns: repeat(4, 1fr); gap: 4px; background: #f3f4f6; padding: 4px; border-radius: 10px; }
    .tm-seg button {
        border: none; background: transparent; padding: 7px 4px; border-radius: 7px; font-family: inherit;
        font-size: 12.5px; font-weight: 600; color: #4b5563; cursor: pointer;
    }
    .tm-seg button.on { background: #fff; color: #0f2a4a; box-shadow: 0 1px 3px rgba(0,0,0,.1); }
    .tm-filter-foot { display: flex; justify-content: space-between; align-items: center; margin-top: 16px; padding-top: 12px; border-top: 1px solid #f1f3f6; }
    .tm-link { background: none; border: none; padding: 0; font-family: inherit; font-size: 13px; font-weight: 600; color: #0f3a66; cursor: pointer; }
    .tm-link:hover { text-decoration: underline; }
    .tm-filter-result { font-size: 12.5px; color: #6b7280; }

    .tm-empty { text-align: center; padding: 50px 20px; background: #fff; border: 1px dashed #d1d5db; border-radius: 16px; color: #6b7280; }
    .tm-empty[hidden] { display: none; }
    .tm-empty > .material-symbols-outlined { font-size: 40px; color: #9ca3af; }
    .tm-empty-title { font-size: 15px; font-weight: 600; color: #374151; margin: 8px 0 14px; }
    .tm-card[hidden] { display: none; }

    /* ---- create team modal ---- */
    .tm-overlay {
        position: fixed; inset: 0; z-index: 5000; background: rgba(15,23,42,.45);
        -webkit-backdrop-filter: blur(4px); backdrop-filter: blur(4px);   /* whole screen, sidebar + header too */
        display: none; align-items: center; justify-content: center; padding: 16px;
    }
    .tm-overlay.open { display: flex; }
    .tm-modal {
        width: min(620px, 100%); max-height: 92vh; display: flex; flex-direction: column; overflow: hidden;
        background: #fff; border-radius: 16px; box-shadow: 0 24px 48px rgba(15,23,42,.22);
    }
    .tm-modal-head { display: flex; gap: 14px; align-items: flex-start; padding: 20px 22px 16px; border-bottom: 1px solid #e5e7eb; }
    .tm-modal-icon { width: 44px; height: 44px; border-radius: 10px; flex-shrink: 0; background: #e8f0fe; color: #2563eb; display: flex; align-items: center; justify-content: center; }
    .tm-modal-head h3 { margin: 0; font-size: 19px; font-weight: 800; color: #0f3a66; }
    .tm-modal-head p { margin: 3px 0 0; font-size: 13px; color: #6b7280; }
    .tm-x { margin-left: auto; background: none; border: none; cursor: pointer; color: #6b7280; width: 34px; height: 34px; border-radius: 8px; display: flex; align-items: center; justify-content: center; }
    .tm-x:hover { background: #f3f4f6; color: #0f2a4a; }
    .tm-modal-body { padding: 18px 22px; overflow-y: auto; }
    .tm-field { margin-bottom: 16px; }
    .tm-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 0 14px; }
    .tm-err { font-size: 12.5px; color: #dc2626; margin-top: 5px; min-height: 0; }
    .tm-err:empty { display: none; }
    .tm-input.invalid, .tm-members.invalid { border-color: #ef4444; box-shadow: 0 0 0 3px rgba(239,68,68,.1); }

    .tm-project-info { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 8px; }
    .tm-project-info[hidden] { display: none; }
    .tm-info-chip { display: inline-flex; align-items: center; gap: 4px; font-size: 12px; color: #374151; background: #f3f4f6; padding: 4px 10px; border-radius: 6px; }
    .tm-info-chip .material-symbols-outlined { font-size: 15px; color: #0f3a66; }
    .tm-info-chip.skill { background: #dbeafe; color: #1e3a5f; font-weight: 500; }

    .tm-f-label-row { display: flex; justify-content: space-between; align-items: baseline; }
    .tm-picked { font-size: 12px; font-weight: 600; color: #0f3a66; }
    .tm-members {
        border: 1px solid #e5e7eb; border-radius: 12px; max-height: 230px; overflow-y: auto; background: #fff;
    }
    .tm-members-empty { padding: 18px; text-align: center; font-size: 13px; color: #9ca3af; }
    .tm-member {
        display: flex; align-items: center; gap: 12px; padding: 10px 14px; cursor: pointer; border-top: 1px solid #f1f3f6;
    }
    .tm-member:first-child { border-top: none; }
    .tm-member:hover { background: #f9fafb; }
    .tm-member input { width: 17px; height: 17px; accent-color: #0f2a4a; margin: 0; flex-shrink: 0; }
    .tm-member.disabled { opacity: .45; cursor: not-allowed; }
    .tm-member .tm-av { width: 34px; height: 34px; font-size: 12px; border: none; }
    .tm-member-info { flex: 1; min-width: 0; }
    .tm-member-name { font-size: 14px; font-weight: 600; color: #111827; }
    .tm-member-sub { font-size: 12px; color: #6b7280; }
    .tm-src { font-size: 11px; font-weight: 700; padding: 2px 8px; border-radius: 999px; white-space: nowrap; }
    .tm-src.assigned { background: #dcfce7; color: #166534; }
    .tm-src.applied  { background: #fef3c7; color: #92400e; }

    .tm-note { display: flex; gap: 8px; align-items: flex-start; padding: 12px 14px; border-radius: 10px; font-size: 13.5px; line-height: 1.5; }
    .tm-note.warn { background: #fffbeb; color: #92400e; border: 1px solid #fde68a; }
    .tm-note .material-symbols-outlined { font-size: 19px; }

    .tm-modal-foot { display: flex; justify-content: flex-end; gap: 10px; padding: 14px 22px 18px; border-top: 1px solid #e5e7eb; }

    /* ---- create / edit team: members + roles, skills tags ---- */
    .tm-modal-wide { width: min(700px, 100%); }
    .tm-step { display: flex; align-items: center; gap: 8px; font-size: 13px; font-weight: 700; color: #0f3a66; margin: 4px 0 12px; }
    .tm-step span { width: 22px; height: 22px; border-radius: 50%; background: #0f2a4a; color: #fff; font-size: 12px; display: inline-flex; align-items: center; justify-content: center; }
    .tm-step:not(:first-child) { margin-top: 10px; padding-top: 16px; border-top: 1px solid #f1f3f6; }
    .tm-hint { font-size: 12px; color: #6b7280; margin-top: 6px; }
    .tm-project-info { margin: -6px 0 16px; }

    .tm-mrow { border-top: 1px solid #f1f3f6; }
    .tm-mrow:first-child { border-top: none; }
    .tm-mrow.on { background: #f8fbff; }
    .tm-mrow .tm-member { border-top: none; }
    .tm-mskills { display: flex; flex-wrap: wrap; gap: 4px; margin-top: 4px; }
    .tm-mskills span { font-size: 10.5px; background: #eef2f7; color: #334155; padding: 1px 7px; border-radius: 5px; }
    .tm-src.locked { background: #f3f4f6; color: #6b7280; }
    .tm-role-row { display: flex; align-items: center; gap: 8px; padding: 0 14px 12px 43px; }
    .tm-role-row[hidden] { display: none; }
    .tm-role-row .material-symbols-outlined { font-size: 18px; color: #0f3a66; }
    .tm-role-row .tm-input { padding: 7px 10px; font-size: 13px; background: #fff; }
    .tm-lead-tag { display: inline-flex; align-items: center; gap: 3px; font-size: 10.5px; font-weight: 700; padding: 2px 8px; border-radius: 999px; background: #0f2a4a; color: #fff; white-space: nowrap; }
    .tm-lead-tag .material-symbols-outlined { font-size: 13px; color: #fff; }

    .tm-tags { display: flex; flex-wrap: wrap; align-items: center; gap: 6px; min-height: 44px; box-sizing: border-box; padding: 6px 8px;
               border: 1px solid #e5e7eb; border-radius: 10px; background: #f9fafb; cursor: text; }
    .tm-tags:focus-within { border-color: #93c5fd; background: #fff; box-shadow: 0 0 0 3px rgba(59,130,246,.12); }
    .tm-tags.invalid { border-color: #ef4444; box-shadow: 0 0 0 3px rgba(239,68,68,.1); }
    .tm-tags input { flex: 1; min-width: 140px; border: none; background: transparent; outline: none; font-family: inherit; font-size: 14px; padding: 4px; color: #111827; }
    .tm-tag { display: inline-flex; align-items: center; gap: 4px; background: #dbeafe; color: #1e3a5f; font-size: 12.5px; font-weight: 500; padding: 4px 6px 4px 10px; border-radius: 6px; }
    .tm-tag button { border: none; background: none; padding: 0; cursor: pointer; color: #1e3a5f; display: flex; border-radius: 4px; }
    .tm-tag button:hover { background: rgba(30,58,95,.12); }
    .tm-tag .material-symbols-outlined { font-size: 15px; }
    .tm-suggest { display: flex; flex-wrap: wrap; align-items: center; gap: 6px; margin-top: 8px; font-size: 12px; color: #6b7280; }
    .tm-suggest:empty { display: none; }
    .tm-sug { border: 1px dashed #93c5fd; background: #fff; color: #1e3a5f; font-family: inherit; font-size: 12px; padding: 3px 9px; border-radius: 6px; cursor: pointer; }
    .tm-sug:hover { background: #eff6ff; }

    /* ---- team details (View) ---- */
    .tv-modal { width: min(940px, 100%); }
    .tv-head { display: flex; align-items: flex-start; gap: 12px; padding: 20px 24px 16px; border-bottom: 1px solid #e5e7eb; }
    .tv-head-main { flex: 1; min-width: 0; }
    .tv-kicker { font-size: 12px; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; color: #6b7280; }
    .tv-title-row { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; margin-top: 4px; }
    .tv-title-row h3 { margin: 0; font-size: 22px; font-weight: 800; color: #0f3a66; }
    .tv-project { font-size: 14px; color: #374151; margin-top: 4px; }
    .tv-body { padding: 18px 24px 22px; overflow-y: auto; background: #f5f7fb; }

    .tv-stats { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; margin-bottom: 14px; }
    .tv-stat { display: flex; align-items: center; gap: 12px; background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; padding: 14px; }
    .tv-stat-icon { width: 40px; height: 40px; border-radius: 10px; flex-shrink: 0; display: flex; align-items: center; justify-content: center; }
    .tv-stat-icon.blue { background: #dbeafe; color: #2563eb; } .tv-stat-icon.green { background: #dcfce7; color: #16a34a; }
    .tv-stat-icon.sky { background: #e0f2fe; color: #0369a1; }  .tv-stat-icon.navy { background: #1e3a5f; color: #fff; }
    .tv-stat-label { font-size: 11px; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; color: #6b7280; }
    .tv-stat-value { font-size: 17px; font-weight: 800; color: #111827; line-height: 1.25; }
    .tv-stat-sub { font-size: 12px; color: #6b7280; }
    .tv-stat-sub.warn { color: #dc2626; font-weight: 600; } .tv-stat-sub.late { color: #b91c1c; font-weight: 600; } .tv-stat-sub.ok { color: #166534; font-weight: 600; }

    .tv-card { background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; padding: 16px 18px; margin-bottom: 14px; }
    .tv-card h4 { display: flex; align-items: center; gap: 6px; margin: 0 0 12px; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: #6b7280; }
    .tv-card h4 .material-symbols-outlined { font-size: 18px; color: #0f3a66; }
    .tv-card h4 .tv-count { margin-left: auto; font-size: 12px; color: #0f3a66; text-transform: none; letter-spacing: 0; }

    .tv-overall-row { display: flex; justify-content: space-between; font-size: 14px; font-weight: 700; color: #111827; margin-bottom: 8px; }
    .tv-bar { height: 8px; background: #e5e7eb; border-radius: 999px; overflow: hidden; }
    .tv-bar span { display: block; height: 100%; border-radius: 999px; background: #0f3a66; }
    .tv-bar.behind span { background: #b91c1c; } .tv-bar.completed span { background: #166534; }

    .tv-info-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px 18px; margin-top: 14px; }
    .tv-info span { display: block; font-size: 11.5px; color: #6b7280; }
    .tv-info strong { font-size: 13.5px; color: #111827; font-weight: 600; }

    .tv-members { display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px; }
    .tv-member { border: 1px solid #e5e7eb; border-radius: 12px; padding: 14px; display: flex; flex-direction: column; gap: 10px; }
    .tv-member.leader { border-color: #93c5fd; background: #f8fbff; }
    .tv-m-top { display: flex; gap: 12px; align-items: flex-start; }
    .tv-m-top .tm-av { width: 44px; height: 44px; font-size: 14px; border: none; }
    .tv-m-info { flex: 1; min-width: 0; }
    .tv-m-name { display: flex; align-items: center; gap: 6px; flex-wrap: wrap; font-size: 15px; font-weight: 700; color: #111827; }
    .tv-leader-tag { display: inline-flex; align-items: center; gap: 3px; font-size: 10.5px; font-weight: 700; padding: 2px 8px; border-radius: 999px; background: #0f2a4a; color: #fff; }
    .tv-leader-tag .material-symbols-outlined { font-size: 13px; }
    .tv-m-role { font-size: 13px; color: #0f3a66; font-weight: 600; }
    .tv-m-line { display: flex; align-items: center; gap: 5px; font-size: 12.5px; color: #6b7280; margin-top: 2px; word-break: break-all; }
    .tv-m-line .material-symbols-outlined { font-size: 15px; flex-shrink: 0; }
    .tv-m-line a { color: #0f3a66; text-decoration: none; } .tv-m-line a:hover { text-decoration: underline; }
    .tv-m-skills { display: flex; flex-wrap: wrap; gap: 6px; }
    .tv-m-skills span { background: #dbeafe; color: #1e3a5f; font-size: 11.5px; font-weight: 500; padding: 3px 9px; border-radius: 6px; }
    .tv-m-prog { display: flex; align-items: center; gap: 10px; font-size: 12px; color: #374151; }
    .tv-m-prog .tv-bar { flex: 1; height: 6px; }
    .tv-work { font-size: 11px; font-weight: 700; padding: 2px 8px; border-radius: 999px; white-space: nowrap; }
    .tv-work.progress { background: #ffedd5; color: #9a3412; } .tv-work.done { background: #dcfce7; color: #166534; } .tv-work.none { background: #f3f4f6; color: #6b7280; }

    .tv-two { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
    .tv-two .tv-card { margin-bottom: 0; }
    .tv-miles { list-style: none; margin: 0; padding: 0; }
    .tv-miles li { display: flex; align-items: center; gap: 10px; padding: 7px 0; font-size: 13.5px; color: #374151; border-top: 1px solid #f1f3f6; }
    .tv-miles li:first-child { border-top: none; }
    .tv-miles .material-symbols-outlined { font-size: 20px; color: #cbd5e1; }
    .tv-miles li.done .material-symbols-outlined { color: #16a34a; }
    .tv-miles li.done span:last-child { color: #6b7280; }
    .tv-miles li.current { font-weight: 700; color: #0f3a66; }
    .tv-miles li.current .material-symbols-outlined { color: #0f3a66; }
    .tv-act { list-style: none; margin: 0; padding: 0; }
    .tv-act li { display: flex; gap: 10px; padding: 7px 0; border-top: 1px solid #f1f3f6; }
    .tv-act li:first-child { border-top: none; }
    .tv-act-dot { width: 8px; height: 8px; border-radius: 50%; background: #0f3a66; margin-top: 6px; flex-shrink: 0; }
    .tv-act-text { font-size: 13.5px; color: #374151; }
    .tv-act-when { font-size: 12px; color: #9ca3af; }
    .tv-empty { font-size: 13px; color: #9ca3af; font-style: italic; }
    .tv-chips { display: flex; flex-wrap: wrap; gap: 8px; }

    /* ---- Delay Review ---- */
    .tr-alert { display: flex; gap: 10px; align-items: flex-start; background: #fef2f2; border: 1px solid #fecaca; border-left: 4px solid #dc2626;
                border-radius: 12px; padding: 12px 14px; margin-bottom: 14px; color: #7f1d1d; font-size: 13.5px; line-height: 1.5; }
    .tr-alert .material-symbols-outlined { color: #dc2626; }
    .tr-alert strong { color: #991b1b; }
    .tr-compare-row { display: grid; grid-template-columns: 90px 1fr 48px; align-items: center; gap: 10px; font-size: 13px; color: #374151; }
    .tr-compare-row + .tr-compare-row { margin-top: 10px; }
    .tr-compare-row strong { text-align: right; color: #111827; }
    .tr-bar { height: 10px; border-radius: 999px; background: #e5e7eb; overflow: hidden; }
    .tr-bar span { display: block; height: 100%; border-radius: 999px; }
    .tr-bar.expected span { background: repeating-linear-gradient(45deg, #94a3b8 0 6px, #cbd5e1 6px 12px); }
    .tr-bar.actual span { background: #dc2626; }
    .tr-gap-note { margin-top: 10px; font-size: 12.5px; color: #6b7280; }
    .tr-gap-note b { color: #b91c1c; }

    .tr-task { display: flex; align-items: center; gap: 10px; padding: 9px 0; border-top: 1px solid #f1f3f6; }
    .tr-task:first-child { border-top: none; }
    .tr-task .tm-av { width: 28px; height: 28px; font-size: 10px; border: none; }
    .tr-task-info { flex: 1; min-width: 0; }
    .tr-task-title { font-size: 13.5px; font-weight: 600; color: #111827; }
    .tr-task-sub { font-size: 12px; color: #6b7280; }
    .tr-flag { font-size: 11px; font-weight: 700; padding: 2px 8px; border-radius: 999px; white-space: nowrap; }
    .tr-flag.late { background: #fee2e2; color: #b91c1c; } .tr-flag.risk { background: #fef3c7; color: #92400e; }

    .tr-mem { display: flex; align-items: center; gap: 10px; padding: 8px 0; border-top: 1px solid #f1f3f6; }
    .tr-mem:first-child { border-top: none; }
    .tr-mem .tm-av { width: 28px; height: 28px; font-size: 10px; border: none; }
    .tr-mem-name { font-size: 13px; font-weight: 600; color: #111827; width: 150px; flex-shrink: 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .tr-mem .tv-bar { flex: 1; height: 6px; }
    .tr-mem .tv-bar.low span { background: #dc2626; }
    .tr-mem-pct { font-size: 12px; font-weight: 700; color: #374151; width: 36px; text-align: right; }
    .tr-support { font-size: 10.5px; font-weight: 700; color: #b91c1c; background: #fee2e2; padding: 2px 7px; border-radius: 999px; white-space: nowrap; }

    .tr-tabs { display: inline-grid; grid-template-columns: 1fr 1fr; gap: 4px; background: #f3f4f6; padding: 4px; border-radius: 10px; margin-bottom: 14px; }
    .tr-tabs button { border: none; background: transparent; padding: 8px 14px; border-radius: 7px; font-family: inherit; font-size: 13px; font-weight: 600; color: #4b5563; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; }
    .tr-tabs button .material-symbols-outlined { font-size: 17px; }
    .tr-tabs button.on { background: #fff; color: #0f2a4a; box-shadow: 0 1px 3px rgba(0,0,0,.1); }
    .tr-pane[hidden] { display: none; }
    .tr-pane textarea { width: 100%; box-sizing: border-box; min-height: 110px; resize: vertical; padding: 10px 12px; border: 1px solid #e5e7eb; border-radius: 10px;
                        background: #f9fafb; font-family: inherit; font-size: 13.5px; line-height: 1.55; color: #111827; outline: none; }
    .tr-pane textarea:focus { border-color: #93c5fd; background: #fff; }
    .tr-pane-foot { display: flex; justify-content: space-between; align-items: center; gap: 10px; margin-top: 10px; flex-wrap: wrap; }
    .tr-check { display: inline-flex; align-items: center; gap: 8px; font-size: 13px; color: #374151; cursor: pointer; }
    .tr-check input { width: 16px; height: 16px; accent-color: #0f2a4a; }
    .tr-note { font-size: 12px; color: #6b7280; }

    @media (max-width: 820px) {
        .tv-stats { grid-template-columns: repeat(2, 1fr); }
        .tv-members, .tv-two { grid-template-columns: 1fr; }
        .tv-info-grid { grid-template-columns: repeat(2, 1fr); }
    }

    /* =========================================================
       TEAM WORKSPACE (Kanban + Chat)
    ========================================================= */
    .tw-page { padding: 14px 28px 28px; }
    .tw-page[hidden], #tmListView[hidden] { display: none; }
    .tw-topbar { display: flex; justify-content: space-between; align-items: center; gap: 12px; flex-wrap: wrap; margin-bottom: 16px; }
    .tw-back { display: inline-flex; align-items: center; gap: 6px; padding: 9px 16px; border-radius: 8px; border: 1px solid #0f2a4a;
               background: #fff; color: #0f2a4a; font-family: inherit; font-size: 14px; font-weight: 500; cursor: pointer; }
    .tw-back:hover { background: #f1f5f9; }
    .tw-back .material-symbols-outlined { font-size: 18px; }
    .tw-crumbs { display: flex; align-items: center; gap: 4px; font-size: 13px; color: #9ca3af; }
    .tw-crumbs .material-symbols-outlined { font-size: 16px; }
    .tw-crumbs strong { color: #374151; font-weight: 600; }

    .tw-hero { display: flex; justify-content: space-between; align-items: flex-start; gap: 20px; flex-wrap: wrap; margin-bottom: 18px; }
    .tw-kicker { display: inline-flex; align-items: center; gap: 6px; font-size: 12px; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; color: #0f3a66; }
    .tw-kicker .material-symbols-outlined { font-size: 17px; }
    .tw-hero h2 { margin: 4px 0 8px; font-size: 26px; font-weight: 800; color: #0f3a66; }
    .tw-team-line { display: flex; align-items: center; gap: 10px; font-size: 13.5px; color: #4b5563; }
    .tw-avs { display: flex; }
    .tw-avs .tm-av { width: 30px; height: 30px; font-size: 11px; margin-left: -8px; }
    .tw-avs .tm-av:first-child { margin-left: 0; }
    .tw-progress-card { width: 280px; background: #fff; border: 1px solid #e5e7eb; border-radius: 14px; padding: 14px 16px; }
    .tw-pc-row { display: flex; justify-content: space-between; font-size: 13.5px; font-weight: 700; color: #111827; }
    .tw-pc-row strong { color: #0f3a66; }
    .tw-pc-row.small { font-size: 11px; font-weight: 700; letter-spacing: .05em; text-transform: uppercase; color: #6b7280; margin-top: 8px; }
    .tw-pc-bar { height: 7px; background: #e5e7eb; border-radius: 999px; overflow: hidden; margin-top: 8px; }
    .tw-pc-bar span { display: block; height: 100%; background: #0f3a66; border-radius: 999px; transition: width .3s ease; }

    .tw-layout { display: grid; grid-template-columns: minmax(0, 1fr) 340px; gap: 18px; align-items: start; }
    .tw-board-head { display: flex; justify-content: space-between; align-items: center; gap: 10px; flex-wrap: wrap; margin-bottom: 12px; }
    .tw-board-head h3 { display: flex; align-items: center; gap: 6px; margin: 0; font-size: 18px; font-weight: 800; color: #0f3a66; }
    .tw-board-actions { display: flex; gap: 8px; }
    .tw-select { padding: 8px 10px; border: 1px solid #d1d5db; border-radius: 8px; background: #fff; font-family: inherit; font-size: 13px; color: #1f2937; }

    .tw-board { display: grid; grid-template-columns: repeat(4, minmax(170px, 1fr)); gap: 12px; overflow-x: auto; padding-bottom: 4px; }
    .tw-col { background: #eef1f6; border-radius: 12px; padding: 10px; min-height: 220px; transition: background .15s ease, outline .15s ease; }
    .tw-col.drop { background: #e0e9f7; outline: 2px dashed #93c5fd; }
    .tw-col-head { display: flex; justify-content: space-between; align-items: center; padding: 2px 4px 10px; font-size: 12px; font-weight: 700; letter-spacing: .05em; text-transform: uppercase; color: #374151; }
    .tw-col-head .dot { width: 8px; height: 8px; border-radius: 50%; display: inline-block; margin-right: 6px; }
    .tw-col-count { background: #fff; border-radius: 999px; padding: 1px 8px; font-size: 11px; color: #4b5563; }
    .tw-col-empty { font-size: 12.5px; color: #9ca3af; text-align: center; padding: 16px 6px; border: 1px dashed #d1d5db; border-radius: 10px; }

    .tw-card { position: relative; background: #fff; border: 1px solid #e5e7eb; border-radius: 10px; padding: 12px; margin-bottom: 10px; cursor: grab; box-shadow: 0 1px 2px rgba(0,0,0,.04); }
    .tw-card:active { cursor: grabbing; }
    .tw-card.dragging { opacity: .5; }
    .tw-card.progress { border-left: 3px solid #ea580c; }
    .tw-card.done .tw-card-title { text-decoration: line-through; color: #6b7280; }
    .tw-tag { display: inline-block; font-size: 10px; font-weight: 700; letter-spacing: .05em; text-transform: uppercase; padding: 3px 8px; border-radius: 5px; }
    .tw-tag.Development { background: #dbeafe; color: #1d4ed8; } .tw-tag.Design { background: #fce7f3; color: #be185d; }
    .tw-tag.Research { background: #e0f2fe; color: #0369a1; }   .tw-tag.Testing { background: #fef3c7; color: #92400e; }
    .tw-tag.Documentation { background: #ede9fe; color: #6d28d9; }
    .tw-card-title { font-size: 14px; font-weight: 600; color: #111827; margin: 8px 0; line-height: 1.4; }
    .tw-card-bar { height: 4px; background: #e5e7eb; border-radius: 999px; overflow: hidden; margin-bottom: 8px; }
    .tw-card-bar span { display: block; height: 100%; background: #ea580c; }
    .tw-card-foot { display: flex; justify-content: space-between; align-items: center; font-size: 11.5px; color: #6b7280; }
    .tw-card-foot .left { display: inline-flex; align-items: center; gap: 4px; }
    .tw-card-foot .material-symbols-outlined { font-size: 15px; }
    .tw-card-foot .late { color: #dc2626; font-weight: 600; }
    .tw-card .tm-av { width: 24px; height: 24px; font-size: 9.5px; border: none; }
    .tw-kebab { position: absolute; top: 8px; right: 6px; background: none; border: none; cursor: pointer; color: #6b7280; border-radius: 6px; padding: 0 4px; font-size: 18px; line-height: 1; }
    .tw-kebab:hover { background: #f3f4f6; }
    .tw-menu { position: absolute; top: 30px; right: 6px; z-index: 5; background: #fff; border: 1px solid #e5e7eb; border-radius: 10px; box-shadow: 0 10px 24px rgba(15,23,42,.14); padding: 6px; min-width: 150px; }
    .tw-menu button { display: flex; align-items: center; gap: 6px; width: 100%; border: none; background: none; padding: 7px 10px; border-radius: 6px; font-family: inherit; font-size: 13px; color: #1f2937; cursor: pointer; text-align: left; }
    .tw-menu button:hover { background: #f3f4f6; }
    .tw-menu button.danger { color: #dc2626; }
    .tw-menu .material-symbols-outlined { font-size: 16px; }
    .tw-menu hr { border: none; border-top: 1px solid #f1f3f6; margin: 4px 0; }
    .tw-hint { display: flex; align-items: center; gap: 6px; font-size: 12px; color: #9ca3af; margin: 10px 0 0; }
    .tw-hint .material-symbols-outlined { font-size: 16px; }

    .tw-chat { background: #fff; border: 1px solid #e5e7eb; border-radius: 14px; display: flex; flex-direction: column; height: 600px; position: sticky; top: 16px; transition: box-shadow .3s ease; }
    .tw-chat.focus { box-shadow: 0 0 0 3px rgba(59,130,246,.25); }
    .tw-chat-head { display: flex; justify-content: space-between; align-items: center; padding: 14px 16px; border-bottom: 1px solid #e5e7eb; }
    .tw-chat-head h3 { margin: 0; font-size: 16px; font-weight: 800; color: #0f3a66; }
    .tw-msgs { flex: 1; overflow-y: auto; padding: 14px 16px; display: flex; flex-direction: column; gap: 14px; background: #fafbfc; }
    .tw-msg { display: flex; flex-direction: column; max-width: 85%; }
    .tw-msg-meta { display: flex; align-items: center; gap: 6px; font-size: 12px; margin-bottom: 4px; }
    .tw-msg-meta strong { color: #111827; } .tw-msg-meta span { color: #9ca3af; }
    .tw-msg .tm-av { width: 22px; height: 22px; font-size: 9px; border: none; }
    .tw-bubble { padding: 10px 12px; border-radius: 4px 12px 12px 12px; background: #eef1f6; color: #1f2937; font-size: 13.5px; line-height: 1.5; white-space: pre-wrap; word-wrap: break-word; }
    .tw-msg.me { align-self: flex-end; align-items: flex-end; }
    .tw-msg.me .tw-bubble { background: #0f2a4a; color: #fff; border-radius: 12px 4px 12px 12px; }
    .tw-sys { align-self: center; font-size: 10.5px; font-weight: 700; letter-spacing: .05em; text-transform: uppercase; color: #6b7280; background: #eef1f6; padding: 4px 10px; border-radius: 999px; }
    .tw-send { display: flex; gap: 8px; padding: 12px; border-top: 1px solid #e5e7eb; }
    .tw-send input { flex: 1; padding: 10px 12px; border: 1px solid #e5e7eb; border-radius: 10px; background: #f9fafb; font-family: inherit; font-size: 13.5px; outline: none; }
    .tw-send input:focus { border-color: #93c5fd; background: #fff; }
    .tw-send button { width: 40px; border: none; border-radius: 10px; background: #0f2a4a; color: #fff; cursor: pointer; display: flex; align-items: center; justify-content: center; }
    .tw-send button:hover { background: #0a1f38; }
    .tw-send .material-symbols-outlined { font-size: 19px; }

    /* Task view = board full width, no chat.  Chat view = board + chat side panel */
    .tw-layout.tasks-only { grid-template-columns: 1fr; }
    .tw-layout.tasks-only .tw-chat { display: none; }
    #twChatToggle.on { background: #0f2a4a; border-color: #0f2a4a; color: #fff; }

    /* Shared assets */
    .tw-assets { margin-top: 24px; }
    .tw-sec-title { display: flex; align-items: center; gap: 6px; margin: 0 0 12px; font-size: 18px; font-weight: 800; color: #0f3a66; }
    .tw-assets-grid { display: grid; grid-template-columns: 1.3fr 1fr; gap: 14px; }
    .tw-files { background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; overflow: hidden; }
    .tw-files-head { display: flex; justify-content: space-between; align-items: center; padding: 12px 16px; border-bottom: 1px solid #e5e7eb; font-size: 13px; font-weight: 700; color: #111827; }
    .tw-files-count { font-size: 12px; font-weight: 600; color: #0f3a66; }
    .tw-file { display: flex; align-items: center; gap: 12px; padding: 11px 16px; border-top: 1px solid #f1f3f6; }
    .tw-file:first-child { border-top: none; }
    .tw-file-icon { width: 38px; height: 38px; border-radius: 9px; flex-shrink: 0; display: flex; align-items: center; justify-content: center; }
    .tw-file-icon.pdf { background: #fee2e2; color: #dc2626; } .tw-file-icon.img { background: #ede9fe; color: #7c3aed; }
    .tw-file-icon.doc { background: #dbeafe; color: #2563eb; } .tw-file-icon.vid { background: #fce7f3; color: #be185d; }
    .tw-file-icon.zip { background: #fef3c7; color: #b45309; } .tw-file-icon.other { background: #f3f4f6; color: #4b5563; }
    .tw-file-info { flex: 1; min-width: 0; }
    .tw-file-name { font-size: 13.5px; font-weight: 600; color: #111827; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .tw-file-meta { font-size: 11.5px; color: #6b7280; }
    .tw-file-btn { width: 32px; height: 32px; border-radius: 8px; border: none; background: none; cursor: pointer; color: #4b5563; display: flex; align-items: center; justify-content: center; }
    .tw-file-btn:hover { background: #f3f4f6; color: #0f2a4a; }
    .tw-file-btn.danger:hover { background: #fee2e2; color: #dc2626; }
    .tw-file-btn:disabled { opacity: .35; cursor: not-allowed; background: none; }
    .tw-file-btn .material-symbols-outlined { font-size: 19px; }
    .tw-files-empty { padding: 20px; text-align: center; font-size: 13px; color: #9ca3af; }
    .tw-drop {
        display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 6px; text-align: center;
        border: 2px dashed #cbd5e1; border-radius: 12px; background: #fff; padding: 24px 18px; cursor: pointer; min-height: 170px;
        transition: border-color .15s ease, background .15s ease;
    }
    .tw-drop:hover, .tw-drop.over { border-color: #0f3a66; background: #f5f8fd; }
    .tw-drop .material-symbols-outlined { font-size: 34px; color: #0f3a66; }
    .tw-drop strong { font-size: 15px; color: #111827; }
    .tw-drop span:last-child { font-size: 12.5px; color: #6b7280; max-width: 240px; }
    .tw-upload-err { font-size: 12.5px; color: #dc2626; margin-top: 8px; }
    .tw-upload-err:empty { display: none; }

    @media (max-width: 1150px) { .tw-layout { grid-template-columns: 1fr; } .tw-chat { position: static; height: 520px; } }
    @media (max-width: 760px)  { .tw-assets-grid { grid-template-columns: 1fr; } }
    @media (max-width: 600px)  { .tw-page { padding: 12px 16px 24px; } .tw-progress-card { width: 100%; } .tw-board { grid-template-columns: repeat(4, 240px); } }

    @media (max-width: 1100px) { .tm-stats { grid-template-columns: repeat(2, 1fr); } }
    @media (max-width: 900px)  { .tm-grid { grid-template-columns: 1fr; } }
    @media (max-width: 600px)  { .tm-wrap { padding: 12px 16px 24px; } .tm-stats { grid-template-columns: 1fr; } .tm-people { flex-direction: column; }
                                 .tm-grid-2 { grid-template-columns: 1fr; } .tm-filter-panel { width: min(320px, 86vw); }
                                 .tm-head-actions { width: 100%; } .tm-head-actions > * { flex: 1; } }
</style>

<main class="content">
    <div class="tm-wrap" id="tmListView">

        <!-- Summary cards -->
        <div class="tm-stats">
            <?php foreach ($summary as $s): ?>
                <div class="tm-stat">
                    <div class="tm-stat-icon <?= $s['class'] ?>"><span class="material-symbols-outlined"><?= $s['icon'] ?></span></div>
                    <div>
                        <div class="tm-stat-label"><?= htmlspecialchars($s['label']) ?></div>
                        <div class="tm-stat-value"><?= (int)$s['value'] ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Heading + actions -->
        <div class="tm-head">
            <div>
                <h2>Active Teams Overview</h2>
                <p>Manage and monitor student collaborative projects.</p>
            </div>
            <div class="tm-head-actions">
                <div class="tm-filter-wrap">
                    <button type="button" class="tm-btn" id="tmFilterBtn" aria-expanded="false">
                        <span class="material-symbols-outlined">filter_list</span>Filter
                        <span class="tm-filter-count" id="tmFilterCount" hidden>0</span>
                    </button>

                    <div class="tm-filter-panel" id="tmFilterPanel" hidden>
                        <div class="tm-filter-row">
                            <label class="tm-f-label" for="tmFilterSearch">Search</label>
                            <input type="text" id="tmFilterSearch" class="tm-input" placeholder="Team, project, leader or member">
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

                <button type="button" class="tm-btn solid" id="tmCreateBtn"><span class="material-symbols-outlined">add</span>Create New Team</button>
            </div>
        </div>

        <!-- Team cards -->
        <div class="tm-grid">
            <?php $tmOrder = 0; foreach ($teams as $ti => $t): ?>
                <?php
                    $searchText = strtolower($t['name'] . ' ' . $t['project'] . ' ' . $t['leader'] . ' ' . implode(' ', $t['members']));
                    $deadlineTs = $t['deadline'] !== '' ? (int)strtotime($t['deadline']) : 0;
                ?>
                <div class="tm-card <?= $t['state'] === 'completed' ? 'completed' : '' ?>" data-team-index="<?= (int)$ti ?>"
                     data-team-name="<?= htmlspecialchars(strtolower($t['name'])) ?>"
                     data-state="<?= htmlspecialchars($t['state']) ?>"
                     data-search="<?= htmlspecialchars($searchText) ?>"
                     data-order="<?= $tmOrder++ ?>"
                     data-deadline="<?= $deadlineTs ?>"
                     data-progress="<?= (int)$t['percent'] ?>">

                    <div class="tm-top">
                        <div class="tm-title">
                            <span class="tm-name"><?= htmlspecialchars($t['name']) ?></span>
                            <span class="tm-pill <?= $t['state'] ?>"><?= htmlspecialchars($t['status']) ?></span>
                            <?php if (!empty($t['is_new'])): ?><span class="tm-pill new">New</span><?php endif; ?>
                        </div>
                        <button type="button" class="tm-kebab" data-team-menu="<?= (int)$ti ?>" aria-label="More options"><span class="material-symbols-outlined">more_vert</span></button>
                    </div>
                    <p class="tm-project">Project: <?= htmlspecialchars($t['project']) ?></p>

                    <div class="tm-people">
                        <div>
                            <div class="tm-label">Team Leader</div>
                            <div class="tm-leader">
                                <div class="tm-av" style="background:<?= $tmColors[abs(crc32($t['leader'])) % count($tmColors)] ?>;"><?= tmInitials($t['leader']) ?></div>
                                <div>
                                    <div class="tm-leader-name"><?= htmlspecialchars($t['leader']) ?></div>
                                    <div class="tm-leader-role"><?= htmlspecialchars($t['role']) ?></div>
                                </div>
                            </div>
                        </div>
                        <div>
                            <div class="tm-label">Team Members</div>
                            <div class="tm-stack">
                                <?php foreach ($t['members'] as $m): ?>
                                    <div class="tm-av" title="<?= htmlspecialchars($m) ?>" style="background:<?= $tmColors[abs(crc32($m)) % count($tmColors)] ?>;"><?= tmInitials($m) ?></div>
                                <?php endforeach; ?>
                                <?php if ($t['more'] > 0): ?><div class="tm-av more">+<?= (int)$t['more'] ?></div><?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <?php if (!empty($t['skills'])): ?>
                        <div class="tm-skills">
                            <div class="tm-label">Skills Covered</div>
                            <div class="tm-chips">
                                <?php foreach ($t['skills'] as $sk): ?><span class="tm-chip"><?= htmlspecialchars($sk) ?></span><?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="tm-progress <?= in_array($t['state'], ['behind', 'completed']) ? $t['state'] : '' ?>">
                        <div class="tm-prow"><span><?= htmlspecialchars($t['phase']) ?></span><span><?= (int)$t['percent'] ?>%</span></div>
                        <div class="tm-bar"><span style="width:<?= (int)$t['percent'] ?>%;"></span></div>
                        <div class="tm-meta">
                            <?php if ($t['deadline'] !== ''): ?>
                                <span class="left"><span class="material-symbols-outlined">calendar_today</span>Deadline: <?= htmlspecialchars($t['deadline']) ?></span>
                            <?php else: ?>
                                <span></span>
                            <?php endif; ?>
                            <span class="right <?= $t['tone'] ?>">
                                <span class="material-symbols-outlined"><?= $t['state'] === 'completed' ? 'check_circle' : ($t['tone'] === 'late' ? 'warning' : 'schedule') ?></span>
                                <?= htmlspecialchars($t['time']) ?>
                            </span>
                        </div>
                    </div>

                    <div class="tm-actions <?= count($t['actions']) === 1 ? 'single' : '' ?>">
                        <?php foreach ($t['actions'] as $i => $a): ?>
                            <button type="button" class="tm-btn <?= $a === 'Final Report' ? 'report' : ($i === 0 ? 'solid' : '') ?>"
                                <?= in_array($a, ['View', 'Final Report'], true) ? 'data-view-team="' . (int)$ti . '"' : '' ?>
                                <?= in_array($a, ['Task', 'Chat'], true) ? 'data-work-team="' . (int)$ti . '" data-work-focus="' . ($a === 'Chat' ? 'chat' : 'tasks') . '"' : '' ?>
                                <?= $a === 'Review' ? 'data-review-team="' . (int)$ti . '"' : '' ?>>
                                <span class="material-symbols-outlined"><?= $tmActionIcon[$a] ?></span><?= $a === 'Final Report' ? 'View Final Report' : $a ?>
                            </button>
                        <?php endforeach; ?>
                    </div>

                </div>
            <?php endforeach; ?>
        </div>

        <div class="tm-empty" id="tmEmpty" hidden>
            <span class="material-symbols-outlined">search_off</span>
            <div class="tm-empty-title">No teams match your filters</div>
            <button type="button" class="tm-btn" id="tmEmptyClear">Clear filters</button>
        </div>

    </div>

    <!-- =====================================================
         TEAM WORKSPACE: Kanban board + team chat (same page)
    ====================================================== -->
    <section class="tw-page" id="tmWorkspace" hidden>

        <div class="tw-topbar">
            <button type="button" class="tw-back" id="twBack">
                <span class="material-symbols-outlined">arrow_back</span> Back to Teams
            </button>
            <div class="tw-crumbs"><span>Teams</span><span class="material-symbols-outlined">chevron_right</span><strong id="twCrumb"></strong></div>
        </div>

        <div class="tw-hero">
            <div>
                <div class="tw-kicker"><span class="material-symbols-outlined">rocket_launch</span><span id="twPhase"></span></div>
                <h2 id="twProject"></h2>
                <div class="tw-team-line">
                    <div class="tw-avs" id="twAvatars"></div>
                    <span id="twTeamName"></span>
                </div>
            </div>
            <div class="tw-progress-card">
                <div class="tw-pc-row"><span>Overall Progress</span><strong id="twPct">0%</strong></div>
                <div class="tw-pc-bar"><span id="twBar"></span></div>
                <div class="tw-pc-row small"><span id="twDone"></span><span id="twPending"></span></div>
            </div>
        </div>

        <div class="tw-layout" id="twLayout">
            <!-- Kanban -->
            <div class="tw-board-wrap" id="twBoardWrap">
                <div class="tw-board-head">
                    <h3><span class="material-symbols-outlined">view_kanban</span> Kanban Board</h3>
                    <div class="tw-board-actions">
                        <select class="tw-select" id="twMemberFilter" aria-label="Filter by member">
                            <option value="all">All members</option>
                        </select>
                        <button type="button" class="tm-btn" id="twChatToggle"><span class="material-symbols-outlined">chat_bubble</span><span>Team Chat</span></button>
                        <button type="button" class="tm-btn solid" id="twNewTask"><span class="material-symbols-outlined">add</span>New Task</button>
                    </div>
                </div>
                <div class="tw-board" id="twBoard"></div>

                <!-- Shared Assets -->
                <div class="tw-assets">
                    <h3 class="tw-sec-title"><span class="material-symbols-outlined">folder_open</span> Shared Assets</h3>
                    <div class="tw-assets-grid">
                        <div class="tw-files">
                            <div class="tw-files-head">
                                <span>Team Files</span>
                                <span class="tw-files-count" id="twFilesCount"></span>
                            </div>
                            <div id="twFiles"></div>
                        </div>

                        <label class="tw-drop" id="twDrop">
                            <input type="file" id="twFileInput" multiple hidden
                                   accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.txt,.csv,.png,.jpg,.jpeg,.gif,.webp,.zip,.mp4,.fig">
                            <span class="material-symbols-outlined">cloud_upload</span>
                            <strong>Upload New Assets</strong>
                            <span>Drag and drop files here or click to browse (max 10MB each)</span>
                        </label>
                    </div>
                    <div class="tw-upload-err" id="twUploadErr"></div>
                </div>
            </div>

            <!-- Chat -->
            <aside class="tw-chat" id="twChat">
                <div class="tw-chat-head">
                    <h3>Team Discussion</h3>
                </div>
                <div class="tw-msgs" id="twMsgs"></div>
                <form class="tw-send" id="twSend">
                    <input type="text" id="twInput" placeholder="Type a message to the team…" autocomplete="off" maxlength="500">
                    <button type="submit" aria-label="Send"><span class="material-symbols-outlined">send</span></button>
                </form>
            </aside>
        </div>
    </section>

    <!-- New Task popup -->
    <div class="tm-overlay" id="twTaskModal">
        <form class="tm-modal" id="twTaskForm" style="width:min(520px,100%);" novalidate>
            <div class="tm-modal-head">
                <div class="tm-modal-icon"><span class="material-symbols-outlined">add_task</span></div>
                <div><h3>New Task</h3><p>Add a task to this team’s board.</p></div>
                <button type="button" class="tm-x" data-close-task aria-label="Close"><span class="material-symbols-outlined">close</span></button>
            </div>
            <div class="tm-modal-body">
                <div class="tm-field">
                    <label class="tm-f-label" for="twTTitle">Task Title *</label>
                    <input type="text" id="twTTitle" class="tm-input" maxlength="120" placeholder="e.g. Design the login screen">
                    <div class="tm-err" id="twTErr"></div>
                </div>
                <div class="tm-grid-2">
                    <div class="tm-field">
                        <label class="tm-f-label" for="twTTag">Type</label>
                        <select id="twTTag" class="tm-input">
                            <option>Development</option><option>Design</option><option>Research</option>
                            <option>Testing</option><option>Documentation</option>
                        </select>
                    </div>
                    <div class="tm-field">
                        <label class="tm-f-label" for="twTCol">Column</label>
                        <select id="twTCol" class="tm-input">
                            <option value="todo">To Do</option><option value="progress">In Progress</option>
                            <option value="review">Review</option><option value="done">Done</option>
                        </select>
                    </div>
                    <div class="tm-field">
                        <label class="tm-f-label" for="twTWho">Assign To</label>
                        <select id="twTWho" class="tm-input"></select>
                    </div>
                    <div class="tm-field">
                        <label class="tm-f-label" for="twTDue">Due Date</label>
                        <input type="date" id="twTDue" class="tm-input" min="<?= date('Y-m-d') ?>">
                    </div>
                </div>
            </div>
            <div class="tm-modal-foot">
                <button type="button" class="tm-btn" data-close-task>Cancel</button>
                <button type="submit" class="tm-btn solid"><span class="material-symbols-outlined">check</span>Add Task</button>
            </div>
        </form>
    </div>

    <!-- ===================== TEAM DETAILS (View) ===================== -->
    <div class="tm-overlay" id="tmViewModal">
        <div class="tm-modal tv-modal" role="dialog" aria-modal="true" aria-labelledby="tvName">
            <div class="tv-head">
                <div class="tv-head-main">
                    <div class="tv-kicker">Team Details</div>
                    <div class="tv-title-row">
                        <h3 id="tvName"></h3>
                        <span class="tm-pill" id="tvStatus"></span>
                    </div>
                    <div class="tv-project" id="tvProject"></div>
                </div>
                <button type="button" class="tm-x" data-close-view aria-label="Close"><span class="material-symbols-outlined">close</span></button>
            </div>
            <div class="tv-body" id="tvBody"></div>
            <div class="tm-modal-foot">
                <button type="button" class="tm-btn solid" data-close-view>Close</button>
            </div>
        </div>
    </div>

    <!-- ===================== EDIT TEAM ===================== -->
    <div class="tm-overlay" id="tmEditModal">
        <form class="tm-modal" method="post" action="teams.php" id="tmEditForm" novalidate>
            <input type="hidden" name="action" value="edit_team">
            <input type="hidden" name="team_id" id="teId">
            <div class="tm-modal-head">
                <div class="tm-modal-icon"><span class="material-symbols-outlined">edit</span></div>
                <div><h3>Edit Team</h3><p id="teProject"></p></div>
                <button type="button" class="tm-x" data-close-edit aria-label="Close"><span class="material-symbols-outlined">close</span></button>
            </div>
            <div class="tm-modal-body">
                <div class="tm-field">
                    <label class="tm-f-label" for="teName">Team Name *</label>
                    <input type="text" id="teName" name="team_name" class="tm-input" maxlength="100">
                    <div class="tm-err" id="teErrName"></div>
                </div>
                <div class="tm-field">
                    <label class="tm-f-label" for="teDeadline">Team Deadline *</label>
                    <input type="date" id="teDeadline" name="deadline" class="tm-input">
                    <div class="tm-err" id="teErrDeadline"></div>
                </div>
            </div>
            <div class="tm-modal-foot">
                <button type="button" class="tm-btn" data-close-edit>Cancel</button>
                <button type="submit" class="tm-btn solid"><span class="material-symbols-outlined">check</span>Save Changes</button>
            </div>
        </form>
    </div>

    <!-- ===================== DELAY REVIEW (Behind Schedule teams) ===================== -->
    <div class="tm-overlay" id="tmReviewModal">
        <div class="tm-modal tv-modal" role="dialog" aria-modal="true">
            <div class="tv-head">
                <div class="tv-head-main">
                    <div class="tv-kicker" style="color:#b91c1c;">Delay Review</div>
                    <div class="tv-title-row">
                        <h3 id="trName"></h3>
                        <span class="tm-pill behind" id="trPill">Behind Schedule</span>
                    </div>
                    <div class="tv-project" id="trProject"></div>
                </div>
                <button type="button" class="tm-x" data-close-review aria-label="Close"><span class="material-symbols-outlined">close</span></button>
            </div>
            <div class="tv-body" id="trBody"></div>
            <div class="tm-modal-foot" style="justify-content:space-between;">
                <button type="button" class="tm-btn" id="trOpenBoard"><span class="material-symbols-outlined">view_kanban</span>Open Kanban Board</button>
                <button type="button" class="tm-btn solid" data-close-review>Close</button>
            </div>
        </div>
    </div>

    <form method="post" action="teams.php" id="trExtendForm" hidden>
        <input type="hidden" name="action" value="extend_deadline">
        <input type="hidden" name="team_id">
        <input type="hidden" name="deadline">
        <input type="hidden" name="reason">
        <input type="hidden" name="set_ontrack">
    </form>

    <!-- used by the ⋮ menu to send Status / Delete for database teams -->
    <form method="post" action="teams.php" id="tmActionForm" hidden>
        <input type="hidden" name="action">
        <input type="hidden" name="team_id">
        <input type="hidden" name="status">
    </form>

    <!-- ===================== CREATE NEW TEAM ===================== -->
    <!-- role suggestions for the "Role" boxes (organization can still type any role) -->
    <datalist id="tmRoleList">
        <option value="Team Leader"><option value="Project Coordinator"><option value="Frontend Developer">
        <option value="Backend Developer"><option value="Full Stack Developer"><option value="Mobile Developer">
        <option value="UI/UX Designer"><option value="Database Designer"><option value="ML Engineer">
        <option value="Data Analyst"><option value="QA / Tester"><option value="DevOps Engineer">
        <option value="Security Analyst"><option value="Technical Writer">
    </datalist>

    <div class="tm-overlay" id="tmCreateModal">
        <form class="tm-modal tm-modal-wide" method="post" action="teams.php" id="tmCreateForm" novalidate>
            <input type="hidden" name="action" value="create_team">

            <div class="tm-modal-head">
                <div class="tm-modal-icon"><span class="material-symbols-outlined">group_add</span></div>
                <div>
                    <h3>Create New Team</h3>
                    <p>Build a team from the students whose proposals you accepted.</p>
                </div>
                <button type="button" class="tm-x" data-close aria-label="Close"><span class="material-symbols-outlined">close</span></button>
            </div>

            <div class="tm-modal-body">
                <?php if (empty($tmProjects)): ?>
                    <div class="tm-note warn">
                        <span class="material-symbols-outlined">info</span>
                        You don’t have any open or active projects yet. Post a project first, then create a team for it.
                    </div>
                <?php else: ?>

                <div class="tm-step"><span>1</span>Team &amp; Project</div>
                <div class="tm-grid-2">
                    <div class="tm-field">
                        <label class="tm-f-label" for="tmName">Team Name *</label>
                        <input type="text" id="tmName" name="team_name" class="tm-input" maxlength="100" placeholder="e.g. Code Crafters" autocomplete="off">
                        <div class="tm-err" data-err="name"></div>
                    </div>
                    <div class="tm-field">
                        <label class="tm-f-label" for="tmProject">Project *</label>
                        <select id="tmProject" name="project_id" class="tm-input">
                            <option value="">Select a project</option>
                            <?php foreach ($tmProjects as $pr): ?>
                                <option value="<?= (int)$pr['id'] ?>"><?= htmlspecialchars($pr['title']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="tm-err" data-err="project"></div>
                    </div>
                </div>
                <div class="tm-project-info" id="tmProjectInfo" hidden></div>

                <div class="tm-step"><span>2</span>Members &amp; Roles</div>
                <div class="tm-field">
                    <div class="tm-f-label-row">
                        <label class="tm-f-label">Accepted Students *</label>
                        <span class="tm-picked" id="tmPicked"></span>
                    </div>
                    <div class="tm-members" id="tmMembers">
                        <div class="tm-members-empty">Select a project to see the students you accepted for it.</div>
                    </div>
                    <div class="tm-hint">Tick a student to add them, then give them a role in the team.</div>
                    <div class="tm-err" data-err="members"></div>
                </div>

                <div class="tm-field">
                    <label class="tm-f-label" for="tmLeader">Team Leader *</label>
                    <select id="tmLeader" name="leader" class="tm-input" disabled>
                        <option value="">Choose members first</option>
                    </select>
                    <div class="tm-err" data-err="leader"></div>
                </div>

                <div class="tm-step"><span>3</span>Skills &amp; Deadline</div>
                <div class="tm-field">
                    <label class="tm-f-label" for="tmSkillInput">Required Skills *</label>
                    <div class="tm-tags" id="tmSkills">
                        <input type="text" id="tmSkillInput" maxlength="40" placeholder="Type a skill and press Enter">
                    </div>
                    <input type="hidden" name="skills" id="tmSkillsValue">
                    <div class="tm-suggest" id="tmSkillSuggest"></div>
                    <div class="tm-err" data-err="skills"></div>
                </div>

                <div class="tm-field">
                    <label class="tm-f-label" for="tmDeadline">Team Deadline *</label>
                    <input type="date" id="tmDeadline" name="deadline" class="tm-input" min="<?= date('Y-m-d') ?>">
                    <div class="tm-hint" id="tmDeadlineHint">Must be today or later, and not after the project deadline.</div>
                    <div class="tm-err" data-err="deadline"></div>
                </div>

                <?php endif; ?>
            </div>

            <div class="tm-modal-foot">
                <button type="button" class="tm-btn" data-close>Cancel</button>
                <?php if (!empty($tmProjects)): ?>
                <button type="submit" class="tm-btn solid"><span class="material-symbols-outlined">check</span>Create Team</button>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <?php if ($flash): ?>
    <div class="flash-toast flash-<?= htmlspecialchars($flash['type']) ?>" id="tmFlash">
        <div class="flash-icon-wrap">
            <span class="material-symbols-outlined"><?= $flash['type'] === 'success' ? 'check_circle' : 'error' ?></span>
        </div>
        <div class="flash-content">
            <div class="flash-title" style="font-weight:700; font-size:14px;"><?= htmlspecialchars($flash['title']) ?></div>
            <div class="flash-msg" style="font-size:13px; margin-top:2px;"><?= htmlspecialchars($flash['message']) ?></div>
        </div>
    </div>
    <?php endif; ?>
</main>

<script>
/* Popups are moved to <body> so they cover the WHOLE screen
   (inside <main> they were limited to the content area). */
document.querySelectorAll('.tm-overlay').forEach(o => document.body.appendChild(o));

/* =================================================================
   FILTER (status, project, search) – works on the cards in the page
================================================================= */
(function () {
    const btn     = document.getElementById('tmFilterBtn');
    const panel   = document.getElementById('tmFilterPanel');
    const search  = document.getElementById('tmFilterSearch');
    const sortEl  = document.getElementById('tmFilterSort');
    const grid    = document.querySelector('.tm-grid');
    const seg     = document.getElementById('tmFilterStatus');
    const countEl = document.getElementById('tmFilterCount');
    const result  = document.getElementById('tmFilterResult');
    const empty   = document.getElementById('tmEmpty');
    const cards   = Array.from(document.querySelectorAll('.tm-grid .tm-card'));

    let status = 'all';

    function apply() {
        const q = search.value.trim().toLowerCase();
        let shown = 0;
        const live = cards.filter(c => !c.dataset.deleted);

        live.forEach(function (card) {
            const ok = (status === 'all' || card.dataset.state === status)
                    && (q === '' || card.dataset.search.indexOf(q) !== -1);
            card.hidden = !ok;
            if (ok) shown++;
        });

        // sort the cards
        const num = (c, k) => Number(c.dataset[k]) || 0;
        const sorted = cards.slice().sort(function (a, b) {
            switch (sortEl.value) {
                case 'deadline':                         // no deadline -> last
                    return (num(a, 'deadline') || Infinity) - (num(b, 'deadline') || Infinity);
                case 'progress_low':  return num(a, 'progress') - num(b, 'progress');
                case 'progress_high': return num(b, 'progress') - num(a, 'progress');
                default:              return num(a, 'order') - num(b, 'order');
            }
        });
        sorted.forEach(c => grid.appendChild(c));

        const active = (status !== 'all') + (q !== '') + (sortEl.value !== 'newest');
        countEl.textContent = active;
        countEl.hidden = active === 0;
        result.textContent = 'Showing ' + shown + ' of ' + live.length;
        empty.hidden = shown !== 0;
    }

    function clearAll() {
        status = 'all';
        seg.querySelectorAll('button').forEach(b => b.classList.toggle('on', b.dataset.value === 'all'));
        sortEl.value = 'newest';
        search.value = '';
        apply();
    }

    btn.addEventListener('click', function (e) {
        e.stopPropagation();
        panel.hidden = !panel.hidden;
        btn.setAttribute('aria-expanded', String(!panel.hidden));
        if (!panel.hidden) search.focus();
    });
    panel.addEventListener('click', e => e.stopPropagation());
    document.addEventListener('click', () => { panel.hidden = true; btn.setAttribute('aria-expanded', 'false'); });
    document.addEventListener('keydown', e => { if (e.key === 'Escape') panel.hidden = true; });

    seg.addEventListener('click', function (e) {
        const b = e.target.closest('button');
        if (!b) return;
        status = b.dataset.value;
        seg.querySelectorAll('button').forEach(x => x.classList.toggle('on', x === b));
        apply();
    });
    sortEl.addEventListener('change', apply);
    search.addEventListener('input', apply);
    document.getElementById('tmFilterClear').addEventListener('click', clearAll);
    document.getElementById('tmEmptyClear').addEventListener('click', clearAll);
    document.addEventListener('tm-refilter', apply);

    apply();
})();


/* =================================================================
   SHARED FORM PARTS (used by Create Team and Edit Team)
   - member list with a Role box for every ticked student
   - skills "tag" input
================================================================= */
window.TMForm = (function () {
    const COLORS = ['#1e3a5f', '#0f766e', '#7c3aed', '#b45309', '#be185d', '#2563eb', '#475569'];
    const esc = t => { const d = document.createElement('div'); d.textContent = t == null ? '' : String(t); return d.innerHTML; };
    const initials = n => String(n || '').trim().split(/\s+/).slice(0, 2).map(w => w.charAt(0).toUpperCase()).join('');
    const colorFor = n => { let h = 0; for (const c of String(n)) h = (h * 31 + c.charCodeAt(0)) >>> 0; return COLORS[h % COLORS.length]; };

    /* people: [{ value, name, sub, skills[], badge, badgeClass, locked }]
       chosen: { value: role }  -> ticked students and their roles          */
    function renderMembers(box, people, chosen) {
        chosen = chosen || {};
        box.innerHTML = people.map(p => {
            const on = Object.prototype.hasOwnProperty.call(chosen, p.value);
            return `
            <div class="tm-mrow ${on ? 'on' : ''}" data-value="${esc(p.value)}">
                <label class="tm-member ${p.locked ? 'disabled' : ''}" ${p.locked ? 'title="' + esc(p.locked) + '"' : ''}>
                    <input type="checkbox" name="members[]" value="${esc(p.value)}" data-name="${esc(p.name)}"
                           ${on ? 'checked' : ''} ${p.locked ? 'disabled data-locked="1"' : ''}>
                    <span class="tm-av" style="background:${colorFor(p.name)};">${esc(initials(p.name))}</span>
                    <span class="tm-member-info">
                        <span class="tm-member-name">${esc(p.name)}</span><br>
                        <span class="tm-member-sub">${esc(p.sub || '')}</span>
                        ${(p.skills || []).length ? `<span class="tm-mskills">${p.skills.map(s => `<span>${esc(s)}</span>`).join('')}</span>` : ''}
                    </span>
                    ${p.badge ? `<span class="tm-src ${esc(p.badgeClass || '')}">${esc(p.badge)}</span>` : ''}
                </label>
                <div class="tm-role-row" ${on ? '' : 'hidden'}>
                    <span class="material-symbols-outlined">badge</span>
                    <input type="text" class="tm-input" name="roles[${esc(p.value)}]" list="tmRoleList" maxlength="50"
                           placeholder="Role in the team, e.g. Frontend Developer" value="${esc(on ? chosen[p.value] : '')}" ${on ? '' : 'disabled'}>
                    <span class="tm-lead-tag" hidden><span class="material-symbols-outlined">star</span>Leader</span>
                </div>
            </div>`;
        }).join('');
    }

    /* keeps role boxes, max members and the leader list in sync */
    function syncMembers(box, leaderEl, max, pickedEl) {
        const rows = Array.from(box.querySelectorAll('.tm-mrow'));
        const picked = rows.filter(r => r.querySelector('input[type="checkbox"]').checked);

        rows.forEach(r => {
            const cb = r.querySelector('input[type="checkbox"]');
            const role = r.querySelector('.tm-role-row input');
            const full = !cb.checked && picked.length >= max;
            if (!cb.dataset.locked) {
                cb.disabled = full;
                r.querySelector('.tm-member').classList.toggle('disabled', full);
            }
            r.classList.toggle('on', cb.checked);
            r.querySelector('.tm-role-row').hidden = !cb.checked;
            role.disabled = !cb.checked;           // unticked -> role is not sent
        });

        if (pickedEl) pickedEl.textContent = picked.length + ' / ' + max + ' selected';

        const keep = leaderEl.value;
        leaderEl.innerHTML = picked.length
            ? '<option value="">Select the leader</option>' + picked.map(r => {
                const cb = r.querySelector('input[type="checkbox"]');
                return `<option value="${esc(cb.value)}">${esc(cb.dataset.name)}</option>`; }).join('')
            : '<option value="">Choose members first</option>';
        leaderEl.disabled = picked.length === 0;
        if (picked.some(r => r.dataset.value === keep)) leaderEl.value = keep;
        else if (picked.length === 1) leaderEl.value = picked[0].dataset.value;
        markLeader(box, leaderEl.value);
        return picked;
    }

    function markLeader(box, value) {
        box.querySelectorAll('.tm-mrow').forEach(r => { r.querySelector('.tm-lead-tag').hidden = !value || r.dataset.value !== value; });
    }

    /* skills tag input */
    function tagInput(wrap, hidden, suggestBox, onChange) {
        const input = wrap.querySelector('input');
        let tags = [], suggestions = [];

        const has = t => tags.some(x => x.toLowerCase() === t.toLowerCase());
        function render() {
            wrap.querySelectorAll('.tm-tag').forEach(t => t.remove());
            tags.forEach((t, i) => {
                const chip = document.createElement('span');
                chip.className = 'tm-tag';
                chip.innerHTML = `${esc(t)}<button type="button" aria-label="Remove ${esc(t)}" data-i="${i}"><span class="material-symbols-outlined">close</span></button>`;
                wrap.insertBefore(chip, input);
            });
            hidden.value = tags.join(', ');
            const left = suggestions.filter(s => !has(s)).slice(0, 10);
            suggestBox.innerHTML = left.length
                ? '<span>Suggested:</span>' + left.map(s => `<button type="button" class="tm-sug" data-sug="${esc(s)}">+ ${esc(s)}</button>`).join('')
                : '';
            if (onChange) onChange(tags);
        }
        function add(raw) {
            String(raw).split(',').map(s => s.trim().replace(/\s+/g, ' ')).filter(Boolean).forEach(s => {
                if (s.length <= 40 && !has(s) && tags.length < 15) tags.push(s);
            });
            render();
        }

        input.addEventListener('keydown', e => {
            if (e.key === 'Enter' || e.key === ',') { e.preventDefault(); add(input.value); input.value = ''; }
            else if (e.key === 'Backspace' && input.value === '' && tags.length) { tags.pop(); render(); }
        });
        input.addEventListener('blur', () => { if (input.value.trim()) { add(input.value); input.value = ''; } });
        wrap.addEventListener('click', e => {
            const b = e.target.closest('button[data-i]');
            if (b) { tags.splice(Number(b.dataset.i), 1); render(); }
            else input.focus();
        });
        suggestBox.addEventListener('click', e => {
            const b = e.target.closest('[data-sug]');
            if (b) add(b.dataset.sug);
        });

        return {
            set(list) { tags = []; add((list || []).join(',')); },
            suggest(list) {
                const seen = {};
                suggestions = (list || []).filter(s => s && !seen[s.toLowerCase()] && (seen[s.toLowerCase()] = true));
                render();
            },
            get: () => tags.slice()
        };
    }

    return { esc, initials, colorFor, renderMembers, syncMembers, markLeader, tagInput };
})();


/* =================================================================
   CREATE NEW TEAM
================================================================= */
(function () {
    const PROJECTS   = <?= json_encode(array_map(fn($p) => [
                            'id' => (int)$p['id'], 'title' => $p['title'], 'members' => (int)$p['members'],
                            'deadline' => (!empty($p['deadline']) && strtotime($p['deadline'])) ? date('Y-m-d', strtotime($p['deadline'])) : '',
                            'deadline_text' => (!empty($p['deadline']) && strtotime($p['deadline'])) ? date('M d, Y', strtotime($p['deadline'])) : '',
                            'skills' => array_values(array_filter(array_map('trim', explode(',', $p['keywords'] ?? '')))),
                        ], $tmProjects), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?>;
    const CANDIDATES = <?= json_encode($tmCandidates, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?>;
    const TODAY = <?= json_encode(date('Y-m-d')) ?>;
    const F = window.TMForm;

    const modal = document.getElementById('tmCreateModal');
    const form  = document.getElementById('tmCreateForm');
    const openBtn = document.getElementById('tmCreateBtn');

    const nameEl    = document.getElementById('tmName');
    const projectEl = document.getElementById('tmProject');
    const infoEl    = document.getElementById('tmProjectInfo');
    const membersEl = document.getElementById('tmMembers');
    const pickedEl  = document.getElementById('tmPicked');
    const leaderEl  = document.getElementById('tmLeader');
    const deadlineEl= document.getElementById('tmDeadline');
    const dlHint    = document.getElementById('tmDeadlineHint');

    const err = (key, msg) => { const el = form.querySelector('[data-err="' + key + '"]'); if (el) el.textContent = msg || ''; };

    function open() {
        modal.classList.add('open');
        if (nameEl) setTimeout(() => nameEl.focus(), 50);
    }
    function close() { modal.classList.remove('open'); }

    openBtn.addEventListener('click', open);
    modal.querySelectorAll('[data-close]').forEach(b => b.addEventListener('click', close));
    modal.addEventListener('click', e => { if (e.target === modal) close(); });
    document.addEventListener('keydown', e => { if (e.key === 'Escape' && modal.classList.contains('open')) close(); });

    if (!projectEl) return;   // no projects -> only the info message is shown

    const skills = F.tagInput(document.getElementById('tmSkills'), document.getElementById('tmSkillsValue'),
                              document.getElementById('tmSkillSuggest'), list => {
        if (list.length) { err('skills', ''); document.getElementById('tmSkills').classList.remove('invalid'); }
    });

    const currentProject = () => PROJECTS.find(p => p.id === Number(projectEl.value)) || null;
    const pickedRows = () => Array.from(membersEl.querySelectorAll('.tm-mrow')).filter(r => r.querySelector('input[type="checkbox"]').checked);

    function refresh() {
        const pr = currentProject();
        if (!pr) { pickedEl.textContent = ''; leaderEl.innerHTML = '<option value="">Choose members first</option>'; leaderEl.disabled = true; return; }
        const picked = F.syncMembers(membersEl, leaderEl, Math.max(1, pr.members), pickedEl);
        if (picked.length) { err('members', ''); membersEl.classList.remove('invalid'); }

        // suggestions = project skills + skills of the ticked students
        const fromMembers = [];
        picked.forEach(r => (CANDIDATES[pr.id].find(c => c.email === r.dataset.value) || {}).skills?.forEach(s => fromMembers.push(s)));
        skills.suggest(pr.skills.concat(fromMembers));
    }

    projectEl.addEventListener('change', function () {
        const pr = currentProject();
        err('project', ''); projectEl.classList.remove('invalid');
        err('members', ''); err('leader', ''); err('deadline', '');
        deadlineEl.classList.remove('invalid'); document.getElementById('tmSkills').classList.remove('invalid');

        if (!pr) {
            infoEl.hidden = true;
            membersEl.innerHTML = '<div class="tm-members-empty">Select a project to see the students you accepted for it.</div>';
            skills.set([]); skills.suggest([]);
            deadlineEl.max = '';
            refresh();
            return;
        }

        infoEl.innerHTML =
            `<span class="tm-info-chip"><span class="material-symbols-outlined">group</span>${pr.members} student${pr.members === 1 ? '' : 's'} needed</span>` +
            (pr.deadline_text ? `<span class="tm-info-chip"><span class="material-symbols-outlined">event</span>Project deadline: ${F.esc(pr.deadline_text)}</span>` : '');
        infoEl.hidden = false;

        // team deadline: today ... project deadline
        deadlineEl.min = TODAY;
        deadlineEl.max = pr.deadline && pr.deadline >= TODAY ? pr.deadline : '';
        if (deadlineEl.value && ((deadlineEl.max && deadlineEl.value > deadlineEl.max) || deadlineEl.value < TODAY)) deadlineEl.value = '';
        if (!deadlineEl.value && deadlineEl.max) deadlineEl.value = deadlineEl.max;   // default = project deadline
        dlHint.textContent = pr.deadline_text
            ? 'Pick a date from today up to the project deadline (' + pr.deadline_text + ').'
            : 'Pick a date from today onwards.';

        const list = CANDIDATES[pr.id] || [];
        if (list.length) {
            F.renderMembers(membersEl, list.map(c => ({
                value: c.email, name: c.name, sub: c.sub, skills: c.skills,
                badge: c.team ? 'In ' + c.team : 'Accepted', badgeClass: c.team ? 'locked' : 'assigned',
                locked: c.team ? 'Already in the team "' + c.team + '"' : ''
            })), {});
        } else {
            membersEl.innerHTML = '<div class="tm-members-empty">No accepted students for this project yet.<br>' +
                                  'Accept student proposals on the <a href="proposal.php">Proposals</a> page first.</div>';
        }

        skills.set(pr.skills);   // start with the project's skills; the organization can edit them
        refresh();
    });

    membersEl.addEventListener('change', e => {
        const cb = e.target.closest('input[type="checkbox"]');
        refresh();
        if (cb && cb.checked) {
            const role = cb.closest('.tm-mrow').querySelector('.tm-role-row input');
            setTimeout(() => role.focus(), 0);
        }
    });
    membersEl.addEventListener('input', e => { if (e.target.matches('.tm-role-row input')) { e.target.classList.remove('invalid'); err('members', ''); } });

    leaderEl.addEventListener('change', () => {
        err('leader', ''); leaderEl.classList.remove('invalid');
        F.markLeader(membersEl, leaderEl.value);
        // leader with an empty role -> "Team Leader"
        const row = membersEl.querySelector(`.tm-mrow[data-value="${CSS.escape(leaderEl.value)}"]`);
        const role = row && row.querySelector('.tm-role-row input');
        if (role && !role.value.trim()) role.value = 'Team Leader';
    });
    nameEl.addEventListener('input', () => { err('name', ''); nameEl.classList.remove('invalid'); });
    deadlineEl.addEventListener('change', () => { err('deadline', ''); deadlineEl.classList.remove('invalid'); });

    form.addEventListener('submit', function (e) {
        let ok = true;
        const pr = currentProject();
        const picked = pickedRows();

        if (nameEl.value.trim() === '') { err('name', 'Please enter a team name.'); nameEl.classList.add('invalid'); ok = false; }
        if (!pr) { err('project', 'Please select a project.'); projectEl.classList.add('invalid'); ok = false; }

        if (pr && picked.length === 0) {
            err('members', 'Please choose at least one student.'); membersEl.classList.add('invalid'); ok = false;
        } else {
            const noRole = picked.map(r => r.querySelector('.tm-role-row input')).filter(i => !i.value.trim());
            noRole.forEach(i => i.classList.add('invalid'));
            if (noRole.length) { err('members', 'Please give every selected student a role.'); ok = false; }
        }
        if (picked.length && !leaderEl.value) {
            err('leader', 'Please choose a team leader.'); leaderEl.classList.add('invalid'); ok = false;
        }

        if (pr && !skills.get().length) {
            err('skills', 'Please add at least one skill the team needs.'); document.getElementById('tmSkills').classList.add('invalid'); ok = false;
        }

        const dl = deadlineEl.value;
        if (!dl) { err('deadline', 'Please pick a team deadline.'); deadlineEl.classList.add('invalid'); ok = false; }
        else if (dl < TODAY) { err('deadline', 'The deadline can’t be in the past.'); deadlineEl.classList.add('invalid'); ok = false; }
        else if (deadlineEl.max && dl > deadlineEl.max) { err('deadline', 'The team deadline can’t be after the project deadline.'); deadlineEl.classList.add('invalid'); ok = false; }

        if (!ok) {
            e.preventDefault();
            const first = form.querySelector('.tm-err:not(:empty)');
            if (first) first.closest('.tm-field').scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    });
})();


/* =================================================================
   TEAM DETAILS (View / View Final Report)
================================================================= */
(function () {
    const TEAMS  = <?= json_encode(array_values(array_map(fn($t) => [
                        'name' => $t['name'], 'state' => $t['state'], 'status' => $t['status'], 'project' => $t['project'],
                        'skills' => $t['skills'], 'phase' => $t['phase'], 'percent' => (int)$t['percent'],
                        'deadline' => $t['deadline'], 'time' => $t['time'], 'tone' => $t['tone'],
                        'details' => $t['details'] ?? null,
                    ], $teams)), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?>;
    const COLORS = ['#1e3a5f', '#0f766e', '#7c3aed', '#b45309', '#be185d', '#2563eb', '#475569'];

    const modal = document.getElementById('tmViewModal');
    const body  = document.getElementById('tvBody');

    const esc = t => { const d = document.createElement('div'); d.textContent = t == null ? '' : String(t); return d.innerHTML; };
    const initials = n => String(n || '').trim().split(/\s+/).slice(0, 2).map(w => w.charAt(0).toUpperCase()).join('');
    const colorFor = n => { let h = 0; for (const c of String(n)) h = (h * 31 + c.charCodeAt(0)) >>> 0; return COLORS[h % COLORS.length]; };
    const pct = v => Math.max(0, Math.min(100, Number(v) || 0));

    function workBadge(w) {
        const t = String(w || '').toLowerCase();
        const cls = t === 'completed' ? 'done' : (t === 'in progress' ? 'progress' : 'none');
        return `<span class="tv-work ${cls}">${esc(w || 'Not started')}</span>`;
    }

    function open(i) {
        const t = window.tmEffective ? window.tmEffective(i, TEAMS[i]) : TEAMS[i];
        if (!t) return;
        const d = t.details || { members: [], milestones: [], activity: [] };
        const members = d.members || [];

        document.getElementById('tvName').textContent = t.name;
        const st = document.getElementById('tvStatus');
        st.className = 'tm-pill ' + t.state;
        st.textContent = t.status;
        document.getElementById('tvProject').innerHTML = 'Project: <strong>' + esc(t.project) + '</strong>';

        const milesDone = (d.milestones || []).filter(m => m.done).length;
        const milesTotal = (d.milestones || []).length;

        const info = [
            ['Organization', d.company], ['Category', d.category], ['Duration', d.duration],
            ['Team created', d.created], ['Team deadline', d.team_deadline], ['Project deadline', d.project_deadline]
        ].filter(r => r[1]).map(r => `<div class="tv-info"><span>${esc(r[0])}</span><strong>${esc(r[1])}</strong></div>`).join('');

        const memberCards = members.length ? members.map(m => `
            <div class="tv-member ${m.leader ? 'leader' : ''}">
                <div class="tv-m-top">
                    <div class="tm-av" style="background:${colorFor(m.name)};">${esc(initials(m.name))}</div>
                    <div class="tv-m-info">
                        <div class="tv-m-name">${esc(m.name)}
                            ${m.leader ? '<span class="tv-leader-tag"><span class="material-symbols-outlined">star</span>Leader</span>' : ''}
                        </div>
                        <div class="tv-m-role">${esc(m.role)}</div>
                        <div class="tv-m-line"><span class="material-symbols-outlined">school</span>${esc([m.university, m.degree].filter(Boolean).join(' · '))}${m.year ? ' · Year ' + esc(m.year) : ''}</div>
                        <div class="tv-m-line"><span class="material-symbols-outlined">mail</span><a href="mailto:${esc(m.email)}">${esc(m.email)}</a></div>
                    </div>
                    ${workBadge(m.work)}
                </div>
                ${(m.skills || []).length ? `<div class="tv-m-skills">${m.skills.map(s => `<span>${esc(s)}</span>`).join('')}</div>` : ''}
                <div class="tv-m-prog">
                    <span>Contribution</span>
                    <div class="tv-bar ${pct(m.progress) === 100 ? 'completed' : ''}"><span style="width:${pct(m.progress)}%"></span></div>
                    <strong>${pct(m.progress)}%</strong>
                </div>
            </div>`).join('')
            : '<div class="tv-empty">No member details available.</div>';

        const miles = milesTotal ? `<ul class="tv-miles">${d.milestones.map(m => `
                <li class="${m.done ? 'done' : ''} ${m.current ? 'current' : ''}">
                    <span class="material-symbols-outlined">${m.done ? 'check_circle' : (m.current ? 'radio_button_checked' : 'radio_button_unchecked')}</span>
                    <span>${esc(m.title)}</span>
                </li>`).join('')}</ul>`
            : '<div class="tv-empty">No milestones added yet.</div>';

        const acts = (d.activity || []).length ? `<ul class="tv-act">${d.activity.map(a => `
                <li><span class="tv-act-dot"></span><div><div class="tv-act-text">${esc(a.text)}</div><div class="tv-act-when">${esc(a.when)}</div></div></li>`).join('')}</ul>`
            : '<div class="tv-empty">No recent activity.</div>';

        const barClass = t.state === 'completed' ? 'completed' : (t.state === 'behind' ? 'behind' : '');

        body.innerHTML = `
            <div class="tv-stats">
                <div class="tv-stat"><div class="tv-stat-icon blue"><span class="material-symbols-outlined">groups</span></div>
                    <div><div class="tv-stat-label">Members</div><div class="tv-stat-value">${members.length}</div></div></div>
                <div class="tv-stat"><div class="tv-stat-icon green"><span class="material-symbols-outlined">trending_up</span></div>
                    <div><div class="tv-stat-label">Progress</div><div class="tv-stat-value">${pct(t.percent)}%</div></div></div>
                <div class="tv-stat"><div class="tv-stat-icon sky"><span class="material-symbols-outlined">flag</span></div>
                    <div><div class="tv-stat-label">Milestones</div><div class="tv-stat-value">${milesTotal ? milesDone + ' / ' + milesTotal : '-'}</div></div></div>
                <div class="tv-stat"><div class="tv-stat-icon navy"><span class="material-symbols-outlined">event</span></div>
                    <div><div class="tv-stat-label">Deadline</div><div class="tv-stat-value">${esc(t.deadline || '-')}</div>
                    <div class="tv-stat-sub ${esc(t.tone)}">${esc(t.time)}</div></div></div>
            </div>

            <div class="tv-card">
                <div class="tv-overall-row"><span>${esc(t.phase)}</span><span>${pct(t.percent)}%</span></div>
                <div class="tv-bar ${barClass}"><span style="width:${pct(t.percent)}%"></span></div>
                ${info ? `<div class="tv-info-grid">${info}</div>` : ''}
            </div>

            <div class="tv-card">
                <h4><span class="material-symbols-outlined">group</span> Team Members <span class="tv-count">${members.length} member${members.length === 1 ? '' : 's'}</span></h4>
                <div class="tv-members">${memberCards}</div>
            </div>

            ${(t.skills || []).length ? `
            <div class="tv-card">
                <h4><span class="material-symbols-outlined">bolt</span> Skills Covered</h4>
                <div class="tv-chips">${t.skills.map(s => `<span class="tm-chip">${esc(s)}</span>`).join('')}</div>
            </div>` : ''}

            <div class="tv-two">
                <div class="tv-card">
                    <h4><span class="material-symbols-outlined">flag</span> Milestones</h4>
                    ${miles}
                </div>
                <div class="tv-card">
                    <h4><span class="material-symbols-outlined">history</span> Recent Activity</h4>
                    ${acts}
                </div>
            </div>
        `;

        body.scrollTop = 0;
        modal.classList.add('open');
    }

    function close() { modal.classList.remove('open'); }

    // works for buttons redrawn after a status change too
    document.addEventListener('click', e => {
        const b = e.target.closest('[data-view-team]');
        if (b) open(Number(b.dataset.viewTeam));
    });
    modal.querySelectorAll('[data-close-view]').forEach(b => b.addEventListener('click', close));
    modal.addEventListener('click', e => { if (e.target === modal) close(); });
    document.addEventListener('keydown', e => { if (e.key === 'Escape' && modal.classList.contains('open')) close(); });
})();


/* =================================================================
   TEAM WORKSPACE – Kanban board + team chat (same page, with Back)
   Demo: tasks and messages are saved in this browser (localStorage).
================================================================= */
(function () {
    const TEAMS = <?= json_encode(array_values(array_map(fn($t) => [
                    'name' => $t['name'], 'project' => $t['project'], 'phase' => $t['phase'],
                    'members' => array_map(fn($m) => ['name' => $m['name'], 'role' => $m['role'], 'leader' => !empty($m['leader'])],
                                           $t['details']['members'] ?? []),
                  ], $teams)), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?>;
    const ORG_NAME = <?= json_encode($user['username'] ?? 'Organization', JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) ?>;
    const COLORS = ['#1e3a5f', '#0f766e', '#7c3aed', '#b45309', '#be185d', '#2563eb', '#475569'];
    const COLS = [
        { key: 'todo',     label: 'To Do',       dot: '#6b7280' },
        { key: 'progress', label: 'In Progress', dot: '#ea580c' },
        { key: 'review',   label: 'Review',      dot: '#2563eb' },
        { key: 'done',     label: 'Done',        dot: '#16a34a' }
    ];

    // ---------- demo seed data ----------
    const SEED_TASKS = {
        'Nexus Systems': [
            ['Collect 12 months of supplier data', 'Research', 'done', 'Chamod Fernando', '2026-09-10', 100, 4],
            ['Demand forecast model v2', 'Development', 'progress', 'Nimal Silva', '2026-10-05', 60, 6],
            ['Supplier dashboard prototype', 'Design', 'review', 'Amaya Dissanayake', '2026-10-02', 100, 3],
            ['REST API for stock levels', 'Development', 'progress', 'Ravindu Pathirana', '2026-10-08', 45, 2],
            ['Write test cases for forecasting', 'Testing', 'todo', 'Ishara Gunawardena', '2026-10-12', 0, 0],
            ['Sprint 2 demo slides', 'Documentation', 'todo', 'Sanduni Perera', '2026-10-15', 0, 1]
        ],
        'Vortex Group': [
            ['Credential smart contract', 'Development', 'progress', 'Kasun Madushanka', '2026-09-22', 55, 5],
            ['Threat model & security review', 'Research', 'review', 'Malith Senanayake', '2026-09-25', 100, 2],
            ['Issuer portal UI', 'Design', 'todo', 'Tharushi Wijeratne', '2026-10-06', 0, 0],
            ['Verification API', 'Development', 'todo', 'Dulaj Bandara', '2026-10-10', 0, 1]
        ],
        'Quantum Analytics': [
            ['Sensor data pipeline', 'Development', 'done', 'Dilini Herath', '2026-08-15', 100, 3],
            ['Failure prediction model', 'Development', 'done', 'Ishan Rathnayake', '2026-08-30', 100, 5],
            ['Maintenance dashboard', 'Design', 'done', 'Nethmi Samarasinghe', '2026-09-10', 100, 2],
            ['Final report', 'Documentation', 'done', 'Yasith Abeysekara', '2026-09-18', 100, 4]
        ],
        'Alpha Ops': [
            ['Terraform modules for VPC', 'Development', 'done', 'Kavinda Jayasuriya', '2026-08-25', 100, 2],
            ['CI/CD with GitHub Actions', 'Development', 'done', 'Malsha Karunaratne', '2026-09-15', 100, 3],
            ['Blue-green deployment on K8s', 'Development', 'progress', 'Sahan Liyanage', '2026-10-10', 70, 4],
            ['Grafana alerts & dashboards', 'Testing', 'review', 'Tharindu Wickramaratne', '2026-10-12', 100, 1],
            ['Runbook for the ops team', 'Documentation', 'todo', 'Kavinda Jayasuriya', '2026-10-25', 0, 0]
        ]
    };
    const SEED_CHAT = {
        'Nexus Systems': [
            ['Sanduni Perera', 'Morning everyone! Sprint 2 review is on Friday. Please move finished cards to Review.', '09:05'],
            ['Nimal Silva', 'Forecast model v2 is training now, accuracy is already better than v1 👌', '09:20'],
            ['Amaya Dissanayake', 'Dashboard prototype is ready. Link is on the Review card.', '10:02']
        ],
        'Vortex Group': [
            ['Malith Senanayake', 'We are behind on Phase 1. Let’s finish the contract tests first.', '08:40'],
            ['Kasun Madushanka', 'Working on it, 2 tests still failing. Should be done by tonight.', '09:15']
        ],
        'Quantum Analytics': [
            ['Erandi Weerasinghe', 'Final report submitted. Thank you all for the great work! 🎉', '16:30']
        ],
        'Alpha Ops': [
            ['Kavinda Jayasuriya', 'Cluster is now in production. Please keep an eye on the alerts.', '11:10'],
            ['Tharindu Wickramaratne', 'Grafana alerts are set up, moved the card to Review.', '11:45']
        ]
    };

    const esc = t => { const d = document.createElement('div'); d.textContent = t == null ? '' : String(t); return d.innerHTML; };
    const initials = n => String(n || '').trim().split(/\s+/).slice(0, 2).map(w => w.charAt(0).toUpperCase()).join('');
    const colorFor = n => { let h = 0; for (const c of String(n)) h = (h * 31 + c.charCodeAt(0)) >>> 0; return COLORS[h % COLORS.length]; };
    const av = n => `<span class="tm-av" title="${esc(n)}" style="background:${colorFor(n)};">${esc(initials(n))}</span>`;
    const load = (k, fallback) => { try { const v = JSON.parse(localStorage.getItem(k)); return v || fallback; } catch (e) { return fallback; } };
    const save = (k, v) => { try { localStorage.setItem(k, JSON.stringify(v)); } catch (e) {} };

    const list  = document.getElementById('tmListView');
    const page  = document.getElementById('tmWorkspace');
    const board = document.getElementById('twBoard');
    const msgs  = document.getElementById('twMsgs');
    const input = document.getElementById('twInput');
    const chat  = document.getElementById('twChat');
    const memberFilter = document.getElementById('twMemberFilter');
    const layout = document.getElementById('twLayout');
    const chatToggle = document.getElementById('twChatToggle');

    // 'tasks' = Kanban full width (no chat)   'chat' = Kanban + chat on the side
    function setMode(mode) {
        const chatOn = mode === 'chat';
        layout.classList.toggle('tasks-only', !chatOn);
        chatToggle.classList.toggle('on', chatOn);
        chatToggle.querySelector('span:last-child').textContent = chatOn ? 'Hide Chat' : 'Team Chat';
        if (chatOn) {
            msgs.scrollTop = msgs.scrollHeight;
            chat.classList.add('focus');
            setTimeout(() => chat.classList.remove('focus'), 1500);
            setTimeout(() => input.focus({ preventScroll: true }), 300);
        }
    }
    chatToggle.addEventListener('click', () => {
        const next = layout.classList.contains('tasks-only') ? 'chat' : 'tasks';
        setMode(next);
        if (next === 'chat' && window.innerWidth <= 1150) chat.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });

    let team = null, teamIndex = -1, tasks = [], messages = [], listScroll = 0;
    const taskKey = () => 'sb_team_tasks_' + team.name;
    const chatKey = () => 'sb_team_chat_' + team.name;
    const fileKey = () => 'sb_team_files_' + team.name;
    let files = [];
    const fileUrls = {};   // files uploaded in this visit can be opened (id -> object URL)

    function seedTasks() {
        return (SEED_TASKS[team.name] || []).map((r, i) => ({
            id: 'd' + i, title: r[0], tag: r[1], col: r[2], who: r[3], due: r[4], progress: r[5], comments: r[6]
        }));
    }
    // helpers used by the Delay Review popup
    window.tmTasksFor = function (i) {
        const name = TEAMS[i].name;
        return load('sb_team_tasks_' + name, null) || (SEED_TASKS[name] || []).map((r, k) => ({
            id: 'd' + k, title: r[0], tag: r[1], col: r[2], who: r[3], due: r[4], progress: r[5], comments: r[6] }));
    };
    window.tmPostToChat = function (i, text) {
        const name = TEAMS[i].name;
        const list = load('sb_team_chat_' + name, null) ||
            [{ sys: true, text: 'Today' }].concat((SEED_CHAT[name] || []).map(r => ({ who: r[0], text: r[1], time: r[2] })));
        const now = new Date();
        list.push({ me: true, who: ORG_NAME, text: text,
                    time: String(now.getHours()).padStart(2, '0') + ':' + String(now.getMinutes()).padStart(2, '0') });
        save('sb_team_chat_' + name, list);
    };
    window.tmOpenWorkspace = (i, focus) => openWorkspace(i, focus);

    function seedChat() {
        const base = [{ sys: true, text: 'Today' }];
        return base.concat((SEED_CHAT[team.name] || []).map(r => ({ who: r[0], text: r[1], time: r[2] })));
    }

    // ---------------- open / close ----------------
    function openWorkspace(i, focus, push) {
        team = TEAMS[i]; teamIndex = i;
        if (team && window.tmEffective) team = Object.assign(window.tmEffective(i, team), { name: TEAMS[i].name, displayName: window.tmEffective(i, team).name });
        if (!team) return;

        tasks    = load(taskKey(), null) || seedTasks();
        messages = load(chatKey(), null) || seedChat();

        document.getElementById('twCrumb').textContent = team.displayName || team.name;
        document.getElementById('twPhase').textContent = team.phase || 'Active Sprint';
        document.getElementById('twProject').textContent = team.project;
        document.getElementById('twTeamName').textContent = (team.displayName || team.name) + ' · ' + team.members.length + ' members';
        document.getElementById('twAvatars').innerHTML = team.members.slice(0, 5).map(m => av(m.name)).join('');

        memberFilter.innerHTML = '<option value="all">All members</option>' +
            team.members.map(m => `<option value="${esc(m.name)}">${esc(m.name)}</option>`).join('');
        document.getElementById('twTWho').innerHTML = '<option value="">Unassigned</option>' +
            team.members.map(m => `<option value="${esc(m.name)}">${esc(m.name)}</option>`).join('');

        files = load(fileKey(), null) || seedFiles();
        renderBoard();
        renderChat();
        renderFiles();

        listScroll = window.scrollY;
        list.hidden = true;
        page.hidden = false;

        if (push !== false) history.pushState({ teamWork: i, focus: focus }, '', '#team-' + i + '-' + focus);

        window.scrollTo(0, 0);
        setMode(focus === 'chat' ? 'chat' : 'tasks');
        if (focus === 'chat' && window.innerWidth <= 1150) chat.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    function closeWorkspace() {
        page.hidden = true;
        list.hidden = false;
        window.scrollTo(0, listScroll);
    }

    document.addEventListener('click', e => {
        const b = e.target.closest('[data-work-team]');
        if (b) openWorkspace(Number(b.dataset.workTeam), b.dataset.workFocus);
    });

    document.getElementById('twBack').addEventListener('click', function () {
        if (history.state && history.state.teamWork !== undefined) history.back();
        else { closeWorkspace(); history.replaceState(null, '', location.pathname); }
    });
    window.addEventListener('popstate', function (e) {
        if (e.state && e.state.teamWork !== undefined) openWorkspace(e.state.teamWork, e.state.focus, false);
        else if (!page.hidden) closeWorkspace();
    });

    // ---------------- Kanban ----------------
    function dueLabel(t) {
        if (!t.due) return '';
        const days = Math.round((new Date(t.due + 'T00:00:00') - new Date(new Date().toDateString())) / 86400000);
        if (t.col === 'done') return new Date(t.due + 'T00:00:00').toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
        if (days < 0)  return `<span class="late">${Math.abs(days)}d overdue</span>`;
        if (days === 0) return '<span class="late">Due today</span>';
        if (days === 1) return 'Due tomorrow';
        return 'Due in ' + days + 'd';
    }

    function renderBoard() {
        const who = memberFilter.value;
        board.innerHTML = COLS.map(c => {
            const items = tasks.filter(t => t.col === c.key && (who === 'all' || t.who === who));
            return `
            <div class="tw-col" data-col="${c.key}">
                <div class="tw-col-head">
                    <span><span class="dot" style="background:${c.dot}"></span>${c.label}</span>
                    <span class="tw-col-count">${items.length}</span>
                </div>
                ${items.length ? items.map(t => `
                    <div class="tw-card ${t.col}" draggable="true" data-id="${esc(t.id)}">
                        <button type="button" class="tw-kebab" data-menu="${esc(t.id)}" aria-label="Task options">⋯</button>
                        <span class="tw-tag ${esc(t.tag)}">${esc(t.tag)}</span>
                        <div class="tw-card-title">${esc(t.title)}</div>
                        ${t.col === 'progress' ? `<div class="tw-card-bar"><span style="width:${Number(t.progress) || 0}%"></span></div>` : ''}
                        <div class="tw-card-foot">
                            <span class="left">${t.due ? '<span class="material-symbols-outlined">event</span>' + dueLabel(t) : ''}
                                ${t.comments ? ' &nbsp;<span class="material-symbols-outlined">chat_bubble</span>' + Number(t.comments) : ''}</span>
                            ${t.who ? av(t.who) : ''}
                        </div>
                    </div>`).join('') : '<div class="tw-col-empty">No tasks</div>'}
            </div>`;
        }).join('');

        const done = tasks.filter(t => t.col === 'done').length;
        const pct = tasks.length ? Math.round(done / tasks.length * 100) : 0;
        document.getElementById('twPct').textContent = pct + '%';
        document.getElementById('twBar').style.width = pct + '%';
        document.getElementById('twDone').textContent = done + ' tasks done';
        document.getElementById('twPending').textContent = (tasks.length - done) + ' pending';
    }

    function moveTask(id, col) {
        const t = tasks.find(x => x.id === id);
        if (!t || t.col === col) return;
        t.col = col;
        if (col === 'done' || col === 'review') t.progress = 100;
        if (col === 'progress' && !t.progress) t.progress = 10;
        if (col === 'todo') t.progress = 0;
        save(taskKey(), tasks);
        renderBoard();
    }

    memberFilter.addEventListener('change', renderBoard);

    // drag & drop
    board.addEventListener('dragstart', e => {
        const card = e.target.closest('.tw-card'); if (!card) return;
        card.classList.add('dragging');
        e.dataTransfer.setData('text/plain', card.dataset.id);
    });
    board.addEventListener('dragend', e => { const c = e.target.closest('.tw-card'); if (c) c.classList.remove('dragging'); });
    board.addEventListener('dragover', e => {
        const col = e.target.closest('.tw-col'); if (!col) return;
        e.preventDefault();
        board.querySelectorAll('.tw-col').forEach(c => c.classList.toggle('drop', c === col));
    });
    board.addEventListener('dragleave', e => { if (!board.contains(e.relatedTarget)) board.querySelectorAll('.tw-col').forEach(c => c.classList.remove('drop')); });
    board.addEventListener('drop', e => {
        const col = e.target.closest('.tw-col'); if (!col) return;
        e.preventDefault();
        board.querySelectorAll('.tw-col').forEach(c => c.classList.remove('drop'));
        moveTask(e.dataTransfer.getData('text/plain'), col.dataset.col);
    });

    // ⋯ menu (move / delete) – also works on phones
    board.addEventListener('click', e => {
        const k = e.target.closest('[data-menu]');
        board.querySelectorAll('.tw-menu').forEach(mn => mn.remove());
        if (!k) return;
        e.stopPropagation();
        const id = k.dataset.menu;
        const t = tasks.find(x => x.id === id);
        const menu = document.createElement('div');
        menu.className = 'tw-menu';
        menu.innerHTML = COLS.filter(c => c.key !== t.col).map(c =>
            `<button type="button" data-move="${c.key}"><span class="material-symbols-outlined">arrow_forward</span>Move to ${c.label}</button>`).join('') +
            '<hr><button type="button" class="danger" data-del><span class="material-symbols-outlined">delete</span>Delete task</button>';
        menu.addEventListener('click', ev => {
            ev.stopPropagation();
            const mv = ev.target.closest('[data-move]');
            if (mv) moveTask(id, mv.dataset.move);
            if (ev.target.closest('[data-del]') && confirm('Delete this task?')) {
                tasks = tasks.filter(x => x.id !== id); save(taskKey(), tasks); renderBoard();
            }
        });
        k.closest('.tw-card').appendChild(menu);
    });
    document.addEventListener('click', () => board.querySelectorAll('.tw-menu').forEach(mn => mn.remove()));

    // New Task popup
    const taskModal = document.getElementById('twTaskModal');
    const taskForm  = document.getElementById('twTaskForm');
    document.getElementById('twNewTask').addEventListener('click', () => {
        taskForm.reset(); document.getElementById('twTErr').textContent = '';
        taskModal.classList.add('open');
        setTimeout(() => document.getElementById('twTTitle').focus(), 50);
    });
    taskModal.querySelectorAll('[data-close-task]').forEach(b => b.addEventListener('click', () => taskModal.classList.remove('open')));
    taskModal.addEventListener('click', e => { if (e.target === taskModal) taskModal.classList.remove('open'); });
    taskForm.addEventListener('submit', e => {
        e.preventDefault();
        const title = document.getElementById('twTTitle').value.trim();
        if (!title) { document.getElementById('twTErr').textContent = 'Please enter a task title.'; return; }
        const col = document.getElementById('twTCol').value;
        tasks.push({
            id: 'n' + Date.now(), title: title, tag: document.getElementById('twTTag').value, col: col,
            who: document.getElementById('twTWho').value, due: document.getElementById('twTDue').value,
            progress: col === 'done' || col === 'review' ? 100 : (col === 'progress' ? 10 : 0), comments: 0
        });
        save(taskKey(), tasks);
        renderBoard();
        taskModal.classList.remove('open');
    });

    // ---------------- Shared Assets ----------------
    const SEED_FILES = {
        'Nexus Systems':     [['Supplier_Data_Analysis.pdf', 2.4, 'Chamod Fernando', '2 days ago'], ['Dashboard_Prototype.fig', 8.1, 'Amaya Dissanayake', 'Yesterday'], ['Forecast_Model_v2.zip', 6.7, 'Nimal Silva', '2h ago']],
        'Vortex Group':      [['Threat_Model.pdf', 1.2, 'Malith Senanayake', '3 days ago'], ['Contract_Architecture.png', 0.9, 'Kasun Madushanka', '5 days ago']],
        'Quantum Analytics': [['Final_Report.pdf', 4.2, 'Yasith Abeysekara', 'Sep 18'], ['Demo_Video.mp4', 18.5, 'Nethmi Samarasinghe', 'Sep 16']],
        'Alpha Ops':         [['Infrastructure_Diagram.png', 1.6, 'Kavinda Jayasuriya', 'Yesterday'], ['Ops_Runbook_Draft.docx', 0.4, 'Malsha Karunaratne', '4h ago']]
    };
    function seedFiles() {
        return (SEED_FILES[team.name] || []).map((f, i) => ({ id: 's' + i, name: f[0], size: f[1] * 1024 * 1024, by: f[2], when: f[3] }));
    }
    function fileType(name) {
        const ext = (name.split('.').pop() || '').toLowerCase();
        if (ext === 'pdf') return ['pdf', 'picture_as_pdf'];
        if (['png', 'jpg', 'jpeg', 'gif', 'webp', 'fig'].includes(ext)) return ['img', 'image'];
        if (['doc', 'docx', 'txt', 'ppt', 'pptx', 'xls', 'xlsx', 'csv'].includes(ext)) return ['doc', 'description'];
        if (ext === 'mp4') return ['vid', 'movie'];
        if (ext === 'zip') return ['zip', 'folder_zip'];
        return ['other', 'draft'];
    }
    const sizeText = b => b < 1024 * 1024 ? Math.max(1, Math.round(b / 1024)) + ' KB' : (b / 1024 / 1024).toFixed(1) + ' MB';

    function renderFiles() {
        const box = document.getElementById('twFiles');
        document.getElementById('twFilesCount').textContent = files.length + ' file' + (files.length === 1 ? '' : 's');
        box.innerHTML = files.length ? files.map(f => {
            const [cls, icon] = fileType(f.name);
            const canOpen = !!fileUrls[f.id];
            return `
            <div class="tw-file">
                <div class="tw-file-icon ${cls}"><span class="material-symbols-outlined">${icon}</span></div>
                <div class="tw-file-info">
                    <div class="tw-file-name" title="${esc(f.name)}">${esc(f.name)}</div>
                    <div class="tw-file-meta">${sizeText(f.size)} · ${esc(f.by)} · ${esc(f.when)}</div>
                </div>
                <button type="button" class="tw-file-btn" data-open-file="${esc(f.id)}" ${canOpen ? '' : 'disabled'}
                        title="${canOpen ? 'Open / download' : 'Demo file'}"><span class="material-symbols-outlined">download</span></button>
                <button type="button" class="tw-file-btn danger" data-del-file="${esc(f.id)}" title="Remove"><span class="material-symbols-outlined">delete</span></button>
            </div>`;
        }).join('') : '<div class="tw-files-empty">No files shared yet.</div>';
    }

    function addFiles(list) {
        const errEl = document.getElementById('twUploadErr');
        const errors = [];
        Array.from(list).forEach(file => {
            if (file.size > 10 * 1024 * 1024) { errors.push(`"${file.name}" is larger than 10MB.`); return; }
            const id = 'u' + Date.now() + Math.random().toString(36).slice(2, 6);
            fileUrls[id] = URL.createObjectURL(file);
            files.unshift({ id: id, name: file.name, size: file.size, by: 'You', when: 'Just now' });
        });
        errEl.textContent = errors.join(' ');
        save(fileKey(), files);
        renderFiles();
    }

    const drop = document.getElementById('twDrop');
    const fileInput = document.getElementById('twFileInput');
    fileInput.addEventListener('change', () => { addFiles(fileInput.files); fileInput.value = ''; });
    ['dragenter', 'dragover'].forEach(ev => drop.addEventListener(ev, e => { e.preventDefault(); drop.classList.add('over'); }));
    ['dragleave', 'drop'].forEach(ev => drop.addEventListener(ev, e => { e.preventDefault(); drop.classList.remove('over'); }));
    drop.addEventListener('drop', e => addFiles(e.dataTransfer.files));

    document.getElementById('twFiles').addEventListener('click', e => {
        const o = e.target.closest('[data-open-file]');
        if (o && fileUrls[o.dataset.openFile]) window.open(fileUrls[o.dataset.openFile], '_blank');
        const d = e.target.closest('[data-del-file]');
        if (d && confirm('Remove this file?')) {
            files = files.filter(f => f.id !== d.dataset.delFile);
            save(fileKey(), files);
            renderFiles();
        }
    });

    // ---------------- Chat ----------------
    function renderChat() {
        msgs.innerHTML = messages.map(mg => {
            if (mg.sys) return `<div class="tw-sys">${esc(mg.text)}</div>`;
            const me = mg.me === true;
            return `
            <div class="tw-msg ${me ? 'me' : ''}">
                <div class="tw-msg-meta">
                    ${me ? `<span>${esc(mg.time)}</span><strong>You</strong>` : `${av(mg.who)}<strong>${esc(mg.who)}</strong><span>${esc(mg.time)}</span>`}
                </div>
                <div class="tw-bubble">${esc(mg.text)}</div>
            </div>`;
        }).join('');
        msgs.scrollTop = msgs.scrollHeight;
    }

    document.getElementById('twSend').addEventListener('submit', e => {
        e.preventDefault();
        const text = input.value.trim();
        if (!text) return;
        const now = new Date();
        messages.push({ me: true, who: ORG_NAME, text: text,
                        time: String(now.getHours()).padStart(2, '0') + ':' + String(now.getMinutes()).padStart(2, '0') });
        save(chatKey(), messages);
        input.value = '';
        renderChat();
    });

    // (runs last, after everything above is ready)
    // open straight from a link like teams.php#team-1-chat (e.g. after refresh)
    const m = location.hash.match(/^#team-(\d+)-(tasks|chat)$/);
    if (m && TEAMS[Number(m[1])]) {
        history.replaceState({ teamWork: Number(m[1]), focus: m[2] }, '', location.hash);
        openWorkspace(Number(m[1]), m[2], false);
    }
})();


/* =================================================================
   ⋮ MENU – Edit Team, Change Status
   Database teams are saved on the server.
   Demo teams (not in the database) are changed in this browser only.
================================================================= */
(function () {
    const TEAMS = <?= json_encode(array_values(array_map(fn($t) => [
                    'id' => $t['id'] ?? null, 'project_id' => $t['project_id'] ?? null,
                    'name' => $t['name'], 'project' => $t['project'], 'state' => $t['state'],
                    'leader' => $t['leader'], 'leader_email' => $t['leader_email'] ?? null,
                    'member_emails' => $t['member_emails'] ?? [],
                    'deadline_raw' => $t['deadline_raw'] ?? '', 'deadline' => $t['deadline'],
                    'skills' => $t['skills'], 'project_deadline_raw' => $t['project_deadline_raw'] ?? '',
                    'members' => array_map(fn($m) => ['name' => $m['name'], 'email' => strtolower($m['email'] ?? ''), 'role' => $m['role'], 'leader' => !empty($m['leader'])],
                                           $t['details']['members'] ?? []),
                  ], $teams)), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?>;
    const CANDIDATES = <?= json_encode($tmCandidates, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?>;
    const PROJECT_MAX = <?= json_encode(array_column(array_map(fn($p) => ['id' => (int)$p['id'], 'm' => (int)$p['members']], $tmProjects), 'm', 'id')) ?>;

    const STATUS = { ontrack: ['On Track', '#16a34a'], behind: ['Behind Schedule', '#dc2626'], completed: ['Completed', '#6b7280'] };
    const COLORS = ['#1e3a5f', '#0f766e', '#7c3aed', '#b45309', '#be185d', '#2563eb', '#475569'];
    const OV_KEY = 'sb_demo_team_overrides';

    const esc = t => { const d = document.createElement('div'); d.textContent = t == null ? '' : String(t); return d.innerHTML; };
    const initials = n => String(n || '').trim().split(/\s+/).slice(0, 2).map(w => w.charAt(0).toUpperCase()).join('');
    const colorFor = n => { let h = 0; for (const c of String(n)) h = (h * 31 + c.charCodeAt(0)) >>> 0; return COLORS[h % COLORS.length]; };
    const loadOv = () => { try { return JSON.parse(localStorage.getItem(OV_KEY)) || {}; } catch (e) { return {}; } };
    const saveOv = o => { try { localStorage.setItem(OV_KEY, JSON.stringify(o)); } catch (e) {} };
    const isDb = t => t.id !== null && t.id !== undefined;
    const fmtShort = ymd => ymd ? new Date(ymd + 'T00:00:00').toLocaleDateString('en-US', { month: 'short', day: '2-digit' }) : '';

    // The popups for "View" / Kanban read the edited demo name + status from here too
    window.tmEffective = function (i, t) {
        const o = loadOv()[TEAMS[i] && TEAMS[i].name] || {};
        return Object.assign({}, t, o.name ? { name: o.name } : {},
            o.state ? { state: o.state, status: STATUS[o.state][0] } : {});
    };

    // ---------- buttons at the bottom of a card (depend on the status) ----------
    //   On Track -> View, Task, Chat | Behind Schedule -> View, Review, Chat | Completed -> View Final Report
    const ACTIONS = {
        ontrack:   ['View', 'Task', 'Chat'],
        behind:    ['View', 'Review', 'Chat'],
        completed: ['Final Report']
    };
    const ACTION_ICON = { 'View': 'visibility', 'Task': 'task_alt', 'Chat': 'chat_bubble', 'Review': 'rate_review', 'Final Report': 'description' };

    function renderActions(card, i, state) {
        const box = card.querySelector('.tm-actions');
        if (!box) return;
        const list = ACTIONS[state] || ACTIONS.ontrack;
        box.classList.toggle('single', list.length === 1);
        box.innerHTML = list.map((a, k) => {
            const cls  = a === 'Final Report' ? 'report' : (k === 0 ? 'solid' : '');
            const data = (a === 'View' || a === 'Final Report') ? `data-view-team="${i}"`
                       : (a === 'Task' || a === 'Chat') ? `data-work-team="${i}" data-work-focus="${a === 'Chat' ? 'chat' : 'tasks'}"`
                       : `data-review-team="${i}"`;
            return `<button type="button" class="tm-btn ${cls}" ${data}>` +
                   `<span class="material-symbols-outlined">${ACTION_ICON[a]}</span>${a === 'Final Report' ? 'View Final Report' : a}</button>`;
        }).join('');
    }

    // ---------- update a card on the page ----------
    function applyToCard(i) {
        const t = TEAMS[i];
        const o = loadOv()[t.name];
        const card = document.querySelector('.tm-card[data-team-index="' + i + '"]');
        if (!o || !card) return;


        if (o.name) card.querySelector('.tm-name').textContent = o.name;

        if (o.state) {
            const pill = card.querySelector('.tm-pill:not(.new)');
            pill.className = 'tm-pill ' + o.state;
            pill.textContent = STATUS[o.state][0];
            card.dataset.state = o.state;
            card.classList.toggle('completed', o.state === 'completed');
            const box = card.querySelector('.tm-progress');
            box.classList.remove('behind', 'completed');
            if (o.state !== 'ontrack') box.classList.add(o.state);
            renderActions(card, i, o.state);
        }

        if (o.members) {
            const all = t.members.filter(m => o.members.includes(m.name));
            const leader = all.find(m => m.name === o.leader) || all[0];
            if (leader) {
                card.querySelector('.tm-leader-name').textContent = leader.name;
                card.querySelector('.tm-leader-role').textContent = (o.roles && o.roles[leader.name]) || leader.role;
                const la = card.querySelector('.tm-leader .tm-av');
                la.textContent = initials(leader.name);
                la.style.background = colorFor(leader.name);
            }
            const others = all.filter(m => m !== leader);
            card.querySelector('.tm-stack').innerHTML =
                others.slice(0, 3).map(m => `<div class="tm-av" title="${esc(m.name)}" style="background:${colorFor(m.name)};">${esc(initials(m.name))}</div>`).join('') +
                (others.length > 3 ? `<div class="tm-av more">+${others.length - 3}</div>` : '');
        }

        if (o.skills) {
            let box = card.querySelector('.tm-skills');
            if (!box && o.skills.length) {
                box = document.createElement('div');
                box.className = 'tm-skills';
                box.innerHTML = '<div class="tm-label">Skills Covered</div><div class="tm-chips"></div>';
                card.querySelector('.tm-progress').before(box);
            }
            if (box) {
                box.hidden = !o.skills.length;
                box.querySelector('.tm-chips').innerHTML = o.skills.map(sk => `<span class="tm-chip">${esc(sk)}</span>`).join('');
            }
        }

        if (o.deadline) {
            const left = card.querySelector('.tm-meta .left');
            if (left) left.innerHTML = '<span class="material-symbols-outlined">calendar_today</span>Deadline: ' + esc(fmtShort(o.deadline));
            card.dataset.deadline = Math.floor(new Date(o.deadline + 'T00:00:00').getTime() / 1000);
            // "X days left" / "X days delayed" next to the deadline
            const right = card.querySelector('.tm-meta .right');
            if (right) {
                const days = Math.round((new Date(o.deadline + 'T00:00:00') - new Date(new Date().toDateString())) / 86400000);
                const tone = days < 0 ? 'late' : (days <= 14 ? 'warn' : 'ok');
                right.className = 'right ' + tone;
                right.innerHTML = '<span class="material-symbols-outlined">' + (days < 0 ? 'warning' : 'schedule') + '</span>' +
                    (days < 0 ? Math.abs(days) + ' days delayed' : days + ' day' + (days === 1 ? '' : 's') + ' left');
            }
        }

        card.dataset.search = [card.querySelector('.tm-name').textContent, t.project,
            card.querySelector('.tm-leader-name').textContent,
            ...Array.from(card.querySelectorAll('.tm-stack .tm-av[title]')).map(a => a.title)].join(' ').toLowerCase();
        document.dispatchEvent(new Event('tm-refilter'));
    }
    TEAMS.forEach((t, i) => { if (!isDb(t)) applyToCard(i); });

    window.tmDemoSave = (i, patch, msg) => demoSave(i, patch, msg);
    window.tmToast = msg => toast(msg);

    function demoSave(i, patch, msg) {
        const all = loadOv();
        all[TEAMS[i].name] = Object.assign(all[TEAMS[i].name] || {}, patch);
        saveOv(all);
        applyToCard(i);
        toast(msg);
    }

    function toast(msg) {
        const el = document.createElement('div');
        el.className = 'flash-toast flash-success';
        el.innerHTML = '<div class="flash-icon-wrap"><span class="material-symbols-outlined">check_circle</span></div>' +
                       '<div class="flash-content"><div class="flash-title" style="font-weight:700;font-size:14px;"></div></div>';
        el.querySelector('.flash-title').textContent = msg;
        document.body.appendChild(el);
        setTimeout(() => { el.style.transition = 'opacity .4s'; el.style.opacity = '0'; setTimeout(() => el.remove(), 400); }, 2500);
    }

    const actionForm = document.getElementById('tmActionForm');
    function postAction(action, id, status) {
        // elements.namedItem() because an input called "action" clashes with form.action
        actionForm.elements.namedItem('action').value = action;
        actionForm.elements.namedItem('team_id').value = id;
        actionForm.elements.namedItem('status').value = status || '';
        actionForm.submit();
    }

    // ---------- the ⋮ menu ----------
    function closeMenus() {
        document.querySelectorAll('.tm-menu').forEach(m => m.remove());
        document.querySelectorAll('.tm-kebab.open').forEach(k => k.classList.remove('open'));
    }

    document.querySelectorAll('[data-team-menu]').forEach(btn => btn.addEventListener('click', function (e) {
        e.stopPropagation();
        const wasOpen = btn.classList.contains('open');
        closeMenus();
        if (wasOpen) return;

        const i = Number(btn.dataset.teamMenu);
        const current = btn.closest('.tm-card').dataset.state;

        const menu = document.createElement('div');
        menu.className = 'tm-menu';
        menu.innerHTML =
            '<button type="button" data-act="edit"><span class="material-symbols-outlined">edit</span>Edit Team</button>' +
            '<hr><div class="tm-menu-label">Change Status</div>' +
            Object.keys(STATUS).map(k => `
                <button type="button" data-act="status" data-status="${k}">
                    <span class="st-dot" style="background:${STATUS[k][1]}"></span>${STATUS[k][0]}
                    ${k === current ? '<span class="material-symbols-outlined tm-check">check</span>' : ''}
                </button>`).join('');

        menu.addEventListener('click', ev => {
            ev.stopPropagation();
            const b = ev.target.closest('[data-act]');
            if (!b) return;
            closeMenus();
            if (b.dataset.act === 'edit') openEdit(i);
            if (b.dataset.act === 'status' && b.dataset.status !== current) {
                if (isDb(TEAMS[i])) postAction('team_status', TEAMS[i].id, b.dataset.status);
                else demoSave(i, { state: b.dataset.status }, 'Status changed to ' + STATUS[b.dataset.status][0]);
            }
        });

        btn.classList.add('open');
        btn.parentElement.appendChild(menu);
    }));
    document.addEventListener('click', closeMenus);
    document.addEventListener('keydown', e => { if (e.key === 'Escape') closeMenus(); });

    // ---------- Edit Team (name and deadline only) ----------
    const TODAY = <?= json_encode(date('Y-m-d')) ?>;
    const editModal = document.getElementById('tmEditModal');
    const editForm  = document.getElementById('tmEditForm');
    const teDeadline = document.getElementById('teDeadline');
    let editing = -1, editOldDeadline = '';

    const teErr = (id, msg) => { document.getElementById(id).textContent = msg || ''; };

    function openEdit(i) {
        editing = i;
        const t = TEAMS[i];
        const o = isDb(t) ? {} : (loadOv()[t.name] || {});
        document.getElementById('teId').value = isDb(t) ? t.id : '';
        document.getElementById('teProject').textContent = 'Project: ' + t.project;
        document.getElementById('teName').value = o.name || t.name;
        ['teErrName', 'teErrDeadline'].forEach(id => teErr(id, ''));
        editForm.querySelectorAll('.invalid').forEach(el => el.classList.remove('invalid'));

        editOldDeadline = isDb(t) ? (t.deadline_raw || '') : '';
        teDeadline.value = isDb(t) ? editOldDeadline : (o.deadline || '');

        // deadline: today ... project deadline (the current deadline can always be kept)
        teDeadline.min = editOldDeadline && editOldDeadline < TODAY ? editOldDeadline : TODAY;
        teDeadline.max = t.project_deadline_raw && t.project_deadline_raw >= TODAY ? t.project_deadline_raw : '';

        editModal.classList.add('open');
    }

    editModal.querySelectorAll('[data-close-edit]').forEach(b => b.addEventListener('click', () => editModal.classList.remove('open')));
    editModal.addEventListener('click', e => { if (e.target === editModal) editModal.classList.remove('open'); });

    editForm.addEventListener('submit', function (e) {
        const name = document.getElementById('teName').value.trim();
        const dl = teDeadline.value;
        let ok = true;

        if (!name) { teErr('teErrName', 'Please enter a team name.'); ok = false; }
        if (!dl) { teErr('teErrDeadline', 'Please pick a team deadline.'); ok = false; }
        else if (dl !== editOldDeadline && dl < TODAY) { teErr('teErrDeadline', 'The deadline can’t be in the past.'); ok = false; }
        else if (dl !== editOldDeadline && teDeadline.max && dl > teDeadline.max) { teErr('teErrDeadline', 'The team deadline can’t be after the project deadline.'); ok = false; }
        if (!ok) { e.preventDefault(); return; }

        if (!isDb(TEAMS[editing])) {          // demo team -> save in the browser
            e.preventDefault();
            demoSave(editing, { name: name, deadline: dl || undefined }, 'Team updated');
            editModal.classList.remove('open');
        }
        // database team -> normal form submit to teams.php
    });

})();


/* =================================================================
   DELAY REVIEW – "Review" button on Behind Schedule teams
   Shows why the team is late and lets the organization act on it.
================================================================= */
(function () {
    const TEAMS = <?= json_encode(array_values(array_map(fn($t) => [
                    'id' => $t['id'] ?? null, 'name' => $t['name'], 'project' => $t['project'],
                    'percent' => (int)$t['percent'], 'time' => $t['time'], 'deadline' => $t['deadline'],
                    'created' => $t['details']['created'] ?? '', 'team_deadline' => $t['details']['team_deadline'] ?? '',
                    'members' => array_map(fn($m) => ['name' => $m['name'], 'role' => $m['role'], 'progress' => (int)$m['progress']],
                                           $t['details']['members'] ?? []),
                  ], $teams)), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?>;
    const COLORS = ['#1e3a5f', '#0f766e', '#7c3aed', '#b45309', '#be185d', '#2563eb', '#475569'];

    const modal = document.getElementById('tmReviewModal');
    const body  = document.getElementById('trBody');
    const esc = t => { const d = document.createElement('div'); d.textContent = t == null ? '' : String(t); return d.innerHTML; };
    const initials = n => String(n || '').trim().split(/\s+/).slice(0, 2).map(w => w.charAt(0).toUpperCase()).join('');
    const colorFor = n => { let h = 0; for (const c of String(n)) h = (h * 31 + c.charCodeAt(0)) >>> 0; return COLORS[h % COLORS.length]; };
    const av = n => `<span class="tm-av" style="background:${colorFor(n)};">${esc(initials(n))}</span>`;
    const today = new Date(new Date().toDateString());
    const toDate = str => { const d = new Date(str); return isNaN(d) ? null : new Date(d.toDateString()); };
    const ymd = d => d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');

    let current = -1;

    function open(i) {
        current = i;
        const base = TEAMS[i];
        const t = window.tmEffective ? Object.assign({}, base, window.tmEffective(i, base)) : base;

        document.getElementById('trName').textContent = t.name;
        document.getElementById('trProject').innerHTML = 'Project: <strong>' + esc(t.project) + '</strong>';

        // expected progress = how much time of the plan has passed
        const start = toDate(t.created), end = toDate(t.team_deadline);
        let expected = 0;
        if (start && end && end > start) expected = Math.round(Math.min(1, Math.max(0, (today - start) / (end - start))) * 100);
        const actual = Math.max(0, Math.min(100, t.percent));
        const gap = Math.max(0, expected - actual);

        // overdue + at-risk tasks from the team's Kanban board
        const tasks = (window.tmTasksFor ? window.tmTasksFor(i) : []).filter(x => x.col !== 'done' && x.due);
        const late = [], risk = [];
        tasks.forEach(x => {
            const days = Math.round((new Date(x.due + 'T00:00:00') - today) / 86400000);
            if (days < 0) late.push(Object.assign({ days: -days }, x));
            else if (days <= 7) risk.push(Object.assign({ days: days }, x));
        });

        const members = t.members.slice().sort((a, b) => a.progress - b.progress);
        const weak = members.filter(m => m.progress < 50);

        const taskRow = (x, isLate) => `
            <div class="tr-task">
                ${x.who ? av(x.who) : '<span class="tm-av" style="background:#cbd5e1;">?</span>'}
                <div class="tr-task-info">
                    <div class="tr-task-title">${esc(x.title)}</div>
                    <div class="tr-task-sub">${esc(x.who || 'Unassigned')} · ${esc({ todo: 'To Do', progress: 'In Progress', review: 'Review' }[x.col] || '')}</div>
                </div>
                <span class="tr-flag ${isLate ? 'late' : 'risk'}">${isLate ? x.days + 'd overdue' : (x.days === 0 ? 'Due today' : 'Due in ' + x.days + 'd')}</span>
            </div>`;

        const reminder =
            `Hi team, our progress is ${actual}% but we should be around ${expected}% by now.` +
            (late.length ? `\n\nPlease finish these overdue tasks first:\n` + late.map(x => `• ${x.title} (${x.who || 'unassigned'})`).join('\n') : '') +
            `\n\nIf anything is blocking you, reply here so we can help.`;

        const minDate = new Date(today); minDate.setDate(minDate.getDate() + 1);
        const suggest = new Date((end && end > today ? end : today)); suggest.setDate(suggest.getDate() + 14);

        body.innerHTML = `
            <div class="tr-alert">
                <span class="material-symbols-outlined">warning</span>
                <div><strong>This team is behind schedule (${esc(t.time)}).</strong><br>
                     Progress is ${actual}% but ${expected}% of the planned time has already passed.
                     ${late.length ? late.length + ' task' + (late.length === 1 ? ' is' : 's are') + ' overdue.' : ''}</div>
            </div>

            <div class="tv-stats">
                <div class="tv-stat"><div class="tv-stat-icon sky"><span class="material-symbols-outlined">schedule</span></div>
                    <div><div class="tv-stat-label">Expected</div><div class="tv-stat-value">${expected}%</div></div></div>
                <div class="tv-stat"><div class="tv-stat-icon" style="background:#fee2e2;color:#dc2626;"><span class="material-symbols-outlined">trending_down</span></div>
                    <div><div class="tv-stat-label">Actual</div><div class="tv-stat-value">${actual}%</div></div></div>
                <div class="tv-stat"><div class="tv-stat-icon" style="background:#fef3c7;color:#b45309;"><span class="material-symbols-outlined">difference</span></div>
                    <div><div class="tv-stat-label">Gap</div><div class="tv-stat-value">${gap}%</div></div></div>
                <div class="tv-stat"><div class="tv-stat-icon navy"><span class="material-symbols-outlined">assignment_late</span></div>
                    <div><div class="tv-stat-label">Overdue Tasks</div><div class="tv-stat-value">${late.length}</div></div></div>
            </div>

            <div class="tv-card">
                <h4><span class="material-symbols-outlined">insights</span> Progress vs Plan</h4>
                <div class="tr-compare-row"><span>Expected</span><div class="tr-bar expected"><span style="width:${expected}%"></span></div><strong>${expected}%</strong></div>
                <div class="tr-compare-row"><span>Actual</span><div class="tr-bar actual"><span style="width:${actual}%"></span></div><strong>${actual}%</strong></div>
                <div class="tr-gap-note">Started ${esc(t.created || '-')} · Deadline ${esc(t.team_deadline || t.deadline || '-')} · The team is <b>${gap}%</b> behind the plan.</div>
            </div>

            <div class="tv-two" style="margin-bottom:14px;">
                <div class="tv-card">
                    <h4><span class="material-symbols-outlined">assignment_late</span> Overdue &amp; At-risk Tasks</h4>
                    ${late.length || risk.length ? late.map(x => taskRow(x, true)).join('') + risk.map(x => taskRow(x, false)).join('')
                                                 : '<div class="tv-empty">No overdue tasks on the board.</div>'}
                </div>
                <div class="tv-card">
                    <h4><span class="material-symbols-outlined">group</span> Member Contribution
                        <span class="tv-count">${weak.length ? weak.length + ' need support' : 'All on track'}</span></h4>
                    ${members.length ? members.map(m => `
                        <div class="tr-mem">
                            ${av(m.name)}
                            <span class="tr-mem-name" title="${esc(m.name)} · ${esc(m.role)}">${esc(m.name)}</span>
                            <div class="tv-bar ${m.progress < 50 ? 'low' : ''}"><span style="width:${m.progress}%"></span></div>
                            <span class="tr-mem-pct">${m.progress}%</span>
                            ${m.progress < 50 ? '<span class="tr-support">Needs support</span>' : ''}
                        </div>`).join('') : '<div class="tv-empty">No member details.</div>'}
                </div>
            </div>

            <div class="tv-card" style="margin-bottom:0;">
                <h4><span class="material-symbols-outlined">task_alt</span> Take Action</h4>
                <div class="tr-tabs">
                    <button type="button" class="on" data-pane="remind"><span class="material-symbols-outlined">campaign</span>Send Reminder</button>
                    <button type="button" data-pane="extend"><span class="material-symbols-outlined">event_repeat</span>Extend Deadline</button>
                </div>

                <div class="tr-pane" data-pane="remind">
                    <textarea id="trReminder" maxlength="1000">${esc(reminder)}</textarea>
                    <div class="tr-pane-foot">
                        <span class="tr-note">The message is posted in the team chat.</span>
                        <button type="button" class="tm-btn solid" id="trSendReminder"><span class="material-symbols-outlined">send</span>Send to Team</button>
                    </div>
                </div>

                <div class="tr-pane" data-pane="extend" hidden>
                    <div class="tm-grid-2">
                        <div class="tm-field">
                            <label class="tm-f-label" for="trNewDate">New Deadline *</label>
                            <input type="date" id="trNewDate" class="tm-input" min="${ymd(minDate)}" value="${ymd(suggest)}">
                        </div>
                        <div class="tm-field">
                            <label class="tm-f-label">Current Deadline</label>
                            <input type="text" class="tm-input" value="${esc(t.team_deadline || t.deadline || 'Not set')}" disabled>
                        </div>
                    </div>
                    <label class="tm-f-label" for="trReason">Reason *</label>
                    <textarea id="trReason" maxlength="300" placeholder="e.g. The smart contract audit took longer than planned."></textarea>
                    <div class="tm-err" id="trErr"></div>
                    <div class="tr-pane-foot">
                        <label class="tr-check"><input type="checkbox" id="trOnTrack" checked> Mark the team as On Track</label>
                        <button type="button" class="tm-btn solid" id="trExtend"><span class="material-symbols-outlined">event_available</span>Extend Deadline</button>
                    </div>
                </div>
            </div>
        `;

        // tabs
        body.querySelectorAll('.tr-tabs button').forEach(b => b.addEventListener('click', () => {
            body.querySelectorAll('.tr-tabs button').forEach(x => x.classList.toggle('on', x === b));
            body.querySelectorAll('.tr-pane').forEach(p => p.hidden = p.dataset.pane !== b.dataset.pane);
        }));

        // send reminder -> team chat
        document.getElementById('trSendReminder').addEventListener('click', () => {
            const text = document.getElementById('trReminder').value.trim();
            if (!text) return;
            if (window.tmPostToChat) window.tmPostToChat(i, text);
            if (window.tmToast) window.tmToast('Reminder sent to the team chat');
            close();
        });

        // extend deadline
        document.getElementById('trExtend').addEventListener('click', () => {
            const date = document.getElementById('trNewDate').value;
            const reason = document.getElementById('trReason').value.trim();
            const err = document.getElementById('trErr');
            if (!date || date < ymd(minDate)) { err.textContent = 'Please pick a date after today.'; return; }
            if (reason.length < 5) { err.textContent = 'Please write a short reason.'; return; }
            const onTrack = document.getElementById('trOnTrack').checked;

            if (base.id !== null && base.id !== undefined) {       // database team -> save on the server
                const f = document.getElementById('trExtendForm');
                f.elements.namedItem('team_id').value = base.id;
                f.elements.namedItem('deadline').value = date;
                f.elements.namedItem('reason').value = reason;
                f.elements.namedItem('set_ontrack').value = onTrack ? '1' : '';
                f.submit();
                return;
            }
            // demo team -> save in the browser + tell the team in chat
            const nice = new Date(date + 'T00:00:00').toLocaleDateString('en-US', { month: 'short', day: '2-digit', year: 'numeric' });
            if (window.tmPostToChat) window.tmPostToChat(i, `The team deadline has been extended to ${nice}. Reason: ${reason}`);
            if (window.tmDemoSave) window.tmDemoSave(i, Object.assign({ deadline: date }, onTrack ? { state: 'ontrack' } : {}), 'Deadline extended to ' + nice);
            close();
        });

        body.scrollTop = 0;
        modal.classList.add('open');
    }

    function close() { modal.classList.remove('open'); }

    document.addEventListener('click', e => {
        const b = e.target.closest('[data-review-team]');
        if (b) open(Number(b.dataset.reviewTeam));
    });
    modal.querySelectorAll('[data-close-review]').forEach(b => b.addEventListener('click', close));
    modal.addEventListener('click', e => { if (e.target === modal) close(); });
    document.addEventListener('keydown', e => { if (e.key === 'Escape' && modal.classList.contains('open')) close(); });
    document.getElementById('trOpenBoard').addEventListener('click', () => {
        close();
        if (window.tmOpenWorkspace) window.tmOpenWorkspace(current, 'tasks');
    });
})();


/* =================================================================
   OPENED FROM A NOTIFICATION
   teams.php?team=Vortex%20Group&open=review   (open = view | review | tasks | chat)
================================================================= */
(function () {
    const q = new URLSearchParams(location.search);
    const name = (q.get('team') || '').trim().toLowerCase();
    if (!name) return;
    const open = q.get('open') || 'view';
    history.replaceState(null, '', location.pathname);

    const card = document.querySelector('.tm-card[data-team-name="' + CSS.escape(name) + '"]');
    if (!card) return;
    const i = Number(card.dataset.teamIndex);

    if ((open === 'tasks' || open === 'chat') && window.tmOpenWorkspace) {
        window.tmOpenWorkspace(i, open);
        return;
    }
    card.scrollIntoView({ block: 'center' });
    card.style.transition = 'box-shadow .3s ease';
    card.style.boxShadow = '0 0 0 3px rgba(59,130,246,.45)';
    setTimeout(() => card.style.boxShadow = '', 2500);

    // Review only exists for Behind Schedule teams -> otherwise show the details
    const btn = (open === 'review' && card.querySelector('[data-review-team]')) || card.querySelector('[data-view-team]');
    if (btn) btn.click();
})();


/* hide the success / error message after a few seconds */
(function () {
    const t = document.getElementById('tmFlash');
    if (!t) return;
    setTimeout(function () {
        t.style.transition = 'opacity .4s ease, transform .4s ease';
        t.style.opacity = '0';
        t.style.transform = 'translateX(20px)';
        setTimeout(() => t.remove(), 400);
        history.replaceState(null, '', location.pathname);
    }, 3500);
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