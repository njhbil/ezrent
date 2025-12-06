<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: ../login.php");
    exit();
}

$booking_id = isset($_GET['booking_id']) ? (int)$_GET['booking_id'] : (isset($_GET['id']) ? (int)$_GET['id'] : 0);

if ($booking_id <= 0) {
    header("Location: my-bookings.php");
    exit();
}

require_once '../../php/config/database.php';

$booking = null;
$error = null;
$success = null;
$existingReview = null;

try {
    $stmt = $pdo->prepare("
        SELECT 
            b.*,
            v.nama as vehicle_name,
            v.merek as vehicle_brand,
            v.model as vehicle_model,
            v.images as vehicle_image
        FROM bookings b
        JOIN vehicles v ON b.vehicle_id = v.id
        WHERE b.id = ? AND b.user_id = ? AND b.status = 'completed'
    ");
    $stmt->execute([$booking_id, $_SESSION['user_id']]);
    $booking = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$booking) {
        $error = "Pesanan tidak ditemukan atau belum selesai.";
    } else {
        // Check for existing review
        $stmt = $pdo->prepare("SELECT * FROM reviews WHERE booking_id = ? AND user_id = ?");
        $stmt->execute([$booking_id, $_SESSION['user_id']]);
        $existingReview = $stmt->fetch(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    // Reviews table might not exist
    $existingReview = null;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $booking && !$existingReview) {
    $rating = isset($_POST['rating']) ? (int)$_POST['rating'] : 0;
    $review = trim($_POST['review'] ?? '');
    
    if ($rating < 1 || $rating > 5) {
        $error = "Pilih rating 1-5 bintang.";
    } elseif (strlen($review) < 10) {
        $error = "Ulasan minimal 10 karakter.";
    } else {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO reviews (booking_id, vehicle_id, user_id, rating, review, created_at)
                VALUES (?, ?, ?, ?, ?, NOW())
            ");
            $stmt->execute([$booking_id, $booking['vehicle_id'], $_SESSION['user_id'], $rating, $review]);
            
            $success = "Terima kasih! Ulasan Anda telah terkirim.";
            $existingReview = ['rating' => $rating, 'review' => $review];
        } catch (PDOException $e) {
            $error = "Gagal mengirim ulasan. Silakan coba lagi.";
        }
    }
}

$page_title = "Beri Ulasan - EzRent";
include 'header.php';
?>

<style>
/* Override body from header.php */
body {
    background: linear-gradient(180deg, #0a0a0a 0%, #050505 100%) !important;
    color: #fff !important;
}

:root {
    --primary: #c41e3a;
    --primary-dark: #a01830;
    --gold: #d4af37;
    --dark: #0a0a0a;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(30px); }
    to { opacity: 1; transform: translateY(0); }
}

@keyframes shimmer {
    0% { background-position: -200% 0; }
    100% { background-position: 200% 0; }
}

@keyframes pulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.15); }
}

@keyframes float {
    0%, 100% { transform: translateY(0) rotate(0deg); }
    50% { transform: translateY(-20px) rotate(5deg); }
}

@keyframes starPop {
    0% { transform: scale(1); }
    50% { transform: scale(1.3); }
    100% { transform: scale(1); }
}

.animate-in {
    animation: fadeIn 0.8s ease-out forwards;
    opacity: 0;
}

.delay-1 { animation-delay: 0.1s; }
.delay-2 { animation-delay: 0.2s; }
.delay-3 { animation-delay: 0.3s; }

/* Hero */
.review-hero {
    background: var(--dark);
    min-height: 220px;
    padding-top: 80px;
    position: relative;
    overflow: hidden;
    display: flex;
    align-items: center;
}

.review-hero::before {
    content: '';
    position: absolute;
    inset: 0;
    background: 
        radial-gradient(ellipse at 20% 50%, rgba(212, 175, 55, 0.1) 0%, transparent 50%),
        radial-gradient(ellipse at 80% 50%, rgba(196, 30, 58, 0.1) 0%, transparent 50%);
}

