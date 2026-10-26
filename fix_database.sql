-- =========================================
-- CRITICAL FIX: Add missing 'id' column to settings table
-- Run this in phpMyAdmin
-- =========================================

-- Step 1: Add the id column as primary key
ALTER TABLE `settings` 
ADD COLUMN `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT FIRST,
ADD PRIMARY KEY (`id`);

-- Step 2: Also add completed_at to tasks if not done yet
ALTER TABLE `tasks` ADD COLUMN IF NOT EXISTS `completed_at` TIMESTAMP NULL DEFAULT NULL AFTER `due_date`;

-- Step 3: Verify the fix
DESCRIBE `settings`;
