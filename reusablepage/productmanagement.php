<?php
require_once __DIR__ . "/../function/addprodfunct.php";
require_once __DIR__ . "/../conn/connection_links.php";

use Classes\ProductManagement;

$product = new ProductManagement($db);
$products = $product->getAllProducts();
$categories = $product->getCategories();
$classifications = $product->getClassifications();

$pmUserRole = strtolower($_SESSION['position'] ?? 'staff');
$pmIsManager = in_array($pmUserRole, ['owner', 'admin']);

if (isset($_GET['deleteProduct'])) {
    $id = (int) $_GET['deleteProduct'];

    if ($product->deleteProduct($id)) {
        echo "<script>showNotif('Product deleted successfully', 'success'); setTimeout(()=>{ window.location.href='dashboard.php?tab=product'; },1500);</script>";
        exit;
    } else {
        echo "<script>showNotif('" . addslashes($product->getResponse()) . "', 'error');</script>";
    }
}

// UPDATE
if ($product->updateProduct()) {
    echo "<script>showNotif('Updated successfully', 'success'); setTimeout(()=>{ window.location.href='dashboard.php?tab=product'; },1500);</script>";
    exit;
}

if ($product->addProduct()) {
    echo "<script>showNotif('Product added successfully', 'success'); setTimeout(()=>{ window.location.href='dashboard.php?tab=product'; },1500);</script>";
    exit;
} else {
    if (!empty($_POST) && isset($_POST['addProduct'])) {
        echo "<script>showNotif('" . addslashes($product->getResponse()) . "', 'error');</script>";
    }
}
?>

<div class="card shadow-sm">
    <div class="card-body">

        <!-- ADD PRODUCT BUTTON -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0">Product Management</h4>
            <div class="d-flex gap-2">
                <?php if ($pmIsManager): ?>
                <button class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#catSettingsBootstrapModal">
                    <i class="fas fa-tags"></i> Category Settings
                </button>
                <?php endif; ?>
                <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addProductModal">
                    + Add Product
                </button>
            </div>
        </div>

        <table class="table table-striped table-hover align-middle w-100 myTable">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Product</th>
                    <th>Category</th>
                    <th>Classification</th>
                    <th>Net Price</th>
                    <th>Total Price</th>
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
                        <td>₱ <?= number_format($prod['total_price'], 2) ?></td>
                        <td><?= $prod['quantity'] ?></td>
                        <td><?= $prod['expiry_date'] ?? 'N/A' ?></td>
                        <td><?= $prod['barcode'] ?></td>
                        <td>
                            <div class="action-btns">
                                <button class="btn-action-edit" data-bs-toggle="modal"
                                    data-bs-target="#editProduct<?= $prod['id'] ?>">
                                    <i class="fas fa-pen"></i> Edit
                                </button>
                                <a href="?deleteProduct=<?= $prod['id'] ?>" class="btn-action-delete"
                                    onclick="return confirm('Delete this product?')">
                                    <i class="fas fa-trash"></i> Delete
                                </a>
                            </div>
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

<?php if ($pmIsManager): ?>
<!-- ═══ CATEGORY SETTINGS MODAL (Bootstrap - Owner/Admin only) ═══ -->
<div class="modal fade" id="catSettingsBootstrapModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-tags"></i> Category Discount & VAT Settings</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted" style="font-size:0.85rem;">
                    Configure which categories qualify for VAT, Senior Citizen, and PWD discounts.<br>
                    These settings are automatically applied at the POS.
                </p>
                <div class="row g-2 mb-3">
                    <div class="col">
                        <input type="text" id="newCategoryName" class="form-control" placeholder="New Category Name (e.g. Vitamins)">
                    </div>
                    <div class="col-auto">
                        <button type="button" class="btn btn-success" onclick="pmAddNewCategory(this)">
                            <i class="fas fa-plus"></i> Add Category
                        </button>
                    </div>
                </div>
                <table class="table table-hover align-middle" id="catSettingsTable">
                    <thead class="table-light">
                        <tr>
                            <th>Category</th>
                            <th class="text-center" style="width:100px;">VAT (12%)</th>
                            <th class="text-center" style="width:100px;">Senior</th>
                            <th class="text-center" style="width:100px;">PWD</th>
                            <th class="text-center" style="width:80px;">Action</th>
                        </tr>
                    </thead>
                    <tbody id="catSettingsBody">
                        <tr><td colspan="5" class="text-center text-muted py-4">Loading...</td></tr>
                    </tbody>
                </table>
                <div id="catSettingsSaveStatus" style="font-size:0.85rem; display:none;"></div>
            </div>
        </div>
    </div>
