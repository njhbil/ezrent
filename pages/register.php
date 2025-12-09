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

<style>
@media (max-width: 768px) {
    .register-header {
        width: 100%;
        position: relative;
        background: #0a0a0a !important;
    }
    .mobile-logo-header {
        background: #0a0a0a !important;
        margin-top: 3.5rem !important;
    }
    .mobile-logo-header .visual-logo-text {
        margin-top: 0.5rem !important;
    }
    header {
        background: #0a0a0a !important;
        box-shadow: none !important;
    }
}
</style>
<?php 
$page_title = "Daftar Akun - EzRent";

// Handle form submission
$success_message = '';
$error_message = '';
$form_data = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize input data
    $nama_lengkap = trim($_POST['nama_lengkap'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $nomor_telepon = trim($_POST['nomor_telepon'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $alamat = trim($_POST['alamat'] ?? '');
    
    // Store form data for repopulation
    $form_data = [
        'nama_lengkap' => $nama_lengkap,
        'email' => $email,
        'nomor_telepon' => $nomor_telepon,
        'alamat' => $alamat
    ];
    
    // Validation
    $errors = [];
    
    if (empty($nama_lengkap)) {
        $errors[] = "Nama lengkap harus diisi";
    } elseif (strlen($nama_lengkap) < 2) {
        $errors[] = "Nama lengkap minimal 2 karakter";
    }
    
    if (empty($email)) {
        $errors[] = "Email harus diisi";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Format email tidak valid";
    }
    
    if (empty($nomor_telepon)) {
        $errors[] = "Nomor telepon harus diisi";
    } elseif (!preg_match('/^[0-9+\-\s()]{10,15}$/', $nomor_telepon)) {
        $errors[] = "Format nomor telepon tidak valid";
    }
    
    if (empty($password)) {
        $errors[] = "Password harus diisi";
    } elseif (strlen($password) < 6) {
        $errors[] = "Password minimal 6 karakter";
    }
    
    if ($password !== $confirm_password) {
        $errors[] = "Konfirmasi password tidak sesuai";
    }
    
    if (empty($errors)) {
        try {
            // Check if email already exists
            require_once '../php/config/database.php';
            
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $existing_user = $stmt->fetch();
            
            if ($existing_user) {
                $error_message = "Email sudah terdaftar. Silakan gunakan email lain.";
            } else {
                // Hash password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                // Insert new user dengan struktur tabel yang sesuai
                $stmt = $pdo->prepare("
                    INSERT INTO users (nama_lengkap, email, password, nomor_telepon, alamat, role, is_verified) 
                    VALUES (?, ?, ?, ?, ?, 'user', TRUE)
                ");
                $stmt->execute([$nama_lengkap, $email, $hashed_password, $nomor_telepon, $alamat]);
                
                $success_message = "Pendaftaran berhasil! Silakan login untuk melanjutkan.";
                
                // Clear form data
                $form_data = [];
            }
            
        } catch (PDOException $e) {
            $error_message = "Terjadi kesalahan sistem: " . $e->getMessage();
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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="../assets/css/responsive.css" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: #0a0a0a;
            min-height: 100vh;
            overflow-x: hidden;
        }
        
        /* Header */
        .register-header {
            position: fixed;
            top: 0;
            right: 0;
            width: 50%;
            z-index: 100;
            padding: 1.5rem 2rem;
            display: flex;
            justify-content: flex-end;
            align-items: center;
            background: transparent;
        }
        @media (max-width: 768px) {
            .register-header { display: none !important; }
            .register-visual .visual-logo, .register-visual .visual-tagline { display: none !important; }
        }
        .back-link {
            color: #6b7280;
            text-decoration: none;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s;
        }
        .back-link:hover { 
            color: #000;
        }
        .back-link svg {
            width: 16px;
            height: 16px;
            transition: transform 0.3s;
        }
        .back-link:hover svg {
            transform: translateX(-3px);
        }
        
        /* Main Layout */
        .register-wrapper {
            display: flex;
            min-height: 100vh;
        }
        
        /* Left Side - Visual (Fixed) */
        .register-visual {
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
        .register-visual::before {
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
        .register-form-side {
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
            max-width: 420px;
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
        
        /* Success Message */
        .success-box {
            background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);
            border: 1px solid #10b981;
            padding: 2rem;
            text-align: center;
            margin-bottom: 1.5rem;
            animation: scaleIn 0.5s ease;
        }
        @keyframes scaleIn {
            from { transform: scale(0.9); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }
        .success-box .icon {
            width: 60px;
            height: 60px;
            background: #10b981;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.25rem;
        }
        .success-box .icon svg {
            width: 30px;
            height: 30px;
            color: #fff;
        }
        .success-box h3 {
            color: #065f46;
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        .success-box p {
            color: #047857;
            margin-bottom: 1.5rem;
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
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }
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
        .form-input.no-icon {
            padding-left: 1rem;
        }
        
        textarea.form-input {
            resize: vertical;
            min-height: 80px;
            padding: 0.875rem 1rem;
        }
        
        .form-hint {
            font-size: 0.75rem;
            color: #9ca3af;
            margin-top: 0.35rem;
        }
        
        /* Password strength */
        .password-strength {
            display: flex;
            gap: 4px;
            margin-top: 0.5rem;
        }
        .strength-bar {
            flex: 1;
            height: 3px;
            background: #e5e7eb;
            transition: background 0.3s;
        }
        .strength-bar.active.weak { background: #dc2626; }
        .strength-bar.active.medium { background: #f59e0b; }
        .strength-bar.active.strong { background: #10b981; }
        
        /* Password Match */
        .password-match {
            font-size: 0.8rem;
            margin-top: 0.35rem;
            display: flex;
            align-items: center;
            gap: 0.35rem;
            opacity: 0;
            transform: translateY(-5px);
            transition: all 0.3s;
        }
        .password-match.visible {
            opacity: 1;
            transform: translateY(0);
        }
        .password-match.match { color: #10b981; }
        .password-match.no-match { color: #dc2626; }
        .password-match svg {
            width: 14px;
            height: 14px;
        }
        
        /* Checkbox */
        .checkbox-group {
            display: flex;
            align-items: flex-start;
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
        .checkbox-wrapper input:checked ~ .checkbox-custom {
            background: #000;
            border-color: #000;
        }
        .checkbox-wrapper input:checked ~ .checkbox-custom svg {
            opacity: 1;
            transform: scale(1);
        }
        .checkbox-label {
            font-size: 0.85rem;
            color: #6b7280;
            line-height: 1.5;
        }
        .checkbox-label a {
            color: #000;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s;
        }
        .checkbox-label a:hover {
            color: #d50000;
        }
        
        /* Submit Button */
        .btn-register {
            width: 100%;
            padding: 1rem;
            background: #000;
            color: #fff;
            border: none;
            font-size: 0.9rem;
            font-weight: 600;
            font-family: inherit;
            cursor: pointer;
            position: relative;
            overflow: hidden;
            transition: all 0.4s;
        }
        .btn-register::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }
        .btn-register:hover::before {
            left: 100%;
        }
        .btn-register:hover {
            background: #1a1a1a;
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        .btn-register:active {
            transform: translateY(0);
        }
        .btn-register:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
        }
        .btn-register .spinner {
            display: none;
            width: 18px;
            height: 18px;
            border: 2px solid rgba(255,255,255,0.3);
            border-top-color: #fff;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
            margin-right: 0.5rem;
        }
        .btn-register.loading .spinner {
            display: inline-block;
        }
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        .btn-login-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            background: #000;
            color: #fff;
            text-decoration: none;
            padding: 0.875rem 2rem;
            font-weight: 500;
            font-size: 0.9rem;
            transition: all 0.3s;
        }
        .btn-login-link:hover {
            background: #1a1a1a;
            transform: translateY(-2px);
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
            .register-visual {
                display: none !important;
            }
            .register-visual .visual-logo,
            .register-visual .visual-tagline {
                display: none !important;
            }
            .mobile-logo-header {
                display: block;
            }
            .register-form-side {
                width: 100%;
                margin-left: 0;
                padding: 2rem;
                min-height: auto;
            }
            .register-header {
                position: relative;
                background: #0a0a0a;
            }
        }
        
        @media (max-width: 640px) {
            .register-header {
                padding: 1rem 1.25rem;
            }
            .logo { font-size: 1.25rem; }
            .back-link span { display: none; }
            .register-form-side {
                padding: 1.5rem 1.25rem 2rem;
            }
            .form-container {
                max-width: 100%;
            }
            .form-header h1 {
                font-size: 1.5rem;
            }
            .form-row {
                grid-template-columns: 1fr;
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

<!-- Header -->
<header class="register-header" id="header">
    <a href="index.php" class="back-link">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        <span>Kembali ke Beranda</span>
    </a>
</header>


<div class="register-wrapper">
    <!-- Left Side - Visual (Fixed) -->
    <div class="register-visual">
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
    <div class="register-form-side">
        <div class="form-container">
            <div class="form-header">
                <h1>Buat Akun Baru</h1>
                <p>Isi data diri Anda untuk mendaftar</p>
            </div>
            
            <?php if ($success_message): ?>
                <div class="success-box">
                    <div class="icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <h3>Pendaftaran Berhasil!</h3>
                    <p><?php echo $success_message; ?></p>
                    <a href="login.php" class="btn-login-link">
                        Login Sekarang
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                        </svg>
                    </a>
                </div>
            <?php endif; ?>

            <?php if ($error_message && !$success_message): ?>
                <div class="error-box">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span><?php echo $error_message; ?></span>
                </div>
            <?php endif; ?>

            <?php if (!$success_message): ?>
            <form method="POST" action="" id="registrationForm">
                <div class="form-group">
                    <label class="form-label">Nama Lengkap <span class="required">*</span></label>
                    <div class="input-wrapper">
                        <input type="text" name="nama_lengkap" required 
                               value="<?php echo htmlspecialchars($form_data['nama_lengkap'] ?? ''); ?>"
                               class="form-input"
                               placeholder="Masukkan nama lengkap"
                               minlength="2" maxlength="100">
                        <svg class="input-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Email <span class="required">*</span></label>
                        <div class="input-wrapper">
                            <input type="email" name="email" required 
                                   value="<?php echo htmlspecialchars($form_data['email'] ?? ''); ?>"
                                   class="form-input"
                                   placeholder="email@contoh.com"
                                   maxlength="100">
                            <svg class="input-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">No. Telepon <span class="required">*</span></label>
                        <div class="input-wrapper">
                            <input type="tel" name="nomor_telepon" required 
                                   value="<?php echo htmlspecialchars($form_data['nomor_telepon'] ?? ''); ?>"
                                   class="form-input"
                                   placeholder="081234567890"
                                   pattern="[0-9+\-\s()]{10,15}" maxlength="15">
                            <svg class="input-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Password <span class="required">*</span></label>
                    <div class="input-wrapper">
                        <input type="password" name="password" required 
                               class="form-input"
                               placeholder="Minimal 6 karakter"
                               minlength="6" id="password">
                        <svg class="input-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                    </div>
                    <div class="password-strength" id="strengthBars">
                        <div class="strength-bar"></div>
                        <div class="strength-bar"></div>
                        <div class="strength-bar"></div>
                        <div class="strength-bar"></div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Konfirmasi Password <span class="required">*</span></label>
                    <div class="input-wrapper">
                        <input type="password" name="confirm_password" required 
                               class="form-input"
                               placeholder="Ketik ulang password"
                               id="confirm_password">
                        <svg class="input-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                    </div>
                    <div class="password-match" id="passwordMatch">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span></span>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Alamat <span style="color: #9ca3af; font-weight: 400;">(opsional)</span></label>
                    <textarea name="alamat" class="form-input no-icon"
                              placeholder="Masukkan alamat lengkap"><?php echo htmlspecialchars($form_data['alamat'] ?? ''); ?></textarea>
                </div>

                <div class="checkbox-group">
                    <div class="checkbox-wrapper">
                        <input type="checkbox" name="agree_terms" required id="agree_terms">
                        <div class="checkbox-custom">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                    </div>
                    <span class="checkbox-label">
                        Saya menyetujui <a href="terms.php">Syarat & Ketentuan</a> 
                        dan <a href="#">Kebijakan Privasi</a> yang berlaku
                    </span>
                </div>

                <button type="submit" class="btn-register" id="submitBtn">
                    <span class="spinner"></span>
                    <span class="btn-text">Daftar Sekarang</span>
                </button>
            </form>
            <?php endif; ?>

            <div class="divider">
                <p>Sudah punya akun? <a href="login.php">Login di sini</a></p>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const passwordInput = document.getElementById('password');
    const confirmInput = document.getElementById('confirm_password');
    const passwordMatch = document.getElementById('passwordMatch');
    const strengthBars = document.querySelectorAll('.strength-bar');
    const form = document.getElementById('registrationForm');
    const submitBtn = document.getElementById('submitBtn');
    const header = document.getElementById('header');

    // Header scroll effect
    window.addEventListener('scroll', () => {
        if (window.scrollY > 50) {
            header.classList.add('scrolled');
        } else {
            header.classList.remove('scrolled');
        }
    });

    // Password strength checker
    function checkStrength(password) {
        let strength = 0;
        if (password.length >= 6) strength++;
        if (password.length >= 8) strength++;
        if (/[A-Z]/.test(password)) strength++;
        if (/[0-9]/.test(password) && /[a-zA-Z]/.test(password)) strength++;
        return strength;
    }

    if (passwordInput) {
        passwordInput.addEventListener('input', function() {
            const strength = checkStrength(this.value);
            strengthBars.forEach((bar, index) => {
                bar.classList.remove('active', 'weak', 'medium', 'strong');
                if (index < strength) {
                    bar.classList.add('active');
                    if (strength <= 1) bar.classList.add('weak');
                    else if (strength <= 2) bar.classList.add('medium');
                    else bar.classList.add('strong');
                }
            });
            checkPasswordMatch();
        });
    }

    function checkPasswordMatch() {
        if (!passwordInput || !confirmInput || !passwordMatch) return;
        
        const password = passwordInput.value;
        const confirmPassword = confirmInput.value;
        const matchSpan = passwordMatch.querySelector('span');
        const matchIcon = passwordMatch.querySelector('svg');

        if (confirmPassword === '') {
            passwordMatch.classList.remove('visible', 'match', 'no-match');
        } else if (password === confirmPassword) {
            passwordMatch.classList.add('visible', 'match');
            passwordMatch.classList.remove('no-match');
            matchSpan.textContent = 'Password cocok';
            matchIcon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>';
        } else {
            passwordMatch.classList.add('visible', 'no-match');
            passwordMatch.classList.remove('match');
            matchSpan.textContent = 'Password tidak cocok';
            matchIcon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>';
        }
    }

    if (confirmInput) {
        confirmInput.addEventListener('input', checkPasswordMatch);
    }

    // Form submission
    if (form) {
        form.addEventListener('submit', function(e) {
            const password = passwordInput.value;
            const confirmPassword = confirmInput.value;
            const agreeTerms = document.getElementById('agree_terms').checked;

            if (password.length < 6) {
                e.preventDefault();
                alert('Password harus minimal 6 karakter');
                passwordInput.focus();
                return false;
            }

            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Konfirmasi password tidak sesuai');
                confirmInput.focus();
                return false;
            }

            if (!agreeTerms) {
                e.preventDefault();
                alert('Anda harus menyetujui Syarat & Ketentuan');
                return false;
            }

            submitBtn.disabled = true;
            submitBtn.classList.add('loading');
            submitBtn.querySelector('.btn-text').textContent = 'Mendaftarkan...';
        });
    }

    // Input animations
    document.querySelectorAll('.form-input').forEach(input => {
        input.addEventListener('focus', function() {
            this.parentElement.classList.add('focused');
        });
        input.addEventListener('blur', function() {
            this.parentElement.classList.remove('focused');
        });
    });
});
</script>

</body>
</html>