document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('form[data-confirm]').forEach(form => {
        form.addEventListener('submit', event => {
            if (!window.confirm(form.dataset.confirm)) {
                event.preventDefault();
            }
        });
    });

    document.querySelectorAll('.notification-toast').forEach(toast => {
        let dismissed = false;
        const dismiss = () => {
            if (dismissed) return;
            dismissed = true;
            toast.classList.add('is-hiding');
            window.setTimeout(() => toast.remove(), 300);
        };
        toast.querySelector('.notification-toast-close')?.addEventListener('click', dismiss);
        window.setTimeout(dismiss, Number(toast.dataset.autohide) || 4500);
    });
});
