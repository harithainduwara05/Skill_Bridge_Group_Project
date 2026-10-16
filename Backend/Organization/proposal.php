<?php
/*
| Organization backend - Proposals: student proposals list + Accept / Reject (AJAX, returns JSON)
| Used by: Functions/Organization/proposal.php
| (the page already loaded Config/db.php + Session/Session.php and checked the role)
*/

$user = current_user();
$organization_email = $user['email'];

require_once __DIR__ . "/team_schema.php";
tmEnsureSchema($conn);

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

/*
| Demo proposals are written for sample project names. So that they work for
| ANY organization (and the students can be put into a team), each proposal
| is linked to one of the logged-in organization's own projects:
|   - same title -> that project
|   - otherwise  -> the organization's open / active projects, in turn
*/
$prOrgProjects = [];
try {
    $oq = $conn->prepare("SELECT title, status FROM projects WHERE organization_email = ? ORDER BY posted_at DESC, id DESC");
    $oq->bind_param("s", $organization_email);
    $oq->execute();
    $prOrgProjects = $oq->get_result()->fetch_all(MYSQLI_ASSOC);
} catch (Throwable $e) {}
$prOrgTitles = array_column($prOrgProjects, 'title');
$prOpenTitles = array_column(array_filter($prOrgProjects, fn($r) => in_array($r['status'], ['open', 'reviewing', 'inprogress'], true)), 'title')
                ?: $prOrgTitles;
$prNext = 0;
foreach ($proposals as &$prop) {
    if ($prOpenTitles && !in_array($prop['project'], $prOrgTitles, true)) {
        $prop['project'] = $prOpenTitles[$prNext++ % count($prOpenTitles)];
    }
}
unset($prop);

/*
| DEMO RESET: open proposal.php?reset_demo=1
| Removes the Accept / Reject decisions saved for these proposals (database +
| browser), so every proposal goes back to how it starts and the Accept /
| Reject buttons show again. Teams that were already created are not touched.
*/
if (isset($_GET['reset_demo'])) {
    foreach ($proposals as $rp) {
        try {
            $dq = $conn->prepare("DELETE pa FROM project_applications pa
                                  JOIN projects p ON p.id = pa.project_id
                                  WHERE p.title = ? AND p.organization_email = ? AND LOWER(pa.Email) = LOWER(?)");
            $dq->bind_param("sss", $rp['project'], $organization_email, $rp['email']);
            $dq->execute();
        } catch (Throwable $e) {}
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

    // Link the proposal to this organization's project (by title) and
    // use the decision saved in the database (Accept / Reject) if there is one.
    $prop['project_id'] = 0;
    try {
        $pq = $conn->prepare("SELECT id FROM projects WHERE title = ? AND organization_email = ? LIMIT 1");
        $pq->bind_param("ss", $prop['project'], $organization_email);
        $pq->execute();
        $prop['project_id'] = (int)($pq->get_result()->fetch_assoc()['id'] ?? 0);
    } catch (Throwable $e) {}
    $prop['db_status'] = '';
    if ($prop['project_id']) {
        try {
            $sq = $conn->prepare("SELECT status FROM project_applications WHERE project_id = ? AND LOWER(Email) = LOWER(?) LIMIT 1");
            $sq->bind_param("is", $prop['project_id'], $prop['email']);
            $sq->execute();
            $prop['db_status'] = $sq->get_result()->fetch_assoc()['status'] ?? '';
        } catch (Throwable $e) {}
        if (in_array($prop['db_status'], ['accepted', 'rejected'], true)) {
            $prop['status'] = $prop['db_status'];
        } elseif ($prop['status'] === 'accepted') {
            // demo proposal that starts as "accepted" -> save it so Teams can use it
            try { tmSaveApplicationStatus($conn, $prop['project_id'], $prop['email'], 'accepted'); $prop['db_status'] = 'accepted'; }
            catch (Throwable $e) {}
        }
    }

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
        'image'      => !empty($stu['profile_image']) ? '../../Assets/Images/Student/' . $stu['profile_image'] : '',
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
| ACCEPT PROPOSAL (AJAX)
|--------------------------------------------------------------------------
| Same as Reject: the organization writes a message and the student gets a
| notification with it. The student is saved as "accepted" for the project
| (project_applications), so they can be picked when creating a team.
| silent=1 -> only sync the status, no notification (used for old
| decisions that were saved only in the browser).
*/
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'accept_proposal') {
    header('Content-Type: application/json');

    $proposalId = (int)($_POST['proposal_id'] ?? 0);
    $silent     = !empty($_POST['silent']);
    $message    = trim($_POST['message'] ?? '');

    $proposal = null;
    foreach ($proposals as $item) {
        if ((int)$item['id'] === $proposalId) { $proposal = $item; break; }
    }
    if (!$proposal) {
        echo json_encode(['ok' => false, 'message' => 'Proposal not found.']);
        exit;
    }
    if (!$silent && mb_strlen($message) < 10) {
        echo json_encode(['ok' => false, 'message' => 'Please write a message (at least 10 characters).']);
        exit;
    }
    $message = mb_substr($message, 0, 500);

    try {
        // remember the decision -> the student can be added to a team for this project
        $linked = !empty($proposal['project_id']);
        if ($linked) {
            tmSaveApplicationStatus($conn, (int)$proposal['project_id'], $proposal['email'], 'accepted');
        }

        if (!$silent) {
            $on = $conn->prepare("SELECT Name FROM organization WHERE Email = ?");
            $on->bind_param("s", $organization_email);
            $on->execute();
            $orgName = $on->get_result()->fetch_assoc()['Name'] ?? 'The organization';

            $nTitle   = 'Proposal Accepted';
            $nMessage = $orgName . ' accepted your proposal for "' . $proposal['project'] . '". '
                      . 'Message: ' . $message;
            $nq = $conn->prepare("INSERT INTO notifications (Email, title, message, type, status)
                                  VALUES (?, ?, ?, 'project', 'Unread')");
            $nq->bind_param("sss", $proposal['email'], $nTitle, $nMessage);
            $nq->execute();
        }

        echo json_encode(['ok' => true, 'linked' => $linked]);
    } catch (Throwable $e) {
        echo json_encode(['ok' => false, 'message' => 'Could not accept the proposal. Please try again.']);
    }
    exit;
}

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

        // remember the decision (a rejected student can't be added to a team)
        if (!empty($proposal['project_id'])) {
            tmSaveApplicationStatus($conn, (int)$proposal['project_id'], $proposal['email'], 'rejected');
        }

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