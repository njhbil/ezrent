<?php
// Temporary migration script
$pdo = new PDO('mysql:host=localhost;dbname=ezrent', 'root', '');

try {
    // Add ktp_number column
    $pdo->exec('ALTER TABLE bookings ADD COLUMN ktp_number VARCHAR(20)');
    echo "✓ Added ktp_number column\n";
} catch (Exception $e) {
    echo "ktp_number column already exists\n";
}

try {
    // Add ktp_image column
    $pdo->exec('ALTER TABLE bookings ADD COLUMN ktp_image VARCHAR(255)');
    echo "✓ Added ktp_image column\n";
} catch (Exception $e) {
    echo "ktp_image column already exists\n";
}

// Verify
$stmt = $pdo->query('DESCRIBE bookings');
$columns = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
echo "\nBookings table columns:\n";
foreach ($columns as $col) {
    echo "  - $col\n";
}

echo "\n✓ Migration complete!\n";
?>
