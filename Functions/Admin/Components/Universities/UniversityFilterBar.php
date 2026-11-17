<div class="card-header">
    <div>
        <h3>Institution List</h3>
    </div>
    <div class="univ-table-actions">
        <div style="display:flex; gap:10px; align-items:center;">
            <select id="univStatusFilter" class="univ-select-filter">
                <option value="all">All Statuses</option>
                <option value="active">Active</option>
                <option value="hold">Hold</option>
                <option value="inactive">Inactive</option>
            </select>

            <div class="univ-search-box">
                <span class="material-symbols-outlined" style="font-size:18px;color:#9ca3af;">search</span>
                <input type="text" placeholder="Search name, faculty, domain..."
                    id="univSearchInput" autocomplete="off">
            </div>
        </div>
        <button type="button" class="univ-icon-btn" title="Export CSV" id="exportBtn">
            <span class="material-symbols-outlined" style="font-size:18px;">download</span>
        </button>
    </div>
</div>
