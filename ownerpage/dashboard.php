<?php
require_once __DIR__ . "/../function/loginfunction.php"; //yours is loginfunction
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
    <!-- Include CSS/JS libraries here -->
    <?php require_once __DIR__ . "/../conn/connection_links.php"; ?>
</head>

<body class="d-flex flex-column m-0 p-0" style="min-height: 100vh; overflow-x: hidden;">
    <?php include __DIR__ . "/../reusablepage/header.php"; ?>

    <div class="d-flex flex-grow-1">

        <!-- SIDEBAR -->
        <div class="offcanvas-lg offcanvas-start" tabindex="-1" id="sidebar" style="--bs-offcanvas-width: 50%;">
            <div class="offcanvas-header d-lg-none">
                <h5>Menu</h5>
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
            </div>

            <div class="offcanvas-body p-0">
                <style>
                    .sidebar-nav {
                        width: 200px;
                        min-width: 200px;
                        background: #fff;
                        border-right: 1px solid #f0f0f0;
                        min-height: 100vh;
                        padding: 16px 12px;
                        box-shadow: 2px 0 8px rgba(0,0,0,.04);
                    }
                    .sidebar-brand {
                        display: flex;
                        align-items: center;
                        gap: 10px;
                        padding: 10px 8px 20px;
                        border-bottom: 1px solid #f0f0f0;
                        margin-bottom: 16px;
                    }
                    .sidebar-brand-icon {
                        width: 34px; height: 34px;
                        background: linear-gradient(135deg, #c0392b, #e74c3c);
                        border-radius: 10px;
                        display: flex; align-items: center; justify-content: center;
                        color: #fff; font-size: 1rem;
                    }
                    .sidebar-brand-text {
                        font-size: .78rem;
                        font-weight: 800;
                        color: #1a2535;
                        letter-spacing: -.3px;
                        line-height: 1.2;
                    }
                    .sidebar-brand-text small {
                        display: block;
                        font-weight: 400;
                        font-size: .68rem;
                        color: #94a3b8;
                    }
                    .sidebar-nav .nav-link {
                        color: #64748b;
                        font-size: .83rem;
                        font-weight: 500;
                        padding: 9px 12px;
                        border-radius: 8px;
                        margin-bottom: 2px;
                        display: flex;
                        align-items: center;
                        gap: 10px;
                        transition: all .15s;
                        border: none;
                    }
                    .sidebar-nav .nav-link i {
                        width: 18px;
                        text-align: center;
                        font-size: .9rem;
                        opacity: .7;
                    }
                    .sidebar-nav .nav-link:hover {
                        background: #fff5f5;
                        color: #c0392b !important;
                    }
                    .sidebar-nav .nav-link:hover i { opacity: 1; }
                    .sidebar-nav .nav-link.active {
                        background: linear-gradient(135deg, #c0392b, #e74c3c) !important;
                        color: #fff !important;
                        font-weight: 600;
                        box-shadow: 0 4px 10px rgba(192,57,43,.3);
                    }
                    .sidebar-nav .nav-link.active i { opacity: 1; }
                    .sidebar-section-label {
                        font-size: .63rem;
                        font-weight: 700;
                        text-transform: uppercase;
                        letter-spacing: .8px;
                        color: #c5cdd6;
                        padding: 12px 12px 6px;
                    }
                </style>

                <div class="sidebar-nav nav flex-column nav-pills" role="tablist">


                    <a class="nav-link <?= $activeTab === 'dashboard' ? 'active' : '' ?>" data-bs-toggle="pill"
                        href="#v-pills-dashboard"><i class="fas fa-gauge-high"></i>Dashboard</a>

                    <div class="sidebar-section-label">Store</div>

                    <a class="nav-link <?= $activeTab === 'product' ? 'active' : '' ?>" data-bs-toggle="pill"
                        href="#v-pills-product"><i class="fas fa-box"></i>Product Management</a>

                    <a class="nav-link <?= $activeTab === 'inventory' ? 'active' : '' ?>" data-bs-toggle="pill"
                        href="#v-pills-inventory"><i class="fas fa-warehouse"></i>Inventory</a>

                    <a class="nav-link <?= $activeTab === 'sales' ? 'active' : '' ?>" data-bs-toggle="pill"
                        href="#v-pills-sales"><i class="fas fa-cash-register"></i>Sales (POS)</a>

                    <a class="nav-link <?= $activeTab === 'reports' ? 'active' : '' ?>" data-bs-toggle="pill"
                        href="#v-pills-reports"><i class="fas fa-chart-bar"></i>Reports</a>

                    <div class="sidebar-section-label">Admin</div>

                    <a class="nav-link <?= $activeTab === 'security' ? 'active' : '' ?>" data-bs-toggle="pill"
                        href="#v-pills-security"><i class="fas fa-shield-halved"></i>Auth & Security</a>

                    <a class="nav-link <?= $activeTab === 'users' ? 'active' : '' ?>" data-bs-toggle="pill"
                        href="#v-pills-users"><i class="fas fa-users"></i>User Management</a>

                    <a class="nav-link <?= $activeTab === 'system' ? 'active' : '' ?>" data-bs-toggle="pill"
                        href="#v-pills-system"><i class="fas fa-gear"></i>System Settings</a>
                </div>
            </div>
        </div>

        <div class="tab-content flex-grow-1 w-100" id="v-pills-tabContent" style="background-color: #f8fafc; min-height: 100vh; overflow: hidden;">
            <div class="tab-pane fade px-3 py-3 <?= $activeTab === 'dashboard' ? 'show active' : '' ?>" id="v-pills-dashboard">
                <?php include __DIR__ . "/../reusablepage/dashboard.php"; ?>
            </div>
            <div class="tab-pane fade px-3 py-3 <?= $activeTab === 'product' ? 'show active' : '' ?>" id="v-pills-product">
                <?php include __DIR__ . "/../reusablepage/productmanagement.php"; ?>
            </div>
            <div class="tab-pane fade px-3 py-3 <?= $activeTab === 'inventory' ? 'show active' : '' ?>" id="v-pills-inventory">
                <?php include __DIR__ . "/../reusablepage/inventorymanagement.php"; ?>
            </div>
            <div class="tab-pane fade <?= $activeTab === 'sales' ? 'show active' : '' ?>" id="v-pills-sales" style="padding: 0; height: 100%; overflow: hidden;">
                <?php include __DIR__ . "/../reusablepage/salespos.php"; ?>
            </div>
            <div class="tab-pane fade px-3 py-3 <?= $activeTab === 'reports' ? 'show active' : '' ?>" id="v-pills-reports">
                <?php include __DIR__ . "/../reusablepage/reports.php"; ?>
            </div>
            <div class="tab-pane fade px-3 py-3 <?= $activeTab === 'security' ? 'show active' : '' ?>" id="v-pills-security">
                <?php include __DIR__ . "/../reusablepage/userauthentication.php"; ?>
            </div>
            <div class="tab-pane fade px-3 py-3 <?= $activeTab === 'users' ? 'show active' : '' ?>" id="v-pills-users">
                <?php include __DIR__ . "/../reusablepage/usermanagement.php"; ?>
            </div>
            <div class="tab-pane fade px-3 py-3 <?= $activeTab === 'system' ? 'show active' : '' ?>" id="v-pills-system">
                <?php include __DIR__ . "/../reusablepage/systemsettings.php"; ?>
            </div>
        </div>
    </div>
    <?php include __DIR__ . "/../reusablepage/footer.php"; ?>
</body>

</html>