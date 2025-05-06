<?php 
require_once '../config/connect.php';
require_once '../config/traitement.php';
session_start();
if(!isset($_SESSION['email']) || empty($_SESSION['email'])){
    header("Location: signup.php?show=login");
}
$evenement_id = isset($_GET['evenement_id']) ? intval($_GET['evenement_id']) : 50;

if (isset($_SESSION["acces_autorise_".$evenement_id]) && isset($_SESSION["last_access_".$evenement_id])) {
    $expireAfter = 10;
    if (time() - $_SESSION["last_access_".$evenement_id] > $expireAfter) {
        unset($_SESSION["acces_autorise_".$evenement_id]);
        unset($_SESSION["last_access_".$evenement_id]);
    }
}

$evenement = getDetailsEvenement($cnx, $evenement_id);
$tickets = getDetailsTicket($cnx, $evenement_id);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détails de l'événement</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assests/css/style_navbar.css">
    <link rel="stylesheet" href="../assests/css/style_events_details.css">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">

</head>
<body>

<nav class="navbar" style='font-family: "Titillium Web", sans-serif;'> 
    <div class="navbar__content">
        <a href="../index.php" class="navbar__logo">EVENTINI</a>
        <ul class="navbar__menu">
            <li class="nav-item">
                <a href="#about">
                    <span> à propos</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="#services">
                    <span>Services</span>
                </a>
            </li>
                <li class="nav-item">
                <a href="#testimonials">
                    <span>Témoignages</span>
                </a>
            </li>
                <li class="nav-item">
                <a href="#contact">
                    <span>Contact</span>
                </a>
            </li>
            <li>
            <?php
            if(isset($_SESSION['user_id']) && $_SESSION['user_id'] != '') {
                echo '<div class="userzone">';
                echo ' <div class="user">
                    <img src="../config/' . (!empty($_SESSION['image_utilisateur']) ? htmlspecialchars($_SESSION['image_utilisateur']) : 'uploads/default.jpg') . '" alt="Profile picture">
                    </div>';
                echo '<div class="zone_profile">' . $_SESSION['nom'] . ' ' . $_SESSION['prenom'] . '</div>';
                
                echo '<div class="profile-dropdown">';
                echo '<a href="view/profile.php" class="dropdown-item"><i class="fas fa-user"></i> Profil</a>';
                echo '<a href="../config/logout.php" class="dropdown-item"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>';
                echo '</div>';
                
                echo '</div>';
            } else {
              echo '<div class="navbar__buttons">
              <a href="view/signup.php?show=login" class="navbar__btn navbar__btn--login" id="seconnecter">Se connecter</a>
              <a href="view/signup.php" class="navbar__btn navbar__btn--signup" id="inscriptions">Inscrivez-vous</a>
          </div>';
            }
            ?>
            </li>
        </ul>
    </div>
</nav>
<style>
    .navbar {
    all: unset;
    font-family: "Titillium Web", sans-serif;
    width: 100%;
    background-color: #ffffff;
    box-shadow: 0 2px 10px rgba(14, 13, 13, 0.1);
    position: sticky;
    top: 0;
    z-index: 1000;
    overflow: hidden; /* To contain the floating circles */
}

/* Floating circles background */
.navbar::before {
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: transparent;
    z-index: -1;
}

.navbar__circle {
    position: absolute;
    border-radius: 50%;
    opacity: 0.1;
    z-index: -1;
    animation: float 15s infinite linear;
}

/* Dark circles */
.navbar__circle--dark {
    background-color: #292929;
}

/* Orange circles */
.navbar__circle--orange {
    background-color: #ff6d00;
}

/* Circle animations */
@keyframes float {
    0% {
        transform: translateY(0) rotate(0deg);
    }
    50% {
        transform: translateY(-20px) rotate(180deg);
    }
    100% {
        transform: translateY(0) rotate(360deg);
    }
}

/* Navbar content container */
.navbar__content {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.8rem 2rem;
    background-color: rgba(255, 255, 255, 0.85); /* Slightly transparent to show circles */
    margin: 0 auto;
    max-width: 100%;
    position: relative;
}

