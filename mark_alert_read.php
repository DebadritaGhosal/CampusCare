<?php
session_start();
require_once 'config/database.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$alert_id = $data['alert_id'];

$stmt = $pdo->prepare(
    "UPDATE alerts SET is_read = 1 WHERE id = ? AND user_id = ?"
);
$success = $stmt->execute([$alert_id, $_SESSION['user_id']]);

echo json_encode(['success' => $success]);
?>