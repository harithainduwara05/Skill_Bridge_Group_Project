<?php

require_once __DIR__ . '/AdminDB.php';

class InternshipManagement extends AdminDB
{
    public function getActiveInternshipCount()
    {
        return (int) $this->runCount("SELECT COUNT(*) FROM internships WHERE LOWER(status) = 'active'");
    }

    public function getAllInternships()
    {
        $sql = "SELECT i.*, COUNT(ia.application_id) AS applications_count 
                FROM internships i 
                LEFT JOIN internship_applications ia ON i.id = ia.internship_id 
                GROUP BY i.id 
                ORDER BY i.id DESC";
        $res = $this->runQuery($sql);
        return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function getInternshipById($internshipId)
    {
        $cleanId = (int) preg_replace('/[^0-9]/', '', (string)$internshipId);
        $sql = "SELECT i.*, COUNT(ia.application_id) AS applications_count 
                FROM internships i 
                LEFT JOIN internship_applications ia ON i.id = ia.internship_id 
                WHERE i.id = ? GROUP BY i.id LIMIT 1";
        $res = $this->runQuery($sql, "i", $cleanId);
        return ($res && $res->num_rows > 0) ? $res->fetch_assoc() : null;
    }

    public function getDistinctIndustries()
    {
        $res = $this->runQuery("SELECT DISTINCT industry FROM internships WHERE industry IS NOT NULL AND TRIM(industry) <> '' ORDER BY industry ASC");
        return $res ? array_column($res->fetch_all(MYSQLI_ASSOC), 'industry') : [];
    }

    public function updateInternshipStatus($internshipId, $status, $reason = null)
    {
        $cleanId = (int) preg_replace('/[^0-9]/', '', (string)$internshipId);
        $statusClean = trim($status);

        if (strcasecmp($statusClean, 'Suspended') === 0) {
            $ok = $this->runAction("UPDATE internships SET status = 'Suspended', admin_reason = ? WHERE id = ?", "si", $reason, $cleanId);
            if ($ok) {
                $item = $this->getInternshipById($cleanId);
                $compEmail = $this->getCompanyEmail($item['company'] ?? '');
                if ($compEmail) {
                    $this->addNotification($compEmail, 'Internship Suspended', "Your listing '{$item['title']}' has been suspended: {$reason}", 'internship');
                }
            }
            return $ok;
        } elseif (strcasecmp($statusClean, 'Active') === 0) {
            $ok = $this->runAction("UPDATE internships SET status = 'Active', admin_reason = NULL, reactivation_status = NULL WHERE id = ?", "i", $cleanId);
            if ($ok) {
                $item = $this->getInternshipById($cleanId);
                $compEmail = $this->getCompanyEmail($item['company'] ?? '');
                if ($compEmail) {
                    $this->addNotification($compEmail, 'Internship Reactivated', "Your listing '{$item['title']}' has been restored to Active status.", 'internship');
                }
            }
            return $ok;
        } else {
            $ok = $this->runAction("UPDATE internships SET status = ?, admin_reason = ? WHERE id = ?", "ssi", $statusClean, $reason, $cleanId);
            if ($ok && strcasecmp($statusClean, 'Terminated') === 0) {
                $item = $this->getInternshipById($cleanId);
                $compEmail = $this->getCompanyEmail($item['company'] ?? '');
                if ($compEmail) {
                    $this->addNotification($compEmail, 'Internship Terminated', "Your listing '{$item['title']}' has been permanently terminated: {$reason}", 'internship');
                }
            }
            return $ok;
        }
    }

    protected function getCompanyEmail($companyName)
    {
        if (empty($companyName)) return null;
        $res = $this->runQuery("SELECT Email FROM company WHERE TRIM(Name) = TRIM(?) LIMIT 1", "s", $companyName);
        if ($res && $row = $res->fetch_assoc()) return $row['Email'];
        return null;
    }
}
?>
