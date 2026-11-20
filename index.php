
<?php
require_once 'db.php';

try {
    $stmt = $pdo->query(
        "SELECT product_id, product_name,
                product_description, product_cost
         FROM products
         ORDER BY product_id"
    );
    $products = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log($e->getMessage());
    http_response_code(500);
    exit('Unable to load products.');
}

function escapeHtml($value) {
    return htmlspecialchars(
        (string)$value,
        ENT_QUOTES,
        'UTF-8'
    );
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">
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
        .products {
            display: grid;
            grid-template-columns:
                repeat(auto-fit, minmax(220px, 1fr));
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
    </style>
</head>
<body>
    <header>
        <h1>Welcome to My Online Store</h1>
        <p>SDC310L PHP Shopping Cart Project</p>
    </header>

    <main>
        <h2>Our Products</h2>
        <p class="status">
            Shopping cart features are under construction.
        </p>

        <div class="products">
            <?php foreach ($products as $product): ?>
                <div class="product">
                    <p>
                        Product ID:
                        <?= (int)$product['product_id'] ?>
                    </p>

                    <h3>
                        <?= escapeHtml(
                            $product['product_name']
                        ) ?>
                    </h3>

                    <p>
                        <?= escapeHtml(
                            $product['product_description']
                        ) ?>
                    </p>

                    <p class="price">
                        $<?= number_format(
                            (float)$product['product_cost'],
                            2
                        ) ?>
                    </p>
                </div>
            <?php endforeach; ?>
        </div>
    </main>
</body>
</html>
