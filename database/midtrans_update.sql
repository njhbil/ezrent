-- =====================================================
-- Database Update for Midtrans Integration
-- Run this SQL in your database to add required columns
-- =====================================================

-- Add midtrans_order_id column to bookings table
ALTER TABLE `bookings` 
ADD COLUMN `midtrans_order_id` VARCHAR(100) NULL DEFAULT NULL AFTER `booking_code`,
ADD COLUMN `payment_status` ENUM('unpaid', 'pending', 'paid', 'failed', 'refunded') DEFAULT 'unpaid' AFTER `status`;

-- Add index for faster lookup
ALTER TABLE `bookings` ADD INDEX `idx_midtrans_order_id` (`midtrans_order_id`);

-- Update payments table to store Midtrans transaction data
ALTER TABLE `payments`
ADD COLUMN `transaction_id` VARCHAR(100) NULL DEFAULT NULL AFTER `status`,
ADD COLUMN `payment_data` JSON NULL DEFAULT NULL AFTER `transaction_id`,
ADD COLUMN `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP AFTER `created_at`;

-- Add index for transaction_id
ALTER TABLE `payments` ADD INDEX `idx_transaction_id` (`transaction_id`);

-- =====================================================
-- Optional: Create logs directory for webhook logs
-- Make sure the directory is writable by web server
-- =====================================================
