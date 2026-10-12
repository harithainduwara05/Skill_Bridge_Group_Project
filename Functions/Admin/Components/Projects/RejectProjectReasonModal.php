<div class="pm-modal-backdrop" id="pmRejectModal">
    <div class="pm-modal-dialog" style="max-width: 520px;">
        <div class="pm-modal-header" style="border-bottom: 1px solid #fee2e2; background-color: #fff5f5;">
            <div style="display: flex; align-items: center; gap: 8px;">
                <span class="material-symbols-outlined" style="color: #dc2626; font-size: 22px;">report_problem</span>
                <h3 style="color: #991b1b; margin: 0; font-size: 17px; font-weight: 700;">Permanently Reject Project</h3>
            </div>
            <button type="button" class="pm-modal-close" onclick="closeRejectReasonModal()">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <form id="pmRejectProjectForm">
            <div class="pm-modal-body">
                <div style="background: #fef2f2; border: 1.5px solid #fecaca; border-radius: 10px; padding: 12px 14px; font-size: 12.5px; color: #991b1b; line-height: 1.45;">
                    <strong>Permanent Action Warning:</strong> Once a project is placed in the <strong>Rejected</strong> state, it <u>CANNOT</u> be reactivated under any circumstance. Only proceed if the grace period has elapsed or the team failed to provide acceptable justification.
                </div>
                <p style="font-size: 13.5px; color: #475569; margin: 0; line-height: 1.5;">
                    Rejecting <strong id="pmRejectProjectTitle" style="color: #0f172a;">this project</strong> (<span id="pmRejectProjectId" style="font-weight: 700;">#PR001</span>).
                </p>
                <div class="pm-form-group">
                    <label for="pmRejectReason" style="font-weight: 600; font-size: 13px; color: #1e293b;">
                        Final Rejection Reason *
                    </label>
                    <textarea id="pmRejectReason" class="pm-form-control" rows="4" style="height: 95px; resize: vertical;" placeholder="State final reason for permanent rejection (e.g., Unresolved complaint after 7-day grace period, confirmed policy violation, failure to respond)..." required></textarea>
                </div>
            </div>
            <div class="pm-modal-footer">
                <button type="button" class="pm-btn-secondary" onclick="closeRejectReasonModal()">Cancel</button>
                <button type="submit" class="pm-btn-danger">Confirm Final Rejection</button>
            </div>
        </form>
    </div>
</div>