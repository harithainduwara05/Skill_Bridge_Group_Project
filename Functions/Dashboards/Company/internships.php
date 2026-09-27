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
   FILE UPLOAD HELPER
========================= */

function uploadInternshipFile($file, $type = 'document')
{
    if (!isset($file) || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) return '';
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) return false;
    if (($file['size'] ?? 0) > 10 * 1024 * 1024) return false;

    $extension = strtolower(pathinfo($file['name'] ?? '', PATHINFO_EXTENSION));
    $allowed = $type === 'image'
        ? ['jpg', 'jpeg', 'png', 'webp']
        : ['pdf', 'doc', 'docx'];

    if (!in_array($extension, $allowed, true)) return false;

    $folder = $type === 'image' ? 'internship_images' : 'internship_documents';
    $uploadDir = __DIR__ . '/../../../Uploads/Company/' . $folder . '/';

    if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true) && !is_dir($uploadDir)) return false;

    $fileName = uniqid('internship_', true) . '.' . $extension;
    if (!move_uploaded_file($file['tmp_name'], $uploadDir . $fileName)) return false;

    return 'Uploads/Company/' . $folder . '/' . $fileName;
}

function normaliseInternshipDeadline($value)
{
    $value = trim((string) $value);
    if ($value === '') return '';
    $timestamp = strtotime($value);
    return $timestamp === false ? '' : date('M j, Y', $timestamp);
}


