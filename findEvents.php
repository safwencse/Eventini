<?php
require_once "../config/connect.php";
require_once "../config/traitement.php";
session_start();

// Get current page from URL, default to 1
// Get current page from URL, default to 1
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($current_page < 1) $current_page = 1;

$events_per_page = 8;
// Get filters from URL
$filters = [
    'category' => isset($_GET['category']) ? $_GET['category'] : '',
    'price' => isset($_GET['price']) ? $_GET['price'] : '',
];

// Get sort option from URL
$sortBy = isset($_GET['sort']) ? $_GET['sort'] : 'date-asc';

$total_events = countAllEvents($cnx, $filters);
$total_pages = ceil($total_events / $events_per_page);

// Ensure current page doesn't exceed total pages
if ($current_page > $total_pages && $total_pages > 0) {
    $current_page = $total_pages;
}

$events = getAllEvents($cnx, $current_page, $events_per_page, $filters, $sortBy);
$events_ca = getAllEventsca($cnx);
?>
<!DOCTYPE html>
<html lang="fr">
<head>



    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eventini - Découvrez des Événements Exceptionnels</title>


    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assests/css/style_events.css">
    <link rel="stylesheet" href="../assests/css/style_navbar.css">

</head>
<body>
<nav class="navbar" style='font-family: "Titillium Web", sans-serif;'> 
    <div class="navbar__content">
        <a href="../index.php" class="navbar__logo">EVENTINI</a>
        <ul class="navbar__menu">

            <li>
            <?php
            if(isset($_SESSION['user_id']) && $_SESSION['user_id'] != '') {
                echo '<div class="userzone">';
                echo ' <div class="user">
                    <img src="../config/' . (!empty($_SESSION['image_utilisateur']) ? htmlspecialchars($_SESSION['image_utilisateur']) : 'uploads/default.jpg') . '" alt="Profile picture">
                    </div>';
                echo '<div class="zone_profile">' . $_SESSION['nom'] . ' ' . $_SESSION['prenom'] . '</div>';
                
                echo '<div class="profile-dropdown">';
                echo '<a href="profile.php" class="dropdown-item"><i class="fas fa-user"></i> Profil</a>';
                echo '<a href="../config/logout.php" class="dropdown-item"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>';
                echo '</div>';
                
                echo '</div>';
            } else {
                echo '<div class="navbar__buttons">
                    <a href="signup.php?show=login" class="navbar__btn navbar__btn--login" id="seconnecter">Se connecter</a>
                    <a href="signup.php" class="navbar__btn navbar__btn--signup" id="inscriptions">Inscrivez-vous</a>
                </div>';
            }
            ?>
            </li>
        </ul>
    </div>
</nav>


    <div class="search-filter-section">
    <div class="title">
        <h1>
            <?php if(isset($_SESSION['user_id']) && $_SESSION['user_id'] != ''): ?>
                WELCOME BACK, <?php echo htmlspecialchars($_SESSION['prenom']); ?>
            <?php else: ?>
                EVENTINI
            <?php endif; ?>
        </h1>
    </div>

  <!-- Éléments de fond animés -->
  <div class="filter-floating-elements">
    <div class="filter-floating-element"></div>
    <div class="filter-floating-element"></div>
    <div class="filter-floating-element"></div>
  </div>
  <div class="filter-grid-overlay"></div>
  <div class="filter-connecting-dots" id="filterDots"></div>
  
  <!-- Votre contenu existant -->
  <div class="container">

            <div class="search-bar">
    <input type="text" id="eventSearch" placeholder="" class="form-control">
    <div id="results-dropdown" class="results-dropdown"></div>
</div>
            <div class="filter-bar">
            <div class="filter-group">
    <label for="price-scale"> prix :</label>
    <div class="price-scale-container">
        <input type="range" id="price-scale" min="0" max="2" step="1" value="<?php echo isset($_GET['price']) ? 
            ($_GET['price'] == '0' ? '0' : ($_GET['price'] == '25' ? '1' : '2')) : '0'; ?>">
        <div class="scale-labels">
            <span>Gratuit</span>
            <span>25 DT</span>
            <span>100 DT</span>
        </div>
    </div>
