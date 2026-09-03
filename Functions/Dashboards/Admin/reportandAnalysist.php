<?php
include "../../../Config/db.php";
include "../../../Session/session.php";

require_login();
require_role('admin');
$user = current_user();


require_once "AdminBackend.php";



include "../../../Includes/admin_sidebar.php";
include "../../../Includes/dash_header.php";
?>

<main class="content">

    <!-- Dashboard Header -->
    <div class="dashboard-header">
        <div>
            <h1>
                Reports & Analysis
            </h1>
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