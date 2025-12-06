<?php
/**
 * Payment Instructions - Real Midtrans VA & QRIS Display
 * Shows actual payment codes from Midtrans API
 */
session_start();
require_once '../../php/config/database.php';
require_once '../../php/includes/MidtransAPI.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$booking_id = isset($_GET['booking_id']) ? (int)$_GET['booking_id'] : 0;

if ($booking_id <= 0) {
    header("Location: my-bookings.php");
    exit();
}

// Get payment data from session or database
$payment_data = $_SESSION['payment_data'] ?? null;

// Get booking and payment details from database
try {
    $stmt = $pdo->prepare("
        SELECT b.*, v.nama as vehicle_name, v.merek, v.harga_per_hari,
               p.payment_method, p.va_number, p.biller_code, p.bill_key, 
               p.qris_url, p.amount, p.status as payment_status,
               p.midtrans_order_id, p.expiry_time
        FROM bookings b
        JOIN vehicles v ON b.vehicle_id = v.id
        LEFT JOIN payments p ON b.id = p.booking_id
        WHERE b.id = ? AND b.user_id = ?
    ");
    $stmt->execute([$booking_id, $_SESSION['user_id']]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$data) {
        header("Location: my-bookings.php");
        exit();
    }
} catch (PDOException $e) {
    header("Location: my-bookings.php");
    exit();
}

// Use session data if available, otherwise use database
$payment_method = $payment_data['method'] ?? $data['payment_method'];
$va_number = $payment_data['va_number'] ?? $data['va_number'];
$biller_code = $payment_data['biller_code'] ?? $data['biller_code'];
$bill_key = $payment_data['bill_key'] ?? $data['bill_key'];
$qris_url = $payment_data['qris_url'] ?? $data['qris_url'];
$amount = $payment_data['amount'] ?? $data['amount'];
$expiry_time = $payment_data['expiry_time'] ?? $data['expiry_time'];
$order_id = $payment_data['order_id'] ?? $data['midtrans_order_id'];

// Calculate duration
$start = new DateTime($data['start_date']);
$end = new DateTime($data['end_date']);
$duration = $start->diff($end)->days + 1;

// Bank info
$banks = [
    'bca' => ['name' => 'BCA', 'color' => '#1f4788', 'bg' => '#eef4ff'],
    'bni' => ['name' => 'BNI', 'color' => '#ff6600', 'bg' => '#fff8f0'],
    'bri' => ['name' => 'BRI', 'color' => '#0066cc', 'bg' => '#f0f7ff'],
    'mandiri' => ['name' => 'Mandiri', 'color' => '#003366', 'bg' => '#f0f5fa'],
    'qris' => ['name' => 'QRIS', 'color' => '#00aa5b', 'bg' => '#f0fff5']
];

$bank_info = $banks[$payment_method] ?? ['name' => 'Bank', 'color' => '#333', 'bg' => '#f5f5f5'];

$page_title = "Instruksi Pembayaran - EzRent";
include 'header.php';
?>

<style>
body {
    background: linear-gradient(180deg, #0a0a0a 0%, #111 100%) !important;
    color: #fff !important;
}

.payment-page {
    min-height: 100vh;
    padding: 80px 20px 40px;
}

.payment-container {
    max-width: 600px;
    margin: 0 auto;
}

.payment-header {
    text-align: center;
    margin-bottom: 1.5rem;
}

.payment-header h1 {
    font-size: 1.5rem;
    margin-bottom: 0.5rem;
}

.payment-header p {
    color: rgba(255,255,255,0.6);
    font-size: 0.9rem;
}

.status-badge {
    display: inline-block;
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 600;
    margin-bottom: 1rem;
}

.status-pending {
    background: rgba(255,193,7,0.15);
    color: #ffc107;
    border: 1px solid rgba(255,193,7,0.3);
}

.status-success {
    background: rgba(76,175,80,0.15);
    color: #4caf50;
    border: 1px solid rgba(76,175,80,0.3);
}

.payment-card {
    background: rgba(255,255,255,0.03);
    border: 1px solid rgba(255,255,255,0.1);
    border-radius: 16px;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
}

.bank-header {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid rgba(255,255,255,0.1);
}

.bank-logo {
    width: 50px;
    height: 50px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 0.8rem;
    color: #fff;
}

.bank-name {
    font-size: 1.1rem;
    font-weight: 600;
}

.bank-type {
    font-size: 0.85rem;
    color: rgba(255,255,255,0.5);
}

.va-section {
    background: rgba(255,255,255,0.05);
    border-radius: 12px;
    padding: 1.25rem;
    margin-bottom: 1.25rem;
    text-align: center;
}

.va-label {
    font-size: 0.8rem;
    color: rgba(255,255,255,0.5);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 0.5rem;
}

.va-number {
    font-size: 1.75rem;
    font-weight: 700;
    font-family: 'Courier New', monospace;
    letter-spacing: 2px;
    margin-bottom: 0.75rem;
    color: #fff;
    word-break: break-all;
}

.mandiri-codes {
    display: flex;
    gap: 1rem;
    justify-content: center;
    flex-wrap: wrap;
}

.mandiri-code {
    background: rgba(255,255,255,0.05);
    padding: 0.75rem 1rem;
    border-radius: 8px;
    text-align: center;
}

.mandiri-code label {
    font-size: 0.7rem;
    color: rgba(255,255,255,0.5);
    text-transform: uppercase;
    display: block;
    margin-bottom: 0.25rem;
}

.mandiri-code span {
    font-size: 1.25rem;
    font-weight: 700;
    font-family: 'Courier New', monospace;
}

.copy-btn {
    background: linear-gradient(135deg, #d50000, #ff4444);
    border: none;
    color: #fff;
    padding: 0.625rem 1.25rem;
    border-radius: 6px;
    font-size: 0.9rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.25s;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}

.copy-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(213,0,0,0.3);
}

.copy-btn.copied {
    background: #00c853;
}

.amount-section {
    background: linear-gradient(135deg, rgba(213,0,0,0.1), rgba(255,68,68,0.1));
    border: 1px solid rgba(213,0,0,0.2);
    border-radius: 12px;
    padding: 1rem;
    text-align: center;
    margin-bottom: 1.25rem;
}

.amount-label {
    font-size: 0.8rem;
    color: rgba(255,255,255,0.6);
    margin-bottom: 0.25rem;
}

.amount-value {
    font-size: 1.5rem;
    font-weight: 700;
    color: #ff4444;
}

.expiry-section {
    background: rgba(255,193,7,0.1);
    border: 1px solid rgba(255,193,7,0.2);
    border-radius: 8px;
    padding: 0.875rem;
    text-align: center;
    margin-bottom: 1.25rem;
}

.expiry-section p {
    font-size: 0.85rem;
    color: #ffc107;
}

.expiry-time {
    font-weight: 700;
    font-size: 1rem;
}

.qris-section {
    text-align: center;
    padding: 1rem;
}

.qris-code {
    background: #fff;
    padding: 1rem;
    border-radius: 12px;
    display: inline-block;
    margin-bottom: 1rem;
}

.qris-code img {
    max-width: 220px;
    height: auto;
    display: block;
}

.qris-note {
    font-size: 0.85rem;
    color: rgba(255,255,255,0.6);
}

.steps-section {
    margin-top: 1.5rem;
}

.steps-title {
    font-size: 1rem;
    font-weight: 600;
    margin-bottom: 1rem;
    color: #d50000;
}

.step-item {
    display: flex;
    gap: 0.875rem;
    margin-bottom: 1rem;
    padding: 0.875rem;
    background: rgba(255,255,255,0.02);
    border-radius: 8px;
    border-left: 3px solid #d50000;
}

.step-num {
    width: 28px;
    height: 28px;
    background: #d50000;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.85rem;
    font-weight: 700;
    flex-shrink: 0;
}

.step-text {
    flex: 1;
}

.step-text h4 {
    font-size: 0.95rem;
    margin-bottom: 0.25rem;
}

.step-text p {
    font-size: 0.85rem;
    color: rgba(255,255,255,0.6);
    line-height: 1.5;
}

.action-buttons {
    display: flex;
    gap: 0.875rem;
    margin-top: 1.5rem;
    flex-wrap: wrap;
}

.btn {
    flex: 1;
    padding: 0.875rem;
    border-radius: 8px;
    font-size: 0.95rem;
    font-weight: 600;
    text-align: center;
    text-decoration: none;
    transition: all 0.25s;
    min-width: 140px;
}

.btn-primary {
    background: linear-gradient(135deg, #d50000, #ff4444);
    color: #fff;
    border: none;
}

.btn-secondary {
    background: rgba(255,255,255,0.05);
    border: 1px solid rgba(255,255,255,0.1);
    color: #fff;
}

.btn:hover {
    transform: translateY(-2px);
}

.check-status {
    text-align: center;
    margin-top: 1.5rem;
    padding: 1rem;
    background: rgba(100,150,255,0.1);
    border-radius: 8px;
    border: 1px solid rgba(100,150,255,0.2);
}

.check-status p {
    font-size: 0.85rem;
    color: #6496ff;
}

#statusText {
    font-weight: 600;
}

@media (max-width: 480px) {
    .va-number { font-size: 1.25rem; }
    .mandiri-codes { flex-direction: column; }
}
</style>

<div class="payment-page">
    <div class="payment-container">
        <div class="payment-header">
            <div class="status-badge status-pending" id="statusBadge">
                ⏳ Menunggu Pembayaran
            </div>
            <h1>Instruksi Pembayaran</h1>
            <p>Selesaikan pembayaran sebelum waktu expired</p>
        </div>

        <div class="payment-card">
            <div class="bank-header">
                <div class="bank-logo" style="background: <?= $bank_info['color'] ?>">
                    <?= strtoupper(substr($bank_info['name'], 0, 3)) ?>
                </div>
                <div>
                    <div class="bank-name"><?= $bank_info['name'] ?></div>
                    <div class="bank-type">
                        <?= $payment_method === 'qris' ? 'QRIS Payment' : ($payment_method === 'mandiri' ? 'Bill Payment' : 'Virtual Account') ?>
                    </div>
                </div>
            </div>

            <?php if ($payment_method === 'qris' && $qris_url): ?>
            <!-- QRIS Payment -->
            <div class="qris-section">
                <div class="qris-code">
                    <img src="<?= htmlspecialchars($qris_url) ?>" alt="QRIS Code" id="qrisImage">
                </div>
                <p class="qris-note">Scan dengan GoPay, OVO, DANA, ShopeePay,<br>atau aplikasi e-wallet lainnya</p>
            </div>

            <?php elseif ($payment_method === 'mandiri' && $biller_code && $bill_key): ?>
            <!-- Mandiri Bill Payment -->
            <div class="va-section">
                <div class="va-label">Kode Biller & Nomor VA</div>
                <div class="mandiri-codes">
                    <div class="mandiri-code">
                        <label>Kode Biller</label>
                        <span id="billerCode"><?= htmlspecialchars($biller_code) ?></span>
                    </div>
                    <div class="mandiri-code">
                        <label>Nomor VA</label>
                        <span id="billKey"><?= htmlspecialchars($bill_key) ?></span>
                    </div>
                </div>
                <button class="copy-btn" onclick="copyMandiri()" style="margin-top: 1rem;">
                    📋 Salin Kode
                </button>
            </div>

            <?php elseif ($va_number): ?>
            <!-- Bank Transfer VA -->
            <div class="va-section">
                <div class="va-label">Nomor Virtual Account</div>
                <div class="va-number" id="vaNumber"><?= htmlspecialchars($va_number) ?></div>
                <button class="copy-btn" onclick="copyVA()">
                    📋 Salin Nomor VA
                </button>
            </div>

            <?php else: ?>
            <!-- No payment data -->
            <div class="va-section">
                <p style="color: rgba(255,255,255,0.6);">
                    Data pembayaran tidak tersedia.<br>
                    Silakan pilih metode pembayaran kembali.
                </p>
                <a href="payment-gateway.php?booking_id=<?= $booking_id ?>" class="btn btn-primary" style="display: inline-block; margin-top: 1rem;">
                    Pilih Metode Pembayaran
                </a>
            </div>
            <?php endif; ?>

            <div class="amount-section">
                <div class="amount-label">Total Pembayaran</div>
                <div class="amount-value">Rp <?= number_format($amount, 0, ',', '.') ?></div>
            </div>

            <?php if ($expiry_time): ?>
            <div class="expiry-section">
                <p>⏰ Bayar sebelum: <span class="expiry-time" id="expiryTime"><?= date('d M Y, H:i', strtotime($expiry_time)) ?> WIB</span></p>
            </div>
            <?php endif; ?>

            <div class="steps-section">
                <div class="steps-title">Cara Pembayaran:</div>
                
                <?php if ($payment_method === 'qris'): ?>
                <div class="step-item">
                    <div class="step-num">1</div>
                    <div class="step-text">
                        <h4>Buka Aplikasi E-Wallet</h4>
                        <p>GoPay, OVO, DANA, ShopeePay, LinkAja, atau mobile banking</p>
                    </div>
                </div>
                <div class="step-item">
                    <div class="step-num">2</div>
                    <div class="step-text">
                        <h4>Scan QR Code</h4>
                        <p>Pilih menu Scan/Bayar, arahkan kamera ke QR code di atas</p>
                    </div>
                </div>
                <div class="step-item">
                    <div class="step-num">3</div>
                    <div class="step-text">
                        <h4>Konfirmasi Pembayaran</h4>
                        <p>Pastikan nominal Rp <?= number_format($amount, 0, ',', '.') ?>, lalu konfirmasi</p>
                    </div>
                </div>

                <?php elseif ($payment_method === 'mandiri'): ?>
                <div class="step-item">
                    <div class="step-num">1</div>
                    <div class="step-text">
                        <h4>Buka Livin' by Mandiri</h4>
                        <p>Pilih menu Pembayaran → Buat Pembayaran Baru</p>
                    </div>
                </div>
                <div class="step-item">
                    <div class="step-num">2</div>
                    <div class="step-text">
                        <h4>Pilih Multipayment</h4>
                        <p>Masukkan Kode Biller: <strong><?= $biller_code ?></strong></p>
                    </div>
                </div>
                <div class="step-item">
                    <div class="step-num">3</div>
                    <div class="step-text">
                        <h4>Input Nomor VA</h4>
                        <p>Masukkan: <strong><?= $bill_key ?></strong></p>
                    </div>
                </div>
                <div class="step-item">
                    <div class="step-num">4</div>
                    <div class="step-text">
                        <h4>Konfirmasi</h4>
                        <p>Verifikasi nominal dan konfirmasi pembayaran</p>
                    </div>
                </div>

                <?php else: ?>
                <div class="step-item">
                    <div class="step-num">1</div>
                    <div class="step-text">
                        <h4>Buka M-Banking <?= $bank_info['name'] ?></h4>
                        <p>Pilih menu Transfer → Virtual Account</p>
                    </div>
                </div>
                <div class="step-item">
                    <div class="step-num">2</div>
                    <div class="step-text">
                        <h4>Masukkan Nomor VA</h4>
                        <p>Input: <strong><?= $va_number ?></strong></p>
                    </div>
                </div>
                <div class="step-item">
                    <div class="step-num">3</div>
                    <div class="step-text">
                        <h4>Verifikasi & Bayar</h4>
                        <p>Cek nominal Rp <?= number_format($amount, 0, ',', '.') ?>, lalu konfirmasi</p>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="check-status">
            <p>🔄 Status: <span id="statusText">Mengecek pembayaran...</span></p>
        </div>

        <div class="action-buttons">
            <a href="booking-detail.php?id=<?= $booking_id ?>" class="btn btn-primary">
                Cek Status Pesanan
            </a>
            <a href="my-bookings.php" class="btn btn-secondary">
                Kembali
            </a>
        </div>
    </div>
</div>

<script>
function copyVA() {
    const va = document.getElementById('vaNumber').textContent.trim();
    copyToClipboard(va, event.target);
}

function copyMandiri() {
    const biller = document.getElementById('billerCode').textContent.trim();
    const billKey = document.getElementById('billKey').textContent.trim();
    copyToClipboard(biller + ' ' + billKey, event.target);
}

function copyToClipboard(text, btn) {
    // Try modern API first
    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(text).then(() => {
            showCopied(btn);
        }).catch(() => {
            fallbackCopy(text, btn);
        });
    } else {
        fallbackCopy(text, btn);
    }
}

function fallbackCopy(text, btn) {
    // Fallback for older browsers or non-HTTPS
    const textArea = document.createElement('textarea');
    textArea.value = text;
    textArea.style.position = 'fixed';
    textArea.style.left = '-999999px';
    textArea.style.top = '-999999px';
    document.body.appendChild(textArea);
    textArea.focus();
    textArea.select();
    
    try {
        document.execCommand('copy');
        showCopied(btn);
    } catch (err) {
        alert('Gagal menyalin. Silakan salin manual: ' + text);
    }
    
    document.body.removeChild(textArea);
}

function showCopied(btn) {
    const original = btn.innerHTML;
    btn.innerHTML = '✅ Tersalin!';
    btn.classList.add('copied');
    setTimeout(() => {
        btn.innerHTML = original;
        btn.classList.remove('copied');
    }, 2000);
}

// Check payment status periodically
let checkCount = 0;
const maxChecks = 60; // 10 minutes (every 10 seconds)

function checkPaymentStatus() {
    if (checkCount >= maxChecks) {
        document.getElementById('statusText').textContent = 'Timeout - Refresh halaman untuk cek ulang';
        return;
    }
    
    fetch('../../php/api/check-payment-status.php?booking_id=<?= $booking_id ?>')
        .then(r => r.json())
        .then(data => {
            checkCount++;
            
            if (data.status === 'completed' || data.status === 'settlement' || data.status === 'capture') {
                document.getElementById('statusText').textContent = 'Pembayaran Berhasil! ✅';
                document.getElementById('statusBadge').className = 'status-badge status-success';
                document.getElementById('statusBadge').textContent = '✅ Pembayaran Berhasil';
                
                // Redirect after 2 seconds
                setTimeout(() => {
                    window.location.href = 'booking-detail.php?id=<?= $booking_id ?>&payment=success';
                }, 2000);
            } else if (data.status === 'pending') {
                document.getElementById('statusText').textContent = 'Menunggu pembayaran... (' + checkCount + ')';
            } else if (data.status === 'expired' || data.status === 'cancel') {
                document.getElementById('statusText').textContent = 'Pembayaran expired/dibatalkan';
                document.getElementById('statusBadge').textContent = '❌ Expired';
            } else {
                document.getElementById('statusText').textContent = 'Status: ' + (data.status || 'Menunggu...');
            }
        })
        .catch(err => {
            document.getElementById('statusText').textContent = 'Mengecek ulang...';
        });
}

// Initial check
checkPaymentStatus();

// Check every 10 seconds
setInterval(checkPaymentStatus, 10000);
</script>

<?php include '../../php/includes/footer.php'; ?>
