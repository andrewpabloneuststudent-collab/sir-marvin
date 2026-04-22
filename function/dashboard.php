<?php
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
        $sql = "SELECT SUM(net_amount) as total FROM sales WHERE DATE(created_at) = CURDATE()";
        $stmt = $this->db->query($sql);
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    }

    public function getTotalSalesMonth()
    {
        $sql = "SELECT SUM(net_amount) as total FROM sales WHERE MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())";
        $stmt = $this->db->query($sql);
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    }

    public function getTransactionCountToday()
    {
        $sql = "SELECT COUNT(*) as total FROM sales WHERE DATE(created_at) = CURDATE()";
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
        $sql = "SELECT p.product_name as name, i.quantity as stock_quantity, i.reorder_level, pc.category_name 
                FROM products p 
                JOIN inventory i ON p.id = i.product_id 
                LEFT JOIN product_categories pc ON p.category_id = pc.id 
                WHERE i.quantity <= i.reorder_level 
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
        $sql = "SELECT s.*, u.username 
                FROM sales s 
                LEFT JOIN users u ON s.user_id = u.id 
                ORDER BY s.created_at DESC 
                LIMIT :limit";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', (int)$limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getTopSellingProducts($limit = 5)
    {
        $sql = "SELECT p.product_name as name, SUM(si.quantity) as total_sold, p.price 
                FROM sale_items si 
                JOIN products p ON si.product_id = p.id 
                GROUP BY si.product_id 
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
        $sql = "SELECT MONTH(created_at) as month, SUM(net_amount) as total 
                FROM sales 
                WHERE YEAR(created_at) = YEAR(CURDATE()) 
                GROUP BY MONTH(created_at)";
        $stmt = $this->db->query($sql);
        while ($row = $stmt->fetch()) {
            $salesTrend[(int)$row['month']] = (float)$row['total'];
        }
        return array_values($salesTrend); // Return as index 0-11 for JS
    }
}


