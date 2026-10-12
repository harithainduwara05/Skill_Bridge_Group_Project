<?php
/**
 * Reusable Admin Page Header Component (PageHeader.php)
 *
 * Parameters via array $HeaderDetails:
 *   - 'title'          : Main page heading (required)
 *   - 'subtitle'       : Subheading description (required)
 *   - 'breadcrumb'     : Optional breadcrumb label (defaults to title)
 *   - 'showBreadcrumb' : Optional boolean to show/hide breadcrumbs (defaults to true)
 *   - 'btnText'        : Optional action button text
 *   - 'btnId'          : Optional action button id
 *   - 'btnIcon'        : Optional action button Material icon name
 *   - 'btnClass'       : Optional action button CSS class (defaults to 'btn-primary')
 *   - 'extraActions'   : Optional custom HTML for additional buttons/controls
 */

$title          = $HeaderDetails['title'] ?? $headerTitle ?? '';
$subtitle       = $HeaderDetails['subtitle'] ?? $headerSubtitle ?? '';
$breadcrumb     = $HeaderDetails['breadcrumb'] ?? $headerBreadcrumb ?? $title;
$showBreadcrumb = $HeaderDetails['showBreadcrumb'] ?? $showBreadcrumb ?? true;
$btnText        = $HeaderDetails['btnText'] ?? $headerBtnText ?? null;
$btnId          = $HeaderDetails['btnId'] ?? $headerBtnId ?? null;
$btnIcon        = $HeaderDetails['btnIcon'] ?? $headerBtnIcon ?? null;
$btnClass       = $HeaderDetails['btnClass'] ?? $headerBtnClass ?? 'btn-primary';
$extraActions   = $HeaderDetails['extraActions'] ?? $headerExtraActions ?? null;
$headerClass    = $HeaderDetails['headerClass'] ?? $HeaderDetails['containerClass'] ?? '';
?>
<div class="admin-page-header<?= !empty($headerClass) ? ' ' . htmlspecialchars($headerClass) : '' ?>">
    <?php if ($showBreadcrumb && !empty($breadcrumb)): ?>
    <div class="admin-breadcrumb">
        <a href="dashboard.php">Admin</a>
        <span class="material-symbols-outlined" style="font-size:14px;color:#9ca3af;">chevron_right</span>
        <span><?= htmlspecialchars($breadcrumb) ?></span>
    </div>
    <?php endif; ?>

    <div class="admin-title-row">
        <div class="admin-title-text">
            <h1 class="admin-title"><?= htmlspecialchars($title) ?></h1>
            <p class="admin-subtitle"><?= htmlspecialchars($subtitle) ?></p>
        </div>

        <?php if (!empty($btnText) || !empty($extraActions)): ?>
        <div class="admin-header-actions">
            <?php if (!empty($btnText)): ?>
            <button class="<?= htmlspecialchars($btnClass) ?>"<?= !empty($btnId) ? ' id="' . htmlspecialchars($btnId) . '"' : '' ?>>
                <?php if (!empty($btnIcon)): ?>
                <span class="material-symbols-outlined" style="font-size:18px;"><?= htmlspecialchars($btnIcon) ?></span>
                <?php endif; ?>
                <?= htmlspecialchars($btnText) ?>
            </button>
            <?php endif; ?>
            <?= $extraActions ?? '' ?>
        </div>
        <?php endif; ?>
    </div>
</div>
