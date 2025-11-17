<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../pages/login.php');
    exit;
}
define('SITE_ACCESS', true);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../functions/product-functions.php';
require_once __DIR__ . '/../functions/cart-functions.php';
require_once __DIR__ . '/../includes/header.php';

$user_id = $_SESSION['user_id'] ?? null;
$message = '';
$error = '';

if (!$user_id) {
    header('Location: ../login.php');
    exit;
}

// 1. Supprimer un produit du panier
if (isset($_GET['action']) && $_GET['action'] === 'remove' && isset($_GET['product_id'])) {
    $product_id = intval($_GET['product_id']);
    $sql = "DELETE FROM cart WHERE user_id = ? AND product_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ii", $user_id, $product_id);
    if (mysqli_stmt_execute($stmt)) {
        $message = "Produit retiré du panier";
    } else {
        $error = "Erreur lors de la suppression";
    }
    mysqli_stmt_close($stmt);
}

// 2. Mettre à jour la quantité
if (isset($_POST['update_quantity'])) {
    $product_id = intval($_POST['product_id']);
    $new_quantity = intval($_POST['quantity']);

    if ($new_quantity > 0) {
        $sql = "UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "iii", $new_quantity, $user_id, $product_id);
        if (mysqli_stmt_execute($stmt)) {
            $message = "Quantité mise à jour";
        } else {
            $error = "Erreur lors de la mise à jour";
        }
        mysqli_stmt_close($stmt);
    } else {
        $sql = "DELETE FROM cart WHERE user_id = ? AND product_id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ii", $user_id, $product_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        $message = "Produit retiré du panier";
    }
}

// 3. Vider le panier
if (isset($_GET['action']) && $_GET['action'] === 'clear') {
    $sql = "DELETE FROM cart WHERE user_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    if (mysqli_stmt_execute($stmt)) {
        $message = "Panier vidé avec succès";
    }
    mysqli_stmt_close($stmt);
}

// 4. Récupération des produits du panier
$sql = "SELECT c.id AS cart_id, c.quantity, c.product_id,
               p.name, p.price, p.image, p.stock,
               (c.quantity * p.price) AS subtotal
        FROM cart c
        INNER JOIN products p ON c.product_id = p.id
        WHERE c.user_id = ?
        ORDER BY c.is_active DESC";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$cart_items = [];
$total = 0;

while ($row = mysqli_fetch_assoc($result)) {
    $cart_items[] = $row;
    $total += $row['subtotal'];
}
mysqli_stmt_close($stmt);
$cart_count = count($cart_items);

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Panier - E-commerce</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .cart-item-img {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 8px;
        }
        .quantity-input {
            width: 70px;
            text-align: center;
        }
        .cart-summary {
            position: sticky;
            top: 20px;
        }
        .empty-cart {
            text-align: center;
            padding: 60px 20px;
        }
        .empty-cart i {
            font-size: 80px;
            color: #ddd;
        }
    </style>
</head>
<body>

<div class="container my-5">
    <h1 class="mb-4"><i class="fas fa-shopping-cart"></i> Mon Panier</h1>


<?php if ($message): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <?php echo htmlspecialchars($message); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <?php echo htmlspecialchars($error); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if ($cart_count == 0): ?>
    <!-- PANIER VIDE -->
    <div class="empty-cart">
        <i class="fas fa-shopping-cart"></i>
        <h3 class="mt-3">Votre panier est vide</h3>
        <p class="text-muted">Découvrez nos produits et ajoutez-les à votre panier</p>
        <a href="products.php" class="btn btn-primary mt-3">
            <i class="fas fa-shopping-bag"></i> Voir les produits
        </a>
    </div>
<?php else: ?>
    <!-- PANIER AVEC PRODUITS -->
    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0"><?php echo $cart_count; ?> article(s) dans votre panier</h5>
                        <a href="?action=clear" class="btn btn-outline-danger btn-sm" 
                           onclick="return confirm('Voulez-vous vraiment vider votre panier ?')">
                            <i class="fas fa-trash"></i> Vider le panier
                        </a>
                    </div>

                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead>
                                <tr>
                                    <th>Produit</th>
                                    <th>Prix unitaire</th>
                                    <th>Quantité</th>
                                    <th>Sous-total</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($cart_items as $item): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <?php if ($item['image']): ?>
                                                    <img src="../images/products/<?php echo htmlspecialchars($item['image']); ?>" 
                                                         alt="<?php echo htmlspecialchars($item['nom']); ?>"
                                                         class="cart-item-img me-3">
                                                <?php else: ?>
                                                    <div class="cart-item-img me-3 bg-light d-flex align-items-center justify-content-center">
                                                        <i class="fas fa-image text-muted"></i>
                                                    </div>
                                                <?php endif; ?>
                                                <div>
                                                    <strong><?php echo htmlspecialchars($item['nom']); ?></strong><br>
                                                    <small class="text-muted">
                                                        Stock: <?php echo $item['stock']; ?> disponible(s)
                                                    </small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <strong><?php echo number_format($item['prix'], 2); ?> €</strong>
                                        </td>
                                        <td>
                                            <form method="POST" class="d-inline">
                                                <input type="hidden" name="product_id" value="<?php echo $item['product_id']; ?>">
                                                <div class="input-group input-group-sm">
                                                    <input type="number" 
                                                           name="quantity" 
                                                           value="<?php echo $item['quantite']; ?>"
                                                           min="0" 
                                                           max="<?php echo $item['stock']; ?>"
                                                           class="form-control quantity-input"
                                                           onchange="this.form.submit()">
                                                    <button type="submit" name="update_quantity" class="btn btn-outline-secondary">
                                                        <i class="fas fa-sync-alt"></i>
                                                    </button>
                                                </div>
                                            </form>
                                        </td>
                                        <td>
                                            <strong class="text-primary">
                                                <?php echo number_format($item['subtotal'], 2); ?> €
                                            </strong>
                                        </td>
                                        <td>
                                            <a href="?action=remove&product_id=<?php echo $item['product_id']; ?>" 
                                               class="btn btn-sm btn-outline-danger"
                                               onclick="return confirm('Retirer ce produit du panier ?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <a href="products.php" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Continuer mes achats
            </a>
        </div>

        <!-- RÉSUMÉ DE LA COMMANDE -->
        <div class="col-lg-4">
            <div class="card shadow-sm cart-summary">
                <div class="card-body">
                    <h5 class="card-title mb-4">Résumé de la commande</h5>
                    
                    <div class="d-flex justify-content-between mb-2">
                        <span>Sous-total (<?php echo $cart_count; ?> articles)</span>
                        <strong><?php echo number_format($total, 2); ?> €</strong>
                    </div>
                    
                    <div class="d-flex justify-content-between mb-2">
                        <span>Livraison</span>
                        <span class="text-success">Gratuite</span>
                    </div>
                    
                    <hr>
                    
                    <div class="d-flex justify-content-between mb-4">
                        <h5>Total</h5>
                        <h5 class="text-primary"><?php echo number_format($total, 2); ?> €</h5>
                    </div>

                    <a href="checkout.php" class="btn btn-primary btn-lg w-100 mb-3">
                        <i class="fas fa-credit-card"></i> Procéder au paiement
                    </a>

                    <div class="alert alert-info small mb-0">
                        <i class="fas fa-shield-alt"></i> Paiement 100% sécurisé
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>


</div>

<?php require_once '../includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>

<?php
// Fermer la connexion
mysqli_close($conn);
?>