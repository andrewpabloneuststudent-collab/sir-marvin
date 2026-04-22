<?php
require_once 'conn/database.php';
function describeTable($db, $table) {
    echo "--- $table ---\n";
    $stmt = $db->query("DESCRIBE $table");
    $fields = $stmt->fetchAll();
    foreach ($fields as $field) {
        echo "{$field['Field']} ({$field['Type']})\n";
    }
}

describeTable($db, 'inventory');
describeTable($db, 'products');
describeTable($db, 'users');
?>
