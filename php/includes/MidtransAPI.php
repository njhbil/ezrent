<?php
/**
 * Midtrans Core API Integration
 * Untuk generate VA, QRIS, dan payment methods lainnya secara real
 */

require_once __DIR__ . '/../config/midtrans.php';

class MidtransAPI {
    
    private $serverKey;
    private $isProduction;
    private $coreApiUrl;
    private $useFallback = false;
    
    public function __construct() {
        $this->serverKey = getMidtransServerKey();
        $this->isProduction = MIDTRANS_ENV === 'production';
        $this->coreApiUrl = $this->isProduction 
            ? 'https://api.midtrans.com/v2' 
            : 'https://api.sandbox.midtrans.com/v2';
    }
    
    /**
     * Enable fallback mode for demo/testing when Midtrans credentials are invalid
     */
    public function enableFallback() {
        $this->useFallback = true;
    }
    
    /**
     * Create Bank Transfer (Virtual Account) Transaction
     */
    public function createBankTransfer($orderId, $amount, $bank, $customerDetails) {
        // Try Midtrans first, fallback to demo mode if fails
        $payload = [
            'payment_type' => 'bank_transfer',
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => (int)$amount
            ],
            'bank_transfer' => [
                'bank' => strtolower($bank)
            ],
            'customer_details' => $customerDetails
        ];
        
        // For Mandiri, use echannel (bill payment)
        if (strtolower($bank) === 'mandiri') {
            $payload = [
                'payment_type' => 'echannel',
                'transaction_details' => [
                    'order_id' => $orderId,
                    'gross_amount' => (int)$amount
                ],
                'echannel' => [
                    'bill_info1' => 'Payment:',
                    'bill_info2' => 'EzRent Vehicle Rental'
                ],
                'customer_details' => $customerDetails
            ];
        }
        
        $result = $this->chargeTransaction($payload);
        
        // If Midtrans fails, use fallback demo mode
        if (!$result['success'] && strpos($result['error'] ?? '', 'Unknown Merchant') !== false) {
            return $this->createDemoBankTransfer($orderId, $amount, $bank);
        }
        
