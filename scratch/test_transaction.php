<?php
error_reporting(E_ALL);
require_once 'C:/xampp/htdocs/mmbpos/conn/database.php';
require_once 'C:/xampp/htdocs/mmbpos/function/workingpos.php';

$p = new Product($db);
$result = $p->processTransaction(
    1,
    [['id' => 18, 'price' => 35.28, 'qty' => 1]],
    1,
    'Walk-in',
    null
);
echo json_encode($result, JSON_PRETTY_PRINT);
