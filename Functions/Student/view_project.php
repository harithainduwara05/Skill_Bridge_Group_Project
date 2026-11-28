<?php
require_once __DIR__ . '/project_bootstrap.php';
$id = projectValidId($_GET['id'] ?? null);
$project = $id ? $projectRepo->find($id, $projectEmail) : null;
if (!$project) {
    http_response_code(404);
    exit('Project not found or unavailable.');
}

$isActiveWorking = (int) ($project['is_student_active'] ?? 0) === 1;
$teamId = (int) ($project['student_team_id'] ?? 0);
if ($isActiveWorking) {
    header('Location: ' . ($teamId > 0 ? "view_team.php?id={$teamId}" : "teams.php?project_id={$id}"));
    exit;
}

$isSaved = in_array($id, $projectRepo->savedIds($projectEmail), true);
$isApplied = $projectRepo->alreadyApplied($projectEmail, $id);
$tags = array_filter(array_map('trim', explode(',', (string) ($project['technology_tags'] ?? ''))));
$organization = (string) ($project['organization_name'] ?? 'Organization');
$words = preg_split('/\s+/', trim($organization));
$initials = strtoupper(implode('', array_map(static fn ($word) => mb_substr($word, 0, 1), array_slice($words, 0, 2))));

$submittedProposal = null;
$proposalSkills = [];
$proposalViewUrl = '';
$proposalDownloadUrl = '';
$proposalFileName = '';

if ($isApplied) {
    try {
        $hasProposalsTable = false;
        $tCheck = $conn->prepare(
            "SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'project_proposals'"
        );
        if ($tCheck) {
            $tCheck->execute();
            $hasProposalsTable = (int) $tCheck->get_result()->fetch_row()[0] > 0;
            $tCheck->close();
        }

        if ($hasProposalsTable) {
            $pStmt = $conn->prepare(
                "SELECT proposal_id, project_id, student_email, proposal_title, target_role, introduction, solution_approach, portfolio_attachment, status, feedback_message, submitted_at, reviewed_at
                 FROM project_proposals
                 WHERE project_id = ? AND LOWER(TRIM(student_email)) = LOWER(TRIM(?))
                 ORDER BY proposal_id DESC
                 LIMIT 1"
            );
            if ($pStmt) {
                $pStmt->bind_param('is', $id, $projectEmail);
                $pStmt->execute();
                $submittedProposal = $pStmt->get_result()->fetch_assoc() ?: null;
                $pStmt->close();
            }

            if ($submittedProposal && !empty($submittedProposal['proposal_id'])) {
                $propId = (int) $submittedProposal['proposal_id'];
                $hasSkillsTable = false;
                $sCheck = $conn->prepare(
                    "SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'proposal_skills'"
                );
                if ($sCheck) {
                    $sCheck->execute();
                    $hasSkillsTable = (int) $sCheck->get_result()->fetch_row()[0] > 0;
                    $sCheck->close();
                }

                if ($hasSkillsTable) {
                    $skStmt = $conn->prepare("SELECT skill_name FROM proposal_skills WHERE proposal_id = ? ORDER BY skill_name ASC");
                    if ($skStmt) {
                        $skStmt->bind_param('i', $propId);
                        $skStmt->execute();
                        $skRes = $skStmt->get_result();
                        while ($skRow = $skRes->fetch_assoc()) {
                            if (!empty($skRow['skill_name'])) {
                                $proposalSkills[] = (string) $skRow['skill_name'];
                            }
                        }
                        $skStmt->close();
                    }
                }
            }
        }

        if (!$submittedProposal) {
            $paStmt = $conn->prepare(
                "SELECT id, project_id, Email, status, applied_at, decided_at
                 FROM project_applications
                 WHERE project_id = ? AND LOWER(TRIM(Email)) = LOWER(TRIM(?))
                 ORDER BY id DESC
                 LIMIT 1"
            );
            if ($paStmt) {
                $paStmt->bind_param('is', $id, $projectEmail);
                $paStmt->execute();
                $paRow = $paStmt->get_result()->fetch_assoc();
                $paStmt->close();
                if ($paRow) {
                    $submittedProposal = [
                        'proposal_title' => (string) ($project['title'] ?? 'Project Proposal'),
                        'target_role' => 'Project Contributor',
                        'introduction' => 'Submitted via project application.',
                        'solution_approach' => 'Application recorded in project applications.',
                        'portfolio_attachment' => null,
                        'status' => (string) ($paRow['status'] ?? 'pending'),
                        'feedback_message' => null,
                        'submitted_at' => (string) ($paRow['applied_at'] ?? ''),
                        'reviewed_at' => (string) ($paRow['decided_at'] ?? ''),
                    ];
                }
            }
        }

        $rawAttach = trim((string) ($submittedProposal['portfolio_attachment'] ?? ''));
        if ($rawAttach !== '') {
            $cleanAttach = ltrim(str_replace('\\', '/', $rawAttach), '/');
            if (str_starts_with($cleanAttach, 'http://') || str_starts_with($cleanAttach, 'https://')) {
                $proposalViewUrl = $cleanAttach;
                $proposalDownloadUrl = $cleanAttach;
                $proposalFileName = basename(parse_url($cleanAttach, PHP_URL_PATH) ?: $cleanAttach);
            } else {
                if (!str_contains($cleanAttach, '/')) {
                    $cleanAttach = 'Assets/Uploads/Student/Proposals/' . $cleanAttach;
                }
                $diskPath = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $cleanAttach);
                if (is_file($diskPath)) {
                    $proposalViewUrl = '../../' . $cleanAttach;
                    $proposalDownloadUrl = '../../' . $cleanAttach;
                    $proposalFileName = basename($cleanAttach);
                }
            }
        }
    } catch (Throwable $e) {
        error_log('Load submitted proposal error: ' . $e->getMessage());
    }
}

