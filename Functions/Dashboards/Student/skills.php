<?php

error_reporting(E_ALL);
ini_set('display_errors',1);


include "../../../Config/db.php";
include "../../../Session/Session.php";

require_once __DIR__ . "/../../../Backend/StudentBackend.php";
require_role("student");

$user = current_user();


if(!$user){

    die("Session expired");

}



$email = $user['Email'] ?? $user['email'] ?? null;


if(!$email){

    die("Email not found");

}


// ===============================
// ADD NEW SKILL
// ===============================

if(isset($_POST['save_skill'])){


    $skill_name = trim($_POST['skill_name']);

    $level = $_POST['level'];

    $experience = $_POST['experience'];



    // ===============================
    // CHECK DUPLICATE SKILL
    // ===============================


    $check = $conn->prepare("

        SELECT skill_id

        FROM skills

        WHERE Email = ?

        AND LOWER(skill_name) = LOWER(?)

        LIMIT 1

    ");



    $check->bind_param(

        "ss",

        $email,

        $skill_name

    );



    $check->execute();



    $result = $check->get_result();



    if($result->num_rows > 0){


        echo "

        <script>

        alert('You already added this skill.');

        window.location.href='skills.php';

        </script>

        ";


        exit();

    }




    // ===============================
    // INSERT NEW SKILL
    // ===============================


    $sql = "

    INSERT INTO skills

    (
        Email,
        skill_name,
        level,
        experience
    )

    VALUES

    (?,?,?,?)

    ";



    $stmt = $conn->prepare($sql);



    $stmt->bind_param(

        "ssss",

        $email,

        $skill_name,

        $level,

        $experience

    );



    $stmt->execute();



    header("Location: skills.php");

    exit();


}

// ===============================
// DELETE SKILL
// ===============================


if(isset($_POST['delete_skill'])){


    $skill_id = $_POST['skill_id'];



    $sql = "

    DELETE FROM skills

    WHERE skill_id=? AND Email=?

    ";



    $stmt=$conn->prepare($sql);



    $stmt->bind_param(

        "is",

        $skill_id,

        $email

    );



    $stmt->execute();



    header("Location: skills.php");

    exit();


}







$studentManager = new StudentManager();



$student = $studentManager->getStudent($email);



if(!$student){

    die("Student profile not found");

}

// GET UPDATED SKILLS

$skills = $studentManager->getSkills($email);
function getSkillLetters($skill)
{

    $words = explode(" ", $skill);


    if(count($words) >= 2){

        return strtoupper(
            substr($words[0],0,1) .
            substr($words[1],0,1)
        );

    }

    return strtoupper(
        substr($skill,0,2)
    );

}


$extra_css = '
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="../../../Assets/CSS/Student/skills.css">
';

include "../../../Includes/student_sidebar.php";
include "../../../Includes/dash_header.php";
?>



<div class="skills-container">



<!-- Header -->

<div class="page-header">


<div>

<h1>
My Skills
</h1>


<p>
Manage and showcase your professional expertise.
</p>


</div>



<button class="add-btn" onclick="openSkillModal()">

+ Add Skill

</button>



</div>







<!-- Skill Cards -->


<div class="skill-grid">



<?php foreach($skills as $skill){ ?>



<div class="skill-card">

<div class="skill-icon-text">
    <?php echo getSkillLetters($skill['skill_name']); ?>
</div>



<h2>

<?php echo htmlspecialchars($skill['skill_name']); ?>

</h2>




<div class="skill-info">

<span>
Level
</span>


<strong>

<?php echo htmlspecialchars($skill['level']); ?>

</strong>


</div>





<div class="skill-info">


<span>
Experience
</span>



<strong>
<?php 
$exp = htmlspecialchars($skill['experience']); 
echo (stripos($exp, 'year') === false && stripos($exp, 'yr') === false) ? $exp . ' Years' : $exp; 
?>
</strong>


</div>




<form method="POST">


<input 
type="hidden"
name="skill_id"
value="<?php echo $skill['skill_id']; ?>">



<button 
type="submit"
name="delete_skill"
class="delete-btn"
onclick="return confirm('Delete this skill?')">

<i class="fa-solid fa-trash"></i>

</button>


</form>




</div>



<?php } ?>








<!-- Add Card -->


<div class="add-card" onclick="openSkillModal()">



<div class="plus">

+

</div>



<h2>
Add a new skill
</h2>


<p>
Share your latest expertise
</p>



</div>



</div>




</div>








<!-- Add Skill Modal -->


<div id="skillModal" class="skill-modal">



<div class="modal-content">



<div class="modal-header">


<h2>
Add New Skill
</h2>



<span onclick="closeSkillModal()">

×

</span>


</div>





<form method="POST" class="skill-form">



<label>
Skill Name
</label>


<input 
type="text"
name="skill_name"
placeholder="Example: React"
required>






<label>
Level
</label>


<div class="select-wrapper">


<select name="level" required>


<option value="">
Select Level
</option>


<option value="Beginner">
Beginner
</option>


<option value="Intermediate">
Intermediate
</option>


<option value="Advanced">
Advanced
</option>


<option value="Expert">
Expert
</option>


</select>


</div>






<label>
Experience
</label>


<input 
type="text"
name="experience"
placeholder="Example: 2 Years"
required>







<div class="modal-buttons">


<button 
type="button"
class="cancel-btn"
onclick="closeSkillModal()">

Cancel

</button>




<button 
type="submit"
name="save_skill"
class="save-btn">

Save Skill

</button>



</div>



</form>



</div>



</div>







<script>


function openSkillModal(){

    let modal=document.getElementById("skillModal");

    modal.style.display="flex";


    setTimeout(()=>{

        modal.classList.add("show");

    },10);

}





function closeSkillModal(){

    let modal=document.getElementById("skillModal");


    modal.classList.remove("show");


    setTimeout(()=>{

        modal.style.display="none";

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

<?php include "../../../Includes/dash_footer.php"; ?>