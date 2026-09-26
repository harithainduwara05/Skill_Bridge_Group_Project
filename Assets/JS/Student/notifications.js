/* =====================================
   SKILLBRIDGE NOTIFICATIONS
===================================== */



document.addEventListener(
"DOMContentLoaded",
function(){



/* =====================================
   TAB SWITCHING
===================================== */


const tabs=document.querySelectorAll(
".notification-tabs button"
);


tabs.forEach(tab=>{


    tab.addEventListener(
    "click",
    function(){


        tabs.forEach(item=>{

            item.classList.remove("active");

        });


        this.classList.add("active");



    });


});







/* =====================================
   MARK ALL READ
===================================== */


const markRead=document.querySelector(
".mark-read"
);



if(markRead){


markRead.addEventListener(
"click",
function(){


const cards=document.querySelectorAll(
".notification-card"
);



cards.forEach(card=>{


    card.classList.add("read");


});



showMessage(
"All notifications marked as read"
);



});


}








/* =====================================
   CLEAR ALL
===================================== */


const clearButton=document.querySelector(
".clear-all"
);



if(clearButton){


clearButton.addEventListener(
"click",
function(){



const confirmDelete=
confirm(
"Are you sure you want to clear all notifications?"
);



if(!confirmDelete){

    return;

}




const list=document.querySelector(
".notification-list"
);



list.innerHTML=`


<div class="empty-notification">

<i class="fa-solid fa-clock-rotate-left"></i>

<p>
You have no notifications.
</p>

</div>


`;



showMessage(
"Notifications cleared"
);



});


}









/* =====================================
   ACCEPT INVITATION
===================================== */


const acceptButtons=document.querySelectorAll(
".accept-btn"
);



acceptButtons.forEach(button=>{


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



showMessage(
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



declineButtons.forEach(button=>{


button.addEventListener(
"click",
function(){


const card=
this.closest(
".notification-card"
);



card.style.opacity="0";


setTimeout(()=>{

    card.remove();

},300);



showMessage(
"Invitation declined"
);



});


});










/* =====================================
   TOAST MESSAGE
===================================== */


function showMessage(message){



let toast=document.querySelector(
".notification-toast"
);



if(!toast){


toast=document.createElement(
"div"
);


toast.className=
"notification-toast";



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




setTimeout(()=>{


toast.classList.remove(
"show"
);


},2500);



}




});