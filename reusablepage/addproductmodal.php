<?php
require_once __DIR__ . "/../function/addprodfunct.php";
require_once __DIR__ . "/../conn/connection_links.php";

use Classes\ProductManagement;

$product = new ProductManagement($db);

// ✅ USE CLASS FUNCTIONS
$categories = $product->getCategories();
$classifications = $product->getClassifications();
?>

<!-- ADD PRODUCT MODAL -->
<div class="modal fade" id="addProductModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">

            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Add Product</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    <!-- PRODUCT NAME -->
                    <div class="mb-2">
                        <label>Product Name</label>
                        <input type="text" name="product_name" class="form-control" required>
                    </div>

                    <!-- BARCODE -->
                    <div class="mb-2">
                        <label>Barcode</label>
                        <input type="text" name="barcode" class="form-control">
                    </div>

                    <!-- CATEGORY -->
                    <div class="mb-2">
                        <label>Category</label>
                        <select name="category_id" class="form-control" required>
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>">
                                    <?= $cat['category_name'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- CLASSIFICATION -->
                    <div class="mb-2">
                        <label>Classification</label>
                        <select name="classification_id" class="form-control" required>
                            <option value="">Select Classification</option>
                            <?php foreach ($classifications as $cls): ?>
                                <option value="<?= $cls['id'] ?>">
                                    <?= $cls['classification_name'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- UNIT -->
                    <div class="mb-2">
                        <label>Unit</label>
                        <input type="text" name="unit" class="form-control">
                    </div>

                    <!-- PRICE -->
                    <div class="mb-2">
                        <label>Price</label>
                        <input type="number" step="0.01" name="price" class="form-control" required>
                    </div>

                    <!-- STOCK -->
                    <div class="mb-2">
                        <label>Initial Stock</label>
                        <input type="number" name="quantity" class="form-control" required>
                    </div>

                    <!-- EXPIRY -->
                    <div class="mb-2">
                        <label>Expiry Date</label>
                        <input type="date" name="expiry_date" class="form-control">
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="submit" name="addProduct" class="btn btn-success">
                        Save Product
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>