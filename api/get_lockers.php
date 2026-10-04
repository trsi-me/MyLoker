<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
ensure_session();

$floor = isset($_GET['floor']) ? trim((string) $_GET['floor']) : '';
$size = isset($_GET['size']) ? trim((string) $_GET['size']) : '';
$zone = isset($_GET['zone']) ? trim((string) $_GET['zone']) : '';

$sql = 'SELECT locker_id, locker_number, floor_level, zone, size, status, location_desc FROM lockers WHERE 1=1';
$params = [];
if ($floor !== '') {
    $sql .= ' AND floor_level = ?';
    $params[] = $floor;
}
if ($size !== '' && in_array($size, ['small', 'medium', 'large'], true)) {
    $sql .= ' AND size = ?';
    $params[] = $size;
}
if ($zone !== '') {
    $sql .= ' AND zone = ?';
    $params[] = $zone;
}
$sql .= ' ORDER BY locker_number ASC';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();

$mineLockerId = null;
if (!empty($_SESSION['user_id'])) {
    $u = (int) $_SESSION['user_id'];
    $st = $pdo->prepare(
        "SELECT locker_id FROM reservations WHERE user_id = ? AND status = 'active' ORDER BY created_at DESC LIMIT 1"
    );
    $st->execute([$u]);
    $r = $st->fetch();
    if ($r) {
        $mineLockerId = (int) $r['locker_id'];
    }
}

json_response(['ok' => true, 'lockers' => $rows, 'mine_locker_id' => $mineLockerId]);
?>
