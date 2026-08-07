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
            'transactions' => $this->getAllTransactions(),
            'returns' => $this->getReturnsReport(),
            'totalDiscounts' => $this->getTotalDiscounts(),
            'totalVatExemption' => $this->getTotalVatExemption(),
            'totalRefunds' => $this->getTotalRefunds(),
            'realRevenueToday' => $this->getRealRevenueToday(),
            'realRevenueMonth' => $this->getRealRevenueMonth(),
            'realRevenueYear' => $this->getRealRevenueYear(),
            'totalSalesYear' => $this->getTotalSalesYear()
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
            SELECT p.generic_name, SUM(ti.quantity) total_sold
            FROM transaction_items ti
            JOIN products p ON ti.product_id = p.id
            GROUP BY p.generic_name
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
            SELECT p.generic_name, i.quantity, i.expiry_date
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
                   SUM(ti.price * ti.quantity) gross_revenue,
                   SUM(p.net_price * ti.quantity) total_cost,
                   SUM(ti.price * ti.quantity) - SUM(p.net_price * ti.quantity) profit,
                   SUM(t.total_amount) net_revenue,
                   AVG(t.total_amount) monthly_avg
            FROM transactions t
            LEFT JOIN transaction_items ti ON t.id = ti.transaction_id
            LEFT JOIN products p ON ti.product_id = p.id
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
                   SUM(t.discount_total) total_discount_given,
                   AVG(t.discount_total) avg_discount,
                   SUM(t.total_vat_exemption) total_vat_exemption,
                   AVG(t.total_vat_exemption) avg_vat_exemption
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
                   t.customer_name,
                   t.customer_id,
                   t.customer_type,
                   CASE 
                       WHEN t.customer_type = 'senior' THEN (SELECT id_number FROM senior_customers WHERE id = t.customer_id LIMIT 1)
                       WHEN t.customer_type = 'pwd' THEN (SELECT id_number FROM pwd_customers WHERE id = t.customer_id LIMIT 1)
                       ELSE NULL
                   END as govt_id_number,
                   d.discount_name,
                   COALESCE(SUM(ti.subtotal), 0) subtotal,
                   COALESCE(SUM(ti.subtotal), 0) - t.total_amount discount_amount,
                   t.discount_total,
                   t.total_vat_exemption,
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

    /* =========================
       TOTAL DISCOUNTS
    ========================= */
    public function getTotalDiscounts(): float
    {
        $stmt = $this->db->prepare("SELECT SUM(discount_total) as total FROM transactions");
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (float)($result['total'] ?? 0);
    }

    /* =========================
       TOTAL VAT EXEMPTION
    ========================= */
    public function getTotalVatExemption(): float
    {
        $stmt = $this->db->prepare("SELECT SUM(total_vat_exemption) as total FROM transactions");
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (float)($result['total'] ?? 0);
    }

    /* =========================
       REAL REVENUE TODAY
    ========================= */
    public function getRealRevenueToday(): float
    {
        $stmt = $this->db->prepare("
            SELECT 
                SUM(ti.price * ti.quantity) as total_selling,
                SUM(p.net_price * ti.quantity) as total_cost
            FROM transactions t
            JOIN transaction_items ti ON t.id = ti.transaction_id
            JOIN products p ON ti.product_id = p.id
            WHERE DATE(t.created_at) = CURDATE()
        ");
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $selling = (float)($result['total_selling'] ?? 0);
        $cost = (float)($result['total_cost'] ?? 0);
        return $selling - $cost;
    }

    /* =========================
       REAL REVENUE THIS MONTH
    ========================= */
    public function getRealRevenueMonth(): float
    {
        $stmt = $this->db->prepare("
            SELECT 
                SUM(ti.price * ti.quantity) as total_selling,
                SUM(p.net_price * ti.quantity) as total_cost
            FROM transactions t
            JOIN transaction_items ti ON t.id = ti.transaction_id
            JOIN products p ON ti.product_id = p.id
            WHERE MONTH(t.created_at) = MONTH(CURDATE()) 
            AND YEAR(t.created_at) = YEAR(CURDATE())
        ");
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $selling = (float)($result['total_selling'] ?? 0);
        $cost = (float)($result['total_cost'] ?? 0);
        return $selling - $cost;
    }

    /* =========================
       REAL REVENUE THIS YEAR
    ========================= */
    public function getRealRevenueYear(): float
    {
        $stmt = $this->db->prepare("
            SELECT 
                SUM(ti.price * ti.quantity) as total_selling,
                SUM(p.net_price * ti.quantity) as total_cost
            FROM transactions t
            JOIN transaction_items ti ON t.id = ti.transaction_id
            JOIN products p ON ti.product_id = p.id
            WHERE YEAR(t.created_at) = YEAR(CURDATE())
        ");
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $selling = (float)($result['total_selling'] ?? 0);
        $cost = (float)($result['total_cost'] ?? 0);
        return $selling - $cost;
    }

    /* =========================
       TOTAL SALES THIS YEAR
    ========================= */
    public function getTotalSalesYear(): float
    {
        $stmt = $this->db->prepare("SELECT SUM(total_amount) as total FROM transactions WHERE YEAR(created_at) = YEAR(CURDATE())");
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (float)($result['total'] ?? 0);
    }

    /* =========================
       RETURNS & REFUNDS REPORT
    ========================= */
    public function getReturnsReport(): array
    {
        try {
            $stmt = $this->db->prepare("
                SELECT rt.id AS return_id,
                       rt.original_transaction_id,
                       rt.refund_amount,
                       rt.reason,
                       rt.refund_method,
                       rt.created_at,
                       u.username AS processed_by
                FROM return_transactions rt
                LEFT JOIN users u ON rt.user_id = u.id
                ORDER BY rt.created_at DESC
            ");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (\Exception $e) {
            return [];
        }
    }

    public function getTotalRefunds(): float
    {
        try {
            $stmt = $this->db->prepare("SELECT COALESCE(SUM(refund_amount), 0) AS total FROM return_transactions");
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return (float)($row['total'] ?? 0);
        } catch (\Exception $e) {
            return 0.0;
        }
    }
}