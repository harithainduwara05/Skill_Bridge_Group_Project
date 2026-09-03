<?php
require_once __DIR__ . '/../Session/Session.php';
require_once __DIR__ . '/../Config/db.php';

$user = function_exists('current_user') ? current_user() : null;
$notifications = [];
$name = "User";
$role = "Student";
$image = null;

// ================================
// GET USER DETAILS
// ================================
if($user && isset($conn)){
    $name = $user['username'] ?? $user['Name'] ?? "User";
    $email = $user['Email'] ?? $user['email'] ?? null;
    
    if(!empty($user['role'])){
        $role = ucfirst($user['role']);
    }
    
    if($email){
        $sql = "
            SELECT
                u.Email,
                u.role,
                COALESCE(s.Name, a.Name, c.contactPersonName, o.contactPersonName, c.Name, o.Name) AS db_name,
                COALESCE(s.profile_image, a.profile_image, c.profile_img) AS profile_image
            FROM user u
            LEFT JOIN student s ON u.Email = s.Email
            LEFT JOIN admin a ON u.Email = a.Email
            LEFT JOIN company c ON u.Email = c.Email
            LEFT JOIN organization o ON u.Email = o.Email
            WHERE u.Email = ?
            LIMIT 1
        ";
        $stmt = $conn->prepare($sql);
        if($stmt){
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            if($data = $result->fetch_assoc()){
                if(!empty($data['db_name'])){
                    $name = $data['db_name'];
                }
                if(!empty($data['role'])){
                    $role = ucfirst($data['role']);
                }
                if(!empty($data['profile_image'])){
                    $image = $data['profile_image'];
                }
            }
            $stmt->close();
        }
    }
}

// ================================
// NOTIFICATIONS
// ================================
if(!empty($email) && isset($conn)){
    $sql = "
        SELECT
            notification_id,
            title,
            message,
            created_at
        FROM notifications
        WHERE Email = ?
        ORDER BY notification_id DESC
        LIMIT 5
    ";
    $stmt = $conn->prepare($sql);
    if($stmt){
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $notifications = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    }
}

$initial = !empty(trim($name)) ? strtoupper(mb_substr(trim($name), 0, 1)) : 'U';
$notif_url = "/Skill_Bridge_Group_Project/Functions/Dashboards/" . ($role ?: "Student") . "/notifications.php";
$base_url = $GLOBALS['BASE_URL'] ?? '/Skill_Bridge_Group_Project';

$profile_file = __DIR__ . '/../Functions/Dashboards/' . ($role ?: 'Student') . '/profile.php';
$profile_url = file_exists($profile_file)
    ? $base_url . "/Functions/Dashboards/" . ($role ?: "Student") . "/profile.php"
    : "";

// Resolve Profile Image URL with dynamic role directory check
$image_src = null;
if (!empty($image)) {
    if (str_starts_with($image, 'http://') || str_starts_with($image, 'https://') || str_starts_with($image, '/')) {
        $image_src = $image;
    } elseif (file_exists(__DIR__ . '/../Assets/Images/Admin/' . $image)) {
        $image_src = $base_url . '/Assets/Images/Admin/' . htmlspecialchars($image);
    } elseif (file_exists(__DIR__ . '/../Assets/Images/Student/' . $image)) {
        $image_src = $base_url . '/Assets/Images/Student/' . htmlspecialchars($image);
    } elseif (file_exists(__DIR__ . '/../Assets/Images/Company/' . $image)) {
        $image_src = $base_url . '/Assets/Images/Company/' . htmlspecialchars($image);
    } elseif (file_exists(__DIR__ . '/../uploads/' . $image)) {
        $image_src = $base_url . '/uploads/' . htmlspecialchars($image);
    } else {
        $targetFolder = ($role === 'Admin') ? 'Admin' : (($role === 'Company') ? 'Company' : 'Student');
        $image_src = $base_url . '/Assets/Images/' . $targetFolder . '/' . htmlspecialchars($image);
    }
}
?>
<link rel="stylesheet" href="<?php echo $base_url; ?>/Assets/CSS/header.css?v=<?php echo time(); ?>">

