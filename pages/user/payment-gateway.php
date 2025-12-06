<?php
/**
 * Payment Gateway - Real Midtrans Integration
 * Generates real VA numbers and QRIS codes via Midtrans Core API
 */
session_start();
require_once '../../php/config/database.php';
require_once '../../php/includes/MidtransAPI.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

// Get booking ID from URL
if (!isset($_GET['booking_id'])) {
    header('Location: my-bookings.php');
    exit;
}

$booking_id = filter_var($_GET['booking_id'], FILTER_SANITIZE_NUMBER_INT);
$user_id = $_SESSION['user_id'];

// Get booking details with user info
$stmt = $pdo->prepare("
    SELECT b.*, v.nama as vehicle_name, v.harga_per_hari, v.merek,
           u.nama_lengkap, u.email, u.nomor_telepon
    FROM bookings b 
    JOIN vehicles v ON b.vehicle_id = v.id 
    JOIN users u ON b.user_id = u.id
    WHERE b.id = ? AND b.user_id = ? AND b.status != 'cancelled'
");
$stmt->execute([$booking_id, $user_id]);
$booking = $stmt->fetch();

if (!$booking) {
    header('Location: my-bookings.php');
    exit;
}

// Calculate total amount
$start_date = new DateTime($booking['start_date']);
$end_date = new DateTime($booking['end_date']);
$duration = $end_date->diff($start_date)->days + 1;
$total_amount = (int)($duration * $booking['harga_per_hari']);

// Initialize Midtrans API
$midtrans = new MidtransAPI();

// Payment methods available
$payment_methods = [
    'bca' => [
        'name' => 'BCA Virtual Account',
        'icon' => '🏦',
        'description' => 'Transfer via BCA Virtual Account',
        'color' => '#1f4788',
        'type' => 'bank_transfer'
    ],
    'bni' => [
        'name' => 'BNI Virtual Account', 
        'icon' => '🏦',
        'description' => 'Transfer via BNI Virtual Account',
        'color' => '#ff6600',
        'type' => 'bank_transfer'
    ],
    'bri' => [
        'name' => 'BRI Virtual Account',
        'icon' => '🏦', 
        'description' => 'Transfer via BRI Virtual Account',
        'color' => '#0066cc',
        'type' => 'bank_transfer'
    ],
    'mandiri' => [
        'name' => 'Mandiri Bill Payment',
        'icon' => '🏦',
        'description' => 'Bayar via Mandiri Bill Payment',
        'color' => '#003366',
        'type' => 'echannel'
    ],
    'qris' => [
        'name' => 'QRIS',
        'icon' => '📱',
        'description' => 'Scan QR dengan e-wallet apapun',
        'color' => '#00aa5b',
        'type' => 'qris'
    ]
];

$error = '';

// Process payment method selection
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['payment_method'])) {
    $payment_method = $_POST['payment_method'];
    
    if (!isset($payment_methods[$payment_method])) {
        $error = 'Metode pembayaran tidak valid!';
    } else {
        // Generate unique order ID
        $order_id = 'EZRENT-' . $booking_id . '-' . time();
        
        // Customer details for Midtrans
        $customerDetails = [
            'first_name' => $booking['nama_lengkap'],
            'email' => $booking['email'],
            'phone' => $booking['nomor_telepon'] ?: '08123456789'
        ];
        
        // Create transaction based on payment method
        $method_info = $payment_methods[$payment_method];
        
        if ($method_info['type'] === 'bank_transfer' || $method_info['type'] === 'echannel') {
            $result = $midtrans->createBankTransfer($order_id, $total_amount, $payment_method, $customerDetails);
        } else {
            $result = $midtrans->createGoPay($order_id, $total_amount, $customerDetails);
        }
        
        if ($result['success']) {
            $payment_data = $result['data'];
            
            // Extract VA number or QRIS data
            $va_info = $midtrans->extractVANumber($result);
            $qris_info = $midtrans->extractQRISData($result);
            
            // Get VA number
            $va_number = null;
            $biller_code = null;
            $bill_key = null;
            $qris_url = null;
            
            if ($va_info) {
                if (isset($va_info['va_number'])) {
                    $va_number = $va_info['va_number'];
                }
                if (isset($va_info['biller_code'])) {
                    $biller_code = $va_info['biller_code'];
                }
                if (isset($va_info['bill_key'])) {
                    $bill_key = $va_info['bill_key'];
                }
            }
            
            if ($qris_info && isset($qris_info['qr_url'])) {
                $qris_url = $qris_info['qr_url'];
            }
            
            $expiry_time = $payment_data['expiry_time'] ?? null;
            
            // Save payment to database
            try {
                // Check if payment already exists
                $check = $pdo->prepare("SELECT id FROM payments WHERE booking_id = ?");
                $check->execute([$booking_id]);
                
                if ($check->rowCount() > 0) {
                    // Update existing
                    $stmt = $pdo->prepare("
                        UPDATE payments SET
                            payment_method = ?,
                            payment_code = ?,
                            amount = ?,
                            status = 'pending',
                            midtrans_order_id = ?,
                            midtrans_transaction_id = ?,
                            va_number = ?,
                            biller_code = ?,
                            bill_key = ?,
                            qris_url = ?,
                            expiry_time = ?,
                            updated_at = NOW()
                        WHERE booking_id = ?
                    ");
                    $stmt->execute([
                        $payment_method,
                        $va_number ?? $bill_key ?? $order_id,
                        $total_amount,
                        $order_id,
                        $payment_data['transaction_id'] ?? null,
                        $va_number,
                        $biller_code,
                        $bill_key,
                        $qris_url,
                        $expiry_time,
                        $booking_id
                    ]);
                } else {
                    // Insert new
                    $stmt = $pdo->prepare("
                        INSERT INTO payments (
                            booking_id, user_id, payment_method, payment_code, 
                            amount, status, midtrans_order_id, midtrans_transaction_id,
                            va_number, biller_code, bill_key, qris_url, expiry_time, created_at
                        ) VALUES (?, ?, ?, ?, ?, 'pending', ?, ?, ?, ?, ?, ?, ?, NOW())
                    ");
                    $stmt->execute([
                        $booking_id,
                        $user_id,
                        $payment_method,
                        $va_number ?? $bill_key ?? $order_id,
                        $total_amount,
                        $order_id,
                        $payment_data['transaction_id'] ?? null,
                        $va_number,
                        $biller_code,
                        $bill_key,
                        $qris_url,
                        $expiry_time
                    ]);
                }
                
                // Update booking with payment method
                $pdo->prepare("UPDATE bookings SET payment_method = ?, updated_at = NOW() WHERE id = ?")
                    ->execute([$payment_method, $booking_id]);
                
                // Store in session for instruction page
                $_SESSION['payment_data'] = [
                    'booking_id' => $booking_id,
                    'order_id' => $order_id,
                    'method' => $payment_method,
                    'method_name' => $method_info['name'],
                    'amount' => $total_amount,
                    'va_number' => $va_number,
                    'biller_code' => $biller_code,
                    'bill_key' => $bill_key,
                    'qris_url' => $qris_url,
                    'expiry_time' => $expiry_time,
                    'transaction_status' => $payment_data['transaction_status'] ?? 'pending'
                ];
                
                // Redirect to payment instruction
                header("Location: payment-instruction.php?booking_id=$booking_id");
                exit;
                
            } catch (PDOException $e) {
                $error = 'Gagal menyimpan data pembayaran. Silakan coba lagi.';
                error_log('Payment DB Error: ' . $e->getMessage());
            }
        } else {
            $error = 'Gagal membuat transaksi: ' . ($result['error'] ?? 'Silakan coba lagi');
            error_log('Midtrans Error: ' . json_encode($result));
        }
    }
}

$page_title = "Pilih Metode Pembayaran - EzRent";
include 'header.php';
?>

<style>
* { margin: 0; padding: 0; box-sizing: border-box; }

body {
    background: #0a0e27;
    color: #fff;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

main {
    min-height: calc(100vh - 120px);
    padding: 2rem;
    background: linear-gradient(135deg, #0a0e27 0%, #16213e 100%);
}

.container { max-width: 900px; margin: 0 auto; }

.header { text-align: center; margin-bottom: 2rem; }
.header h1 { font-size: 1.75rem; margin-bottom: 0.5rem; }
.header p { color: rgba(255,255,255,0.6); font-size: 0.95rem; }

.summary-card {
    background: rgba(255,255,255,0.05);
    border: 1px solid rgba(255,255,255,0.1);
    border-radius: 12px;
    padding: 1.25rem;
    margin-bottom: 1.5rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 1rem;
}

.summary-item { display: flex; flex-direction: column; gap: 0.2rem; }
.summary-item label { font-size: 0.75rem; color: rgba(255,255,255,0.5); text-transform: uppercase; }
.summary-item span { font-weight: 600; }

.total-box {
    background: linear-gradient(135deg, #d50000, #ff4444);
    padding: 0.75rem 1.25rem;
    border-radius: 8px;
    font-size: 1.1rem;
    font-weight: 700;
}

.alert {
    padding: 0.875rem 1rem;
    border-radius: 8px;
    margin-bottom: 1.25rem;
    font-size: 0.9rem;
}

.alert-error {
    background: rgba(255,68,68,0.15);
    border: 1px solid rgba(255,68,68,0.3);
    color: #ff6b6b;
}

.alert-info {
    background: rgba(100,150,255,0.1);
    border: 1px solid rgba(100,150,255,0.2);
    color: #6496ff;
}

.methods-card {
    background: rgba(255,255,255,0.03);
    border: 1px solid rgba(255,255,255,0.1);
    border-radius: 12px;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
}

.methods-card h2 {
    font-size: 1.1rem;
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.methods-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 0.875rem;
}

.method-option {
    background: rgba(255,255,255,0.02);
    border: 2px solid rgba(255,255,255,0.08);
    border-radius: 10px;
    padding: 1rem;
    cursor: pointer;
    transition: all 0.25s ease;
    position: relative;
}

.method-option:hover {
    border-color: rgba(255,255,255,0.2);
    background: rgba(255,255,255,0.05);
}

.method-option input { position: absolute; opacity: 0; }

.method-option input:checked + .method-body {
    background: rgba(213,0,0,0.1);
    border-radius: 8px;
}

.method-option input:checked + .method-body .check-icon {
    background: var(--color, #d50000);
    border-color: var(--color, #d50000);
}

.method-option input:checked + .method-body .check-icon::after {
    content: '✓';
    color: #fff;
    font-size: 0.7rem;
}

.method-body {
    display: flex;
    align-items: center;
    gap: 0.875rem;
    padding: 0.5rem;
    transition: all 0.25s;
}

.method-icon { font-size: 2rem; }

.method-info { flex: 1; }
.method-name { font-weight: 600; font-size: 0.95rem; margin-bottom: 0.15rem; }
.method-desc { font-size: 0.8rem; color: rgba(255,255,255,0.5); }

.check-icon {
    width: 22px;
    height: 22px;
    border: 2px solid rgba(255,255,255,0.3);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.25s;
    flex-shrink: 0;
}

.btn-row {
    display: flex;
    gap: 0.875rem;
    justify-content: center;
    flex-wrap: wrap;
}

.btn {
    padding: 0.875rem 1.5rem;
    border: none;
    border-radius: 8px;
    font-size: 0.95rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.25s;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}

.btn-primary {
    background: linear-gradient(135deg, #d50000, #ff4444);
    color: #fff;
    min-width: 180px;
    justify-content: center;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(213,0,0,0.3);
}

.btn-primary:disabled {
    opacity: 0.5;
    cursor: not-allowed;
    transform: none;
}

.btn-secondary {
    background: rgba(255,255,255,0.1);
    color: #fff;
    border: 1px solid rgba(255,255,255,0.2);
}

.btn-secondary:hover {
    background: rgba(255,255,255,0.15);
}

.security-note {
    text-align: center;
    margin-top: 1.25rem;
    color: rgba(255,255,255,0.5);
    font-size: 0.85rem;
}

.loading {
    display: none;
    position: fixed;
    top: 0; left: 0; right: 0; bottom: 0;
    background: rgba(0,0,0,0.85);
    z-index: 9999;
    justify-content: center;
    align-items: center;
    flex-direction: column;
    gap: 1rem;
}

.loading.show { display: flex; }

.spinner {
    width: 40px;
    height: 40px;
    border: 3px solid rgba(255,255,255,0.1);
    border-top-color: #d50000;
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
}

@keyframes spin { to { transform: rotate(360deg); } }

@media (max-width: 600px) {
    .methods-grid { grid-template-columns: 1fr; }
    .summary-card { flex-direction: column; text-align: center; }
}
</style>

<main>
    <div class="container">
        <div class="header">
            <h1>💳 Pilih Metode Pembayaran</h1>
            <p>Pembayaran aman via Midtrans Payment Gateway</p>
        </div>
        
        <div class="summary-card">
            <div class="summary-item">
                <label>Kendaraan</label>
                <span><?= htmlspecialchars($booking['merek'] . ' ' . $booking['vehicle_name']) ?></span>
            </div>
            <div class="summary-item">
                <label>Durasi</label>
                <span><?= $duration ?> hari</span>
            </div>
            <div class="summary-item">
                <label>Periode</label>
                <span><?= $start_date->format('d M') ?> - <?= $end_date->format('d M Y') ?></span>
            </div>
            <div class="total-box">
                💰 Rp <?= number_format($total_amount, 0, ',', '.') ?>
            </div>
        </div>
        
        <?php if (!empty($error)): ?>
        <div class="alert alert-error">⚠️ <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <div class="alert alert-info">
            ℹ️ Pilih metode pembayaran. Anda akan mendapat nomor VA atau QR code dari Midtrans.
        </div>
        
        <form method="POST" id="paymentForm">
            <div class="methods-card">
                <h2>🏦 Metode Pembayaran</h2>
                
                <div class="methods-grid">
                    <?php foreach ($payment_methods as $key => $method): ?>
                    <label class="method-option" style="--color: <?= $method['color'] ?>">
                        <input type="radio" name="payment_method" value="<?= $key ?>" required>
                        <div class="method-body">
                            <span class="method-icon"><?= $method['icon'] ?></span>
                            <div class="method-info">
                                <div class="method-name"><?= $method['name'] ?></div>
                                <div class="method-desc"><?= $method['description'] ?></div>
                            </div>
                            <div class="check-icon"></div>
                        </div>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <div class="btn-row">
                <a href="my-bookings.php" class="btn btn-secondary">← Kembali</a>
                <button type="submit" class="btn btn-primary" id="submitBtn">
                    Lanjutkan Pembayaran →
                </button>
            </div>
            
            <div class="security-note">🔒 Transaksi diamankan dengan enkripsi SSL</div>
        </form>
    </div>
</main>

<div class="loading" id="loading">
    <div class="spinner"></div>
    <p>Memproses pembayaran dengan Midtrans...</p>
</div>

<script>
document.getElementById('paymentForm').addEventListener('submit', function(e) {
    const selected = document.querySelector('input[name="payment_method"]:checked');
    if (!selected) {
        e.preventDefault();
        alert('Pilih metode pembayaran terlebih dahulu!');
        return;
    }
    
    document.getElementById('loading').classList.add('show');
    document.getElementById('submitBtn').disabled = true;
});
</script>

<?php include '../../php/includes/footer.php'; ?>
