<?php
require_once '../config/connect.php';
require_once '../config/traitement.php';
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    $_SESSION['participation_error'] = "Vous devez être connecté pour participer à un événement.";
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit();
}

// Récupérer les données du formulaire
$user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
$event_id = isset($_POST['event_id']) ? intval($_POST['event_id']) : 0;

// Vérifier que les IDs sont valides
if ($user_id <= 0 || $event_id <= 0) {
    $_SESSION['participation_error'] = "Données invalides.";
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit();
}

// Vérifier que l'utilisateur n'est pas déjà inscrit à cet événement
$check_query = "SELECT * FROM utilisateur_evenement 
                WHERE utilisateur_id = :user_id AND evenement_id = :event_id";
$stmt = $cnx->prepare($check_query);
$stmt->bindParam(':user_id', $user_id);
$stmt->bindParam(':event_id', $event_id);
$stmt->execute();

if ($stmt->rowCount() > 0) {
    $_SESSION['participation_error'] = "Vous êtes déjà inscrit à cet événement.";
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit();
}

// Insérer la participation dans la base de données
$insert_query = "INSERT INTO utilisateur_evenement (utilisateur_id, evenement_id) 
                 VALUES (:user_id, :event_id)";
$stmt = $cnx->prepare($insert_query);
$stmt->bindParam(':user_id', $user_id);
$stmt->bindParam(':event_id', $event_id);

if ($stmt->execute()) {
    // Mettre à jour la session pour l'accès
    $_SESSION["acces_autorise_".$event_id] = true;
    $_SESSION["last_access_".$event_id] = time();
    
    $_SESSION['participation_success'] = "Participation enregistrée avec succès!";
} else {
    $_SESSION['participation_error'] = "Erreur lors de l'enregistrement de la participation.";
}

header("Location: " . $_SERVER['HTTP_REFERER']);
exit();
?>