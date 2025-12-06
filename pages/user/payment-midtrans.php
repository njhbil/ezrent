<?php
/**
 * Halaman Pembayaran EzRent
 * Menggunakan Midtrans Snap untuk pembayaran (VA, QRIS, E-Wallet, Kartu Kredit)
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: ../login.php");
    exit();
}

$booking_id = isset($_GET['booking_id']) ? (int)$_GET['booking_id'] : (isset($_GET['id']) ? (int)$_GET['id'] : 0);

if ($booking_id <= 0) {
    header("Location: my-bookings.php");
    exit();
}

require_once '../../php/config/database.php';
require_once '../../php/config/midtrans.php';

$booking = null;
$error = null;

try {
    $stmt = $pdo->prepare("
        SELECT 
            b.*,
            v.nama as vehicle_name,
            v.merek as vehicle_brand,
            v.model as vehicle_model,
            v.jenis as vehicle_type,
            v.harga_per_hari as daily_rate,
            v.images as vehicle_image
        FROM bookings b
        JOIN vehicles v ON b.vehicle_id = v.id
        WHERE b.id = ? AND b.user_id = ? AND b.status = 'pending'
    ");
    $stmt->execute([$booking_id, $_SESSION['user_id']]);
    $booking = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$booking) {
        $error = "Pesanan tidak ditemukan atau sudah dibayar.";
    }
} catch (PDOException $e) {
    $error = "Terjadi kesalahan.";
}

if ($booking) {
    $start = new DateTime($booking['start_date']);
    $end = new DateTime($booking['end_date']);
    $duration = $start->diff($end)->days + 1;
}

$page_title = "Pembayaran - EzRent";
include 'header.php';

// Get Midtrans client key for Snap
$midtrans_client_key = getMidtransClientKey();
$midtrans_snap_url = getMidtransSnapUrl();
?>

<style>
body {
    background: linear-gradient(180deg, #0a0a0a 0%, #050505 100%) !important;
    color: #fff !important;
}

:root {
    --primary: #d50000;
    --primary-dark: #b50000;
    --gold: #d4af37;
    --dark: #0a0a0a;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(30px); }
    to { opacity: 1; transform: translateY(0); }
}

@keyframes pulse {
    0%, 100% { transform: scale(1); opacity: 1; }
    50% { transform: scale(1.05); opacity: 0.8; }
}

@keyframes shimmer {
    0% { background-position: -200% 0; }
    100% { background-position: 200% 0; }
}

.animate-in {
    animation: fadeIn 0.8s ease-out forwards;
    opacity: 0;
}

.delay-1 { animation-delay: 0.1s; }
.delay-2 { animation-delay: 0.2s; }
.delay-3 { animation-delay: 0.3s; }
.delay-4 { animation-delay: 0.4s; }

/* Hero Section */
.payment-hero {
    background: var(--dark);
    min-height: 200px;
    padding-top: 80px;
    position: relative;
    overflow: hidden;
    display: flex;
    align-items: center;
}

.payment-hero::before {
    content: '';
    position: absolute;
    inset: 0;
    background: 
        radial-gradient(ellipse at 30% 50%, rgba(213, 0, 0, 0.12) 0%, transparent 50%),
        radial-gradient(ellipse at 70% 50%, rgba(212, 175, 55, 0.08) 0%, transparent 50%);
}

.payment-hero::after {
    content: '';
    position: absolute;
    inset: 0;
    background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.015'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
}

/* Particle Canvas */
.particle-canvas {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 1;
    pointer-events: none;
}

.hero-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 2.5rem 20px;
    position: relative;
    z-index: 2;
}

.breadcrumb {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 1.5rem;
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.15em;
}

.breadcrumb a {
    color: rgba(255,255,255,0.4);
    text-decoration: none;
    transition: color 0.3s;
}

.breadcrumb a:hover { color: var(--primary); }
.breadcrumb .sep { color: rgba(255,255,255,0.2); }
.breadcrumb .current { color: rgba(255,255,255,0.8); }

.hero-title {
    font-size: 2.5rem;
    font-weight: 200;
    letter-spacing: -0.02em;
}

.hero-title strong { font-weight: 700; color: var(--primary); }

