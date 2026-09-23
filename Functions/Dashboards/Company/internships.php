<?php

include "../../../Config/db.php";
include "../../../Session/Session.php";
require_once "../../../Backend/CompanyBackend.php";

require_role('company');

$user = current_user();

$companyEmail = isset($user['email'])
    ? $user['email']
    : (isset($user['Email']) ? $user['Email'] : '');

$companyManager = new CompanyManager($conn);


/* =========================
   GET COMPANY
========================= */

$company = $companyManager->getCompany($companyEmail);

if (!$company) {
    die('Company profile not found.');
}

$companyName = $company['Name'];

$createError = '';
$updateError = '';
$updateFormData = [];

/* =========================
   CREATE INTERNSHIP
========================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_internship'])) {
    $title = trim($_POST['title'] ?? '');
    $industry = trim($_POST['industry'] ?? '');
    $techTags = trim($_POST['tech_tags'] ?? '');
    $duration = trim($_POST['duration'] ?? '');
    $deadline = trim($_POST['deadline'] ?? '');

    if ($title === '' || $industry === '' || $duration === '' || $deadline === '') {
        $createError = 'Please complete all required fields.';
    } else {
        $created = $companyManager->createInternship(
            $title, $companyName, $industry, $techTags, $duration, $deadline
        );

        if ($created) {
            header('Location: internships.php?success=added');
            exit;
        }

        $createError = 'Unable to create internship. Please try again.';
    }
}


/* =========================
   UPDATE INTERNSHIP
========================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_internship'])) {
    $internshipId = isset($_POST['internship_id']) ? (int) $_POST['internship_id'] : 0;
    $title = trim($_POST['title'] ?? '');
    $industry = trim($_POST['industry'] ?? '');
    $techTags = trim($_POST['tech_tags'] ?? '');
    $duration = trim($_POST['duration'] ?? '');
    $deadline = trim($_POST['deadline'] ?? '');
    $updateFormData = [
        'id' => $internshipId,
        'title' => $title,
        'industry' => $industry,
        'tech_tags' => $techTags,
        'duration' => $duration,
        'deadline' => $deadline,
    ];

    $internshipToUpdate = $companyManager->getInternshipById($internshipId, $companyName);

    if (!$internshipToUpdate) {
        $updateError = 'Internship not found.';
    } elseif ($title === '' || $industry === '' || $duration === '' || $deadline === '') {
        $updateError = 'Please complete all required fields.';
    } else {
        $updated = $companyManager->updateInternship(
            $internshipId,
            $companyName,
            $title,
            $industry,
            $techTags,
            $duration,
            $deadline
        );

        if ($updated) {
            header('Location: internships.php?success=updated');
            exit;
        }

        $updateError = 'Unable to update internship.';
    }
}


/* =========================
   DELETE INTERNSHIP
========================= */

if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['delete_internship'])
) {

    $internshipId = isset($_POST['internship_id'])
        ? (int) $_POST['internship_id']
        : 0;

    if ($internshipId > 0) {

        $deleted = $companyManager->deleteInternship(
            $internshipId,
            $companyName
        );

    }

    header("Location: internships.php" . (!empty($deleted) ? "?success=deleted" : ""));
    exit;
}


/* =========================
   GET COMPANY INTERNSHIPS
========================= */

$internships = $companyManager->getCompanyInternships(
    $companyName
);


/* =========================
   DASHBOARD COUNTS
========================= */

$totalInternships = count($internships);

$activeCount = 0;

$closingSoon = 0;

$totalApplications = 0;


foreach ($internships as $internship) {

    $totalApplications +=
        isset($internship['applicant_count'])
            ? (int) $internship['applicant_count']
            : 0;


    $deadlineTimestamp =
        strtotime($internship['deadline'] ?? '');


    if (
        $deadlineTimestamp !== false
        && $deadlineTimestamp >= strtotime('today')
    ) {

        $activeCount++;


        $daysLeft = (int) ceil(
            (
                $deadlineTimestamp
                - strtotime('today')
            ) / 86400
        );


        if ($daysLeft <= 7) {

            $closingSoon++;

        }

    }

}


