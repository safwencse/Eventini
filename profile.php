<?php 
require_once '../config/connect.php';
require_once '../config/traitement.php';

// Define the getEventStatus function
function getEventStatus($event) {
    $now = new DateTime();
    $start = new DateTime($event['date_debut']);
    $end = new DateTime($event['date_fin']);
    
    if ($now < $start) {
        return 'À venir';
    } elseif ($now > $end) {
        return 'Terminé';
    } else {
        return 'En cours';
    }
}

session_start();

// Vérification de l'authentification
if (!isset($_SESSION['user_id'])) {
    header("Location: ../view/signup.php?show=login");
    exit();
}

$utilisateur_id = $_SESSION['user_id'];
$utilisateur = getUtilisateurById($cnx, $utilisateur_id);

if (!$utilisateur) {
    die("Utilisateur non trouvé");
}

$evenementsCrees = getEvenementsCrees($cnx, $utilisateur_id);
$evenementsParticipes = getEvenementsParticipes($cnx, $utilisateur_id);

// Prepare events data for map
$eventsForMap = [];
foreach ($evenementsCrees as $event) {
    $eventsForMap[] = [
        'title' => $event['nom'],
        'date' => date('d/m/Y H:i', strtotime($event['date_debut'])),
        'location' => $event['lieu'],
        'image' => $event['image_evenement'],
        'latitude' => 36.8065, // Default Tunisia coordinates
        'longitude' => 10.1815,
        'user_relation' => 'created'
    ];
}
foreach ($evenementsParticipes as $event) {
    $eventsForMap[] = [
        'title' => $event['nom'],
        'date' => date('d/m/Y H:i', strtotime($event['date_debut'])),
        'location' => $event['lieu'],
        'image' => $event['image_evenement'],
        'latitude' => 36.8065, // Default Tunisia coordinates
        'longitude' => 10.1815,
        'user_relation' => 'participated'
    ];
}

