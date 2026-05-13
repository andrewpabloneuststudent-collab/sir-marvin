<?php foreach ($products as $prod): ?>
    <!-- Modal -->
    <div class="modal fade" id="editProduct<?= $prod['id'] ?>" tabindex="-1" aria-labelledby="updateproductLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="updateproductLabel">Edit Product</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">

                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="id" value="<?= $prod['id'] ?? '' ?>">
                        <input type="hidden" name="old_image" value="<?= $prod['imageproduct'] ?? '' ?>">

                        <!-- BASIC INFORMATION SECTION -->
                        <h6 class="mb-3 text-secondary fw-bold">Basic Information</h6>
                        <div class="mb-3">
                            <label for="product_name_<?= $prod['id'] ?>" class="form-label">Product Name <span
                                    class="text-danger">*</span></label>
                            <input type="text" id="product_name_<?= $prod['id'] ?>" name="product_name" class="form-control"
                                value="<?= htmlspecialchars($prod['product_name'] ?? '') ?>" required>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="barcode_<?= $prod['id'] ?>" class="form-label">Barcode <span
                                        class="text-danger">*</span></label>
                                <input type="text" id="barcode_<?= $prod['id'] ?>" name="barcode" class="form-control"
                                    value="<?= htmlspecialchars($prod['barcode'] ?? '') ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="unit_<?= $prod['id'] ?>" class="form-label">Unit <span
                                        class="text-danger">*</span></label>
                                <input type="text" id="unit_<?= $prod['id'] ?>" name="unit" class="form-control"
                                    value="<?= htmlspecialchars($prod['unit'] ?? '') ?>" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="category_id_<?= $prod['id'] ?>" class="form-label">Category <span
                                        class="text-danger">*</span></label>
                                <select name="category_id" class="form-control mb-2">
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?= $cat['id'] ?>" <?= (isset($prod['category_id']) && $prod['category_id'] == $cat['id']) ? 'selected' : '' ?>>
                                            <?= $cat['category_name'] ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                           
                        </div>

                        <hr class="my-4">

                        <!-- PRICING & STOCK SECTION -->
                        <h6 class="mb-3 text-secondary fw-bold">Pricing & Stock</h6>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="net_price_<?= $prod['id'] ?>" class="form-label">Net Price (Cost) <span
                                        class="text-danger">*</span></label>
                                <input type="number" id="net_price_<?= $prod['id'] ?>" step="0.01" name="net_price"
                                    class="form-control" value="<?= $prod['net_price'] ?? '' ?>" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="total_price_<?= $prod['id'] ?>" class="form-label">Sale Price <span
                                        class="text-danger">*</span></label>
                                <input type="number" id="total_price_<?= $prod['id'] ?>" step="0.01" name="total_price"
                                    class="form-control" value="<?= $prod['total_price'] ?? '' ?>" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="quantity_<?= $prod['id'] ?>" class="form-label">Stock <span
                                        class="text-danger">*</span></label>
                                <input type="number" id="quantity_<?= $prod['id'] ?>" name="quantity" class="form-control"
                                    value="<?= $prod['quantity'] ?? '' ?>" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="expiry_date_<?= $prod['id'] ?>" class="form-label">Expiry Date <span
                                    class="text-danger">*</span></label>
                            <input type="date" id="expiry_date_<?= $prod['id'] ?>" name="expiry_date" class="form-control"
                                value="<?= $prod['expiry_date'] ?? '' ?>" required>
                        </div>

                        <hr class="my-4">

                        <!-- IMAGE SECTION -->
                        <h6 class="mb-3 text-secondary fw-bold">Product Image</h6>

                        <!-- CURRENT IMAGE PREVIEW -->
                        <?php if (!empty($prod['imageproduct'])): ?>
                            <div class="mb-3">
                                <label class="form-label">Current Image</label>
                                <div class="text-center">
                                    <img src="../img/<?= htmlspecialchars($prod['imageproduct']) ?>" alt="Current Product Image"
                                        style="max-width: 100%; max-height: 200px; border: 1px solid #ddd; border-radius: 8px; padding: 10px; background-color: #f8f9fa;">
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- NEW IMAGE UPLOAD -->
                        <div class="mb-3">
                            <label for="product_image_<?= $prod['id'] ?>" class="form-label">Replace Image
                                (Optional)</label>
                            <input type="file" id="product_image_<?= $prod['id'] ?>" name="product_image"
                                class="form-control" accept="image/*"
                                onchange="previewUpdateImage(event, <?= $prod['id'] ?>)">
                            <small class="text-muted d-block mt-2"><i class="fas fa-info-circle"></i> Recommended:
                                500x500px, JPG/PNG, max 5MB</small>
                        </div>

                        <!-- NEW IMAGE PREVIEW -->
                        <div id="image_preview_container_<?= $prod['id'] ?>" style="display: none;">
                            <div class="text-center">
                                <img id="image_preview_<?= $prod['id'] ?>" src="" alt="New Image Preview"
                                    style="max-width: 100%; max-height: 250px; border: 1px solid #28a745; border-radius: 8px; padding: 10px; background-color: #f8f9fa;">
                                <small class="text-success d-block mt-2">✓ New image selected</small>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" name="updateProduct" class="btn btn-primary">
                                <i class="fas fa-save"></i> Save Changes
                            </button>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>
<?php endforeach; ?>

<script>
    function previewUpdateImage(event, productId) {
        const file = event.target.files[0];
        const preview = document.getElementById('image_preview_' + productId);
        const previewContainer = document.getElementById('image_preview_container_' + productId);

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
</script>