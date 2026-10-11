<div class="pm-modal-backdrop" id="pmDetailsModal">
    <div class="pm-modal-dialog">
        <div class="pm-modal-header">
            <h3 id="pmDetailTitle">Project Overview</h3>
            <button type="button" class="pm-modal-close" onclick="closeProjectDetailsModal()">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <div class="pm-modal-body">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
                <span style="font-weight: 700; color: #0b2246; font-size: 16px;" id="pmDetailId">#PR001</span>
                <span class="pm-status-pill status-active" id="pmDetailStatus">Active</span>
            </div>
            <div style="background: #f8fafc; border-radius: 12px; padding: 14px 16px; display: flex; flex-direction: column; gap: 8px;">
                <div style="font-size: 13px; color: #64748b;">
                    <strong>Category:</strong> <span id="pmDetailCategory">Technology</span>
                </div>
                <div style="font-size: 13px; color: #64748b;">
                    <strong>Organization:</strong> <span id="pmDetailOrg">UCSC</span>
                </div>
                <div style="font-size: 13px; color: #64748b;">
                    <strong>Required Skills:</strong> <span id="pmDetailSkills">Python, ML</span>
                </div>
            </div>

            <!-- Hold Status Banner (shown when status is Hold) -->
            <div id="pmDetailHoldBox" class="pm-hold-banner" style="display: none;">
                <div class="pm-hold-banner-header">
                    <span class="material-symbols-outlined" style="font-size: 20px;">pause_circle</span>
                    <span>Project Temporarily On Hold</span>
                </div>
                <p class="pm-hold-banner-text" id="pmDetailHoldReason">
                    Complaint received or workflow review required. Team has been granted a grace period to clarify.
                </p>
                <div class="pm-hold-meta-row">
                    <span><strong>Grace Period Remaining:</strong> <span id="pmDetailHoldDeadline">5 Days Left</span></span>
                    <span style="font-size: 11.5px; background: #fbbf24; color: #78350f; padding: 2px 8px; border-radius: 4px; font-weight: 700;">Awaiting Response</span>
                </div>
            </div>

            <!-- Permanent Rejected Banner (shown when status is Rejected) -->
            <div id="pmDetailRejectedBox" class="pm-rejected-banner" style="display: none;">
                <div class="pm-rejected-banner-header">
                    <span class="material-symbols-outlined" style="font-size: 20px;">block</span>
                    <span>Permanently Rejected</span>
                </div>
                <p class="pm-rejected-banner-text" id="pmDetailRejectReason" style="margin-bottom: 8px;">
                    Reason: Violated submission criteria and failed to provide acceptable justification.
                </p>
                <div style="font-size: 11.5px; color: #b91c1c; font-weight: 600; display: flex; align-items: center; gap: 5px;">
                    <span class="material-symbols-outlined" style="font-size: 16px;">lock</span>
                    <span>System Policy: Once rejected, this project CANNOT be reactivated.</span>
                </div>
            </div>

            <!-- Dynamic Moderation Action Area -->
            <div id="pmDetailActionsArea" style="margin-top: 16px; padding-top: 14px; border-top: 1px solid #f1f5f9;">
                <div id="pmDetailActionControls">
                    <!-- Populated dynamically by project_management.js -->
                </div>
            </div>
        </div>
        <div class="pm-modal-footer">
            <button type="button" class="pm-btn-secondary" onclick="closeProjectDetailsModal()">Close</button>
        </div>
    </div>
</div>