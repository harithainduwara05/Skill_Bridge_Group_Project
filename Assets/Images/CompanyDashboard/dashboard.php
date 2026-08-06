<?php
require_once "../Session/Sessionn.php";

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'company') {
    header("Location: ../Login/login.php");
    exit();
}

$companyName = $_SESSION['user']['username'];
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Company Dashboard | SkillBridge</title>

    <link rel="stylesheet" href="../Assets/CSS/company-dashboard.css">

    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

</head>

<body>

<div class="dashboard">

    <?php include "sidebar.php"; ?>

    <div class="content">

        <?php include "navbar.php"; ?>

        <!-- Welcome Section -->

        <section class="welcome-section">

            <div>

                <h1>
                    Welcome back,
                    <?php echo htmlspecialchars($companyName); ?>
                </h1>

                <p>
                    Here is an overview of your recruitment momentum today.
                </p>

            </div>

            <div class="company-card">

                <img src="../Assets/Images/logo.png" alt="Company Logo">

                <div>

                    <h3>

                        <?php echo htmlspecialchars($companyName); ?>

                        <i class="fa-solid fa-circle-check" style="color:#1b67d9;"></i>

                    </h3>

                    <p>
                        <i class="fa-solid fa-location-dot"></i>
                        San Francisco, CA
                    </p>

                    <p>
                        <i class="fa-solid fa-users"></i>
                        500–1000 Employees
                    </p>

                </div>

            </div>

        </section>

        <!-- Statistics -->

        <section class="stats">

            <div class="stat-card">

                <i class="fa-solid fa-briefcase"></i>

                <p>+2 this week</p>

                <h2>12</h2>

                <span>ACTIVE INTERNSHIPS</span>

            </div>

            <div class="stat-card orange">

                <i class="fa-solid fa-file-lines"></i>

                <p>+42 New</p>

                <h2>356</h2>

                <span>TOTAL APPLICATIONS</span>

            </div>

            <div class="stat-card">

                <i class="fa-solid fa-user-check"></i>

                <p>13% Conversion</p>

                <h2>48</h2>

                <span>SHORTLISTED</span>

            </div>

            <div class="stat-card orange">

                <i class="fa-solid fa-calendar-check"></i>

                <p>3 Today</p>

                <h2>15</h2>

                <span>INTERVIEWS</span>

            </div>

        </section>

        <!-- Main Grid -->

        <div class="dashboard-grid">

            <!-- LEFT -->

            <div class="left">

                <div class="panel">

                    <div class="box-header">

                        <h3>Recent Applications</h3>

                        <a href="#">View All</a>

                    </div>

                    <table>

                        <thead>

                        <tr>

                            <th>Candidate</th>
                            <th>University</th>
                            <th>Position</th>
                            <th>Status</th>
                            <th>Action</th>

                        </tr>

                        </thead>

                        <tbody>

                        <tr>

                            <td>

                                <img src="../Assets/Images/candidates/maya.jpg">

                                Maya Sharma

                            </td>

                            <td>Stanford University</td>

                            <td>Software Engineer</td>

                            <td><span class="new">NEW</span></td>

                            <td><i class="fa-solid fa-ellipsis-vertical"></i></td>

                        </tr>

                        <tr>

                            <td>

                                <img src="../Assets/Images/candidates/david.jpg">

                                David Chen

                            </td>

                            <td>MIT</td>

                            <td>Data Analyst</td>

                            <td><span class="short">SHORTLISTED</span></td>

                            <td><i class="fa-solid fa-ellipsis-vertical"></i></td>

                        </tr>

                        <tr>

                            <td>

                                <img src="../Assets/Images/candidates/sarah.jpg">

                                Sarah Jenkins

                            </td>

                            <td>UC Berkeley</td>

                            <td>Product Design</td>

                            <td><span class="selected">SELECTED</span></td>

                            <td><i class="fa-solid fa-ellipsis-vertical"></i></td>

                        </tr>

                    </table>

                </div>

                <div class="panel">

                    <div class="box-header">

                        <h3>Recent Internships</h3>

                    </div>

                    <div class="job-card">

                        <h4>Full Stack Developer</h4>

                        <p>Building next generation enterprise tools.</p>

                        <button>View</button>

                    </div>

                    <br>

                    <div class="job-card">

                        <h4>Data Science Intern</h4>

                        <p>Analyzing recruitment datasets.</p>

                        <button>View</button>

                    </div>

                </div>

            </div>

            <!-- RIGHT -->

            <div class="right">

                <div class="panel">

                    <h3>Upcoming Interviews</h3>

                    <div class="interview">

                        <strong>24 OCT</strong>

                        <br>

                        Maya Sharma

                        <br>

                        10:00 AM

                    </div>

                    <div class="interview">

                        <strong>24 OCT</strong>

                        <br>

                        David Chen

                        <br>

                        02:30 PM

                    </div>

                    <div class="interview">

                        <strong>25 OCT</strong>

                        <br>

                        Sarah Jenkins

                        <br>

                        09:00 AM

                    </div>

                </div>

                <div class="goal">

                    <h3>Hiring Goal</h3>

                    <p>

                        You are 65% towards your quarterly hiring goal.

                    </p>

                    <div class="progress">

                        <div style="width:65%;"></div>

                    </div>

                    <div class="goal-info">

                        <span>13 Hired</span>

                        <span>20 Goal</span>

                    </div>

                    <button>

                        Boost Listings

                    </button>

                </div>

            </div>

        </div>

        <footer class="dashboard-footer">

            <p>

                © 2026 SkillBridge. All rights reserved.

            </p>

        </footer>

    </div>

</div>

<script src="../Assets/JS/company-dashboard.js"></script>

</body>
</html>