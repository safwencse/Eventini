document.addEventListener('DOMContentLoaded', function() {
   
   
   
    // Mobile menu toggle
    const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
    const navbarLinks = document.querySelector('.navbar-links');
    
    if (mobileMenuBtn && navbarLinks) {
        mobileMenuBtn.addEventListener('click', function() {
            navbarLinks.style.display = navbarLinks.style.display === 'block' ? 'none' : 'block';
        });
    }
    
    // Star rating interaction
    const starInputs = document.querySelectorAll('.star-rating input');
    starInputs.forEach(input => {
        input.addEventListener('change', function() {
            const rating = this.value;
            console.log(`Selected rating: ${rating} stars`);
            // You can add your filter logic here
        });
    });
    
    // Filter button click
    const filterBtn = document.querySelector('.filter-btn');
    if (filterBtn) {
        filterBtn.addEventListener('click', function() {
            const priceRange = document.getElementById('price-range').value;
            const distance = document.getElementById('distance').value;
            const rating = document.querySelector('.star-rating input:checked')?.value || 'all';
            const category = document.getElementById('categories').value;
            
            console.log('Filters applied:', {
                priceRange,
                distance,
                rating,
                category
            });
            
            // Here you would typically filter your events based on the selected filters
            // For demonstration, we'll just show an alert
            alert('Filters applied! (Check console for details)');
        });
    }
    
    // Reset button click
    const resetBtn = document.querySelector('.reset-btn');
    if (resetBtn) {
        resetBtn.addEventListener('click', function() {
            document.getElementById('price-range').value = 'all';
            document.getElementById('distance').value = 'all';
            document.getElementById('categories').value = 'all';
            
            // Reset star rating
            const starInputs = document.querySelectorAll('.star-rating input');
            starInputs.forEach(input => input.checked = false);
            
            console.log('Filters reset');
        });
    }
    
    // Search functionality
    const searchBtn = document.querySelector('.search-btn');
    if (searchBtn) {
        searchBtn.addEventListener('click', function() {
            const searchInput = document.querySelector('.search-bar input');
            const searchTerm = searchInput.value.trim();
            
            if (searchTerm) {
                console.log(`Searching for: ${searchTerm}`);
                // Here you would typically filter events based on the search term
                // For demonstration, we'll just show an alert
                alert(`Searching for: ${searchTerm}`);
            }
        });
    }
    
    // Event card hover effect enhancement
    const eventCards = document.querySelectorAll('.event-card');
    eventCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.querySelector('.event-btn').style.backgroundColor = '#0d5490';
        });
        
        card.addEventListener('mouseleave', function() {
            this.querySelector('.event-btn').style.backgroundColor = 'var(--primary-color)';
        });
    });
    
    // Pagination button clicks
    const pageBtns = document.querySelectorAll('.page-btn:not(.next-btn)');
    pageBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            // Remove active class from all buttons
            pageBtns.forEach(b => b.classList.remove('active'));
            
            // Add active class to clicked button
            this.classList.add('active');
            
            // Here you would typically load the corresponding page of events
            console.log(`Page ${this.textContent} clicked`);
        });
    });
    
    // Next button click
    const nextBtn = document.querySelector('.next-btn');
    if (nextBtn) {
        nextBtn.addEventListener('click', function() {
            const activePage = document.querySelector('.page-btn.active');
            const nextPage = parseInt(activePage.textContent) + 1;
            
            if (nextPage <= 5) { // Assuming we have 5 pages
                // Remove active class from current button
                activePage.classList.remove('active');
                
                // Add active class to next button
                document.querySelector(`.page-btn:nth-child(${nextPage})`).classList.add('active');
                
                console.log(`Moving to page ${nextPage}`);
            }
        });
    }
    

    
    
});
// Carousel Functionality
document.addEventListener('DOMContentLoaded', function() {
    const carousel = document.querySelector('.carousel');
    const prevBtn = document.querySelector('.prev-btn');
    const nextBtn = document.querySelector('.next-btn');
    const items = document.querySelectorAll('.carousel-item');
    
    let currentIndex = 0;
    let autoRotateInterval;
    
    // Mettre à jour les positions des éléments
    function updateCarousel() {
        const totalItems = items.length;
        
        items.forEach((item, index) => {
            item.classList.remove('left', 'center', 'right', 'hidden', 'active');
            
            if (index === currentIndex) {
                item.classList.add('center', 'active');
            } 
            else if (index === (currentIndex + 1) % totalItems) {
                item.classList.add('right');
            }
            else if (index === (currentIndex - 1 + totalItems) % totalItems) {
                item.classList.add('left');
            }
            else {
                item.classList.add('hidden');
            }
        });
    }
    
    // Aller à l'élément suivant
    function nextSlide() {
        currentIndex = (currentIndex + 1) % items.length;
        updateCarousel();
    }
    
    // Aller à l'élément précédent
    function prevSlide() {
        currentIndex = (currentIndex - 1 + items.length) % items.length;
        updateCarousel();
    }
    
    // Rotation automatique
    function startAutoRotate() {
        autoRotateInterval = setInterval(nextSlide, 5000);
    }
    
    // Arrêter la rotation automatique
    function pauseAutoRotate() {
        clearInterval(autoRotateInterval);
    }
    
    // Initialisation
    function initCarousel() {
        // On active le premier élément au chargement
        if (items.length > 0) {
            items[0].classList.add('active');
        }
        
        updateCarousel();
        startAutoRotate();
        
        // Gestion des événements
        carousel.addEventListener('mouseenter', pauseAutoRotate);
        carousel.addEventListener('mouseleave', startAutoRotate);
        
        nextBtn.addEventListener('click', function() {
            nextSlide();
            pauseAutoRotate();
            startAutoRotate();
        });
        
        prevBtn.addEventListener('click', function() {
            prevSlide();
            pauseAutoRotate();
            startAutoRotate();
        });
    }
    
    initCarousel();
});

document.addEventListener('DOMContentLoaded', function() {
    const input = document.getElementById('eventSearch');
    const placeholderText = "Rechercher des événements...";
    let index = 0;
    let isDeleting = false;
    let timer;
    
    function animatePlaceholder() {
        // Effacer le timeout précédent s'il existe
        clearTimeout(timer);
        
        // Déterminer le texte actuel à afficher
        let currentText = isDeleting 
            ? placeholderText.substring(0, index - 1)
            : placeholderText.substring(0, index + 1);
        
        // Mettre à jour le placeholder
        input.placeholder = currentText;
        
        // Mettre à jour l'index
        if (!isDeleting) {
            index++;
            
            // Si tout le texte est affiché, passer en mode suppression après 2s
            if (currentText === placeholderText) {
                isDeleting = true;
                timer = setTimeout(animatePlaceholder, 2000);
                return;
            }
        } else {
            index--;
            
            // Si tout le texte est effacé, repasser en mode écriture après un court délai
            if (currentText === "") {
                isDeleting = false;
                timer = setTimeout(animatePlaceholder, 500);
                return;
            }
        }
        
        // Vitesse d'écriture/effacement (en ms)
        const speed = isDeleting ? 100 : 200;
        timer = setTimeout(animatePlaceholder, speed);
    }
    
    // Démarrer l'animation
    animatePlaceholder();
});
