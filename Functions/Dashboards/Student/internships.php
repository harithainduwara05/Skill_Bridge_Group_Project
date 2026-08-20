<?php

include "../../../Config/db.php";
include "../../../Session/Session.php";

require_role("student");

require_once __DIR__ . "/check_student_access.php";


$user = current_user();


if(!$user){
    die("Session expired");
}


$email = $user['Email'];


// Check student year

if(!validateStudentYear($email)){

    header(
        "Location: dashboard.php?error=invalid_batch"
    );

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



$pageTitle = "Internships | SkillBridge";
$extra_css = '<link rel="stylesheet" href="../../../Assets/CSS/Student/dashboard.css">';

include "../../../Includes/student_sidebar.php";
include "../../../Includes/dash_header.php";
?>

<main class="content" style="padding: 25px 35px; max-width: 1300px; margin: 0 auto; width: 100%; box-sizing: border-box;">
    <h1>Internships</h1>
</main>

<?php
include "../../../Includes/dash_footer.php";
?>