.navbar__logo {
    font-size: 1.8rem;
    font-weight: 700;
    color: #1a73e8;
    text-decoration: none;
    transition: color 0.3s ease;
}

.navbar__logo:hover {
    color: #ff6d00;
}

/* Navigation Menu */
.navbar__menu {
    display: flex;
    list-style: none;
    gap: 1.5rem;
    margin: 0;
    padding: 0;
    align-items: center;
}

.navbar__menu a {
    text-decoration: none;
    color: #292929;
    font-weight: 600;
    transition: color 0.3s ease;
}

.navbar__menu a:hover {
    color: #1a73e8;
}

/* Buttons Container */
.navbar__buttons {
    display: flex;
    gap: 1rem;
}

/* Button Styles */
.navbar__btn {
    padding: 0.6rem 1.5rem;
    border-radius: 50px;
    font-weight: 600;
    font-size: 0.9rem;
    transition: all 0.3s ease;
    text-decoration: none;
}

.navbar__btn--login {
    background: transparent;
    color: #292929;
    border: 2px solid #dadada;
}

.navbar__btn--signup {
    background: #1a73e8;
    color: white;
    border: 2px solid #1a73e8;
}

.navbar__btn--login:hover {
    background: #ebebeb;
    border-color: #ebebeb;
}

.navbar__btn--signup:hover {
    background: #ff6d00;
    border-color: #ff6d00;
}

/* User Zone Styles */
.userzone {
    position: relative;
    display: flex;
    align-items: center;
    gap: 12px;
    background-color: #ebebeb;
    border-radius: 50px;
    padding: 0.3rem 0.8rem 0.3rem 0.3rem;
    box-shadow: 0 2px 8px rgba(14, 13, 13, 0.08);
    transition: all 0.3s ease;
    cursor: pointer;
    max-width: 220px;
}

.userzone:hover {
    background-color: #dadada;
    box-shadow: 0 4px 12px rgba(14, 13, 13, 0.15);
}

.user {
    width: 42px;
    height: 42px;
    border-radius: 50%;
    overflow: hidden;
    border: 3px solid #ffffff;
}

