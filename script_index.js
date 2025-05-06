// =============================================
// Curseur Personnalisé - Version Petit Point Transparent
// =============================================
const cursor = document.querySelector('.cursor');
const sparklesContainer = document.querySelector('.sparkles');
let lastSparkleTime = 0;
const sparkleDelay = 100; // Délai minimum entre les étincelles en ms

// Configuration du curseur
cursor.style.width = '4px'; // Taille réduite
cursor.style.height = '4px';
cursor.style.backgroundColor = 'rgba(255, 255, 255, 0.3)'; // Transparence augmentée
cursor.style.mixBlendMode = 'difference'; // Maintien de l'effet de contraste

// Optimisation du mouvement du curseur
document.addEventListener('mousemove', (e) => {
    const { clientX, clientY } = e;
    const now = Date.now();
    
    // Positionner le curseur avec transform pour meilleure performance
    cursor.style.transform = `translate(${clientX}px, ${clientY}px)`;
    
    // Limiter la création d'étincelles pour la performance
    if (now - lastSparkleTime > sparkleDelay) {
        createSparkle(clientX, clientY);
        lastSparkleTime = now;
    }
});

function createSparkle(x, y) {
    const sparkleCount = Math.floor(Math.random() * 2) + 1; // 1-2 étincelles
    
    for (let i = 0; i < sparkleCount; i++) {
        const sparkle = document.createElement('div');
        sparkle.className = 'sparkle';
        
        // Configuration aléatoire
        const angle = Math.random() * Math.PI * 2;
        const distance = Math.random() * 15 + 5;
        const offsetX = Math.cos(angle) * distance;
        const offsetY = Math.sin(angle) * distance;
        const size = Math.random() * 3 + 3;
        const opacity = Math.random() * 0.5 + 0.5;
        
        sparkle.style.cssText = `
            left: ${x + offsetX}px;
            top: ${y + offsetY}px;
            width: ${size}px;
            height: ${size}px;
            opacity: ${opacity};
            animation-duration: ${Math.random() * 300 + 300}ms;
        `;
        
        sparklesContainer.appendChild(sparkle);
        
        // Nettoyage automatique après l'animation
        sparkle.addEventListener('animationend', () => {
            sparkle.remove();
        });
    }
}

// =============================================
// Gestion des Témoignages
// =============================================
document.addEventListener('DOMContentLoaded', function() {
    const testimonials = document.querySelectorAll('.testimonial-card');
    if (testimonials.length === 0) return;
    
    let currentIndex = 0;
    const intervalDuration = 4000;
    let intervalId;
    
    function showTestimonial(index) {
        testimonials.forEach((testimonial, i) => {
            testimonial.classList.toggle('active', i === index);
        });
    }
    
    function startSlider() {
        showTestimonial(currentIndex);
        intervalId = setInterval(() => {
            currentIndex = (currentIndex + 1) % testimonials.length;
            showTestimonial(currentIndex);
        }, intervalDuration);
    }
    
    function pauseSlider() {
        clearInterval(intervalId);
    }
    
    // Démarrer le slider
    startSlider();
    
    // Pause au survol pour une meilleure UX
    const sliderContainer = document.querySelector('.testimonials-container');
    if (sliderContainer) {
        sliderContainer.addEventListener('mouseenter', pauseSlider);
        sliderContainer.addEventListener('mouseleave', startSlider);
    }
    
    // Initialisation de AOS
    if (typeof AOS !== 'undefined') {
        AOS.init({
            duration: 800,
            easing: 'ease-in-out',
            once: true,
            offset: 120
        });
    }
});

// =============================================
// Fonction utilitaire pour sélection sécurisée
// =============================================
function safeQuerySelector(selector) {
    const element = document.querySelector(selector);
    if (!element) console.warn(`Élément non trouvé: ${selector}`);
    return element;
}