/*
This variable is used by the existing
company sidebar application badge.
*/

$companyApplicationCount =
    $totalApplications;


/* =========================
   PAGE CSS
========================= */

$extra_css = '
<link rel="stylesheet"
href="../../../Assets/CSS/Company/internships.css?v=' . filemtime(__DIR__ . '/../../../Assets/CSS/Company/internships.css') . '">
';


/* =========================
   SHARED LAYOUT
========================= */

include "../../../Includes/company_sidebar.php";

include "../../../Includes/dash_header.php";

$successMessages = [
    'added' => 'Internship added successfully',
    'updated' => 'Internship updated successfully',
    'deleted' => 'Internship deleted successfully',
];
$successMessage = $successMessages[$_GET['success'] ?? ''] ?? '';

?>


<main class="content company-internship-page">

    <?php if ($successMessage !== ''): ?>
        <div class="company-toast<?= ($_GET['success'] ?? '') === 'deleted' ? ' company-toast--deleted' : '' ?>" id="companyToast" role="status" aria-live="polite" aria-atomic="true">
            <span class="toast-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" focusable="false">
                    <path d="m5 12 4 4L19 6" />
                </svg>
            </span>
            <div class="toast-copy">
                <strong>Success</strong>
                <span><?= htmlspecialchars($successMessage) ?></span>
            </div>
            <button type="button" class="toast-close" aria-label="Close notification">
                <span aria-hidden="true">&times;</span>
            </button>
            <span class="toast-progress" aria-hidden="true"></span>
        </div>
    <?php endif; ?>


    <!-- =========================
         PAGE HEADER
    ========================== -->

    <section class="internship-page-header">


        <div>

            <p class="page-label">
                RECRUITMENT
            </p>


            <h1>
                Internship Management
            </h1>


            <p class="page-description">

                Create, manage and monitor internship
                opportunities posted by your company.

            </p>

        </div>



        <button
            type="button"
            class="add-internship-btn"
            id="openInternshipModal"
            aria-haspopup="dialog"
            aria-controls="internshipModal"
        >

            <span class="material-symbols-outlined">
                add
            </span>

            Post Internship

        </button>


    </section>



    <!-- =========================
         SUMMARY CARDS
    ========================== -->

    <section class="internship-summary-grid">


        <!-- TOTAL INTERNSHIPS -->

        <article class="internship-summary-card">


            <div class="summary-icon blue">

                <img
                    src="../../../Assets/Images/Icons/internship.png"
                    alt=""
                >

            </div>


            <div>

                <span>
                    Total Internships
                </span>

                <strong>
                    <?= $totalInternships ?>
                </strong>

            </div>


        </article>



        <!-- ACTIVE -->

        <article class="internship-summary-card">


            <div class="summary-icon green">

                <span class="material-symbols-outlined">
                    check_circle
                </span>

            </div>


            <div>

                <span>
                    Active
                </span>

                <strong>
                    <?= $activeCount ?>
                </strong>

            </div>


        </article>



        <!-- APPLICATIONS -->

        <article class="internship-summary-card">


            <div class="summary-icon orange">

                <img
                    src="../../../Assets/Images/Icons/application.png"
                    alt=""
                >

            </div>


            <div>

                <span>
                    Total Applications
                </span>

                <strong>
                    <?= $totalApplications ?>
                </strong>

            </div>


        </article>



        <!-- CLOSING SOON -->

        <article class="internship-summary-card">


            <div class="summary-icon red">

                <span class="material-symbols-outlined">
                    schedule
                </span>

            </div>


            <div>

                <span>
                    Closing Soon
                </span>

                <strong>
                    <?= $closingSoon ?>
                </strong>

            </div>


        </article>


    </section>



    <!-- =========================
         SEARCH + FILTER
    ========================== -->

    <section class="internship-toolbar">


        <div class="internship-search">


            <span class="material-symbols-outlined">
                search
            </span>


            <input
                type="text"
                id="internshipSearch"
                placeholder="Search internships..."
            >


        </div>



        <div class="internship-filters">


            <select id="statusFilter">


                <option value="all">
                    All Status
                </option>


                <option value="active">
                    Active
                </option>


                <option value="closed">
                    Closed
                </option>


            </select>


        </div>


    </section>



    <!-- =========================
         INTERNSHIP LIST
    ========================== -->

    <section class="internship-list-panel">


        <div class="internship-list-heading">


            <div>


                <h2>
                    Your Internship Opportunities
                </h2>


                <p>

                    Manage all internships posted
                    by your company.

                </p>


            </div>


        </div>



        <div class="internship-table-wrapper">


            <table
                class="internship-table"
                id="internshipTable"
            >


                <thead>


                    <tr>

                        <th>
                            INTERNSHIP
                        </th>

                        <th>
                            INDUSTRY
                        </th>

                        <th>
                            DURATION
                        </th>

                        <th>
                            DEADLINE
                        </th>

                        <th>
                            APPLICATIONS
                        </th>

                        <th>
                            STATUS
                        </th>

                        <th>
                            ACTION
                        </th>

                    </tr>


                </thead>



                <tbody>


                <?php if (empty($internships)): ?>


                    <tr>


                        <td
                            colspan="7"
                            class="empty-row"
                        >

                            No internships posted yet.

                        </td>


                    </tr>


                <?php else: ?>


                    <?php foreach ($internships as $internship): ?>


                        <?php


                        $deadlineTimestamp =
                            strtotime(
                                $internship['deadline'] ?? ''
                            );


                        $isActive =
                            $deadlineTimestamp !== false
                            &&
                            $deadlineTimestamp >=
                            strtotime('today');


                        $statusText =
                            $isActive
                                ? 'Active'
                                : 'Closed';


                        ?>


                        <tr
                            class="internship-row"
                            data-status="<?= strtolower($statusText) ?>"
                        >


                            <!-- INTERNSHIP -->


                            <td>


                                <div class="internship-title-cell">


                                    <div class="internship-icon-box">


                                        <img
                                            src="../../../Assets/Images/Icons/internship.png"
                                            alt=""
                                        >


                                    </div>



                                    <div>


                                        <strong>

                                            <?= htmlspecialchars(
                                                $internship['title'] ?? ''
                                            ) ?>

                                        </strong>


                                        <span>

                                            <?= htmlspecialchars(
                                                $internship['tech_tags'] ?? ''
                                            ) ?>

                                        </span>


                                    </div>


                                </div>


                            </td>



                            <!-- INDUSTRY -->


                            <td>

                                <?= htmlspecialchars(
                                    $internship['industry'] ?? ''
                                ) ?>

                            </td>



                            <!-- DURATION -->


                            <td>

                                <?= htmlspecialchars(
                                    $internship['duration'] ?? ''
                                ) ?>

                            </td>



                            <!-- DEADLINE -->


                            <td>

                                <?= htmlspecialchars(
                                    $internship['deadline'] ?? ''
                                ) ?>

                            </td>



                            <!-- APPLICATION COUNT -->


                            <td>

                                <?= isset(
                                    $internship['applicant_count']
                                )
                                    ? (int)
                                    $internship['applicant_count']
                                    : 0
                                ?>

                            </td>



                            <!-- STATUS -->


                            <td>


                                <span
                                    class="status <?= strtolower($statusText) ?>"
                                >

                                    <?= $statusText ?>

                                </span>


                            </td>



                            <!-- ACTION BUTTONS -->


                            <td>


                                <div class="action-buttons">


                                    <!-- UPDATE -->

                                    <button
                                        type="button"
                                        class="edit-action"
                                        title="Edit Internship"
                                        data-edit-internship='<?= htmlspecialchars(json_encode([
                                            'id' => (int) $internship['id'],
                                            'title' => $internship['title'] ?? '',
                                            'industry' => $internship['industry'] ?? '',
                                            'duration' => $internship['duration'] ?? '',
                                            'tech_tags' => $internship['tech_tags'] ?? '',
                                            'deadline' => $internship['deadline'] ?? '',
                                        ]), ENT_QUOTES, 'UTF-8') ?>'
                                    >

                                        <span class="material-symbols-outlined">
                                            edit
                                        </span>

                                    </button>



                                    <!-- DELETE -->

                                    <form
                                        method="POST"
                                        action=""
                                        class="delete-form"
                                    >


                                        <input
                                            type="hidden"
                                            name="internship_id"
                                            value="<?= (int) $internship['id'] ?>"
                                        >

                                        <input
                                            type="hidden"
                                            name="delete_internship"
                                            value="1"
                                        >


                                        <button
                                            type="submit"
                                            class="delete-action"
                                            title="Delete Internship"
                                        >

                                            <span class="material-symbols-outlined">
                                                delete
                                            </span>

                                        </button>


                                    </form>


                                </div>


                            </td>


                        </tr>


                    <?php endforeach; ?>


                <?php endif; ?>


                </tbody>


            </table>


        </div>


    </section>



