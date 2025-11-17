 <?php
// Inclure la configuration et la connexion à la base de données
require_once("../config.php");
require_once("../includes/database.php");
include_once("../includes/header.php");

// Requête pour récupérer les catégories
$query = "SELECT id, name, description, image FROM categories ORDER BY name ASC";
$result = $conn->query($query);
?>

<div class="container">
    <h1>Nos catégories</h1>
    <div class="categories-container">

        <?php if ($result && $result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="category-card">
                    <a href="products.php?category=<?= $row['id'] ?>">
                        <img src="../images/products/<?= htmlspecialchars($row['image']) ?>" alt="<?= htmlspecialchars($row['name']) ?>">
                        <h2><?= htmlspecialchars($row['name']) ?></h2>
                        <p><?= htmlspecialchars($row['description']) ?></p>
                    </a>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>Aucune catégorie disponible pour le moment.</p>
        <?php endif; ?>

    </div>
</div>

<?php include_once("../includes/footer.php"); ?>

<style>
/* Tu peux déplacer ce style dans ton css/style.css */
.container {
    width: 90%;
    margin: 0 auto;
    padding: 20px;
}

.categories-container {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
}

.category-card {
    width: 250px;
    background: #f7f7f7;
    border: 1px solid #ddd;
    border-radius: 8px;
    text-align: center;
    padding: 15px;
    transition: 0.3s;
}

.category-card:hover {
    transform: scale(1.05);
    box-shadow: 0 0 10px rgba(0,0,0,0.1);
}

.category-card img {
    width: 100%;
    height: 160px;
    object-fit: cover;
    border-radius: 8px;
}
</style>