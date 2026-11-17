<!--  Visual Analytics Charts Grid - Row 2-->
<div class="ra-charts-grid-equal" id="secChartsSkills">
    
    <!-- Skill Demand Progress Bars -->
    <div class="ra-chart-card">
        <div class="ra-chart-header">
            <div>
                <h3>Top In-Demand Technical Skills</h3>
                <p>Most requested competencies across open industry internship drives.</p>
            </div>
            <span class="ra-badge-pill">2026 Tech Index</span>
        </div>

        <div class="ra-skills-list">
            <?php if (!empty($topSkills)): ?>
                <?php foreach ($topSkills as $skill): ?>
                <div class="ra-skill-item">
                    <div class="ra-skill-meta">
                        <span><?= htmlspecialchars($skill['name']) ?></span>
                        <span><?= $skill['pct'] ?>% Demand (<?= $skill['freq'] ?> drives)</span>
                    </div>
                    <div class="ra-skill-bar-bg">
                        <div class="ra-skill-bar-fill <?= $skill['color'] ?>" style="width: <?= max(5, $skill['pct']) ?>%;"></div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="color: #64748b; font-size: 13px; text-align: center; padding: 20px 0;">No technical skills indexed yet.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Internship Funnel Progress Steps -->
    <div class="ra-chart-card">
        <div class="ra-chart-header">
            <div>
                <h3>Application Conversion Funnel</h3>
                <p>End-to-end recruitment funnel conversion rate from submission to hired.</p>
            </div>
            <span class="ra-badge-pill">Funnel Health</span>
        </div>

        <div class="ra-funnel-grid">
            <?php if (!empty($recruitmentFunnel)): ?>
                <?php foreach ($recruitmentFunnel as $f): ?>
                <div class="ra-funnel-step">
                    <div class="ra-funnel-step-num"><?= $f['step'] ?></div>
                    <div class="ra-funnel-val"><?= number_format($f['val']) ?></div>
                    <div class="ra-funnel-label"><?= $f['label'] ?></div>
                    <span class="ra-funnel-pct" <?= ($f['step'] === 'Step 4') ? 'style="background: #dcfce7; color: #15803d;"' : '' ?>><?= $f['pct'] ?></span>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div style="margin-top: 20px; padding: 14px 16px; background: #f8fafc; border: 1px dashed var(--ra-border); border-radius: var(--ra-radius-md); display: flex; align-items: center; justify-content: space-between;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <span class="material-symbols-outlined" style="color: #2563eb; font-size: 22px;">speed</span>
                <div>
                    <strong style="font-size: 13px; color: var(--ra-text-main); display: block;">Average Time-to-Offer</strong>
                    <span style="font-size: 12px; color: var(--ra-text-muted);">From first candidate application to contract offer issuance</span>
                </div>
            </div>
            <span style="font-size: 15px; font-weight: 800; color: #0b2246;">11.4 Days</span>
        </div>
    </div>

</div>
