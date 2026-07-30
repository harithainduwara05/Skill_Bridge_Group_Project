<?php

include "../../../Config/db.php";
include "../../../Session/session.php";

is_logged_in();
$user=current_user();

include "../../../Includes/org_sidebar.php";
include "../../../Includes/dash_header.php";

?>

<main class="content">
    <div class="dashboard-header">


        <div>


            <h1>
                Organization Dashboard Overview
            </h1>

            <p>
                Welcome back <?php echo $user['name']; ?>, here's what's happening with your projects today.
            </p>


        </div>


    </div>

</main>
<?php include "../../../Includes/dash_footer.php"; ?>
