<?php
require_once __DIR__ . "/../conn/Database.php";
require_once __DIR__ . "/../conn/connection_links.php";
require_once __DIR__ . "/../function/Reports.php";

use Classes\Reports;

$report = new Reports($db);

// GET ALL DATA
$data = $report->getAllReports();

// ASSIGN VARIABLES
$sales = $data['sales'];
$topProducts = $data['topProducts'];
$inventory = $data['inventory'];
$discounts = $data['discounts'];
$cashiers = $data['cashiers'];
$dailySales = $data['dailySales'];
$yearlySales = $data['yearlySales'];
$discountBreakdown = $data['discountBreakdown'];
$allTransactions = $data['transactions'];
$returns = $data['returns'] ?? [];
$totalDiscounts = $data['totalDiscounts'];
$totalVatExemption = $data['totalVatExemption'];
$totalRefunds = $data['totalRefunds'] ?? 0;
$realRevenueToday = $data['realRevenueToday'];
$realRevenueMonth = $data['realRevenueMonth'];
$realRevenueYear = $data['realRevenueYear'];
$totalSalesYear = $data['totalSalesYear'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Drugstore Reports</title>
    <link rel="stylesheet" href="../css/report.css">
</head>

<body class="p-4">

    <div class="container">

        <!-- HEADER -->
        <div class="d-flex justify-content-between align-items-center mb-5 p-4 bg-white rounded shadow-sm" style="border-left: 5px solid #1a3a52;">
            <div>
                <h2 class="report-title m-0">📊 MMB Drugstore Reports</h2>
            </div>
        </div>

        <div id="reportContent">

            <!-- SUMMARY -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card shadow-sm summary-card" style="cursor: pointer;" data-bs-toggle="modal" data-bs-target="#transactionModal">
                        <div><i class="fas fa-money-bill-wave text-success"></i> Total Revenue</div>
                        <div class="summary-value text-success">
                            ₱<?= number_format($sales['total_sales'] ?? 0, 2) ?>
                        </div>
                        <small class="text-muted">All-time sales</small>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card shadow-sm summary-card" style="cursor: pointer;" data-bs-toggle="modal" data-bs-target="#topProductsModal">
                        <div><i class="fas fa-crown text-warning"></i> Top Selling Product</div>
                        <div class="summary-value text-warning">
                            <?= htmlspecialchars($topProducts[0]['product_name'] ?? 'N/A') ?>
                        </div>
                        <small class="text-muted"><?= $topProducts[0]['total_sold'] ?? 0 ?> units sold</small>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card shadow-sm summary-card" style="cursor: pointer;" data-bs-toggle="modal" data-bs-target="#cashierModal">
                        <div><i class="fas fa-user-tie text-primary"></i> Top Cashier</div>
                        <div class="summary-value text-primary">
                            <?= htmlspecialchars($cashiers[0]['username'] ?? 'N/A') ?>
                        </div>
                        <small class="text-muted"><?= $cashiers[0]['total_transactions'] ?? 0 ?> transactions</small>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card shadow-sm summary-card" style="cursor: pointer;" data-bs-toggle="modal" data-bs-target="#top5ProductsModal">
                        <div><i class="fas fa-box text-info"></i> Top 5 Products</div>
                        <div class="summary-value text-info">
                            <?php 
                                $top5Total = 0;
                                for ($i = 0; $i < min(5, count($topProducts)); $i++) {
                                    $top5Total += $topProducts[$i]['total_sold'] ?? 0;
                                }
                                echo $top5Total;
                            ?>
                        </div>
                        <small class="text-muted">Total units sold</small>
                    </div>
                </div>

                <div class="col-md-4">
                    <?php 
                        $expiredProducts = array_filter($inventory, function($row) {
                            return $row['expiry_date'] && strtotime($row['expiry_date']) < time();
                        });
                        $expiredCount = count($expiredProducts);
                    ?>
                    <div class="card shadow-sm summary-card" style="cursor: pointer; <?= $expiredCount > 0 ? 'border-danger; background-color: #fef2f2;' : '' ?>" data-bs-toggle="modal" data-bs-target="#expiredProductsModal">
                        <div><i class="fas fa-exclamation-triangle <?= $expiredCount > 0 ? 'text-danger' : 'text-success' ?>"></i> Expired Products</div>
                        <div class="summary-value <?= $expiredCount > 0 ? 'text-danger' : 'text-success' ?>">
                            <?= $expiredCount ?>
                        </div>
                        <small class="text-muted"><?= $expiredCount > 0 ? 'Action required' : 'All items safe' ?></small>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card shadow-sm summary-card" style="cursor: pointer;" data-bs-toggle="modal" data-bs-target="#returnsModal">
                        <div><i class="fas fa-undo-alt text-danger"></i> Total Refunds</div>
                        <div class="summary-value text-danger">
                            ₱<?= number_format($totalRefunds, 2) ?>
                        </div>
                        <small class="text-muted"><?= count($returns) ?> return transaction(s)</small>
                    </div>
                </div>

                <div class="col-md-4">
                    <?php 
                        $totalDailySalesLast30 = 0;
                        $daysWithSales = count($dailySales);
                        $avgDailySales = 0;
                        foreach ($dailySales as $row) {
                            $totalDailySalesLast30 += $row['daily_total'] ?? 0;
                        }
                        if ($daysWithSales > 0) {
                            $avgDailySales = $totalDailySalesLast30 / $daysWithSales;
                        }
                    ?>
                    <div class="card shadow-sm summary-card" style="cursor: pointer;" data-bs-toggle="modal" data-bs-target="#dailySalesModal">
                        <div><i class="fas fa-chart-line text-primary"></i> Daily Sales Summary</div>
                        <div class="summary-value text-primary">
                            ₱<?= number_format($avgDailySales, 2) ?>
                        </div>
                        <small class="text-muted"><?= $daysWithSales ?> days recorded</small>
                    </div>
                </div>

                <div class="col-md-4">
                    <?php 
                        $monthlyTotalSales = 0;
                        $monthsRecorded = count($yearlySales);
                        $avgMonthlySales = 0;
                        $bestMonth = '';
                        $bestMonthSales = 0;

                        $monthNames = ['', 'January', 'February', 'March', 'April', 'May', 'June', 
                                       'July', 'August', 'September', 'October', 'November', 'December'];

                        foreach ($yearlySales as $row) {
                            $monthlyTotalSales += $row['net_revenue'] ?? 0;

                            if (($row['net_revenue'] ?? 0) > $bestMonthSales) {
                                $bestMonthSales = $row['net_revenue'];
                                $bestMonth = $monthNames[$row['sale_month']] . ' ' . $row['sale_year'];
                            }
                        }

                        if ($monthsRecorded > 0) {
                            $avgMonthlySales = $monthlyTotalSales / $monthsRecorded;
                        }
                    ?>
                    <div class="card shadow-sm summary-card" style="cursor: pointer;" data-bs-toggle="modal" data-bs-target="#monthlySalesModal">
                        <div><i class="fas fa-chart-area text-success"></i> Monthly Sales Trend</div>
                        <div class="summary-value text-success">
                            ₱<?= number_format($avgMonthlySales, 2) ?>
                        </div>
                        <small class="text-muted">Best Month: <?= $bestMonth ?: 'N/A' ?></small>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card shadow-sm summary-card" style="cursor: pointer;" data-bs-toggle="modal" data-bs-target="#vatDiscountModal">
                        <div><i class="fas fa-receipt text-warning"></i> VAT Exemption & Discount</div>
                        <div class="summary-value text-warning">
                            ₱<?= number_format(($totalVatExemption ?? 0) + ($totalDiscounts ?? 0), 2) ?>
                        </div>
                        <small class="text-muted">
                            VAT: ₱<?= number_format($totalVatExemption ?? 0, 2) ?> | Discount: ₱<?= number_format($totalDiscounts ?? 0, 2) ?>
                        </small>
                    </div>
                </div>
            </div>
        </div><!-- End of #reportContent -->

        <!-- MONTHLY SALES TREND MODAL -->
        <div class="modal fade" id="monthlySalesModal" tabindex="-1" aria-labelledby="monthlySalesModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-xl">
                    <div class="modal-content">
                        <div class="modal-header" style="background: linear-gradient(135deg, #14532d 0%, #16a34a 100%); color: white;">
                            <h5 class="modal-title" id="monthlySalesModalLabel">
                                <i class="fas fa-chart-line me-2"></i>
                                Monthly Sales Trend & Performance
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>

                        <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover myTableExport">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Month & Year</th>
                                            <th>Total Transactions</th>
                                            <th>Gross Revenue</th>
                                            <th>Net Revenue</th>
                                            <th>Cost</th>
                                            <th>Profit</th>
                                            <th>Average per Transaction</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                            $prevMonthSales = 0;
                                            foreach ($yearlySales as $row):
                                                $grossRevenue = $row['gross_revenue'] ?? 0;
                                                $netRevenue = $row['net_revenue'] ?? 0;
                                                $totalCost = $row['total_cost'] ?? 0;
                                                $profit = $row['profit'] ?? 0;
                                                $percentChange = $prevMonthSales > 0
                                                    ? (($netRevenue - $prevMonthSales) / $prevMonthSales) * 100
                                                    : 0;
                                                $performanceClass = $percentChange >= 0 ? 'text-success' : 'text-danger';
                                                $performanceIcon = $percentChange >= 0 ? '📈' : '📉';
                                        ?>
                                        <tr>
                                            <td>
                                                <strong>
                                                    <?= $monthNames[$row['sale_month']] ?>
                                                    <?= $row['sale_year'] ?>
                                                </strong>
                                            </td>
                                            <td>
                                                <span class="badge bg-primary">
                                                    <?= $row['total_transactions'] ?>
                                                </span>
                                            </td>
                                            <td class="text-info">
                                                <strong>
                                                    ₱<?= number_format($grossRevenue, 2) ?>
                                                </strong>
                                            </td>
                                            <td class="text-success">
                                                <strong>
                                                    ₱<?= number_format($netRevenue, 2) ?>
                                                </strong>
                                            </td>
                                            <td class="text-warning">
                                                ₱<?= number_format($totalCost, 2) ?>
                                            </td>
                                            <td class="<?= $profit >= 0 ? 'text-success' : 'text-danger' ?>">
                                                <strong>
                                                    ₱<?= number_format($profit, 2) ?>
                                                </strong>
                                            </td>
                                            <td>
                                                ₱<?= number_format($row['monthly_avg'] ?? 0, 2) ?>
                                            </td>
                                        </tr>
                                        <?php 
                                            $prevMonthSales = $netRevenue;
                                            endforeach; 
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="modal-footer" style="background: #f8f9fa;">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                Close
                            </button>
                        </div>
                    </div>
                </div>
            </div>
    

    <!-- TRANSACTION DETAILS MODAL -->
    <div class="modal fade" id="transactionModal" tabindex="-1" aria-labelledby="transactionModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-fullscreen-lg-down">
            <div class="modal-content">
                <div class="modal-header" style="background: linear-gradient(135deg, #475569 0%, #334155 100%); color: white;">
                    <h5 class="modal-title" id="transactionModalLabel">
                        <i class="fas fa-receipt me-2"></i>Transaction Details (Last 500)
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover table-sm myTableExport">
                            <thead class="table-dark">
                                <tr>
                                    <th>Ref #</th>
                                    <th>Date & Time</th>
                                    <th>Cashier</th>
                                    <th>Items</th>
                                    <th>Subtotal</th>
                                    <th>Discount</th>
                                    <th>VAT Exempt</th>
                                    <th>Total Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $grandTotalAllTransactions = 0;
                                $grandTotalDiscounts = 0;
                                $grandTotalVatExemption = 0;
                                foreach ($allTransactions as $row): 
                                    $grandTotalAllTransactions += $row['total_amount'] ?? 0;
                                    $grandTotalDiscounts += $row['discount_total'] ?? 0;
                                    $grandTotalVatExemption += $row['total_vat_exemption'] ?? 0;
                                ?>
                                    <tr>
                                        <td><small><code>#<?= $row['id'] ?></code></small></td>
                                        <td><?= date('M d, Y h:i A', strtotime($row['transaction_date'])) ?></td>
                                        <td><?= htmlspecialchars($row['username'] ?? 'N/A') ?></td>
                                        <td><small><?= $row['items_count'] ?? 0 ?> items</small></td>
                                        <td>₱<?= number_format($row['subtotal'] ?? 0, 2) ?></td>
                                        <td class="text-danger">
                                            <?= $row['discount_total'] > 0 ? '-₱' . number_format($row['discount_total'], 2) : '—' ?>
                                        </td>
                                        <td class="text-danger">
                                            <?= $row['total_vat_exemption'] > 0 ? '-₱' . number_format($row['total_vat_exemption'], 2) : '—' ?>
                                        </td>
                                        <td class="text-success"><strong>₱<?= number_format($row['total_amount'] ?? 0, 2) ?></strong></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- GRAND TOTALS -->
                    <div class="alert alert-info mt-3 mb-0">
                        <div class="row">
                            <div class="col-md-3">
                                <strong>Total Transactions:</strong> <span class="badge bg-primary"><?= count($allTransactions) ?></span>
                            </div>
                            <div class="col-md-3">
                                <strong>Total Discounts:</strong> 
                                <span class="badge bg-danger">₱<?= number_format($grandTotalDiscounts, 2) ?></span>
                            </div>
                            <div class="col-md-3">
                                <strong>Total VAT Exemption:</strong> 
                                <span class="badge bg-warning">₱<?= number_format($grandTotalVatExemption, 2) ?></span>
                            </div>
                            <div class="col-md-3">
                                <strong>Grand Total Sales:</strong> 
                                <span class="badge bg-success">₱<?= number_format($grandTotalAllTransactions, 2) ?></span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer" style="background: #f8f9fa;">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                   
                </div>
            </div>
        </div>
    </div>

    <!-- TOP PRODUCTS MODAL -->
    <div class="modal fade" id="topProductsModal" tabindex="-1" aria-labelledby="topProductsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header" style="background: linear-gradient(135deg, #1a3a52 0%, #2c3e50 100%); color: white;">
                    <h5 class="modal-title" id="topProductsModalLabel">
                        <i class="fas fa-star me-2"></i>Top 5 Best-Selling Products
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover myTableExport">
                            <thead class="table-dark">
                                <tr>
                                    <th><i class="fas fa-cube"></i> Product Name</th>
                                    <th><i class="fas fa-chart-pie"></i> Units Sold</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($topProducts as $row): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($row['product_name']) ?></td>
                                        <td><span class="badge bg-success"><?= $row['total_sold'] ?> units</span></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer" style="background: #f8f9fa;">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- TOP 5 PRODUCTS MODAL -->
    <div class="modal fade" id="top5ProductsModal" tabindex="-1" aria-labelledby="top5ProductsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header" style="background: linear-gradient(135deg, #1a3a52 0%, #2c3e50 100%); color: white;">
                    <h5 class="modal-title" id="top5ProductsModalLabel">
                        <i class="fas fa-box me-2"></i>Top 5 Best-Selling Products
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover myTableExport">
                            <thead class="table-dark">
                                <tr>
                                    <th><i class="fas fa-cube me-2"></i>Rank</th>
                                    <th><i class="fas fa-tag me-2"></i>Product Name</th>
                                    <th><i class="fas fa-shopping-cart me-2"></i>Units Sold</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                    for ($i = 0; $i < min(5, count($topProducts)); $i++):
                                        $product = $topProducts[$i];
                                ?>
                                    <tr>
                                        <td><strong><?= $i + 1 ?></strong></td>
                                        <td><?= htmlspecialchars($product['product_name']) ?></td>
                                        <td><span class="badge bg-success"><?= $product['total_sold'] ?> units</span></td>
                                    </tr>
                                <?php endfor; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer" style="background: #f8f9fa;">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- CASHIER PERFORMANCE MODAL -->
    <div class="modal fade" id="cashierModal" tabindex="-1" aria-labelledby="cashierModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header" style="background: linear-gradient(135deg, #0369a1 0%, #0284c7 100%); color: white;">
                    <h5 class="modal-title" id="cashierModalLabel">
                        <i class="fas fa-user-tie me-2"></i>Cashier Performance Report
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover myTableExport">
                            <thead class="table-dark">
                                <tr>
                                    <th><i class="fas fa-user"></i> Cashier Name</th>
                                    <th><i class="fas fa-shopping-cart"></i> Transactions Processed</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($cashiers as $row): ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($row['username']) ?></strong></td>
                                        <td><span class="badge bg-info"><?= $row['total_transactions'] ?> transactions</span></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer" style="background: #f8f9fa;">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- EXPIRED PRODUCTS MODAL -->
    <div class="modal fade" id="expiredProductsModal" tabindex="-1" aria-labelledby="expiredProductsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header" style="background: linear-gradient(135deg, #7f1d1d 0%, #dc2626 100%); color: white;">
                    <h5 class="modal-title" id="expiredProductsModalLabel">
                        <i class="fas fa-exclamation-triangle me-2"></i>Expired Products (Action Required)
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                    <?php 
                        $expiredProducts = array_filter($inventory, function($row) {
                            return $row['expiry_date'] && strtotime($row['expiry_date']) < time();
                        });
                    ?>
                    <?php if (empty($expiredProducts)): ?>
                        <div class="alert alert-success" role="alert">
                            <i class="fas fa-check-circle"></i> No expired products. All items are within expiry dates.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover myTableExport">
                                <thead class="table-dark">
                                    <tr>
                                        <th><i class="fas fa-cube me-2"></i>Product Name</th>
                                        <th><i class="fas fa-hourglass-end me-2"></i>Expiry Date</th>
                                        <th><i class="fas fa-boxes me-2"></i>Quantity</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($expiredProducts as $row): ?>
                                        <tr class="table-danger">
                                            <td><strong><?= htmlspecialchars($row['product_name']) ?></strong></td>
                                            <td><?= date('M d, Y', strtotime($row['expiry_date'])) ?> <span class="badge bg-danger"><?= ceil((time() - strtotime($row['expiry_date'])) / 86400) ?> days ago</span></td>
                                            <td><span class="badge bg-warning"><?= $row['quantity'] ?> units</span></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="modal-footer" style="background: #f8f9fa;">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- DAILY SALES SUMMARY MODAL -->
    <div class="modal fade" id="dailySalesModal" tabindex="-1" aria-labelledby="dailySalesModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header" style="background: linear-gradient(135deg, #0f766e 0%, #14b8a6 100%); color: white;">
                    <h5 class="modal-title" id="dailySalesModalLabel">
                        <i class="fas fa-chart-line me-2"></i>Daily Sales Summary (Last 30 Days)
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover myTableExport">
                            <thead class="table-dark">
                                <tr>
                                    <th><i class="fas fa-calendar-alt me-2"></i>Date</th>
                                    <th><i class="fas fa-receipt me-2"></i>Transactions</th>
                                    <th><i class="fas fa-money-bill-wave me-2"></i>Daily Total</th>
                                    <th><i class="fas fa-calculator me-2"></i>Average</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                    $totalForSummary = 0;
                                    foreach ($dailySales as $row): 
                                        $totalForSummary += $row['daily_total'] ?? 0;
                                ?>
                                    <tr>
                                        <td><strong><?= date('l, M d, Y', strtotime($row['sale_date'])) ?></strong></td>
                                        <td><span class="badge bg-info"><?= $row['total_transactions'] ?></span></td>
                                        <td class="text-success"><strong>₱<?= number_format($row['daily_total'] ?? 0, 2) ?></strong></td>
                                        <td>₱<?= number_format($row['daily_avg'] ?? 0, 2) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr style="background-color: #f8f9fa; font-weight: bold;">
                                    <td colspan="2">Total (Last 30 Days)</td>
                                    <td class="text-success">₱<?= number_format($totalForSummary, 2) ?></td>
                                    <td>₱<?= number_format(count($dailySales) > 0 ? $totalForSummary / count($dailySales) : 0, 2) ?></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                <div class="modal-footer" style="background: #f8f9fa;">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- VAT EXEMPTION & DISCOUNT MODAL -->
    <div class="modal fade" id="vatDiscountModal" tabindex="-1" aria-labelledby="vatDiscountModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-fullscreen-lg-down">
            <div class="modal-content">
                <div class="modal-header" style="background: linear-gradient(135deg, #b45309 0%, #f59e0b 100%); color: white;">
                    <h5 class="modal-title" id="vatDiscountModalLabel">
                        <i class="fas fa-receipt me-2"></i>VAT Exemption & Discount Details
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover table-sm myTableExport">
                            <thead class="table-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>Transaction Date</th>
                                    <th>Customer Name</th>
                                    <th>Customer Type</th>
                                    <th>VAT Exemption</th>
                                    <th>Discount</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $grandTotalVatExemp = 0;
                                $grandTotalDiscount = 0;
                                $grandTotal = 0;
                                foreach ($allTransactions as $row): 
                                    $vatAmount = $row['total_vat_exemption'] ?? 0;
                                    $discAmount = $row['discount_total'] ?? 0;
                                    
                                    if ($vatAmount > 0 || $discAmount > 0):
                                        $grandTotalVatExemp += $vatAmount;
                                        $grandTotalDiscount += $discAmount;
                                        $rowTotal = $vatAmount + $discAmount;
                                        $grandTotal += $rowTotal;
                                ?>
                                    <tr>
                                        <td>
                                            <small>
                                                <?php 
                                                    if (in_array($row['customer_type'], ['pwd', 'senior']) && $row['govt_id_number']) {
                                                        echo '<code style="background-color: #dbeafe; padding: 2px 6px; border-radius: 4px;">' . htmlspecialchars($row['govt_id_number']) . '</code>';
                                                    } else {
                                                        echo '<code>#' . htmlspecialchars($row['id']) . '</code>';
                                                    }
                                                ?>
                                            </small>
                                        </td>
                                        <td><small><?= date('M d, Y h:i A', strtotime($row['transaction_date'])) ?></small></td>
                                        <td><?= htmlspecialchars($row['customer_name'] ?? 'N/A') ?></td>
                                        <td>
                                            <?php 
                                                $typeClass = match($row['customer_type']) {
                                                    'pwd' => 'bg-info',
                                                    'senior' => 'bg-warning',
                                                    default => 'bg-secondary'
                                                };
                                                $typeLabel = $row['customer_type'] ? ucfirst($row['customer_type']) : 'Regular';
                                            ?>
                                            <span class="badge <?= $typeClass ?>"><?= $typeLabel ?></span>
                                        </td>
                                        <td class="text-warning"><strong>₱<?= number_format($vatAmount, 2) ?></strong></td>
                                        <td class="text-danger"><strong>₱<?= number_format($discAmount, 2) ?></strong></td>
                                        <td class="text-info"><strong>₱<?= number_format($rowTotal, 2) ?></strong></td>
                                    </tr>
                                <?php 
                                    endif;
                                endforeach; 
                                ?>
                            </tbody>
                            <tfoot>
                                <tr style="background: linear-gradient(135deg, #1e293b 0%, #ffffff 100%); font-weight: bold; color: white; font-size: 1.1em;">
                                    <td colspan="4" style="text-align: right; padding: 15px;">TOTAL</td>
                                    <td style="padding: 15px; border-left: 2px solid #fbbf24;"><i class="fas fa-check-circle me-2"></i>₱<?= number_format($grandTotalVatExemp, 2) ?></td>
                                    <td style="padding: 15px; border-left: 2px solid #ef4444;"><i class="fas fa-tag me-2"></i>₱<?= number_format($grandTotalDiscount, 2) ?></td>
                                    <td style="padding: 15px; border-left: 2px solid #06b6d4; background-color: rgba(252, 252, 252, 0.1);">₱<?= number_format($grandTotal, 2) ?></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
    <!-- RETURNS & REFUNDS MODAL -->
    <div class="modal fade" id="returnsModal" tabindex="-1" aria-labelledby="returnsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header" style="background: linear-gradient(135deg, #c0392b 0%, #e74c3c 100%); color: white;">
                    <h5 class="modal-title" id="returnsModalLabel">
                        <i class="fas fa-undo-alt me-2"></i>Processed Returns & Refunds Log
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover myTableExport">
                            <thead class="table-dark">
                                <tr>
                                    <th>Return Ref #</th>
                                    <th>Original Tx #</th>
                                    <th>Refund Amount</th>
                                    <th>Method</th>
                                    <th>Reason / Authorization</th>
                                    <th>Processed By</th>
                                    <th>Date & Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($returns as $ret): ?>
                                    <tr>
                                        <td><strong>#<?= str_pad($ret['return_id'], 6, '0', STR_PAD_LEFT) ?></strong></td>
                                        <td>#<?= str_pad($ret['original_transaction_id'], 6, '0', STR_PAD_LEFT) ?></td>
                                        <td class="text-danger"><strong>₱<?= number_format($ret['refund_amount'], 2) ?></strong></td>
                                        <td><span class="badge bg-secondary"><?= htmlspecialchars($ret['refund_method']) ?></span></td>
                                        <td><?= htmlspecialchars($ret['reason']) ?></td>
                                        <td><?= htmlspecialchars($ret['processed_by'] ?? 'Cashier') ?></td>
                                        <td><?= date('M d, Y H:i', strtotime($ret['created_at'])) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer" style="background: #f8f9fa;">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    </div><!-- End of .container -->

    <!-- Initialize DataTable Export on modals -->
    <script>
        $(function () {
            // Initialize export tables when modals are shown
            $('#transactionModal, #monthlySalesModal, #topProductsModal, #top5ProductsModal, #cashierModal, #expiredProductsModal, #dailySalesModal, #vatDiscountModal, #returnsModal').on('shown.bs.modal', function () {
                $(this).find('.myTableExport').each(function () {
                    if (!$.fn.DataTable.isDataTable(this)) {
                        $(this).DataTable({
                            responsive: true,                                                                                                                                                                                                       
                            autoWidth: false,
                            dom: 'fBrtip',
                            buttons: ['copy', 'excel', 'pdf', 'print']
                        });
                    }
                });
            });
        });
    </script>

    <!-- PDF SCRIPT -->
    <script src="../js/reports.js">
    </script>

    <!-- Bootstrap JS for Modal -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>