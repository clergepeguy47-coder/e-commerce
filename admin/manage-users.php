<?php
session_start();
define('SITE_ACCESS', true);

// 🔐 Vérification admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../pages/login.php");
    exit();
}

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../functions/admin-functions.php';

$message = "";

// ✅ Ajouter un utilisateur
if (isset($_POST['add_user'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];

    $stmt = mysqli_prepare($conn, "INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "ssss", $name, $email, $password, $role);

    if (mysqli_stmt_execute($stmt)) {
        $message = "✅ Utilisateur ajouté avec succès.";
    } else {
        $message = "❌ Erreur : " . mysqli_error($conn);
    }
    mysqli_stmt_close($stmt);
}

// ❌ Supprimer un utilisateur
if (isset($_GET['delete'])) {
    $user_id = intval($_GET['delete']);
    mysqli_query($conn, "DELETE FROM users WHERE id = $user_id");
    $message = "⚠ Utilisateur supprimé.";
}

// 📋 Récupérer tous les utilisateurs
$users = mysqli_query($conn, "SELECT * FROM users ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des Utilisateurs - Admin</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>
<body class="container py-4">

    <h1 class="mb-4">👥 Gestion des Utilisateurs</h1>
    <p><a href="index.php" class="btn btn-secondary btn-sm">⬅ Retour au tableau de bord</a></p>

    <?php if ($message): ?>
        <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <!-- Formulaire ajout utilisateur -->
    <h2>➕ Ajouter un utilisateur</h2>
    <form method="POST" class="mb-4">
        <div class="mb-2"><input type="text" name="name" class="form-control" placeholder="Nom" required></div>
        <div class="mb-2"><input type="email" name="email" class="form-control" placeholder="Email" required></div>
        <div class="mb-2"><input type="password" name="password" class="form-control" placeholder="Mot de passe" required></div>
        <div class="mb-2">
            <select name="role" class="form-select">
                <option value="client">Client</option>
                <option value="admin">Admin</option>
            </select>
        </div>
        <button type="submit" name="add_user" class="btn btn-primary">Ajouter</button>
    </form>

    <hr>

    <!-- Liste des utilisateurs -->
    <h2>📋 Liste des utilisateurs</h2>
    <table class="table table-bordered table-striped">
        <thead class="table-light">
            <tr>
                <th>ID</th>
                <th>Nom</th>
                <th>Email</th>
                <th>Rôle</th>
                <th>Date d’inscription</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
        <?php while ($user = mysqli_fetch_assoc($users)): ?>
            <tr>
                <td><?= $user['id'] ?></td>
                <td><?= htmlspecialchars($user['name']) ?></td>
                <td><?= htmlspecialchars($user['email']) ?></td>
                <td><?= ucfirst($user['role']) ?></td>
                <td><?= $user['created_at'] ?></td>
                <td>
                    <a href="manage-users.php?delete=<?= $user['id'] ?>" class="btn btn-danger btn-sm"
                       onclick="return confirm('Supprimer cet utilisateur ?')">❌ Supprimer</a>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>

</body>
</html>
