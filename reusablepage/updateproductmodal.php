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
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="branded_name_<?= $prod['id'] ?>" class="form-label">Branded Name </label>
                                <input type="text" id="branded_name_<?= $prod['id'] ?>" name="branded_name" class="form-control"
                                    value="<?= htmlspecialchars($prod['branded_name'] ?? '') ?>" >
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="generic_name_<?= $prod['id'] ?>" class="form-label">Generic Name </label>
                                <input type="text" id="generic_name_<?= $prod['id'] ?>" name="generic_name" class="form-control"
                                    value="<?= htmlspecialchars($prod['generic_name'] ?? '') ?>" >
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="unit_measurement_<?= $prod['id'] ?>" class="form-label">Unit of Measurement</label>
                                <select id="unit_measurement_<?= $prod['id'] ?>" name="unit_measurement" class="form-control">
                                    <option value="">Select Unit</option>
                                    <?php foreach ($unitMeasurements as $unit): ?>
                                        <option value="<?= (int) ($unit['id'] ?? 0) ?>"
                                            <?= ((int) ($prod['measurement_id'] ?? 0) === (int) ($unit['id'] ?? 0)) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($unit['name'] ?? '') ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="strength_<?= $prod['id'] ?>" class="form-label">Strength <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="number" id="strength_<?= $prod['id'] ?>" name="strength" class="form-control"
                                        placeholder="e.g., 200,500,1000" value="<?= htmlspecialchars((string) ($prod['strength'] ?? '')) ?>" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="barcode_<?= $prod['id'] ?>" class="form-label">Barcode </label>
                                <input type="text" id="barcode_<?= $prod['id'] ?>" name="barcode" class="form-control"
                                    value="<?= htmlspecialchars($prod['barcode'] ?? '') ?>" >
                            </div>
                             <div class="col-md-6 mb-3">
                                <label for="category_id_<?= $prod['id'] ?>" class="form-label">Category </label>
                                <select id="category_id_<?= $prod['id'] ?>" name="category_id" class="form-control mb-2" onchange="updateCategoryRuleNote(this)">
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?= $cat['id'] ?>"
                                                data-senior="<?= (int)($cat['senior_discount'] ?? 0) ?>"
                                                data-pwd="<?= (int)($cat['pwd_discount'] ?? 0) ?>"
                                                data-vat="<?= (int)($cat['has_vat'] ?? 0) ?>"
                                                <?= (isset($prod['category_id']) && $prod['category_id'] == $cat['id']) ? 'selected' : '' ?>>
                                            <?= $cat['category_name'] ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div id="categoryRuleNote_<?= $prod['id'] ?>" class="form-text text-muted mt-1">
                                    This is controlled by the selected category.
                                </div>
                            </div>
                        </div>
                        <hr class="my-4">

                        <!-- PRICING & STOCK SECTION -->
                        <h6 class="mb-3 text-secondary fw-bold">Pricing, Stock and Expiration</h6>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="net_price_<?= $prod['id'] ?>" class="form-label">Net Price (Cost) </label>
                                <input type="number" id="net_price_<?= $prod['id'] ?>" step="0.01" name="net_price"
                                    class="form-control" value="<?= $prod['net_price'] ?? '' ?>"  oninput="updateProductSalePrice(<?= $prod['id'] ?>)">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="markup_percent_<?= $prod['id'] ?>" class="form-label">Markup %</label>
                                <input type="number" id="markup_percent_<?= $prod['id'] ?>" step="0.01" name="markup_percent"
                                    class="form-control" value="5" placeholder="5" oninput="updateProductSalePrice(<?= $prod['id'] ?>)">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="total_price_<?= $prod['id'] ?>" class="form-label">Sale Price </label>
                                <input type="number" id="total_price_<?= $prod['id'] ?>" step="0.01" name="total_price"
                                    class="form-control" value="<?= $prod['total_price'] ?? '' ?>"  oninput="updateProductMarkupPercent(<?= $prod['id'] ?>)">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="quantity_<?= $prod['id'] ?>" class="form-label">Stock </label>
                                <input type="number" id="quantity_<?= $prod['id'] ?>" name="quantity" class="form-control"
                                    value="<?= $prod['quantity'] ?? '' ?>" >
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="expiry_date_<?= $prod['id'] ?>" class="form-label">Expiry Date </label>
                                <input type="date" id="expiry_date_<?= $prod['id'] ?>" name="expiry_date" class="form-control"
                                    value="<?= $prod['expiry_date'] ?? '' ?>" >
                            </div>
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
function updateCategoryRuleNote(selectEl) {
    const noteId = selectEl.id.replace('category_id_', 'categoryRuleNote_');
    const note = document.getElementById(noteId);
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

document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('select[id^="category_id_"]').forEach(function(select) {
        updateCategoryRuleNote(select);
    });
});

function updateProductSalePrice(productId) {
    const netInput = document.getElementById('net_price_' + productId);
    const markupInput = document.getElementById('markup_percent_' + productId);
    const priceInput = document.getElementById('total_price_' + productId);
    if (!netInput || !markupInput || !priceInput) return;

    const net = parseFloat(netInput.value) || 0;
    const markup = parseFloat(markupInput.value) || 0;
    const salePrice = net + (net * markup / 100);

    if (net > 0) {
        priceInput.value = salePrice.toFixed(2);
    }
}

function updateProductMarkupPercent(productId) {
    const netInput = document.getElementById('net_price_' + productId);
    const priceInput = document.getElementById('total_price_' + productId);
    const markupInput = document.getElementById('markup_percent_' + productId);
    if (!netInput || !priceInput || !markupInput) return;

    const net = parseFloat(netInput.value) || 0;
    const price = parseFloat(priceInput.value) || 0;

    if (net > 0) {
        const markup = ((price - net) / net) * 100;
        markupInput.value = markup.toFixed(2);
    }
}
</script>

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