<?php
/**
 * SkillBridge - Base Database Service
 * 
 * Provides database connection management, protected query execution,
 * and common helper operations for all specialized admin services.
 */

class AdminDB
{
    protected $conn;
    protected $allowedTables = [
        'user', 'student', 'company', 'organization', 'admin', 
        'universityemails', 'university_faculties', 'projects', 'project', 
        'internships', 'internship', 'student_projects', 'complain', 
        'internship_applications', 'project_moderation_logs', 'internship_moderation_logs'
    ];

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    /**
     * Protected query runners accessible only by derived child classes
     */
    protected function runCount($sql, $types = "", ...$params)
    {
        $stmt = $this->conn->prepare($sql);
        if ($types) $stmt->bind_param($types, ...$params);
        $stmt->execute();
        return $stmt->get_result()->fetch_row()[0];
    }

    protected function runQuery($sql, $types = "", ...$params)
    {
        $stmt = $this->conn->prepare($sql);
        if ($types) $stmt->bind_param($types, ...$params);
        $stmt->execute();
        return $stmt->get_result();
    }

    protected function runAction($sql, $types, ...$params)
    {
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        return $stmt->execute();
    }

    protected function runInsert($sql, $types = "", ...$params)
    {
        $stmt = $this->conn->prepare($sql);
        if ($types) $stmt->bind_param($types, ...$params);
        return $stmt->execute() ? $this->conn->insert_id : false;
    }

    /**
     * Common helper methods for other sub classes
     */
    public function getCount($table)
    {
        $tableLower = strtolower($table);
        if (!in_array($tableLower, $this->allowedTables)) return 0;
        $actualTable = ($tableLower === 'project') ? 'projects' : (($tableLower === 'internship') ? 'internships' : $tableLower);
        return $this->runCount("SELECT COUNT(*) FROM $actualTable");
    }

    public function getCountWhere($table, $column, $value)
    {
        $tableLower = strtolower($table);
        if (!in_array($tableLower, $this->allowedTables)) return 0;
        $actualTable = ($tableLower === 'project') ? 'projects' : (($tableLower === 'internship') ? 'internships' : $tableLower);
        
        // Handle internships suspension count from moderation logs if status column not in internships
        if ($actualTable === 'internships' && strtolower($column) === 'status' && strtolower($value) === 'suspended') {
            return $this->runCount("SELECT COUNT(DISTINCT internship_id) FROM internship_moderation_logs WHERE status = 'Suspended'");
        }

        return $this->runCount("SELECT COUNT(*) FROM $actualTable WHERE $column = ?", "s", $value);
    }

    public function addNotification($email, $title, $message, $type = 'complaint')
    {
        return $this->runAction(
            "INSERT INTO notifications (Email, title, message, type, status, created_at) VALUES (?, ?, ?, ?, 'Unread', CURRENT_TIMESTAMP)",
            "ssss",
            $email, $title, $message, $type
        );
    }
}
?>
