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

$companyName = $company['Name'];
$dashboardCounts = $companyManager->getDashboardCounts($companyName);
$companyApplicationCount = $dashboardCounts['total_applications'];
$recentApplications = $companyManager->getRecentApplications($companyName);
$recentInternships = $companyManager->getRecentInternships($companyName);
$interviewQueue = $companyManager->getInterviewQueue($companyName);
$conversionRate = $dashboardCounts['total_applications'] > 0
    ? round(($dashboardCounts['shortlisted'] / $dashboardCounts['total_applications']) * 100)
    : 0;
$hiringGoal = 20;
$hiringProgress = min(100, round(($dashboardCounts['hired'] / $hiringGoal) * 100));

// Company dashboard styles are isolated from the student and organization dashboards.
$extra_css = '<link rel="stylesheet" href="../../../Assets/CSS/Company/dashboard.css">';
include "../../../Includes/company_sidebar.php";
include "../../../Includes/dash_header.php";
?>

<main class="content company-dashboard">
    <section class="company-welcome">
        <div><h1>Welcome back, <?= htmlspecialchars($companyName) ?></h1><p>Here is an overview of your recruitment momentum today.</p></div>
        <article class="company-summary-card">
            <div class="company-logo-mark"><img src="../../../Assets/Images/logo.png" alt="<?= htmlspecialchars($companyName) ?>"></div>
            <div><h2><?= htmlspecialchars($companyName) ?> <?php if (strtolower($company['Status']) === 'verify' || strtolower($company['Status']) === 'verified'): ?><span class="verified-mark" title="Verified company">&#10022;</span><?php endif; ?></h2><div class="company-meta"><span><span class="material-symbols-outlined">location_on</span><?= htmlspecialchars($company['location']) ?></span><span><span class="material-symbols-outlined">domain</span><?= htmlspecialchars($company['companytype']) ?></span></div></div>
        </article>
    </section>

    <section class="company-stat-grid" aria-label="Recruitment summary">
        <article class="company-stat-card blue"><div class="stat-card-head"><span class="company-stat-icon"><img src="../../../Assets/Images/Icons/internship.png" alt=""></span><small><?= $dashboardCounts['total_internships'] ?> total</small></div><p>Active Internships</p><strong><?= $dashboardCounts['active_internships'] ?></strong></article>
        <article class="company-stat-card orange"><div class="stat-card-head"><span class="company-stat-icon"><img src="../../../Assets/Images/Icons/application.png" alt=""></span><small class="positive">+<?= $dashboardCounts['new_this_week'] ?> this week</small></div><p>Total Applications</p><strong><?= $dashboardCounts['total_applications'] ?></strong></article>
        <article class="company-stat-card blue"><div class="stat-card-head"><span class="company-stat-icon"><img src="../../../Assets/Images/Icons/shortlist.png" alt=""></span><small><?= $conversionRate ?>% conversion</small></div><p>Shortlisted</p><strong><?= $dashboardCounts['shortlisted'] ?></strong></article>
        <article class="company-stat-card orange"><div class="stat-card-head"><span class="company-stat-icon"><img src="../../../Assets/Images/Icons/interviews.png" alt=""></span><small>In queue</small></div><p>Interviews</p><strong><?= $dashboardCounts['interviews'] ?></strong></article>
    </section>

    <div class="company-dashboard-grid">
        <div class="company-main-column">
            <section class="company-panel applications-panel">
                <div class="company-panel-header"><h2>Recent Applications</h2><a href="#" aria-disabled="true">View All</a></div>
                <div class="applications-table-wrap"><table class="applications-table">
                    <thead><tr><th>Candidate</th><th>University</th><th>Position</th><th>Status</th><th>Action</th></tr></thead>
                    <tbody>
                        <?php if (empty($recentApplications)): ?>
                            <tr><td colspan="5" class="company-empty-state">No applications have been received yet.</td></tr>
                        <?php else: ?>
                            <?php foreach ($recentApplications as $application): ?>
                                <?php
                                $candidateName = $application['student_name'] ?? 'Student';
                                $nameParts = preg_split('/\s+/', trim($candidateName));
                                $initials = strtoupper(substr($nameParts[0] ?? 'S', 0, 1) . substr($nameParts[1] ?? '', 0, 1));
                                $status = strtolower(trim($application['status'] ?? 'pending'));
                                if (in_array($status, ['accepted', 'selected', 'hired'])) {
                                    $statusClass = 'selected';
                                } elseif ($status === 'rejected') {
                                    $statusClass = 'rejected';
                                } elseif (in_array($status, ['shortlist', 'shortlisted']) || strpos($status, 'interview') !== false) {
                                    $statusClass = 'shortlisted';
                                } else {
                                    $statusClass = 'new';
                                }
                                ?>
                                <tr>
                                    <td><span class="candidate-avatar"><?= htmlspecialchars($initials) ?></span><b><?= htmlspecialchars($candidateName) ?></b></td>
                                    <td><?= htmlspecialchars($application['University'] ?? 'Not provided') ?></td>
                                    <td><?= htmlspecialchars($application['title'] ?? '') ?></td>
                                    <td><span class="status-pill <?= $statusClass ?>"><?= htmlspecialchars($application['status'] ?? 'Pending') ?></span></td>
                                    <td><span class="table-action" aria-label="Application actions unavailable">&#8942;</span></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table></div>
            </section>

            <section class="recent-internships">
                <div class="section-title-row"><h2>Recent Internships</h2><div><button type="button" aria-label="Previous">&#8249;</button><button type="button" aria-label="Next">&#8250;</button></div></div>
                <div class="internship-card-grid">
                    <?php if (empty($recentInternships)): ?>
                        <div class="company-empty-state internship-empty">No internships have been posted yet.</div>
                    <?php else: ?>
                        <?php foreach ($recentInternships as $internship): ?>
                            <?php
                            $isDataRole = stripos(($internship['title'] ?? '') . ' ' . ($internship['industry'] ?? ''), 'data') !== false;
                            $internshipIcon = $isDataRole ? 'datascience.png' : 'fullstack.png';
                            $deadlineTimestamp = strtotime($internship['deadline'] ?? '');
                            $isActive = $deadlineTimestamp !== false && $deadlineTimestamp >= strtotime('today');
                            $daysLeft = $isActive ? max(0, (int) ceil(($deadlineTimestamp - strtotime('today')) / 86400)) : 0;
                            ?>
                            <article class="internship-card">
                                <div class="internship-card-head"><img src="../../../Assets/Images/Icons/<?= $internshipIcon ?>" alt=""><span class="<?= $isActive ? '' : 'closed' ?>"><?= $isActive ? 'Active' : 'Closed' ?></span></div>
                                <h3><?= htmlspecialchars($internship['title'] ?? '') ?></h3>
                                <p><?= htmlspecialchars($internship['tech_tags'] ?: ($internship['industry'] ?? 'Internship opportunity')) ?></p>
                                <div class="internship-meta"><span><span class="material-symbols-outlined">group</span><?= (int) $internship['applicant_count'] ?> Applicants</span><span><span class="material-symbols-outlined">schedule</span><?= $isActive ? $daysLeft . ' Days left' : 'Deadline passed' ?></span></div>
                            </article>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </section>
        </div>

        <aside class="company-side-column">
            <section class="company-panel interview-panel">
                <div class="company-panel-header"><h2>Upcoming Interviews</h2><span class="material-symbols-outlined">calendar_month</span></div>
                <div class="interview-list">
                    <?php if (empty($interviewQueue)): ?>
                        <div class="company-empty-state">No candidates are currently waiting for an interview.</div>
                    <?php else: ?>
                        <?php foreach ($interviewQueue as $interview): ?>
                            <?php $queueDate = strtotime($interview['applied_date'] ?? '') ?: time(); ?>
                            <article class="interview-item">
                                <div class="interview-date"><strong><?= date('d', $queueDate) ?></strong><span><?= date('M', $queueDate) ?></span></div>
                                <div><h3>Interview with <?= htmlspecialchars($interview['student_name'] ?? 'Candidate') ?></h3><p><?= htmlspecialchars($interview['title'] ?? '') ?></p><small>Schedule pending</small></div>
                            </article>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <span class="panel-footer-link">View All Schedule</span>
            </section>
            <section class="hiring-goal-card"><h2>Hiring Goal</h2><p>You are <?= $hiringProgress ?>% towards your intern intake goal.</p><div class="goal-progress"><span style="width: <?= $hiringProgress ?>%"></span></div><div class="goal-labels"><span><?= $dashboardCounts['hired'] ?> Hired</span><span><?= $hiringGoal ?> Goal</span></div><span class="goal-action-disabled">Boost Listings</span></section>
        </aside>
    </div>
</main>

<footer class="company-footer"><span>&copy; 2026 SkillBridge. All rights reserved.</span><nav><a href="#">Help Center</a><a href="#">Privacy Policy</a><a href="#">Terms of Service</a></nav></footer>
<?php include "../../../Includes/dash_footer.php"; ?>
