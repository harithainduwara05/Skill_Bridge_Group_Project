/**
 * SkillBridge - Company Dashboard JavaScript
 * Handles interactivity and animations for the company dashboard
 */

document.addEventListener('DOMContentLoaded', function() {
    
    // Initialize dashboard features
    initializeCharts();
    initializeTableActions();
    initializeTooltips();
    initializeAnimations();
    
});

/**
 * Initialize Chart.js for dashboard statistics
 */
function initializeCharts() {
    // Check if we need to add chart functionality in future
    console.log('Charts initialized');
}

/**
 * Initialize table action buttons
 */
function initializeTableActions() {
    const actionBtns = document.querySelectorAll('.action-btn');
    
    actionBtns.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            const menu = createActionMenu();
            menu.style.position = 'absolute';
            menu.style.right = '10px';
            menu.style.top = e.target.offsetTop + 'px';
            btn.parentElement.appendChild(menu);
        });
    });
}

/**
 * Create action menu for table rows
 */
function createActionMenu() {
    const menu = document.createElement('div');
    menu.className = 'action-menu';
    menu.innerHTML = `
        <button class="action-menu-item" onclick="viewCandidate()">
            <span class="material-symbols-outlined">visibility</span>
            View Profile
        </button>
        <button class="action-menu-item" onclick="scheduleInterview()">
            <span class="material-symbols-outlined">event_note</span>
            Schedule Interview
        </button>
        <button class="action-menu-item" onclick="sendMessage()">
            <span class="material-symbols-outlined">mail</span>
            Send Message
        </button>
        <button class="action-menu-item reject" onclick="rejectCandidate()">
            <span class="material-symbols-outlined">close</span>
            Reject
        </button>
    `;
    
    // Close menu when clicking outside
    document.addEventListener('click', function closeMenu(e) {
        if (!e.target.closest('.action-menu') && !e.target.closest('.action-btn')) {
            menu.remove();
            document.removeEventListener('click', closeMenu);
        }
    });
    
    return menu;
}

/**
 * View candidate profile
 */
function viewCandidate() {
    console.log('Viewing candidate profile');
    // Implement view profile functionality
}

/**
 * Schedule interview with candidate
 */
function scheduleInterview() {
    const modal = document.createElement('div');
    modal.className = 'interview-modal';
    modal.innerHTML = `
        <div class="modal-content">
            <div class="modal-header">
                <h3>Schedule Interview</h3>
                <button class="close-btn" onclick="this.closest('.interview-modal').remove()">×</button>
            </div>
            <form class="modal-body" onsubmit="submitInterview(event)">
                <div class="form-group">
                    <label>Interview Date</label>
                    <input type="date" required>
                </div>
                <div class="form-group">
                    <label>Interview Time</label>
                    <input type="time" required>
                </div>
                <div class="form-group">
                    <label>Interview Type</label>
                    <select required>
                        <option>Video Call</option>
                        <option>Phone Call</option>
                        <option>In-Person</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Notes</label>
                    <textarea placeholder="Add any notes for this interview"></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-cancel" onclick="this.closest('.interview-modal').remove()">Cancel</button>
                    <button type="submit" class="btn-submit">Schedule Interview</button>
                </div>
            </form>
        </div>
    `;
    
    document.body.appendChild(modal);
}

/**
 * Submit interview scheduling
 */
function submitInterview(event) {
    event.preventDefault();
    console.log('Interview scheduled');
    // Implement interview scheduling
    event.target.closest('.interview-modal').remove();
    showNotification('Interview scheduled successfully', 'success');
}

/**
 * Send message to candidate
 */
function sendMessage() {
    console.log('Sending message to candidate');
    // Implement message functionality
    showNotification('Message sent successfully', 'success');
}

/**
 * Reject candidate
 */
function rejectCandidate() {
    if (confirm('Are you sure you want to reject this candidate?')) {
        console.log('Candidate rejected');
        showNotification('Candidate rejected', 'info');
    }
}

/**
 * Initialize tooltips
 */
function initializeTooltips() {
    const tooltipElements = document.querySelectorAll('[data-tooltip]');
    
    tooltipElements.forEach(el => {
        el.addEventListener('mouseenter', function() {
            const tooltip = document.createElement('div');
            tooltip.className = 'tooltip';
            tooltip.textContent = this.getAttribute('data-tooltip');
            this.appendChild(tooltip);
        });
        
        el.addEventListener('mouseleave', function() {
            const tooltip = this.querySelector('.tooltip');
            if (tooltip) tooltip.remove();
        });
    });
}

/**
 * Initialize animations on scroll
 */
function initializeAnimations() {
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate-in');
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.1 });
    
    document.querySelectorAll('.stat-card, .internship-card, .interview-item').forEach(el => {
        observer.observe(el);
    });
}

/**
 * Show notification toast
 */
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.innerHTML = `
        <div class="notification-content">
            <span class="notification-message">${message}</span>
            <button class="notification-close" onclick="this.closest('.notification').remove()">×</button>
        </div>
    `;
    
    document.body.appendChild(notification);
    
    // Auto-remove after 3 seconds
    setTimeout(() => {
        notification.remove();
    }, 3000);
}

/**
 * Filter table data
 */
function filterTable(filterValue) {
    const table = document.querySelector('table');
    const rows = table.querySelectorAll('tbody tr');
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        if (text.includes(filterValue.toLowerCase())) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

/**
 * Export data to CSV
 */
function exportToCSV() {
    const table = document.querySelector('table');
    let csv = [];
    
    // Get headers
    const headers = [];
    table.querySelectorAll('th').forEach(th => {
        headers.push(th.textContent);
    });
    csv.push(headers.join(','));
    
    // Get rows
    table.querySelectorAll('tbody tr').forEach(tr => {
        const row = [];
        tr.querySelectorAll('td').forEach(td => {
            row.push('"' + td.textContent.replace(/"/g, '""') + '"');
        });
        csv.push(row.join(','));
    });
    
    // Create blob and download
    const blob = new Blob([csv.join('\n')], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'applications.csv';
    a.click();
    window.URL.revokeObjectURL(url);
}

/**
 * Print dashboard
 */
function printDashboard() {
    window.print();
}

/**
 * Refresh dashboard data
 */
function refreshDashboard() {
    location.reload();
}

// Export functions for use
window.viewCandidate = viewCandidate;
window.scheduleInterview = scheduleInterview;
window.submitInterview = submitInterview;
window.sendMessage = sendMessage;
window.rejectCandidate = rejectCandidate;
window.filterTable = filterTable;
window.exportToCSV = exportToCSV;
window.printDashboard = printDashboard;
window.refreshDashboard = refreshDashboard;
