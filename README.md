# EzRent

Aplikasi rental kendaraan berbasis PHP & MySQL.

## Fitur
- Booking kendaraan (mobil & motor)
- Pembayaran otomatis via Midtrans (sandbox)
- Dashboard admin & user
- Upload KTP & bukti pembayaran
- Notifikasi status booking

## Instalasi Lokal

1. **Clone repo:**
   ```
   git clone https://github.com/username/ezrent.git
   cd ezrent
   ```

2. **Copy file konfigurasi contoh:**
   - Salin `php/config/database.example.php` menjadi `database.php` dan isi dengan data database kamu.
   - Salin `php/config/midtrans.example.php` menjadi `midtrans.php` dan isi dengan server key & client key Midtrans kamu.

3. **Import database:**
   - Buka `database/ezrent.sql` di phpMyAdmin, lalu import ke database MySQL kamu.

4. **Jalankan di lokal:**
   - Jalankan di Laragon/XAMPP, akses via `http://localhost/ezrent`

## Keamanan

- **JANGAN upload file yang berisi kredensial asli** (`database.php`, `midtrans.php`, `.env`) ke repo publik.
- Gunakan file contoh (`*.example.php`) untuk repo.
- Tambahkan file sensitif ke `.gitignore`.

## Deployment

- Upload semua file ke hosting PHP (misal AlwaysData).
- Import database ke server.
- Edit file konfigurasi di server sesuai environment produksi.

## Lisensi

Project ini untuk pembelajaran. Silakan modifikasi sesuai kebutuhan.
