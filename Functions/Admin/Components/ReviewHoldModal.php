<div class="pm-modal-backdrop" id="pmReviewHoldModal">
    <div class="pm-modal-dialog" style="max-width: 620px;">
        <div class="pm-modal-header" style="border-bottom: 1px solid #e2e8f0; background-color: #f8fafc;">
            <div style="display: flex; align-items: center; gap: 8px;">
                <span class="material-symbols-outlined" id="pmReviewModalHeaderIcon" style="color: #0b2246; font-size: 24px;">task_alt</span>
                <div>
                    <h3 id="pmReviewModalHeaderTitle" style="color: #0f172a; margin: 0; font-size: 17px; font-weight: 700;">Hold Review & Decision</h3>
                    <span id="pmReviewModalHeaderSub" style="font-size: 12px; color: #64748b;">Compare Admin pause reason against Team's submitted justification</span>
                </div>
            </div>
            <button type="button" class="pm-modal-close" onclick="closeReviewHoldModal()">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>

        <!-- STEP 1: Review & Decision Comparison -->
        <div id="pmReviewStep1" class="pm-modal-body" style="gap: 14px; display: flex; flex-direction: column;">
            <!-- Project Header Info -->
            <div style="display: flex; align-items: center; justify-content: space-between; background: #ffffff; border: 1px solid #e2e8f0; padding: 12px 16px; border-radius: 12px;">
                <div>
                    <span id="pmReviewProjectId" style="font-weight: 700; color: #0b2246; font-size: 15px;">#PR002</span>
                    <h4 id="pmReviewProjectTitle" style="margin: 2px 0 0; font-size: 14px; font-weight: 600; color: #334155;">Healthcare Management System</h4>
                </div>
                <span class="pm-status-pill status-hold" id="pmReviewStatusBadge">
                    <span class="material-symbols-outlined" style="font-size: 13px; margin-right: 3px;">schedule</span>Hold (5d left)
                </span>
            </div>

            <!-- Two Comparison Cards -->
            <div style="display: flex; flex-direction: column; gap: 12px;">
                
                <!-- Card 1: Admin's Pause Reason -->
                <div class="pm-review-card-admin">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 6px;">
                        <span style="font-size: 13px; font-weight: 700; color: #92400e; display: flex; align-items: center; gap: 6px;">
                            <span class="material-symbols-outlined" style="font-size: 18px;">pause_circle</span>
                            Admin's Pause Reason (Why project was placed on Hold)
                        </span>
                        <span id="pmReviewHoldDaysBadge" style="font-size: 11.5px; font-weight: 600; color: #b45309; background: #fef3c7; padding: 2px 8px; border-radius: 6px;">Grace Period: 5d left</span>
                    </div>
                    <p id="pmReviewAdminReason" style="font-size: 13px; color: #78350f; margin: 0; line-height: 1.5;">
                        Discrepancy in project architecture and pending team compliance explanation.
                    </p>
                </div>

                <!-- Card 2: Team's Submitted Reason / Explanation -->
                <div class="pm-review-card-team">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 6px;">
                        <span style="font-size: 13px; font-weight: 700; color: #166534; display: flex; align-items: center; gap: 6px;">
                            <span class="material-symbols-outlined" style="font-size: 18px;">forum</span>
                            Team's Submitted Justification / Explanation
                        </span>
                        <span id="pmReviewTeamStatusBadge" style="font-size: 11.5px; font-weight: 600; color: #15803d; background: #dcfce7; padding: 2px 8px; border-radius: 6px;">Response Submitted</span>
                    </div>
                    <p id="pmReviewTeamResponse" style="font-size: 13px; color: #14532d; margin: 0; line-height: 1.5;">
                        We have uploaded the revised software architecture diagram (v2.1) and clarified student roles in accordance with platform guidelines. Please review the updated milestones.
                    </p>
                </div>

            </div>

            <!-- Decision Options Guidance -->
            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 10px 14px; font-size: 12px; color: #64748b; line-height: 1.45;">
                <strong style="color: #334155;">Final Decision:</strong> Choose <strong>Reactivate Project</strong> to restore it to Active if the justification is valid. Choose <strong>Permanently Reject</strong> to proceed to state the final rejection reason.
            </div>
        </div>

        <!-- STEP 1 FOOTER -->
        <div id="pmReviewFooter1" class="pm-modal-footer" style="display: flex; align-items: center; justify-content: space-between; gap: 12px;">
            <button type="button" class="pm-btn-secondary" onclick="closeReviewHoldModal()">Cancel</button>
            <div style="display: flex; align-items: center; gap: 10px;">
                <!-- Permanently Reject Button -> Navigates to Step 2 -->
                <button type="button" class="pm-btn-reject" onclick="goToRejectStep()" style="height: 40px;">
                    <span class="material-symbols-outlined" style="font-size: 18px;">block</span>
                    <span>Permanently Reject</span>
                </button>
                <!-- Reactivate to Active Button -->
                <button type="button" class="pm-btn-reactivate" onclick="confirmReactivateFromReview()" style="height: 40px; padding: 0 18px;">
                    <span class="material-symbols-outlined" style="font-size: 18px;">check_circle</span>
                    <span>Reactivate Project</span>
                </button>
            </div>
        </div>

        <!-- STEP 2: State Reason for Permanent Rejection -->
        <div id="pmReviewStep2" class="pm-modal-body" style="gap: 14px; display: none; flex-direction: column;">
            <!-- Warning Banner -->
            <div style="background: #fef2f2; border: 1.5px solid #fecaca; border-radius: 12px; padding: 14px 16px;">
                <div style="display: flex; align-items: center; gap: 8px; color: #b91c1c; font-weight: 700; font-size: 13.5px; margin-bottom: 4px;">
                    <span class="material-symbols-outlined" style="font-size: 20px;">report_problem</span>
                    <span>Permanent Terminal Action Warning</span>
                </div>
                <p style="font-size: 12.5px; color: #991b1b; margin: 0; line-height: 1.5;">
                    Once this project is permanently rejected, it <strong>CANNOT</strong> be reactivated under any circumstance. Please provide the official reason for permanent termination.
                </p>
            </div>

            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 10px 14px; font-size: 13px; color: #334155;">
                Rejecting Project: <strong id="pmReviewStep2ProjectTitle">Healthcare Management System</strong> (<span id="pmReviewStep2ProjectId" style="font-weight: 700; color: #0b2246;">#PR002</span>)
            </div>

            <!-- Rejection Category Dropdown -->
            <div class="pm-form-group">
                <label for="pmFinalRejectCategory" style="font-weight: 600; font-size: 13px; color: #1e293b;">
                    Primary Rejection Category *
                </label>
                <select id="pmFinalRejectCategory" class="pm-form-control">
                    <option value="Unsatisfactory Justification">Unsatisfactory or Inadequate Justification</option>
                    <option value="Unresolved Complaint">Unresolved Complaint within Grace Period</option>
                    <option value="Policy Violation">Violation of Platform Policies / Guidelines</option>
                    <option value="Plagiarism / Falsification">Plagiarism or Falsified Project Information</option>
                    <option value="Non-Responsive Team">Team Failed to Respond before Deadline</option>
                    <option value="Other">Other Specific Grounds</option>
                </select>
            </div>

            <!-- Detailed Rejection Reason Textarea -->
            <div class="pm-form-group">
                <label for="pmFinalRejectReason" style="font-weight: 600; font-size: 13px; color: #1e293b;">
                    Official Rejection Reason & Findings *
                </label>
                <textarea id="pmFinalRejectReason" class="pm-form-control" rows="3" style="height: 85px; resize: vertical;" placeholder="Explain clearly why the team's explanation was rejected and why this project is being permanently terminated..." required></textarea>
                <span id="pmFinalRejectError" style="display: none; font-size: 12px; color: #dc2626; font-weight: 500;">Please state the specific reason for rejecting this project.</span>
            </div>
        </div>

        <!-- STEP 2 FOOTER -->
        <div id="pmReviewFooter2" class="pm-modal-footer" style="display: none; align-items: center; justify-content: space-between; gap: 12px;">
            <button type="button" class="pm-btn-secondary" onclick="goToReviewStep()" style="display: inline-flex; align-items: center; gap: 6px;">
                <span class="material-symbols-outlined" style="font-size: 18px;">arrow_back</span>
                <span>Back to Review</span>
            </button>
            <button type="button" class="pm-btn-danger" onclick="executePermanentRejection()" style="display: inline-flex; align-items: center; gap: 6px;">
                <span class="material-symbols-outlined" style="font-size: 18px;">block</span>
                <span>Confirm Permanent Rejection</span>
            </button>
        </div>
    </div>
</div>