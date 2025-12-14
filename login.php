<?php 
session_start();
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'] ?? '';

    if (!$email) {
        $message = 'Please enter a valid email address.';
        $message_type = 'error';
    } else {
        try {
            $pdo = new PDO('mysql:host=127.0.0.1;dbname=campuscare;charset=utf8mb4', 'root', '');
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // FIXED: Changed from 'users' to 'signupdetails'
            $stmt = $pdo->prepare('SELECT * FROM signupdetails WHERE email = ?');
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user && password_verify($password, $user['password'])) {
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['name'] = $user['name'] ?? 'User';
                
                header('Location: profile.php');
                exit;
            } else {
                $message = 'Invalid email or password.';
                $message_type = 'error';
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
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>CampusCare | Log In</title>
  <link rel="stylesheet" href="style.css" />
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
      <h1>Welcome Back</h1>
      <p>Please sign in to continue</p>
      <form id="loginForm" method="post" action="">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" placeholder="Email" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" />
        <label for="password">Password</label>
        <input type="password" id="password" name="password" placeholder="Password" required />
        <button type="submit">Log In</button>
        <p class="signup">Don't have an account? <a href="signup.php">Sign up</a></p>
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
      localStorage.setItem('campuscare_theme', document.body.classList.contains('dark') ? 'dark' : 'light');
    });
    document.addEventListener('DOMContentLoaded', () => {
      const saved = localStorage.getItem('campuscare_theme');
      if (saved === 'dark') {
        document.body.classList.add('dark');
        toggleBtn.textContent = '☀︎';
      } else {
        toggleBtn.textContent = '☾';
      }
    });
  </script>
</body>
</html>