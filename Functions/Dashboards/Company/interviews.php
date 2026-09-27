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
                <span class="summary-label">Today's Schedule</span>
                <strong class="summary-number">08</strong>
                <p class="summary-note">
                    <span class="summary-positive">+2</span> from yesterday
                </p>
            </article>

            <!-- TOTAL INTERVIEWING -->
            <article class="interview-summary-card interviewing-card">
                <span class="summary-label">Total Interviewing</span>
                <strong class="summary-number" id="totalInterviewingCount">2</strong>
                <p class="summary-note">
                    <span class="summary-warning">Active</span> in pipeline
                </p>
            </article>

            <!-- HIRED -->
            <article class="interview-summary-card hired-card">
                <span class="summary-label">Hired</span>
                <strong class="summary-number" id="hiredInterviewCount">1</strong>
                <p class="summary-note">
                    <span class="summary-positive">Selected</span> offered
                </p>
            </article>

            <!-- REJECTED / DISQUALIFIED -->
            <article class="interview-summary-card rejected-card">
                <span class="summary-label">Rejected</span>
                <strong class="summary-number" id="rejectedInterviewCount">0</strong>
                <p class="summary-note">
                    <span class="summary-negative">Disqualified</span> candidates
                </p>
            </article>

        </section>


        <!-- =====================================================
             STANDALONE SEARCH & FILTER TOOLBAR (MATCHING INTERNSHIPS)
        ====================================================== -->
        <section class="interviews-toolbar">
            <div class="interviews-search">
                <span class="material-symbols-outlined">search</span>
                <input
                    type="text"
                    id="interviewSearchInput"
                    placeholder="Search interviews..."
                    autocomplete="off">
            </div>

            <div class="interviews-filters">
                <select id="interviewStatusFilter">
                    <option value="all">All Status</option>
                    <option value="Interviewing">Interviewing</option>
                    <option value="Hired">Hired</option>
                    <option value="Disqualified">Disqualified</option>
                </select>
            </div>
        </section>

        <!-- =====================================================
             UPCOMING INTERVIEWS (SEPARATE TABLE CARD)
        ====================================================== -->
        <section class="interviews-table-card">
            <div class="interviews-table-heading">
                <h2>Upcoming Interviews</h2>
                <p>Manage all candidate interviews scheduled by your company.</p>
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
                                        class="interview-action-btn view-interview-btn"
                                        title="View Interview">

                                        <span class="material-symbols-outlined">
                                            visibility
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
                                        class="interview-action-btn view-interview-btn"
                                        title="View Interview">

                                        <span class="material-symbols-outlined">
                                            visibility
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
                                        class="interview-action-btn view-interview-btn"
                                        title="View Interview">

                                        <span class="material-symbols-outlined">
                                            visibility
                                        </span>

                                    </button>


                                </div>

                            </td>

                        <tr id="noInterviewResultsRow" hidden>
                            <td colspan="5" class="empty-results-cell">
                                <div class="empty-results-content">
                                    <span class="material-symbols-outlined">search_off</span>
                                    <p>No interviews found matching your search and filter criteria.</p>
                                    <button type="button" id="resetInterviewFiltersBtn" class="reset-filter-btn">
                                        <span class="material-symbols-outlined">restart_alt</span>
                                        Reset Filters
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

                <p id="interviewsCountText">
                    Showing <span id="visibleInterviewCount">3</span> of <span id="totalInterviewCount">3</span> interviews
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

