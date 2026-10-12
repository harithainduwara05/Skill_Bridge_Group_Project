<div class="cm-toolbar-card">
    <div class="cm-toolbar-row">
        
        <div class="cm-filters-group">
            
            <!-- Search Input -->
            <div class="cm-search-wrap">
                <span class="material-symbols-outlined">search</span>
                <input type="text" id="cmSearchInput" class="cm-search-input" 
                        placeholder="Search projects, organizations, users, complaints..." 
                        autocomplete="off">
            </div>

            <!-- Status Filter -->
            <div class="cm-filter-item">
                <div class="cm-select-wrap">
                    <select id="cmStatusFilter" class="cm-select">
                        <option value="all">All Statuses</option>
                        <option value="pending">Pending</option>
                        <option value="in_progress">In Progress</option>
                        <option value="resolved">Resolved</option>
                        <option value="dismissed">Dismissed</option>
                    </select>
                    <span class="material-symbols-outlined select-chevron">expand_more</span>
                </div>
            </div>

            <!-- Priority Level -->
            <div class="cm-filter-item">
                <div class="cm-select-wrap">
                    <select id="cmPriorityFilter" class="cm-select">
                        <option value="all">All Priorities</option>
                        <option value="urgent">Urgent</option>
                        <option value="high">High</option>
                        <option value="medium">Medium</option>
                        <option value="low">Low</option>
                    </select>
                    <span class="material-symbols-outlined select-chevron">expand_more</span>
                </div>
            </div>

            <!-- Category -->
            <div class="cm-filter-item">
                <div class="cm-select-wrap">
                    <select id="cmCategoryFilter" class="cm-select">
                        <option value="all">All Categories</option>
                        <option value="Technical">Technical</option>
                        <option value="Organization">Organization</option>
                        <option value="Academic">Academic</option>
                        <option value="Project & Milestones">Project & Milestones</option>
                        <option value="User Conduct">User Conduct</option>
                    </select>
                    <span class="material-symbols-outlined select-chevron">expand_more</span>
                </div>
            </div>

        </div>

    </div>
</div>