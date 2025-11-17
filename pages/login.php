<?php
/**
 * PAGE DE CONNEXION - pages/login.php
 * Permet aux utilisateurs de se connecter au site e-commerce
 */

session_start();
define('SITE_ACCESS', true);

// Inclusion des fichiers essentiels
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../functions/user-functions.php';

// Redirection si l'utilisateur est déjà connecté
if (is_logged_in()) {
    header("Location: ../index.php");
    exit();
}

// Variables pour les messages
$error = '';
$success = '';

// Traitement du formulaire de connexion
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = isset($_POST['username']) ? clean_input($_POST['username']) : '';
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = "Veuillez remplir tous les champs.";
    } else {
        if (login_user($username, $password)) {
            // Redirection après connexion
            $redirect = $_GET['redirect'] ?? '../index.php';
            $allowed_redirects = ['cart', 'checkout', 'profile', 'orders'];

            if (in_array($redirect, $allowed_redirects)) {
                $redirect = '../pages/' . basename($redirect) . '.php';
            } else {
                $redirect = '../index.php';
            }

            $_SESSION['message'] = "Conn réussie ! Bienvenue " . ($_SESSION['first_name'] ?? '') . " !";
            $_SESSION['message_type'] = "success";

            header("Location: $redirect");
            exit();
        } else {
            $error = "Nom d'utilisateur ou mot de passe incorrect.";
        }
    }
}

// Titre de la page
$page_title = "Conn";
?>

<!DOCTYPE html>

<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?> - E-Commerce</title>

<?require_once __DIR__ .'/../includes/footer.php'; ?>

<!-- Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">

<!-- CSS personnalisé -->
<link href="../css/style.css" rel="stylesheet">

<style>
    /* STYLES SPÉCIFIQUES À LA PAGE DE CONNEXION */
    
    body {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        min-height: 100vh;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    
    .login-container {
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 2rem 1rem;
    }
    
    .login-card {
        background: white;
        border-radius: 20px;
        box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        overflow: hidden;
        max-width: 400px;
        width: 100%;
        animation: slideIn 0.6s ease-out;
    }
    
    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .login-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 2rem;
        text-align: center;
    }
    
    .login-header h2 {
        margin: 0;
        font-weight: bold;
    }
    
    .login-header p {
        margin: 0.5rem 0 0 0;
        opacity: 0.9;
    }
    
    .login-body {
        padding: 2rem;
    }
    
    .form-control {
        border-radius: 10px;
        border: 2px solid #e9ecef;
        padding: 0.75rem 1rem;
        font-size: 1rem;
        transition: all 0.3s ease;
    }
    
    .form-control:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        transform: translateY(-2px);
    }
    
    .form-label {
        font-weight: 600;
        color: #495057;
        margin-bottom: 0.5rem;
    }
    
    .btn-login {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        border-radius: 10px;
        padding: 0.75rem 2rem;
        font-weight: bold;
        font-size: 1.1rem;
        color: white;
        width: 100%;
        transition: all 0.3s ease;
    }
    
    .btn-login:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        color: white;
    }
    
    .btn-login:active {
        transform: translateY(0);
    }
    
    .alert {
        border-radius: 10px;
        border: none;
        font-weight: 500;
    }
    
    .alert-danger {
        background: #ffe6e6;
        color: #d63384;
    }
    
    .alert-success {
        background: #e6ffe6;
        color: #28a745;
    }
    
    .divider {
        text-align: center;
        margin: 1.5rem 0;
        position: relative;
    }
    
    .divider::before {
        content: '';
        position: absolute;
        top: 50%;
        left: 0;
        right: 0;
        height: 1px;
        background: #dee2e6;
    }
    
    .divider span {
        background: white;
        padding: 0 1rem;
        color: #6c757d;
        font-size: 0.9rem;
    }
    
    .links-section {
        text-align: center;
        margin-top: 1.5rem;
    }
    
    .links-section a {
        color: #667eea;
        text-decoration: none;
        font-weight: 500;
        transition: color 0.3s ease;
    }
    
    .links-section a:hover {
        color: #5a67d8;
        text-decoration: underline;
    }
    
    .back-home {
        position: fixed;
        top: 20px;
        left: 20px;
        background: rgba(255,255,255,0.2);
        color: white;
        border: 2px solid rgba(255,255,255,0.3);
        border-radius: 50px;
        padding: 0.5rem 1rem;
        text-decoration: none;
        font-weight: 500;
        transition: all 0.3s ease;
        backdrop-filter: blur(10px);
    }
    
    .back-home:hover {
        background: rgba(255,255,255,0.3);
        color: white;
        transform: translateX(-5px);
    }
    
    .demo-info {
        background: #f8f9fa;
        border-radius: 10px;
        padding: 1rem;
        margin-top: 1rem;
        border-left: 4px solid #17a2b8;
    }
    
    .demo-info h6 {
        color: #17a2b8;
        margin: 0 0 0.5rem 0;
    }
    
    .demo-info small {
        color: #6c757d;
    }
    
    /* Animations de chargement */
    .loading {
        position: relative;
        pointer-events: none;
    }
    
    .loading::after {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        width: 20px;
        height: 20px;
        margin: -10px 0 0 -10px;
        border: 2px solid transparent;
        border-top: 2px solid white;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }
    
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    
    /* Responsive */
    @media (max-width: 576px) {
        .login-card {
            margin: 1rem;
            border-radius: 15px;
        }
        
        .login-header {
            padding: 1.5rem;
        }
        
        .login-body {
            padding: 1.5rem;
        }
    }
