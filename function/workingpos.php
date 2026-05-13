<?php

class Product {

    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // ✅ GET PRODUCTS with category name, stock, barcode and category-level discount flags
    public function getProducts() {
        $sql = "SELECT p.*, 
                    pc.category_name,
                    pc.has_vat, pc.senior_discount, pc.pwd_discount,
                    pcl.classification_name,
                    COALESCE(SUM(i.quantity), 0) AS stock,
                    MIN(i.expiry_date) AS earliest_expiry_date
                FROM products p
                LEFT JOIN product_classifications pcl ON p.classification_id = pcl.id
                LEFT JOIN product_categories pc ON p.category_id = pc.id
                LEFT JOIN inventory i ON p.id = i.product_id
                GROUP BY p.id
                ORDER BY p.product_name ASC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
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

    // ✅ PROCESS TRANSACTION
    public function processTransaction($userId, $cartItems, $discountId, $customerName, $customerId, $discountTotal = 0, $totalVatExemption = 0, $customerType = null) {
        try {
            $this->conn->beginTransaction();

            // Calculate totals
            $totalAmount = 0;
            foreach ($cartItems as $item) {
                $totalAmount += $item['price'] * $item['qty'];
            }

            // Insert transaction with discount_total, total_vat_exemption, and customer_type
            $stmt = $this->conn->prepare("INSERT INTO transactions (user_id, discount_id, customer_name, customer_id, total_amount, discount_total, total_vat_exemption, customer_type) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$userId, $discountId ?: null, $customerName ?: null, $customerId ?: null, $totalAmount, $discountTotal, $totalVatExemption, $customerType]);
            $transactionId = $this->conn->lastInsertId();

            // Insert transaction items
            foreach ($cartItems as $item) {
                $subtotal = $item['price'] * $item['qty'];
                $stmt = $this->conn->prepare("INSERT INTO transaction_items (transaction_id, product_id, quantity, price, subtotal) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$transactionId, $item['id'], $item['qty'], $item['price'], $subtotal]);

                // Deduct inventory
                $stmt = $this->conn->prepare("UPDATE inventory SET quantity = quantity - ? WHERE product_id = ? AND quantity >= ?");
                $stmt->execute([$item['qty'], $item['id'], $item['qty']]);
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