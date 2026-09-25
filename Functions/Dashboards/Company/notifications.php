<?php
require_once __DIR__ . '/../../../Session/Session.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pageTitle = 'Notifications';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Notifications | SkillBridge</title>

    <link rel="stylesheet"
          href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined">

    <link rel="stylesheet"
          href="../../../Assets/CSS/Company/notifications.css?v=<?= filemtime(__DIR__ . '/../../../Assets/CSS/Company/notifications.css') ?>">
</head>

<body>

<?php
include __DIR__ . '/../../../Includes/company_sidebar.php';
include __DIR__ . '/../../../Includes/dash_header.php';
?>

<main class="content company-notifications-page">

    <!-- PAGE TOP -->
    <div class="notifications-top">

        <div>
            <h1>Notifications</h1>
            <p>
                Stay updated with your latest recruitment pipeline activities.
            </p>
        </div>

        <div class="notifications-top-actions">

            <button type="button"
                    class="mark-read-btn"
                    id="markAllNotifications">

                <span class="material-symbols-outlined">
                    done_all
                </span>

                Mark all as read
            </button>

            <button type="button"
                    class="filter-btn"
                    id="notificationFilterBtn">

                <span class="material-symbols-outlined">
                    filter_list
                </span>

                Filters
            </button>

        </div>

    </div>


    <!-- FILTER POPUP -->
    <div class="notification-filter-menu"
         id="notificationFilterMenu">

        <button type="button"
                class="filter-option active"
                data-filter="all">
            All Notifications
        </button>

        <button type="button"
                class="filter-option"
                data-filter="unread">
            Unread Only
        </button>

        <button type="button"
                class="filter-option"
                data-filter="applications">
            Applications
        </button>

        <button type="button"
                class="filter-option"
                data-filter="interviews">
            Interviews
        </button>

        <button type="button"
                class="filter-option"
                data-filter="internships">
            Internships
        </button>

    </div>


    <!-- MAIN GRID -->
    <div class="notifications-grid">

        <!-- ==========================
             LEFT SIDE
        =========================== -->
        <section class="notification-cards"
                 id="notificationCards">


            <!-- APPLICATION -->
            <article class="notification-card unread"
                     data-type="applications">

                <div class="notification-card-icon application-icon">

                    <span class="material-symbols-outlined">
                        person_add
                    </span>

                </div>

                <div class="notification-card-content">

                    <div class="notification-card-header">

                        <h2>
                            New Application: UX Design Intern
                        </h2>

                        <div class="notification-meta">

                            <span>2 mins ago</span>

                            <span class="notification-status-dot"></span>

                        </div>

                    </div>

                    <p>
                        Alex Rivera has submitted an application for the
                        Summer 2026 Design cohort. Their portfolio matches
                        your 95% match criteria.
                    </p>

                    <div class="notification-card-actions">

                        <button type="button"
                                class="primary-action-btn">
                            View Resume
                        </button>

                        <button type="button"
                                class="secondary-action-btn snooze-btn">
                            Snooze
                        </button>

                    </div>

                </div>

            </article>


            <!-- INTERVIEW -->
            <article class="notification-card unread"
                     data-type="interviews">

                <div class="notification-card-icon interview-icon">

                    <span class="material-symbols-outlined">
                        alarm
                    </span>

                </div>

                <div class="notification-card-content">

                    <div class="notification-card-header">

                        <h2>
                            Interview Reminder
                        </h2>

                        <div class="notification-meta">

                            <span>45 mins ago</span>

                            <span class="notification-status-dot"></span>

                        </div>

                    </div>

                    <p>
                        Final round interview with
                        <strong>Maya Thompson</strong>
                        for Backend Engineer starts in 1 hour via Google Meet.
                    </p>

                    <div class="notification-card-actions">

                        <button type="button"
                                class="meeting-action-btn">
                            Join Meeting
                        </button>

                        <button type="button"
                                class="secondary-action-btn">
                            View Candidate Profile
                        </button>

                    </div>

                </div>

            </article>


            <!-- INTERNSHIP -->
            <article class="notification-card"
                     data-type="internships">

                <div class="notification-card-icon internship-icon">

                    <span class="material-symbols-outlined">
                        history
                    </span>

                </div>

                <div class="notification-card-content">

                    <div class="notification-card-header">

                        <h2>
                            Internship Posting Expiring
                        </h2>

                        <div class="notification-meta">
                            <span>Yesterday</span>
                        </div>

                    </div>

                    <p>
                        Your posting for “Front-end Developer” will expire
                        in 48 hours. Would you like to extend it?
                    </p>

                    <div class="notification-card-actions">

                        <button type="button"
                                class="secondary-action-btn">
                            Extend Posting
                        </button>

                    </div>

                </div>

            </article>


            <!-- EMPTY FILTER STATE -->
            <div class="notification-empty"
                 id="notificationEmpty">

                <span class="material-symbols-outlined">
                    notifications_none
                </span>

                <h3>No notifications found</h3>

                <p>
                    There are no notifications matching this filter.
                </p>

            </div>

        </section>


        <!-- ==========================
             RIGHT SIDE
        =========================== -->

        <aside class="notification-side-column">


            <!-- WEEKLY SUMMARY -->
            <section class="weekly-summary">

                <h2>Weekly Summary</h2>

                <p>
                    You have 12 pending applications to review this week.
                </p>

                <div class="summary-stat">

                    <span>New Apps</span>

                    <strong>+45</strong>

                </div>

                <div class="summary-stat">

                    <span>Interviews</span>

                    <strong>18</strong>

                </div>

                <div class="summary-decoration">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>

            </section>


            <!-- SETTINGS -->
            <section class="notification-settings">

                <h2>Notification Settings</h2>


                <div class="setting-row">

                    <span>Email Alerts</span>

                    <label class="switch">

                        <input type="checkbox"
                               id="emailAlerts"
                               checked>

                        <span class="slider"></span>

                    </label>

                </div>


                <div class="setting-row">

                    <span>Push Notifications</span>

                    <label class="switch">

                        <input type="checkbox"
                               id="pushNotifications">

                        <span class="slider"></span>

                    </label>

                </div>

            </section>

        </aside>

    </div>

    <?php include '../../../Includes/company_dashboard_footer.php'; ?>
</main>

<script src="../../../Assets/JS/Company/notifications.js"></script>

<?php include '../../../Includes/dash_footer.php'; ?>
