<?php
require_once __DIR__ . '/../includes/auth_check.php';
require_admin();

$page_title_key = 'manage_reservations';
$sidebar_active = 'adm_res';
require_once __DIR__ . '/../includes/icons.php';

$rows = $pdo->query(
    "SELECT r.*, u.full_name, u.student_id, l.locker_number FROM reservations r
     JOIN users u ON u.user_id = r.user_id
     JOIN lockers l ON l.locker_id = r.locker_id
     ORDER BY r.created_at DESC LIMIT 100"
)->fetchAll();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>MyLocker — Reservations</title>
    <link rel="stylesheet" href="<?php echo h(BASE_PATH); ?>/assets/css/global.css">
</head>
<body>
<div class="app-shell">
    <?php require __DIR__ . '/../includes/sidebar.php'; ?>
    <div class="app-main">
        <?php require __DIR__ . '/../includes/header.php'; ?>
        <main class="main-content">
            <h1 data-i18n="manage_reservations">الحجوزات</h1>
            <div class="card table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th data-i18n="student">Student</th>
                            <th data-i18n="col_locker">Locker</th>
                            <th data-i18n="col_duration">Duration</th>
                            <th data-i18n="filter_status">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rows as $r): ?>
                        <tr>
                            <td><?php echo h($r['reservation_id']); ?></td>
                            <td><?php echo h($r['full_name']); ?></td>
                            <td><?php echo h($r['locker_number']); ?></td>
                            <td><?php echo h($r['duration_type']); ?></td>
                            <td><?php echo h($r['status']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
        <?php require __DIR__ . '/../includes/footer.php'; ?>
    </div>
</div>
<script>var MYLOCKER_BASE = <?php echo json_encode(BASE_PATH, JSON_UNESCAPED_UNICODE); ?>;</script>
<script src="<?php echo h(BASE_PATH); ?>/assets/js/lang.js"></script>
<script src="<?php echo h(BASE_PATH); ?>/assets/js/global.js"></script>
</body>
</html>
