<?php
require_once __DIR__ . "/../function/loginfunction.php"; //yours is loginfunction
require_once __DIR__ . "/../conn/connection_links.php";
session_start();

// 🔐 CHECK IF LOGGED IN
if (!isset($_SESSION['user_id'])) {
    header("Location: /MMBPOS/login.php");
    exit;
}


$activeTab = $_GET['tab'] ?? 'dashboard';

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

                    <a class="nav-link <?= $activeTab === 'dashboard' ? 'active' : '' ?>" data-bs-toggle="pill"
                        href="#v-pills-dashboard">Dashboard</a>

                    <a class="nav-link <?= $activeTab === 'product' ? 'active' : '' ?>" data-bs-toggle="pill"
                        href="#v-pills-product">Product Management</a>

                    <a class="nav-link <?= $activeTab === 'inventory' ? 'active' : '' ?>" data-bs-toggle="pill"
                        href="#v-pills-inventory">Inventory</a>

                    <a class="nav-link <?= $activeTab === 'sales' ? 'active' : '' ?>" data-bs-toggle="pill"
                        href="#v-pills-sales">Sales (POS)</a>

                    <a class="nav-link <?= $activeTab === 'reports' ? 'active' : '' ?>" data-bs-toggle="pill"
                        href="#v-pills-reports">Reports</a>

                    <a class="nav-link <?= $activeTab === 'security' ? 'active' : '' ?>" data-bs-toggle="pill"
                        href="#v-pills-security">User Authentication & Security</a>

                    <a class="nav-link <?= $activeTab === 'users' ? 'active' : '' ?>" data-bs-toggle="pill"
                        href="#v-pills-users">User Management</a>

                    <a class="nav-link <?= $activeTab === 'system' ? 'active' : '' ?>" data-bs-toggle="pill"
                        href="#v-pills-system">System Settings</a>
                </div>
            </div>
        </div>

        <div class="tab-content" id="v-pills-tabContent">
            <div class="tab-pane fade <?= $activeTab === 'dashboard' ? 'show active' : '' ?>" id="v-pills-dashboard">
                <?php include __DIR__ . "/../reusablepage/dashboard.php"; ?>
            </div>
            <div class="tab-pane fade <?= $activeTab === 'product' ? 'show active' : '' ?>" id="v-pills-product">
                <?php include __DIR__ . "/../reusablepage/productmanagement.php"; ?>
            </div>
            <div class="tab-pane fade <?= $activeTab === 'inventory' ? 'show active' : '' ?>" id="v-pills-inventory">
                <?php include __DIR__ . "/../reusablepage/inventorymanagement.php"; ?>
            </div>
            <div class="tab-pane fade <?= $activeTab === 'sales' ? 'show active' : '' ?>" id="v-pills-sales">
                <?php include __DIR__ . "/../reusablepage/salespos.php"; ?>
            </div>
            <div class="tab-pane fade <?= $activeTab === 'reports' ? 'show active' : '' ?>" id="v-pills-reports">
                <?php include __DIR__ . "/../reusablepage/reports.php"; ?>
            </div>
            <div class="tab-pane fade <?= $activeTab === 'security' ? 'show active' : '' ?>" id="v-pills-security">
                <?php include __DIR__ . "/../reusablepage/userauthentication.php"; ?>
            </div>
            <div class="tab-pane fade <?= $activeTab === 'users' ? 'show active' : '' ?>" id="v-pills-users">
                <?php include __DIR__ . "/../reusablepage/usermanagement.php"; ?>
            </div>
            <div class="tab-pane fade <?= $activeTab === 'system' ? 'show active' : '' ?>" id="v-pills-system">
                <?php include __DIR__ . "/../reusablepage/systemsettings.php"; ?>
            </div>
        </div>
    </div>
    <?php include __DIR__ . "/../reusablepage/footer.php"; ?>
</body>

</html>