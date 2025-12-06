<?php
// pages/user/cancel-booking.php

session_start();
require_once '../../php/config/database.php';

// 1. Cek Login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// 2. Cek Parameter ID
if (!isset($_GET['id'])) {
    header("Location: my-bookings.php");
    exit();
}

$booking_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

try {
    // 3. Cek apakah pesanan ini benar milik user & statusnya masih 'pending'
    // Kita hanya izinkan batal jika status masih 'pending' (belum dikonfirmasi admin)
    $stmt = $pdo->prepare("SELECT id, status FROM bookings WHERE id = ? AND user_id = ?");
    $stmt->execute([$booking_id, $user_id]);
    $booking = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$booking) {
        // Pesanan tidak ditemukan atau bukan milik user ini
        echo "<script>alert('Pesanan tidak ditemukan!'); window.location='my-bookings.php';</script>";
        exit();
    }

    if ($booking['status'] !== 'pending') {
        // Jika sudah dikonfirmasi/aktif/selesai, tidak bisa batal sembarangan
        echo "<script>alert('Pesanan yang sudah diproses tidak dapat dibatalkan secara otomatis. Silakan hubungi Admin.'); window.location='my-bookings.php';</script>";
        exit();
    }

    // 4. Proses Pembatalan
    $pdo->beginTransaction();

    // Update status booking
    $stmt = $pdo->prepare("UPDATE bookings SET status = 'cancelled' WHERE id = ?");
    $stmt->execute([$booking_id]);

    // Update status payment jika ada (opsional, untuk kerapihan data)
    $stmt = $pdo->prepare("UPDATE payments SET status = 'failed' WHERE booking_id = ?");
    $stmt->execute([$booking_id]);

    $pdo->commit();

    // 5. Redirect Sukses
    header("Location: my-bookings.php?msg=cancelled");
    exit();

} catch (Exception $e) {
    $pdo->rollBack();
    die("Error: " . $e->getMessage());
}
?>