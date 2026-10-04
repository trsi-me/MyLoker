<?php
require_once __DIR__ . '/../includes/auth_check.php';
require_admin();

$page_title_key = 'manage_users';
$sidebar_active = 'adm_users';
require_once __DIR__ . '/../includes/icons.php';

$rows = $pdo->query('SELECT user_id, student_id, full_name, email, role, created_at FROM users ORDER BY user_id')->fetchAll();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>MyLocker — Users</title>
    <link rel="stylesheet" href="<?php echo h(BASE_PATH); ?>/assets/css/global.css">
</head>
<body>
<div class="app-shell">
    <?php require __DIR__ . '/../includes/sidebar.php'; ?>
    <div class="app-main">
        <?php require __DIR__ . '/../includes/header.php'; ?>
        <main class="main-content">
            <h1 data-i18n="manage_users">المستخدمون</h1>
            <div class="card table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th data-i18n="student_id">Student ID</th>
                            <th data-i18n="full_name">Name</th>
                            <th data-i18n="email">Email</th>
                            <th>Role</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rows as $r): ?>
                        <tr>
                            <td><?php echo (int) $r['user_id']; ?></td>
                            <td><?php echo h($r['student_id']); ?></td>
                            <td><?php echo h($r['full_name']); ?></td>
                            <td><?php echo h($r['email']); ?></td>
                            <td><?php echo h($r['role']); ?></td>
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
