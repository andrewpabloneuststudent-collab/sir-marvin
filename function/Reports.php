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
            'cashiers' => $this->getCashierPerformance(),
            'dailySales' => $this->getDailySales(),
            'yearlySales' => $this->getYearlySales(),
            'discountBreakdown' => $this->getDiscountBreakdown(),
            'transactions' => $this->getAllTransactions()
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

    /* =========================
       DAILY SALES
    ========================= */
    public function getDailySales(): array
    {
        $stmt = $this->db->prepare("
            SELECT DATE(t.created_at) sale_date, 
                   COUNT(*) total_transactions,
                   SUM(t.total_amount) daily_total,
                   AVG(t.total_amount) daily_avg
            FROM transactions t
            GROUP BY DATE(t.created_at)
            ORDER BY t.created_at DESC
            LIMIT 30
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /* =========================
       YEARLY SALES
    ========================= */
    public function getYearlySales(): array
    {
        $stmt = $this->db->prepare("
            SELECT YEAR(t.created_at) sale_year,
                   MONTH(t.created_at) sale_month,
                   COUNT(*) total_transactions,
                   SUM(t.total_amount) monthly_total,
                   AVG(t.total_amount) monthly_avg
            FROM transactions t
            GROUP BY YEAR(t.created_at), MONTH(t.created_at)
            ORDER BY t.created_at DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /* =========================
       DISCOUNT BREAKDOWN (PWD & SENIOR)
    ========================= */
    public function getDiscountBreakdown(): array
    {
        $stmt = $this->db->prepare("
            SELECT d.discount_name,
                   COUNT(DISTINCT t.id) usage_count,
                   SUM((SELECT SUM(subtotal) FROM transaction_items WHERE transaction_id = t.id) - t.total_amount) total_discount_given,
                   AVG((SELECT SUM(subtotal) FROM transaction_items WHERE transaction_id = t.id) - t.total_amount) avg_discount
            FROM transactions t
            JOIN discounts d ON t.discount_id = d.id
            WHERE d.discount_name IN ('PWD', 'Senior Citizen', 'Senior')
            GROUP BY d.discount_name
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /* =========================
       ALL TRANSACTIONS
    ========================= */
    public function getAllTransactions(): array
    {
        $stmt = $this->db->prepare("
            SELECT t.id,
                   t.created_at transaction_date,
                   u.username,
                   d.discount_name,
                   COALESCE(SUM(ti.subtotal), 0) subtotal,
                   COALESCE(SUM(ti.subtotal), 0) - t.total_amount discount_amount,
                   t.total_amount,
                   COUNT(DISTINCT ti.id) items_count
            FROM transactions t
            LEFT JOIN users u ON t.user_id = u.id
            LEFT JOIN discounts d ON t.discount_id = d.id
            LEFT JOIN transaction_items ti ON t.id = ti.transaction_id
            GROUP BY t.id
            ORDER BY t.created_at DESC
            LIMIT 500
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}