.review-hero::after {
    content: '';
    position: absolute;
    inset: 0;
    background-image: url("data:image/svg+xml,%3Csvg width='80' height='80' viewBox='0 0 80 80' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.012'%3E%3Cpath d='M50 50c0-5.523 4.477-10 10-10s10 4.477 10 10-4.477 10-10 10c0 5.523-4.477 10-10 10s-10-4.477-10-10 4.477-10 10-10zM10 10c0-5.523 4.477-10 10-10s10 4.477 10 10-4.477 10-10 10c0 5.523-4.477 10-10 10S0 25.523 0 20s4.477-10 10-10zm10 8c4.418 0 8-3.582 8-8s-3.582-8-8-8-8 3.582-8 8 3.582 8 8 8zm40 40c4.418 0 8-3.582 8-8s-3.582-8-8-8-8 3.582-8 8 3.582 8 8 8z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
}

.floating-el {
    position: absolute;
    opacity: 0.03;
    pointer-events: none;
}

.float-1 {
    top: 15%;
    right: 10%;
    width: 180px;
    height: 180px;
    border: 1px solid var(--gold);
    border-radius: 50%;
    animation: float 15s ease-in-out infinite;
}

.float-2 {
    bottom: -40px;
    left: 5%;
    width: 120px;
    height: 120px;
    border: 1px solid #fff;
    transform: rotate(45deg);
    animation: float 18s ease-in-out infinite reverse;
}

.hero-container {
    max-width: 900px;
    margin: 0 auto;
    padding: 2.5rem 20px;
    position: relative;
    z-index: 2;
}

.breadcrumb {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 1.5rem;
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.15em;
}

.breadcrumb a {
    color: rgba(255,255,255,0.4);
    text-decoration: none;
    transition: color 0.3s;
}

.breadcrumb a:hover { color: var(--primary); }
.breadcrumb .sep { color: rgba(255,255,255,0.2); }
.breadcrumb .current { color: rgba(255,255,255,0.8); }

.hero-title {
    font-size: 2.5rem;
    font-weight: 200;
    letter-spacing: -0.02em;
}

.hero-title strong { font-weight: 700; }

/* Content */
.review-content {
    background: linear-gradient(180deg, #0f0f0f 0%, #080808 100%);
    min-height: 60vh;
    padding: 4rem 20px;
    position: relative;
}

.review-content::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 1px;
    background: linear-gradient(90deg, transparent, var(--gold), transparent);
}

.content-container {
    max-width: 700px;
    margin: 0 auto;
}

/* Card */
.card {
    background: linear-gradient(145deg, rgba(20,20,20,0.9) 0%, rgba(10,10,10,0.95) 100%);
    border: 1px solid rgba(255,255,255,0.06);
    margin-bottom: 2rem;
    position: relative;
    overflow: hidden;
}

.card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 1px;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.1), transparent);
}

.card-header {
    padding: 1.25rem 1.75rem;
    border-bottom: 1px solid rgba(255,255,255,0.06);
}

.card-title {
    font-size: 0.65rem;
    font-weight: 600;
    letter-spacing: 0.2em;
    text-transform: uppercase;
    color: rgba(255,255,255,0.5);
}

.card-body { padding: 2rem; }

/* Vehicle Info */
.vehicle-card {
    display: flex;
    gap: 1.5rem;
    align-items: center;
    padding: 1.5rem;
    background: rgba(0,0,0,0.3);
    margin-bottom: 2rem;
}

@media (max-width: 500px) {
    .vehicle-card { flex-direction: column; text-align: center; }
}

