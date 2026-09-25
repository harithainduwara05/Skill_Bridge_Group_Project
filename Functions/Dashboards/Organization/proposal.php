<?php

include "../../../Config/db.php";
include "../../../Session/Session.php";

require_role('organization');

$user = current_user();
$organization_email = $user['email'];

/*
|--------------------------------------------------------------------------
| UI-ONLY DEMO DATA
|--------------------------------------------------------------------------
| Backend/database functionality is NOT used here.
| All proposal actions are handled on the frontend using JavaScript.
|
| status:
| pending | accepted | rejected
|--------------------------------------------------------------------------
*/

$proposals = [
    [
        'id' => 1,
        'email' => '2024is015@stu.ucsc.cmb.ac.lk',
        'name' => 'Nimasha Fernando',
        'school' => 'University of Colombo - Software Engineering',
        'status' => 'pending',
        'project' => 'Cloud Migration UI/UX',
        'skills' => ['React', 'Figma', 'Tailwind CSS'],
        'summary' => 'I propose a comprehensive redesign of the cloud migration dashboard focusing on user-centric navigation and real-time data visualization to make every migration step easy to follow and track for the whole team.',
        'days_ago' => 3,
        'motivation' => 'I have redesigned two admin dashboards for university clubs and I enjoy turning complex, data-heavy screens into simple flows. I am comfortable running quick user interviews and testing prototypes with real users.',
        'plan' => [
            ['when' => 'Week 1-2', 'task' => 'User interviews, audit of the current dashboard and pain-point map'],
            ['when' => 'Week 3-4', 'task' => 'Wireframes, information architecture and a clickable Figma prototype'],
            ['when' => 'Week 5-6', 'task' => 'Design system (colours, type, components) and usability testing'],
            ['when' => 'Week 7-8', 'task' => 'React + Tailwind implementation of the key screens and hand-over'],
        ],
        'availability' => '15 hrs / week',
        'start' => 'Immediately',
        'estimate' => '8 weeks',
    ],
    [
        'id' => 2,
        'email' => '2024is032@stu.ucsc.cmb.ac.lk',
        'name' => 'Sahan Wickramasinghe',
        'school' => 'University of Colombo - Information Systems',
        'status' => 'pending',
        'project' => 'AI Model Optimization',
        'skills' => ['Python', 'TensorFlow', 'Model Pruning'],
        'summary' => 'My approach is to profile the current model, apply structured pruning and quantization, and then benchmark the accuracy against inference speed so the final model runs efficiently on low-cost hardware.',
        'days_ago' => 1,
        'motivation' => 'I work a lot with Python and Linux servers and I am interested in making ML models run on low-cost hardware. I have already tried pruning and quantization on a small image classifier as a personal project.',
        'plan' => [
            ['when' => 'Week 1-2', 'task' => 'Profile the current model: size, latency, accuracy baseline'],
            ['when' => 'Week 3-5', 'task' => 'Structured pruning and post-training quantization experiments'],
            ['when' => 'Week 6-7', 'task' => 'Benchmark accuracy vs. speed on target hardware'],
            ['when' => 'Week 8-10', 'task' => 'Final optimized model, report and deployment guide'],
        ],
        'availability' => '12 hrs / week',
        'start' => 'From Oct 01',
        'estimate' => '10 weeks',
    ],
    [
        'id' => 3,
        'email' => '2024is044@stu.ucsc.cmb.ac.lk',
        'name' => 'Dinithi Jayawardena',
        'school' => 'University of Colombo - Computer Science',
        'status' => 'accepted',
        'project' => 'Campus Event Management System',
        'skills' => ['Laravel', 'MySQL', 'Vue.js'],
        'summary' => 'I have built two event booking systems before. I plan to deliver the ticketing, attendee check-in and reporting modules in three sprints, with weekly demos for your team to review the progress.',
        'days_ago' => 6,
        'motivation' => 'I have built two event booking systems for faculty events and know the problems with ticket check-in on busy days. I can also set up Docker-based deployment so the system is easy to host.',
        'plan' => [
            ['when' => 'Sprint 1', 'task' => 'Event creation, ticket types and online registration'],
            ['when' => 'Sprint 2', 'task' => 'QR-code attendee check-in and live attendance count'],
            ['when' => 'Sprint 3', 'task' => 'Reports, CSV export and Docker deployment'],
        ],
        'availability' => '18 hrs / week',
        'start' => 'Immediately',
        'estimate' => '6 weeks',
    ],
    [
        'id' => 4,
        'email' => '2024is001@stu.ucsc.cmb.ac.lk',
        'name' => 'Kavindu Perera',
        'school' => 'University of Colombo - Computer Science',
        'status' => 'pending',
        'project' => 'Smart Library Assistant',
        'skills' => ['Java', 'Spring Boot', 'PostgreSQL'],
        'summary' => 'I would create a smart assistant that helps students search, reserve and renew library books. It will include a recommendation feature based on borrowing history and a simple admin panel for librarians.',
        'days_ago' => 9,
        'motivation' => 'I use the university library every day and know what students struggle with when searching and reserving books. I have experience with Java backends and recommendation logic from my ML coursework.',
        'plan' => [
            ['when' => 'Week 1-2', 'task' => 'Database design and book search API (Spring Boot + PostgreSQL)'],
            ['when' => 'Week 3-4', 'task' => 'Reserve, renew and due-date reminder features'],
            ['when' => 'Week 5-6', 'task' => 'Recommendations based on borrowing history'],
            ['when' => 'Week 7-8', 'task' => 'Librarian admin panel, testing and documentation'],
        ],
        'availability' => '10 hrs / week',
        'start' => 'From Oct 05',
        'estimate' => '8 weeks',
    ],
    [
        'id' => 5,
        'email' => '2024is091@stu.ucsc.cmb.ac.lk',
        'name' => 'Sachini Wijesinghe',
        'school' => 'University of Colombo - Software Engineering',
        'status' => 'rejected',
        'project' => 'Mobile Attendance Tracker',
        'skills' => ['Flutter', 'Firebase'],
        'summary' => 'I can build a cross-platform attendance app with QR check-in and offline support, and connect it to the existing student records so that lecturers get automatic attendance reports every week.',
        'days_ago' => 12,
        'motivation' => 'I build Flutter apps with Firebase and have published a campus bus tracker used by my batch. Offline support and simple UI for lecturers are my main focus for this attendance app.',
        'plan' => [
            ['when' => 'Week 1-2', 'task' => 'QR check-in flow and lecturer session setup'],
            ['when' => 'Week 3-4', 'task' => 'Offline mode with automatic sync'],
            ['when' => 'Week 5-6', 'task' => 'Weekly attendance reports and export'],
        ],
        'availability' => '8 hrs / week',
        'start' => 'From Oct 15',
        'estimate' => '6 weeks',
    ],
];

/*
|--------------------------------------------------------------------------
| STUDENT PROFILES (from the database)
|--------------------------------------------------------------------------
| "View Profile" shows the real student profile: details, social links,
| skills, portfolio projects, SkillBridge projects and certificates.
*/
function prFetchAll(mysqli $conn, string $sql, string $email): array
{
    try {
        $st = $conn->prepare($sql);
        $st->bind_param("s", $email);
        $st->execute();
        return $st->get_result()->fetch_all(MYSQLI_ASSOC);
    } catch (Throwable $e) {
        return [];
    }
}

