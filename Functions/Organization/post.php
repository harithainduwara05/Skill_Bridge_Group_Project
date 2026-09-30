<?php

include "../../Config/db.php";
include "../../Session/Session.php";

require_role('organization');
$user = current_user();

$flash = null;

// ---- Supporting documents: allowed types + size limit ----
const DOC_MAX_BYTES = 10 * 1024 * 1024; // 10MB per file
const DOC_ALLOWED_EXT = ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx', 'txt', 'csv', 'png', 'jpg', 'jpeg', 'gif', 'webp', 'zip'];

/**
 * Save every file in $_FILES['attachments'] for the given project.
 * Files with a wrong type or bigger than 10MB are skipped.
 */
function saveProjectDocuments(mysqli $conn, int $projectId): int
{
    if (empty($_FILES['attachments']) || !is_array($_FILES['attachments']['name'])) {
        return 0;
    }

    $uploadDir = __DIR__ . '/../../Assets/Uploads/project_docs/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0775, true);
    }

    $ins = $conn->prepare("INSERT INTO project_documents
                           (project_id, original_name, file_path, file_size, mime_type)
                           VALUES (?,?,?,?,?)");

    $failed = 0;
    $files  = $_FILES['attachments'];

    foreach ($files['name'] as $i => $originalName) {
        if ($files['error'][$i] === UPLOAD_ERR_NO_FILE) continue;

        $ext  = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $size = (int)$files['size'][$i];

        if ($files['error'][$i] !== UPLOAD_ERR_OK
            || !in_array($ext, DOC_ALLOWED_EXT, true)
            || $size <= 0 || $size > DOC_MAX_BYTES) {
            $failed++;
            continue;
        }

        // Unique, safe file name on disk; the original name is kept in the DB
        $storedName = 'project_' . $projectId . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;

        if (!move_uploaded_file($files['tmp_name'][$i], $uploadDir . $storedName)) {
            $failed++;
            continue;
        }

        $relativePath = 'Assets/Uploads/project_docs/' . $storedName;
        $mime         = mime_content_type($uploadDir . $storedName) ?: 'application/octet-stream';
        $cleanName    = mb_substr(basename($originalName), 0, 255);

        $ins->bind_param("issis", $projectId, $cleanName, $relativePath, $size, $mime);
        $ins->execute();
    }

    return $failed;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $title               = trim($_POST['title'] ?? '');
    $category            = trim($_POST['category'] ?? '');
    $keywords            = trim($_POST['keywords'] ?? '');
    $description         = trim($_POST['description'] ?? '');
    $learning_objectives = trim($_POST['learning_objectives'] ?? '');
    $expected_outcomes   = trim($_POST['expected_outcomes'] ?? '');
    $difficulty          = trim($_POST['difficulty'] ?? 'Intermediate');
    $duration_weeks      = trim($_POST['duration_weeks'] ?? '');
    $students_required   = (int)($_POST['students_required'] ?? 1);
    $preferred_year      = trim($_POST['preferred_year'] ?? 'Any Year');
    $deadline            = trim($_POST['deadline'] ?? '');
    $visibility          = trim($_POST['visibility'] ?? 'Public');
    $action              = trim($_POST['action'] ?? 'publish'); // draft | publish

    $status        = ($action === 'draft') ? 'draft' : 'reviewing';
    $duration_text = $duration_weeks !== '' ? $duration_weeks . ' Weeks' : null;

    if (empty($title) || empty($category) || empty($description)) {
        $flash = ['type' => 'error', 'message' => 'Please fill Title, Category and Description.'];
    } else {
        try {
            $company            = $user['username'] ?? '';
            $organization_email = $user['email'];
            $tech_stack         = $keywords; // reused for landing-page display cards

            $sql = "INSERT INTO projects
                    (title, company, organization_email, category, keywords, description,
                     learning_objectives, expected_outcomes, difficulty, duration, members,
                     preferred_year, deadline, visibility, status, tech_stack)
                    VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";

            $stmt = $conn->prepare($sql);
            $stmt->bind_param(
                "ssssssssssisssss",
                $title, $company, $organization_email, $category, $keywords,
                $description, $learning_objectives, $expected_outcomes, $difficulty,
                $duration_text, $students_required, $preferred_year, $deadline,
                $visibility, $status, $tech_stack
            );

            if ($stmt->execute()) {
                $projectId = (int)$conn->insert_id;
                saveProjectDocuments($conn, $projectId);

                header("Location: manage_projects.php?posted=1");
                exit;
            } else {
                $flash = ['type' => 'error', 'message' => 'Failed to save project.'];
            }
        } catch (Exception $e) {
            $flash = ['type' => 'error', 'message' => 'Error: ' . $e->getMessage()];
        }
    }
}