</style>

</head>

<body>
    <!-- LIEN RETOUR ACCUEIL -->
    <a href="../index.php" class="back-home">
        <i class="bi bi-arrow-left me-2"></i>Retour à l'accueil
    </a>


<div class="login-container">
    <div class="login-card">
        
        <!-- EN-TÊTE DE LA CARTE -->
        <div class="login-header">
            <h2>
                <i class="bi bi-person-circle me-2"></i>
                Connexion
            </h2>
            <p>Accédez à votre compte</p>
        </div>
        
        <!-- CORPS DE LA CARTE -->
        <div class="login-body">
            
            <!-- MESSAGES D'ERREUR OU SUCCÈS -->
            <?php if ($error): ?>
                <div class="alert alert-danger d-flex align-items-center" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success d-flex align-items-center" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    <?= htmlspecialchars($success) ?>
                </div>
            <?php endif; ?>
            
            <!-- FORMULAIRE DE CONNEXION -->
            <form method="post" id="loginForm" novalidate>
                
                <!-- CHAMP NOM D'UTILISATEUR / EMAIL -->
                <div class="mb-3">
                    <label for="username" class="form-label">
                        <i class="bi bi-person me-2"></i>
                        Nom d'utilisateur ou Email
                    </label>
                    <input type="text" 
                           class="form-control" 
                           id="username" 
                           name="username" 
                           value="<?= isset($username) ? htmlspecialchars($username) : '' ?>"
                           placeholder="Entrez votre nom d'utilisateur ou email"
                           required
                           autocomplete="username">
                    <div class="invalid-feedback">
                        Veuillez entrer votre nom d'utilisateur ou email.
                    </div>
                </div>
                
                <!-- CHAMP MOT DE PASSE -->
                <div class="mb-4">
                    <label for="password" class="form-label">
                        <i class="bi bi-lock me-2"></i>
                        Mot de passe
                    </label>
                    <div class="input-group">
                        <input type="password" 
                               class="form-control" 
                               id="password" 
                               name="password" 
                               placeholder="Entrez votre mot de passe"
                               required
                               autocomplete="current-password">
                        <button type="button" 
                                class="btn btn-outline-secondary" 
                                id="togglePassword"
                                title="Afficher/Masquer le mot de passe">
                            <i class="bi bi-eye" id="eyeIcon"></i>
                        </button>
                    </div>
                    <div class="invalid-feedback">
                        Veuillez entrer votre mot de passe.
                    </div>
                </div>
                
                <!-- BOUTON DE CONNEXION -->
                <button type="submit" class="btn btn-login" id="submitBtn">
                    <i class="bi bi-box-arrow-in-right me-2"></i>
                    Se connecter
                </button>
            </form>
            
            <!-- SÉPARATEUR -->
            <div class="divider">
                <span>Nouveau sur notre site ?</span>
            </div>
            
            <!-- LIENS VERS AUTRES PAGES -->
            <div class="links-section">
                <a href="register.php" class="btn btn-outline-primary w-100 mb-2">
                    <i class="bi bi-person-plus me-2"></i>
                    Créer un compte
                </a>
                
                <div class="mt-3">
                    <a href="#">Mot de passe oublié ?</a>
                </div>
            </div>
            
            <!-- INFORMATIONS DE DÉMONSTRATION -->
            <div class="demo-info">
                <h6>
                    <i class="bi bi-info-circle me-2"></i>
                    Compte de démonstration
                </h6>
                <small>
         <strong>Administrateur :</strong> admin / admin123<br>
          <strong>Utilisateur test :</strong> user / user123
                </small>
            </div>
        </div>
    </div>
