<div class="univ-modal-overlay" id="addModal">
    <div class="univ-modal">
        <div class="univ-modal-header">
            <h3>Add New University</h3>
            <button class="univ-modal-close" id="closeAddModal" type="button">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <form class="univ-modal-body" action="" method="post">
            <input type="hidden" name="action" value="add">
            <div class="form-group">
                <label class="form-label">University Name <span style="color:#ef4444;">*</span></label>
                <div class="univ-combobox" id="addUnivCombobox">
                    <div class="univ-combobox-input-wrap">
                        <input type="text" class="form-input univ-combobox-input" name="university" id="addUnivInput" placeholder="Select or type university name" autocomplete="off" required>
                        <button type="button" class="univ-combobox-toggle" id="addUnivToggle" title="Toggle university list" tabindex="-1">
                            <span class="material-symbols-outlined">expand_more</span>
                        </button>
                    </div>
                    <div class="univ-combobox-dropdown" id="addUnivDropdown">
                        <div class="univ-combobox-list" id="addUnivList"></div>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Faculty Name <span style="color:#ef4444;">*</span></label>
                <input type="text" class="form-input" name="faculty" id="addFacInput" placeholder="e.g. School Of Technology" required>
            </div>
            <div class="form-group">
                <label class="form-label">Email Domain <span style="color:#ef4444;">*</span></label>
                <input type="text" name="domain" id="addDomainInput" class="form-input" placeholder="e.g. cmb.ac.lk" required>
                <small style="color:#9ca3af;font-size:11px;margin-top:4px;display:block;">Enter without the @
                    symbol</small>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Location</label>
                    <input type="text" name="location" id="addLocationInput" class="form-input" placeholder="e.g. Colombo, Sri Lanka">
                </div>
                <div class="form-group">
                    <label class="form-label">Status</label>
                    <select class="form-input" name="status">
                        <option value="Active">Active</option>
                        <option value="Hold">Hold</option>
                        <option value="Inactive">Inactive</option>
                    </select>
                </div>
            </div>
            <div class="univ-modal-footer">
                <button type="button" class="btn-outline" id="cancelAddModal">Cancel</button>
                <button type="submit" class="btn-add-university" style="margin:0;">
                    <span class="material-symbols-outlined" style="font-size:16px;">add</span>
                    Add University
                </button>
            </div>
        </form>
    </div>
</div>