<?php
/**
 * Record Payment API
 * This API should be called when payment is confirmed via webhook or manual confirmation
 * Used for Midtrans webhooks and manual payment verification
 */

header('Content-Type: application/json');

require_once '../config/database.php';

// Handle both POST and webhook calls
$input = file_get_contents('php://input');
$data = !empty($input) ? json_decode($input, true) : $_POST;

$booking_id = isset($data['booking_id']) ? (int)$data['booking_id'] : 0;
$payment_method = isset($data['payment_method']) ? $data['payment_method'] : '';
$payment_code = isset($data['payment_code']) ? $data['payment_code'] : '';
$amount = isset($data['amount']) ? (float)$data['amount'] : 0;
$reference_number = isset($data['reference_number']) ? $data['reference_number'] : '';

if ($booking_id <= 0) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid booking ID']);
    exit();
}

try {
    // Get booking details
    $stmt = $pdo->prepare("
        SELECT b.id, b.user_id, b.total_price, b.status
        FROM bookings b
        WHERE b.id = ?
    ");
    $stmt->execute([$booking_id]);
    $booking = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$booking) {
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => 'Booking not found']);
        exit();
    }
    
    // Check if payment amount matches
    if ($amount > 0 && $amount != $booking['total_price']) {
        http_response_code(400);
        echo json_encode([
            'status' => 'error',
            'message' => 'Payment amount mismatch',
            'expected' => $booking['total_price'],
            'received' => $amount
        ]);
        exit();
    }
    
    // Check if payment already exists
    $check_stmt = $pdo->prepare("
        SELECT id, status FROM payments WHERE booking_id = ?
    ");
    $check_stmt->execute([$booking_id]);
    $existing_payment = $check_stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($existing_payment && $existing_payment['status'] === 'completed') {
        echo json_encode([
            'status' => 'success',
            'message' => 'Payment already confirmed',
            'payment_id' => $existing_payment['id']
        ]);
        exit();
    }
    
    // Record or update payment
    if ($existing_payment) {
        $update_stmt = $pdo->prepare("
            UPDATE payments 
            SET status = 'completed',
                payment_method = ?,
                payment_code = ?,
                reference_number = ?,
                amount = ?,
                paid_at = NOW(),
                updated_at = NOW()
            WHERE booking_id = ?
        ");
        $update_stmt->execute([$payment_method, $payment_code, $reference_number, $amount ?: $booking['total_price'], $booking_id]);
        $payment_id = $existing_payment['id'];
    } else {
        $insert_stmt = $pdo->prepare("
            INSERT INTO payments (
                booking_id, 
                user_id, 
                payment_method, 
                payment_code,
                reference_number, 
                amount, 
                status, 
                paid_at
            ) VALUES (?, ?, ?, ?, ?, ?, 'completed', NOW())
        ");
        $insert_stmt->execute([
            $booking_id,
            $booking['user_id'],
            $payment_method,
            $payment_code,
            $reference_number,
            $amount ?: $booking['total_price']
        ]);
        $payment_id = $pdo->lastInsertId();
    }
    
    // Update booking status to confirmed
    $booking_update = $pdo->prepare("
        UPDATE bookings 
        SET status = 'confirmed', 
            payment_method = ?,
            updated_at = NOW()
        WHERE id = ? AND status = 'pending'
    ");
    $booking_update->execute([$payment_method, $booking_id]);
    
    echo json_encode([
        'status' => 'success',
        'message' => 'Payment recorded successfully',
        'payment_id' => $payment_id,
        'booking_status' => 'confirmed'
    ]);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
    exit();
}
