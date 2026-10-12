<?php
/*
| Organization backend - Feedback: submit feedback for a team + list of given feedback (+ demo reviews)
| Used by: Functions/Organization/feedback.php
| (the page already loaded Config/db.php + Session/Session.php and checked the role)
*/

$user = current_user();
$organization_email = $user['email'];

function fbFetch(mysqli $conn, string $sql, string $types = '', array $params = []): array
{
    try {
        $st = $conn->prepare($sql);
        if ($types !== '') $st->bind_param($types, ...$params);
        $st->execute();
        $res = $st->get_result();
        return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    } catch (Throwable $e) {
        return [];
    }
}

// ---------------------------------------------------------------
// Projects that can get feedback = this organization's projects that
// already have students working on them. Team name comes from Teams
// (org_teams) when one exists, otherwise "<project> Team".
// ---------------------------------------------------------------
$fbProjects = [];
foreach (fbFetch($conn,
    "SELECT p.id, p.title, p.category, p.status
     FROM projects p
     WHERE p.organization_email = ?
       AND EXISTS (SELECT 1 FROM student_projects sp WHERE sp.project_id = p.id)
     ORDER BY p.posted_at DESC", "s", [$organization_email]) as $pr) {

    $team = fbFetch($conn, "SELECT name FROM org_teams WHERE project_id = ? AND organization_email = ? ORDER BY created_at DESC LIMIT 1",
                    "is", [(int)$pr['id'], $organization_email])[0]['name'] ?? ($pr['title'] . ' Team');
    $students = fbFetch($conn,
        "SELECT s.Name AS name, sp.role FROM student_projects sp JOIN student s ON s.Email = sp.Email WHERE sp.project_id = ?",
        "i", [(int)$pr['id']]);

    $fbProjects[] = [
        'id' => (int)$pr['id'], 'title' => $pr['title'], 'category' => $pr['category'],
        'status' => $pr['status'], 'team' => $team, 'students' => $students,
    ];
}

// ---------------------------------------------------------------
// Save new feedback
// ---------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'submit_feedback') {
    $pid     = (int)($_POST['project_id'] ?? 0);
    $rating  = (int)($_POST['rating'] ?? 0);
    $scores  = [];
    foreach (['technical', 'communication', 'teamwork', 'problem_solving'] as $k) {
        $scores[$k] = round((float)($_POST[$k] ?? 0) * 2) / 2;      // steps of 0.5
    }
    $summaryText  = trim($_POST['summary'] ?? '');
    $improvements = implode(', ', array_slice(array_filter(array_map('trim', explode(',', $_POST['improvements'] ?? ''))), 0, 8));
    $share        = 1;   // the team always gets a notification when feedback is submitted

    $project = null;
    foreach ($fbProjects as $fp) { if ($fp['id'] === $pid) { $project = $fp; break; } }

    $scoresOk = count(array_filter($scores, fn($v) => $v >= 1 && $v <= 5)) === 4;
    if (!$project || $rating < 1 || $rating > 5 || !$scoresOk || mb_strlen($summaryText) < 20) {
        header("Location: feedback.php?fb_error=1");
        exit;
    }

    try {
        $q = $conn->prepare("INSERT INTO project_feedback
            (organization_email, project_id, team_name, rating, technical, communication, teamwork, problem_solving, summary, improvements, shared_with_students)
            VALUES (?,?,?,?,?,?,?,?,?,?,?)");
        $q->bind_param("sisiddddssi", $organization_email, $pid, $project['team'], $rating,
            $scores['technical'], $scores['communication'], $scores['teamwork'], $scores['problem_solving'],
            $summaryText, $improvements, $share);
        $q->execute();

        if ($share) {
            $orgName = fbFetch($conn, "SELECT Name FROM organization WHERE Email = ?", "s", [$organization_email])[0]['Name'] ?? 'The organization';
            $nTitle = 'New Project Feedback';
            $nMsg   = $orgName . ' gave your team ' . $rating . '/5 for "' . $project['title'] . '". Open your project to read the full feedback.';
            $n = $conn->prepare("INSERT INTO notifications (Email, title, message, type, status)
                                 SELECT Email, ?, ?, 'project', 'Unread' FROM student_projects WHERE project_id = ?");
            $n->bind_param("ssi", $nTitle, $nMsg, $pid);
            $n->execute();
        }
        header("Location: feedback.php?fb_saved=1");
    } catch (Throwable $e) {
        header("Location: feedback.php?fb_error=1");
    }
    exit;
}

$flash = null;
if (isset($_GET['fb_saved'])) $flash = ['type' => 'success', 'title' => 'Feedback submitted', 'message' => 'Your feedback was saved and shared with the team.'];
if (isset($_GET['fb_error'])) $flash = ['type' => 'error', 'title' => 'Could not submit feedback', 'message' => 'Please fill all required fields and try again.'];

// ---------------------------------------------------------------
// Feedback saved in the database (shown first)
// ---------------------------------------------------------------
$dbReviews = [];
$catIcon = ['Web Development' => 'language', 'Mobile Development' => 'smartphone', 'AI / Machine Learning' => 'psychology',
            'Data Science' => 'monitoring', 'UI/UX Design' => 'palette', 'Cloud & DevOps' => 'cloud', 'Cybersecurity' => 'shield'];
foreach (fbFetch($conn,
    "SELECT f.*, p.title, p.category FROM project_feedback f JOIN projects p ON p.id = f.project_id
     WHERE f.organization_email = ? ORDER BY f.created_at DESC", "s", [$organization_email]) as $f) {
    $dbReviews[] = [
        'icon' => $catIcon[$f['category']] ?? 'folder', 'title' => $f['title'], 'team' => $f['team_name'],
        'days_ago' => max(0, (int)floor((time() - strtotime($f['created_at'])) / 86400)),
        'created' => strtotime($f['created_at']),
        'rating' => (int)$f['rating'],
        'skills' => ['Technical Skills' => (float)$f['technical'], 'Communication' => (float)$f['communication'],
                     'Teamwork' => (float)$f['teamwork'], 'Problem-solving' => (float)$f['problem_solving']],
        'summary' => $f['summary'],
        'improvements' => array_values(array_filter(array_map('trim', explode(',', $f['improvements'] ?? '')))),
        'is_new' => true,
        'id' => (int)$f['id'], 'key' => 'db-' . (int)$f['id'], 'project_id' => (int)$f['project_id'],
        'category' => $f['category'], 'shared' => (bool)$f['shared_with_students'],
        'updated' => !empty($f['updated_at']) ? date('M d, Y', strtotime($f['updated_at'])) : '',
        'members' => fbFetch($conn, "SELECT s.Name AS name, sp.role FROM student_projects sp JOIN student s ON s.Email = sp.Email WHERE sp.project_id = ?",
                             "i", [(int)$f['project_id']]),
    ];
}

// ---------------------------------------------------------------
// UI-ONLY DEMO DATA (kept for the demo)
// ---------------------------------------------------------------
$summary = [
    'submitted' => 42, 'submitted_change' => '+12%',
    'pending'   => 5,
    'avg'       => 4.8,
    'completed' => 47,
];

$reviews = [
    [
        'icon' => 'cloud', 'title' => 'Cloud Architecture Redesign', 'team' => 'Skyline Team', 'days_ago' => 5,
        'rating' => 5,
        'skills' => ['Technical Skills' => 5.0, 'Communication' => 4.5, 'Teamwork' => 4.8, 'Problem-solving' => 5.0],
        'summary' => 'The team demonstrated exceptional mastery of Azure serverless components. Their ability to migrate the legacy SQL structure into a distributed CosmosDB environment with zero downtime was a significant technical achievement.',
        'improvements' => ['Docker Optimization', 'Agile Documentation', 'Terraform Modularization'],
        'key' => 'demo-cloud', 'category' => 'Cloud & DevOps', 'shared' => true, 'updated' => '',
        'members' => [['name' => 'Tharindu Rajapaksha', 'role' => 'Cloud Architect'], ['name' => 'Hiruni Fernando', 'role' => 'Backend Developer'],
                      ['name' => 'Janith Wickramasinghe', 'role' => 'Database Engineer'], ['name' => 'Oshadi Senarath', 'role' => 'DevOps Engineer']],
    ],
    [
        'icon' => 'api', 'title' => 'Secure API Gateway Implementation', 'team' => 'Omega Devs', 'days_ago' => 21,
        'rating' => 4,
        'skills' => ['Technical Skills' => 4.2, 'Communication' => 4.0, 'Teamwork' => 4.5, 'Problem-solving' => 4.0],
        'summary' => 'Strong execution of the security protocols and OAuth2 implementation. The team was highly responsive to feedback regarding the latency issues in the initial staging environment and optimized the caching layer effectively.',
        'improvements' => ['Security Auditing', 'GraphQL Optimization'],
        'key' => 'demo-api', 'category' => 'Cybersecurity', 'shared' => true, 'updated' => '',
        'members' => [['name' => 'Ravindu Gamage', 'role' => 'Security Engineer'], ['name' => 'Sithumi Perera', 'role' => 'Backend Developer'],
                      ['name' => 'Kaveesha Jayalath', 'role' => 'QA Engineer']],
    ],
    [
        'icon' => 'smartphone', 'title' => 'Campus Event Management App', 'team' => 'Pixel Pioneers', 'days_ago' => 28,
        'rating' => 5,
        'skills' => ['Technical Skills' => 4.8, 'Communication' => 4.6, 'Teamwork' => 5.0, 'Problem-solving' => 4.7],
        'summary' => 'A polished Flutter app with QR check-in that worked smoothly during a live faculty event. The team planned their sprints well and handled last-minute changes calmly.',
        'improvements' => ['Accessibility', 'Automated UI Tests'],
        'key' => 'demo-event', 'category' => 'Mobile Development', 'shared' => true, 'updated' => '',
        'members' => [['name' => 'Nadeesha Kumari', 'role' => 'Mobile Developer'], ['name' => 'Chathura Bandara', 'role' => 'Backend Developer'], ['name' => 'Isuri Madushani', 'role' => 'UI/UX Designer']],
    ],
    [
        'icon' => 'psychology', 'title' => 'Sinhala Sentiment Analysis Model', 'team' => 'Lanka NLP Crew', 'days_ago' => 35,
        'rating' => 4,
        'skills' => ['Technical Skills' => 4.5, 'Communication' => 3.8, 'Teamwork' => 4.2, 'Problem-solving' => 4.4],
        'summary' => 'Good model accuracy on a difficult local-language dataset. Documentation of the data cleaning steps was limited, which made it harder for us to reproduce the results.',
        'improvements' => ['Documentation', 'Experiment Tracking', 'Data Versioning'],
        'key' => 'demo-nlp', 'category' => 'AI / Machine Learning', 'shared' => true, 'updated' => '',
        'members' => [['name' => 'Dilshan Rathnayake', 'role' => 'ML Engineer'], ['name' => 'Sanduni Wijesekara', 'role' => 'Data Analyst'], ['name' => 'Pubudu Herath', 'role' => 'Backend Developer']],
    ],
    [
        'icon' => 'language', 'title' => 'Community Library Website', 'team' => 'Code Crafters', 'days_ago' => 42,
        'rating' => 3,
        'skills' => ['Technical Skills' => 3.4, 'Communication' => 3.0, 'Teamwork' => 3.5, 'Problem-solving' => 3.2],
        'summary' => 'The site meets the basic requirements, but several features were delivered late and the search page still has performance issues. Communication improved towards the end.',
        'improvements' => ['Time Management', 'Performance Tuning', 'Code Reviews'],
        'key' => 'demo-library', 'category' => 'Web Development', 'shared' => true, 'updated' => '',
        'members' => [['name' => 'Kasun Dissanayake', 'role' => 'Frontend Developer'], ['name' => 'Hansani Peiris', 'role' => 'Backend Developer']],
    ],
    [
        'icon' => 'monitoring', 'title' => 'Sales Forecast Dashboard', 'team' => 'Data Dynamos', 'days_ago' => 56,
        'rating' => 4,
        'skills' => ['Technical Skills' => 4.3, 'Communication' => 4.4, 'Teamwork' => 4.0, 'Problem-solving' => 4.1],
        'summary' => 'Clear and useful Power BI dashboards. Forecasts were reasonable, and the team explained their assumptions well in the final presentation.',
        'improvements' => ['SQL Optimization', 'Unit Testing'],
        'key' => 'demo-sales', 'category' => 'Data Science', 'shared' => true, 'updated' => '',
        'members' => [['name' => 'Tharaka Liyanage', 'role' => 'Data Analyst'], ['name' => 'Rashmi Gunasekara', 'role' => 'BI Developer'], ['name' => 'Akila Perera', 'role' => 'Data Engineer']],
    ],
    [
        'icon' => 'palette', 'title' => 'Tourism App UI Redesign', 'team' => 'Design Pulse', 'days_ago' => 70,
        'rating' => 5,
        'skills' => ['Technical Skills' => 4.6, 'Communication' => 5.0, 'Teamwork' => 4.8, 'Problem-solving' => 4.7],
        'summary' => 'Outstanding user research and a beautiful, consistent design system. The prototype tested very well with real tourists visiting Kandy.',
        'improvements' => ['Design Handoff', 'Git Workflow'],
        'key' => 'demo-tourism', 'category' => 'UI/UX Design', 'shared' => true, 'updated' => '',
        'members' => [['name' => 'Sachintha Fonseka', 'role' => 'UI/UX Designer'], ['name' => 'Imesha Ranasinghe', 'role' => 'UX Researcher']],
    ],
    [
        'icon' => 'shield', 'title' => 'Network Intrusion Detection Lab', 'team' => 'Cipher Squad', 'days_ago' => 84,
        'rating' => 4,
        'skills' => ['Technical Skills' => 4.4, 'Communication' => 3.9, 'Teamwork' => 4.1, 'Problem-solving' => 4.5],
        'summary' => 'Solid detection rules and a well-built lab setup. The final report could explain the false-positive analysis in more detail.',
        'improvements' => ['Report Writing', 'Threat Modelling'],
        'key' => 'demo-ids', 'category' => 'Cybersecurity', 'shared' => true, 'updated' => '',
        'members' => [['name' => 'Pasan Jayawardena', 'role' => 'Security Analyst'], ['name' => 'Nethmi Abeyratne', 'role' => 'Network Engineer'], ['name' => 'Lahiru Silva', 'role' => 'Python Developer']],
    ],
];

foreach ($reviews as &$rv) { $rv['created'] = strtotime('-' . (int)$rv['days_ago'] . ' day'); }
unset($rv);
$reviews = array_merge($dbReviews, $reviews);

// stat cards include the saved feedback
if ($dbReviews) {
    $allRatings = array_merge(array_fill(0, (int)$summary['submitted'], (float)$summary['avg']), array_column($dbReviews, 'rating'));
    $summary['avg'] = round(array_sum($allRatings) / count($allRatings), 1);
    $summary['submitted'] += count($dbReviews);
}

$ratingLabels = [5 => 'Exceptional', 4 => 'Very Good', 3 => 'Good', 2 => 'Fair', 1 => 'Needs Work'];

// star icons: full / half / empty
function fbStars($rating, $size = 20) {
    $html = '';
    for ($i = 1; $i <= 5; $i++) {
        if ($rating >= $i)            { $html .= '<span class="material-symbols-outlined fb-star on" style="font-size:' . $size . 'px;">star</span>'; }
        elseif ($rating >= $i - 0.5)  { $html .= '<span class="material-symbols-outlined fb-star on" style="font-size:' . $size . 'px;">star_half</span>'; }
        else                          { $html .= '<span class="material-symbols-outlined fb-star" style="font-size:' . $size . 'px;">star</span>'; }
    }
    return $html;
}