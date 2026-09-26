/* =====================================
   VIEW TEAM PAGE
===================================== */


document.addEventListener(
"DOMContentLoaded",
function(){






/* =====================================
   TAB SWITCHING
===================================== */


const tabs=document.querySelectorAll(
".team-tabs button"
);



tabs.forEach(
tab=>{


tab.addEventListener(
"click",
function(){


tabs.forEach(
item=>{

item.classList.remove(
"active"
);

});



this.classList.add(
"active"
);



showToast(
this.innerText+" selected"
);



});


});









/* =====================================
   OPEN WORKSPACE
===================================== */


const workspaceLink=document.querySelector(
".card-title-row a"
);



if(workspaceLink){


workspaceLink.addEventListener(
"click",
function(){


showToast(
"Opening team workspace..."
);



});


}









/* =====================================
   TASK CARD INTERACTION
===================================== */


const tasks=document.querySelectorAll(
".task-preview div"
);



tasks.forEach(
task=>{


task.addEventListener(
"click",
function(){


tasks.forEach(
item=>{

item.classList.remove(
"selected-task"
);

});



this.classList.add(
"selected-task"
);



showToast(
"Task selected"
);



});


});









/* =====================================
   MEMBER ANIMATION
===================================== */


const members=document.querySelectorAll(
".member-item"
);



members.forEach(
member=>{


member.addEventListener(
"mouseenter",
function(){


this.style.transform=
"translateX(5px)";

this.style.transition=
".2s";


});


member.addEventListener(
"mouseleave",
function(){


this.style.transform=
"translateX(0)";


});


});









/* =====================================
   PAGE CARD ANIMATION
===================================== */


const cards=document.querySelectorAll(
".detail-card,.team-banner"
);



cards.forEach(
(card,index)=>{


card.style.opacity="0";

card.style.transform=
"translateY(20px)";



setTimeout(
function(){


card.style.transition=
".4s ease";

card.style.opacity="1";

card.style.transform=
"translateY(0)";


},
index*100
);



});









/* =====================================
   TOAST
===================================== */


function showToast(message){



let toast=document.querySelector(
".view-team-toast"
);



if(!toast){


toast=document.createElement(
"div"
);



toast.className=
"view-team-toast";



document.body.appendChild(
toast
);



}



toast.innerHTML=
`
<i class="fa-solid fa-circle-check"></i>
${message}
`;



toast.classList.add(
"show"
);



setTimeout(
function(){


toast.classList.remove(
"show"
);



},
2500
);



}





});