<?php
$active = $sidebar_active ?? '';
$role = $_SESSION['role'] ?? 'student';
$base = BASE_PATH;
?>
<aside class="sidebar">
    <div class="sidebar__head">
        <strong class="sidebar__portal">MyLocker</strong>
        <span class="sidebar__sub" data-i18n="locker_management">Locker Management</span>
    </div>
    <nav class="sidebar__nav">
        <?php if ($role === 'student'): ?>
        <a class="sidebar__link <?php echo $active === 'home' ? 'is-active' : ''; ?>" href="<?php echo h($base); ?>/home.php">
            <span class="sidebar__icon"><?php echo $icons['home'] ?? ''; ?></span>
            <span data-i18n="nav_home">الرئيسية</span>
        </a>
        <a class="sidebar__link <?php echo $active === 'reservations' ? 'is-active' : ''; ?>" href="<?php echo h($base); ?>/my-reservations.php">
            <span class="sidebar__icon"><?php echo $icons['list'] ?? ''; ?></span>
            <span data-i18n="my_reservations">حجوزاتي</span>
        </a>
        <a class="sidebar__link <?php echo $active === 'find' ? 'is-active' : ''; ?>" href="<?php echo h($base); ?>/find-locker.php">
            <span class="sidebar__icon"><?php echo $icons['search'] ?? ''; ?></span>
            <span data-i18n="find_locker">ابحث عن خزانة</span>
        </a>
        <a class="sidebar__link <?php echo $active === 'feedback' ? 'is-active' : ''; ?>" href="<?php echo h($base); ?>/feedback.php">
            <span class="sidebar__icon"><?php echo $icons['feedback'] ?? ''; ?></span>
            <span data-i18n="submit_feedback">إرسال بلاغ</span>
        </a>
        <a class="sidebar__link <?php echo $active === 'maintenance' ? 'is-active' : ''; ?>" href="<?php echo h($base); ?>/maintenance.php">
            <span class="sidebar__icon"><?php echo $icons['wrench'] ?? ''; ?></span>
            <span data-i18n="maintenance_report">طلب صيانة</span>
        </a>
        <a class="sidebar__link <?php echo $active === 'notifications' ? 'is-active' : ''; ?>" href="<?php echo h($base); ?>/notifications.php">
            <span class="sidebar__icon"><?php echo $icons['list'] ?? ''; ?></span>
            <span data-i18n="notifications">التنبيهات</span>
        </a>
        <?php else: ?>
        <a class="sidebar__link <?php echo $active === 'adm_dash' ? 'is-active' : ''; ?>" href="<?php echo h($base); ?>/admin/dashboard.php">
            <span class="sidebar__icon"><?php echo $icons['home'] ?? ''; ?></span>
            <span data-i18n="nav_dashboard">لوحة التحكم</span>
        </a>
        <a class="sidebar__link <?php echo $active === 'adm_lockers' ? 'is-active' : ''; ?>" href="<?php echo h($base); ?>/admin/manage_lockers.php">
            <span class="sidebar__icon"><?php echo $icons['grid'] ?? ''; ?></span>
            <span data-i18n="manage_lockers">إدارة الخزائن</span>
        </a>
        <a class="sidebar__link <?php echo $active === 'adm_users' ? 'is-active' : ''; ?>" href="<?php echo h($base); ?>/admin/manage_users.php">
            <span class="sidebar__icon"><?php echo $icons['users'] ?? ''; ?></span>
            <span data-i18n="manage_users">المستخدمون</span>
        </a>
        <a class="sidebar__link <?php echo $active === 'adm_res' ? 'is-active' : ''; ?>" href="<?php echo h($base); ?>/admin/manage_reservations.php">
            <span class="sidebar__icon"><?php echo $icons['list'] ?? ''; ?></span>
            <span data-i18n="manage_reservations">الحجوزات</span>
        </a>
        <a class="sidebar__link <?php echo $active === 'adm_maint' ? 'is-active' : ''; ?>" href="<?php echo h($base); ?>/admin/maintenance_requests.php">
            <span class="sidebar__icon"><?php echo $icons['wrench'] ?? ''; ?></span>
            <span data-i18n="maintenance_requests">طلبات الصيانة</span>
        </a>
        <a class="sidebar__link <?php echo $active === 'adm_keys' ? 'is-active' : ''; ?>" href="<?php echo h($base); ?>/key-return.php">
            <span class="sidebar__icon"><?php echo $icons['key'] ?? ''; ?></span>
            <span data-i18n="key_return">إدارة المفاتيح</span>
        </a>
        <a class="sidebar__link <?php echo $active === 'notifications' ? 'is-active' : ''; ?>" href="<?php echo h($base); ?>/notifications.php">
            <span class="sidebar__icon"><?php echo $icons['list'] ?? ''; ?></span>
            <span data-i18n="notifications">التنبيهات</span>
        </a>
        <?php endif; ?>
        <a class="sidebar__link sidebar__logout" href="<?php echo h($base); ?>/logout.php">
            <span class="sidebar__icon"><?php echo $icons['logout'] ?? ''; ?></span>
            <span data-i18n="logout">تسجيل الخروج</span>
        </a>
    </nav>
    <?php if ($role === 'student'): ?>
    <a class="btn btn--primary sidebar__cta" href="<?php echo h($base); ?>/find-locker.php" data-i18n="book_now">احجز الآن</a>
    <?php else: ?>
    <a class="btn btn--primary sidebar__cta" href="<?php echo h($base); ?>/admin/manage_lockers.php" data-i18n="manage_lockers">إدارة الخزائن</a>
    <?php endif; ?>
</aside>
