<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Connect to database
$pdo = new PDO('mysql:host=127.0.0.1;dbname=signup_details;charset=utf8mb4', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Fetch current user data
$stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? $user['name'];
    $year = $_POST['year'] ?? $user['year'];
    $major = $_POST['major'] ?? $user['major'];
    $phone = $_POST['phone'] ?? $user['phone'];
    $college = $_POST['college'] ?? $user['college'];
    
    // Update user in database
    $update_stmt = $pdo->prepare('UPDATE users SET name = ?, year = ?, major = ?, phone = ?, college = ? WHERE id = ?');
    if ($update_stmt->execute([$name, $year, $major, $phone, $college, $_SESSION['user_id']])) {
        $message = 'Profile updated successfully!';
        $message_type = 'success';
        // Refresh user data
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
    } else {
        $message = 'Error updating profile.';
        $message_type = 'error';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile - CampusCare</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f5f5f5; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; }
        h1 { color: #2E8B57; }
        label { display: block; margin: 15px 0 5px; }
        input, select { width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ddd; border-radius: 5px; }
        button { background: #2E8B57; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; }
        .message { padding: 10px; margin: 15px 0; border-radius: 5px; }
        .success { background: #d4edda; color: #155724; }
        .error { background: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Edit Profile</h1>
        <a href="profile.php" style="color: #2E8B57; text-decoration: none;">← Back to Profile</a>
        
        <?php if ($message): ?>
            <div class="message <?php echo $message_type; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        
        <form method="post">
            <label for="name">Full Name</label>
            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
            
            <label for="email">Email (cannot be changed)</label>
            <input type="email" id="email" value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
            
            <label for="year">Year</label>
            <select id="year" name="year">
                <option value="Freshman" <?php echo ($user['year'] == 'Freshman') ? 'selected' : ''; ?>>Freshman</option>
                <option value="Sophomore" <?php echo ($user['year'] == 'Sophomore') ? 'selected' : ''; ?>>Sophomore</option>
                <option value="Junior" <?php echo ($user['year'] == 'Junior') ? 'selected' : ''; ?>>Junior</option>
                <option value="Senior" <?php echo ($user['year'] == 'Senior') ? 'selected' : ''; ?>>Senior</option>
                <option value="Graduate" <?php echo ($user['year'] == 'Graduate') ? 'selected' : ''; ?>>Graduate</option>
            </select>
            
            <label for="major">Major</label>
            <input type="text" id="major" name="major" value="<?php echo htmlspecialchars($user['major']); ?>">
            
            <label for="phone">Phone Number</label>
            <input type="tel" id="phone" name="phone" pattern="[0-9]{10}" value="<?php echo htmlspecialchars($user['phone']); ?>">
            
            <label for="college">College/University</label>
            <input type="text" id="college" name="college" value="<?php echo htmlspecialchars($user['college']); ?>">
            
            <button type="submit">Save Changes</button>
        </form>
    </div>
</body>
</html>