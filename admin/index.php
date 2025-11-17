<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../functions/admin-functions.php';
require_once __DIR__ . '/../functions/user_functions.php'; // corrigé .PHP → .php

$res1 = mysqli_query($conn, "SELECT COUNT(*) as total FROM products");
$products = mysqli_fetch_assoc($res1)['total'];

$res2 = mysqli_query($conn, "SELECT COUNT(*) as total FROM categories");
$categories = mysqli_fetch_assoc($res2)['total'];

$res3 = mysqli_query($conn, "SELECT COUNT(*) as total FROM users");
$users = mysqli_fetch_assoc($res3)['total'];

$res4 = mysqli_query($conn, "SELECT COUNT(*) as total FROM orders WHERE status='pending'");
$orders = mysqli_fetch_assoc($res4)['total'];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Tableau de bord - Administration</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>
<body class="container py-4">

    <!-- En-tête -->
    <header class="mb-4">
        <h1>Tableau de bord - Administration</h1>
        <p>Bienvenue, <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong> !</p>
    </header>

    <!-- Menu de navigation -->
    <nav class="mb-3">
        <a href="manage-products.php" class="btn btn-outline-primary btn-sm">📦 Produits</a>
        <a href="manage-categories.php" class="btn btn-outline-primary btn-sm">📂 Catégories</a>
        <a href="manage-users.php" class="btn btn-outline-primary btn-sm">👥 Utilisateurs</a>
        <a href="manage-orders.php" class="btn btn-outline-primary btn-sm">🛒 Commandes</a>
        <a href="../index.php" class="btn btn-outline-secondary btn-sm">🏠 Retour au site</a>
        <a href="../logout.php" class="btn btn-outline-danger btn-sm">🚪 Déconnexion</a>
    </nav>

    <hr>

    <!-- Statistiques -->
    <section class="mb-4">
        <h2>📊 Statistiques du site</h2>
        <ul class="list-group">
            <li class="list-group-item">Total produits : <strong><?php echo $products; ?></strong></li>
            <li class="list-group-item">Total catégories : <strong><?php echo $categories; ?></strong></li>
            <li class="list-group-item">Total utilisateurs : <strong><?php echo $users; ?></strong></li>
            <li class="list-group-item">Commandes en attente : <strong><?php echo $orders; ?></strong></li>
        </ul>
    </section>

    <!-- Actions rapides -->
    <section>
        <h2>⚡ Actions rapides</h2>
        <p><a href="manage-products.php?action=add" class="btn btn-success btn-sm">➕ Ajouter un produit</a></p>
        <p><a href="manage-categories.php?action=add" class="btn btn-success btn-sm">➕ Ajouter une catégorie</a></p>
    </section>

</body>
</html>
