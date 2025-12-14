<?php 
if (session_status() === PHP_SESSION_NONE) session_start();

if (isset($_SESSION['user_id'])) {
    header('Location: ' . ($_SESSION['role'] === 'admin' ? 'admin/dashboard.php' : 'user/dashboard.php'));
    exit;
}

$page_title = "Daftar Akun - EzRent";
$error_message = '';
$form_data = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_lengkap = trim($_POST['nama_lengkap'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $nomor_telepon = trim($_POST['nomor_telepon'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $alamat = trim($_POST['alamat'] ?? '');
    
    $form_data = ['nama_lengkap' => $nama_lengkap, 'email' => $email, 'nomor_telepon' => $nomor_telepon, 'alamat' => $alamat];
    
    $errors = [];
    
    if (empty($nama_lengkap)) $errors[] = "Nama lengkap wajib diisi";
    elseif (strlen($nama_lengkap) < 2) $errors[] = "Nama lengkap minimal 2 karakter";
    
    if (empty($email)) $errors[] = "Email wajib diisi";
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Format email tidak valid";
    
    if (empty($nomor_telepon)) $errors[] = "Nomor telepon wajib diisi";
    if (empty($password)) $errors[] = "Password wajib diisi";
    elseif (strlen($password) < 6) $errors[] = "Password minimal 6 karakter";
    if ($password !== $confirm_password) $errors[] = "Konfirmasi password tidak cocok";
    
    if (empty($errors)) {
        try {
            require_once '../php/config/database.php';
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $error_message = "Email sudah terdaftar.";
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (nama_lengkap, email, password, nomor_telepon, alamat, role, is_verified, created_at) VALUES (?, ?, ?, ?, ?, 'user', TRUE, NOW())");
                if ($stmt->execute([$nama_lengkap, $email, $hashed_password, $nomor_telepon, $alamat])) {
                    header("Location: login.php? status=registered");
                    exit;
                } else {
                    $error_message = "Gagal mendaftar. Silakan coba lagi.";
                }
            }
        } catch (Exception $e) {
            $error_message = "Terjadi kesalahan sistem.";
        }
    } else {
        $error_message = implode("<br>", $errors);
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title><?php echo $page_title; ?></title>
    <link href="https://fonts.googleapis.com/css2? family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; background:  #fff; min-height: 100vh; }
    
    .register-wrapper { display: flex; min-height: 100vh; }
    
    /* LEFT VISUAL SIDE */
    .register-visual {
        width: 50%; position: fixed; top: 0; left:  0; bottom: 0;
        display: flex; align-items: center; justify-content: center; overflow: hidden;
        background: linear-gradient(135deg, #0a0a0a 0%, #1a1a1a 50%, #0d0d0d 100%);
    }
    
    .register-visual::before {
        content: ''; position: absolute; inset: 0;
        background-image: 
            radial-gradient(circle at 20% 50%, rgba(213,0,0,0.15) 0%, transparent 50%),
            radial-gradient(circle at 80% 80%, rgba(255,82,82,0.1) 0%, transparent 50%),
            radial-gradient(circle at 40% 20%, rgba(255,255,255,0.03) 0%, transparent 50%);
        animation: gradientShift 15s ease infinite;
        z-index: 1;
    }
    
    .register-visual::after {
        content: ''; position: absolute; inset: 0;
        background-image:  
            linear-gradient(0deg, transparent 24%, rgba(255,82,82,0.05) 25%, rgba(255,82,82,0.05) 26%, transparent 27%, transparent 74%, rgba(255,82,82,0.05) 75%, rgba(255,82,82,0.05) 76%, transparent 77%, transparent),
            linear-gradient(90deg, transparent 24%, rgba(255,82,82,0.05) 25%, rgba(255,82,82,0.05) 26%, transparent 27%, transparent 74%, rgba(255,82,82,0.05) 75%, rgba(255,82,82,0.05) 76%, transparent 77%, transparent);
        background-size: 100px 100px;
        z-index: 2; opacity: 0.3;
        animation: gridMove 20s linear infinite;
    }
    
    @keyframes gradientShift {
        0%, 100% { filter: hue-rotate(0deg); }
        50% { filter: hue-rotate(5deg); }
    }
    @keyframes gridMove { 0% { background-position: 0 0; } 100% { background-position: 100px 100px; } }
    
    .particle {
        position: absolute; border-radius: 50%; pointer-events: none;
        animation: float 20s infinite ease-in-out;
    }
    
    @keyframes float {
        0%, 100% { transform: translateY(0) translateX(0); opacity: 0; }
        10% { opacity: 1; }
        90% { opacity: 1; }
        100% { transform: translateY(-300px) translateX(100px); opacity: 0; }
    }
    
    .decor-circle {
        position: absolute; border-radius: 50%;
        border: 2px solid rgba(213,0,0,0.2); pointer-events: none; opacity: 0.5;
    }
    .decor-circle.c1 { width: 400px; height: 400px; top: -200px; left: -200px; animation: rotateSlow 30s linear infinite; }
    .decor-circle.c2 { width: 300px; height: 300px; bottom: -150px; right: -150px; animation: rotateSlow 25s linear infinite reverse; }
    .decor-circle.c3 { width: 250px; height: 250px; top: 50%; left: 50%; transform: translate(-50%, -50%); border-color: rgba(255,255,255,0.08); animation: rotateSlow 40s linear infinite; }
    
    @keyframes rotateSlow { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
    
    .visual-content {
        position: relative; z-index: 10; text-align: center; padding: 3rem; max-width: 500px;
    }
    
    .visual-logo {
        margin-bottom: 2rem;
        animation: slideInDown 1s ease 0.3s forwards;
        opacity: 0;
    }
    
    @keyframes slideInDown {
        from { opacity: 0; transform: translateY(-30px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    .visual-logo-text { font-size: 4rem; font-weight: 700; letter-spacing: -0.03em; line-height: 1; }
    .visual-logo-text .ez { color: #fff; }
    .visual-logo-text .rent { color: #d50000; }
    .visual-logo-text .dot { color: #d50000; }
    
    .visual-tagline {
        color: rgba(255,255,255,0.4); font-size: 1rem; letter-spacing: 0.2em;
        text-transform: uppercase; margin-top: 1rem;
        animation: fadeIn 1s ease 0.6s forwards;
        opacity: 0;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }
    
    /* RIGHT FORM SIDE */
    .register-form-side {
        width: 50%; margin-left: 50%;
        background: #fff; display: flex; align-items: center; justify-content: center;
        padding: 4rem 3rem; min-height: 100vh;
    }
    
    .form-container {
        width: 100%; max-width:  450px;
        opacity: 0; transform: translateY(30px);
        animation: formFadeUp 0.8s 0.3s forwards;
    }
    
    @keyframes formFadeUp { to { opacity: 1; transform:  translateY(0); } }
    
    .form-header { margin-bottom: 2.5rem; }
    .form-header h1 { font-size: 1.75rem; font-weight: 600; color: #000; margin-bottom: 0.5rem; letter-spacing: -0.02em; }
    .form-header p { color: #6b7280; font-size:  0.95rem; }
    
    .error-box {
        background: #fef2f2; border-left: 4px solid #dc2626; padding: 1rem 1.25rem;
        margin-bottom: 1.5rem; display: flex; align-items: flex-start; gap: 0.75rem;
        animation: shake 0.5s ease;
    }
    @keyframes shake {
        0%, 100% { transform: translateX(0); }
        20%, 60% { transform: translateX(-5px); }
        40%, 80% { transform: translateX(5px); }
    }
    .error-box svg { width: 20px; height: 20px; color: #dc2626; flex-shrink: 0; margin-top: 2px; }
    .error-box span { color: #991b1b; font-size: 0.9rem; line-height: 1.5; }
    
    .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
    .form-group { margin-bottom: 1.25rem; }
    .form-label { display: block; font-size: 0.8rem; font-weight: 500; color: #374151; margin-bottom: 0.5rem; }
    .form-label .required { color: #d50000; }
    
    .input-wrapper { position: relative; }
    .input-icon {
        position: absolute; left: 1rem; top: 50%; transform: translateY(-50%);
        width: 18px; height: 18px; color: #9ca3af; pointer-events: none; transition: color 0.3s;
    }
    
    .form-input {
        width: 100%; padding: 0.875rem 1rem 0.875rem 2.75rem;
        border: 2px solid #e5e7eb; background: #fff; font-size: 0.95rem; font-family: inherit;
        transition: all 0.3s; color: #000;
    }
    .form-input:focus {
        outline: none; border-color:  #000;
    }
    .form-input:focus + .input-icon, .form-input:not(:placeholder-shown) + .input-icon { color: #000; }
    .form-input::placeholder { color: #9ca3af; }
    
    textarea.form-input { resize: vertical; min-height: 80px; padding: 0.875rem 1rem; }
    
    .checkbox-group {
        display: flex; align-items: flex-start; gap: 0.75rem; margin: 1.5rem 0;
    }
    .checkbox-wrapper {
        position: relative; width: 20px; height: 20px; flex-shrink: 0;
    }
    .checkbox-wrapper input {
        position: absolute; opacity: 0; cursor: pointer; width: 100%; height: 100%; z-index: 2;
    }
    .checkbox-custom {
        position: absolute; top: 0; left: 0; width: 20px; height: 20px;
        border: 2px solid #d1d5db; background: #fff; transition: all 0.3s;
        display: flex; align-items: center; justify-content: center;
    }
    .checkbox-custom svg {
        width: 12px; height: 12px; color: #fff; opacity: 0; transform: scale(0); transition: all 0.2s;
    }
    .checkbox-wrapper input:checked ~ .checkbox-custom {
        background: #000; border-color: #000;
    }
    .checkbox-wrapper input:checked ~ .checkbox-custom svg {
        opacity: 1; transform: scale(1);
    }
    .checkbox-label { font-size: 0.85rem; color: #6b7280; line-height: 1.5; }
    .checkbox-label a { color: #000; text-decoration: none; font-weight: 500; transition: color 0.3s; }
    .checkbox-label a:hover { color: #d50000; }
    
    .btn-register {
        width: 100%; padding: 1rem; background: #000; color: #fff; border:  none;
        font-size: 0.9rem; font-weight: 600; font-family: inherit; cursor: pointer;
        position: relative; overflow: hidden; transition: all 0.4s;
        text-transform: uppercase; letter-spacing: 0.1em;
    }
    .btn-register::before {
        content: ''; position: absolute; top: 0; left: -100%; width: 100%; height: 100%;
        background:  linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
        transition: left 0.5s;
    }
    .btn-register:hover::before { left: 100%; }
    .btn-register:hover { background: #1a1a1a; transform: translateY(-2px); box-shadow: 0 10px 30px rgba(0,0,0,0.2); }
    .btn-register:active { transform: translateY(0); }
    
    .divider {
        text-align: center; margin-top: 2rem; padding-top: 2rem; border-top: 1px solid #e5e7eb;
    }
    .divider p { color: #6b7280; font-size:  0.9rem; margin-bottom: 0.75rem; }
    .divider a { color: #000; font-weight: 600; text-decoration: none; transition: color 0.3s; }
    .divider a:hover { color: #d50000; }
    
    .mobile-logo-header { display: none; background: linear-gradient(135deg, #0a0a0a 0%, #1a1a1a 100%); padding: 3rem 1.5rem; text-align: center; }
    .mobile-logo-header .visual-logo-text { font-size: 2.5rem; }
    .mobile-logo-header .visual-tagline { font-size: 0.75rem; margin-top: 0.5rem; }
    
    /* HEADER */
    .register-header {
        position: fixed; top: 0; right: 0; width: 50%; z-index: 100;
        padding: 1.5rem 2rem; display: flex; justify-content: flex-end; align-items: center;
        background: transparent;
    }
    .back-link {
        display: flex; align-items: center; gap: 0.5rem;
        color: #6b7280; text-decoration:  none; font-size: 0.9rem; transition: color 0.3s;
    }
    .back-link:hover { color: #000; }
    .back-link svg { width: 16px; height: 16px; transition: transform 0.3s; }
    .back-link:hover svg { transform: translateX(-3px); }
    
    @media (max-width: 768px) {
        .register-visual { display: none ! important; }
        .mobile-logo-header { display: block; }
        .register-form-side { width: 100%; margin-left: 0; padding: 2rem; min-height: auto; }
        .register-header { width: 100%; position: relative; background: #0a0a0a ! important; }
        .mobile-logo-header { background: #0a0a0a !important; }
        .back-link { color: rgba(255,255,255,0.7); }
        .back-link:hover { color: #fff; }
    }
    
    @media (max-width: 640px) {
        .register-header { padding: 1rem 1.25rem; }
        .back-link span { display: none; }
        .register-form-side { padding: 1.5rem 1.25rem 2rem; }
        .form-container { max-width: 100%; }
        .form-header h1 { font-size: 1.5rem; }
        .form-row { grid-template-columns: 1fr; }
        .mobile-logo-header { padding: 2rem 1.25rem; }
        .mobile-logo-header .visual-logo-text { font-size: 2rem; }
    }
    </style>
</head>
<body>

<header class="register-header">
    <a href="index.php" class="back-link">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        <span>Kembali</span>
    </a>
</header>

<div class="mobile-logo-header">
    <div class="visual-logo">
        <div class="visual-logo-text">
            <span class="ez">Ez</span><span class="rent">Rent</span><span class="dot">.</span>
        </div>
        <div class="visual-tagline">Rental Kendaraan</div>
    </div>
</div>

<div class="register-wrapper">
    <div class="register-visual" id="registerVisual">
        <div class="decor-circle c1"></div>
        <div class="decor-circle c2"></div>
        <div class="decor-circle c3"></div>
        
        <div class="visual-content">
            <div class="visual-logo">
                <div class="visual-logo-text">
                    <span class="ez">Ez</span><span class="rent">Rent</span><span class="dot">.</span>
                </div>
                <div class="visual-tagline">Bergabung Sekarang</div>
            </div>
        </div>
    </div>
    
    <div class="register-form-side">
        <div class="form-container">
            <div class="form-header">
                <h1>Buat Akun Baru</h1>
                <p>Isi data diri Anda untuk mendaftar</p>
            </div>

            <?php if ($error_message): ?>
                <div class="error-box">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
                    <span><?php echo $error_message; ?></span>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label class="form-label">Nama Lengkap <span class="required">*</span></label>
                    <div class="input-wrapper">
                        <input type="text" name="nama_lengkap" required value="<?php echo htmlspecialchars($form_data['nama_lengkap'] ?? ''); ?>" class="form-input" placeholder="Nama lengkap" minlength="2" maxlength="100">
                        <svg class="input-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Email <span class="required">*</span></label>
                        <div class="input-wrapper">
                            <input type="email" name="email" required value="<?php echo htmlspecialchars($form_data['email'] ?? ''); ?>" class="form-input" placeholder="email@contoh.com" maxlength="100">
                            <svg class="input-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">No.  Telepon <span class="required">*</span></label>
                        <div class="input-wrapper">
                            <input type="tel" name="nomor_telepon" required value="<?php echo htmlspecialchars($form_data['nomor_telepon'] ?? ''); ?>" class="form-input" placeholder="081234567890" pattern="[0-9+\-\s()]{10,15}" maxlength="15">
                            <svg class="input-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-. 502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Alamat</label>
                    <div class="input-wrapper">
                        <textarea name="alamat" class="form-input" placeholder="Alamat tempat tinggal"><?php echo htmlspecialchars($form_data['alamat'] ?? ''); ?></textarea>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Password <span class="required">*</span></label>
                        <div class="input-wrapper">
                            <input type="password" name="password" required class="form-input" placeholder="Min. 6 karakter" minlength="6">
                            <svg class="input-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Konfirmasi Password <span class="required">*</span></label>
                        <div class="input-wrapper">
                            <input type="password" name="confirm_password" required class="form-input" placeholder="Ulangi password" minlength="6">
                            <svg class="input-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="checkbox-group">
                    <div class="checkbox-wrapper">
                        <input type="checkbox" name="agree" id="agree" required>
                        <div class="checkbox-custom">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                    </div>
                    <label class="checkbox-label" for="agree">Saya setuju dengan <a href="terms. php" target="_blank">Syarat & Ketentuan</a></label>
                </div>

                <button type="submit" class="btn-register">Daftar</button>
            </form>

            <div class="divider">
                <p>Sudah punya akun? </p>
                <a href="login.php">Masuk di sini →</a>
            </div>
        </div>
    </div>
</div>

<script>
function createParticles() {
    const visual = document.getElementById('registerVisual');
    if (!visual) return;
    for (let i = 0; i < 30; i++) {
        const particle = document.createElement('div');
        particle.className = 'particle';
        const size = Math.random() * 3 + 1;
        const left = Math.random() * 100;
        const delay = Math.random() * 5;
        const duration = Math.random() * 15 + 20;
        particle.style.width = size + 'px';
        particle.style.height = size + 'px';
        particle.style.left = left + '%';
        particle.style.bottom = '-10px';
        particle.style.background = `rgba(${Math.random() > 0.5 ? '213,0,0' : '255,255,255'}, ${Math.random() * 0.3 + 0.2})`;
        particle.style.animationDelay = delay + 's';
        particle. style.animationDuration = duration + 's';
        visual.appendChild(particle);
    }
}
document.addEventListener('DOMContentLoaded', createParticles);
</script>

</body>
</html>