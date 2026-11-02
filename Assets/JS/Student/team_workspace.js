/* =====================================
   SKILLBRIDGE TEAM WORKSPACE (DATABASE-BACKED)
===================================== */

document.addEventListener("DOMContentLoaded", function () {
    const page = document.querySelector(".workspace-page");
    if (!page) return;

    const teamId = Number(page.getAttribute("data-team-id") || 0);
    const isLeader = page.getAttribute("data-is-leader") === "1";

    function esc(str) {
        const d = document.createElement("div");
        d.textContent = str == null ? "" : String(str);
        return d.innerHTML;
    }

    function showToast(message, isError = false) {
        let toast = document.querySelector(".workspace-toast");
        if (!toast) {
            toast = document.createElement("div");
            toast.className = "workspace-toast";
            document.body.appendChild(toast);
        }
        toast.innerHTML = `<i class="fa-solid ${isError ? 'fa-circle-exclamation' : 'fa-circle-check'}"></i> ${esc(message)}`;
        toast.classList.toggle("error", !!isError);
        toast.classList.add("show");
        clearTimeout(toast._timer);
        toast._timer = setTimeout(function () {
            toast.classList.remove("show");
        }, 3200);
    }

    function api(action, fields = {}) {
        const fd = new FormData();
        fd.append("ws_action", action);
        if (!fields.team_id) {
            fd.append("team_id", teamId);
        }
        Object.keys(fields).forEach(function (k) {
            fd.append(k, fields[k]);
        });
        return fetch(`team_workspace.php?id=${teamId}`, {
            method: "POST",
            body: fd
        })
            .then(r => r.json())
            .catch(() => ({ ok: false, message: "Network error. Please try again." }));
    }

    /* =====================================
       MODALS OPEN / CLOSE
    ===================================== */
    const taskModal = document.getElementById("wsTaskModal");
    const reviewModal = document.getElementById("wsReviewModal");
    const openNewTaskBtn = document.getElementById("openNewTaskBtn");

    if (openNewTaskBtn && taskModal) {
        openNewTaskBtn.addEventListener("click", function () {
            taskModal.hidden = false;
        });
    }

    document.querySelectorAll("[data-close-modal]").forEach(function (btn) {
        btn.addEventListener("click", function () {
            const target = document.getElementById(btn.getAttribute("data-close-modal"));
            if (target) target.hidden = true;
        });
    });

    /* =====================================
       RENDER KANBAN BOARD & PROGRESS
    ===================================== */
    function renderTasks(tasks) {
        const cols = { todo: [], progress: [], review: [], done: [] };
        (tasks || []).forEach(function (t) {
            const c = cols[t.col] ? t.col : "todo";
            cols[c].push(t);
        });

        Object.keys(cols).forEach(function (colKey) {
            const countEl = document.getElementById("col-count-" + colKey);
            const bodyEl = document.getElementById("col-body-" + colKey);
            if (countEl) countEl.textContent = cols[colKey].length;
            if (!bodyEl) return;

            bodyEl.innerHTML = cols[colKey].map(function (t) {
                let actions = "";
                if (colKey === "todo") {
                    actions += `<button type="button" class="task-act-btn primary" data-move-task="${t.id}" data-target-col="progress">Start</button>`;
                } else if (colKey === "progress") {
                    actions += `<button type="button" class="task-act-btn" data-move-task="${t.id}" data-target-col="todo">To Do</button>`;
                    actions += `<button type="button" class="task-act-btn primary" data-review-task="${t.id}" data-task-title="${esc(t.title)}">Send for Review</button>`;
                } else if (colKey === "review") {
                    actions += `<button type="button" class="task-act-btn" data-move-task="${t.id}" data-target-col="progress">Take Back</button>`;
                }
                if (isLeader) {
                    actions += `<button type="button" class="task-act-btn danger" data-delete-task="${t.id}" title="Delete Task"><i class="fa-solid fa-trash"></i></button>`;
                }

                return `
                    <div class="task-card" data-task-id="${t.id}">
                        <h4>${esc(t.title)}</h4>
                        ${t.description ? `<p>${esc(t.description)}</p>` : ""}
                        <div class="task-card-meta">
                            <small><i class="fa-regular fa-user"></i> ${esc(t.who || "Unassigned")}</small>
                            <small><i class="fa-regular fa-calendar"></i> ${t.due ? "Due " + esc(t.due) : "No deadline"}</small>
                        </div>
                        <div class="task-card-actions">${actions}</div>
                    </div>
                `;
            }).join("");
        });

        const total = (tasks || []).length;
        const done = cols.done.length;
        const pct = total > 0 ? Math.round((done / total) * 100) : 0;

        const pctEl = document.getElementById("wsProgressPct");
        const barEl = document.getElementById("wsProgressBar");
        const doneEl = document.getElementById("wsDoneCount");
        const pendEl = document.getElementById("wsPendingCount");

        if (pctEl) pctEl.textContent = pct + "%";
        if (barEl) barEl.style.width = pct + "%";
        if (doneEl) doneEl.textContent = done + " TASKS DONE";
        if (pendEl) pendEl.textContent = Math.max(0, total - done) + " PENDING";
    }

    /* =====================================
       ADD / MOVE / DELETE TASK
    ===================================== */
    const taskForm = document.getElementById("wsTaskForm");
    if (taskForm) {
        taskForm.addEventListener("submit", function (e) {
            e.preventDefault();
            const title = document.getElementById("wsTaskTitle").value.trim();
            const desc = document.getElementById("wsTaskDesc").value.trim();
            const assignee = document.getElementById("wsTaskAssignee").value;
            const due = document.getElementById("wsTaskDue").value;

            api("add_task", {
                team_id: teamId,
                title: title,
                description: desc,
                assignee: assignee,
                due: due
            }).then(function (res) {
                if (!res.ok) {
                    showToast(res.message || "Could not add task.", true);
                    return;
                }
                taskForm.reset();
                if (taskModal) taskModal.hidden = true;
                renderTasks(res.tasks);
                showToast("Task added to board!");
            });
        });
    }

    const kanbanBoard = document.getElementById("wsKanbanBoard");
    if (kanbanBoard) {
        kanbanBoard.addEventListener("click", function (e) {
            const moveBtn = e.target.closest("[data-move-task]");
            if (moveBtn) {
                const taskId = moveBtn.getAttribute("data-move-task");
                const col = moveBtn.getAttribute("data-target-col");
                api("move_task", { task_id: taskId, col: col }).then(function (res) {
                    if (!res.ok) {
                        showToast(res.message || "Could not move task.", true);
                        return;
                    }
                    renderTasks(res.tasks);
                    showToast("Task status updated");
                });
                return;
            }

            const revBtn = e.target.closest("[data-review-task]");
            if (revBtn && reviewModal) {
                document.getElementById("wsReviewTaskId").value = revBtn.getAttribute("data-review-task");
                document.getElementById("wsReviewTaskName").textContent = "Task: " + (revBtn.getAttribute("data-task-title") || "");
                document.getElementById("wsReviewNote").value = "";
                document.getElementById("wsReviewLink").value = "";
                reviewModal.hidden = false;
                return;
            }

            const delBtn = e.target.closest("[data-delete-task]");
            if (delBtn) {
                const taskId = delBtn.getAttribute("data-delete-task");
                api("delete_task", { task_id: taskId }).then(function (res) {
                    if (!res.ok) {
                        showToast(res.message || "Could not delete task.", true);
                        return;
                    }
                    renderTasks(res.tasks);
                    showToast("Task deleted");
                });
            }
        });
    }

    const reviewForm = document.getElementById("wsReviewForm");
    if (reviewForm) {
        reviewForm.addEventListener("submit", function (e) {
            e.preventDefault();
            const taskId = document.getElementById("wsReviewTaskId").value;
            const note = document.getElementById("wsReviewNote").value.trim();
            const link = document.getElementById("wsReviewLink").value.trim();

            api("move_task", {
                task_id: taskId,
                col: "review",
                note: note,
                link: link
            }).then(function (res) {
                if (!res.ok) {
                    showToast(res.message || "Could not send task for review.", true);
                    return;
                }
                if (reviewModal) reviewModal.hidden = true;
                renderTasks(res.tasks);
                loadWorkspaceState();
                showToast("Task sent to Organization for review!");
            });
        });
    }

    /* =====================================
       SHARED FILES UPLOAD & DELETE
    ===================================== */
    const fileInput = document.getElementById("wsFileInput");
    const uploadBox = document.getElementById("wsUploadBox");
    const filesList = document.getElementById("wsFilesList");
    const filesCount = document.getElementById("wsFilesCount");

    function renderFiles(files) {
        if (filesCount) filesCount.textContent = (files ? files.length : 0) + " file(s)";
        if (!filesList) return;
        if (!files || !files.length) {
            filesList.innerHTML = `<p style="padding:15px;color:#667085;">No shared files yet.</p>`;
            return;
        }
        filesList.innerHTML = files.map(function (f) {
            return `
                <div class="asset-item" data-file-id="${f.id}">
                    <i class="fa-regular fa-file"></i>
                    <div>
                        <strong><a href="../../${esc(f.path)}" target="_blank" rel="noopener" style="color:#082544;text-decoration:none;">${esc(f.name)}</a></strong>
                        <p>Shared by ${esc(f.by)} &bull; ${esc(f.when)}</p>
                    </div>
                    <div class="asset-item-actions" style="margin-left:auto;display:flex;gap:8px;">
                        <a href="../../${esc(f.path)}" download class="task-act-btn" title="Download"><i class="fa-solid fa-download"></i></a>
                        ${f.can_delete ? `<button type="button" class="task-act-btn danger" data-delete-file="${f.id}" title="Remove"><i class="fa-solid fa-trash"></i></button>` : ""}
                    </div>
                </div>
            `;
        }).join("");
    }

    function uploadSelectedFiles(list) {
        if (!list || !list.length) return;
        const fd = new FormData();
        fd.append("ws_action", "upload_file");
        fd.append("team_id", teamId);
        Array.from(list).forEach(function (f) {
            fd.append("files[]", f);
        });

        fetch(`team_workspace.php?id=${teamId}`, { method: "POST", body: fd })
            .then(r => r.json())
            .then(function (res) {
                if (!res.ok) {
                    showToast(res.message || "File upload failed.", true);
                    return;
                }
                if (res.errors && res.errors.length) {
                    showToast(res.errors.join(" "), true);
                } else {
                    showToast("File(s) shared with team!");
                }
                renderFiles(res.files || []);
            })
            .catch(() => showToast("Could not upload file.", true));
    }

    if (fileInput) {
        fileInput.addEventListener("change", function () {
            uploadSelectedFiles(fileInput.files);
            fileInput.value = "";
        });
    }

    if (uploadBox) {
        ["dragenter", "dragover"].forEach(ev => uploadBox.addEventListener(ev, e => {
            e.preventDefault();
            uploadBox.style.borderColor = "#ff8a00";
        }));
        ["dragleave", "drop"].forEach(ev => uploadBox.addEventListener(ev, e => {
            e.preventDefault();
            uploadBox.style.borderColor = "#cbd5e1";
        }));
        uploadBox.addEventListener("drop", function (e) {
            uploadSelectedFiles(e.dataTransfer.files);
        });
    }

    if (filesList) {
        filesList.addEventListener("click", function (e) {
            const delBtn = e.target.closest("[data-delete-file]");
            if (!delBtn) return;
            const fileId = delBtn.getAttribute("data-delete-file");
            api("delete_file", { file_id: fileId }).then(function (res) {
                if (!res.ok) {
                    showToast(res.message || "Could not delete file.", true);
                    return;
                }
                renderFiles(res.files || []);
                showToast("File removed");
            });
        });
    }

    /* =====================================
       TEAM CHAT DISCUSSION
    ===================================== */
    const chatForm = document.getElementById("wsChatForm");
    const chatInput = document.getElementById("wsChatInput");
    const chatMessages = document.getElementById("wsChatMessages");
    const chatCount = document.getElementById("wsChatCount");

    function renderMessages(list) {
        if (chatCount) chatCount.textContent = (list ? list.length : 0) + " messages";
        if (!chatMessages) return;
        if (!list || !list.length) {
            chatMessages.innerHTML = `<p class="ws-empty-chat" style="color:#667085;text-align:center;margin-top:20px;">No team messages yet. Start the conversation!</p>`;
            return;
        }
        chatMessages.innerHTML = list.map(function (m) {
            return `
                <div class="message ${m.me ? "mine" : "other"}" data-msg-id="${m.id}">
                    <strong>${esc(m.who)}</strong>
                    <small>${esc(m.date + " " + m.time)}</small>
                    <p>${esc(m.text).replace(/\n/g, "<br>")}</p>
                </div>
            `;
        }).join("");
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }

    if (chatForm && chatInput) {
        chatForm.addEventListener("submit", function (e) {
            e.preventDefault();
            const text = chatInput.value.trim();
            if (!text) return;
            chatInput.value = "";
            api("send_message", { team_id: teamId, message: text }).then(function (res) {
                if (!res.ok) {
                    showToast(res.message || "Could not send message.", true);
                    return;
                }
                loadWorkspaceState();
            });
        });
    }

    /* =====================================
       FINAL REPORT SUBMISSION
    ===================================== */
    const reportForm = document.getElementById("wsReportForm");
    const reportStatusBox = document.getElementById("wsReportStatusBox");

    if (reportForm) {
        reportForm.addEventListener("submit", function (e) {
            e.preventDefault();
            const fileEl = document.getElementById("wsReportFile");
            const summaryEl = document.getElementById("wsReportSummary");
            if (!fileEl.files.length) {
                showToast("Please select your final report file.", true);
                return;
            }

            const fd = new FormData();
            fd.append("ws_action", "submit_report");
            fd.append("team_id", teamId);
            fd.append("summary", summaryEl.value.trim());
            fd.append("report", fileEl.files[0]);

            const btn = document.getElementById("wsReportSubmitBtn");
            if (btn) btn.disabled = true;

            fetch(`team_workspace.php?id=${teamId}`, { method: "POST", body: fd })
                .then(r => r.json())
                .then(function (res) {
                    if (btn) btn.disabled = false;
                    if (!res.ok) {
                        showToast(res.message || "Could not submit final report.", true);
                        return;
                    }
                    const rep = res.report;
                    if (rep && reportStatusBox) {
                        reportStatusBox.innerHTML = `
                            <div class="report-banner">
                                <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;">
                                    <div>
                                        <span class="status-track">${esc(rep.status.toUpperCase())}</span>
                                        <h4 style="margin:8px 0 4px;color:#082544;"><i class="fa-regular fa-file-lines"></i> ${esc(rep.name)}</h4>
                                        <p style="margin:0;color:#667085;font-size:12px;">Submitted by ${esc(rep.by)} on ${esc(rep.submitted)}</p>
                                    </div>
                                    <a href="../../${esc(rep.path)}" target="_blank" download class="workspace-filter-btn" style="text-decoration:none;"><i class="fa-solid fa-download"></i>&nbsp;Download Report</a>
                                </div>
                                <p style="margin:12px 0 0;color:#344054;font-size:13px;line-height:1.5;"><strong>Summary:</strong> ${esc(rep.summary).replace(/\n/g, "<br>")}</p>
                            </div>
                        `;
                    }
                    reportForm.hidden = true;
                    loadWorkspaceState();
                    showToast("Final report submitted for Organization review!");
                })
                .catch(function () {
                    if (btn) btn.disabled = false;
                    showToast("Failed to upload final report.", true);
                });
        });
    }

    /* =====================================
       LIVE POLLING SYNC WITH ORGANIZATION
    ===================================== */
    function loadWorkspaceState() {
        api("load", { team_id: teamId, after_id: 0 }).then(function (res) {
            if (!res.ok) return;
            renderTasks(res.tasks || []);
            renderFiles(res.files || []);
            renderMessages(res.messages || []);
        });
    }

    if (chatMessages) {
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }

    setInterval(function () {
        if (!document.hidden) {
            loadWorkspaceState();
        }
    }, 5000);
});
