<?php
/**
 * Fichier : functions/product-functions.php
 * Description : Fonctions de gestion des produits et catégories
 */
// Empêche l'accès direct à ce fichier
if (basename($_SERVER['PHP_SELF']) === basename(__FILE__)) {
    header("Location: ../pages/products.php");
    exit;
}
// Inclure les fichiers nécessaires
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/database.php';

/**
 * Récupérer toutes les catégories
 * @return array - Tableau des catégories
 */
if (!function_exists('get_all_categories')) {
    function get_all_categories() {
        global $conn;
        $sql = "SELECT id, name FROM categories ORDER BY name ASC";
        $result = mysqli_query($conn, $sql);

        $categories = [];
        if ($result && mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $categories[] = $row;
            }
        }

        return $categories;
    }
}

/**
 * Récupérer tous les produits
 * @return array - Tableau des produits
 */
if (!function_exists('get_All_Products')) {
    function get_All_Products() {
        global $conn;
        $query = "SELECT * FROM products";
        $result = mysqli_query($conn, $query);

        $products = [];
        if ($result && mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $products[] = $row;
            }
        }

        return $products;
    }
}
/**
 * Récupérer un produit par son ID
 * @param int $productId - ID du produit
 * @return array|null - Données du produit ou null si non trouvé
 */
function get_Product_ById($productId) {
    global $conn;
    
    $query = "SELECT p.*, c.name as category_name 
              FROM products p 
              LEFT JOIN categories c ON p.category_id = c.id 
              WHERE p.id = ? AND p.status = 'active'";
    
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $productId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    return mysqli_fetch_assoc($result);
}

/**
 * Récupérer les produits d'une catégorie
 * @param int $categoryId - ID de la catégorie
 * @param int $limit - Limite de résultats (optionnel)
 * @return array - Tableau des produits
 */
function get_Products_ByCategory($categoryId, $limit = null) {
    global $conn;
    
    $query = "SELECT p.*, c.name as category_name 
              FROM products p 
              LEFT JOIN categories c ON p.category_id = c.id 
              WHERE p.category_id = ? AND p.status = 'active' 
              ORDER BY p.created_at DESC";
    
    if ($limit !== null) {
        $query .= " LIMIT " . intval($limit);
    }
    
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $categoryId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $products = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $products[] = $row;
    }
    
    return $products;
}

/**
 * Récupérer toutes les catégories disponibles
 * @return array - Tableau des catégories
 */
if (!function_exists('get_all_categories')) {
    function get_all_categories() {
        global $conn;
        $sql = "SELECT id, name FROM categories ORDER BY name ASC";
        $result = mysqli_query($conn, $sql);
        
        $categories = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $categories[] = $row;
        }
        return $categories;
    }
}


/**
 * Rechercher des produits par nom ou description
 * @param string $searchTerm - Terme de recherche
 * @return array - Tableau des produits trouvés
 */
function searchProducts($searchTerm) {
    global $conn;
    
    $searchTerm = '%' . $searchTerm . '%';
    
    $query = "SELECT p.*, c.name as category_name 
              FROM products p 
              LEFT JOIN categories c ON p.category_id = c.id 
              WHERE (p.name LIKE ? OR p.description LIKE ?) 
              AND p.status = 'active' 
              ORDER BY p.created_at DESC";
    
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "ss", $searchTerm, $searchTerm);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $products = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $products[] = $row;
    }
    
    return $products;
}

/**
 * Récupérer les produits les plus récents
 * @param int $limit - Nombre de produits à récupérer
 * @return array - Tableau des produits récents
 */
function get_Latest_Products($limit = 8) {
    global $conn;
    
    $query = "SELECT p.*, c.name as category_name 
              FROM products p 
              LEFT JOIN categories c ON p.category_id = c.id 
              WHERE p.status = 'active' 
              ORDER BY p.created_at DESC 
              LIMIT ?";
    
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $limit);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $products = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $products[] = $row;
    }
    
    return $products;
}

/**
 * Récupérer les produits en promotion
 * @param int $limit - Limite de résultats (optionnel)
 * @return array - Tableau des produits en promotion
 */
function get_Featured_Products($limit = null) {
    global $conn;
    
    $query = "SELECT p.*, c.name as category_name 
              FROM products p 
              LEFT JOIN categories c ON p.category_id = c.id 
              WHERE p.is_featured = 1 AND p.status = 'active' 
              ORDER BY p.created_at DESC";
    
    if ($limit !== null) {
        $query .= " LIMIT " . intval($limit);
    }
    
    $result = mysqli_query($conn, $query);
    $products = [];
    
    while ($row = mysqli_fetch_assoc($result)) {
        $products[] = $row;
    }
    
    return $products;
}

/**
 * Récupérer toutes les catégories actives
 * @return array - Tableau des catégories
 */
function get_All_Categories() {
    global $conn;
    
    $query = "SELECT * FROM categories WHERE status = 'active' ORDER BY name ASC";
    $result = mysqli_query($conn, $query);
    
    $categories = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $categories[] = $row;
    }
    
    return $categories;
}

