<?php
session_start();
require_once '../config.php';
require_once '../includes/database.php';
require_once '../includes/header.php';

// Récupérer toutes les catégories avec le nombre de produits
$query = "SELECT c.*, COUNT(p.id) as product_count 
          FROM categories c 
          LEFT JOIN products p ON c.id = p.category_id 
          GROUP BY c.id 
          ORDER BY c.name ASC";
$result = mysqli_query($conn, $query);

if (!$result) {
    die("Erreur de requête: " . mysqli_error($conn));
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

    .empty-state i {
        font-size: 5em;
        margin-bottom: 20px;
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


<?php if (mysqli_num_rows($result) > 0): ?>
    <div class="categories-grid">
        <?php while ($category = mysqli_fetch_assoc($result)): ?>
            <div class="category-card" onclick="window.location.href='products.php?category=<?php echo $category['id']; ?>'">
                <div class="category-image">
                    <?php 
                    // Afficher l'icône ou image de la catégorie
                    echo isset($category['icon']) ? $category['icon'] : '📦';
                    ?>
                </div>
                <div class="category-content">
                    <h2 class="category-name"><?php echo htmlspecialchars($category['name']); ?></h2>
                    
                    <?php if (!empty($category['description'])): ?>
                        <p class="category-description">
                            <?php echo htmlspecialchars(substr($category['description'], 0, 100)); ?>
                            <?php echo strlen($category['description']) > 100 ? '...' : ''; ?>
                        </p>
                    <?php endif; ?>
                    
                    <div class="category-info">
                        <span class="product-count">
                            <?php echo $category['product_count']; ?> 
                            <?php echo $category['product_count'] > 1 ? 'produits' : 'produit'; ?>
                        </span>
                        <a href="products.php?category=<?php echo $category['id']; ?>" 
                           class="view-products-btn" 
                           onclick="event.stopPropagation();">
                            Voir produits
                        </a>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
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