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
            
            $stmt = $pdo->prepare("SELECT id, nama_lengkap, email FROM users WHERE email = ? LIMIT 1");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user) {
                $token = bin2hex(random_bytes(32));
                $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
                
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
                
                $reset_link = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/reset-password.php?token=" . $token;
                
                $success_message = "Link reset password telah dikirim ke email Anda.<br><br>
                    <small class='text-muted'>Demo: <a href='reset-password.php?token={$token}' class='text-danger'>Klik di sini untuk reset</a></small>";
                
            } else {
                $success_message = "Jika email terdaftar, link reset password akan dikirim.";
            }
            
        } catch (PDOException $e) {
            $error_message = "Terjadi kesalahan sistem. Silakan coba lagi.";
            error_log("Forgot Password Error: " . $e->getMessage());
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
        
        .forgot-header {
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
        
        .forgot-wrapper { display: flex; min-height: 100vh; }
        
        .forgot-visual {
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
        .forgot-visual::before {
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
        
        .forgot-form-side {
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
        
        @media (max-width: 768px) {
            .forgot-header { display: none; }
            .forgot-wrapper { display: block; }
            .forgot-visual {
                width: 100%;
                position: relative;
                height: 200px;
            }
            .visual-logo-text { font-size: 2.5rem; }
            .visual-tagline { font-size: 0.8rem; }
            .visual-icon { display: none; }
            .forgot-form-side {
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
    <header class="forgot-header">
        <a href="login.php" class="back-link">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Kembali ke Login
        </a>
    </header>

    <div class="forgot-wrapper">
        <div class="forgot-visual">
            <div class="visual-content">
                <div class="visual-logo">
                    <div class="visual-logo-text">
                        <span class="ez">Ez</span><span class="rent">Rent</span><span class="dot">.</span>
                    </div>
                    <div class="visual-tagline">Reset Password</div>
                </div>
                <div class="visual-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="forgot-form-side">
            <div class="form-container">
                <div class="mobile-back">
                    <a href="login.php">← Kembali ke Login</a>
                </div>
                
                <div class="form-header">
                    <h1 class="form-title">Lupa Password?</h1>
                    <p class="form-subtitle">Masukkan email yang terdaftar dan kami akan mengirimkan link untuk reset password Anda.</p>
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
                        <input type="email" name="email" class="form-input" placeholder="nama@email.com" value="<?php echo $email_value; ?>" required autofocus>
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
</body>
</html>
