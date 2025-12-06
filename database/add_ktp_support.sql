-- Add KTP upload to bookings table
ALTER TABLE bookings 
ADD COLUMN IF NOT EXISTS ktp_image VARCHAR(255) DEFAULT NULL,
ADD COLUMN IF NOT EXISTS ktp_number VARCHAR(20) DEFAULT NULL;

-- Create directory untuk KTP if needed (manual: create folder php/uploads/ktp/)