.vehicle-thumb {
    width: 100px;
    height: 70px;
    background: linear-gradient(135deg, #1a1a1a 0%, #0a0a0a 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    opacity: 0.3;
    flex-shrink: 0;
    border: 1px solid rgba(255,255,255,0.05);
}

.vehicle-info h3 {
    font-size: 1.1rem;
    font-weight: 500;
    margin-bottom: 0.25rem;
}

.vehicle-info p {
    font-size: 0.85rem;
    color: rgba(255,255,255,0.4);
}

.booking-ref {
    margin-top: 0.5rem;
    padding: 0.35rem 0.75rem;
    background: rgba(255,255,255,0.05);
    display: inline-block;
    font-size: 0.7rem;
    font-weight: 600;
    letter-spacing: 0.1em;
    color: rgba(255,255,255,0.6);
}

/* Star Rating */
.rating-section {
    text-align: center;
    margin-bottom: 2.5rem;
}

.rating-label {
    font-size: 0.7rem;
    letter-spacing: 0.2em;
    text-transform: uppercase;
    color: rgba(255,255,255,0.4);
    margin-bottom: 1.25rem;
}

.star-rating {
    display: flex;
    justify-content: center;
    gap: 0.75rem;
}

.star-rating input {
    display: none;
}

.star-rating label {
    font-size: 2.5rem;
    color: rgba(255,255,255,0.15);
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
}

.star-rating label::before {
    content: '★';
}

.star-rating label:hover,
.star-rating input:checked ~ label {
    color: var(--gold);
    text-shadow: 0 0 20px rgba(212, 175, 55, 0.5);
}

.star-rating label:hover {
    animation: starPop 0.3s ease-out;
}

/* Reverse order for CSS sibling selector trick */
.star-rating {
    flex-direction: row-reverse;
}

.star-rating input:checked + label,
.star-rating input:checked + label ~ label {
    color: var(--gold);
    text-shadow: 0 0 20px rgba(212, 175, 55, 0.5);
}

.star-rating label:hover ~ label {
    color: var(--gold);
    text-shadow: 0 0 20px rgba(212, 175, 55, 0.5);
}

.rating-text {
    margin-top: 1rem;
    font-size: 0.9rem;
    color: rgba(255,255,255,0.5);
    min-height: 24px;
}

/* Textarea */
.review-section { margin-bottom: 2rem; }

.review-label {
    display: block;
    font-size: 0.7rem;
    letter-spacing: 0.15em;
    text-transform: uppercase;
    color: rgba(255,255,255,0.4);
    margin-bottom: 0.75rem;
}

.review-textarea {
    width: 100%;
    min-height: 180px;
    padding: 1.25rem;
    background: rgba(0,0,0,0.4);
    border: 1px solid rgba(255,255,255,0.08);
    color: #fff;
    font-family: inherit;
    font-size: 0.95rem;
    line-height: 1.7;
    resize: vertical;
    transition: all 0.3s;
}

.review-textarea::placeholder {
    color: rgba(255,255,255,0.25);
}

.review-textarea:focus {
    outline: none;
    border-color: var(--gold);
    box-shadow: 0 0 20px rgba(212, 175, 55, 0.1);
}

.char-count {
    text-align: right;
    font-size: 0.75rem;
    color: rgba(255,255,255,0.3);
    margin-top: 0.5rem;
}

/* Submit Button */
.submit-btn {
    width: 100%;
    padding: 1.25rem;
    font-size: 0.75rem;
    font-weight: 600;
    letter-spacing: 0.2em;
    text-transform: uppercase;
    border: none;
    cursor: pointer;
    background: linear-gradient(135deg, var(--gold) 0%, #b8962e 100%);
    color: #000;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
}

.submit-btn::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
    transition: left 0.6s;
}

.submit-btn:hover::before { left: 100%; }

.submit-btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 15px 40px rgba(212, 175, 55, 0.35);
}

.submit-btn:disabled {
    background: rgba(255,255,255,0.1);
    color: rgba(255,255,255,0.3);
    cursor: not-allowed;
    transform: none;
    box-shadow: none;
}

.cancel-link {
    display: block;
    text-align: center;
    margin-top: 1.25rem;
    color: rgba(255,255,255,0.4);
    text-decoration: none;
    font-size: 0.8rem;
    transition: color 0.3s;
}

.cancel-link:hover { color: var(--primary); }

/* Success State */
.success-container {
    text-align: center;
    padding: 3rem 2rem;
}

.success-icon {
    width: 100px;
    height: 100px;
    margin: 0 auto 2rem;
    background: linear-gradient(135deg, rgba(212, 175, 55, 0.2) 0%, rgba(212, 175, 55, 0.05) 100%);
    border: 2px solid var(--gold);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2.5rem;
    color: var(--gold);
    animation: pulse 2s ease-in-out infinite;
}

.success-title {
    font-size: 1.75rem;
    font-weight: 300;
    margin-bottom: 0.75rem;
}

.success-title strong { font-weight: 600; color: var(--gold); }

