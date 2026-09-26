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
   GET INTERNSHIP ID
========================= */

$internshipId = isset($_GET['id'])
    ? (int) $_GET['id']
    : 0;

if ($internshipId <= 0) {
    header("Location: internships.php");
    exit;
}


/* =========================
   GET INTERNSHIP
========================= */

$internship = $companyManager->getInternshipById(
    $internshipId,
    $companyName
);

if (!$internship) {
    die('Internship not found.');
}


/* =========================
   SIDEBAR APPLICATION COUNT
========================= */

$companyInternships = $companyManager->getCompanyInternships(
    $companyName
);

$companyApplicationCount = 0;

foreach ($companyInternships as $item) {

    $companyApplicationCount +=
        isset($item['applicant_count'])
            ? (int) $item['applicant_count']
            : 0;
}


/* =========================
   STATUS
========================= */

$deadlineTimestamp = strtotime(
    $internship['deadline'] ?? ''
);

$isActive =
    $deadlineTimestamp !== false
    &&
    $deadlineTimestamp >= strtotime('today');

$statusText = $isActive
    ? 'Active'
    : 'Closed';


/* =========================
   FORMAT START DATE
========================= */

$startDate = 'Not specified';

if (!empty($internship['start_date'])) {

    $startTimestamp =
        strtotime($internship['start_date']);

    if ($startTimestamp !== false) {

        $startDate =
            date(
                'M j, Y',
                $startTimestamp
            );
    }
}


/* =========================
   COVER IMAGE
========================= */

$coverImage = '';

if (!empty($internship['cover_image'])) {

    $coverImage =
        '../../../' .
        ltrim(
            $internship['cover_image'],
            '/'
        );
}


/* =========================
   SUPPORTING DOCUMENT
========================= */

$supportingDocument = '';

if (!empty(
    $internship['supporting_document']
)) {

    $supportingDocument =
        '../../../' .
        ltrim(
            $internship['supporting_document'],
            '/'
        );
}


/* =========================
   PAGE CSS
========================= */

$extra_css = '

<link
    rel="stylesheet"
    href="../../../Assets/CSS/Company/internships.css?v=' .
    filemtime(
        __DIR__ .
        '/../../../Assets/CSS/Company/internships.css'
    ) .
'">

<style>

.company-internship-view-page {
    min-height: 100vh;
    padding: 28px 36px 50px;
    background: #f5f7fb;
    font-family: "Inter", sans-serif;
}


/* PAGE HEADER */

.internship-view-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 20px;
    margin-bottom: 24px;
}

.internship-view-header h1 {
    margin: 0;
    color: #102d58;
    font-size: 32px;
    font-weight: 800;
}

.internship-view-header p {
    margin: 8px 0 0;
    color: #6b7280;
    font-size: 14px;
}

.back-internship-btn {
    min-height: 44px;
    padding: 0 18px;

    border: 1px solid #d7dfea;
    border-radius: 8px;

    background: #ffffff;
    color: #40516a;

    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 7px;

    text-decoration: none;

    font-size: 13px;
    font-weight: 700;
}

.back-internship-btn:hover {
    background: #f4f7fb;
    color: #18345f;
}


/* MAIN CARD */

.internship-view-card {
    overflow: hidden;

    background: #ffffff;

    border: 1px solid #e0e6ee;
    border-radius: 14px;

    box-shadow:
        0 5px 18px
        rgba(17, 42, 79, 0.04);
}


/* COVER */

.internship-cover {
    width: 100%;
    max-height: 330px;

    background: #eef3f9;

    overflow: hidden;
}

.internship-cover img {
    width: 100%;
    height: 330px;

    display: block;

    object-fit: cover;
}


/* TITLE AREA */

.internship-view-top {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;

    gap: 20px;

    padding: 26px 30px;

    border-bottom: 1px solid #e8edf3;
}

.internship-view-top h2 {
    margin: 0 0 8px;

    color: #102d58;

    font-size: 24px;
    font-weight: 800;
}

