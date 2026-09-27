<?php

include "../../../Config/db.php";
include "../../../Session/Session.php";
require_once "../../../Backend/CompanyBackend.php";

require_role('company');

$user = current_user();
$companyEmail = $user['email'] ?? $user['Email'] ?? '';

$companyManager = new CompanyManager($conn);
$company = $companyManager->getCompany($companyEmail);

if (!$company) {
    die('Company profile not found.');
}

/*
|--------------------------------------------------------------------------
| PAGE CSS
|--------------------------------------------------------------------------
*/
$extra_css =
    '<link rel="stylesheet" href="../../../Assets/CSS/Company/interviews.css?v=' . time() . '">';

/*
|--------------------------------------------------------------------------
| EXISTING SHARED COMPANY LAYOUT
|--------------------------------------------------------------------------
| Do not recreate the header/search bar/sidebar here.
*/
include "../../../Includes/company_sidebar.php";
include "../../../Includes/dash_header.php";
?>

<main class="content">

    <div class="company-interviews-page">

        <!-- =====================================================
             PAGE HEADER
        ====================================================== -->

        <section class="interviews-page-header">

            <div class="interviews-title">

                <h1>Interview Management</h1>

                <p>
                    Track and coordinate your upcoming talent assessment pipelines.
                </p>

            </div>

        </section>


        <!-- =====================================================
             SUMMARY CARDS
        ====================================================== -->

        <section class="interview-summary-grid">

            <!-- TODAY -->

            <article class="interview-summary-card today-card">

                <span class="summary-label">
                    TODAY'S SCHEDULE
                </span>

                <strong class="summary-number">
                    08
                </strong>

                <p class="summary-note">

                    <span class="summary-positive">
                        +2
                    </span>

                    from yesterday

                </p>

            </article>


            <!-- HIRED -->

            <article class="interview-summary-card hired-card">

                <span class="summary-label">
                    HIRED
                </span>

                <strong class="summary-number" id="hiredInterviewCount">0</strong>

                <p class="summary-note">
                    Candidates hired after interview
                </p>

            </article>


            <!-- UPCOMING -->

            <article class="interview-summary-card upcoming-card">

                <span class="summary-label">
                    UPCOMING THIS WEEK
                </span>

                <strong class="summary-number">
                    32
                </strong>

                <div class="summary-progress">
                    <span></span>
                </div>

            </article>

        </section>


        <!-- =====================================================
             UPCOMING INTERVIEWS
        ====================================================== -->

        <section class="interviews-table-card">

            <div class="interviews-table-header">

                <h2>
                    Upcoming Interviews
                </h2>

                <div class="table-header-actions">

                    <button
                        type="button"
                        class="table-icon-btn"
                        id="filterInterviewsBtn"
                        title="Filter">

                        <span class="material-symbols-outlined">
                            filter_list
                        </span>

                    </button>

                    <button
                        type="button"
                        class="table-icon-btn"
                        id="downloadInterviewsBtn"
                        title="Download">

                        <span class="material-symbols-outlined">
                            download
                        </span>

                    </button>

                </div>

            </div>


            <div class="interviews-table-wrapper">

                <table class="interviews-table">

                    <thead>

                        <tr>
                            <th>STUDENT</th>
                            <th>INTERNSHIP</th>
                            <th>DATE &amp; TIME</th>
                            <th>STATUS</th>
                            <th>ACTIONS</th>
                        </tr>

                    </thead>

                    <tbody>

                        <!-- =================================================
                             INTERVIEW 1
                        ================================================== -->

                        <tr
                            class="interview-row"
                            data-student="Alex Rivera"
                            data-course="Computer Science Senior"
                            data-internship="Software Engineering Intern"
                            data-team="Backend & Devops Team"
                            data-date="Oct 24, 2026"
                            data-time="10:30 AM - 11:30 AM"
                            data-status="Interviewing">

                            <td>

                                <div class="student-info">

                                    <div class="student-avatar avatar-one">
                                        AR
                                        <span class="online-dot"></span>
                                    </div>

                                    <div>

                                        <strong>
                                            Alex Rivera
                                        </strong>

                                        <small>
                                            Computer Science Senior
                                        </small>

                                    </div>

                                </div>

                            </td>


                            <td>

                                <div class="internship-info">

                                    <strong>
                                        Software Engineering Intern
                                    </strong>

                                    <small>
                                        Backend &amp; Devops Team
                                    </small>

                                </div>

                            </td>


                            <td>

                                <div class="date-info">

                                    <strong>
                                        Oct 24, 2026
                                    </strong>

                                    <small>

                                        <span class="material-symbols-outlined">
                                            schedule
                                        </span>

                                        10:30 AM - 11:30 AM

                                    </small>

                                </div>

                            </td>


                            <td>

                                <span class="interview-status interviewing">
                                    Interviewing
                                </span>

                            </td>


                            <td>

                                <div class="interview-actions">

                                    <button
                                        type="button"
                                        class="interview-action-btn schedule-row-interview-btn"
                                        title="Schedule Interview">

                                        <span class="material-symbols-outlined">
                                            event
                                        </span>

                                    </button>

                                    <button
                                        type="button"
                                        class="interview-action-btn view-interview-btn"
                                        title="View Interview">

                                        <span class="material-symbols-outlined">
                                            visibility
                                        </span>

                                    </button>


                                    <button
                                        type="button"
                                        class="interview-action-btn edit-interview-btn"
                                        title="Edit Interview">

                                        <span class="material-symbols-outlined">
                                            edit
                                        </span>

                                    </button>

                                </div>

                            </td>

                        </tr>


                        <!-- =================================================
                             INTERVIEW 2
                        ================================================== -->

                        <tr
                            class="interview-row"
                            data-student="Elena Sorova"
                            data-course="UX/UI Design Intern"
                            data-internship="Product Design Fellowship"
                            data-team="Mobile Design Squad"
                            data-date="Oct 25, 2026"
                            data-time="02:00 PM - 02:45 PM"
                            data-status="Interviewing">

                            <td>

                                <div class="student-info">

                                    <div class="student-avatar avatar-two">
                                        ES
                                        <span class="offline-dot"></span>
                                    </div>

                                    <div>

                                        <strong>
                                            Elena Sorova
                                        </strong>

                                        <small>
                                            UX/UI Design Intern
                                        </small>

                                    </div>

                                </div>

                            </td>


                            <td>

                                <div class="internship-info">

                                    <strong>
                                        Product Design Fellowship
                                    </strong>

                                    <small>
                                        Mobile Design Squad
                                    </small>

                                </div>

                            </td>


                            <td>

                                <div class="date-info">

                                    <strong>
                                        Oct 25, 2026
                                    </strong>

                                    <small>

                                        <span class="material-symbols-outlined">
                                            schedule
                                        </span>

                                        02:00 PM - 02:45 PM

                                    </small>

                                </div>

                            </td>


                            <td>

                                <span class="interview-status interviewing">
                                    Interviewing
                                </span>

                            </td>


                            <td>

                                <div class="interview-actions">

                                    <button
                                        type="button"
                                        class="interview-action-btn schedule-row-interview-btn"
                                        title="Schedule Interview">

                                        <span class="material-symbols-outlined">
                                            event
                                        </span>

                                    </button>

                                    <button
                                        type="button"
                                        class="interview-action-btn view-interview-btn"
                                        title="View Interview">

                                        <span class="material-symbols-outlined">
                                            visibility
                                        </span>

                                    </button>


                                    <button
                                        type="button"
                                        class="interview-action-btn edit-interview-btn"
                                        title="Edit Interview">

                                        <span class="material-symbols-outlined">
                                            edit
                                        </span>

                                    </button>

                                </div>

                            </td>

                        </tr>


                        <!-- =================================================
                             INTERVIEW 3
                        ================================================== -->

                        <tr
                            class="interview-row"
                            data-student="Jordan Smith"
                            data-course="Business Analytics"
                            data-internship="Data Analyst Intern"
                            data-team="Market Intelligence"
                            data-date="Oct 26, 2026"
                            data-time="11:00 AM - 12:00 PM"
                            data-status="Hired">

                            <td>

                                <div class="student-info">

                                    <div class="student-avatar avatar-three">
                                        JS
                                        <span class="online-dot"></span>
                                    </div>

                                    <div>

                                        <strong>
                                            Jordan Smith
                                        </strong>

                                        <small>
                                            Business Analytics
                                        </small>

                                    </div>

                                </div>

                            </td>


                            <td>

                                <div class="internship-info">

                                    <strong>
                                        Data Analyst Intern
                                    </strong>

                                    <small>
                                        Market Intelligence
                                    </small>

                                </div>

                            </td>


                            <td>

                                <div class="date-info">

                                    <strong>
                                        Oct 26, 2026
                                    </strong>

                                    <small>

                                        <span class="material-symbols-outlined">
                                            schedule
                                        </span>

                                        11:00 AM - 12:00 PM

                                    </small>

                                </div>

                            </td>


                            <td>

                                <span class="interview-status hired">
                                    Hired
                                </span>

                            </td>


                            <td>

                                <div class="interview-actions">

                                    <button
                                        type="button"
                                        class="interview-action-btn schedule-row-interview-btn"
                                        title="Schedule Interview">

                                        <span class="material-symbols-outlined">
                                            event
                                        </span>

                                    </button>

                                    <button
                                        type="button"
                                        class="interview-action-btn view-interview-btn"
                                        title="View Interview">

                                        <span class="material-symbols-outlined">
                                            visibility
                                        </span>

                                    </button>


                                    <button
                                        type="button"
                                        class="interview-action-btn edit-interview-btn"
                                        title="Edit Interview">

                                        <span class="material-symbols-outlined">
                                            edit
                                        </span>

                                    </button>

                                </div>

                            </td>

                        </tr>

                    </tbody>

                </table>

            </div>


            <!-- =================================================
                 PAGINATION
            ================================================== -->

            <div class="interviews-pagination">

                <p>
                    Showing 1-3 of 42 interviews
                </p>

                <div>

                    <button
                        type="button"
                        class="pagination-btn"
                        id="previousInterviewPage">
                        Previous
                    </button>

                    <button
                        type="button"
                        class="pagination-btn"
                        id="nextInterviewPage">
                        Next
                    </button>

                </div>

            </div>

        </section>

    </div>

    <?php include "../../../Includes/company_dashboard_footer.php"; ?>
