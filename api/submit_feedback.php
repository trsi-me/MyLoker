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
$category = isset($_POST['category']) ? (string) $_POST['category'] : '';

if (!in_array($category, ['maintenance', 'general'], true)) {
    json_response(['ok' => false, 'message' => 'Invalid category']);
}

$lockerId = isset($_POST['locker_id']) ? (int) $_POST['locker_id'] : 0;
$message = isset($_POST['message']) ? trim((string) $_POST['message']) : '';
if ($lockerId < 1 || $message === '') {
    json_response(['ok' => false, 'message' => 'Missing fields']);
}

$rating = null;
if ($category === 'general' && isset($_POST['rating']) && $_POST['rating'] !== '') {
    $rating = max(1, min(5, (int) $_POST['rating']));
}

$issue = isset($_POST['issue_category']) ? (string) $_POST['issue_category'] : 'other';
if ($category === 'maintenance' && !in_array($issue, ['broken_lock', 'damaged_door', 'dirty', 'hinge', 'other'], true)) {
    $issue = 'other';
}

$photoPath = null;
if (!empty($_FILES['photo']['name']) && is_uploaded_file($_FILES['photo']['tmp_name'])) {
    $dir = __DIR__ . '/../uploads/feedback/';
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    $ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
    $safe = 'fb_' . bin2hex(random_bytes(8)) . '.' . preg_replace('/[^a-zA-Z0-9]/', '', $ext);
    $target = $dir . $safe;
    if (move_uploaded_file($_FILES['photo']['tmp_name'], $target)) {
        $photoPath = 'uploads/feedback/' . $safe;
    }
}

try {
    $prefix = $category === 'maintenance' ? 'MT' : 'FB';
    $fid = generate_feedback_id($pdo, $prefix);

    $stmt = $pdo->prepare(
        'INSERT INTO feedback (feedback_id, user_id, locker_id, category, message, rating, status) VALUES (?,?,?,?,?,?,?)'
    );
    $stmt->execute([$fid, $userId, $lockerId, $category, $message, $rating, 'pending']);

    if ($category === 'maintenance') {
        $m = $pdo->prepare(
            'INSERT INTO maintenance (locker_id, feedback_id, issue_category, description, priority, status) VALUES (?,?,?,?,?,?)'
        );
        $m->execute([$lockerId, $fid, $issue, $message, 'normal', 'pending']);
    }

    $notif = $pdo->prepare(
        'INSERT INTO notifications (user_id, title_ar, title_en, message_ar, message_en, type, is_read) VALUES (?,?,?,?,?,?,0)'
    );
    $notif->execute([
        $userId,
        'تم استلام البلاغ',
        'Feedback received',
        'تم تسجيل تذكرتك ' . $fid . '.',
        'Your ticket ' . $fid . ' was registered.',
        'system',
    ]);

    json_response(['ok' => true, 'feedback_id' => $fid]);
} catch (Throwable $e) {
    json_response(['ok' => false, 'message' => 'Error']);
}
?>
