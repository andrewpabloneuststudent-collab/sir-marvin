<?php
class Database
{

    private $host = 'localhost';
    private $db = 'mmbpos';
    private $user = 'root';
    private $pass = '';
    private $charset = 'utf8mb4';
    private $port = '3306';

    private $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];

    private $pdo;
    private $dsn;


    public function initConnection()
    {
        $this->dsn = "mysql:host=$this->host;dbname=$this->db;charset=$this->charset;port=$this->port";

        try {
            $this->pdo = new PDO($this->dsn, $this->user, $this->pass, $this->options);

            // ✅ Set PHP timezone (server-side)
            date_default_timezone_set('Asia/Manila');

            // ✅ Set MySQL timezone (database-side)
            $this->pdo->exec("SET time_zone = '+08:00'");

            // ✅ Auto-Migrate Missing Schema Columns & Tables
            $this->autoMigrate($this->pdo);

        } catch (\PDOException $e) {
            throw new \PDOException($e->getMessage(), (int) $e->getCode());
        }

        return $this->pdo;
    }

    private function autoMigrate(PDO $pdo)
    {
        try {
            $hasCol = function($table, $col) use ($pdo) {
                try {
                    $stmt = $pdo->prepare("SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?");
                    $stmt->execute([$table, $col]);
                    return (int)$stmt->fetchColumn() > 0;
                } catch (\Exception $e) {
                    return false;
                }
            };

            // 1. Ensure PRODUCTS columns exist
            if (!$hasCol('products', 'branded_name')) {
                $pdo->exec("ALTER TABLE products ADD COLUMN branded_name VARCHAR(255) NOT NULL DEFAULT ''");
            }
            if (!$hasCol('products', 'generic_name')) {
                if ($hasCol('products', 'product_name')) {
                    $pdo->exec("ALTER TABLE products ADD COLUMN generic_name VARCHAR(255) NOT NULL DEFAULT ''");
                    $pdo->exec("UPDATE products SET generic_name = product_name WHERE generic_name = '' OR generic_name IS NULL");
                } else {
                    $pdo->exec("ALTER TABLE products ADD COLUMN generic_name VARCHAR(255) NOT NULL DEFAULT ''");
                }
            }
            if (!$hasCol('products', 'strength')) {
                $pdo->exec("ALTER TABLE products ADD COLUMN strength VARCHAR(100) NOT NULL DEFAULT ''");
            }
            if (!$hasCol('products', 'measurement_id')) {
                $pdo->exec("ALTER TABLE products ADD COLUMN measurement_id INT NOT NULL DEFAULT 0");
            }
            if (!$hasCol('products', 'barcode')) {
                $pdo->exec("ALTER TABLE products ADD COLUMN barcode VARCHAR(100) DEFAULT NULL");
            }
            if (!$hasCol('products', 'category_id')) {
                $pdo->exec("ALTER TABLE products ADD COLUMN category_id INT DEFAULT NULL");
            }
            if (!$hasCol('products', 'classification_id')) {
                $pdo->exec("ALTER TABLE products ADD COLUMN classification_id INT DEFAULT NULL");
            }
            if (!$hasCol('products', 'pcs')) {
                $pdo->exec("ALTER TABLE products ADD COLUMN pcs INT DEFAULT 0");
            }
            if (!$hasCol('products', 'net_price')) {
                $pdo->exec("ALTER TABLE products ADD COLUMN net_price DECIMAL(10,2) NOT NULL DEFAULT 0.00");
            }
            if (!$hasCol('products', 'total_price')) {
                $pdo->exec("ALTER TABLE products ADD COLUMN total_price DECIMAL(10,2) NOT NULL DEFAULT 0.00");
            }
            if (!$hasCol('products', 'imageproduct')) {
                $pdo->exec("ALTER TABLE products ADD COLUMN imageproduct VARCHAR(500) NOT NULL DEFAULT ''");
            }
            if (!$hasCol('products', 'is_basic_necessities')) {
                $pdo->exec("ALTER TABLE products ADD COLUMN is_basic_necessities TINYINT(1) NOT NULL DEFAULT 0");
            }

            // 2. Ensure TRANSACTIONS columns exist
            if (!$hasCol('transactions', 'customer_name')) {
                $pdo->exec("ALTER TABLE transactions ADD COLUMN customer_name VARCHAR(100) DEFAULT NULL");
            }
            if (!$hasCol('transactions', 'customer_id')) {
                $pdo->exec("ALTER TABLE transactions ADD COLUMN customer_id VARCHAR(100) DEFAULT NULL");
            }
            if (!$hasCol('transactions', 'customer_type')) {
                $pdo->exec("ALTER TABLE transactions ADD COLUMN customer_type VARCHAR(20) DEFAULT NULL");
            }
            if (!$hasCol('transactions', 'discount_total')) {
                $pdo->exec("ALTER TABLE transactions ADD COLUMN discount_total DECIMAL(10,2) DEFAULT 0.00");
            }
            if (!$hasCol('transactions', 'total_vat_exemption')) {
                $pdo->exec("ALTER TABLE transactions ADD COLUMN total_vat_exemption DECIMAL(10,2) DEFAULT 0.00");
            }

            // 3. Ensure USERS columns exist
            if (!$hasCol('users', 'void_password')) {
                $pdo->exec("ALTER TABLE users ADD COLUMN void_password VARCHAR(255) DEFAULT NULL");
            }
            if (!$hasCol('users', 'failed_attempts')) {
                $pdo->exec("ALTER TABLE users ADD COLUMN failed_attempts INT DEFAULT 0");
            }
            if (!$hasCol('users', 'last_attempt')) {
                $pdo->exec("ALTER TABLE users ADD COLUMN last_attempt TIMESTAMP NULL DEFAULT NULL");
            }

            // 4. Ensure PRODUCT_CATEGORIES columns exist
            if (!$hasCol('product_categories', 'has_vat')) {
                $pdo->exec("ALTER TABLE product_categories ADD COLUMN has_vat TINYINT(1) NOT NULL DEFAULT 0");
            }
            if (!$hasCol('product_categories', 'senior_discount')) {
                $pdo->exec("ALTER TABLE product_categories ADD COLUMN senior_discount TINYINT(1) NOT NULL DEFAULT 0");
            }
            if (!$hasCol('product_categories', 'pwd_discount')) {
                $pdo->exec("ALTER TABLE product_categories ADD COLUMN pwd_discount TINYINT(1) NOT NULL DEFAULT 0");
            }

            // 5. Ensure TABLES exist
            $pdo->exec("CREATE TABLE IF NOT EXISTS senior_customers (
                id INT AUTO_INCREMENT PRIMARY KEY,
                customer_name VARCHAR(255) NOT NULL,
                id_number VARCHAR(100) NOT NULL UNIQUE,
                cashier_id INT NOT NULL,
                verified_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB");

            $pdo->exec("CREATE TABLE IF NOT EXISTS pwd_customers (
                id INT AUTO_INCREMENT PRIMARY KEY,
                customer_name VARCHAR(255) NOT NULL,
                id_number VARCHAR(100) NOT NULL UNIQUE,
                cashier_id INT NOT NULL,
                verified_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB");

            $pdo->exec("CREATE TABLE IF NOT EXISTS override_log (
                id INT AUTO_INCREMENT PRIMARY KEY,
                transaction_id INT DEFAULT NULL,
                product_id INT NOT NULL,
                product_name VARCHAR(255) NOT NULL,
                cashier_id INT NOT NULL,
                cashier_name VARCHAR(255) NOT NULL,
                approver_id INT NOT NULL,
                approver_name VARCHAR(255) NOT NULL,
                original_price DECIMAL(10,2) NOT NULL,
                discounted_price DECIMAL(10,2) NOT NULL,
                discount_amount DECIMAL(10,2) NOT NULL,
                discount_percent DECIMAL(5,2) NOT NULL,
                reason TEXT NOT NULL,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB");

            $pdo->exec("CREATE TABLE IF NOT EXISTS inventory_disposals (
                id INT AUTO_INCREMENT PRIMARY KEY,
                product_id INT NOT NULL,
                batch_number VARCHAR(100) DEFAULT NULL,
                quantity INT NOT NULL DEFAULT 0,
                expiry_date DATE DEFAULT NULL,
                reason VARCHAR(100) NOT NULL DEFAULT 'Expired',
                disposed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB");

            $pdo->exec("CREATE TABLE IF NOT EXISTS unit_measurement (
                unit_id INT AUTO_INCREMENT PRIMARY KEY,
                different_measurement VARCHAR(255) NOT NULL
            ) ENGINE=InnoDB");

            $pdo->exec("CREATE TABLE IF NOT EXISTS discounts (
                id INT AUTO_INCREMENT PRIMARY KEY,
                discount_name VARCHAR(50) DEFAULT NULL,
                discount_rate DECIMAL(5,2) DEFAULT NULL,
                is_vat_exempt TINYINT(1) DEFAULT NULL
            ) ENGINE=InnoDB");

        } catch (\Exception $e) {
            // Skip silently if auto-migration encounters non-critical warnings
        }
    }

    public static function getConnection()
    {
        $instance = new self();
        return $instance->initConnection();
    }
}

$connect = new Database();
$db = $connect->initConnection();