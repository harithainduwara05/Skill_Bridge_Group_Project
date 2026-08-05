<?php
require_once __DIR__ . '/../Session/Session.php';
require_once __DIR__ . '/../Config/db.php';
require_once __DIR__ . '/../Config/env_loader.php';
require_once __DIR__ . '/../Includes/simple_smtp.php';

$flash = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $role = $_POST['role'];
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    $hashPassword = sha1($password);
    $today = date("d/m/Y");
    if (strtolower($role) === 'student') {    
        $university = $_POST['university'];
        $degree = $_POST['degree'];
        $academicYear = $_POST['academicYear']; 
        
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
                        $sql = "INSERT INTO User (Email,password,role,status, verification_code,created_at) VALUES (?,?,?,?,?,?)";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("ssssss", $email, $hashPassword, $role, $status, $verificationCode,$today);
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

    } else if (strtolower($role) === 'organization') {
        $orgtype = $_POST['org_type'];
        $contacPName = $_POST['contact_person'];
        $contacNo = $_POST['contact_number'];
        $website = $_POST['website'];
        $location = $_POST['location'];

         try {
            if (!empty(trim($name)) && !empty(trim($email)) && !empty(trim($contacPName)) && !empty(trim($contacNo)) && !empty(trim($website)) && !empty(trim($location)) && !empty(trim($password))) {

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
                    /*$domain = substr($email, strpos($email, '@') + 1);
                    $stmt = $conn->prepare("SELECT *from UniversityEmails  where emailEx=?");
                    $stmt->bind_param("s", $domain);
                    $stmt->execute();
                    $stmt->store_result();
                    if ($stmt->num_rows > 0) {*/
                        // Generate random verification code
                        $verificationCode = rand(100000, 999999);
                        $status = 'De-Active';

                        // Insert new user
                        $sql = "INSERT INTO User (Email,password,role,status, verification_code,created_at) VALUES (?,?,?,?,?,?)";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("ssssss", $email, $hashPassword, $role, $status, $verificationCode,$today);
                        $_SESSION['userData'] = [
                            'name' => $name,
                            'type' => $orgtype,
                            'contactPersonName' => $contacPName,
                            'contactNumber' => $contacNo,
                            'website' => $website,
                            'location' => $location,
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
                   /*} else {
                        $flash = ['type' => 'error', 'message' => 'This email is not valid for this platfome. Plz contact admin'];
                    }*/
                }
            } else {
                $flash = ['type' => 'error', 'message' => 'Please fill in all the required fields.'];
            }
        } catch (Exception $e) {
            $flash = ['type' => 'error', 'message' => 'Error :' . $e->getMessage()];
        }


    } else if (strtolower($role) === 'company') {
        $corg_type = $_POST['org_type'];
        $CcontacPName = $_POST['contact_person'];
        $CcontacNo = $_POST['contact_number'];
        $Cwebsite = $_POST['website'];
        $Clocation = $_POST['location'];

         try {
            if (!empty(trim($name)) && !empty(trim($email)) && !empty(trim($CcontacPName)) && !empty(trim($CcontacNo)) && !empty(trim($Cwebsite)) && !empty(trim($Clocation)) && !empty(trim($password))) {

                // Check if email already exists
                $checkSql = "SELECT Email FROM User WHERE Email = ?";
                $checkStmt = $conn->prepare($checkSql);
                $checkStmt->bind_param("s", $email);
                $checkStmt->execute();
                $checkStmt->store_result();

                if ($checkStmt->num_rows > 0) {
                    $flash = ['type' => 'error', 'message' => 'This email already exists!','role'=> $role];
                } else {
                    //check this is valid Email
                    /*$domain = substr($email, strpos($email, '@') + 1);
                    $stmt = $conn->prepare("SELECT *from UniversityEmails  where emailEx=?");
                    $stmt->bind_param("s", $domain);
                    $stmt->execute();
                    $stmt->store_result();
                    if ($stmt->num_rows > 0) {*/
                        // Generate random verification code
                        $verificationCode = rand(100000, 999999);
                        $status = 'De-Active';

                        // Insert new user
                        $sql = "INSERT INTO User (Email,password,role,status, verification_code,created_at) VALUES (?,?,?,?,?,?)";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("ssssss", $email, $hashPassword, $role, $status, $verificationCode,$today);
                        $_SESSION['userData'] = [
                            'name' => $name,
                            'type' => $corg_type,
                            'contactPersonName' => $CcontacPName,
                            'contactNumber' => $CcontacNo,
                            'website' => $Cwebsite,
                            'location' => $Clocation,
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
                                $flash = ['type' => 'error', 'message' => 'User registered but failed to send email: ' . $mailEx->getMessage(),'role'=> $role];
                            }
                        } else {
                            $flash = ['type' => 'error', 'message' => 'Registration failed. Please try again.','role'=> $role];
                        }
                   /*} else {
                        $flash = ['type' => 'error', 'message' => 'This email is not valid for this platfome. Plz contact admin'];
                    }*/
                }
            } else {
                $flash = ['type' => 'error', 'message' => 'Please fill in all the required fields.','role'=> $role];
            }
        } catch (Exception $e) {
            $flash = ['type' => 'error', 'message' => 'Error :' . $e->getMessage(),'role'=> $role];
        }


    }
}