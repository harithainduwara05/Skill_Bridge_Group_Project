<div class="pm-toolbar-card">
    <div class="pm-toolbar-row">
        <div class="pm-filters-left">
            
            <!-- Search Input -->
            <div class="pm-search-wrap">
                <span class="material-symbols-outlined">search</span>
                <input type="text" id="pmSearchInput" class="pm-search-input" placeholder="Search projects...">
            </div>

            <!-- Project Status Filter (Published, Review, Active, Hold, Completed, Rejected) -->
            <div class="pm-select-wrap">
                <select id="pmStatusFilter" class="pm-select" aria-label="Filter by Project Status">
                    <option value="all">Project Status</option>
                    <option value="published">Published</option>
                    <option value="review">Review</option>
                    <option value="active">Active</option>
                    <option value="hold">Hold</option>
                    <option value="completed">Completed</option>
                    <option value="rejected">Rejected</option>
                </select>
                <span class="material-symbols-outlined select-chevron">expand_more</span>
            </div>

            <!-- Project Type Filter -->
            <div class="pm-select-wrap">
                <select id="pmTypeFilter" class="pm-select" aria-label="Filter by Project Type">
                    <option value="all">Project Type</option>
                    <?php if (!empty($categoriesList)): ?>
                        <?php foreach ($categoriesList as $cat): ?>
                            <option value="<?= htmlspecialchars(strtolower($cat)) ?>"><?= htmlspecialchars($cat) ?></option>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <option value="education">Education Technology</option>
                        <option value="health">HealthTech</option>
                        <option value="fintech">FinTech</option>
                        <option value="logistics">Logistics & AI</option>
                        <option value="iot">IoT & Sensors</option>
                        <option value="cybersecurity">Cybersecurity</option>
                    <?php endif; ?>
                </select>
                <span class="material-symbols-outlined select-chevron">expand_more</span>
            </div>

        </div>
    </div>
</div>
