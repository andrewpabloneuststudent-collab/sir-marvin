<?php
require_once __DIR__ . "/../function/addprodfunct.php";
require_once __DIR__ . "/../conn/connection_links.php";

use Classes\ProductManagement;

$product = new ProductManagement($db);

// ✅ HANDLE UPDATE FIRST (NO OUTPUT BEFORE THIS.)
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
echo $product->renderExpiryAlert();
?>

<link rel="stylesheet" href="../css/inventory.css?v=1.1">
<link rel="stylesheet" href="../css/table.css">

<div class="card shadow-sm">
    <div class="card-body">
        <!-- HEADER -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0">
                <i class="fas fa-boxes text-success me-2"></i>Inventory Management
            </h4>
        </div>

        <!-- TABLE -->
        <table class="table table-striped table-hover align-middle w-100 myTableExport">
            <thead class="table-dark">
                <tr>
                    <th>Product</th>
                    <th>Category</th>
                    <th>Barcode</th>
                    <th>Stock Level</th>
                    <th>Status</th>
                    <th>Expiry Date</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $prod): ?>
                    <tr>
                        <td>
                            <strong><?= htmlspecialchars($prod['product_name']) ?></strong>
                        </td>
                        <td><?= htmlspecialchars($prod['category_name'] ?? 'N/A') ?></td>
                        <td><code><?= htmlspecialchars($prod['barcode'] ?: 'No Barcode') ?></code></td>
                        <td>
                            <span class="fw-bold <?= ($prod['quantity'] <= 0) ? 'text-danger' : (($prod['quantity'] <= 50) ? 'text-warning' : 'text-success') ?>">
                                <?= $prod['quantity'] ?> units
                            </span>
                        </td>
                        <td>
                            <?php if ($prod['quantity'] <= 0): ?>
                                <span class="badge bg-danger">
                                    <i class="fas fa-times-circle me-1"></i>Out of Stock
                                </span>
                            <?php elseif ($prod['quantity'] <= 50): ?>
                                <span class="badge bg-warning text-dark">
                                    <i class="fas fa-exclamation-triangle me-1"></i>Low Stock
                                </span>
                            <?php else: ?>
                                <span class="badge bg-success">
                                    <i class="fas fa-check-circle me-1"></i>In Stock
                                </span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php
                            $expiry = $prod['expiry_date'] ?? null;
                            if (!$expiry) {
                                echo '<span class="text-muted">N/A</span>';
                            } else {
                                $today = new DateTime();
                                $expDate = new DateTime($expiry);
                                $interval = $today->diff($expDate);
                                if ($expDate <= $today) {
                                    echo "<span class='badge bg-danger'><i class='fas fa-exclamation-circle me-1'></i>" . htmlspecialchars($expiry) . "</span>";
                                } elseif ($interval->days <= 90 && !$interval->invert) {
                                    echo "<span class='badge bg-warning text-dark'><i class='fas fa-clock me-1'></i>" . htmlspecialchars($expiry) . "</span>";
                                } else {
                                    echo "<span class='badge bg-success'><i class='fas fa-calendar-check me-1'></i>" . htmlspecialchars($expiry) . "</span>";
                                }
                            }
                            ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<script src="../js/updateprod.js"></script>
<script src="../js/usermanagement.js"></script>
<script>
    // Reinitialize DataTable for myTableExport when tab becomes visible
    setTimeout(function() {
        if (!$.fn.DataTable.isDataTable('.myTableExport')) {
            $('.myTableExport').DataTable({
                responsive: true,
                autoWidth: false,
                dom: 'fBrtip',
                buttons: ['copy', 'excel', 'pdf', 'print']
            });
            
            // Auto-focus on the search input after a small delay
            setTimeout(function() {
                $('input[type="search"]').first().focus();
            }, 50);
        }
    }, 100);
</script> 