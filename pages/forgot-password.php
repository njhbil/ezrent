<?php 
if (session_status() === PHP_SESSION_NONE) session_start();

if (isset($_SESSION['user_id'])) {
    header('Location: ' . ($_SESSION['role'] === 'admin' ? 'admin/dashboard.php' : 'user/dashboard.php'));
    exit;
}

$error_message = '';
$success_message = '';
$email_value = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $email_value = htmlspecialchars($email);
    
    if (empty($email)) {
        $error_message = "Email harus diisi";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Format email tidak valid";
    } else {
        try {
            require_once '../php/config/database.php';
            
            $stmt = $pdo->prepare("SELECT id, nama_lengkap, email FROM users WHERE email = ?  LIMIT 1");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user) {
                $token = bin2hex(random_bytes(32));
                $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
                
                // Cek table
                $checkTable = $pdo->query("SHOW TABLES LIKE 'password_resets'")->rowCount();
                if ($checkTable == 0) {
                    $pdo->exec("CREATE TABLE password_resets (
                        id INT PRIMARY KEY AUTO_INCREMENT,
                        user_id INT NOT NULL,
                        token VARCHAR(64) NOT NULL,
                        expires_at DATETIME NOT NULL,
                        used TINYINT(1) DEFAULT 0,
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
                    )");
                }
                
                $pdo->prepare("DELETE FROM password_resets WHERE user_id = ?")->execute([$user['id']]);
                
                $stmt = $pdo->prepare("INSERT INTO password_resets (user_id, token, expires_at) VALUES (?, ?, ?)");
                $stmt->execute([$user['id'], $token, $expires]);
                
                $success_message = "Link reset password telah dikirim ke email Anda.<br><small>Demo: <a href='reset-password.php?token={$token}' style='color:#d50000;'>Klik di sini</a></small>";
            } else {
                $success_message = "Jika email terdaftar, link akan dikirim.";
            }
        } catch (Exception $e) {
            $error_message = "Terjadi kesalahan sistem.";
        }
    }
}

