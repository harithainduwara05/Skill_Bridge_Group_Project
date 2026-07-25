<?php
require_once __DIR__ . '/../Session/Sessionn.php';
require_once __DIR__ . '/../Config/db.php';
require_once __DIR__ . '/../Config/env_loader.php';
require_once __DIR__ . '/../Includes/simple_smtp.php';

$flash = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $role = $_POST['role'];
    if ($role === 'student') {
        $name = $_POST['name'];
        $email = $_POST['email'];
        $university = $_POST['university'];
        $degree = $_POST['degree'];
        $academicYear = $_POST['academicYear'];
        $password = $_POST['password'];

        $hashPassword = sha1($password);

        try {
            if (!empty(trim($name)) && !empty(trim($email)) && !empty(trim($university)) && !empty(trim($degree)) && !empty(trim($academicYear)) && !empty(trim($password))) {

                // Check if email already exists
                $checkSql = "SELECT Email FROM User WHERE Email = ?";
                $checkStmt = $conn->prepare($checkSql);
                $checkStmt->bind_param("s", $email);
                $checkStmt->execute();
                $checkStmt->store_result();

                if ($checkStmt->num_rows > 0) {
                    $flash = ['type' => 'error', 'message' => 'This email already exists!'];
                } else {
                    //check this is valid Email
                    $domain = substr($email, strpos($email, '@') + 1);
                    $stmt = $conn->prepare("SELECT *from UniversityEmails  where emailEx=?");
                    $stmt->bind_param("s", $domain);
                    $stmt->execute();
                    $stmt->store_result();
                    if ($stmt->num_rows > 0) {
                        // Generate random verification code
                        $verificationCode = rand(100000, 999999);
                        $status = 'De-Active';

                        // Insert new user
                        $sql = "INSERT INTO User (Email,password,role,status, verification_code) VALUES (?,?,?,?,?)";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("sssss", $email, $hashPassword, $role, $status, $verificationCode);
                        $_SESSION['userData'] = [
                            'name' => $name,
                            'university' => $university,
                            'degree' => $degree,
                            'academicYear' => $academicYear,
                            'role' => $role
                        ];

                        if ($stmt->execute()) {
                            // Send Email

                            try {
                                send_verification_email($email, $verificationCode);
                                $_SESSION['verify_email'] = $email;
                                header("Location: verify-email.php");
                                exit;
                            } catch (Exception $mailEx) {
                                $flash = ['type' => 'error', 'message' => 'User registered but failed to send email: ' . $mailEx->getMessage()];
                            }
                        } else {
                            $flash = ['type' => 'error', 'message' => 'Registration failed. Please try again.'];
                        }
                    } else {
                        $flash = ['type' => 'error', 'message' => 'This email is not valid for this platfome. Plz contact admin'];
                    }
                }
            } else {
                $flash = ['type' => 'error', 'message' => 'Please fill in all the required fields.'];
            }
        } catch (Exception $e) {
            $flash = ['type' => 'error', 'message' => 'Error :' . $e->getMessage()];
        }

    } else if ($role === 'organization') {
        $name = $_POST['name'];
        $email = $_POST['email'];
        $password = $_POST['password'];
    } else if ($role === 'Company') {
        $name = $_POST['name'];
        $email = $_POST['email'];
        $password = $_POST['password'];
    }
}