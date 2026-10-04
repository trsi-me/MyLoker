-- MyLocker — database schema and seed data
-- Database: mylocker_db

CREATE DATABASE IF NOT EXISTS mylocker_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE mylocker_db;

SET NAMES utf8mb4;

DROP TABLE IF EXISTS notifications;
DROP TABLE IF EXISTS maintenance;
DROP TABLE IF EXISTS feedback;
DROP TABLE IF EXISTS key_returns;
DROP TABLE IF EXISTS reservations;
DROP TABLE IF EXISTS lockers;
DROP TABLE IF EXISTS users;

CREATE TABLE users (
    user_id       INT AUTO_INCREMENT PRIMARY KEY,
    student_id    VARCHAR(20) UNIQUE NOT NULL,
    full_name     VARCHAR(100) NOT NULL,
    email         VARCHAR(150) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    phone         VARCHAR(20),
    role          ENUM('student', 'admin') DEFAULT 'student',
    avatar        VARCHAR(255) DEFAULT NULL,
    created_at    DATETIME DEFAULT CURRENT_TIMESTAMP,
    last_login    DATETIME DEFAULT NULL
);

CREATE TABLE lockers (
    locker_id     INT AUTO_INCREMENT PRIMARY KEY,
    locker_number VARCHAR(20) UNIQUE NOT NULL,
    floor_level   VARCHAR(10) NOT NULL,
    zone          VARCHAR(10) NOT NULL,
    size          ENUM('small','medium','large') DEFAULT 'medium',
    status        ENUM('available','booked','maintenance') DEFAULT 'available',
    location_desc VARCHAR(200),
    last_updated  DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE reservations (
    reservation_id VARCHAR(20) PRIMARY KEY,
    user_id        INT NOT NULL,
    locker_id      INT NOT NULL,
    duration_type  ENUM('daily','weekly','monthly','term') NOT NULL,
    start_date     DATE NOT NULL,
    end_date       DATE NOT NULL,
    status         ENUM('active','expired','cancelled') DEFAULT 'active',
    created_at     DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id),
    FOREIGN KEY (locker_id) REFERENCES lockers(locker_id)
);

CREATE TABLE key_returns (
    return_id      INT AUTO_INCREMENT PRIMARY KEY,
    reservation_id VARCHAR(20) NOT NULL,
    returned_by    INT NOT NULL,
    return_date    DATETIME DEFAULT NULL,
    is_returned    TINYINT(1) DEFAULT 0,
    notes          TEXT,
    FOREIGN KEY (reservation_id) REFERENCES reservations(reservation_id),
    FOREIGN KEY (returned_by) REFERENCES users(user_id)
);

CREATE TABLE feedback (
    feedback_id   VARCHAR(20) PRIMARY KEY,
    user_id       INT NOT NULL,
    locker_id     INT NOT NULL,
    category      ENUM('maintenance','general') NOT NULL,
    message       TEXT NOT NULL,
    rating        TINYINT DEFAULT NULL,
    status        ENUM('pending','in_progress','resolved') DEFAULT 'pending',
    submitted_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id),
    FOREIGN KEY (locker_id) REFERENCES lockers(locker_id)
);

CREATE TABLE maintenance (
    maintenance_id  INT AUTO_INCREMENT PRIMARY KEY,
    locker_id       INT NOT NULL,
    feedback_id     VARCHAR(20) DEFAULT NULL,
    issue_category  ENUM('broken_lock','damaged_door','dirty','hinge','other') NOT NULL,
    description     TEXT NOT NULL,
    photo_path      VARCHAR(255) DEFAULT NULL,
    priority        ENUM('urgent','normal','low') DEFAULT 'normal',
    assigned_to     INT DEFAULT NULL,
    status          ENUM('pending','in_progress','resolved') DEFAULT 'pending',
    scheduled_date  DATE DEFAULT NULL,
    completed_date  DATE DEFAULT NULL,
    created_at      DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (locker_id) REFERENCES lockers(locker_id),
    FOREIGN KEY (feedback_id) REFERENCES feedback(feedback_id),
    FOREIGN KEY (assigned_to) REFERENCES users(user_id)
);

CREATE TABLE notifications (
    notif_id    INT AUTO_INCREMENT PRIMARY KEY,
    user_id     INT NOT NULL,
    title_ar    VARCHAR(200) NOT NULL,
    title_en    VARCHAR(200) NOT NULL,
    message_ar  TEXT NOT NULL,
    message_en  TEXT NOT NULL,
    type        ENUM('booking','maintenance','key_return','system','expiry') NOT NULL,
    is_read     TINYINT(1) DEFAULT 0,
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id)
);

