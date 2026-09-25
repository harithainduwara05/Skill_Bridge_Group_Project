<?php

include "../../../Config/db.php";
include "../../../Session/Session.php";

require_role("student");

$user = current_user();


$pageTitle = "Find Internships | SkillBridge";


$extra_css = '

<link rel="stylesheet" href="../../../Assets/CSS/Student/dashboard.css">

<link rel="stylesheet" href="../../../Assets/CSS/Student/internships.css">

';



include "../../../Includes/student_sidebar.php";
include "../../../Includes/dash_header.php";



/*
 SAMPLE INTERNSHIP DATA

 Replace this query later with internship table

*/


$internships = [

[
"title"=>"Junior Frontend Developer",
"company"=>"Nexus Cloud Systems",
"location"=>"San Francisco (Hybrid)",
"industry"=>"Technology",
"skills"=>[
"React",
"TypeScript",
"Tailwind CSS"
],
"duration"=>"6 Months",
"deadline"=>"July 15, 2026",
"logo"=>"company1.png",
"type"=>"On-Site"
],


[
"title"=>"Product Design Intern",
"company"=>"Prism Creative Agency",
"location"=>"Fully Remote",
"industry"=>"Design",
"skills"=>[
"Figma",
"UX Research",
"Prototyping"
],
"duration"=>"3 Months",
"deadline"=>"Nov 02, 2026",
"logo"=>"company2.png",
"type"=>"Remote"
],



[
"title"=>"Data Science Analyst Intern",
"company"=>"Quantum Ledger",
"location"=>"New York, NY",
"industry"=>"Data Science",
"skills"=>[
"Python",
"SQL",
"PyTorch"
],
"duration"=>"12 Months",
"deadline"=>"Oct 30, 2026",
"logo"=>"company3.png",
"type"=>"Remote"
],


[
"title"=>"Financial Risk Analyst",
"company"=>"Horizon Finance",
"location"=>"Charlotte, NC",
"industry"=>"Finance",
"skills"=>[
"Excel",
"R Programming",
"Forecasting"
],
"duration"=>"6 Months",
"deadline"=>"Dec 20, 2026",
"logo"=>"company4.png",
"type"=>"Hybrid"
]

];


?>



<main class="content internship-page">



<!-- HEADER -->

<section class="internship-header">

<div>

<h1>
Find Internships
</h1>

<p>
Kickstart your career with top-tier opportunities tailored to your skills.
</p>

</div>


<div class="internship-header-actions">

<a href="saved_internships.php" class="btn-secondary">

<i class="fa-regular fa-bookmark"></i>

Saved Internships

</a>

</div>


</section>






<!-- FILTER -->
<section class="internship-filter">


<div class="filter-box">

<label>
Industry
</label>

<select id="industryFilter">

<option value="">
All Industries
</option>

<option>
Software Engineering
</option>

<option>
Artificial Intelligence & Machine Learning
</option>

<option>
Cloud Computing & DevOps
</option>

<option>
Cybersecurity
</option>

<option>
Data Science & Analytics
</option>

</select>

</div>




<div class="filter-box">

<label>
Location
</label>


<select id="locationFilter">

<option value="">
All Locations
</option>

<option>
Remote
</option>

<option>
On-site
</option>

<option>
Hybrid
</option>

</select>

</div>





<div class="filter-box">

<label>
Schedule
</label>


<select id="scheduleFilter">

<option value="">
All Schedules
</option>

<option>
Full Time
</option>

<option>
Part Time
</option>

</select>

</div>





<div class="filter-action">


<button 
class="clear-filter"
onclick="clearFilters()">

<i class="fa-solid fa-filter-circle-xmark"></i>

Clear Filters

</button>


</div>



</section>






<!-- INTERNSHIP GRID -->


<section class="internship-grid">


<?php foreach($internships as $internship): ?>


<div class="internship-card">



<div class="card-top">


<div class="company-logo">

<?php

$companyWords = explode(" ", $internship['company']);

$initials = "";

foreach($companyWords as $word){

    if(!empty($word)){
        $initials .= strtoupper($word[0]);
    }

}

$initials = substr($initials,0,2);

?>

<span>
<?= $initials; ?>
</span>

</div>



<span class="status-badge">

<?= $internship['type']; ?>

</span>



</div>







<h2>

<?= $internship['title']; ?>

</h2>




<p class="company-name">

<?= $internship['company']; ?>

</p>




<p class="location">

<i class="fa-solid fa-location-dot"></i>

<?= $internship['location']; ?>

</p>








<div class="internship-tags">


<?php foreach($internship['skills'] as $skill): ?>

<span>

<?= $skill ?>

</span>


<?php endforeach; ?>


</div>






<div class="internship-info">


<div>

<small>
Duration
</small>


<strong>
<?= $internship['duration']; ?>
</strong>


</div>




<div>

<small>
Deadline
</small>


<strong>
<?= $internship['deadline']; ?>
</strong>


</div>


</div>








<div class="internship-actions">


<a 

href="view_internship.php?id=<?= $internship['id']; ?>"

class="view-details">


<i class="fa-solid fa-eye"></i>

View Details


</a>





<a

href="submit_application.php?id=<?= $internship['id']; ?>"

class="apply-now">


<i class="fa-solid fa-paper-plane"></i>

Apply Now


</a>


</div>
</div>


<?php endforeach; ?>



</section>








<!-- PAGINATION -->


<div class="internship-pagination">


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


<span>
...
</span>



<button>
12
</button>



<button>

<i class="fa-solid fa-chevron-right"></i>

</button>


</div>






</main>
<script>

function clearFilters(){

    document.getElementById("industryFilter").value="";
    document.getElementById("locationFilter").value="";
    document.getElementById("scheduleFilter").value="";

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