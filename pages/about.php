<?php 
$page_title = "Tentang Kami - EzRent";
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title><?php echo $page_title; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="../assets/css/bootstrap.min.css" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background: #fff; color: #000; }
        
        /* Hero dengan Background Image */
        .hero {
            position: relative;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            overflow: hidden;
        }
        .hero-bg {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('https://images.unsplash.com/photo-1449965408869-eaa3f722e40d?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80') center/cover no-repeat;
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
                radial-gradient(circle at 30% 70%, rgba(213,0,0,0.1) 0%, transparent 50%),
                radial-gradient(circle at 70% 30%, rgba(255,255,255,0.05) 0%, transparent 50%);
            z-index: 3;
        }
        .hero-content {
            position: relative;
            z-index: 10;
            padding: 8rem 2rem 4rem;
            max-width: 900px;
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
            animation: fadeInDown 0.6s ease;
            backdrop-filter: blur(10px);
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
            margin: 0 auto;
            line-height: 1.8;
            animation: fadeInUp 1s ease;
        }
        .hero-desc {
            margin-bottom: 2rem !important;
        }
        .hero-cta {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
            animation: fadeInUp 1.2s ease;
        }
        .btn-explore {
            display: inline-block;
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
        .btn-explore:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 40px rgba(213,0,0,0.5);
        }
        .btn-contact {
            display: inline-block;
            background: transparent;
            border: 2px solid rgba(255,255,255,0.5);
            color: #fff;
            text-decoration: none;
            padding: 1rem 2.5rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            font-size: 0.85rem;
            transition: all 0.3s;
        }
        .btn-contact:hover {
            background: rgba(255,255,255,0.1);
            border-color: #fff;
            transform: translateY(-3px);
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
        @keyframes bounce {
            0%, 100% { transform: translateX(-50%) translateY(0); }
            50% { transform: translateX(-50%) translateY(-10px); }
        }
        @keyframes scrollWheel {
            0% { opacity: 1; transform: translateX(-50%) translateY(0); }
            100% { opacity: 0; transform: translateX(-50%) translateY(10px); }
        }
        .animate-pulse {
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
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
        
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes fadeInDown {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        /* Section Title */
        .section-title {
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.2em;
            color: #d50000;
            margin-bottom: 1rem;
        }
        
        /* Vision Mission - Elegant Cards */
        .vm-section {
            padding: 6rem 2rem;
            max-width: 1200px;
            margin: 0 auto;
            background: linear-gradient(180deg, #fff 0%, #fafafa 100%);
        }
        .vm-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 3rem;
        }
        .vm-card {
            text-align: center;
            padding: 3rem 2.5rem;
            background: #fff;
            border: 1px solid #e5e7eb;
            box-shadow: 0 15px 50px rgba(0,0,0,0.08);
            transition: all 0.4s ease;
            position: relative;
            overflow: hidden;
        }
        .vm-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #d50000 0%, #ff5252 100%);
            transform: scaleX(0);
            transition: transform 0.4s ease;
        }
        .vm-card:hover::before {
            transform: scaleX(1);
        }
        .vm-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 25px 60px rgba(0,0,0,0.12);
            border-color: #000;
        }
        .vm-card .icon {
            font-size: 3.5rem;
            margin-bottom: 1.5rem;
            display: inline-block;
            padding: 1rem;
            background: linear-gradient(135deg, #f8f9fa 0%, #fff 100%);
            border-radius: 50%;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        .vm-card h2 {
            font-size: 1.75rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: #000;
        }
        .vm-card p {
            color: #6b7280;
            line-height: 1.9;
            font-size: 1.05rem;
        }
        
        /* History Values - Glass Effect */
        .hv-section {
            position: relative;
            padding: 6rem 2rem;
            overflow: hidden;
        }
        .hv-bg {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('https://images.unsplash.com/photo-1503376780353-7e6692767b70?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80') center/cover no-repeat;
            filter: grayscale(100%);
            z-index: 1;
        }
        .hv-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(245,245,245,0.95) 0%, rgba(255,255,255,0.9) 100%);
            z-index: 2;
        }
        .hv-container {
            position: relative;
            max-width: 1200px;
            margin: 0 auto;
            z-index: 10;
        }
        .hv-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 3rem;
        }
        .hv-card {
            text-align: center;
            padding: 3rem 2.5rem;
            background: rgba(255,255,255,0.9);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(0,0,0,0.1);
            box-shadow: 0 20px 50px rgba(0,0,0,0.1);
            transition: all 0.4s ease;
        }
        .hv-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 30px 60px rgba(0,0,0,0.15);
        }
        .hv-card .icon {
            font-size: 3.5rem;
            margin-bottom: 1.5rem;
        }
        .hv-card h2 {
            font-size: 1.75rem;
            font-weight: 600;
            margin-bottom: 1rem;
        }
        .hv-card p {
            color: #6b7280;
            line-height: 1.9;
            font-size: 1.05rem;
        }
        
        .hv-card p {
            color: #6b7280;
            line-height: 1.8;
            font-size: 1.05rem;
        }
        
        /* Stats - Animated Counter Style */
        .stats-section {
            padding: 6rem 2rem;
            max-width: 1200px;
            margin: 0 auto;
            text-align: center;
            background: linear-gradient(180deg, #fafafa 0%, #fff 100%);
        }
        .stats-section h2 {
            font-size: clamp(2rem, 4vw, 3rem);
            font-weight: 300;
            margin-bottom: 0.5rem;
        }
        .stats-section h2 strong {
            font-weight: 700;
        }
        .stats-section > p {
            color: #6b7280;
            margin-bottom: 4rem;
            font-size: 1.1rem;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 2rem;
        }
        .stat-item {
            padding: 2.5rem 2rem;
            background: #fff;
            border: 1px solid #e5e7eb;
            transition: all 0.4s ease;
            box-shadow: 0 10px 40px rgba(0,0,0,0.05);
            position: relative;
            overflow: hidden;
        }
        .stat-item::before {
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
        .stat-item:hover::before {
            transform: scaleX(1);
        }
        .stat-item:hover {
            border-color: #000;
            transform: translateY(-8px);
            box-shadow: 0 25px 50px rgba(0,0,0,0.12);
        }
        .stat-item .value {
            font-size: clamp(2.5rem, 4vw, 3.5rem);
            font-weight: 700;
            color: #000;
            margin-bottom: 0.5rem;
            background: linear-gradient(135deg, #000 0%, #333 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .stat-item .label {
            color: #6b7280;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            font-weight: 500;
        }
        
        /* Team - Premium Dark Section */
        .team-section {
            padding: 6rem 2rem;
            background: linear-gradient(135deg, #0a0a0a 0%, #1a1a1a 50%, #0a0a0a 100%);
            position: relative;
            overflow: hidden;
        }
        .team-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('https://images.unsplash.com/photo-1522071820081-009f0129c71c?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80') center/cover no-repeat;
            opacity: 0.08;
            pointer-events: none;
        }
        .team-section::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: 
                radial-gradient(circle at 20% 30%, rgba(213,0,0,0.15) 0%, transparent 40%),
                radial-gradient(circle at 80% 70%, rgba(213,0,0,0.1) 0%, transparent 40%),
                radial-gradient(circle at 50% 50%, rgba(255,255,255,0.02) 0%, transparent 50%);
            pointer-events: none;
        }
        .team-decoration {
            position: absolute;
            width: 300px;
            height: 300px;
            border: 1px solid rgba(213,0,0,0.1);
            border-radius: 50%;
            pointer-events: none;
        }
        .team-decoration-1 {
            top: -100px;
            left: -100px;
            animation: rotateSlow 30s linear infinite;
        }
        .team-decoration-2 {
            bottom: -150px;
            right: -150px;
            width: 400px;
            height: 400px;
            animation: rotateSlow 40s linear infinite reverse;
        }
        @keyframes rotateSlow {
            100% { transform: rotate(360deg); }
        }
        .team-container {
            max-width: 1200px;
            margin: 0 auto;
            text-align: center;
            position: relative;
            z-index: 2;
        }
        .team-header {
            margin-bottom: 4rem;
        }
        .team-badge {
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
            backdrop-filter: blur(10px);
        }
        .team-section h2 {
            font-size: clamp(2.5rem, 5vw, 3.5rem);
            font-weight: 300;
            margin-bottom: 1rem;
            color: #fff;
        }
        .team-section h2 strong {
            font-weight: 700;
            background: linear-gradient(135deg, #fff 0%, #d50000 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .team-section .team-subtitle {
            color: rgba(255,255,255,0.6);
            margin-bottom: 1rem;
            font-size: 1.1rem;
            max-width: 500px;
            margin-left: auto;
            margin-right: auto;
        }
        .team-line {
            width: 80px;
            height: 3px;
            background: linear-gradient(90deg, transparent, #d50000, transparent);
            margin: 0 auto;
        }
        .team-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 2rem;
            margin-top: 3rem;
        }
        .team-card {
            background: linear-gradient(145deg, rgba(30,30,30,0.9) 0%, rgba(20,20,20,0.95) 100%);
            border: 1px solid rgba(255,255,255,0.08);
            padding: 3rem 1.5rem 2.5rem;
            text-align: center;
            transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            backdrop-filter: blur(10px);
        }
        .team-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, transparent, #d50000, transparent);
            transform: scaleX(0);
            transition: transform 0.4s ease;
        }
        .team-card::after {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: conic-gradient(from 0deg, transparent, rgba(213,0,0,0.1), transparent 30%);
            animation: none;
            opacity: 0;
            transition: opacity 0.4s ease;
        }
        .team-card:hover::before {
            transform: scaleX(1);
        }
        .team-card:hover::after {
            opacity: 1;
            animation: rotateBorder 4s linear infinite;
        }
        .team-card:hover {
            transform: translateY(-15px) scale(1.02);
            background: linear-gradient(145deg, rgba(40,40,40,0.95) 0%, rgba(25,25,25,0.98) 100%);
            border-color: rgba(213,0,0,0.5);
            box-shadow: 
                0 25px 60px rgba(0,0,0,0.5),
                0 0 40px rgba(213,0,0,0.2),
                inset 0 1px 0 rgba(255,255,255,0.1);
        }
        .team-card .photo-wrapper {
            position: relative;
            width: 130px;
            height: 130px;
            margin: 0 auto 1.5rem;
        }
        .team-card .photo-ring {
            position: absolute;
            top: -5px;
            left: -5px;
            right: -5px;
            bottom: -5px;
            border: 2px solid rgba(213,0,0,0.3);
            border-radius: 50%;
            animation: pulse-ring 2s ease-in-out infinite;
            opacity: 0;
            transition: opacity 0.4s ease;
        }
        .team-card:hover .photo-ring {
            opacity: 1;
        }
        @keyframes pulse-ring {
            0%, 100% { transform: scale(1); opacity: 0.5; }
            50% { transform: scale(1.1); opacity: 0; }
        }
        .team-card .photo {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            margin: 0 auto;
            overflow: hidden;
            border: 3px solid rgba(255,255,255,0.1);
            position: relative;
            z-index: 1;
            transition: all 0.4s ease;
            box-shadow: 0 10px 30px rgba(0,0,0,0.4);
        }
        .team-card:hover .photo {
            border-color: #d50000;
            transform: scale(1.08);
            box-shadow: 
                0 15px 40px rgba(0,0,0,0.5),
                0 0 30px rgba(213,0,0,0.3);
        }
        .team-card .photo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.4s ease;
            filter: grayscale(30%);
        }
        .team-card:hover .photo img {
            transform: scale(1.15);
            filter: grayscale(0%);
        }
        .team-card .member-info {
            position: relative;
            z-index: 1;
        }
        .team-card h3 {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: #fff;
            position: relative;
            z-index: 1;
            transition: all 0.3s ease;
        }
        .team-card:hover h3 {
            color: #fff;
            text-shadow: 0 0 20px rgba(255,255,255,0.3);
        }
        .team-card .role {
            color: rgba(255,255,255,0.5);
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.15em;
            position: relative;
            z-index: 1;
            transition: color 0.3s ease;
            margin-bottom: 1rem;
            display: block;
        }
        .team-card:hover .role {
            color: #d50000;
        }
        .team-card .social-links {
            display: flex;
            justify-content: center;
            gap: 0.75rem;
            opacity: 0;
            transform: translateY(10px);
            transition: all 0.4s ease;
        }
        .team-card:hover .social-links {
            opacity: 1;
            transform: translateY(0);
        }
        .team-card .social-link {
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid rgba(255,255,255,0.2);
            color: rgba(255,255,255,0.6);
            transition: all 0.3s ease;
            text-decoration: none;
        }
        .team-card .social-link:hover {
            background: #d50000;
            border-color: #d50000;
            color: #fff;
            transform: translateY(-3px);
        }
        .team-card .social-link svg {
            width: 14px;
            height: 14px;
        }
        
        /* Slide In Animation */
        .slide-in-left {
            opacity: 0;
            transform: translateX(-80px);
            transition: all 0.8s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .slide-in-right {
            opacity: 0;
            transform: translateX(80px);
            transition: all 0.8s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .slide-in-up {
            opacity: 0;
            transform: translateY(60px);
            transition: all 0.8s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .slide-in-left.revealed,
        .slide-in-right.revealed,
        .slide-in-up.revealed {
            opacity: 1;
            transform: translateX(0) translateY(0);
        }
        
        /* Stagger delays for team cards */
        .team-card:nth-child(1) { transition-delay: 0.1s; }
        .team-card:nth-child(2) { transition-delay: 0.2s; }
        .team-card:nth-child(3) { transition-delay: 0.3s; }
        .team-card:nth-child(4) { transition-delay: 0.4s; }
        
        /* CTA - Animated Section */
        .cta-section {
            position: relative;
            padding: 8rem 2rem;
            text-align: center;
            overflow: hidden;
        }
        .cta-bg {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('https://images.unsplash.com/photo-1493238792000-8113da705763?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80') center/cover no-repeat;
            z-index: 1;
            animation: slowZoom 20s ease-in-out infinite alternate;
        }
        @keyframes slowZoom {
            0% { transform: scale(1); }
            100% { transform: scale(1.1); }
        }
        .cta-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(0,0,0,0.95) 0%, rgba(20,20,20,0.9) 50%, rgba(0,0,0,0.95) 100%);
            z-index: 2;
        }
        .cta-glow {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            z-index: 3;
            animation: glowPulse 4s ease-in-out infinite;
        }
        @keyframes glowPulse {
            0%, 100% { opacity: 0.5; transform: translate(-50%, -50%) scale(1); }
            50% { opacity: 1; transform: translate(-50%, -50%) scale(1.2); }
        }
        .cta-particles {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 4;
            pointer-events: none;
        }
        .cta-content {
            position: relative;
            z-index: 10;
            max-width: 800px;
            margin: 0 auto;
        }
        .cta-section h2 {
            color: #fff;
            font-size: clamp(2rem, 4vw, 3rem);
            font-weight: 300;
            margin-bottom: 1rem;
            text-shadow: 0 4px 20px rgba(0,0,0,0.5);
        }
        .cta-section h2 strong {
            font-weight: 700;
            color: #fff;
        }
        .cta-section p {
            color: rgba(255,255,255,0.7);
            margin-bottom: 2.5rem;
            font-size: 1.1rem;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }
        .cta-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
            margin-bottom: 3rem;
        }
        .btn-cta-primary {
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
            position: relative;
            overflow: hidden;
        }
        .btn-cta-primary::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }
        .btn-cta-primary:hover::before {
            left: 100%;
        }
        .btn-cta-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 40px rgba(213,0,0,0.5);
        }
        .btn-cta-primary svg {
            width: 18px;
            height: 18px;
            transition: transform 0.3s;
        }
        .btn-cta-primary:hover svg {
            transform: translateX(5px);
        }
        .btn-cta-secondary {
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
        .btn-cta-secondary:hover {
            background: rgba(255,255,255,0.1);
            border-color: #fff;
            transform: translateY(-3px);
        }
        .cta-trust {
            display: flex;
            justify-content: center;
            gap: 3rem;
            flex-wrap: wrap;
        }
        .trust-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: rgba(255,255,255,0.6);
            font-size: 0.85rem;
        }
        .trust-item svg {
            width: 18px;
            height: 18px;
            color: #d50000;
        }
        
        @media (max-width: 1024px) {
            .team-grid, .team-stats { grid-template-columns: repeat(2, 1fr); }
            .stats-grid { grid-template-columns: repeat(2, 1fr); }
        }
        /* Stats Animated Section - Light Theme */
        .stats-animated {
            padding: 6rem 2rem;
            background: linear-gradient(180deg, #fff 0%, #f8f9fa 50%, #fff 100%);
            position: relative;
            overflow: hidden;
        }
        .stats-animated::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: 
                radial-gradient(circle at 20% 50%, rgba(213,0,0,0.08) 0%, transparent 50%),
                radial-gradient(circle at 80% 50%, rgba(213,0,0,0.05) 0%, transparent 50%);
            pointer-events: none;
        }
        .stats-animated::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('https://images.unsplash.com/photo-1449965408869-eaa3f722e40d?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80') center/cover no-repeat;
            opacity: 0.03;
            pointer-events: none;
        }
        .stats-header {
            text-align: center;
            max-width: 600px;
            margin: 0 auto 4rem;
            position: relative;
            z-index: 2;
        }
        .stats-badge {
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
        .stats-title {
            color: #000;
            font-size: clamp(2rem, 4vw, 3rem);
            font-weight: 300;
            margin-bottom: 1rem;
        }
        .stats-title strong {
            font-weight: 700;
            background: linear-gradient(135deg, #d50000 0%, #b71c1c 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .stats-subtitle {
            color: #6b7280;
            font-size: 1.1rem;
        }
        .stats-line {
            width: 80px;
            height: 3px;
            background: linear-gradient(90deg, transparent, #d50000, transparent);
            margin: 1.5rem auto 0;
        }
        .stats-container {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 2rem;
            position: relative;
            z-index: 2;
        }
        .stat-icon {
            width: 70px;
            height: 70px;
            margin: 0 auto 1.5rem;
            background: linear-gradient(135deg, #fff 0%, #f8f9fa 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid rgba(213,0,0,0.15);
            transition: all 0.4s ease;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
        }
        .stat-icon svg {
            width: 30px;
            height: 30px;
            color: #d50000;
        }
        .stat-box:hover .stat-icon {
            transform: scale(1.1) rotate(5deg);
            background: linear-gradient(135deg, #d50000 0%, #b71c1c 100%);
            border-color: #d50000;
            box-shadow: 0 15px 40px rgba(213,0,0,0.3);
        }
        .stat-box:hover .stat-icon svg {
            color: #fff;
        }
        .stat-desc {
            color: #9ca3af;
            font-size: 0.85rem;
            margin-top: 0.5rem;
        }
        
        /* Scroll Color Animation */
        .scroll-color {
            transition: all 0.6s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .scroll-color.color-active {
            background: #fff !important;
            border-color: #d50000 !important;
            transform: translateY(-10px) scale(1.02);
            box-shadow: 0 25px 60px rgba(213,0,0,0.15) !important;
        }
        .scroll-color.color-active .stat-value,
        .scroll-color.color-active h3 {
            color: #000 !important;
        }
        .scroll-color.color-active .stat-label,
        .scroll-color.color-active .role {
            color: #d50000 !important;
        }
        .scroll-color.color-active .photo {
            border-color: #d50000 !important;
            box-shadow: 0 0 30px rgba(213,0,0,0.4);
        }
        
        /* Stats box in light section */
        .stats-animated .stat-box {
            background: #fff;
            border: 1px solid #e5e7eb;
            backdrop-filter: blur(10px);
            position: relative;
            overflow: hidden;
            box-shadow: 0 10px 40px rgba(0,0,0,0.06);
        }
        .stats-animated .stat-box::before {
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
        .stats-animated .stat-box:hover::before {
            transform: scaleX(1);
        }
        .stats-animated .stat-box .stat-value {
            color: #000;
            font-size: clamp(2.5rem, 4vw, 3.5rem);
            font-weight: 700;
            background: linear-gradient(135deg, #000 0%, #333 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            position: relative;
            z-index: 2;
        }
        .stats-animated .stat-box .stat-label {
            color: #000;
            font-weight: 600;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            position: relative;
            z-index: 2;
        }
        .stats-animated .stat-box .stat-desc {
            color: #6b7280;
            font-size: 0.85rem;
            margin-top: 0.5rem;
            font-style: normal;
            position: relative;
            z-index: 2;
        }
        .stats-animated .stat-box:hover {
            background: #fff;
            border-color: #d50000;
            transform: translateY(-12px) scale(1.02);
            box-shadow: 0 25px 60px rgba(213,0,0,0.15);
        }
        .stats-animated .stat-box:hover .stat-value {
            background: linear-gradient(135deg, #d50000 0%, #b71c1c 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .stats-animated .stat-box:hover .stat-icon {
            animation: iconBounce 0.6s ease;
        }
        @keyframes iconBounce {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.2) rotate(10deg); }
        }
        .stat-box {
            text-align: center;
            padding: 2.5rem 2rem;
            background: #fff;
            border: 1px solid #e5e7eb;
            transition: all 0.4s ease;
            box-shadow: 0 10px 40px rgba(0,0,0,0.05);
            position: relative;;
            overflow: hidden;
        }
        .stat-box::before {
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
        .stat-box:hover::before {
            transform: scaleX(1);
        }
        .stat-box:hover {
            border-color: #000;
            transform: translateY(-8px);
            box-shadow: 0 25px 50px rgba(0,0,0,0.12);
        }
        .stat-box .stat-value {
            font-size: clamp(2.5rem, 4vw, 3.5rem);
            font-weight: 700;
            color: #000;
            margin-bottom: 0.5rem;
        }
        .stat-box .stat-label {
            color: #6b7280;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            font-weight: 500;
        }
        
        /* Scroll Reveal Animation */
        .scroll-reveal {
            opacity: 0;
            transform: translateY(40px);
            transition: all 0.8s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .scroll-reveal.revealed {
            opacity: 1;
            transform: translateY(0);
        }
        
        /* Scroll Progress Indicator */
        .scroll-progress {
            position: fixed;
            top: 80px;
            right: 20px;
            width: 4px;
            height: 100px;
            background: rgba(0,0,0,0.1);
            z-index: 100;
            border-radius: 2px;
        }
        .scroll-progress-bar {
            width: 100%;
            height: 0%;
            background: linear-gradient(180deg, #d50000 0%, #ff5252 100%);
            border-radius: 2px;
            transition: height 0.1s ease;
        }
        
        /* Stagger animation for cards */
        .team-card.scroll-reveal:nth-child(1) { transition-delay: 0.1s; }
        .team-card.scroll-reveal:nth-child(2) { transition-delay: 0.2s; }
        .team-card.scroll-reveal:nth-child(3) { transition-delay: 0.3s; }
        .team-card.scroll-reveal:nth-child(4) { transition-delay: 0.4s; }
        
        .stat-box.scroll-reveal:nth-child(1) { transition-delay: 0.1s; }
        .stat-box.scroll-reveal:nth-child(2) { transition-delay: 0.2s; }
        .stat-box.scroll-reveal:nth-child(3) { transition-delay: 0.3s; }
        .stat-box.scroll-reveal:nth-child(4) { transition-delay: 0.4s; }
        
        @media (max-width: 1024px) {
            .stats-container { grid-template-columns: repeat(2, 1fr); }
            .team-grid { grid-template-columns: repeat(2, 1fr); }
        }
        @media (max-width: 768px) {
            .hero h1 { font-size: 2.5rem; }
            .nav-links { display: none; }
            .team-grid { grid-template-columns: 1fr; }
            .stats-container { grid-template-columns: repeat(2, 1fr); }
            .scroll-progress { display: none; }
        }
    </style>
</head>
<body>

<?php include '../php/includes/header.php'; ?>

<!-- Hero -->
<section class="hero">
    <div class="hero-bg"></div>
    <div class="hero-overlay"></div>
    <div class="hero-pattern"></div>
    <div class="hero-particles" id="particles"></div>
    <div class="hero-content">
        <h1>Tentang <strong>EzRent</strong></h1>
        <p class="hero-desc">Platform sewa kendaraan modern yang memudahkan masyarakat dalam menyewa motor dan mobil berkualitas dengan proses cepat, mudah, dan terpercaya.</p>
        <div class="hero-cta">
            <a href="vehicles.php" class="btn-explore">Lihat Kendaraan</a>
            <a href="contact.php" class="btn-contact">Hubungi Kami</a>
        </div>
    </div>
    <div class="scroll-indicator">
        <div class="mouse"></div>
        <span>Scroll</span>
    </div>
</section>

<!-- Stats Section -->
<section class="stats-animated">
    <div class="stats-header">
        <h2 class="stats-title scroll-reveal">EzRent dalam <strong>Angka</strong></h2>
        <p class="stats-subtitle scroll-reveal">Bukti kepercayaan pelanggan terhadap layanan kami</p>
        <div class="stats-line"></div>
    </div>
    <div class="stats-container">
        <div class="stat-box scroll-reveal scroll-color">
            <div class="stat-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M19 17h2c.6 0 1-.4 1-1v-3c0-.9-.7-1.7-1.5-1.9L18 10l-1.6-3.2c-.3-.6-.9-1-1.5-1H9.1c-.6 0-1.2.4-1.5 1L6 10l-2.5.1C2.7 10.3 2 11.1 2 12v3c0 .6.4 1 1 1h2"/>
                    <circle cx="7" cy="17" r="2"/>
                    <circle cx="17" cy="17" r="2"/>
                </svg>
            </div>
            <div class="stat-value">500+</div>
            <div class="stat-label">Unit Kendaraan</div>
            <div class="stat-desc">Motor & Mobil Tersedia</div>
        </div>
        <div class="stat-box scroll-reveal scroll-color">
            <div class="stat-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                    <circle cx="9" cy="7" r="4"/>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                </svg>
            </div>
            <div class="stat-value">10K+</div>
            <div class="stat-label">Pelanggan Puas</div>
            <div class="stat-desc">Kepercayaan Terjaga</div>
        </div>
        <div class="stat-box scroll-reveal scroll-color">
            <div class="stat-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/>
                    <polyline points="12 6 12 12 16 14"/>
                </svg>
            </div>
            <div class="stat-value">4+</div>
            <div class="stat-label">Tahun Pengalaman</div>
            <div class="stat-desc">Melayani Sejak 2020</div>
        </div>
        <div class="stat-box scroll-reveal scroll-color">
            <div class="stat-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
                </svg>
            </div>
            <div class="stat-value">24/7</div>
            <div class="stat-label">Customer Support</div>
            <div class="stat-desc">Siap Membantu Anda</div>
        </div>
    </div>
</section>



<!-- Team -->
<section class="team-section">
    <div class="team-decoration team-decoration-1"></div>
    <div class="team-decoration team-decoration-2"></div>
    <div class="team-container">
        <div class="team-header">
            <div class="team-badge slide-in-up">Our Team</div>
            <h2 class="slide-in-up">Tim <strong>Kami</strong></h2>
            <p class="team-subtitle slide-in-up">Profesional berdedikasi di balik kesuksesan EzRent</p>
            <div class="team-line"></div>
        </div>
        
        <div class="team-grid">
            <div class="team-card slide-in-left">
                <div class="photo-wrapper">
                    <div class="photo-ring"></div>
                    <div class="photo">
                        <img src="../assets/images/profiles/Dimas Abdus.png" alt="Dimas Abdus Syukur">
                    </div>
                </div>
                <div class="member-info">
                    <h3>Dimas Abdus Syukur</h3>
                    <span class="role">Team Member</span>
                    <div class="social-links">
                        <a href="#" class="social-link"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2 2 2 0 0 0-2 2v7h-4v-7a6 6 0 0 1 6-6z"/><rect x="2" y="9" width="4" height="12"/><circle cx="4" cy="4" r="2"/></svg></a>
                        <a href="#" class="social-link"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 19c-5 1.5-5-2.5-7-3m14 6v-3.87a3.37 3.37 0 0 0-.94-2.61c3.14-.35 6.44-1.54 6.44-7A5.44 5.44 0 0 0 20 4.77 5.07 5.07 0 0 0 19.91 1S18.73.65 16 2.48a13.38 13.38 0 0 0-7 0C6.27.65 5.09 1 5.09 1A5.07 5.07 0 0 0 5 4.77a5.44 5.44 0 0 0-1.5 3.78c0 5.42 3.3 6.61 6.44 7A3.37 3.37 0 0 0 9 18.13V22"/></svg></a>
                        <a href="#" class="social-link"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/></svg></a>
                    </div>
                </div>
            </div>
            
            <div class="team-card slide-in-up">
                <div class="photo-wrapper">
                    <div class="photo-ring"></div>
                    <div class="photo">
                        <img src="../assets/images/profiles/Nabil Akbar.png" alt="Nabil Akbar">
                    </div>
                </div>
                <div class="member-info">
                    <h3>Nabil Akbar</h3>
                    <span class="role">Team Member</span>
                    <div class="social-links">
                        <a href="#" class="social-link"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2 2 2 0 0 0-2 2v7h-4v-7a6 6 0 0 1 6-6z"/><rect x="2" y="9" width="4" height="12"/><circle cx="4" cy="4" r="2"/></svg></a>
                        <a href="#" class="social-link"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 19c-5 1.5-5-2.5-7-3m14 6v-3.87a3.37 3.37 0 0 0-.94-2.61c3.14-.35 6.44-1.54 6.44-7A5.44 5.44 0 0 0 20 4.77 5.07 5.07 0 0 0 19.91 1S18.73.65 16 2.48a13.38 13.38 0 0 0-7 0C6.27.65 5.09 1 5.09 1A5.07 5.07 0 0 0 5 4.77a5.44 5.44 0 0 0-1.5 3.78c0 5.42 3.3 6.61 6.44 7A3.37 3.37 0 0 0 9 18.13V22"/></svg></a>
                        <a href="#" class="social-link"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/></svg></a>
                    </div>
                </div>
            </div>
            
            <div class="team-card slide-in-up">
                <div class="photo-wrapper">
                    <div class="photo-ring"></div>
                    <div class="photo">
                        <img src="../assets/images/profiles/M Fathur.png" alt="Muhammad Fathur">
                    </div>
                </div>
                <div class="member-info">
                    <h3>Muhammad Fathur</h3>
                    <span class="role">Team Member</span>
                    <div class="social-links">
                        <a href="#" class="social-link"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2 2 2 0 0 0-2 2v7h-4v-7a6 6 0 0 1 6-6z"/><rect x="2" y="9" width="4" height="12"/><circle cx="4" cy="4" r="2"/></svg></a>
                        <a href="#" class="social-link"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 19c-5 1.5-5-2.5-7-3m14 6v-3.87a3.37 3.37 0 0 0-.94-2.61c3.14-.35 6.44-1.54 6.44-7A5.44 5.44 0 0 0 20 4.77 5.07 5.07 0 0 0 19.91 1S18.73.65 16 2.48a13.38 13.38 0 0 0-7 0C6.27.65 5.09 1 5.09 1A5.07 5.07 0 0 0 5 4.77a5.44 5.44 0 0 0-1.5 3.78c0 5.42 3.3 6.61 6.44 7A3.37 3.37 0 0 0 9 18.13V22"/></svg></a>
                        <a href="#" class="social-link"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/></svg></a>
                    </div>
                </div>
            </div>
            
            <div class="team-card slide-in-right">
                <div class="photo-wrapper">
                    <div class="photo-ring"></div>
                    <div class="photo">
                        <img src="../assets/images/profiles/M Fahreza.png" alt="Muhammad Fahreza">
                    </div>
                </div>
                <div class="member-info">
                    <h3>Muhammad Fahreza</h3>
                    <span class="role">Team Member</span>
                    <div class="social-links">
                        <a href="#" class="social-link"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2 2 2 0 0 0-2 2v7h-4v-7a6 6 0 0 1 6-6z"/><rect x="2" y="9" width="4" height="12"/><circle cx="4" cy="4" r="2"/></svg></a>
                        <a href="#" class="social-link"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 19c-5 1.5-5-2.5-7-3m14 6v-3.87a3.37 3.37 0 0 0-.94-2.61c3.14-.35 6.44-1.54 6.44-7A5.44 5.44 0 0 0 20 4.77 5.07 5.07 0 0 0 19.91 1S18.73.65 16 2.48a13.38 13.38 0 0 0-7 0C6.27.65 5.09 1 5.09 1A5.07 5.07 0 0 0 5 4.77a5.44 5.44 0 0 0-1.5 3.78c0 5.42 3.3 6.61 6.44 7A3.37 3.37 0 0 0 9 18.13V22"/></svg></a>
                        <a href="#" class="social-link"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/></svg></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Scroll Progress Indicator -->
<div class="scroll-progress">
    <div class="scroll-progress-bar"></div>
</div>

<script>
// Floating Particles Effect for Hero
function createParticles() {
    const container = document.getElementById('particles');
    if (!container) return;
    
    for (let i = 0; i < 30; i++) {
        const particle = document.createElement('div');
        particle.style.cssText = `
            position: absolute;
            width: ${Math.random() * 4 + 2}px;
            height: ${Math.random() * 4 + 2}px;
            background: rgba(213, 0, 0, ${Math.random() * 0.5 + 0.2});
            border-radius: 50%;
            left: ${Math.random() * 100}%;
            top: ${Math.random() * 100}%;
            animation: float ${Math.random() * 10 + 10}s linear infinite;
            opacity: 0;
        `;
        container.appendChild(particle);
        
        setTimeout(() => {
            particle.style.opacity = '1';
        }, Math.random() * 2000);
    }
}

// Add floating animation
const style = document.createElement('style');
style.textContent = `
    @keyframes float {
        0% { transform: translateY(100vh) rotate(0deg); opacity: 0; }
        10% { opacity: 1; }
        90% { opacity: 1; }
        100% { transform: translateY(-100vh) rotate(720deg); opacity: 0; }
    }
`;
document.head.appendChild(style);
createParticles();

// Scroll Reveal Animation
const observerOptions = {
    root: null,
    rootMargin: '0px',
    threshold: 0.1
};

const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.classList.add('revealed');
        }
    });
}, observerOptions);

document.querySelectorAll('.scroll-reveal').forEach(el => {
    observer.observe(el);
});

// Slide In Animation Observer
const slideObserver = new IntersectionObserver((entries) => {
    entries.forEach((entry, index) => {
        if (entry.isIntersecting) {
            // Add stagger delay based on element position
            setTimeout(() => {
                entry.target.classList.add('revealed');
            }, index * 150);
        }
    });
}, { threshold: 0.15, rootMargin: '0px 0px -50px 0px' });

document.querySelectorAll('.slide-in-left, .slide-in-right, .slide-in-up').forEach(el => {
    slideObserver.observe(el);
});

// Scroll Color Animation - berubah warna saat di viewport tengah
const colorObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            // Add color with delay for stagger effect
            const cards = document.querySelectorAll('.scroll-color');
            cards.forEach((card, index) => {
                setTimeout(() => {
                    if (isInViewport(card)) {
                        card.classList.add('color-active');
                    }
                }, index * 100);
            });
        }
    });
}, { threshold: 0.3 });

