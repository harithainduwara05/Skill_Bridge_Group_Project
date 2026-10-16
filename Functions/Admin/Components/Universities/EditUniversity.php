<div class="univ-modal-overlay" id="editModal">
    <div class="univ-modal">
        <div class="univ-modal-header">
            <h3>Edit University</h3>
            <button class="univ-modal-close" id="closeEditModal" type="button">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <form class="univ-modal-body" action="" method="post">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="original_domain" id="editOrigDomain">

            <div class="form-group">
                <label class="form-label">University Name <span style="color:#ef4444;">*</span></label>
                <div class="univ-combobox" id="editUnivCombobox">
                    <div class="univ-combobox-input-wrap">
                        <input type="text" class="form-input univ-combobox-input" name="university" id="editUni" placeholder="Select or type university name" autocomplete="off" required>
                        <button type="button" class="univ-combobox-toggle" id="editUnivToggle" title="Toggle university list" tabindex="-1">
                            <span class="material-symbols-outlined">expand_more</span>
                        </button>
                    </div>
                    <div class="univ-combobox-dropdown" id="editUnivDropdown">
                        <div class="univ-combobox-list" id="editUnivList"></div>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Faculty Name <span style="color:#ef4444;">*</span></label>
                <input type="text" class="form-input" name="faculty" id="editFac" required>
            </div>
            <div class="form-group">
                <label class="form-label">Email Domain <span style="color:#ef4444;">*</span></label>
                <input type="text" name="domain" id="editDomain" class="form-input" required>
                <small style="color:#9ca3af;font-size:11px;margin-top:4px;display:block;">Enter without the @
                    symbol</small>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Location</label>
                    <input type="text" name="location" id="editLocation" class="form-input">
                </div>
                <div class="form-group">
                    <label class="form-label">Status</label>
                    <select class="form-input" name="status" id="editStatus">
                        <option value="Active">Active</option>
                        <option value="Hold">Hold</option>
                        <option value="Inactive">Inactive</option>
                    </select>
                    <small id="inactiveStatusNotice" style="color:#b45309; font-size:11.5px; margin-top:5px; display:none; line-height:1.4;">
                        <span class="material-symbols-outlined" style="font-size:13px; vertical-align:middle;">warning</span>
                        <strong>Permanent Change:</strong> Once saved as <em>Inactive</em>, this university cannot be edited or modified again.
                    </small>
                </div>
            </div>
            <div class="univ-modal-footer">
                <button type="button" class="btn-outline" id="cancelEditModal">Cancel</button>
                <button type="submit" class="btn-add-university" style="margin:0;">
                    <span class="material-symbols-outlined" style="font-size:16px;">save</span>
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>