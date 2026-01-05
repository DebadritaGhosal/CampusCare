<?php
session_start();
require_once 'includes/db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Handle Deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm'])) {
    $user_id = $_SESSION['user_id'];
    
    try {
        $pdo->beginTransaction();

        // 1. Get profile picture path to delete file later
        $stmt = $pdo->prepare("SELECT profile_pic FROM signup_details WHERE id = ?");
        $stmt->execute([$user_id]);
        $profile_pic = $stmt->fetchColumn();

        // 2. Delete from related tables
        // We attempt to delete from tables where user_id is likely used
        $tables = [
            'marketplace',
            'wellness_checks',
            'support_sessions',
            'mental_wellness_messages',
            'quiz_results',
            'alerts'
        ];

        foreach ($tables as $table) {
            try {
                // Check if table exists first to avoid fatal errors
                $pdo->query("SELECT 1 FROM $table LIMIT 1");
                $pdo->prepare("DELETE FROM $table WHERE user_id = ?")->execute([$user_id]);
            } catch (Exception $e) {
                // Table might not exist or column name differs, continue
                continue;
            }
        }

        // Specific deletions for tables with different column names
        try {
            $pdo->prepare("DELETE FROM proposals WHERE buyer_id = ?")->execute([$user_id]);
        } catch (Exception $e) {}

        try {
            $pdo->prepare("DELETE FROM products WHERE seller_id = ?")->execute([$user_id]);
        } catch (Exception $e) {}

        // 3. Delete from main user table
        $pdo->prepare("DELETE FROM signup_details WHERE id = ?")->execute([$user_id]);

        $pdo->commit();

        // Delete profile picture file if it's not default
        if ($profile_pic && file_exists($profile_pic) && $profile_pic != 'dp.png' && $profile_pic != 'default_item.png') {
            @unlink($profile_pic);
        }

        // Destroy session and redirect
        session_destroy();
        header('Location: signup.php?message=account_deleted');
        exit();

    } catch (Exception $e) {
        $pdo->rollBack();
        $error = "Error deleting account: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Account | CampusCare</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f5f5f5; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .container { background: white; padding: 40px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); text-align: center; max-width: 450px; width: 90%; }
        h1 { color: #dc2626; margin-bottom: 20px; }
        p { color: #4b5563; margin-bottom: 30px; line-height: 1.6; }
        .btn-group { display: flex; gap: 15px; justify-content: center; }
        .btn { padding: 12px 24px; border-radius: 8px; border: none; font-weight: 600; cursor: pointer; transition: all 0.3s; text-decoration: none; font-size: 16px; }
        .btn-cancel { background: #e5e7eb; color: #374151; }
        .btn-cancel:hover { background: #d1d5db; }
        .btn-delete { background: #dc2626; color: white; }
        .btn-delete:hover { background: #b91c1c; }
        .error { color: #dc2626; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>⚠ Delete Account</h1>
        <?php if (isset($error)): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <p>Are you sure you want to permanently delete your account? This action <strong>cannot be undone</strong>. All your listings, messages, and history will be erased.</p>
        
        <form method="POST">
            <div class="btn-group">
                <a href="student_dashboard.php" class="btn btn-cancel">Cancel</a>
                <button type="submit" name="confirm" class="btn btn-delete">Yes, Delete Account</button>
            </div>
        </form>
    </div>
</body>
</html>