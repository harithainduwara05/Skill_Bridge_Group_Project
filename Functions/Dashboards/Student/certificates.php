<?php

include "../../../Config/db.php";
include "../../../Session/Session.php";


require_role("student");

$user = current_user();


include "../../../Includes/student_sidebar.php";
include "../../../Includes/dash_header.php";

?>

<!DOCTYPE html>
<html>

<head>

<title>Certificates | SkillBridge</title>

<link rel="stylesheet" href="../../../Assets/CSS/Student/dashboard.css">

</head>


<body>


<div class="content">


<h1>
   Certificates
</h1>


</div>


</body>

</html>


<?php include "../../../Includes/dash_footer.php"; ?>