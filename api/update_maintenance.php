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

$id = isset($data['maintenance_id']) ? (int) $data['maintenance_id'] : 0;
$assignedTo = isset($data['assigned_to']) ? (int) $data['assigned_to'] : 0;
$status = isset($data['status']) ? (string) $data['status'] : '';

if ($id < 1) {
    json_response(['ok' => false, 'message' => 'Invalid']);
}

try {
    if ($assignedTo > 0) {
        $pdo->prepare('UPDATE maintenance SET assigned_to = ? WHERE maintenance_id = ?')->execute([$assignedTo, $id]);
    }
    if ($status !== '' && in_array($status, ['pending', 'in_progress', 'resolved'], true)) {
        if ($status === 'resolved') {
            $pdo->prepare(
                'UPDATE maintenance SET status = ?, completed_date = CURDATE() WHERE maintenance_id = ?'
            )->execute([$status, $id]);
        } else {
            $pdo->prepare('UPDATE maintenance SET status = ? WHERE maintenance_id = ?')->execute([$status, $id]);
        }
    }
    json_response(['ok' => true]);
} catch (Throwable $e) {
    json_response(['ok' => false, 'message' => 'Error']);
}
?>
