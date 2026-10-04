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

$reservationId = isset($_POST['reservation_id']) ? trim((string) $_POST['reservation_id']) : '';
$notes = isset($_POST['notes']) ? trim((string) $_POST['notes']) : '';

if ($reservationId === '') {
    json_response(['ok' => false, 'message' => 'Invalid']);
}

$adminId = (int) $_SESSION['user_id'];

try {
    $stmt = $pdo->prepare('SELECT return_id FROM key_returns WHERE reservation_id = ?');
    $stmt->execute([$reservationId]);
    $row = $stmt->fetch();
    if (!$row) {
        json_response(['ok' => false, 'message' => 'Not found']);
    }

    $pdo->prepare(
        'UPDATE key_returns SET is_returned = 1, return_date = NOW(), notes = COALESCE(?, notes) WHERE reservation_id = ?'
    )->execute([$notes !== '' ? $notes : null, $reservationId]);

    $uidStmt = $pdo->prepare('SELECT user_id FROM reservations WHERE reservation_id = ?');
    $uidStmt->execute([$reservationId]);
    $r = $uidStmt->fetch();
    if ($r) {
        $uid = (int) $r['user_id'];
        $n = $pdo->prepare(
            'INSERT INTO notifications (user_id, title_ar, title_en, message_ar, message_en, type, is_read) VALUES (?,?,?,?,?,?,0)'
        );
        $n->execute([
            $uid,
            'تأكيد إعادة المفتاح',
            'Key return confirmed',
            'تم تسجيل إعادة المفتاح.',
            'Key return was recorded.',
            'key_return',
        ]);
    }

    json_response(['ok' => true]);
} catch (Throwable $e) {
    json_response(['ok' => false, 'message' => 'Error']);
}
?>
