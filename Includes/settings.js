document.addEventListener('DOMContentLoaded', () => {
    // Tab Switching Logic
    const tabLinks = document.querySelectorAll('.tab-link');

    tabLinks.forEach(link => {
        link.addEventListener('click', (e) => {
            e.preventDefault();
            tabLinks.forEach(t => t.classList.remove('active'));
            link.classList.add('active');
        });
    });

    // Invite Member Button Action
    const inviteBtn = document.getElementById('btnInviteMember');
    if (inviteBtn) {
        inviteBtn.addEventListener('click', () => {
            alert('Invite Team Member modal triggered.');
        });
    }

    // Action Menu Buttons for Team Members
    const actionMenuBtns = document.querySelectorAll('.btn-action-menu');
    actionMenuBtns.forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.stopPropagation();
            console.log('Action menu clicked');
        });
    });
});