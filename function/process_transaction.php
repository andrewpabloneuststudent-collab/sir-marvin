<?php
/**
 * API: Process POS Transaction
 * POST body: { items: [{id, price, qty}], discount_id, customer_name }
 * Returns: { success, transaction_id, total } or { success: false, error }
 */

// Suppress any output-breaking warnings
error_reporting(0);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

// Both files are siblings in the same function/ folder
require_once __DIR__ . '/../conn/database.php';
require_once __DIR__ . '/workingpos.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit;
}

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Not authenticated. Please log in again.']);
    exit;
}

$body         = json_decode(file_get_contents('php://input'), true);
$items        = $body['items']       ?? [];
$discountId   = $body['discount_id'] ?? null;
$customerName = trim($body['customer_name'] ?? 'Walk-in');

if (empty($items)) {
    echo json_encode(['success' => false, 'error' => 'Cart is empty']);
    exit;
}

// Convert discountId to proper int/null
$discountId = (!empty($discountId) && $discountId != '0') ? (int)$discountId : null;

try {
    $pos = new \Classes\POSManagement($db);
    $result = $pos->processTransaction(
        (int)$_SESSION['user_id'],
        $items,
        $discountId,
        $customerName,
        null
    );
    echo json_encode($result);
} catch (\Throwable $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
