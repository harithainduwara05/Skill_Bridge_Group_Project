<?php
require_once __DIR__ . '/AdminDB.php';
class ProjectManagement extends AdminDB
{
    public function getActiveProjectCount()
    {
        return (int) $this->runCount(
            "SELECT COUNT(*) FROM projects WHERE LOWER(status) IN ('inprogress', 'active')"
        );
    }

    public function getAllProjects()
    {
        $res = $this->runQuery("SELECT * FROM projects ORDER BY id DESC");
        return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function getProjectById($projectId)
    {
        $cleanId = (int) preg_replace('/[^0-9]/', '', (string)$projectId);
        $res = $this->runQuery("SELECT * FROM projects WHERE id = ? LIMIT 1", "i", $cleanId);
        return ($res && $res->num_rows > 0) ? $res->fetch_assoc() : null;
    }

    public function getDistinctCategories()
    {
        $res = $this->runQuery("SELECT DISTINCT category FROM projects WHERE category IS NOT NULL AND TRIM(category) <> '' ORDER BY category ASC");
        return $res ? array_column($res->fetch_all(MYSQLI_ASSOC), 'category') : [];
    }

    public function updateProjectStatus($projectId, $status, $holdReason = null, $heldBy = null)
    {
        $cleanId = (int) preg_replace('/[^0-9]/', '', (string)$projectId);
        $statusClean = trim($status);

        if (strcasecmp($statusClean, 'Hold') === 0) {
            $ok = $this->runAction(
                "UPDATE projects SET status = 'Hold', hold_reason = ?, held_by = ?, held_at = NOW() WHERE id = ?",
                "ssi", $holdReason, $heldBy, $cleanId
            );
            if ($ok) {
                $proj = $this->getProjectById($cleanId);
                if (!empty($proj['organization_email'])) {
                    $this->addNotification($proj['organization_email'], 'Project on Hold', "Your project '{$proj['title']}' has been placed on hold: {$holdReason}", 'project');
                }
            }
            return $ok;
        } elseif (strcasecmp($statusClean, 'Active') === 0) {
            $ok = $this->runAction("UPDATE projects SET status = 'Active', hold_resolved_at = NOW() WHERE id = ?", "i", $cleanId);
            if ($ok) {
                $proj = $this->getProjectById($cleanId);
                if (!empty($proj['organization_email'])) {
                    $this->addNotification($proj['organization_email'], 'Project Reactivated', "Your project '{$proj['title']}' has been restored to Active status.", 'project');
                }
            }
            return $ok;
        } else {
            $ok = $this->runAction("UPDATE projects SET status = ?, hold_reason = ? WHERE id = ?", "ssi", $statusClean, $holdReason, $cleanId);
            if ($ok && strcasecmp($statusClean, 'Rejected') === 0) {
                $proj = $this->getProjectById($cleanId);
                if (!empty($proj['organization_email'])) {
                    $this->addNotification($proj['organization_email'], 'Project Rejected', "Your project '{$proj['title']}' has been rejected: {$holdReason}", 'project');
                }
            }
            return $ok;
        }
    }
}
?>
