<?php
require_once __DIR__ . '/../Config/db.php';

// Fetch internships
$sql_internships = "SELECT * FROM internships";
$result_internships = $conn->query($sql_internships);

$page_css = "Assets/CSS/internships.css";
include '../Includes/header.php';
?>

<div class="internships-container">
    
    <div class="internships-header">
        <h1>Find Your Next Internship Opportunity</h1>
        <p>Connect with leading technology companies and start your professional journey through high-impact roles designed for growth.</p>
    </div>

    <div class="filter-bar">
        <div class="filter-group keyword">
            <label>Keyword</label>
            <input type="text" placeholder="🔍 Design, Dev...">
        </div>
        <div class="filter-group">
            <label>Industry</label>
            <select>
                <option>All Industries</option>
                <option>IT Services</option>
                <option>Software Development</option>
                <option>Healthcare Tech</option>
            </select>
        </div>
        <div class="filter-group">
            <label>Location</label>
            <select>
                <option>Remote</option>
                <option>On-site</option>
                <option>Hybrid</option>
            </select>
        </div>
        <div class="filter-group">
            <label>Duration</label>
            <select>
                <option>3 Months</option>
                <option>6 Months</option>
            </select>
        </div>
        <button class="btn-filter" onclick="window.location.href='../Auth/login.php'">♈ Apply Filters</button>
    </div>

    <div class="internships-layout">
        <!-- Left List -->
        <div class="internships-list">
            
            <?php if(isset($result_internships) && $result_internships->num_rows > 0): ?>
                <?php while($internship = $result_internships->fetch_assoc()): ?>
                    <div class="internship-card">
                        <div class="company-logo" style="<?php echo htmlspecialchars($internship['logo_style'] ?? ''); ?>">
                            <?php if(!empty($internship['logo_text'])): ?>
                                <?php echo htmlspecialchars($internship['logo_text']); ?>
                            <?php else: ?>
                                <img src="<?php echo $base_url; ?>Assets/Images/logo.png" alt="Logo" style="width: 30px; height: auto;">
                            <?php endif; ?>
                        </div>
                        <div class="internship-info">
                            <h3 class="internship-title"><?php echo htmlspecialchars($internship['title']); ?></h3>
                            <div class="internship-company"><?php echo htmlspecialchars($internship['company']); ?></div>
                            <div class="internship-industry">🏢 <?php echo htmlspecialchars($internship['industry']); ?></div>
                            
                            <div class="tech-tags">
                                <?php 
                                if (!empty($internship['tech_tags'])) {
                                    $tags = explode(',', $internship['tech_tags']);
                                    foreach($tags as $tag): 
                                ?>
                                        <span class="tech-tag"><?php echo htmlspecialchars(trim($tag)); ?></span>
                                <?php 
                                    endforeach; 
                                }
                                ?>
                            </div>

                            <div class="internship-meta">
                                <span>🕒 <?php echo htmlspecialchars($internship['duration']); ?></span>
                                <span>📅 <?php echo htmlspecialchars($internship['deadline']); ?></span>
                            </div>
                        </div>
                        <div class="bookmark" style="opacity: 0.5;">🔖</div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p style="color: #555;">No internships available at the moment.</p>
            <?php endif; ?>

        </div>

        <!-- Right Details -->
        <div class="internship-details">
            <div class="details-hero">
                <img src="<?php echo $base_url; ?>Assets/Images/landing.jpeg" alt="Working">
                <div class="details-company-logo">
                    <img src="<?php echo $base_url; ?>Assets/Images/logo.png" alt="Logo" style="width: 50px;">
                </div>
            </div>
            
            <div class="details-content">
                <div class="details-header">
                    <div class="details-title">
                        <h2>UI/UX Designer Intern</h2>
                        <p>TechFlow Solutions</p>
                    </div>
                    <div class="details-actions">
                        <button class="btn-view" onclick="window.location.href='../Auth/login.php'">View Company</button>
                        <button class="btn-apply" onclick="window.location.href='../Auth/login.php'">Apply Now</button>
                    </div>
                </div>

                <div class="stats-grid">
                    <div class="stat-box">
                        <div class="stat-label">STIPEND</div>
                        <div class="stat-value">$1,200/mo</div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-label">DURATION</div>
                        <div class="stat-value">6 Months</div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-label">COMMITMENT</div>
                        <div class="stat-value">Full-time</div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-label">LOCATION</div>
                        <div class="stat-value">Remote</div>
                    </div>
                </div>

                <div class="details-section">
                    <h3>About TechFlow Solutions</h3>
                    <p>TechFlow Solutions is a leading innovation lab focusing on academic-to-industry transition tools. We believe in empowering students by providing them with real-world problems that require creative, human-centric solutions.</p>
                </div>

                <div class="details-section">
                    <h3>Role Description</h3>
                    <p>Join our design team to help build the next generation of SkillBridge features. You will work closely with senior designers and product managers to translate user needs into intuitive digital experiences.</p>
                </div>

                <div class="details-section">
                    <h3>Responsibilities</h3>
                    <ul class="responsibilities-list">
                        <li>Conducting user research and synthesizing findings.</li>
                        <li>Creating wireframes and task flows for new features.</li>
                        <li>Building high-fidelity interactive prototypes.</li>
                        <li>Collaborating on developer handoff and QA.</li>
                    </ul>
                </div>

                <div class="details-section">
                    <h3>Application Process</h3>
                    <div class="process-flow">
                        <div class="process-step">Submit portfolio</div>
                        <div class="process-arrow">→</div>
                        <div class="process-step">Initial screening</div>
                        <div class="process-arrow">→</div>
                        <div class="process-step">Design challenge</div>
                        <div class="process-arrow">→</div>
                        <div class="process-step">Final interview</div>
                    </div>
                </div>

            </div>
        </div>
    </div>

</div>

<?php include '../Includes/footer.php'; ?>
