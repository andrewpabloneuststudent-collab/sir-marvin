
<!-- EDIT PRODUCT MODAL -->
<div class="modal <?= $editData ? 'show d-block' : 'fade' ?>" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">

            <form method="POST">
                <input type="hidden" name="id" value="<?= $editData['id'] ?? '' ?>">

                <div class="modal-header">
                    <h5>Edit Product</h5>
                  <a href="dashboard?page=product" class="btn-close"></a>
                </div>

                <div class="modal-body">

                    <input type="text" name="product_name" class="form-control mb-2" value="<?= $editData['product_name'] ?? '' ?>" placeholder="Product Name">

                    <input type="text" name="barcode" class="form-control mb-2" value="<?= $editData['barcode'] ?? '' ?>" placeholder="Barcode">

                    <!-- CATEGORY SELECT -->
                    <select name="category_id" class="form-control mb-2">
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>"
                                <?= ($editData && $editData['category_id'] == $cat['id']) ? 'selected' : '' ?>>
                                <?= $cat['category_name'] ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <!-- CLASSIFICATION SELECT -->
                    <select name="classification_id" class="form-control mb-2">
                        <?php foreach ($classifications as $cls): ?>
                            <option value="<?= $cls['id'] ?>"
                                <?= ($editData && $editData['classification_id'] == $cls['id']) ? 'selected' : '' ?>>
                                <?= $cls['classification_name'] ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <input type="text" name="unit" class="form-control mb-2"value="<?= $editData['unit'] ?? '' ?>">
                    <input type="number" name="price" class="form-control mb-2"value="<?= $editData['price'] ?? '' ?>">
                    <input type="number" name="quantity" class="form-control mb-2"value="<?= $editData['quantity'] ?? '' ?>">
                    <input type="date" name="expiry_date" class="form-control mb-2"value="<?= $editData['expiry_date'] ?? '' ?>">
                </div>

                <div class="modal-footer">
                    <button type="submit" name="updateProduct" class="btn btn-primary">
                        Update
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

