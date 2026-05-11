<?php
// UPDATE_ID: 11:01:45
require_once "../conn/database.php";
require_once "../function/dashboard.php";

use Classes\DashboardManager;

$dashboardManager = new DashboardManager($db);

$totalSalesToday   = $dashboardManager->getTotalSalesToday();
$totalSalesMonth   = $dashboardManager->getTotalSalesMonth();
$transactionsToday = $dashboardManager->getTransactionCountToday();
$totalProducts     = $dashboardManager->getTotalProducts();

$recentTransactions  = $dashboardManager->getRecentTransactions(5);
$topProducts         = $dashboardManager->getTopSellingProducts(5);
$lowStockItems       = $dashboardManager->getLowStockAlerts();
$expiringItems       = $dashboardManager->getExpiringProducts();
$monthlySalesTrend   = $dashboardManager->getMonthlySalesTrend();

date_default_timezone_set('Asia/Manila');
?>

<!-- Inter Font & Dashboard CSS -->
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../css/dashboard.css">

<!-- Pass PHP data to dashboard.js without mixing PHP into the JS file -->
<script>
  window.dashboardData = {
    monthlySalesTrend: <?php echo json_encode($monthlySalesTrend); ?>
  };
</script>

<div class="dash-wrapper">

  <!-- Header -->
  <div class="dash-header">
    <h4>Business Overview</h4>
    <span class="date-badge"><?php echo date('F j, Y'); ?></span>
  </div>

  <!-- ── Stat Cards ── -->
  <div class="row g-3 mb-4">
    <div class="col-6 col-xl-3">
      <div class="stat-card card-red">
        <div class="stat-icon blue"><i class="fas fa-peso-sign"></i></div>
        <div>
          <div class="stat-label">Today's Sales</div>
          <div class="stat-value">₱<?php echo number_format($totalSalesToday, 2); ?></div>
          <div class="stat-sub">Revenue today</div>
        </div>
      </div>
    </div>
    <div class="col-6 col-xl-3">
      <div class="stat-card card-rose">
        <div class="stat-icon green"><i class="fas fa-chart-line"></i></div>
        <div>
          <div class="stat-label">Monthly Sales</div>
          <div class="stat-value">₱<?php echo number_format($totalSalesMonth, 2); ?></div>
          <div class="stat-sub"><?php echo date('F Y'); ?></div>
        </div>
      </div>
    </div>
    <div class="col-6 col-xl-3">
      <div class="stat-card card-teal">
        <div class="stat-icon cyan"><i class="fas fa-receipt"></i></div>
        <div>
          <div class="stat-label">Transactions</div>
          <div class="stat-value"><?php echo $transactionsToday; ?></div>
          <div class="stat-sub">Today</div>
        </div>
      </div>
    </div>
    <div class="col-6 col-xl-3">
      <div class="stat-card card-indigo">
        <div class="stat-icon amber"><i class="fas fa-boxes-stacked"></i></div>
        <div>
          <div class="stat-label">Total Products</div>
          <div class="stat-value"><?php echo $totalProducts; ?></div>
          <div class="stat-sub">In inventory</div>
        </div>
      </div>
    </div>
  </div>

  <!-- ── Chart ── -->
  <div class="row mb-4">
    <div class="col-12">
      <div class="dash-card">
        <div class="dash-card-header">
          <h6><i class="fas fa-chart-bar me-2" style="color:#c0392b;"></i>Monthly Sales Performance</h6>
          <span class="pill pill-red"><?php echo date('Y'); ?></span>
        </div>
        <div class="dash-card-body">
          <div class="chart-wrapper">
            <canvas id="salesChart"></canvas>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- ── Transactions & Top Products ── -->
  <div class="row g-3 mb-4">
    <!-- Recent Transactions -->
    <div class="col-12 col-xl-7">
      <div class="dash-card">
        <div class="dash-card-header">
          <h6><i class="fas fa-clock-rotate-left me-2"></i>Recent Transactions</h6>
          <a href="#" class="pill pill-gray text-decoration-none">View All</a>
        </div>
        <div class="dash-card-body">
          <?php if (empty($recentTransactions)): ?>
            <div class="empty-state"><i class="fas fa-inbox"></i>No transactions yet</div>
          <?php else: ?>
            <table class="dash-table">
              <thead>
                <tr>
                  <th>Ref ID</th>
                  <th>Date / Time</th>
                  <th>Customer</th>
                  <th>Total Amount</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($recentTransactions as $tx): ?>
                  <tr>
                    <td class="ref-id">#<?php echo str_pad($tx['id'], 6, '0', STR_PAD_LEFT); ?></td>
                    <td><?php echo date('M d, H:i', strtotime($tx['created_at'])); ?></td>
                    <td><?php echo htmlspecialchars($tx['customer_name'] ?? 'Guest'); ?></td>
                    <td class="amount-pos">₱<?php echo number_format($tx['total_amount'], 2); ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- Top Selling Products -->
    <div class="col-12 col-xl-5">
      <div class="dash-card">
        <div class="dash-card-header">
          <h6><i class="fas fa-trophy me-2 text-warning"></i>Top Selling Products</h6>
        </div>
        <div class="dash-card-body">
          <?php if (empty($topProducts)): ?>
            <div class="empty-state"><i class="fas fa-box-open"></i>No sales data yet</div>
          <?php else: ?>
            <?php foreach ($topProducts as $i => $product): ?>
              <div class="top-product-item">
                <div class="d-flex align-items-center">
                  <div class="product-rank"><?php echo $i + 1; ?></div>
                  <div>
                    <div class="product-name"><?php echo htmlspecialchars($product['name']); ?></div>
                    <div class="product-sold"><?php echo $product['total_sold']; ?> units sold</div>
                  </div>
                </div>
                <div class="product-price">₱<?php echo number_format($product['price'], 2); ?></div>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <!-- ── Alert Widgets ── -->
  <div class="row g-3">
    <!-- Low Stock -->
    <div class="col-12 col-md-6">
      <div class="dash-card alert-card-warn">
        <div class="dash-card-header">
          <h6><i class="fas fa-triangle-exclamation me-2"></i>Low Stock Alerts</h6>
          <?php if (!empty($lowStockItems)): ?>
            <span class="badge-warn"><?php echo count($lowStockItems); ?> items</span>
          <?php endif; ?>
        </div>
        <div class="dash-card-body">
          <?php if (empty($lowStockItems)): ?>
            <div class="empty-state"><i class="fas fa-circle-check" style="color:#16a34a;opacity:1"></i>All inventory looks good!</div>
          <?php else: ?>
            <table class="dash-table">
              <thead><tr><th>Product</th><th>Stock</th><th>Reorder At</th></tr></thead>
              <tbody>
                <?php foreach ($lowStockItems as $item): ?>
                  <tr>
                    <td class="fw-semibold"><?php echo htmlspecialchars($item['name']); ?></td>
                    <td><span class="badge-warn"><?php echo $item['stock_quantity']; ?></span></td>
                    <td style="color:#94a3b8;font-size:.8rem"><?php echo $item['reorder_level']; ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- Expiring Soon -->
    <div class="col-12 col-md-6">
      <div class="dash-card alert-card-danger">
        <div class="dash-card-header">
          <h6><i class="fas fa-calendar-xmark me-2"></i>Expiring Soon (30 Days)</h6>
          <?php if (!empty($expiringItems)): ?>
            <span class="badge-danger"><?php echo count($expiringItems); ?> items</span>
          <?php endif; ?>
        </div>
        <div class="dash-card-body">
          <?php if (empty($expiringItems)): ?>
            <div class="empty-state"><i class="fas fa-circle-check" style="color:#16a34a;opacity:1"></i>No immediate expiries.</div>
          <?php else: ?>
            <table class="dash-table">
              <thead><tr><th>Product</th><th>Expiry Date</th><th>Status</th></tr></thead>
              <tbody>
                <?php foreach ($expiringItems as $item): ?>
                  <?php $days = (strtotime($item['expiry_date']) - time()) / 86400; ?>
                  <tr>
                    <td class="fw-semibold"><?php echo htmlspecialchars($item['name']); ?></td>
                    <td style="font-size:.82rem"><?php echo date('M d, Y', strtotime($item['expiry_date'])); ?></td>
                    <td>
                      <?php if ($days <= 10): ?>
                        <span class="badge-danger">Critical</span>
                      <?php else: ?>
                        <span class="badge-warn">Warning</span>
                      <?php endif; ?>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

</div><!-- end .dash-wrapper -->

<!-- Dashboard Chart -->
<script src="../js/dashboard.js?v=2.0"></script>