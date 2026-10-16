<?php
/**
 * SkillBridge - Dashboard Service
 * Handles metrics and counts for active projects, internships, and platform operations.
 */

require_once __DIR__ . '/AdminDB.php';

class DashboardManagement extends AdminDB
{
    public function getOngoingProjectCount()
    {
        return $this->runCount("SELECT COUNT(*) FROM projects WHERE STR_TO_DATE(deadline, '%b %d, %Y') >= CURRENT_DATE()");
    }

    public function getAvailableInternshipCount()
    {
        return $this->runCount("SELECT COUNT(*) FROM internships WHERE STR_TO_DATE(deadline, '%b %d, %Y') >= CURRENT_DATE()");
    }

    public function getActiveProjectCount()
    {
        return $this->runCount("SELECT COUNT(*) FROM student_projects WHERE status = 'Active'");
    }

    public function getPlacedStudentsCount()
    {
        return (int) $this->runCount("SELECT COUNT(*) FROM internship_applications WHERE LOWER(status) = 'accepted'");
    }

    public function getActiveDriveCount()
    {
        return (int) $this->runCount("SELECT COUNT(*) FROM internships WHERE LOWER(status) = 'active'");
    }

    public function getSupervisorReviewProjectCount()
    {
        return (int) $this->runCount("SELECT COUNT(*) FROM projects WHERE LOWER(status) = 'reviewing'");
    }

    public function getResolvedComplaintsCount()
    {
        return (int) $this->runCount("SELECT COUNT(*) FROM complain WHERE LOWER(status) IN ('resolved', 'dismissed')");
    }

    public function getOpenComplaintsCount()
    {
        return (int) $this->runCount("SELECT COUNT(*) FROM complain WHERE LOWER(status) IN ('pending', 'in_review')");
    }

    public function getUniversityBenchmarks()
    {
        $sql = "SELECT u.University, u.Location, COUNT(s.Email) AS student_count 
                FROM universityemails u 
                LEFT JOIN student s ON LOWER(s.University) LIKE CONCAT('%', LOWER(SUBSTRING_INDEX(u.University, ' ', 3)), '%') 
                GROUP BY u.University, u.Location 
                ORDER BY student_count DESC";
        $res = $this->runQuery($sql);
        return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function getCompanyBenchmarks()
    {
        $sql = "SELECT c.Name, c.companytype, c.location, 
                COUNT(DISTINCT i.id) AS vacancies_count, 
                COUNT(ia.application_id) AS applications_count, 
                COALESCE(SUM(CASE WHEN LOWER(ia.status) = 'accepted' THEN 1 ELSE 0 END), 0) AS hired_count 
                FROM company c 
                LEFT JOIN internships i ON TRIM(LOWER(c.Name)) = TRIM(LOWER(i.company)) 
                LEFT JOIN internship_applications ia ON i.id = ia.internship_id 
                GROUP BY c.Email, c.Name, c.companytype, c.location 
                ORDER BY vacancies_count DESC";
        $res = $this->runQuery($sql);
        return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function getComplaintsLog($limit = 100)
    {
        $sql = "SELECT id, user_name, role, category, subject, status, created_at FROM complain ORDER BY id DESC LIMIT ?";
        $res = $this->runQuery($sql, "i", $limit);
        return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    }
}