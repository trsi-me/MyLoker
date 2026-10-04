<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
ensure_session();

if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'student') {
    json_response(['ok' => false, 'message' => 'Unauthorized'], 401);
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    json_response(['ok' => false, 'message' => 'Method not allowed'], 405);
}

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
if (!is_array($data)) {
    $data = $_POST;
}

$reservationId = isset($data['reservation_id']) ? (string) $data['reservation_id'] : '';
if ($reservationId === '') {
    json_response(['ok' => false, 'message' => 'Invalid']);
}

$userId = (int) $_SESSION['user_id'];

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare(
        "SELECT reservation_id, user_id, locker_id, status FROM reservations WHERE reservation_id = ? FOR UPDATE"
    );
    $stmt->execute([$reservationId]);
    $res = $stmt->fetch();
    if (!$res || (int) $res['user_id'] !== $userId) {
        $pdo->rollBack();
        json_response(['ok' => false, 'message' => 'Not found']);
    }
    if ($res['status'] !== 'active') {
        $pdo->rollBack();
        json_response(['ok' => false, 'message' => 'Not active']);
    }

    $pdo->prepare("UPDATE reservations SET status = 'cancelled' WHERE reservation_id = ?")->execute([$reservationId]);
    $pdo->prepare("UPDATE lockers SET status = 'available' WHERE locker_id = ?")->execute([(int) $res['locker_id']]);

    $pdo->commit();
    json_response(['ok' => true]);
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    json_response(['ok' => false, 'message' => 'Error']);
}
?>
