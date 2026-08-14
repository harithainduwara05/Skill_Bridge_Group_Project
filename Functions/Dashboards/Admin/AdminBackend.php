<?php

class AdminDB
{
    private $conn;
    private $allowedTables = ['user', 'student', 'company', 'organization', 'admin', 'universityemails', 'projects', 'internships', 'student_projects', 'complain'];

    public function __construct($conn)
    {
        $this->conn = $conn;
    }
    private function runCount($sql, $types = "", ...$params)
    {
        $stmt = $this->conn->prepare($sql);
        if ($types) $stmt->bind_param($types, ...$params);
        $stmt->execute();
        return $stmt->get_result()->fetch_row()[0];
    }

    private function runQuery($sql, $types = "", ...$params)
    {
        $stmt = $this->conn->prepare($sql);
        if ($types) $stmt->bind_param($types, ...$params);
        $stmt->execute();
        return $stmt->get_result();
    }

    private function runAction($sql, $types, ...$params)
    {
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        return $stmt->execute();
    }

    public function getCount($table)
    {
        if (!in_array($table, $this->allowedTables)) return 0;
        return $this->runCount("SELECT COUNT(*) FROM $table");
    }

    public function getCountWhere($table, $column, $value)
    {
        if (!in_array($table, $this->allowedTables)) return 0;
        return $this->runCount("SELECT COUNT(*) FROM $table WHERE $column = ?", "s", $value);
    }

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

    public function getUndismissedComplaintCount()
    {
        return $this->runCount("SELECT COUNT(*) FROM complain WHERE status != 'DISMISSED'");
    }

    public function getStudentCountByDomain($domain)
    {
        return $this->runCount("SELECT COUNT(*) FROM student WHERE Email LIKE ?", "s", '%@' . $domain);
    }


    public function getUsers()
    {
        $sql = "
        SELECT s.Name AS user_name, s.Email AS email, 'Student' AS role, s.University AS organization_name, u.status AS status
        FROM student s INNER JOIN user u ON s.Email = u.Email
        UNION ALL
        SELECT c.contactPersonName AS user_name, c.Email AS email, 'Company' AS role, c.Name AS organization_name, u.status AS status
        FROM company c INNER JOIN user u ON c.Email = u.Email
        UNION ALL
        SELECT o.contactPersonName AS user_name, o.Email AS email, 'Organization' AS role, o.Name AS organization_name, u.status AS status
        FROM organization o INNER JOIN user u ON o.Email = u.Email
        UNION ALL
        SELECT a.Name AS user_name, a.Email AS email, 'Admin' AS role, 'System' AS organization_name, u.status AS status
        FROM admin a INNER JOIN user u ON a.Email = u.Email
        ";
        return $this->runQuery($sql)->fetch_all(MYSQLI_ASSOC);
    }

    public function getPopularUni()
    {
        $sql = "SELECT ue.University AS 'UNIVERSITY NAME', COUNT(DISTINCT s.Email) AS 'STUDENTS',
                COUNT(DISTINCT sp.project_id) AS 'ACTIVE PROJECTS', ue.Status AS 'status'
                FROM student s
                JOIN universityemails ue ON SUBSTRING_INDEX(s.Email, '@', -1) = ue.emailEx
                LEFT JOIN student_projects sp ON s.Email = sp.Email AND sp.status = 'Active'
                GROUP BY ue.University
                ORDER BY `ACTIVE PROJECTS` DESC";
        return $this->runQuery($sql)->fetch_all(MYSQLI_ASSOC);
    }

    public function getComplaints()
    {
        return $this->runQuery("SELECT * FROM complain")->fetch_all(MYSQLI_ASSOC);
    }

    public function getAllUniversities()
    {
        return $this->runQuery("SELECT * FROM universityemails");
    }

    // Complaint actions
    public function dismissComplaint($id)
    {
        return $this->runAction("UPDATE complain SET status = 'DISMISSED' WHERE id = ?", "i", $id);
    }

    // University CRUD
    public function domainExists($domain)
    {
        return $this->runQuery("SELECT emailEx FROM universityemails WHERE emailEx = ?", "s", $domain)->num_rows > 0;
    }

    public function addUniversity($university, $faculty, $domain, $status, $location)
    {
        return $this->runAction("INSERT INTO universityemails (University, faculty, emailEx, Status, Location) VALUES (?, ?, ?, ?, ?)", "sssss", $university, $faculty, $domain, $status, $location);
    }

    public function updateUniversity($university, $faculty, $domain, $status, $location, $origDomain)
    {
        return $this->runAction("UPDATE universityemails SET University=?, faculty=?, emailEx=?, Status=?, Location=? WHERE emailEx=?", "ssssss", $university, $faculty, $domain, $status, $location, $origDomain);
    }

    public function deleteUniversity($domain)
    {
        return $this->runAction("DELETE FROM universityemails WHERE emailEx = ?", "s", $domain);
    }
}

$adminDB = new AdminDB($conn);

//get total users
$totalUsers = $adminDB->getCount("user");
//get total university
$totUni = $adminDB->getCount("universityemails");
//get total ongoing projects
$tot_Ongoin_projects = $adminDB->getOngoingProjectCount();
//available internships
$tot_internship = $adminDB->getAvailableInternshipCount();
//all users
$allusers = $adminDB->getUsers();
//get all complaints
$complains = $adminDB->getComplaints();
//popular universities
$popularUni = $adminDB->getPopularUni();
//active student projects
$totalAcPro = $adminDB->getActiveProjectCount();

?>