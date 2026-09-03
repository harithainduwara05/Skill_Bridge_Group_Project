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

        $companyManager->deleteInternship(
            $internshipId,
            $companyName
        );

    }

    header("Location: internships.php");
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
href="../../../Assets/CSS/Company/internships.css">
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
                Internship Management
            </h1>


            <p class="page-description">

                Create, manage and monitor internship
                opportunities posted by your company.

            </p>

        </div>



        <a
            href="add_internship.php"
            class="add-internship-btn"
        >

            <span class="material-symbols-outlined">
                add
            </span>

            Post Internship

        </a>


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


                                    <!-- READ -->

                                    <a
                                        href="view_internship.php?id=<?= (int) $internship['id'] ?>"
                                        title="View Internship"
                                    >

                                        <span class="material-symbols-outlined">
                                            visibility
                                        </span>

                                    </a>



                                    <!-- UPDATE -->

                                    <a
                                        href="edit_internship.php?id=<?= (int) $internship['id'] ?>"
                                        title="Edit Internship"
                                    >

                                        <span class="material-symbols-outlined">
                                            edit
                                        </span>

                                    </a>



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


                                        <button
                                            type="submit"
                                            name="delete_internship"
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
     INTERNSHIP JAVASCRIPT
========================== -->

<script src="../../../Assets/JS/Company/internships.js"></script>



<?php

include "../../../Includes/dash_footer.php";

?>