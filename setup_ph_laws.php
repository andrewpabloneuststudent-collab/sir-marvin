<?php
session_start();
require_once __DIR__ . "/conn/database.php";

echo "<h1>Updating Categories According to Philippine Law</h1>";
echo "<p>RA 9994 (Senior Citizens Act) & RA 10754 (PWD Act)</p>";
echo "<hr>";

// Update Health & Wellness category - should be VAT-exempt and eligible for discounts
$stmt = $db->prepare("
    UPDATE product_categories 
    SET has_vat = 0, senior_discount = 1, pwd_discount = 1 
    WHERE id = 24
");

if ($stmt->execute()) {
    echo "✓ Updated Health & Wellness category<br>";
} else {
    echo "✗ Failed to update Health & Wellness<br>";
}

echo "<h2>Category Configuration Summary</h2>";
echo "<table border='1' cellpadding='10'>";
echo "<tr><th>Category</th><th>VAT Status</th><th>Senior Discount (RA 9994)</th><th>PWD Discount (RA 10754)</th><th>Notes</th></tr>";

$stmt = $db->prepare("SELECT id, category_name, has_vat, senior_discount, pwd_discount FROM product_categories ORDER BY category_name");
$stmt->execute();
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

$medicine_categories = [
    'Prescription Medicines' => 'Medical treatment',
    'Over-the-Counter (OTC)' => 'Health maintenance',
    'Medical Supplies' => 'Medical care',
    'Vitamins & Supplements' => 'Health supplements',
    'First Aid' => 'Emergency care',
    'Diagnostics' => 'Medical testing',
    'Herbal Products' => 'Traditional medicine',
    'Health & Wellness' => 'Health products'
];

foreach ($categories as $cat) {
    $vat_status = $cat['has_vat'] ? 'Standard VAT (12%)' : '<strong style="color:green;">EXEMPT (RA No. 9357)</strong>';
    $senior = $cat['senior_discount'] ? '<strong style="color:green;">YES (12% discount)</strong>' : 'NO';
    $pwd = $cat['pwd_discount'] ? '<strong style="color:green;">YES (12% discount)</strong>' : 'NO';
    
    $notes = isset($medicine_categories[$cat['category_name']]) ? $medicine_categories[$cat['category_name']] : 'Non-medical';
    
    echo "<tr>";
    echo "<td><strong>{$cat['category_name']}</strong></td>";
    echo "<td>{$vat_status}</td>";
    echo "<td>{$senior}</td>";
    echo "<td>{$pwd}</td>";
    echo "<td>{$notes}</td>";
    echo "</tr>";
}

echo "</table>";

echo "<h2>Philippine Law References</h2>";
echo "<ul>";
echo "<li><strong>RA 9994 (Senior Citizens Act of 2010):</strong> 12% discount on health and medical products for senior citizens (60 years and above)</li>";
echo "<li><strong>RA 10754 (Expanded PWD Privileges Act):</strong> 12% discount on medical/health-related products for persons with disabilities</li>";
echo "<li><strong>VAT Exemption:</strong> Medicines, medical devices, and medical services are VAT-exempt per BIR regulations</li>";
echo "</ul>";

echo "<h2>How It Works in Your POS</h2>";
echo "<ol>";
echo "<li>Customer selects a product (e.g., Prescription Medicine)</li>";
echo "<li>System applies VAT exemption automatically (no 12% VAT charged)</li>";
echo "<li>If customer is Senior or PWD, additional 12% discount is applied</li>";
echo "<li>Receipt shows breakdown of discounts and VAT exemptions applied</li>";
echo "</ol>";

echo "<h2>Example Calculation</h2>";
echo "<p><strong>Product: Amoxicillin (₱500) - Prescription Medicine</strong></p>";
echo "<ul>";
echo "<li><strong>Regular Customer:</strong></li>";
echo "<ul>";
echo "<li>Price: ₱500</li>";
echo "<li>VAT (0% - EXEMPT): ₱0</li>";
echo "<li><strong>Total: ₱500</strong></li>";
echo "</ul>";
echo "<li><strong>Senior Citizen/PWD Customer:</strong></li>";
echo "<ul>";
echo "<li>Price: ₱500</li>";
echo "<li>VAT (0% - EXEMPT): ₱0</li>";
echo "<li>Senior/PWD Discount (12%): -₱60</li>";
echo "<li><strong>Total: ₱440</strong></li>";
echo "</ul>";
echo "</ul>";

?>
<br><br>
<a href="check_categories.php">← Back to Categories</a> | 
<a href="staffpos/dashboard.php">→ Go to POS</a>
