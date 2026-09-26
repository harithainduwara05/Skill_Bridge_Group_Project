/* =====================================
   SKILLBRIDGE CV BUILDER
===================================== */


/* =====================================
   PROFILE PHOTO PREVIEW
===================================== */

const profileUpload=document.getElementById("profileUpload");

const profilePreview=document.getElementById("profilePreview");

const previewPhoto=document.getElementById("previewPhoto");


if(profileUpload){

    profileUpload.addEventListener("change",function(){

        const file=this.files[0];

        if(file){

            const reader=new FileReader();

            reader.onload=function(e){

                if(profilePreview){

                    profilePreview.src=e.target.result;

                }

                if(previewPhoto){

                    previewPhoto.src=e.target.result;

                }

            };

            reader.readAsDataURL(file);

        }

    });

}






/* =====================================
   LIVE PROFILE UPDATE
===================================== */


function updatePreview(inputId,targetId,uppercase=false){

    const input=document.getElementById(inputId);

    const target=document.getElementById(targetId);


    if(input && target){

        input.addEventListener("input",function(){

            target.innerText=
            uppercase
            ? this.value.toUpperCase()
            : this.value;

        });

    }

}


updatePreview(
    "fullName",
    "previewName",
    true
);


updatePreview(
    "professionalTitle",
    "previewTitle"
);


updatePreview(
    "summaryInput",
    "previewSummary"
);


updatePreview(
    "phone",
    "previewPhone"
);


updatePreview(
    "location",
    "previewLocation"
);








/* =====================================
   TEMPLATE SWITCH
===================================== */


const templates=document.querySelectorAll(".template-card");


templates.forEach(template=>{


    template.addEventListener("click",function(){


        templates.forEach(item=>{

            item.classList.remove("active");

        });



        this.classList.add("active");



        const cv=document.getElementById("cvPreview");



        if(!cv){

            return;

        }



        if(this.dataset.template==="professional"){

            cv.classList.add("professional");

        }
        else{

            cv.classList.remove("professional");

        }



    });


});








/* =====================================
   SKILL DRAG DROP
===================================== */


let draggedSkill="";



const skillItems=
document.querySelectorAll(".drag-skill");



skillItems.forEach(skill=>{


    skill.addEventListener(
        "dragstart",
        function(){

            draggedSkill=
            this.dataset.skill ||
            this.innerText.trim();

        }
    );


});





const dropZone=
document.getElementById("skillDropZone");



if(dropZone){


dropZone.addEventListener(
"dragover",
function(e){

    e.preventDefault();

}

);




dropZone.addEventListener(
"drop",
function(e){


    e.preventDefault();



    if(!draggedSkill){

        return;

    }



    const exists=
    [...document.querySelectorAll(".selected-skill")]
    .some(skill=>
        skill.dataset.skill===draggedSkill
    );



    if(exists){

        draggedSkill="";

        return;

    }




    const skill=document.createElement("span");


    skill.className="selected-skill";


    skill.dataset.skill=draggedSkill;



    skill.innerHTML=`

    ${draggedSkill}

    <i class="fa-solid fa-xmark"></i>

    `;



    dropZone.appendChild(skill);





    const previewSkills=
    document.getElementById("previewSkills");



    if(previewSkills){


        const item=document.createElement("p");


        item.dataset.skill=draggedSkill;


        item.innerText=
        "• "+draggedSkill;



        previewSkills.appendChild(item);


    }




    draggedSkill="";


});


}









/* =====================================
   REMOVE SKILL
===================================== */


document.addEventListener(
"click",
function(e){


    if(
        e.target.classList.contains("fa-xmark")
    ){


        const skill=
        e.target.parentElement.dataset.skill;



        e.target.parentElement.remove();




        const preview=
        document.querySelector(
            `[data-skill="${skill}"]`
        );



        if(preview){

            preview.remove();

        }


    }


});









/* =====================================
   PROJECT SELECTION
===================================== */


const projectCheckboxes=
document.querySelectorAll(".project-checkbox");



projectCheckboxes.forEach(box=>{


    box.addEventListener(
    "change",
    function(){


        const project=
        this.closest(".project-card");



        const title=
        project.querySelector("h3").innerText;



        const tech=
        project.querySelector("p").innerText;



        const container=
        document.getElementById(
            "previewProjects"
        );




        if(this.checked){



            const exists=
            container.querySelector(
                `[data-project="${title}"]`
            );



            if(!exists){


                const item=
                document.createElement("div");



                item.className=
                "preview-project";


                item.dataset.project=
                title;



                item.innerHTML=`

                <h3>
                ${title}
                </h3>

                <p>
                ${tech}
                </p>

                `;



                container.appendChild(item);


            }



        }
        else{


            const remove=
            container.querySelector(
                `[data-project="${title}"]`
            );



            if(remove){

                remove.remove();

            }


        }



    });


});








/* =====================================
   ZOOM
===================================== */


let zoom=1;



function zoomIn(){

    zoom+=0.1;

    updateZoom();

}



function zoomOut(){

    zoom-=0.1;


    if(zoom<0.6){

        zoom=0.6;

    }


    updateZoom();

}




function updateZoom(){


    const cv=
    document.getElementById("cvPreview");


    if(cv){

        cv.style.transform=
        `scale(${zoom})`;

    }



    const label=
    document.getElementById("zoomLevel");


    if(label){

        label.innerText=
        Math.round(zoom*100)+"%";

    }


}








/* =====================================
   SAVE CV
===================================== */


const saveButton=
document.querySelector(".save-cv-btn");



if(saveButton){


saveButton.addEventListener(
"click",
function(){


    const skills=[];


    document
    .querySelectorAll(".selected-skill")
    .forEach(skill=>{


        skills.push(
            skill.dataset.skill
        );


    });




    const projects=[];


    document
    .querySelectorAll(".project-checkbox:checked")
    .forEach(project=>{


        projects.push(

            project
            .closest(".project-card")
            .querySelector("h3")
            .innerText

        );


    });





    const template=
    document
    .querySelector(".template-card.active")
    .dataset.template;






    const data={


        name:
        document.getElementById("fullName").value,


        title:
        document.getElementById("professionalTitle").value,


        phone:
        document.getElementById("phone").value,


        location:
        document.getElementById("location").value,


        github:
        document.getElementById("github").value,


        linkedin:
        document.getElementById("linkedin").value,


        summary:
        document.getElementById("summaryInput").value,


        skills:skills,


        projects:projects,


        template:template


    };






    fetch(
        "save_cv.php",
        {

            method:"POST",

            headers:{

                "Content-Type":
                "application/json"

            },

            body:
            JSON.stringify(data)

        }

    )
    .then(res=>res.json())
    .then(()=>{


        const toast=
        document.getElementById("saveToast");


        if(toast){

            toast.classList.add("show");


            setTimeout(()=>{

                toast.classList.remove("show");

            },3000);

        }


    });



});


}








/* =====================================
   EXPORT
===================================== */


const exportButton=
document.querySelector(".export-cv-btn");



if(exportButton){


exportButton.addEventListener(
"click",
function(){

    window.print();

});


}