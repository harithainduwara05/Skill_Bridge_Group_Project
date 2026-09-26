<?php

include "../../../Config/db.php";
include "../../../Session/Session.php";

require_role("student");

$pageTitle = "Saved Projects | SkillBridge";


$extra_css = '
<link rel="stylesheet" href="../../../Assets/CSS/Student/dashboard.css">
<link rel="stylesheet" href="../../../Assets/CSS/Student/projects.css">
<link rel="stylesheet" href="../../../Assets/CSS/Student/saved_projects.css">
';


include "../../../Includes/student_sidebar.php";
include "../../../Includes/dash_header.php";

?>


<main class="content saved-projects-page">


<section class="saved-header">


<div>


<div class="breadcrumb">

<a href="projects.php">
Projects
</a>

<span>
>
</span>

<strong>
Saved Projects
</strong>

</div>



<h1>
Saved Projects
</h1>


<p>
View and manage projects you saved for future applications.
</p>


</div>


<a href="projects.php" class="btn-secondary">

<i class="fa-solid fa-arrow-left"></i>

Explore Projects

</a>


</section>









<section class="saved-grid">





<!-- PROJECT 1 -->

<div class="saved-project-card">


<div class="saved-image">

<img src="../../../Assets/Images/UI/projects/project1.png">


<span>
Intermediate
</span>


</div>




<div class="saved-content">


<small>
SLIIT FOSS Community
</small>


<h2>
FOSS Contributor Leaderboard
</h2>


<p>
Gamified leaderboard tracking pull requests, commits, and reviews of university students contributing to open source repositories.
</p>




<div class="saved-tags">

<span>
Go
</span>

<span>
GraphQL
</span>

<span>
Next.js
</span>

</div>




<div class="saved-actions">


<a href="view_project.php?id=1"
class="btn-outline">

View Details

</a>



<button 
class="remove-btn"
onclick="removeSaved(this)">

<i class="fa-solid fa-bookmark"></i>

Remove

</button>


</div>


</div>


</div>









<!-- PROJECT 2 -->


<div class="saved-project-card">


<div class="saved-image">


<img src="../../../Assets/Images/UI/projects/project2.webp">


<span>
Beginner
</span>


</div>




<div class="saved-content">


<small>
Rotaract Club of UCSC
</small>


<h2>
Open Source Blood Donation Portal
</h2>


<p>
A digital platform connecting blood donors with hospitals during emergency situations.
</p>




<div class="saved-tags">

<span>
PHP
</span>

<span>
MySQL
</span>

<span>
Bootstrap
</span>


</div>




<div class="saved-actions">


<a href="view_project.php?id=2"
class="btn-outline">

View Details

</a>



<button 
class="remove-btn"
onclick="removeSaved(this)">

<i class="fa-solid fa-bookmark"></i>

Remove

</button>


</div>


</div>


</div>









<!-- PROJECT 3 -->


<div class="saved-project-card">


<div class="saved-image">


<img src="../../../Assets/Images/UI/projects/project3.jpg">


<span>
Advanced
</span>


</div>




<div class="saved-content">


<small>
IEEE Student Branch UCSC
</small>


<h2>
Smart Campus Energy Monitor
</h2>


<p>
IoT based energy monitoring system to track and reduce electricity waste.
</p>



<div class="saved-tags">

<span>
ESP32
</span>

<span>
MQTT
</span>

<span>
Node.js
</span>

</div>




<div class="saved-actions">


<a href="view_project.php?id=3"
class="btn-outline">

View Details

</a>



<button 
class="remove-btn"
onclick="removeSaved(this)">

<i class="fa-solid fa-bookmark"></i>

Remove

</button>


</div>


</div>


</div>





</section>



</main>






<script>

function removeSaved(button){

    const card = button.closest(".saved-project-card");


    card.style.opacity="0";


    setTimeout(()=>{

        card.remove();

    },300);


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