<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CampusCare - Home</title>
    <link rel="stylesheet" href="styleHome.css">
</head>
<body>
    <header>
        <div class="logo">
            <img src="logo.png" alt="CampusCare Logo">
        </div>
        <nav class="nav">
            <a href="Home.php" class="active">Home</a>
            <a href="marketplace.php">Marketplace</a>
            <a href="wellness.php">Mental Wellness</a>
            <a href="#">Peer Support</a>

        </nav>
        <div class="btns1">
            <button class="toggle-btn" id="theme" onclick="toggleTheme()" aria-label="Toggle dark mode">
                <span id="moonIcon">☾</span>
            </button>
            <?php if(isset($_SESSION['user_id'])): ?>
                <button id="sign_out" onclick="window.location.href='logout.php'">Sign Out</button>
            <?php else: ?>
                <button id="sign_in" onclick="window.location.href='signup.php'">Sign In</button>
            <?php endif; ?>
        </div>
    </header>
    <hr>
    <div class="heading">
        <h1>Welcome to CampusCare</h1>
        <p>Your all-in-one platform for campus marketplace and mental wellness <br>
         support. Connect, trade, and thrive in your student community.</p>
    </div>
    <div class="btns2">
        <a href="marketplace.php" class="btn-contained primary" style="color: #fff;background-color: #63d18bff;justify-content: space-evenly;
    align-items: center;
    text-decoration: none;size: 18px;
    width: 40%;padding-top: 20px;
    padding-bottom: 20px; border-radius: 8px;padding-left: 20px;
    padding-right: 20px;
    display: flex;">
            <img src="shopping_bah_white.png" alt="Shopping bag icon">Get Started
        </a>
        <a href="wellness.php" class="btn-contained secondary" style="color: #fff;background-color: #bccec3ff;justify-content: space-evenly;
    align-items: center;
    text-decoration: none;border-radius: 8px;padding-left: 20px;
    padding-right: 20px;
    width: 45%;padding-top: 19px;
    padding-bottom: 19px;display: flex;">
            <img src="heart_green.png" alt="Heart icon">Check Mental Wellness
        </a>
    </div>
    <div class="cards_Container">
        <div class="card">
            <img src="shopping_bag.png" alt="Shopping bag">
            <h3>Campus Marketplace</h3>
            <p>Buy and sell second-hand items with fellow students safely and easily</p>
        </div>

        <div class="card">
            <img src="heart.png" alt="Heart">
            <h3>Mental Health Check</h3>
            <p>Anonymous wellness quizzes to track and improve your mental health</p>
        </div>

        <div class="card">
            <img src="support.png" alt="Support">
            <h3>Peer Support</h3>
            <p>Connect with verified senior mentors for guidance and emotional support</p>
        </div>

        <div class="card">
            <img src="book.png" alt="Book">
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
    <script src="script2.js"></script>
</body>
</html>
