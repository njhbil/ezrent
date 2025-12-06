<?php
session_start();

// 1. Cek Login Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

require_once '../../php/config/database.php';

// 2. Cek Parameter ID
if (!isset($_GET['id'])) {
    header("Location: vehicles.php");
    exit();
}

$id = $_GET['id'];

// 3. Ambil Data Kendaraan Lama
$stmt = $pdo->prepare("SELECT * FROM vehicles WHERE id = ?");
$stmt->execute([$id]);
$vehicle = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$vehicle) {
    echo "<script>alert('Data tidak ditemukan!'); window.location='vehicles.php';</script>";
    exit();
}

// Helper: Decode Images
$images = json_decode($vehicle['images'], true);
$current_image = !empty($images) ? $images[0] : 'default.jpg';

// --- FUNGSI UPLOAD (Sama dengan vehicles.php) ---
function uploadImage($file) {
    $targetDir = "../../assets/images/vehicles/";
    if (!file_exists($targetDir)) mkdir($targetDir, 0777, true);

    $fileName = basename($file["name"]);
    $targetFilePath = $targetDir . $fileName;
    $fileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));
    
    $allowTypes = array('jpg', 'png', 'jpeg', 'gif', 'webp');
    if (in_array($fileType, $allowTypes)) {
        if ($file["size"] > 5000000) return ['status' => false, 'msg' => 'Ukuran file max 5MB.'];
        
        $newFileName = uniqid() . '.' . $fileType;
        if (move_uploaded_file($file["tmp_name"], $targetDir . $newFileName)) {
            return ['status' => true, 'fileName' => $newFileName];
        }
    }
    return ['status' => false, 'msg' => 'Format file tidak didukung atau gagal upload.'];
}

// 4. Handle Form Submit (Update Data)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_vehicle'])) {
    
    $final_image = $current_image; // Default pakai gambar lama

    // Cek jika ada gambar baru diupload
    if (!empty($_FILES["image"]["name"])) {
        $uploadResult = uploadImage($_FILES["image"]);
        if ($uploadResult['status']) {
            $final_image = $uploadResult['fileName'];
            
            // Hapus gambar lama jika bukan default
            if ($current_image !== 'default.jpg' && file_exists("../../assets/images/vehicles/" . $current_image)) {
                unlink("../../assets/images/vehicles/" . $current_image);
            }
        } else {
            $error_msg = $uploadResult['msg'];
        }
    }

    if (!isset($error_msg)) {
        try {
            $images_json = json_encode([$final_image]);
            
            $sql = "UPDATE vehicles SET 
                    nama=?, jenis=?, merek=?, model=?, tahun=?, 
                    plat_nomor=?, warna=?, kapasitas=?, bahan_bakar=?, 
                    transmisi=?, harga_per_hari=?, status=?, deskripsi=?, images=? 
                    WHERE id=?";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $_POST['nama'], $_POST['jenis'], $_POST['merek'], $_POST['model'], $_POST['tahun'],
                $_POST['plat_nomor'], $_POST['warna'], $_POST['kapasitas'], $_POST['bahan_bakar'],
                $_POST['transmisi'], $_POST['harga_per_hari'], $_POST['status'], $_POST['deskripsi'], 
                $images_json, $id
            ]);

            echo "<script>alert('Data kendaraan berhasil diperbarui!'); window.location='vehicles.php';</script>";
            exit();
        } catch (PDOException $e) {
            $error_msg = "Database Error: " . $e->getMessage();
        }
    }
}

$page_title = "Edit Kendaraan";
include 'header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Edit Kendaraan</h2>
    <a href="vehicles.php" class="btn btn-secondary"><i class="fas fa-arrow-left me-2"></i>Kembali</a>
</div>

<?php if(isset($error_msg)): ?>
    <div class="alert alert-danger"><?php echo $error_msg; ?></div>
<?php endif; ?>

