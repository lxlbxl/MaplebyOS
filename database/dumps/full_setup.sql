-- Full Database Setup Script for ADGOD-OS
-- Generated to resolve migration dependency orders manually
-- Execute this in phpMyAdmin or via mysql command line

SET FOREIGN_KEY_CHECKS=0;

-- -----------------------------------------------------
-- Table `users`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Table `permissions`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `permissions`;
CREATE TABLE `permissions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Table `roles`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `roles`;
CREATE TABLE `roles` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Table `model_has_permissions`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `model_has_permissions`;
CREATE TABLE `model_has_permissions` (
  `permission_id` bigint(20) unsigned NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Table `model_has_roles`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `model_has_roles`;
CREATE TABLE `model_has_roles` (
  `role_id` bigint(20) unsigned NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Table `role_has_permissions`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `role_has_permissions`;
CREATE TABLE `role_has_permissions` (
  `permission_id` bigint(20) unsigned NOT NULL,
  `role_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`role_id`),
  KEY `role_has_permissions_role_id_foreign` (`role_id`),
  CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Table `clients`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `clients`;
CREATE TABLE `clients` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'buyer',
  `company_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contact_person` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `secondary_phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `state_province` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `postal_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tax_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `source` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'direct',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `clients_created_by_foreign` (`created_by`),
  CONSTRAINT `clients_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Table `shipments` (formerly jobs)
-- -----------------------------------------------------
DROP TABLE IF EXISTS `shipments`;
DROP TABLE IF EXISTS `jobs`; -- Ensure old table is gone
CREATE TABLE `shipments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `shipment_number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `quote_id` bigint(20) unsigned DEFAULT NULL,
  `client_id` bigint(20) unsigned NOT NULL,
  `service_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `origin` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `destination` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `estimated_completion` date DEFAULT NULL,
  `actual_completion` date DEFAULT NULL,
  `total_value` decimal(15,2) DEFAULT NULL,
  `invoice_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `invoice_status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `payment_status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'unpaid',
  `assigned_to` bigint(20) unsigned DEFAULT NULL,
  `priority` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'medium',
  `notes` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  
  -- Legacy Columns Added
  `vin` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `vehicle_year` int(11) DEFAULT NULL,
  `vehicle_make` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `vehicle_model` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `vehicle_color` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tow_required` tinyint(1) NOT NULL DEFAULT 0,
  `driver_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ownership_documents` tinyint(1) NOT NULL DEFAULT 0,
  `date_received` date DEFAULT NULL,
  `location_notes` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  
  PRIMARY KEY (`id`),
  UNIQUE KEY `shipments_shipment_number_unique` (`shipment_number`),
  KEY `shipments_client_id_foreign` (`client_id`),
  KEY `shipments_assigned_to_foreign` (`assigned_to`),
  CONSTRAINT `shipments_assigned_to_foreign` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`),
  CONSTRAINT `shipments_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Table `leads`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `leads`;
CREATE TABLE `leads` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `client_id` bigint(20) unsigned DEFAULT NULL,
  `contact_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contact_email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contact_phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'new',
  `source` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `estimated_value` decimal(15,2) DEFAULT NULL,
  `expected_close_date` date DEFAULT NULL,
  `assigned_to` bigint(20) unsigned DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `leads_client_id_foreign` (`client_id`),
  KEY `leads_assigned_to_foreign` (`assigned_to`),
  CONSTRAINT `leads_assigned_to_foreign` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`),
  CONSTRAINT `leads_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Table `quotes`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `quotes`;
CREATE TABLE `quotes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `quote_number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `client_id` bigint(20) unsigned NOT NULL,
  `lead_id` bigint(20) unsigned DEFAULT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `issue_date` date NOT NULL,
  `expiry_date` date DEFAULT NULL,
  `subtotal` decimal(15,2) NOT NULL DEFAULT '0.00',
  `tax_total` decimal(15,2) NOT NULL DEFAULT '0.00',
  `total_amount` decimal(15,2) NOT NULL DEFAULT '0.00',
  `notes` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `terms_conditions` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `quotes_quote_number_unique` (`quote_number`),
  KEY `quotes_client_id_foreign` (`client_id`),
  KEY `quotes_lead_id_foreign` (`lead_id`),
  KEY `quotes_created_by_foreign` (`created_by`),
  CONSTRAINT `quotes_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`),
  CONSTRAINT `quotes_lead_id_foreign` FOREIGN KEY (`lead_id`) REFERENCES `leads` (`id`),
  CONSTRAINT `quotes_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- -----------------------------------------------------
-- Table `vehicles`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `vehicles`;
CREATE TABLE `vehicles` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `make` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `year` int(11) NOT NULL,
  `vin` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `color` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `condition` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'used',
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'in_stock',
  `purchase_price` decimal(15,2) DEFAULT NULL,
  `sale_price` decimal(15,2) DEFAULT NULL,
  `purchase_date` date DEFAULT NULL,
  `sale_date` date DEFAULT NULL,
  `buyer_id` bigint(20) unsigned DEFAULT NULL,
  `supplier_id` bigint(20) unsigned DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `vehicles_vin_unique` (`vin`),
  KEY `vehicles_buyer_id_foreign` (`buyer_id`),
  KEY `vehicles_supplier_id_foreign` (`supplier_id`),
  CONSTRAINT `vehicles_buyer_id_foreign` FOREIGN KEY (`buyer_id`) REFERENCES `clients` (`id`),
  CONSTRAINT `vehicles_supplier_id_foreign` FOREIGN KEY (`supplier_id`) REFERENCES `clients` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Table `tasks`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `tasks`;
