<div class="notif-filter-tabs">
    <a href="?tab=all" class="notif-tab <?= $filterTab === 'all' ? 'active' : '' ?>">
        All
        <span class="notif-tab-badge"><?= $totalCount ?></span>
    </a>
    <a href="?tab=unread" class="notif-tab <?= $filterTab === 'unread' ? 'active' : '' ?>">
        Unread
        <span class="notif-tab-badge"><?= $unreadCount ?></span>
    </a>
    <a href="?tab=inquiries" class="notif-tab <?= $filterTab === 'inquiries' ? 'active' : '' ?>">
        Contact Inquiries
        <span class="notif-tab-badge"><?= $inquiryCount ?></span>
    </a>
</div>