<?php

include "../../Config/db.php";
include "../../Session/Session.php";

require_role('organization');

// Backend (PHP + SQL) of this page
require_once __DIR__ . "/../../Backend/Organization/post.php";

$extra_css = '<link rel="stylesheet" href="../../Assets/CSS/Organization/project_form.css?v=' . filemtime(__DIR__ . '/../../Assets/CSS/Organization/project_form.css') . '">'
           . '<link rel="stylesheet" href="../../Assets/CSS/Organization/post.css?v=' . filemtime(__DIR__ . '/../../Assets/CSS/Organization/post.css') . '">';

// value typed before (after a validation error), safe to print in HTML
function old($key, $default = '') {
    global $old;
    return htmlspecialchars($old[$key] ?? $default);
}
$oldCategory   = $old['category'] ?? '';
$oldDifficulty = $old['difficulty'] ?? 'Intermediate';
$oldYear       = $old['preferred_year'] ?? 'Any Year';
$oldVisibility = $old['visibility'] ?? 'Public';

// deadline picker: from tomorrow to 1 year from today
$deadlineMin = date('Y-m-d', strtotime('+1 day'));
$deadlineMax = date('Y-m-d', strtotime('+1 year'));
include "../../Includes/org_sidebar.php";
include "../../Includes/dash_header.php";

?>