-- Passwords: students = Student@123, admin = Admin@123
INSERT INTO users (user_id, student_id, full_name, email, password_hash, phone, role, avatar) VALUES
(1, 'ADMIN001', 'مشرف النظام', 'admin@psau.edu.sa', '$2y$10$XbideZzJ0TgBGpqdvqvd9u5HrUoaU2HSjFu.m4incos2Tx7uGZquy', '0500000001', 'admin', NULL),
(2, '20241001', 'أحمد محمد العتيبي', 'ahmad.m@student.psau.edu.sa', '$2y$10$yI4fhip2qf/RwbVwJKmIBeE9BWAY.hWuQqRm93yo9z8xDjSnURi72', '0501111111', 'student', NULL),
(3, '20241002', 'فاطمة سعد الدوسري', 'fatima.s@student.psau.edu.sa', '$2y$10$yI4fhip2qf/RwbVwJKmIBeE9BWAY.hWuQqRm93yo9z8xDjSnURi72', '0502222222', 'student', NULL),
(4, '20241003', 'خالد عبدالله القحطاني', 'khaled.a@student.psau.edu.sa', '$2y$10$yI4fhip2qf/RwbVwJKmIBeE9BWAY.hWuQqRm93yo9z8xDjSnURi72', '0503333333', 'student', NULL),
(5, '20241004', 'نورة علي الشهري', 'noura.a@student.psau.edu.sa', '$2y$10$yI4fhip2qf/RwbVwJKmIBeE9BWAY.hWuQqRm93yo9z8xDjSnURi72', '0504444444', 'student', NULL),
(6, '20241005', 'سعد فيصل المطيري', 'saad.f@student.psau.edu.sa', '$2y$10$yI4fhip2qf/RwbVwJKmIBeE9BWAY.hWuQqRm93yo9z8xDjSnURi72', '0505555555', 'student', NULL);

