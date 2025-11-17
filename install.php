<?php
// Installation de la base de données pour le site e-commerce
require_once __DIR__ . '/config.php'; // ⚠ corriger _DIR_ en __DIR__

// Configuration de la base de données
$host = 'localhost';
$username = 'root';  // Changez si nécessaire
$password = '';      // Changez si nécessaire
$dbname = 'ecommerce_db';

// Connexion sans sélectionner de base de données
$conn = new mysqli($host, $username, $password);

// Vérifier la connexion
if ($conn->connect_error) {
    die("Erreur de connexion: " . $conn->connect_error);
}

echo "<h2>Installation de la base de données</h2>";

// Créer la base de données si elle n'existe pas
$sql = "CREATE DATABASE IF NOT EXISTS $dbname CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
if ($conn->query($sql) === TRUE) {
    echo "✅ Base de données '$dbname' créée ou déjà existante<br>";
} else {
    die("❌ Erreur création base de données: " . $conn->error);
}

// Sélectionner la base de données
$conn->select_db($dbname);

// Table des utilisateurs
$sql = "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    nom VARCHAR(100),
    prenom VARCHAR(100),
    telephone VARCHAR(20),
    adresse TEXT,
    ville VARCHAR(100),
    code_postal VARCHAR(10),
    role ENUM('client', 'admin') DEFAULT 'client',
    date_inscription TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_username (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

$conn->query($sql);

// Table des catégories
$sql = "CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    description TEXT,
    image VARCHAR(255),
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

$conn->query($sql);

// Table des produits
$sql = "CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    stock INT DEFAULT 0,
    image VARCHAR(255),   
    category_id INT,
    status VARCHAR(20) DEFAULT 'active',
    is_featured TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
    INDEX idx_category (category_id),
    INDEX idx_price (price)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

$conn->query($sql);

// Table du panier
$sql = "CREATE TABLE IF NOT EXISTS cart (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT DEFAULT 1,
    is_active TINYINT(1) DEFAULT 1,
    date_added TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY unique_cart (user_id, product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

$conn->query($sql);

// Table des commandes
$sql = "CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    total DECIMAL(10, 2) NOT NULL,
    statut ENUM('en_attente', 'confirmee', 'expediee', 'livree', 'annulee') DEFAULT 'en_attente',
    adresse_livraison TEXT NOT NULL,
    date_commande TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_statut (statut)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

$conn->query($sql);

// Table des détails de commande
$sql = "CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    unit_price DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

$conn->query($sql);

// Insérer des catégories par défaut
$sql = "INSERT IGNORE INTO categories (id, nom, description) VALUES
(1, 'Électronique', 'Appareils et gadgets électroniques'),
(2, 'Vêtements', 'Mode et habillement'),
(3, 'Automobile', 'Toutes les marques'),
(4, 'Sport', 'Équipements sportifs'),
(5, 'Livres', 'Livres et magazines')";
$conn->query($sql);

// Insérer des produits exemple
$sql = "INSERT IGNORE INTO products (id, name, description, price, stock, category_id) VALUES
(1, 'Iphone', 'iPhone dernière génération avec 128GB', 599.99, 20, 1),
(2, 'T-shirt Bleu', 'T-shirt 100% coton taille M', 20, 100, 2),
(3, 'Voiture', 'Voiture récente dernière génération', 200000, 15, 3),
(4, 'Ballon de Football', 'Ballon de football professionnel', 29.94, 30, 4),
(5, 'Roman Bestseller', 'Roman à succès de l'année', 15, 20, 5)";
$conn->query($sql);

// Créer un compte admin par défaut
$admin_password = password_hash('admin123', PASSWORD_DEFAULT);
$sql = "INSERT IGNORE INTO users (id, username, email, password, role) VALUES
(1, 'admin1', 'clergepeguy47@gmail.com', '$admin_password', 'admin'),
(2, 'admin2', 'kalanbe47@gmail.com', '$admin_password', 'admin')";

$conn->query($sql);

$sql = "SELECT c.id AS cart_id, c.quantity, c.product_id,
               p.name, p.price, p.image, p.stock,
               (c.quantity * p.price) AS subtotal
        FROM cart c
        INNER JOIN products p ON c.product_id = p.id
        WHERE c.user_id = 1
        ORDER BY c.id DESC";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

while ($row = mysqli_fetch_assoc($result)) {
    echo "Produit : " . $row['name'] . " | Quantité : " . $row['quantity'] . " | Sous-total : " . $row['subtotal'] . "<br>";
}

echo "<hr>";
echo "<h3>✅ Installation terminée avec succès !</h3>";
echo "<p>Vous pouvez maintenant:</p>";
echo "<ul>";
echo "<li><a href='index.php'>Accéder au site</a></li>";
echo "<li><a href='pages/login.php'>Se connecter</a></li>";
echo "</ul>";
echo "<p><strong>⚠ IMPORTANT:</strong> Supprimez ou renommez le fichier install.php pour des raisons de sécurité !</p>";

$conn->close();
?>
