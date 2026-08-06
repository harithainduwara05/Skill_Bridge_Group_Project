document.addEventListener("DOMContentLoaded", function () {


    /* ===============================
       SIDEBAR ACTIVE MENU
    =============================== */

    const menuItems = document.querySelectorAll(".menu li a");


    menuItems.forEach(item => {

        item.addEventListener("click", function () {

            menuItems.forEach(link => {

                link.parentElement.classList.remove("active");

            });


            this.parentElement.classList.add("active");

        });

    });



    /* ===============================
       SEARCH BOX
    =============================== */

    const searchInput = document.querySelector(".search-box input");


    if(searchInput){

        searchInput.addEventListener("keyup", function(){

            let value = this.value.toLowerCase();

            console.log("Searching:", value);

            // Later you can connect this with database search

        });

    }





    /* ===============================
       NOTIFICATION BUTTON
    =============================== */

    const notification = document.querySelector(".notification");


    if(notification){

        notification.addEventListener("click", function(){

            alert(
                "You have 3 new notifications"
            );

        });

    }





    /* ===============================
       PROFILE CLICK
    =============================== */

    const profile = document.querySelector(".profile");


    if(profile){

        profile.addEventListener("click", function(){

            alert(
                "Opening profile settings..."
            );

        });

    }







    /* ===============================
       BOOST LISTINGS BUTTON
    =============================== */


    const boostButton = document.querySelector(".goal button");


    if(boostButton){

        boostButton.addEventListener("click", function(){

            alert(
                "Your internship listings are boosted!"
            );

        });

    }







    /* ===============================
       VIEW INTERNSHIP BUTTONS
    =============================== */


    const viewButtons = document.querySelectorAll(".job-card button");


    viewButtons.forEach(button => {


        button.addEventListener("click", function(){

            alert(
                "Opening internship details..."
            );


        });


    });








    /* ===============================
       CARD HOVER ANIMATION
    =============================== */


    const cards = document.querySelectorAll(
        ".stat-card, .panel, .company-card"
    );


    cards.forEach(card=>{


        card.addEventListener("mouseenter",()=>{

            card.style.cursor="pointer";

        });


    });







});