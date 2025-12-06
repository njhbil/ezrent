<?php
/**
 * Halaman Panduan Testing Midtrans Sandbox
 * Untuk Admin - Informasi lengkap cara testing pembayaran di sandbox
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$page_title = "Panduan Testing Midtrans - EzRent Admin";
include 'header.php';
?>

<style>
body {
    background: linear-gradient(180deg, #0a0a0a 0%, #050505 100%) !important;
    color: #fff !important;
}

:root {
    --primary: #d50000;
    --success: #00c853;
    --warning: #ffc107;
    --info: #00b0ff;
}

.test-guide {
    padding: 2rem 0;
    min-height: 100vh;
}

.container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 0 20px;
}

.page-header {
    background: linear-gradient(145deg, rgba(213, 0, 0, 0.1), rgba(0, 0, 0, 0.2));
    border: 1px solid rgba(213, 0, 0, 0.2);
    border-radius: 12px;
    padding: 2rem;
    margin-bottom: 2rem;
}

.page-title {
    font-size: 2rem;
    margin-bottom: 0.5rem;
    color: var(--primary);
}

.page-subtitle {
    color: rgba(255, 255, 255, 0.6);
    font-size: 1rem;
}

.section {
    background: rgba(255, 255, 255, 0.03);
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 12px;
    padding: 2rem;
    margin-bottom: 2rem;
}

.section-title {
    font-size: 1.5rem;
    margin-bottom: 1.5rem;
    color: var(--primary);
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.section-title::before {
    content: '';
    width: 4px;
    height: 24px;
    background: var(--primary);
    border-radius: 2px;
}

.card-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.test-card {
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 8px;
    padding: 1.5rem;
    transition: all 0.3s;
}

.test-card:hover {
    border-color: var(--primary);
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(213, 0, 0, 0.2);
}

.card-header {
    font-weight: 600;
    color: var(--primary);
    margin-bottom: 1rem;
    font-size: 1.1rem;
}

.card-number {
    font-family: 'Courier New', monospace;
    font-size: 1.2rem;
    background: rgba(0, 0, 0, 0.3);
    padding: 0.75rem;
    border-radius: 6px;
    margin: 0.75rem 0;
    border-left: 3px solid var(--primary);
    cursor: pointer;
    transition: all 0.3s;
}

.card-number:hover {
    background: rgba(0, 0, 0, 0.5);
    border-left-color: var(--success);
}

.card-info {
    font-size: 0.85rem;
    color: rgba(255, 255, 255, 0.6);
    margin-top: 0.5rem;
}

.badge {
    display: inline-block;
    padding: 0.25rem 0.75rem;
    border-radius: 4px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    margin-right: 0.5rem;
}

.badge-success { background: rgba(0, 200, 83, 0.2); color: var(--success); }
.badge-danger { background: rgba(213, 0, 0, 0.2); color: var(--primary); }
.badge-info { background: rgba(0, 176, 255, 0.2); color: var(--info); }
.badge-warning { background: rgba(255, 193, 7, 0.2); color: var(--warning); }

table {
    width: 100%;
    border-collapse: collapse;
    margin: 1rem 0;
}

th, td {
    padding: 0.75rem;
    text-align: left;
    border-bottom: 1px solid rgba(255, 255, 255, 0.08);
}

th {
    background: rgba(255, 255, 255, 0.05);
    color: var(--primary);
    font-weight: 600;
}

.simulator-link {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    background: linear-gradient(135deg, var(--info), #0066cc);
    color: white;
    padding: 0.75rem 1.5rem;
    border-radius: 6px;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s;
    margin-right: 1rem;
    margin-bottom: 1rem;
}

.simulator-link:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(0, 176, 255, 0.4);
}

.code-block {
    background: rgba(0, 0, 0, 0.5);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 6px;
    padding: 1rem;
    font-family: 'Courier New', monospace;
    font-size: 0.9rem;
    overflow-x: auto;
    margin: 1rem 0;
}

.info-box {
    background: rgba(0, 176, 255, 0.1);
    border-left: 4px solid var(--info);
    padding: 1rem 1.5rem;
    border-radius: 6px;
    margin: 1rem 0;
}

.warning-box {
    background: rgba(255, 193, 7, 0.1);
    border-left: 4px solid var(--warning);
    padding: 1rem 1.5rem;
    border-radius: 6px;
    margin: 1rem 0;
}

.copy-btn {
    background: var(--success);
    color: white;
    border: none;
    padding: 0.5rem 1rem;
    border-radius: 4px;
    cursor: pointer;
    font-size: 0.85rem;
    transition: all 0.3s;
    margin-top: 0.5rem;
}

.copy-btn:hover {
    background: #00a043;
    transform: scale(1.05);
}

.payment-method-section {
    margin: 2rem 0;
}

.accordion {
    background: rgba(255, 255, 255, 0.03);
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 8px;
    margin-bottom: 1rem;
    overflow: hidden;
}

.accordion-header {
    padding: 1rem 1.5rem;
    cursor: pointer;
    display: flex;
    justify-content: space-between;
    align-items: center;
    transition: all 0.3s;
}

.accordion-header:hover {
    background: rgba(255, 255, 255, 0.05);
}

.accordion-content {
    padding: 0 1.5rem;
    max-height: 0;
    overflow: hidden;
    transition: max-height 0.3s ease;
}

.accordion.active .accordion-content {
    max-height: 2000px;
    padding: 1.5rem;
}
</style>

<div class="test-guide">
    <div class="container">
        <div class="page-header">
            <h1 class="page-title">🧪 Panduan Testing Midtrans Sandbox</h1>
            <p class="page-subtitle">Lengkap dengan test credentials, simulators, dan cara penggunaan</p>
        </div>

        <!-- Environment Status -->
        <div class="section">
            <h2 class="section-title">Status Environment</h2>
            <div class="info-box">
                <strong>🟢 Mode: SANDBOX</strong><br>
                Semua transaksi di environment ini adalah testing dan TIDAK menggunakan uang asli.
                Gunakan test credentials di bawah untuk simulasi pembayaran.
            </div>
            <div class="code-block">
Server Key: <?php require_once '../../php/config/midtrans.php'; echo getMidtransServerKey(); ?><br>
Client Key: <?php echo getMidtransClientKey(); ?><br>
Environment: <?php echo MIDTRANS_ENV; ?>
            </div>
        </div>

        <!-- Credit Card Testing -->
        <div class="section">
            <h2 class="section-title">💳 Credit Card Testing</h2>
            
            <div class="warning-box">
                <strong>⚠️ Penting!</strong> Gunakan kredensial berikut untuk semua kartu test:
                <ul style="margin: 0.5rem 0 0 1.5rem;">
                    <li><strong>Expiry Month:</strong> 01 (atau bulan apa saja)</li>
                    <li><strong>Expiry Year:</strong> 2025 (atau tahun yang akan datang)</li>
                    <li><strong>CVV:</strong> 123</li>
                    <li><strong>OTP/3DS:</strong> 112233</li>
                </ul>
            </div>

            <!-- VISA Cards -->
            <div class="payment-method-section">
                <h3 style="color: var(--info); margin-bottom: 1rem;">🔵 VISA Cards</h3>
                
                <h4 style="color: rgba(255,255,255,0.8); margin: 1.5rem 0 1rem;">Full Authentication (3DS Enabled)</h4>
                <div class="card-grid">
                    <div class="test-card">
                        <div class="card-header">✅ Accept Transaction</div>
                        <div class="card-number" onclick="copyToClipboard('4811111111111114')">
                            4811 1111 1111 1114
                        </div>
                        <button class="copy-btn" onclick="copyToClipboard('4811111111111114')">📋 Copy</button>
                        <div class="card-info">Transaksi akan berhasil dengan 3DS authentication</div>
                    </div>
                    <div class="test-card">
                        <div class="card-header">❌ Denied by Bank</div>
                        <div class="card-number" onclick="copyToClipboard('4911111111111113')">
                            4911 1111 1111 1113
                        </div>
                        <button class="copy-btn" onclick="copyToClipboard('4911111111111113')">📋 Copy</button>
                        <div class="card-info">Transaksi akan ditolak oleh bank</div>
                    </div>
                </div>

                <h4 style="color: rgba(255,255,255,0.8); margin: 1.5rem 0 1rem;">Attempted Authentication (No 3DS)</h4>
                <div class="card-grid">
                    <div class="test-card">
                        <div class="card-header">✅ Accept Transaction</div>
                        <div class="card-number" onclick="copyToClipboard('4411111111111118')">
                            4411 1111 1111 1118
                        </div>
                        <button class="copy-btn" onclick="copyToClipboard('4411111111111118')">📋 Copy</button>
                        <div class="card-info">Transaksi berhasil tanpa 3DS</div>
                    </div>
                    <div class="test-card">
                        <div class="card-header">🚫 Denied by FDS</div>
                        <div class="card-number" onclick="copyToClipboard('4611111111111116')">
                            4611 1111 1111 1116
                        </div>
                        <button class="copy-btn" onclick="copyToClipboard('4611111111111116')">📋 Copy</button>
                        <div class="card-info">Ditolak oleh Fraud Detection System</div>
                    </div>
                    <div class="test-card">
                        <div class="card-header">❌ Denied by Bank</div>
                        <div class="card-number" onclick="copyToClipboard('4711111111111115')">
                            4711 1111 1111 1115
                        </div>
                        <button class="copy-btn" onclick="copyToClipboard('4711111111111115')">📋 Copy</button>
                        <div class="card-info">Transaksi ditolak oleh bank</div>
                    </div>
                </div>
            </div>

            <!-- Mastercard -->
            <div class="payment-method-section">
                <h3 style="color: var(--warning); margin-bottom: 1rem;">🟡 Mastercard</h3>
                
                <h4 style="color: rgba(255,255,255,0.8); margin: 1.5rem 0 1rem;">Full Authentication (3DS Enabled)</h4>
                <div class="card-grid">
                    <div class="test-card">
                        <div class="card-header">✅ Accept Transaction</div>
                        <div class="card-number" onclick="copyToClipboard('5211111111111117')">
                            5211 1111 1111 1117
                        </div>
                        <button class="copy-btn" onclick="copyToClipboard('5211111111111117')">📋 Copy</button>
                    </div>
                    <div class="test-card">
                        <div class="card-header">❌ Denied by Bank</div>
                        <div class="card-number" onclick="copyToClipboard('5111111111111118')">
                            5111 1111 1111 1118
                        </div>
                        <button class="copy-btn" onclick="copyToClipboard('5111111111111118')">📋 Copy</button>
                    </div>
                </div>

                <h4 style="color: rgba(255,255,255,0.8); margin: 1.5rem 0 1rem;">Attempted Authentication (No 3DS)</h4>
                <div class="card-grid">
                    <div class="test-card">
                        <div class="card-header">✅ Accept</div>
                        <div class="card-number" onclick="copyToClipboard('5410111111111116')">
                            5410 1111 1111 1116
                        </div>
                        <button class="copy-btn" onclick="copyToClipboard('5410111111111116')">📋 Copy</button>
                    </div>
                    <div class="test-card">
                        <div class="card-header">🚫 Denied by FDS</div>
                        <div class="card-number" onclick="copyToClipboard('5411111111111115')">
                            5411 1111 1111 1115
                        </div>
                        <button class="copy-btn" onclick="copyToClipboard('5411111111111115')">📋 Copy</button>
                    </div>
                    <div class="test-card">
                        <div class="card-header">❌ Denied by Bank</div>
                        <div class="card-number" onclick="copyToClipboard('5511111111111114')">
                            5511 1111 1111 1114
                        </div>
                        <button class="copy-btn" onclick="copyToClipboard('5511111111111114')">📋 Copy</button>
                    </div>
                </div>
            </div>

            <!-- 3DS 2.0 Testing -->
            <div class="payment-method-section">
                <h3 style="color: var(--success); margin-bottom: 1rem;">🔐 3D Secure 2.0 Testing</h3>
                
                <div class="info-box">
                    <strong>ℹ️ 3DS 2.0 Features:</strong><br>
                    - <strong>Frictionless:</strong> No OTP prompt (automatic verification)<br>
                    - <strong>Challenged:</strong> OTP prompt required (enter 112233)
                </div>

                <h4 style="color: rgba(255,255,255,0.8); margin: 1.5rem 0 1rem;">VISA 3DS 2.0</h4>
                <div class="card-grid">
                    <div class="test-card">
                        <div class="card-header">✅ Frictionless Accept</div>
                        <div class="card-number" onclick="copyToClipboard('4556557955726624')">
                            4556 5579 5572 6624
                        </div>
                        <button class="copy-btn" onclick="copyToClipboard('4556557955726624')">📋 Copy</button>
                        <div class="card-info">No OTP prompt - automatic success</div>
                    </div>
                    <div class="test-card">
                        <div class="card-header">🔐 Challenged Accept</div>
                        <div class="card-number" onclick="copyToClipboard('4916994064252017')">
                            4916 9940 6425 2017
                        </div>
                        <button class="copy-btn" onclick="copyToClipboard('4916994064252017')">📋 Copy</button>
                        <div class="card-info">OTP required: 112233</div>
                    </div>
                    <div class="test-card">
                        <div class="card-header">❌ Challenged Deny</div>
                        <div class="card-number" onclick="copyToClipboard('4604633194219929')">
                            4604 6331 9421 9929
                        </div>
                        <button class="copy-btn" onclick="copyToClipboard('4604633194219929')">📋 Copy</button>
                        <div class="card-info">Will be denied after OTP</div>
                    </div>
                </div>

                <h4 style="color: rgba(255,255,255,0.8); margin: 1.5rem 0 1rem;">Mastercard 3DS 2.0</h4>
                <div class="card-grid">
                    <div class="test-card">
                        <div class="card-header">✅ Frictionless Accept</div>
                        <div class="card-number" onclick="copyToClipboard('5333259155643223')">
                            5333 2591 5564 3223
                        </div>
                        <button class="copy-btn" onclick="copyToClipboard('5333259155643223')">📋 Copy</button>
                    </div>
                    <div class="test-card">
                        <div class="card-header">🔐 Challenged Accept</div>
                        <div class="card-number" onclick="copyToClipboard('5306889942833340')">
                            5306 8899 4283 3340
                        </div>
                        <button class="copy-btn" onclick="copyToClipboard('5306889942833340')">📋 Copy</button>
                    </div>
                    <div class="test-card">
                        <div class="card-header">❌ Challenged Deny</div>
                        <div class="card-number" onclick="copyToClipboard('5424184049821670')">
                            5424 1840 4982 1670
                        </div>
                        <button class="copy-btn" onclick="copyToClipboard('5424184049821670')">📋 Copy</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- E-Wallet & QRIS -->
        <div class="section">
            <h2 class="section-title">📱 E-Wallet & QRIS Testing</h2>
            
            <table>
                <thead>
                    <tr>
                        <th>Payment Method</th>
                        <th>Testing Method</th>
                        <th>Simulator Link</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>GoPay</strong></td>
                        <td>Redirect to simulator / QR Code</td>
                        <td>
                            <a href="https://simulator.sandbox.midtrans.com/qris/index" target="_blank" class="simulator-link">
                                🔗 QRIS Simulator
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>ShopeePay</strong></td>
                        <td>Redirect to simulator / QR Code</td>
                        <td>
                            <a href="https://simulator.sandbox.midtrans.com/qris/index" target="_blank" class="simulator-link">
                                🔗 QRIS Simulator
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>QRIS</strong></td>
                        <td>Copy QR code URL to simulator</td>
                        <td>
                            <a href="https://simulator.sandbox.midtrans.com/qris/index" target="_blank" class="simulator-link">
                                🔗 QRIS Simulator
                            </a>
                        </td>
                    </tr>
                </tbody>
            </table>

            <div class="info-box">
                <strong>📝 Cara Testing E-Wallet:</strong>
                <ol style="margin: 0.5rem 0 0 1.5rem;">
                    <li>Pilih metode pembayaran GoPay/ShopeePay/QRIS</li>
                    <li>Copy URL QR Code yang muncul</li>
                    <li>Buka QRIS Simulator di tab baru</li>
                    <li>Paste URL QR Code dan klik Pay</li>
                    <li>Transaksi akan otomatis berhasil</li>
                </ol>
            </div>
        </div>

        <!-- Bank Transfer / Virtual Account -->
        <div class="section">
            <h2 class="section-title">🏦 Bank Transfer / Virtual Account Testing</h2>
            
            <table>
                <thead>
                    <tr>
                        <th>Bank</th>
                        <th>Testing Method</th>
                        <th>Simulator Link</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>BCA VA</strong></td>
                        <td>Input VA number di simulator</td>
                        <td>
                            <a href="https://simulator.sandbox.midtrans.com/bca/va/index" target="_blank" class="simulator-link">
                                🔗 BCA VA Simulator
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>BNI VA</strong></td>
                        <td>Input VA number di simulator</td>
                        <td>
                            <a href="https://simulator.sandbox.midtrans.com/openapi/va/index" target="_blank" class="simulator-link">
                                🔗 BNI VA Simulator
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>BRI VA</strong></td>
                        <td>Input VA number di simulator</td>
                        <td>
                            <a href="https://simulator.sandbox.midtrans.com/openapi/va/index" target="_blank" class="simulator-link">
                                🔗 BRI VA Simulator
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Permata VA</strong></td>
                        <td>Input VA number di simulator</td>
                        <td>
                            <a href="https://simulator.sandbox.midtrans.com/openapi/va/index" target="_blank" class="simulator-link">
                                🔗 Permata VA Simulator
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Mandiri Bill</strong></td>
                        <td>Input payment code di simulator</td>
                        <td>
                            <a href="https://simulator.sandbox.midtrans.com/openapi/va/index" target="_blank" class="simulator-link">
                                🔗 Mandiri Simulator
                            </a>
                        </td>
                    </tr>
                </tbody>
            </table>

            <div class="info-box">
                <strong>📝 Cara Testing Virtual Account:</strong>
                <ol style="margin: 0.5rem 0 0 1.5rem;">
                    <li>Pilih metode pembayaran Bank Transfer</li>
                    <li>Copy nomor Virtual Account yang muncul</li>
                    <li>Buka Bank Simulator yang sesuai</li>
                    <li>Pilih bank yang sama, paste VA number</li>
                    <li>Klik Pay untuk simulasi pembayaran</li>
                </ol>
            </div>
        </div>

        <!-- Convenience Store -->
        <div class="section">
            <h2 class="section-title">🏪 Convenience Store Testing</h2>
            
            <table>
                <thead>
                    <tr>
                        <th>Store</th>
                        <th>Simulator Link</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>Indomaret</strong></td>
                        <td>
                            <a href="https://simulator.sandbox.midtrans.com/indomaret/index" target="_blank" class="simulator-link">
                                🔗 Indomaret Simulator
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Alfamart</strong></td>
                        <td>
                            <a href="https://simulator.sandbox.midtrans.com/alfamart/index" target="_blank" class="simulator-link">
                                🔗 Alfamart Simulator
                            </a>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Quick Testing Steps -->
        <div class="section">
            <h2 class="section-title">🚀 Quick Testing Steps</h2>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem;">
                <div class="test-card">
                    <div class="card-header">1️⃣ Buat Booking</div>
                    <div class="card-info">
                        Login sebagai user dan buat booking kendaraan
                    </div>
                </div>
                <div class="test-card">
                    <div class="card-header">2️⃣ Pilih Metode Bayar</div>
                    <div class="card-info">
                        Klik "Bayar Sekarang" untuk buka Snap
                    </div>
                </div>
                <div class="test-card">
                    <div class="card-header">3️⃣ Gunakan Test Card</div>
                    <div class="card-info">
                        Pilih Credit Card dan gunakan nomor test di atas
                    </div>
                </div>
                <div class="test-card">
                    <div class="card-header">4️⃣ Verifikasi</div>
                    <div class="card-info">
                        Cek status booking berubah menjadi "confirmed"
                    </div>
                </div>
            </div>
        </div>

        <!-- Midtrans Dashboard -->
        <div class="section">
            <h2 class="section-title">📊 Midtrans Dashboard</h2>
            
            <div class="info-box">
                <strong>Monitor Transaksi:</strong><br>
                Semua transaksi testing dapat dipantau di:
                <div style="margin-top: 1rem;">
                    <a href="https://dashboard.sandbox.midtrans.com/" target="_blank" class="simulator-link">
                        🔗 Midtrans Sandbox Dashboard
                    </a>
                </div>
                <div style="margin-top: 1rem; color: rgba(255,255,255,0.6);">
                    Login dengan akun Midtrans Anda untuk melihat semua transaksi test, webhook logs, dan detail pembayaran.
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function copyToClipboard(text) {
    // Remove spaces from card number
    const cleanText = text.replace(/\s/g, '');
    
    navigator.clipboard.writeText(cleanText).then(() => {
        // Show notification
        const notification = document.createElement('div');
        notification.textContent = '✅ Card number copied!';
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: #00c853;
            color: white;
            padding: 1rem 2rem;
            border-radius: 6px;
            font-weight: 600;
            z-index: 10000;
            animation: slideIn 0.3s ease;
        `;
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.style.animation = 'slideOut 0.3s ease';
            setTimeout(() => notification.remove(), 300);
        }, 2000);
    });
}

// Accordion functionality
document.querySelectorAll('.accordion-header').forEach(header => {
    header.addEventListener('click', () => {
        const accordion = header.parentElement;
        accordion.classList.toggle('active');
    });
});
</script>

<style>
@keyframes slideIn {
    from {
        transform: translateX(400px);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

@keyframes slideOut {
    from {
        transform: translateX(0);
        opacity: 1;
    }
    to {
        transform: translateX(400px);
        opacity: 0;
    }
}
</style>

<?php include '../../php/includes/footer.php'; ?>
