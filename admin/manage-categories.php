<?php
session_start();

// Vérification : est-ce que c'est un admin ?
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

// Connexion à la base de données
require_once '../inc/db.php';

$message = "";

// ========================
// Ajouter une catégorie
// ========================
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_category'])) {
    $name = trim($_POST['name']);

    if (!empty($name)) {
        $stmt = mysqli_prepare($conn, "INSERT INTO categories (name) VALUES (?)");
        mysqli_stmt_bind_param($stmt, "s", $name);

        if (mysqli_stmt_execute($stmt)) {
            $message = "✅ Catégorie ajoutée avec succès !";
        } else {
            $message = "❌ Erreur : " . mysqli_error($conn);
        }
        mysqli_stmt_close($stmt);
    } else {
        $message = "⚠ Merci de remplir le champ.";
    }
}

// ========================
// Supprimer une catégorie
// ========================
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = mysqli_prepare($conn, "DELETE FROM categories WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);

    if (mysqli_stmt_execute($stmt)) {
        $message = "🗑 Catégorie supprimée.";
    } else {
        $message = "❌ Erreur lors de la suppression.";
    }
    mysqli_stmt_close($stmt);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion Catégories - Admin</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>
<body class="container py-4">

    <h1 class="mb-4">Gestion des Catégories</h1>
    <p><a href="index.php" class="btn btn-secondary btn-sm">⬅ Retour au tableau de bord</a></p>

    <!-- Message -->
    <?php if ($message != ""): ?>
        <div class="alert alert-info"><?= $message ?></div>
    <?php endif; ?>

    <!-- Formulaire ajout catégorie -->
    <h2>➕ Ajouter une catégorie</h2>
    <form method="POST" class="mb-4">
        <div class="mb-2">
            <input type="text" name="name" class="form-control" placeholder="Nom de la catégorie" required>
        </div>
        <button type="submit" name="add_category" class="btn btn-primary">Ajouter</button>
    </form>

    <hr>

    <!-- Liste des catégories existantes -->
    <h2>📂 Catégories existantes</h2>
    <ul class="list-group">
    <?php
    $res = mysqli_query($conn, "SELECT * FROM categories ORDER BY id DESC");
    while ($row = mysqli_fetch_assoc($res)) {
        echo "<li class='list-group-item d-flex justify-content-between align-items-center'>"
             . htmlspecialchars($row['name']) .
             "<a href='manage-categories.php?delete=" . $row['id'] . "' class='btn btn-sm btn-danger' onclick=\"return confirm('Supprimer cette catégorie ?');\">❌</a></li>";
    }
    ?>
    </ul>

</body>
</html>
