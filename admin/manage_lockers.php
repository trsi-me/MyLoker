<?php
require_once __DIR__ . '/../includes/auth_check.php';
require_admin();

$page_title_key = 'manage_lockers';
$sidebar_active = 'adm_lockers';
require_once __DIR__ . '/../includes/icons.php';

$rows = $pdo->query('SELECT * FROM lockers ORDER BY locker_number')->fetchAll();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>MyLocker — Lockers</title>
    <link rel="stylesheet" href="<?php echo h(BASE_PATH); ?>/assets/css/global.css">
</head>
<body>
<div class="app-shell">
    <?php require __DIR__ . '/../includes/sidebar.php'; ?>
    <div class="app-main">
        <?php require __DIR__ . '/../includes/header.php'; ?>
        <main class="main-content">
            <h1 data-i18n="manage_lockers">إدارة الخزائن</h1>
            <div class="card table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Number</th>
                            <th>Floor</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rows as $r): ?>
                        <tr>
                            <td><?php echo (int) $r['locker_id']; ?></td>
                            <td><?php echo h($r['locker_number']); ?></td>
                            <td><?php echo h($r['floor_level']); ?></td>
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
