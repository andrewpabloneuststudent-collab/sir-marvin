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
                <small class="text-muted">Generated: <?= date("F d, Y h:i A") ?></small>
            </div>
            <button onclick="downloadPDF()" class="btn btn-success btn-lg" style="background: linear-gradient(135deg, #0f766e 0%, #14b8a6 100%); border: none; font-weight: 600; padding: 10px 25px;">
                📄 Download PDF
            </button>
        </div>

        <div id="reportContent">

            <!-- SUMMARY -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card shadow-sm summary-card">
                        <div><i class="fas fa-money-bill-wave text-success"></i> Total Revenue</div>
                        <div class="summary-value text-success">
                            ₱<?= number_format($sales['total_sales'] ?? 0, 2) ?>
                        </div>
                        <small class="text-muted">All-time sales</small>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card shadow-sm summary-card">
                        <div><i class="fas fa-receipt text-primary"></i> Total Transactions</div>
                        <div class="summary-value">
                            <?= $sales['total_transactions'] ?? 0 ?>
                        </div>
                        <small class="text-muted">All receipts processed</small>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card shadow-sm summary-card">
                        <div><i class="fas fa-chart-bar text-info"></i> Average Per Transaction</div>
                        <div class="summary-value text-primary">
                            ₱<?= number_format($sales['avg_sale'] ?? 0, 2) ?>
                        </div>
                        <small class="text-muted">Average transaction value</small>
                    </div>
                </div>
            </div>

            <!-- TOP PRODUCTS -->
            <div class="card shadow-sm mb-4">
                <div class="card-header" style="background: linear-gradient(135deg, #1a3a52 0%, #2c3e50 100%);">
                    <i class="fas fa-star"></i> Top 5 Best-Selling Products
                </div>
                <div class="card-body">
                    <table class="table table-striped">
                        <thead>
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

            <!-- EXPIRED PRODUCTS -->
            <div class="card shadow-sm mb-4 border-danger">
                <div class="card-header" style="background: linear-gradient(135deg, #7f1d1d 0%, #dc2626 100%);">
                    <i class="fas fa-exclamation-triangle"></i> Expired Products (Action Required)
                </div>
                <div class="card-body">
                    <?php 
                    $expiredProducts = array_filter($inventory, function($row) {
                        return $row['expiry_date'] && strtotime($row['expiry_date']) < time();
                    });
                    ?>
                    <?php if (empty($expiredProducts)): ?>
                        <div class="alert alert-success mb-0">
                            <i class="fas fa-check-circle"></i> No expired products. All items are within expiry dates.
                        </div>
                    <?php else: ?>
                        <table class="table table-bordered table-striped">
                            <thead class="table-danger">
                                <tr>
                                    <th><i class="fas fa-box"></i> Product Name</th>
                                    <th><i class="fas fa-inventory"></i> Current Stock</th>
                                    <th><i class="fas fa-calendar-times text-danger"></i> Expired Since</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($expiredProducts as $row): ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($row['product_name']) ?></strong></td>
                                        <td><?= $row['quantity'] ?> units</td>
                                        <td style="color: #dc3545; font-weight: bold;">
                                            <?= date('M d, Y', strtotime($row['expiry_date'])) ?> (<?= ceil((time() - strtotime($row['expiry_date'])) / 86400) ?> days ago)
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>


            <!-- CASHIERS -->
            <div class="card shadow-sm mb-4">
                <div class="card-header" style="background: linear-gradient(135deg, #0369a1 0%, #0284c7 100%);">
                    <i class="fas fa-user-tie"></i> Cashier Performance Report
                </div>
                <div class="card-body">
                    <table class="table table-striped">
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

            <!-- PWD & SENIOR DISCOUNT BREAKDOWN -->
            <div class="card shadow-sm mb-4">
                <div class="card-header" style="background: linear-gradient(135deg, #1e7e74 0%, #0f766e 100%);">
                    <i class="fas fa-hand-holding-heart"></i> PWD & Senior Citizen Discount Report
                </div>
                <div class="card-body">
                    <table class="table table-bordered table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th><i class="fas fa-tag"></i> Discount Type</th>
                                <th><i class="fas fa-check-circle"></i> Times Applied</th>
                                <th><i class="fas fa-money-bill-wave text-danger"></i> Total Discount Given</th>
                                <th><i class="fas fa-calculator"></i> Average Discount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $totalPwdSeniorDiscount = 0;
                            foreach ($discountBreakdown as $row): 
                                $totalPwdSeniorDiscount += $row['total_discount_given'] ?? 0;
                            ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($row['discount_name']) ?></strong></td>
                                    <td><span class="badge bg-primary"><?= $row['usage_count'] ?? 0 ?></span></td>
                                    <td class="text-danger">
                                        <strong>₱<?= number_format($row['total_discount_given'] ?? 0, 2) ?></strong>
                                    </td>
                                    <td>₱<?= number_format($row['avg_discount'] ?? 0, 2) ?></td>
                                </tr>
                            <?php endforeach; ?>
                            <tr class="table-dark">
                                <td colspan="2"><strong><i class="fas fa-sum"></i> Total Discount Given (PWD & Senior)</strong></td>
                                <td class="text-danger"><strong>₱<?= number_format($totalPwdSeniorDiscount, 2) ?></strong></td>
                                <td></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- DAILY SALES -->
            <div class="card shadow-sm mb-4">
                <div class="card-header" style="background: linear-gradient(135deg, #0f766e 0%, #14b8a6 100%);">
                    <i class="fas fa-calendar-alt"></i> Daily Sales Summary (Last 30 Days)
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th><i class="fas fa-calendar"></i> Date</th>
                                    <th><i class="fas fa-receipt"></i> # of Transactions</th>
                                    <th><i class="fas fa-money-bill-wave text-success"></i> Daily Revenue</th>
                                    <th><i class="fas fa-chart-bar"></i> Avg per Transaction</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $totalDailySales = 0;
                                foreach ($dailySales as $row): 
                                    $totalDailySales += $row['daily_total'] ?? 0;
                                ?>
                                    <tr>
                                        <td><strong><?= date('l, M d, Y', strtotime($row['sale_date'])) ?></strong></td>
                                        <td><?= $row['total_transactions'] ?></td>
                                        <td class="text-success"><strong>₱<?= number_format($row['daily_total'] ?? 0, 2) ?></strong></td>
                                        <td>₱<?= number_format($row['daily_avg'] ?? 0, 2) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- YEARLY/MONTHLY SALES -->
            <div class="card shadow-sm mb-4">
                <div class="card-header" style="background: linear-gradient(135deg, #1a3a52 0%, #2c3e50 100%);">
                    <i class="fas fa-chart-line"></i> Monthly Sales Trend & Performance
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th><i class="fas fa-calendar-check"></i> Month & Year</th>
                                    <th><i class="fas fa-receipt"></i> # of Transactions</th>
                                    <th><i class="fas fa-money-bill-wave text-success"></i> Monthly Revenue</th>
                                    <th><i class="fas fa-chart-bar"></i> Avg per Transaction</th>
                                    <th><i class="fas fa-arrow-up-down text-warning"></i> Performance vs Previous</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $monthNames = ['', 'January', 'February', 'March', 'April', 'May', 'June', 
                                               'July', 'August', 'September', 'October', 'November', 'December'];
                                $prevMonthSales = 0;
                                foreach ($yearlySales as $row): 
                                    $currentSales = $row['monthly_total'] ?? 0;
                                    $percentChange = $prevMonthSales > 0 ? (($currentSales - $prevMonthSales) / $prevMonthSales) * 100 : 0;
                                    $performanceClass = $percentChange >= 0 ? 'text-success' : 'text-danger';
                                    $performanceIcon = $percentChange >= 0 ? '📈' : '📉';
                                ?>
                                    <tr>
                                        <td><strong><?= $monthNames[$row['sale_month']] ?> <?= $row['sale_year'] ?></strong></td>
                                        <td><?= $row['total_transactions'] ?></td>
                                        <td class="text-success"><strong>₱<?= number_format($currentSales, 2) ?></strong></td>
                                        <td>₱<?= number_format($row['monthly_avg'] ?? 0, 2) ?></td>
                                        <td class="<?= $performanceClass ?>">
                                            <?= $performanceIcon ?> 
                                            <?= number_format($percentChange, 2) ?>%
                                        </td>
                                    </tr>
                                    <?php $prevMonthSales = $currentSales; ?>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- ALL TRANSACTIONS -->
            <div class="card shadow-sm mb-4">
                <div class="card-header" style="background: linear-gradient(135deg, #475569 0%, #334155 100%);">
                    <i class="fas fa-receipt"></i> Transaction Details (Last 500)
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover table-sm">
                            <thead class="table-dark">
                                <tr>
                                    <th>Ref #</th>
                                    <th>Date & Time</th>
                                    <th>Cashier</th>
                                    <th>Items</th>
                                    <th>Subtotal</th>
                                    <th>Discount Type</th>
                                    <th>Discount Amount</th>
                                    <th>Total Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $grandTotalAllTransactions = 0;
                                $grandTotalDiscounts = 0;
                                foreach ($allTransactions as $row): 
                                    $grandTotalAllTransactions += $row['total_amount'] ?? 0;
                                    $grandTotalDiscounts += $row['discount_amount'] ?? 0;
                                ?>
                                    <tr>
                                        <td><small><code>#<?= $row['id'] ?></code></small></td>
                                        <td><?= date('M d, Y h:i A', strtotime($row['transaction_date'])) ?></td>
                                        <td><?= htmlspecialchars($row['username'] ?? 'N/A') ?></td>
                                        <td><small><?= $row['items_count'] ?? 0 ?> items</small></td>
                                        <td>₱<?= number_format($row['subtotal'] ?? 0, 2) ?></td>
                                        <td><?= htmlspecialchars($row['discount_name'] ?? 'None') ?></td>
                                        <td class="text-danger">
                                            <?= $row['discount_amount'] > 0 ? '-₱' . number_format($row['discount_amount'], 2) : '—' ?>
                                        </td>
                                        <td class="text-success"><strong>₱<?= number_format($row['total_amount'] ?? 0, 2) ?></strong></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <!-- GRAND TOTALS -->
                    <div class="alert alert-info mt-3">
                        <div class="row">
                            <div class="col-md-4">
                                <strong>Total Transactions:</strong> <span class="badge bg-primary"><?= count($allTransactions) ?></span>
                            </div>
                            <div class="col-md-4">
                                <strong>Total Discounts Given:</strong> 
                                <span class="badge bg-danger">₱<?= number_format($grandTotalDiscounts, 2) ?></span>
                            </div>
                            <div class="col-md-4">
                                <strong>Grand Total Sales:</strong> 
                                <span class="badge bg-success">₱<?= number_format($grandTotalAllTransactions, 2) ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

    </div>

    <!-- PDF SCRIPT -->
    <script src="../js/reports.js">

    </script>

</body>

</html>