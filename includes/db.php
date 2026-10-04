<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'mylocker_db');
define('DB_CHARSET', 'utf8mb4');

if (!defined('BASE_PATH')) {
    $scriptName = str_replace('\\', '/', (string) ($_SERVER['SCRIPT_NAME'] ?? ''));
    $dir = $scriptName !== '' ? dirname($scriptName) : '';
    if ($dir === '/' || $dir === '.' || $dir === '\\') {
        $parts = [];
    } else {
        $parts = array_values(array_filter(explode('/', $dir), static function ($p) {
            return $p !== '';
        }));
    }
    while (!empty($parts) && in_array(end($parts), ['admin', 'api', 'includes'], true)) {
        array_pop($parts);
    }
    $base = implode('/', $parts);
    $base = $base === '' ? '' : '/' . $base;
    define('BASE_PATH', $base);
}

$pdo = new PDO(
    'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET,
    DB_USER,
    DB_PASS,
    [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]
);
?>
