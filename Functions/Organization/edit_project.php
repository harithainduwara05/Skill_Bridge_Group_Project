<?php

include "../../Config/db.php";
include "../../Session/Session.php";

require_role('organization');

// Backend (PHP + SQL) of this page
require_once __DIR__ . "/../../Backend/Organization/edit_project.php";

$extra_css = '<link rel="stylesheet" href="../../Assets/CSS/Organization/project_form.css?v=' . filemtime(__DIR__ . '/../../Assets/CSS/Organization/project_form.css') . '">';
include "../../Includes/org_sidebar.php";
include "../../Includes/dash_header.php";

?>

<main class="content">
    <div class="dashboard-header">
        <div>
            <h1>Edit Project</h1>
            <p>Update the details of your posted project.</p>
        </div>
    </div>

    <?php if (!empty($flash)): ?>
    <div class="flash-toast flash-<?= htmlspecialchars($flash['type']) ?>" style="margin:0 28px 16px;">
        <?= htmlspecialchars($flash['message']) ?>
    </div>
    <?php endif; ?>

    <!-- novalidate: the checks are done by project-validation.js (messages under the fields)
         and again on the server, instead of the browser's own popups -->
    <form id="editProjectForm" action="edit_project.php?id=<?= (int)$project['id'] ?>" method="POST" novalidate>

        <div class="post-form-card">

            <div class="required-note">
                Fields marked <span class="req">*</span> are required.
                <?php if ($project['status'] === 'draft'): ?>While the project stays a <b>Draft</b>, only the title is needed.<?php endif; ?>
            </div>

            <!-- ===================== PROJECT BASICS ===================== -->
            <div class="post-section">
                <div class="post-section-title">
                    <span class="material-symbols-outlined">info</span>
                    Project Basics
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Project Title <span class="req">*</span></label>
                        <input type="text" name="title" class="form-input" maxlength="100" value="<?= htmlspecialchars($project['title']) ?>">
                        <?= fieldError($errors, 'title') ?>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Project Category <span class="req">*</span></label>
                        <select name="category" class="form-select">
                            <option value="">Select a category</option>
                            <?php
                            // categories come from the project_categories table ($categories in the backend)
                            foreach ($categories as $cat):
                            ?>
                                <option value="<?= htmlspecialchars($cat) ?>" <?= ($project['category'] === $cat) ? 'selected' : '' ?>><?= htmlspecialchars($cat) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <?= fieldError($errors, 'category') ?>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Required Skills <span class="req">*</span></label>
                    <div class="chip-input-box" id="chipBox">
                        <input type="text" id="chipInput" maxlength="30" placeholder="Type and press enter...">
                    </div>
                    <input type="hidden" name="keywords" id="keywordsHidden" value="<?= htmlspecialchars($project['keywords'] ?? '') ?>">
                    <div class="form-hint">Press Enter after each skill (e.g. React, Python, Figma). Up to 10 skills.</div>
                    <?= fieldError($errors, 'keywords') ?>
                </div>
            </div>

            <!-- ===================== PROJECT DETAILS ===================== -->
            <div class="post-section">
                <div class="post-section-title">
                    <span class="material-symbols-outlined">description</span>
                    Project Details
                </div>

                <div class="form-group">
                    <label class="form-label">Project Description <span class="req">*</span></label>
                    <textarea name="description" class="form-textarea" maxlength="3000"><?= htmlspecialchars($project['description'] ?? '') ?></textarea>
                    <div class="form-hint">At least 50 characters, so students understand the project.</div>
                    <?= fieldError($errors, 'description') ?>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Learning Objectives</label>
                        <textarea name="learning_objectives" class="form-textarea" maxlength="1500"><?= htmlspecialchars($project['learning_objectives'] ?? '') ?></textarea>
                        <?= fieldError($errors, 'learning_objectives') ?>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Expected Outcomes</label>
                        <textarea name="expected_outcomes" class="form-textarea" maxlength="1500"><?= htmlspecialchars($project['expected_outcomes'] ?? '') ?></textarea>
                        <?= fieldError($errors, 'expected_outcomes') ?>
                    </div>
                </div>
            </div>

            <!-- ===================== LOGISTICS & REQUIREMENTS ===================== -->
            <div class="post-section">
                <div class="post-section-title">
                    <span class="material-symbols-outlined">settings</span>
                    Logistics &amp; Requirements
                </div>

                <div class="form-group">
                    <label class="form-label">Difficulty Level <span class="req">*</span></label>
                    <div class="toggle-group" id="difficultyGroup">
                        <?php foreach (['Beginner', 'Intermediate', 'Advanced'] as $level): ?>
                            <div class="toggle-btn <?= ($project['difficulty'] === $level) ? 'selected' : '' ?>" data-value="<?= $level ?>"><?= $level ?></div>
                        <?php endforeach; ?>
                    </div>
                    <input type="hidden" name="difficulty" id="difficultyHidden" value="<?= htmlspecialchars($project['difficulty'] ?? 'Intermediate') ?>">
                    <?= fieldError($errors, 'difficulty') ?>
                </div>

                <div class="form-row-3">
                    <div class="form-group">
                        <label class="form-label">Project Duration (weeks) <span class="req">*</span></label>
                        <input type="number" min="1" max="52" step="1" name="duration_weeks" class="form-input" value="<?= htmlspecialchars($durationWeeksValue) ?>">
                        <div class="form-hint">1 to 52 weeks.</div>
                        <?= fieldError($errors, 'duration_weeks') ?>
                    </div>
                    <div class="form-group">
                        <label class="form-label">No. of Students Required <span class="req">*</span></label>
                        <!-- data-min: can't be less than the students already in the team -->
                        <input type="number" min="<?= max(1, $assignedCount) ?>" max="10" step="1" name="students_required" class="form-input"
                               value="<?= htmlspecialchars((string)($project['members'] ?? '')) ?>" data-min="<?= (int)$assignedCount ?>">
                        <?= fieldError($errors, 'students_required') ?>
                        <?php if ($assignedCount > (int)($project['members'] ?? 1)): ?>
                            <div class="form-hint" style="color:#d97706; display:flex; align-items:center; gap:4px;">
                                <span class="material-symbols-outlined" style="font-size:15px;">warning</span>
                                <?= $assignedCount ?> students are already assigned — more than this project asks for.
                            </div>
                        <?php elseif ($assignedCount > 0): ?>
                            <div class="form-hint"><?= $assignedCount ?> student<?= $assignedCount === 1 ? '' : 's' ?> currently assigned.</div>
                        <?php endif; ?>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Preferred Academic Year <span class="req">*</span></label>
                        <select name="preferred_year" class="form-select">
                            <?php foreach (PROJECT_YEARS as $yr): ?>
                                <option value="<?= $yr ?>" <?= ($project['preferred_year'] === $yr) ? 'selected' : '' ?>><?= $yr ?></option>
                            <?php endforeach; ?>
                        </select>
                        <?= fieldError($errors, 'preferred_year') ?>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Application Deadline <span class="req">*</span></label>
                        <!-- data-original: a deadline that is not changed is not checked again -->
                        <input type="date" name="deadline" class="form-input" value="<?= htmlspecialchars($deadlineValue) ?>"
                               data-original="<?= htmlspecialchars($originalDeadline) ?>"
                               max="<?= date('Y-m-d', strtotime('+1 year')) ?>">
                        <div class="form-hint">Last day students can apply. A new date must be after today.</div>
                        <?= fieldError($errors, 'deadline') ?>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Project Visibility <span class="req">*</span></label>
                        <div class="radio-inline">
                            <label class="radio-option">
                                <input type="radio" name="visibility" value="Public" <?= ($project['visibility'] === 'Public') ? 'checked' : '' ?>>
                                Public <span style="color:#9ca3af; font-weight:400;">(Visible to all students)</span>
                            </label>
                            <label class="radio-option">
                                <input type="radio" name="visibility" value="Private" <?= ($project['visibility'] === 'Private') ? 'checked' : '' ?>>
                                Private <span style="color:#9ca3af; font-weight:400;">(Only me)</span>
                            </label>
                        </div>
                        <?= fieldError($errors, 'visibility') ?>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Project Status</label>
                    <?php if (isset($adminOnlyStatusLabels[$project['status']])): ?>
                        <!-- Set by the Admin: shown read-only, the organization can't change it -->
                        <select class="form-select" disabled>
                            <option selected><?= htmlspecialchars($adminOnlyStatusLabels[$project['status']]) ?></option>
                        </select>
                        <div style="font-size:12px; color:#6b7280; margin-top:6px;">
                            <?php if ($project['status'] === 'reviewing'): ?>
                                The Admin is reviewing this project. Students will see it after the Admin accepts it.
                            <?php else: ?>
                                This status was set by the Admin and can't be changed here.
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <select name="status" class="form-select" id="statusSelect">
                            <?php foreach ($orgStatusOptions as $val => $label):
                                // the status names stay plain; extra info is shown BELOW the field
                                // Active needs at least one student in the team
                                $locked = ($val === 'inprogress' && $project['status'] !== 'inprogress' && $assignedCount < 1); ?>
                                <option value="<?= $val ?>" <?= ($selectedStatus === $val) ? 'selected' : '' ?> <?= $locked ? 'disabled' : '' ?>>
                                    <?= $label ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if ($project['status'] === 'published'): ?>
                            <div style="font-size:12px; color:#6b7280; margin-top:6px;">
                                Team: <?= (int)$assignedCount ?> / <?= (int)$project['members'] ?> students joined.
                                <?php if ($assignedCount < 1): ?>
                                    Add at least one student to the team before setting it to <b>Active</b>.
                                <?php else: ?>
                                    Set the project to <b>Active</b> when the team is ready to start.
                                <?php endif; ?>
                            </div>
                            <?php if (!$teamIsFull && $assignedCount > 0): ?>
                                <!-- shown when "Active" is chosen before the team is full -->
                                <div id="earlyStartNote" hidden
                                     style="font-size:12.5px; color:#92400e; background:#fffbeb; border:1px solid #fde68a; border-radius:8px; padding:9px 11px; margin-top:8px; line-height:1.45;">
                                    The team has <b><?= (int)$assignedCount ?> of <?= (int)$project['members'] ?></b> students.
                                    Saving will start the project with these <?= (int)$assignedCount ?> student<?= $assignedCount == 1 ? '' : 's' ?>:
                                    <b>Students Required will become <?= (int)$assignedCount ?></b> and no more students can join.
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                        <?php if ($project['status'] === 'inprogress'): ?>
                            <div style="font-size:12px; color:#6b7280; margin-top:6px;">
                                The project becomes <b>Completed</b> by itself when you accept the final report of its team on the Teams page.
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- ===================== FOOTER ACTIONS ===================== -->
            <div class="post-form-footer">
                <div class="footer-left">
                    <a href="manage_projects.php" class="btn-outline">Cancel</a>
                </div>
                <button type="submit" class="btn-solid">
                    <span class="material-symbols-outlined" style="font-size:16px;">save</span>
                    Save Changes
                </button>
            </div>

        </div>
    </form>

</main>


<script src="../../Assets/JS/Organization/edit_project.js?v=<?= filemtime(__DIR__ . '/../../Assets/JS/Organization/edit_project.js') ?>"></script>
<script src="../../Assets/JS/Organization/project-validation.js?v=<?= filemtime(__DIR__ . '/../../Assets/JS/Organization/project-validation.js') ?>"></script>

<?php include "../../Includes/dash_footer.php"; ?>