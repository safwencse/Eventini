document.addEventListener('DOMContentLoaded', function() {
    // Gestion du bouton "J'aime"
    const likeBtn = document.querySelector('.like-btn');
    const likeIcon = document.querySelector('.likes i');
    const likeCount = document.querySelector('.like-count');
    
    if (likeBtn) {
        likeBtn.addEventListener('click', function() {
            const isLiked = likeIcon.classList.contains('fas');
            const currentLikes = parseInt(likeCount.textContent);
            
            likeIcon.style.transform = 'scale(1.2)';
            setTimeout(() => {
                likeIcon.style.transform = 'scale(1)';
            }, 300);
            
            likeIcon.classList.toggle('far');
            likeIcon.classList.toggle('fas');
            
            if (isLiked) {
                likeCount.textContent = currentLikes - 1;
                likeCount.style.color = '#dc3545';
            } else {
                likeCount.textContent = currentLikes + 1;
                likeCount.style.color = '#28a745';
            }
            
            setTimeout(() => {
                likeCount.style.color = '';
            }, 1000);
        });
    }
    
    // Gestion des tickets
    const ticketCards = document.querySelectorAll('.ticket-card');
    
    ticketCards.forEach(card => {
        card.addEventListener('click', function() {
            this.style.transform = 'scale(0.98)';
            setTimeout(() => {
                this.style.transform = '';
            }, 200);
        });
        
        const ticketType = card.classList[1];
        const priceElement = card.querySelector('.ticket-price');
        
        if (ticketType === 'vip' && priceElement) {
            priceElement.style.color = '#ff7207';
        } else if (ticketType === 'backstage' && priceElement) {
            priceElement.style.color = '#7fbc1e';
        }
    });
    
    // Gestion du bouton d'inscription
    const registerBtn = document.querySelector('.register-btn');
    if (registerBtn) {
        registerBtn.addEventListener('click', function() {
            registerBtn.style.transform = 'scale(0.95)';
            setTimeout(() => {
                registerBtn.style.transform = '';
            }, 200);
            
            setTimeout(() => {
                alert('Merci pour votre intérêt !\n\nVous serez redirigé vers la page de réservation.');
            }, 300);
        });
    }
    
    // Animation d'entrée
    const eventCard = document.querySelector('.event-card');
    if (eventCard) {
        setTimeout(() => {
            eventCard.style.opacity = '1';
            eventCard.style.transform = 'translateY(0)';
        }, 100);
    }

    // Gestion du popup mot de passe
    const passwordBtn = document.getElementById('password-btn');
    if (passwordBtn) {
        passwordBtn.addEventListener('click', function() {
            const popup = document.getElementById('password-popup');
            popup.style.display = 'block';
            
            document.addEventListener('click', function closePopup(e) {
                if (!popup.contains(e.target) && e.target !== passwordBtn) {
                    popup.style.display = 'none';
                    document.removeEventListener('click', closePopup);
                }
            });
        });

        // Gestion de la soumission du formulaire
        const passwordForm = document.getElementById('password-form');
        if (passwordForm) {
            passwordForm.addEventListener('submit', handlePasswordSubmit);
        }
    }
});



function showPasswordSuccess(form) {
    const popup = document.getElementById('password-popup');
    const passwordBtn = document.getElementById('password-btn');
    
    // Afficher message de succès
    const successMessage = document.createElement('div');
    successMessage.className = 'password-success';
    successMessage.innerHTML = 'Mot de passe validé <i class="fas fa-check-circle"></i>';
    
    // Remplacer le formulaire par le message
    form.style.display = 'none';
    popup.appendChild(successMessage);
    
    // Fermer le popup après 1.5 secondes
    setTimeout(() => {
        popup.style.display = 'none';
        createReservationButton();
    }, 1500);
}

function showPasswordError(errorElement, message) {
    errorElement.textContent = message;
    errorElement.style.display = 'block';
}

function createReservationButton() {
    const passwordBtn = document.getElementById('password-btn');
    const actionsContainer = document.querySelector('.event-actions');
    
    passwordBtn.style.display = 'none';
    const registerBtn = document.createElement('button');
    registerBtn.className = 'btn btn-primary register-btn';
    registerBtn.textContent = 'Réserver';
    actionsContainer.appendChild(registerBtn);
    
    registerBtn.addEventListener('click', function() {
        this.style.transform = 'scale(0.95)';
        setTimeout(() => {
            this.style.transform = '';
        }, 200);
        
        setTimeout(() => {
            alert('Merci pour votre intérêt !\n\nVous serez redirigé vers la page de réservation.');
        }, 300);
    });
}



