<?php
/**
 * ==============================================================================
 * SkillBridge - Help & Support Center
 * User-facing portal for lodging formal complaints, tracking resolution progress,
 * and accessing self-help knowledge guides.
 * ==============================================================================
 */

require_once __DIR__ . "/Config/db.php";
require_once __DIR__ . "/Session/session.php";

$isLoggedIn = is_logged_in();
$currentUser = current_user();
$userEmail = $currentUser['email'] ?? '';
$userName = $currentUser['username'] ?? 'Guest';
$userRole = strtolower($currentUser['role'] ?? 'guest');

// Determine Dashboard Back-Link
$dashboardUrl = '/Skill_Bridge_Group_Project/index.php';
switch ($userRole) {
    case 'admin':
        $dashboardUrl = '/Skill_Bridge_Group_Project/Functions/Admin/dashboard.php';
        break;
    case 'student':
        $dashboardUrl = '/Skill_Bridge_Group_Project/Functions/Dashboards/Student/dashboard.php';
        break;
    case 'organization':
        $dashboardUrl = '/Skill_Bridge_Group_Project/Functions/Dashboards/Organization/dashboard.php';
        break;
    case 'company':
        $dashboardUrl = '/Skill_Bridge_Group_Project/Functions/Dashboards/Company/dashboard.php';
        break;
}

