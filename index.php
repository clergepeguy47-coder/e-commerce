<?php 
/* index.php racing*/
session_start();
define('SITE_ACCESS', true);

// Inclusion des fichiers essentiels
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/database.php';
require_once __DIR__ . '/functions/cart-functions.php';
require_once __DIR__ . '/functions/product-functions.php';
require_once __DIR__ . '/functions/user-functions.php';

// Vérification de la connexion MySQL
if (!isset($conn) || !$conn) {
    die("Erreur de connexion MySQL : " . mysqli_connect_error());
}

// Initialisation des variables
$allowed_pages = ['home', 'products', 'categories', 'cart', 'login', 'register', 'checkout'];
$page    = isset($_GET['page']) && in_array($_GET['page'], $allowed_pages) ? $_GET['page'] : 'home';
$action  = isset($_GET['action']) ? htmlspecialchars(trim($_GET['action'])) : '';
$message = '';
$error   = '';

// Traitement des actions POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'login') {
        $email    = mysqli_real_escape_string($conn, $_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (login_user($email, $password)) {
            header("Location: index.php");
            exit;
        } else {
            $error = "Email ou mot de passe incorrect.";
        }
    }

    if ($action === 'register') {
        $firstname        = $_POST['firstname'] ?? '';
        $lastname         = $_POST['lastname'] ?? '';
        $email            = $_POST['email'] ?? '';
        $password         = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        if (empty($firstname) || empty($lastname) || empty($email) || empty($password)) {
            $error = "Tous les champs sont obligatoires.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Format d'email invalide.";
        } elseif (strlen($password) < 8) {
            $error = "Le mot de passe doit contenir au moins 8 caractères.";
        } elseif ($password !== $confirm_password) {
            $error = "Les mots de passe ne correspondent pas.";
        } else {
            if (!register_user($firstname, $lastname, $email, $password)) {
                $error = "Erreur lors de l'inscription. Email déjà utilisé.";
            } else {
                $message = "Inscription réussie, vous pouvez vous connecter.";
            }
        }
    }

    if ($action === 'add_to_cart') {
        $product_id = (int)($_POST['product_id'] ?? 0);
        $quantity   = (int)($_POST['quantity'] ?? 1);

        if ($quantity <= 0 || $quantity > 99) {
            $error = "Quantité invalide (entre 1 et 99).";
        } elseif (add_to_cart($product_id, $quantity)) {
            $message = "Produit ajouté au panier !";
        } else {
            $error = "Erreur lors de l'ajout au panier.";
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
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?php echo SITE_NAME; ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>

<?php include 'includes/header.php'; ?>

<?php if (!empty($message)): ?>
    <div class="alert alert-success alert-dismissible fade show text-center" role="alert">
        <?php echo $message; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger alert-dismissible fade show text-center" role="alert">
        <?php echo $error; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<main class="container-fluid px-0">

    <?php if ($page === 'home'): ?>
        <div class="container my-5">
            <h2 class="mb-4 text-center">🛍 Produits en vedette</h2>
            <div class="row row-cols-1 row-cols-md-3 row-cols-lg-4 g-4">
                <?php foreach ($featured_products as $product): ?>
                    <div class="col">
                        <div class="card h-100 shadow-sm">
                            <img src="images/products/<?php echo htmlspecialchars($product['image']); ?>"
                                 class="card-img-top"
                                 alt="<?php echo htmlspecialchars($product['name']); ?>"
                                 style="height: 200px; object-fit: cover;"
                                 onerror="this.src='images/products/placeholder.jpg'">
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title"><?php echo htmlspecialchars($product['name']); ?></h5>
                                <p class="card-text flex-grow-1">
                                    <?php echo htmlspecialchars(substr($product['description'], 0, 80)); ?>...
                                </p>
                                <div class="mt-auto">
                                    <span class="h5 text-primary">
                                        <?php echo number_format($product['price'], 2, ',', ' ') . ' €'; ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
        </div> <!-- Fin des produits en vedette -->
        
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
            default:
                include 'pages/products.php';
                break;
        }
    ?>

</main>

<?php include 'includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    setTimeout(() => {
        const alertBox = document.querySelector('.alert');
        if (alertBox) {
            alertBox.style.display = 'none';
        }
    }, 5000);
</script>

</body>
</html>