</main>

<!-- =========================
     ADD INTERNSHIP MODAL
========================== -->
<div
    class="internship-modal"
    id="internshipModal"
    role="dialog"
    aria-modal="true"
    aria-labelledby="internshipModalTitle"
    aria-hidden="<?= $createError !== '' ? 'false' : 'true' ?>"
    <?= $createError !== '' ? '' : 'hidden' ?>
>
    <div class="internship-modal__backdrop" data-modal-close></div>

    <section class="internship-modal__panel" role="document">
        <div class="internship-modal__header">
            <div>
                <p class="page-label">RECRUITMENT</p>
                <h2 id="internshipModalTitle">Add Internship Opportunity</h2>
                <p>Create a new internship opportunity and publish it for students.</p>
            </div>
            <button type="button" class="internship-modal__close" data-modal-close aria-label="Close add internship form">
                <span class="material-symbols-outlined" aria-hidden="true">close</span>
            </button>
        </div>

        <div class="internship-modal__body">
            <?php if ($createError !== ''): ?>
                <div class="form-error" role="alert">
                    <?= htmlspecialchars($createError) ?>
                </div>
            <?php endif; ?>

            <form method="POST" id="internshipForm" autocomplete="off">
                <input type="hidden" name="create_internship" value="1">
                <div class="form-grid">
                    <div class="form-group full">
                        <label for="title">Internship Title</label>
                        <input id="title" type="text" name="title" maxlength="255" value="<?= htmlspecialchars($_POST['title'] ?? '') ?>" placeholder="e.g. Software Engineering Intern" required>
                    </div>
                    <div class="form-group">
                        <label for="industry">Industry</label>
                        <input id="industry" type="text" name="industry" maxlength="255" value="<?= htmlspecialchars($_POST['industry'] ?? '') ?>" placeholder="e.g. Software Development" required>
                    </div>
                    <div class="form-group">
                        <label for="duration">Duration</label>
                        <input id="duration" type="text" name="duration" maxlength="50" value="<?= htmlspecialchars($_POST['duration'] ?? '') ?>" placeholder="e.g. 6 Months" required>
                    </div>
                    <div class="form-group full">
                        <label for="tech_tags">Skills / Technologies</label>
                        <input id="tech_tags" type="text" name="tech_tags" maxlength="255" value="<?= htmlspecialchars($_POST['tech_tags'] ?? '') ?>" placeholder="e.g. PHP, MySQL, JavaScript">
                    </div>
                    <div class="form-group full">
                        <label for="deadline">Application Deadline</label>
                        <input id="deadline" type="text" name="deadline" maxlength="100" value="<?= htmlspecialchars($_POST['deadline'] ?? '') ?>" placeholder="e.g. Nov 15, 2026" required>
                    </div>
                </div>
                <div class="form-actions">
                    <button type="button" class="secondary-btn" data-modal-close>Cancel</button>
                    <button type="submit" class="primary-btn">
                        <span class="material-symbols-outlined" aria-hidden="true">publish</span>
                        Publish Internship
                    </button>
                </div>
            </form>
        </div>
    </section>
