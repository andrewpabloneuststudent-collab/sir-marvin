<?php foreach ($products as $prod): ?>
<!-- Modal -->
<div class="modal fade" id="editProduct<?= $prod['id'] ?>"  tabindex="-1" aria-labelledby="updateproductLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="updateproductLabel">Edit Product</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                
                <form method="POST">
                    <input type="hidden" name="id" value="<?= $prod['id'] ?? '' ?>">
                    <input type="text" name="product_name" class="form-control mb-2"
                        value="<?= $prod['product_name'] ?? '' ?>" placeholder="Product Name">

                    <input type="text" name="barcode" class="form-control mb-2" value="<?= $prod['barcode'] ?? '' ?>"
                        placeholder="Barcode">

                    <!-- CATEGORY SELECT -->
                    <select name="category_id" class="form-control mb-2">
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= (isset($prod['category_id']) && $prod['category_id'] == $cat['id']) ? 'selected' : '' ?>>
                                <?= $cat['category_name'] ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <!-- CLASSIFICATION SELECT -->
                    <select name="classification_id" class="form-control mb-2">
                        <?php foreach ($classifications as $cls): ?>
                            <option value="<?= $cls['id'] ?>" <?= (isset($prod['classification_id']) && $prod['classification_id'] == $cls['id']) ? 'selected' : '' ?>>
                                <?= $cls['classification_name'] ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <input type="text" name="unit" class="form-control mb-2" value="<?= $prod['unit'] ?? '' ?>">
                    <input type="number" step="0.01" name="net_price" class="form-control mb-2" value="<?= $prod['net_price'] ?? '' ?>">
                    <input type="number" name="quantity" class="form-control mb-2"
                        value="<?= $prod['quantity'] ?? '' ?>">
                    <input type="date" name="expiry_date" class="form-control mb-2"
                        value="<?= $prod['expiry_date'] ?? '' ?>">

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" name="updateProduct" class="btn btn-primary">Save changes</button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>
<?php endforeach; ?>