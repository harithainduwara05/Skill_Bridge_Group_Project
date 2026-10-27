<?php

include "../../Config/db.php";
include "../../Session/Session.php";

require_role('organization');

// Backend (PHP + SQL) of this page
require_once __DIR__ . "/../../Backend/Organization/teams.php";

$extra_css = '<link rel="stylesheet" href="../../Assets/CSS/Organization/teams.css?v=' . filemtime(__DIR__ . '/../../Assets/CSS/Organization/teams.css') . '">';
include "../../Includes/org_sidebar.php";
include "../../Includes/dash_header.php";
?>



<main class="content">
    <div class="tm-wrap" id="tmListView">

        <!-- Summary cards -->
        <div class="tm-stats">
            <?php foreach ($summary as $s): ?>
                <div class="tm-stat">
                    <div class="tm-stat-icon <?= $s['class'] ?>"><span class="material-symbols-outlined"><?= $s['icon'] ?></span></div>
                    <div>
                        <div class="tm-stat-label"><?= htmlspecialchars($s['label']) ?></div>
                        <div class="tm-stat-value"><?= (int)$s['value'] ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Heading + actions -->
        <div class="tm-head">
            <div>
                <h2>Active Teams Overview</h2>
                <p>Manage and monitor student collaborative projects.</p>
            </div>
            <div class="tm-head-actions">
                <div class="tm-filter-wrap">
                    <button type="button" class="tm-btn" id="tmFilterBtn" aria-expanded="false">
                        <span class="material-symbols-outlined">filter_list</span>Filter
                        <span class="tm-filter-count" id="tmFilterCount" hidden>0</span>
                    </button>

                    <div class="tm-filter-panel" id="tmFilterPanel" hidden>
                        <div class="tm-filter-row">
                            <label class="tm-f-label" for="tmFilterSearch">Search</label>
                            <input type="text" id="tmFilterSearch" class="tm-input" placeholder="Team, project, leader or member">
                        </div>
                        <div class="tm-filter-row">
                            <label class="tm-f-label">Status</label>
                            <div class="tm-seg" id="tmFilterStatus">
                                <button type="button" data-value="all" class="on">All</button>
                                <button type="button" data-value="ontrack">On Track</button>
                                <button type="button" data-value="behind">Behind</button>
                                <button type="button" data-value="completed">Completed</button>
                            </div>
                        </div>
                        <div class="tm-filter-row">
                            <label class="tm-f-label" for="tmFilterSort">Sort by</label>
                            <select id="tmFilterSort" class="tm-input">
                                <option value="newest">Newest first</option>
                                <option value="deadline">Deadline (soonest first)</option>
                                <option value="progress_low">Progress (lowest first)</option>
                                <option value="progress_high">Progress (highest first)</option>
                            </select>
                        </div>
                        <div class="tm-filter-foot">
                            <button type="button" class="tm-link" id="tmFilterClear">Clear filters</button>
                            <span class="tm-filter-result" id="tmFilterResult"></span>
                        </div>
                    </div>
                </div>

                <button type="button" class="tm-btn solid" id="tmCreateBtn"><span class="material-symbols-outlined">add</span>Create New Team</button>
            </div>
        </div>

        <!-- Team cards -->
        <div class="tm-grid">
            <?php $tmOrder = 0; foreach ($teams as $ti => $t): ?>
                <?php
                    $searchText = strtolower($t['name'] . ' ' . $t['project'] . ' ' . $t['leader'] . ' ' . implode(' ', $t['members']));
                    $deadlineTs = $t['deadline'] !== '' ? (int)strtotime($t['deadline']) : 0;
                ?>
                <div class="tm-card <?= $t['state'] === 'completed' ? 'completed' : '' ?>" data-team-index="<?= (int)$ti ?>"
                     data-team-name="<?= htmlspecialchars(strtolower($t['name'])) ?>"
                     data-state="<?= htmlspecialchars($t['state']) ?>"
                     data-search="<?= htmlspecialchars($searchText) ?>"
                     data-order="<?= $tmOrder++ ?>"
                     data-deadline="<?= $deadlineTs ?>"
                     data-progress="<?= (int)$t['percent'] ?>">

                    <div class="tm-top">
                        <div class="tm-title">
                            <span class="tm-name"><?= htmlspecialchars($t['name']) ?></span>
                            <span class="tm-pill <?= $t['state'] ?>"><?= htmlspecialchars($t['status']) ?></span>
                            <?php if (!empty($t['is_new'])): ?><span class="tm-pill new">New</span><?php endif; ?>
                            <?php if (!empty($t['report_waiting'])): ?><span class="tm-pill report"><span class="material-symbols-outlined">fact_check</span>Report to review</span><?php endif; ?>
                        </div>
                        <button type="button" class="tm-kebab" data-team-menu="<?= (int)$ti ?>" aria-label="More options"><span class="material-symbols-outlined">more_vert</span></button>
                    </div>
                    <p class="tm-project">Project: <?= htmlspecialchars($t['project']) ?></p>

                    <div class="tm-people">
                        <div>
                            <div class="tm-label">Team Leader</div>
                            <div class="tm-leader">
                                <div class="tm-av" style="background:<?= $tmColors[abs(crc32($t['leader'])) % count($tmColors)] ?>;"><?= tmInitials($t['leader']) ?></div>
                                <div>
                                    <div class="tm-leader-name"><?= htmlspecialchars($t['leader']) ?></div>
                                    <div class="tm-leader-role"><?= htmlspecialchars($t['role']) ?></div>
                                </div>
                            </div>
                        </div>
                        <div>
                            <div class="tm-label">Team Members</div>
                            <div class="tm-stack">
                                <?php foreach ($t['members'] as $m): ?>
                                    <div class="tm-av" title="<?= htmlspecialchars($m) ?>" style="background:<?= $tmColors[abs(crc32($m)) % count($tmColors)] ?>;"><?= tmInitials($m) ?></div>
                                <?php endforeach; ?>
                                <?php if ($t['more'] > 0): ?><div class="tm-av more">+<?= (int)$t['more'] ?></div><?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <?php if (!empty($t['skills'])): ?>
                        <div class="tm-skills">
                            <div class="tm-label">Skills Covered</div>
                            <div class="tm-chips">
                                <?php foreach ($t['skills'] as $sk): ?><span class="tm-chip"><?= htmlspecialchars($sk) ?></span><?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="tm-progress <?= in_array($t['state'], ['behind', 'completed']) ? $t['state'] : '' ?>">
                        <div class="tm-prow"><span><?= htmlspecialchars($t['phase']) ?></span><span><?= (int)$t['percent'] ?>%</span></div>
                        <div class="tm-bar"><span style="width:<?= (int)$t['percent'] ?>%;"></span></div>
                        <div class="tm-meta">
                            <?php if ($t['deadline'] !== ''): ?>
                                <span class="left"><span class="material-symbols-outlined">calendar_today</span>Deadline: <?= htmlspecialchars($t['deadline']) ?></span>
                            <?php else: ?>
                                <span></span>
                            <?php endif; ?>
                            <span class="right <?= $t['tone'] ?>">
                                <span class="material-symbols-outlined"><?= $t['state'] === 'completed' ? 'check_circle' : ($t['tone'] === 'late' ? 'warning' : 'schedule') ?></span>
                                <?= htmlspecialchars($t['time']) ?>
                            </span>
                        </div>
                    </div>

                    <div class="tm-actions <?= count($t['actions']) === 1 ? 'single' : '' ?>">
                        <?php foreach ($t['actions'] as $i => $a): ?>
                            <button type="button" class="tm-btn <?= in_array($a, ['Final Report', 'Review Report'], true) ? 'report' : ($i === 0 ? 'solid' : '') ?>"
                                <?= in_array($a, ['View', 'Final Report', 'Review Report'], true) ? 'data-view-team="' . (int)$ti . '"' : '' ?>
                                <?= in_array($a, ['Task', 'Chat'], true) ? 'data-work-team="' . (int)$ti . '" data-work-focus="' . ($a === 'Chat' ? 'chat' : 'tasks') . '"' : '' ?>
                                <?= $a === 'Review' ? 'data-review-team="' . (int)$ti . '"' : '' ?>>
                                <span class="material-symbols-outlined"><?= $tmActionIcon[$a] ?></span><?= $a === 'Final Report' ? 'View Final Report' : $a ?>
                            </button>
                        <?php endforeach; ?>
                    </div>

                </div>
            <?php endforeach; ?>
        </div>

        <div class="tm-empty" id="tmEmpty" hidden>
            <span class="material-symbols-outlined">search_off</span>
            <div class="tm-empty-title">No teams match your filters</div>
            <button type="button" class="tm-btn" id="tmEmptyClear">Clear filters</button>
        </div>

    </div>

    <!-- =====================================================
         TEAM WORKSPACE: Kanban board + team chat (same page)
    ====================================================== -->
    <section class="tw-page" id="tmWorkspace" hidden>

        <div class="tw-topbar">
            <button type="button" class="tw-back" id="twBack">
                <span class="material-symbols-outlined">arrow_back</span> Back to Teams
            </button>
            <div class="tw-crumbs"><span>Teams</span><span class="material-symbols-outlined">chevron_right</span><strong id="twCrumb"></strong></div>
        </div>

        <div class="tw-hero">
            <div>
                <div class="tw-kicker"><span class="material-symbols-outlined">rocket_launch</span><span id="twPhase"></span></div>
                <h2 id="twProject"></h2>
                <div class="tw-team-line">
                    <div class="tw-avs" id="twAvatars"></div>
                    <span id="twTeamName"></span>
                </div>
            </div>
            <div class="tw-progress-card">
                <div class="tw-pc-row"><span>Overall Progress</span><strong id="twPct">0%</strong></div>
                <div class="tw-pc-bar"><span id="twBar"></span></div>
                <div class="tw-pc-row small"><span id="twDone"></span><span id="twPending"></span></div>
            </div>
        </div>

        <div class="tw-layout" id="twLayout">
            <!-- Kanban -->
            <div class="tw-board-wrap" id="twBoardWrap">
                <div class="tw-board-head">
                    <h3><span class="material-symbols-outlined">view_kanban</span> Kanban Board</h3>
                    <div class="tw-board-actions">
                        <select class="tw-select" id="twMemberFilter" aria-label="Filter by member">
                            <option value="all">All members</option>
                        </select>
                        <button type="button" class="tm-btn" id="twChatToggle"><span class="material-symbols-outlined">chat_bubble</span><span>Team Chat</span></button>
                        <button type="button" class="tm-btn solid" id="twNewTask"><span class="material-symbols-outlined">add</span>New Task</button>
                    </div>
                </div>
                <div class="tw-board" id="twBoard"></div>

                <!-- Shared Assets -->
                <div class="tw-assets">
                    <h3 class="tw-sec-title"><span class="material-symbols-outlined">folder_open</span> Shared Assets</h3>
                    <div class="tw-assets-grid">
                        <div class="tw-files">
                            <div class="tw-files-head">
                                <span>Team Files</span>
                                <span class="tw-files-count" id="twFilesCount"></span>
                            </div>
                            <div id="twFiles"></div>
                        </div>

                        <label class="tw-drop" id="twDrop">
                            <input type="file" id="twFileInput" multiple hidden
                                   accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.txt,.csv,.png,.jpg,.jpeg,.gif,.webp,.zip,.mp4,.fig">
                            <span class="material-symbols-outlined">cloud_upload</span>
                            <strong>Upload New Assets</strong>
                            <span>Drag and drop files here or click to browse (max 10MB each)</span>
                        </label>
                    </div>
                    <div class="tw-upload-err" id="twUploadErr"></div>
                </div>
            </div>

            <!-- Chat -->
            <aside class="tw-chat" id="twChat">
                <div class="tw-chat-head">
                    <h3>Team Discussion</h3>
                </div>
                <div class="tw-msgs" id="twMsgs"></div>
                <form class="tw-send" id="twSend">
                    <input type="text" id="twInput" placeholder="Type a message to the team…" autocomplete="off" maxlength="500">
                    <button type="submit" aria-label="Send"><span class="material-symbols-outlined">send</span></button>
                </form>
                <div class="tw-chat-err" id="twChatErr"></div>
            </aside>
        </div>
    </section>

    <!-- New Task popup -->
    <div class="tm-overlay" id="twTaskModal">
        <form class="tm-modal" id="twTaskForm" style="width:min(520px,100%);" novalidate>
            <div class="tm-modal-head">
                <div class="tm-modal-icon"><span class="material-symbols-outlined">add_task</span></div>
                <div><h3>New Task</h3><p>Add a task to this team’s board.</p></div>
                <button type="button" class="tm-x" data-close-task aria-label="Close"><span class="material-symbols-outlined">close</span></button>
            </div>
            <div class="tm-modal-body">
                <div class="tm-field">
                    <label class="tm-f-label" for="twTTitle">Task Title *</label>
                    <input type="text" id="twTTitle" class="tm-input" maxlength="120" placeholder="e.g. Design the login screen">
                    <div class="tm-err" id="twTErr"></div>
                </div>
                <!-- teams saved in the database: a short description (team_tasks.description) -->
                <div class="tm-field" id="twTDescWrap" hidden>
                    <label class="tm-f-label" for="twTDesc">Description</label>
                    <textarea id="twTDesc" class="tm-input" maxlength="500" rows="3" placeholder="What needs to be done? (optional)"></textarea>
                </div>
                <div class="tm-grid-2">
                    <div class="tm-field" id="twTTagWrap">
                        <label class="tm-f-label" for="twTTag">Type</label>
                        <select id="twTTag" class="tm-input">
                            <option>Development</option><option>Design</option><option>Research</option>
                            <option>Testing</option><option>Documentation</option>
                        </select>
                    </div>
                    <div class="tm-field">
                        <label class="tm-f-label" for="twTCol">Column</label>
                        <select id="twTCol" class="tm-input">
                            <option value="todo">To Do</option><option value="progress">In Progress</option>
                            <option value="review">Review</option><option value="done">Done</option>
                        </select>
                    </div>
                    <div class="tm-field">
                        <label class="tm-f-label" for="twTWho">Assign To</label>
                        <select id="twTWho" class="tm-input"></select>
                    </div>
                    <div class="tm-field">
                        <label class="tm-f-label" for="twTDue">Due Date</label>
                        <input type="date" id="twTDue" class="tm-input" min="<?= date('Y-m-d') ?>">
                        <div class="tm-err" id="twTDueErr"></div>
                    </div>
                </div>
            </div>
            <div class="tm-modal-foot">
                <button type="button" class="tm-btn" data-close-task>Cancel</button>
                <button type="submit" class="tm-btn solid"><span class="material-symbols-outlined">check</span>Add Task</button>
            </div>
        </form>
    </div>

    <!-- ===================== TEAM DETAILS (View) ===================== -->
    <div class="tm-overlay" id="tmViewModal">
        <div class="tm-modal tv-modal" role="dialog" aria-modal="true" aria-labelledby="tvName">
            <div class="tv-head">
                <div class="tv-head-main">
                    <div class="tv-kicker">Team Details</div>
                    <div class="tv-title-row">
                        <h3 id="tvName"></h3>
                        <span class="tm-pill" id="tvStatus"></span>
                    </div>
                    <div class="tv-project" id="tvProject"></div>
                </div>
                <button type="button" class="tm-x" data-close-view aria-label="Close"><span class="material-symbols-outlined">close</span></button>
            </div>
            <div class="tv-body" id="tvBody"></div>
            <div class="tm-modal-foot">
                <button type="button" class="tm-btn solid" data-close-view>Close</button>
            </div>
        </div>
    </div>

    <!-- ===================== EDIT TEAM ===================== -->
    <div class="tm-overlay" id="tmEditModal">
        <form class="tm-modal" method="post" action="teams.php" id="tmEditForm" novalidate>
            <input type="hidden" name="action" value="edit_team">
            <input type="hidden" name="team_id" id="teId">
            <div class="tm-modal-head">
                <div class="tm-modal-icon"><span class="material-symbols-outlined">edit</span></div>
                <div><h3>Edit Team</h3><p id="teProject"></p></div>
                <button type="button" class="tm-x" data-close-edit aria-label="Close"><span class="material-symbols-outlined">close</span></button>
            </div>
            <div class="tm-modal-body">
                <div class="tm-field">
                    <label class="tm-f-label" for="teName">Team Name *</label>
                    <input type="text" id="teName" name="team_name" class="tm-input" maxlength="100">
                    <div class="tm-err" id="teErrName"></div>
                </div>
                <div class="tm-field">
                    <label class="tm-f-label" for="teDeadline">Team Deadline *</label>
                    <input type="date" id="teDeadline" name="deadline" class="tm-input">
                    <div class="tm-err" id="teErrDeadline"></div>
                </div>
            </div>
            <div class="tm-modal-foot">
                <button type="button" class="tm-btn" data-close-edit>Cancel</button>
                <button type="submit" class="tm-btn solid"><span class="material-symbols-outlined">check</span>Save Changes</button>
            </div>
        </form>
    </div>

    <!-- ===================== DELAY REVIEW (Behind Schedule teams) ===================== -->
    <div class="tm-overlay" id="tmReviewModal">
        <div class="tm-modal tv-modal" role="dialog" aria-modal="true">
            <div class="tv-head">
                <div class="tv-head-main">
                    <div class="tv-kicker" style="color:#b91c1c;">Delay Review</div>
                    <div class="tv-title-row">
                        <h3 id="trName"></h3>
                        <span class="tm-pill behind" id="trPill">Behind Schedule</span>
                    </div>
                    <div class="tv-project" id="trProject"></div>
                </div>
                <button type="button" class="tm-x" data-close-review aria-label="Close"><span class="material-symbols-outlined">close</span></button>
            </div>
            <div class="tv-body" id="trBody"></div>
            <div class="tm-modal-foot" style="justify-content:space-between;">
                <button type="button" class="tm-btn" id="trOpenBoard"><span class="material-symbols-outlined">view_kanban</span>Open Kanban Board</button>
                <button type="button" class="tm-btn solid" data-close-review>Close</button>
            </div>
        </div>
    </div>

    <form method="post" action="teams.php" id="trExtendForm" hidden>
        <input type="hidden" name="action" value="extend_deadline">
        <input type="hidden" name="team_id">
        <input type="hidden" name="deadline">
        <input type="hidden" name="reason">
    </form>

    <!-- final report: Accept / Request changes (View popup, database teams) -->
    <form method="post" action="teams.php" id="tmReportForm" hidden>
        <input type="hidden" name="action" value="review_report">
        <input type="hidden" name="team_id">
        <input type="hidden" name="decision">
        <input type="hidden" name="note">
    </form>

    <!-- ===================== CREATE NEW TEAM ===================== -->
    <!-- role suggestions for the "Role" boxes (organization can still type any role) -->
    <datalist id="tmRoleList">
        <option value="Team Leader"><option value="Project Coordinator"><option value="Frontend Developer">
        <option value="Backend Developer"><option value="Full Stack Developer"><option value="Mobile Developer">
        <option value="UI/UX Designer"><option value="Database Designer"><option value="ML Engineer">
        <option value="Data Analyst"><option value="QA / Tester"><option value="DevOps Engineer">
        <option value="Security Analyst"><option value="Technical Writer">
    </datalist>

    <div class="tm-overlay" id="tmCreateModal">
        <form class="tm-modal tm-modal-wide" method="post" action="teams.php" id="tmCreateForm" novalidate>
            <input type="hidden" name="action" value="create_team">

            <div class="tm-modal-head">
                <div class="tm-modal-icon"><span class="material-symbols-outlined">group_add</span></div>
                <div>
                    <h3>Create New Team</h3>
                    <p>Build a team from the students whose proposals you accepted.</p>
                </div>
                <button type="button" class="tm-x" data-close aria-label="Close"><span class="material-symbols-outlined">close</span></button>
            </div>

            <div class="tm-modal-body">
                <?php if (empty($tmProjects)): ?>
                    <div class="tm-note warn">
                        <span class="material-symbols-outlined">info</span>
                        You don’t have any open or active projects yet. Post a project first, then create a team for it.
                    </div>
                <?php else: ?>

                <div class="tm-step"><span>1</span>Team &amp; Project</div>
                <div class="tm-grid-2">
                    <div class="tm-field">
                        <label class="tm-f-label" for="tmName">Team Name *</label>
                        <input type="text" id="tmName" name="team_name" class="tm-input" maxlength="100" placeholder="e.g. Code Crafters" autocomplete="off">
                        <div class="tm-err" data-err="name"></div>
                    </div>
                    <div class="tm-field">
                        <label class="tm-f-label" for="tmProject">Project *</label>
                        <select id="tmProject" name="project_id" class="tm-input">
                            <option value="">Select a project</option>
                            <?php foreach ($tmProjects as $pr): ?>
                                <option value="<?= (int)$pr['id'] ?>"><?= htmlspecialchars($pr['title']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="tm-err" data-err="project"></div>
                    </div>
                </div>
                <div class="tm-project-info" id="tmProjectInfo" hidden></div>

                <div class="tm-step"><span>2</span>Members &amp; Roles</div>
                <div class="tm-field">
                    <div class="tm-f-label-row">
                        <label class="tm-f-label">Accepted Students *</label>
                        <span class="tm-picked" id="tmPicked"></span>
                    </div>
                    <div class="tm-members" id="tmMembers">
                        <div class="tm-members-empty">Select a project to see the students you accepted for it.</div>
                    </div>
                    <div class="tm-hint">Tick a student to add them, then give them a role in the team.</div>
                    <div class="tm-err" data-err="members"></div>
                </div>

                <div class="tm-field">
                    <label class="tm-f-label" for="tmLeader">Team Leader *</label>
                    <select id="tmLeader" name="leader" class="tm-input" disabled>
                        <option value="">Choose members first</option>
                    </select>
                    <div class="tm-err" data-err="leader"></div>
                </div>

                <div class="tm-step"><span>3</span>Deadline</div>

                <div class="tm-field">
                    <label class="tm-f-label" for="tmDeadline">Team Deadline *</label>
                    <input type="date" id="tmDeadline" name="deadline" class="tm-input" min="<?= date('Y-m-d') ?>">
                    <div class="tm-hint" id="tmDeadlineHint">Must be today or later, and not after the day the project ends.</div>
                    <div class="tm-err" data-err="deadline"></div>
                </div>

                <?php endif; ?>
            </div>

            <div class="tm-modal-foot">
                <button type="button" class="tm-btn" data-close>Cancel</button>
                <?php if (!empty($tmProjects)): ?>
                <button type="submit" class="tm-btn solid"><span class="material-symbols-outlined">check</span>Create Team</button>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <?php if ($flash): ?>
    <div class="flash-toast flash-<?= htmlspecialchars($flash['type']) ?>" id="tmFlash">
        <div class="flash-icon-wrap">
            <span class="material-symbols-outlined"><?= $flash['type'] === 'success' ? 'check_circle' : 'error' ?></span>
        </div>
        <div class="flash-content">
            <div class="flash-title" style="font-weight:700; font-size:14px;"><?= htmlspecialchars($flash['title']) ?></div>
            <div class="flash-msg" style="font-size:13px; margin-top:2px;"><?= htmlspecialchars($flash['message']) ?></div>
        </div>
    </div>
    <?php endif; ?>
</main>

<script>
    // Values from PHP (database) used by Assets/JS/Organization/teams.js
    const ORG_DATA = {
        PROJECTS: <?= json_encode(array_map(fn($p) => [
                            'id' => (int)$p['id'], 'title' => $p['title'], 'members' => (int)$p['members'],
                            // the day the project ends (Application Deadline + Duration) = the latest team deadline
                            'deadline' => tmProjectEnd($p['deadline'] ?? '', $p['duration_weeks'] ?? 0),
                            'deadline_text' => tmProjectEnd($p['deadline'] ?? '', $p['duration_weeks'] ?? 0) !== ''
                                               ? date('M d, Y', strtotime(tmProjectEnd($p['deadline'] ?? '', $p['duration_weeks'] ?? 0))) : '',
                            'skills' => array_values(array_filter(array_map('trim', explode(',', $p['keywords'] ?? '')))),
                        ], $tmProjects), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?>,
        CANDIDATES: <?= json_encode($tmCandidates, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?>,
        TODAY: <?= json_encode(date('Y-m-d')) ?>,
        DETAIL_TEAMS: <?= json_encode(array_values(array_map(fn($t) => [
                        'name' => $t['name'], 'state' => $t['state'], 'status' => $t['status'], 'project' => $t['project'],
                        'skills' => $t['skills'], 'phase' => $t['phase'], 'percent' => (int)$t['percent'],
                        'id' => $t['id'] ?? null,
                        'deadline' => $t['deadline'], 'time' => $t['time'], 'tone' => $t['tone'],
                        'details' => $t['details'] ?? null,
                    ], $teams)), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?>,
        WORKSPACE_TEAMS: <?= json_encode(array_values(array_map(fn($t) => [
                    // id is set only for teams saved in the database -> their tasks + chat are saved on the server
                    'id' => $t['id'] ?? null, 'deadline_raw' => $t['deadline_raw'] ?? '', 'tasks' => $t['tasks'] ?? [],
                    'name' => $t['name'], 'project' => $t['project'], 'phase' => $t['phase'],
                    'members' => array_map(fn($m) => ['name' => $m['name'], 'role' => $m['role'], 'leader' => !empty($m['leader']),
                                                       'email' => strtolower($m['email'] ?? '')],
                                           $t['details']['members'] ?? []),
                  ], $teams)), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?>,
        ORG_NAME: <?= json_encode($user['username'] ?? 'Organization', JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) ?>,
        MENU_TEAMS: <?= json_encode(array_values(array_map(fn($t) => [
                    'id' => $t['id'] ?? null, 'project_id' => $t['project_id'] ?? null,
                    'name' => $t['name'], 'project' => $t['project'], 'state' => $t['state'],
                    'leader' => $t['leader'], 'leader_email' => $t['leader_email'] ?? null,
                    'member_emails' => $t['member_emails'] ?? [],
                    'deadline_raw' => $t['deadline_raw'] ?? '', 'deadline' => $t['deadline'],
                    'skills' => $t['skills'], 'project_deadline_raw' => $t['project_deadline_raw'] ?? '',
                    'members' => array_map(fn($m) => ['name' => $m['name'], 'email' => strtolower($m['email'] ?? ''), 'role' => $m['role'], 'leader' => !empty($m['leader'])],
                                           $t['details']['members'] ?? []),
                  ], $teams)), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?>,
        PROJECT_MAX: <?= json_encode(array_column(array_map(fn($p) => ['id' => (int)$p['id'], 'm' => (int)$p['members']], $tmProjects), 'm', 'id')) ?>,
        REVIEW_TEAMS: <?= json_encode(array_values(array_map(fn($t) => [
                    'id' => $t['id'] ?? null, 'name' => $t['name'], 'project' => $t['project'],
                    'percent' => (int)$t['percent'], 'time' => $t['time'], 'deadline' => $t['deadline'],
                    'created' => $t['details']['created'] ?? '', 'team_deadline' => $t['details']['team_deadline'] ?? '',
                    'members' => array_map(fn($m) => ['name' => $m['name'], 'role' => $m['role'], 'progress' => (int)$m['progress']],
                                           $t['details']['members'] ?? []),
                  ], $teams)), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?>,
    };
</script>
<script src="../../Assets/JS/Organization/teams.js?v=<?= filemtime(__DIR__ . '/../../Assets/JS/Organization/teams.js') ?>"></script>


<?php include "../../Includes/dash_footer.php"; ?>