/* =========================
   CREATE INTERNSHIP
========================= */

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_internship'])) {
    $title = trim($_POST['title'] ?? '');
    $industry = trim($_POST['industry'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $techTags = trim($_POST['tech_tags'] ?? '');
    $academicYear = trim($_POST['academic_year'] ?? '');
    $experienceLevel = trim($_POST['experience_level'] ?? '');
    $vacancies = max(0, (int) ($_POST['vacancies'] ?? 0));
    $duration = trim($_POST['duration'] ?? '');
    $internshipType = trim($_POST['internship_type'] ?? '');
    $workMode = trim($_POST['work_mode'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $startDate = trim($_POST['start_date'] ?? '');
    $deadlineInput = trim($_POST['deadline'] ?? '');
    $deadline = normaliseInternshipDeadline($deadlineInput);
    $paidStatus = trim($_POST['paid_status'] ?? 'Unpaid');
    $stipend = ($_POST['stipend'] ?? '') !== '' ? (float) $_POST['stipend'] : 0;
    $responsibilities = trim($_POST['responsibilities'] ?? '');
    $benefits = trim($_POST['benefits'] ?? '');

    $coverImage = uploadInternshipFile($_FILES['cover_image'] ?? null, 'image');
    if ($coverImage === false) {
        $createError = 'Invalid cover image. Upload JPG, JPEG, PNG or WEBP under 10MB.';
    }

    $supportingDocument = '';
    if ($createError === '') {
        $supportingDocument = uploadInternshipFile($_FILES['supporting_document'] ?? null, 'document');
        if ($supportingDocument === false) {
            $createError = 'Invalid document. Upload PDF, DOC or DOCX under 10MB.';
        }
    }

    if ($createError === '' && (
        $title === '' || $industry === '' || $description === '' || $techTags === '' ||
        $duration === '' || $deadline === '' || $vacancies < 1
    )) {
        $createError = 'Please complete all required fields.';
    }

    if ($createError === '') {
        $created = $companyManager->createInternship(
            $title, $companyName, $industry, $description, $coverImage, $techTags,
            $academicYear, $experienceLevel, $vacancies, $duration, $internshipType,
            $workMode, $location, $startDate, $deadline, $paidStatus, $stipend,
            $responsibilities, $benefits, $supportingDocument
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
    $internshipId = (int) ($_POST['internship_id'] ?? 0);
    $internshipToUpdate = $companyManager->getInternshipById($internshipId, $companyName);

    if (!$internshipToUpdate) {
        $updateError = 'Internship not found.';
    } elseif (strtolower($internshipToUpdate['status'] ?? '') === 'terminated') {
        $updateError = 'Terminated internships cannot be edited.';
    } else {
        $title = trim($_POST['title'] ?? '');
        $industry = trim($_POST['industry'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $techTags = trim($_POST['tech_tags'] ?? '');
        $academicYear = trim($_POST['academic_year'] ?? '');
        $experienceLevel = trim($_POST['experience_level'] ?? '');
        $vacancies = max(0, (int) ($_POST['vacancies'] ?? 0));
        $duration = trim($_POST['duration'] ?? '');
        $internshipType = trim($_POST['internship_type'] ?? '');
        $workMode = trim($_POST['work_mode'] ?? '');
        $location = trim($_POST['location'] ?? '');
        $startDate = trim($_POST['start_date'] ?? '');
        $deadlineInput = trim($_POST['deadline'] ?? '');
        $deadline = normaliseInternshipDeadline($deadlineInput);
        $paidStatus = trim($_POST['paid_status'] ?? 'Unpaid');
        $stipend = ($_POST['stipend'] ?? '') !== '' ? (float) $_POST['stipend'] : 0;
        $responsibilities = trim($_POST['responsibilities'] ?? '');
        $benefits = trim($_POST['benefits'] ?? '');

        $coverImage = $internshipToUpdate['cover_image'] ?? '';
        $newCover = uploadInternshipFile($_FILES['cover_image'] ?? null, 'image');
        if ($newCover === false) {
            $updateError = 'Invalid cover image. Upload JPG, JPEG, PNG or WEBP under 10MB.';
        } elseif ($newCover !== '') {
            $coverImage = $newCover;
        }

        $supportingDocument = $internshipToUpdate['supporting_document'] ?? '';
        if ($updateError === '') {
            $newDocument = uploadInternshipFile($_FILES['supporting_document'] ?? null, 'document');
            if ($newDocument === false) {
                $updateError = 'Invalid document. Upload PDF, DOC or DOCX under 10MB.';
            } elseif ($newDocument !== '') {
                $supportingDocument = $newDocument;
            }
        }

        $editStatus = ucfirst(strtolower(trim($internshipToUpdate['status'] ?? '')));
        $editDeadlineTimestamp = strtotime($internshipToUpdate['deadline'] ?? '');
        if ($editStatus === 'Active' && $editDeadlineTimestamp !== false && $editDeadlineTimestamp < strtotime('today')) {
            $editStatus = 'Closed';
        }

        $updateFormData = [
            'id' => $internshipId, 'status' => $editStatus, 'title' => $title, 'industry' => $industry,
            'description' => $description, 'tech_tags' => $techTags,
            'academic_year' => $academicYear, 'experience_level' => $experienceLevel,
            'vacancies' => $vacancies, 'duration' => $duration,
            'internship_type' => $internshipType, 'work_mode' => $workMode,
            'location' => $location, 'start_date' => $startDate,
            'deadline' => $deadlineInput, 'paid_status' => $paidStatus,
            'stipend' => $stipend, 'responsibilities' => $responsibilities,
            'benefits' => $benefits
        ];

        if ($updateError === '' && (
            $title === '' || $industry === '' || $duration === '' || $deadline === ''
        )) {
            $updateError = 'Please complete all required fields.';
        }

        if ($updateError === '') {
            $updated = $companyManager->updateInternship(
                $internshipId, $companyName, $title, $industry, $description, $coverImage,
                $techTags, $academicYear, $experienceLevel, $vacancies, $duration,
                $internshipType, $workMode, $location, $startDate, $deadline, $paidStatus,
                $stipend, $responsibilities, $benefits, $supportingDocument
            );

            if ($updated) {
                if (isset($_POST['status']) && $_POST['status'] !== '') {
                    $statusUpdated = $companyManager->setCompanyInternshipStatus(
                        $internshipId,
                        $companyName,
                        $_POST['status']
                    );
                    if (!$statusUpdated) {
                        $updateError = 'Internship details were saved, but the requested status change was not allowed.';
                    }
                }
                if ($updateError !== '') {
                    // Keep the edit dialog open to show the status validation message.
                } else {
                header('Location: internships.php?success=updated');
                exit;
                }
            }

            $updateError = 'Unable to update internship.';
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['request_reactivation'])) {
    $id = (int) ($_POST['internship_id'] ?? 0);
    $requested = $companyManager->requestInternshipReactivation($id, $companyName);
    header('Location: internships.php?notice=' . ($requested ? 'reactivation_requested' : 'reactivation_unavailable'));
    exit;
}


/* =========================

   DELETE INTERNSHIP

========================= */



if (

    $_SERVER['REQUEST_METHOD'] === 'POST'

    && isset($_POST['delete_internship'])

) {

    $deleted = false;



    $internshipId = isset($_POST['internship_id'])

        ? (int) $_POST['internship_id']

        : 0;



    if ($internshipId > 0) {



        $deleted = $companyManager->deleteInternship(

            $internshipId,

            $companyName

        );



    }



    header("Location: internships.php" . (!empty($deleted) ? "?success=deleted" : "?notice=delete_blocked"));

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





    $recordStatus = strtolower(trim($internship['status'] ?? ''));
    $effectiveStatus = in_array($recordStatus, ['active', 'closed', 'suspended', 'terminated'], true)
        ? $recordStatus
        : (($deadlineTimestamp !== false && $deadlineTimestamp >= strtotime('today')) ? 'active' : 'closed');
    if ($effectiveStatus === 'active') {



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
    'reactivation_requested' => 'Reactivation request submitted. Waiting for administrator approval.',
    'status_updated' => 'Internship status updated.',

];

$successMessage = $successMessages[$_GET['notice'] ?? $_GET['success'] ?? ''] ?? '';
$pageError = ($_GET['notice'] ?? '') === 'status_rejected'
    ? 'This status change is not allowed. Active status requires a valid future deadline.'
    : ((($_GET['notice'] ?? '') === 'reactivation_unavailable')
        ? 'A reactivation request could not be submitted.'
        : ((($_GET['notice'] ?? '') === 'delete_blocked')
            ? 'This internship cannot be deleted because applications have already been submitted.'
            : ''));



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
    <?php if ($pageError !== ''): ?><div class="internship-inline-error" role="alert"><?= htmlspecialchars($pageError) ?></div><?php endif; ?>
    <div class="internship-inline-error" id="deleteValidationMessage" role="alert" hidden></div>





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





                        <option value="closed">Closed</option>
                <option value="suspended">Suspended</option>
                <option value="terminated">Terminated</option>





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





                        $storedStatus = strtolower(trim($internship['status'] ?? ''));
                        $statusText = ($storedStatus === 'active' && !$isActive)
                            ? 'Closed'
                            : (in_array($storedStatus, ['active', 'closed', 'suspended', 'terminated'], true)
                                ? ucfirst($storedStatus)
                                : ($isActive ? 'Active' : 'Closed'));





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
                                <?php if ($statusText === 'Suspended' && strtolower(trim($internship['reactivation_status'] ?? '')) === 'requested'): ?>
                                    <span class="reactivation-requested-badge">Reactivation Requested</span>
                                <?php endif; ?>





                            </td>







                            <!-- ACTION BUTTONS -->





                            <td>





                                <div class="action-buttons">

                                <a
    href="view_internship.php?id=<?= (int) $internship['id'] ?>"
    class="view-action"
    title="View Internship"
>
    <span class="material-symbols-outlined">
        visibility
    </span>
</a>





                                    <!-- UPDATE -->



                                    <?php if ($statusText !== 'Terminated'): ?><button

                                        type="button"

                                        class="edit-action"

                                        title="<?= $statusText === 'Suspended' ? 'Fix Internship' : 'Edit Internship' ?>"
                                        aria-label="<?= $statusText === 'Suspended' ? 'Fix Internship' : 'Edit Internship' ?>"
                                        data-internship-id="<?= (int) $internship['id'] ?>"

                                        data-edit-internship='<?= htmlspecialchars(json_encode([

                                            'id' => (int) $internship['id'],
                                            'status' => $statusText,
                                            'title' => $internship['title'] ?? '',
                                            'industry' => $internship['industry'] ?? '',
                                            'description' => $internship['description'] ?? '',
                                            'tech_tags' => $internship['tech_tags'] ?? '',
                                            'academic_year' => $internship['academic_year'] ?? '',
                                            'experience_level' => $internship['experience_level'] ?? '',
                                            'vacancies' => $internship['vacancies'] ?? '',
                                            'duration' => $internship['duration'] ?? '',
                                            'internship_type' => $internship['internship_type'] ?? '',
                                            'work_mode' => $internship['work_mode'] ?? '',
                                            'location' => $internship['location'] ?? '',
                                            'start_date' => $internship['start_date'] ?? '',
                                            'deadline' => !empty($internship['deadline']) ? date('Y-m-d', strtotime($internship['deadline'])) : '',
                                            'paid_status' => $internship['paid_status'] ?? 'Unpaid',
                                            'stipend' => $internship['stipend'] ?? '',
                                            'responsibilities' => $internship['responsibilities'] ?? '',
                                            'benefits' => $internship['benefits'] ?? '',

                                        ]), ENT_QUOTES, 'UTF-8') ?>'

                                    >



                                        <span class="material-symbols-outlined">

                                            edit

                                        </span>



                                    </button><?php endif; ?>

                                    <?php if ($statusText === 'Suspended'): ?>
                                        <button type="button" class="view-reason-action" data-admin-reason="<?= htmlspecialchars($internship['admin_reason'] ?? '', ENT_QUOTES, 'UTF-8') ?>" data-reason-title="Internship Suspended" aria-label="View Admin reason" title="Admin Reason"><span class="material-symbols-outlined">info</span></button>
                                        <?php if (strtolower(trim($internship['reactivation_status'] ?? '')) !== 'requested'): ?>
                                            <form method="POST" class="reactivation-form"><input type="hidden" name="internship_id" value="<?= (int) $internship['id'] ?>"><button type="submit" name="request_reactivation" value="1" class="request-reactivation-action" aria-label="Request Reactivation" title="Request Reactivation"><span class="material-symbols-outlined">send</span><span>Request</span></button></form>
                                        <?php endif; ?>
                                    <?php elseif ($statusText === 'Terminated'): ?>
                                        <button type="button" class="view-reason-action" data-admin-reason="<?= htmlspecialchars($internship['admin_reason'] ?? '', ENT_QUOTES, 'UTF-8') ?>" data-reason-title="Internship Terminated" aria-label="View Admin reason" title="Admin Reason"><span class="material-symbols-outlined">info</span></button>
                                    <?php endif; ?>







                                    <!-- DELETE -->



                                    <?php if ($statusText !== 'Suspended'): ?><form

                                        method="POST"

                                        action=""

                                        class="delete-form"

                                        data-applicant-count="<?= (int) ($internship['applicant_count'] ?? 0) ?>"

                                        data-status="<?= strtolower($statusText) ?>"

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





                                    </form><?php endif; ?>





                                </div>





                            </td>





                        </tr>





                    <?php endforeach; ?>





                <?php endif; ?>





                </tbody>





            </table>





        </div>





    </section>



    <?php include "../../../Includes/company_dashboard_footer.php"; ?>

</main>



<!-- =========================
     ADD INTERNSHIP MODAL
========================== -->

<div class="internship-modal" id="internshipModal" role="dialog" aria-modal="true"
    aria-labelledby="internshipModalTitle" aria-hidden="<?= $createError !== '' ? 'false' : 'true' ?>"
    <?= $createError !== '' ? '' : 'hidden' ?>>
    <div class="internship-modal__backdrop" data-modal-close></div>

    <section class="internship-modal__panel" role="document">
        <div class="internship-modal__header">
            <div>
                <p class="page-label">RECRUITMENT</p>
                <h2 id="internshipModalTitle">Post Internship Opportunity</h2>
                <p>Add complete internship details before publishing for students.</p>
            </div>
            <button type="button" class="internship-modal__close" data-modal-close aria-label="Close add internship form">
                <span class="material-symbols-outlined" aria-hidden="true">close</span>
            </button>
        </div>

        <div class="internship-modal__body">
            <?php if ($createError !== ''): ?>
                <div class="form-error" role="alert"><?= htmlspecialchars($createError) ?></div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data" id="internshipForm" autocomplete="off">
                <input type="hidden" name="create_internship" value="1">

                <div class="internship-form-section">
                    <h3>Basic Information</h3>
                    <div class="form-grid">
                        <div class="form-group full">
                            <label for="title">Internship Title *</label>
                            <input id="title" type="text" name="title" maxlength="255" value="<?= htmlspecialchars($_POST['title'] ?? '') ?>" placeholder="e.g. Software Engineering Intern" required>
                        </div>
                        <div class="form-group">
                            <label for="industry">Industry Category *</label>
                            <select id="industry" name="industry" required>
                                <option value="">Select Industry Category</option>
                                <option value="Information Technology">Information Technology</option>
                                <option value="Software Development">Software Development</option>
                                <option value="Data Science & AI">Data Science & AI</option>
                                <option value="Cyber Security">Cyber Security</option>
                                <option value="Business & Management">Business & Management</option>
                                <option value="Finance & Accounting">Finance & Accounting</option>
                                <option value="Marketing">Marketing</option>
                                <option value="Engineering">Engineering</option>
                                <option value="Design / UI-UX">Design / UI-UX</option>
                                <option value="Human Resources">Human Resources</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="cover_image">Cover Image</label>
                            <input id="cover_image" type="file" name="cover_image" accept=".jpg,.jpeg,.png,.webp,image/*">
                            <small>JPG, PNG or WEBP. Maximum 10MB.</small>
                        </div>
                        <div class="form-group full">
                            <label for="description">Internship Description *</label>
                            <textarea id="description" name="description" rows="5" placeholder="Describe the internship role, opportunity and main purpose..." required><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                        </div>
                    </div>
                </div>

                <div class="internship-form-section">
                    <h3>Requirements</h3>
                    <div class="form-grid">
                        <div class="form-group full">
                            <label for="tech_tags">Required Skills / Technologies *</label>
                            <input id="tech_tags" type="text" name="tech_tags" maxlength="255" value="<?= htmlspecialchars($_POST['tech_tags'] ?? '') ?>" placeholder="e.g. PHP, MySQL, JavaScript" required>
                            <small>Separate skills using commas.</small>
                        </div>
                        <div class="form-group">
                            <label for="academic_year">Preferred Academic Year</label>
                            <select id="academic_year" name="academic_year">
                                <option value="">Any Year</option><option>1st Year</option><option>2nd Year</option><option>3rd Year</option><option>4th Year</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="experience_level">Experience Level</label>
                            <select id="experience_level" name="experience_level">
                                <option value="">Select Level</option><option>Beginner</option><option>Intermediate</option><option>Advanced</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="vacancies">Number of Vacancies *</label>
                            <input id="vacancies" type="number" name="vacancies" min="1" max="999" value="<?= htmlspecialchars($_POST['vacancies'] ?? '1') ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="duration">Duration *</label>
                            <input id="duration" type="text" name="duration" maxlength="50" value="<?= htmlspecialchars($_POST['duration'] ?? '') ?>" placeholder="e.g. 6 Months" required>
                        </div>
                    </div>
                </div>

                <div class="internship-form-section">
                    <h3>Work Details</h3>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="internship_type">Internship Type</label>
                            <select id="internship_type" name="internship_type"><option value="">Select Type</option><option>Full-time</option><option>Part-time</option></select>
                        </div>
                        <div class="form-group">
                            <label for="work_mode">Work Mode</label>
                            <select id="work_mode" name="work_mode"><option value="">Select Work Mode</option><option>On-site</option><option>Remote</option><option>Hybrid</option></select>
                        </div>
                        <div class="form-group full">
                            <label for="location">Location</label>
                            <input id="location" type="text" name="location" maxlength="255" value="<?= htmlspecialchars($_POST['location'] ?? '') ?>" placeholder="e.g. Colombo, Sri Lanka">
                        </div>
                        <div class="form-group">
                            <label for="start_date">Start Date</label>
                            <input id="start_date" type="date" name="start_date" value="<?= htmlspecialchars($_POST['start_date'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label for="deadline">Application Deadline *</label>
                            <input id="deadline" type="date" name="deadline" value="<?= htmlspecialchars($_POST['deadline'] ?? '') ?>" required>
                        </div>
                    </div>
                </div>

                <div class="internship-form-section">
                    <h3>Additional Information</h3>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="paid_status">Allowance / Stipend</label>
                            <select id="paid_status" name="paid_status"><option>Unpaid</option><option>Paid</option></select>
                        </div>
                        <div class="form-group">
                            <label for="stipend">Monthly Allowance (LKR)</label>
                            <input id="stipend" type="number" name="stipend" min="0" step="0.01" value="<?= htmlspecialchars($_POST['stipend'] ?? '') ?>" placeholder="e.g. 25000">
                        </div>
                        <div class="form-group full">
                            <label for="responsibilities">Responsibilities</label>
                            <textarea id="responsibilities" name="responsibilities" rows="4" placeholder="Describe the main responsibilities..."><?= htmlspecialchars($_POST['responsibilities'] ?? '') ?></textarea>
                        </div>
                        <div class="form-group full">
                            <label for="benefits">Learning Opportunities / Benefits</label>
                            <textarea id="benefits" name="benefits" rows="4" placeholder="Describe learning opportunities and benefits..."><?= htmlspecialchars($_POST['benefits'] ?? '') ?></textarea>
                        </div>
                        <div class="form-group full">
                            <label for="supporting_document">Supporting Document</label>
                            <input id="supporting_document" type="file" name="supporting_document" accept=".pdf,.doc,.docx">
                            <small>PDF, DOC or DOCX. Maximum 10MB.</small>
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="button" class="secondary-btn" data-modal-close>Cancel</button>
                    <button type="submit" class="primary-btn"><span class="material-symbols-outlined" aria-hidden="true">publish</span>Publish Internship</button>
                </div>
            </form>
        </div>
    </section>
</div>

<!-- =========================
     EDIT INTERNSHIP MODAL
========================== -->

<div class="internship-modal" id="editInternshipModal" role="dialog" aria-modal="true"
    aria-labelledby="editInternshipModalTitle" aria-hidden="<?= $updateError !== '' ? 'false' : 'true' ?>"
    <?= $updateError !== '' ? '' : 'hidden' ?>>
    <div class="internship-modal__backdrop" data-edit-modal-close></div>

    <section class="internship-modal__panel" role="document">
        <div class="internship-modal__header">
            <div><p class="page-label">RECRUITMENT</p><h2 id="editInternshipModalTitle">Edit Internship</h2><p>Update the internship opportunity details.</p></div>
            <button type="button" class="internship-modal__close" data-edit-modal-close aria-label="Close edit internship form"><span class="material-symbols-outlined">close</span></button>
        </div>

        <div class="internship-modal__body">
            <?php if ($updateError !== ''): ?><div class="form-error" role="alert"><?= htmlspecialchars($updateError) ?></div><?php endif; ?>

            <form method="POST" enctype="multipart/form-data" id="editInternshipForm" autocomplete="off">
                <input type="hidden" name="update_internship" value="1">
                <input type="hidden" name="internship_id" id="edit_internship_id" value="<?= (int) ($updateFormData['id'] ?? 0) ?>">

                <div class="internship-form-section"><h3>Basic Information</h3><div class="form-grid">
                    <div class="form-group full"><label for="edit_title">Internship Title *</label><input id="edit_title" type="text" name="title" maxlength="255" value="<?= htmlspecialchars($updateFormData['title'] ?? '') ?>" required></div>
                    <div class="form-group"><label for="edit_industry">Industry Category *</label><select id="edit_industry" name="industry" required><option value="">Select Industry Category</option>
                                <option value="Information Technology">Information Technology</option>
                                <option value="Software Development">Software Development</option>
                                <option value="Data Science & AI">Data Science & AI</option>
                                <option value="Cyber Security">Cyber Security</option>
                                <option value="Business & Management">Business & Management</option>
                                <option value="Finance & Accounting">Finance & Accounting</option>
                                <option value="Marketing">Marketing</option>
                                <option value="Engineering">Engineering</option>
                                <option value="Design / UI-UX">Design / UI-UX</option>
                                <option value="Human Resources">Human Resources</option>
                                <option value="Other">Other</option></select></div>
                    <div class="form-group"><label for="edit_cover_image">Replace Cover Image</label><input id="edit_cover_image" type="file" name="cover_image" accept=".jpg,.jpeg,.png,.webp,image/*"><small>Leave empty to keep the current image.</small></div>
                    <div class="form-group full"><label for="edit_description">Internship Description</label><textarea id="edit_description" name="description" rows="5"><?= htmlspecialchars($updateFormData['description'] ?? '') ?></textarea></div>
                </div></div>

                <div class="internship-form-section"><h3>Requirements</h3><div class="form-grid">
                    <div class="form-group full"><label for="edit_tech_tags">Required Skills / Technologies</label><input id="edit_tech_tags" type="text" name="tech_tags" maxlength="255" value="<?= htmlspecialchars($updateFormData['tech_tags'] ?? '') ?>"></div>
                    <div class="form-group"><label for="edit_academic_year">Preferred Academic Year</label><select id="edit_academic_year" name="academic_year"><option value="">Any Year</option><option>1st Year</option><option>2nd Year</option><option>3rd Year</option><option>4th Year</option></select></div>
                    <div class="form-group"><label for="edit_experience_level">Experience Level</label><select id="edit_experience_level" name="experience_level"><option value="">Select Level</option><option>Beginner</option><option>Intermediate</option><option>Advanced</option></select></div>
                    <div class="form-group"><label for="edit_vacancies">Number of Vacancies</label><input id="edit_vacancies" type="number" name="vacancies" min="1" max="999" value="<?= htmlspecialchars($updateFormData['vacancies'] ?? '1') ?>"></div>
                    <div class="form-group"><label for="edit_duration">Duration *</label><input id="edit_duration" type="text" name="duration" maxlength="50" value="<?= htmlspecialchars($updateFormData['duration'] ?? '') ?>" required></div>
                </div></div>

                <div class="internship-form-section"><h3>Work Details</h3><div class="form-grid">
                    <div class="form-group"><label for="edit_internship_type">Internship Type</label><select id="edit_internship_type" name="internship_type"><option value="">Select Type</option><option>Full-time</option><option>Part-time</option></select></div>
                    <div class="form-group"><label for="edit_work_mode">Work Mode</label><select id="edit_work_mode" name="work_mode"><option value="">Select Work Mode</option><option>On-site</option><option>Remote</option><option>Hybrid</option></select></div>
                    <div class="form-group full"><label for="edit_location">Location</label><input id="edit_location" type="text" name="location" maxlength="255" value="<?= htmlspecialchars($updateFormData['location'] ?? '') ?>"></div>
                    <div class="form-group"><label for="edit_start_date">Start Date</label><input id="edit_start_date" type="date" name="start_date" value="<?= htmlspecialchars($updateFormData['start_date'] ?? '') ?>"></div>
                    <div class="form-group"><label for="edit_deadline">Application Deadline *</label><input id="edit_deadline" type="date" name="deadline" value="<?= htmlspecialchars($updateFormData['deadline'] ?? '') ?>" required></div>
                </div></div>

                <div class="internship-form-section"><h3>Additional Information</h3><div class="form-grid">
                    <div class="form-group"><label for="edit_paid_status">Allowance / Stipend</label><select id="edit_paid_status" name="paid_status"><option>Unpaid</option><option>Paid</option></select></div>
                    <div class="form-group"><label for="edit_stipend">Monthly Allowance (LKR)</label><input id="edit_stipend" type="number" name="stipend" min="0" step="0.01" value="<?= htmlspecialchars($updateFormData['stipend'] ?? '') ?>"></div>
                    <div class="form-group full"><label for="edit_responsibilities">Responsibilities</label><textarea id="edit_responsibilities" name="responsibilities" rows="4"><?= htmlspecialchars($updateFormData['responsibilities'] ?? '') ?></textarea></div>
                    <div class="form-group full"><label for="edit_benefits">Learning Opportunities / Benefits</label><textarea id="edit_benefits" name="benefits" rows="4"><?= htmlspecialchars($updateFormData['benefits'] ?? '') ?></textarea></div>
                    <div class="form-group full"><label for="edit_supporting_document">Replace Supporting Document</label><input id="edit_supporting_document" type="file" name="supporting_document" accept=".pdf,.doc,.docx"><small>Leave empty to keep the current document.</small></div>
                </div></div>

                <div class="internship-edit-status" id="editStatusGroup" <?= in_array(ucfirst(strtolower($updateFormData['status'] ?? '')), ['Active', 'Closed', 'Suspended'], true) ? '' : 'hidden' ?>><label for="edit_status">Status</label><select id="edit_status" name="status" <?= in_array(ucfirst(strtolower($updateFormData['status'] ?? '')), ['Active', 'Closed'], true) ? '' : 'hidden disabled' ?>><option value="Active" <?= ucfirst(strtolower($updateFormData['status'] ?? '')) === 'Active' ? 'selected' : '' ?>>Active</option><option value="Closed" <?= ucfirst(strtolower($updateFormData['status'] ?? '')) === 'Closed' ? 'selected' : '' ?>>Closed</option></select><input id="edit_status_readonly" type="text" value="Suspended" readonly disabled <?= ucfirst(strtolower($updateFormData['status'] ?? '')) === 'Suspended' ? '' : 'hidden' ?>></div>
                <div class="form-actions"><button type="button" class="secondary-btn" data-edit-modal-close>Cancel</button><button type="submit" class="primary-btn">Save Changes</button></div>
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

\>

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







<div class="admin-reason-modal" id="adminReasonModal" role="dialog" aria-modal="true" aria-labelledby="adminReasonTitle" aria-hidden="true" hidden>
    <div class="admin-reason-modal__backdrop" data-reason-close></div>
    <section class="admin-reason-modal__panel" role="document">
        <h2 id="adminReasonTitle">Internship Suspended</h2>
        <p class="admin-reason-modal__reason" id="adminReasonText"></p>
        <div class="admin-reason-modal__actions"><button type="button" class="secondary-btn" data-reason-close>Close</button><button type="button" class="primary-btn" id="fixSuspendedInternship">Fix Internship</button></div>
    </section>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-edit-internship]').forEach(function (button) {
        button.addEventListener('click', function () {
            try {
                var data = JSON.parse(button.getAttribute('data-edit-internship') || '{}');
                ['description','academic_year','experience_level','vacancies','internship_type','work_mode','location','start_date','paid_status','stipend','responsibilities','benefits']
                    .forEach(function (field) {
                        var el = document.getElementById('edit_' + field);
                        if (el) el.value = data[field] ?? '';
                    });
            } catch (e) { console.error(e); }
        });
    });

    var paid = document.getElementById('paid_status');
    var stipend = document.getElementById('stipend');
    function syncStipend() {
        if (!paid || !stipend) return;
        stipend.disabled = paid.value !== 'Paid';
        if (stipend.disabled) stipend.value = '';
    }
    if (paid) { paid.addEventListener('change', syncStipend); syncStipend(); }
});
</script>

<!-- =========================

     INTERNSHIP JAVASCRIPT

========================== -->



<script src="../../../Assets/JS/Company/internships.js?v=<?= filemtime(__DIR__ . '/../../../Assets/JS/Company/internships.js') ?>"></script>









<?php



include "../../../Includes/dash_footer.php";



?>
