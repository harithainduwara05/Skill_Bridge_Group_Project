<?php

include "../../../Config/db.php";
include "../../../Session/Session.php";

require_role('organization');
$user = current_user();
$organization_email = $user['email'];

// ---------------------------------------------------------------
// UI-ONLY DEMO DATA (no database yet). Replace with real queries later.
// state: ontrack | behind | completed      tone: ok | warn | late
// ---------------------------------------------------------------
$summary = [
    ['icon' => 'groups',        'class' => 'blue',  'label' => 'Active Teams',     'value' => 18],
    ['icon' => 'check_circle',  'class' => 'green', 'label' => 'Completed Teams',  'value' => 42],
    ['icon' => 'school',        'class' => 'sky',   'label' => 'Students Involved', 'value' => 156],
    ['icon' => 'pending_actions', 'class' => 'navy', 'label' => 'In Progress',     'value' => 12],
];

$teams = [
    [
        'name' => 'Nexus Systems', 'state' => 'ontrack', 'status' => 'On Track',
        'project' => 'AI-Driven Supply Chain Optimizer',
        'leader' => 'Sarah Chen', 'role' => 'Full Stack Lead',
        'members' => ['Ravi P', 'Nimal S', 'Amaya D'], 'more' => 2,
        'skills' => ['React.js', 'Python', 'TensorFlow', 'Figma'],
        'phase' => 'Phase 2: Prototyping', 'percent' => 72,
        'deadline' => 'Oct 24', 'time' => '14 days left', 'tone' => 'warn',
        'actions' => ['View', 'Task', 'Chat'],
    ],
    [
        'name' => 'Vortex Group', 'state' => 'behind', 'status' => 'Behind Schedule',
        'project' => 'Blockchain-Based Academic Credentials',
        'leader' => 'Marcus Thorne', 'role' => 'Security Architect',
        'members' => ['Kasun M', 'Tharushi W'], 'more' => 1,
        'skills' => ['Solidity', 'Node.js', 'Cryptography'],
        'phase' => 'Phase 1: Smart Contract Design', 'percent' => 45,
        'deadline' => 'Oct 15', 'time' => '5 days delayed', 'tone' => 'late',
        'actions' => ['View', 'Review', 'Chat'],
    ],
    [
        'name' => 'Quantum Analytics', 'state' => 'completed', 'status' => 'Completed',
        'project' => 'Predictive Maintenance for Smart Cities',
        'leader' => 'Elena Vance', 'role' => 'Data Analyst',
        'members' => ['Ishan R', 'Dilini H'], 'more' => 3,
        'skills' => [],
        'phase' => 'All Milestones Met', 'percent' => 100,
        'deadline' => '', 'time' => 'Finished on Sep 18', 'tone' => 'ok',
        'actions' => ['Final Report'],
    ],
    [
        'name' => 'Alpha Ops', 'state' => 'ontrack', 'status' => 'On Track',
        'project' => 'Cloud Infrastructure Automation',
        'leader' => 'Kevin Zhang', 'role' => 'DevOps Engineer',
        'members' => ['Sahan L', 'Malsha K'], 'more' => 1,
        'skills' => ['AWS', 'Docker', 'Kubernetes'],
        'phase' => 'Phase 3: Strategy Deployment', 'percent' => 85,
        'deadline' => 'Nov 02', 'time' => '22 days left', 'tone' => 'ok',
        'actions' => ['View', 'Task', 'Chat'],
    ],
];

function tmInitials($name) {
    $parts = preg_split('/\s+/', trim($name));
    $out = '';
    foreach (array_slice($parts, 0, 2) as $p) { $out .= mb_strtoupper(mb_substr($p, 0, 1)); }
    return $out;
}
$tmColors = ['#1e3a5f', '#0f766e', '#7c3aed', '#b45309', '#be185d', '#2563eb', '#475569'];
$tmActionIcon = ['View' => 'visibility', 'Task' => 'task_alt', 'Chat' => 'chat_bubble', 'Review' => 'rate_review', 'Final Report' => 'description'];

