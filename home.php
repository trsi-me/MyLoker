<?php
require_once __DIR__ . '/includes/auth_check.php';
require_student();

$page_title_key = 'dashboard';
$sidebar_active = 'home';
require_once __DIR__ . '/includes/icons.php';

$userId = (int) $_SESSION['user_id'];

$stmt = $pdo->prepare(
    "SELECT r.reservation_id, r.end_date, r.start_date, l.locker_number, l.location_desc, l.locker_id
     FROM reservations r
     JOIN lockers l ON l.locker_id = r.locker_id
     WHERE r.user_id = ? AND r.status = 'active'
     ORDER BY r.created_at DESC LIMIT 1"
);
$stmt->execute([$userId]);
$activeRes = $stmt->fetch();

$keyReturned = null;
if ($activeRes) {
    $k = $pdo->prepare('SELECT is_returned FROM key_returns WHERE reservation_id = ?');
    $k->execute([$activeRes['reservation_id']]);
    $kr = $k->fetch();
    $keyReturned = $kr ? (int) $kr['is_returned'] : null;
}

$mc = $pdo->prepare("SELECT COUNT(*) FROM feedback WHERE user_id = ? AND status IN ('pending','in_progress')");
$mc->execute([$userId]);
$maintCount = (int) $mc->fetchColumn();

$endTs = $activeRes ? strtotime($activeRes['end_date'] . ' 23:59:59') : false;
$startTs = $activeRes ? strtotime($activeRes['start_date'] . ' 00:00:00') : false;
$now = time();
$totalDays = ($endTs && $startTs && $endTs > $startTs) ? max(1, ceil(($endTs - $startTs) / 86400)) : 1;
$leftDays = $endTs ? max(0, ceil(($endTs - $now) / 86400)) : 0;
$pct = $totalDays > 0 ? min(100, round(($leftDays / $totalDays) * 100)) : 0;
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MyLocker — Home</title>
    <link rel="stylesheet" href="<?php echo h(BASE_PATH); ?>/assets/css/global.css">
    <link rel="stylesheet" href="<?php echo h(BASE_PATH); ?>/assets/css/dashboard.css">
