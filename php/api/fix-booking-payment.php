<?php
/**
 * One-off fixer: force payment record and mark booking as paid.
 * Usage: php/api/fix-booking-payment.php?kode=EZR-20251214-7690
 */
header('Content-Type: application/json');

require_once '../config/database.php';

$kode = isset($_GET['kode']) ? trim($_GET['kode']) : '';
if (!$kode) {
    echo json_encode(['status' => 'error', 'message' => 'Missing kode parameter']);
    exit();
}

try {
    // find booking
    $stmt = $pdo->prepare("SELECT * FROM bookings WHERE kode_booking = ? LIMIT 1");
    $stmt->execute([$kode]);
    $booking = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$booking) {
        echo json_encode(['status' => 'error', 'message' => 'Booking not found']);
        exit();
    }

    $booking_id = (int)$booking['id'];

    // insert or update payment
    $pstmt = $pdo->prepare("SELECT * FROM payments WHERE booking_id = ? LIMIT 1");
    $pstmt->execute([$booking_id]);
    $payment = $pstmt->fetch(PDO::FETCH_ASSOC);

    if (!$payment) {
        $ins = $pdo->prepare("INSERT INTO payments (booking_id, user_id, payment_method, payment_code, midtrans_order_id, midtrans_transaction_id, amount, status, paid_at, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW(), NOW())");
        $tranId = 'manual-' . time();
        $ins->execute([
            $booking_id,
            $booking['user_id'] ?? null,
            'manual',
            null,
            $booking['kode_booking'],
            $tranId,
            $booking['total_price'] ?? 0,
            'completed'
        ]);
        $payment_id = $pdo->lastInsertId();
        $inserted = true;
    } else {
        $upd = $pdo->prepare("UPDATE payments SET status = 'completed', paid_at = COALESCE(paid_at, NOW()), updated_at = NOW(), amount = COALESCE(amount, ?) , midtrans_order_id = COALESCE(midtrans_order_id, ?) WHERE id = ?");
        $upd->execute([$booking['total_price'] ?? 0, $booking['kode_booking'], $payment['id']]);
        $payment_id = $payment['id'];
        $inserted = false;
    }

    // update booking payment_status and status
    $pdo->prepare("UPDATE bookings SET payment_status = 'paid', status = 'confirmed', updated_at = NOW() WHERE id = ?")->execute([$booking_id]);

    // update vehicle status to disewa
    if (!empty($booking['vehicle_id'])) {
        $pdo->prepare("UPDATE vehicles SET status = 'disewa' WHERE id = ?")->execute([$booking['vehicle_id']]);
    }

    echo json_encode([
        'status' => 'ok',
        'message' => 'Booking marked as paid',
        'booking_id' => $booking_id,
        'payment_id' => $payment_id,
        'inserted_payment' => $inserted
    ]);
    exit();

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    exit();
}

?>
