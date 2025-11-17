<?php 
/*
 * HEADER.PHP - En-tête commun à toutes les pages
 * Ce fichier est inclus au début de chaque page pour assurer la cohérence
 */

// Sécurité : interdiction d'accès direct
if (!defined('SITE_ACCESS')) {
    die('Accès direct interdit');
}

// Connexion à la base de données
$conn = mysqli_connect('localhost', 'root', '', 'ecommerce_db');
if (!$conn) {
    die('Erreur de connexion à la base de données : ' . mysqli_connect_error());
}

// Inclusion des fonctions nécessaires
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../functions/cart-functions.php'; 

// Récupération des données pour l'affichage
$cart_count = get_cart_count();            // Nombre d'articles dans le panier
$cart_total = get_cart_total($conn);       // Total du panier en euros
$categories = get_all_categories();        // Liste des catégories
$is_user_logged = is_logged_in();          // Utilisateur connecté ?
$is_user_admin = is_admin();               // Utilisateur admin ?

// Déterminer le chemin de base selon le dossier actuel
$base_path = (basename(dirname($_SERVER['PHP_SELF'])) == 'admin') ? '../' : './';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <!-- MÉTADONNÉES DE BASE -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- TITRE DYNAMIQUE -->
    <title>
        <?= isset($page_title) ? htmlspecialchars($page_title) . ' - ' : '' ?>E-Commerce
    </title>
    
    <!-- MÉTADONNÉES SEO -->
    <meta name="description" content="Boutique en ligne - Produits de qualité aux meilleurs prix">
    <meta name="author" content="E-Commerce Team">
    <meta name="robots" content="index, follow">
    
    <!-- CSS EXTERNES -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- CSS PERSONNALISÉ -->
    <link href="<?= $base_path ?>css/style.css" rel="stylesheet">
    
    <!-- FAVICON -->
    <link rel="icon" type="image/x-icon" href="<?= $base_path ?>assets/favicon.ico">
</head>

