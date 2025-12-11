# Fitur Diskon EzRent - Panduan Setup

## 🎯 Fitur yang Ditambahkan

Sistem diskon lengkap dengan:
- ✅ Admin panel untuk kelola kode diskon
- ✅ User dapat input kode diskon saat booking
- ✅ Validasi otomatis (min. pembelian, batas waktu, batas penggunaan)
- ✅ 2 tipe diskon: Persentase (%) dan Nominal Tetap (Rp)
- ✅ Tracking penggunaan diskon per user
- ✅ Statistik diskon di admin

---

## 📋 Cara Setup Database

### 1. Import file SQL
Buka phpMyAdmin atau database manager Anda, lalu jalankan file:
```
database/setup_discount_system.sql
```

File ini akan:
- Membuat tabel `discount_codes` (menyimpan kode diskon)
- Membuat tabel `discount_usage` (tracking penggunaan)
- Menambah kolom `discount_code` dan `discount_amount` ke tabel `bookings`
- Insert 4 contoh kode diskon siap pakai

### 2. Kode Diskon Default
Setelah import, sistem sudah memiliki 4 kode diskon aktif:
- **WELCOME10** - Diskon 10% (max Rp 100.000) untuk booking ≥ Rp 500.000
- **HEMAT50K** - Potongan Rp 50.000 untuk booking ≥ Rp 300.000
- **WEEKEND20** - Diskon 20% (max Rp 200.000) untuk booking ≥ Rp 600.000
- **LEBARAN2025** - Diskon 15% (max Rp 300.000) untuk booking ≥ Rp 1.000.000

---

## 🎨 Halaman yang Diupdate

### 1. **Admin - Halaman Diskon** (`pages/admin/discounts.php`)
Fitur:
- Lihat semua kode diskon
- Buat kode diskon baru
- Edit kode diskon
- Hapus kode diskon
- Toggle aktif/nonaktif
- Filter: Aktif, Kadaluarsa, Nonaktif
- Search kode diskon
- Statistik: Total kode, Kode aktif, Digunakan hari ini, Total hemat

**Cara Buat Kode Diskon Baru:**
1. Login sebagai admin
2. Klik menu "Kode Diskon" di sidebar
3. Klik tombol "Buat Kode Diskon"
4. Isi form:
   - Kode Diskon (contoh: TAHUNBARU2025)
   - Tipe: Persentase atau Nominal
   - Nilai diskon
   - Min. booking (opsional)
   - Max. potongan (opsional)
   - Batas penggunaan (opsional)
   - Tanggal mulai & berakhir (opsional)
5. Klik "Simpan Kode Diskon"

### 2. **User - Booking Process** (`pages/user/booking-process.php`)
Ditambahkan:
- Input kode diskon (opsional)
- Validasi otomatis saat submit booking
- Diskon langsung teraplikasi ke total harga

**Cara User Pakai Diskon:**
1. Pilih kendaraan → Klik "Sewa Sekarang"
2. Isi formulir booking
3. Di bagian "Kode Diskon (Opsional)", masukkan kode (contoh: WELCOME10)
4. Klik "Konfirmasi Pemesanan"
5. Sistem otomatis validasi dan apply diskon

---

## 🔧 File Baru yang Ditambahkan

```
database/
  └── setup_discount_system.sql         # SQL untuk setup tabel diskon

pages/admin/
  └── discounts.php                      # Halaman admin kelola diskon

php/api/
  └── validate-discount.php              # API untuk validasi kode diskon (future use)
```

---

## 🎯 Cara Kerja Sistem Diskon

### Validasi Kode Diskon:
1. ✅ Kode harus aktif (`is_active = 1`)
2. ✅ Belum kadaluarsa (cek `end_date`)
3. ✅ Sudah dimulai (cek `start_date`)
4. ✅ Nilai booking ≥ minimum yang ditentukan
5. ✅ Belum mencapai batas penggunaan

