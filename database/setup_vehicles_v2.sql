-- =====================================================
-- EzRent Vehicle Setup Script v2
-- Motor: ID 1-20, Mobil: ID 21-40
-- Nama unik EzRent branded (bukan merek nyata)
-- Images terintegrasi dengan file yang tersedia
-- =====================================================

USE ezrent;

-- Disable foreign key checks
SET FOREIGN_KEY_CHECKS = 0;

-- Hapus data kendaraan lama
DELETE FROM vehicles WHERE 1=1;

-- Reset Auto Increment
ALTER TABLE vehicles AUTO_INCREMENT = 1;

-- =====================================================
-- MOTOR (ID 1-20) - EzRent Exclusive Names
-- =====================================================

INSERT INTO vehicles (nama, jenis, merek, model, tahun, plat_nomor, warna, kapasitas, bahan_bakar, transmisi, harga_per_hari, status, deskripsi, fitur, lokasi, images) VALUES

-- Motor 1-9: EzRent Thunder, Storm, Velocity Series
('EzRent Thunder X1', 'motor', 'EzRent Motors', 'Thunder X1', 2024, 'B 1001 EZR', 'Hitam Glossy', 2, 'bensin', 'matic', 85000, 'tersedia', 'Motor sport matic dengan desain agresif dan performa tangguh. Cocok untuk jiwa muda yang energik.', '["LED Projector", "Digital Speedometer", "USB Charger", "ABS", "Keyless"]', 'Jakarta Selatan', '["ez-1.jpg"]'),

('EzRent Thunder X2', 'motor', 'EzRent Motors', 'Thunder X2', 2024, 'B 1002 EZR', 'Merah Racing', 2, 'bensin', 'matic', 95000, 'tersedia', 'Versi upgrade Thunder dengan mesin lebih bertenaga. Sensasi berkendara premium.', '["LED All Light", "Smart Key", "USB Type-C", "ABS", "Traction Control"]', 'Jakarta Selatan', '["ez-2.jpg"]'),

('EzRent Thunder Pro', 'motor', 'EzRent Motors', 'Thunder Pro', 2024, 'B 1003 EZR', 'Biru Electric', 2, 'bensin', 'matic', 110000, 'tersedia', 'Flagship Thunder series dengan fitur premium lengkap. Performa maksimal di kelasnya.', '["Full LED", "TFT Display", "Quick Charge", "Dual ABS", "Riding Mode"]', 'Jakarta Selatan', '["ez-3.jpg"]'),

('EzRent Storm 150', 'motor', 'EzRent Motors', 'Storm 150', 2024, 'B 1004 EZR', 'Silver Metallic', 2, 'bensin', 'matic', 75000, 'tersedia', 'Motor harian yang tangguh dan irit. Desain sporty untuk aktivitas sehari-hari.', '["LED Headlight", "USB Charger", "Bagasi Luas", "Combi Brake"]', 'Jakarta Selatan', '["ez-4.jpg"]'),

('EzRent Storm 200', 'motor', 'EzRent Motors', 'Storm 200', 2024, 'B 1005 EZR', 'Putih Pearl', 2, 'bensin', 'matic', 88000, 'tersedia', 'Storm dengan kapasitas mesin lebih besar. Power dan efisiensi dalam satu paket.', '["LED DRL", "Digital Panel", "USB Charger", "ABS", "Idle Stop"]', 'Jakarta Selatan', '["ez-5.jpg"]'),

('EzRent Velocity 160', 'motor', 'EzRent Motors', 'Velocity 160', 2024, 'B 1006 EZR', 'Hitam Doff', 2, 'bensin', 'matic', 120000, 'tersedia', 'Maxi scooter premium dengan kenyamanan terbaik. Ideal untuk touring dan daily use.', '["LED Projector", "Smart Key", "Windshield", "ABS", "Traction Control"]', 'Jakarta Selatan', '["ez-6.jpg"]'),

('EzRent Velocity 200', 'motor', 'EzRent Motors', 'Velocity 200', 2024, 'B 1007 EZR', 'Abu-abu Titanium', 2, 'bensin', 'matic', 145000, 'tersedia', 'Big scooter dengan mesin 200cc. Performa bertenaga untuk perjalanan jauh.', '["Full LED", "TFT Display", "Cruise Control", "ABS", "Emergency Stop Signal"]', 'Jakarta Selatan', '["ez-7.jpg"]'),

