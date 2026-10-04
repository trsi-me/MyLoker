(function () {
    const base = typeof MYLOCKER_BASE !== 'undefined' ? MYLOCKER_BASE : '/MyLoker';

    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('[data-mark-return]').forEach((btn) => {
            btn.addEventListener('click', async () => {
                const rid = btn.getAttribute('data-reservation-id');
                if (!rid || !confirm('Confirm key return?')) {
                    return;
                }
                try {
                    const fd = new FormData();
                    fd.append('reservation_id', rid);
                    const res = await fetch(base + '/api/return_key.php', {
                        method: 'POST',
                        body: fd,
                        credentials: 'same-origin',
                    });
                    const data = await res.json();
                    if (data.ok) {
                        location.reload();
                    } else {
                        alert(data.message || 'Error');
                    }
                } catch (e) {
                    alert('Error');
                }
            });
        });
    });
})();
