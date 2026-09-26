/* =====================================
   SKILLBRIDGE TEAM WORKSPACE
===================================== */


document.addEventListener(
"DOMContentLoaded",
function(){



/* =====================================
   SEND CHAT MESSAGE
===================================== */


const sendButton=document.querySelector(
".workspace-send-btn"
);


const chatInput=document.querySelector(
".workspace-chat-input input"
);


const messages=document.querySelector(
".messages"
);



function sendMessage(){


if(!chatInput){

    return;

}


const text=chatInput.value.trim();



if(text===""){

    return;

}




const message=document.createElement(
"div"
);



message.className=
"message mine";



message.innerHTML=
`
<p>
${text}
</p>
`;



messages.appendChild(
message
);



chatInput.value="";



messages.scrollTop=
messages.scrollHeight;



showToast(
"Message sent"
);



}



if(sendButton){


sendButton.addEventListener(
"click",
sendMessage
);


}




if(chatInput){


chatInput.addEventListener(
"keypress",
function(e){

if(e.key==="Enter"){

    sendMessage();

}

});


}








/* =====================================
   NEW TASK BUTTON
===================================== */


const newTask=document.querySelector(
".workspace-new-task-btn"
);



if(newTask){


newTask.addEventListener(
"click",
function(){


showToast(
"Create task window opened"
);



});


}








/* =====================================
   FILTER BUTTON
===================================== */


const filter=document.querySelector(
".workspace-filter-btn"
);



if(filter){


filter.addEventListener(
"click",
function(){


this.classList.toggle(
"active"
);



showToast(
"Filter applied"
);



});


}








/* =====================================
   UPLOAD AREA
===================================== */


const uploadBox=document.querySelector(
".upload-box"
);



if(uploadBox){


uploadBox.addEventListener(
"click",
function(){


const input=document.createElement(
"input"
);



input.type="file";

input.multiple=true;



input.click();




input.addEventListener(
"change",
function(){


if(this.files.length){

showToast(
this.files.length+
" file(s) selected"
);


}


});


});


}








/* =====================================
   TASK SELECT
===================================== */


const tasks=document.querySelectorAll(
".task-card"
);



tasks.forEach(task=>{


task.addEventListener(
"click",
function(){


tasks.forEach(item=>{

item.classList.remove(
"selected"
);

});


this.classList.add(
"selected"
);



});


});









/* =====================================
   FLOAT BUTTON
===================================== */


const addButton=document.querySelector(
".workspace-floating-add"
);



if(addButton){


addButton.addEventListener(
"click",
function(){


showToast(
"Add new item"
);



});


}









/* =====================================
   TOAST
===================================== */


function showToast(message){



let toast=document.querySelector(
".workspace-toast"
);



if(!toast){


toast=document.createElement(
"div"
);



toast.className=
"workspace-toast";



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