</div>


<div class="filter-group">
    <label for="categories">Catégories :</label>
    <select id="categories">
        <option value="all" <?php echo empty($_GET['category']) || $_GET['category'] == 'all' ? 'selected' : ''; ?>>Toutes les catégories</option>
        <option value="music" <?php echo isset($_GET['category']) && $_GET['category'] == 'music' ? 'selected' : ''; ?>>Musique</option>
        <option value="sports" <?php echo isset($_GET['category']) && $_GET['category'] == 'sports' ? 'selected' : ''; ?>>Sports</option>
        <option value="food" <?php echo isset($_GET['category']) && $_GET['category'] == 'food' ? 'selected' : ''; ?>>Nourriture & Boissons</option>
        <option value="art" <?php echo isset($_GET['category']) && $_GET['category'] == 'art' ? 'selected' : ''; ?>>Art & Culture</option>
        <option value="business" <?php echo isset($_GET['category']) && $_GET['category'] == 'business' ? 'selected' : ''; ?>>Business</option>
        <option value="education" <?php echo isset($_GET['category']) && $_GET['category'] == 'education' ? 'selected' : ''; ?>>Éducation</option>
    </select>
</div >
<div  class="filter-group">
<div class="espaces"></div>
</div>
    <div class="btns-filters" > 
    <?php if(isset($_SESSION['user_id']) && $_SESSION['user_id'] != '') : ?>
        <button class="filter-btn">Appliquer les filtres</button>
    <?php else : ?>
        <button class="filter-btn disabled" style="cursor: not-allowed;">Appliquer les filtres</button>
    <?php endif; ?>
    
    <button class="reset-btn">Réinitialiser</button>
</div>
</div>

<!-- Popup de connexion -->
<div id="login-popup" class="popup-container">
    <div class="popup-content">
        <span class="close-popup">&times;</span>
        <p>Vous devez vous connecter pour utiliser le filtrage.</p>
        <a href="signup.php?show=login" class="login-link">Se connecter</a>
    </div>
</div>

<style>
/* Style pour le bouton désactivé */
.popup-content p{
    color:black;
}
.filter-btn.disabled {
    opacity: 0.7;
    cursor: not-allowed !important;
}

.filter-btn.disabled:hover {
    background-color:rgba(106, 100, 100, 0.65);
}

/* Style pour la popup */
.popup-content{
    border:2px solid red;
}
.popup-container {
    background-color:rgba(0, 0, 0, 0.69);
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 1000;
    justify-content: center;
    align-items: center;

}

.popup-content {
    background: white;
    padding: 25px;
    border-radius: 8px;
    width: 300px;
    text-align: center;
    position: relative;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.close-popup {
    position: absolute;
    top: 10px;
    right: 15px;
    font-size: 20px;
    cursor: pointer;
    color: black;
}

.login-link {
    display: inline-block;
    margin-top: 15px;
    padding: 8px 20px;
    border:2px solid rgb(0, 0, 0) ;
    color: black;
    text-decoration: none;
    border-radius: 4px;
    transition: background-color 0.3s;
}

.login-link:hover {
    background-color:rgba(53, 53, 53, 0.23);
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const disabledButtons = document.querySelectorAll('.filter-btn.disabled');
    const popup = document.getElementById('login-popup');
    const closePopup = document.querySelector('.close-popup');
    
    disabledButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            popup.style.display = 'flex';
        });
    });
    
    closePopup.addEventListener('click', function() {
        popup.style.display = 'none';
    });
    
    // Fermer la popup en cliquant à l'extérieur
    window.addEventListener('click', function(e) {
        if (e.target === popup) {
            popup.style.display = 'none';
        }
    });
});
</script>
 

           
        </div>
    </div>

    <div class="recent-events-section">
        <div class="container">
            <h2 class="section-title">Événements ajoutés récemment</h2>
            <div class="carousel-container">
                <div class="carousel" data-total-items="<?php echo count($events_ca); ?>">
                    <?php foreach ($events_ca as $index => $event): ?>
                    <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>">
                        <div class="event-card">
                            <div class="event-image">
                            <?php
