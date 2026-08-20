<?php
include "../../../Config/db.php";
include "../../../Session/Session.php";

require_role("student");

$user = current_user();

$pageTitle = "Notifications | SkillBridge";
$extra_css = '<link rel="stylesheet" href="../../../Assets/CSS/Student/dashboard.css">';

include "../../../Includes/student_sidebar.php";
include "../../../Includes/dash_header.php";
?>

<main class="content" style="padding: 25px 35px; max-width: 1300px; margin: 0 auto; width: 100%; box-sizing: border-box;">
    <h1>Notifications</h1>
</main>

<?php include "../../../Includes/dash_footer.php"; ?>