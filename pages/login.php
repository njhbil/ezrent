<?php 
// Pindahkan session_start() ke paling atas, sebelum output apapun
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Handle form submission - HARUS SEBELUM OUTPUT APAPUN
$error_message = '';
$form_data = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize input data
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    // Store form data for repopulation
    $form_data = ['email' => $email];
    
    // Validation
    if (empty($email) || empty($password)) {
        $error_message = "Email dan password harus diisi";
    } else {
        try {
            require_once '../php/config/database.php';
            
            // Cari user berdasarkan email
            $stmt = $pdo->prepare("
                SELECT id, nama_lengkap, email, password, role, is_verified, nomor_telepon, alamat, foto_profil
                FROM users 
                WHERE email = ?
            ");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user) {
                // Verifikasi password dengan password_verify()
                if (password_verify($password, $user['password'])) {
                    // Password BENAR - set session
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
                    $_SESSION['email'] = $user['email'];
                    $_SESSION['role'] = $user['role'];
                    $_SESSION['is_verified'] = $user['is_verified'];
                    $_SESSION['nomor_telepon'] = $user['nomor_telepon'];
                    $_SESSION['alamat'] = $user['alamat'];
                    $_SESSION['foto_profil'] = $user['foto_profil'];
                    
                    // Redirect berdasarkan role
                    if ($user['role'] === 'admin') {
                        header('Location: admin/dashboard.php');
                        exit;
                    } else {
                        header('Location: user/dashboard.php');
                        exit;
                    }
                    
                } else {
                    $error_message = "Email atau password salah";
                }
            } else {
                $error_message = "Email atau password salah";
            }
            
        } catch (PDOException $e) {
            $error_message = "Terjadi kesalahan sistem: " . $e->getMessage();
        }
    }
}

