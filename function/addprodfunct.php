<?php

namespace Classes;

require_once "../conn/Database.php";
require_once "file_upload.php";

class ProductManagement
{
    public int $id;
    public string $generic_name;
    public string $branded_name;
    public string $strength;
    public int $unit_measurement;
    public string $barcode;
    public int $category_id;
    public int $pcs;
    public float $net_price;
    public float $total_price;
    public int $quantity;
    public string $expiry_date;
    public string $batch_number;
    public int $is_basic_necessities;

    private $con;
    private string $response = "";

    public function __construct($db)
    {
        $this->con = $db;
    }

    // Helper: check if a column exists in the current database
    private function hasColumn(string $table, string $column): bool
    {
        $sql = "SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?";
        $stmt = $this->con->prepare($sql);
        $stmt->execute([$table, $column]);
        return (int)$stmt->fetchColumn() > 0;
    }

    public function getUnitMeasurements(): array
    {
        try {
            $stmt = $this->con->prepare("SELECT unit_id, different_measurement FROM unit_measurement ORDER BY different_measurement ASC");
            $stmt->execute();

            $units = [];
            while ($row = $stmt->fetch()) {
                $id = (int) ($row['unit_id'] ?? 0);
                $value = trim($row['different_measurement'] ?? '');

                if ($id > 0 && $value !== '') {
                    $units[] = [
                        'id' => $id,
                        'name' => $value,
                    ];
                }
            }

            return $units;
        } catch (\Exception $e) {
            return [];
        }
    }

