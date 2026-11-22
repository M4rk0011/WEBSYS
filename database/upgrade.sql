-- Upgrade script for an existing V3 database; back up data before running.
-- Select your EXISTING database in phpMyAdmin before importing this file.
-- Additive only: existing users, reports and attachments are retained.
CREATE TABLE IF NOT EXISTS php_departments (id VARCHAR(40) PRIMARY KEY, name VARCHAR(150) NOT NULL UNIQUE);
CREATE TABLE IF NOT EXISTS php_announcements (id VARCHAR(40) PRIMARY KEY, title VARCHAR(200) NOT NULL, body TEXT NOT NULL, type VARCHAR(50) NOT NULL, published INTEGER NOT NULL DEFAULT 0, created_at VARCHAR(40) NOT NULL);
INSERT IGNORE INTO php_departments (id,name) VALUES ('administration','Barangay Administration');
INSERT IGNORE INTO php_departments (id,name) VALUES ('public_works','Public Works & Infrastructure');
INSERT IGNORE INTO php_departments (id,name) VALUES ('health','Health & Welfare');
INSERT IGNORE INTO php_departments (id,name) VALUES ('safety','Peace & Public Safety');
INSERT IGNORE INTO php_departments (id,name) VALUES ('sanitation','Environment & Sanitation');


-- V4: staff-only registries and validated locations. No sample personal data.
CREATE TABLE IF NOT EXISTS php_residents (
 id VARCHAR(40) PRIMARY KEY,
 first_name VARCHAR(100) NOT NULL, middle_name VARCHAR(100) NOT NULL DEFAULT '',
 last_name VARCHAR(100) NOT NULL, birth_date VARCHAR(10) NOT NULL DEFAULT '',
 sex VARCHAR(20) NOT NULL DEFAULT '', phone VARCHAR(40) NOT NULL DEFAULT '',
 address VARCHAR(250) NOT NULL, household_no VARCHAR(80) NOT NULL DEFAULT '',
 notes TEXT NOT NULL, created_at VARCHAR(40) NOT NULL, updated_at VARCHAR(40) NOT NULL
);
CREATE TABLE IF NOT EXISTS php_businesses (
 id VARCHAR(40) PRIMARY KEY, business_name VARCHAR(180) NOT NULL,
 owner_name VARCHAR(160) NOT NULL DEFAULT '', business_type VARCHAR(120) NOT NULL DEFAULT '',
 address VARCHAR(250) NOT NULL, contact VARCHAR(100) NOT NULL DEFAULT '',
 permit_no VARCHAR(100) NOT NULL DEFAULT '', renewal_date VARCHAR(10) NOT NULL DEFAULT '',
 cctv_status VARCHAR(15) NOT NULL DEFAULT 'unknown', cctv_count INT NOT NULL DEFAULT 0,
 notes TEXT NOT NULL, created_at VARCHAR(40) NOT NULL, updated_at VARCHAR(40) NOT NULL
);
CREATE TABLE IF NOT EXISTS php_map_places (
 id VARCHAR(40) PRIMARY KEY, name VARCHAR(160) NOT NULL,
 kind VARCHAR(30) NOT NULL, hazard_type VARCHAR(30) NOT NULL DEFAULT '',
 description TEXT NOT NULL, latitude DECIMAL(10,7) NOT NULL,
 longitude DECIMAL(10,7) NOT NULL,
 review_status VARCHAR(20) NOT NULL DEFAULT 'draft',
 photo_name VARCHAR(80) NOT NULL DEFAULT '',
 created_at VARCHAR(40) NOT NULL, updated_at VARCHAR(40) NOT NULL
);