</div>

<!-- =========================
     EDIT INTERNSHIP MODAL
========================== -->
<div
    class="internship-modal"
    id="editInternshipModal"
    role="dialog"
    aria-modal="true"
    aria-labelledby="editInternshipModalTitle"
    aria-hidden="<?= $updateError !== '' ? 'false' : 'true' ?>"
    <?= $updateError !== '' ? '' : 'hidden' ?>
>
    <div class="internship-modal__backdrop" data-edit-modal-close></div>

    <section class="internship-modal__panel" role="document">
        <div class="internship-modal__header">
            <div>
                <p class="page-label">RECRUITMENT</p>
                <h2 id="editInternshipModalTitle">Edit Internship</h2>
                <p>Update the internship opportunity details.</p>
            </div>
            <button type="button" class="internship-modal__close" data-edit-modal-close aria-label="Close edit internship form">
                <span class="material-symbols-outlined" aria-hidden="true">close</span>
            </button>
        </div>

        <div class="internship-modal__body">
            <?php if ($updateError !== ''): ?>
                <div class="form-error" role="alert">
                    <?= htmlspecialchars($updateError) ?>
                </div>
            <?php endif; ?>

            <form method="POST" id="editInternshipForm" autocomplete="off">
                <input type="hidden" name="update_internship" value="1">
                <input type="hidden" name="internship_id" id="edit_internship_id" value="<?= (int) ($updateFormData['id'] ?? 0) ?>">
                <div class="form-grid">
                    <div class="form-group full">
                        <label for="edit_title">Internship Title</label>
                        <input id="edit_title" type="text" name="title" maxlength="255" value="<?= htmlspecialchars($updateFormData['title'] ?? '') ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_industry">Industry</label>
                        <input id="edit_industry" type="text" name="industry" maxlength="255" value="<?= htmlspecialchars($updateFormData['industry'] ?? '') ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_duration">Duration</label>
                        <input id="edit_duration" type="text" name="duration" maxlength="50" value="<?= htmlspecialchars($updateFormData['duration'] ?? '') ?>" required>
                    </div>
                    <div class="form-group full">
                        <label for="edit_tech_tags">Skills / Technologies</label>
                        <input id="edit_tech_tags" type="text" name="tech_tags" maxlength="255" value="<?= htmlspecialchars($updateFormData['tech_tags'] ?? '') ?>">
                    </div>
                    <div class="form-group full">
                        <label for="edit_deadline">Application Deadline</label>
                        <input id="edit_deadline" type="text" name="deadline" maxlength="100" value="<?= htmlspecialchars($updateFormData['deadline'] ?? '') ?>" required>
                    </div>
                </div>
                <div class="form-actions">
                    <button type="button" class="secondary-btn" data-edit-modal-close>Cancel</button>
                    <button type="submit" class="primary-btn">Save Changes</button>
                </div>
            </form>
        </div>
    </section>
