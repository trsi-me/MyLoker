<?php
require_once __DIR__ . '/includes/auth_check.php';
require_student();

$page_title_key = 'find_locker';
$sidebar_active = 'find';
require_once __DIR__ . '/includes/icons.php';
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MyLocker — Find</title>
    <link rel="stylesheet" href="<?php echo h(BASE_PATH); ?>/assets/css/global.css">
    <link rel="stylesheet" href="<?php echo h(BASE_PATH); ?>/assets/css/locker-map.css">
</head>
<body>
<div class="app-shell">
    <?php require __DIR__ . '/includes/sidebar.php'; ?>
    <div class="app-main">
        <?php require __DIR__ . '/includes/header.php'; ?>
        <main class="main-content">
            <div class="find-hero__dots" aria-hidden="true"><span></span><span></span><span></span></div>
            <h1 data-i18n="book_locker_title">احجز خزانة</h1>
            <p class="text-muted" data-i18n="book_locker_sub">اختر الفلاتر ثم خزانة متاحة.</p>

            <div class="filter-bar">
                <div>
                    <label class="text-muted" data-i18n="filter_floor">الدور</label>
                    <select class="input" id="filterFloor">
                        <option value="" data-i18n="filter_floor">الكل</option>
                        <option value="1" data-i18n="floor_1">الأول</option>
                        <option value="2" data-i18n="floor_2">الثاني</option>
                        <option value="3" data-i18n="floor_3">الثالث</option>
                    </select>
                </div>
                <div>
                    <label class="text-muted" data-i18n="filter_size">الحجم</label>
                    <select class="input" id="filterSize">
                        <option value="">All</option>
                        <option value="small" data-i18n="size_small">صغيرة</option>
                        <option value="medium" data-i18n="size_medium">متوسطة</option>
                        <option value="large" data-i18n="size_large">كبيرة</option>
                    </select>
                </div>
                <div>
                    <label class="text-muted" data-i18n="duration">المدة</label>
                    <select class="input" id="durationType">
                        <option value="term" data-i18n="dur_term">فصل</option>
                        <option value="monthly" data-i18n="dur_monthly">شهري</option>
                        <option value="weekly" data-i18n="dur_weekly">أسبوعي</option>
                        <option value="daily" data-i18n="dur_daily">يومي</option>
                    </select>
                </div>
                <button type="button" class="btn btn--secondary" id="applyFilterBtn" data-i18n="filter_extra">فلتر</button>
            </div>

            <div class="find-layout">
                <div>
                    <div class="card map-section">
                        <div class="map-section__head">
                            <h2 data-i18n="section_title">Section B-2</h2>
                            <div class="legend">
                                <span class="legend__item"><span class="legend__swatch" style="background:#4CAF50"></span> <span data-i18n="available">متاحة</span></span>
                                <span class="legend__item"><span class="legend__swatch" style="background:#F8BBD0"></span> <span data-i18n="occupied">Occupied</span></span>
                                <span class="legend__item"><span class="legend__swatch" style="background:#E0E0E0"></span> <span data-i18n="maintenance">صيانة</span></span>
                            </div>
                        </div>
                        <div id="findLockerGrid" class="locker-grid"></div>
                    </div>

                    <div class="terms-banner">
                        <div>
                            <strong data-i18n="terms_title">الشروط والأحكام</strong>
                            <p style="margin:4px 0 0;font-size:14px;opacity:.95;">Terms summary for locker use.</p>
                        </div>
                        <button type="button" class="btn btn--secondary" id="openPolicyBtn" data-i18n="terms_btn">عرض السياسة</button>
                    </div>
                </div>

                <aside class="card booking-panel" id="bookingPanel">
                    <span class="badge badge--success selected-badge" id="selectedBadge" style="display:none;" data-i18n="selected">SELECTED</span>
                    <div class="locker-title" id="panelTitle">—</div>
                    <p class="text-muted" id="panelLoc">—</p>
                    <p><span data-i18n="status">الحالة</span>: <span id="panelStatus">—</span></p>

                    <div class="duration-cards" id="durationCards">
                        <div class="duration-card is-selected" data-dur="term">
                            <strong data-i18n="price_term">فصل — 150</strong>
                            <span data-i18n="dur_term">فصل</span>
                        </div>
                        <div class="duration-card" data-dur="monthly">
                            <strong data-i18n="price_month">شهر — 40</strong>
                            <span data-i18n="dur_monthly">شهري</span>
                        </div>
                        <div class="duration-card" data-dur="daily">
                            <strong data-i18n="price_day">يوم — 5</strong>
                            <span data-i18n="dur_daily">يومي</span>
                        </div>
                    </div>

                    <div class="auth-field">
                        <label for="startDate" data-i18n="start_date">تاريخ البداية</label>
                        <input class="input" type="date" id="startDate" name="start_date" value="<?php echo date('Y-m-d'); ?>">
                    </div>

                    <button type="button" class="btn btn--primary btn--block" id="confirmBookBtn" data-i18n="confirm_booking">تأكيد الحجز</button>
                    <p class="text-muted" style="margin-top:8px;" data-i18n="digital_key_note">سيُصدر مفتاح رقمي فوراً</p>
                </aside>
            </div>
        </main>
        <?php require __DIR__ . '/includes/footer.php'; ?>
    </div>
