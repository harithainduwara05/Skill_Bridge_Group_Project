<?php
/*
| Organization backend - Manage Projects: delete, On-Hold fix request, filters, pagination, stat cards
| Used by: Functions/Organization/manage_projects.php
| (the page already loaded Config/db.php + Session/Session.php and checked the role)
*/

require_once __DIR__ . '/project_validation.php';   // same project rules as Post / Edit Project
require_once __DIR__ . '/org_db.php';

// The Admin's latest "Hold" for a project (project_moderation_logs: the Admin's reason + our reply),
// added to a project row as hold_log_id, hold_reason, held_at, hold_response
const ORG_HOLD_COLUMNS = ", hl.id AS hold_log_id, hl.reason AS hold_reason, hl.created_at AS held_at,
           hl.org_response AS hold_response";
const ORG_HOLD_JOIN = " LEFT JOIN project_moderation_logs hl ON hl.id =
           (SELECT MAX(l.id) FROM project_moderation_logs l WHERE l.project_id = p.id AND l.action_type = 'Hold')";

// ORG_PROJECT_SELECT is "SELECT ... FROM projects p LEFT JOIN ..." -> put the hold columns before FROM
function orgProjectWithHoldSql(string $where): string
{
    $sql = str_replace("\n    FROM projects p", ORG_HOLD_COLUMNS . "\n    FROM projects p", ORG_PROJECT_SELECT);
    return $sql . ORG_HOLD_JOIN . " " . $where;
}

$user = current_user();
$organization_email = $user['email'];

// ---- Delete action (must run before any HTML/include output) ----
// A project can ONLY be deleted when it is:
//   - Rejected, or
//   - Draft, or
//   - Reviewing / Published with 0 applicants (nobody applied / nobody in the team)
if (isset($_GET['delete'])) {
    $delId = (int)$_GET['delete'];

    $dq = $conn->prepare("SELECT status FROM projects WHERE id = ? AND organization_email = ?");
    $dq->bind_param("is", $delId, $organization_email);
    $dq->execute();
    $delProject = $dq->get_result()->fetch_assoc();

    $delAllowed = false;
    if ($delProject) {
        // same status rules as the list below (lowercase, Admin's 'Active' / old 'accepted' = Published)
        $delStatus = strtolower(trim($delProject['status'] ?? ''));
        if (in_array($delStatus, ['active', 'approved', 'accepted'], true)) $delStatus = 'published';

        if (in_array($delStatus, ['rejected', 'draft'], true)) {
            $delAllowed = true;
        } elseif (in_array($delStatus, ['reviewing', 'published'], true)) {
            $cq = $conn->prepare("SELECT COUNT(*) FROM student_projects WHERE project_id = ?");
            $cq->bind_param("i", $delId);
            $cq->execute();
            $delApplicants = (int)$cq->get_result()->fetch_row()[0];
            try {
                $cq = $conn->prepare("SELECT COUNT(*) FROM project_proposals WHERE project_id = ?");
                $cq->bind_param("i", $delId);
                $cq->execute();
                $delApplicants += (int)$cq->get_result()->fetch_row()[0];
            } catch (Throwable $e) { /* count students in the team only */ }
            $delAllowed = ($delApplicants === 0);
        }
    }

    if ($delAllowed) {
        $delStmt = $conn->prepare("DELETE FROM projects WHERE id=? AND organization_email=?");
        $delStmt->bind_param("is", $delId, $organization_email);
        $delStmt->execute();
        header("Location: manage_projects.php?deleted=1");
    } else {
        header("Location: manage_projects.php?delete_blocked=1");
    }
    exit;
}

