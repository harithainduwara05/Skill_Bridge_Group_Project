<?php

include "../../Config/db.php";
include "../../Session/Session.php";

require_role('organization');

// Backend (PHP + SQL) of this page
require_once __DIR__ . "/../../Backend/Organization/proposal.php";

// ?v=<file time> makes the browser load the NEW css every time the file changes
$extra_css = '<link rel="stylesheet" href="../../Assets/CSS/Organization/proposal.css?v='
           . filemtime(__DIR__ . '/../../Assets/CSS/Organization/proposal.css') . '">';
include "../../Includes/org_sidebar.php";
include "../../Includes/dash_header.php";
?>



<main class="content">

    <div class="pr-wrap" id="proposalListView">

        <!-- =====================================================
             HEADER
        ====================================================== -->

        <div class="pr-head">

            <h1>Proposals Management</h1>

            <p>
                <span id="proposalTotal"><?= (int)$totalProposals ?></span>
                Total Proposals
            </p>

        </div>


        <!-- =====================================================
             FILTERS
        ====================================================== -->

        <div class="pr-filters">

            <!-- Search -->
            <div class="pr-search">

                <span class="material-symbols-outlined">
                    search
                </span>

                <input
                    type="text"
                    id="proposalSearch"
                    placeholder="Search proposals..."
                    autocomplete="off"
                >

            </div>


            <!-- Status filter -->
            <select
                class="pr-select"
                id="statusFilter"
            >

                <option value="all">
                    All Status
                </option>

                <?php foreach ($statusLabels as $key => $sl): ?>

                    <option value="<?= htmlspecialchars($key) ?>">
                        <?= htmlspecialchars($sl) ?>
                    </option>

                <?php endforeach; ?>

            </select>


            <!-- Time filter -->
            <select
                class="pr-select"
                id="timeFilter"
            >

                <option value="all">Time: All</option>
                <option value="daily">Daily (Today)</option>
                <option value="weekly">Weekly (Last 7 days)</option>
                <option value="monthly">Monthly (Last 30 days)</option>

            </select>


            <!-- Sort -->
            <button
                type="button"
                class="pr-sort"
                id="sortButton"
            >

                <span class="material-symbols-outlined">
                    sort
                </span>

                <span id="sortText">
                    Sort by Newest
                </span>

            </button>

        </div>


        <!-- =====================================================
             PROPOSAL LIST
        ====================================================== -->

        <div class="pr-list" id="proposalList">

            <?php foreach ($proposals as $p): ?>

                <?php

                $st = $p['status'];

                $locked = in_array(
                    $st,
                    ['accepted', 'rejected'],
                    true
                );

                ?>

                <div
                    class="pr-card"
                    data-id="<?= (int)$p['id'] ?>"
                    data-name="<?= htmlspecialchars(strtolower($p['name'])) ?>"
                    data-school="<?= htmlspecialchars(strtolower($p['school'])) ?>"
                    data-project="<?= htmlspecialchars($p['project']) ?>"
                    data-status="<?= htmlspecialchars($st) ?>"
                    data-skills="<?= htmlspecialchars(strtolower(implode(' ', $p['skills']))) ?>"
                    data-days="<?= (int)$p['days_ago'] ?>"
                >

                    <!-- =================================================
                         TOP
                    ================================================== -->

                    <div class="pr-top">

                        <div class="pr-person">

                            <div class="pr-avatar">

                                <?= htmlspecialchars(prInitials($p['name'])) ?>

                            </div>

                            <div>

                                <div class="pr-name-row">

                                    <span class="pr-name">
                                        <?= htmlspecialchars($p['name']) ?>
                                    </span>

                                </div>

                                <div class="pr-school">

                                    <?= htmlspecialchars($p['school']) ?>

                                </div>

                            </div>

                        </div>


                        <!-- Status: hidden while still pending — a proposal's
                             default/unacted state isn't shown as a badge on
                             the card, only "Accepted" / "Rejected" are. The
                             "Pending" label still exists purely for the
                             Status filter dropdown above. -->

                        <span
                            class="pr-badge <?= htmlspecialchars($st) ?>"
                            data-status-badge
                            <?= $st === 'pending' ? 'style="display:none;"' : '' ?>
                        >

                            <?= htmlspecialchars($statusLabels[$st]) ?>

                        </span>

                    </div>


                    <!-- =================================================
                         BODY
                    ================================================== -->

                    <div class="pr-body">

                        <div>

                            <!-- Project -->

                            <div class="pr-section">

                                <div class="pr-label">
                                    Applied For
                                </div>

                                <div class="pr-project">

                                    <?= htmlspecialchars($p['project']) ?>

                                </div>

                            </div>


                            <!-- Skills -->

                            <div class="pr-section">

                                <div class="pr-label">
                                    Skills
                                </div>

                                <div class="pr-chips">

                                    <?php foreach ($p['skills'] as $sk): ?>

                                        <span class="pr-chip">

                                            <?= htmlspecialchars($sk) ?>

                                        </span>

                                    <?php endforeach; ?>

                                </div>

                            </div>


                            <!-- Summary -->

                            <div class="pr-section">

                                <div class="pr-label">
                                    Proposal Summary
                                </div>

                                <p class="pr-summary">

                                    <?= htmlspecialchars($p['summary']) ?>

                                </p>

                            </div>

                        </div>


                        <!-- Portfolio -->

                        <div>

                            <div class="pr-label">
                                Portfolio Preview
                            </div>

                            <?php
                                $items   = $p['portfolio_items'];
                                $hasFile = $p['portfolio_type'] !== '';
                            ?>
                            <?php if (!$hasFile && !$items): ?>
                                <div class="pr-portfolio-box" title="No portfolio">
                                    <span class="material-symbols-outlined">hide_image</span>
                                    <span class="pf-box-hint">No portfolio attached</span>
                                </div>
                            <?php else: ?>
                                <div class="pf-list">
                                    <div class="pf-list-note">
                                        <?= $p['portfolio_picked'] ? 'Chosen by the student for this project' : 'From the student\'s profile' ?>
                                    </div>

                                    <?php if ($hasFile): ?>
                                        <!-- file attached to the proposal: PDF/image -> preview, ZIP -> download -->
                                        <?php if ($p['portfolio_type'] === 'zip'): ?>
                                            <a class="pf-item" href="<?= htmlspecialchars($p['portfolio']) ?>" download>
                                        <?php else: ?>
                                            <button type="button" class="pf-item" data-portfolio-open="<?= (int)$p['id'] ?>">
                                        <?php endif; ?>
                                            <span class="pf-thumb pf-thumb-file">
                                                <span class="material-symbols-outlined"><?= ['pdf' => 'picture_as_pdf', 'image' => 'image', 'zip' => 'folder_zip'][$p['portfolio_type']] ?></span>
                                            </span>
                                            <span class="pf-item-text">
                                                <strong><?= htmlspecialchars($p['portfolio_name']) ?></strong>
                                                <small><?= $p['portfolio_type'] === 'zip' ? 'ZIP file · download' : 'Attached file · preview' ?></small>
                                            </span>
                                        <?= $p['portfolio_type'] === 'zip' ? '</a>' : '</button>' ?>
                                    <?php endif; ?>

                                    <?php foreach ($items as $it): ?>
                                        <!-- profile portfolio project -> preview popup with details + link -->
                                        <button type="button" class="pf-item" data-portfolio-item="<?= (int)$p['id'] ?>:<?= (int)$it['id'] ?>">
                                            <span class="pf-thumb">
                                                <?php if ($it['image'] !== ''): ?>
                                                    <img src="<?= htmlspecialchars($it['image']) ?>" alt="">
                                                <?php else: ?>
                                                    <span class="material-symbols-outlined"><?= $it['link_label'] === 'View on GitHub' ? 'code' : 'work' ?></span>
                                                <?php endif; ?>
                                            </span>
                                            <span class="pf-item-text">
                                                <strong><?= htmlspecialchars($it['title']) ?></strong>
                                                <small><?= $it['link'] !== '' ? ($it['link_label'] === 'View on GitHub' ? 'GitHub project' : 'Project link') : 'Project' ?> · preview</small>
                                            </span>
                                        </button>
                                    <?php endforeach; ?>

                                    <?php if (!$p['portfolio_picked'] && $p['portfolio_count'] > count($items)): ?>
                                        <button type="button" class="pf-more" data-portfolio-profile="<?= (int)$p['id'] ?>">
                                            +<?= (int)$p['portfolio_count'] - count($items) ?> more in profile
                                        </button>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>

                            <div class="pr-submitted">

                                Submitted on
                                <?= date(
                                    'M d, Y',
                                    strtotime(
                                        '-' . (int)$p['days_ago'] . ' day'
                                    )
                                ) ?>

                            </div>

                        </div>

                    </div>


                    <!-- =================================================
                         FOOTER
                    ================================================== -->

                    <div class="pr-foot">

                        <div class="pr-links">

                            <!-- View Full Proposal -->

                            <button
                                type="button"
                                class="pr-btn outline view-proposal-btn"
                                data-id="<?= (int)$p['id'] ?>"
                            >

                                <span class="material-symbols-outlined">
                                    visibility
                                </span>

                                View Full Proposal

                            </button>


                            <!-- View Profile -->

                            <button
                                type="button"
                                class="pr-link view-profile-btn"
                                data-id="<?= (int)$p['id'] ?>"
                            >

                                View Profile

                            </button>

                        </div>


                        <!-- Action buttons -->

                        <!-- Hidden once the proposal is Accepted / Rejected -->
                        <div class="pr-actions" <?= $locked ? 'hidden' : '' ?>>

                            <!-- Accept -->

                            <button
                                type="button"
                                class="pr-btn accept accept-btn"
                                data-id="<?= (int)$p['id'] ?>"
                                <?= $locked ? 'disabled' : '' ?>
                            >

                                <span class="material-symbols-outlined">
                                    check
                                </span>

                                Accept

                            </button>


                            <!-- Reject -->

                            <button
                                type="button"
                                class="pr-btn reject reject-btn"
                                data-id="<?= (int)$p['id'] ?>"
                                <?= $locked ? 'disabled' : '' ?>
                            >

                                <span class="material-symbols-outlined">
                                    close
                                </span>

                                Reject

                            </button>

                        </div>

                    </div>

                </div>

            <?php endforeach; ?>

        </div>


        <!-- =====================================================
             NO RESULTS
        ====================================================== -->

        <div
            class="pr-empty"
            id="emptyResults"
        >

            <span class="material-symbols-outlined">
                search_off
            </span>

            <h3>
                No proposals found
            </h3>

            <p>
                Try changing your search or filter options.
            </p>

        </div>

        <!-- Load more (5 proposals at a time) -->
        <div class="pr-more" id="prMore" hidden>
            <button type="button" class="pr-more-btn" id="prLoadMore">
                <span class="material-symbols-outlined">expand_more</span>
                Load More Proposals
            </button>
            <p>Showing <span id="prShown">0</span> of <span id="prTotal">0</span> proposals</p>
        </div>

    </div>


    <!-- =====================================================
         STUDENT PROFILE (shown on the same page, no popup)
    ====================================================== -->

    <section class="sp-page" id="studentProfileView" hidden>

        <div class="sp-topbar">
            <button type="button" class="sp-back" id="spBack">
                <span class="material-symbols-outlined">arrow_back</span>
                Back to Proposals
            </button>
            <div class="sp-crumbs">
                <span>Proposals</span>
                <span class="material-symbols-outlined">chevron_right</span>
                <strong>Student Profile</strong>
            </div>
        </div>

        <div class="sp-sheet">
            <div class="sp-body" id="spBody"></div>
        </div>

    </section>

