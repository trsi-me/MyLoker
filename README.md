# MyLocker (خزانتي)

## 1 ما هو المشروع

تطبيق ويب لإدارة خزائن الطلاب: البحث، الحجز، المفتاح، البلاغ، الصيانة، والإشعارات، مع لوحة مشرف. الاسم في الواجهة MyLocker (خزانتي). نص التذييل في `assets/js/lang.js` يذكر جامعة الأمير سطام بن عبدالعزيز، الكلية التطبيقية.

## 2 لماذا وُجد

الملف السابق يصف محاكاة مكتب الخزائن حتى تُعرف الخزانة المتاحة والحجز والمفتاح وطلب الصيانة في قاعدة واحدة. الجداول `lockers` و`reservations` و`key_returns` و`maintenance` و`notifications` تطابق هذا الغرض.

## 3 المستخدمون

دوران في `users.role`: `student` و`admin`. الدخول من `index.php` برقم `student_id` لا بالبريد، مع تبويب يخفي حقلاً `role` بقيمة `student` أو `admin`. إن نجحت كلمة المرور واختلف الدور عن التبويب فالخطأ `role_mismatch`.

البذرة: مشرف `ADMIN001` والبريد `admin@psau.edu.sa`، وطلاب أرقام `20241001` حتى `20241005`. كلمات المرور مذكورة في تعليق `database.sql` والملف السابق ومخزنة في `password_hash`. لا تُعاد هنا.

رابط «نسيت كلمة المرور؟» في `index.php` يشير إلى `#`. لا صفحة استعادة في الملفات الحالية.

## 4 القدرات

- تسجيل طالب من `register.php` (كلمة 8 أحرف على الأقل، عدم تكرار الرقم والبريد).
- لوحة طالب `home.php` وخريطة `find-locker.php` وحجوزات `my-reservations.php`.
- بلاغ `feedback.php` وصيانة `maintenance.php`.
- إشعارات `notifications.php` و`api/get_notifications.php`.
- مشرف: `admin/dashboard.php` وإدارة خزائن ومستخدمين وحجوزات وصيانة. `admin/key_management.php` يعيد التوجيه إلى `key-return.php` كما ذكر الملف السابق.
- تغيير حالة خزانة وتحديث صيانة وإرجاع مفتاح عبر واجهات `api/`.

## 5 كيف يعمل

`includes/db.php` يفتح PDO ويحسب `BASE_PATH` من مسار السكربت مع حذف اللواحق `admin` و`api` و`includes`. الصفحات المحمية تستدعي `require_login` أو `require_student` أو `require_admin`. الخريطة تطلب `api/get_lockers.php` وترسم المربعات في `assets/js/locker-map.js`.

## 6 أمثلة واقعية

`reserve_locker.php` يبدأ معاملة، يقرأ الخزانة `SELECT ... FOR UPDATE`، يرفض إن الحالة ليست `available` أو إن للطالب حجزاً `active`، ثم `reservation_end_date` ثم إدراج `reservations` وتحديث الخزانة إلى `booked` وإدراج `key_returns` وإشعار.

`reservation_end_date`: `daily` يضيف يوماً، `weekly` يضيف 7 أيام، `monthly` يضيف شهراً، و`term` أو أي قيمة أخرى ترجع التاريخ الثابت `2026-06-30`.

`generate_reservation_id` ينتج `RES-` وأربعة أرقام. `generate_feedback_id` ينتج `MT-` أو `FB-` حسب البادئة.

## 7 رحلة المستخدم

طالب جديد: `register.php` ثم `index.php` ثم `home.php` ثم `find-locker.php` ثم تظهر النتيجة في `my-reservations.php`. بلاغ عطل من `feedback.php` أو `maintenance.php`. المشرف يسجّل إعادة المفتاح من `key-return.php` فيُحدَّث `key_returns` ويُدرج إشعار.

## 8 الوحدات

