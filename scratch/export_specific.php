<?php
require_once __DIR__ . "/../conn/database.php";

try {
    $tables = ['product_categories', 'override_log'];
    $sqlOutput = "-- Specific Tables Export for Neil\n";
    $sqlOutput .= "-- Generated: " . date('Y-m-d H:i:s') . "\n\n";
    $sqlOutput .= "SET FOREIGN_KEY_CHECKS = 0;\n\n";

    foreach ($tables as $table) {
        // Get Create Table
        $stmt = $db->query("SHOW CREATE TABLE `$table` ");
        $createRow = $stmt->fetch();
        $sqlOutput .= "-- Structure for table `$table` \n";
        $sqlOutput .= "DROP TABLE IF EXISTS `$table`;\n";
        $sqlOutput .= $createRow['Create Table'] . ";\n\n";

        // Get Data
        $stmt = $db->query("SELECT * FROM `$table` ");
        $rows = $stmt->fetchAll();
        if ($rows) {
            $sqlOutput .= "-- Data for table `$table` \n";
            foreach ($rows as $row) {
                $keys = array_keys($row);
                $values = array_map(function($v) use ($db) {
                    if ($v === null) return 'NULL';
                    return $db->quote($v);
                }, array_values($row));
                
                $sqlOutput .= "INSERT INTO `$table` (`" . implode("`, `", $keys) . "`) VALUES (" . implode(", ", $values) . ");\n";
            }
            $sqlOutput .= "\n";
        }
    }

    $sqlOutput .= "SET FOREIGN_KEY_CHECKS = 1;\n";
    file_put_contents(__DIR__ . "/specific_tables_export.sql", $sqlOutput);
    echo "Successfully exported specific tables to scratch/specific_tables_export.sql\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
