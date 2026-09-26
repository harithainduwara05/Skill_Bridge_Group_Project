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
        $cleanDomain = ltrim(strtolower(trim($domain)), '@');
        return $this->runCount("SELECT COUNT(*) FROM student WHERE Email LIKE ?", "s", '%@' . $cleanDomain);
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

    public function getUrgentComplaints($limit = 3)
    {
        $sql = "SELECT * FROM complain 
                WHERE status != 'DISMISSED' 
                ORDER BY 
                    CASE priority 
                        WHEN 'URGENT' THEN 1 
                        WHEN 'HIGH' THEN 2 
                        WHEN 'MEDIUM' THEN 3 
                        WHEN 'LOW' THEN 4 
                        ELSE 5 
                    END ASC, 
                    create_at DESC, 
                    id DESC 
                LIMIT ?";
        return $this->runQuery($sql, "i", $limit)->fetch_all(MYSQLI_ASSOC);
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

    // Complaint actions
    public function dismissComplaint($id)
    {
        return $this->runAction("UPDATE complain SET status = 'DISMISSED', update_at = CURRENT_TIMESTAMP WHERE id = ?", "i", $id);
    }

    public function addNotification($email, $title, $message, $type = 'complaint')
    {
        return $this->runAction(
            "INSERT INTO notifications (Email, title, message, type, status, created_at) VALUES (?, ?, ?, ?, 'Unread', CURRENT_TIMESTAMP)",
            "ssss",
            $email, $title, $message, $type
        );
    }

    public function updateComplaintStatus($id, $status, $notes = '')
    {
        $allowedStatuses = ['PENDING', 'IN_REVIEW', 'RESOLVED', 'DISMISSED'];
        $cleanStatus = strtoupper(str_replace(' ', '_', $status));
        if ($cleanStatus === 'IN_PROGRESS') {
            $cleanStatus = 'IN_REVIEW';
        }
        if (!in_array($cleanStatus, $allowedStatuses)) {
            return false;
        }

        // Fetch complaint details to get student/user email
        $cmp = $this->getComplaintById($id);

        if (!empty($notes)) {
            $updated = $this->runAction("UPDATE complain SET status = ?, resolution_notes = ?, update_at = CURRENT_TIMESTAMP WHERE id = ?", "ssi", $cleanStatus, $notes, $id);
        } else {
            $updated = $this->runAction("UPDATE complain SET status = ?, update_at = CURRENT_TIMESTAMP WHERE id = ?", "si", $cleanStatus, $id);
        }

        // Complainant Notification: Notify complainant regarding status and resolution/dismissal notes
        if ($updated && $cmp && !empty($cmp['email'])) {
            $recipientEmail = $cmp['email'];
            $complaintTitle = $cmp['title'] ?? 'Complaint';
            $paddedId = '#CMP-' . str_pad($id, 4, '0', STR_PAD_LEFT);

            if ($cleanStatus === 'IN_REVIEW') {
                $notifTitle = "Complaint Under Investigation ($paddedId)";
                $notifMsg = !empty($notes) 
                    ? "Admin update regarding \"$complaintTitle\": $notes"
                    : "Your reported issue ($paddedId) has been placed under active investigation by administrators.";
            } elseif ($cleanStatus === 'RESOLVED') {
                $notifTitle = "Complaint Resolved ($paddedId)";
                $notifMsg = !empty($notes) 
                    ? "Resolution for \"$complaintTitle\": $notes"
                    : "Your reported issue ($paddedId) has been successfully resolved and finalized.";
            } elseif ($cleanStatus === 'DISMISSED') {
                $notifTitle = "Complaint Dismissed ($paddedId)";
                $notifMsg = !empty($notes) 
                    ? "Your complaint \"$complaintTitle\" was dismissed. Reason: $notes"
                    : "Your reported issue ($paddedId) has been dismissed by administrators.";
            } else {
                $notifTitle = "Complaint Update ($paddedId)";
                $notifMsg = !empty($notes) ? $notes : "Your complaint status has been updated to $cleanStatus.";
            }

            $this->addNotification($recipientEmail, $notifTitle, $notifMsg, 'complaint');
        }

        return $updated;
    }

    public function getComplaintById($id)
    {
        $sql = "
        SELECT 
            c.id,
            c.email,
            c.title,
            c.discription,
            COALESCE(c.category, 'Technical') AS category,
            c.priority,
            c.status,
            c.resolution_notes,
            c.create_at,
            c.update_at,
            LOWER(COALESCE(u.role, 'user')) AS role,
            COALESCE(s.Name, comp.contactPersonName, o.contactPersonName, a.Name, 'Community Member') AS user_name,
            COALESCE(s.University, comp.Name, o.Name, 'SkillBridge') AS organization_name,
            s.profile_image AS profile_image
        FROM complain c
        LEFT JOIN user u ON c.email = u.Email
        LEFT JOIN student s ON c.email = s.Email
        LEFT JOIN company comp ON c.email = comp.Email
        LEFT JOIN organization o ON c.email = o.Email
        LEFT JOIN admin a ON c.email = a.Email
        WHERE c.id = ?
        LIMIT 1
        ";
        $res = $this->runQuery($sql, "i", $id);
        return $res ? $res->fetch_assoc() : null;
    }

    public function getAllComplaintsDetailed($status = 'all', $priority = 'all', $category = 'all', $search = '')
    {
        $sql = "
        SELECT 
            c.id,
            c.email,
            c.title,
            c.discription,
            COALESCE(c.category, 'Technical') AS category,
            c.priority,
            c.status,
            c.resolution_notes,
            c.create_at,
            c.update_at,
            LOWER(COALESCE(u.role, 'user')) AS role,
            COALESCE(s.Name, comp.contactPersonName, o.contactPersonName, a.Name, 'Community Member') AS user_name,
            COALESCE(s.University, comp.Name, o.Name, 'SkillBridge') AS organization_name,
            s.profile_image AS profile_image
        FROM complain c
        LEFT JOIN user u ON c.email = u.Email
        LEFT JOIN student s ON c.email = s.Email
        LEFT JOIN company comp ON c.email = comp.Email
        LEFT JOIN organization o ON c.email = o.Email
        LEFT JOIN admin a ON c.email = a.Email
        WHERE 1=1
        ";

        $params = [];
        $types = "";

        if (!empty($status) && $status !== 'all') {
            $mappedStatus = strtoupper(str_replace(' ', '_', $status));
            if ($mappedStatus === 'IN_PROGRESS') {
                $mappedStatus = 'IN_REVIEW';
            }
            $sql .= " AND c.status = ?";
            $params[] = $mappedStatus;
            $types .= "s";
        }

        if (!empty($priority) && $priority !== 'all') {
            $sql .= " AND c.priority = ?";
            $params[] = strtoupper($priority);
            $types .= "s";
        }

        if (!empty($category) && $category !== 'all') {
            $sql .= " AND c.category = ?";
            $params[] = $category;
            $types .= "s";
        }

        if (!empty($search)) {
            $searchTerm = '%' . $search . '%';
            $sql .= " AND (c.id LIKE ? OR c.email LIKE ? OR c.title LIKE ? OR c.discription LIKE ? OR s.Name LIKE ? OR comp.contactPersonName LIKE ? OR o.contactPersonName LIKE ? OR a.Name LIKE ?)";
            for ($i = 0; $i < 8; $i++) {
                $params[] = $searchTerm;
                $types .= "s";
            }
        }

        $sql .= " ORDER BY 
            CASE c.status
                WHEN 'PENDING' THEN 1
                WHEN 'IN_REVIEW' THEN 2
                WHEN 'RESOLVED' THEN 3
                WHEN 'DISMISSED' THEN 4
                ELSE 5
            END ASC,
            CASE c.priority 
                WHEN 'URGENT' THEN 1 
                WHEN 'HIGH' THEN 2 
                WHEN 'MEDIUM' THEN 3 
                WHEN 'LOW' THEN 4 
                ELSE 5 
            END ASC, 
            c.create_at DESC, 
            c.id DESC";

        if (!empty($types)) {
            return $this->runQuery($sql, $types, ...$params)->fetch_all(MYSQLI_ASSOC);
        }
        return $this->runQuery($sql)->fetch_all(MYSQLI_ASSOC);
    }

    public function getComplaintStats()
    {
        $total = $this->runCount("SELECT COUNT(*) FROM complain");
        $pending = $this->runCount("SELECT COUNT(*) FROM complain WHERE status IN ('PENDING', 'IN_REVIEW')");
        $resolved = $this->runCount("SELECT COUNT(*) FROM complain WHERE status = 'RESOLVED'");
        
        $avgMinutesRes = $this->runQuery("
            SELECT AVG(TIMESTAMPDIFF(MINUTE, create_at, update_at)) AS avg_mins 
            FROM complain 
            WHERE status = 'RESOLVED' AND update_at > create_at
        ")->fetch_assoc();
        
        if (!empty($avgMinutesRes['avg_mins']) && floatval($avgMinutesRes['avg_mins']) > 0) {
            $avgMins = round(floatval($avgMinutesRes['avg_mins']));
            $hours = floor($avgMins / 60);
            $mins = $avgMins % 60;
            $avgFormatted = "{$hours}h {$mins}m";
        } elseif ($resolved > 0) {
            $avgFormatted = "< 1h";
        } else {
            $avgFormatted = "N/A";
        }

        return [
            'total' => $total,
            'pending' => $pending,
            'resolved' => $resolved,
            'avg_response_time' => $avgFormatted
        ];
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

    // User Management CRUD & Stats
    public function userExists($email)
    {
        return $this->runQuery("SELECT Email FROM user WHERE Email = ?", "s", $email)->num_rows > 0;
    }

    public function getAllUsersDetailed($role = '', $status = '', $search = '')
    {
        $sql = "
        SELECT 
            u.Email AS email,
            LOWER(u.role) AS role,
            u.status AS status,
            u.created_at AS created_at,
            COALESCE(s.Name, c.contactPersonName, o.contactPersonName, a.Name, 'User') AS user_name,
            COALESCE(s.University, c.Name, o.Name, 'System') AS organization_name,
            s.profile_image AS profile_image,
            c.contactNumber AS contact_number,
            c.website AS website,
            s.degree AS degree,
            s.year AS academic_year
        FROM user u
        LEFT JOIN student s ON u.Email = s.Email
        LEFT JOIN company c ON u.Email = c.Email
        LEFT JOIN organization o ON u.Email = o.Email
        LEFT JOIN admin a ON u.Email = a.Email
        WHERE 1=1
        ";
        
        $params = [];
        $types = "";
        
        if (!empty($role) && $role !== 'all') {
            $sql .= " AND LOWER(u.role) = ?";
            $params[] = strtolower($role);
            $types .= "s";
        }
        
        if (!empty($status) && $status !== 'all') {
            $sql .= " AND u.status = ?";
            $params[] = $status;
            $types .= "s";
        }
        
        if (!empty($search)) {
            $searchTerm = '%' . $search . '%';
            $sql .= " AND (u.Email LIKE ? OR s.Name LIKE ? OR c.contactPersonName LIKE ? OR o.contactPersonName LIKE ? OR a.Name LIKE ? OR s.University LIKE ? OR c.Name LIKE ? OR o.Name LIKE ?)";
            for ($i = 0; $i < 8; $i++) {
                $params[] = $searchTerm;
                $types .= "s";
            }
        }
        
        $sql .= " ORDER BY u.created_at DESC";
        
        if (!empty($types)) {
            return $this->runQuery($sql, $types, ...$params)->fetch_all(MYSQLI_ASSOC);
        } else {
            return $this->runQuery($sql)->fetch_all(MYSQLI_ASSOC);
        }
    }

    public function getUserByEmail($email)
    {
        $sql = "
        SELECT 
            u.Email AS email,
            LOWER(u.role) AS role,
            u.status AS status,
            u.created_at AS created_at,
            COALESCE(s.Name, c.contactPersonName, o.contactPersonName, a.Name, 'User') AS user_name,
            COALESCE(s.University, c.Name, o.Name, 'System') AS organization_name,
            s.profile_image AS profile_image,
            c.contactNumber AS contact_number,
            c.website AS website,
            c.location AS location,
            s.degree AS degree,
            s.year AS academic_year,
            o.about AS about
        FROM user u
        LEFT JOIN student s ON u.Email = s.Email
        LEFT JOIN company c ON u.Email = c.Email
        LEFT JOIN organization o ON u.Email = o.Email
        LEFT JOIN admin a ON u.Email = a.Email
        WHERE u.Email = ?
        LIMIT 1
        ";
        $res = $this->runQuery($sql, "s", $email);
        return $res ? $res->fetch_assoc() : null;
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
                $this->runAction("INSERT INTO student (Email, University, year, degree, Name, profile_completion) VALUES (?, ?, ?, ?, ?, 0)", "sssss", $email, $uni, $yr, $deg, $name);
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
                    $this->runAction("INSERT INTO student (Email, University, year, degree, Name, profile_completion) VALUES (?, ?, ?, ?, ?, 0)", "sssss", $email, $uni, date('Y'), 'General', $name);
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

    //Admin Profile Management
    public function getAdminProfile($email)
    {
        $sql = "
            SELECT 
                a.Name AS name,
                a.Email AS email,
                a.profile_image AS profile_image,
                a.contactNumber AS contact_number,
                u.role AS role,
                u.status AS status,
                u.created_at AS created_at
            FROM admin a
            JOIN user u ON a.Email = u.Email
            WHERE a.Email = ?
            LIMIT 1
        ";
        $res = $this->runQuery($sql, "s", $email);
        if ($res && $res->num_rows > 0) {
            return $res->fetch_assoc();
        }
        // Fallback if not yet in admin table
        $userQuery = $this->runQuery("SELECT Email AS email, role, status, created_at FROM user WHERE Email = ?", "s", $email);
        if ($userQuery && $userQuery->num_rows > 0) {
            $u = $userQuery->fetch_assoc();
            return [
                'name' => 'Admin',
                'email' => $u['email'],
                'profile_image' => null,
                'contact_number' => '',
                'role' => $u['role'] ?? 'admin',
                'status' => $u['status'] ?? 'Active',
                'created_at' => $u['created_at'] ?? null,
            ];
        }
        return null;
    }

    public function updateAdminProfile($email, $name, $contactNumber = '', $profileImage = null)
    {
        $name = trim($name);
        $contactNumber = trim($contactNumber);
        
        // Ensure record in admin table exists
        $chk = $this->runQuery("SELECT Email FROM admin WHERE Email = ?", "s", $email);
        if (!$chk || $chk->num_rows === 0) {
            $this->runAction("INSERT INTO admin (Name, Email, contactNumber) VALUES (?, ?, ?)", "sss", $name, $email, $contactNumber);
        }

        if ($profileImage !== null) {
            $sql = "UPDATE admin SET Name = ?, contactNumber = ?, profile_image = ? WHERE Email = ?";
            return $this->runAction($sql, "ssss", $name, $contactNumber, $profileImage, $email);
        } else {
            $sql = "UPDATE admin SET Name = ?, contactNumber = ? WHERE Email = ?";
            return $this->runAction($sql, "sss", $name, $contactNumber, $email);
        }
    }

    public function removeAdminProfileImage($email)
    {
        $prof = $this->getAdminProfile($email);
        if ($prof && !empty($prof['profile_image'])) {
            $path = __DIR__ . '/../../../Assets/Images/Admin/' . $prof['profile_image'];
            if (file_exists($path)) {
                @unlink($path);
            }
        }
        return $this->runAction("UPDATE admin SET profile_image = NULL WHERE Email = ?", "s", $email);
    }

    public function changeAdminPassword($email, $currentPassword, $newPassword)
    {
        $currHash = sha1($currentPassword);
        $newHash = sha1($newPassword);

        // Verify current password
        $chk = $this->runQuery("SELECT Email FROM user WHERE Email = ? AND password = ?", "ss", $email, $currHash);
        if (!$chk || $chk->num_rows === 0) {
            return ['success' => false, 'message' => 'Current password does not match.'];
        }

        // Update password
        $updated = $this->runAction("UPDATE user SET password = ? WHERE Email = ?", "ss", $newHash, $email);
        if ($updated) {
            return ['success' => true, 'message' => 'Password updated successfully!'];
        }
        return ['success' => false, 'message' => 'Failed to update password in database.'];
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
//get urgent complaints (high risk, limit 3)
$complains = $adminDB->getUrgentComplaints(3);
//popular universities
$popularUni = $adminDB->getPopularUni();
//active student projects
$totalAcPro = $adminDB->getActiveProjectCount();

?>