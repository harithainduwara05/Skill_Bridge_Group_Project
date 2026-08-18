document.addEventListener('DOMContentLoaded', () => {
    const btnToggle = document.getElementById('btnToggleNotifications');
    const dropdown = document.getElementById('notificationsDropdown');
    const markReadBtn = document.getElementById('btnMarkAllRead');

    // Toggle dropdown overlay visibility on bell icon click
    btnToggle.addEventListener('click', (e) => {
        e.stopPropagation();
        dropdown.classList.toggle('show');
    });

    // Stop propagation on clicking inside the dropdown container
    dropdown.addEventListener('click', (e) => {
        e.stopPropagation();
    });

    // Close popup when clicking outside
    document.addEventListener('click', () => {
        dropdown.classList.remove('show');
    });

    // Mark all as read button click handler
    if (markReadBtn) {
        markReadBtn.addEventListener('click', () => {
            const unreadItems = document.querySelectorAll('.notification-item.unread');
            const unreadDots = document.querySelectorAll('.unread-dot');
            const subCounter = document.querySelector('.sub-counter');

            unreadItems.forEach(item => item.classList.remove('unread'));
            unreadDots.forEach(dot => dot.remove());

            if (subCounter) {
                subCounter.textContent = '0 new notifications';
            }
        });
    }
});