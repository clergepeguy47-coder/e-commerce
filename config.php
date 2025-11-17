<?php
/**
 * Fichier de configuration principal du site e-commerce
 * Contient tous les paramètres de base de données, sécurité et site
 */

// ==========================================
// CONFIGURATION BASE DE DONNÉES
// ==========================================
define('DB_HOST', 'localhost');                    // Serveur de base de données
define('DB_USERNAME', 'root');                     // Nom d'utilisateur MySQL (défaut WAMP)
define('DB_PASSWORD', '');                         // Mot de passe MySQL (vide par défaut WAMP)
define('DB_NAME', 'ecommerce_db');                 // Nom de la base de données
define('DB_CHARSET', 'utf8mb4');                   // Encodage de la base de données
define('DEBUG_MODE', true);

//CONNECTION 
$conn= mysqli_connect (DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);
// veriffier la connection à la base de donnees 
if(!$conn) {
die("erreur de connection MYSQL:".mysqli_connect_error());
}
// ==========================================
// CONFIGURATION DU SITE
// ==========================================
define('SITE_NAME', 'Mon E-commerce');             // Nom du site
define('SITE_URL', 'http://localhost/ecommerce-site/'); // URL de base du site
define('SITE_EMAIL', 'kalanbe47@gmail.com'); // Email de contact
define('ADMIN_EMAIL', 'clergepeguy47@gmail.com');  // Email administrateur

// ==========================================
// CHEMINS ET RÉPERTOIRES
// ==========================================
define('ROOT_PATH', dirname(__FILE__) . '/');      // Chemin racine du projet
define('UPLOAD_PATH', 'images/');                  // Dossier principal des images
define('PRODUCTS_PATH', 'images/products/');       // Dossier images produits
define('CATEGORIES_PATH', 'images/categories/');   // Dossier images catégories
define('DEFAULT_IMAGE', 'images/no-image.webp');    // Image par défaut

// ==========================================
// CONFIGURATION SÉCURITÉ
// ==========================================
define('SESSION_TIMEOUT', 3600);                   // Durée de session (1 heure)
define('ADMIN_SESSION_TIMEOUT', 1800);             // Durée session admin (30 min)
define('MAX_LOGIN_ATTEMPTS', 5);                   // Tentatives de connexion max
define('LOGIN_LOCKOUT_TIME', 900);                 // Temps de blocage (15 min)
define('PASSWORD_MIN_LENGTH', 6);                  // Longueur min mot de passe
define('CSRF_TOKEN_EXPIRE', 3600);                 // Expiration token CSRF

// ==========================================
// CONFIGURATION E-COMMERCE
// ==========================================
define('DEFAULT_CURRENCY', '€');                   // Devise par défaut
define('TAX_RATE', 0.20);                         // Taux de TVA (20%)
define('FREE_SHIPPING_THRESHOLD', 50.00);         // Seuil livraison gratuite
define('MIN_ORDER_AMOUNT', 5.00);                 // Montant minimum commande
define('MAX_CART_ITEMS', 99);                     // Quantité max par article panier
define('FEATURED_PRODUCTS_LIMIT', 6);             // Nb produits mis en avant

// ==========================================
// CONFIGURATION PAGINATION
// ==========================================
define('PRODUCTS_PER_PAGE', 12);                  // Produits par page
define('ORDERS_PER_PAGE', 20);                    // Commandes par page admin
define('USERS_PER_PAGE', 25);                     // Utilisateurs par page admin

// ==========================================
// CONFIGURATION UPLOAD FICHIERS
// ==========================================
define('MAX_FILE_SIZE', 2097152);                 // Taille max fichier (2MB)
define('ALLOWED_IMAGE_TYPES', 'jpg,jpeg,png,gif'); // Types images autorisés
define('IMAGE_MAX_WIDTH', 800);                   // Largeur max images
define('IMAGE_MAX_HEIGHT', 600);                  // Hauteur max images

// ==========================================
// GESTION DES ERREURS
// ==========================================
// Mode développement (mettre à false en production)
if (!defined('DEBUG_MODE')) {
    define('DEBUG_MODE', true);
}

if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ini_set('log_errors', 1);
    ini_set('error_log', ROOT_PATH . 'logs/error.log');
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', ROOT_PATH . 'logs/error.log');
}

// ==========================================
// FUSEAU HORAIRE
// ==========================================
date_default_timezone_set('Europe/Paris');

// ==========================================
// CONFIGURATION SESSION
// ==========================================

// Vérifie si une session est déjà active
if (session_status() === PHP_SESSION_NONE) { 
    ini_set('session.cookie_httponly', 1);           // Cookie accessible que via HTTP
    ini_set('session.cookie_secure', 0);            // HTTPS uniquement (0 pour localhost)
    ini_set('session.use_strict_mode', 1);         // Mode strict sessions
    ini_set('session.cookie_samesite', 'Strict'); // Protection CSRF
    session_start();
}

