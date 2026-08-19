<?php
$userName = $_SESSION['user']['username'] ?? 'Company';
?>

<div class="navbar">

    <!-- Search Box -->
    <div class="search-box">

        <i class="fa-solid fa-magnifying-glass"></i>

        <input
            type="text"
            placeholder="Search candidates, applications..."
        >

    </div>

    <!-- Right Side -->
    <div class="profile-area">

        <!-- Notification -->
        <div class="notification">

            <i class="fa-solid fa-bell"></i>

            <span class="badge">3</span>

        </div>

        <!-- Profile -->
        <div class="profile">

            <img src="../Assets/Images/default-profile.png" alt="Profile">

            <div class="profile-text">

                <strong>
                    <?php echo htmlspecialchars($userName); ?>
                </strong>

                <small>
                    Company
                </small>

            </div>

            <i class="fa-solid fa-chevron-down"></i>

        </div>

    </div>

</div>