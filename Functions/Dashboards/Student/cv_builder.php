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

$email=$user['Email'] ?? $user['email'] ?? '';

if(!$email){
    die("Student email not found");
}



$student=[
    "Name"=>"",
    "Email"=>$email,
    "profile_image"=>"default.png",
    "bio"=>"",
    "github"=>"",
    "linkedin"=>"",
    "website"=>"",
    "University"=>"",
    "degree"=>"",
    "year"=>""
];



$stmt=$conn->prepare("
    SELECT
        Email,
        Name,
        profile_image,
        bio,
        github,
        linkedin,
        website,
        University,
        degree,
        year
    FROM student
    WHERE Email=?
");

$stmt->bind_param("s",$email);
$stmt->execute();

$result=$stmt->get_result();

if($result->num_rows>0){
    $student=$result->fetch_assoc();
}



$skills=[];

$skillStmt=$conn->prepare("
    SELECT
        skill_name,
        category,
        level,
        percentage
    FROM skills
    WHERE Email=?
    ORDER BY percentage DESC
");

$skillStmt->bind_param("s",$email);
$skillStmt->execute();

$skillResult=$skillStmt->get_result();

while($row=$skillResult->fetch_assoc()){
    $skills[]=$row;
}




$projects=[];

$projectStmt=$conn->prepare("
    SELECT
        id,
        title,
        tech_stack,
        description,
        category,
        duration
    FROM projects
    ORDER BY id DESC
    LIMIT 10
");

$projectStmt->execute();

$projectResult=$projectStmt->get_result();

while($row=$projectResult->fetch_assoc()){
    $projects[]=$row;
}



$pageTitle="CV Builder | SkillBridge";

$extra_css='
<link rel="stylesheet" href="../../../Assets/CSS/Student/cv_builder.css">
';



include "../../../Includes/student_sidebar.php";
include "../../../Includes/dash_header.php";

?>


<main class="content cv-builder-page">


<section class="cv-builder-header">

<div>

<h1>
Create Your Professional CV
</h1>

<p>
Customize your CV using your SkillBridge profile.
</p>

</div>


<div class="cv-header-actions">

<button class="save-cv-btn">
<i class="fa-solid fa-floppy-disk"></i>
Save Changes
</button>


<button class="export-cv-btn">
<i class="fa-solid fa-file-pdf"></i>
Export CV
</button>

</div>

</section>
<section class="template-section">

<div class="section-header">

<h2>
Choose CV Template
</h2>

<p>
Select a professional layout for your CV.
</p>

</div>


<div class="template-grid">


<div class="template-card active"
data-template="modern">


<div class="modern-preview">

<div class="preview-left"></div>

<div class="preview-right"></div>

</div>


<h3>
Modern Sidebar
</h3>


<p>
Best for technology and creative profiles.
</p>


</div>




<div class="template-card"
data-template="professional">


<div class="professional-preview">

<div></div>
<div></div>
<div></div>

</div>


<h3>
Professional
</h3>


<p>
Clean corporate CV layout.
</p>


</div>


</div>


</section>






<section class="builder-layout">



<div class="editor-panel">





<section class="builder-card">


<div class="card-title">

<i class="fa-solid fa-user"></i>

<h2>
Personal Details
</h2>

</div>




<div class="profile-upload">


<img
id="profilePreview"
src="<?= !empty($student['profile_image']) 
? '../../../Assets/Images/Profile/'.$student['profile_image']
: '../../../Assets/Images/Profile/default.png'; ?>">



<div>


<input
type="file"
id="profileUpload"
accept="image/*"
hidden>



<button
type="button"
onclick="document.getElementById('profileUpload').click()">

Change Photo

</button>


<p>
Upload your professional photo
</p>


</div>


</div>






<div class="form-grid">


<div>

<label>
Full Name
</label>

<input
type="text"
id="fullName"
value="<?=htmlspecialchars($student['Name']);?>">

</div>





<div>

<label>
Professional Title
</label>

<input
type="text"
id="professionalTitle"
value="<?=htmlspecialchars($student['degree']);?>">

</div>





<div>

<label>
Email
</label>

<input
type="email"
readonly
value="<?=htmlspecialchars($student['Email']);?>">

</div>





<div>

<label>
Phone Number
</label>

<input
type="text"
id="phone"
placeholder="Enter phone number">

</div>





<div>

<label>
Location
</label>

<input
type="text"
id="location"
placeholder="Enter location">

</div>





<div>

<label>
University
</label>

<input
type="text"
value="<?=htmlspecialchars($student['University']);?>">

</div>





<div>

<label>
LinkedIn
</label>

<input
type="text"
id="linkedin"
value="<?=htmlspecialchars($student['linkedin']);?>">

</div>





<div>

<label>
Github
</label>

<input
type="text"
id="github"
value="<?=htmlspecialchars($student['github']);?>">

</div>



</div>


</section>







<section class="builder-card">


<div class="card-title">

<i class="fa-solid fa-code"></i>

<h2>
Skills
</h2>

</div>


<p class="helper-text">
Drag skills that you want to include in your CV.
</p>



<div class="skills-builder">



<div class="available-skills">


<h3>
Available Skills
</h3>



<div class="skill-list">


<?php foreach($skills as $skill): ?>


<div
class="drag-skill"
draggable="true"
data-skill="<?=htmlspecialchars($skill['skill_name']);?>">


<?=htmlspecialchars($skill['skill_name']);?>


</div>


<?php endforeach; ?>


</div>


</div>






<div class="selected-skills">


<h3>
Selected Skills
</h3>


<div
class="skill-drop-zone"
id="skillDropZone">


Drop skills here


</div>


</div>


</div>


</section>








<section class="builder-card">


<div class="card-title">

<i class="fa-solid fa-folder-open"></i>

<h2>
Projects
</h2>

</div>



<p class="helper-text">
Select projects to highlight in your CV.
</p>



<div class="project-list">



<?php foreach($projects as $project): ?>


<div class="project-card">


<div class="project-select">


<input
type="checkbox"
class="project-checkbox"
checked>


<div>


<h3>

<?=htmlspecialchars($project['title']);?>

</h3>


<p>

<?=htmlspecialchars($project['tech_stack']);?>

</p>


<small>

<?=htmlspecialchars($project['duration']);?>

</small>


</div>


</div>


</div>



<?php endforeach; ?>


</div>


</section>








</div>









<div class="preview-panel">


<div class="preview-header">


<h2>
Live CV Preview
</h2>



<div class="zoom-controls">


<button onclick="zoomOut()">
-
</button>


<span id="zoomLevel">
100%
</span>


<button onclick="zoomIn()">
+
</button>


</div>


</div>







<div class="cv-preview-wrapper">



<div
id="cvPreview"
class="cv-document modern">






<div class="cv-sidebar">


<img
id="previewPhoto"
src="<?= !empty($student['profile_image'])
? '../../../Assets/Images/Profile/'.$student['profile_image']
: '../../../Assets/Images/Profile/default.png'; ?>">





<h1 id="previewName">

<?=htmlspecialchars($student['Name']);?>

</h1>




<h3 id="previewTitle">

<?=htmlspecialchars($student['degree']);?>

</h3>







<div class="preview-contact">


<h4>
CONTACT
</h4>



<p>
<?=htmlspecialchars($student['Email']);?>
</p>



<p id="previewPhone">

</p>



<p id="previewLocation">

</p>



<p>
<?=htmlspecialchars($student['linkedin']);?>
</p>



<p>
<?=htmlspecialchars($student['github']);?>
</p>


</div>







<div class="preview-skills">


<h4>
SKILLS
</h4>


<div id="previewSkills">

</div>


</div>


</div>
<div class="cv-content">


<h2>
PROFILE
</h2>


<p id="previewSummary">

<?=htmlspecialchars($student['bio']);?>

</p>





<h2>
PROJECTS
</h2>



<div id="previewProjects">


<?php foreach($projects as $project): ?>


<div class="preview-project">


<h3>

<?=htmlspecialchars($project['title']);?>

</h3>


<p>

<?=htmlspecialchars($project['tech_stack']);?>

</p>


</div>


<?php endforeach; ?>


</div>







<h2>
EDUCATION
</h2>


<h3>

<?=htmlspecialchars($student['degree']);?>

</h3>


<p>

<?=htmlspecialchars($student['University']);?>

</p>


<p>

Year <?=htmlspecialchars($student['year']);?>

</p>







<h2>
ONLINE LINKS
</h2>


<p>

<?=htmlspecialchars($student['website']);?>

</p>



</div>


</div>


</div>


</div>


</section>








<div
id="saveToast"
class="save-toast">


<i class="fa-solid fa-circle-check"></i>


<span>
CV saved successfully
</span>


</div>





</main>

<script src="../../../Assets/JS/Student/cv_builder.js"></script>

<footer class="footer">
    <div>&copy; 2026 SkillBridge. All rights reserved.</div>
    <div class="footer-links">
        <a href="#">Help Center</a>
        <a href="#">Privacy Policy</a>
        <a href="#">Terms of Service</a>
    </div>
</footer>

<?php include "../../../Includes/dash_footer.php"; ?>