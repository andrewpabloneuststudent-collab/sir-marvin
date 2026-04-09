<?php

namespace Classes;

class VoidItems
{
    public int $id;
    public int $user_id;
    public string $void_code;
    public int $item_id;
    public string $item_name;
    public int $quantity;
    public float $reason;
    public string $reason_text;
    public string $date_voided;

    private $con;
    private string $response;

    public function __construct($db)
    {
        $this->con = $db;
    }

    public function generateVoidCode()
    {
        // Generate unique void code: VOID-YYYYMMDD-XXXXX
        $date = date('Ymd');
        $random = strtoupper(substr(md5(time() . rand()), 0, 5));
        return 'VOID-' . $date . '-' . $random;
    }

    // Check if user has permission to void items (Owner or Admin only)
    public function canVoidItems($userPosition)
    {
        return in_array($userPosition, ['Owner', 'Admin']);
    }

    // Get void code by permission check
    public function checkVoidPermission($userId, $position)
    {
        if (!$this->canVoidItems($position)) {
            $this->response = 'Only Owner and Admin can void items';
            return false;
        }
        return true;
    }

    // Record a voided item
    public function voidItem($userId, $itemId, $itemName, $quantity, $reasonText)
    {
        try {
            $voidCode = $this->generateVoidCode();
            
            $stmt = $this->con->prepare("INSERT INTO void_items (user_id, void_code, item_id, item_name, quantity, reason_text, date_voided) 
            VALUES (?, ?, ?, ?, ?, ?, NOW())");
            
            $insert = $stmt->execute([
                $userId,
                $voidCode,
                $itemId,
                $itemName,
                $quantity,
                $reasonText
            ]);

            if ($insert) {
                $this->response = 'Item voided successfully with code: ' . $voidCode;
                return true;
            } else {
                $this->response = 'Failed to void item';
                return false;
            }
        } catch (Exception $e) {
            $this->response = 'Error: ' . $e->getMessage();
            return false;
        }
    }

    // Get all voided items
    public function getAllVoidedItems()
    {
        try {
            $stmt = $this->con->prepare("
                SELECT v.*, u.username, u.position 
                FROM void_items v 
                JOIN users u ON v.user_id = u.id 
                ORDER BY v.date_voided DESC
            ");
            $stmt->execute();
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $this->response = 'Error: ' . $e->getMessage();
            return [];
        }
    }

    // Get voided items by user
    public function getVoidedItemsByUser($userId)
    {
        try {
            $stmt = $this->con->prepare("
                SELECT * FROM void_items 
                WHERE user_id = ? 
                ORDER BY date_voided DESC
            ");
            $stmt->execute([$userId]);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $this->response = 'Error: ' . $e->getMessage();
            return [];
        }
    }

    // Get void item by void code
    public function getVoidItemByCode($voidCode)
    {
        try {
            $stmt = $this->con->prepare("
                SELECT v.*, u.username, u.position 
                FROM void_items v 
                JOIN users u ON v.user_id = u.id 
                WHERE v.void_code = ?
            ");
            $stmt->execute([$voidCode]);
            return $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $this->response = 'Error: ' . $e->getMessage();
            return null;
        }
    }

    // Get response message
    public function getResponse()
    {
        return $this->response;
    }
}
?>
