<?php

include "../../../Config/db.php";
include "../../../Session/Session.php";

require_role('organization');
$user = current_user();
$organization_email = $user['email'];

include "../../../Includes/org_sidebar.php";
include "../../../Includes/dash_header.php";
?>

<main class="content">

    <div class="dashboard-header">
        <div>
            <h1>Feedback</h1>
            <p>View feedback shared by students and companies about your projects.</p>
        </div>
    </div>


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