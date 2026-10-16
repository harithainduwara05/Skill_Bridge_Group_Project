<div class="im-modal-backdrop" id="imReviewSuspensionModal">
    <div class="im-modal-dialog" style="max-width: 600px;">
        <div class="im-modal-header" style="border-bottom: 1px solid #fde68a; background-color: #fffbeb;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <div style="width: 38px; height: 38px; border-radius: 10px; background-color: #fef3c7; color: #d97706; display: flex; align-items: center; justify-content: center;">
                    <span class="material-symbols-outlined" style="font-size: 22px;">rate_review</span>
                </div>
                <div>
                    <h3 style="color: #92400e; margin: 0; font-size: 17px; font-weight: 700;">Review Suspension & Resolution</h3>
                    <span style="font-size: 12px; color: #b45309;">Assess company's remedial actions to decide between Reactivation or Termination</span>
                </div>
            </div>
            <button type="button" class="im-modal-close" onclick="closeReviewSuspensionModal()">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>

        <div class="im-modal-body" style="gap: 16px; display: flex; flex-direction: column;">
            <input type="hidden" id="imRevTargetId" value="">

            <!-- Target Posting Header -->
            <div style="display: flex; align-items: center; justify-content: space-between; background: #ffffff; border: 1px solid #e2e8f0; padding: 12px 16px; border-radius: 12px;">
                <div>
                    <span id="imRevId" style="font-weight: 800; color: #091a30; font-size: 15px;">#INT006</span>
                    <h4 id="imRevPosition" style="margin: 2px 0 0; font-size: 14px; font-weight: 600; color: #1e293b;">Data Analytics Intern</h4>
                    <span id="imRevCompany" style="font-size: 12px; color: #64748b;">Apex Data Corp</span>
                </div>
                <span class="im-status-badge status-suspended" id="imRevStatusBadge">
                    <span class="material-symbols-outlined" style="font-size: 13px;">pause_circle</span> Suspended
                </span>
            </div>

            <!-- Two Comparison Cards -->
            <div style="display: flex; flex-direction: column; gap: 12px;">
                <!-- 1. Suspension Reason (Admin's Issue) -->
                <div style="background-color: #fffbeb; border: 1px solid #fde68a; border-radius: 12px; padding: 14px 16px;">
                    <div style="display: flex; align-items: center; gap: 6px; margin-bottom: 6px; color: #92400e; font-weight: 700; font-size: 13px;">
                        <span class="material-symbols-outlined" style="font-size: 18px; color: #d97706;">warning</span>
                        <span>Reason for Suspension (Recorded Policy Violation / Complaint)</span>
                    </div>
                    <p id="imRevReasonText" style="margin: 0; font-size: 13px; color: #78350f; line-height: 1.5;">
                        Reported for deceptive stipend information and policy non-compliance.
                    </p>
                </div>

                <!-- 2. Company's Submitted Action / Remedial Response -->
                <div id="imRevActionCard" style="background-color: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 12px; padding: 14px 16px;">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 6px;">
                        <div style="display: flex; align-items: center; gap: 6px; color: #166534; font-weight: 700; font-size: 13px;">
                            <span class="material-symbols-outlined" style="font-size: 18px; color: #16a34a;">fact_check</span>
                            <span>Company's Remedial Action / Resolution Taken</span>
                        </div>
                        <span id="imRevActionBadge" style="font-size: 11px; font-weight: 700; background: #dcfce7; color: #15803d; padding: 2px 8px; border-radius: 6px;">Action Taken</span>
                    </div>
                    <p id="imRevActionText" style="margin: 0; font-size: 13px; color: #14532d; line-height: 1.5;">
                        We have revised the official internship agreement, clearly specified the LKR 50,000 monthly allowance, and agreed to adhere strictly to the platform's fair recruitment guidelines.
                    </p>
                </div>

                <!-- 3. If No Action Submitted Yet (Conditional display) -->
                <div id="imRevNoActionCard" style="display: none; background-color: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 12px; padding: 14px 16px;">
                    <div style="display: flex; align-items: center; gap: 6px; margin-bottom: 4px; color: #64748b; font-weight: 700; font-size: 13px;">
                        <span class="material-symbols-outlined" style="font-size: 18px; color: #94a3b8;">pending</span>
                        <span>No Remedial Action Submitted</span>
                    </div>
                    <p style="margin: 0; font-size: 12.5px; color: #64748b; line-height: 1.45;">
                        The employer has not submitted any response, clarification, or corrective measures yet. You may grant additional time or permanently terminate the listing if the response deadline has elapsed.
                    </p>
                </div>
            </div>

            <!-- Decision Notice -->
            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 10px 14px; font-size: 12px; color: #475569; line-height: 1.45;">
                <strong>Administrative Decision Policy:</strong> If satisfied with the company's corrective steps, choose <strong>Reactivate Internship</strong> to restore student visibility. If the response is unacceptable or absent, choose <strong>Permanently Terminate</strong> (this irreversible action halts the internship forever).
            </div>
        </div>

        <div class="im-modal-footer" style="display: flex; align-items: center; justify-content: space-between; gap: 12px;">
            <button type="button" class="im-btn-secondary" onclick="closeReviewSuspensionModal()">Cancel</button>
            <div style="display: flex; align-items: center; gap: 10px;">
                <!-- Terminate Button (Red) -->
                <button type="button" class="im-btn-danger" id="imBtnExecuteTerminate" onclick="executeTerminationFromReview()" style="display: inline-flex; align-items: center; gap: 6px; height: 38px; padding: 0 16px; background-color: #dc2626; color: #ffffff; border: none; border-radius: 8px; font-weight: 600; font-size: 13px; cursor: pointer;">
                    <span class="material-symbols-outlined" style="font-size: 18px;">cancel</span>
                    <span>Permanently Terminate</span>
                </button>
                <!-- Reactivate Button (Green) -->
                <button type="button" class="im-btn-reactivate" id="imBtnExecuteReactivate" onclick="executeReactivationFromReview()" style="display: inline-flex; align-items: center; gap: 6px; height: 38px; padding: 0 16px; background-color: #10b981; color: #ffffff; border: none; border-radius: 8px; font-weight: 600; font-size: 13px; cursor: pointer;">
                    <span class="material-symbols-outlined" style="font-size: 18px;">check_circle</span>
                    <span>Satisfied - Reactivate</span>
                </button>
            </div>
        </div>
    </div>
</div>