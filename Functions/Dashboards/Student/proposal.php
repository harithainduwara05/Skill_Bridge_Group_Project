<?php
include "../../../Config/db.php";
include "../../../Session/Session.php";

require_role("student");

$user = current_user();

$project_id = $_GET['project_id'] ?? null;

if(!$project_id){
    die("Invalid Project");
}

$stmt = $conn->prepare(
    "SELECT *
     FROM projects
     WHERE id=?"
);

$stmt->bind_param("i",$project_id);
$stmt->execute();

$result = $stmt->get_result();

$project = $result->fetch_assoc();

if(!$project){
    die("Project not found");
}

$pageTitle = "Submit Proposal | SkillBridge";

$extra_css = '
<link rel="stylesheet" href="../../../Assets/CSS/Student/dashboard.css">
<link rel="stylesheet" href="../../../Assets/CSS/Student/proposal.css">
';

include "../../../Includes/student_sidebar.php";
include "../../../Includes/dash_header.php";
?>


<main class="proposal-page">


<div class="proposal-header">

<div class="breadcrumb">

<a href="projects.php">
Projects
</a>

<span>></span>

<a href="view_project.php?id=<?= $project['id']; ?>">
View Project
</a>

<span>></span>

<strong>
Submit Proposal
</strong>

</div>


<h1>
New Proposal Submission
</h1>

<p>
Detailed approach and portfolio attachment for this project opportunity.
</p>

</div>





<form 
action="submit_proposal.php"
method="POST"
enctype="multipart/form-data">



<input 
type="hidden"
name="project_id"
value="<?= $project['id']; ?>">






<div class="proposal-layout">



<div class="proposal-left">



<div class="proposal-card">


<label>
Proposal Title
</label>

<input
type="text"
name="proposal_title"
placeholder="e.g. Building an AI Dashboard System"
required>


<small>
A concise name for your application approach.
</small>





<label>
Description
</label>


<textarea
name="description"
rows="5"
placeholder="Briefly introduce yourself and why you're a fit for this project..."
required></textarea>






<label>
Solution Approach
</label>


<textarea
name="solution"
rows="6"
placeholder="Explain your workflow, tools, and methodology..."
required></textarea>



</div>








<div class="bottom-grid">


<div class="mini-card">

<h3>
Technologies Used
</h3>

<div class="skill-tags">

<span>
React
<input type="hidden" name="skills[]" value="React">
</span>

<span>
Javascript
<input type="hidden" name="skills[]" value="Javascript">
</span>

<span>
Python
<input type="hidden" name="skills[]" value="Python">
</span>

</div>


<div class="skill-input">

Add Technology

<b>
+
</b>

</div>


</div>





<div class="mini-card">

<h3>
Role Interested In
</h3>


<select 
name="role"
class="role-select"
required>


<option value="">
Select project role...
</option>


<option value="Frontend Developer">
Frontend Developer
</option>


<option value="Backend Engineer">
Backend Engineer
</option>


<option value="UI/UX Designer">
UI/UX Designer
</option>


<option value="Data Analyst">
Data Analyst
</option>


<option value="AI Engineer">
AI Engineer
</option>


<option value="Project Manager">
Project Manager
</option>


<option value="QA Engineer">
QA Engineer
</option>


</select>



</div>














</div>





</div>









<div class="proposal-right">



<div class="upload-card">


<h2>

<i class="fa-solid fa-paperclip"></i>

Attachments

</h2>


<p>
Upload your project case studies or link your digital portfolio for review.
</p>





<div class="upload-box">


<i class="fa-solid fa-cloud-arrow-up"></i>


<strong>
Attach Portfolio
</strong>


<small>
PDF, ZIP, or Links up to 50MB
</small>



<input 
type="file"
name="portfolio"
accept=".pdf,.zip">



</div>






</div>










<div class="summary-card">


<h3>
Submission Summary
</h3>






<button 
type="submit"
class="submit-btn">


Submit Proposal

<i class="fa-solid fa-paper-plane"></i>


</button>




<small>
By submitting, you agree to our Student Professional Standards.
</small>



</div>






</div>





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