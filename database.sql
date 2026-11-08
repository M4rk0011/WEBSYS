-- Barangay Lower General Luna | Resident portal database
-- Import this file through phpMyAdmin's Import tab (do not paste PHP code into SQL).
-- Safe to re-import: existing accounts are preserved; this script has no DROP statements.
-- Current scope: resident registration, login, and dashboard profile only.
-- Future concern-reporting tables are intentionally not created in this design update.

CREATE DATABASE IF NOT EXISTS lower_general_luna
    CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE lower_general_luna;

CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    fullname VARCHAR(100) NOT NULL,
    email VARCHAR(190) NOT NULL UNIQUE,
    address VARCHAR(255) NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;
