<?php

include "../../Config/db.php";
include "../../Session/Session.php";

require_role('organization');

// Backend (PHP + SQL) of this page: loads the notifications from the DB
require_once __DIR__ . "/../../Backend/Organization/notifications.php";

$extra_css = '<link rel="stylesheet" href="../../Assets/CSS/Organization/notifications.css">';
include "../../Includes/org_sidebar.php";
include "../../Includes/dash_header.php";
?>




<main class="content">

    <div class="nc-head">
        <div>
            <h1>Notification Center</h1>
            <p>Stay updated on student proposals, your teams and your projects.</p>
        </div>
        <div class="nc-head-actions">
            <button type="button" class="nc-head-btn" id="ncMarkAll"><span class="material-symbols-outlined">done_all</span>Mark All as Read</button>
            <button type="button" class="nc-head-btn" id="ncClearAll"><span class="material-symbols-outlined">delete</span>Clear All</button>
        </div>
    </div>

    <div class="nc-tabs" role="tablist">
        <button type="button" class="nc-tab active" data-tab="all">All</button>
        <button type="button" class="nc-tab" data-tab="unread">Unread</button>
        <button type="button" class="nc-tab" data-tab="archived">Archived</button>
    </div>

    <div class="nc-list" id="ncList"></div>
    <div class="nc-empty" id="ncEmpty" hidden>
        <span class="material-symbols-outlined">notifications_off</span>
        <div id="ncEmptyText">No notifications</div>
    </div>
    <div class="nc-more" id="ncMore" hidden></div>

</main>

<script>
    // Values from PHP (database) used by Assets/JS/Organization/notifications.js
    const ORG_DATA = {
        notifications: <?= json_encode($orgNotifications, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>,
    };
</script>
<script src="../../Assets/JS/Organization/notifications.js?v=<?= filemtime(__DIR__ . '/../../Assets/JS/Organization/notifications.js') ?>"></script>


<?php include "../../Includes/dash_footer.php"; ?>