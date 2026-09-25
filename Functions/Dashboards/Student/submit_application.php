<?php

include "../../../Config/db.php";
include "../../../Session/Session.php";

require_role("student");

$user=current_user();


$internship=[

"title"=>"Junior Frontend Developer",

"company"=>"Nexus Cloud Systems",

"skills"=>[
"React",
"TypeScript",
"Tailwind CSS"
]

];


$pageTitle="Submit Application | SkillBridge";


$extra_css='

<link rel="stylesheet" href="../../../Assets/CSS/Student/dashboard.css">

<link rel="stylesheet" href="../../../Assets/CSS/Student/submit_application.css">

';


include "../../../Includes/student_sidebar.php";

include "../../../Includes/dash_header.php";

?>


<main class="application-page">


<div class="application-header">


<div class="breadcrumb">

<a href="internships.php">
Internships
</a>

<span>></span>


<a href="view_internship.php">
View Internship
</a>


<span>></span>


<strong>
Apply Now
</strong>


</div>



<h1>
Internship Application
</h1>


<p>
Complete your application and submit your professional profile.
</p>


</div>





<form 
class="application-layout"
action="#"
method="POST"
enctype="multipart/form-data">






<div class="application-left">





<section class="application-card">


<h2>

<i class="fa-solid fa-user"></i>

Personal Introduction

</h2>



<label>
Application Title
</label>


<input 
type="text"
placeholder="e.g. Frontend Development Internship Application"
required>




<label>
Introduction
</label>


<textarea
rows="5"
placeholder="Introduce yourself and explain why you are interested in this internship."
required></textarea>


</section>







<section class="application-card">


<h2>

<i class="fa-solid fa-briefcase"></i>

Professional Information

</h2>



<label>
Relevant Experience
</label>


<textarea
rows="5"
placeholder="Describe your previous projects, experience, and achievements."
required></textarea>




<label>
Why should we select you?
</label>


<textarea
rows="5"
placeholder="Explain your strengths and contribution."
required></textarea>


</section>







<section class="application-card">


<h2>

<i class="fa-solid fa-code"></i>

Technical Skills

</h2>



<div class="skill-tags">


<?php foreach($internship['skills'] as $skill): ?>

<span>
<?= $skill; ?>
</span>

<?php endforeach; ?>


</div>




<div class="skill-input">

Add Skills

<b>
+
</b>

</div>



</section>





</div>









<div class="application-right">






<section class="upload-card">


<h2>

<i class="fa-solid fa-paperclip"></i>

Resume / Portfolio

</h2>



<p>

Upload your resume or portfolio document for company review.

</p>





<div class="upload-box">


<i class="fa-solid fa-cloud-arrow-up"></i>


<strong>
Attach Resume
</strong>


<small>
PDF up to 10MB
</small>



<input 
type="file"
accept=".pdf">


</div>






<div class="uploaded-file">


<i class="fa-solid fa-file-pdf"></i>


<span>
Lasith_Perera_CV.pdf
</span>


<span>
2.4 MB
</span>



<i class="fa-solid fa-trash"></i>


</div>



</section>








<section class="summary-card">


<h2>
Application Summary
</h2>



<div class="summary-item">

<span>
Company
</span>


<strong>
<?= $internship['company']; ?>
</strong>

</div>





<div class="summary-item">

<span>
Position
</span>


<strong>
<?= $internship['title']; ?>
</strong>

</div>





<ul>


<li>

<i class="fa-solid fa-circle-check"></i>

Profile Completed

</li>



<li>

<i class="fa-solid fa-circle-check"></i>

Skills Added

</li>



<li>

<i class="fa-solid fa-circle-check"></i>

Resume Attached

</li>


</ul>





<button 
type="submit"
class="submit-btn">


Submit Application

<i class="fa-solid fa-paper-plane"></i>


</button>




<small>

By submitting this application, you agree to SkillBridge professional standards.

</small>


</section>






</div>





</form>





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