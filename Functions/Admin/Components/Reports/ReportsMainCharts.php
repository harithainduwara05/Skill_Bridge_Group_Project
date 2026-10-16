<!--  Visual Analytics Charts Grid - Row 1-->
<div class="ra-charts-grid-row" id="secChartsMain">
    
    <!-- Chart 1: Hiring Velocity & Applications (Native SVG Area & Line Chart) -->
    <div class="ra-chart-card">
        <div class="ra-chart-header">
            <div>
                <h3>Hiring Velocity & Placement Trends</h3>
                <p>Monthly application volume compared to confirmed industry placements.</p>
            </div>
            <div class="ra-chart-tools">
                <span class="ra-badge-pill badge-live">Live Analytics</span>
            </div>
        </div>

        <div class="ra-svg-chart-container">
            <!-- SVG Area / Line Chart -->
            <svg id="raHiringSvg" class="ra-svg-chart" viewBox="0 0 660 230" preserveAspectRatio="none">
                <defs>
                    <linearGradient id="gradApps" x1="0" y1="0" x2="0" y2="1">
                        <stop offset="0%" stop-color="#2563eb" stop-opacity="0.25" />
                        <stop offset="100%" stop-color="#2563eb" stop-opacity="0.0" />
                    </linearGradient>
                    <linearGradient id="gradHired" x1="0" y1="0" x2="0" y2="1">
                        <stop offset="0%" stop-color="#16a34a" stop-opacity="0.25" />
                        <stop offset="100%" stop-color="#16a34a" stop-opacity="0.0" />
                    </linearGradient>
                </defs>

                <!-- Horizontal Grid Lines -->
                <g class="ra-grid-lines">
                    <line x1="50" y1="25" x2="640" y2="25" stroke="#f1f5f9" stroke-width="1" stroke-dasharray="4" />
                    <line x1="50" y1="70" x2="640" y2="70" stroke="#f1f5f9" stroke-width="1" stroke-dasharray="4" />
                    <line x1="50" y1="115" x2="640" y2="115" stroke="#f1f5f9" stroke-width="1" stroke-dasharray="4" />
                    <line x1="50" y1="160" x2="640" y2="160" stroke="#f1f5f9" stroke-width="1" stroke-dasharray="4" />
                    <line x1="50" y1="195" x2="640" y2="195" stroke="#e2e8f0" stroke-width="1.5" />
                </g>

                <!-- Y Axis Numeric Labels -->
                <g class="ra-axis-labels-y" font-size="11" fill="#94a3b8" text-anchor="end" font-family="'Inter', sans-serif">
                    <text x="42" y="29" id="raYLabel4">800</text>
                    <text x="42" y="74" id="raYLabel3">600</text>
                    <text x="42" y="119" id="raYLabel2">400</text>
                    <text x="42" y="164" id="raYLabel1">200</text>
                    <text x="42" y="199">0</text>
                </g>

                <!-- Shaded Area Fills -->
                <path id="raPathAreaApps" fill="url(#gradApps)" d="" />
                <path id="raPathAreaHired" fill="url(#gradHired)" d="" />

                <!-- Line Strokes -->
                <path id="raPathLineApps" fill="none" stroke="#2563eb" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" d="" />
                <path id="raPathLineHired" fill="none" stroke="#16a34a" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" d="" />

                <!-- Interactive Data Points (Dots) -->
                <g id="raDotsApps" class="ra-chart-dots apps"></g>
                <g id="raDotsHired" class="ra-chart-dots hired"></g>
            </svg>

            <!-- Bottom X Axis Month/Period Labels -->
            <div id="raXLabelsRow" class="ra-x-labels-row">
                <span>Week 1</span>
                <span>Week 2</span>
                <span>Week 3</span>
                <span>Week 4</span>
            </div>

            <!-- Interactive Custom Tooltip Bubble -->
            <div id="raSvgTooltip" class="ra-svg-tooltip"></div>
        </div>

        <!-- Chart Custom Legend -->
        <div class="ra-chart-legend-row">
            <div class="ra-legend-item">
                <span class="ra-legend-dot dot-blue"></span>
                <span>Applications Submitted</span>
            </div>
            <div class="ra-legend-item">
                <span class="ra-legend-dot dot-green"></span>
                <span>Students Placed & Hired</span>
            </div>
        </div>
    </div>

    <!-- Chart 2: Student Share by University (Native SVG Donut) -->
    <div class="ra-chart-card">
        <div class="ra-chart-header">
            <div>
                <h3>Student Enrolment by Institute</h3>
                <p>Distribution of active student candidates across partner universities.</p>
            </div>
        </div>

        <div class="ra-donut-wrap">
            <div class="ra-donut-svg-box">
                <svg class="ra-donut-svg" viewBox="0 0 200 200">
                    <!-- Background base ring -->
                    <circle cx="100" cy="100" r="70" fill="none" stroke="#f8fafc" stroke-width="26" />
                    
                    <!-- Dynamic SVG Donut Segments from Real Database -->
                    <?php if (!empty($donutDistribution['segments'])): ?>
                        <?php foreach ($donutDistribution['segments'] as $seg): ?>
                            <?php if ($seg['dash'] > 0): ?>
                            <circle class="ra-donut-seg" cx="100" cy="100" r="70" fill="none" 
                                    stroke="<?= $seg['color'] ?>" stroke-width="24" 
                                    stroke-dasharray="<?= $seg['dash'] ?> 440" 
                                    stroke-dashoffset="<?= $seg['offset'] ?>" />
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </svg>

                <!-- Donut Center Label -->
                <div class="ra-donut-center-info">
                    <strong id="raDonutTotalCandidates"><?= number_format($donutDistribution['total_students'] ?? 0) ?></strong>
                    <span>Active Students</span>
                </div>
            </div>

            <!-- Donut Legend Breakdown -->
            <div class="ra-donut-legend-list">
                <?php if (!empty($donutDistribution['segments'])): ?>
                    <?php foreach ($donutDistribution['segments'] as $seg): ?>
                    <div class="ra-donut-legend-item">
                        <span class="ra-donut-dot" style="background: <?= $seg['color'] ?>;"></span>
                        <span class="ra-donut-uni-name"><?= htmlspecialchars($seg['name']) ?></span>
                        <strong class="ra-donut-val"><?= $seg['pct'] ?>% <small style="color:#64748b; font-weight:normal;">(<?= number_format($seg['count']) ?>)</small></strong>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="ra-donut-legend-item">
                        <span class="ra-donut-uni-name" style="color: #64748b;">No partner institutions data</span>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

</div>