// ==========================================
// STATUTS DE COMMANDE
// ==========================================
define('ORDER_STATUS_PENDING', 'pending');         // En attente
define('ORDER_STATUS_CONFIRMED', 'confirmed');    // Confirmée
define('ORDER_STATUS_SHIPPED', 'shipped');        // Expédiée
define('ORDER_STATUS_DELIVERED', 'delivered');    // Livrée
define('ORDER_STATUS_CANCELLED', 'cancelled');    // Annulée

// Libellés des statuts (pour affichage)
$order_status_labels = [
    ORDER_STATUS_PENDING => 'En attente',
    ORDER_STATUS_CONFIRMED => 'Confirmée',
    ORDER_STATUS_SHIPPED => 'Expédiée',
    ORDER_STATUS_DELIVERED => 'Livrée',
    ORDER_STATUS_CANCELLED => 'Annulée'
];

// ==========================================
// MESSAGES SYSTÈME
// ==========================================
define('MSG_LOGIN_SUCCESS', 'Connexion réussie !');
define('MSG_LOGIN_ERROR', 'Email ou mot de passe incorrect.');
define('MSG_REGISTER_SUCCESS', 'Inscription réussie ! Vous pouvez maintenant vous connecter.');
define('MSG_REGISTER_ERROR', 'Erreur lors de l\'inscription.');
define('MSG_CART_ADD_SUCCESS', 'Produit ajouté au panier !');
define('MSG_CART_UPDATE_SUCCESS', 'Panier mis à jour.');
define('MSG_ORDER_SUCCESS', 'Commande validée avec succès !');
define('MSG_ORDER_ERROR', 'Erreur lors de la validation de la commande.');
define('MSG_ACCESS_DENIED', 'Accès refusé. Vous devez être connecté.');
define('MSG_ADMIN_REQUIRED', 'Accès administrateur requis.');

// ==========================================
// FONCTIONS UTILITAIRES
// ==========================================

/**
 * Fonction pour formater un prix
 */
function format_price($price) {
    return number_format($price, 2, ',', ' ') . ' ' . DEFAULT_CURRENCY;
}

/**
 * Fonction pour calculer le prix TTC
 */
function calculate_price_ttc($price_ht) {
    return $price_ht * (1 + TAX_RATE);
}

/**
 * Fonction pour générer une URL complète
 */
function site_url($path = '') {
    return SITE_URL . ltrim($path, '/');
}

/**
 * Fonction pour vérifier si un fichier image existe
 */
function image_exists($image_path) {
    return file_exists(ROOT_PATH . $image_path) && !empty($image_path);
}

/**
 * Fonction pour obtenir l'image par défaut si nécessaire
 */
function get_image_or_default($image_path) {
    return image_exists($image_path) ? $image_path : DEFAULT_IMAGE;
}

/**
 * Fonction pour nettoyer les données d'entrée
 */
function clean_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

/**
 * Fonction pour rediriger
 */
function redirect($url) {
    header("Location: " . $url);
    exit();
}

/**
 * Fonction pour vérifier si l'utilisateur est admin
 */
function is_admin() {
    return isset($_SESSION['user_id']) && isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1;
}

/**
 * Fonction pour vérifier si l'utilisateur est connecté
 */
function is_logged_in() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Fonction pour créer les répertoires nécessaires
 */
function create_directories() {
    $directories = [
        ROOT_PATH . 'images/',
        ROOT_PATH . 'images/products/',
        ROOT_PATH . 'images/categories/',
        ROOT_PATH . 'logs/'
    ];
    
    foreach ($directories as $dir) {
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
    }
}

// Créer les répertoires si ils n'existent pas
create_directories();

// ==========================================
// VÉRIFICATIONS SYSTÈME
// ==========================================

/**
 * Vérifier que les extensions PHP nécessaires sont chargées
 */
$required_extensions = ['mysqli', 'gd', 'session'];
$missing_extensions = [];

foreach ($required_extensions as $extension) {
    if (!extension_loaded($extension)) {
        $missing_extensions[] = $extension;
    }
}

if (!empty($missing_extensions)) {
    die('Extensions PHP manquantes : ' . implode(', ', $missing_extensions));
}

/**
 * Vérifier les permissions d'écriture
 */
$writable_dirs = [
    ROOT_PATH . 'images/',
    ROOT_PATH . 'logs/'
];

foreach ($writable_dirs as $dir) {
    if (!is_writable($dir)) {
        if (DEBUG_MODE) {
            echo "<div style='background: #ffebee; color: #c62828; padding: 10px; margin: 10px;'>";
            echo "Attention : Le répertoire {$dir} n'est pas accessible en écriture.";
            echo "</div>";
        }
    }
}
?>