</div>

<!-- SCRIPTS JAVASCRIPT -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        
        // RÉFÉRENCES AUX ÉLÉMENTS
        const form = document.getElementById('loginForm');
        const submitBtn = document.getElementById('submitBtn');
        const passwordField = document.getElementById('password');
        const togglePasswordBtn = document.getElementById('togglePassword');
        const eyeIcon = document.getElementById('eyeIcon');
        
        // VALIDATION DU FORMULAIRE
        form.addEventListener('submit', function(event) {
            event.preventDefault();
            event.stopPropagation();
            
            // Validation Bootstrap
            if (form.checkValidity()) {
                // Animation de chargement
                submitBtn.classList.add('loading');
                submitBtn.innerHTML = '<span class="me-2">Connexion en cours...</span>';
                submitBtn.disabled = true;
                
                // Soumettre le formulaire après un court délai (pour l'animation)
                setTimeout(function() {
                    form.submit();
                }, 500);
            }
            
            form.classList.add('was-validated');
        });
        
        // AFFICHAGE/MASQUAGE DU MOT DE PASSE
        togglePasswordBtn.addEventListener('click', function() {
            const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordField.setAttribute('type', type);
            
            // Changer l'icône
            if (type === 'text') {
                eyeIcon.classList.remove('bi-eye');
                eyeIcon.classList.add('bi-eye-slash');
            } else {
                eyeIcon.classList.remove('bi-eye-slash');
                eyeIcon.classList.add('bi-eye');
            }
        });
        
        // AUTO-FOCUS SUR LE PREMIER CHAMP
        document.getElementById('username').focus();
        
        // ANIMATION DES CHAMPS AU FOCUS
        const inputs = document.querySelectorAll('.form-control');
        inputs.forEach(input => {
            input.addEventListener('focus', function() {
                this.parentNode.classList.add('focused');
            });
            
            input.addEventListener('blur', function() {
                if (this.value === '') {
                    this.parentNode.classList.remove('focused');
                }
            });
        });
        
        // RACCOURCI CLAVIER POUR LA DÉMONSTRATION
        document.addEventListener('keydown', function(event) {
            // Ctrl + D pour remplir avec les données de démo
            if (event.ctrlKey && event.key === 'd') {
                event.preventDefault();
                document.getElementById('username').value = 'admin';
                document.getElementById('password').value = 'admin123';
            }
        });
        
        // GESTION DES ERREURS DE RÉSEAU
        window.addEventListener('beforeunload', function() {
            if (submitBtn.classList.contains('loading')) {
                return 'La connexion est en cours...';
            }
        });
    });
    
    // FONCTION POUR MASQUER LES ALERTES APRÈS DÉLAI
    setTimeout(function() {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 300);
        });
    }, 5000);
</script>


</body>
</html>