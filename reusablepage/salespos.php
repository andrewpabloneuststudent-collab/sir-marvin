<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/../conn/database.php";
require_once __DIR__ . "/../function/workingpos.php";

use Classes\POSManagement;

$pos = new POSManagement($db);
$pos->handle();

// OPTIONAL ALERT
$response = $pos->getResponse();
?>

<!DOCTYPE html>
<html>

<head>
    <title>MMBPOS PRO</title>
    <link href="../css/pos.css" rel="stylesheet">
</head>

<body>

<?php if (!empty($response)): ?>
<script>alert("<?= $response ?>");</script>
<?php endif; ?>

<form method="POST" id="posForm">

    <!-- HIDDEN INPUTS -->
    <input type="hidden" name="void_index" id="void_index">
    <input type="hidden" name="void_password" id="void_password">
    <input type="hidden" name="action" id="action">

    <!-- BARCODE INPUT -->
    <input type="text" name="barcode" id="barcodeInput" autofocus style="opacity:0; position:absolute;">

    <div class="container-fluid py-3">
        <div class="row g-3">

            <!-- LEFT SIDE -->
            <div class="col-lg-9">

                <div class="card shadow-sm h-100">
                    <div class="card-header bg-dark text-white">
                        Cart
                    </div>

                    <div class="card-body p-0">
                        <table class="table table-striped mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th width="10%">Qty</th>
                                    <th>Item</th>
                                    <th width="20%">Price</th>
                                    <th width="20%">Total</th>
                                    <th width="10%">Action</th>
                                </tr>
                            </thead>

                            <tbody>
                            <?php if (!empty($_SESSION['cart'])): ?>
                                <?php foreach ($_SESSION['cart'] as $index => $item): 
                                    $total = $item['price'] * $item['qty'];
                                ?>
                                <tr>
                                    <td><?= $item['qty'] ?></td>
                                    <td><?= $item['name'] ?></td>
                                    <td><?= number_format($item['price'], 2) ?></td>
                                    <td><?= number_format($total, 2) ?></td>
                                    <td>
                                        <button type="button" 
                                            class="btn btn-danger btn-sm voidBtn"
                                            data-index="<?= $index ?>">
                                            VOID
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            </tbody>

                        </table>
                    </div>
                </div>

            </div>

            <!-- RIGHT SIDE -->
            <div class="col-lg-3">
                <?php include "possummary.php" ?>
            </div>

        </div>
    </div>

</form>

<script src="../js/transactionpos.js"></script>

</body>
</html>