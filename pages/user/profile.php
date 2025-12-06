<?php 
$page_title = "Profil Saya - EzRent";
include 'header.php'; 

// Koneksi database
require_once '../../php/config/database.php';

$success_message = '';
$error_message = '';
$user_name = $_SESSION['nama_lengkap'] ?? '';
$user_email = $_SESSION['email'] ?? '';

// Ambil data user dari database
try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user_data = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user_data) {
        $error_message = "Data pengguna tidak ditemukan.";
    }
} catch (PDOException $e) {
    $error_message = "Terjadi kesalahan saat mengambil data: " . $e->getMessage();
}

// Handle form update personal info
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $nama_lengkap = trim($_POST['nama_lengkap'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $nomor_telepon = trim($_POST['nomor_telepon'] ?? '');
    $alamat = trim($_POST['alamat'] ?? '');
    
    // Validasi
    if (empty($nama_lengkap) || empty($email)) {
        $error_message = "Nama lengkap dan email harus diisi.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Format email tidak valid.";
    } else {
        try {
            // Cek apakah email sudah digunakan oleh user lain
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $stmt->execute([$email, $_SESSION['user_id']]);
            $existing_user = $stmt->fetch();
            
            if ($existing_user) {
                $error_message = "Email sudah digunakan oleh pengguna lain.";
            } else {
                // Update data user - HANYA kolom yang ada di tabel
                $stmt = $pdo->prepare("
                    UPDATE users 
                    SET nama_lengkap = ?, email = ?, nomor_telepon = ?, alamat = ?
                    WHERE id = ?
                ");
                $stmt->execute([$nama_lengkap, $email, $nomor_telepon, $alamat, $_SESSION['user_id']]);
                
                // Update session data
                $_SESSION['nama_lengkap'] = $nama_lengkap;
                $_SESSION['email'] = $email;
                
                $success_message = "🎉 Profil berhasil diperbarui!";
                $user_name = $nama_lengkap;
                
                // Refresh data
                $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
                $stmt->execute([$_SESSION['user_id']]);
                $user_data = $stmt->fetch(PDO::FETCH_ASSOC);
            }
        } catch (PDOException $e) {
            $error_message = "Terjadi kesalahan saat memperbarui profil: " . $e->getMessage();
        }
    }
}

// Handle form change password
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validasi
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error_message = "Semua field password harus diisi.";
    } elseif ($new_password !== $confirm_password) {
        $error_message = "Password baru dan konfirmasi password tidak cocok.";
    } elseif (strlen($new_password) < 6) {
        $error_message = "Password baru minimal 6 karakter.";
    } else {
        try {
            // Verifikasi password saat ini
            $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user && password_verify($current_password, $user['password'])) {
                // Update password - TANPA updated_at
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt->execute([$hashed_password, $_SESSION['user_id']]);
                
                $success_message = "🔐 Password berhasil diubah!";
            } else {
                $error_message = "Password saat ini tidak valid.";
            }
        } catch (PDOException $e) {
            $error_message = "Terjadi kesalahan saat mengubah password: " . $e->getMessage();
        }
    }
}

// Handle upload foto profil
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_photo'])) {
    if (isset($_FILES['foto_profil']) && $_FILES['foto_profil']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['foto_profil'];
        
        // Validasi file
        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        $max_size = 2 * 1024 * 1024; // 2MB
        
        if (!in_array($file['type'], $allowed_types)) {
            $error_message = "Format file tidak didukung. Gunakan JPG, PNG, atau GIF.";
        } elseif ($file['size'] > $max_size) {
            $error_message = "Ukuran file terlalu besar. Maksimal 2MB.";
        } else {
            // Generate unique filename
            $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = 'profile_' . $_SESSION['user_id'] . '_' . time() . '.' . $file_extension;
            $upload_path = '../../assets/images/profiles/' . $filename;
            
            // Create directory if not exists
            if (!is_dir('../../assets/images/profiles/')) {
                mkdir('../../assets/images/profiles/', 0777, true);
            }
            
            if (move_uploaded_file($file['tmp_name'], $upload_path)) {
                // Delete old photo if exists
                if (!empty($user_data['foto_profil']) && file_exists('../../assets/images/profiles/' . $user_data['foto_profil'])) {
                    unlink('../../assets/images/profiles/' . $user_data['foto_profil']);
                }
                
                // Update database - TANPA updated_at
                $stmt = $pdo->prepare("UPDATE users SET foto_profil = ? WHERE id = ?");
                $stmt->execute([$filename, $_SESSION['user_id']]);
                
                $success_message = "📷 Foto profil berhasil diupload!";
                
                // Refresh data
                $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
                $stmt->execute([$_SESSION['user_id']]);
                $user_data = $stmt->fetch(PDO::FETCH_ASSOC);
            } else {
                $error_message = "Gagal mengupload foto profil. Silakan coba lagi.";
            }
        }
    } else {
        $error_message = "Pilih file foto profil yang valid.";
    }
}
?>

