<!--Initialize Admin Environment: Auth Guard, 
Backend Controller & Sidebar-->
<?php
$backendPage = "User_Management.php";
require_once __DIR__ . "/Components/Common/AdminInit.php";
?>
<!--End Here -->

<link rel="stylesheet" href="../../Assets/CSS/Admin/usermanagement.css">
<?php
include "../../Includes/dash_header.php";
?>

<main class="content">

    <!-- Flash Notification Component -->
    <?php include __DIR__ . "/Components/Common/FlashToast.php"; ?>
    <!-- EndHere -->

    <!-- Page Header -->
    <?php
        $HeaderDetails = [
            "title"      => "User Management",
            "subtitle"   => "Oversee, manage, and configure all user accounts across students, universities, partnering companies, and system administrators.",
            "breadcrumb" => "User Management",
            "btnText"     => "Add New User",
            "btnId"       => "btnAddUser",
            "btnIcon"     => "person_add",
            "btnClass"    => "btn-add-user",
            "headerClass" => "user-page-header"
        ];
        include __DIR__ . "/Components/Common/PageHeader.php";
    ?>
    <!-- EndHere -->

    <!-- UserManagement  Cards -->
    <div class="user-stats-grid">
        <?php
            $CardDetails = [
                "cardIcons"  => ["group", "how_to_reg", "school", "domain"],
                "iconColors" => ["navy", "green", "blue", "purple"],
                "cardLabels" => ["Total Users", "Active Users", "Students", "Companies & Orgs"],
                "cardValues" => [
                    number_format($stats['total']),
                    number_format($stats['active']),
                    number_format($stats['students']),
                    number_format($stats['companies'] + $stats['organizations'])
                ]
            ];
            include __DIR__ . "/Components/Common/Card.php";
        ?>
    </div>
    <!-- EndHere -->

    <!-- Main Content Card -->
    <div class="full-width-section" style="padding: 0 28px 40px;">
        <div class="card" style="border: 1px solid #e5e7eb; border-radius: 14px; background: #fff; overflow: hidden;">
            
            <!-- Filters Toolbar -->
            <?php require_once __DIR__ . '/Components/Users/UserFilterBar.php'; ?>
            <!-- EndHere -->

            <!-- Users Table -->
            <?php require_once __DIR__ . '/Components/Users/UserTable.php'; ?>
            <!-- EndHere -->

            <!-- Reusable Pagination Component -->
            <?php 
                $entityLabel = 'users';
                $paginationInfoId = 'userPaginationInfo';
                $paginationControlsId = 'userPaginationControls';
                $alwaysShow = true;
                $perPage = 5;
                include __DIR__ . "/Components/Common/Pagination.php";
            ?>
            <!-- EndHere -->

        </div>
    </div>

</main>

<!-- MODAL: ADD NEW USER -->
<?php include_once __DIR__ . '/Components/Users/UserModels.php'; ?>
<!-- EndHere -->

<script>
    window.dbUniversities = <?= json_encode($dbUniversities, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    window.activeInstitutions = <?= json_encode($activeInstitutions, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
</script>
<script src="../../Assets/JS/Admin/usermanagement.js"></script>

<?php include "../../Includes/dash_footer.php"; ?>
