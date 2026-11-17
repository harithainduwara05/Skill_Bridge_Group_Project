<div class="card-body" style="padding:0; overflow-x: auto;">
    <table class="data-table" id="univTable">
        <?php 
            $TableColumns = ["", "UNIVERSITY NAME", "EMAIL DOMAIN", "LOCATION", "STUDENTS", "STATUS", "ACTIONS"];
            include dirname(__DIR__) . "/Common/TableHeder.php";
        ?>
        <tbody>
            <?php if (empty($pageUniversities)): ?>
                <tr>
                    <td colspan="7" style="text-align: center; padding: 40px; color: #94a3b8;">
                        <span class="material-symbols-outlined"
                            style="font-size: 40px; color: #cbd5e1; display:block; margin-bottom:8px;">search_off</span>
                        No universities found matching your criteria.
                    </td>
                </tr>
            <?php else: ?>
                <?php
                foreach ($pageUniversities as $row):
                    $initials = strtoupper(substr($row['University'], 0, 3));
                    $badgeCls = match (strtolower($row['Status'] ?? '')) {
                        'active' => 'active',
                        'hold' => 'hold',
                        'inactive', 'deactive' => 'inactive',
                        default => 'inactive'
                    };
                    ?>
                    <tr class="univ-row"
                        data-status="<?= htmlspecialchars($badgeCls) ?>"
                        data-search="<?= htmlspecialchars(strtolower($row['University'] . ' ' . ($row['faculty'] ?? '') . ' ' . $row['emailEx'] . ' ' . ($row['Location'] ?? '')), ENT_QUOTES) ?>">
                        <td>
                            <div class="university-logo mit" style="background:#1e293b;">
                                <?= htmlspecialchars($initials) ?></div>
                        </td>
                        <td>
                            <div class="university-name"><?= htmlspecialchars($row['University']) ?></div>
                            <div class="university-faculty"><?= htmlspecialchars($row['faculty'] ?? '') ?></div>
                        </td>
                        <td><span class="univ-domain-badge">@<?= htmlspecialchars($row['emailEx']) ?></span></td>
                        <td><?= htmlspecialchars($row['Location'] ?? '—') ?></td>
                        <?php $stuCount = $uniDB->getStudentCountByDomain($row['emailEx']); ?>
                        <td><?= $stuCount ?></td>
                        <td><span
                                class="badge-status <?= $badgeCls ?>"><?= htmlspecialchars($row['Status'] ?? '') ?></span>
                        </td>
                        <td>
                            <div class="univ-actions-cell">
                                <button class="action-btn" type="button" title="View Details" onclick="openViewModal(
                                '<?= htmlspecialchars(addslashes($row['University'])) ?>',
                                '<?= htmlspecialchars(addslashes($row['faculty'] ?? '')) ?>',
                                '<?= htmlspecialchars(addslashes($row['emailEx'])) ?>',
                                '<?= htmlspecialchars(addslashes($row['Location'] ?? '')) ?>',
                                '<?= $stuCount ?>',
                                '<?= htmlspecialchars(addslashes($row['Status'] ?? '')) ?>')">
                                    <span class="material-symbols-outlined"
                                        style="font-size:18px;">visibility</span>
                                </button>
                                <?php if (strcasecmp($row['Status'] ?? '', 'Inactive') !== 0): ?>
                                <button class="action-btn" type="button" title="Edit" onclick="openEditModal(
                                '<?= htmlspecialchars(addslashes($row['University'])) ?>',
                                '<?= htmlspecialchars(addslashes($row['faculty'] ?? '')) ?>',
                                '<?= htmlspecialchars(addslashes($row['emailEx'])) ?>',
                                '<?= htmlspecialchars(addslashes($row['Location'] ?? '')) ?>',
                                '<?= htmlspecialchars(addslashes($row['Status'] ?? '')) ?>')">
                                    <span class="material-symbols-outlined" style="font-size:18px;">edit</span>
                                </button>
                                <?php endif; ?>
                                <button class="action-btn" type="button" title="Delete" style="color:#dc2626;"
                                    data-status="<?= htmlspecialchars(trim($row['Status'] ?? '')) ?>"
                                    onclick="openDeleteModal('<?= htmlspecialchars(addslashes($row['emailEx'])) ?>', '<?= htmlspecialchars(addslashes($row['University'])) ?>', <?= (int)$stuCount ?>, '<?= htmlspecialchars(addslashes($row['faculty'] ?? '')) ?>', '<?= htmlspecialchars(addslashes($row['Location'] ?? '')) ?>', '<?= htmlspecialchars(addslashes(trim($row['Status'] ?? ''))) ?>')">
                                    <span class="material-symbols-outlined" style="font-size:18px;">delete</span>
                                </button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>