<?php

include "../../../Config/db.php";
include "../../../Session/Session.php";
require_once "../../../Backend/CompanyBackend.php";

require_role('company');

$user = current_user();

$companyEmail =
    $user['email']
    ?? $user['Email']
    ?? '';

$companyManager = new CompanyManager($conn);


/* =========================
   GET COMPANY
========================= */

$company = $companyManager->getCompany($companyEmail);

if (!$company) {
    die('Company profile not found.');
}

$companyName = $company['Name'];

$error = '';
$success = '';


/* =========================
   CREATE INTERNSHIP
========================= */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $title = trim($_POST['title'] ?? '');
    $industry = trim($_POST['industry'] ?? '');
    $techTags = trim($_POST['tech_tags'] ?? '');
    $duration = trim($_POST['duration'] ?? '');
    $deadline = trim($_POST['deadline'] ?? '');


    if (
        $title === ''
        ||
        $industry === ''
        ||
        $duration === ''
        ||
        $deadline === ''
    ) {

        $error = 'Please complete all required fields.';

    } else {

        $created = $companyManager->createInternship(
            $title,
            $companyName,
            $industry,
            $techTags,
            $duration,
            $deadline
        );


        if ($created) {

            header("Location: internships.php");
            exit;

        } else {

            $error = 'Unable to create internship. Please try again.';

        }

    }

}


/* =========================
   SIDEBAR COUNT
========================= */

$companyApplicationCount = 0;


/* =========================
   PAGE CSS
========================= */

$extra_css = '
<link
    rel="stylesheet"
    href="../../../Assets/CSS/Company/internships.css"
>
';


/* =========================
   SHARED LAYOUT
========================= */

include "../../../Includes/company_sidebar.php";

include "../../../Includes/dash_header.php";

?>


<main class="content company-internship-page">


    <!-- =========================
         PAGE HEADER
    ========================== -->

    <section class="internship-page-header">


        <div>


            <p class="page-label">
                RECRUITMENT
            </p>


            <h1>
                Add Internship Opportunity
            </h1>


            <p class="page-description">

                Create a new internship opportunity
                and publish it for students.

            </p>


        </div>


        <a
            href="internships.php"
            class="secondary-btn"
        >
            Back to Internships
        </a>


    </section>



    <!-- =========================
         FORM CARD
    ========================== -->

    <section class="internship-form-card">


        <?php if ($error !== ''): ?>


            <div class="form-error">

                <?= htmlspecialchars($error) ?>

            </div>


        <?php endif; ?>



        <form
            method="POST"
            id="internshipForm"
            autocomplete="off"
        >


            <div class="form-grid">


                <!-- TITLE -->

                <div class="form-group full">


                    <label for="title">
                        Internship Title
                    </label>


                    <input
                        id="title"
                        type="text"
                        name="title"
                        maxlength="255"
                        value="<?= htmlspecialchars($_POST['title'] ?? '') ?>"
                        placeholder="e.g. Software Engineering Intern"
                        required
                    >


                </div>



                <!-- INDUSTRY -->

                <div class="form-group">


                    <label for="industry">
                        Industry
                    </label>


                    <input
                        id="industry"
                        type="text"
                        name="industry"
                        maxlength="255"
                        value="<?= htmlspecialchars($_POST['industry'] ?? '') ?>"
                        placeholder="e.g. Software Development"
                        required
                    >


                </div>



                <!-- DURATION -->

                <div class="form-group">


                    <label for="duration">
                        Duration
                    </label>


                    <input
                        id="duration"
                        type="text"
                        name="duration"
                        maxlength="50"
                        value="<?= htmlspecialchars($_POST['duration'] ?? '') ?>"
                        placeholder="e.g. 6 Months"
                        required
                    >


                </div>



                <!-- SKILLS -->

                <div class="form-group full">


                    <label for="tech_tags">
                        Skills / Technologies
                    </label>


                    <input
                        id="tech_tags"
                        type="text"
                        name="tech_tags"
                        maxlength="255"
                        value="<?= htmlspecialchars($_POST['tech_tags'] ?? '') ?>"
                        placeholder="e.g. PHP, MySQL, JavaScript"
                    >


                </div>



                <!-- DEADLINE -->

                <div class="form-group full">


                    <label for="deadline">
                        Application Deadline
                    </label>


                    <input
                        id="deadline"
                        type="text"
                        name="deadline"
                        maxlength="100"
                        value="<?= htmlspecialchars($_POST['deadline'] ?? '') ?>"
                        placeholder="e.g. Nov 15, 2026"
                        required
                    >


                </div>


            </div>



            <!-- =========================
                 FORM BUTTONS
            ========================== -->

            <div class="form-actions">


                <a
                    href="internships.php"
                    class="secondary-btn"
                >
                    Cancel
                </a>


                <button
                    type="submit"
                    class="primary-btn"
                >

                    <span class="material-symbols-outlined">
                        publish
                    </span>

                    Publish Internship

                </button>


            </div>


        </form>


    </section>


</main>



<!-- =========================
     INTERNSHIP JS
========================== -->

<script src="../../../Assets/JS/Company/internships.js"></script>


<?php

include "../../../Includes/dash_footer.php";

?>