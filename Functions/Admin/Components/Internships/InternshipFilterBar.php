<div class="im-toolbar-card">
    <div class="im-toolbar-controls">
        
        <!-- Search Box -->
        <div class="im-search-wrap">
            <span class="material-symbols-outlined">search</span>
            <input type="text" id="imSearchInput" class="im-search-input" placeholder="Search internships by title, ID, or company...">
        </div>

        <!-- Status Filter (4 Statuses: Active, Suspended, Terminated, Closed) -->
        <div class="im-select-wrap">
            <span class="material-symbols-outlined im-select-icon">tune</span>
            <select id="imStatusFilter" class="im-select" aria-label="Filter by Status">
                <option value="all" selected>Status: All</option>
                <option value="active">Active</option>
                <option value="suspended">Suspended</option>
                <option value="terminated">Terminated</option>
                <option value="closed">Closed</option>
            </select>
            <span class="material-symbols-outlined im-select-chevron">expand_more</span>
        </div>

        <!-- Industry Filter -->
        <div class="im-select-wrap">
            <span class="material-symbols-outlined im-select-icon">domain</span>
            <select id="imIndustryFilter" class="im-select" aria-label="Filter by Industry">
                <option value="all" selected>Industry: All</option>
                <?php if (!empty($industriesList)): ?>
                    <?php foreach ($industriesList as $ind): ?>
                        <option value="<?= htmlspecialchars(strtolower($ind)) ?>"><?= htmlspecialchars($ind) ?></option>
                    <?php endforeach; ?>
                <?php else: ?>
                    <option value="software development">Software Development</option>
                    <option value="artificial intelligence">Artificial Intelligence</option>
                    <option value="mobile development">Mobile Development</option>
                    <option value="cloud computing">Cloud Computing</option>
                    <option value="cybersecurity">Cybersecurity</option>
                <?php endif; ?>
            </select>
            <span class="material-symbols-outlined im-select-chevron">expand_more</span>
        </div>

        <!-- Duration / Mode Filter -->
        <div class="im-select-wrap">
            <span class="material-symbols-outlined im-select-icon">schedule</span>
            <select id="imDurationFilter" class="im-select" aria-label="Filter by Duration or Mode">
                <option value="all" selected>Duration</option>
                <option value="full-time">Full-time</option>
                <option value="part-time">Part-time</option>
                <option value="remote">Remote</option>
                <option value="hybrid">Hybrid</option>
                <option value="on-site">On-site</option>
            </select>
            <span class="material-symbols-outlined im-select-chevron">expand_more</span>
        </div>

    </div>
</div>