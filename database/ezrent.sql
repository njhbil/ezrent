-- Buat database EzRent
CREATE DATABASE IF NOT EXISTS ezrent;
USE ezrent;

-- Tabel Users
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nama_lengkap VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    nomor_telepon VARCHAR(20),
    alamat TEXT,
    role ENUM('admin', 'user') DEFAULT 'user',
    is_verified BOOLEAN DEFAULT TRUE,
    foto_profil VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel Vehicles
CREATE TABLE vehicles (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nama VARCHAR(100) NOT NULL,
    jenis ENUM('mobil', 'motor', 'sepeda_listrik', 'sepeda') NOT NULL,
    merek VARCHAR(50) NOT NULL,
    model VARCHAR(50) NOT NULL,
    tahun YEAR NOT NULL,
    plat_nomor VARCHAR(15) UNIQUE NOT NULL,
    warna VARCHAR(30) NOT NULL,
    kapasitas INT NOT NULL,
    bahan_bakar ENUM('bensin', 'solar', 'listrik', 'manual') NOT NULL,
    transmisi ENUM('manual', 'matic', 'otomatis') NOT NULL,
    harga_per_hari DECIMAL(10,2) NOT NULL,
    status ENUM('tersedia', 'disewa', 'maintenance') DEFAULT 'tersedia',
    deskripsi TEXT,
    images JSON,
    fitur JSON,
    lokasi VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel Bookings
CREATE TABLE bookings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    vehicle_id INT NOT NULL,
    kode_booking VARCHAR(20) UNIQUE NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    total_days INT NOT NULL,
    total_price DECIMAL(12,2) NOT NULL,
    pickup_location VARCHAR(100) NOT NULL,
    return_location VARCHAR(100) NOT NULL,
    status ENUM('pending', 'confirmed', 'active', 'completed', 'cancelled') DEFAULT 'pending',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (vehicle_id) REFERENCES vehicles(id)
);

-- Tabel Payments
CREATE TABLE payments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    booking_id INT UNIQUE NOT NULL,
    amount DECIMAL(12,2) NOT NULL,
    method ENUM('transfer', 'credit_card', 'e_wallet', 'cash') NOT NULL,
    status ENUM('pending', 'paid', 'failed') DEFAULT 'pending',
    proof_image VARCHAR(255),
    paid_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES bookings(id)
);

-- Tabel Messages
CREATE TABLE messages (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    admin_id INT,
    subject VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    reply TEXT,
    status ENUM('new', 'replied', 'closed') DEFAULT 'new',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    replied_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (admin_id) REFERENCES users(id)
);

-- Insert Admin User (password: admin123)
INSERT INTO users (nama_lengkap, email, password, nomor_telepon, role, is_verified) 
VALUES ('Admin EzRent', 'admin@ezrent.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '081234567890', 'admin', TRUE);

-- Insert Sample Vehicles
INSERT INTO vehicles (nama, jenis, merek, model, tahun, plat_nomor, warna, kapasitas, bahan_bakar, transmisi, harga_per_hari, deskripsi, fitur) VALUES
('Toyota Avanza', 'mobil', 'Toyota', 'Avanza', 2023, 'B 1234 ABC', 'Putih', 7, 'bensin', 'matic', 250000, 'Mobil keluarga dengan kapasitas 7 penumpang', '["AC", "Power Steering", "Audio Bluetooth", "Rear Camera"]'),
('Honda Vario 160', 'motor', 'Honda', 'Vario 160', 2024, 'B 5678 DEF', 'Hitam', 2, 'bensin', 'matic', 80000, 'Skutik modern dengan fitur lengkap', '["LED Light", "Smart Key", "USB Charger", "Bagasi"]'),
('Uwin Flash', 'sepeda_listrik', 'Uwin', 'Flash', 2023, 'EL 001', 'Merah', 1, 'listrik', 'otomatis', 50000, 'Sepeda listrik dengan jarak tempuh 40km', '["Battery 48V", "LCD Display", "LED Light"]'),
('Polygon Path 3', 'sepeda', 'Polygon', 'Path 3', 2023, '-', 'Hijau', 1, 'manual', 'manual', 30000, 'Sepeda gunung dengan 21 speed', '["21 Speed", "Disc Brake", "Suspension"]');