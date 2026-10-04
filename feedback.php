<?php
require_once __DIR__ . '/includes/auth_check.php';
require_student();

$page_title_key = 'submit_feedback';
$sidebar_active = 'feedback';
require_once __DIR__ . '/includes/icons.php';

$stmt = $pdo->query('SELECT locker_id, locker_number FROM lockers ORDER BY locker_number');
$lockers = $stmt->fetchAll();

$recent = $pdo->prepare(
    'SELECT f.feedback_id, f.category, f.message, f.status, l.locker_number FROM feedback f JOIN lockers l ON l.locker_id = f.locker_id WHERE f.user_id = ? ORDER BY f.submitted_at DESC LIMIT 10'
);
$recent->execute([(int) $_SESSION['user_id']]);
$recentRows = $recent->fetchAll();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MyLocker — Feedback</title>
    <link rel="stylesheet" href="<?php echo h(BASE_PATH); ?>/assets/css/global.css">
    <link rel="stylesheet" href="<?php echo h(BASE_PATH); ?>/assets/css/feedback.css">
</head>
<body>
<div class="app-shell">
    <?php require __DIR__ . '/includes/sidebar.php'; ?>
    <div class="app-main">
        <?php require __DIR__ . '/includes/header.php'; ?>
        <main class="main-content">
            <div class="feedback-hero">
                <h1 data-i18n="feedback_page_title">الإبلاغ والتغذية الراجعة</h1>
                <p class="text-muted" data-i18n="feedback_page_sub">وصف مختصر</p>
            </div>

            <div class="feedback-tabs">
                <button type="button" class="feedback-tabs__btn is-active" id="fbTabMaint" data-i18n="tab_maint">صيانة</button>
                <button type="button" class="feedback-tabs__btn" id="fbTabGeneral" data-i18n="tab_general">تغذية راجعة</button>
            </div>

            <div class="feedback-layout">
                <div class="card">
                    <form id="formMaint" enctype="multipart/form-data" method="post" action="<?php echo h(BASE_PATH); ?>/api/submit_feedback.php">
                        <input type="hidden" name="category" value="maintenance">
                        <div id="fbPanelMaint">
                            <div class="auth-field">
                                <label data-i18n="select_locker">الخزانة</label>
                                <select class="input" name="locker_id" required>
                                    <?php foreach ($lockers as $L): ?>
                                    <option value="<?php echo (int) $L['locker_id']; ?>"><?php echo h($L['locker_number']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="auth-field">
                                <label data-i18n="issue_type">نوع المشكلة</label>
                                <select class="input" name="issue_category">
                                    <option value="broken_lock" data-i18n="issue_broken_lock">قفل</option>
                                    <option value="damaged_door" data-i18n="issue_door">باب</option>
                                    <option value="dirty" data-i18n="issue_dirty">نظافة</option>
                                    <option value="hinge" data-i18n="issue_hinge">مفصلة</option>
                                    <option value="other" data-i18n="issue_other">أخرى</option>
                                </select>
                            </div>
                            <div class="auth-field">
                                <label data-i18n="description">الوصف</label>
                                <textarea class="input" name="message" rows="4" required></textarea>
                            </div>
                            <div class="upload-zone" id="uploadZone">
                                <input type="file" name="photo" id="maintPhoto" accept="image/*">
                                <p data-i18n="upload_hint">اسحب الصورة</p>
                            </div>
                            <button type="submit" class="btn btn--primary btn--block" data-i18n="send_maint">إرسال</button>
                        </div>
                    </form>

                    <form id="formGeneral" method="post" action="<?php echo h(BASE_PATH); ?>/api/submit_feedback.php" style="display:none;">
                        <input type="hidden" name="category" value="general">
                        <div id="fbPanelGeneral">
                            <div class="auth-field">
                                <label data-i18n="select_locker">الخزانة</label>
                                <select class="input" name="locker_id" required>
                                    <?php foreach ($lockers as $L): ?>
                                    <option value="<?php echo (int) $L['locker_id']; ?>"><?php echo h($L['locker_number']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="auth-field">
                                <label data-i18n="feedback_message">الرسالة</label>
                                <textarea class="input" name="message" rows="4" required></textarea>
                            </div>
                            <input type="hidden" name="rating" id="ratingValue" value="5">
                            <div class="stars" aria-label="rating">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                <button type="button" class="is-on">★</button>
                                <?php endfor; ?>
                            </div>
                            <button type="submit" class="btn btn--primary btn--block" data-i18n="send_feedback">إرسال</button>
                        </div>
                    </form>
                </div>
                <aside>
                    <div class="card sla-card">
                        <h3 data-i18n="sla_title">وقت الاستجابة</h3>
                        <p class="text-muted" data-i18n="sla_text">2–5 أيام</p>
                    </div>
                    <div class="card">
                        <h3 data-i18n="recent_submissions">آخر التقديمات</h3>
                        <div class="table-wrap">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th data-i18n="col_ticket">تذكرة</th>
                                        <th data-i18n="col_subject">الحالة</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recentRows as $rr): ?>
                                    <tr>
                                        <td><?php echo h($rr['feedback_id']); ?></td>
                                        <td><?php echo h($rr['status']); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </aside>
            </div>
        </main>
        <?php require __DIR__ . '/includes/footer.php'; ?>
    </div>
</div>
<script>var MYLOCKER_BASE = <?php echo json_encode(BASE_PATH, JSON_UNESCAPED_UNICODE); ?>;</script>
<script src="<?php echo h(BASE_PATH); ?>/assets/js/lang.js"></script>
<script src="<?php echo h(BASE_PATH); ?>/assets/js/feedback.js"></script>
<script src="<?php echo h(BASE_PATH); ?>/assets/js/global.js"></script>
<script>
document.getElementById('fbTabGeneral').addEventListener('click', () => {
    document.getElementById('fbTabGeneral').classList.add('is-active');
    document.getElementById('fbTabMaint').classList.remove('is-active');
    document.getElementById('formMaint').style.display = 'none';
    document.getElementById('formGeneral').style.display = 'block';
});
document.getElementById('fbTabMaint').addEventListener('click', () => {
    document.getElementById('fbTabMaint').classList.add('is-active');
    document.getElementById('fbTabGeneral').classList.remove('is-active');
    document.getElementById('formMaint').style.display = 'block';
    document.getElementById('formGeneral').style.display = 'none';
});

async function postForm(ev, form) {
    ev.preventDefault();
    const fd = new FormData(form);
    const res = await fetch(form.action, { method: 'POST', body: fd, credentials: 'same-origin' });
    const data = await res.json();
    if (data.ok) { alert('OK'); form.reset(); } else { alert(data.message || 'Error'); }
}
document.getElementById('formMaint').addEventListener('submit', (e) => postForm(e, document.getElementById('formMaint')));
document.getElementById('formGeneral').addEventListener('submit', (e) => postForm(e, document.getElementById('formGeneral')));
</script>
</body>
</html>