// Check if element is in viewport center
function isInViewport(el) {
    const rect = el.getBoundingClientRect();
    const windowHeight = window.innerHeight;
    return rect.top < windowHeight * 0.7 && rect.bottom > windowHeight * 0.3;
}

// Scroll-based color toggle
let scrollColorElements = document.querySelectorAll('.scroll-color');
window.addEventListener('scroll', () => {
    scrollColorElements.forEach(el => {
        if (isInViewport(el)) {
            el.classList.add('color-active');
        } else {
            el.classList.remove('color-active');
        }
    });
});

// Scroll Progress Bar
window.addEventListener('scroll', () => {
    const scrollTop = window.scrollY;
    const docHeight = document.documentElement.scrollHeight - window.innerHeight;
    const scrollPercent = (scrollTop / docHeight) * 100;
    document.querySelector('.scroll-progress-bar').style.height = scrollPercent + '%';
});

// Parallax effect on hero
window.addEventListener('scroll', () => {
    const scrolled = window.pageYOffset;
    const hero = document.querySelector('.hero-bg');
    if (hero && scrolled < window.innerHeight) {
        hero.style.transform = 'translateY(' + (scrolled * 0.4) + 'px)';
    }
});

// Mouse move effect on team cards
document.querySelectorAll('.team-card').forEach(card => {
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
const siteHeader = document.querySelector('header');
if (siteHeader) {
    window.addEventListener('scroll', () => {
        if (window.scrollY > 50) {
            siteHeader.classList.add('scrolled');
        } else {
            siteHeader.classList.remove('scrolled');
        }
    });
}
</script>

<?php include '../php/includes/footer.php'; ?>