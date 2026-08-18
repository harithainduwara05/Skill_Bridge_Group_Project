document.addEventListener('DOMContentLoaded', () => {
    // Candidates Table Options Dropdown Click Handler
    const optionButtons = document.querySelectorAll('.btn-more-options');

    optionButtons.forEach(button => {
        button.addEventListener('click', (e) => {
            e.stopPropagation();
            console.log('Action options clicked for candidate');
        });
    });
});