$imagePath = "../config/uploads/" . ($event['image_evenement'] ?? 'default_event.jpg');
$defaultImage = "../config/uploads/default_event.jpg";
$finalImage = (isset($event['image_evenement']) && file_exists($imagePath)) ? $imagePath : $defaultImage;
?>

<img src="<?php echo htmlspecialchars($finalImage); ?>" alt="<?php echo htmlspecialchars($event['nom']); ?>">                            </div>
                            <div class="event-details">
                                <h3 class="event-title"><?php echo htmlspecialchars($event['nom']); ?></h3>
                                <p class="event-date">
                                    <i class="far fa-calendar-alt"></i>
                                    <?php
                                    $dateDebut = date('d/m/Y', strtotime($event['date_debut']));
                                    $dateFin = date('d/m/Y', strtotime($event['date_fin']));
                                
                                    if ($dateDebut === $dateFin) {
                                        echo $dateDebut;
                                    } else {
                                        echo $dateDebut . ' - ' . $dateFin;
                                    }
                                    ?>
                                </p>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

            </div>
        </div>
    </div>
    <!-- Contenu principal - Cartes d'événements -->
    <main class="main-content">
        <div class="container">
            <div class="events-header">
                <h2>Événements à Venir</h2>
                <<div class="sort-options">
    <label for="sort-by">Trier par :</label>
    <select id="sort-by" onchange="updateSort()">
        <option value="date-asc" <?php echo $sortBy == 'date-asc' ? 'selected' : ''; ?>>Date (plus lointaine)</option>
        <option value="date-desc" <?php echo $sortBy == 'date-desc' ? 'selected' : ''; ?>>Date (plus proche)</option>
        <option value="price-asc" <?php echo $sortBy == 'price-asc' ? 'selected' : ''; ?>>Prix (croissant)</option>
        <option value="price-desc" <?php echo $sortBy == 'price-desc' ? 'selected' : ''; ?>>Prix (décroissant)</option>
    </select>
</div>
            </div>
            
            <div class="events-grid">
            <?php foreach ($events as $event): ?>
    <div class="event-card">
    <div class="event-image">
    <?php
$imagePath = "../config/uploads/" . ($event['image_evenement'] ?? 'default_event.jpg');
$defaultImage = "../config/uploads/default_event.jpg";
$finalImage = (isset($event['image_evenement']) && file_exists($imagePath)) ? $imagePath : $defaultImage;
?>

<img src="<?php echo htmlspecialchars($finalImage); ?>" alt="<?php echo htmlspecialchars($event['nom']); ?>">    <span class="event-category"><?php echo htmlspecialchars($event['categorie']); ?></span>
    <h3 class="event-title-on-image"><?php echo htmlspecialchars($event['nom']); ?></h3>
</div>
<div class="orangebar"></div>

    <div class="event-details">
        <p class="event-date"><i class="far fa-calendar-alt"></i> <?php 
        if ($event['type'] == 'privé') {
            echo '--/--/----';
        } else {
            echo date('j F Y', strtotime($event['date_debut']));
        }
        ?></p>
        <p class="event-location"><i class="fas fa-map-marker-alt"></i> <?php
        if ($event['type'] == 'privé') {
            $event['lieu'] = '----------';
        }
        echo htmlspecialchars($event['lieu']); ?></p>
     <p class="event-price">
            <?php 
        $price = isset($event['prix']) ? $event['prix']. ' DT' : 'Gratuit';
        if($price==0){
            $price='Gratuit';
        }
        if ($event['type'] == 'privé') {
            $price = 'Cet événement est privé';
        }
        echo htmlspecialchars($price);
        ?>
     </p>
     <form action="events.php" method="get">
  <input type="hidden" name="evenement_id" value="<?= $event['evenement_id'] ?>">
  <input type="hidden" name="pr" value="<?= $price ?>">

  <button type="submit" class="event-btn">Détails</button>
