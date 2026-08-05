<?php
class DashboardManager
{
    public function getTotalCount($tableName)
    {
        global $conn;
        $query = "SELECT COUNT(*) FROM $tableName";
        $stmt = $conn->prepare($query);
        $stmt->execute();
        return $stmt->get_result()->fetch_row()[0];
    }
    public function getUsers()
    {
        global $conn;
        $sql = "
        SELECT 
            s.Name AS user_name, 
            s.Email AS email, 
            'Student' AS role, 
            s.University AS organization_name, 
            u.status AS status
        FROM student s
        INNER JOIN user u ON s.Email = u.Email

        UNION ALL

        SELECT 
            c.contactPersonName AS user_name, 
            c.Email AS email, 
            'Company' AS role, 
            c.companyName AS organization_name, 
            u.status AS status
        FROM company c
        INNER JOIN user u ON c.Email = u.Email

        UNION ALL

        SELECT 
            o.contactPersonName AS user_name, 
            o.Email AS email, 
            'Organization' AS role, 
            o.organizationName AS organization_name, 
            u.status AS status
        FROM organization o
        INNER JOIN user u ON o.Email = u.Email

        UNION ALL

        SELECT 
            a.Name AS user_name, 
            a.Email AS email, 
            'Admin' AS role, 
            'System' AS organization_name, 
            u.status AS status 
        FROM admin a
        INNER JOIN user u ON a.Email = u.Email
        ";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    public function getAll($sqlback)
    {
        global $conn;
        $sql = "SELECT * FROM $sqlback";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    public function update_db($sqlback, $tableName)
    {
        global $conn;
        $sql = "UPDATE $tableName SET $sqlback";
        $stmt = $conn->prepare($sql);
        return $stmt->execute();
    }
}

$dashboardManager = new DashboardManager();
//get total users
$totalUsers = $dashboardManager->getTotalCount("user");
//get total university
$totUni = $dashboardManager->getTotalCount("universityemails");
//get total Project
$tot_Ongoin_projects = $dashboardManager->getTotalCount("projects WHERE STR_TO_DATE(deadline, '%b %d, %Y') >= CURRENT_DATE()");
//availbale Internship
$tot_internship = $dashboardManager->getTotalCount("internships WHERE STR_TO_DATE(deadline, '%b %d, %Y') >= CURRENT_DATE()");
//All Users
$allusers = $dashboardManager->getUsers();
//Get All COmplain 
$complains = $dashboardManager->getAll("complain");

?>