('EzRent Velocity Max', 'motor', 'EzRent Motors', 'Velocity Max', 2024, 'B 1008 EZR', 'Biru Navy', 2, 'bensin', 'matic', 180000, 'tersedia', 'Flagship maxi scooter dengan mesin 250cc. Kemewahan dan performa tanpa kompromi.', '["LED Matrix", "7 inch TFT", "Heated Grip", "Electronic Suspension", "Keyless"]', 'Jakarta Selatan', '["ez-8.jpg"]'),

('EzRent Phantom 125', 'motor', 'EzRent Motors', 'Phantom 125', 2024, 'B 1009 EZR', 'Hijau Army', 2, 'bensin', 'matic', 65000, 'tersedia', 'Motor compact yang lincah dan irit. Sempurna untuk mobilitas urban.', '["LED Light", "USB Port", "Bagasi", "Side Stand Switch"]', 'Jakarta Selatan', '["ez-9.jpg"]'),

-- Motor 10-14: EzRent Blaze, Venom, Raptor Series (Manual Sport)
('EzRent Blaze 150R', 'motor', 'EzRent Motors', 'Blaze 150R', 2024, 'B 1010 EZR', 'Merah Ferrari', 2, 'bensin', 'manual', 130000, 'tersedia', 'Sport bike dengan DNA racing murni. Full fairing aerodinamis untuk performa maksimal.', '["LED Projector", "Full Digital", "Slipper Clutch", "ABS", "Quick Shifter"]', 'Jakarta Selatan', '["ez-1.jpg"]'),

('EzRent Blaze 250RR', 'motor', 'EzRent Motors', 'Blaze 250RR', 2024, 'B 1011 EZR', 'Hijau Racing', 2, 'bensin', 'manual', 200000, 'tersedia', 'Twin cylinder sport bike dengan power 40HP. Sensasi balap di jalanan umum.', '["LED Matrix", "TFT Cluster", "Riding Mode", "Traction Control", "Up/Down QS"]', 'Jakarta Selatan', '["ez-2.jpg"]'),

('EzRent Venom 160', 'motor', 'EzRent Motors', 'Venom 160', 2024, 'B 1012 EZR', 'Hitam Matte', 2, 'bensin', 'manual', 135000, 'tersedia', 'Naked bike dengan karakter agresif. Handling presisi untuk street riding.', '["LED Eyes", "Digital Display", "VVA Engine", "Slipper Clutch"]', 'Jakarta Selatan', '["ez-3.jpg"]'),

('EzRent Venom 200', 'motor', 'EzRent Motors', 'Venom 200', 2024, 'B 1013 EZR', 'Orange Sunset', 2, 'bensin', 'manual', 155000, 'tersedia', 'Naked bike bertenaga dengan torsi melimpah. Dominasi setiap tikungan.', '["Dual LED", "TFT Color", "Ride by Wire", "IMU 6-Axis", "Cornering ABS"]', 'Jakarta Selatan', '["ez-4.jpg"]'),

('EzRent Raptor 250', 'motor', 'EzRent Motors', 'Raptor 250', 2024, 'B 1014 EZR', 'Biru Racing', 2, 'bensin', 'manual', 175000, 'tersedia', 'Adventure sport untuk segala medan. Ground clearance tinggi dengan power besar.', '["LED Adventure", "Rally Display", "Long Travel Suspension", "ABS Off-road"]', 'Jakarta Selatan', '["ez-5.jpg"]'),

-- Motor 15-17: EzRent Volt Electric Series
('EzRent Volt E1', 'motor', 'EzRent Electric', 'Volt E1', 2024, 'EL 1015', 'Putih Futuristik', 2, 'listrik', 'otomatis', 55000, 'tersedia', 'Motor listrik entry level dengan jarak tempuh 80km. Zero emission untuk kota hijau.', '["LCD Display", "Regen Brake", "USB Charger", "GPS Tracking", "App Connect"]', 'Jakarta Selatan', '["ez-6.jpg"]'),

('EzRent Volt E2 Plus', 'motor', 'EzRent Electric', 'Volt E2 Plus', 2024, 'EL 1016', 'Biru Cyber', 2, 'listrik', 'otomatis', 70000, 'tersedia', 'Motor listrik dengan baterai swappable. Jarak tempuh 120km tanpa khawatir.', '["TFT Display", "Dual Battery", "Fast Charge", "Bluetooth", "Anti Theft"]', 'Jakarta Selatan', '["ez-7.jpg"]'),

