
<div class="cm-table-responsive">
    <table class="data-table cm-table">
        <?php 
            $TableColumns = ["Complaint ID", "Submitted By", "Category", "Description", "Priority", "Status", "Actions"];
            include __DIR__ . "/TableHeder.php";
        ?>
        <tbody id="cmTableBody">
            <?php if (!empty($complaints)): ?>
                <?php foreach ($complaints as $idx => $cmp): 
                    // Real Complaint ID padded to 4 digits: #CMP-0001
                    $displayId = '#CMP-' . str_pad($cmp['id'], 4, '0', STR_PAD_LEFT);
                    $priorityLower = strtolower($cmp['priority'] ?? 'medium');
                    $statusRaw = strtoupper($cmp['status'] ?? 'PENDING');
                    
                    // Normalizing status display
                    if ($statusRaw === 'IN_REVIEW' || $statusRaw === 'IN PROGRESS') {
                        $statusClass = 'progress';
                        $statusLabel = 'IN PROGRESS';
                    } elseif ($statusRaw === 'RESOLVED') {
                        $statusClass = 'resolved';
                        $statusLabel = 'RESOLVED';
                    } elseif ($statusRaw === 'DISMISSED') {
                        $statusClass = 'dismissed';
                        $statusLabel = 'DISMISSED';
                    } else {
                        $statusClass = 'pending';
                        $statusLabel = 'PENDING';
                    }

                    // Role formatting
                    $roleDisplay = ucfirst($cmp['role'] ?? 'Student');
                    if (stripos($cmp['organization_name'] ?? '', 'University') !== false) {
                        $roleDisplay = 'University Rep';
                    } elseif ($cmp['role'] === 'company' || $cmp['role'] === 'organization') {
                        $roleDisplay = 'Organization HR';
                    } elseif ($cmp['role'] === 'mentor') {
                        $roleDisplay = 'Mentor';
                    }

                    // Avatar initials
                    $userName = $cmp['user_name'] ?? 'Community Member';
                    $nameWords = explode(' ', trim($userName));
                    $initials = count($nameWords) >= 2 
                        ? strtoupper(substr($nameWords[0], 0, 1) . substr($nameWords[1], 0, 1))
                        : strtoupper(substr($userName, 0, 2));

                    // Avatar color theme
                    $avatarClass = 'av-student';
                    if (stripos($roleDisplay, 'University') !== false) $avatarClass = 'av-uni';
                    elseif (stripos($roleDisplay, 'Mentor') !== false) $avatarClass = 'av-mentor';
                    elseif (stripos($roleDisplay, 'HR') !== false || stripos($roleDisplay, 'Org') !== false) $avatarClass = 'av-hr';
                    elseif (stripos($roleDisplay, 'Company') !== false) $avatarClass = 'av-company';

                    $categoryDisplay = htmlspecialchars($cmp['category'] ?? 'Technical');
                    $descExcerpt = htmlspecialchars($cmp['title'] . ' - ' . $cmp['discription']);
                ?>
                <tr class="cm-data-row"
                    style="<?= $idx >= 6 ? 'display: none;' : '' ?>"
                    data-id="<?= $cmp['id'] ?>"
                    data-display-id="<?= $displayId ?>"
                    data-user-name="<?= htmlspecialchars($userName) ?>"
                    data-user-email="<?= htmlspecialchars($cmp['email']) ?>"
                    data-user-role="<?= htmlspecialchars($roleDisplay) ?>"
                    data-org="<?= htmlspecialchars($cmp['organization_name'] ?? 'SkillBridge') ?>"
                    data-category="<?= $categoryDisplay ?>"
                    data-title="<?= htmlspecialchars($cmp['title']) ?>"
                    data-desc="<?= htmlspecialchars($cmp['discription']) ?>"
                    data-priority="<?= $priorityLower ?>"
                    data-status="<?= strtolower($statusClass) ?>"
                    data-notes="<?= htmlspecialchars($cmp['resolution_notes'] ?? '') ?>"
                    data-created-at="<?= htmlspecialchars($cmp['create_at']) ?>">
                    
                    <!-- ID -->
                    <td>
                        <span class="cm-complaint-id"><?= $displayId ?></span>
                    </td>

                    <!-- User -->
                    <td>
                        <div class="cm-user-cell">
                            <div class="cm-user-avatar <?= $avatarClass ?>"><?= $initials ?></div>
                            <div class="cm-user-meta">
                                <span class="cm-user-name"><?= htmlspecialchars($userName) ?></span>
                                <span class="cm-user-role"><?= htmlspecialchars($roleDisplay) ?></span>
                            </div>
                        </div>
                    </td>

                    <!-- Category -->
                    <td>
                        <span class="cm-category-cell"><?= $categoryDisplay ?></span>
                    </td>

                    <!-- Description -->
                    <td>
                        <div class="cm-desc-cell" title="<?= $descExcerpt ?>">
                            <?= $descExcerpt ?>
                        </div>
                    </td>

                    <!-- Priority -->
                    <td>
                        <span class="cm-priority-pill priority-<?= $priorityLower ?>">
                            <?= strtoupper($priorityLower) ?>
                        </span>
                    </td>

                    <!-- Status -->
                    <td>
                        <span class="cm-status-pill status-<?= $statusClass ?>">
                            <?= $statusLabel ?>
                        </span>
                    </td>

                    <!-- Actions -->
                    <td>
                        <div class="cm-actions-cell">
                            <button type="button" class="cm-btn-icon cm-btn-view" data-action="view" title="View & Investigate Details">
                                <span class="material-symbols-outlined" style="font-size: 19px;">visibility</span>
                            </button>
                        </div>
                    </td>

                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr id="cmEmptyStateRow">
                    <td colspan="7" style="text-align: center; padding: 48px 20px; color: #64748b;">
                        <span class="material-symbols-outlined" style="font-size: 40px; color: #94a3b8; display: block; margin-bottom: 8px;">report_off</span>
                        <strong style="font-size: 15px; color: #1e293b; display: block; margin-bottom: 4px;">No complaints recorded</strong>
                        <span style="font-size: 13px;">All platform systems and student activities are running smoothly without open grievances.</span>
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>