<main class="content">
    <div class="dashboard-header">
        <div>
            <h1>Post New Project</h1>
            <p>Connect with student talent by sharing your project details.</p>
        </div>
    </div>

    <?php if (!empty($flash)): ?>
    <div class="flash-toast flash-<?= htmlspecialchars($flash['type']) ?>" style="margin:0 28px 16px;">
        <?= htmlspecialchars($flash['message']) ?>
    </div>
    <?php endif; ?>

    <!-- novalidate: the checks are done by project-validation.js (messages under the fields)
         and again on the server, instead of the browser's own popups -->
    <form id="postProjectForm" action="" method="POST" enctype="multipart/form-data" novalidate
          data-draft="<?= !empty($isDraft) ? 1 : 0 ?>">

        <div class="post-form-card">

            <div class="required-note">
                Fields marked <span class="req">*</span> are required to publish.
                <b>Save Draft</b> needs only the project title.
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
                        <input type="text" name="title" class="form-input" maxlength="100" value="<?= old('title') ?>" placeholder="e.g. AI-Driven Customer Insights Platform">
                        <?= fieldError($errors, 'title') ?>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Project Category <span class="req">*</span></label>
                        <select name="category" class="form-select">
                            <option value="">Select a category</option>
                            <?php foreach ($categories as $cat): /* from the project_categories table */ ?>
                                <option value="<?= htmlspecialchars($cat) ?>" <?= $oldCategory === $cat ? 'selected' : '' ?>><?= htmlspecialchars($cat) ?></option>
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
                    <input type="hidden" name="keywords" id="keywordsHidden" value="<?= old('keywords') ?>">
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
                    <textarea name="description" class="form-textarea" maxlength="3000" placeholder="Provide a high-level overview of the project goals..."><?= old('description') ?></textarea>
                    <div class="form-hint">At least 50 characters, so students understand the project.</div>
                    <?= fieldError($errors, 'description') ?>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Learning Objectives</label>
                        <textarea name="learning_objectives" class="form-textarea" maxlength="1500" placeholder="What will students learn from this project?"><?= old('learning_objectives') ?></textarea>
                        <?= fieldError($errors, 'learning_objectives') ?>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Expected Outcomes</label>
                        <textarea name="expected_outcomes" class="form-textarea" maxlength="1500" placeholder="What are the final deliverables?"><?= old('expected_outcomes') ?></textarea>
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
                        <?php foreach (PROJECT_DIFFICULTIES as $level): ?>
                            <div class="toggle-btn <?= $oldDifficulty === $level ? 'selected' : '' ?>" data-value="<?= $level ?>"><?= $level ?></div>
                        <?php endforeach; ?>
                    </div>
                    <input type="hidden" name="difficulty" id="difficultyHidden" value="<?= htmlspecialchars($oldDifficulty) ?>">
                    <?= fieldError($errors, 'difficulty') ?>
                </div>

                <div class="form-row-3">
                    <div class="form-group">
                        <label class="form-label">Project Duration (weeks) <span class="req">*</span></label>
                        <input type="number" min="1" max="52" step="1" name="duration_weeks" class="form-input" value="<?= old('duration_weeks') ?>" placeholder="e.g. 12">
                        <div class="form-hint">1 to 52 weeks.</div>
                        <?= fieldError($errors, 'duration_weeks') ?>
                    </div>
                    <div class="form-group">
                        <label class="form-label">No. of Students Required <span class="req">*</span></label>
                        <input type="number" min="1" max="10" step="1" name="students_required" class="form-input" value="<?= old('students_required') ?>" placeholder="e.g. 4">
                        <div class="form-hint">Team size: 1 to 10 students.</div>
                        <?= fieldError($errors, 'students_required') ?>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Preferred Academic Year <span class="req">*</span></label>
                        <select name="preferred_year" class="form-select">
                            <?php foreach (PROJECT_YEARS as $yr): ?>
                                <option value="<?= $yr ?>" <?= $oldYear === $yr ? 'selected' : '' ?>><?= $yr ?></option>
                            <?php endforeach; ?>
                        </select>
                        <?= fieldError($errors, 'preferred_year') ?>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Application Deadline <span class="req">*</span></label>
                        <input type="date" name="deadline" class="form-input" value="<?= old('deadline') ?>"
                               min="<?= $deadlineMin ?>" max="<?= $deadlineMax ?>">
                        <div class="form-hint">Last day students can apply (after today, within 1 year).</div>
                        <?= fieldError($errors, 'deadline') ?>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Project Visibility <span class="req">*</span></label>
                        <div class="radio-inline">
                            <label class="radio-option">
                                <input type="radio" name="visibility" value="Public" <?= $oldVisibility !== 'Private' ? 'checked' : '' ?>>
                                Public <span style="color:#9ca3af; font-weight:400;">(Visible to all students)</span>
                            </label>
                            <label class="radio-option">
                                <input type="radio" name="visibility" value="Private" <?= $oldVisibility === 'Private' ? 'checked' : '' ?>>
                                Private <span style="color:#9ca3af; font-weight:400;">(Only me)</span>
                            </label>
                        </div>
                        <?= fieldError($errors, 'visibility') ?>
                    </div>
                </div>
            </div>

            <!-- ===================== SUPPORTING DOCUMENTS ===================== -->
            <div class="post-section">
                <div class="post-section-title">
                    <span class="material-symbols-outlined">upload_file</span>
                    Supporting Documents
                </div>

                <div class="dropzone" id="dropzone">
                    <span class="material-symbols-outlined">cloud_upload</span>
                    <div class="dropzone-title">Drag &amp; drop files here, or click to browse</div>
                    <div class="dropzone-sub">PDF, Word, PowerPoint, Excel, images, TXT, CSV or ZIP (Max 10MB each)</div>
                    <input type="file" id="fileInput" name="attachments[]" multiple style="display:none;"
                           accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.txt,.csv,.png,.jpg,.jpeg,.gif,.webp,.zip">
                </div>
                <div class="doc-upload-errors" id="fileErrors"></div>
                <div class="doc-upload-list" id="fileList"></div>
            </div>

            <!-- ===================== FOOTER ACTIONS ===================== -->
            <div class="post-form-footer">
                <div class="footer-left">
                    <button type="submit" name="action" value="draft" class="btn-outline">Save Draft</button>
                    <button type="button" id="previewBtn" class="btn-outline">Preview Project</button>
                </div>
                <button type="submit" name="action" value="publish" class="btn-solid">
                    <span class="material-symbols-outlined" style="font-size:16px;">send</span>
                    Publish Project
                </button>
            </div>

        </div>
    </form>

</main>


<script src="../../Assets/JS/Organization/post-project.js?v=<?= filemtime(__DIR__ . '/../../Assets/JS/Organization/post-project.js') ?>"></script>
<script src="../../Assets/JS/Organization/project-validation.js?v=<?= filemtime(__DIR__ . '/../../Assets/JS/Organization/project-validation.js') ?>"></script>

<?php include "../../Includes/dash_footer.php"; ?>