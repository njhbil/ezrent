<?php

session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

// Only allow in development
$allowed_ips = ['127.0.0.1', '::1', 'localhost'];
$client_ip = $_SERVER['REMOTE_ADDR'] ?? '';

// Check if this is a local request
$is_local = in_array($client_ip, $allowed_ips) || strpos($client_ip, '192.168.') === 0;

if (!$is_local) {
    http_response_code(403);
    echo json_encode(['error' => 'Simulator only available in development']);
    exit();
}

$booking_id = isset($_POST['booking_id']) ? (int)$_POST['booking_id'] : 0;
$action = isset($_POST['action']) ? $_POST['action'] : 'pay';

if ($booking_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid booking ID']);
    exit();
}

try {
    // Check if payment exists
    $stmt = $pdo->prepare("SELECT * FROM payments WHERE booking_id = ?");
    $stmt->execute([$booking_id]);
    $payment = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$payment) {
        echo json_encode(['success' => false, 'message' => 'Payment record not found']);
        exit();
    }
    
    if ($action === 'pay') {
        // Simulate successful payment
        $pdo->prepare("
            UPDATE payments 
            SET status = 'completed', 
                paid_at = NOW(),
                updated_at = NOW()
            WHERE booking_id = ?
        ")->execute([$booking_id]);
        
        // Update booking status
            $pdo->prepare("
                UPDATE bookings 
                SET status = 'confirmed',
                    updated_at = NOW()
                WHERE id = ?
            ")->execute([$booking_id]);

            // Set vehicle as rented ('disewa')
            $stmt = $pdo->prepare("SELECT vehicle_id FROM bookings WHERE id = ?");
            $stmt->execute([$booking_id]);
            $b = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($b && !empty($b['vehicle_id'])) {
                $pdo->prepare("UPDATE vehicles SET status = 'disewa', updated_at = NOW() WHERE id = ?")->execute([$b['vehicle_id']]);
            }
        
        echo json_encode([
            'success' => true,
            'message' => 'Payment simulated successfully!',
            'payment_status' => 'completed',
            'booking_status' => 'confirmed',
            'timestamp' => date('Y-m-d H:i:s')
        ]);
        
    } elseif ($action === 'expire') {
        // Simulate expired payment
        $pdo->prepare("
            UPDATE payments 
            SET status = 'expired',
                updated_at = NOW()
            WHERE booking_id = ?
        ")->execute([$booking_id]);
        
            // Ensure vehicle becomes available again
            $stmt = $pdo->prepare("SELECT vehicle_id FROM bookings WHERE id = ?");
            $stmt->execute([$booking_id]);
            $b = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($b && !empty($b['vehicle_id'])) {
                $pdo->prepare("UPDATE vehicles SET status = 'tersedia', updated_at = NOW() WHERE id = ?")->execute([$b['vehicle_id']]);
            }

        echo json_encode([
            'success' => true,
            'message' => 'Payment expired',
            'payment_status' => 'expired'
        ]);
        
    } elseif ($action === 'reset') {
        // Reset to pending
        $pdo->prepare("
            UPDATE payments 
            SET status = 'pending',
                paid_at = NULL,
                updated_at = NOW()
            WHERE booking_id = ?
        ")->execute([$booking_id]);
        
        $pdo->prepare("
            UPDATE bookings 
            SET status = 'pending',
                updated_at = NOW()
            WHERE id = ?
        ")->execute([$booking_id]);
            // Make vehicle available again
            $stmt = $pdo->prepare("SELECT vehicle_id FROM bookings WHERE id = ?");
            $stmt->execute([$booking_id]);
            $b = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($b && !empty($b['vehicle_id'])) {
                $pdo->prepare("UPDATE vehicles SET status = 'tersedia', updated_at = NOW() WHERE id = ?")->execute([$b['vehicle_id']]);
            }
        
        echo json_encode([
            'success' => true,
            'message' => 'Payment reset to pending',
            'payment_status' => 'pending'
        ]);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
