<?php
session_start();
require_once '../../php/config/database.php';

// Cek Login
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: ../login.php");
    exit;
}

// Cek Vehicle ID
if (!isset($_GET['vehicle_id'])) {
    header("Location: vehicles.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['nama_lengkap'] ?? '';
$v_id = $_GET['vehicle_id'];

// Ambil data kendaraan
$stmt = $pdo->prepare("SELECT * FROM vehicles WHERE id = ?");
$stmt->execute([$v_id]);
$vehicle = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$vehicle) {
    header("Location: vehicles.php");
    exit;
}

// Parse images
$images = json_decode($vehicle['images'], true);
$image = isset($images[0]) ? $images[0] : 'default.jpg';

// Parse fitur
$fitur = json_decode($vehicle['fitur'], true) ?? [];

// Proses Submit Booking
$success = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $start_date = $_POST['start_date'] ?? '';
    $end_date = $_POST['end_date'] ?? '';
    $pickup = $_POST['pickup'] ?? 'Kantor Pusat';
    $return_loc = $_POST['return'] ?? 'Kantor Pusat';
    $ktp_number = $_POST['ktp_number'] ?? '';
    $notes = $_POST['notes'] ?? '';
    $ktp_image = '';
    
    if (empty($start_date) || empty($end_date)) {
        $error = "Tanggal sewa harus diisi!";
    } elseif (empty($ktp_number)) {
        $error = "Nomor KTP harus diisi!";
    } elseif (!isset($_FILES['ktp_image']) || $_FILES['ktp_image']['error'] !== UPLOAD_ERR_OK) {
        $error = "Foto KTP harus diunggah!";
    } else {
        // Validate KTP number format (Indonesian ID: 16 digits)
        if (!preg_match('/^\d{16}$/', $ktp_number)) {
            $error = "Nomor KTP harus 16 digit angka!";
        } else {
            $start = new DateTime($start_date);
            $end = new DateTime($end_date);
            
            if ($end < $start) {
                $error = "Tanggal selesai tidak boleh sebelum tanggal mulai!";
            } else {
                // Process KTP upload
                $file = $_FILES['ktp_image'];
                $allowed = ['jpg', 'jpeg', 'png', 'pdf'];
                $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                
                if (!in_array($file_ext, $allowed)) {
                    $error = "Format file harus JPG, PNG, atau PDF!";
                } else if ($file['size'] > 5 * 1024 * 1024) {
                    $error = "Ukuran file maksimal 5MB!";
                } else {
                    // Create uploads directory if not exists
                    $upload_dir = '../../php/uploads/ktp/';
                    if (!is_dir($upload_dir)) {
                        mkdir($upload_dir, 0755, true);
                    }
                    
                    // Generate unique filename
                    $ktp_image = 'KTP_' . $user_id . '_' . time() . '.' . $file_ext;
                    $upload_path = $upload_dir . $ktp_image;
                    
                    if (move_uploaded_file($file['tmp_name'], $upload_path)) {
                        // File uploaded successfully, proceed with booking
                        $days = $start->diff($end)->days + 1;
                        $total = $days * $vehicle['harga_per_hari'];
                        $kode = 'EZR-' . date('Ymd') . '-' . rand(1000, 9999);
                        
                        // Process discount if provided
                        $discount_code = strtoupper(trim($_POST['discount_code'] ?? ''));
                        $discount_amount = 0;
                        $discount_id = null;
                        
                        if (!empty($discount_code)) {
                            $stmt_disc = $pdo->prepare("
                                SELECT * FROM discount_codes 
                                WHERE code = ? AND is_active = 1 
                                AND (start_date IS NULL OR start_date <= NOW())
                                AND (end_date IS NULL OR end_date > NOW())
                            ");
                            $stmt_disc->execute([$discount_code]);
                            $discount = $stmt_disc->fetch(PDO::FETCH_ASSOC);
                            
                            if ($discount) {
                                // Check minimum amount
                                if ($total >= $discount['min_booking_amount']) {
                                    // Check usage limit
                                    if (!$discount['usage_limit'] || $discount['used_count'] < $discount['usage_limit']) {
                                        // Calculate discount
                                        if ($discount['discount_type'] === 'percentage') {
                                            $discount_amount = ($total * $discount['discount_value']) / 100;
                                        } else {
                                            $discount_amount = $discount['discount_value'];
                                        }
                                        
                                        // Apply max discount limit
                                        if ($discount['max_discount_amount'] && $discount_amount > $discount['max_discount_amount']) {
                                            $discount_amount = $discount['max_discount_amount'];
                                        }
                                        
                                        // Ensure discount doesn't exceed total
                                        if ($discount_amount > $total) {
                                            $discount_amount = $total;
                                        }
                                        
                                        $discount_id = $discount['id'];
                                        $total = $total - $discount_amount;
                                        
                                        // Update discount usage count
                                        $pdo->prepare("UPDATE discount_codes SET used_count = used_count + 1 WHERE id = ?")->execute([$discount_id]);
                                    }
                                }
                            }
                        }
                        
                        try {
                            $pdo->beginTransaction();
                            
                            // Insert booking with KTP info and discount
                            $sql = "INSERT INTO bookings (user_id, vehicle_id, kode_booking, start_date, end_date, total_days, total_price, discount_code, discount_amount, pickup_location, return_location, ktp_number, ktp_image, status, notes, created_at) 
                                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', ?, NOW())";
                            $stmt = $pdo->prepare($sql);
                            $stmt->execute([$user_id, $v_id, $kode, $start_date, $end_date, $days, $total, $discount_code ?: null, $discount_amount, $pickup, $return_loc, $ktp_number, $ktp_image, $notes]);
                            
                            $booking_id = $pdo->lastInsertId();
                            
                            // Record discount usage if applied
                            if ($discount_id && $discount_amount > 0) {
                                $pdo->prepare("INSERT INTO discount_usage (discount_id, user_id, booking_id, discount_amount) VALUES (?, ?, ?, ?)")
                                    ->execute([$discount_id, $user_id, $booking_id, $discount_amount]);
                            }
                            
                            $pdo->commit();
                            
                            // Redirect ke payment gateway
                            header("Location: payment-gateway.php?booking_id=" . $booking_id);
                            exit;
                            
                        } catch (Exception $e) {
                            $pdo->rollBack();
                            // Delete uploaded file if booking fails
                            @unlink($upload_path);
                            $error = "Gagal membuat booking: " . $e->getMessage();
                        }
                    } else {
                        $error = "Gagal mengunggah foto KTP. Silakan coba lagi.";
                    }
                }
            }
        }
    }
}

$page_title = "Booking " . $vehicle['nama'] . " - EzRent";
include 'header.php';
?>

<style>
    .booking-page {
        background: linear-gradient(180deg, #0a0a0a 0%, #1a1a1a 100%);
        min-height: 100vh;
        padding-top: 100px;
        padding-bottom: 4rem;
    }
    
    .booking-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 20px;
    }
    
    .booking-header {
        text-align: center;
        margin-bottom: 3rem;
    }
    
    .booking-header h1 {
        color: #fff;
        font-size: 2rem;
        font-weight: 300;
        margin-bottom: 0.5rem;
    }
    
    .booking-header h1 strong {
        font-weight: 700;
        color: #d50000;
    }
    
    .booking-header p {
        color: rgba(255,255,255,0.6);
    }
    
    .booking-grid {
        display: grid;
        grid-template-columns: 1fr 400px;
        gap: 2rem;
    }
    
    @media (max-width: 992px) {
        .booking-grid {
            grid-template-columns: 1fr;
        }
    }
    
    /* Form Card */
    .form-card {
        background: #111;
        border: 1px solid rgba(255,255,255,0.1);
        border-radius: 16px;
        padding: 2rem;
    }
    
    .form-card h3 {
        color: #fff;
        font-size: 1.25rem;
        font-weight: 600;
        margin-bottom: 1.5rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid rgba(255,255,255,0.1);
    }
    
    .form-group {
        margin-bottom: 1.5rem;
    }
    
    .form-group label {
        display: block;
        color: rgba(255,255,255,0.8);
        font-size: 0.9rem;
        font-weight: 500;
        margin-bottom: 0.5rem;
    }
    
    .form-control {
        width: 100%;
        padding: 0.875rem 1rem;
        background: rgba(255,255,255,0.05);
        border: 1px solid rgba(255,255,255,0.15);
        border-radius: 8px;
        color: #fff !important;
        font-size: 1rem;
        transition: all 0.3s ease;
        -webkit-text-fill-color: #fff !important;
    }
    
    .form-control:focus {
        outline: none;
        border-color: #d50000;
        background: rgba(255,255,255,0.08);
    }
    
    .form-control::placeholder {
        color: rgba(255,255,255,0.4) !important;
        -webkit-text-fill-color: rgba(255,255,255,0.4) !important;
    }
    
    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
    }
    
    @media (max-width: 576px) {
        .form-row {
            grid-template-columns: 1fr;
        }
    }
    
    select.form-control {
        cursor: pointer;
    }
    
    select.form-control option {
        background: #1a1a1a;
        color: #fff;
    }
    
    textarea.form-control {
        min-height: 100px;
        resize: vertical;
    }
    
    .btn-submit {
        width: 100%;
        padding: 1rem;
        background: linear-gradient(135deg, #d50000 0%, #b71c1c 100%);
        color: #fff;
        border: none;
        border-radius: 8px;
        font-size: 1rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        cursor: pointer;
        transition: all 0.3s ease;
        margin-top: 1rem;
    }
    
    .btn-submit:hover {
        background: linear-gradient(135deg, #b71c1c 0%, #8b0000 100%);
        transform: translateY(-2px);
        box-shadow: 0 10px 30px rgba(213, 0, 0, 0.3);
    }
    
    .btn-back {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        color: rgba(255,255,255,0.6);
        text-decoration: none;
        font-size: 0.9rem;
        margin-bottom: 1.5rem;
        transition: color 0.3s ease;
    }
    
    .btn-back:hover {
        color: #fff;
    }
    
    /* Vehicle Summary Card */
    .summary-card {
        background: #111;
        border: 1px solid rgba(255,255,255,0.1);
        border-radius: 16px;
        overflow: hidden;
        position: sticky;
        top: 100px;
    }
    
    .summary-image {
        position: relative;
        aspect-ratio: 16/10;
        overflow: hidden;
    }
    
    .summary-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .summary-badge {
        position: absolute;
        top: 1rem;
        left: 1rem;
        background: rgba(0,0,0,0.8);
        color: #fff;
        padding: 0.4rem 0.8rem;
        border-radius: 4px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
    }
    
    .summary-content {
        padding: 1.5rem;
    }
    
    .summary-title {
        color: #fff;
        font-size: 1.25rem;
        font-weight: 600;
        margin-bottom: 0.25rem;
    }
    
    .summary-model {
        color: rgba(255,255,255,0.5);
        font-size: 0.9rem;
        margin-bottom: 1rem;
    }
    
    .summary-specs {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        margin-bottom: 1.5rem;
    }
    
    .spec-tag {
        background: rgba(255,255,255,0.05);
        color: rgba(255,255,255,0.7);
        padding: 0.3rem 0.6rem;
        border-radius: 4px;
        font-size: 0.75rem;
    }
    
    .summary-features {
        margin-bottom: 1.5rem;
        padding-bottom: 1.5rem;
        border-bottom: 1px solid rgba(255,255,255,0.1);
    }
    
    .summary-features h4 {
        color: rgba(255,255,255,0.8);
        font-size: 0.85rem;
        font-weight: 600;
        margin-bottom: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    
    .features-list {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
    }
    
    .feature-item {
        background: rgba(213, 0, 0, 0.1);
        color: #ff5252;
        padding: 0.25rem 0.5rem;
        border-radius: 4px;
        font-size: 0.7rem;
    }
    
    .price-breakdown {
        background: rgba(255,255,255,0.03);
        border-radius: 8px;
        padding: 1rem;
    }
    
    .price-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.5rem 0;
        color: rgba(255,255,255,0.7);
        font-size: 0.9rem;
    }
    
    .price-row.total {
        border-top: 1px solid rgba(255,255,255,0.1);
        margin-top: 0.5rem;
        padding-top: 1rem;
        color: #fff;
        font-weight: 700;
        font-size: 1.1rem;
    }
    
    .price-row.total .amount {
        color: #d50000;
        font-size: 1.25rem;
    }
    
    /* Alert */
    .alert {
        padding: 1rem;
        border-radius: 8px;
        margin-bottom: 1.5rem;
        font-size: 0.9rem;
    }
    
    .alert-danger {
        background: rgba(239, 68, 68, 0.1);
        border: 1px solid rgba(239, 68, 68, 0.3);
        color: #ef4444;
    }
    
    .alert-success {
        background: rgba(34, 197, 94, 0.1);
        border: 1px solid rgba(34, 197, 94, 0.3);
        color: #22c55e;
    }
</style>

<main class="booking-page">
    <div class="booking-container">
        
        <a href="vehicles.php" class="btn-back">← Kembali</a>
        
        <div class="booking-header">
            <h1>Formulir <strong>Pemesanan</strong></h1>
            <p>Lengkapi data berikut untuk menyewa kendaraan</p>
        </div>
        
        <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <div class="booking-grid">
            <!-- Form Section -->
            <div class="form-card">
                <h3>Detail Pemesanan</h3>
                
                <form method="POST" id="bookingForm" enctype="multipart/form-data">
                    <div class="form-row">
                        <div class="form-group">
                            <label>Tanggal Mulai Sewa</label>
                            <input type="date" name="start_date" id="start_date" class="form-control" required 
                                   min="<?php echo date('Y-m-d'); ?>" 
                                   value="<?php echo $_POST['start_date'] ?? ''; ?>">
                        </div>
                        <div class="form-group">
                            <label>Tanggal Selesai Sewa</label>
                            <input type="date" name="end_date" id="end_date" class="form-control" required 
                                   min="<?php echo date('Y-m-d'); ?>"
                                   value="<?php echo $_POST['end_date'] ?? ''; ?>">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Lokasi Pengambilan</label>
                            <select name="pickup" class="form-control">
                                <option value="Kantor Pusat Jakarta">Kantor Pusat Jakarta</option>
                                <option value="Bandara Soekarno-Hatta">Bandara Soekarno-Hatta</option>
                                <option value="Stasiun Gambir">Stasiun Gambir</option>
                                <option value="Mall Grand Indonesia">Mall Grand Indonesia</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Lokasi Pengembalian</label>
                            <select name="return" class="form-control">
                                <option value="Kantor Pusat Jakarta">Kantor Pusat Jakarta</option>
                                <option value="Bandara Soekarno-Hatta">Bandara Soekarno-Hatta</option>
                                <option value="Stasiun Gambir">Stasiun Gambir</option>
                                <option value="Mall Grand Indonesia">Mall Grand Indonesia</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Catatan Tambahan (Opsional)</label>
                        <textarea name="notes" class="form-control" placeholder="Contoh: Butuh child seat, pengantaran ke hotel, dll..."><?php echo $_POST['notes'] ?? ''; ?></textarea>
                    </div>
                    
                    <!-- Discount Code Section -->
                    <div style="padding: 1.5rem; background: rgba(213,0,0,0.05); border-radius: 8px; border: 1px dashed rgba(213,0,0,0.3); margin-bottom: 1.5rem;">
                        <h4 style="color: #ff5252; font-size: 1rem; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
                            <i class="fas fa-tag"></i> Kode Diskon (Opsional)
                        </h4>
                        <div class="form-group" style="margin-bottom: 0;">
                            <label>Punya kode promo? Masukkan di sini</label>
                            <input type="text" name="discount_code" id="discount_code" class="form-control" 
                                   placeholder="Contoh: WELCOME10, HEMAT50K" style="text-transform: uppercase;"
                                   value="<?php echo $_POST['discount_code'] ?? ''; ?>">
                            <small style="color: rgba(255,255,255,0.5); display: block; margin-top: 0.25rem;">
                                Kode diskon akan divalidasi saat pemesanan dibuat
                            </small>
                        </div>
                        <button type="button" id="applyDiscountBtn" class="btn-submit" style="margin-top: 1rem; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);" onclick="validateDiscount()">
                            <i class="fas fa-tag"></i> Terapkan Kode Diskon
                        </button>
                        <div id="discountMessage" style="margin-top: 0.5rem; font-size: 0.85rem;"></div>
                    </div>
                    
                
                    <!-- KTP Section -->
                    <div style="padding-top: 1.5rem; border-top: 1px solid rgba(255,255,255,0.1); margin-top: 1.5rem;">
                        <h4 style="color: #fff; font-size: 1rem; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
                            <span style="color: #d50000;">*</span> Verifikasi Identitas (Jaminan)
                        </h4>
                        
                        <div class="form-group">
                            <label>Nomor Kartu Tanda Penduduk (KTP)</label>
                            <input type="text" name="ktp_number" id="ktp_number" class="form-control" 
                                   placeholder="1234567890123456" maxlength="16"
                                   pattern="\d{16}" title="KTP harus 16 digit angka"
                                   value="<?php echo $_POST['ktp_number'] ?? ''; ?>" required>
                            <small style="color: rgba(255,255,255,0.5); display: block; margin-top: 0.25rem;">
                                Masukkan 16 digit nomor KTP Anda (tanpa spasi)
                            </small>
                        </div>
                        
                        <div class="form-group">
                            <label>Foto KTP (JPG, PNG, atau PDF - Max 5MB)</label>
                            <div style="position: relative; display: flex; align-items: center;">
                                <input type="file" name="ktp_image" id="ktp_image" class="form-control" 
                                       accept=".jpg,.jpeg,.png,.pdf" required
                                       style="cursor: pointer;">
                            </div>
                            <small style="color: rgba(255,255,255,0.5); display: block; margin-top: 0.25rem;">
                                Format: JPG, PNG, atau PDF | Ukuran maksimal: 5 MB | Pastikan foto jelas dan terbaca
                            </small>
                            <div id="file-preview" style="margin-top: 1rem; display: none;">
                                <div style="background: rgba(255,255,255,0.05); padding: 1rem; border-radius: 8px; border: 1px solid rgba(255,255,255,0.1);">
                                    <p style="color: rgba(255,255,255,0.7); margin: 0; font-size: 0.9rem;">
                                        <strong>File terpilih:</strong> <span id="file-name"></span>
                                    </p>
                                    <p style="color: rgba(255,255,255,0.5); margin: 0.25rem 0 0 0; font-size: 0.85rem;">
                                        <span id="file-size"></span>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    
                </form>
            </div>
            
            <!-- Vehicle Summary -->
            <div class="summary-card">
                <div class="summary-image">
                    <img src="../../assets/images/vehicles/<?php echo htmlspecialchars($image); ?>" 
                         alt="<?php echo htmlspecialchars($vehicle['nama']); ?>"
                         onerror="this.src='../../assets/images/vehicles/default.jpg'">
                    <span class="summary-badge"><?php echo htmlspecialchars($vehicle['merek']); ?></span>
                </div>
                
                <div class="summary-content">
                    <h3 class="summary-title"><?php echo htmlspecialchars($vehicle['nama']); ?></h3>
                    <p class="summary-model"><?php echo htmlspecialchars($vehicle['model']); ?></p>
                    
                    <div class="summary-specs">
                        <span class="spec-tag"><?php echo $vehicle['tahun']; ?></span>
                        <span class="spec-tag"><?php echo htmlspecialchars($vehicle['warna']); ?></span>
                        <span class="spec-tag"><?php echo ucfirst($vehicle['transmisi']); ?></span>
                        <?php if ($vehicle['jenis'] === 'mobil'): ?>
                        <span class="spec-tag"><?php echo $vehicle['kapasitas']; ?> Kursi</span>
                        <?php endif; ?>
                        <span class="spec-tag"><?php echo ucfirst($vehicle['bahan_bakar']); ?></span>
                    </div>
                    
                    <?php if (!empty($fitur)): ?>
                    <div class="summary-features">
                        <h4>Fitur</h4>
                        <div class="features-list">
                            <?php foreach (array_slice($fitur, 0, 6) as $f): ?>
                            <span class="feature-item"><?php echo htmlspecialchars($f); ?></span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <div class="price-breakdown">
                        <div class="price-row">
                            <span>Harga per hari</span>
                            <span>Rp <?php echo number_format($vehicle['harga_per_hari'], 0, ',', '.'); ?></span>
                        </div>
                        <div class="price-row">
                            <span>Durasi sewa</span>
                            <span id="duration">0 hari</span>
                        </div>
                        <div class="price-row" id="discountRow" style="display: none; color: #22c55e; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 1rem; margin-top: 0.5rem;">
                            <span id="discountLabel">Diskon</span>
                            <span id="discountAmount">-Rp 0</span>
                        </div>
                        <div class="price-row total">
                            <span>Total Bayar</span>
                            <span class="amount" id="totalPrice">Rp 0</span>
                        </div>
                        
                        <button type="submit" class="btn-submit" style="margin-top: 1.5rem;">
                        Konfirmasi Pemesanan
                        </button>
                        
                    </div>
                </div>
            </div>
        </div>
        
    </div>
</main>

<script>
const pricePerDay = <?php echo $vehicle['harga_per_hari']; ?>;
const startInput = document.getElementById('start_date');
const endInput = document.getElementById('end_date');
const durationEl = document.getElementById('duration');
const totalEl = document.getElementById('totalPrice');
const discountInput = document.getElementById('discount_code');
const discountRow = document.getElementById('discountRow');
const discountLabel = document.getElementById('discountLabel');
const discountAmount = document.getElementById('discountAmount');
const discountMessage = document.getElementById('discountMessage');

let appliedDiscount = 0;
let discountCode = '';

function calculateTotal() {
    const start = new Date(startInput.value);
    const end = new Date(endInput.value);
    
    if (startInput.value && endInput.value && end >= start) {
        const diffTime = Math.abs(end - start);
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
        const subtotal = diffDays * pricePerDay;
        const total = subtotal - appliedDiscount;
        
        durationEl.textContent = diffDays + ' hari';
        totalEl.textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(total);
        
        return subtotal;
    } else {
        durationEl.textContent = '0 hari';
        totalEl.textContent = 'Rp 0';
        return 0;
    }
}

async function validateDiscount() {
    const code = discountInput.value.trim().toUpperCase();
    
    if (!code) {
        discountMessage.innerHTML = '<span style="color: #ef4444;">⚠️ Masukkan kode diskon terlebih dahulu</span>';
        return;
    }
    
    const subtotal = calculateTotal();
    if (subtotal <= 0) {
        discountMessage.innerHTML = '<span style="color: #ef4444;">⚠️ Pilih tanggal sewa terlebih dahulu</span>';
        return;
    }
    
    discountMessage.innerHTML = '<span style="color: #3b82f6;"><i class="fas fa-spinner fa-spin"></i> Memvalidasi kode...</span>';
    
    try {
        const formData = new FormData();
        formData.append('code', code);
        formData.append('booking_amount', subtotal);
        
        const response = await fetch('../../php/api/validate-discount.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            appliedDiscount = result.discount_amount;
            discountCode = result.discount_code;
            
            discountRow.style.display = 'flex';
            discountLabel.innerHTML = '<i class="fas fa-tag"></i> Diskon (' + result.discount_code + ')';
            discountAmount.textContent = '-Rp ' + new Intl.NumberFormat('id-ID').format(result.discount_amount);
            discountMessage.innerHTML = '<span style="color: #22c55e;">✅ ' + result.message + ' Hemat Rp ' + new Intl.NumberFormat('id-ID').format(result.discount_amount) + '!</span>';
            
            calculateTotal();
        } else {
            appliedDiscount = 0;
            discountRow.style.display = 'none';
            discountMessage.innerHTML = '<span style="color: #ef4444;">❌ ' + result.message + '</span>';
            calculateTotal();
        }
    } catch (error) {
        discountMessage.innerHTML = '<span style="color: #ef4444;">❌ Terjadi kesalahan saat validasi</span>';
    }
}

startInput.addEventListener('change', function() {
    endInput.min = this.value;
    if (endInput.value && endInput.value < this.value) {
        endInput.value = this.value;
    }
    appliedDiscount = 0;
    discountRow.style.display = 'none';
    discountMessage.innerHTML = '';
    calculateTotal();
});

endInput.addEventListener('change', function() {
    appliedDiscount = 0;
    discountRow.style.display = 'none';
    discountMessage.innerHTML = '';
    calculateTotal();
});

calculateTotal();

// KTP File Preview
document.getElementById('ktp_image').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const preview = document.getElementById('file-preview');
        const fileName = document.getElementById('file-name');
        const fileSize = document.getElementById('file-size');
        
        fileName.textContent = file.name;
        fileSize.textContent = 'Ukuran: ' + (file.size / 1024).toFixed(2) + ' KB';
        preview.style.display = 'block';
        
        // Validate file
        const allowedTypes = ['image/jpeg', 'image/png', 'application/pdf'];
        const maxSize = 5 * 1024 * 1024; // 5MB
        
        let isValid = true;
        if (!allowedTypes.includes(file.type)) {
            fileSize.innerHTML = '<span style="color: #ff4444;">❌ Format file tidak diizinkan. Gunakan JPG, PNG, atau PDF</span>';
            isValid = false;
        } else if (file.size > maxSize) {
            fileSize.innerHTML = '<span style="color: #ff4444;">❌ File terlalu besar. Maksimal 5 MB</span>';
            isValid = false;
        } else {
            fileSize.innerHTML += ' <span style="color: #4ade80;">✓ File valid</span>';
        }
        
        // Only clear file if invalid
        if (!isValid) {
            this.value = '';
        }
    }
});

