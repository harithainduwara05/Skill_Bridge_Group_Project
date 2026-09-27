<?php

error_reporting(E_ALL);
ini_set('display_errors',1);

include "../../../Config/db.php";
include "../../../Session/Session.php";

require_once __DIR__ . "/../../../Backend/StudentBackend.php";

require_role("student");

$user=current_user();

if(!$user){
    die("Session expired");
}


$pageTitle="My Teams | SkillBridge";


$extra_css='
<link rel="stylesheet" href="../../../Assets/CSS/Student/teams.css">
';


include "../../../Includes/student_sidebar.php";
include "../../../Includes/dash_header.php";

?>

<main class="content teams-page">


<section class="teams-header">


<div>

<h1>
My Teams
</h1>

<p>
Collaborate with peers and manage your projects.
</p>

</div>



<div class="team-actions-top">


<button class="team-secondary-btn">

<i class="fa-solid fa-compass"></i>

Discover Teams

</button>



</div>


</section>





<section class="team-stat-grid">


<div class="team-stat-card">

<div class="stat-icon">

<i class="fa-solid fa-users"></i>

</div>


<div>

<h3>
3
</h3>

<p>
All Teams
</p>

</div>


</div>

<div class="team-stat-card">

<div class="stat-icon green">

<i class="fa-solid fa-user-group"></i>

</div>


<div>

<h3>
14
</h3>

<p>
Active Teams
</p>

</div>


</div>

<div class="team-stat-card">

<div class="stat-icon orange">

<i class="fa-solid fa-envelope"></i>

</div>


<div>

<h3>
1
</h3>

<p>
Pending Invitation
</p>

</div>


</div>


</section>








<section class="invitation-section">


<h2>
Team Invitations
</h2>



<div class="invitation-card">


<div class="team-logo">

QL

</div>



<div class="invitation-content">


<div class="invitation-title">


<h3>
Quantum Leap Labs
</h3>


<span>
NEW INVITATION
</span>


</div>



<p>
AI based learning analytics platform development.
</p>



<div class="skill-tags">


<span>
Python
</span>

<span>
Machine Learning
</span>

<span>
React
</span>


</div>



<div class="invitation-footer">


<div class="inviter">

<div class="avatar">
    SC
</div>

<p>
Invited by Sarah Chen
</p>

</div>



<div>


<button class="accept-btn">
Accept
</button>


<button class="decline-btn">
Decline
</button>


</div>


</div>


</div>


</div>


</section>









<section class="active-team-section">


<div class="section-title">


<h2>
Active Team Workspaces
</h2>


</div>





<div class="teams-grid">





<div class="student-team-card">


<div class="card-top">


<div class="team-logo">

NA

</div>



<span class="status-track">

ON TRACK

</span>


</div>





<h3>
Nexus AI Solutions
</h3>


<p class="project-name">

Predictive Analytics Dashboard

</p>





<div class="leader">


<img src="../../../Assets/Images/Profile/default.png">


<div>

<strong>
Sarah Chen
</strong>


<p>
Team Leader

</p>


</div>


</div>






<div class="members">


<div>

<img src="../../../Assets/Images/Profile/default.png">

<img src="../../../Assets/Images/Profile/default.png">

<img src="../../../Assets/Images/Profile/default.png">


<span>
+2
</span>

</div>


<p>
5 Members
</p>


</div>







<div class="skills">


<span>
React
</span>

<span>
Python
</span>

<span>
ML
</span>

</div>







<div class="progress-area">


<div class="progress-label">


<span>
Project Progress
</span>


<strong>
68%
</strong>


</div>



<div class="progress-bar">


<div style="width:68%"></div>


</div>


</div>






<div class="deadline">


<i class="fa-regular fa-calendar"></i>


Deadline:
<strong>
Oct 20, 2026
</strong>


</div>






<div class="card-buttons">


<a href="view_team.php?id=1">

View Team

</a>


<a href="team_workspace.php?id=1">

Workspace

</a>


</div>




</div>









<div class="student-team-card">


<div class="card-top">


<div class="team-logo">

UC

</div>



<span class="status-track">

ON TRACK

</span>


</div>





<h3>
Urban Canvas
</h3>


<p class="project-name">

Smart City Mobility App

</p>





<div class="leader">


<img src="../../../Assets/Images/Profile/default.png">


<div>

<strong>
David Lee
</strong>


<p>
Team Leader

</p>


</div>


</div>





<div class="members">


<div>

<img src="../../../Assets/Images/Profile/default.png">

<img src="../../../Assets/Images/Profile/default.png">


<span>
+1
</span>


</div>


<p>
3 Members
</p>


</div>







<div class="skills">


<span>
Flutter
</span>

<span>
UI Design
</span>

</div>







<div class="progress-area">


<div class="progress-label">


<span>
Project Progress
</span>


<strong>
42%
</strong>


</div>



<div class="progress-bar">


<div style="width:42%"></div>


</div>


</div>





<div class="deadline">


<i class="fa-regular fa-calendar"></i>


Deadline:
<strong>
Nov 05, 2026
</strong>


</div>





<div class="card-buttons">


<a href="view_team.php?id=2">

View Team

</a>


<a href="team_workspace.php?id=2">

Workspace

</a>


</div>




</div>









<div class="student-team-card">


<div class="card-top">


<div class="team-logo">

MT

</div>



<span class="status-complete">

COMPLETED

</span>


</div>





<h3>
MediTrack Systems
</h3>


<p class="project-name">

Healthcare Monitoring Portal

</p>





<div class="leader">


<img src="../../../Assets/Images/Profile/default.png">


<div>

<strong>
Alex Wong
</strong>


<p>
Team Leader

</p>


</div>


</div>






<div class="members">


<div>

<img src="../../../Assets/Images/Profile/default.png">


<span>
+4
</span>


</div>


<p>
6 Members
</p>


</div>






<div class="skills">


<span>
PHP
</span>

<span>
Database
</span>

<span>
API
</span>


</div>





<div class="progress-area">


<div class="progress-label">


<span>
Project Progress
</span>


<strong>
100%
</strong>


</div>



<div class="progress-bar">


<div style="width:100%"></div>


</div>


</div>







<div class="deadline">


<i class="fa-regular fa-calendar"></i>


Completed:
<strong>
Sep 12, 2026
</strong>


</div>






<div class="card-buttons">


<a href="view_team.php?id=3">

View Team

</a>


<a href="team_workspace.php?id=3">

Workspace

</a>


</div>



</div>






</div>


</section>





</main>


<script src="../../../Assets/JS/Student/teams.js"></script>


<footer class="footer">
    <div>&copy; 2026 SkillBridge. All rights reserved.</div>
    <div class="footer-links">
        <a href="#">Help Center</a>
        <a href="#">Privacy Policy</a>
        <a href="#">Terms of Service</a>
    </div>
</footer>

<?php include "../../../Includes/dash_footer.php"; ?>