<?php
require_once __DIR__ . '/includes/auth_check.php';
require_student();

$page_title_key = 'maintenance_report';
$sidebar_active = 'maintenance';
require_once __DIR__ . '/includes/icons.php';

$stmt = $pdo->query('SELECT locker_id, locker_number FROM lockers ORDER BY locker_number');
$lockers = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MyLocker — Maintenance</title>
    <link rel="stylesheet" href="<?php echo h(BASE_PATH); ?>/assets/css/global.css">
    <link rel="stylesheet" href="<?php echo h(BASE_PATH); ?>/assets/css/maintenance.css">
</head>
<body>
<div class="app-shell">
    <?php require __DIR__ . '/includes/sidebar.php'; ?>
    <div class="app-main">
        <?php require __DIR__ . '/includes/header.php'; ?>
        <main class="main-content">
            <h1 data-i18n="maintenance_title">طلب صيانة</h1>
            <p class="text-muted" data-i18n="maintenance_sub">صف المشكلة</p>

            <form id="maintForm" class="maintenance-form card" enctype="multipart/form-data" method="post" action="<?php echo h(BASE_PATH); ?>/api/submit_maintenance.php">
                <div class="auth-field">
                    <label data-i18n="select_locker">الخزانة</label>
                    <select class="input" name="locker_id" required>
                        <?php foreach ($lockers as $L): ?>
                        <option value="<?php echo (int) $L['locker_id']; ?>"><?php echo h($L['locker_number']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="auth-field">
                    <label data-i18n="issue_type">النوع</label>
                    <select class="input" name="issue_category">
                        <option value="broken_lock">broken_lock</option>
                        <option value="damaged_door">damaged_door</option>
                        <option value="dirty">dirty</option>
                        <option value="hinge">hinge</option>
                        <option value="other">other</option>
                    </select>
                </div>
                <div class="auth-field">
                    <label data-i18n="description">الوصف</label>
                    <textarea class="input" name="description" rows="5" required></textarea>
                </div>
                <div class="auth-field">
                    <label data-i18n="filter">الأولوية</label>
                    <select class="input priority-select" name="priority">
                        <option value="low">low</option>
                        <option value="normal" selected>normal</option>
                        <option value="urgent">urgent</option>
                    </select>
                </div>
                <div class="auth-field">
                    <label>Photo</label>
                    <input class="input" type="file" name="photo" accept="image/*">
                </div>
                <button type="submit" class="btn btn--primary" data-i18n="send_maint">إرسال</button>
            </form>
        </main>
        <?php require __DIR__ . '/includes/footer.php'; ?>
    </div>
</div>
<script>var MYLOCKER_BASE = <?php echo json_encode(BASE_PATH, JSON_UNESCAPED_UNICODE); ?>;</script>
<script src="<?php echo h(BASE_PATH); ?>/assets/js/lang.js"></script>
<script src="<?php echo h(BASE_PATH); ?>/assets/js/maintenance.js"></script>
<script src="<?php echo h(BASE_PATH); ?>/assets/js/global.js"></script>
</body>
</html>
