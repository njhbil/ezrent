<?php
/**
 * Check Payment Status API
 * Queries Midtrans for real-time payment status
 */
header('Content-Type: application/json');

session_start();
require_once '../config/database.php';
require_once '../includes/MidtransAPI.php';

$booking_id = isset($_GET['booking_id']) ? (int)$_GET['booking_id'] : 0;

if ($booking_id <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid booking ID']);
    exit();
}

try {
    // Get payment record
    $stmt = $pdo->prepare("
        SELECT p.*, b.status as booking_status
        FROM payments p
        JOIN bookings b ON p.booking_id = b.id
        WHERE p.booking_id = ?
    ");
    $stmt->execute([$booking_id]);
    $payment = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$payment) {
        echo json_encode(['status' => 'not_found', 'message' => 'Payment not found']);
        exit();
    }
    
    // If already completed, return immediately
    if ($payment['status'] === 'completed') {
        echo json_encode([
            'status' => 'completed',
            'booking_status' => $payment['booking_status'],
            'message' => 'Payment already confirmed'
        ]);
        exit();
    }
    
    // Check with Midtrans if we have order ID
    if (!empty($payment['midtrans_order_id'])) {
        $midtrans = new MidtransAPI();
        $result = $midtrans->checkStatus($payment['midtrans_order_id']);
        
        if ($result['success'] && isset($result['data']['transaction_status'])) {
            $midtrans_status = $result['data']['transaction_status'];
            
            // Map Midtrans status
            $new_status = 'pending';
            if (in_array($midtrans_status, ['settlement', 'capture'])) {
                $new_status = 'completed';
            } elseif ($midtrans_status === 'pending') {
                $new_status = 'pending';
            } elseif (in_array($midtrans_status, ['deny', 'cancel', 'expire'])) {
                $new_status = $midtrans_status === 'expire' ? 'expired' : 'failed';
            }
            
            // Update payment status if changed
            if ($new_status !== $payment['status']) {
                $update = $pdo->prepare("
                    UPDATE payments 
                    SET status = ?, 
                        paid_at = CASE WHEN ? = 'completed' THEN NOW() ELSE paid_at END,
                        updated_at = NOW()
                    WHERE booking_id = ?
                ");
                $update->execute([$new_status, $new_status, $booking_id]);
                
                // Update booking status if payment completed
                if ($new_status === 'completed') {
                    $pdo->prepare("UPDATE bookings SET status = 'confirmed', updated_at = NOW() WHERE id = ?")
                        ->execute([$booking_id]);
                }
            }
            
            echo json_encode([
                'status' => $new_status,
                'midtrans_status' => $midtrans_status,
                'booking_status' => $new_status === 'completed' ? 'confirmed' : $payment['booking_status'],
                'message' => 'Status updated from Midtrans'
            ]);
            exit();
        }
    }
    
    // Return current status from database
    echo json_encode([
        'status' => $payment['status'],
        'booking_status' => $payment['booking_status'],
        'message' => 'Current status from database'
    ]);
    
} catch (Exception $e) {
    error_log('Check payment status error: ' . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to check status'
    ]);
}
