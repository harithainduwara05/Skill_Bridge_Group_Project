<?php
// Generate avatar color based on name
$avatarName = isset($user['username']) ? $user['username'] : 'Admin User';
$roleLabel  = isset($user['role']) ? ucfirst($user['role']) : 'Super Admin';
$initial    = strtoupper(substr($avatarName, 0, 1));

// Pick a consistent color based on first letter
$colors = [
    'A' => '#1e3a5f', 'B' => '#6d28d9', 'C' => '#0369a1', 'D' => '#065f46',
    'E' => '#7c2d12', 'F' => '#1e40af', 'G' => '#4c1d95', 'H' => '#134e4a',
    'I' => '#1f2937', 'J' => '#7f1d1d', 'K' => '#14532d', 'L' => '#0c4a6e',
    'M' => '#312e81', 'N' => '#1e3a5f', 'O' => '#7c3aed', 'P' => '#1d4ed8',
    'Q' => '#064e3b', 'R' => '#831843', 'S' => '#0f172a', 'T' => '#1e3a5f',
    'U' => '#4338ca', 'V' => '#065f46', 'W' => '#1f2937', 'X' => '#312e81',
    'Y' => '#0c4a6e', 'Z' => '#1e293b',
];
$avatarBg = isset($colors[$initial]) ? $colors[$initial] : '#1e3a5f';
?>

<header class="top-header">

    <div class="search-box">
        <span class="material-symbols-outlined">search</span>
        <input type="text" placeholder="Search for projects, users...">
    </div>

    <div class="header-right">

        <button class="notification" type="button" title="Notifications">
            <span class="material-symbols-outlined">notifications</span>
            <span class="notification-dot"></span>
        </button>

        <div class="profile">
            <div class="profile-info">
                <h4><?php echo htmlspecialchars($avatarName); ?></h4>
                <small><?php echo htmlspecialchars($roleLabel); ?></small>
            </div>

            <!-- Avatar with initial instead of broken image -->
            <div class="profile-avatar" style="background: <?php echo $avatarBg; ?>;">
                <?php echo $initial; ?>
            </div>
        </div>

    </div>

</header>
