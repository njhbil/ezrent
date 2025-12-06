-- Fix Payment Integration for Midtrans
-- Run this to ensure smooth payment flow

-- 1. Make booking_id UNIQUE in payments table (prevent duplicates)
ALTER TABLE payments 
DROP INDEX IF EXISTS booking_id;

ALTER TABLE payments 
ADD UNIQUE INDEX booking_id (booking_id);

-- 2. Add midtrans_order_id to bookings if not exists
ALTER TABLE bookings 
ADD COLUMN IF NOT EXISTS midtrans_order_id VARCHAR(255) DEFAULT NULL;

CREATE INDEX IF NOT EXISTS idx_midtrans_order_id 
ON bookings(midtrans_order_id);

-- 3. Add payment_status to bookings for easier tracking
ALTER TABLE bookings 
ADD COLUMN IF NOT EXISTS payment_status ENUM('unpaid', 'pending', 'paid', 'failed', 'refunded') DEFAULT 'unpaid';

-- 4. Update payments table to support Midtrans payment types
ALTER TABLE payments 
MODIFY COLUMN method ENUM('transfer', 'e_wallet', 'cash', 'credit_card', 
    'bca_va', 'bni_va', 'bri_va', 'permata_va', 'mandiri_bill',
    'gopay', 'shopeepay', 'qris', 'dana', 'ovo',
    'indomaret', 'alfamart', 'cimb_clicks', 'danamon_online',
    'other') DEFAULT 'transfer';

-- 5. Clean up duplicate payments (keep only the latest one per booking)
DELETE p1 FROM payments p1
INNER JOIN payments p2 
WHERE p1.booking_id = p2.booking_id 
AND p1.id < p2.id;

-- 6. Add paid_at column if not exists
ALTER TABLE payments 
ADD COLUMN IF NOT EXISTS paid_at DATETIME DEFAULT NULL;

-- 7. Update existing confirmed bookings
UPDATE bookings 
SET payment_status = 'paid' 
WHERE status IN ('confirmed', 'active', 'completed') 
AND payment_status = 'unpaid';

UPDATE bookings 
SET payment_status = 'failed' 
WHERE status = 'cancelled' 
AND payment_status != 'refunded';

-- 8. Sync payment status from payments table to bookings
UPDATE bookings b
INNER JOIN payments p ON b.id = p.booking_id
SET b.payment_status = p.status
WHERE p.status IN ('paid', 'pending', 'failed');

-- Verification queries
-- Run these to verify everything is correct:

-- Check for duplicate payments
SELECT booking_id, COUNT(*) as count 
FROM payments 
GROUP BY booking_id 
HAVING count > 1;
-- Should return 0 rows

-- Check bookings without midtrans_order_id column
DESCRIBE bookings;
-- Should show midtrans_order_id column

-- Check payment methods
SELECT DISTINCT method FROM payments;
-- Should show all payment methods

-- Check payment status sync
SELECT 
    b.id,
    b.kode_booking,
    b.status as booking_status,
    b.payment_status,
    p.status as payment_status_from_table,
    p.method
FROM bookings b
LEFT JOIN payments p ON b.id = p.booking_id
WHERE b.status = 'pending'
LIMIT 10;

-- Success message
SELECT 'Database migration completed successfully!' as status;