<style>
    .profile-hero {
        background: linear-gradient(135deg, #0a0a0a 0%, #1a1a1a 100%);
        padding: 6rem 20px;
        width: 100%;
        position: relative;
        overflow: hidden;
    }
    .profile-hero::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.02'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        pointer-events: none;
    }
    .profile-hero h1 {
        color: #fff !important;
        opacity: 0;
        transform: translateY(30px);
        animation: fadeInUp 0.8s ease forwards 0.2s;
    }
    .profile-hero p {
        color: rgba(255,255,255,0.7) !important;
        opacity: 0;
        transform: translateY(30px);
        animation: fadeInUp 0.8s ease forwards 0.4s;
    }
    .profile-hero .welcome-card {
        opacity: 0;
        transform: translateY(30px);
        animation: fadeInUp 0.8s ease forwards 0.6s;
    }
    
    @keyframes fadeInUp {
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .profile-content {
        padding: 4rem 20px;
        background: #f8fafc;
        width: 100%;
    }
    
    /* Scroll Reveal */
    .reveal-card {
        opacity: 0;
        transform: translateY(40px);
        transition: all 0.6s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .reveal-card.visible {
        opacity: 1;
        transform: translateY(0);
    }
    .reveal-card:nth-child(1) { transition-delay: 0.1s; }
    .reveal-card:nth-child(2) { transition-delay: 0.2s; }
    .reveal-card:nth-child(3) { transition-delay: 0.3s; }
    
    .quick-action-card {
        opacity: 0;
        transform: translateY(30px);
        transition: all 0.6s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .quick-action-card.visible {
        opacity: 1;
        transform: translateY(0);
    }
    .quick-action-card:nth-child(1) { transition-delay: 0.1s; }
    .quick-action-card:nth-child(2) { transition-delay: 0.2s; }
    .quick-action-card:nth-child(3) { transition-delay: 0.3s; }
</style>

<!-- Hero Section -->
<section class="profile-hero">
    <div class="container">
        <div style="max-width: 800px; margin: 0 auto; text-align: center;">
            <h1 style="font-size: 3rem; font-weight: 700; line-height: 1.2; margin-bottom: 1.5rem;">
                Profil Saya
            </h1>
            <p style="font-size: 1.25rem; margin-bottom: 2.5rem; line-height: 1.7;">
                Kelola informasi akun dan data pribadi Anda
            </p>
            
            <!-- Welcome Message for Logged-in User -->
            <div class="welcome-card" style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); padding: 1.5rem; max-width: 500px; margin: 0 auto;">
                <div style="display: flex; align-items: center; gap: 1rem; justify-content: center;">
                    <div style="width: 50px; height: 50px; background: rgba(213,0,0,0.2); border: 1px solid rgba(213,0,0,0.4); display: flex; align-items: center; justify-content: center;">
                        <svg width="24" height="24" fill="none" stroke="#d50000" stroke-width="2" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                    </div>
                    <div style="text-align: left;">
                        <h3 style="color: #fff; margin-bottom: 0.5rem; font-size: 1.1rem;">
                            Halo, <?php echo htmlspecialchars($user_name); ?>!
                        </h3>
                        <p style="color: rgba(255,255,255,0.6); font-size: 0.9rem; margin: 0;">
                            Kelola informasi profil dan keamanan akun Anda di sini.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

    <!-- Profile Content -->
    <section style="padding: 4rem 0;">
        <div class="container">
            <?php if ($success_message): ?>
                <div style="background: #d1fae5; color: #065f46; padding: 1.5rem; border-radius: 8px; margin-bottom: 2rem; border: 1px solid #a7f3d0; text-align: center;">
                    <div style="font-size: 2rem; margin-bottom: 0.5rem;">✅</div>
                    <h3 style="margin-bottom: 0.5rem; font-size: 1.1rem;">Berhasil!</h3>
                    <p style="margin: 0;"><?php echo $success_message; ?></p>
                </div>
            <?php endif; ?>

            <?php if ($error_message): ?>
                <div style="background: #fee2e2; color: #dc2626; padding: 1rem; border-radius: 8px; margin-bottom: 2rem; border: 1px solid #fecaca;">
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 4rem; align-items: start;">
                
                <!-- Profile Photo & Personal Information -->
                <div>
                    <!-- Profile Photo -->
                    <div class="reveal-card" style="background: white; padding: 2.5rem; border-radius: 12px; box-shadow: var(--shadow); margin-bottom: 2rem;">
                        <h2 style="color: var(--text-dark); margin-bottom: 2rem; font-size: 1.75rem; font-weight: 600;">
                            Foto Profil
                        </h2>
                        
                        <div style="text-align: center;">
                            <div style="width: 150px; height: 150px; border-radius: 50%; margin: 0 auto 1.5rem; overflow: hidden; border: 4px solid var(--primary); background: #f8fafc; display: flex; align-items: center; justify-content: center;">
                                <?php if (!empty($user_data['foto_profil'])): ?>
                                    <img src="../../assets/images/profiles/<?php echo htmlspecialchars($user_data['foto_profil']); ?>" 
                                         alt="Foto Profil" 
                                         style="width: 100%; height: 100%; object-fit: cover;">
                                <?php else: ?>
                                    <div style="font-size: 3rem; color: #888;"><svg width="48" height="48" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg></div>
                                <?php endif; ?>
                            </div>
                            
                            <form method="POST" action="" enctype="multipart/form-data" id="photoForm">
                                <input type="hidden" name="upload_photo" value="1">
                                
                                <div style="margin-bottom: 1rem;">
                                    <input type="file" name="foto_profil" accept="image/*" 
                                        style="width: 100%; padding: 0.5rem; border: 2px dashed var(--border); border-radius: 6px; font-size: 0.9rem;"
                                        id="photoInput">
                                    <div style="font-size: 0.8rem; color: var(--text-light); margin-top: 0.5rem;">
                                        Format: JPG, PNG, GIF (Maks. 2MB)
                                    </div>
                                </div>
                                
                                <button type="submit" 
                                    style="background: var(--primary); color: white; border: none; padding: 0.75rem 1.5rem; border-radius: 6px; font-size: 0.9rem; font-weight: 600; cursor: pointer; transition: all 0.3s ease; width: 100%;">
                                    📷 Upload Foto
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Personal Information -->
                    <div class="reveal-card" style="background: white; padding: 2.5rem; border-radius: 12px; box-shadow: var(--shadow);">
                        <h2 style="color: var(--text-dark); margin-bottom: 2rem; font-size: 1.75rem; font-weight: 600;">
                            Informasi Pribadi
                        </h2>

                        <form method="POST" action="" id="profileForm">
                            <input type="hidden" name="update_profile" value="1">
                            
                            <div style="margin-bottom: 1.5rem;">
                                <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--text-dark);">
                                    Nama Lengkap *
                                </label>
                                <input type="text" name="nama_lengkap" required 
                                    value="<?php echo htmlspecialchars($user_data['nama_lengkap'] ?? ''); ?>"
                                    style="width: 100%; padding: 0.75rem; border: 2px solid var(--border); border-radius: 6px; font-size: 1rem; transition: border-color 0.3s ease;">
                            </div>

                            <div style="margin-bottom: 1.5rem;">
                                <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--text-dark);">
                                    Email *
                                </label>
                                <input type="email" name="email" required 
                                    value="<?php echo htmlspecialchars($user_data['email'] ?? ''); ?>"
                                    style="width: 100%; padding: 0.75rem; border: 2px solid var(--border); border-radius: 6px; font-size: 1rem; transition: border-color 0.3s ease;">
                            </div>

                            <div style="margin-bottom: 1.5rem;">
                                <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--text-dark);">
                                    Nomor Telepon
                                </label>
                                <input type="tel" name="nomor_telepon" 
                                    value="<?php echo htmlspecialchars($user_data['nomor_telepon'] ?? ''); ?>"
                                    style="width: 100%; padding: 0.75rem; border: 2px solid var(--border); border-radius: 6px; font-size: 1rem; transition: border-color 0.3s ease;">
                            </div>

                            <div style="margin-bottom: 2rem;">
                                <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--text-dark);">
                                    Alamat
                                </label>
                                <textarea name="alamat" rows="4"
                                    style="width: 100%; padding: 0.75rem; border: 2px solid var(--border); border-radius: 6px; font-size: 1rem; transition: border-color 0.3s ease; resize: vertical;"><?php echo htmlspecialchars($user_data['alamat'] ?? ''); ?></textarea>
                            </div>

                            <button type="submit" 
                                style="background: var(--primary); color: white; border: none; padding: 0.75rem 2rem; border-radius: 6px; font-size: 1rem; font-weight: 600; cursor: pointer; transition: all 0.3s ease; width: 100%;">
                                💾 Simpan Perubahan
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Change Password & Account Info -->
                <div>
                    <!-- Change Password -->
                    <div class="reveal-card" style="background: white; padding: 2.5rem; border-radius: 12px; box-shadow: var(--shadow); margin-bottom: 2rem;">
                        <h2 style="color: var(--text-dark); margin-bottom: 2rem; font-size: 1.75rem; font-weight: 600;">
                            Ubah Password
                        </h2>

                        <form method="POST" action="" id="passwordForm">
                            <input type="hidden" name="change_password" value="1">
                            
                            <div style="margin-bottom: 1.5rem;">
                                <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--text-dark);">
                                    Password Saat Ini *
                                </label>
                                <input type="password" name="current_password" required 
                                    style="width: 100%; padding: 0.75rem; border: 2px solid var(--border); border-radius: 6px; font-size: 1rem; transition: border-color 0.3s ease;">
                            </div>

                            <div style="margin-bottom: 1.5rem;">
                                <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--text-dark);">
                                    Password Baru *
                                </label>
                                <input type="password" name="new_password" required 
                                    style="width: 100%; padding: 0.75rem; border: 2px solid var(--border); border-radius: 6px; font-size: 1rem; transition: border-color 0.3s ease;">
                                <div style="font-size: 0.8rem; color: var(--text-light); margin-top: 0.5rem;">
                                    Minimal 6 karakter
                                </div>
                            </div>

                            <div style="margin-bottom: 2rem;">
                                <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--text-dark);">
                                    Konfirmasi Password Baru *
                                </label>
                                <input type="password" name="confirm_password" required 
                                    style="width: 100%; padding: 0.75rem; border: 2px solid var(--border); border-radius: 6px; font-size: 1rem; transition: border-color 0.3s ease;">
                            </div>

                            <button type="submit" 
                                style="background: var(--primary); color: white; border: none; padding: 0.75rem 2rem; border-radius: 6px; font-size: 1rem; font-weight: 600; cursor: pointer; transition: all 0.3s ease; width: 100%;">
                                🔐 Ubah Password
                            </button>
                        </form>
                    </div>

                    <!-- Account Info -->
                    <div class="reveal-card" style="background: white; padding: 2.5rem; border-radius: 12px; box-shadow: var(--shadow);">
                        <h3 style="color: var(--text-dark); margin-bottom: 1.5rem; font-size: 1.5rem; font-weight: 600;">
                            Informasi Akun
                        </h3>
                        
                        <div style="display: flex; flex-direction: column; gap: 1rem;">
                            <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.75rem 0; border-bottom: 1px solid var(--border);">
                                <span style="color: var(--text-light); font-weight: 500;">ID Pengguna:</span>
                                <span style="color: var(--text-dark); font-weight: 600;">#<?php echo htmlspecialchars($user_data['id'] ?? '-'); ?></span>
                            </div>
                            
                            <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.75rem 0; border-bottom: 1px solid var(--border);">
                                <span style="color: var(--text-light); font-weight: 500;">Role:</span>
                                <span style="background: #dbeafe; color: #1e40af; padding: 0.25rem 0.75rem; border-radius: 12px; font-size: 0.8rem; font-weight: 500; text-transform: capitalize;">
                                    <?php echo htmlspecialchars($user_data['role'] ?? 'user'); ?>
                                </span>
                            </div>
                            
                            <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.75rem 0; border-bottom: 1px solid var(--border);">
                                <span style="color: var(--text-light); font-weight: 500;">Status:</span>
                                <span style="background: #d1fae5; color: #065f46; padding: 0.25rem 0.75rem; border-radius: 12px; font-size: 0.8rem; font-weight: 500;">
                                    <?php echo ($user_data['is_verified'] ?? true) ? 'Terverifikasi' : 'Belum Terverifikasi'; ?>
                                </span>
                            </div>
                            
                            <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.75rem 0;">
                                <span style="color: var(--text-light); font-weight: 500;">Bergabung Sejak:</span>
                                <span style="color: var(--text-dark); font-weight: 600; font-size: 0.9rem;">
                                    <?php echo isset($user_data['created_at']) ? date('d M Y', strtotime($user_data['created_at'])) : '-'; ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Quick Actions -->
    <section style="background: #f8fafc; padding: 4rem 0;">
        <div class="container">
            <div style="text-align: center; margin-bottom: 3rem;">
                <h2 style="font-size: 2.5rem; font-weight: 700; color: var(--text-dark); margin-bottom: 1rem;">
                    Akses Cepat
                </h2>
                <p style="font-size: 1.125rem; color: var(--text-light); max-width: 600px; margin: 0 auto;">
                    Jelajahi fitur-fitur lainnya
                </p>
            </div>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 2rem;">
                <a href="dashboard.php" style="text-decoration: none;" class="quick-action-card">
                    <div style="text-align: center; padding: 3rem 2rem; background: white; border-radius: 12px; box-shadow: var(--shadow); transition: all 0.3s ease; border: 2px solid transparent; height: 100%;">
                        <div style="font-size: 3.5rem; margin-bottom: 1.5rem; color: var(--primary);"><svg width="48" height="48" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="9"></rect><rect x="14" y="3" width="7" height="5"></rect><rect x="14" y="12" width="7" height="9"></rect><rect x="3" y="16" width="7" height="5"></rect></svg></div>
                        <h3 style="margin-bottom: 1rem; color: var(--text-dark); font-size: 1.25rem; font-weight: 600;">Dashboard</h3>
                        <p style="color: var(--text-light); line-height: 1.6;">Kembali ke dashboard utama</p>
                    </div>
                </a>
                
                <a href="my-bookings.php" style="text-decoration: none;" class="quick-action-card">
                    <div style="text-align: center; padding: 3rem 2rem; background: white; border-radius: 12px; box-shadow: var(--shadow); transition: all 0.3s ease; border: 2px solid transparent; height: 100%;">
                        <div style="font-size: 3.5rem; margin-bottom: 1.5rem; color: var(--primary);"><svg width="48" height="48" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg></div>
                        <h3 style="margin-bottom: 1rem; color: var(--text-dark); font-size: 1.25rem; font-weight: 600;">Pesanan Saya</h3>
                        <p style="color: var(--text-light); line-height: 1.6;">Lihat dan kelola pesanan Anda</p>
                    </div>
                </a>
                
                <a href="bookings.php" style="text-decoration: none;" class="quick-action-card">
                    <div style="text-align: center; padding: 3rem 2rem; background: white; border-radius: 12px; box-shadow: var(--shadow); transition: all 0.3s ease; border: 2px solid transparent; height: 100%;">
                        <div style="font-size: 3.5rem; margin-bottom: 1.5rem; color: var(--primary);"><svg width="48" height="48" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M5 11l1.5-4.5A2 2 0 0 1 8.4 5h7.2a2 2 0 0 1 1.9 1.5L19 11"></path><path d="M3 17h18v-4a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v4z"></path><circle cx="7" cy="17" r="2"></circle><circle cx="17" cy="17" r="2"></circle></svg></div>
                        <h3 style="margin-bottom: 1rem; color: var(--text-dark); font-size: 1.25rem; font-weight: 600;">Sewa Kendaraan</h3>
                        <p style="color: var(--text-light); line-height: 1.6;">Temukan kendaraan untuk disewa</p>
                    </div>
                </a>
            </div>
        </div>
    </section>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const profileForm = document.getElementById('profileForm');
    const passwordForm = document.getElementById('passwordForm');
    const photoForm = document.getElementById('photoForm');
    const photoInput = document.getElementById('photoInput');
    
    // Form validation for profile
    profileForm.addEventListener('submit', function(e) {
        const namaLengkap = profileForm.querySelector('input[name="nama_lengkap"]').value.trim();
        const email = profileForm.querySelector('input[name="email"]').value.trim();
        
        if (!namaLengkap) {
            e.preventDefault();
            alert('Harap isi nama lengkap');
            profileForm.querySelector('input[name="nama_lengkap"]').focus();
            return false;
        }
        
        if (!email) {
            e.preventDefault();
            alert('Harap isi email');
            profileForm.querySelector('input[name="email"]').focus();
            return false;
        }
        
        // Email validation
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            e.preventDefault();
            alert('Format email tidak valid');
            profileForm.querySelector('input[name="email"]').focus();
            return false;
        }
    });
    
    // Form validation for password
    passwordForm.addEventListener('submit', function(e) {
        const currentPassword = passwordForm.querySelector('input[name="current_password"]').value;
        const newPassword = passwordForm.querySelector('input[name="new_password"]').value;
        const confirmPassword = passwordForm.querySelector('input[name="confirm_password"]').value;
        
        if (!currentPassword || !newPassword || !confirmPassword) {
            e.preventDefault();
            alert('Harap lengkapi semua field password');
            return false;
        }
        
        if (newPassword !== confirmPassword) {
            e.preventDefault();
            alert('Password baru dan konfirmasi password tidak cocok');
            passwordForm.querySelector('input[name="confirm_password"]').focus();
            return false;
        }
        
        if (newPassword.length < 6) {
            e.preventDefault();
            alert('Password baru minimal 6 karakter');
            passwordForm.querySelector('input[name="new_password"]').focus();
            return false;
        }
    });
    
    // Form validation for photo upload
    photoForm.addEventListener('submit', function(e) {
        if (!photoInput.files || !photoInput.files[0]) {
            e.preventDefault();
            alert('Harap pilih file foto profil');
            return false;
        }
        
        const file = photoInput.files[0];
        const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        const maxSize = 2 * 1024 * 1024; // 2MB
        
        if (!allowedTypes.includes(file.type)) {
            e.preventDefault();
            alert('Format file tidak didukung. Gunakan JPG, PNG, atau GIF.');
            return false;
        }
        
        if (file.size > maxSize) {
            e.preventDefault();
            alert('Ukuran file terlalu besar. Maksimal 2MB.');
            return false;
        }
    });
    
    // Preview photo before upload
    photoInput.addEventListener('change', function(e) {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                // You could add a preview feature here if needed
                console.log('File selected:', file.name);
            };
            reader.readAsDataURL(file);
        }
    });
    
    // Add focus effects to form inputs
    const inputs = document.querySelectorAll('input, textarea');
    inputs.forEach(input => {
        input.addEventListener('focus', function() {
            this.style.borderColor = 'var(--primary)';
            this.style.boxShadow = '0 0 0 3px rgba(37, 99, 235, 0.1)';
        });
        
        input.addEventListener('blur', function() {
            this.style.borderColor = 'var(--border)';
            this.style.boxShadow = 'none';
        });
    });
    
    // Add hover effects to quick action cards
    const quickActionCards = document.querySelectorAll('a > div');
    quickActionCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px)';
            this.style.boxShadow = 'var(--shadow-lg)';
            this.style.borderColor = 'var(--primary)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = 'var(--shadow)';
            this.style.borderColor = 'transparent';
        });
    });
});
</script>

