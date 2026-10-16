function openDeleteModal(notifId, notifTitle) {
    document.getElementById('deleteNotifId').value = notifId;
    document.getElementById('deleteNotifTitle').textContent = notifTitle;
    document.getElementById('deleteNotifModal').classList.add('active');
}

function closeDeleteModal(event) {
    if (!event || event.target.id === 'deleteNotifModal' || event.type === 'click') {
        const modal = document.getElementById('deleteNotifModal');
        if (modal) modal.classList.remove('active');
    }
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeDeleteModal();
    }
});