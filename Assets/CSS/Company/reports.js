document.addEventListener('DOMContentLoaded', () => {
    
    // 1. Render Applications Over Time Area Chart
    const ctxApp = document.getElementById('applicationsChart').getContext('2d');
    
    const techGradient = ctxApp.createLinearGradient(0, 0, 0, 250);
    techGradient.addColorStop(0, 'rgba(8, 37, 68, 0.15)');
    techGradient.addColorStop(1, 'rgba(8, 37, 68, 0.0)');

    const creativeGradient = ctxApp.createLinearGradient(0, 0, 0, 250);
    creativeGradient.addColorStop(0, 'rgba(249, 115, 22, 0.15)');
    creativeGradient.addColorStop(1, 'rgba(249, 115, 22, 0.0)');

    new Chart(ctxApp, {
        type: 'line',
        data: {
            labels: ['WK 1', 'WK 2', 'WK 3', 'WK 4', 'WK 5', 'WK 6', 'WK 7', 'WK 8'],
            datasets: [
                {
                    label: 'Technical Roles',
                    data: [150, 220, 180, 290, 310, 260, 380, 420],
                    borderColor: '#082544',
                    backgroundColor: techGradient,
                    fill: true,
                    tension: 0.4,
                    borderWidth: 3,
                    pointRadius: 0,
                    pointHoverRadius: 6
                },
                {
                    label: 'Creative Roles',
                    data: [80, 110, 95, 140, 160, 130, 210, 250],
                    borderColor: '#f97316',
                    backgroundColor: creativeGradient,
                    fill: true,
                    tension: 0.4,
                    borderWidth: 3,
                    pointRadius: 0,
                    pointHoverRadius: 6
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: { color: '#64748b', font: { weight: '700', size: 10 } }
                },
                y: {
                    display: false,
                    grid: { display: false }
                }
            }
        }
    });

    // 2. Render Skills Distribution Doughnut Chart
    const ctxSkills = document.getElementById('skillsChart').getContext('2d');
    new Chart(ctxSkills, {
        type: 'doughnut',
        data: {
            labels: ['React / Frontend', 'Python / Backend', 'UX/UI Design', 'Product Management'],
            datasets: [{
                data: [40, 30, 20, 10],
                backgroundColor: ['#0f172a', '#f97316', '#38bdf8', '#64748b'],
                borderWidth: 2,
                borderColor: '#ffffff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '75%',
            plugins: {
                legend: { display: false }
            }
        }
    });

    // 3. Time Range Picker Toggle
    const rangeBtns = document.querySelectorAll('.btn-time-range');
    rangeBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            rangeBtns.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
        });
    });
});