<?php
require_once __DIR__ . '/includes/auth_check.php';
require_login();

$page_title_key = 'notifications';
$sidebar_active = 'notifications';
require_once __DIR__ . '/includes/icons.php';

$userId = (int) $_SESSION['user_id'];
$type = isset($_GET['type']) ? (string) $_GET['type'] : '';

$sql = 'SELECT * FROM notifications WHERE user_id = ?';
$params = [$userId];
if ($type !== '' && in_array($type, ['booking', 'maintenance', 'key_return', 'system', 'expiry'], true)) {
    $sql .= ' AND type = ?';
    $params[] = $type;
}
$sql .= ' ORDER BY created_at DESC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$list = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MyLocker — Notifications</title>
    <link rel="stylesheet" href="<?php echo h(BASE_PATH); ?>/assets/css/global.css">
</head>
<body>
<div class="app-shell">
    <?php require __DIR__ . '/includes/sidebar.php'; ?>
    <div class="app-main">
        <?php require __DIR__ . '/includes/header.php'; ?>
        <main class="main-content">
            <h1 data-i18n="notifications">التنبيهات</h1>
            <div style="display:flex;gap:12px;margin-bottom:16px;flex-wrap:wrap;align-items:center;">
                <select class="input" id="typeFilter" style="max-width:200px;">
                    <option value=""><?php echo h('All'); ?></option>
                    <option value="booking">booking</option>
                    <option value="maintenance">maintenance</option>
                    <option value="key_return">key_return</option>
                    <option value="system">system</option>
                    <option value="expiry">expiry</option>
                </select>
                <button type="button" class="btn btn--secondary" id="markAllReadBtn" data-i18n="mark_all_read">تعليم الكل كمقروء</button>
            </div>
            <div class="card">
                <ul style="list-style:none;padding:0;margin:0;">
                    <?php foreach ($list as $n): ?>
                    <li style="padding:16px;border-bottom:1px solid var(--color-border);<?php echo !$n['is_read'] ? 'background:var(--color-primary-light);' : ''; ?>">
                        <strong><?php echo h($n['title_ar']); ?></strong>
                        <p class="text-muted" style="margin:8px 0 0;"><?php echo h($n['message_ar']); ?></p>
                        <span class="badge badge--muted"><?php echo h($n['type']); ?></span>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </main>
        <?php require __DIR__ . '/includes/footer.php'; ?>
    </div>
</div>
<script>var MYLOCKER_BASE = <?php echo json_encode(BASE_PATH, JSON_UNESCAPED_UNICODE); ?>;</script>
<script src="<?php echo h(BASE_PATH); ?>/assets/js/lang.js"></script>
<script src="<?php echo h(BASE_PATH); ?>/assets/js/notifications.js"></script>
<script src="<?php echo h(BASE_PATH); ?>/assets/js/global.js"></script>
<script>
document.getElementById('typeFilter').addEventListener('change', function () {
    const v = this.value;
    const url = new URL(window.location.href);
    if (v) url.searchParams.set('type', v); else url.searchParams.delete('type');
    window.location = url.toString();
});
document.getElementById('typeFilter').value = <?php echo json_encode($type, JSON_UNESCAPED_UNICODE); ?>;
</script>
</body>
</html>
