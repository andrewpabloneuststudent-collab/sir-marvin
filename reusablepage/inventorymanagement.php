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
?>


<link rel="stylesheet" href="../css/inventory.css">

<h2>Inventory Management</h2>

<table id="usersTable" class="table table-striped table-hover">
    <thead class="table-dark">
        <tr>
            <th>ID</th>
            <th>Product</th>
            <th>Category</th>
            <th>Stock</th>
            <th>Expiry</th>
            <th>Status</th>
            <th>Action</th>
        </tr>
    </thead>

    <tbody>
        <?php foreach ($products as $prod): ?>
            <tr>
                <td><?= $prod['id'] ?></td>
                <td><?= $prod['product_name'] ?></td>
                <td><?= $prod['category_name'] ?? 'N/A' ?></td>
                <td><?= $prod['quantity'] ?></td>
                <td><?= $prod['expiry_date'] ?? 'N/A' ?></td>

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
                </td>

                <td>
                    <!-- EDIT STOCK -->
                    <button class="btn btn-success" data-bs-toggle="modal" onclick='openStockModal(<?= json_encode($prod) ?>)'>
                        Update
                    </button>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php include 'updateprodmodal.php'; ?>
<script src="../js/updateprod.js"></script>
<script src="../js/usermanagement.js"></script>
