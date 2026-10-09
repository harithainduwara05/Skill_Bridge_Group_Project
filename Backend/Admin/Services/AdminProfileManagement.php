<?php
/**
 * SkillBridge - Admin Profile Service
 * Handles admin identity, photo upload/removal, and security credentials.
 */

require_once __DIR__ . '/AdminDB.php';

class AdminProfileManagement extends AdminDB
{
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
?>