include "../../Includes/org_sidebar.php";
include "../../Includes/dash_header.php";

?>

<style>
    /* ---- Supporting documents: uploaded file list ---- */
    .doc-upload-errors { margin-top: 10px; display: flex; flex-direction: column; gap: 6px; }
    .doc-upload-errors:empty { display: none; }
    .doc-upload-error {
        display: flex; align-items: center; gap: 8px;
        background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca;
        border-radius: 10px; padding: 8px 12px; font-size: 13px;
    }
    .doc-upload-error .material-symbols-outlined { font-size: 18px; }

    .doc-upload-list { margin-top: 12px; display: flex; flex-direction: column; gap: 8px; }
    .doc-upload-list:empty { display: none; }
    .doc-upload-summary { font-size: 12px; color: #6b7280; margin-bottom: 2px; }

    .doc-item {
        display: flex; align-items: center; gap: 12px;
        background: #fff; border: 1px solid #e5e7eb; border-radius: 12px;
        padding: 10px 12px; transition: border-color .2s ease, box-shadow .2s ease;
    }
    .doc-item:hover { border-color: #bfdbfe; box-shadow: 0 2px 10px rgba(0,0,0,0.04); }

    .doc-thumb {
        width: 42px; height: 42px; border-radius: 10px; flex-shrink: 0;
        display: flex; align-items: center; justify-content: center;
        background: #eff6ff; color: #2563eb; overflow: hidden;
    }
    .doc-thumb img { width: 100%; height: 100%; object-fit: cover; }
    .doc-thumb.pdf   { background: #fef2f2; color: #dc2626; }
    .doc-thumb.word  { background: #eff6ff; color: #2563eb; }
    .doc-thumb.ppt   { background: #fff7ed; color: #ea580c; }
    .doc-thumb.excel { background: #f0fdf4; color: #16a34a; }
    .doc-thumb.zip   { background: #f5f3ff; color: #7c3aed; }
    .doc-thumb.text  { background: #f3f4f6; color: #4b5563; }

    .doc-info { flex: 1; min-width: 0; }
    .doc-name {
        display: block; font-size: 13px; font-weight: 600; color: #111827;
        white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        text-decoration: none; cursor: pointer;
    }
    .doc-name:hover { color: #1e40af; text-decoration: underline; }
    .doc-meta { font-size: 12px; color: #9ca3af; margin-top: 2px; }

    .doc-actions { display: flex; gap: 4px; flex-shrink: 0; }
    .doc-btn {
        width: 34px; height: 34px; border-radius: 8px; border: none; background: transparent;
        display: flex; align-items: center; justify-content: center; cursor: pointer;
        color: #6b7280; transition: background .15s ease, color .15s ease;
    }
    .doc-btn .material-symbols-outlined { font-size: 20px; }
    .doc-btn.view:hover   { background: #eff6ff; color: #1e40af; }
    .doc-btn.remove:hover { background: #fef2f2; color: #dc2626; }
</style>

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
                                Private <span style="color:#9ca3af; font-weight:400;">(Only me)</span>
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

<footer class="footer">
    <div>&copy; 2026 SkillBridge. All rights reserved.</div>
    <div class="footer-links">
        <a href="/Skill_Bridge_Group_Project/help_center.php">Help Center</a>
        <a href="/Skill_Bridge_Group_Project/help_center.php#knowledgeBaseSection">Privacy Policy</a>
        <a href="/Skill_Bridge_Group_Project/help_center.php#knowledgeBaseSection">Terms of Service</a>
    </div>
</footer>

<script src="../../Assets/JS/post-project.js?v=<?= filemtime(__DIR__ . '/../../Assets/JS/post-project.js') ?>"></script>

<?php include "../../Includes/dash_footer.php"; ?>