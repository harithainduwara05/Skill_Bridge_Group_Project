<?php
/*
| Organization backend - Manage Projects: delete, On-Hold fix request, filters, pagination, stat cards
| Used by: Functions/Organization/manage_projects.php
| (the page already loaded Config/db.php + Session/Session.php and checked the role)
*/

$user = current_user();
$organization_email = $user['email'];

// ---- Delete action (must run before any HTML/include output) ----
// A project can ONLY be deleted when it is:
//   - Rejected, or
//   - Reviewing with 0 applicants (nobody applied / nobody in the team)
if (isset($_GET['delete'])) {
    $delId = (int)$_GET['delete'];

    $dq = $conn->prepare("SELECT status FROM projects WHERE id = ? AND organization_email = ?");
    $dq->bind_param("is", $delId, $organization_email);
    $dq->execute();
    $delProject = $dq->get_result()->fetch_assoc();

    $delAllowed = false;
    if ($delProject) {
        if ($delProject['status'] === 'rejected') {
            $delAllowed = true;
        } elseif ($delProject['status'] === 'reviewing') {
            $cq = $conn->prepare("SELECT COUNT(*) FROM student_projects WHERE project_id = ?");
            $cq->bind_param("i", $delId);
            $cq->execute();
            $delApplicants = (int)$cq->get_result()->fetch_row()[0];
            try {
                $cq = $conn->prepare("SELECT COUNT(*) FROM project_applications WHERE project_id = ?");
                $cq->bind_param("i", $delId);
                $cq->execute();
                $delApplicants += (int)$cq->get_result()->fetch_row()[0];
            } catch (Throwable $e) { /* table not created yet */ }
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

    $hq = $conn->prepare("SELECT * FROM projects WHERE id = ? AND organization_email = ? AND status = 'hold'");
    $hq->bind_param("is", $holdId, $organization_email);
    $hq->execute();
    $holdProject = $hq->get_result()->fetch_assoc();

    if (!$holdProject) {
        header("Location: manage_projects.php?hold_error=1");
        exit;
    }

    // The fix form starts EMPTY. A field left empty keeps its current value;
    // the fields the Admin flagged must be filled in.
    $posted = function ($name) { return trim($_POST[$name] ?? ''); };
    $flaggedFields = array_filter(array_map('trim', explode(',', $holdProject['hold_fields'] ?? '')));

    $postNames = [
        'title' => 'title', 'category' => 'category', 'keywords' => 'keywords',
        'description' => 'description', 'learning_objectives' => 'learning_objectives',
        'expected_outcomes' => 'expected_outcomes', 'deadline' => 'deadline',
        'members' => 'members', 'duration' => 'duration_weeks',
    ];
    foreach ($flaggedFields as $ff) {
        if (isset($postNames[$ff]) && $posted($postNames[$ff]) === '') {
            header("Location: manage_projects.php?hold_error=1");
            exit;
        }
    }

    $title       = $posted('title')               !== '' ? $posted('title')               : $holdProject['title'];
    $category    = $posted('category')            !== '' ? $posted('category')            : $holdProject['category'];
    $keywords    = $posted('keywords')            !== '' ? $posted('keywords')            : $holdProject['keywords'];
    $description = $posted('description')         !== '' ? $posted('description')         : $holdProject['description'];
    $objectives  = $posted('learning_objectives') !== '' ? $posted('learning_objectives') : $holdProject['learning_objectives'];
    $outcomes    = $posted('expected_outcomes')   !== '' ? $posted('expected_outcomes')   : $holdProject['expected_outcomes'];
    $deadline    = $posted('deadline')            !== '' ? $posted('deadline')            : $holdProject['deadline'];
    $members     = $posted('members')             !== '' ? max(1, (int)$posted('members')) : (int)$holdProject['members'];
    $durWeeks    = (int)$posted('duration_weeks');
    $duration    = $durWeeks > 0 ? $durWeeks . ' Weeks' : $holdProject['duration'];
    $response    = $posted('hold_response');

    if ($response === '') {
        header("Location: manage_projects.php?hold_error=1");
        exit;
    }

    try {
        $up = $conn->prepare("UPDATE projects SET
                                  title = ?, category = ?, keywords = ?, tech_stack = ?, description = ?,
                                  learning_objectives = ?, expected_outcomes = ?, deadline = ?, members = ?,
                                  duration = ?, hold_response = ?, hold_resolved_at = NOW()
                              WHERE id = ? AND organization_email = ? AND status = 'hold'");
        // hold_resolved_at = when the activation request was sent
        $up->bind_param("ssssssssissis",
            $title, $category, $keywords, $keywords, $description,
            $objectives, $outcomes, $deadline, $members,
            $duration, $response, $holdId, $organization_email);
        $up->execute();

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

        header("Location: manage_projects.php?hold_resolved=1");
        exit;
    } catch (Throwable $e) {
        header("Location: manage_projects.php?hold_error=1");
        exit;
    }
}

$flash = null;
if (isset($_GET['updated'])) {
    $flash = ['type' => 'success', 'title' => 'Project updated', 'message' => 'Your changes were saved successfully.'];
} elseif (isset($_GET['deleted'])) {
    $flash = ['type' => 'success', 'title' => 'Project deleted', 'message' => 'The project was deleted successfully.'];
} elseif (isset($_GET['delete_blocked'])) {
    $flash = ['type' => 'error', 'title' => 'Can’t delete this project', 'message' => 'Only Rejected projects, or Reviewing projects with 0 applicants, can be deleted.'];
} elseif (isset($_GET['hold_resolved'])) {
    $flash = ['type' => 'success', 'title' => 'Activation request sent', 'message' => 'Your changes were saved. The project stays On Hold until the Admin activates it.'];
} elseif (isset($_GET['hold_error'])) {
    $flash = ['type' => 'error', 'title' => 'Could not update', 'message' => 'Please fill all required fields and try again.'];
}

// Projects are NOT promoted automatically any more.
// A new project starts as "Reviewing" and stays Reviewing even when the team is full;
// the organization changes it to "Active" manually from Edit Project.

// ---- Filters ----
$statusFilter   = $_GET['status'] ?? 'all';
$timeFilter     = $_GET['time'] ?? 'all';      // all | daily | weekly | monthly
if (!in_array($timeFilter, ['all', 'daily', 'weekly', 'monthly'], true)) $timeFilter = 'all';
$searchQuery    = trim($_GET['search'] ?? '');   // from the header search box

// ---- Pagination ----
$perPage = 5;
$page    = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;

// ---- Fetch all projects posted by the logged-in organization ----
$realStmt = $conn->prepare("SELECT * FROM projects WHERE organization_email = ? ORDER BY posted_at DESC");
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

// An "Active" project whose team is not full yet is still shown as "Reviewing":
// it only becomes Active when the number of students it needs have joined.
// (db_status keeps the saved value for the delete rules below.)
foreach ($allProjects as &$ap) {
    $ap['db_status'] = $ap['status'];
    $needed = (int)($ap['members'] ?? 0);
    if ($ap['status'] === 'inprogress' && $needed > 0 && ($teamCounts[(int)$ap['id']] ?? 0) < $needed) {
        $ap['status'] = 'reviewing';
    }
}
unset($ap);

// ---- Applicant counts ----
// Applicants = students who applied (project_applications)
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
    $apStmt = $conn->prepare("SELECT pa.project_id, pa.Email FROM project_applications pa
                              JOIN projects p ON pa.project_id = p.id
                              WHERE p.organization_email = ?");
    $apStmt->bind_param("s", $organization_email);
    $apStmt->execute();
    foreach ($apStmt->get_result()->fetch_all(MYSQLI_ASSOC) as $row) {
        $applicantEmails[(int)$row['project_id']][strtolower(trim($row['Email']))] = true;
    }
} catch (Throwable $e) {
    // project_applications table not created yet -> only team members are counted
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
    if ($status === 'rejected') return '';
    $apps = applicantCount($project['id']);
    if ($status === 'reviewing' && $apps === 0) return '';

    $labels = ['reviewing' => 'Reviewing', 'inprogress' => 'Active', 'hold' => 'Hold', 'rejected' => 'Rejected', 'completed' => 'Completed', 'draft' => 'Draft'];
    $label  = $labels[$status] ?? ucfirst($status);
    if ($status === 'reviewing') {
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
// "Reviewing Projects" card = projects shown with the Reviewing badge
// (saved as Reviewing, or Active but the team is not full yet)
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
    $labels = ['reviewing' => 'Reviewing', 'inprogress' => 'Active', 'hold' => 'Hold', 'rejected' => 'Rejected', 'completed' => 'Completed', 'draft' => 'Draft'];
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
// "Active Projects" = projects shown with the green Active badge
// (saved as Active AND the team is full – same rule as the list below)
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