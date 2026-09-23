<?php
    error_reporting(E_ALL);
    ini_set('display_errors',1);

    include "../../../Config/db.php";

    $email = $_GET['email'] ?? null;

    if(!$email){
        die("Invalid profile link");
    }

    $stmt = $conn->prepare(
        "SELECT *
        FROM student
        WHERE Email=?
        ");

    $stmt->bind_param("s",$email);
    $stmt->execute();
    $student = $stmt->get_result()->fetch_assoc();
    if(!$student){
        die("Student not found");
    }

    $skills=[];

    $q=$conn->prepare(
        "SELECT *
        FROM skills
        WHERE Email=?");


    $q->bind_param("s",$email);
    $q->execute();
    $result=$q->get_result();

    while($row=$result->fetch_assoc()){
        $skills[]=$row;
    }

    // Future modules
    $projects=[];
    $certificates=[];
?>

<!DOCTYPE html>
<html>

<head>
    <title><?= htmlspecialchars($student['Name']); ?> | SkillBridge</title>
    <link rel="stylesheet" href="../../../Assets/CSS/Student/public_profile.css">
    <link rel="stylesheet"href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body>

    <div class="profile-wrapper">

    <section class="profile-card">
        <div class="profile-top">
        <div class="avatar">

        <?php if(!empty($student['profile_image'])){ ?>
        <img src="../../../Assets/Images/Student/<?= htmlspecialchars($student['profile_image']); ?>">
        <?php }else{ ?>

        <div class="avatar-letter">
        <?= strtoupper(substr($student['Name'],0,1)); ?>
        </div>

        <?php } ?>
        </div>

        <div class="profile-info">
        <h1><?= htmlspecialchars($student['Name']); ?></h1>
        <h3>🎓 <?= htmlspecialchars($student['degree']); ?></h3>
        <p>
        <?= htmlspecialchars($student['University']); ?>
        • Year <?= htmlspecialchars($student['year']); ?>
        </p>

        <p class="headline"><?= htmlspecialchars($student['bio'] ?? "Student at SkillBridge"); ?></p>

        <div class="buttons">
            <button class="message-btn">
                <i class="fa-solid fa-message"></i>Message
            </button>

            <button class="share-btn" onclick="shareProfile()">
                <i class="fa-solid fa-share"></i>
                Share Profile
            </button>
        </div>

        </div>
        </div>
    </section>

    <section class="section">
        <h2>About</h2>
        <p>
        <?= nl2br(htmlspecialchars(
        $student['bio'] ?? 
        "No information added yet.")); ?>
        </p>
    </section>

    <section class="section">
        <div class="section-header">
            <h2>Skills & Expertise</h2>
            <button class="view-btn">View All Skills</button>
        </div>

        <div class="skill-grid">

        <?php foreach($skills as $skill){ ?>
        <div class="skill-card">
            <h3>
                <?= htmlspecialchars(
                $skill['skill_name']
                ); ?>
            </h3>
            <p>
                <?= htmlspecialchars(
                $skill['skill_type'] ?? "Technical Skill"
                ); ?>
            </p>
            <span>
                <?= htmlspecialchars(
                $skill['level'] ?? "Not Specified"
                ); ?>
            </span>
        </div>

        <?php } ?>
        </div>
    </section>

    <section class="section">
        <div class="section-header">
            <h2>Featured Projects</h2>
            <button class="view-btn">Explore Projects</button>
        </div>

        <div class="empty-box">
            <i class="fa-solid fa-folder-open"></i>
            <p>Projects will appear here.</p>
        </div>
    </section>


    <section class="section">

        <h2>Certificates</h2>
        <div class="empty-box">
            <i class="fa-solid fa-certificate"></i>
            <p>No certificates added yet.</p>
        </div>

    </section>

    <section class="section">
        <h2>Professional Links</h2>

            <div class="links">
                <?php if(!empty($student['github'])){ ?>

                <a href="<?=htmlspecialchars($student['github']);?>">
                <i class="fa-brands fa-github"></i>Github</a>

                <?php } ?>
                <?php if(!empty($student['linkedin'])){ ?>

                <a href="<?=htmlspecialchars($student['linkedin']);?>">

                <i class="fa-brands fa-linkedin"></i>LinkedIn</a>

                <?php } ?>

                <?php if(!empty($student['website'])){ ?>

                <a href="<?=htmlspecialchars($student['website']);?>">
                    <i class="fa-solid fa-globe"></i>Website
                </a>
                <?php } ?>
            </div>
    </section>
    </div>

    <script>
        function shareProfile(){
            navigator.clipboard.writeText(window.location.href);
            alert("Profile link copied!");
        }
    </script>
</body>
</html>