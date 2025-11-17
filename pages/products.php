<?php

if (!defined('SITE_ACCESS')) {
    define('SITE_ACCESS', true);
}
/**
 * Fichier : pages/products.php
 * Description : Page publique d'affichage des produits
 */


require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../functions/cart-functions.php'; // ✅ doit venir AVANT
//require_once '../functions/product-functions.php';
$success_message = '';
$error_message = '';

// Gestion de l'ajout au panier
if (isset($_POST['add_to_cart'])) {
    $productId = intval($_POST['product_id']);
    $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;

    // ✅ Correction : ordre des arguments
    if (addToCart($productId, $conn, $quantity)) {
        $success_message = "Produit ajouté au panier avec succès !";
    } else {
        $error_message = "Erreur : stock insuffisant ou produit indisponible.";
    }
}

// Récupération des catégories
$categories_query = "SELECT id, name FROM categories ORDER BY name ASC";
$categories_result = mysqli_query($conn, $categories_query);

// Filtres
$category_filter = isset($_GET['category']) ? intval($_GET['category']) : 0;
$search_query = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$allowed_sorts = ['name_asc', 'name_desc', 'price_asc', 'price_desc'];
$sort_param = $_GET['sort'] ?? 'name_asc';
$sort_by = in_array($sort_param, $allowed_sorts) ? $sort_param : 'name_asc';

// Construction de la requête SQL

$sql = "SELECT p.*, c.name AS category_name
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE p.status = 'active'";

if ($category_filter > 0) {
    $sql .= " AND p.category_id = $category_filter";
}

if (!empty($search_query)) {
    $sql .= " AND (p.name LIKE '%$search_query%' OR p.description LIKE '%$search_query%')";
}

switch ($sort_by) {
    case 'price_asc':
        $sql .= " ORDER BY p.price ASC";
        break;
    case 'price_desc':
        $sql .= " ORDER BY p.price DESC";
        break;
    case 'name_desc':
        $sql .= " ORDER BY p.name DESC";
        break;
    default:
        $sql .= " ORDER BY p.name ASC";
}

$products_result = mysqli_query($conn, $sql);
$products_count = mysqli_num_rows($products_result);
?>

<?php //include '../includes/header.php'; ?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nos Produits - E-commerce</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>


<div class="container my-5">
    <h1 class="mb-4">📦 Nos Produits</h1>

    <?php if ($success_message): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            ✓ <?= htmlspecialchars($success_message) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if ($error_message): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            ✗ <?= htmlspecialchars($error_message) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Filtres -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">🔍 Rechercher</label>
                    <input type="text" name="search" class="form-control"
                           placeholder="Nom du produit..."
                           value="<?= htmlspecialchars($search_query) ?>">
                </div>

                <div class="col-md-3">
                    <label class="form-label">📁 Catégorie</label>
                    <select name="category" class="form-select">
                        <option value="0">Toutes les catégories</option>
                        <?php while ($cat = mysqli_fetch_assoc($categories_result)): ?>
                            <option value="<?= $cat['id'] ?>" <?= ($category_filter == $cat['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat['name']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">🔀 Trier par</label>
                    <select name="sort" class="form-select">
                        <option value="name_asc" <?= ($sort_by == 'name_asc') ? 'selected' : '' ?>>Nom (A-Z)</option>
                        <option value="name_desc" <?= ($sort_by == 'name_desc') ? 'selected' : '' ?>>Nom (Z-A)</option>
                        <option value="price_asc" <?= ($sort_by == 'price_asc') ? 'selected' : '' ?>>Prix (croissant)</option>
                        <option value="price_desc" <?= ($sort_by == 'price_desc') ? 'selected' : '' ?>>Prix (décroissant)</option>
                    </select>
                </div>

                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">Filtrer</button>
                </div>
            </form>
        </div>
    </div>

    <div class="mb-3">
        <strong><?= $products_count ?></strong> produit(s) trouvé(s)
    </div>

    <!-- Grille de produits -->
    <div class="row row-cols-1 row-cols-md-3 row-cols-lg-4 g-4">
        <?php if ($products_count > 0): ?>
            <?php while ($product = mysqli_fetch_assoc($products_result)): ?>
                <div class="col">
                    <div class="card h-100 shadow-sm">
                      <img 
                       src="<?= function_exists('get_Product_Image_Path') 
                       ? htmlspecialchars(get_Product_Image_Path($product['image'])) 
                       : '/ecommerce-site/images/products/' . htmlspecialchars($product['image']) ?>"
                        class="card-img-top"
                        alt="<?= htmlspecialchars($product['name']) ?>"
                        style="height: 200px; object-fit: cover;"
                        onerror="this.src='/ecommerce-site/images/products/placeholder.jpg'">

                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title"><?= htmlspecialchars($product['name']) ?></h5>
                            <p class="card-text text-muted small"><?= htmlspecialchars($product['category_name']) ?></p>
                            <p class="card-text flex-grow-1">
                                <?= htmlspecialchars(substr($product['description'], 0, 80)) ?>...
                            </p>

                            <div class="mt-auto">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="h5 mb-0 text-primary">
                                        <?= number_format($product['price'], 2, ',', ' ') ?> €
                                    </span>
                                    <small class="text-muted">Stock: <?= $product['stock'] ?></small>
                                </div>

                                <?php if ($product['stock'] > 0): ?>
                                    <form method="POST" action="">
                                        <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                                        <div class="input-group mb-2">
                                            <input type="number" name="quantity" class="form-control"
                                                   value="1" min="1" max="<?= $product['stock'] ?>">
                                            <button type="submit" name="add_to_cart" class="btn btn-success">
                                                🛒 Ajouter
                                            </button>
                                        </div>
                                    </form>
                                <?php else: ?>
                                    <button class="btn btn-secondary w-100" disabled>Rupture de stock</button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="alert alert-info">Aucun produit trouvé avec ces critères de recherche.</div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php //include '../includes/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>