<?php 
// Start session dan cek login SEBELUM output apapun
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect jika belum login atau bukan user
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: ../login.php");
    exit();
}

// Koneksi database untuk ambil kendaraan
require_once '../../php/config/database.php';

$user_name = $_SESSION['nama_lengkap'] ?? '';
$user_email = $_SESSION['email'] ?? '';

// Ambil 4 motor dan 4 mobil dari database
$motors = [];
$mobils = [];
try {
    $stmt = $pdo->prepare("SELECT id, nama, merek, harga_per_hari, images FROM vehicles WHERE jenis = 'motor' AND status = 'tersedia' ORDER BY id LIMIT 4");
    $stmt->execute();
    $motors = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $stmt = $pdo->prepare("SELECT id, nama, merek, harga_per_hari, images FROM vehicles WHERE jenis = 'mobil' AND status = 'tersedia' ORDER BY id LIMIT 4");
    $stmt->execute();
    $mobils = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Handle silently
}

$page_title = "Beranda - EzRent";
include 'header.php'; 
?>

<style>
    /* Override container for full-width dashboard */
    main > .container {
        max-width: 100% !important;
        padding: 0 !important;
        margin: 0 !important;
    }
    
    main {
        padding: 0 !important;
    }

    /* Video Hero Section */
    .video-hero {
        position: relative;
        height: 100vh;
        width: 100%;
        margin: 0;
    }
    
    .video-container {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 1;
    }
    
    .hero-video {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .video-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(to bottom, rgba(0,0,0,0.4), rgba(0,0,0,0.6));
        z-index: 2;
    }
    
    .hero-content {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 10;
        display: flex;
        align-items: center;
        justify-content: flex-start; /* desktop: left-aligned */
        padding-left: 10%;
        padding-right: 1.25rem;
    }

    .hero-text {
        text-align: left; /* desktop: left text alignment */
        color: white;
        max-width: 720px;
        margin: 0;
    }

    /* Mobile: center hero content and text */
    @media (max-width: 768px) {
        .hero-content {
            justify-content: center !important;
            padding-left: 1.25rem !important;
            padding-right: 1.25rem !important;
        }
        .hero-text { text-align: center !important; }
    }
    
    .hero-welcome {
        font-size: 1.1rem;
        font-weight: 300;
        margin-bottom: 1rem;
        opacity: 0.9;
        text-transform: uppercase;
        letter-spacing: 0.1em;
    }
    
    .hero-title {
        font-size: clamp(2rem, 5vw, 3.5rem);
        font-weight: 300;
        line-height: 1.2;
        margin-bottom: 2rem;
        letter-spacing: -0.02em;
    }
    
    .btn-hero {
        display: inline-block;
        padding: 1rem 2.5rem;
        font-size: 0.9rem;
        font-weight: 500;
        text-decoration: none;
        text-transform: uppercase;
        letter-spacing: 0.1em;
        transition: all 0.3s ease;
        margin-right: 1rem;
        margin-bottom: 1rem;
    }
    
    .btn-hero-primary {
        background: #d50000;
        color: white;
        border: 2px solid #d50000;
    }
    
    .btn-hero-primary:hover {
        background: transparent;
        border-color: white;
    }
    
    .btn-hero-secondary {
        background: transparent;
        color: white;
        border: 2px solid white;
    }
    
    .btn-hero-secondary:hover {
        background: white;
        color: black;
    }
    
    .scroll-indicator {
        position: absolute;
        bottom: 2rem;
        left: 50%;
        transform: translateX(-50%);
        z-index: 10;
        animation: bounce 2s infinite;
    }
    
    .scroll-arrow {
        width: 30px;
        height: 50px;
        border: 2px solid white;
        border-radius: 15px;
        position: relative;
    }
    
    .scroll-arrow::before {
        content: '';
        position: absolute;
        top: 8px;
        left: 50%;
        width: 6px;
        height: 6px;
        background: white;
        border-radius: 50%;
        transform: translateX(-50%);
        animation: scroll 2s infinite;
    }
    
    @keyframes bounce {
        0%, 20%, 50%, 80%, 100% { transform: translateX(-50%) translateY(0); }
        40% { transform: translateX(-50%) translateY(-10px); }
        60% { transform: translateX(-50%) translateY(-5px); }
    }
    
    @keyframes scroll {
        0% { opacity: 1; top: 8px; }
        100% { opacity: 0; top: 30px; }
    }

    /* Features Section */
    .features-section {
        padding: 6rem 20px;
        background: 
            linear-gradient(135deg, rgba(250,250,250,0.97) 0%, rgba(255,255,255,0.97) 100%),
            url("data:image/svg+xml,%3Csvg width='100' height='100' viewBox='0 0 100 100' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M11 18c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm48 25c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm-43-7c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm63 31c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM34 90c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm56-76c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM12 86c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm28-65c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm23-11c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-6 60c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm29 22c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zM32 63c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm57-13c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-9-21c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM60 91c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM35 41c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM12 60c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2z' fill='%23d50000' fill-opacity='0.03' fill-rule='evenodd'/%3E%3C/svg%3E");
        width: 100%;
        position: relative;
        overflow: hidden;
    }
    
    .features-section::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -20%;
        width: 600px;
        height: 600px;
        background: radial-gradient(circle, rgba(213,0,0,0.06) 0%, transparent 70%);
        pointer-events: none;
    }
    
    .features-section::after {
        content: '';
        position: absolute;
        bottom: -30%;
        left: -10%;
        width: 400px;
        height: 400px;
        background: radial-gradient(circle, rgba(0,0,0,0.03) 0%, transparent 70%);
        pointer-events: none;
    }
    
    .section-header {
        text-align: center;
        margin-bottom: 4rem;
        position: relative;
        z-index: 1;
    }
    
    .section-header h2 {
        font-size: clamp(2rem, 4vw, 3rem);
        font-weight: 500;
        margin-bottom: 1rem;
        letter-spacing: -0.02em;
    }
    
    .section-header p {
        font-size: 1.1rem;
        color: #6b7280;
        font-weight: 300;
    }


    
    .features-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 2rem;
        max-width: 1200px;
        margin: 0 auto;
        position: relative;
        z-index: 1;
    }
    
    .feature-card {
        text-align: center;
        padding: 3rem 2rem;
        background: linear-gradient(145deg, rgba(255,255,255,0.95) 0%, rgba(250,250,250,0.9) 100%);
        border: 1px solid rgba(0,0,0,0.06);
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
        backdrop-filter: blur(10px);
        box-shadow: 0 4px 20px rgba(0,0,0,0.03);
    }
    
    .feature-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: linear-gradient(90deg, transparent, #d50000, transparent);
        opacity: 0;
        transition: opacity 0.4s ease;
    }
    
    .feature-card::after {
        content: '';
        position: absolute;
        bottom: 0;
        right: 0;
        width: 100px;
        height: 100px;
        background: radial-gradient(circle, rgba(213,0,0,0.05) 0%, transparent 70%);
        pointer-events: none;
    }
    
    .feature-card:hover::before {
        opacity: 1;
    }
    
    .feature-card:hover {
        border-color: rgba(213,0,0,0.2);
        transform: translateY(-8px);
        box-shadow: 0 20px 40px rgba(0,0,0,0.08);
    }
    
    .feature-icon {
        width: 70px;
        height: 70px;
        margin: 0 auto 1.5rem;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 2px solid #000;
        border-radius: 50%;
        color: #000;
        transition: all 0.3s ease;
    }
    
    .feature-card:hover .feature-icon {
        border-color: #d50000;
        color: #d50000;
    }
    
    .feature-icon svg {
        width: 32px;
        height: 32px;
    }
    
    .feature-card h3 {
        font-size: 1.25rem;
        font-weight: 600;
        color: #000;
        margin-bottom: 1rem;
    }
    
    .feature-card p {
        color: #6b7280;
        line-height: 1.7;
        font-weight: 300;
        font-size: 0.95rem;
    }

    /* Vehicles Preview Section */
    .vehicles-preview {
        padding: 6rem 20px;
        background: #0a0a0a;
        width: 100%;
    }
    
    .vehicle-card {
        text-decoration: none;
        display: block;
        background: #111;
        overflow: hidden;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 4px 20px rgba(255,255,255,0.05);
        position: relative;
    }
    
    .vehicle-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 3px;
        height: 0;
        background: linear-gradient(180deg, #d50000 0%, #ff5252 100%);
        transition: height 0.3s ease;
        z-index: 10;
    }
    
    .vehicle-card:hover::before {
        height: 100%;
    }
    
    .vehicle-card:hover {
        transform: translateY(-10px) scale(1.02);
        box-shadow: 0 20px 50px rgba(255,255,255,0.15);
        background: #1a1a1a;
    }
    
    .vehicle-card .card-img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.6s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    .vehicle-card:hover .card-img {
        transform: scale(1.1);
    }
    
    .category-label {
        font-size: 1.5rem;
        font-weight: 300;
        color: #fff;
        margin-bottom: 2rem;
        text-transform: uppercase;
        letter-spacing: 0.1em;
        padding-bottom: 0.5rem;
        position: relative;
        display: inline-block;
    }
    
    .category-label::after {
        content: '';
        position: absolute;
        bottom: -8px;
        left: 0;
        width: 40px;
        height: 2px;
        background: #d50000;
    }
    
    .vehicles-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1.5rem;
    }
    
    .btn-view-all {
        display: inline-block;
        padding: 1rem 3rem;
        font-size: 0.9rem;
        font-weight: 500;
        text-decoration: none;
        text-transform: uppercase;
        letter-spacing: 0.1em;
        background: #fff;
        color: #000;
        border: 2px solid #fff;
        transition: all 0.3s ease;
    }
    
    .btn-view-all:hover {
        background: transparent;
        color: #fff;
    }

    /* Stats Section */
    .stats-section {
        padding: 6rem 20px;
        background: linear-gradient(135deg, #0a0a0a 0%, #1a1a1a 100%);
        width: 100%;
        position: relative;
        overflow: hidden;
    }
    
    .stats-section::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-image: 
            radial-gradient(ellipse at 30% 20%, rgba(213,0,0,0.15) 0%, transparent 50%),
            radial-gradient(ellipse at 70% 80%, rgba(213,0,0,0.1) 0%, transparent 40%);
        pointer-events: none;
    }
    
    .stats-section::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: url("data:image/svg+xml,%3Csvg width='40' height='40' viewBox='0 0 40 40' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='%23ffffff' fill-opacity='0.02' fill-rule='evenodd'%3E%3Cpath d='M0 40L40 0H20L0 20M40 40V20L20 40'/%3E%3C/g%3E%3C/svg%3E");
        pointer-events: none;
    }
    
    .stats-section .section-header h2 {
        color: #fff !important;
    }
    
    .stats-section .section-header p {
        color: rgba(255,255,255,0.6) !important;
    }
    
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 2rem;
        max-width: 1000px;
        margin: 0 auto;
        position: relative;
        z-index: 1;
    }
    
    .stat-card {
        text-align: center;
        padding: 2.5rem 2rem;
        background: rgba(255,255,255,0.03);
        border: 1px solid rgba(255,255,255,0.08);
        border-radius: 16px;
        backdrop-filter: blur(10px);
        position: relative;
        transition: all 0.4s ease;
    }
    
    .stat-card::before {
        content: '';
        position: absolute;
        bottom: 0;
        left: 50%;
        transform: translateX(-50%);
        width: 40px;
        height: 3px;
        background: linear-gradient(90deg, #d50000, #ff5252);
        border-radius: 3px;
        transition: width 0.3s ease;
    }
    
    .stat-card:hover {
        background: rgba(255,255,255,0.06);
        border-color: rgba(213,0,0,0.3);
        transform: translateY(-5px);
    }
    
    .stat-card:hover::before {
        width: 60px;
    }
    
    .stat-value {
        font-size: 3.5rem;
        font-weight: 700;
        color: #fff;
        line-height: 1;
        margin-bottom: 0.75rem;
        text-shadow: 0 4px 20px rgba(255,255,255,0.1);
    }
    
    .stat-value.accent { 
        color: #d50000; 
        text-shadow: 0 4px 20px rgba(213,0,0,0.3);
    }
    
    .stat-label {
        font-size: 0.85rem;
        color: rgba(255,255,255,0.6);
        text-transform: uppercase;
        letter-spacing: 0.1em;
    }

    /* CTA Section */
    .cta-section {
        padding: 6rem 20px;
        background: linear-gradient(135deg, #d50000 0%, #b71c1c 100%);
        text-align: center;
        width: 100%;
        position: relative;
        overflow: hidden;
    }
    
    .cta-section::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: 
            radial-gradient(circle at 20% 50%, rgba(255,255,255,0.1) 0%, transparent 40%),
            radial-gradient(circle at 80% 50%, rgba(0,0,0,0.1) 0%, transparent 40%);
        pointer-events: none;
    }
    
    .cta-section::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.03'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        pointer-events: none;
    }
    
    .cta-section h2 {
        font-size: clamp(2rem, 4vw, 2.5rem);
        font-weight: 500;
        color: #fff;
        margin-bottom: 1rem;
        position: relative;
        z-index: 1;
    }
    
    .cta-section p {
        font-size: 1.1rem;
        color: rgba(255,255,255,0.9);
        margin-bottom: 2rem;
        position: relative;
        z-index: 1;
    }
    
    .btn-cta {
        display: inline-block;
        padding: 1rem 3rem;
        font-size: 0.9rem;
        font-weight: 500;
        text-decoration: none;
        text-transform: uppercase;
        letter-spacing: 0.1em;
        background: #fff;
        color: #d50000;
        border: 2px solid #fff;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        z-index: 1;
        box-shadow: 0 4px 20px rgba(0,0,0,0.2);
    }
    
    .btn-cta:hover {
        background: transparent;
        color: #fff;
        transform: translateY(-3px);
        box-shadow: 0 8px 30px rgba(0,0,0,0.3);
    }

    @media (max-width: 768px) {
        .features-grid { grid-template-columns: 1fr; }
        .vehicles-grid { grid-template-columns: 1fr 1fr; }
        .stats-grid { grid-template-columns: repeat(2, 1fr); }
        .hero-content { padding-left: 5%; padding-right: 5%; }
    }
    
    /* Scroll Reveal Animations */
    .reveal {
        opacity: 0;
        transform: translateY(40px);
        transition: all 0.8s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .reveal.visible {
        opacity: 1;
        transform: translateY(0);
    }
    .reveal-left {
        opacity: 0;
        transform: translateX(-50px);
        transition: all 0.8s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .reveal-left.visible {
        opacity: 1;
        transform: translateX(0);
    }
    .reveal-right {
        opacity: 0;
        transform: translateX(50px);
        transition: all 0.8s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .reveal-right.visible {
        opacity: 1;
        transform: translateX(0);
    }
    .reveal-scale {
        opacity: 0;
        transform: scale(0.9);
        transition: all 0.8s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .reveal-scale.visible {
        opacity: 1;
        transform: scale(1);
    }
    
    /* Staggered Animation Delays */
    .feature-card:nth-child(1) { transition-delay: 0.1s; }
    .feature-card:nth-child(2) { transition-delay: 0.2s; }
    .feature-card:nth-child(3) { transition-delay: 0.3s; }
    
    .vehicle-card:nth-child(1) { transition-delay: 0.05s; }
    .vehicle-card:nth-child(2) { transition-delay: 0.1s; }
    .vehicle-card:nth-child(3) { transition-delay: 0.15s; }
    .vehicle-card:nth-child(4) { transition-delay: 0.2s; }
    
    .stat-card:nth-child(1) { transition-delay: 0.1s; }
    .stat-card:nth-child(2) { transition-delay: 0.2s; }
    .stat-card:nth-child(3) { transition-delay: 0.3s; }
    .stat-card:nth-child(4) { transition-delay: 0.4s; }
    
    /* Hero Text Animation */
    .hero-text .hero-welcome {
        opacity: 0;
        transform: translateY(20px);
        animation: fadeInUp 0.8s ease forwards 0.3s;
    }
    .hero-text .hero-title {
        opacity: 0;
        transform: translateY(30px);
        animation: fadeInUp 0.8s ease forwards 0.5s;
    }
    .hero-text > div {
        opacity: 0;
        transform: translateY(30px);
        animation: fadeInUp 0.8s ease forwards 0.7s;
    }
    
    @keyframes fadeInUp {
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    /* Enhanced Feature Card Hover */
    .feature-card {
        opacity: 0;
        transform: translateY(40px);
        transition: opacity 0.6s ease, transform 0.6s ease, box-shadow 0.4s ease, border-color 0.4s ease;
    }
    .feature-card.visible {
        opacity: 1;
        transform: translateY(0);
    }
    
    /* Enhanced Vehicle Card Animation */
    .vehicle-card {
        opacity: 0;
        transform: translateY(30px);
    }
    .vehicle-card.visible {
        opacity: 1;
        transform: translateY(0);
    }
    
    /* Enhanced Stat Card Animation */
    .stat-card {
        opacity: 0;
        transform: translateY(40px) scale(0.95);
    }
    .stat-card.visible {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
    
    /* Section Header Animation */
    .section-header h2,
    .section-header p {
        opacity: 0;
        transform: translateY(20px);
        transition: all 0.6s ease;
    }
    .section-header.visible h2,
    .section-header.visible p {
        opacity: 1;
        transform: translateY(0);
    }
    .section-header h2 { transition-delay: 0s; }
    .section-header p { transition-delay: 0.15s; }
    
    /* Category Label Animation */
    .category-label {
        opacity: 0;
        transform: translateX(-30px);
        transition: all 0.6s ease;
    }
    .category-label.visible {
        opacity: 1;
        transform: translateX(0);
    }
    
    /* CTA Section Animation */
    .cta-section h2,
    .cta-section p,
    .cta-section .btn-cta {
        opacity: 0;
        transform: translateY(20px);
        transition: all 0.6s ease;
    }
    .cta-section.visible h2,
    .cta-section.visible p,
    .cta-section.visible .btn-cta {
        opacity: 1;
        transform: translateY(0);
    }
    .cta-section h2 { transition-delay: 0s; }
    .cta-section p { transition-delay: 0.1s; }
    .cta-section .btn-cta { transition-delay: 0.2s; }
</style>

<!-- Video Hero Section -->
<section class="video-hero">
    <div class="video-container">
        <video autoplay muted loop playsinline class="hero-video">
            <source src="../../assets/video/heroDashboard.mp4" type="video/mp4">
        </video>
        <div class="video-overlay"></div>
    </div>
    
    <div class="hero-content">
        <div class="hero-text">
            <p class="hero-welcome">Selamat datang kembali, <?php echo htmlspecialchars($user_name); ?>!</p>
            <h1 class="hero-title">
                Kemewahan dan Kenyamanan<br>
                <span style="font-weight: 600;">dalam Setiap Perjalanan</span>
            </h1>
            <div>
                <a href="vehicles.php" class="btn-hero btn-hero-primary">Sewa Sekarang</a>
                <a href="my-bookings.php" class="btn-hero btn-hero-secondary">Pesanan Saya</a>
            </div>
        </div>
    </div>
    
    <div class="scroll-indicator">
        <div class="scroll-arrow"></div>
    </div>
</section>

<!-- Features Section -->
<section class="features-section">
    <div class="section-header">
        <h2 style="color: #000;">Mengapa Memilih EzRent?</h2>
        <p>Pengalaman sewa kendaraan yang tak tertandingi</p>
    </div>
    
    <div class="features-grid">
        <div class="feature-card">
            <div class="feature-icon">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                </svg>
            </div>
            <h3>Terjamin & Aman</h3>
            <p>Dukungan 24/7 dan perlindungan asuransi komprehensif untuk ketenangan Anda.</p>
        </div>
        
        <div class="feature-card">
            <div class="feature-icon">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <line x1="12" y1="1" x2="12" y2="23"></line>
                    <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                </svg>
            </div>
            <h3>Harga Transparan</h3>
            <p>Harga kompetitif tanpa biaya tersembunyi. Apa yang Anda lihat adalah apa yang Anda bayar.</p>
        </div>
        
        <div class="feature-card">
            <div class="feature-icon">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon>
                </svg>
            </div>
            <h3>Proses Cepat</h3>
            <p>Pemesanan online dalam hitungan menit. Tanpa antri, tanpa ribet.</p>
        </div>
    </div>
</section>

<!-- Vehicles Preview Section -->
<section class="vehicles-preview">
    <div style="max-width: 1200px; margin: 0 auto;">
        <div class="section-header">
            <h2 style="color: #fff;">Pilihan Kendaraan Kami</h2>
            <p style="color: rgba(255,255,255,0.7);">Koleksi kendaraan berkualitas untuk perjalanan Anda</p>
        </div>
        
        <!-- Motor Section -->
        <div style="margin-bottom: 4rem;">
            <h3 class="category-label">Motor</h3>
            <div class="vehicles-grid">
                <?php foreach ($motors as $motor): 
                    $images = json_decode($motor['images'], true);
                    $image = isset($images[0]) ? $images[0] : 'default.jpg';
                ?>
                <a href="booking-process.php?id=<?php echo $motor['id']; ?>" class="vehicle-card">
                    <div style="aspect-ratio: 4/3; overflow: hidden;">
                        <img src="../../assets/images/vehicles/<?php echo htmlspecialchars($image); ?>" alt="<?php echo htmlspecialchars($motor['nama']); ?>" class="card-img">
                    </div>
                    <div style="padding: 1.25rem;">
                        <h4 style="color: #fff; font-size: 1rem; font-weight: 500; margin-bottom: 0.5rem;"><?php echo htmlspecialchars($motor['nama']); ?></h4>
                        <p style="color: rgba(255,255,255,0.6); font-size: 0.9rem; margin-bottom: 0.75rem;"><?php echo htmlspecialchars($motor['merek']); ?></p>
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="color: #fff; font-weight: 600;">Rp <?php echo number_format($motor['harga_per_hari'], 0, ',', '.'); ?><span style="font-weight: 300; font-size: 0.85rem;">/hari</span></span>
                            <span style="color: #d50000; font-size: 0.8rem;">Pesan →</span>
                        </div>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Mobil Section -->
        <div style="margin-bottom: 4rem;">
            <h3 class="category-label">Mobil</h3>
            <div class="vehicles-grid">
                <?php foreach ($mobils as $mobil): 
                    $images = json_decode($mobil['images'], true);
                    $image = isset($images[0]) ? $images[0] : 'default.jpg';
                ?>
                <a href="booking-process.php?id=<?php echo $mobil['id']; ?>" class="vehicle-card">
                    <div style="aspect-ratio: 4/3; overflow: hidden;">
                        <img src="../../assets/images/vehicles/<?php echo htmlspecialchars($image); ?>" alt="<?php echo htmlspecialchars($mobil['nama']); ?>" class="card-img">
                    </div>
                    <div style="padding: 1.25rem;">
                        <h4 style="color: #fff; font-size: 1rem; font-weight: 500; margin-bottom: 0.5rem;"><?php echo htmlspecialchars($mobil['nama']); ?></h4>
                        <p style="color: rgba(255,255,255,0.6); font-size: 0.9rem; margin-bottom: 0.75rem;"><?php echo htmlspecialchars($mobil['merek']); ?></p>
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="color: #fff; font-weight: 600;">Rp <?php echo number_format($mobil['harga_per_hari'], 0, ',', '.'); ?><span style="font-weight: 300; font-size: 0.85rem;">/hari</span></span>
                            <span style="color: #d50000; font-size: 0.8rem;">Pesan →</span>
                        </div>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- CTA View All -->
        <div style="text-align: center; margin-top: 2rem;">
            <a href="vehicles.php" class="btn-view-all">Lihat Semua Kendaraan</a>
        </div>
    </div>
</section>

<!-- Stats Section -->
<section class="stats-section">
    <div class="section-header">
        <h2 style="color: #000;">EzRent dalam Angka</h2>
        <p>Dipercaya ribuan pelanggan di seluruh Indonesia</p>
    </div>
    
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-value">500+</div>
            <div class="stat-label">Kendaraan</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">10K+</div>
            <div class="stat-label">Pelanggan</div>
        </div>
        <div class="stat-card">
            <div class="stat-value accent">50K+</div>
            <div class="stat-label">Transaksi</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">4.9</div>
            <div class="stat-label">Rating</div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="cta-section">
    <h2>Siap Memulai Perjalanan?</h2>
    <p>Temukan kendaraan sempurna untuk petualangan berikutnya</p>
    <a href="vehicles.php" class="btn-cta">Lihat Kendaraan</a>
</section>

        </div>
    </main>

<?php include '../../php/includes/footer.php'; ?>

<script>
// Scroll Reveal Animation with Intersection Observer
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

// Observe all animatable elements
document.querySelectorAll('.section-header, .feature-card, .vehicle-card, .stat-card, .category-label, .cta-section, .reveal, .reveal-left, .reveal-right, .reveal-scale').forEach(el => {
    scrollObserver.observe(el);
});

// Parallax effect on hero video
window.addEventListener('scroll', () => {
    const scrolled = window.pageYOffset;
    const hero = document.querySelector('.video-hero');
    const heroContent = document.querySelector('.hero-content');
    
    if (hero && scrolled < hero.offsetHeight) {
        if (heroContent) {
            heroContent.style.opacity = 1 - (scrolled / hero.offsetHeight);
            heroContent.style.transform = `translateY(${scrolled * 0.3}px)`;
        }
    }
});

// Counter animation for stats
function animateCounter(element, target, duration = 2000) {
    let start = 0;
    const increment = target / (duration / 16);
    
    function updateCounter() {
        start += increment;
        if (start < target) {
            element.textContent = Math.floor(start).toLocaleString() + (element.dataset.suffix || '');
            requestAnimationFrame(updateCounter);
        } else {
            element.textContent = target.toLocaleString() + (element.dataset.suffix || '');
        }
    }
    
    updateCounter();
}

// Trigger counter animation when stats section is visible
const statsObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            const statValues = entry.target.querySelectorAll('.stat-value');
            statValues.forEach(stat => {
                const text = stat.textContent;
                if (text.includes('500')) {
                    stat.textContent = '0+';
                    stat.dataset.suffix = '+';
                    animateCounter(stat, 500);
                } else if (text.includes('10K')) {
                    stat.textContent = '0K+';
                    stat.dataset.suffix = 'K+';
                    animateCounter(stat, 10);
                } else if (text.includes('50K')) {
                    stat.textContent = '0K+';
                    stat.dataset.suffix = 'K+';
                    animateCounter(stat, 50);
                } else if (text.includes('4.9')) {
                    stat.textContent = '0';
                    stat.dataset.suffix = '';
                    // Special animation for rating
                    let rating = 0;
                    const ratingInterval = setInterval(() => {
                        rating += 0.1;
                        if (rating >= 4.9) {
                            stat.textContent = '4.9';
                            clearInterval(ratingInterval);
                        } else {
                            stat.textContent = rating.toFixed(1);
                        }
                    }, 40);
                }
            });
            statsObserver.unobserve(entry.target);
        }
    });
}, { threshold: 0.3 });

const statsSection = document.querySelector('.stats-section');
if (statsSection) {
    statsObserver.observe(statsSection);
}
</script>