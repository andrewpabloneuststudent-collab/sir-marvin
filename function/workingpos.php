<?php

namespace Classes;

class POSManagement
{
    private $con;
    private string $response = "";

    public function __construct($db)
    {
        $this->con = $db;
    }

    public function getResponse(): string
    {
        return $this->response;
    }

    public function handle(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;

        $action = $_POST['action'] ?? '';

        if ($action === 'void_item') {
            $this->voidItem();
        } elseif (!empty($_POST['barcode'])) {
            $this->addProduct($_POST['barcode']);
        }
    }

    // ==========================
    // VOID ITEM
    // ==========================
    private function voidItem(): void
    {
        $index = $_POST['void_index'] ?? '';
        $password = trim($_POST['void_password'] ?? '');

        if ($index === '' || $password === '') {
            $this->response = "Invalid request";
            return;
        }

        $stmt = $this->con->prepare("
            SELECT position FROM users 
            WHERE void_password = ?
            AND position IN ('Owner','Admin')
        ");
        $stmt->execute([$password]);
        $user = $stmt->fetch();

        if (!$user) {
            $this->response = "Invalid VOID password";
            return;
        }

        if (isset($_SESSION['cart'][$index])) {
            unset($_SESSION['cart'][$index]);
            $_SESSION['cart'] = array_values($_SESSION['cart']);
        }

        $this->response = "Item voided by " . $user['position'];
    }

    // ==========================
    // ADD PRODUCT
    // ==========================
    private function addProduct(string $barcode): void
    {
        $barcode = trim($barcode);

        $stmt = $this->con->prepare("
            SELECT id, product_name, total_price 
            FROM products WHERE barcode = ?
        ");
        $stmt->execute([$barcode]);
        $product = $stmt->fetch();

        if (!$product) {
            $this->response = "Product not found";
            return;
        }

        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }

        foreach ($_SESSION['cart'] as &$item) {
            if ($item['id'] == $product['id']) {
                $item['qty']++;
                return;
            }
        }

        $_SESSION['cart'][] = [
            'id' => $product['id'],
            'name' => $product['product_name'],
            'price' => $product['total_price'],
            'qty' => 1
        ];
    }
}