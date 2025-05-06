<?php
require_once '../config/connect.php';
require_once '../config/traitement.php';
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    $_SESSION['participation_error'] = "Vous devez être connecté pour cette action.";
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

try {
    // Préparer et exécuter la requête de suppression
    $delete_query = "DELETE FROM utilisateur_evenement 
                    WHERE utilisateur_id = :user_id AND evenement_id = :event_id";
    $stmt = $cnx->prepare($delete_query);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindParam(':event_id', $event_id, PDO::PARAM_INT);
    
    if ($stmt->execute()) {
        // Vérifier si des lignes ont été affectées
        if ($stmt->rowCount() > 0) {
            $_SESSION['participation_success'] = "Désinscription réussie.";
            // Mettre à jour la session si nécessaire
            if (isset($_SESSION["acces_autorise_".$event_id])) {
                unset($_SESSION["acces_autorise_".$event_id]);
                unset($_SESSION["last_access_".$event_id]);
            }
        } else {
            $_SESSION['participation_error'] = "Vous n'étiez pas inscrit à cet événement.";
        }
    } else {
        $_SESSION['participation_error'] = "Erreur lors de la désinscription.";
    }
} catch (PDOException $e) {
    $_SESSION['participation_error'] = "Erreur technique: " . $e->getMessage();
}

header("Location: " . $_SERVER['HTTP_REFERER']);
exit();
?>