$page_title = "Lupa Password - EzRent";
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title><?php echo $page_title; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; background: #fff; min-height: 100vh; }
    
    .forgot-wrapper { display: flex; min-height: 100vh; }
    
    /* LEFT VISUAL SIDE */
    .forgot-visual {
        width: 50%; position: fixed; top: 0; left: 0; bottom: 0;
        display: flex; align-items: center; justify-content: center; overflow: hidden;
        background: linear-gradient(135deg, #0a0a0a 0%, #1a1a1a 50%, #0d0d0d 100%);
    }
    
    .forgot-visual::before {
        content: ''; position: absolute; inset: 0;
        background-image: 
            radial-gradient(circle at 20% 50%, rgba(213,0,0,0.15) 0%, transparent 50%),
            radial-gradient(circle at 80% 80%, rgba(255,82,82,0.1) 0%, transparent 50%),
            radial-gradient(circle at 40% 20%, rgba(255,255,255,0.03) 0%, transparent 50%);
        animation: gradientShift 15s ease infinite;
        z-index: 1;
    }
    
    .forgot-visual::after {
        content: ''; position: absolute; inset: 0;
        background-image: 
            linear-gradient(0deg, transparent 24%, rgba(255,82,82,0.05) 25%, rgba(255,82,82,0.05) 26%, transparent 27%, transparent 74%, rgba(255,82,82,0.05) 75%, rgba(255,82,82,0.05) 76%, transparent 77%, transparent),
            linear-gradient(90deg, transparent 24%, rgba(255,82,82,0.05) 25%, rgba(255,82,82,0.05) 26%, transparent 27%, transparent 74%, rgba(255,82,82,0.05) 75%, rgba(255,82,82,0.05) 76%, transparent 77%, transparent);
        background-size: 100px 100px;
        z-index: 2; opacity: 0.3;
        animation: gridMove 20s linear infinite;
    }
    
    @keyframes gradientShift { 0%, 100% { filter: hue-rotate(0deg); } 50% { filter: hue-rotate(5deg); } }
    @keyframes gridMove { 0% { background-position: 0 0; } 100% { background-position: 100px 100px; } }
    
    .particle { position: absolute; border-radius: 50%; pointer-events: none; animation: float 20s infinite ease-in-out; }
    @keyframes float {
        0%, 100% { transform: translateY(0) translateX(0); opacity: 0; }
        10% { opacity: 1; }
        90% { opacity: 1; }
        100% { transform: translateY(-300px) translateX(100px); opacity: 0; }
    }
    
    .decor-circle { position: absolute; border-radius: 50%; border: 2px solid rgba(213,0,0,0.2); pointer-events: none; opacity: 0.5; }
    .decor-circle.c1 { width: 400px; height: 400px; top: -200px; left: -200px; animation: rotateSlow 30s linear infinite; }
    .decor-circle.c2 { width: 300px; height: 300px; bottom: -150px; right: -150px; animation: rotateSlow 25s linear infinite reverse; }
    .decor-circle.c3 { width: 250px; height: 250px; top: 50%; left: 50%; transform: translate(-50%, -50%); border-color: rgba(255,255,255,0.08); animation: rotateSlow 40s linear infinite; }
    
    @keyframes rotateSlow { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
    
    .visual-content { position: relative; z-index: 10; text-align: center; padding: 3rem; max-width: 500px; }
    
    .visual-logo { margin-bottom: 2rem; animation: slideInDown 1s ease 0.3s forwards; opacity: 0; }
    @keyframes slideInDown { from { opacity: 0; transform:  translateY(-30px); } to { opacity: 1; transform: translateY(0); } }
    
    .visual-logo-text { font-size: 4rem; font-weight: 700; letter-spacing: -0.03em; line-height: 1; }
    .visual-logo-text .ez { color: #fff; }
    .visual-logo-text .rent { color: #d50000; }
    .visual-logo-text .dot { color: #d50000; }
    
    .visual-tagline { color: rgba(255,255,255,0.4); font-size: 1rem; letter-spacing: 0.2em; text-transform: uppercase; margin-top: 1rem; animation: fadeIn 1s ease 0.6s forwards; opacity: 0; }
    @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
    
    /* RIGHT FORM SIDE */
    .forgot-form-side {
        width: 50%; margin-left: 50%;
        background: #fff; display: flex; align-items: center; justify-content: center;
        padding: 4rem 3rem; min-height: 100vh;
    }
    
    .form-container { width: 100%; max-width:  400px; opacity: 0; transform: translateY(30px); animation: formFadeUp 0.8s 0.3s forwards; }
    @keyframes formFadeUp { to { opacity: 1; transform:  translateY(0); } }
    
    .form-header { margin-bottom: 2.5rem; }
    .form-header h1 { font-size: 1.75rem; font-weight: 600; color: #000; margin-bottom: 0.5rem; letter-spacing: -0.02em; }
    .form-header p { color: #6b7280; font-size: 0.95rem; line-height: 1.6; }
    
    .alert { padding: 1rem 1.25rem; border-radius: 12px; margin-bottom: 1.5rem; font-size: 0.9rem; }
    .alert-error { background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; }
    .alert-success { background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; }
    
    .form-group { margin-bottom: 1.5rem; }
    .form-label { display: block; font-size: 0.85rem; font-weight: 500; color:  #374151; margin-bottom: 0.5rem; }
    
    .input-wrapper { position: relative; }
    .input-icon { position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); width: 18px; height: 18px; color: #9ca3af; pointer-events: none; transition: color 0.3s; }
    
    .form-input {
        width: 100%; padding: 0.875rem 1rem 0.875rem 2.75rem;
        border: 2px solid #e5e7eb; background: #fff; font-size: 0.95rem; font-family: inherit;
        transition: all 0.3s; color: #000;
    }
    .form-input:focus { outline: none; border-color:  #d50000; box-shadow: 0 0 0 4px rgba(213,0,0,0.1); }
    .form-input:focus + .input-icon { color: #000; }
    .form-input::placeholder { color:  #9ca3af; }
    
    .btn-submit {
        width: 100%; padding: 1rem; background: linear-gradient(135deg, #d50000 0%, #b71c1c 100%);
        color: #fff; border: none; border-radius: 12px; font-size: 1rem; font-weight: 600;
        cursor: pointer; transition:  all 0.3s; text-transform: uppercase; letter-spacing: 0.1em;
    }
    .btn-submit:hover { transform: translateY(-2px); box-shadow: 0 10px 30px rgba(213,0,0,0.3); }
    
    .form-footer { text-align: center; margin-top: 2rem; padding-top: 2rem; border-top: 1px solid #e5e7eb; }
    .form-footer p { color: #6b7280; font-size: 0.9rem; }
    .form-footer a { color: #d50000; text-decoration: none; font-weight: 600; }
    .form-footer a:hover { text-decoration: underline; }
    
    .mobile-logo-header { display: none; background: linear-gradient(135deg, #0a0a0a 0%, #1a1a1a 100%); padding: 3rem 1.5rem; text-align: center; }
    .mobile-logo-header .visual-logo-text { font-size: 2.5rem; }
    .mobile-logo-header .visual-tagline { font-size: 0.75rem; margin-top: 0.5rem; }
    
    .forgot-header { position: fixed; top: 0; right: 0; width: 50%; z-index: 100; padding: 1.5rem 2rem; display: flex; justify-content: flex-end; align-items: center; background: transparent; }
    .back-link { display: flex; align-items: center; gap: 0.5rem; color: #6b7280; text-decoration:  none; font-size: 0.9rem; transition: color 0.3s; }
    .back-link:hover { color: #000; }
    .back-link svg { width: 16px; height: 16px; transition: transform 0.3s; }
    .back-link:hover svg { transform: translateX(-3px); }
    
    @media (max-width: 768px) {
        .forgot-visual { display: none ! important; }
        .mobile-logo-header { display: block; }
        .forgot-form-side { width: 100%; margin-left: 0; padding: 2rem; min-height: auto; }
        .forgot-header { width: 100%; position: relative; background: #0a0a0a ! important; }
        .mobile-logo-header { background: #0a0a0a !important; }
        .back-link { color: rgba(255,255,255,0.7); }
        .back-link:hover { color: #fff; }
    }
    
    @media (max-width: 640px) {
        .forgot-header { padding: 1rem 1.25rem; }
        .back-link span { display: none; }
        .forgot-form-side { padding: 1.5rem 1.25rem 2rem; }
        .form-container { max-width: 100%; }
        .form-header h1 { font-size: 1.5rem; }
        .mobile-logo-header { padding: 2rem 1.25rem; }
        .mobile-logo-header .visual-logo-text { font-size: 2rem; }
    }
    </style>
</head>
<body>

<header class="forgot-header">
    <a href="login.php" class="back-link">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        <span>Kembali ke Login</span>
    </a>
</header>

<div class="mobile-logo-header">
    <div class="visual-logo">
        <div class="visual-logo-text">
            <span class="ez">Ez</span><span class="rent">Rent</span><span class="dot">.</span>
        </div>
        <div class="visual-tagline">Reset Password</div>
    </div>
</div>

<div class="forgot-wrapper">
    <div class="forgot-visual" id="forgotVisual">
        <div class="decor-circle c1"></div>
        <div class="decor-circle c2"></div>
        <div class="decor-circle c3"></div>
        
        <div class="visual-content">
            <div class="visual-logo">
                <div class="visual-logo-text">
                    <span class="ez">Ez</span><span class="rent">Rent</span><span class="dot">.</span>
                </div>
                <div class="visual-tagline">Pulihkan Akses</div>
            </div>
        </div>
    </div>
    
    <div class="forgot-form-side">
        <div class="form-container">
            <div class="form-header">
                <h1>Lupa Password? </h1>
                <p>Masukkan email yang terdaftar dan kami akan mengirimkan link untuk reset password Anda.</p>
            </div>

            <?php if ($error_message): ?>
                <div class="alert alert-error"><?php echo $error_message; ?></div>
            <?php endif; ?>

            <?php if ($success_message): ?>
                <div class="alert alert-success"><?php echo $success_message; ?></div>
            <?php else: ?>

                <form method="POST" action="">
                    <div class="form-group">
                        <label class="form-label">Email</label>
                        <div class="input-wrapper">
                            <input type="email" name="email" class="form-input" placeholder="nama@email.com" value="<?php echo $email_value; ?>" required autofocus>
                            <svg class="input-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                        </div>
                    </div>

                    <button type="submit" class="btn-submit">Kirim Link Reset</button>
                </form>

            <?php endif; ?>

            <div class="form-footer">
                <p>Sudah ingat password? <a href="login.php">Login di sini</a></p>
                <p style="margin-top: 0.75rem;">Belum punya akun? <a href="register.php">Daftar sekarang</a></p>
            </div>
        </div>
    </div>
</div>

<script>
function createParticles() {
    const visual = document.getElementById('forgotVisual');
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
        particle.style. background = `rgba(${Math.random() > 0.5 ? '213,0,0' : '255,255,255'}, ${Math.random() * 0.3 + 0.2})`;
        particle.style.animationDelay = delay + 's';
        particle. style.animationDuration = duration + 's';
        visual.appendChild(particle);
    }
}
document.addEventListener('DOMContentLoaded', createParticles);
</script>

</body>
</html>