/* Midtrans Badge */
.midtrans-powered {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    background: rgba(0, 150, 200, 0.1);
    border: 1px solid rgba(0, 150, 200, 0.3);
    padding: 0.5rem 1rem;
    border-radius: 6px;
    font-size: 0.75rem;
    color: #00c8ff;
    margin-top: 1rem;
}

/* Content */
.payment-content {
    background: linear-gradient(180deg, #0f0f0f 0%, #080808 100%);
    min-height: 60vh;
    padding: 4rem 20px;
    position: relative;
}

.payment-content::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 1px;
    background: linear-gradient(90deg, transparent, var(--primary), transparent);
}

.content-container {
    max-width: 1100px;
    margin: 0 auto;
}

.payment-grid {
    display: grid;
    grid-template-columns: 1fr 400px;
    gap: 3rem;
}

@media (max-width: 900px) {
    .payment-grid { grid-template-columns: 1fr; }
}

/* Cards */
.card {
    background: linear-gradient(145deg, rgba(20,20,20,0.9) 0%, rgba(10,10,10,0.95) 100%);
    border: 1px solid rgba(255,255,255,0.06);
    border-radius: 12px;
    margin-bottom: 2rem;
    position: relative;
    overflow: hidden;
}

.card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 1px;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.1), transparent);
}

.card-header {
    padding: 1.25rem 1.75rem;
    border-bottom: 1px solid rgba(255,255,255,0.06);
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.card-title {
    font-size: 0.7rem;
    font-weight: 600;
    letter-spacing: 0.2em;
    text-transform: uppercase;
    color: rgba(255,255,255,0.5);
}

.card-body { padding: 1.75rem; }

/* Payment Methods Preview */
.payment-methods-preview {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1rem;
    margin-bottom: 1.5rem;
}

@media (max-width: 600px) {
    .payment-methods-preview {
        grid-template-columns: repeat(2, 1fr);
    }
}

.method-item {
    background: rgba(255,255,255,0.03);
    border: 1px solid rgba(255,255,255,0.08);
    border-radius: 8px;
    padding: 1rem;
    text-align: center;
    transition: all 0.3s;
}

.method-item:hover {
    border-color: var(--primary);
    background: rgba(213, 0, 0, 0.05);
}

.method-icon {
    width: 48px;
    height: 32px;
    background: rgba(255,255,255,0.1);
    border-radius: 4px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 0.5rem;
    font-size: 0.6rem;
    font-weight: 700;
    color: rgba(255,255,255,0.7);
}

.method-name {
    font-size: 0.75rem;
    color: rgba(255,255,255,0.6);
}

/* Features Grid */
.features-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1rem;
}

.feature-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 1rem;
    background: rgba(255,255,255,0.02);
    border-radius: 8px;
}

.feature-icon {
    width: 40px;
    height: 40px;
    background: rgba(213, 0, 0, 0.1);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--primary);
    font-size: 1.2rem;
}

.feature-text {
    flex: 1;
}

.feature-text h4 {
    font-size: 0.85rem;
    font-weight: 600;
    margin-bottom: 0.25rem;
}

.feature-text p {
    font-size: 0.75rem;
    color: rgba(255,255,255,0.5);
    margin: 0;
}

/* Order Summary Sidebar */
.summary-card {
    position: sticky;
    top: 100px;
}

.vehicle-mini {
    display: flex;
    gap: 1rem;
    padding-bottom: 1.5rem;
    border-bottom: 1px solid rgba(255,255,255,0.06);
    margin-bottom: 1.5rem;
}

