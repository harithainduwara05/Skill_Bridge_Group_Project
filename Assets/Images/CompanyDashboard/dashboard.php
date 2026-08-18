<?php

require_once "../Session/Session.php";

require_login();

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Company Dashboard | SkillBridge</title>


<link rel="stylesheet" href="../Assets/CSS/company-dashboard.css">


<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">


</head>


<body>


<div class="dashboard">


<?php include "sidebar.php"; ?>


<div class="content">


<?php include "navbar.php"; ?>



<section class="welcome-section">


<div class="welcome-text">

<h1>
Welcome back, ABC Technologies
</h1>

<p>
Here is an overview of your recruitment momentum today.
</p>


</div>




<div class="company-card">


<img src="../Assets/Images/logo.png">


<div>

<h3>
ABC Technologies
</h3>


<p>
📍 San Francisco
</p>


<p>
👥 500-1000 Employees
</p>


</div>


</div>


</section>





<section class="stats">


<div class="stat-card">

<i class="fa-solid fa-briefcase"></i>

<span>
ACTIVE INTERNSHIPS
</span>

<h2>
12
</h2>

</div>




<div class="stat-card">

<i class="fa-solid fa-file"></i>

<span>
TOTAL APPLICATIONS
</span>

<h2>
356
</h2>

</div>





<div class="stat-card">

<i class="fa-solid fa-users"></i>

<span>
SHORTLISTED
</span>

<h2>
48
</h2>

</div>





<div class="stat-card">

<i class="fa-solid fa-calendar"></i>

<span>
INTERVIEWS
</span>

<h2>
15
</h2>

</div>


</section>






<div class="dashboard-grid">



<div class="left">



<div class="panel">


<div class="panel-title">

<h3>
Recent Applications
</h3>

<a href="#">
View All
</a>

</div>




<table>


<tr>

<th>
Candidate
</th>

<th>
University
</th>

<th>
Position
</th>

<th>
Status
</th>

</tr>



<tr>

<td>
Maya Sharma
</td>

<td>
Stanford University
</td>

<td>
Software Engineer
</td>

<td>
<span class="new">
NEW
</span>
</td>


</tr>




<tr>

<td>
David Chen
</td>

<td>
MIT
</td>

<td>
Data Analyst
</td>

<td>
<span class="short">
SHORTLISTED
</span>
</td>


</tr>





<tr>

<td>
Sarah Jenkins
</td>

<td>
UC Berkeley
</td>

<td>
Product Design
</td>

<td>
<span class="selected">
SELECTED
</span>
</td>


</tr>



</table>



</div>





<div class="panel">


<h3>
Recent Internships
</h3>



<div class="job-card">


<h4>
Full Stack Developer
</h4>


<p>
Building next generation enterprise tools.
</p>


<button>
View
</button>


</div>


</div>




</div>









<div class="right">


<div class="panel">


<h3>
Upcoming Interviews
</h3>



<div class="interview">

<strong>
24 OCT
</strong>

<br>

Maya Sharma

<br>

10:00 AM

</div>




<div class="interview">

<strong>
25 OCT
</strong>

<br>

David Chen

<br>

02:30 PM

</div>



</div>





<div class="goal">


<h3>
Hiring Goal
</h3>


<p>
You are 65% towards your quarterly intake goal.
</p>



<div class="progress">

<div></div>

</div>



<button>
Boost Listings
</button>



</div>



</div>




</div>



</div>


</div>




<script src="../Assets/JS/company_dashboard.js"></script>


</body>

</html>