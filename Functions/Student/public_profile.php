<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once "../../Config/db.php";

$email = $_GET['email'] ?? null;
if (!$email) {
    die("Invalid profile link");
}

$stmt = $conn->prepare("SELECT * FROM student WHERE Email=?");
$stmt->bind_param("s", $email);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$student) {
    die("Student not found");
}

// Fetch skills
$skills = [];
$q = $conn->prepare("SELECT * FROM skills WHERE Email=? ORDER BY percentage DESC");
$q->bind_param("s", $email);
$q->execute();
$res = $q->get_result();
while ($row = $res->fetch_assoc()) {
    $skills[] = $row;
}
$q->close();

// Fetch portfolios
$portfolios = [];
$q = $conn->prepare("SELECT * FROM student_portfolio_projects WHERE student_email=? ORDER BY start_date DESC");
$q->bind_param("s", $email);
$q->execute();
$res = $q->get_result();
while ($row = $res->fetch_assoc()) {
    $portfolios[] = $row;
}
$q->close();

// Fetch Internal Projects (Completed)
$internal_projects = [];
$q = $conn->prepare("
    SELECT sp.student_project_id, p.title, p.description, org.Name AS company, sp.role, p.icon, sp.progress, p.status,
           (SELECT GROUP_CONCAT(ps.skill_name ORDER BY ps.skill_name SEPARATOR ', ') FROM project_skills ps WHERE ps.project_id = p.id) AS tech_stack
    FROM student_projects sp
    JOIN projects p ON p.id = sp.project_id
    LEFT JOIN organization org ON org.Email = p.organization_email
    WHERE sp.Email = ? 
    AND (LOWER(sp.status) IN ('completed', 'complete', 'done') OR LOWER(p.status) IN ('completed', 'complete', 'done'))
");
$q->bind_param("s", $email);
$q->execute();
$res = $q->get_result();
while ($row = $res->fetch_assoc()) {
    $internal_projects[] = $row;
}
$q->close();

// Fetch Certificates
$certificates = [];
$q = $conn->prepare("SELECT * FROM certificates WHERE Email=? ORDER BY issue_date DESC");
$q->bind_param("s", $email);
$q->execute();
$res = $q->get_result();
while ($row = $res->fetch_assoc()) {
    $certificates[] = $row;
}
$q->close();

// Record Profile View (skip if it's the student themselves viewing it, we can check session if available)
if (session_status() === PHP_SESSION_NONE) {
    session_name('skill_bridge_sess');
    session_start();
}
if (!isset($_SESSION['user']) || ($_SESSION['user']['Email'] ?? $_SESSION['user']['email'] ?? '') !== $email) {
    $q = $conn->prepare("INSERT INTO student_profile_views (student_email) VALUES (?)");
    $q->bind_param("s", $email);
    $q->execute();
    $q->close();
}

$name = htmlspecialchars($student['Name'] ?? 'Unknown');
$initials = strtoupper(mb_substr($name, 0, 1) . mb_substr(explode(' ', $name)[1] ?? '', 0, 1));
if (strlen($initials) < 2 && strlen($name) >= 2) {
    $initials = strtoupper(mb_substr($name, 0, 2));
}

$photoUrl = '';
$rawPhoto = $student['profile_image'] ?? '';
if ($rawPhoto !== '') {
    $cleanPhoto = basename(str_replace('\\', '/', $rawPhoto));
    if (is_file(__DIR__ . '/../../Assets/Images/Student/Profile/' . $cleanPhoto)) {
        $photoUrl = '../../Assets/Images/Student/Profile/' . rawurlencode($cleanPhoto);
    } elseif (is_file(__DIR__ . '/../../Assets/Images/Student/' . $cleanPhoto)) {
        $photoUrl = '../../Assets/Images/Student/' . rawurlencode($cleanPhoto);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $name ?> | SkillBridge Public Profile</title>
    <link rel="stylesheet" href="../../Assets/CSS/Student/public_profile.css?v=<?= time() ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
    <div class="public-profile-container">


        <div class="card profile-header-card">
            <div class="avatar-container">
                <?php if ($photoUrl): ?>
                    <img src="<?= htmlspecialchars($photoUrl) ?>" alt="<?= $name ?>">
                <?php else: ?>
                    <div class="avatar-initials"><?= $initials ?></div>
                <?php endif; ?>
            </div>
            <div class="profile-info">
                <h1><?= $name ?></h1>
                <p class="subtitle">
                    <?php if(!empty($student['degree'])): ?>
                        <?= htmlspecialchars($student['degree']) ?> 
                        <?= !empty($student['year']) ? '• Year ' . htmlspecialchars($student['year']) : '' ?>
                        <br>
                    <?php endif; ?>
                    <?= htmlspecialchars($student['University'] ?? 'University not specified') ?>
                </p>
                
                <div class="social-links">
                    <?php if (!empty($student['github'])): ?>
                        <a href="<?= htmlspecialchars($student['github']) ?>" target="_blank" class="social-btn"><i class="fa-brands fa-github"></i> GitHub</a>
                    <?php endif; ?>
                    <?php if (!empty($student['linkedin'])): ?>
                        <a href="<?= htmlspecialchars($student['linkedin']) ?>" target="_blank" class="social-btn"><i class="fa-brands fa-linkedin"></i> LinkedIn</a>
                    <?php endif; ?>
                    <?php if (!empty($student['website'])): ?>
                        <a href="<?= htmlspecialchars($student['website']) ?>" target="_blank" class="social-btn"><i class="fa-solid fa-globe"></i> Website</a>
                    <?php endif; ?>
                    <a href="mailto:<?= htmlspecialchars($student['Email']) ?>" class="social-btn"><i class="fa-regular fa-envelope"></i> Email</a>

                    <button type="button" class="linkedin-outline-btn" style="background:#0f172a; color:#fff; border:none; padding:8px 16px; border-radius:8px;" onclick="sharePublicProfile()">
                    <i class="fa-solid fa-share-nodes"></i> Share Profile
                </button>
                </div>
            </div>
        </div>

        <div class="stats-row">
            <div class="stat-card">
                <div class="stat-icon blue"><i class="fa-solid fa-bolt"></i></div>
                <div class="stat-text">
                    <span class="stat-label">SKILLS</span>
                    <span class="stat-value"><?= count($skills) ?></span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon green"><i class="fa-regular fa-window-maximize"></i></div>
                <div class="stat-text">
                    <span class="stat-label">PORTFOLIO</span>
                    <span class="stat-value"><?= count($portfolios) ?></span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon grey"><i class="fa-solid fa-briefcase"></i></div>
                <div class="stat-text">
                    <span class="stat-label">PROJECTS</span>
                    <span class="stat-value"><?= count($internal_projects) ?></span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon orange"><i class="fa-solid fa-award"></i></div>
                <div class="stat-text">
                    <span class="stat-label">CERTIFICATES</span>
                    <span class="stat-value"><?= count($certificates) ?></span>
                </div>
            </div>
        </div>

        <div class="card about-card">
            <h3 class="card-title"><i class="fa-regular fa-user"></i> ABOUT</h3>
            <p class="about-text"><?= nl2br(htmlspecialchars($student['bio'] ?? 'No information provided yet.')) ?></p>
        </div>

        <div class="card skills-card">
            <h3 class="card-title"><i class="fa-solid fa-bolt"></i> SKILLS</h3>
            <?php if (empty($skills)): ?>
                <p class="empty-text">No skills added yet.</p>
            <?php else: ?>
                <div class="skills-list">
                    <?php foreach ($skills as $skill): ?>
                        <div class="skill-item">
                            <span class="skill-name"><?= htmlspecialchars($skill['skill_name']) ?></span>
                            <span class="skill-level"><?= htmlspecialchars($skill['level']) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <?php 
        // Combine and limit to 5 projects max for display
        $all_projects = [];
        foreach ($internal_projects as $proj) {
            $proj['type'] = 'internal';
            $all_projects[] = $proj;
        }
        foreach ($portfolios as $proj) {
            $proj['type'] = 'portfolio';
            $all_projects[] = $proj;
        }
        $display_projects = array_slice($all_projects, 0, 5);
        ?>

        <?php if (!empty($all_projects)): ?>
        <div class="card projects-card">
            <div class="card-header-flex">
                <h2>Projects (<?= count($all_projects) ?>)</h2>
            </div>
            
            <div class="linkedin-projects-list">
                <?php foreach ($display_projects as $proj): ?>
                    <?php if ($proj['type'] === 'internal'): 
                        $techs = array_values(array_filter(array_map('trim', explode(',', (string)$proj['tech_stack']))));
                        $projData = [
                            'title' => $proj['title'],
                            'description' => $proj['description'],
                            'organization' => $proj['company'],
                            'role' => $proj['role'],
                            'status' => 'Completed',
                            'progress' => (int)$proj['progress'],
                            'technologies' => $techs
                        ];
                        
                        $displayTech = "";
                        if (count($techs) > 0) {
                            if (count($techs) > 2) {
                                $displayTech = htmlspecialchars($techs[0]) . ", " . htmlspecialchars($techs[1]) . " and " . (count($techs) - 2) . " skill" . ((count($techs) - 2) > 1 ? "s" : "");
                            } else {
                                $displayTech = htmlspecialchars(implode(', ', $techs));
                            }
                        }
                    ?>
                        <div class="linkedin-project-item">
                            <div class="project-thumb">
                                <div class="linkedin-project-image-placeholder">
                                    <?php if (!empty($proj['icon'])): ?>
                                        <img src="../../<?= htmlspecialchars($proj['icon']) ?>" alt="Project Cover" onerror="this.style.display='none'">
                                    <?php else: ?>
                                        <div style="background:#f1f5f9;width:100%;height:100%;"></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="project-content">
                                <h3><?= htmlspecialchars($proj['title']) ?></h3>
                                <p class="project-date">SkillBridge Internal Project</p>
                                <p class="project-desc">
                                    <?= htmlspecialchars(mb_strimwidth((string)$proj['description'], 0, 160, '...')) ?>
                                    <button type="button" class="view-internal-btn" data-project="<?= htmlspecialchars(json_encode($projData, JSON_HEX_APOS | JSON_HEX_QUOT)) ?>">... more</button>
                                </p>
                                
                                <?php if ($displayTech): ?>
                                <p class="project-skills-list">
                                    <i class="fa-regular fa-gem"></i> <strong><?= $displayTech ?></strong>
                                </p>
                                <?php endif; ?>
                                
                                <?php if (!empty($proj['company'])): ?>
                                <p class="project-association">
                                    <img src="../../Assets/Images/logo.png" alt="Icon" onerror="this.style.display='none'"> Associated with <?= htmlspecialchars($proj['company']) ?>
                                </p>
                                <?php endif; ?>
                            </div>
                        </div>

                    <?php else: 
                        // Portfolio Project Block
                        $techQ = $conn->prepare("SELECT t.technology_name FROM student_technologies t JOIN student_portfolio_project_technologies pt ON pt.technology_id = t.technology_id WHERE pt.portfolio_project_id = ?");
                        $techQ->bind_param("i", $proj['portfolio_project_id']);
                        $techQ->execute();
                        $techRes = $techQ->get_result();
                        $techs = [];
                        while($tr = $techRes->fetch_assoc()) {
                            $techs[] = $tr['technology_name'];
                        }
                        $techQ->close();
                        
                        $displayTech = "";
                        if (count($techs) > 0) {
                            if (count($techs) > 2) {
                                $displayTech = htmlspecialchars($techs[0]) . ", " . htmlspecialchars($techs[1]) . " and " . (count($techs) - 2) . " skill" . ((count($techs) - 2) > 1 ? "s" : "");
                            } else {
                                $displayTech = htmlspecialchars(implode(', ', $techs));
                            }
                        }
                        
                        $startFormat = '';
                        if (!empty($proj['start_date'])) {
                            $startFormat = date('M Y', strtotime($proj['start_date']));
                            if (!empty($proj['end_date'])) {
                                $startFormat .= ' - ' . date('M Y', strtotime($proj['end_date']));
                            } else {
                                $startFormat .= ' - Present';
                            }
                        }
                    ?>
                        <div class="linkedin-project-item">
                            <div class="project-thumb">
                                <div class="linkedin-project-image-placeholder">
                                    <?php if (!empty($proj['project_image'])): ?>
                                        <img src="../../<?= htmlspecialchars($proj['project_image']) ?>" alt="Project Cover" onerror="this.style.display='none'">
                                    <?php else: ?>
                                        <div style="background:#f1f5f9;width:100%;height:100%;"></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="project-content">
                                <h3><?= htmlspecialchars($proj['title']) ?></h3>
                                <?php if ($startFormat): ?>
                                    <p class="project-date"><?= htmlspecialchars($startFormat) ?></p>
                                <?php endif; ?>
                                <p class="project-desc">
                                    <?= htmlspecialchars(mb_strimwidth((string)$proj['description'], 0, 160, '...')) ?>
                                </p>
                                
                                <?php if ($displayTech): ?>
                                <p class="project-skills-list">
                                    <i class="fa-regular fa-gem"></i> <strong><?= $displayTech ?></strong>
                                </p>
                                <?php endif; ?>

                                <?php if (!empty($proj['repository_url'])): ?>
                                <div style="margin-top: 12px;">
                                    <a href="<?= htmlspecialchars($proj['repository_url']) ?>" target="_blank" class="linkedin-outline-btn">
                                        Show project <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                    </a>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
                
                <?php if(count($all_projects) > 5): ?>
                    <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #e2e8f0; text-align: center;">
                        <button type="button" class="view-internal-btn" style="font-size: 15px; color: #64748b; margin: 0;">Show all <?= count($all_projects) ?> projects <i class="fa-solid fa-arrow-right"></i></button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <?php if (!empty($certificates)): ?>
        <div class="card certificates-card">
            <div class="card-header-flex">
                <h2>Licenses & certifications</h2>
            </div>
            <div class="linkedin-projects-list">
                <?php foreach ($certificates as $cert): 
                    $fileUrl = '';
                    if (!empty($cert['certificate_file'])) {
                        $fileUrl = '../../' . htmlspecialchars($cert['certificate_file']);
                    }
                ?>
                    <div class="linkedin-project-item">
                        <div class="project-thumb">
                            <div class="linkedin-project-image-placeholder cert-placeholder" style="background:#ffffff; border: 1px solid #e2e8f0; border-radius: 4px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                                <i class="fa-solid fa-building-columns" style="color:#94a3b8;font-size:26px;"></i>
                            </div>
                        </div>
                        <div class="project-content">
                            <h3><?= htmlspecialchars($cert['certificate_name']) ?></h3>
                            <p class="project-date" style="color:#000;font-weight:400;margin-bottom:4px;"><?= htmlspecialchars($cert['issuer']) ?></p>
                            <p class="project-date">Issued <?= htmlspecialchars($cert['issue_date']) ?></p>
                            
                            <?php if (!empty($cert['credential_id'])): ?>
                                <p class="project-date" style="margin-top:4px;">Credential ID <?= htmlspecialchars($cert['credential_id']) ?></p>
                            <?php endif; ?>

                            <div style="display:flex; gap: 10px; margin-top: 12px; flex-wrap:wrap;">
                                <?php if (!empty($cert['credential_url'])): ?>
                                    <a href="<?= htmlspecialchars($cert['credential_url']) ?>" target="_blank" class="linkedin-outline-btn">
                                        Show credential <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                    </a>
                                <?php endif; ?>
                                
                                <?php if ($fileUrl && str_ends_with(strtolower(parse_url($fileUrl, PHP_URL_PATH) ?? ''), '.pdf')): ?>
                                    <button type="button" class="linkedin-outline-btn view-cert-btn" data-url="<?= $fileUrl ?>" data-type="pdf">
                                        View Certificate
                                    </button>
                                <?php elseif ($fileUrl): ?>
                                    <button type="button" class="linkedin-outline-btn view-cert-btn" data-url="<?= $fileUrl ?>" data-type="image">
                                        View Certificate
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

    </div>

    <!-- Internal Project Details Modal -->
    <div class="modal-overlay" id="publicProjectModal" style="display:none;">
        <div class="modal-card">
            <div class="modal-header">
                <h2 id="pubModalTitle">Project Title</h2>
                <button type="button" class="modal-close" id="pubModalClose"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="modal-body">
                <h3>About this project</h3>
                <p id="pubModalDesc" class="modal-desc"></p>
                
                <h3>Project information</h3>
                <div class="modal-info-grid">
                    <div class="info-box">
                        <span class="info-label">Status</span>
                        <strong id="pubModalStatus"></strong>
                    </div>
                    <div class="info-box">
                        <span class="info-label">Organization</span>
                        <strong id="pubModalOrg"></strong>
                    </div>
                    <div class="info-box">
                        <span class="info-label">Role</span>
                        <strong id="pubModalRole"></strong>
                    </div>
                    <div class="info-box">
                        <span class="info-label">Progress</span>
                        <strong id="pubModalProgress"></strong>
                    </div>
                </div>

                <h3>Technologies used</h3>
                <div class="modal-tags" id="pubModalTags"></div>

                <div class="modal-note">
                    This is an organization-managed project. Your edits affect only how it appears in your portfolio.
                </div>
            </div>
        </div>
    </div>

    <!-- Certificate File Modal -->
    <div class="modal-overlay" id="publicCertModal" style="display:none;">
        <div class="modal-card" style="width: min(800px, 100%);">
            <div class="modal-header">
                <h2 style="font-size:20px;">Certificate Preview</h2>
                <button type="button" class="modal-close" id="pubCertModalClose"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="modal-body" id="pubCertModalBody" style="padding: 0; display:flex; justify-content:center; align-items:center; background:#f1f5f9; min-height:400px;">
            </div>
        </div>
    </div>

    <!-- Toast Notification -->
    <div id="publicToast" style="position:fixed; top:20px; right:20px; background:#fff; border-left:4px solid #10b981; padding:12px 20px; border-radius:8px; box-shadow:0 10px 25px rgba(0,0,0,0.1); display:flex; align-items:center; gap:12px; transform:translateX(150%); opacity:0; transition:0.3s cubic-bezier(0.4, 0, 0.2, 1); z-index:9999;">
        <i class="fa-solid fa-circle-check" style="color:#10b981; font-size:20px;"></i>
        <span id="publicToastMsg" style="color:#0f172a; font-weight:600; font-size:14px;"></span>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('publicProjectModal');
            const closeBtn = document.getElementById('pubModalClose');
            
            const certModal = document.getElementById('publicCertModal');
            const certClose = document.getElementById('pubCertModalClose');
            const certBody = document.getElementById('pubCertModalBody');
            
            document.querySelectorAll('.view-cert-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const url = this.getAttribute('data-url');
                    const type = this.getAttribute('data-type');
                    certBody.innerHTML = '';
                    
                    if (type === 'pdf') {
                        certBody.innerHTML = `<iframe src="${url}#toolbar=0&view=FitH" width="100%" height="600px" style="border:none;"></iframe>`;
                    } else {
                        certBody.innerHTML = `<img src="${url}" style="max-width:100%; max-height:75vh; object-fit:contain; padding:20px;">`;
                    }
                    certModal.style.display = 'flex';
                });
            });
            
            if (certClose) certClose.addEventListener('click', () => { certModal.style.display = 'none'; certBody.innerHTML = ''; });
            if (certModal) certModal.addEventListener('click', e => { if(e.target === certModal) { certModal.style.display = 'none'; certBody.innerHTML = ''; } });

            
            document.querySelectorAll('.view-internal-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const data = JSON.parse(this.getAttribute('data-project'));
                    
                    document.getElementById('pubModalTitle').textContent = data.title;
                    document.getElementById('pubModalDesc').textContent = data.description;
                    document.getElementById('pubModalStatus').textContent = data.status;
                    document.getElementById('pubModalOrg').textContent = data.organization || 'Not specified';
                    document.getElementById('pubModalRole').textContent = data.role || 'Member';
                    document.getElementById('pubModalProgress').textContent = data.progress + '%';
                    
                    const tagsDiv = document.getElementById('pubModalTags');
                    tagsDiv.innerHTML = '';
                    if (data.technologies && data.technologies.length > 0) {
                        data.technologies.forEach(tech => {
                            const span = document.createElement('span');
                            span.textContent = tech;
                            tagsDiv.appendChild(span);
                        });
                    } else {
                        tagsDiv.textContent = 'None listed';
                    }
                    
                    modal.style.display = 'flex';
                });
            });
            
            closeBtn.addEventListener('click', () => { modal.style.display = 'none'; });
            modal.addEventListener('click', e => { if(e.target === modal) modal.style.display = 'none'; });
        });

        function sharePublicProfile() {
            const url = window.location.href;
            const toast = document.getElementById('publicToast');
            const msg = document.getElementById('publicToastMsg');
            
            if (navigator.clipboard) {
                navigator.clipboard.writeText(url).then(() => {
                    msg.textContent = 'Profile link copied to clipboard!';
                    toast.style.transform = 'translateX(0)';
                    toast.style.opacity = '1';
                    setTimeout(() => {
                        toast.style.transform = 'translateX(150%)';
                        toast.style.opacity = '0';
                    }, 3000);
                }).catch(err => {
                    console.error('Failed to copy', err);
                });
            }
        }
        
        // Expose globally
        window.sharePublicProfile = sharePublicProfile;
    </script>
</body>
</html>
