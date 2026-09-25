<?php
include "../../../Config/db.php";
include "../../../Session/Session.php";

require_role("student");

$id = $_GET['id'] ?? null;

if(!$id){
    die("Invalid Project");
}

$stmt = $conn->prepare("SELECT * FROM projects WHERE id=?");
$stmt->bind_param("i",$id);
$stmt->execute();

$result = $stmt->get_result();
$project = $result->fetch_assoc();

if(!$project){
    die("Project not found");
}

$pageTitle = "Project Details | SkillBridge";

$extra_css = '
<link rel="stylesheet" href="../../../Assets/CSS/Student/dashboard.css">
<link rel="stylesheet" href="../../../Assets/CSS/Student/view_project.css">
';

include "../../../Includes/student_sidebar.php";
include "../../../Includes/dash_header.php";
?>


<main class="content view-project-page">


<div class="breadcrumb">

<a href="projects.php">
Projects
</a>

<span>></span>

<strong>
View Project
</strong>

</div>



<section class="project-header-card">


<div class="project-info">


<div class="project-tags">

<span>
<?= htmlspecialchars($project['category']); ?>
</span>

<span class="active-tag">
Active Recruitment
</span>

</div>


<h1>
<?= htmlspecialchars($project['title']); ?>
</h1>


<p>
<?= htmlspecialchars($project['description']); ?>
</p>


</div>




<div class="project-buttons">


<a href="proposal.php?project_id=<?= $project['id']; ?>" class="proposal-btn">
    <i class="fa-solid fa-paper-plane"></i>
    Submit Proposal
</a>



<button 
type="button"
class="save-btn"
onclick="saveProject();">

<i class="fa-regular fa-bookmark"></i>

Save Project

</button>


</div>


</section>






<section class="project-stat-wrapper">


<div class="project-stat">


<div class="stat-circle">

<i class="fa-regular fa-clock"></i>

</div>


<div>

<label>
Duration
</label>


<strong>
<?= htmlspecialchars($project['duration']); ?>
</strong>


</div>


</div>





<div class="project-stat">


<div class="stat-circle">

<i class="fa-solid fa-users"></i>

</div>


<div>

<label>
Open Seats
</label>


<strong>
<?= htmlspecialchars($project['members']); ?> Available
</strong>


</div>


</div>


</section>







<div class="project-content-grid">



<div>



<section class="info-card">


<h2>

<i class="fa-solid fa-circle-info"></i>

Project Overview

</h2>



<p>
<?= htmlspecialchars($project['description']); ?>
</p>



<h3>
Key Objectives
</h3>



<ul>

<li>
<?= htmlspecialchars($project['learning_objectives']); ?>
</li>


<li>
<?= htmlspecialchars($project['expected_outcomes']); ?>
</li>


</ul>



</section>






<section class="info-card team-section">


<h2>

<i class="fa-solid fa-people-group"></i>

Team Composition

</h2>



<div class="team-grid">


<div class="team-box">

<span class="status filled">
FILLED
</span>

<h3>
Frontend Architect
</h3>

<p>
Focus on React, Tailwind, and UI integration.
</p>

</div>



<div class="team-box">

<span class="status">
1 OPEN
</span>

<h3>
Backend Engineer
</h3>

<p>
API development and database architecture.
</p>

</div>



<div class="team-box">

<span class="status">
1 OPEN
</span>

<h3>
UI/UX Designer
</h3>

<p>
Figma prototyping and interface design.
</p>

</div>



<div class="team-box">

<span class="status">
1 OPEN
</span>

<h3>
QA Engineer
</h3>

<p>
Testing and documentation.
</p>

</div>


</div>


</section>


</div>







<div>



<section class="info-card">


<h2>

<i class="fa-solid fa-code"></i>

Required Skills

</h2>



<?php

$skills = explode(",", $project['tech_stack']);

foreach($skills as $skill){

?>

<span class="skill-pill">

<?= htmlspecialchars(trim($skill)); ?>

</span>

<?php } ?>


</section>







<section class="organization-card">


<div class="organization-header">


<div class="organization-logo">

<?php

$companyName = $project['company'];

$words = explode(" ", $companyName);

$initials = "";

foreach($words as $word){

    if(!empty($word)){
        $initials .= strtoupper($word[0]);
    }

}

$initials = substr($initials,0,2);

?>

<span>
<?= htmlspecialchars($initials); ?>
</span>


</div>



<div>


<h3>

<?= htmlspecialchars($project['company']); ?>

</h3>


<p>

<i class="fa-solid fa-certificate"></i>

Verified Organization

</p>


</div>


</div>





<p class="organization-description">

"Leading innovative technology solutions through modern development, research, and digital transformation."

</p>





<div class="organization-stats">


<div>

<span>
Projects
</span>


<strong>
12+
</strong>


</div>




<div>

<span>
Hired Students
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
        Project saved successfully!
    </span>

</div>

</main>


<script>

function saveProject(){

    const toast = document.getElementById("toastMessage");


    toast.classList.remove("hide");

    toast.classList.add("show");


    setTimeout(()=>{

        toast.classList.remove("show");

        toast.classList.add("hide");

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

<?php include "../../../Includes/dash_footer.php"; ?>