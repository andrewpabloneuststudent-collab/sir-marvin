<?php

class Product {

    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // ✅ GET PRODUCTS
    public function getProducts() {
        $sql = "SELECT p.*, pc.is_discountable, pc.is_vatable
                FROM products p
                JOIN product_classifications pc 
                ON p.classification_id = pc.id";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


}