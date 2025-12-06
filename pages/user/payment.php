<?php
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

$booking = null;
$error = null;
$success = null;

try {
    $stmt = $pdo->prepare("
        SELECT 
            b.*,
            v.nama as vehicle_name,
            v.merek as vehicle_brand,
            v.model as vehicle_model,
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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $booking) {
    $payment_method = $_POST['payment_method'] ?? '';
    
    if (empty($payment_method)) {
        $error = "Pilih metode pembayaran.";
    } else {
        try {
            // Map payment method to database enum
            $method_map = [
                'bca' => 'transfer',
                'mandiri' => 'transfer',
                'bni' => 'transfer',
                'bri' => 'transfer',
                'gopay' => 'e_wallet',
                'ovo' => 'e_wallet',
                'dana' => 'e_wallet',
                'shopeepay' => 'e_wallet',
                'qris' => 'e_wallet'
            ];
            $db_method = $method_map[$payment_method] ?? 'transfer';
            
            // Redirect to Midtrans payment instead
            header("Location: payment-gateway.php?booking_id=" . $booking_id);
            exit();
        } catch (PDOException $e) {
            $error = "Gagal memproses pembayaran: " . $e->getMessage();
        }
    }
}

if ($booking) {
    $start = new DateTime($booking['start_date']);
    $end = new DateTime($booking['end_date']);
    $duration = $start->diff($end)->days + 1;
}

$page_title = "Pembayaran - EzRent";
include 'header.php';
?>

<style>
/* Override body from header.php */
body {
    background: linear-gradient(180deg, #0a0a0a 0%, #050505 100%) !important;
    color: #fff !important;
}

:root {
    --primary: #c41e3a;
    --primary-dark: #a01830;
    --gold: #d4af37;
    --dark: #0a0a0a;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(30px); }
    to { opacity: 1; transform: translateY(0); }
}

@keyframes shimmer {
    0% { background-position: -200% 0; }
    100% { background-position: 200% 0; }
}

@keyframes pulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.02); }
}

@keyframes float {
    0%, 100% { transform: translateY(0) rotate(0deg); }
    50% { transform: translateY(-15px) rotate(3deg); }
}

.animate-in {
    animation: fadeIn 0.8s ease-out forwards;
    opacity: 0;
}

.delay-1 { animation-delay: 0.1s; }
.delay-2 { animation-delay: 0.2s; }
.delay-3 { animation-delay: 0.3s; }
.delay-4 { animation-delay: 0.4s; }

/* Hero */
.payment-hero {
    background: var(--dark);
    min-height: 220px;
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
        radial-gradient(ellipse at 30% 50%, rgba(196, 30, 58, 0.12) 0%, transparent 50%),
        radial-gradient(ellipse at 70% 50%, rgba(212, 175, 55, 0.08) 0%, transparent 50%);
}

.payment-hero::after {
    content: '';
    position: absolute;
    inset: 0;
    background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.015'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
}

.floating-shape {
    position: absolute;
    opacity: 0.03;
    pointer-events: none;
}

.shape-1 {
    top: 10%;
    right: 15%;
    width: 200px;
    height: 200px;
    border: 1px solid #fff;
    border-radius: 50%;
    animation: float 12s ease-in-out infinite;
}

.shape-2 {
    bottom: -30px;
    left: 10%;
    width: 150px;
    height: 150px;
    border: 1px solid var(--gold);
    transform: rotate(45deg);
    animation: float 15s ease-in-out infinite reverse;
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

.hero-title strong { font-weight: 700; }

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
    grid-template-columns: 1fr 380px;
    gap: 3rem;
}

@media (max-width: 900px) {
    .payment-grid { grid-template-columns: 1fr; }
}

/* Cards */
.card {
    background: linear-gradient(145deg, rgba(20,20,20,0.9) 0%, rgba(10,10,10,0.95) 100%);
    border: 1px solid rgba(255,255,255,0.06);
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
}

.card-title {
    font-size: 0.65rem;
    font-weight: 600;
    letter-spacing: 0.2em;
    text-transform: uppercase;
    color: rgba(255,255,255,0.5);
}

.card-body { padding: 1.75rem; }

