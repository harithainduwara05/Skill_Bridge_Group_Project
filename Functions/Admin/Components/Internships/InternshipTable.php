<div class="im-table-responsive">
    <table class="data-table im-table">
                    <?php 
                        $TableColumns = ["ID", "POSITION", "COMPANY", "INDUSTRY", "REQUIRED SKILLS", "APPLICATIONS", "DEADLINE", "STATUS", "ACTIONS"];
                        include dirname(__DIR__) . "/Common/TableHeder.php";
                    ?>
                    <tbody id="imTableBody">
                    <?php if (!empty($internshipsList)): ?>
                        <?php foreach ($internshipsList as $item): 
                            $idNum = (int)$item['id'];
                            $formattedId = '#INT' . str_pad($idNum, 3, '0', STR_PAD_LEFT);
                            $title = htmlspecialchars($item['title'] ?? 'Untitled Internship');
                            $company = htmlspecialchars($item['company'] ?? 'N/A');
                            $industry = htmlspecialchars($item['industry'] ?? 'Technology');

                            // Work Mode & Duration
                            $workMode = trim($item['work_mode'] ?? '');
                            $duration = trim($item['duration'] ?? '');
                            if (!empty($workMode) && !empty($duration)) {
                                if (stripos($workMode, $duration) !== false) {
                                    $modeDisplay = $workMode;
                                } else {
                                    $modeDisplay = $duration . ' • ' . $workMode;
                                }
                            } elseif (!empty($workMode)) {
                                $modeDisplay = $workMode;
                            } elseif (!empty($duration)) {
                                $modeDisplay = $duration;
                            } else {
                                $modeDisplay = 'Full-time • Remote';
                            }
                            $modeDisplay = htmlspecialchars($modeDisplay);

                            // Required Skills
                            $skillsStr = trim($item['tech_tags'] ?? '');
                            if (!empty($skillsStr)) {
                                if (strpos($skillsStr, ',') !== false) {
                                    $skillsArr = array_filter(array_map('trim', explode(',', $skillsStr)));
                                } else {
                                    $skillsArr = array_filter(array_map('trim', preg_split('/\s+/', $skillsStr)));
                                }
                            } else {
                                $skillsArr = ['General'];
                            }
                            $skillsAttr = htmlspecialchars(implode(' ', $skillsArr));

                            // Applications & Deadline
                            $appsCount = (int)($item['applications_count'] ?? 0);
                            $rawDeadline = trim($item['deadline'] ?? '');
                            if (!empty($rawDeadline)) {
                                $time = strtotime($rawDeadline);
                                $deadlineFormatted = $time ? date('d M Y', $time) : htmlspecialchars($rawDeadline);
                            } else {
                                $deadlineFormatted = 'Not Specified';
                            }

                            // Status Normalization
                            $rawStatus = trim($item['status'] ?? 'Active');
                            $sLower = strtolower($rawStatus);
                            if (in_array($sLower, ['active', 'open', 'approved'])) {
                                $status = 'Active';
                            } elseif (in_array($sLower, ['suspended', 'hold', 'paused'])) {
                                $status = 'Suspended';
                            } elseif (in_array($sLower, ['terminated', 'cancelled', 'rejected'])) {
                                $status = 'Terminated';
                            } elseif (in_array($sLower, ['closed', 'completed', 'ended'])) {
                                $status = 'Closed';
                            } else {
                                $status = ucfirst($sLower);
                            }

                            $adminReason = htmlspecialchars($item['admin_reason'] ?? '');
                            $companyAction = htmlspecialchars($item['reactivation_status'] ?? '');
                            $terminateReason = ($status === 'Terminated') ? $adminReason : '';

                            // Industry styling class
                            $indLower = strtolower($industry);
                            $indClass = '';
                            if (strpos($indLower, 'ai') !== false || strpos($indLower, 'artificial') !== false) {
                                $indClass = 'ai';
                            } elseif (strpos($indLower, 'mobile') !== false) {
                                $indClass = 'mobile';
                            }
                        ?>
                        <tr class="im-data-row"
                            data-id="<?= $formattedId ?>"
                            data-position="<?= $title ?>"
                            data-company="<?= $company ?>"
                            data-industry="<?= $industry ?>"
                            data-skills="<?= $skillsAttr ?>"
                            data-status="<?= $status ?>"
                            data-duration="<?= $modeDisplay ?>"
                            data-reason="<?= $adminReason ?>"
                            data-company-action="<?= $companyAction ?>"
                            data-terminate-reason="<?= $terminateReason ?>">
                            <td class="im-internship-id"><?= $formattedId ?></td>
                            <td>
                                <div class="im-position-meta">
                                    <span class="im-position-title"><?= $title ?></span>
                                    <span class="im-position-type"><?= $modeDisplay ?></span>
                                </div>
                            </td>
                            <td class="im-company-name"><?= $company ?></td>
                            <td>
                                <span class="im-industry-badge <?= $indClass ?>"><?= $industry ?></span>
                            </td>
                            <td>
                                <div class="im-skills-stack">
                                    <?php foreach (array_slice($skillsArr, 0, 3) as $sk): ?>
                                        <span class="im-skill-tag"><?= htmlspecialchars($sk) ?></span>
                                    <?php endforeach; ?>
                                </div>
                            </td>
                            <td class="im-applications-count"><?= $appsCount ?></td>
                            <td class="im-deadline-date"><?= $deadlineFormatted ?></td>
                            <td class="im-status-cell">
                                <?php if ($status === 'Active'): ?>
                                    <span class="im-status-badge status-active">
                                        <span class="im-status-dot"></span> Active
                                    </span>
                                <?php elseif ($status === 'Suspended'): ?>
                                    <span class="im-status-badge status-suspended">
                                        <span class="material-symbols-outlined" style="font-size: 13px;">pause_circle</span> Suspended
                                    </span>
                                <?php elseif ($status === 'Terminated'): ?>
                                    <span class="im-status-badge status-terminated">
                                        <span class="material-symbols-outlined" style="font-size: 13px;">cancel</span> Terminated
                                    </span>
                                <?php else: ?>
                                    <span class="im-status-badge status-closed">
                                        <span class="im-status-dot"></span> <?= htmlspecialchars($status) ?>
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="im-actions-cell">
                                <button type="button" class="im-btn-action" title="View Details" onclick="viewInternshipDetails('<?= $formattedId ?>')">
                                    <span class="material-symbols-outlined">visibility</span>
                                </button>
                                <?php if ($status === 'Suspended'): ?>
                                    <button type="button" class="im-btn-action im-btn-action-reactivate" title="Review Suspension & Decide" onclick="openReviewSuspensionModal('<?= $formattedId ?>')">
                                        <span class="material-symbols-outlined">check_circle</span>
                                    </button>
                                <?php elseif ($status === 'Terminated' || $status === 'Closed'): ?>
                                    <button type="button" class="im-btn-action" disabled title="<?= $status === 'Terminated' ? 'Permanently Terminated (Cannot be modified)' : 'Listing Closed' ?>" style="color: #cbd5e1; cursor: not-allowed;">
                                        <span class="material-symbols-outlined">lock</span>
                                    </button>
                                <?php else: ?>
                                    <button type="button" class="im-btn-action im-btn-action-suspend" title="Suspend Post" onclick="openSuspendModal('<?= $formattedId ?>')">
                                        <span class="material-symbols-outlined">block</span>
                                    </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9">
                                <div class="empty-state-card" style="text-align: center; padding: 40px 20px;">
                                    <div class="empty-state-icon" style="margin-bottom: 12px; color: #94a3b8;">
                                        <span class="material-symbols-outlined" style="font-size: 48px;">work_off</span>
                                    </div>
                                    <h4 class="empty-state-title" style="margin: 0 0 6px; color: #334155; font-size: 16px;">No internships found</h4>
                                    <p class="empty-state-text" style="margin: 0; color: #64748b; font-size: 13px;">No internship listings currently exist in the database.</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
