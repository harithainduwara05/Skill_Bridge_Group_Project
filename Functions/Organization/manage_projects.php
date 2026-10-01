<?php

include "../../Config/db.php";
include "../../Session/Session.php";

require_role('organization');
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

$extra_css = '<link rel="stylesheet" href="../../Assets/CSS/Organization/manage_projects.css">';
include "../../Includes/org_sidebar.php";
include "../../Includes/dash_header.php";

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

?>

<main class="content">

    <div class="dashboard-header">
        <div>
            <h1>Project Management</h1>
            <p>Curate and monitor your posted projects and student collaborations.</p>
        </div>
    </div>

    <!-- ===================== SUMMARY STAT CARDS ===================== -->
    <div class="stats-grid">

        <div class="stat-card">
            <div class="stat-card-top">
                <div class="stat-icon blue">
                    <span class="material-symbols-outlined">rocket_launch</span>
                </div>
            </div>
            <div class="stat-info">
                <div class="stat-label">Total Projects Posted</div>
                <div class="stat-value"><?= (int)$totalProjectsAll ?></div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-card-top">
                <div class="stat-icon orange">
                    <span class="material-symbols-outlined">hourglass_top</span>
                </div>
            </div>
            <div class="stat-info">
                <div class="stat-label">Reviewing Projects</div>
                <div class="stat-value"><?= (int)$reviewingProjects ?></div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-card-top">
                <div class="stat-icon green">
                    <span class="material-symbols-outlined">task_alt</span>
                </div>
            </div>
            <div class="stat-info">
                <div class="stat-label">Active Projects</div>
                <div class="stat-value"><?= (int)$activeProjects ?></div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-card-top">
                <div class="stat-icon slate">
                    <span class="material-symbols-outlined">sentiment_neutral</span>
                </div>
            </div>
            <div class="stat-info">
                <div class="stat-label">Assigned Teams</div>
                <div class="stat-value"><?= $assignedTeams ?></div>
            </div>
        </div>

    </div>

    <!-- ===================== TOOLBAR: FILTERS + VIEW ===================== -->
    <div class="page-toolbar">

        <form class="toolbar-filters" method="get">
            <?php if ($searchQuery !== ''): ?>
                <input type="hidden" name="search" value="<?= htmlspecialchars($searchQuery) ?>">
            <?php endif; ?>
            <span class="filter-label">Filters:</span>

            <select name="status" class="select-filter" onchange="this.form.submit()">
                <option value="all" <?= (($_GET['status'] ?? 'all') == 'all') ? 'selected' : '' ?>>Status: All</option>
                <option value="reviewing" <?= (($_GET['status'] ?? '') == 'reviewing') ? 'selected' : '' ?>>Reviewing</option>
                <option value="inprogress" <?= (($_GET['status'] ?? '') == 'inprogress') ? 'selected' : '' ?>>Active</option>
                <option value="hold" <?= (($_GET['status'] ?? '') == 'hold') ? 'selected' : '' ?>>Hold</option>
                <option value="completed" <?= (($_GET['status'] ?? '') == 'completed') ? 'selected' : '' ?>>Completed</option>
                <option value="rejected" <?= (($_GET['status'] ?? '') == 'rejected') ? 'selected' : '' ?>>Rejected</option>
            </select>

            <select name="time" class="select-filter" onchange="this.form.submit()">
                <option value="all" <?= $timeFilter === 'all' ? 'selected' : '' ?>>Time: All</option>
                <option value="daily" <?= $timeFilter === 'daily' ? 'selected' : '' ?>>Daily (Today)</option>
                <option value="weekly" <?= $timeFilter === 'weekly' ? 'selected' : '' ?>>Weekly (Last 7 days)</option>
                <option value="monthly" <?= $timeFilter === 'monthly' ? 'selected' : '' ?>>Monthly (Last 30 days)</option>
            </select>
        </form>

        <div class="toolbar-meta">
            <span class="results-count">Showing <?= $shownCount ?> of <?= $filteredTotal ?> projects<?= $totalPages > 1 ? " (Page $page of $totalPages)" : '' ?></span>
            <?php if ($searchQuery !== ''): ?>
                <span class="results-count" style="display:inline-flex; align-items:center; gap:6px;">
                    Search: <strong>“<?= htmlspecialchars($searchQuery) ?>”</strong>
                    <a href="manage_projects.php" title="Clear search" style="color:#2563eb; text-decoration:none; font-weight:600;">Clear</a>
                </span>
            <?php endif; ?>

            <div class="view-toggle-group" role="group" aria-label="Project view">
                <button type="button" class="active" data-view="list" title="List view" aria-label="List view" aria-pressed="true">
                    <span class="material-symbols-outlined">view_list</span>
                </button>
                <button type="button" data-view="grid" title="Grid view" aria-label="Grid view" aria-pressed="false">
                    <span class="material-symbols-outlined">grid_view</span>
                </button>
            </div>
        </div>

    </div>

    <!-- ===================== PROJECTS TABLE ===================== -->
    <div class="full-width-section project-list-view" id="projectListView">
        <div class="card">
            <div class="card-body" style="padding:0 0 4px;">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Project Title</th>
                            <th>Category</th>
                            <th>Applicants</th>
                            <th>Assigned Team</th>
                            <th>Deadline</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($projects)): ?>
                        <tr>
                            <td colspan="7" style="text-align:center; padding:36px 16px; color:#6b7280;">
                                No projects found<?= $searchQuery !== '' ? ' for “' . htmlspecialchars($searchQuery) . '”' : '' ?>.
                            </td>
                        </tr>
                        <?php endif; ?>
                        <?php foreach ($projects as $p): ?>
                        <tr>
                            <td class="project-title-cell">
                                <div class="project-title"><?= htmlspecialchars($p['title']) ?></div>
                                <div class="project-meta">Posted <?= htmlspecialchars(date('M d, Y', strtotime($p['posted_at']))) ?></div>
                            </td>
                            <td>
                                <span class="category-tag"><?= htmlspecialchars($p['category']) ?></span>
                            </td>
                            <td>
                                <?= shownApplicants($p) ?>
                            </td>
                            <td>
                                <?php $assignedCount = assignedCount($p); ?>
                                <?php if ($assignedCount > 0): ?>
                                    <div class="team-cell" style="color:#16a34a; font-size:12px; font-style:italic;">
                                        <span class="team-dot" style="background:#16a34a;"></span>
                                        Assigned (<?= $assignedCount ?>/<?= (int)($p['members'] ?? 0) ?>)
                                    </div>
                                <?php else: ?>
                                    <div class="team-cell not-assigned">
                                        <span class="team-dot"></span>
                                        Not Assigned
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars(formatDeadline($p['deadline'])) ?></td>
                            <td>
                                <span class="badge-status <?= htmlspecialchars($p['status']) ?>"><?= htmlspecialchars(statusLabel($p['status'])) ?></span>
                                <?php if ($p['status'] === 'hold' && !empty($p['hold_resolved_at'])): ?>
                                    <div class="hold-requested"><span class="material-symbols-outlined">hourglass_top</span> Activation requested</div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="row-actions">
                                    <button type="button" class="action-btn" title="View" onclick="openProjectView('<?= htmlspecialchars((string)$p['id']) ?>')">
                                        <span class="material-symbols-outlined" style="font-size:18px;">visibility</span>
                                    </button>
                                    <?php if (in_array($p['status'], ['hold', 'rejected'], true)): ?>
                                    <button type="button" class="action-btn" title="Edit"
                                        onclick="openEditBlocked(<?= htmlspecialchars(json_encode($p['title']), ENT_QUOTES) ?>, '<?= $p['status'] ?>', '<?= (int)$p['id'] ?>')">
                                        <span class="material-symbols-outlined" style="font-size:18px;">edit</span>
                                    </button>
                                    <?php else: ?>
                                    <a class="action-btn" title="Edit" href="edit_project.php?id=<?= (int)$p['id'] ?>">
                                        <span class="material-symbols-outlined" style="font-size:18px;">edit</span>
                                    </a>
                                    <?php endif; ?>
                                    <button type="button" class="action-btn danger" title="Delete"
                                        onclick="openDeleteModal(<?= (int)$p['id'] ?>, <?= htmlspecialchars(json_encode($p['title']), ENT_QUOTES) ?>, <?= htmlspecialchars(json_encode(deleteBlockReason($p)), ENT_QUOTES) ?>)">
                                        <span class="material-symbols-outlined" style="font-size:18px;">delete</span>
                                    </button>
                                    <?php if ($p['status'] === 'hold'): ?>
                                    <button type="button" class="action-btn hold-btn" title="On hold – see reason &amp; fix" onclick="openHoldModal('<?= (int)$p['id'] ?>')">
                                        <span class="material-symbols-outlined" style="font-size:18px;">report</span>
                                    </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <!-- ===================== PAGINATION ===================== -->
                <?php if ($totalPages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="<?= buildPageUrl($page - 1, $statusFilter, $timeFilter) ?>" class="pagination-link">&lsaquo; Previous</a>
                    <?php else: ?>
                        <span class="pagination-link" style="opacity:.4; pointer-events:none;">&lsaquo; Previous</span>
                    <?php endif; ?>

                    <div class="page-numbers">
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <a href="<?= buildPageUrl($i, $statusFilter, $timeFilter) ?>" class="page-btn <?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
                        <?php endfor; ?>
                    </div>

                    <?php if ($page < $totalPages): ?>
                        <a href="<?= buildPageUrl($page + 1, $statusFilter, $timeFilter) ?>" class="pagination-link">Next &rsaquo;</a>
                    <?php else: ?>
                        <span class="pagination-link" style="opacity:.4; pointer-events:none;">Next &rsaquo;</span>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- ===================== PROJECTS GRID VIEW ===================== -->
    <div class="projects-grid-view" id="projectGridView" hidden>
        <div class="projects-management-grid">
            <?php if (empty($projects)): ?>
                <div style="grid-column:1 / -1; text-align:center; padding:36px 16px; color:#6b7280;">
                    No projects found<?= $searchQuery !== '' ? ' for “' . htmlspecialchars($searchQuery) . '”' : '' ?>.
                </div>
            <?php endif; ?>
            <?php foreach ($projects as $p): ?>
                <?php
                    $gridApplicants = shownApplicants($p);
                    $gridAssigned   = assignedCount($p);
                ?>
                <article class="project-management-card">
                    <div class="project-management-card-top">
                        <span class="category-tag"><?= htmlspecialchars($p['category']) ?></span>
                        <span class="badge-status <?= htmlspecialchars($p['status']) ?>"><?= htmlspecialchars(statusLabel($p['status'])) ?></span>
                        <?php if ($p['status'] === 'hold' && !empty($p['hold_resolved_at'])): ?>
                            <span class="hold-requested"><span class="material-symbols-outlined">hourglass_top</span> Activation requested</span>
                        <?php endif; ?>
                    </div>

                    <h3><?= htmlspecialchars($p['title']) ?></h3>
                    <div class="project-management-date">Posted <?= htmlspecialchars(date('M d, Y', strtotime($p['posted_at']))) ?></div>

                    <div class="project-management-details">
                        <div>
                            <span class="material-symbols-outlined">groups</span>
                            <span><strong><?= $gridApplicants ?></strong> Applicants</span>
                        </div>
                        <div style="<?= $gridAssigned > 0 ? 'color:#16a34a;' : 'color:#ea7a1a;' ?>">
                            <span class="material-symbols-outlined">group</span>
                            <span><?= $gridAssigned > 0 ? '<strong>Assigned (' . $gridAssigned . '/' . (int)($p['members'] ?? 0) . ')</strong>' : '<strong>Not Assigned</strong>' ?></span>
                        </div>
                        <div>
                            <span class="material-symbols-outlined">event</span>
                            <span>Deadline: <strong><?= htmlspecialchars(formatDeadline($p['deadline'])) ?></strong></span>
                        </div>
                    </div>

                    <div class="project-management-actions">
                        <button type="button" class="action-btn" title="View" onclick="openProjectView('<?= htmlspecialchars((string)$p['id']) ?>')">
                            <span class="material-symbols-outlined" style="font-size:18px;">visibility</span>
                        </button>
                        <?php if (in_array($p['status'], ['hold', 'rejected'], true)): ?>
                        <button type="button" class="action-btn" title="Edit"
                            onclick="openEditBlocked(<?= htmlspecialchars(json_encode($p['title']), ENT_QUOTES) ?>, '<?= $p['status'] ?>', '<?= (int)$p['id'] ?>')">
                            <span class="material-symbols-outlined" style="font-size:18px;">edit</span>
                        </button>
                        <?php else: ?>
                        <a class="action-btn" title="Edit" href="edit_project.php?id=<?= (int)$p['id'] ?>">
                            <span class="material-symbols-outlined" style="font-size:18px;">edit</span>
                        </a>
                        <?php endif; ?>
                        <button type="button" class="action-btn danger" title="Delete"
                            onclick="openDeleteModal(<?= (int)$p['id'] ?>, <?= htmlspecialchars(json_encode($p['title']), ENT_QUOTES) ?>, <?= htmlspecialchars(json_encode(deleteBlockReason($p)), ENT_QUOTES) ?>)">
                            <span class="material-symbols-outlined" style="font-size:18px;">delete</span>
                        </button>
                        <?php if ($p['status'] === 'hold'): ?>
                        <button type="button" class="action-btn hold-btn" title="On hold – see reason &amp; fix" onclick="openHoldModal('<?= (int)$p['id'] ?>')">
                            <span class="material-symbols-outlined" style="font-size:18px;">report</span>
                        </button>
                        <?php endif; ?>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>

        <!-- Same pagination is available in grid view. -->
        <?php if ($totalPages > 1): ?>
        <div class="pagination grid-pagination">
            <?php if ($page > 1): ?>
                <a href="<?= buildPageUrl($page - 1, $statusFilter, $timeFilter) ?>" class="pagination-link">&lsaquo; Previous</a>
            <?php else: ?>
                <span class="pagination-link" style="opacity:.4; pointer-events:none;">&lsaquo; Previous</span>
            <?php endif; ?>

            <div class="page-numbers">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a href="<?= buildPageUrl($i, $statusFilter, $timeFilter) ?>" class="page-btn <?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
                <?php endfor; ?>
            </div>

            <?php if ($page < $totalPages): ?>
                <a href="<?= buildPageUrl($page + 1, $statusFilter, $timeFilter) ?>" class="pagination-link">Next &rsaquo;</a>
            <?php else: ?>
                <span class="pagination-link" style="opacity:.4; pointer-events:none;">Next &rsaquo;</span>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- ===================== DELETE CONFIRMATION MODAL ===================== -->
    <div class="confirm-modal-overlay" id="deleteConfirmModal">
        <div class="confirm-modal">
            <div class="confirm-modal-icon">
                <span class="material-symbols-outlined">warning</span>
            </div>
            <h3>Delete this project?</h3>
            <p>You're about to delete "<strong id="deleteProjectTitle"></strong>". This action cannot be undone.</p>
            <div class="confirm-modal-actions">
                <button type="button" class="confirm-modal-cancel" onclick="closeDeleteModal()">Cancel</button>
                <button type="button" class="confirm-modal-delete" onclick="confirmDelete()">Delete</button>
            </div>
        </div>
    </div>

    <div class="confirm-modal-overlay" id="deleteBlockedModal">
        <div class="confirm-modal">
            <div class="confirm-modal-icon" style="background:#fef3c7; color:#d97706;">
                <span class="material-symbols-outlined">block</span>
            </div>
            <h3 id="blockedHeading">You can’t delete this project</h3>
            <p style="margin-bottom:10px;"><strong id="blockedProjectTitle"></strong></p>
            <p id="blockedReason" style="margin-bottom:12px;"></p>
            <div class="delete-rule-box">
                <span class="material-symbols-outlined">info</span>
                <span id="blockedRule">Only <b>Rejected</b> projects, or <b>Reviewing</b> projects with <b>0 applicants</b>, can be deleted.</span>
            </div>
            <div class="confirm-modal-actions">
                <button type="button" class="hm-btn primary" id="blockedHoldBtn" hidden>
                    <span class="material-symbols-outlined">report</span> Open hold details
                </button>
                <button type="button" class="confirm-modal-cancel" onclick="closeDeleteBlocked()">OK, got it</button>
            </div>
        </div>
    </div>

    <!-- ===================== PROJECT DETAILS MODAL (View) ===================== -->
    <?php
        $projectViewData = [];
        foreach ($projects as $p) {
            $vApplicants = shownApplicants($p);
            $vAssigned   = assignedCount($p);
            $projectViewData[(string)$p['id']] = [
                'id'         => $p['id'],
                'title'      => $p['title'] ?? '',
                'category'   => $p['category'] ?? '',
                'status'     => $p['status'] ?? '',
                'posted'     => date('M d, Y', strtotime($p['posted_at'])),
                'deadline'   => formatDeadline($p['deadline'] ?? ''),
                'applicants' => $vApplicants,
                'assigned'   => $vAssigned,
                'required'   => (int)($p['members'] ?? 0),
                'visibility' => $p['visibility'] ?? '',
                'duration'   => $p['duration'] ?? '',
                'members'    => $p['members'] ?? '',
                'year'       => $p['preferred_year'] ?? '',
                'difficulty' => $p['difficulty'] ?? '',
                'keywords'   => $p['keywords'] ?? '',
                'description'=> $p['description'] ?? '',
                'objectives' => $p['learning_objectives'] ?? '',
                'outcomes'   => $p['expected_outcomes'] ?? '',
            ];
        }
    ?>
    <div class="confirm-modal-overlay" id="projectViewModal">
        <div class="pv-modal" role="dialog" aria-modal="true" aria-labelledby="pvTitle">
            <div class="pv-head">
                <div>
                    <div class="pv-tags"><span class="category-tag" id="pvCategory"></span><span class="badge-status" id="pvStatus"></span></div>
                    <h3 id="pvTitle"></h3>
                </div>
                <button type="button" class="pv-close" onclick="closeProjectView()" aria-label="Close">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <div class="pv-body">
                <div class="pv-info" id="pvInfo"></div>
                <div id="pvSections"></div>
            </div>
            <div class="pv-foot">
                <button type="button" class="pv-btn-close" onclick="closeProjectView()">Close</button>
                <a class="pv-btn-edit" id="pvEdit" href="#"><span class="material-symbols-outlined" style="font-size:18px;">edit</span>Edit Project</a>
            </div>
        </div>
    </div>

    <!-- ===================== ON HOLD: REASON + FIX MODAL ===================== -->
    <?php
        $holdData = [];
        foreach ($allProjects as $p) {
            if (($p['status'] ?? '') !== 'hold') continue;
            preg_match('/\d+/', $p['duration'] ?? '', $hm);
            $dlTs = !empty($p['deadline']) ? strtotime($p['deadline']) : false;
            $holdData[(string)$p['id']] = [
                'id'          => (int)$p['id'],
                'title'       => $p['title'] ?? '',
                'category'    => $p['category'] ?? '',
                'keywords'    => $p['keywords'] ?? '',
                'description' => $p['description'] ?? '',
                'learning_objectives' => $p['learning_objectives'] ?? '',
                'expected_outcomes'   => $p['expected_outcomes'] ?? '',
                'deadline'    => $dlTs ? date('Y-m-d', $dlTs) : '',
                'members'     => (int)($p['members'] ?? 1),
                'duration'    => $hm[0] ?? '',
                'reason'      => $p['hold_reason'] ?? '',
                'fields'      => array_values(array_filter(array_map('trim', explode(',', $p['hold_fields'] ?? '')))),
                'held_at'     => !empty($p['held_at']) ? date('M d, Y · h:i A', strtotime($p['held_at'])) : '',
                'requested_at'=> !empty($p['hold_resolved_at']) ? date('M d, Y · h:i A', strtotime($p['hold_resolved_at'])) : '',
                'response'    => $p['hold_response'] ?? '',
            ];
        }
        $holdCategories = ['Web Development', 'Mobile Development', 'AI / Machine Learning', 'Data Science', 'UI/UX Design', 'Cloud & DevOps', 'Cybersecurity', 'Other'];
    ?>
    <div class="confirm-modal-overlay" id="holdModal">
        <form class="hm-modal" method="post" action="manage_projects.php" role="dialog" aria-modal="true" aria-labelledby="hmTitle">
            <input type="hidden" name="action" value="resolve_hold">
            <input type="hidden" name="project_id" id="hmProjectId">

            <div class="hm-head">
                <div class="hm-head-icon"><span class="material-symbols-outlined">pause_circle</span></div>
                <div class="hm-head-text">
                    <div class="hm-kicker">Project On Hold</div>
                    <h3 id="hmTitle"></h3>
                </div>
                <button type="button" class="pv-close" onclick="closeHoldModal()" aria-label="Close">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>

            <div class="hm-steps">
                <div class="hm-step active" data-step="1"><span>1</span> Admin's reason</div>
                <div class="hm-step-line"></div>
                <div class="hm-step" data-step="2"><span>2</span> Fix project</div>
                <div class="hm-step-line"></div>
                <div class="hm-step" data-step="3"><span>3</span> Request activation</div>
            </div>

            <div class="hm-body">
                <!-- STEP 1: reason from the Admin -->
                <section class="hm-panel" data-panel="1">
                    <div class="hm-reason">
                        <div class="hm-reason-top">
                            <span class="material-symbols-outlined">admin_panel_settings</span>
                            <div>
                                <div class="hm-reason-by">SkillBridge Admin put this project on hold</div>
                                <div class="hm-reason-date" id="hmHeldAt"></div>
                            </div>
                        </div>
                        <p class="hm-reason-text" id="hmReason"></p>
                    </div>

                    <div class="hm-pending" id="hmPending" hidden>
                        <span class="material-symbols-outlined">hourglass_top</span>
                        <div>
                            <div class="hm-pending-title">Activation request sent <span id="hmPendingDate"></span></div>
                            <div class="hm-pending-msg">Waiting for the Admin to activate this project. You can update it and send the request again if needed.</div>
                        </div>
                    </div>

                    <div class="hm-subtitle">Needs your attention</div>
                    <div class="hm-flag-list" id="hmFlagList"></div>
                </section>

                <!-- STEP 2: fix the fields -->
                <section class="hm-panel" data-panel="2" hidden>
                    <details class="hm-reason-mini" id="hmReasonToggle">
                        <summary><span class="material-symbols-outlined">admin_panel_settings</span> Admin’s reason <span class="hm-reason-mini-hint">(click to read again)</span></summary>
                        <p id="hmReasonMini"></p>
                    </details>

                    <p class="hm-hint"><span class="material-symbols-outlined">info</span> Fill in the fields marked <span class="hm-flag-tag">Flagged</span>. Other fields are optional – leave them empty to keep them as they are.</p>

                    <div class="hm-grid-2">
                        <div class="hm-field" data-field="title">
                            <label class="form-label">Project Title</label>
                            <input type="text" name="title" class="form-input">
                        </div>
                        <div class="hm-field" data-field="category">
                            <label class="form-label">Category</label>
                            <select name="category" class="form-select">
                                <option value="">Keep current category</option>
                                <?php foreach ($holdCategories as $cat): ?>
                                    <option value="<?= htmlspecialchars($cat) ?>"><?= htmlspecialchars($cat) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="hm-field" data-field="keywords">
                        <label class="form-label">Required Skills / Keywords</label>
                        <div class="chip-input-box hm-chip-box" id="hmChipBox">
                            <input type="text" id="hmChipInput" class="hm-main-input" placeholder="Type a skill and press Enter (e.g. Laravel)">
                        </div>
                        <input type="hidden" name="keywords" id="hmKeywords">
                        <div class="hm-field-note">Add as many skills as you need. Press <b>Enter</b> or <b>,</b> after each one.</div>
                    </div>

                    <div class="hm-field" data-field="description">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-textarea" rows="4"></textarea>
                    </div>

                    <div class="hm-field" data-field="learning_objectives">
                        <label class="form-label">Learning Objectives</label>
                        <textarea name="learning_objectives" class="form-textarea" rows="3"></textarea>
                    </div>

                    <div class="hm-field" data-field="expected_outcomes">
                        <label class="form-label">Expected Outcomes</label>
                        <textarea name="expected_outcomes" class="form-textarea" rows="3"></textarea>
                    </div>

                    <div class="hm-grid-3">
                        <div class="hm-field" data-field="deadline">
                            <label class="form-label">Application Deadline</label>
                            <input type="date" name="deadline" class="form-input">
                        </div>
                        <div class="hm-field" data-field="members">
                            <label class="form-label">Students Required</label>
                            <input type="number" min="1" name="members" class="form-input">
                        </div>
                        <div class="hm-field" data-field="duration">
                            <label class="form-label">Duration (Weeks)</label>
                            <input type="number" min="1" name="duration_weeks" class="form-input">
                        </div>
                    </div>
                </section>

                <!-- STEP 3: ask the Admin to activate -->
                <section class="hm-panel" data-panel="3" hidden>
                    <div class="hm-flow">
                        <div class="hm-flow-item">
                            <span class="badge-status hold">Hold</span>
                            <small>Now</small>
                        </div>
                        <span class="material-symbols-outlined hm-flow-arrow">arrow_forward</span>
                        <div class="hm-flow-item">
                            <span class="hm-flow-admin"><span class="material-symbols-outlined">admin_panel_settings</span> Admin reviews</span>
                            <small>After your request</small>
                        </div>
                        <span class="material-symbols-outlined hm-flow-arrow">arrow_forward</span>
                        <div class="hm-flow-item">
                            <span class="badge-status inprogress">Active</span>
                            <small>Set by the Admin</small>
                        </div>
                    </div>

                    <div class="hm-field">
                        <label class="form-label">Message to Admin *</label>
                        <textarea name="hold_response" class="form-textarea" rows="4" required
                                  placeholder="Explain what you fixed, e.g. 'Added a detailed description, moved the deadline to Nov 15 and reduced the team to 4 students.'"></textarea>
                    </div>

                    <div class="hm-notify-note">
                        <span class="material-symbols-outlined">info</span>
                        <span>Only the Admin can activate this project. It stays <b>On Hold</b> until the Admin approves it. After that you can change its status as usual.</span>
                    </div>
                </section>
            </div>

            <div class="hm-foot">
                <button type="button" class="hm-btn ghost" id="hmBack" onclick="holdStep(-1)">
                    <span class="material-symbols-outlined">arrow_back</span> Back
                </button>
                <div class="hm-foot-right">
                    <button type="button" class="hm-btn ghost" onclick="closeHoldModal()">Cancel</button>
                    <button type="button" class="hm-btn primary" id="hmNext" onclick="holdStep(1)">
                        Next <span class="material-symbols-outlined">arrow_forward</span>
                    </button>
                    <button type="submit" class="hm-btn primary" id="hmSubmit" hidden>
                        <span class="material-symbols-outlined">send</span> Send Activation Request
                    </button>
                </div>
            </div>
        </form>
    </div>

    <?php if (!empty($flash)): ?>
    <div class="flash-toast flash-<?= htmlspecialchars($flash['type']) ?>" id="holdFlash">
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
    const PROJECT_VIEW_DATA = <?= json_encode($projectViewData, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) ?>;

    function pvEl(tag, cls, text) {
        const el = document.createElement(tag);
        if (cls) el.className = cls;
        if (text !== undefined) el.textContent = text;
        return el;
    }

    function openProjectView(id) {
        const d = PROJECT_VIEW_DATA[id];
        if (!d) return;

        document.getElementById('pvTitle').textContent = d.title;
        document.getElementById('pvCategory').textContent = d.category;
        const STATUS_LABELS = { reviewing: 'Reviewing', inprogress: 'Active', hold: 'Hold', completed: 'Completed', draft: 'Draft' };
        const st = document.getElementById('pvStatus');
        st.className = 'badge-status ' + d.status;
        st.textContent = d.status ? (STATUS_LABELS[d.status] || (d.status.charAt(0).toUpperCase() + d.status.slice(1))) : '';

        // key facts (empty ones are skipped)
        const info = document.getElementById('pvInfo');
        info.innerHTML = '';
        [
            ['Posted', d.posted],
            ['Deadline', d.deadline],
            ['Applicants', d.applicants],
            ['Assigned Team', d.assigned > 0 ? ('Assigned (' + d.assigned + '/' + d.required + ')') : 'Not Assigned'],
            ['Visibility', d.visibility],
            ['Duration', d.duration],
            ['Students Required', d.members],
            ['Preferred Year', d.year],
            ['Difficulty', d.difficulty]
        ].forEach(function (row) {
            if (row[1] === '' || row[1] === null || row[1] === undefined) return;
            const box = pvEl('div');
            box.appendChild(pvEl('div', 'pv-info-label', row[0]));
            box.appendChild(pvEl('div', 'pv-info-value', String(row[1])));
            info.appendChild(box);
        });

        // text sections (empty ones are skipped)
        const sections = document.getElementById('pvSections');
        sections.innerHTML = '';
        [['Description', d.description], ['Learning Objectives', d.objectives], ['Expected Outcomes', d.outcomes]].forEach(function (sec) {
            if (!sec[1]) return;
            const box = pvEl('div', 'pv-section');
            box.appendChild(pvEl('h4', '', sec[0]));
            box.appendChild(pvEl('p', '', sec[1]));
            sections.appendChild(box);
        });
        if (d.keywords) {
            const box = pvEl('div', 'pv-section');
            box.appendChild(pvEl('h4', '', 'Skills / Keywords'));
            const chips = pvEl('div', 'pv-chips');
            d.keywords.split(',').forEach(function (k) {
                k = k.trim();
                if (k) chips.appendChild(pvEl('span', 'pv-chip', k));
            });
            box.appendChild(chips);
            sections.appendChild(box);
        }

        const pvEdit = document.getElementById('pvEdit');
        pvEdit.href = 'edit_project.php?id=' + encodeURIComponent(d.id);
        pvEdit.style.display = (d.status === 'hold' || d.status === 'rejected') ? 'none' : '';
        document.getElementById('projectViewModal').classList.add('open');
    }

    function closeProjectView() {
        document.getElementById('projectViewModal').classList.remove('open');
    }

    document.getElementById('projectViewModal').addEventListener('click', function (e) {
        if (e.target === this) closeProjectView();
    });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeProjectView();
    });
