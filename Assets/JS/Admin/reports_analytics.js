/**
 * ==============================================================================
 * SkillBridge - Reports & Performance Analytics Dashboard Logic (Admin)
 * Handles client-side interactive charts, period toggling, domain tab filtering,
 * export modal configuration, and custom toast notifications.
 * STRICTLY FRONTEND WITH REALISTIC DUMMY DATA (ZERO BACKEND CALLS).
 * ==============================================================================
 */

document.addEventListener('DOMContentLoaded', () => {
    initReportsAnalytics();
});

function initReportsAnalytics() {
    // --------------------------------------------------------------------------
    // State & Dummy Datasets for Periods
    // --------------------------------------------------------------------------
    let currentPeriod = '30d'; // '30d', 'quarter', 'ytd', 'all'
    let currentTab = 'all';

    const periodData = {
        '30d': {
            placedStudents: '1,428',
            placedTrend: '+18.4% vs last mo',
            placementRate: '89.2% Placement Rate',
            companies: '168 Companies',
            companyTrend: '+14 New this month',
            drives: '432 Active Drives',
            projects: '842 Projects',
            projectRate: '94.6% Completed',
            projectReview: '124 In Supervisor Review',
            resolution: '98.1% Resolved',
            resolutionTime: '2.4h Avg Response',
            complaintOpen: '3 Open in Queue',
            monthlyLabels: ['Week 1', 'Week 2', 'Week 3', 'Week 4'],
            applications: [320, 480, 560, 610],
            hired: [190, 290, 380, 420]
        },
        'quarter': {
            placedStudents: '4,150',
            placedTrend: '+24.1% vs Q2',
            placementRate: '91.5% Placement Rate',
            companies: '210 Companies',
            companyTrend: '+32 New this quarter',
            drives: '620 Active Drives',
            projects: '2,480 Projects',
            projectRate: '95.8% Completed',
            projectReview: '180 In Supervisor Review',
            resolution: '99.0% Resolved',
            resolutionTime: '2.1h Avg Response',
            complaintOpen: '5 Open in Queue',
            monthlyLabels: ['July', 'August', 'September'],
            applications: [1200, 1550, 1850],
            hired: [850, 1100, 1420]
        },
        'ytd': {
            placedStudents: '12,890',
            placedTrend: '+38.5% YoY',
            placementRate: '92.8% Placement Rate',
            companies: '340 Companies',
            companyTrend: '+85 New in 2026',
            drives: '1,240 Total Drives',
            projects: '7,650 Projects',
            projectRate: '96.2% Completed',
            projectReview: '310 In Supervisor Review',
            resolution: '98.8% Resolved',
            resolutionTime: '2.6h Avg Response',
            complaintOpen: '3 Open in Queue',
            monthlyLabels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep'],
            applications: [780, 890, 1100, 1250, 1400, 1580, 1720, 1950, 2100],
            hired: [520, 610, 780, 920, 1050, 1200, 1340, 1510, 1680]
        },
        'all': {
            placedStudents: '28,450',
            placedTrend: '+112% All Time',
            placementRate: '93.5% Overall Rate',
            companies: '512 Companies',
            companyTrend: '512 Lifetime Partners',
            drives: '3,890 Drives Conducted',
            projects: '18,400 Projects',
            projectRate: '97.1% Overall Success',
            projectReview: '310 In Supervisor Review',
            resolution: '99.2% Resolved',
            resolutionTime: '2.8h Avg Response',
            complaintOpen: '3 Open in Queue',
            monthlyLabels: ['2023', '2024', '2025', '2026 (YTD)'],
            applications: [3200, 6800, 11400, 15800],
            hired: [2100, 4900, 8900, 12890]
        }
    };

    // --------------------------------------------------------------------------
    // Toast Notification Utility (ZERO window.alert/confirm)
    // --------------------------------------------------------------------------
    const toast = document.getElementById('raToast');
    const toastMsg = document.getElementById('raToastMessage');

    function showToast(message, icon = 'check_circle', color = '#22c55e') {
        if (!toast || !toastMsg) return;
        toastMsg.textContent = message;
        const iconEl = toast.querySelector('.material-symbols-outlined');
        if (iconEl) {
            iconEl.textContent = icon;
            iconEl.style.color = color;
        }
        toast.classList.add('show');
        clearTimeout(toast._timer);
        toast._timer = setTimeout(() => {
            toast.classList.remove('show');
        }, 3600);
    }

    // --------------------------------------------------------------------------
    // Period Pills Click Handler
    // --------------------------------------------------------------------------
    const periodButtons = document.querySelectorAll('.ra-period-btn');
    periodButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            const period = btn.getAttribute('data-period');
            if (!period || period === currentPeriod) return;

            periodButtons.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            currentPeriod = period;

            updateDashboardMetrics(period);
            renderSvgLineChart(period);
            updateDonutChart(period);
            showToast(`Dashboard updated for period: ${btn.textContent.trim()}`);
        });
    });

    function updateDashboardMetrics(period) {
        const d = periodData[period] || periodData['30d'];

        const elPlaced = document.getElementById('raKpiPlaced');
        const elPlacedTrend = document.getElementById('raKpiPlacedTrend');
        const elPlacedSub = document.getElementById('raKpiPlacedSub');

        const elCompanies = document.getElementById('raKpiCompanies');
        const elCompanyTrend = document.getElementById('raKpiCompanyTrend');
        const elCompanySub = document.getElementById('raKpiCompanySub');

        const elProjects = document.getElementById('raKpiProjects');
        const elProjectTrend = document.getElementById('raKpiProjectTrend');
        const elProjectSub = document.getElementById('raKpiProjectSub');

        const elResolution = document.getElementById('raKpiResolution');
        const elResolutionTrend = document.getElementById('raKpiResolutionTrend');
        const elResolutionSub = document.getElementById('raKpiResolutionSub');

        if (elPlaced) elPlaced.textContent = d.placedStudents;
        if (elPlacedTrend) elPlacedTrend.innerHTML = `<span class="material-symbols-outlined">trending_up</span> ${d.placedTrend}`;
        if (elPlacedSub) elPlacedSub.innerHTML = `<span class="material-symbols-outlined">verified</span> ${d.placementRate}`;

        if (elCompanies) elCompanies.textContent = d.companies;
        if (elCompanyTrend) elCompanyTrend.innerHTML = `<span class="material-symbols-outlined">trending_up</span> ${d.companyTrend}`;
        if (elCompanySub) elCompanySub.innerHTML = `<span class="material-symbols-outlined">work</span> ${d.drives}`;

        if (elProjects) elProjects.textContent = d.projects;
        if (elProjectTrend) elProjectTrend.innerHTML = `<span class="material-symbols-outlined">task_alt</span> ${d.projectRate}`;
        if (elProjectSub) elProjectSub.innerHTML = `<span class="material-symbols-outlined">pending_actions</span> ${d.projectReview}`;

        if (elResolution) elResolution.textContent = d.resolution;
        if (elResolutionTrend) elResolutionTrend.innerHTML = `<span class="material-symbols-outlined">timer</span> ${d.resolutionTime}`;
        if (elResolutionSub) elResolutionSub.innerHTML = `<span class="material-symbols-outlined">report_problem</span> ${d.complaintOpen}`;
    }

    // --------------------------------------------------------------------------
    // Navigation Tabs Switching
    // --------------------------------------------------------------------------
    const tabButtons = document.querySelectorAll('.ra-tab-btn');
    const sections = {
        'all': ['secChartsMain', 'secChartsSkills', 'secTableUnis', 'secTableCompanies'],
        'placements': ['secChartsMain', 'secTableCompanies'],
        'universities': ['secChartsSkills', 'secTableUnis'],
        'skills': ['secChartsSkills'],
        'operations': ['secChartsMain', 'secTableUnis', 'secTableCompanies']
    };

    tabButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            const targetTab = btn.getAttribute('data-tab') || 'all';
            tabButtons.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            currentTab = targetTab;

            // Toggle visibility of relevant sections smoothly
            const allSecIds = ['secChartsMain', 'secChartsSkills', 'secTableUnis', 'secTableCompanies'];
            const visibleIds = sections[targetTab] || allSecIds;

            allSecIds.forEach(id => {
                const el = document.getElementById(id);
                if (el) {
                    if (visibleIds.includes(id)) {
                        el.style.display = '';
                        el.style.opacity = '0';
                        setTimeout(() => {
                            el.style.transition = 'opacity 0.3s ease';
                            el.style.opacity = '1';
                        }, 20);
                    } else {
                        el.style.display = 'none';
                    }
                }
            });
        });
    });

    // --------------------------------------------------------------------------
    // Native SVG Interactive Charts (100% Pure Vanilla JS, ZERO External Libraries)
    // --------------------------------------------------------------------------
    const svgTooltip = document.getElementById('raSvgTooltip');
    const svgChartContainer = document.querySelector('.ra-svg-chart-container');

    function renderSvgLineChart(period) {
        const d = periodData[period] || periodData['30d'];
        const apps = d.applications;
        const hired = d.hired;
        const labels = d.monthlyLabels;

        // SVG Coordinate boundaries
        const width = 660;
        const height = 230;
        const paddingLeft = 70;
        const paddingRight = 30;
        const paddingTop = 25;
        const paddingBottom = 35; // y = 195 baseline
        const plotWidth = width - paddingLeft - paddingRight;
        const plotHeight = (height - paddingBottom) - paddingTop; // 170

        // Determine max ceiling for dynamic scaling
        const maxValRaw = Math.max(...apps, ...hired);
        let maxCeil = 800;
        if (maxValRaw > 10000) maxCeil = 18000;
        else if (maxValRaw > 2000) maxCeil = 2400;
        else if (maxValRaw > 1000) maxCeil = 2000;
        else if (maxValRaw > 500) maxCeil = 800;
        else maxCeil = 500;

        // Update Y Axis numeric labels
        const yLabels = [
            { id: 'raYLabel4', val: maxCeil },
            { id: 'raYLabel3', val: Math.round(maxCeil * 0.75) },
            { id: 'raYLabel2', val: Math.round(maxCeil * 0.5) },
            { id: 'raYLabel1', val: Math.round(maxCeil * 0.25) }
        ];
        yLabels.forEach(yl => {
            const el = document.getElementById(yl.id);
            if (el) {
                el.textContent = yl.val >= 1000 ? (yl.val / 1000) + 'k' : yl.val;
            }
        });

        // Compute coordinate points
        const numPoints = labels.length;
        const getX = (i) => numPoints === 1 ? (paddingLeft + plotWidth / 2) : (paddingLeft + (i / (numPoints - 1)) * plotWidth);
        const getY = (val) => (height - paddingBottom) - (val / maxCeil) * plotHeight;

        const pointsApps = apps.map((val, i) => ({ x: getX(i), y: getY(val), val: val, label: labels[i] }));
        const pointsHired = hired.map((val, i) => ({ x: getX(i), y: getY(val), val: val, label: labels[i] }));

        let dLineApps = '';
        let dAreaApps = '';
        let dLineHired = '';
        let dAreaHired = '';

        if (pointsApps.length > 0) {
            dLineApps = `M ${pointsApps[0].x} ${pointsApps[0].y}` + pointsApps.slice(1).map(p => ` L ${p.x} ${p.y}`).join('');
            dAreaApps = `M ${pointsApps[0].x} ${height - paddingBottom} L ${pointsApps[0].x} ${pointsApps[0].y}` +
                pointsApps.slice(1).map(p => ` L ${p.x} ${p.y}`).join('') +
                ` L ${pointsApps[pointsApps.length - 1].x} ${height - paddingBottom} Z`;
        }

        if (pointsHired.length > 0) {
            dLineHired = `M ${pointsHired[0].x} ${pointsHired[0].y}` + pointsHired.slice(1).map(p => ` L ${p.x} ${p.y}`).join('');
            dAreaHired = `M ${pointsHired[0].x} ${height - paddingBottom} L ${pointsHired[0].x} ${pointsHired[0].y}` +
                pointsHired.slice(1).map(p => ` L ${p.x} ${p.y}`).join('') +
                ` L ${pointsHired[pointsHired.length - 1].x} ${height - paddingBottom} Z`;
        }

        // Apply path strings to SVG
        const pathLineApps = document.getElementById('raPathLineApps');
        const pathAreaApps = document.getElementById('raPathAreaApps');
        const pathLineHired = document.getElementById('raPathLineHired');
        const pathAreaHired = document.getElementById('raPathAreaHired');

        if (pathLineApps) pathLineApps.setAttribute('d', dLineApps);
        if (pathAreaApps) pathAreaApps.setAttribute('d', dAreaApps);
        if (pathLineHired) pathLineHired.setAttribute('d', dLineHired);
        if (pathAreaHired) pathAreaHired.setAttribute('d', dAreaHired);

        // Render interactive dots with tooltip hover
        const gDotsApps = document.getElementById('raDotsApps');
        const gDotsHired = document.getElementById('raDotsHired');

        if (gDotsApps) {
            gDotsApps.innerHTML = '';
            pointsApps.forEach(pt => {
                const circle = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
                circle.setAttribute('cx', pt.x);
                circle.setAttribute('cy', pt.y);
                circle.setAttribute('r', '4.5');
                circle.addEventListener('mouseenter', (e) => showSvgTooltip(e, pt.label, pt.val, 'Applications', '#2563eb'));
                circle.addEventListener('mouseleave', hideSvgTooltip);
                gDotsApps.appendChild(circle);
            });
        }

        if (gDotsHired) {
            gDotsHired.innerHTML = '';
            pointsHired.forEach(pt => {
                const circle = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
                circle.setAttribute('cx', pt.x);
                circle.setAttribute('cy', pt.y);
                circle.setAttribute('r', '4.5');
                circle.addEventListener('mouseenter', (e) => showSvgTooltip(e, pt.label, pt.val, 'Placements Hired', '#16a34a'));
                circle.addEventListener('mouseleave', hideSvgTooltip);
                gDotsHired.appendChild(circle);
            });
        }

        // Update Bottom X Labels Row
        const xRow = document.getElementById('raXLabelsRow');
        if (xRow) {
            xRow.innerHTML = '';
            labels.forEach(lbl => {
                const span = document.createElement('span');
                span.textContent = lbl;
                xRow.appendChild(span);
            });
        }
    }

    function showSvgTooltip(e, label, val, metricName, color) {
        if (!svgTooltip || !svgChartContainer) return;
        const rect = svgChartContainer.getBoundingClientRect();
        const posX = e.clientX - rect.left;
        const posY = e.clientY - rect.top;

        svgTooltip.innerHTML = `<span style="color: #94a3b8; font-size: 11px;">${label}</span><br><strong style="color: ${color};">${val.toLocaleString()}</strong> ${metricName}`;
        svgTooltip.style.left = `${posX}px`;
        svgTooltip.style.top = `${posY}px`;
        svgTooltip.classList.add('show');
    }

    function hideSvgTooltip() {
        if (!svgTooltip) return;
        svgTooltip.classList.remove('show');
    }

    function updateDonutChart(period) {
        const totalEl = document.getElementById('raDonutTotalCandidates');
        if (totalEl) {
            if (period === '30d') totalEl.textContent = '3,850';
            else if (period === 'quarter') totalEl.textContent = '4,150';
            else if (period === 'ytd') totalEl.textContent = '12,890';
            else if (period === 'all') totalEl.textContent = '28,450';
        }
    }

    // Initial render of SVG Line Chart & Donut
    renderSvgLineChart(currentPeriod);
    updateDonutChart(currentPeriod);

    // --------------------------------------------------------------------------
    // Export Report Modal Logic
    // --------------------------------------------------------------------------
    const exportModal = document.getElementById('raExportModal');
    const btnOpenExport = document.getElementById('raBtnOpenExport');
    const btnCloseExport = document.getElementById('raBtnCloseExport');
    const btnCancelExport = document.getElementById('raBtnCancelExport');
    const btnGenerateReport = document.getElementById('raBtnGenerateReport');
    const formatCards = document.querySelectorAll('.ra-format-option');
    let selectedFormat = 'PDF';

    if (btnOpenExport && exportModal) {
        btnOpenExport.addEventListener('click', () => {
            exportModal.classList.add('show');
            document.body.style.overflow = 'hidden';
        });
    }

    function closeExportModal() {
        if (!exportModal) return;
        exportModal.classList.remove('show');
        document.body.style.overflow = '';
    }

    if (btnCloseExport) btnCloseExport.addEventListener('click', closeExportModal);
    if (btnCancelExport) btnCancelExport.addEventListener('click', closeExportModal);

    if (exportModal) {
        exportModal.addEventListener('click', (e) => {
            if (e.target === exportModal) closeExportModal();
        });
    }

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && exportModal && exportModal.classList.contains('show')) {
            closeExportModal();
        }
    });

    formatCards.forEach(card => {
        card.addEventListener('click', () => {
            formatCards.forEach(c => c.classList.remove('active'));
            card.classList.add('active');
            selectedFormat = card.getAttribute('data-format') || 'PDF';
        });
    });

    if (btnGenerateReport) {
        btnGenerateReport.addEventListener('click', () => {
            const scopeSelect = document.getElementById('raReportScope');
            const scopeName = scopeSelect ? scopeSelect.options[scopeSelect.selectedIndex].text : 'Executive Summary';

            const origContent = btnGenerateReport.innerHTML;
            btnGenerateReport.disabled = true;
            btnGenerateReport.innerHTML = `<span class="material-symbols-outlined" style="font-size: 18px; animation: ra-spin 1s linear infinite;">progress_activity</span> Compiling ${selectedFormat}...`;

            setTimeout(() => {
                btnGenerateReport.disabled = false;
                btnGenerateReport.innerHTML = origContent;
                closeExportModal();
                showToast(`${scopeName} (${selectedFormat}) exported & downloaded successfully!`, 'download_done', '#22c55e');
            }, 1200);
        });
    }

    // --------------------------------------------------------------------------
    // Print Summary Simulation
    // --------------------------------------------------------------------------
    const btnPrint = document.getElementById('raBtnPrint');
    if (btnPrint) {
        btnPrint.addEventListener('click', () => {
            showToast('Preparing clean print view...', 'print', '#2563eb');
            setTimeout(() => {
                window.print();
            }, 500);
        });
    }
}
