<?php

include "../../../Config/db.php";
include "../../../Session/Session.php";

require_role("student");

$user=current_user();


$internship=[
"title"=>"Junior Frontend Developer",
"company"=>"Nexus Cloud Systems",
"industry"=>"Software Engineering",
"description"=>"Work with modern frontend technologies to build scalable web applications and collaborate with experienced engineers.",
"duration"=>"6 Months",
"location"=>"Hybrid",
"deadline"=>"July 15, 2026",
"skills"=>[
"React",
"TypeScript",
"Tailwind CSS",
"Git"
],
"responsibilities"=>[
"Develop reusable frontend components",
"Collaborate with UI/UX designers",
"Optimize application performance"
],
"learning"=>[
"Modern frontend architecture",
"Team collaboration workflow",
"Professional software development practices"
]
];


$pageTitle="View Internship | SkillBridge";


$extra_css='
<link rel="stylesheet" href="../../../Assets/CSS/Student/dashboard.css">
<link rel="stylesheet" href="../../../Assets/CSS/Student/view_internship.css">
';


include "../../../Includes/student_sidebar.php";
include "../../../Includes/dash_header.php";

?>


<main class="content view-internship-page">


<div class="breadcrumb">

<a href="internships.php">
Internships
</a>

<span>></span>

<strong>
View Internship
</strong>

</div>



<section class="internship-header-card">


<div class="internship-info">


<div class="internship-tags">

<span>
<?= $internship['industry']; ?>
</span>


<span class="open-tag">
Open Internship
</span>

</div>



<h1>
<?= $internship['title']; ?>
</h1>



<p>
<?= $internship['description']; ?>
</p>


</div>




<div class="internship-buttons">


<a href="submit_application.php" class="apply-btn">

<i class="fa-solid fa-paper-plane"></i>

Apply Now

</a>



<button 
type="button"
class="save-btn"
onclick="saveInternship()">

<i class="fa-regular fa-bookmark"></i>

Save Internship

</button>


</div>



</section>







<section class="internship-stat-wrapper">


<div class="internship-stat">

<div class="stat-circle">

<i class="fa-regular fa-clock"></i>

</div>


<div>

<label>
Duration
</label>

<strong>
<?= $internship['duration']; ?>
</strong>

</div>

</div>




<div class="internship-stat">

<div class="stat-circle">

<i class="fa-solid fa-location-dot"></i>

</div>


<div>

<label>
Work Mode
</label>

<strong>
<?= $internship['location']; ?>
</strong>

</div>

</div>




<div class="internship-stat">

<div class="stat-circle">

<i class="fa-regular fa-calendar"></i>

</div>


<div>

<label>
Deadline
</label>

<strong>
<?= $internship['deadline']; ?>
</strong>

</div>

</div>


</section>








<div class="internship-content-grid">



<div>



<section class="info-card">


<h2>

<i class="fa-solid fa-circle-info"></i>

Internship Overview

</h2>



<p>
<?= $internship['description']; ?>
</p>



<h3>
Responsibilities
</h3>


<ul>

<?php foreach($internship['responsibilities'] as $item): ?>

<li>
<?= $item; ?>
</li>

<?php endforeach; ?>


</ul>



<h3>
Learning Opportunities
</h3>



<ul>

<?php foreach($internship['learning'] as $item): ?>

<li>
<?= $item; ?>
</li>

<?php endforeach; ?>


</ul>



</section>



</div>









<div>



<section class="info-card">


<h2>

<i class="fa-solid fa-code"></i>

Required Skills

</h2>



<?php foreach($internship['skills'] as $skill): ?>

<span class="skill-pill">
<?= $skill; ?>
</span>

<?php endforeach; ?>


</section>







<section class="company-card">


<div class="company-header">


<div class="company-logo">

<span>
NC
</span>

</div>


<div>

<h3>
<?= $internship['company']; ?>
</h3>


<p>

<i class="fa-solid fa-certificate"></i>

Verified Company

</p>


</div>


</div>




<p class="company-description">

"Building innovative digital products through modern technology solutions."

</p>





<div class="company-stats">


<div>

<span>
Active Internships
</span>


<strong>
12+
</strong>


</div>



<div>

<span>
Students Hired
</span>


<strong>
45
</strong>


</div>



</div>



</section>



</div>



</div>

<div id="toastMessage" class="toast-message">

<i class="fa-solid fa-circle-check"></i>

<span>
Internship saved successfully!
</span>

</div>

</main>


<script>

function saveInternship(){

    const toast = document.getElementById("toastMessage");

    toast.classList.add("show");


    setTimeout(()=>{

        toast.classList.remove("show");

    },3000);

}

</script>




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
<?php include "../../../Includes/dash_footer.php"; ?>