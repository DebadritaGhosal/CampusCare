<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CampusCare Marketplace</title>
    <link rel="stylesheet" href="styleMarket.css">
</head>
<body>
    <header>
        <div class="logo">
            <img src="logo.png" alt="CampusCare Logo">
        </div>
        <nav class="nav">
            <a href="Home.html">Home</a>
            <a href="marketplace.php" class="active">Marketplace</a>
            <a href="wellness.html">Mental Wellness</a>
            <a href="#">Peer Support</a>
            <a href="profile.html">Profile</a>
        </nav>
        <div class="btns1">
            <button class="toggle-btn" id="theme" onclick="toggleTheme()"><span id="moonIcon">☾</span></button>
            <button id="sign_in" onclick="window.location.href='signup.html'">Sign In</button>
        </div>
    </header>
    <hr>
     <div class="heading">
        <h1>Campus Marketplace</h1>
        <p>Find great deals on textbooks, electronics, and furniture from fellow students</p>
     </div>
     <section class="search-filter-section">
     <div class="search-bar">
        <label for="searchInput" class="sr-only">Search items</label>
        <input type="text" id="searchInput" placeholder="Search items...">
        <button id="searchBtn" aria-label="Search">🔍</button>
     </div>
     <div class="filters">
        <div class="filter-group">
            <label for="categoryFilter">Category</label>
            <select id="categoryFilter" aria-label="Filter by category">
                <option value="">All Categories</option>
                <option value="books">Books</option>
                <option value="electronics">Electronics</option>
                <option value="furniture">Furniture</option>
                <option value="lab">Lab Requirements</option>
            </select>
        </div>
        <div class="filter-group">
            <label for="locationFilter">Location</label>
            <select id="locationFilter" aria-label="Filter by location">
                <option value="">All Locations</option>
                <option value="east-block">East Block</option>
                <option value="west-block">West Block</option>
                <option value="south-block">South Block</option>
                <option value="north-block">North Block</option>
            </select>
        </div>
        <div class="filter-group">
            <label for="priceFilter">Price Range</label>
            <select id="priceFilter" aria-label="Filter by price range">
                <option value="">All Prices</option>
                <option value="low">Under ₹500</option>
                <option value="medium">₹500–₹2000</option>
                <option value="high">Above ₹2000</option>
            </select>
        </div>
    </div>
    </section>
     <div class="imageContainer">
        <div class="list_container" data-category="books" data-location="north-block" data-price="medium">
            <div class="topimg">
                <img src="list1.png" alt="Advanced Calculus textbook">
            </div>
            <div class="content">
                <div class="parting">
                    <h2>Advanced Calculus Textbook</h2>
                    <h3>Rs.500/-</h3>
                </div>
                <p>Barely used calculus textbook for Math 201. All pages intact, no highlighting.</p>
                <div class="parting">
                    <p><span aria-label="Location: North Block">⚲ North Block</span></p>
                    <h4>Like New</h4>
                </div>
                <div class="parting1">
                    <h5>Seller:</h5>
                    <h6>Alex Chen</h6>
                </div>
                <button aria-label="Contact seller Alex Chen">🗨Contact Seller</button>
            </div>
        </div>
        <div class="list_container" data-category="electronics" data-location="south-block" data-price="high">
            <div class="topimg">
                <img src="list2.png" alt="MacBook Pro laptop">
            </div>
            <div class="content">
                <div class="parting">
                    <h2>MacBook Pro 13" (2020)</h2>
                    <h3>Rs.20000/-</h3>
                </div>
                <p>Excellent condition MacBook Pro with 256GB storage. Perfect for students.</p>
                <div class="parting">
                    <p><span aria-label="Location: South Block">⚲ South Block</span></p>
                    <h4>Excellent</h4>
                </div>
                <div class="parting1">
                    <h5>Seller:</h5>
                    <h6>Sarah Kim</h6>
                </div>
                <button aria-label="Contact seller Sarah Kim">🗨Contact Seller</button>
            </div>
        </div>
        <div class="list_container" data-category="furniture" data-location="east-block" data-price="medium">
            <div class="topimg">
                <img src="list3.png" alt="Desk and chair set">
            </div>
            <div class="content">
                <div class="parting">
                    <h2>Study Desk & Chair Set</h2>
                    <h3>Rs.3500/-</h3>
                </div>
                <p>Comfortable study setup. Desk has drawers and the chair is ergonomic.</p>
                <div class="parting">
                    <p><span aria-label="Location: East Block">⚲ East Block</span></p>
                    <h4>Good</h4>
                </div>
                <div class="parting1">
                    <h5>Seller:</h5>
                    <h6>Mike Johnson</h6>
                </div>
                <button aria-label="Contact seller Mike Johnson">🗨Contact Seller</button>
            </div>
        </div>
        <div class="list_container" data-category="lab" data-location="west-block" data-price="medium">
            <div class="topimg">
                <img src="list4.png" alt="Organic chemistry lab kit">
            </div>
            <div class="content">
                <div class="parting">
                    <h2>Organic Chemistry Lab Kit</h2>
                    <h3>Rs.900/-</h3>
                </div>
                <p>Complete lab kit with all necessary equipment for Chemistry 102.</p>
                <div class="parting">
                    <p><span aria-label="Location: West Block">⚲ West Block</span></p>
                    <h4>Good</h4>
                </div>
                <div class="parting1">
                    <h5>Seller:</h5>
                    <h6>Emma Davis</h6>
                </div>
                <button aria-label="Contact seller Emma Davis">🗨Contact Seller</button>
            </div>
        </div>
        <div class="list_container" data-category="electronics" data-location="north-block" data-price="high">
            <div class="topimg">
                <img src="list5.png" alt="Gaming headset">
            </div>
            <div class="content">
                <div class="parting">
                    <h2>Gaming Headset</h2>
                    <h3>Rs.5000/-</h3>
                </div>
                <p>High-quality gaming headset with noise cancellation. Great for online classes too.</p>
                <div class="parting">
                    <p><span aria-label="Location: North Block">⚲ North Block</span></p>
                    <h4>Very Good</h4>
                </div>
                <div class="parting1">
                    <h5>Seller:</h5>
                    <h6>Ryan Lee</h6>
                </div>
                <button aria-label="Contact seller Ryan Lee">🗨Contact Seller</button>
            </div>
        </div>
        <div class="list_container" data-category="electronics" data-location="south-block" data-price="high">
            <div class="topimg">
                <img src="list6.png" alt="Mini refrigerator">
            </div>
            <div class="content">
                <div class="parting">
                    <h2>Mini Refrigerator</h2>
                    <h3>Rs.15000/-</h3>
                </div>
                <p>Perfect for dorm rooms. Energy efficient and quiet operation.</p>
                <div class="parting">
                    <p><span aria-label="Location: South Block">⚲ South Block</span></p>
                    <h4>Like New</h4>
                </div>
                <div class="parting1">
                    <h5>Seller:</h5>
                    <h6>Lisa Wang</h6>
                </div>
                <button aria-label="Contact seller Lisa Wang">🗨Contact Seller</button>
            </div>
        </div>
    </div>
    <button class="btn">More</button>
     <hr>
     <footer>
        <img src="logo.png" alt="CampusCare Logo">
        <p>Supporting student well-being through community connection, peer support, and accessible mental health resources.</p>
        <h6>© 2025 CampusCare. All rights reserved.</h6>
     </footer>
    <script src="script2.js"></script>
</body>
</html>