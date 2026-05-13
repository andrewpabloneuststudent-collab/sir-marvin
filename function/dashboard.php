<?php
// UPDATE_ID: 11:01:45
namespace Classes;

class DashboardManager
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function getTotalSalesToday()
    {
        $sql = "SELECT SUM(total_amount) as total FROM transactions WHERE DATE(created_at) = CURDATE()";
        $stmt = $this->db->query($sql);
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    }

    public function getTotalSalesMonth()
    {
        $sql = "SELECT SUM(total_amount) as total FROM transactions WHERE MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())";
        $stmt = $this->db->query($sql);
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    }

    public function getTotalSalesYear()
    {
        $sql = "SELECT SUM(total_amount) as total FROM transactions WHERE YEAR(created_at) = YEAR(CURDATE())";
        $stmt = $this->db->query($sql);
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    }

    public function getTransactionCountToday()
    {
        $sql = "SELECT COUNT(*) as total FROM transactions WHERE DATE(created_at) = CURDATE()";
        $stmt = $this->db->query($sql);
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    }

    public function getTotalProducts()
    {
        $sql = "SELECT COUNT(*) as total FROM products";
        $stmt = $this->db->query($sql);
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    }

    public function getLowStockAlerts()
    {
        // Join with inventory to get actual stock and reorder level
        $sql = "SELECT p.product_name as name, i.quantity as stock_quantity, 15 as reorder_level, pc.category_name 
                FROM products p 
                JOIN inventory i ON p.id = i.product_id 
                LEFT JOIN product_categories pc ON p.category_id = pc.id 
                WHERE i.quantity <= 15 
                ORDER BY i.quantity ASC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    public function getExpiringProducts()
    {
        // Join with inventory to get expiry_date
        $sql = "SELECT p.product_name as name, i.expiry_date, pc.category_name 
                FROM products p 
                JOIN inventory i ON p.id = i.product_id 
                LEFT JOIN product_categories pc ON p.category_id = pc.id 
                WHERE i.expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY) 
                ORDER BY i.expiry_date ASC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    public function getRecentTransactions($limit = 5)
    {
        $sql = "SELECT t.*, u.username 
                FROM transactions t 
                LEFT JOIN users u ON t.user_id = u.id 
                ORDER BY t.created_at DESC 
                LIMIT :limit";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', (int)$limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getTopSellingProducts($limit = 5)
    {
        $sql = "SELECT p.product_name as name, SUM(ti.quantity) as total_sold, ti.price 
                FROM transaction_items ti 
                JOIN products p ON ti.product_id = p.id 
                GROUP BY ti.product_id 
                ORDER BY total_sold DESC 
                LIMIT :limit";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', (int)$limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getMonthlySalesTrend()
    {
        $salesTrend = array_fill(1, 12, 0); // Fill with 0s for Jan-Dec
        $sql = "SELECT MONTH(created_at) as month, SUM(total_amount) as total 
                FROM transactions 
                WHERE YEAR(created_at) = YEAR(CURDATE()) 
                GROUP BY MONTH(created_at)";
        $stmt = $this->db->query($sql);
        while ($row = $stmt->fetch()) {
            $salesTrend[(int)$row['month']] = (float)$row['total'];
        }
        return array_values($salesTrend); // Return as index 0-11 for JS
    }

    public function getTotalDiscountToday()
    {
        $sql = "SELECT SUM(discount_total) as total FROM transactions WHERE DATE(created_at) = CURDATE()";
        $stmt = $this->db->query($sql);
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    }

    public function getTotalDiscountMonth()
    {
        $sql = "SELECT SUM(discount_total) as total FROM transactions WHERE MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())";
        $stmt = $this->db->query($sql);
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    }

    public function getTotalVatExemptionToday()
    {
        $sql = "SELECT SUM(total_vat_exemption) as total FROM transactions WHERE DATE(created_at) = CURDATE()";
        $stmt = $this->db->query($sql);
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    }

    public function getTotalVatExemptionMonth()
    {
        $sql = "SELECT SUM(total_vat_exemption) as total FROM transactions WHERE MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())";
        $stmt = $this->db->query($sql);
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    }

    public function getRealRevenueToday()
    {
        // Real Revenue = Selling Price - Cost of Goods Sold
        $sql = "SELECT 
                    SUM(ti.price * ti.quantity) as total_selling,
                    SUM(p.net_price * ti.quantity) as total_cost
                FROM transactions t
                JOIN transaction_items ti ON t.id = ti.transaction_id
                JOIN products p ON ti.product_id = p.id
                WHERE DATE(t.created_at) = CURDATE()";
        $stmt = $this->db->query($sql);
        $result = $stmt->fetch();
        $selling = (float)($result['total_selling'] ?? 0);
        $cost = (float)($result['total_cost'] ?? 0);
        return $selling - $cost;
    }

    public function getRealRevenueMonth()
    {
        // Real Revenue = Selling Price - Cost of Goods Sold
        $sql = "SELECT 
                    SUM(ti.price * ti.quantity) as total_selling,
                    SUM(p.net_price * ti.quantity) as total_cost
                FROM transactions t
                JOIN transaction_items ti ON t.id = ti.transaction_id
                JOIN products p ON ti.product_id = p.id
                WHERE MONTH(t.created_at) = MONTH(CURDATE()) 
                AND YEAR(t.created_at) = YEAR(CURDATE())";
        $stmt = $this->db->query($sql);
        $result = $stmt->fetch();
        $selling = (float)($result['total_selling'] ?? 0);
        $cost = (float)($result['total_cost'] ?? 0);
        return $selling - $cost;
    }

    public function getRealRevenueYear()
    {
        // Real Revenue = Selling Price - Cost of Goods Sold
        $sql = "SELECT 
                    SUM(ti.price * ti.quantity) as total_selling,
                    SUM(p.net_price * ti.quantity) as total_cost
                FROM transactions t
                JOIN transaction_items ti ON t.id = ti.transaction_id
                JOIN products p ON ti.product_id = p.id
                WHERE YEAR(t.created_at) = YEAR(CURDATE())";
        $stmt = $this->db->query($sql);
        $result = $stmt->fetch();
        $selling = (float)($result['total_selling'] ?? 0);
        $cost = (float)($result['total_cost'] ?? 0);
        return $selling - $cost;
    }

    public function getTotalTransactionsAllTime()
    {
        $sql = "SELECT COUNT(*) as total FROM transactions";
        $stmt = $this->db->query($sql);
        $result = $stmt->fetch();
        return (int)($result['total'] ?? 0);
    }

    public function getAverageTransactionValue()
    {
        $sql = "SELECT AVG(total_amount) as avg_value FROM transactions";
        $stmt = $this->db->query($sql);
        $result = $stmt->fetch();
        return (float)($result['avg_value'] ?? 0);
    }

    public function getTotalDiscountAllTime()
    {
        $sql = "SELECT COALESCE(SUM(discount_total), 0) as total_discount FROM transactions";
        $stmt = $this->db->query($sql);
        $result = $stmt->fetch();
        return (float)($result['total_discount'] ?? 0);
    }

    public function getTotalVatExemptionAllTime()
    {
        $sql = "SELECT COALESCE(SUM(total_vat_exemption), 0) as total_vat FROM transactions";
        $stmt = $this->db->query($sql);
        $result = $stmt->fetch();
        return (float)($result['total_vat'] ?? 0);
    }
}