| الوحدة | الملفات |
| --- | --- |
| الدخول | `index.php` `register.php` `logout.php` `assets/js/auth.js` |
| الطالب | `home.php` `find-locker.php` `my-reservations.php` `feedback.php` `maintenance.php` |
| المشرف | `admin/*.php` و`key-return.php` |
| واجهات JSON | `api/*.php` |
| مشترك | `includes/` |
| لغة | `assets/js/lang.js` |
| بيانات | `database.sql` |

يوجد أيضاً ملف عربي عن توزيع الشغل على زميلات داخل المجلد. محتواه تنظيمي للفريق وليس منطق التشغيل.

## 9 الجهات والكيانات

الجهة المذكورة في الترجمة: جامعة الأمير سطام بن عبدالعزيز، الكلية التطبيقية. الخزائن البذرية في المنطقة `B` بأرقام مثل `B-101` وعلى أدوار. لا جدول كليات متعدد.

## 10 الصلاحيات

| الدالة | السلوك |
| --- | --- |
| `require_student` | غير الطالب يُحوَّل إلى `admin/dashboard.php` |
| `require_admin` | غير المشرف يُحوَّل إلى `home.php` |
| حجز وإلغاء وبلاغ وصيانة طالب | الجلسة ودور `student` في ملفات `api` المعنية |
| `return_key.php` و`update_locker_status.php` و`update_maintenance.php` و`get_stats.php` | دور `admin` |

`get_lockers.php` و`get_notifications.php` يبدآن الجلسة. الإشعارات مرتبطة بـ `user_id` في الجلسة عند الاستخدام من الواجهة.

## 11 الأتمتة

معرفات الحجز والبلاغ تُولَّد من آخر معرف. معاملة الحجز والإلغاء مع `FOR UPDATE`. لا مهمة مجدولة تنقل الحجز من `active` إلى `expired`. تاريخ نهاية الفصل ثابت داخل الدالة لا يُحسب من تقويم الجامعة.

## 12 أثر الوحدات على بعضها

الحجز يغيّر `lockers.status` إلى `booked` وينشئ `key_returns` بإعادة غير مكتملة وإشعاراً نوعه `booking`. الإلغاء يعيد الخزانة ويحدّث حالة الحجز. بلاغ الصيانة قد يدرج صفاً في `maintenance` مربوطاً بـ `feedback_id`. إرجاع المفتاح يحدث `key_returns` ويخطر الطالب. تغيير المشرف لحالة الخزانة إلى صيانة يمنع حجزها لأن الحجز يشترط `available`.

## 13 المعجم

| المصطلح | المعنى |
| --- | --- |
| مدة | `daily` أو `weekly` أو `monthly` أو `term` |
| حالة خزانة | `available` أو `booked` أو `maintenance` |
| حالة حجز | `active` أو `expired` أو `cancelled` |
| مفتاح | صف `key_returns` لكل حجز |
| BASE_PATH | بادئة URL محسوبة في `db.php` |

## 14 الأسئلة الشائعة

| السؤال | الجواب |
| --- | --- |
| هل تُنسى كلمة المرور من الرابط؟ | الرابط `#` فقط. غير موجود معالج استعادة |
| هل تنتهي المدة وحدها؟ | عمود `status` لا يتبدل بمجدول. النهاية تاريخ مخزن |
| أين الصور؟ | `uploads/feedback/` و`uploads/maintenance/` تُنشأ عند الرفع |
| هل الواجهة ثنائية اللغة؟ | نعم عبر `lang.js` و`data-i18n` و`localStorage` كما في الملف السابق |

## 15 المعمارية

```
المتصفح (PHP pages + assets/js)
        |
        +-- includes/auth_check.php / functions.php
        +-- includes/db.php --> MySQL mylocker_db
        +-- api/*.php --> JSON
        +-- uploads/
```

## 16 التقنيات المستخدمة