.success-text {
    color: rgba(255,255,255,0.5);
    margin-bottom: 2rem;
    font-size: 0.95rem;
}

.success-rating {
    display: flex;
    justify-content: center;
    gap: 0.5rem;
    margin-bottom: 2rem;
    font-size: 1.5rem;
}

.success-rating .star { color: var(--gold); }
.success-rating .star.empty { color: rgba(255,255,255,0.15); }

.success-review {
    background: rgba(0,0,0,0.3);
    padding: 1.5rem;
    text-align: left;
    margin-bottom: 2rem;
    border-left: 3px solid var(--gold);
}

.success-review p {
    color: rgba(255,255,255,0.7);
    font-size: 0.9rem;
    line-height: 1.8;
    font-style: italic;
}

.back-btn {
    display: inline-block;
    padding: 1rem 2.5rem;
    background: transparent;
    border: 1px solid rgba(255,255,255,0.2);
    color: #fff;
    text-decoration: none;
    font-size: 0.75rem;
    font-weight: 600;
    letter-spacing: 0.15em;
    text-transform: uppercase;
    transition: all 0.3s;
}

.back-btn:hover {
    background: rgba(255,255,255,0.05);
    border-color: rgba(255,255,255,0.4);
}

/* Error State */
.error-container {
    text-align: center;
    padding: 5rem 2rem;
}

.error-icon {
    width: 80px;
    height: 80px;
    border: 2px solid rgba(255,255,255,0.1);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 2rem;
    font-size: 2rem;
    color: rgba(255,255,255,0.3);
}

.error-title {
    font-size: 1.5rem;
    font-weight: 300;
    margin-bottom: 0.75rem;
}

.error-text {
    color: rgba(255,255,255,0.5);
    margin-bottom: 2rem;
}

/* Alert */
.alert {
    padding: 1rem 1.5rem;
    margin-bottom: 2rem;
    font-size: 0.85rem;
}

.alert-error {
    background: rgba(255, 77, 77, 0.1);
    border: 1px solid rgba(255, 77, 77, 0.3);
    color: #ff6b6b;
}
</style>

<?php if ($error && !$booking): ?>
<section class="review-content">
    <div class="content-container">
        <div class="error-container animate-in">
            <div class="error-icon">!</div>
            <h2 class="error-title">Tidak Dapat Memberikan Ulasan</h2>
            <p class="error-text"><?php echo htmlspecialchars($error); ?></p>
            <a href="my-bookings.php" class="back-btn">Kembali ke Pesanan</a>
        </div>
    </div>
</section>
<?php elseif ($success || $existingReview): ?>

<section class="review-hero">
    <div class="floating-el float-1"></div>
    <div class="floating-el float-2"></div>
    
    <div class="hero-container">
        <nav class="breadcrumb animate-in">
            <a href="my-bookings.php">Pesanan</a>
            <span class="sep">/</span>
            <span class="current">Ulasan Terkirim</span>
        </nav>
        <h1 class="hero-title animate-in delay-1">Terima <strong>Kasih</strong></h1>
    </div>
</section>

<section class="review-content">
    <div class="content-container">
        <div class="card animate-in delay-2">
            <div class="card-body">
                <div class="success-container">
                    <div class="success-icon">★</div>
                    <h2 class="success-title">Ulasan <strong>Terkirim</strong></h2>
                    <p class="success-text">Ulasan Anda membantu pengguna lain memilih kendaraan terbaik.</p>
                    
                    <div class="success-rating">
                        <?php 
                        $rating = $existingReview['rating'] ?? 5;
                        for ($i = 1; $i <= 5; $i++): 
                        ?>
                        <span class="star <?php echo $i <= $rating ? '' : 'empty'; ?>">★</span>
                        <?php endfor; ?>
                    </div>
                    
                    <?php if (!empty($existingReview['review'])): ?>
                    <div class="success-review">
                        <p>"<?php echo htmlspecialchars($existingReview['review']); ?>"</p>
                    </div>
                    <?php endif; ?>
                    
                    <a href="my-bookings.php" class="back-btn">Kembali ke Pesanan</a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php else: ?>

