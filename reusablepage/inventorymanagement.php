<?php
require_once __DIR__ . "/../function/addprodfunct.php";
require_once __DIR__ . "/../conn/connection_links.php";

use Classes\ProductManagement;

$product = new ProductManagement($db);

// ✅ HANDLE UPDATE FIRST (NO OUTPUT BEFORE THIS)
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


<link rel="stylesheet" href="../css/inventory.css">
<link rel="stylesheet" href="../css/table.css">
<style>
    .inventory-container {
        padding: 10px;
    }
    .premium-card {
        background: #ffffff;
        border: 1px solid rgba(0,0,0,0.08);
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.04);
        transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        overflow: hidden;
        height: 100%;
        display: flex;
        flex-direction: column;
        position: relative;
    }
    .premium-card:hover {
        box-shadow: 0 8px 25px rgba(0,0,0,0.08);
        border-color: rgba(25, 135, 84, 0.3);
    }
    .card-img-wrapper {
        background: linear-gradient(135deg, #fdfdfd 0%, #f4f7f6 100%);
        height: 200px;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 1.5rem;
        position: relative;
        border-bottom: 1px solid rgba(0,0,0,0.03);
    }
    .card-img-wrapper img {
        max-height: 100%;
        max-width: 100%;
        object-fit: contain;
        filter: drop-shadow(0 8px 16px rgba(0,0,0,0.08));
        transition: transform 0.3s ease;
    }
    .premium-card:hover .card-img-wrapper img {
        transform: scale(1.05);
    }
    .status-badge {
        position: absolute;
        top: 12px;
        right: 12px;
        padding: 6px 12px;
        border-radius: 30px;
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        z-index: 2;
        backdrop-filter: blur(4px);
    }
    .badge-danger-glow {
        background: rgba(220, 53, 69, 0.95);
        color: white;
        border: 1px solid rgba(255,255,255,0.2);
    }
    .badge-warning-glow {
        background: rgba(255, 193, 7, 0.95);
        color: #212529;
        border: 1px solid rgba(255,255,255,0.4);
    }
    .premium-card-body {
        padding: 1.25rem;
        flex-grow: 1;
        display: flex;
        flex-direction: column;
    }
    .product-title {
        font-size: 1.1rem;
        font-weight: 700;
        color: #2c3e50;
        margin-bottom: 0.5rem;
        line-height: 1.3;
    }
    .product-meta {
        font-size: 0.85rem;
        color: #6c757d;
        display: flex;
        align-items: center;
        margin-bottom: 0.3rem;
    }
    .product-meta i {
        width: 16px;
        color: #adb5bd;
    }
    .stock-footer {
        background: #f8fcf9;
        border-top: 1px solid rgba(25, 135, 84, 0.1);
        padding: 12px 20px;
        border-bottom-left-radius: 16px;
        border-bottom-right-radius: 16px;
        margin-top: auto;
    }
    .stock-value {
        font-size: 1.2rem;
        font-weight: 800;
    }
    .text-gradient-success {
        background: linear-gradient(90deg, #198754, #20c997);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }
</style>

<div class="inventory-container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0 fw-bold" style="color: #2c3e50;">
            <i class="fas fa-boxes text-success me-2"></i>Inventory Overview
        </h3>
    </div>

    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-xl-4 g-4">
        <?php foreach ($products as $prod): ?>
            <div class="col">
                <div class="premium-card">
                    <!-- Status Badge Overlay -->
                    <?php if ($prod['quantity'] <= 0): ?>
                        <div class="status-badge badge-danger-glow">
                            <i class="fas fa-times-circle me-1"></i> Out of Stock
                        </div>
                    <?php elseif ($prod['quantity'] <= 50): ?>
                        <div class="status-badge badge-warning-glow">
                            <i class="fas fa-exclamation-triangle me-1"></i> Low Stock
                        </div>
                    <?php endif; ?>

                    <!-- Image Section -->
                    <div class="card-img-wrapper">
                        <?php if(!empty($prod['imageproduct'])): ?>
                            <img src="../uploads/products/<?= htmlspecialchars($prod['imageproduct']) ?>" alt="Product Image">
                        <?php else: ?>
                            <i class="fas fa-pills text-secondary opacity-25" style="font-size: 5rem;"></i>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Details Section -->
                    <div class="premium-card-body">
                        <h5 class="product-title text-truncate" title="<?= htmlspecialchars($prod['product_name']) ?>">
                            <?= htmlspecialchars($prod['product_name']) ?>
                        </h5>
                        
                        <div class="product-meta">
                            <i class="fas fa-tag"></i> 
                            <span class="ms-1 fw-medium"><?= htmlspecialchars($prod['category_name'] ?? 'Uncategorized') ?></span>
                        </div>
                        <div class="product-meta">
                            <i class="fas fa-barcode"></i> 
                            <span class="ms-1 font-monospace"><?= htmlspecialchars($prod['barcode'] ?: 'No Barcode') ?></span>
                        </div>
                    </div>

                    <!-- Footer Data -->
                    <div class="stock-footer">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted small text-uppercase fw-bold" style="letter-spacing: 0.5px;">Stock Level</span>
                            <span class="stock-value <?= ($prod['quantity'] <= 0) ? 'text-danger' : (($prod['quantity'] <= 50) ? 'text-warning' : 'text-gradient-success') ?>">
                                <?= $prod['quantity'] ?> <span class="fs-6 fw-normal text-muted">units</span>
                            </span>
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted small text-uppercase fw-bold" style="letter-spacing: 0.5px;">Expiry Date</span>
                            <span class="small fw-bold">
                                <?php
                                $expiry = $prod['expiry_date'] ?? null;
                                if (!$expiry) {
                                    echo '<span class="text-muted">N/A</span>';
                                } else {
                                    $today = new DateTime();
                                    $expDate = new DateTime($expiry);
                                    $interval = $today->diff($expDate);
                                    if ($expDate <= $today) {
                                        echo "<span class='text-danger bg-danger bg-opacity-10 px-2 py-1 rounded'><i class='fas fa-exclamation-circle me-1'></i>" . htmlspecialchars($expiry) . "</span>";
                                    } elseif ($interval->days <= 90 && !$interval->invert) {
                                        echo "<span class='text-warning text-dark bg-warning bg-opacity-10 px-2 py-1 rounded'><i class='fas fa-clock me-1'></i>" . htmlspecialchars($expiry) . "</span>";
                                    } else {
                                        echo "<span class='text-success'><i class='fas fa-calendar-check me-1'></i>" . htmlspecialchars($expiry) . "</span>";
                                    }
                                }
                                ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<script src="../js/updateprod.js"></script>
<script src="../js/usermanagement.js"></script>