('EzRent Volt Pro Max', 'motor', 'EzRent Electric', 'Volt Pro Max', 2024, 'EL 1017', 'Hitam Stealth', 2, 'listrik', 'otomatis', 95000, 'tersedia', 'Flagship motor listrik dengan performa setara 250cc. Masa depan mobilitas.', '["7 inch Touch", "Triple Battery", "DC Fast Charge", "AI Assistant", "OTA Update"]', 'Jakarta Selatan', '["ez-8.jpg"]'),

-- Motor 18-20: EzRent Classico & Heritage Series (Retro)
('EzRent Classico 150', 'motor', 'EzRent Motors', 'Classico 150', 2024, 'B 1018 EZR', 'Cream Vintage', 2, 'bensin', 'matic', 140000, 'tersedia', 'Retro scooter dengan sentuhan modern. Elegan dan timeless untuk setiap momen.', '["LED Classic", "Chrome Accent", "Brown Leather Seat", "ABS", "USB Hidden"]', 'Jakarta Selatan', '["ez-9.jpg"]'),

('EzRent Classico 200', 'motor', 'EzRent Motors', 'Classico 200', 2024, 'B 1019 EZR', 'Hijau British', 2, 'bensin', 'matic', 165000, 'tersedia', 'Premium retro dengan mesin lebih bertenaga. Gaya klasik performa modern.', '["LED Retro", "Digital Classic", "Tan Leather", "Dual ABS", "Bluetooth"]', 'Jakarta Selatan', '["ez-1.jpg"]'),

('EzRent Heritage 300', 'motor', 'EzRent Motors', 'Heritage 300', 2024, 'B 1020 EZR', 'Hitam Classic', 2, 'bensin', 'matic', 220000, 'tersedia', 'Grand touring retro dengan kemewahan tertinggi. Ikon gaya yang tak lekang waktu.', '["Matrix LED", "TFT Vintage", "Heated Seat", "Cruise Control", "Premium Audio"]', 'Jakarta Selatan', '["ez-2.jpg"]'),

-- =====================================================
-- MOBIL (ID 21-40) - EzRent Exclusive Names
-- =====================================================

-- Mobil 21-25: EzRent City & Spark Series (Compact & City Car)
('EzRent City Runner', 'mobil', 'EzRent Auto', 'City Runner', 2024, 'B 2021 EZR', 'Kuning Energic', 5, 'bensin', 'matic', 280000, 'tersedia', 'City car lincah untuk jalanan kota. Compact tapi spacious, irit dan stylish.', '["AC Digital", "Rear Camera", "Keyless", "Audio 7 inch", "LED DRL"]', 'Jakarta Selatan', '["ez-21.jpg"]'),

('EzRent City Prime', 'mobil', 'EzRent Auto', 'City Prime', 2024, 'B 2022 EZR', 'Putih Elegant', 5, 'bensin', 'matic', 320000, 'tersedia', 'Premium city car dengan fitur lengkap. Nyaman dan ekonomis untuk harian.', '["Climate Control", "360 Camera", "Sunroof", "Leather Seat", "Wireless Charger"]', 'Jakarta Selatan', '["ez-22.jpg"]'),

('EzRent Spark 1.2', 'mobil', 'EzRent Auto', 'Spark 1.2', 2024, 'B 2023 EZR', 'Merah Cherry', 5, 'bensin', 'matic', 250000, 'tersedia', 'Entry level hatchback yang fun to drive. Desain modern dengan biaya terjangkau.', '["AC", "Audio Touchscreen", "Rear Camera", "Power Window", "Central Lock"]', 'Jakarta Selatan', '["ez-23.jpg"]'),

('EzRent Spark Sport', 'mobil', 'EzRent Auto', 'Spark Sport', 2024, 'B 2024 EZR', 'Biru Ocean', 5, 'bensin', 'matic', 300000, 'tersedia', 'Hot hatch dengan tampilan sporty. Performa lebih untuk pengalaman berkendara seru.', '["Sport Suspension", "LED Headlamp", "Sport Seat", "Paddle Shift", "Drive Mode"]', 'Jakarta Selatan', '["ez-24.jpg"]'),