// KTP Number Format
document.getElementById('ktp_number').addEventListener('input', function(e) {
    this.value = this.value.replace(/\D/g, '').slice(0, 16);
});

// Form Validation
document.getElementById('bookingForm').addEventListener('submit', function(e) {
    const ktpNumber = document.getElementById('ktp_number').value;
    const ktpImageInput = document.getElementById('ktp_image');
    const ktpImageFile = ktpImageInput.files.length > 0 ? ktpImageInput.files[0] : null;
    
    if (!/^\d{16}$/.test(ktpNumber)) {
        e.preventDefault();
        alert('Nomor KTP harus 16 digit angka!');
        document.getElementById('ktp_number').focus();
        return;
    }
    
    if (!ktpImageFile) {
        e.preventDefault();
        alert('Silakan unggah foto KTP terlebih dahulu!');
        document.getElementById('ktp_image').focus();
        return;
    }
    
    // Validate file one more time
    const allowedTypes = ['image/jpeg', 'image/png', 'application/pdf'];
    const maxSize = 5 * 1024 * 1024;
    
    if (!allowedTypes.includes(ktpImageFile.type)) {
        e.preventDefault();
        alert('Format file tidak diizinkan. Gunakan JPG, PNG, atau PDF');
        return;
    }
    
    if (ktpImageFile.size > maxSize) {
        e.preventDefault();
        alert('Ukuran file terlalu besar. Maksimal 5 MB');
        return;
    }
});
</script>

<?php include '../../php/includes/footer.php'; ?>
