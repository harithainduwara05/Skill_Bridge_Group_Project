<?php
/**
 * SkillBridge - User Management Service
 * Handles user onboarding, authentication credentials, role administration, and profiles.
 */

require_once __DIR__ . '/AdminDB.php';

class UserManagement extends AdminDB
{
    public function userExists($email)
    {
        return $this->runQuery("SELECT Email FROM user WHERE Email = ?", "s", $email)->num_rows > 0;
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

    public function getAllUsersDetailed($role = '', $status = '', $search = '')
    {
        $cleanRole = strtolower(trim($role));
        $queries = [];
        $params = [];
        $types = "";

        // Student subquery
        if (empty($cleanRole) || $cleanRole === 'all' || $cleanRole === 'student') {
            $queries[] = "
                SELECT 
                    u.Email AS email, 'student' AS role, u.status AS status, u.created_at AS created_at,
                    s.Name AS user_name, s.University AS organization_name, s.profile_image AS profile_image,
                    '' AS contact_number, s.website AS website, '' AS location, s.degree AS degree, s.year AS academic_year, s.bio AS about
                FROM user u
                INNER JOIN student s ON u.Email = s.Email
            ";
        }

        // Company subquery
        if (empty($cleanRole) || $cleanRole === 'all' || $cleanRole === 'company') {
            $queries[] = "
                SELECT 
                    u.Email AS email, 'company' AS role, u.status AS status, u.created_at AS created_at,
                    c.contactPersonName AS user_name, c.Name AS organization_name, c.profile_img AS profile_image,
                    c.contactNumber AS contact_number, c.website AS website, c.location AS location, '' AS degree, '' AS academic_year, '' AS about
                FROM user u
                INNER JOIN company c ON u.Email = c.Email
            ";
        }

        // Organization subquery
        if (empty($cleanRole) || $cleanRole === 'all' || $cleanRole === 'organization') {
            $queries[] = "
                SELECT 
                    u.Email AS email, 'organization' AS role, u.status AS status, u.created_at AS created_at,
                    o.contactPersonName AS user_name, o.Name AS organization_name, o.logo AS profile_image,
                    o.contactNumber AS contact_number, o.website AS website, o.location AS location, '' AS degree, '' AS academic_year, o.about AS about
                FROM user u
                INNER JOIN organization o ON u.Email = o.Email
            ";
        }

        // Admin subquery
        if (empty($cleanRole) || $cleanRole === 'all' || $cleanRole === 'admin') {
            $queries[] = "
                SELECT 
                    u.Email AS email, 'admin' AS role, u.status AS status, u.created_at AS created_at,
                    a.Name AS user_name, 'System' AS organization_name, a.profile_image AS profile_image,
                    a.contactNumber AS contact_number, '' AS website, '' AS location, '' AS degree, '' AS academic_year, '' AS about
                FROM user u
                INNER JOIN admin a ON u.Email = a.Email
            ";
        }

        if (empty($queries)) {
            return [];
        }

        $unionSql = implode(" UNION ALL ", $queries);
        $sql = "SELECT * FROM ($unionSql) AS all_users WHERE 1=1";

        if (!empty($status) && $status !== 'all') {
            $sql .= " AND status = ?";
            $params[] = $status;
            $types .= "s";
        }

        if (!empty($search)) {
            $searchTerm = '%' . $search . '%';
            $sql .= " AND (email LIKE ? OR user_name LIKE ? OR organization_name LIKE ?)";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $types .= "sss";
        }

        $sql .= " ORDER BY created_at DESC";

        if (!empty($types)) {
            return $this->runQuery($sql, $types, ...$params)->fetch_all(MYSQLI_ASSOC);
        } else {
            return $this->runQuery($sql)->fetch_all(MYSQLI_ASSOC);
        }
    }

    public function getUserByEmail($email)
    {
        $userRes = $this->runQuery("SELECT Email, role, status, created_at FROM user WHERE Email = ? LIMIT 1", "s", $email);
        if (!$userRes || $userRes->num_rows === 0) {
            return null;
        }

        $u = $userRes->fetch_assoc();
        $role = strtolower(trim($u['role']));

        $data = [
            'email' => $u['Email'],
            'role' => $role,
            'status' => $u['status'],
            'created_at' => $u['created_at'],
            'user_name' => 'User',
            'organization_name' => 'System',
            'profile_image' => null,
            'contact_number' => '',
            'website' => '',
            'location' => '',
            'degree' => '',
            'academic_year' => '',
            'about' => ''
        ];

        if ($role === 'student') {
            $r = $this->runQuery("SELECT s.* FROM student s INNER JOIN user u ON s.Email = u.Email WHERE s.Email = ? LIMIT 1", "s", $email);
            if ($r && $row = $r->fetch_assoc()) {
                $data['user_name'] = $row['Name'] ?? 'Student';
                $data['organization_name'] = $row['University'] ?? '';
                $data['profile_image'] = $row['profile_image'];
                $data['degree'] = $row['degree'] ?? '';
                $data['academic_year'] = $row['year'] ?? '';
                $data['website'] = $row['website'] ?? '';
                $data['about'] = $row['bio'] ?? '';
            }
        } elseif ($role === 'company') {
            $r = $this->runQuery("SELECT c.* FROM company c INNER JOIN user u ON c.Email = u.Email WHERE c.Email = ? LIMIT 1", "s", $email);
            if ($r && $row = $r->fetch_assoc()) {
                $data['user_name'] = $row['contactPersonName'] ?? '';
                $data['organization_name'] = $row['Name'] ?? '';
                $data['profile_image'] = $row['profile_img'];
                $data['contact_number'] = $row['contactNumber'] ?? '';
                $data['website'] = $row['website'] ?? '';
                $data['location'] = $row['location'] ?? '';
            }
        } elseif ($role === 'organization') {
            $r = $this->runQuery("SELECT o.* FROM organization o INNER JOIN user u ON o.Email = u.Email WHERE o.Email = ? LIMIT 1", "s", $email);
            if ($r && $row = $r->fetch_assoc()) {
                $data['user_name'] = $row['contactPersonName'] ?? '';
                $data['organization_name'] = $row['Name'] ?? '';
                $data['profile_image'] = $row['logo'];
                $data['contact_number'] = $row['contactNumber'] ?? '';
                $data['website'] = $row['website'] ?? '';
                $data['location'] = $row['location'] ?? '';
                $data['about'] = $row['about'] ?? '';
            }
        } elseif ($role === 'admin') {
            $r = $this->runQuery("SELECT a.* FROM admin a INNER JOIN user u ON a.Email = u.Email WHERE a.Email = ? LIMIT 1", "s", $email);
            if ($r && $row = $r->fetch_assoc()) {
                $data['user_name'] = $row['Name'] ?? 'Admin';
                $data['profile_image'] = $row['profile_image'];
                $data['contact_number'] = $row['contactNumber'] ?? '';
            }
        }

        return $data;
    }

    public function addUser($email, $password, $role, $name, $status = 'Active', $orgOrUni = '', $contactNumber = '', $degree = '', $year = '')
    {
        $role = strtolower(trim($role));
        $email = trim($email);
        $name = trim($name);
        $hashPassword = sha1($password);
        $verificationCode = '';

        // Insert into user table
        $userSql = "INSERT INTO user (Email, password, role, status, verification_code) VALUES (?, ?, ?, ?, ?)";
        $inserted = $this->runAction($userSql, "sssss", $email, $hashPassword, $role, $status, $verificationCode);
        if (!$inserted) return false;

        // Insert into role-specific table
        switch ($role) {
            case 'student':
                $uni = !empty($orgOrUni) ? $orgOrUni : 'University';
                $deg = !empty($degree) ? $degree : 'General';
                $yr = !empty($year) ? $year : date('Y');
                $domain = ltrim(substr(strrchr($email, "@"), 1), '@');
                $this->runAction(
                    "INSERT INTO student (Email, domain, University, year, degree, Name, profile_completion) VALUES (?, ?, ?, ?, ?, ?, 0)",
                    "ssssss",
                    $email, $domain, $uni, $yr, $deg, $name
                );
                break;
                
            case 'company':
                $compName = !empty($orgOrUni) ? $orgOrUni : $name;
                $type = 'General';
                $contact = !empty($contactNumber) ? $contactNumber : '';
                $website = '';
                $location = '';
                $compStatus = ($status === 'Active') ? 'Verify' : 'Unverified';
                $this->runAction("INSERT INTO company (Email, Name, companytype, contactPersonName, contactNumber, website, location, Status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)", "ssssssss", $email, $compName, $type, $name, $contact, $website, $location, $compStatus);
                break;
                
            case 'organization':
                $orgName = !empty($orgOrUni) ? $orgOrUni : $name;
                $orgType = 'General';
                $contact = !empty($contactNumber) ? $contactNumber : '';
                $website = '';
                $location = '';
                $this->runAction("INSERT INTO organization (Name, orgtype, contactPersonName, contactNumber, website, location, Email) VALUES (?, ?, ?, ?, ?, ?, ?)", "sssssss", $orgName, $orgType, $name, $contact, $website, $location, $email);
                break;
                
            case 'admin':
                $this->runAction("INSERT INTO admin (Name, Email) VALUES (?, ?)", "ss", $name, $email);
                break;
        }

        return true;
    }

    public function updateUserStatus($email, $status)
    {
        return $this->runAction("UPDATE user SET status = ? WHERE Email = ?", "ss", $status, $email);
    }

    public function updateUser($email, $name, $role, $status, $orgOrUni = '', $contactNumber = '')
    {
        $role = strtolower(trim($role));
        $name = trim($name);
        
        // Update user table
        $this->runAction("UPDATE user SET role = ?, status = ? WHERE Email = ?", "sss", $role, $status, $email);

        // Update role specific table if exists
        switch ($role) {
            case 'student':
                $uni = !empty($orgOrUni) ? $orgOrUni : 'University';
                $chk = $this->runQuery("SELECT Email FROM student WHERE Email = ?", "s", $email);
                if ($chk && $chk->num_rows > 0) {
                    $this->runAction("UPDATE student SET Name = ?, University = ? WHERE Email = ?", "sss", $name, $uni, $email);
                } else {
                    $domain = ltrim(substr(strrchr($email, "@"), 1), '@');
                    $this->runAction("INSERT INTO student (Email, domain, University, year, degree, Name, profile_completion) VALUES (?, ?, ?, ?, ?, ?, 0)", "ssssss", $email, $domain, $uni, date('Y'), 'General', $name);
                }
                break;
                
            case 'company':
                $compName = !empty($orgOrUni) ? $orgOrUni : $name;
                $contact = !empty($contactNumber) ? $contactNumber : '';
                $chk = $this->runQuery("SELECT Email FROM company WHERE Email = ?", "s", $email);
                if ($chk && $chk->num_rows > 0) {
                    $this->runAction("UPDATE company SET contactPersonName = ?, Name = ?, contactNumber = ? WHERE Email = ?", "ssss", $name, $compName, $contact, $email);
                } else {
                    $this->runAction("INSERT INTO company (Email, Name, companytype, contactPersonName, contactNumber, website, location, Status) VALUES (?, ?, 'General', ?, ?, '', '', 'Verify')", "sssss", $email, $compName, $name, $contact);
                }
                break;
                
            case 'organization':
                $orgName = !empty($orgOrUni) ? $orgOrUni : $name;
                $contact = !empty($contactNumber) ? $contactNumber : '';
                $chk = $this->runQuery("SELECT Email FROM organization WHERE Email = ?", "s", $email);
                if ($chk && $chk->num_rows > 0) {
                    $this->runAction("UPDATE organization SET contactPersonName = ?, Name = ?, contactNumber = ? WHERE Email = ?", "ssss", $name, $orgName, $contact, $email);
                } else {
                    $this->runAction("INSERT INTO organization (Name, orgtype, contactPersonName, contactNumber, website, location, Email) VALUES (?, 'General', ?, ?, '', '', ?)", "ssss", $orgName, $name, $contact, $email);
                }
                break;
                
            case 'admin':
                $chk = $this->runQuery("SELECT Email FROM admin WHERE Email = ?", "s", $email);
                if ($chk && $chk->num_rows > 0) {
                    $this->runAction("UPDATE admin SET Name = ? WHERE Email = ?", "ss", $name, $email);
                } else {
                    $this->runAction("INSERT INTO admin (Name, Email) VALUES (?, ?)", "ss", $name, $email);
                }
                break;
        }

        return true;
    }

    public function deleteUser($email)
    {
        $this->runAction("DELETE FROM student WHERE Email = ?", "s", $email);
        $this->runAction("DELETE FROM company WHERE Email = ?", "s", $email);
        $this->runAction("DELETE FROM organization WHERE Email = ?", "s", $email);
        $this->runAction("DELETE FROM admin WHERE Email = ?", "s", $email);
        return $this->runAction("DELETE FROM user WHERE Email = ?", "s", $email);
    }

    public function resetUserPassword($email, $newPassword)
    {
        $hashPassword = sha1($newPassword);
        return $this->runAction("UPDATE user SET password = ? WHERE Email = ?", "ss", $hashPassword, $email);
    }

    public function getUserManagementStats()
    {
        return [
            'total' => $this->runCount("SELECT COUNT(*) FROM user"),
            'active' => $this->runCount("SELECT COUNT(*) FROM user WHERE status = 'Active'"),
            'inactive' => $this->runCount("SELECT COUNT(*) FROM user WHERE status != 'Active'"),
            'students' => $this->runCount("SELECT COUNT(*) FROM user WHERE LOWER(role) = 'student'"),
            'companies' => $this->runCount("SELECT COUNT(*) FROM user WHERE LOWER(role) = 'company'"),
            'organizations' => $this->runCount("SELECT COUNT(*) FROM user WHERE LOWER(role) = 'organization'"),
            'admins' => $this->runCount("SELECT COUNT(*) FROM user WHERE LOWER(role) = 'admin'"),
        ];
    }
}
?>