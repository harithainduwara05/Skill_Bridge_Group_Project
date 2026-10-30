<?php
require_once __DIR__ . '/AdminDB.php';

class ProjectManagement extends AdminDB
{
    public function getActiveProjectCount()
    {
        return (int) $this->runCount(
            "SELECT COUNT(*) FROM projects WHERE LOWER(status) IN ('published', 'inprogress', 'active', 'accepted')"
        );
    }

    public function getPublishedProjectCount()
    {
        return (int) $this->runCount(
            "SELECT COUNT(*) FROM projects WHERE LOWER(status) IN ('published', 'publish', 'approved')"
        );
    }

    public function getReviewingProjectCount()
    {
        return (int) $this->runCount(
            "SELECT COUNT(*) FROM projects WHERE LOWER(status) IN ('reviewing', 'review', 'rewiewing')"
        );
    }

    public function getAllProjects()
    {
        $sql = "
            SELECT 
                p.id,
                p.title,
                o.Name AS company,
                o.Name AS organization_name,
                p.organization_email,
                p.icon,
                pc.category_name AS category,
                p.difficulty,
                p.duration_weeks,
                CONCAT(p.duration_weeks, ' Weeks') AS duration,
                p.members,
                p.preferred_year,
                p.deadline,
                p.visibility,
                p.status,
                p.posted_at,
                p.description,
                p.learning_objectives,
                p.expected_outcomes,
                (SELECT GROUP_CONCAT(skill_name SEPARATOR ', ') FROM project_skills WHERE project_id = p.id) AS tech_stack,
                (SELECT reason FROM project_moderation_logs WHERE project_id = p.id ORDER BY id DESC LIMIT 1) AS hold_reason,
                (SELECT org_response FROM project_moderation_logs WHERE project_id = p.id ORDER BY id DESC LIMIT 1) AS hold_response,
                (SELECT created_at FROM project_moderation_logs WHERE project_id = p.id ORDER BY id DESC LIMIT 1) AS held_at
            FROM projects p
            INNER JOIN organization o ON p.organization_email = o.Email
            INNER JOIN project_categories pc ON p.category_id = pc.category_id
            ORDER BY p.id DESC
        ";
        $res = $this->runQuery($sql);
        return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function getProjectById($projectId)
    {
        $cleanId = (int) preg_replace('/[^0-9]/', '', (string)$projectId);
        $sql = "
            SELECT 
                p.id,
                p.title,
                o.Name AS company,
                o.Name AS organization_name,
                p.organization_email,
                p.icon,
                pc.category_name AS category,
                p.difficulty,
                p.duration_weeks,
                CONCAT(p.duration_weeks, ' Weeks') AS duration,
                p.members,
                p.preferred_year,
                p.deadline,
                p.visibility,
                p.status,
                p.posted_at,
                p.description,
                p.learning_objectives,
                p.expected_outcomes,
                (SELECT GROUP_CONCAT(skill_name SEPARATOR ', ') FROM project_skills WHERE project_id = p.id) AS tech_stack,
                (SELECT reason FROM project_moderation_logs WHERE project_id = p.id ORDER BY id DESC LIMIT 1) AS hold_reason,
                (SELECT org_response FROM project_moderation_logs WHERE project_id = p.id ORDER BY id DESC LIMIT 1) AS hold_response,
                (SELECT created_at FROM project_moderation_logs WHERE project_id = p.id ORDER BY id DESC LIMIT 1) AS held_at
            FROM projects p
            INNER JOIN organization o ON p.organization_email = o.Email
            INNER JOIN project_categories pc ON p.category_id = pc.category_id
            WHERE p.id = ?
            LIMIT 1
        ";
        $res = $this->runQuery($sql, "i", $cleanId);
        return ($res && $res->num_rows > 0) ? $res->fetch_assoc() : null;
    }

    public function getDistinctCategories()
    {
        $res = $this->runQuery("SELECT category_name AS category FROM project_categories ORDER BY category_name ASC");
        return $res ? array_column($res->fetch_all(MYSQLI_ASSOC), 'category') : [];
    }

    public function updateProjectStatus($projectId, $status, $holdReason = null, $heldBy = null)
    {
        $cleanId = (int) preg_replace('/[^0-9]/', '', (string)$projectId);
        $statusClean = trim($status);
        $adminEmail = !empty($heldBy) ? $heldBy : 'skillbridge62@gmail.com';

        // Ensure admin email exists to satisfy FK constraint fk_pml_admin
        $chkAdmin = $this->runQuery("SELECT Email FROM admin WHERE LOWER(Email) = LOWER(?) LIMIT 1", "s", $adminEmail);
        if (!$chkAdmin || $chkAdmin->num_rows === 0) {
            $adminEmail = 'skillbridge62@gmail.com';
        }

        if (strcasecmp($statusClean, 'Hold') === 0) {
            $ok = $this->runAction("UPDATE projects SET status = 'hold' WHERE id = ?", "i", $cleanId);
            if ($ok) {
                $this->runAction(
                    "INSERT INTO project_moderation_logs (project_id, admin_email, action_type, reason, status) VALUES (?, ?, ?, ?, 'Open')",
                    "isss",
                    $cleanId, $adminEmail, 'Hold', $holdReason
                );
                $proj = $this->getProjectById($cleanId);
                if (!empty($proj['organization_email'])) {
                    $this->addNotification(
                        $proj['organization_email'],
                        'Project Placed on Hold',
                        "Your project '{$proj['title']}' has been placed on hold by Admin. Reason: {$holdReason}. Please review and submit your clarification.",
                        'project'
                    );
                }
            }
            return $ok;
        } elseif (strcasecmp($statusClean, 'Published') === 0 || strcasecmp($statusClean, 'Active') === 0) {
            $existingProj = $this->getProjectById($cleanId);
            $wasHold = strcasecmp($existingProj['status'] ?? '', 'Hold') === 0;
            $newStatus = (strcasecmp($statusClean, 'Active') === 0) ? 'Active' : 'Published';

            $ok = $this->runAction("UPDATE projects SET status = ? WHERE id = ?", "si", $newStatus, $cleanId);
            if ($ok) {
                $this->runAction(
                    "UPDATE project_moderation_logs SET status = 'Resolved', resolved_at = NOW() WHERE project_id = ? AND status = 'Open'",
                    "i",
                    $cleanId
                );
                $proj = $this->getProjectById($cleanId);
                if (!empty($proj['organization_email'])) {
                    $notifTitle = $wasHold ? 'Project Restored & Published' : 'Project Approved & Published';
                    $notifMsg   = $wasHold 
                        ? "Your project '{$proj['title']}' has been verified by Admin and restored to Published status! It is now open to students."
                        : "Great news! Your project '{$proj['title']}' has been reviewed and approved by Admin. It is now published and open for students to apply!";
                    $this->addNotification($proj['organization_email'], $notifTitle, $notifMsg, 'project');
                }
            }
            return $ok;
        } else {
            $ok = $this->runAction("UPDATE projects SET status = ? WHERE id = ?", "si", strtolower($statusClean), $cleanId);
            if ($ok && strcasecmp($statusClean, 'Rejected') === 0) {
                $this->runAction(
                    "INSERT INTO project_moderation_logs (project_id, admin_email, action_type, reason, status) VALUES (?, ?, ?, ?, 'Open')",
                    "isss",
                    $cleanId, $adminEmail, 'Rejected', $holdReason
                );
                $proj = $this->getProjectById($cleanId);
                if (!empty($proj['organization_email'])) {
                    $this->addNotification($proj['organization_email'], 'Project Rejected', "Your project '{$proj['title']}' has been permanently rejected. Reason: {$holdReason}", 'project');
                }
            }
            return $ok;
        }
    }
}
?>
