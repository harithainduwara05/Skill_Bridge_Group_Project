<?php
require_once __DIR__ . '/loader.php';
require_once __DIR__ . '/sendUserEmail.php';

$userDB = new UserManagement($conn);
$uniDB  = new UniversityManagement($conn);
$adminDB = $userDB;

// Download Sample CSV for Bulk Student Import (Must run before any HTML output)
if (isset($_GET['action']) && $_GET['action'] === 'download_sample_csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="sample_students_roster.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['Name', 'Email', 'Degree', 'Academic_Year']);
    fputcsv($out, ['Kavindu Perera', 'kavindu.p@itfac.mrt.ac.lk', 'Information Technology', '2024']);
    fputcsv($out, ['Dilshan Fernando', 'dilshan.f@itfac.mrt.ac.lk', 'Information Systems', '2024']);
    fputcsv($out, ['Rashmi Senanayake', 'rashmi.s@itfac.mrt.ac.lk', 'Cyber Security', '2023']);
    fclose($out);
    exit();
}

$loggedInUser = current_user();
$currentAdminEmail = strtolower($loggedInUser['Email'] ?? $loggedInUser['email'] ?? '');
$flash = null;

// Fetch Universities and Faculties from database (universityemails)
$dbUniversities = [];
$activeInstitutions = []; // University => [ Faculty => domain, ... ]
$uniRows = $uniDB->getUniversityFacultiesList();

foreach ($uniRows as $row) {
    $uName = trim($row['University'] ?? '');
    $fName = trim($row['faculty'] ?? '');
    $domain = strtolower(trim($row['emailEx'] ?? ''));
    $status = trim($row['Status'] ?? '');
    if (!empty($uName)) {
        if (!isset($dbUniversities[$uName])) {
            $dbUniversities[$uName] = [];
        }
        if (!empty($fName) && !in_array($fName, $dbUniversities[$uName])) {
            $dbUniversities[$uName][] = $fName;
        }
        // Only Active status institutions are permitted for bulk onboarding (Hold/Inactive are strictly excluded)
        if ($status === 'Active' && !empty($fName) && !empty($domain)) {
            if (!isset($activeInstitutions[$uName])) {
                $activeInstitutions[$uName] = [];
            }
            $activeInstitutions[$uName][$fName] = ltrim($domain, '@');
        }
    }
}

