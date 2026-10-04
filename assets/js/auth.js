(function () {
    function updateLoginFormRoleUI() {
        const roleInput = document.getElementById('loginRole');
        const label = document.querySelector('label[for="student_id"]');
        const input = document.getElementById('student_id');
        const reg = document.getElementById('authRegisterRow');
        if (!roleInput || !label || !input || typeof window.t !== 'function') {
            return;
        }
        const isAdmin = roleInput.value === 'admin';
        const key = isAdmin ? 'admin_login_id' : 'student_id';
        label.setAttribute('data-i18n', key);
        input.setAttribute('data-i18n-placeholder', key);
        label.textContent = window.t(key);
        input.setAttribute('placeholder', window.t(key));
        if (reg) {
            reg.style.display = isAdmin ? 'none' : '';
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        const tabStudent = document.getElementById('tabStudent');
        const tabAdmin = document.getElementById('tabAdmin');
        const roleInput = document.getElementById('loginRole');
        if (tabStudent && tabAdmin && roleInput) {
            tabStudent.addEventListener('click', () => {
                tabStudent.classList.add('is-active');
                tabAdmin.classList.remove('is-active');
                roleInput.value = 'student';
                updateLoginFormRoleUI();
            });
            tabAdmin.addEventListener('click', () => {
                tabAdmin.classList.add('is-active');
                tabStudent.classList.remove('is-active');
                roleInput.value = 'admin';
                updateLoginFormRoleUI();
            });
            updateLoginFormRoleUI();
        }

        document.addEventListener('mylocker:langchange', () => {
            if (document.getElementById('loginRole')) {
                updateLoginFormRoleUI();
            }
        });

        const pwd = document.getElementById('password');
        const toggle = document.getElementById('togglePassword');
        function pwdToggleLabel() {
            if (!pwd || !toggle || typeof window.t !== 'function') {
                return;
            }
            toggle.textContent = pwd.type === 'password' ? window.t('password_show') : window.t('password_hide');
        }
        if (pwd && toggle) {
            toggle.addEventListener('click', () => {
                pwd.type = pwd.type === 'password' ? 'text' : 'password';
                pwdToggleLabel();
            });
            document.addEventListener('mylocker:langchange', pwdToggleLabel);
        }
    });
})();
