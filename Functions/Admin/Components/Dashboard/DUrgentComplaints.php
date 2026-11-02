<div class="card complaints-section">
    <div class="card-header">
        <div class="complaints-header">
            <span class="warning-icon">⚠</span>
            <h3>Urgent Complaints</h3>
        </div>
    </div>
    <div class="card-body">
        <?php
        $hasComplaints = false;
        $limit = min(3, count($complains));
        for ($i = 0; $i < $limit; $i++) {
            if (($complains[$i]['status'] != "DISMISSED")) {
                $hasComplaints = true;
                $priorityClass = strtolower($complains[$i]['priority']);
                ?>
                <div class="complaint-item"
                    data-id="<?php echo $complains[$i]['id']; ?>"
                    data-title="<?php echo htmlspecialchars($complains[$i]['title'] ?? '', ENT_QUOTES); ?>"
                    data-desc="<?php echo htmlspecialchars($complains[$i]['discription'] ?? '', ENT_QUOTES); ?>"
                    data-priority="<?php echo htmlspecialchars($complains[$i]['priority'] ?? 'HIGH', ENT_QUOTES); ?>"
                    data-status="<?php echo htmlspecialchars($complains[$i]['status'] ?? 'PENDING', ENT_QUOTES); ?>"
                    data-email="<?php echo htmlspecialchars($complains[$i]['email'] ?? 'Not specified', ENT_QUOTES); ?>"
                    data-category="<?php echo htmlspecialchars($complains[$i]['category'] ?? 'Technical', ENT_QUOTES); ?>"
                    data-date="<?php echo !empty($complains[$i]['create_at']) ? date('M d, Y h:i A', strtotime($complains[$i]['create_at'])) : 'Recent'; ?>">
                    <div class="complaint-meta">
                        <span class="complaint-id">#<?php echo $complains[$i]['id']; ?></span>
                        <span
                            class="complaint-priority <?php echo $priorityClass === 'urgent' || $priorityClass === 'high' ? 'high' : ($priorityClass === 'medium' ? 'medium' : 'low'); ?>">
                            <?php echo htmlspecialchars($complains[$i]['priority']); ?>
                        </span>
                    </div>
                    <div class="complaint-title"><?php echo htmlspecialchars($complains[$i]['title']); ?></div>
                    <div class="complaint-desc">
                        <?php echo htmlspecialchars($complains[$i]['discription']); ?>
                    </div>
                    <div class="complaint-actions">
                        <button class="btn-sm primary" type="button" onclick="openUrgentComplaintReview(this)">Review</button>
                        <button type="button" class="btn-sm secondary" onclick="openUrgentComplaintDismiss(<?php echo $complains[$i]['id']; ?>, '<?php echo htmlspecialchars($complains[$i]['title'] ?? '', ENT_QUOTES); ?>')">Dismiss</button>
                    </div>
                </div>
                <?php
            }
        }

        if (!$hasComplaints) { ?>
            <div
                style="text-align: center; padding: 40px 20px; display: flex; flex-direction: column; align-items: center; justify-content: center; background: #f9fafb; border-radius: 8px; margin: 10px 0; border: 1px dashed #d1d5db;">
                <span class="material-symbols-outlined"
                    style="font-size: 48px; color: #9ca3af; margin-bottom: 12px;">inbox</span>
                <h4 style="margin: 0; font-size: 16px; color: #4b5563; font-weight: 600;">No Urgent Complaints</h4>
                <p style="margin: 6px 0 0 0; font-size: 13px; color: #6b7280;">There are no high risk complaints to
                    display at
                    the moment.</p>
            </div>
        <?php } ?>

    </div>
    <a href="complain.php" class="see-all-link">See all
        (<?php echo $complaintDB->getUndismissedComplaintCount(); ?>) complaints</a>
</div>

<?php require_once __DIR__ . '/DComplaintReviewModal.php'; ?>
<?php require_once __DIR__ . '/DComplaintDismissModal.php'; ?>
