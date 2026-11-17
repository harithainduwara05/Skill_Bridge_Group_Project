<div class="cm-modal-backdrop" id="cmDetailModal">
    <div class="cm-modal-dialog">
        <form id="cmDetailForm" class="cm-modal-form">
            <div class="cm-modal-header">
                <div>
                    <h3 id="modalComplaintId">#CMP-9284</h3>
                    <span style="font-size: 12px; color: #64748b;">Complaint Investigation & Resolution</span>
                </div>
                <button type="button" class="cm-modal-close" data-dismiss="modal" onclick="closeDetailModal()" title="Close">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>

            <input type="hidden" id="modalHiddenId" value="">

            <div class="cm-modal-body">
                
                <!-- Complainant Identity Box -->
                <div class="cm-detail-meta-box">
                    <div style="display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #e2e8f0; padding-bottom: 10px;">
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <div class="cm-user-avatar" id="modalUserAvatar">JD</div>
                            <div>
                                <strong id="modalUserName" style="font-size: 14.5px; color: #0f172a; display: block;">John Doe</strong>
                                <span id="modalUserRole" style="font-size: 12px; color: #64748b; font-weight: 500;">Student</span>
                            </div>
                        </div>
                        <div style="display: flex; gap: 8px;">
                            <span id="modalPriorityBadge" class="cm-priority-pill priority-high">HIGH</span>
                            <span id="modalStatusBadge" class="cm-status-pill status-pending">PENDING</span>
                        </div>
                    </div>

                    <div class="cm-meta-row">
                        <span>Organization / University:</span>
                        <strong id="modalUserOrg">SkillBridge Network</strong>
                    </div>

                    <div class="cm-meta-row">
                        <span>Contact Email:</span>
                        <strong id="modalUserEmail">user@example.com</strong>
                    </div>

                    <div class="cm-meta-row">
                        <span>Category:</span>
                        <strong id="modalCategoryText">Technical</strong>
                    </div>

                    <div class="cm-meta-row">
                        <span>Date Reported:</span>
                        <strong id="modalSubmittedDate">2026-09-25 10:20:00</strong>
                    </div>
                </div>

                <!-- Complaint Content Box -->
                <div class="cm-detail-desc-box">
                    <h4 id="modalComplaintTitle">Issue Title</h4>
                    <p id="modalComplaintDesc">Detailed description of the complaint submitted by the user...</p>
                </div>

                <!-- Notice when already resolved -->
                <div id="modalResolvedNotice" class="cm-resolved-notice" style="display: none;">
                    <span class="material-symbols-outlined">verified</span>
                    <div>
                        <strong style="font-size: 13.5px;">This Complaint is Resolved</strong>
                        <p style="margin: 3px 0 0; font-size: 12.5px; color: #166534; line-height: 1.4;">This issue has been successfully resolved and finalized. You can review the details and recorded findings below, but no further actions can be taken.</p>
                    </div>
                </div>

                <!-- Resolution Findings & Actions -->
                <div class="cm-resolution-area" id="modalResolutionArea">
                    <div class="cm-form-group" id="modalStatusGroup">
                        <label for="modalStatusSelect">Update Resolution Status</label>
                        <div class="cm-select-wrap">
                            <select id="modalStatusSelect" class="cm-select">
                                <option value="PENDING">Pending (Requires Attention)</option>
                                <option value="IN_REVIEW">In Progress (Under Active Investigation)</option>
                                <option value="RESOLVED">Resolved (Issue Addressed & Verified)</option>
                                <option value="DISMISSED">Dismissed (No Action Required)</option>
                            </select>
                            <span class="material-symbols-outlined select-chevron">expand_more</span>
                        </div>
                    </div>

                    <div class="cm-form-group">
                        <label for="modalResolutionNotes" id="modalNotesLabel">Investigation Findings / Resolution Notes</label>
                        <textarea id="modalResolutionNotes" class="cm-form-control" 
                                  placeholder="Record investigation notes, corrective action taken, or explanation for dismissal..."></textarea>
                        <small id="modalNotifHint" style="font-size: 11.5px; color: #64748b; margin-top: 5px; display: flex; align-items: center; gap: 5px;"></small>
                    </div>
                </div>

            </div>

            <div class="cm-modal-footer">
                <button type="button" class="cm-btn-secondary" data-dismiss="modal" onclick="closeDetailModal()">Close</button>
                <button type="button" id="modalBtnSubmitAction" class="cm-btn-primary">Update Status</button>
            </div>
        </form>

    </div>
</div>