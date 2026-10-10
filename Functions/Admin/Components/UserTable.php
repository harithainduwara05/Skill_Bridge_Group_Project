<div class="card-body" style="padding: 0; overflow-x: auto;">
    <table class="data-table">
            <?php 
                $TableColumns = ["User", "Role", "Affiliation / Organization", "Status", "Joined Date", "Actions"];
                include __DIR__ . "/TableHeder.php";
            ?>
        <tbody>
            <?php if (empty($usersList)): ?>
                <tr>
                    <td colspan="<?= count($TableColumns) ?>" class="table-empty-state">
                        <span class="material-symbols-outlined">person_search</span>
                        No users found matching your criteria.
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($usersList as $u): 
                    $userRole = strtolower($u['role']);
                    $initial = strtoupper(substr($u['user_name'], 0, 1));
                    $statusClass = strtolower($u['status'] ?? 'active');
                    if ($statusClass === 'de-active') $statusClass = 'deactive';
                    $joinedDate = !empty($u['created_at']) ? date('M d, Y', strtotime($u['created_at'])) : 'N/A';
                    $isSelf = ($currentAdminEmail !== '' && strtolower($u['email']) === $currentAdminEmail);
                ?>
                <tr class="<?= $isSelf ? 'user-self-row' : '' ?>">
                    <td>
                        <div class="user-cell-meta">
                            <div class="user-avatar-circle <?= $userRole ?>">
                                <?= $initial ?>
                            </div>
                            <div>
                                <div class="user-name-text">
                                    <?= htmlspecialchars($u['user_name']) ?>
                                    <?php if ($isSelf): ?>
                                        <span class="self-badge" style="font-size:11px; font-weight:700; color:#2563eb; background:#eff6ff; border:1px solid #bfdbfe; padding:1px 6px; border-radius:10px; margin-left:4px;">You</span>
                                    <?php endif; ?>
                                </div>
                                <div class="user-email-text"><?= htmlspecialchars($u['email']) ?></div>
                            </div>
                        </div>
                    </td>

                    <td class="cell-nowrap">
                        <span class="badge-role <?= $userRole ?>">
                            <?= htmlspecialchars(ucfirst($u['role'])) ?>
                        </span>
                    </td>

                    <td>
                        <?= htmlspecialchars($u['organization_name'] ?: 'N/A') ?>
                    </td>

                    <td class="cell-nowrap">
                        <span class="badge-status-pill <?= $statusClass ?>">
                            <?= htmlspecialchars(ucfirst($u['status'] ?: 'Active')) ?>
                        </span>
                    </td>

                    <td class="cell-nowrap" style="color: #64748b;">
                        <?= htmlspecialchars($joinedDate) ?>
                    </td>

                    <td class="cell-nowrap">
                        <div class="user-actions-cell">
                            <!-- View Details -->
                            <button class="btn-table-action view" title="View Details" onclick="viewUser(<?= htmlspecialchars(json_encode($u), ENT_QUOTES, 'UTF-8') ?>)">
                                <span class="material-symbols-outlined" style="font-size: 17px;">visibility</span>
                            </button>
                            
                            <!-- Edit User -->
                            <button class="btn-table-action edit" title="Edit User" onclick="editUser(<?= htmlspecialchars(json_encode($u), ENT_QUOTES, 'UTF-8') ?>, <?= $isSelf ? 'true' : 'false' ?>)">
                                <span class="material-symbols-outlined" style="font-size: 17px;">edit</span>
                            </button>

                            <?php if ($isSelf): ?>
                                <!-- Disabled Status Toggle for Current Logged-in Admin -->
                                <button type="button" class="btn-table-action" disabled 
                                        title="You cannot deactivate your own account" 
                                        style="color: #94a3b8; background: #f1f5f9; border-color: #e2e8f0; cursor: not-allowed; opacity: 0.5;">
                                    <span class="material-symbols-outlined" style="font-size: 17px;">block</span>
                                </button>

                                <!-- Disabled Delete for Current Logged-in Admin -->
                                <button type="button" class="btn-table-action" disabled 
                                        title="You cannot delete your own account" 
                                        style="color: #94a3b8; background: #f1f5f9; border-color: #e2e8f0; cursor: not-allowed; opacity: 0.5;">
                                    <span class="material-symbols-outlined" style="font-size: 17px;">delete</span>
                                </button>
                            <?php else: ?>
                                <!-- Toggle Status Modal Trigger -->
                                <button type="button" class="btn-table-action" 
                                        title="<?= $u['status'] === 'Active' ? 'Deactivate User' : 'Activate User' ?>" 
                                        style="<?= $u['status'] === 'Active' ? 'color:#ef4444;' : 'color:#10b981;' ?>"
                                        onclick="openStatusModal('<?= htmlspecialchars($u['email'], ENT_QUOTES) ?>', '<?= htmlspecialchars($u['user_name'], ENT_QUOTES) ?>', '<?= $u['status'] === 'Active' ? 'De-Active' : 'Active' ?>')">
                                    <span class="material-symbols-outlined" style="font-size: 17px;">
                                        <?= $u['status'] === 'Active' ? 'block' : 'check_circle' ?>
                                    </span>
                                </button>

                                <!-- Delete User -->
                                <button class="btn-table-action delete" title="Delete User" onclick="openDeleteModal('<?= htmlspecialchars($u['email'], ENT_QUOTES) ?>', '<?= htmlspecialchars($u['user_name'], ENT_QUOTES) ?>')">
                                    <span class="material-symbols-outlined" style="font-size: 17px;">delete</span>
                                </button>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>