// ==============================================================================
// AJAX API Request Handlers
// ==============================================================================
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['ajax_action'])) {
    header('Content-Type: application/json; charset=utf-8');

    // 1. Lodge New Complaint
    if ($_POST['ajax_action'] === 'lodge_complaint') {
        $email = trim($_POST['email'] ?? '');
        $title = trim($_POST['title'] ?? '');
        $category = trim($_POST['category'] ?? 'Technical');
        $priority = strtoupper(trim($_POST['priority'] ?? 'MEDIUM'));
        $description = trim($_POST['discription'] ?? ($_POST['description'] ?? ''));

        // If logged in, prioritize session email if none supplied
        if (empty($email) && $isLoggedIn) {
            $email = $userEmail;
        }

        // Validation
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'message' => 'Please provide a valid email address.']);
            exit();
        }

        // Verify that the email is registered in SkillBridge user table
        $chkUser = $conn->prepare("SELECT Email FROM user WHERE Email = ? LIMIT 1");
        if ($chkUser) {
            $chkUser->bind_param("s", $email);
            $chkUser->execute();
            $chkRes = $chkUser->get_result();
            if ($chkRes->num_rows === 0) {
                echo json_encode([
                    'success' => false, 
                    'message' => 'The email "' . htmlspecialchars($email) . '" is not registered in SkillBridge. Please use your registered account email.'
                ]);
                exit();
            }
            $chkUser->close();
        }

        if (empty($title) || mb_strlen($title) < 5) {
            echo json_encode(['success' => false, 'message' => 'Subject/Title must be at least 5 characters long.']);
            exit();
        }
        if (empty($description) || mb_strlen($description) < 10) {
            echo json_encode(['success' => false, 'message' => 'Please provide a descriptive explanation (at least 10 characters).']);
            exit();
        }

        // Category to Priority Level Automatic Mapping
        $categoryPriorityMap = [
            'Account & Access' => 'URGENT',
            'Technical' => 'HIGH',
            'Organization Dispute' => 'HIGH',
            'Project Milestones & Review' => 'MEDIUM',
            'Academic' => 'MEDIUM',
            'Platform Policies' => 'LOW',
            'Other' => 'LOW'
        ];

        $validCategories = array_keys($categoryPriorityMap);
        if (!in_array($category, $validCategories)) {
            $category = 'Technical';
        }

        // Priority is automatically assigned by system policy based on category
        $priority = $categoryPriorityMap[$category] ?? 'MEDIUM';

        $status = 'PENDING';

        try {
            $stmt = $conn->prepare("INSERT INTO complain (email, title, category, discription, priority, status) VALUES (?, ?, ?, ?, ?, ?)");
            if ($stmt) {
                $stmt->bind_param("ssssss", $email, $title, $category, $description, $priority, $status);
                if ($stmt->execute()) {
                    $newId = $stmt->insert_id;
                    $formattedId = '#CMP-' . sprintf('%04d', $newId);
                    $formattedDate = date('M d, Y • h:i A');

                    echo json_encode([
                        'success' => true,
                        'message' => 'Your complaint has been lodged successfully with reference ID ' . $formattedId,
                        'complaint' => [
                            'id' => $newId,
                            'case_id' => $formattedId,
                            'email' => htmlspecialchars($email),
                            'title' => htmlspecialchars($title),
                            'category' => htmlspecialchars($category),
                            'priority' => $priority,
                            'status' => $status,
                            'discription' => htmlspecialchars($description),
                            'submitted_date' => $formattedDate,
                            'resolution_notes' => null
                        ]
                    ]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Failed to save complaint. Database error.']);
                }
                $stmt->close();
            } else {
                echo json_encode(['success' => false, 'message' => 'Database statement preparation error.']);
            }
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
        }
        exit();
    }

    // 2. Fetch Complaint Details for Modal
    if ($_POST['ajax_action'] === 'get_complaint_details') {
        $complaintId = intval($_POST['id'] ?? 0);
        if ($complaintId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid complaint ID.']);
            exit();
        }

        // Strict Privacy: Only the submitter and Administrators can view details
        if ($userRole === 'admin') {
            $stmt = $conn->prepare("SELECT id, email, title, category, discription, priority, status, resolution_notes, create_at, update_at FROM complain WHERE id = ? LIMIT 1");
            if ($stmt) $stmt->bind_param("i", $complaintId);
        } elseif ($isLoggedIn) {
            // Strictly check that email matches the logged-in user
            $stmt = $conn->prepare("SELECT id, email, title, category, discription, priority, status, resolution_notes, create_at, update_at FROM complain WHERE id = ? AND email = ? LIMIT 1");
            if ($stmt) $stmt->bind_param("is", $complaintId, $userEmail);
        } else {
            // Guests cannot access any complaint details
            echo json_encode(['success' => false, 'message' => 'Please sign in to view your complaint details.']);
            exit();
        }

        if ($stmt) {
            $stmt->execute();
            $result = $stmt->get_result();
            if ($row = $result->fetch_assoc()) {
                $formattedId = '#CMP-' . sprintf('%04d', $row['id']);
                $dateFormatted = !empty($row['create_at']) ? date('M d, Y • h:i A', strtotime($row['create_at'])) : 'Recently';

                echo json_encode([
                    'success' => true,
                    'complaint' => [
                        'id' => $row['id'],
                        'case_id' => $formattedId,
                        'email' => htmlspecialchars($row['email']),
                        'title' => htmlspecialchars($row['title']),
                        'category' => htmlspecialchars($row['category'] ?? 'Technical'),
                        'priority' => $row['priority'],
                        'status' => $row['status'],
                        'discription' => htmlspecialchars($row['discription']),
                        'resolution_notes' => !empty($row['resolution_notes']) ? htmlspecialchars($row['resolution_notes']) : null,
                        'submitted_date' => $dateFormatted
                    ]
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Complaint record not found or access denied.']);
            }
            $stmt->close();
        } else {
            echo json_encode(['success' => false, 'message' => 'Query error.']);
        }
        exit();
    }

    echo json_encode(['success' => false, 'message' => 'Unknown action.']);
    exit();
}

// ==============================================================================
// Load Complaints for Initial View
// Strict Privacy: Only the submitter and Administrators can view complaints.
// Other users or guests cannot view someone else's complaints.
// ==============================================================================
$complaintList = [];

if ($userRole === 'admin') {
    // Admin sees all complaints across the platform
    $res = $conn->query("SELECT id, email, title, category, discription, priority, status, resolution_notes, create_at FROM complain ORDER BY id DESC LIMIT 50");
    if ($res) {
        while ($r = $res->fetch_assoc()) {
            $complaintList[] = $r;
        }
    }
} elseif ($isLoggedIn) {
    // Logged-in users ONLY see complaints lodged under their own registered email
    $stmt = $conn->prepare("SELECT id, email, title, category, discription, priority, status, resolution_notes, create_at FROM complain WHERE email = ? ORDER BY id DESC LIMIT 50");
    if ($stmt) {
        $stmt->bind_param("s", $userEmail);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($r = $res->fetch_assoc()) {
            $complaintList[] = $r;
        }
        $stmt->close();
    }
} else {
    // Guests/Non-logged in users do NOT see other users' private complaints
    $complaintList = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Help & Support Center - SkillBridge</title>

    <!-- Google Fonts: Inter & Outfit -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Outfit:wght@500;600;700;800&display=swap" rel="stylesheet">

    <!-- Google Material Symbols Outlined -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />

    <!-- Help Center Stylesheet -->
    <link rel="stylesheet" href="/Skill_Bridge_Group_Project/Assets/CSS/help_center.css?v=<?= time() ?>">
</head>
<body class="hc-body">

    <!-- ==========================================================================
         1. Navigation Header
         ========================================================================== -->
    <header class="hc-navbar">
        <div class="hc-nav-container">
            <div class="hc-nav-left">
                <a href="<?= htmlspecialchars($dashboardUrl) ?>" class="hc-logo-link" title="SkillBridge Home">
                    <div class="hc-logo-badge">
                        <span class="material-symbols-outlined" style="font-size:22px;">handshake</span>
                    </div>
                    <span class="hc-logo-text">SkillBridge</span>
                    <span class="hc-logo-tag">Support</span>
                </a>

                <ul class="hc-nav-links">
                    <li><a href="<?= htmlspecialchars($dashboardUrl) ?>"><span class="material-symbols-outlined" style="font-size:17px;">dashboard</span> Dashboard</a></li>
                    <li><a href="#complaintsSection" class="active"><span class="material-symbols-outlined" style="font-size:17px;">assignment</span> My Complaints & Requests</a></li>
                    <li><a href="#knowledgeBaseSection"><span class="material-symbols-outlined" style="font-size:17px;">menu_book</span> Knowledge Base</a></li>
                </ul>
            </div>

            <div class="hc-nav-right">
                <button type="button" class="hc-btn-primary" id="hcNavLodgeBtn">
                    <span class="material-symbols-outlined" style="font-size:19px;">report_problem</span>
                    <span>Lodge Complaint</span>
                </button>

                <?php if ($isLoggedIn): ?>
                    <a href="<?= htmlspecialchars($dashboardUrl) ?>" class="hc-user-pill" title="<?= htmlspecialchars($userName) ?> (<?= htmlspecialchars(ucfirst($userRole)) ?>)">
                        <div class="hc-user-avatar">
                            <?= strtoupper(substr($userName, 0, 1)) ?>
                        </div>
                        <span><?= htmlspecialchars($userName) ?></span>
                    </a>
                <?php else: ?>
                    <a href="/Skill_Bridge_Group_Project/Auth/login.php" class="hc-user-pill">
                        <span class="material-symbols-outlined" style="font-size:18px;">login</span>
                        <span>Sign In</span>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <!-- ==========================================================================
         2. Main Content Container
         ========================================================================== -->
    <main class="hc-main">

        <!-- Hero Section -->
        <section class="hc-hero">
            <div class="hc-hero-badge">
                <span class="material-symbols-outlined" style="font-size:16px;">verified_user</span>
                <span>Instant Assistance & Guaranteed SLA Protocols</span>
            </div>
            <h1>Help & Support Center</h1>
            <p>
                Have an issue or need assistance? Lodge a formal complaint or explore our self-help guides.
                We deliver rapid, transparent, and empathetic resolution for all students, mentors, and partner organizations.
            </p>

            <!-- Search Bar -->
            <div class="hc-search-wrapper">
                <span class="material-symbols-outlined hc-search-icon">search</span>
                <input 
                    type="text" 
                    id="hcSearchInput" 
                    class="hc-search-input" 
                    placeholder="Search complaints, requests, or issues..." 
                    autocomplete="off"
                >
            </div>
        </section>

        <!-- Two Primary Action Cards -->
        <section class="hc-action-cards">
            
            <!-- Card 1: Lodge a Complaint -->
            <div class="hc-card-feature card-lodge">
                <div>
                    <div class="hc-card-header">
                        <div class="hc-card-icon-wrap icon-orange">
                            <span class="material-symbols-outlined" style="font-size:26px;">shield_with_heart</span>
                        </div>
                        <span class="hc-card-badge badge-orange">
                            <span class="material-symbols-outlined" style="font-size:13px;">schedule</span> Guaranteed 24h SLA
                        </span>
                    </div>

                    <h2 class="hc-card-title">Lodge a Complaint</h2>
                    <p class="hc-card-desc">
                        Facing an escalated issue, technical barrier, or project mentorship dispute? Lodge a formal complaint with priority triaging and continuous real-time audit logs.
                    </p>

                    <div class="hc-card-features-list">
                        <div class="hc-card-feature-item">
                            <span class="material-symbols-outlined">check_circle</span>
                            <span>Case Manager Assigned</span>
                        </div>
                        <div class="hc-card-feature-item">
                            <span class="material-symbols-outlined">check_circle</span>
                            <span>Formal Audit Record</span>
                        </div>
                    </div>
                </div>

                <button type="button" class="hc-card-btn-lodge" id="hcHeroLodgeBtn">
                    <span>Submit New Complaint</span>
                    <span class="material-symbols-outlined" style="font-size:18px;">arrow_forward</span>
                </button>
            </div>

            <!-- Card 2: Self-Help & Documentation -->
            <div class="hc-card-feature card-help">
                <div>
                    <div class="hc-card-header">
                        <div class="hc-card-icon-wrap icon-blue">
                            <span class="material-symbols-outlined" style="font-size:26px;">support_agent</span>
                        </div>
                        <span class="hc-card-badge badge-blue">
                            <span class="material-symbols-outlined" style="font-size:13px;">help</span> 24/7 Available
                        </span>
                    </div>

                    <h2 class="hc-card-title">Self-Help & Knowledge Guides</h2>
                    <p class="hc-card-desc">
                        Need immediate answers? Check our validated step-by-step guides, policy guidelines, and troubleshooting paths to resolve common questions without waiting.
                    </p>

                    <div class="hc-card-features-list">
                        <div class="hc-card-feature-item">
                            <span class="material-symbols-outlined">check_circle</span>
                            <span>Interactive Step Guides</span>
                        </div>
                        <div class="hc-card-feature-item">
                            <span class="material-symbols-outlined">check_circle</span>
                            <span>Account & Project FAQs</span>
                        </div>
                    </div>
                </div>

                <a href="#knowledgeBaseSection" class="hc-card-btn-outline">
                    <span class="material-symbols-outlined" style="font-size:18px;">menu_book</span>
                    <span>Browse FAQs & Guides</span>
                </a>
            </div>

        </section>

        <!-- Active Complaints & Help Requests Section -->
        <section class="hc-section" id="complaintsSection">
            <div class="hc-section-header">
                <div class="hc-section-title-wrap">
                    <h2>Active Complaints & Help Requests</h2>
                    <p>Real-time status updates and correspondence history for your submitted complaints and admin help requests.</p>
                </div>
                <div style="font-size: 13px; font-weight: 600; color: #ea580c;" id="hcTableCounter">
                    Showing <span id="hcCountNumber"><?= count($complaintList) ?></span> complaints
                </div>
            </div>

            <div class="hc-table-card">
                <div class="hc-table-responsive">
                    <table class="hc-table">
                        <thead>
                            <tr>
                                <th>Case ID</th>
                                <th>Subject & Category</th>
                                <th>Submitted Date</th>
                                <th>Priority</th>
                                <th>Status</th>
                                <th style="text-align: right;">Action</th>
                            </tr>
                        </thead>
                        <tbody id="hcComplaintsTableBody">
                            <?php if (!empty($complaintList)): ?>
                                <?php foreach ($complaintList as $c): 
                                    $cid = intval($c['id']);
                                    $caseCode = '#CMP-' . sprintf('%04d', $cid);
                                    $rawStatus = strtoupper($c['status'] ?? 'PENDING');
                                    $rawPriority = strtoupper($c['priority'] ?? 'MEDIUM');
                                    $dateStr = !empty($c['create_at']) ? date('M d, Y • h:i A', strtotime($c['create_at'])) : 'Recently';

                                    // Status Pill Styling
                                    $statusClass = 'status-pending';
                                    $statusLabel = 'Pending';
                                    if ($rawStatus === 'IN_REVIEW') {
                                        $statusClass = 'status-review';
                                        $statusLabel = 'In Review';
                                    } elseif ($rawStatus === 'RESOLVED') {
                                        $statusClass = 'status-resolved';
                                        $statusLabel = 'Resolved';
                                    } elseif ($rawStatus === 'DISMISSED') {
                                        $statusClass = 'status-dismissed';
                                        $statusLabel = 'Dismissed';
                                    }

                                    // Priority Pill Styling
                                    $priorityClass = 'priority-medium';
                                    if ($rawPriority === 'LOW') $priorityClass = 'priority-low';
                                    if ($rawPriority === 'HIGH') $priorityClass = 'priority-high';
                                    if ($rawPriority === 'URGENT') $priorityClass = 'priority-urgent';
                                ?>
                                    <tr class="hc-data-row" 
                                        data-id="<?= $cid ?>"
                                        data-case="<?= htmlspecialchars($caseCode) ?>"
                                        data-title="<?= htmlspecialchars($c['title']) ?>"
                                        data-category="<?= htmlspecialchars($c['category'] ?? 'Technical') ?>"
                                        data-priority="<?= htmlspecialchars($rawPriority) ?>"
                                        data-status="<?= htmlspecialchars($rawStatus) ?>"
                                        data-email="<?= htmlspecialchars($c['email']) ?>"
                                        data-date="<?= htmlspecialchars($dateStr) ?>"
                                        data-desc="<?= htmlspecialchars($c['discription']) ?>"
                                        data-notes="<?= htmlspecialchars($c['resolution_notes'] ?? '') ?>">
                                        <td>
                                            <span class="hc-case-id"><?= htmlspecialchars($caseCode) ?></span>
                                        </td>
                                        <td>
                                            <div class="hc-meta-title"><?= htmlspecialchars($c['title']) ?></div>
                                            <div class="hc-meta-category">
                                                <span class="material-symbols-outlined" style="font-size:13px; vertical-align:-2px;">folder_open</span>
                                                Category: <?= htmlspecialchars($c['category'] ?? 'Technical') ?>
                                            </div>
                                        </td>
                                        <td style="color: #64748b; font-size: 12.5px; white-space: nowrap;">
                                            <?= htmlspecialchars($dateStr) ?>
                                        </td>
                                        <td>
                                            <span class="hc-priority-pill <?= $priorityClass ?>"><?= htmlspecialchars($rawPriority) ?></span>
                                        </td>
                                        <td>
                                            <span class="hc-status-pill <?= $statusClass ?>">
                                                <span style="display:inline-block; width:6px; height:6px; border-radius:50%; background:currentColor;"></span>
                                                <?= htmlspecialchars($statusLabel) ?>
                                            </span>
                                        </td>
                                        <td style="text-align: right;">
                                            <button type="button" class="hc-btn-view-details" data-id="<?= $cid ?>">
                                                <span>View Details</span>
                                                <span class="material-symbols-outlined" style="font-size: 15px;">chevron_right</span>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>

                    <!-- Empty State Container -->
                    <div id="hcEmptyState" class="hc-empty-state" style="<?= empty($complaintList) ? 'display:block;' : 'display:none;' ?>">
                        <span class="material-symbols-outlined">inbox</span>
                        <?php if ($isLoggedIn): ?>
                            <h4 style="font-size: 16px; font-weight: 700; color: #1e293b; margin-bottom: 4px;">No Complaints Found</h4>
                            <p style="font-size: 13px; color: #64748b; margin-bottom: 16px;">
                                You have not submitted any complaints yet under <strong><?= htmlspecialchars($userEmail) ?></strong>, or no records match your search filter.
                            </p>
                            <button type="button" class="hc-btn-primary" id="hcEmptyLodgeBtn">
                                <span class="material-symbols-outlined" style="font-size:18px;">add</span>
                                <span>Lodge a Complaint</span>
                            </button>
                        <?php else: ?>
                            <h4 style="font-size: 16px; font-weight: 700; color: #1e293b; margin-bottom: 4px;">Private & Protected Inquiries</h4>
                            <p style="font-size: 13px; color: #64748b; margin-bottom: 16px; max-width: 480px; margin-left: auto; margin-right: auto;">
                                To protect your privacy, complaints can strictly be viewed only by the person who submitted them and system administrators. Please sign in to track your submissions.
                            </p>
                            <div style="display: inline-flex; gap: 10px; flex-wrap: wrap; justify-content: center;">
                                <a href="/Skill_Bridge_Group_Project/Auth/login.php" class="hc-btn-primary" style="text-decoration:none;">
                                    <span class="material-symbols-outlined" style="font-size:18px;">login</span>
                                    <span>Sign In to Track Complaints</span>
                                </a>
                                <button type="button" class="hc-btn-secondary" id="hcEmptyLodgeBtn">
                                    <span class="material-symbols-outlined" style="font-size:18px;">report_problem</span>
                                    <span>Lodge New Complaint</span>
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>

                </div>
            </div>
        </section>

        <!-- Self-Help & Knowledge Categories Section (4 Cards) -->
        <section class="hc-section" id="knowledgeBaseSection">
            <div class="hc-section-header">
                <div class="hc-section-title-wrap">
                    <h2>Self-Help & Knowledge Categories</h2>
                    <p>Browse validated documentation, troubleshooting paths, and organizational policies.</p>
                </div>
            </div>

            <div class="hc-knowledge-grid">

                <!-- Category 1: Account Security -->
                <div class="hc-knowledge-card" data-category-key="security">
                    <div>
                        <div class="hc-knowledge-icon">
                            <span class="material-symbols-outlined">lock_reset</span>
                        </div>
                        <h3 class="hc-knowledge-title">Account Security</h3>
                        <p class="hc-knowledge-desc">
                            Password resets, credential verification, university email authentication, role permissions, and active session management.
                        </p>
                    </div>
                    <div class="hc-knowledge-footer">
                        <span>Read 4 Solutions</span>
                        <span class="material-symbols-outlined">arrow_forward</span>
                    </div>
                </div>

                <!-- Category 2: Skills & Project Badges -->
                <div class="hc-knowledge-card" data-category-key="skills">
                    <div>
                        <div class="hc-knowledge-icon">
                            <span class="material-symbols-outlined">workspace_premium</span>
                        </div>
                        <h3 class="hc-knowledge-title">Skills & Project Badges</h3>
                        <p class="hc-knowledge-desc">
                            100% free educational platform policy, project milestone approvals, mentor evaluations, skill badge endorsements, and portfolio certifications.
                        </p>
                    </div>
                    <div class="hc-knowledge-footer">
                        <span>Read 4 Solutions</span>
                        <span class="material-symbols-outlined">arrow_forward</span>
                    </div>
                </div>

                <!-- Category 3: Platform Guide -->
                <div class="hc-knowledge-card" data-category-key="platform">
                    <div>
                        <div class="hc-knowledge-icon">
                            <span class="material-symbols-outlined">menu_book</span>
                        </div>
                        <h3 class="hc-knowledge-title">Platform Guide</h3>
                        <p class="hc-knowledge-desc">
                            Step-by-step submission guides, internship application processes, portfolio showcase setup, and mentor collaboration tools.
                        </p>
                    </div>
                    <div class="hc-knowledge-footer">
                        <span>Read 8 Solutions</span>
                        <span class="material-symbols-outlined">arrow_forward</span>
                    </div>
                </div>

                <!-- Category 4: Policies & SLAs -->
                <div class="hc-knowledge-card" data-category-key="policies">
                    <div>
                        <div class="hc-knowledge-icon">
                            <span class="material-symbols-outlined">gavel</span>
                        </div>
                        <h3 class="hc-knowledge-title">Policies & SLAs</h3>
                        <p class="hc-knowledge-desc">
                            Escalation thresholds, code of conduct, academic integrity guidelines, dispute resolution timelines, and service availability.
                        </p>
                    </div>
                    <div class="hc-knowledge-footer">
                        <span>Read 6 Solutions</span>
                        <span class="material-symbols-outlined">arrow_forward</span>
                    </div>
                </div>

            </div>

            <!-- Escalation Safeguards Banner -->
            <div class="hc-escalation-banner">
                <div class="hc-escalation-left">
                    <div class="hc-escalation-icon">
                        <span class="material-symbols-outlined">verified_user</span>
                    </div>
                    <div class="hc-escalation-text">
                        <h4>Every Case is Protected by Executive Escalation Safeguards</h4>
                        <p>If your issue is unresolved within 48 hours of lodging timestamp, it automatically routes to our Tier-3 Lead Administrative Council.</p>
                    </div>
                </div>
                <button type="button" class="hc-btn-banner" id="btnEscalationPolicy">
                    Read Escalation Matrix
                </button>
            </div>
        </section>

    </main>

    <!-- ==========================================================================
         3. Footer
         ========================================================================== -->
    <footer class="hc-footer">
        <div class="hc-footer-container">
            <div style="display: flex; align-items: center; gap: 8px;">
                <span class="material-symbols-outlined" style="color: #ea580c; font-size: 20px;">handshake</span>
                <span>&copy; <?= date('Y') ?> SkillBridge Help Center. All rights reserved. Transparent, swift, and empathetic resolution.</span>
            </div>

            <div class="hc-footer-links">
                <a href="#complaintsSection">Complaints</a>
                <a href="#knowledgeBaseSection">Knowledge Base</a>
                <a href="#" id="footerPrivacyLink">Privacy Policy</a>
                <a href="#" id="footerTermsLink">Terms of Service</a>
            </div>
        </div>
    </footer>

    <!-- ==========================================================================
         4. Modal: Lodge Complaint
         ========================================================================== -->
    <div class="hc-modal-backdrop" id="hcLodgeModal">
        <div class="hc-modal-dialog">
            <div class="hc-modal-header">
                <div>
                    <h3>Lodge a Formal Complaint</h3>
                    <p>Provide detailed information. All complaints receive dedicated administrative oversight.</p>
                </div>
                <button type="button" class="hc-modal-close" id="btnCloseLodgeModal" aria-label="Close">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>

            <form id="hcLodgeComplaintForm">
                <div class="hc-modal-body">
                    
                    <div class="hc-form-row">
                        <!-- Email Input -->
                        <div class="hc-form-group">
                            <label for="hcLodgeEmail">Your Email Address <span style="color:#dc2626;">*</span></label>
                            <input 
                                type="email" 
                                id="hcLodgeEmail" 
                                name="email" 
                                class="hc-form-control" 
                                placeholder="name@domain.com"
                                value="<?= htmlspecialchars($userEmail) ?>" 
                                required
                            >
                        </div>

                        <!-- Category Selector -->
                        <div class="hc-form-group">
                            <label for="hcLodgeCategory">Complaint Category <span style="color:#dc2626;">*</span></label>
                            <select id="hcLodgeCategory" name="category" class="hc-form-control">
                                <option value="Technical" selected>Technical Issue / System Bug</option>
                                <option value="Account & Access">Account Access & Authentication</option>
                                <option value="Project Milestones & Review">Project Milestones & Mentor Review</option>
                                <option value="Organization Dispute">Organization or Partner Dispute</option>
                                <option value="Academic">Academic / University Evaluation</option>
                                <option value="Other">Other Inquiry</option>
                            </select>
                        </div>
                    </div>

                    <!-- Priority Level (Auto-assigned based on Category) -->
                    <div class="hc-form-group">
                        <label style="display:flex; justify-content:space-between; align-items:center;">
                            <span>Priority Level <span style="color:#64748b; font-size:11.5px; font-weight:normal;">(Auto-assigned based on category)</span></span>
                            <span class="material-symbols-outlined" style="font-size:16px; color:#94a3b8;" title="Priority is determined automatically by system triage policy">lock</span>
                        </label>
                        <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:10px; padding:10px 14px; display:flex; align-items:center; gap:10px;">
                            <span id="hcPriorityPill" class="hc-priority-pill priority-high">HIGH</span>
                            <span id="hcPriorityReason" style="font-size:12.5px; color:#475569;">System bug or operational platform impediment</span>
                        </div>
                        <input type="hidden" id="hcLodgePriority" name="priority" value="HIGH">
                    </div>

                    <!-- Subject / Title -->
                    <div class="hc-form-group">
                        <label for="hcLodgeTitle">Subject / Summary <span style="color:#dc2626;">*</span></label>
                        <input 
                            type="text" 
                            id="hcLodgeTitle" 
                            name="title" 
                            class="hc-form-control" 
                            placeholder="e.g. Unable to submit final project proposal deliverable"
                            maxlength="100"
                            required
                        >
                    </div>

                    <!-- Detailed Description -->
                    <div class="hc-form-group">
                        <label for="hcLodgeDesc">Detailed Description <span style="color:#dc2626;">*</span></label>
                        <textarea 
                            id="hcLodgeDesc" 
                            name="discription" 
                            class="hc-form-control" 
                            placeholder="Describe what occurred, any error messages shown, and steps to reproduce..."
                            rows="4" 
                            required
                        ></textarea>
                    </div>

                </div>

                <div class="hc-modal-footer">
                    <button type="button" class="hc-btn-secondary" id="btnCancelLodge">Cancel</button>
                    <button type="submit" class="hc-btn-primary" id="btnSubmitLodge">
                        <span class="material-symbols-outlined" style="font-size:18px;">send</span>
                        <span>Submit Complaint</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ==========================================================================
         5. Modal: View Complaint Details
         ========================================================================== -->
    <div class="hc-modal-backdrop" id="hcDetailModal">
        <div class="hc-modal-dialog">
            <div class="hc-modal-header">
                <div>
                    <h3 id="hcDetailModalTitle">Complaint Details</h3>
                    <p id="hcDetailCaseId">Case #CMP-0000</p>
                </div>
                <button type="button" class="hc-modal-close" id="btnCloseDetailModal" aria-label="Close">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>

            <div class="hc-modal-body">
                
                <div class="hc-detail-row">
                    <span class="hc-detail-label">Status</span>
                    <span id="hcDetailStatusBadge" class="hc-status-pill status-pending">Pending</span>
                </div>

                <div class="hc-detail-row">
                    <span class="hc-detail-label">Priority Level</span>
                    <span id="hcDetailPriorityBadge" class="hc-priority-pill priority-medium">MEDIUM</span>
                </div>

                <div class="hc-detail-row">
                    <span class="hc-detail-label">Category</span>
                    <span id="hcDetailCategory" class="hc-detail-val">Technical</span>
                </div>

                <div class="hc-detail-row">
                    <span class="hc-detail-label">Submitted On</span>
                    <span id="hcDetailDate" class="hc-detail-val">-</span>
                </div>

                <div class="hc-detail-row">
                    <span class="hc-detail-label">Submitter Email</span>
                    <span id="hcDetailEmail" class="hc-detail-val">-</span>
                </div>

                <div style="margin-top: 10px;">
                    <div class="hc-detail-label" style="font-weight:700; color:#1e293b; margin-bottom: 4px;">Description of Issue</div>
                    <div id="hcDetailDescription" class="hc-detail-box">No description provided.</div>
                </div>

                <div id="hcDetailResolutionWrap" style="margin-top: 10px;">
                    <div id="hcDetailResolutionLabel" class="hc-detail-label" style="font-weight:700; color:#065f46; margin-bottom: 4px;">
                        <span class="material-symbols-outlined" style="font-size:16px; vertical-align:-2px;">verified</span>
                        Admin Resolution & Audit Log
                    </div>
                    <div id="hcDetailResolutionNotes" class="hc-resolution-box">
                        No resolution notes updated yet. Our administrative staff is reviewing this complaint.
                    </div>
                </div>

            </div>

            <div class="hc-modal-footer">
                <button type="button" class="hc-btn-secondary" id="btnCloseDetailFooter">Close</button>
            </div>
        </div>
    </div>

    <!-- ==========================================================================
         6. Modal: Self-Help Knowledge Reader
         ========================================================================== -->
    <div class="hc-modal-backdrop" id="hcKnowledgeModal">
        <div class="hc-modal-dialog">
            <div class="hc-modal-header">
                <div>
                    <h3 id="hcKnowledgeModalTitle">Help Category</h3>
                    <p id="hcKnowledgeModalSubtitle">Recommended solutions and procedural guidance</p>
                </div>
                <button type="button" class="hc-modal-close" id="btnCloseKnowledgeModal" aria-label="Close">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>

            <div class="hc-modal-body" id="hcKnowledgeModalBody">
                <!-- Dynamically populated by JS based on clicked category -->
            </div>

            <div class="hc-modal-footer">
                <button type="button" class="hc-btn-secondary" id="btnCloseKnowledgeFooter">Close</button>
                <button type="button" class="hc-btn-primary" id="btnKnowledgeLodgeCTA">
                    <span class="material-symbols-outlined" style="font-size:18px;">report_problem</span>
                    <span>Still Need Help? Lodge a Complaint</span>
                </button>
            </div>
        </div>
    </div>

    <!-- ==========================================================================
         7. Interactive Toast Notification
         ========================================================================== -->
    <div class="hc-toast" id="hcToast">
        <span class="material-symbols-outlined">check_circle</span>
        <span id="hcToastMsg">Complaint lodged successfully!</span>
    </div>

    <!-- Help Center JavaScript Engine -->
    <script src="/Skill_Bridge_Group_Project/Assets/JS/help_center.js?v=<?= time() ?>"></script>
</body>
</html>