</main>



<!-- =========================================================
     SCHEDULE INTERVIEW MODAL
========================================================= -->

<div class="interview-modal-overlay" id="scheduleInterviewModal">

    <div class="interview-modal">

        <div class="interview-modal-header">

            <div>

                <span class="modal-label">
                    INTERVIEW MANAGEMENT
                </span>

                <h2>
                    Schedule Interview
                </h2>

                <p>
                    Add a new interview to your recruitment schedule.
                </p>

            </div>

            <button
                type="button"
                class="modal-close-btn"
                data-close-modal>

                <span class="material-symbols-outlined">
                    close
                </span>

            </button>

        </div>


        <form id="scheduleInterviewForm">

            <div class="modal-form-grid">

                <div class="form-group full-width">

                    <label for="scheduleCandidate">
                        Candidate
                    </label>

                    <input type="text" id="scheduleCandidate" readonly>

                </div>

                <div class="form-group full-width">

                    <label for="scheduleInternship">
                        Internship
                    </label>

                    <input type="text" id="scheduleInternship" readonly>

                </div>


                <div class="form-group">

                    <label for="scheduleDate">
                        Date
                    </label>

                    <input
                        type="date"
                        id="scheduleDate"
                        required>

                </div>


                <div class="form-group">

                    <label for="scheduleTime">
                        Time
                    </label>

                    <input
                        type="time"
                        id="scheduleTime"
                        required>

                </div>


                <div class="form-group full-width">

                    <label for="scheduleType">
                        Interview Type
                    </label>

                    <select id="scheduleType">

                        <option>
                            Video Interview
                        </option>

                        <option>
                            In-Person Interview
                        </option>

                        <option>
                            Phone Interview
                        </option>

                    </select>

                </div>


                <div class="form-group full-width">

                    <label for="scheduleNotes">
                        Notes
                    </label>

                    <textarea
                        id="scheduleNotes"
                        rows="4"
                        placeholder="Add interview notes..."></textarea>

                </div>

            </div>


            <div class="modal-footer">

                <button
                    type="button"
                    class="modal-cancel-btn"
                    data-close-modal>

                    Cancel

                </button>

                <button
                    type="submit"
                    class="modal-primary-btn">

                    Schedule Interview

                </button>

            </div>

        </form>

    </div>

