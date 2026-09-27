document.addEventListener("DOMContentLoaded", function () {
    if (window.location.hash === "#resumePreview") {
        const preview = document.getElementById("resumePreview");
        if (preview) preview.scrollIntoView({ behavior: "smooth", block: "start" });
    }

    const downloadButton = document.getElementById("downloadCvBtn");
    const resumePaper = document.querySelector("#resumePreview .resume-paper");
    if (downloadButton && resumePaper) {
        downloadButton.addEventListener("click", function () {
            const content = resumePaper.cloneNode(true);
            const candidateName = content.querySelector("h2")?.textContent.trim() || "Candidate";
            const safeName = candidateName.toLowerCase().replace(/[^a-z0-9]+/g, "-").replace(/^-|-$/g, "") || "candidate";
            const documentHtml = `<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>${candidateName.replace(/[&<>"']/g, "") } - CV</title><style>body{margin:0;padding:32px;background:#f2f5f9;color:#17273a;font:15px/1.55 Arial,sans-serif}.resume-paper{max-width:760px;margin:auto;padding:40px;background:#fff;box-shadow:0 4px 18px #09274618;overflow-wrap:anywhere}.resume-paper h2{margin:0 0 6px;color:#092746;font-size:24px}.resume-contact{margin:0;color:#606977}.resume-divider{height:2px;margin:20px 0;background:#092746}.resume-section{margin:0 0 20px}.resume-section h4{margin:0 0 8px;color:#a95b00;font-size:12px;letter-spacing:.05em}.resume-section strong{display:block;margin:0 0 5px}.resume-section p{margin:0 0 10px;color:#3d4653;overflow-wrap:anywhere}@media(max-width:600px){body{padding:12px}.resume-paper{padding:20px}}</style></head><body>${content.outerHTML}</body></html>`;
            const file = new Blob([documentHtml], { type: "text/html;charset=utf-8" });
            const url = URL.createObjectURL(file);
            const link = document.createElement("a");
            link.href = url;
            link.download = `${safeName}-cv.html`;
            document.body.appendChild(link);
            link.click();
            link.remove();
            window.setTimeout(function () { URL.revokeObjectURL(url); }, 1000);
        });
    }
});
