document.addEventListener('DOMContentLoaded', () => {
    const btnToggle = document.getElementById('btnToggleNotifications');
    const dropdown = document.getElementById('notificationsDropdown');
    const markReadBtn = document.querySelector('.btn-mark-read');

    // Toggle dropdown visibility on bell click
    btnToggle.addEventListener('click', (e) => {
        e.stopPropagation();
        dropdown.classList.toggle('show');
    });

    // Prevent closing when clicking inside the dropdown
    dropdown.addEventListener('click', (e) => {
        e.stopPropagation();
    });

    // Close dropdown when clicking anywhere outside
    document.addEventListener('click', () => {
        dropdown.classList.remove('show');
    });

    // "Mark all as read" functionality
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