.vehicle-thumb {
    width: 80px;
    height: 60px;
    background: linear-gradient(135deg, #1a1a1a 0%, #0a0a0a 100%);
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    color: var(--primary);
}

.vehicle-mini-info h4 {
    font-size: 0.95rem;
    font-weight: 500;
    margin-bottom: 0.25rem;
}

.vehicle-mini-info p {
    font-size: 0.8rem;
    color: rgba(255,255,255,0.4);
}

.summary-row {
    display: flex;
    justify-content: space-between;
    padding: 0.75rem 0;
    font-size: 0.9rem;
}

.summary-row .label { color: rgba(255,255,255,0.5); }
.summary-row .value { font-weight: 500; }

.summary-divider {
    height: 1px;
    background: rgba(255,255,255,0.06);
    margin: 0.75rem 0;
}

.summary-total {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-top: 1rem;
    border-top: 2px solid var(--primary);
    margin-top: 1rem;
}

.summary-total .label {
    font-size: 1rem;
    font-weight: 500;
}

.summary-total .value {
    font-size: 1.75rem;
    font-weight: 700;
    color: var(--primary);
}

/* Pay Button */
.pay-btn {
    width: 100%;
    padding: 1.25rem;
    margin-top: 1.5rem;
    font-size: 0.8rem;
    font-weight: 600;
    letter-spacing: 0.15em;
    text-transform: uppercase;
    border: none;
    cursor: pointer;
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
    color: #fff;
    border-radius: 8px;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.75rem;
}

.pay-btn::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.15), transparent);
    transition: left 0.6s;
}

.pay-btn:hover::before { left: 100%; }

.pay-btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 15px 40px rgba(213, 0, 0, 0.35);
}

.pay-btn:disabled {
    background: rgba(255,255,255,0.1);
    color: rgba(255,255,255,0.3);
    cursor: not-allowed;
    transform: none;
    box-shadow: none;
}

.pay-btn.loading {
    pointer-events: none;
}

