<?php
include "../../../Config/db.php";
include "../../../Session/Session.php";

require_role("student");

$user = current_user();

$pageTitle = "My Projects | SkillBridge";
$extra_css = '<link rel="stylesheet" href="../../../Assets/CSS/Student/dashboard.css">';

include "../../../Includes/student_sidebar.php";
include "../../../Includes/dash_header.php";
?>

<main class="content" style="padding: 25px 35px; max-width: 1300px; margin: 0 auto; width: 100%; box-sizing: border-box;">
    <h1>My Projects</h1>
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