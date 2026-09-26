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

$pageTitle="Team Workspace | SkillBridge";

$extra_css='
<link rel="stylesheet" href="../../../Assets/CSS/Student/team_workspace.css">
';

include "../../../Includes/student_sidebar.php";
include "../../../Includes/dash_header.php";

?>

<main class="content workspace-page">

<section class="workspace-header">

<div>

<span class="sprint">
<i class="fa-solid fa-rocket"></i>
ACTIVE SPRINT 04
</span>

<h1>
Modern E-commerce Redesign
</h1>

<div class="team-members">

<img src="../../../Assets/Images/Profile/default.png">
<img src="../../../Assets/Images/Profile/default.png">
<img src="../../../Assets/Images/Profile/default.png">

<span>
+5
</span>

<p>
Team Collaboration
</p>

</div>

</div>


<div class="progress-card">

<h3>
Overall Progress
</h3>

<div class="progress-value">
68%
</div>

<div class="progress-bar">
<div></div>
</div>

<div class="progress-info">

<span>
12 TASKS DONE
</span>

<span>
4 PENDING
</span>

</div>

</div>

</section>



<section class="workspace-layout">


<div class="workspace-main">


<div class="kanban-header">

<h2>
<i class="fa-solid fa-table-columns"></i>
Kanban Board
</h2>


<div>

<button class="workspace-filter-btn">
Filter
</button>

<button class="workspace-new-task-btn">
+ New Task
</button>

</div>

</div>




<div class="kanban-board">


<div class="kanban-column">

<h3>
TO DO
<span>3</span>
</h3>


<div class="task-card">

<div class="tag research">
RESEARCH
</div>

<h4>
User Interview Analysis
</h4>

<p>
<i class="fa-regular fa-comment"></i>
12
</p>

</div>


<div class="task-card">

<div class="tag design">
DESIGN
</div>

<h4>
Mobile Navigation Prototyping
</h4>

</div>


</div>





<div class="kanban-column">

<h3>
IN PROGRESS
<span>1</span>
</h3>


<div class="task-card">

<div class="tag ux">
UX UI
</div>

<h4>
Refining Checkout Flow
</h4>


<div class="task-progress"></div>


<small>
Due tomorrow
</small>

</div>


</div>





<div class="kanban-column">

<h3>
REVIEW
<span>2</span>
</h3>


<div class="task-card">

<div class="tag review">
REVIEW
</div>


<h4>
Typography System Audit
</h4>


<p>
figma.com/file/system
</p>


</div>


</div>


</div>






<section class="shared-assets">

<h2>
<i class="fa-regular fa-folder-open"></i>
Shared Assets
</h2>



<div class="asset-layout">


<div class="asset-files">


<div class="asset-header">

<span>
Design Files
</span>

<a>
View All
</a>

</div>



<div class="asset-item">

<i class="fa-regular fa-file"></i>


<div>

<strong>
StyleGuide_V2.pdf
</strong>

<p>
4.2 MB • Added 2h ago
</p>

</div>


<i class="fa-solid fa-download"></i>

</div>





<div class="asset-item">

<i class="fa-regular fa-image"></i>


<div>

<strong>
Hero_Animation.mp4
</strong>

<p>
18.5 MB • Added yesterday
</p>

</div>


<i class="fa-solid fa-download"></i>

</div>


</div>





<div class="upload-box">

<i class="fa-solid fa-cloud-arrow-up"></i>

<h4>
Upload New Assets
</h4>

<p>
Drag and drop files here or click browse.
</p>

</div>


</div>


</section>


</div>






<aside class="discussion-panel">


<div class="discussion-header">

<h2>
Discussion
</h2>

<span>
3 Online
</span>

</div>



<div class="messages">


<div class="message other">

<strong>
Sarah Miller
</strong>

<small>
10:45 AM
</small>

<p>
Hey team! Just uploaded the new typography audit.
</p>

</div>



<div class="message mine">

<p>
Great, checking it out now!
</p>

</div>



<div class="message other">

<strong>
David Chen
</strong>

<small>
11:02 AM
</small>

<p>
The mobile nav looks tight on smaller screens.
</p>

</div>


</div>





<div class="workspace-chat-input">

<input placeholder="Type a message...">

<button class="workspace-send-btn">

<i class="fa-solid fa-paper-plane"></i>

</button>

</div>


</aside>


</section>



</main>


<script src="../../../Assets/JS/Student/team_workspace.js"></script>

<footer class="footer">
    <div>&copy; 2026 SkillBridge. All rights reserved.</div>
    <div class="footer-links">
        <a href="#">Help Center</a>
        <a href="#">Privacy Policy</a>
        <a href="#">Terms of Service</a>
    </div>
</footer>

<?php include "../../../Includes/dash_footer.php"; ?>