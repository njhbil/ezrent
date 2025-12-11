<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['user_id'])) {
    header('Location: ' . ($_SESSION['role'] === 'admin' ? 'admin/dashboard.php' : 'user/dashboard.php'));
    exit;
}

$error_message = '';
$success_message = '';
$token_valid = false;
$token = isset($_GET['token']) ? trim($_GET['token']) : '';

if (empty($token)) {
    $error_message = "Token tidak valid. Silakan minta link reset password baru.";
} else {
    try {
        require_once '../php/config/database.php';
        
        $stmt = $pdo->prepare("SELECT pr.*, u.email, u.nama_lengkap 
                              FROM password_resets pr 
                              JOIN users u ON pr.user_id = u.id 
                              WHERE pr.token = ? AND pr.used = 0 AND pr.expires_at > NOW() 
                              LIMIT 1");
        $stmt->execute([$token]);
        $reset_data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($reset_data) {
            $token_valid = true;
        } else {
            $error_message = "Link reset password tidak valid atau sudah kadaluarsa. Silakan minta link baru.";
        }
        
    } catch (PDOException $e) {
        $error_message = "Terjadi kesalahan sistem. Silakan coba lagi.";
        error_log("Reset Password Error: " . $e->getMessage());
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $token_valid) {
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';
    
    if (empty($password) || empty($confirm_password)) {
        $error_message = "Semua field harus diisi";
    } elseif (strlen($password) < 6) {
        $error_message = "Password minimal 6 karakter";
    } elseif ($password !== $confirm_password) {
        $error_message = "Password dan konfirmasi password tidak sama";
    } else {
        try {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([$hashed_password, $reset_data['user_id']]);
            
            $stmt = $pdo->prepare("UPDATE password_resets SET used = 1 WHERE id = ?");
            $stmt->execute([$reset_data['id']]);
            
            $success_message = "Password berhasil diubah! Silakan login dengan password baru Anda.";
            $token_valid = false;
            
        } catch (PDOException $e) {
            $error_message = "Gagal mengubah password. Silakan coba lagi.";
            error_log("Reset Password Update Error: " . $e->getMessage());
        }
    }
}

$page_title = "Reset Password - EzRent";
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title><?php echo $page_title; ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="../assets/css/responsive.css" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: #fff;
            color: #000;
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
        }
        
        .reset-header {
            position: fixed;
            top: 0;
            right: 0;
            width: 50%;
            padding: 1.5rem 2rem;
            display: flex;
            justify-content: flex-end;
            align-items: center;
            z-index: 100;
            background: transparent;
        }
        .back-link {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #6b7280;
            text-decoration: none;
            font-size: 0.9rem;
            transition: color 0.3s;
        }
        .back-link:hover { color: #000; }
        .back-link svg { width: 16px; height: 16px; transition: transform 0.3s; }
        .back-link:hover svg { transform: translateX(-3px); }
        
        .reset-wrapper { display: flex; min-height: 100vh; }
        
        .reset-visual {
            width: 50%;
            background: linear-gradient(135deg, #0a0a0a 0%, #1a1a1a 100%);
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }
        .reset-visual::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Ccircle cx='30' cy='30' r='1' fill='rgba(255,255,255,0.03)'/%3E%3C/svg%3E");
        }
        .visual-content {
            position: relative;
            z-index: 2;
            text-align: center;
            padding: 3rem;
            max-width: 500px;
        }
        .visual-logo { margin-bottom: 2rem; }
        .visual-logo-text {
            font-size: 4rem;
            font-weight: 700;
            letter-spacing: -0.03em;
            line-height: 1;
        }
        .visual-logo-text .ez { color: #fff; }
        .visual-logo-text .rent { color: #d50000; }
        .visual-logo-text .dot { color: #d50000; }
        .visual-tagline {
            color: rgba(255,255,255,0.4);
            font-size: 1rem;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            margin-top: 1rem;
        }
        .visual-icon {
            width: 120px;
            height: 120px;
            background: rgba(213,0,0,0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 2rem auto 0;
        }
        .visual-icon svg {
            width: 60px;
            height: 60px;
            color: #d50000;
        }
        
        .reset-form-side {
            width: 50%;
            margin-left: 50%;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            background: #fff;
        }
        
        .form-container { width: 100%; max-width: 400px; }
        .form-header { margin-bottom: 2rem; }
        .form-title {
            font-size: 2rem;
            font-weight: 700;
            color: #000;
            letter-spacing: -0.02em;
            margin-bottom: 0.5rem;
        }
        .form-subtitle {
            color: #6b7280;
            font-size: 0.95rem;
            line-height: 1.6;
        }
        
        .alert {
            padding: 1rem 1.25rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
        }
        .alert-error {
            background: #fef2f2;
            color: #dc2626;
            border: 1px solid #fecaca;
        }
        .alert-success {
            background: #f0fdf4;
            color: #166534;
            border: 1px solid #bbf7d0;
        }
        
        .form-group { margin-bottom: 1.5rem; }
        .form-label {
            display: block;
            font-size: 0.85rem;
            font-weight: 500;
            color: #374151;
            margin-bottom: 0.5rem;
        }
        .form-input {
            width: 100%;
            padding: 0.875rem 1rem;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.3s;
            background: #fff;
        }
        .form-input:focus {
            outline: none;
            border-color: #d50000;
            box-shadow: 0 0 0 4px rgba(213,0,0,0.1);
        }
        
        .password-wrapper {
            position: relative;
        }
        .password-toggle {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            color: #9ca3af;
            padding: 0;
        }
        .password-toggle:hover { color: #6b7280; }
        .password-toggle svg { width: 20px; height: 20px; }
        
        .btn-submit {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(135deg, #d50000 0%, #b71c1c 100%);
            color: #fff;
            border: none;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(213,0,0,0.3);
        }
        
        .btn-link {
            display: inline-block;
            padding: 1rem 2rem;
            background: linear-gradient(135deg, #d50000 0%, #b71c1c 100%);
            color: #fff;
            text-decoration: none;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            transition: all 0.3s;
        }
        .btn-link:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(213,0,0,0.3);
            color: #fff;
        }
        
        .form-footer {
            text-align: center;
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid #e5e7eb;
        }
        .form-footer p {
            color: #6b7280;
            font-size: 0.9rem;
        }
        .form-footer a {
            color: #d50000;
            text-decoration: none;
            font-weight: 600;
        }
        .form-footer a:hover { text-decoration: underline; }
        
        .password-requirements {
            margin-top: 0.5rem;
            font-size: 0.8rem;
            color: #9ca3af;
        }
        
        @media (max-width: 768px) {
            .reset-header { display: none; }
            .reset-wrapper { display: block; }
            .reset-visual {
                width: 100%;
                position: relative;
                height: 200px;
            }
            .visual-logo-text { font-size: 2.5rem; }
            .visual-tagline { font-size: 0.8rem; }
            .visual-icon { display: none; }
            .reset-form-side {
                width: 100%;
                margin-left: 0;
                min-height: auto;
                padding: 2rem 1.5rem 3rem;
            }
            .form-title { font-size: 1.5rem; }
            .mobile-back {
                display: block;
                margin-bottom: 1.5rem;
            }
            .mobile-back a {
                color: #6b7280;
                text-decoration: none;
                font-size: 0.9rem;
                display: inline-flex;
                align-items: center;
                gap: 0.5rem;
            }
        }
        @media (min-width: 769px) {
            .mobile-back { display: none; }
        }
    </style>
</head>
<body>
    <header class="reset-header">
        <a href="login.php" class="back-link">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Kembali ke Login
        </a>
    </header>

    <div class="reset-wrapper">
        <div class="reset-visual">
            <div class="visual-content">
                <div class="visual-logo">
                    <div class="visual-logo-text">
                        <span class="ez">Ez</span><span class="rent">Rent</span><span class="dot">.</span>
                    </div>
                    <div class="visual-tagline">Password Baru</div>
                </div>
                <div class="visual-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="reset-form-side">
            <div class="form-container">
                <div class="mobile-back">
                    <a href="login.php">← Kembali ke Login</a>
                </div>
                
                <div class="form-header">
                    <h1 class="form-title">Reset Password</h1>
                    <?php if ($token_valid): ?>
                    <p class="form-subtitle">Hai <?php echo htmlspecialchars($reset_data['nama_lengkap']); ?>, silakan masukkan password baru Anda.</p>
                    <?php else: ?>
                    <p class="form-subtitle">Buat password baru untuk akun Anda.</p>
                    <?php endif; ?>
                </div>

                <?php if ($error_message): ?>
                <div class="alert alert-error"><?php echo $error_message; ?></div>
                <?php endif; ?>

                <?php if ($success_message): ?>
                <div class="alert alert-success"><?php echo $success_message; ?></div>
                <div style="text-align: center; margin-top: 1.5rem;">
                    <a href="login.php" class="btn-link">Login Sekarang</a>
                </div>
                <?php elseif ($token_valid): ?>

                <form method="POST" action="">
                    <div class="form-group">
                        <label class="form-label">Password Baru</label>
                        <div class="password-wrapper">
                            <input type="password" name="password" id="password" class="form-input" placeholder="Masukkan password baru" required autofocus>
                            <button type="button" class="password-toggle" onclick="togglePassword('password', this)">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </button>
                        </div>
                        <p class="password-requirements">Minimal 6 karakter</p>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Konfirmasi Password</label>
                        <div class="password-wrapper">
                            <input type="password" name="confirm_password" id="confirm_password" class="form-input" placeholder="Ulangi password baru" required>
                            <button type="button" class="password-toggle" onclick="togglePassword('confirm_password', this)">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="btn-submit">Simpan Password Baru</button>
                </form>
                <?php elseif (!$success_message): ?>
                <div style="text-align: center;">
                    <a href="forgot-password.php" class="btn-link">Minta Link Reset Baru</a>
                </div>
                <?php endif; ?>

                <div class="form-footer">
                    <p>Sudah ingat password? <a href="login.php">Login di sini</a></p>
                </div>
            </div>
        </div>
    </div>

    <script>
    function togglePassword(inputId, button) {
        const input = document.getElementById(inputId);
        if (input.type === 'password') {
            input.type = 'text';
            button.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>';
        } else {
            input.type = 'password';
            button.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>';
        }
    }
    </script>
</body>
</html>
