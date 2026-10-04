<?php
require_once __DIR__ . '/../includes/auth_check.php';
require_admin();

$page_title_key = 'maintenance_requests';
$sidebar_active = 'adm_maint';
require_once __DIR__ . '/../includes/icons.php';

$rows = $pdo->query(
    "SELECT m.*, l.locker_number FROM maintenance m JOIN lockers l ON l.locker_id = m.locker_id ORDER BY m.created_at DESC"
)->fetchAll();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>MyLocker — Maintenance</title>
    <link rel="stylesheet" href="<?php echo h(BASE_PATH); ?>/assets/css/global.css">
</head>
<body>
<div class="app-shell">
    <?php require __DIR__ . '/../includes/sidebar.php'; ?>
    <div class="app-main">
        <?php require __DIR__ . '/../includes/header.php'; ?>
        <main class="main-content">
            <h1 data-i18n="maintenance_requests">طلبات الصيانة</h1>
            <div class="card table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th data-i18n="col_locker">Locker</th>
                            <th>Issue</th>
                            <th data-i18n="filter_status">Status</th>
                            <th data-i18n="actions">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rows as $r): ?>
                        <tr>
                            <td><?php echo (int) $r['maintenance_id']; ?></td>
                            <td><?php echo h($r['locker_number']); ?></td>
                            <td><?php echo h($r['issue_category']); ?></td>
                            <td><?php echo h($r['status']); ?></td>
                            <td>
                                <button type="button" class="btn btn--secondary btn-resolved" data-id="<?php echo (int) $r['maintenance_id']; ?>">Resolve</button>
                            </td>
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
<script>
document.querySelectorAll('.btn-resolved').forEach((b) => {
    b.addEventListener('click', async () => {
        const id = b.getAttribute('data-id');
        const res = await fetch(MYLOCKER_BASE + '/api/update_maintenance.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ maintenance_id: parseInt(id, 10), status: 'resolved' }),
            credentials: 'same-origin'
        });
        const j = await res.json();
        if (j.ok) location.reload();
    });
});
</script>
</body>
</html>