/* Payment Methods */
.payment-methods {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.method-option {
    position: relative;
}

.method-option input {
    position: absolute;
    top: 0;
    left: 0;
    opacity: 0;
    width: 100%;
    height: 100%;
    cursor: pointer;
    z-index: 10;
    margin: 0;
}

.method-label {
    display: flex;
    align-items: center;
    gap: 1.25rem;
    padding: 1.25rem 1.5rem;
    background: rgba(0,0,0,0.3);
    border: 1px solid rgba(255,255,255,0.08);
    cursor: pointer;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
}

.method-label::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.05), transparent);
    transition: left 0.5s;
}

.method-option:hover .method-label::before { left: 100%; }
.method-option:hover .method-label { border-color: rgba(255,255,255,0.15); }

.method-option input:checked + .method-label {
    background: rgba(196, 30, 58, 0.1);
    border-color: var(--primary);
    box-shadow: 0 0 30px rgba(196, 30, 58, 0.15);
}

.method-radio {
    width: 22px;
    height: 22px;
    border: 2px solid rgba(255,255,255,0.2);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    transition: all 0.3s;
}

.method-option input:checked + .method-label .method-radio {
    border-color: var(--primary);
}

.method-radio::after {
    content: '';
    width: 10px;
    height: 10px;
    background: var(--primary);
    border-radius: 50%;
    transform: scale(0);
    transition: transform 0.3s;
}

.method-option input:checked + .method-label .method-radio::after {
    transform: scale(1);
}

.method-icon {
    width: 50px;
    height: 35px;
    background: rgba(255,255,255,0.05);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.65rem;
    font-weight: 700;
    letter-spacing: 0.05em;
    color: rgba(255,255,255,0.7);
    border: 1px solid rgba(255,255,255,0.1);
}

.method-info { flex: 1; }

.method-name {
    font-size: 0.95rem;
    font-weight: 500;
    margin-bottom: 0.25rem;
}

.method-desc {
    font-size: 0.8rem;
    color: rgba(255,255,255,0.4);
}

.method-badge {
    padding: 0.35rem 0.75rem;
    font-size: 0.6rem;
    font-weight: 600;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    background: rgba(0, 204, 102, 0.15);
    color: #00cc66;
    border: 1px solid rgba(0, 204, 102, 0.3);
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
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    opacity: 0.3;
    flex-shrink: 0;
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

/* Submit Button */
.submit-btn {
    width: 100%;
    padding: 1.25rem;
    margin-top: 1.5rem;
    font-size: 0.75rem;
    font-weight: 600;
    letter-spacing: 0.2em;
    text-transform: uppercase;
    border: none;
    cursor: pointer;
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
    color: #fff;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
}

.submit-btn::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.15), transparent);
    transition: left 0.6s;
}

.submit-btn:hover::before { left: 100%; }

.submit-btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 15px 40px rgba(196, 30, 58, 0.35);
}

.submit-btn:disabled {
    background: rgba(255,255,255,0.1);
    color: rgba(255,255,255,0.3);
    cursor: not-allowed;
    transform: none;
    box-shadow: none;
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
}

.security-icon {
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #00cc66;
    font-size: 0.9rem;
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
}

.back-btn:hover {
    background: rgba(255,255,255,0.05);
    border-color: rgba(255,255,255,0.4);
}

/* Alert */
.alert {
    padding: 1rem 1.5rem;
    margin-bottom: 2rem;
    font-size: 0.85rem;
}