<body>
    <!-- NAVIGATION PRINCIPALE -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary sticky-top">
        <div class="container">
            
            <!-- LOGO -->
            <a class="navbar-brand fw-bold fs-3" href="<?= $base_path ?>index.php">
                <i class="bi bi-shop me-2"></i>E-Commerce
            </a>
            
            <!-- BOUTON MOBILE -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" 
                    data-bs-target="#navbarContent" aria-controls="navbarContent" 
                    aria-expanded="false" aria-label="Basculer la navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <!-- CONTENU NAV -->
            <div class="collapse navbar-collapse" id="navbarContent">
                
                <!-- BARRE DE RECHERCHE -->
                <form class="d-flex mx-auto my-2 my-lg-0" method="GET" 
                      action="<?= $base_path ?>pages/search.php" style="max-width: 400px;">
                    <div class="input-group">
                        <input class="form-control" type="search" name="q" 
                               placeholder="Rechercher un produit..." 
                               value="<?= isset($_GET['q']) ? htmlspecialchars($_GET['q']) : '' ?>"
                               aria-label="Rechercher">
                        <button class="btn btn-outline-light" type="submit" title="Rechercher">
                            <i class="bi bi-search"></i>
                        </button>
                    </div>
                </form>
                
                <!-- MENU UTILISATEUR -->
                <ul class="navbar-nav ms-auto">
                    
                    <?php if ($is_user_logged): ?>
                        <!-- UTILISATEUR CONNECTÉ -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle d-flex align-items-center" 
                               href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-person-circle me-2"></i>
                                <span class="d-none d-lg-inline">
                                    <?= isset($_SESSION['first_name']) ? htmlspecialchars($_SESSION['first_name']) : '' ?>
                                </span>
                                <span class="d-lg-none">
                                    <?= htmlspecialchars($_SESSION['username']) ?>
                                </span>
                            </a>
                            
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <h6 class="dropdown-header">
                                        <i class="bi bi-person-badge me-2"></i>
                                        <?= isset($_SESSION['first_name'], $_SESSION['last_name'])
                               ? htmlspecialchars($_SESSION['first_name'] . ' ' . $_SESSION['last_name'])
                               : 'Invité'; ?>
                                </h6>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item" href="<?= $base_path ?>pages/profile.php">
                                        <i class="bi bi-person-gear me-2"></i>Mon Profil
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="<?= $base_path ?>pages/orders.php">
                                        <i class="bi bi-bag-check me-2"></i>Mes Commandes
                                    </a>
                                </li>
                                
                                <?php if ($is_user_admin): ?>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <a class="dropdown-item text-warning fw-bold" 
                                           href="<?= $base_path ?>admin/index.php">
                                            <i class="bi bi-gear-fill me-2"></i>Administration
                                        </a>
                                    </li>
                                <?php endif; ?>
                                
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item text-danger" 
                                       href="<?= $base_path ?>index.php?logout=1"
                                       onclick="return confirm('Êtes-vous sûr de vouloir vous déconnecter ?')">
                                        <i class="bi bi-box-arrow-right me-2"></i>Déconnexion
                                    </a>
                                </li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <!-- VISITEUR NON CONNECTÉ -->
                        <li class="nav-item">
                            <a class="nav-link" href="<?= $base_path ?>pages/login.php">
                                <i class="bi bi-box-arrow-in-right me-1"></i>Connexion
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= $base_path ?>pages/register.php">
                                <i class="bi bi-person-plus me-1"></i>Inscription
                            </a>
                        </li>
                    <?php endif; ?>
                    
                    <!-- PANIER -->
                    <li class="nav-item">
                        <a class="nav-link position-relative" href="<?= $base_path ?>pages/cart.php"
                           title="Mon panier - <?= $cart_count ?> article(s) - <?= number_format($cart_total, 2) ?>€">
                            <i class="bi bi-cart3 me-1"></i>
                            <span class="d-none d-md-inline">
                                Panier (<?= number_format($cart_total, 2) ?>€)
                            </span>
                            <span class="d-md-none">Panier</span>
                            <?php if ($cart_count > 0): ?>
                                <span class="position-absolute top-0 start-100 translate-middle 
                                             badge rounded-pill bg-danger">
                                    <?= $cart_count ?>
                                </span>
                            <?php endif; ?>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    
    <!-- NAVIGATION SECONDAIRE - CATÉGORIES -->
    <?php if (!empty($categories)): ?>
    <nav class="bg-light border-bottom py-2">
        <div class="container">
            <div class="d-flex align-items-center overflow-auto">
                <small class="text-muted me-3 flex-shrink-0">Catégories :</small>
                
                <!-- LIEN "TOUS" -->
                <a href="<?= $base_path ?>index.php" 
                   class="btn btn-outline-primary btn-sm me-2 flex-shrink-0">
                    <i class="bi bi-grid me-1"></i>Tous
                </a>

                <!-- LIENS CATÉGORIES -->
                <?php foreach ($categories as $category): ?>
                    <a href="<?= $base_path ?>pages/category.php?id=<?= $category['id'] ?>" 
                       class="btn btn-outline-secondary btn-sm me-2 flex-shrink-0"
                       title="<?= !empty($category['description']) ? htmlspecialchars($category['description'], ENT_QUOTES, 'UTF-8') : '' ?>">
                        <?= htmlspecialchars($category['name']) ?>
                    </a>
                <?php endforeach; ?>
            </div>
            <!-- Bouton Déconnexion -->
        <a href="<?= $base_path ?>pages/logout.php" 
           class="btn btn-outline-danger btn-sm">
            <i class="bi bi-box-arrow-right me-1"></i>Déconnexion
            </a>
        </div>
    </nav>
    <?php endif; ?>

    <!-- ZONE D'AFFICHAGE DES MESSAGES -->
    <div id="messagesZone">
        <?php if (isset($_SESSION['message'])): 
            $message_type = $_SESSION['message_type'] ?? 'info';
            $icon = ($message_type == 'success') ? 'check-circle' : 
                    (($message_type == 'error') ? 'exclamation-triangle' : 'info-circle');
        ?>
            <div class="alert alert-<?= $message_type == 'error' ? 'danger' : $message_type ?> 
                        alert-dismissible fade show m-3" role="alert">
                <i class="bi bi-<?= $icon ?> me-2"></i>
                <?= htmlspecialchars($_SESSION['message']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php
                unset($_SESSION['message']);
                unset($_SESSION['message_type']);
            ?>
        <?php endif; ?>
    </div>

</body>
</html>
