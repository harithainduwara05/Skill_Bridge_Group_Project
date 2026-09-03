<?php
require_once __DIR__ . "/../../../Config/db.php";

class CompanyDB
{
    private $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    private function runQuery($sql, $types = "", ...$params)
    {
        $stmt = $this->conn->prepare($sql);
        if ($stmt) {
            if ($types && !empty($params)) {
                $stmt->bind_param($types, ...$params);
            }
            $stmt->execute();
            return $stmt->get_result();
        }
        return false;
    }

    private function runAction($sql, $types, ...$params)
    {
        $stmt = $this->conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param($types, ...$params);
            return $stmt->execute();
        }
        return false;
    }

    /**
     * Fetch full company profile details joined with user account
     */
    public function getCompanyProfile($email)
    {
        $sql = "
            SELECT 
                c.Email AS email,
                c.Name AS company_name,
                c.companytype AS company_type,
                c.contactPersonName AS contact_person,
                c.contactNumber AS contact_number,
                c.website AS website,
                c.location AS location,
                c.Status AS company_status,
                c.profile_img AS profile_image,
                u.role AS role,
                u.status AS user_status,
                u.created_at AS created_at
            FROM company c
            JOIN user u ON c.Email = u.Email
            WHERE c.Email = ?
            LIMIT 1
        ";
        $res = $this->runQuery($sql, "s", $email);
        if ($res && $res->num_rows > 0) {
            return $res->fetch_assoc();
        }

        // Fallback: check user table if not yet present in company table
        $userQuery = $this->runQuery("SELECT Email AS email, role, status, created_at FROM user WHERE Email = ?", "s", $email);
        if ($userQuery && $userQuery->num_rows > 0) {
            $u = $userQuery->fetch_assoc();
            return [
                'email' => $u['email'],
                'company_name' => 'Company',
                'company_type' => 'General',
                'contact_person' => '',
                'contact_number' => '',
                'website' => '',
                'location' => '',
                'company_status' => 'Active',
                'profile_image' => '',
                'role' => $u['role'] ?? 'company',
                'user_status' => $u['status'] ?? 'Active',
                'created_at' => $u['created_at'] ?? null,
            ];
        }

        return null;
    }

    /**
     * Update Company Profile Information
     */
    public function updateCompanyProfile($email, $companyName, $companyType, $contactPerson, $contactNumber, $website, $location, $profileImg = null)
    {
        $companyName   = trim($companyName);
        $companyType   = trim($companyType);
        $contactPerson = trim($contactPerson);
        $contactNumber = trim($contactNumber);
        $website       = trim($website);
        $location      = trim($location);

        // Check if row already exists in company table
        $chk = $this->runQuery("SELECT Email FROM company WHERE Email = ?", "s", $email);
        if (!$chk || $chk->num_rows === 0) {
            $img = $profileImg !== null ? $profileImg : '';
            $insertSql = "INSERT INTO company (Email, Name, companytype, contactPersonName, contactNumber, website, location, Status, profile_img) VALUES (?, ?, ?, ?, ?, ?, ?, 'Active', ?)";
            return $this->runAction($insertSql, "ssssssss", $email, $companyName, $companyType, $contactPerson, $contactNumber, $website, $location, $img);
        }

        if ($profileImg !== null) {
            $updateSql = "UPDATE company SET Name = ?, companytype = ?, contactPersonName = ?, contactNumber = ?, website = ?, location = ?, profile_img = ? WHERE Email = ?";
            return $this->runAction($updateSql, "ssssssss", $companyName, $companyType, $contactPerson, $contactNumber, $website, $location, $profileImg, $email);
        } else {
            $updateSql = "UPDATE company SET Name = ?, companytype = ?, contactPersonName = ?, contactNumber = ?, website = ?, location = ? WHERE Email = ?";
            return $this->runAction($updateSql, "sssssss", $companyName, $companyType, $contactPerson, $contactNumber, $website, $location, $email);
        }
    }

    /**
     * Update only the company profile image
     */
    public function updateCompanyProfileImage($email, $fileName)
    {
        $current = $this->getCompanyProfile($email);
        if ($current && !empty($current['profile_image'])) {
            $oldPath = __DIR__ . '/../../../Assets/Images/Company/' . $current['profile_image'];
            if (file_exists($oldPath)) {
                @unlink($oldPath);
            }
        }
        $sql = "UPDATE company SET profile_img = ? WHERE Email = ?";
        return $this->runAction($sql, "ss", $fileName, $email);
    }

    /**
     * Remove company profile image
     */
    public function removeCompanyProfileImage($email)
    {
        $current = $this->getCompanyProfile($email);
        if ($current && !empty($current['profile_image'])) {
            $oldPath = __DIR__ . '/../../../Assets/Images/Company/' . $current['profile_image'];
            if (file_exists($oldPath)) {
                @unlink($oldPath);
            }
        }
        $sql = "UPDATE company SET profile_img = '' WHERE Email = ?";
        return $this->runAction($sql, "s", $email);
    }

    /**
     * Change Company Account Password
     */
    public function changeCompanyPassword($email, $currentPassword, $newPassword)
    {
        $currHash = sha1($currentPassword);
        $newHash  = sha1($newPassword);

        // Verify current password
        $chk = $this->runQuery("SELECT Email FROM user WHERE Email = ? AND password = ?", "ss", $email, $currHash);
        if (!$chk || $chk->num_rows === 0) {
            return ['success' => false, 'message' => 'Current password does not match our records.'];
        }

        // Update password
        $updated = $this->runAction("UPDATE user SET password = ? WHERE Email = ?", "ss", $newHash, $email);
        if ($updated) {
            return ['success' => true, 'message' => 'Your password has been changed successfully.'];
        }
        return ['success' => false, 'message' => 'Database error while updating password.'];
    }
}

$companyDB = new CompanyDB($conn);
?>
