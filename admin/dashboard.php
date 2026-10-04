<?php
require_once __DIR__ . '/../includes/auth_check.php';
require_admin();

$page_title_key = 'admin_dash';
$sidebar_active = 'adm_dash';
require_once __DIR__ . '/../includes/icons.php';

$total = (int) $pdo->query('SELECT COUNT(*) FROM lockers')->fetchColumn();
$avail = (int) $pdo->query("SELECT COUNT(*) FROM lockers WHERE status = 'available'")->fetchColumn();
$booked = (int) $pdo->query("SELECT COUNT(*) FROM lockers WHERE status = 'booked'")->fetchColumn();
$maint = (int) $pdo->query("SELECT COUNT(*) FROM lockers WHERE status = 'maintenance'")->fetchColumn();
$pendingKeys = (int) $pdo->query(
    "SELECT COUNT(*) FROM key_returns kr JOIN reservations r ON r.reservation_id = kr.reservation_id WHERE kr.is_returned = 0 AND r.status = 'active'"
)->fetchColumn();
$pct = $total > 0 ? round(($avail / $total) * 100) : 0;

$recent = $pdo->query(
    "SELECT r.*, u.full_name, u.student_id, l.locker_number FROM reservations r
     JOIN users u ON u.user_id = r.user_id
     JOIN lockers l ON l.locker_id = r.locker_id
     ORDER BY r.created_at DESC LIMIT 8"
)->fetchAll();

$maintRows = $pdo->query(
    "SELECT m.*, l.locker_number FROM maintenance m JOIN lockers l ON l.locker_id = m.locker_id WHERE m.status IN ('pending','in_progress') ORDER BY m.priority DESC, m.created_at DESC LIMIT 5"
)->fetchAll();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MyLocker — Admin</title>
    <link rel="stylesheet" href="<?php echo h(BASE_PATH); ?>/assets/css/global.css">
    <link rel="stylesheet" href="<?php echo h(BASE_PATH); ?>/assets/css/admin.css">
</head>
<body>
<div class="app-shell">
    <?php require __DIR__ . '/../includes/sidebar.php'; ?>
    <div class="app-main">
        <?php require __DIR__ . '/../includes/header.php'; ?>
        <main class="main-content">
            <h1 data-i18n="admin_dash">لوحة المشرف</h1>

            <div class="admin-stats" id="adminStats">
                <div class="card">
                    <h3 data-i18n="stat_total_lockers">إجمالي الخزائن</h3>
                    <div class="big-num"><?php echo (int) $total; ?></div>
                </div>
                <div class="card">
                    <h3 data-i18n="stat_available">متاحة</h3>
                    <div class="big-num"><?php echo (int) $avail; ?></div>
                    <span class="badge badge--success"><?php echo (int) $pct; ?>%</span>
                </div>
                <div class="card">
                    <h3 data-i18n="stat_booked">محجوزة</h3>
                    <div class="big-num"><?php echo (int) $booked; ?></div>
                </div>
                <div class="card">
                    <h3 data-i18n="stat_maint">صيانة</h3>
                    <div class="big-num"><?php echo (int) $maint; ?></div>
                </div>
                <div class="card">
                    <h3 data-i18n="stat_keys_pending">مفاتيح معلقة</h3>
                    <div class="big-num"><?php echo (int) $pendingKeys; ?></div>
                </div>
            </div>

            <div class="admin-floor">
                <div class="card floor-plan-card">
                    <h2 data-i18n="floor_plan">مخطط الأدوار</h2>
                    <div style="display:flex;gap:12px;margin-bottom:12px;">
                        <select class="input" id="admFloor">
                            <option value="1">1</option>
                            <option value="2" selected>2</option>
                            <option value="3">3</option>
                        </select>
                        <select class="input" id="admZone">
                            <option value="B">B</option>
                        </select>
                    </div>
                    <div id="adminLockerGrid" class="locker-grid"></div>
                    <div class="aisle-gap" data-i18n="aisle_label">Aisle Corridor B-2</div>
                </div>
                <aside>
                    <h2 data-i18n="maintenance_requests">صيانة</h2>
                    <?php foreach ($maintRows as $mr): ?>
                    <div class="card maint-card">
                        <div class="maint-card__head">
                            <strong><?php echo h($mr['locker_number']); ?></strong>
                            <?php if ($mr['priority'] === 'urgent'): ?>
                            <span class="badge badge--danger">urgent</span>
                            <?php else: ?>
                            <span class="badge badge--warning">pending</span>
                            <?php endif; ?>
                        </div>
                        <p><?php echo h(mb_substr($mr['description'], 0, 120)); ?></p>
                        <button type="button" class="btn btn--primary btn-assign" data-id="<?php echo (int) $mr['maintenance_id']; ?>" data-i18n="assign">Assign</button>
                    </div>
                    <?php endforeach; ?>
                </aside>
            </div>

            <div class="card" style="margin-top:24px;">
                <div class="table-toolbar">
                    <h2 data-i18n="recent_res">آخر الحجوزات</h2>
                    <div>
                        <input type="search" class="input" placeholder="Search" style="max-width:200px;">
                        <button type="button" class="btn btn--secondary" data-i18n="filter">فلتر</button>
                    </div>
                </div>
                <div class="table-wrap">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th data-i18n="student">الطالب</th>
                                <th data-i18n="student_id">الهوية</th>
                                <th data-i18n="col_locker">الخزانة</th>
                                <th data-i18n="col_duration">المدة</th>
                                <th data-i18n="filter_status">الحالة</th>
                                <th data-i18n="col_actions">إجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent as $r): ?>
                            <tr>
                                <td><?php echo h($r['full_name']); ?></td>
                                <td><?php echo h($r['student_id']); ?></td>
                                <td><?php echo h($r['locker_number']); ?></td>
                                <td><?php echo h($r['duration_type']); ?></td>
                                <td><?php echo h($r['status']); ?></td>
                                <td>
                                    <div class="dropdown-menu">
                                        <button type="button" class="dropdown-menu__btn" aria-expanded="false">⋯</button>
                                        <div class="dropdown-menu__panel">
                                            <a href="<?php echo h(BASE_PATH); ?>/admin/manage_reservations.php">View</a>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
        <?php require __DIR__ . '/../includes/footer.php'; ?>
    </div>
