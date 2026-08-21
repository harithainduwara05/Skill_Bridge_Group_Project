<?php

error_reporting(E_ALL);
ini_set('display_errors',1);

include "../../../Config/db.php";
include "../../../Session/Session.php";

require_once __DIR__ . "/profile_completion.php";
require_once __DIR__ . "/check_student_access.php";

require_login('student');

$user = current_user();


if(!$user){
    die("Session expired");
}

$email = $user['Email'] ?? $user['email'] ?? null;

if(!$email){
    die("Email not found");
}

// CHANGE PASSWORD

if(isset($_POST['change_password'])){
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if($new_password !== $confirm_password){
        $password_error = "New passwords do not match.";
    }
    elseif(strlen($new_password) < 6){
        $password_error = "Password must contain at least 6 characters.";
    }
    else{
        // Get current password
        $stmt = $conn->prepare(
            "
            SELECT password
            FROM user
            WHERE Email=?
            ");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $userData = $result->fetch_assoc();

        if(!$userData){
            $password_error="User not found.";
        }
        else{
            // Database stores hashed passwords
            if(
                sha1($current_password) == $userData['password']
            ){

                $new_hash = sha1($new_password);
                $updatePassword = $conn->prepare(
                    "
                    UPDATE user
                    SET password=?
                    WHERE Email=?
                    ");
                $updatePassword->bind_param("ss",$new_hash,$email);
                if($updatePassword->execute()){
                    $password_success = "Password updated successfully.";
                }
            }
            else{
                $password_error ="Current password is incorrect.";
            }
        }
    }
}

// SAVE PROFILE

if(isset($_POST['save_changes'])){

    $name = $_POST['name'] ?? '';

    $university = $_POST['university'] ?? '';

    $degree = $_POST['degree'] ?? '';

    $year = $_POST['year'] ?? '';
    if($year < 1 || $year > 4){
        $error = "Invalid year. Only Year 1 - Year 4 students are allowed.";
    }

    $bio = $_POST['bio'] ?? '';

    $github = $_POST['github'] ?? '';

    $linkedin = $_POST['linkedin'] ?? '';

    $website = $_POST['website'] ?? '';

    // Get old image
    $old = $conn->prepare(
    "
    SELECT profile_image
    FROM student
    WHERE Email=?
    ");
    $old->bind_param(
        "s",
        $email
    );
    $old->execute();

    $oldImage = $old->get_result() ->fetch_assoc()['profile_image'];
    $image = $oldImage;

    // REMOVE PROFILE IMAGE

if(isset($_POST['remove_profile_image']) 
    && $_POST['remove_profile_image'] == "1"){

    $folder = __DIR__ ."/../../../Assets/Images/Student/";

    if(
        !empty($oldImage) 
        &&
        file_exists($folder.$oldImage)
    ){
        unlink($folder.$oldImage);
    }
    $image = NULL;
}

    // UPLOAD PROFILE IMAGE

    if(
        isset($_FILES['profile_image'])
        &&
        $_FILES['profile_image']['error']==0
    ){

        $folder = __DIR__ ."/../../../Assets/Images/Student/";

        if(!is_dir($folder)){
            mkdir(
                $folder,0777,true);
        }

        $extension = strtolower(
            pathinfo(
                $_FILES['profile_image']['name'],
                PATHINFO_EXTENSION
            )
        );

        $allowed = ["jpg","jpeg","png","webp"];

        if(!in_array($extension,$allowed)){
            die("Invalid image type");
        }

        $newImage = 
        "profile_"
        .time()
        ."_"
        .uniqid()
        .".".$extension;
        
        $destination = $folder.$newImage;

        if(
            move_uploaded_file(
                $_FILES['profile_image']['tmp_name'],
                $destination
            )
        ){

            // remove old image

            if(
                !empty($oldImage)
                &&
                file_exists(
                    $folder.$oldImage
                )
            ){
                unlink(
                    $folder.$oldImage
                );
            }
            $image = $newImage;
        }
        else{
            die("Image upload failed");
        }
    }

    // UPDATE DATABASE

    $update = $conn->prepare(
    "
    UPDATE student SET
    Name=?,
    University=?,
    degree=?,
    bio=?,
    github=?,
    linkedin=?,
    website=?,
    profile_image=?
    WHERE Email=?
    "
    );

    $update->bind_param(
        "sssssssss",
        $name,
        $university,
        $degree,
        $bio,
        $github,
        $linkedin,
        $website,
        $image,
        $email
    );


    if(!$update->execute()){
        die("Update error: ".$update->error);
    }

    header(
        "Location: settings.php?success=1");
    exit();

}

// GET STUDENT DATA

$stmt = $conn->prepare(
"
SELECT 
    student.*,
    user.status AS user_status,
    user.password
FROM student
INNER JOIN user
ON student.Email = user.Email
WHERE student.Email=?
");

$stmt->bind_param("s",$email);
$stmt->execute();

$student = $stmt->get_result()->fetch_assoc();

if(!$student){
    die("Student profile not found");
}

// COUNTS

// Skills
$q=$conn->prepare(
"
SELECT COUNT(*) total
FROM skills
WHERE Email=?");

$q->bind_param(
"s",
$email);

$q->execute();

$skill_count =
$q->get_result()
->fetch_assoc()['total'];

// Certificates

$q=$conn->prepare(
"
SELECT COUNT(*) total
FROM certificates
WHERE Email=?");

$q->bind_param(
"s",
$email);

$q->execute();

$certificate_count =
$q->get_result()
->fetch_assoc()['total'];

// Projects
// no Email column

$q=$conn->prepare(
"
SELECT COUNT(*) total
FROM projects");

$q->execute();

$project_count =

$q->get_result()
->fetch_assoc()['total'];

$completion = calculateProfileCompletion(
    $student,
    $skill_count,
    $certificate_count,
    $project_count
);

?>
<?php

include "../../../Includes/student_sidebar.php";
include "../../../Includes/dash_header.php";

?>

<!DOCTYPE html>
<html>

<head>
<title>My Profile</title>

<link rel="stylesheet"href="../../../Assets/CSS/Student/settings.css">
<link rel="stylesheet"href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

</head>

<body>

<div class="profile-container">

<form method="POST" enctype="multipart/form-data">
<div class="profile-title">
    <div>
        <h2>Settings > My Profile</h2>
        <p>Manage your academic identity and professional presence.</p>
    </div>
    
    <div class="profile-actions">

        <button type="button" class="share-btn" onclick="shareProfile()">
            <i class="fa-solid fa-share-nodes"></i>
            Share Profile
        </button>

        <button type="submit" name="save_changes" class="save-btn">
            <i class="fa-solid fa-floppy-disk"></i>
            Save Changes
        </button>
    </div>
</div>

<!-- PROFILE CARD -->
<div class="profile-card">
    <div class="avatar">

        <img id="profilePreview"
        src="<?php
        if(!empty($student['profile_image'])){
            echo "../../../Assets/Images/Student/" . $student['profile_image'];
        }
        else{
            echo "../../../Assets/Images/Student/profile.webp";
        }
        ?>">


        <!-- Upload Image -->
        <label class="camera">
            <i class="fa-solid fa-camera"></i>
            <input 
            type="file" 
            name="profile_image" 
            accept="image/*" 
            onchange="previewImage(event)">
        </label>


        <?php if(!empty($student['profile_image'])){ ?>

        <!-- Remove Image -->
        <button 
        type="button"
        class="remove-image"
        onclick="removeProfileImage()">

        <i class="fa-solid fa-trash"></i>
        Remove Photo

        </button>

        <?php } ?>


    </div>

    <div class="profile-details">
        <h2>
            <?= htmlspecialchars($student['Name']); ?>
            <?php if($student['user_status']=="Active"){ ?>
            <span class="verified"> ✔ Verified Student</span>
            <?php } ?>
        </h2>

        <p>
            🎓
            <?= htmlspecialchars($student['degree']); ?>
        </p>

        <p>
            <?= htmlspecialchars($student['University']); ?>
            • Year
            <?= htmlspecialchars($student['year']); ?>
        </p>

        <!-- PROFILE STATS -->
        <div class="profile-stats">
            <div>
                Profile Strength
                <strong>
                    <?= $completion ?>%
                </strong>
            </div>

            <div>
                Projects
                <strong>
                    <?= $project_count ?>
                </strong>
            </div>

            <div>
                Skills
                <strong>
                    <?= $skill_count ?>
                </strong>
            </div>
        </div>
    </div>
</div>

<!-- INFORMATION SECTION -->
<div class="profile-layout">

<!-- PERSONAL INFORMATION -->
<div class="box">

    <h2>Personal Information</h2>
    <label>Full Name</label>

    <input type="text" name="name" value="<?= htmlspecialchars($student['Name']); ?>">
    <label>University</label>

    <input type="text" name="university"
    value="<?= htmlspecialchars($student['University']); ?>">

    <label>Degree</label>
    <input type="text" name="degree" value="<?= htmlspecialchars($student['degree']); ?>">
    <label>Year</label>
    <input type="text" value="<?= htmlspecialchars($student['year']); ?>"readonly>
</div>

<!-- PROFESSIONAL INFORMATION -->
<div class="box">

    <h2>Professional Presence</h2>
    <label>About Me</label>
    <textarea name="bio">
    <?= htmlspecialchars($student['bio'] ?? ""); ?>
    </textarea>

    <label>GitHub</label>
    <input 
        type="text" name="github" value="<?= htmlspecialchars($student['github'] ?? ""); ?>"
        placeholder="https://github.com/username">

    <label>LinkedIn</label>
    <input type="text" name="linkedin" value="<?= htmlspecialchars($student['linkedin'] ?? ""); ?>"
        placeholder="https://linkedin.com/in/username">

    <label>Website</label>
    <input type="text" name="website" value="<?= htmlspecialchars($student['website'] ?? ""); ?>"
        placeholder="https://yourwebsite.com">

</div>
</div>
</form>

<!-- SECURITY & PASSWORD -->
<div class="box security-box">

<h2>Security & Password</h2>

<form method="POST">
<label>Current Password *</label>
<div class="password-field">


<input type="password" name="current_password" placeholder="Enter current password" required>
<i class="fa-solid fa-eye"></i>
</div>

<!-- PASSWORD ROW -->
<div class="password-row">
<div>

<label>New Password *</label>
<div class="password-field">

<input type="password" name="new_password" placeholder="Minimum 6 characters" required>
<i class="fa-solid fa-eye"></i>
</div>
</div>
<div>

<label>Confirm New Password *</label>
<div class="password-field">

<input type="password" name="confirm_password" placeholder="Re-type new password" required>
<i class="fa-solid fa-eye"></i>
</div>
</div>
</div>

<button type="submit" name="change_password" class="password-btn">
<i class="fa-solid fa-key"></i>
Update Password
</button>
</form>
</div>

</div>
<script>

// IMAGE PREVIEW
function previewImage(event){

    let image = document.getElementById(
        "profilePreview"
    );

    let file = event.target.files[0];

    if(file){
        image.src = URL.createObjectURL(file);
    }
}

// SHARE PROFILE

function shareProfile(){

    if(navigator.share){
        navigator.share({
            title:"Student Profile",
            text:"Check my SkillBridge profile",
            url:window.location.href
        });
    }
    else{
        navigator.clipboard.writeText(window.location.href);
        alert("Profile link copied");
    }
}
function togglePassword(icon){
    let input = icon.previousElementSibling;
    if(input.type === "password"){
        input.type="text";
        icon.classList.remove("fa-eye");
        icon.classList.add("fa-eye-slash");
    }
    else{
        input.type="password";
        icon.classList.remove("fa-eye-slash");
        icon.classList.add("fa-eye" );
    }
}
document.querySelectorAll(".password-field i").forEach(icon => {
    icon.addEventListener("click", function(){
        let input = this.parentElement.querySelector("input");
        if(input.type === "password"){
            input.type = "text";
            this.classList.remove("fa-eye");
            this.classList.add("fa-eye-slash");
        }
        else{
            input.type = "password";
            this.classList.remove("fa-eye-slash");
            this.classList.add("fa-eye");
        }
    });
});
function removeProfileImage(){
    if(confirm("Remove current profile image?")){
        document.getElementById("profilePreview").src =
        "../../../Assets/Images/Student/profile.webp";

        // create hidden input
        let input=document.createElement("input");

        input.type="hidden";
        input.name="remove_profile_image";
        input.value="1";
        document.querySelector("form").appendChild(input);
    }
}

</script>

</body>
</html>

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