.alert-error {
    background: rgba(255, 77, 77, 0.1);
    border: 1px solid rgba(255, 77, 77, 0.3);
    color: #ff6b6b;
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
    <div class="floating-shape shape-1"></div>
    <div class="floating-shape shape-2"></div>
    
    <div class="hero-container">
        <nav class="breadcrumb animate-in">
            <a href="my-bookings.php">Pesanan</a>
            <span class="sep">/</span>
            <a href="booking-detail.php?id=<?php echo $booking_id; ?>">Detail</a>
            <span class="sep">/</span>
            <span class="current">Pembayaran</span>
        </nav>
        <h1 class="hero-title animate-in delay-1">Selesaikan <strong>Pembayaran</strong></h1>
    </div>
</section>

<section class="payment-content">
    <div class="content-container">
        <?php if ($error): ?>
        <div class="alert alert-error animate-in"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <form method="POST" id="paymentForm">
            <div class="payment-grid">
                <div class="payment-main">
                    <!-- Payment Methods -->
                    <div class="card animate-in delay-1">
                        <div class="card-header">
                            <span class="card-title">Pilih Metode Pembayaran</span>
                        </div>
                        <div class="card-body">
                            <div class="payment-methods">
                                <!-- Bank Transfer -->
                                <div class="method-option">
                                    <input type="radio" name="payment_method" value="bca" id="bca">
                                    <label for="bca" class="method-label">
                                        <span class="method-radio"></span>
                                        <span class="method-icon">BCA</span>
                                        <div class="method-info">
                                            <div class="method-name">Bank BCA</div>
                                            <div class="method-desc">Transfer ke rekening BCA</div>
                                        </div>
                                        <span class="method-badge">Populer</span>
                                    </label>
                                </div>
                                
                                <div class="method-option">
                                    <input type="radio" name="payment_method" value="mandiri" id="mandiri">
                                    <label for="mandiri" class="method-label">
                                        <span class="method-radio"></span>
                                        <span class="method-icon">MDR</span>
                                        <div class="method-info">
                                            <div class="method-name">Bank Mandiri</div>
                                            <div class="method-desc">Transfer ke rekening Mandiri</div>
                                        </div>
                                    </label>
                                </div>
                                
                                <div class="method-option">
                                    <input type="radio" name="payment_method" value="bni" id="bni">
                                    <label for="bni" class="method-label">
                                        <span class="method-radio"></span>
                                        <span class="method-icon">BNI</span>
                                        <div class="method-info">
                                            <div class="method-name">Bank BNI</div>
                                            <div class="method-desc">Transfer ke rekening BNI</div>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- E-Wallet -->
                    <div class="card animate-in delay-2">
                        <div class="card-header">
                            <span class="card-title">E-Wallet</span>
                        </div>
                        <div class="card-body">
                            <div class="payment-methods">
                                <div class="method-option">
                                    <input type="radio" name="payment_method" value="gopay" id="gopay">
                                    <label for="gopay" class="method-label">
                                        <span class="method-radio"></span>
                                        <span class="method-icon">GP</span>
                                        <div class="method-info">
                                            <div class="method-name">GoPay</div>
                                            <div class="method-desc">Bayar dengan saldo GoPay</div>
                                        </div>
                                    </label>
                                </div>
                                
                                <div class="method-option">
                                    <input type="radio" name="payment_method" value="ovo" id="ovo">
                                    <label for="ovo" class="method-label">
                                        <span class="method-radio"></span>
                                        <span class="method-icon">OVO</span>
                                        <div class="method-info">
                                            <div class="method-name">OVO</div>
                                            <div class="method-desc">Bayar dengan saldo OVO</div>
                                        </div>
                                    </label>
                                </div>
                                
                                <div class="method-option">
                                    <input type="radio" name="payment_method" value="dana" id="dana">
                                    <label for="dana" class="method-label">
                                        <span class="method-radio"></span>
                                        <span class="method-icon">DNA</span>
                                        <div class="method-info">
                                            <div class="method-name">DANA</div>
                                            <div class="method-desc">Bayar dengan saldo DANA</div>
                                        </div>
                                    </label>
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
                                <div class="vehicle-thumb">▣</div>
                                <div class="vehicle-mini-info">
                                    <h4><?php echo htmlspecialchars($booking['vehicle_name']); ?></h4>
                                    <p><?php echo htmlspecialchars($booking['vehicle_brand'] . ' ' . $booking['vehicle_model']); ?></p>
                                </div>
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
                            
                            <div class="summary-row">
                                <span class="label">Subtotal</span>
                                <span class="value">Rp <?php echo number_format($booking['total_price'], 0, ',', '.'); ?></span>
                            </div>
                            <div class="summary-row">
                                <span class="label">Biaya Layanan</span>
                                <span class="value">Rp 0</span>
                            </div>
                            
                            <div class="summary-total">
                                <span class="label">Total Bayar</span>
                                <span class="value">Rp <?php echo number_format($booking['total_price'], 0, ',', '.'); ?></span>
                            </div>
                            
                            <button type="submit" class="submit-btn" id="submitBtn" disabled>
                                Bayar Sekarang
                            </button>
                            
                            <div class="security-badge">
                                <span class="security-icon">&#128274;</span>
                                <span class="security-text"><strong>Transaksi Aman</strong> - Data Anda terlindungi</span>
                            </div>
                            
                            <a href="booking-detail.php?id=<?php echo $booking_id; ?>" class="cancel-link">Batalkan & Kembali</a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const radios = document.querySelectorAll('input[name="payment_method"]');
    const submitBtn = document.getElementById('submitBtn');
    
    radios.forEach(radio => {
        radio.addEventListener('change', function() {
            submitBtn.disabled = false;
        });
    });
    
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
