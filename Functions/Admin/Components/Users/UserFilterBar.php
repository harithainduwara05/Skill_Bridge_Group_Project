<div class="user-filter-bar">
    <!-- Role Tabs (Instant UI Switching) -->
    <div class="user-role-tabs">
        <button type="button" class="user-role-tab active" data-role="all">
            All Users <span class="tab-badge"><?= $stats['total'] ?></span>
        </button>
        <button type="button" class="user-role-tab" data-role="student">
            Students <span class="tab-badge"><?= $stats['students'] ?></span>
        </button>
        <button type="button" class="user-role-tab" data-role="company">
            Companies <span class="tab-badge"><?= $stats['companies'] ?></span>
        </button>
        <button type="button" class="user-role-tab" data-role="organization">
            Organizations <span class="tab-badge"><?= $stats['organizations'] ?></span>
        </button>
        <button type="button" class="user-role-tab" data-role="admin">
            Admins <span class="tab-badge"><?= $stats['admins'] ?></span>
        </button>
    </div>

    <!-- Right search & dropdown (Instant Real-time Filter) -->
    <div class="user-toolbar-right">
        <div style="display:flex; gap:10px; align-items:center;">
            <select id="userStatusFilter" class="user-select-filter">
                <option value="all">All Statuses</option>
                <option value="active">Active</option>
                <option value="deactive">De-Active</option>
                <option value="pending">Pending</option>
            </select>

            <div class="user-search-box">
                <span class="material-symbols-outlined">search</span>
                <input type="text" id="userSearchInput" placeholder="Search name, email, org..." autocomplete="off">
            </div>
        </div>
    </div>
</div>
