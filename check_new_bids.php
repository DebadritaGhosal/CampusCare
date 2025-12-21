<?php
session_start();
require_once 'config/database.php';

header('Content-Type: application/json');

$product_id = $_GET['product_id'];
$last_check = $_GET['last_check'];

// Check for new bids
$stmt = $pdo->prepare(
    "SELECT COUNT(*) as new_bids, MAX(proposed_price) as latest_price
     FROM proposals 
     WHERE product_id = ? 
     AND created_at > ? 
     AND buyer_id != ?"
);
$stmt->execute([$product_id, $last_check, $_SESSION['user_id']]);
$result = $stmt->fetch();

echo json_encode([
    'new_bids' => $result['new_bids'],
    'latest_price' => $result['latest_price'] ?: 0
]);
?>