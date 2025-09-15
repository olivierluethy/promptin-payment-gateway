-- ==========================================
-- Database: promptin_api (Beispielname)
-- ==========================================
DROP DATABASE IF EXISTS promptin_api;

CREATE DATABASE IF NOT EXISTS promptin_api CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE promptin_api;

-- ==========================================
-- Users Table
-- ==========================================
CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    two_factor_secret VARCHAR(255) DEFAULT NULL,
    stripe_customer_id VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

-- ==========================================
-- Products Table
-- ==========================================
CREATE TABLE products (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    code VARCHAR(50) NOT NULL UNIQUE,
    -- z.B. "extension_blocker"
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

-- ==========================================
-- Plans Table
-- ==========================================
CREATE TABLE plans (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(50) NOT NULL,
    -- z.B. "Basic", "Pro", "Enterprise"
    stripe_price_id VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

-- ==========================================
-- Subscriptions Table
-- ==========================================
CREATE TABLE subscriptions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    plan_id BIGINT UNSIGNED NOT NULL,
    status ENUM('active', 'canceled', 'past_due', 'trial') NOT NULL DEFAULT 'trial',
    starts_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    expires_at DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (plan_id) REFERENCES plans(id) ON DELETE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

-- ==========================================
-- Password Resets (für Laravel Standard)
-- ==========================================
CREATE TABLE password_resets (
    email VARCHAR(255) NOT NULL,
    token VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX (email)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

CREATE TABLE `personal_access_tokens` (
    `id` bigint unsigned NOT NULL AUTO_INCREMENT,
    `tokenable_type` varchar(255) NOT NULL,
    `tokenable_id` bigint unsigned NOT NULL,
    `name` varchar(255) NOT NULL,
    `token` varchar(64) NOT NULL,
    `abilities` text,
    `last_used_at` timestamp NULL DEFAULT NULL,
    `expires_at` timestamp NULL DEFAULT NULL,
    `created_at` timestamp NULL DEFAULT NULL,
    `updated_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
    KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`, `tokenable_id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- Add refresh_tokens table for session persistence
CREATE TABLE refresh_tokens (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    token VARCHAR(255) NOT NULL UNIQUE,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- Creating the sessions table for Laravel session management
CREATE TABLE `sessions` (
    `id` VARCHAR(255) NOT NULL,
    `user_id` BIGINT UNSIGNED NULL,
    `ip_address` VARCHAR(45) NULL,
    `user_agent` TEXT NULL,
    `payload` LONGTEXT NOT NULL,
    `last_activity` INT NOT NULL,
    PRIMARY KEY (`id`),
    INDEX `sessions_user_id_index` (`user_id`),
    INDEX `sessions_last_activity_index` (`last_activity`),
    CONSTRAINT `sessions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE
    subscriptions
ADD
    COLUMN billing_type ENUM('monthly', 'yearly') NOT NULL DEFAULT 'monthly';

ALTER TABLE
    plans
ADD
    COLUMN default_billing_type ENUM('monthly', 'yearly') NOT NULL DEFAULT 'monthly';

-- Erst ein Produkt anlegen
INSERT INTO products (name, code, description)
VALUES ('PromptIn Subscription', 'promptin_subscription', 'Abos für PromptIn');

-- Produkt-ID merken (z. B. 1)

-- Pläne einfügen (mit Stripe Price IDs)
INSERT INTO plans (product_id, name, stripe_price_id, default_billing_type)
VALUES 
(1, 'Basic Monthly', 'price_1S24tdGCiPyXR7LX0IioJH6G', 'monthly'),
(1, 'Basic Yearly',  'price_1S24v2GCiPyXR7LXjrEVd8Vv', 'yearly'),
(1, 'Pro Monthly',   'price_1S24wnGCiPyXR7LXDBJXkvc6', 'monthly'),
(1, 'Pro Yearly',    'price_1S24x7GCiPyXR7LXkVm8d8HH', 'yearly');


-- Pläne einfügen (mit Stripe Price IDs) - Testing
INSERT INTO plans (product_id, name, stripe_price_id, default_billing_type)
VALUES 
(1, 'Basic Monthly', 'price_1S273l7L878EJ8iQicYZYi0f', 'monthly'),
(1, 'Basic Yearly',  'price_1S275P7L878EJ8iQwCxiAO80', 'yearly'),
(1, 'Pro Monthly',   'price_1S275q7L878EJ8iQZYXVrApZ', 'monthly'),
(1, 'Pro Yearly',    'price_1S276I7L878EJ8iQxTXWGAbv', 'yearly');
