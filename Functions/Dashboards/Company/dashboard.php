<?php
include "../../../Config/db.php";
include "../../../Config/company_schema.php";
include "../../../Session/Session.php";

date_default_timezone_set('Asia/Kolkata');

require_role("company");
$user = current_user();

// Ensure schema and company record exist
ensure_company_schema($conn);
$company = ensure_company_record($conn, $user);
$company_id = isset($company['id']) ? (int) $company['id'] : 0;

// Seed demo data if needed
seed_company_demo_data($conn, $company_id);

// Fetch dashboard statistics using actual schema
$active_internships_result = $conn->query("SELECT COUNT(*) as count FROM internships WHERE company_id = $company_id AND status = 'active'");
$active_internships_row = ($active_internships_result && $active_internships_result->num_rows > 0) ? $active_internships_result->fetch_assoc() : array();
$active_internships = isset($active_internships_row['count']) ? (int) $active_internships_row['count'] : 0;

$total_applications_result = $conn->query("SELECT COUNT(*) as count FROM applications WHERE company_id = $company_id");
$total_applications_row = ($total_applications_result && $total_applications_result->num_rows > 0) ? $total_applications_result->fetch_assoc() : array();
$total_applications = isset($total_applications_row['count']) ? (int) $total_applications_row['count'] : 0;

$shortlisted_result = $conn->query("SELECT COUNT(*) as count FROM applications WHERE company_id = $company_id AND status = 'shortlisted'");
$shortlisted_row = ($shortlisted_result && $shortlisted_result->num_rows > 0) ? $shortlisted_result->fetch_assoc() : array();
$shortlisted = isset($shortlisted_row['count']) ? (int) $shortlisted_row['count'] : 0;

$interviews_scheduled_result = $conn->query("SELECT COUNT(*) as count FROM interviews WHERE company_id = $company_id AND status = 'scheduled'");
$interviews_scheduled_row = ($interviews_scheduled_result && $interviews_scheduled_result->num_rows > 0) ? $interviews_scheduled_result->fetch_assoc() : array();
$interviews_scheduled = isset($interviews_scheduled_row['count']) ? (int) $interviews_scheduled_row['count'] : 0;

