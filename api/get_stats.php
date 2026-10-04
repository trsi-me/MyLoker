<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
ensure_session();

if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    json_response(['ok' => false, 'message' => 'Forbidden'], 403);
}

$total = (int) $pdo->query('SELECT COUNT(*) FROM lockers')->fetchColumn();
$avail = (int) $pdo->query("SELECT COUNT(*) FROM lockers WHERE status = 'available'")->fetchColumn();
$booked = (int) $pdo->query("SELECT COUNT(*) FROM lockers WHERE status = 'booked'")->fetchColumn();
$maint = (int) $pdo->query("SELECT COUNT(*) FROM lockers WHERE status = 'maintenance'")->fetchColumn();

$pendingKeys = (int) $pdo->query(
    "SELECT COUNT(*) FROM key_returns kr JOIN reservations r ON r.reservation_id = kr.reservation_id WHERE kr.is_returned = 0 AND r.status = 'active'"
)->fetchColumn();

$pct = $total > 0 ? round(($avail / $total) * 100) : 0;

json_response([
    'ok' => true,
    'total_lockers' => $total,
    'available' => $avail,
    'booked' => $booked,
    'maintenance' => $maint,
    'pending_keys' => $pendingKeys,
    'available_pct' => $pct,
]);
?>
