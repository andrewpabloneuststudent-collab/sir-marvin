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
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <form method="POST" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Product</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    <!-- BASIC INFORMATION SECTION -->
                    <h6 class="mb-3 text-secondary fw-bold">Basic Information</h6>
                    <div class="mb-3">
                        <label for="product_name" class="form-label">Product Name <span class="text-danger">*</span></label>
                        <input type="text" id="product_name" name="product_name" class="form-control" placeholder="e.g., Coca-Cola 1.5L" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="barcode" class="form-label">Barcode <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="text" id="barcode" name="barcode" class="form-control" placeholder="e.g., 123456789012" required>
                                <button type="button" class="btn btn-outline-secondary" onclick="generateBarcode()">
                                    <i class="fas fa-sync-alt"></i> Auto
                                </button>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="unit" class="form-label">Unit <span class="text-danger">*</span></label>
                            <input type="text" id="unit" name="unit" class="form-control" placeholder="e.g., 1pc, 1box, 1liter" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="category_id" class="form-label">Category <span class="text-danger">*</span></label>
                            <select id="category_id" name="category_id" class="form-control" required>
                                <option value="">Select Category</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>">
                                        <?= $cat['category_name'] ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="classification_id" class="form-label">Classification <span class="text-danger">*</span></label>
                            <select id="classification_id" name="classification_id" class="form-control" required>
                                <option value="">Select Classification</option>
                                <?php foreach ($classifications as $cls): ?>
                                    <option value="<?= $cls['id'] ?>">
                                        <?= $cls['classification_name'] ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <hr class="my-4">

                    <!-- PRICING SECTION .-->
                    <h6 class="mb-3 text-secondary fw-bold">Pricing & Stock</h6>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="net_price" class="form-label">Net Price (Cost) <span class="text-danger">*</span></label>
                            <input type="number" id="net_price" step="0.01" name="net_price" class="form-control" placeholder="e.g., 50.00" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="total_price" class="form-label">Sale Price <span class="text-danger">*</span></label>
                            <input type="number" id="total_price" step="0.01" name="total_price" class="form-control" placeholder="e.g., 85.00" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="quantity" class="form-label">Initial Stock <span class="text-danger">*</span></label>
                            <input type="number" id="quantity" name="quantity" class="form-control" placeholder="e.g., 100" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="expiry_date" class="form-label">Expiry Date <span class="text-danger">*</span></label>
                        <input type="date" id="expiry_date" name="expiry_date" class="form-control" required>
                    </div>

                    <hr class="my-4">

                    <!-- IMAGE SECTION -->
                    <h6 class="mb-3 text-secondary fw-bold">Product Image</h6>
                    <div class="mb-3">
                        <label for="product_image_input" class="form-label">Upload Image <span class="text-danger">*</span></label>
                        <input type="file" id="product_image_input" name="product_image" class="form-control" accept="image/*" required onchange="previewImage(event)">
                        <small class="text-muted d-block mt-2"><i class="fas fa-info-circle"></i> Recommended: 500x500px, JPG/PNG, max 5MB</small>
                    </div>

                    <!-- IMAGE PREVIEW -->
                    <div id="image_preview_container" style="display: none;">
                        <div class="text-center">
                            <img id="image_preview" src="" alt="Image Preview" style="max-width: 100%; max-height: 300px; border: 1px solid #ddd; border-radius: 8px; padding: 10px; background-color: #f8f9fa;">
                        </div>
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="addProduct" class="btn btn-success">
                        <i class="fas fa-save"></i> Save Product
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>
<script src="../js/auto_generatebarcode.js"></script>

<script>
function previewImage(event) {
    const file = event.target.files[0];
    const preview = document.getElementById('image_preview');
    const previewContainer = document.getElementById('image_preview_container');
    
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            previewContainer.style.display = 'block';
        };
        reader.readAsDataURL(file);
    } else {
        previewContainer.style.display = 'none';
    }
}
</script>