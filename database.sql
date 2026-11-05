-- Import this SQL file in phpMyAdmin. Do not paste PHP files into the SQL tab.
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
