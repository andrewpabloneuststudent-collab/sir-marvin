<?php
namespace Classes;

use PDO;

class Reports
{
    private $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /* =========================
       GET ALL REPORT DATA
    ========================= */
    public function getAllReports(): array
    {
        return [
            'sales' => $this->getSalesSummary(),
            'topProducts' => $this->getTopProducts(),
            'inventory' => $this->getInventory(),
            'discounts' => $this->getDiscountUsage(),
            'cashiers' => $this->getCashierPerformance()
        ];
    }

    /* =========================
       SALES SUMMARY
    ========================= */
    public function getSalesSummary(): array
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) total_transactions,
                   SUM(total_amount) total_sales,
                   AVG(total_amount) avg_sale
            FROM transactions
        ");
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    }

    /* =========================
       TOP PRODUCTS
    ========================= */
    public function getTopProducts(): array
    {
        $stmt = $this->db->prepare("
            SELECT p.product_name, SUM(ti.quantity) total_sold
            FROM transaction_items ti
            JOIN products p ON ti.product_id = p.id
            GROUP BY p.product_name
            ORDER BY total_sold DESC
            LIMIT 5
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /* =========================
       INVENTORY
    ========================= */
    public function getInventory(): array
    {
        $stmt = $this->db->prepare("
            SELECT p.product_name, i.quantity, i.expiry_date
            FROM inventory i
            JOIN products p ON i.product_id = p.id
            ORDER BY i.expiry_date ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /* =========================
       DISCOUNTS
    ========================= */
    public function getDiscountUsage(): array
    {
        $stmt = $this->db->prepare("
            SELECT d.discount_name, COUNT(t.id) used_count
            FROM transactions t
            JOIN discounts d ON t.discount_id = d.id
            GROUP BY d.discount_name
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /* =========================
       CASHIER PERFORMANCE
    ========================= */
    public function getCashierPerformance(): array
    {
        $stmt = $this->db->prepare("
            SELECT u.username, COUNT(t.id) total_transactions
            FROM transactions t
            JOIN users u ON t.user_id = u.id
            GROUP BY u.username
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}