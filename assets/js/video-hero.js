/* 
 * EzRent - Video Hero Optimization
 * Additional JavaScript untuk enhance video hero experience
 */

document.addEventListener('DOMContentLoaded', function() {
    const video = document.querySelector('.hero-video');
    
    if (video) {
        // Pause video when not in viewport (performance optimization)
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    video.play();
                } else {
                    video.pause();
                }
            });
        }, { threshold: 0.25 });
        
        observer.observe(video);
        
        // Handle video load error
        video.addEventListener('error', function() {
            console.log('Video failed to load');
            const videoContainer = document.querySelector('.video-container');
            if (videoContainer) {
                // Fallback to gradient background
                videoContainer.style.background = 'linear-gradient(135deg, #000000 0%, #1a1a1a 100%)';
            }
        });
        
        // Ensure video plays on mobile
        video.play().catch(function(error) {
            console.log('Autoplay prevented:', error);
            // Video will play when user interacts with page
        });
    }
    
    // Smooth scroll for scroll indicator
    const scrollIndicator = document.querySelector('.scroll-indicator');
    if (scrollIndicator) {
        scrollIndicator.addEventListener('click', function() {
            const featuresSection = document.querySelector('.features-section');
            if (featuresSection) {
                featuresSection.scrollIntoView({ behavior: 'smooth' });
            }
        });
    }
    
    // Add parallax effect to hero content (optional enhancement)
    window.addEventListener('scroll', function() {
        const scrolled = window.pageYOffset;
        const heroContent = document.querySelector('.hero-content');
        
        if (heroContent && scrolled < window.innerHeight) {
            heroContent.style.transform = `translateY(${scrolled * 0.5}px)`;
            heroContent.style.opacity = 1 - (scrolled / window.innerHeight);
        }
    });
});