### Perhitungan Diskon:
- **Tipe Persentase:** `discount = (total × percentage) / 100`
- **Tipe Nominal:** `discount = fixed_amount`
- Jika ada `max_discount_amount`, diskon tidak boleh melebihi batas ini
- Diskon tidak boleh melebihi total booking

### Contoh Perhitungan:
**Booking Rp 1.000.000 dengan kode WELCOME10 (10%, max Rp 100.000):**
- Diskon 10% = Rp 100.000
- Max diskon = Rp 100.000 ✅
- Total bayar = Rp 900.000

**Booking Rp 2.000.000 dengan kode WELCOME10:**
- Diskon 10% = Rp 200.000
- Max diskon = Rp 100.000 ❌ (dikurangi jadi Rp 100.000)
- Total bayar = Rp 1.900.000

---

## 📊 Tabel Database

### `discount_codes`
Menyimpan data kode diskon:
- `code` - Kode unik (contoh: WELCOME10)
- `discount_type` - percentage / fixed
- `discount_value` - Nilai diskon
- `min_booking_amount` - Min. pembelian
- `max_discount_amount` - Batas maksimal potongan
- `usage_limit` - Batas penggunaan
- `used_count` - Sudah dipakai berapa kali
- `start_date` / `end_date` - Periode berlaku
- `is_active` - Status aktif/nonaktif

### `discount_usage`
Tracking siapa pakai diskon apa:
- `discount_id` - ID kode diskon
- `user_id` - ID user
- `booking_id` - ID booking
- `discount_amount` - Berapa diskon yang didapat
- `used_at` - Kapan dipakai

### Update `bookings`
Ditambah 2 kolom:
- `discount_code` - Kode yang dipakai
- `discount_amount` - Nilai diskon yang didapat

---

## 🧪 Testing

### Test sebagai Admin:
1. Login ke admin panel
2. Masuk ke "Kode Diskon"
3. Buat kode baru (contoh: TEST50K, Nominal Rp 50.000, min booking Rp 200.000)
4. Toggle aktif/nonaktif
5. Edit kode
6. Lihat statistik

### Test sebagai User:
1. Login sebagai user
2. Pilih kendaraan → Sewa Sekarang
3. Isi form booking (pastikan total ≥ min. booking diskon)
4. Masukkan kode: WELCOME10
5. Submit → Cek apakah total sudah dikurangi diskon

---

## 🚀 Tips & Rekomendasi

### Strategi Diskon:
- **WELCOME10** - Untuk user baru (min. booking kecil)
- **WEEKEND20** - Diskon weekend (min. booking lebih besar)
- **FLASHSALE50K** - Flash sale terbatas (usage_limit kecil)
- **VIP15** - Untuk pelanggan setia (min. booking besar)

### Pengaturan Optimal:
- Set `end_date` untuk membuat urgency
- Gunakan `usage_limit` untuk Flash Sale
- `max_discount_amount` mencegah kerugian pada booking besar
- `min_booking_amount` untuk maintain profit margin

---

## ⚠️ Catatan Penting

1. **Kode diskon UPPERCASE otomatis** - User bisa ketik huruf kecil, sistem auto convert
2. **Validasi real-time** - Diskon langsung dicek saat booking dibuat
3. **Tracking lengkap** - Setiap penggunaan tercatat di `discount_usage`
4. **Cannot delete used discount** - Kode yang sudah dipakai tidak bisa dihapus (protected by foreign key)

---

## 🔥 Fitur Tambahan yang Bisa Dikembangkan

Jika perlu upgrade di masa depan:
- [ ] Auto-apply diskon (tanpa perlu input kode)
- [ ] Diskon khusus user tertentu
- [ ] Diskon khusus kendaraan tertentu
- [ ] Kombinasi multiple diskon
- [ ] Cashback system
- [ ] Referral code
- [ ] Gamification (poin loyalty)

---

## 📞 Support

Jika ada error atau pertanyaan:
1. Cek console browser (F12) untuk error JavaScript
2. Cek error log PHP di server
3. Pastikan semua file sudah diupload
4. Pastikan database sudah diimport

Sistem diskon sudah siap digunakan! 🎉