include "../../../Includes/org_sidebar.php";
include "../../../Includes/dash_header.php";
?>

<style>
    .tm-wrap { padding: 14px 28px 28px; }

    /* ---- summary cards ---- */
    .tm-stats { display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px; margin-bottom: 26px; }
    .tm-stat { display: flex; align-items: center; gap: 14px; background: #fff; border: 1px solid #e5e7eb; border-radius: 14px; padding: 16px 18px; }
    .tm-stat-icon { width: 46px; height: 46px; border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .tm-stat-icon .material-symbols-outlined { font-size: 24px; }
    .tm-stat-icon.blue  { background: #dbeafe; color: #2563eb; }
    .tm-stat-icon.green { background: #dcfce7; color: #16a34a; }
    .tm-stat-icon.sky   { background: #e0f2fe; color: #0369a1; }
    .tm-stat-icon.navy  { background: #1e3a5f; color: #fff; }
    .tm-stat-label { font-size: 13px; color: #4b5563; }
    .tm-stat-value { font-size: 22px; font-weight: 800; color: #111827; line-height: 1.2; }

    /* ---- heading row ---- */
    .tm-head { display: flex; align-items: flex-end; justify-content: space-between; gap: 16px; flex-wrap: wrap; margin-bottom: 18px; }
    .tm-head h2 { margin: 0; font-size: 24px; font-weight: 800; color: #0f3a66; }
    .tm-head p { margin: 4px 0 0; font-size: 14px; color: #4b5563; }
    .tm-head-actions { display: flex; gap: 10px; }
    .tm-btn { display: inline-flex; align-items: center; justify-content: center; gap: 6px; padding: 9px 16px; border-radius: 8px; font-family: inherit;
              font-size: 13px; font-weight: 600; cursor: pointer; text-decoration: none; border: 1px solid #d1d5db; background: #fff; color: #1f2937; transition: all .15s ease; }
    .tm-btn .material-symbols-outlined { font-size: 17px; }
    .tm-btn:hover { background: #f3f4f6; }
    .tm-btn.solid { background: #0f2a4a; border-color: #0f2a4a; color: #fff; }
    .tm-btn.solid:hover { background: #0a1f38; }

    /* ---- team cards ---- */
    .tm-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 18px; }
    .tm-card { background: #fff; border: 1px solid #e5e7eb; border-radius: 16px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,.04); }
    .tm-card.completed { background: #fafafa; border: 1.5px dashed #cbd5e1; box-shadow: none; }
    .tm-card.completed .tm-name, .tm-card.completed .tm-project { color: #6b7280; }

    .tm-top { display: flex; align-items: center; justify-content: space-between; margin-bottom: 4px; }
    .tm-title { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
    .tm-name { font-size: 17px; font-weight: 700; color: #111827; }
    .tm-pill { font-size: 11px; font-weight: 700; padding: 3px 10px; border-radius: 999px; }
    .tm-pill.ontrack   { background: #dcfce7; color: #166534; }
    .tm-pill.behind    { background: #fee2e2; color: #b91c1c; }
    .tm-pill.completed { background: #e5e7eb; color: #4b5563; }
    .tm-kebab { background: none; border: none; cursor: pointer; color: #6b7280; padding: 2px; border-radius: 6px; display: flex; }
    .tm-kebab:hover { background: #f3f4f6; }
    .tm-project { font-size: 13.5px; color: #374151; margin: 0 0 16px; }

    .tm-people { display: flex; justify-content: space-between; align-items: flex-start; gap: 16px; margin-bottom: 16px; }
    .tm-label { font-size: 10.5px; font-weight: 700; letter-spacing: .06em; color: #6b7280; text-transform: uppercase; margin-bottom: 8px; }
    .tm-leader { display: flex; align-items: center; gap: 10px; }
    .tm-leader-name { font-size: 14px; font-weight: 700; color: #111827; line-height: 1.2; }
    .tm-leader-role { font-size: 12.5px; color: #4b5563; }
    .tm-av { width: 40px; height: 40px; border-radius: 50%; color: #fff; font-size: 13px; font-weight: 700; display: flex; align-items: center; justify-content: center;
             border: 2px solid #fff; flex-shrink: 0; }
    .tm-stack { display: flex; }
    .tm-stack .tm-av { width: 32px; height: 32px; font-size: 11px; margin-left: -8px; }
    .tm-stack .tm-av:first-child { margin-left: 0; }
    .tm-stack .tm-av.more { background: #e5e7eb; color: #4b5563; }
    .tm-card.completed .tm-av { filter: grayscale(1); opacity: .7; }

    .tm-skills { margin-bottom: 16px; }
    .tm-chips { display: flex; flex-wrap: wrap; gap: 8px; }
    .tm-chip { background: #dbeafe; color: #1e3a5f; font-size: 12px; font-weight: 500; padding: 4px 12px; border-radius: 6px; }

    .tm-progress { background: #f3f4f6; border: 1px solid #e5e7eb; border-radius: 12px; padding: 14px 16px; margin-bottom: 16px; }
    .tm-progress.behind { background: #fef2f2; border-color: #ef4444; border-left-width: 4px; }
    .tm-progress.completed { background: #f3f4f6; }
    .tm-prow { display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; font-size: 14px; font-weight: 700; color: #111827; }
    .tm-progress.behind .tm-prow { color: #b91c1c; }
    .tm-bar { height: 7px; background: #d1d5db; border-radius: 999px; overflow: hidden; margin-bottom: 10px; }
    .tm-bar span { display: block; height: 100%; border-radius: 999px; background: #0f3a66; }
    .tm-progress.behind .tm-bar span { background: #b91c1c; }
    .tm-progress.completed .tm-bar span { background: #166534; }
    .tm-meta { display: flex; justify-content: space-between; align-items: center; font-size: 12.5px; color: #4b5563; }
    .tm-meta .left, .tm-meta .right { display: inline-flex; align-items: center; gap: 5px; }
    .tm-meta .material-symbols-outlined { font-size: 16px; }
    .tm-meta .right { font-weight: 600; }
    .tm-meta .right.ok   { color: #166534; }
    .tm-meta .right.warn { color: #dc2626; }
    .tm-meta .right.late { color: #b91c1c; }

    .tm-actions { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; }
    .tm-actions.single { grid-template-columns: 1fr; }
    .tm-actions .tm-btn { padding: 9px 10px; }
    .tm-actions .tm-btn.report { background: #d1d5db; border-color: #d1d5db; color: #374151; }
    .tm-actions .tm-btn.report:hover { background: #c4c9d1; }

    @media (max-width: 1100px) { .tm-stats { grid-template-columns: repeat(2, 1fr); } }
    @media (max-width: 900px)  { .tm-grid { grid-template-columns: 1fr; } }
    @media (max-width: 600px)  { .tm-wrap { padding: 12px 16px 24px; } .tm-stats { grid-template-columns: 1fr; } .tm-people { flex-direction: column; } }
</style>

<main class="content">
    <div class="tm-wrap">

        <!-- Summary cards -->
        <div class="tm-stats">
            <?php foreach ($summary as $s): ?>
                <div class="tm-stat">
                    <div class="tm-stat-icon <?= $s['class'] ?>"><span class="material-symbols-outlined"><?= $s['icon'] ?></span></div>
                    <div>
                        <div class="tm-stat-label"><?= htmlspecialchars($s['label']) ?></div>
                        <div class="tm-stat-value"><?= (int)$s['value'] ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Heading + actions -->
        <div class="tm-head">
            <div>
                <h2>Active Teams Overview</h2>
                <p>Manage and monitor student collaborative projects.</p>
            </div>
            <div class="tm-head-actions">
                <button type="button" class="tm-btn"><span class="material-symbols-outlined">filter_list</span>Filter</button>
                <button type="button" class="tm-btn solid"><span class="material-symbols-outlined">add</span>Create New Team</button>
            </div>
        </div>

        <!-- Team cards -->
        <div class="tm-grid">
            <?php foreach ($teams as $t): ?>
                <div class="tm-card <?= $t['state'] === 'completed' ? 'completed' : '' ?>">

                    <div class="tm-top">
                        <div class="tm-title">
                            <span class="tm-name"><?= htmlspecialchars($t['name']) ?></span>
                            <span class="tm-pill <?= $t['state'] ?>"><?= htmlspecialchars($t['status']) ?></span>
                        </div>
                        <button type="button" class="tm-kebab" aria-label="More options"><span class="material-symbols-outlined">more_vert</span></button>
                    </div>
                    <p class="tm-project">Project: <?= htmlspecialchars($t['project']) ?></p>

                    <div class="tm-people">
                        <div>
                            <div class="tm-label">Team Leader</div>
                            <div class="tm-leader">
                                <div class="tm-av" style="background:<?= $tmColors[abs(crc32($t['leader'])) % count($tmColors)] ?>;"><?= tmInitials($t['leader']) ?></div>
                                <div>
                                    <div class="tm-leader-name"><?= htmlspecialchars($t['leader']) ?></div>
                                    <div class="tm-leader-role"><?= htmlspecialchars($t['role']) ?></div>
                                </div>
                            </div>
                        </div>
                        <div>
                            <div class="tm-label">Team Members</div>
                            <div class="tm-stack">
                                <?php foreach ($t['members'] as $m): ?>
                                    <div class="tm-av" title="<?= htmlspecialchars($m) ?>" style="background:<?= $tmColors[abs(crc32($m)) % count($tmColors)] ?>;"><?= tmInitials($m) ?></div>
                                <?php endforeach; ?>
                                <?php if ($t['more'] > 0): ?><div class="tm-av more">+<?= (int)$t['more'] ?></div><?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <?php if (!empty($t['skills'])): ?>
                        <div class="tm-skills">
                            <div class="tm-label">Skills Covered</div>
                            <div class="tm-chips">
                                <?php foreach ($t['skills'] as $sk): ?><span class="tm-chip"><?= htmlspecialchars($sk) ?></span><?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="tm-progress <?= in_array($t['state'], ['behind', 'completed']) ? $t['state'] : '' ?>">
                        <div class="tm-prow"><span><?= htmlspecialchars($t['phase']) ?></span><span><?= (int)$t['percent'] ?>%</span></div>
                        <div class="tm-bar"><span style="width:<?= (int)$t['percent'] ?>%;"></span></div>
                        <div class="tm-meta">
                            <?php if ($t['deadline'] !== ''): ?>
                                <span class="left"><span class="material-symbols-outlined">calendar_today</span>Deadline: <?= htmlspecialchars($t['deadline']) ?></span>
                            <?php else: ?>
                                <span></span>
                            <?php endif; ?>
                            <span class="right <?= $t['tone'] ?>">
                                <span class="material-symbols-outlined"><?= $t['state'] === 'completed' ? 'check_circle' : ($t['tone'] === 'late' ? 'warning' : 'schedule') ?></span>
                                <?= htmlspecialchars($t['time']) ?>
                            </span>
                        </div>
                    </div>

                    <div class="tm-actions <?= count($t['actions']) === 1 ? 'single' : '' ?>">
                        <?php foreach ($t['actions'] as $i => $a): ?>
                            <button type="button" class="tm-btn <?= $a === 'Final Report' ? 'report' : ($i === 0 ? 'solid' : '') ?>">
                                <span class="material-symbols-outlined"><?= $tmActionIcon[$a] ?></span><?= $a === 'Final Report' ? 'View Final Report' : $a ?>
                            </button>
                        <?php endforeach; ?>
                    </div>

                </div>
            <?php endforeach; ?>
        </div>

    </div>
</main>

<footer class="footer">
    <div>&copy; 2026 SkillBridge. All rights reserved.</div>
    <div class="footer-links">
        <a href="#">Help Center</a>
        <a href="#">Privacy Policy</a>
        <a href="#">Terms of Service</a>
    </div>
</footer>

<?php include "../../../Includes/dash_footer.php"; ?>