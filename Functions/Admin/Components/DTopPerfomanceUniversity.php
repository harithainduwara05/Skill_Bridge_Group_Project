 <div class="full-width-section">
    <div class="card">
        <div class="card-header">
            <h3>Top Performing Universities</h3>
            <a href="university.php" class="btn-outline">View All Universities</a>
        </div>
        <div class="card-body" style="padding:0; overflow-x: auto;">
            <table class="data-table">
                <?php 
                    $TableColumns = ["University Name", "Students", "Active Projects", "Completion Rate", "Status"];
                    include __DIR__ . "/TableHeder.php";
                ?>
                <tbody>
                    <?php
                    $limit = (count($popularUni) < 3) ? count($popularUni) : 3;
                    for ($x = 0; $x < $limit; $x++) {
                        $acurate = ($totalAcPro > 0) ? ($popularUni[$x]["ACTIVE PROJECTS"] / $totalAcPro) * 100 : 0;
                        ?>
                        <tr>
                            <td>
                                <div class="university-info">
                                    <div class="university-logo mit">
                                        <?php echo $popularUni[$x]['UNIVERSITY NAME'][0]; ?>
                                    </div>
                                    <span
                                        class="university-name"><?php echo $popularUni[$x]['UNIVERSITY NAME']; ?></span>
                                </div>
                            </td>
                            <td><?php echo $popularUni[$x]['STUDENTS']; ?></td>
                            <td><?php echo $popularUni[$x]['ACTIVE PROJECTS']; ?></td>
                            <td>
                                <div class="progress-bar-container">
                                    <div class="progress-bar">
                                        <div class="progress-bar-fill high" style="width:<?php echo $acurate; ?>%;">
                                        </div>
                                    </div>
                                    <span class="progress-text"><?php echo $acurate; ?>%</span>
                                </div>
                            </td>
                            <td><span class="badge-status active"><?php echo $popularUni[$x]['status']; ?></span></td>
                            <!--<td><button class="action-btn">⋮</button></td>-->
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>