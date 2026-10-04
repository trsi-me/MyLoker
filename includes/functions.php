<?php
function h(?string $s): string
{
    return htmlspecialchars((string) $s, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

function ensure_session(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

function login_url(): string
{
    $script = $_SERVER['SCRIPT_NAME'] ?? '';
    if (strpos($script, '/admin/') !== false || strpos($script, '\\admin\\') !== false) {
        return BASE_PATH . '/index.php';
    }
    return BASE_PATH . '/index.php';
}

function require_login(): void
{
    ensure_session();
    if (empty($_SESSION['user_id'])) {
        header('Location: ' . login_url());
        exit;
    }
}

function require_admin(): void
{
    require_login();
    if (($_SESSION['role'] ?? '') !== 'admin') {
        header('Location: ' . BASE_PATH . '/home.php');
        exit;
    }
}

function require_student(): void
{
    require_login();
    if (($_SESSION['role'] ?? '') !== 'student') {
        header('Location: ' . BASE_PATH . '/admin/dashboard.php');
        exit;
    }
}

function json_response(array $data, int $code = 200): void
{
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function generate_reservation_id(PDO $pdo): string
{
    $stmt = $pdo->query("SELECT reservation_id FROM reservations WHERE reservation_id LIKE 'RES-%' ORDER BY reservation_id DESC LIMIT 1");
    $row = $stmt->fetch();
    $n = 1;
    if ($row && preg_match('/RES-(\d+)/', $row['reservation_id'], $m)) {
        $n = (int) $m[1] + 1;
    }
    return 'RES-' . str_pad((string) $n, 4, '0', STR_PAD_LEFT);
}

function generate_feedback_id(PDO $pdo, string $prefix): string
{
    $stmt = $pdo->prepare('SELECT feedback_id FROM feedback WHERE feedback_id LIKE ? ORDER BY feedback_id DESC LIMIT 1');
    $stmt->execute([$prefix . '-%']);
    $row = $stmt->fetch();
    $n = 1;
    if ($row && preg_match('/' . preg_quote($prefix, '/') . '-(\d+)/', $row['feedback_id'], $m)) {
        $n = (int) $m[1] + 1;
    }
    return $prefix . '-' . str_pad((string) $n, 4, '0', STR_PAD_LEFT);
}

function reservation_end_date(string $startYmd, string $durationType): string
{
    $ts = strtotime($startYmd . ' 12:00:00');
    if ($ts === false) {
        return $startYmd;
    }
    switch ($durationType) {
        case 'daily':
            return date('Y-m-d', strtotime('+1 day', $ts));
        case 'weekly':
            return date('Y-m-d', strtotime('+7 days', $ts));
        case 'monthly':
            return date('Y-m-d', strtotime('+1 month', $ts));
        case 'term':
        default:
            return '2026-06-30';
    }
}
?>
