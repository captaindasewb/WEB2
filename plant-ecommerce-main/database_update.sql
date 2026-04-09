-- =====================================================
-- DATABASE UPDATE SCRIPT FOR PLANT ECOMMERCE ADMIN
-- Run this script on the 'plantshop' database
-- =====================================================

-- 1. Update products table: add new columns
ALTER TABLE `products`
  ADD COLUMN `product_code` VARCHAR(50) DEFAULT NULL AFTER `id`,
  ADD COLUMN `unit` VARCHAR(50) DEFAULT 'cây' AFTER `desc`,
  ADD COLUMN `cost_price` DECIMAL(15,2) DEFAULT 0 AFTER `unit`,
  ADD COLUMN `profit_margin` DECIMAL(5,2) DEFAULT 20.00 AFTER `cost_price`,
  ADD COLUMN `visibility` TINYINT(1) DEFAULT 1 AFTER `profit_margin`;

-- Set existing products price as cost_price if not set
UPDATE `products` SET `cost_price` = `price` WHERE `cost_price` = 0 OR `cost_price` IS NULL;

-- 2. Create import_receipts table (phieu nhap kho)
CREATE TABLE IF NOT EXISTS `import_receipts` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `import_date` DATE NOT NULL,
  `note` TEXT DEFAULT NULL,
  `status` ENUM('draft','completed') DEFAULT 'draft',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `completed_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 3. Create import_receipt_details table (chi tiet phieu nhap)
CREATE TABLE IF NOT EXISTS `import_receipt_details` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `receipt_id` INT(11) NOT NULL,
  `product_id` INT(11) NOT NULL,
  `quantity` INT(11) NOT NULL DEFAULT 0,
  `import_price` DECIMAL(15,2) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `fk_receipt` (`receipt_id`),
  KEY `fk_product` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 4. Update categories table to have auto_increment
ALTER TABLE `categories` MODIFY `id` INT(100) NOT NULL AUTO_INCREMENT;
