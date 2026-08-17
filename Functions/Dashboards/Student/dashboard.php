<?php

error_reporting(E_ALL);
ini_set('display_errors',1);


include "../../../Config/db.php";
include "../../../Session/Session.php";

require_once __DIR__ . "/../../../Backend/StudentBackend.php";
require_once __DIR__ . "/profile_completion.php";
require_once __DIR__ . "/check_student_access.php";


require_login();

$user = current_user();

if(!$user){
    die("Session expired");

}

$email = $user['Email'] ?? $user['email'] ?? null;

if(!validateStudentYear($email)){


    die("
    <h2 style='color:red;text-align:center;margin-top:100px;'>
    Access Denied
    </h2>

    <p style='text-align:center;'>
    Invalid student batch. 
    Only Year 1 - Year 4 students can access SkillBridge.
    </p>
    ");

}

$studentManager = new StudentManager();

// Student details
$student = $studentManager->getStudent($email);
if(!$student){

    die("Student profile not found");

}

// Dashboard counts
$student['skills'] = $studentManager->getSkillCount($email);

$student['certificates'] = $studentManager->getCertificateCount($email);

$student['projects'] = $studentManager->getProjectCount($email);

$student['applications'] = $studentManager->getApplicationCount($email);

// Skills
$skills = array_slice($studentManager->getSkills($email), 0,6);

// Projects
$projects =$studentManager->getProjects($email);

// Internships
include "../../../Functions/Dashboards/Student/check_student_access.php";

if(canApplyInternship($email)){
    $internships =
    $studentManager->getRecommendedInternships();
}
else{
    $internships=[];
}

// Notifications
$notifications = $studentManager->getNotifications($email);

$completion = calculateProfileCompletion($student, $student['skills'],
    $student['certificates'], $student['projects']
);

// Pass specific CSS to the sidebar
$extra_css = '
<link rel="stylesheet" href="../../../Assets/CSS/Student/dashboard.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
';

include "../../../Includes/student_sidebar.php";
include "../../../Includes/dash_header.php";
?>

<div class="container">


<!-- HEADER -->
<div class="dashboard-header">

<div>

<h1>
Welcome back, <?php echo htmlspecialchars($student['Name']); ?>
</h1>

<p>
Continue building your skills and career journey.
</p>

</div>

<a href="projects.php" class="post-btn"> 
<span class="material-symbols-outlined" style="font-size: 18px;">add_circle</span>
Post New Project
</a>
</div>

<!-- PROFILE + STATS -->
<div class="top-section">
<div class="profile-card">

<div class="dashboard-avatar">

<img 
src="<?php

if(!empty($student['profile_image'])){

echo "../../../Assets/Images/Student/"
.$student['profile_image']
."?v=".time();

}
else{

echo "../../../Assets/Images/Student/profile.webp";

}

?>"

onerror="this.src='../../../Assets/Images/Student/profile.webp';"

>

</div>

<div>
<h2>
<?php echo htmlspecialchars($student['Name']); ?>
</h2>

<p>
<?php echo htmlspecialchars($student['degree']); ?>
 •
<?php 
$yearText = htmlspecialchars($student['year']);
echo (stripos($yearText, 'Year') === false) ? "Year " . $yearText : $yearText; 
?>
<br>
<?php echo htmlspecialchars($student['University']); ?>
</p>

<?php if(($student['user_status'] ?? '') == "Active"){ ?>

<span class="verified">
✔ Verified Student
</span>

<?php } ?>

<div class="profile-completion">
<div class="completion-header">

<span>
Profile Completion
</span>

<strong>
<?php echo $completion; ?>%
</strong>

</div>

<div class="progress">
<div class="progress-bar"
style="width:<?php echo $completion; ?>%;">
</div>

</div>

</div>

<div class="profile-buttons">


<a href="profile.php"class="btn">
Complete Profile</a>

<a href="../profile/public_cv.php"class="outline">
View Public CV</a>

</div>

</div>
</div>

<!-- STATISTICS -->
<div class="stats">

<div class="card">
<h1>
<span class="ico">
<span class="material-symbols-outlined">lightbulb</span>
</span>
<?php echo $student['skills']; ?>
</h1>
<p>Skills Added</p>
</div>

<div class="card">
<h1>
<span class="ico">
<span class="material-symbols-outlined">workspace_premium</span>
</span>
<?php echo $student['certificates']; ?>
</h1>
<p>Certificates</p>
</div>

<div class="card">
<h1>
<span class="ico">
<span class="material-symbols-outlined">work</span>
</span>
<?php echo $student['projects']; ?>
</h1>
<p>Projects Done</p>
</div>

<div class="card">
<h1>
<span class="ico">
<span class="material-symbols-outlined">assignment</span>
</span>
<?php echo $student['applications']; ?>
</h1>
<p>Applications</p>
</div>

</div>
</div>

<!-- MIDDLE -->
<div class="middle">

<!-- SKILLS -->

<div class="box">
<div class="skill-header">

<h2>
Skill Development
</h2>

<a href="skills.php"class="manage-skills">Manage Skills →</a>

</div>

<div class="skills">

<?php foreach($skills as $skill){ ?>

<div class="skill-card">
<h5>
<?php echo htmlspecialchars($skill['skill_name']); ?>

</h5>

<p>

<?php echo htmlspecialchars($skill['level']); ?>

</p>

<div class="progress">
<div style="width:

<?php

if($skill['level']=="Expert")
{
echo "95";
}
elseif($skill['level']=="Advanced")
{
echo "85";
}
elseif($skill['level']=="Intermediate")
{
echo "70";
}
else
{
echo "50";
}

?>%">

</div>
</div>

</div>

<?php } ?>

</div>

</div>

<!-- UPDATES -->

<div class="box updates-box">

<div class="updates-header">
    <h2> Updates </h2>
    <a href="notifications.php" class="view-all-btn">View All →</a>
</div>

<?php foreach($notifications as $notification){ ?>
<div class="update-item">
<?php

$type = strtolower(trim($notification['type'] ?? ''));

$title = strtolower($notification['title'] ?? '');


if(
    $type == "application" ||
    str_contains($title,"application")
){

    $iconClass = "blue";
    $icon = "application.png";

}
elseif(
    $type == "project" ||
    str_contains($title,"project")
){

    $iconClass = "green";
    $icon = "projects.svg";

}
elseif(
    $type == "skill" ||
    str_contains($title,"skill")
){

    $iconClass = "orange";
    $icon = "skills.png";

}
else{

    $iconClass = "orange";
    $icon = "notification.png";

}

?>


<div class="update-icon <?php echo $iconClass; ?>">

    <img src="../../../Assets/Images/Icons/<?php echo $icon; ?>">

</div>

<div class="update-content">

<h3>
<?php echo htmlspecialchars($notification['title']); ?>
</h3>

<p>
<?php echo htmlspecialchars($notification['message']); ?>
</p>

<span>
<?php

if(!empty($notification['created_at'])){

    $date = new DateTime($notification['created_at']);

    echo $date->format("d M Y");

}

?>
</span>

</div>

</div>
<?php } ?>
</div> 

</div> 

<!-- Bottom Section -->

<div class="bottom-section">


<!-- Recent Projects -->

<div class="projects-box">


<div class="project-header">

<h2>
Recent Projects
</h2>


<a href="projects.php" class="view-all-btn">
See All Projects
</a>


</div>


<?php foreach($projects as $project){ ?>


<div class="project-item">


    <!-- COLUMN 1 : PROJECT LOGO -->
    <div class="project-logo">

        <?php

        $words = explode(
            " ",
            $project['title']
        );


        echo strtoupper(
            substr($words[0],0,1) .
            substr($words[1] ?? "",0,1)
        );

        ?>

    </div>




    <!-- COLUMN 2 : PROJECT DETAILS -->
    <div class="project-details">


        <h4>
        <?php echo htmlspecialchars(
            $project['title'] ?? ''
        ); ?>
        </h4>



        <p>
        Organization:
        <?php echo htmlspecialchars(
            $project['organization'] ?? 
            $project['company'] ??
            'N/A'
        ); ?>
        </p>



        <p>
        Role:
        <?php echo htmlspecialchars(
            $project['team'] ??
            $project['role'] ??
            'N/A'
        ); ?>
        </p>


    </div>





    <!-- COLUMN 3 : PROGRESS -->

    <div class="progress-column">


        <span>
        Progress
        </span>


        <strong>
        <?php echo $project['progress'] ?? 0; ?>%
        </strong>


    </div>





    <!-- COLUMN 4 : STATUS -->

    <div class="status-column">
        <span>
        Status
        </span>

        <div class="status 
        <?php echo strtolower(
            str_replace(
                ' ',
                '-',
                $project['status'] ?? ''
            )
        ); ?>">


        <?php echo htmlspecialchars(
            $project['status'] ?? ''
        ); ?>


        </div>


    </div>





    <!-- COLUMN 5 : ACTION -->

    <div class="action-column">


        <a 
        href="view_project.php?id=<?php echo $project['id']; ?>"
        class="view-icon"
        title="View Project">


        <i class="fa-solid fa-eye"></i>


        </a>


    </div>



</div>


<?php } ?>
</div>
<!-- Recommended -->

<?php if(canApplyInternship($email)){ ?>

<div class="recommended-box">


<h2>
Recommended For You
</h2>


<?php foreach($internships as $internship){ ?>


<div class="intern-card">


<div class="intern-title">

<h3>
<?php echo htmlspecialchars($internship['title']); ?>
</h3>


<span>
Closes in 2d
</span>

</div>


<p>
<?php echo htmlspecialchars($internship['company']); ?>
</p>


<p>
<?php echo htmlspecialchars($internship['tech_tags']); ?>
</p>


<a href="internships.php" class="apply-btn">
Apply Now
</a>


</div>


<?php } ?>


</div>


<?php } ?>



</div>



</div>

</body>

</html>

<?php include "../../../Includes/dash_footer.php"; ?>