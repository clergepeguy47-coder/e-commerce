<?php
session_start();
define('SITE_ACCESS', true);

// Inclusion des fichiers essentiels
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/database.php';
//require_once __DIR__ . '/functions/product-functions.php';
require_once __DIR__ . '/functions/cart-functions.php';
require_once __DIR__ . '/functions/user-functions.php';
require_once __DIR__ . '/includes/header.php';

// Vérification de la connexion MySQL
if (!isset($conn) || !$conn) {
    die("Erreur de connexion MySQL : " . mysqli_connect_error());
}

// Requête test
$result = execute_query("SELECT NOW() as date_actuelle");
$row = fetch_assoc($result);

// Affichage de bienvenue
echo "<div class='container mt-4'>";
echo "<h1>Bienvenue sur " . SITE_NAME . "</h1>";
echo "<p>✅ Connexion réussie ! Date MySQL : " . $row['date_actuelle'] . "</p>";
echo "</div>";

// Initialisation des variables
$allowed_pages = ['home', 'products', 'categories', 'cart', 'login', 'register', 'checkout'];
$page    = isset($_GET['page']) && in_array($_GET['page'], $allowed_pages) ? $_GET['page'] : 'home';
$action  = isset($_GET['action']) ? htmlspecialchars(trim($_GET['action'])) : '';
$message = '';
$error   = '';

// Traitement des actions
if ($action === 'checkout') {
    if (isset($_SESSION['user_id'])) {
        if (process_order($_SESSION['user_id'])) {
            clear_cart();
            $message = "Commande validée avec succès !";
        } else {
            $error = "Erreur lors de la validation de la commande.";
        }
    } else {
        $error = "Vous devez être connecté pour valider une commande.";
    }
}

if ($action === 'update_cart') {
    $product_id = (int)($_POST['product_id'] ?? 0);
    $quantity   = (int)($_POST['quantity'] ?? 1);

    if ($quantity <= 0) {
        remove_from_cart($product_id);
        $message = "Produit retiré du panier.";
    } else {
        update_cart_quantity($product_id, $quantity);
        $message = "Panier mis à jour.";
    }
}

// Déconnexion
if ($action === 'logout') {
    logout_user();
    header("Location: index.php");
    exit;
}

// Récupération des données pour affichage
$featured_products = function_exists('get_featured_products') ? get_featured_products(6) : [];
$categories        = function_exists('get_all_categories') ? get_all_categories() : [];
$cart_items        = function_exists('get_cart_items') ? get_cart_items() : [];
$cart_total        = function_exists('calculate_cart_total') ? calculate_cart_total() : 0;
$cart_count        = function_exists('get_cart_count') ? get_cart_count() : 0;

// Affichage des messages
if (!empty($message)) {
    echo "<div class='alert alert-success text-center'>$message</div>";
}
if (!empty($error)) {
    echo "<div class='alert alert-danger text-center'>$error</div>";
}

// Inclusion du pied de page
require_once __DIR__ . '/includes/footer.php';
?>

<!DOCTYPE html>

<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo ($page === 'home') ? 'Accueil - Mon Site E-commerce' : ucfirst($page) . ' - Mon Site E-commerce'; ?></title>


<!-- Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
<!-- Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<!-- CSS personnalisé -->
<link rel="stylesheet" href="css/style.css">


<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?php echo SITE_NAME; ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>

<!-- Header -->
<?php include 'includes/header.php'; ?>

<!-- Messages d'alerte -->
<?php if (!empty($message)): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?php echo $message; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?php echo $error; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- Contenu principal -->
<main class="container-fluid px-0">
    <?php
    switch ($page) {
        case 'products':
            include 'pages/products.php';
            break;

        case 'categories':
            include 'pages/categories.php';
            break;

        case 'cart':
            include 'pages/cart.php';
            break;

        case 'login':
            if (isset($_SESSION['user_id'])) {
                header("Location: index.php");
                exit;
            }
            include 'pages/login.php';
            break;

        case 'register':
            if (isset($_SESSION['user_id'])) {
                header("Location: index.php");
                exit;
            }
            include 'pages/register.php';
            break;

        case 'checkout':
            if (!isset($_SESSION['user_id'])) {
                header("Location: index.php?page=login");
                exit;
            }
            include 'pages/checkout.php';
            break;

        case 'home':
        default:
          
        break;
          
            break;
    }
    ?>
</main>

<!-- Footer -->
<?php include 'includes/footer.php'; ?>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- Script personnalisé -->
<script>
    setTimeout(() => {
        document.querySelectorAll('.alert').forEach(alert => {
            new bootstrap.Alert(alert).close();
        });
    }, 5000);

    function confirmRemove(productName) {
        return confirm('Êtes-vous sûr de vouloir supprimer "' + productName + '" de votre panier ?');
    }
</script>

</body>
</html>
