<?php
if (!defined('SITE_ACCESS')) {
    die('Accès interdit');
}

//////////////////////
// 📊 Statistiques
//////////////////////

function get_total_products(mysqli $conn): int {
    $res = mysqli_query($conn, "SELECT COUNT(*) AS total FROM products");
    return mysqli_fetch_assoc($res)['total'] ?? 0;
}

function get_total_categories(mysqli $conn): int {
    $res = mysqli_query($conn, "SELECT COUNT(*) AS total FROM categories");
    return mysqli_fetch_assoc($res)['total'] ?? 0;
}

function get_total_users(mysqli $conn): int {
    $res = mysqli_query($conn, "SELECT COUNT(*) AS total FROM users");
    return mysqli_fetch_assoc($res)['total'] ?? 0;
}

function get_pending_orders(mysqli $conn): int {
    $res = mysqli_query($conn, "SELECT COUNT(*) AS total FROM orders WHERE status = 'pending'");
    return mysqli_fetch_assoc($res)['total'] ?? 0;
}

//////////////////////
// 📦 Produits
//////////////////////

function get_all_products(mysqli $conn): array {
    $res = mysqli_query($conn, "SELECT * FROM products ORDER BY id DESC");
    return mysqli_fetch_all($res, MYSQLI_ASSOC);
}

function get_product_by_id(mysqli $conn, int $id): ?array {
    $res = mysqli_query($conn, "SELECT * FROM products WHERE id = $id");
    return mysqli_fetch_assoc($res) ?: null;
}

function delete_product(mysqli $conn, int $id): bool {
    return mysqli_query($conn, "DELETE FROM products WHERE id = $id");
}

//////////////////////
// 📂 Catégories
//////////////////////

function get_all_categories(mysqli $conn): array {
    $res = mysqli_query($conn, "SELECT * FROM categories ORDER BY name ASC");
    return mysqli_fetch_all($res, MYSQLI_ASSOC);
}

function get_category_by_id(mysqli $conn, int $id): ?array {
    $res = mysqli_query($conn, "SELECT * FROM categories WHERE id = $id");
    return mysqli_fetch_assoc($res) ?: null;
}

function delete_category(mysqli $conn, int $id): bool {
    return mysqli_query($conn, "DELETE FROM categories WHERE id = $id");
}

//////////////////////
// 🛒 Commandes
//////////////////////

function get_all_orders(mysqli $conn): array {
    $res = mysqli_query($conn, "SELECT * FROM orders ORDER BY created_at DESC");
    return mysqli_fetch_all($res, MYSQLI_ASSOC);
}

function update_order_status(mysqli $conn, int $id, string $status): bool {
    $stmt = mysqli_prepare($conn, "UPDATE orders SET status = ? WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "si", $status, $id);
    $success = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $success;
}
// 🔍 Récupérer une commande par ID
function get_order_by_id(mysqli $conn, int $order_id): ?array {
    $sql = "SELECT o.*, u.username FROM orders o 
            INNER JOIN users u ON o.user_id = u.id 
            WHERE o.id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $order_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $order = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    return $order ?: null;
}

// 📦 Récupérer les produits d'une commande
function get_order_items(mysqli $conn, int $order_id): array {
    $sql = "SELECT oi.*, p.name, p.price, p.image 
            FROM order_items oi 
            INNER JOIN products p ON oi.product_id = p.id 
            WHERE oi.order_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $order_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $items = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $items[] = $row;
    }
    mysqli_stmt_close($stmt);
    return $items;
}