.internship-view-top .industry {
    margin: 0;

    color: #70798a;

    font-size: 14px;
}

.internship-view-status {
    display: inline-flex;
    align-items: center;
    justify-content: center;

    min-width: 82px;

    padding: 7px 14px;

    border-radius: 50px;

    font-size: 12px;
    font-weight: 700;
}

.internship-view-status.active {
    background: #e4f7eb;
    color: #2f8257;
}

.internship-view-status.closed {
    background: #fbe7e7;
    color: #b15d5d;
}


/* CONTENT */

.internship-view-body {
    padding: 30px;
}

.view-section {
    margin-bottom: 32px;
}

.view-section:last-child {
    margin-bottom: 0;
}

.view-section h3 {
    margin: 0 0 17px;

    color: #102d58;

    font-size: 17px;
    font-weight: 800;
}

.view-section-text {
    margin: 0;

    color: #536073;

    font-size: 14px;

    line-height: 1.8;

    white-space: pre-line;
}


/* DETAILS GRID */

.internship-view-grid {
    display: grid;

    grid-template-columns:
        repeat(3, 1fr);

    gap: 16px;
}

.view-detail-item {
    padding: 17px;

    background: #f8fafd;

    border: 1px solid #e4e9f0;
    border-radius: 10px;
}

.view-detail-item span {
    display: block;

    margin-bottom: 7px;

    color: #838b99;

    font-size: 10px;
    font-weight: 800;

    text-transform: uppercase;

    letter-spacing: .3px;
}

.view-detail-item strong {
    color: #293c5d;

    font-size: 13px;
    font-weight: 700;

    line-height: 1.5;
}


/* SKILLS */

.skills-list {
    display: flex;
    flex-wrap: wrap;

    gap: 9px;
}

.skill-chip {
    padding: 7px 11px;

    background: #eef4ff;
    color: #315991;

    border-radius: 50px;

    font-size: 12px;
    font-weight: 600;
}


/* DOCUMENT */

.document-link {
    min-height: 44px;

    padding: 0 16px;

    display: inline-flex;
    align-items: center;
    justify-content: center;

    gap: 8px;

    border: 1px solid #cfd9e7;
    border-radius: 8px;

    color: #244274;
    background: #ffffff;

    text-decoration: none;

    font-size: 13px;
    font-weight: 700;
}

.document-link:hover {
    background: #eef4ff;
}


/* MOBILE */

@media (max-width: 900px) {

    .internship-view-grid {
        grid-template-columns:
            repeat(2, 1fr);
    }

}

@media (max-width: 650px) {

    .company-internship-view-page {
        padding: 22px 18px 40px;
    }

    .internship-view-header {
        flex-direction: column;
    }

    .internship-view-grid {
        grid-template-columns: 1fr;
    }

    .internship-view-top {
        padding: 22px 20px;
    }

    .internship-view-body {
        padding: 24px 20px;
    }

    .internship-cover img {
        height: 230px;
    }

}

</style>

';


/* =========================
   SHARED LAYOUT
========================= */

include "../../../Includes/company_sidebar.php";
include "../../../Includes/dash_header.php";

?>