$proposalModalData = [
    'project_title' => (string) ($project['title'] ?? 'Project'),
    'organization' => $organization,
    'proposal_title' => (string) ($submittedProposal['proposal_title'] ?? ($project['title'] ?? '') . ' Proposal'),
    'target_role' => (string) ($submittedProposal['target_role'] ?? 'Team Member'),
    'introduction' => (string) ($submittedProposal['introduction'] ?? ''),
    'solution_approach' => (string) ($submittedProposal['solution_approach'] ?? ''),
    'status' => ucwords(str_replace('_', ' ', (string) ($submittedProposal['status'] ?? 'pending'))),
    'feedback_message' => (string) ($submittedProposal['feedback_message'] ?? ''),
    'submitted_at' => !empty($submittedProposal['submitted_at']) && strtotime((string) $submittedProposal['submitted_at']) !== false
        ? date('M d, Y', strtotime((string) $submittedProposal['submitted_at']))
        : '',
    'skills' => $proposalSkills,
    'file_name' => $proposalFileName,
    'view_url' => $proposalViewUrl,
    'download_url' => $proposalDownloadUrl,
];

$pageTitle = 'Project Details | SkillBridge';
$extra_css = '<link rel="stylesheet" href="../../Assets/CSS/Student/dashboard.css">'
    . '<link rel="stylesheet" href="../../Assets/CSS/Student/projects.css?v=' . filemtime(__DIR__ . '/../../Assets/CSS/Student/projects.css') . '">'
    . '<link rel="stylesheet" href="../../Assets/CSS/Student/view_project.css?v=' . filemtime(__DIR__ . '/../../Assets/CSS/Student/view_project.css') . '">';
