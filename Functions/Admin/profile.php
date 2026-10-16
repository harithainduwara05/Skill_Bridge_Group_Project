<!--Initialize Admin Environment: Auth Guard, 
Backend Controller & Sidebar-->
<?php
$backendPage = "profile.php";
require_once __DIR__ . "/Components/Common/AdminInit.php";
?>
<!--End Here -->

<link rel="stylesheet" href="../../Assets/CSS/Admin/profile.css">
<link rel="stylesheet" href="../../Assets/CSS/Admin/usermanagement.css">
<?php
include "../../Includes/dash_header.php";
?>

<main class="content">

    <!-- Flash Toast Component -->
    <?php include __DIR__ . "/Components/Common/FlashToast.php"; ?>
    <!--End Here-->

    <div class="admin-profile-container">

        <!-- Page Header -->
        <?php
            $HeaderDetails = [
                "title"      => "Admin Profile & Settings",
                "subtitle"   => "Manage your personal administrator account details, profile picture, and security credentials.",
                "breadcrumb" => "Profile & Settings"
            ];
            include __DIR__ . "/Components/Common/PageHeader.php";
        ?>
        <!--End Here-->

        <div class="profile-grid">
            <!-- Left Column: Profile Card -->
            <?php require_once __DIR__ . '/Components/Profile/ProfileLeftProfileCard.php'; ?>
            <!--End Here-->

            <!--Right Column: Edit Details & Password -->
            <?php require_once __DIR__ . '/Components/Profile/ProfileRightColum.php'; ?>
            <!--End Here-->
        </div>

    </div>

</main>

<!-- Admin Profile Dedicated Script -->
<script src="../../Assets/JS/Admin/Profile.js?v=<?php echo time(); ?>"></script>

<?php include "../../Includes/dash_footer.php"; ?>