</script>

<script>
    let pendingDeleteId = null;

    function openDeleteModal(id, title, blockReason) {
        // Not allowed -> show the warning instead of the delete confirmation
        if (blockReason) {
            showBlocked(
                'You can’t delete this project', title, blockReason,
                'Only <b>Rejected</b> projects, or <b>Reviewing</b> projects with <b>0 applicants</b>, can be deleted.'
            );
            return;
        }
        pendingDeleteId = id;
        document.getElementById('deleteProjectTitle').textContent = title;
        document.getElementById('deleteConfirmModal').classList.add('open');
    }

    function closeDeleteModal() {
        pendingDeleteId = null;
        document.getElementById('deleteConfirmModal').classList.remove('open');
    }

    function confirmDelete() {
        if (pendingDeleteId !== null) {
            window.location.href = 'manage_projects.php?delete=' + pendingDeleteId;
        }
    }

    document.getElementById('deleteConfirmModal').addEventListener('click', function (e) {
        if (e.target === this) closeDeleteModal();
    });

    // Shared warning popup (used for blocked Delete and blocked Edit)
    function showBlocked(heading, title, reason, ruleHtml, holdId) {
        document.getElementById('blockedHeading').textContent = heading;
        document.getElementById('blockedProjectTitle').textContent = title;
        document.getElementById('blockedReason').textContent = reason;
        document.getElementById('blockedRule').innerHTML = ruleHtml;

        const holdBtn = document.getElementById('blockedHoldBtn');
        holdBtn.hidden = !holdId;
        holdBtn.onclick = holdId ? function () { closeDeleteBlocked(); openHoldModal(holdId); } : null;

        document.getElementById('deleteBlockedModal').classList.add('open');
    }

    function openEditBlocked(title, status, holdId) {
        if (status === 'hold') {
            showBlocked(
                'You can’t edit this project', title,
                'This project is On Hold by the Admin, so it can’t be edited from here.',
                'Use the <b>hold icon</b> in the Actions column to see the Admin’s reason, fix the project and send it back.',
                holdId
            );
        } else {
            showBlocked(
                'You can’t edit this project', title,
                'This project was Rejected by the Admin, so it can’t be edited.',
                'Rejected projects can’t be changed. You can delete it and post a new project instead.'
            );
        }
    }

    function closeDeleteBlocked() {
        document.getElementById('deleteBlockedModal').classList.remove('open');
    }
    document.getElementById('deleteBlockedModal').addEventListener('click', function (e) {
        if (e.target === this) closeDeleteBlocked();
    });
