document.addEventListener('DOMContentLoaded', () => {
    // 1. Interactive Skills Tags Logic
    const skillsTagWrapper = document.getElementById('skillsTagWrapper');
    const skillTextField = document.getElementById('skillTextField');
    const btnAddSkill = document.getElementById('btnAddSkill');
    const skillsInput = document.getElementById('skillsInput');

    function updateSkillsHiddenInput() {
        const tags = Array.from(skillsTagWrapper.querySelectorAll('.tag')).map(tag => {
            return tag.childNodes[0].textContent.trim();
        });
        skillsInput.value = tags.join(',');
    }

    function addSkillTag(skillName) {
        const cleanSkill = skillName.trim();
        if (!cleanSkill) return;

        const tagDiv = document.createElement('div');
        tagDiv.className = 'tag';
        tagDiv.innerHTML = `${cleanSkill} <button type="button" class="remove-tag">&times;</button>`;

        skillsTagWrapper.appendChild(tagDiv);
        skillTextField.value = '';
        updateSkillsHiddenInput();
    }

    // Add Skill on Button Click
    btnAddSkill.addEventListener('click', () => {
        addSkillTag(skillTextField.value);
    });

    // Add Skill on Enter Key
    skillTextField.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') {
            e.preventDefault();
            addSkillTag(skillTextField.value);
        }
    });

    // Remove Skill Tag Event Delegation
    skillsTagWrapper.addEventListener('click', (e) => {
        if (e.target.classList.contains('remove-tag')) {
            e.target.parentElement.remove();
            updateSkillsHiddenInput();
        }
    });

    // 2. Positions Increment/Decrement Counter
    const btnDecrement = document.getElementById('btnDecrement');
    const btnIncrement = document.getElementById('btnIncrement');
    const positionsCount = document.getElementById('positionsCount');

    btnDecrement.addEventListener('click', () => {
        let currentValue = parseInt(positionsCount.value, 10) || 1;
        if (currentValue > 1) {
            positionsCount.value = currentValue - 1;
        }
    });

    btnIncrement.addEventListener('click', () => {
        let currentValue = parseInt(positionsCount.value, 10) || 1;
        positionsCount.value = currentValue + 1;
    });

    // 3. Form Action Handlers (Save Draft vs Publish vs Discard)
    const internshipForm = document.getElementById('internshipForm');
    const formAction = document.getElementById('formAction');
    const btnSaveDraft = document.getElementById('btnSaveDraft');
    const btnDiscard = document.getElementById('btnDiscard');

    btnSaveDraft.addEventListener('click', () => {
        formAction.value = 'draft';
        // Remove required constraints temporarily for draft saving
        internshipForm.querySelectorAll('[required]').forEach(input => input.removeAttribute('required'));
        internshipForm.submit();
    });

    btnDiscard.addEventListener('click', () => {
        if (confirm('Are you sure you want to discard this form? All unsaved data will be lost.')) {
            window.location.href = 'internships.php';
        }
    });
});