<section class="review-hero">
    <div class="floating-el float-1"></div>
    <div class="floating-el float-2"></div>
    
    <div class="hero-container">
        <nav class="breadcrumb animate-in">
            <a href="my-bookings.php">Pesanan</a>
            <span class="sep">/</span>
            <a href="booking-detail.php?id=<?php echo $booking_id; ?>">Detail</a>
            <span class="sep">/</span>
            <span class="current">Ulasan</span>
        </nav>
        <h1 class="hero-title animate-in delay-1">Beri <strong>Ulasan</strong></h1>
    </div>
</section>

<section class="review-content">
    <div class="content-container">
        <?php if ($error): ?>
        <div class="alert alert-error animate-in"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <form method="POST" id="reviewForm">
            <div class="card animate-in delay-1">
                <div class="card-body">
                    <!-- Vehicle Info -->
                    <div class="vehicle-card">
                        <div class="vehicle-thumb">▣</div>
                        <div class="vehicle-info">
                            <h3><?php echo htmlspecialchars($booking['vehicle_name']); ?></h3>
                            <p><?php echo htmlspecialchars($booking['vehicle_brand'] . ' ' . $booking['vehicle_model']); ?></p>
                            <span class="booking-ref">#EZR-<?php echo str_pad($booking['id'], 6, '0', STR_PAD_LEFT); ?></span>
                        </div>
                    </div>
                    
                    <!-- Star Rating -->
                    <div class="rating-section">
                        <div class="rating-label">Bagaimana pengalaman Anda?</div>
                        <div class="star-rating">
                            <input type="radio" name="rating" value="5" id="star5">
                            <label for="star5"></label>
                            <input type="radio" name="rating" value="4" id="star4">
                            <label for="star4"></label>
                            <input type="radio" name="rating" value="3" id="star3">
                            <label for="star3"></label>
                            <input type="radio" name="rating" value="2" id="star2">
                            <label for="star2"></label>
                            <input type="radio" name="rating" value="1" id="star1">
                            <label for="star1"></label>
                        </div>
                        <div class="rating-text" id="ratingText"></div>
                    </div>
                    
                    <!-- Review Text -->
                    <div class="review-section">
                        <label class="review-label">Ceritakan pengalaman Anda</label>
                        <textarea 
                            name="review" 
                            class="review-textarea" 
                            id="reviewText"
                            placeholder="Bagikan pengalaman Anda menggunakan kendaraan ini. Ceritakan tentang kondisi kendaraan, kenyamanan, dan layanan yang Anda terima..."
                            maxlength="1000"
                        ></textarea>
                        <div class="char-count"><span id="charCount">0</span>/1000 karakter</div>
                    </div>
                    
                    <button type="submit" class="submit-btn" id="submitBtn" disabled>
                        Kirim Ulasan
                    </button>
                    
                    <a href="booking-detail.php?id=<?php echo $booking_id; ?>" class="cancel-link">Lewati untuk sekarang</a>
                </div>
            </div>
        </form>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const radios = document.querySelectorAll('input[name="rating"]');
    const submitBtn = document.getElementById('submitBtn');
    const reviewText = document.getElementById('reviewText');
    const charCount = document.getElementById('charCount');
    const ratingText = document.getElementById('ratingText');
    
    const ratingLabels = {
        1: 'Sangat Buruk',
        2: 'Buruk',
        3: 'Cukup',
        4: 'Baik',
        5: 'Sangat Baik'
    };
    
    function checkForm() {
        const ratingSelected = document.querySelector('input[name="rating"]:checked');
        const hasReview = reviewText.value.trim().length >= 10;
        submitBtn.disabled = !(ratingSelected && hasReview);
    }
    
    radios.forEach(radio => {
        radio.addEventListener('change', function() {
            ratingText.textContent = ratingLabels[this.value];
            checkForm();
        });
    });
    
    reviewText.addEventListener('input', function() {
        charCount.textContent = this.value.length;
        checkForm();
    });
    
    // Animation
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.animationPlayState = 'running';
            }
        });
    }, { threshold: 0.1 });
    
    document.querySelectorAll('.animate-in').forEach(el => {
        el.style.animationPlayState = 'paused';
        observer.observe(el);
    });
});
</script>

<?php endif; ?>

<?php include '../../php/includes/footer.php'; ?>
