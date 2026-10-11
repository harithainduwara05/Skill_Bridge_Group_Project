<?php

$totalRecords = (isset($totalRecords) && is_numeric($totalRecords)) 
    ? (int)$totalRecords 
    : ((isset($totFilteredUni) && is_numeric($totFilteredUni)) ? (int)$totFilteredUni : ((isset($totalItems) && is_numeric($totalItems)) ? (int)$totalItems : 0));

$perPage = (isset($perPage) && is_numeric($perPage)) 
    ? (int)$perPage 
    : ((isset($recordsPerPage) && is_numeric($recordsPerPage)) ? (int)$recordsPerPage : ((isset($limit) && is_numeric($limit)) ? (int)$limit : 10));

if ($perPage < 1) {
    $perPage = 10;
}

$pageParam = $pageParam ?? 'page';

// Note: $currentPage is often used by admin_sidebar.php as a string (e.g. 'User_Management.php').
// We strictly only use it if it's numeric, otherwise falling back to $currentPageNum or $_GET.
if (isset($currentPageNum) && is_numeric($currentPageNum)) {
    $activePageNum = (int)$currentPageNum;
} elseif (isset($pageNo) && is_numeric($pageNo)) {
    $activePageNum = (int)$pageNo;
} elseif (isset($page) && is_numeric($page)) {
    $activePageNum = (int)$page;
} elseif (isset($currentPage) && is_numeric($currentPage)) {
    $activePageNum = (int)$currentPage;
} else {
    $activePageNum = isset($_GET[$pageParam]) ? (int)$_GET[$pageParam] : 1;
}

if ($activePageNum < 1) {
    $activePageNum = 1;
}

$totalPages  = (isset($totalPages) && is_numeric($totalPages)) ? (int)$totalPages : (int)ceil($totalRecords / max(1, $perPage));
$entityLabel = $entityLabel ?? 'entries';
$alwaysShow  = $alwaysShow ?? false;
$paginationInfoId     = $paginationInfoId ?? null;
$paginationControlsId = $paginationControlsId ?? null;

// Only render when there is more than 1 page or when $alwaysShow is enabled with records
if ($totalPages > 1 || ($alwaysShow && $totalRecords > 0)):
    $offset      = ($activePageNum - 1) * $perPage;
    $startRecord = ($totalRecords > 0) ? ($offset + 1) : 0;
    $endRecord   = min($offset + $perPage, $totalRecords);

    // Build URL maintaining existing query parameters (search, filter, role, etc.)
    $buildPageUrl = function($targetPage) use ($pageParam) {
        $params = $_GET;
        $params[$pageParam] = $targetPage;
        return '?' . http_build_query($params);
    };
?>
<div class="user-pagination">
    <div class="user-pagination-info"<?= !empty($paginationInfoId) ? ' id="' . htmlspecialchars($paginationInfoId) . '"' : '' ?>>
        Showing <?= $startRecord ?>–<?= $endRecord ?> of <?= $totalRecords ?> <?= htmlspecialchars($entityLabel) ?>
    </div>
    <div class="user-pagination-controls"<?= !empty($paginationControlsId) ? ' id="' . htmlspecialchars($paginationControlsId) . '"' : '' ?>>
        <!-- Previous Page -->
        <?php if ($activePageNum > 1): ?>
            <a href="<?= htmlspecialchars($buildPageUrl($activePageNum - 1)) ?>" class="user-page-btn">Previous</a>
        <?php else: ?>
            <span class="user-page-btn disabled">Previous</span>
        <?php endif; ?>

        <!-- Page Numbers with Ellipsis Handling -->
        <?php
            $range = [];
            if ($totalPages <= 7) {
                $range = range(1, $totalPages);
            } else {
                if ($activePageNum <= 4) {
                    $range = [1, 2, 3, 4, 5, '...', $totalPages];
                } elseif ($activePageNum >= $totalPages - 3) {
                    $range = [1, '...', $totalPages - 4, $totalPages - 3, $totalPages - 2, $totalPages - 1, $totalPages];
                } else {
                    $range = [1, '...', $activePageNum - 1, $activePageNum, $activePageNum + 1, '...', $totalPages];
                }
            }

            foreach ($range as $pageItem):
                if ($pageItem === '...'):
        ?>
                    <span class="page-ellipsis">..</span>
        <?php   elseif ($pageItem === (int)$activePageNum): ?>
                    <span class="user-page-btn active"><?= $pageItem ?></span>
        <?php   else: ?>
                    <a href="<?= htmlspecialchars($buildPageUrl($pageItem)) ?>" class="user-page-btn">
                        <?= $pageItem ?>
                    </a>
        <?php   endif;
            endforeach;
        ?>

        <!-- Next Page -->
        <?php if ($activePageNum < $totalPages): ?>
            <a href="<?= htmlspecialchars($buildPageUrl($activePageNum + 1)) ?>" class="user-page-btn">Next</a>
        <?php else: ?>
            <span class="user-page-btn disabled">Next</span>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>
