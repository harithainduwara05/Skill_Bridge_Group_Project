<div class="card-header">
    <div>
        <h3>Institution List</h3>
    </div>
    <div class="univ-table-actions">
        <form method="GET" action="" style="display:flex; gap:10px; align-items:center;">
            <select name="status" class="univ-select-filter" onchange="this.form.submit()">
                <option value="all" <?= $selectedStatus === 'all' ? 'selected' : '' ?>>All Statuses</option>
                <option value="Active" <?= $selectedStatus === 'Active' ? 'selected' : '' ?>>Active</option>
                <option value="Hold" <?= $selectedStatus === 'Hold' ? 'selected' : '' ?>>Hold</option>
                <option value="Inactive" <?= $selectedStatus === 'Inactive' ? 'selected' : '' ?>>Inactive
                </option>
            </select>

            <div class="univ-search-box">
                <span class="material-symbols-outlined" style="font-size:18px;color:#9ca3af;">search</span>
                <input type="text" name="search" placeholder="Search name, email, org..."
                    id="univSearchInput" value="<?= htmlspecialchars($searchQuery) ?>" autocomplete="off">
            </div>
        </form>
        <button type="button" class="univ-icon-btn" title="Export CSV" id="exportBtn">
            <span class="material-symbols-outlined" style="font-size:18px;">download</span>
        </button>
    </div>
</div>