<?php
/**
 * Database Migration - Add payment tracking columns
 * Adds payment_method to bookings and ensures payments table exists
 */

require_once 'config/database.php';

try {
    echo "Starting database migration...\n";
    
    // Check if payment_method column exists in bookings
    $result = $pdo->query("SHOW COLUMNS FROM bookings LIKE 'payment_method'");
    if ($result->rowCount() === 0) {
        $pdo->exec("ALTER TABLE bookings ADD COLUMN payment_method VARCHAR(50) AFTER status");
        echo "✓ Added payment_method column to bookings table\n";
    } else {
        echo "- payment_method column already exists\n";
    }
    
    // Check if updated_at column exists
    $result = $pdo->query("SHOW COLUMNS FROM bookings LIKE 'updated_at'");
    if ($result->rowCount() === 0) {
        $pdo->exec("ALTER TABLE bookings ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at");
        echo "✓ Added updated_at column to bookings table\n";
    } else {
        echo "- updated_at column already exists\n";
    }
    
    // Create payments table if it doesn't exist
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS payments (
            id INT PRIMARY KEY AUTO_INCREMENT,
            booking_id INT NOT NULL UNIQUE,
            user_id INT NOT NULL,
            payment_method VARCHAR(50),
            payment_code VARCHAR(100),
            amount DECIMAL(10, 2),
            status ENUM('pending', 'processing', 'completed', 'failed') DEFAULT 'pending',
            reference_number VARCHAR(100),
            paid_at TIMESTAMP NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            INDEX idx_booking (booking_id),
            INDEX idx_status (status)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "✓ Payments table created/verified\n";
    
    // Verify schema
    $stmt = $pdo->query("SHOW COLUMNS FROM bookings");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "\nBookings table columns:\n";
    foreach ($columns as $col) {
        echo "  - $col\n";
    }
    
    echo "\n✅ Migration completed successfully!\n";
    
} catch (Exception $e) {
    echo "❌ Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
