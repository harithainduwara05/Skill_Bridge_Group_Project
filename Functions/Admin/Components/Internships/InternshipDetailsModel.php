<div class="im-modal-backdrop" id="imDetailsModal">
    <div class="im-modal-dialog">
        <div class="im-modal-header">
            <div>
                <h3 id="imDetailTitle" style="margin: 0 0 2px;">Internship Details</h3>
                <span style="font-size: 12.5px; color: #64748b;">Post overview and administrative moderation</span>
            </div>
            <button type="button" class="im-modal-close" onclick="closeInternshipDetailsModal()">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <div class="im-modal-body">
            <!-- ID, Status Badge & Industry Header -->
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px; flex-wrap: wrap; gap: 8px;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <span style="font-weight: 800; color: #091a30; font-size: 16px;" id="imDetailId">#INT001</span>
                    <span id="imDetailStatusBadge">
                        <!-- Populated by JS -->
                    </span>
                </div>
                <span class="im-industry-badge" id="imDetailIndustry">Software Development</span>
            </div>

            <!-- Details Metadata Card -->
            <div class="im-details-info-card">
                <div class="im-details-info-row">
                    <span class="im-details-label">Company:</span>
                    <span class="im-details-val" id="imDetailCompany">Tech Solutions</span>
                </div>
                <div class="im-details-info-row">
                    <span class="im-details-label">Work Type & Mode:</span>
                    <span class="im-details-val" id="imDetailDuration">Full-time • Remote</span>
                </div>
                <div class="im-details-info-row">
                    <span class="im-details-label">Required Skills:</span>
                    <span class="im-details-val" id="imDetailSkills">Java, React, SQL</span>
                </div>
                <div class="im-details-info-row">
                    <span class="im-details-label">Applications Received:</span>
                    <span class="im-details-val highlight" id="imDetailApps">120</span>
                </div>
                <div class="im-details-info-row">
                    <span class="im-details-label">Application Deadline:</span>
                    <span class="im-details-val" id="imDetailDeadline">30 Aug 2026</span>
                </div>
            </div>

            <!-- Suspended Banner (Visible if currently suspended) -->
            <div id="imDetailSuspendedBox" class="im-suspended-banner" style="display: none;">
                <div class="im-suspended-banner-header">
                    <span class="material-symbols-outlined">pause_circle</span>
                    <strong>Currently Suspended Listing</strong>
                </div>
                <p class="im-suspended-banner-text">
                    This post is deactivated and hidden from students due to reported violations or complaints.
                </p>
                <div class="im-suspended-reason-line">
                    <strong>Recorded Reason:</strong> <span id="imDetailSuspensionReason">-</span>
                </div>
                <div class="im-suspended-notif-line">
                    <span class="material-symbols-outlined" style="font-size: 15px;">mark_email_read</span>
                    <span>An official suspension notice has been dispatched to the company.</span>
                </div>
            </div>

            <!-- Terminated Banner (Visible if permanently terminated) -->
            <div id="imDetailTerminatedBox" class="im-terminated-banner" style="display: none;">
                <div class="im-terminated-banner-header">
                    <span class="material-symbols-outlined">cancel</span>
                    <strong>Permanently Terminated Listing</strong>
                </div>
                <p class="im-terminated-banner-text">
                    This opportunity was permanently terminated due to severe policy violations or refusal to provide acceptable remediation.
                </p>
                <div class="im-terminated-reason-line">
                    <strong>Recorded Grounds:</strong> <span id="imDetailTerminationReason">-</span>
                </div>
                <div class="im-terminated-lock-line">
                    <span class="material-symbols-outlined" style="font-size: 15px;">lock</span>
                    <span>Platform Policy: Once permanently terminated, this post CANNOT be reopened, edited, or reactivated.</span>
                </div>
            </div>

            <!-- Admin Moderation & Control Section -->
            <div class="im-moderation-box">
                <div class="im-moderation-info">
                    <label class="im-moderation-title">Admin Moderation & Policy Action</label>
                    <p class="im-moderation-desc" id="imModerationDesc">
                        If this listing breaches platform regulations or student complaints are verified, suspend it and send an official explanation to the company.
                    </p>
                </div>
                <div id="imModerationActionContainer">
                    <!-- Dynamically populated by JS: Suspend or Reactivate button -->
                </div>
            </div>
        </div>
        <div class="im-modal-footer">
            <button type="button" class="im-btn-secondary" onclick="closeInternshipDetailsModal()">Close</button>
        </div>
    </div>
</div>