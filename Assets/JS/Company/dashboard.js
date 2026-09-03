/**
 * Company Dashboard Interactive Controls - SkillBridge
 */
document.addEventListener('DOMContentLoaded', function() {
    // Recent Internships Slider Controls
    const prevBtn = document.querySelector('.slider-btn.prev');
    const nextBtn = document.querySelector('.slider-btn.next');
    const internshipsGrid = document.querySelector('.internships-grid');

    if (prevBtn && nextBtn && internshipsGrid) {
        let currentIndex = 0;
        
        prevBtn.addEventListener('click', function() {
            internshipsGrid.style.transform = 'scale(0.98)';
            setTimeout(() => {
                internshipsGrid.style.transform = 'scale(1)';
            }, 150);
        });

        nextBtn.addEventListener('click', function() {
            internshipsGrid.style.transform = 'scale(0.98)';
            setTimeout(() => {
                internshipsGrid.style.transform = 'scale(1)';
            }, 150);
        });
    }

    // Action menu triggers
    const actionBtns = document.querySelectorAll('.action-menu-btn');
    actionBtns.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            // Subtle feedback for dummy data
            btn.style.transform = 'rotate(90deg)';
            setTimeout(() => {
                btn.style.transform = 'rotate(0deg)';
            }, 250);
        });
    });
});
