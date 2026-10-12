<div class="im-modal-backdrop" id="imAddModal">
    <div class="im-modal-dialog">
        <div class="im-modal-header">
            <h3>Add New Internship Opportunity</h3>
            <button type="button" class="im-modal-close" onclick="closeAddInternshipModal()">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <form id="imAddInternshipForm">
            <div class="im-modal-body">
                <div class="im-form-group">
                    <label for="imInputPosition">Position Title *</label>
                    <input type="text" id="imInputPosition" class="im-form-control" placeholder="e.g. Full-Stack Developer Intern" required>
                </div>
                <div class="im-form-row">
                    <div class="im-form-group">
                        <label for="imInputCompany">Company Name *</label>
                        <input type="text" id="imInputCompany" class="im-form-control" placeholder="e.g. Sysco Labs" required>
                    </div>
                    <div class="im-form-group">
                        <label for="imInputIndustry">Industry Category *</label>
                        <input type="text" id="imInputIndustry" class="im-form-control" placeholder="e.g. Software Development" required>
                    </div>
                </div>
                <div class="im-form-row">
                    <div class="im-form-group">
                        <label for="imInputMode">Work Type & Location</label>
                        <input type="text" id="imInputMode" class="im-form-control" placeholder="e.g. Full-time • Remote">
                    </div>
                    <div class="im-form-group">
                        <label for="imInputDeadline">Application Deadline</label>
                        <input type="text" id="imInputDeadline" class="im-form-control" placeholder="e.g. 30 Oct 2026">
                    </div>
                </div>
                <div class="im-form-group">
                    <label for="imInputSkills">Required Skills (Comma separated)</label>
                    <input type="text" id="imInputSkills" class="im-form-control" placeholder="e.g. React, Node.js, SQL">
                </div>
            </div>
            <div class="im-modal-footer">
                <button type="button" class="im-btn-secondary" onclick="closeAddInternshipModal()">Cancel</button>
                <button type="submit" class="im-btn-primary">Add Internship</button>
            </div>
        </form>
    </div>
</div>