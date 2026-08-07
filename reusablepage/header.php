<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/../conn/Database.php";
require_once __DIR__ . "/../function/addprodfunct.php";

use Classes\ProductManagement;

$productAlertManager = new ProductManagement($db);
$lowStockItems = $productAlertManager->getLowStockAlertItems();
$expiryItems = $productAlertManager->getExpiryAlertItems();

$globalAlertItems = [];

foreach ($lowStockItems as $item) {
    $globalAlertItems[] = [
        'title' => 'Low Stock',
        'message' => htmlspecialchars($item['product_name']) . ' has only ' . $item['quantity'] . ' unit(s) left.',
        'icon' => 'fas fa-exclamation-triangle',
        'bg' => '#f59e0b'
    ];
}

foreach ($expiryItems as $item) {
    $formattedDate = !empty($item['expiry_date'])
        ? ' on ' . date('M d, Y', strtotime($item['expiry_date']))
        : '';

    $message = $item['status'] === 'Expired'
        ? htmlspecialchars($item['name']) . ' expired' . $formattedDate . ' and needs immediate attention.'
        : htmlspecialchars($item['name']) . ' will expire' . $formattedDate . ' in ' . $item['days_left'] . ' day(s).';

    $globalAlertItems[] = [
        'title' => $item['status'] === 'Expired' ? 'Expired Item' : 'Near Expiry',
        'message' => $message,
        'icon' => $item['status'] === 'Expired' ? 'fas fa-times-circle' : 'fas fa-clock',
        'bg' => $item['status'] === 'Expired' ? '#dc2626' : '#d97706'
    ];
}
?>

<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm px-3">

    <!-- ☰ Sidebar Toggle (Mobile) -->
    <button class="btn btn-outline-success d-lg-none me-2" data-bs-toggle="offcanvas" data-bs-target="#sidebar">
        ☰
    </button>

    <!-- 🏥 Logo / System Name -->
    <a class="navbar-brand fw-bold text-success" href="#">
        MMB'S DRUGSTORE
    </a>

    <!-- Right Side -->
    <div class="ms-auto d-flex align-items-center gap-2">

        <!-- 👤 User Dropdown -->
        <div class="dropdown">
            <button type="button" class="btn btn-success dropdown-toggle" id="userDropdownBtn" onclick="toggleDropdown()">
                <?php echo htmlspecialchars($_SESSION['position']); ?>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow-lg" id="userDropdownMenu" style="display: none; width: 20%; border-radius: 8px; border: none;">
                <li><a class="dropdown-item text-danger d-flex align-items-center gap-2" href="../login_logout_page/logout.php" style="padding: 12px 16px 12px; font-size: 15px; font-weight: 500;"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>

        <script>
            function toggleDropdown() {
                const menu = document.getElementById('userDropdownMenu');
                menu.style.display = menu.style.display === 'none' ? 'block' : 'none';
            }

            // Close dropdown when clicking outside
            document.addEventListener('click', function(event) {
                const btn = document.getElementById('userDropdownBtn');
                const menu = document.getElementById('userDropdownMenu');
                if (!btn.contains(event.target) && !menu.contains(event.target)) {
                    menu.style.display = 'none';
                }
            });
        </script>

    </div>
</nav>

<?php if (!empty($globalAlertItems)): ?>
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1080; max-width: 360px;">
    <?php foreach ($globalAlertItems as $alert): ?>
        <div class="toast show align-items-center border-0 shadow-lg mb-2" role="alert" aria-live="assertive" aria-atomic="true" style="background: <?= $alert['bg'] ?>; color: #fff;">
            <div class="d-flex">
                <div class="toast-body">
                    <div class="fw-bold"><i class="<?= $alert['icon'] ?> me-2"></i><?= htmlspecialchars($alert['title']) ?></div>
                    <div class="small mt-1"><?= $alert['message'] ?></div>
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" aria-label="Close" onclick="this.closest('.toast').remove()"></button>
            </div>
        </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>