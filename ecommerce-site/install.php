<?php
// Installation de la base de données pour le site e-commerce
 require_once __DIR__. '/config.php';
 
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

if ($conn->query($sql) === TRUE) {
    echo "✅ Table 'users' créée<br>";
} else {
    echo "❌ Erreur table users: " . $conn->error . "<br>";
}

// Table des catégories
$sql = "CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    description TEXT,
    image VARCHAR(255),
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if ($conn->query($sql) === TRUE) {
    echo "✅ Table 'categories' créée<br>";
} else {
    echo "❌ Erreur table categories: " . $conn->error . "<br>";
}

// Table des produits
$sql = "CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(200) NOT NULL,
    description TEXT,
    prix DECIMAL(10, 2) NOT NULL,
    stock INT DEFAULT 0,
    image VARCHAR(255),
    category_id INT,
    date_ajout TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
    INDEX idx_category (category_id),
    INDEX idx_prix (prix)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if ($conn->query($sql) === TRUE) {
    echo "✅ Table 'products' créée<br>";
} else {
    echo "❌ Erreur table products: " . $conn->error . "<br>";
}

// Table du panier
$sql = "CREATE TABLE IF NOT EXISTS cart (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    quantite INT DEFAULT 1,
    date_ajout TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY unique_cart (user_id, product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if ($conn->query($sql) === TRUE) {
    echo "✅ Table 'cart' créée<br>";
} else {
    echo "❌ Erreur table cart: " . $conn->error . "<br>";
}

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

if ($conn->query($sql) === TRUE) {
    echo "✅ Table 'orders' créée<br>";
} else {
    echo "❌ Erreur table orders: " . $conn->error . "<br>";
}

// Table des détails de commande
$sql = "CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantite INT NOT NULL,
    prix_unitaire DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if ($conn->query($sql) === TRUE) {
    echo "✅ Table 'order_items' créée<br>";
} else {
    echo "❌ Erreur table order_items: " . $conn->error . "<br>";
}

// Insérer des catégories par défaut
$sql = "INSERT IGNORE INTO categories (id, nom, description) VALUES
(1, 'Vehicule', 'Tous types de véhicules : voitures, motos, utilitaires, et accessoires automobiles'),
(2, 'Vêtements', 'Mode et habillement pour hommes, femmes et enfants'),
(3, 'Electronique', 'Appareils électroniques de qualité : téléphones, ordinateurs, accessoires'),
(4, 'Sport', 'Équipements sportifs, vêtements et accessoires pour toutes disciplines'),
(5, 'Livres', 'Livres, magazines, BD et ouvrages spécialisés')";

if ($conn->query($sql) === TRUE) {
    echo "✅ Catégories par défaut insérées<br>";
}

// Insérer des produits exemple
$sql = "INSERT IGNORE INTO products (id, nom, description, prix, stock, category_id) VALUES
(1, 'voiture', 'neuve', 50000.99, 50, 1),
(2, 'T-shirt Bleu', 'T-shirt 100% coton taille M', 30.00, 100, 2),
(3, 'iphone', 'Dernier génération avec 128GB', 1200.99, 75, 3),
(4, 'Ballon de Football', 'Ballon de foot professionnel', 29.99, 30, 4),
(5, 'Roman Bestseller', 'Roman à succès de l\'année', 15.99, 200, 5)";

if ($conn->query($sql) === TRUE) {
    echo "✅ Produits exemple insérés<br>";
}

// Créer un compte admin par défaut
$admin_password = password_hash('admin123', PASSWORD_DEFAULT);
$sql = "INSERT IGNORE INTO users (id, username, email, password, role) VALUES
(1, 'admin', 'admin@ecommerce.com', '$admin_password', 'admin')";

if ($conn->query($sql) === TRUE) {
    echo "✅ Compte administrateur créé<br>";
    echo "<p><strong>Identifiants admin:</strong><br>";
    echo "Username: admin<br>";
    echo "Password: admin123</p>";
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