</script>

<script>
    /* ===================== ON HOLD MODAL ===================== */
    const HOLD_DATA = <?= json_encode($holdData, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) ?>;
    const HOLD_FIELD_LABELS = {
        title: ['Project Title', 'title'],
        category: ['Category', 'category'],
        keywords: ['Skills / Keywords', 'sell'],
        description: ['Description', 'description'],
        learning_objectives: ['Learning Objectives', 'school'],
        expected_outcomes: ['Expected Outcomes', 'flag'],
        deadline: ['Application Deadline', 'event'],
        members: ['Students Required', 'group'],
        duration: ['Duration', 'schedule']
    };

    const holdModal = document.getElementById('holdModal');
    const holdForm  = holdModal.querySelector('form');
    let holdCurrentStep = 1;
    // form.elements[...] is used because inputs named "title"/"action" clash with form properties
    const hf = function (name) { return holdForm.elements.namedItem(name); };

    /* ---- Skills chips in the Fix step (same idea as Post Project) ---- */
    let hmSkills = [];
    const hmChipBox   = document.getElementById('hmChipBox');
    const hmChipInput = document.getElementById('hmChipInput');

    function hmRenderSkills() {
        hmChipBox.querySelectorAll('.chip').forEach(function (c) { c.remove(); });
        hmSkills.forEach(function (word, idx) {
            const chip = document.createElement('span');
            chip.className = 'chip';
            chip.appendChild(document.createTextNode(word + ' '));
            const x = document.createElement('button');
            x.type = 'button';
            x.className = 'chip-remove';
            x.innerHTML = '&times;';
            x.addEventListener('click', function (e) {
                e.stopPropagation();
                hmSkills.splice(idx, 1);
                hmRenderSkills();
            });
            chip.appendChild(x);
            hmChipBox.insertBefore(chip, hmChipInput);
        });
        document.getElementById('hmKeywords').value = hmSkills.join(', ');
        hmChipInput.placeholder = hmSkills.length ? 'Add another skill…' : 'Type a skill and press Enter (e.g. Laravel)';
        hmChipInput.setCustomValidity('');
    }
    function hmSetSkills(list) { hmSkills = list.slice(); hmRenderSkills(); }
    function hmAddSkill(raw) {
        (raw || '').split(',').forEach(function (part) {
            const val = part.trim();
            const exists = hmSkills.some(function (s) { return s.toLowerCase() === val.toLowerCase(); });
            if (val && !exists) hmSkills.push(val);
        });
        hmChipInput.value = '';
        hmRenderSkills();
    }

    hmChipInput.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' || e.key === ',') {
            e.preventDefault();               // Enter must not submit the form
            hmAddSkill(hmChipInput.value);
        } else if (e.key === 'Backspace' && hmChipInput.value === '' && hmSkills.length) {
            hmSkills.pop();
            hmRenderSkills();
        }
    });
    hmChipInput.addEventListener('blur', function () { hmAddSkill(hmChipInput.value); });
    hmChipBox.addEventListener('click', function () { hmChipInput.focus(); });

    function openHoldModal(id) {
        const d = HOLD_DATA[id];
        if (!d) return;

        holdForm.reset();
        document.getElementById('hmProjectId').value = d.id;
        document.getElementById('hmTitle').textContent = d.title;
        document.getElementById('hmReason').textContent = d.reason || 'No reason was given by the Admin.';
        document.getElementById('hmHeldAt').textContent = d.held_at ? 'On ' + d.held_at : '';
        document.getElementById('hmPending').hidden = !d.requested_at;
        document.getElementById('hmPendingDate').textContent = d.requested_at ? '· ' + d.requested_at : '';

        // Step 1: chips for the flagged fields
        const list = document.getElementById('hmFlagList');
        list.innerHTML = '';
        if (d.fields.length === 0) {
            list.innerHTML = '<span class="hm-empty">The Admin did not mark specific fields. Please check the whole project.</span>';
        }
        d.fields.forEach(function (f) {
            const info = HOLD_FIELD_LABELS[f];
            if (!info) return;
            const chip = document.createElement('span');
            chip.className = 'hm-flag-chip';
            chip.innerHTML = '<span class="material-symbols-outlined">' + info[1] + '</span>';
            chip.appendChild(document.createTextNode(info[0]));
            list.appendChild(chip);
        });

        // Step 2: a clean, EMPTY form – the organization types the fixes.
        // Placeholders are only examples (no old values are shown).
        const EXAMPLES = {
            title: 'e.g. Smart Clinic Queue System',
            description: 'Describe the project again, following the Admin’s reason…',
            learning_objectives: 'What will students learn?',
            expected_outcomes: 'What will be delivered at the end?',
            members: 'e.g. 4',
            duration_weeks: 'e.g. 12'
        };
        Object.keys(EXAMPLES).forEach(function (name) {
            hf(name).value = '';
            hf(name).placeholder = EXAMPLES[name];
        });
        hf('deadline').value = '';
        hf('category').value = '';
        hf('hold_response').value = '';
        hmSetSkills([]);

        // Short copy of the Admin's reason, so it can be read while typing
        document.getElementById('hmReasonMini').textContent = d.reason || '';
        document.getElementById('hmReasonToggle').open = false;

        // Flagged fields: highlight + must be filled
        holdModal.querySelectorAll('.hm-field[data-field]').forEach(function (el) {
            const flagged = d.fields.indexOf(el.dataset.field) !== -1;
            el.classList.toggle('flagged', flagged);

            const label = el.querySelector('.form-label');
            const oldTag = label.querySelector('.hm-flag-tag');
            if (oldTag) oldTag.remove();

            const mainInput = el.querySelector('.hm-main-input') || el.querySelector('input, select, textarea');
            mainInput.required = flagged && el.dataset.field !== 'keywords';
            el.dataset.required = flagged ? '1' : '';

            if (flagged) {
                const tag = document.createElement('span');
                tag.className = 'hm-flag-tag';
                tag.textContent = 'Flagged';
                label.appendChild(tag);
            }
        });

        showHoldStep(1);
        holdModal.classList.add('open');
    }

    function closeHoldModal() {
        holdModal.classList.remove('open');
    }

    function showHoldStep(step) {
        holdCurrentStep = step;
        holdModal.querySelectorAll('.hm-panel').forEach(function (p) {
            p.hidden = Number(p.dataset.panel) !== step;
        });
        holdModal.querySelectorAll('.hm-step').forEach(function (s) {
            const n = Number(s.dataset.step);
            s.classList.toggle('active', n === step);
            s.classList.toggle('done', n < step);
        });
        document.getElementById('hmBack').style.visibility = step === 1 ? 'hidden' : 'visible';
        document.getElementById('hmNext').hidden = step === 3;
        document.getElementById('hmSubmit').hidden = step !== 3;
        holdModal.querySelector('.hm-body').scrollTop = 0;
    }

    function holdStep(dir) {
        const next = holdCurrentStep + dir;
        if (next < 1 || next > 3) return;

        // Don't leave step 2 with a required field empty
        if (dir > 0 && holdCurrentStep === 2) {
            hmAddSkill(document.getElementById('hmChipInput').value);   // keep a half-typed skill
            const skillsField = holdModal.querySelector('.hm-field[data-field="keywords"]');
            const chipInput = document.getElementById('hmChipInput');
            chipInput.setCustomValidity(
                skillsField.dataset.required && hmSkills.length === 0 ? 'Please add at least one skill.' : ''
            );
            const panel = holdModal.querySelector('[data-panel="2"]');
            const invalid = Array.from(panel.querySelectorAll('input, select, textarea')).find(function (el) {
                return !el.checkValidity();
            });
            if (invalid) { invalid.reportValidity(); return; }
        }
        showHoldStep(next);
    }

    holdModal.addEventListener('click', function (e) {
        if (e.target === holdModal) closeHoldModal();
    });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && holdModal.classList.contains('open')) closeHoldModal();
    });

    // Hide the success / error toast after a few seconds
    (function () {
        const toast = document.getElementById('holdFlash');
        if (!toast) return;
        // remove ?updated / ?deleted ... from the address bar so a refresh doesn't show it again
        try {
            const url = new URL(location.href);
            ['updated', 'deleted', 'delete_blocked', 'hold_resolved', 'hold_error'].forEach(k => url.searchParams.delete(k));
            history.replaceState(null, '', url.pathname + (url.search || '') + url.hash);
        } catch (e) {}
        setTimeout(function () {
            toast.style.transition = 'opacity .4s ease, transform .4s ease';
            toast.style.opacity = '0';
            toast.style.transform = 'translateX(20px)';
            setTimeout(function () { toast.remove(); }, 400);
        }, 3500);
    })();
