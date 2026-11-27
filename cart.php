
<?php
require_once __DIR__ . '/cart_functions.php';

// Escape text before displaying it
function e($value) {
    return htmlspecialchars(
        (string)$value,
        ENT_QUOTES,
        'UTF-8'
    );
}

$error = '';

// Handle shopping cart changes
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $productId = filter_var(
        $_POST['product_id'] ?? null,
        FILTER_VALIDATE_INT
    );

    if (!$productId || $productId < 1) {
        $error = 'Invalid product selected.';
    } else {
        try {
            if ($action === 'remove') {
                removeFromCart($pdo, $productId);
            } elseif ($action === 'update') {
                $quantity = filter_var(
                    $_POST['quantity'] ?? null,
                    FILTER_VALIDATE_INT
                );

                if ($quantity === false ||
                    $quantity === null ||
                    $quantity < 0 ||
                    $quantity > 999) {
                    $error = 'Enter a quantity between 0 and 999.';
                } else {
                    updateCartQuantity(
                        $pdo,
                        $productId,
                        $quantity
                    );
                }
            } else {
                $error = 'Unknown cart action.';
            }

            if ($error === '') {
                header('Location: cart.php');
                exit;
            }
        } catch (PDOException $ex) {
            error_log($ex->getMessage());
            $error = 'Unable to update your cart.';
        }
    }
}

// Retrieve the customer's cart
try {
    $items = getCartItems($pdo);
    $itemCount = getCartCount($pdo);
} catch (PDOException $ex) {
    error_log($ex->getMessage());
    http_response_code(500);
    exit('Unable to load the shopping cart.');
}

// Calculate subtotal
$subtotal = 0;
foreach ($items as $item) {
    $subtotal += (float)$item['line_total'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport"
      content="width=device-width, initial-scale=1.0">
<title>My Shopping Cart - SDC310L</title>
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
        text-align: center;
        padding: 25px;
    }

    main {
        max-width: 950px;
        margin: 30px auto;
        padding: 20px;
    }

    .cart-item {
        background: white;
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 15px;
        box-shadow: 0 2px 8px #00000015;
    }

    .price {
        color: #176b43;
        font-weight: bold;
    }

    .actions {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        align-items: center;
    }

    input[type="number"] {
        width: 75px;
        padding: 8px;
    }

    button, .button {
        background: #233f67;
        color: white;
        border: 0;
        border-radius: 6px;
        padding: 10px 15px;
        cursor: pointer;
        text-decoration: none;
        display: inline-block;
    }

    .remove {
        background: #b42318;
    }

    .summary {
        background: white;
        border-radius: 10px;
        padding: 20px;
        margin-top: 20px;
    }

    .error {
        color: #b42318;
    }
</style>
</head>
<body>

<header>
    <h1>My Shopping Cart</h1>
    <p>SDC310L PHP Shopping Cart Project</p>
</header>

<main>
    <p>
        <a class="button" href="index.php">
            Continue Shopping
        </a>
    </p>

    <?php if ($error !== ''): ?>
        <p class="error"><?= e($error) ?></p>
    <?php endif; ?>

    <?php if (empty($items)): ?>
        <div class="cart-item">
            <p>Your shopping cart is empty.</p>
        </div>
    <?php else: ?>

        <?php foreach ($items as $item): ?>
            <div class="cart-item">
                <h2><?= e($item['product_name']) ?></h2>

                <p>
                    Product ID:
                    <?= (int)$item['product_id'] ?>
                </p>

                <p class="price">
                    Individual Price:
                    $<?= number_format(
                        (float)$item['product_cost'],
                        2
                    ) ?>
                </p>

                <p>
                    Line Total:
                    $<?= number_format(
                        (float)$item['line_total'],
                        2
                    ) ?>
                </p>

                <div class="actions">
                    <form method="POST" action="cart.php">
                        <input type="hidden"
                               name="action"
                               value="update">

                        <input type="hidden"
                               name="product_id"
                               value="<?= (int)$item['product_id'] ?>">

                        <label>
                            Quantity:
                            <input type="number"
                                   name="quantity"
                                   min="0"
                                   max="999"
                                   value="<?= (int)$item['quantity'] ?>">
                        </label>

                        <button type="submit">
                            Update Quantity
                        </button>
                    </form>

                    <form method="POST" action="cart.php">
                        <input type="hidden"
                               name="action"
                               value="remove">

                        <input type="hidden"
                               name="product_id"
                               value="<?= (int)$item['product_id'] ?>">

                        <button type="submit" class="remove">
                            Remove
                        </button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>

    <?php endif; ?>

    <div class="summary">
        <h2>Order Summary</h2>
        <p>Total Items: <?= $itemCount ?></p>
        <h3>
            Subtotal:
            $<?= number_format($subtotal, 2) ?>
        </h3>
        <p>Tax and shipping will be completed in Week 5.</p>
    </div>
</main>
</body>
</html>