HTML وCSS (ملف عام وملف لكل منطقة) وJavaScript بلا jQuery. PHP 8 مذكور في الملف السابق. الكود يستخدم أنواعاً مثل `?string` و`void`. MySQL بجداول ومفاتيح أجنبية. PDO مع تعطيل محاكاة التحضير `ATTR_EMULATE_PREPARES => false`.

## 17 شجرة الملفات

```
MyLoker/
├── index.php register.php logout.php home.php
├── find-locker.php my-reservations.php
├── feedback.php maintenance.php notifications.php key-return.php
├── database.sql README.md
├── admin/ dashboard.php manage_lockers.php manage_users.php
│         manage_reservations.php maintenance_requests.php key_management.php
├── api/ get_lockers.php reserve_locker.php cancel_reservation.php
│      submit_feedback.php submit_maintenance.php return_key.php
│      get_notifications.php update_locker_status.php
│      update_maintenance.php get_stats.php
├── includes/ db.php functions.php auth_check.php header.php
│            sidebar.php footer.php icons.php
├── assets/css/ assets/js/ assets/fonts/ assets/images/Logo.png
└── uploads/.gitkeep
```

## 18 الواجهة الأمامية

صفحات PHP كاملة مع `includes/header.php` و`sidebar.php`. الشبكة في `locker-map.js`. اللغة في `lang.js`. الجرس في `global.js`. لا `rel="icon"` في ملفات PHP التي فُحصت. الشعار `assets/images/Logo.png`.

## 19 الواجهة الخلفية

`json_response` يضبط JSON و`JSON_UNESCAPED_UNICODE`. أخطاء PDO تُرمى كاستثناءات بسبب `ERRMODE_EXCEPTION`. لا إطار.

## 20 تدفق الطلب

مثال حجز شهري:

```
POST api/reserve_locker.php
Content-Type: application/json
جلسة الطالب
  -> beginTransaction
  -> SELECT locker FOR UPDATE
  -> رفض إن الحالة ليست available أو وُجد حجز active
  -> reservation_end_date(start, monthly)
  -> INSERT reservations (RES-xxxx)
  -> UPDATE lockers SET status = booked
  -> INSERT key_returns
  -> INSERT notifications
  -> commit
  -> JSON
```

## 21 جداول قاعدة البيانات

قاعدة `mylocker_db`.

| الجدول | ملاحظات من `database.sql` |
| --- | --- |
| users | `student_id` فريد، `password_hash`، دور، `avatar` |
| lockers | رقم فريد، دور، منطقة، حجم `small|medium|large`، حالة |
| reservations | مفتاح نصي `reservation_id`، مدة، بداية، نهاية، حالة |
| key_returns | حجز، `returned_by`، `is_returned` |
| feedback | مفتاح `MT-` أو `FB-`، تصنيف `maintenance|general`، تقييم، حالة |
| maintenance | تصنيف العطل، `photo_path`، أولوية، إسناد، حالة |
| notifications | عنوان ورسالة بالعربي والإنجليزي، نوع `booking|maintenance|key_return|system|expiry` |

البذرة: مستخدمون وخزائن (الملف السابق يذكر 60 خزانة: 3 أدوار في 20) وحجوزات ومفاتيح وبلاغات وصيانة وإشعارات. التعليق في SQL يذكر 60 خزانة والجناح العلمي.

## 22 نقاط النهاية

| الملف | الغرض |
| --- | --- |
| `api/get_lockers.php` | GET خزائن |
| `api/reserve_locker.php` | POST JSON حجز |
| `api/cancel_reservation.php` | POST إلغاء |
| `api/submit_feedback.php` | POST نموذج، صورة اختيارية |
| `api/submit_maintenance.php` | POST نموذج صيانة |
| `api/return_key.php` | POST مشرف |
| `api/get_notifications.php` | قائمة وعدد وتعليم مقروء |
| `api/update_locker_status.php` | POST مشرف |
| `api/update_maintenance.php` | POST مشرف |
| `api/get_stats.php` | GET مشرف |

