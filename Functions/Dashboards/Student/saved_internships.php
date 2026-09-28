<?php

include "../../../Config/db.php";
include "../../../Session/Session.php";

require_role("student");

$user=current_user();

$pageTitle="Saved Internships | SkillBridge";


$extra_css='

<link rel="stylesheet" href="../../../Assets/CSS/Student/dashboard.css">

<link rel="stylesheet" href="../../../Assets/CSS/Student/saved_internships.css">

';


include "../../../Includes/student_sidebar.php";
include "../../../Includes/dash_header.php";


$savedInternships=[

[
"id" => 1,
"title"=>"Junior Frontend Developer",
"company"=>"Nexus Cloud Systems",
"location"=>"San Francisco (Hybrid)",
"skills"=>[
"React",
"TypeScript",
"Tailwind CSS"
],
"duration"=>"6 Months",
"deadline"=>"July 15, 2026",
"type"=>"On-Site"
],


[
"id" => 2,
"title"=>"Product Design Intern",
"company"=>"Prism Creative Agency",
"location"=>"Fully Remote",
"skills"=>[
"Figma",
"UX Research",
"Prototyping"
],
"duration"=>"3 Months",
"deadline"=>"Nov 02, 2026",
"type"=>"Remote"
],


[
"id" => 3,
"title"=>"Data Science Analyst Intern",
"company"=>"Quantum Ledger",
"location"=>"New York, NY",
"skills"=>[
"Python",
"SQL",
"PyTorch"
],
"duration"=>"12 Months",
"deadline"=>"Oct 30, 2026",
"type"=>"Remote"
]

];

?>


<main class="content saved-internship-page">


<section class="saved-header">


<div>

<div class="breadcrumb">

<a href="internships.php">
Internships
</a>

<span>></span>

<strong>
Saved Internships
</strong>

</div>


<h1>
Saved Internships
</h1>


<p>
Review internship opportunities you have bookmarked for later.
</p>


</div>


</section>





<section class="saved-grid">


<?php foreach($savedInternships as $internship): ?>


<div class="saved-card">


<div class="saved-card-top">


<div class="company-logo">

<?php

$words=explode(" ",$internship['company']);

$initials="";

foreach($words as $word){

    $initials.=strtoupper($word[0]);

}

echo substr($initials,0,2);

?>

</div>



<span class="status-badge">

<?= $internship['type']; ?>

</span>


</div>





<h2>

<?= $internship['title']; ?>

</h2>



<h3>

<?= $internship['company']; ?>

</h3>




<p class="location">

<i class="fa-solid fa-location-dot"></i>

<?= $internship['location']; ?>

</p>





<div class="skill-tags">


<?php foreach($internship['skills'] as $skill): ?>


<span>
<?= $skill; ?>
</span>


<?php endforeach; ?>


</div>







<div class="saved-info">


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







<div class="saved-actions">


<a href="view_internship.php?id=<?= htmlspecialchars($internship['id'] ?? 1); ?>" class="view-btn">

<i class="fa-solid fa-eye"></i>

View Details

</a>


<a href="submit_application.php?id=<?= htmlspecialchars($internship['id'] ?? 1); ?>" class="apply-btn">

<i class="fa-solid fa-paper-plane"></i>

Apply Now

</a>


</div>




</div>


<?php endforeach; ?>


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