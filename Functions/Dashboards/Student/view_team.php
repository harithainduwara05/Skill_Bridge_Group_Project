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



$pageTitle="View Team | SkillBridge";


$extra_css='
<link rel="stylesheet" href="../../../Assets/CSS/Student/view_team.css">
';



include "../../../Includes/student_sidebar.php";
include "../../../Includes/dash_header.php";


?>


<main class="content view-team-page">

<div class="breadcrumb">

<a href="teams.php">
Teams
</a>

<span>></span>

<strong>
View Team
</strong>

</div>


<section class="team-banner">


<div class="team-banner-left">


<div class="large-team-logo">

NA

</div>



<div>


<span class="team-status">

ON TRACK

</span>


<h1>
Nexus AI Solutions
</h1>


<p>
Predictive Analytics Dashboard
</p>


<div class="team-meta">


<span>

<i class="fa-regular fa-calendar"></i>

Deadline:
<strong>
Oct 20, 2026
</strong>

</span>



<span>

<i class="fa-solid fa-user"></i>

Leader:
<strong>
Sarah Chen
</strong>

</span>


</div>


</div>


</div>







<div class="team-progress-card">


<h3>
Project Progress
</h3>


<strong>
68%
</strong>


<div class="progress-track">

<div style="width:68%"></div>

</div>


<p>
Sprint 04 Active
</p>


</div>



</section>








<nav class="team-tabs">


<button class="active">
Overview
</button>


<button>
Tasks
</button>


<button>
Members
</button>


<button>
Files
</button>


<button>
Discussion
</button>


</nav>








<section class="team-layout">



<div class="team-main">





<div class="detail-card">


<h2>
About Project
</h2>


<p>

A predictive analytics dashboard that helps organizations analyze large datasets and generate meaningful insights using machine learning models.

</p>


</div>







<div class="detail-card">


<h2>
Technology Stack
</h2>


<div class="skill-list">


<span>
React
</span>


<span>
Python
</span>


<span>
Node.js
</span>


<span>
Machine Learning
</span>


<span>
MySQL
</span>


</div>


</div>








<div class="detail-card">


<div class="card-title-row">


<h2>
Current Tasks
</h2>


<a href="team_workspace.php?id=1">

Open Workspace

</a>


</div>





<div class="task-preview">


<div>

<span>
TO DO
</span>

<h4>
User Research Analysis
</h4>

</div>


<div>

<span>
IN PROGRESS
</span>

<h4>
Dashboard Development
</h4>


</div>


<div>

<span>
REVIEW
</span>

<h4>
UI Testing
</h4>


</div>


</div>



</div>







</div>









<aside class="team-sidebar">



<div class="detail-card">


<h2>
Team Members
</h2>



<div class="member-item">


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






<div class="member-item">


<img src="../../../Assets/Images/Profile/default.png">


<div>

<strong>
Alex Wong
</strong>

<p>
Frontend Developer
</p>


</div>


</div>






<div class="member-item">


<img src="../../../Assets/Images/Profile/default.png">


<div>

<strong>
David Lee
</strong>

<p>
Backend Developer
</p>


</div>


</div>



</div>







<div class="detail-card activity-card">


<h2>
Recent Activity
</h2>



<p>
✓ Uploaded new UI design
</p>


<p>
✓ Completed API integration
</p>


<p>
✓ Updated project documentation
</p>



</div>





</aside>






</section>






</main>




<script src="../../../Assets/JS/Student/view_team.js"></script>



<footer class="footer">
    <div>&copy; 2026 SkillBridge. All rights reserved.</div>
    <div class="footer-links">
        <a href="#">Help Center</a>
        <a href="#">Privacy Policy</a>
        <a href="#">Terms of Service</a>
    </div>
</footer>

<?php include "../../../Includes/dash_footer.php"; ?>