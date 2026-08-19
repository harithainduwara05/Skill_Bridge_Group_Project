<?php
// Include session and database configuration
require_once '../../../Session/Session.php';
require_once '../../../Config/db.php';

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Get form data
    $company_name = isset($_POST['company_name']) ? trim($_POST['company_name']) : '';
    $business_email = isset($_POST['business_email']) ? trim($_POST['business_email']) : '';
    $industry_sector = isset($_POST['industry_sector']) ? trim($_POST['industry_sector']) : '';
    $contact_person = isset($_POST['contact_person']) ? trim($_POST['contact_person']) : '';
    $contact_number = isset($_POST['contact_number']) ? trim($_POST['contact_number']) : '';
    $website = isset($_POST['website']) ? trim($_POST['website']) : '';
    $location = isset($_POST['location']) ? trim($_POST['location']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $confirmPassword = isset($_POST['confirmPassword']) ? $_POST['confirmPassword'] : '';
    $terms = isset($_POST['terms']) ? true : false;
    
    // Validation
    $errors = [];
    
    if (empty($company_name)) {
        $errors[] = "Company name is required";
    }
    
    if (empty($business_email) || !filter_var($business_email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Valid business email is required";
    }
    
    if (empty($industry_sector)) {
        $errors[] = "Industry sector is required";
    }
    
    if (empty($contact_person)) {
        $errors[] = "Contact person name is required";
    }
    
    if (empty($contact_number)) {
        $errors[] = "Contact number is required";
    }
    
    if (empty($location)) {
        $errors[] = "Location is required";
    }
    
    if (empty($password) || strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters";
    }
    
    if ($password !== $confirmPassword) {
        $errors[] = "Passwords do not match";
    }
    
    if (!$terms) {
        $errors[] = "You must agree to the terms and conditions";
    }
    
    // If validation passes, proceed with registration
    if (empty($errors)) {
        try {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Prepare insert statement
            $stmt = $conn->prepare("
                INSERT INTO companies (company_name, business_email, industry_sector, contact_person, contact_number, website, location, password, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $conn->error);
            }
            
            $stmt->bind_param("ssssssss", $company_name, $business_email, $industry_sector, $contact_person, $contact_number, $website, $location, $hashed_password);
            
            if ($stmt->execute()) {
                // Success - redirect to login or dashboard
                $_SESSION['success_message'] = "Company account created successfully! Please log in.";
                header("Location: ../../login.php");
                exit();
            } else {
                throw new Exception("Registration failed: " . $stmt->error);
            }
            
            $stmt->close();
        } catch (Exception $e) {
            $errors[] = $e->getMessage();
        }
    }
    
    // If there are errors, redirect back to registration form with error messages
    if (!empty($errors)) {
        $_SESSION['registration_errors'] = $errors;
        $_SESSION['form_data'] = $_POST;
        header("Location: company_register.php");
        exit();
    }
    
} else {
    // If not POST request, redirect to registration form
    header("Location: company_register.php");
    exit();
}
?>
