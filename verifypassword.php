<?php
require_once '../config/connect.php';
require_once 'traitement.php';

// Démarrer la session en premier
session_start();

// Vérifier que la requête est bien POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('HTTP/1.1 405 Method Not Allowed');
    exit();
}

// Validation et nettoyage des entrées
$event_id = filter_input(INPUT_POST, 'event_id', FILTER_VALIDATE_INT);
$submitted_password = filter_input(INPUT_POST, 'event_password', FILTER_SANITIZE_STRING);
$pr=$_POST["pr"];
// Vérifier que l'ID et le mot de passe sont valides
if (!$event_id || $event_id <= 0 || $submitted_password === null || $submitted_password === '') {
    $_SESSION['password_error'] = "Données invalides";
    header('Location: ../view/events.php?evenement_id='.$event_id.'&pr='.$pr);
    exit();
}

try {
    // Récupérer le mot de passe de l'événement
    $query = "SELECT mdp_prive FROM evenement WHERE evenement_id = ?";
    $stmt = $cnx->prepare($query);
    $stmt->execute([$event_id]);
    $event = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$event) {
        $_SESSION['password_error'] = "Événement introuvable";
        header('Location: ../view/events.php?evenement_id='.$event_id.'&pr='.$pr);
        exit();
    }

    // Vérifier le mot de passe (comparaison directe car non haché)
    if (!empty($event['mdp_prive']) && $event['mdp_prive'] === $submitted_password) {
        // Mot de passe correct
        $_SESSION["acces_autorise_".$event_id] = true;
        $_SESSION["last_access_".$event_id] = time();
        unset($_SESSION['password_error']);
    } else {
        $_SESSION["acces_autorise_".$event_id] = false;
        $_SESSION['password_error'] = "Mot de passe incorrect";
    }

    // Redirection vers la page de l'événement
    header('Location: ../view/events.php?evenement_id='.$event_id.'&pr='.$pr);
    exit();

} catch (PDOException $e) {
    // Journaliser l'erreur
    error_log("Database error in verifypassword.php: " . $e->getMessage());
    
    $_SESSION['password_error'] = "Une erreur technique est survenue";
    header('Location: ../view/events.php?evenement_id='.$event_id.'&pr='.$pr);
    exit();
}