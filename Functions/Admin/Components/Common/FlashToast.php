<?php if (!empty($flash)): ?>
    <div class="flash-toast flash-<?= htmlspecialchars($flash['type']) ?>" id="flashToast">
        <div class="flash-icon-wrap">
            <span class="material-symbols-outlined">
                <?= $flash['type'] === 'error' ? 'error' : 'check_circle' ?>
            </span>
        </div>
        <div class="flash-content">
            <div class="flash-title"><?= $flash['type'] === 'error' ? 'Action Failed' : 'Success!' ?></div>
            <div class="flash-msg"><?= htmlspecialchars($flash['message']) ?></div>
        </div>
        <button class="flash-close" onclick="closeToast()" title="Dismiss" type="button">
            <span class="material-symbols-outlined" style="font-size:18px;">close</span>
        </button>
        <div class="flash-progress"></div>
    </div>
<?php endif; ?>