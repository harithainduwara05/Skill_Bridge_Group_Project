<?php
/*
|--------------------------------------------------------------------------
| Tables used by Teams + Proposals (Organization module)
|--------------------------------------------------------------------------
| Creates the tables if they are missing and adds any new columns,
| so every team member's local database works without re-importing.
| (Same SQL is in Database/org_teams.sql if you want to run it by hand.)
*/

function tmColumnExists(mysqli $conn, string $table, string $column): bool
{
    $q = $conn->prepare("SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
                         WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?");
    $q->bind_param("ss", $table, $column);
    $q->execute();
    return (bool)$q->get_result()->fetch_row();
}

function tmEnsureSchema(mysqli $conn): void
{
    static $done = false;
    if ($done) return;
    $done = true;

    try {
        // Student proposals / applications for a project
        $conn->query("CREATE TABLE IF NOT EXISTS `project_applications` (
            `id`          INT(11) NOT NULL AUTO_INCREMENT,
            `project_id`  INT(11) NOT NULL,
            `Email`       VARCHAR(100) NOT NULL,
            `status`      VARCHAR(20) NOT NULL DEFAULT 'pending',
            `applied_at`  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `decided_at`  TIMESTAMP NULL DEFAULT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `uq_app_project_student` (`project_id`, `Email`),
            KEY `Email` (`Email`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

        // Teams created by an organization
        $conn->query("CREATE TABLE IF NOT EXISTS `org_teams` (
            `id`                 INT(11) NOT NULL AUTO_INCREMENT,
            `organization_email` VARCHAR(100) NOT NULL,
            `project_id`         INT(11) NOT NULL,
            `name`               VARCHAR(100) NOT NULL,
            `leader_email`       VARCHAR(100) NOT NULL,
            `skills`             TEXT NULL,
            `deadline`           DATE NULL,
            `status`             VARCHAR(20) NOT NULL DEFAULT 'ontrack',
            `created_at`         TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `uq_org_team_name` (`organization_email`, `name`),
            KEY `project_id` (`project_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

        // Members of a team + the role each one has in the team
        $conn->query("CREATE TABLE IF NOT EXISTS `org_team_members` (
            `id`       INT(11) NOT NULL AUTO_INCREMENT,
            `team_id`  INT(11) NOT NULL,
            `Email`    VARCHAR(100) NOT NULL,
            `role`     VARCHAR(50) NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `uq_team_member` (`team_id`, `Email`),
            KEY `Email` (`Email`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

        // Older copies of these tables (made before roles / skills existed)
        if (!tmColumnExists($conn, 'org_teams', 'skills')) {
            $conn->query("ALTER TABLE `org_teams` ADD COLUMN `skills` TEXT NULL AFTER `leader_email`");
        }
        if (!tmColumnExists($conn, 'org_team_members', 'role')) {
            $conn->query("ALTER TABLE `org_team_members` ADD COLUMN `role` VARCHAR(50) NULL AFTER `Email`");
        }
        if (!tmColumnExists($conn, 'project_applications', 'status')) {
            $conn->query("ALTER TABLE `project_applications` ADD COLUMN `status` VARCHAR(20) NOT NULL DEFAULT 'pending'");
        }
        if (!tmColumnExists($conn, 'project_applications', 'decided_at')) {
            $conn->query("ALTER TABLE `project_applications` ADD COLUMN `decided_at` TIMESTAMP NULL DEFAULT NULL");
        }
    } catch (Throwable $e) {
        // Page still loads; the create / accept actions will show an error instead.
    }
}

/*
| Save a proposal decision (accepted / rejected) for a student + project.
| Works even if the table has no UNIQUE key (older copies).
*/
function tmSaveApplicationStatus(mysqli $conn, int $projectId, string $email, string $status): void
{
    $email = strtolower(trim($email));
    $q = $conn->prepare("SELECT id FROM project_applications WHERE project_id = ? AND LOWER(Email) = ? LIMIT 1");
    $q->bind_param("is", $projectId, $email);
    $q->execute();
    $row = $q->get_result()->fetch_assoc();

    if ($row) {
        $u = $conn->prepare("UPDATE project_applications SET status = ?, decided_at = NOW() WHERE id = ?");
        $id = (int)$row['id'];
        $u->bind_param("si", $status, $id);
        $u->execute();
    } else {
        $i = $conn->prepare("INSERT INTO project_applications (project_id, Email, status, decided_at) VALUES (?, ?, ?, NOW())");
        $i->bind_param("iss", $projectId, $email, $status);
        $i->execute();
    }
}