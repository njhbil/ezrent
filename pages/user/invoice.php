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

try {
    $stmt = $pdo->prepare("
        SELECT 
            b.*,
            v.nama as vehicle_name,
            v.merek as vehicle_brand,
            v.model as vehicle_model,
            v.jenis as vehicle_type,
            v.plat_nomor as vehicle_plate,
            v.harga_per_hari as daily_rate,
            u.nama_lengkap as user_name,
            u.email as user_email,
            u.nomor_telepon as user_phone,
            u.alamat as user_address
        FROM bookings b
        JOIN vehicles v ON b.vehicle_id = v.id
        JOIN users u ON b.user_id = u.id
        WHERE b.id = ? AND b.user_id = ?
    ");
    $stmt->execute([$booking_id, $_SESSION['user_id']]);
    $booking = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$booking) {
        $error = "Invoice tidak ditemukan.";
    }
} catch (PDOException $e) {
    $error = "Terjadi kesalahan.";
}

if ($booking) {
    $start = new DateTime($booking['start_date']);
    $end = new DateTime($booking['end_date']);
    $duration = $start->diff($end)->days + 1;
    $created = new DateTime($booking['created_at']);
    $invoiceNumber = 'INV-' . date('Ymd', strtotime($booking['created_at'])) . '-' . str_pad($booking['id'], 4, '0', STR_PAD_LEFT);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice <?php echo isset($invoiceNumber) ? $invoiceNumber : ''; ?> - EzRent</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@200;300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #c41e3a;
            --primary-dark: #a01830;
            --gold: #d4af37;
            --dark: #0a0a0a;
        }
        
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(180deg, #0a0a0a 0%, #050505 100%);
            color: #fff;
            min-height: 100vh;
            line-height: 1.6;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        @keyframes shimmer {
            0% { background-position: -200% 0; }
            100% { background-position: 200% 0; }
        }
        
        .invoice-wrapper {
            max-width: 900px;
            margin: 0 auto;
            padding: 40px 20px;
            animation: fadeIn 0.8s ease-out;
        }
        
        .header-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            flex-wrap: wrap;
            gap: 1rem;
        }
        
        .back-link {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: rgba(255,255,255,0.5);
            text-decoration: none;
            font-size: 0.85rem;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            transition: color 0.3s;
        }
        
        .back-link:hover { color: var(--primary); }
        
        .action-buttons { display: flex; gap: 0.75rem; }
        
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            font-size: 0.7rem;
            font-weight: 600;
            letter-spacing: 0.15em;
            text-transform: uppercase;
            text-decoration: none;
            border: none;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: #fff;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(196, 30, 58, 0.3);
        }
        
        .btn-secondary {
            background: transparent;
            border: 1px solid rgba(255,255,255,0.2);
            color: #fff;
        }
        
        .btn-secondary:hover { background: rgba(255,255,255,0.05); }
        
        .invoice-container {
            background: linear-gradient(145deg, rgba(20,20,20,0.95) 0%, rgba(10,10,10,0.98) 100%);
            border: 1px solid rgba(255,255,255,0.08);
            position: relative;
            overflow: hidden;
        }
        
        .invoice-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary), var(--gold), var(--primary));
            background-size: 200% 100%;
            animation: shimmer 3s linear infinite;
        }
        
        .invoice-header {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 3rem;
            padding: 3rem;
            border-bottom: 1px solid rgba(255,255,255,0.06);
            position: relative;
        }
        
        .invoice-header::after {
            content: 'INVOICE';
            position: absolute;
            top: 50%;
            right: 3rem;
            transform: translateY(-50%);
            font-size: 6rem;
            font-weight: 700;
            color: rgba(255,255,255,0.02);
            letter-spacing: -0.05em;
            pointer-events: none;
        }
        
        @media (max-width: 768px) {
            .invoice-header { grid-template-columns: 1fr; gap: 2rem; }
            .invoice-header::after { display: none; }
        }
        
        .company-info { position: relative; z-index: 1; }
        
        .logo {
            font-size: 2rem;
            font-weight: 700;
            letter-spacing: -0.02em;
            margin-bottom: 1.5rem;
        }
        
        .logo span { color: var(--primary); }
        
        .company-details {
            font-size: 0.85rem;
            color: rgba(255,255,255,0.5);
            line-height: 1.8;
        }
        
        .invoice-meta {
            text-align: right;
            position: relative;
            z-index: 1;
        }
        
        @media (max-width: 768px) {
            .invoice-meta { text-align: left; }
        }
        
        .invoice-number {
            font-size: 0.65rem;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            color: var(--primary);
            margin-bottom: 0.5rem;
        }
        
        .invoice-id {
            font-size: 1.5rem;
            font-weight: 300;
            margin-bottom: 1.5rem;
        }
        
        .meta-item {
            display: flex;
            justify-content: flex-end;
            gap: 1rem;
            margin-bottom: 0.5rem;
            font-size: 0.85rem;
        }
        
        @media (max-width: 768px) {
            .meta-item { justify-content: flex-start; }
        }
        
        .meta-label { color: rgba(255,255,255,0.4); }
        .meta-value { color: #fff; font-weight: 500; }
        
        .status-badge {
            display: inline-block;
            padding: 0.5rem 1.5rem;
            font-size: 0.7rem;
            font-weight: 600;
            letter-spacing: 0.15em;
            text-transform: uppercase;
            margin-top: 1rem;
        }
        
        .status-paid {
            background: rgba(0, 204, 102, 0.15);
            border: 1px solid rgba(0, 204, 102, 0.3);
            color: #00cc66;
        }
        
        .status-unpaid {
            background: rgba(212, 175, 55, 0.15);
            border: 1px solid rgba(212, 175, 55, 0.3);
            color: var(--gold);
        }
        
        .invoice-parties {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 3rem;
            padding: 2.5rem 3rem;
            background: rgba(0,0,0,0.3);
        }
        
        @media (max-width: 768px) {
            .invoice-parties { grid-template-columns: 1fr; gap: 2rem; }
        }
        
        .party-section h4 {
            font-size: 0.65rem;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            color: rgba(255,255,255,0.4);
            margin-bottom: 1rem;
        }
        
        .party-name {
            font-size: 1.1rem;
            font-weight: 500;
            margin-bottom: 0.75rem;
        }
        
        .party-details {
            font-size: 0.85rem;
            color: rgba(255,255,255,0.5);
            line-height: 1.8;
        }
        
        .invoice-items { padding: 0 3rem; }
        
        .items-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .items-table th {
            text-align: left;
            padding: 1.25rem 1rem;
            font-size: 0.65rem;
            font-weight: 600;
            letter-spacing: 0.15em;
            text-transform: uppercase;
            color: rgba(255,255,255,0.4);
            border-bottom: 1px solid rgba(255,255,255,0.08);
        }
        
        .items-table th:last-child { text-align: right; }
        
        .items-table td {
            padding: 1.5rem 1rem;
            border-bottom: 1px solid rgba(255,255,255,0.04);
            vertical-align: top;
        }
        
        .items-table td:last-child { text-align: right; }
        
        .item-name {
            font-size: 1rem;
            font-weight: 500;
            margin-bottom: 0.25rem;
        }
        
        .item-desc {
            font-size: 0.8rem;
            color: rgba(255,255,255,0.4);
        }
        
        .item-price { font-weight: 500; font-size: 0.95rem; }
        
        .invoice-summary {
            padding: 2.5rem 3rem;
            display: flex;
            justify-content: flex-end;
        }
        
        .summary-box { width: 350px; max-width: 100%; }
        
        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 0.75rem 0;
            border-bottom: 1px solid rgba(255,255,255,0.04);
        }
        
        .summary-row.total {
            border-bottom: none;
            border-top: 2px solid var(--primary);
            margin-top: 0.5rem;
            padding-top: 1rem;
        }
        
        .summary-label { color: rgba(255,255,255,0.5); }
        .summary-value { font-weight: 500; }
        .summary-row.total .summary-label { color: #fff; font-weight: 500; font-size: 1rem; }
        .summary-row.total .summary-value { color: var(--primary); font-size: 1.5rem; font-weight: 700; }
        
        .invoice-footer {
            padding: 2.5rem 3rem;
            background: rgba(0,0,0,0.3);
            border-top: 1px solid rgba(255,255,255,0.06);
        }
        
        .footer-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 2rem;
        }
        
        @media (max-width: 768px) {
            .footer-grid { grid-template-columns: 1fr; }
        }
        
        .footer-item h5 {
            font-size: 0.65rem;
            letter-spacing: 0.15em;
            text-transform: uppercase;
            color: rgba(255,255,255,0.4);
            margin-bottom: 0.75rem;
        }
        
        .footer-item p {
            font-size: 0.85rem;
            color: rgba(255,255,255,0.7);
            line-height: 1.7;
        }
        
        .terms {
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid rgba(255,255,255,0.06);
        }
        
        .terms h5 {
            font-size: 0.65rem;
            letter-spacing: 0.15em;
            text-transform: uppercase;
            color: rgba(255,255,255,0.4);
            margin-bottom: 0.75rem;
        }
        
        .terms p {
            font-size: 0.8rem;
            color: rgba(255,255,255,0.4);
            line-height: 1.8;
        }
        
        @media print {
            body { background: #fff !important; color: #000 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .header-actions { display: none !important; }
            .invoice-wrapper { padding: 0; max-width: 100%; }
            .invoice-container { border: 1px solid #ddd !important; background: #fff !important; }
            .invoice-container::before { background: var(--primary) !important; }
            .logo, .party-name, .item-name, .meta-value { color: #000 !important; }
            .company-details, .party-details, .meta-label, .item-desc, .summary-label, .footer-item h5, .terms h5 { color: #666 !important; }
            .footer-item p, .terms p { color: #333 !important; }
            .invoice-header::after { color: rgba(0,0,0,0.05) !important; }
            .status-badge { border: 1px solid !important; }
        }
        
        .error-container { text-align: center; padding: 5rem 2rem; }
        
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
        
        .error-title { font-size: 1.5rem; font-weight: 300; margin-bottom: 0.75rem; }
        .error-text { color: rgba(255,255,255,0.5); margin-bottom: 2rem; }
    </style>
</head>
<body>

<div class="invoice-wrapper">
    <div class="header-actions">
        <a href="booking-detail.php?id=<?php echo $booking_id; ?>" class="back-link">
            <span>&larr;</span> Kembali ke Detail
        </a>
        <div class="action-buttons">
            <button onclick="window.print()" class="btn btn-primary">Cetak Invoice</button>
            <a href="my-bookings.php" class="btn btn-secondary">Pesanan Saya</a>
        </div>
    </div>

    <?php if ($error): ?>
    <div class="invoice-container">
        <div class="error-container">
            <div class="error-icon">!</div>
            <h2 class="error-title">Invoice Tidak Ditemukan</h2>
            <p class="error-text"><?php echo htmlspecialchars($error); ?></p>
            <a href="my-bookings.php" class="btn btn-primary">Kembali</a>
        </div>
    </div>
    <?php else: ?>
    
    <div class="invoice-container">
        <div class="invoice-header">
            <div class="company-info">
                <div class="logo">Ez<span>Rent</span></div>
                <div class="company-details">
                    Jl. Raya Rental No. 123<br>
                    Jakarta Selatan 12345<br>
                    Indonesia<br><br>
                    info@ezrent.id<br>
                    +62 21 1234 5678
                </div>
            </div>
            <div class="invoice-meta">
                <div class="invoice-number">Invoice Number</div>
                <div class="invoice-id"><?php echo $invoiceNumber; ?></div>
                <div class="meta-item">
                    <span class="meta-label">Tanggal Invoice:</span>
                    <span class="meta-value"><?php echo $created->format('d M Y'); ?></span>
                </div>
                <div class="meta-item">
                    <span class="meta-label">Jatuh Tempo:</span>
                    <span class="meta-value"><?php echo $start->format('d M Y'); ?></span>
                </div>
                <div class="status-badge <?php echo $booking['status'] !== 'pending' ? 'status-paid' : 'status-unpaid'; ?>">
                    <?php echo $booking['status'] !== 'pending' ? 'Lunas' : 'Belum Bayar'; ?>
                </div>
            </div>
        </div>
        
        <div class="invoice-parties">
            <div class="party-section">
                <h4>Ditagihkan Kepada</h4>
                <div class="party-name"><?php echo htmlspecialchars($booking['user_name'] ?? '-'); ?></div>
                <div class="party-details">
                    <?php echo htmlspecialchars($booking['user_email'] ?? '-'); ?><br>
                    <?php echo htmlspecialchars($booking['user_phone'] ?? '-'); ?><br>
                    <?php echo htmlspecialchars($booking['user_address'] ?? '-'); ?>
                </div>
            </div>
            <div class="party-section">
                <h4>Detail Pesanan</h4>
                <div class="party-name">Booking #<?php echo str_pad($booking['id'], 6, '0', STR_PAD_LEFT); ?></div>
                <div class="party-details">
                    Periode: <?php echo $start->format('d M Y'); ?> - <?php echo $end->format('d M Y'); ?><br>
                    Lokasi: <?php echo htmlspecialchars($booking['pickup_location'] ?? '-'); ?>
                </div>
            </div>
        </div>
        
        <div class="invoice-items">
            <table class="items-table">
                <thead>
                    <tr>
                        <th>Deskripsi</th>
                        <th>Qty</th>
                        <th>Harga</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <div class="item-name"><?php echo htmlspecialchars($booking['vehicle_name']); ?></div>
                            <div class="item-desc">
                                <?php echo htmlspecialchars($booking['vehicle_brand'] . ' ' . $booking['vehicle_model']); ?> 
                                | Plat: <?php echo htmlspecialchars($booking['vehicle_plate']); ?>
                            </div>
                        </td>
                        <td><?php echo $duration; ?> hari</td>
                        <td class="item-price">Rp <?php echo number_format($booking['daily_rate'] ?? ($booking['total_price'] / $duration), 0, ',', '.'); ?></td>
                        <td class="item-price">Rp <?php echo number_format($booking['total_price'] + ($booking['discount_amount'] ?? 0), 0, ',', '.'); ?></td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <div class="invoice-summary">
            <div class="summary-box">
                <div class="summary-row">
                    <span class="summary-label">Subtotal</span>
                    <span class="summary-value">Rp <?php echo number_format($booking['total_price'] + ($booking['discount_amount'] ?? 0), 0, ',', '.'); ?></span>
                </div>
                <?php if (!empty($booking['discount_code']) && $booking['discount_amount'] > 0): ?>
                <div class="summary-row" style="color: #22c55e;">
                    <span class="summary-label">
                        <i class="fas fa-tag"></i> Diskon (<?php echo strtoupper($booking['discount_code']); ?>)
                    </span>
                    <span class="summary-value">-Rp <?php echo number_format($booking['discount_amount'], 0, ',', '.'); ?></span>
                </div>
                <?php endif; ?>
                <div class="summary-row">
                    <span class="summary-label">Pajak (0%)</span>
                    <span class="summary-value">Rp 0</span>
                </div>
                <div class="summary-row total">
                    <span class="summary-label">Total Bayar</span>
                    <span class="summary-value">Rp <?php echo number_format($booking['total_price'], 0, ',', '.'); ?></span>
                </div>
            </div>
        </div>
        
        <div class="invoice-footer">
            <div class="footer-grid">
                <div class="footer-item">
                    <h5>Metode Pembayaran</h5>
                    <p>Transfer Bank<br>BCA: 1234567890<br>a.n. PT EzRent Indonesia</p>
                </div>
                <div class="footer-item">
                    <h5>Kontak</h5>
                    <p>WhatsApp: +62 812 3456 7890<br>Email: support@ezrent.id</p>
                </div>
                <div class="footer-item">
                    <h5>Jam Operasional</h5>
                    <p>Senin - Sabtu<br>08:00 - 20:00 WIB</p>
                </div>
            </div>
            <div class="terms">
                <h5>Syarat & Ketentuan</h5>
                <p>
                    Invoice ini merupakan bukti pemesanan yang sah. Pembayaran harus dilakukan sebelum tanggal jatuh tempo.
                    Pembatalan pesanan yang sudah dibayar akan dikenakan biaya administrasi sesuai kebijakan yang berlaku.
                </p>
            </div>
        </div>
    </div>
    
    <?php endif; ?>
</div>

</body>
</html>