<div class="card shadow border-0 mb-5">
    <div class="card-body p-4">
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="update_vehicle" value="1">
            
            <div class="row g-3">
                <div class="col-12 text-center mb-3">
                    <label class="d-block fw-bold mb-2">Foto Saat Ini</label>
                    <img src="../../assets/images/vehicles/<?php echo $current_image; ?>" 
                         alt="Preview" class="img-thumbnail rounded" style="height: 150px; object-fit: cover;">
                </div>

                <div class="col-12">
                    <label class="form-label fw-bold">Ganti Foto (Opsional)</label>
                    <input type="file" class="form-control" name="image" accept="image/*">
                    <small class="text-muted">Biarkan kosong jika tidak ingin mengubah foto.</small>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Nama Kendaraan</label>
                    <input type="text" class="form-control" name="nama" value="<?php echo htmlspecialchars($vehicle['nama']); ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Jenis</label>
                    <select class="form-select" name="jenis" required>
                        <option value="mobil" <?php echo ($vehicle['jenis']=='mobil')?'selected':''; ?>>Mobil</option>
                        <option value="motor" <?php echo ($vehicle['jenis']=='motor')?'selected':''; ?>>Motor</option>
                        <option value="sepeda_listrik" <?php echo ($vehicle['jenis']=='sepeda_listrik')?'selected':''; ?>>Sepeda Listrik</option>
                        <option value="sepeda" <?php echo ($vehicle['jenis']=='sepeda')?'selected':''; ?>>Sepeda</option>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Merek</label>
                    <input type="text" class="form-control" name="merek" value="<?php echo htmlspecialchars($vehicle['merek']); ?>" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Model</label>
                    <input type="text" class="form-control" name="model" value="<?php echo htmlspecialchars($vehicle['model']); ?>" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Tahun</label>
                    <input type="number" class="form-control" name="tahun" value="<?php echo htmlspecialchars($vehicle['tahun']); ?>" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Plat Nomor</label>
                    <input type="text" class="form-control" name="plat_nomor" value="<?php echo htmlspecialchars($vehicle['plat_nomor']); ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Warna</label>
                    <input type="text" class="form-control" name="warna" value="<?php echo htmlspecialchars($vehicle['warna']); ?>" required>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Kapasitas</label>
                    <input type="number" class="form-control" name="kapasitas" value="<?php echo htmlspecialchars($vehicle['kapasitas']); ?>" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Bahan Bakar</label>
                    <select class="form-select" name="bahan_bakar">
                        <option value="bensin" <?php echo ($vehicle['bahan_bakar']=='bensin')?'selected':''; ?>>Bensin</option>
                        <option value="solar" <?php echo ($vehicle['bahan_bakar']=='solar')?'selected':''; ?>>Solar</option>
                        <option value="listrik" <?php echo ($vehicle['bahan_bakar']=='listrik')?'selected':''; ?>>Listrik</option>
                        <option value="manual" <?php echo ($vehicle['bahan_bakar']=='manual')?'selected':''; ?>>Manual</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Transmisi</label>
                    <select class="form-select" name="transmisi">
                        <option value="manual" <?php echo ($vehicle['transmisi']=='manual')?'selected':''; ?>>Manual</option>
                        <option value="matic" <?php echo ($vehicle['transmisi']=='matic')?'selected':''; ?>>Matic</option>
                        <option value="otomatis" <?php echo ($vehicle['transmisi']=='otomatis')?'selected':''; ?>>Otomatis</option>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Harga per Hari (Rp)</label>
                    <input type="number" class="form-control" name="harga_per_hari" value="<?php echo htmlspecialchars($vehicle['harga_per_hari']); ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Status</label>
                    <select class="form-select" name="status">
                        <option value="tersedia" <?php echo ($vehicle['status']=='tersedia')?'selected':''; ?>>Tersedia</option>
                        <option value="disewa" <?php echo ($vehicle['status']=='disewa')?'selected':''; ?>>Disewa</option>
                        <option value="maintenance" <?php echo ($vehicle['status']=='maintenance')?'selected':''; ?>>Maintenance</option>
                    </select>
                </div>

                <div class="col-12">
                    <label class="form-label">Deskripsi</label>
                    <textarea class="form-control" name="deskripsi" rows="4"><?php echo htmlspecialchars($vehicle['deskripsi']); ?></textarea>
                </div>

                <div class="col-12 mt-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary px-4 fw-bold">
                        <i class="fas fa-save me-2"></i> Simpan Perubahan
                    </button>
                    <a href="vehicles.php" class="btn btn-light border px-4">Batal</a>
                </div>
            </div>
        </form>
    </div>
</div>

        </div>
    </main>
<script src="../../assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>