<?php
require_once __DIR__ . '/functions.php';
if (!isset($page_title_key)) {
    $page_title_key = 'app_name';
}
ensure_session();
$uid = $_SESSION['user_id'] ?? null;
$role = $_SESSION['role'] ?? '';
$uname = $_SESSION['full_name'] ?? '';
$isAdminBar = ($role === 'admin');
?>
<header class="topbar">
    <div class="topbar__brand">
        <a href="<?php echo h(BASE_PATH . ($isAdminBar ? '/admin/dashboard.php' : '/home.php')); ?>" class="topbar__logo-link">
            <img src="<?php echo h(BASE_PATH); ?>/assets/images/Logo.png" alt="MyLocker" class="topbar__logo" width="140" height="auto">
        </a>
    </div>
    <div class="topbar__actions">
        <button type="button" class="btn btn--ghost btn--lang" id="langToggle" data-i18n="lang_toggle">AR / EN</button>
        <div class="topbar__notif-wrap">
            <button type="button" class="topbar__notif-btn" id="notifBellBtn" aria-label="Notifications">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <path d="M12 22c1.1 0 2-.9 2-2h-4c0 1.1.89 2 2 2zm6-6v-5c0-3.07-1.64-5.64-4.5-6.32V4c0-.83-.67-1.5-1.5-1.5s-1.5.67-1.5 1.5v.68C7.63 5.36 6 7.92 6 11v5l-2 2v1h16v-1l-2-2z" fill="currentColor"/>
                </svg>
                <span class="topbar__notif-badge" id="notifCountBadge" style="display:none;">0</span>
            </button>
            <div class="notif-dropdown" id="notifDropdown" hidden>
                <div class="notif-dropdown__head">
                    <span data-i18n="notifications">التنبيهات</span>
                    <a href="<?php echo h(BASE_PATH); ?>/notifications.php" data-i18n="view_all">عرض الكل</a>
                </div>
                <ul class="notif-dropdown__list" id="notifDropdownList"></ul>
            </div>
        </div>
        <div class="topbar__user">
            <?php if ($isAdminBar): ?>
                <span class="badge badge--admin" data-i18n="admin_badge">ADMIN</span>
            <?php endif; ?>
            <span class="topbar__user-name"><?php echo h($uname); ?></span>
            <a class="btn btn--ghost topbar__logout" href="<?php echo h(BASE_PATH); ?>/logout.php" data-i18n="logout">تسجيل الخروج</a>
            <span class="topbar__avatar" aria-hidden="true">
                <svg width="36" height="36" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="20" cy="20" r="20" fill="#E6F1FB"/>
                    <path d="M20 20a6 6 0 100-12 6 6 0 000 12zm0 3c-4.67 0-8.5 2.24-8.5 5v1h17v-1c0-2.76-3.83-5-8.5-5z" fill="#1A7EC8"/>
                </svg>
            </span>
        </div>
    </div>
</header>