<div class="interview-modal-overlay" id="interviewDecisionModal">
    <div class="interview-modal" style="max-width: 540px;">
        <div class="interview-modal-header">
            <div>
                <span class="modal-label" style="color: #16854a;">INTERVIEW OUTCOME DECISION</span>
                <h2 id="decisionModalCandidate">Candidate Decision</h2>
                <p id="decisionModalInternship">Record the final interview result for this candidate.</p>
            </div>
            <button type="button" class="modal-close-btn" data-close-modal aria-label="Close">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <form id="interviewDecisionForm">
            <div style="padding: 20px 24px; display: flex; flex-direction: column; gap: 16px;">
                <!-- DECISION SELECTION CARDS -->
                <div>
                    <label style="font-size: 12px; font-weight: 700; color: #213a55; display: block; margin-bottom: 8px;">
                        Select Interview Result <span style="color: #e53e3e;">*</span>
                    </label>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                        <!-- HIRE CARD -->
                        <label class="decision-choice-card active" id="cardHireChoice" style="border: 2px solid #16854a; background: #f0fdf4; border-radius: 10px; padding: 12px; cursor: pointer; display: flex; align-items: center; gap: 10px; transition: all 0.2s ease;">
                            <input type="radio" name="decision_choice" value="Hired" id="choiceHired" checked style="accent-color: #16854a; width: 16px; height: 16px;">
                            <div style="display: flex; flex-direction: column;">
                                <strong style="color: #15803d; font-size: 13.5px; display: flex; align-items: center; gap: 4px;">
                                    <span class="material-symbols-outlined" style="font-size: 18px;">how_to_reg</span> Hire
                                </strong>
                                <small style="color: #475569; font-size: 11px;">Offer role to student</small>
                            </div>
                        </label>

                        <!-- DISQUALIFY CARD -->
                        <label class="decision-choice-card" id="cardDisqualifyChoice" style="border: 2px solid #e2e8f0; background: #ffffff; border-radius: 10px; padding: 12px; cursor: pointer; display: flex; align-items: center; gap: 10px; transition: all 0.2s ease;">
                            <input type="radio" name="decision_choice" value="Disqualified" id="choiceDisqualified" style="accent-color: #dc2626; width: 16px; height: 16px;">
                            <div style="display: flex; flex-direction: column;">
                                <strong style="color: #dc2626; font-size: 13.5px; display: flex; align-items: center; gap: 4px;">
                                    <span class="material-symbols-outlined" style="font-size: 18px;">person_off</span> Disqualify
                                </strong>
                                <small style="color: #64748b; font-size: 11px;">Candidate not selected</small>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- HIRE SECTION -->
                <div id="hireFieldsSection" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 14px; display: flex; flex-direction: column; gap: 12px;">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                        <div class="form-group">
                            <label for="hireStartDate" style="color: #213a55; font-size: 12px; font-weight: 700;">Proposed Start Date</label>
                            <input type="date" id="hireStartDate" min="<?= date('Y-m-d') ?>" style="border: 1px solid #d4dbe5; border-radius: 7px; padding: 8px 10px; font-size: 13px;">
                        </div>
                        <div class="form-group">
                            <label for="hireStipend" style="color: #213a55; font-size: 12px; font-weight: 700;">Monthly Allowance / Stipend (Optional)</label>
                            <input type="text" id="hireStipend" placeholder="e.g. LKR 45,000" style="border: 1px solid #d4dbe5; border-radius: 7px; padding: 8px 10px; font-size: 13px;">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="hireNote" style="color: #213a55; font-size: 12px; font-weight: 700;">Offer Instructions / Welcome Note (Optional)</label>
                        <textarea id="hireNote" rows="2" placeholder="e.g. Congratulations! Please submit your university clearance documents..." style="border: 1px solid #d4dbe5; border-radius: 7px; padding: 8px 10px; font-size: 13px;"></textarea>
                    </div>
                </div>

                <!-- DISQUALIFY SECTION -->
                <div id="disqualifyFieldsSection" hidden style="display: none; background: #fff5f5; border: 1px solid #fed7d7; border-radius: 10px; padding: 14px; flex-direction: column; gap: 12px;">
                    <div class="form-group">
                        <label for="disqualificationCategory" style="color: #9b2c2c; font-size: 12px; font-weight: 700;">
                            Primary Reason <span style="color: #e53e3e;">*</span>
                        </label>
                        <select id="disqualificationCategory" style="border: 1px solid #feb2b2; border-radius: 7px; padding: 8px 10px; font-size: 13px;">
                            <option value="">Select a reason</option>
                            <option value="Technical skills insufficient">Technical skills insufficient</option>
                            <option value="Interview performance">Interview performance</option>
                            <option value="Availability / Schedule mismatch">Availability / Schedule mismatch</option>
                            <option value="Position already filled">Position already filled</option>
                            <option value="Not suitable for role requirements">Not suitable for role requirements</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="disqualificationFeedback" style="color: #9b2c2c; font-size: 12px; font-weight: 700;">
                            Detailed Disqualification Reason <span style="color: #e53e3e;">* (Mandatory)</span>
                        </label>
                        <textarea id="disqualificationFeedback" rows="3" placeholder="Provide the exact reason why candidate was disqualified..." style="border: 1px solid #feb2b2; border-radius: 7px; padding: 8px 10px; font-size: 13px;"></textarea>
                        <small style="color: #c53030; font-size: 11px;">A specific justification must be documented when disqualifying.</small>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="modal-cancel-btn" data-close-modal>Cancel</button>
                <button type="submit" class="modal-primary-btn" id="btnSubmitDecision" style="background: #16854a; border-color: #16854a; color: #ffffff; display: inline-flex; align-items: center; gap: 6px;">
                    <span class="material-symbols-outlined" style="font-size: 17px;">check_circle</span>
                    <span id="decisionSubmitText">Confirm Hire</span>
                </button>
            </div>
        </form>
    </div>
</div>


<script src="../../../Assets/JS/Company/interviews.js?v=<?php echo time(); ?>"></script>

<?php
include "../../../Includes/dash_footer.php";
?>
