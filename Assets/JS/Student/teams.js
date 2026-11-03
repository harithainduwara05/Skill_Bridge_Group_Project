/* =====================================
   STUDENT TEAMS PAGE (WITH ORG-STYLE FILTER PANEL)
===================================== */

document.addEventListener("DOMContentLoaded", function () {

    /* =====================================
       FILTER PANEL (Status: All | On Track | Behind | Completed, Search, Sort)
    ===================================== */
    const btn     = document.getElementById("tmFilterBtn");
    const panel   = document.getElementById("tmFilterPanel");
    const search  = document.getElementById("tmFilterSearch");
    const sortEl  = document.getElementById("tmFilterSort");
    const grid    = document.getElementById("studentTeamsGrid");
    const seg     = document.getElementById("tmFilterStatus");
    const countEl = document.getElementById("tmFilterCount");
    const result  = document.getElementById("tmFilterResult");
    const empty   = document.getElementById("tmEmpty");
    const clearBtn = document.getElementById("tmFilterClear");
    const emptyClearBtn = document.getElementById("tmEmptyClear");

    if (btn && panel && grid) {
        const cards = Array.from(grid.querySelectorAll(".student-team-card"));
        let status = "all";

        function apply() {
            const q = (search ? search.value : "").trim().toLowerCase();
            let shown = 0;

            cards.forEach(function (card) {
                const cardState = card.getAttribute("data-state") || "ontrack";
                const cardSearch = (card.getAttribute("data-search") || "").toLowerCase();
                const ok = (status === "all" || cardState === status) &&
                           (q === "" || cardSearch.indexOf(q) !== -1);
                card.hidden = !ok;
                card.style.display = ok ? "" : "none";
                if (ok) shown++;
            });

            const num = (c, k) => Number(c.getAttribute("data-" + k)) || 0;
            const sorted = cards.slice().sort(function (a, b) {
                switch (sortEl ? sortEl.value : "newest") {
                    case "deadline":
                        return (num(a, "deadline") || Infinity) - (num(b, "deadline") || Infinity);
                    case "progress_low":
                        return num(a, "progress") - num(b, "progress");
                    case "progress_high":
                        return num(b, "progress") - num(a, "progress");
                    default:
                        return num(a, "order") - num(b, "order");
                }
            });
            sorted.forEach(c => grid.appendChild(c));

            const active = (status !== "all" ? 1 : 0) + (q !== "" ? 1 : 0) + ((sortEl && sortEl.value !== "newest") ? 1 : 0);
            if (countEl) {
                countEl.textContent = String(active);
                countEl.hidden = active === 0;
            }
            if (result) {
                result.textContent = "Showing " + shown + " of " + cards.length;
            }
            if (empty) {
                empty.hidden = shown !== 0;
            }
        }

        function clearAll() {
            status = "all";
            if (seg) {
                seg.querySelectorAll("button").forEach(b => b.classList.toggle("on", b.getAttribute("data-value") === "all"));
            }
            if (sortEl) sortEl.value = "newest";
            if (search) search.value = "";
            apply();
        }

        btn.addEventListener("click", function (e) {
            e.stopPropagation();
            panel.hidden = !panel.hidden;
            btn.setAttribute("aria-expanded", String(!panel.hidden));
            if (!panel.hidden && search) search.focus();
        });

        panel.addEventListener("click", e => e.stopPropagation());
        document.addEventListener("click", function () {
            panel.hidden = true;
            btn.setAttribute("aria-expanded", "false");
        });
        document.addEventListener("keydown", function (e) {
            if (e.key === "Escape") panel.hidden = true;
        });

        if (seg) {
            seg.addEventListener("click", function (e) {
                const b = e.target.closest("button");
                if (!b) return;
                status = b.getAttribute("data-value") || "all";
                seg.querySelectorAll("button").forEach(x => x.classList.toggle("on", x === b));
                apply();
            });
        }

        if (sortEl) sortEl.addEventListener("change", apply);
        if (search) search.addEventListener("input", apply);
        if (clearBtn) clearBtn.addEventListener("click", clearAll);
        if (emptyClearBtn) emptyClearBtn.addEventListener("click", clearAll);

        apply();
    }

    /* =====================================
       TEAM CARD HOVER & TOAST INTERACTIONS
    ===================================== */
    const viewButtons = document.querySelectorAll(".card-buttons a:first-child");
    viewButtons.forEach(button => {
        button.addEventListener("click", function () {
            showToast("Opening team details...");
        });
    });

    const workspaceButtons = document.querySelectorAll(".card-buttons a:last-child");
    workspaceButtons.forEach(button => {
        button.addEventListener("click", function () {
            showToast("Opening workspace...");
        });
    });

    function showToast(message) {
        let toast = document.querySelector(".team-toast");
        if (!toast) {
            toast = document.createElement("div");
            toast.className = "team-toast";
            document.body.appendChild(toast);
        }
        toast.innerHTML = `<i class="fa-solid fa-circle-check"></i> ${message}`;
        toast.classList.add("show");
        setTimeout(function () {
            toast.classList.remove("show");
        }, 2500);
    }
});