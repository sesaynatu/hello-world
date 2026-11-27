<?php
// Week 3: Product catalog with database-backed shopping cart
require_once __DIR__ . '/cart_functions.php';

function escapeHtml($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

$error = '';

// Process the Add to Cart form before displaying the page.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $productId = filter_var($_POST['product_id'] ?? null, FILTER_VALIDATE_INT);
    $quantity = filter_var($_POST['quantity'] ?? null, FILTER_VALIDATE_INT);

    if ($action !== 'add') {
        $error = 'Invalid action.';
    } elseif ($productId === false || $productId === null || $productId < 1 ||
              $quantity === false || $quantity === null || $quantity < 1 || $quantity > 999) {
        $error = 'Please select a valid product and quantity (1–999).';
    } else {
        try {
            if (addToCart($pdo, $productId, $quantity)) {
                header('Location: cart.php');
                exit;
            }
            $error = 'The selected product could not be added.';
        } catch (PDOException $e) {
            error_log($e->getMessage());
            $error = 'Unable to add the product to your cart.';
        }
    }
}

try {
    $stmt = $pdo->query(
        "SELECT product_id, product_name,
                product_description, product_cost
         FROM products
         ORDER BY product_id"
    );
    $products = $stmt->fetchAll();
    $cartCount = getCartCount($pdo);
} catch (PDOException $e) {
    error_log($e->getMessage());
    http_response_code(500);
    exit('Unable to load products.');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SDC310L Online Shopping Store</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            background: #f2f5fa;
            color: #24354a;
        }
        header {
            background: #233f67;
            color: white;
            padding: 25px;
            text-align: center;
        }
        main {
            max-width: 1000px;
            margin: 30px auto;
            padding: 20px;
        }
        .top-links { margin-bottom: 24px; }
        .cart-link, button {
            display: inline-block;
            background: #233f67;
            color: white;
            border: none;
            padding: 11px 16px;
            border-radius: 6px;
            text-decoration: none;
            cursor: pointer;
            font-size: 15px;
        }
        button { margin-top: 12px; }
        button:hover, .cart-link:hover { background: #365986; }
        .products {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
        }
        .product {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 8px #00000015;
        }
        .price {
            font-size: 22px;
            font-weight: bold;
            color: #176b43;
        }
        .status {
            color: #795400;
            background: #fff3cc;
            padding: 10px;
            border-radius: 6px;
        }
        .error {
            color: #8a1f11;
            background: #ffe9e6;
            padding: 12px;
            border-radius: 6px;
        }
        .quantity-input { width: 70px; padding: 8px; margin-left: 5px; }
    </style>
</head>
<body>
    <header>
        <h1>Welcome to My Online Store</h1>
        <p>SDC310L PHP Shopping Cart Project</p>
    </header>

    <main>
        <div class="top-links">
            <a class="cart-link" href="cart.php">
                View Shopping Cart (<?= (int)$cartCount ?>)
            </a>
        </div>

        <h2>Our Products</h2>
        <p class="status">Shopping cart is available. Tax, shipping, and checkout will be completed in Week 5.</p>
        <?php if ($error !== ''): ?>
            <p class="error"><?= escapeHtml($error) ?></p>
        <?php endif; ?>

        <div class="products">
            <?php foreach ($products as $product): ?>
                <div class="product">
                    <p>Product ID: <?= (int)$product['product_id'] ?></p>
                    <h3><?= escapeHtml($product['product_name']) ?></h3>
                    <p><?= escapeHtml($product['product_description']) ?></p>
                    <p class="price">$<?= number_format((float)$product['product_cost'], 2) ?></p>

                    <form method="POST" action="index.php">
                        <input type="hidden" name="action" value="add">
                        <input type="hidden" name="product_id" value="<?= (int)$product['product_id'] ?>">
                        <label for="qty-<?= (int)$product['product_id'] ?>">Quantity:</label>
                        <input class="quantity-input" type="number"
                               id="qty-<?= (int)$product['product_id'] ?>"
                               name="quantity" value="1" min="1" max="999" required>
                        <button type="submit">Add to Cart</button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
    </main>
</body>
</html>
