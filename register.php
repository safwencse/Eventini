<?php
header('Content-Type: application/json');
session_start();

if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid CSRF token',
        'errors' => ['general' => 'Invalid CSRF token']
    ]);
    exit;
}

require_once 'connect.php';

$response = [
    'success' => false,
    'message' => '',
    'errors' => [],
    'redirect' => ''
];

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Invalid request method");
    }

    // Sanitize inputs
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $sexe = $_POST['sexe'] ?? '';
    $date_de_naissance = $_POST['date_de_naissance'] ?? '';
    $telephone = trim($_POST['telephone'] ?? '');
    $mot_de_passe = $_POST['mot_de_passe'] ?? '';
    $terms = isset($_POST['terms']);

    // Validate fields
    if (empty($nom)) $response['errors']['nom'] = "Nom is required";
    if (empty($prenom)) $response['errors']['prenom'] = "Prénom is required";
    
    if (empty($email)) {
        $response['errors']['email'] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response['errors']['email'] = "Invalid email format";
    }
    
    if (empty($sexe)) $response['errors']['sexe'] = "Gender is required";
    
    if (empty($date_de_naissance)) {
        $response['errors']['date_de_naissance'] = "Birth date is required";
    } else {
        $today = new DateTime();
        $birthdate = new DateTime($date_de_naissance);
        $age = $today->diff($birthdate)->y;
        
        if ($age < 13) {
            $response['errors']['date_de_naissance'] = "You must be at least 13 years old";
        }
    }
    
    if (empty($telephone)) {
        $response['errors']['telephone'] = "Phone is required";
    } 
    if (empty($mot_de_passe)) {
        $response['errors']['mot_de_passe'] = "Password is required";
    } elseif (strlen($mot_de_passe) < 8) {
        $response['errors']['mot_de_passe'] = "Password must be at least 8 characters";
    } elseif (!preg_match('/[A-Z]/', $mot_de_passe) || !preg_match('/[a-z]/', $mot_de_passe) || !preg_match('/[0-9]/', $mot_de_passe)) {
        $response['errors']['mot_de_passe'] = "Password must contain uppercase, lowercase and numbers";
    }
    
    if (!$terms) $response['errors']['terms'] = "You must accept the terms";

    // Check for duplicate email/phone only if no validation errors
    if (empty($response['errors'])) {
        $stmt = $pdo->prepare("SELECT email FROM utilisateur WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->rowCount() > 0) $response['errors']['email'] = "Email already registered";

        $stmt = $pdo->prepare("SELECT telephone FROM utilisateur WHERE telephone = ?");
        $stmt->execute([$telephone]);
        if ($stmt->rowCount() > 0) $response['errors']['telephone'] = "Phone number already registered";
    }

    // If errors, return them
    if (!empty($response['errors'])) {
        $response['message'] = 'Please correct the errors below';
        echo json_encode($response);
        exit;
    }

    // Hash password
    $mot_de_passe_hash = password_hash($mot_de_passe, PASSWORD_DEFAULT);

    // Handle file upload
    $profile_image_path = null;
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $fileType = mime_content_type($_FILES['profile_image']['tmp_name']);
        
        if (!in_array($fileType, $allowedTypes)) {
            $response['errors']['profile_image'] = "Only JPG, PNG, and GIF files are allowed";
            echo json_encode($response);
            exit;
        }
        
        $uploadDir = 'uploads/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $ext = pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '.' . $ext;
        $targetPath = $uploadDir . $filename;

        if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $targetPath)) {
            $profile_image_path = $targetPath;
        }
    }

    // Insert into database
    $stmt = $pdo->prepare("INSERT INTO utilisateur (nom, prenom, email, mot_de_passe,role, sexe, date_de_naissance, telephone, image_utilisateur) VALUES (?, ?, ?, ?,'utilisateur', ?, ?, ?, ?)");
    $stmt->execute([$nom, $prenom, $email, $mot_de_passe_hash, $sexe, $date_de_naissance, $telephone, $profile_image_path]);

    // Start session
    $_SESSION['user'] = [
        'id' => $pdo->lastInsertId(),
        'nom' => $nom,
        'prenom' => $prenom,
        'email' => $email
    ];

    // Success response
    $response['success'] = true;
    $response['message'] = 'Registration successful!';
    $response['redirect'] = 'profile.php';

} catch (Exception $e) {
    $response['message'] = 'An error occurred during registration';
    error_log('Registration error: ' . $e->getMessage());
}

echo json_encode($response);
?>