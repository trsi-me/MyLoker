<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
ensure_session();

if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    json_response(['ok' => false, 'message' => 'Forbidden'], 403);
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    json_response(['ok' => false, 'message' => 'Method not allowed'], 405);
}

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
if (!is_array($data)) {
    $data = $_POST;
}

$lockerId = isset($data['locker_id']) ? (int) $data['locker_id'] : 0;
$status = isset($data['status']) ? (string) $data['status'] : '';

if ($lockerId < 1 || !in_array($status, ['available', 'booked', 'maintenance'], true)) {
    json_response(['ok' => false, 'message' => 'Invalid']);
}

try {
    $pdo->prepare('UPDATE lockers SET status = ? WHERE locker_id = ?')->execute([$status, $lockerId]);
    json_response(['ok' => true]);
} catch (Throwable $e) {
    json_response(['ok' => false, 'message' => 'Error']);
}
?>
