<?php

class Product {

    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    private function ensureDisposalTable() {
        $sql = <<<SQL
CREATE TABLE IF NOT EXISTS inventory_disposals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    batch_number VARCHAR(100) DEFAULT NULL,
    quantity INT NOT NULL DEFAULT 0,
    expiry_date DATE DEFAULT NULL,
    reason VARCHAR(100) NOT NULL DEFAULT 'Expired',
    disposed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_inventory_disposals_product (product_id),
    INDEX idx_inventory_disposals_expiry (expiry_date)
) ENGINE=InnoDB
SQL;

        $this->conn->exec($sql);
    }

    public function syncExpiredInventoryToDisposal() {
        $this->ensureDisposalTable();

        $this->conn->beginTransaction();

        try {
            $expiredBatches = $this->conn->prepare("SELECT id, product_id, batch_number, quantity, expiry_date FROM inventory WHERE expiry_date IS NOT NULL AND quantity > 0 AND (TRIM(expiry_date) = '' OR expiry_date = '0000-00-00' OR STR_TO_DATE(expiry_date, '%Y-%m-%d') IS NULL OR STR_TO_DATE(expiry_date, '%Y-%m-%d') < CURDATE()) ORDER BY product_id, id");
            $expiredBatches->execute();

            while ($batch = $expiredBatches->fetch(PDO::FETCH_ASSOC)) {
                $productId = (int)($batch['product_id'] ?? 0);

                $insert = $this->conn->prepare("INSERT INTO inventory_disposals (product_id, batch_number, quantity, expiry_date, reason) VALUES (?, ?, ?, ?, 'Expired')");
                $insert->execute([
                    $productId,
                    $batch['batch_number'] ?? null,
                    (int)($batch['quantity'] ?? 0),
                    $batch['expiry_date'] ?? null
                ]);

                $delete = $this->conn->prepare("DELETE FROM inventory WHERE id = ?");
                $delete->execute([(int)($batch['id'] ?? 0)]);
            }

            $this->conn->commit();
        } catch (Exception $e) {
            $this->conn->rollBack();
            throw $e;
        }
    }

    // ✅ GET PRODUCTS with category name, stock, barcode and category-level discount flags
    public function getProducts() {
        $this->syncExpiredInventoryToDisposal();

        $sql = "SELECT p.*, 
                    pc.category_name,
                    pc.has_vat, pc.senior_discount, pc.pwd_discount,
                    um.different_measurement AS measurement_name,
                    COALESCE(SUM(CASE WHEN i.expiry_date IS NULL OR i.expiry_date >= CURDATE() THEN i.quantity ELSE 0 END), 0) AS stock,
                    MIN(CASE WHEN i.expiry_date IS NOT NULL AND i.expiry_date >= CURDATE() THEN i.expiry_date END) AS earliest_expiry_date,
                    MAX(CASE WHEN i.expiry_date IS NOT NULL AND i.expiry_date < CURDATE() THEN 1 ELSE 0 END) AS has_expired_batches
                FROM products p
                LEFT JOIN product_categories pc ON p.category_id = pc.id
                LEFT JOIN unit_measurement um ON p.measurement_id = um.unit_id
                LEFT JOIN inventory i ON p.id = i.product_id
                GROUP BY p.id
                ORDER BY p.id ASC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($products as &$row) {
            $stock = (int)($row['stock'] ?? 0);
            $hasExpiredBatches = (int)($row['has_expired_batches'] ?? 0);
            $row['is_expired'] = ($hasExpiredBatches === 1 && $stock <= 0) ? 1 : 0;

            if (empty($row['product_name'])) {
                $branded = trim($row['branded_name'] ?? '');
                $generic = trim($row['generic_name'] ?? '');
                if ($branded !== '' && $generic !== '') {
                    $row['product_name'] = $branded . ' (' . $generic . ')';
                } else {
                    $row['product_name'] = $generic ?: ($branded ?: ('Product #' . $row['id']));
                }
            }
        }

        return $products;
    }

    // ✅ GET ALL CATEGORIES for filter buttons
    public function getCategories() {
        $sql = "SELECT * FROM product_categories ORDER BY category_name ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ✅ GET DISCOUNTS
    public function getDiscounts() {
        $sql = "SELECT * FROM discounts ORDER BY id ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function calculateSpecialDiscount(array $cartItems, ?string $customerType, string $discountRule = 'regular', ?string $customerId = null, float $weekDiscountTotal = 0.0, float $weekEligibleSubtotal = 0.0): array {
        $customerType = strtolower(trim((string)($customerType ?? '')));
        if (!in_array($customerType, ['senior', 'pwd'], true)) {
            return [
                'discount_total' => 0.0,
                'eligible_subtotal' => 0.0,
                'rate' => 0.0,
                'remaining_discount_cap' => 125.0,
                'remaining_purchase_cap' => 2500.0,
                'eligible_item_count' => 0,
            ];
        }

        $eligibleSubtotal = 0.0;
        $eligibleItemCount = 0;

        foreach ($cartItems as $item) {
            $isEligible = !empty($item['eligible_for_discount']) || !empty($item['senior']) || !empty($item['pwd']);
            if ($isEligible) {
                $qty = max(0, (int)($item['qty'] ?? 0));
                $price = (float)($item['price'] ?? 0);
                if ($qty > 0 && $price > 0) {
                    $eligibleSubtotal += $price * $qty;
                    $eligibleItemCount++;
                }
            }
        }

        $rate = ($discountRule === 'statutory') ? 0.20 : 0.05;
        $remainingDiscountCap = max(0.0, 125.0 - (float)$weekDiscountTotal);
        $remainingPurchaseCap = max(0.0, 2500.0 - (float)$weekEligibleSubtotal);
        $discountableSubtotal = min($eligibleSubtotal, $remainingPurchaseCap);
        $discountTotal = round(min($discountableSubtotal * $rate, $remainingDiscountCap), 2);

        return [
            'discount_total' => $discountTotal,
            'eligible_subtotal' => $discountableSubtotal,
            'rate' => $rate,
            'remaining_discount_cap' => $remainingDiscountCap,
            'remaining_purchase_cap' => $remainingPurchaseCap,
            'eligible_item_count' => $eligibleItemCount,
        ];
    }

    // ✅ PROCESS TRANSACTION
    public function processTransaction($userId, $cartItems, $discountId, $customerName, $customerId, $discountTotal = 0, $totalVatExemption = 0, $customerType = null, $discountRule = 'regular') {
        try {
            $this->conn->beginTransaction();

            // Calculate totals
            $totalAmount = 0;
            foreach ($cartItems as $item) {
                $totalAmount += $item['price'] * $item['qty']; // qty here is 'packs'
            }

            $appliedDiscount = (float)$discountTotal;
            if (in_array(strtolower(trim((string)$customerType)), ['senior', 'pwd'], true)) {
                $weekDiscountStmt = $this->conn->prepare(
                    "SELECT COALESCE(SUM(discount_total), 0) AS week_discount_total
                     FROM transactions
                     WHERE customer_type = ? AND customer_id = ? AND YEARWEEK(created_at, 1) = YEARWEEK(CURDATE(), 1)"
                );
                $weekDiscountStmt->execute([strtolower(trim((string)$customerType)), $customerId]);
                $weekDiscountRow = $weekDiscountStmt->fetch(PDO::FETCH_ASSOC);
                $weekDiscountTotal = (float)($weekDiscountRow['week_discount_total'] ?? 0);

                $weekEligibleStmt = $this->conn->prepare(
                    "SELECT COALESCE(SUM(total_amount), 0) AS week_eligible_subtotal
                     FROM transactions
                     WHERE customer_type = ? AND customer_id = ? AND YEARWEEK(created_at, 1) = YEARWEEK(CURDATE(), 1)"
                );
                $weekEligibleStmt->execute([strtolower(trim((string)$customerType)), $customerId]);
                $weekEligibleRow = $weekEligibleStmt->fetch(PDO::FETCH_ASSOC);
                $weekEligibleSubtotal = (float)($weekEligibleRow['week_eligible_subtotal'] ?? 0);

                $discountDetails = self::calculateSpecialDiscount($cartItems, $customerType, $discountRule, $customerId, $weekDiscountTotal, $weekEligibleSubtotal);
                $appliedDiscount = (float)$discountDetails['discount_total'];
            }

            // Insert transaction with discount_total, total_vat_exemption, and customer_type
            $stmt = $this->conn->prepare("INSERT INTO transactions (user_id, discount_id, customer_name, customer_id, total_amount, discount_total, total_vat_exemption, customer_type) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$userId, $discountId ?: null, $customerName ?: null, $customerId ?: null, $totalAmount, $appliedDiscount, $totalVatExemption, $customerType]);
            $transactionId = $this->conn->lastInsertId();

            // Insert transaction items and deduct inventory
            foreach ($cartItems as $item) {
                $subtotal = $item['price'] * $item['qty'];
                $actualQtyToDeduct = $item['qty'] * ($item['pcs'] ?? 1); // Use pcs for actual deduction
                $stmt = $this->conn->prepare("INSERT INTO transaction_items (transaction_id, product_id, quantity, price, subtotal) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$transactionId, $item['id'], $item['qty'], $item['price'], $subtotal]);

                // Deduct inventory (use actual calculated quantity)
                $stmt = $this->conn->prepare("UPDATE inventory SET quantity = quantity - ? WHERE product_id = ? AND quantity >= ?");
                $stmt->execute([$actualQtyToDeduct, $item['id'], $actualQtyToDeduct]);
            }

            $this->conn->commit();
            return ['success' => true, 'transaction_id' => $transactionId, 'total' => $totalAmount];
        } catch (Exception $e) {
            $this->conn->rollBack();
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}

// ✅ API ENDPOINT: Get products for inventory refresh
if (isset($_GET['action']) && $_GET['action'] === 'getProducts') {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    require_once __DIR__ . '/../conn/database.php';
    
    header('Content-Type: application/json');
    
    try {
        $product = new Product($db);
        $products = $product->getProducts();
        echo json_encode($products);
    } catch (Exception $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit;
}