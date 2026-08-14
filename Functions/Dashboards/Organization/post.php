<?php

include "../../../Session/Session.php";

require_role('organization');

$user = current_user();

include "../../../Includes/org_sidebar.php";
include "../../../Includes/dash_header.php";

?>

<main class="content">
    <div class="dashboard-header">
        <div>
            <h1>Post New Project</h1>
            <p>Connect with student talent by sharing your project details.</p>
        </div>
    </div>

    <!-- Frontend only for now — hook this form's "action" up to your backend handler when ready -->
    <form id="postProjectForm" action="" method="POST" enctype="multipart/form-data">

        <div class="post-form-card">

            <!-- ===================== PROJECT BASICS ===================== -->
            <div class="post-section">
                <div class="post-section-title">
                    <span class="material-symbols-outlined">info</span>
                    Project Basics
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Project Title</label>
                        <input type="text" name="title" class="form-input" placeholder="e.g. AI-Driven Customer Insights Platform" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Project Category</label>
                        <select name="category" class="form-select" required>
                            <option value="">Select a category</option>
                            <option value="Web Development">Web Development</option>
                            <option value="Mobile Development">Mobile Development</option>
                            <option value="AI / Machine Learning">AI / Machine Learning</option>
                            <option value="Data Science">Data Science</option>
                            <option value="UI/UX Design">UI/UX Design</option>
                            <option value="Cloud & DevOps">Cloud & DevOps</option>
                            <option value="Cybersecurity">Cybersecurity</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Required Skills</label>
                    <div class="chip-input-box" id="chipBox">
                        <input type="text" id="chipInput" placeholder="Type and press enter...">
                    </div>
                    <input type="hidden" name="keywords" id="keywordsHidden">
                    <div class="form-hint">Press Enter after each skill (e.g. React, Python, Figma).</div>
                </div>
            </div>

            <!-- ===================== PROJECT DETAILS ===================== -->
            <div class="post-section">
                <div class="post-section-title">
                    <span class="material-symbols-outlined">description</span>
                    Project Details
                </div>

                <div class="form-group">
                    <label class="form-label">Project Description</label>
                    <textarea name="description" class="form-textarea" placeholder="Provide a high-level overview of the project goals..." required></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Learning Objectives</label>
                        <textarea name="learning_objectives" class="form-textarea" placeholder="What will students learn from this project?"></textarea>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Expected Outcomes</label>
                        <textarea name="expected_outcomes" class="form-textarea" placeholder="What are the final deliverables?"></textarea>
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
                    <label class="form-label">Difficulty Level</label>
                    <div class="toggle-group" id="difficultyGroup">
                        <div class="toggle-btn" data-value="Beginner">Beginner</div>
                        <div class="toggle-btn selected" data-value="Intermediate">Intermediate</div>
                        <div class="toggle-btn" data-value="Advanced">Advanced</div>
                    </div>
                    <input type="hidden" name="difficulty" id="difficultyHidden" value="Intermediate">
                </div>

                <div class="form-row-3">
                    <div class="form-group">
                        <label class="form-label">Project Duration (weeks)</label>
                        <input type="number" min="1" name="duration_weeks" class="form-input" placeholder="e.g. 12">
                    </div>
                    <div class="form-group">
                        <label class="form-label">No. of Students Required</label>
                        <input type="number" min="1" name="students_required" class="form-input" placeholder="e.g. 4">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Preferred Academic Year</label>
                        <select name="preferred_year" class="form-select">
                            <option value="Any Year">Any Year</option>
                            <option value="Year 1">Year 1</option>
                            <option value="Year 2">Year 2</option>
                            <option value="Year 3">Year 3</option>
                            <option value="Year 4">Year 4</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Application Deadline</label>
                        <input type="date" name="deadline" class="form-input">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Project Visibility</label>
                        <div class="radio-inline">
                            <label class="radio-option">
                                <input type="radio" name="visibility" value="Public" checked>
                                Public <span style="color:#9ca3af; font-weight:400;">(Visible to all students)</span>
                            </label>
                            <label class="radio-option">
                                <input type="radio" name="visibility" value="Private">
                                Private <span style="color:#9ca3af; font-weight:400;">(Invite only)</span>
                            </label>
                        </div>
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
                    <div class="dropzone-sub">Upload project briefs, technical specs, or reference images (Max 10MB)</div>
                    <input type="file" id="fileInput" name="attachments[]" multiple style="display:none;">
                </div>
                <div class="dropzone-filelist" id="fileList"></div>
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

<footer class="footer">
    <div>&copy; 2026 SkillBridge. All rights reserved.</div>
    <div class="footer-links">
        <a href="#">Help Center</a>
        <a href="#">Privacy Policy</a>
        <a href="#">Terms of Service</a>
    </div>
</footer>

<script src="../../../Assets/JS/post-project.js"></script>

<?php include "../../../Includes/dash_footer.php"; ?>