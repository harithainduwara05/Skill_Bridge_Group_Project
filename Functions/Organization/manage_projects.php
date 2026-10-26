<?php

include "../../Config/db.php";
include "../../Session/Session.php";

require_role('organization');

// Backend (PHP + SQL) of this page
require_once __DIR__ . "/../../Backend/Organization/manage_projects.php";

// ?v=<file time> makes the browser load the NEW css every time the file changes (no old cached colours)
$extra_css = '<link rel="stylesheet" href="../../Assets/CSS/Organization/manage_projects.css?v='
           . filemtime(__DIR__ . '/../../Assets/CSS/Organization/manage_projects.css') . '">';
include "../../Includes/org_sidebar.php";
include "../../Includes/dash_header.php";
?>

<main class="content">

    <div class="dashboard-header">
        <div>
            <h1>Project Management</h1>
            <p>Curate and monitor your posted projects and student collaborations.</p>
        </div>
    </div>

    <!-- ===================== SUMMARY STAT CARDS ===================== -->
    <div class="stats-grid">

        <div class="stat-card">
            <div class="stat-card-top">
                <div class="stat-icon blue">
                    <span class="material-symbols-outlined">rocket_launch</span>
                </div>
            </div>
            <div class="stat-info">
                <div class="stat-label">Total Projects Posted</div>
                <div class="stat-value"><?= (int)$totalProjectsAll ?></div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-card-top">
                <div class="stat-icon orange">
                    <span class="material-symbols-outlined">hourglass_top</span>
                </div>
            </div>
            <div class="stat-info">
                <div class="stat-label">Reviewing Projects</div>
                <div class="stat-value"><?= (int)$reviewingProjects ?></div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-card-top">
                <div class="stat-icon green">
                    <span class="material-symbols-outlined">task_alt</span>
                </div>
            </div>
            <div class="stat-info">
                <div class="stat-label">Active Projects</div>
                <div class="stat-value"><?= (int)$activeProjects ?></div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-card-top">
                <div class="stat-icon slate">
                    <span class="material-symbols-outlined">sentiment_neutral</span>
                </div>
            </div>
            <div class="stat-info">
                <div class="stat-label">Assigned Teams</div>
                <div class="stat-value"><?= $assignedTeams ?></div>
            </div>
        </div>

    </div>

    <!-- ===================== TOOLBAR: FILTERS + VIEW ===================== -->
    <div class="page-toolbar">

        <form class="toolbar-filters" method="get">
            <?php if ($searchQuery !== ''): ?>
                <input type="hidden" name="search" value="<?= htmlspecialchars($searchQuery) ?>">
            <?php endif; ?>
            <span class="filter-label">Filters:</span>

            <select name="status" class="select-filter" onchange="this.form.submit()">
                <option value="all" <?= (($_GET['status'] ?? 'all') == 'all') ? 'selected' : '' ?>>Status: All</option>
                <option value="draft" <?= (($_GET['status'] ?? '') == 'draft') ? 'selected' : '' ?>>Draft</option>
                <option value="reviewing" <?= (($_GET['status'] ?? '') == 'reviewing') ? 'selected' : '' ?>>Reviewing</option>
                <option value="published" <?= (($_GET['status'] ?? '') == 'published') ? 'selected' : '' ?>>Published</option>
                <option value="inprogress" <?= (($_GET['status'] ?? '') == 'inprogress') ? 'selected' : '' ?>>Active</option>
                <option value="hold" <?= (($_GET['status'] ?? '') == 'hold') ? 'selected' : '' ?>>Hold</option>
                <option value="completed" <?= (($_GET['status'] ?? '') == 'completed') ? 'selected' : '' ?>>Completed</option>
                <option value="rejected" <?= (($_GET['status'] ?? '') == 'rejected') ? 'selected' : '' ?>>Rejected</option>
            </select>

            <select name="time" class="select-filter" onchange="this.form.submit()">
                <option value="all" <?= $timeFilter === 'all' ? 'selected' : '' ?>>Time: All</option>
                <option value="daily" <?= $timeFilter === 'daily' ? 'selected' : '' ?>>Daily (Today)</option>
                <option value="weekly" <?= $timeFilter === 'weekly' ? 'selected' : '' ?>>Weekly (Last 7 days)</option>
                <option value="monthly" <?= $timeFilter === 'monthly' ? 'selected' : '' ?>>Monthly (Last 30 days)</option>
            </select>
        </form>

        <div class="toolbar-meta">
            <span class="results-count">Showing <?= $shownCount ?> of <?= $filteredTotal ?> projects<?= $totalPages > 1 ? " (Page $page of $totalPages)" : '' ?></span>
            <?php if ($searchQuery !== ''): ?>
                <span class="results-count" style="display:inline-flex; align-items:center; gap:6px;">
                    Search: <strong>“<?= htmlspecialchars($searchQuery) ?>”</strong>
                    <a href="manage_projects.php" title="Clear search" style="color:#2563eb; text-decoration:none; font-weight:600;">Clear</a>
                </span>
            <?php endif; ?>

            <div class="view-toggle-group" role="group" aria-label="Project view">
                <button type="button" class="active" data-view="list" title="List view" aria-label="List view" aria-pressed="true">
                    <span class="material-symbols-outlined">view_list</span>
                </button>
                <button type="button" data-view="grid" title="Grid view" aria-label="Grid view" aria-pressed="false">
                    <span class="material-symbols-outlined">grid_view</span>
                </button>
            </div>
        </div>

    </div>

    <!-- ===================== PROJECTS TABLE ===================== -->
    <div class="full-width-section project-list-view" id="projectListView">
        <div class="card">
            <div class="card-body" style="padding:0 0 4px;">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Project Title</th>
                            <th>Category</th>
                            <th>Applicants</th>
                            <th>Assigned Team</th>
                            <th>Deadline</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($projects)): ?>
                        <tr>
                            <td colspan="7" style="text-align:center; padding:36px 16px; color:#6b7280;">
                                No projects found<?= $searchQuery !== '' ? ' for “' . htmlspecialchars($searchQuery) . '”' : '' ?>.
                            </td>
                        </tr>
                        <?php endif; ?>
                        <?php foreach ($projects as $p): ?>
                        <tr>
                            <td class="project-title-cell">
                                <div class="project-title"><?= htmlspecialchars($p['title']) ?></div>
                                <div class="project-meta">Posted <?= htmlspecialchars(date('M d, Y', strtotime($p['posted_at']))) ?></div>
                            </td>
                            <td>
                                <span class="category-tag"><?= htmlspecialchars($p['category']) ?></span>
                            </td>
                            <td>
                                <?= shownApplicants($p) ?>
                            </td>
                            <td>
                                <?php $assignedCount = assignedCount($p); ?>
                                <?php if ($assignedCount > 0): ?>
                                    <div class="team-cell" style="color:#16a34a; font-size:12px; font-style:italic;">
                                        <span class="team-dot" style="background:#16a34a;"></span>
                                        Assigned (<?= $assignedCount ?>/<?= (int)($p['members'] ?? 0) ?>)
                                    </div>
                                <?php else: ?>
                                    <div class="team-cell not-assigned">
                                        <span class="team-dot"></span>
                                        Not Assigned
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars(formatDeadline($p['deadline'])) ?></td>
                            <td>
                                <span class="badge-status <?= htmlspecialchars($p['status']) ?>"><?= htmlspecialchars(statusLabel($p['status'])) ?></span>
                                <?php if ($p['status'] === 'hold' && !empty($p['hold_response'])): /* the organization already answered the Admin */ ?>
                                    <div class="hold-requested"><span class="material-symbols-outlined">hourglass_top</span> Activation requested</div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="row-actions">
                                    <button type="button" class="action-btn" title="View" onclick="openProjectView('<?= htmlspecialchars((string)$p['id']) ?>')">
                                        <span class="material-symbols-outlined" style="font-size:18px;">visibility</span>
                                    </button>
                                    <?php if (in_array($p['status'], ['hold', 'rejected'], true)): ?>
                                    <button type="button" class="action-btn" title="Edit"
                                        onclick="openEditBlocked(<?= htmlspecialchars(json_encode($p['title']), ENT_QUOTES) ?>, '<?= $p['status'] ?>', '<?= (int)$p['id'] ?>')">
                                        <span class="material-symbols-outlined" style="font-size:18px;">edit</span>
                                    </button>
                                    <?php else: ?>
                                    <a class="action-btn" title="Edit" href="edit_project.php?id=<?= (int)$p['id'] ?>">
                                        <span class="material-symbols-outlined" style="font-size:18px;">edit</span>
                                    </a>
                                    <?php endif; ?>
                                    <button type="button" class="action-btn danger" title="Delete"
                                        onclick="openDeleteModal(<?= (int)$p['id'] ?>, <?= htmlspecialchars(json_encode($p['title']), ENT_QUOTES) ?>, <?= htmlspecialchars(json_encode(deleteBlockReason($p)), ENT_QUOTES) ?>)">
                                        <span class="material-symbols-outlined" style="font-size:18px;">delete</span>
                                    </button>
                                    <?php if ($p['status'] === 'hold'): ?>
                                    <button type="button" class="action-btn hold-btn" title="On hold – see reason &amp; fix" onclick="openHoldModal('<?= (int)$p['id'] ?>')">
                                        <span class="material-symbols-outlined" style="font-size:18px;">report</span>
                                    </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <!-- ===================== PAGINATION ===================== -->
                <?php if ($totalPages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="<?= buildPageUrl($page - 1, $statusFilter, $timeFilter) ?>" class="pagination-link">&lsaquo; Previous</a>
                    <?php else: ?>
                        <span class="pagination-link" style="opacity:.4; pointer-events:none;">&lsaquo; Previous</span>
                    <?php endif; ?>

                    <div class="page-numbers">
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <a href="<?= buildPageUrl($i, $statusFilter, $timeFilter) ?>" class="page-btn <?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
                        <?php endfor; ?>
                    </div>

                    <?php if ($page < $totalPages): ?>
                        <a href="<?= buildPageUrl($page + 1, $statusFilter, $timeFilter) ?>" class="pagination-link">Next &rsaquo;</a>
                    <?php else: ?>
                        <span class="pagination-link" style="opacity:.4; pointer-events:none;">Next &rsaquo;</span>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- ===================== PROJECTS GRID VIEW ===================== -->
    <div class="projects-grid-view" id="projectGridView" hidden>
        <div class="projects-management-grid">
            <?php if (empty($projects)): ?>
                <div style="grid-column:1 / -1; text-align:center; padding:36px 16px; color:#6b7280;">
                    No projects found<?= $searchQuery !== '' ? ' for “' . htmlspecialchars($searchQuery) . '”' : '' ?>.
                </div>
            <?php endif; ?>
            <?php foreach ($projects as $p): ?>
                <?php
                    $gridApplicants = shownApplicants($p);
                    $gridAssigned   = assignedCount($p);
                ?>
                <article class="project-management-card">
                    <div class="project-management-card-top">
                        <span class="category-tag"><?= htmlspecialchars($p['category']) ?></span>
                        <span class="badge-status <?= htmlspecialchars($p['status']) ?>"><?= htmlspecialchars(statusLabel($p['status'])) ?></span>
                        <?php if ($p['status'] === 'hold' && !empty($p['hold_response'])): /* the organization already answered the Admin */ ?>
                            <span class="hold-requested"><span class="material-symbols-outlined">hourglass_top</span> Activation requested</span>
                        <?php endif; ?>
                    </div>

                    <h3><?= htmlspecialchars($p['title']) ?></h3>
                    <div class="project-management-date">Posted <?= htmlspecialchars(date('M d, Y', strtotime($p['posted_at']))) ?></div>

                    <div class="project-management-details">
                        <div>
                            <span class="material-symbols-outlined">groups</span>
                            <span><strong><?= $gridApplicants ?></strong> Applicants</span>
                        </div>
                        <div style="<?= $gridAssigned > 0 ? 'color:#16a34a;' : 'color:#ea7a1a;' ?>">
                            <span class="material-symbols-outlined">group</span>
                            <span><?= $gridAssigned > 0 ? '<strong>Assigned (' . $gridAssigned . '/' . (int)($p['members'] ?? 0) . ')</strong>' : '<strong>Not Assigned</strong>' ?></span>
                        </div>
                        <div>
                            <span class="material-symbols-outlined">event</span>
                            <span>Deadline: <strong><?= htmlspecialchars(formatDeadline($p['deadline'])) ?></strong></span>
                        </div>
                    </div>

                    <div class="project-management-actions">
                        <button type="button" class="action-btn" title="View" onclick="openProjectView('<?= htmlspecialchars((string)$p['id']) ?>')">
                            <span class="material-symbols-outlined" style="font-size:18px;">visibility</span>
                        </button>
                        <?php if (in_array($p['status'], ['hold', 'rejected'], true)): ?>
                        <button type="button" class="action-btn" title="Edit"
                            onclick="openEditBlocked(<?= htmlspecialchars(json_encode($p['title']), ENT_QUOTES) ?>, '<?= $p['status'] ?>', '<?= (int)$p['id'] ?>')">
                            <span class="material-symbols-outlined" style="font-size:18px;">edit</span>
                        </button>
                        <?php else: ?>
                        <a class="action-btn" title="Edit" href="edit_project.php?id=<?= (int)$p['id'] ?>">
                            <span class="material-symbols-outlined" style="font-size:18px;">edit</span>
                        </a>
                        <?php endif; ?>
                        <button type="button" class="action-btn danger" title="Delete"
                            onclick="openDeleteModal(<?= (int)$p['id'] ?>, <?= htmlspecialchars(json_encode($p['title']), ENT_QUOTES) ?>, <?= htmlspecialchars(json_encode(deleteBlockReason($p)), ENT_QUOTES) ?>)">
                            <span class="material-symbols-outlined" style="font-size:18px;">delete</span>
                        </button>
                        <?php if ($p['status'] === 'hold'): ?>
                        <button type="button" class="action-btn hold-btn" title="On hold – see reason &amp; fix" onclick="openHoldModal('<?= (int)$p['id'] ?>')">
                            <span class="material-symbols-outlined" style="font-size:18px;">report</span>
                        </button>
                        <?php endif; ?>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>

        <!-- Same pagination is available in grid view. -->
        <?php if ($totalPages > 1): ?>
        <div class="pagination grid-pagination">
            <?php if ($page > 1): ?>
                <a href="<?= buildPageUrl($page - 1, $statusFilter, $timeFilter) ?>" class="pagination-link">&lsaquo; Previous</a>
            <?php else: ?>
                <span class="pagination-link" style="opacity:.4; pointer-events:none;">&lsaquo; Previous</span>
            <?php endif; ?>

            <div class="page-numbers">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a href="<?= buildPageUrl($i, $statusFilter, $timeFilter) ?>" class="page-btn <?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
                <?php endfor; ?>
            </div>

            <?php if ($page < $totalPages): ?>
                <a href="<?= buildPageUrl($page + 1, $statusFilter, $timeFilter) ?>" class="pagination-link">Next &rsaquo;</a>
            <?php else: ?>
                <span class="pagination-link" style="opacity:.4; pointer-events:none;">Next &rsaquo;</span>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- ===================== DELETE CONFIRMATION MODAL ===================== -->
    <div class="confirm-modal-overlay" id="deleteConfirmModal">
        <div class="confirm-modal">
            <div class="confirm-modal-icon">
                <span class="material-symbols-outlined">warning</span>
            </div>
            <h3>Delete this project?</h3>
            <p>You're about to delete "<strong id="deleteProjectTitle"></strong>". This action cannot be undone.</p>
            <div class="confirm-modal-actions">
                <button type="button" class="confirm-modal-cancel" onclick="closeDeleteModal()">Cancel</button>
                <button type="button" class="confirm-modal-delete" onclick="confirmDelete()">Delete</button>
            </div>
        </div>
    </div>

    <div class="confirm-modal-overlay" id="deleteBlockedModal">
        <div class="confirm-modal">
            <div class="confirm-modal-icon" style="background:#fef3c7; color:#d97706;">
                <span class="material-symbols-outlined">block</span>
            </div>
            <h3 id="blockedHeading">You can’t delete this project</h3>
            <p style="margin-bottom:10px;"><strong id="blockedProjectTitle"></strong></p>
            <p id="blockedReason" style="margin-bottom:12px;"></p>
            <div class="delete-rule-box">
                <span class="material-symbols-outlined">info</span>
                <span id="blockedRule">Only <b>Rejected</b> projects, or <b>Reviewing</b> projects with <b>0 applicants</b>, can be deleted.</span>
            </div>
            <div class="confirm-modal-actions">
                <button type="button" class="hm-btn primary" id="blockedHoldBtn" hidden>
                    <span class="material-symbols-outlined">report</span> Open hold details
                </button>
                <button type="button" class="confirm-modal-cancel" onclick="closeDeleteBlocked()">OK, got it</button>
            </div>
        </div>
    </div>

    <!-- ===================== PROJECT DETAILS MODAL (View) ===================== -->
    <?php
        $projectViewData = [];
        foreach ($projects as $p) {
            $vApplicants = shownApplicants($p);
            $vAssigned   = assignedCount($p);
            $projectViewData[(string)$p['id']] = [
                'id'         => $p['id'],
                'title'      => $p['title'] ?? '',
                'category'   => $p['category'] ?? '',
                'status'     => $p['status'] ?? '',
                'posted'     => date('M d, Y', strtotime($p['posted_at'])),
                'deadline'   => formatDeadline($p['deadline'] ?? ''),
                'applicants' => $vApplicants,
                'assigned'   => $vAssigned,
                'required'   => (int)($p['members'] ?? 0),
                'visibility' => $p['visibility'] ?? '',
                'duration'   => $p['duration'] ?? '',
                'members'    => $p['members'] ?? '',
                'year'       => $p['preferred_year'] ?? '',
                'difficulty' => $p['difficulty'] ?? '',
                'keywords'   => $p['keywords'] ?? '',
                'description'=> $p['description'] ?? '',
                'objectives' => $p['learning_objectives'] ?? '',
                'outcomes'   => $p['expected_outcomes'] ?? '',
            ];
        }
    ?>
    <div class="confirm-modal-overlay" id="projectViewModal">
        <div class="pv-modal" role="dialog" aria-modal="true" aria-labelledby="pvTitle">
            <div class="pv-head">
                <div>
                    <div class="pv-tags"><span class="category-tag" id="pvCategory"></span><span class="badge-status" id="pvStatus"></span></div>
                    <h3 id="pvTitle"></h3>
                </div>
                <button type="button" class="pv-close" onclick="closeProjectView()" aria-label="Close">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <div class="pv-body">
                <div class="pv-info" id="pvInfo"></div>
                <div id="pvSections"></div>
            </div>
            <div class="pv-foot">
                <button type="button" class="pv-btn-close" onclick="closeProjectView()">Close</button>
                <a class="pv-btn-edit" id="pvEdit" href="#"><span class="material-symbols-outlined" style="font-size:18px;">edit</span>Edit Project</a>
            </div>
        </div>
    </div>

    <!-- ===================== ON HOLD: REASON + FIX MODAL ===================== -->
    <?php
        $holdData = [];
        foreach ($allProjects as $p) {
            if (($p['status'] ?? '') !== 'hold') continue;
            preg_match('/\d+/', $p['duration'] ?? '', $hm);
            $dlTs = !empty($p['deadline']) ? strtotime($p['deadline']) : false;
            $holdData[(string)$p['id']] = [
                'id'          => (int)$p['id'],
                'title'       => $p['title'] ?? '',
                'category'    => $p['category'] ?? '',
                'keywords'    => $p['keywords'] ?? '',
                'description' => $p['description'] ?? '',
                'learning_objectives' => $p['learning_objectives'] ?? '',
                'expected_outcomes'   => $p['expected_outcomes'] ?? '',
                'deadline'    => $dlTs ? date('Y-m-d', $dlTs) : '',
                'members'     => (int)($p['members'] ?? 1),
                'in_team'     => (int)($teamCounts[(int)$p['id']] ?? 0),
                'duration'    => $hm[0] ?? '',
                'reason'      => $p['hold_reason'] ?? '',
                'held_at'     => !empty($p['held_at']) ? date('M d, Y · h:i A', strtotime($p['held_at'])) : '',
                'requested_at'=> !empty($p['hold_response']) ? 'sent' : '',   // the organization already answered the Admin
                'response'    => $p['hold_response'] ?? '',
            ];
        }
        $holdCategories = orgCategories($conn);   // from project_categories (same list as Post Project)
    ?>
    <div class="confirm-modal-overlay" id="holdModal">
        <!-- novalidate: the steps are checked by holdValidate() below (messages under the fields), and again on the server -->
        <form class="hm-modal" method="post" action="manage_projects.php" role="dialog" aria-modal="true" aria-labelledby="hmTitle" novalidate>
            <input type="hidden" name="action" value="resolve_hold">
            <input type="hidden" name="project_id" id="hmProjectId">

            <div class="hm-head">
                <div class="hm-head-icon"><span class="material-symbols-outlined">pause_circle</span></div>
                <div class="hm-head-text">
                    <div class="hm-kicker">Project On Hold</div>
                    <h3 id="hmTitle"></h3>
                </div>
                <button type="button" class="pv-close" onclick="closeHoldModal()" aria-label="Close">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>

            <div class="hm-steps">
                <div class="hm-step active" data-step="1"><span>1</span> Admin's reason</div>
                <div class="hm-step-line"></div>
                <div class="hm-step" data-step="2"><span>2</span> Fix project</div>
                <div class="hm-step-line"></div>
                <div class="hm-step" data-step="3"><span>3</span> Request activation</div>
            </div>

            <div class="hm-body">
                <!-- STEP 1: reason from the Admin -->
                <section class="hm-panel" data-panel="1">
                    <div class="hm-reason">
                        <div class="hm-reason-top">
                            <span class="material-symbols-outlined">admin_panel_settings</span>
                            <div>
                                <div class="hm-reason-by">SkillBridge Admin put this project on hold</div>
                                <div class="hm-reason-date" id="hmHeldAt"></div>
                            </div>
                        </div>
                        <p class="hm-reason-text" id="hmReason"></p>
                    </div>

                    <div class="hm-pending" id="hmPending" hidden>
                        <span class="material-symbols-outlined">hourglass_top</span>
                        <div>
                            <div class="hm-pending-title">Activation request sent <span id="hmPendingDate"></span></div>
                            <div class="hm-pending-msg">Waiting for the Admin to activate this project. You can update it and send the request again if needed.</div>
                        </div>
                    </div>

                    <p class="hm-hint hm-next"><span class="material-symbols-outlined">info</span> Read the reason, then click <b>Next</b> to fix the project.</p>
                </section>

                <!-- STEP 2: fix the fields -->
                <section class="hm-panel" data-panel="2" hidden>
                    <details class="hm-reason-mini" id="hmReasonToggle">
                        <summary><span class="material-symbols-outlined">admin_panel_settings</span> Admin’s reason <span class="hm-reason-mini-hint">(click to read again)</span></summary>
                        <p id="hmReasonMini"></p>
                    </details>

                    <p class="hm-hint"><span class="material-symbols-outlined">info</span> <span id="hmHintText">The current details are filled in. Change only what the Admin asked you to fix.</span></p>
                    <p class="hm-req-note">Fields marked <span class="req">*</span> are required, because the project will be published again.</p>

                    <div class="hm-grid-2">
                        <div class="hm-field" data-field="title">
                            <label class="form-label">Project Title <span class="req">*</span></label>
                            <input type="text" name="title" class="form-input" maxlength="100">
                            <div class="field-error" data-for="title"></div>
                        </div>
                        <div class="hm-field" data-field="category">
                            <label class="form-label">Category <span class="req">*</span></label>
                            <select name="category" class="form-select">
                                <option value="">Select category</option>
                                <?php foreach ($holdCategories as $cat): ?>
                                    <option value="<?= htmlspecialchars($cat) ?>"><?= htmlspecialchars($cat) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <div class="field-error" data-for="category"></div>
                        </div>
                    </div>

                    <div class="hm-field" data-field="keywords">
                        <label class="form-label">Required Skills / Keywords <span class="req">*</span></label>
                        <div class="chip-input-box hm-chip-box" id="hmChipBox">
                            <input type="text" id="hmChipInput" class="hm-main-input" placeholder="Type a skill and press Enter (e.g. Laravel)">
                        </div>
                        <input type="hidden" name="keywords" id="hmKeywords">
                        <div class="hm-field-note">Up to 10 skills. Press <b>Enter</b> or <b>,</b> after each one.</div>
                        <div class="field-error" data-for="keywords"></div>
                    </div>

                    <div class="hm-field" data-field="description">
                        <label class="form-label">Description <span class="req">*</span></label>
                        <textarea name="description" class="form-textarea" rows="4" maxlength="3000"></textarea>
                        <div class="hm-field-note">At least 50 characters.</div>
                        <div class="field-error" data-for="description"></div>
                    </div>

                    <div class="hm-field" data-field="learning_objectives">
                        <label class="form-label">Learning Objectives</label>
                        <textarea name="learning_objectives" class="form-textarea" rows="3" maxlength="1500"></textarea>
                        <div class="field-error" data-for="learning_objectives"></div>
                    </div>

                    <div class="hm-field" data-field="expected_outcomes">
                        <label class="form-label">Expected Outcomes</label>
                        <textarea name="expected_outcomes" class="form-textarea" rows="3" maxlength="1500"></textarea>
                        <div class="field-error" data-for="expected_outcomes"></div>
                    </div>

                    <div class="hm-grid-3">
                        <div class="hm-field" data-field="deadline">
                            <label class="form-label">Application Deadline <span class="req">*</span></label>
                            <input type="date" name="deadline" class="form-input">
                            <div class="field-error" data-for="deadline"></div>
                        </div>
                        <div class="hm-field" data-field="members">
                            <label class="form-label">Students Required <span class="req">*</span></label>
                            <input type="number" min="1" max="10" name="members" class="form-input">
                            <div class="field-error" data-for="members"></div>
                        </div>
                        <div class="hm-field" data-field="duration">
                            <label class="form-label">Duration (Weeks) <span class="req">*</span></label>
                            <input type="number" min="1" max="52" name="duration_weeks" class="form-input">
                            <div class="field-error" data-for="duration_weeks"></div>
                        </div>
                    </div>
                </section>

                <!-- STEP 3: ask the Admin to activate -->
                <section class="hm-panel" data-panel="3" hidden>
                    <div class="hm-flow">
                        <div class="hm-flow-item">
                            <span class="badge-status hold">Hold</span>
                            <small>Now</small>
                        </div>
                        <span class="material-symbols-outlined hm-flow-arrow">arrow_forward</span>
                        <div class="hm-flow-item">
                            <span class="hm-flow-admin"><span class="material-symbols-outlined">admin_panel_settings</span> Admin reviews</span>
                            <small>After your request</small>
                        </div>
                        <span class="material-symbols-outlined hm-flow-arrow">arrow_forward</span>
                        <div class="hm-flow-item">
                            <span class="badge-status published">Published</span>
                            <small>Set by the Admin</small>
                        </div>
                    </div>

                    <div class="hm-field">
                        <label class="form-label">Message to Admin <span class="req">*</span></label>
                        <textarea name="hold_response" class="form-textarea" rows="4" maxlength="500"
                                  placeholder="Explain what you fixed, e.g. 'Added a detailed description, moved the deadline to Nov 15 and reduced the team to 4 students.'"></textarea>
                        <div class="hm-field-note">10 to 500 characters.</div>
                        <div class="field-error" data-for="hold_response"></div>
                    </div>

                    <!-- shown when the organization tries to send without changing anything -->
                    <div class="hm-nochange" id="hmNoChange" hidden>
                        <span class="material-symbols-outlined">warning</span>
                        <span>You did not change any project details. Go <b>Back</b> to fix the project, or click <b>Send Anyway</b> if you only want to reply to the Admin.</span>
                    </div>

                    <div class="hm-notify-note">
                        <span class="material-symbols-outlined">info</span>
                        <span>Only the Admin can activate this project. It stays <b>On Hold</b> until the Admin approves it. Then it becomes <b>Published</b> and students can see it again.</span>
                    </div>
                </section>
            </div>

            <div class="hm-foot">
                <button type="button" class="hm-btn ghost" id="hmBack" onclick="holdStep(-1)">
                    <span class="material-symbols-outlined">arrow_back</span> Back
                </button>
                <div class="hm-foot-right">
                    <button type="button" class="hm-btn ghost" onclick="closeHoldModal()">Cancel</button>
                    <button type="button" class="hm-btn primary" id="hmNext" onclick="holdStep(1)">
                        Next <span class="material-symbols-outlined">arrow_forward</span>
                    </button>
                    <button type="submit" class="hm-btn primary" id="hmSubmit" hidden>
                        <span class="material-symbols-outlined">send</span> <span id="hmSubmitText">Send Activation Request</span>
                    </button>
                </div>
            </div>
        </form>
    </div>

    <?php if (!empty($flash)): ?>
    <div class="flash-toast flash-<?= htmlspecialchars($flash['type']) ?>" id="holdFlash">
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
    // Values from PHP (database) used by Assets/JS/Organization/manage_projects.js
    const ORG_DATA = {
        PROJECT_VIEW_DATA: <?= json_encode($projectViewData, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) ?>,
        HOLD_DATA: <?= json_encode($holdData, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) ?>,
    };
</script>
<script src="../../Assets/JS/Organization/manage_projects.js?v=<?= filemtime(__DIR__ . '/../../Assets/JS/Organization/manage_projects.js') ?>"></script>


<?php include "../../Includes/dash_footer.php"; ?>