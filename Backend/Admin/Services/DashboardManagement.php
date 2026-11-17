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
        return $this->runCount("SELECT COUNT(*) FROM projects WHERE deadline >= CURRENT_DATE()");
    }

    public function getAvailableInternshipCount()
    {
        return $this->runCount("SELECT COUNT(*) FROM internships WHERE deadline_date >= CURRENT_DATE()");
    }

    public function getActiveProjectCount()
    {
        return $this->runCount("SELECT COUNT(*) FROM student_projects WHERE status = 'Active'");
    }
}
?>