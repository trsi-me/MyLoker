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

$lockerId = isset($data['locker_id']) ? (int) $data['locker_id'] : 0;
$durationType = isset($data['duration_type']) ? (string) $data['duration_type'] : '';
$startDate = isset($data['start_date']) ? (string) $data['start_date'] : '';

if ($lockerId < 1 || !in_array($durationType, ['daily', 'weekly', 'monthly', 'term'], true)) {
    json_response(['ok' => false, 'message' => 'Invalid input']);
}

if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $startDate)) {
    json_response(['ok' => false, 'message' => 'Invalid date']);
}

$userId = (int) $_SESSION['user_id'];

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare('SELECT locker_id, status FROM lockers WHERE locker_id = ? FOR UPDATE');
    $stmt->execute([$lockerId]);
    $locker = $stmt->fetch();
    if (!$locker || $locker['status'] !== 'available') {
        $pdo->rollBack();
        json_response(['ok' => false, 'message' => 'Locker not available']);
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM reservations WHERE user_id = ? AND status = 'active'");
    $stmt->execute([$userId]);
    if ((int) $stmt->fetchColumn() > 0) {
        $pdo->rollBack();
        json_response(['ok' => false, 'message' => 'You already have an active reservation']);
    }

    $endDate = reservation_end_date($startDate, $durationType);
    $resId = generate_reservation_id($pdo);

    $ins = $pdo->prepare(
        'INSERT INTO reservations (reservation_id, user_id, locker_id, duration_type, start_date, end_date, status) VALUES (?,?,?,?,?,?,?)'
    );
    $ins->execute([$resId, $userId, $lockerId, $durationType, $startDate, $endDate, 'active']);

    $upd = $pdo->prepare("UPDATE lockers SET status = 'booked' WHERE locker_id = ?");
    $upd->execute([$lockerId]);

    $kr = $pdo->prepare(
        'INSERT INTO key_returns (reservation_id, returned_by, return_date, is_returned, notes) VALUES (?,?,NULL,?,NULL)'
    );
    $kr->execute([$resId, $userId, 0]);

    $notif = $pdo->prepare(
        'INSERT INTO notifications (user_id, title_ar, title_en, message_ar, message_en, type, is_read) VALUES (?,?,?,?,?,?,0)'
    );
    $notif->execute([
        $userId,
        'تم تأكيد الحجز',
        'Booking confirmed',
        'تم تأكيد حجز الخزانة رقم ' . $resId . '.',
        'Your reservation ' . $resId . ' is confirmed.',
        'booking',
    ]);

    $pdo->commit();
    json_response(['ok' => true, 'reservation_id' => $resId, 'end_date' => $endDate]);
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    json_response(['ok' => false, 'message' => 'Server error']);
}
?>
