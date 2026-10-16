<!--Initialize Admin Environment: Auth Guard, 
Backend Controller & Sidebar-->
<?php
$backendPage = "university.php";
require_once __DIR__ . "/Components/Common/AdminInit.php";
?>
<!--End Here -->

<link rel="stylesheet" href="../../Assets/CSS/Admin/university.css">
<?php
include "../../Includes/dash_header.php";
?>

<main class="content">

    <!-- Flash Notification Component -->
    <?php include __DIR__ . "/Components/Common/FlashToast.php"; ?>

    <!-- Page Header -->
    <?php
        $HeaderDetails = [
            "title"      => "University Registry",
            "subtitle"   => "Manage partner institutions and their academic domains to ensure secure student enrollment and verification across the platform.",
            "breadcrumb" => "University Registry",
            "btnText"     => "Add University",
            "btnId"       => "btnAddUniversity",
            "btnIcon"     => "add",
            "btnClass"    => "btn-add-university",
            "headerClass" => "univ-page-header"
        ];
        include __DIR__ . "/Components/Common/PageHeader.php";
    ?>
    <!--End Here-->

    <!-- UniversityManagement Cards -->
    <div class="univ-stats-grid">

        <?php
            $CardDetails = [
                "cardIcons"  => ["school", "check_circle", "schedule", "people"],
                "iconColors" => ["grey", "green", "orange", "blue"],
                "cardLabels" => ["TOTAL INSTITUTIONS", "ACTIVE DOMAINS", "ON HOLD", "TOTAL STUDENTS"],
                "cardValues" => [$totUni, $activeDomains, $holdDomains, $totalStudents]
            ];
            include __DIR__ . "/Components/Common/Card.php";
        ?>

    </div>

    <!-- Institution List Table -->
    <div class="full-width-section">
        <div class="card">
            <!--University Filterbar-->
            <?php require_once __DIR__ . '/Components/Universities/UniversityFilterBar.php'; ?>
            <!--End Here-->

            <!--University Table-->
            <?php require_once __DIR__ . '/Components/Universities/UniversityTable.php'; ?>
            <!--End Here-->
            
            <!-- Reusable Pagination Component -->
            <?php 
                $entityLabel = 'universities';
                include __DIR__ . "/Components/Common/Pagination.php";
            ?>
            <!--End Here-->

        </div>
    </div>
</main>

<!-- Add University Modal  -->
<?php require_once __DIR__ . '/Components/Universities/AddUniversity.php'; ?>
<!--End Here-->

<!-- Edit University Modal -->
<?php require_once __DIR__ . '/Components/Universities/EditUniversity.php'; ?>
<!--End Here-->

<!-- View University Modal -->
<?php require_once __DIR__ . '/Components/Universities/ViewUniversity.php'; ?>
<!--End Here-->

<!-- Delete University Modal -->
<?php require_once __DIR__ . '/Components/Universities/DeleteUniversity.php'; ?>
<!--End Here-->

<?php include "../../Includes/dash_footer.php"; ?>
<script src="../../Assets/JS/Admin/universityData.js"></script>
<script src="../../Assets/JS/Admin/university.js?v=<?= time() ?>"></script>
<script>
// Direct safety guarantee to hide Change Status button for Inactive universities
(function() {
    var origFn = window.openDeleteModal;
    window.openDeleteModal = function(domain, name, stuCount, faculty, location, status) {
        if (typeof origFn === 'function') {
            origFn(domain, name, stuCount, faculty, location, status);
        }
        var s = String(status || '').trim().toLowerCase();
        var isInactive = (s === 'inactive' || s === 'deactive' || s === 'disabled');
        var btn = document.getElementById('editStatusInsteadBtn');
        var rec = document.getElementById('deleteModalRecommendation');
        if (btn && isInactive) {
            btn.style.setProperty('display', 'none', 'important');
        }
        if (rec && isInactive) {
            rec.innerHTML = '<span class="material-symbols-outlined" style="font-size:14px; vertical-align:middle; color:#d97706;">lock</span> <em>Status:</em> This institution is currently <strong>Inactive</strong> and locked. Deleting it is restricted while student accounts remain enrolled.';
        }
    };
})();
</script>