</div>

<script>
// Load settings when modal opens
document.getElementById('catSettingsBootstrapModal').addEventListener('show.bs.modal', function() {
    pmLoadCatSettings();
});

// Alias for POS JS compatibility
function weposOpenCatSettings() {
    const modal = new bootstrap.Modal(document.getElementById('catSettingsBootstrapModal'));
    modal.show();
}

async function pmLoadCatSettings() {
    const tbody = document.getElementById('catSettingsBody');
    tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-4">Loading...</td></tr>';
    try {
        const res  = await fetch('../function/category_settings.php');
        const data = await res.json();
        if (!data.success) throw new Error(data.error);
        let html = '';
        data.categories.forEach(cat => {
            html += `<tr>
                <td><strong>${cat.category_name}</strong></td>
                <td class="text-center"><input type="checkbox" class="form-check-input" id="vat-${cat.id}" ${cat.has_vat==1?'checked':''}></td>
                <td class="text-center"><input type="checkbox" class="form-check-input" id="senior-${cat.id}" ${cat.senior_discount==1?'checked':''}></td>
                <td class="text-center"><input type="checkbox" class="form-check-input" id="pwd-${cat.id}" ${cat.pwd_discount==1?'checked':''}></td>
                <td class="text-center"><button class="btn btn-sm btn-primary" onclick="pmSaveCat(${cat.id}, this)">Save</button></td>
            </tr>`;
        });
        tbody.innerHTML = html;
    } catch(e) {
        tbody.innerHTML = '<tr><td colspan="5" class="text-center text-danger py-4">Failed to load: ' + e.message + '</td></tr>';
    }
}

async function pmSaveCat(id, btn) {
    const origText = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    btn.disabled = true;
    const payload = {
        id,
        has_vat:         document.getElementById('vat-'+id)?.checked    ? 1 : 0,
        senior_discount: document.getElementById('senior-'+id)?.checked  ? 1 : 0,
        pwd_discount:    document.getElementById('pwd-'+id)?.checked     ? 1 : 0,
    };
    try {
        const res  = await fetch('../function/category_settings', {
            method: 'POST', headers: {'Content-Type':'application/json'}, body: JSON.stringify(payload)
        });
        const data = await res.json();
        if (data.success) {
            btn.innerHTML = '<i class="fas fa-check"></i>';
            btn.className = 'btn btn-sm btn-success';
            setTimeout(() => { btn.innerHTML = origText; btn.className = 'btn btn-sm btn-primary'; btn.disabled = false; }, 1500);
        } else {
            showNotif('Error: ' + data.error, 'error');
            btn.innerHTML = origText; btn.disabled = false;
        }
    } catch(e) {
        showNotif('Network error. Please try again.', 'error');
        btn.innerHTML = origText; btn.disabled = false;
    }
}

async function pmAddNewCategory(btn) {
    const input = document.getElementById('newCategoryName');
    const categoryName = input.value.trim();
    if (!categoryName) {
        showNotif('Please enter a category name.', 'warning');
        return;
    }

    const origText = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding...';
    btn.disabled = true;

    try {
        const res = await fetch('../function/category_settings', {
            method: 'POST',
            headers: {'Content-Type':'application/json'},
            body: JSON.stringify({ action: 'add_category', category_name: categoryName })
        });
        const data = await res.json();
        if (data.success) {
            input.value = '';
            pmLoadCatSettings(); // Reload table
        } else {
            showNotif('Error: ' + data.error, 'error');
        }
    } catch(e) {
        showNotif('Network error. Please try again.', 'error');
    } finally {
        btn.innerHTML = origText;
        btn.disabled = false;
    }
}
</script>
<?php endif; ?>