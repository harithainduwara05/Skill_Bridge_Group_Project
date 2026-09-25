<?php

include "../../../Config/db.php";
include "../../../Session/Session.php";

require_role('organization');
$user = current_user();
$organization_email = $user['email'];

// ---------------------------------------------------------------
// UI-ONLY DEMO DATA (no database yet). Replace with real queries later.
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
    ],
    [
        'icon' => 'api', 'title' => 'Secure API Gateway Implementation', 'team' => 'Omega Devs', 'days_ago' => 21,
        'rating' => 4,
        'skills' => ['Technical Skills' => 4.2, 'Communication' => 4.0, 'Teamwork' => 4.5, 'Problem-solving' => 4.0],
        'summary' => 'Strong execution of the security protocols and OAuth2 implementation. The team was highly responsive to feedback regarding the latency issues in the initial staging environment and optimized the caching layer effectively.',
        'improvements' => ['Security Auditing', 'GraphQL Optimization'],
    ],
];

$ratingLabels = [5 => 'Exceptional', 4 => 'Very Good', 3 => 'Good', 2 => 'Fair', 1 => 'Needs Work'];
$projectOptions = array_column($reviews, 'title');

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

include "../../../Includes/org_sidebar.php";
include "../../../Includes/dash_header.php";
?>

