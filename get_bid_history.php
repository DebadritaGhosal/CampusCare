<?php
require_once 'config/database.php';

header('Content-Type: application/json');

$product_id = $_GET['product_id'];

$stmt = $pdo->prepare(
    "SELECT proposed_price as price, status, created_at
     FROM proposals 
     WHERE product_id = ? 
     ORDER BY created_at ASC"
);
$stmt->execute([$product_id]);
$history = $stmt->fetchAll();

echo json_encode($history);
?>