// HANDLE USER ACTIONS (Add, Edit, Delete, Toggle Status, Bulk Upload)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // 1. ADD USER
    if ($action === 'add') {
        $name          = trim($_POST['name'] ?? '');
        $email         = trim($_POST['email'] ?? '');
        $role          = trim($_POST['role'] ?? '');
        $status        = trim($_POST['status'] ?? 'Active');
        $contact       = trim($_POST['contact_number'] ?? '');
        $degree        = trim($_POST['degree'] ?? '');
        $year          = trim($_POST['academic_year'] ?? '');

        // Resolve Organization / University based on role
        if ($role === 'student') {
            $uniName = trim($_POST['university_name'] ?? '');
            $facName = trim($_POST['faculty_name'] ?? '');
            $orgName = (!empty($uniName) && !empty($facName)) ? ($uniName . ' - ' . $facName) : $uniName;
        } else {
            $orgName = trim($_POST['organization_name'] ?? '');
            // If organization or company selected, fallback name appropriately
            if (($role === 'company' || $role === 'organization') && empty($orgName)) {
                $orgName = $name;
            }
        }

        if (empty($name) || empty($email) || empty($role)) {
            $flash = ['type' => 'error', 'message' => 'Name, Email, and Role are required fields.'];
        } elseif ($role === 'student' && empty($orgName)) {
            $flash = ['type' => 'error', 'message' => 'Please select or enter a University for the student.'];
        } elseif ($userDB->userExists($email)) {
            $flash = ['type' => 'error', 'message' => 'A user with this email address already exists!'];
        } else {
            try {
                // Generate a secure unactivated placeholder hash so no one can log in until activated
                $unactivatedPassword = bin2hex(random_bytes(16));
                $ok = $userDB->addUser($email, $unactivatedPassword, $role, $name, $status, $orgName, $contact, $degree, $year);
                if ($ok) {
                    $emailMsg = '';
                    try {
                        send_user_activation_email($email, $name);
                        $emailMsg = ' An activation email has been sent to ' . htmlspecialchars($email) . ' with password setup instructions.';
                    } catch (Exception $mailEx) {
                        $emailMsg = ' (User saved! Note: Email dispatch skipped: ' . htmlspecialchars($mailEx->getMessage()) . '. User can activate via Forgot Password).';
                    }
                    $flash = ['type' => 'success', 'message' => 'User created successfully!' . $emailMsg];
                } else {
                    $flash = ['type' => 'error', 'message' => 'Failed to create user. Please try again.'];
                }
            } catch (Exception $e) {
                $flash = ['type' => 'error', 'message' => 'Error: ' . $e->getMessage()];
            }
        }
    }

    // 1.5 BULK IMPORT STUDENTS (Institutional Bulk Upload with Selected University & Faculty)
    elseif ($action === 'bulk_upload') {
        $selectedUni = trim($_POST['bulk_university'] ?? '');
        $selectedFac = trim($_POST['bulk_faculty'] ?? '');

        if (empty($selectedUni) || empty($selectedFac)) {
            $flash = ['type' => 'error', 'message' => 'Please select both University and Faculty before uploading the CSV roster.'];
        } elseif (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
            $flash = ['type' => 'error', 'message' => 'Please select a valid CSV file to upload.'];
        } else {
            $file = $_FILES['csv_file']['tmp_name'];
            $fileName = $_FILES['csv_file']['name'];
            $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

            if ($ext !== 'csv') {
                $flash = ['type' => 'error', 'message' => 'Only CSV files (.csv) are allowed.'];
            } else {
                $targetDomain = $uniDB->getAuthorizedDomain($selectedUni, $selectedFac);

                if (!$targetDomain) {
                    $flash = ['type' => 'error', 'message' => "The selected faculty '{$selectedFac}' under '{$selectedUni}' is not currently active in the system."];
                } else {
                    $handle = fopen($file, 'r');
                    if ($handle === false) {
                        $flash = ['type' => 'error', 'message' => 'Unable to read the uploaded CSV file.'];
                    } else {
                        // Skip UTF-8 BOM if present
                        $bom = fread($handle, 3);
                        if ($bom !== "\xEF\xBB\xBF") {
                            rewind($handle);
                        }

                        // Read header row
                        $headers = fgetcsv($handle);
                        if ($headers === false) {
                            $flash = ['type' => 'error', 'message' => 'The uploaded CSV file is empty.'];
                            fclose($handle);
                        } else {
                            // Normalize header names to lowercase trimmed
                            $normHeaders = array_map(function($h) {
                                return strtolower(trim($h));
                            }, $headers);

                            $nameIdx   = array_search('name', $normHeaders);
                            $emailIdx  = array_search('email', $normHeaders);
                            $degreeIdx = array_search('degree', $normHeaders);
                            $yearIdx   = array_search('academic_year', $normHeaders);
                            if ($yearIdx === false) {
                                $yearIdx = array_search('year', $normHeaders);
                            }

                            if ($nameIdx === false || $emailIdx === false) {
                                $flash = ['type' => 'error', 'message' => 'Invalid CSV format: Required columns "Name" and "Email" were not found in the header row.'];
                                fclose($handle);
                            } else {
                                $importedCount = 0;
                                $skippedCount = 0;
                                $domainMismatchCount = 0;
                                $invalidCount = 0;
                                $finalOrg = $selectedUni . ' - ' . $selectedFac;

                                while (($row = fgetcsv($handle)) !== false) {
                                    // Skip completely blank rows
                                    if (empty(array_filter($row))) {
                                        continue;
                                    }

                                    $stuName  = trim($row[$nameIdx] ?? '');
                                    $stuEmail = trim($row[$emailIdx] ?? '');
                                    $stuDeg   = ($degreeIdx !== false && isset($row[$degreeIdx])) ? trim($row[$degreeIdx]) : 'General';
                                    $stuYear  = ($yearIdx !== false && isset($row[$yearIdx])) ? trim($row[$yearIdx]) : date('Y');

                                    if (empty($stuDeg))  $stuDeg  = 'General';
                                    if (empty($stuYear)) $stuYear = date('Y');

                                    // Basic validation
                                    if (empty($stuName) || empty($stuEmail) || !filter_var($stuEmail, FILTER_VALIDATE_EMAIL)) {
                                        $invalidCount++;
                                        continue;
                                    }

                                    // Extract domain and verify against selected institution
                                    $parts = explode('@', $stuEmail);
                                    $emailDomain = strtolower(trim(end($parts)));

                                    // Strict domain match after the @ symbol
                                    if ($emailDomain !== $targetDomain) {
                                        $domainMismatchCount++;
                                        continue;
                                    }

                                    if ($userDB->userExists($stuEmail)) {
                                        $skippedCount++;
                                        continue;
                                    }

                                    // Unactivated random password hash for zero-knowledge onboarding
                                    $unactivatedPassword = bin2hex(random_bytes(16));

                                    // Direct database insert (no bulk email dispatch)
                                    $ok = $userDB->addUser($stuEmail, $unactivatedPassword, 'student', $stuName, 'Active', $finalOrg, '', $stuDeg, $stuYear);
                                    if ($ok) {
                                        $importedCount++;
                                    }
                                }
                                fclose($handle);

                                $msg = "Bulk import completed for <strong>" . htmlspecialchars($finalOrg) . "</strong>: <strong>{$importedCount}</strong> student(s) imported successfully.";
                                if ($skippedCount > 0) $msg .= " ({$skippedCount} existing account(s) skipped).";
                                if ($domainMismatchCount > 0) $msg .= " <span style='color:#ef4444;'>({$domainMismatchCount} student(s) skipped because email domain did not match @{$targetDomain}).</span>";
                                if ($invalidCount > 0) $msg .= " ({$invalidCount} row(s) had invalid email/data format).";

                                $flash = [
                                    'type' => $importedCount > 0 ? 'success' : 'warning',
                                    'message' => $msg
                                ];
                            }
                        }
                    }
                }
            }
        }
    }

    // 2. EDIT USER
    elseif ($action === 'edit') {
        $email   = trim($_POST['email'] ?? '');
        $name    = trim($_POST['name'] ?? '');
        $role    = trim($_POST['role'] ?? 'student');
        $status  = trim($_POST['status'] ?? 'Active');
        $orgName = trim($_POST['organization_name'] ?? '');
        $contact = trim($_POST['contact_number'] ?? '');

        if (empty($email) || empty($name)) {
            $flash = ['type' => 'error', 'message' => 'Name and Email are required.'];
        } else {
            // If editing self, preserve admin role and active status
            if (strtolower($email) === $currentAdminEmail) {
                $role = 'admin';
                $status = 'Active';
            }
            try {
                $userDB->updateUser($email, $name, $role, $status, $orgName, $contact);
                $flash = ['type' => 'success', 'message' => 'User updated successfully!'];
            } catch (Exception $e) {
                $flash = ['type' => 'error', 'message' => 'Error: ' . $e->getMessage()];
            }
        }
    }

    // 3. TOGGLE STATUS
    elseif ($action === 'toggle_status') {
        $email     = trim($_POST['email'] ?? '');
        $newStatus = trim($_POST['status'] ?? 'Active');
        if (strtolower($email) === $currentAdminEmail) {
            $flash = ['type' => 'error', 'message' => 'You cannot deactivate your own admin account!'];
        } elseif (!empty($email)) {
            try {
                $userDB->updateUserStatus($email, $newStatus);
                $flash = ['type' => 'success', 'message' => 'User status updated to ' . htmlspecialchars($newStatus) . '!'];
            } catch (Exception $e) {
                $flash = ['type' => 'error', 'message' => 'Error: ' . $e->getMessage()];
            }
        }
    }

    // 4. DELETE USER
    elseif ($action === 'delete') {
        $emailToDelete = trim($_POST['delete_email'] ?? '');
        if (strtolower($emailToDelete) === $currentAdminEmail) {
            $flash = ['type' => 'error', 'message' => 'You cannot delete your own admin account!'];
        } elseif (!empty($emailToDelete)) {
            try {
                $userDB->deleteUser($emailToDelete);
                $flash = ['type' => 'success', 'message' => 'User account and data deleted successfully!'];
            } catch (Exception $e) {
                $flash = ['type' => 'error', 'message' => 'Error: ' . $e->getMessage()];
            }
        }
    }
}

// FILTERS, STATS & PAGINATION
$stats = $userDB->getUserManagementStats();

// Fetch all users for instant client-side searching and filtering
$allUsers = $userDB->getAllUsersDetailed('all', 'all', '');
$totalRecords = count($allUsers);
$usersList = $allUsers;

// Default pagination configuration (5 per page handled on frontend)
$perPage = 5;
$currentPageNum = 1;
$totalPages = ceil($totalRecords / $perPage);
$paginatedUsers = $usersList;
?>