CREATE TABLE `tasks` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `taskable_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `taskable_id` bigint(20) unsigned DEFAULT NULL,
  `assigned_to` bigint(20) unsigned DEFAULT NULL,
  `assigned_by` bigint(20) unsigned DEFAULT NULL,
  `priority` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'medium',
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'new',
  `due_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tasks_taskable_type_taskable_id_index` (`taskable_type`,`taskable_id`),
  KEY `tasks_assigned_to_foreign` (`assigned_to`),
  KEY `tasks_assigned_by_foreign` (`assigned_by`),
  CONSTRAINT `tasks_assigned_to_foreign` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`),
  CONSTRAINT `tasks_assigned_by_foreign` FOREIGN KEY (`assigned_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- -----------------------------------------------------
-- Table `settings` (Moved to end)
-- -----------------------------------------------------
DROP TABLE IF EXISTS `settings`;
CREATE TABLE `settings` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'string',
  `group` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'general',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Default Data & Legacy Import
-- -----------------------------------------------------

-- Users (Default Super Admin if not exists)
INSERT IGNORE INTO `users` (`id`, `name`, `email`, `password`, `created_at`, `updated_at`) VALUES
(1, 'Super Admin', 'admin@adgod-os.com', '$2y$10$PL6r3169COEZCV7a2QumieflBy6GiZi4W63jN1Vy5Gq6yrovd1fuu', NOW(), NOW());

-- Permissions (Basic set)
INSERT IGNORE INTO `permissions` (`name`, `guard_name`, `created_at`, `updated_at`) VALUES
('view_admin', 'web', NOW(), NOW()),
('manage_users', 'web', NOW(), NOW());

-- Roles
INSERT IGNORE INTO `roles` (`name`, `guard_name`, `created_at`, `updated_at`) VALUES
('Super Admin', 'web', NOW(), NOW()),
('User', 'web', NOW(), NOW());

-- Assign Role
INSERT IGNORE INTO `model_has_roles` (`role_id`, `model_type`, `model_id`) VALUES
(1, 'App\\Models\\User', 1);

-- Settings
INSERT IGNORE INTO `settings` (`key`, `value`, `type`, `group`, `created_at`, `updated_at`) VALUES 
('app_name', 'Shipping and Logistics OS', 'string', 'general', NOW(), NOW());


SET FOREIGN_KEY_CHECKS=1;
-- MySQL Dump generated by ADGOD-OS
-- Date: 2026-01-12 19:32:12

SET FOREIGN_KEY_CHECKS=0;

INSERT INTO `clients` (`id`, `type`, `company_name`, `contact_person`, `email`, `phone`, `secondary_phone`, `address`, `city`, `state_province`, `country`, `postal_code`, `tax_id`, `notes`, `source`, `is_active`, `created_by`, `created_at`, `updated_at`, `deleted_at`) VALUES ('1', 'supplier', 'Alex Inc', 'Alex', 'lxlbxl@gmail.com', '+1234456789', NULL, 'My Address', 'New York', NULL, 'USA', NULL, NULL, NULL, 'direct', '1', '3', '2025-12-18 23:32:25', '2025-12-18 23:32:25', NULL);
INSERT INTO `clients` (`id`, `type`, `company_name`, `contact_person`, `email`, `phone`, `secondary_phone`, `address`, `city`, `state_province`, `country`, `postal_code`, `tax_id`, `notes`, `source`, `is_active`, `created_by`, `created_at`, `updated_at`, `deleted_at`) VALUES ('2', 'buyer', NULL, 'ogutuga ibidapo', NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'legacy_import', '1', NULL, '2026-01-12 19:20:51', '2026-01-12 19:20:51', NULL);
INSERT INTO `clients` (`id`, `type`, `company_name`, `contact_person`, `email`, `phone`, `secondary_phone`, `address`, `city`, `state_province`, `country`, `postal_code`, `tax_id`, `notes`, `source`, `is_active`, `created_by`, `created_at`, `updated_at`, `deleted_at`) VALUES ('3', 'buyer', NULL, 'Olatunji Adeniran', NULL, '(289) 990-6944', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'legacy_import', '1', NULL, '2026-01-12 19:21:20', '2026-01-12 19:21:20', NULL);
INSERT INTO `clients` (`id`, `type`, `company_name`, `contact_person`, `email`, `phone`, `secondary_phone`, `address`, `city`, `state_province`, `country`, `postal_code`, `tax_id`, `notes`, `source`, `is_active`, `created_by`, `created_at`, `updated_at`, `deleted_at`) VALUES ('4', 'buyer', NULL, 'fowowe autos', NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'legacy_import', '1', NULL, '2026-01-12 19:21:20', '2026-01-12 19:21:20', NULL);
INSERT INTO `clients` (`id`, `type`, `company_name`, `contact_person`, `email`, `phone`, `secondary_phone`, `address`, `city`, `state_province`, `country`, `postal_code`, `tax_id`, `notes`, `source`, `is_active`, `created_by`, `created_at`, `updated_at`, `deleted_at`) VALUES ('5', 'buyer', NULL, 'JAYMANUEL', NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'legacy_import', '1', NULL, '2026-01-12 19:21:20', '2026-01-12 19:21:20', NULL);
INSERT INTO `clients` (`id`, `type`, `company_name`, `contact_person`, `email`, `phone`, `secondary_phone`, `address`, `city`, `state_province`, `country`, `postal_code`, `tax_id`, `notes`, `source`, `is_active`, `created_by`, `created_at`, `updated_at`, `deleted_at`) VALUES ('6', 'buyer', NULL, 'kelvin eze', NULL, '(647) 771-2966', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'legacy_import', '1', NULL, '2026-01-12 19:21:20', '2026-01-12 19:21:20', NULL);
INSERT INTO `clients` (`id`, `type`, `company_name`, `contact_person`, `email`, `phone`, `secondary_phone`, `address`, `city`, `state_province`, `country`, `postal_code`, `tax_id`, `notes`, `source`, `is_active`, `created_by`, `created_at`, `updated_at`, `deleted_at`) VALUES ('7', 'buyer', NULL, 'OYEBAMIJI ISSA OLALEKAN', NULL, '234 809 136 2731', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'legacy_import', '1', NULL, '2026-01-12 19:21:20', '2026-01-12 19:21:20', NULL);
INSERT INTO `clients` (`id`, `type`, `company_name`, `contact_person`, `email`, `phone`, `secondary_phone`, `address`, `city`, `state_province`, `country`, `postal_code`, `tax_id`, `notes`, `source`, `is_active`, `created_by`, `created_at`, `updated_at`, `deleted_at`) VALUES ('8', 'buyer', NULL, 'mapleby', NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'legacy_import', '1', NULL, '2026-01-12 19:21:20', '2026-01-12 19:21:20', NULL);
INSERT INTO `clients` (`id`, `type`, `company_name`, `contact_person`, `email`, `phone`, `secondary_phone`, `address`, `city`, `state_province`, `country`, `postal_code`, `tax_id`, `notes`, `source`, `is_active`, `created_by`, `created_at`, `updated_at`, `deleted_at`) VALUES ('9', 'buyer', NULL, 'Olufemi Anthony', NULL, '(437) 235-8442', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'legacy_import', '1', NULL, '2026-01-12 19:21:20', '2026-01-12 19:21:20', NULL);
INSERT INTO `clients` (`id`, `type`, `company_name`, `contact_person`, `email`, `phone`, `secondary_phone`, `address`, `city`, `state_province`, `country`, `postal_code`, `tax_id`, `notes`, `source`, `is_active`, `created_by`, `created_at`, `updated_at`, `deleted_at`) VALUES ('10', 'buyer', NULL, 'omoruyi edobor', NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'legacy_import', '1', NULL, '2026-01-12 19:21:20', '2026-01-12 19:21:20', NULL);
INSERT INTO `clients` (`id`, `type`, `company_name`, `contact_person`, `email`, `phone`, `secondary_phone`, `address`, `city`, `state_province`, `country`, `postal_code`, `tax_id`, `notes`, `source`, `is_active`, `created_by`, `created_at`, `updated_at`, `deleted_at`) VALUES ('11', 'buyer', NULL, 'anfela alabi', NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'legacy_import', '1', NULL, '2026-01-12 19:21:20', '2026-01-12 19:21:20', NULL);
INSERT INTO `clients` (`id`, `type`, `company_name`, `contact_person`, `email`, `phone`, `secondary_phone`, `address`, `city`, `state_province`, `country`, `postal_code`, `tax_id`, `notes`, `source`, `is_active`, `created_by`, `created_at`, `updated_at`, `deleted_at`) VALUES ('12', 'buyer', NULL, 'OSAYANMO MARVIS OSAGIEDE', NULL, ' (437) 217-1549', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'legacy_import', '1', NULL, '2026-01-12 19:21:20', '2026-01-12 19:21:20', NULL);
INSERT INTO `clients` (`id`, `type`, `company_name`, `contact_person`, `email`, `phone`, `secondary_phone`, `address`, `city`, `state_province`, `country`, `postal_code`, `tax_id`, `notes`, `source`, `is_active`, `created_by`, `created_at`, `updated_at`, `deleted_at`) VALUES ('13', 'buyer', NULL, 'adebayo babajide', NULL, '(416) 857-2557', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'legacy_import', '1', NULL, '2026-01-12 19:21:20', '2026-01-12 19:21:20', NULL);
INSERT INTO `clients` (`id`, `type`, `company_name`, `contact_person`, `email`, `phone`, `secondary_phone`, `address`, `city`, `state_province`, `country`, `postal_code`, `tax_id`, `notes`, `source`, `is_active`, `created_by`, `created_at`, `updated_at`, `deleted_at`) VALUES ('14', 'buyer', NULL, 'michael izekor', NULL, '(437) 246-6141', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'legacy_import', '1', NULL, '2026-01-12 19:21:20', '2026-01-12 19:21:20', NULL);
INSERT INTO `clients` (`id`, `type`, `company_name`, `contact_person`, `email`, `phone`, `secondary_phone`, `address`, `city`, `state_province`, `country`, `postal_code`, `tax_id`, `notes`, `source`, `is_active`, `created_by`, `created_at`, `updated_at`, `deleted_at`) VALUES ('15', 'buyer', NULL, 'yusuf sadiq', NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'legacy_import', '1', NULL, '2026-01-12 19:21:20', '2026-01-12 19:21:20', NULL);
INSERT INTO `clients` (`id`, `type`, `company_name`, `contact_person`, `email`, `phone`, `secondary_phone`, `address`, `city`, `state_province`, `country`, `postal_code`, `tax_id`, `notes`, `source`, `is_active`, `created_by`, `created_at`, `updated_at`, `deleted_at`) VALUES ('16', 'buyer', NULL, 'mayclan', NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'legacy_import', '1', NULL, '2026-01-12 19:21:20', '2026-01-12 19:21:20', NULL);
INSERT INTO `clients` (`id`, `type`, `company_name`, `contact_person`, `email`, `phone`, `secondary_phone`, `address`, `city`, `state_province`, `country`, `postal_code`, `tax_id`, `notes`, `source`, `is_active`, `created_by`, `created_at`, `updated_at`, `deleted_at`) VALUES ('17', 'buyer', NULL, 'Oluranti Sandra Franklin', NULL, '234 803 232 4242', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'legacy_import', '1', NULL, '2026-01-12 19:21:20', '2026-01-12 19:21:20', NULL);
INSERT INTO `clients` (`id`, `type`, `company_name`, `contact_person`, `email`, `phone`, `secondary_phone`, `address`, `city`, `state_province`, `country`, `postal_code`, `tax_id`, `notes`, `source`, `is_active`, `created_by`, `created_at`, `updated_at`, `deleted_at`) VALUES ('18', 'buyer', NULL, 'abimboye regina', NULL, '234 802 334 0845', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'legacy_import', '1', NULL, '2026-01-12 19:21:20', '2026-01-12 19:21:20', NULL);
INSERT INTO `clients` (`id`, `type`, `company_name`, `contact_person`, `email`, `phone`, `secondary_phone`, `address`, `city`, `state_province`, `country`, `postal_code`, `tax_id`, `notes`, `source`, `is_active`, `created_by`, `created_at`, `updated_at`, `deleted_at`) VALUES ('19', 'buyer', NULL, 'taofeek (tao)', NULL, '(437) 432-0125', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'legacy_import', '1', NULL, '2026-01-12 19:21:20', '2026-01-12 19:21:20', NULL);
INSERT INTO `clients` (`id`, `type`, `company_name`, `contact_person`, `email`, `phone`, `secondary_phone`, `address`, `city`, `state_province`, `country`, `postal_code`, `tax_id`, `notes`, `source`, `is_active`, `created_by`, `created_at`, `updated_at`, `deleted_at`) VALUES ('20', 'buyer', NULL, 'taofeek', NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'legacy_import', '1', NULL, '2026-01-12 19:21:20', '2026-01-12 19:21:20', NULL);
INSERT INTO `clients` (`id`, `type`, `company_name`, `contact_person`, `email`, `phone`, `secondary_phone`, `address`, `city`, `state_province`, `country`, `postal_code`, `tax_id`, `notes`, `source`, `is_active`, `created_by`, `created_at`, `updated_at`, `deleted_at`) VALUES ('21', 'buyer', NULL, 'entol', NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'legacy_import', '1', NULL, '2026-01-12 19:21:20', '2026-01-12 19:21:20', NULL);
INSERT INTO `clients` (`id`, `type`, `company_name`, `contact_person`, `email`, `phone`, `secondary_phone`, `address`, `city`, `state_province`, `country`, `postal_code`, `tax_id`, `notes`, `source`, `is_active`, `created_by`, `created_at`, `updated_at`, `deleted_at`) VALUES ('22', 'buyer', NULL, 'BAYO ADEDOSU', NULL, '(647) 238-3796', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'legacy_import', '1', NULL, '2026-01-12 19:21:20', '2026-01-12 19:21:20', NULL);
INSERT INTO `clients` (`id`, `type`, `company_name`, `contact_person`, `email`, `phone`, `secondary_phone`, `address`, `city`, `state_province`, `country`, `postal_code`, `tax_id`, `notes`, `source`, `is_active`, `created_by`, `created_at`, `updated_at`, `deleted_at`) VALUES ('23', 'buyer', NULL, 'anayochukwu ben ihekgwara', NULL, '(613) 762-4902', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'legacy_import', '1', NULL, '2026-01-12 19:21:20', '2026-01-12 19:21:20', NULL);
INSERT INTO `clients` (`id`, `type`, `company_name`, `contact_person`, `email`, `phone`, `secondary_phone`, `address`, `city`, `state_province`, `country`, `postal_code`, `tax_id`, `notes`, `source`, `is_active`, `created_by`, `created_at`, `updated_at`, `deleted_at`) VALUES ('24', 'buyer', NULL, 'benny london', NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'legacy_import', '1', NULL, '2026-01-12 19:21:20', '2026-01-12 19:21:20', NULL);
INSERT INTO `clients` (`id`, `type`, `company_name`, `contact_person`, `email`, `phone`, `secondary_phone`, `address`, `city`, `state_province`, `country`, `postal_code`, `tax_id`, `notes`, `source`, `is_active`, `created_by`, `created_at`, `updated_at`, `deleted_at`) VALUES ('25', 'buyer', NULL, 'anfel alabi', NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'legacy_import', '1', NULL, '2026-01-12 19:21:20', '2026-01-12 19:21:20', NULL);
INSERT INTO `clients` (`id`, `type`, `company_name`, `contact_person`, `email`, `phone`, `secondary_phone`, `address`, `city`, `state_province`, `country`, `postal_code`, `tax_id`, `notes`, `source`, `is_active`, `created_by`, `created_at`, `updated_at`, `deleted_at`) VALUES ('26', 'buyer', NULL, 'Olatunji (OLA)', NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'legacy_import', '1', NULL, '2026-01-12 19:21:20', '2026-01-12 19:21:20', NULL);
INSERT INTO `clients` (`id`, `type`, `company_name`, `contact_person`, `email`, `phone`, `secondary_phone`, `address`, `city`, `state_province`, `country`, `postal_code`, `tax_id`, `notes`, `source`, `is_active`, `created_by`, `created_at`, `updated_at`, `deleted_at`) VALUES ('27', 'buyer', NULL, 'olatunji', NULL, '(647) 535-3909', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'legacy_import', '1', NULL, '2026-01-12 19:21:20', '2026-01-12 19:21:20', NULL);
INSERT INTO `clients` (`id`, `type`, `company_name`, `contact_person`, `email`, `phone`, `secondary_phone`, `address`, `city`, `state_province`, `country`, `postal_code`, `tax_id`, `notes`, `source`, `is_active`, `created_by`, `created_at`, `updated_at`, `deleted_at`) VALUES ('28', 'buyer', NULL, 'oyedekun taiwo', NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'legacy_import', '1', NULL, '2026-01-12 19:21:20', '2026-01-12 19:21:20', NULL);
INSERT INTO `clients` (`id`, `type`, `company_name`, `contact_person`, `email`, `phone`, `secondary_phone`, `address`, `city`, `state_province`, `country`, `postal_code`, `tax_id`, `notes`, `source`, `is_active`, `created_by`, `created_at`, `updated_at`, `deleted_at`) VALUES ('29', 'buyer', NULL, 'HARRIET MAC-ASORE', NULL, '(437) 484-3506', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'legacy_import', '1', NULL, '2026-01-12 19:21:20', '2026-01-12 19:21:20', NULL);
INSERT INTO `clients` (`id`, `type`, `company_name`, `contact_person`, `email`, `phone`, `secondary_phone`, `address`, `city`, `state_province`, `country`, `postal_code`, `tax_id`, `notes`, `source`, `is_active`, `created_by`, `created_at`, `updated_at`, `deleted_at`) VALUES ('30', 'buyer', NULL, 'Mohamadou Mucktarr Conteh', NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'legacy_import', '1', NULL, '2026-01-12 19:21:20', '2026-01-12 19:21:20', NULL);
INSERT INTO `clients` (`id`, `type`, `company_name`, `contact_person`, `email`, `phone`, `secondary_phone`, `address`, `city`, `state_province`, `country`, `postal_code`, `tax_id`, `notes`, `source`, `is_active`, `created_by`, `created_at`, `updated_at`, `deleted_at`) VALUES ('31', 'buyer', NULL, 'Christopher Ighalo', NULL, '(647) 855-8985', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'legacy_import', '1', NULL, '2026-01-12 19:21:20', '2026-01-12 19:21:20', NULL);
INSERT INTO `clients` (`id`, `type`, `company_name`, `contact_person`, `email`, `phone`, `secondary_phone`, `address`, `city`, `state_province`, `country`, `postal_code`, `tax_id`, `notes`, `source`, `is_active`, `created_by`, `created_at`, `updated_at`, `deleted_at`) VALUES ('32', 'buyer', NULL, 'damilare samuel shobowale', NULL, '(647) 531-5010', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'legacy_import', '1', NULL, '2026-01-12 19:21:20', '2026-01-12 19:21:20', NULL);
INSERT INTO `clients` (`id`, `type`, `company_name`, `contact_person`, `email`, `phone`, `secondary_phone`, `address`, `city`, `state_province`, `country`, `postal_code`, `tax_id`, `notes`, `source`, `is_active`, `created_by`, `created_at`, `updated_at`, `deleted_at`) VALUES ('33', 'buyer', NULL, 'id', NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'legacy_import', '1', NULL, '2026-01-12 19:21:20', '2026-01-12 19:21:20', NULL);
INSERT INTO `clients` (`id`, `type`, `company_name`, `contact_person`, `email`, `phone`, `secondary_phone`, `address`, `city`, `state_province`, `country`, `postal_code`, `tax_id`, `notes`, `source`, `is_active`, `created_by`, `created_at`, `updated_at`, `deleted_at`) VALUES ('34', 'buyer', NULL, 'Zeyid Abdul Salam', NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'legacy_import', '1', NULL, '2026-01-12 19:21:20', '2026-01-12 19:21:20', NULL);
INSERT INTO `clients` (`id`, `type`, `company_name`, `contact_person`, `email`, `phone`, `secondary_phone`, `address`, `city`, `state_province`, `country`, `postal_code`, `tax_id`, `notes`, `source`, `is_active`, `created_by`, `created_at`, `updated_at`, `deleted_at`) VALUES ('35', 'buyer', NULL, 'DAMILARE SAMUEL SHOBOWALE', NULL, '(647) 531-5010', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'legacy_import', '1', NULL, '2026-01-12 19:21:20', '2026-01-12 19:21:20', NULL);
INSERT INTO `clients` (`id`, `type`, `company_name`, `contact_person`, `email`, `phone`, `secondary_phone`, `address`, `city`, `state_province`, `country`, `postal_code`, `tax_id`, `notes`, `source`, `is_active`, `created_by`, `created_at`, `updated_at`, `deleted_at`) VALUES ('36', 'buyer', NULL, 'YUSUF SADIQ', NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'legacy_import', '1', NULL, '2026-01-12 19:21:20', '2026-01-12 19:21:20', NULL);
INSERT INTO `clients` (`id`, `type`, `company_name`, `contact_person`, `email`, `phone`, `secondary_phone`, `address`, `city`, `state_province`, `country`, `postal_code`, `tax_id`, `notes`, `source`, `is_active`, `created_by`, `created_at`, `updated_at`, `deleted_at`) VALUES ('37', 'buyer', NULL, 'FESTUS', NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'legacy_import', '1', NULL, '2026-01-12 19:21:20', '2026-01-12 19:21:20', NULL);
INSERT INTO `clients` (`id`, `type`, `company_name`, `contact_person`, `email`, `phone`, `secondary_phone`, `address`, `city`, `state_province`, `country`, `postal_code`, `tax_id`, `notes`, `source`, `is_active`, `created_by`, `created_at`, `updated_at`, `deleted_at`) VALUES ('38', 'buyer', NULL, 'crestview', NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'legacy_import', '1', NULL, '2026-01-12 19:21:20', '2026-01-12 19:21:20', NULL);
INSERT INTO `clients` (`id`, `type`, `company_name`, `contact_person`, `email`, `phone`, `secondary_phone`, `address`, `city`, `state_province`, `country`, `postal_code`, `tax_id`, `notes`, `source`, `is_active`, `created_by`, `created_at`, `updated_at`, `deleted_at`) VALUES ('39', 'buyer', NULL, 'MAPLEBY', NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'legacy_import', '1', NULL, '2026-01-12 19:21:20', '2026-01-12 19:21:20', NULL);
INSERT INTO `clients` (`id`, `type`, `company_name`, `contact_person`, `email`, `phone`, `secondary_phone`, `address`, `city`, `state_province`, `country`, `postal_code`, `tax_id`, `notes`, `source`, `is_active`, `created_by`, `created_at`, `updated_at`, `deleted_at`) VALUES ('40', 'buyer', NULL, 'jegede', NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'legacy_import', '1', NULL, '2026-01-12 19:21:20', '2026-01-12 19:21:20', NULL);
INSERT INTO `clients` (`id`, `type`, `company_name`, `contact_person`, `email`, `phone`, `secondary_phone`, `address`, `city`, `state_province`, `country`, `postal_code`, `tax_id`, `notes`, `source`, `is_active`, `created_by`, `created_at`, `updated_at`, `deleted_at`) VALUES ('41', 'buyer', NULL, 'akinyemi olutobi', NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'legacy_import', '1', NULL, '2026-01-12 19:21:20', '2026-01-12 19:21:20', NULL);
INSERT INTO `clients` (`id`, `type`, `company_name`, `contact_person`, `email`, `phone`, `secondary_phone`, `address`, `city`, `state_province`, `country`, `postal_code`, `tax_id`, `notes`, `source`, `is_active`, `created_by`, `created_at`, `updated_at`, `deleted_at`) VALUES ('42', 'buyer', NULL, 'omar saidy', NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'legacy_import', '1', NULL, '2026-01-12 19:21:20', '2026-01-12 19:21:20', NULL);

INSERT INTO `shipments` (`id`, `shipment_number`, `quote_id`, `client_id`, `service_type`, `status`, `title`, `description`, `origin`, `destination`, `estimated_completion`, `actual_completion`, `total_value`, `invoice_number`, `invoice_status`, `payment_status`, `assigned_to`, `priority`, `notes`, `created_at`, `updated_at`, `deleted_at`, `vin`, `vehicle_year`, `vehicle_make`, `vehicle_model`, `vehicle_color`, `tow_required`, `driver_name`, `ownership_documents`, `date_received`, `location_notes`) VALUES ('1', 'SHP-O3D81AKL', NULL, '1', 'Car Shipment', 'pending', 'Camry for Ade', NULL, 'Ontario', 'Lagos', '2026-01-19', NULL, '20000', NULL, 'pending', 'unpaid', '1', 'medium', NULL, '2025-12-18 23:34:43', '2026-01-11 12:45:01', NULL, NULL, NULL, NULL, NULL, NULL, '0', NULL, '0', NULL, NULL);
INSERT INTO `shipments` (`id`, `shipment_number`, `quote_id`, `client_id`, `service_type`, `status`, `title`, `description`, `origin`, `destination`, `estimated_completion`, `actual_completion`, `total_value`, `invoice_number`, `invoice_status`, `payment_status`, `assigned_to`, `priority`, `notes`, `created_at`, `updated_at`, `deleted_at`, `vin`, `vehicle_year`, `vehicle_make`, `vehicle_model`, `vehicle_color`, `tow_required`, `driver_name`, `ownership_documents`, `date_received`, `location_notes`) VALUES ('2', 'SHP-R6KEO7IR', NULL, '2', 'CONTAINER', 'draft', '2009 Toyota Tacoma', ' ', NULL, NULL, NULL, NULL, NULL, NULL, 'pending', 'unpaid', NULL, 'medium', NULL, '2026-01-12 19:21:20', '2026-01-12 19:21:20', NULL, '5TEMU52N19Z610465', '2009', 'Toyota', 'Tacoma', '', '1', 'success', '0', '2026-01-05 00:00:00', '');
INSERT INTO `shipments` (`id`, `shipment_number`, `quote_id`, `client_id`, `service_type`, `status`, `title`, `description`, `origin`, `destination`, `estimated_completion`, `actual_completion`, `total_value`, `invoice_number`, `invoice_status`, `payment_status`, `assigned_to`, `priority`, `notes`, `created_at`, `updated_at`, `deleted_at`, `vin`, `vehicle_year`, `vehicle_make`, `vehicle_model`, `vehicle_color`, `tow_required`, `driver_name`, `ownership_documents`, `date_received`, `location_notes`) VALUES ('3', 'SHP-NLSV50L9', NULL, '3', 'CONTAINER', 'draft', '2022 Toyota RAV4', ' ', NULL, NULL, NULL, NULL, NULL, NULL, 'pending', 'unpaid', NULL, 'medium', NULL, '2026-01-12 19:21:20', '2026-01-12 19:21:20', NULL, ' 2T3D1RFV8NC276663', '2022', 'Toyota', 'RAV4', '', '0', '', '1', '2026-01-05 00:00:00', 'WASSAP');
INSERT INTO `shipments` (`id`, `shipment_number`, `quote_id`, `client_id`, `service_type`, `status`, `title`, `description`, `origin`, `destination`, `estimated_completion`, `actual_completion`, `total_value`, `invoice_number`, `invoice_status`, `payment_status`, `assigned_to`, `priority`, `notes`, `created_at`, `updated_at`, `deleted_at`, `vin`, `vehicle_year`, `vehicle_make`, `vehicle_model`, `vehicle_color`, `tow_required`, `driver_name`, `ownership_documents`, `date_received`, `location_notes`) VALUES ('4', 'SHP-RHIINHJ5', NULL, '4', 'CONTAINER', 'draft', '2009 Toyota Venza', ' ', NULL, NULL, NULL, NULL, NULL, NULL, 'pending', 'unpaid', NULL, 'medium', NULL, '2026-01-12 19:21:20', '2026-01-12 19:21:20', NULL, '4T3ZE11A79U019854', '2009', 'Toyota', 'Venza', '', '0', '', '1', '2026-01-05 00:00:00', 'WASSAP');
INSERT INTO `shipments` (`id`, `shipment_number`, `quote_id`, `client_id`, `service_type`, `status`, `title`, `description`, `origin`, `destination`, `estimated_completion`, `actual_completion`, `total_value`, `invoice_number`, `invoice_status`, `payment_status`, `assigned_to`, `priority`, `notes`, `created_at`, `updated_at`, `deleted_at`, `vin`, `vehicle_year`, `vehicle_make`, `vehicle_model`, `vehicle_color`, `tow_required`, `driver_name`, `ownership_documents`, `date_received`, `location_notes`) VALUES ('5', 'SHP-Y7X7Y51G', NULL, '5', 'CONTAINER', 'draft', '2015 Chevrolet Camaro', ' ', NULL, NULL, NULL, NULL, NULL, NULL, 'pending', 'unpaid', NULL, 'medium', NULL, '2026-01-12 19:21:20', '2026-01-12 19:21:20', NULL, ' 2G1FF1E31F9270847', '2015', 'Chevrolet', 'Camaro', '', '1', '', '1', '2026-01-05 00:00:00', 'WASSAP');
INSERT INTO `shipments` (`id`, `shipment_number`, `quote_id`, `client_id`, `service_type`, `status`, `title`, `description`, `origin`, `destination`, `estimated_completion`, `actual_completion`, `total_value`, `invoice_number`, `invoice_status`, `payment_status`, `assigned_to`, `priority`, `notes`, `created_at`, `updated_at`, `deleted_at`, `vin`, `vehicle_year`, `vehicle_make`, `vehicle_model`, `vehicle_color`, `tow_required`, `driver_name`, `ownership_documents`, `date_received`, `location_notes`) VALUES ('6', 'SHP-O3C8AQ6E', NULL, '6', 'ADGOD', 'pending', '2020 Toyota Camry', ' ', NULL, NULL, NULL, NULL, NULL, NULL, 'pending', 'unpaid', NULL, 'medium', NULL, '2026-01-12 19:21:20', '2026-01-12 19:21:20', NULL, ' 4T1K61AK1LU314133', '2020', 'Toyota', 'Camry', '', '1', 'junior', '1', '2026-01-05 00:00:00', 'WASSAP');
INSERT INTO `shipments` (`id`, `shipment_number`, `quote_id`, `client_id`, `service_type`, `status`, `title`, `description`, `origin`, `destination`, `estimated_completion`, `actual_completion`, `total_value`, `invoice_number`, `invoice_status`, `payment_status`, `assigned_to`, `priority`, `notes`, `created_at`, `updated_at`, `deleted_at`, `vin`, `vehicle_year`, `vehicle_make`, `vehicle_model`, `vehicle_color`, `tow_required`, `driver_name`, `ownership_documents`, `date_received`, `location_notes`) VALUES ('7', 'SHP-MNKMHV0H', NULL, '7', 'ADGOD', 'draft', '2016 Honda CR-V', ' ', NULL, NULL, NULL, NULL, NULL, NULL, 'pending', 'unpaid', NULL, 'medium', NULL, '2026-01-12 19:21:20', '2026-01-12 19:21:20', NULL, '5J6RM4H94GL800163', '2016', 'Honda', 'CR-V', '', '1', 'SAM - 647-770-2851', '1', '2026-01-06 00:00:00', 'FILE');
INSERT INTO `shipments` (`id`, `shipment_number`, `quote_id`, `client_id`, `service_type`, `status`, `title`, `description`, `origin`, `destination`, `estimated_completion`, `actual_completion`, `total_value`, `invoice_number`, `invoice_status`, `payment_status`, `assigned_to`, `priority`, `notes`, `created_at`, `updated_at`, `deleted_at`, `vin`, `vehicle_year`, `vehicle_make`, `vehicle_model`, `vehicle_color`, `tow_required`, `driver_name`, `ownership_documents`, `date_received`, `location_notes`) VALUES ('8', 'SHP-VZY9ACFC', NULL, '8', 'CONTAINER', 'draft', '2009 Toyota Sienna', ' ', NULL, NULL, NULL, NULL, NULL, NULL, 'pending', 'unpaid', NULL, 'medium', NULL, '2026-01-12 19:21:20', '2026-01-12 19:21:20', NULL, '5TDZK23CX9S283147', '2009', 'Toyota', 'Sienna', '', '1', 'PASTOR SUNDAY', '0', '2026-01-05 00:00:00', '');
INSERT INTO `shipments` (`id`, `shipment_number`, `quote_id`, `client_id`, `service_type`, `status`, `title`, `description`, `origin`, `destination`, `estimated_completion`, `actual_completion`, `total_value`, `invoice_number`, `invoice_status`, `payment_status`, `assigned_to`, `priority`, `notes`, `created_at`, `updated_at`, `deleted_at`, `vin`, `vehicle_year`, `vehicle_make`, `vehicle_model`, `vehicle_color`, `tow_required`, `driver_name`, `ownership_documents`, `date_received`, `location_notes`) VALUES ('9', 'SHP-3AO1UDPR', NULL, '8', 'CONTAINER', 'draft', '2012 Mercedes-Benz C-Class', ' ', NULL, NULL, NULL, NULL, NULL, NULL, 'pending', 'unpaid', NULL, 'medium', NULL, '2026-01-12 19:21:20', '2026-01-12 19:21:20', NULL, ' WDDGF8JB0CA605149', '2012', 'Mercedes-Benz', 'C-Class', '', '1', 'PASTOR SUNDAY', '0', '2026-01-05 00:00:00', '');
INSERT INTO `shipments` (`id`, `shipment_number`, `quote_id`, `client_id`, `service_type`, `status`, `title`, `description`, `origin`, `destination`, `estimated_completion`, `actual_completion`, `total_value`, `invoice_number`, `invoice_status`, `payment_status`, `assigned_to`, `priority`, `notes`, `created_at`, `updated_at`, `deleted_at`, `vin`, `vehicle_year`, `vehicle_make`, `vehicle_model`, `vehicle_color`, `tow_required`, `driver_name`, `ownership_documents`, `date_received`, `location_notes`) VALUES ('10', 'SHP-USYSPRXV', NULL, '8', 'CONTAINER', 'draft', '2013 Toyota Corolla', ' ', NULL, NULL, NULL, NULL, NULL, NULL, 'pending', 'unpaid', NULL, 'medium', NULL, '2026-01-12 19:21:20', '2026-01-12 19:21:20', NULL, ' 2T1BU4EE4DC026914', '2013', 'Toyota', 'Corolla', '', '1', 'PASTOR SUNDAY', '0', '2026-01-05 00:00:00', '');
INSERT INTO `shipments` (`id`, `shipment_number`, `quote_id`, `client_id`, `service_type`, `status`, `title`, `description`, `origin`, `destination`, `estimated_completion`, `actual_completion`, `total_value`, `invoice_number`, `invoice_status`, `payment_status`, `assigned_to`, `priority`, `notes`, `created_at`, `updated_at`, `deleted_at`, `vin`, `vehicle_year`, `vehicle_make`, `vehicle_model`, `vehicle_color`, `tow_required`, `driver_name`, `ownership_documents`, `date_received`, `location_notes`) VALUES ('11', 'SHP-K7YDQXBX', NULL, '9', 'ADGOD', 'pending', '2008 Lexus RX 350', ' ', NULL, NULL, NULL, NULL, NULL, NULL, 'pending', 'unpaid', NULL, 'medium', NULL, '2026-01-12 19:21:20', '2026-01-12 19:21:20', NULL, '2T2HK31U08C074385', '2008', 'Lexus', 'RX 350', '', '1', 'PASTOR SUNDAY', '1', '2026-01-06 00:00:00', 'WASSAP');
INSERT INTO `shipments` (`id`, `shipment_number`, `quote_id`, `client_id`, `service_type`, `status`, `title`, `description`, `origin`, `destination`, `estimated_completion`, `actual_completion`, `total_value`, `invoice_number`, `invoice_status`, `payment_status`, `assigned_to`, `priority`, `notes`, `created_at`, `updated_at`, `deleted_at`, `vin`, `vehicle_year`, `vehicle_make`, `vehicle_model`, `vehicle_color`, `tow_required`, `driver_name`, `ownership_documents`, `date_received`, `location_notes`) VALUES ('12', 'SHP-A30EKFAL', NULL, '10', 'CONTAINER', 'draft', '2017 Hyundai Santa Fe XL', ' ', NULL, NULL, NULL, NULL, NULL, NULL, 'pending', 'unpaid', NULL, 'medium', NULL, '2026-01-12 19:21:20', '2026-01-12 19:21:20', NULL, 'KM8SNDHF3HU222199', '2017', 'Hyundai', 'Santa Fe XL', '', '1', 'ola guy', '0', '2026-01-06 00:00:00', '');
INSERT INTO `shipments` (`id`, `shipment_number`, `quote_id`, `client_id`, `service_type`, `status`, `title`, `description`, `origin`, `destination`, `estimated_completion`, `actual_completion`, `total_value`, `invoice_number`, `invoice_status`, `payment_status`, `assigned_to`, `priority`, `notes`, `created_at`, `updated_at`, `deleted_at`, `vin`, `vehicle_year`, `vehicle_make`, `vehicle_model`, `vehicle_color`, `tow_required`, `driver_name`, `ownership_documents`, `date_received`, `location_notes`) VALUES ('13', 'SHP-BMZGDL50', NULL, '11', 'CONTAINER', 'draft', '2013 Hyundai Elantra', ' ', NULL, NULL, NULL, NULL, NULL, NULL, 'pending', 'unpaid', NULL, 'medium', NULL, '2026-01-12 19:21:20', '2026-01-12 19:21:20', NULL, '5NPDH4AE5DH151680', '2013', 'Hyundai', 'Elantra', '', '0', '', '1', '2026-01-06 00:00:00', 'WASSAP');
INSERT INTO `shipments` (`id`, `shipment_number`, `quote_id`, `client_id`, `service_type`, `status`, `title`, `description`, `origin`, `destination`, `estimated_completion`, `actual_completion`, `total_value`, `invoice_number`, `invoice_status`, `payment_status`, `assigned_to`, `priority`, `notes`, `created_at`, `updated_at`, `deleted_at`, `vin`, `vehicle_year`, `vehicle_make`, `vehicle_model`, `vehicle_color`, `tow_required`, `driver_name`, `ownership_documents`, `date_received`, `location_notes`) VALUES ('14', 'SHP-Y1FH5JW2', NULL, '8', 'CONTAINER', 'draft', '2011 Toyota Corolla', ' ', NULL, NULL, NULL, NULL, NULL, NULL, 'pending', 'unpaid', NULL, 'medium', NULL, '2026-01-12 19:21:20', '2026-01-12 19:21:20', NULL, '2T1BU4EE0BC736437', '2011', 'Toyota', 'Corolla', '', '1', 'zack', '0', '2026-01-06 00:00:00', '');
INSERT INTO `shipments` (`id`, `shipment_number`, `quote_id`, `client_id`, `service_type`, `status`, `title`, `description`, `origin`, `destination`, `estimated_completion`, `actual_completion`, `total_value`, `invoice_number`, `invoice_status`, `payment_status`, `assigned_to`, `priority`, `notes`, `created_at`, `updated_at`, `deleted_at`, `vin`, `vehicle_year`, `vehicle_make`, `vehicle_model`, `vehicle_color`, `tow_required`, `driver_name`, `ownership_documents`, `date_received`, `location_notes`) VALUES ('15', 'SHP-VJ5XVCNQ', NULL, '8', 'CONTAINER', 'draft', '2013 Hyundai Elantra', ' ', NULL, NULL, NULL, NULL, NULL, NULL, 'pending', 'unpaid', NULL, 'medium', NULL, '2026-01-12 19:21:20', '2026-01-12 19:21:20', NULL, '5NPDH4AE4DH432904', '2013', 'Hyundai', 'Elantra', '', '1', 'emmanuel', '0', '2026-01-06 00:00:00', '');
INSERT INTO `shipments` (`id`, `shipment_number`, `quote_id`, `client_id`, `service_type`, `status`, `title`, `description`, `origin`, `destination`, `estimated_completion`, `actual_completion`, `total_value`, `invoice_number`, `invoice_status`, `payment_status`, `assigned_to`, `priority`, `notes`, `created_at`, `updated_at`, `deleted_at`, `vin`, `vehicle_year`, `vehicle_make`, `vehicle_model`, `vehicle_color`, `tow_required`, `driver_name`, `ownership_documents`, `date_received`, `location_notes`) VALUES ('16', 'SHP-SJC6HFPI', NULL, '12', 'CONTAINER', 'draft', '2009 Toyota Venza', ' ', NULL, NULL, NULL, NULL, NULL, NULL, 'pending', 'unpaid', NULL, 'medium', NULL, '2026-01-12 19:21:20', '2026-01-12 19:21:20', NULL, '4T3BK11AX9U022094', '2009', 'Toyota', 'Venza', '', '1', 'mustapha', '0', '2026-01-06 00:00:00', '');
INSERT INTO `shipments` (`id`, `shipment_number`, `quote_id`, `client_id`, `service_type`, `status`, `title`, `description`, `origin`, `destination`, `estimated_completion`, `actual_completion`, `total_value`, `invoice_number`, `invoice_status`, `payment_status`, `assigned_to`, `priority`, `notes`, `created_at`, `updated_at`, `deleted_at`, `vin`, `vehicle_year`, `vehicle_make`, `vehicle_model`, `vehicle_color`, `tow_required`, `driver_name`, `ownership_documents`, `date_received`, `location_notes`) VALUES ('17', 'SHP-7CLRPJPJ', NULL, '13', 'ADGOD', 'pending', '2020 Toyota RAV4', ' ', NULL, NULL, NULL, NULL, NULL, NULL, 'pending', 'unpaid', NULL, 'medium', NULL, '2026-01-12 19:21:20', '2026-01-12 19:21:20', NULL, ' 2T3B1RFV0LC130634', '2020', 'Toyota', 'RAV4', '', '0', '', '1', '2026-01-06 00:00:00', 'WASSAP');
INSERT INTO `shipments` (`id`, `shipment_number`, `quote_id`, `client_id`, `service_type`, `status`, `title`, `description`, `origin`, `destination`, `estimated_completion`, `actual_completion`, `total_value`, `invoice_number`, `invoice_status`, `payment_status`, `assigned_to`, `priority`, `notes`, `created_at`, `updated_at`, `deleted_at`, `vin`, `vehicle_year`, `vehicle_make`, `vehicle_model`, `vehicle_color`, `tow_required`, `driver_name`, `ownership_documents`, `date_received`, `location_notes`) VALUES ('18', 'SHP-JT6LZIZH', NULL, '11', 'CONTAINER', 'draft', '2011 Hyundai Elantra', ' ', NULL, NULL, NULL, NULL, NULL, NULL, 'pending', 'unpaid', NULL, 'medium', NULL, '2026-01-12 19:21:20', '2026-01-12 19:21:20', NULL, '5NPDH4AE2BH057740', '2011', 'Hyundai', 'Elantra', '', '0', '', '1', '2026-01-06 00:00:00', '');
INSERT INTO `shipments` (`id`, `shipment_number`, `quote_id`, `client_id`, `service_type`, `status`, `title`, `description`, `origin`, `destination`, `estimated_completion`, `actual_completion`, `total_value`, `invoice_number`, `invoice_status`, `payment_status`, `assigned_to`, `priority`, `notes`, `created_at`, `updated_at`, `deleted_at`, `vin`, `vehicle_year`, `vehicle_make`, `vehicle_model`, `vehicle_color`, `tow_required`, `driver_name`, `ownership_documents`, `date_received`, `location_notes`) VALUES ('19', 'SHP-T92251MN', NULL, '14', 'ADGOD', 'pending', '2010 Mercedes-Benz GLK-Class', ' ', NULL, NULL, NULL, NULL, NULL, NULL, 'pending', 'unpaid', NULL, 'medium', NULL, '2026-01-12 19:21:20', '2026-01-12 19:21:20', NULL, ' WDCGG8HB8AF427112', '2010', 'Mercedes-Benz', 'GLK-Class', '', '0', '', '1', '2026-01-06 00:00:00', '');
INSERT INTO `shipments` (`id`, `shipment_number`, `quote_id`, `client_id`, `service_type`, `status`, `title`, `description`, `origin`, `destination`, `estimated_completion`, `actual_completion`, `total_value`, `invoice_number`, `invoice_status`, `payment_status`, `assigned_to`, `priority`, `notes`, `created_at`, `updated_at`, `deleted_at`, `vin`, `vehicle_year`, `vehicle_make`, `vehicle_model`, `vehicle_color`, `tow_required`, `driver_name`, `ownership_documents`, `date_received`, `location_notes`) VALUES ('20', 'SHP-9P3CZUSI', NULL, '15', 'CONTAINER', 'draft', '2014 Mercedes-Benz M-Class', ' ', NULL, NULL, NULL, NULL, NULL, NULL, 'pending', 'unpaid', NULL, 'medium', NULL, '2026-01-12 19:21:20', '2026-01-12 19:21:20', NULL, '4JGDA7EB3EA434427', '2014', 'Mercedes-Benz', 'M-Class', '', '1', '', '0', '2026-01-06 00:00:00', '');
INSERT INTO `shipments` (`id`, `shipment_number`, `quote_id`, `client_id`, `service_type`, `status`, `title`, `description`, `origin`, `destination`, `estimated_completion`, `actual_completion`, `total_value`, `invoice_number`, `invoice_status`, `payment_status`, `assigned_to`, `priority`, `notes`, `created_at`, `updated_at`, `deleted_at`, `vin`, `vehicle_year`, `vehicle_make`, `vehicle_model`, `vehicle_color`, `tow_required`, `driver_name`, `ownership_documents`, `date_received`, `location_notes`) VALUES ('21', 'SHP-TU5JGJ46', NULL, '8', 'CONTAINER', 'draft', '2009 Toyota Corolla', ' ', NULL, NULL, NULL, NULL, NULL, NULL, 'pending', 'unpaid', NULL, 'medium', NULL, '2026-01-12 19:21:20', '2026-01-12 19:21:20', NULL, '2T1BU40E69C011457', '2009', 'Toyota', 'Corolla', '', '1', '', '1', '2026-01-06 00:00:00', 'WASSAP');
INSERT INTO `shipments` (`id`, `shipment_number`, `quote_id`, `client_id`, `service_type`, `status`, `title`, `description`, `origin`, `destination`, `estimated_completion`, `actual_completion`, `total_value`, `invoice_number`, `invoice_status`, `payment_status`, `assigned_to`, `priority`, `notes`, `created_at`, `updated_at`, `deleted_at`, `vin`, `vehicle_year`, `vehicle_make`, `vehicle_model`, `vehicle_color`, `tow_required`, `driver_name`, `ownership_documents`, `date_received`, `location_notes`) VALUES ('22', 'SHP-ZSZUWVTP', NULL, '16', 'CONTAINER', 'draft', '2014 Lexus GX 460', ' ', NULL, NULL, NULL, NULL, NULL, NULL, 'pending', 'unpaid', NULL, 'medium', NULL, '2026-01-12 19:21:20', '2026-01-12 19:21:20', NULL, ' JTJJM7FX7E5066316', '2014', 'Lexus', 'GX 460', '', '0', '', '1', '2026-01-06 00:00:00', 'WASSAP');
INSERT INTO `shipments` (`id`, `shipment_number`, `quote_id`, `client_id`, `service_type`, `status`, `title`, `description`, `origin`, `destination`, `estimated_completion`, `actual_completion`, `total_value`, `invoice_number`, `invoice_status`, `payment_status`, `assigned_to`, `priority`, `notes`, `created_at`, `updated_at`, `deleted_at`, `vin`, `vehicle_year`, `vehicle_make`, `vehicle_model`, `vehicle_color`, `tow_required`, `driver_name`, `ownership_documents`, `date_received`, `location_notes`) VALUES ('23', 'SHP-DCPRPH3M', NULL, '8', 'CONTAINER', 'draft', '2010 Toyota RAV4', ' ', NULL, NULL, NULL, NULL, NULL, NULL, 'pending', 'unpaid', NULL, 'medium', NULL, '2026-01-12 19:21:20', '2026-01-12 19:21:20', NULL, ' 2T3ZF4DV6AW019054', '2010', 'Toyota', 'RAV4', '', '1', 'PASTOR SUNDAY', '0', '2026-01-06 00:00:00', '');
INSERT INTO `shipments` (`id`, `shipment_number`, `quote_id`, `client_id`, `service_type`, `status`, `title`, `description`, `origin`, `destination`, `estimated_completion`, `actual_completion`, `total_value`, `invoice_number`, `invoice_status`, `payment_status`, `assigned_to`, `priority`, `notes`, `created_at`, `updated_at`, `deleted_at`, `vin`, `vehicle_year`, `vehicle_make`, `vehicle_model`, `vehicle_color`, `tow_required`, `driver_name`, `ownership_documents`, `date_received`, `location_notes`) VALUES ('24', 'SHP-VV1E8ASQ', NULL, '17', 'CONTAINER', 'draft', '2009 Toyota Highlander', ' ', NULL, NULL, NULL, NULL, NULL, NULL, 'pending', 'unpaid', NULL, 'medium', NULL, '2026-01-12 19:21:20', '2026-01-12 19:21:20', NULL, 'JTEES43A692140823', '2009', 'Toyota', 'Highlander', '', '1', '', '0', '2026-01-06 00:00:00', '');
INSERT INTO `shipments` (`id`, `shipment_number`, `quote_id`, `client_id`, `service_type`, `status`, `title`, `description`, `origin`, `destination`, `estimated_completion`, `actual_completion`, `total_value`, `invoice_number`, `invoice_status`, `payment_status`, `assigned_to`, `priority`, `notes`, `created_at`, `updated_at`, `deleted_at`, `vin`, `vehicle_year`, `vehicle_make`, `vehicle_model`, `vehicle_color`, `tow_required`, `driver_name`, `ownership_documents`, `date_received`, `location_notes`) VALUES ('25', 'SHP-1OEU8Y3M', NULL, '8', 'CONTAINER', 'draft', '2013 Lexus RX 350', ' ', NULL, NULL, NULL, NULL, NULL, NULL, 'pending', 'unpaid', NULL, 'medium', NULL, '2026-01-12 19:21:20', '2026-01-12 19:21:20', NULL, '2T2BK1BA6DC202167', '2013', 'Lexus', 'RX 350', '', '1', 'initially was for syllabus', '0', '2026-01-07 00:00:00', '');
INSERT INTO `shipments` (`id`, `shipment_number`, `quote_id`, `client_id`, `service_type`, `status`, `title`, `description`, `origin`, `destination`, `estimated_completion`, `actual_completion`, `total_value`, `invoice_number`, `invoice_status`, `payment_status`, `assigned_to`, `priority`, `notes`, `created_at`, `updated_at`, `deleted_at`, `vin`, `vehicle_year`, `vehicle_make`, `vehicle_model`, `vehicle_color`, `tow_required`, `driver_name`, `ownership_documents`, `date_received`, `location_notes`) VALUES ('26', 'SHP-SAU8F9V1', NULL, '17', 'CONTAINER', 'draft', '2018 Lexus NX', ' ', NULL, NULL, NULL, NULL, NULL, NULL, 'pending', 'unpaid', NULL, 'medium', NULL, '2026-01-12 19:21:20', '2026-01-12 19:21:20', NULL, 'JTJBARBZ0J2152255', '2018', 'Lexus', 'NX', '', '1', '', '0', '2026-01-07 00:00:00', '');
INSERT INTO `shipments` (`id`, `shipment_number`, `quote_id`, `client_id`, `service_type`, `status`, `title`, `description`, `origin`, `destination`, `estimated_completion`, `actual_completion`, `total_value`, `invoice_number`, `invoice_status`, `payment_status`, `assigned_to`, `priority`, `notes`, `created_at`, `updated_at`, `deleted_at`, `vin`, `vehicle_year`, `vehicle_make`, `vehicle_model`, `vehicle_color`, `tow_required`, `driver_name`, `ownership_documents`, `date_received`, `location_notes`) VALUES ('27', 'SHP-6I8YJ6BS', NULL, '17', 'CONTAINER', 'draft', '2007 Toyota Camry', ' ', NULL, NULL, NULL, NULL, NULL, NULL, 'pending', 'unpaid', NULL, 'medium', NULL, '2026-01-12 19:21:20', '2026-01-12 19:21:20', NULL, '4T1BE46K37U136468', '2007', 'Toyota', 'Camry', '', '1', '', '0', '2026-01-07 00:00:00', '');
INSERT INTO `shipments` (`id`, `shipment_number`, `quote_id`, `client_id`, `service_type`, `status`, `title`, `description`, `origin`, `destination`, `estimated_completion`, `actual_completion`, `total_value`, `invoice_number`, `invoice_status`, `payment_status`, `assigned_to`, `priority`, `notes`, `created_at`, `updated_at`, `deleted_at`, `vin`, `vehicle_year`, `vehicle_make`, `vehicle_model`, `vehicle_color`, `tow_required`, `driver_name`, `ownership_documents`, `date_received`, `location_notes`) VALUES ('28', 'SHP-A1BHUAWX', NULL, '18', 'ADGOD', 'draft', '2018 Mercedes-Benz GLE', ' ', NULL, NULL, NULL, NULL, NULL, NULL, 'pending', 'unpaid', NULL, 'medium', NULL, '2026-01-12 19:21:20', '2026-01-12 19:21:20', NULL, ' 4JGDA5GB4JB097742', '2018', 'Mercedes-Benz', 'GLE', '', '1', '', '1', '2026-01-07 00:00:00', '');
INSERT INTO `shipments` (`id`, `shipment_number`, `quote_id`, `client_id`, `service_type`, `status`, `title`, `description`, `origin`, `destination`, `estimated_completion`, `actual_completion`, `total_value`, `invoice_number`, `invoice_status`, `payment_status`, `assigned_to`, `priority`, `notes`, `created_at`, `updated_at`, `deleted_at`, `vin`, `vehicle_year`, `vehicle_make`, `vehicle_model`, `vehicle_color`, `tow_required`, `driver_name`, `ownership_documents`, `date_received`, `location_notes`) VALUES ('29', 'SHP-XGUHYYVO', NULL, '19', 'CONTAINER', 'draft', '5TDZA29C06S488594', ' ', NULL, NULL, NULL, NULL, NULL, NULL, 'pending', 'unpaid', NULL, 'medium', NULL, '2026-01-12 19:21:20', '2026-01-12 19:21:20', NULL, '2006 Toyota Sienna', NULL, '5TDZA29C06S488594', NULL, '', '0', '', '1', '2026-01-07 00:00:00', 'WASSAP');
INSERT INTO `shipments` (`id`, `shipment_number`, `quote_id`, `client_id`, `service_type`, `status`, `title`, `description`, `origin`, `destination`, `estimated_completion`, `actual_completion`, `total_value`, `invoice_number`, `invoice_status`, `payment_status`, `assigned_to`, `priority`, `notes`, `created_at`, `updated_at`, `deleted_at`, `vin`, `vehicle_year`, `vehicle_make`, `vehicle_model`, `vehicle_color`, `tow_required`, `driver_name`, `ownership_documents`, `date_received`, `location_notes`) VALUES ('30', 'SHP-PBZPRUCT', NULL, '20', 'CONTAINER', 'draft', '2006 Toyota Matrix', ' ', NULL, NULL, NULL, NULL, NULL, NULL, 'pending', 'unpaid', NULL, 'medium', NULL, '2026-01-12 19:21:20', '2026-01-12 19:21:20', NULL, '2T1KR32E66C617362', '2006', 'Toyota', 'Matrix', '', '0', '', '1', '2026-01-07 00:00:00', 'WASSAP');
INSERT INTO `shipments` (`id`, `shipment_number`, `quote_id`, `client_id`, `service_type`, `status`, `title`, `description`, `origin`, `destination`, `estimated_completion`, `actual_completion`, `total_value`, `invoice_number`, `invoice_status`, `payment_status`, `assigned_to`, `priority`, `notes`, `created_at`, `updated_at`, `deleted_at`, `vin`, `vehicle_year`, `vehicle_make`, `vehicle_model`, `vehicle_color`, `tow_required`, `driver_name`, `ownership_documents`, `date_received`, `location_notes`) VALUES ('31', 'SHP-5KRSGYDA', NULL, '21', 'CONTAINER', 'draft', '2012 Toyota Camry', ' ', NULL, NULL, NULL, NULL, NULL, NULL, 'pending', 'unpaid', NULL, 'medium', NULL, '2026-01-12 19:21:20', '2026-01-12 19:21:20', NULL, '4T1BF1FK6CU119704', '2012', 'Toyota', 'Camry', '', '1', 'PASTOR SUNDAY', '0', '2026-01-07 00:00:00', '');
INSERT INTO `shipments` (`id`, `shipment_number`, `quote_id`, `client_id`, `service_type`, `status`, `title`, `description`, `origin`, `destination`, `estimated_completion`, `actual_completion`, `total_value`, `invoice_number`, `invoice_status`, `payment_status`, `assigned_to`, `priority`, `notes`, `created_at`, `updated_at`, `deleted_at`, `vin`, `vehicle_year`, `vehicle_make`, `vehicle_model`, `vehicle_color`, `tow_required`, `driver_name`, `ownership_documents`, `date_received`, `location_notes`) VALUES ('32', 'SHP-AMSGEFBE', NULL, '22', 'ADGOD', 'draft', '2013 Infiniti JX35', ' ', NULL, NULL, NULL, NULL, NULL, NULL, 'pending', 'unpaid', NULL, 'medium', NULL, '2026-01-12 19:21:20', '2026-01-12 19:21:20', NULL, '5N1AL0MM0DC305250', '2013', 'Infiniti', 'JX35', '', '0', '', '1', '2026-01-07 00:00:00', 'WASSAP');
INSERT INTO `shipments` (`id`, `shipment_number`, `quote_id`, `client_id`, `service_type`, `status`, `title`, `description`, `origin`, `destination`, `estimated_completion`, `actual_completion`, `total_value`, `invoice_number`, `invoice_status`, `payment_status`, `assigned_to`, `priority`, `notes`, `created_at`, `updated_at`, `deleted_at`, `vin`, `vehicle_year`, `vehicle_make`, `vehicle_model`, `vehicle_color`, `tow_required`, `driver_name`, `ownership_documents`, `date_received`, `location_notes`) VALUES ('33', 'SHP-2QAIT9DJ', NULL, '23', 'ADGOD', 'pending', '2005 Acura MDX', ' ', NULL, NULL, NULL, NULL, NULL, NULL, 'pending', 'unpaid', NULL, 'medium', NULL, '2026-01-12 19:21:20', '2026-01-12 19:21:20', NULL, ' 2HNYD18985H505166', '2005', 'Acura', 'MDX', '', '0', '', '1', '2026-01-07 00:00:00', 'WASSAP');
INSERT INTO `shipments` (`id`, `shipment_number`, `quote_id`, `client_id`, `service_type`, `status`, `title`, `description`, `origin`, `destination`, `estimated_completion`, `actual_completion`, `total_value`, `invoice_number`, `invoice_status`, `payment_status`, `assigned_to`, `priority`, `notes`, `created_at`, `updated_at`, `deleted_at`, `vin`, `vehicle_year`, `vehicle_make`, `vehicle_model`, `vehicle_color`, `tow_required`, `driver_name`, `ownership_documents`, `date_received`, `location_notes`) VALUES ('34', 'SHP-ITQKTGOB', NULL, '24', 'CONTAINER', 'draft', '2015 Honda Civic Sedan', ' ', NULL, NULL, NULL, NULL, NULL, NULL, 'pending', 'unpaid', NULL, 'medium', NULL, '2026-01-12 19:21:20', '2026-01-12 19:21:20', NULL, ' 2HGFB2F74FH002943', '2015', 'Honda', 'Civic Sedan', '', '1', '', '0', '2026-01-07 00:00:00', '');
INSERT INTO `shipments` (`id`, `shipment_number`, `quote_id`, `client_id`, `service_type`, `status`, `title`, `description`, `origin`, `destination`, `estimated_completion`, `actual_completion`, `total_value`, `invoice_number`, `invoice_status`, `payment_status`, `assigned_to`, `priority`, `notes`, `created_at`, `updated_at`, `deleted_at`, `vin`, `vehicle_year`, `vehicle_make`, `vehicle_model`, `vehicle_color`, `tow_required`, `driver_name`, `ownership_documents`, `date_received`, `location_notes`) VALUES ('35', 'SHP-9WYDL9NH', NULL, '20', 'CONTAINER', 'draft', '2003 Toyota Highlander', ' ', NULL, NULL, NULL, NULL, NULL, NULL, 'pending', 'unpaid', NULL, 'medium', NULL, '2026-01-12 19:21:20', '2026-01-12 19:21:20', NULL, 'JTEHF21A130103805', '2003', 'Toyota', 'Highlander', '', '0', '', '1', '2026-01-08 00:00:00', 'WASSAP');
INSERT INTO `shipments` (`id`, `shipment_number`, `quote_id`, `client_id`, `service_type`, `status`, `title`, `description`, `origin`, `destination`, `estimated_completion`, `actual_completion`, `total_value`, `invoice_number`, `invoice_status`, `payment_status`, `assigned_to`, `priority`, `notes`, `created_at`, `updated_at`, `deleted_at`, `vin`, `vehicle_year`, `vehicle_make`, `vehicle_model`, `vehicle_color`, `tow_required`, `driver_name`, `ownership_documents`, `date_received`, `location_notes`) VALUES ('36', 'SHP-PY212DS9', NULL, '25', 'CONTAINER', 'draft', '2011 Mazda CX-7', ' ', NULL, NULL, NULL, NULL, NULL, NULL, 'pending', 'unpaid', NULL, 'medium', NULL, '2026-01-12 19:21:20', '2026-01-12 19:21:20', NULL, ' JM3ER2B54B0369387', '2011', 'Mazda', 'CX-7', '', '0', '', '1', '2026-01-07 00:00:00', 'WASSAP');
INSERT INTO `shipments` (`id`, `shipment_number`, `quote_id`, `client_id`, `service_type`, `status`, `title`, `description`, `origin`, `destination`, `estimated_completion`, `actual_completion`, `total_value`, `invoice_number`, `invoice_status`, `payment_status`, `assigned_to`, `priority`, `notes`, `created_at`, `updated_at`, `deleted_at`, `vin`, `vehicle_year`, `vehicle_make`, `vehicle_model`, `vehicle_color`, `tow_required`, `driver_name`, `ownership_documents`, `date_received`, `location_notes`) VALUES ('37', 'SHP-TIW1KVBM', NULL, '26', 'CONTAINER', 'draft', '2012 BMW X1', ' ', NULL, NULL, NULL, NULL, NULL, NULL, 'pending', 'unpaid', NULL, 'medium', NULL, '2026-01-12 19:21:20', '2026-01-12 19:21:20', NULL, ' WBAVL1C50CVR78321', '2012', 'BMW', 'X1', '', '1', '', '1', '2026-01-07 00:00:00', 'WASSAP');
INSERT INTO `shipments` (`id`, `shipment_number`, `quote_id`, `client_id`, `service_type`, `status`, `title`, `description`, `origin`, `destination`, `estimated_completion`, `actual_completion`, `total_value`, `invoice_number`, `invoice_status`, `payment_status`, `assigned_to`, `priority`, `notes`, `created_at`, `updated_at`, `deleted_at`, `vin`, `vehicle_year`, `vehicle_make`, `vehicle_model`, `vehicle_color`, `tow_required`, `driver_name`, `ownership_documents`, `date_received`, `location_notes`) VALUES ('38', 'SHP-HU9C1YOP', NULL, '27', 'CONTAINER', 'draft', '2015 INFINITI Q50', ' ', NULL, NULL, NULL, NULL, NULL, NULL, 'pending', 'unpaid', NULL, 'medium', NULL, '2026-01-12 19:21:20', '2026-01-12 19:21:20', NULL, 'JN1BV7AR2FM405961', '2015', 'INFINITI', 'Q50', '', '1', '', '1', '2026-01-07 00:00:00', 'WASSAP');
INSERT INTO `shipments` (`id`, `shipment_number`, `quote_id`, `client_id`, `service_type`, `status`, `title`, `description`, `origin`, `destination`, `estimated_completion`, `actual_completion`, `total_value`, `invoice_number`, `invoice_status`, `payment_status`, `assigned_to`, `priority`, `notes`, `created_at`, `updated_at`, `deleted_at`, `vin`, `vehicle_year`, `vehicle_make`, `vehicle_model`, `vehicle_color`, `tow_required`, `driver_name`, `ownership_documents`, `date_received`, `location_notes`) VALUES ('39', 'SHP-N3TQMGLC', NULL, '8', 'CONTAINER', 'draft', '2009 Mercedes-Benz M-Class', ' ', NULL, NULL, NULL, NULL, NULL, NULL, 'pending', 'unpaid', NULL, 'medium', NULL, '2026-01-12 19:21:20', '2026-01-12 19:21:20', NULL, ' 4JGBB86E79A482196', '2009', 'Mercedes-Benz', 'M-Class', '', '0', '', '1', '2026-01-08 00:00:00', 'WASSAP');
INSERT INTO `shipments` (`id`, `shipment_number`, `quote_id`, `client_id`, `service_type`, `status`, `title`, `description`, `origin`, `destination`, `estimated_completion`, `actual_completion`, `total_value`, `invoice_number`, `invoice_status`, `payment_status`, `assigned_to`, `priority`, `notes`, `created_at`, `updated_at`, `deleted_at`, `vin`, `vehicle_year`, `vehicle_make`, `vehicle_model`, `vehicle_color`, `tow_required`, `driver_name`, `ownership_documents`, `date_received`, `location_notes`) VALUES ('40', 'SHP-QLJYGVZ9', NULL, '2', 'CONTAINER', 'draft', '2012 Toyota Corolla', ' ', NULL, NULL, NULL, NULL, NULL, NULL, 'pending', 'unpaid', NULL, 'medium', NULL, '2026-01-12 19:21:20', '2026-01-12 19:21:20', NULL, ' 2T1BU4EE8CC839851', '2012', 'Toyota', 'Corolla', '', '1', 'after we were closed', '1', '2026-01-08 00:00:00', 'WASSAP');
INSERT INTO `shipments` (`id`, `shipment_number`, `quote_id`, `client_id`, `service_type`, `status`, `title`, `description`, `origin`, `destination`, `estimated_completion`, `actual_completion`, `total_value`, `invoice_number`, `invoice_status`, `payment_status`, `assigned_to`, `priority`, `notes`, `created_at`, `updated_at`, `deleted_at`, `vin`, `vehicle_year`, `vehicle_make`, `vehicle_model`, `vehicle_color`, `tow_required`, `driver_name`, `ownership_documents`, `date_received`, `location_notes`) VALUES ('41', 'SHP-XIKEKVOU', NULL, '27', 'CONTAINER', 'draft', '2013 Hyundai Elantra', ' ', NULL, NULL, NULL, NULL, NULL, NULL, 'pending', 'unpaid', NULL, 'medium', NULL, '2026-01-12 19:21:20', '2026-01-12 19:21:20', NULL, '5NPDH4AE8DH213296', '2013', 'Hyundai', 'Elantra', '', '1', '', '0', '2026-01-08 00:00:00', '');
INSERT INTO `shipments` (`id`, `shipment_number`, `quote_id`, `client_id`, `service_type`, `status`, `title`, `description`, `origin`, `destination`, `estimated_completion`, `actual_completion`, `total_value`, `invoice_number`, `invoice_status`, `payment_status`, `assigned_to`, `priority`, `notes`, `created_at`, `updated_at`, `deleted_at`, `vin`, `vehicle_year`, `vehicle_make`, `vehicle_model`, `vehicle_color`, `tow_required`, `driver_name`, `ownership_documents`, `date_received`, `location_notes`) VALUES ('42', 'SHP-39ME5RPF', NULL, '28', 'ADGOD', 'draft', '2023 Toyota Corolla Cross 7MUAAAAG3PV062752', ' ', NULL, NULL, NULL, NULL, NULL, NULL, 'pending', 'unpaid', NULL, 'medium', NULL, '2026-01-12 19:21:20', '2026-01-12 19:21:20', NULL, '2022 Toyota Corolla 5YFBPMBE5NP355554', '2023', 'Toyota', 'Corolla Cross 7MUAAAAG3PV062752', '', '1', '', '0', '2026-01-08 00:00:00', '');
INSERT INTO `shipments` (`id`, `shipment_number`, `quote_id`, `client_id`, `service_type`, `status`, `title`, `description`, `origin`, `destination`, `estimated_completion`, `actual_completion`, `total_value`, `invoice_number`, `invoice_status`, `payment_status`, `assigned_to`, `priority`, `notes`, `created_at`, `updated_at`, `deleted_at`, `vin`, `vehicle_year`, `vehicle_make`, `vehicle_model`, `vehicle_color`, `tow_required`, `driver_name`, `ownership_documents`, `date_received`, `location_notes`) VALUES ('43', 'SHP-PHGDXEEC', NULL, '29', 'ADGOD', 'draft', '5TDJGRFH8HS034231', ' ', NULL, NULL, NULL, NULL, NULL, NULL, 'pending', 'unpaid', NULL, 'medium', NULL, '2026-01-12 19:21:20', '2026-01-12 19:21:20', NULL, '2017 Toyota Highlander Hybrid ', NULL, '5TDJGRFH8HS034231', NULL, '', '1', 'GUY WITH DEALER', '1', '2026-01-08 00:00:00', 'WASSAP');
INSERT INTO `shipments` (`id`, `shipment_number`, `quote_id`, `client_id`, `service_type`, `status`, `title`, `description`, `origin`, `destination`, `estimated_completion`, `actual_completion`, `total_value`, `invoice_number`, `invoice_status`, `payment_status`, `assigned_to`, `priority`, `notes`, `created_at`, `updated_at`, `deleted_at`, `vin`, `vehicle_year`, `vehicle_make`, `vehicle_model`, `vehicle_color`, `tow_required`, `driver_name`, `ownership_documents`, `date_received`, `location_notes`) VALUES ('44', 'SHP-LJBL0CEO', NULL, '8', 'CONTAINER', 'draft', '2009 Toyota Matrix', ' ', NULL, NULL, NULL, NULL, NULL, NULL, 'pending', 'unpaid', NULL, 'medium', NULL, '2026-01-12 19:21:20', '2026-01-12 19:21:20', NULL, ' 2T1KU40E49C186077', '2009', 'Toyota', 'Matrix', '', '1', '', '0', '2026-01-08 00:00:00', '');
INSERT INTO `shipments` (`id`, `shipment_number`, `quote_id`, `client_id`, `service_type`, `status`, `title`, `description`, `origin`, `destination`, `estimated_completion`, `actual_completion`, `total_value`, `invoice_number`, `invoice_status`, `payment_status`, `assigned_to`, `priority`, `notes`, `created_at`, `updated_at`, `deleted_at`, `vin`, `vehicle_year`, `vehicle_make`, `vehicle_model`, `vehicle_color`, `tow_required`, `driver_name`, `ownership_documents`, `date_received`, `location_notes`) VALUES ('45', 'SHP-QKKC0ALQ', NULL, '30', 'CONTAINER', 'draft', '2012 Ford Escape', ' ', NULL, NULL, NULL, NULL, NULL, NULL, 'pending', 'unpaid', NULL, 'medium', NULL, '2026-01-12 19:21:20', '2026-01-12 19:21:20', NULL, '1FMCU0D71CKA11277', '2012', 'Ford', 'Escape', '', '0', '', '1', '2026-01-08 00:00:00', 'WASSAP');
INSERT INTO `shipments` (`id`, `shipment_number`, `quote_id`, `client_id`, `service_type`, `status`, `title`, `description`, `origin`, `destination`, `estimated_completion`, `actual_completion`, `total_value`, `invoice_number`, `invoice_status`, `payment_status`, `assigned_to`, `priority`, `notes`, `created_at`, `updated_at`, `deleted_at`, `vin`, `vehicle_year`, `vehicle_make`, `vehicle_model`, `vehicle_color`, `tow_required`, `driver_name`, `ownership_documents`, `date_received`, `location_notes`) VALUES ('46', 'SHP-8DXB9DMT', NULL, '30', 'CONTAINER', 'draft', '2010 Jeep Compass', ' ', NULL, NULL, NULL, NULL, NULL, NULL, 'pending', 'unpaid', NULL, 'medium', NULL, '2026-01-12 19:21:20', '2026-01-12 19:21:20', NULL, '1J4NT4FB4AD569905', '2010', 'Jeep', 'Compass', '', '0', '', '1', NULL, 'WASSAP');
INSERT INTO `shipments` (`id`, `shipment_number`, `quote_id`, `client_id`, `service_type`, `status`, `title`, `description`, `origin`, `destination`, `estimated_completion`, `actual_completion`, `total_value`, `invoice_number`, `invoice_status`, `payment_status`, `assigned_to`, `priority`, `notes`, `created_at`, `updated_at`, `deleted_at`, `vin`, `vehicle_year`, `vehicle_make`, `vehicle_model`, `vehicle_color`, `tow_required`, `driver_name`, `ownership_documents`, `date_received`, `location_notes`) VALUES ('47', 'SHP-ZKQOVRM3', NULL, '31', 'CONTAINER', 'draft', '2016 Toyota RAV4', ' ', NULL, NULL, NULL, NULL, NULL, NULL, 'pending', 'unpaid', NULL, 'medium', NULL, '2026-01-12 19:21:20', '2026-01-12 19:21:20', NULL, ' 2T3ZFREV3GW280419', '2016', 'Toyota', 'RAV4', '', '1', '', '1', '2026-01-08 00:00:00', 'WASSAP');
INSERT INTO `shipments` (`id`, `shipment_number`, `quote_id`, `client_id`, `service_type`, `status`, `title`, `description`, `origin`, `destination`, `estimated_completion`, `actual_completion`, `total_value`, `invoice_number`, `invoice_status`, `payment_status`, `assigned_to`, `priority`, `notes`, `created_at`, `updated_at`, `deleted_at`, `vin`, `vehicle_year`, `vehicle_make`, `vehicle_model`, `vehicle_color`, `tow_required`, `driver_name`, `ownership_documents`, `date_received`, `location_notes`) VALUES ('48', 'SHP-9HJ59YKH', NULL, '32', 'UNSURE', 'draft', '2008 Toyota Sienna', ' ', NULL, NULL, NULL, NULL, NULL, NULL, 'pending', 'unpaid', NULL, 'medium', NULL, '2026-01-12 19:21:20', '2026-01-12 19:21:20', NULL, '5TDZK29C28S180648', '2008', 'Toyota', 'Sienna', '', '1', 'at night 11 pm ', '1', '2026-01-09 00:00:00', '');
INSERT INTO `shipments` (`id`, `shipment_number`, `quote_id`, `client_id`, `service_type`, `status`, `title`, `description`, `origin`, `destination`, `estimated_completion`, `actual_completion`, `total_value`, `invoice_number`, `invoice_status`, `payment_status`, `assigned_to`, `priority`, `notes`, `created_at`, `updated_at`, `deleted_at`, `vin`, `vehicle_year`, `vehicle_make`, `vehicle_model`, `vehicle_color`, `tow_required`, `driver_name`, `ownership_documents`, `date_received`, `location_notes`) VALUES ('49', 'SHP-U5QU3FVY', NULL, '18', 'ADGOD', 'pending', '2009 Toyota Corolla', ' ', NULL, NULL, NULL, NULL, NULL, NULL, 'pending', 'unpaid', NULL, 'medium', NULL, '2026-01-12 19:21:20', '2026-01-12 19:21:20', NULL, '2T1BU40E39C109698', '2009', 'Toyota', 'Corolla', '', '0', '', '1', '2026-01-09 00:00:00', 'WASSAP');
INSERT INTO `shipments` (`id`, `shipment_number`, `quote_id`, `client_id`, `service_type`, `status`, `title`, `description`, `origin`, `destination`, `estimated_completion`, `actual_completion`, `total_value`, `invoice_number`, `invoice_status`, `payment_status`, `assigned_to`, `priority`, `notes`, `created_at`, `updated_at`, `deleted_at`, `vin`, `vehicle_year`, `vehicle_make`, `vehicle_model`, `vehicle_color`, `tow_required`, `driver_name`, `ownership_documents`, `date_received`, `location_notes`) VALUES ('50', 'SHP-WN0AC3YC', NULL, '33', 'CONTAINER', 'draft', '2016 Ford Escape', ' ', NULL, NULL, NULL, NULL, NULL, NULL, 'pending', 'unpaid', NULL, 'medium', NULL, '2026-01-12 19:21:20', '2026-01-12 19:21:20', NULL, '1FMCU9GX8GUA79098', '2016', 'Ford', 'Escape', '', '0', '', '1', '2026-01-09 00:00:00', 'WASSAP');
INSERT INTO `shipments` (`id`, `shipment_number`, `quote_id`, `client_id`, `service_type`, `status`, `title`, `description`, `origin`, `destination`, `estimated_completion`, `actual_completion`, `total_value`, `invoice_number`, `invoice_status`, `payment_status`, `assigned_to`, `priority`, `notes`, `created_at`, `updated_at`, `deleted_at`, `vin`, `vehicle_year`, `vehicle_make`, `vehicle_model`, `vehicle_color`, `tow_required`, `driver_name`, `ownership_documents`, `date_received`, `location_notes`) VALUES ('51', 'SHP-M4GZ0RRR', NULL, '34', 'CONTAINER', 'draft', '2018 Dodge Grand Caravan 2C4RDGEG0JR179920', ' ', NULL, NULL, NULL, NULL, NULL, NULL, 'pending', 'unpaid', NULL, 'medium', NULL, '2026-01-12 19:21:20', '2026-01-12 19:21:20', NULL, '2014 Toyota Prius c JTDKDTB35E1559373', '2018', 'Dodge', 'Grand Caravan 2C4RDGEG0JR179920', '', '1', 'SANDY, OLA', '0', '2026-01-09 00:00:00', '');
INSERT INTO `shipments` (`id`, `shipment_number`, `quote_id`, `client_id`, `service_type`, `status`, `title`, `description`, `origin`, `destination`, `estimated_completion`, `actual_completion`, `total_value`, `invoice_number`, `invoice_status`, `payment_status`, `assigned_to`, `priority`, `notes`, `created_at`, `updated_at`, `deleted_at`, `vin`, `vehicle_year`, `vehicle_make`, `vehicle_model`, `vehicle_color`, `tow_required`, `driver_name`, `ownership_documents`, `date_received`, `location_notes`) VALUES ('52', 'SHP-F4BPXRTY', NULL, '11', 'CONTAINER', 'draft', '2017 Toyota Tacoma', ' ', NULL, NULL, NULL, NULL, NULL, NULL, 'pending', 'unpaid', NULL, 'medium', NULL, '2026-01-12 19:21:20', '2026-01-12 19:21:20', NULL, '5TFDZ5BN4HX024415', '2017', 'Toyota', 'Tacoma', '', '0', '', '1', '2026-01-09 00:00:00', 'WASSAP');
INSERT INTO `shipments` (`id`, `shipment_number`, `quote_id`, `client_id`, `service_type`, `status`, `title`, `description`, `origin`, `destination`, `estimated_completion`, `actual_completion`, `total_value`, `invoice_number`, `invoice_status`, `payment_status`, `assigned_to`, `priority`, `notes`, `created_at`, `updated_at`, `deleted_at`, `vin`, `vehicle_year`, `vehicle_make`, `vehicle_model`, `vehicle_color`, `tow_required`, `driver_name`, `ownership_documents`, `date_received`, `location_notes`) VALUES ('53', 'SHP-GEDRZ4CW', NULL, '35', 'CONTAINER', 'draft', '2015 Mercedes-Benz CLA-Class', ' ', NULL, NULL, NULL, NULL, NULL, NULL, 'pending', 'unpaid', NULL, 'medium', NULL, '2026-01-12 19:21:20', '2026-01-12 19:21:20', NULL, ' WDDSJ4GB6FN178372', '2015', 'Mercedes-Benz', 'CLA-Class', '', '1', 'PASTOR SUNDAY', '0', '2026-01-09 00:00:00', '');
INSERT INTO `shipments` (`id`, `shipment_number`, `quote_id`, `client_id`, `service_type`, `status`, `title`, `description`, `origin`, `destination`, `estimated_completion`, `actual_completion`, `total_value`, `invoice_number`, `invoice_status`, `payment_status`, `assigned_to`, `priority`, `notes`, `created_at`, `updated_at`, `deleted_at`, `vin`, `vehicle_year`, `vehicle_make`, `vehicle_model`, `vehicle_color`, `tow_required`, `driver_name`, `ownership_documents`, `date_received`, `location_notes`) VALUES ('54', 'SHP-NVEEPCTI', NULL, '36', 'CONTAINER', 'draft', '2018 Toyota Corolla', ' ', NULL, NULL, NULL, NULL, NULL, NULL, 'pending', 'unpaid', NULL, 'medium', NULL, '2026-01-12 19:21:20', '2026-01-12 19:21:20', NULL, '2T1BURHE0JC117599', '2018', 'Toyota', 'Corolla', '', '1', '', '1', '2026-01-09 00:00:00', 'WASSAP');
INSERT INTO `shipments` (`id`, `shipment_number`, `quote_id`, `client_id`, `service_type`, `status`, `title`, `description`, `origin`, `destination`, `estimated_completion`, `actual_completion`, `total_value`, `invoice_number`, `invoice_status`, `payment_status`, `assigned_to`, `priority`, `notes`, `created_at`, `updated_at`, `deleted_at`, `vin`, `vehicle_year`, `vehicle_make`, `vehicle_model`, `vehicle_color`, `tow_required`, `driver_name`, `ownership_documents`, `date_received`, `location_notes`) VALUES ('55', 'SHP-I1YC9QLA', NULL, '37', 'CONTAINER', 'draft', '2004 Toyota Corolla', ' ', NULL, NULL, NULL, NULL, NULL, NULL, 'pending', 'unpaid', NULL, 'medium', NULL, '2026-01-12 19:21:20', '2026-01-12 19:21:20', NULL, '2T1BR32E74C813422', '2004', 'Toyota', 'Corolla', '', '0', '', '1', '2026-01-10 00:00:00', 'WASSAP');
INSERT INTO `shipments` (`id`, `shipment_number`, `quote_id`, `client_id`, `service_type`, `status`, `title`, `description`, `origin`, `destination`, `estimated_completion`, `actual_completion`, `total_value`, `invoice_number`, `invoice_status`, `payment_status`, `assigned_to`, `priority`, `notes`, `created_at`, `updated_at`, `deleted_at`, `vin`, `vehicle_year`, `vehicle_make`, `vehicle_model`, `vehicle_color`, `tow_required`, `driver_name`, `ownership_documents`, `date_received`, `location_notes`) VALUES ('56', 'SHP-VGZUYZNK', NULL, '38', 'CONTAINER', 'draft', '2005 Toyota Corolla', ' ', NULL, NULL, NULL, NULL, NULL, NULL, 'pending', 'unpaid', NULL, 'medium', NULL, '2026-01-12 19:21:20', '2026-01-12 19:21:20', NULL, ' 2T1BR32E75C901792', '2005', 'Toyota', 'Corolla', '', '0', '', '1', '2026-01-10 00:00:00', 'WASSAP');
INSERT INTO `shipments` (`id`, `shipment_number`, `quote_id`, `client_id`, `service_type`, `status`, `title`, `description`, `origin`, `destination`, `estimated_completion`, `actual_completion`, `total_value`, `invoice_number`, `invoice_status`, `payment_status`, `assigned_to`, `priority`, `notes`, `created_at`, `updated_at`, `deleted_at`, `vin`, `vehicle_year`, `vehicle_make`, `vehicle_model`, `vehicle_color`, `tow_required`, `driver_name`, `ownership_documents`, `date_received`, `location_notes`) VALUES ('57', 'SHP-DMNR32FN', NULL, '39', 'CONTAINER', 'draft', '2013 Ford Explorer', ' ', NULL, NULL, NULL, NULL, NULL, NULL, 'pending', 'unpaid', NULL, 'medium', NULL, '2026-01-12 19:21:20', '2026-01-12 19:21:20', NULL, '1FM5K7F82DGB94593', '2013', 'Ford', 'Explorer', '', '1', '', '1', '2026-01-10 00:00:00', 'WASSAP');
INSERT INTO `shipments` (`id`, `shipment_number`, `quote_id`, `client_id`, `service_type`, `status`, `title`, `description`, `origin`, `destination`, `estimated_completion`, `actual_completion`, `total_value`, `invoice_number`, `invoice_status`, `payment_status`, `assigned_to`, `priority`, `notes`, `created_at`, `updated_at`, `deleted_at`, `vin`, `vehicle_year`, `vehicle_make`, `vehicle_model`, `vehicle_color`, `tow_required`, `driver_name`, `ownership_documents`, `date_received`, `location_notes`) VALUES ('58', 'SHP-CWOWSQNL', NULL, '40', 'CONTAINER', 'draft', '2012 Hyundai Elantra', ' ', NULL, NULL, NULL, NULL, NULL, NULL, 'pending', 'unpaid', NULL, 'medium', NULL, '2026-01-12 19:21:20', '2026-01-12 19:21:20', NULL, '5NPDH4AE7CH066466', '2012', 'Hyundai', 'Elantra', 'silver grey', '1', '', '0', '2026-01-12 00:00:00', '');
INSERT INTO `shipments` (`id`, `shipment_number`, `quote_id`, `client_id`, `service_type`, `status`, `title`, `description`, `origin`, `destination`, `estimated_completion`, `actual_completion`, `total_value`, `invoice_number`, `invoice_status`, `payment_status`, `assigned_to`, `priority`, `notes`, `created_at`, `updated_at`, `deleted_at`, `vin`, `vehicle_year`, `vehicle_make`, `vehicle_model`, `vehicle_color`, `tow_required`, `driver_name`, `ownership_documents`, `date_received`, `location_notes`) VALUES ('59', 'SHP-KQHWV2T7', NULL, '34', 'CONTAINER', 'draft', '2014 Scion tC', ' ', NULL, NULL, NULL, NULL, NULL, NULL, 'pending', 'unpaid', NULL, 'medium', NULL, '2026-01-12 19:21:20', '2026-01-12 19:21:20', NULL, ' JTKJF5C71E3069528', '2014', 'Scion', 'tC', '', '1', '', '1', '2026-01-12 00:00:00', 'WASSAP');
INSERT INTO `shipments` (`id`, `shipment_number`, `quote_id`, `client_id`, `service_type`, `status`, `title`, `description`, `origin`, `destination`, `estimated_completion`, `actual_completion`, `total_value`, `invoice_number`, `invoice_status`, `payment_status`, `assigned_to`, `priority`, `notes`, `created_at`, `updated_at`, `deleted_at`, `vin`, `vehicle_year`, `vehicle_make`, `vehicle_model`, `vehicle_color`, `tow_required`, `driver_name`, `ownership_documents`, `date_received`, `location_notes`) VALUES ('60', 'SHP-PKBL2ZVS', NULL, '41', 'CONTAINER', 'draft', '2015 MINI Cooper Hardtop 5 Door', ' ', NULL, NULL, NULL, NULL, NULL, NULL, 'pending', 'unpaid', NULL, 'medium', NULL, '2026-01-12 19:21:20', '2026-01-12 19:21:20', NULL, ' WMWXU3C58F2B66307', '2015', 'MINI', 'Cooper Hardtop 5 Door', '', '1', '', '0', '2026-01-12 00:00:00', '');
INSERT INTO `shipments` (`id`, `shipment_number`, `quote_id`, `client_id`, `service_type`, `status`, `title`, `description`, `origin`, `destination`, `estimated_completion`, `actual_completion`, `total_value`, `invoice_number`, `invoice_status`, `payment_status`, `assigned_to`, `priority`, `notes`, `created_at`, `updated_at`, `deleted_at`, `vin`, `vehicle_year`, `vehicle_make`, `vehicle_model`, `vehicle_color`, `tow_required`, `driver_name`, `ownership_documents`, `date_received`, `location_notes`) VALUES ('61', 'SHP-CRUPJIWF', NULL, '42', 'CONTAINER', 'draft', '2013 Mercedes-Benz C-Class', ' ', NULL, NULL, NULL, NULL, NULL, NULL, 'pending', 'unpaid', NULL, 'medium', NULL, '2026-01-12 19:21:20', '2026-01-12 19:21:20', NULL, 'WDDGF8JB2DA797403', '2013', 'Mercedes-Benz', 'C-Class', '', '0', '', '1', '2026-01-12 00:00:00', 'WASSAP');

SET FOREIGN_KEY_CHECKS=1;
