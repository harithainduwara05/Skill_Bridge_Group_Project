<div class="pm-table-responsive">
    <table class="data-table pm-table">
        <?php 
            $TableColumns = ["PROJECT ID", "PROJECT TITLE", "ORGANIZATION", "STATUS", "CREATED DATE", "ACTIONS"];
            include dirname(__DIR__) . "/Common/TableHeder.php";
        ?>
        <tbody id="pmTableBody">
            
            <?php if (!empty($projectsList)): ?>
                <?php foreach ($projectsList as $proj): 
                    $idNum = (int)$proj['id'];
                    $projId = '#PR' . str_pad($idNum, 3, '0', STR_PAD_LEFT);
                    $title = htmlspecialchars($proj['title'] ?? 'Untitled Project');
                    $category = htmlspecialchars($proj['category'] ?? 'General');
                    $org = htmlspecialchars($proj['company'] ?? 'N/A');
                    
                    $rawStatus = trim($proj['status'] ?? 'Published');
                    $sLower = strtolower($rawStatus);
                    if (in_array($sLower, ['published', 'publish', 'approved'])) {
                        $status = 'Published';
                    } elseif (in_array($sLower, ['inprogress', 'active'])) {
                        $status = 'Active';
                    } elseif (in_array($sLower, ['completed', 'complete', 'close'])) {
                        $status = 'Completed';
                    } elseif (in_array($sLower, ['reviewing', 'review', 'rewiewing'])) {
                        $status = 'Review';
                    } elseif ($sLower === 'hold') {
                        $status = 'Hold';
                    } elseif ($sLower === 'rejected') {
                        $status = 'Rejected';
                    } else {
                        $status = ucfirst($sLower);
                    }

                    $skillsStr = trim($proj['tech_stack'] ?? '');
                    $skillsArr = !empty($skillsStr) ? array_filter(array_map('trim', explode(',', $skillsStr))) : ['General'];
                    $skillsAttr = htmlspecialchars(implode(', ', $skillsArr));
                    $members = (int)($proj['members'] ?? 1);
                    $createdDate = !empty($proj['posted_at']) ? date('d F Y', strtotime($proj['posted_at'])) : date('d F Y');
                    
                    $holdReason = htmlspecialchars($proj['hold_reason'] ?? '');
                    $holdResponse = htmlspecialchars($proj['hold_response'] ?? '');
                    $rejectReason = ($status === 'Rejected') ? $holdReason : '';
                    $orgInitial = !empty($org) ? strtoupper(substr($org, 0, 1)) : 'U';
                    
                    $description = htmlspecialchars($proj['description'] ?? 'No description provided.');
                    $learningObjectives = htmlspecialchars($proj['learning_objectives'] ?? 'Standard industry project objectives.');
                    $expectedOutcomes = htmlspecialchars($proj['expected_outcomes'] ?? 'Production ready deliverables.');
                    $difficulty = htmlspecialchars($proj['difficulty'] ?? 'Intermediate');
                    $duration = htmlspecialchars($proj['duration'] ?? '8 Weeks');
                    $deadline = !empty($proj['deadline']) ? htmlspecialchars($proj['deadline']) : 'Not Specified';
                    $orgEmail = htmlspecialchars($proj['organization_email'] ?? 'N/A');

                    $holdDaysLeft = 7;
                    if (!empty($proj['held_at'])) {
                        $diffDays = floor((time() - strtotime($proj['held_at'])) / 86400);
                        $holdDaysLeft = max(0, 7 - (int)$diffDays);
                    }
                    
                    $orgLower = strtolower($org);
                    $orgClass = 'org-default';
                    if (strpos($orgLower, 'ucsc') !== false) {
                        $orgClass = 'org-ucsc';
                    } elseif (strpos($orgLower, 'abc') !== false) {
                        $orgClass = 'org-abc';
                    } elseif (strpos($orgLower, 'tech') !== false) {
                        $orgClass = 'org-tech';
                    } elseif (strpos($orgLower, 'sliit') !== false) {
                        $orgClass = 'org-sliit';
                    }
                ?>
                <tr class="pm-data-row" 
                    data-id="<?= $projId ?>" 
                    data-title="<?= $title ?>" 
                    data-category="<?= $category ?>" 
                    data-org="<?= $org ?>" 
                    data-org-email="<?= $orgEmail ?>"
                    data-status="<?= $status ?>" 
                    data-skills="<?= $skillsAttr ?>"
                    data-description="<?= $description ?>"
                    data-learning-objectives="<?= $learningObjectives ?>"
                    data-expected-outcomes="<?= $expectedOutcomes ?>"
                    data-difficulty="<?= $difficulty ?>"
                    data-duration="<?= $duration ?>"
                    data-deadline="<?= $deadline ?>"
                    data-members="<?= $members ?>"
                    data-hold-reason="<?= $holdReason ?>"
                    data-hold-days="<?= $holdDaysLeft ?>"
                    data-team-response="<?= $holdResponse ?>"
                    data-reject-reason="<?= $rejectReason ?>">
                    <td class="pm-project-id"><?= $projId ?></td>
                    <td>
                        <div class="pm-project-meta">
                            <span class="pm-title-main"><?= $title ?></span>
                            <span class="pm-title-category"><?= $category ?></span>
                        </div>
                    </td>
                    <td>
                        <div class="pm-org-wrapper">
                            <span class="pm-org-avatar <?= $orgClass ?>"><?= $orgInitial ?></span>
                            <span class="pm-org-name"><?= $org ?></span>
                        </div>
                    </td>
                    <td>
                        <?php if ($status === 'Published'): ?>
                            <span class="pm-status-pill status-published" title="Approved by Admin, currently open for student applications">
                                <span class="material-symbols-outlined" style="font-size: 13px; margin-right: 3px;">public</span>Published
                            </span>
                        <?php elseif ($status === 'Active'): ?>
                            <span class="pm-status-pill status-active" title="Team formed and active in progress">
                                <span class="material-symbols-outlined" style="font-size: 13px; margin-right: 3px;">bolt</span>Active
                            </span>
                        <?php elseif ($status === 'Hold'): ?>
                            <span class="pm-status-pill status-hold" title="On Hold: <?= $holdDaysLeft ?> days left in grace period">
                                <span class="material-symbols-outlined" style="font-size: 13px; margin-right: 3px;">pause_circle</span>Hold (<?= $holdDaysLeft ?>d left)
                            </span>
                        <?php elseif ($status === 'Completed'): ?>
                            <span class="pm-status-pill status-completed">
                                <span class="material-symbols-outlined" style="font-size: 13px; margin-right: 3px;">task_alt</span>Completed
                            </span>
                        <?php elseif ($status === 'Review'): ?>
                            <span class="pm-status-pill status-review" title="Pending Admin review before student publication">
                                <span class="material-symbols-outlined" style="font-size: 13px; margin-right: 3px;">hourglass_top</span>Review
                            </span>
                        <?php elseif ($status === 'Rejected'): ?>
                            <span class="pm-status-pill status-rejected">
                                <span class="material-symbols-outlined" style="font-size: 13px; margin-right: 3px;">block</span>Rejected
                            </span>
                        <?php else: ?>
                            <span class="pm-status-pill status-active"><?= htmlspecialchars($status) ?></span>
                        <?php endif; ?>
                    </td>
                    <td class="pm-created-date"><?= $createdDate ?></td>
                    <td class="pm-actions-cell">
                        <button class="pm-btn-icon" title="View Full Details & Moderate" onclick="viewProjectDetails('<?= $projId ?>')">
                            <span class="material-symbols-outlined">visibility</span>
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>

        </tbody>
    </table>
</div>