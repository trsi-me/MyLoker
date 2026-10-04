<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
ensure_session();

if (empty($_SESSION['user_id'])) {
    json_response(['ok' => false, 'message' => 'Unauthorized'], 401);
}

$userId = (int) $_SESSION['user_id'];

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['mark_all'])) {
    $pdo->prepare('UPDATE notifications SET is_read = 1 WHERE user_id = ?')->execute([$userId]);
    json_response(['ok' => true]);
}

if (isset($_GET['count']) && $_GET['count'] === '1') {
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0');
    $stmt->execute([$userId]);
    $unread = (int) $stmt->fetchColumn();
    json_response(['ok' => true, 'unread' => $unread]);
}

$limit = isset($_GET['limit']) ? max(1, min(50, (int) $_GET['limit'])) : 20;
$type = isset($_GET['type']) ? trim((string) $_GET['type']) : '';

$sql = 'SELECT notif_id, title_ar, title_en, message_ar, message_en, type, is_read, created_at FROM notifications WHERE user_id = ?';
$params = [$userId];
if ($type !== '' && in_array($type, ['booking', 'maintenance', 'key_return', 'system', 'expiry'], true)) {
    $sql .= ' AND type = ?';
    $params[] = $type;
}
$sql .= ' ORDER BY created_at DESC LIMIT ' . (int) $limit;

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$items = $stmt->fetchAll();

json_response(['ok' => true, 'items' => $items]);
?>
