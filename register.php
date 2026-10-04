<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
ensure_session();

if (!empty($_SESSION['user_id'])) {
    header('Location: ' . BASE_PATH . '/home.php');
    exit;
}

$errors = [];
$ok = false;
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $fullName = isset($_POST['full_name']) ? trim((string) $_POST['full_name']) : '';
    $studentId = isset($_POST['student_id']) ? trim((string) $_POST['student_id']) : '';
    $email = isset($_POST['email']) ? trim((string) $_POST['email']) : '';
    $phone = isset($_POST['phone']) ? trim((string) $_POST['phone']) : '';
    $password = isset($_POST['password']) ? (string) $_POST['password'] : '';
    $password2 = isset($_POST['password2']) ? (string) $_POST['password2'] : '';

    if ($fullName === '' || mb_strlen($fullName) < 3) {
        $errors[] = 'name';
    }
    if (!preg_match('/^[A-Za-z0-9_-]{4,20}$/', $studentId)) {
        $errors[] = 'student_id';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'email';
    }
    if ($password === '' || strlen($password) < 8) {
        $errors[] = 'password';
    }
    if ($password !== $password2) {
        $errors[] = 'match';
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare('SELECT 1 FROM users WHERE student_id = ? OR email = ? LIMIT 1');
        $stmt->execute([$studentId, $email]);
        if ($stmt->fetch()) {
            $errors[] = 'duplicate';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $ins = $pdo->prepare(
                'INSERT INTO users (student_id, full_name, email, password_hash, phone, role) VALUES (?,?,?,?,?,?)'
            );
            $ins->execute([$studentId, $fullName, $email, $hash, $phone, 'student']);
            $ok = true;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MyLocker — Register</title>
    <link rel="stylesheet" href="<?php echo h(BASE_PATH); ?>/assets/css/global.css">
    <link rel="stylesheet" href="<?php echo h(BASE_PATH); ?>/assets/css/auth.css">
</head>
<body class="auth-page">
    <div class="auth-page__center" style="padding-top:48px;">
        <img src="<?php echo h(BASE_PATH); ?>/assets/images/Logo.png" alt="MyLocker" class="auth-page__logo" width="140">
        <h1 class="auth-page__title-ar" data-i18n="register_title">إنشاء حساب</h1>
        <div class="auth-card" style="max-width:480px;">
            <div class="auth-card__body">
                <?php if ($ok): ?>
                    <p data-i18n="success_saved">تم الحفظ بنجاح</p>
                    <p><a href="<?php echo h(BASE_PATH); ?>/index.php" data-i18n="login_btn">تسجيل الدخول</a></p>
                <?php else: ?>
                    <?php if (!empty($errors)): ?>
                        <p style="color:var(--color-danger);margin-bottom:12px;">تحقق من الحقول / Check fields</p>
                    <?php endif; ?>
                    <form method="post" action="">
                        <div class="auth-field">
                            <label for="full_name" data-i18n="full_name">الاسم الكامل</label>
                            <input class="input" id="full_name" name="full_name" required value="<?php echo isset($_POST['full_name']) ? h((string) $_POST['full_name']) : ''; ?>">
                        </div>
                        <div class="auth-field">
                            <label for="student_id" data-i18n="student_id">رقم الطالب</label>
                            <input class="input" id="student_id" name="student_id" required value="<?php echo isset($_POST['student_id']) ? h((string) $_POST['student_id']) : ''; ?>">
                        </div>
                        <div class="auth-field">
                            <label for="email" data-i18n="email">البريد</label>
                            <input class="input" type="email" id="email" name="email" required value="<?php echo isset($_POST['email']) ? h((string) $_POST['email']) : ''; ?>">
                        </div>
                        <div class="auth-field">
                            <label for="phone" data-i18n="phone">الجوال</label>
                            <input class="input" id="phone" name="phone" value="<?php echo isset($_POST['phone']) ? h((string) $_POST['phone']) : ''; ?>">
                        </div>
                        <div class="auth-field">
                            <label for="password" data-i18n="password">كلمة المرور</label>
                            <input class="input" type="password" id="password" name="password" required minlength="8">
                        </div>
                        <div class="auth-field">
                            <label for="password2" data-i18n="confirm_password">تأكيد</label>
                            <input class="input" type="password" id="password2" name="password2" required minlength="8">
                        </div>
                        <button type="submit" class="btn btn--primary btn--block" data-i18n="register_btn">إنشاء الحساب</button>
                    </form>
                <?php endif; ?>
                <p style="text-align:center;margin-top:16px;"><a href="<?php echo h(BASE_PATH); ?>/index.php" data-i18n="login_title">تسجيل الدخول</a></p>
            </div>
        </div>
    </div>
    <script>var MYLOCKER_BASE = <?php echo json_encode(BASE_PATH, JSON_UNESCAPED_UNICODE); ?>;</script>
    <script src="<?php echo h(BASE_PATH); ?>/assets/js/lang.js"></script>
</body>
</html>
