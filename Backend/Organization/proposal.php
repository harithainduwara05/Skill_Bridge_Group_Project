<?php
/*
| Organization backend - Proposals: student proposals list + Accept / Reject (AJAX, returns JSON)
| Used by: Functions/Organization/proposal.php
| (the page already loaded Config/db.php + Session/Session.php and checked the role)
*/

$user = current_user();
$organization_email = $user['email'];

require_once __DIR__ . "/org_db.php";

/*
|--------------------------------------------------------------------------
| PROPOSALS (from the database)
|--------------------------------------------------------------------------
| A student sends a proposal from the Student "Submit Proposal" page.
| It is saved in project_proposals (one row per student per project):
|   proposal_title, target_role, introduction (why I'm a good fit),
|   solution_approach (my approach), portfolio_attachment, status, submitted_at
| and its skills in proposal_skills (one row per skill).
| status: pending | accepted | rejected | withdrawn (withdrawn ones are not shown)
| Only proposals for THIS organization's projects are loaded.
*/
$proposals = [];
try {
    // the same names as before are used below (id, Email, description, solution, skills ...)
    $pq = $conn->prepare("SELECT pp.proposal_id AS id, pp.project_id, pp.student_email AS Email, pp.status,
                                 pp.submitted_at AS applied_at, pp.proposal_title,
                                 pp.introduction AS description, pp.solution_approach AS solution,
                                 (SELECT GROUP_CONCAT(ps.skill_name SEPARATOR ', ') FROM proposal_skills ps
                                   WHERE ps.proposal_id = pp.proposal_id) AS skills,
                                 pp.target_role AS role, pp.portfolio_attachment AS portfolio_file,
                                 p.title AS project_title
                          FROM project_proposals pp
                          JOIN projects p ON p.id = pp.project_id
                          WHERE p.organization_email = ? AND pp.status <> 'withdrawn'
                          ORDER BY pp.submitted_at DESC, pp.proposal_id DESC");
    $pq->bind_param("s", $organization_email);
    $pq->execute();
    $rows = $pq->get_result()->fetch_all(MYSQLI_ASSOC);
} catch (Throwable $e) {
    $rows = [];
}

/*
| Portfolio file the student attached (saved by the Student "Submit Proposal" page).
| Only files inside Assets/Uploads/ with a known type are shown:
|   pdf          -> preview inside the page
|   png/jpg/jpeg -> preview as an image
|   zip          -> can't be previewed, download only
*/
function prPortfolioInfo(?string $path): array
{
    $path = ltrim(str_replace('\\', '/', trim((string)$path)), '/');
    // only a file name saved (e.g. "portfolio_scanner.png") -> look in the proposal upload folders
    // (the Student "Apply" page saves them in Assets/Uploads/Student/Proposals/)
    if ($path !== '' && strpos($path, '/') === false) {
        $name = $path;
        $path = 'Assets/Uploads/proposals/' . $name;
        if (!is_file(__DIR__ . '/../../' . $path)) $path = 'Assets/Uploads/Student/Proposals/' . $name;
    }
    $ext  = strtolower(pathinfo($path, PATHINFO_EXTENSION));
    $types = ['pdf' => 'pdf', 'png' => 'image', 'jpg' => 'image', 'jpeg' => 'image', 'webp' => 'image', 'zip' => 'zip'];

    if ($path === '' || strpos($path, 'Assets/Uploads/') !== 0 || strpos($path, '..') !== false || !isset($types[$ext])
        || !is_file(__DIR__ . '/../../' . $path)) {          // the file must really be there
        return ['url' => '', 'type' => '', 'name' => ''];
    }
    return ['url' => '../../' . $path, 'type' => $types[$ext], 'name' => basename($path)];
}

/*
| One profile portfolio project -> what the card / preview popup needs.
| GitHub and most websites can't be opened INSIDE another page (they block it),
| so the preview shows the project details and a button that opens the link.
*/
function prPortfolioItem(array $pf): array
{
    $link = trim((string)($pf['project_link'] ?? ''));
    $link = preg_match('#^https?://#i', $link) ? $link : '';      // only real web links

    // screenshot: only shown if the file really exists.
    // The Student Portfolio page saves the full path (Assets/Images/Student/Portfolio/<file>);
    // older rows have only the file name -> look in the upload folders.
    $image = '';
    $raw   = ltrim(str_replace('\\', '/', trim((string)($pf['image'] ?? ''))), '/');
    $img   = basename($raw);
    $tries = strpos($raw, '/') !== false && strpos($raw, '..') === false ? [$raw] : [];
    foreach (['Assets/Images/Student/Portfolio/', 'Assets/Uploads/portfolio/', 'Assets/Images/Student/portfolio/'] as $dir) {
        $tries[] = $dir . $img;
    }
    foreach ($tries as $path) {
        if ($img !== '' && preg_match('/\.(png|jpe?g|webp|gif)$/i', $img) && is_file(__DIR__ . '/../../' . $path)) {
            $image = '../../' . $path;
            break;
        }
    }

    // "Sep 2026 - Present" / "Jun 2026 - Aug 2026"
    $period = '';
    if (!empty($pf['start_date'])) {
        $period = date('M Y', strtotime($pf['start_date'])) . ' - '
                . (!empty($pf['end_date']) ? date('M Y', strtotime($pf['end_date'])) : 'Present');
    }

    return [
        'id'          => (int)$pf['portfolio_id'],
        'title'       => $pf['title'] ?: 'Untitled project',
        'description' => $pf['description'] ?? '',
        'link'        => $link,
        'link_label'  => stripos($link, 'github.com') !== false ? 'View on GitHub' : 'Open project',
        'image'       => $image,
        'status'      => $pf['project_status'] ?? '',            // Completed / Currently Working
        'period'      => $period,
        'tech'        => array_values(array_filter(array_map('trim', explode(',', (string)($pf['technologies'] ?? ''))))),
    ];
}

foreach ($rows as $r) {
    $status   = strtolower(trim($r['status'] ?? 'pending'));
    $pf       = prPortfolioInfo($r['portfolio_file'] ?? '');
    $appliedT = strtotime($r['applied_at'] ?? '') ?: time();
    $skills   = array_values(array_filter(array_map('trim', explode(',', (string)($r['skills'] ?? '')))));

    $proposals[] = [
        'id'           => (int)$r['id'],
        'project_id'   => (int)$r['project_id'],
        'email'        => $r['Email'],
        'name'         => $r['Email'],          // replaced with the real name below
        'school'       => '',
        'status'       => in_array($status, ['pending', 'accepted', 'rejected'], true) ? $status : 'pending',
        'db_status'    => $status,
        'project'      => $r['project_title'],
        'title'        => $r['proposal_title'] ?? '',
        'skills'       => $skills,
        // "Proposal Summary" = the student's approach; "Why I'm a Good Fit" = their introduction
        'summary'      => trim($r['solution'] ?? '') !== '' ? $r['solution']
                          : (trim($r['description'] ?? '') !== '' ? $r['description'] : 'The student did not add a proposal text.'),
        'motivation'   => trim($r['solution'] ?? '') !== '' ? ($r['description'] ?? '') : '',
        'role'         => $r['role'] ?? '',
        'portfolio'      => $pf['url'],
        'portfolio_type' => $pf['type'],    // pdf | image | zip | ''
        'portfolio_name' => $pf['name'],
        'portfolio_count'=> 0,              // projects in the student's profile, filled below
        'portfolio_ids'  => [],             // (project_proposals has no picked profile projects -> latest 3 are shown)
        'portfolio_items'  => [],           // project cards shown in "Portfolio Preview", filled below
        'portfolio_picked' => false,        // true = the student chose these projects for this proposal
        'days_ago'     => max(0, (int)floor((time() - $appliedT) / 86400)),
        'submitted_on' => date('M d, Y', $appliedT),
        'plan'         => [],
        'availability' => '',
        'start'        => '',
        'estimate'     => '',
    ];
}

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
| Student rating = the feedback that organizations gave (Feedback page -> project_feedback)
| to the teams this student was a member of (org_team_members), from ANY organization.
| So every organization can see how the student did before, on the proposal and on the profile.
| (Feedback is given to the whole team, so each member of the team gets that rating.)
*/
function prStudentRating(mysqli $conn, string $email): array
{
    $rating = ['avg' => null, 'count' => 0, 'technical' => null, 'communication' => null,
               'teamwork' => null, 'problem_solving' => null, 'reviews' => []];
    try {
        $q = $conn->prepare("SELECT COUNT(*) AS n, ROUND(AVG(f.rating), 1) AS avg_rating,
                                    ROUND(AVG(f.technical), 1) AS technical, ROUND(AVG(f.communication), 1) AS communication,
                                    ROUND(AVG(f.teamwork), 1) AS teamwork, ROUND(AVG(f.problem_solving), 1) AS problem_solving
                             FROM project_feedback f
                             JOIN org_team_members m ON m.team_id = f.team_id
                             WHERE LOWER(m.Email) = LOWER(?)");
        $q->bind_param("s", $email);
        $q->execute();
        $r = $q->get_result()->fetch_assoc();
        if ($r && (int)$r['n'] > 0) {
            $rating['count'] = (int)$r['n'];
            $rating['avg']   = (float)$r['avg_rating'];
            foreach (['technical', 'communication', 'teamwork', 'problem_solving'] as $k) $rating[$k] = (float)$r[$k];

            // the latest 5 reviews: which organization, which project, the student's role, stars and summary
            $q = $conn->prepare("SELECT f.rating, f.summary, f.created_at, p.title AS project, o.Name AS organization, m.role
                                 FROM project_feedback f
                                 JOIN org_team_members m  ON m.team_id = f.team_id
                                 JOIN org_teams t         ON t.id = f.team_id
                                 JOIN projects p          ON p.id = t.project_id
                                 LEFT JOIN organization o ON o.Email = p.organization_email
                                 WHERE LOWER(m.Email) = LOWER(?)
                                 ORDER BY f.created_at DESC LIMIT 5");
            $q->bind_param("s", $email);
            $q->execute();
            foreach ($q->get_result()->fetch_all(MYSQLI_ASSOC) as $rv) {
                $rating['reviews'][] = ['rating' => (int)$rv['rating'], 'summary' => $rv['summary'],
                                        'project' => $rv['project'], 'organization' => $rv['organization'] ?? '',
                                        'role' => $rv['role'] ?? '', 'date' => date('M d, Y', strtotime($rv['created_at']))];
            }
        }
    } catch (Throwable $e) {
        // no rating shown
    }
    return $rating;
}

$projectDetails = [];   // title => details, for the "Applied project" box
$studentProfiles = [];
foreach ($proposals as &$prop) {
    if (!isset($projectDetails[$prop['project']])) {
        $pd = null;
        try {
            $dq = $conn->prepare(ORG_PROJECT_SELECT . " WHERE p.id = ? LIMIT 1");
            $dq->bind_param("i", $prop['project_id']);
            $dq->execute();
            $pd = $dq->get_result()->fetch_assoc();
        } catch (Throwable $e) {}
        if ($pd && !empty($pd['deadline']) && strtotime($pd['deadline'])) {
            $pd['deadline'] = date('M d, Y', strtotime($pd['deadline']));
        }
        $projectDetails[$prop['project']] = $pd;
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

        // the student's portfolio projects (Student -> Portfolio page saves them in student_portfolio_projects:
        // repository link, screenshot, status, dates and the technologies used)
        'portfolio' => prFetchAll($conn, "SELECT pp.portfolio_project_id AS portfolio_id, pp.title, pp.description,
                                                 pp.repository_url AS project_link, pp.project_image AS image,
                                                 pp.project_status, pp.start_date, pp.end_date,
                                                 (SELECT GROUP_CONCAT(t.technology_name ORDER BY t.technology_name SEPARATOR ', ')
                                                    FROM student_portfolio_project_technologies pt
                                                    JOIN student_technologies t ON t.technology_id = pt.technology_id
                                                   WHERE pt.portfolio_project_id = pp.portfolio_project_id) AS technologies
                                          FROM student_portfolio_projects pp
                                          WHERE pp.student_email = ?
                                          ORDER BY pp.created_at DESC, pp.portfolio_project_id DESC", $email),

        'projects' => prFetchAll($conn, "SELECT p.title, o.Name AS company, c.category_name AS category,
                                                sp.role, sp.progress, sp.status
                                         FROM student_projects sp
                                         JOIN projects p ON p.id = sp.project_id
                                         LEFT JOIN organization o ON o.Email = p.organization_email
                                         LEFT JOIN project_categories c ON c.category_id = p.category_id
                                         WHERE sp.Email = ?
                                         ORDER BY sp.student_project_id DESC", $email),

        'certificates' => prFetchAll($conn, "SELECT certificate_name, issuer, status
                                             FROM certificates WHERE Email = ? ORDER BY certificate_id DESC", $email),

        'rating' => prStudentRating($conn, $email),
    ];
    // for the proposal card
    $prop['rating_avg']   = $studentProfiles[$email]['rating']['avg'];
    $prop['rating_count'] = $studentProfiles[$email]['rating']['count'];

    /*
    | "Portfolio Preview" projects (like Upwork / Handshake):
    |  1. the profile projects the student PICKED for this proposal (portfolio_ids)
    |  2. if they picked none -> their 3 latest profile projects
    */
    $all    = $studentProfiles[$email]['portfolio'];
    $picked = array_values(array_filter($all, fn($pf) => in_array((int)$pf['portfolio_id'], $prop['portfolio_ids'], true)));
    $prop['portfolio_picked'] = count($picked) > 0;
    $prop['portfolio_count']  = count($all);
    $prop['portfolio_items']  = array_map('prPortfolioItem', $prop['portfolio_picked'] ? $picked : array_slice($all, 0, 3));
    $studentProfiles[$email]['portfolio'] = array_map('prPortfolioItem', $all);   // same details in the profile
}
unset($prop);

/*
|--------------------------------------------------------------------------
| ACCEPT PROPOSAL (AJAX)
|--------------------------------------------------------------------------
| Same as Reject: the organization writes a message and the student gets a
| notification with it. The proposal is saved as "accepted" (project_proposals),
| so the student can be picked when creating a team.
| silent=1 -> only sync the status, no notification (used for old
| decisions that were saved only in the browser).
*/
/*
| Portfolio PDF for the preview popup (AJAX, returns JSON).
| The file is sent as text (base64) inside JSON, not as a .pdf link, so a download manager
| (e.g. IDM catches every PDF the browser loads) can't take it away from the preview.
| Only a proposal of THIS organization's projects ($proposals above) can be read.
*/
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'portfolio_file') {
    header('Content-Type: application/json');
    $pid = (int)($_POST['proposal_id'] ?? 0);
    $found = null;
    foreach ($proposals as $pr) {
        if ($pr['id'] === $pid) { $found = $pr; break; }
    }
    $file = ($found && $found['portfolio_type'] === 'pdf')
          ? __DIR__ . '/../../' . substr($found['portfolio'], strlen('../../'))   // '../../Assets/...' -> real path
          : '';
    if ($file === '' || !is_file($file)) {
        echo json_encode(['ok' => false, 'message' => 'The file was not found.']);
        exit;
    }
    echo json_encode(['ok' => true, 'data' => base64_encode(file_get_contents($file))]);
    exit;
}

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

    // A proposal can be accepted only while the project is PUBLISHED (accepted by the Admin)
    // and its team is not full yet (not Reviewing / Hold / Rejected / Completed / full Active).
    $openError = prProjectOpenError($conn, (int)$proposal['project_id']);
    if ($openError !== '') {
        echo json_encode(['ok' => false, 'message' => $openError]);
        exit;
    }

    if (!$silent && mb_strlen($message) < 10) {
        echo json_encode(['ok' => false, 'message' => 'Please write a message (at least 10 characters).']);
        exit;
    }
    $message = mb_substr($message, 0, 500);

    try {
        // remember the decision -> the student can be added to a team for this project
        $linked = true;
        prSaveDecision($conn, (int)$proposal['id'], 'accepted', $silent ? null : $message);

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
        prSaveDecision($conn, (int)$proposal['id'], 'rejected', $reason);

        echo json_encode(['ok' => true]);
    } catch (Throwable $e) {
        echo json_encode(['ok' => false, 'message' => 'Could not send the notification. Please try again.']);
    }
    exit;
}

/*
| Save Accept / Reject on the proposal: status, the message sent to the student, and when.
| ($message null = keep the message that is already there)
*/
function prSaveDecision(mysqli $conn, int $proposalId, string $status, ?string $message): void
{
    $q = $conn->prepare("UPDATE project_proposals
                         SET status = ?, feedback_message = COALESCE(?, feedback_message), reviewed_at = NOW()
                         WHERE proposal_id = ?");
    $q->bind_param("ssi", $status, $message, $proposalId);
    $q->execute();
}

/*
| Is the project still taking students?  '' = yes, otherwise the reason why not.
| Same status rules as Manage Projects: the Admin's 'Active' / old 'accepted' = Published,
| and an 'inprogress' (Active) project whose team is not full is treated as Published.
*/
function prProjectOpenError(mysqli $conn, int $projectId): string
{
    $q = $conn->prepare("SELECT status, members,
                                (SELECT COUNT(*) FROM student_projects sp WHERE sp.project_id = p.id) AS in_team
                         FROM projects p WHERE id = ?");
    $q->bind_param("i", $projectId);
    $q->execute();
    $p = $q->get_result()->fetch_assoc();
    if (!$p) return 'This project no longer exists.';

    $status = strtolower(trim($p['status'] ?? ''));
    if (in_array($status, ['active', 'approved', 'accepted'], true)) $status = 'published';
    $full = (int)$p['in_team'] >= max(1, (int)$p['members']);

    if ($status === 'published' || ($status === 'inprogress' && !$full)) {
        return $full ? 'The team for this project is already full, so no more students can be accepted.' : '';
    }
    $labels = ['reviewing' => 'still waiting for the Admin', 'hold' => 'On Hold', 'rejected' => 'Rejected',
               'completed' => 'Completed', 'inprogress' => 'Active with a full team', 'draft' => 'a Draft'];
    return 'This project is ' . ($labels[$status] ?? ucfirst($status)) . ', so new students can not be accepted.';
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