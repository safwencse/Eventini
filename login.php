<?php
require_once 'connect.php';
session_start();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = "Méthode de requête invalide";
    header("Location: ../view/signup.php?show=login");
    exit();
}

try {
    $email = filter_input(INPUT_POST, 'email-login', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password-login'] ?? '';

    // Check for empty fields
    if (empty($email) || empty($password)) {
        $_SESSION['error'] = "Veuillez remplir tous les champs";
        header("Location: ../view/signup.php?show=login");
        exit();
    }

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "Format d'email invalide";
        header("Location: ../view/signup.php?show=login");
        exit();
    }

    // Corrected database query using prepared statements
    $req = "SELECT * FROM utilisateur WHERE email = :email";
    $stmt = $cnx->prepare($req);
    $stmt->bindParam(':email', $email, PDO::PARAM_STR);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Verify user exists and password is correct
    if (!$user) {
        $_SESSION['error'] = "Aucun compte trouvé avec cet email";
        header("Location: ../view/signup.php?show=login");
        exit();
    }

    if (!password_verify($password, $user['mot_de_passe'])) {
        $_SESSION['error'] = "Mot de passe incorrect";
        header("Location: ../view/signup.php?show=login");
        exit();
    }

    // Set session variables
    $_SESSION['user_id'] = $user['utilisateur_id'];
    $_SESSION['nom'] = $user['nom'];
    $_SESSION['prenom'] = $user['prenom'];
    $_SESSION['image_utilisateur'] = $user['image_utilisateur'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['logged_in'] = true;
    
    // Regenerate session ID for security
    session_regenerate_id(true);

    // Redirect to profile page
    header("Location: ../view/profile.php");
    exit();

} catch (PDOException $e) {
    error_log("Database error in login.php: " . $e->getMessage());
    $_SESSION['error'] = "Une erreur technique est survenue";
    header("Location: ../view/signup.php?show=login");
    exit();
} catch (Exception $e) {
    error_log("General error in login.php: " . $e->getMessage());
    $_SESSION['error'] = "Une erreur inattendue est survenue";
    header("Location: ../view/signup.php?show=login");
    exit();
}
?>