.pay-btn.loading .btn-text { display: none; }
.pay-btn .btn-loading { display: none; }
.pay-btn.loading .btn-loading {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.spinner {
    width: 18px;
    height: 18px;
    border: 2px solid rgba(255,255,255,0.3);
    border-top-color: #fff;
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

/* Security Badge */
.security-badge {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.75rem;
    margin-top: 1.25rem;
    padding: 1rem;
    background: rgba(0, 204, 102, 0.05);
    border: 1px solid rgba(0, 204, 102, 0.15);
    border-radius: 8px;
}

.security-icon {
    color: #00cc66;
    font-size: 1.2rem;
}

.security-text {
    font-size: 0.75rem;
    color: rgba(255,255,255,0.5);
}

.security-text strong {
    color: #00cc66;
    font-weight: 600;
}

/* Cancel Link */
.cancel-link {
    display: block;
    text-align: center;
    margin-top: 1.25rem;
    color: rgba(255,255,255,0.4);
    text-decoration: none;
    font-size: 0.8rem;
    transition: color 0.3s;
}

.cancel-link:hover { color: var(--primary); }

/* Error Container */
.error-container {
    max-width: 500px;
    margin: 0 auto;
    text-align: center;
    padding: 5rem 2rem;
}

.error-icon {
    width: 80px;
    height: 80px;
    border: 2px solid rgba(255,255,255,0.1);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 2rem;
    font-size: 2rem;
    color: rgba(255,255,255,0.3);
}

.error-title {
    font-size: 1.5rem;
    font-weight: 300;
    margin-bottom: 0.75rem;
}

.error-text {
    color: rgba(255,255,255,0.5);
    margin-bottom: 2rem;
}

.back-btn {
    display: inline-block;
    padding: 1rem 2rem;
    background: transparent;
    border: 1px solid rgba(255,255,255,0.2);
    color: #fff;
    text-decoration: none;
    font-size: 0.75rem;
    font-weight: 600;
    letter-spacing: 0.15em;
    text-transform: uppercase;
    transition: all 0.3s;
    border-radius: 8px;
}

.back-btn:hover {
    background: rgba(255,255,255,0.05);
    border-color: rgba(255,255,255,0.4);
}

/* Notification */
.notification {
    position: fixed;
    top: 20px;
    right: 20px;
    padding: 1rem 1.5rem;
    border-radius: 8px;
    font-size: 0.9rem;
    z-index: 9999;
    transform: translateX(120%);
    transition: transform 0.3s ease;
}

.notification.show {
    transform: translateX(0);
}

.notification.success {
    background: rgba(0, 204, 102, 0.9);
    color: #fff;
}

.notification.error {
    background: rgba(255, 77, 77, 0.9);
    color: #fff;
}

/* Responsive */
@media (max-width: 768px) {
    .hero-title {
        font-size: 1.75rem;
    }
    
    .payment-methods-preview {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .features-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<?php if ($error && !$booking): ?>
<section class="payment-content">
    <div class="error-container animate-in">
        <div class="error-icon">!</div>
        <h2 class="error-title">Tidak Dapat Memproses</h2>
        <p class="error-text"><?php echo htmlspecialchars($error); ?></p>
        <a href="my-bookings.php" class="back-btn">Kembali ke Pesanan</a>
    </div>
</section>
<?php else: ?>

<section class="payment-hero">
    <canvas id="particleCanvas" class="particle-canvas"></canvas>
    
    <div class="hero-container">
        <nav class="breadcrumb animate-in">
            <a href="my-bookings.php">Pesanan</a>
            <span class="sep">/</span>
            <a href="booking-detail.php?id=<?php echo $booking_id; ?>">Detail</a>
            <span class="sep">/</span>
            <span class="current">Pembayaran</span>
        </nav>
        <h1 class="hero-title animate-in delay-1">Selesaikan <strong>Pembayaran</strong></h1>
        <div class="midtrans-powered animate-in delay-2">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
            Powered by Midtrans - Pembayaran Aman & Terpercaya
        </div>
    </div>
</section>

<section class="payment-content">
    <div class="content-container">
        <div class="payment-grid">
            <div class="payment-main">
                <!-- Available Payment Methods -->
                <div class="card animate-in delay-1">
                    <div class="card-header">
                        <span class="card-title">Metode Pembayaran Tersedia</span>
                    </div>
                    <div class="card-body">
                        <div class="payment-methods-preview">
                            <div class="method-item">
                                <div class="method-icon">BCA</div>
                                <div class="method-name">Virtual Account</div>
                            </div>
                            <div class="method-item">
                                <div class="method-icon">BNI</div>
                                <div class="method-name">Virtual Account</div>
                            </div>
                            <div class="method-item">
                                <div class="method-icon">BRI</div>
                                <div class="method-name">Virtual Account</div>
                            </div>
                            <div class="method-item">
                                <div class="method-icon">MDR</div>
                                <div class="method-name">Virtual Account</div>
                            </div>
                            <div class="method-item">
                                <div class="method-icon">
                                    <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M7 7h10v2H7zM7 11h10v2H7zM7 15h6v2H7z" fill="#0a0a0a"/></svg>
                                </div>
                                <div class="method-name">QRIS</div>
                            </div>
                            <div class="method-item">
                                <div class="method-icon">GP</div>
                                <div class="method-name">GoPay</div>
                            </div>
                            <div class="method-item">
                                <div class="method-icon">SP</div>
                                <div class="method-name">ShopeePay</div>
                            </div>
                            <div class="method-item">
                                <div class="method-icon">CC</div>
                                <div class="method-name">Kartu Kredit</div>
                            </div>
                        </div>
                        <p style="font-size: 0.85rem; color: rgba(255,255,255,0.5); text-align: center; margin: 0;">
                            Klik tombol "Bayar Sekarang" untuk memilih metode pembayaran
                        </p>
                    </div>
                </div>
                
                <!-- Payment Features -->
                <div class="card animate-in delay-2">
                    <div class="card-header">
                        <span class="card-title">Keamanan Pembayaran</span>
                    </div>
                    <div class="card-body">
                        <div class="features-grid">
                            <div class="feature-item">
                                <div class="feature-icon">🔒</div>
                                <div class="feature-text">
                                    <h4>Enkripsi SSL</h4>
                                    <p>Data dienkripsi 256-bit</p>
                                </div>
                            </div>
                            <div class="feature-item">
                                <div class="feature-icon">✓</div>
                                <div class="feature-text">
                                    <h4>PCI DSS Certified</h4>
                                    <p>Standar keamanan internasional</p>
                                </div>
                            </div>
                            <div class="feature-item">
                                <div class="feature-icon">🛡️</div>
                                <div class="feature-text">
                                    <h4>Fraud Detection</h4>
                                    <p>Perlindungan dari penipuan</p>
                                </div>
                            </div>
                            <div class="feature-item">
                                <div class="feature-icon">📱</div>
                                <div class="feature-text">
                                    <h4>Real-time</h4>
                                    <p>Konfirmasi pembayaran instan</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="payment-sidebar">
                <div class="card summary-card animate-in delay-2">
                    <div class="card-header">
                        <span class="card-title">Ringkasan Pesanan</span>
                    </div>
                    <div class="card-body">
                        <div class="vehicle-mini">
                            <div class="vehicle-thumb">
                                <?php
                                $icon = '<svg width="32" height="32" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M5 11l1.5-4.5A2 2 0 0 1 8.4 5h7.2a2 2 0 0 1 1.9 1.5L19 11M3 17h18v-4a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v4z"/><circle cx="7" cy="17" r="2"/><circle cx="17" cy="17" r="2"/></svg>';
                                if ($booking['vehicle_type'] === 'motor') {
                                    $icon = '<svg width="32" height="32" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><circle cx="5.5" cy="17.5" r="3.5"/><circle cx="18.5" cy="17.5" r="3.5"/><path d="M15 6a1 1 0 100-2 1 1 0 000 2zm-3 11.5V14l-3-3 4-3 2 3h2"/></svg>';
                                }
                                echo $icon;
                                ?>
                            </div>
                            <div class="vehicle-mini-info">
                                <h4><?php echo htmlspecialchars($booking['vehicle_name']); ?></h4>
                                <p><?php echo htmlspecialchars($booking['vehicle_brand'] . ' ' . $booking['vehicle_model']); ?></p>
                            </div>
                        </div>
                        
                        <div class="summary-row">
                            <span class="label">Kode Booking</span>
                            <span class="value"><?php echo htmlspecialchars($booking['kode_booking']); ?></span>
                        </div>
                        <div class="summary-row">
                            <span class="label">Tanggal Sewa</span>
                            <span class="value"><?php echo $start->format('d M'); ?> - <?php echo $end->format('d M Y'); ?></span>
                        </div>
                        <div class="summary-row">
                            <span class="label">Durasi</span>
                            <span class="value"><?php echo $duration; ?> Hari</span>
                        </div>
                        <div class="summary-row">
                            <span class="label">Tarif per Hari</span>
                            <span class="value">Rp <?php echo number_format($booking['daily_rate'] ?? ($booking['total_price'] / $duration), 0, ',', '.'); ?></span>
                        </div>
                        
                        <div class="summary-divider"></div>
                        
                        <div class="summary-total">
                            <span class="label">Total Bayar</span>
                            <span class="value">Rp <?php echo number_format($booking['total_price'], 0, ',', '.'); ?></span>
                        </div>
                        
                        <button type="button" class="pay-btn" id="payButton" data-booking-id="<?php echo $booking_id; ?>">
                            <span class="btn-text">
                                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
                                Bayar Sekarang
                            </span>
                            <span class="btn-loading">
                                <span class="spinner"></span>
                                Memproses...
                            </span>
                        </button>
                        
                        <div class="security-badge">
                            <span class="security-icon">🔒</span>
                            <span class="security-text"><strong>Transaksi Aman</strong> - Dilindungi Midtrans</span>
                        </div>
                        
                        <a href="booking-detail.php?id=<?php echo $booking_id; ?>" class="cancel-link">Batalkan & Kembali</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Notification -->
<div id="notification" class="notification"></div>

<!-- Midtrans Snap JS -->
<script src="<?php echo $midtrans_snap_url; ?>" data-client-key="<?php echo $midtrans_client_key; ?>"></script>

<script>
// Copy test card number
function copyTestCard(cardNumber) {
    navigator.clipboard.writeText(cardNumber).then(() => {
        const notification = document.getElementById('notification');
        notification.textContent = '✅ Card number copied: ' + cardNumber;
        notification.className = 'notification success show';
        setTimeout(() => {
            notification.classList.remove('show');
        }, 3000);
    });
}

document.addEventListener('DOMContentLoaded', function() {
    const payButton = document.getElementById('payButton');
    const notification = document.getElementById('notification');
    
    function showNotification(message, type = 'success') {
        notification.textContent = message;
        notification.className = 'notification ' + type + ' show';
        setTimeout(() => {
            notification.classList.remove('show');
        }, 5000);
    }
    
    payButton.addEventListener('click', async function() {
        const bookingId = this.dataset.bookingId;
        
        // Show loading state
        this.classList.add('loading');
        
        try {
            // Get Snap token from server
            const response = await fetch('../../php/api/midtrans-token.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ booking_id: parseInt(bookingId) })
            });
            
            const data = await response.json();
            
            if (!response.ok) {
                throw new Error(data.error || 'Failed to get payment token');
            }
            
            // Open Midtrans Snap popup
            window.snap.pay(data.snap_token, {
                onSuccess: function(result) {
                    showNotification('Pembayaran berhasil!', 'success');
                    setTimeout(() => {
                        window.location.href = 'booking-detail.php?id=' + bookingId + '&payment=success';
                    }, 1500);
                },
                onPending: function(result) {
                    showNotification('Menunggu pembayaran...', 'success');
                    setTimeout(() => {
                        window.location.href = 'booking-detail.php?id=' + bookingId + '&payment=pending';
                    }, 1500);
                },
                onError: function(result) {
                    showNotification('Pembayaran gagal. Silakan coba lagi.', 'error');
                    payButton.classList.remove('loading');
                },
                onClose: function() {
                    payButton.classList.remove('loading');
                }
            });
            
        } catch (error) {
            console.error('Payment error:', error);
            showNotification(error.message || 'Terjadi kesalahan. Silakan coba lagi.', 'error');
            payButton.classList.remove('loading');
        }
    });
    
    // Particle Animation
    const canvas = document.getElementById('particleCanvas');
    if (canvas) {
        const ctx = canvas.getContext('2d');
        let particles = [];
        const particleCount = 50;
        
        function resizeCanvas() {
            const hero = document.querySelector('.payment-hero');
            canvas.width = hero.offsetWidth;
            canvas.height = hero.offsetHeight;
        }
        
        function createParticles() {
            particles = [];
            for (let i = 0; i < particleCount; i++) {
                particles.push({
                    x: Math.random() * canvas.width,
                    y: Math.random() * canvas.height,
                    radius: Math.random() * 2 + 1,
                    vx: (Math.random() - 0.5) * 0.3,
                    vy: (Math.random() - 0.5) * 0.3,
                    alpha: Math.random() * 0.4 + 0.1
                });
            }
        }
        
        function drawParticles() {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            
            particles.forEach((p, i) => {
                p.x += p.vx;
                p.y += p.vy;
                
                if (p.x < 0) p.x = canvas.width;
                if (p.x > canvas.width) p.x = 0;
                if (p.y < 0) p.y = canvas.height;
                if (p.y > canvas.height) p.y = 0;
                
                ctx.beginPath();
                ctx.arc(p.x, p.y, p.radius, 0, Math.PI * 2);
                ctx.fillStyle = `rgba(213, 0, 0, ${p.alpha})`;
                ctx.fill();
                
                particles.forEach((p2, j) => {
                    if (i !== j) {
                        const dx = p.x - p2.x;
                        const dy = p.y - p2.y;
                        const dist = Math.sqrt(dx * dx + dy * dy);
                        
                        if (dist < 100) {
                            ctx.beginPath();
                            ctx.moveTo(p.x, p.y);
                            ctx.lineTo(p2.x, p2.y);
                            ctx.strokeStyle = `rgba(213, 0, 0, ${0.08 * (1 - dist / 100)})`;
                            ctx.lineWidth = 0.5;
                            ctx.stroke();
                        }
                    }
                });
            });
            
            requestAnimationFrame(drawParticles);
        }
        
        resizeCanvas();
        createParticles();
        drawParticles();
        
        window.addEventListener('resize', () => {
            resizeCanvas();
            createParticles();
        });
    }
    
    // Animation observer
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.animationPlayState = 'running';
            }
        });
    }, { threshold: 0.1 });
    
    document.querySelectorAll('.animate-in').forEach(el => {
        el.style.animationPlayState = 'paused';
        observer.observe(el);
    });
});
</script>

<?php endif; ?>

<?php include '../../php/includes/footer.php'; ?>