<style>
    .fb-wrap { padding: 14px 28px 28px; }

    /* ---- heading ---- */
    .fb-head { display: flex; justify-content: space-between; align-items: flex-start; gap: 16px; flex-wrap: wrap; margin-bottom: 20px; }
    .fb-head h1 { margin: 0; font-size: 32px; font-weight: 800; color: #0f3a66; line-height: 1.15; }
    .fb-head p { margin: 6px 0 0; font-size: 14.5px; color: #4b5563; }
    .fb-btn { display: inline-flex; align-items: center; justify-content: center; gap: 6px; padding: 10px 18px; border-radius: 8px; font-family: inherit;
              font-size: 14px; font-weight: 600; cursor: pointer; text-decoration: none; border: 1px solid transparent; transition: all .15s ease; }
    .fb-btn .material-symbols-outlined { font-size: 18px; }
    .fb-btn.solid { background: #0f2a4a; color: #fff; }
    .fb-btn.solid:hover { background: #0a1f38; }
    .fb-btn.outline { background: #fff; border-color: #cbd5e1; color: #1f2937; }
    .fb-btn.outline:hover { background: #f1f5f9; }

    /* ---- stat cards ---- */
    .fb-stats { display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px; margin-bottom: 18px; }
    .fb-stat { background: #fff; border: 1px solid #e5e7eb; border-radius: 14px; padding: 16px 18px; }
    .fb-stat-top { display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px; }
    .fb-stat-icon { width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; }
    .fb-stat-icon .material-symbols-outlined { font-size: 22px; }
    .fb-stat-icon.blue  { background: #dbeafe; color: #1d4ed8; }
    .fb-stat-icon.sky   { background: #e0ecf7; color: #1e4e79; }
    .fb-stat-icon.green { background: #dcfce7; color: #15803d; }
    .fb-pill { font-size: 11px; font-weight: 700; padding: 3px 9px; border-radius: 999px; }
    .fb-pill.up     { background: #dcfce7; color: #166534; }
    .fb-pill.urgent { background: #fee2e2; color: #b91c1c; }
    .fb-stat-label { font-size: 11px; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; color: #6b7280; }
    .fb-stat-value { font-size: 28px; font-weight: 800; color: #111827; line-height: 1.2; margin-top: 2px; }
    .fb-stat-value small { font-size: 14px; font-weight: 500; color: #6b7280; }
    .fb-star { color: #d1d5db; font-variation-settings: 'FILL' 1; }
    .fb-star.on { color: #f59e0b; }

    /* ---- filter bar ---- */
    .fb-filters { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; background: #fff; border: 1px solid #e5e7eb; border-radius: 14px; padding: 12px 14px; margin-bottom: 18px; }
    .fb-select-wrap { position: relative; }
    .fb-select-wrap .lead { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); font-size: 18px; color: #4b5563; pointer-events: none; }
    .fb-select { padding: 10px 34px 10px 38px; border: 1px solid #d1d5db; border-radius: 10px; background: #fff; font-family: inherit; font-size: 14px; color: #374151; cursor: pointer; }
    .fb-sort { margin-left: auto; display: inline-flex; align-items: center; gap: 6px; background: none; border: none; font-family: inherit; font-size: 14px; font-weight: 600; color: #1f2937; cursor: pointer; }
    .fb-sort .material-symbols-outlined { font-size: 18px; }

    /* ---- review cards ---- */
    .fb-list { display: flex; flex-direction: column; gap: 18px; }
    .fb-card { background: #fff; border: 1px solid #e5e7eb; border-radius: 16px; padding: 22px 24px; box-shadow: 0 1px 3px rgba(0,0,0,.04); }
    .fb-top { display: flex; justify-content: space-between; align-items: flex-start; gap: 16px; margin-bottom: 18px; }
    .fb-title-row { display: flex; align-items: center; gap: 14px; }
    .fb-proj-icon { width: 46px; height: 46px; border-radius: 12px; background: #e0ecf7; color: #1e4e79; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .fb-proj-icon .material-symbols-outlined { font-size: 24px; }
    .fb-title { font-size: 18px; font-weight: 700; color: #111827; }
    .fb-meta { display: flex; align-items: center; gap: 6px; font-size: 13px; color: #4b5563; margin-top: 3px; flex-wrap: wrap; }
    .fb-meta .material-symbols-outlined { font-size: 16px; }
    .fb-rating { text-align: right; flex-shrink: 0; }
    .fb-rating .stars { display: flex; justify-content: flex-end; }
    .fb-rating-label { font-size: 11px; font-weight: 800; letter-spacing: .06em; text-transform: uppercase; color: #111827; margin-top: 2px; }

    .fb-body { display: grid; grid-template-columns: 340px 1fr; gap: 24px; }
    .fb-comp { border: 1px solid #e5e7eb; border-radius: 12px; padding: 16px 18px; align-self: start; }
    .fb-label { font-size: 11px; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; color: #6b7280; margin-bottom: 12px; }
    .fb-comp-row { margin-bottom: 12px; }
    .fb-comp-row:last-child { margin-bottom: 0; }
    .fb-comp-top { display: flex; justify-content: space-between; font-size: 13px; color: #374151; margin-bottom: 5px; }
    .fb-comp-top strong { color: #111827; }
    .fb-track { height: 6px; background: #e5e7eb; border-radius: 999px; overflow: hidden; }
    .fb-track span { display: block; height: 100%; background: #0f3a66; border-radius: 999px; }

    .fb-section { margin-bottom: 18px; }
    .fb-summary { margin: 0; font-size: 14px; line-height: 1.6; color: #374151; }
    .fb-chips { display: flex; flex-wrap: wrap; gap: 8px; }
    .fb-chip { background: #dbeafe; color: #1e3a5f; font-size: 12px; font-weight: 600; padding: 5px 12px; border-radius: 999px; }

    .fb-foot { display: flex; justify-content: flex-end; align-items: center; gap: 22px; flex-wrap: wrap; margin-top: 4px; }
    .fb-textbtn { display: inline-flex; align-items: center; gap: 6px; background: none; border: none; font-family: inherit; font-size: 14px; color: #374151; cursor: pointer; padding: 0; }
    .fb-textbtn:hover { color: #0f3a66; }
    .fb-textbtn .material-symbols-outlined { font-size: 18px; }

    /* ---- load more ---- */
    .fb-more { text-align: center; margin-top: 26px; }
    .fb-more p { margin: 10px 0 0; font-size: 13px; color: #6b7280; }

    @media (max-width: 1100px) { .fb-stats { grid-template-columns: repeat(2, 1fr); } }
    @media (max-width: 900px)  { .fb-body { grid-template-columns: 1fr; } }
    @media (max-width: 600px)  { .fb-wrap { padding: 12px 16px 24px; } .fb-head h1 { font-size: 24px; } .fb-stats { grid-template-columns: 1fr; } .fb-top { flex-direction: column; } .fb-rating { text-align: left; } .fb-rating .stars { justify-content: flex-start; } }
</style>

<main class="content">
    <div class="fb-wrap">

        <!-- Heading -->
        <div class="fb-head">
            <div>
                <h1>Project Feedback &amp; Reviews</h1>
                <p>Manage and analyze technical performance across your academic collaborations.</p>
            </div>
            <button type="button" class="fb-btn solid"><span class="material-symbols-outlined">add</span>Submit New Feedback</button>
        </div>

        <!-- Stat cards -->
        <div class="fb-stats">
            <div class="fb-stat">
                <div class="fb-stat-top">
                    <div class="fb-stat-icon blue"><span class="material-symbols-outlined">rate_review</span></div>
                    <span class="fb-pill up"><?= htmlspecialchars($summary['submitted_change']) ?></span>
                </div>
                <div class="fb-stat-label">Feedback Submitted</div>
                <div class="fb-stat-value"><?= (int)$summary['submitted'] ?></div>
            </div>
            <div class="fb-stat">
                <div class="fb-stat-top">
                    <div class="fb-stat-icon sky"><span class="material-symbols-outlined">pending_actions</span></div>
                    <span class="fb-pill urgent">Urgent</span>
                </div>
                <div class="fb-stat-label">Pending Reviews</div>
                <div class="fb-stat-value"><?= (int)$summary['pending'] ?></div>
            </div>
            <div class="fb-stat">
                <div class="fb-stat-top">
                    <div class="fb-stat-icon green"><span class="material-symbols-outlined" style="font-variation-settings:'FILL' 1;">star</span></div>
                    <div style="display:flex;"><?= fbStars($summary['avg'], 14) ?></div>
                </div>
                <div class="fb-stat-label">Average Rating</div>
                <div class="fb-stat-value"><?= number_format($summary['avg'], 1) ?><small>/5.0</small></div>
            </div>
            <div class="fb-stat">
                <div class="fb-stat-top">
                    <div class="fb-stat-icon sky"><span class="material-symbols-outlined">task_alt</span></div>
                </div>
                <div class="fb-stat-label">Completed Projects</div>
                <div class="fb-stat-value"><?= (int)$summary['completed'] ?></div>
            </div>
        </div>

        <!-- Filters -->
        <div class="fb-filters">
            <div class="fb-select-wrap">
                <span class="material-symbols-outlined lead">filter_list</span>
                <select class="fb-select">
                    <option>Filter by Rating</option>
                    <?php foreach ([5, 4, 3, 2, 1] as $r): ?>
                        <option><?= $r ?> Star<?= $r > 1 ? 's' : '' ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="fb-select-wrap">
                <span class="material-symbols-outlined lead">filter_list</span>
                <select class="fb-select">
                    <option>Filter by Project</option>
                    <?php foreach ($projectOptions as $po): ?>
                        <option><?= htmlspecialchars($po) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="button" class="fb-sort"><span class="material-symbols-outlined">sort</span>Latest First</button>
        </div>

        <!-- Review cards -->
        <div class="fb-list">
            <?php foreach ($reviews as $r): ?>
                <div class="fb-card">

                    <div class="fb-top">
                        <div class="fb-title-row">
                            <div class="fb-proj-icon"><span class="material-symbols-outlined"><?= htmlspecialchars($r['icon']) ?></span></div>
                            <div>
                                <div class="fb-title"><?= htmlspecialchars($r['title']) ?></div>
                                <div class="fb-meta">
                                    <span class="material-symbols-outlined">groups</span><?= htmlspecialchars($r['team']) ?>
                                    <span>&bull;</span>
                                    <?= date('M d, Y', strtotime('-' . (int)$r['days_ago'] . ' day')) ?>
                                </div>
                            </div>
                        </div>
                        <div class="fb-rating">
                            <div class="stars"><?= fbStars($r['rating'], 20) ?></div>
                            <div class="fb-rating-label"><?= $ratingLabels[$r['rating']] ?></div>
                        </div>
                    </div>

                    <div class="fb-body">
                        <div class="fb-comp">
                            <div class="fb-label">Competency Breakdown</div>
                            <?php foreach ($r['skills'] as $name => $score): ?>
                                <div class="fb-comp-row">
                                    <div class="fb-comp-top"><span><?= htmlspecialchars($name) ?></span><strong><?= number_format($score, 1) ?></strong></div>
                                    <div class="fb-track"><span style="width:<?= round($score / 5 * 100) ?>%;"></span></div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <div>
                            <div class="fb-section">
                                <div class="fb-label">Performance Summary</div>
                                <p class="fb-summary"><?= htmlspecialchars($r['summary']) ?></p>
                            </div>
                            <div class="fb-section">
                                <div class="fb-label">Suggested Improvements</div>
                                <div class="fb-chips">
                                    <?php foreach ($r['improvements'] as $imp): ?><span class="fb-chip"><?= htmlspecialchars($imp) ?></span><?php endforeach; ?>
                                </div>
                            </div>
                            <div class="fb-foot">
                                <button type="button" class="fb-textbtn"><span class="material-symbols-outlined">edit</span>Edit</button>
                                <button type="button" class="fb-textbtn"><span class="material-symbols-outlined">description</span>Report</button>
                                <button type="button" class="fb-btn solid">View Full Details</button>
                            </div>
                        </div>
                    </div>

                </div>
            <?php endforeach; ?>
        </div>

        <!-- Load more -->
        <div class="fb-more">
            <button type="button" class="fb-btn outline">Load More Records</button>
            <p>Showing <?= count($reviews) ?> of <?= (int)$summary['submitted'] ?> completed reviews</p>
        </div>

    </div>
</main>

<footer class="footer">
    <div>&copy; 2026 SkillBridge. All rights reserved.</div>
    <div class="footer-links">
        <a href="#">Help Center</a>
        <a href="#">Privacy Policy</a>
        <a href="#">Terms of Service</a>
    </div>
</footer>

<?php include "../../../Includes/dash_footer.php"; ?>