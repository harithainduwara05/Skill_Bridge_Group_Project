<?php

include "../../../Config/db.php";
include "../../../Session/Session.php";

require_role("student");

$user = current_user();

$pageTitle = "Portfolio | SkillBridge";

$extra_css = '
<link rel="stylesheet" href="../../../Assets/CSS/Student/dashboard.css">
<link rel="stylesheet" href="../../../Assets/CSS/Student/portfolio.css">
';


include "../../../Includes/student_sidebar.php";
include "../../../Includes/dash_header.php";

?>



<main class="content portfolio-page">



<!-- ===========================
     HEADER
=========================== -->


<section class="portfolio-header">


<div>

<h1>
My Portfolio
</h1>


<p>
Showcase your projects, certificates, and professional achievements.
</p>


</div>



<div class="portfolio-buttons">


<button class="btn-project"
onclick="openModal('projectModal')">

+ Add Project

</button>


<button class="btn-certificate"
onclick="openModal('certificateModal')">

+ Add Certificate

</button>


</div>



</section>






<!-- ===========================
     STATS
=========================== -->


<section class="portfolio-stats">


<div class="stat-card">

<div class="stat-icon blue">
    <i class="fa-solid fa-folder-open"></i>
</div>


<div>

<span>
Projects
</span>


<h2>
12
</h2>


<p>
Completed projects
</p>


</div>

</div>





<div class="stat-card">

<div class="stat-icon orange">
    <i class="fa-solid fa-certificate"></i>
</div>
<div>

<span>
Certificates
</span>


<h2>
8
</h2>


<p>
Professional credentials
</p>


</div>


</div>






<div class="stat-card">

<div class="stat-icon green">
    <i class="fa-solid fa-eye"></i>
</div>


<div>

<span>
Profile Views
</span>


<h2>
248
</h2>


<p>
Recruiter visits
</p>


</div>


</div>



</section>









<!-- ===========================
     PROJECT SECTION
=========================== -->


<section class="portfolio-section">


<div class="section-title">

<h2>
All Projects
</h2>




</div>





<div class="project-grid">



<div class="project-card">


<div class="project-image">

<img src="../../../Assets/Images/UI/portfolio/project1.jpeg">

</div>




<div class="project-content">


<h3>
Campus Complaint System
</h3>



<p>
A web-based complaint management platform developed for university students.
</p>



<div class="tags">

<span>
PHP
</span>


<span>
MySQL
</span>


<span>
JavaScript
</span>


</div>

<div class="project-links">



<button 
type="button"
class="edit-btn"
onclick="editItem('Project')"
title="Edit Project">

<i class="fa-solid fa-pen"></i>

</button>



<button 
type="button"
class="delete-btn"
onclick="deleteCard(this)"
title="Delete Project">

<i class="fa-solid fa-trash"></i>

</button>


</div>


</div>


</div>








<div class="project-card">


<div class="project-image">

<img src="../../../Assets/Images/UI/portfolio/project2.jpeg">

</div>




<div class="project-content">


<h3>
Machine Learning Prediction System
</h3>



<p>
Student performance prediction using machine learning algorithms.
</p>



<div class="tags">

<span>
Python
</span>


<span>
TensorFlow
</span>


<span>
AI
</span>


</div>


<div class="project-links">

<button 
type="button"
class="edit-btn"
onclick="editItem('Project')"
title="Edit Project">

<i class="fa-solid fa-pen"></i>

</button>



<button 
type="button"
class="delete-btn"
onclick="deleteCard(this)"
title="Delete Project">

<i class="fa-solid fa-trash"></i>

</button>


</div>


</div>


</div>





</div>

<div class="portfolio-pagination">

    <button class="page-arrow">
        <i class="fa-solid fa-chevron-left"></i>
    </button>


    <button class="page-number active">
        1
    </button>


    <button class="page-number">
        2
    </button>


    <button class="page-number">
        3
    </button>


    <span class="dots">
        ...
    </span>


    <button class="page-number">
        12
    </button>


    <button class="page-arrow">
        <i class="fa-solid fa-chevron-right"></i>
    </button>

</div>
</section>









<!-- ===========================
     CERTIFICATE SECTION
=========================== -->


<section class="portfolio-section">


<div class="section-title">


<h2>
Licenses & Certifications
</h2>





</div>





<div class="certificate-grid">



<div class="certificate-card">


<div class="certificate-image">

<img 
src="../../../Assets/Images/UI/certificates/certificate1.png"
onclick="viewCertificateFile(
'../../../Assets/Images/UI/certificates/certificate1.png'
)"
>
</div>



