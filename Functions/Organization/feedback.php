<?php

include "../../Config/db.php";
include "../../Session/Session.php";

require_role('organization');

// Backend (PHP + SQL) of this page
require_once __DIR__ . "/../../Backend/Organization/feedback.php";

// ?v=<file time> makes the browser load the NEW css every time the file changes
$extra_css = '<link rel="stylesheet" href="../../Assets/CSS/Organization/feedback.css?v='
           . filemtime(__DIR__ . '/../../Assets/CSS/Organization/feedback.css') . '">';
include "../../Includes/org_sidebar.php";
include "../../Includes/dash_header.php";
?>



<main class="content">
    <div class="fb-wrap">

        <!-- Heading -->
        <div class="fb-head">
            <div>
                <h1>Project Feedback &amp; Reviews</h1>
                <p>Manage and analyze technical performance across your academic collaborations.</p>
            </div>
            <button type="button" class="fb-btn solid" id="fbOpenForm"><span class="material-symbols-outlined">add</span>Submit New Feedback</button>
        </div>

        <!-- Stat cards -->
        <div class="fb-stats">
            <div class="fb-stat">
                <div class="fb-stat-top">
                    <div class="fb-stat-icon blue"><span class="material-symbols-outlined">rate_review</span></div>
                </div>
                <div class="fb-stat-label">Feedback Submitted</div>
                <div class="fb-stat-value"><?= (int)$summary['submitted'] ?></div>
            </div>
            <div class="fb-stat">
                <div class="fb-stat-top">
                    <div class="fb-stat-icon sky"><span class="material-symbols-outlined">pending_actions</span></div>
                </div>
                <div class="fb-stat-label">Pending Reviews</div>
                <div class="fb-stat-value"><?= (int)$summary['pending'] ?></div>
            </div>
            <div class="fb-stat">
                <div class="fb-stat-top">
                    <div class="fb-stat-icon green"><span class="material-symbols-outlined" style="font-variation-settings:'FILL' 1;">star</span></div>
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
            <div class="fb-search">
                <span class="material-symbols-outlined">search</span>
                <input type="text" id="fbSearch" placeholder="Search by project or team name">
            </div>
            <div class="fb-select-wrap">
                <span class="material-symbols-outlined lead">star</span>
                <select class="fb-select" id="fbRating">
                    <option value="all">All ratings</option>
                    <?php foreach ([5, 4, 3, 2, 1] as $r): ?>
                        <option value="<?= $r ?>"><?= $r ?> Star<?= $r > 1 ? 's' : '' ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="fb-select-wrap">
                <span class="material-symbols-outlined lead">sort</span>
                <select class="fb-select" id="fbSort">
                    <option value="latest">Latest first</option>
                    <option value="oldest">Oldest first</option>
                    <option value="high">Highest rating</option>
                    <option value="low">Lowest rating</option>
                </select>
            </div>
            <button type="button" class="fb-clear" id="fbClear" hidden>Clear</button>
        </div>

        <?php if (!$reviews): ?>
            <div class="fb-empty">
                <span class="material-symbols-outlined">rate_review</span>
                <div>You have not given any feedback yet.</div>
                <small>Feedback can be given to a team once it is Completed (you accepted its final report on the Teams page).</small>
            </div>
        <?php endif; ?>

        <!-- Review cards -->
        <div class="fb-list">
            <?php foreach ($reviews as $ri => $r): ?>
                <div class="fb-card" data-fb-index="<?= (int)$ri ?>"
                     data-rating="<?= (int)$r['rating'] ?>"
                     data-created="<?= (int)$r['created'] ?>"
                     data-search="<?= htmlspecialchars(strtolower($r['title'] . ' ' . $r['team'])) ?>">

                    <div class="fb-top">
                        <div class="fb-title-row">
                            <div class="fb-proj-icon"><span class="material-symbols-outlined"><?= htmlspecialchars($r['icon']) ?></span></div>
                            <div>
                                <div class="fb-title"><?= htmlspecialchars($r['title']) ?>
                                    <?php if (!empty($r['is_new'])): ?><span class="fb-new">New</span><?php endif; ?></div>
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
                                <button type="button" class="fb-btn solid" data-fb-view="<?= (int)$ri ?>">View Full Details</button>
                            </div>
                        </div>
                    </div>

                </div>
            <?php endforeach; ?>
        </div>

        <div class="fb-empty" id="fbEmpty" hidden>
            <span class="material-symbols-outlined">search_off</span>
            <div>No feedback matches your filters</div>
        </div>

        <!-- Load more -->
        <div class="fb-more">
            <button type="button" class="fb-btn outline" id="fbLoadMore"><span class="material-symbols-outlined">expand_more</span>Load More Records</button>
            <p>Showing <span id="fbShown"><?= count($reviews) ?></span> of <span id="fbTotal"><?= count($reviews) ?></span> reviews</p>
        </div>

    </div>

    <!-- ===================== FEEDBACK DETAILS (View Full Details) ===================== -->
    <div class="fb-overlay" id="fbViewModal">
        <div class="fb-modal fv-modal" role="dialog" aria-modal="true">
            <div class="fv-head">
                <div class="fv-head-main">
                    <div class="fv-kicker">Feedback Details</div>
                    <h3 id="fvTitle"></h3>
                    <div class="fv-meta" id="fvMeta"></div>
                </div>
                <div class="fv-score">
                    <div class="fv-score-num" id="fvScore"></div>
                    <div class="fv-score-stars" id="fvStars"></div>
                    <div class="fv-score-label" id="fvLabel"></div>
                </div>
                <button type="button" class="fb-x" data-fv-close aria-label="Close"><span class="material-symbols-outlined">close</span></button>
            </div>
            <div class="fv-body" id="fvBody"></div>
            <div class="fb-m-foot fv-foot">
                <button type="button" class="fb-btn outline" id="fvPrint"><span class="material-symbols-outlined">print</span>Print / Save PDF</button>
                <div style="display:flex; gap:10px; margin-left:auto;">
                    <button type="button" class="fb-btn outline" data-fv-close>Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- ===================== SUBMIT NEW FEEDBACK ===================== -->
    <div class="fb-overlay" id="fbModal">
        <form class="fb-modal" method="post" action="feedback.php" id="fbForm" novalidate>
            <input type="hidden" name="action" value="submit_feedback" id="fbAction">
            <input type="hidden" name="rating" id="fbRatingVal">

            <div class="fb-m-head">
                <div class="fb-m-icon"><span class="material-symbols-outlined">rate_review</span></div>
                <div>
                    <h3 id="fbFormTitle">Submit New Feedback</h3>
                    <p id="fbFormSub">Rate the team’s work and help them improve.</p>
                </div>
                <button type="button" class="fb-x" data-fb-close aria-label="Close"><span class="material-symbols-outlined">close</span></button>
            </div>

            <div class="fb-m-body">
                <!-- shown only for NEW feedback when no project can get feedback -->
                <div class="fb-note" id="fbNoProjects" hidden>
                    <span class="material-symbols-outlined">info</span>
                    <span>No team can get new feedback right now. Feedback can be given once to each team that is
                    <b>Completed</b> (you accepted its final report on the Teams page) and does not have feedback yet.</span>
                </div>

                <!-- the form fields (hidden only when no project can get feedback) -->
                <div id="fbFields">

                <!-- 1. Project -->
                <div class="fb-step"><span>1</span> Project &amp; Team</div>
                <div class="fb-field">
                    <label class="fb-f-label" for="fbProject">Project &amp; Team *</label>
                    <!-- feedback is given to a team (value = team id) -->
                    <select id="fbProject" name="team_id" class="fb-input">
                        <option value="">Select a project team</option>
                        <?php foreach ($fbProjects as $fp): ?>
                            <option value="<?= $fp['id'] ?>"><?= htmlspecialchars($fp['title'] . ' — ' . $fp['team']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <div class="fb-err" data-err="project"></div>
                </div>
                <div class="fb-team" id="fbTeamBox" hidden></div>

                <!-- 2. Ratings -->
                <div class="fb-step"><span>2</span> Ratings</div>
                <div class="fb-field">
                    <label class="fb-f-label">Overall Rating *</label>
                    <div class="fb-overall">
                        <div class="fb-star-pick" id="fbStarPick">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <button type="button" data-v="<?= $i ?>" aria-label="<?= $i ?> star"><span class="material-symbols-outlined">star</span></button>
                            <?php endfor; ?>
                        </div>
                        <span class="fb-star-text" id="fbStarText">Click to rate</span>
                    </div>
                    <div class="fb-err" data-err="rating"></div>
                </div>

                <div class="fb-f-label" style="margin-top:4px;">Competency Breakdown *</div>
                <div class="fb-comp-grid">
                    <?php foreach (['technical' => 'Technical Skills', 'communication' => 'Communication', 'teamwork' => 'Teamwork', 'problem_solving' => 'Problem-solving'] as $k => $label): ?>
                        <div class="fb-slider">
                            <div class="fb-slider-top"><span><?= $label ?></span><strong data-out="<?= $k ?>">3.0</strong></div>
                            <input type="range" name="<?= $k ?>" min="1" max="5" step="0.5" value="3" data-range="<?= $k ?>">
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- 3. Written feedback -->
                <div class="fb-step"><span>3</span> Written Feedback</div>
                <div class="fb-field">
                    <label class="fb-f-label" for="fbSummary">Performance Summary *</label>
                    <textarea id="fbSummary" name="summary" class="fb-input" rows="4" maxlength="1500"
                              placeholder="What did the team do well? How was the quality of their work and their communication?"></textarea>
                    <div class="fb-counter"><span class="fb-err" data-err="summary"></span><span><span id="fbCount">0</span>/1500</span></div>
                </div>

                <div class="fb-field">
                    <label class="fb-f-label">Suggested Improvements</label>
                    <div class="fb-chip-box" id="fbChipBox">
                        <input type="text" id="fbChipInput" maxlength="40" placeholder="Type a skill to improve and press Enter">
                    </div>
                    <input type="hidden" name="improvements" id="fbImprovements">
                    <div class="fb-quick">
                        <span>Quick add:</span>
                        <?php foreach (['Documentation', 'Unit Testing', 'Time Management', 'Code Reviews', 'Git Workflow'] as $qa): ?>
                            <button type="button" data-quick="<?= $qa ?>">+ <?= $qa ?></button>
                        <?php endforeach; ?>
                    </div>
                </div>


                    <div class="fb-final-note">
                        <span class="material-symbols-outlined">lock</span>
                        <span>Please check your feedback before submitting. <b>Feedback is final</b> and can’t be changed after it is submitted.</span>
                    </div>
                </div>
            </div>

            <div class="fb-m-foot">
                <button type="button" class="fb-btn outline" data-fb-close>Cancel</button>
                <button type="submit" class="fb-btn solid" id="fbSubmitBtn"><span class="material-symbols-outlined" id="fbSubmitIcon">send</span><span id="fbSubmitText">Submit Feedback</span></button>
            </div>
        </form>
    </div>

    <?php if ($flash): ?>
    <div class="flash-toast flash-<?= htmlspecialchars($flash['type']) ?>" id="fbFlash">
        <div class="flash-icon-wrap"><span class="material-symbols-outlined"><?= $flash['type'] === 'success' ? 'check_circle' : 'error' ?></span></div>
        <div class="flash-content">
            <div class="flash-title" style="font-weight:700; font-size:14px;"><?= htmlspecialchars($flash['title']) ?></div>
            <div class="flash-msg" style="font-size:13px; margin-top:2px;"><?= htmlspecialchars($flash['message']) ?></div>
        </div>
    </div>
    <?php endif; ?>
</main>

<script>
    // Values from PHP (database) used by Assets/JS/Organization/feedback.js
    const ORG_DATA = {
        PROJECTS: <?= json_encode($fbProjects, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?>,
        REVIEWS: <?= json_encode(array_values(array_map(fn($r) => [
                        'key' => $r['key'], 'id' => $r['id'] ?? null, 'project_id' => $r['project_id'] ?? null,
                        'title' => $r['title'], 'team' => $r['team'], 'category' => $r['category'] ?? '',
                        'date' => date('M d, Y', $r['created']), 'rating' => (int)$r['rating'], 'skills' => $r['skills'],
                        'summary' => $r['summary'], 'improvements' => $r['improvements'],
                        'members' => $r['members'] ?? [],
                    ], $reviews)), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?>,
    };
</script>
<script src="../../Assets/JS/Organization/feedback.js?v=<?= filemtime(__DIR__ . '/../../Assets/JS/Organization/feedback.js') ?>"></script>


<?php include "../../Includes/dash_footer.php"; ?>