صفحات HTML-PHP مذكورة في القسم 8.

## 23 المصادقة

`ensure_session` ثم `password_verify` على `password_hash`. الجلسة تخزن `user_id` و`role` والاسم. `logout.php` يمسح الكعكة ويستدعي `session_destroy`. لا `session_regenerate_id` في `index.php`. لا CSRF على واجهات `api`.

## 24 ضوابط الأمان الموجودة فعلياً

- `password_hash` / `password_verify`.
- `h()` عبر `htmlspecialchars` مع `ENT_QUOTES | ENT_HTML5`.
- جمل محضّرة و`EMULATE_PREPARES` معطلة.
- `FOR UPDATE` داخل معاملة للحجز والإلغاء.
- فحص الدور قبل عمليات الطالب أو المشرف.
- اسم ملف الرفع: بادئة و`random_bytes` وامتداد بعد حذف غير الأحرف والأرقام.

الصورة تُنقل بامتداد اسم الملف الأصلي بلا فحص `finfo`. لا حد حجم ظاهر في المقطعين المفحوصين من `submit_feedback.php`.

## 25 الإعدادات

ثوابت `includes/db.php`: `DB_HOST` = `localhost`، `DB_USER` = `root`، `DB_PASS` فارغة، `DB_NAME` = `mylocker_db`، `DB_CHARSET` = `utf8mb4`. `BASE_PATH` يُحسب ولا يُكتب يدوياً. لا `.env`.

## 26 التكاملات

غير موجود في الملفات الحالية. لا بريد رغم نوع إشعار `expiry` في التعداد. لا بوابة دفع.

## 27 المهام المجدولة

غير موجود في الملفات الحالية.

## 28 تخزين الملفات

`uploads/.gitkeep` في المستودع. عند البلاغ يُنشأ `uploads/feedback/`. عند صيانة الصفحة الأخرى يُنشأ `uploads/maintenance/` والمسار يُحفظ في `maintenance.photo_path`. عمود `feedback` نفسه بلا مسار صورة في تعريف الجدول، والصورة المرتبطة بالبلاغ تذهب إلى صف الصيانة حين يُنشأ.

## 29 السجلات

غير موجود في الملفات الحالية. `users.last_login` عمود بلا تحديث ظاهر في مسار الدخول المفحوص.

## 30 التثبيت

1. Apache أو ما يعادله مع PHP وMySQL. الملف السابق يذكر XAMPP.
2. استيراد `database.sql` لإنشاء `mylocker_db` (الملف يحذف الجداول ثم يعيد إنشاءها).
3. مطابقة ثوابت `includes/db.php`.
4. فتح `index.php`. `BASE_PATH` يتبع مجلد المشروع تحت جذر الويب.

## 31 دليل التطوير

صفحات العرض في الجذر و`admin/`. كل عملية JSON في ملف تحت `api/` وتستدعي `ensure_session` ثم `json_response`. دوال المعرفات والتاريخ في `includes/functions.php`. النصوص ثنائية اللغة تُضاف في كائن `LANG` داخل `lang.js` وتربط بـ `data-i18n`.

## 32 النشر

غير موثق. الملفات المرفوعة على القرص تحتاج مجلداً دائماً. لا Docker ولا ملف منصة.

## 33 النسخ الاحتياطي

غير موثق. انسخ `mylocker_db` ومجلد `uploads/` بما فيه `feedback` و`maintenance` بعد الاستخدام.

## 34 استكشاف الأخطاء

| الظاهرة | الاستنتاج من الكود |
| --- | --- |
| فشل PDO | MySQL متوقف أو الثوابت لا تطابق `mylocker_db` |
| دخول يفشل رغم صحة الكلمة على التبويب الآخر | `role_mismatch` |
| حجز مرفوض | الخزانة ليست `available` أو للطالب حجز `active` |
| CSS مكسور | `BASE_PATH` مشتق من `SCRIPT_NAME`. مسار غير اعتيادي يغيّر البادئة |
| استيراد ثانٍ يمسح البيانات | `database.sql` يبدأ بـ `DROP TABLE` |
| زر نسيت كلمة المرور بلا صفحة | `href="#"` |

