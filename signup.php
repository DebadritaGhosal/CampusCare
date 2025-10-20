<?php
session_start();
$name = $email = $password = '';
$error = '';
$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    if (empty($name) || empty($email) || empty($password)) {
        $error = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long.";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $_SESSION['temp_user'] = [
            'name' => $name,
            'email' => $email,
            'password_hash' => $hashed_password
        ];
        header('Location: personal_details.php');
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>CampusCare | Sign Up</title>
  <link rel="stylesheet" href="styles.css" />
  <style>
    .theme-toggle {
      position: absolute;
      top: 20px;
      right: 20px;
    }
    
    .theme-toggle button {
      background: none;
      border: none;
      font-size: 1.5rem;
      cursor: pointer;
      padding: 0.5rem;
      border-radius: 50%;
      transition: background-color 0.3s;
    }
    
    .theme-toggle button:hover {
      background-color: rgba(0, 0, 0, 0.1);
    }
    
    .dark {
      background-color: #1a1a1a;
      color: #ffffff;
    }
    
    .dark .container {
      background-color: #2d2d2d;
    }
    
    .container {
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: 100vh;
      background-color: #f5f5f5;
    }
    
    .signup-box {
      background: white;
      padding: 2rem;
      border-radius: 10px;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
      width: 100%;
      max-width: 400px;
    }
    
    .dark .signup-box {
      background: #2d2d2d;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.3);
    }
    
    .signup-box h1 {
      text-align: center;
      margin-bottom: 0.5rem;
      color: #2E8B57;
    }
    
    .signup-box p {
      text-align: center;
      margin-bottom: 1.5rem;
      color: #666;
    }
    
    .dark .signup-box p {
      color: #ccc;
    }
    
    form {
      display: flex;
      flex-direction: column;
    }
    
    label {
      margin-bottom: 0.5rem;
      font-weight: 500;
    }
    
    input {
      padding: 0.75rem;
      margin-bottom: 1rem;
      border: 1px solid #ddd;
      border-radius: 5px;
      font-size: 1rem;
    }
    
    .dark input {
      background-color: #3d3d3d;
      border-color: #555;
      color: white;
    }
    
    button {
      background-color: #2E8B57;
      color: white;
      padding: 0.75rem;
      border: none;
      border-radius: 5px;
      font-size: 1rem;
      cursor: pointer;
      transition: background-color 0.3s;
    }
    
    button:hover {
      background-color: #267349;
    }
    
    .login-link {
      text-align: center;
      margin-top: 1rem;
    }
    
    .login-link a {
      color: #2E8B57;
      text-decoration: none;
    }
    
    .login-link a:hover {
      text-decoration: underline;
    }
    
    .message {
      padding: 0.75rem;
      margin-bottom: 1rem;
      border-radius: 5px;
      text-align: center;
    }
    
    .error {
      background-color: #ffebee;
      color: #c62828;
      border: 1px solid #ffcdd2;
    }
    
    .dark .error {
      background-color: #3c1f1f;
      color: #ff8a8a;
      border-color: #5d2f2f;
    }
    
    .success {
      background-color: #e8f5e8;
      color: #2e7d32;
      border: 1px solid #c8e6c9;
    }
    
    .dark .success {
      background-color: #1f3c1f;
      color: #8aff8a;
      border-color: #2f5d2f;
    }
  </style>
</head>
<body class="<?php echo isset($_COOKIE['theme']) && $_COOKIE['theme'] === 'dark' ? 'dark' : ''; ?>">
  <div class="theme-toggle">
    <button id="toggleTheme"><?php echo (isset($_COOKIE['theme']) && $_COOKIE['theme'] === 'dark') ? '☀' : '☾'; ?></button>
  </div>
  <div class="container">
    <div class="signup-box">
      <h1>Sign Up</h1>
      <p>Create a new account</p>
      <?php if ($error): ?>
        <div class="message error"><?php echo htmlspecialchars($error); ?></div>
      <?php endif; ?>
      
      <?php if ($success): ?>
        <div class="message success"><?php echo htmlspecialchars($success); ?></div>
      <?php endif; ?>

      <form method="POST" action="">
        <label for="name">Full Name</label>
        <input type="text" id="name" name="name" placeholder="Full Name" value="<?php echo htmlspecialchars($name); ?>" required />

        <label for="email">Email</label>
        <input type="email" id="email" name="email" placeholder="Email" value="<?php echo htmlspecialchars($email); ?>" required />

        <label for="password">Password</label>
        <input type="password" id="password" name="password" placeholder="Password" required />

        <button type="submit">Continue</button>
        
        <p class="login-link">Already have an account? <a href="login.php">Log in</a></p>
      </form>
    </div>
  </div>
  <script>
    const toggleBtn = document.getElementById('toggleTheme');
    toggleBtn.addEventListener('click', () => {
      document.body.classList.toggle('dark');
      const isDark = document.body.classList.contains('dark');
      toggleBtn.textContent = isDark ? '☀' : '☾';
      document.cookie = `theme=${isDark ? 'dark' : 'light'}; path=/; max-age=31536000`; // 1 year
    });
    document.querySelector('form').addEventListener('submit', function(e) {
      const password = document.getElementById('password').value;
      if (password.length < 6) {
        e.preventDefault();
        alert('Password must be at least 6 characters long.');
        return false;
      }
      return true;
    });
  </script>
</body>
</html>