</div>
<script>var MYLOCKER_BASE = <?php echo json_encode(BASE_PATH, JSON_UNESCAPED_UNICODE); ?>;</script>
<script src="<?php echo h(BASE_PATH); ?>/assets/js/lang.js"></script>
<script src="<?php echo h(BASE_PATH); ?>/assets/js/locker-map.js"></script>
<script src="<?php echo h(BASE_PATH); ?>/assets/js/global.js"></script>
<script>
document.addEventListener('DOMContentLoaded', async () => {
    const grid = document.getElementById('adminLockerGrid');
    async function loadFloor() {
        const f = document.getElementById('admFloor').value;
        const data = await MyLockerMap.fetchLockers({ floor: f });
        if (data.ok) {
            MyLockerMap.renderGrid(grid, data.lockers, { clickable: true, clickAny: true, mineLockerId: null, onSelect: async (L) => {
                const st = prompt('Status: available / booked / maintenance', L.status);
                if (!st) return;
                const res = await fetch(MYLOCKER_BASE + '/api/update_locker_status.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ locker_id: L.locker_id, status: st }),
                    credentials: 'same-origin'
                });
                const j = await res.json();
                if (j.ok) loadFloor();
            }});
        }
    }
    document.getElementById('admFloor').addEventListener('change', loadFloor);
    loadFloor();

    document.querySelectorAll('.btn-assign').forEach((b) => {
        b.addEventListener('click', async () => {
            const id = b.getAttribute('data-id');
            const uid = prompt('Assign to user_id (admin id = 1)');
            if (!uid) return;
            const res = await fetch(MYLOCKER_BASE + '/api/update_maintenance.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ maintenance_id: parseInt(id, 10), assigned_to: parseInt(uid, 10), status: 'in_progress' }),
                credentials: 'same-origin'
            });
            const j = await res.json();
            if (j.ok) location.reload();
        });
    });

    document.querySelectorAll('.dropdown-menu__btn').forEach((btn) => {
        btn.addEventListener('click', (e) => {
            e.stopPropagation();
            btn.closest('.dropdown-menu').classList.toggle('is-open');
        });
    });
});
</script>
</body>
</html>
