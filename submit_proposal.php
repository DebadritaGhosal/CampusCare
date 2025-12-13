<?php
session_start();
require_once 'config/database.php';

header('Content-Type: application/json');

if(!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login to suggest a price']);
    exit;
}

$product_id = $_POST['product_id'];
$price = $_POST['price'];
$message = $_POST['message'] ?? '';
$buyer_id = $_SESSION['user_id'];
$buyer_name = $_SESSION['user_name'];

try {
    // Check if product exists and is available
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ? AND status IN ('available', 'negotiating')");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();
    
    if(!$product) {
        echo json_encode(['success' => false, 'message' => 'Product not available for proposals']);
        exit;
    }
    
    // Check if buyer is not the seller
    if($product['seller_id'] == $buyer_id) {
        echo json_encode(['success' => false, 'message' => 'You cannot suggest price on your own item']);
        exit;
    }
    
    // Check if buyer already has active proposal
    $stmt = $pdo->prepare("SELECT id FROM proposals WHERE product_id = ? AND buyer_id = ? AND status IN ('pending', 'countered')");
    $stmt->execute([$product_id, $buyer_id]);
    if($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'You already have an active proposal for this item']);
        exit;
    }
    
    // Insert proposal
    $expires_at = date('Y-m-d H:i:s', strtotime('+48 hours'));
    $stmt = $pdo->prepare("INSERT INTO proposals (product_id, buyer_id, buyer_name, proposed_price, message, expires_at) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$product_id, $buyer_id, $buyer_name, $price, $message, $expires_at]);
    
    // Update product status if first proposal
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM proposals WHERE product_id = ? AND status IN ('pending', 'countered')");
    $stmt->execute([$product_id]);
    if($stmt->fetchColumn() == 1) {
        $stmt = $pdo->prepare("UPDATE products SET status = 'negotiating' WHERE id = ?");
        $stmt->execute([$product_id]);
    }
    
    echo json_encode(['success' => true, 'message' => 'Price suggestion submitted successfully']);
    
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>