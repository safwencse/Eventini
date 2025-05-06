<?php
require_once '../config/connect.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Récupération des données du formulaire
    $nom = $_POST["nom"];
    $description = $_POST["description"];
    $date_debut = $_POST["date-debut"];
    $date_fin = isset($_POST["date-fin"]) && !empty($_POST["date-fin"]) ? $_POST["date-fin"] : $date_debut;
    $heure = $_POST["event-time"];
    $lieu = $_POST["lieu"];
    $categorie = $_POST["type"];
    $nb_place = $_POST["total_tickets"];
    $type = $_POST["visibilite"];
    $mdp_prive = $_POST["password"];
    $organisateur_id = $_SESSION["user_id"];

    // Combiner date et heure pour la date de début
    $date_debut = $date_debut . ' ' . $heure . ':00';
    if ($date_fin) {
        $date_fin = $date_fin . ' ' . $heure . ':00';
    }

    // Upload image de l'événement
    $image_evenement = "";
    if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/../uploads/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $nomImage = uniqid() . "_" . basename($_FILES['image']['name']);
        $chemin = $uploadDir . $nomImage;
        
        if (move_uploaded_file($_FILES['image']['tmp_name'], $chemin)) {
            $image_evenement = 'uploads/' . $nomImage;
        } else {
            error_log("Failed to move uploaded file to: " . $chemin);
        }
    }

    // Insertion de l'événement dans la base
    $sql = "INSERT INTO evenement 
            (nom, description, date_debut, date_fin, lieu, categorie, image_evenement, nb_place, nb_like, type, mdp_prive, organisateur_id) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0, ?, ?, ?)";

    $stmt = $cnx->prepare($sql);
    $stmt->execute([
        $nom,
        $description,
        $date_debut,
        $date_fin,
        $lieu,
        $categorie,
        $image_evenement,
        $nb_place,
        $type,
        $mdp_prive,
        $organisateur_id,    ]);

    $evenement_id = $cnx->lastInsertId();

    // Traitement des documents légaux
    if (isset($_FILES['legal-docs']) && is_array($_FILES['legal-docs']['name'])) {
        $uploadDir = __DIR__ . '/../uploads/legal-docs/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        foreach ($_FILES['legal-docs']['tmp_name'] as $key => $tmp_name) {
            if ($_FILES['legal-docs']['error'][$key] == UPLOAD_ERR_OK) {
                $originalName = basename($_FILES['legal-docs']['name'][$key]);
                $extension = pathinfo($originalName, PATHINFO_EXTENSION);
                $newName = uniqid() . '_' . $evenement_id . '.' . $extension;
                $destination = $uploadDir . $newName;
                
                if (move_uploaded_file($tmp_name, $destination)) {
                    $typeDoc = 'autre';
                    if (preg_match('/kbis/i', $originalName)) $typeDoc = 'kbis';
                    elseif (preg_match('/cin|identité/i', $originalName)) $typeDoc = 'cin';
                    
                    $sqlDoc = "INSERT INTO documents_legaux 
                              (evenement_id, nom_fichier, chemin_fichier, type_document) 
                              VALUES (?, ?, ?, ?)";
                    $stmtDoc = $cnx->prepare($sqlDoc);
                    $stmtDoc->execute([
                        $evenement_id,
                        $originalName,
                        'uploads/legal-docs/' . $newName,
                        $typeDoc
                    ]);
                }
            }
        }
    }

    // Traitement des types de tickets
    if (isset($_POST['ticket-name']) && is_array($_POST['ticket-name'])) {
        foreach ($_POST['ticket-name'] as $index => $ticketName) {
            $ticketPrix = (float)$_POST['ticket-price'][$index];
            $ticketNb = (int)$_POST['ticket-quantity'][$index];
            
            if ($ticketPrix >= 0 && $ticketNb > 0) {
                $sqlTicket = "INSERT INTO ticket 
                    (evenement_id, type, prix, date_validite, nb_ticket) 
                    VALUES (?, ?, ?, ?, ?)";
                
                $stmtTicket = $cnx->prepare($sqlTicket);
                $stmtTicket->execute([
                    $evenement_id,
                    htmlspecialchars($ticketName),
                    $ticketPrix,
                    $date_fin,
                    $ticketNb
                ]);
            }
        }
    } else {
        // Si aucun ticket n'a été spécifié, créer un ticket standard gratuit
        $sqlTicket = "INSERT INTO ticket 
            (evenement_id, type, prix, date_validite, nb_ticket) 
            VALUES (?, 'Standard', 0, ?, ?)";
        
        $stmtTicket = $cnx->prepare($sqlTicket);
        $stmtTicket->execute([
            $evenement_id,
            $date_fin,
            $nb_place
        ]);
    }

    // Redirection vers une page de confirmation
    header("Location: ../view/f.php");
    exit();
} else {
    // Si la méthode n'est pas POST, rediriger vers la page du formulaire
    header("Location: ../view/f.php");
    exit();
}