<?php
require_once __DIR__ . "/../function/addprodfunct.php";
require_once __DIR__ . "/../conn/connection_links.php";

use Classes\ProductManagement;

$product = new ProductManagement($db);

// ✅ HANDLE UPDATE FIRST (NO OUTPUT BEFORE THIS)
if ($product->updateStock()) {
    echo "<script>
    setTimeout(function() {
        window.location.href = 'dashboard.php?page=inventory&success=1';
    }, 10);
</script>";
    exit;
}

// ✅ SHOW SUCCESS ALERT AFTER REDIRECT
if (isset($_GET['success'])) {
    echo "<script>
        alert('✅ Updated successfully!');
        window.history.replaceState(null, null, window.location.pathname + '?page=inventory');
    </script>";
}

// ✅ NOW LOAD DATA (AFTER LOGIC)
$products = $product->getAllProducts();
echo $product->renderLowStockAlert();
echo $product->renderExpiryAlert();
?>


<link rel="stylesheet" href="../css/inventory.css">
<link rel="stylesheet" href="../css/table.css">
<div class="card shadow-sm">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0">Inventory</h4>
        </div>

        <table class="table table-striped table-hover align-middle w-100 myTableExport">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Product</th>
                    <th>Category</th>
                    <th>Stock</th>
                    <th>Expiry</th>
                    <th>Status</th>
                </tr>
            </thead>

            <tbody>
                <?php foreach ($products as $prod): ?>
                    <tr>
                        <td><?= $prod['id'] ?></td>
                        <td><?= $prod['product_name'] ?></td>
                        <td><?= $prod['category_name'] ?? 'N/A' ?></td>
                        <td><?= $prod['quantity'] ?></td>
                        <td>
                            <?php
                            $expiry = $prod['expiry_date'] ?? null;

                            if (!$expiry) {
                                echo 'N/A';
                            } else {
                                $today = new DateTime();
                                $expDate = new DateTime($expiry);
                                $interval = $today->diff($expDate);

                                if ($expDate <= $today) {
                                    // 🔴 EXPIRED
                                    echo "<span class='text-danger fw-bold'>" . htmlspecialchars($expiry) . "</span>";
                                } elseif ($interval->days <= 90 && !$interval->invert) {
                                    // 🟡 NEAR EXPIRY
                                    echo "<span class='text-warning fw-bold'>" . htmlspecialchars($expiry) . "</span>";
                                } else {
                                    // 🟢 SAFE
                                    echo "<span class='text-success'>" . htmlspecialchars($expiry) . "</span>";
                                }
                            }
                            ?>
                        </td>

                        <!-- 🔥 STOCK STATUS -->
                        <td>
                            <?php
                            if ($prod['quantity'] <= 0) {
                                echo "<span style='color:red;'>Out of Stock</span>";
                            } elseif ($prod['quantity'] <= 50) {
                                echo "<span style='color:orange;'>Low Stock</span>";
                            } else {
                                echo "<span style='color:green;'>In Stock</span>";
                            }
                            ?>
                        </td </tr>
                    <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<script src="../js/updateprod.js"></script>
<script src="../js/usermanagement.js"></script>