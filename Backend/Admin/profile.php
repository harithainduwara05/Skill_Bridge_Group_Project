<?php
/**
 * SkillBridge - Admin Profile Backend Controller
 * Handles profile updates, photo uploads/removals, password changes, and display data preparation
 */

require_once __DIR__ . '/../../Config/db.php';
require_once __DIR__ . '/AdminBackend.php';

if (!isset($adminDB) || !($adminDB instanceof AdminDB)) {
    $adminDB = new AdminDB($conn);
}

$loggedInUser = current_user();
$email = strtolower($loggedInUser['Email'] ?? $loggedInUser['email'] ?? '');
$flash = null;

// HANDLE POST ACTIONS
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // 1. Update Profile Details & Photo
    if ($action === 'update_profile') {
        $name = trim($_POST['name'] ?? '');
        $contactNumber = trim($_POST['contact_number'] ?? '');

        if (empty($name)) {
            $flash = ['type' => 'error', 'title' => 'Validation Error', 'message' => 'Full Name cannot be empty.'];
        } else {
            $uploadedFileName = null;

            // Handle Profile Image Upload
            if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
                $fileTmp  = $_FILES['profile_image']['tmp_name'];
                $fileName = $_FILES['profile_image']['name'];
                $fileSize = $_FILES['profile_image']['size'];
                $fileExt  = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

                $allowedExts = ['jpg', 'jpeg', 'png', 'webp'];
                $maxSize = 3 * 1024 * 1024; // 3MB

                if (!in_array($fileExt, $allowedExts)) {
                    $flash = ['type' => 'error', 'title' => 'Invalid File', 'message' => 'Only JPG, JPEG, PNG, and WEBP image files are allowed.'];
                } elseif ($fileSize > $maxSize) {
                    $flash = ['type' => 'error', 'title' => 'File Too Large', 'message' => 'Image size must not exceed 3MB.'];
                } else {
                    $targetDir = __DIR__ . '/../../Assets/Images/Admin/';
                    if (!is_dir($targetDir)) {
                        mkdir($targetDir, 0777, true);
                    }

                    // Delete old photo if exists
                    $currentProfile = $adminDB->getAdminProfile($email);
                    if (!empty($currentProfile['profile_image'])) {
                        $oldPath = $targetDir . $currentProfile['profile_image'];
                        if (file_exists($oldPath)) {
                            @unlink($oldPath);
                        }
                    }

                    $newFileName = 'admin_' . time() . '_' . uniqid() . '.' . $fileExt;
                    $destPath = $targetDir . $newFileName;

                    if (move_uploaded_file($fileTmp, $destPath)) {
                        $uploadedFileName = $newFileName;
                    } else {
                        $flash = ['type' => 'error', 'title' => 'Upload Failed', 'message' => 'Failed to save uploaded image.'];
                    }
                }
            }

            if (!$flash) {
                $updated = $adminDB->updateAdminProfile($email, $name, $contactNumber, $uploadedFileName);
                if ($updated) {
                    $_SESSION['user']['username'] = $name;
                    $flash = ['type' => 'success', 'title' => 'Profile Updated', 'message' => 'Your profile information has been saved successfully.'];
                } else {
                    $flash = ['type' => 'error', 'title' => 'Update Failed', 'message' => 'Could not update profile details in database.'];
                }
            }
        }
    }

    // 2. Remove Profile Photo
    elseif ($action === 'remove_photo') {
        $removed = $adminDB->removeAdminProfileImage($email);
        if ($removed) {
            $flash = ['type' => 'success', 'title' => 'Photo Removed', 'message' => 'Your profile photo has been removed.'];
        } else {
            $flash = ['type' => 'error', 'title' => 'Action Failed', 'message' => 'Could not remove profile photo.'];
        }
    }

    // 3. Change Password
    elseif ($action === 'change_password') {
        $currPass = $_POST['current_password'] ?? '';
        $newPass  = $_POST['new_password'] ?? '';
        $confPass = $_POST['confirm_password'] ?? '';

        if (empty($currPass) || empty($newPass) || empty($confPass)) {
            $flash = ['type' => 'error', 'title' => 'Missing Fields', 'message' => 'Please fill in all password fields.'];
        } elseif (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/', $newPass)) {
            $flash = ['type' => 'error', 'title' => 'Weak Password', 'message' => 'New password must be at least 8 characters long and contain uppercase, lowercase letters, and numbers.'];
        } elseif ($newPass !== $confPass) {
            $flash = ['type' => 'error', 'title' => 'Password Mismatch', 'message' => 'New password and confirmation do not match.'];
        } else {
            $res = $adminDB->changeAdminPassword($email, $currPass, $newPass);
            if ($res['success']) {
                $flash = ['type' => 'success', 'title' => 'Security Updated', 'message' => $res['message']];
            } else {
                $flash = ['type' => 'error', 'title' => 'Password Error', 'message' => $res['message']];
            }
        }
    }
}

// Fetch Admin Profile
$profile = $adminDB->getAdminProfile($email);
$adminName = $profile['name'] ?? ($loggedInUser['username'] ?? 'Admin');
$adminPhoto = $profile['profile_image'] ?? null;
$adminContact = $profile['contact_number'] ?? '';
$adminRole = ucfirst($profile['role'] ?? 'admin');
$adminCreated = (!empty($profile['created_at'])) ? date('M d, Y', strtotime($profile['created_at'])) : 'N/A';
$initial = !empty(trim($adminName)) ? strtoupper(mb_substr(trim($adminName), 0, 1)) : 'A';

$photoUrl = null;
if (!empty($adminPhoto) && file_exists(__DIR__ . '/../../Assets/Images/Admin/' . $adminPhoto)) {
    $photoUrl = ($GLOBALS['BASE_URL'] ?? '') . '/Assets/Images/Admin/' . htmlspecialchars($adminPhoto);
}
