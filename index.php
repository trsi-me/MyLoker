<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
ensure_session();

$error = '';
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $studentId = isset($_POST['student_id']) ? trim((string) $_POST['student_id']) : '';
    $password = isset($_POST['password']) ? (string) $_POST['password'] : '';
    $expectedRole = isset($_POST['role']) && $_POST['role'] === 'admin' ? 'admin' : 'student';

    if ($studentId === '' || $password === '') {
        $error = 'empty';
    } else {
        $stmt = $pdo->prepare('SELECT user_id, student_id, full_name, password_hash, role FROM users WHERE student_id = ? LIMIT 1');
        $stmt->execute([$studentId]);
        $user = $stmt->fetch();
        if ($user && password_verify($password, $user['password_hash'])) {
            if ($user['role'] !== $expectedRole) {
                $error = 'role_mismatch';
            } else {
                $_SESSION['user_id'] = (int) $user['user_id'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['student_id'] = $user['student_id'];
                $pdo->prepare('UPDATE users SET last_login = NOW() WHERE user_id = ?')->execute([(int) $user['user_id']]);
                if ($user['role'] === 'admin') {
                    header('Location: ' . BASE_PATH . '/admin/dashboard.php');
                } else {
                    header('Location: ' . BASE_PATH . '/home.php');
                }
                exit;
            }
        } else {
            $error = 'invalid';
        }
    }
}

$errorKey = '';
if ($error === 'invalid') {
    $errorKey = 'err_login_invalid';
} elseif ($error === 'role_mismatch') {
    $errorKey = 'err_login_role';
} elseif ($error === 'empty') {
    $errorKey = 'err_login_empty';
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MyLocker — Login</title>
    <link rel="stylesheet" href="<?php echo h(BASE_PATH); ?>/assets/css/global.css">
    <link rel="stylesheet" href="<?php echo h(BASE_PATH); ?>/assets/css/auth.css">
</head>
<body class="auth-page" data-page-title-key="login_title">
    <div class="auth-page__topbar">
        <button type="button" class="btn btn--ghost btn--lang js-lang-toggle" data-i18n="lang_toggle">عربي / EN</button>
    </div>
    <div class="auth-page__center">
        <img src="<?php echo h(BASE_PATH); ?>/assets/images/Logo.png" alt="MyLocker" class="auth-page__logo" width="200" height="auto">
        <h1 class="auth-page__title-ar" data-i18n="login_hero_primary">خزانتك الذكية، في أي وقت وأي مكان</h1>
        <p class="auth-page__title-en" data-i18n="login_hero_secondary">Your Smart Locker, Anytime, Anywhere</p>
        <div class="auth-page__dots" aria-hidden="true"><span></span><span></span><span></span></div>

        <div class="auth-card">
            <div class="auth-tabs">
                <button type="button" class="auth-tabs__btn is-active" id="tabStudent" data-i18n="tab_student">طالب</button>
                <button type="button" class="auth-tabs__btn" id="tabAdmin" data-i18n="tab_admin">مشرف</button>
            </div>
            <div class="auth-card__body">
                <?php if ($errorKey !== ''): ?>
                    <p class="text-muted auth-page__error" style="color:var(--color-danger);margin-bottom:12px;" data-i18n="<?php echo h($errorKey); ?>"> </p>
                <?php endif; ?>
                <form method="post" action="">
                    <input type="hidden" name="role" id="loginRole" value="student">
                    <div class="auth-field">
                        <label for="student_id" data-i18n="student_id">رقم الطالب</label>
                        <div class="auth-field__wrap">
                            <span class="auth-field__icon">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="#6B7280"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
                            </span>
                            <input class="input" type="text" id="student_id" name="student_id" autocomplete="username" data-i18n-placeholder="student_id" placeholder="رقم الطالب" required>
                        </div>
                    </div>
                    <div class="auth-field">
                        <label for="password" data-i18n="password">كلمة المرور</label>
                        <div class="auth-field__wrap">
                            <span class="auth-field__icon">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="#6B7280"><path d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zm-6 9c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2zm3.1-9H8.9V6c0-1.71 1.39-3.1 3.1-3.1 1.71 0 3.1 1.39 3.1 3.1v2z"/></svg>
                            </span>
                            <input class="input" type="password" id="password" name="password" autocomplete="current-password" data-i18n-placeholder="password" placeholder="كلمة المرور" required>
                            <button type="button" class="auth-field__toggle" id="togglePassword">إظهار</button>
                        </div>
                    </div>
                    <div class="auth-row">
                        <label><input type="checkbox" name="remember" value="1"> <span data-i18n="remember_me">تذكرني</span></label>
                        <a href="#" data-i18n="forgot_password">نسيت كلمة المرور؟</a>
                    </div>
                    <button type="submit" class="btn btn--primary btn--block" data-i18n="login_btn">تسجيل الدخول ←</button>
                </form>
            </div>
        </div>
        <p class="auth-page__register" id="authRegisterRow"><a href="<?php echo h(BASE_PATH); ?>/register.php" data-i18n="new_student">طالب جديد؟ سجّل الآن</a></p>
    </div>
    <footer class="site-footer site-footer--auth">
        <div class="site-footer__inner">
            <p class="site-footer__copy"><span data-i18n="footer_univ">Prince Sattam bin Abdulaziz University</span> — <span data-i18n="footer_rights">All rights reserved</span></p>
            <div class="site-footer__links">
                <a href="#" data-i18n="footer_support">Support</a>
                <a href="#" data-i18n="footer_security">Security policy</a>
                <button type="button" class="btn btn--link js-lang-toggle" id="footerLangToggle" data-i18n="lang_toggle">AR / EN</button>
            </div>
        </div>
    </footer>
    <script>var MYLOCKER_BASE = <?php echo json_encode(BASE_PATH, JSON_UNESCAPED_UNICODE); ?>;</script>
    <script src="<?php echo h(BASE_PATH); ?>/assets/js/lang.js"></script>
    <script src="<?php echo h(BASE_PATH); ?>/assets/js/auth.js"></script>
</body>
</html>
