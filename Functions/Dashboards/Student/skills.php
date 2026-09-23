<?php

error_reporting(E_ALL);
ini_set('display_errors',1);

include "../../../Config/db.php";
include "../../../Session/Session.php";

require_once __DIR__ . "/../../../Backend/StudentBackend.php";
require_once __DIR__ . "/profile_completion.php";
require_role("student");

$user=current_user();


if(!$user)
{
    die("Session expired");
}


$email=$user['Email'] ?? $user['email'] ?? null;


if(!$email)
{
    die("Email not found");
}

$studentManager = new StudentManager();

$skillCount = $studentManager->getSkillCount($email);
$certificateCount = $studentManager->getCertificateCount($email);
$projectCount = 0;

/*
|--------------------------------------------------------------------------
| ADD SKILL
|--------------------------------------------------------------------------
*/
if(isset($_POST['save_skill']))
{
    $skill_name=trim($_POST['skill_name']);
    $category=trim($_POST['category']);
    $level=$_POST['level'];
    $initial_experience=$_POST['initial_experience'];
    $percentage=calculatePercentage($level);

    // Duplicate checking

    $check=$conn->prepare(
    "SELECT skill_id
    FROM skills
    WHERE Email=?
    AND LOWER(skill_name)=LOWER(?)");

    $check->bind_param("ss",$email,$skill_name);
    $check->execute();
    $result=$check->get_result();

    if($result->num_rows>0)
    {
        $_SESSION['skill_error']="You already added this skill.";
        header("Location: skills.php");
        exit();
    }

    $sql="INSERT INTO skills
    (Email,skill_name,category,level,initial_experience,percentage)VALUES(?,?,?,?,?,?)";


    $stmt=$conn->prepare($sql);
    $stmt->bind_param("ssssii",$email,$skill_name,$category,$level,$initial_experience,$percentage);
    $stmt->execute();
    header("Location: skills.php");
    exit();
}

/*
|--------------------------------------------------------------------------
| UPDATE SKILL
|--------------------------------------------------------------------------
*/

if(isset($_POST['update_skill']))
{
    $skill_id=$_POST['skill_id'];

    $skill_name=trim($_POST['skill_name']);
    $category=trim($_POST['category']);
    $level=$_POST['level'];
    $initial_experience=$_POST['initial_experience'];
    $percentage=calculatePercentage($level);

    // Duplicate checking during update

    $check=$conn->prepare(
    " SELECT skill_id
     FROM skills
     WHERE Email=?
     AND LOWER(skill_name)=LOWER(?)
     AND skill_id != ?");

    $check->bind_param("ssi",$email,$skill_name,$skill_id);
    $check->execute();
    $result=$check->get_result();

    if($result->num_rows>0)
    {
        $_SESSION['skill_error']="This skill already exists.";
        header("Location: skills.php");
        exit();
    }

    $sql="UPDATE skills
    SET skill_name=?,category=?,level=?,initial_experience=?,percentage=?
    WHERE skill_id=?
    AND Email=?";

    $stmt=$conn->prepare($sql);
    $stmt->bind_param("sssiiis",$skill_name,$category,$level,$initial_experience,$percentage,$skill_id,$email);
    $stmt->execute();
    header("Location: skills.php");
    exit();

}

/*
|--------------------------------------------------------------------------
| DELETE SKILL
|--------------------------------------------------------------------------
*/

if(isset($_POST['delete_skill']))
{
    $skill_id=$_POST['skill_id'];

    $stmt=$conn->prepare("DELETE FROM skills
    WHERE skill_id=?
    AND Email=?");

    $stmt->bind_param("is",$skill_id,$email);
    $stmt->execute();
    header("Location: skills.php");
    exit();
}

function calculateExperience($initial,$created_at)
{
    $created=new DateTime($created_at);
    $today=new DateTime();
    // Calculate years passed since skill was added
    $years=$created->diff($today)->y;

    // Add initial experience
    $total=$initial+$years;

    // Less than 1 year experience
    if($total <= 0)
    {
        return "1 Year";
    }

    // Singular year
    if($total == 1)
    {
        return "1 Year";
    }

    // Multiple years
    return $total." Years";
}

function calculatePercentage($level)
{
    $levels=["Beginner"=>40,"Intermediate"=>60,"Advanced"=>80,"Expert"=>95];
    return $levels[$level] ?? 0;
}

$student = $studentManager->getStudent($email);
$skills=$studentManager->getSkills($email);
$totalSkills=$studentManager->getSkillCount($email);
$averagePercentage=$studentManager->getAverageSkillPercentage($email);
$profileStrength = calculateProfileCompletion(
    $student,
    $skillCount,
    $certificateCount,
    $projectCount
);
$featuredSkills=$skills;

usort($featuredSkills,function($a,$b){
    return $b['percentage']-$a['percentage'];
});


$featuredSkills=array_slice($featuredSkills,0,3);
$pageTitle="My Skills | SkillBridge";


$extra_css='
<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<link rel="stylesheet"
href="../../../Assets/CSS/Student/skills.css">';


include "../../../Includes/student_sidebar.php";
include "../../../Includes/dash_header.php";
?>

<div class="skills-container">
<!-- HEADER -->
    <div class="skills-header">

        <div>
            <h1>My Skills</h1>
            <p>Showcase your professional expertise and track your skill development.</p>
        </div>

        <button class="add-skill-btn" onclick="openSkillModal()">
        <i class="fa-solid fa-plus"></i>Add Skill
        </button>
    </div>

<!-- STATISTICS -->
<div class="skill-statistics">
<div class="stat-card">
    <div class="stat-icon">
    <i class="fa-solid fa-layer-group"></i>
    </div>

    <div>
        <h2><?php echo $totalSkills; ?></h2>
        <p>Total Skills</p>
    </div>

</div>


<div class="stat-card">
    <div class="stat-icon">
        <i class="fa-solid fa-chart-line"></i>
    </div>
    <div>
        <h2><?php echo $averagePercentage; ?>%</h2>
        <p>Average Proficiency</p>
    </div>

</div>


<div class="stat-card">
    <div class="stat-icon">
        <i class="fa-solid fa-award"></i>
    </div>
    <div>
        <h2><?php echo $profileStrength; ?>%</h2>
        <p>Profile Completion</p>
    </div>
</div>
</div>


<!-- FEATURED SKILLS -->
<div class="section-title">
    <h2>Featured Skills</h2>
</div>

<div class="featured-skills">
    <?php foreach($featuredSkills as $skill): ?>
        <div class="featured-card">
        <div class="skill-top">
        <div class="skill-icon">

    <?php echo strtoupper(substr($skill['skill_name'],0,1)); ?>
</div>

<div>
    <h3><?php echo htmlspecialchars($skill['skill_name']); ?></h3>
    <span><?php echo htmlspecialchars($skill['category']); ?></span>
</div>
</div>

<span class="level-badge"><?php echo htmlspecialchars($skill['level']); ?></span>

<div class="skill-progress">
<div class="progress-header">

<span>Proficiency</span>
<strong><?php echo $skill['percentage']; ?>%</strong>

</div>

<div class="progress-track">
    <div class="progress-fill" style="width:<?php echo $skill['percentage']; ?>%">
    </div>
</div>
</div>

<p class="experience">
<i class="fa-solid fa-clock"></i>
<?php echo calculateExperience($skill['initial_experience'],$skill['created_at']);?>
Experience </p>

</div>

<?php endforeach; ?>
</div>

<!-- ALL SKILLS -->
<div class="section-title">
    <h2>All Skills</h2>
</div>

<div class="skills-grid">

<?php foreach($skills as $skill): ?>
<div class="skill-card">
<div class="card-actions">

<button 
type="button"
class="edit-btn"
onclick='editSkill(
<?= json_encode($skill["skill_id"]) ?>,
<?= json_encode($skill["skill_name"]) ?>,
<?= json_encode($skill["category"]) ?>,
<?= json_encode($skill["level"]) ?>,
<?= json_encode($skill["initial_experience"]) ?>
)'>
<i class="fa-solid fa-pen"></i>
</button>

<form method="POST">

    <input type="hidden"name="skill_id" value="<?php echo $skill['skill_id']; ?>">
    <button type="submit" name="delete_skill" class="delete-btn" 
    onclick="return confirm('Delete this skill?')">
    <i class="fa-solid fa-trash"></i>
    </button>
</form>
</div>

<div class="skill-icon-large">
    <?php echo strtoupper(substr($skill['skill_name'],0,1));?>
</div>

<h3><?php echo htmlspecialchars($skill['skill_name']); ?></h3>
<p class="category"> <?php echo htmlspecialchars($skill['category']); ?></p>

<span class="level-badge">
<?php echo htmlspecialchars($skill['level']); ?>
</span>

<div class="skill-progress">
<div class="progress-header">

<span>Skill Level</span>
<strong><?php echo $skill['percentage']; ?>%</strong>

</div>

<div class="progress-track">
    <div class="progress-fill" style="width:<?php echo $skill['percentage']; ?>%">
    </div>
</div>

</div>

<div class="experience-box">

<i class="fa-solid fa-calendar"></i>

<?php
echo calculateExperience(
$skill['initial_experience'],
$skill['created_at']);?> 

Experience
</div>

<div class="added-date">Added:
    <?php $date=new DateTime($skill['created_at']);
    echo $date->format("d M Y");
    ?>
</div>

</div>

<?php endforeach; ?>
</div>
<!-- ADD / EDIT MODAL -->

<div class="modal" id="skillModal">
<div class="modal-content">


<span class="close" onclick="closeSkillModal()"> &times;</span>
<h2 id="modalTitle">Add Skill</h2>

<form method="POST" id="skillForm">

    <input type="hidden" name="skill_id" id="skill_id">
    <label>Skill Name</label>
    <input type="text" name="skill_name" id="skill_name" placeholder="Example: React" required>


    <label>Category</label>

    <select name="category" id="category" required>
        <option value="">Select Category</option>
        <option value="Programming">Programming</option>
        <option value="Frontend">Frontend</option>
        <option value="Backend">Backend</option>
        <option value="Database">Database</option>
        <option value="Mobile Development">Mobile Development</option>
        <option value="AI & Machine Learning">AI & Machine Learning</option>
        <option value="Cloud & DevOps">Cloud & DevOps</option>
        <option value="Web Design">Web Design</option>
        <option value="Other">Other</option>
    </select>

    <label>Level</label>

    <select name="level" id="level" required>
        <option value="">Select Level</option>
        <option value="Beginner">Beginner</option>
        <option value="Intermediate">Intermediate</option>
        <option value="Advanced">Advanced</option>
        <option value="Expert">Expert</option>
    </select>

    <label>Experience (Years)</label>

    <input type="number" name="initial_experience" id="initial_experience" min="0" required>

    <button type="submit" name="save_skill" id="saveButton" class="save-btn">
    Save Skill
    </button>

</form>

</div>
</div>

<script>
    function openSkillModal()
    {
        document.getElementById("skillModal").style.display="flex";
        document.getElementById("modalTitle").innerHTML="Add Skill";
        document.getElementById("skillForm").reset();
        document.getElementById("skill_id").value="";
        document.getElementById("saveButton").name="save_skill";
        document.getElementById("saveButton").innerHTML="Save Skill";
    }

    function closeSkillModal()
    {
        document.getElementById("skillModal").style.display="none";
    }

    function editSkill(id,name,category,level,experience)
    {
        document.getElementById("skillModal").style.display="flex";
        document.getElementById("modalTitle").innerHTML="Edit Skill";
        document.getElementById("skill_id").value=id;
        document.getElementById("skill_name").value=name;
        document.getElementById("category").value=category;
        document.getElementById("level").value=level;
        document.getElementById("initial_experience").value=experience;
        document.getElementById("saveButton").name="update_skill";
        document.getElementById("saveButton").innerHTML="Update Skill";
    }

    window.onclick=function(event)
    {
        let modal=document.getElementById("skillModal");
        if(event.target==modal)
        {
            modal.style.display="none";
        }
    }

</script>
</div>

<?php if(isset($_SESSION['skill_error'])): ?>
<div id="toastMessage" class="toast-message">
    <i class="fa-solid fa-circle-exclamation"></i>

    <span>
        <?php 
            echo $_SESSION['skill_error'];
            unset($_SESSION['skill_error']);
        ?>
    </span>
</div>

<script>
    setTimeout(function(){
        let toast=document.getElementById("toastMessage");
        if(toast)
        {
            toast.classList.add("hide");
            setTimeout(()=>{
                toast.remove();
            },500);
        }
    },3000);
</script>

<?php endif; ?>


<footer class="footer">
    <div>&copy; 2026 SkillBridge. All rights reserved.</div>
    <div class="footer-links">
        <a href="#">Help Center</a>
        <a href="#">Privacy Policy</a>
        <a href="#">Terms of Service</a>
    </div>
</footer>

<?php include "../../../Includes/dash_footer.php"; ?>