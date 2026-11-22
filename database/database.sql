-- Fresh-install database schema and demonstration seed data. Do not import over live records.
-- Fresh local demonstration installation. All demo accounts use admin123.
CREATE DATABASE IF NOT EXISTS barangay_service_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE barangay_service_system;
CREATE TABLE IF NOT EXISTS php_users (id VARCHAR(40) PRIMARY KEY, username VARCHAR(100) NOT NULL UNIQUE, email VARCHAR(200) NOT NULL UNIQUE, password_hash VARCHAR(255) NOT NULL, body LONGTEXT NOT NULL);
CREATE TABLE IF NOT EXISTS php_reports (id VARCHAR(40) PRIMARY KEY, body LONGTEXT NOT NULL);
CREATE TABLE IF NOT EXISTS php_departments (id VARCHAR(40) PRIMARY KEY, name VARCHAR(150) NOT NULL UNIQUE);
CREATE TABLE IF NOT EXISTS php_announcements (id VARCHAR(40) PRIMARY KEY, title VARCHAR(200) NOT NULL, body TEXT NOT NULL, type VARCHAR(50) NOT NULL, published INTEGER NOT NULL DEFAULT 0, created_at VARCHAR(40) NOT NULL);
-- V4: staff-only registries and validated locations. No sample personal data.
CREATE TABLE IF NOT EXISTS php_residents (
 id VARCHAR(40) PRIMARY KEY,
 first_name VARCHAR(100) NOT NULL, middle_name VARCHAR(100) NOT NULL DEFAULT '',
 last_name VARCHAR(100) NOT NULL, birth_date VARCHAR(10) NOT NULL DEFAULT '',
 sex VARCHAR(20) NOT NULL DEFAULT '', phone VARCHAR(40) NOT NULL DEFAULT '',
 address VARCHAR(250) NOT NULL, household_no VARCHAR(80) NOT NULL DEFAULT '',
 notes TEXT NOT NULL, created_at VARCHAR(40) NOT NULL, updated_at VARCHAR(40) NOT NULL
);
CREATE INDEX idx_resident_name ON php_residents (last_name, first_name);
CREATE TABLE IF NOT EXISTS php_businesses (
 id VARCHAR(40) PRIMARY KEY, business_name VARCHAR(180) NOT NULL,
 owner_name VARCHAR(160) NOT NULL DEFAULT '', business_type VARCHAR(120) NOT NULL DEFAULT '',
 address VARCHAR(250) NOT NULL, contact VARCHAR(100) NOT NULL DEFAULT '',
 permit_no VARCHAR(100) NOT NULL DEFAULT '', renewal_date VARCHAR(10) NOT NULL DEFAULT '',
 cctv_status VARCHAR(15) NOT NULL DEFAULT 'unknown', cctv_count INT NOT NULL DEFAULT 0,
 notes TEXT NOT NULL, created_at VARCHAR(40) NOT NULL, updated_at VARCHAR(40) NOT NULL
);
CREATE INDEX idx_business_name ON php_businesses (business_name);
CREATE TABLE IF NOT EXISTS php_map_places (
 id VARCHAR(40) PRIMARY KEY, name VARCHAR(160) NOT NULL,
 kind VARCHAR(30) NOT NULL, hazard_type VARCHAR(30) NOT NULL DEFAULT '',
 description TEXT NOT NULL, latitude DECIMAL(10,7) NOT NULL,
 longitude DECIMAL(10,7) NOT NULL,
 review_status VARCHAR(20) NOT NULL DEFAULT 'draft',
 photo_name VARCHAR(80) NOT NULL DEFAULT '',
 created_at VARCHAR(40) NOT NULL, updated_at VARCHAR(40) NOT NULL
);
CREATE INDEX idx_place_kind ON php_map_places (kind, review_status);


INSERT IGNORE INTO php_departments (id,name) VALUES ('administration','Barangay Administration');
INSERT IGNORE INTO php_departments (id,name) VALUES ('public_works','Public Works & Infrastructure');
INSERT IGNORE INTO php_departments (id,name) VALUES ('health','Health & Welfare');
INSERT IGNORE INTO php_departments (id,name) VALUES ('safety','Peace & Public Safety');
INSERT IGNORE INTO php_departments (id,name) VALUES ('sanitation','Environment & Sanitation');
INSERT IGNORE INTO php_users (id,username,email,password_hash,body) VALUES ('demo-system','admin','admin@lowergeneralluna.example','$2y$10$vGhiJW9cuW6wjXUM4CgXxepVQSs0mFNf4k7r.NcWJYm7inGPaw5sW','{"id":"demo-system","username":"admin","email":"admin@lowergeneralluna.example","role":"system_admin","verified":true,"departmentId":"","profile":{"firstName":"System","lastName":"Administrator","phone":"09170000000","address":"Lower General Luna, Baguio City","studentId":"","department":"","position":""},"createdAt":"2026-11-05T00:00:00+08:00","isActive":true}');
INSERT IGNORE INTO php_users (id,username,email,password_hash,body) VALUES ('demo-lgu','lguadmin','lguadmin@lowergeneralluna.example','$2y$10$vGhiJW9cuW6wjXUM4CgXxepVQSs0mFNf4k7r.NcWJYm7inGPaw5sW','{"id":"demo-lgu","username":"lguadmin","email":"lguadmin@lowergeneralluna.example","role":"lgu_admin","verified":true,"departmentId":"","profile":{"firstName":"LGU","lastName":"Administrator","phone":"09170000000","address":"Lower General Luna, Baguio City","studentId":"","department":"","position":""},"createdAt":"2026-11-05T00:00:00+08:00","isActive":true}');
INSERT IGNORE INTO php_users (id,username,email,password_hash,body) VALUES ('demo-head','departmenthead','departmenthead@lowergeneralluna.example','$2y$10$vGhiJW9cuW6wjXUM4CgXxepVQSs0mFNf4k7r.NcWJYm7inGPaw5sW','{"id":"demo-head","username":"departmenthead","email":"departmenthead@lowergeneralluna.example","role":"department_head","verified":true,"departmentId":"public_works","profile":{"firstName":"Department","lastName":"Head","phone":"09170000000","address":"Lower General Luna, Baguio City","studentId":"","department":"","position":""},"createdAt":"2026-11-05T00:00:00+08:00","isActive":true}');
INSERT IGNORE INTO php_users (id,username,email,password_hash,body) VALUES ('demo-personnel','personnel','personnel@lowergeneralluna.example','$2y$10$vGhiJW9cuW6wjXUM4CgXxepVQSs0mFNf4k7r.NcWJYm7inGPaw5sW','{"id":"demo-personnel","username":"personnel","email":"personnel@lowergeneralluna.example","role":"personnel","verified":true,"departmentId":"public_works","profile":{"firstName":"Government","lastName":"Personnel","phone":"09170000000","address":"Lower General Luna, Baguio City","studentId":"","department":"","position":""},"createdAt":"2026-11-05T00:00:00+08:00","isActive":true}');
INSERT IGNORE INTO php_users (id,username,email,password_hash,body) VALUES ('demo-resident','resident','resident@lowergeneralluna.example','$2y$10$vGhiJW9cuW6wjXUM4CgXxepVQSs0mFNf4k7r.NcWJYm7inGPaw5sW','{"id":"demo-resident","username":"resident","email":"resident@lowergeneralluna.example","role":"resident","verified":true,"departmentId":"","profile":{"firstName":"Community","lastName":"Resident","phone":"09170000000","address":"Lower General Luna, Baguio City","studentId":"","department":"","position":""},"createdAt":"2026-11-05T00:00:00+08:00","isActive":true}');
