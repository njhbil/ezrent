<?php
// Lokasi: pages/vehicle-detail.php
session_start();
require_once '../php/config/database.php';

// Cek ID
if (!isset($_GET['id'])) {
    header("Location: vehicles.php");
    exit();
}

$id = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM vehicles WHERE id = ?");
$stmt->execute([$id]);
$vehicle = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$vehicle) {
    header("Location: vehicles.php");
    exit();
}

// Decode Images
$images = json_decode($vehicle['images'], true);
$main_image = !empty($images) ? $images[0] : 'default.jpg';

$page_title = $vehicle['nama'];
include '../php/includes/header.php'; 
?>

<main style="background: #f8fafc; padding: 4rem 0;">
    <div class="container">
        <a href="vehicles.php" class="text-decoration-none text-muted mb-3 d-inline-block">
            <i class="fas fa-arrow-left me-2"></i> Kembali ke Katalog
        </a>
        
        <div class="row">
            <div class="col-md-6 mb-4">
                <div class="card border-0 shadow-sm overflow-hidden">
                    <img src="../assets/images/vehicles/<?php echo $main_image; ?>" class="img-fluid w-100" alt="<?php echo htmlspecialchars($vehicle['nama']); ?>" style="height: 400px; object-fit: cover;">
                </div>
            </div>

            <div class="col-md-6">
                <h1 class="fw-bold mb-2"><?php echo htmlspecialchars($vehicle['nama']); ?></h1>
                <p class="text-muted mb-4"><?php echo htmlspecialchars($vehicle['merek'] . ' ' . $vehicle['model'] . ' ' . $vehicle['tahun']); ?></p>

                <h2 class="text-primary fw-bold mb-4">
                    Rp <?php echo number_format($vehicle['harga_per_hari'], 0, ',', '.'); ?>
                    <span class="fs-6 text-muted fw-normal">/ hari</span>
                </h2>

                <div class="row g-3 mb-4">
                    <div class="col-6">
                        <div class="p-3 border rounded bg-white">
                            <small class="text-muted d-block">Transmisi</small>
                            <strong><?php echo ucfirst($vehicle['transmisi']); ?></strong>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-3 border rounded bg-white">
                            <small class="text-muted d-block">Kapasitas</small>
                            <strong><?php echo $vehicle['kapasitas']; ?> Orang</strong>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-3 border rounded bg-white">
                            <small class="text-muted d-block">Bahan Bakar</small>
                            <strong><?php echo ucfirst($vehicle['bahan_bakar']); ?></strong>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-3 border rounded bg-white">
                            <small class="text-muted d-block">Warna</small>
                            <strong><?php echo ucfirst($vehicle['warna']); ?></strong>
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <h5 class="fw-bold">Deskripsi</h5>
                    <p class="text-muted" style="line-height: 1.8;">
                        <?php echo nl2br(htmlspecialchars($vehicle['deskripsi'])); ?>
                    </p>
                </div>

                <div class="d-grid gap-2">
                    <?php if ($vehicle['status'] == 'tersedia'): ?>
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <a href="user/booking-process.php?vehicle_id=<?php echo $vehicle['id']; ?>" class="btn btn-primary btn-lg fw-bold shadow-sm">
                                Sewa Sekarang
                            </a>
                        <?php else: ?>
                            <a href="login.php?redirect=vehicle&id=<?php echo $vehicle['id']; ?>" class="btn btn-primary btn-lg fw-bold shadow-sm">
                                Login untuk Menyewa
                            </a>
                        <?php endif; ?>
                    <?php else: ?>
                        <button class="btn btn-secondary btn-lg" disabled>
                            Maaf, Sedang Disewa
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include '../php/includes/footer.php'; ?>