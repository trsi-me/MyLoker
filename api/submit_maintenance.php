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

$userId = (int) $_SESSION['user_id'];
$lockerId = isset($_POST['locker_id']) ? (int) $_POST['locker_id'] : 0;
$issue = isset($_POST['issue_category']) ? (string) $_POST['issue_category'] : '';
$description = isset($_POST['description']) ? trim((string) $_POST['description']) : '';
$priority = isset($_POST['priority']) ? (string) $_POST['priority'] : 'normal';

if ($lockerId < 1 || $description === '') {
    json_response(['ok' => false, 'message' => 'Missing fields']);
}

if (!in_array($issue, ['broken_lock', 'damaged_door', 'dirty', 'hinge', 'other'], true)) {
    $issue = 'other';
}
if (!in_array($priority, ['urgent', 'normal', 'low'], true)) {
    $priority = 'normal';
}

$photoPath = null;
if (!empty($_FILES['photo']['name']) && is_uploaded_file($_FILES['photo']['tmp_name'])) {
    $dir = __DIR__ . '/../uploads/maintenance/';
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    $ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
    $safe = 'm_' . bin2hex(random_bytes(8)) . '.' . preg_replace('/[^a-zA-Z0-9]/', '', $ext);
    if (move_uploaded_file($_FILES['photo']['tmp_name'], $dir . $safe)) {
        $photoPath = 'uploads/maintenance/' . $safe;
    }
}

try {
    $pdo->beginTransaction();
    $fid = generate_feedback_id($pdo, 'MT');
    $stmt = $pdo->prepare(
        'INSERT INTO feedback (feedback_id, user_id, locker_id, category, message, rating, status) VALUES (?,?,?,?,?,?,?)'
    );
    $stmt->execute([$fid, $userId, $lockerId, 'maintenance', $description, null, 'pending']);

    $m = $pdo->prepare(
        'INSERT INTO maintenance (locker_id, feedback_id, issue_category, description, photo_path, priority, status) VALUES (?,?,?,?,?,?,?)'
    );
    $m->execute([$lockerId, $fid, $issue, $description, $photoPath, $priority, 'pending']);

    $notif = $pdo->prepare(
        'INSERT INTO notifications (user_id, title_ar, title_en, message_ar, message_en, type, is_read) VALUES (?,?,?,?,?,?,0)'
    );
    $notif->execute([
        $userId,
        'طلب صيانة مسجل',
        'Maintenance logged',
        'تم تسجيل طلب الصيانة.',
        'Your maintenance request was logged.',
        'maintenance',
    ]);

    $pdo->commit();
    json_response(['ok' => true, 'maintenance_id' => $pdo->lastInsertId(), 'feedback_id' => $fid]);
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    json_response(['ok' => false, 'message' => 'Error']);
}
?>
