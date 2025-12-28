<?php
session_start();
require_once __DIR__ . '/includes/auth_check.php';
require_once __DIR__ . '/includes/db_connect.php';

if (empty($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: student_dashboard.php?tab=tab1');
    exit;
}

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
if ($id <= 0) {
    header('Location: student_dashboard.php?tab=tab1');
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT image FROM marketplace WHERE id = :id AND user_id = :uid");
    $stmt->execute([':id' => $id, ':uid' => (int)$_SESSION['user_id']]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        $del = $pdo->prepare("DELETE FROM marketplace WHERE id = :id AND user_id = :uid");
        $del->execute([':id' => $id, ':uid' => (int)$_SESSION['user_id']]);

        if (!empty($row['image']) && $row['image'] !== 'default_item.png') {
            $imgPath = __DIR__ . '/' . ltrim($row['image'], '/\\');
            if (file_exists($imgPath)) {
                @unlink($imgPath);
            }
        }
    }
} catch (PDOException $e) {
    error_log('Remove listing error: ' . $e->getMessage());
}

header('Location: student_dashboard.php?tab=tab1');
exit;
?>