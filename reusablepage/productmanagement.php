<?php
require_once __DIR__ . "/../function/addprodfunct.php";
require_once __DIR__ . "/../conn/connection_links.php";

use Classes\ProductManagement;

$product = new ProductManagement($db);
$products = $product->getAllProducts();
$categories = $product->getCategories();
$classifications = $product->getClassifications();

if (isset($_GET['deleteProduct'])) {
    $id = (int) $_GET['deleteProduct'];

    if ($product->deleteProduct($id)) {
        echo "<script>alert('Product deleted successfully'); window.location.href = window.location.href = 'dashboard.php?tab=product';</script>";
        exit;
    } else {
        echo "<script>alert('" . $product->getResponse() . "');</script>";
    }
}

// UPDATE
if ($product->updateProduct()) {
    echo "<script>alert('Updated successfully'); window.location.href = window.location.href = 'dashboard.php?tab=product';</script>";
    exit;
}

if ($product->addProduct()) {
    echo "<script>alert('Product added successfully'); window.location.href = window.location.href = 'dashboard.php?tab=product';</script>";
    exit;
} else {
    if (!empty($_POST) && isset($_POST['addProduct'])) {
        echo "<script>alert('" . $product->getResponse() . "');</script>";
    }
}
?>

<div class="card shadow-sm">
    <div class="card-body">

        <!-- ADD PRODUCT BUTTON -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0">Product Management</h4>

            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addProductModal">
                + Add Product
            </button>
        </div>

        <table class="table table-striped table-hover align-middle w-100 myTable">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Product</th>
                    <th>Category</th>
                    <th>Classification</th>
                    <th>Net Price</th>
                    <th>Gross Price</th>
                    <th>Stock</th>
                    <th>Expiry</th>
                    <th>Barcode</th>
                    <th>Action</th>
                </tr>
            </thead>

            <tbody>
                <?php foreach ($products as $prod): ?>
                    <tr>
                        <td><?= $prod['id'] ?></td>
                        <td><?= $prod['product_name'] ?></td>
                        <td><?= $prod['category_name'] ?? 'N/A' ?></td>
                        <td><?= $prod['classification_name'] ?? 'N/A' ?></td>
                        <td>₱ <?= number_format($prod['net_price'], 2) ?></td>
                        <td>₱ <?= number_format($prod['gross_price'], 2) ?></td>
                        <td><?= $prod['quantity'] ?></td>
                        <td><?= $prod['expiry_date'] ?? 'N/A' ?></td>
                        <td><?= $prod['barcode'] ?></td>
                        <td>
                            <!-- EDIT -->
                            <button class="btn btn-warning" data-bs-toggle="modal"
                                data-bs-target="#editProduct<?= $prod['id'] ?>">
                                Edit
                            </button>
                            <!-- DELETE -->
                            <a href="?deleteProduct=<?= $prod['id'] ?>" class="btn btn-sm btn-danger"
                                onclick="return confirm('Delete this product?')">
                                Delete
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php include 'updateproductmodal.php'; ?>
<?php include 'addproductmodal.php'; ?>
<script src="js/usermanagement.js"></script>