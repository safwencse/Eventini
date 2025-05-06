<?php

require_once 'connect.php';
function getAllEvents($cnx, $page = 1, $perPage = 8, $filters = [], $sortBy = 'date-asc') {
    try {
        $offset = ($page - 1) * $perPage;
        
        $baseQuery = "SELECT *,
               (SELECT t.prix 
                FROM ticket t  
                WHERE evenement.evenement_id = t.evenement_id 
                ORDER BY t.prix ASC
                LIMIT 1) AS prix 
               FROM evenement 
               WHERE confirmation LIKE :confirmation";
        
        // Add filters
        $whereClauses = [];
        $params = [':confirmation' => '%OUI%'];
        
        // Category filter
        if (!empty($filters['category']) && $filters['category'] !== 'all') {
            $whereClauses[] = "categorie = :category";
            $params[':category'] = $filters['category'];
        }
        
        // Price filter
        if (!empty($filters['price'])) {
            switch($filters['price']) {
                case '0': // Gratuit
                    $whereClauses[] = "(SELECT MIN(t.prix) FROM ticket t WHERE t.evenement_id = evenement.evenement_id) = 0";
                    break;
                case '25': // 25 DT
                    $whereClauses[] = "(SELECT MIN(t.prix) FROM ticket t WHERE t.evenement_id = evenement.evenement_id) <= 25";
                    $whereClauses[] = "(SELECT MIN(t.prix) FROM ticket t WHERE t.evenement_id = evenement.evenement_id) > 0";
                    break;
                case '100': // 100 DT
                    $whereClauses[] = "(SELECT MIN(t.prix) FROM ticket t WHERE t.evenement_id = evenement.evenement_id) > 25";
                    break;
            }
        }
        
        // Build final query
        if (!empty($whereClauses)) {
            $baseQuery .= " AND " . implode(" AND ", $whereClauses);
        }
        
        // Add sorting
        switch($sortBy) {
            case 'date-asc':
                $baseQuery .= " ORDER BY date_debut ASC";
                break;
            case 'date-desc':
                $baseQuery .= " ORDER BY date_debut DESC";
                break;
            case 'price-asc':
                $baseQuery .= " ORDER BY prix ASC";
                break;
            case 'price-desc':
                $baseQuery .= " ORDER BY prix DESC";
                break;
            default:
                $baseQuery .= " ORDER BY date_debut ASC";
        }
        
        $baseQuery .= " LIMIT :limit OFFSET :offset";
        
        $stmt = $cnx->prepare($baseQuery);
        
        // Bind all parameters
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error fetching events: " . $e->getMessage());
        return [];
    }
}

function countAllEvents($cnx, $filters = []) {
    try {
        $baseQuery = "SELECT COUNT(*) as total FROM evenement WHERE confirmation LIKE :confirmation";
        
        $whereClauses = [];
        $params = [':confirmation' => '%OUI%'];
        
        // Add the same filters as in getAllEvents
        if (!empty($filters['category']) && $filters['category'] !== 'all') {
            $whereClauses[] = "categorie = :category";
            $params[':category'] = $filters['category'];
        }
        
        if (!empty($filters['price'])) {
            switch($filters['price']) {
                case '0':
                    $whereClauses[] = "(SELECT MIN(t.prix) FROM ticket t WHERE t.evenement_id = evenement.evenement_id) = 0";
                    break;
                case '25':
                    $whereClauses[] = "(SELECT MIN(t.prix) FROM ticket t WHERE t.evenement_id = evenement.evenement_id) <= 25";
                    $whereClauses[] = "(SELECT MIN(t.prix) FROM ticket t WHERE t.evenement_id = evenement.evenement_id) > 0";
                    break;
                case '100':
                    $whereClauses[] = "(SELECT MIN(t.prix) FROM ticket t WHERE t.evenement_id = evenement.evenement_id) > 25";
                    break;
            }
        }
        
        if (!empty($whereClauses)) {
            $baseQuery .= " AND " . implode(" AND ", $whereClauses);
        }
        
        $stmt = $cnx->prepare($baseQuery);
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    } catch (PDOException $e) {
        error_log("Error counting events: " . $e->getMessage());
        return 0;
    }
}
function getAllEventsca($cnx) {
    try {
        $req = "SELECT nom, image_evenement, categorie, date_debut, lieu ,date_fin
                FROM evenement 
                WHERE confirmation = :confirmation and type!='privé'
                ORDER BY evenement_id  DESC 
                LIMIT 8";
        $stmt = $cnx->prepare($req);
        $stmt->execute([':confirmation' => 'OUI']);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error fetching events: " . $e->getMessage());
        return [];
    }
}
function getDetailsEvenement($cnx, $evenement_id) {
    $req = "SELECT nom, description, date_debut, date_fin, lieu, categorie, image_evenement, nb_like, type, mdp_prive 
            FROM evenement 
            WHERE evenement_id = " . intval($evenement_id);
    
    $res = $cnx->query($req);
    return $res->fetch();
}

