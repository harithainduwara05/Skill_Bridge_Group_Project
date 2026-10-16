<div class="pm-modal-backdrop" id="pmHoldModal">
    <div class="pm-modal-dialog" style="max-width: 520px;">
        <div class="pm-modal-header" style="border-bottom: 1px solid #fef3c7; background-color: #fffbeb;">
            <div style="display: flex; align-items: center; gap: 8px;">
                <span class="material-symbols-outlined" style="color: #d97706; font-size: 22px;">pause_circle</span>
                <h3 style="color: #92400e; margin: 0; font-size: 17px; font-weight: 700;">Place Project on Hold</h3>
            </div>
            <button type="button" class="pm-modal-close" onclick="closeHoldModal()">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <form id="pmHoldProjectForm">
            <div class="pm-modal-body">
                <div style="background: #fffbeb; border: 1px solid #fde68a; border-radius: 10px; padding: 12px 14px; font-size: 12.5px; color: #92400e; line-height: 1.45;">
                    <strong>Ethical Moderation Notice:</strong> Projects should not be rejected on first sight. Placing on Hold grants the team a fair grace period to address complaints or workflow issues before any permanent action is considered.
                </div>
                <p style="font-size: 13.5px; color: #475569; margin: 0; line-height: 1.5;">
                    Placing <strong id="pmHoldProjectTitle" style="color: #0f172a;">this project</strong> (<span id="pmHoldProjectId" style="font-weight: 700;">#PR001</span>) on Hold.
                </p>
                <div class="pm-form-group">
                    <label for="pmHoldReason" style="font-weight: 600; font-size: 13px; color: #1e293b;">
                        Issue / Complaint Reason *
                    </label>
                    <textarea id="pmHoldReason" class="pm-form-control" rows="3" style="height: 85px; resize: vertical;" placeholder="Specify the complaint or issue (e.g., Inappropriate content report, milestone discrepancy, team conflict)..." required></textarea>
                </div>
                <div class="pm-form-group">
                    <label for="pmHoldGracePeriod" style="font-weight: 600; font-size: 13px; color: #1e293b;">
                        Grace Period (Days to respond) *
                    </label>
                    <select id="pmHoldGracePeriod" class="pm-form-control">
                        <option value="7">7 Days (Standard Grace Period)</option>
                        <option value="3">3 Days (Urgent Review)</option>
                        <option value="5">5 Days (Moderate Review)</option>
                        <option value="14">14 Days (Extended Inquiry)</option>
                    </select>
                    <span style="font-size: 11.5px; color: #64748b;">If the team fails to provide a proper justification within this period, the project can be permanently rejected.</span>
                </div>
            </div>
            <div class="pm-modal-footer">
                <button type="button" class="pm-btn-secondary" onclick="closeHoldModal()">Cancel</button>
                <button type="submit" class="pm-btn-hold">
                    <span class="material-symbols-outlined" style="font-size: 18px;">pause_circle</span>
                    <span>Confirm Hold</span>
                </button>
            </div>
        </form>
    </div>
</div>