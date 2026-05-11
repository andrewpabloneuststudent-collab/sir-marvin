<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: /MMBPOS/login.php");
    exit;
}

if (strtolower($_SESSION['position']) !== 'admin') {
    echo "Access denied";
    exit;
}

$activeTab = $_GET['tab'] ?? 'dashboard';

require_once __DIR__ . "/../conn/database.php";
require_once __DIR__ . "/../conn/connection_links.php";
require_once __DIR__ . "/../function/userregistration.php";

use Classes\UserRegistration;

$user = new UserRegistration($db);

// ✅ SAFE POST HANDLING
$error_msg = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($user->pre_addUser()) {
        header("Location: dashboard.php?tab=users&added=1");
        exit;
    } else {
        $error_msg = $user->getResponse();
    }
}

// ✅ ALERT AFTER REDIRECT
if (isset($_GET['added'])) {
    echo "<script>showNotif('User added successfully!', 'success'); setTimeout(()=>{ window.location.href='dashboard.php?tab=users'; },1500);</script>";
}
if (!empty($error_msg)) {
    echo "<script>showNotif('Error: " . addslashes($error_msg) . "', 'error');</script>";
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>

</head>

<body class="m-0 p-0" style="height: 100vh; overflow: hidden; display: flex; flex-direction: column;">
   <?php include __DIR__ . "/../reusablepage/header.php"; ?>


    <div class="d-flex">

        <!-- SIDEBAR -->
        <div class="offcanvas-lg offcanvas-start" tabindex="-1" id="sidebar" style="--bs-offcanvas-width: 50%;">
            <div class="offcanvas-header d-lg-none">
                <h5>Menu</h5>
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
            </div>

            <div class="offcanvas-body p-0">
                <style>
                    .sidebar-nav { width:200px; min-width:200px; background:#fff; border-right:1px solid #f0f0f0; height: 100%; overflow-y: auto; padding:16px 12px; box-shadow:2px 0 8px rgba(0,0,0,.04); }
                    .sidebar-nav .nav-link { color:#64748b; font-size:.83rem; font-weight:500; padding:9px 12px; border-radius:8px; margin-bottom:2px; display:flex; align-items:center; gap:10px; transition:all .15s; border:none; }
                    .sidebar-nav .nav-link i { width:18px; text-align:center; font-size:.9rem; opacity:.7; }
                    .sidebar-nav .nav-link:hover { background:#fff5f5; color:#c0392b !important; }
                    .sidebar-nav .nav-link:hover i { opacity:1; }
                    .sidebar-nav .nav-link.active { background:linear-gradient(135deg,#c0392b,#e74c3c) !important; color:#fff !important; font-weight:600; box-shadow:0 4px 10px rgba(192,57,43,.3); }
                    .sidebar-nav .nav-link.active i { opacity:1; }
                    .sidebar-section-label { font-size:.63rem; font-weight:700; text-transform:uppercase; letter-spacing:.8px; color:#c5cdd6; padding:12px 12px 6px; }
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

                    <a class="nav-link <?= $activeTab === 'pendingaccount' ? 'active' : '' ?>" data-bs-toggle="pill"
                        href="#v-pills-pendingaccount"><i class="fas fa-user-clock"></i>Pending Account</a>

                    <a class="nav-link <?= $activeTab === 'users' ? 'active' : '' ?>" data-bs-toggle="pill"
                        href="#v-pills-users"><i class="fas fa-users"></i>User Management</a>

                    <a class="nav-link <?= $activeTab === 'system' ? 'active' : '' ?>" data-bs-toggle="pill"
                        href="#v-pills-system"><i class="fas fa-gear"></i>System Settings</a>
                </div>
            </div>
        </div>

        <div class="tab-content flex-grow-1 w-100" id="v-pills-tabContent" style="background-color: #f8fafc; height: 100%; overflow-y: auto; overflow-x: hidden;">

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
            <div class="tab-pane fade px-3 py-3 <?= $activeTab === 'pendingaccount' ? 'show active' : '' ?>"
                id="v-pills-pendingaccount">
                <?php include __DIR__ . "/../reusablepage/pendingaccountadmin.php"; ?>
            </div>
            <div class="tab-pane fade px-3 py-3 <?= $activeTab === 'users' ? 'show active' : '' ?>" id="v-pills-users">
                <?php include __DIR__ . "/../reusablepage/adminaddaccount.php"; ?>
            </div>
            <div class="tab-pane fade px-3 py-3 <?= $activeTab === 'system' ? 'show active' : '' ?>" id="v-pills-system">
                <?php include __DIR__ . "/../reusablepage/systemsettings.php"; ?>
            </div>
        </div>
    </div>
    <?php include __DIR__ . "/../reusablepage/footer.php"; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>