document.addEventListener("DOMContentLoaded", function () {

    const menuToggle = document.getElementById("menuToggle");
    const navLinks = document.querySelector(".nav-links");

    if (menuToggle) {

        menuToggle.addEventListener("click", function () {

            navLinks.classList.toggle("mobile-active");

        });

    }


    // Newsletter form
    const newsletterForm =
        document.querySelector(".newsletter-form");

    if (newsletterForm) {

        newsletterForm.addEventListener("submit", function (event) {

            event.preventDefault();

            const emailInput =
                newsletterForm.querySelector("input");

            if (emailInput.value.trim() !== "") {

                alert(
                    "Thank you for subscribing to SkillBridge!"
                );

                emailInput.value = "";

            }

        });

    }

});