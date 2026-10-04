(function () {
    document.addEventListener('DOMContentLoaded', () => {
        const form = document.getElementById('maintForm');
        if (!form) {
            return;
        }
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const fd = new FormData(form);
            const base = typeof MYLOCKER_BASE !== 'undefined' ? MYLOCKER_BASE : '/MyLoker';
            try {
                const res = await fetch(base + '/api/submit_maintenance.php', {
                    method: 'POST',
                    body: fd,
                    credentials: 'same-origin',
                });
                const data = await res.json();
                if (data.ok) {
                    alert(typeof t === 'function' ? t('success_saved') : 'OK');
                    form.reset();
                } else {
                    alert(data.message || (typeof t === 'function' ? t('error_generic') : 'Error'));
                }
            } catch (err) {
                alert(typeof t === 'function' ? t('error_generic') : 'Error');
            }
        });
    });
})();