</div>



<!-- =========================================================
     VIEW INTERVIEW MODAL
========================================================= -->

<div class="interview-modal-overlay" id="viewInterviewModal">

    <div class="interview-modal small-modal">

        <div class="interview-modal-header">

            <div>

                <span class="modal-label">
                    INTERVIEW DETAILS
                </span>

                <h2 id="viewStudentName">
                    Candidate
                </h2>

            </div>

            <button
                type="button"
                class="modal-close-btn"
                data-close-modal>

                <span class="material-symbols-outlined">
                    close
                </span>

            </button>

        </div>


        <div class="interview-details">

            <div class="detail-item">

                <span>
                    Internship
                </span>

                <strong id="viewInternship">
                    -
                </strong>

            </div>


            <div class="detail-item">

                <span>
                    Team
                </span>

                <strong id="viewTeam">
                    -
                </strong>

            </div>


            <div class="detail-item">

                <span>
                    Date
                </span>

                <strong id="viewDate">
                    -
                </strong>

            </div>


            <div class="detail-item">

                <span>
                    Time
                </span>

                <strong id="viewTime">
                    -
                </strong>

            </div>


            <div class="detail-item">

                <span>
                    Status
                </span>

                <strong id="viewStatus">
                    -
                </strong>

            </div>

            <div class="detail-item" id="viewDisqualificationReasonItem" hidden>
                <span>Disqualification Reason</span>
                <strong id="viewDisqualificationReason">-</strong>
            </div>

        </div>


        <div class="modal-footer">

            <button
                type="button"
                class="modal-primary-btn"
                data-close-modal>

                Close

            </button>

        </div>

    </div>

