<?php
/**
 * SkillBridge - University Registry Service
 * Handles accredited universities, faculty records, authorized email domains, and metrics.
 */

require_once __DIR__ . '/AdminDB.php';

class UniversityManagement extends AdminDB
{
    public function getStudentCountByDomain($domain)
    {
        $cleanDomain = ltrim(strtolower(trim($domain)), '@');
        return $this->runCount("SELECT COUNT(*) FROM student WHERE Email LIKE ?", "s", '%@' . $cleanDomain);
    }

    public function getPopularUni()
    {
        $sql = "
            SELECT 
                ue.University AS 'UNIVERSITY NAME', 
                COUNT(DISTINCT s.Email) AS 'STUDENTS',
                (SELECT COUNT(DISTINCT sp.project_id) 
                 FROM student_projects sp 
                 INNER JOIN student st ON sp.Email = st.Email 
                 WHERE (st.domain = ue.emailEx OR st.University = ue.University) AND sp.status = 'Active') AS 'ACTIVE PROJECTS', 
                ue.Status AS 'status'
            FROM universityemails ue
            INNER JOIN student s ON (s.domain = ue.emailEx OR s.University = ue.University)
            GROUP BY ue.University, ue.Status, ue.emailEx
            ORDER BY `ACTIVE PROJECTS` DESC
        ";
        return $this->runQuery($sql)->fetch_all(MYSQLI_ASSOC);
    }

    public function getAllUniversities($status = 'all', $search = '')
    {
        $sql = "SELECT * FROM universityemails WHERE 1=1";
        $params = [];
        $types = "";

        if (!empty($status) && $status !== 'all') {
            $sql .= " AND Status = ?";
            $params[] = $status;
            $types .= "s";
        }

        if (!empty($search)) {
            $sql .= " AND (University LIKE ? OR emailEx LIKE ? OR Location LIKE ? OR faculty LIKE ?)";
            $searchTerm = '%' . $search . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $types .= "ssss";
        }

        $sql .= " ORDER BY University ASC";

        if (!empty($types)) {
            return $this->runQuery($sql, $types, ...$params);
        }
        return $this->runQuery($sql);
    }

    public function domainExists($domain)
    {
        return $this->runQuery("SELECT emailEx FROM universityemails WHERE emailEx = ?", "s", $domain)->num_rows > 0;
    }

    public function getUniversityByDomain($domain)
    {
        $res = $this->runQuery("SELECT * FROM universityemails WHERE emailEx = ?", "s", $domain);
        return ($res && $res->num_rows > 0) ? $res->fetch_assoc() : null;
    }

    public function addUniversity($university, $faculty, $domain, $status, $location)
    {
        $ok = $this->runAction("INSERT INTO universityemails (University, faculty, emailEx, Status, Location) VALUES (?, ?, ?, ?, ?)", "sssss", $university, $faculty, $domain, $status, $location);
        
        // Keep 3NF university_faculties companion table synchronized
        @$this->runAction(
            "INSERT INTO university_faculties (domain, University, faculty, Location, Status) VALUES (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE University=VALUES(University), faculty=VALUES(faculty), Location=VALUES(Location), Status=VALUES(Status)",
            "sssss",
            $domain, $university, $faculty, $location, $status
        );
        return $ok;
    }

    public function updateUniversity($university, $faculty, $domain, $status, $location, $origDomain)
    {
        $ok = $this->runAction("UPDATE universityemails SET University=?, faculty=?, emailEx=?, Status=?, Location=? WHERE emailEx=?", "ssssss", $university, $faculty, $domain, $status, $location, $origDomain);

        // Keep 3NF university_faculties companion table synchronized
        @$this->runAction(
            "UPDATE university_faculties SET University=?, faculty=?, domain=?, Status=?, Location=? WHERE domain=?",
            "ssssss",
            $university, $faculty, $domain, $status, $location, $origDomain
        );
        return $ok;
    }

    public function deleteUniversity($domain)
    {
        $ok = $this->runAction("DELETE FROM universityemails WHERE emailEx = ?", "s", $domain);
        @$this->runAction("DELETE FROM university_faculties WHERE domain = ?", "s", $domain);
        return $ok;
    }

    public function getUniversityFacultiesList()
    {
        $sql = "SELECT DISTINCT University, faculty, emailEx, Status FROM universityemails WHERE Status != 'De-Active' ORDER BY University ASC, faculty ASC";
        $res = $this->runQuery($sql);
        return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function getAuthorizedDomain($university, $faculty)
    {
        $sql = "SELECT LOWER(emailEx) AS domain FROM universityemails WHERE University = ? AND faculty = ? AND Status = 'Active' LIMIT 1";
        $res = $this->runQuery($sql, "ss", $university, $faculty);
        if ($res && $row = $res->fetch_assoc()) {
            return ltrim(trim($row['domain']), '@');
        }
        return null;
    }
}
?>