include __DIR__ . '/../../Includes/student_sidebar.php';
include __DIR__ . '/../../Includes/dash_header.php';
?>
<main class="content view-project-page">
    <?php projectRenderToast(projectToast()); ?>
    <div class="breadcrumb"><a href="projects.php">Projects</a><span>&gt;</span><strong>View Project</strong></div>
    <section class="project-header-card">
        <div class="project-detail-info">
            <div class="project-tags"><span><?= projectEsc($project['category_name']) ?></span><span class="active-tag"><?= projectEsc(ucwords((string) $project['status'])) ?></span></div>
            <h1><?= projectEsc($project['title']) ?></h1>
            <p><?= projectEsc($project['description']) ?></p>
        </div>
        <div class="project-buttons">
            <?php if (!$isApplied): ?>
                <a href="proposal.php?project_id=<?= $id ?>" class="proposal-btn"><i class="fa-solid fa-paper-plane"></i> Submit Proposal</a>
            <?php else: ?>
                <button type="button" class="view-proposal-btn" id="openProposalModalBtn"><i class="fa-regular fa-file-lines"></i> View Proposal</button>
            <?php endif; ?>
            <form method="post" action="project_action.php">
                <input type="hidden" name="csrf" value="<?= projectEsc($projectCsrf) ?>">
                <input type="hidden" name="project_id" value="<?= $id ?>">
                <input type="hidden" name="action" value="toggle_save">
                <input type="hidden" name="return_to" value="view_project.php">
                <button class="save-btn" type="submit"><i class="fa-<?= $isSaved ? 'solid' : 'regular' ?> fa-bookmark"></i> <?= $isSaved ? 'Remove Saved' : 'Save Project' ?></button>
            </form>
        </div>
    </section>
    <section class="project-stat-wrapper">
        <div class="project-stat"><div class="stat-circle"><i class="fa-regular fa-clock"></i></div><div><label>Duration</label><strong><?= projectEsc($project['duration_label']) ?></strong></div></div>
        <div class="project-stat"><div class="stat-circle"><i class="fa-solid fa-users"></i></div><div><label>Team Size</label><strong><?= (int) $project['members'] ?> Members</strong></div></div>
        <div class="project-stat"><div class="stat-circle"><i class="fa-regular fa-calendar"></i></div><div><label>Deadline</label><strong><?= projectEsc($project['deadline']) ?></strong></div></div>
    </section>
    <div class="project-content-grid">
        <div>
            <section class="info-card">
                <h2><i class="fa-solid fa-circle-info"></i> Project Overview</h2>
                <p><?= nl2br(projectEsc($project['description'])) ?></p>
                <h3>Key Objectives</h3>
                <ul>
                    <?php if (!empty($project['learning_objectives'])): ?><li><?= projectEsc($project['learning_objectives']) ?></li><?php endif; ?>
                    <?php if (!empty($project['expected_outcomes'])): ?><li><?= projectEsc($project['expected_outcomes']) ?></li><?php endif; ?>
                </ul>
            </section>
        </div>
        <div>
            <section class="info-card">
                <h2><i class="fa-solid fa-code"></i> Required Skills</h2>
                <?php foreach ($tags as $tag): ?><span class="skill-pill"><?= projectEsc($tag) ?></span><?php endforeach; ?>
            </section>
            <section class="organization-card">
                <div class="organization-header">
                    <div class="organization-logo"><span><?= projectEsc($initials) ?></span></div>
                    <div><h3><?= projectEsc($organization) ?></h3><p><i class="fa-solid fa-building"></i> Project Organization</p></div>
                </div>
                <p class="organization-description">Explore this organization's project opportunities and collaborate through SkillBridge.</p>
            </section>
        </div>
    </div>

    <?php if ($isApplied): ?>
        <div class="proposal-modal-backdrop" id="proposalModal" hidden>
            <div class="proposal-modal-card" role="dialog" aria-modal="true" aria-labelledby="proposalModalTitle">
                <div class="proposal-modal-head">
                    <div>
                        <span class="proposal-modal-badge" id="proposalModalStatus">Pending</span>
                        <h2 id="proposalModalTitle">Submitted Proposal</h2>
                        <p id="proposalModalMeta">Project &bull; Organization</p>
                    </div>
                    <button type="button" class="proposal-modal-close" id="closeProposalModalBtn" aria-label="Close modal">&times;</button>
                </div>

                <div class="proposal-modal-body">
                    <div class="proposal-field-group">
                        <label>Proposal Title</label>
                        <p id="propFieldTitle">-</p>
                    </div>

                    <div class="proposal-field-group">
                        <label>Target Role</label>
                        <p id="propFieldRole">-</p>
                    </div>

                    <div class="proposal-field-group">
                        <label>Introduction</label>
                        <p id="propFieldIntro">-</p>
                    </div>

                    <div class="proposal-field-group">
                        <label>Solution Approach</label>
                        <p id="propFieldApproach">-</p>
                    </div>

                    <div class="proposal-field-group" id="propFieldSkillsWrap" hidden>
                        <label>Selected Skills</label>
                        <div class="proposal-modal-skills" id="propFieldSkills"></div>
                    </div>

                    <div class="proposal-field-group" id="propFieldFeedbackWrap" hidden>
                        <label>Organization Feedback</label>
                        <p id="propFieldFeedback">-</p>
                    </div>

                    <div class="proposal-attachment-box" id="propAttachmentWrap" hidden>
                        <div>
                            <label>Submitted Document</label>
                            <div class="proposal-file-name" id="propFileName"></div>
                        </div>
                        <div class="proposal-file-actions">
                            <a id="propViewBtn" href="#" target="_blank" rel="noopener" class="proposal-view-btn">
                                <i class="fa-regular fa-eye"></i> View Document
                            </a>
                            <a id="propDownloadBtn" href="#" download class="proposal-download-btn">
                                <i class="fa-solid fa-download"></i> Download
                            </a>
                        </div>
                    </div>

                    <div class="proposal-attachment-empty" id="propAttachmentEmpty" hidden>
                        No document was attached with this proposal.
                    </div>
                </div>

                <div class="proposal-modal-foot">
                    <button type="button" class="proposal-modal-close-btn" id="doneProposalModalBtn">Close</button>
                </div>
            </div>
        </div>

        <script>
        document.addEventListener("DOMContentLoaded", function () {
            const payload = <?= json_encode($proposalModalData, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
            const modal = document.getElementById("proposalModal");
            const openBtn = document.getElementById("openProposalModalBtn");
            const closeBtn = document.getElementById("closeProposalModalBtn");
            const doneBtn = document.getElementById("doneProposalModalBtn");

            function openModal() {
                if (!modal) return;
                document.getElementById("proposalModalStatus").textContent = payload.status || "Pending";
                document.getElementById("proposalModalMeta").textContent =
                    (payload.project_title || "Project") +
                    (payload.organization ? " • " + payload.organization : "") +
                    (payload.submitted_at ? " • Submitted " + payload.submitted_at : "");

                document.getElementById("propFieldTitle").textContent = payload.proposal_title || "Not provided";
                document.getElementById("propFieldRole").textContent = payload.target_role || "Not provided";
                document.getElementById("propFieldIntro").textContent = payload.introduction || "Not provided";
                document.getElementById("propFieldApproach").textContent = payload.solution_approach || "Not provided";

                const skillsWrap = document.getElementById("propFieldSkillsWrap");
                const skillsBox = document.getElementById("propFieldSkills");
                if (Array.isArray(payload.skills) && payload.skills.length > 0) {
                    skillsBox.innerHTML = payload.skills.map(function (sk) {
                        const span = document.createElement("span");
                        span.textContent = sk;
                        return span.outerHTML;
                    }).join("");
                    skillsWrap.hidden = false;
                } else {
                    skillsWrap.hidden = true;
                }

                const fbWrap = document.getElementById("propFieldFeedbackWrap");
                if (payload.feedback_message) {
                    document.getElementById("propFieldFeedback").textContent = payload.feedback_message;
                    fbWrap.hidden = false;
                } else {
                    fbWrap.hidden = true;
                }

                const attachWrap = document.getElementById("propAttachmentWrap");
                const attachEmpty = document.getElementById("propAttachmentEmpty");
                const fileNameEl = document.getElementById("propFileName");
                const viewBtn = document.getElementById("propViewBtn");
                const dlBtn = document.getElementById("propDownloadBtn");

                if (payload.download_url && payload.file_name) {
                    fileNameEl.textContent = payload.file_name;
                    viewBtn.href = payload.view_url || payload.download_url;
                    dlBtn.href = payload.download_url;
                    dlBtn.setAttribute("download", payload.file_name);
                    attachWrap.hidden = false;
                    attachEmpty.hidden = true;
                } else {
                    attachWrap.hidden = true;
                    attachEmpty.hidden = false;
                }

                modal.hidden = false;
            }

            function closeModal() {
                if (modal) modal.hidden = true;
            }

            if (openBtn) openBtn.addEventListener("click", openModal);
            if (closeBtn) closeBtn.addEventListener("click", closeModal);
            if (doneBtn) doneBtn.addEventListener("click", closeModal);
            if (modal) {
                modal.addEventListener("click", function (e) {
                    if (e.target === modal) closeModal();
                });
            }
            document.addEventListener("keydown", function (e) {
                if (e.key === "Escape" && modal && !modal.hidden) closeModal();
            });
        });
        </script>
    <?php endif; ?>
</main>
<?php include __DIR__ . '/../../Includes/dash_footer.php'; ?>