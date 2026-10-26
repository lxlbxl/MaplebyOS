-- ADGOD-OS Database Update Script
-- compatible with MySQL 8.0+

-- 1. Create Payments Table
CREATE TABLE IF NOT EXISTS `payments` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `quote_id` BIGINT UNSIGNED NOT NULL,
    `amount` DECIMAL(10,2) NOT NULL,
    `payment_date` DATE NOT NULL,
    `payment_method` VARCHAR(255) NOT NULL,
    `transaction_reference` VARCHAR(255) NULL,
    `notes` TEXT NULL,
    `created_by` BIGINT UNSIGNED NULL,
    `created_at` TIMESTAMP NULL,
    `updated_at` TIMESTAMP NULL,
    `deleted_at` TIMESTAMP NULL,
    FOREIGN KEY (`quote_id`) REFERENCES `quotes`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`created_by`) REFERENCES `users`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Update Quotes Table (Add linking columns)
-- Note: These statements might fail if columns already exist.
-- If so, you can ignore the error or skip these lines.

ALTER TABLE `quotes` ADD COLUMN `shipment_id` BIGINT UNSIGNED NULL;
ALTER TABLE `quotes` ADD CONSTRAINT `quotes_shipment_id_foreign` FOREIGN KEY (`shipment_id`) REFERENCES `shipments`(`id`) ON DELETE SET NULL;

ALTER TABLE `quotes` ADD COLUMN `inventory_type` VARCHAR(255) NULL;
ALTER TABLE `quotes` ADD COLUMN `inventory_id` BIGINT UNSIGNED NULL;
ALTER TABLE `quotes` ADD INDEX `quotes_inventory_type_inventory_id_index` (`inventory_type`, `inventory_id`);

-- 3. Update Shipment Statuses (Migration of old values)
-- UPDATE `shipments` SET `status` = 'received' WHERE `status` = 'pending'; -- Preserved for new flow
UPDATE `shipments` SET `status` = 'shipped' WHERE `status` = 'in_progress';
UPDATE `shipments` SET `status` = 'delivered' WHERE `status` = 'completed';
UPDATE `shipments` SET `status` = 'received' WHERE `status` = 'cancelled';

-- 4. Settings Table (If not exists)
CREATE TABLE IF NOT EXISTS `settings` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `key` VARCHAR(255) NOT NULL UNIQUE,
    `value` TEXT NULL,
    `type` VARCHAR(50) DEFAULT 'string',
    `group` VARCHAR(100) DEFAULT 'general',
    `created_at` TIMESTAMP NULL,
    `updated_at` TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Users Table updates (If not exists)
-- ALTER TABLE `users` ADD COLUMN `role` VARCHAR(50) NULL DEFAULT 'sales' AFTER `email`;
-- ALTER TABLE `users` ADD COLUMN `is_active` TINYINT(1) NOT NULL DEFAULT 1 AFTER `role`;
