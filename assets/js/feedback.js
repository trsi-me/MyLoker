(function () {
    document.addEventListener('DOMContentLoaded', () => {
        const stars = document.querySelectorAll('.stars button');
        const ratingInput = document.getElementById('ratingValue');
        stars.forEach((btn, idx) => {
            btn.addEventListener('click', () => {
                const v = idx + 1;
                if (ratingInput) {
                    ratingInput.value = String(v);
                }
                stars.forEach((b, i) => {
                    b.classList.toggle('is-on', i < v);
                });
            });
        });

        const zone = document.getElementById('uploadZone');
        const fileInput = document.getElementById('maintPhoto');
        if (zone && fileInput) {
            zone.addEventListener('click', () => fileInput.click());
            zone.addEventListener('dragover', (e) => {
                e.preventDefault();
                zone.style.borderColor = '#1A7EC8';
            });
            zone.addEventListener('dragleave', () => {
                zone.style.borderColor = '';
            });
            zone.addEventListener('drop', (e) => {
                e.preventDefault();
                zone.style.borderColor = '';
                if (e.dataTransfer.files.length) {
                    fileInput.files = e.dataTransfer.files;
                }
            });
        }
    });
})();
