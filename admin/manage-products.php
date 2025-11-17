<?php
session_start();
//session_start();  Vérification de l'accès admin 
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') { 
header('Location: ../pages/login.php');
 exit(); 
}

// Inclusions
define('SITE_ACCESS', true);
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../functions/admin-functions.php';

$conn = $conn ?? null;

// Messages
$success_message = '';
$error_message = '';

// Extensions autorisées
$allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

// ========== INSÉRER DES PRODUITS EXEMPLE ==========
if (isset($_GET['seed']) && $_SESSION['role'] === 'admin') {
    $sql = "INSERT IGNORE INTO products (id, name, description, price, stock, category_id, image, is_active, created_at) VALUES
    (1, 'Voiture', 'Voiture neuve', 50000.99, 50, 1, 'Voiture.webp', 1, NOW()),
    (2, 'T-shirt Bleu', 'T-shirt 100% coton taille M', 30.00, 100, 2, 'T_shirt.webp', 1, NOW()),
    (3, 'iPhone', 'Dernière génération avec 128GB', 1200.99, 75, 3, 'Iphone.webp', 1, NOW()),
    (4, 'Ballon de Football', 'Ballon de foot professionnel', 29.99, 30, 4, 'Ballon.webp', 1, NOW()),
    (5, 'Roman Bestseller', 'Roman à succès de l\'année', 15.99, 200, 5, 'livre.webp', 1, NOW())";

    if (mysqli_query($conn, $sql)) {
        $success_message = "✓ Produits exemple insérés avec succès.";
    } else {
        $error_message = "✗ Erreur lors de l'insertion : " . mysqli_error($conn);
    }
}

// ========== AJOUTER UN PRODUIT ==========
if (isset($_POST['add_product'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $price = floatval($_POST['price']);
    $category_id = intval($_POST['category_id']);
    $stock = intval($_POST['stock']);
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    $image_name = 'placeholder.jpg';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $file_extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));

        if (in_array($file_extension, $allowed_extensions)) {
            $image_name = uniqid('product_') . '.' . $file_extension;
            $upload_path = '../images/products/' . $image_name;

            if (!move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                $error_message = "Erreur lors de l'upload de l'image.";
                $image_name = 'placeholder.jpg';
            }
        } else {
            $error_message = "Type de fichier non autorisé (JPG, PNG, GIF, WEBP uniquement).";
        }
    }

    if (empty($error_message)) {
        $stmt = mysqli_prepare($conn,
            "INSERT INTO products (name, description, price, category_id, stock, image, is_active, created_at) 
             VALUES (?, ?, ?, ?, ?, ?, ?, NOW())"
        );
        mysqli_stmt_bind_param($stmt, "ssdissi", $name, $description, $price, $category_id, $stock, $image_name, $is_active);

        if (mysqli_stmt_execute($stmt)) {
            $success_message = "✓ Produit ajouté avec succès !";
        } else {
            $error_message = "✗ Erreur lors de l'ajout : " . mysqli_error($conn);
        }
        mysqli_stmt_close($stmt);
    }
}

// ========== MODIFIER UN PRODUIT ==========
if (isset($_POST['edit_product'])) {
    $id = intval($_POST['product_id']);
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $price = floatval($_POST['price']);
    $category_id = intval($_POST['category_id']);
    $stock = intval($_POST['stock']);
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    $old_image_query = mysqli_query($conn, "SELECT image FROM products WHERE id = $id");
    $old_image_data = mysqli_fetch_assoc($old_image_query);
    $image_name = $old_image_data['image'];

    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $file_extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));

        if (in_array($file_extension, $allowed_extensions)) {
            $new_image_name = uniqid('product_') . '.' . $file_extension;
            $upload_path = '../images/products/' . $new_image_name;

            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                if ($old_image_data['image'] !== 'placeholder.jpg') {
                    @unlink('../images/products/' . $old_image_data['image']);
                }
                $image_name = $new_image_name;
            }
        } else {
            $error_message = "Type de fichier non autorisé (JPG, PNG, GIF, WEBP uniquement).";
        }
    }

    $stmt = mysqli_prepare($conn,
        "UPDATE products SET name=?, description=?, price=?, category_id=?, stock=?, image=?, is_active=? WHERE id=?"
    );
    mysqli_stmt_bind_param($stmt, "ssdissii", $name, $description, $price, $category_id, $stock, $image_name, $is_active, $id);

    if (mysqli_stmt_execute($stmt)) {
        $success_message = "✓ Produit modifié avec succès !";
    } else {
        $error_message = "✗ Erreur lors de la modification : " . mysqli_error($conn);
    }
    mysqli_stmt_close($stmt);
}

// ========== SUPPRIMER UN PRODUIT ==========
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);

    $image_query = mysqli_query($conn, "SELECT image FROM products WHERE id = $id");
    $image_data = mysqli_fetch_assoc($image_query);

    $stmt = mysqli_prepare($conn, "DELETE FROM products WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);

    if (mysqli_stmt_execute($stmt)) {
        if ($image_data && $image_data['image'] !== 'placeholder.jpg') {
            @unlink('../images/products/' . $image_data['image']);
        }
        $success_message = "✓ Produit supprimé avec succès !";
    } else {
        $error_message = "✗ Erreur lors de la suppression.";
    }
    mysqli_stmt_close($stmt);
}

// ========== RÉCUPÉRER LES PRODUITS ET CATÉGORIES ==========
$products_query = "SELECT p.*, c.name as category_name 
                   FROM products p 
                   LEFT JOIN categories c ON p.category_id = c.id 
                   ORDER BY p.created_at DESC";
$products_result = mysqli_query($conn, $products_query);

