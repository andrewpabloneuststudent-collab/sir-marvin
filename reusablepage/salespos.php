<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/../conn/database.php";
require_once __DIR__ . "/../function/workingpos.php";

$product = new Product($db);
$products = $product->getProducts();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>MMBPOS - POS</title>
    <link rel="stylesheet" href="../css/pos.css">
</head>

<body>

    <div class="container">

        <!-- LEFT -->
        <div class="products">

            <div class="search">
                <input type="text" id="searchBox" placeholder="Scan or search product...">
            </div>

            <!-- CATEGORY -->
            <div class="categories">
                <button class="cat-btn active">All</button>
                <button class="cat-btn">Beverages</button>
                <button class="cat-btn">Medicine</button>
            </div>

            <!-- PRODUCTS -->
            <div class="product-grid">
                <?php foreach ($products as $row): ?>

                    <div class="product" data-id="<?= $row['id'] ?>"
                        data-name="<?= htmlspecialchars($row['product_name']) ?>" data-price="<?= $row['gross_price'] ?>"
                        data-net="<?= $row['net_price'] ?>" data-discountable="<?= $row['is_discountable'] ?>"
                        data-vatable="<?= $row['is_vatable'] ?>">

                        <?php
                        $image = !empty($row['image_product'])
                            ? "../uploads/" . $row['image_product']
                            : "https://via.placeholder.com/100";
                        ?>

                        <img src="<?= $image ?>" alt="product">

                        <strong><?= htmlspecialchars($row['product_name']) ?></strong><br>
                        ₱<?= number_format($row['gross_price'], 2) ?>

                    </div>

                <?php endforeach; ?>

            </div>

        </div>

        <!-- RIGHT -->
        <div class="cart">

            <h2>Cart</h2>

            <div class="cart-items" id="cartItems"></div>

            <!-- DISCOUNT -->
            <div class="discount-section">
                <label>Discount</label>
                <select id="discountType">
                    <option value="none">None</option>
                    <option value="senior">Senior</option>
                    <option value="pwd">PWD</option>
                </select>

                <div id="customerInfo" style="display:none;">
                    <input type="text" placeholder="Name">
                    <input type="text" placeholder="ID Number">
                </div>
            </div>

            <div class="total" id="totalDisplay">TOTAL: ₱0.00</div>

            <button class="checkout">Checkout</button>
            <button class="clear" onclick="clearCart()">Clear</button>

        </div>

    </div>

    <script src="../js/buttonpos.js"></script>

</body>

</html>