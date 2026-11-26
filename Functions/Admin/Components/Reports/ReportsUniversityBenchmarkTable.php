<!--  Deep-Dive Table 1: Top Performing Partner Universities-->
<div class="ra-table-card" id="secTableUnis">
    <div class="ra-table-header">
        <div>
            <h3>Partner Universities</h3>
            <p>Student enrolment, projects hosted, and placement performance by university.</p>
        </div>
    </div>

    <div class="ra-table-responsive">
        <table class="ra-table">
            <thead>
                <tr>
                    <th>University / Institute</th>
                    <th>Enrolled Students</th>
                    <th>Status</th>
                    <th>Placed Students</th>
                    <th>Projects Hosted</th>
                    <th>Placement %</th>
                    <th>Rating</th>
                </tr>
            </thead>
            <tbody id="raUniTableBody">
            <?php if (!empty($universityBenchmarks)): ?>
                <?php foreach ($universityBenchmarks as $idx => $uni): 
                    $uName = htmlspecialchars($uni['University'] ?? 'Partner University');
                    $uLoc  = htmlspecialchars($uni['Location'] ?? 'Sri Lanka');
                    $uStudents = (int)($uni['student_count'] ?? 0);
                    
                    $uLower = strtolower($uName);
                    $uniClass = 'uni-ucsc';
                    $initials = 'UN';
                    $rating = '5.0';

                    if (strpos($uLower, 'colombo') !== false || strpos($uLower, 'ucsc') !== false) {
                        $uniClass = 'uni-ucsc';
                        $initials = 'UC';
                    } elseif (strpos($uLower, 'moratuwa') !== false) {
                        $uniClass = 'uni-moratuwa';
                        $initials = 'UM';
                    } elseif (strpos($uLower, 'kelaniya') !== false) {
                        $uniClass = 'uni-kln';
                        $initials = 'KL';
                    } elseif (strpos($uLower, 'sliit') !== false) {
                        $uniClass = 'uni-sliit';
                        $initials = 'SL';
                    } elseif (strpos($uLower, 'peradeniya') !== false) {
                        $uniClass = 'uni-moratuwa';
                        $initials = 'UP';
                    } elseif (strpos($uLower, 'jayewardenepura') !== false) {
                        $uniClass = 'uni-kln';
                        $initials = 'SJ';
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
                <tr class="ra-uni-row" style="<?= $idx >= 3 ? 'display: none;' : '' ?>">
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

    <?php
        $entityLabel = 'universities';
        $paginationInfoId = 'raUniPaginationInfo';
        $paginationControlsId = 'raUniPaginationControls';
        $alwaysShow = true;
        $perPage = 3;
        $totalRecords = count($universityBenchmarks ?? []);
        include dirname(__DIR__) . "/Common/Pagination.php";
    ?>
</div>
