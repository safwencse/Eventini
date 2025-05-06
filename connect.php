<?php
// Configuration de la base de données
$db_server = "127.0.0.1";
$db_username = "root";
$db_pwd = "";
$db_name = "eventini";

try {
    // Options de configuration PDO
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // Active le mode erreur pour les exceptions
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // Retourne les résultats sous forme de tableau associatif
        PDO::ATTR_EMULATE_PREPARES => false, // Désactive l'émulation des requêtes préparées
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4" // Définit l'encodage UTF-8
    ];

    // Création de la connexion PDO
    $cnx = new PDO(
        "mysql:host=$db_server;dbname=$db_name;charset=utf8mb4",
        $db_username,
        $db_pwd,
        $options
    );
    
    // Définir $pdo comme alias de $cnx pour compatibilité avec votre code existant
    $pdo = $cnx;

} catch (PDOException $e) {
    // Journalisation de l'erreur et affichage d'un message générique
    error_log("Erreur de connexion à la base de données: " . $e->getMessage());
    die("Une erreur est survenue lors de la connexion à la base de données. Veuillez réessayer plus tard.");
}
?>