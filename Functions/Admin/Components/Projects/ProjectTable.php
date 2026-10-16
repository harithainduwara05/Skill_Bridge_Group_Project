<div class="pm-table-responsive">
    <table class="data-table pm-table">
        <?php 
            $TableColumns = ["PROJECT ID", "PROJECT TITLE", "ORGANIZATION", "REQUIRED SKILLS", "TEAM MEMBERS", "STATUS", "CREATED DATE", "ACTIONS"];
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
                    
                    $rawStatus = trim($proj['status'] ?? 'Active');
                    $sLower = strtolower($rawStatus);
                    if (in_array($sLower, ['inprogress', 'active'])) {
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
                    $skillsAttr = htmlspecialchars(implode(' ', $skillsArr));
                    $members = (int)($proj['members'] ?? 1);
                    $createdDate = !empty($proj['posted_at']) ? date('d F Y', strtotime($proj['posted_at'])) : date('d F Y');
                    
                    $holdReason = htmlspecialchars($proj['hold_reason'] ?? '');
                    $holdResponse = htmlspecialchars($proj['hold_response'] ?? '');
                    $orgInitial = !empty($org) ? strtoupper(substr($org, 0, 1)) : 'U';
                    
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
                    data-status="<?= $status ?>" 
                    data-skills="<?= $skillsAttr ?>"
                    data-hold-reason="<?= $holdReason ?>"
                    data-hold-days="5"
                    data-team-response="<?= $holdResponse ?>"
                    data-reject-reason="">
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
                        <div class="pm-skills-list">
                            <?php foreach (array_slice($skillsArr, 0, 3) as $sk): ?>
                                <span class="pm-skill-pill"><?= htmlspecialchars($sk) ?></span>
                            <?php endforeach; ?>
                        </div>
                    </td>
                    <td>
                        <div class="pm-team-count">
                            <span class="material-symbols-outlined">group</span>
                            <span><?= $members ?> Members</span>
                        </div>
                    </td>
                    <td>
                        <?php if ($status === 'Active'): ?>
                            <span class="pm-status-pill status-active">Active</span>
                        <?php elseif ($status === 'Hold'): ?>
                            <span class="pm-status-pill status-hold" title="On Hold: 5 days left in grace period">
                                <span class="material-symbols-outlined" style="font-size: 13px; margin-right: 3px;">schedule</span>Hold (5d left)
                            </span>
                        <?php elseif ($status === 'Completed'): ?>
                            <span class="pm-status-pill status-completed">Completed</span>
                        <?php elseif ($status === 'Review'): ?>
                            <span class="pm-status-pill status-review">Review</span>
                        <?php elseif ($status === 'Rejected'): ?>
                            <span class="pm-status-pill status-rejected">Rejected</span>
                        <?php else: ?>
                            <span class="pm-status-pill status-active"><?= htmlspecialchars($status) ?></span>
                        <?php endif; ?>
                    </td>
                    <td class="pm-created-date"><?= $createdDate ?></td>
                    <td class="pm-actions-cell">
                        <button class="pm-btn-icon" title="View Details" onclick="viewProjectDetails('<?= $projId ?>')">
                            <span class="material-symbols-outlined">visibility</span>
                        </button>
                        <?php if ($status === 'Active' || $status === 'Review'): ?>
                            <button class="pm-btn-icon pm-btn-icon-hold" title="Place on Hold (Temporary Review)" onclick="openHoldModal('<?= $projId ?>')">
                                <span class="material-symbols-outlined">pause_circle</span>
                            </button>
                        <?php elseif ($status === 'Hold'): ?>
                            <button class="pm-btn-icon pm-btn-icon-reactivate" title="Review Hold & Decide Status" onclick="openReviewHoldModal('<?= $projId ?>')">
                                <span class="material-symbols-outlined">check_circle</span>
                            </button>
                        <?php elseif ($status === 'Rejected'): ?>
                            <button class="pm-btn-icon" title="Permanently Rejected (Cannot be reactivated)" disabled style="color: #cbd5e1; cursor: not-allowed;">
                                <span class="material-symbols-outlined">lock</span>
                            </button>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>

        </tbody>
    </table>
</div>