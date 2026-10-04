<?php
require_once __DIR__ . '/includes/auth_check.php';
require_student();

$page_title_key = 'my_reservations';
$sidebar_active = 'reservations';
require_once __DIR__ . '/includes/icons.php';

$userId = (int) $_SESSION['user_id'];
$filter = isset($_GET['st']) ? (string) $_GET['st'] : '';
$sql = "SELECT r.*, l.locker_number FROM reservations r JOIN lockers l ON l.locker_id = r.locker_id WHERE r.user_id = ?";
$params = [$userId];
if ($filter !== '' && in_array($filter, ['active', 'expired', 'cancelled'], true)) {
    $sql .= ' AND r.status = ?';
    $params[] = $filter;
}
$sql .= ' ORDER BY r.created_at DESC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();

function dur_label(string $d): string
{
    $m = ['daily' => 'daily', 'weekly' => 'weekly', 'monthly' => 'monthly', 'term' => 'term'];
    return $m[$d] ?? $d;
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MyLocker — Reservations</title>
    <link rel="stylesheet" href="<?php echo h(BASE_PATH); ?>/assets/css/global.css">
    <link rel="stylesheet" href="<?php echo h(BASE_PATH); ?>/assets/css/reservations.css">
</head>
<body>
<div class="app-shell">
    <?php require __DIR__ . '/includes/sidebar.php'; ?>
    <div class="app-main">
        <?php require __DIR__ . '/includes/header.php'; ?>
        <main class="main-content">
            <div class="page-head">
                <h1 data-i18n="reservations_title">حجوزاتي</h1>
            </div>
            <div class="filters-inline">
                <label data-i18n="filter_status">الحالة</label>
                <select class="input" id="statusFilter" style="max-width:200px;">
                    <option value="" data-i18n="filter_status">الكل</option>
                    <option value="active" data-i18n="status_active">نشطة</option>
                    <option value="expired" data-i18n="status_expired">منتهية</option>
                    <option value="cancelled" data-i18n="status_cancelled">ملغاة</option>
                </select>
            </div>

            <div class="card table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th data-i18n="col_res_id">رقم الحجز</th>
                            <th data-i18n="col_locker">الخزانة</th>
                            <th data-i18n="col_duration">المدة</th>
                            <th data-i18n="col_start">البداية</th>
                            <th data-i18n="col_end">النهاية</th>
                            <th data-i18n="filter_status">الحالة</th>
                            <th data-i18n="col_actions">إجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rows as $r): ?>
                        <tr>
                            <td><?php echo h($r['reservation_id']); ?></td>
                            <td><?php echo h($r['locker_number']); ?></td>
                            <td><?php echo h(dur_label($r['duration_type'])); ?></td>
                            <td><?php echo h($r['start_date']); ?></td>
                            <td><?php echo h($r['end_date']); ?></td>
                            <td><span class="badge badge--muted"><?php echo h($r['status']); ?></span></td>
                            <td>
                                <button type="button" class="btn btn--ghost btn-view" data-detail="<?php echo h(json_encode($r, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP)); ?>" data-i18n="action_view">عرض</button>
                                <?php if ($r['status'] === 'active'): ?>
                                <button type="button" class="btn btn--danger btn-cancel" data-id="<?php echo h($r['reservation_id']); ?>" data-i18n="action_cancel">إلغاء</button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
        <?php require __DIR__ . '/includes/footer.php'; ?>
    </div>
</div>
<div id="detailModal" class="modal-backdrop">
    <div class="modal">
        <h3 data-i18n="action_view">تفاصيل</h3>
        <div id="detailModalBody"></div>
        <button type="button" class="btn btn--primary" id="detailModalClose" data-i18n="policy_modal_close">إغلاق</button>
    </div>
</div>
<script>var MYLOCKER_BASE = <?php echo json_encode(BASE_PATH, JSON_UNESCAPED_UNICODE); ?>;</script>
<script src="<?php echo h(BASE_PATH); ?>/assets/js/lang.js"></script>
<script src="<?php echo h(BASE_PATH); ?>/assets/js/global.js"></script>
<script>
document.getElementById('statusFilter').addEventListener('change', function () {
    const v = this.value;
    const url = new URL(window.location.href);
    if (v) url.searchParams.set('st', v); else url.searchParams.delete('st');
    window.location = url.toString();
});
document.getElementById('statusFilter').value = <?php echo json_encode($filter, JSON_UNESCAPED_UNICODE); ?>;

document.querySelectorAll('.btn-view').forEach((btn) => {
    btn.addEventListener('click', () => {
        const raw = btn.getAttribute('data-detail');
        let j;
        try { j = JSON.parse(raw); } catch (e) { return; }
        document.getElementById('detailModalBody').innerHTML = '<pre style="white-space:pre-wrap;font-size:14px;">' +
            Object.keys(j).map((k) => k + ': ' + j[k]).join('\n') + '</pre>';
        document.getElementById('detailModal').classList.add('is-open');
    });
});
document.getElementById('detailModalClose').addEventListener('click', () => {
    document.getElementById('detailModal').classList.remove('is-open');
});

document.querySelectorAll('.btn-cancel').forEach((btn) => {
    btn.addEventListener('click', async () => {
        if (!confirm(typeof t === 'function' ? t('confirm_cancel') : 'Cancel?')) return;
        const id = btn.getAttribute('data-id');
        const res = await fetch(MYLOCKER_BASE + '/api/cancel_reservation.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ reservation_id: id }),
            credentials: 'same-origin'
        });
        const data = await res.json();
        if (data.ok) location.reload(); else alert(data.message || 'Error');
    });
});
</script>
</body>
</html>