// Fetch recent applications
$recent_apps = $conn->query("
    SELECT * FROM applications 
    WHERE company_id = $company_id
    ORDER BY applied_date DESC
    LIMIT 5
");

// Fetch recent internships
$recent_internships = $conn->query("
    SELECT * FROM internships 
    WHERE company_id = $company_id 
    ORDER BY created_at DESC 
    LIMIT 2
");

// Fetch upcoming interviews
$upcoming_interviews = $conn->query("
    SELECT * FROM interviews 
    WHERE company_id = $company_id AND status = 'scheduled'
    ORDER BY interview_date ASC
    LIMIT 3
");

include "../../../Includes/company_sidebar.php";
include "../../../Includes/dash_header.php";
?>

<style>
    .company-dashboard {
        padding: 2rem;
        background: #f8fafc;
        min-height: 100vh;
    }

    .dashboard-header {
        margin-bottom: 2rem;
    }

    .dashboard-header h1 {
        font-size: 2rem;
        color: #0f172a;
        margin-bottom: 0.5rem;
    }

    .dashboard-header p {
        color: #64748b;
        font-size: 0.95rem;
    }

    /* Company Info Card */
    .company-info-card {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        margin-bottom: 2rem;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .company-info-main {
        display: flex;
        align-items: center;
        gap: 1.5rem;
    }

    .company-avatar {
        width: 80px;
        height: 80px;
        background: #e0f2fe;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
    }

    .company-details h2 {
        font-size: 1.25rem;
        color: #0f172a;
        margin-bottom: 0.5rem;
    }

    .company-details p {
        color: #64748b;
        font-size: 0.9rem;
        margin: 0.25rem 0;
    }

    .company-stats {
        display: flex;
        gap: 2rem;
    }

    .stat-item {
        text-align: center;
    }

    .stat-number {
        font-size: 1.5rem;
        font-weight: 700;
        color: #082544;
    }

    .stat-label {
        font-size: 0.8rem;
        color: #94a3b8;
        text-transform: uppercase;
        margin-top: 0.25rem;
    }

    /* Stats Grid */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .stat-card {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }

    .stat-card-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 1rem;
    }

    .stat-card-title {
        font-size: 0.85rem;
        color: #64748b;
        text-transform: uppercase;
        font-weight: 600;
        letter-spacing: 0.5px;
    }

    .stat-badge {
        background: #dcfce7;
        color: #15803d;
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .stat-card-value {
        font-size: 2rem;
        font-weight: 700;
        color: #082544;
    }

    /* Section Headers */
    .section-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
    }

    .section-header h3 {
        font-size: 1.1rem;
        color: #0f172a;
        font-weight: 700;
    }

    .view-all {
        color: #082544;
        text-decoration: none;
        font-size: 0.9rem;
        font-weight: 600;
        cursor: pointer;
    }

    .view-all:hover {
        text-decoration: underline;
    }

    /* Table Styles */
    .content-section {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        margin-bottom: 2rem;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        overflow-x: auto;
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    table thead {
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
    }

    table th {
        padding: 1rem;
        text-align: left;
        font-size: 0.85rem;
        color: #64748b;
        text-transform: uppercase;
        font-weight: 600;
        letter-spacing: 0.5px;
    }

    table td {
        padding: 1rem;
        border-bottom: 1px solid #e2e8f0;
        font-size: 0.9rem;
        color: #334155;
    }

    table tbody tr:hover {
        background: #f8fafc;
    }

    .candidate-name {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .candidate-avatar {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: #dbeafe;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        color: #1e40af;
    }

    .status-badge {
        display: inline-block;
        padding: 0.35rem 0.75rem;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
        text-transform: uppercase;
    }

    .status-new {
        background: #dbeafe;
        color: #1e40af;
    }

    .status-shortlisted {
        background: #dcfce7;
        color: #15803d;
    }

    .status-rejected {
        background: #fee2e2;
        color: #991b1b;
    }

    .action-btn {
        background: transparent;
        border: none;
        color: #64748b;
        cursor: pointer;
        font-size: 1.2rem;
    }

    .action-btn:hover {
        color: #082544;
    }

    /* Internship Cards Grid */
    .internship-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .internship-card {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        border-left: 4px solid #082544;
    }

    .internship-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 1rem;
    }

    .internship-title {
        font-size: 1.1rem;
        color: #0f172a;
        font-weight: 700;
    }

    .internship-status {
        background: #dcfce7;
        color: #15803d;
        padding: 0.35rem 0.75rem;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
    }

    .internship-desc {
        color: #64748b;
        font-size: 0.9rem;
        margin-bottom: 1rem;
        line-height: 1.5;
    }

    .internship-meta {
        display: flex;
        gap: 1.5rem;
        font-size: 0.9rem;
        color: #64748b;
    }

    /* Interview Schedule */
    .interview-section {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }

    .interview-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1rem 0;
        border-bottom: 1px solid #e2e8f0;
    }

    .interview-item:last-child {
        border-bottom: none;
    }

    .interview-candidate {
        flex: 1;
    }

    .interview-name {
        font-weight: 600;
        color: #0f172a;
        margin-bottom: 0.25rem;
    }

    .interview-position {
        font-size: 0.9rem;
        color: #64748b;
    }

    .interview-time {
        text-align: right;
    }

    .interview-date {
        font-size: 1.25rem;
        font-weight: 700;
        color: #082544;
    }

    .interview-timerange {
        font-size: 0.85rem;
        color: #64748b;
    }

    .interview-type {
        font-size: 0.8rem;
        color: #94a3b8;
    }

    /* Two Column Layout */
    .dashboard-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 2rem;
        margin-bottom: 2rem;
    }

    .dashboard-col-full {
        grid-column: 1 / -1;
    }

    @media (max-width: 1200px) {
        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
        }
        
        .internship-grid {
            grid-template-columns: 1fr;
        }
        
        .dashboard-row {
            grid-template-columns: 1fr;
        }
    }
</style>

<main class="content">
    <div class="company-dashboard">
        <!-- Dashboard Header -->
        <div class="dashboard-header">
            <h1>Welcome back, <?php echo htmlspecialchars(isset($company['company_name']) ? $company['company_name'] : $user['username']); ?></h1>
            <p>Here is an overview of your recruitment momentum today.</p>
        </div>

        <!-- Company Info Card -->
        <?php if (!empty($company)): ?>
        <div class="company-info-card">
            <div class="company-info-main">
                <div class="company-avatar">🏢</div>
                <div class="company-details">
                    <h2><?php echo htmlspecialchars(isset($company['company_name']) ? $company['company_name'] : 'Company'); ?></h2>
                    <p>📍 <?php echo htmlspecialchars(isset($company['location']) ? $company['location'] : 'Not specified'); ?></p>
                    <p>👥 <?php echo htmlspecialchars(isset($company['employees']) ? $company['employees'] : 'Not specified'); ?> Employees</p>
                </div>
            </div>
            <div class="company-stats">
                <div class="stat-item">
                    <div class="stat-number"><?php echo $active_internships; ?></div>
                    <div class="stat-label">Active Internships</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number"><?php echo $total_applications; ?></div>
                    <div class="stat-label">Applications</div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Stats Grid -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-card-header">
                    <span class="stat-card-title">Active Internships</span>
                    <img src="../../../Assets/Images/Icons/internship.png" alt="Internships" style="width: 36px; height: 36px; object-fit: contain;">
                    <span class="stat-badge">+2 this week</span>
                </div>
                <div class="stat-card-value"><?php echo $active_internships; ?></div>
            </div>

            <div class="stat-card">
                <div class="stat-card-header">
                    <span class="stat-card-title">Total Applications</span>
                    <img src="../../../Assets/Images/Icons/application.png" alt="Applications" style="width: 36px; height: 36px; object-fit: contain;">
                    <span class="stat-badge">+42 new</span>
                </div>
                <div class="stat-card-value"><?php echo $total_applications; ?></div>
            </div>

            <div class="stat-card">
                <div class="stat-card-header">
                    <span class="stat-card-title">Shortlisted</span>
                    <img src="../../../Assets/Images/Icons/shortlist.png" alt="Shortlist" style="width: 36px; height: 36px; object-fit: contain;">
                    <span class="stat-badge">13% conversion</span>
                </div>
                <div class="stat-card-value"><?php echo $shortlisted; ?></div>
            </div>

            <div class="stat-card">
                <div class="stat-card-header">
                    <span class="stat-card-title">Interviews</span>
                    <img src="../../../Assets/Images/Icons/interviews.png" alt="Interviews" style="width: 36px; height: 36px; object-fit: contain;">
                    <span class="stat-badge">3 today</span>
                </div>
                <div class="stat-card-value"><?php echo $interviews_scheduled; ?></div>
            </div>
        </div>

        <!-- Main Content Row -->
        <div class="dashboard-row">
            <!-- Recent Applications -->
            <div class="dashboard-col-full">
                <div class="section-header">
                    <h3>Recent Applications</h3>
                    <a href="applications.php" class="view-all">View All</a>
                </div>

                <div class="content-section">
                    <table>
                        <thead>
                            <tr>
                                <th>Applicant</th>
                                <th>Email</th>
                                <th>Position</th>
                                <th>Status</th>
                                <th>Applied Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($recent_apps && $recent_apps->num_rows > 0): ?>
                                <?php while ($app = $recent_apps->fetch_assoc()): ?>
                                <tr>
                                    <td>
                                        <div class="candidate-name">
                                            <div class="candidate-avatar"><?php echo strtoupper(substr($app['applicant_name'], 0, 1)); ?></div>
                                            <span><?php echo htmlspecialchars($app['applicant_name']); ?></span>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($app['email']); ?></td>
                                    <td><?php echo htmlspecialchars($app['position']); ?></td>
                                    <td>
                                        <span class="status-badge status-<?php echo strtolower($app['status']); ?>">
                                            <?php echo ucfirst($app['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($app['applied_date'])); ?></td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="5" style="text-align:center; color:#64748b; padding:2rem;">No applications yet.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Internships & Interviews Row -->
        <div class="dashboard-row">
            <!-- Recent Internships -->
            <div>
                <div class="section-header">
                    <h3>Recent Internships</h3>
                </div>
                <div class="internship-grid">
                    <?php if ($recent_internships && $recent_internships->num_rows > 0): ?>
                        <?php while ($internship = $recent_internships->fetch_assoc()): ?>
                        <div class="internship-card">
                            <div class="internship-header">
                                <div>
                                    <div class="internship-title"><?php echo htmlspecialchars($internship['title']); ?></div>
                                </div>
                                <span class="internship-status"><?php echo ucfirst($internship['status']); ?></span>
                            </div>
                            <p class="internship-desc"><?php echo htmlspecialchars(substr($internship['description'], 0, 100)) . (strlen($internship['description']) > 100 ? '...' : ''); ?></p>
                            <div class="internship-meta">
                                <span>📍 <?php echo htmlspecialchars($internship['location']); ?></span>
                                <span>⏱️ <?php echo htmlspecialchars($internship['duration']); ?></span>
                                <span>📅 <?php echo date('M d, Y', strtotime($internship['deadline'])); ?></span>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div style="grid-column: 1 / -1; text-align:center; color:#64748b; padding:2rem;">No internships posted yet. <a href="internships.php" style="color:#082544; font-weight:600;">Create one</a></div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Upcoming Interviews -->
            <div>
                <div class="section-header">
                    <h3>Upcoming Interviews</h3>
                    <a href="interviews.php" class="view-all">View All Schedule</a>
                </div>

                <div class="interview-section">
                    <?php if ($upcoming_interviews && $upcoming_interviews->num_rows > 0): ?>
                        <?php while ($interview = $upcoming_interviews->fetch_assoc()): ?>
                        <div class="interview-item">
                            <div class="interview-candidate">
                                <div class="interview-name"><?php echo htmlspecialchars($interview['applicant_name']); ?></div>
                                <div class="interview-position"><?php echo htmlspecialchars($interview['position']); ?></div>
                            </div>
                            <div class="interview-time">
                                <div class="interview-date"><?php echo date('d', strtotime($interview['interview_date'])); ?></div>
                                <div class="interview-timerange"><?php echo date('M', strtotime($interview['interview_date'])); ?></div>
                                <div class="interview-type"><?php echo htmlspecialchars($interview['interview_type']); ?></div>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div style="text-align:center; color:#64748b; padding:2rem;">No interviews scheduled yet. <a href="interviews.php" style="color:#082544; font-weight:600;">Schedule one</a></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</main>

<link rel="stylesheet" href="../../../Assets/CSS/company-dashboard.css">
<script src="../../../Assets/JS/company-dashboard.js"></script>

<?php include "../../../Includes/dash_footer.php"; ?>