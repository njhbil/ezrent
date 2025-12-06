<?php
/**
 * Database Migration - Update payments table for Midtrans integration
 */

require_once 'config/database.php';

echo "=== Midtrans Payment Integration Migration ===\n";
echo "Date: " . date('Y-m-d H:i:s') . "\n\n";

try {
    // Drop and recreate payments table with all required columns
    echo "1. Recreating payments table...\n";
    
    $pdo->exec("DROP TABLE IF EXISTS payments");
    
    $pdo->exec("
        CREATE TABLE payments (
            id INT PRIMARY KEY AUTO_INCREMENT,
            booking_id INT NOT NULL,
            user_id INT NOT NULL,
            payment_method VARCHAR(50) NOT NULL,
            payment_code VARCHAR(100),
            amount DECIMAL(12, 2) NOT NULL,
            status ENUM('pending', 'processing', 'completed', 'failed', 'expired', 'refund') DEFAULT 'pending',
            
            -- Midtrans specific fields
            midtrans_order_id VARCHAR(100),
            midtrans_transaction_id VARCHAR(100),
            
            -- VA fields
            va_number VARCHAR(50),
            biller_code VARCHAR(20),
            bill_key VARCHAR(50),
            
            -- QRIS fields
            qris_url TEXT,
            
            -- Timestamps
            expiry_time DATETIME,
            paid_at DATETIME,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            
            -- Indexes
            UNIQUE KEY unique_booking (booking_id),
            INDEX idx_status (status),
            INDEX idx_midtrans_order (midtrans_order_id),
            INDEX idx_user (user_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    echo "   ✅ Payments table created with Midtrans fields\n";
    
    // Verify columns
    $result = $pdo->query("SHOW COLUMNS FROM payments");
    $columns = $result->fetchAll(PDO::FETCH_COLUMN);
    echo "\n   Columns: " . implode(', ', $columns) . "\n";
    
    // Check bookings table has payment_method
    echo "\n2. Checking bookings table...\n";
    $result = $pdo->query("SHOW COLUMNS FROM bookings LIKE 'payment_method'");
    if ($result->rowCount() === 0) {
        $pdo->exec("ALTER TABLE bookings ADD COLUMN payment_method VARCHAR(50) AFTER status");
        echo "   ✅ Added payment_method column\n";
    } else {
        echo "   - payment_method already exists\n";
    }
    
    echo "\n✅ Migration completed successfully!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