<style>
/* Enhanced styles for profile page */
input:focus, textarea:focus {
    outline: none;
    border-color: var(--primary) !important;
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1) !important;
}

/* Responsive design */
@media (max-width: 768px) {
    .container {
        padding: 0 1rem;
    }
    
    main section:first-child {
        padding: 2rem 0 !important;
    }
    
    main section:last-child {
        padding: 2rem 0 !important;
    }
    
    /* Untuk tampilan mobile, ubah grid menjadi 1 kolom */
    .profile-grid {
        grid-template-columns: 1fr !important;
        gap: 2rem !important;
    }
}

/* Button hover effects */
button:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

/* Smooth transitions */
button, input, textarea, a > div {
    transition: all 0.3s ease;
}

/* File input styling */
input[type="file"] {
    cursor: pointer;
}

input[type="file"]:hover {
    border-color: var(--primary) !important;
    background: #f8fafc;
}
</style>

<script>
// Scroll Reveal Animation
const observerOptions = {
    threshold: 0.1,
    rootMargin: '0px 0px -50px 0px'
};

const scrollObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.classList.add('visible');
        }
    });
}, observerOptions);

document.querySelectorAll('.reveal-card, .quick-action-card').forEach(el => {
    scrollObserver.observe(el);
});
</script>

<?php include '../../php/includes/footer.php'; ?>