(function () {
    document.addEventListener('DOMContentLoaded', () => {
        const modal = document.getElementById('detailModal');
        const closeBtn = document.getElementById('detailModalClose');
        if (closeBtn && modal) {
            closeBtn.addEventListener('click', () => {
                modal.classList.remove('is-open');
            });
        }
        document.querySelectorAll('[data-open-res]').forEach((btn) => {
            btn.addEventListener('click', () => {
                const body = document.getElementById('detailModalBody');
                if (body) {
                    body.innerHTML = btn.getAttribute('data-detail-html') || '';
                }
                if (modal) {
                    modal.classList.add('is-open');
                }
            });
        });
    });
})();
