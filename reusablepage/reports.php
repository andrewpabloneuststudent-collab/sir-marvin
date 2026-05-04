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
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="report-title">MMB Drugstore Reports</h2>
                <small class="text-muted">Generated: <?= date("F d, Y h:i A") ?></small>
            </div>
            <button onclick="downloadPDF()" class="btn btn-success">
                📄 Download PDF
            </button>
        </div>

        <div id="reportContent">

            <!-- SUMMARY -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card shadow-sm summary-card">
                        <div>Total Sales</div>
                        <div class="summary-value text-success">
                            ₱<?= number_format($sales['total_sales'] ?? 0, 2) ?>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card shadow-sm summary-card">
                        <div>Total Transactions</div>
                        <div class="summary-value">
                            <?= $sales['total_transactions'] ?? 0 ?>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card shadow-sm summary-card">
                        <div>Average Sale</div>
                        <div class="summary-value text-primary">
                            ₱<?= number_format($sales['avg_sale'] ?? 0, 2) ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TOP PRODUCTS -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-dark text-white">
                    🔥 Top Selling Products
                </div>
                <div class="card-body">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Units Sold</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($topProducts as $row): ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['product_name']) ?></td>
                                    <td><?= $row['total_sold'] ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- INVENTORY -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-dark text-dark">
                    📦 Inventory (Expiry Monitoring)
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Stock</th>
                                <th>Expiry</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($inventory as $row): ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['product_name']) ?></td>
                                    <td><?= $row['quantity'] ?></td>
                                    <td class="<?= (strtotime($row['expiry_date']) < time()) ? 'expired' : '' ?>">
                                        <?= $row['expiry_date'] ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- DISCOUNTS -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-dark text-white">
                    🎯 Discount Usage
                </div>
                <div class="card-body">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Discount</th>
                                <th>Usage</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($discounts as $row): ?>
                                <tr>
                                    <td><?= $row['discount_name'] ?></td>
                                    <td><?= $row['used_count'] ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- CASHIERS -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-dark text-white">
                    👨‍💼 Cashier Performance
                </div>
                <div class="card-body">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Cashier</th>
                                <th>Transactions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($cashiers as $row): ?>
                                <tr>
                                    <td><?= $row['username'] ?></td>
                                    <td><?= $row['total_transactions'] ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

    </div>

    <!-- PDF SCRIPT -->
    <script src="../js/reports.js">

    </script>

</body>

</html>