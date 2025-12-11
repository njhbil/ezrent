-- Tabel untuk menyimpan kode diskon
CREATE TABLE IF NOT EXISTS discount_codes (
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
);

-- Tabel untuk tracking penggunaan diskon per user
CREATE TABLE IF NOT EXISTS discount_usage (
    id INT AUTO_INCREMENT PRIMARY KEY,
    discount_id INT NOT NULL,
    user_id INT NOT NULL,
    booking_id INT NOT NULL,
    discount_amount DECIMAL(10,2) NOT NULL,
    used_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (discount_id) REFERENCES discount_codes(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE
);

-- Tambah kolom discount ke tabel bookings jika belum ada
ALTER TABLE bookings 
ADD COLUMN IF NOT EXISTS discount_code VARCHAR(50) DEFAULT NULL,
ADD COLUMN IF NOT EXISTS discount_amount DECIMAL(10,2) DEFAULT 0;

-- Insert beberapa contoh kode diskon
INSERT INTO discount_codes (code, description, discount_type, discount_value, min_booking_amount, max_discount_amount, usage_limit, start_date, end_date, is_active, created_by) VALUES
('WELCOME10', 'Diskon 10% untuk pelanggan baru', 'percentage', 10.00, 500000, 100000, 100, NOW(), DATE_ADD(NOW(), INTERVAL 90 DAY), 1, 1),
('HEMAT50K', 'Potongan Rp 50.000 untuk semua booking', 'fixed', 50000, 300000, NULL, 200, NOW(), DATE_ADD(NOW(), INTERVAL 60 DAY), 1, 1),
('WEEKEND20', 'Diskon 20% khusus weekend', 'percentage', 20.00, 600000, 200000, 50, NOW(), DATE_ADD(NOW(), INTERVAL 30 DAY), 1, 1),
('LEBARAN2025', 'Diskon spesial Lebaran 15%', 'percentage', 15.00, 1000000, 300000, NULL, NOW(), DATE_ADD(NOW(), INTERVAL 120 DAY), 1, 1);