## 35 الاعتماديات

لا Composer ولا npm. خطوط IBM Plex Sans Arabic محلية مع `OFL.txt`. إصدار PHP 8 مذكور في الملف السابق وغير مثبت بملف قفل. الترميز `utf8mb4`.

## 36 القيود المعروفة

- نهاية `term` ثابتة `2026-06-30`.
- لا انتقال تلقائي إلى `expired`.
- لا استعادة كلمة مرور.
- رفع بلا فحص MIME.
- لا CSRF.
- لا تجديد معرف جلسة عند الدخول.
- لا `rel="icon"`.
- نوع الإشعار `expiry` موجود في الجدول بلا مجدول يملؤه.

## 37 الحالة الحالية

نظام خزائن محلي بطالب ومشرف، خريطة، حجز بمعاملة، مفاتيح، بلاغات، لغتان، وبذرة جاهزة بعد الاستيراد. لا رقم إصدار تطبيق.

## 38 قرارات معمارية

- استنتاج من الكود: الحجز معاملة واحدة مع قفل صف حتى لا يُحجز المقعد مرتين معاً.
- استنتاج من الكود: معرف الحجز نصي `RES-` لا رقم تلقائي، فيُولَّد في PHP من آخر قيمة.
- استنتاج من الكود: `BASE_PATH` ديناميكي حتى يعمل المشروع في مجلد فرعي دون ثابت مسار يدوي.

## 39 سجل التغييرات

غير موجود في الملفات الحالية.

## System Overview

خزانتي تحجز خزانة متاحة لطالب واحد نشط، تنشئ سجل مفتاح وإشعاراً، وتترك الصيانة والمفاتيح للمشرف عبر واجهات JSON.

## Quick Reference

| الجزء | التقنية | الموقع | الدور |
| --- | --- | --- | --- |
| الاتصال | PDO | `includes/db.php` | `mylocker_db` و`BASE_PATH` |
| الدوال | PHP | `includes/functions.php` | جلسة ومعرفات وتاريخ نهاية |
| الدخول | PHP | `index.php` | رقم مستخدم ودور |
| الخريطة | JS | `assets/js/locker-map.js` | رسم الخزائن |
| الحجز | PHP | `api/reserve_locker.php` | معاملة وقفل |
| اللغة | JS | `assets/js/lang.js` | عربي وإنجليزي |
| المخطط | SQL | `database.sql` | 7 جداول وبذرة |
| الرفع | ملفات | `uploads/` | صور البلاغ والصيانة |

## Quick Start

1. استورد `database.sql` مرة على بيئة تقبل حذف الجداول.
2. راجع ثوابت `includes/db.php`.
3. افتح `index.php`.
4. أرقام الدخول البذرية في تعليق أعلى `INSERT` داخل `database.sql`. لا تُنسخ كلمات المرور هنا.

## For Non-Technical Users

الطالب يسجّل برقمه الجامعي ثم يبحث عن خزانة خضراء ويختار المدة. الحجز يظهر في حجوزاتي. العطل يُرسل من صفحة البلاغ أو الصيانة. المشرف يسجّل رجوع المفتاح ويغيّر الخزانة إلى صيانة إذا تعطلت. زر نسيت كلمة المرور على الشاشة لا يفتح خدمة استرجاع.

## For Developers

أي مدة جديدة تُضاف في `ENUM` وفي `reservation_end_date` وفي الواجهة معاً. إبقاء `FOR UPDATE` داخل المعاملة يحافظ على منع التعارض. فحص نوع الملف بالمحتوى غير موجود اليوم عند الرفع.
