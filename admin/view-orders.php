<?php
session_start();
define('SITE_ACCESS', true);

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../pages/login.php");
    exit();
}

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../functions/admin-functions.php';

$message = "";

// ✅ Marquer une commande comme traitée
if (isset($_GET['complete'])) {
    $order_id = intval($_GET['complete']);
    if (update_order_status($conn, $order_id, 'completed')) {
        $message = "✅ Commande #$order_id marquée comme traitée.";
    } else {
        $message = "❌ Erreur lors de la mise à jour.";
    }
}

// ✅ Récupérer les commandes
$orders = get_all_orders($conn);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Commandes - Admin</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>
<body class="container py-4">

    <h1 class="mb-4">🛒 Gestion des Commandes</h1>
    <p><a href="index.php" class="btn btn-secondary btn-sm">⬅ Retour au tableau de bord</a></p>

    <?php if ($message): ?>
        <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <?php if (empty($orders)): ?>
        <div class="alert alert-warning">Aucune commande trouvée.</div>
    <?php else: ?>
        <table class="table table-bordered table-striped">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>Client</th>
                    <th>Email</th>
                    <th>Total</th>
                    <th>Statut</th>
                    <th>Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($orders as $order): ?>
                <tr>
                    <td><?= $order['id'] ?></td>
                    <td><?= htmlspecialchars($order['username'] ?? 'Client inconnu') ?></td>
                    <td><?= htmlspecialchars($order['email'] ?? '-') ?></td>
                    <td><?= isset($order['total']) ? number_format($order['total'], 2, ',', ' ') : '0,00' ?> €</td>
                    <td><?= ucfirst($order['status']) ?></td>
                    <td><?= $order['created_at'] ?></td>
                    <td>
                        <?php if ($order['status'] === 'pending'): ?>
                            <a href="view-orders.php?complete=<?= $order['id'] ?>" class="btn btn-success btn-sm"
                               onclick="return confirm('Marquer cette commande comme traitée ?')">✔ Traiter</a>
                        <?php else: ?>
                            <span class="text-muted">✔ Déjà traitée</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

</body>
</html>
