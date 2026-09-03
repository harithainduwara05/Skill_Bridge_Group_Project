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


/* =========================
   GET INTERNSHIP ID
========================= */

$internshipId =
    isset($_GET['id'])
        ? (int) $_GET['id']
        : 0;


if ($internshipId <= 0) {
    die('Invalid internship ID.');
}


/* =========================
   GET INTERNSHIP
========================= */

$internship =
    $companyManager->getInternshipById(
        $internshipId,
        $companyName
    );


if (!$internship) {
    die('Internship not found.');
}


$error = '';


/* =========================
   UPDATE INTERNSHIP
========================= */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $title =
        trim($_POST['title'] ?? '');

    $industry =
        trim($_POST['industry'] ?? '');

    $techTags =
        trim($_POST['tech_tags'] ?? '');

    $duration =
        trim($_POST['duration'] ?? '');

    $deadline =
        trim($_POST['deadline'] ?? '');


    if (
        $title === ''
        ||
        $industry === ''
        ||
        $duration === ''
        ||
        $deadline === ''
    ) {

        $error =
            'Please complete all required fields.';

    } else {

        $updated =
            $companyManager->updateInternship(
                $internshipId,
                $companyName,
                $title,
                $industry,
                $techTags,
                $duration,
                $deadline
            );


        if ($updated) {

            header("Location: internships.php");
            exit;

        } else {

            $error =
                'Unable to update internship.';

        }

    }

}


$companyApplicationCount = 0;


/* =========================
   CSS
========================= */

$extra_css = '
<link
    rel="stylesheet"
    href="../../../Assets/CSS/Company/internships.css"
>
';


include "../../../Includes/company_sidebar.php";
include "../../../Includes/dash_header.php";

?>


<main class="content company-internship-page">


    <section class="internship-page-header">


        <div>

            <p class="page-label">
                RECRUITMENT
            </p>

            <h1>
                Edit Internship
            </h1>

            <p class="page-description">
                Update the internship opportunity details.
            </p>

        </div>


        <a
            href="internships.php"
            class="secondary-btn"
        >
            Back to Internships
        </a>


    </section>



    <section class="internship-form-card">


        <?php if ($error !== ''): ?>

            <div class="form-error">
                <?= htmlspecialchars($error) ?>
            </div>

        <?php endif; ?>


        <form method="POST">


            <div class="form-grid">


                <div class="form-group full">

                    <label for="title">
                        Internship Title
                    </label>

                    <input
                        id="title"
                        type="text"
                        name="title"
                        value="<?= htmlspecialchars($internship['title']) ?>"
                        required
                    >

                </div>



                <div class="form-group">

                    <label for="industry">
                        Industry
                    </label>

                    <input
                        id="industry"
                        type="text"
                        name="industry"
                        value="<?= htmlspecialchars($internship['industry']) ?>"
                        required
                    >

                </div>



                <div class="form-group">

                    <label for="duration">
                        Duration
                    </label>

                    <input
                        id="duration"
                        type="text"
                        name="duration"
                        value="<?= htmlspecialchars($internship['duration']) ?>"
                        required
                    >

                </div>



                <div class="form-group full">

                    <label for="tech_tags">
                        Skills / Technologies
                    </label>

                    <input
                        id="tech_tags"
                        type="text"
                        name="tech_tags"
                        value="<?= htmlspecialchars($internship['tech_tags']) ?>"
                    >

                </div>



                <div class="form-group full">

                    <label for="deadline">
                        Application Deadline
                    </label>

                    <input
                        id="deadline"
                        type="text"
                        name="deadline"
                        value="<?= htmlspecialchars($internship['deadline']) ?>"
                        required
                    >

                </div>


            </div>



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
                    Save Changes
                </button>


            </div>


        </form>


    </section>


</main>


<script src="../../../Assets/JS/Company/internships.js"></script>


<?php
include "../../../Includes/dash_footer.php";
?>