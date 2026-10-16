<!--  Deep-Dive Table 1: Top Performing Partner Universities-->
<div class="ra-table-card" id="secTableUnis">
    <div class="ra-table-header">
        <div>
            <h3>Top Partner Universities Performance Benchmark</h3>
            <p>Comparative metrics on candidate quality, project completion, and student hiring percentage.</p>
        </div>
        <span class="ra-badge-pill">Institutes Audit</span>
    </div>

    <div class="ra-table-responsive">
        <table class="ra-table">
            <thead>
                <tr>
                    <th>University / Institute</th>
                    <th>Enrolled Students</th>
                    <th>Verification Rate</th>
                    <th>Active Interns</th>
                    <th>Projects Completed</th>
                    <th>Placement %</th>
                    <th>Rating</th>
                </tr>
            </thead>
            <tbody>
            <?php if (!empty($universityBenchmarks)): ?>
                <?php foreach ($universityBenchmarks as $idx => $uni): 
                    $uName = htmlspecialchars($uni['University'] ?? 'Partner University');
                    $uLoc  = htmlspecialchars($uni['Location'] ?? 'Sri Lanka');
                    $uStudents = (int)($uni['student_count'] ?? 0);
                    
                    $uLower = strtolower($uName);
                    $uniClass = 'uni-ucsc';
                    $initials = 'UN';
                    $rating = '5.0';
                    $ratePill = 'ra-pill-green';

                    if (strpos($uLower, 'colombo') !== false || strpos($uLower, 'ucsc') !== false) {
                        $uniClass = 'uni-ucsc';
                        $initials = 'UC';
                        $rating = '5.0';
                        $pRate = '96.4%';
                    } elseif (strpos($uLower, 'moratuwa') !== false) {
                        $uniClass = 'uni-moratuwa';
                        $initials = 'UM';
                        $rating = '4.9';
                        $pRate = '94.8%';
                    } elseif (strpos($uLower, 'kelaniya') !== false) {
                        $uniClass = 'uni-kln';
                        $initials = 'KL';
                        $rating = '4.8';
                        $pRate = '91.2%';
                    } elseif (strpos($uLower, 'sliit') !== false) {
                        $uniClass = 'uni-sliit';
                        $initials = 'SL';
                        $rating = '4.6';
                        $pRate = '87.5%';
                        $ratePill = 'ra-pill-blue';
                    } elseif (strpos($uLower, 'peradeniya') !== false) {
                        $uniClass = 'uni-moratuwa';
                        $initials = 'UP';
                        $rating = '4.9';
                        $pRate = '93.5%';
                    } elseif (strpos($uLower, 'jayewardenepura') !== false) {
                        $uniClass = 'uni-kln';
                        $initials = 'SJ';
                        $rating = '4.8';
                        $pRate = '92.0%';
                    } else {
                        $initials = strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $uName), 0, 2)) ?: 'UN';
                    }

                    $uPlaced = (int)($uni['placed_count'] ?? 0);
                    $uProjects = (int)($uni['project_count'] ?? 0);
                    $uRate = $uni['placement_rate'] ?? 0;
                    $faculty = htmlspecialchars($uni['faculty'] ?? 'Department of Computing');

                    if ($uRate >= 80) $rating = '5.0';
                    elseif ($uRate >= 50) $rating = '4.8';
                    elseif ($uRate > 0) $rating = '4.5';
                    else $rating = $uStudents > 0 ? '4.0' : '-';
                ?>
                <tr>
                    <td>
                        <div class="ra-entity-cell">
                            <div class="ra-entity-icon <?= $uniClass ?>"><?= $initials ?></div>
                            <div class="ra-entity-info">
                                <span class="ra-entity-name"><?= $uName ?></span>
                                <span class="ra-entity-sub"><?= $uLoc ?> &bull; <?= $faculty ?></span>
                            </div>
                        </div>
                    </td>
                    <td><strong><?= number_format($uStudents) ?></strong></td>
                    <td>
                        <?php if ($uStudents > 0): ?>
                            <span class="ra-pill ra-pill-green">100% Active</span>
                        <?php else: ?>
                            <span class="ra-pill ra-pill-blue">Pending Intake</span>
                        <?php endif; ?>
                    </td>
                    <td><?= $uPlaced ?> Students</td>
                    <td><?= $uProjects ?> Projects</td>
                    <td><strong style="color: <?= ($uRate > 0) ? '#16a34a' : '#64748b' ?>;"><?= $uRate ?>%</strong></td>
                    <td>
                        <span class="ra-rating-badge">
                            <span class="material-symbols-outlined">star</span> <?= $rating ?>
                        </span>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" style="text-align: center; padding: 35px 20px; color: #64748b;">
                        <span class="material-symbols-outlined" style="font-size: 36px; display: block; margin-bottom: 8px; color: #94a3b8;">domain_disabled</span>
                        No partner universities currently registered in database.
                    </td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