<div class="certificate-content">


<h3>
AWS Certified Cloud Practitioner
</h3>



<p>
Amazon Web Services
</p>



<div class="certificate-info">

<span>
Issued: Jan 2025
</span>


<span>
ID: AWS123456
</span>


</div>

<div class="certificate-actions">




<button 
type="button"
class="edit-btn"
onclick="editItem('AWS Certificate')"
title="Edit Certificate">

<i class="fa-solid fa-pen"></i>

</button>



<button 
type="button"
class="delete-btn"
onclick="deleteCard(this)"
title="Delete Certificate">

<i class="fa-solid fa-trash"></i>

</button>


</div>

</div>


</div>






<div class="certificate-card">


<div class="certificate-image">


<img 
src="../../../Assets/Images/UI/certificates/certificate2.jpeg"
onclick="viewCertificateFile(
'../../../Assets/Images/UI/certificates/certificate2.jpeg'
)"
>

</div>

<div class="certificate-content">


<h3>
Full Stack Development
</h3>



<p>
Udemy Academy
</p>



<div class="certificate-info">

<span>
Issued: Aug 2024
</span>


<span>
ID: UD56789
</span>


</div>

<div class="certificate-actions">




<button 
type="button"
class="edit-btn"
onclick="editItem('AWS Certificate')"
title="Edit Certificate">

<i class="fa-solid fa-pen"></i>

</button>



<button 
type="button"
class="delete-btn"
onclick="deleteCard(this)"
title="Delete Certificate">

<i class="fa-solid fa-trash"></i>

</button>


</div>



</div>


</div>

</div>

<!-- ===========================
 MODAL PROJECT
=========================== -->


<div class="modal" id="projectModal">


<div class="modal-box">


<div class="modal-header">


<h2>
Add Project
</h2>


<button class="close"
onclick="closeModal('projectModal')">
×
</button>


</div>



<div class="modal-content">


<label>
Project Name
</label>

<input type="text" 
placeholder="Project title">



<label>
Description
</label>

<textarea 
placeholder="Describe your project"></textarea>



<label>
Skills Used
</label>

<input type="text"
placeholder="PHP, React, MySQL">



<label>
Github URL
</label>

<input type="text"
placeholder="https://github.com/...">



<label>
Project Image
</label>

<input type="file">



<label>
Project Status
</label>

<select>

<option>
Completed
</option>

<option>
Currently Working
</option>

</select>



<label>
Start Date
</label>

<input type="date">



<label>
End Date
</label>

<input type="date">



</div>



<div class="modal-footer">


<button class="save-btn">

Save Project

</button>


</div>



</div>


</div>

<!-- ===========================
 MODAL CERTIFICATE
=========================== -->
<div class="modal" id="certificateModal">

    <div class="modal-box">

        <div class="modal-header">

            <h2>
                Add Certificate
            </h2>

            <button class="close"
            onclick="closeModal('certificateModal')">
                ×
            </button>

        </div>


        <div class="modal-content">


            <label>
            Certificate Name
            </label>

            <input type="text"
            placeholder="Certificate name">



            <label>
            Issuing Organization
            </label>

            <input type="text"
            placeholder="Organization">



            <label>
            Issue Date
            </label>

            <input type="date">



            <label>
            Expiration date
            </label>

            <input type="date">



            <label>
            Credential ID
            </label>

            <input type="text">



            <label>
            Credential URL
            </label>

            <input type="text">



            <label>
            Certificate File
            </label>

            <input type="file">



        </div>


        <div class="modal-footer">

            <button class="save-btn">
                Save Certificate
            </button>

        </div>


    </div>

</div>





</main>






<script>


function openModal(id){

document.getElementById(id).style.display="flex";

}



function closeModal(id){

document.getElementById(id).style.display="none";

}



function deleteCard(btn){

let card =
btn.closest(".project-card, .certificate-card");

card.style.opacity="0";

card.style.transform="scale(.8)";


setTimeout(()=>{

card.remove();

},300);


}



function editItem(name){

alert(
"Edit mode: "+name
);

}
function viewCertificateFile(file){


let popup=document.getElementById(
"certificatePopup"
);


let image=document.getElementById(
"popupCertificateImage"
);


let pdf=document.getElementById(
"popupCertificatePdf"
);



popup.style.display="flex";



if(file.toLowerCase().endsWith(".pdf")){


image.style.display="none";

pdf.style.display="block";

pdf.src=file;


}
else{


pdf.style.display="none";

image.style.display="block";

image.src=file;


}



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