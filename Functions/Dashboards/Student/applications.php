<?php

include "../../../Config/db.php";
include "../../../Session/Session.php";

require_role("student");

$user = current_user();

$pageTitle = "Applications | SkillBridge";


$extra_css = '

<link rel="stylesheet" href="../../../Assets/CSS/Student/dashboard.css">

<link rel="stylesheet" href="../../../Assets/CSS/Student/applications.css">

';


include "../../../Includes/student_sidebar.php";

include "../../../Includes/dash_header.php";



$applications = [

[
"company"=>"Global Tech Solutions",
"position"=>"Product Design Intern",
"location"=>"San Francisco, CA",
"type"=>"Full-time",
"date"=>"Oct 12, 2026",
"status"=>"Accepted"
],


[
"company"=>"Apex Creative Agency",
"position"=>"Junior UI/UX Designer",
"location"=>"Remote",
"type"=>"Contract",
"date"=>"Oct 24, 2026",
"status"=>"Rejected"
],


[
"company"=>"MetaStream Digital",
"position"=>"Interaction Designer",
"location"=>"New York, NY",
"type"=>"Internship",
"date"=>"Oct 28, 2026",
"status"=>"Accepted"
],


[
"company"=>"Nebula Systems",
"position"=>"Front-end Developer",
"location"=>"Austin, TX",
"type"=>"Full-time",
"date"=>"Nov 02, 2026",
"status"=>"Accepted"
],


[
"company"=>"Synthetix Labs",
"position"=>"Junior Researcher",
"location"=>"Seattle, WA",
"type"=>"Contract",
"date"=>"Oct 10, 2026",
"status"=>"Rejected"
]

];

?>


<main class="content applications-page">


<section class="applications-header">


<div>

<h1>
Application Tracking
</h1>


<p>
Monitor your professional journey. Keep track of every internship and job application in real-time as you bridge the gap to your career.
</p>

</div>



<div class="header-actions">


<a href="internships.php" class="new-application">

<i class="fa-solid fa-plus"></i>

New Application

</a>



</div>


</section>





<section class="application-stats">

    <div class="stat-card">
        <div class="stat-icon submitted">
            <i class="fa-solid fa-paper-plane"></i>
        </div>

        <div class="stat-content">
            <span>SUBMITTED</span>
            <strong>24</strong>
        </div>
    </div>


    <div class="stat-card">
        <div class="stat-icon accepted">
            <i class="fa-solid fa-star"></i>
        </div>

        <div class="stat-content">
            <span>ACCEPTED</span>
            <strong>08</strong>
        </div>
    </div>


    <div class="stat-card">
        <div class="stat-icon interview">
            <i class="fa-solid fa-comments"></i>
        </div>

        <div class="stat-content">
            <span>INTERVIEWS</span>
            <strong>03</strong>
        </div>
    </div>


    <div class="stat-card">
        <div class="stat-icon offer">
            <i class="fa-solid fa-circle-check"></i>
        </div>

        <div class="stat-content">
            <span>OFFERS</span>
            <strong>01</strong>
        </div>
    </div>

</section>








<section class="applications-table-card">


<div class="table-header">


<h2>
All Applications
</h2>


<div class="status-filter">

<span>
Filter by Status:
</span>


<select>

<option>
All Statuses
</option>
<option>
Accepted
</option>

<option>
Rejected
</option>

</select>


</div>


</div>






<div class="table-wrapper">


<table>


<thead>

<tr>

<th>
COMPANY
</th>

<th>
POSITION
</th>

<th>
APPLIED DATE
</th>

<th>
STATUS
</th>

<th>
ACTIONS
</th>

</tr>

</thead>



<tbody>


<?php foreach($applications as $application): ?>


<tr>


<td>


<div class="company-cell">


<div class="company-avatar">

<?= strtoupper(substr($application['company'],0,1)); ?>

</div>



<div>

<strong>
<?= $application['company']; ?>
</strong>

<small>
<?= $application['location']; ?>
</small>


</div>


</div>


</td>




<td>


<strong>
<?= $application['position']; ?>
</strong>


<small>
<?= $application['type']; ?>
</small>


</td>





<td>

<?= $application['date']; ?>

</td>





<td>


<span class="status 
<?= strtolower($application['status']); ?>">


<?= $application['status']; ?>


</span>


</td>





<td>

<button class="action-btn">
    <span class="material-symbols-outlined">
        visibility
    </span>
</button>


</td>



</tr>



<?php endforeach; ?>


</tbody>


</table>


</div>



<div class="pagination-area">


<span>
Showing 1-10 of 42 applications
</span>



<div class="pagination">


<button>
<i class="fa-solid fa-chevron-left"></i>
</button>


<button class="active">
1
</button>


<button>
2
</button>


<button>
3
</button>


<button>
<i class="fa-solid fa-chevron-right"></i>
</button>


</div>


</div>



</section>









<section class="application-bottom">


<div class="profile-card">


<h3>
Improve Your Profile
</h3>


<p>

Students with completed portfolios are 
<strong>
3x more likely
</strong>
to move from "Submitted" to "Interview" status.

</p>


<a href="portfolio.php">

Update Portfolio

<i class="fa-solid fa-arrow-right"></i>

</a>


</div>







<div class="interview-card">


<div class="interview-icon">

<i class="fa-solid fa-bolt"></i>

</div>



<h3>
Upcoming Interview
</h3>


<p>

You have a technical screening with

<strong>
Apex Creative Agency
</strong>

tomorrow at 10:00 AM PST.

</p>



<button>

Prepare Now

<i class="fa-regular fa-calendar"></i>

</button>


</div>


</section>



</main>

<footer class="footer">
    <div>&copy; 2026 SkillBridge. All rights reserved.</div>
    <div class="footer-links">
        <a href="#">Help Center</a>
        <a href="#">Privacy Policy</a>
        <a href="#">Terms of Service</a>
    </div>
</footer>

<?php
include "../../../Includes/dash_footer.php";
?>