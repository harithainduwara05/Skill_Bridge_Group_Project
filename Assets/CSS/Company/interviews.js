document.addEventListener('DOMContentLoaded', () => {
    // Handle Schedule Interview button click
    const scheduleBtn = document.querySelector('.btn-schedule');
    if (scheduleBtn) {
        scheduleBtn.addEventListener('click', () => {
            alert('Schedule Interview modal/page opened.');
        });
    }

    // Interactive Action Buttons
    const actionBtns = document.querySelectorAll('.btn-action');
    actionBtns.forEach(btn => {
        btn.addEventListener('click', (e) => {
            const row = e.target.closest('tr');
            const candidateName = row.querySelector('.candidate-name').textContent;
            
            if (btn.querySelector('.fa-eye')) {
                console.log(`Viewing details for candidate: ${candidateName}`);
            } else if (btn.querySelector('.fa-pen-to-square')) {
                console.log(`Editing interview for candidate: ${candidateName}`);
            }
        });
    });
});