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
        $sql = "SELECT * FROM projects ORDER BY id DESC";
        $res = $this->runQuery($sql);
        return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    }
    public function getProjectById($projectId)
    {
        $sql = "SELECT * FROM projects WHERE id = ? LIMIT 1";
        $res = $this->runQuery($sql, "i", (int) $projectId);
        return $res ? $res->fetch_assoc() : null;
    }
    public function getDistinctCategories()
    {
        $sql = "SELECT DISTINCT category FROM projects WHERE category IS NOT NULL AND TRIM(category) <> '' ORDER BY category ASC";
        $res = $this->runQuery($sql);
        return $res ? array_column($res->fetch_all(MYSQLI_ASSOC), 'category') : [];
    }
    public function updateProjectStatus($projectId, $status, $holdReason = null, $heldBy = null)
    {
        $statusClean = trim($status);
        if ($statusClean === 'Hold') {
            return $this->runAction(
                "UPDATE projects SET status = 'Hold', hold_reason = ?, held_by = ?, held_at = NOW() WHERE id = ?",
                "ssi",
                $holdReason,
                $heldBy,
                (int) $projectId
            );
        } elseif ($statusClean === 'Active') {
            return $this->runAction(
                "UPDATE projects SET status = 'Active', hold_resolved_at = NOW() WHERE id = ?",
                "i",
                (int) $projectId
            );
        } else {
            return $this->runAction(
                "UPDATE projects SET status = ? WHERE id = ?",
                "si",
                $statusClean,
                (int) $projectId
            );
        }
    }
}
