<?php

namespace Classes;

require_once "../conn/Database.php";
require_once "file_upload.php";

class ProductManagement
{
    public int $id;
    public string $product_name;
    public string $barcode;
    public int $category_id;
    public int $classification_id;
    public string $unit;
    public float $net_price;
    public float $total_price;
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
            $this->product_name = $_POST['product_name'] ?? '';
            $this->barcode = $_POST['barcode'] ?? '';
            $this->category_id = (int) ($_POST['category_id'] ?? 0);
            $this->classification_id = (int) ($_POST['classification_id'] ?? 0);
            $this->unit = $_POST['unit'] ?? '';
            $this->net_price = (float) ($_POST['net_price'] ?? 0);
            $this->total_price = (float) ($_POST['total_price'] ?? 0);
            $this->quantity = (int) ($_POST['quantity'] ?? 0);
            $this->expiry_date = $_POST['expiry_date'] ?? '';
        }
    }

    private function handleImageUpload()
    {
        if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] == 0) {
            // Use the fileupload class
            $uploadDir = __DIR__ . '/../img/'; // Save to img folder
            
            // Ensure directory exists
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            $upload = new fileupload($_FILES['product_image'], $uploadDir);
            
            if ($upload->upload()) {
                return $upload->filename; // Return the generated filename
            }
        }
        return '';
    }

    // 🔥 ADD PRODUCT (WITH TRANSACTION)
   public function addProduct()
{
    if (isset($_POST['addProduct'])) {
        $this->getPost();

        try {
            $this->con->beginTransaction();

            // ✅ INSERT THIS BLOCK HERE
            if (empty($this->barcode)) {
                do {
                    $this->barcode = time() . rand(100, 999);

                    $check = $this->con->prepare("SELECT id FROM products WHERE barcode = ?");
                    $check->execute([$this->barcode]);

                } while ($check->fetch());
            } else {
                $check = $this->con->prepare("SELECT id FROM products WHERE barcode = ?");
                $check->execute([$this->barcode]);

                if ($check->fetch()) {
                    $this->response = "Barcode already exists!";
                    return false;
                }
            }

            $imagePath = $this->handleImageUpload();

            // ➕ INSERT PRODUCT
            $stmt = $this->con->prepare("
                INSERT INTO products 
                (product_name, barcode, category_id, classification_id, unit, net_price, total_price, imageproduct)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");

            $stmt->execute([
                $this->product_name,
                $this->barcode,
                $this->category_id,
                $this->classification_id,
                $this->unit,
                $this->net_price,
                $this->total_price,
                $imagePath
            ]);

            $productId = $this->con->lastInsertId();

            // ➕ INVENTORY
            $stmt = $this->con->prepare("
                INSERT INTO inventory (product_id, quantity, expiry_date)
                VALUES (?, ?, ?)
            ");

            $stmt->execute([
                $productId,
                $this->quantity,
                $this->expiry_date
            ]);

            $this->con->commit();

            $this->response = "Success";
            return true;

        } catch (\Exception $e) {
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
                p.net_price,
                p.total_price,
                pc.category_name,
                p.barcode,
                p.unit,
                p.imageproduct,
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
            $oldImage = $_POST['old_image'] ?? '';

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

                $imagePath = $this->handleImageUpload();

                // If new image uploaded, delete old image
                if ($imagePath !== '' && !empty($oldImage)) {
                    $oldImagePath = __DIR__ . '/../img/' . $oldImage;
                    if (file_exists($oldImagePath)) {
                        unlink($oldImagePath);
                    }
                }

                // ✏️ UPDATE PRODUCT
                if ($imagePath !== '') {
                    $stmt = $this->con->prepare("
                    UPDATE products 
                    SET product_name = ?, barcode = ?, category_id = ?, 
                    classification_id = ?, unit = ?, net_price = ?, total_price = ?, imageproduct = ?
                    WHERE id = ?
                ");

                    $stmt->execute([
                        $this->product_name,
                        $this->barcode,
                        $this->category_id,
                        $this->classification_id,
                        $this->unit,
                        $this->net_price,
                        $this->total_price,
                        $imagePath,
                        $this->id
                    ]);
                } else {
                    $stmt = $this->con->prepare("
                    UPDATE products 
                    SET product_name = ?, barcode = ?, category_id = ?, 
                    classification_id = ?, unit = ?, net_price = ?, total_price = ?
                    WHERE id = ?
                ");

                    $stmt->execute([
                        $this->product_name,
                        $this->barcode,
                        $this->category_id,
                        $this->classification_id,
                        $this->unit,
                        $this->net_price,
                        $this->total_price,
                        $this->id
                    ]);
                }

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
        $html .= '<div class="d-flex justify-content-between align-items-center mb-3">';
        $html .= '<h5 class="mb-0 text-warning"><i class="fas fa-exclamation-triangle me-2"></i>Low Stock</h5>';
        $html .= '<button type="button" class="btn-close btn-close-sm" onclick="this.parentElement.parentElement.remove()"></button>';
        $html .= '</div><div class="alert-items-container">';

        foreach ($items as $item) {
            $html .= '<div class="alert-item d-flex justify-content-between mb-2 pb-2 border-bottom">';
            $html .= '<span class="text-truncate pe-2">' . htmlspecialchars($item['product_name']) . '</span>';
            $html .= '<span class="badge bg-warning text-dark">' . $item['quantity'] . ' left</span>';
            $html .= '</div>';
        }

        $html .= '</div>';
        $html .= '</div>';

        return $html;
    }

    public function renderExpiryAlert()
    {
        $products = $this->getAllProducts();
        $items = [];

        $today = new \DateTime();

        foreach ($products as $prod) {
            if (!empty($prod['expiry_date'])) {
                $expDate = new \DateTime($prod['expiry_date']);
                $interval = $today->diff($expDate);

                if ($expDate < $today) {
                    $items[] = [
                        'name' => $prod['product_name'],
                        'status' => 'Expired'
                    ];
                } elseif ($interval->days <= 7 && !$interval->invert) {
                    $items[] = [
                        'name' => $prod['product_name'],
                        'status' => 'Near Expiry'
                    ];
                }
            }
        }

        if (empty($items)) {
            return '';
        }

        $html = '<div id="expiryAlert">';
        $html .= '<div class="d-flex justify-content-between align-items-center mb-3">';
        $html .= '<h5 class="mb-0 text-danger"><i class="fas fa-exclamation-circle me-2"></i>Expiring Soon</h5>';
        $html .= '<button type="button" class="btn-close btn-close-sm" onclick="this.parentElement.parentElement.remove()"></button>';
        $html .= '</div><div class="alert-items-container">';

        foreach ($items as $item) {
            if ($item['status'] === 'Expired') {
                $html .= '<div class="alert-item d-flex justify-content-between mb-2 pb-2 border-bottom">';
                $html .= '<span class="text-truncate pe-2">' . htmlspecialchars($item['name']) . '</span>';
                $html .= '<span class="badge bg-danger">Expired</span></div>';
            } else {
                $html .= '<div class="alert-item d-flex justify-content-between mb-2 pb-2 border-bottom">';
                $html .= '<span class="text-truncate pe-2">' . htmlspecialchars($item['name']) . '</span>';
                $html .= '<span class="badge bg-warning text-dark">Near Expiry</span></div>';
            }
        }

        $html .= '</div>';
        $html .= '</div>';

        return $html;
    }

    public function getExpiryStatus(array $products): array
    {
        $expired = [];
        $near = [];

        $today = new \DateTime();

        foreach ($products as $prod) {
            if (empty($prod['expiry_date']))
                continue;

            $expDate = new \DateTime($prod['expiry_date']);
            $interval = $today->diff($expDate);

            if ($expDate <= $today) {
                $expired[] = $prod;
            } elseif ($interval->days <= 90 && !$interval->invert) {
                $near[] = $prod;
            }
        }

        return [
            'expired' => $expired,
            'near' => $near
        ];
    }

    public function getResponse()
    {
        return $this->response;
    }
}