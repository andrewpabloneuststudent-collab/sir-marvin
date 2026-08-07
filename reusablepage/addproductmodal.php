<?php
require_once __DIR__ . "/../function/addprodfunct.php";
require_once __DIR__ . "/../conn/connection_links.php";

use Classes\ProductManagement;

$product = new ProductManagement($db);

// ✅ USE CLASS FUNCTIONS
$categories = $product->getCategories();
$unitMeasurements = $product->getUnitMeasurements();

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

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="branded_name" class="form-label">Brand Name</label>
                            <input type="text" id="branded_name" name="branded_name" class="form-control"
                                placeholder="e.g., Tylenol">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="generic_name" class="form-label">Generic Name</label>
                            <input type="text" id="generic_name" name="generic_name" class="form-control"
                                placeholder="e.g., Paracetamol" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="unit_measurement" class="form-label">Unit of Measurement</label>
                            <select id="unit_measurement" name="unit_measurement" class="form-control">
                                <option value="">Select Unit</option>
                                <?php foreach ($unitMeasurements as $unit): ?>
                                    <option value="<?= (int) ($unit['id'] ?? 0) ?>">
                                        <?= htmlspecialchars($unit['name'] ?? '') ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="strength" class="form-label">Strength <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" id="strength" name="strength" class="form-control"
                                    placeholder="e.g., 200,500,1000" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="barcode" class="form-label">Barcode <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="text" id="barcode" name="barcode" class="form-control"
                                    placeholder="e.g., 123456789012" required>
                                <button type="button" class="btn btn-outline-secondary" onclick="generateBarcode()">
                                    <i class="fas fa-sync-alt"></i> Auto
                                </button>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="category_id" class="form-label">Category <span
                                    class="text-danger">*</span></label>
                            <select id="category_id" name="category_id" class="form-control" required
                                onchange="updateCategoryRuleNote(this)">
                                <option value="">Select Category</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>"
                                        data-senior="<?= (int) ($cat['senior_discount'] ?? 0) ?>"
                                        data-pwd="<?= (int) ($cat['pwd_discount'] ?? 0) ?>"
                                        data-vat="<?= (int) ($cat['has_vat'] ?? 0) ?>">
                                        <?= $cat['category_name'] ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div id="categoryRuleNote" class="form-text text-muted mt-1">
                                Select a category to see whether senior/PWD discounts apply.
                            </div>
                        </div>

                    </div>

                    <hr class="my-4">

                    <!-- PRICING SECTION .-->
                    <h6 class="mb-3 text-secondary fw-bold">Pricing, Stock and Expiration</h6>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="net_price" class="form-label">Net Price (Cost) <span
                                    class="text-danger">*</span></label>
                            <input type="number" id="net_price" step="0.01" name="net_price" class="form-control"
                                placeholder="e.g., 50.00" required oninput="updateAddProductSalePrice()">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="markup_percent" class="form-label">Markup %</label>
                            <input type="number" id="markup_percent" step="0.01" name="markup_percent"
                                class="form-control" value="5" placeholder="e.g., 5"
                                oninput="updateAddProductSalePrice()">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="total_price" class="form-label">Sale Price <span
                                    class="text-danger">*</span></label>
                            <input type="number" id="total_price" step="0.01" name="total_price" class="form-control"
                                placeholder="e.g., 85.00" required oninput="updateAddProductMarkupPercent()">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="batch_number" class="form-label">Batch No</label>
                            <input type="text" id="batch_number" name="batch_number" class="form-control"
                                placeholder="e.g., BATCH-001">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="quantity" class="form-label">Initial Stock <span
                                    class="text-danger">*</span></label>
                            <input type="number" id="quantity" name="quantity" class="form-control"
                                placeholder="e.g., 100" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="expiry_date" class="form-label">Expiry Date <span
                                    class="text-danger">*</span></label>
                            <input type="date" id="expiry_date" name="expiry_date" class="form-control" required>
                        </div>
                    </div>



                    <hr class="my-4">

                    <!-- IMAGE SECTION -->
                    <h6 class="mb-3 text-secondary fw-bold">Product Image</h6>
                    <div class="mb-3">
                        <label for="product_image_input" class="form-label">Upload Image <span
                                class="text-danger">*</span></label>
                        <input type="file" id="product_image_input" name="product_image" class="form-control"
                            accept="image/*" required onchange="previewImage(event)">
                        <small class="text-muted d-block mt-2"><i class="fas fa-info-circle"></i> Recommended:
                            500x500px, JPG/PNG, max 5MB</small>
                    </div>

                    <!-- IMAGE PREVIEW -->
                    <div id="image_preview_container" style="display: none;">
                        <div class="text-center">
                            <img id="image_preview" src="" alt="Image Preview"
                                style="max-width: 100%; max-height: 300px; border: 1px solid #ddd; border-radius: 8px; padding: 10px; background-color: #f8f9fa;">
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
            reader.onload = function (e) {
                preview.src = e.target.result;
                previewContainer.style.display = 'block';
            };
            reader.readAsDataURL(file);
        } else {
            previewContainer.style.display = 'none';
        }
    }

    function updateCategoryRuleNote(selectEl) {
        const note = document.getElementById('categoryRuleNote');
        if (!selectEl || !note) return;

        const opt = selectEl.options[selectEl.selectedIndex];
        if (!opt) return;

        const senior = opt.getAttribute('data-senior') === '1';
        const pwd = opt.getAttribute('data-pwd') === '1';
        const vat = opt.getAttribute('data-vat') === '1';

        const discountText = senior && pwd
            ? 'Senior/PWD discount: Yes for both'
            : senior
                ? 'Senior/PWD discount: Senior only'
                : pwd
                    ? 'Senior/PWD discount: PWD only'
                    : 'Senior/PWD discount: No';

        note.innerHTML = `${discountText} • VAT: ${vat ? 'Yes' : 'No'}`;
    }

    function updateAddProductSalePrice() {
        const netInput = document.getElementById('net_price');
        const markupInput = document.getElementById('markup_percent');
        const priceInput = document.getElementById('total_price');

        if (!netInput || !markupInput || !priceInput) return;

        const net = parseFloat(netInput.value) || 0;
        const markup = parseFloat(markupInput.value) || 0;
        const salePrice = net + (net * markup / 100);

        if (net > 0) {
            priceInput.value = salePrice.toFixed(2);
        }
    }

    function updateAddProductMarkupPercent() {
        const netInput = document.getElementById('net_price');
        const priceInput = document.getElementById('total_price');
        const markupInput = document.getElementById('markup_percent');

        if (!netInput || !priceInput || !markupInput) return;

        const net = parseFloat(netInput.value) || 0;
        const price = parseFloat(priceInput.value) || 0;

        if (net > 0) {
            const markup = ((price - net) / net) * 100;
            markupInput.value = markup.toFixed(2);
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        const select = document.getElementById('category_id');
        if (select) {
            updateCategoryRuleNote(select);
        }
    });
</script>