<main class="content company-internship-view-page">


    <!-- =========================
         HEADER
    ========================== -->

    <section class="internship-view-header">

        <div>

            <p class="page-label">
                RECRUITMENT
            </p>

            <h1>
                Internship Details
            </h1>

            <p>
                View complete information about
                this internship opportunity.
            </p>

        </div>


        <a
            href="internships.php"
            class="back-internship-btn"
        >

            <span class="material-symbols-outlined">
                arrow_back
            </span>

            Back to Internships

        </a>

    </section>



    <!-- =========================
         INTERNSHIP CARD
    ========================== -->

    <section class="internship-view-card">


        <!-- COVER IMAGE -->

        <?php if ($coverImage !== ''): ?>

            <div class="internship-cover">

                <img
                    src="<?= htmlspecialchars($coverImage) ?>"
                    alt="<?= htmlspecialchars(
                        $internship['title'] ?? 'Internship'
                    ) ?>"
                >

            </div>

        <?php endif; ?>



        <!-- TITLE + STATUS -->

        <div class="internship-view-top">

            <div>

                <h2>

                    <?= htmlspecialchars(
                        $internship['title'] ?? ''
                    ) ?>

                </h2>


                <p class="industry">

                    <?= htmlspecialchars(
                        $internship['industry']
                        ?? 'Industry not specified'
                    ) ?>

                </p>

            </div>


            <span
                class="internship-view-status <?= strtolower($statusText) ?>"
            >

                <?= htmlspecialchars($statusText) ?>

            </span>

        </div>



        <div class="internship-view-body">


            <!-- =========================
                 DESCRIPTION
            ========================== -->

            <section class="view-section">

                <h3>
                    Internship Description
                </h3>


                <p class="view-section-text">

                    <?= htmlspecialchars(
                        $internship['description']
                        ?? 'No description provided.'
                    ) ?>

                </p>

            </section>



            <!-- =========================
                 INTERNSHIP DETAILS
            ========================== -->

            <section class="view-section">

                <h3>
                    Internship Details
                </h3>


                <div class="internship-view-grid">


                    <!-- DURATION -->

                    <div class="view-detail-item">

                        <span>
                            Duration
                        </span>

                        <strong>

                            <?= htmlspecialchars(
                                $internship['duration']
                                ?? 'Not specified'
                            ) ?>

                        </strong>

                    </div>



                    <!-- TYPE -->

                    <div class="view-detail-item">

                        <span>
                            Internship Type
                        </span>

                        <strong>

                            <?= htmlspecialchars(
                                $internship['internship_type']
                                ?? 'Not specified'
                            ) ?>

                        </strong>

                    </div>



                    <!-- WORK MODE -->

                    <div class="view-detail-item">

                        <span>
                            Work Mode
                        </span>

                        <strong>

                            <?= htmlspecialchars(
                                $internship['work_mode']
                                ?? 'Not specified'
                            ) ?>

                        </strong>

                    </div>



                    <!-- LOCATION -->

                    <div class="view-detail-item">

                        <span>
                            Location
                        </span>

                        <strong>

                            <?= htmlspecialchars(
                                !empty($internship['location'])
                                    ? $internship['location']
                                    : 'Not specified'
                            ) ?>

                        </strong>

                    </div>



                    <!-- VACANCIES -->

                    <div class="view-detail-item">

                        <span>
                            Vacancies
                        </span>

                        <strong>

                            <?= isset(
                                $internship['vacancies']
                            )
                                ? (int)
                                $internship['vacancies']
                                : 0
                            ?>

                        </strong>

                    </div>



                    <!-- EXPERIENCE -->

                    <div class="view-detail-item">

                        <span>
                            Experience Level
                        </span>

                        <strong>

                            <?= htmlspecialchars(
                                !empty(
                                    $internship[
                                        'experience_level'
                                    ]
                                )
                                    ? $internship[
                                        'experience_level'
                                    ]
                                    : 'Not specified'
                            ) ?>

                        </strong>

                    </div>



                    <!-- ACADEMIC YEAR -->

                    <div class="view-detail-item">

                        <span>
                            Preferred Academic Year
                        </span>

                        <strong>

                            <?= htmlspecialchars(
                                !empty(
                                    $internship[
                                        'academic_year'
                                    ]
                                )
                                    ? $internship[
                                        'academic_year'
                                    ]
                                    : 'Any Year'
                            ) ?>

                        </strong>

                    </div>



                    <!-- START DATE -->

                    <div class="view-detail-item">

                        <span>
                            Start Date
                        </span>

                        <strong>

                            <?= htmlspecialchars(
                                $startDate
                            ) ?>

                        </strong>

                    </div>



                    <!-- DEADLINE -->

                    <div class="view-detail-item">

                        <span>
                            Application Deadline
                        </span>

                        <strong>

                            <?= htmlspecialchars(
                                $internship['deadline']
                                ?? 'Not specified'
                            ) ?>

                        </strong>

                    </div>


                </div>

            </section>



            <!-- =========================
                 SKILLS
            ========================== -->

            <section class="view-section">

                <h3>
                    Required Skills / Technologies
                </h3>


                <div class="skills-list">

                    <?php

                    $skills = array_filter(
                        array_map(
                            'trim',
                            explode(
                                ',',
                                $internship['tech_tags']
                                ?? ''
                            )
                        )
                    );

                    ?>


                    <?php if (!empty($skills)): ?>

                        <?php foreach ($skills as $skill): ?>

                            <span class="skill-chip">

                                <?= htmlspecialchars($skill) ?>

                            </span>

                        <?php endforeach; ?>


                    <?php else: ?>

                        <p class="view-section-text">
                            No skills specified.
                        </p>

                    <?php endif; ?>

                </div>

            </section>



            <!-- =========================
                 RESPONSIBILITIES
            ========================== -->

            <section class="view-section">

                <h3>
                    Responsibilities
                </h3>


                <p class="view-section-text">

                    <?= htmlspecialchars(
                        !empty(
                            $internship[
                                'responsibilities'
                            ]
                        )
                            ? $internship[
                                'responsibilities'
                            ]
                            : 'No responsibilities provided.'
                    ) ?>

                </p>

            </section>



            <!-- =========================
                 BENEFITS
            ========================== -->

            <section class="view-section">

                <h3>
                    Learning Opportunities / Benefits
                </h3>


                <p class="view-section-text">

                    <?= htmlspecialchars(
                        !empty(
                            $internship['benefits']
                        )
                            ? $internship['benefits']
                            : 'No benefits specified.'
                    ) ?>

                </p>

            </section>



            <!-- =========================
                 ALLOWANCE
            ========================== -->

            <section class="view-section">

                <h3>
                    Allowance Information
                </h3>


                <div class="internship-view-grid">


                    <div class="view-detail-item">

                        <span>
                            Allowance Offered
                        </span>

                        <strong>

                            <?= (
                                $internship[
                                    'paid_status'
                                ]
                                ?? 'Unpaid'
                            ) === 'Paid'
                                ? 'Yes'
                                : 'No'
                            ?>

                        </strong>

                    </div>


                    <div class="view-detail-item">

                        <span>
                            Monthly Allowance
                        </span>

                        <strong>

                            <?php if (
                                (
                                    $internship[
                                        'paid_status'
                                    ]
                                    ?? 'Unpaid'
                                ) === 'Paid'
                                &&
                                isset(
                                    $internship[
                                        'stipend'
                                    ]
                                )
                            ): ?>

                                LKR
                                <?= number_format(
                                    (float)
                                    $internship[
                                        'stipend'
                                    ],
                                    2
                                ) ?>

                            <?php else: ?>

                                Not applicable

                            <?php endif; ?>

                        </strong>

                    </div>


                </div>

            </section>



            <!-- =========================
                 SUPPORTING DOCUMENT
            ========================== -->

            <?php if (
                $supportingDocument !== ''
            ): ?>

                <section class="view-section">

                    <h3>
                        Supporting Document
                    </h3>


                    <a
                        href="<?= htmlspecialchars(
                            $supportingDocument
                        ) ?>"
                        class="document-link"
                        target="_blank"
                        rel="noopener noreferrer"
                    >

                        <span class="material-symbols-outlined">
                            description
                        </span>

                        View Supporting Document

                    </a>

                </section>

            <?php endif; ?>


        </div>

    </section>


    <?php
    include "../../../Includes/company_dashboard_footer.php";
    ?>


</main>


<?php

include "../../../Includes/dash_footer.php";

?>