-- Activity 1: Secure Login System
-- Run in phpMyAdmin: SQL tab (this script creates the wst2_auth database too)

CREATE DATABASE IF NOT EXISTS wst2_auth
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE wst2_auth;

CREATE TABLE IF NOT EXISTS users (
    id              INT UNSIGNED NOT NULL AUTO_INCREMENT,
    username        VARCHAR(30)  NOT NULL,
    email           VARCHAR(255) NOT NULL,
    password_hash   VARCHAR(255) NOT NULL,
    role            ENUM('admin','instructor','student') NOT NULL DEFAULT 'student',
    created_at      TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    -- Bonus: account lockout
    failed_attempts TINYINT UNSIGNED NOT NULL DEFAULT 0,
    locked_until    DATETIME     NULL DEFAULT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_users_username (username),
    UNIQUE KEY uq_users_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