$page_title = "Login - EzRent";
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
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: #fff;
            color: #000;
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
        }
        
        /* Header */
        .login-header {
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
        .back-link svg {
            width: 16px;
            height: 16px;
            transition: transform 0.3s;
        }
        .back-link:hover svg {
            transform: translateX(-3px);
        }
        
        /* Main Layout */
        .login-wrapper {
            display: flex;
            min-height: 100vh;
        }
        
        /* Left Side - Visual (Fixed) */
        .login-visual {
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
        .login-visual::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Ccircle cx='30' cy='30' r='1' fill='rgba(255,255,255,0.03)'/%3E%3C/svg%3E");
        }
        .visual-content {
            position: relative;
            z-index: 2;
            text-align: center;
            padding: 3rem;
            max-width: 500px;
        }
        
        /* Visual Logo */
        .visual-logo {
            margin-bottom: 2rem;
        }
        .visual-logo-text {
            font-size: 4rem;
            font-weight: 700;
            letter-spacing: -0.03em;
            line-height: 1;
        }
        .visual-logo-text .ez {
            color: #fff;
        }
        .visual-logo-text .rent {
            color: #d50000;
        }
        .visual-logo-text .dot {
            color: #d50000;
        }
        .visual-tagline {
            color: rgba(255,255,255,0.4);
            font-size: 1rem;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            margin-top: 1rem;
        }
        
        /* Decorative elements */
        .decor-circle {
            position: absolute;
            border-radius: 50%;
            border: 1px solid rgba(213,0,0,0.1);
            pointer-events: none;
        }
        .decor-circle.c1 {
            width: 400px;
            height: 400px;
            top: -200px;
            left: -200px;
            animation: rotate 30s linear infinite;
        }
        .decor-circle.c2 {
            width: 300px;
            height: 300px;
            bottom: -150px;
            right: -150px;
            animation: rotate 25s linear infinite reverse;
        }
        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        
        /* Right Side - Form */
        .login-form-side {
            width: 50%;
            margin-left: 50%;
            background: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 4rem 3rem;
            min-height: 100vh;
            position: relative;
        }
        .form-container {
            width: 100%;
            max-width: 400px;
            opacity: 0;
            transform: translateY(30px);
            animation: fadeUp 0.8s 0.3s forwards;
        }
        @keyframes fadeUp {
            to { opacity: 1; transform: translateY(0); }
        }
        
        .form-header {
            margin-bottom: 2.5rem;
        }
        .form-header h1 {
            font-size: 1.75rem;
            font-weight: 600;
            color: #000;
            margin-bottom: 0.5rem;
            letter-spacing: -0.02em;
        }
        .form-header p {
            color: #6b7280;
            font-size: 0.95rem;
        }
        
        /* Error Message */
        .error-box {
            background: #fef2f2;
            border-left: 4px solid #dc2626;
            padding: 1rem 1.25rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            animation: shake 0.5s ease;
        }
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            20%, 60% { transform: translateX(-5px); }
            40%, 80% { transform: translateX(5px); }
        }
        .error-box svg {
            width: 20px;
            height: 20px;
            color: #dc2626;
            flex-shrink: 0;
            margin-top: 2px;
        }
        .error-box span {
            color: #991b1b;
            font-size: 0.9rem;
            line-height: 1.5;
        }
        
        /* Form */
        .form-group {
            margin-bottom: 1.25rem;
            position: relative;
        }
        .form-label {
            display: block;
            font-size: 0.8rem;
            font-weight: 500;
            color: #374151;
            margin-bottom: 0.5rem;
        }
        .form-label .required {
            color: #d50000;
        }
        .input-wrapper {
            position: relative;
        }
        .input-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            width: 18px;
            height: 18px;
            color: #9ca3af;
            pointer-events: none;
            transition: color 0.3s;
        }
        .form-input {
            width: 100%;
            padding: 0.875rem 1rem 0.875rem 2.75rem;
            border: 2px solid #e5e7eb;
            background: #fff;
            font-size: 0.95rem;
            font-family: inherit;
            transition: all 0.3s;
            color: #000;
        }
        .form-input:focus {
            outline: none;
            border-color: #000;
        }
        .form-input:focus + .input-icon,
        .form-input:not(:placeholder-shown) + .input-icon {
            color: #000;
        }
        .form-input::placeholder {
            color: #9ca3af;
        }
        
        /* Forgot Password */
        .forgot-link {
            display: block;
            text-align: right;
            margin-top: 0.5rem;
            font-size: 0.8rem;
            color: #6b7280;
            text-decoration: none;
            transition: color 0.3s;
        }
        .forgot-link:hover {
            color: #000;
        }
        
        /* Remember Me */
        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin: 1.5rem 0;
        }
        .checkbox-wrapper {
            position: relative;
            width: 20px;
            height: 20px;
            flex-shrink: 0;
        }
        .checkbox-wrapper input {
            position: absolute;
            opacity: 0;
            cursor: pointer;
            width: 100%;
            height: 100%;
            z-index: 2;
        }
        .checkbox-custom {
            position: absolute;
            top: 0;
            left: 0;
            width: 20px;
            height: 20px;
            border: 2px solid #d1d5db;
            background: #fff;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .checkbox-custom svg {
            width: 12px;
            height: 12px;
            color: #fff;
            opacity: 0;
            transform: scale(0);
            transition: all 0.2s;
        }
        .checkbox-wrapper input:checked + .checkbox-custom {
            background: #000;
            border-color: #000;
        }
        .checkbox-wrapper input:checked + .checkbox-custom svg {
            opacity: 1;
            transform: scale(1);
        }
        .checkbox-label {
            font-size: 0.9rem;
            color: #6b7280;
            cursor: pointer;
        }
        
        /* Button */
        .btn-login {
            width: 100%;
            padding: 1rem 2rem;
            background: #000;
            color: #fff;
            border: 2px solid #000;
            font-size: 0.9rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            font-family: inherit;
        }
        .btn-login:hover {
            background: transparent;
            color: #000;
        }
        .btn-login:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        .btn-login.loading .btn-text {
            opacity: 0;
        }
        .btn-login .spinner {
            display: none;
            width: 20px;
            height: 20px;
            border: 2px solid transparent;
            border-top-color: currentColor;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }
        .btn-login.loading .spinner {
            display: block;
            position: absolute;
        }
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        /* Divider */
        .divider {
            text-align: center;
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid #e5e7eb;
        }
        .divider p {
            color: #6b7280;
            font-size: 0.9rem;
            margin-bottom: 0.75rem;
        }
        .divider a {
            color: #000;
            font-weight: 600;
            text-decoration: none;
            transition: color 0.3s;
        }
        .divider a:hover {
            color: #d50000;
        }
        
        /* Mobile Logo Header */
        .mobile-logo-header {
            display: none;
            background: linear-gradient(135deg, #0a0a0a 0%, #1a1a1a 100%);
            padding: 3rem 1.5rem;
            text-align: center;
        }
        .mobile-logo-header .visual-logo-text {
            font-size: 2.5rem;
        }
        .mobile-logo-header .visual-tagline {
            font-size: 0.75rem;
            margin-top: 0.5rem;
        }
        
        /* Mobile */
        @media (max-width: 1024px) {
            .login-visual {
                display: none;
            }
            .mobile-logo-header {
                display: block;
            }
            .login-form-side {
                width: 100%;
                margin-left: 0;
                padding: 2rem;
                min-height: auto;
            }
            .login-header {
                width: 100%;
                position: relative;
                background: #0a0a0a;
            }
            .logo-ez { color: #fff; }
            .back-link { color: rgba(255,255,255,0.7); }
            .back-link:hover { color: #fff; }
        }
        
        @media (max-width: 640px) {
            .login-header {
                padding: 1rem 1.25rem;
            }
            .logo { font-size: 1.25rem; }
            .back-link span { display: none; }
            .login-form-side {
                padding: 1.5rem 1.25rem 2rem;
            }
            .form-container {
                max-width: 100%;
            }
            .form-header h1 {
                font-size: 1.5rem;
            }
            .mobile-logo-header {
                padding: 2rem 1.25rem;
            }
            .mobile-logo-header .visual-logo-text {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>

<?php include '../php/includes/header.php'; ?>

<!-- Mobile Logo Header -->
<div class="mobile-logo-header">
    <div class="visual-logo">
        <div class="visual-logo-text">
            <span class="ez">Ez</span><span class="rent">Rent</span><span class="dot">.</span>
        </div>
        <div class="visual-tagline">Rental Kendaraan</div>
    </div>
</div>

<div class="login-wrapper">
    <!-- Left Side - Visual (Fixed) -->
    <div class="login-visual">
        <div class="decor-circle c1"></div>
        <div class="decor-circle c2"></div>
        
        <div class="visual-content">
            <div class="visual-logo">
                <div class="visual-logo-text">
                    <span class="ez">Ez</span><span class="rent">Rent</span><span class="dot">.</span>
                </div>
                <div class="visual-tagline">Rental Kendaraan</div>
            </div>
        </div>
    </div>
    
    <!-- Right Side - Form -->
    <div class="login-form-side">
        <div class="form-container">
            <div class="form-header">
                <h1>Selamat Datang</h1>
                <p>Masuk ke akun Anda untuk melanjutkan</p>
            </div>
            
            <?php if ($error_message): ?>
                <div class="error-box">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span><?php echo htmlspecialchars($error_message); ?></span>
                </div>
            <?php endif; ?>

            <form method="POST" action="" id="loginForm">
                <div class="form-group">
                    <label class="form-label">Email <span class="required">*</span></label>
                    <div class="input-wrapper">
                        <input type="email" name="email" required 
                               value="<?php echo htmlspecialchars($form_data['email'] ?? ''); ?>"
                               class="form-input"
                               placeholder="email@contoh.com"
                               id="email">
                        <svg class="input-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Password <span class="required">*</span></label>
                    <div class="input-wrapper">
                        <input type="password" name="password" required 
                               class="form-input"
                               placeholder="Masukkan password"
                               id="password">
                        <svg class="input-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                    </div>
                    <a href="forgot-password.php" class="forgot-link">Lupa password?</a>
                </div>

                <div class="checkbox-group">
                    <div class="checkbox-wrapper">
                        <input type="checkbox" name="remember_me" id="remember_me">
                        <div class="checkbox-custom">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                    </div>
                    <label class="checkbox-label" for="remember_me">Ingat saya</label>
                </div>

                <button type="submit" class="btn-login" id="submitBtn">
                    <span class="btn-text">Masuk</span>
                    <div class="spinner"></div>
                </button>
            </form>

            <div class="divider">
                <p>Belum punya akun?</p>
                <a href="register.php">Daftar Sekarang →</a>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('loginForm');
    const submitBtn = document.getElementById('submitBtn');
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');

    form.addEventListener('submit', function(e) {
        const email = emailInput.value.trim();
        const password = passwordInput.value;

        if (!email || !password) {
            e.preventDefault();
            alert('Email dan password harus diisi');
            return false;
        }

        submitBtn.disabled = true;
        submitBtn.classList.add('loading');
        submitBtn.querySelector('.btn-text').textContent = 'Memproses...';
    });

    // Input animations
    document.querySelectorAll('.form-input').forEach(input => {
        input.addEventListener('focus', function() {
            this.parentElement.classList.add('focused');
        });
        input.addEventListener('blur', function() {
            this.parentElement.classList.remove('focused');
        });
    });

    emailInput.focus();
});
</script>

</body>
</html>