('EzRent Metro Plus', 'mobil', 'EzRent Auto', 'Metro Plus', 2024, 'B 2025 EZR', 'Silver Moon', 5, 'bensin', 'matic', 270000, 'tersedia', 'Urban mobility solution yang praktis. Compact di luar, lega di dalam.', '["Smart AC", "Android Auto", "Rear Camera", "Auto Light", "Rain Sensor"]', 'Jakarta Selatan', '["ez-25.jpg"]'),

-- Mobil 26-29: EzRent Family Series (MPV)
('EzRent Family 7', 'mobil', 'EzRent Auto', 'Family 7', 2024, 'B 2026 EZR', 'Putih Pearl', 7, 'bensin', 'matic', 380000, 'tersedia', 'MPV 7 seater untuk keluarga Indonesia. Kabin luas dengan fitur keselamatan lengkap.', '["AC Double Blower", "Captain Seat Row 2", "Rear Camera", "ISOFIX", "6 Airbags"]', 'Jakarta Selatan', '["ez-26.jpg"]'),

('EzRent Family Pro', 'mobil', 'EzRent Auto', 'Family Pro', 2024, 'B 2027 EZR', 'Hitam Elegant', 7, 'bensin', 'matic', 450000, 'tersedia', 'Premium MPV dengan kenyamanan ekstra. Perjalanan keluarga jadi lebih menyenangkan.', '["Auto AC", "Ottoman Seat", "Sunroof", "Premium Audio", "Ambient Light"]', 'Jakarta Selatan', '["ez-27.jpg"]'),

('EzRent Grand Family', 'mobil', 'EzRent Auto', 'Grand Family', 2024, 'B 2028 EZR', 'Abu-abu Graphite', 7, 'solar', 'matic', 550000, 'tersedia', 'Large MPV dengan mesin diesel bertenaga. Kenyamanan maksimal untuk perjalanan jauh.', '["Tri-Zone AC", "Electric Seat", "Rear Entertainment", "ADAS", "Power Sliding Door"]', 'Jakarta Selatan', '["ez-28.jpg"]'),

('EzRent Grand Family VIP', 'mobil', 'EzRent Auto', 'Grand Family VIP', 2024, 'B 2029 EZR', 'Putih Mutiara', 7, 'bensin', 'matic', 750000, 'tersedia', 'Ultimate luxury MPV untuk eksekutif. First class experience di setiap perjalanan.', '["Executive Lounge Seat", "JBL Surround", "Dual Sunroof", "Massage Seat", "Mini Bar"]', 'Jakarta Selatan', '["ez-29.jpg"]'),

-- Mobil 30-33: EzRent Explorer & Titan Series (SUV)
('EzRent Explorer 4x2', 'mobil', 'EzRent Auto', 'Explorer 4x2', 2024, 'B 2030 EZR', 'Silver Titanium', 7, 'bensin', 'matic', 420000, 'tersedia', 'Compact SUV untuk petualangan urban. Ground clearance tinggi dengan gaya modern.', '["LED Projector", "Panoramic Camera", "Roof Rail", "Hill Assist", "Sport Mode"]', 'Jakarta Selatan', '["ez-30.jpg"]'),

('EzRent Explorer 4x4', 'mobil', 'EzRent Auto', 'Explorer 4x4', 2024, 'B 2031 EZR', 'Hijau Army', 7, 'solar', 'matic', 550000, 'tersedia', 'True SUV dengan sistem 4WD. Siap temani petualangan off-road Anda.', '["4x4 System", "Terrain Mode", "Skid Plate", "Diff Lock", "Hill Descent Control"]', 'Jakarta Selatan', '["ez-31.jpg"]'),

('EzRent Titan SUV', 'mobil', 'EzRent Auto', 'Titan SUV', 2024, 'B 2032 EZR', 'Hitam Obsidian', 7, 'solar', 'matic', 850000, 'tersedia', 'Full size SUV dengan kemampuan segala medan. Tangguh dan mewah.', '["4x4 Permanent", "Air Suspension", "360 Camera", "Bose Audio", "Adaptive Cruise"]', 'Jakarta Selatan', '["ez-21.jpg"]'),

('EzRent Titan Ultimate', 'mobil', 'EzRent Auto', 'Titan Ultimate', 2024, 'B 2033 EZR', 'Putih Diamond', 7, 'solar', 'matic', 1200000, 'tersedia', 'Flagship SUV dengan kemewahan tanpa batas. Raja segala medan.', '["Super Select 4WD", "Rear Entertainment", "Massaging Seat", "Night Vision", "ADAS Pro"]', 'Jakarta Selatan', '["ez-22.jpg"]'),