-- 60 lockers: 3 floors x 20 lockers, zone B scientific wing
INSERT INTO lockers (locker_id, locker_number, floor_level, zone, size, status, location_desc) VALUES
(1, 'B-101', '1', 'B', 'small', 'booked', 'الجناح العلمي — الدور الأول — الممر أ'),
(2, 'B-102', '1', 'B', 'medium', 'booked', 'الجناح العلمي — الدور الأول — الممر أ'),
(3, 'B-103', '1', 'B', 'medium', 'available', 'الجناح العلمي — الدور الأول — الممر أ'),
(4, 'B-104', '1', 'B', 'large', 'available', 'الجناح العلمي — الدور الأول — الممر أ'),
(5, 'B-105', '1', 'B', 'small', 'maintenance', 'الجناح العلمي — الدور الأول — الممر ب'),
(6, 'B-106', '1', 'B', 'medium', 'available', 'الجناح العلمي — الدور الأول — الممر ب'),
(7, 'B-107', '1', 'B', 'medium', 'booked', 'الجناح العلمي — الدور الأول — الممر ب'),
(8, 'B-108', '1', 'B', 'small', 'available', 'الجناح العلمي — الدور الأول — الممر ب'),
(9, 'B-109', '1', 'B', 'large', 'available', 'الجناح العلمي — الدور الأول — الممر ج'),
(10, 'B-110', '1', 'B', 'medium', 'booked', 'الجناح العلمي — الدور الأول — الممر ج'),
(11, 'B-111', '1', 'B', 'medium', 'available', 'الجناح العلمي — الدور الأول — الممر ج'),
(12, 'B-112', '1', 'B', 'small', 'available', 'الجناح العلمي — الدور الأول — الممر ج'),
(13, 'B-113', '1', 'B', 'large', 'available', 'الجناح العلمي — الدور الأول — الممر د'),
(14, 'B-114', '1', 'B', 'medium', 'booked', 'الجناح العلمي — الدور الأول — الممر د'),
(15, 'B-115', '1', 'B', 'small', 'available', 'الجناح العلمي — الدور الأول — الممر د'),
(16, 'B-116', '1', 'B', 'medium', 'available', 'الجناح العلمي — الدور الأول — الممر هـ'),
(17, 'B-117', '1', 'B', 'medium', 'available', 'الجناح العلمي — الدور الأول — الممر هـ'),
(18, 'B-118', '1', 'B', 'small', 'available', 'الجناح العلمي — الدور الأول — الممر هـ'),
(19, 'B-119', '1', 'B', 'large', 'available', 'الجناح العلمي — الدور الأول — الممر هـ'),
(20, 'B-120', '1', 'B', 'medium', 'available', 'الجناح العلمي — الدور الأول — الممر هـ'),
(21, 'B-201', '2', 'B', 'medium', 'available', 'الجناح العلمي — الدور الثاني — الممر أ'),
(22, 'B-202', '2', 'B', 'small', 'available', 'الجناح العلمي — الدور الثاني — الممر أ'),
(23, 'B-203', '2', 'B', 'large', 'available', 'الجناح العلمي — الدور الثاني — الممر أ'),
(24, 'B-204', '2', 'B', 'medium', 'available', 'الجناح العلمي — الدور الثاني — الممر أ'),
(25, 'B-205', '2', 'B', 'medium', 'available', 'الجناح العلمي — الدور الثاني — الممر ب'),
(26, 'B-206', '2', 'B', 'small', 'maintenance', 'الجناح العلمي — الدور الثاني — الممر ب'),
(27, 'B-207', '2', 'B', 'medium', 'available', 'الجناح العلمي — الدور الثاني — الممر ب'),
(28, 'B-208', '2', 'B', 'large', 'available', 'الجناح العلمي — الدور الثاني — الممر ب'),
(29, 'B-209', '2', 'B', 'medium', 'available', 'الجناح العلمي — الدور الثاني — الممر ج'),
(30, 'B-210', '2', 'B', 'small', 'available', 'الجناح العلمي — الدور الثاني — الممر ج'),
(31, 'B-211', '2', 'B', 'medium', 'available', 'الجناح العلمي — الدور الثاني — الممر ج'),
(32, 'B-212', '2', 'B', 'medium', 'available', 'الجناح العلمي — الدور الثاني — الممر ج'),
(33, 'B-213', '2', 'B', 'large', 'available', 'الجناح العلمي — الدور الثاني — الممر د'),
(34, 'B-214', '2', 'B', 'small', 'available', 'الجناح العلمي — الدور الثاني — الممر د'),
(35, 'B-215', '2', 'B', 'medium', 'available', 'الجناح العلمي — الدور الثاني — الممر د'),
(36, 'B-216', '2', 'B', 'medium', 'available', 'الجناح العلمي — الدور الثاني — الممر هـ'),
(37, 'B-217', '2', 'B', 'small', 'available', 'الجناح العلمي — الدور الثاني — الممر هـ'),
(38, 'B-218', '2', 'B', 'medium', 'available', 'الجناح العلمي — الدور الثاني — الممر هـ'),
(39, 'B-219', '2', 'B', 'large', 'available', 'الجناح العلمي — الدور الثاني — الممر هـ'),
(40, 'B-220', '2', 'B', 'medium', 'available', 'الجناح العلمي — الدور الثاني — الممر هـ'),
(41, 'B-301', '3', 'B', 'medium', 'available', 'الجناح العلمي — الدور الثالث — الممر أ'),
(42, 'B-302', '3', 'B', 'small', 'booked', 'الجناح العلمي — الدور الثالث — الممر أ'),
(43, 'B-303', '3', 'B', 'large', 'available', 'الجناح العلمي — الدور الثالث — الممر أ'),
(44, 'B-304', '3', 'B', 'medium', 'available', 'الجناح العلمي — الدور الثالث — الممر أ'),
(45, 'B-305', '3', 'B', 'medium', 'maintenance', 'الجناح العلمي — الدور الثالث — الممر ب'),
(46, 'B-306', '3', 'B', 'small', 'available', 'الجناح العلمي — الدور الثالث — الممر ب'),
(47, 'B-307', '3', 'B', 'medium', 'available', 'الجناح العلمي — الدور الثالث — الممر ب'),
(48, 'B-308', '3', 'B', 'large', 'available', 'الجناح العلمي — الدور الثالث — الممر ب'),
(49, 'B-309', '3', 'B', 'medium', 'booked', 'الجناح العلمي — الدور الثالث — الممر ج'),
(50, 'B-310', '3', 'B', 'small', 'available', 'الجناح العلمي — الدور الثالث — الممر ج'),
(51, 'B-311', '3', 'B', 'medium', 'available', 'الجناح العلمي — الدور الثالث — الممر ج'),
(52, 'B-312', '3', 'B', 'medium', 'available', 'الجناح العلمي — الدور الثالث — الممر ج'),
(53, 'B-313', '3', 'B', 'large', 'available', 'الجناح العلمي — الدور الثالث — الممر د'),
(54, 'B-314', '3', 'B', 'small', 'available', 'الجناح العلمي — الدور الثالث — الممر د'),
(55, 'B-315', '3', 'B', 'medium', 'available', 'الجناح العلمي — الدور الثالث — الممر د'),
(56, 'B-316', '3', 'B', 'medium', 'booked', 'الجناح العلمي — الدور الثالث — الممر هـ'),
(57, 'B-317', '3', 'B', 'small', 'available', 'الجناح العلمي — الدور الثالث — الممر هـ'),
(58, 'B-318', '3', 'B', 'medium', 'available', 'الجناح العلمي — الدور الثالث — الممر هـ'),
(59, 'B-319', '3', 'B', 'large', 'available', 'الجناح العلمي — الدور الثالث — الممر هـ'),
(60, 'B-320', '3', 'B', 'medium', 'available', 'الجناح العلمي — الدور الثالث — الممر هـ');

