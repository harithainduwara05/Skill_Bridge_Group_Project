<div class="pm-modal-backdrop" id="pmAddModal">
    <div class="pm-modal-dialog">
        <div class="pm-modal-header">
            <h3>Add New Student Project</h3>
            <button type="button" class="pm-modal-close" onclick="closeAddProjectModal()">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <form id="pmAddProjectForm">
            <div class="pm-modal-body">
                <div class="pm-form-group">
                    <label for="pmInputTitle">Project Title *</label>
                    <input type="text" id="pmInputTitle" class="pm-form-control" placeholder="e.g. AI-Powered Smart Agriculture" required>
                </div>
                <div class="pm-form-row">
                    <div class="pm-form-group">
                        <label for="pmInputCategory">Project Category / Type *</label>
                        <input type="text" id="pmInputCategory" class="pm-form-control" placeholder="e.g. Education Technology" required>
                    </div>
                    <div class="pm-form-group">
                        <label for="pmInputOrg">Organization / University *</label>
                        <input type="text" id="pmInputOrg" class="pm-form-control" placeholder="e.g. UCSC" required>
                    </div>
                </div>
                <div class="pm-form-row">
                    <div class="pm-form-group">
                        <label for="pmInputSkills">Required Skills (Comma separated)</label>
                        <input type="text" id="pmInputSkills" class="pm-form-control" placeholder="e.g. Python, ML, React">
                    </div>
                    <div class="pm-form-group">
                        <label for="pmInputStatus">Project Status</label>
                        <select id="pmInputStatus" class="pm-form-control">
                            <option value="Active" selected>Active</option>
                            <option value="Review">Review</option>
                            <option value="Completed">Completed</option>
                            <option value="Rejected">Rejected</option>
                        </select>
                    </div>
                </div>
                <div class="pm-form-group">
                    <label for="pmInputDesc">Project Overview / Description</label>
                    <textarea id="pmInputDesc" class="pm-form-control" placeholder="Brief summary of project objectives and scope..."></textarea>
                </div>
            </div>
            <div class="pm-modal-footer">
                <button type="button" class="pm-btn-secondary" onclick="closeAddProjectModal()">Cancel</button>
                <button type="submit" class="pm-btn-primary">Add Project</button>
            </div>
        </form>
    </div>
</div>