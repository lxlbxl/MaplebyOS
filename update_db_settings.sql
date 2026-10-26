CREATE TABLE IF NOT EXISTS `settings` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `key` VARCHAR(255) NOT NULL UNIQUE,
    `value` TEXT NULL,
    `type` VARCHAR(50) DEFAULT 'string',
    `group` VARCHAR(100) DEFAULT 'general',
    `created_at` TIMESTAMP NULL,
    `updated_at` TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `settings` (`key`, `value`, `type`, `group`, `created_at`, `updated_at`) VALUES
('app_name', 'Shipping and Logistics OS', 'string', 'general', NOW(), NOW()),
('support_email', 'support@example.com', 'string', 'general', NOW(), NOW()),
('default_currency', 'USD', 'string', 'general', NOW(), NOW()),
('timezone', 'UTC', 'string', 'general', NOW(), NOW()),
('smtp_host', '', 'string', 'email', NOW(), NOW()),
('smtp_port', '587', 'integer', 'email', NOW(), NOW()),
('smtp_encryption', 'tls', 'string', 'email', NOW(), NOW()),
('smtp_username', '', 'string', 'email', NOW(), NOW()),
('smtp_password', '', 'string', 'email', NOW(), NOW()),
('vat_rate', '20', 'float', 'finance', NOW(), NOW()),
('handling_fee', '0', 'float', 'finance', NOW(), NOW()),
('strict_password_policy', 'true', 'boolean', 'security', NOW(), NOW()),
('session_timeout', 'true', 'boolean', 'security', NOW(), NOW());
