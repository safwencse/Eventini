<?php
session_start();
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
if(isset($_SESSION['email']) && !empty($_SESSION['email'])){
    header("Location: profile.php");
    exit;
}
?>   
<?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger">
        <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
    </div>
<?php endif; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eventini | Sign Up / Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assests/css/style_signup.css">
    <style>
        .error-message {
            color: #dc3545;
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }
        .is-invalid {
            border-color: #dc3545 !important;
        }
        .invalid-feedback {
            display: none;
            color: #dc3545;
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }
      
    </style>
</head>
<body>
    <!-- Animated Background -->
    <div class="bg-elements">
        <div class="bg-shape circle" style="--size: 120px; --color: rgba(16,101,171,0.1); --left: 15%; --top: 20%; --delay: 0s;"></div>
        <div class="bg-shape triangle" style="--size: 80px; --color: rgba(255,89,0,0.1); --left: 85%; --top: 30%; --delay: 2s;"></div>
        <div class="floating-icon" style="--left: 10%; --top: 15%; --delay: 1s;">🎤</div>
        <div class="floating-icon" style="--left: 90%; --top: 25%; --delay: 1s;">🎭</div>
        <div class="floating-icon" style="--left: 20%; --top: 70%; --delay: 1s;">🎪</div>
        <div class="floating-icon" style="--left: 80%; --top: 60%; --delay: 1s;">🎟️</div>
        <div class="floating-icon" style="--left: 50%; --top: 80%; --delay: 1s;">🎨</div>
        <div class="floating-icon" style="--left: 30%; --top: 40%; --delay: 1s;">🎧</div>
        <div class="floating-icon" style="--left: 70%; --top: 50%; --delay: 1s;">🎷</div>
    </div>

    <div class="container">
        <!-- Brand Header -->
        <header class="brand-header">
            <div class="logo-wrapper">
                <h1 class="logo animate__animated animate__fadeInDown">EVENTINI</h1>
                <div class="logo-highlight"></div>
            </div>
            <p class="tagline animate__animated animate__fadeIn animate__delay-1s">Where events come to life</p>
        </header>
        
        <!-- Form Section -->
        <div class="form-wrapper">
            <div class="form-holder">
                <!-- Signup Form -->
                <div class="form-container signup-form active">
                    <form id="registerForm" action="../config/register.php" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        <div id="general-error" class="alert alert-danger mt-3" style="display: none;"></div>
                        
                        <div class="form-group floating">
                            <span class="input-icon"><i class="far fa-user"></i></span>
                            <input type="text" class="form-control" id="nom" name="nom" placeholder=" " required>
                            <label for="nom">Nom</label>
                            <div id="nom-error" class="invalid-feedback"></div>
                        </div>
                        <div class="form-group floating">
                            <span class="input-icon"><i class="far fa-user"></i></span>
                            <input type="text" class="form-control" id="prenom" name="prenom" placeholder=" " required>
                            <label for="prenom">Prénom</label>
                            <div id="prenom-error" class="invalid-feedback"></div>
                        </div>
                        <div class="form-group floating">
                            <span class="input-icon"><i class="far fa-envelope"></i></span>
                            <input type="email" class="form-control" id="email" name="email" placeholder=" " required>
                            <label for="email">Email</label>
                            <div id="email-error" class="invalid-feedback"></div>
                        </div>
                        <div class="form-group floating">
                            <span class="input-icon"><i class="fas fa-venus-mars"></i></span>
                            <select class="form-control" id="sexe" name="sexe" required>
                                <option value="" disabled selected></option>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                                <option value="other">Other</option>
                            </select>
                            <label for="sexe">Sexe</label>
                            <div id="sexe-error" class="invalid-feedback"></div>
                        </div>
                        <div class="form-group floating">
                            <span class="input-icon"><i class="far fa-calendar-alt"></i></span>
                            <input type="date" class="form-control" id="date_de_naissance" name="date_de_naissance" placeholder=" " required>
                            <label for="date_de_naissance">Date de Naissance</label>
                            <div id="date_de_naissance-error" class="invalid-feedback"></div>
                        </div>
                        <div class="form-group floating">
                            <span class="input-icon"><i class="fas fa-mobile-alt"></i></span>
                            <input type="tel" class="form-control" id="telephone" name="telephone" placeholder=" " required>
                            <label for="telephone">Téléphone</label>
                            <div id="telephone-error" class="invalid-feedback"></div>
                        </div>
                        <div class="form-group floating password-group">
                            <span class="input-icon"><i class="fas fa-lock"></i></span>
                            <input type="password" class="form-control" id="mot_de_passe" name="mot_de_passe" placeholder=" " required>
                            <label for="mot_de_passe">Mot de passe</label>
                            <button type="button" class="toggle-password">
                                <i class="far fa-eye"></i>
                            </button>
                            <div id="mot_de_passe-error" class="invalid-feedback"></div>
                        </div>
                        <div class="form-group floating">
                            <span class="input-icon"><i class="far fa-image"></i></span>
                            <input type="file" class="form-control" id="profile_image" name="profile_image" placeholder=" " accept="image/*">
                            <label for="profile_image">Profile Image (Optional)</label>
                        </div>
                        <div class="form-group terms-group">
                            <input type="checkbox" id="terms" name="terms" required>
                            <label for="terms">I agree to all <a href="#">terms and conditions</a></label>
                            <div id="terms-error" class="invalid-feedback"></div>
                        </div>
                        <button type="submit" class="btn btn-primary btn-signup">Sign Up</button>
                    </form>
                    <div class="form-footer">
                        <button class="btn-switch" id="switch-to-login">Already have an account? <strong>Login</strong></button>
                    </div>
                </div>

 <!-- Login Form -->
 <div class="form-container login-form"  method="POST" action="../config/login.php">
                    <form method="POST" action="../config/login.php">
                        <div class="form-group floating">
                            <input type="email" name="email-login" class="form-control" id="loginEmail" placeholder=" " required>
                            <label for="loginEmail">Email</label>
                            <span class="input-icon"><i class="far fa-envelope"></i></span>
                        </div>
                        <div class="form-group floating password-group">
                            <input type="password" name="password-login" class="form-control" id="loginPassword" placeholder=" " required>
                            <label for="loginPassword">Password</label>
                            <span class="input-icon"><i class="fas fa-lock"></i></span>
                            <button type="button" class="toggle-password">
                                <i class="far fa-eye"></i>
                            </button>
                        </div>
                        <button type="submit" class="btn btn-primary btn-login">Login</button>
                    </form>
                    <div class="form-footer">
                        <button class="btn-switch" id="switch-to-signup">Don't have an account? <strong>Sign Up</strong></button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>
    <script src="../assests/js/script_signup.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const params = new URLSearchParams(window.location.search);
            if (params.get("show") === "login") {
                const loginButton = document.getElementById("switch-to-login");
                if (loginButton) {
                    loginButton.click();
                } else {
                    console.warn("L'élément #switch-to-login n'a pas été trouvé !");
                }
            }
        });
    </script>
    </body>
</body>
</html> 