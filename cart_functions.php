
<?php
// Shopping cart functions for SDC310L
require_once __DIR__ . '/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get the current customer's session
function getCartSessionId() {
    return session_id();
}

// Check whether a product exists
function productExists($pdo, $productId) {
    $stmt = $pdo->prepare(
        "SELECT product_id FROM products WHERE product_id = ?"
    );
    $stmt->execute([$productId]);
    return (bool) $stmt->fetch();
}

// Add a product to the shopping cart
function addToCart($pdo, $productId, $quantity = 1) {
    if ($quantity < 1 || !productExists($pdo, $productId)) {
        return false;
    }

    $sql = "INSERT INTO cart_items
                (session_id, product_id, quantity)
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE
                quantity = quantity + VALUES(quantity)";

    $stmt = $pdo->prepare($sql);
    return $stmt->execute([
        getCartSessionId(),
        $productId,
        $quantity
    ]);
}

// Update the quantity of a product in the cart
function updateCartQuantity($pdo, $productId, $quantity) {
    if ($quantity < 0) {
        return false;
    }

    if ($quantity == 0) {
        return removeFromCart($pdo, $productId);
    }

    $sql = "UPDATE cart_items
            SET quantity = ?
            WHERE session_id = ? AND product_id = ?";

    $stmt = $pdo->prepare($sql);
    return $stmt->execute([
        $quantity,
        getCartSessionId(),
        $productId
    ]);
}

// Remove a product from the cart
function removeFromCart($pdo, $productId) {
    $sql = "DELETE FROM cart_items
            WHERE session_id = ? AND product_id = ?";

    $stmt = $pdo->prepare($sql);
    return $stmt->execute([
        getCartSessionId(),
        $productId
    ]);
}

// Retrieve cart items with their product information
function getCartItems($pdo) {
    $sql = "SELECT
                c.product_id,
                p.product_name,
                p.product_description,
                p.product_cost,
                c.quantity,
                (p.product_cost * c.quantity) AS line_total
            FROM cart_items c
            INNER JOIN products p
                ON c.product_id = p.product_id
            WHERE c.session_id = ?
            ORDER BY c.cart_item_id";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([getCartSessionId()]);

    return $stmt->fetchAll();
}

// Get the total number of items ordered
function getCartCount($pdo) {
    $stmt = $pdo->prepare(
        "SELECT COALESCE(SUM(quantity), 0)
         FROM cart_items
         WHERE session_id = ?"
    );

    $stmt->execute([getCartSessionId()]);
    return (int) $stmt->fetchColumn();
}
