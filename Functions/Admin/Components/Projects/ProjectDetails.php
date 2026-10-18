<div class="pm-modal-backdrop" id="pmDetailsModal">
    <div class="pm-modal-dialog" style="max-width: 680px; max-height: 90vh; display: flex; flex-direction: column;">
        
        <!-- Modal Header -->
        <div class="pm-modal-header" style="border-bottom: 1px solid #e2e8f0; padding: 16px 22px; background: #ffffff;">
            <div>
                <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 4px;">
                    <span style="font-weight: 800; color: #0b2246; font-size: 13.5px; background: #f1f5f9; padding: 2px 8px; border-radius: 6px;" id="pmDetailId">#PR001</span>
                    <span class="pm-status-pill status-active" id="pmDetailStatus">Active</span>
                </div>
                <h3 id="pmDetailTitle" style="margin: 0; font-size: 18px; font-weight: 700; color: #0f172a; line-height: 1.3;">Project Title</h3>
            </div>
            <button type="button" class="pm-modal-close" onclick="closeProjectDetailsModal()">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>

        <!-- Scrollable Modal Body -->
        <div class="pm-modal-body" style="padding: 20px 22px; overflow-y: auto; gap: 16px; display: flex; flex-direction: column;">

            <!-- Status Banner 1: Awaiting Review (When status is Review) -->
            <div id="pmDetailReviewBox" class="pm-review-banner" style="display: none;">
                <div class="pm-review-banner-header">
                    <span class="material-symbols-outlined" style="font-size: 20px;">hourglass_top</span>
                    <span>Awaiting Initial Moderation</span>
                </div>
                <p class="pm-review-banner-text">
                    This project was recently submitted by the organization. Students <strong>cannot</strong> see this project until you review the scope, deliverables, and ethical compliance below.
                </p>
                <div style="font-size: 12px; color: #92400e; font-weight: 600; display: flex; align-items: center; gap: 6px;">
                    <span class="material-symbols-outlined" style="font-size: 16px;">verified_user</span>
                    <span>Accept to publish to students, or Place on Hold if corrections are needed.</span>
                </div>
            </div>

            <!-- Status Banner 2: Hold (When status is Hold) -->
            <div id="pmDetailHoldBox" class="pm-hold-banner" style="display: none;">
                <div class="pm-hold-banner-header">
                    <span class="material-symbols-outlined" style="font-size: 20px;">pause_circle</span>
                    <span>Project Temporarily On Hold</span>
                </div>
                <p class="pm-hold-banner-text" id="pmDetailHoldReason">
                    Issue reported. Organization has been given a grace period to clarify.
                </p>
                <div class="pm-hold-meta-row">
                    <span><strong>Grace Period:</strong> <span id="pmDetailHoldDeadline">7 Days Left</span></span>
                    <span id="pmDetailHoldRespBadge" style="font-size: 11.5px; background: #fbbf24; color: #78350f; padding: 2px 8px; border-radius: 4px; font-weight: 700;">Awaiting Organization Response</span>
                </div>
            </div>

            <!-- Status Banner 3: Permanent Rejected (When status is Rejected) -->
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
                    <span>System Policy: Once rejected, this project cannot be reactivated.</span>
                </div>
            </div>

            <!-- Status Banner 4: Active (When status is Active) -->
            <div id="pmDetailActiveBox" class="pm-active-banner" style="display: none;">
                <div style="display: flex; align-items: center; gap: 8px; color: #15803d; font-weight: 700; font-size: 13.5px;">
                    <span class="material-symbols-outlined" style="font-size: 20px;">bolt</span>
                    <span>Live & In Progress</span>
                </div>
                <p style="font-size: 12.5px; color: #166534; margin: 4px 0 0; line-height: 1.45;">
                    This project has recruited its student team and is actively in progress.
                </p>
            </div>

            <!-- Status Banner 5: Published (When status is Published) -->
            <div id="pmDetailPublishedBox" class="pm-published-banner" style="display: none;">
                <div style="display: flex; align-items: center; gap: 8px; color: #0284c7; font-weight: 700; font-size: 13.5px;">
                    <span class="material-symbols-outlined" style="font-size: 20px;">public</span>
                    <span>Published & Open to Students</span>
                </div>
                <p style="font-size: 12.5px; color: #0369a1; margin: 4px 0 0; line-height: 1.45;">
                    This project has been approved by Admin and published. University students can discover and apply for this project. Once the student team is formed, the organization will transition it to Active.
                </p>
            </div>

            <!-- Key Parameters Grid -->
            <div class="pm-detail-info-grid">
                <div class="pm-detail-info-item">
                    <span class="pm-detail-info-label">Organization</span>
                    <span class="pm-detail-info-value" id="pmDetailOrg">UCSC</span>
                    <span style="font-size: 11px; color: #94a3b8;" id="pmDetailOrgEmail">org@domain.lk</span>
                </div>
                <div class="pm-detail-info-item">
                    <span class="pm-detail-info-label">Category</span>
                    <span class="pm-detail-info-value" id="pmDetailCategory">Technology</span>
                </div>
                <div class="pm-detail-info-item">
                    <span class="pm-detail-info-label">Difficulty & Duration</span>
                    <span class="pm-detail-info-value"><span id="pmDetailDifficulty">Intermediate</span> &bull; <span id="pmDetailDuration">8 Weeks</span></span>
                </div>
                <div class="pm-detail-info-item">
                    <span class="pm-detail-info-label">Team & Deadline</span>
                    <span class="pm-detail-info-value"><span id="pmDetailMembers">4</span> Members &bull; Due: <span id="pmDetailDeadline">2026-11-15</span></span>
                </div>
            </div>

            <!-- Skills & Tech Stack -->
            <div class="pm-detail-section">
                <h4 class="pm-detail-section-title">
                    <span class="material-symbols-outlined" style="font-size: 17px; color: #0284c7;">code</span>
                    <span>Required Tech Stack & Skills</span>
                </h4>
                <div id="pmDetailSkillsContainer" class="pm-skills-list" style="max-width: 100%;">
                    <!-- Populated dynamically -->
                </div>
            </div>

            <!-- Description -->
            <div class="pm-detail-section">
                <h4 class="pm-detail-section-title">
                    <span class="material-symbols-outlined" style="font-size: 17px; color: #0b2246;">description</span>
                    <span>Project Description</span>
                </h4>
                <div class="pm-detail-text-card" id="pmDetailDescription">
                    No description provided.
                </div>
            </div>

            <!-- Learning Objectives -->
            <div class="pm-detail-section">
                <h4 class="pm-detail-section-title">
                    <span class="material-symbols-outlined" style="font-size: 17px; color: #8b5cf6;">school</span>
                    <span>Learning Objectives for Students</span>
                </h4>
                <div class="pm-detail-text-card pm-detail-objectives" id="pmDetailObjectives">
                    No specific learning objectives listed.
                </div>
            </div>

            <!-- Expected Outcomes -->
            <div class="pm-detail-section">
                <h4 class="pm-detail-section-title">
                    <span class="material-symbols-outlined" style="font-size: 17px; color: #10b981;">check_circle</span>
                    <span>Expected Outcomes & Deliverables</span>
                </h4>
                <div class="pm-detail-text-card pm-detail-outcomes" id="pmDetailOutcomes">
                    Standard project deliverables.
                </div>
            </div>

            <!-- Dynamic Action Controls Bar -->
            <div id="pmDetailActionsArea" style="margin-top: 8px; padding-top: 14px; border-top: 1px solid #e2e8f0;">
                <div id="pmDetailActionControls">
                    <!-- Populated dynamically by project_management.js -->
                </div>
            </div>

        </div>

        <!-- Modal Footer -->
        <div class="pm-modal-footer" style="padding: 12px 22px; background: #f8fafc; border-top: 1px solid #e2e8f0; display: flex; justify-content: flex-end;">
            <button type="button" class="pm-btn-secondary" onclick="closeProjectDetailsModal()">Close</button>
        </div>

    </div>
</div>