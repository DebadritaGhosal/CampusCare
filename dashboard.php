<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: signup.php');
    exit();
}
$user_email = $_SESSION['user_email'] ?? 'User';
$user_name = $_SESSION['user_name'] ?? 'Student';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>CampusCare | Dashboard</title>
  <link rel="stylesheet" href="styleHome.css" />
  <style>
    .btns {
      display: flex;
      align-items: center;
      gap: 12px; 
    }
    
    #logoutBtn {
      background-color: #e74c3c;
      color: #fff;
      border: none;
      padding: 8px 18px;
      border-radius: 16px;
      font-size: 16px;
      cursor: pointer;
      margin-left: 0;
      white-space: nowrap;
      transition: background 0.3s;
    }

    #logoutBtn:hover {
      background-color: #cd5f53;
    }
    
    #theme {
      background: transparent;
      border: none;
      padding: 5px;
      cursor: pointer;
      font-size: 1.5rem;
      color: #333;
    }
    
    #theme:hover {
      color: #34d399;
    }
    
    .dark-mode #theme {
      color: var(--text-color);
    }
    
    .user-info {
      color: #2E8B57;
      font-weight: 500;
    }
    
    .dark-mode .user-info {
      color: #4CAF50;
    }
    .dark-mode {
      background-color: #1a1a1a;
      color: #ffffff;
    }
    
    .dark-mode header {
      background-color: #2d2d2d;
    }
    
    .dark-mode .card {
      background-color: #2d2d2d;
      color: #ffffff;
    }
    
    .dark-mode footer {
      background-color: #0d0d0d;
      color: #ffffff;
    }
  </style>
</head>
<body class="<?php echo isset($_COOKIE['theme']) && $_COOKIE['theme'] === 'dark' ? 'dark-mode' : ''; ?>">
  <header>
    <div class="logo">
      <img src="logo.png" alt="CampusCare Logo">
    </div>
    <nav class="nav">
      <a href="Home.php" class="active">Home</a>
      <a href="marketplace.php">Marketplace</a>
      <a href="wellness.php">Mental Wellness</a>
      <a href="#">Peer Support</a>
      <a href="profile.php">Profile</a>
    </nav>
    <div class="btns">
      <button class="toggle-btn" id="theme">
        <span id="moonIcon"><?php echo (isset($_COOKIE['theme']) && $_COOKIE['theme'] === 'dark') ? '☀' : '☾'; ?></span>
      </button>
      <span class="user-info" style="margin-top: 10px;">
        <strong id="userEmail"><?php echo htmlspecialchars($user_email); ?></strong>
      </span>
      <a href="logout.php" style="text-decoration: none;">
        <button id="logoutBtn">Log Out</button>
      </a>
    </div>
  </header> 
  <hr>
  <div class="heading">
    <h1>Welcome to CampusCare, <?php echo htmlspecialchars($user_name); ?>!</h1>
    <p>Your all-in-one platform for campus marketplace and mental wellness <br>
    Connect, trade, and thrive in your student community.</p>
  </div>
  <div class="btns2">
    <a href="marketplace.php" class="btn-contained primary">
      <img src="shopping_bag_white.png" alt="Shopping">Get Started
    </a>
    <a href="wellness.php" class="btn-contained secondary">
      <img src="heart_green.png" alt="Heart">Check Mental Wellness
    </a>
  </div>
  <div class="cards_Container">
    <div class="card">
      <img src="shopping_bag.png" alt="Marketplace">
      <h3>Campus Marketplace</h3>
      <p>Buy and sell second-hand items with fellow students safely and easily</p>
    </div>

    <div class="card">
      <img src="heart.png" alt="Mental Health">
      <h3>Mental Health Check</h3>
      <p>Anonymous wellness quizzes to track and improve your mental health</p>
    </div>

    <div class="card">
      <img src="support.png" alt="Peer Support">
      <h3>Peer Support</h3>
      <p>Connect with verified senior mentors for guidance and emotional support</p>
    </div>

    <div class="card">
      <img src="book.png" alt="Academic Resources">
      <h3>Academic Resources</h3>
      <p>Find textbooks, notes, and study materials from your peers</p>
    </div>
  </div>
  <hr>
  <footer>
    <img src="logo.png" alt="CampusCare Logo">
    <p>Supporting student well-being through community connection, peer support, and accessible mental health resources.</p>
    <h6>© <?php echo date('Y'); ?> CampusCare. All rights reserved.</h6>
  </footer>
  <script>
    document.getElementById('theme').addEventListener('click', function() {
      const body = document.body;
      const icon = document.getElementById("moonIcon");
      body.classList.toggle("dark-mode");
      const theme = body.classList.contains("dark-mode") ? "dark" : "light";
      document.cookie = `theme=${theme}; path=/; max-age=31536000`; 
      icon.textContent = theme === "dark" ? "☀" : "☾";
    });
    document.getElementById('logoutBtn').addEventListener('click', function(e) {
      if (!confirm('Are you sure you want to log out?')) {
        e.preventDefault();
      }
    });
  </script>
</body>
</html>