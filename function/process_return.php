<?php
/**
 * API: Process Product Return & Refund
 * POST body: { original_transaction_id, void_pin, refund_method, reason, items: [{ product_id, qty, price, is_restockable }] }
 * Returns: { success: true, return_id, refund_total } or { success: false, error: string }
 */

error_reporting(0);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

require_once __DIR__ . '/../conn/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit;
}

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Not authenticated. Please log in again.']);
    exit;
}

$body = json_decode(file_get_contents('php://input'), true);

$originalTxId  = (int)($body['original_transaction_id'] ?? 0);
$voidPin       = trim($body['void_pin'] ?? '');
$refundMethod  = trim($body['refund_method'] ?? 'Cash');
$reason        = trim($body['reason'] ?? 'Customer Return');
$items         = $body['items'] ?? [];

if ($originalTxId <= 0) {
    echo json_encode(['success' => false, 'error' => 'Invalid original transaction ID']);
    exit;
}

$userRole = strtolower(trim($_SESSION['position'] ?? ''));
$isLoggedInOwner = ($userRole === 'owner');

if (!$isLoggedInOwner && empty($voidPin)) {
    echo json_encode(['success' => false, 'error' => 'Manager Void PIN is required']);
    exit;
}

if (empty($items)) {
    echo json_encode(['success' => false, 'error' => 'No items selected for return']);
    exit;
}

try {
    $manager = null;

    if ($isLoggedInOwner) {
        // Logged-in Owner session auto-authorizes
        $manager = [
            'id'       => $_SESSION['user_id'],
            'username' => $_SESSION['username'] ?? 'Owner',
            'position' => 'Owner'
        ];
    } else {
        // 1. Verify Manager Void PIN for Admin / Staff
        $stmtPin = $db->prepare("
            SELECT id, username, position 
            FROM users 
            WHERE void_password = ? 
              AND position IN ('Owner', 'Admin') 
            LIMIT 1
        ");
        $stmtPin->execute([$voidPin]);
        $manager = $stmtPin->fetch(PDO::FETCH_ASSOC);

        if (!$manager) {
            echo json_encode(['success' => false, 'error' => 'Invalid Manager Void PIN. Authorization denied.']);
            exit;
        }
    }

    // 2. Verify Original Transaction Exists
    $stmtTx = $db->prepare("SELECT id, total_amount FROM transactions WHERE id = ?");
    $stmtTx->execute([$originalTxId]);
    $originalTx = $stmtTx->fetch(PDO::FETCH_ASSOC);

    if (!$originalTx) {
        echo json_encode(['success' => false, 'error' => 'Original transaction not found']);
        exit;
    }

    $db->beginTransaction();

    // Calculate total refund amount
    $refundTotal = 0;
    foreach ($items as $item) {
        $qty   = max(0, (int)($item['qty'] ?? 0));
        $price = (float)($item['price'] ?? 0);
        if ($qty > 0) {
            $refundTotal += $qty * $price;
        }
    }

    if ($refundTotal <= 0) {
        $db->rollBack();
        echo json_encode(['success' => false, 'error' => 'Return total must be greater than zero']);
        exit;
    }

    // 3. Insert into return_transactions
    $stmtReturn = $db->prepare("
        INSERT INTO return_transactions 
        (original_transaction_id, user_id, refund_amount, reason, refund_method, created_at)
        VALUES (?, ?, ?, ?, ?, NOW())
    ");
    $stmtReturn->execute([
        $originalTxId,
        $_SESSION['user_id'],
        $refundTotal,
        $reason . ' (Authorized by Manager: ' . $manager['username'] . ')',
        $refundMethod
    ]);
    $returnTxId = $db->lastInsertId();

    // 4. Process line items
    foreach ($items as $item) {
        $productId    = (int)($item['product_id'] ?? 0);
        $qty          = max(0, (int)($item['qty'] ?? 0));
        $price        = (float)($item['price'] ?? 0);
        $isRestockable= !empty($item['is_restockable']);

        if ($productId <= 0 || $qty <= 0) continue;

        $subtotal = $qty * $price;

        // Record line item
        $stmtItem = $db->prepare("
            INSERT INTO return_items 
            (return_transaction_id, product_id, quantity, price, subtotal, item_type)
            VALUES (?, ?, ?, ?, ?, 'returned')
        ");
        $stmtItem->execute([$returnTxId, $productId, $qty, $price, $subtotal]);

        if ($isRestockable) {
            // Restock to inventory table
            // Check if product exists in inventory
            $stmtInvCheck = $db->prepare("SELECT id FROM inventory WHERE product_id = ? ORDER BY id ASC LIMIT 1");
            $stmtInvCheck->execute([$productId]);
            $invRow = $stmtInvCheck->fetch(PDO::FETCH_ASSOC);

            if ($invRow) {
                $stmtRestock = $db->prepare("UPDATE inventory SET quantity = quantity + ? WHERE id = ?");
                $stmtRestock->execute([$qty, $invRow['id']]);
            } else {
                $stmtNewInv = $db->prepare("INSERT INTO inventory (product_id, quantity, batch_number) VALUES (?, ?, ?)");
                $stmtNewInv->execute([$productId, $qty, 'RETURN-' . time()]);
            }
        } else {
            // Add to disposal log
            $stmtDisp = $db->prepare("
                INSERT INTO inventory_disposals 
                (product_id, batch_number, quantity, reason, disposed_at)
                VALUES (?, ?, ?, ?, NOW())
            ");
            $stmtDisp->execute([$productId, 'RETURN-' . time(), $qty, 'Damaged/Expired Return: ' . $reason]);
        }
    }

    $db->commit();

    echo json_encode([
        'success'      => true,
        'return_id'    => $returnTxId,
        'refund_total' => $refundTotal,
        'message'      => 'Return processed successfully!'
    ]);

} catch (Exception $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    echo json_encode(['success' => false, 'error' => 'Failed to process return: ' . $e->getMessage()]);
}