/**
 * Récupérer une catégorie par son ID
 * @param int $categoryId - ID de la catégorie
 * @return array|null - Données de la catégorie ou null
 */
function getCategoryById($categoryId) {
    global $conn;
    
    $query = "SELECT * FROM categories WHERE id = ? AND status = 'active'";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $categoryId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    return mysqli_fetch_assoc($result);
}

/**
 * Compter le nombre de produits par catégorie
 * @param int $categoryId - ID de la catégorie
 * @return int - Nombre de produits
 */
function countProductsByCategory($categoryId) {
    global $conn;
    
    $query = "SELECT COUNT(*) as total FROM products 
              WHERE category_id = ? AND status = 'active'";
    
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $categoryId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    
    return $row['total'];
}

/**
 * Vérifier la disponibilité d'un produit
 * @param int $productId - ID du produit
 * @return bool - True si disponible, False sinon
 */
function isProductAvailable($productId) {
    global $conn;
    
    $query = "SELECT stock FROM products WHERE id = ? AND status = 'active'";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $productId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($row = mysqli_fetch_assoc($result)) {
        return $row['stock'] > 0;
    }
    
    return false;
}

/**
 * Formater le prix en euros
 * @param float $price - Prix à formater
 * @return string - Prix formaté (ex: 19,99 €)
 */
function formatPrice($price) {
  return number_format((float)$price, 2, ',', ' ') . ' €';

}

/**
 * Récupérer le chemin de l'image d'un produit
 * @param string $imageName - Nom du fichier image
 * @return string - Chemin complet de l'image
 */
function get_Product_Image_Path($imageName) {
    if (empty($imageName)) {
        return 'images/products/default.jpg';
    }
    return 'images/products/' . $imageName;
}

/**
 * Afficher le badge de disponibilité
 * @param int $stock - Quantité en stock
 * @return string - HTML du badge
 */
function getStockBadge($stock) {
    if ($stock > 10) {
        return '<span class="badge bg-success">En stock</span>';
    } elseif ($stock > 0) {
        return '<span class="badge bg-warning">Stock limité</span>';
    } else {
        return '<span class="badge bg-danger">Rupture de stock</span>';
    }
}

/**
 * Récupérer les produits similaires (même catégorie)
 * @param int $productId - ID du produit actuel
 * @param int $categoryId - ID de la catégorie
 * @param int $limit - Nombre de produits à récupérer
 * @return array - Tableau des produits similaires
 */
function getSimilarProducts($productId, $categoryId, $limit = 4) {
    global $conn;
    
    $query = "SELECT p.*, c.name as category_name 
              FROM products p 
              LEFT JOIN categories c ON p.category_id = c.id 
              WHERE p.category_id = ? AND p.id != ? AND p.status = 'active' 
              ORDER BY RAND() 
              LIMIT ?";
    
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "iii", $categoryId, $productId, $limit);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $products = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $products[] = $row;
    }
    
    return $products;
}

/**
 * Filtrer les produits par fourchette de prix
 * @param float $minPrice - Prix minimum
 * @param float $maxPrice - Prix maximum
 * @return array - Tableau des produits filtrés
 */
function getProductsByPriceRange($minPrice, $maxPrice) {
    global $conn;
    
    $query = "SELECT p.*, c.name as category_name 
              FROM products p 
              LEFT JOIN categories c ON p.category_id = c.id 
              WHERE p.price BETWEEN ? AND ? AND p.status = 'active' 
              ORDER BY p.price ASC";
    
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "dd", $minPrice, $maxPrice);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $products = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $products[] = $row;
    }
    
    return $products;
}

/**
 * Compter le nombre total de produits actifs
 * @return int - Nombre total de produits
 */
function getTotalProductsCount() {
    global $conn;
    
    $query = "SELECT COUNT(*) as total FROM products WHERE status = 'active'";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);
    
    return $row['total'];
}

/**
 * Trier les produits
 * @param string $sortBy - Critère de tri (price_asc, price_desc, name_asc, name_desc, newest)
 * @return array - Tableau des produits triés
 */
function getProductsSorted($sortBy = 'newest') {
    global $conn;
    
    $orderClause = "p.created_at DESC"; // Par défaut: plus récents
    
    switch ($sortBy) {
        case 'price_asc':
            $orderClause = "p.price ASC";
            break;
        case 'price_desc':
            $orderClause = "p.price DESC";
            break;
        case 'name_asc':
            $orderClause = "p.name ASC";
            break;
        case 'name_desc':
            $orderClause = "p.name DESC";
            break;
        case 'newest':
        default:
            $orderClause = "p.created_at DESC";
            break;
    }
    $query = "SELECT p.*, c.name as category_name 
              FROM products p 
              LEFT JOIN categories c ON p.category_id = c.id 
              WHERE p.status = 'active' 
              ORDER BY $orderClause";
    
    $result = mysqli_query($conn, $query);
    $products = [];
    
    while ($row = mysqli_fetch_assoc($result)) {
        $products[] = $row;
    }
    
    return $products;
}
?>