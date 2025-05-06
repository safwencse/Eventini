<?php
session_start();
include("connect.php");

if(isset($_POST['delete'])) {
    // Sanitize input
    $event_id = filter_input(INPUT_POST, 'event_id', FILTER_VALIDATE_INT);
    
    if($event_id) {
        $req = "DELETE FROM evenement WHERE evenement_id = ?";
        $stmt = $cnx->prepare($req);
        $stmt->execute([$event_id]);
        $_SESSION['message-e'] = "Événement supprimé avec succès";
    }
    header('Location: ../view/profile.php');
    exit();
} else {
    header('Location: ../view/profile.php');
    exit();
}