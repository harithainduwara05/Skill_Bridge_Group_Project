<?php
require_once __DIR__ . '/AdminDB.php';

class InternshipManagement extends AdminDB
{
    public function getActiveInternshipCount()
    {
        return (int) $this->runCount(
            "SELECT COUNT(*) FROM internships WHERE id NOT IN (SELECT internship_id FROM internship_moderation_logs WHERE status = 'Suspended')"
        );
    }

    public function getSuspendedInternshipCount()
    {
        return (int) $this->runCount(
            "SELECT COUNT(DISTINCT internship_id) FROM internship_moderation_logs WHERE status = 'Suspended'"
        );
    }

    public function getAllInternships()
    {
        $sql = "
            SELECT 
                i.*,
                c.Name AS company,
                ind.industry_name AS industry,
                CONCAT(i.duration_value, ' ', i.duration_unit, IF(i.duration_value > 1, 's', '')) AS duration,
                DATE_FORMAT(i.deadline_date, '%b %d, %Y') AS deadline,
                (SELECT COUNT(*) FROM internship_applications WHERE internship_id = i.id) AS applications_count,
                (SELECT GROUP_CONCAT(t.tag_name ORDER BY t.tag_name SEPARATOR ', ') 
                 FROM internship_tech_tag_map tm 
                 INNER JOIN internship_tech_tags t ON tm.tag_id = t.tag_id 
                 WHERE tm.internship_id = i.id) AS tech_tags,
                COALESCE((SELECT status FROM internship_moderation_logs WHERE internship_id = i.id ORDER BY id DESC LIMIT 1), 'Active') AS status,
                (SELECT admin_reason FROM internship_moderation_logs WHERE internship_id = i.id ORDER BY id DESC LIMIT 1) AS admin_reason
            FROM internships i
            INNER JOIN company c ON i.company_email = c.Email
            INNER JOIN internship_industries ind ON i.industry_id = ind.industry_id
            ORDER BY i.id DESC
        ";
        $res = $this->runQuery($sql);
        return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function getInternshipById($internshipId)
    {
        $cleanId = (int) preg_replace('/[^0-9]/', '', (string)$internshipId);
        $sql = "
            SELECT 
                i.*,
                c.Name AS company,
                ind.industry_name AS industry,
                CONCAT(i.duration_value, ' ', i.duration_unit, IF(i.duration_value > 1, 's', '')) AS duration,
                DATE_FORMAT(i.deadline_date, '%b %d, %Y') AS deadline,
                (SELECT COUNT(*) FROM internship_applications WHERE internship_id = i.id) AS applications_count,
                (SELECT GROUP_CONCAT(t.tag_name ORDER BY t.tag_name SEPARATOR ', ') 
                 FROM internship_tech_tag_map tm 
                 INNER JOIN internship_tech_tags t ON tm.tag_id = t.tag_id 
                 WHERE tm.internship_id = i.id) AS tech_tags,
                COALESCE((SELECT status FROM internship_moderation_logs WHERE internship_id = i.id ORDER BY id DESC LIMIT 1), 'Active') AS status,
                (SELECT admin_reason FROM internship_moderation_logs WHERE internship_id = i.id ORDER BY id DESC LIMIT 1) AS admin_reason
            FROM internships i
            INNER JOIN company c ON i.company_email = c.Email
            INNER JOIN internship_industries ind ON i.industry_id = ind.industry_id
            WHERE i.id = ?
            LIMIT 1
        ";
        $res = $this->runQuery($sql, "i", $cleanId);
        return ($res && $res->num_rows > 0) ? $res->fetch_assoc() : null;
    }

    public function getDistinctIndustries()
    {
        $res = $this->runQuery("SELECT industry_name AS industry FROM internship_industries ORDER BY industry_name ASC");
        return $res ? array_column($res->fetch_all(MYSQLI_ASSOC), 'industry') : [];
    }

    public function updateInternshipStatus($internshipId, $status, $reason = null)
    {
        $cleanId = (int) preg_replace('/[^0-9]/', '', (string)$internshipId);
        $statusClean = trim($status);
        $adminEmail = $_SESSION['user']['Email'] ?? $_SESSION['user']['email'] ?? 'skillbridge62@gmail.com';
        $item = $this->getInternshipById($cleanId);
        $compEmail = $item['company_email'] ?? '';

        if (strcasecmp($statusClean, 'Suspended') === 0) {
            $ok = $this->runAction(
                "INSERT INTO internship_moderation_logs (internship_id, admin_email, admin_reason, status) VALUES (?, ?, ?, 'Suspended')",
                "isss",
                $cleanId, $adminEmail, $reason ?? 'Suspended by Administrator'
            );
            if ($ok && !empty($compEmail)) {
                $this->addNotification($compEmail, 'Internship Suspended', "Your listing '{$item['title']}' has been suspended: {$reason}", 'internship');
            }
            return $ok;
        } elseif (strcasecmp($statusClean, 'Active') === 0) {
            $ok = $this->runAction(
                "UPDATE internship_moderation_logs SET status = 'Active', resolved_at = NOW() WHERE internship_id = ? AND status = 'Suspended'",
                "i",
                $cleanId
            );
            if ($ok && !empty($compEmail)) {
                $this->addNotification($compEmail, 'Internship Reactivated', "Your listing '{$item['title']}' has been restored to Active status.", 'internship');
            }
            return $ok;
        } else {
            $ok = $this->runAction(
                "INSERT INTO internship_moderation_logs (internship_id, admin_email, admin_reason, status) VALUES (?, ?, ?, ?)",
                "isss",
                $cleanId, $adminEmail, $reason ?? 'Status updated by Administrator', $statusClean
            );
            if ($ok && !empty($compEmail) && strcasecmp($statusClean, 'Terminated') === 0) {
                $this->addNotification($compEmail, 'Internship Terminated', "Your listing '{$item['title']}' has been permanently terminated: {$reason}", 'internship');
            }
            return $ok;
        }
    }
}
?>