<header class="top-header">
    <div class="header-left">
        <button class="sidebar-toggle-btn" id="sidebarToggleBtn" type="button" aria-label="Toggle Navigation" title="Toggle Navigation">
            <span class="material-symbols-outlined">menu</span>
        </button>
        <div class="search-box">
            <span class="material-symbols-outlined">search</span>
            <input type="text" id="globalDashboardSearch" placeholder="Search for projects, skills, or internships...">
        </div>
    </div>

    <div class="header-right">
        <!-- NOTIFICATION -->
        <div class="notification-wrapper">
            <button class="notification" onclick="toggleNotifications(event)" title="Notifications" aria-label="Notifications" type="button">
                <span class="material-symbols-outlined">notifications</span>
                <?php if(!empty($notifications) && count($notifications) > 0): ?>
                    <span class="notification-dot"></span>
                <?php endif; ?>
            </button>

            <div class="notification-popup" id="notificationPopup" onclick="event.stopPropagation()">
                <div class="notification-header">
                    <h3>Notifications</h3>
                    <a href="<?php echo htmlspecialchars($notif_url); ?>" class="mark-all-read">View All</a>
                </div>
                
                <div class="notification-count">
                    <span><?php echo count($notifications); ?> new notification<?php echo count($notifications) == 1 ? '' : 's'; ?></span>
                </div>

                <div class="notification-list">
                    <?php if(empty($notifications)): ?>
                        <div class="notification-empty">
                            <span class="material-symbols-outlined">notifications_off</span>
                            <p>No new notifications</p>
                        </div>
                    <?php else: ?>
                        <?php foreach($notifications as $notification): ?>
                            <div class="notification-item">
                                <div class="notification-icon">
                                    <span class="material-symbols-outlined">notifications</span>
                                </div>
                                <div class="notification-content">
                                    <strong><?php echo htmlspecialchars($notification['title'] ?? 'Notification'); ?></strong>
                                    <p><?php echo htmlspecialchars($notification['message'] ?? ''); ?></p>
                                    <small><?php echo htmlspecialchars($notification['created_at'] ?? ''); ?></small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <a class="view-all" href="<?php echo htmlspecialchars($notif_url); ?>">
                    View All Notifications →
                </a>
            </div>
        </div>

        <!-- PROFILE -->
        <div class="profile" <?php if (!empty($profile_url)): ?>onclick="window.location.href='<?php echo htmlspecialchars($profile_url); ?>'" title="Edit Profile & Settings"<?php endif; ?>>
            <div class="profile-info">
                <h4><?php echo htmlspecialchars($name); ?></h4>
                <small><?php echo htmlspecialchars($role); ?></small>
            </div>
            <?php if(!empty($image_src)): ?>
                <img
                    class="profile-avatar"
                    src="<?php echo $image_src; ?>?v=<?php echo time(); ?>"
                    alt="<?php echo htmlspecialchars($name); ?>"
                    onerror="this.style.display='none'; if(this.nextElementSibling) this.nextElementSibling.style.display='flex';"
                >
                <div class="profile-avatar default-avatar" style="display:none;">
                    <?php echo htmlspecialchars($initial); ?>
                </div>
            <?php else: ?>
                <div class="profile-avatar default-avatar">
                    <?php echo htmlspecialchars($initial); ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</header>

<div class="sidebar-backdrop" id="sidebarBackdrop"></div>

<script>
    function toggleNotifications(event){
        if(event){
            event.stopPropagation();
        }
        var popup = document.getElementById("notificationPopup");
        if(popup){
            popup.classList.toggle("show");
        }
    }

    document.addEventListener("click", function(e){
        var wrapper = document.querySelector(".notification-wrapper");
        var popup = document.getElementById("notificationPopup");
        if(popup && wrapper && !wrapper.contains(e.target)){
            popup.classList.remove("show");
        }
    });

    // Responsive Sidebar & Search Logic
    (function(){
        function setupResponsiveDashboard() {
            var toggleBtn = document.getElementById("sidebarToggleBtn");
            var sidebar = document.querySelector(".sidebar");
            var backdrop = document.getElementById("sidebarBackdrop");
            var searchInput = document.getElementById("globalDashboardSearch");

            function toggleSidebar(e) {
                if (e) e.stopPropagation();
                document.body.classList.toggle("sidebar-open");
                if (sidebar) sidebar.classList.toggle("open");
            }

            function closeSidebar() {
                document.body.classList.remove("sidebar-open");
                if (sidebar) sidebar.classList.remove("open");
            }

            if (toggleBtn && !toggleBtn._hasListener) {
                toggleBtn._hasListener = true;
                toggleBtn.addEventListener("click", toggleSidebar);
            }

            if (backdrop && !backdrop._hasListener) {
                backdrop._hasListener = true;
                backdrop.addEventListener("click", closeSidebar);
            }

            function adjustSearch() {
                if (searchInput) {
                    if (window.innerWidth <= 600) {
                        searchInput.placeholder = "Search...";
                    } else if (window.innerWidth <= 850) {
                        searchInput.placeholder = "Search projects, skills...";
                    } else {
                        searchInput.placeholder = "Search for projects, skills, or internships...";
                    }
                }
            }

            window.addEventListener("resize", adjustSearch);
            adjustSearch();
        }

        if (document.readyState === "loading") {
            document.addEventListener("DOMContentLoaded", setupResponsiveDashboard);
        } else {
            setupResponsiveDashboard();
        }
    })();
</script>