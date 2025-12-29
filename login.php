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
            
            // Query users table
            $stmt = $pdo->prepare('SELECT * FROM signup_details WHERE email = ?');
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user && password_verify($password, $user['password'])) {
                // Update last login
                try {
                    $updateStmt = $pdo->prepare('UPDATE signup_details SET last_login = NOW() WHERE id = ?');
                    $updateStmt->execute([$user['id']]);
                } catch (Exception $e) {
                    // Silently continue even if update fails
                    error_log("Failed to update last_login: " . $e->getMessage());
                }
                
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['name'] = $user['name'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['profile_pic'] = $user['profile_pic'];
                
                // Create profile picture from first letter
                if (empty($user['profile_pic'])) {
                    $firstLetter = strtoupper(substr($user['name'], 0, 1));
                    $_SESSION['profile_initials'] = $firstLetter;
                }
                $_SESSION['role'] = strtolower(trim($user['role']));
                // Redirect based on role
                switch ($_SESSION['role']) {
    case 'admin':
        header('Location: admin_dashboard.php');
        break;

    case 'mentor':
        header('Location: mentor_dashboard.php');
        break;

    case 'student':
        header('Location: student_dashboard.php');
        break;

    default:
        header('Location: profile.php');
}
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
  <style>
    * {
  box-sizing: border-box;
  margin: 0;
  padding: 0;
}
body {
  font-family: 'Segoe UI', sans-serif;
  background-color: #f1fafa;
  color: #111;
  display: flex;
  flex-direction: column;
  align-items: center;
  min-height: 100vh;
  transition: background-color 0.3s ease, color 0.3s ease;
}

body.dark {
  background-color: #1e1e1e;
  color: #f1fafa;
}

.container {
  margin-top: 100px;
  display: flex;
  justify-content: center;
  width: 100%;
}

.theme-btn {
  background: none;
  border: none;
  font-size: 22px;
  cursor: pointer;
  color: inherit;
}

.theme-btn:hover {
  transform: scale(1.1);
}
.login-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 8px;
}

/* Circular icon button */
.theme-toggle {
  width: 32px;
  height: 32px;

  border-radius: 50%;
  border: none;
  outline: none;

  background: rgba(0,0,0,0.05);
  color: #333;

  font-size: 16px;
  cursor: pointer;

  display: flex;
  align-items: center;
  justify-content: center;

  transition: background 0.25s ease,
             transform 0.15s ease,
             color 0.25s ease;
}

.theme-toggle:hover {
  background: rgba(0,0,0,0.12);
  transform: scale(1.08);
}

.theme-toggle:active {
  transform: scale(0.96);
}

/* Light / Dark adjustments */
body.dark .theme-toggle {
  background: rgba(255,255,255,0.08);
  color: #eee;
}

body.dark .theme-toggle:hover {
  background: rgba(255,255,255,0.18);
}


body.dark .theme-btn {
  color: #f1fafa;
}

.login-box {
  background-color: #fff;
  padding: 30px;
  border-radius: 16px;
  box-shadow: 0 8px 28px rgba(0, 0, 0, 0.1);
  width: 320px;
  text-align: center;
  transition: background-color 0.3s ease;
}

body.dark .login-box {
  background-color: #2c2c2c;
}

.login-box h1 {
  font-size: 26px;text-align:center;
  margin-bottom: 10px;color: #2ecc71;
}

.login-box p {
  font-size: 14px;
  margin-bottom: 20px;
}

label {
  display: block;
  text-align: left;
  margin-bottom: 5px;
  font-size: 14px;
}

input {
  width: 100%;
  padding: 12px 16px;
  margin-bottom: 16px;
  border-radius: 10px;
  border: 1px solid #ccc;
  font-size: 14px;
  background-color: #f9f9f9;
}

body.dark input {
  background-color: #444;
  border: 1px solid #666;
  color: #fff;
}

button[type="submit"] {
  background-color: #00757f;
  color: white;
  padding: 12px;
  border: none;
  border-radius: 10px;
  width: 100%;
  font-size: 16px;
  cursor: pointer;
}

button[type="submit"]:hover {
  background-color: #005d66;
}

.signup {
  font-size: 14px;
  margin-top: 18px;
  color: #444;
}

.signup a {
  color: #00757f;
  text-decoration: none;
}

/* Media Queries */

@media (max-width: 480px) {
  .login-box {
    width: 90%;
    padding: 24px 18px;
  }

  .login-box h1 {
    font-size: 22px;
  }

  .login-box p,
  label,
  input,
  button[type="submit"],
  .signup {
    font-size: 13px;
  }
}
body.dark .login-box {
  background-color: #2c2c2c;
}

@media (max-width: 768px) {
  .login-box {
    width: 80%;
    max-width: 340px;
    padding: 26px 22px;
  }
}
.password-wrapper {
    position: relative;
}

.toggle-password {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    cursor: pointer;
    z-index: 10;
}
    #message { margin-top: 12px; font-size: 14px; }
    #message.success { color: green; }
    #message.error { color: red; }
  </style>
</head>
<body>
    <div class="container">
    <div class="login-box">
      <div class="login-header">
  <h1>Welcome Back</h1>
  <button id="toggleTheme" class="theme-toggle">☾</button>
</div>

      <p>Please log in to continue</p>
      <form id="loginForm" method="post" action="">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" placeholder="Email" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" />
        <label for="password">Password</label>
<div class="password-wrapper">
    <input type="password" id="password" name="password" placeholder="Must be atleast 6 characters"required>
    <span id="togglePassword" class="toggle-password">👁️</span>
</div>
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
    document.addEventListener("DOMContentLoaded", () => {
      const pwd = document.getElementById("password");
    const toggle = document.getElementById("togglePassword");
    /* 👁️ Show / Hide Password */
    toggle.addEventListener("click", () => {
        pwd.type = pwd.type === "password" ? "text" : "password";
        toggle.textContent = pwd.type === "password" ? "👁️" : "🙈";
    });

    const toggleBtn = document.getElementById('toggleTheme');
toggleBtn.addEventListener('click', () => {
  document.body.classList.toggle('dark');
  toggleBtn.textContent = document.body.classList.contains('dark') ? '☀︎' : '☾';
});

  });
  </script>
</body>
</html>