</div>



<!-- =========================================================
     EDIT INTERVIEW MODAL
========================================================= -->

<div class="interview-modal-overlay" id="editInterviewModal">

    <div class="interview-modal">

        <div class="interview-modal-header">

            <div>

                <span class="modal-label">
                    INTERVIEW MANAGEMENT
                </span>

                <h2>
                    Edit Interview
                </h2>

                <p>
                    Update the selected interview details.
                </p>

            </div>

            <button
                type="button"
                class="modal-close-btn"
                data-close-modal>

                <span class="material-symbols-outlined">
                    close
                </span>

            </button>

        </div>


        <form id="editInterviewForm">

            <div class="modal-form-grid">

                <div class="form-group full-width">

                    <label for="editCandidate">
                        Candidate
                    </label>

                    <input
                        type="text"
                        id="editCandidate"
                        readonly>

                </div>


                <div class="form-group full-width">

                    <label for="editInternship">
                        Internship
                    </label>

                    <input
                        type="text"
                        id="editInternship">

                </div>


                <div class="form-group">

                    <label for="editDate">
                        Date
                    </label>

                    <input
                        type="text"
                        id="editDate">

                </div>


                <div class="form-group">

                    <label for="editTime">
                        Time
                    </label>

                    <input
                        type="text"
                        id="editTime">

                </div>


                <div class="form-group full-width">

                    <label for="editStatus">
                        Status
                    </label>

                    <select id="editStatus">

                        <option value="Interviewing">
                            Interviewing
                        </option>

                        <option value="Hired">
                            Hired
                        </option>

                        <option value="Disqualified">
                            Disqualified
                        </option>

                    </select>

                </div>

            </div>


            <div class="modal-footer">

                <button
                    type="button"
                    class="modal-cancel-btn"
                    data-close-modal>

                    Cancel

                </button>

                <button
                    type="submit"
                    class="modal-primary-btn">

                    Save Changes

                </button>

            </div>

        </form>

    </div>

</div>

<div class="interview-modal-overlay" id="disqualifyInterviewModal">
    <div class="interview-modal small-modal">
        <div class="interview-modal-header">
            <div>
                <span class="modal-label">INTERVIEW OUTCOME</span>
                <h2>Disqualify Candidate</h2>
                <p>Select a reason before recording this interview outcome.</p>
            </div>
            <button type="button" class="modal-close-btn" data-close-modal aria-label="Close">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <form id="disqualifyInterviewForm">
            <div class="modal-form-grid">
                <div class="form-group full-width">
                    <label for="disqualificationReason">Disqualification Reason</label>
                    <select id="disqualificationReason" required>
                        <option value="">Select a reason</option>
                        <option>Technical skills insufficient</option>
                        <option>Interview performance</option>
                        <option>Availability mismatch</option>
                        <option>Not suitable for the role</option>
                        <option>Other</option>
                    </select>
                </div>
                <div class="form-group full-width" id="otherDisqualificationGroup" hidden>
                    <label for="otherDisqualificationNote">Short note</label>
                    <textarea id="otherDisqualificationNote" maxlength="240" placeholder="Add a short reason..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="modal-cancel-btn" data-close-modal>Cancel</button>
                <button type="submit" class="modal-primary-btn disqualify-confirm-btn">Disqualify Candidate</button>
            </div>
        </form>
    </div>
</div>


<script src="../../../Assets/JS/Company/interviews.js?v=<?php echo time(); ?>"></script>

<?php
include "../../../Includes/dash_footer.php";
?>
