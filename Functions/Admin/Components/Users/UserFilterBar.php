<div class="user-filter-bar">
    <!-- Role Tabs -->
    <div class="user-role-tabs">
        <a href="?role=all&status=<?= urlencode($selectedStatus) ?>&search=<?= urlencode($searchQuery) ?>" 
            class="user-role-tab <?= $selectedRole === 'all' ? 'active' : '' ?>">
            All Users <span class="tab-badge"><?= $stats['total'] ?></span>
        </a>
        <a href="?role=student&status=<?= urlencode($selectedStatus) ?>&search=<?= urlencode($searchQuery) ?>" 
            class="user-role-tab <?= $selectedRole === 'student' ? 'active' : '' ?>">
            Students <span class="tab-badge"><?= $stats['students'] ?></span>
        </a>
        <a href="?role=company&status=<?= urlencode($selectedStatus) ?>&search=<?= urlencode($searchQuery) ?>" 
            class="user-role-tab <?= $selectedRole === 'company' ? 'active' : '' ?>">
            Companies <span class="tab-badge"><?= $stats['companies'] ?></span>
        </a>
        <a href="?role=organization&status=<?= urlencode($selectedStatus) ?>&search=<?= urlencode($searchQuery) ?>" 
            class="user-role-tab <?= $selectedRole === 'organization' ? 'active' : '' ?>">
            Organizations <span class="tab-badge"><?= $stats['organizations'] ?></span>
        </a>
        <a href="?role=admin&status=<?= urlencode($selectedStatus) ?>&search=<?= urlencode($searchQuery) ?>" 
            class="user-role-tab <?= $selectedRole === 'admin' ? 'active' : '' ?>">
            Admins <span class="tab-badge"><?= $stats['admins'] ?></span>
        </a>
    </div>

    <!-- Right search & dropdown -->
    <div class="user-toolbar-right">
        <form method="GET" action="" style="display:flex; gap:10px; align-items:center;">
            <input type="hidden" name="role" value="<?= htmlspecialchars($selectedRole) ?>">
            
            <select name="status" class="user-select-filter" onchange="this.form.submit()">
                <option value="all" <?= $selectedStatus === 'all' ? 'selected' : '' ?>>All Statuses</option>
                <option value="Active" <?= $selectedStatus === 'Active' ? 'selected' : '' ?>>Active</option>
                <option value="De-Active" <?= $selectedStatus === 'De-Active' ? 'selected' : '' ?>>De-Active</option>
                <option value="Pending" <?= $selectedStatus === 'Pending' ? 'selected' : '' ?>>Pending</option>
            </select>

            <div class="user-search-box">
                <span class="material-symbols-outlined">search</span>
                <input type="text" name="search" placeholder="Search name, email, org..." value="<?= htmlspecialchars($searchQuery) ?>">
            </div>
        </form>
    </div>
</div>