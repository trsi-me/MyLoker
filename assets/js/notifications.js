(function () {
    const base = typeof MYLOCKER_BASE !== 'undefined' ? MYLOCKER_BASE : '/MyLoker';

    document.addEventListener('DOMContentLoaded', () => {
        const btn = document.getElementById('markAllReadBtn');
        if (!btn) {
            return;
        }
        btn.addEventListener('click', async () => {
            try {
                const fd = new FormData();
                fd.append('mark_all', '1');
                const res = await fetch(base + '/api/get_notifications.php', {
                    method: 'POST',
                    body: fd,
                    credentials: 'same-origin',
                });
                const data = await res.json();
                if (data.ok) {
                    location.reload();
                }
            } catch (e) {
                /* ignore */
            }
        });
    });
})();
