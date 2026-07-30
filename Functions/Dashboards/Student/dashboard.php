<?php
include "../../../Config/db.php";
include "../../../Session/Session.php";

is_logged_in();
$user = current_user();

include "../../../Includes/student_sidebar.php";
include "../../../Includes/dash_header.php";

?>


<main class="content">
    <div class="dashboard-header">


        <div>


            <h1>
                Welcome <?php echo $user['name']; ?>!
            </h1>

            <p>
                Continue building your skills and career journey.
            </p>
        </div>
    </div>
</main>
<?php include "../../../Includes/dash_footer.php"; ?>