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
            <a href="Home.php">Home</a>
            <a href="marketplace.php" class="active">Marketplace</a>
            <a href="wellness.php">Mental Wellness</a>
            <a href="#">Peer Support</a>
            <a href="profile.php">Profile</a>
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
                <option value="north-block">North Block</option>
                <option value="east-block">East Block</option>
                <option value="west-block">West Block</option>
                <option value="south-block">South Block</option>
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
        <!-- Add IDs to your listings for the "More" button functionality -->
        <div class="list_container" id="set1" data-category="books" data-location="north-block" data-price="medium">
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
        <div class="list_container" id="set2" data-category="electronics" data-location="south-block" data-price="high">
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
        <!-- Add IDs set3, set4, set5, set6 to the remaining listings -->
     </div>
    <button class="btn">More</button>
     <hr>
     <footer>
        <img src="logo.png" alt="CampusCare Logo">
        <p>Supporting student well-being through community connection, peer support, and accessible mental health resources.</p>
        <h6>© 2025 CampusCare. All rights reserved.</h6>
     </footer>

    <script>
    // Theme functionality
    function toggleTheme() {
        const body = document.body;
        const icon = document.getElementById("moonIcon");
        body.classList.toggle("dark-mode");

        // Save theme to localStorage
        const theme = body.classList.contains("dark-mode") ? "dark" : "light";
        localStorage.setItem("theme", theme);

        // Toggle icon
        icon.textContent = theme === "dark" ? "☀︎" : "☾";
    }

    // Single window.onload function
    window.onload = function() {
        // Theme loading
        const savedTheme = localStorage.getItem("theme");
        const icon = document.getElementById("moonIcon");

        if (savedTheme === "dark") {
            document.body.classList.add("dark-mode");
            icon.textContent = "☀︎";
        } else {
            icon.textContent = "☾";
        }

        // User email display (if you add this element later)
        const userEmail = localStorage.getItem('userEmail');
        const profileEmail = document.getElementById('profileEmail');
        if (userEmail && profileEmail) {
            profileEmail.textContent = userEmail;
        }

        // Load more functionality
        const imageContainerElement = document.querySelector('.imageContainer');
        const btn = document.querySelector('.btn');

        function loadMoreCards() {
            const rand = Math.floor(Math.random() * 6) + 1;
            const cardToClone = document.getElementById(`set${rand}`);
            
            if (cardToClone) {
                const clone = cardToClone.cloneNode(true);
                imageContainerElement.appendChild(clone);
            }
        }

        if (btn) {
            btn.addEventListener('click', () => {
                for(let i = 0; i < 6; i++) {
                    loadMoreCards();
                }
            });
        }

        // Filtering functionality
        const listings = [
            {
                id: 1,
                title: "Advanced Calculus Textbook",
                price: 500,
                image: "list1.png",
                location: "North Block",
                condition: "Like New",
                seller: "Alex Chen",
                category: "books"
            },
            // ... your other listings
        ];

        function renderListings(data) {
            const container = document.querySelector('.imageContainer');
            container.innerHTML = '';

            data.forEach(item => {
                const card = document.createElement('div');
                card.className = 'list_container'; // Fixed spelling
                card.innerHTML = `
                    <div class="topimg">
                        <img src="${item.image}">
                    </div>
                    <div class="content">
                        <div class="parting">
                            <h2>${item.title}</h2>
                            <h3>Rs.${item.price}/-</h3>
                        </div>
                        <p>${item.title} available in ${item.location}</p>
                        <div class="parting">
                            <p><span aria-label="Location: ${item.location}">⚲ ${item.location}</span></p>
                            <h4>${item.condition}</h4>
                        </div>
                        <div class="parting1">
                            <h5>Seller:</h5>
                            <h6>${item.seller}</h6>
                        </div>
                        <button aria-label="Contact seller ${item.seller}">🗨Contact Seller</button>
                    </div>
                `;
                container.appendChild(card);
            });
        }

        function applyFilters() {
            const keyword = document.getElementById("searchInput").value.toLowerCase();
            const category = document.getElementById("categoryFilter").value;
            const location = document.getElementById("locationFilter").value;
            const price = document.getElementById("priceFilter").value;

            const filtered = listings.filter(item => {
                const matchKeyword = item.title.toLowerCase().includes(keyword);
                const matchCategory = !category || item.category === category;
                const matchLocation = !location || item.location.toLowerCase().includes(location.toLowerCase());
                const matchPrice =
                    !price ||
                    (price === 'low' && item.price < 500) ||
                    (price === 'medium' && item.price >= 500 && item.price <= 2000) ||
                    (price === 'high' && item.price > 2000);

                return matchKeyword && matchCategory && matchLocation && matchPrice;
            });

            renderListings(filtered);
        }

        // Event listeners for filtering
        document.getElementById("searchInput").addEventListener("input", applyFilters);
        document.getElementById("categoryFilter").addEventListener("change", applyFilters);
        document.getElementById("locationFilter").addEventListener("change", applyFilters);
        document.getElementById("priceFilter").addEventListener("change", applyFilters);
    };

    // Tab functionality (if you need it)
    function showTab(tabId) {
        const tabs = document.querySelectorAll(".tab-content");
        tabs.forEach(tab => {
            tab.style.display = "none";
        });
        document.getElementById(tabId).style.display = "block";
    }
    </script>
</body>
</html>