</div>
<div id="policyModal" class="modal-backdrop" role="dialog" aria-hidden="true">
    <div class="modal">
        <h3 data-i18n="policy_modal_title">السياسة</h3>
        <p class="text-muted">Full policy text placeholder. Respect university rules for locker use, key return, and maintenance reporting.</p>
        <button type="button" class="btn btn--primary" id="closePolicyBtn" data-i18n="policy_modal_close">إغلاق</button>
    </div>
</div>
<script>var MYLOCKER_BASE = <?php echo json_encode(BASE_PATH, JSON_UNESCAPED_UNICODE); ?>;</script>
<script src="<?php echo h(BASE_PATH); ?>/assets/js/lang.js"></script>
<script src="<?php echo h(BASE_PATH); ?>/assets/js/locker-map.js"></script>
<script src="<?php echo h(BASE_PATH); ?>/assets/js/global.js"></script>
<script>
(function () {
    let selected = null;
    let durationType = 'term';

    const grid = document.getElementById('findLockerGrid');
    const floor = document.getElementById('filterFloor');
    const size = document.getElementById('filterSize');
    const applyBtn = document.getElementById('applyFilterBtn');

    async function load() {
        const params = {};
        if (floor.value) params.floor = floor.value;
        if (size.value) params.size = size.value;
        const data = await MyLockerMap.fetchLockers(params);
        if (!data.ok) return;
        MyLockerMap.renderGrid(grid, data.lockers, {
            mineLockerId: data.mine_locker_id,
            clickable: true,
            onSelect: function (L) {
                selected = L;
                document.getElementById('selectedBadge').style.display = 'inline-block';
                document.getElementById('panelTitle').textContent = 'Locker #' + L.locker_number;
                document.getElementById('panelLoc').textContent = L.location_desc || '';
                document.getElementById('panelStatus').textContent = L.status;
                Array.from(grid.querySelectorAll('.locker-cell')).forEach((c) => c.classList.remove('locker-cell--selected'));
                const cell = grid.querySelector('[data-id="' + L.locker_id + '"]');
                if (cell) cell.classList.add('locker-cell--selected');
            }
        });
    }

    applyBtn.addEventListener('click', load);
    document.addEventListener('DOMContentLoaded', load);

    document.querySelectorAll('.duration-card').forEach((c) => {
        c.addEventListener('click', () => {
            document.querySelectorAll('.duration-card').forEach((x) => x.classList.remove('is-selected'));
            c.classList.add('is-selected');
            durationType = c.getAttribute('data-dur');
            document.getElementById('durationType').value = durationType;
        });
    });

    document.getElementById('confirmBookBtn').addEventListener('click', async () => {
        if (!selected) {
            alert(typeof t === 'function' ? t('no_data') : 'Select locker');
            return;
        }
        const start = document.getElementById('startDate').value;
        const body = JSON.stringify({
            locker_id: selected.locker_id,
            duration_type: durationType,
            start_date: start
        });
        const res = await fetch(MYLOCKER_BASE + '/api/reserve_locker.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: body,
            credentials: 'same-origin'
        });
        const data = await res.json();
        if (data.ok) {
            alert('OK: ' + data.reservation_id);
            window.location.href = MYLOCKER_BASE + '/my-reservations.php';
        } else {
            alert(data.message || 'Error');
        }
    });

    const pol = document.getElementById('policyModal');
    document.getElementById('openPolicyBtn').addEventListener('click', () => { pol.classList.add('is-open'); });
    document.getElementById('closePolicyBtn').addEventListener('click', () => { pol.classList.remove('is-open'); });
})();
</script>
</body>
</html>
