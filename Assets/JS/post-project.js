document.addEventListener('DOMContentLoaded', function () {

    /* ---------- Keyword / skill chips ---------- */
    const chipBox = document.getElementById('chipBox');
    const chipInput = document.getElementById('chipInput');
    const keywordsHidden = document.getElementById('keywordsHidden');
    let keywords = [];

    function renderChips() {
        chipBox.querySelectorAll('.chip').forEach(el => el.remove());
        keywords.forEach((word, idx) => {
            const chip = document.createElement('span');
            chip.className = 'chip';
            chip.innerHTML = `${escapeHtml(word)} <button type="button" class="chip-remove" data-idx="${idx}">&times;</button>`;
            chipBox.insertBefore(chip, chipInput);
        });
        keywordsHidden.value = keywords.join(',');
    }

    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    if (chipInput) {
        chipInput.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' || e.key === ',') {
                e.preventDefault();
                const val = chipInput.value.trim();
                if (val && !keywords.includes(val)) {
                    keywords.push(val);
                    renderChips();
                }
                chipInput.value = '';
            } else if (e.key === 'Backspace' && chipInput.value === '' && keywords.length) {
                keywords.pop();
                renderChips();
            }
        });

        chipBox.addEventListener('click', function () {
            chipInput.focus();
        });

        chipBox.addEventListener('click', function (e) {
            if (e.target.classList.contains('chip-remove')) {
                const idx = parseInt(e.target.dataset.idx, 10);
                keywords.splice(idx, 1);
                renderChips();
            }
        });
    }

    /* ---------- Difficulty toggle ---------- */
    const difficultyGroup = document.getElementById('difficultyGroup');
    const difficultyHidden = document.getElementById('difficultyHidden');

    if (difficultyGroup) {
        difficultyGroup.querySelectorAll('.toggle-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                difficultyGroup.querySelectorAll('.toggle-btn').forEach(b => b.classList.remove('selected'));
                btn.classList.add('selected');
                difficultyHidden.value = btn.dataset.value;
            });
        });
    }

    /* ---------- Supporting documents dropzone ---------- */
    const dropzone = document.getElementById('dropzone');
    const fileInput = document.getElementById('fileInput');
    const fileList = document.getElementById('fileList');

    if (dropzone && fileInput) {
        dropzone.addEventListener('click', () => fileInput.click());

        ['dragenter', 'dragover'].forEach(evt => {
            dropzone.addEventListener(evt, function (e) {
                e.preventDefault();
                dropzone.classList.add('dragover');
            });
        });

        ['dragleave', 'drop'].forEach(evt => {
            dropzone.addEventListener(evt, function (e) {
                e.preventDefault();
                dropzone.classList.remove('dragover');
            });
        });

        dropzone.addEventListener('drop', function (e) {
            fileInput.files = e.dataTransfer.files;
            renderFileList();
        });

        fileInput.addEventListener('change', renderFileList);

        function renderFileList() {
            fileList.innerHTML = '';
            Array.from(fileInput.files).forEach(file => {
                const chip = document.createElement('span');
                chip.className = 'chip';
                chip.innerHTML = `<span class="material-symbols-outlined" style="font-size:14px;">description</span> ${file.name}`;
                fileList.appendChild(chip);
            });
        }
    }

    /* ---------- Preview ---------- */
    const previewBtn = document.getElementById('previewBtn');
    const form = document.getElementById('postProjectForm') || document.getElementById('editProjectForm');

    function escapeForPreview(str) {
        const div = document.createElement('div');
        div.textContent = str || '';
        return div.innerHTML;
    }

    function buildPreviewHtml() {
        const get = name => {
            const el = form.querySelector(`[name="${name}"]`);
            return el ? el.value.trim() : '';
        };
        const getChecked = name => {
            const el = form.querySelector(`[name="${name}"]:checked`);
            return el ? el.value : '';
        };
        const getSelectText = name => {
            const el = form.querySelector(`select[name="${name}"]`);
            return el && el.selectedIndex >= 0 ? el.options[el.selectedIndex].text : '';
        };

        const title = get('title') || 'Untitled Project';
        const category = getSelectText('category') || '—';
        const skills = (keywordsHidden.value || '').split(',').map(s => s.trim()).filter(Boolean);
        const description = get('description') || '—';
        const learningObjectives = get('learning_objectives');
        const expectedOutcomes = get('expected_outcomes');
        const difficulty = difficultyHidden ? difficultyHidden.value : '—';
        const durationWeeks = get('duration_weeks');
        const studentsRequired = get('students_required');
        const preferredYear = getSelectText('preferred_year') || 'Any Year';
        const deadline = get('deadline');
        const visibility = getChecked('visibility') || 'Public';

        const skillsHtml = skills.length
            ? skills.map(s => `<span class="chip" style="margin:0 6px 6px 0;">${escapeForPreview(s)}</span>`).join('')
            : '<span style="color:#9ca3af;">No skills added</span>';

        return `
            <h2 style="margin:0 0 4px;">${escapeForPreview(title)}</h2>
            <div style="color:#6b7280; font-size:14px; margin-bottom:18px;">
                ${escapeForPreview(category)} &middot; ${escapeForPreview(difficulty)} &middot; ${escapeForPreview(visibility)}
            </div>

            <div style="margin-bottom:14px;">${skillsHtml}</div>

            <h4 style="margin:16px 0 6px;">Description</h4>
            <p style="white-space:pre-wrap; margin:0;">${escapeForPreview(description)}</p>

            ${learningObjectives ? `<h4 style="margin:16px 0 6px;">Learning Objectives</h4><p style="white-space:pre-wrap; margin:0;">${escapeForPreview(learningObjectives)}</p>` : ''}

            ${expectedOutcomes ? `<h4 style="margin:16px 0 6px;">Expected Outcomes</h4><p style="white-space:pre-wrap; margin:0;">${escapeForPreview(expectedOutcomes)}</p>` : ''}

            <div style="display:grid; grid-template-columns:repeat(3, 1fr); gap:12px; margin-top:18px; font-size:14px;">
                <div><strong>Duration</strong><br>${durationWeeks ? escapeForPreview(durationWeeks) + ' Weeks' : '—'}</div>
                <div><strong>Students Required</strong><br>${studentsRequired ? escapeForPreview(studentsRequired) : '—'}</div>
                <div><strong>Preferred Year</strong><br>${escapeForPreview(preferredYear)}</div>
                <div><strong>Deadline</strong><br>${deadline ? escapeForPreview(deadline) : '—'}</div>
            </div>
        `;
    }

    function openPreviewModal() {
        const overlay = document.createElement('div');
        overlay.id = 'previewModalOverlay';
        overlay.style.cssText = 'position:fixed; inset:0; background:rgba(15,23,42,0.55); display:flex; align-items:center; justify-content:center; z-index:1000; padding:24px;';

        const modal = document.createElement('div');
        modal.style.cssText = 'background:#fff; border-radius:12px; max-width:640px; width:100%; max-height:85vh; overflow-y:auto; padding:28px 32px; position:relative; box-shadow:0 20px 50px rgba(0,0,0,0.25);';

        const closeBtn = document.createElement('button');
        closeBtn.type = 'button';
        closeBtn.innerHTML = '&times;';
        closeBtn.setAttribute('aria-label', 'Close preview');
        closeBtn.style.cssText = 'position:absolute; top:14px; right:16px; background:none; border:none; font-size:26px; line-height:1; cursor:pointer; color:#6b7280;';
        closeBtn.addEventListener('click', () => overlay.remove());

        modal.innerHTML = buildPreviewHtml();
        modal.appendChild(closeBtn);
        overlay.appendChild(modal);

        overlay.addEventListener('click', function (e) {
            if (e.target === overlay) overlay.remove();
        });
        document.addEventListener('keydown', function escListener(e) {
            if (e.key === 'Escape') {
                overlay.remove();
                document.removeEventListener('keydown', escListener);
            }
        });

        document.body.appendChild(overlay);
    }

    if (previewBtn && form) {
        previewBtn.addEventListener('click', openPreviewModal);
    }
});