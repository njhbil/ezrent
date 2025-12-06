<?php
/**
 * Midtrans Snap Token Generator API
 * 
 * Endpoint untuk generate Snap Token untuk pembayaran
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

session_start();

// Check login
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

require_once '../config/database.php';
require_once '../config/midtrans.php';

// Get input
$input = json_decode(file_get_contents('php://input'), true);
$booking_id = isset($input['booking_id']) ? (int)$input['booking_id'] : 0;

if ($booking_id <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid booking ID']);
    exit();
}

try {
    // Get booking data
    $stmt = $pdo->prepare("
        SELECT 
            b.*,
            v.nama as vehicle_name,
            v.merek as vehicle_brand,
            v.model as vehicle_model,
            u.nama_lengkap as customer_name,
            u.email as customer_email,
            u.nomor_telepon as customer_phone
        FROM bookings b
        JOIN vehicles v ON b.vehicle_id = v.id
        JOIN users u ON b.user_id = u.id
        WHERE b.id = ? AND b.user_id = ? AND b.status = 'pending'
    ");
    $stmt->execute([$booking_id, $_SESSION['user_id']]);
    $booking = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$booking) {
        http_response_code(404);
        echo json_encode(['error' => 'Booking not found or already paid']);
        exit();
    }
    
    // Generate unique order ID
    $order_id = 'EZRENT-' . $booking['kode_booking'] . '-' . time();
    
    // Calculate duration
    $start = new DateTime($booking['start_date']);
    $end = new DateTime($booking['end_date']);
    $duration = $start->diff($end)->days + 1;
    
    // Prepare Midtrans transaction data
    $transaction_data = [
        'transaction_details' => [
            'order_id' => $order_id,
            'gross_amount' => (int)$booking['total_price']
        ],
        'customer_details' => [
            'first_name' => $booking['customer_name'],
            'email' => $booking['customer_email'],
            'phone' => $booking['customer_phone'] ?? ''
        ],
        'item_details' => [
            [
                'id' => 'VEHICLE-' . $booking['vehicle_id'],
                'price' => (int)($booking['total_price'] / $duration),
                'quantity' => $duration,
                'name' => substr($booking['vehicle_brand'] . ' ' . $booking['vehicle_name'], 0, 50)
            ]
        ],
        'callbacks' => [
            'finish' => (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/pages/user/booking-detail.php?id=' . $booking_id . '&payment=success'
        ],
        'expiry' => [
            'unit' => 'hours',
            'duration' => 24
        ],
        // Enable all payment methods
        'enabled_payments' => [
            'credit_card',
            'bca_va',
            'bni_va', 
            'bri_va',
            'permata_va',
            'other_va',
            'gopay',
            'shopeepay',
            'qris',
            'dana',
            'ovo',
            'indomaret',
            'alfamart'
        ],
        'credit_card' => [
            'secure' => true
        ]
    ];
    
    // Save order_id to booking for webhook reference
    // Try to update midtrans_order_id column (may not exist yet)
    try {
        $stmt = $pdo->prepare("UPDATE bookings SET midtrans_order_id = ? WHERE id = ?");
        $stmt->execute([$order_id, $booking_id]);
    } catch (PDOException $e) {
        // Column might not exist yet - that's okay, continue
        error_log("Midtrans: Could not update midtrans_order_id column - " . $e->getMessage());
    }
    
    // Call Midtrans API
    $curl = curl_init();
    
    curl_setopt_array($curl, [
        CURLOPT_URL => getMidtransBaseUrl() . '/transactions',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => json_encode($transaction_data),
        CURLOPT_HTTPHEADER => [
            'Accept: application/json',
            'Content-Type: application/json',
            'Authorization: Basic ' . base64_encode(getMidtransServerKey() . ':')
        ],
    ]);
    
    $response = curl_exec($curl);
    $err = curl_error($curl);
    $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    
    curl_close($curl);
    
    if ($err) {
        throw new Exception('cURL Error: ' . $err);
    }
    
    $result = json_decode($response, true);
    
    if ($http_code !== 201 && $http_code !== 200) {
        throw new Exception('Midtrans Error: ' . ($result['error_messages'][0] ?? 'Unknown error'));
    }
    
    // Return snap token
    echo json_encode([
        'success' => true,
        'snap_token' => $result['token'],
        'redirect_url' => $result['redirect_url'],
        'order_id' => $order_id
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => $e->getMessage()
    ]);
}
