 <div class="card">
    <div class="card-header">
        <h3>Recent Registrations</h3>
        <a href="User_Management.php" class="btn-outline">View All Users</a>
    </div>
    <div class="card-body" style="padding:0; overflow-x: auto;">
        <table class="data-table">
            <?php 
                $TableColumns = ["User", "Role", "Organization", "Status"];
                include __DIR__ . "/TableHeder.php";
            ?>
            <tbody>
                <?php
                $limit = (count($allusers) < 5) ? count($allusers) : 5;
                for ($x = 0; $x < $limit; $x++) {
                    $userRole = strtolower($allusers[$x]['role'] ?? 'student');
                    $userStatus = $allusers[$x]['status'] ?? 'Active';
                    $statusClass = strtolower($userStatus);
                    if ($statusClass === 'de-active' || $statusClass === 'deactive') {
                        $statusClass = 'deactive';
                    } elseif ($statusClass === 'pending') {
                        $statusClass = 'pending';
                    } else {
                        $statusClass = 'active';
                    }
                    ?>
                    <tr>
                        <td>
                            <div class="user-info">
                                <div class="user-details">
                                    <div class="user-name">
                                        <?php echo htmlspecialchars($allusers[$x]['user_name']); ?></div>
                                    <div class="user-email"><?php echo htmlspecialchars($allusers[$x]['email']); ?>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td><span
                                class="badge-role <?= $userRole ?>"><?php echo htmlspecialchars(ucfirst($allusers[$x]['role'])); ?></span>
                        </td>
                        <td><?php echo htmlspecialchars($allusers[$x]['organization_name'] ?: 'N/A'); ?></td>
                        <td><span
                                class="badge-status-pill <?= $statusClass ?>"><?php echo htmlspecialchars(ucfirst($userStatus)); ?></span>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>