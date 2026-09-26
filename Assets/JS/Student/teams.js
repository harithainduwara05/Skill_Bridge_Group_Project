/* =====================================
   STUDENT TEAMS PAGE
===================================== */


document.addEventListener(
"DOMContentLoaded",
function(){



/* =====================================
   CREATE TEAM
===================================== */


const createButton=document.querySelector(
".team-primary-btn"
);



if(createButton){


createButton.addEventListener(
"click",
function(){


showToast(
"Create Team page opened"
);



});


}






/* =====================================
   DISCOVER TEAMS
===================================== */


const discoverButton=document.querySelector(
".team-secondary-btn"
);



if(discoverButton){


discoverButton.addEventListener(
"click",
function(){


showToast(
"Opening team directory"
);



});


}








/* =====================================
   ACCEPT INVITATION
===================================== */


const acceptButtons=document.querySelectorAll(
".accept-btn"
);



acceptButtons.forEach(
button=>{


button.addEventListener(
"click",
function(){


this.innerHTML=
`
<i class="fa-solid fa-check"></i>
Accepted
`;



this.disabled=true;



this.style.opacity=".7";



showToast(
"Team invitation accepted"
);



});


});








/* =====================================
   DECLINE INVITATION
===================================== */


const declineButtons=document.querySelectorAll(
".decline-btn"
);



declineButtons.forEach(
button=>{


button.addEventListener(
"click",
function(){



const card=
this.closest(
".invitation-card"
);



if(card){


card.style.opacity="0";



setTimeout(
function(){

card.remove();

},
300
);



}



showToast(
"Invitation declined"
);



});


});









/* =====================================
   VIEW TEAM
===================================== */


const viewButtons=document.querySelectorAll(
".card-buttons a:first-child"
);



viewButtons.forEach(
button=>{


button.addEventListener(
"click",
function(e){


showToast(
"Opening team details..."
);



});


});








/* =====================================
   WORKSPACE
===================================== */


const workspaceButtons=document.querySelectorAll(
".card-buttons a:last-child"
);



workspaceButtons.forEach(
button=>{


button.addEventListener(
"click",
function(){


showToast(
"Opening workspace..."
);



});


});









/* =====================================
   TEAM CARD EFFECT
===================================== */


const cards=document.querySelectorAll(
".student-team-card"
);



cards.forEach(
card=>{


card.addEventListener(
"mouseenter",
function(){


this.style.transition=".2s";


});


});








/* =====================================
   TOAST MESSAGE
===================================== */


function showToast(message){



let toast=document.querySelector(
".team-toast"
);



if(!toast){


toast=document.createElement(
"div"
);



toast.className=
"team-toast";



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