    // 🔹 GET POST DATA
    public function getPost()
    {
        if (!empty($_POST)) {
            $this->generic_name = $_POST['generic_name'] ?? '';
            $this->branded_name = $_POST['branded_name'] ?? '';
            $this->strength = $_POST['strength'] ?? '';
            $this->unit_measurement = (int) ($_POST['unit_measurement'] ?? 0);
            $this->barcode = $_POST['barcode'] ?? '';
            $this->category_id = (int) ($_POST['category_id'] ?? 0);
            $this->pcs = (int) ($_POST['pcs'] ?? $_POST['unit'] ?? 0);
            $this->net_price = (float) ($_POST['net_price'] ?? 0);
            $this->total_price = (float) ($_POST['total_price'] ?? 0);
            $this->quantity = (int) ($_POST['quantity'] ?? 0);
            $this->expiry_date = $_POST['expiry_date'] ?? '';
            $this->batch_number = trim($_POST['batch_number'] ?? '');
            $this->is_basic_necessities = isset($_POST['is_basic_necessities']) ? 1 : 0;
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
                (generic_name, branded_name, strength, measurement_id, barcode, category_id, pcs, net_price, total_price, imageproduct, is_basic_necessities)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");

            $stmt->execute([
                $this->generic_name,
                $this->branded_name,
                $this->strength,
                $this->unit_measurement,
                $this->barcode,
                $this->category_id,
                $this->pcs,
                $this->net_price,
                $this->total_price,
                $imagePath,
                $this->is_basic_necessities
            ]);

            $productId = $this->con->lastInsertId();

            // Ensure inventory supports batch_number
            if (!$this->hasColumn('inventory', 'batch_number')) {
                $this->con->exec("ALTER TABLE inventory ADD COLUMN batch_number VARCHAR(100) DEFAULT NULL");
            }

            // ➕ INVENTORY
            $stmt = $this->con->prepare("
                INSERT INTO inventory (product_id, quantity, expiry_date, batch_number)
                VALUES (?, ?, ?, ?)
            ");

            $stmt->execute([
                $productId,
                $this->quantity,
                $this->expiry_date,
                $this->batch_number ?: null
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
        $hasBasic = $this->hasColumn('products', 'is_basic_necessities');
        $basicSelect = $hasBasic ? 'p.is_basic_necessities,' : '';

        $hasGeneric  = $this->hasColumn('products', 'generic_name');
        $hasBranded  = $this->hasColumn('products', 'branded_name');
        $hasStrength = $this->hasColumn('products', 'strength');
        $hasBarcode     = $this->hasColumn('products', 'barcode');
        $hasMeasurement = $this->hasColumn('products', 'measurement_id');
        $hasNetPrice    = $this->hasColumn('products', 'net_price');
        $hasTotalPrice  = $this->hasColumn('products', 'total_price');
        $hasPcs         = $this->hasColumn('products', 'pcs');
        $hasCategoryId  = $this->hasColumn('products', 'category_id');
        $hasImage       = $this->hasColumn('products', 'imageproduct');

        $nameFields = "";
        if ($hasGeneric && $hasBranded) {
            $nameFields .= "p.generic_name, p.branded_name, CONCAT(COALESCE(p.branded_name,''), ' ', COALESCE(p.generic_name,'')) AS product_name, ";
        } else if ($hasGeneric) {
            $nameFields .= "p.generic_name, '' AS branded_name, p.generic_name AS product_name, ";
        } else if ($this->hasColumn('products', 'product_name')) {
            $nameFields .= "p.product_name AS generic_name, '' AS branded_name, p.product_name AS product_name, ";
        } else {
            $nameFields .= "'' AS generic_name, '' AS branded_name, '' AS product_name, ";
        }

        $strengthField    = $hasStrength    ? "p.strength," : "'' AS strength,";
        $barcodeField     = $hasBarcode     ? "p.barcode,"  : "'' AS barcode,";
        $measurementField = $hasMeasurement ? "p.measurement_id," : "0 AS measurement_id,";
        $netPriceField    = $hasNetPrice    ? "p.net_price," : "0.00 AS net_price,";
        $totalPriceField  = $hasTotalPrice  ? "p.total_price," : "0.00 AS total_price,";
        $pcsField         = $hasPcs         ? "p.pcs AS pcs," : "0 AS pcs,";
        $categoryIdField  = $hasCategoryId  ? "p.category_id," : "0 AS category_id,";
        $imageField       = $hasImage       ? "p.imageproduct," : "'' AS imageproduct,";
        $categoryNameField= $this->hasColumn('product_categories', 'category_name') ? "COALESCE(pc.category_name, 'N/A') AS category_name," : "'N/A' AS category_name,";

        $orderBy          = $hasGeneric     ? "p.generic_name ASC" : ($this->hasColumn('products', 'product_name') ? "p.product_name ASC" : "p.id ASC");

        $sql = "SELECT 
                p.id,
                {$nameFields}
                {$strengthField}
                {$measurementField}
                {$netPriceField}
                {$totalPriceField}
                {$categoryNameField}
                {$barcodeField}
                {$pcsField}
                {$categoryIdField}
                {$imageField} ";

        $sql .= $basicSelect . " COALESCE(SUM(i.quantity), 0) AS quantity, MIN(i.expiry_date) AS expiry_date
            FROM products p
            LEFT JOIN product_categories pc ON p.category_id = pc.id
            LEFT JOIN inventory i ON p.id = i.product_id
            GROUP BY p.id
            ORDER BY {$orderBy}";

        $stmt = $this->con->prepare($sql);
        $stmt->execute();
        $products = $stmt->fetchAll();

        $measurementNames = [];
        try {
            $measurementStmt = $this->con->prepare("SELECT unit_id, different_measurement FROM unit_measurement");
            $measurementStmt->execute();

            while ($row = $measurementStmt->fetch()) {
                $measurementNames[(int) ($row['unit_id'] ?? 0)] = trim($row['different_measurement'] ?? '');
            }
        } catch (\Exception $e) {
            $measurementNames = [];
        }

        foreach ($products as &$product) {
            $measurementId = (int) ($product['measurement_id'] ?? 0);
            $product['measurement_name'] = $measurementNames[$measurementId] ?? '';
        }

        return $products;
    }

    public function getAllInventoryBatches(): array
    {
        $sql = "
            SELECT 
                i.id,
                i.batch_number,
                i.product_id,
                p.generic_name,
                p.branded_name,
                p.strength,
                p.category_id,
                pc.category_name,
                p.barcode,
                p.net_price,
                i.quantity,
                i.expiry_date
            FROM inventory i
            LEFT JOIN products p ON p.id = i.product_id
            LEFT JOIN product_categories pc ON p.category_id = pc.id
            ORDER BY p.generic_name ASC, i.expiry_date ASC, i.id ASC
        ";

        $stmt = $this->con->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getDisposedBatches(): array
    {
        $sql = "
            SELECT 
                d.id,
                d.batch_number,
                d.product_id,
                p.generic_name,
                p.branded_name,
                p.strength,
                p.category_id,
                pc.category_name,
                p.barcode,
                d.quantity,
                d.expiry_date,
                d.reason,
                d.disposed_at
            FROM inventory_disposals d
            LEFT JOIN products p ON p.id = d.product_id
            LEFT JOIN product_categories pc ON p.category_id = pc.id
            ORDER BY d.disposed_at DESC, p.generic_name ASC, d.id ASC
        ";

        $stmt = $this->con->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // 🔥 DELETE PRODUCT (SIMPLIFIED - CASCADE HANDLES INVENTORY.)
    public function deleteProduct($productId)
    {
        if (!$productId) {
            $this->response = "Invalid product ID";
            return false;
        }

        try {
            $this->con->beginTransaction();

            $stmt = $this->con->prepare("DELETE FROM transaction_items WHERE product_id = ?");
            $stmt->execute([$productId]);

            $stmt = $this->con->prepare("DELETE FROM inventory WHERE product_id = ?");
            $stmt->execute([$productId]);

            $stmt = $this->con->prepare("DELETE FROM products WHERE id = ?");
            $result = $stmt->execute([$productId]);

            $this->con->commit();

            if ($result) {
                $this->response = "Success";
                return true;
            }

            $this->response = "Failed to delete product";
            return false;
        } catch (\Exception $e) {
            $this->con->rollBack();
            $this->response = "Delete failed: " . $e->getMessage();
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
                    SET generic_name = ?, branded_name = ?, strength = ?, measurement_id = ?, barcode = ?, category_id = ?, 
                    pcs = ?, net_price = ?, total_price = ?, imageproduct = ?, is_basic_necessities = ?
                    WHERE id = ?
                ");

                    $stmt->execute([
                        $this->generic_name,
                        $this->branded_name,
                        $this->strength,
                        $this->unit_measurement,
                        $this->barcode,
                        $this->category_id,
                        $this->pcs,
                        $this->net_price,
                        $this->total_price,
                        $imagePath,
                        $this->is_basic_necessities,
                        $this->id
                    ]);
                } else {
                    $stmt = $this->con->prepare("
                    UPDATE products 
                    SET generic_name = ?, branded_name = ?, strength = ?, measurement_id = ?, barcode = ?, category_id = ?, 
                    pcs = ?, net_price = ?, total_price = ?, is_basic_necessities = ?
                    WHERE id = ?
                ");

                    $stmt->execute([
                        $this->generic_name,
                        $this->branded_name,
                        $this->strength,
                        $this->unit_measurement,
                        $this->barcode,
                        $this->category_id,
                        $this->pcs,
                        $this->net_price,
                        $this->total_price,
                        $this->is_basic_necessities,
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
        SELECT p.*, p.pcs AS pcs, i.quantity, i.expiry_date
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

    public function addInventoryBatch()
    {
        if (!isset($_POST['addInventoryBatch'])) {
            return false;
        }

        $productId = (int) ($_POST['product_id'] ?? 0);
        $quantity = (int) ($_POST['quantity'] ?? 0);
        $expiryDate = $_POST['expiry_date'] ?? null;
        $batchNumber = trim($_POST['batch_number'] ?? '');
        $netPrice = isset($_POST['net_price']) && $_POST['net_price'] !== '' ? trim($_POST['net_price']) : null;
        $updatePrice = isset($_POST['update_price']) && ($_POST['update_price'] == '1' || $_POST['update_price'] == 1);

        if ($productId <= 0 || $quantity <= 0) {
            $this->response = "Invalid product or quantity";
            return false;
        }

        try {
            $this->con->beginTransaction();

            if (!$this->hasColumn('inventory', 'batch_number')) {
                $this->con->exec("ALTER TABLE inventory ADD COLUMN batch_number VARCHAR(100) DEFAULT NULL");
            }

            $stmt = $this->con->prepare("INSERT INTO inventory (product_id, quantity, expiry_date, batch_number) VALUES (?, ?, ?, ?)");
            $stmt->execute([$productId, $quantity, $expiryDate, $batchNumber ?: null]);

            // Optionally update product net price if admin requested
            if ($updatePrice && $netPrice !== null && is_numeric($netPrice)) {
                $priceStmt = $this->con->prepare("UPDATE products SET net_price = ? WHERE id = ?");
                $priceStmt->execute([ (float)$netPrice, $productId ]);
            }

            $this->con->commit();
            $this->response = "Stock batch added successfully";
            return true;
        } catch (\Exception $e) {
            $this->con->rollBack();
            $this->response = "Failed to add stock batch: " . $e->getMessage();
            return false;
        }
    }

    // 🔥 LOW STOCK ALERT (FULL HTML OUTPUT)
    public function getLowStockAlertItems($limit = 50)
    {
        $hasGeneric = $this->hasColumn('products', 'generic_name');
        $hasBranded = $this->hasColumn('products', 'branded_name');

        if ($hasGeneric && $hasBranded) {
            $nameSelect = "p.generic_name, p.branded_name, ";
        } else if ($hasGeneric) {
            $nameSelect = "p.generic_name, '' AS branded_name, ";
        } else if ($this->hasColumn('products', 'product_name')) {
            $nameSelect = "p.product_name AS generic_name, '' AS branded_name, ";
        } else {
            $nameSelect = "'' AS generic_name, '' AS branded_name, ";
        }

        $stmt = $this->con->prepare("
            SELECT 
                p.id,
                {$nameSelect}
                COALESCE(SUM(i.quantity), 0) AS quantity
            FROM products p
            LEFT JOIN inventory i ON p.id = i.product_id
            GROUP BY p.id
            HAVING quantity <= ? AND quantity > 0
        ");

        $stmt->execute([$limit]);
        $rows = $stmt->fetchAll();

        foreach ($rows as &$row) {
            $branded = trim($row['branded_name'] ?? '');
            $generic = trim($row['generic_name'] ?? '');
            if ($branded !== '' && $generic !== '') {
                $row['product_name'] = $branded . ' (' . $generic . ')';
            } else if ($generic !== '') {
                $row['product_name'] = $generic;
            } else if ($branded !== '') {
                $row['product_name'] = $branded;
            } else {
                $row['product_name'] = 'Product #' . $row['id'];
            }
        }

        return $rows;
    }

    public function renderLowStockAlert($limit = 50)
    {
        $items = $this->getLowStockAlertItems($limit);

        if (empty($items)) {
            return ""; // no alert
        }

        $html = '<div id="lowStockAlert">';
        $html .= '<div class="d-flex justify-content-between align-items-center mb-3">';
        $html .= '<h5 class="mb-0 text-warning"><i class="fas fa-exclamation-triangle me-2"></i>Low Stock</h5>';
        $html .= '<button type="button" class="btn-close btn-close-sm" onclick="this.parentElement.parentElement.remove()"></button>';
        $html .= '</div><div class="alert-items-container">';

        foreach ($items as $item) {
            $displayName = $item['product_name'];
            $html .= '<div class="alert-item d-flex justify-content-between mb-2 pb-2 border-bottom">';
            $html .= '<span class="text-truncate pe-2">' . htmlspecialchars($displayName) . '</span>';
            $html .= '<span class="badge bg-warning text-dark">' . $item['quantity'] . ' left</span>';
            $html .= '</div>';
        }

        $html .= '</div>';
        $html .= '</div>';

        return $html;
    }

    public function getExpiryAlertItems()
    {
        if (!$this->hasColumn('inventory', 'batch_number')) {
            $this->con->exec("ALTER TABLE inventory ADD COLUMN batch_number VARCHAR(100) DEFAULT NULL");
        }

        $hasGeneric = $this->hasColumn('products', 'generic_name');
        $hasBranded = $this->hasColumn('products', 'branded_name');
        $hasStrength = $this->hasColumn('products', 'strength');

        if ($hasGeneric && $hasBranded) {
            $nameSelect = "p.generic_name, p.branded_name, ";
        } else if ($hasGeneric) {
            $nameSelect = "p.generic_name, '' AS branded_name, ";
        } else if ($this->hasColumn('products', 'product_name')) {
            $nameSelect = "p.product_name AS generic_name, '' AS branded_name, ";
        } else {
            $nameSelect = "'' AS generic_name, '' AS branded_name, ";
        }

        $strengthSelect = $hasStrength ? "p.strength" : "'' AS strength";

        $sql = "
            SELECT
                i.id,
                i.product_id,
                i.expiry_date,
                i.batch_number,
                {$nameSelect}
                {$strengthSelect}
            FROM inventory i
            LEFT JOIN products p ON p.id = i.product_id
            WHERE i.expiry_date IS NOT NULL
              AND TRIM(i.expiry_date) <> ''
            ORDER BY i.expiry_date ASC, i.id ASC
        ";

        $stmt = $this->con->prepare($sql);
        $stmt->execute();
        $batches = $stmt->fetchAll();

        $items = [];
        $today = new \DateTime('today');

        foreach ($batches as $batch) {
            $expiryDate = trim((string) ($batch['expiry_date'] ?? ''));
            if ($expiryDate === '') {
                continue;
            }

            try {
                $expDate = new \DateTime($expiryDate);
                $expDate->setTime(0, 0, 0);
                $today->setTime(0, 0, 0);
            } catch (\Exception $e) {
                continue;
            }

            $interval = $today->diff($expDate);
            $daysLeft = (int) $interval->days;

            $brandedName = trim($batch['branded_name'] ?? '');
            $genericName = trim($batch['generic_name'] ?? '');
            $strength = trim($batch['strength'] ?? '');
            $batchNumber = trim((string) ($batch['batch_number'] ?? ''));

            if ($brandedName !== '' && $genericName !== '') {
                $displayName = $brandedName . ' - ' . $genericName . ' (' . $strength . ')';
            } elseif ($brandedName !== '') {
                $displayName = $brandedName . ' (' . $strength . ')';
            } elseif ($genericName !== '') {
                $displayName = $genericName . ' (' . $strength . ')';
            } else {
                $displayName = 'Unnamed Product';
            }

            if ($batchNumber !== '') {
                $displayName .= ' (Batch ' . $batchNumber . ')';
            }

            if ($expDate <= $today) {
                $items[] = [
                    'name' => $displayName,
                    'status' => 'Expired',
                    'days_left' => 0,
                    'expiry_date' => $expiryDate
                ];
            } elseif ($daysLeft <= 60 && !$interval->invert) {
                $items[] = [
                    'name' => $displayName,
                    'status' => 'Near Expiry',
                    'days_left' => $daysLeft,
                    'expiry_date' => $expiryDate
                ];
            }
        }

        return $items;
    }

    public function renderExpiryAlert()
    {
        $items = $this->getExpiryAlertItems();

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