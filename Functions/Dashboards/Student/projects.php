<?php

include "../../../Config/db.php";
include "../../../Session/Session.php";

require_role("student");

$user = current_user();

$pageTitle = "Explore Projects | SkillBridge";

$extra_css = '
<link rel="stylesheet" href="../../../Assets/CSS/Student/dashboard.css">
<link rel="stylesheet" href="../../../Assets/CSS/Student/projects.css">
';

include "../../../Includes/student_sidebar.php";
include "../../../Includes/dash_header.php";


$query = "
SELECT *
FROM projects
WHERE visibility = 'Public'
ORDER BY posted_at DESC
";

$result = $conn->query($query);

?>

<main class="content projects-page">

<section class="projects-header">

    <div>
        <h1>Explore Projects</h1>
        <p>Discover real-world internships and collaborative tech projects from global organizations.</p>
    </div>

    <div class="project-header-actions">
        <a href="save_project.php" class="btn-secondary">
            <i class="fa-solid fa-bookmark"></i>
            Saved Projects
        </a>
    </div>

</section>

<section class="project-stats">

<div class="stat-card">

<div class="stat-icon blue">
    <i class="fa-solid fa-folder-open"></i>
</div>

<div>
    <span>Total Projects</span>
    <h2>12</h2>
</div>

</div>


<div class="stat-card">

<div class="stat-icon orange">
    <i class="fa-solid fa-spinner"></i>
</div>

<div>
    <span>Active Projects</span>
    <h2>2</h2>
</div>

</div>


<div class="stat-card">

<div class="stat-icon green">
    <i class="fa-solid fa-circle-check"></i>
</div>

<div>
    <span>Completed Projects</span>
    <h2>10</h2>
</div>

</div>

</section>


</section>

<div class="project-toolbar">

<div class="filter-group">

<span class="filter-label">
Filters:
</span>


<select class="filter-select" id="categoryFilter">

<option value="">
Category: All
</option>

<option>Web Development</option>
<option>Mobile Development</option>
<option>AI / Machine Learning</option>
<option>Data Science</option>
<option>UI/UX Design</option>
<option>Cloud & DevOps</option>
<option>Cybersecurity</option>
<option>IoT</option>
<option>Blockchain</option>
<option>Robotics</option>

</select>



<select class="filter-select" id="difficultyFilter">

<option value="">
Difficulty: All
</option>

<option>Beginner</option>
<option>Intermediate</option>
<option>Advanced</option>

</select>


</div>


<div class="toolbar-right">



<button class="clear-filter" onclick="clearFilters()">

<i class="fa-solid fa-filter-circle-xmark"></i>

Clear Filters

</button>


</div>


</div>



<section class="projects-grid">


<?php
$projectIndex = 0;
$projectImages = [
    'project1.png',
    'project2.webp',
    'project3.jpg',
    'project4.png',
    'project5.jpg'
];

while($project = $result->fetch_assoc()){
    $projectImage = $projectImages[$projectIndex % count($projectImages)];
    $projectIndex++;
?>


<div class="project-card"
data-category="<?= strtolower($project['category']); ?>"
data-difficulty="<?= strtolower($project['difficulty']); ?>">


<div class="project-image">

<img src="../../../Assets/Images/UI/projects/<?= htmlspecialchars($projectImage) ?>" alt="<?= htmlspecialchars($project['title']) ?>">


<span class="difficulty">

<?= htmlspecialchars($project['difficulty']); ?>

</span>


</div>



<div class="project-content">


<small>

<?= htmlspecialchars($project['company']); ?>

</small>



<h2>

<?= htmlspecialchars($project['title']); ?>

</h2>



<p>

<?= htmlspecialchars(
substr($project['description'],0,120)
); ?>...

</p>



<div class="tech-tags">


<?php

$skills = explode(",", $project['tech_stack']);

foreach($skills as $skill){

?>

<span>
<?= htmlspecialchars(trim($skill)); ?>
</span>

<?php } ?>


</div>



<div class="project-info">


<div>

<i class="fa-regular fa-clock"></i>

<?= htmlspecialchars($project['duration']); ?>

</div>


<div>

<i class="fa-regular fa-calendar"></i>

<?= htmlspecialchars($project['deadline']); ?>

</div>


</div>




<div class="project-actions">


<a 
href="view_project.php?id=<?= $project['id']; ?>"
class="btn-outline">

<i class="fa-solid fa-eye"></i>

View Details

</a>



<a 
href="proposal.php?project_id=<?= $project['id']; ?>"
class="btn-primary">

Apply Now

</a>


</div>


</div>


</div>


<?php } ?>


</section>



<div class="pagination">


<button>

<i class="fa-solid fa-chevron-left"></i>

</button>


<button class="active">
1
</button>


<button>
2
</button>


<button>
3
</button>


<span>
...
</span>


<button>
12
</button>


<button>

<i class="fa-solid fa-chevron-right"></i>

</button>


</div>


</main>



<script>

const categoryFilter = document.getElementById("categoryFilter");
const difficultyFilter = document.getElementById("difficultyFilter");
const projectCards = document.querySelectorAll(".project-card");


function filterProjects(){

    let category = categoryFilter.value.toLowerCase();
    let difficulty = difficultyFilter.value.toLowerCase();


    projectCards.forEach(card => {

        let cardCategory = card.dataset.category?.toLowerCase() || "";
        let cardDifficulty = card.dataset.difficulty?.toLowerCase() || "";


        let categoryMatch = category === "" || cardCategory === category;
        let difficultyMatch = difficulty === "" || cardDifficulty === difficulty;


        if(categoryMatch && difficultyMatch){
            card.style.display="block";
        }
        else{
            card.style.display="none";
        }

    });

}



function clearFilters(){

    categoryFilter.value="";
    difficultyFilter.value="";

    projectCards.forEach(card=>{
        card.style.display="block";
    });

}


categoryFilter.addEventListener("change",filterProjects);
difficultyFilter.addEventListener("change",filterProjects);


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