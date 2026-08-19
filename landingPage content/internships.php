<?php 
include '../Includes/header.php'; 
?>

<link rel="stylesheet" href="../Assets/CSS/internships.css">

<div class="internships-container">
    <div class="internships-header">
        <h1>Explore Internship Opportunities</h1>
        <p>Find real-world industry experience provided by top partner companies. Apply directly with your SkillBridge verified student profile.</p>
    </div>

    <!-- Filter Bar -->
    <div class="filter-bar">
        <div class="filter-group keyword">
            <label>Search Keyword</label>
            <input type="text" placeholder="Title, skill, or company...">
        </div>
        <div class="filter-group">
            <label>Industry</label>
            <select>
                <option value="">All Industries</option>
                <option>Software Engineering</option>
                <option>Data Science</option>
                <option>UI/UX Design</option>
                <option>Cybersecurity</option>
            </select>
        </div>
        <div class="filter-group">
            <label>Location / Type</label>
            <select>
                <option value="">All Types</option>
                <option>Remote</option>
                <option>On-site</option>
                <option>Hybrid</option>
            </select>
        </div>
        <button class="btn-filter">
            <i class="fa-solid fa-filter"></i> Filter Results
        </button>
    </div>

    <!-- Main Content Layout -->
    <div class="internships-layout">
        
        <!-- Internship List (Left Column) -->
        <div class="internships-list">
            
            <!-- Card 1 -->
            <div class="internship-card active">
                <div class="company-logo">TC</div>
                <div class="internship-info">
                    <h3 class="internship-title">Frontend Developer Intern</h3>
                    <div class="internship-company">TechCorp Solutions</div>
                    <div class="internship-industry"><i class="fa-solid fa-location-dot"></i> Colombo, Sri Lanka (Hybrid)</div>
                    <div class="tech-tags">
                        <span class="tech-tag">React.js</span>
                        <span class="tech-tag">TypeScript</span>
                        <span class="tech-tag">CSS3</span>
                    </div>
                    <div class="internship-meta">
                        <span><i class="fa-regular fa-clock"></i> 6 Months</span>
                        <span>Posted 2 days ago</span>
                    </div>
                </div>
                <div class="bookmark"><i class="fa-solid fa-bookmark"></i></div>
            </div>

            <!-- Card 2 -->
            <div class="internship-card">
                <div class="company-logo" style="background:#fef3c7; color:#d97706;">DS</div>
                <div class="internship-info">
                    <h3 class="internship-title">Data Science Trainee</h3>
                    <div class="internship-company">DataScale Labs</div>
                    <div class="internship-industry"><i class="fa-solid fa-globe"></i> Remote</div>
                    <div class="tech-tags">
                        <span class="tech-tag">Python</span>
                        <span class="tech-tag">Pandas</span>
                        <span class="tech-tag">SQL</span>
                    </div>
                    <div class="internship-meta">
                        <span><i class="fa-regular fa-clock"></i> 3 Months</span>
                        <span>Posted 5 days ago</span>
                    </div>
                </div>
                <div class="bookmark"><i class="fa-regular fa-bookmark"></i></div>
            </div>

            <!-- Card 3 -->
            <div class="internship-card">
                <div class="company-logo" style="background:#e0e7ff; color:#4338ca;">NX</div>
                <div class="internship-info">
                    <h3 class="internship-title">UI/UX Product Design Intern</h3>
                    <div class="internship-company">NextGen Innovations</div>
                    <div class="internship-industry"><i class="fa-solid fa-location-dot"></i> Kandy, Sri Lanka</div>
                    <div class="tech-tags">
                        <span class="tech-tag">Figma</span>
                        <span class="tech-tag">Prototyping</span>
                    </div>
                    <div class="internship-meta">
                        <span><i class="fa-regular fa-clock"></i> 6 Months</span>
                        <span>Posted 1 week ago</span>
                    </div>
                </div>
                <div class="bookmark"><i class="fa-regular fa-bookmark"></i></div>
            </div>

        </div>

        <!-- Internship Details View (Right Column) -->
        <div class="internship-details">
            <div class="details-hero">
                <img src="../Assets/Images/landing.jpeg" alt="Company Banner">
                <div class="details-company-logo">
                    <strong style="font-size:24px; color:#0056b3;">TC</strong>
                </div>
            </div>

            <div class="details-content">
                <div class="details-header">
                    <div class="details-title">
                        <h2>Frontend Developer Intern</h2>
                        <p>TechCorp Solutions • Colombo (Hybrid)</p>
                    </div>
                    <div class="details-actions">
                        <button class="btn-apply" onclick="window.location.href='../Auth/login.php'">Apply Now</button>
                    </div>
                </div>

                <div class="stats-grid">
                    <div class="stat-box">
                        <div class="stat-label">Duration</div>
                        <div class="stat-value">6 Months</div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-label">Stipend</div>
                        <div class="stat-value">LKR 45,000 / mo</div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-label">Deadline</div>
                        <div class="stat-value">30 Sep 2026</div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-label">Openings</div>
                        <div class="stat-value">3 Positions</div>
                    </div>
                </div>

                <div class="details-section">
                    <h3>About the Internship</h3>
                    <p>We are seeking a motivated Frontend Developer Intern to join our digital transformation team. You will work alongside senior software engineers to build responsive, accessible web applications using modern web technologies.</p>
                </div>

                <div class="details-section">
                    <h3>Key Responsibilities</h3>
                    <ul class="responsibilities-list">
                        <li>Develop UI components using React.js and maintain web style guides.</li>
                        <li>Collaborate with UI/UX designers to translate Figma mockups into code.</li>
                        <li>Participate in daily agile standups and code reviews.</li>
                        <li>Write clean, modular, and reusable code for front-facing platforms.</li>
                    </ul>
                </div>

                <div class="details-section">
                    <h3>Application & Selection Process</h3>
                    <div class="process-flow">
                        <span class="process-step">SkillBridge Profile Review</span>
                        <i class="fa-solid fa-arrow-right process-arrow"></i>
                        <span class="process-step">Online Coding Assessment</span>
                        <i class="fa-solid fa-arrow-right process-arrow"></i>
                        <span class="process-step">Technical Interview</span>
                    </div>
                </div>

            </div>
        </div>

    </div>
</div>

<?php 
include '../Includes/footer.php'; 
?>