</main>


<!-- =============================================================
     FULL PROPOSAL MODAL
============================================================== -->

<!-- =============================================================
     PORTFOLIO PREVIEW (PDF / image the student attached)
============================================================== -->
<div class="pr-modal-overlay" id="portfolioModal">
    <div class="pr-modal pv-modal" role="dialog" aria-modal="true">
        <div class="pv-head">
            <div class="pv-title">
                <span class="material-symbols-outlined">folder_open</span>
                <div>
                    <strong id="pvName">Portfolio</strong>
                    <small id="pvStudent"></small>
                </div>
            </div>
            <button type="button" class="pr-modal-close" data-close-modal="portfolioModal" aria-label="Close">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <div class="pv-body" id="pvBody"></div>
        <div class="pv-foot">
            <a class="pr-btn pv-download" id="pvLink" href="#" target="_blank" rel="noopener" hidden>
                <span class="material-symbols-outlined">open_in_new</span> <span id="pvLinkText">Open project</span>
            </a>
            <a class="pr-btn pv-download" id="pvDownload" href="#" download>
                <span class="material-symbols-outlined">download</span> Download
            </a>
            <button type="button" class="pr-btn pr-cancel-btn" data-close-modal="portfolioModal">Close</button>
        </div>
    </div>
</div>