INSERT INTO reservations (reservation_id, user_id, locker_id, duration_type, start_date, end_date, status) VALUES
('RES-0001', 2, 1, 'monthly', '2026-01-10', '2026-06-30', 'active'),
('RES-0002', 3, 2, 'term', '2026-02-01', '2026-06-30', 'active'),
('RES-0003', 4, 7, 'weekly', '2026-03-01', '2026-03-15', 'active'),
('RES-0004', 5, 10, 'daily', '2026-04-01', '2026-04-07', 'active'),
('RES-0005', 6, 14, 'monthly', '2026-03-15', '2026-04-15', 'active'),
('RES-0006', 2, 18, 'term', '2026-01-20', '2026-06-30', 'cancelled'),
('RES-0007', 3, 21, 'monthly', '2025-02-15', '2025-05-15', 'expired'),
('RES-0008', 4, 24, 'weekly', '2025-03-20', '2025-03-27', 'expired'),
('RES-0009', 5, 29, 'monthly', '2025-03-01', '2025-04-01', 'expired'),
('RES-0010', 6, 33, 'term', '2025-01-05', '2025-06-30', 'expired');

INSERT INTO key_returns (reservation_id, returned_by, return_date, is_returned, notes) VALUES
('RES-0001', 2, NULL, 0, NULL),
('RES-0002', 3, '2026-03-01 10:00:00', 1, 'تمت الإعادة'),
('RES-0003', 4, NULL, 0, NULL),
('RES-0004', 5, NULL, 0, NULL),
('RES-0005', 6, NULL, 0, NULL),
('RES-0006', 2, NULL, 0, NULL),
('RES-0007', 3, '2025-05-20 09:00:00', 1, NULL),
('RES-0008', 4, '2025-03-28 11:00:00', 1, NULL),
('RES-0009', 5, '2025-04-02 08:30:00', 1, NULL),
('RES-0010', 6, '2025-07-01 10:00:00', 1, NULL);

INSERT INTO feedback (feedback_id, user_id, locker_id, category, message, rating, status) VALUES
('MT-0001', 2, 5, 'maintenance', 'قفل الخزانة يصدر صوتاً عند الفتح', NULL, 'pending'),
('MT-0002', 3, 26, 'maintenance', 'الباب لا يغلق بالكامل', NULL, 'in_progress'),
('FB-0001', 4, 8, 'general', 'تجربة ممتازة، شكراً للفريق', 5, 'resolved'),
('FB-0002', 5, 11, 'general', 'أقترح إضافة إضاءة في الممر', 4, 'pending'),
('FB-0003', 6, 12, 'general', 'الخزانة نظيفة وسهلة الاستخدام', 5, 'resolved');

INSERT INTO maintenance (locker_id, feedback_id, issue_category, description, priority, status) VALUES
(5, 'MT-0001', 'broken_lock', 'صوت غريب من القفل عند الاستخدام', 'urgent', 'pending'),
(26, 'MT-0002', 'damaged_door', 'الباب مائل قليلاً', 'normal', 'in_progress'),
(45, NULL, 'hinge', 'مفصلة تحتاج تشحيم', 'low', 'pending');

INSERT INTO notifications (user_id, title_ar, title_en, message_ar, message_en, type, is_read) VALUES
(2, 'تم تأكيد الحجز', 'Booking confirmed', 'تم تأكيد حجز الخزانة B-101.', 'Your locker B-101 reservation is confirmed.', 'booking', 0),
(2, 'تذكير: انتهاء الحجز', 'Expiry reminder', 'حجزك ينتهي خلال 5 أيام.', 'Your reservation ends in 5 days.', 'expiry', 0),
(3, 'تحديث صيانة', 'Maintenance update', 'جاري العمل على طلب الصيانة الخاص بك.', 'Your maintenance request is in progress.', 'maintenance', 1),
(4, 'مفتاح متأخر', 'Key overdue', 'يرجى إعادة مفتاح الخزانة.', 'Please return your locker key.', 'key_return', 0),
(5, 'إشعار النظام', 'System notice', 'تم تحديث سياسة الحجز.', 'Reservation policy has been updated.', 'system', 0),
(6, 'حجز جديد', 'New booking', 'تم إنشاء حجزك بنجاح.', 'Your booking was created successfully.', 'booking', 0);
