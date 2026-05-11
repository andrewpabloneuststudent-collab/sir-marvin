<?php
require_once __DIR__ . "/../conn/Database.php";
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

<link rel="stylesheet" href="../css/report.css">

<div class="container-fluid px-4 mt-3">

    <!-- HEADER -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0" style="font-weight:800; color:#1a2535;">
                <i class="fas fa-chart-pie me-2" style="color:#c0392b;"></i>MMB Drugstore Reports
            </h4>
            <small style="color:#94a3b8; font-size:.78rem;">Generated: <?= date("F d, Y h:i A") ?></small>
        </div>
        <button onclick="downloadPDF()" class="btn btn-primary">
            <i class="fas fa-file-pdf me-1"></i> Download PDF
        </button>
    </div>

    <div id="reportContent">

        <!-- SUMMARY -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card shadow-sm summary-card">
                    <div class="card-body text-center py-3">
                        <div style="font-size:.75rem; font-weight:600; color:#94a3b8; text-transform:uppercase; letter-spacing:.5px;">Total Sales</div>
                        <div style="font-size:1.4rem; font-weight:800; color:#16a34a;">
                            ₱<?= number_format($sales['total_sales'] ?? 0, 2) ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card shadow-sm summary-card">
                    <div class="card-body text-center py-3">
                        <div style="font-size:.75rem; font-weight:600; color:#94a3b8; text-transform:uppercase; letter-spacing:.5px;">Total Transactions</div>
                        <div style="font-size:1.4rem; font-weight:800; color:#1a2535;">
                            <?= $sales['total_transactions'] ?? 0 ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card shadow-sm summary-card">
                    <div class="card-body text-center py-3">
                        <div style="font-size:.75rem; font-weight:600; color:#94a3b8; text-transform:uppercase; letter-spacing:.5px;">Average Sale</div>
                        <div style="font-size:1.4rem; font-weight:800; color:#c0392b;">
                            ₱<?= number_format($sales['avg_sale'] ?? 0, 2) ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- TOP PRODUCTS -->
        <div class="card shadow-sm mb-4">
            <div class="card-header" style="background:#1a2535; color:#fff; font-weight:700; font-size:.85rem;">
                <i class="fas fa-trophy me-2" style="color:#f59e0b;"></i>Top Selling Products
            </div>
            <div class="card-body p-0">
                <table class="table table-striped table-hover mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>Product</th>
                            <th>Units Sold</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($topProducts as $row): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['product_name']) ?></td>
                                <td><strong><?= $row['total_sold'] ?></strong></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- INVENTORY -->
        <div class="card shadow-sm mb-4">
            <div class="card-header" style="background:#1a2535; color:#fff; font-weight:700; font-size:.85rem;">
                <i class="fas fa-boxes me-2" style="color:#14b8a6;"></i>Inventory (Expiry Monitoring)
            </div>
            <div class="card-body p-0">
                <table class="table table-striped table-hover mb-0">
                    <thead class="table-dark">
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
                                <td style="<?= ($row['expiry_date'] && strtotime($row['expiry_date']) < time()) ? 'color:#dc2626; font-weight:700;' : '' ?>">
                                    <?= $row['expiry_date'] ?: 'N/A' ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- DISCOUNTS -->
        <div class="card shadow-sm mb-4">
            <div class="card-header" style="background:#1a2535; color:#fff; font-weight:700; font-size:.85rem;">
                <i class="fas fa-tags me-2" style="color:#e74c3c;"></i>Discount Usage
            </div>
            <div class="card-body p-0">
                <table class="table table-striped table-hover mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>Discount</th>
                            <th>Usage</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($discounts as $row): ?>
                            <tr>
                                <td><?= $row['discount_name'] ?></td>
                                <td><strong><?= $row['used_count'] ?></strong></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- CASHIERS -->
        <div class="card shadow-sm mb-4">
            <div class="card-header" style="background:#1a2535; color:#fff; font-weight:700; font-size:.85rem;">
                <i class="fas fa-user-tie me-2" style="color:#6366f1;"></i>Cashier Performance
            </div>
            <div class="card-body p-0">
                <table class="table table-striped table-hover mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>Cashier</th>
                            <th>Transactions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cashiers as $row): ?>
                            <tr>
                                <td><?= $row['username'] ?></td>
                                <td><strong><?= $row['total_transactions'] ?></strong></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

</div>

<!-- PDF SCRIPT -->
<script src="../js/reports.js"></script>