<div class="pr-modal-overlay" id="proposalModal">
    <div class="pr-modal pf-modal" role="dialog" aria-modal="true">
        <div class="pf-scroll" id="proposalModalBody"></div>
        <div class="pf-foot" id="proposalModalFoot"></div>
    </div>
</div>


<!-- =============================================================
     PROFILE / PORTFOLIO MODAL
============================================================== -->

<div
    class="pr-modal-overlay"
    id="infoModal"
>

    <div class="pr-modal">

        <div class="pr-modal-head">

            <h2 id="infoModalTitle">
                Information
            </h2>

            <button
                type="button"
                class="pr-modal-close"
                data-close-modal="infoModal"
            >

                <span class="material-symbols-outlined">
                    close
                </span>

            </button>

        </div>


        <div
            class="pr-modal-body"
            id="infoModalBody"
        >
        </div>


        <div class="pr-modal-footer">

            <button
                type="button"
                class="pr-btn reject"
                data-close-modal="infoModal"
            >
                Close
            </button>

        </div>

    </div>

</div>


<!-- =============================================================
     CONFIRMATION MODAL
============================================================== -->

<div
    class="pr-modal-overlay"
    id="confirmModal"
>

    <div class="pr-modal">

        <div class="pr-modal-body">

            <div class="pr-confirm-content">

                <div class="pr-confirm-icon">

                    <span
                        class="material-symbols-outlined"
                        id="confirmIcon"
                    >
                        help
                    </span>

                </div>

                <h3 id="confirmTitle">
                    Confirm Action
                </h3>

                <p id="confirmMessage">
                    Are you sure you want to continue?
                </p>

                <div class="pr-confirm-actions">

                    <button
                        type="button"
                        class="pr-btn pr-cancel-btn"
                        data-close-modal="confirmModal"
                    >
                        Cancel
                    </button>

                    <button
                        type="button"
                        class="pr-btn pr-confirm-btn"
                        id="confirmActionButton"
                    >
                        Confirm
                    </button>

                </div>

            </div>

        </div>

    </div>

