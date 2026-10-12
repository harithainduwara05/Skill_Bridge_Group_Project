<?php 
    $x = 0;
    $totalCards = !empty($CardDetails["cardIcons"]) ? count($CardDetails["cardIcons"]) : 0;
    while ($x < $totalCards) { 
?>
<div class="stat-card">
    <div class="stat-icon-wrap <?= htmlspecialchars($CardDetails["iconColors"][$x] ?? '') ?>">
        <span class="material-symbols-outlined"><?= htmlspecialchars($CardDetails["cardIcons"][$x] ?? '') ?></span>
    </div>
    <div class="stat-info">
        <div class="stat-label"><?= htmlspecialchars($CardDetails["cardLabels"][$x] ?? '') ?></div>
        <div class="stat-value"<?= !empty($CardDetails["valueIds"][$x]) ? ' id="' . htmlspecialchars($CardDetails["valueIds"][$x]) . '"' : '' ?>><?= htmlspecialchars($CardDetails["cardValues"][$x] ?? 0) ?></div>
        <?= !empty($CardDetails["subtexts"][$x]) 
            ? '<div class="stat-subtext"' . (!empty($CardDetails["subtextIds"][$x]) ? ' id="' . htmlspecialchars($CardDetails["subtextIds"][$x]) . '"' : '') . '>' 
              . (!empty($CardDetails["subtextIcons"][$x]) ? '<span class="material-symbols-outlined">' . htmlspecialchars($CardDetails["subtextIcons"][$x]) . '</span> ' : '') 
              . htmlspecialchars($CardDetails["subtexts"][$x]) . '</div>' 
            : '' ?>
    </div>
</div>
<?php
        $x++;
    } 
?>