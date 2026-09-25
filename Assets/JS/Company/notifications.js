document.addEventListener("DOMContentLoaded", function () {

    const markAllButton =
        document.getElementById("markAllNotifications");

    const filterButton =
        document.getElementById("notificationFilterBtn");

    const filterMenu =
        document.getElementById("notificationFilterMenu");

    const filterOptions =
        document.querySelectorAll(".filter-option");

    const notificationCards =
        document.querySelectorAll(".notification-card");

    const emptyState =
        document.getElementById("notificationEmpty");


    /* =====================================
       MARK ALL AS READ
    ===================================== */

    if (markAllButton) {

        markAllButton.addEventListener("click", function () {

            notificationCards.forEach(function (card) {

                card.classList.remove("unread");
                card.classList.add("read");

            });

        });

    }


    /* =====================================
       FILTER MENU
    ===================================== */

    if (filterButton && filterMenu) {

        filterButton.addEventListener("click", function (event) {

            event.stopPropagation();

            filterMenu.classList.toggle("show");

        });


        filterMenu.addEventListener("click", function (event) {
            event.stopPropagation();
        });


        document.addEventListener("click", function () {
            filterMenu.classList.remove("show");
        });


        document.addEventListener("keydown", function (event) {

            if (event.key === "Escape") {
                filterMenu.classList.remove("show");
            }

        });

    }


    /* =====================================
       FILTER NOTIFICATIONS
    ===================================== */

    function applyFilter(filter) {

        let visibleCount = 0;


        notificationCards.forEach(function (card) {

            const cardType =
                card.dataset.type;

            const isUnread =
                card.classList.contains("unread");


            let shouldShow = false;


            if (filter === "all") {

                shouldShow = true;

            } else if (filter === "unread") {

                shouldShow = isUnread;

            } else {

                shouldShow =
                    cardType === filter;

            }


            if (shouldShow) {

                card.style.display = "flex";

                visibleCount++;

            } else {

                card.style.display = "none";

            }

        });


        if (emptyState) {

            emptyState.style.display =
                visibleCount === 0
                    ? "flex"
                    : "none";

        }

    }


    filterOptions.forEach(function (option) {

        option.addEventListener("click", function () {

            filterOptions.forEach(function (item) {
                item.classList.remove("active");
            });


            option.classList.add("active");


            const filter =
                option.dataset.filter;


            applyFilter(filter);


            if (filterMenu) {
                filterMenu.classList.remove("show");
            }

        });

    });


    /* =====================================
       CLICK CARD -> READ
    ===================================== */

    notificationCards.forEach(function (card) {

        card.addEventListener("click", function (event) {

            if (event.target.closest("button")) {
                return;
            }


            card.classList.remove("unread");
            card.classList.add("read");

        });

    });


    /* =====================================
       SNOOZE BUTTON
    ===================================== */

    const snoozeButtons =
        document.querySelectorAll(".snooze-btn");


    snoozeButtons.forEach(function (button) {

        button.addEventListener("click", function () {

            const card =
                button.closest(".notification-card");


            if (card) {

                card.style.display = "none";

            }

        });

    });


    /* =====================================
       SETTINGS
       Frontend UI only
    ===================================== */

    const emailAlerts =
        document.getElementById("emailAlerts");

    const pushNotifications =
        document.getElementById("pushNotifications");


    if (emailAlerts) {

        emailAlerts.addEventListener("change", function () {

            console.log(
                "Email alerts:",
                emailAlerts.checked
            );

        });

    }


    if (pushNotifications) {

        pushNotifications.addEventListener("change", function () {

            console.log(
                "Push notifications:",
                pushNotifications.checked
            );

        });

    }


    /* Initial state */

    applyFilter("all");

});