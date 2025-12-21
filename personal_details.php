<?php
session_start();
if (!isset($_SESSION['temp_user'])) {
    header('Location: signup.php');
    exit();
}
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dob = $_POST['dob'] ?? '';
    $gender = $_POST['gender'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $college = $_POST['college'] ?? '';
    
    if (empty($dob) || empty($gender) || empty($phone) || empty($college)) {
        $message = 'Please fill out all fields.';
        $message_type = 'error';
    } elseif (!preg_match('/^[0-9]{10}$/', $phone)) {
        $message = 'Phone number must be a 10-digit number.';
        $message_type = 'error';
    } else {
        // Merge with temp_user
        $user_data = array_merge($_SESSION['temp_user'], [
            'dob' => $dob,
            'gender' => $gender,
            'phone' => $phone,
            'college' => $college
        ]);
        
        try {
            $pdo = new PDO('mysql:host=127.0.0.1;dbname=campuscare;charset=utf8mb4', 'root', '');
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $passwordHash = password_hash($user_data['password'], PASSWORD_DEFAULT);
            // Create table if it doesn't exist
            $tableExists = $pdo->query("SHOW TABLES LIKE 'signup_details'")->rowCount() > 0;
            
            if (!$tableExists) {
                $pdo->exec("CREATE TABLE signup_details (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    email VARCHAR(255) UNIQUE NOT NULL,
                    password VARCHAR(255) NOT NULL,
                    name VARCHAR(100) NOT NULL,
                    dob DATE,
                    gender VARCHAR(20),
                    phone VARCHAR(20),
                    college VARCHAR(255),
                    joined_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )");
            }
            
            // Check if email exists
            $checkStmt = $pdo->prepare('SELECT id FROM signup_details WHERE email = ?');
            $checkStmt->execute([$user_data['email']]);
            
            if ($checkStmt->fetch()) {
                $message = 'Email already exists. Please use a different email.';
                $message_type = 'error';
            } else {
                // Insert into signup_details table
                $stmt = $pdo->prepare('INSERT INTO signup_details (email, password, name, dob, gender, phone, college, joined_date) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())');
                
                $stmt->execute([
                    $user_data['email'],
                    $passwordHash,
                    $user_data['name'],
                    $user_data['dob'],
                    $user_data['gender'],
                    $user_data['phone'],
                    $user_data['college']
                ]);
                
                $user_id = $pdo->lastInsertId();
                
                $_SESSION['user_id'] = $user_id;
                $_SESSION['email'] = $user_data['email'];
                $_SESSION['name'] = $user_data['name'];
                
                unset($_SESSION['temp_user']);
                
                header('Location: login.php');
                exit;
            }
            
        } catch (PDOException $e) {
            $message = 'Database error: ' . $e->getMessage();
            $message_type = 'error';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Personal Details</title>
  <link rel="stylesheet" href="s.css">
  <style>
    #message { margin-top: 12px; font-size: 14px; }
    #message.success { color: green; }
    #message.error { color: red; }
  </style>
</head>
<body>
  <div class="theme-toggle">
    <button id="toggleTheme">☾</button>
  </div>
  <div class="container">
    <div class="login-box">
      <h1>Personal Details</h1>
      <p>Please fill out your details</p>
      <form id="detailsForm" method="post" action="">
        <label for="dob">Date of Birth</label>
        <input type="date" id="dob" name="dob" required value="<?php echo isset($_POST['dob']) ? htmlspecialchars($_POST['dob']) : ''; ?>">
        
        <label for="gender">Gender</label>
        <select id="gender" name="gender" required>
          <option value="">--Select--</option>
          <option value="Male" <?php echo (isset($_POST['gender']) && $_POST['gender'] === 'Male') ? 'selected' : ''; ?>>Male</option>
          <option value="Female" <?php echo (isset($_POST['gender']) && $_POST['gender'] === 'Female') ? 'selected' : ''; ?>>Female</option>
          <option value="Other" <?php echo (isset($_POST['gender']) && $_POST['gender'] === 'Other') ? 'selected' : ''; ?>>Other</option>
          <option value="Prefer not to say" <?php echo (isset($_POST['gender']) && $_POST['gender'] === 'Prefer not to say') ? 'selected' : ''; ?>>Prefer not to say</option>
        </select>

        <label for="phone">Phone Number</label>
        <input type="tel" id="phone" name="phone" pattern="[0-9]{10}" placeholder="10-digit number" required value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">

        <label for="college">College/University</label>
        <input type="text" id="college" name="college" required value="<?php echo isset($_POST['college']) ? htmlspecialchars($_POST['college']) : ''; ?>">

        <button type="submit">Complete Signup</button>
      </form>
      <?php if ($message): ?>
        <div id="message" class="<?php echo htmlspecialchars($message_type); ?>">
          <?php echo htmlspecialchars($message); ?>
        </div>
      <?php endif; ?>
    </div>
  </div>
  <script>
    const toggleBtn = document.getElementById('toggleTheme');
    toggleBtn.addEventListener('click', () => {
      document.body.classList.toggle('dark');
      toggleBtn.textContent = document.body.classList.contains('dark') ? '☀︎' : '☾';
    });
  </script>
</body>
</html>