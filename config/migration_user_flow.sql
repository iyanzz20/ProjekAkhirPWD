USE db_vredeburg;

ALTER TABLE users
    ADD COLUMN IF NOT EXISTS phone VARCHAR(20) NULL AFTER email;

ALTER TABLE reservations
    ADD COLUMN IF NOT EXISTS reservation_code VARCHAR(30) NULL AFTER id,
    ADD COLUMN IF NOT EXISTS payment_deadline DATETIME NULL AFTER status,
    ADD COLUMN IF NOT EXISTS admin_note TEXT NULL AFTER payment_deadline;

UPDATE reservations
SET reservation_code = CONCAT('OLD-', id)
WHERE reservation_code IS NULL OR reservation_code = '';

ALTER TABLE reservations
    MODIFY reservation_code VARCHAR(30) NOT NULL;

ALTER TABLE reservations
    ADD UNIQUE KEY IF NOT EXISTS unique_reservation_code (reservation_code);

UPDATE reservations
SET payment_deadline = DATE_ADD(created_at, INTERVAL 2 HOUR)
WHERE payment_deadline IS NULL;

ALTER TABLE reservations
    MODIFY payment_deadline DATETIME NOT NULL;

CREATE TABLE IF NOT EXISTS quota_slots (
    id INT AUTO_INCREMENT PRIMARY KEY,
    visit_date DATE NOT NULL,
    visit_time TIME NOT NULL,
    quota_limit INT NOT NULL DEFAULT 50,

    created_by VARCHAR(50) DEFAULT 'system',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_by VARCHAR(50) NULL,
    updated_at DATETIME NULL,
    is_deleted TINYINT(1) DEFAULT 0,
    deleted_by VARCHAR(50) NULL,
    deleted_at DATETIME NULL,

    UNIQUE KEY unique_quota_slot (visit_date, visit_time)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS reservation_histories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    reservation_id INT NOT NULL,
    old_status VARCHAR(30) NULL,
    new_status VARCHAR(30) NOT NULL,
    note TEXT NULL,

    created_by VARCHAR(50) DEFAULT 'system',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_history_reservation (reservation_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