</form>
    </div>
    </div>
<?php endforeach; ?>
                
                
                </div>

                <div class="pagination">
    <?php if ($current_page > 1): ?>
        <a href="?page=<?php echo $current_page - 1; ?>&sort=<?php echo $sortBy; ?><?php echo !empty($filters['category']) ? '&category='.$filters['category'] : ''; ?><?php echo !empty($filters['price']) ? '&price='.$filters['price'] : ''; ?>" class="page-btn"><i class="fas fa-chevron-left"></i> Précédent</a>
    <?php endif; ?>
    
    <?php 
    // Show page numbers
    $start_page = max(1, $current_page - 2);
    $end_page = min($total_pages, $current_page + 2);
    
    if ($start_page > 1) {
        echo '<a href="?page=1&sort='.$sortBy. (!empty($filters['category']) ? '&category='.$filters['category'] : '') . (!empty($filters['price']) ? '&price='.$filters['price'] : '') .'" class="page-btn">1</a>';
        if ($start_page > 2) {
            echo '<span class="page-dots">...</span>';
        }
    }
    
    for ($i = $start_page; $i <= $end_page; $i++): ?>
        <a href="?page=<?php echo $i; ?>&sort=<?php echo $sortBy; ?><?php echo !empty($filters['category']) ? '&category='.$filters['category'] : ''; ?><?php echo !empty($filters['price']) ? '&price='.$filters['price'] : ''; ?>" class="page-btn <?php echo $i == $current_page ? 'active' : ''; ?>"><?php echo $i; ?></a>
    <?php endfor; 
    
    if ($end_page < $total_pages) {
        if ($end_page < $total_pages - 1) {
            echo '<span class="page-dots">...</span>';
        }
        echo '<a href="?page='.$total_pages.'&sort='.$sortBy. (!empty($filters['category']) ? '&category='.$filters['category'] : '') . (!empty($filters['price']) ? '&price='.$filters['price'] : '') .'" class="page-btn">'.$total_pages.'</a>';
    }
    ?>
    
    <?php if ($current_page < $total_pages): ?>
        <a href="?page=<?php echo $current_page + 1; ?>&sort=<?php echo $sortBy; ?><?php echo !empty($filters['category']) ? '&category='.$filters['category'] : ''; ?><?php echo !empty($filters['price']) ? '&price='.$filters['price'] : ''; ?>" class="page-btn">Suivant <i class="fas fa-chevron-right"></i></a>
    <?php endif; ?>