$categories_query = "SELECT * FROM categories ORDER BY name ASC";
$categories_result = mysqli_query($conn, $categories_query);
?>

<!DOCTYPE html>

<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Produits - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <nav class="navbar navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">🔧 Administration</a>
            <a href="../index.php" class="btn btn-outline-light btn-sm">← Retour au site</a>
        </div>
    </nav>


<div class="container my-5">
    <h1 class="mb-4">📦 Gestion des Produits</h1>

    <!-- Messages -->
    <?php if (!empty($success_message)): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?php echo $success_message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (!empty($error_message)): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?php echo $error_message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Bouton ajouter -->
    <button class="btn btn-success mb-4" data-bs-toggle="modal" data-bs-target="#addProductModal">
        ➕ Ajouter un produit
    </button>

    <!-- Tableau des produits -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Image</th>
                            <th>Nom</th>
                            <th>Catégorie</th>
                            <th>Prix</th>
                            <th>Stock</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($product = mysqli_fetch_assoc($products_result)): ?>
                            <tr>
                                <td><?php echo $product['id']; ?></td>
                                <td>
                                    <img src="../images/products/<?php echo htmlspecialchars($product['image']); ?>" 
                                         alt="<?php echo htmlspecialchars($product['name']); ?>"
                                         style="width: 50px; height: 50px; object-fit: cover;"
                                         onerror="this.src='../images/products/placeholder.jpg'">
                                </td>
                                <td><?php echo htmlspecialchars($product['name']); ?></td>
                                <td><?php echo htmlspecialchars($product['category_name']); ?></td>
                                <td><?php echo number_format($product['price'], 2, ',', ' '); ?> €</td>
                                <td>
                                    <span class="badge <?php echo $product['stock'] > 10 ? 'bg-success' : ($product['stock'] > 0 ? 'bg-warning' : 'bg-danger'); ?>">
                                        <?php echo $product['stock']; ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge <?php echo $product['is_active'] ? 'bg-success' : 'bg-secondary'; ?>">
                                        <?php echo $product['is_active'] ? 'Actif' : 'Inactif'; ?>
                                    </span>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-primary edit-btn" 
                                            data-id="<?php echo $product['id']; ?>"
                                            data-name="<?php echo htmlspecialchars($product['name']); ?>"
                                            data-description="<?php echo htmlspecialchars($product['description']); ?>"
                                            data-price="<?php echo $product['price']; ?>"
                                            data-category="<?php echo $product['category_id']; ?>"
                                            data-stock="<?php echo $product['stock']; ?>"
                                            data-active="<?php echo $product['is_active']; ?>"
                                            data-bs-toggle="modal" 
                                            data-bs-target="#editProductModal">
                                        ✏
                                    </button>
                                    <a href="?delete=<?php echo $product['id']; ?>" 
                                       class="btn btn-sm btn-danger"
                                       onclick="return confirm('Supprimer ce produit ?')">
                                        🗑
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal AJOUTER -->
<div class="modal fade" id="addProductModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title">➕ Ajouter un produit</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nom du produit *</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Prix (€) *</label>
                            <input type="number" name="price" class="form-control" step="0.01" min="0" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Stock *</label>
                            <input type="number" name="stock" class="form-control" min="0" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Catégorie *</label>
                        <select name="category_id" class="form-select" required>
                            <option value="">-- Choisir --</option>
                            <?php 
                            mysqli_data_seek($categories_result, 0);
                            while ($cat = mysqli_fetch_assoc($categories_result)): 
                            ?>
                                <option value="<?php echo $cat['id']; ?>">
                                    <?php echo htmlspecialchars($cat['name']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Image</label>
                        <input type="file" name="image" class="form-control" accept="image/*">
                        <small class="text-muted">JPG, PNG, GIF, WEBP (max 2MB)</small>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" name="is_active" class="form-check-input" id="activeAdd" checked>
                        <label class="form-check-label" for="activeAdd">Produit actif</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" name="add_product" class="btn btn-success">Ajouter</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal MODIFIER -->
<div class="modal fade" id="editProductModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="product_id" id="edit_id">
                <div class="modal-header">
                    <h5 class="modal-title">✏ Modifier un produit</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nom du produit *</label>
                        <input type="text" name="name" id="edit_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" id="edit_description" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Prix (€) *</label>
                            <input type="number" name="price" id="edit_price" class="form-control" step="0.01" min="0" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Stock *</label>
                            <input type="number" name="stock" id="edit_stock" class="form-control" min="0" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Catégorie *</label>
                        <select name="category_id" id="edit_category" class="form-select" required>
                            <?php 
                            mysqli_data_seek($categories_result, 0);
                            while ($cat = mysqli_fetch_assoc($categories_result)): 
                            ?>
                                <option value="<?php echo $cat['id']; ?>">
                                    <?php echo htmlspecialchars($cat['name']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nouvelle image (laisser vide pour conserver)</label>
                        <input type="file" name="image" class="form-control" accept="image/*">
                    </div>
                    <div class="form-check">
                        <input type="checkbox" name="is_active" class="form-check-input" id="edit_active">
                        <label class="form-check-label" for="edit_active">Produit actif</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" name="edit_product" class="btn btn-primary">Modifier</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Remplir le formulaire de modification
    document.querySelectorAll('.edit-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('edit_id').value = this.dataset.id;
            document.getElementById('edit_name').value = this.dataset.name;
            document.getElementById('edit_description').value = this.dataset.description;
            document.getElementById('edit_price').value = this.dataset.price;
            document.getElementById('edit_category').value = this.dataset.category;
            document.getElementById('edit_stock').value = this.dataset.stock;
            document.getElementById('edit_active').checked = this.dataset.active == '1';
        });
    });
</script>


</body>
</html>

<?php
mysqli_close($conn);
?>