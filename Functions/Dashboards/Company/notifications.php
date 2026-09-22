<?php
require_once __DIR__ . '/../../../Session/Session.php';
require_role('company');

$extra_css = '<link rel="stylesheet" href="../../../Assets/CSS/Company/dashboard.css">';
include __DIR__ . '/../../../Includes/company_sidebar.php';
include __DIR__ . '/../../../Includes/dash_header.php';
?>

<main class="content">
    <div class="company-dashboard-container">
        <div class="company-dashboard-header">
            <div class="company-welcome-text">
                <h1>Notifications</h1>
            </div>
        </div>
        <div class="company-card" style="min-height: 220px;"></div>
    </div>
</main>

<?php include __DIR__ . '/../../../Includes/dash_footer.php'; ?>