</div>
        </main>

    <!-- Pied de page -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section about">
                    <h3>Eventini</h3>
                    <p>Découvrez et réservez des événements exceptionnels près de chez vous. Des concerts aux ateliers, nous avons quelque chose pour tout le monde.</p>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-linkedin-in"></i></a>
                    </div>
                </div>
                
                <div class="footer-section links">
                    <h3>Liens Rapides</h3>
                    <ul>
                        <li><a href="#">Accueil</a></li>
                        <li><a href="#">Événements</a></li>
                        <li><a href="#">Catégories</a></li>
                        <li><a href="#">À propos</a></li>
                        <li><a href="#">Contact</a></li>
                    </ul>
                </div>
                
                <div class="footer-section contact">
                    <h3>Contactez-nous</h3>
                    <p><i class="fas fa-map-marker-alt"></i>Sousse, ESSTHS</p>
                    <p><i class="fas fa-phone"></i>+216 84 587 284</p>
                    <p><i class="fas fa-envelope"></i> essths@eventini.com</p>
                </div>
                
                <div class="footer-section newsletter">
                    <h3>Newsletter</h3>
                    <p>Abonnez-vous pour recevoir les dernières actualités sur les événements à venir</p>
                    <form>
                        <input type="email" placeholder="Votre Email">
                        <button type="submit">S'abonner</button>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="footer-bottom">
            <div class="container">
                <p>&copy; 2023 Eventini. Tous droits réservés.</p>
            </div>
        </div>
    </footer>

    <script src="../assests/js/script_events.js"></script>
    <script src="../assests/js/script_back_events.js" defer></script>
    <script>
        document.getElementById('price-scale').addEventListener('input', function() {
            const value = parseInt(this.value);
            let priceRange;
            
            switch(value) {
                case 0:
                    priceRange = "0€ - 50€"; // Bas
                    break;
                case 1:
                    priceRange = "50€ - 100€"; // Moyen
                    break;
                case 2:
                    priceRange = "100€ et plus"; // Elevé
                    break;
                default:
                    priceRange = "Tous les prix";
            }
            
            console.log("Selected price range:", priceRange);
            // You can use this value to filter your products
        });
        document.addEventListener('DOMContentLoaded', function() {
  const carousel = document.querySelector('.carousel');
  const items = document.querySelectorAll('.carousel-item');
  const itemWidth = 300; // Largeur de chaque élément + gap
  const totalItems = items.length;
  const visibleItems = Math.floor(window.innerWidth / itemWidth);
  const totalWidth = totalItems * itemWidth;
  
  // Clone les éléments pour une rotation infinie
  items.forEach(item => {
    carousel.appendChild(item.cloneNode(true));
  });
  
  // Animation CSS personnalisée
  const style = document.createElement('style');
  style.innerHTML = `
    @keyframes carousel-scroll {
      0% {
        transform: translateX(0);
      }
      100% {
        transform: translateX(-${totalWidth}px);
      }
    }
    
    .carousel {
      animation: carousel-scroll ${totalItems * 3}s linear infinite;
      width: ${totalWidth * 2}px;
    }
  `;
  document.head.appendChild(style);
  
  // Pause au survol
  carousel.addEventListener('mouseenter', () => {
    carousel.style.animationPlayState = 'paused';
  });
  
  carousel.addEventListener('mouseleave', () => {
    carousel.style.animationPlayState = 'running';
  });
});


    </script>
    <script>
