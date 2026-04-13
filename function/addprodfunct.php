<?php

namespace Classes;

require_once "../conn/Database.php";

class ProductManagement
{
    public int $id;
    public string $product_name;
    public string $barcode;
    public int $category_id;
    public int $classification_id;
    public string $unit;
    public float $price;
    public int $quantity;
    public string $expiry_date;

    private $con;
    private string $response = "";

    public function __construct($db)
    {
        $this->con = $db;
    }

    // 🔹 GET POST DATA
    public function getPost()
    {
        if (!empty($_POST)) {
            $this->product_name = $_POST['product_name'];
            $this->barcode = !empty($_POST['barcode']) ? $_POST['barcode'] : null;
            $this->category_id = (int) $_POST['category_id'];
            $this->classification_id = (int) $_POST['classification_id'];
            $this->unit = $_POST['unit'];
            $this->price = (float) $_POST['price'];
            $this->quantity = (int) $_POST['quantity'];
            $this->expiry_date = $_POST['expiry_date'];
        }
    }

    // 🔥 ADD PRODUCT (WITH TRANSACTION)
    public function addProduct()
    {
        if (isset($_POST['addProduct'])) {
            $this->getPost();
            try {
                // START TRANSACTION
                $this->con->beginTransaction();

                // ➕ INSERT PRODUCT
                $stmt = $this->con->prepare("
                    INSERT INTO products 
                    (product_name, barcode, category_id, classification_id, unit, price)
                    VALUES (?, ?, ?, ?, ?, ?)
                ");

                $stmt->execute([
                    $this->product_name,
                    $this->barcode,
                    $this->category_id,
                    $this->classification_id,
                    $this->unit,
                    $this->price
                ]);

                $productId = $this->con->lastInsertId();

                // ➕ INSERT INVENTORY
                $stmt = $this->con->prepare("
                    INSERT INTO inventory (product_id, quantity, expiry_date)
                    VALUES (?, ?, ?)
                ");

                $stmt->execute([
                    $productId,
                    $this->quantity,
                    $this->expiry_date
                ]);

                // ✅ COMMIT
                $this->con->commit();

                $this->response = "Success";
                return true;

            } catch (\Exception $e) {
                // ❌ ROLLBACK
                $this->con->rollBack();
                $this->response = "Transaction failed: " . $e->getMessage();
                return false;
            }
        }
        return false;
    }

    // 🔥 GET ALL PRODUCTS (IMPROVED - GROUPED INVENTORY)
    public function getAllProducts()
    {
        $stmt = $this->con->prepare("
            SELECT 
                p.id,
                p.product_name,
                p.price,
                pc.category_name,
                pcl.classification_name,
                COALESCE(SUM(i.quantity), 0) AS quantity,
                MAX(i.expiry_date) AS expiry_date
            FROM products p
            LEFT JOIN product_categories pc ON p.category_id = pc.id
            LEFT JOIN product_classifications pcl ON p.classification_id = pcl.id
            LEFT JOIN inventory i ON p.id = i.product_id
            GROUP BY p.id
        ");

        $stmt->execute();
        return $stmt->fetchAll();
    }

    // 🔥 DELETE PRODUCT (SIMPLIFIED - CASCADE HANDLES INVENTORY)
    public function deleteProduct($productId)
    {
        if (!$productId) {
            $this->response = "Invalid product ID";
            return false;
        }

        $stmt = $this->con->prepare("DELETE FROM products WHERE id = ?");
        $result = $stmt->execute([$productId]);

        if ($result) {
            $this->response = "Success";
            return true;
        } else {
            $this->response = "Failed to delete product";
            return false;
        }
    }

    // 🔹 GET ALL CATEGORIES
    public function getCategories()
    {
        $stmt = $this->con->prepare("SELECT * FROM product_categories");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // 🔹 GET ALL CLASSIFICATIONS
    public function getClassifications()
    {
        $stmt = $this->con->prepare("SELECT * FROM product_classifications");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // 🔥 UPDATE PRODUCT
    public function updateProduct()
    {
        if (isset($_POST['updateProduct'])) {
            $this->getPost();
            $this->id = (int) $_POST['id'];

            try {
                $this->con->beginTransaction();

                // 🔍 CHECK BARCODE (exclude current product)
                if (!empty($this->barcode)) {
                    $stmt = $this->con->prepare("
                    SELECT COUNT(*) 
                    FROM products 
                    WHERE barcode = ? AND id != ?
                ");
                    $stmt->execute([$this->barcode, $this->id]);

                    if ($stmt->fetchColumn() > 0) {
                        $this->con->rollBack();
                        $this->response = "Barcode already exists";
                        return false;
                    }
                }

                // ✏️ UPDATE PRODUCT
                $stmt = $this->con->prepare("
                UPDATE products 
                SET product_name = ?, barcode = ?, category_id = ?, 
                    classification_id = ?, unit = ?, price = ?
                WHERE id = ?
            ");

                $stmt->execute([
                    $this->product_name,
                    $this->barcode,
                    $this->category_id,
                    $this->classification_id,
                    $this->unit,
                    $this->price,
                    $this->id
                ]);

                // ✏️ UPDATE INVENTORY (simple version)
                $stmt = $this->con->prepare("
                UPDATE inventory 
                SET quantity = ?, expiry_date = ?
                WHERE product_id = ?
            ");

                $stmt->execute([
                    $this->quantity,
                    $this->expiry_date,
                    $this->id
                ]);

                $this->con->commit();

                $this->response = "Updated successfully";
                return true;

            } catch (\Exception $e) {
                $this->con->rollBack();
                $this->response = "Update failed: " . $e->getMessage();
                return false;
            }
        }

        return false;
    }

    // 🔥 GET SINGLE PRODUCT (FOR EDIT)
    public function getProductById($id)
    {
        $stmt = $this->con->prepare("
        SELECT p.*, i.quantity, i.expiry_date
        FROM products p
        LEFT JOIN inventory i ON p.id = i.product_id
        WHERE p.id = ?
    ");
        $stmt->execute([$id]);

        return $stmt->fetch();
    }

    // 🔥 UPDATE STOCK (AUTO HANDLE POST)
    public function updateStock()
    {
        if (isset($_POST['updateStock'])) {

            $id = (int) $_POST['id'];
            $quantity = (int) $_POST['quantity'];
            $expiry = $_POST['expiry_date'];

            try {
                $stmt = $this->con->prepare("
                UPDATE inventory 
                SET quantity = ?, expiry_date = ?
                WHERE product_id = ?
            ");

                if ($stmt->execute([$quantity, $expiry, $id])) {
                    $this->response = "success";
                    return true;
                } else {
                    $this->response = "error";
                    return false;
                }

            } catch (\Exception $e) {
                $this->response = "error: " . $e->getMessage();
                return false;
            }
        }

        return false;
    }

    // 🔥 LOW STOCK ALERT (FULL HTML OUTPUT)
    public function renderLowStockAlert($limit = 50)
    {
        $stmt = $this->con->prepare("
        SELECT 
            p.product_name,
            COALESCE(SUM(i.quantity), 0) AS quantity
        FROM products p
        LEFT JOIN inventory i ON p.id = i.product_id
        GROUP BY p.id
        HAVING quantity <= ? AND quantity > 0
    ");

        $stmt->execute([$limit]);
        $items = $stmt->fetchAll();

        if (empty($items)) {
            return ""; // no alert
        }

        $html = '<div id="lowStockAlert">';
        $html .= '⚠ WARNING: Low stock detected!<br><br>';

        foreach ($items as $item) {
            $html .= '• ' . htmlspecialchars($item['product_name']) .
                ' (' . $item['quantity'] . ' left)<br>';
        }

        $html .= '<br><button onclick="closeAlert()">OK</button>';
        $html .= '</div>';

        return $html;
    }

    public function getResponse()
    {
        return $this->response;
    }
}