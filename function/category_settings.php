<?php
/**
 * API: Get and Update Category Discount Settings
 * GET  → returns all categories with their flags
 * POST → { id, has_vat, senior_discount, pwd_discount } → updates a single category
 */
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../conn/database.php';

// Only Owner/Admin can write settings
$role = strtolower($_SESSION['position'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $stmt = $db->query("SELECT id, category_name, has_vat, senior_discount, pwd_discount FROM product_categories ORDER BY category_name ASC");
        echo json_encode(['success' => true, 'categories' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
    } catch (PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($role !== 'owner' && $role !== 'admin') {
        echo json_encode(['error' => 'Access denied. Only Owner or Admin can change category settings.']);
        exit;
    }

    $body = json_decode(file_get_contents('php://input'), true);
    $id              = (int)($body['id'] ?? 0);
    $has_vat         = (int)($body['has_vat'] ?? 0);
    $senior_discount = (int)($body['senior_discount'] ?? 0);
    $pwd_discount    = (int)($body['pwd_discount'] ?? 0);

    if (!$id) {
        echo json_encode(['error' => 'Invalid category ID']);
        exit;
    }

    try {
        $stmt = $db->prepare("UPDATE product_categories SET has_vat = ?, senior_discount = ?, pwd_discount = ? WHERE id = ?");
        $stmt->execute([$has_vat, $senior_discount, $pwd_discount, $id]);
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit;
}

echo json_encode(['error' => 'Invalid request method']);