</head>
<body>
<div class="app-shell">
    <?php require __DIR__ . '/includes/sidebar.php'; ?>
    <div class="app-main">
        <?php require __DIR__ . '/includes/header.php'; ?>
        <main class="main-content">
            <section class="welcome-banner">
                <h1><span data-i18n="welcome_sub">مرحباً</span>، <?php echo h($_SESSION['full_name'] ?? ''); ?>!</h1>
                <p data-i18n="welcome_sub">نظام إدارة الخزائن جاهز لك اليوم.</p>
            </section>

            <div class="stats-row">
                <div class="card stat-card">
                    <h3 data-i18n="stat_my_locker">خزانتي النشطة</h3>
                    <?php if ($activeRes): ?>
                        <div class="locker-code"><?php echo h($activeRes['locker_number']); ?></div>
                        <p class="text-muted"><?php echo h($activeRes['location_desc']); ?></p>
                    <?php else: ?>
                        <p class="text-muted" data-i18n="no_data">لا يوجد حجز نشط</p>
                    <?php endif; ?>
                </div>
                <div class="card stat-card">
                    <h3 data-i18n="stat_ends">تنتهي في</h3>
                    <?php if ($activeRes): ?>
                        <p><?php echo h($activeRes['end_date']); ?></p>
                        <div class="progress-bar"><div class="progress-bar__fill" style="width:<?php echo (int) $pct; ?>%"></div></div>
                        <p class="text-muted"><?php echo (int) $leftDays; ?> days left</p>
                    <?php else: ?>
                        <p class="text-muted">—</p>
                    <?php endif; ?>
                </div>
                <div class="card stat-card">
                    <h3 data-i18n="stat_key">حالة المفتاح</h3>
                    <?php if ($activeRes && $keyReturned !== null): ?>
                        <?php if ($keyReturned): ?>
                            <span class="badge badge--success" data-i18n="key_returned">مُعادة</span>
                        <?php else: ?>
                            <span class="badge badge--danger" data-i18n="key_not_returned">لم تُعَد</span>
                        <?php endif; ?>
                    <?php else: ?>
                        <span class="badge badge--muted">—</span>
                    <?php endif; ?>
                </div>
                <div class="card stat-card">
                    <h3 data-i18n="stat_requests">الطلبات النشطة</h3>
                    <div class="locker-code" style="font-size:22px;"><?php echo (int) $maintCount; ?></div>
                    <a href="<?php echo h(BASE_PATH); ?>/maintenance.php" data-i18n="view_details">عرض التفاصيل</a>
                </div>
            </div>

            <div class="two-col">
                <div>
                    <div class="card" style="margin-bottom:16px;">
                        <h2 class="section-title" data-i18n="map_title">خريطة الخزائن التفاعلية</h2>
                        <p class="text-muted" data-i18n="map_zone_desc">المنطقة B: الجناح العلمي — الدور الثاني</p>
                        <div id="homeLockerGrid" class="locker-grid" style="margin-top:12px;"></div>
                        <div class="legend">
                            <span class="legend__item"><span class="legend__swatch" style="background:#4CAF50"></span> <span data-i18n="legend_available">متاحة</span></span>
                            <span class="legend__item"><span class="legend__swatch" style="background:#F8BBD0"></span> <span data-i18n="legend_booked">محجوزة</span></span>
                            <span class="legend__item"><span class="legend__swatch" style="background:#E0E0E0"></span> <span data-i18n="legend_maint">صيانة</span></span>
                            <span class="legend__item"><span class="legend__swatch" style="background:#1A7EC8"></span> <span data-i18n="legend_mine">خزانتي</span></span>
                        </div>
                    </div>

                    <div class="card">
                        <h2 class="section-title" data-i18n="actions">إجراءات سريعة</h2>
                        <div class="quick-actions">
                            <a class="btn btn--secondary" href="<?php echo h(BASE_PATH); ?>/find-locker.php"><span data-i18n="quick_book">حجز جديد</span></a>
                            <a class="btn btn--secondary" href="<?php echo h(BASE_PATH); ?>/feedback.php"><span data-i18n="quick_support">الدعم</span></a>
                            <a class="btn btn--secondary" href="<?php echo h(BASE_PATH); ?>/my-reservations.php"><span data-i18n="quick_logs">السجلات</span></a>
                            <a class="btn btn--secondary" href="<?php echo h(BASE_PATH); ?>/notifications.php"><span data-i18n="quick_access">التنبيهات</span></a>
                        </div>
                    </div>
                </div>
                <div>
                    <div class="card location-card">
                        <h3 data-i18n="location_highlights">Location Highlights</h3>
                        <div class="mini-map" data-i18n="location_notes">موقع آمن</div>
                        <p class="text-muted" data-i18n="location_notes">موقع آمن ومراقب.</p>
                    </div>
                </div>
            </div>
        </main>
        <?php require __DIR__ . '/includes/footer.php'; ?>
    </div>
</div>
<script>var MYLOCKER_BASE = <?php echo json_encode(BASE_PATH, JSON_UNESCAPED_UNICODE); ?>;</script>
<script src="<?php echo h(BASE_PATH); ?>/assets/js/lang.js"></script>
<script src="<?php echo h(BASE_PATH); ?>/assets/js/locker-map.js"></script>
<script src="<?php echo h(BASE_PATH); ?>/assets/js/global.js"></script>
<script>
document.addEventListener('DOMContentLoaded', async () => {
    const grid = document.getElementById('homeLockerGrid');
    if (!window.MyLockerMap) return;
    const data = await MyLockerMap.fetchLockers({ floor: '2' });
    if (data.ok) {
        MyLockerMap.renderGrid(grid, data.lockers, {
            mineLockerId: data.mine_locker_id,
            clickable: false
        });
    }
});
</script>
</body>
</html>