-- Mobil 34-36: EzRent Prestige & Royal Series (Sedan)
('EzRent Prestige 2.0', 'mobil', 'EzRent Auto', 'Prestige 2.0', 2024, 'B 2034 EZR', 'Hitam Midnight', 5, 'bensin', 'matic', 600000, 'tersedia', 'Executive sedan untuk profesional modern. Elegan di setiap kesempatan.', '["Leather Interior", "Bose Audio", "Sunroof", "Ventilated Seat", "HUD"]', 'Jakarta Selatan', '["ez-23.jpg"]'),

('EzRent Prestige 2.5 Turbo', 'mobil', 'EzRent Auto', 'Prestige 2.5 Turbo', 2024, 'B 2035 EZR', 'Biru Navy', 5, 'bensin', 'matic', 800000, 'tersedia', 'Sport sedan dengan mesin turbo. Performa tinggi dengan kemewahan.', '["Turbo Engine", "Adaptive Suspension", "Premium Audio", "360 Camera", "ADAS"]', 'Jakarta Selatan', '["ez-24.jpg"]'),

('EzRent Royal 3.5', 'mobil', 'EzRent Auto', 'Royal 3.5', 2024, 'B 2036 EZR', 'Hitam VIP', 5, 'bensin', 'matic', 1500000, 'tersedia', 'Flagship sedan untuk kalangan elite. Simbol kesuksesan dan prestise.', '["V6 Engine", "Air Purifier", "Rear Reclining Seat", "Champagne Cooler", "Privacy Glass"]', 'Jakarta Selatan', '["ez-25.jpg"]'),

-- Mobil 37-38: EzRent Cross Series (Crossover)
('EzRent Cross 1.5', 'mobil', 'EzRent Auto', 'Cross 1.5', 2024, 'B 2037 EZR', 'Orange Copper', 5, 'bensin', 'matic', 380000, 'tersedia', 'Compact crossover yang stylish. Sempurna untuk gaya hidup aktif.', '["LED Signature", "Contrast Roof", "Sport Seat", "Paddle Shift", "Auto Tailgate"]', 'Jakarta Selatan', '["ez-26.jpg"]'),

('EzRent Cross 2.0 Turbo', 'mobil', 'EzRent Auto', 'Cross 2.0 Turbo', 2024, 'B 2038 EZR', 'Merah Metallic', 5, 'bensin', 'matic', 520000, 'tersedia', 'Performance crossover dengan mesin turbo. Handling sporty untuk daily driver.', '["Turbo Direct Injection", "AWD", "Sport Exhaust", "Brembo Brake", "Launch Control"]', 'Jakarta Selatan', '["ez-27.jpg"]'),

-- Mobil 39-40: EzRent eVolt Series (Electric Car)
('EzRent eVolt City', 'mobil', 'EzRent Electric', 'eVolt City', 2024, 'EL 2039', 'Putih Futuristik', 5, 'listrik', 'otomatis', 450000, 'tersedia', 'Electric city car dengan jarak tempuh 300km. Masa depan mobilitas urban.', '["Battery 40kWh", "DC Fast Charge", "Regen Paddle", "Digital Cockpit", "OTA Update"]', 'Jakarta Selatan', '["ez-28.jpg"]'),

('EzRent eVolt Prime', 'mobil', 'EzRent Electric', 'eVolt Prime', 2024, 'EL 2040', 'Biru Electric', 5, 'listrik', 'otomatis', 700000, 'tersedia', 'Premium electric sedan dengan jarak tempuh 500km. Zero emission, full performance.', '["Battery 77kWh", "800V Architecture", "Autonomous Lv2", "Glass Roof", "AI Cockpit"]', 'Jakarta Selatan', '["ez-29.jpg"]');

-- =====================================================
-- Re-enable foreign key checks
-- =====================================================
SET FOREIGN_KEY_CHECKS = 1;

-- =====================================================
-- Verifikasi Data
-- =====================================================
SELECT 
    CASE 
        WHEN jenis = 'motor' THEN 'MOTOR (ID 1-20)'
        ELSE 'MOBIL (ID 21-40)'
    END as kategori,
    COUNT(*) as jumlah
FROM vehicles 
GROUP BY jenis;

SELECT id, nama, merek, jenis, harga_per_hari, status, images FROM vehicles ORDER BY id;