// Create random particles
document.addEventListener('DOMContentLoaded', function() {
  const particlesContainer = document.createElement('div');
  particlesContainer.className = 'particles';
  document.body.appendChild(particlesContainer);
  
  // Create 20 particles
  for (let i = 0; i < 20; i++) {
    const particle = document.createElement('div');
    particle.className = 'particle';
    
    // Random properties
    const size = Math.random() * 3 + 1;
    const posX = Math.random() * 100;
    const posY = Math.random() * 100;
    const duration = Math.random() * 20 + 10;
    const delay = Math.random() * 10;
    
    // Apply styles
    particle.style.width = `${size}px`;
    particle.style.height = `${size}px`;
    particle.style.left = `${posX}%`;
    particle.style.top = `${posY}%`;
    particle.style.animationDuration = `${duration}s`;
    particle.style.animationDelay = `${delay}s`;
    
    particlesContainer.appendChild(particle);
  }
});
// Création des points connectés
document.addEventListener('DOMContentLoaded', function() {
  const dotsContainer = document.getElementById('filterDots');
  if (!dotsContainer) return;

  // Créer 15 points aléatoires
  for (let i = 0; i < 15; i++) {
    const dot = document.createElement('div');
    dot.className = 'filter-dot';
    dot.style.left = `${Math.random() * 100}%`;
    dot.style.top = `${Math.random() * 100}%`;
    dot.style.width = `${Math.random() * 3 + 1}px`;
    dot.style.height = dot.style.width;
    dot.style.animation = `pulse ${Math.random() * 4 + 2}s infinite alternate`;
    dotsContainer.appendChild(dot);
  }

  // Animation de pulsation pour les points
  const style = document.createElement('style');
  style.textContent = `
    @keyframes pulse {
      from { transform: scale(1); opacity: 0.3; }
      to { transform: scale(1.5); opacity: 0.7; }
    }
  `;
  document.head.appendChild(style);
});
</script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
    // Handle filter button click
    document.querySelector('.filter-btn').addEventListener('click', function() {
        if (this.classList.contains('disabled')) {
            return;
        }
        
        const priceScale = document.getElementById('price-scale').value;
        const category = document.getElementById('categories').value;
        
        // Map price scale to actual values
        let priceValue = '';
        switch(priceScale) {
            case '0': priceValue = '0'; break; // Gratuit
            case '1': priceValue = '25'; break; // Moyen (25 DT)
            case '2': priceValue = '100'; break; // Elevé (100 DT)
        }
        
        // Build URL with filters
        let url = '?page=1'; // Always reset to first page when filtering
        
        if (category !== 'all') {
            url += `&category=${encodeURIComponent(category)}`;
        }
        
        if (priceValue !== '') {
            url += `&price=${encodeURIComponent(priceValue)}`;
        }
        

        
        window.location.href = url;
    });
    
    // Handle reset button click
    document.querySelector('.reset-btn').addEventListener('click', function() {
        // Reset all filter controls
        document.getElementById('price-scale').value = '0';
        document.getElementById('categories').value = 'all';
        
        // Reload page without filters
        window.location.href = '?page=1';
    });
    
    // Update price labels based on slider
    const priceScale = document.getElementById('price-scale');
    const priceLabels = document.querySelectorAll('.scale-labels span');
    
    priceScale.addEventListener('input', function() {
        priceLabels.forEach((label, index) => {
            label.style.fontWeight = index == this.value ? 'bold' : 'normal';
        });
    });
    
    // Initialize price labels
    priceLabels[0].style.fontWeight = 'bold';
    
    // Update price labels text to show actual values
    priceLabels[0].textContent = 'Gratuit';
    priceLabels[1].textContent = '25 DT';
    priceLabels[2].textContent = '100 DT';
});
</script>
<script>
// Search functionality with dropdown
document.addEventListener('DOMContentLoaded', function() {
    const performSearch = async () => {
        const query = document.getElementById('eventSearch').value.trim();
        const dropdown = document.getElementById('results-dropdown');
        
        if (!query) {
            dropdown.style.display = 'none';
            return;
        }
        
        dropdown.innerHTML = '<div class="search-loading">Recherche en cours...</div>';
        dropdown.style.display = 'block';

        try {
            const response = await fetch('http://127.0.0.1:5000/api/search?q=' + encodeURIComponent(query), {
                method: 'GET',
                headers: {
                    'Accept': 'application/json'
                }
            });

            if (!response.ok) {
                throw new Error(`Erreur HTTP ${response.status}`);
            }

            const data = await response.json();
            
            if (data.length === 0) {
                dropdown.innerHTML = '<div class="no-results">Aucun résultat trouvé</div>';
                return;
            }

            let html = '';
            data.forEach(event => {
                html += `
                <div class="event-search-item">
                    <div class="event-search-name">${event.nom}</div>
                    <div class="event-search-score">Pertinence: ${(event.score * 100).toFixed(1)}%</div>
                </div>`;
            });
            dropdown.innerHTML = html;

        } catch (error) {
            dropdown.innerHTML = `
            <div class="search-error">
                Erreur de connexion au serveur<br>
                <small>Assurez-vous que le serveur Python est démarré</small>
            </div>`;
            console.error('Détails:', error);
        }
    };

    // Event listeners
    document.getElementById('eventSearch').addEventListener('input', function() {
        if (this.value.trim().length > 0) {
            performSearch();
        } else {
            document.getElementById('results-dropdown').style.display = 'none';
        }
    });
    
    document.getElementById('eventSearch').addEventListener('keypress', (e) => {
        if (e.key === 'Enter') performSearch();
    });
    
    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.search-bar')) {
            document.getElementById('results-dropdown').style.display = 'none';
        }
    });
});
</script>
<script>
    function updateSort() {
    const sortBy = document.getElementById('sort-by').value;
    const url = new URL(window.location.href);
    
    // Update or add sort parameter
    url.searchParams.set('sort', sortBy);
    
    // Reset to first page when changing sort
    url.searchParams.set('page', '1');
    
    window.location.href = url.toString();
}

// Initialize the select with the current sort value
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const currentSort = urlParams.get('sort') || 'date-asc';
    document.getElementById('sort-by').value = currentSort;
});
</script>
</body>
</html>