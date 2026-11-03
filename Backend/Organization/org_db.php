<?php
/*
| Organization backend - helpers for the 3NF database (skillbridge_db)
| Used by the Organization backend files.
|
| In the 3NF database some project details are kept in their own tables:
|   category  -> projects.category_id   + project_categories (category_name)
|   skills    -> project_skills          (one row per skill)
|   company   -> organization.Name       (from projects.organization_email)
|   duration  -> projects.duration_weeks (a number)
| ORG_PROJECT_SELECT joins them back, so a project row still has the easy names
| the pages use: category, company, duration ("8 Weeks") and keywords ("React, PHP").
*/

const ORG_PROJECT_SELECT = "SELECT p.*, c.category_name AS category, o.Name AS company,
           CONCAT(p.duration_weeks, ' Weeks') AS duration,
           (SELECT GROUP_CONCAT(ps.skill_name ORDER BY ps.skill_name SEPARATOR ', ')
              FROM project_skills ps WHERE ps.project_id = p.id) AS keywords
    FROM projects p
    LEFT JOIN project_categories c ON c.category_id = p.category_id
    LEFT JOIN organization o ON o.Email = p.organization_email";

/** All project categories: [category_id => category_name], A-Z */
function orgCategories(mysqli $conn): array
{
    $list = [];
    // A–Z, with "Other" always last
    $res = $conn->query("SELECT category_id, category_name FROM project_categories ORDER BY category_name = 'Other', category_name");
    foreach ($res->fetch_all(MYSQLI_ASSOC) as $row) {
        $list[(int)$row['category_id']] = $row['category_name'];
    }
    return $list;
}

/*
| "+ Add a new category" on Post Project / Edit Project (fetch POST, action = add_category).
| The organization adds a category that is missing from the list. It goes into project_categories,
| so it is in the dropdown from now on (for every organization, and in the students' category filter).
| A name that is already there (any capitals / spaces) is not added again: the existing one is returned.
| Answers with JSON and stops, so call it before the page prints anything.
*/
const ORG_CATEGORY_MIN = 2;
const ORG_CATEGORY_MAX = 40;

function orgHandleAddCategory(mysqli $conn): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || ($_POST['action'] ?? '') !== 'add_category') return;

    $reply = function (array $data) { header('Content-Type: application/json'); echo json_encode($data); exit; };

    // "  web   design " -> "web design"
    $name = trim(preg_replace('/\s+/u', ' ', (string)($_POST['name'] ?? '')));
    $len  = mb_strlen($name);

    if ($len < ORG_CATEGORY_MIN || $len > ORG_CATEGORY_MAX) {
        $reply(['ok' => false, 'message' => 'The category name must be ' . ORG_CATEGORY_MIN . ' to ' . ORG_CATEGORY_MAX . ' characters.']);
    }
    if (!preg_match('/\p{L}/u', $name)) {
        $reply(['ok' => false, 'message' => 'The category name must contain letters.']);
    }
    if (!preg_match('/^[\p{L}\p{N}][\p{L}\p{N} &\/+.,()\-]*$/u', $name)) {
        $reply(['ok' => false, 'message' => 'Use only letters, numbers, spaces and & / + . , ( ) -']);
    }

    try {
        // already in the list? ("ai / ml" = "AI/ML")
        $q = $conn->prepare("SELECT category_name FROM project_categories
                             WHERE LOWER(REPLACE(category_name, ' ', '')) = LOWER(REPLACE(?, ' ', '')) LIMIT 1");
        $q->bind_param("s", $name);
        $q->execute();
        $found = $q->get_result()->fetch_row();
        if ($found) $reply(['ok' => true, 'exists' => true, 'name' => $found[0]]);

        $q = $conn->prepare("INSERT INTO project_categories (category_name) VALUES (?)");
        $q->bind_param("s", $name);
        $q->execute();
        $reply(['ok' => true, 'exists' => false, 'name' => $name]);
    } catch (Throwable $e) {
        $reply(['ok' => false, 'message' => 'The category could not be saved. Please try again.']);
    }
}

/** category name -> category_id (null if the name is not in project_categories) */
function orgCategoryId(mysqli $conn, string $name): ?int
{
    $id = array_search($name, orgCategories($conn), true);
    return $id === false ? null : (int)$id;
}

/** Replace the skills of a project (project_skills: one row per skill) */
function orgSaveProjectSkills(mysqli $conn, int $projectId, array $skills): void
{
    $del = $conn->prepare("DELETE FROM project_skills WHERE project_id = ?");
    $del->bind_param("i", $projectId);
    $del->execute();

    $ins = $conn->prepare("INSERT IGNORE INTO project_skills (project_id, skill_name) VALUES (?, ?)");
    foreach ($skills as $skill) {
        $ins->bind_param("is", $projectId, $skill);
        $ins->execute();
    }
}

/** Run a SELECT and return all rows (empty array if it fails, so the page still loads) */
function orgFetch(mysqli $conn, string $sql, string $types = '', array $params = []): array
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

/**
 * Save the correct status of every team of this organization (org_teams.status).
 * The rule is in Backend/TeamWorkspace.php (TW_STATUS_SQL): Completed when the final report is accepted,
 * Behind when the deadline or a task's due date has passed, otherwise On Track. Called when a page loads,
 * because a deadline can pass without anybody changing anything.
 */
function orgRefreshTeamStatuses(mysqli $conn, string $orgEmail): void
{
    require_once __DIR__ . '/../TeamWorkspace.php';
    try {
        $q = $conn->prepare("UPDATE org_teams t JOIN projects p ON p.id = t.project_id
                             SET t.status = " . TW_STATUS_SQL . " WHERE p.organization_email = ?");
        $q->bind_param("s", $orgEmail);
        $q->execute();
    } catch (Throwable $e) {
        // the page still loads with the saved status
    }
    // each member's progress = their tasks that are Done (from the Kanban board)
    twSyncProgress($conn, "p.organization_email = ?", "s", $orgEmail);
}