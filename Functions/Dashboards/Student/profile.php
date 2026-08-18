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


// ======================================
// SAVE PROFILE
// ======================================


if(isset($_POST['save_changes'])){


    $name = $_POST['name'] ?? '';

    $university = $_POST['university'] ?? '';

    $degree = $_POST['degree'] ?? '';

    $year = intval($_POST['year'] ?? 0);
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

    "

    );


    $old->bind_param(
        "s",
        $email
    );


    $old->execute();



    $oldImage =
    $old->get_result()
    ->fetch_assoc()['profile_image'];



    $image = $oldImage;






    // ==================================
    // UPLOAD PROFILE IMAGE
    // ==================================


    if(

        isset($_FILES['profile_image'])

        &&

        $_FILES['profile_image']['error']==0

    ){



        $folder =

        __DIR__

        ."/../../../Assets/Images/Student/";




        if(!is_dir($folder)){


            mkdir(

                $folder,

                0777,

                true

            );


        }




        $extension = strtolower(

            pathinfo(

                $_FILES['profile_image']['name'],

                PATHINFO_EXTENSION

            )

        );





        $allowed = [

            "jpg",
            "jpeg",
            "png",
            "webp"

        ];





        if(!in_array($extension,$allowed)){


            die(
            "Invalid image type"
            );


        }





        $newImage =

        "profile_"

        .time()

        ."_"

        .uniqid()

        .".".$extension;





        $destination =

        $folder.$newImage;







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


            die(
            "Image upload failed"
            );


        }



    }


    // ==================================
    // UPDATE DATABASE
    // ==================================


    $update = $conn->prepare(

    "
    UPDATE student SET

    Name=?,

    University=?,

    degree=?,

    year=?,

    bio=?,

    github=?,

    linkedin=?,

    website=?,

    profile_image=?

    WHERE Email=?

    "

    );


    $update->bind_param(

        "ssssssssss",

        $name,

        $university,

        $degree,

        $year,

        $bio,

        $github,

        $linkedin,

        $website,

        $image,

        $email

    );


    if(!$update->execute()){

        die(
            "Update error: ".$update->error
        );

    }


    header(
        "Location: profile.php?success=1"
    );

    exit();

}

// ======================================
// GET STUDENT DATA
// ======================================
$stmt = $conn->prepare(
"
SELECT 
    student.*,
    user.status AS user_status

FROM student

INNER JOIN user

ON student.Email = user.Email

WHERE student.Email=?
"
);

$stmt->bind_param("s",$email);

$stmt->execute();

$student =
$stmt->get_result()->fetch_assoc();


if(!$student){
    die("Student profile not found");
}




// ======================================
// COUNTS
// ======================================


// Skills


$q=$conn->prepare(

"
SELECT COUNT(*) total

FROM skills

WHERE Email=?

"

);



$q->bind_param(

"s",

$email

);



$q->execute();



$skill_count =

$q->get_result()
->fetch_assoc()['total'];







// Certificates


$q=$conn->prepare(

"
SELECT COUNT(*) total

FROM certificates

WHERE Email=?

"

);



$q->bind_param(

"s",

$email

);



$q->execute();



$certificate_count =

$q->get_result()
->fetch_assoc()['total'];







// Projects
// no Email column


$q=$conn->prepare(

"
SELECT COUNT(*) total

FROM projects

"

);



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
$extra_css = '
<link rel="stylesheet" href="../../../Assets/CSS/Student/profile.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
';

include "../../../Includes/student_sidebar.php";
include "../../../Includes/dash_header.php";
?>


<div class="profile-container">


<form method="POST" enctype="multipart/form-data">


<!-- HEADER -->

<div class="profile-title">


<div>

<h1>
My Profile
</h1>


<p>
Manage your academic identity and professional presence.
</p>


</div>



<div class="profile-actions">


<button

type="button"

class="share-btn"

onclick="shareProfile()">


<i class="fa-solid fa-share-nodes"></i>

Share Profile


</button>





<button

type="submit"

name="save_changes"

class="save-btn">


<i class="fa-solid fa-floppy-disk"></i>

Save Changes


</button>



</div>


</div>







<!-- PROFILE CARD -->


<div class="profile-card">



<div class="avatar">


<img

id="profilePreview"

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

>




<label class="camera">


<i class="fa-solid fa-camera"></i>



<input

type="file"

name="profile_image"

id="imageUpload"

accept="image/*"

onchange="previewImage(event)"

>


</label>



</div>







<div class="profile-details">


<h2>


<?php

echo htmlspecialchars(
$student['Name']
);

?>


<?php if($student['user_status']=="Active"){ ?>

<span class="verified">
    ✔ Verified Student
</span>

<?php } ?>

</h2>






<p>

🎓

<?php

echo htmlspecialchars(
$student['degree']
);

?>

</p>





<p>

<?php

echo htmlspecialchars(
$student['University']
);

?>

•

Year

<?php

echo htmlspecialchars(
$student['year']
);

?>

</p>







<div class="profile-stats">



<div>

Profile Strength

<strong>
<?php echo $completion; ?>%
</strong>


</div>






<div>

Projects

<strong>

<?php

echo $project_count;

?>

</strong>


</div>






<div>

Skills

<strong>

<?php

echo $skill_count;

?>

</strong>


</div>



</div>



</div>



</div>









<!-- INFORMATION AREA -->


<div class="profile-layout">





<!-- LEFT SIDE -->


<div class="box">



<h2>
Personal Information
</h2>





<label>
Full Name
</label>


<input

type="text"

name="name"

value="<?php

echo htmlspecialchars(
$student['Name']
);

?>"

>








<label>
University
</label>


<input

type="text"

name="university"

value="<?php

echo htmlspecialchars(
$student['University']
);

?>"

>








<label>
Degree
</label>


<input

type="text"

name="degree"

value="<?php

echo htmlspecialchars(
$student['degree']
);

?>"

>



<label>
    Year
</label>

<input
    type="number"
    value="<?= htmlspecialchars($student['year']); ?>"
    readonly
>



</div>


<!-- RIGHT SIDE -->


<div>





<div class="box">


<h2>
Professional Presence
</h2>







<label>
About Me
</label>


<textarea name="bio"><?php echo htmlspecialchars(trim($student['bio'] ?? "")); ?></textarea>








<label>
GitHub
</label>


<input

type="text"

name="github"

value="<?php

echo htmlspecialchars(
$student['github'] ?? ""
);

?>"

placeholder="https://github.com/example"

>

<label>
LinkedIn
</label>


<input

type="text"

name="linkedin"

value="<?php

echo htmlspecialchars(
$student['linkedin'] ?? ""
);

?>"

placeholder="https://linkedin.com/in/example"

>


<label>
Website
</label>


<input

type="text"

name="website"

value="<?php

echo htmlspecialchars(
$student['website'] ?? ""
);

?>"

placeholder="https://example.com"

>


</div>


</div>

</div>

</div>






</form>


</div>
<script>


// ============================
// IMAGE PREVIEW
// ============================


function previewImage(event){


    let image = 
    document.getElementById(
        "profilePreview"
    );



    let file =
    event.target.files[0];



    if(file){


        image.src =
        URL.createObjectURL(file);


    }


}






// ============================
// SHARE PROFILE
// ============================


function shareProfile(){


    if(navigator.share){


        navigator.share({

            title:
            "Student Profile",

            text:
            "Check my SkillBridge profile",

            url:
            window.location.href


        });


    }
    else{


        navigator.clipboard.writeText(
            window.location.href
        );


        alert(
        "Profile link copied"
        );


    }


}



</script>







<?php

include "../../../Includes/dash_footer.php";

?>