$projectDetails = [];   // title => details, for the "Applied project" box
$studentProfiles = [];
foreach ($proposals as &$prop) {
    if (!isset($projectDetails[$prop['project']])) {
        $pd = prFetchAll($conn, "SELECT title, company, category, duration, members, deadline, difficulty, preferred_year
                                 FROM projects WHERE title = ? LIMIT 1", $prop['project'])[0] ?? null;
        if ($pd && !empty($pd['deadline']) && strtotime($pd['deadline'])) {
            $pd['deadline'] = date('M d, Y', strtotime($pd['deadline']));
        }
        $projectDetails[$prop['project']] = $pd;
    }
    $prop['submitted_on'] = date('M d, Y', strtotime('-' . (int)$prop['days_ago'] . ' days'));

    $email = $prop['email'];

    $stu = prFetchAll($conn, "SELECT Name, University, year, degree, profile_image, bio, github, linkedin, website
                              FROM student WHERE Email = ?", $email)[0] ?? null;

    if ($stu) {
        // Keep the card in sync with the real student account
        $prop['name']   = $stu['Name'];
        $prop['school'] = $stu['University'] . ' - ' . preg_replace('/^B\.Sc\.\s*in\s*/i', '', $stu['degree']);
    }

    $studentProfiles[$email] = [
        'email'      => $email,
        'name'       => $stu['Name'] ?? $prop['name'],
        'university' => $stu['University'] ?? '',
        'degree'     => $stu['degree'] ?? '',
        'year'       => $stu['year'] ?? '',
        'bio'        => $stu['bio'] ?? '',
        'image'      => !empty($stu['profile_image']) ? '../../../Assets/Images/Student/' . $stu['profile_image'] : '',
        'github'     => $stu['github'] ?? '',
        'linkedin'   => $stu['linkedin'] ?? '',
        'website'    => $stu['website'] ?? '',

        'skills' => prFetchAll($conn, "SELECT skill_name, category, level, percentage
                                       FROM skills WHERE Email = ? ORDER BY percentage DESC, skill_name", $email),

        'portfolio' => prFetchAll($conn, "SELECT title, description, project_link
                                          FROM portfolio WHERE Email = ? ORDER BY portfolio_id DESC", $email),

        'projects' => prFetchAll($conn, "SELECT p.title, p.company, p.category, sp.role, sp.progress, sp.status
                                         FROM student_projects sp
                                         JOIN projects p ON p.id = sp.project_id
                                         WHERE sp.Email = ?
                                         ORDER BY sp.student_project_id DESC", $email),

        'certificates' => prFetchAll($conn, "SELECT certificate_name, issuer, status
                                             FROM certificates WHERE Email = ? ORDER BY certificate_id DESC", $email),
    ];
}
unset($prop);

/*
|--------------------------------------------------------------------------
| REJECT WITH REASON (AJAX)
|--------------------------------------------------------------------------
| The page calls this with fetch(). It sends the rejection reason to the
| student as a notification and returns JSON.
*/
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'reject_proposal') {
    header('Content-Type: application/json');

    $proposalId = (int)($_POST['proposal_id'] ?? 0);
    $reason     = trim($_POST['reason'] ?? '');

    $proposal = null;
    foreach ($proposals as $item) {
        if ((int)$item['id'] === $proposalId) { $proposal = $item; break; }
    }

    if (!$proposal) {
        echo json_encode(['ok' => false, 'message' => 'Proposal not found.']);
        exit;
    }
    if (mb_strlen($reason) < 10) {
        echo json_encode(['ok' => false, 'message' => 'Please write a reason (at least 10 characters).']);
        exit;
    }
    $reason = mb_substr($reason, 0, 500);

    try {
        $on = $conn->prepare("SELECT Name FROM organization WHERE Email = ?");
        $on->bind_param("s", $organization_email);
        $on->execute();
        $orgName = $on->get_result()->fetch_assoc()['Name'] ?? 'The organization';

        $nTitle   = 'Proposal Not Accepted';
        $nMessage = $orgName . ' reviewed your proposal for "' . $proposal['project'] . '" and did not accept it. '
                  . 'Reason: ' . $reason;

        $nq = $conn->prepare("INSERT INTO notifications (Email, title, message, type, status)
                              VALUES (?, ?, ?, 'project', 'Unread')");
        $nq->bind_param("sss", $proposal['email'], $nTitle, $nMessage);
        $nq->execute();

        echo json_encode(['ok' => true]);
    } catch (Throwable $e) {
        echo json_encode(['ok' => false, 'message' => 'Could not send the notification. Please try again.']);
    }
    exit;
}

$statusLabels = [
    'pending' => 'Pending',
    'accepted' => 'Accepted',
    'rejected' => 'Rejected'
];

$totalProposals = count($proposals);

function prInitials($name)
{
    $parts = preg_split('/\s+/', trim($name));
    $out = '';

    foreach (array_slice($parts, 0, 2) as $p) {
        $out .= mb_strtoupper(mb_substr($p, 0, 1));
    }

    return $out;
}

include "../../../Includes/org_sidebar.php";
include "../../../Includes/dash_header.php";
?>

<style>

    /* =========================================================
       MAIN
    ========================================================= */

    .pr-wrap {
        padding: 14px 28px 28px;
    }

    .pr-head h1 {
        margin: 0;
        font-size: 34px;
        font-weight: 800;
        color: #0f3a66;
        line-height: 1.15;
    }

    .pr-head p {
        margin: 6px 0 0;
        font-size: 15px;
        color: #4b5563;
    }


    /* =========================================================
       FILTER BAR
    ========================================================= */

    .pr-filters {
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        padding: 14px;
        margin: 22px 0 18px;
    }

    .pr-search {
        flex: 1 1 260px;
        position: relative;
    }

    .pr-search .material-symbols-outlined {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 20px;
        color: #6b7280;
    }

    .pr-search input {
        width: 100%;
        box-sizing: border-box;
        padding: 11px 12px 11px 40px;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        background: #f3f4f6;
        font-family: inherit;
        font-size: 14px;
        color: #111827;
    }

    .pr-search input:focus {
        outline: 2px solid #0f3a66;
        background: #fff;
    }

    .pr-select {
        padding: 11px 34px 11px 14px;
        border: 1px solid #d1d5db;
        border-radius: 10px;
        background: #f9fafb;
        font-family: inherit;
        font-size: 14px;
        color: #374151;
        cursor: pointer;
    }

    .pr-sort {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 11px 16px;
        border: 1px solid #d1d5db;
        border-radius: 10px;
        background: #e5e7eb;
        font-family: inherit;
        font-size: 14px;
        font-weight: 500;
        color: #1f2937;
        cursor: pointer;
    }

    .pr-sort:hover {
        background: #dbe0e5;
    }

    .pr-sort .material-symbols-outlined {
        font-size: 18px;
    }


    /* =========================================================
       PROPOSAL LIST
    ========================================================= */

    .pr-list {
        display: flex;
        flex-direction: column;
        gap: 18px;
    }

    .pr-card {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 16px;
        padding: 22px 24px;
        box-shadow: 0 1px 3px rgba(0,0,0,.04);
        transition: .2s ease;
    }

    .pr-card:hover {
        box-shadow: 0 5px 18px rgba(15, 58, 102, .08);
    }

    .pr-card.hidden {
        display: none;
    }

    .pr-top {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 16px;
        margin-bottom: 18px;
    }

    .pr-person {
        display: flex;
        align-items: center;
        gap: 16px;
    }

    .pr-avatar {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        background: #e5edf5;
        color: #1e3a5f;
        font-size: 15px;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .pr-name-row {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .pr-name {
        font-size: 18px;
        font-weight: 600;
        color: #0f3a66;
    }

    .pr-status-block {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        gap: 5px;
        flex-shrink: 0;
    }

    .pr-verified {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        background: #dcfce7;
        color: #166534;
        font-size: 11px;
        font-weight: 700;
        padding: 3px 9px;
        border-radius: 999px;
    }

    .pr-verified .material-symbols-outlined {
        font-size: 14px;
    }

    .pr-school {
        font-size: 14px;
        color: #4b5563;
        margin-top: 2px;
    }


    /* =========================================================
       STATUS BADGES
    ========================================================= */

    .pr-badge {
        font-size: 12px;
        font-weight: 700;
        padding: 4px 14px;
        border-radius: 999px;
        white-space: nowrap;
    }

    .pr-badge.pending {
        background: #fef3c7;
        color: #92400e;
    }

    .pr-badge.accepted {
        background: #dcfce7;
        color: #166534;
    }

    .pr-badge.rejected {
        background: #fee2e2;
        color: #b91c1c;
    }


    /* =========================================================
       CARD BODY
    ========================================================= */

    .pr-body {
        display: grid;
        grid-template-columns: 1fr 300px;
        gap: 28px;
        padding-bottom: 20px;
        border-bottom: 1px solid #e5e7eb;
    }

    .pr-label {
        font-size: 11px;
        font-weight: 700;
        letter-spacing: .06em;
        text-transform: uppercase;
        color: #6b7280;
        margin-bottom: 7px;
    }

    .pr-section {
        margin-bottom: 16px;
    }

    .pr-section:last-child {
        margin-bottom: 0;
    }

    .pr-project {
        font-size: 17px;
        font-weight: 600;
        color: #111827;
    }

    .pr-chips {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .pr-chip {
        background: #e5e7eb;
        color: #374151;
        font-size: 12px;
        padding: 4px 12px;
        border-radius: 5px;
    }

    .pr-summary {
        font-size: 14px;
        line-height: 1.55;
        color: #4b5563;
        margin: 0;

        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .pr-portfolio-box {
        height: 130px;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        background: #f3f4f6;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #9ca3af;
    }

    .pr-portfolio-box .material-symbols-outlined {
        font-size: 36px;
    }

    .pr-submitted {
        text-align: right;
        font-size: 12px;
        color: #4b5563;
        margin-top: 8px;
    }


    /* =========================================================
       FOOTER ACTIONS
    ========================================================= */

    .pr-foot {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 14px;
        flex-wrap: wrap;
        padding-top: 16px;
    }

    .pr-links {
        display: flex;
        align-items: center;
        gap: 18px;
        flex-wrap: wrap;
    }

    .pr-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        padding: 9px 18px;
        border-radius: 8px;
        font-family: inherit;
        font-size: 14px;
        font-weight: 500;
        cursor: pointer;
        text-decoration: none;
        border: 1px solid transparent;
        transition: all .15s ease;
    }

    .pr-btn.outline {
        background: #fff;
        border-color: #0f2a4a;
        color: #0f2a4a;
    }

    .pr-btn.outline:hover {
        background: #f1f5f9;
    }

    .pr-link {
        font-size: 14px;
        color: #1f2937;
        text-decoration: none;
        cursor: pointer;
        border: none;
        background: none;
        padding: 0;
        font-family: inherit;
    }

    .pr-link:hover {
        text-decoration: underline;
    }

    .pr-actions {
        display: flex;
        gap: 10px;
    }

    /* Accept / Reject buttons disappear after a decision is made */
    .pr-actions[hidden] {
        display: none !important;
    }

    .pr-btn.accept {
        background: #0f2a4a;
        color: #fff;
    }

    .pr-btn.reject {
        background: #e5e7eb;
        color: #374151;
    }

    .pr-btn.accept:hover {
        background: #0a1f38;
    }

    .pr-btn.reject:hover {
        background: #d1d5db;
    }

    .pr-btn[disabled] {
        opacity: .5;
        cursor: not-allowed;
    }


    /* =========================================================
       EMPTY RESULT
    ========================================================= */

    .pr-empty {
        display: none;
        text-align: center;
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 16px;
        padding: 50px 20px;
        color: #6b7280;
    }

    .pr-empty.show {
        display: block;
    }

    .pr-empty .material-symbols-outlined {
        font-size: 46px;
        color: #9ca3af;
        margin-bottom: 10px;
    }

    .pr-empty h3 {
        margin: 0 0 6px;
        color: #374151;
    }

    .pr-empty p {
        margin: 0;
        font-size: 14px;
    }


    /* =========================================================
       MODAL
    ========================================================= */

    .pr-modal-overlay {
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, .55);
        display: none;
        align-items: center;
        justify-content: center;
        padding: 20px;
        z-index: 9999;
    }

    .pr-modal-overlay.show {
        display: flex;
    }

    .pr-modal {
        width: min(680px, 100%);
        max-height: 90vh;
        overflow-y: auto;
        background: #fff;
        border-radius: 18px;
        box-shadow: 0 20px 60px rgba(0,0,0,.2);
        animation: prModalIn .18s ease;
    }

    @keyframes prModalIn {
        from {
            opacity: 0;
            transform: translateY(10px) scale(.98);
        }
        to {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }

    .pr-modal-head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 15px;
        padding: 20px 24px;
        border-bottom: 1px solid #e5e7eb;
    }

    .pr-modal-head h2 {
        margin: 0;
        color: #0f3a66;
        font-size: 21px;
    }

    .pr-modal-close {
        width: 36px;
        height: 36px;
        border: none;
        border-radius: 50%;
        background: #f3f4f6;
        color: #374151;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .pr-modal-close:hover {
        background: #e5e7eb;
    }

    .pr-modal-body {
        padding: 24px;
    }

    .pr-modal-profile {
        display: flex;
        align-items: center;
        gap: 14px;
        margin-bottom: 22px;
    }

    .pr-modal-avatar {
        width: 54px;
        height: 54px;
        border-radius: 50%;
        background: #e5edf5;
        color: #1e3a5f;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
    }

    .pr-modal-name {
        font-size: 19px;
        font-weight: 700;
        color: #0f3a66;
    }

    .pr-modal-school {
        font-size: 14px;
        color: #6b7280;
        margin-top: 3px;
    }

    .pr-modal-section {
        margin-bottom: 20px;
    }

    .pr-modal-section:last-child {
        margin-bottom: 0;
    }

    .pr-modal-section h4 {
        margin: 0 0 8px;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: .05em;
        color: #6b7280;
    }

    .pr-modal-section p {
        margin: 0;
        font-size: 14px;
        line-height: 1.65;
        color: #374151;
    }

    .pr-modal-skills {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .pr-modal-footer {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        padding: 18px 24px;
        border-top: 1px solid #e5e7eb;
    }


    /* =========================================================
       CONFIRMATION MODAL
    ========================================================= */

    .pr-confirm-icon {
        width: 54px;
        height: 54px;
        border-radius: 50%;
        background: #e5edf5;
        color: #0f3a66;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 16px;
    }

    .pr-confirm-icon .material-symbols-outlined {
        font-size: 28px;
    }

    .pr-confirm-content {
        text-align: center;
    }

    .pr-confirm-content h3 {
        margin: 0 0 8px;
        color: #111827;
        font-size: 20px;
    }

    .pr-confirm-content p {
        margin: 0;
        color: #6b7280;
        line-height: 1.5;
        font-size: 14px;
    }

    .pr-confirm-actions {
        display: flex;
        justify-content: center;
        gap: 10px;
        margin-top: 24px;
    }

    .pr-cancel-btn {
        background: #e5e7eb;
        color: #374151;
    }

    .pr-confirm-btn {
        background: #0f2a4a;
        color: #fff;
    }

    .pr-confirm-btn.danger {
        background: #b91c1c;
    }

    /* =========================================================
       FULL PROPOSAL POPUP  (matches the SkillBridge dashboard theme)
    ========================================================= */

    .pf-modal { width: min(920px, 100%); max-height: 92vh; display: flex; flex-direction: column; overflow: hidden; border-radius: 16px; }
    .pf-scroll { overflow-y: auto; background: #f5f7fb; }

    .pf-hero { position: relative; padding: 22px 26px 18px; background: #fff; border-bottom: 1px solid #e5e7eb; }
    .pf-close {
        position: absolute; top: 16px; right: 16px; width: 36px; height: 36px; border-radius: 8px; border: none;
        background: transparent; color: #6b7280; cursor: pointer; display: flex; align-items: center; justify-content: center;
    }
    .pf-close:hover { background: #f3f4f6; color: #0f2a4a; }
    .pf-kicker { font-size: 12px; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; color: #6b7280; }
    .pf-title { margin: 6px 0 10px; font-size: 24px; font-weight: 800; line-height: 1.3; color: #0f3a66; padding-right: 44px; }
    .pf-hero-meta { display: flex; flex-wrap: wrap; align-items: center; gap: 8px 16px; font-size: 13px; color: #6b7280; }
    .pf-hero-meta > span:not(.pr-badge) { display: inline-flex; align-items: center; gap: 4px; }
    .pf-hero-meta .material-symbols-outlined { font-size: 16px; }

    .pf-student {
        display: flex; align-items: center; gap: 16px; flex-wrap: wrap;
        margin: 18px 26px 0; padding: 16px; border: 1px solid #e5e7eb; border-radius: 12px; background: #fff;
    }
    .pf-avatar {
        width: 64px; height: 64px; border-radius: 50%; overflow: hidden; flex-shrink: 0;
        display: flex; align-items: center; justify-content: center; border: 2px solid #e5e7eb;
        background: linear-gradient(135deg, #1e3a5f, #0f8f82); color: #fff; font-weight: 700; font-size: 24px;
    }
    .pf-avatar img { width: 100%; height: 100%; object-fit: cover; }
    .pf-student-info { flex: 1; min-width: 180px; }
    .pf-name { font-size: 18px; font-weight: 700; color: #0f2a4a; }
    .pf-sub { font-size: 14px; color: #374151; margin-top: 2px; display: flex; align-items: center; gap: 4px; }
    .pf-sub.muted { color: #6b7280; font-size: 13px; }
    .pf-sub .material-symbols-outlined { font-size: 16px; }
    .pf-mini-stats { display: flex; gap: 8px; }
    .pf-mini-stats > div { min-width: 78px; text-align: center; padding: 8px 10px; border-radius: 10px; background: #f8fafc; border: 1px solid #e5e7eb; }
    .pf-mini-stats strong { display: block; font-size: 18px; font-weight: 800; color: #0f2a4a; }
    .pf-mini-stats span { font-size: 10.5px; color: #6b7280; text-transform: uppercase; letter-spacing: .05em; font-weight: 700; }

    .pf-grid { display: grid; grid-template-columns: minmax(0, 1.6fr) minmax(0, 1fr); gap: 16px; padding: 16px 26px 22px; }
    .pf-main { background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; padding: 18px; }
    .pf-sec + .pf-sec { margin-top: 18px; padding-top: 18px; border-top: 1px solid #f1f3f6; }
    .pf-sec h4 {
        display: flex; align-items: center; gap: 6px; margin: 0 0 8px; font-size: 12px; font-weight: 700;
        text-transform: uppercase; letter-spacing: .06em; color: #6b7280;
    }
    .pf-sec h4 .material-symbols-outlined { font-size: 18px; color: #0f3a66; }
    .pf-sec p { margin: 0; font-size: 14px; line-height: 1.7; color: #374151; }

    .pf-plan { list-style: none; margin: 0; padding: 0; position: relative; }
    .pf-plan::before { content: ''; position: absolute; left: 13px; top: 8px; bottom: 8px; width: 2px; background: #e5e7eb; }
    .pf-plan li { position: relative; display: flex; gap: 12px; padding: 6px 0; }
    .pf-dot {
        width: 28px; height: 28px; border-radius: 8px; flex-shrink: 0; z-index: 1; display: flex; align-items: center; justify-content: center;
        background: #0f2a4a; color: #fff; font-size: 12px; font-weight: 700;
    }
    .pf-when { font-size: 12px; font-weight: 700; color: #0f3a66; }
    .pf-task { font-size: 14px; color: #374151; line-height: 1.5; }

    .pf-chips { display: flex; flex-wrap: wrap; gap: 8px; }
    .pf-chip { padding: 5px 12px; border-radius: 6px; background: #e8effa; color: #0f3a66; font-size: 12.5px; font-weight: 600; }
    .pf-muted { font-size: 13px; color: #9ca3af; }

    .pf-side { display: flex; flex-direction: column; gap: 12px; }
    .pf-box { background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; padding: 16px; }
    .pf-box-title { display: flex; align-items: center; gap: 6px; font-size: 12px; font-weight: 700; color: #6b7280; text-transform: uppercase; letter-spacing: .06em; margin-bottom: 10px; }
    .pf-box-title .material-symbols-outlined { font-size: 17px; color: #0f3a66; }
    .pf-row { display: flex; justify-content: space-between; gap: 10px; padding: 7px 0; font-size: 13px; border-top: 1px solid #f1f3f6; }
    .pf-box-title + .pf-row, .pf-project-name + .pf-row { border-top: none; }
    .pf-row span { color: #6b7280; }
    .pf-row strong { color: #111827; font-weight: 600; text-align: right; }
    .pf-project-name { font-size: 15px; font-weight: 700; color: #0f3a66; margin-bottom: 4px; }
    .pf-contact { font-size: 13px; color: #374151; word-break: break-all; margin-bottom: 8px; }
    .pf-link { display: inline-flex; align-items: center; gap: 3px; margin-right: 12px; font-size: 13px; font-weight: 600; color: #0f3a66; text-decoration: none; }
    .pf-link .material-symbols-outlined { font-size: 14px; }
    .pf-link:hover { text-decoration: underline; }

    .pf-foot { display: flex; justify-content: space-between; align-items: center; gap: 10px; padding: 14px 26px 18px; border-top: 1px solid #e5e7eb; background: #fff; }
    .pf-foot-right { display: flex; gap: 10px; }
    .pf-foot .material-symbols-outlined { font-size: 18px; }
    .pf-ghost { background: #fff; border: 1px solid #0f2a4a; color: #0f2a4a; }
    .pf-ghost:hover { background: #f1f5f9; }

    @media (max-width: 760px) {
        .pf-grid { grid-template-columns: 1fr; padding: 16px 18px 20px; }
        .pf-hero { padding: 20px 18px 16px; }
        .pf-student { margin: 16px 18px 0; }
        .pf-foot { flex-direction: column-reverse; align-items: stretch; padding: 14px 18px 18px; }
        .pf-foot-right { flex-direction: column-reverse; }
        .pf-foot .pr-btn { width: 100%; }
    }

    /* =========================================================
       STUDENT PROFILE (same page)  – dashboard theme
    ========================================================= */

    .sp-page { padding: 14px 28px 28px; }
    .sp-page[hidden] { display: none; }

    .sp-topbar { display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap; margin-bottom: 16px; }
    .sp-back {
        display: inline-flex; align-items: center; gap: 6px; padding: 9px 16px; border-radius: 8px;
        border: 1px solid #0f2a4a; background: #fff; color: #0f2a4a; font-family: inherit;
        font-size: 14px; font-weight: 500; cursor: pointer; transition: all .15s ease;
    }
    .sp-back .material-symbols-outlined { font-size: 18px; transition: transform .15s ease; }
    .sp-back:hover { background: #f1f5f9; }
    .sp-back:hover .material-symbols-outlined { transform: translateX(-3px); }
    .sp-crumbs { display: flex; align-items: center; gap: 4px; font-size: 13px; color: #9ca3af; }
    .sp-crumbs .material-symbols-outlined { font-size: 16px; }
    .sp-crumbs strong { color: #374151; font-weight: 600; }

    .sp-sheet { animation: spIn .2s ease; }
    @keyframes spIn { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: none; } }
    .sp-body { display: flex; flex-direction: column; gap: 16px; }

    /* Header card – same style as the dashboard profile card */
    .sp-headcard {
        display: flex; gap: 24px; align-items: flex-start; background: #fff; border: 1px solid #e5e7eb;
        border-radius: 16px; padding: 28px;
    }
    .sp-avatar {
        width: 150px; height: 150px; border-radius: 50%; flex-shrink: 0; overflow: hidden;
        background: linear-gradient(135deg, #1e3a5f, #0f8f82); color: #fff; font-size: 56px; font-weight: 700;
        display: flex; align-items: center; justify-content: center; border: 3px solid #e5e7eb;
    }
    .sp-avatar img { width: 100%; height: 100%; object-fit: cover; }
    .sp-head-info { flex: 1; min-width: 0; }
    .sp-head-info h2 { margin: 0; font-size: 28px; font-weight: 800; color: #0f2a4a; line-height: 1.2; }
    .sp-degree { font-size: 17px; color: #1f2937; margin-top: 6px; }
    .sp-uni { font-size: 17px; color: #1f2937; margin-top: 2px; }
    .sp-applied {
        display: inline-flex; align-items: center; gap: 6px; margin-top: 14px; padding: 7px 12px;
        border-radius: 8px; background: #e8effa; color: #0f3a66; font-size: 13.5px;
    }
    .sp-applied .material-symbols-outlined { font-size: 18px; }
    .sp-socials { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 16px; }
    .sp-social {
        display: inline-flex; align-items: center; gap: 6px; padding: 9px 16px; border-radius: 8px;
        border: 1px solid #0f2a4a; background: #fff; color: #0f2a4a; font-size: 14px; font-weight: 500;
        text-decoration: none; transition: background .15s ease;
    }
    .sp-social .material-symbols-outlined { font-size: 18px; }
    .sp-social:hover { background: #f1f5f9; }

    /* Stat cards – same style as the dashboard stat cards */
    .sp-stats { display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px; }
    .sp-stat { display: flex; align-items: center; gap: 14px; background: #fff; border: 1px solid #e5e7eb; border-radius: 16px; padding: 18px; }
    .sp-stat-icon { width: 44px; height: 44px; border-radius: 10px; flex-shrink: 0; display: flex; align-items: center; justify-content: center; }
    .sp-stat-icon .material-symbols-outlined { font-size: 22px; }
    .sp-stat-icon.blue   { background: #e8f0fe; color: #2563eb; }
    .sp-stat-icon.green  { background: #dcfce7; color: #16a34a; }
    .sp-stat-icon.gray   { background: #e5e7eb; color: #374151; }
    .sp-stat-icon.orange { background: #ffedd5; color: #ea580c; }
    .sp-stat-label { font-size: 12px; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; color: #6b7280; }
    .sp-stat-value { font-size: 26px; font-weight: 800; color: #111827; line-height: 1.2; }

    /* Section cards */
    .sp-section { background: #fff; border: 1px solid #e5e7eb; border-radius: 16px; padding: 20px 22px; }
    .sp-section h4 {
        display: flex; align-items: center; gap: 6px; margin: 0 0 14px; font-size: 13px; font-weight: 700;
        color: #6b7280; text-transform: uppercase; letter-spacing: .06em;
    }
    .sp-section h4 .material-symbols-outlined { font-size: 18px; color: #0f3a66; }
    .sp-bio { margin: 0; font-size: 15px; line-height: 1.7; color: #374151; }
    .sp-empty { margin: 0; font-size: 13px; color: #9ca3af; font-style: italic; }

    .sp-skills { display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px; }
    .sp-skill { border: 1px solid #e5e7eb; border-radius: 12px; padding: 12px 14px; }
    .sp-skill-top { display: flex; justify-content: space-between; gap: 8px; margin-bottom: 8px; }
    .sp-skill-name { font-size: 14px; font-weight: 600; color: #111827; }
    .sp-skill-level { font-size: 12px; font-weight: 700; color: #0f3a66; }
    .sp-skill-cat { font-size: 12px; color: #9ca3af; margin-top: 6px; }
    .sp-bar { height: 8px; border-radius: 999px; background: #e5e7eb; overflow: hidden; }
    .sp-bar span { display: block; height: 100%; border-radius: 999px; background: #0f3a66; }
    .sp-bar.done span { background: #16a34a; }

    .sp-list { display: flex; flex-direction: column; gap: 10px; }
    .sp-card, .sp-cert { display: flex; gap: 12px; align-items: flex-start; border: 1px solid #e5e7eb; border-radius: 12px; padding: 14px; }
    .sp-cert { align-items: center; }
    .sp-card-icon, .sp-cert-icon {
        width: 40px; height: 40px; border-radius: 10px; flex-shrink: 0; display: flex; align-items: center; justify-content: center;
    }
    .sp-card-icon { background: #e8f0fe; color: #2563eb; }
    .sp-cert-icon { background: #ffedd5; color: #ea580c; }
    .sp-card-body { flex: 1; min-width: 0; }
    .sp-card-title { font-size: 15px; font-weight: 600; color: #111827; }
    .sp-card-text { font-size: 13.5px; color: #6b7280; line-height: 1.55; margin-top: 3px; }
    .sp-card-link { display: inline-flex; align-items: center; gap: 3px; margin-top: 8px; font-size: 13px; font-weight: 600; color: #0f3a66; text-decoration: none; }
    .sp-card-link .material-symbols-outlined { font-size: 15px; }
    .sp-card-link:hover { text-decoration: underline; }

    .sp-proj { border: 1px solid #e5e7eb; border-radius: 12px; padding: 14px; }
    .sp-proj-top { display: flex; justify-content: space-between; gap: 10px; align-items: flex-start; margin-bottom: 10px; }
    .sp-proj-meta { font-size: 13px; color: #6b7280; margin-top: 2px; }
    .sp-proj-pct { font-size: 12px; color: #9ca3af; margin-top: 6px; }
    .sp-status { font-size: 12px; font-weight: 600; padding: 3px 10px; border-radius: 999px; white-space: nowrap; }
    .sp-status.active  { background: #fed7aa; color: #9a3412; }
    .sp-status.done    { background: #dcfce7; color: #166534; }
    .sp-status.pending { background: #fef3c7; color: #92400e; }

    @media (max-width: 900px) {
        .sp-stats { grid-template-columns: repeat(2, 1fr); }
    }
    @media (max-width: 640px) {
        .sp-page { padding: 12px 16px 24px; }
        .sp-headcard { flex-direction: column; padding: 20px; }
        .sp-avatar { width: 110px; height: 110px; font-size: 42px; }
        .sp-head-info h2 { font-size: 22px; }
        .sp-skills { grid-template-columns: 1fr; }
    }

    /* =========================================================
       REJECT MODAL
    ========================================================= */

    .rj-modal { width: min(560px, 100%); }

    .rj-head {
        display: flex; align-items: flex-start; gap: 14px;
        padding: 20px 22px 16px; border-bottom: 1px solid #eef0f3;
    }
    .rj-head-icon {
        width: 44px; height: 44px; border-radius: 12px; flex-shrink: 0;
        background: #fef2f2; color: #dc2626; display: flex; align-items: center; justify-content: center;
    }
    .rj-head-text { flex: 1; min-width: 0; }
    .rj-head-text h3 { margin: 0; font-size: 18px; color: #111827; }
    .rj-head-text p { margin: 4px 0 0; font-size: 13px; color: #6b7280; line-height: 1.5; }
    .rj-head-text strong { color: #374151; font-weight: 600; }
    .rj-close {
        background: none; border: none; cursor: pointer; color: #6b7280;
        padding: 4px; border-radius: 8px; display: flex;
    }
    .rj-close:hover { background: #f3f4f6; color: #111827; }

    .rj-body { padding: 18px 22px 6px; }
    .rj-label {
        display: block; font-size: 12px; font-weight: 700; letter-spacing: .04em;
        text-transform: uppercase; color: #6b7280; margin-bottom: 10px;
    }
    .rj-quick { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 12px; }
    .rj-chip {
        border: 1px solid #e5e7eb; background: #f9fafb; color: #374151; font-family: inherit;
        font-size: 12.5px; padding: 6px 12px; border-radius: 999px; cursor: pointer; text-align: left;
        transition: all .15s ease;
    }
    .rj-chip:hover { border-color: #fca5a5; background: #fef2f2; color: #b91c1c; }
    .rj-chip.picked { border-color: #dc2626; background: #fef2f2; color: #b91c1c; font-weight: 600; }

    #rjReason {
        width: 100%; box-sizing: border-box; resize: vertical; min-height: 100px;
        border: 1px solid #e5e7eb; border-radius: 10px; padding: 10px 12px;
        font-family: inherit; font-size: 14px; line-height: 1.5; color: #111827; outline: none;
    }
    #rjReason:focus { border-color: #93c5fd; box-shadow: 0 0 0 3px rgba(59,130,246,.12); }
    #rjReason.invalid { border-color: #ef4444; box-shadow: 0 0 0 3px rgba(239,68,68,.12); }

    .rj-meta { display: flex; justify-content: space-between; gap: 10px; margin-top: 6px; min-height: 18px; }
    .rj-error { font-size: 12.5px; color: #dc2626; }
    .rj-count { font-size: 12px; color: #9ca3af; margin-left: auto; }

    .rj-note {
        display: flex; align-items: center; gap: 8px; margin: 12px 0 8px; padding: 10px 12px;
        border-radius: 10px; background: #eff6ff; color: #1e40af; font-size: 12.5px;
    }
    .rj-note .material-symbols-outlined { font-size: 18px; }

    .rj-foot {
        display: flex; justify-content: flex-end; gap: 10px;
        padding: 14px 22px 20px; border-top: 1px solid #eef0f3;
    }
    #rjSubmit .material-symbols-outlined { font-size: 18px; }
    #rjSubmit:disabled { opacity: .7; cursor: wait; }

    @media (max-width: 520px) {
        .rj-foot { flex-direction: column-reverse; }
        .rj-foot .pr-btn { width: 100%; }
    }


    /* =========================================================
       TOAST
    ========================================================= */

    .pr-toast {
        position: fixed;
        right: 24px;
        bottom: 24px;
        background: #0f2a4a;
        color: #fff;
        padding: 13px 18px;
        border-radius: 10px;
        font-size: 14px;
        box-shadow: 0 10px 30px rgba(0,0,0,.18);
        transform: translateY(20px);
        opacity: 0;
        pointer-events: none;
        transition: .25s ease;
        z-index: 10001;
    }

    .pr-toast.show {
        transform: translateY(0);
        opacity: 1;
    }


    /* =========================================================
       RESPONSIVE
    ========================================================= */

    @media (max-width: 900px) {

        .pr-body {
            grid-template-columns: 1fr;
        }

    }

    @media (max-width: 600px) {

        .pr-wrap {
            padding: 12px 16px 24px;
        }

        .pr-head h1 {
            font-size: 26px;
        }

        .pr-top {
            flex-direction: column;
        }

        .pr-foot {
            flex-direction: column;
            align-items: stretch;
        }

        .pr-actions {
            justify-content: stretch;
        }

        .pr-actions .pr-btn {
            flex: 1;
        }

        .pr-modal-footer {
            flex-direction: column;
        }

        .pr-confirm-actions {
            flex-direction: column;
        }

    }

</style>


<main class="content">

    <div class="pr-wrap" id="proposalListView">

        <!-- =====================================================
             HEADER
        ====================================================== -->

        <div class="pr-head">

            <h1>Proposals Management</h1>

            <p>
                <span id="proposalTotal"><?= (int)$totalProposals ?></span>
                Total Proposals
            </p>

        </div>


        <!-- =====================================================
             FILTERS
        ====================================================== -->

        <div class="pr-filters">

            <!-- Search -->
            <div class="pr-search">

                <span class="material-symbols-outlined">
                    search
                </span>

                <input
                    type="text"
                    id="proposalSearch"
                    placeholder="Search proposals..."
                    autocomplete="off"
                >

            </div>


            <!-- Status filter -->
            <select
                class="pr-select"
                id="statusFilter"
            >

                <option value="all">
                    All Status
                </option>

                <?php foreach ($statusLabels as $key => $sl): ?>

                    <option value="<?= htmlspecialchars($key) ?>">
                        <?= htmlspecialchars($sl) ?>
                    </option>

                <?php endforeach; ?>

            </select>


            <!-- Time filter -->
            <select
                class="pr-select"
                id="timeFilter"
            >

                <option value="all">All Time</option>
                <option value="1">Last 24 Hours</option>
                <option value="7">Last 7 Days</option>
                <option value="30">Last 30 Days</option>

            </select>


            <!-- Sort -->
            <button
                type="button"
                class="pr-sort"
                id="sortButton"
            >

                <span class="material-symbols-outlined">
                    sort
                </span>

                <span id="sortText">
                    Sort by Newest
                </span>

            </button>

        </div>


        <!-- =====================================================
             PROPOSAL LIST
        ====================================================== -->

        <div class="pr-list" id="proposalList">

            <?php foreach ($proposals as $p): ?>

                <?php

                $st = $p['status'];

                $locked = in_array(
                    $st,
                    ['accepted', 'rejected'],
                    true
                );

                ?>

                <div
                    class="pr-card"
                    data-id="<?= (int)$p['id'] ?>"
                    data-name="<?= htmlspecialchars(strtolower($p['name'])) ?>"
                    data-school="<?= htmlspecialchars(strtolower($p['school'])) ?>"
                    data-project="<?= htmlspecialchars($p['project']) ?>"
                    data-status="<?= htmlspecialchars($st) ?>"
                    data-skills="<?= htmlspecialchars(strtolower(implode(' ', $p['skills']))) ?>"
                    data-days="<?= (int)$p['days_ago'] ?>"
                >

                    <!-- =================================================
                         TOP
                    ================================================== -->

                    <div class="pr-top">

                        <div class="pr-person">

                            <div class="pr-avatar">

                                <?= htmlspecialchars(prInitials($p['name'])) ?>

                            </div>

                            <div>

                                <div class="pr-name-row">

                                    <span class="pr-name">
                                        <?= htmlspecialchars($p['name']) ?>
                                    </span>

                                </div>

                                <div class="pr-school">

                                    <?= htmlspecialchars($p['school']) ?>

                                </div>

                            </div>

                        </div>


                        <!-- Status: hidden while still pending — a proposal's
                             default/unacted state isn't shown as a badge on
                             the card, only "Accepted" / "Rejected" are. The
                             "Pending" label still exists purely for the
                             Status filter dropdown above. -->

                        <span
                            class="pr-badge <?= htmlspecialchars($st) ?>"
                            data-status-badge
                            <?= $st === 'pending' ? 'style="display:none;"' : '' ?>
                        >

                            <?= htmlspecialchars($statusLabels[$st]) ?>

                        </span>

                    </div>


                    <!-- =================================================
                         BODY
                    ================================================== -->

                    <div class="pr-body">

                        <div>

                            <!-- Project -->

                            <div class="pr-section">

                                <div class="pr-label">
                                    Applied For
                                </div>

                                <div class="pr-project">

                                    <?= htmlspecialchars($p['project']) ?>

                                </div>

                            </div>


                            <!-- Skills -->

                            <div class="pr-section">

                                <div class="pr-label">
                                    Skills
                                </div>

                                <div class="pr-chips">

                                    <?php foreach ($p['skills'] as $sk): ?>

                                        <span class="pr-chip">

                                            <?= htmlspecialchars($sk) ?>

                                        </span>

                                    <?php endforeach; ?>

                                </div>

                            </div>


                            <!-- Summary -->

                            <div class="pr-section">

                                <div class="pr-label">
                                    Proposal Summary
                                </div>

                                <p class="pr-summary">

                                    <?= htmlspecialchars($p['summary']) ?>

                                </p>

                            </div>

                        </div>


                        <!-- Portfolio -->

                        <div>

                            <div class="pr-label">
                                Portfolio Preview
                            </div>

                            <div
                                class="pr-portfolio-box"
                                title="Portfolio preview"
                            >

                                <span class="material-symbols-outlined">
                                    image
                                </span>

                            </div>

                            <div class="pr-submitted">

                                Submitted on
                                <?= date(
                                    'M d, Y',
                                    strtotime(
                                        '-' . (int)$p['days_ago'] . ' day'
                                    )
                                ) ?>

                            </div>

                        </div>

                    </div>


                    <!-- =================================================
                         FOOTER
                    ================================================== -->

                    <div class="pr-foot">

                        <div class="pr-links">

                            <!-- View Full Proposal -->

                            <button
                                type="button"
                                class="pr-btn outline view-proposal-btn"
                                data-id="<?= (int)$p['id'] ?>"
                            >

                                <span class="material-symbols-outlined">
                                    visibility
                                </span>

                                View Full Proposal

                            </button>


                            <!-- View Profile -->

                            <button
                                type="button"
                                class="pr-link view-profile-btn"
                                data-id="<?= (int)$p['id'] ?>"
                            >

                                View Profile

                            </button>

                        </div>


                        <!-- Action buttons -->

                        <!-- Hidden once the proposal is Accepted / Rejected -->
                        <div class="pr-actions" <?= $locked ? 'hidden' : '' ?>>

                            <!-- Accept -->

                            <button
                                type="button"
                                class="pr-btn accept accept-btn"
                                data-id="<?= (int)$p['id'] ?>"
                                <?= $locked ? 'disabled' : '' ?>
                            >

                                <span class="material-symbols-outlined">
                                    check
                                </span>

                                Accept

                            </button>


                            <!-- Reject -->

                            <button
                                type="button"
                                class="pr-btn reject reject-btn"
                                data-id="<?= (int)$p['id'] ?>"
                                <?= $locked ? 'disabled' : '' ?>
                            >

                                <span class="material-symbols-outlined">
                                    close
                                </span>

                                Reject

                            </button>

                        </div>

                    </div>

                </div>

            <?php endforeach; ?>

        </div>


        <!-- =====================================================
             NO RESULTS
        ====================================================== -->

        <div
            class="pr-empty"
            id="emptyResults"
        >

            <span class="material-symbols-outlined">
                search_off
            </span>

            <h3>
                No proposals found
            </h3>

            <p>
                Try changing your search or filter options.
            </p>

        </div>

    </div>


    <!-- =====================================================
         STUDENT PROFILE (shown on the same page, no popup)
    ====================================================== -->

    <section class="sp-page" id="studentProfileView" hidden>

        <div class="sp-topbar">
            <button type="button" class="sp-back" id="spBack">
                <span class="material-symbols-outlined">arrow_back</span>
                Back to Proposals
            </button>
            <div class="sp-crumbs">
                <span>Proposals</span>
                <span class="material-symbols-outlined">chevron_right</span>
                <strong>Student Profile</strong>
            </div>
        </div>

        <div class="sp-sheet">
            <div class="sp-body" id="spBody"></div>
        </div>

    </section>

</main>


<!-- =============================================================
     FULL PROPOSAL MODAL
============================================================== -->

<div class="pr-modal-overlay" id="proposalModal">
    <div class="pr-modal pf-modal" role="dialog" aria-modal="true">
        <div class="pf-scroll" id="proposalModalBody"></div>
        <div class="pf-foot" id="proposalModalFoot"></div>
    </div>
</div>


<!-- =============================================================
     PROFILE / PORTFOLIO MODAL
============================================================== -->

<div
    class="pr-modal-overlay"
    id="infoModal"
>

    <div class="pr-modal">

        <div class="pr-modal-head">

            <h2 id="infoModalTitle">
                Information
            </h2>

            <button
                type="button"
                class="pr-modal-close"
                data-close-modal="infoModal"
            >

                <span class="material-symbols-outlined">
                    close
                </span>

            </button>

        </div>


        <div
            class="pr-modal-body"
            id="infoModalBody"
        >
        </div>


        <div class="pr-modal-footer">

            <button
                type="button"
                class="pr-btn reject"
                data-close-modal="infoModal"
            >
                Close
            </button>

        </div>

    </div>

</div>


<!-- =============================================================
     CONFIRMATION MODAL
============================================================== -->

<div
    class="pr-modal-overlay"
    id="confirmModal"
>

    <div class="pr-modal">

        <div class="pr-modal-body">

            <div class="pr-confirm-content">

                <div class="pr-confirm-icon">

                    <span
                        class="material-symbols-outlined"
                        id="confirmIcon"
                    >
                        help
                    </span>

                </div>

                <h3 id="confirmTitle">
                    Confirm Action
                </h3>

                <p id="confirmMessage">
                    Are you sure you want to continue?
                </p>

                <div class="pr-confirm-actions">

                    <button
                        type="button"
                        class="pr-btn pr-cancel-btn"
                        data-close-modal="confirmModal"
                    >
                        Cancel
                    </button>

                    <button
                        type="button"
                        class="pr-btn pr-confirm-btn"
                        id="confirmActionButton"
                    >
                        Confirm
                    </button>

                </div>

            </div>

        </div>

    </div>

</div>


<!-- =============================================================
     REJECT MODAL (reason is sent to the student)
============================================================== -->

<div class="pr-modal-overlay" id="rejectModal">
    <div class="pr-modal rj-modal">

        <div class="rj-head">
            <div class="rj-head-icon">
                <span class="material-symbols-outlined">person_remove</span>
            </div>
            <div class="rj-head-text">
                <h3>Reject Proposal</h3>
                <p>From <strong id="rjStudent"></strong> for <strong id="rjProject"></strong></p>
            </div>
            <button type="button" class="rj-close" data-close-modal="rejectModal" aria-label="Close">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>

        <div class="rj-body">
            <label class="rj-label" for="rjReason">Reason for rejecting *</label>

            <div class="rj-quick">
                <button type="button" class="rj-chip">Your skills don’t match the project requirements.</button>
                <button type="button" class="rj-chip">The team for this project is already full.</button>
                <button type="button" class="rj-chip">The proposal needs more detail about your approach.</button>
                <button type="button" class="rj-chip">Your availability doesn’t match the project timeline.</button>
            </div>

            <textarea id="rjReason" rows="4" maxlength="500"
                      placeholder="Write a short, polite reason. Click a suggestion above to start quickly."></textarea>
            <div class="rj-meta">
                <span class="rj-error" id="rjError"></span>
                <span class="rj-count"><span id="rjCount">0</span>/500</span>
            </div>

            <div class="rj-note">
                <span class="material-symbols-outlined">notifications_active</span>
                The student will get a notification with this reason.
            </div>
        </div>

        <div class="rj-foot">
            <button type="button" class="pr-btn pr-cancel-btn" data-close-modal="rejectModal">Cancel</button>
            <button type="button" class="pr-btn pr-confirm-btn danger" id="rjSubmit">
                <span class="material-symbols-outlined">send</span>
                Reject &amp; Notify Student
            </button>
        </div>

    </div>
</div>


<!-- =============================================================
     TOAST
============================================================== -->

<div
    class="pr-toast"
    id="prToast"
>
</div>


<!-- =============================================================
     FOOTER
============================================================== -->

<footer class="footer">

    <div>
        &copy; 2026 SkillBridge. All rights reserved.
    </div>

    <div class="footer-links">

        <a href="#">
            Help Center
        </a>

        <a href="#">
            Privacy Policy
        </a>

        <a href="#">
            Terms of Service
        </a>

    </div>

</footer>


<script>

/* ================================================================
   PROPOSAL DATA
================================================================ */

const proposalData = <?= json_encode($proposals, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;

const statusLabels = <?= json_encode($statusLabels, JSON_UNESCAPED_UNICODE) ?>;

const projectDetails = <?= json_encode($projectDetails, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP) ?>;

const studentProfiles = <?= json_encode($studentProfiles, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP) ?>;


/* ================================================================
   DOM ELEMENTS
================================================================ */

const searchInput = document.getElementById('proposalSearch');

const statusFilter = document.getElementById('statusFilter');

const timeFilter = document.getElementById('timeFilter');

const sortButton = document.getElementById('sortButton');

const sortText = document.getElementById('sortText');

const proposalList = document.getElementById('proposalList');

const emptyResults = document.getElementById('emptyResults');

const proposalTotal = document.getElementById('proposalTotal');


let newestFirst = true;

let pendingAction = null;


/* ================================================================
   LOCAL STORAGE
================================================================ */

const STORAGE_KEY = 'skillbridge_organization_proposal_statuses';

// DEMO RESET: open proposal.php?reset_demo=1 to put every proposal back
// to its original status (clears the saved Accept / Reject decisions).
if (new URLSearchParams(location.search).has('reset_demo')) {
    localStorage.removeItem(STORAGE_KEY);
    location.replace(location.pathname);
}


function loadSavedStatuses()
{
    try {

        const saved = localStorage.getItem(STORAGE_KEY);

        if (!saved) {
            return {};
        }

        return JSON.parse(saved);

    } catch (error) {

        return {};

    }
}


function saveStatuses(statuses)
{
    localStorage.setItem(
        STORAGE_KEY,
        JSON.stringify(statuses)
    );
}


function getCurrentStatus(id)
{
    const statuses = loadSavedStatuses();

    if (statuses[id]) {
        return statuses[id];
    }

    const proposal = proposalData.find(
        p => Number(p.id) === Number(id)
    );

    return proposal ? proposal.status : 'pending';
}


/* ================================================================
   APPLY SAVED STATUS
================================================================ */

function applySavedStatuses()
{
    const cards = document.querySelectorAll('.pr-card');

    cards.forEach(card => {

        const id = card.dataset.id;

        const status = getCurrentStatus(id);

        updateCardStatus(
            card,
            status,
            false
        );

    });

}


/* ================================================================
   UPDATE CARD STATUS
================================================================ */

function updateCardStatus(
    card,
    newStatus,
    notify = true   // renamed: "showToast" here hid the showToast() function and crashed
)
{

    card.dataset.status = newStatus;


    /* Status badge */

    const badge = card.querySelector(
        '[data-status-badge]'
    );

    if (badge) {

        badge.className =
            'pr-badge ' + newStatus;

        badge.textContent =
            statusLabels[newStatus];

        // Only "Accepted" / "Rejected" are ever shown as a badge on the
        // card — "Pending" stays hidden (it's just the default state).
        badge.style.display =
            newStatus === 'pending' ? 'none' : '';

    }


    /* Buttons */

    const acceptBtn =
        card.querySelector('.accept-btn');

    const rejectBtn =
        card.querySelector('.reject-btn');


    if (acceptBtn) {

        acceptBtn.disabled =
            newStatus === 'accepted' ||
            newStatus === 'rejected';

    }


    if (rejectBtn) {

        rejectBtn.disabled =
            newStatus === 'accepted' ||
            newStatus === 'rejected';

    }

    /* Hide the Accept / Reject buttons once a decision is made */

    const actions =
        card.querySelector('.pr-actions');

    if (actions) {

        actions.hidden =
            newStatus === 'accepted' ||
            newStatus === 'rejected';

    }


    /* Save */

    const statuses = loadSavedStatuses();

    statuses[card.dataset.id] =
        newStatus;

    saveStatuses(statuses);


    if (notify) {

        let message = '';

        if (newStatus === 'accepted') {
            message = 'Proposal accepted successfully.';
        }

        if (newStatus === 'rejected') {
            message = 'Proposal rejected successfully.';
        }

        showToast(message);

    }

    applyFilters();
}


/* ================================================================
   FILTER PROPOSALS
================================================================ */

function applyFilters()
{

    const search =
        searchInput.value
            .trim()
            .toLowerCase();

    const selectedStatus =
        statusFilter.value;

    const selectedTime =
        timeFilter.value;


    const cards =
        Array.from(
            document.querySelectorAll('.pr-card')
        );


    let visibleCount = 0;


    cards.forEach(card => {

        const name =
            card.dataset.name || '';

        const school =
            card.dataset.school || '';

        const project =
            card.dataset.project || '';

        const status =
            card.dataset.status || '';

        const daysAgo =
            parseInt(card.dataset.days, 10) || 0;

        const skills =
            card.dataset.skills || '';


        const searchMatch =
            !search ||
            name.includes(search) ||
            school.includes(search) ||
            project.toLowerCase().includes(search) ||
            skills.includes(search);


        const statusMatch =
            selectedStatus === 'all' ||
            status === selectedStatus;

        const timeMatch =
            selectedTime === 'all' ||
            daysAgo <= parseInt(selectedTime, 10);


        const shouldShow =
            searchMatch &&
            statusMatch &&
            timeMatch;


        if (shouldShow) {

            card.classList.remove('hidden');

            visibleCount++;

        } else {

            card.classList.add('hidden');

        }

    });


    emptyResults.classList.toggle(
        'show',
        visibleCount === 0
    );

}


/* ================================================================
   SORT
================================================================ */

function sortCards()
{

    const cards =
        Array.from(
            document.querySelectorAll('.pr-card')
        );


    cards.sort((a, b) => {

        const aDays =
            Number(a.dataset.days);

        const bDays =
            Number(b.dataset.days);


        if (newestFirst) {

            return aDays - bDays;

        } else {

            return bDays - aDays;

        }

    });


    cards.forEach(card => {

        proposalList.appendChild(card);

    });


    sortText.textContent =
        newestFirst
            ? 'Sort by Newest'
            : 'Sort by Oldest';


    applyFilters();

}


/* ================================================================
   OPEN MODAL
================================================================ */

function openModal(id)
{

    const modal =
        document.getElementById(id);

    if (modal) {

        modal.classList.add('show');

        document.body.style.overflow =
            'hidden';

    }

}


/* ================================================================
   CLOSE MODAL
================================================================ */

function closeModal(id)
{

    const modal =
        document.getElementById(id);

    if (modal) {

        modal.classList.remove('show');

        document.body.style.overflow =
            '';

    }

}


/* ================================================================
   CLOSE ALL MODALS
================================================================ */

function closeAllModals()
{

    document
        .querySelectorAll('.pr-modal-overlay')
        .forEach(modal => {

            modal.classList.remove('show');

        });

    document.body.style.overflow = '';

}


/* ================================================================
   FULL PROPOSAL
================================================================ */

function showFullProposal(id)
{
    const proposal = proposalData.find(p => Number(p.id) === Number(id));
    if (!proposal) {
        return;
    }

    const e       = escapeHtml;
    const status  = getCurrentStatus(id);
    const sp      = studentProfiles[proposal.email] || {};
    const project = projectDetails[proposal.project] || null;

    const avatar = sp.image
        ? `<img src="${e(sp.image)}" alt="">`
        : e(getInitials(proposal.name));

    const skillsHtml = (proposal.skills || [])
        .map(sk => `<span class="pf-chip">${e(sk)}</span>`).join('');

    const planHtml = (proposal.plan || []).map((step, i) => `
        <li>
            <span class="pf-dot">${i + 1}</span>
            <div>
                <div class="pf-when">${e(step.when)}</div>
                <div class="pf-task">${e(step.task)}</div>
            </div>
        </li>`).join('');

    const projectRows = project ? [
        ['Organization', project.company],
        ['Category', project.category],
        ['Duration', project.duration],
        ['Students needed', project.members],
        ['Difficulty', project.difficulty],
        ['Deadline', project.deadline]
    ].filter(r => r[1] !== null && r[1] !== undefined && r[1] !== '')
     .map(r => `<div class="pf-row"><span>${e(r[0])}</span><strong>${e(String(r[1]))}</strong></div>`).join('')
    : '';

    const yearDegree = [sp.degree ? e(sp.degree) : '', sp.year ? 'Year ' + e(sp.year) : ''].filter(Boolean).join(' &bull; ');

    document.getElementById('proposalModalBody').innerHTML = `
        <div class="pf-hero">
            <button type="button" class="pf-close" data-close-modal="proposalModal" aria-label="Close">
                <span class="material-symbols-outlined">close</span>
            </button>
            <div class="pf-kicker">Project Proposal</div>
            <h2 class="pf-title">${e(proposal.project)}</h2>
            <div class="pf-hero-meta">
                <span class="pr-badge ${status}">${e(statusLabels[status])}</span>
                <span><span class="material-symbols-outlined">calendar_today</span> Submitted ${e(proposal.submitted_on || '')}</span>
                <span><span class="material-symbols-outlined">schedule</span> ${Number(proposal.days_ago)} day${Number(proposal.days_ago) === 1 ? '' : 's'} ago</span>
            </div>
        </div>

        <div class="pf-student">
            <div class="pf-avatar">${avatar}</div>
            <div class="pf-student-info">
                <div class="pf-name">${e(proposal.name)}</div>
                <div class="pf-sub">${yearDegree || e(proposal.school)}</div>
                <div class="pf-sub muted"><span class="material-symbols-outlined">school</span>${e(sp.university || proposal.school)}</div>
            </div>
            <div class="pf-mini-stats">
                <div><strong>${(sp.skills || []).length}</strong><span>Skills</span></div>
                <div><strong>${(sp.certificates || []).length}</strong><span>Certificates</span></div>
                <div><strong>${(sp.portfolio || []).length}</strong><span>Portfolio</span></div>
            </div>
        </div>

        <div class="pf-grid">
            <div class="pf-main">

                <section class="pf-sec">
                    <h4><span class="material-symbols-outlined">lightbulb</span> Proposal Summary</h4>
                    <p>${e(proposal.summary)}</p>
                </section>

                ${proposal.motivation ? `
                <section class="pf-sec">
                    <h4><span class="material-symbols-outlined">star</span> Why I’m a Good Fit</h4>
                    <p>${e(proposal.motivation)}</p>
                </section>` : ''}

                ${planHtml ? `
                <section class="pf-sec">
                    <h4><span class="material-symbols-outlined">timeline</span> Work Plan</h4>
                    <ol class="pf-plan">${planHtml}</ol>
                </section>` : ''}

                <section class="pf-sec">
                    <h4><span class="material-symbols-outlined">bolt</span> Skills Offered</h4>
                    <div class="pf-chips">${skillsHtml || '<span class="pf-muted">No skills listed.</span>'}</div>
                </section>

            </div>

            <aside class="pf-side">

                <div class="pf-box">
                    <div class="pf-box-title"><span class="material-symbols-outlined">event_available</span> Availability</div>
                    <div class="pf-row"><span>Time per week</span><strong>${e(proposal.availability || '-')}</strong></div>
                    <div class="pf-row"><span>Can start</span><strong>${e(proposal.start || '-')}</strong></div>
                    <div class="pf-row"><span>Estimated time</span><strong>${e(proposal.estimate || '-')}</strong></div>
                </div>

                <div class="pf-box">
                    <div class="pf-box-title"><span class="material-symbols-outlined">work</span> Applied Project</div>
                    <div class="pf-project-name">${e(proposal.project)}</div>
                    ${projectRows || '<div class="pf-muted">Project details are not available.</div>'}
                </div>

                <div class="pf-box">
                    <div class="pf-box-title"><span class="material-symbols-outlined">alternate_email</span> Contact</div>
                    <div class="pf-contact">${e(proposal.email)}</div>
                    ${sp.github ? `<a class="pf-link" href="${e(sp.github)}" target="_blank" rel="noopener">GitHub <span class="material-symbols-outlined">open_in_new</span></a>` : ''}
                    ${sp.linkedin ? `<a class="pf-link" href="${e(sp.linkedin)}" target="_blank" rel="noopener">LinkedIn <span class="material-symbols-outlined">open_in_new</span></a>` : ''}
                </div>

            </aside>
        </div>
    `;

    // Footer: Accept / Reject only while still pending
    const pending = status === 'pending';
    document.getElementById('proposalModalFoot').innerHTML = `
        <button type="button" class="pr-btn pf-ghost" data-pf-action="profile" data-id="${proposal.id}">
            <span class="material-symbols-outlined">person</span> View Profile
        </button>
        <div class="pf-foot-right">
            ${pending ? `
            <button type="button" class="pr-btn reject" data-pf-action="reject" data-id="${proposal.id}">
                <span class="material-symbols-outlined">close</span> Reject
            </button>
            <button type="button" class="pr-btn accept" data-pf-action="accept" data-id="${proposal.id}">
                <span class="material-symbols-outlined">check</span> Accept
            </button>` : `
            <button type="button" class="pr-btn pr-cancel-btn" data-close-modal="proposalModal">Close</button>`}
        </div>
    `;

    document.getElementById('proposalModalBody').scrollTop = 0;
    openModal('proposalModal');
}

// Buttons inside the Full Proposal popup
document.getElementById('proposalModalFoot').addEventListener('click', function (ev) {
    const btn = ev.target.closest('[data-pf-action]');
    if (!btn) {
        return;
    }
    const id = btn.dataset.id;
    closeModal('proposalModal');

    if (btn.dataset.pfAction === 'profile') { showProfile(id); }
    if (btn.dataset.pfAction === 'accept')  { askConfirmation(id, 'accept'); }
    if (btn.dataset.pfAction === 'reject')  { openRejectModal(id); }
});


/* ================================================================
   PROFILE
================================================================ */

function showProfile(id, pushHistory = true)
{
    const proposal = proposalData.find(p => Number(p.id) === Number(id));
    if (!proposal) {
        return;
    }

    const sp = studentProfiles[proposal.email] || {
        name: proposal.name, university: proposal.school, degree: '', year: '', bio: '',
        image: '', github: '', linkedin: '', website: '', email: proposal.email,
        skills: [], portfolio: [], projects: [], certificates: []
    };

    const e = escapeHtml;

    // ---------- header ----------
    const avatar = sp.image
        ? `<img src="${e(sp.image)}" alt="${e(sp.name)}">`
        : e(getInitials(sp.name));

    const yearText = sp.year ? `Year ${e(sp.year)}` : '';
    const degreeLine = [e(sp.degree), yearText].filter(Boolean).join(' &bull; ');

    const GITHUB_SVG = '<svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor"><path d="M12 .5a12 12 0 0 0-3.8 23.4c.6.1.8-.3.8-.6v-2.2c-3.3.7-4-1.4-4-1.4-.6-1.4-1.4-1.8-1.4-1.8-1.1-.8.1-.7.1-.7 1.2.1 1.9 1.2 1.9 1.2 1.1 1.8 2.8 1.3 3.5 1 .1-.8.4-1.3.8-1.6-2.7-.3-5.5-1.3-5.5-5.9 0-1.3.5-2.4 1.2-3.2-.1-.3-.5-1.5.1-3.2 0 0 1-.3 3.3 1.2a11.5 11.5 0 0 1 6 0C17.3 4.7 18.3 5 18.3 5c.7 1.7.3 2.9.1 3.2.8.8 1.2 1.9 1.2 3.2 0 4.6-2.8 5.6-5.5 5.9.4.4.8 1.1.8 2.2v3.3c0 .3.2.7.8.6A12 12 0 0 0 12 .5z"/></svg>';
    const LINKEDIN_SVG = '<svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor"><path d="M20.4 20.5h-3.6v-5.6c0-1.3 0-3-1.8-3s-2.1 1.4-2.1 2.9v5.7H9.3V9h3.4v1.6h.1c.5-.9 1.6-1.8 3.4-1.8 3.6 0 4.3 2.4 4.3 5.5v6.2zM5.3 7.4a2.1 2.1 0 1 1 0-4.2 2.1 2.1 0 0 1 0 4.2zM7.1 20.5H3.5V9h3.6v11.5zM22.2 0H1.8C.8 0 0 .8 0 1.7v20.6c0 .9.8 1.7 1.8 1.7h20.4c1 0 1.8-.8 1.8-1.7V1.7C24 .8 23.2 0 22.2 0z"/></svg>';

    const socials = [
        sp.github   ? `<a class="sp-social" href="${e(sp.github)}" target="_blank" rel="noopener">${GITHUB_SVG} GitHub</a>` : '',
        sp.linkedin ? `<a class="sp-social" href="${e(sp.linkedin)}" target="_blank" rel="noopener">${LINKEDIN_SVG} LinkedIn</a>` : '',
        sp.website  ? `<a class="sp-social" href="${e(sp.website)}" target="_blank" rel="noopener"><span class="material-symbols-outlined">language</span> Website</a>` : '',
        sp.email    ? `<a class="sp-social" href="mailto:${e(sp.email)}"><span class="material-symbols-outlined">mail</span> Email</a>` : ''
    ].join('');

    // ---------- skills ----------
    const skillsHtml = sp.skills.length
        ? sp.skills.map(sk => `
            <div class="sp-skill">
                <div class="sp-skill-top">
                    <span class="sp-skill-name">${e(sk.skill_name)}</span>
                    <span class="sp-skill-level">${e(sk.level || '')}</span>
                </div>
                <div class="sp-bar"><span style="width:${Math.max(0, Math.min(100, Number(sk.percentage) || 0))}%"></span></div>
                ${sk.category ? `<div class="sp-skill-cat">${e(sk.category)}</div>` : ''}
            </div>`).join('')
        : '<p class="sp-empty">No skills added yet.</p>';

    // ---------- portfolio ----------
    const portfolioHtml = sp.portfolio.length
        ? sp.portfolio.map(pf => `
            <div class="sp-card">
                <div class="sp-card-icon"><span class="material-symbols-outlined">code_blocks</span></div>
                <div class="sp-card-body">
                    <div class="sp-card-title">${e(pf.title || 'Untitled project')}</div>
                    ${pf.description ? `<div class="sp-card-text">${e(pf.description)}</div>` : ''}
                    ${pf.project_link ? `<a class="sp-card-link" href="${e(pf.project_link)}" target="_blank" rel="noopener">View project <span class="material-symbols-outlined">open_in_new</span></a>` : ''}
                </div>
            </div>`).join('')
        : '<p class="sp-empty">No portfolio projects yet.</p>';

    // ---------- SkillBridge projects ----------
    const projectsHtml = sp.projects.length
        ? sp.projects.map(pr => {
            const pct = Math.max(0, Math.min(100, Number(pr.progress) || 0));
            const done = (pr.status || '').toLowerCase() === 'completed';
            return `
            <div class="sp-proj">
                <div class="sp-proj-top">
                    <div>
                        <div class="sp-card-title">${e(pr.title)}</div>
                        <div class="sp-proj-meta">${e(pr.role || 'Team member')} &bull; ${e(pr.company || '')}</div>
                    </div>
                    <span class="sp-status ${done ? 'done' : 'active'}">${e(pr.status || 'In Progress')}</span>
                </div>
                <div class="sp-bar ${done ? 'done' : ''}"><span style="width:${pct}%"></span></div>
                <div class="sp-proj-pct">${pct}% complete</div>
            </div>`;
        }).join('')
        : '<p class="sp-empty">No SkillBridge projects yet.</p>';

    // ---------- certificates ----------
    const certsHtml = sp.certificates.length
        ? sp.certificates.map(c => {
            const ok = (c.status || '').toLowerCase() === 'approved';
            return `
            <div class="sp-cert">
                <div class="sp-cert-icon"><span class="material-symbols-outlined">workspace_premium</span></div>
                <div class="sp-card-body">
                    <div class="sp-card-title">${e(c.certificate_name || 'Certificate')}</div>
                    <div class="sp-proj-meta">${e(c.issuer || '')}</div>
                </div>
                <span class="sp-status ${ok ? 'done' : 'pending'}">${ok ? 'Approved' : e(c.status || 'Pending')}</span>
            </div>`;
        }).join('')
        : '<p class="sp-empty">No certificates yet.</p>';

    document.getElementById('spBody').innerHTML = `
        <div class="sp-headcard">
            <div class="sp-avatar">${avatar}</div>
            <div class="sp-head-info">
                <h2>${e(sp.name)}</h2>
                ${degreeLine ? `<div class="sp-degree">${degreeLine}</div>` : ''}
                ${sp.university ? `<div class="sp-uni">${e(sp.university)}</div>` : ''}
                <div class="sp-applied">
                    <span class="material-symbols-outlined">assignment</span>
                    Applied for <strong>${e(proposal.project)}</strong>
                </div>
                ${socials ? `<div class="sp-socials">${socials}</div>` : ''}
            </div>
        </div>

        <div class="sp-stats">
            <div class="sp-stat"><span class="sp-stat-icon blue"><span class="material-symbols-outlined">bolt</span></span>
                <div><div class="sp-stat-label">Skills</div><div class="sp-stat-value">${sp.skills.length}</div></div></div>
            <div class="sp-stat"><span class="sp-stat-icon green"><span class="material-symbols-outlined">folder_special</span></span>
                <div><div class="sp-stat-label">Portfolio</div><div class="sp-stat-value">${sp.portfolio.length}</div></div></div>
            <div class="sp-stat"><span class="sp-stat-icon gray"><span class="material-symbols-outlined">work</span></span>
                <div><div class="sp-stat-label">Projects</div><div class="sp-stat-value">${sp.projects.length}</div></div></div>
            <div class="sp-stat"><span class="sp-stat-icon orange"><span class="material-symbols-outlined">workspace_premium</span></span>
                <div><div class="sp-stat-label">Certificates</div><div class="sp-stat-value">${sp.certificates.length}</div></div></div>
        </div>

        ${sp.bio ? `
        <section class="sp-section">
            <h4><span class="material-symbols-outlined">person</span> About</h4>
            <p class="sp-bio">${e(sp.bio)}</p>
        </section>` : ''}

        <section class="sp-section">
            <h4><span class="material-symbols-outlined">bolt</span> Skills</h4>
            <div class="sp-skills">${skillsHtml}</div>
        </section>

        <section class="sp-section">
            <h4><span class="material-symbols-outlined">work</span> SkillBridge Projects</h4>
            <div class="sp-list">${projectsHtml}</div>
        </section>

        <section class="sp-section">
            <h4><span class="material-symbols-outlined">folder_special</span> Portfolio</h4>
            <div class="sp-list">${portfolioHtml}</div>
        </section>

        <section class="sp-section">
            <h4><span class="material-symbols-outlined">verified</span> Certificates</h4>
            <div class="sp-list">${certsHtml}</div>
        </section>
    `;

    openProfileView(proposal.id, pushHistory);
}


/* ---- Same-page profile view with Back (also works with the browser Back button) ---- */

let listScrollY = 0;

function openProfileView(id, pushHistory = true)
{
    listScrollY = window.scrollY;

    document.getElementById('proposalListView').hidden = true;
    document.getElementById('studentProfileView').hidden = false;
    window.scrollTo(0, 0);

    if (pushHistory) {
        history.pushState({ profile: Number(id) }, '', '#profile-' + id);
    }
}

function closeProfileView()
{
    document.getElementById('studentProfileView').hidden = true;
    document.getElementById('proposalListView').hidden = false;
    window.scrollTo(0, listScrollY);
}

document.getElementById('spBack').addEventListener('click', function () {
    if (history.state && history.state.profile) {
        history.back();            // popstate below closes the profile
    } else {
        closeProfileView();
        history.replaceState(null, '', location.pathname + location.search);
    }
});

document.addEventListener('DOMContentLoaded', function () {
    const m = location.hash.match(/^#profile-(\d+)$/);
    if (m) {
        history.replaceState({ profile: Number(m[1]) }, '', location.hash);
        showProfile(Number(m[1]), false);
    }
});

window.addEventListener('popstate', function (e) {
    if (e.state && e.state.profile) {
        showProfile(e.state.profile, false);
    } else {
        closeProfileView();
    }
});


/* ================================================================
   CONFIRM ACTION
================================================================ */

function askConfirmation(
    id,
    action
)
{

    const proposal =
        proposalData.find(
            p => Number(p.id) === Number(id)
        );


    if (!proposal) {
        return;
    }


    pendingAction = {
        id: Number(id),
        action: action
    };


    const confirmIcon =
        document.getElementById(
            'confirmIcon'
        );

    const confirmTitle =
        document.getElementById(
            'confirmTitle'
        );

    const confirmMessage =
        document.getElementById(
            'confirmMessage'
        );

    const confirmButton =
        document.getElementById(
            'confirmActionButton'
        );


    if (action === 'accept') {

        confirmIcon.textContent =
            'check_circle';

        confirmTitle.textContent =
            'Accept Proposal';

        confirmMessage.textContent =
            `Are you sure you want to accept ${proposal.name}'s proposal?`;

        confirmButton.textContent =
            'Accept';

        confirmButton.classList.remove(
            'danger'
        );

    }


    if (action === 'reject') {

        confirmIcon.textContent =
            'cancel';

        confirmTitle.textContent =
            'Reject Proposal';

        confirmMessage.textContent =
            `Are you sure you want to reject ${proposal.name}'s proposal?`;

        confirmButton.textContent =
            'Reject';

        confirmButton.classList.add(
            'danger'
        );

    }


    openModal('confirmModal');

}


/* ================================================================
   PERFORM ACTION
================================================================ */

function performAction()
{

    if (!pendingAction) {
        return;
    }


    const id =
        pendingAction.id;

    const action =
        pendingAction.action;


    let newStatus;


    if (action === 'accept') {
        newStatus = 'accepted';
    }

    if (action === 'reject') {
        newStatus = 'rejected';
    }


    const card =
        document.querySelector(
            `.pr-card[data-id="${id}"]`
        );


    // Close the confirmation straight away
    pendingAction = null;

    closeModal('confirmModal');


    if (card && newStatus) {

        updateCardStatus(
            card,
            newStatus,
            true
        );

    }


    pendingAction = null;

    closeModal('confirmModal');

}


/* ================================================================
   TOAST
================================================================ */

let toastTimer;


function showToast(message)
{

    const toast =
        document.getElementById(
            'prToast'
        );


    toast.textContent =
        message;


    toast.classList.add('show');


    clearTimeout(toastTimer);


    toastTimer =
        setTimeout(
            () => {

                toast.classList.remove(
                    'show'
                );

            },
            2500
        );

}


/* ================================================================
   INITIALS
================================================================ */

function getInitials(name)
{

    return name
        .trim()
        .split(/\s+/)
        .slice(0, 2)
        .map(
            part =>
                part.charAt(0).toUpperCase()
        )
        .join('');

}


/* ================================================================
   HTML ESCAPE
================================================================ */

function escapeHtml(value)
{

    return String(value)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');

}


/* ================================================================
   SEARCH EVENT
================================================================ */

searchInput.addEventListener(
    'input',
    applyFilters
);


/* ================================================================
   STATUS FILTER EVENT
================================================================ */

statusFilter.addEventListener(
    'change',
    applyFilters
);


/* ================================================================
   TIME FILTER EVENT
================================================================ */

timeFilter.addEventListener(
    'change',
    applyFilters
);


/* ================================================================
   SORT EVENT
================================================================ */

sortButton.addEventListener(
    'click',
    () => {

        newestFirst =
            !newestFirst;

        sortCards();

    }
);


/* ================================================================
   BUTTON EVENTS
================================================================ */

document.addEventListener(
    'click',
    function(event)
    {

        const fullProposalButton =
            event.target.closest(
                '.view-proposal-btn'
            );


        const profileButton =
            event.target.closest(
                '.view-profile-btn'
            );


        const acceptButton =
            event.target.closest(
                '.accept-btn'
            );


        const rejectButton =
            event.target.closest(
                '.reject-btn'
            );


        if (fullProposalButton) {

            showFullProposal(
                fullProposalButton.dataset.id
            );

            return;

        }


        if (profileButton) {

            showProfile(
                profileButton.dataset.id
            );

            return;

        }


        if (acceptButton) {

            if (!acceptButton.disabled) {

                askConfirmation(
                    acceptButton.dataset.id,
                    'accept'
                );

            }

            return;

        }


        if (rejectButton) {

            if (!rejectButton.disabled) {

                openRejectModal(
                    rejectButton.dataset.id
                );

            }

            return;

        }

    }
);


/* ================================================================
   REJECT WITH REASON
================================================================ */

let rejectingId = null;

const rjReason = document.getElementById('rjReason');
const rjError  = document.getElementById('rjError');
const rjSubmit = document.getElementById('rjSubmit');

function openRejectModal(id)
{
    const proposal = proposalData.find(p => Number(p.id) === Number(id));
    if (!proposal) {
        return;
    }

    rejectingId = Number(id);

    document.getElementById('rjStudent').textContent = proposal.name;
    document.getElementById('rjProject').textContent = proposal.project;

    rjReason.value = '';
    rjReason.classList.remove('invalid');
    rjError.textContent = '';
    document.getElementById('rjCount').textContent = '0';
    document.querySelectorAll('.rj-chip').forEach(c => c.classList.remove('picked'));

    rjSubmit.disabled = false;

    openModal('rejectModal');
    setTimeout(() => rjReason.focus(), 50);
}

// Quick reasons fill the textarea (can still be edited)
document.querySelectorAll('.rj-chip').forEach(function (chip) {
    chip.addEventListener('click', function () {
        document.querySelectorAll('.rj-chip').forEach(c => c.classList.remove('picked'));
        chip.classList.add('picked');
        rjReason.value = chip.textContent.trim();
        rjReason.dispatchEvent(new Event('input'));
        rjReason.focus();
    });
});

rjReason.addEventListener('input', function () {
    document.getElementById('rjCount').textContent = rjReason.value.length;
    if (rjReason.value.trim().length >= 10) {
        rjReason.classList.remove('invalid');
        rjError.textContent = '';
    }
});

rjSubmit.addEventListener('click', async function () {
    const reason = rjReason.value.trim();

    if (reason.length < 10) {
        rjReason.classList.add('invalid');
        rjError.textContent = 'Please write a reason (at least 10 characters).';
        rjReason.focus();
        return;
    }

    const proposal = proposalData.find(p => Number(p.id) === rejectingId);
    const card = document.querySelector(`.pr-card[data-id="${rejectingId}"]`);

    rjSubmit.disabled = true;
    const oldLabel = rjSubmit.innerHTML;
    rjSubmit.innerHTML = '<span class="material-symbols-outlined">hourglass_top</span> Rejecting…';

    try {
        const body = new FormData();
        body.append('action', 'reject_proposal');
        body.append('proposal_id', rejectingId);
        body.append('reason', reason);

        const res  = await fetch('proposal.php', { method: 'POST', body: body });
        const data = await res.json();

        if (!data.ok) {
            throw new Error(data.message || 'Something went wrong.');
        }

        // Rejected straight away + popup closes
        closeModal('rejectModal');

        if (card) {
            updateCardStatus(card, 'rejected', false);
        }

        showToast(`Proposal rejected. ${proposal ? proposal.name : 'The student'} has been notified.`);
        rejectingId = null;

    } catch (err) {
        rjError.textContent = err.message || 'Could not reject the proposal. Please try again.';
    } finally {
        rjSubmit.disabled = false;
        rjSubmit.innerHTML = oldLabel;
    }
});

document.getElementById('rejectModal').addEventListener('click', function (e) {
    if (e.target === this) {
        closeModal('rejectModal');
    }
});


/* ================================================================
   CONFIRM BUTTON
================================================================ */

document
    .getElementById('confirmActionButton')
    .addEventListener(
        'click',
        performAction
    );


/* ================================================================
   CLOSE MODAL BUTTONS
================================================================ */

document.addEventListener(
    'click',
    function(event)
    {

        const closeButton =
            event.target.closest(
                '[data-close-modal]'
            );


        if (!closeButton) {
            return;
        }


        closeModal(
            closeButton.dataset.closeModal
        );

    }
);


/* ================================================================
   CLOSE WHEN CLICKING OUTSIDE MODAL
================================================================ */

document
    .querySelectorAll('.pr-modal-overlay')
    .forEach(overlay => {

        overlay.addEventListener(
            'click',
            function(event)
            {

                if (event.target === overlay) {

                    closeModal(
                        overlay.id
                    );

                }

            }
        );

    });


/* ================================================================
   ESCAPE KEY
================================================================ */

document.addEventListener(
    'keydown',
    function(event)
    {

        if (event.key === 'Escape') {

            closeAllModals();

        }

    }
);


/* ================================================================
   INITIAL LOAD
================================================================ */

applySavedStatuses();

sortCards();

applyFilters();

</script>


<?php include "../../../Includes/dash_footer.php"; ?>