CREATE DATABASE IF NOT EXISTS db_vredeburg CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE db_vredeburg;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS reservation_histories;
DROP TABLE IF EXISTS reservations;
DROP TABLE IF EXISTS quota_slots;
DROP TABLE IF EXISTS users;
SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(120) NOT NULL UNIQUE,
    phone VARCHAR(20) NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'user') NOT NULL DEFAULT 'user',

    created_by VARCHAR(50) DEFAULT 'system',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_by VARCHAR(50) NULL,
    updated_at DATETIME NULL,
    is_deleted TINYINT(1) DEFAULT 0,
    deleted_by VARCHAR(50) NULL,
    deleted_at DATETIME NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE quota_slots (
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

CREATE TABLE reservations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    reservation_code VARCHAR(30) NOT NULL UNIQUE,
    user_id INT NOT NULL,
    visit_date DATE NOT NULL,
    visit_time TIME NOT NULL,
    total_people INT NOT NULL,
    price_per_person INT NOT NULL DEFAULT 10000,
    total_price INT NOT NULL,
    status ENUM('pending', 'paid', 'expired', 'cancelled', 'rejected') NOT NULL DEFAULT 'pending',
    payment_deadline DATETIME NOT NULL,
    admin_note TEXT NULL,

    created_by VARCHAR(50) DEFAULT 'system',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_by VARCHAR(50) NULL,
    updated_at DATETIME NULL,
    is_deleted TINYINT(1) DEFAULT 0,
    deleted_by VARCHAR(50) NULL,
    deleted_at DATETIME NULL,

    CONSTRAINT fk_reservations_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE RESTRICT ON UPDATE CASCADE,

    INDEX idx_reservation_user (user_id),
    INDEX idx_reservation_date_time (visit_date, visit_time),
    INDEX idx_reservation_status (status),
    INDEX idx_reservation_code (reservation_code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE reservation_histories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    reservation_id INT NOT NULL,
    old_status VARCHAR(30) NULL,
    new_status VARCHAR(30) NOT NULL,
    note TEXT NULL,

    created_by VARCHAR(50) DEFAULT 'system',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_histories_reservation
        FOREIGN KEY (reservation_id) REFERENCES reservations(id)
        ON DELETE CASCADE ON UPDATE CASCADE,

    INDEX idx_history_reservation (reservation_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
