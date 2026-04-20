<?php
require_once __DIR__ . "/../function/loginfunction.php"; //yours is loginfunction
require_once __DIR__ . "/../conn/connection_links.php";
session_start();

// 🔐 CHECK IF LOGGED IN
if (!isset($_SESSION['user_id'])) {
    header("Location: /MMBPOS/login.php");
    exit;
}


$page = $_GET['page'] ?? 'dashboard';

if (isset($_GET['editProduct'])) {
    $page = 'product';
}



// 🔐 OPTIONAL: CHECK ROLE (case-insensitive)

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Owner Dashboard</title>

</head>

<body class="d-flex flex-column m-0 p-0" style="min-height: 100vh;">
    <?php include __DIR__ . "/../reusablepage/header.php"; ?>


    <div class="d-flex">

        <!-- SIDEBAR -->
        <div class="offcanvas-lg offcanvas-start" tabindex="-1" id="sidebar" style="--bs-offcanvas-width: 50%;">
            <div class="offcanvas-header d-lg-none">
                <h5>Menu</h5>
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
            </div>

            <div class="offcanvas-body">
                <div class="nav flex-column nav-pills me-3" role="tablist">

                    <a class="nav-link active" data-bs-toggle="pill" href="#v-pills-dashboard">Dashboard</a>
                    <a class="nav-link" data-bs-toggle="pill" href="#v-pills-product">Product Management</a>
                    <a class="nav-link" data-bs-toggle="pill" href="#v-pills-inventory">Inventory</a>
                    <a class="nav-link" data-bs-toggle="pill" href="#v-pills-sales">Sales (POS)</a>
                    <a class="nav-link" data-bs-toggle="pill" href="#v-pills-purchase">Purchase / Restock</a>
                    <a class="nav-link" data-bs-toggle="pill" href="#v-pills-reports">Reports</a>
                    <a class="nav-link" data-bs-toggle="pill" href="#v-pills-security">User Authentication &Security</a>
                    <a class="nav-link" data-bs-toggle="pill" href="#v-pills-users">User Management</a>
                    <a class="nav-link" data-bs-toggle="pill" href="#v-pills-system">System Settings</a>

                </div>
            </div>
        </div>

        <div class="tab-content" id="v-pills-tabContent">
            <div class="tab-pane fade show active" id="v-pills-dashboard" role="tabpanel"
                aria-labelledby="v-pills-dashboard-tab">
                <?php include __DIR__ . "/../reusablepage/dashboard.php"; ?>
            </div>
            <div class="tab-pane fade" id="v-pills-product" role="tabpanel" aria-labelledby="v-pills-product-tab">
                <?php include __DIR__ . "/../reusablepage/productmanagement.php"; ?>
            </div>
            <div class="tab-pane fade" id="v-pills-inventory" role="tabpanel" aria-labelledby="v-pills-inventory-tab">
                <?php include __DIR__ . "/../reusablepage/inventorymanagement.php"; ?>
            </div>
            <div class="tab-pane fade" id="v-pills-sales" role="tabpanel" aria-labelledby="v-pills-sales-tab">
                <?php include __DIR__ . "/../reusablepage/salespos.php"; ?>
            </div>
            <div class="tab-pane fade" id="v-pills-purchase" role="tabpanel" aria-labelledby="v-pills-purchase-tab">
                <?php include __DIR__ . "/../reusablepage/purchaserestock.php"; ?>
            </div>
            <div class="tab-pane fade" id="v-pills-reports" role="tabpanel" aria-labelledby="v-pills-reports-tab">
                <?php include __DIR__ . "/../reusablepage/reports.php"; ?>
            </div>
            <div class="tab-pane fade" id="v-pills-security" role="tabpanel" aria-labelledby="v-pills-security-tab">
                <?php include __DIR__ . "/../reusablepage/userauthentication.php"; ?>
            </div>
            <div class="tab-pane fade" id="v-pills-users" role="tabpanel" aria-labelledby="v-pills-users-tab">
                <?php include __DIR__ . "/../reusablepage/usermanagement.php"; ?>
            </div>
            <div class="tab-pane fade" id="v-pills-system" role="tabpanel" aria-labelledby="v-pills-system-tab">
                <?php include __DIR__ . "/../reusablepage/systemsettings.php"; ?>
            </div>
        </div>
    </div>
    <?php include __DIR__ . "/../reusablepage/footer.php"; ?>

    
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const params = new URLSearchParams(window.location.search);

            if (params.has("editProduct")) {
                // Activate Product tab
                let trigger = document.querySelector('[href="#v-pills-product"]');
                if (trigger) {
                    let tab = new bootstrap.Tab(trigger);
                    tab.show();
                }
            }
        });
    </script>
</body>

</html>