function getDetailsTicket($cnx, $evenement_id) {
    $req = "SELECT type, prix, date_validite, nb_ticket, description_ticket 
            FROM ticket 
            WHERE evenement_id = " . intval($evenement_id);
    
    $res = $cnx->query($req);
    return $res->fetchAll(); // Just return ticket data (No Stripe logic here)
}
function getUtilisateurById($cnx, $utilisateur_id) {
    $query = "SELECT * FROM utilisateur WHERE utilisateur_id = :id";
    $stmt = $cnx->prepare($query);
    $stmt->bindParam(':id', $utilisateur_id);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function updateUtilisateur($cnx, $data, $utilisateur_id) {
    try {
        // Vérification du mot de passe
        if (!empty($data['current_mdp'])) {
            $utilisateur = getUtilisateurById($cnx, $utilisateur_id);
            
            if (!password_verify($data['current_mdp'], $utilisateur['mot_de_passe'])) {
                throw new Exception("Mot de passe actuel incorrect");
            }
            
            if (empty($data['new_mdp'])) {
                throw new Exception("Nouveau mot de passe requis");
            }
            
            if ($data['new_mdp'] !== $data['confirm_mdp']) {
                throw new Exception("Les mots de passe ne correspondent pas");
            }
            
            $hashed_password = password_hash($data['new_mdp'], PASSWORD_DEFAULT);
            $query = "UPDATE utilisateur SET mot_de_passe = :mdp WHERE utilisateur_id = :id";
            $stmt = $cnx->prepare($query);
            $stmt->bindParam(':mdp', $hashed_password);
            $stmt->bindParam(':id', $utilisateur_id);
            $stmt->execute();
        }

        // Gestion de l'image de profil
        $image_name = null;
        if (!empty($_FILES['image_utilisateur']['name'])) {
            $target_dir = "../uploads/profiles/";
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            
            // Supprimer l'ancienne image si elle existe
            $old_image = getUtilisateurById($cnx, $utilisateur_id)['image_utilisateur'];
            if ($old_image && file_exists($target_dir.$old_image)) {
                unlink($target_dir.$old_image);
            }
            
            $imageFileType = strtolower(pathinfo($_FILES["image_utilisateur"]["name"], PATHINFO_EXTENSION));
            $image_name = "profile_".$utilisateur_id."_".time().".".$imageFileType;
            $target_file = $target_dir . $image_name;
            
            // Validation de l'image
            $check = getimagesize($_FILES["image_utilisateur"]["tmp_name"]);
            if($check === false) throw new Exception("Fichier non valide");
            if ($_FILES["image_utilisateur"]["size"] > 5000000) throw new Exception("Fichier trop volumineux");
            if(!in_array($imageFileType, ['jpg','png','jpeg','gif'])) throw new Exception("Format non supporté");
            
            if (!move_uploaded_file($_FILES["image_utilisateur"]["tmp_name"], $target_file)) {
                throw new Exception("Erreur lors de l'upload");
            }
        }

        // Mise à jour des informations
        $query = "UPDATE utilisateur SET 
                 nom = :nom,
                 prenom = :prenom,
                 email = :email,
                 telephone = :telephone,
                 sexe = :sexe,
                 date_de_naissance = :date_naissance"
                 .($image_name ? ", image_utilisateur = :image" : "")."
                 WHERE utilisateur_id = :id";
        
        $stmt = $cnx->prepare($query);
        $params = [
            ':nom' => $data['nom'],
            ':prenom' => $data['prenom'],
            ':email' => $data['email'],
            ':telephone' => $data['telephone'],
            ':sexe' => $data['sexe'],
            ':date_naissance' => $data['date_naissance'],
            ':id' => $utilisateur_id
        ];
        
        if ($image_name) {
            $params[':image'] = $image_name;
        }
        
        $stmt->execute($params);
        
        return "Mise à jour réussie";
    } catch (Exception $e) {
        return $e->getMessage();
    }
}
function getEvenementsCrees($cnx, $organisateur_id) {
    $query = "SELECT * FROM evenement WHERE organisateur_id = :id";
    $stmt = $cnx->prepare($query);
    $stmt->bindParam(':id', $organisateur_id);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getEvenementsParticipes($cnx, $utilisateur_id) {
    $query = "SELECT e.* FROM evenement e 
              JOIN utilisateur_evenement ue ON e.evenement_id = ue.evenement_id
              WHERE ue.utilisateur_id = :id";
    $stmt = $cnx->prepare($query);
    $stmt->bindParam(':id', $utilisateur_id);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
function getEvenementsEnAttente($cnx) {
    $query = "SELECT * FROM evenement WHERE Confirmation = 'non'";
    $stmt = $cnx->prepare($query);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>