</div>


<!-- =============================================================
     ACCEPT MODAL (message is sent to the student)
============================================================== -->

<div class="pr-modal-overlay" id="acceptModal">
    <div class="pr-modal rj-modal">

        <div class="rj-head">
            <div class="rj-head-icon accept">
                <span class="material-symbols-outlined">how_to_reg</span>
            </div>
            <div class="rj-head-text">
                <h3>Accept Proposal</h3>
                <p>From <strong id="acStudent"></strong> for <strong id="acProject"></strong></p>
            </div>
            <button type="button" class="rj-close" data-close-modal="acceptModal" aria-label="Close">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>

        <div class="rj-body">
            <label class="rj-label" for="acMessage">Message to the student *</label>

            <div class="rj-quick">
                <button type="button" class="rj-chip ac-chip">Great proposal! Welcome to the project. We will add you to a team soon.</button>
                <button type="button" class="rj-chip ac-chip">Your skills are a great fit for this project. Watch your notifications for your team details.</button>
                <button type="button" class="rj-chip ac-chip">We liked your plan. Please be ready for a short kick-off meeting next week.</button>
                <button type="button" class="rj-chip ac-chip">Congratulations! Please keep the availability you mentioned in your proposal.</button>
            </div>

            <textarea id="acMessage" rows="4" maxlength="500"
                      placeholder="Write a short welcome message or the next steps. Click a suggestion above to start quickly."></textarea>
            <div class="rj-meta">
                <span class="rj-error" id="acError"></span>
                <span class="rj-count"><span id="acCount">0</span>/500</span>
            </div>

            <div class="rj-note">
                <span class="material-symbols-outlined">notifications_active</span>
                The student will get a notification with this message.
            </div>
        </div>

        <div class="rj-foot">
            <button type="button" class="pr-btn pr-cancel-btn" data-close-modal="acceptModal">Cancel</button>
            <button type="button" class="pr-btn pr-confirm-btn success" id="acSubmit">
                <span class="material-symbols-outlined">send</span>
                Accept &amp; Notify Student
            </button>
        </div>

    </div>