// Determine active page from URL or default to profile
$active_page = isset($_GET['page']) ? $_GET['page'] : 'profile';

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['maj'])) {
    $data = [
        'email' => $_POST['email'],
        'telephone' => $_POST['telephone']
    ];
    
    // Handle file upload if present
    if (!empty($_FILES['image_utilisateur']['name'])) {
        $data['image_utilisateur'] = $_FILES['image_utilisateur'];
    }
    
    $result = updateUtilisateur($cnx, $data, $utilisateur_id);
    if ($result === "Mise à jour réussie") {
        $utilisateur = getUtilisateurById($cnx, $utilisateur_id);
        $success_message = "Profil mis à jour avec succès!";
    } else {
        $error_message = $result;
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assests/css/style_profile.css">
    <link rel="stylesheet" href="../assests/css/style_navbar.css">

    <title>Profil - Eventini</title>
</head>
<body>
<nav class="navbar" style='font-family: "Titillium Web", sans-serif;'>
    <div class="navbar__content">
        <a href="../index.php" class="navbar__logo">EVENTINI</a>
        <ul class="navbar__menu">
            <li class="nav-item <?php echo $active_page === 'home' ? 'active' : ''; ?>">
                <a href="../view/findEvents.php">
                    <i class="fa-solid fa-house"></i>
                    <span>Accueil</span>
                </a>
            </li>
            <li class="nav-item <?php echo $active_page === 'profile' ? 'active' : ''; ?>">
                <a href="profile.php?page=profile">
                    <i class="fa-solid fa-user"></i>
                    <span>Profil</span>
                </a>
            </li>
            <li class="nav-item <?php echo $active_page === 'settings' ? 'active' : ''; ?>">
                <a href="profile.php?page=settings">
                    <i class="fa-solid fa-sliders"></i>
                    <span>Paramètres</span>
                </a>
            </li>
            <li class="nav-item <?php echo $active_page === 'map' ? 'active' : ''; ?>">
                <a href="profile.php?page=map">
                    <i class="fa-solid fa-map-location-dot"></i>
                    <span>Map</span>
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
                    echo '<a href="profile.php" class="dropdown-item"><i class="fas fa-user"></i> Profil</a>';
                    echo '<a href="profile.php?page=settings" class="dropdown-item"><i class="fas fa-cog"></i> Paramètres</a>';
                    echo '<a href="profile.php?page=map" class="dropdown-item"><i class="fas fa-map"></i> Map</a>';
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
    <div class="app-container">


        <div class="main-content">
        
            <div class="content-area">
                <!-- Success/Error Messages -->
                <?php if (isset($success_message)): ?>
                    <div class="alert alert-success"><?php echo $success_message; ?></div>
                <?php endif; ?>
                <?php if (isset($error_message)): ?>
                    <div class="alert alert-danger"><?php echo $error_message; ?></div>
                <?php endif; ?>

                <div id="profile-page" class="page" style="display: <?php echo $active_page === 'profile' ? 'block' : 'none'; ?>;">
                    <div class="profile-container">
                        <div class="profile-header">
                        <div class="profile-picture">
    <img src="<?php echo '../config/' . (!empty($_SESSION['image_utilisateur']) ? htmlspecialchars($_SESSION['image_utilisateur']) : 'uploads/default.jpg'); ?>" alt="Profile picture">
</div>

                            <div class="profile-info">
                                <h2 class="profile-name"><?php echo htmlspecialchars($utilisateur['prenom'].' '.$utilisateur['nom']); ?></h2>
                                <p class="profile-email"><?php echo htmlspecialchars($utilisateur['email']); ?></p>
                                <div class="profile-stats">
                                    <div class="stat-item">
                                        <span class="stat-number"><?php echo count($evenementsCrees); ?></span>
                                        <span class="stat-label">Événements créés</span>
                                    </div>
                                    <div class="stat-item">
                                        <span class="stat-number"><?php echo count($evenementsParticipes); ?></span>
                                        <span class="stat-label">Participations</span>
                                    </div>
                                    <button class="btn btn-primary" id="create-event-btn"><a href="f.php" style="text-decoration: none; color: inherit;">Créer un Événement</a></button>

                                </div>
                            </div>
                        </div>
                        <nav class="content-nav">
                            <a href="#" class="nav-item active" id="events-tab">Mes Événements</a>
                            <a href="#" class="nav-item" id="participations-tab">Mes Participations</a>
                        </nav>

                        <!-- Événements Créés -->
                        <div class="events-section" id="events-section">
    <?php if (count($evenementsCrees) > 0): ?>
        <div class="events-grid">
            <?php foreach ($evenementsCrees as $event): 
                $status = getEventStatus($event);
            ?>
                <div class="event-card created-event">
                    <div class="event-card-content">
                        <?php
                            $imagePath = "../config/uploads/" . ($event['image_evenement'] ?? 'default_event.jpg');
                            $defaultImage = "../config/uploads/default_event.jpg";
                            $finalImage = (isset($event['image_evenement'])) && file_exists($imagePath) ? $imagePath : $defaultImage;
                        ?>
                        <img src="<?php echo htmlspecialchars($finalImage); ?>" alt="<?php echo htmlspecialchars($event['nom']); ?>">
                        <div class="event-details">
                            <h4><?php echo htmlspecialchars($event['nom']); ?></h4>
                            <div class="event-meta">
                                <span class="event-status status-<?php echo strtolower(str_replace(' ', '-', $status)); ?>">
                                    <?php echo $status; ?>
                                </span>
                                <span class="event-type <?php echo $event['type'] === 'privé' ? 'private' : 'public'; ?>">
                                    <?php echo $event['type']; ?>
                                </span>
                            </div>
                            <p class="event-description"><?php echo htmlspecialchars($event['description']); ?></p>
                            <div class="event-dates">
                                <span><i class="fas fa-calendar-day"></i> <?php echo date('d/m/Y H:i', strtotime($event['date_debut'])); ?></span>
                                <span><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($event['lieu']); ?></span>
                            </div>
                            <div class="event-actions">
                                <form action="events.php" method="get" class="action-form">
                                    <input type="hidden" name="evenement_id" value="<?= $event['evenement_id'] ?>">
                                    <input type="hidden" name="pr" value="Gratuit">
                                    <button type="submit" class="details-btn">Détails</button>
                                </form>
<form action="../config/eventbtns.php" method="post" name="delete" class="action-form">
    <input type="hidden" name="event_id" value="<?= $event['evenement_id'] ?>">
    <input type="hidden" name="action" value="delete">
    <button type="submit"  name="delete" class="delete-btn" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet événement?')">
        <i class="fas fa-trash"></i> Supprimer
    </button>
</form>
                                
                                <form action="modifier_form.php" method="get" class="action-form">
                                    <input type="hidden" name="event_id" value="<?= $event['evenement_id'] ?>">
                                    <button type="submit" class="edit-btn">
                                        <i class="fas fa-edit"></i> Modifier
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p class="no-events-message">Aucun événement créé. <a href="f.html" class="create-event-link">Créez votre premier événement</a></p>
    <?php endif; ?>
</div>

<style>
    .event-actions {
        display: flex;
        gap: 8px;
        margin-top: 10px;
        flex-wrap: wrap;
    }
    
    .action-form {
        flex: 1;
        min-width: 100px;
    }
    
    .details-btn, .delete-btn, .edit-btn {
        width: 100%;
        padding: 8px 12px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
        transition: all 0.3s;
    }
    
    .details-btn {
        background-color: var(--primary);
        color: white;
    }
    
    .details-btn:hover {
        background-color: blue;
    }
    
    .delete-btn {
        background-color: #f44336;
        color: white;
    }
    
    .delete-btn:hover {
        background-color: #d32f2f;
    }
    
    .edit-btn {
        background-color: #ff9800;
        color: white;
    }
    
    .edit-btn:hover {
        background-color: #f57c00;
    }
</style>

                        <!-- Événements Participés -->
                        <div class="events-section" id="participations-section" style="display: none;">
                            <?php if (count($evenementsParticipes) > 0): ?>
                                <div class="events-grid">
                                    <?php foreach ($evenementsParticipes as $event): 
                                        $status = getEventStatus($event);
                                    ?>
                                        <div class="event-card attended-event">
                                            <div class="event-card-content">
                                            <?php
$imagePath = "../config/uploads/" . ($event['image_evenement'] ?? 'default_event.jpg');
$defaultImage = "../config/uploads/default_event.jpg";
$finalImage = (isset($event['image_evenement']) && file_exists($imagePath)) ? $imagePath : $defaultImage;
?>

<img src="<?php echo htmlspecialchars($finalImage); ?>" alt="<?php echo htmlspecialchars($event['nom']); ?>">                                                 <div class="event-details">
                                                    <h4><?php echo htmlspecialchars($event['nom']); ?></h4>
                                                    <div class="event-meta">
                                                        <span class="event-status status-<?php echo strtolower(str_replace(' ', '-', $status)); ?>">
                                                            <?php echo $status; ?>
                                                        </span>
                                                        <span class="event-type <?php echo $event['type'] === 'privé' ? 'private' : 'public'; ?>">
                                                            <?php echo $event['type']; ?>
                                                        </span>
                                                    </div>
                                                    <p class="event-description"><?php echo htmlspecialchars($event['description']); ?></p>
                                                    <div class="event-dates">
                                                        <span><i class="fas fa-calendar-day"></i> <?php echo date('d/m/Y H:i', strtotime($event['date_debut'])); ?></span>
                                                        <span><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($event['lieu']); ?></span>
                                                    </div>
                                                    <form action="events.php" method="get">
  <input type="hidden" name="evenement_id" value="<?= $event['evenement_id'] ?>">
  <input type="hidden" name="pr" value="Gratuit">

  <button type="submit" class="details-btn">Détails</button>
</form>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <p class="no-events-message">Aucune participation à des événements. <a href="../view/findEvents.php" class="find-events-link">Découvrez des événements</a></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Settings Page -->
                <div id="settings-page" class="page" style="display: <?php echo $active_page === 'settings' ? 'block' : 'none'; ?>;">
                    <div class="settings-container">
                        <h2 class="settings-title">Paramètres du compte</h2>
                        
                        <form method="POST" enctype="multipart/form-data" class="settings-form">
                            <div class="settings-section">
                                <h3 class="section-title">Photo de profil</h3>
                                <div class="photo-upload">
                                <div class="profile-picture">
    <img src="<?php echo '../config/' . (!empty($_SESSION['image_utilisateur']) ? htmlspecialchars($_SESSION['image_utilisateur']) : 'uploads/default.jpg'); ?>" alt="Profile picture">
</div>
                                    <div class="upload-controls">
                                        <input type="file" id="profileUpload" name="image_utilisateur" accept="image/*" class="file-input">
                                        <label for="profileUpload" class="upload-btn">
                                            <i class="fas fa-camera"></i> Changer de photo
                                        </label>
                                        <p class="file-hint">Formats acceptés: JPG, PNG (max 5MB)</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="settings-section">
                                <h3 class="section-title">Informations personnelles</h3>
                                <div class="form-group">
                                    <label for="email">Adresse e-mail</label>
                                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($utilisateur['email']); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="telephone">Numéro de téléphone</label>
                                    <input type="tel" id="phone" name="telephone" value="<?php echo htmlspecialchars($utilisateur['telephone']); ?>" required>
                                </div>
                            </div>
                            
                            <div class="form-actions">
                                <button type="submit" name="maj" class="save-btn">
                                    <i class="fas fa-save"></i> Enregistrer les modifications
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Map Page -->
                <div id="map-page" class="page" style="display: <?php echo $active_page === 'map' ? 'block' : 'none'; ?>;">
                    <div class="map-container">
                        <div id="map"></div>
                    </div>
                    <script id="events-json" type="application/json"><?php echo json_encode($eventsForMap); ?></script>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet.markercluster@1.4.1/dist/leaflet.markercluster.js"></script>
    <script src="../assests/js/script.js"></script>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Tab switching functionality
        document.getElementById('events-tab').addEventListener('click', function(e) {
            e.preventDefault();
            document.getElementById('events-section').style.display = 'block';
            document.getElementById('participations-section').style.display = 'none';
            document.querySelectorAll('.content-nav .nav-item').forEach(el => el.classList.remove('active'));
            this.classList.add('active');
        });
        
        document.getElementById('participations-tab').addEventListener('click', function(e) {
            e.preventDefault();
            document.getElementById('events-section').style.display = 'none';
            document.getElementById('participations-section').style.display = 'block';
            document.querySelectorAll('.content-nav .nav-item').forEach(el => el.classList.remove('active'));
            this.classList.add('active');
        });

        // Menu toggle functionality
        document.querySelector('.menu-toggle').addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('active');
        });
        
        // Initialize map if on map page
        if (document.getElementById('map-page').style.display === 'block') {
            initMap();
        }

        function initMap() {
            const map = L.map('map').setView([36.8065, 10.1815], 13); // Default to Tunisia coordinates
            
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(map);

            const events = JSON.parse(document.getElementById('events-json').textContent);
            const markers = L.markerClusterGroup();

            // Custom icons
            const createdIcon = L.icon({
                iconUrl: 'https://cdn.jsdelivr.net/npm/leaflet-color-markers@1.0.3/img/marker-icon-2x-orange.png',
                shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-shadow.png',
                iconSize: [25, 41],
                iconAnchor: [12, 41],
                popupAnchor: [1, -34],
                shadowSize: [41, 41]
            });

            const participatedIcon = L.icon({
                iconUrl: 'https://cdn.jsdelivr.net/npm/leaflet-color-markers@1.0.3/img/marker-icon-2x-blue.png',
                shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-shadow.png',
                iconSize: [25, 41],
                iconAnchor: [12, 41],
                popupAnchor: [1, -34],
                shadowSize: [41, 41]
            });

            events.forEach(event => {
                const icon = event.user_relation === 'created' ? createdIcon : participatedIcon;
                const marker = L.marker([event.latitude, event.longitude], { icon })
                    .bindPopup(`
                        <div class="map-popup">
                            <h3>${event.title}</h3>
                            <p><strong>Date:</strong> ${event.date}</p>
                            <p><strong>Location:</strong> ${event.location}</p>
                            <p><strong>Type:</strong> ${event.user_relation === 'created' ? 'Created by you' : 'You participated'}</p>
                            ${event.image ? `<img src="../resources/images/${event.image}" style="max-width:100%;height:100px;object-fit:cover;">` : ''}
                        </div>
                    `);
                markers.addLayer(marker);
            });

            map.addLayer(markers);
            
            // Fit bounds to show all markers
            if (events.length > 0) {
                map.fitBounds(markers.getBounds());
            }
        }
    });
    </script>
</body>
</html>