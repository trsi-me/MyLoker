<?php
require_once __DIR__ . '/includes/auth_check.php';
require_admin();

$page_title_key = 'key_return';
$sidebar_active = 'adm_keys';
require_once __DIR__ . '/includes/icons.php';

$page = isset($_GET['p']) ? max(1, (int) $_GET['p']) : 1;
$perPage = 10;
$offset = ($page - 1) * $perPage;

$countSql = "SELECT COUNT(*) FROM key_returns kr JOIN reservations r ON r.reservation_id = kr.reservation_id WHERE r.status = 'active'";
$totalRows = (int) $pdo->query($countSql)->fetchColumn();
$totalPages = max(1, (int) ceil($totalRows / $perPage));

$sql = "SELECT kr.*, r.user_id, r.locker_id, r.end_date, u.full_name, u.student_id, l.locker_number
        FROM key_returns kr
        JOIN reservations r ON r.reservation_id = kr.reservation_id
        JOIN users u ON u.user_id = r.user_id
        JOIN lockers l ON l.locker_id = r.locker_id
        WHERE r.status = 'active'
        ORDER BY kr.return_id DESC
        LIMIT " . (int) $perPage . " OFFSET " . (int) $offset;
$rows = $pdo->query($sql)->fetchAll();

$activeKeys = (int) $pdo->query(
    "SELECT COUNT(*) FROM key_returns kr JOIN reservations r ON r.reservation_id = kr.reservation_id WHERE r.status = 'active' AND kr.is_returned = 0"
)->fetchColumn();
$returnedToday = (int) $pdo->query(
    "SELECT COUNT(*) FROM key_returns WHERE is_returned = 1 AND DATE(return_date) = CURDATE()"
)->fetchColumn();
$overdue = (int) $pdo->query(
    "SELECT COUNT(*) FROM key_returns kr JOIN reservations r ON r.reservation_id = kr.reservation_id
     WHERE r.status = 'active' AND kr.is_returned = 0 AND r.end_date < CURDATE()"
)->fetchColumn();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MyLocker — Key return</title>
    <link rel="stylesheet" href="<?php echo h(BASE_PATH); ?>/assets/css/global.css">
    <link rel="stylesheet" href="<?php echo h(BASE_PATH); ?>/assets/css/key-return.css">
</head>
<body>
<div class="app-shell">
    <?php require __DIR__ . '/includes/sidebar.php'; ?>
    <div class="app-main">
        <?php require __DIR__ . '/includes/header.php'; ?>
        <main class="main-content">
            <h1 data-i18n="key_mgmt_title">إدارة المفاتيح</h1>
            <div class="toolbar">
                <input type="search" class="input" placeholder="Search" id="keySearch" style="max-width:260px;">
                <button type="button" class="btn btn--secondary" data-i18n="filter">فلتر</button>
                <button type="button" class="btn btn--ghost" data-i18n="export">تصدير</button>
            </div>

            <div class="key-stats">
                <div class="card">
                    <h3 data-i18n="stat_active_keys">إجمالي المفاتيح النشطة</h3>
                    <div class="big-num"><?php echo (int) $activeKeys; ?></div>
                    <div class="sub text-success" data-i18n="week_growth">+12%</div>
                </div>
                <div class="card">
                    <h3 data-i18n="stat_returned_today">مُعادة اليوم</h3>
                    <div class="big-num"><?php echo (int) $returnedToday; ?></div>
                    <div class="sub" data-i18n="compliance">85%</div>
                </div>
                <div class="card">
                    <h3 data-i18n="stat_overdue">متأخرة</h3>
                    <div class="big-num" style="color:var(--color-danger);"><?php echo (int) $overdue; ?></div>
                    <div class="sub text-danger" data-i18n="needs_action">يتطلب إجراء</div>
                </div>
            </div>

            <div class="card table-wrap">
                <h2 data-i18n="key_activity">النشاط</h2>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th data-i18n="col_res_id">الحجز</th>
                            <th data-i18n="student">الطالب</th>
                            <th data-i18n="col_locker">الخزانة</th>
                            <th data-i18n="col_end">الانتهاء</th>
                            <th data-i18n="filter_status">الحالة</th>
                            <th data-i18n="col_actions">إجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rows as $row): ?>
                        <tr>
                            <td><?php echo h($row['reservation_id']); ?></td>
                            <td><?php echo h($row['full_name']); ?> (<?php echo h($row['student_id']); ?>)</td>
                            <td><?php echo h($row['locker_number']); ?></td>
                            <td><?php echo h($row['end_date']); ?></td>
                            <td>
                                <?php if ((int) $row['is_returned']): ?>
                                    <span class="badge badge--success" data-i18n="returned">مُعادة</span>
                                <?php elseif ($row['end_date'] < date('Y-m-d')): ?>
                                    <span class="badge badge--danger" data-i18n="overdue">متأخر</span>
                                <?php else: ?>
                                    <span class="badge badge--warning" data-i18n="active">نشط</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!(int) $row['is_returned']): ?>
                                <button type="button" class="btn btn--primary" data-mark-return="1" data-reservation-id="<?php echo h($row['reservation_id']); ?>" data-i18n="mark_returned">Mark returned</button>
                                <?php else: ?>
                                <span class="text-muted" title="view">View</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="pagination">
                <?php if ($page > 1): ?>
                <a href="?p=<?php echo (int) ($page - 1); ?>" data-i18n="pagination_prev">Prev</a>
                <?php endif; ?>
                <span class="is-current"><?php echo (int) $page; ?></span>
                <?php if ($page < $totalPages): ?>
                <a href="?p=<?php echo (int) ($page + 1); ?>" data-i18n="pagination_next">Next</a>
                <?php endif; ?>
            </div>

            <div class="alert-banner">
                <span data-i18n="urgent_banner">إشعار عاجل</span>
                <button type="button" class="btn btn--secondary" data-i18n="manage_reminders">Manage</button>
            </div>
        </main>
        <?php require __DIR__ . '/includes/footer.php'; ?>
    </div>
</div>
<script>var MYLOCKER_BASE = <?php echo json_encode(BASE_PATH, JSON_UNESCAPED_UNICODE); ?>;</script>
<script src="<?php echo h(BASE_PATH); ?>/assets/js/lang.js"></script>
<script src="<?php echo h(BASE_PATH); ?>/assets/js/key-return.js"></script>
<script src="<?php echo h(BASE_PATH); ?>/assets/js/global.js"></script>
</body>
</html>