</div>

<!-- =========================
     DELETE INTERNSHIP MODAL
========================== -->
<div
    class="delete-internship-modal"
    id="deleteInternshipModal"
    role="dialog"
    aria-modal="true"
    aria-labelledby="deleteInternshipModalTitle"
    aria-hidden="true"
    hidden
>
    <div class="delete-internship-modal__backdrop" data-delete-modal-close></div>

    <section class="delete-internship-modal__panel" role="document">
        <button type="button" class="delete-internship-modal__close" data-delete-modal-close aria-label="Close delete confirmation">
            <span class="material-symbols-outlined" aria-hidden="true">close</span>
        </button>

        <div class="delete-internship-modal__icon" aria-hidden="true">
            <span class="material-symbols-outlined">delete</span>
        </div>

        <h2 id="deleteInternshipModalTitle">Delete Internship?</h2>
        <p>Are you sure you want to delete this internship? This action cannot be undone.</p>

        <div class="delete-internship-modal__actions">
            <button type="button" class="secondary-btn" data-delete-modal-close>Cancel</button>
            <button type="button" class="delete-internship-modal__confirm" id="confirmDeleteInternship">
                <span class="material-symbols-outlined" aria-hidden="true">delete</span>
                Delete Internship
            </button>
        </div>
    </section>
</div>



<!-- =========================
     INTERNSHIP JAVASCRIPT
========================== -->

<script src="../../../Assets/JS/Company/internships.js?v=<?= filemtime(__DIR__ . '/../../../Assets/JS/Company/internships.js') ?>"></script>




<?php

include "../../../Includes/dash_footer.php";

?>
