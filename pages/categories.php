<?php
session_start();
define('SITE_ACCESS', true);

require_once '../config.php';
require_once '../includes/database.php';
require_once '../functions/product-functions.php';
require_once '../includes/header.php';

// Récupérer toutes les catégories avec le nombre de produits
$query = "SELECT c.id, c.name, c.description, c.image, COUNT(p.id) AS product_count 
          FROM categories c 
          LEFT JOIN products p ON c.id = p.category_id 
          GROUP BY c.id, c.name, c.description, c.image 
          ORDER BY c.name ASC";

$result = mysqli_query($conn, $query);

if (!$result) {
    die("Erreur de requête: " . mysqli_error($conn));
}

// Stocker les résultats dans un tableau
$categories = [];
while ($row = mysqli_fetch_assoc($result)) {
    $categories[] = $row;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catégories - E-Commerce</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        /* Styles identiques à ceux que tu avais */
        .categories-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 20px;
        }
        .page-title {
            text-align: center;
            font-size: 2.5em;
            color: #333;
            margin-bottom: 40px;
        }
        .categories-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 30px;
            margin-top: 30px;
        }
        .category-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            cursor: pointer;
        }
        .category-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
        }
        .category-image {
            width: 100%;
            height: 200px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 4em;
            color: white;
        }
        .category-content {
            padding: 20px;
        }
        .category-name {
            font-size: 1.5em;
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
        }
        .category-description {
            color: #666;
            font-size: 0.95em;
            line-height: 1.5;
            margin-bottom: 15px;
        }
        .category-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 15px;
            border-top: 1px solid #eee;
        }
        .product-count {
            background: #667eea;
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.9em;
        }
        .view-products-btn {
            background: #764ba2;
            color: white;
            padding: 8px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            font-size: 0.9em;
            transition: background 0.3s ease;
        }
        .view-products-btn:hover {
            background: #5a3a7f;
        }
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #999;
        }
        .breadcrumb {
            max-width: 1200px;
            margin: 20px auto;
            padding: 0 20px;
            color: #666;
        }
        .breadcrumb a {
            color: #667eea;
            text-decoration: none;
        }
        .breadcrumb a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="breadcrumb">
    <a href="../index.php">Accueil</a> / Catégories
</div>

<div class="categories-container">
    <h1 class="page-title">Nos Catégories</h1>

    <?php if (count($categories) > 0): ?>
        <div class="categories-grid">
            <?php foreach ($categories as $category): ?>
                <div class="category-card" onclick="window.location.href='products.php?category=<?= $category['id'] ?>'">
                    <div class="category-image">
                        <?= isset($category['icon']) ? $category['icon'] : '📦' ?>
                    </div>
                    <div class="category-content">
                        <h2 class="category-name"><?= htmlspecialchars($category['name']) ?></h2>
                        <?php if (!empty($category['description'])): ?>
                            <p class="category-description">
                                <?= htmlspecialchars(substr($category['description'], 0, 100)) ?>
                                <?= strlen($category['description']) > 100 ? '...' : '' ?>
                            </p>
                        <?php endif; ?>
                        <div class="category-info">
                            <span class="product-count">
                                <?= $category['product_count'] ?> <?= $category['product_count'] > 1 ? 'produits' : 'produit' ?>
                            </span>
                            <a href="products.php?category=<?= $category['id'] ?>" class="view-products-btn" onclick="event.stopPropagation();">
                                Voir produits
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <div style="font-size: 5em; margin-bottom: 20px;">📂</div>
            <h2>Aucune catégorie disponible</h2>
            <p>Les catégories seront ajoutées prochainement.</p>
        </div>
    <?php endif; ?>
</div>

<?php
mysqli_close($conn);
require_once '../includes/footer.php';
?>

</body>
</html>
