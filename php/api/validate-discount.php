<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

require_once '../config/database.php';

$code = strtoupper(trim($_POST['code'] ?? ''));
$bookingAmount = floatval($_POST['booking_amount'] ?? 0);
$userId = $_SESSION['user_id'];

if (empty($code)) {
    echo json_encode(['success' => false, 'message' => 'Kode diskon tidak boleh kosong']);
    exit();
}

if ($bookingAmount <= 0) {
    echo json_encode(['success' => false, 'message' => 'Nilai booking tidak valid']);
    exit();
}

try {
    // Get discount details
    $stmt = $pdo->prepare("
        SELECT * FROM discount_codes 
        WHERE code = ? AND is_active = 1
    ");
    $stmt->execute([$code]);
    $discount = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$discount) {
        echo json_encode(['success' => false, 'message' => 'Kode diskon tidak valid atau sudah tidak aktif']);
        exit();
    }

    // Check if discount has started
    if ($discount['start_date'] && strtotime($discount['start_date']) > time()) {
        echo json_encode([
            'success' => false, 
            'message' => 'Kode diskon belum dapat digunakan. Mulai: ' . date('d M Y H:i', strtotime($discount['start_date']))
        ]);
        exit();
    }

    // Check if discount has expired
    if ($discount['end_date'] && strtotime($discount['end_date']) < time()) {
        echo json_encode([
            'success' => false, 
            'message' => 'Kode diskon sudah kadaluarsa pada ' . date('d M Y', strtotime($discount['end_date']))
        ]);
        exit();
    }

    // Check minimum booking amount
    if ($bookingAmount < $discount['min_booking_amount']) {
        echo json_encode([
            'success' => false, 
            'message' => 'Minimal pemesanan Rp ' . number_format($discount['min_booking_amount'], 0, ',', '.') . ' untuk menggunakan kode ini'
        ]);
        exit();
    }

    // Check usage limit
    if ($discount['usage_limit']) {
        if ($discount['used_count'] >= $discount['usage_limit']) {
            echo json_encode([
                'success' => false, 
                'message' => 'Kode diskon sudah mencapai batas penggunaan'
            ]);
            exit();
        }
    }

    // Calculate discount amount
    $discountAmount = 0;
    if ($discount['discount_type'] === 'percentage') {
        $discountAmount = ($bookingAmount * $discount['discount_value']) / 100;
    } else {
        $discountAmount = $discount['discount_value'];
    }

    // Apply max discount limit
    if ($discount['max_discount_amount'] && $discountAmount > $discount['max_discount_amount']) {
        $discountAmount = $discount['max_discount_amount'];
    }

    // Ensure discount doesn't exceed booking amount
    if ($discountAmount > $bookingAmount) {
        $discountAmount = $bookingAmount;
    }

    $finalAmount = $bookingAmount - $discountAmount;

    echo json_encode([
        'success' => true,
        'message' => 'Kode diskon berhasil diterapkan!',
        'discount_id' => $discount['id'],
        'discount_code' => $discount['code'],
        'discount_type' => $discount['discount_type'],
        'discount_value' => $discount['discount_value'],
        'discount_amount' => $discountAmount,
        'original_amount' => $bookingAmount,
        'final_amount' => $finalAmount,
        'savings' => $discountAmount,
        'description' => $discount['description']
    ]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan sistem']);
}
