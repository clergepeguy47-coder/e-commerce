 <?php
if (basename($_SERVER['PHP_SELF']) === basename(__FILE__)) {
    exit('Accès direct interdit');
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config.php';
//require_once __DIR__ . '/../includes/database.php';

function initializeCart() {
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = array();
    }
}

function addToCart($productId, $conn, $quantity = 1) {
    initializeCart();
    $stmt = mysqli_prepare($conn, "SELECT id, name, price, stock FROM products WHERE id = ? AND is_active = 1");
    mysqli_stmt_bind_param($stmt, "i", $productId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($product = mysqli_fetch_assoc($result)) {
        $currentQty = isset($_SESSION['cart'][$productId]) ? $_SESSION['cart'][$productId] : 0;
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

if (!function_exists('removeFromCart')) {
    function removeFromCart($productId) {
        initializeCart();
        if (isset($_SESSION['cart'][$productId])) {
            unset($_SESSION['cart'][$productId]);
        }
    }
}

if (!function_exists('get_cart_count')) {
    function get_cart_count() {
        initializeCart();
        return array_sum($_SESSION['cart']);
    }
}

if (!function_exists('get_cart_total')) {
    function get_cart_total() {
        initializeCart();
        $total = 0.0;
        global $connection;

        foreach ($_SESSION['cart'] as $productId => $quantity) {
            $stmt = mysqli_prepare($connection, "SELECT price FROM products WHERE id = ?");
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
}

function getCartSubtotal($conn) {
    return get_cart_total();
}

function getShippingCost($subtotal) {
    if ($subtotal >= 50) {
        return 0;
    }
    return 5.00;
}

function getCartTotalWithShipping($conn) {
    $subtotal = getCartSubtotal($conn);
    $shipping = getShippingCost($subtotal);
    $total = $subtotal + $shipping;

    return array(
        'subtotal' => $subtotal,
        'shipping' => $shipping,
        'total' => $total
    );
}

function isCartEmpty() {
    initializeCart();
    return empty($_SESSION['cart']);
}

function getCartItems($conn) {
    initializeCart();
    $items = array();

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

function validateCartStock($conn) {
    initializeCart();
    $errors = array();

    if (empty($_SESSION['cart'])) {
        return array('valid' => true, 'errors' => $errors);
    }

    $cartItems = getCartItems($conn);

    foreach ($cartItems as $item) {
        if ($item['quantity'] > $item['stock']) {
            $errors[] = "Stock insuffisant pour {$item['name']}. Disponible : {$item['stock']}";
        }
    }

    return array(
        'valid' => empty($errors),
        'errors' => $errors
    );
}

function createOrderFromCart($conn, $userId, $shippingInfo) {
    $validation = validateCartStock($conn);
    if (!$validation['valid']) {
        return false;
    }

    $cartItems = getCartItems($conn);
    if (empty($cartItems)) {
        return false;
    }

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

            $stmt = mysqli_prepare($conn, "UPDATE products SET stock = stock - ? WHERE id = ?");
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

function clearCart() {
    $_SESSION['cart'] = array();
}

function formatPrice($amount) {
    return number_format($amount, 2, ',', ' ') . ' €';
}
?>