// ---- On Hold: organization fixes the project and REQUESTS the Admin to activate it ----
// The status stays 'hold' – only the Admin can make it Active.
// (must run before any HTML/include output because it redirects)

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'resolve_hold') {
    $holdId = (int)($_POST['project_id'] ?? 0);

    $hq = $conn->prepare(orgProjectWithHoldSql("WHERE p.id = ? AND p.organization_email = ? AND p.status = 'hold'"));
    $hq->bind_param("is", $holdId, $organization_email);
    $hq->execute();
    $holdProject = $hq->get_result()->fetch_assoc();

    if (!$holdProject) {
        header("Location: manage_projects.php?hold_error=1");
        exit;
    }

    // The fix form opens with the current details filled in.
    // A field left empty keeps its current value.
    $posted = function ($name) { return trim($_POST[$name] ?? ''); };
    $holdErrors = [];

    // saved deadline (DATE "2026-10-09") and duration (duration_weeks, a number) in the form's format
    $oldDeadline = (string)($holdProject['deadline'] ?? '');
    $dm = [(string)($holdProject['duration_weeks'] ?? '')];

    $title       = $posted('title')               !== '' ? $posted('title')               : (string)$holdProject['title'];
    $category    = $posted('category')            !== '' ? $posted('category')            : (string)$holdProject['category'];
    $keywords    = $posted('keywords')            !== '' ? $posted('keywords')            : (string)$holdProject['keywords'];
    $description = $posted('description')         !== '' ? $posted('description')         : (string)$holdProject['description'];
    $objectives  = $posted('learning_objectives') !== '' ? $posted('learning_objectives') : (string)$holdProject['learning_objectives'];
    $outcomes    = $posted('expected_outcomes')   !== '' ? $posted('expected_outcomes')   : (string)$holdProject['expected_outcomes'];
    $deadline    = $posted('deadline')            !== '' ? $posted('deadline')            : $oldDeadline;
    $membersRaw  = $posted('members')             !== '' ? $posted('members')             : (string)(int)$holdProject['members'];
    $durRaw      = $posted('duration_weeks')      !== '' ? $posted('duration_weeks')      : ($dm[0] ?? '');
    $response    = $posted('hold_response');

    // Students already in the team (Students Required can't be less)
    $tq = $conn->prepare("SELECT COUNT(*) FROM student_projects WHERE project_id = ?");
    $tq->bind_param("i", $holdId);
    $tq->execute();
    $inTeam = (int)$tq->get_result()->fetch_row()[0];

    // After the Admin activates it the project is Published again,
    // so it must pass the same rules as "Publish" in Post Project.
    // The deadline is always checked (it must still be after today, the hold may have taken days).
    $fieldErrors = validateProjectForm([
        'title' => $title, 'category' => $category, 'keywords' => $keywords,
        'description' => $description, 'learning_objectives' => $objectives,
        'expected_outcomes' => $outcomes, 'difficulty' => (string)($holdProject['difficulty'] ?: 'Intermediate'),
        'duration_weeks' => $durRaw, 'students_required' => $membersRaw,
        'preferred_year' => (string)($holdProject['preferred_year'] ?: 'Any Year'), 'deadline' => $deadline,
        'visibility' => (string)($holdProject['visibility'] ?: 'Public'),
    ], false, [
        'categories'   => array_values(orgCategories($conn)),
        'min_students' => $inTeam,
    ]);
    // difficulty / year / visibility are not in this form, so old values there are not blamed on it
    unset($fieldErrors['difficulty'], $fieldErrors['preferred_year'], $fieldErrors['visibility']);
    $holdErrors = array_merge($holdErrors, array_values($fieldErrors));

    // Message to the Admin: required, 10 - 500 characters
    $len = mb_strlen($response);
    if ($len === 0)                    $holdErrors[] = 'Please write a message to the Admin.';
    elseif ($len < 10 || $len > 500)   $holdErrors[] = 'The message to the Admin must be 10 to 500 characters.';

    if (!empty($holdErrors)) {
        // shown in the error toast after the redirect
        $_SESSION['hold_error_msg'] = $holdErrors[0];
        header("Location: manage_projects.php?hold_error=1");
        exit;
    }

    $skills     = cleanSkills($keywords);
    $members    = (int)$membersRaw;
    $duration   = (int)$durRaw;                          // duration_weeks
    $categoryId = orgCategoryId($conn, $category);
    $holdLogId  = (int)$holdProject['hold_log_id'];

    try {
        $conn->begin_transaction();

        // the fixed project details
        $up = $conn->prepare("UPDATE projects SET
                                  title = ?, category_id = ?, description = ?, learning_objectives = ?,
                                  expected_outcomes = ?, deadline = ?, members = ?, duration_weeks = ?
                              WHERE id = ? AND organization_email = ? AND status = 'hold'");
        $up->bind_param("sissssiiis",
            $title, $categoryId, $description, $objectives,
            $outcomes, $deadline, $members, $duration, $holdId, $organization_email);
        $up->execute();
        orgSaveProjectSkills($conn, $holdId, $skills);

        // the organization's answer is saved on the Admin's hold record (stays 'Open' until the Admin decides)
        if ($holdLogId > 0) {
            $ml = $conn->prepare("UPDATE project_moderation_logs SET org_response = ? WHERE id = ?");
            $ml->bind_param("si", $response, $holdLogId);
            $ml->execute();
        }
        $conn->commit();

        // Organization name for the notification message
        $on = $conn->prepare("SELECT Name FROM organization WHERE Email = ?");
        $on->bind_param("s", $organization_email);
        $on->execute();
        $orgName = $on->get_result()->fetch_assoc()['Name'] ?? $organization_email;

        // Notify every Admin: please activate
        $nTitle   = 'Activation Request: On-Hold Project';
        $nMessage = $orgName . ' fixed the on-hold project "' . $title . '" and is requesting to activate it. '
                  . 'Message: ' . $response;

        $nq = $conn->prepare("INSERT INTO notifications (Email, title, message, type, status)
                              SELECT Email, ?, ?, 'admin', 'Unread' FROM admin");
        $nq->bind_param("ss", $nTitle, $nMessage);
        $nq->execute();

        // ...and a copy for the organization itself, so it shows in its Notifications page
        $oTitle   = 'Activation Request Sent';
        $oMessage = 'You fixed the on-hold project "' . $title . '" and asked the Admin to activate it. It stays On Hold until the Admin decides.';
        $oq = $conn->prepare("INSERT INTO notifications (Email, title, message, type, status)
                              VALUES (?, ?, ?, 'project', 'Unread')");
        $oq->bind_param("sss", $organization_email, $oTitle, $oMessage);
        $oq->execute();

        header("Location: manage_projects.php?hold_resolved=1");
        exit;
    } catch (Throwable $e) {
        try { $conn->rollback(); } catch (Throwable $ignore) {}
        header("Location: manage_projects.php?hold_error=1");
        exit;
    }
}

$flash = null;
if (isset($_GET['posted'])) {
    $flash = ['type' => 'success', 'title' => 'Project sent to Admin', 'message' => 'Your project is now Reviewing. Students will see it only after the Admin accepts it.'];
} elseif (isset($_GET['drafted'])) {
    $flash = ['type' => 'success', 'title' => 'Draft saved', 'message' => 'Your project was saved as a Draft. Edit it and choose "Send to Admin" when it is ready.'];
} elseif (isset($_GET['submitted'])) {
    $flash = ['type' => 'success', 'title' => 'Project sent to Admin', 'message' => 'The Admin has been notified. Students will see it only after the Admin accepts it.'];
} elseif (isset($_GET['started_early'])) {
    $n = max(1, (int)$_GET['started_early']);
    $flash = ['type' => 'success', 'title' => 'Project is now Active',
              'message' => "The project started with the current team of $n student" . ($n === 1 ? '' : 's') . ", so Students Required was changed to $n. No more students can join."];
} elseif (isset($_GET['back_to_published'])) {
    $flash = ['type' => 'success', 'title' => 'Project updated – now Published', 'message' => 'Students Required is now more than the students in the team, so the project went back to Published. Students can join again; set it to Active when the team is full.'];
} elseif (isset($_GET['updated'])) {
    $flash = ['type' => 'success', 'title' => 'Project updated', 'message' => 'Your changes were saved successfully.'];
} elseif (isset($_GET['deleted'])) {
    $flash = ['type' => 'success', 'title' => 'Project deleted', 'message' => 'The project was deleted successfully.'];
} elseif (isset($_GET['delete_blocked'])) {
    $flash = ['type' => 'error', 'title' => 'Can’t delete this project', 'message' => 'Only Rejected / Draft projects, or Reviewing / Published projects with 0 applicants, can be deleted.'];
} elseif (isset($_GET['hold_resolved'])) {
    $flash = ['type' => 'success', 'title' => 'Activation request sent', 'message' => 'Your changes were saved and the Admin has been notified. The project stays On Hold until the Admin activates it.'];
} elseif (isset($_GET['hold_error'])) {
    // the exact problem found by the check above (or a general message)
    $flash = ['type' => 'error', 'title' => 'Activation request not sent',
              'message' => ($_SESSION['hold_error_msg'] ?? 'Please fill all required fields and try again.') . ' Open the hold popup and try again.'];
}
unset($_SESSION['hold_error_msg']);

// Project status flow:
//   Reviewing  -> new project, waiting for the Admin (students can't see it)
//   Published  -> Admin accepted it, students can see it and apply
//   Active     -> (DB value 'inprogress') the organization sets it manually from
//                 Edit Project, only after the required number of students joined
//   Completed  -> set by the organization when the project is finished
//   Hold / Rejected -> set only by the Admin

// ---- Filters ----
$statusFilter   = $_GET['status'] ?? 'all';
$timeFilter     = $_GET['time'] ?? 'all';      // all | daily | weekly | monthly
if (!in_array($timeFilter, ['all', 'daily', 'weekly', 'monthly'], true)) $timeFilter = 'all';
$searchQuery    = trim($_GET['search'] ?? '');   // from the header search box

// ---- Pagination ----
$perPage = 5;
$page    = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;

// ---- Fetch all projects posted by the logged-in organization ----
$realStmt = $conn->prepare(orgProjectWithHoldSql("WHERE p.organization_email = ? ORDER BY p.posted_at DESC"));
$realStmt->bind_param("s", $organization_email);
$realStmt->execute();
$allProjects = $realStmt->get_result()->fetch_all(MYSQLI_ASSOC);

// ---- Assigned-student counts (also used to flag over-capacity teams below) ----
$teamCounts = [];
$tcStmt = $conn->prepare("SELECT sp.project_id, COUNT(*) AS c FROM student_projects sp
                           JOIN projects p ON sp.project_id = p.id
                           WHERE p.organization_email = ?
                           GROUP BY sp.project_id");
$tcStmt->bind_param("s", $organization_email);
$tcStmt->execute();
foreach ($tcStmt->get_result()->fetch_all(MYSQLI_ASSOC) as $row) {
    $teamCounts[(int)$row['project_id']] = (int)$row['c'];
}

// The status is shown exactly as it is saved in the DB.
// (db_status is kept for the delete rules below.)
// (lowercase, because the Admin saves some statuses as 'Hold')
// Admin's Accept / Reactivate saves the status as 'Active' -> for the organization
// that means "Published" (old data may still say 'accepted'). The organization's own Active is 'inprogress'.
foreach ($allProjects as &$ap) {
    $ap['status']    = strtolower(trim($ap['status'] ?? ''));
    if (in_array($ap['status'], ['active', 'approved', 'accepted'], true)) $ap['status'] = 'published';

    // Active only when the team is full: an Active project whose team is not full
    // (old data, a student left, or Students Required was raised) is shown as Published.
    $needed = max(1, (int)($ap['members'] ?? 1));
    if ($ap['status'] === 'inprogress' && ($teamCounts[(int)$ap['id']] ?? 0) < $needed) {
        $ap['status'] = 'published';
    }
    $ap['db_status'] = $ap['status'];
}
unset($ap);

// ---- Applicant counts ----
// Applicants = students who sent a proposal (project_proposals)
//            + students already in the team (student_projects),
// each student counted only ONCE per project.
$applicantEmails = [];   // [project_id => [email => true]]

$teStmt = $conn->prepare("SELECT sp.project_id, sp.Email FROM student_projects sp
                          JOIN projects p ON sp.project_id = p.id
                          WHERE p.organization_email = ?");
$teStmt->bind_param("s", $organization_email);
$teStmt->execute();
foreach ($teStmt->get_result()->fetch_all(MYSQLI_ASSOC) as $row) {
    $applicantEmails[(int)$row['project_id']][strtolower(trim($row['Email']))] = true;
}

try {
    $apStmt = $conn->prepare("SELECT pa.project_id, pa.student_email AS Email FROM project_proposals pa
                              JOIN projects p ON pa.project_id = p.id
                              WHERE p.organization_email = ?");
    $apStmt->bind_param("s", $organization_email);
    $apStmt->execute();
    foreach ($apStmt->get_result()->fetch_all(MYSQLI_ASSOC) as $row) {
        $applicantEmails[(int)$row['project_id']][strtolower(trim($row['Email']))] = true;
    }
} catch (Throwable $e) {
    // proposals could not be read -> only team members are counted
}

function applicantCount($projectId) {
    global $applicantEmails;
    return count($applicantEmails[(int)$projectId] ?? []);
}

// Number shown in the "Applicants" column: a project that is still "Not Assigned"
// (no student in its team yet) always shows 0.
function shownApplicants($project) {
    return assignedCount($project) > 0 ? applicantCount($project['id']) : 0;
}

// Can the organization delete this project? Returns '' if yes, otherwise the reason why not.
function deleteBlockReason($project) {
    $status = $project['db_status'] ?? ($project['status'] ?? '');
    if (in_array($status, ['rejected', 'draft'], true)) return '';
    $apps = applicantCount($project['id']);
    if (in_array($status, ['reviewing', 'published'], true) && $apps === 0) return '';

    $label = statusLabel($status);
    if (in_array($status, ['reviewing', 'published'], true)) {
        return "This project already has $apps applicant" . ($apps === 1 ? '' : 's') . ", so it can’t be deleted.";
    }
    if ($status === 'inprogress') {
        return "This project has already been approved and students are joining it, so it can’t be deleted.";
    }
    return "This project is $label, so it can’t be deleted.";
}

// Assigned = students actually in the team, but never more than the number required.
// A Rejected project can't have a team, so it always shows "Not Assigned".
function assignedCount($project) {
    global $teamCounts;
    if (($project['db_status'] ?? $project['status'] ?? '') === 'rejected') return 0;
    $inTeam   = $teamCounts[(int)$project['id']] ?? 0;
    $required = (int)($project['members'] ?? 0);
    return $required > 0 ? min($inTeam, $required) : $inTeam;
}

$totalProjectsAll = count($allProjects);
// "Reviewing Projects" card = projects still waiting for the Admin
$reviewingProjects = count(array_filter($allProjects, fn($p) => ($p['status'] ?? '') === 'reviewing'));
// Time filter is based on the date the project was posted:
//   daily   = posted today
//   weekly  = posted in the last 7 days
//   monthly = posted in the last 30 days
$timeCutoff = null;
if ($timeFilter === 'daily')   $timeCutoff = strtotime('today');
if ($timeFilter === 'weekly')  $timeCutoff = strtotime('-7 days');
if ($timeFilter === 'monthly') $timeCutoff = strtotime('-30 days');

$filtered = array_values(array_filter($allProjects, function ($p) use ($statusFilter, $timeCutoff, $searchQuery) {
    if ($statusFilter !== 'all' && $p['status'] !== $statusFilter) return false;
    if ($timeCutoff !== null) {
        $postedTs = strtotime($p['posted_at'] ?? '');
        if ($postedTs === false || $postedTs < $timeCutoff) return false;
    }
    if ($searchQuery !== '') {
        // search in title, category and keywords/skills — matches the START of any word
        // (so "AI" finds "AI Chatbot" but not "Tailwind", and "flut" finds "Flutter")
        $haystack = ($p['title'] ?? '') . ' ' . ($p['category'] ?? '') . ' ' . ($p['keywords'] ?? '');
        if (!preg_match('/(?<![\p{L}\p{N}])' . preg_quote($searchQuery, '/') . '/iu', $haystack)) return false;
    }
    return true;
}));

$filteredTotal = count($filtered);
$totalPages    = max(1, (int)ceil($filteredTotal / $perPage));
$page          = min($page, $totalPages); // clamp if someone jumps past the last page
$offset        = ($page - 1) * $perPage;

$projects   = array_slice($filtered, $offset, $perPage);
$shownCount = count($projects);

// Some deadlines are stored as "Oct 20, 2026" (older seed data) and some as
// "2027-01-15" (from the date-picker on Post Project) — normalize both to
// the same "Mon DD, YYYY" display format wherever a deadline is shown.
function formatDeadline($raw) {
    if (empty($raw)) return '';
    $ts = strtotime($raw);
    return $ts !== false ? date('M d, Y', $ts) : $raw;
}

// Human-readable label for a status value (DB stores 'inprogress' for what's shown as "Active")
function statusLabel($status) {
    $labels = ['reviewing' => 'Reviewing', 'published' => 'Published', 'inprogress' => 'Active', 'hold' => 'Hold', 'rejected' => 'Rejected', 'completed' => 'Completed', 'draft' => 'Draft'];
    return $labels[$status] ?? ucfirst($status);
}

// Helper to build a pagination link that keeps the current filters
function buildPageUrl($pageNum, $statusFilter, $timeFilter) {
    global $searchQuery;
    $query = [
        'status'   => $statusFilter,
        'time'     => $timeFilter,
        'page'     => $pageNum,
    ];
    if ($searchQuery !== '') $query['search'] = $searchQuery;
    return '?' . http_build_query($query);
}

// ---- Summary stat cards (real data) ----
// "Active Projects" = projects the organization set to Active
$activeProjects = count(array_filter($allProjects, fn($p) => ($p['status'] ?? '') === 'inprogress'));

// "Assigned Teams" = projects whose team is COMPLETE (all the students it needs have joined).
// Same rule as the Active badge; Rejected and Hold projects are not counted.
$assignedTeams = 0;
foreach ($allProjects as $ap) {
    $dbStatus = $ap['db_status'] ?? $ap['status'];
    $needed   = (int)($ap['members'] ?? 0);
    if (in_array($dbStatus, ['rejected', 'hold'], true) || $needed < 1) continue;
    if (($teamCounts[(int)$ap['id']] ?? 0) >= $needed) $assignedTeams++;
}