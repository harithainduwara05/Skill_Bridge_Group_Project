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
    $res = $conn->query("SELECT category_id, category_name FROM project_categories ORDER BY category_name");
    foreach ($res->fetch_all(MYSQLI_ASSOC) as $row) {
        $list[(int)$row['category_id']] = $row['category_name'];
    }
    return $list;
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
}