/*
| Organization - edit_project page script
| Used by: Functions/Organization/edit_project.php
*/

// Edit Project: show the "start with current team" note when Active is chosen early
(function () {
    const sel  = document.getElementById('statusSelect');
    const note = document.getElementById('earlyStartNote');
    if (!sel || !note) return;
    const update = () => { note.hidden = sel.value !== 'inprogress'; };
    sel.addEventListener('change', update);
    update();
})();

    // Pre-fill skill chips from existing keywords, then reuse the same chip logic as post-project.js
    document.addEventListener('DOMContentLoaded', function () {
        const chipBox = document.getElementById('chipBox');
        const chipInput = document.getElementById('chipInput');
        const keywordsHidden = document.getElementById('keywordsHidden');
        const existing = keywordsHidden.value.split(',').map(s => s.trim()).filter(Boolean);

        function addChip(text) {
            const chip = document.createElement('span');
            chip.className = 'chip';
            chip.textContent = text;
            const remove = document.createElement('span');
            remove.textContent = ' \u00d7';
            remove.style.cursor = 'pointer';
            remove.onclick = function () {
                chip.remove();
                updateHidden();
            };
            chip.appendChild(remove);
            chipBox.insertBefore(chip, chipInput);
        }

        function updateHidden() {
            const chips = Array.from(chipBox.querySelectorAll('.chip')).map(c => c.firstChild.textContent);
            keywordsHidden.value = chips.join(', ');
        }

        existing.forEach(addChip);

        // add a skill once (same skill in any case is not added again)
        function addTypedSkill() {
            const text = chipInput.value.trim().replace(/,+$/, '');
            const current = keywordsHidden.value.split(',').map(s => s.trim().toLowerCase());
            if (text !== '' && !current.includes(text.toLowerCase())) {
                addChip(text);
                updateHidden();
            }
            chipInput.value = '';
        }

        chipInput.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' || e.key === ',') {
                e.preventDefault();
                addTypedSkill();
            }
        });

        // a skill typed but not "entered" yet is added when the form is sent
        // (runs before the check in project-validation.js)
        chipInput.form.addEventListener('submit', addTypedSkill);

        // Difficulty toggle
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
    });