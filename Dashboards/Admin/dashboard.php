<?php
include "../../../Config/db.php";
include "../../../Session/session.php";

is_logged_in();
$user=current_user();

include "../../../Includes/admin_sidebar.php";
include "../../../Includes/dash_header.php";
?>

<body>
<main class="content">

    <div class="dashboard-header">


        <div>


            <h1>
                Hi <?php echo $user['name']; ?>, Dashboard Overview
            </h1>

            <p>
                Manage users, track platform growth, and oversee ongoing collaborations.
            </p>


        </div>


    </div>



</main>

<?php include "../../../Includes/dash_footer.php"; ?>