</script>

<script>
(function () {
    const listView = document.getElementById('projectListView');
    const gridView = document.getElementById('projectGridView');
    const buttons = document.querySelectorAll('.view-toggle-group button[data-view]');

    if (!listView || !gridView || !buttons.length) return;

    function setView(view, save = true) {
        const isGrid = view === 'grid';
        listView.hidden = isGrid;
        gridView.hidden = !isGrid;

        buttons.forEach(button => {
            const active = button.dataset.view === view;
            button.classList.toggle('active', active);
            button.setAttribute('aria-pressed', active ? 'true' : 'false');
        });

        if (save) {
            try { localStorage.setItem('skillbridge-project-view', view); } catch (e) {}
        }
    }

    buttons.forEach(button => {
        button.addEventListener('click', function () {
            setView(this.dataset.view);
        });
    });

    let savedView = 'list';
    try {
        savedView = localStorage.getItem('skillbridge-project-view') || 'list';
    } catch (e) {}
    setView(savedView, false);
})();
</script>

<footer class="footer">
    <div>&copy; 2026 SkillBridge. All rights reserved.</div>
    <div class="footer-links">
        <a href="/Skill_Bridge_Group_Project/help_center.php">Help Center</a>
        <a href="/Skill_Bridge_Group_Project/help_center.php#knowledgeBaseSection">Privacy Policy</a>
        <a href="/Skill_Bridge_Group_Project/help_center.php#knowledgeBaseSection">Terms of Service</a>
    </div>
</footer>

<?php include "../../Includes/dash_footer.php"; ?>