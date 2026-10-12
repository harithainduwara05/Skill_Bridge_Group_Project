<div class="im-modal-backdrop" id="imSuspendModal">
    <div class="im-modal-dialog" style="max-width: 560px;">
        <div class="im-modal-header im-suspend-header">
            <div class="im-suspend-header-title">
                <div class="im-suspend-header-icon">
                    <span class="material-symbols-outlined">warning</span>
                </div>
                <div>
                    <h3 style="color: #991b1b; margin: 0; font-size: 17px; font-weight: 700;">Suspend Internship Listing</h3>
                    <span style="font-size: 12.5px; color: #b91c1c;">Deactivate post and dispatch formal notification to employer</span>
                </div>
            </div>
            <button type="button" class="im-modal-close" onclick="closeSuspendModal()">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>

        <form id="imSuspendForm">
            <input type="hidden" id="imSuspendTargetId" value="">
            
            <div class="im-modal-body">
                
                <!-- Target Posting Summary Callout -->
                <div class="im-suspend-target-card">
                    <div class="im-suspend-target-row">
                        <span class="im-suspend-target-label">Target Internship:</span>
                        <strong class="im-suspend-target-value" id="imSuspendTargetPosition">Software Engineer Intern</strong>
                        <span class="im-suspend-target-pill" id="imSuspendTargetIdBadge">#INT001</span>
                    </div>
                    <div class="im-suspend-target-row">
                        <span class="im-suspend-target-label">Target Employer:</span>
                        <strong class="im-suspend-target-value" id="imSuspendTargetCompany">Tech Solutions</strong>
                    </div>
                    <div class="im-suspend-notice-alert">
                        <span class="material-symbols-outlined">info</span>
                        <span>This listing will be hidden immediately from applicants. The reason and message below will be delivered as a formal notification to the company.</span>
                    </div>
                </div>

                <!-- Reason Category Selection -->
                <div class="im-form-group">
                    <label for="imSuspendCategory">Violation / Suspension Category *</label>
                    <select id="imSuspendCategory" class="im-form-control" required>
                        <option value="Platform Policy Violation" selected>Platform Policy Violation</option>
                        <option value="Student Complaint Received">Student / Applicant Complaint Received</option>
                        <option value="Misleading Job Description or Stipend">Misleading Job Description or Stipend Details</option>
                        <option value="Unresponsive Employer / Inactive Post">Unresponsive Employer / Inactive Post</option>
                        <option value="Discriminatory or Inappropriate Requirements">Discriminatory or Inappropriate Requirements</option>
                        <option value="Other Policy Breach">Other Policy Breach</option>
                    </select>
                </div>

                <!-- Specific Reason / Internal Note -->
                <div class="im-form-group">
                    <label for="imSuspendReason">Specific Reason / Complaint Summary *</label>
                    <input type="text" id="imSuspendReason" class="im-form-control" placeholder="e.g. Complaint regarding undisclosed unpaid overtime exceeding policy limit..." required>
                </div>

                <!-- Notification Message to Company -->
                <div class="im-form-group">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 2px;">
                        <label for="imCompanyMessage">Notification Message to Company (Sent to Employer) *</label>
                        <span class="im-notif-badge">
                            <span class="material-symbols-outlined" style="font-size: 13px;">mail</span>
                            Direct Notification
                        </span>
                    </div>
                    <textarea id="imCompanyMessage" class="im-form-control" rows="4" style="height: 85px; font-family: 'Inter', sans-serif; font-size: 13px; line-height: 1.45; resize: vertical;" required placeholder="Write message explaining the suspension reason to the company..."></textarea>
                    <small style="font-size: 11.5px; color: #64748b; margin-top: 3px;">
                        The employer will receive this in their notification feed and can submit clarification or contact admin to resolve the issue.
                    </small>
                </div>

            </div>

            <div class="im-modal-footer">
                <button type="button" class="im-btn-secondary" onclick="closeSuspendModal()">Cancel</button>
                <button type="submit" class="im-btn-suspend-submit">
                    <span class="material-symbols-outlined" style="font-size: 18px;">notifications_active</span>
                    <span>Confirm Suspension & Notify Company</span>
                </button>
            </div>
        </form>
    </div>
</div>
