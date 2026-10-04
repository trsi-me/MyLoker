(function () {
    const base = typeof MYLOCKER_BASE !== 'undefined' ? MYLOCKER_BASE : '/MyLoker';

    function apiUrl(path) {
        return base + '/api/' + path;
    }

    async function loadNotifCount() {
        const badge = document.getElementById('notifCountBadge');
        if (!badge) {
            return;
        }
        try {
            const res = await fetch(apiUrl('get_notifications.php?count=1'), { credentials: 'same-origin' });
            const data = await res.json();
            if (data.ok && typeof data.unread === 'number') {
                if (data.unread > 0) {
                    badge.style.display = 'flex';
                    badge.textContent = data.unread > 99 ? '99+' : String(data.unread);
                } else {
                    badge.style.display = 'none';
                }
            }
        } catch (e) {
            /* ignore */
        }
    }

    async function loadNotifDropdown() {
        const list = document.getElementById('notifDropdownList');
        if (!list) {
            return;
        }
        try {
            const res = await fetch(apiUrl('get_notifications.php?limit=5'), { credentials: 'same-origin' });
            const data = await res.json();
            list.innerHTML = '';
            if (!data.ok || !data.items || !data.items.length) {
                const li = document.createElement('li');
                li.textContent = typeof t === 'function' ? t('no_data') : '—';
                list.appendChild(li);
                return;
            }
            const lang = typeof getLang === 'function' ? getLang() : 'ar';
            data.items.forEach((n) => {
                const li = document.createElement('li');
                if (!n.is_read) {
                    li.classList.add('is-unread');
                }
                const title = lang === 'en' ? n.title_en : n.title_ar;
                const msg = lang === 'en' ? n.message_en : n.message_ar;
                li.innerHTML = '<strong>' + escapeHtml(title) + '</strong><br><span class="text-muted">' + escapeHtml(msg) + '</span>';
                list.appendChild(li);
            });
        } catch (e) {
            list.innerHTML = '<li>' + (typeof t === 'function' ? t('error_generic') : 'Error') + '</li>';
        }
    }

    function escapeHtml(s) {
        const d = document.createElement('div');
        d.textContent = s;
        return d.innerHTML;
    }

    document.addEventListener('DOMContentLoaded', () => {
        loadNotifCount();
        const bell = document.getElementById('notifBellBtn');
        const drop = document.getElementById('notifDropdown');
        if (bell && drop) {
            bell.addEventListener('click', (e) => {
                e.stopPropagation();
                const open = !drop.hidden;
                drop.hidden = open;
                if (!open) {
                    loadNotifDropdown();
                    loadNotifCount();
                }
            });
            document.addEventListener('click', () => {
                drop.hidden = true;
            });
        }
        setInterval(loadNotifCount, 60000);
    });
})();
