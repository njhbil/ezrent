<?php


session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

require_once '../config/database.php';

$booking_id = isset($_POST['booking_id']) ? (int)$_POST['booking_id'] : 0;
$user_id = $_SESSION['user_id'];

if ($booking_id <= 0) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid booking ID']);
    exit();
}

try {
    // Get booking details
    $stmt = $pdo->prepare("
        SELECT b.id, b.status, b.payment_method, b.total_price, 
               COUNT(p.id) as payment_confirmed
        FROM bookings b
        LEFT JOIN payments p ON b.id = p.booking_id AND p.status = 'completed'
        WHERE b.id = ? AND b.user_id = ?
        GROUP BY b.id
    ");
    $stmt->execute([$booking_id, $user_id]);
    $booking = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$booking) {
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => 'Booking not found']);
        exit();
    }
    
    // Check if payment has been confirmed
    if ($booking['payment_confirmed'] > 0) {
        // Payment confirmed - update booking status to confirmed
        $update_stmt = $pdo->prepare("
            UPDATE bookings 
            SET status = 'confirmed', updated_at = NOW()
            WHERE id = ? AND status = 'pending'
        ");
        $update_stmt->execute([$booking_id]);
            // Also update booking.payment_status to paid
            try {
                $pdo->prepare("UPDATE bookings SET payment_status = 'paid', updated_at = NOW() WHERE id = ?")->execute([$booking_id]);
            } catch (Exception $e) {}
        
        echo json_encode([
            'status' => 'success',
            'payment_status' => 'confirmed',
            'booking_status' => 'confirmed',
            'message' => 'Pembayaran berhasil dikonfirmasi'
        ]);
    } else {
        // Check if payment is pending (wait max 5 minutes)
        echo json_encode([
            'status' => 'pending',
            'payment_status' => 'pending',
            'booking_status' => $booking['status'],
            'message' => 'Menunggu konfirmasi pembayaran...'
        ]);
    }
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Database error']);
    exit();
}
