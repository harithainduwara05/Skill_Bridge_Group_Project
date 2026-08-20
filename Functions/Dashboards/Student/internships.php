<?php

include "../../../Config/db.php";
include "../../../Session/Session.php";

require_role("student");
require_once __DIR__ . "/check_student_access.php";

$user = current_user();

if(!$user){
    die("Session expired");
}

$email = $user['Email'] ?? $user['email'] ?? null;

// Check student year
if(!validateStudentYear($email)){
    header("Location: dashboard.php?error=invalid_batch");
    exit();
}

// Check internship permission
if(!canApplyInternship($email)){

    echo "
    <script>
    alert('Internships are available only for 3rd and 4th year students.');
    window.location.href='dashboard.php';
    </script>";
    exit();
}

include "../../../Includes/student_sidebar.php";
include "../../../Includes/dash_header.php";

?>

<main class="content" style="padding: 25px 35px; max-width: 1300px; margin: 0 auto; width: 100%; box-sizing: border-box;">
    <h1>Internships</h1>
</main>

<footer class="footer">
    <div>&copy; 2026 SkillBridge. All rights reserved.</div>
    <div class="footer-links">
        <a href="#">Help Center</a>
        <a href="#">Privacy Policy</a>
        <a href="#">Terms of Service</a>
    </div>
</footer>

<?php include "../../../Includes/dash_footer.php"; ?>