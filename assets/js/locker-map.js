(function () {
    const base = typeof MYLOCKER_BASE !== 'undefined' ? MYLOCKER_BASE : '/MyLoker';

    function apiUrl(path) {
        return base + '/api/' + path;
    }

    function renderGrid(container, lockers, options) {
        if (!container) {
            return;
        }
        container.innerHTML = '';
        const mineLockerId = options.mineLockerId || null;
        lockers.forEach((L) => {
            const cell = document.createElement('div');
            cell.className = 'locker-cell';
            cell.textContent = L.locker_number;
            cell.dataset.id = L.locker_id;
            if (String(L.locker_id) === String(mineLockerId)) {
                cell.classList.add('locker-cell--mine');
            } else if (L.status === 'available') {
                cell.classList.add('locker-cell--available');
            } else if (L.status === 'maintenance') {
                cell.classList.add('locker-cell--maintenance');
            } else {
                cell.classList.add('locker-cell--booked');
            }
            const canClick = options.clickable && (L.status === 'available' || options.clickAny);
            if (canClick) {
                cell.classList.add('locker-cell--clickable');
                cell.addEventListener('click', () => {
                    if (typeof options.onSelect === 'function') {
                        options.onSelect(L);
                    }
                });
            }
            container.appendChild(cell);
        });
    }

    async function fetchLockers(params) {
        const q = new URLSearchParams(params || {});
        const res = await fetch(apiUrl('get_lockers.php') + '?' + q.toString(), { credentials: 'same-origin' });
        return res.json();
    }

    window.MyLockerMap = { renderGrid, fetchLockers };
})();
