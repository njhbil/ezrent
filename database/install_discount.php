<?php
/**
 * Script untuk setup tabel diskon otomatis
 * Jalankan sekali via browser: http://localhost/ezrent/database/install_discount.php
 */

require_once '../php/config/database.php';

echo "<!DOCTYPE html>
<html>
<head>
    <title>Setup Diskon - EzRent</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; background: #f5f5f5; }
        .log { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .success { color: #22c55e; padding: 10px; background: #f0fdf4; border-left: 4px solid #22c55e; margin: 10px 0; }
        .error { color: #ef4444; padding: 10px; background: #fef2f2; border-left: 4px solid #ef4444; margin: 10px 0; }
        .info { color: #3b82f6; padding: 10px; background: #eff6ff; border-left: 4px solid #3b82f6; margin: 10px 0; }
        h1 { color: #111; }
        code { background: #f3f4f6; padding: 2px 6px; border-radius: 4px; font-family: monospace; }
    </style>
</head>
<body>
<div class='log'>
<h1>🚀 Setup Sistem Diskon EzRent</h1>";

try {
    // 1. Buat tabel discount_codes
    echo "<div class='info'>📝 Membuat tabel <code>discount_codes</code>...</div>";
    $sql1 = "CREATE TABLE IF NOT EXISTS discount_codes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        code VARCHAR(50) UNIQUE NOT NULL,
        description VARCHAR(255),
        discount_type ENUM('percentage', 'fixed') NOT NULL DEFAULT 'percentage',
        discount_value DECIMAL(10,2) NOT NULL,
        min_booking_amount DECIMAL(10,2) DEFAULT 0,
        max_discount_amount DECIMAL(10,2) DEFAULT NULL,
        usage_limit INT DEFAULT NULL,
        used_count INT DEFAULT 0,
        start_date DATETIME DEFAULT NULL,
        end_date DATETIME DEFAULT NULL,
        is_active TINYINT(1) DEFAULT 1,
        created_by INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
    )";
    $pdo->exec($sql1);
    echo "<div class='success'>✅ Tabel <code>discount_codes</code> berhasil dibuat!</div>";

    // 2. Buat tabel discount_usage
    echo "<div class='info'>📝 Membuat tabel <code>discount_usage</code>...</div>";
    $sql2 = "CREATE TABLE IF NOT EXISTS discount_usage (
        id INT AUTO_INCREMENT PRIMARY KEY,
        discount_id INT NOT NULL,
        user_id INT NOT NULL,
        booking_id INT NOT NULL,
        discount_amount DECIMAL(10,2) NOT NULL,
        used_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (discount_id) REFERENCES discount_codes(id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE
    )";
    $pdo->exec($sql2);
    echo "<div class='success'>✅ Tabel <code>discount_usage</code> berhasil dibuat!</div>";

    // 3. Update tabel bookings
    echo "<div class='info'>📝 Menambah kolom discount ke tabel <code>bookings</code>...</div>";
    
    // Cek apakah kolom sudah ada
    $checkCol = $pdo->query("SHOW COLUMNS FROM bookings LIKE 'discount_code'")->fetch();
    if (!$checkCol) {
        $pdo->exec("ALTER TABLE bookings ADD COLUMN discount_code VARCHAR(50) DEFAULT NULL");
        echo "<div class='success'>✅ Kolom <code>discount_code</code> ditambahkan!</div>";
    } else {
        echo "<div class='info'>ℹ️ Kolom <code>discount_code</code> sudah ada.</div>";
    }
    
    $checkCol2 = $pdo->query("SHOW COLUMNS FROM bookings LIKE 'discount_amount'")->fetch();
    if (!$checkCol2) {
        $pdo->exec("ALTER TABLE bookings ADD COLUMN discount_amount DECIMAL(10,2) DEFAULT 0");
        echo "<div class='success'>✅ Kolom <code>discount_amount</code> ditambahkan!</div>";
    } else {
        echo "<div class='info'>ℹ️ Kolom <code>discount_amount</code> sudah ada.</div>";
    }

    // 4. Insert contoh kode diskon
    echo "<div class='info'>📝 Menambahkan kode diskon contoh...</div>";
    
    // Cek apakah sudah ada data
    $countExisting = $pdo->query("SELECT COUNT(*) FROM discount_codes")->fetchColumn();
    
    if ($countExisting == 0) {
        $sql4 = "INSERT INTO discount_codes (code, description, discount_type, discount_value, min_booking_amount, max_discount_amount, usage_limit, start_date, end_date, is_active, created_by) VALUES
        ('WELCOME10', 'Diskon 10% untuk pelanggan baru', 'percentage', 10.00, 500000, 100000, 100, NOW(), DATE_ADD(NOW(), INTERVAL 90 DAY), 1, 1),
        ('HEMAT50K', 'Potongan Rp 50.000 untuk semua booking', 'fixed', 50000, 300000, NULL, 200, NOW(), DATE_ADD(NOW(), INTERVAL 60 DAY), 1, 1),
        ('WEEKEND20', 'Diskon 20% khusus weekend', 'percentage', 20.00, 600000, 200000, 50, NOW(), DATE_ADD(NOW(), INTERVAL 30 DAY), 1, 1),
        ('LEBARAN2025', 'Diskon spesial Lebaran 15%', 'percentage', 15.00, 1000000, 300000, NULL, NOW(), DATE_ADD(NOW(), INTERVAL 120 DAY), 1, 1)";
        $pdo->exec($sql4);
        echo "<div class='success'>✅ 4 kode diskon contoh berhasil ditambahkan!</div>";
        echo "<div class='info'>
            <strong>Kode yang ditambahkan:</strong><br>
            • WELCOME10 - Diskon 10% (max Rp 100.000)<br>
            • HEMAT50K - Potongan Rp 50.000<br>
            • WEEKEND20 - Diskon 20% (max Rp 200.000)<br>
            • LEBARAN2025 - Diskon 15% (max Rp 300.000)
        </div>";
    } else {
        echo "<div class='info'>ℹ️ Sudah ada $countExisting kode diskon. Skip insert data contoh.</div>";
    }

    echo "<br><div class='success' style='font-size: 18px; font-weight: bold;'>
        🎉 SETUP SELESAI!<br><br>
        Sistem diskon sudah siap digunakan.<br>
        <a href='../pages/admin/discounts.php' style='color: #d50000; text-decoration: none;'>
            → Buka Halaman Admin Diskon
        </a>
    </div>";

} catch (PDOException $e) {
    echo "<div class='error'>❌ Error: " . $e->getMessage() . "</div>";
    echo "<div class='info'>
        <strong>Troubleshooting:</strong><br>
        1. Pastikan database <code>njhbil_ezrent</code> sudah ada<br>
        2. Cek kredensial database di <code>php/config/database.php</code><br>
        3. Pastikan user database punya akses CREATE TABLE
    </div>";
}

echo "</div></body></html>";
?>
