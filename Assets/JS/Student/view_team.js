/* =====================================
   VIEW TEAM PAGE
===================================== */
document.addEventListener("DOMContentLoaded", function() {

    /* TAB SWITCHING & SMOOTH SCROLLING */
    const tabs = document.querySelectorAll(".team-tabs a");
    tabs.forEach(tab => {
        tab.addEventListener("click", function(e) {
            const href = this.getAttribute("href");
            
            // Only intercept hash links (same-page scroll)
            if (href.startsWith("#")) {
                e.preventDefault();
                
                // Update active class
                tabs.forEach(item => item.classList.remove("active"));
                this.classList.add("active");
                
                // Smooth scroll to section
                const target = document.querySelector(href);
                if(target) {
                    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
                showToast(this.innerText + " selected");
            }
            // If it's a full URL (like team_workspace.php), let the browser navigate natively!
        });
    });

    /* TASK CARD INTERACTION */
    const tasks = document.querySelectorAll(".task-preview div");
    tasks.forEach(task => {
        task.addEventListener("click", function() {
            tasks.forEach(item => item.classList.remove("selected-task"));
            this.classList.add("selected-task");
        });
    });

    /* MEMBER ANIMATION */
    const members = document.querySelectorAll(".member-item");
    members.forEach(member => {
        member.addEventListener("mouseenter", function() {
            this.style.transform = "translateX(5px)";
            this.style.transition = ".2s";
        });
        member.addEventListener("mouseleave", function() {
            this.style.transform = "translateX(0)";
        });
    });

    /* PAGE CARD ANIMATION */
    const cards = document.querySelectorAll(".detail-card, .team-banner");
    cards.forEach((card, index) => {
        card.style.opacity = "0";
        card.style.transform = "translateY(20px)";
        setTimeout(function() {
            card.style.transition = ".4s ease";
            card.style.opacity = "1";
            card.style.transform = "translateY(0)";
        }, index * 100);
    });

    /* TOAST */
    function showToast(message) {
        let toast = document.querySelector(".view-team-toast");
        if (!toast) {
            toast = document.createElement("div");
            toast.className = "view-team-toast";
            document.body.appendChild(toast);
        }
        toast.innerHTML = `<i class="fa-solid fa-circle-check"></i> ${message}`;
        toast.classList.add("show");
        setTimeout(function() {
            toast.classList.remove("show");
        }, 2500);
    }
});
