<?php

include "../../../Config/db.php";
include "../../../Session/Session.php";

require_once __DIR__ . "/../../../Backend/StudentBackend.php";

require_role("student");

if(!isset($_GET['id'])){

    die("Project not found");

}

$id=$_GET['id'];


$sql="

SELECT *
FROM projects
WHERE id=?
";

$stmt=$conn->prepare($sql);

$stmt->bind_param(
"i",
$id
);

$stmt->execute();

$project=$stmt
->get_result()
->fetch_assoc();

if(!$project){

    die("Project not found");

}

?>


<h1>
<?php echo htmlspecialchars($project['title']); ?>
</h1>

<p>
Company:
<?php echo htmlspecialchars($project['company']); ?>
</p>

<p>
Description:
<?php echo htmlspecialchars($project['description']); ?>
</p>


<a href="projects.php">
Back
</a>