</div>


<!-- =============================================================
     REJECT MODAL (reason is sent to the student)
============================================================== -->

<div class="pr-modal-overlay" id="rejectModal">
    <div class="pr-modal rj-modal">

        <div class="rj-head">
            <div class="rj-head-icon">
                <span class="material-symbols-outlined">person_remove</span>
            </div>
            <div class="rj-head-text">
                <h3>Reject Proposal</h3>
                <p>From <strong id="rjStudent"></strong> for <strong id="rjProject"></strong></p>
            </div>
            <button type="button" class="rj-close" data-close-modal="rejectModal" aria-label="Close">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>

        <div class="rj-body">
            <label class="rj-label" for="rjReason">Reason for rejecting *</label>

            <div class="rj-quick">
                <button type="button" class="rj-chip">Your skills don’t match the project requirements.</button>
                <button type="button" class="rj-chip">The team for this project is already full.</button>
                <button type="button" class="rj-chip">The proposal needs more detail about your approach.</button>
                <button type="button" class="rj-chip">Your availability doesn’t match the project timeline.</button>
            </div>

            <textarea id="rjReason" rows="4" maxlength="500"
                      placeholder="Write a short, polite reason. Click a suggestion above to start quickly."></textarea>
            <div class="rj-meta">
                <span class="rj-error" id="rjError"></span>
                <span class="rj-count"><span id="rjCount">0</span>/500</span>
            </div>

            <div class="rj-note">
                <span class="material-symbols-outlined">notifications_active</span>
                The student will get a notification with this reason.
            </div>
        </div>

        <div class="rj-foot">
            <button type="button" class="pr-btn pr-cancel-btn" data-close-modal="rejectModal">Cancel</button>
            <button type="button" class="pr-btn pr-confirm-btn danger" id="rjSubmit">
                <span class="material-symbols-outlined">send</span>
                Reject &amp; Notify Student
            </button>
        </div>

    </div>
</div>


<!-- =============================================================
     TOAST
============================================================== -->

<!-- (toasts are created by showToast() in the same style as Manage Projects) -->


<!-- =============================================================
     FOOTER
============================================================== -->



<script>
    // Values from PHP (database) used by Assets/JS/Organization/proposal.js
    const ORG_DATA = {
        proposalData: <?= json_encode($proposals, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>,
        statusLabels: <?= json_encode($statusLabels, JSON_UNESCAPED_UNICODE) ?>,
        projectDetails: <?= json_encode($projectDetails, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP) ?>,
        studentProfiles: <?= json_encode($studentProfiles, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP) ?>,
    };
</script>
<script src="../../Assets/JS/Organization/proposal.js?v=<?= filemtime(__DIR__ . '/../../Assets/JS/Organization/proposal.js') ?>"></script>


<?php include "../../Includes/dash_footer.php"; ?>