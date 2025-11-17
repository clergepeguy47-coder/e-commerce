<?php
session_start();
define('SITE_ACCESS', true);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../functions/product-functions.php';
require_once __DIR__ . '/../includes/header.php';

$search_query = isset($_GET['q']) ? trim($_GET['q']) : '';
$products = [];

if ($search_query !== '') {
    $sql = "SELECT * FROM products WHERE name LIKE ? OR description LIKE ?";
    $stmt = mysqli_prepare($conn, $sql);
    $like_query = '%' . $search_query . '%';
    mysqli_stmt_bind_param($stmt, "ss", $like_query, $like_query);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    while ($row = mysqli_fetch_assoc($result)) {
        $products[] = $row;
    }

    mysqli_stmt_close($stmt);
}
?>

<div class="container py-4">
    <h2 class="mb-4 text-center">🔍 Résultats de recherche pour : "<?= htmlspecialchars($search_query) ?>"</h2>

    <?php if ($search_query === ''): ?>
        <div class="alert alert-warning text-center">
            Veuillez entrer un mot-clé pour rechercher un produit.
        </div>
    <?php elseif (empty($products)): ?>
        <div class="alert alert-info text-center">
            Aucun produit trouvé pour "<?= htmlspecialchars($search_query) ?>".
        </div>
    <?php else: ?>
        <div class="row row-cols-1 row-cols-md-3 row-cols-lg-4 g-4">
            <?php foreach ($products as $product): ?>
                <div class="col">
                    <div class="card h-100 shadow-sm">
                        <img src="/ecommerce-site/images/products/<?= htmlspecialchars($product['image']) ?>"
                             class="card-img-top"
                             alt="<?= htmlspecialchars($product['name']) ?>"
                             style="height: 200px; object-fit: cover;"
                             onerror="this.src='/ecommerce-site/images/products/placeholder.jpg'">
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title"><?= htmlspecialchars($product['name']) ?></h5>
                            <p class="card-text flex-grow-1">
                                <?= htmlspecialchars(substr($product['description'], 0, 80)) ?>...
                            </p>
                            <div class="mt-auto">
                                <span class="h5 text-primary">
                                    <?= number_format($product['price'], 2, ',', ' ') ?> €
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
