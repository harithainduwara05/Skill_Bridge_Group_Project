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


$pageTitle="Notifications | SkillBridge";


$extra_css='
<link rel="stylesheet" href="../../../Assets/CSS/Student/notifications.css">
';


include "../../../Includes/student_sidebar.php";
include "../../../Includes/dash_header.php";

?>


<main class="content notification-page">


<section class="notification-header">


<div>

<h1>
Notification Center
</h1>


<p>
Stay updated with your career progress and platform activities.
</p>


</div>



<div class="notification-actions">


<button class="mark-read">

<i class="fa-solid fa-check-double"></i>

Mark All as Read

</button>



<button class="clear-all">

<i class="fa-regular fa-trash-can"></i>

Clear All

</button>


</div>


</section>





<div class="notification-tabs">


<button class="active">
All
</button>


<button>
Unread
</button>


<button>
Archived
</button>


</div>







<section class="notification-list">





<div class="notification-card invitation">


<div class="notification-icon">

<i class="fa-solid fa-users"></i>

</div>



<div class="notification-content">


<div class="notification-top">


<h3>
Team Invitation: AI Research Cluster
</h3>


<span>
2 mins ago
</span>


</div>


<p>
Sarah Mitchell has invited you to join the "Generative UI Design" sprint team for the upcoming hackathon.
</p>



<div class="notification-buttons">


<button class="accept-btn">
Accept Invite
</button>


<button class="decline-btn">
Decline
</button>


</div>


</div>


</div>







<div class="notification-card update">


<div class="notification-icon">

<i class="fa-solid fa-folder-open"></i>

</div>


<div class="notification-content">


<div class="notification-top">


<h3>
Project Update: SkillBridge Beta
</h3>


<span>
1 hour ago
</span>


</div>


<p>
New assets and design tokens have been pushed to the Global Styles branch. Review the changes in your workspace.
</p>



<div class="tags">

<span>
WORKSPACE
</span>


<span>
HIGH PRIORITY
</span>

</div>



</div>


</div>








<div class="notification-card success">


<div class="notification-icon">

<i class="fa-solid fa-certificate"></i>

</div>


<div class="notification-content">


<div class="notification-top">


<h3>
Identity Verified
</h3>


<span>
5 hours ago
</span>


</div>


<p>
Your student credentials have been verified by SkillBridge. You can now access exclusive premium internships and apply for certified badges.
</p>


</div>


</div>








<div class="notification-card system">


<div class="notification-icon">

<i class="fa-solid fa-mobile-screen"></i>

</div>



<div class="notification-content">


<div class="notification-top">


<h3>
System Release: v2.4.0
</h3>


<span>
Yesterday
</span>


</div>



<p>
We've added a new CV Generator with AI-powered resume parsing. Try the new templates in your dashboard.
</p>



<a href="#">
Read Release Notes ↗
</a>



</div>


</div>








<div class="empty-notification">


<i class="fa-solid fa-clock-rotate-left"></i>


<p>
You have no older notifications from this week.
</p>


</div>





</section>



</main>




<script src="../../../Assets/JS/Student/notifications.js"></script>


<footer class="footer">
    <div>&copy; 2026 SkillBridge. All rights reserved.</div>
    <div class="footer-links">
        <a href="#">Help Center</a>
        <a href="#">Privacy Policy</a>
        <a href="#">Terms of Service</a>
    </div>
</footer>

