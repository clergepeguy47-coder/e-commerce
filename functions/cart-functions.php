<?php
// functions/cart-functions.php

// Démarre la session si elle n'est pas déjà active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Empêche l'accès direct à ce fichier
if (basename($_SERVER['PHP_SELF']) === basename(__FILE__)) {
    header("Location: ../pages/cart.php");
    exit;
}

// Inclut la connexion à la base de données
require_once __DIR__ . '/../includes/database.php';


// Vérifie que la connexion est valide
if (!isset($conn) || !($conn instanceof mysqli)) {
    die("Connexion invalide à la base de données.");
}

// Initialise le panier
function initializeCart() {
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
}


// Ajoute un produit au panier
function addToCart($productId, $conn, $quantity = 1) {
    initializeCart();

    // Vérifie que $conn est bien une connexion MySQLi
    if (!($conn instanceof mysqli)) {
        die("Connexion invalide : objet mysqli attendu.");
    }

    // Prépare la requête
    $query = "SELECT id, name, price, stock FROM products WHERE id = ? AND is_active = 1";
    $stmt = mysqli_prepare($conn, $query);

    if (!$stmt) {
        die("Erreur préparation requête : " . mysqli_error($conn));
    }

    mysqli_stmt_bind_param($stmt, "i", $productId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($product = mysqli_fetch_assoc($result)) {
        $currentQty = $_SESSION['cart'][$productId] ?? 0;
        $newQty = $currentQty + $quantity;

        if ($newQty <= $product['stock']) {
            $_SESSION['cart'][$productId] = $newQty;
            mysqli_stmt_close($stmt);
            return true;
        }
    }

    mysqli_stmt_close($stmt);
    return false;
}


// Supprime un produit du panier
function removeFromCart($productId) {
    initializeCart();
    unset($_SESSION['cart'][$productId]);
}

// Retourne le nombre total d'articles dans le panier

function get_cart_count() {
    initializeCart();
    return array_sum($_SESSION['cart']);
}
// Calcule le total du panier
function get_Cart_Total($conn) {
    initializeCart();
    $total = 0.0;

    foreach ($_SESSION['cart'] as $productId => $quantity) {
        $stmt = mysqli_prepare($conn, "SELECT price FROM products WHERE id = ?");
        if (!$stmt) {
            continue;
        }

        mysqli_stmt_bind_param($stmt, "i", $productId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $product = mysqli_fetch_assoc($result);

        if ($product) {
            $total += $product['price'] * $quantity;
        }

        mysqli_stmt_close($stmt);
    }

    return $total;
}
// Retourne le sous-total
function getCartSubtotal($conn) {
    return getCartTotal($conn);
}

// Calcule les frais de livraison
function getShippingCost($subtotal) {
    return ($subtotal >= 50) ? 0 : 5.00;
}

// Calcule le total avec livraison
function getCartTotalWithShipping($conn) {
    $subtotal = getCartSubtotal($conn);
    $shipping = getShippingCost($subtotal);
    $total = $subtotal + $shipping;

    return [
        'subtotal' => $subtotal,
        'shipping' => $shipping,
        'total' => $total
    ];
}

// Vérifie si le panier est vide
function isCartEmpty() {
    initializeCart();
    return empty($_SESSION['cart']);
}

// Récupère les détails des produits du panier
function getCartItems($conn) {
    initializeCart();
    $items = [];

    foreach ($_SESSION['cart'] as $productId => $quantity) {
        $stmt = mysqli_prepare($conn, "SELECT id, name, price, stock FROM products WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $productId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $product = mysqli_fetch_assoc($result);

        if ($product) {
            $product['quantity'] = $quantity;
            $items[] = $product;
        }

        mysqli_stmt_close($stmt);
    }

    return $items;
}

// Vérifie les stocks du panier
function validateCartStock($conn) {
    initializeCart();
    $errors = [];

    foreach (getCartItems($conn) as $item) {
        if ($item['quantity'] > $item['stock']) {
            $errors[] = "Stock insuffisant pour {$item['name']} (disponible : {$item['stock']})";
        }
    }

    return [
        'valid' => empty($errors),
        'errors' => $errors
    ];
}

// Crée une commande à partir du panier
function createOrderFromCart($conn, $userId, $shippingInfo) {
    $validation = validateCartStock($conn);
    if (!$validation['valid']) return false;

    $cartItems = getCartItems($conn);
    if (empty($cartItems)) return false;

    $totals = getCartTotalWithShipping($conn);
    mysqli_begin_transaction($conn);

    try {
        $stmt = mysqli_prepare($conn,
            "INSERT INTO orders (user_id, total_amount, shipping_address, shipping_city, shipping_postal_code, shipping_country, order_status, created_at)
             VALUES (?, ?, ?, ?, ?, ?, 'pending', NOW())"
        );
        mysqli_stmt_bind_param($stmt, "idssss",
            $userId,
            $totals['total'],
            $shippingInfo['address'],
            $shippingInfo['city'],
            $shippingInfo['postal_code'],
            $shippingInfo['country']
        );
        mysqli_stmt_execute($stmt);
        $orderId = mysqli_insert_id($conn);
        mysqli_stmt_close($stmt);

        foreach ($cartItems as $item) {
            $stmt = mysqli_prepare($conn,
                "INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)"
            );
            mysqli_stmt_bind_param($stmt, "iiid", $orderId, $item['id'], $item['quantity'], $item['price']);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);

            $stmt = mysqli_prepare($conn,
                "UPDATE products SET stock = stock - ? WHERE id = ?"
            );
            mysqli_stmt_bind_param($stmt, "ii", $item['quantity'], $item['id']);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }

        mysqli_commit($conn);
        clearCart();
        return $orderId;

    } catch (Exception $e) {
        mysqli_rollback($conn);
        return false;
    }
}

// Vide le panier
function clearCart() {
    $_SESSION['cart'] = [];
}
?>