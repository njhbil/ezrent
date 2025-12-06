<?php 
$page_title = "Kontak Kami - EzRent";

// Koneksi database
require_once '../php/config/database.php';

// Simulasi status login dan user data
$is_logged_in = false; // Ganti dengan true jika user sudah login
$user_id = 1; // Ganti dengan ID user dari session

$success_message = '';
$error_message = '';

// Handle form submission HANYA jika user sudah login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $is_logged_in) {
    $subject = $_POST['subject'] ?? '';
    $message = $_POST['message'] ?? '';
    
    // Validasi
    if (empty($subject) || empty($message)) {
        $error_message = "Harap lengkapi semua field yang wajib diisi.";
    } else {
        try {
            // Simpan pesan ke database
            $stmt = $pdo->prepare("
                INSERT INTO messages (user_id, subject, message, status) 
                VALUES (?, ?, ?, 'new')
            ");
            $stmt->execute([$user_id, $subject, $message]);
            
            $success_message = "Terima kasih! Pesan Anda telah berhasil dikirim. Kami akan merespons dalam 1x24 jam.";
            
        } catch (PDOException $e) {
            $error_message = "Terjadi kesalahan saat menyimpan pesan: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="../assets/css/bootstrap.min.css" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background: #fff; color: #000; }
        
        /* Header - Transparent & merge with hero */
        .site-header {
            background: transparent;
            padding: 0.75rem 0;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 100;
            transition: all 0.3s ease;
        }
        .site-header.scrolled {
            background: rgba(0,0,0,0.95);
            backdrop-filter: blur(10px);
        }
        .header-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .logo { 
            font-size: 1.75rem;
            color: #fff; 
            text-decoration: none; 
            display: flex;
            align-items: baseline;
            letter-spacing: -0.03em;
        }
        .logo-ez { font-weight: 300; color: #fff; }
        .logo-rent { font-weight: 700; color: #fff; }
        .logo-accent { color: #d50000; font-weight: 700; }
        
        .nav-links { 
            display: flex; 
            gap: 2.5rem; 
            list-style: none;
            align-items: center;
        }
        .nav-links a {
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            font-weight: 300;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            padding: 0.5rem 0;
            position: relative;
            transition: color 0.3s ease;
        }
        .nav-links a::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 0;
            height: 1px;
            background: white;
            transition: width 0.3s ease;
        }
        .nav-links a:hover::after, .nav-links a.active::after { width: 100%; }
        .nav-links a:hover, .nav-links a.active { color: #fff; }
        
        .auth-buttons { 
            display: flex; 
            gap: 1.5rem;
            align-items: center;
        }
        .btn-login {
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            font-weight: 300;
            padding: 0.5rem 1rem;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            font-size: 0.85rem;
        }
        .btn-login:hover { color: #fff; }
        .btn-register { 
            background: white;
            color: black;
            text-decoration: none;
            font-weight: 500;
            padding: 0.6rem 1.75rem;
            border: 2px solid white;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            font-size: 0.85rem;
            transition: all 0.3s ease;
        }
        .btn-register:hover { 
            background: transparent;
            color: white;
        }
        
        /* Hero 100vh */
        .hero {
            position: relative;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            padding-top: 80px;
        }
        .hero-bg {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('https://images.unsplash.com/photo-1534536281715-e28d76689b4d?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80') center/cover no-repeat;
            z-index: 1;
        }
        .hero-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(0,0,0,0.85) 0%, rgba(0,0,0,0.5) 50%, rgba(0,0,0,0.85) 100%);
            z-index: 2;
        }
        .hero-pattern {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: 
                radial-gradient(circle at 20% 80%, rgba(213,0,0,0.1) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(255,255,255,0.05) 0%, transparent 50%);
            z-index: 3;
        }
        .hero-particles {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 4;
            pointer-events: none;
        }
        .hero-content {
            position: relative;
            z-index: 10;
            padding: 2rem;
            max-width: 900px;
            text-align: center;
        }
        .hero-badge {
            display: inline-block;
            background: rgba(255,255,255,0.1);
            border: 1px solid rgba(255,255,255,0.3);
            color: #fff;
            padding: 0.5rem 1.5rem;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.15em;
            margin-bottom: 1.5rem;
            backdrop-filter: blur(10px);
            animation: fadeInDown 0.6s ease;
        }
        .hero h1 {
            color: #fff;
            font-size: clamp(2.5rem, 5vw, 4.5rem);
            font-weight: 300;
            margin-bottom: 1.5rem;
            letter-spacing: -0.02em;
            text-shadow: 0 4px 30px rgba(0,0,0,0.5);
            animation: fadeInUp 0.8s ease;
        }
        .hero h1 strong {
            font-weight: 700;
            background: linear-gradient(135deg, #fff 0%, #e0e0e0 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .hero p {
            color: rgba(255,255,255,0.85);
            font-size: 1.25rem;
            max-width: 700px;
            margin: 0 auto 2.5rem;
            line-height: 1.8;
            animation: fadeInUp 1s ease;
        }
        


        
        .scroll-indicator {
            position: absolute;
            bottom: 2rem;
            left: 50%;
            transform: translateX(-50%);
            z-index: 10;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.5rem;
            animation: bounce 2s infinite;
        }
        .scroll-indicator .mouse {
            width: 24px;
            height: 40px;
            border: 2px solid rgba(255,255,255,0.5);
            border-radius: 12px;
            position: relative;
        }
        .scroll-indicator .mouse::before {
            content: '';
            position: absolute;
            top: 8px;
            left: 50%;
            transform: translateX(-50%);
            width: 4px;
            height: 8px;
            background: #d50000;
            border-radius: 2px;
            animation: scrollWheel 1.5s infinite;
        }
        .scroll-indicator span {
            color: rgba(255,255,255,0.5);
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.2em;
        }
        
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes fadeInDown {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes bounce {
            0%, 100% { transform: translateX(-50%) translateY(0); }
            50% { transform: translateX(-50%) translateY(-10px); }
        }
        @keyframes scrollWheel {
            0% { opacity: 1; transform: translateX(-50%) translateY(0); }
            100% { opacity: 0; transform: translateX(-50%) translateY(10px); }
        }
        
        /* Contact Section - Light with texture */
        .contact-section {
            padding: 8rem 2rem;
            background: #fafafa;
            position: relative;
            overflow: hidden;
        }
        .contact-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: url("data:image/svg+xml,%3Csvg width='100' height='100' viewBox='0 0 100 100' xmlns='http://www.w3.org/2000/svg'%3E%3Ccircle cx='50' cy='50' r='1' fill='rgba(0,0,0,0.07)'/%3E%3C/svg%3E");
            background-size: 30px 30px;
            pointer-events: none;
        }
        .contact-section::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: 
                radial-gradient(ellipse at 0% 0%, rgba(213,0,0,0.04) 0%, transparent 50%),
                radial-gradient(ellipse at 100% 100%, rgba(213,0,0,0.03) 0%, transparent 50%);
            pointer-events: none;
        }
        
        /* Subtle corner accents */
        .contact-decoration {
            position: absolute;
            width: 300px;
            height: 300px;
            border: 1px dashed rgba(213,0,0,0.12);
            border-radius: 50%;
            pointer-events: none;
        }
        .contact-decoration.top-right {
            top: -150px;
            right: -150px;
        }
        .contact-decoration.bottom-left {
            bottom: -150px;
            left: -150px;
        }
        .contact-decoration-line {
            display: none;
        }
        
        .contact-container {
            max-width: 1200px;
            margin: 0 auto;
            position: relative;
            z-index: 2;
        }
        .section-header {
            text-align: center;
            margin-bottom: 4rem;
        }
        .section-badge {
            display: inline-block;
            background: rgba(213,0,0,0.1);
            border: 1px solid rgba(213,0,0,0.2);
            color: #d50000;
            padding: 0.5rem 1.5rem;
            font-size: 0.7rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.2em;
            margin-bottom: 1.5rem;
        }
        .section-header h2 {
            font-size: clamp(2rem, 4vw, 3rem);
            font-weight: 300;
            margin-bottom: 1rem;
            color: #000;
        }
        .section-header h2 strong {
            font-weight: 700;
            background: linear-gradient(135deg, #d50000 0%, #b71c1c 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .section-header p {
            color: #6b7280;
            font-size: 1.1rem;
            max-width: 600px;
            margin: 0 auto;
        }
        .section-line {
            width: 80px;
            height: 3px;
            background: linear-gradient(90deg, transparent, #d50000, transparent);
            margin: 1.5rem auto 0;
        }
        .contact-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 2rem;
        }
        
        /* Contact Cards */
        .contact-card {
            background: #fff;
            border: 1px solid #e5e7eb;
            padding: 2.5rem 2rem;
            text-align: center;
            transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            box-shadow: 0 10px 40px rgba(0,0,0,0.05);
        }
        .contact-card::before {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, #d50000 0%, #ff5252 100%);
            transform: scaleX(0);
            transition: transform 0.4s ease;
        }
        .contact-card:hover::before {
            transform: scaleX(1);
        }
        .contact-card:hover {
            transform: translateY(-10px);
            border-color: #d50000;
            box-shadow: 0 25px 60px rgba(213,0,0,0.15);
        }
        .contact-card .card-icon {
            width: 70px;
            height: 70px;
            margin: 0 auto 1.5rem;
            background: linear-gradient(135deg, #f8f9fa 0%, #fff 100%);
            border: 2px solid #e5e7eb;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.4s ease;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
        }
        .contact-card .card-icon svg {
            width: 28px;
            height: 28px;
            color: #d50000;
            transition: all 0.4s ease;
        }
        .contact-card:hover .card-icon {
            background: linear-gradient(135deg, #d50000 0%, #b71c1c 100%);
            border-color: #d50000;
            transform: scale(1.1) rotate(5deg);
            box-shadow: 0 15px 40px rgba(213,0,0,0.3);
        }
        .contact-card:hover .card-icon svg {
            color: #fff;
        }
        .contact-card h3 {
            font-size: 1.1rem;
            font-weight: 600;
            color: #000;
            margin-bottom: 0.75rem;
        }
        .contact-card .card-value {
            color: #d50000;
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        .contact-card .card-desc {
            color: #6b7280;
            font-size: 0.85rem;
        }
        
        /* Form Section - Dark */
        .form-section {
            padding: 8rem 2rem;
            background: linear-gradient(135deg, #0a0a0a 0%, #1a1a1a 50%, #0a0a0a 100%);
            position: relative;
            overflow: hidden;
        }
        .form-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('https://images.unsplash.com/photo-1497366216548-37526070297c?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80') center/cover no-repeat;
            opacity: 0.05;
            pointer-events: none;
        }
        .form-section::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: 
                radial-gradient(circle at 20% 30%, rgba(213,0,0,0.1) 0%, transparent 40%),
                radial-gradient(circle at 80% 70%, rgba(213,0,0,0.08) 0%, transparent 40%);
            pointer-events: none;
        }
        .form-container {
            max-width: 700px;
            margin: 0 auto;
            position: relative;
            z-index: 2;
        }
        .form-header {
            text-align: center;
            margin-bottom: 3rem;
        }
        .form-badge {
            display: inline-block;
            background: rgba(213,0,0,0.15);
            border: 1px solid rgba(213,0,0,0.3);
            color: #d50000;
            padding: 0.5rem 1.5rem;
            font-size: 0.7rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.2em;
            margin-bottom: 1.5rem;
        }
        .form-header h2 {
            font-size: clamp(2rem, 4vw, 2.5rem);
            font-weight: 300;
            margin-bottom: 1rem;
            color: #fff;
        }
        .form-header h2 strong {
            font-weight: 700;
            background: linear-gradient(135deg, #fff 0%, #d50000 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .form-header p {
            color: rgba(255,255,255,0.6);
            font-size: 1.1rem;
        }
        .form-line {
            width: 80px;
            height: 3px;
            background: linear-gradient(90deg, transparent, #d50000, transparent);
            margin: 1.5rem auto 0;
        }
        
        /* Locked Form */
        .locked-form {
            background: linear-gradient(145deg, rgba(30,30,30,0.9) 0%, rgba(20,20,20,0.95) 100%);
            border: 1px solid rgba(255,255,255,0.1);
            padding: 4rem 3rem;
            text-align: center;
            backdrop-filter: blur(10px);
            position: relative;
            overflow: hidden;
        }
        .locked-form::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, transparent, #d50000, transparent);
        }
        .locked-form .lock-icon {
            width: 100px;
            height: 100px;
            margin: 0 auto 2rem;
            background: linear-gradient(135deg, rgba(213,0,0,0.2) 0%, rgba(213,0,0,0.1) 100%);
            border: 2px solid rgba(213,0,0,0.3);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            animation: pulse 2s infinite;
        }
        .locked-form .lock-icon svg {
            width: 45px;
            height: 45px;
            color: #d50000;
        }
        @keyframes pulse {
            0%, 100% { box-shadow: 0 0 0 0 rgba(213,0,0,0.4); }
            50% { box-shadow: 0 0 0 20px rgba(213,0,0,0); }
        }
        .locked-form h3 {
            color: #fff;
            font-size: 1.75rem;
            font-weight: 600;
            margin-bottom: 1rem;
        }
        .locked-form p {
            color: rgba(255,255,255,0.6);
            font-size: 1.05rem;
            margin-bottom: 2rem;
            max-width: 400px;
            margin-left: auto;
            margin-right: auto;
            line-height: 1.7;
        }
        .locked-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }
        .btn-unlock {
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
            background: linear-gradient(135deg, #d50000 0%, #b71c1c 100%);
            color: #fff;
            text-decoration: none;
            padding: 1rem 2.5rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            font-size: 0.85rem;
            transition: all 0.3s;
            box-shadow: 0 10px 30px rgba(213,0,0,0.4);
        }
        .btn-unlock:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 40px rgba(213,0,0,0.5);
        }
        .btn-unlock svg {
            width: 18px;
            height: 18px;
        }
        .btn-register-form {
            display: inline-block;
            background: transparent;
            border: 2px solid rgba(255,255,255,0.3);
            color: #fff;
            text-decoration: none;
            padding: 1rem 2.5rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            font-size: 0.85rem;
            transition: all 0.3s;
        }
        .btn-register-form:hover {
            background: rgba(255,255,255,0.1);
            border-color: #fff;
            transform: translateY(-3px);
        }
        
        /* Contact Form Box */
        .contact-form-box {
            background: linear-gradient(145deg, rgba(30,30,30,0.9) 0%, rgba(20,20,20,0.95) 100%);
            border: 1px solid rgba(255,255,255,0.1);
            padding: 3rem;
            backdrop-filter: blur(10px);
        }
        .form-group { margin-bottom: 1.75rem; }
        .form-label {
            display: block;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: rgba(255,255,255,0.8);
            margin-bottom: 0.6rem;
        }
        .form-input, .form-select, .form-textarea {
            width: 100%;
            padding: 1rem 1.25rem;
            border: 2px solid rgba(255,255,255,0.1);
            background: rgba(255,255,255,0.05);
            color: #fff;
            font-size: 1rem;
            transition: all 0.3s;
        }
        .form-input:focus, .form-select:focus, .form-textarea:focus {
            outline: none;
            border-color: #d50000;
            background: rgba(255,255,255,0.08);
            box-shadow: 0 5px 20px rgba(213,0,0,0.2);
        }
        .form-input::placeholder, .form-textarea::placeholder {
            color: rgba(255,255,255,0.4);
        }
        .form-select option {
            background: #1a1a1a;
            color: #fff;
        }
        .form-textarea {
            resize: vertical;
            min-height: 160px;
        }
        .btn-submit {
            width: 100%;
            padding: 1.1rem;
            background: linear-gradient(135deg, #d50000 0%, #b71c1c 100%);
            color: #fff;
            border: none;
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 10px 30px rgba(213,0,0,0.3);
        }
        .btn-submit:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 40px rgba(213,0,0,0.4);
        }
        .success-msg {
            background: linear-gradient(135deg, rgba(16,185,129,0.2) 0%, rgba(16,185,129,0.1) 100%);
            border: 1px solid rgba(16,185,129,0.5);
            color: #10b981;
            padding: 1.25rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        .error-msg {
            background: linear-gradient(135deg, rgba(220,38,38,0.2) 0%, rgba(220,38,38,0.1) 100%);
            border: 1px solid rgba(220,38,38,0.5);
            color: #dc2626;
            padding: 1.25rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        /* FAQ Section - Light with Premium Style */
        .faq-section {
            padding: 8rem 2rem;
            background: linear-gradient(180deg, #f8f9fa 0%, #fff 100%);
            position: relative;
            overflow: hidden;
        }
        .faq-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: 
                radial-gradient(circle at 10% 90%, rgba(213,0,0,0.03) 0%, transparent 50%),
                radial-gradient(circle at 90% 10%, rgba(0,0,0,0.03) 0%, transparent 50%);
            pointer-events: none;
        }
        .faq-container {
            max-width: 900px;
            margin: 0 auto;
            position: relative;
            z-index: 2;
        }
        .faq-header {
            text-align: center;
            margin-bottom: 4rem;
        }
        .faq-badge {
            display: inline-block;
            background: rgba(213,0,0,0.1);
            border: 1px solid rgba(213,0,0,0.2);
            color: #d50000;
            padding: 0.5rem 1.5rem;
            font-size: 0.7rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.2em;
            margin-bottom: 1.5rem;
        }
        .faq-header h2 {
            font-size: clamp(2rem, 4vw, 2.75rem);
            font-weight: 300;
            margin-bottom: 1rem;
            color: #000;
        }
        .faq-header h2 strong {
            font-weight: 700;
            background: linear-gradient(135deg, #d50000 0%, #b71c1c 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .faq-header p {
            color: #6b7280;
            font-size: 1.1rem;
        }
        .faq-line {
            width: 80px;
            height: 3px;
            background: linear-gradient(90deg, transparent, #d50000, transparent);
            margin: 1.5rem auto 0;
        }
        .faq-list {
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
        }
        .faq-item {
            background: #fff;
            border: 1px solid #e5e7eb;
            padding: 2rem 2.5rem;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 8px 30px rgba(0,0,0,0.05);
            position: relative;
            overflow: hidden;
            display: flex;
            gap: 1.5rem;
            align-items: flex-start;
        }
        .faq-item::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 4px;
            background: linear-gradient(180deg, #d50000 0%, #ff5252 100%);
            transform: scaleY(0);
            transform-origin: top;
            transition: transform 0.4s ease;
        }
        .faq-item:hover::before {
            transform: scaleY(1);
        }
        .faq-item:hover {
            border-color: #d50000;
            transform: translateX(10px);
            box-shadow: 0 20px 50px rgba(213,0,0,0.1);
        }
        .faq-icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, rgba(213,0,0,0.1) 0%, rgba(213,0,0,0.05) 100%);
            border: 1px solid rgba(213,0,0,0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            transition: all 0.3s;
        }
        .faq-icon svg {
            width: 22px;
            height: 22px;
            color: #d50000;
        }
        .faq-item:hover .faq-icon {
            background: linear-gradient(135deg, #d50000 0%, #b71c1c 100%);
            border-color: #d50000;
            transform: rotate(10deg) scale(1.1);
        }
        .faq-item:hover .faq-icon svg {
            color: #fff;
        }
        .faq-content {
            flex: 1;
        }
        .faq-item h3 {
            font-size: 1.15rem;
            font-weight: 600;
            margin-bottom: 0.75rem;
            color: #000;
        }
        .faq-item p {
            color: #6b7280;
            line-height: 1.8;
            font-size: 0.95rem;
            margin: 0;
        }
        
        /* Scroll Animations */
        .scroll-reveal {
            opacity: 0;
            transform: translateY(40px);
            transition: all 0.8s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .scroll-reveal.active {
            opacity: 1;
            transform: translateY(0);
        }
        .slide-in-left {
            opacity: 0;
            transform: translateX(-60px);
            transition: all 0.8s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .slide-in-left.active {
            opacity: 1;
            transform: translateX(0);
        }
        .slide-in-right {
            opacity: 0;
            transform: translateX(60px);
            transition: all 0.8s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .slide-in-right.active {
            opacity: 1;
            transform: translateX(0);
        }
        .slide-in-up {
            opacity: 0;
            transform: translateY(60px);
            transition: all 0.8s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .slide-in-up.active {
            opacity: 1;
            transform: translateY(0);
        }
        .scale-in {
            opacity: 0;
            transform: scale(0.8);
            transition: all 0.8s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .scale-in.active {
            opacity: 1;
            transform: scale(1);
        }
        .stagger-1 { transition-delay: 0.1s; }
        .stagger-2 { transition-delay: 0.2s; }
        .stagger-3 { transition-delay: 0.3s; }
        .stagger-4 { transition-delay: 0.4s; }
        .stagger-5 { transition-delay: 0.5s; }
        
        /* Form Section - Dark Theme */
        .form-section {
            background: linear-gradient(135deg, #0a0a0a 0%, #1a1a1a 50%, #0f0f0f 100%);
            padding: 6rem 2rem;
            position: relative;
            overflow: hidden;
        }
        .form-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: 
                radial-gradient(circle at 20% 30%, rgba(213,0,0,0.08) 0%, transparent 50%),
                radial-gradient(circle at 80% 70%, rgba(213,0,0,0.05) 0%, transparent 50%);
            pointer-events: none;
        }
        .form-section::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' xmlns='http://www.w3.org/2000/svg'%3E%3Cdefs%3E%3Cpattern id='grid' width='60' height='60' patternUnits='userSpaceOnUse'%3E%3Cpath d='M 60 0 L 0 0 0 60' fill='none' stroke='rgba(255,255,255,0.03)' stroke-width='1'/%3E%3C/pattern%3E%3C/defs%3E%3Crect width='100%25' height='100%25' fill='url(%23grid)'/%3E%3C/svg%3E");
            pointer-events: none;
        }
        .form-container {
            max-width: 700px;
            margin: 0 auto;
            position: relative;
            z-index: 2;
        }
        .form-wrapper {
            background: rgba(255,255,255,0.02);
            border: 1px solid rgba(255,255,255,0.08);
            backdrop-filter: blur(10px);
            padding: 3rem;
        }
        .form-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .form-header h2 {
            font-size: 1.75rem;
            font-weight: 400;
            color: #fff;
            margin-bottom: 0.5rem;
        }
        .form-header h2 strong {
            font-weight: 600;
        }
        .form-header p {
            color: rgba(255,255,255,0.5);
            font-size: 0.95rem;
        }
        
        /* Login Notice */
        .login-notice {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.1);
            padding: 1rem 1.5rem;
            margin-bottom: 1.5rem;
            color: rgba(255,255,255,0.7);
            font-size: 0.9rem;
        }
        .login-notice svg {
            width: 18px;
            height: 18px;
            color: #d50000;
            flex-shrink: 0;
        }
        .login-notice a {
            color: #ff5252;
            text-decoration: none;
            font-weight: 500;
        }
        .login-notice a:hover {
            text-decoration: underline;
        }
        
        /* Locked Form Preview */
        .locked-form-preview {
            opacity: 0.4;
            pointer-events: none;
        }
        .locked-form-preview .form-group {
            margin-bottom: 1.25rem;
        }
        .locked-form-preview label {
            display: block;
            color: rgba(255,255,255,0.5);
            font-size: 0.85rem;
            margin-bottom: 0.5rem;
        }
        .locked-form-preview select,
        .locked-form-preview textarea {
            width: 100%;
            background: rgba(255,255,255,0.03);
            border: 1px solid rgba(255,255,255,0.1);
            padding: 1rem;
            color: rgba(255,255,255,0.3);
            font-size: 0.95rem;
        }
        .locked-form-preview textarea {
            height: 120px;
            resize: none;
        }
        .btn-disabled {
            width: 100%;
            background: rgba(255,255,255,0.1);
            color: rgba(255,255,255,0.3);
            padding: 1rem;
            border: none;
            font-size: 1rem;
            cursor: not-allowed;
        }
        
        /* Contact Form (Logged In) */
        .contact-form .form-group {
            margin-bottom: 1.5rem;
        }
        .contact-form .form-label {
            display: block;
            color: rgba(255,255,255,0.8);
            font-size: 0.9rem;
            font-weight: 500;
            margin-bottom: 0.5rem;
        }
        .contact-form .form-select,
        .contact-form .form-textarea {
            width: 100%;
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.15);
            padding: 1rem 1.25rem;
            color: #fff;
            font-size: 0.95rem;
            transition: all 0.3s;
        }
        .contact-form .form-select:focus,
        .contact-form .form-textarea:focus {
            outline: none;
            border-color: #d50000;
            background: rgba(255,255,255,0.08);
            box-shadow: 0 0 0 3px rgba(213,0,0,0.15);
        }
        .contact-form .form-select option {
            background: #1a1a1a;
            color: #fff;
        }
        .contact-form .form-textarea {
            resize: vertical;
            min-height: 140px;
        }
        .contact-form .form-textarea::placeholder {
            color: rgba(255,255,255,0.4);
        }
        .contact-form .btn-submit {
            width: 100%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            background: linear-gradient(135deg, #d50000 0%, #b71c1c 100%);
            color: #fff;
            border: none;
            padding: 1.1rem 2rem;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }
        .contact-form .btn-submit::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }
        .contact-form .btn-submit:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(213,0,0,0.4);
        }
        .contact-form .btn-submit:hover::before {
            left: 100%;
        }
        
        /* Form Messages */
        .form-section .success-msg,
        .form-section .error-msg {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 1rem 1.25rem;
            margin-bottom: 1.5rem;
            font-size: 0.95rem;
        }
        .form-section .success-msg {
            background: rgba(34,197,94,0.15);
            border: 1px solid rgba(34,197,94,0.3);
            color: #4ade80;
        }
        .form-section .error-msg {
            background: rgba(239,68,68,0.15);
            border: 1px solid rgba(239,68,68,0.3);
            color: #f87171;
        }
        
        /* Responsive Design */
        @media (max-width: 1024px) {
            .contact-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        @media (max-width: 768px) {
            .hero h1 { font-size: 2rem; }
            .nav-links { display: none; }
            .contact-grid { 
                grid-template-columns: 1fr; 
                gap: 1.5rem;
            }
            .contact-card {
                padding: 2rem 1.5rem;
            }
            .form-section {
                padding: 5rem 1.5rem;
            }
            .form-wrapper {
                padding: 2rem 1.5rem;
            }
            .login-notice {
                flex-direction: column;
                text-align: center;
                gap: 1rem;
            }
            .faq-section {
                padding: 5rem 1.5rem;
            }
            .faq-item {
                padding: 1.5rem;
                flex-direction: column;
                gap: 1rem;
            }
            .scroll-indicator {
                display: none;
            }
        }
        @media (max-width: 480px) {
            .hero-content h1 { font-size: 1.75rem; }
            .contact-card .card-icon {
                width: 60px;
                height: 60px;
            }
            .contact-card .card-icon svg {
                width: 24px;
                height: 24px;
            }
        }
    </style>
</head>
<body>

<!-- Header -->
<header class="site-header">
    <div class="header-container">
        <nav class="navbar">
            <a href="index.php" class="logo">
                <span class="logo-ez">Ez</span><span class="logo-rent">Rent</span><span class="logo-accent">.</span>
            </a>
            <ul class="nav-links">
                <li><a href="index.php">Beranda</a></li>
                <li><a href="vehicles.php">Kendaraan</a></li>
                <li><a href="about.php">Tentang Kami</a></li>
                <li><a href="contact.php" class="active">Kontak</a></li>
            </ul>
            <div class="auth-buttons">
                <a href="login.php" class="btn-login">Login</a>
                <a href="register.php" class="btn-register">Daftar</a>
            </div>
        </nav>
    </div>
</header>

<!-- Hero Section - Simple Centered -->
<section class="hero">
    <div class="hero-bg"></div>
    <div class="hero-overlay"></div>
    <div class="hero-pattern"></div>
    <div class="hero-particles" id="hero-particles"></div>
    <div class="hero-content">
        <div class="scroll-reveal"></div>
        <h1 class="scroll-reveal">Kami Siap <strong>Membantu</strong> Anda</h1>
        <p class="scroll-reveal">Butuh bantuan atau memiliki pertanyaan? Tim support kami siap membantu Anda 24/7 dengan pelayanan terbaik</p>
    </div>
    <div class="scroll-indicator">
        <div class="scroll-mouse"><div class="scroll-wheel"></div></div>
        <span>Scroll</span>
    </div>
</section>

<!-- Contact Info Cards -->
<section class="contact-section">
    <div class="contact-decoration top-right"></div>
    <div class="contact-decoration bottom-left"></div>
    <div class="contact-decoration-line top"></div>
    <div class="contact-decoration-line bottom"></div>
    
    <div class="contact-container">
        <div class="section-header scroll-reveal">
            <h2>Hubungi <strong>Kami</strong></h2>
            <p>Pilih channel komunikasi yang paling nyaman untuk Anda</p>
            <div class="section-line"></div>
        </div>
        
        <div class="contact-grid">
            <!-- Phone Card -->
            <div class="contact-card slide-in-up stagger-1">
                <div class="card-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                    </svg>
                </div>
                <h3>Telepon</h3>
                <div class="card-value">(021) 1234-5678</div>
                <p class="card-desc">Senin - Minggu, 24 Jam</p>
            </div>

            <!-- Email Card -->
            <div class="contact-card slide-in-up stagger-2">
                <div class="card-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                </div>
                <h3>Email</h3>
                <div class="card-value">info@ezrent.com</div>
                <p class="card-desc">Respon dalam 1-2 jam</p>
            </div>

            <!-- Location Card -->
            <div class="contact-card slide-in-up stagger-3">
                <div class="card-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <h3>Alamat Kantor</h3>
                <div class="card-value">Jl. Sudirman No. 123</div>
                <p class="card-desc">Jakarta Pusat, 10210</p>
            </div>

            <!-- WhatsApp Card -->
            <div class="contact-card slide-in-up stagger-4">
                <div class="card-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                    </svg>
                </div>
                <h3>WhatsApp</h3>
                <div class="card-value">+62 812-3456-7890</div>
                <p class="card-desc">Fast response via chat</p>
            </div>
        </div>
    </div>
</section>

<!-- Form Section - Dark Theme -->
<section class="form-section">
    <div class="form-container">
        <div class="form-wrapper scroll-reveal">
            <div class="form-header">
                <h2>Kirim <strong>Pesan</strong></h2>
                <p>Ada pertanyaan atau kendala? Kami siap membantu.</p>
            </div>
            
            <?php if (!$is_logged_in): ?>
            <div class="login-notice">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
                <span>Silakan <a href="login.php">login</a> untuk mengirim pesan</span>
            </div>
            
            <div class="locked-form-preview">
                <div class="form-group disabled">
                    <label>Subjek</label>
                    <select disabled><option>Pilih subjek pesan</option></select>
                </div>
                <div class="form-group disabled">
                    <label>Pesan</label>
                    <textarea disabled placeholder="Tulis pesan Anda..."></textarea>
                </div>
                <button disabled class="btn-disabled">Kirim Pesan</button>
            </div>
            <?php else: ?>
            <form method="POST" action="" class="contact-form">
                <?php if ($success_message): ?>
                    <div class="success-msg">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <?php echo $success_message; ?>
                    </div>
                <?php endif; ?>

                <?php if ($error_message): ?>
                    <div class="error-msg">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <?php echo $error_message; ?>
                    </div>
                <?php endif; ?>

                <div class="form-group">
                    <label class="form-label">Subjek</label>
                    <select name="subject" required class="form-select">
                        <option value="">Pilih subjek pesan</option>
                        <option value="Pertanyaan Umum">Pertanyaan Umum</option>
                        <option value="Bantuan Teknis">Bantuan Teknis</option>
                        <option value="Penyewaan Kendaraan">Penyewaan Kendaraan</option>
                        <option value="Keluhan">Keluhan</option>
                        <option value="Kerjasama Bisnis">Kerjasama Bisnis</option>
                        <option value="Lainnya">Lainnya</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Pesan</label>
                    <textarea name="message" required class="form-textarea" rows="5"
                        placeholder="Tulis pesan Anda..."><?php echo htmlspecialchars($_POST['message'] ?? ''); ?></textarea>
                </div>

                <button type="submit" class="btn-submit">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="20" height="20">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                    </svg>
                    Kirim Pesan
                </button>
            </form>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- FAQ Section -->
<section class="faq-section">
    <div class="faq-container">
        <div class="faq-header scroll-reveal">
            <h2>Pertanyaan yang Sering <strong>Diajukan</strong></h2>
            <p>Temukan jawaban atas pertanyaan umum seputar layanan rental kami</p>
            <div class="faq-line"></div>
        </div>

        <div class="faq-list">
            <div class="faq-item slide-in-left stagger-1">
                <div class="faq-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="faq-content">
                    <h3>Apa saja syarat untuk menyewa kendaraan di EzRent?</h3>
                    <p>Anda harus berusia minimal 21 tahun, memiliki KTP yang masih berlaku, SIM sesuai jenis kendaraan yang disewa (SIM A untuk mobil), dan kartu kredit/debit untuk deposit. Untuk penyewaan atas nama perusahaan, diperlukan juga surat kuasa dan dokumen legalitas perusahaan.</p>
                </div>
            </div>

            <div class="faq-item slide-in-right stagger-2">
                <div class="faq-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="faq-content">
                    <h3>Berapa biaya deposit dan bagaimana proses pengembaliannya?</h3>
                    <p>Deposit mulai dari Rp 500.000 hingga Rp 5.000.000 tergantung jenis kendaraan. Deposit akan dikembalikan dalam 1-3 hari kerja setelah kendaraan dikembalikan dalam kondisi baik tanpa kerusakan dan keterlambatan pengembalian.</p>
                </div>
            </div>

            <div class="faq-item slide-in-left stagger-3">
                <div class="faq-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="faq-content">
                    <h3>Bagaimana jika saya ingin memperpanjang waktu sewa?</h3>
                    <p>Anda dapat mengajukan perpanjangan melalui dashboard akun Anda atau menghubungi customer service minimal 6 jam sebelum waktu pengembalian. Perpanjangan dikenakan tarif harian normal dan harus ada konfirmasi ketersediaan kendaraan.</p>
                </div>
            </div>

            <div class="faq-item slide-in-right stagger-4">
                <div class="faq-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                </div>
                <div class="faq-content">
                    <h3>Apakah kendaraan sudah termasuk asuransi?</h3>
                    <p>Ya, semua kendaraan sudah dilengkapi asuransi dasar yang mencakup perlindungan terhadap kecelakaan dan kerusakan kendaraan. Untuk perlindungan lebih lengkap, Anda dapat menambahkan asuransi premium dengan biaya tambahan yang sangat terjangkau.</p>
                </div>
            </div>

            <div class="faq-item slide-in-left stagger-5">
                <div class="faq-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                </div>
                <div class="faq-content">
                    <h3>Apa yang terjadi jika saya membatalkan pesanan?</h3>
                    <p>Pembatalan 48 jam sebelum waktu pengambilan mendapat pengembalian dana 100%. Pembatalan 24-48 jam sebelumnya dikenakan biaya 25% dari total sewa. Pembatalan kurang dari 24 jam dikenakan biaya 50%. Pembatalan mendadak pada hari H tidak dapat dikembalikan.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include '../php/includes/footer.php'; ?>

<script>
// Scroll Reveal Animation
const observerOptions = {
    root: null,
    rootMargin: '0px',
    threshold: 0.1
};

const scrollObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.classList.add('active');
        }
    });
}, observerOptions);

document.querySelectorAll('.scroll-reveal, .slide-in-left, .slide-in-right, .slide-in-up, .scale-in').forEach(el => {
    scrollObserver.observe(el);
});

// Hero Parallax Effect
window.addEventListener('scroll', () => {
    const scrolled = window.pageYOffset;
    const hero = document.querySelector('.hero');
    const heroBg = document.querySelector('.hero-bg');
    
    if (hero && scrolled < hero.offsetHeight) {
        heroBg.style.transform = `translateY(${scrolled * 0.4}px)`;
    }
});

// Floating Particles
function createParticles() {
    const container = document.getElementById('hero-particles');
    if (!container) return;
    
    for (let i = 0; i < 30; i++) {
        const particle = document.createElement('div');
        particle.style.cssText = `
            position: absolute;
            width: ${Math.random() * 6 + 2}px;
            height: ${Math.random() * 6 + 2}px;
            background: rgba(255,255,255,${Math.random() * 0.3 + 0.1});
            border-radius: 50%;
            left: ${Math.random() * 100}%;
            top: ${Math.random() * 100}%;
            animation: float ${Math.random() * 10 + 10}s infinite ease-in-out;
            animation-delay: ${Math.random() * 5}s;
        `;
        container.appendChild(particle);
    }
}

// Add float animation
const style = document.createElement('style');
style.textContent = `
    @keyframes float {
        0%, 100% { transform: translateY(0) translateX(0) rotate(0deg); opacity: 0; }
        10% { opacity: 1; }
        90% { opacity: 1; }
        50% { transform: translateY(-100px) translateX(50px) rotate(180deg); }
    }
`;
document.head.appendChild(style);
createParticles();

// Card 3D Hover Effect
document.querySelectorAll('.contact-card').forEach(card => {
    card.addEventListener('mousemove', (e) => {
        const rect = card.getBoundingClientRect();
        const x = e.clientX - rect.left;
        const y = e.clientY - rect.top;
        const centerX = rect.width / 2;
        const centerY = rect.height / 2;
        const rotateX = (y - centerY) / 10;
        const rotateY = (centerX - x) / 10;
        
        card.style.transform = `perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) translateY(-10px)`;
    });
    
    card.addEventListener('mouseleave', () => {
        card.style.transform = 'perspective(1000px) rotateX(0) rotateY(0) translateY(0)';
    });
});

// Header scroll effect
const siteHeader = document.querySelector('.site-header');
window.addEventListener('scroll', () => {
    if (window.scrollY > 50) {
        siteHeader.classList.add('scrolled');
    } else {
        siteHeader.classList.remove('scrolled');
    }
});
</script>
</body>
</html>