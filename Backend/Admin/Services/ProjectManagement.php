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

            $ok = $this->runAction("UPDATE projects SET status = ?, hold_resolved_at = NOW() WHERE id = ?", "si", $newStatus, $cleanId);
            if ($ok) {
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
            $ok = $this->runAction("UPDATE projects SET status = ?, hold_reason = ? WHERE id = ?", "ssi", $statusClean, $holdReason, $cleanId);
            if ($ok && strcasecmp($statusClean, 'Rejected') === 0) {
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
