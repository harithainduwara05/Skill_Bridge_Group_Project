/*
| Organization - profile page script
| Used by: Functions/Organization/profile.php
*/

// ---- Remove Logo popup ----
// (the Remove Logo popup is on the page only when the organization has a logo)
function openRemoveLogoModal() {
    document.getElementById('removeLogoModal').classList.add('show');
    document.getElementById('rlCancel').focus();
}
function closeRemoveLogoModal() {
    const modal = document.getElementById('removeLogoModal');
    if (modal) modal.classList.remove('show');
}
function confirmRemoveLogo() {
    document.getElementById('rlConfirm').disabled = true;   // avoid double submit
    document.getElementById('removeLogo').value = '1';
    document.getElementById('profileForm').requestSubmit();   // goes through the check below
}
document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') closeRemoveLogoModal();
});

// ---- Profile form check (the same rules are checked again in Backend/Organization/profile.php) ----
(function () {
    const form = document.getElementById('profileForm');
    const val  = name => (form.querySelector('[name="' + name + '"]')?.value || '').trim();

    // "mydb.org" or "https://mydb.org" -> true; sites = allowed domains for a social link
    function isUrl(text, sites) {
        if (!/^https?:\/\//i.test(text)) text = 'https://' + text;
        let host;
        try { host = new URL(text).hostname.toLowerCase(); } catch (e) { return false; }
        if (text.length > 200 || !host.includes('.')) return false;
        if (sites) return sites.includes(host.replace(/^(www\.|m\.)/, ''));
        return true;
    }

    function validate() {
        const e = {};
        const name = val('org_name'), industry = val('industry'), address = val('address');
        const phone = val('contact_number').replace(/[\s-]/g, ''), website = val('website');

        if (name === '') e.org_name = 'Organization name is required.';
        else if (name.length < 2 || name.length > 100) e.org_name = 'Name must be 2 to 100 characters.';

        if (industry === '') e.industry = 'Industry is required.';
        else if (industry.length < 2 || industry.length > 100) e.industry = 'Industry must be 2 to 100 characters.';

        if (phone === '') e.contact_number = 'Contact number is required.';
        else if (!/^(0\d{9}|\+94\d{9})$/.test(phone)) e.contact_number = 'Enter a valid number, e.g. 0771234567 or +94 77 123 4567.';

        if (website === '') e.website = 'Website URL is required.';
        else if (!isUrl(website)) e.website = 'Enter a valid website, e.g. https://example.com';

        if (address === '') e.address = 'Address is required.';
        else if (address.length < 5 || address.length > 100) e.address = 'Address must be 5 to 100 characters.';

        if (val('about').length > 1000) e.about = 'About can have up to 1000 characters.';

        if (val('linkedin') && !isUrl(val('linkedin'), ['linkedin.com'])) e.linkedin = 'Enter a LinkedIn link, e.g. https://linkedin.com/company/your-org';
        if (val('twitter') && !isUrl(val('twitter'), ['twitter.com', 'x.com'])) e.twitter = 'Enter a Twitter / X link, e.g. https://x.com/your-org';
        if (val('facebook') && !isUrl(val('facebook'), ['facebook.com', 'fb.com'])) e.facebook = 'Enter a Facebook link, e.g. https://facebook.com/your-org';

        // logo: type + size (2MB)
        const logo = document.getElementById('logoInput').files[0];
        if (logo) {
            if (!['image/jpeg', 'image/png', 'image/gif', 'image/webp'].includes(logo.type)) e.logo = 'Logo must be a JPG, PNG, GIF or WEBP image.';
            else if (logo.size > 2 * 1024 * 1024) e.logo = 'Logo file is too large (max 2MB).';
        }
        return e;
    }

    function show(errors) {
        form.querySelectorAll('.field-error').forEach(box => box.textContent = errors[box.dataset.for] || '');
    }

    let checked = !!form.querySelector('.field-error:not(:empty)');   // server already found errors
    form.addEventListener('submit', function (ev) {
        const errors = validate();
        show(errors);
        if (Object.keys(errors).length) {
            ev.preventDefault();
            checked = true;
            // a logo upload / removal that was stopped is cancelled
            if (errors.logo) document.getElementById('logoInput').value = '';
            const remove = document.getElementById('removeLogo');
            if (remove) { remove.value = '0'; if (window.closeRemoveLogoModal) closeRemoveLogoModal(); }
            const rc = document.getElementById('rlConfirm'); if (rc) rc.disabled = false;
            form.querySelector('.field-error:not(:empty)').closest('.form-group, .avatar-wrap')
                .scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    });
    form.addEventListener('input', function () { if (checked) show(validate()); });
})();

// Auto-hide the flash toast message after a few seconds
document.addEventListener('DOMContentLoaded', function () {
    const toast = document.querySelector('.flash-toast');
    if (toast) {
        setTimeout(function () {
            toast.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
            toast.style.opacity = '0';
            toast.style.transform = 'translateX(20px)';
            setTimeout(function () {
                toast.remove();
            }, 400);
        }, 3000); // visible for 3 seconds
    }
});