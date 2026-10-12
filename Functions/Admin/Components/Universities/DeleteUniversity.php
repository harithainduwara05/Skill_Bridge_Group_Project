<div class="univ-modal-overlay" id="deleteModal">
    <div class="univ-modal">
        <div class="univ-modal-header" id="deleteModalHeader">
            <h3 style="color:#dc2626; display:flex; align-items:center; gap:8px;">
                <span class="material-symbols-outlined" style="font-size:20px;">delete</span>
                <span>Delete University</span>
            </h3>
            <button class="univ-modal-close" id="closeDeleteModal" type="button">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <form action="" method="post" id="deleteUnivForm">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="delete_domain" id="deleteDomainInput">

            <!-- STATE 1: Normal Deletion (No Students) -->
            <div id="deleteAllowedWrap" class="univ-modal-body" style="padding: 22px 24px;">
                <div style="display:flex; gap:16px; align-items:flex-start;">
                    <div
                        style="width:44px; height:44px; border-radius:50%; background:#fee2e2; color:#dc2626; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                        <span class="material-symbols-outlined" style="font-size:24px;">warning</span>
                    </div>
                    <div style="flex:1;">
                        <h4 style="font-size: 14.5px; font-weight:700; color:#0f172a; margin:0 0 6px 0;">
                            Are you sure you want to delete this university?
                        </h4>
                        <p style="font-size: 13px; color:#64748b; margin:0 0 14px 0; line-height:1.5;">
                            This action cannot be undone. All student domain associations will be removed.
                        </p>

                        <div
                            style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:10px; padding:12px 14px;">
                            <div
                                style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px; font-size:12.5px;">
                                <span style="color:#64748b; font-weight:600;">University:</span>
                                <strong id="deleteUniDisplay" style="color:#0f172a; font-weight:600;"></strong>
                            </div>
                            <div
                                style="display:flex; justify-content:space-between; align-items:center; font-size:12.5px;">
                                <span style="color:#64748b; font-weight:600;">Domain:</span>
                                <strong id="deleteDomainDisplay"
                                    style="color:#2563eb; font-weight:600; font-family:'Courier New', monospace;"></strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- STATE 2: Blocked Deletion (Active Students Enrolled) -->
            <div id="deleteBlockedWrap" class="univ-modal-body" style="padding: 22px 24px; display:none;">
                <div style="display:flex; gap:16px; align-items:flex-start;">
                    <div
                        style="width:46px; height:46px; border-radius:50%; background:#fef3c7; color:#d97706; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                        <span class="material-symbols-outlined" style="font-size:26px;">gpp_maybe</span>
                    </div>
                    <div style="flex:1;">
                        <h4 style="font-size: 15px; font-weight:700; color:#92400e; margin:0 0 6px 0;">
                            Deletion Restricted: Enrolled Students Found
                        </h4>
                        <div
                            style="background:#fffbeb; border:1.5px solid #fde68a; border-radius:10px; padding:12px 14px; margin-bottom:14px; font-size:12.5px; color:#92400e; line-height:1.5;">
                            This institution cannot be deleted because it currently has <strong id="deleteBlockedStuCount" style="color:#b45309; text-decoration:underline;">0 students</strong> enrolled on SkillBridge. Deleting it would orphan active student accounts and break login authorization.
                        </div>

                        <div
                            style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:10px; padding:12px 14px; margin-bottom:12px;">
                            <div
                                style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px; font-size:12.5px;">
                                <span style="color:#64748b; font-weight:600;">University:</span>
                                <strong id="deleteBlockedUni" style="color:#0f172a; font-weight:600;"></strong>
                            </div>
                            <div
                                style="display:flex; justify-content:space-between; align-items:center; font-size:12.5px;">
                                <span style="color:#64748b; font-weight:600;">Authorized Domain:</span>
                                <strong id="deleteBlockedDomain"
                                    style="color:#2563eb; font-weight:600; font-family:'Courier New', monospace;"></strong>
                            </div>
                        </div>

                        <p id="deleteModalRecommendation" style="font-size:12px; color:#64748b; margin:0; line-height:1.4;">
                            <span class="material-symbols-outlined" style="font-size:14px; vertical-align:middle; color:#d97706;">lightbulb</span>
                            <em>Recommended:</em> If you wish to suspend onboarding or platform access without removing user records, change the status to <strong>"Hold"</strong> or <strong>"Inactive"</strong> instead.
                        </p>
                    </div>
                </div>
            </div>

            <div class="univ-modal-footer">
                <button type="button" class="univ-btn-cancel" id="cancelDeleteModal">Cancel</button>
                <button type="button" class="btn-primary" id="editStatusInsteadBtn" style="background:#d97706; border:none; display:none; align-items:center; gap:6px; padding:8px 16px; border-radius:8px; font-size:13px; font-weight:600; color:#fff; cursor:pointer;">
                    <span class="material-symbols-outlined" style="font-size:16px;">edit</span>
                    Change Status Instead
                </button>
                <button type="submit" class="univ-btn-delete" id="confirmDeleteBtn">
                    <span class="material-symbols-outlined" style="font-size:16px;">delete</span>
                    Delete University
                </button>
            </div>
        </form>
    </div>
</div>