.user img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.zone_profile {
    font-size: 0.9rem;
    font-weight: 600;
    color: #292929;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* Profile Dropdown */
.profile-dropdown {
    position: absolute;
    top: 120%;
    right: 0;
    background: #ffffff;
    border-radius: 8px;
    box-shadow: 0 4px 15px rgba(14, 13, 13, 0.15);
    padding: 0.5rem 0;
    width: 200px;
    opacity: 0;
    visibility: hidden;
    transition: all 0.3s ease;
    z-index: 1000;
}

.userzone:hover .profile-dropdown {
    opacity: 1;
    visibility: visible;
}

.dropdown-item {
    display: flex;
    align-items: center;
    padding: 0.7rem 1.2rem;
    color: #292929;
    text-decoration: none;
    transition: all 0.2s;
    font-size: 0.9rem;
}

.dropdown-item:hover {
    background-color: #ebebeb;
    color: #1a73e8;
}

.dropdown-item i {
    margin-right: 10px;
    width: 20px;
    text-align: center;
    color: #ff6d00;
}

/* Responsive Design */
@media (max-width: 768px) {
    .navbar__content {
        flex-direction: column;
        padding: 1rem;
    }
    
    .navbar__menu {
        flex-direction: column;
        gap: 1rem;
        margin-top: 1rem;
        width: 100%;
        align-items: center;
    }
    
    .navbar__buttons {
        margin-top: 1rem;
        flex-direction: column;
        width: 100%;
        gap: 0.8rem;
    }
    
    .navbar__btn {
        width: 100%;
        text-align: center;
        padding: 0.8rem;
    }
    
    .userzone {
        margin-top: 1rem;
        width: 100%;
        max-width: 100%;
        justify-content: center;
        padding: 0.5rem;
    }
    
    .profile-dropdown {
        width: 100%;
        right: auto;
        left: 0;
    }
}
.nav-item {
    padding: 0.5rem 1.2rem;
    margin: 0 0.3rem;
    position: relative;
    color: var(--text-secondary);
    text-decoration: none;
    font-family: 'Montserrat', sans-serif;
    font-weight: 500;
    font-size: 1.1rem;
    text-transform: uppercase;
    letter-spacing: 1px;
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    
    /* Effet de surbrillance originale */
    background: 
      linear-gradient(to right, 
        transparent 0%, 
        rgba(255,255,255,0.05) 50%, 
        transparent 100%);
    background-size: 200% 100%;
    background-position: 100% 0;
    
    /* Bordure animée */
    &::before {
      content: '';
      position: absolute;
      bottom: 0;
      left: 50%;
      width: 0;
      height: 2px;
      background: linear-gradient(90deg, #FF6B6B, #4ECDC4);
      transform: translateX(-50%);
      transition: width 0.3s ease;
    }
    
    /* Point décoratif */
    &::after {
      content: '•';
      position: absolute;
      top: 50%;
      right: -10px;
      transform: translateY(-50%) scale(0);
      color: #FF6B6B;
      transition: transform 0.3s ease;
    }
    
    &:hover {
      color: white;
      background-position: 0 0;
      
      &::before {
        width: 80%;
      }
      
      &::after {
        transform: translateY(-50%) scale(1);
      }
    }
    
    /* Animation active */
    &.active {
      color: white;
      text-shadow: 0 0 8px rgba(78, 205, 196, 0.5);
      
      &::before {
        width: 100%;
        background: #4ECDC4;
      }
    }
  }

.content-nav .nav-item.active {
  color: var(--primary);
  
}

.content-nav .nav-item.active::after {
  content: "";
  position: absolute;
  bottom: -1rem;
  left: 0;
  width: 100%;
  height: 2px;
  border-radius: 2px;
}

</style>

    <main class="event-container">
        <div class="container">
        <?php if ($evenement): ?>
            <article class="event-card">
                <div class="event-meta-top">
                    <span class="event-category"><?php echo htmlspecialchars($evenement['categorie']); ?></span>
                    <span class="event-type public"><?php echo htmlspecialchars($evenement['type']); ?></span>
                </div>
                
                <h1 class="event-title animated-gradient"><?php echo htmlspecialchars($evenement['nom']); ?></h1>
                
                <div class="event-content">
                    <div class="event-media">
                    <?php
$imagePath = "../config/uploads/" . ($evenement['image_evenement'] ?? 'default_event.jpg');
$defaultImage = "../config/uploads/default_event.jpg";
$finalImage = (isset($evenement['image_evenement']) && file_exists($imagePath)) ? $imagePath : $defaultImage;
?>

<img src="<?php echo htmlspecialchars($finalImage); ?>" >                    </div>

                    <div class="event-body">
                    <?php if($evenement['type'] === 'privé' && (!isset($_SESSION["acces_autorise_".$evenement_id]) || $_SESSION["acces_autorise_".$evenement_id] == false)): ?>                 
                        <p>Accès restreint – Cet événement privé nécessite un mot de passe pour révéler ses informations.</p>
                        <?php else: ?>
                        <p class="event-description"><?php echo htmlspecialchars($evenement['description']); ?></p>
                        
                        <div class="event-details">
                            <div class="detail-item">
                                <i class="far fa-calendar-alt"></i>
                                <span><?php echo date('d M Y, H:i', strtotime($evenement['date_debut'])); ?> - 
                                <?php echo date('H:i', strtotime($evenement['date_fin'])); ?></span>
                            </div>
                            
                            <div class="detail-item">
                                <i class="fas fa-map-marker-alt"></i>
                                <a href="https://goo.gl/maps/example" target="_blank"><?php echo htmlspecialchars($evenement['lieu']); ?></a>
                            </div>
                            
                            <div class="detail-item">
                                <i class="fas fa-users"></i>
                                <span>250 places</span>
                            </div>
                            
                            <div class="detail-item likes">
                                <i class="far fa-heart"></i>
                                <span class="like-count"><?php echo htmlspecialchars($evenement['nb_like']); ?></span>
                                <button class="like-btn">J'aime</button>
                            </div>
                        </div>
                        <?php endif; ?>
                        <div class="event-actions">
    <?php if ($evenement['type'] === 'privé'): ?>
        
        <?php if(!isset($_SESSION["acces_autorise_".$evenement_id]) || $_SESSION["acces_autorise_".$evenement_id] == false): ?>
            <button class="btn btn-password" id="password-btn">ENTRER MOT DE PASSE</button>
                                    <div class="password-popup" id="password-popup" style="display:none;">
                                        <form id="password-form" method="POST" action="../config/verifypassword.php">
                                            <input type="password" name="event_password" placeholder="Mot de passe de l'événement" required>
                                            <input type="hidden" name="event_id" value="<?php echo $evenement_id; ?>">
                                            <?php 
                                            $pr=$_GET['pr'];
                                            if (count($tickets)==0) {
                                                $pr="Gratuit";
                                            }?>
                                            <input type="hidden" name="pr" value="<?php echo $pr ?>">

                                            <button type="submit" class="btn btn-validate">Valider</button>
                                        </form>
                                        <div id="password-error" style="color: red; margin-top: 10px; display: none;"></div>
                                    </div>        <?php else: ?>
            <?php if ($_GET['pr'] == 'Gratuit'): ?>
                <?php 
                // Check if user already participated

                $check_participation = $cnx->prepare("SELECT * FROM utilisateur_evenement 
                                                     WHERE utilisateur_id = ? AND evenement_id = ?");
                $check_participation->execute([$_SESSION['user_id'], $evenement_id]);
                $already_participated = $check_participation->rowCount() > 0;
                ?>
                
                <?php if ($already_participated): ?>
                    <form action="../config/retirer.php"  method="POST">
                <input type="hidden" name="user_id" value="<?php echo $_SESSION['user_id']; ?>">
                <input type="hidden" name="event_id" value="<?php echo $evenement_id; ?>">
                    <button type="submit" class="btn btn-danger">Retirer la participation</button>
                </form>

                <?php else: ?>
                    <form action="../config/participer.php" method="post" id="participation-form">
                        <input type="hidden" name="user_id" value="<?php echo $_SESSION['user_id']; ?>">
                        <input type="hidden" name="event_id" value="<?php echo $evenement_id; ?>">
                        <button type="submit" class="btn btn-primary">Participer</button>
                        <div class="reponse mt-2">
                            <?php if (isset($_SESSION['participation_error'])): ?>
                                <div class="alert alert-danger"><?php echo $_SESSION['participation_error']; ?></div>
                                <?php unset($_SESSION['participation_error']); ?>
                            <?php endif; ?>
                        </div>
                    </form>
                <?php endif; ?>
            <?php else: ?>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#reservationModal">Réserver</button>
                <?php endif; ?>
        <?php endif; ?>
    <?php else: ?>
        <?php if ($_GET['pr'] == 'Gratuit'): ?>
            <?php 
            // Check if user already participated (same as above)
            $check_participation = $cnx->prepare("SELECT * FROM utilisateur_evenement 
                                                WHERE utilisateur_id = ? AND evenement_id = ?");
            $check_participation->execute([$_SESSION['user_id'], $evenement_id]);
            $already_participated = $check_participation->rowCount() > 0;
            ?>
            
            <?php if ($already_participated): ?>
                <form action="../config/retirer.php"  method="POST">
                <input type="hidden" name="user_id" value="<?php echo $_SESSION['user_id']; ?>">
                <input type="hidden" name="event_id" value="<?php echo $evenement_id; ?>">
                    <button type="submit" class="btn btn-danger">Retirer la participation</button>
                </form>
            <?php else: ?>
                <form action="../config/participer.php" method="post" id="participation-form">
                    <input type="hidden" name="user_id" value="<?php echo $_SESSION['user_id']; ?>">
                    <input type="hidden" name="event_id" value="<?php echo $evenement_id; ?>">
                    <button type="submit" class="btn btn-primary">Participer</button>
                    <div class="reponse mt-2">
                        <?php if (isset($_SESSION['participation_error'])): ?>
                            <div class="alert alert-danger"><?php echo $_SESSION['participation_error']; ?></div>
                            <?php unset($_SESSION['participation_error']); ?>
                        <?php endif; ?>
                    </div>
                </form>
            <?php endif; ?>
        <?php else: ?>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#reservationModal">Réserver</button>
        <?php endif; ?>
    <?php endif; ?>
</div>
                    </div>
                </div>
            </article>
        <?php else: ?>
            <p class="error">Événement non trouvé</p>
        <?php endif; ?>
        </div>
    </main>

    <section class="tickets-section">
        <?php if($evenement['type'] === 'privé' && (!isset($_SESSION["acces_autorise_".$evenement_id]) || $_SESSION["acces_autorise_".$evenement_id] == false)): ?>
            <p>Accès restreint – Cet événement privé nécessite un mot de passe pour révéler ses informations.</p>
        <?php else: ?>
            <?php if (count($tickets) > 0): ?>
                <h2 class="section-title">Types de Tickets (<?php echo count($tickets); ?>)</h2>
                <div class="tickets-grid">
                    <?php foreach ($tickets as $ticket): ?>
                        <div class="ticket-card <?php echo htmlspecialchars(strtolower($ticket['type'])); ?>">
                            <h2><?php echo htmlspecialchars($ticket['type']); ?></h2>
                            <div class="ticket-price"><?php echo htmlspecialchars($ticket['prix']); ?> DNT</div>
                            <ul class="ticket-features">
                                <?php if (!empty($ticket['description_ticket'])): ?>
                                    <li><?php echo htmlspecialchars($ticket['description_ticket']); ?></li>
                                <?php endif; ?>
                                <li>Validité jusqu'au <?php echo date('d/m/Y', strtotime($ticket['date_validite'])); ?></li>
                                <li><?php echo htmlspecialchars($ticket['nb_ticket']); ?> places disponibles</li>
                            </ul>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p>Aucun ticket disponible pour cet événement</p>
            <?php endif; ?>
        <?php endif; ?>
    </section>

    <!-- Reservation Modal -->
    <div class="modal fade" id="reservationModal" tabindex="-1" aria-labelledby="reservationModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="reservationModalLabel">Sélectionnez vos billets</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <?php foreach ($tickets as $ticket): ?>
                        <div class="ticket-option mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="fw-bold mb-0"><?= htmlspecialchars($ticket['type']) ?></h6>
                                <span class="text-primary fw-bold"><?= htmlspecialchars($ticket['prix']) ?> DNT</span>
                            </div>
                            <?php if (!empty($ticket['description_ticket'])): ?>
                                <p class="small text-muted mb-3"><?= htmlspecialchars($ticket['description_ticket']) ?></p>
                            <?php endif; ?>
                            <div class="input-group">
                                <button class="btn btn-outline-secondary decrement" type="button">-</button>
                                <input type="number" 
       class="form-control text-center ticket-quantity" 
       value="0" 
       min="0" 
       data-price="<?php echo floatval($ticket['prix']); ?>" 
       data-type="<?php echo htmlspecialchars($ticket['type']); ?>">
                                <button class="btn btn-outline-secondary increment" type="button">+</button>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <div class="total-section mt-4 p-3 bg-light rounded">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6 class="fw-bold mb-0">Total:</h6>
                            <span id="totalAmount" class="fw-bold">0 DNT</span>
                        </div>
                    </div>
                    
                    <div id="errorMessage" class="alert alert-danger mt-3 d-none" role="alert"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                    <button type="button" class="btn btn-primary" id="payButton">
                        <span id="payButtonText">Payer avec Stripe</span>
                        <span id="payButtonSpinner" class="spinner-border spinner-border-sm d-none" role="status"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Success Modal -->
    <div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body text-center p-5">
                    <div class="mb-4">
                        <i class="bi bi-check-circle-fill text-success" style="font-size: 4rem;"></i>
                    </div>
                    <h4 class="fw-bold mb-3">Paiement Réussi!</h4>
                    <p class="mb-4">Vos billets ont été réservés. Un email de confirmation vous a été envoyé.</p>
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Continuer</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Stripe JS -->
    <script src="https://js.stripe.com/v3/"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assests/js/script_events_details.js" ></script>
    <script>
        // Initialize Stripe with your publishable key
        const stripe = Stripe('pk_test_51REYH2FbYksDBRZfUnGj1GZCzFolIGhKd4rx7S5LABI15RgKhZdvYD7MTKCYFQignkoOul7YE7encCLrhz4UNaLt00OXMxgGNv');
        const payButton = document.getElementById('payButton');
        const payButtonText = document.getElementById('payButtonText');
        const payButtonSpinner = document.getElementById('payButtonSpinner');
        const errorMessage = document.getElementById('errorMessage');

        // Ticket quantity controls
        document.querySelectorAll('.increment').forEach(button => {
            button.addEventListener('click', function() {
                const input = this.parentNode.querySelector('.ticket-quantity');
                input.value = parseInt(input.value) + 1;
                updateTotal();
            });
        });

        document.querySelectorAll('.decrement').forEach(button => {
            button.addEventListener('click', function() {
                const input = this.parentNode.querySelector('.ticket-quantity');
                const newValue = parseInt(input.value) - 1;
                input.value = newValue >= 0 ? newValue : 0;
                updateTotal();
            });
        });

        document.querySelectorAll('.ticket-quantity').forEach(input => {
            input.addEventListener('change', function() {
                this.value = parseInt(this.value) >= 0 ? parseInt(this.value) : 0;
                updateTotal();
            });
        });

        function updateTotal() {
            let total = 0;
            document.querySelectorAll('.ticket-quantity').forEach(input => {
                total += parseInt(input.value) * parseFloat(input.dataset.price);
            });
            document.getElementById('totalAmount').textContent = total + ' DNT';
        }

        // Replace the existing payButton click handler with this:
payButton.addEventListener('click', async function() {
    const total = calculateTotal();
    if (total > 0) {
        togglePayButtonState(true);
        hideError();

        try {
            // 1. Get selected tickets
            const selectedTickets = Array.from(document.querySelectorAll('.ticket-quantity'))
                .filter(input => parseInt(input.value) > 0)
                .map(input => ({
                    ticket_type: input.dataset.type, // "VIP", "Standard", etc.
                    quantity: parseInt(input.value),
                    price: parseFloat(input.dataset.price)
                }));

            // 2. Send to Node.js server
            const response = await fetch('http://localhost:3000/create-checkout-session', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    eventId: <?php echo $evenement_id; ?>,
                    tickets: selectedTickets
                }),
            });

            // 3. Handle response
            if (!response.ok) {
                const errorData = await response.json();
                throw new Error(errorData.message || "Payment failed");
            }

            const data = await response.json();
            
            // 4. Redirect to Stripe Checkout
            const { error } = await stripe.redirectToCheckout({ sessionId: data.id });
            if (error) throw error;

        } catch (error) {
            console.error("Payment Error:", error);
            showError(error.message || "Payment failed. Please try again.");
            togglePayButtonState(false);
        }
    } else {
        showError('Please select at least 1 ticket');
    }
});
        function calculateTotal() {
            return Array.from(document.querySelectorAll('.ticket-quantity'))
                .reduce((total, input) => total + (parseInt(input.value) * parseFloat(input.dataset.price)), 0);
        }

        function createLineItems() {
            return Array.from(document.querySelectorAll('.ticket-quantity'))
                .filter(input => parseInt(input.value) > 0)
                .map(input => ({
                    price: input.dataset.stripePriceId,
                    quantity: parseInt(input.value)
                }));
        }

        function togglePayButtonState(isLoading) {
            payButton.disabled = isLoading;
            payButtonText.textContent = isLoading ? 'Traitement en cours...' : 'Payer avec Stripe';
            payButtonSpinner.classList.toggle('d-none', !isLoading);
        }

        function showError(message) {
            errorMessage.textContent = message;
            errorMessage.classList.remove('d-none');
            setTimeout(() => {
                errorMessage.classList.add('d-none');
            }, 5000);
        }

        function hideError() {
            errorMessage.classList.add('d-none');
        }
    </script>
</body>
</html>