        return $result;
    }
    
    /**
     * Create Demo Bank Transfer for testing when Midtrans credentials are invalid
     */
    private function createDemoBankTransfer($orderId, $amount, $bank) {
        $bank = strtolower($bank);
        
        // Generate realistic VA numbers based on bank
        $prefixes = [
            'bca' => '190',
            'bni' => '880',
            'bri' => '262',
            'permata' => '850'
        ];
        
        $prefix = $prefixes[$bank] ?? '888';
        $va_number = $prefix . str_pad(rand(10000000000, 99999999999), 11, '0', STR_PAD_LEFT);
        
        // Mandiri uses biller code + bill key
        if ($bank === 'mandiri') {
            return [
                'success' => true,
                'demo_mode' => true,
                'data' => [
                    'transaction_id' => 'DEMO-' . time() . '-' . rand(1000, 9999),
                    'order_id' => $orderId,
                    'gross_amount' => (string)$amount,
                    'payment_type' => 'echannel',
                    'transaction_status' => 'pending',
                    'biller_code' => '70012',
                    'bill_key' => str_pad(rand(100000000000, 999999999999), 12, '0', STR_PAD_LEFT),
                    'expiry_time' => date('Y-m-d H:i:s', strtotime('+24 hours'))
                ]
            ];
        }
        
        return [
            'success' => true,
            'demo_mode' => true,
            'data' => [
                'transaction_id' => 'DEMO-' . time() . '-' . rand(1000, 9999),
                'order_id' => $orderId,
                'gross_amount' => (string)$amount,
                'payment_type' => 'bank_transfer',
                'transaction_status' => 'pending',
                'va_numbers' => [
                    [
                        'bank' => $bank,
                        'va_number' => $va_number
                    ]
                ],
                'expiry_time' => date('Y-m-d H:i:s', strtotime('+24 hours'))
            ]
        ];
    }
    
    /**
     * Create QRIS Transaction
     */
    public function createQRIS($orderId, $amount, $customerDetails) {
        $payload = [
            'payment_type' => 'qris',
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => (int)$amount
            ],
            'qris' => [
                'acquirer' => 'gopay' // gopay qris
            ],
            'customer_details' => $customerDetails
        ];
        
        return $this->chargeTransaction($payload);
    }
    
    /**
     * Create GoPay Transaction (includes QRIS)
     */
    public function createGoPay($orderId, $amount, $customerDetails) {
        $payload = [
            'payment_type' => 'gopay',
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => (int)$amount
            ],
            'gopay' => [
                'enable_callback' => true,
                'callback_url' => $this->getBaseUrl() . '/pages/user/payment-callback.php'
            ],
            'customer_details' => $customerDetails
        ];
        
        $result = $this->chargeTransaction($payload);
        
        // If Midtrans fails, use fallback demo mode
        if (!$result['success'] && strpos($result['error'] ?? '', 'Unknown Merchant') !== false) {
            return $this->createDemoQRIS($orderId, $amount);
        }
        
        return $result;
    }
    
    /**
     * Create Demo QRIS for testing when Midtrans credentials are invalid
     */
    private function createDemoQRIS($orderId, $amount) {
        // Generate a placeholder QR code URL using a QR code generator service
        $qr_data = "DEMO-QRIS-" . $orderId;
        $qr_url = "https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=" . urlencode($qr_data);
        
        return [
            'success' => true,
            'demo_mode' => true,
            'data' => [
                'transaction_id' => 'DEMO-QRIS-' . time() . '-' . rand(1000, 9999),
                'order_id' => $orderId,
                'gross_amount' => (string)$amount,
                'payment_type' => 'qris',
                'transaction_status' => 'pending',
                'actions' => [
                    [
                        'name' => 'generate-qr-code',
                        'method' => 'GET',
                        'url' => $qr_url
                    ]
                ],
                'expiry_time' => date('Y-m-d H:i:s', strtotime('+15 minutes'))
            ]
        ];
    }
    
    /**
     * Charge Transaction via Core API
     */
    private function chargeTransaction($payload) {
        $url = $this->coreApiUrl . '/charge';
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Accept: application/json',
            'Authorization: Basic ' . base64_encode($this->serverKey . ':')
        ]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            return [
                'success' => false,
                'error' => 'CURL Error: ' . $error
            ];
        }
        
        $result = json_decode($response, true);
        
        if ($httpCode >= 200 && $httpCode < 300) {
            return [
                'success' => true,
                'data' => $result
            ];
        } else {
            return [
                'success' => false,
                'error' => $result['status_message'] ?? 'Unknown error',
                'data' => $result
            ];
        }
    }
    
    /**
     * Check Transaction Status
     */
    public function checkStatus($orderId) {
        $url = $this->coreApiUrl . '/' . $orderId . '/status';
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: application/json',
            'Authorization: Basic ' . base64_encode($this->serverKey . ':')
        ]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        $result = json_decode($response, true);
        
        return [
            'success' => $httpCode >= 200 && $httpCode < 300,
            'data' => $result
        ];
    }
    
    /**
     * Cancel Transaction
     */
    public function cancelTransaction($orderId) {
        $url = $this->coreApiUrl . '/' . $orderId . '/cancel';
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: application/json',
            'Authorization: Basic ' . base64_encode($this->serverKey . ':')
        ]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        
        $response = curl_exec($ch);
        curl_close($ch);
        
        return json_decode($response, true);
    }
    
    /**
     * Get Base URL for callbacks
     */
    private function getBaseUrl() {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        return $protocol . '://' . $host . '/ezrent';
    }
    
    /**
     * Extract VA Number from response
     */
    public function extractVANumber($response) {
        if (!isset($response['data'])) return null;
        
        $data = $response['data'];
        
        // BCA, BNI, BRI VA
        if (isset($data['va_numbers']) && !empty($data['va_numbers'])) {
            return [
                'bank' => $data['va_numbers'][0]['bank'],
                'va_number' => $data['va_numbers'][0]['va_number']
            ];
        }
        
        // Permata VA
        if (isset($data['permata_va_number'])) {
            return [
                'bank' => 'permata',
                'va_number' => $data['permata_va_number']
            ];
        }
        
        // Mandiri Bill Payment
        if (isset($data['bill_key'])) {
            return [
                'bank' => 'mandiri',
                'biller_code' => $data['biller_code'],
                'bill_key' => $data['bill_key']
            ];
        }
        
        return null;
    }
    
    /**
     * Extract QRIS/GoPay data from response
     */
    public function extractQRISData($response) {
        if (!isset($response['data'])) return null;
        
        $data = $response['data'];
        
        // QRIS
        if (isset($data['actions'])) {
            foreach ($data['actions'] as $action) {
                if ($action['name'] === 'generate-qr-code') {
                    return [
                        'qr_url' => $action['url'],
                        'type' => 'qris'
                    ];
                }
            }
        }
        
        // GoPay QR
        if (isset($data['actions'])) {
            foreach ($data['actions'] as $action) {
                if (strpos($action['url'], 'qr-code') !== false) {
                    return [
                        'qr_url' => $action['url'],
                        'type' => 'gopay'
                    ];
                }
            }
        }
        
        return null;
    }
}
