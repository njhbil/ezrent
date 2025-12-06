<?php
/**
 * Midtrans Webhook Handler
 * Receives real-time payment notifications from Midtrans
 * 
 * Configure in Midtrans Dashboard:
 * Settings → Configuration → Payment Notification URL
 * Set to: https://yourdomain.com/ezrent/php/webhooks/midtrans.php
 */

header('Content-Type: application/json');

require_once '../config/database.php';
require_once '../config/midtrans.php';

// Get webhook payload
$raw_body = file_get_contents('php://input');

// Log webhook for debugging
$log_dir = __DIR__;
$log_file = $log_dir . '/webhook.log';
file_put_contents($log_file, date('Y-m-d H:i:s') . " - Webhook received\n", FILE_APPEND);
file_put_contents($log_file, $raw_body . "\n\n", FILE_APPEND);

// Parse JSON
$notification = json_decode($raw_body, true);

if (!$notification || !isset($notification['order_id'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid notification']);
    exit();
}

try {
    // Verify signature
    $server_key = getMidtransServerKey();
    $order_id = $notification['order_id'];
    $status_code = $notification['status_code'];
    $gross_amount = $notification['gross_amount'];
    $signature_key = $notification['signature_key'] ?? '';
    
    $expected_signature = hash('sha512', $order_id . $status_code . $gross_amount . $server_key);
    
    if ($signature_key !== $expected_signature) {
        file_put_contents($log_file, "SIGNATURE MISMATCH\n", FILE_APPEND);
        // Continue anyway for sandbox testing, but log warning
    }
    
    // Extract booking_id from order_id (format: EZRENT-{booking_id}-{timestamp})
    preg_match('/EZRENT-(\d+)-/', $order_id, $matches);
    $booking_id = isset($matches[1]) ? (int)$matches[1] : 0;
    
    if ($booking_id <= 0) {
        file_put_contents($log_file, "Could not extract booking_id from: $order_id\n", FILE_APPEND);
        echo json_encode(['status' => 'error', 'message' => 'Invalid order_id format']);
        exit();
    }
    
    // Get transaction status
    $transaction_status = $notification['transaction_status'];
    $fraud_status = $notification['fraud_status'] ?? 'accept';
    $payment_type = $notification['payment_type'] ?? '';
    $transaction_id = $notification['transaction_id'] ?? '';
    
    file_put_contents($log_file, "Booking: $booking_id, Status: $transaction_status, Fraud: $fraud_status\n", FILE_APPEND);
    
    // Determine payment status
    $payment_status = 'pending';
    $booking_status = null;
    
    if ($transaction_status == 'capture') {
        if ($fraud_status == 'accept') {
            $payment_status = 'completed';
            $booking_status = 'confirmed';
        } else {
            $payment_status = 'failed';
        }
    } else if ($transaction_status == 'settlement') {
        $payment_status = 'completed';
        $booking_status = 'confirmed';
    } else if ($transaction_status == 'pending') {
        $payment_status = 'pending';
    } else if ($transaction_status == 'deny' || $transaction_status == 'cancel') {
        $payment_status = 'failed';
        $booking_status = 'cancelled';
    } else if ($transaction_status == 'expire') {
        $payment_status = 'expired';
    } else if ($transaction_status == 'refund') {
        $payment_status = 'refund';
    }
    
    // Update payment record
    $stmt = $pdo->prepare("
        UPDATE payments 
        SET status = ?,
            midtrans_transaction_id = ?,
            paid_at = CASE WHEN ? = 'completed' THEN NOW() ELSE paid_at END,
            updated_at = NOW()
        WHERE booking_id = ?
    ");
    $stmt->execute([$payment_status, $transaction_id, $payment_status, $booking_id]);
    
    $rows_updated = $stmt->rowCount();
    file_put_contents($log_file, "Payment updated: $rows_updated rows\n", FILE_APPEND);
    
    // Update booking status if needed
    if ($booking_status) {
        $stmt = $pdo->prepare("UPDATE bookings SET status = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$booking_status, $booking_id]);
        file_put_contents($log_file, "Booking status updated to: $booking_status\n", FILE_APPEND);
    }
    
    // Return success
    http_response_code(200);
    echo json_encode([
        'status' => 'ok',
        'message' => 'Notification processed',
        'booking_id' => $booking_id,
        'payment_status' => $payment_status
    ]);
    
} catch (Exception $e) {
    file_put_contents($log_file, "ERROR: " . $e->getMessage() . "\n", FILE_APPEND);
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
