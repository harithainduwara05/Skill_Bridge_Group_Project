<?php
include "../../../Config/db.php";
include "../../../Session/session.php";

is_logged_in();
$user = current_user();

include "../../../Includes/company_sidebar.php";
include "../../../Includes/dash_header.php";

?>

<main class="content">

    <div class="dashboard-header">


        <div>


            <h1>
                Welcome back, <?php echo $user['username']; ?>!
            </h1>

            <p>
                Here is an overview of your recruitment momentum today.
            </p>


        </div>


    </div>



</main>

<?php include "../../../Includes/dash_footer.php"; ?>